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

// Fichier appelé pour chaque appel ajax.
// Passage en GET des paramètres pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Détermination du CHARSET d'en-tête
/*
$test_xml = (strpos($_SERVER['HTTP_ACCEPT'],'/xml')) ? TRUE : FALSE;
$test_upload = ( (isset($_SERVER['CONTENT_TYPE'])) &&(strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==FALSE) ) ? TRUE : FALSE; // L'upload d'un fichier XML change le HTTP_ACCEPT, d'où ce second test
$format = ( $test_xml && !$test_upload ) ? 'text/xml' : 'text/html' ;
header('Content-Type: '.$format.'; charset=utf-8');
*/
header('Content-Type: text/html; charset=utf-8');

// Page appelée
if(!isset($_GET['page']))
{
  exit_error( 'Référence manquante' /*titre*/ , 'Référence de page manquante (le paramètre "page" n\'a pas été transmis en GET).' /*contenu*/ );
}
$PAGE = Clean::fichier($_GET['page']);

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
  require(CHEMIN_FICHIER_CONFIG_INSTALL);
}
elseif($PAGE!='public_installation')
{
  exit_error( 'Informations hébergement manquantes' /*titre*/ , 'Les informations relatives à l\'hébergeur n\'ont pas été trouvées.<br />C\'est probablement votre première installation de SACoche, ou bien le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).'" a été supprimé.<br />Cliquer sur le lien ci-dessous.' /*contenu*/ , 'install' /*lien*/ );
}

// Le fait de lister les droits d'accès de chaque page empêche de surcroit l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
if(!Session::recuperer_droit_acces($PAGE))
{
  exit_error( 'Droits manquants' /*titre*/ , 'Droits de la page "'.$PAGE.'" manquants.<br />Soit le paramètre "page" transmis en GET est incorrect, soit les droits de cette page n\'ont pas été attribués dans le fichier "'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php').'".' /*contenu*/ );
}

// Ouverture de la session et gestion des droits d'accès
Session::execute();

// Infos DEBUG dans FirePHP
if (DEBUG>3) afficher_infos_debug_FirePHP();

// Arrêt s'il fallait seulement mettre la session à jour (la session d'un user connecté n'a pas été perdue si on arrive jusqu'ici)
if($PAGE=='conserver_session_active')
{
  Json::end( TRUE );
}

// Arrêt s'il fallait seulement fermer la session
if($PAGE=='fermer_session')
{
  Session::close(FALSE); // FALSE car il est arrivé que des appels ajax se croisent...
  Json::end( TRUE );
}

// Traductions
if($_SESSION['USER_PROFIL_TYPE']!='public')
{
  Lang::setlocale( LC_MESSAGES, Lang::get_locale_used() );
  Lang::bindtextdomain( LOCALE_DOMAINE, LOCALE_DIR );
  Lang::bind_textdomain_codeset( LOCALE_DOMAINE, LOCALE_CHARSET );
  Lang::textdomain( LOCALE_DOMAINE );
}

// Blocage éventuel par le webmestre ou un administrateur ou l'automate (on ne peut pas le tester avant car il faut avoir récupéré les données de session)
LockAcces::stopper_si_blocage( $_SESSION['BASE'] , FALSE /*demande_connexion_profil*/ );

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// Logs d'infos au cas où un trop grand nombre de variables seraient postées (par défaut max_input_vars est configuré dans PHP à 1000, et en cas de dépassement les logs indiquent juste "in Unknown on line 0").
if(count($_POST)>999)
{
  Outil::ajouter_log_PHP( 'Trop de variables postées' /*log_objet*/ , 'Page '.$PAGE /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
}

// Jeton CSRF
Session::verifier_jeton_anti_CSRF($PAGE);

// MAJ fichier de config hébergement si besoin
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
  require(CHEMIN_DOSSIER_INCLUDE.'maj_fichier_constantes_hebergement.php');
}

// Interface de connexion à la base, chargement et config (test sur CHEMIN_FICHIER_CONFIG_INSTALL car à éviter si procédure d'installation non terminée).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
  // Choix des paramètres de connexion à la base de données adaptée...
  // ...multi-structures ; base sacoche_structure_*** (si connecté sur un établissement)
  if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($_SESSION['BASE']>0) )
  {
    $fichier_mysql_config = 'serveur_sacoche_structure_'.$_SESSION['BASE'];
    $fichier_class_config = 'class.DB.config.sacoche_structure';
  }
  // ...multi-structures ; base sacoche_webmestre (si non connecté ou connecté comme webmestre)
  elseif(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    $fichier_mysql_config = 'serveur_sacoche_webmestre';
    $fichier_class_config = 'class.DB.config.sacoche_webmestre';
  }
  // ...mono-structure ; base sacoche_structure
  elseif(HEBERGEUR_INSTALLATION=='mono-structure')
  {
    $fichier_mysql_config = 'serveur_sacoche_structure';
    $fichier_class_config = 'class.DB.config.sacoche_structure';
  }
  else
  {
    exit_error( 'Configuration anormale' /*titre*/ , 'Une anomalie dans les données d\'hébergement empêche l\'application de se poursuivre.<br />HEBERGEUR_INSTALLATION vaut '.HEBERGEUR_INSTALLATION /*contenu*/ );
  }
  // Chargement du fichier de connexion à la BDD
  define('CHEMIN_FICHIER_CONFIG_MYSQL',CHEMIN_DOSSIER_MYSQL.$fichier_mysql_config.'.php');
  if(is_file(CHEMIN_FICHIER_CONFIG_MYSQL))
  {
    require(CHEMIN_FICHIER_CONFIG_MYSQL);
    require(CHEMIN_DOSSIER_INCLUDE.$fichier_class_config.'.php');
  }
  elseif($PAGE!='public_installation')
  {
    exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.<br />C\'est probablement votre première installation de SACoche, ou bien le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_MYSQL).'" a été supprimé.<br />Cliquer sur le lien ci-dessous.' /*contenu*/ , 'install' /*lien*/ );
  }
}

// Lancer une mise à jour complémentaire de la base par morceaux
if($PAGE=='maj_base_complementaire')
{
  DB_STRUCTURE_MAJ_BASE::DB_maj_base_complement();
  $retour = ($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']) ? 'encore' : 'terminé' ;
  Json::end( TRUE , $retour );
}

// Chargement de la page concernée
$filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.ajax.php';
if(is_file($filename_php))
{
  require($filename_php);
}
else
{
  exit_error( 'Page manquante' /*titre*/ , 'La page "'.$filename_php.'" n\'a pas été trouvée.' /*contenu*/ );
}
?>
