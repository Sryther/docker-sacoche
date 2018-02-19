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

class Layout
{

  /**
   * Quelques constantes ; on ne peut pas concaténer ou faire référence à d'autres choses lors de leur définition sauf à une constante.
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   */
  const SITE_NAME          = 'SACoche';
  const TITLE_SEPARATOR    = ' » ';
  const META_DESCRIPTION   = 'Logiciel libre et gratuit de suivi d\'acquisition de compétences, pour évaluer par compétences et positionner sur le socle commun.';
  const META_KEYWORDS      = 'Sésamath évaluer évaluation compétences compétence logiciel libre gratuit socle commun collège école points note notes Lomer';
  const META_AUTHOR        = 'Thomas Crespin pour Sésamath';
  const META_ROBOTS        = 'index,follow';
  const FLUX_RSS_LINK      = SERVEUR_RSS;
  const FRAME_RESTRICTION  = FALSE; // FALSE | 'DENY' | 'SAMEORIGIN' -> Pour interdire sans sommation ni explication l'affichage dans un cadre.
  const OPENGRAPH_URL_BASE = NULL; // Non définissable ici ; voir inserer_meta_opengraph()
  const OPENGRAPH_IMAGE    = '/_img/logo_grand.gif';
  const DOSSIER_TMP_URL    = NULL; // Non définissable ici ; voir compacter()
  const DOSSIER_TMP_CHEMIN = NULL; // Non définissable ici ; voir compacter()

  /**
   * Attributs de la classe (équivalents des "variables")
   */
  private static $head_browser_title   = Layout::SITE_NAME; // Titre (par défaut / provisoire) du navigateur
  private static $head_base            = NULL;    // Eventuel <base> dans le head
  private static $tab_css_file         = array(); // CSS Fichiers
  private static $tab_css_file_ie      = array(); // CSS Fichiers réservés à IE antérieur à une version donnée
  private static $tab_css_inline       = array(); // CSS Inline
  private static $tab_js_file          = array(); //  JS Fichiers
  private static $tab_js_file_ie       = array(); //  JS Fichiers réservés à IE antérieur à une version donnée
  private static $tab_js_config        = array(); //  JS de config avec un type particulier
  private static $tab_js_inline_before = array(); //  JS Inline avant fichiers JS
  private static $tab_js_inline_after  = array(); //  JS Inline après fichiers JS
  private static $is_meta_charset      = NULL;    // insertion (ou pas) de la balise meta du charset ; voir commentaire dans la méthode concernée
  private static $is_meta_robots       = NULL;    // insertion (ou pas) des balises meta pour les robots
  private static $is_opengraph         = NULL;    // insertion (ou pas) des balises meta pour le protocole Open Graph
  private static $is_favicon           = NULL;    // insertion (ou pas) des favicon
  private static $is_rss               = NULL;    // insertion (ou pas) du flux RSS
  private static $is_add_noscript      = NULL;    // pour ajouter une balise <noscript> au début du <body>
  private static $is_server_xss_head   = NULL;    // pour indiquer si le serveur est déjà configuré pour envoyer un header "X-XSS-Protection"
  private static $is_minify            = NULL;    // pour indiquer s'il faut minifier les js / css
  private static $body_class           = NULL;    // classe (facultative) de l'élément <body>

  /**
   * Méthode configurer ce qu'il faut afficher dans le <head>
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @param string $config   'light' | 'portail' | 'prog-touch' | 'prog-mouse'
   * @return void
   */
  private static function config_params_head($config)
  {
    if($config=='light')
    {
      Layout::$is_meta_charset    = FALSE;
      Layout::$is_meta_robots     = FALSE;
      Layout::$is_opengraph       = FALSE;
      Layout::$is_favicon         = TRUE;
      Layout::$is_rss             = FALSE;
      Layout::$is_add_noscript    = TRUE;
      Layout::$is_server_xss_head = TRUE;
      Layout::$body_class         = '';
    }
    else
    {
      Layout::$is_meta_charset    = FALSE;
      Layout::$is_meta_robots     = TRUE;
      Layout::$is_opengraph       = TRUE;
      Layout::$is_favicon         = TRUE;
      Layout::$is_rss             = TRUE;
      Layout::$is_server_xss_head = (IS_HEBERGEMENT_SESAMATH) ? TRUE : FALSE ;
      Layout::$is_add_noscript    = ($config=='portail') ? TRUE : FALSE ; // Pour l'appli la ligne est déjà incluse à un endroit plus approprié
      Layout::$body_class         = ($config=='portail') ? '' : ' class="'.substr($config,5).'"' ;
    }
  }

