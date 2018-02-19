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

// ============================================================================
// Premières constantes
// ============================================================================

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé
// Dès maintenant car utilisé par exit_error().
// Tous les fichiers étant en UTF-8, et le code prévu pour manipuler des données en UTF-8, changer le CHARSET serait assez hasardeux pour ne pas dire risqué...
define('CHARSET','utf-8');
define('NL',PHP_EOL);

// Constante "SACoche" : atteste l'appel de ce fichier avant inclusion d'une autre page, et permet de connaître le nom du script initial.
if(defined('PATHINFO_FILENAME'))
{
  define( 'SACoche', pathinfo( $_SERVER['SCRIPT_NAME'] , PATHINFO_FILENAME ) );
}
else
{
  // Pour eviter une erreur fatale d'entrée, le paramètre PATHINFO_FILENAME de la fonction pathinfo() n'étant disponible que depuis PHP 5.2.
  define( 'SACoche', substr( pathinfo( $_SERVER['SCRIPT_NAME'] , PATHINFO_BASENAME ) , 0 , - strlen( pathinfo( $_SERVER['SCRIPT_NAME'] , PATHINFO_EXTENSION ) ) - 1 ) );
}
if(SACoche=='_loader') {exit('Ce fichier ne peut être appelé directement !');}

// ============================================================================
// Versions PHP & MySQL requises et conseillées
// ============================================================================

// @see commentaire() in ./_inc/class.InfoServeur.php
// Attention : ne pas mettre de ".0" (par exemple "5.0") car version_compare() considère que 5 < 5.0 (@see http://fr.php.net/version_compare)
define('PHP_VERSION_MINI_PRE_REQUISE' ,'5.1'); // PHP 5.1 et PHP 5.2 tolérées jusqu'en avril 2016, mais cela devenait trop lourd à gérer.
define('PHP_VERSION_MINI_REQUISE'     ,'5.3');
define('PHP_VERSION_MINI_CONSEILLEE'  ,'5.4');
define('MYSQL_VERSION_MINI_REQUISE'   ,'5.1');
define('MYSQL_VERSION_MINI_CONSEILLEE','5.5');

// Vérifier la version de PHP
if(version_compare(PHP_VERSION,PHP_VERSION_MINI_REQUISE,'<'))
{
  // Il se peut que la version indiquée soit plus récente que ce qui est indiquée ; un test de présence d'éléments nouveaux utilisés évite de se faire leurrer.
  if( version_compare(PHP_VERSION,PHP_VERSION_MINI_PRE_REQUISE,'<') || !function_exists('array_fill_keys') || !function_exists('error_get_last') || !function_exists('json_decode') || !function_exists('json_encode') || !function_exists('str_getcsv') || !defined('PATHINFO_FILENAME') )
  {
    exit_error( 'PHP trop ancien' /*titre*/ , 'Version de PHP utilisée sur ce serveur : '.PHP_VERSION.'<br />Version de PHP requise au minimum : '.PHP_VERSION_MINI_REQUISE /*contenu*/ );
  }
}

// ============================================================================
// Modules PHP requis
// ============================================================================

define('PHP_LISTE_EXTENSIONS' , ' curl , dom , gd , json , mbstring , PDO , pdo_mysql , session , SPL , zip , zlib '); // respecter le séparateur " , "

// Vérifier la présence des modules nécessaires
$tab_extensions_chargees = get_loaded_extensions();
$tab_extensions_requises = explode( ' , ' , trim(PHP_LISTE_EXTENSIONS) );
$tab_extensions_manquantes = array_diff( $tab_extensions_requises , $tab_extensions_chargees );
if(count($tab_extensions_manquantes))
{
  exit_error( 'PHP incomplet' /*titre*/ , 'Module(s) PHP manquant(s) : <b>'.implode($tab_extensions_manquantes,' ; ').'</b>.<br />Ce serveur n\'a pas la configuration minimale requise.' /*contenu*/ );
}
// Rmq : Un souci a été rencontré sur un serveur FreeBSD : fonction json_encode() indéfinie alors qu'un phpinfo() du PHP 5.6.11 indiquait json chargé !
// Il a fallu explicitement installer le paquet "php56-json" existant dans les dépôts de FreeBSD...
// @see http://php.net/manual/fr/json.installation.php

// ============================================================================
// Configuration PHP
// ============================================================================

// Définir le décalage horaire par défaut de toutes les fonctions date/heure
// La fonction date_default_timezone_set() est disponible depuis PHP 5.1 ; on a été stoppé avant si ce n'est pas le cas.
date_default_timezone_set('Europe/Paris'); 

// Modifier l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
// Requiert le module "mbstring" ; on a été stoppé avant si ce dernier est manquant.
mb_internal_encoding(CHARSET);

// Remédier à l'éventuelle configuration de magic_quotes_gpc à On (directive obsolète depuis PHP 5.3.0 et supprimée en PHP 5.4.0).
// Cette remédiation n'est toutefois que partielle car des antislashs sont aussi ajoutés à tout ce qui vient "de l'extérieur", donc file_get_contents() & Co.
// array_map() génère une erreur si le tableau contient lui-même un tableau ; à la place on peut utiliser array_walk_recursive() ou la fonction ci-dessous présente dans le code de MySQL_Dumper et PunBB) :
// function stripslashes_array($val){$val = is_array($val) ? array_map('stripslashes_array',$val) : stripslashes($val);return $val;}
if(get_magic_quotes_gpc())
{
  function tab_stripslashes(&$val,$key)
  {
    $val = stripslashes($val);
  }
  array_walk_recursive($_COOKIE ,'tab_stripslashes');
  array_walk_recursive($_GET    ,'tab_stripslashes');
  array_walk_recursive($_POST   ,'tab_stripslashes');
  array_walk_recursive($_REQUEST,'tab_stripslashes');
}

// ============================================================================
// Chemins dans le système de fichiers du serveur (pour des manipulations de fichiers locaux)
// ============================================================================

// Vers le dossier d'installation de l'application SACoche, avec séparateur final.
define('DS',DIRECTORY_SEPARATOR);
define('CHEMIN_DOSSIER_SACOCHE'  , realpath(dirname(dirname(__FILE__))).DS);
define('LONGUEUR_CHEMIN_SACOCHE' , strlen(CHEMIN_DOSSIER_SACOCHE)-1);

