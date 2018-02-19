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

$action      = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])           : '';
$user_id     = (isset($_POST['f_id']))          ? Clean::entier($_POST['f_id'])              : 0;
$ligne1      = (isset($_POST['f_ligne1']))      ? Clean::adresse($_POST['f_ligne1'])         : '';
$ligne2      = (isset($_POST['f_ligne2']))      ? Clean::adresse($_POST['f_ligne2'])         : '';
$ligne3      = (isset($_POST['f_ligne3']))      ? Clean::adresse($_POST['f_ligne3'])         : '';
$ligne4      = (isset($_POST['f_ligne4']))      ? Clean::adresse($_POST['f_ligne4'])         : '';
$code_postal = (isset($_POST['f_code_postal'])) ? Clean::codepostal($_POST['f_code_postal']) : '';
$commune     = (isset($_POST['f_commune']))     ? Clean::commune($_POST['f_commune'])        : '';
$pays        = (isset($_POST['f_pays']))        ? Clean::pays($_POST['f_pays'])              : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle adresse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $user_id )
{
  // Insérer l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_adresse_parent( $user_id , array($ligne1,$ligne2,$ligne3,$ligne4,$code_postal,$commune,$pays) );
  // Afficher le retour
  Json::add_str('<td><span>'.html($ligne1).'</span> ; <span>'.html($ligne2).'</span> ; <span>'.html($ligne3).'</span> ; <span>'.html($ligne4).'</span></td>');
  Json::add_str('<td>'.html($code_postal).'</td>');
  Json::add_str('<td>'.html($commune).'</td>');
  Json::add_str('<td>'.html($pays).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier ce parent."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une adresse existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $user_id )
{
  // Insérer l'enregistrement
  $user_id = DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_adresse_parent( $user_id , array($ligne1,$ligne2,$ligne3,$ligne4,$code_postal,$commune,$pays) );
  // Afficher le retour
  Json::add_str('<td><span>'.html($ligne1).'</span> ; <span>'.html($ligne2).'</span> ; <span>'.html($ligne3).'</span> ; <span>'.html($ligne4).'</span></td>');
  Json::add_str('<td>'.html($code_postal).'</td>');
  Json::add_str('<td>'.html($commune).'</td>');
  Json::add_str('<td>'.html($pays).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier ce parent."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
