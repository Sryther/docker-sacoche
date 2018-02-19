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

$action = (isset($_POST['f_action'])) ? $_POST['f_action'] : '';
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve  = (isset($_POST['f_eleve']))  ? ( (is_array($_POST['f_eleve']))  ? $_POST['f_eleve']  : explode(',',$_POST['f_eleve'])  ) : array() ;
$tab_groupe = (isset($_POST['f_groupe'])) ? ( (is_array($_POST['f_groupe'])) ? $_POST['f_groupe'] : explode(',',$_POST['f_groupe']) ) : array() ;
$tab_eleve  = array_filter( Clean::map('entier',$tab_eleve)  , 'positif' );
$tab_groupe = array_filter( Clean::map('entier',$tab_groupe) , 'positif' );

// Ajouter des élèves à des groupes
if($action=='ajouter')
{
  foreach($tab_eleve as $user_id)
  {
    foreach($tab_groupe as $groupe_id)
    {
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_admin( $user_id , 'eleve' , $groupe_id , 'groupe' , TRUE );
    }
  }
}

// Retirer des élèves à des groupes
elseif($action=='retirer')
{
  foreach($tab_eleve as $user_id)
  {
    foreach($tab_groupe as $groupe_id)
    {
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_admin( $user_id , 'eleve' , $groupe_id , 'groupe' , FALSE );
    }
  }
}

// Affichage du bilan des affectations des élèves dans les groupes ; en deux requêtes pour récupérer les élèves sans groupes et les groupes sans élèves
$tab_niveau_groupe = array();
$tab_user          = array();
// Récupérer la liste des groupes
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_groupes_avec_niveaux();
foreach($DB_TAB as $DB_ROW)
{
  $tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
  $tab_user[$DB_ROW['groupe_id']] = '';
}
// Récupérer la liste des élèves / groupes
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_avec_groupe( 'eleve' /*profil_type*/ , TRUE /*only_actuels*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_user[$DB_ROW['groupe_id']]  .= html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'<br />';
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
Json::add_str('<hr />'.NL);
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
  Json::add_str('<table class="affectation">'.NL);
  Json::add_str(  '<thead><tr>'.$TH[$niveau_id].'</tr></thead>'.NL);
  Json::add_str(  '<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>'.NL);
  Json::add_str(  '<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>'.NL);
  Json::add_str('</table>'.NL);
}
Json::end( TRUE );
?>
