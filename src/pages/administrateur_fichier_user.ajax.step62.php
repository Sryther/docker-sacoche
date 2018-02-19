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
// Étape 62 - Traitement des ajouts d'affectations éventuelles (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Récupérer les éléments postés
$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
$tab_post = array( 'classe'=>array() , 'pp'=>array() , 'matiere'=>array() , 'groupe'=>array() );
foreach($tab_check as $check_infos)
{
  if( (substr($check_infos,0,7)=='classe_') || (substr($check_infos,0,3)=='pp_') || (substr($check_infos,0,8)=='matiere_') || (substr($check_infos,0,7)=='groupe_') )
  {
    list($obj,$id1,$id2,$etat) = explode('_',$check_infos);
    $tab_post[$obj][$id1][$id2] = (bool)$etat;
  }
}
// Modifier des associations users/classes (profs uniquements, pour les élèves c'est fait à l'étape 52)
$nb_asso_classes = count($tab_post['classe']);
if($nb_asso_classes)
{
  foreach($tab_post['classe'] as $user_id => $tab_id2)
  {
    foreach($tab_id2 as $classe_id => $etat)
    {
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_admin($user_id,$import_profil,$classe_id,'classe',$etat);
    }
  }
}
// Ajouter des associations users/pp (profs uniquements)
$nb_asso_pps = count($tab_post['pp']);
if($nb_asso_pps)
{
  foreach($tab_post['pp'] as $user_id => $tab_id2)
  {
    foreach($tab_id2 as $classe_id => $etat)
    {
      // En espérant qu'on ne fasse pas une association de PP avec une classe à laquelle le prof n'est pas associée
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_professeur_principal($user_id,$classe_id,$etat);
    }
  }
}
// Ajouter des associations users/matières (profs uniquements)
$nb_asso_matieres = count($tab_post['matiere']);
if($nb_asso_matieres)
{
  foreach($tab_post['matiere'] as $user_id => $tab_id2)
  {
    foreach($tab_id2 as $matiere_id => $etat)
    {
      DB_STRUCTURE_MATIERE::DB_modifier_liaison_professeur_matiere($user_id,$matiere_id,$etat);
    }
  }
}
// Ajouter des associations users/groupes (profs ou élèves)
$nb_asso_groupes = count($tab_post['groupe']);
if($nb_asso_groupes)
{
  foreach($tab_post['groupe'] as $user_id => $tab_id2)
  {
    foreach($tab_id2 as $groupe_id => $etat)
    {
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_admin($user_id,$import_profil,$groupe_id,'groupe',$etat);
    }
  }
}
// Afficher le résultat
if($import_profil=='professeur')
{
  Json::add_str('<p><label class="valide">Modifications associations utilisateurs / classes effectuées : '.$nb_asso_classes.'</label></p>'.NL);
  if($import_origine=='siecle')
  {
    Json::add_str('<p><label class="valide">Modifications associations utilisateurs / p.principal effectuées : '.$nb_asso_pps.'</label></p>'.NL);
    Json::add_str('<p><label class="valide">Modifications associations utilisateurs / matières effectuées : '.$nb_asso_matieres.'</label></p>'.NL);
  }
}
Json::add_str('<p><label class="valide">Modifications associations utilisateurs / groupes effectuées : '.$nb_asso_groupes.'</label></p>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 7.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
