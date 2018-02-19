<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 82 - Traitement des liens de responsabilités des parents (siecle_parents | onde_parents | tableur_parents | factos_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse[$eleve_id][$parent_id] = $resp_legal_num;
$tab_memo_analyse = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'memo_analyse.txt' );
// Récupérer les éléments postés
$tab_eleve_id = array() ;
$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
foreach($tab_check as $check_infos)
{
  if(substr($check_infos,0,4)=='mod_')
  {
    $eleve_id = Clean::entier( substr($check_infos,4) );
    if( isset($tab_memo_analyse[$eleve_id]) )
    {
      $tab_eleve_id[] = $eleve_id;
    }
  }
}
$nb_modifs_eleves = count($tab_eleve_id);
if($nb_modifs_eleves)
{
  // supprimer les liens de responsabilité des élèves concernés (il est plus simple de réinitialiser que de traiter les resp un par un puis de vérifier s'il n'en reste pas à supprimer...)
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_jointures_parents_for_eleves(implode(',',$tab_eleve_id));
  // modifier les liens de responsabilité
  foreach($tab_eleve_id as $eleve_id)
  {
    foreach($tab_memo_analyse[$eleve_id] as $parent_id => $resp_legal_num)
    {
      DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num);
    }
  }
}
// Afficher le résultat
$s = ($nb_modifs_eleves>1) ? 's' : '' ;
Json::add_str('<p><label class="valide">Liens de responsabilités modifiés pour '.$nb_modifs_eleves.' élève'.$s.'</label></p>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