if(defined('APPEL_SITE_PROJET'))
{
  define('CHEMIN_DOSSIER_PROJET'         , realpath(dirname(dirname(dirname(__FILE__)))).DS);
  define('LONGUEUR_CHEMIN_PROJET'        , strlen(CHEMIN_DOSSIER_PROJET)-1);
  define('CHEMIN_DOSSIER_PROJET_INCLUDE' , CHEMIN_DOSSIER_PROJET.'_inc'.DS);
}

// Vers des sous-dossiers, avec séparateur final.
define('CHEMIN_DOSSIER_PRIVATE'       , CHEMIN_DOSSIER_SACOCHE.'__private'.DS);
define('CHEMIN_DOSSIER_TMP'           , CHEMIN_DOSSIER_SACOCHE.'__tmp'.DS);
define('CHEMIN_DOSSIER_IMG'           , CHEMIN_DOSSIER_SACOCHE.'_img'.DS);
define('CHEMIN_DOSSIER_INCLUDE'       , CHEMIN_DOSSIER_SACOCHE.'_inc'.DS);
define('CHEMIN_DOSSIER_FPDF_FONT'     , CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'FPDF'.DS.'font'.DS);
define('CHEMIN_DOSSIER_SQL'           , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS);
define('CHEMIN_DOSSIER_SQL_STRUCTURE' , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS.'structure'.DS);
define('CHEMIN_DOSSIER_SQL_WEBMESTRE' , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS.'webmestre'.DS);
define('CHEMIN_DOSSIER_XSD'           , CHEMIN_DOSSIER_SACOCHE.'_xsd'.DS);
define('CHEMIN_DOSSIER_MENUS'         , CHEMIN_DOSSIER_SACOCHE.'menus'.DS);
define('CHEMIN_DOSSIER_PAGES'         , CHEMIN_DOSSIER_SACOCHE.'pages'.DS);
define('CHEMIN_DOSSIER_WEBSERVICES'   , CHEMIN_DOSSIER_SACOCHE.'webservices'.DS);
define('CHEMIN_DOSSIER_CONFIG'        , CHEMIN_DOSSIER_PRIVATE.'config'.DS);
define('CHEMIN_DOSSIER_LOG'           , CHEMIN_DOSSIER_PRIVATE.'log'.DS);
define('CHEMIN_DOSSIER_MYSQL'         , CHEMIN_DOSSIER_PRIVATE.'mysql'.DS);
define('CHEMIN_DOSSIER_BADGE'         , CHEMIN_DOSSIER_TMP.'badge'.DS);
define('CHEMIN_DOSSIER_COOKIE'        , CHEMIN_DOSSIER_TMP.'cookie'.DS);
define('CHEMIN_DOSSIER_DEVOIR'        , CHEMIN_DOSSIER_TMP.'devoir'.DS);
define('CHEMIN_DOSSIER_DUMP'          , CHEMIN_DOSSIER_TMP.'dump-base'.DS);
define('CHEMIN_DOSSIER_EXPORT'        , CHEMIN_DOSSIER_TMP.'export'.DS);
define('CHEMIN_DOSSIER_IMPORT'        , CHEMIN_DOSSIER_TMP.'import'.DS);
define('CHEMIN_DOSSIER_LOGINPASS'     , CHEMIN_DOSSIER_TMP.'login-mdp'.DS);
define('CHEMIN_DOSSIER_LOGO'          , CHEMIN_DOSSIER_TMP.'logo'.DS);
define('CHEMIN_DOSSIER_OFFICIEL'      , CHEMIN_DOSSIER_TMP.'officiel'.DS);
define('CHEMIN_DOSSIER_PARTENARIAT'   , CHEMIN_DOSSIER_TMP.'partenariat'.DS);
define('CHEMIN_DOSSIER_RSS'           , CHEMIN_DOSSIER_TMP.'rss'.DS);
define('CHEMIN_DOSSIER_SYMBOLE'       , CHEMIN_DOSSIER_TMP.'symbole'.DS);
//      CHEMIN_FICHIER_CONFIG_MYSQL     est défini dans index.php ou ajax.php, en fonction du type d'installation et d'utilisateur connecté
define('FPDF_FONTPATH'                , CHEMIN_DOSSIER_FPDF_FONT); // Pour FPDF (répertoire où se situent les polices)

