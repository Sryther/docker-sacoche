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

// Fichier appelé pour l'affichage de chaque page.
// Passage en GET des paramètres pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// En théorie, si l'utilisateur refuse les cookies de SACoche mais accepte ceux de l'ENT, une demande de connexion SSO peut entraîner des boucles de redirections (la session n'étant pas retrouvée).
if(empty($_COOKIE[COOKIE_TEST]))
{
  Cookie::definir( COOKIE_TEST , TRUE );
  if(isset($_GET['sso']))
  {
    if(!isset($_GET['cookie']))
    {
      exit_redirection(URL_BASE.$_SERVER['REQUEST_URI'].'&cookie');
    }
    else
    {
      exit_error( 'Risque de redirections en boucle' /*titre*/ , 'Pour utiliser <em>SACoche</em> vous devez configurer l\'acceptation des cookies par votre navigateur.' /*contenu*/ );
    }
  }
}
else
{
  if(isset($_GET['cookie']))
  {
    // Maintenant qu'on a vérifié que les cookies sont ok, on recharge sans le GET ajouté dans l'URL, sinon Itop demande une ré-authentification (service considéré différent...).
    exit_redirection(str_replace('&cookie','',URL_BASE.$_SERVER['REQUEST_URI']));
  }
}

// Mémorisation de paramètres multiples transmis en GET pour les retrouver par la suite dans le cas où le service d'authentification externe en perd
// C'est le cas lors de l'appel d'un IdP de type RSA FIM, application nationale du ministère...
if(isset($_GET['memoget']))
{
  Cookie::save_get_and_exit_reload( urldecode($_GET['memoget']) );
}
// Récupération de paramètres multiples transmis en GET et mémorisés temporairement dans un cookie (pour le cas où le service d'authentification externe en perd).
if(isset($_COOKIE[COOKIE_MEMOGET]))
{
  Cookie::load_get();
}

// Page et section appelées ; normalement transmis en $_GET mais $_POST possibles depuis GEPI
    if(isset($_GET['page']))     { $PAGE = Clean::fichier($_GET['page']); }
elseif(isset($_POST['page']))    { $PAGE = Clean::fichier($_POST['page']); }
elseif(isset($_GET['code_mdp'])) { $PAGE = 'public_nouveau_mdp'; }
elseif(isset($_GET['sso']))      { $PAGE = 'compte_accueil'; }
else                             { $PAGE = 'public_accueil'; }
    if(isset($_GET['section']))  { $SECTION = Clean::fichier($_GET['section']); }
elseif(isset($_POST['section'])) { $SECTION = Clean::fichier($_POST['section']); }
else                             { $SECTION = ''; }

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
  Session::$tab_message_erreur[] = 'Droits de la page "'.$PAGE.'" manquants !<br />Paramètre "page" transmis en GET incorrect, ou droits non attribués dans le fichier "'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php').'".';
  // La page vers laquelle rediriger sera définie après ouverture de la session
}

// Ouverture de la session et gestion des droits d'accès
Session::execute();
if(count(Session::$tab_message_erreur))
{
  $PAGE = ($_SESSION['USER_PROFIL_TYPE'] == 'public') ? 'public_accueil' : 'compte_accueil' ;
}

// Traductions
if($_SESSION['USER_PROFIL_TYPE']!='public')
{
  Lang::setlocale( LC_MESSAGES, Lang::get_locale_used() );
  Lang::bindtextdomain( LOCALE_DOMAINE, LOCALE_DIR );
  Lang::bind_textdomain_codeset( LOCALE_DOMAINE, LOCALE_CHARSET );
  Lang::textdomain( LOCALE_DOMAINE );
}

// Alerte si navigateur trop ancien
if(!empty($_SESSION['BROWSER']['alerte']))
{
  Session::$tab_message_erreur[] = $_SESSION['BROWSER']['alerte'];
}

// Infos DEBUG dans FirePHP
if (DEBUG>3) afficher_infos_debug_FirePHP();

