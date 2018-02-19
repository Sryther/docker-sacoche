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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action        = (isset($_POST['f_action']))        ? Clean::texte($_POST['f_action'])     : '';
$rubrique_type = (isset($_POST['f_rubrique_type'])) ? Clean::id($_POST['f_rubrique_type']) : '';
$rubrique_join = (isset($_POST['f_rubrique_join'])) ? Clean::id($_POST['f_rubrique_join']) : '';
$rubrique_id   = (isset($_POST['f_rubrique']))      ? Clean::entier($_POST['f_rubrique'])  : 0;
$element_id    = (isset($_POST['f_element']))       ? Clean::entier($_POST['f_element'])   : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_element = (isset($_POST['f_elements'])) ? ( (is_array($_POST['f_elements'])) ? $_POST['f_elements'] : explode(',',$_POST['f_elements']) ) : array() ;
$tab_element = array_filter( Clean::map('entier',$tab_element) , 'positif' );

$tab_type = array( 'c1_theme' , 'c2_domaine' , 'c3_domaine' , 'c3_matiere' , 'c4_matiere' );

$tab_join = array(
  'item'    => "liaisons aux items",
  'theme'   => "liaisons aux thèmes",
  'domaine' => "liaisons aux domaines",
  'matiere' => "liaisons aux matières",
  'user'    => "liaisons aux enseignants",
);

if( !$rubrique_type || !$rubrique_join || !in_array($rubrique_type,$tab_type) || !isset($tab_join[$rubrique_join]) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter de nouvelles jointures
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $rubrique_id && DB_STRUCTURE_LIVRET::DB_tester_page_jointure_rubrique( $rubrique_type , $rubrique_join , $rubrique_id ) && !empty($tab_element) )
{
  // Insérer les jointures
  foreach($tab_element as $element_id)
  {
    DB_STRUCTURE_LIVRET::DB_modifier_jointure_référentiel( $rubrique_type , $rubrique_id , $element_id , TRUE );
  }
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer une jointure existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='retirer') && $rubrique_id && DB_STRUCTURE_LIVRET::DB_tester_page_jointure_rubrique( $rubrique_type , $rubrique_join , $rubrique_id ) && $element_id )
{
  // Retirer la jointure
  DB_STRUCTURE_LIVRET::DB_modifier_jointure_référentiel( $rubrique_type , $rubrique_id , $element_id , FALSE );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le type de liaison aux référentiels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='choisir_type_liaison') )
{
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_LIVRET::DB_modifier_type_jointure( $rubrique_type , $rubrique_join );
  // Retirer les jointures
  DB_STRUCTURE_LIVRET::DB_supprimer_jointures_référentiel( $rubrique_type );
  // Log de l'action
  SACocheLog::ajouter('Modification du type de liaison Livret Scolaire / Référentiel ('.$rubrique_type.' / '.$rubrique_join.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' modifie le type de liaison Livret Scolaire / Référentiel ('.$rubrique_type.' / '.$rubrique_join.').'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
