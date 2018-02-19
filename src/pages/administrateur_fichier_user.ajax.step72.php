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
// Étape 72 - Traitement des ajouts/modifications d'adresses éventuelles (siecle_parents | onde_parents | tableur_parents | factos_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
$tab_users_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt' );
// Récupérer les éléments postés et ajouter/modifier les adresses
$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
$nb_add = 0;
$nb_mod = 0;
foreach($tab_check as $check_infos)
{
  if(substr($check_infos,0,4)=='mod_')
  {
    $i_fichier = Clean::entier( substr($check_infos,4) );
    if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
    {
      DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
      $nb_mod++;
    }
  }
  elseif(substr($check_infos,0,4)=='add_')
  {
    $i_fichier = Clean::entier( substr($check_infos,4) );
    if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
    {
      DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
      $nb_add++;
    }
  }
}
// Afficher le résultat
Json::add_str('<p><label class="valide">Nouvelles adresses ajoutées : '.$nb_add.'</label></p>'.NL);
Json::add_str('<p><label class="valide">Anciennes adresses modifiées : '.$nb_mod.'</label></p>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step81" id="passer_etape_suivante">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