// Vers des fichiers.
define('CHEMIN_FICHIER_CONFIG_INSTALL'       , CHEMIN_DOSSIER_CONFIG.'constantes.php');
define('CHEMIN_FICHIER_DEBUG_CONFIG'         , CHEMIN_DOSSIER_TMP.'debug.txt');
define('CHEMIN_FICHIER_CA_CERTS_FILE'        , CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'phpCAS'.DS.'certificats'.DS.'certificats.txt');
define('CHEMIN_FICHIER_WS_ARGOS'             , CHEMIN_DOSSIER_WEBSERVICES.'argos_import.php');
define('CHEMIN_FICHIER_WS_ENTLIBRE_ESSONNE'  , CHEMIN_DOSSIER_WEBSERVICES.'EntLibre_RecupId_Essonne.php');
define('CHEMIN_FICHIER_WS_ENTLIBRE_PICARDIE' , CHEMIN_DOSSIER_WEBSERVICES.'EntLibre_RecupId_PicardieLeo.php');
define('CHEMIN_FICHIER_WS_ENTLIBRE_TEST'     , CHEMIN_DOSSIER_WEBSERVICES.'EntLibre_RecupId_ServeurTest.php');
define('CHEMIN_FICHIER_WS_LACLASSE'          , CHEMIN_DOSSIER_WEBSERVICES.'Laclasse-recup_id_ent.php');
define('CHEMIN_FICHIER_WS_LCS'               , CHEMIN_DOSSIER_WEBSERVICES.'import_lcs.php');
define('CHEMIN_FICHIER_WS_SESAMATH_ENT'      , CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_conventions.php');

// ============================================================================
// Constantes de DEBUG
// ============================================================================

/**
 *   1 => afficher toutes les erreurs PHP
 *   2 => enregistrer les dialogues avec le serveur CAS (dans le fichier de log phpCAS)
 *   4 => résultats des requêtes SQL (dans la console Firefox avec FirePHP)
 *   8 => valeurs de $_SESSION       (dans la console Firefox avec FirePHP)
 *  16 => valeurs de $_POST          (dans la console Firefox avec FirePHP)
 *  32 => valeurs de $_GET           (dans la console Firefox avec FirePHP)
 *  64 => valeurs de $_FILES         (dans la console Firefox avec FirePHP)
 * 128 => valeurs de $_COOKIE        (dans la console Firefox avec FirePHP)
 * 256 => valeurs de $_SERVER        (dans la console Firefox avec FirePHP)
 * 512 => valeurs des constantes PHP (dans la console Firefox avec FirePHP)
 *
 * On fonctionne comme pour les droits unix wrx, en testant chaque bit en partant de la droite.
 * Pour choisir les infos voulues, il faut mettre en DEBUG la somme des valeurs correspondantes.
 * Par exemple 7 = 4 + 2 + 1 = requêtes SQL + logs CAS + erreurs PHP
 * La valeur minimale est 0 (pas de debug) et la valeur maximale est 1023.
 */

// Dans un fichier texte du dossier TMP pour ne pas être dans la distribution ni écrasé par les maj et être accessible en écriture, en particulier par l'espace webmestre.
define('DEBUG', !is_file(CHEMIN_FICHIER_DEBUG_CONFIG) ? 0 : (int)file_get_contents(CHEMIN_FICHIER_DEBUG_CONFIG) );

// Les lignes suivantes ne doivent pas être modifiées : pour changer le niveau de debug modifier seulement le fichier alimentant la constante DEBUG
define('DEBUG_PHP',     DEBUG &   1 ? TRUE : FALSE );
define('DEBUG_PHPCAS',  DEBUG &   2 ? TRUE : FALSE );
define('DEBUG_SQL',     DEBUG &   4 ? TRUE : FALSE );
define('DEBUG_SESSION', DEBUG &   8 ? TRUE : FALSE );
define('DEBUG_POST',    DEBUG &  16 ? TRUE : FALSE );
define('DEBUG_GET',     DEBUG &  32 ? TRUE : FALSE );
define('DEBUG_FILES',   DEBUG &  64 ? TRUE : FALSE );
define('DEBUG_COOKIE',  DEBUG & 128 ? TRUE : FALSE );
define('DEBUG_SERVER',  DEBUG & 256 ? TRUE : FALSE );
define('DEBUG_CONST',   DEBUG & 512 ? TRUE : FALSE );

// ============================================================================
// DEBUG - Fixer le niveau de rapport d'erreurs PHP
// ============================================================================

if(DEBUG_PHP)
{
  // Rapporter toutes les erreurs PHP (http://fr.php.net/manual/fr/errorfunc.constants.php)
  ini_set('error_reporting', E_ALL | E_STRICT);
}
else
{
  // Rapporter seulement les erreurs les plus importantes (configuration par défaut de php.ini : tout sauf E_NOTICE + E_STRICT qui est englobé dans E_ALL à compter de PHP 5.4).
  ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE); // ex E_ALL & ~E_STRICT & ~E_NOTICE
}

// ============================================================================
// Si appel depuis /_img/php/etiquette.php alors on n'a pas besoin d'aller plus loin dans ce fichier inclus.
// ============================================================================

if(SACoche=='etiquette') return;

// ============================================================================
// Auto-chargement des classes
// ============================================================================

/**
 * Auto-chargement des classes (aucune inclusion de classe n'est nécessaire, elles sont chargées suivant les besoins).
 * 
 * @param string   $class_name   nom de la classe
 * @return void
 */
