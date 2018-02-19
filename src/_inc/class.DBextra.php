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

class DBextra
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Charger les parametres mysql de connexion d'un établissement qui n'auraient pas été chargé par le fichier index ou ajax.
   * 
   * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur une base d'établissement sans y être connecté :
   * - pour savoir si le mode de connexion est SSO ou pas
   * - pour mettre à jour la base si besoin
   * - pour l'identification (méthode SessionUser::tester_authentification_utilisateur())
   * - pour le webmestre (création d'un admin, info sur les admins, initialisation du mdp, retrait d'un email, mise à jour, stats...)
   * - pour contacter un administrateur ou obtenir de nouveaux identifiants égarés
   * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur la base du webmestre :
   * - pour avoir des infos sur le contact référent, ou l'état d'une convention ENT
   * Dans le cas de l'installation sur le serveur Sésamath :
   * - pour les fonctionnalités de gestion
   * - pour l'ajout d'un établissement
   * 
   * @param int   $BASE   0 pour celle du webmestre
   * @param bool  $exit   TRUE par défaut (arrêt si erreur)
   * @return bool | exit
   */
  public static function charger_parametres_mysql_supplementaires( $BASE , $exit=TRUE )
  {
    $fichier_mysql_config_supplementaire = ($BASE) ? CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php' : CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_webmestre.php' ;
    $fichier_class_config_supplementaire = ($BASE) ? CHEMIN_DOSSIER_INCLUDE.'class.DB.config.sacoche_structure.php' : CHEMIN_DOSSIER_INCLUDE.'class.DB.config.sacoche_webmestre.php' ;
    if(is_file($fichier_mysql_config_supplementaire))
    {
      global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
      require($fichier_mysql_config_supplementaire);
      require($fichier_class_config_supplementaire);
      return TRUE;
    }
    else
    {
      if($exit)
      {
        exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.<br />Le fichier "'.FileSystem::fin_chemin($fichier_mysql_config_supplementaire).'" (base n°'.$BASE.') est manquant !' /*contenu*/ );
      }
      else
      {
        return FALSE;
      }
    }
  }

  /**
   * Mettre à jour automatiquement la base si besoin ; à effectuer avant toute récupération des données sinon ça peut poser pb...
   * 
   * @param int   $BASE
   * @return void
   */
  public static function maj_base_structure_si_besoin($BASE)
  {
    $version_base_structure = DB_STRUCTURE_MAJ_BASE::DB_version_base();
    if($version_base_structure != VERSION_BASE_STRUCTURE)
    {
      // On ne met pas à jour la base tant que le webmestre bloque l'accès à l'application, car sinon cela pourrait se produire avant le transfert de tous les fichiers.
      if(LockAcces::tester_blocage('webmestre',0)===NULL)
      {
        // Bloquer l'application
        LockAcces::bloquer_application( 'automate' , $BASE , 'Mise à jour de la base en cours.' );
        // Lancer une mise à jour de la base
        DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base_structure);
        // Log de l'action
        SACocheLog::ajouter('Mise à jour automatique de la base '.SACOCHE_STRUCTURE_BD_NAME.'.');
        // Débloquer l'application
        LockAcces::debloquer_application( 'automate' , $BASE );
      }
    }
  }

}
?>