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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='initialiser')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action     = (isset($_POST['f_action']))     ? $_POST['f_action']                     : '';
$date_debut = (isset($_POST['f_date_debut'])) ? Clean::date_fr($_POST['f_date_debut']) : '';
$date_fin   = (isset($_POST['f_date_fin']))   ? Clean::date_fr($_POST['f_date_fin'])   : '';
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_select_periodes        = (isset($_POST['select_periodes']))        ? ( (is_array($_POST['select_periodes']))        ? $_POST['select_periodes']        : explode(',',$_POST['select_periodes'])        ) : array() ;
$tab_select_classes_groupes = (isset($_POST['select_classes_groupes'])) ? ( (is_array($_POST['select_classes_groupes'])) ? $_POST['select_classes_groupes'] : explode(',',$_POST['select_classes_groupes']) ) : array() ;
$tab_select_periodes        = array_filter( Clean::map('entier',$tab_select_periodes)        , 'positif' );
$tab_select_classes_groupes = array_filter( Clean::map('entier',$tab_select_classes_groupes) , 'positif' );

$tab_groupe    = array();
$tab_periode   = array();
$tab_jointure  = array();
$tab_graphique = array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bilan des affectations des périodes aux classes & groupes ; en plusieurs requêtes pour récupérer les périodes sans classes-groupes et les classes-groupes sans périodes.
// 1/2 - On commence les premières requêtes dès maintenant afin d'avoir les noms des groupes et des périodes à disposition.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Récupérer la liste des classes & groupes, dans l'ordre des niveaux
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_et_groupes_avec_niveaux();
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucune classe et aucun groupe ne sont enregistrés !' );
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_groupe[$DB_ROW['groupe_id']]    = $DB_ROW['groupe_nom'];
  $tab_graphique[$DB_ROW['groupe_id']] = '';
}
// Récupérer la liste des périodes, dans l'ordre choisi par l'admin
$DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_periodes();
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucune période n\'est enregistrée !' );
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_periode[$DB_ROW['periode_id']] = $DB_ROW['periode_nom'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter des périodes à des classes & groupes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $date_debut && $date_fin )
{
  // Formater les dates
  $date_debut_mysql = To::date_french_to_mysql($date_debut);
  $date_fin_mysql   = To::date_french_to_mysql($date_fin);
  // Vérifier que le date de début est antérieure à la date de fin
  if($date_debut_mysql>$date_fin_mysql)
  {
    Json::end( FALSE , 'La date de début est postérieure à la date de fin !' );
  }
  foreach($tab_select_periodes as $periode_id)
  {
    foreach($tab_select_classes_groupes as $groupe_id)
    {
      DB_STRUCTURE_PERIODE::DB_modifier_liaison_groupe_periode( $groupe_id , $periode_id , TRUE , $date_debut_mysql , $date_fin_mysql );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer des périodes à des classes & groupes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

elseif($action=='retirer')
{
  $notification_contenu = '';
  $notification_intro = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'];
  foreach($tab_select_periodes as $periode_id)
  {
    foreach($tab_select_classes_groupes as $groupe_id)
    {
      DB_STRUCTURE_PERIODE::DB_modifier_liaison_groupe_periode( $groupe_id , $periode_id , FALSE );
      // Log de l'action
      SACocheLog::ajouter('Suppression de l\'association période "'.$tab_periode[$periode_id].'" (n°'.$periode_id.') / regroupement "'.$tab_groupe[$groupe_id].'" (n°'.$groupe_id.'), et donc des bilans officiels associés.');
      $notification_contenu .= $notification_intro.' a supprimé l\'association période "'.$tab_periode[$periode_id].'" (n°'.$periode_id.') / regroupement "'.$tab_groupe[$groupe_id].'" (n°'.$groupe_id.'), et donc les bilans officiels associés.'."\r\n";
    }
  }
  // Notifications (rendues visibles ultérieurement)
  if($notification_contenu)
  {
    DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bilan des affectations des périodes aux classes & groupes ; en plusieurs requêtes pour récupérer les périodes sans classes-groupes et les classes-groupes sans périodes.
// 2/2 - On poursuit avec les requêtes suivantes (associations) et l'affichage.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Récupérer l'amplitude complète sur l'ensemble des périodes
$DB_ROW = DB_STRUCTURE_PERIODE::DB_recuperer_amplitude_periodes();
$tout_debut     = ($DB_ROW['tout_debut'])     ? $DB_ROW['tout_debut']     : '2000-01-01' ;
$toute_fin      = ($DB_ROW['toute_fin'])      ? $DB_ROW['toute_fin']      : '2000-01-01' ;
$nb_jours_total = ($DB_ROW['nb_jours_total']) ? $DB_ROW['nb_jours_total'] : 0;
// Récupérer la liste des jointures, et le nécessaire pour établir les graphiques
$DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_jointure_groupe_periode_avec_infos_graphiques($tout_debut);
$memo_groupe_id = 0;
foreach($DB_TAB as $DB_ROW)
{
  $groupe_id = $DB_ROW['groupe_id'];
  $date_affich_debut = To::date_mysql_to_french($DB_ROW['jointure_date_debut']);
  $date_affich_fin   = To::date_mysql_to_french($DB_ROW['jointure_date_fin']);
  $tab_jointure[$groupe_id][$DB_ROW['periode_id']] = html($date_affich_debut).' ~ '.html($date_affich_fin).'<q class="date_ajouter" title="Importer ces dates dans les champs."></q>';
  // graphique (début)
  if($memo_groupe_id!=$groupe_id)
  {
    $memo_position = 0;
    $memo_groupe_id = $groupe_id;
  }
  $margin_left = 100*round($DB_ROW['position_jour_debut'] / $nb_jours_total , 4);
  $width       = 100*round( ($DB_ROW['nb_jour']+1) / $nb_jours_total , 4);  // On ajoute un jour pour dessiner les barres jusqu'au jour suivant.
  if($memo_position+0.02<$margin_left) // Le 0.02 sert à éviter les erreurs d'arrondi et une erreur PHP style un test 12.34<12.34 qui renvoie vrai !
  {
    // Deux périodes ne sont pas consécutives
    $margin_left_erreur = $memo_position;
    $width_erreur = $margin_left - $memo_position;
    $tab_graphique[$groupe_id] .= '<div class="graph_erreur" style="margin-left:'.$margin_left_erreur.'%;width:'.$width_erreur.'%"></div>';
  }
  elseif($memo_position>$margin_left+0.02) // Le 0.02 sert à éviter les erreurs d'arrondi et une erreur PHP style un test 12.34<12.34 qui renvoie vrai !
  {
    // Deux périodes se chevauchent
    $margin_left_erreur = $margin_left;
    $width_erreur = $memo_position - $margin_left;
    $tab_graphique[$groupe_id] .= '<div class="graph_erreur" style="margin-left:'.$margin_left_erreur.'%;width:'.$width_erreur.'%"></div>';
  }
  $tab_graphique[$groupe_id] .= '<div class="graph_partie" style="margin-left:'.$margin_left.'%;width:'.$width.'%"></div>';
  $memo_position = $margin_left + $width;
  // graphique (fin)
}
// Fabrication du tableau résultant
foreach($tab_groupe as $groupe_id => $groupe_nom)
{
  $tab_groupe[$groupe_id] = '<th>'.html($groupe_nom).'</th>';
  foreach($tab_periode as $periode_id => $periode_nom)
  {
    $tab_groupe[$groupe_id] .= (isset($tab_jointure[$groupe_id][$periode_id])) ? '<td>'.$tab_jointure[$groupe_id][$periode_id].'</td>' : '<td class="hc">-</td>' ;
  }
  $tab_groupe[$groupe_id] .= '<td>'.$tab_graphique[$groupe_id].'</td>';
}
foreach($tab_periode as $periode_id => $periode_nom)
{
  $tab_periode[$periode_id] = '<th>'.html($periode_nom).'</th>';
}
// Affichage du tableau résultant
Json::add_str('<hr />'.NL);
Json::add_str('<table>'.NL);
Json::add_str(  '<thead>'.NL);
Json::add_str(    '<tr><td class="nu"></td>'.implode('',$tab_periode).'<td class="graph_total">Étendue du '.To::date_mysql_to_french($tout_debut).' au '.To::date_mysql_to_french($toute_fin).'.</td></tr>'.NL);
Json::add_str(  '</thead>'.NL);
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr>'.implode('</tr>'.NL.'<tr>',$tab_groupe).'</tr>'.NL);
Json::add_str(  '</tbody>'.NL);
Json::add_str('</table><p>&nbsp;</p>'.NL);
Json::end( TRUE );
?>
