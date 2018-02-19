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

// Mettre à jour l'élément de formulaire "select_professeurs"

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$select          = (isset($_POST['f_select']))        ? Clean::lettres($_POST['f_select'])       : '';
$page_ref        = (isset($_POST['f_page_ref']))      ? Clean::id(     $_POST['f_page_ref'])     : '';
$groupe_id       = (isset($_POST['f_groupe_id']))     ? Clean::entier( $_POST['f_groupe_id'])    : 0;
$matiere_id      = (isset($_POST['f_matiere_id']))    ? Clean::entier( $_POST['f_matiere_id'])   : 0;
$prof_id         = (isset($_POST['f_prof_id']))       ? Clean::entier( $_POST['f_prof_id'])      : 0;
$rubrique_join   = (isset($_POST['f_rubrique_join'])) ? Clean::texte( $_POST['f_rubrique_join']) : '';
$only_groupes_id = (isset($_POST['only_groupes_id'])) ? Clean::texte( $_POST['only_groupes_id']) : '';
$is_all          = (isset($_POST['f_is_all']))        ? Clean::entier( $_POST['f_is_all'])       : 0;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas particulier d'une recherche de profs potentiellement associées à une classe & une matière du livret
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($select=='profs_matiere') && $page_ref && $rubrique_join && $groupe_id && $matiere_id )
{
  $rubrique_type = ($page_ref=='6e') ? 'c3_matiere' : 'c4_matiere' ;
  $tab_meilleure_suggestion = DB_STRUCTURE_LIVRET::DB_recuperer_profs_jointure_rubrique( $rubrique_type , $rubrique_join , $matiere_id , $groupe_id );
  $tab_autres_propositions  = DB_STRUCTURE_LIVRET::DB_recuperer_profs_jointure_rubrique( $rubrique_type , $rubrique_join , $matiere_id , NULL );
  $tab_meilleure_suggestion = empty($tab_meilleure_suggestion) ? array() : array_keys($tab_meilleure_suggestion);
  $tab_autres_propositions  = empty($tab_autres_propositions)  ? array() : array_keys($tab_autres_propositions);
  $tab_autres_propositions = array_values(array_diff( $tab_autres_propositions , $tab_meilleure_suggestion ));
  Json::add_row( 'script' , 'tab_meilleure_suggestion='.json_encode($tab_meilleure_suggestion).';' );
  Json::add_row( 'script' , 'tab_autres_propositions=' .json_encode($tab_autres_propositions).';' );
  Json::end( TRUE );
}

else if( ($select=='profs_classe') && $groupe_id )
{
  $tab_meilleure_suggestion = DB_STRUCTURE_LIVRET::DB_recuperer_profs_classe( $groupe_id );
  $tab_meilleure_suggestion = empty($tab_meilleure_suggestion) ? array() : array_keys($tab_meilleure_suggestion);
  Json::add_row( 'script' , 'tab_meilleure_suggestion='.json_encode($tab_meilleure_suggestion).';' );
  Json::end( TRUE );
}

else if( ($select=='groupes') && $page_ref )
{
  if($only_groupes_id)
  {
    $tab_id = explode(',',$only_groupes_id);
    $tab_id = Clean::map('entier',$tab_id);
    $tab_id = array_filter($tab_id,'positif');
    $only_groupes_id = implode(',',$tab_id);
  }
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_OPT_groupes_for_page( $page_ref , $only_groupes_id );
  $selection = $groupe_id;
}

/*
else if( ($select=='matieres') && $groupe_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_groupe( $groupe_id );
  $selection = $matiere_id;
}
else if( ($select=='profs_matiere') && $groupe_id && $matiere_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe_matiere( $groupe_id , $matiere_id );
  $selection = $prof_id;
}
*/


else if( ($select=='profs') && $groupe_id )
{
  if(!$is_all)
  {
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe_matiere( $groupe_id );
  }
  else
  {
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl('all');
  }
  $selection = $prof_id;
}

else
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Affichage du retour.

$option_first = ( is_string($DB_TAB) || (count($DB_TAB)>1) ) ? '' : FALSE ;

Json::end( TRUE , HtmlForm::afficher_select( $DB_TAB , FALSE /*select_nom*/ , $option_first , $selection , FALSE /*optgroup*/ , FALSE /*multiple*/ ) );

?>
