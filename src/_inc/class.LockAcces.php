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

class LockAcces
{

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * chemin_fichier_blocage
   *
   * @param string   $profil   "webmestre" | "administrateur" | "automate"
   * @param int      $base_id
   * @return string
   */
  private static function chemin_fichier_blocage( $profil , $base_id )
  {
    return CHEMIN_DOSSIER_CONFIG.'blocage_'.$profil.'_'.$base_id.'.txt';
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Bloquer l'accès à SACoche (les profils concernés dépendent du profil qui exerce le blocage).
   * 
   * @param string $profil_demandeur (webmestre|administrateur|automate)
   * @param int    $base_id   (0 si demande mono-structure ou du webmestre multi-structures de bloquer tous les établissements)
   * @param string $motif
   * @return void
   */
  public static function bloquer_application( $profil_demandeur , $base_id , $motif )
  {
    FileSystem::ecrire_fichier( LockAcces::chemin_fichier_blocage($profil_demandeur,$base_id) , $motif );
    // Log de l'action
    SACocheLog::ajouter('Blocage de l\'accès à l\'application ['.$motif.'].');
  }

  /**
   * Débloquer l'accès à SACoche.
   * 
   * @param string $profil_demandeur (webmestre|administrateur|automate)
   * @param int    $base_id   (0 si demande mono-structure ou du webmestre multi-structures de débloquer tous les établissements)
   * @return void
   */
  public static function debloquer_application( $profil_demandeur , $base_id )
  {
    FileSystem::supprimer_fichier( LockAcces::chemin_fichier_blocage($profil_demandeur,$base_id) , TRUE /*verif_exist*/ );
    // Log de l'action
    SACocheLog::ajouter('Déblocage de l\'accès à l\'application.');
  }

  /**
   * Supprimer les fichiers de blocage éventuels d'une structure
   * 
   * @param int   $base_id
   * @return void
   */
  public static function supprimer_fichiers_blocage($base_id)
  {
    FileSystem::supprimer_fichier( LockAcces::chemin_fichier_blocage('webmestre'     ,$base_id) , TRUE /*verif_exist*/ );
    FileSystem::supprimer_fichier( LockAcces::chemin_fichier_blocage('administrateur',$base_id) , TRUE /*verif_exist*/ );
    FileSystem::supprimer_fichier( LockAcces::chemin_fichier_blocage('automate'      ,$base_id) , TRUE /*verif_exist*/ );
  }

  /**
   * Tester si un fichier de blocage est présent, et si oui renvoyer son contenu (NULL sinon).
   * 
   * @param string $profil_demandeur (webmestre|administrateur|automate)
   * @param int    $base_id   (0 si demande mono-structure ou du webmestre multi-structures de bloquer tous les établissements)
   * @return string|NULL
   */
  public static function tester_blocage( $profil , $base_id )
  {
    return (is_file( LockAcces::chemin_fichier_blocage($profil,$base_id) )) ? file_get_contents(LockAcces::chemin_fichier_blocage($profil,$base_id)) : NULL ;
  }

  /**
   * Test si l'accès est bloqué sur demande du webmestre ou d'un administrateur (maintenance, sauvegarde/restauration, ...).
   * Si tel est le cas, alors exit().
   * 
   * Nécessite que la session soit ouverte.
   * Appelé depuis les pages index.php + ajax.php + lors d'une demande d'identification d'un utilisateur (sauf webmestre & développeur)
   * 
   * En cas de blocage demandé par le webmestre, on ne laisse l'accès que :
   * - pour le webmestre | développeur déjà identifié
   * - pour la partie publique, si pas une demande d'identification, sauf demande webmestre | développeur
   * 
   * En cas de blocage demandé par un administrateur ou par l'automate (sauvegarde/restauration) pour un établissement donné, on ne laisse l'accès que :
   * - pour le webmestre | développeur déjà identifié
   * - pour un administrateur déjà identifié
   * - pour la partie publique, si pas une demande d'identification, sauf demande webmestre | développeur | administrateur
   * 
   * @param string $BASE                             car $_SESSION['BASE'] non encore renseigné si demande d'identification
   * @param string $demande_connexion_profil_sigle   FALSE si appel depuis index.php ou ajax.php, le sigle du profil si demande d'identification
   * @return void | exit !
   */
  public static function stopper_si_blocage( $BASE , $demande_connexion_profil_sigle )
  {
    $is_session_webm_devel       = in_array($_SESSION['USER_PROFIL_SIGLE'],array('WBM','DVL'));
    $is_session_webm_devel_admin = in_array($_SESSION['USER_PROFIL_SIGLE'],array('WBM','DVL','ADM'));
    $is_demande_webm_devel_admin = in_array($demande_connexion_profil_sigle,array(FALSE,'WBM','DVL','ADM'));
    // Blocage demandé par le webmestre pour tous les établissements (multi-structures) ou pour l'établissement (mono-structure).
    $blocage_msg = LockAcces::tester_blocage( 'webmestre' , 0 );
    if( ($blocage_msg!==NULL) && (!$is_session_webm_devel) && (($_SESSION['USER_PROFIL_SIGLE']!='OUT')||($demande_connexion_profil_sigle!=FALSE)) )
    {
      exit_error( 'Blocage par le webmestre' /*titre*/ , html('Blocage par le webmestre - '.$blocage_msg) /*contenu*/ );
    }
    // Blocage demandé par le webmestre pour un établissement donné (multi-structures).
    $blocage_msg = LockAcces::tester_blocage( 'webmestre' , $BASE );
    if( ($blocage_msg!==NULL) && (!$is_session_webm_devel) && (($_SESSION['USER_PROFIL_SIGLE']!='OUT')||($demande_connexion_profil_sigle!=FALSE)) )
    {
      exit_error('Blocage par le webmestre' /*titre*/ , html('Blocage par le webmestre - '.$blocage_msg) /*contenu*/ );
    }
    // Blocage demandé par un administrateur pour son établissement.
    $blocage_txt = LockAcces::tester_blocage( 'administrateur' , $BASE );
    if( ($blocage_txt!==NULL) && (!$is_session_webm_devel_admin) && (($_SESSION['USER_PROFIL_SIGLE']!='OUT')||(!$is_demande_webm_devel_admin)) )
    {
      // Évolution de pouvoir restreindre le blocage à certains profils ; par compatibilité avec l'existant leur liste est ajoutée à la chaîne de caractères existante
      list( $blocage_msg , $string_profils ) = explode(NL,$blocage_txt) + array(NULL,'ALL') ;
      if( ($string_profils=='ALL') || in_array($demande_connexion_profil_sigle,explode(',',$string_profils)) )
      {
        exit_error( 'Blocage par un administrateur' /*titre*/ , html('Blocage par un administrateur - '.$blocage_msg) /*contenu*/ );
      }
    }
    // Blocage demandé par l'automate pour un établissement donné.
    $blocage_msg = LockAcces::tester_blocage( 'automate' , $BASE );
    if( ($blocage_msg!==NULL) && (!$is_session_webm_devel_admin) && (($_SESSION['USER_PROFIL_SIGLE']!='OUT')||(!$is_demande_webm_devel_admin)) )
    {
      // Au cas où une procédure de sauvegarde / restauration / nettoyage / tranfert échouerait, un fichier de blocage automatique pourrait être créé et ne pas être effacé.
      // Pour cette raison on teste une durée de vie anormalement longue d'une tel fichier de blocage (puisqu'il ne devrait être que temporaire).
      if( $_SERVER['REQUEST_TIME'] - filemtime(LockAcces::chemin_fichier_blocage('automate',$BASE)) < 5*60 )
      {
        exit_error( 'Blocage automatique' /*titre*/ , html('Blocage automatique - '.$blocage_msg) /*contenu*/ );
      }
      else
      {
        // Annuler un blocage à SACoche par l'automate anormalement long.
        LockAcces::debloquer_application( 'automate' , $BASE );
      }
    }
  }

}
?>