function SACoche_autoload($class_name)
{
  $tab_classes_appli = array(
    'DB'                           => '_lib'.DS.'DB'.DS.'DB.class.php' ,
    'FirePHP'                      => '_lib'.DS.'FirePHPCore'.DS.'FirePHP.class.php' ,
    'FPDF'                         => '_lib'.DS.'FPDF'.DS.'fpdf.php' ,
    'PDF_Label'                    => '_lib'.DS.'FPDF'.DS.'PDF_Label.php' ,
    'FPDI'                         => '_lib'.DS.'FPDI'.DS.'fpdi.php' ,
    'PDFMerger'                    => '_lib'.DS.'FPDI'.DS.'PDFMerger.php' ,
    'Lang'                         => '_lib'.DS.'gettext'.DS.'Lang.class.php' ,
    'phpCAS'                       => '_lib'.DS.'phpCAS'.DS.'CAS.php' ,
    // Pour SimpleSAMLphp c'est plus compliqué, on fait un include directement dans les 2 fichiers concernés...
    'Browser'                      => '_inc'.DS.'class.Browser.php' ,
    'Clean'                        => '_inc'.DS.'class.Clean.php' ,
    'Cookie'                       => '_inc'.DS.'class.Cookie.php' ,
    'cssmin'                       => '_inc'.DS.'class.CssMinified.php' ,
    'cURL'                         => '_inc'.DS.'class.cURL.php' ,
    'DBextra'                      => '_inc'.DS.'class.DBextra.php' ,
    'Erreur500'                    => '_inc'.DS.'class.Erreur500.php' ,
    'FileSystem'                   => '_inc'.DS.'class.FileSystem.php' ,
    'Form'                         => '_inc'.DS.'class.Form.php' ,
    'Html'                         => '_inc'.DS.'class.Html.php' ,
    'HtmlArborescence'             => '_inc'.DS.'class.HtmlArborescence.php' ,
    'HtmlForm'                     => '_inc'.DS.'class.HtmlForm.php' ,
    'HtmlMail'                     => '_inc'.DS.'class.HtmlMail.php' ,
    'Image'                        => '_inc'.DS.'class.Image.php' ,
    'InfoServeur'                  => '_inc'.DS.'class.InfoServeur.php' ,
    'JSqueeze'                     => '_inc'.DS.'class.JSqueeze.php' ,
    'Json'                         => '_inc'.DS.'class.Json.php' ,
    'Layout'                       => '_inc'.DS.'class.Layout.php' ,
    'LockAcces'                    => '_inc'.DS.'class.LockAcces.php' ,
    'Math'                         => '_inc'.DS.'class.Math.php' ,
    'Mobile_Detect'                => '_inc'.DS.'class.MobileDetect.php' ,
    'MyDOMDocument'                => '_inc'.DS.'class.domdocument.php' ,
    'Outil'                        => '_inc'.DS.'class.Outil.php' ,
    'OutilBilan'                   => '_inc'.DS.'class.OutilBilan.php' ,
    'PDF'                          => '_inc'.DS.'class.PDF.php' ,
    'PDF_archivage_tableau'        => '_inc'.DS.'class.PDF_archivage_tableau.php' ,
    'PDF_evaluation_cartouche'     => '_inc'.DS.'class.PDF_evaluation_cartouche.php' ,
    'PDF_evaluation_commentaires'  => '_inc'.DS.'class.PDF_evaluation_commentaires.php' ,
    'PDF_evaluation_tableau'       => '_inc'.DS.'class.PDF_evaluation_tableau.php' ,
    'PDF_grille_referentiel'       => '_inc'.DS.'class.PDF_grille_referentiel.php' ,
    'PDF_item_bulletin'            => '_inc'.DS.'class.PDF_item_bulletin.php' ,
    'PDF_item_releve'              => '_inc'.DS.'class.PDF_item_releve.php' ,
    'PDF_item_synthese'            => '_inc'.DS.'class.PDF_item_synthese.php' ,
    'PDF_item_tableau'             => '_inc'.DS.'class.PDF_item_tableau.php' ,
    'PDF_livret_scolaire'          => '_inc'.DS.'class.PDF_livret_scolaire.php' ,
    'PDF_socle2016_releve'         => '_inc'.DS.'class.PDF_socle2016_releve.php' ,
    'PDF_socle2016_synthese'       => '_inc'.DS.'class.PDF_socle2016_synthese.php' ,
    'PDF_trombinoscope'            => '_inc'.DS.'class.PDF_trombinoscope.php' ,
    'RSS'                          => '_inc'.DS.'class.RSS.php' ,
    'SACocheLog'                   => '_inc'.DS.'class.SACocheLog.php' ,
    'ServeurCommunautaire'         => '_inc'.DS.'class.ServeurCommunautaire.php' ,
    'Sesamail'                     => '_inc'.DS.'class.Sesamail.php' ,
    'Session'                      => '_inc'.DS.'class.Session.php' ,
    'SessionUser'                  => '_inc'.DS.'class.SessionUser.php' ,
    'To'                           => '_inc'.DS.'class.To.php' ,
    'Webmestre'                    => '_inc'.DS.'class.Webmestre.php' ,

    'DB_STRUCTURE_ADMINISTRATEUR'  => '_sql'.DS.'requetes_structure_administrateur.php' ,
    'DB_STRUCTURE_DIRECTEUR'       => '_sql'.DS.'requetes_structure_directeur.php' ,
    'DB_STRUCTURE_ELEVE'           => '_sql'.DS.'requetes_structure_eleve.php' ,
    'DB_STRUCTURE_PROFESSEUR'      => '_sql'.DS.'requetes_structure_professeur.php' ,
    'DB_STRUCTURE_PUBLIC'          => '_sql'.DS.'requetes_structure_public.php' ,
    'DB_STRUCTURE_WEBMESTRE'       => '_sql'.DS.'requetes_structure_webmestre.php' ,

    'DB_STRUCTURE_BILAN'           => '_sql'.DS.'requetes_structure_bilan.php' ,
    'DB_STRUCTURE_COMMENTAIRE'     => '_sql'.DS.'requetes_structure_commentaire.php' ,
    'DB_STRUCTURE_COMMUN'          => '_sql'.DS.'requetes_structure_commun.php' ,
    'DB_STRUCTURE_DEMANDE'         => '_sql'.DS.'requetes_structure_demande.php' ,
    'DB_STRUCTURE_IMAGE'           => '_sql'.DS.'requetes_structure_image.php' ,
    'DB_STRUCTURE_LIVRET'          => '_sql'.DS.'requetes_structure_livret.php' ,
    'DB_STRUCTURE_MAJ_BASE'        => '_sql'.DS.'requetes_structure_maj_base.php' ,
    'DB_STRUCTURE_MATIERE'         => '_sql'.DS.'requetes_structure_matiere.php' ,
    'DB_STRUCTURE_MESSAGE'         => '_sql'.DS.'requetes_structure_message.php' ,
    'DB_STRUCTURE_NIVEAU'          => '_sql'.DS.'requetes_structure_niveau.php' ,
    'DB_STRUCTURE_NOTIFICATION'    => '_sql'.DS.'requetes_structure_notification.php' ,
    'DB_STRUCTURE_OFFICIEL'        => '_sql'.DS.'requetes_structure_officiel.php' ,
    'DB_STRUCTURE_OFFICIEL_CONFIG' => '_sql'.DS.'requetes_structure_officiel_configuration.php' ,
    'DB_STRUCTURE_PARAMETRE'       => '_sql'.DS.'requetes_structure_parametre.php' ,
    'DB_STRUCTURE_PERIODE'         => '_sql'.DS.'requetes_structure_periode.php' ,
    'DB_STRUCTURE_REFERENTIEL'     => '_sql'.DS.'requetes_structure_referentiel.php' ,
    'DB_STRUCTURE_REGROUPEMENT'    => '_sql'.DS.'requetes_structure_regroupement.php' ,
    'DB_STRUCTURE_SELECTION_ITEM'  => '_sql'.DS.'requetes_structure_selection_item.php' ,
    'DB_STRUCTURE_SIECLE'          => '_sql'.DS.'requetes_structure_siecle.php' ,
    'DB_STRUCTURE_SWITCH'          => '_sql'.DS.'requetes_structure_switch.php' ,

    'DB_WEBMESTRE_ADMINISTRATEUR'  => '_sql'.DS.'requetes_webmestre_administrateur.php' ,
    'DB_WEBMESTRE_MAJ_BASE'        => '_sql'.DS.'requetes_webmestre_maj_base.php' ,
    'DB_WEBMESTRE_PARTENAIRE'      => '_sql'.DS.'requetes_webmestre_partenaire.php' ,
    'DB_WEBMESTRE_PUBLIC'          => '_sql'.DS.'requetes_webmestre_public.php' ,
    'DB_WEBMESTRE_SELECT'          => '_sql'.DS.'requetes_webmestre_select.php' ,
    'DB_WEBMESTRE_WEBMESTRE'       => '_sql'.DS.'requetes_webmestre_webmestre.php' ,
  );
  if( isset($tab_classes_appli[$class_name]) )
  {
    require(CHEMIN_DOSSIER_SACOCHE.$tab_classes_appli[$class_name]);
  }
  // Pour le portail sacoche.sesamath.net
  if(defined('APPEL_SITE_PROJET'))
  {
    $tab_classes_projet = array(
      'Blacklist'       => 'class.Blacklist.php' ,
      'DB_PROJET'       => 'class.requetes_DB_projet.php' ,
      'ProjetAdmin'     => 'class.ProjetAdmin.php' ,
      'ServeurSesamath' => 'class.ServeurSesamath.php' ,
    );
    if( isset($tab_classes_projet[$class_name]) )
    {
      require(CHEMIN_DOSSIER_PROJET_INCLUDE.$tab_classes_projet[$class_name]);
    }
  }
}

