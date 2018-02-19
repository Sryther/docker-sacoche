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

$action           = (isset($_POST['f_action']))           ? Clean::texte($_POST['f_action'])           : '';
$chemin_logs      = (isset($_POST['f_chemin_logs']))      ? Clean::texte($_POST['f_chemin_logs'])      : '';
$etabl_id_listing = (isset($_POST['f_etabl_id_listing'])) ? Clean::texte($_POST['f_etabl_id_listing']) : '';
$fichier_logs     = (isset($_POST['f_fichier']))          ? Clean::fichier($_POST['f_fichier'])        : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier les paramètres de debug
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='modifier_debug')
{
  $debug = 0;
  $tab_debug = array(
    'PHP'     =>   1,
    'PHPCAS'  =>   2,
    'SQL'     =>   4,
    'SESSION' =>   8,
    'POST'    =>  16,
    'GET'     =>  32,
    'FILES'   =>  64,
    'COOKIE'  => 128,
    'SERVER'  => 256,
    'CONST'   => 512,
  );
  foreach($tab_debug as $debug_mode => $debug_val)
  {
    $debug += (isset($_POST['f_debug_'.$debug_mode])) ? $debug_val : 0 ;
  }
  if($debug)
  {
    FileSystem::ecrire_fichier( CHEMIN_FICHIER_DEBUG_CONFIG , $debug );
  }
  else
  {
    FileSystem::supprimer_fichier( CHEMIN_FICHIER_DEBUG_CONFIG , TRUE /*verif_exist*/ );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier les paramètres des logs phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier_phpCAS') && ($chemin_logs) )
{
  $chemin_logs = (substr($chemin_logs,-1)==DS) ? $chemin_logs : $chemin_logs.DS ;
  // Vérifier chemin valide
  if(!is_dir($chemin_logs))
  {
    Json::end( FALSE , 'Chemin invalide (le dossier n\'existe pas) !' );
  }
  // Tester droits en écriture
  if( !FileSystem::ecrire_fichier_si_possible( $chemin_logs.'debugcas_test_ecriture.txt' , 'ok' ) )
  {
    Json::end( FALSE , 'Droits en écriture dans le dossier insuffisants !' );
  }
  FileSystem::supprimer_fichier($chemin_logs.'debugcas_test_ecriture.txt');
  // Nettoyer la liste des établissements transmise
  if($etabl_id_listing)
  {
    $tab_etabl_id = explode(',',$etabl_id_listing);
    $tab_etabl_id = Clean::map('entier',$tab_etabl_id);
    $tab_etabl_id = array_filter($tab_etabl_id,'positif');
    $etabl_id_listing = count($tab_etabl_id) ? ','.implode(',',$tab_etabl_id).',' : '' ;
  }
  // ok
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array(
    'PHPCAS_LOGS_CHEMIN'        => $chemin_logs,
    'PHPCAS_LOGS_ETABL_LISTING' => $etabl_id_listing,
  ) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Effacer un fichier de logs de phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $fichier_logs )
{
  FileSystem::supprimer_fichier( PHPCAS_LOGS_CHEMIN.$fichier_logs.'.txt' , TRUE /*verif_exist*/ );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer un fichier de logs de phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir') && $fichier_logs )
{
  // Compression du fichier
  $result = FileSystem::zip_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_logs.'.zip' , $fichier_logs.'.txt' , PHPCAS_LOGS_CHEMIN.$fichier_logs.'.txt' );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE , '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_logs.'.zip'.'"><span class="file file_zip">Fichier de logs au format <em>zip</em>.</li></ul>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
