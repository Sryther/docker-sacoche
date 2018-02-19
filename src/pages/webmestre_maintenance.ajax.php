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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '' ;
$motif  = (isset($_POST['f_motif']))  ? Clean::texte($_POST['f_motif'])  : '' ;

$file_memo = CHEMIN_DOSSIER_EXPORT.'webmestre_maintenance_'.session_id().'.txt';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquer ou débloquer l'application
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='bloquer')
{
  Outil::ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application fermée.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::bloquer_application( $_SESSION['USER_PROFIL_TYPE'] , 0 , $motif );
  Json::end( TRUE , '<label class="erreur">Application fermée : '.html($motif).'</label>' );
}

if($action=='debloquer')
{
  Outil::ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application( $_SESSION['USER_PROFIL_TYPE'] , 0 );
  Json::end( TRUE , '<label class="valide">Application accessible.</label>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des dossiers additionnels (par établissement si multi-structures)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='verif_dir_etabl')
{
  $tab_dossiers = array();
  // Pour l'affichage du retour
  $titre_verifi_dossiers_additionnels = (HEBERGEUR_INSTALLATION=='multi-structures') ? 'Vérification des dossiers additionnels par établissement' : 'Vérification des dossiers additionnels' ;
  $thead = '<tr><td colspan="2">'.$titre_verifi_dossiers_additionnels.' - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody_ok = '';
  $tbody_pb = '';
  // On commence déjà par les répertoires principaux
  $tab_dossiers = array_fill_keys ( FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_TMP) , TRUE );
  foreach(FileSystem::$tab_dossier_tmp as $dossier_key => $dossier_dir)
  {
    if(isset($tab_dossiers[substr($dossier_key,6,-1)]))
    {
      $tbody_ok .= '<tr class="v"><td>Dossier présent</td><td>'.$dossier_key.'</td></tr>';
      unset($tab_dossiers[$dossier_dir]);
    }
    else
    {
      FileSystem::creer_dossier($dossier_dir);
      $tbody_pb .= '<tr class="r"><td>Dossier manquant (&rarr; ajouté)</td><td>'.$dossier_key.'</td></tr>';
    }
  }
  // Récupérer les ids des structures
  $tab_bases = (HEBERGEUR_INSTALLATION=='multi-structures') ? array_keys( DB_WEBMESTRE_WEBMESTRE::DB_lister_structures_id() ) : array(0) ;
  // Récupérer les dossiers additionnels par établissement
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
  {
    $tab_dossiers[$dossier_dir] = array_fill_keys ( FileSystem::lister_contenu_dossier($dossier_dir) , TRUE );
    unset($tab_dossiers[$dossier_dir]['index.htm']);
    $sort_flag = defined('SORT_NATURAL') ? SORT_NATURAL : SORT_STRING ; // SORT_NATURAL requière PHP 5.4.0
    ksort($tab_dossiers[$dossier_dir],$sort_flag);
  }
  // On parcourt les sous-dossiers devant exister : ok ou création.
  foreach($tab_bases as $base_id)
  {
    foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
    {
      if(isset($tab_dossiers[$dossier_dir][$base_id]))
      {
        $tbody_ok .= '<tr class="v"><td>Dossier présent</td><td>'.$dossier_key.$base_id.'</td></tr>';
        unset($tab_dossiers[$dossier_dir][$base_id]);
      }
      else
      {
        FileSystem::creer_dossier($dossier_dir.$base_id);
        FileSystem::ecrire_fichier($dossier_dir.$base_id.DS.'index.htm','Circulez, il n\'y a rien à voir par ici !');
        $tbody_pb .= '<tr class="r"><td>Dossier manquant (&rarr; ajouté)</td><td>'.$dossier_key.$base_id.'</td></tr>';
      }
    }
  }
  // Il reste éventuellement les dossiers en trop.
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
  {
    if(count($tab_dossiers[$dossier_dir]))
    {
      foreach($tab_dossiers[$dossier_dir] as $base_id => $tab)
      {
        if(isset($tab_dossiers[$dossier_dir][$base_id]))
        {
          if(is_dir($dossier_dir.$base_id))
          {
            FileSystem::supprimer_dossier($dossier_dir.$base_id);
            $tbody_pb .= '<tr class="r"><td>Dossier en trop (&rarr; supprimé)</td><td>'.$dossier_key.$base_id.'</td></tr>';
          }
          // Normalement, ne devrait pas, mais suite à un bug, des fichiers se sont retrouvés créés...
          if(is_file($dossier_dir.$base_id))
          {
            FileSystem::supprimer_fichier($dossier_dir.$base_id);
            $tbody_pb .= '<tr class="r"><td>Fichier en trop (&rarr; supprimé)</td><td>'.$dossier_key.$base_id.'</td></tr>';
          }
        }
      }
    }
  }
  // Enregistrement du rapport
  $fichier_nom = 'rapport_verif_dir_etabl_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody_pb.$tbody_ok );
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mise à jour automatique des fichiers
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_import  = CHEMIN_DOSSIER_IMPORT.'telechargement.zip';
$dossier_dezip   = CHEMIN_DOSSIER_IMPORT.'SACoche'.DS;
$dossier_install = CHEMIN_DOSSIER_SACOCHE;

//
// 1. Récupération de l'archive <em>ZIP</em>...
//
if($action=='maj_etape1')
{
  if(IS_HEBERGEMENT_SESAMATH)
  {
    Json::end( FALSE , 'La mise à jour de SACoche sur le serveur Sésamath doit s\'effectuer en déployant le SVN !' );
  }
  if(is_file(CHEMIN_FICHIER_WS_LCS))
  {
    Json::end( FALSE , 'La mise à jour du module LCS-SACoche doit s\'effectuer via le LCS !' );
  }
  $contenu_zip = cURL::get_contents( SERVEUR_TELECHARGEMENT ,FALSE /*tab_post*/ , 90 /*timeout*/ );
  if(substr($contenu_zip,0,6)=='Erreur')
  {
    Json::end( FALSE , $contenu_zip );
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  Json::end( TRUE , 'Décompression de l\'archive&hellip;' );
}

//
// 2. Décompression de l'archive...
//
if($action=='maj_etape2')
{
  if(is_dir($dossier_dezip))
  {
    FileSystem::supprimer_dossier($dossier_dezip);
  }
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( $fichier_import , CHEMIN_DOSSIER_IMPORT , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , 'Erreur d\'extraction du contenu ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  Json::end( TRUE , 'Analyse des fichiers et recensement des dossiers&hellip;' );
}

//
// 3. Analyse des fichiers et recensement des dossiers...
//
if($action=='maj_etape3')
{
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ );
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , FileSystem::$tab_analyse );
  // Retour
  Json::end( TRUE , 'Analyse et répercussion des modifications&hellip;' );
}

//
// 4. Analyse et répercussion des modifications... (tout en bloquant l'appli)
//
if($action=='maj_etape4')
{
  $thead = '<tr><td colspan="2">Mise à jour automatique - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = '';
  // Bloquer l'application
  Outil::ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application fermée.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::bloquer_application( 'automate' , 0 , 'Mise à jour des fichiers en cours.' );
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Dossiers : ordre croissant pour commencer par ceux les moins imbriqués : obligatoire pour l'ajout, et pour la suppression on teste si pas déjà supprimé.
  ksort($tab_memo['dossier']);
  foreach($tab_memo['dossier'] as $dossier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      // Dossier inchangé (cas le plus fréquent donc testé en premier).
    }
    elseif(!isset($tab['avant']))
    {
      // Dossier à ajouter
      $tbody .= '<tr><td class="v">Dossier ajouté</td><td>'.$dossier.'</td></tr>';
      if( !FileSystem::creer_dossier($dossier_install.$dossier) )
      {
        Outil::ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
        LockAcces::debloquer_application( 'automate' , 0 );
        Json::end( FALSE , 'Dossier "'.$dossier.'" non créé ou inaccessible en écriture !' );
      }
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Dossier à supprimer
      $tbody .= '<tr><td class="r">Dossier supprimé</td><td>'.$dossier.'</td></tr>';
      if(is_dir($dossier_install.$dossier))
      {
        FileSystem::supprimer_dossier($dossier_install.$dossier);
      }
    }
  }
  // Fichiers : ordre décroissant pour avoir VERSION.txt en dernier (majuscules avant dans la table ASCII).
  krsort($tab_memo['fichier']);
  foreach($tab_memo['fichier'] as $fichier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      if( ($tab['avant']!=$tab['apres']) && (substr($fichier,-9)!='.htaccess') )
      {
        // Fichier changé => maj (si le .htaccess a été changé, c'est sans doute volontaire, ne pas y toucher)
        if( !copy( $dossier_dezip.$fichier , $dossier_install.$fichier ) )
        {
          Outil::ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
          LockAcces::debloquer_application( 'automate' , 0 );
          Json::end( FALSE , 'Erreur lors de l\'écriture du fichier "'.$fichier.'" !' );
        }
        $tbody .= '<tr><td class="b">Fichier modifié</td><td>'.$fichier.'</td></tr>';
      }
    }
    elseif( (!isset($tab['avant'])) && (substr($fichier,-9)!='.htaccess') )
    {
      // Fichier à ajouter (si le .htaccess n'y est pas, c'est sans doute volontaire, ne pas l'y remettre)
      if( !copy( $dossier_dezip.$fichier , $dossier_install.$fichier ) )
      {
        Outil::ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
        LockAcces::debloquer_application( 'automate' , 0 );
        Json::end( FALSE , 'Erreur lors de l\'écriture du fichier "'.$fichier.'" !' );
      }
      $tbody .= '<tr><td class="v">Fichier ajouté</td><td>'.$fichier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Fichier à supprimer
      FileSystem::supprimer_fichier($dossier_install.$fichier , TRUE /*verif_exist*/ );
      $tbody .= '<tr><td class="r">Fichier supprimé</td><td>'.$fichier.'</td></tr>';
    }
  }
  // Débloquer l'application
  Outil::ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application( 'automate' , 0 );
  // Enregistrement du rapport
  $_SESSION['tmp']['rapport_filename'] = 'rapport_maj_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $_SESSION['tmp']['rapport_filename'] , $thead , $tbody );
  // Retour
  Json::end( TRUE , 'Rapport des modifications apportées et nettoyage&hellip;' );
}