/**
 * Le principe du code qui suit est inspiré de celui de phpCAS.
 */
if(function_exists('spl_autoload_register'))
{
  // On peut utiliser une pile d'autoload ( PHP >= 5.1.2 ).
  if( !(spl_autoload_functions()) || !in_array('SACoche_autoload', spl_autoload_functions()) )
  {
    // On y ajoute notre autoload.
    spl_autoload_register('SACoche_autoload');
    if( function_exists('__autoload') && !in_array('__autoload', spl_autoload_functions()) )
    {
      // __autoload() a déjà été déclaré : pour ne pas l'ignorer on l'ajoute à la pile
      spl_autoload_register('__autoload');
    }
  }
}
else
{
  // Pas de pile d'autoload possible
  if(!function_exists('__autoload'))
  {
    // On définit notre autoload
    function __autoload($class_name)
    {
      return SACoche_autoload($class_name);
    }
  }
  else
  {
    exit('Erreur : l\'autoload ne peut être chargé car il a déjà été déclaré et PHP est en version < 5.1.2 !');
  }
}

// ============================================================================
// DEBUG - Sorties FirePHP
// ============================================================================

if(DEBUG>3)
{
  ini_set('output_buffering','On');
  $firephp = FirePHP::getInstance(TRUE);
  function afficher_infos_debug_FirePHP()
  {
    global $firephp;
    if(DEBUG_SESSION) { $firephp->dump('SESSION', $_SESSION); }
    if(DEBUG_POST)    { $firephp->dump('POST'   , $_POST); }
    if(DEBUG_GET)     { $firephp->dump('GET'    , $_GET); }
    if(DEBUG_FILES)   { $firephp->dump('FILES'  , $_FILES); }
    if(DEBUG_COOKIE)  { $firephp->dump('COOKIE' , $_COOKIE); }
    if(DEBUG_SERVER)  { $firephp->dump('SERVER' , $_SERVER); }
    if(DEBUG_CONST)   {
      $tab_constantes = get_defined_constants(TRUE);
      $firephp->dump('CONSTANTES', $tab_constantes['user']);
    }
  }
}

// ============================================================================
// URL de base du serveur
// ============================================================================

define('HTTP' , getServerProtocole() );
define('HOST' , getServerUrl() );
define('PORT' , getServerPort(HOST) );
define('URL_BASE' , HTTP.HOST.PORT);

// ============================================================================
// Type de serveur (LOCAL|DEV|PROD)
// ============================================================================

// On ne peut pas savoir avec certitude si un serveur est "local" car aucune méthode ne fonctionne à tous les coups :
// - $_SERVER['HTTP_HOST'] peut ne pas renvoyer localhost sur un serveur local (si configuration de domaines locaux via fichiers hosts / httpd.conf par exemple).
// - gethostbyname($_SERVER['HTTP_HOST']) peut renvoyer "127.0.0.1" sur un serveur non local car un serveur a en général 2 ip (une publique - ou privée s'il est sur un lan - et une locale).
// - $_SERVER['SERVER_ADDR'] peut renvoyer "127.0.0.1" avec nginx + apache sur 127.0.0.1 ...
if( mb_strpos(URL_BASE,'localhost') || mb_strpos(URL_BASE,'127.0.0.1') || mb_strpos(URL_BASE,'.local') )
{
  $serveur_type = 'LOCAL';
}
elseif( mb_strpos(URL_BASE,'.devsesamath.net') )
{
  $serveur_type = 'DEV';
}
else
{
  $serveur_type = 'PROD';
}

define('SERVEUR_TYPE',$serveur_type); // PROD | DEV | LOCAL

// ============================================================================
// URLs de l'application (les chemins restent relatifs pour les images ou les css/js...)
// ============================================================================

$url = URL_BASE.$_SERVER['SCRIPT_NAME'];
$fin = mb_strpos($url,SACoche);
if($fin)
{
  $url = mb_substr($url,0,$fin-1);
}
// Il manque "/sacoche" à l'URL si appelé depuis le projet
if(defined('APPEL_SITE_PROJET'))
{
  define('URL_DIR_PROJET',$url.'/'); // avec slash final
  $url .= '/sacoche';
}
define('URL_INSTALL_SACOCHE',$url); // la seule constante sans slash final
define('URL_DIR_SACOCHE',$url.'/'); // avec slash final

function chemin_to_url($chemin)
{
  $tab_bad = array( CHEMIN_DOSSIER_SACOCHE , DS );
  $tab_bon = array( URL_DIR_SACOCHE        , '/');
  return str_replace( $tab_bad , $tab_bon , $chemin );
}
function url_to_chemin($url)
{
  $tab_bad = array( URL_DIR_SACOCHE        , '/');
  $tab_bon = array( CHEMIN_DOSSIER_SACOCHE , DS );
  return str_replace( $tab_bad , $tab_bon , $url );
}

define('URL_DIR_TMP'         , chemin_to_url(CHEMIN_DOSSIER_TMP        ) );
define('URL_DIR_IMG'         , chemin_to_url(CHEMIN_DOSSIER_IMG        ) );
define('URL_DIR_DEVOIR'      , chemin_to_url(CHEMIN_DOSSIER_DEVOIR     ) );
define('URL_DIR_DUMP'        , chemin_to_url(CHEMIN_DOSSIER_DUMP       ) );
define('URL_DIR_EXPORT'      , chemin_to_url(CHEMIN_DOSSIER_EXPORT     ) );
define('URL_DIR_IMPORT'      , chemin_to_url(CHEMIN_DOSSIER_IMPORT     ) );
define('URL_DIR_LOGINPASS'   , chemin_to_url(CHEMIN_DOSSIER_LOGINPASS  ) );
define('URL_DIR_LOGO'        , chemin_to_url(CHEMIN_DOSSIER_LOGO       ) );
define('URL_DIR_OFFICIEL'    , chemin_to_url(CHEMIN_DOSSIER_OFFICIEL   ) );
define('URL_DIR_PARTENARIAT' , chemin_to_url(CHEMIN_DOSSIER_PARTENARIAT) );
define('URL_DIR_RSS'         , chemin_to_url(CHEMIN_DOSSIER_RSS        ) );
define('URL_DIR_WEBSERVICES' , chemin_to_url(CHEMIN_DOSSIER_WEBSERVICES) );

// ============================================================================
// URL externes appelées par l'application
// ============================================================================

