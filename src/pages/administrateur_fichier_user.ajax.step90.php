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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 90 - Nettoyage des fichiers temporaires (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Il est arrivé que ces fichiers n'existent plus (bizarre...) d'où le test d'existence.
FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_dest_nom                      , TRUE /*verif_exist*/ );
if($import_profil!='nomenclature')
{
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt'         , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'classes.txt'       , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'groupes.txt'       , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'memo_analyse.txt'  , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'date_sortie.txt'   , TRUE /*verif_exist*/ );
  // Retenir qu'un import a été effectué
  $nom_variable = 'date_last_import_'.$import_profil.'s';
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( array( $nom_variable => TODAY_MYSQL ) );
  $_SESSION[Clean::upper($nom_variable)] = TODAY_MYSQL;
}
// Game over
Json::add_str('<p><label class="valide">Fichiers temporaires effacés, procédure d\'import terminée !</label></p>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#" id="retourner_depart">Retour au départ.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