//
// 5. Nettoyage...
//
if($action=='maj_etape5')
{
  $fichier_chemin = URL_DIR_EXPORT.$_SESSION['tmp']['rapport_filename'];
  // Supprimer toutes les données provisoires
  FileSystem::supprimer_dossier($dossier_dezip);
  FileSystem::supprimer_fichier( $file_memo );
  unset($_SESSION['tmp']);
  // Retour
  Json::end( TRUE , array( 'version' => VERSION_PROG , 'fichier' => $fichier_chemin ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des fichiers de l'application en place
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_import  = CHEMIN_DOSSIER_IMPORT.'verification.zip';
$dossier_dezip   = CHEMIN_DOSSIER_IMPORT.'SACoche'.DS;
$dossier_install = '.'.DS;

//
// 1. Récupération de l'archive <em>ZIP</em>...
//
if($action=='verif_file_appli_etape1')
{
  $tab_post = array();
  $tab_post['verification'] = 1;
  $tab_post['version'] = VERSION_PROG;
  $contenu_zip = cURL::get_contents( SERVEUR_TELECHARGEMENT , $tab_post , 60 /*timeout*/ );
  if(substr($contenu_zip,0,6)=='Erreur')
  {
    Json::end( FALSE , $contenu_zip );
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  Json::end( TRUE , 'Décompression de l\'archive&hellip;' );
}

//
// 2. Décompression de l'archive...
//
if($action=='verif_file_appli_etape2')
{
  if(is_dir($dossier_dezip))
  {
    FileSystem::supprimer_dossier($dossier_dezip);
  }
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( $fichier_import , CHEMIN_DOSSIER_IMPORT , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , 'Erreur d\'extraction du contenu ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  Json::end( TRUE , 'Analyse des fichiers et recensement des dossiers&hellip;' );
}

//
// 3. Analyse des fichiers et recensement des dossiers...
//
if($action=='verif_file_appli_etape3')
{
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ , FALSE );
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , FileSystem::$tab_analyse );
  // Retour
  Json::end( TRUE , 'Comparaison des données&hellip;' );
}

//
// 4. Comparaison des données...
//
if($action=='verif_file_appli_etape4')
{
  $thead = '<tr><td colspan="2">Vérification des fichiers de l\'application en place - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody_ok = '';
  $tbody_pb = '';
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Dossiers : ordre croissant pour commencer par ceux les moins imbriqués : obligatoire pour l'ajout, et pour la suppression on teste si pas déjà supprimé.
  ksort($tab_memo['dossier']);
  foreach($tab_memo['dossier'] as $dossier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      // Dossier inchangé (cas le plus fréquent donc testé en premier).
      $tbody_ok .= '<tr class="v"><td>Dossier présent</td><td>'.$dossier.'</td></tr>';
    }
    elseif(!isset($tab['avant']))
    {
      // Dossier manquant
      $tbody_pb .= '<tr class="r"><td>Dossier manquant</td><td>'.$dossier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Dossier en trop
      $tbody_pb .= '<tr class="r"><td>Dossier en trop</td><td>'.$dossier.'</td></tr>';
    }
  }
  // Fichiers : ordre décroissant pour avoir VERSION.txt en dernier (majuscules avant dans la table ASCII).
  krsort($tab_memo['fichier']);
  foreach($tab_memo['fichier'] as $fichier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      if( ($tab['avant']==$tab['apres']) || (substr($fichier,-9)=='.htaccess') )
      {
        // Fichier identique (si le .htaccess a été changé, c'est sans doute volontaire, ne pas y toucher)
        $tbody_ok .= '<tr class="v"><td>Fichier identique</td><td>'.$fichier.'</td></tr>';
      }
      else
      {
        // Fichier différent
        $tbody_pb .= '<tr class="r"><td>Fichier différent</td><td>'.$fichier.'</td></tr>';
      }
    }
    elseif( (!isset($tab['avant'])) && (substr($fichier,-9)!='.htaccess') )
    {
      // Fichier manquant
      $tbody_pb .= '<tr class="r"><td>Fichier manquant</td><td>'.$fichier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      $tbody_pb .= '<tr class="r"><td>Fichier en trop</td><td>'.$fichier.'</td></tr>';
    }
  }
  // Enregistrement du rapport
  $_SESSION['tmp']['rapport_filename'] = 'rapport_verif_file_appli_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $_SESSION['tmp']['rapport_filename'] , $thead , $tbody_pb.$tbody_ok );
  // Retour
  Json::end( TRUE , 'Rapport des différences trouvées et nettoyage&hellip;' );
}

//
// 5. Nettoyage...
//
if($action=='verif_file_appli_etape5')
{
  $fichier_chemin = URL_DIR_EXPORT.$_SESSION['tmp']['rapport_filename'];
  // Supprimer toutes les données provisoires
  FileSystem::supprimer_dossier($dossier_dezip);
  FileSystem::supprimer_fichier( $file_memo );
  unset($_SESSION['tmp']);
  // Retour
  Json::end( TRUE , $fichier_chemin );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape de mise à jour forcée des bases des établissements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$step_maj = (isset($_POST['step_maj'])) ? Clean::entier($_POST['step_maj']) : 0 ; // Numéro de l'étape

if( ($action=='maj_bases_etabl') && $step_maj )
{
  // 1. Liste des bases
  if($step_maj==1)
  {
    // Récupérer les ids des structures
    $tab_memo = array(
      'base_id' => array_keys( DB_WEBMESTRE_WEBMESTRE::DB_lister_structures_id() ),
      'rapport' => array(),
    );
    // Enregistrer ces informations
    FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
    // Retour
    Json::end( TRUE , 'continuer' );
  }
  else
  {
    // Récupérer les informations
    $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
    // n. Étape suivante
    if(!empty($tab_memo['base_id']))
    {
      $base_id = current($tab_memo['base_id']);
      DBextra::charger_parametres_mysql_supplementaires($base_id);
      $version_base = DB_STRUCTURE_MAJ_BASE::DB_version_base();
      if(empty($tab_memo['rapport'][$base_id]))
      {
        $tab_memo['rapport'][$base_id] = $version_base;
      }
      // base déjà à jour
      if($tab_memo['rapport'][$base_id] == VERSION_BASE_STRUCTURE)
      {
        array_shift($tab_memo['base_id']);
        // Enregistrer ces informations
        FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
        // Retour
        Json::end( TRUE , 'continuer' );
      }
      // on lance la maj "classique"
      if($version_base != VERSION_BASE_STRUCTURE)
      {
        $maj_classique = TRUE;
        // Bloquer l'application
        LockAcces::bloquer_application( 'automate' , $base_id , 'Mise à jour de la base en cours.' );
        // Lancer une mise à jour de la base
        DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base);
      }
      else
      {
        $maj_classique = FALSE;
      }
      // test si cela nécessite une mise à jour complémentaire
      $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire();
      if(!$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'])
      {
        // Débloquer l'application
        LockAcces::debloquer_application( 'automate' , $base_id );
        array_shift($tab_memo['base_id']);
        // Enregistrer ces informations
        FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
        // Retour
        Json::end( TRUE , 'continuer' );
      }
      elseif($maj_classique)
      {
        // on fera la maj complémentaire au prochain coup
        Json::end( TRUE , 'continuer' );
      }
      else
      {
        // on lance une étape de la maj complémentaire
        DB_STRUCTURE_MAJ_BASE::DB_maj_base_complement();
        if(!$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'])
        {
          LockAcces::debloquer_application( 'automate' , $base_id );
          array_shift($tab_memo['base_id']);
        }
        // Enregistrer ces informations
        FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
        // Retour
        Json::end( TRUE , 'continuer' );
      }
    }
    // n. Dernière étape
    else
    {
      // Rapport
      $thead = '<tr><td>Mise à jour forcée des bases - '.date('d/m/Y H:i:s').'</td></tr>';
      $tbody = '';
      foreach($tab_memo['rapport'] as $base_id => $version_base)
      {
        $tbody .= ($version_base==VERSION_BASE_STRUCTURE) ? '<tr><td class="b">Base n°'.$base_id.' déjà à jour.</td></tr>' : '<tr><td class="v">Base n°'.$base_id.' mise à jour depuis la version '.$version_base.'.</td></tr>' ;
      }
      // Enregistrement du rapport
      $fichier_rapport = 'rapport_maj_bases_etabl_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
      FileSystem::fabriquer_fichier_rapport( $fichier_rapport , $thead , $tbody );
      $fichier_chemin = URL_DIR_EXPORT.$fichier_rapport;
      // Supprimer les informations provisoires
      FileSystem::supprimer_fichier( $file_memo );
      // Retour
      Json::end( TRUE , $fichier_chemin );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape de nettoyage des fichiers temporaires des établissements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$step_clean = (isset($_POST['step_clean'])) ? Clean::entier($_POST['step_clean']) : 0 ; // Numéro de l'étape

if( ($action=='clean_file_temp') && $step_clean )
{
  // 1. Liste des bases
  if($step_clean==1)
  {
    // Récupérer les ids des structures
    $tab_memo = array(
      'base_id' => array_keys( DB_WEBMESTRE_WEBMESTRE::DB_lister_structures_id() ),
      'rapport' => array(),
    );
    // Enregistrer ces informations
    FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
    // Retour
    Json::end( TRUE , 'continuer' );
  }
  else
  {
    // Récupérer les informations
    $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
    // n. Étape suivante
    if(!empty($tab_memo['base_id']))
    {
      $base_id = current($tab_memo['base_id']);
      $tab_memo['rapport'][$base_id] = FileSystem::nettoyer_fichiers_temporaires_etablissement($base_id);
      array_shift($tab_memo['base_id']);
      // Enregistrer ces informations
      FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
      // Retour
      Json::end( TRUE , 'continuer' );
    }
    // n. Dernière étape
    else
    {
      // Rapport
      $thead = '<tr><td>Nettoyage forcé des fichiers temporaires - '.date('d/m/Y H:i:s').'</td></tr>';
      $tbody = '';
      $total = 0;
      foreach($tab_memo['rapport'] as $base_id => $nb_suppression)
      {
        $total += $nb_suppression;
        $s = ($nb_suppression>1) ? 's' : '' ;
        $tbody .= ($nb_suppression) ? '<tr><td class="v">Base n°'.$base_id.' &rarr; '.$nb_suppression.' fichier'.$s.' / dossier'.$s.' supprimé'.$s.'.</td></tr>' : '<tr><td class="b">Base n°'.$base_id.' &rarr; rien à signaler.</td></tr>' ;
      }
      $s = ($total>1) ? 's' : '' ;
      $tfoot = '<tr><td>'.$total.' fichier'.$s.' / dossier'.$s.' supprimé'.$s.'</td></tr>';
      // Enregistrement du rapport
      $fichier_rapport = 'rapport_clean_file_temp_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
      FileSystem::fabriquer_fichier_rapport( $fichier_rapport , $thead , $tbody , $tfoot );
      $fichier_chemin = URL_DIR_EXPORT.$fichier_rapport;
      // Supprimer les informations provisoires
      FileSystem::supprimer_fichier( $file_memo );
        // Retour
      Json::end( TRUE , $fichier_chemin );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