define('SERVEUR_PROJET'         ,'https://sacoche.sesamath.net');        // URL du projet SACoche (en https depuis le 08/02/2012)
define('SERVEUR_SSL'            ,'https://sacoche.sesamath.net');        // URL du serveur Sésamath sécurisé (idem serveur projet SACoche depuis le 08/02/2012)
define('SERVEUR_ASSO'           ,'http://www.sesamath.net');             // URL du serveur de l'association Sésamath
define('SERVEUR_COMMUNAUTAIRE'  ,SERVEUR_PROJET.'/appel_externe.php');   // URL du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
define('SERVEUR_DOCUMENTAIRE'   ,SERVEUR_PROJET.'/appel_doc.php');       // URL du fichier chargé d'afficher les documentations
define('SERVEUR_TELECHARGEMENT' ,SERVEUR_PROJET.'/telechargement.php');  // URL du fichier renvoyant le ZIP de la dernière archive de SACoche disponible
define('SERVEUR_VERSION'        ,SERVEUR_PROJET.'/sacoche/VERSION.txt'); // URL du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_CNIL'           ,SERVEUR_PROJET.'/?page=cnil');          // URL de la page "CNIL (données personnelles)"
define('SERVEUR_ECHANGER'       ,SERVEUR_PROJET.'/?page=echanger');      // URL de la page "Où échanger autour de SACoche ?"
define('SERVEUR_GUIDE_ENT'      ,SERVEUR_PROJET.'/?page=ent');           // URL de la page "Mode d'identification & Guide d'intégration aux ENT"
define('SERVEUR_GUIDE_ADMIN'    ,SERVEUR_PROJET.'/?page=guide_admin');   // URL de la page "Guide de démarrage (administrateur de SACoche)"
define('SERVEUR_GUIDE_RENTREE'  ,SERVEUR_PROJET.'/?page=guide_rentree'); // URL de la page "Guide de changement d'année (administrateur de SACoche)"
define('SERVEUR_NEWS'           ,SERVEUR_PROJET.'/?page=news');          // URL de la page "Historique des nouveautés"
define('SERVEUR_RSS'            ,SERVEUR_PROJET.'/_rss/rss.xml');        // URL du fichier comportant le flux RSS
define('SERVEUR_RESS_HTML'      ,SERVEUR_PROJET.'/__ress_html/');        // URL du dossier avec les pages de ressources associées aux items
define('SERVEUR_LSU_PDF'        ,SERVEUR_PROJET.'/_doc/LSU/');           // URL du dossier avec les pages d'exemple du Livret Scolaire Unique

define('SERVEUR_BLOG_CONVENTION',SERVEUR_ASSO.'/blog/index.php/aM4');    // URL de la page expliquant les conventions ENT

// ============================================================================
// Autres constantes diverses... et parfois importantes !
// ============================================================================

// test si c'est l'hébergement Sésamath qui est utilisé
$is_hebergement_sesamath = ( mb_strpos(URL_BASE,'.sesamath.net') || mb_strpos(URL_BASE,'.devsesamath.net') ) ? TRUE : FALSE ;
define('IS_HEBERGEMENT_SESAMATH', $is_hebergement_sesamath);
// define('IS_HEBERGEMENT_SESAMATH', TRUE);

// Pour forcer la minification des js et css y compris sur un serveur local (ou le contraire, y compris sur un serveur en prod)
// define('FORCE_MINIFY' , TRUE);
// define('FORCE_NO_MINIFY' , TRUE);

// mail de contact en cas d'erreur applicative
define('SACOCHE_CONTACT_COURRIEL','contact-sacoche@sesamath.net');

// indiquer si une convention Établissement-ENT est requise et à compter de quand
define('CONVENTION_ENT_REQUISE'         ,TRUE);
define('CONVENTION_ENT_START_DATE_FR'   ,'01/09/2013');
define('CONVENTION_ENT_START_DATE_MYSQL','2013-09-01');
define('CONVENTION_ENT_ID_ETABL_MAXI'   ,1000000); // Les établissements d'id >= sont des établissements de test (connecteurs ENT...).

// Identifiants particuliers (à NE PAS modifier)
define('ID_DEMO'                   ,   9999); // id de l'établissement de démonstration (pour $_SESSION['SESAMATH_ID']) ; 0 pose des pbs, et il fallait prendre un id disponible dans la base d'établissements de Sésamath
define('ID_MATIERE_PARTAGEE_MAX'   ,   9999); // id maximal des matières partagées (les id des matières spécifiques sont supérieurs)
define('ID_NIVEAU_PARTAGE_MAX'     , 999999); // id maximal des niveaux partagés (les id des niveaux spécifiques sont supérieurs)
define('ID_FAMILLE_MATIERE_USUELLE',     99);
define('ID_FAMILLE_NIVEAU_USUEL'   ,    999);

// longueur maxi de certains champs (à NE PAS modifier : doit être en cohérence avec la BDD)
// Pour le nom et le prénom, est aussi défini dans ./_img/php/etiquette.php
define(   'LOGIN_LONGUEUR_MAX',  50);
define('PASSWORD_LONGUEUR_MAX',  50);
define(     'NOM_LONGUEUR_MAX',  50);
define(  'PRENOM_LONGUEUR_MAX',  50);
define('COURRIEL_LONGUEUR_MAX', 100);
define(  'ID_ENT_LONGUEUR_MAX',  63);
define( 'ID_GEPI_LONGUEUR_MAX',  63);

// cookies
define('COOKIE_STRUCTURE' ,'SACoche-etablissement' ); // nom du cookie servant à retenir l'établissement sélectionné, afin de ne pas à avoir à le sélectionner de nouveau, et à pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_AUTHMODE'  ,'SACoche-mode-connexion'); // nom du cookie servant à retenir le dernier mode de connexion utilisé par un user connecté, afin de pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_PARTENAIRE','SACoche-partenaire'    ); // nom du cookie servant à retenir le partenaire sélectionné, afin de ne pas à avoir à le sélectionner de nouveau (convention ENT sur serveur Sésamath uniquement).
define('COOKIE_MEMOGET'   ,'SACoche-memoget'       ); // nom du cookie servant à retenir des paramètres multiples transmis en GET dans le cas où le service d'authentification externe en perd...
define('COOKIE_TEST'      ,'SACoche-test-cookie'   ); // nom du cookie servant à tester si les cookies s'enregistrent bien, pour éviter une boucle SSO...

// session
define('SESSION_NOM','SACoche-session'); // Est aussi défini dans /_lib/SimpleSAMLphp/config/config.php