  /**
   * Méthode pour renvoyer la valeur du header Content Security Policy
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
   * @see http://www.w3.org/TR/CSP/#directives
   * 
   * @param void
   * @return string
   */
  private static function header_CSP_directives()
  {
    $tab_CSP_directives = array();
    // Scripts JS ; "unsafe-eval" requis si usage de compression js avec la méthode "pack" ; URL_SSL requis pour la bannière rotative
    $tab_CSP_directives[] = (!defined('APPEL_SITE_PROJET')) ? "script-src 'self' 'unsafe-inline' 'unsafe-eval'" : "script-src 'self' 'unsafe-inline' 'unsafe-eval' ".URL_SSL ;
    // Styles CSS ; URL_SSL requis pour la bannière rotative
    $tab_CSP_directives[] = (!defined('APPEL_SITE_PROJET')) ? "style-src 'self' 'unsafe-inline'" :  "style-src 'self' 'unsafe-inline' ".URL_SSL;
    // Images
    $tab_CSP_directives[] = "img-src 'self' 'unsafe-inline' data:";
    // Appels ajax
    $tab_CSP_directives[] = "connect-src 'self'";
    // Cadres (frames) ; est requis pour le js AjaxUpload ; peut être requis pour le js Fancybox
    $modules = empty($_SESSION['MODULE']) ? '' : ' '.implode(' ',$_SESSION['MODULE']) ;
    $tab_CSP_directives[] = "child-src 'self' ".SERVEUR_LSU_PDF.$modules;
    // Si audio ou vidéo
    $tab_CSP_directives[] = "media-src 'self' data:";
    // Si object ou applet ; requis pour du flash (IEP, TEP, MEP...)
    $tab_CSP_directives[] = "object-src 'none'";
    // Si font
    $tab_CSP_directives[] = "font-src 'none'";
    // Adresse d'un parser des rapports de blocages
    if(IS_HEBERGEMENT_SESAMATH)
    {
      $tab_CSP_directives[] = "report-uri ".SERVEUR_ASSO."/csp-alert.php";
    }
    // on concatène
    return implode(' ; ',$tab_CSP_directives);
  }

  /**
   * Méthode pour afficher les favicon et autres icones pour divers dispositifs
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @param void
   * @return string
   */
  private static function inserer_link_favicon()
  {
    $string = '';
    // @see http://realfavicongenerator.net/
    $string.= '<meta name="msapplication-TileColor" content="#e6e6ff" />'.NL;
    $string.= '<meta name="msapplication-TileImage" content="/_img/favicon/microsoft-tile-square-144x144.png" />'.NL;
    $string.= '<meta name="msapplication-square70x70logo"   content="/_img/favicon/microsoft-tile-square-70x70.png" />'.NL;
    // Pb validité HTML : Bad value msapplication-square144x144logo for attribute name on element meta: Keyword msapplication-square144x144logo is not registered (ce qui peut se vérifier ici : http://wiki.whatwg.org/wiki/MetaExtensions).
    // $string.= '<meta name="msapplication-square144x144logo" content="/_img/favicon/microsoft-tile-square-144x144.png" />'.NL;
    $string.= '<meta name="msapplication-square150x150logo" content="/_img/favicon/microsoft-tile-square-150x150.png" />'.NL;
    $string.= '<meta name="msapplication-square310x310logo" content="/_img/favicon/microsoft-tile-square-310x310.png" />'.NL;
    $string.= '<meta name="msapplication-wide310x150logo"   content="/_img/favicon/microsoft-tile-wide-310x150.png" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./favicon.png" />'.NL;
    $string.= '<link rel="shortcut icon"  type="images/x-icon" href="./_img/favicon/favicon.ico" />'.NL;
    $string.= '<link rel="apple-touch-icon-precomposed"     href="./_img/favicon/apple-touch-icon-114x114.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="57x57"   href="./_img/favicon/apple-touch-icon-57x57.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="114x114" href="./_img/favicon/apple-touch-icon-114x114.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="72x72"   href="./_img/favicon/apple-touch-icon-72x72.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="144x144" href="./_img/favicon/apple-touch-icon-144x144.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="60x60"   href="./_img/favicon/apple-touch-icon-60x60.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="120x120" href="./_img/favicon/apple-touch-icon-120x120.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="76x76"   href="./_img/favicon/apple-touch-icon-76x76.png" />'.NL;
    $string.= '<link rel="apple-touch-icon" sizes="152x152" href="./_img/favicon/apple-touch-icon-152x152.png" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./_img/favicon/favicon-196x196.png" sizes="196x196" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./_img/favicon/favicon-160x160.png" sizes="160x160" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./_img/favicon/favicon-96x96.png" sizes="96x96" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./_img/favicon/favicon-32x32.png" sizes="32x32" />'.NL;
    $string.= '<link rel="icon" type="image/png" href="./_img/favicon/favicon-16x16.png" sizes="16x16" />'.NL;
    return $string;
  }