// Blocage éventuel par le webmestre ou un administrateur ou l'automate (on ne peut pas le tester avant car il faut avoir récupéré les données de session)
LockAcces::stopper_si_blocage( $_SESSION['BASE'] , FALSE /*demande_connexion_profil*/ );

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

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

// Authentification requise par SSO
if(Session::$_sso_redirect)
{
  require(CHEMIN_DOSSIER_PAGES.'public_sso_login.php');
  // En cas de redirection pour cause de profil incompatible, il faut vérifier que l'on n'est pas encore identifié avec le mauvais profil.
  if( !Session::verifier_droit_acces($_SESSION['USER_PROFIL_TYPE']) && !Session::verifier_droit_acces('public') )
  {
    Session::$tab_message_erreur[] = 'Appel incompatible avec votre identification actuelle.'.' Veuillez vous (re)connecter.';
    $PAGE = 'public_accueil';
  }
}

// Suppression du cookie provisoire ayant servi à mémoriser des paramètres multiples transmis en GET dans le cas où le service d'authentification externe en perd.
// C'est le cas lors de l'appel d'un IdP de type RSA FIM, application nationale du ministère...
if(isset($_COOKIE[COOKIE_MEMOGET]))
{
  Cookie::effacer(COOKIE_MEMOGET);
}

// Authentification pour le compte d'une application tierce
if(isset($_GET['url_redirection']))
{
  $url_redirection = urldecode($_GET['url_redirection']);
  if($_SESSION['USER_PROFIL_SIGLE'] == 'OUT')
  {
    // User non connecté -> Retenir la demande en attendant qu'il se connecte
    $_SESSION['MEMO_GET']['url_redirection'] = $url_redirection;
  }
  else
  {
    // User connecté -> Redirection vers l'application, avec une clef (ticket) pour attester du login et permettre de récupérer ses infos
    $clef = FileSystem::fabriquer_fichier_user_infos_for_appli_externe();
    unset($_SESSION['MEMO_GET']);
    $separateur = (strpos($url_redirection,'?')===FALSE) ? '?' : '&' ;
    exit_redirection($url_redirection.$separateur.'clef='.$clef);
  }
}

// Page CNIL si message d'information CNIL non validé.
if(isset($_SESSION['STOP_CNIL']))
{
  Session::$tab_message_erreur[] = 'Avant d\'utiliser <em>SACoche</em>, vous devez valider le formulaire ci-dessous.';
  $PAGE = 'compte_cnil';
}

// Fichier de données de la page concernée
$filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.php';
if(!is_file($filename_php))
{
  Session::$tab_message_erreur[] = 'Fichier '.FileSystem::fin_chemin($filename_php).' manquant ; redirection vers une page d\'accueil.';
  $PAGE = ($_SESSION['USER_PROFIL_TYPE']=='public') ? 'public_accueil' : ( (isset($_SESSION['STOP_CNIL'])) ? 'compte_cnil' : 'compte_accueil' ) ;
  $filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.php';
}

// Contenu à afficher récupéré dans une variable
ob_start();
require($filename_php);
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Jeton CSRF ; ne peut pas être généré avant car $PAGE peut être changé par le code inclus ci-dessus.
Session::generer_jeton_anti_CSRF($PAGE);

// Titre du navigateur
$browser_title = ($TITRE) ? $TITRE : 'Évaluer par compétences et valider le socle commun' ;
Layout::add( 'browser_title' , $browser_title );

// Css personnalisé
if(!empty($_SESSION['CSS']))
{
  Layout::add( 'css_inline' , $_SESSION['CSS'] );
}

