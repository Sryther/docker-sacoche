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

// Versions des navigateurs.

define(  'CHROME_VERSION_MINI_REQUISE'   , 3  ); define(  'CHROME_TEXTE_MINI_REQUIS'     , 'Version 3 minimum (sortie en 2009).');
define(  'CHROME_VERSION_MINI_CONSEILLEE', 5  );
define(  'CHROME_VERSION_LAST'           ,58  ); define(  'CHROME_URL_DOWNLOAD'          , 'http://www.google.fr/chrome');

define( 'FIREFOX_VERSION_MINI_REQUISE'   , 3.5); define( 'FIREFOX_TEXTE_MINI_REQUIS'     , 'Version 3.5 minimum (sortie en 2009).');
define( 'FIREFOX_VERSION_MINI_CONSEILLEE', 4  );
define( 'FIREFOX_VERSION_LAST'           ,53  ); define( 'FIREFOX_URL_DOWNLOAD'          , 'https://www.mozilla.org/fr/');

define(   'OPERA_VERSION_MINI_REQUISE'   ,10  ); define(   'OPERA_TEXTE_MINI_REQUIS'     , 'Version 10 minimum (sortie en 2009).');
define(   'OPERA_VERSION_MINI_CONSEILLEE',11  );
define(   'OPERA_VERSION_LAST'           ,45  ); define(   'OPERA_URL_DOWNLOAD'          , 'http://www.opera-fr.com/telechargements/');

define(    'EDGE_VERSION_MINI_REQUISE'   ,12  ); define(    'EDGE_TEXTE_MINI_REQUIS'      , 'Version 12 minimum (sortie en 2015).');
define(    'EDGE_VERSION_MINI_CONSEILLEE',12  );
define(    'EDGE_VERSION_LAST'           ,14  ); define(    'EDGE_URL_DOWNLOAD'           , 'https://www.microsoft.com/fr-fr/windows/microsoft-edge');

define(  'SAFARI_VERSION_MINI_REQUISE'   , 4  ); define(  'SAFARI_TEXTE_MINI_REQUIS'     , 'Version 4 minimum (sortie en 2009).');
define(  'SAFARI_VERSION_MINI_CONSEILLEE', 5  );
define(  'SAFARI_VERSION_LAST'           , 8  ); define(  'SAFARI_URL_DOWNLOAD'          , 'http://www.apple.com/fr/safari/'); // plus téléchargeable ? Pour Windows, devel stoppé version 5.1.7.

define('EXPLORER_VERSION_MINI_REQUISE'   , 8  ); define('EXPLORER_TEXTE_MINI_REQUIS'     , 'Version 8 minimum (sortie en 2009) <span class="danger">mais usage déconseillé</span> (surtout avant la version 9).');
define('EXPLORER_VERSION_MINI_CONSEILLEE', 9  );
define('EXPLORER_VERSION_LAST'           ,11  ); define('EXPLORER_URL_DOWNLOAD'          , 'http://windows.microsoft.com/fr-fr/internet-explorer/download-ie');

class Browser
{

  public static $tab_navigo = array(
    'chrome'   => 'Chrome' ,
    'firefox'  => 'Firefox' ,
    'edge'     => 'Edge' ,
    'opera'    => 'Opéra' ,
    'safari'   => 'Safari' ,
    'explorer' => 'Internet Explorer'
  );

  // //////////////////////////////////////////////////
  // Méthode privée (interne)
  // //////////////////////////////////////////////////

