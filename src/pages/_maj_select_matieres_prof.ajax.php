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

// Mettre à jour l'élément de formulaire "f_matiere"

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$action   = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])   : ''; // ajouter | retirer
$matiere  = (isset($_POST['f_matiere']))  ? Clean::entier($_POST['f_matiere']) : 0;
$multiple = (empty($_POST['f_multiple'])) ? FALSE                              : TRUE ;

// Autres valeurs à récupérer ou à définir.

$select_nom   = ($multiple) ? 'f_matieres' : FALSE ;
$option_first = ($multiple) ? FALSE        : ''    ;
$selection    = ($multiple) ? TRUE         : $matiere ;

$tab_matieres = ($action=='ajouter') ? DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl() : DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']) ;

// Affichage du retour.

Json::end( TRUE , HtmlForm::afficher_select( $tab_matieres , $select_nom , $option_first , $selection , '' /*optgroup*/ , $multiple ) );

?>