// Fichiers css & js
$tab_pages_graphiques = array('brevet_fiches','officiel_accueil','releve_bilan_chronologique','livret_edition');
$filename_js_normal = './pages/'.$PAGE.'.js';
$jquery_version = ( ($_SESSION['BROWSER']['modele']!='explorer') || ($_SESSION['BROWSER']['version']>=9) ) ? '3' : '1' ;
Layout::add( 'css_file' , './_css/style.css'                              , 'mini' );
Layout::add( 'js_file'  , './_js/jquery'.$jquery_version.'-librairies.js' , 'comm' );
// Layout::add( 'js_file'  , './_js/jquery-migrate-3.0.0.js' , 'comm' );
Layout::add( 'js_file'  , './_js/script.js'                               , 'mini' );
if(in_array($PAGE,$tab_pages_graphiques))
{
  Layout::add( 'js_file' , './_js/highcharts.js' , 'comm' );
}
if(is_file($filename_js_normal))
{
  Layout::add( 'js_file' , $filename_js_normal   , 'mini' );
}

// Ultimes constantes javascript
Layout::add( 'js_inline_before' , 'var PAGE              = "'.$PAGE.'";' );
Layout::add( 'js_inline_before' , 'var CSRF              = "'.Session::$_CSRF_value.'";' );
Layout::add( 'js_inline_before' , 'var PROFIL_TYPE       = "'.$_SESSION['USER_PROFIL_TYPE'].'";' );
Layout::add( 'js_inline_before' , 'var CONNEXION_USED    = "'.((isset($_COOKIE[COOKIE_AUTHMODE])) ? $_COOKIE[COOKIE_AUTHMODE] : 'normal').'";' );
Layout::add( 'js_inline_before' , 'var DUREE_AUTORISEE   = '.$_SESSION['USER_DUREE_INACTIVITE'].';' );
Layout::add( 'js_inline_before' , 'var DUREE_AFFICHEE    = '.$_SESSION['USER_DUREE_INACTIVITE'].';' );
Layout::add( 'js_inline_before' , 'var DECONNEXION_REDIR = "'.((isset($_SESSION['DECONNEXION_ADRESSE_REDIRECTION'])) ? html($_SESSION['DECONNEXION_ADRESSE_REDIRECTION']) : '').'";' );
Layout::add( 'js_inline_before' , 'var BASE_MAJ_DECALEE  = '.((empty($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'])) ? 'false' : 'true').';' );
Layout::add( 'js_inline_before' , 'var isMobile          = '.(int)$_SESSION['BROWSER']['mobile'].';' );

// Affichage
$body_class = ($_SESSION['BROWSER']['mobile']) ? 'touch' : 'mouse' ;
echo Layout::afficher_page_entete('prog-'.$body_class);
if( substr($PAGE,0,7) !== 'public_' )
{
  $lien_page_langue = ($_SESSION['USER_ETABLISSEMENT'])     ? ' <a href="./index.php?page=compte_langue">['.Lang::get_locale_used().']</a>' : '' ;
  $lien_page_switch = (!empty($_SESSION['USER_SWITCH_ID'])) ? '<a href="./index.php?page=compte_switch"><span class="top switch">'.$_SESSION['USER_PROFIL_NOM_COURT'].'</span></a>' : $_SESSION['USER_PROFIL_NOM_COURT'] ;
  // Espace identifié : cadre_haut (avec le menu) et cadre_bas (avec le contenu).
  echo'<div id="cadre_haut">'.NL;
  echo  '<a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_PROJET.'" class="no_puce"><img id="logo" alt="SACoche" src="./_img/logo_petit_menu.png" width="154" height="39" /></a>'.NL;
  echo  '<div id="top_info">'.NL;
  echo    $_SESSION['MENU'];
  echo    '<div>'.NL;
  echo      '<span class="top home">'.html($_SESSION['ETABLISSEMENT']['DENOMINATION']).' <a href="./index.php?page=compte_accueil">['.html(Lang::_("accueil")).']</a></span><br />'.NL;
  echo      '<span class="top profil_'.$_SESSION['USER_PROFIL_TYPE'].'">'.html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).' ['.$lien_page_switch.']'.$lien_page_langue.'</span>'.NL;
  echo    '</div>'.NL;
  echo    '<div>'.NL;
  echo      '<span class="top clock_fixe"><span id="clock">'.$_SESSION['USER_DUREE_INACTIVITE'].' min</span></span><br />'.NL;
  echo      '<a href="#"><span class="top deconnexion" id="deconnecter">'.html(Lang::_("Déconnexion")).'</span></a>'.NL;
  echo    '</div>'.NL;
  echo  '</div>'.NL;
  echo  '<audio id="audio_bip" preload="none" class="hide">'.NL;
  // On pourrait bien sur laisser le navigateur se débrouiller et prendre le format qui lui convient, il me semble que dans ce cas j'avais noté des avertissements dans Firebug.
  if(!in_array( $_SESSION['BROWSER']['modele'] , array('firefox','opera') ))
  {
    echo    '<source src="./_audio/bip.mp3" type="audio/mpeg" />'.NL;
  }
  if(!in_array( $_SESSION['BROWSER']['modele'] , array('explorer','safari') ))
  {
    echo    '<source src="./_audio/bip.ogg" type="audio/ogg" />'.NL;
  }
  echo  '</audio>'.NL;
  echo'</div>'.NL;
  echo'<div id="cadre_navig"><a id="go_haut" href="#cadre_haut" title="Haut de page"></a><a id="go_bas" href="#ancre_bas" title="Bas de page"></a></div>'.NL;
  echo'<div id="cadre_bas">'.NL;
  if(!empty($SOUS_MENU))
  {
    echo'<div class="sousmenu">'.$SOUS_MENU.'</div>';
  }
  echo  '<h1>'.$TITRE.'</h1>'.NL;
}
else
{
  // Accueil (identification ou procédure d'installation ou génération de nouveaux identifiants) : cadre unique (avec image SACoche & image hébergeur).
  echo'<div id="cadre_milieu">'.NL;
  if($PAGE=='public_accueil')
  {
    $tab_image_infos = ( (defined('HEBERGEUR_LOGO')) && (is_file(CHEMIN_DOSSIER_LOGO.HEBERGEUR_LOGO)) ) ? getimagesize(CHEMIN_DOSSIER_LOGO.HEBERGEUR_LOGO) : array() ;
    $hebergeur_img   = count($tab_image_infos) ? '<img alt="Hébergeur" src="'.URL_DIR_LOGO.HEBERGEUR_LOGO.'" '.$tab_image_infos[3].' />' : '' ;
    $hebergeur_lien  = ( (defined('HEBERGEUR_ADRESSE_SITE')) && HEBERGEUR_ADRESSE_SITE && ($hebergeur_img) ) ? '<a href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.$hebergeur_img.'</a>' : $hebergeur_img ;
    $SACoche_lien    = '<a href="'.SERVEUR_PROJET.'"><img alt="Suivi d\'Acquisition de Compétences" src="./_img/logo_grand.gif" width="208" height="71" /></a>' ;
    echo  '<div id="titre_logo">'.$SACoche_lien.$hebergeur_lien.'</div>'.NL;
  }
  else
  {
    echo  '<div class="hc"><img src="./_img/logo_grand.gif" alt="SACoche" width="208" height="71" /></div>'.NL;
    echo  '<h1>'.$TITRE.'</h1>'.NL;
  }
}
// Alerte si pas de javascript activé, et autres messages d'erreurs (ceux relatifs à l'acceptation des cookies et/ou l'usage d'iframe sont ajoutés par script.js)
echo  '<noscript>Pour utiliser <em>SACoche</em> vous devez activer JavaScript dans votre navigateur.</noscript>'.NL;
if(count(Session::$tab_message_erreur))
{
  echo  '<div class="probleme">'.implode('</div><div class="probleme">',Session::$tab_message_erreur).'</div>'.NL;
  Session::$tab_message_erreur = array();
}
echo  '<hr />'.NL;
echo   $CONTENU_PAGE;
echo  '<div id="ancre_bas"></div>'.NL; // Il faut un div et pas seulement un span pour le navigateur Safari (sinon href="#ancre_bas" ne fonctionne pas).
echo'</div>'.NL;
echo Layout::afficher_page_pied();
?>