// Version des fichiers installés.
// À comparer avec la dernière version disponible sur le serveur communautaire.
// Pour une conversion en entier : list($annee,$mois,$jour) = explode('-',substr(VERSION_PROG,0,10)); $indice_version = (date('Y')-2011)*365 + date('z',mktime(0,0,0,$mois,$jour,$annee));
// Dans un fichier texte pour permettre un appel au serveur communautaire sans lui faire utiliser PHP.
define('VERSION_PROG', file_get_contents(CHEMIN_DOSSIER_SACOCHE.'VERSION.txt') );

// Version de la base associée d'un établissement, et du webmestre si multi-structures.
// À comparer avec la version de la base actuellement en place.
// Dans des fichiers texte pour faciliter la maintenance par les développeurs.
define('VERSION_BASE_STRUCTURE', file_get_contents(CHEMIN_DOSSIER_SQL.'VERSION_BASE_STRUCTURE.txt') ); 
define('VERSION_BASE_WEBMESTRE', file_get_contents(CHEMIN_DOSSIER_SQL.'VERSION_BASE_WEBMESTRE.txt') );

// dates
define('TODAY_FR'    ,date("d/m/Y"));
define('TODAY_MYSQL' ,date("Y-m-d"));
define('SORTIE_DEFAUT_MYSQL' ,'9999-12-31');

// Dimension maxi d'une photo redimensionnée (en pixels) ; utilisé aussi dans le style.css : .photo {min-height:180px} (144+36)
// Prendre un nombre divisible par 3 et par 4, donc un multiple de 12.
// Avec 180 (12x15), au format 2/3 ça donne 120/180 et au format 3/4 ça donne 135/180 ; c'est un peu grand à l'écran.
// Avec 144 (12x12), au format 2/3 ça donne  96/144 et au format 3/4 ça donne 108/144 ; c'est un choix intermédiaire.
// Avec 120 (12x10), au format 2/3 ça donne  80/120 et au format 3/4 ça donne  90/120 ; c'est un peu petit à l'écran.
define('PHOTO_DIMENSION_MAXI',144);
// Le format jpeg est le plus adapté aux photos ; un facteur 90 permet un gain de poids significatif (>50% par rapport à 100), pour une perte de qualité minime.
// Avec une dimension maxi imposée de 180 pixels, on arrive à 8~9 Ko par photo par élève dans la base (en comptant le base64_encode).
// Avec une dimension maxi imposée de 144 pixels, on arrive à 6~7 Ko par photo par élève dans la base (en comptant le base64_encode).
// Avec une dimension maxi imposée de 120 pixels, on arrive à 4~5 Ko par photo par élève dans la base (en comptant le base64_encode).
define('JPEG_QUALITY',90);

// Traductions
define('LOCALE_DEFAULT', 'fr_FR');
define('LOCALE_CHARSET', 'UTF-8');
define('LOCALE_DOMAINE', 'traductions');
define('LOCALE_DIR'    , CHEMIN_DOSSIER_SACOCHE.'_lang');
if(!defined('LC_MESSAGES'))
{
  // Si PHP n'a pas été compilé avec "libintl"...
  define('LC_MESSAGES', 5);
}

// Pour les appels cURL, dont ceux effectués par phpCAS
define('CURL_AGENT' , 'SACoche '.URL_INSTALL_SACOCHE);


// ============================================================================
// Fonctions utilisées pour déterminer l'URL de base du serveur
// ============================================================================

// Code issu de la fonction _getServerUrl() provenant de phpCAS/CAS/Client.php
// Les variables HTTP_X_FORWARDED_* sont définies quand un serveur web (ou un proxy) qui récupère la main la passe à un serveur php (qui peut ou pas être un autre serveur web, mais en général pas accessible directement).
// Concernant HTTP_X_FORWARDED_HOST, il peut contenir plusieurs HOST successifs séparés par des virgules : on explose le tableau et on utilise la première valeur.
// Daniel privilégie HTTP_HOST (qui provient de la requete HTTP) à SERVER_NAME (qui vient de la conf du serveur) quand les 2 existent, mais phpCAS fait le contraire ; en général c'est pareil, sauf s'il y a des alias sans redirection (ex d'un site qui donne les mêmes pages avec et sans les www), dans ce cas le 1er est l'alias demandé et le 2nd le nom principal configuré du serveur (qui peut être avec ou sans les www, suivant la conf).
// Il arrive (très rarement) que HTTP_HOST ne soit pas défini (HTTP 1.1 impose au client web de préciser un nom de site, ce qui n'était pas le cas en HTTP 1.0 ; HTTP 1.1 date de 1999, avec un brouillon en 1996 ; et puis il y a aussi les appels en mode CLI).

/**
 * getServerUrl
 *
 * @param void
 * @return string
 */
function getServerUrl()
{
  if (!empty($_SERVER['HTTP_X_FORWARDED_HOST']))   { return current(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])); }
  if (!empty($_SERVER['HTTP_X_FORWARDED_SERVER'])) { return $_SERVER['HTTP_X_FORWARDED_SERVER']; }
  if (!empty($_SERVER['HTTP_HOST']))               { return $_SERVER['HTTP_HOST']; }
  if (!empty($_SERVER['SERVER_NAME']))             { return $_SERVER['SERVER_NAME']; }
  exit_error( 'HOST indéfini' /*titre*/ , 'SACoche n\'arrive pas à déterminer le nom du serveur hôte !<br />HTTP_HOST, SERVER_NAME, HTTP_X_FORWARDED_HOST et HTTP_X_FORWARDED_SERVER sont tous indéfinis.' /*contenu*/ );
}

/**
 * getServerProtocole
 *
 * @param void
 * @return string
 */
