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

class Erreur500
{

  // //////////////////////////////////////////////////
  // Méthodes internes (privées)
  // //////////////////////////////////////////////////


  /*
   * Augmenter memory_limit (si autorisé) pour les pages les plus gourmandes
   * 
   * @param void
   * @return void
   */
  private static function augmenter_memory_limit()
  {
    if( (int)ini_get('memory_limit') < 256 )
    {
      @ini_set(  'memory_limit','256M');
      @ini_alter('memory_limit','256M');
    }
  }

  /*
   * Augmenter max_execution_time (si autorisé) pour les pages les plus gourmandes
   * 
   * @param void
   * @return void
   */
  private static function augmenter_max_execution_time()
  {
    if( (int)ini_get('max_execution_time') < 30 )
    {
      @ini_set(  'max_execution_time',30);
      @ini_alter('max_execution_time',30);
    }
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /*
   * Pour intercepter les erreurs de dépassement de mémoire ou de durée d'exécution (une erreur fatale échappe à un try{...}catch(){...}).
   * Doit être déclarée en méthode publique car invoquée de l'extérieur après déclaration dans register_shutdown_function().
   *
   * Source : http://pmol.fr/programmation/web/la-gestion-des-erreurs-en-php/
   * Mais ça ne fonctionne pas en CGI : PHP a déjà envoyé l'erreur 500 et cette fonction est appelée trop tard, PHP n'a plus la main.
   * Pour avoir des informations accessibles en cas d'erreur type « PHP Fatal error : Allowed memory size of ... bytes exhausted » on peut aussi mettre dans les pages sensibles :
   * Outil::ajouter_log_PHP( 'Demande de bilan' , serialize($_POST) , __FILE__ , __LINE__ , TRUE );
   * 
   * @param void
   * @return void
   */
  public static function rapporter_erreur_fatale_memoire_ou_duree()
  {
    $tab_last_error = error_get_last(); // tableau à 4 indices : type ; message ; file ; line
    if( ($tab_last_error!==NULL) && ($tab_last_error['type']===E_ERROR) )
    {
      if(mb_substr($tab_last_error['message'],0,19)=='Allowed memory size')
      {
        exit_error( 'Mémoire insuffisante' /*titre*/ , 'Mémoire de '.ini_get('memory_limit').' insuffisante ; sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' /*contenu*/ , '' /*lien*/ );
      }
      if(mb_substr($tab_last_error['message'],0,22)=='Maximum execution time')
      {
        exit_error( 'Temps alloué insuffisant' /*titre*/ , 'Temps de '.ini_get('max_execution_time').'s alloué au script insuffisant ; sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "max_execution_time".' /*contenu*/ , '' /*lien*/ );
      }
    }
  }

  /*
   * Pour enclencher les fonctions de prévention et d'interception des erreurs fatales dépassement de mémoire ou de durée d'exécution.
   * 
   * 1/ dépassement de mémoire (memory_limit)
   * La récupération de beaucoup d'informations peut provoquer un dépassement de mémoire.
   * Et la classe FPDF a besoin de mémoire, malgré toutes les optimisations possibles, pour générer un PDF comportant parfois entre 100 et 200 pages.
  // Un graphique sur une longue période peut aussi nécessiter de nombreux calculs.
   * De plus la consommation d'une classe PHP n'est pas mesurable - non comptabilisée par memory_get_usage() - et non corrélée à la taille de l'objet PDF en l'occurrence...
   * Un memory_limit() de 64Mo est ainsi dépassé avec un pdf d'environ 150 pages, ce qui est atteint avec 4 pages par élèves ou un groupe d'élèves > effectif moyen d'une classe.
   * 
   * 2/ durée d'exécution (max_execution_time)
   * La découpe d'un PDF peut provoquer un dépassement de la durée d'exécution allouée au script.
   * Un max_execution_time() de 10s peut ainsi être dépassé avec un pdf de bulletins d'une classe de 25 élèves (archives + tirages responsables : ça fait plus de 100 pages).
   * 
   * 3/ Solution
   * On commence par un ini_set(), même si cette directive peut être interdite dans la conf PHP.
   * Pour memory_limit, elle peut aussi être bloquée via Suhosin (http:*www.hardened-php.net/suhosin/configuration.html#suhosin.memory_limit).
   * En complément, register_shutdown_function() permet de capter une erreur fatale de dépassement de mémoire, sauf si CGI.
   * D'où une combinaison de toutes ces pistes, plus une détection par javascript du statusCode.
   * 
   * @param bool $memory
   * @param bool $time
   * @return void
   */
  public static function prevention_et_gestion_erreurs_fatales($memory,$time)
  {
    if($memory) Erreur500::augmenter_memory_limit();
    if($time)   Erreur500::augmenter_max_execution_time();
    register_shutdown_function( array('Erreur500','rapporter_erreur_fatale_memoire_ou_duree') ); // @see http://fr.php.net/manual/fr/language.types.callable.php
  }

}
?>