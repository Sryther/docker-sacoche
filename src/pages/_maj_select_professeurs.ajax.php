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

$groupe_type  = (isset($_POST['f_groupe_type']))  ? Clean::lettres($_POST['f_groupe_type']) : 'd'; // d n c g
$groupe_id    = (isset($_POST['f_groupe_id']))    ? Clean::entier($_POST['f_groupe_id'])    : 0;
$selection    = (empty($_POST['f_selection']))    ? FALSE                                   : ( (ctype_digit($_POST['f_selection'])) ? $_POST['f_selection'] : TRUE ) ;
$option_first = (empty($_POST['f_option_first'])) ? FALSE                                   : '' ;
$multiple     = (empty($_POST['f_multiple']))     ? FALSE                                   : TRUE ;

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe');

if( ($groupe_id) && (!isset($tab_types[$groupe_type])) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Autres valeurs à récupérer ou à définir.

$select_nom   = ($multiple) ? 'f_user' : FALSE ;
$option_first = ($multiple) ? FALSE    : ''    ;

// Affichage du retour.

Json::end( TRUE , HtmlForm::afficher_select( DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl($tab_types[$groupe_type],$groupe_id) , $select_nom , $option_first , $selection , '' /*optgroup*/ , $multiple ) );
?>
