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

$module = (isset($_POST['f_module'])) ? Clean::texte($_POST['f_module']) : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifications
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!$module)
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','developpeur')))
{
  Json::end( FALSE , 'Profil incompatible avec cette fonctionnalité !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage d'un phpinfo d'un module
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_modules = InfoServeur::array_phpinfo(INFO_MODULES); 

if(!isset($tab_modules[$module]))
{
  Json::end( FALSE , 'Informations sur le module "'.html($module).'" non trouvées dans le phpinfo() !' );
}

Json::add_str('<table class="p"><thead>'.NL.'<tr><th colspan="3">Informations sur le module &laquo;&nbsp;'.html($module).'&nbsp;&raquo;</th></tr>'.NL.'</thead><tbody>'.NL);
foreach($tab_modules[$module] as $parametre_nom => $parametre_val)
{
  $colonnes = is_string($parametre_val) ? '<td colspan="2">'.chunk_split($parametre_val,128,'<br />') .'</td>' : '<td>local : '.html($parametre_val['local']).'</td><td>master : '.html($parametre_val['master']).'</td>' ;
  Json::add_str('<tr><td>'.html($parametre_nom).'</td>'.$colonnes.'</tr>'.NL);
}
Json::add_str('</tbody></table>'.NL);
Json::end( TRUE );

?>