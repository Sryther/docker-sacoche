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

/**
 * @see ../info_gettext.txt pour info
 */

require_once('streams.php');
require_once('gettext.php');

/* Class to hold a single domain included in $text_domains. */
class domain {
  var $l10n;
  var $path;
  var $codeset;
}

/**
 * Class Lang
 */
class Lang
{

  // //////////////////////////////////////////////////
  // Attributs
  // //////////////////////////////////////////////////

  private static $text_domains = array();

  private static $default_domain = 'messages';

  private static $tab_lc_categories = array('LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_MESSAGES', 'LC_ALL');

  private static $current_locale = '';

  // //////////////////////////////////////////////////
  // Méthodes privées
  // //////////////////////////////////////////////////

  /**
   * Return a list of locales to try for any POSIX-style locale specification.
   */
  private static function get_list_of_locales($locale) {
    /* Figure out all possible locale names and start with the most
     * specific ones.  I.e. for sr_CS.UTF-8@latin, look through all of
     * sr_CS.UTF-8@latin, sr_CS@latin, sr@latin, sr_CS.UTF-8, sr_CS, sr.
     */
    $locale_names = array();
    $lang = NULL;
    $country = NULL;
    $charset = NULL;
    $modifier = NULL;
    if ($locale) {
      if (preg_match("/^(?P<lang>[a-z]{2,3})"                 // language code
                    ."(?:_(?P<country>[A-Z]{2}))?"            // country code
                    ."(?:\.(?P<charset>[-A-Za-z0-9_]+))?"     // charset
                    ."(?:@(?P<modifier>[-A-Za-z0-9_]+))?$/",  // @ modifier
                    $locale, $matches)) {
        if (isset($matches["lang"])) $lang = $matches["lang"];
        if (isset($matches["country"])) $country = $matches["country"];
        if (isset($matches["charset"])) $charset = $matches["charset"];
        if (isset($matches["modifier"])) $modifier = $matches["modifier"];
        if ($modifier) {
          if ($country) {
            if ($charset)
              array_push($locale_names, "${lang}_$country.$charset@$modifier");
            array_push($locale_names, "${lang}_$country@$modifier");
          } elseif ($charset)
              array_push($locale_names, "${lang}.$charset@$modifier");
          array_push($locale_names, "$lang@$modifier");
        }
        if ($country) {
          if ($charset)
            array_push($locale_names, "${lang}_$country.$charset");
          array_push($locale_names, "${lang}_$country");
        } elseif ($charset)
            array_push($locale_names, "${lang}.$charset");
        array_push($locale_names, $lang);
      }
      // If the locale name doesn't match POSIX style, just include it as-is.
      if (!in_array($locale, $locale_names))
        array_push($locale_names, $locale);
    }
    return $locale_names;
  }

  /**
   * Utility function to get a StreamReader for the given text domain.
   */
  private static function get_reader($domain=null, $category=5, $enable_cache=true) {
    if (!isset($domain)) $domain = Lang::$default_domain;
    if (!isset(Lang::$text_domains[$domain]->l10n)) {
      // get the current locale
      $locale = Lang::setlocale(LC_MESSAGES, 0);
      $bound_path = isset(Lang::$text_domains[$domain]->path) ? Lang::$text_domains[$domain]->path : './';
      $subpath = Lang::$tab_lc_categories[$category] ."/$domain.mo";
      $locale_names = Lang::get_list_of_locales($locale);
      $input = null;
      foreach ($locale_names as $locale) {
        $full_path = $bound_path . $locale . "/" . $subpath;
        if (file_exists($full_path)) {
          $input = new FileReader($full_path);
          break;
        }
      }
      if (!array_key_exists($domain, Lang::$text_domains)) {
        // Initialize an empty domain object.
        Lang::$text_domains[$domain] = new domain();
      }
      Lang::$text_domains[$domain]->l10n = new gettext_reader($input, $enable_cache);
    }
    return Lang::$text_domains[$domain]->l10n;
  }

  /**
   * Get the codeset for the given domain.
   */
  private static function get_codeset($domain=null) {
    if (!isset($domain)) $domain = Lang::$default_domain;
    return (isset(Lang::$text_domains[$domain]->codeset))? Lang::$text_domains[$domain]->codeset : ini_get('mbstring.internal_encoding');
  }