  /**
   * Méthode pour déclarer "automatiquement" des fichiers javascript associés aux pages
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @param void
   * @return void
   */
  private static function declarer_js_commun()
  {
    // Sans objet pour ce domaine
  }

  /**
   * Méthode pour déclarer "automatiquement" un fichier javascript associé à une page précise
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @param void
   * @return void
   */
  private static function declarer_js_page()
  {
    // Sans objet pour ce domaine
  }

  /**
   * Méthode pour insérer un texte en pied de page
   * 
   * ****************************************************
   * ***** CONTENU A ADAPTER EN FONCTION DU DOMAINE *****
   * ****************************************************
   * 
   * @param void
   * @return string
   */
  private static function inserer_page_pied()
  {
    // Sans objet pour ce domaine
  }

  /**
   * Méthode pour afficher une balise <meta> pour le charset
   * 
   * Les metas http-equiv servent à redéfinir une valeur normalement définie dans un en-tête HTTP.
   * Leur utilisation n'est pas justifiée si l'en-tête HTTP est déjà correctement renseigné (selon www.dareboost.com).
   * Cela peut toutefois être utile en cas d'enregistrement de la page pour une consultation hors-ligne (selon validator.w3.org).
   * 
   * @param void
   * @return string
   */
  private static function inserer_meta_charset()
  {
    return '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'.NL;
  }

  /**
   * Méthode pour afficher les balises principales <meta> utilisées par les robots
   * 
   * Ce type de données permet de fournir des informations aux réseaux sociaux, notamment Facebook.
   * @see http://ogp.me/
   * 
   * @param void
   * @return string
   */
  private static function inserer_meta_robots()
  {
    $string = '';
    $string.= '<meta name="description" content="'. Layout::SITE_NAME . Layout::TITLE_SEPARATOR . Layout::META_DESCRIPTION .'" />'.NL;
    $string.= '<meta name="keywords" content="'. Layout::SITE_NAME . ' ' . Layout::META_KEYWORDS .'" />'.NL;
    $string.= '<meta name="author" content="'.Layout::META_AUTHOR.'" />'.NL;
    $string.= '<meta name="robots" content="'.Layout::META_ROBOTS.'" />'.NL;
    return $string;
  }

  /**
   * Méthode pour afficher les balises META du Protocole Open Graph
   * 
   * Ce type de données permet de fournir des informations aux réseaux sociaux, notamment Facebook.
   * @see http://ogp.me/
   * 
   * @param void
   * @return string
   */
  private static function inserer_meta_opengraph()
  {
    $url = defined('APPEL_SITE_PROJET') ? URL_BASE : URL_INSTALL_SACOCHE ; /* /!\ modif classe Sésamath /!\ */
    $string = '';
    $string.= '<meta property="og:title" content="'.Layout::$head_browser_title.'" />'.NL;
    $string.= '<meta property="og:type" content="website" />'.NL;
    $string.= '<meta property="og:url" content="'. $url . html($_SERVER['REQUEST_URI']) .'" />'.NL; /* /!\ modif classe Sésamath /!\ */
    $string.= '<meta property="og:image" content="'. SERVEUR_PROJET . Layout::OPENGRAPH_IMAGE .'" />'.NL; /* /!\ modif classe Sésamath /!\ */
    $string.= '<meta property="og:locale" content="fr_FR" />'.NL;
    $string.= '<meta property="og:site_name" content="'.Layout::SITE_NAME.'" />'.NL;
    return $string;
  }

