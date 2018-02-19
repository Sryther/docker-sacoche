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

class Cookie
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Définir un cookie
   * 
   * @param string $cookie_name
   * @param mixed  $cookie_value
   * @param int    $cookie_time   le délai en secondes (ou rien ne pas expirer)
   * @return void
   */
  public static function definir( $cookie_name , $cookie_value , $cookie_time=NULL )
  {
    $cookie_expire = $cookie_time ? $_SERVER['REQUEST_TIME']+$cookie_time : 0 ;
    setcookie( $cookie_name /*name*/ , $cookie_value /*value*/ , $cookie_expire /*expire*/ , '/' /*path*/ , HOST /*domain*/ );
  }

  /**
   * Effacer un cookie
   * 
   * @param string $cookie_name
   * @return void
   */
  public static function effacer( $cookie_name )
  {
    setcookie( $cookie_name /*name*/ , '' /*value*/ , $_SERVER['REQUEST_TIME']-42000 /*expire*/ , '/' /*path*/ , HOST /*domain*/ );
  }

  /**
   * Définir un cookie avec le contenu de paramètres transmis en GET puis rappeler la page
   * 
   * @param string $query_string   éventuellement avec 'url_redirection' en dernier paramètre
   * @return void
   */
  public static function save_get_and_exit_reload( $query_string )
  {
    Cookie::definir( COOKIE_MEMOGET , $query_string , 300 /* 60*5 = 5 min */ );
    $param_redir_pos = mb_strpos($query_string,'&url_redirection');
    $param_sans_redir = ($param_redir_pos) ? mb_substr( $query_string , 0 , $param_redir_pos ) : $query_string ; // J'ai déjà eu un msg d'erreur car il n'aime pas les chaines trop longues + Pas la peine d'encombrer avec le paramètre de redirection qui sera retrouvé dans le cookie de toutes façons
    exit_redirection(URL_BASE.$_SERVER['SCRIPT_NAME'].'?'.$param_sans_redir);
  }

  /**
   * Définir un cookie avec le contenu de paramètres transmis en GET puis rappeler la page
   * 
   * @param void
   * @return void
   */
  public static function load_get()
  {
    $tab_get = explode('&',$_COOKIE[COOKIE_MEMOGET]);
    foreach($tab_get as $get)
    {
      list($get_name,$get_value) = explode('=',$get) + array(NULL,TRUE) ;
      $_GET[$get_name] = $get_value;
    }
  }

}
?>