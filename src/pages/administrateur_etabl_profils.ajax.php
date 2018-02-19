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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';

$tab_profils_actifs = (isset($_POST['tab_id'])) ? Clean::map('texte',explode(',',$_POST['tab_id'])) : array() ;

$tab_profils          = array_keys($_SESSION['TAB_PROFILS_ADMIN']['TYPE']);
$tab_profils_inactifs = array_diff( $tab_profils , $tab_profils_actifs );
$tab_profils_anormaux = array_diff( $tab_profils_actifs , $tab_profils );

if( count( $tab_profils_anormaux ) )
{
  Json::end( FALSE , 'Erreur avec les profils transmis !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix des profils
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='choix_profils')
{
  // Il n'y a que quelques profils : on ne s'embête pas à comparer pour voir ce qui a changé, on effectue un update pour chacun.
  foreach($tab_profils_actifs as $profil_sigle)
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre($profil_sigle,'user_profil_actif',1);
  }
  foreach($tab_profils_inactifs as $profil_sigle)
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre($profil_sigle,'user_profil_actif',0);
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