  /**
   * Méthode pour insérer un flux RSS
   * 
   * @param void
   * @return string
   */
  private static function inserer_link_rss()
  {
    return Layout::FLUX_RSS_LINK ? '<link rel="alternate" type="application/rss+xml" href="'.Layout::FLUX_RSS_LINK.'" title="'.Layout::SITE_NAME.'" />'.NL : '' ;
  }

  /**
   * Méthode pour insérer le titre de la page
   * 
   * @param void
   * @return string
   */
  private static function inserer_head_title()
  {
    return '<title>' . Layout::SITE_NAME . Layout::TITLE_SEPARATOR . Layout::$head_browser_title . '</title>' . NL;
  }

  /**
   * Méthode pour insérer une balise <base>
   * 
   * @param void
   * @return string
   */
  private static function inserer_head_base()
  {
    return '<base href="'.Layout::$head_base.'" />'.NL;
  }

  /**
   * Méthode pour minifier un fichier css ou js
   * 
   * Minification d'un fichier css ou js sur le serveur en production, avec date auto-insérée si besoin pour éviter tout souci de mise en cache.
   * Il s'agit bien ici de "minification" et non de "compression" car la compression est à mettre en place au niveau du serveur (GZIP)
   * (donc inutile de rendre le code obscur et de donner du travail supplémentaire au navigateur, comme le Packer de Dean Edwards avec base62 activé)
   * Hors PROD on ne minifie pas pour faciliter le débugage.
   * On peut toutefois forcer la minification avec GET['minify'] afin d'en tester le bon fonctionnement.
   * Si pas de minification (hors PROD), on ajoute un GET dans l'URL pour forcer le navigateur à mettre à jour son cache.
   * Attention cependant concernant cette dernière technique : avec les réglages standards d'Apache, ajouter un GET dans l'URL fait que beaucoup de navigateurs ne mettent pas le fichier en cache (donc il est rechargé tout le temps, même si le GET est le même) ; pas de souci si le serveur envoie un header avec une date d'expiration explicite...
   * 
   * @param string $chemin  chemin complet vers le fichier
   * @param string $methode "mini" | "comm"
   * @return string         chemin vers le fichier à prendre en compte (à indiquer dans la page web) ; il sera relatif si non compressé, absolu si compressé
   */
  private static function compacter( $chemin , $methode )
  {
    if(substr($chemin,0,4)=='http')
    {
      // Cas d'un fichier distant
      return $chemin;
    }
    $fichier_original_chemin = $chemin;
    $fichier_original_date   = filemtime($fichier_original_chemin);
    $fichier_original_url    = $fichier_original_chemin.'?t='.$fichier_original_date;
    if(is_null(Layout::$is_minify))
    {
      if( defined('FORCE_MINIFY') || isset($_GET['minify']) )
      {
        Layout::$is_minify = TRUE;
      }
      else if( defined('FORCE_NO_MINIFY') || isset($_GET['no-minify']) )
      {
        Layout::$is_minify = FALSE;
      }
      else
      {
        Layout::$is_minify = (SERVEUR_TYPE == 'PROD') ? TRUE : FALSE;
      }
    }
    if(Layout::$is_minify)
    {
      // On peut se permettre d'enregistrer les js et css en dehors de leur dossier d'origine car les répertoires sont tous de mêmes niveaux.
      // En cas d'appel depuis le site du projet il faut éventuellement respecter le chemin vers le site du projet.
      $tmp_appli = ( (!defined('APPEL_SITE_PROJET')) || (strpos($chemin,'/sacoche/')!==FALSE) ) ? TRUE : FALSE ; /* /!\ modif classe Sésamath /!\ */
      // Conserver les extensions des js et css (le serveur pouvant se baser sur les extensions pour sa gestion du cache et des charsets).
      $fichier_original_extension = pathinfo($fichier_original_chemin,PATHINFO_EXTENSION);
      $fichier_chemin_sans_slash  = substr( str_replace( array('./sacoche/','./','/') , array('','','__') , $fichier_original_chemin ) , 0 , -(strlen($fichier_original_extension)+1) ); /* /!\ modif classe Sésamath /!\ */
      $fichier_compact_nom        = $fichier_chemin_sans_slash.'_'.$fichier_original_date.'.'.$methode.'.'.$fichier_original_extension;
      $fichier_compact_chemin     = ($tmp_appli) ? CHEMIN_DOSSIER_TMP.$fichier_compact_nom : CHEMIN_DOSSIER_PROJET_TMP.$fichier_compact_nom ; /* /!\ modif classe Sésamath /!\ */
      $fichier_compact_url        = ($tmp_appli) ?        URL_DIR_TMP.$fichier_compact_nom :        URL_DIR_PROJET_TMP.$fichier_compact_nom ; /* /!\ modif classe Sésamath /!\ */
      $fichier_compact_date       = (is_file($fichier_compact_chemin)) ? filemtime($fichier_compact_chemin) : 0 ;
      // Sur le serveur en production, on minifie le fichier s'il ne l'est pas.
      if($fichier_compact_date<$fichier_original_date)
      {
        $fichier_original_contenu = file_get_contents($fichier_original_chemin);
        $fichier_original_contenu = utf8_decode($fichier_original_contenu); // Attention, il faut envoyer à ces classes de l'iso et pas de l'utf8.
        if( ($fichier_original_extension=='js') && ($methode=='mini') )
        {
          $jSqueeze = new JSqueeze();
          $fichier_compact_contenu = $jSqueeze->squeeze( $fichier_original_contenu , TRUE /*singleLine*/ , FALSE /*keepImportantComments*/ );
        }
        elseif( ($fichier_original_extension=='js') && ($methode=='comm') )
        {
          // Retrait des commentaires // ... et /* ... */ et /** ... */ et /*! ... */
          // Option de recherche "s" (PCRE_DOTALL) pour inclure les retours à la lignes (@see http://fr.php.net/manual/fr/reference.pcre.pattern.modifiers.php).
          $fichier_compact_contenu = trim(
            preg_replace( '#'.'(\n)+'.'#s' , "\n" , 
            preg_replace( '#'.'// '.'(.*?)'.'\n'.'#s' , '' , 
            preg_replace( '#'.'/\*'.'(.*?)'.'\*/'.'#s' , '' , 
            preg_replace( '#'.'/\*!'.'(.*?)'.'\*/'.'#s' , '' , 
            preg_replace( '#'.'/\*\*'.'(.*?)'.'\*/'.'#s' , '' , 
            $fichier_original_contenu ) ) ) ) ) );
        }
        elseif( ($fichier_original_extension=='css') && ($methode=='mini') )
        {
          $fichier_compact_contenu = cssmin::minify($fichier_original_contenu);
        }
        else
        {
          // Normalement on ne doit pas en arriver là... sauf à passer de mauvais paramètres à la fonction.
          $fichier_compact_contenu = $fichier_original_contenu;
        }
        $fichier_compact_contenu = utf8_encode($fichier_compact_contenu);  // On réencode donc en UTF-8...
        // Il se peut que le droit en écriture ne soit pas autorisé et que la procédure d'install ne l'ai pas encore vérifié ou que le dossier __tmp n'ait pas encore été créé.
        $test_ecriture = FileSystem::ecrire_fichier_si_possible($fichier_compact_chemin,$fichier_compact_contenu); /* /!\ modif classe Sésamath /!\ */
        return $test_ecriture ? $fichier_compact_url : $fichier_original_url ; /* /!\ modif classe Sésamath /!\ */
      }
      return $fichier_compact_url;
    }
    else
    {
      // Sur un serveur local on n'encombre pas le SVN, en DEV on garde le fichier normal pour debugguer si besoin.
      return $fichier_original_url;
    }
  }