function getServerProtocole()
{
  // $_SERVER['HTTPS'] peut valoir 'on' ou 'off' ou ''
  if ( !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off') )
  {
    return 'https://';
  }
  // Pour les serveurs derrière un équilibreur de charge (@see http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_request_fields)
  if( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO']=='https') )
  {
    return 'https://';
  }
  if( isset($_SERVER['HTTP_X_FORWARDED_PROTOCOL']) && ($_SERVER['HTTP_X_FORWARDED_PROTOCOL']=='https') )
  {
    return 'https://';
  }
  return 'http://';
}

/**
 * getServerPort
 *
 * @param string $host
 * @return string
 */
function getServerPort($host)
{
  if(!empty($_SERVER['HTTP_X_FORWARDED_PORT']))
  {
    $port = $_SERVER['HTTP_X_FORWARDED_PORT'];
  }
  elseif(!empty($_SERVER['SERVER_PORT']))
  {
    $port = $_SERVER['SERVER_PORT'];
  }
  else
  {
    $port = NULL;
  }
  // Rien à indiquer si port 80 (protocole standard HTTP) ou 443 (protocole standard HTTPS) ou port déjà indiqué dans le HOST (les navigateurs indiquent le port dans le header Host de la requete http quand il est non standard comme la norme http1/1 le préconise http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.23 mais le serveur web ne le file généralement pas à PHP dans HTTP_HOST)
  return ( !$port || in_array($port,array(80,443)) || strpos($host,':') ) ? '' : ':'.$port ;
}

// ============================================================================
// Fonctions d'affichage et de sortie
// ============================================================================

/**
 * Fonction pour remplacer mb_detect_encoding() à cause d'un bug : http://fr2.php.net/manual/en/function.mb-detect-encoding.php#81936
 *
 * @param string
 * @return bool
 */
function perso_mb_detect_encoding_utf8($text)
{
  return (mb_detect_encoding($text.' ','auto',TRUE)=='UTF-8');
}

/**
 * Convertir les caractères spéciaux (&"'<>) en entité HTML afin d'éviter des problèmes d'affichage (INPUT, SELECT, TEXTAREA, XML, ou simple texte HTML valide...).
 * Pour que les retours à la ligne soient convertis en <br /> il faut coupler cette fontion à la fonction nl2br()
 * 
 * @param string
 * @return string
 */
function html($text)
{
  // Ne pas modifier ce code à la légère : les résultats sont différents suivant que ce soit un affichage direct ou ajax, suivant la version de PHP (5.1 ou 5.3)...
  return (perso_mb_detect_encoding_utf8($text)) ? htmlspecialchars($text,ENT_COMPAT,'UTF-8') : utf8_encode(htmlspecialchars($text,ENT_COMPAT)) ;
}

/**
 * Afficher une page HTML minimale (mais aux couleurs de SACoche) avec un message explicatif et un lien adapté éventuel.
 * 
 * @param string $titre     titre de la page
 * @param string $contenu   contenu HTML affiché (ou AJAX retourné) ; il doit déjà avoir été filtré si besoin avec html()
 * @param string $lien      "accueil" pour un lien vers l'accueil (par défaut) OU "install" vers la procédure d'installation OU "contact" pour le formulaire de contact admin OU "" pour aucun
 * @param int    $BASE      numéro de base, en cas de redirection vers un contact admin
 * @return void
 */
function exit_error( $titre , $contenu , $lien='accueil' , $BASE=NULL )
{
  // Suppression du cookie provisoire ayant servi à mémoriser des paramètres multiples transmis en GET dans le cas où le service d'authentification externe en perd.
  // C'est le cas lors de l'appel d'un IdP de type RSA FIM, application nationale du ministère...
  // COOKIE_MEMOGET peut ne pas être encore défini si sortie au début du loader.
  if( defined('URL_DIR_SACOCHE') && isset($_COOKIE[COOKIE_MEMOGET]) )
  {
    Cookie::effacer(COOKIE_MEMOGET);
  }
  if( SACoche == 'ajax' )
  {
    Json::end( FALSE , str_replace('<br />',' ',$contenu) );
  }
  elseif( SACoche == 'appel_externe' )
  {
    exit( $contenu ); // Erreur ...
  }
  else
  {
    // URL_DIR_SACOCHE peut ne pas être encore défini si sortie au début du loader.
    if(defined('URL_DIR_SACOCHE'))
    {
      $chemin = URL_DIR_SACOCHE;
    }
    elseif(defined('APPEL_SITE_PROJET'))
    {
      $chemin = './sacoche/';
    }
    else
    {
      $chemin = './';
    }
    header('Content-Type: text/html; charset='.CHARSET);
    echo'<!DOCTYPE html>'.NL;
    echo'<html lang="fr">'.NL;
    echo  '<head>'.NL;
    echo    '<link rel="stylesheet" type="text/css" href="'.$chemin.'_css/style.css" />'.NL;
    echo    '<style type="text/css">#cadre_milieu{color:#D00}</style>'.NL;
    echo    '<title>SACoche » '.$titre.'</title>'.NL;
    echo  '</head>'.NL;
    echo  '<body>'.NL;
    echo    '<div id="cadre_milieu">'.NL;
    echo      '<div class="hc"><img src="'.$chemin.'_img/logo_grand.gif" alt="SACoche" width="208" height="71" /></div>'.NL;
    echo      '<h1>'.$titre.'</h1>'.NL;
    echo      '<p>'.str_replace('<br />','</p><p>',$contenu).'</p>'.NL;
        if($lien=='accueil') { echo'<p><a href="'.$chemin.'index.php">Retour en page d\'accueil de SACoche.</a></p>'.NL; } 
    elseif($lien=='install') { echo'<p><a href="'.$chemin.'index.php?page=public_installation">Procédure d\'installation de SACoche.</a></p>'.NL; } 
    elseif($lien=='contact') { echo'<p><a href="'.$chemin.'index.php?page=public_contact_admin&amp;base='.$BASE.'&amp;msg='.html(urlencode($titre.'<br />'.$contenu)).'">Contacter les administrateurs de cet établissement pour les en informer.</a></p>'.NL; } 
    echo    '</div>'.NL;
    echo  '</body>'.NL;
    echo'</html>'.NL;
    exit();
  }
}

/**
 * Rediriger le navigateur.
 * 
 * @param string $adresse   URL de la page vers laquelle rediriger
 * @return void
 */
function exit_redirection($adresse)
{
  // Qqs header ajoutés par précaution car même si la redirection est indiquée comme étant temporaire, il semblent que certains navigateurs buguent en la mettant en cache.
  header('Pragma: no-cache');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); // IE n'aime pas "no-store" ni "no-cache".
  header('Expires: 0');
  // Cette fois-ci on y va
  header('Status: 307 Temporary Redirect', TRUE, 307);
  header('Location: '.$adresse);
  header('Content-Length: 0'); // Varnish (le frontal web), en cas de redirection dans le header HTTP avec un body HTTP vide, envoie le header tout de suite mais attends 5s un éventuel body avant de couper la connexion si la réponse ne précise pas sa taille ; or Firefox, qui reçoit une redirection, dit au serveur de fermer la connexion TCP et attend la réponse à cette demande de fermeture avant de suivre la redirection ; moralité, quand on fait une redirection via les headers il faudrait toujours ajouter 'Content-Length: 0'
  exit();
}

?>