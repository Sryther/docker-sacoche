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

// Fichier appelé par un service externe
// Une clef est transmise ; elle est comparée avec celle stockée dans un fichier du dossier /webservices/ donc non visible dans les sources / le svn.
// Passage en POST des paramètres.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// La classe Sesamail a besoin d'info pour définir son $default_sender.
require(CHEMIN_FICHIER_CONFIG_INSTALL);

// Fonctions
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// On récupère les paramètres ; on utilise REQUEST car selon les services les données sont reçues en POST en ou GET.
$WS_qui  = (isset($_REQUEST['qui']))  ? Clean::texte($_REQUEST['qui']) : '' ;
$WS_cle  = (isset($_REQUEST['cle']))  ? Clean::texte($_REQUEST['cle']) : '' ;
$WS_uai  = (isset($_REQUEST['uai']))  ? Clean::uai($_REQUEST['uai'])   : '' ;
$WS_uid  = (isset($_REQUEST['uid']))  ? Clean::texte($_REQUEST['uid']) : '' ;
$WS_data = (isset($_REQUEST['data'])) ? $_REQUEST['data']              : '' ; // tableau sérialisé

/**
 * Cas d'un service externe récupérant les données d'un user authentifié sur SACoche.
 * C'est un webservice un peu particulier qui ne requiert pas d'autre fichier de code dans CHEMIN_DOSSIER_WEBSERVICES.
 * Il y a en revanche quelques lignes de code associées dans le fichier /index.php
 */
if($WS_qui=='AutoMaths')
{
  
  $WS_cle = Clean::param_chemin($WS_cle);
  if(!$WS_cle)
  {
    exit('Erreur : clef non transmise !');
  }
  $fichier = CHEMIN_DOSSIER_LOGINPASS.$WS_cle.'.txt';
  if(!is_file($fichier))
  {
    exit('Erreur : absence de données associées à cette clef !');
  }
  $infos_user = file_get_contents($fichier);
  FileSystem::supprimer_fichier($fichier);
  exit($infos_user);
}

/**
 * Place aux autres webservices appelés depuis l'extérieur.
 * On ne vérifie dans un premier temps que le 1er paramètre (le service web prendra éventuellement en charge la suite).
 */
$tab_ws = array('argos_parent','argos_ajout','Laclasse-provisionning');
if(!in_array($WS_qui,$tab_ws))
{
  exit('Erreur : nom du service web manquant / incorrect ou service non autorisé !');
}
$fichier = CHEMIN_DOSSIER_WEBSERVICES.$WS_qui.'.php';
if(!is_file($fichier))
{
  exit('Erreur : le service web "'.$WS_qui.'" n\'est pas disponible sur cette installation !');
}
require($fichier);

?>