  /**
   * Convert the given string to the encoding set by bind_textdomain_codeset.
   */
  private static function encode($text) {
    $target_encoding = Lang::get_codeset();
    if (function_exists("mb_detect_encoding")) {
      $source_encoding = mb_detect_encoding($text);
      if ($source_encoding != $target_encoding)
        $text = mb_convert_encoding($text, $target_encoding, $source_encoding);
    }
    return $text;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Retourne la $locale à utiliser (méthode ajoutée).
   */
  public static function get_locale_used()
  {
    if(!empty($_SESSION['USER_LANGUE']))
    {
      return $_SESSION['USER_LANGUE'];
    }
    else if (!empty($_SESSION['ETABLISSEMENT']['LANGUE']))
    {
      return $_SESSION['ETABLISSEMENT']['LANGUE'];
    }
    else
    {
      return LOCALE_DEFAULT;
    }
  }

  // Custom implementation of the standard gettext related functions

  /**
   * Returns passed in $locale, or environment variable $LANG if $locale == ''.
   */
  public static function get_default_locale($locale) {
    if ($locale == '') // emulate variable support
      return getenv('LANG');
    else
      return $locale;
  }

  /**
   * Sets a requested locale, if needed emulates it.
   */
  public static function setlocale($category, $locale) {
    if ($locale === 0) { // use === to differentiate between string "0"
      if (Lang::$current_locale != '')
      return Lang::$current_locale;
      else
        // obey LANG variable, maybe extend to support all of LC_* vars
        // even if we tried to read locale without setting it first
        return Lang::setlocale($category, Lang::$current_locale);
    } else {
      if (function_exists('setlocale')) {
      $ret = setlocale($category, $locale);
      if (($locale == '' and !$ret) or // failed setting it by env
        ($locale != '' and $ret != $locale)) { // failed setting it
        // Failed setting it according to environment.
        Lang::$current_locale = Lang::get_default_locale($locale);
      } else {
        Lang::$current_locale = $ret;
      }
      } else {
      // No function setlocale(), emulate it all.
      Lang::$current_locale = Lang::get_default_locale($locale);
      }
      // Allow locale to be changed on the go for one translation domain.
      if (array_key_exists(Lang::$default_domain, Lang::$text_domains)) {
        unset(Lang::$text_domains[Lang::$default_domain]->l10n);
      }
      return Lang::$current_locale;
    }
  }

  /**
   * Sets the path for a domain.
   */
  public static function bindtextdomain($domain, $path) {
    // ensure $path ends with a slash ('/' should work for both, but lets still play nice)
    if (substr(php_uname(), 0, 7) == "Windows") {
    if ($path[strlen($path)-1] != '\\' and $path[strlen($path)-1] != '/')
      $path .= '\\';
    } else {
    if ($path[strlen($path)-1] != '/')
      $path .= '/';
    }
    if (!array_key_exists($domain, Lang::$text_domains)) {
    // Initialize an empty domain object.
    Lang::$text_domains[$domain] = new domain();
    }
    Lang::$text_domains[$domain]->path = $path;
  }

  /**
   * Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
   */
  public static function bind_textdomain_codeset($domain, $codeset) {
    Lang::$text_domains[$domain]->codeset = $codeset;
  }

  /**
   * Sets the default domain.
   */
  public static function textdomain($domain) {
    Lang::$default_domain = $domain;
  }

  /**
   * Lookup a message in the current domain.
   */
  public static function gettext($msgid) {
    $l10n = Lang::get_reader();
    return Lang::encode($l10n->translate($msgid));
  }

  /**
   * Alias for gettext.
   */
  public static function _($msgid) {
    return Lang::gettext($msgid);
  }

  /**
   * Plural version of gettext.
   */
  public static function ngettext($singular, $plural, $number) {
    $l10n = Lang::get_reader();
    return Lang::encode($l10n->ngettext($singular, $plural, $number));
  }

  /**
   * Override the current domain.
   */
  public static function dgettext($domain, $msgid) {
    $l10n = Lang::get_reader($domain);
    return Lang::encode($l10n->translate($msgid));
  }

  /**
   * Plural version of dgettext.
   */
  public static function dngettext($domain, $singular, $plural, $number) {
    $l10n = Lang::get_reader($domain);
    return Lang::encode($l10n->ngettext($singular, $plural, $number));
  }

  /**
   * Overrides the domain and category for a single lookup.
   */
  public static function dcgettext($domain, $msgid, $category) {
    $l10n = Lang::get_reader($domain, $category);
    return Lang::encode($l10n->translate($msgid));
  }
  /**
   * Plural version of dcgettext.
   */
  public static function dcngettext($domain, $singular, $plural, $number, $category) {
    $l10n = Lang::get_reader($domain, $category);
    return Lang::encode($l10n->ngettext($singular, $plural, $number));
  }

  /**
   * Context version of gettext.
   */
  public static function pgettext($context, $msgid) {
    $l10n = Lang::get_reader();
    return Lang::encode($l10n->pgettext($context, $msgid));
  }

  /**
   * Override the current domain in a context gettext call.
   */
  public static function dpgettext($domain, $context, $msgid) {
    $l10n = Lang::get_reader($domain);
    return Lang::encode($l10n->pgettext($context, $msgid));
  }

  /**
   * Overrides the domain and category for a single context-based lookup.
   */
  public static function dcpgettext($domain, $context, $msgid, $category) {
    $l10n = Lang::get_reader($domain, $category);
    return Lang::encode($l10n->pgettext($context, $msgid));
  }

  /**
   * Context version of ngettext.
   */
  public static function npgettext($context, $singular, $plural) {
    $l10n = Lang::get_reader();
    return Lang::encode($l10n->npgettext($context, $singular, $plural));
  }

  /**
   * Override the current domain in a context ngettext call.
   */
  public static function dnpgettext($domain, $context, $singular, $plural) {
    $l10n = Lang::get_reader($domain);
    return Lang::encode($l10n->npgettext($context, $singular, $plural));
  }

  /**
   * Overrides the domain and category for a plural context-based lookup.
   */
  public static function dcnpgettext($domain, $context, $singular, $plural, $category) {
    $l10n = Lang::get_reader($domain, $category);
    return Lang::encode($l10n->npgettext($context, $singular, $plural));
  }

}

?>
