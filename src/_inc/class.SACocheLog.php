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

class SACocheLog
{

  // //////////////////////////////////////////////////
  // Méthode interne (privée) de bas niveau
  // //////////////////////////////////////////////////

  /**
   * chemin_fichier_log
   *
   * @param int $base_id
   * @return string
   */
  private static function chemin_fichier_log($base_id)
  {
    return CHEMIN_DOSSIER_LOG.'base_'.$base_id.'.php';
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Ajout d'un log dans un fichier d'actions sensibles (un fichier par structure)
   * Nécessite que la session soit ouverte.
   * 
   * @param string $contenu   description de l'action
   * @return void
   */
  public static function ajouter($contenu)
  {
    $identite = $_SESSION['USER_PROFIL_NOM_COURT'].' ['.$_SESSION['USER_ID'].'] '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'];
    // Nettoyage ; on boucle pour contrer par exemple //** qui deviendrait /* avec un seul passage.
    do { $identite = str_replace( array('/*','*/') , '' , $identite , $count ); } while ($count) ;
    do { $contenu  = str_replace( array('/*','*/') , '' , $contenu  , $count ); } while ($count) ;
    $tab_ligne = array();
    $tab_ligne[] = '<?php /*';
    $tab_ligne[] = date('d-m-Y H:i:s');
    $tab_ligne[] = html($identite);
    $tab_ligne[] = html($contenu);
    $tab_ligne[] = '*/ ?>'."\r\n";
    FileSystem::ecrire_fichier( SACocheLog::chemin_fichier_log($_SESSION['BASE']) , implode("\t",$tab_ligne) , FILE_APPEND );
  }

  /**
   * Renvoie le contenu d'un fichier de log si existant, et sinon NULL.
   * 
   * @param int $base_id
   * @return string|NULL
   */
  public static function lire($base_id)
  {
    return is_file( SACocheLog::chemin_fichier_log($base_id) ) ? file_get_contents(SACocheLog::chemin_fichier_log($base_id)) : NULL ;
  }

  /**
   * Retourner un tableau de lignes à partir d'un log en se basant sur les retours chariot.
   * 
   * @param string   $fichier_log_contenu
   * @return array
   */
  public static function extraire_lignes($fichier_log_contenu)
  {
    return explode( "\r\n" , trim($fichier_log_contenu) );
  }

  /**
   * Allège le contenu d'un fichier de log (si besoin).
   * 
   * @param int $base_id
   * @return void
   */
  public static function alleger($base_id)
  {
    $fichier_log_contenu = SACocheLog::lire($base_id);
    if(!is_null($fichier_log_contenu))
    {
      $tab_lignes = SACocheLog::extraire_lignes($fichier_log_contenu);
      unset($fichier_log_contenu);
      if( count($tab_lignes) > 10000 )
      {
        $tab_lignes = array_slice( $tab_lignes , -8000 );
        FileSystem::ecrire_fichier( SACocheLog::chemin_fichier_log($base_id) , implode("\r\n",$tab_lignes) );
      }
    }
  }

}
?>