  /**
   * PHP CSS Browser Selector v0.0.1
   * @author Bastian Allgeier (http://bastian-allgeier.de)
   * http://bastian-allgeier.de/css_browser_selector
   * License: http://creativecommons.org/licenses/by/2.5/
   * Credits: This is a php port from Rafael Lima's original Javascript CSS Browser Selector: http://rafael.adm.br/css_browser_selector
   * 
   * Autre solution intéressante mais lourde :
   * https://github.com/GaretJax/phpbrowscap (http://tempdownloads.browserscap.com/)
   *
   * Fonction originale réécrite et modifiée pour SACoche par Thomas Crespin.
   * @param string   $UserAgent   facultatif
   * @return array                array( 'modele' , 'version' );
   */
  private static function css_selector($UserAgent=NULL)
  {
    $tab_retour = array( 'modele'=>'' , 'version'=>0 );
    // Variable à analyser
    $UserAgent = ($UserAgent) ? strtolower($UserAgent) : ( isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '' ) ; // Pas Clean::lower() car appelé depuis ./_js/video.js.php
    // Détection du navigateur et si possible de sa version
    if(strstr($UserAgent,'Edge'))
    {
      $tab_retour['modele']  = 'edge';
      $tab_retour['version'] = (preg_match('#Edge/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif( (!preg_match('#opera|webtv#', $UserAgent)) && (strstr($UserAgent,'msie')) )
    {
      $tab_retour['modele']  = 'explorer';
      $tab_retour['version'] = (preg_match('#msie\s([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    // IE11 ne contient plus "MSIE" pour ne pas se faire repérer comme étant IE ! Alors on cherche aussi "Trident" (IE8+).
    elseif( (!preg_match('#opera|webtv#', $UserAgent)) && (strstr($UserAgent,'trident')) )
    {
      $tab_retour['modele']  = 'explorer';
      $tab_retour['version'] = (preg_match('#rv:([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'firefox'))
    {
      $tab_retour['modele']  = 'firefox';
      $tab_retour['version'] = (preg_match('#firefox/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'iceweasel'))
    {
      $tab_retour['modele']  = 'firefox';
      $tab_retour['version'] = (preg_match('#iceweasel/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'icecat'))
    {
      $tab_retour['modele']  = 'firefox';
      $tab_retour['version'] = (preg_match('#icecat/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'gecko/'))
    {
      $tab_retour['modele']  = 'gecko';
    }
    elseif(strstr($UserAgent,'opera'))
    {
      $tab_retour['modele']  = 'opera';
      $tab_retour['version'] = (preg_match('#opera(\s|\/)([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[2] : 0 ;
      $tab_retour['version'] = (preg_match('#version/([0-9]+\.?[0-9]*)#', $UserAgent, $array)) ? $array[1] : $tab_retour['version'] ;
    }
    elseif(strstr($UserAgent,'konqueror'))
    {
      $tab_retour['modele']  = 'konqueror';
    }
    elseif(strstr($UserAgent,'chrome'))
    {
      $tab_retour['modele']  = 'chrome';
      $tab_retour['version'] = (preg_match('#chrome/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;

    }
    elseif(strstr($UserAgent,'iron'))
    {
      $tab_retour['modele']  = 'chrome';
    }
    elseif(strstr($UserAgent,'applewebkit/'))
    {
      $tab_retour['modele']  = 'safari';
      $tab_retour['version'] = (preg_match('#version\/([0-9]+\.?[0-9]*)#', $UserAgent, $array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'mozilla'))
    {
      $tab_retour['modele']  = 'gecko';
    }
    // Envoi du résultat
    return $tab_retour;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  public static function afficher_navigateurs_modernes()
  {
    $tab_chaine = array();
    foreach(Browser::$tab_navigo as $navigo_ref => $navigo_name)
    {
      $tab_chaine[$navigo_ref] = '<a target="_blank" rel="noopener noreferrer" href="'.constant(strtoupper($navigo_ref).'_URL_DOWNLOAD').'"><span class="navigo navigo_'.$navigo_ref.'">'.ucfirst($navigo_ref).' '.constant(strtoupper($navigo_ref).'_VERSION_LAST').'</span></a>'; // Pas Clean::upper() car appelé depuis ./_js/video.js.php
    }
    // Affichage
    return $tab_chaine;
  }

  /*
   * Méthode pour renvoyer les infos concernant le navigateur utilisé.
   * 
   * @param void
   * @return array   array( 'modele'=>... , 'version'=>... , 'alerte'=>... )
   */
  public static function caracteristiques_navigateur()
  {
    $tab_return = Browser::css_selector(); // array( 'modele' , 'version' );
    $alerte = '';
    foreach(Browser::$tab_navigo as $navigo_ref => $navigo_name)
    {
      if($tab_return['modele']==$navigo_ref)
      {
        $version_mini_requise    = constant(strtoupper($navigo_ref).'_VERSION_MINI_REQUISE'); // Pas Clean::upper() car appelé depuis ./_js/video.js.php
        $version_mini_conseillee = constant(strtoupper($navigo_ref).'_VERSION_MINI_CONSEILLEE'); // Pas Clean::upper() car appelé depuis ./_js/video.js.php
        if($tab_return['version']<$version_mini_requise)
        {
          $alerte = 'Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! '.$navigo_name.' est utilisable à partir de sa version '.$version_mini_requise.'.';
        }
        elseif($tab_return['version']<$version_mini_conseillee)
        {
          $alerte = ($navigo_ref=='explorer') ? 'Votre navigateur dysfonctionne ! L\'usage d\'Internet Explorer est déconseillé avant sa version 9.' : 'Votre navigateur est dépassé ! Utilisez une version récente pour une navigation plus sure, rapide et efficace.' ;
        }
      }
    }
    $tab_return['alerte'] = ($alerte) ? $alerte.'<br />Installez '.implode(' ou ',Browser::afficher_navigateurs_modernes()).'.' : NULL ;
    return $tab_return;
  }

}
?>