  /**
   * Méthode pour insérer les fichiers CSS
   * 
   * @param void
   * @return string
   */
  private static function inserer_css_file()
  {
    $string = '';
    if(!empty(Layout::$tab_css_file))
    {
      foreach(Layout::$tab_css_file as $css_file)
      {
        $string .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'" />'.NL;
      }
    }
    if(!empty(Layout::$tab_css_file_ie))
    {
      foreach(Layout::$tab_css_file_ie as $css_ie_file)
      {
        $string .= '<!--[if lte IE '.$css_ie_file['version'].']>';
        $string .= '<link rel="stylesheet" type="text/css" href="'.$css_ie_file['source'].'" />';
        $string .= '<![endif]-->'.NL;
      }
    }
    return $string;
  }

  /**
   * Méthode pour insérer les fichiers JS
   * 
   * @param void
   * @return string
   */
  private static function inserer_js_file()
  {
    $string = '';
    if(!empty(Layout::$tab_js_file_ie))
    {
      foreach(Layout::$tab_js_file_ie as $js_ie_file)
      {
        $string .= '<!--[if lte IE '.$css_ie_file['version'].']>';
        $string .= '<script type="text/javascript" charset="'.CHARSET.'" src="'.$js_ie_file['source'].'"></script>';
        $string .= '<![endif]-->'.NL;
      }
    }
    if(!empty(Layout::$tab_js_file))
    {
      foreach(Layout::$tab_js_file as $js_file)
      {
        $string .= '<script type="text/javascript" charset="'.CHARSET.'" src="'.$js_file.'"></script>'.NL;
      }
    }
    return $string;
  }

