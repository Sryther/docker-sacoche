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

class Math
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

/**
 * roundTo
 * Arrondir à une précision donnée, par exemple à 0,5 près
 * @see   http://fr.php.net/manual/fr/function.round.php#93747
 * @param float $nombre
 * @param float $precision
 * @return float
 */
public static function roundTo( $nombre , $precision )
{
  return ($precision) ? round( $nombre/$precision , 0 ) * $precision : $nombre ;
}

/**
 * ceilTo
 * Arrondir à une précision donnée, par exemple à 0,5 près, par excès
 * @param float $nombre
 * @param float $precision
 * @return float
 */
public static function ceilTo( $nombre , $precision )
{
  return ($precision) ? ceil( $nombre/$precision ) * $precision : $nombre ;
}

/**
 * floorTo
 * Arrondir à une précision donnée, par exemple à 0,5 près, par défaut
 * @param float $nombre
 * @param float $precision
 * @return float
 */
public static function floorTo( $nombre , $precision )
{
  return ($precision) ? floor( $nombre/$precision ) * $precision : $nombre ;
}

}
?>