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

$action          = (isset($_POST['f_action']))        ? $_POST['f_action']   : '';
$enscompl        = (isset($_POST['f_enscompl']))      ? $_POST['f_enscompl'] : '';
$only_groupes_id = (isset($_POST['only_groupes_id'])) ? Clean::texte( $_POST['only_groupes_id']) : '';
// Avant c'était un tableau qui est transmis, mais à cause d'une limitation possible "suhosin" / "max input vars", on est passé à une concaténation en chaine...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

//
// Modifier des associations
//

if($action=='associer')
{
  // dispositif
  if(!DB_STRUCTURE_LIVRET::DB_tester_enscompl($enscompl))
  {
    Json::end( FALSE , 'Modalité "'.$objet.'" inconnue !' );
  }
  // liste des élèves
  if(empty($tab_eleve))
  {
    Json::end( FALSE , 'Aucun compte élève récupéré !' );
  }
  // go
  if( $enscompl != 'AUC' )
  {
    foreach($tab_eleve as $eleve_id)
    {
      DB_STRUCTURE_LIVRET::DB_modifier_eleve_enscompl( $enscompl , $eleve_id );
    }
  }
  else
  {
    DB_STRUCTURE_LIVRET::DB_supprimer_eleve_enscompl( implode(',',$tab_eleve) );
  }
}

//
// Affichage du bilan des affectations des dispositifs aux élèves
//

$tab_niveau_groupe = array();
$tab_enscompl      = array();
$tab_user          = array();
$tab_niveau_groupe[0][0] = 'sans classe';
$tab_user[0]             = '';

if($only_groupes_id)
{
  $tab_id = explode(',',$only_groupes_id);
  $tab_id = Clean::map('entier',$tab_id);
  $tab_id = array_filter($tab_id,'positif');
  $only_groupes_id = implode(',',$tab_id);
}

// Récupérer la liste des dispositifs
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_enscompl();
foreach($DB_TAB as $DB_ROW)
{
  $tab_enscompl[$DB_ROW['eleve_id']] = '<span class="fluo" title="'.html($DB_ROW['livret_enscompl_nom']).'">'.$DB_ROW['livret_enscompl_code'].'</span> ';
}

// Récupérer la liste des classes
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_avec_niveaux( $niveau_ordre='DESC' , $only_groupes_id );
foreach($DB_TAB as $DB_ROW)
{
  $tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
  $tab_user[$DB_ROW['groupe_id']] = '';
}
// Récupérer la liste des élèves / classes
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' , 1 /*only_actuels*/ , 'eleve_classe_id,user_id,user_nom,user_prenom' /*liste_champs*/ , FALSE /*with_classe*/ );
foreach($DB_TAB as $DB_ROW)
{
  if( empty($only_groupes_id) || isset($tab_user[$DB_ROW['eleve_classe_id']]) )
  {
    $span = isset($tab_enscompl[$DB_ROW['user_id']]) ? $tab_enscompl[$DB_ROW['user_id']] : '' ;
    $tab_user[$DB_ROW['eleve_classe_id']] .= $span.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'<br />';
  }
}
// Assemblage du tableau résultant
$TH = array();
$TB = array();
$TF = array();
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
  $TH[$niveau_id] = '';
  $TB[$niveau_id] = '';
  $TF[$niveau_id] = '';
  foreach($tab_groupe as $groupe_id => $groupe_nom)
  {
    $nb = mb_substr_count($tab_user[$groupe_id],'<br />','UTF-8');
    $s = ($nb>1) ? 's' : '' ;
    $TH[$niveau_id] .= '<th>'.$groupe_nom.'</th>';
    $TB[$niveau_id] .= '<td>'.mb_substr($tab_user[$groupe_id],0,-6,'UTF-8').'</td>';
    $TF[$niveau_id] .= '<td>'.$nb.' élève'.$s.'</td>';
  }
}
// Afficher le retour
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
  if(mb_strlen($TB[$niveau_id])>9)
  {
    Json::add_str('<table class="affectation">'.NL);
    Json::add_str(  '<thead><tr>'.$TH[$niveau_id].'</tr></thead>'.NL);
    Json::add_str(  '<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>'.NL);
    Json::add_str(  '<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>'.NL);
    Json::add_str('</table>'.NL);
  }
}
Json::end( TRUE );
?>