  /**
   * Méthode pour insérer les fichiers JS de configuration
   * 
   * @param void
   * @return string
   */
  private static function inserer_js_config()
  {
    $string = '';
    if(!empty(Layout::$tab_js_config))
    {
      foreach(Layout::$tab_js_config as $js_config)
      {
        $string .= '<script type="'.$js_config['script_type'].'">'.$js_config['contenu'].'</script>'.NL;
      }
    }
    return $string;
  }

  /**
   * Méthode pour insérer les CSS dans la page (personnalisations propres à la page ou à la session)
   * 
   * @param void
   * @return string
   */
  private static function inserer_css_inline()
  {
    $string = '';
    if(!empty(Layout::$tab_css_inline))
    {
      $string_css_inline = implode(NL,Layout::$tab_css_inline);
      $string .= '<style type="text/css">'.NL;
      $string .= (SERVEUR_TYPE == 'PROD') ? cssmin::minify($string_css_inline).NL : $string_css_inline.NL ;
      $string .= '</style>'.NL;
    }
    return $string;
  }

  /**
   * Méthode pour insérer les JS dans la page (données dynamiques, telles des constantes supplémentaires)
   * 
   * @param string   position   'before' | 'after'
   * @return string
   */
  private static function inserer_js_inline($position)
  {
    if(!empty(Layout::${'tab_js_inline_'.$position}))
    {
      $string_js_inline = implode(NL,Layout::${'tab_js_inline_'.$position});
      if(SERVEUR_TYPE == 'PROD')
      {
        $jSqueeze = new JSqueeze();
        return '<script type="text/javascript">'.$jSqueeze->squeeze( $string_js_inline , TRUE /*singleLine*/ , FALSE /*keepImportantComments*/ ).'</script>'.NL;
      }
      else
      {
        return '<script type="text/javascript">'.NL.$string_js_inline.NL.'</script>'.NL;
      }
    }
    return '';
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes publiques
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Méthode ressemblant à un __set()
   * 
   * @param string       $type      voir les types possibles dans la fonction
   * @param string|array $contenu   la chaine (si type "inline") ou le chemin du fichier (si type "file")
   * @param string|int   $info      "mini" | "comm" | type de script | version de IE | NULL
   * @return void
   */
  public static function add( $type , $contenu , $info=NULL )
  {
    switch($type)
    {
      case 'browser_title';
        Layout::$head_browser_title = $contenu;
        break;
      case 'base';
        Layout::$head_base = $contenu;
        break;
      case 'css_inline';
      case  'js_inline_before';
      case  'js_inline_after';
        Layout::${'tab_'.$type}[] = $contenu;
        break;
      case 'css_file';
      case  'js_file';
        Layout::${'tab_'.$type}[] = Layout::compacter($contenu,$info);
        break;
      case 'css_file_ie';
      case  'js_file_ie';
        Layout::${'tab_'.$type}[] = array( 'version'=>$info , 'source'=>Layout::compacter($contenu,NULL) );
        break;
      case  'js_config';
        Layout::$tab_js_config[] = array( 'script_type'=>$info , 'contenu'=>$contenu );
        break;
    }
  }

  /**
   * Méthode pour afficher l'ouverture de la déclaration HTML & la section HEAD du document & l'ouverture de la balise BODY
   * 
   * @param string $config
   * @return string
   */
  public static function afficher_page_entete($config='defaut')
  {
    Layout::config_params_head($config);
    Layout::declarer_js_commun();
    // type de données et charset
    header('Content-Type: text/html; charset='.CHARSET);
    // mode de compatibilité sur IE
    // @see http://www.alsacreations.com/astuce/lire/1437-comment-interdire-le-mode-de-compatibilite-sur-ie.html
    header('X-UA-Compatible: IE=edge');
    // Content Security Policy
    // @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/ & http://www.w3.org/TR/CSP/#directives
    header('Content-Security-Policy: '.Layout::header_CSP_directives());
    // Réduire l'exposition aux attaques du type "clickjacking" / "framesniffing"
    // Attention : cela donne une page blanche en cas d'inclusion dans un frame, sans avertissement à l'utilisateur
    // @see http://www.cert.ssi.gouv.fr/site/CERTA-2011-ACT-007/ & https://www.owasp.org/index.php/Clickjacking
    if(Layout::FRAME_RESTRICTION)
    {
      header('X-Frame-Options: '.Layout::FRAME_RESTRICTION);
    }
    // Contrer l'exploitation malveillante du MIME Sniffing.
    // Cependant, ici cela ne s'applique qu'au fichier PHP, ce qui est peu intéressant : mieux vaut le configurer au niveau du serveur (pour les images etc.)
    // @see https://www.owasp.org/index.php/List_of_useful_HTTP_headers
    header('X-Content-Type-Options: nosniff');
    // S'assurer que le navigateur du client fasse son maximum pour prévenir d'une attaque de type XSS
    // @see https://www.owasp.org/index.php/List_of_useful_HTTP_headers
    if(!Layout::$is_server_xss_head)
    {
      header('X-XSS-Protection: 1; mode=block');
    }
    // on passe au contenu
    $retour = '';
    $retour.= '<!DOCTYPE html>'.NL;
    $retour.= '<html lang="fr">'.NL;
    $retour.= '<head>'.NL;
    if(Layout::$is_meta_charset)
    {
      $retour.= Layout::inserer_meta_charset();
    }
    if(Layout::$is_meta_robots)
    {
      $retour.= Layout::inserer_meta_robots();
    }
    // opengraph
    if(Layout::$is_opengraph)
    {
      $retour.= Layout::inserer_meta_opengraph();
    }
    // icones
    if(Layout::$is_favicon)
    {
      $retour.= Layout::inserer_link_favicon();
    }
    // rss
    if(Layout::$is_rss)
    {
      $retour.= Layout::inserer_link_rss();
    }
    // title
    $retour.= Layout::inserer_head_title();
    // base
    if(Layout::$head_base)
    {
      $retour.= Layout::inserer_head_base();
    }
    $retour.= Layout::inserer_css_file();
    $retour.= Layout::inserer_css_inline();
    $retour.= '</head>'.NL;
    $retour.= '<body'.Layout::$body_class.'>'.NL;
    if(Layout::$is_add_noscript)
    {
      $retour.= '<noscript>Pour afficher convenablement cette page, vous devez activer JavaScript dans votre navigateur.</noscript>'.NL;
    }
    return $retour;
  }

  /**
   * Méthode pour afficher la fermeture de la balise BODY & la fermeture de la déclaration HTML
   * 
   * @param bool   $is_js_page  insertion (ou pas) d'un fichier javascript associé à la page
   * @return string
   */
  public static function afficher_page_pied( $is_js_page=FALSE )
  {
    if($is_js_page)
    {
      Layout::declarer_js_page();
    }
    $retour = '';
    $retour.= Layout::inserer_page_pied();
    $retour.= Layout::inserer_js_inline('before');
    $retour.= Layout::inserer_js_config();
    $retour.= Layout::inserer_js_file();
    $retour.= Layout::inserer_js_inline('after');
    $retour.= '</body>'.NL;
    $retour.= '</html>'.NL;
    return $retour;
  }

}
?>
