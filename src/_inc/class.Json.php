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

class Json
{

  // //////////////////////////////////////////////////
  // Attributs de la classe (équivalents des "variables")
  // //////////////////////////////////////////////////

  private static $str_retour = '';
  private static $tab_retour = array();

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  public static function add_str($str)
  {
    Json::$str_retour .= $str;
  }

  public static function add_row($key,$val)
  {
    if($key==NULL)
    {
      Json::$tab_retour[] = $val;
    }
    else if(isset(Json::$tab_retour[$key]))
    {
      Json::$tab_retour[$key] .= $val;
    }
    else
    {
      Json::$tab_retour[$key] = $val;
    }
  }

  public static function add_tab($tab)
  {
    Json::$tab_retour = $tab;
  }

  /**
   * Retour d'un tableau au format JSON avec comme clefs "statut" (boolean) et "value" (chaine ou un tableau)
   *
   * @param bool         $statut
   * @param string|array $value   facultatif ; si rien de transmis, on prend $str_retour ou $tab_retour s'ils ne sont pas vides
   * @return string
   */
  public static function end( $statut , $value=NULL )
  {
    $tab_statut = array( 'statut' => $statut ) ;
    if(is_null($value))
    {
      if(!empty(Json::$str_retour))
      {
        $value = Json::$str_retour;
      }
      elseif(!empty(Json::$tab_retour))
      {
        $value = Json::$tab_retour;
      }
    }
    $tab_valeur = is_array($value) ? $value : array( 'value' => $value ) ;
    $json_retour = json_encode( array_merge($tab_statut,$tab_valeur) );
    if($json_retour===FALSE)
    {
      $json_retour = "Une erreur est survenue lors de la conversion JSON.";
    }
    // Normalement, le serveur Sésamath ne gzip pas les "petites" réponses (<512 ou 1024 octets).
    // Mais c'est basé sur le Content-Length, donc s'il n'y en a pas, il gzip toujours.
    // Du coup, quand PHP renvoie du json, c'est mieux d'indiquer Content-Length.
    // Ça ne devrait pas changer grand chose, mais ça ne peut pas faire de mal.
    header('Content-Type: application/json; charset='.CHARSET);
    header('Content-Length: '.strlen($json_retour));
    exit($json_retour);
  }

}
?>