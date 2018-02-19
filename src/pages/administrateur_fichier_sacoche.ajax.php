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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$etape     = (isset($_POST['f_etape']))  ? Clean::entier($_POST['f_etape']) : FALSE;
$tab_eleve = (isset($_POST['f_eleve']))  ? explode(',',$_POST['f_eleve'])   : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , TRUE /*time*/ );

$dossier_temp_import = CHEMIN_DOSSIER_IMPORT.$_SESSION['BASE'].DS;
$dossier_temp_export = CHEMIN_DOSSIER_EXPORT.$_SESSION['BASE'].DS;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de saisies SACoche - 1 Récupération des élèves + Extraction des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && ($etape==1) && count($tab_eleve) )
{
  $listing_eleve_id = implode(',',$tab_eleve);
  // Récupérer les saisies, donc aussi les identifiants des élèves et des items
  $tab_item = array();
  $tab_user = array();
  $tab_code = array();
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves( $listing_eleve_id );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucune saisie d\'évaluation trouvée !');
  }
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp_export);
  // Infos concernant les saisies
  $nb_saisies = count($DB_TAB);
  $xml_saisie = '<saisies>'."\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_user[$DB_ROW['user_id']] = $DB_ROW['user_id'];
    $tab_item[$DB_ROW['item_id']] = $DB_ROW['item_id'];
    if(is_numeric($DB_ROW['note']))
    {
      $tab_code[$DB_ROW['note']] = $DB_ROW['note'];
    }
    $xml_saisie.= '  <saisie user="'.$DB_ROW['user_id'].'" item="'.$DB_ROW['item_id'].'" date="'.$DB_ROW['date'].'" note="'.$DB_ROW['note'].'" info="'.html($DB_ROW['info']).'" visu="'.$DB_ROW['visible'].'" />'."\r\n";
  }
  $xml_saisie.= '</saisies>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'saisies.xml' , $xml_saisie );
  unset($xml_saisie);
  // Infos concernant les codes d'évaluation
  sort($tab_code);
  $xml_code = '<codes>'."\r\n";
  foreach($tab_code as $code)
  {
    $xml_code.= '  <code id="'.$code.'" valeur="'.$_SESSION['NOTE'][$code]['VALEUR'].'" legende="'.html($_SESSION['NOTE'][$code]['LEGENDE']).'" />'."\r\n";
  }
  $xml_code.= '</codes>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'codes.xml' , $xml_code );
  unset($xml_code);
  // On enregistre les autres infos
  FileSystem::ecrire_fichier( $dossier_temp_export.'nb_saisies.txt' , $nb_saisies );
  FileSystem::ecrire_fichier( $dossier_temp_export.'items.txt' , serialize($tab_item) );
  FileSystem::ecrire_fichier( $dossier_temp_export.'users.txt' , serialize($tab_user) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Extraction des élèves&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de saisies SACoche - 2 Extraction des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && ($etape==2) )
{
  // Variables nécessaires
  $tab_user = unserialize( file_get_contents($dossier_temp_export.'users.txt') );
  $nb_eleves = count($tab_user);
  // Infos concernant les élèves
  $xml_user = '<users>'."\r\n";
  $listing_user_id = implode(',',$tab_user);
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( $listing_user_id , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_nom,user_prenom' );
  foreach($DB_TAB as $DB_ROW)
  {
    $xml_user.= '  <user id="'.$DB_ROW['user_id'].'" sconet="'.$DB_ROW['user_sconet_id'].'" elenoet="'.$DB_ROW['user_sconet_elenoet'].'" reference="'.html($DB_ROW['user_reference']).'" nom="'.html($DB_ROW['user_nom']).'" prenom="'.html($DB_ROW['user_prenom']).'" />'."\r\n";
  }
  $xml_user.= '</users>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'users.xml' , $xml_user );
  unset($xml_user);
  // On enregistre les autres infos
  FileSystem::ecrire_fichier( $dossier_temp_export.'nb_eleves.txt' , $nb_eleves );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Extraction des référentiels&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de saisies SACoche - 3 Extraction des référentiels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && ($etape==3) )
{
  // Variables nécessaires
  $tab_item = unserialize( file_get_contents($dossier_temp_export.'items.txt') );
  $nb_items = count($tab_item);
  $tab_matiere = array();
  $tab_niveau  = array();
  $tab_domaine = array();
  $tab_theme   = array();
  // Infos concernant les items (et les arborescences associées)
  $xml_item    = '<items>'."\r\n";
  $listing_item_id = implode(',',$tab_item);
  $DB_TAB = DB_STRUCTURE_BILAN::recuperer_arborescence_items( $listing_item_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_matiere[$DB_ROW['matiere_id']] = '  <matiere id="'.$DB_ROW['matiere_id'].'" nom="'.html($DB_ROW['matiere_nom']).'" />'."\r\n";
    $tab_niveau[$DB_ROW['niveau_id']]   = '  <niveau id="'.$DB_ROW['niveau_id'].'" nom="'.html($DB_ROW['niveau_nom']).'" />'."\r\n";
    $tab_domaine[$DB_ROW['domaine_id']] = '  <domaine id="'.$DB_ROW['domaine_id'].'" nom="'.html($DB_ROW['domaine_nom']).'" matiere="'.$DB_ROW['matiere_id'].'" niveau="'.$DB_ROW['niveau_id'].'" />'."\r\n";
    $tab_theme[$DB_ROW['theme_id']]     = '  <theme id="'.$DB_ROW['theme_id'].'" nom="'.html($DB_ROW['theme_nom']).'" domaine="'.$DB_ROW['domaine_id'].'" />'."\r\n";
    $xml_item.= '  <item id="'.$DB_ROW['item_id'].'" nom="'.html($DB_ROW['item_nom']).'" theme="'.$DB_ROW['theme_id'].'" />'."\r\n";
  }
  $xml_item.= '</items>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'items.xml' , $xml_item );
  unset($xml_item);
  // Infos concernant les éléments de référentiel associés
  FileSystem::ecrire_fichier( $dossier_temp_export.'matieres.xml' , '<matieres>'."\r\n".implode('',$tab_matiere).'</matieres>'."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp_export.'niveaux.xml'  , '<niveaux>' ."\r\n".implode('',$tab_niveau ).'</niveaux>' ."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp_export.'domaines.xml' , '<domaines>'."\r\n".implode('',$tab_domaine).'</domaines>'."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp_export.'themes.xml'   , '<themes>'  ."\r\n".implode('',$tab_theme  ).'</themes>'  ."\r\n" );
  unset( $tab_matiere , $tab_niveau , $tab_domaine , $tab_theme );
  // On enregistre les autres infos
  FileSystem::ecrire_fichier( $dossier_temp_export.'nb_items.txt' , $nb_items );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Extraction des archives PDF&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de saisies SACoche - 4 Extraction des archives PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && ($etape==4) )
{
  // Variables nécessaires
  $tab_user = unserialize( file_get_contents($dossier_temp_export.'users.txt') );
  $listing_user_id = implode(',',$tab_user);
  list( $DB_TAB_Archives , $DB_TAB_Images ) = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_archive_avec_infos( $listing_user_id );
  $nb_archives = count($DB_TAB_Archives);
  // Infos concernant les archives
  $xml_archive = '<archives>'."\r\n";
  foreach($DB_TAB_Archives as $key => $DB_ROW)
  {
    $xml_archive.= '  <archive user_id="'.$DB_ROW['user_id'].'" uai="'.$DB_ROW['structure_uai'].'" annee="'.$DB_ROW['annee_scolaire'].'" type="'.$DB_ROW['archive_type'].'" ref="'.$DB_ROW['archive_ref'].'" periode_id="'.$DB_ROW['periode_id'].'" periode_nom="'.html($DB_ROW['periode_nom']).'" structure="'.html($DB_ROW['structure_denomination']).'" version="'.$DB_ROW['sacoche_version'].'" date_generation="'.$DB_ROW['archive_date_generation'].'" date_eleve="'.$DB_ROW['archive_date_consultation_eleve'].'" date_parent="'.$DB_ROW['archive_date_consultation_parent'].'" contenu="'.html($DB_ROW['archive_contenu']).'" image1="'.$DB_ROW['archive_md5_image1'].'" image2="'.$DB_ROW['archive_md5_image2'].'" image3="'.$DB_ROW['archive_md5_image3'].'" image4="'.$DB_ROW['archive_md5_image4'].'" />'."\r\n";
  }
  $xml_archive.= '</archives>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'archives.xml' , $xml_archive );
  // Infos concernant les images
  $xml_image   = '<images>'."\r\n";
  foreach($DB_TAB_Images as $image_md5 => $DB_ROW)
  {
    $xml_image.= '  <image md5="'.$image_md5.'" hexa="'.bin2hex($DB_ROW[0]['archive_image_contenu']).'" />'."\r\n";
  }
  $xml_image.= '</images>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp_export.'images.xml' , $xml_image );
  unset($xml_user);
  // On enregistre les autres infos
  FileSystem::ecrire_fichier( $dossier_temp_export.'nb_archives.txt' , $nb_archives );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Fabrication du fichier compressé final&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de saisies SACoche - 5 Zip et bilan
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && ($etape==5) )
{
  // Variables nécessaires
  $nb_saisies  = file_get_contents($dossier_temp_export.'nb_saisies.txt');
  $nb_eleves   = file_get_contents($dossier_temp_export.'nb_eleves.txt');
  $nb_items    = file_get_contents($dossier_temp_export.'nb_items.txt');
  $nb_archives = file_get_contents($dossier_temp_export.'nb_archives.txt');
  // Retirer les fichiers de travail temporaires
  FileSystem::supprimer_fichier( $dossier_temp_export.'users.txt' );
  FileSystem::supprimer_fichier( $dossier_temp_export.'items.txt' );
  FileSystem::supprimer_fichier( $dossier_temp_export.'nb_saisies.txt' );
  FileSystem::supprimer_fichier( $dossier_temp_export.'nb_eleves.txt' );
  FileSystem::supprimer_fichier( $dossier_temp_export.'nb_items.txt' );
  FileSystem::supprimer_fichier( $dossier_temp_export.'nb_archives.txt' );
  // On zippe (gain significatif de facteur 15 à 20)
  $fichier_zip_nom = 'sacoche_transfert_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::zip_fichiers( $dossier_temp_export , CHEMIN_DOSSIER_EXPORT , $fichier_zip_nom );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Supprimer le dossier temporaire
  FileSystem::supprimer_dossier($dossier_temp_export);
  // Afficher le retour
  $fichier_lien = URL_DIR_EXPORT.$fichier_zip_nom;
  $ss = ($nb_saisies>1)  ? 's' : '' ;
  $se = ($nb_eleves>1)   ? 's' : '' ;
  $si = ($nb_items>1)    ? 's' : '' ;
  $sa = ($nb_archives>1) ? 's' : '' ;
  Json::add_str('<li><label class="valide">Fichier d\'export généré avec '.number_format($nb_saisies,0,'',' ').' saisie'.$ss.' d\'évaluation'.$ss.' trouvée'.$ss.' concernant '.number_format($nb_eleves,0,'',' ').' élève'.$se.' et '.number_format($nb_items,0,'',' ').' item'.$si.', ainsi que '.number_format($nb_archives,0,'',' ').' archive'.$sa.'.</label></li>'.NL);
  Json::add_str('<li><a target="_blank" rel="noopener noreferrer" href="'.$fichier_lien.'"><span class="file file_zip">Récupérer le fichier au format <em>zip</em>.</span></a></li>'.NL);
  Json::add_str('<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>'.NL);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 0 Récupération du fichier + Analyse des codes de notation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==0) )
{
  // Récupération du fichier
  $fichier_upload_nom = 'sacoche_transfert_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_upload_nom /*fichier_nom*/ , array('zip') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp_import);
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( CHEMIN_DOSSIER_IMPORT.$fichier_upload_nom , $dossier_temp_import , FALSE /*use_ZipArchive*/ , array('xml') /*tab_extensions_autorisees*/ );
  if($code_erreur)
  {
    FileSystem::supprimer_dossier($dossier_temp_import); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , 'Erreur d\'extraction du contenu ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  // Vérifier le contenu : noms des fichiers
  $tab_fichiers_archive = array( 'matieres.xml' , 'niveaux.xml'  , 'domaines.xml' , 'themes.xml' , 'items.xml' , 'users.xml' , 'saisies.xml' , 'codes.xml' , 'archives.xml' , 'images.xml' );
  $tab_fichiers_cherches = array_fill_keys( $tab_fichiers_archive , TRUE );
  $tab_fichiers_trouves  = FileSystem::lister_contenu_dossier($dossier_temp_import);
  foreach($tab_fichiers_trouves as $fichier_nom)
  {
    unset($tab_fichiers_cherches[$fichier_nom]);
  }
  if(count($tab_fichiers_cherches))
  {
    FileSystem::supprimer_dossier($dossier_temp_import); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , 'Cette archive ZIP ne semble pas contenir les fichiers d\'un export de saisies effectué par SACoche !' );
  }
  // On passe à l'analyse des codes de notation
  $fichier_nom = 'codes';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->code as $code)
  {
    $code_id      = Clean::entier($code->attributes()->id);
    $code_valeur  = Clean::entier($code->attributes()->valeur);
    $code_legende = Clean::texte( $code->attributes()->legende);
    Json::add_str('Code #'.$code_id.' &rarr; valeur '.$code_valeur.' (légende "'.html($code_legende).'")<br />');
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 1 Analyse des matières
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==1) )
{
  // Variables nécessaires
  $tab_matiere = array();
  $fichier_nom = 'matieres';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->matiere as $matiere)
  {
    $matiere_id  = Clean::entier($matiere->attributes()->id);
    $matiere_nom = Clean::texte( $matiere->attributes()->nom);
    $tab_matiere[$matiere_id] = array(
      'base_id' => FALSE,
      'nom'     => $matiere_nom,
    );
  }
  // On compare avec la base
  $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement(TRUE);
  foreach($DB_TAB as $key => $DB_ROW)
  {
    // Pour les matières partagées, on se fie à l'identifiant
    if( $DB_ROW['matiere_id'] <= ID_MATIERE_PARTAGEE_MAX )
    {
      if(isset($tab_matiere[$DB_ROW['matiere_id']]))
      {
        $tab_matiere[$DB_ROW['matiere_id']]['base_id'] = (int)$DB_ROW['matiere_id'];
      }
      unset($DB_TAB[$key]);
    }
    // Pour les matières spécifiques, on se base sur le nom
    else
    {
      foreach($tab_matiere as $matiere_id => $matiere_info)
      {
        if( $matiere_id > ID_MATIERE_PARTAGEE_MAX )
        {
          if( $matiere_info['nom'] == $DB_ROW['matiere_nom'] )
          {
            $tab_matiere[$matiere_id]['base_id'] = (int)$DB_ROW['matiere_id'];
            unset($DB_TAB[$key]);
            break;
          }
        }
      }
    }
  }
  // Pour les matières spécifiques qui resteraient, on cherche un nom approchant
  if(!empty($DB_TAB))
  {
    foreach($tab_matiere as $matiere_id => $matiere_info)
    {
      if( ( $matiere_id > ID_MATIERE_PARTAGEE_MAX ) && !$matiere_info['base_id'] )
      {
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if( Outil::pourcentage_commun( $matiere_info['nom'] , $DB_ROW['matiere_nom'] ) > 80 )
          {
            $tab_matiere[$matiere_id]['base_id'] = (int)$DB_ROW['matiere_id'];
            unset($DB_TAB[$key]);
            break;
          }
        }
      }
    }
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  foreach($tab_matiere as $matiere_id => $matiere_info)
  {
    if(!$matiere_info['base_id'])
    {
      $matiere_type = ( $matiere_id <= ID_MATIERE_PARTAGEE_MAX ) ? 'partagée' : 'spécifique' ;
      $contenu_problemes .= '<li>Matière '.$matiere_type.' "'.html($matiere_info['nom']).'" non trouvée.</li>'.NL;
      unset($tab_matiere[$matiere_id]);
    }
  }
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_matiere) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des niveaux&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 2 Analyse des niveaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==2) )
{
  // Variables nécessaires
  $tab_niveau = array();
  $fichier_nom = 'niveaux';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->niveau as $niveau)
  {
    $niveau_id  = Clean::entier($niveau->attributes()->id);
    $niveau_nom = Clean::texte($niveau ->attributes()->nom);
    $tab_niveau[$niveau_id] = array(
      'base_id' => FALSE,
      'nom'     => $niveau_nom,
    );
  }
  // On compare avec la base
  $DB_TAB = DB_STRUCTURE_NIVEAU::DB_lister_niveaux_etablissement( TRUE /*with_particuliers*/ );
  foreach($DB_TAB as $key => $DB_ROW)
  {
    // Pour les niveaux partagées, on se fie à l'identifiant
    if( $DB_ROW['niveau_id'] <= ID_NIVEAU_PARTAGE_MAX )
    {
      if(isset($tab_niveau[$DB_ROW['niveau_id']]))
      {
        $tab_niveau[$DB_ROW['niveau_id']]['base_id'] = (int)$DB_ROW['niveau_id'];
      }
      unset($DB_TAB[$key]);
    }
    // Pour les niveaux spécifiques, on se base sur le nom
    else
    {
      foreach($tab_niveau as $niveau_id => $niveau_info)
      {
        if( $niveau_id > ID_NIVEAU_PARTAGE_MAX )
        {
          if( $niveau_info['nom'] == $DB_ROW['niveau_nom'] )
          {
            $tab_niveau[$niveau_id]['base_id'] = (int)$DB_ROW['niveau_id'];
            unset($DB_TAB[$key]);
            break;
          }
        }
      }
    }
  }
  // Pour les niveaux spécifiques qui resteraient, on cherche un nom approchant
  if(!empty($DB_TAB))
  {
    foreach($tab_niveau as $niveau_id => $niveau_info)
    {
      if( ( $niveau_id > ID_NIVEAU_PARTAGE_MAX ) && !$niveau_info['base_id'] )
      {
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if( Outil::pourcentage_commun( $niveau_info['nom'] , $DB_ROW['niveau_nom'] ) > 80 )
          {
            $tab_niveau[$niveau_id]['base_id'] = (int)$DB_ROW['niveau_id'];
            unset($DB_TAB[$key]);
            break;
          }
        }
      }
    }
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  foreach($tab_niveau as $niveau_id => $niveau_info)
  {
    if(!$niveau_info['base_id'])
    {
      $niveau_type = ( $niveau_id <= ID_NIVEAU_PARTAGE_MAX ) ? 'partagé' : 'spécifique' ;
      $contenu_problemes .= '<li>Niveau '.$niveau_type.' "'.html($niveau_info['nom']).'" non trouvé.</li>'.NL;
      unset($tab_niveau[$niveau_id]);
    }
  }
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_niveau) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des référentiels et des domaines&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 3 Analyse des référentiels et des domaines
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==3) )
{
  // Variables nécessaires
  $tab_referentiel = array();
  $tab_domaine     = array();
  $tab_matiere     = unserialize( file_get_contents($dossier_temp_import.'matieres.txt') );
  $tab_niveau      = unserialize( file_get_contents($dossier_temp_import.'niveaux.txt') );
  $tab_base_referentiel_to_domaine = array();
  $tab_base_matiere_id = array();
  $tab_base_niveau_id  = array();
  $fichier_nom = 'domaines';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->domaine as $domaine)
  {
    $domaine_id  = Clean::entier($domaine->attributes()->id);
    $domaine_nom = Clean::texte( $domaine->attributes()->nom);
    $matiere_id  = Clean::entier($domaine->attributes()->matiere);
    $niveau_id   = Clean::entier($domaine->attributes()->niveau);
    // A condition que la matière et le niveau soient présents
    if( !empty($tab_matiere[$matiere_id]['base_id']) && !empty($tab_niveau[$niveau_id]['base_id']) )
    {
      $tab_domaine[$domaine_id] = array(
        'base_id' => FALSE,
        'nom'     => $domaine_nom,
        'matiere' => $matiere_id,
        'niveau'  => $niveau_id,
        'parent'  => html($tab_matiere[$matiere_id]['nom']).' &rarr; '.html($tab_niveau[$niveau_id]['nom']),
      );
      $tab_referentiel[$matiere_id][$niveau_id] = FALSE;
      $tab_base_referentiel_to_domaine[ $tab_matiere[$matiere_id]['base_id'].'x'.$tab_niveau[$niveau_id]['base_id'] ][] = $domaine_id;
      $tab_base_matiere_id[$matiere_id] = $tab_matiere[$matiere_id]['base_id'];
      $tab_base_niveau_id[$niveau_id]   = $tab_niveau[$niveau_id]['base_id'];
    }
  }
  // On compare avec la base (on se base sur le nom)
  if( !empty($tab_base_matiere_id) && !empty($tab_base_niveau_id) )
  {
    $listing_matiere_id = implode(',',$tab_base_matiere_id);
    $listing_niveau_id  = implode(',',$tab_base_niveau_id);
    $DB_TAB = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels_domaines_cibles($listing_matiere_id,$listing_niveau_id);
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if( isset($tab_referentiel[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']]) )
      {
        $tab_referentiel[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = TRUE;
        foreach($tab_base_referentiel_to_domaine[ $DB_ROW['matiere_id'].'x'.$DB_ROW['niveau_id'] ] as $domaine_id)
        {
          if( $tab_domaine[$domaine_id]['nom'] == $DB_ROW['domaine_nom'] )
          {
            $tab_domaine[$domaine_id]['base_id'] = (int)$DB_ROW['domaine_id'];
            unset($DB_TAB[$key]);
            break;
          }
        }
      }
    }
  }
  // Pour les domaines qui resteraient, on cherche un nom approchant
  if(!empty($DB_TAB))
  {
    foreach($tab_domaine as $domaine_id => $domaine_info)
    {
      if( !$domaine_info['base_id'] )
      {
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if( ($tab_matiere[$matiere_id]['base_id']==$DB_ROW['matiere_id']) && ($tab_niveau[$niveau_id]['base_id']==$DB_ROW['niveau_id']) )
          {
            if( Outil::pourcentage_commun( $domaine_info['nom'] , $DB_ROW['domaine_nom'] ) > 80 )
            {
              $tab_domaine[$domaine_id]['base_id'] = (int)$DB_ROW['domaine_id'];
              unset($DB_TAB[$key]);
              break;
            }
          }
        }
      }
    }
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  foreach($tab_referentiel as $matiere_id => $tab_niveau)
  {
    foreach($tab_niveau as $niveau_id => $referentiel_etat)
    {
      if( !$referentiel_etat )
      {
        $contenu_problemes .= '<li>Référentiel "'.html($tab_matiere[$matiere_id]['nom']).'" - "'.html($tab_niveau[$niveau_id]['nom']).'" non trouvé.</li>'.NL;
        unset($tab_domaine[$domaine_id]);
      }
    }
  }
  foreach($tab_domaine as $domaine_id => $domaine_info)
  {
    if(!$domaine_info['base_id'])
    {
      $contenu_problemes .= '<li>Domaine "'.html($domaine_info['nom']).'" non trouvé dans le référentiel "'.$domaine_info['parent'].'".</li>'.NL;
      unset($tab_domaine[$domaine_id]);
    }
  }
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_domaine) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des thèmes&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 4 - Analyse des thèmes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==4) )
{
  // Variables nécessaires
  $tab_theme   = array();
  $tab_domaine = unserialize( file_get_contents($dossier_temp_import.'domaines.txt') );
  $tab_base_domaine_to_theme = array();
  $tab_base_domaine_id = array();
  $fichier_nom = 'themes';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->theme as $theme)
  {
    $theme_id   = Clean::entier($theme->attributes()->id);
    $theme_nom  = Clean::texte( $theme->attributes()->nom);
    $domaine_id = Clean::entier($theme->attributes()->domaine);
    // A condition que le domaine soit présent
    if( !empty($tab_domaine[$domaine_id]['base_id']) )
    {
      $tab_theme[$theme_id] = array(
        'base_id' => FALSE,
        'nom'     => $theme_nom,
        'domaine' => $domaine_id,
        'parent'  => $tab_domaine[$domaine_id]['parent'].' &rarr; '.html($tab_domaine[$domaine_id]['nom']),
      );
      $tab_base_domaine_to_theme[ $tab_domaine[$domaine_id]['base_id'] ][] = $theme_id;
      $tab_base_domaine_id[$domaine_id] = $tab_domaine[$domaine_id]['base_id'];
    }
  }
  // On compare avec la base (on se base sur le nom)
  if(!empty($tab_base_domaine_id))
  {
    $listing_domaine_id  = implode(',',$tab_base_domaine_id);
    $DB_TAB = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels_themes_cibles($listing_domaine_id);
    foreach($DB_TAB as $key => $DB_ROW)
    {
      foreach($tab_base_domaine_to_theme[$DB_ROW['domaine_id']] as $theme_id)
      {
        if( $tab_theme[$theme_id]['nom'] == $DB_ROW['theme_nom'] )
        {
          $tab_theme[$theme_id]['base_id'] = (int)$DB_ROW['theme_id'];
          unset($DB_TAB[$key]);
          break;
        }
      }
    }
  }
  // Pour les thèmes qui resteraient, on cherche un nom approchant
  if(!empty($DB_TAB))
  {
    foreach($tab_theme as $theme_id => $theme_info)
    {
      if( !$theme_info['base_id'] )
      {
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if($tab_domaine[$domaine_id]['base_id']==$DB_ROW['domaine_id'])
          {
            if( Outil::pourcentage_commun( $theme_info['nom'] , $DB_ROW['theme_nom'] ) > 80 )
            {
              $tab_theme[$theme_id]['base_id'] = (int)$DB_ROW['theme_id'];
              unset($DB_TAB[$key]);
              break;
            }
          }
        }
      }
    }
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  foreach($tab_theme as $theme_id => $theme_info)
  {
    if(!$theme_info['base_id'])
    {
      $domaine_id = $theme_info['domaine'];
      $domaine_nom = $tab_domaine[$domaine_id]['nom'];
      $contenu_problemes .= '<li>Thème "'.html($theme_info['nom']).'" non trouvé dans le domaine "'.$theme_info['parent'].'".</li>'.NL;
      unset($tab_theme[$theme_id]);
    }
  }
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_theme) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des items&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 5 - Analyse des items
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==5) )
{
  // Variables nécessaires
  $tab_item  = array();
  $tab_theme = unserialize( file_get_contents($dossier_temp_import.'themes.txt') );
  $tab_base_theme_to_item = array();
  $tab_base_theme_id = array();
  $fichier_nom = 'items';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->item as $item)
  {
    $item_id  = Clean::entier($item->attributes()->id);
    $item_nom = Clean::texte( $item->attributes()->nom);
    $theme_id = Clean::entier($item->attributes()->theme);
    // A condition que le theme soit présent
    if( !empty($tab_theme[$theme_id]['base_id']) )
    {
      $tab_item[$item_id] = array(
        'base_id' => FALSE,
        'nom'     => $item_nom,
        'theme'   => $theme_id,
        'parent'  => $tab_theme[$theme_id]['parent'].' &rarr; '.html($tab_theme[$theme_id]['nom']),
      );
      $tab_base_theme_to_item[ $tab_theme[$theme_id]['base_id'] ][] = $item_id;
      $tab_base_theme_id[$theme_id] = $tab_theme[$theme_id]['base_id'];
    }
  }
  // On compare avec la base (on se base sur le nom)
  if(!empty($tab_base_theme_id))
  {
    $listing_theme_id = implode(',',$tab_base_theme_id);
    $DB_TAB = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels_items_cibles($listing_theme_id);
    foreach($DB_TAB as $key => $DB_ROW)
    {
      foreach($tab_base_theme_to_item[$DB_ROW['theme_id']] as $item_id)
      {
        if( $tab_item[$item_id]['nom'] == $DB_ROW['item_nom'] )
        {
          $tab_item[$item_id]['base_id'] = (int)$DB_ROW['item_id'];
          unset($DB_TAB[$key]);
          break;
        }
      }
    }
  }
  // Pour les items qui resteraient, on cherche un nom approchant
  if(!empty($DB_TAB))
  {
    foreach($tab_item as $item_id => $item_info)
    {
      if( !$item_info['base_id'] )
      {
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if($tab_theme[$theme_id]['base_id']==$DB_ROW['theme_id'])
          {
            if( Outil::pourcentage_commun( $item_info['nom'] , $DB_ROW['item_nom'] ) > 80 )
            {
              $tab_item[$item_id]['base_id'] = (int)$DB_ROW['item_id'];
              unset($DB_TAB[$key]);
              break;
            }
          }
        }
      }
    }
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  foreach($tab_item as $item_id => $item_info)
  {
    if(!$item_info['base_id'])
    {
      $theme_id = $item_info['theme'];
      $theme_nom = $tab_theme[$theme_id]['nom'];
      $contenu_problemes .= '<li>Item "'.html($item_info['nom']).'" non trouvé dans le thème "'.$item_info['parent'].'".</li>'.NL;
      unset($tab_item[$item_id]);
    }
    else
    {
      $tab_item[$item_id] = $item_info['base_id'];
    }
  }
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_item) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des élèves&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 6 - Analyse des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==6) )
{
  // Variables nécessaires
  $tab_user = array();
  $tab_user_fichier = array();
  $tab_user_base    = array();
  $fichier_nom = 'users';
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier
  foreach($xml->user as $user)
  {
    $user_id = Clean::entier( $user->attributes()->id);
    $tab_user_fichier['sconet'   ][$user_id] = Clean::entier( $user->attributes()->sconet);
    $tab_user_fichier['elenoet'  ][$user_id] = Clean::entier( $user->attributes()->elenoet);
    $tab_user_fichier['reference'][$user_id] = Clean::ref(    $user->attributes()->reference);
    $tab_user_fichier['nom'      ][$user_id] = Clean::nom(    $user->attributes()->nom);
    $tab_user_fichier['prenom'   ][$user_id] = Clean::prenom( $user->attributes()->prenom);
  }
  // On récupère le contenu de la base
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' /*profil_type*/ , 1 /*only_actuels*/ , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_nom,user_prenom' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $user_id = (int)$DB_ROW['user_id'];
    $tab_user_base['sconet'   ][$user_id] = $DB_ROW['user_sconet_id'];
    $tab_user_base['elenoet'  ][$user_id] = $DB_ROW['user_sconet_elenoet'];
    $tab_user_base['reference'][$user_id] = $DB_ROW['user_reference'];
    $tab_user_base['nom'      ][$user_id] = $DB_ROW['user_nom'];
    $tab_user_base['prenom'   ][$user_id] = $DB_ROW['user_prenom'];
  }
  // Retenir les problèmes
  $contenu_problemes = '';
  // On compare les listes
  $tab_indice_fichier = array_keys($tab_user_fichier['sconet']);
  // Parcourir chaque entrée du fichier
  foreach($tab_indice_fichier as $i_fichier)
  {
    $id_base = FALSE;
    // Recherche sur sconet
    if( (!$id_base) && ($tab_user_fichier['sconet'][$i_fichier]) )
    {
      $id_base = array_search($tab_user_fichier['sconet'][$i_fichier],$tab_user_base['sconet']);
    }
    // Recherche sur elenoet
    if( (!$id_base) && ($tab_user_fichier['elenoet'][$i_fichier]) )
    {
      $id_base = array_search($tab_user_fichier['elenoet'][$i_fichier],$tab_user_base['elenoet']);
    }
    // Si pas trouvé, recherche sur reference
    if( (!$id_base) && ($tab_user_fichier['reference'][$i_fichier]) )
    {
      $id_base = array_search($tab_user_fichier['reference'][$i_fichier],$tab_user_base['reference']);
    }
    // Si pas trouvé, recherche sur nom prénom
    if(!$id_base)
    {
      $tab_id_nom    = array_keys($tab_user_base['nom'],$tab_user_fichier['nom'][$i_fichier]);
      $tab_id_prenom = array_keys($tab_user_base['prenom'],$tab_user_fichier['prenom'][$i_fichier]);
      $tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
      $nb_homonymes  = count($tab_id_commun);
      if($nb_homonymes==1)
      {
        list($inutile,$id_base) = each($tab_id_commun);
      }
    }
    // Si trouvé
    if($id_base)
    {
      $tab_user[$i_fichier] = $id_base;
      unset(
        $tab_user_base['sconet'   ][$id_base] ,
        $tab_user_base['elenoet'  ][$id_base] ,
        $tab_user_base['reference'][$id_base] ,
        $tab_user_base['nom'      ][$id_base] ,
        $tab_user_base['prenom'   ][$id_base]
      );
    }
    // si pas trouvé
    else
    {
      $contenu_problemes .= '<li>Élève "'.html($tab_user_fichier['nom'][$i_fichier].' '.$tab_user_fichier['prenom'][$i_fichier]).'" ('.$tab_user_fichier['sconet'][$i_fichier].'/'.$tab_user_fichier['elenoet'][$i_fichier].'/'.$tab_user_fichier['reference'][$i_fichier].') non trouvé.</li>'.NL;
    }
    unset(
      $tab_user_fichier['sconet'   ][$i_fichier] ,
      $tab_user_fichier['elenoet'  ][$i_fichier] ,
      $tab_user_fichier['reference'][$i_fichier] ,
      $tab_user_fichier['nom'      ][$i_fichier] ,
      $tab_user_fichier['prenom'   ][$i_fichier]
    );
  }
  // Retenir les problèmes
  if($contenu_problemes)
  {
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_user) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des saisies&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 7 - Analyse des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==7) )
{
  // Variables nécessaires
  $tab_saisie = array();
  $tab_base   = array();
  $tab_item = unserialize( file_get_contents($dossier_temp_import.'items.txt') );
  $tab_user = unserialize( file_get_contents($dossier_temp_import.'users.txt') );
  $fichier_nom = 'saisies';
  $nb_ok = 0;
  $nb_pb = 0;
  $nb_notes_deja_presentes = 0;
  $tab_note = array_flip( array('1','2','3','4','5','6','AB','NE','NF','NN','NR','DI') );
  // Ouverture du XML
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu de la base
  $listing_user_id = implode(',',$tab_user);
  $listing_item_id = implode(',',$tab_item);
  if( $listing_user_id && $listing_item_id )
  {
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $listing_user_id , $listing_item_id , 0 /*matiere_id*/ , NULL /*date_mysql_debut*/ , NULL /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base[$DB_ROW['eleve_id']][$DB_ROW['item_id']][$DB_ROW['date']] = TRUE;
    }
  }
  /* 
   * Libérer de la place mémoire.
   * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
   * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
   */
  DB::close(SACOCHE_STRUCTURE_BD_NAME);
  unset($DB_TAB);
  // On récupère le contenu du fichier
  foreach($xml->saisie as $saisie)
  {
    $user_id = Clean::entier(    $saisie->attributes()->user);
    $item_id = Clean::entier(    $saisie->attributes()->item);
    $date    = Clean::date_mysql($saisie->attributes()->date);
    $note    = Clean::texte(     $saisie->attributes()->note);
    $info    = Clean::texte(     $saisie->attributes()->info);
    $visu    = Clean::date_mysql($saisie->attributes()->visu);
    // A condition que les valeurs soient valides
    if( isset($tab_note[$note]) && ($date!='0000-00-00') && ($visu!='0000-00-00') )
    {
      // A condition que l'item et l'élève soient présents
      if( isset($tab_user[$user_id]) && isset($tab_item[$item_id]) )
      {
        // A condition qu'il n'y ait pas déjà une évaluation à cette date
        if( !isset($tab_base[$tab_user[$user_id]][$tab_item[$item_id]][$date]) )
        {
          $tab_saisie[] = array( $tab_user[$user_id] , $tab_item[$item_id] , $note , $info , $date , $visu );
        }
        else
        {
          $nb_notes_deja_presentes++;
          $nb_pb++;
        }
      }
      else
      {
        $nb_pb++;
      }
    }
  }
  // Retenir les problèmes
  if($nb_notes_deja_presentes)
  {
    $s = ($nb_notes_deja_presentes>1) ? 's' : '' ;
    $contenu_problemes = '<li>'.number_format($nb_notes_deja_presentes,0,'',' ').' saisie'.$s.' déjà présente'.$s.' (même élève, même item, même jour).</li>'.NL;
    FileSystem::ecrire_fichier( $dossier_temp_import.'problemes.txt' , $contenu_problemes , FILE_APPEND );
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_saisie) );
  FileSystem::ecrire_fichier( $dossier_temp_import.'nb_pb.txt' , $nb_pb );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Association de numéros de devoirs virtuels&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 8 - Association de numéros de devoirs virtuels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==8) )
{
  // Variables nécessaires
  $fichier_nom = 'saisies';
  $tab_devoir = array();
  $tab_saisie = unserialize( file_get_contents($dossier_temp_import.$fichier_nom.'.txt') );
  // La table sacoche_saisie comporte une clef PRIMARY KEY ( devoir_id , eleve_id , item_id ).
  // Du coup il faut associer aux saisies un numéro de devoir disponible et compatible...
  // En pratique, on a besoin de très peu d'identifiants de devoirs, et on les trouve facilement sur une base normalement utilisée.
  $tab_devoir_id_dispo = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_devoirs_id_disponibles();
  foreach($tab_saisie as $key => $tab)
  {
    list( $eleve_id , $item_id ) = $tab;
    $search = $eleve_id.'x'.$item_id;
    foreach($tab_devoir_id_dispo as $devoir_id)
    {
      $memo = $search.'x'.$devoir_id;
      if(!isset($tab_devoir[$memo]))
      {
        $tab_saisie[$key][] = $devoir_id;
        $tab_devoir[$memo] = TRUE;
        $search = FALSE;
        break;
      }
    }
    if($search)
    {
      Json::end( FALSE , 'Pas assez d\'identifiants de devoirs supprimés disponibles pour créer les évaluations virtuelles nécéssaires&hellip;');
    }
  }
  // On enregistre les infos
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_saisie) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Analyse des archives&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 9 - Analyse des archives
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==9) )
{
  // Variables nécessaires
  $tab_image   = array();
  $tab_archive = array();
  $tab_user = unserialize( file_get_contents($dossier_temp_import.'users.txt') );
  // Ouverture du XML des images
  $fichier_nom = 'images';
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier des images
  foreach($xml->image as $image)
  {
    $md5  = Clean::code($image->attributes()->md5);
    $hexa = Clean::code($image->attributes()->hexa);
    // A condition que les valeurs soient valides
    if( $md5 && $hexa )
    {
      $tab_image[$md5] = array( 'hexa'=>$hexa , 'used'=>FALSE );
    }
  }
  // Ouverture du XML des archives
  $fichier_nom = 'archives';
  $fichier_contenu = file_get_contents($dossier_temp_import.$fichier_nom.'.xml');
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'.xml" n\'est pas un XML valide !');
  }
  // On récupère le contenu du fichier des archives
  foreach($xml->archive as $archive)
  {
    $user_id         = Clean::entier(    $archive->attributes()->user_id);
    $uai             = Clean::uai(       $archive->attributes()->uai);
    $annee           = Clean::texte(     $archive->attributes()->annee);
    $type            = Clean::code(      $archive->attributes()->type);
    $ref             = Clean::code(      $archive->attributes()->ref);
    $periode_id      = Clean::entier(    $archive->attributes()->periode_id);
    $periode_nom     = Clean::texte(     $archive->attributes()->periode_nom);
    $structure       = Clean::texte(     $archive->attributes()->structure);
    $version         = Clean::date_mysql($archive->attributes()->version);
    $date_generation = Clean::date_mysql($archive->attributes()->date_generation);
    $date_eleve      = Clean::date_mysql($archive->attributes()->date_eleve);
    $date_parent     = Clean::date_mysql($archive->attributes()->date_parent);
    $contenu         = Clean::texte(     $archive->attributes()->contenu);
    $image1          = Clean::code(      $archive->attributes()->image1);
    $image2          = Clean::code(      $archive->attributes()->image2);
    $image3          = Clean::code(      $archive->attributes()->image3);
    $image4          = Clean::code(      $archive->attributes()->image4);
    // A condition que les valeurs soient valides
    if( in_array($type,array('sacoche','livret')) && $periode_nom && $structure && ($version!='0000-00-00') && ($date_generation!='0000-00-00') && !is_null(json_decode($contenu)) && ( !$image1 || isset($tab_image[$image1]) ) && ( !$image2 || isset($tab_image[$image2]) ) && ( !$image3 || isset($tab_image[$image3]) ) && ( !$image4 || isset($tab_image[$image4]) ) )
    {
      // A condition que l'élève soit présent
      if( isset($tab_user[$user_id]) )
      {
        $date_generation = ($date_generation!='0000-00-00') ? $date_generation : NULL ;
        $date_eleve      = ($date_eleve     !='0000-00-00') ? $date_eleve      : NULL ;
        $date_parent     = ($date_parent    !='0000-00-00') ? $date_parent     : NULL ;
        $image1 = ($image1) ? $image1 : NULL ;
        $image2 = ($image2) ? $image2 : NULL ;
        $image3 = ($image3) ? $image3 : NULL ;
        $image4 = ($image4) ? $image4 : NULL ;
        $tab_archive[] = array( $tab_user[$user_id] , $uai , $annee , $type , $ref , $periode_id , $periode_nom , $structure , $version , $date_generation , $date_eleve , $date_parent , $contenu , $image1 , $image2 , $image3 , $image4 );
        if($image1) { $tab_image[$image1]['used'] = TRUE; }
        if($image2) { $tab_image[$image2]['used'] = TRUE; }
        if($image3) { $tab_image[$image3]['used'] = TRUE; }
        if($image4) { $tab_image[$image4]['used'] = TRUE; }
      }
    }
  }
  // On enregistre les infos des archives
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_archive) );
  // On repasse aux images pour ne garder que le nécessaire
  $fichier_nom = 'images';
  foreach($tab_image as $md5 => $tab)
  {
    if(!$tab['used'])
    {
      unset($tab_image[$md5]);
    }
    else
    {
      $tab_image[$md5] = $tab['hexa'];
    }
  }
  // On enregistre les infos des images
  FileSystem::ecrire_fichier( $dossier_temp_import.$fichier_nom.'.txt' , serialize($tab_image) );
  // Passage à l'étape suivante
  Json::end( TRUE , 'Enregistrement des données&hellip;' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de saisies SACoche - 10 - Enregistrement des données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import') && ($etape==10) )
{
  $nb_pb = file_get_contents($dossier_temp_import.'nb_pb.txt');
  // On commence par les saisies
  $tab_saisie = unserialize( file_get_contents($dossier_temp_import.'saisies.txt') );
  $nb_ok = count($tab_saisie);
  // Lancer les requêtes par lot car potentiellement de nombreuses lignes & résultat
  if($nb_ok)
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_saisies( $tab_saisie , $nb_ok );
    $sok = ($nb_ok>1) ? 's' : '' ;
    Json::add_str('<li><label class="valide">'.number_format($nb_ok,0,'',' ').' saisie'.$sok.' d\'évaluation'.$sok.' importée'.$sok.'.</label></li>'.NL);
  }
  else
  {
    Json::add_str('<li><label class="alerte">Aucune saisie d\'évaluation importée !</label></li>'.NL);
  }
  // On poursuit avec les archives
  $tab_archive = unserialize( file_get_contents($dossier_temp_import.'archives.txt') );
  $nb_ok = count($tab_archive);
  // Lancer les requêtes à l'unité & résultat
  if($nb_ok)
  {
    foreach($tab_archive as $tab)
    {
      list( $eleve_id , $uai , $annee , $type , $ref , $periode_id , $periode_nom , $structure , $version , $date_generation , $date_eleve , $date_parent , $contenu , $image1 , $image2 , $image3 , $image4 ) = $tab;
      $livret_archive_id = DB_STRUCTURE_OFFICIEL::DB_ajouter_officiel_archive( $eleve_id , $uai , $annee , $type , $ref , $periode_id , $periode_nom , $structure , $version , $contenu , array($image1,$image2,$image3,$image4) , array($date_generation,$date_eleve,$date_parent) );
    }
    $sok = ($nb_ok>1) ? 's' : '' ;
    Json::add_str('<li><label class="valide">'.number_format($nb_ok,0,'',' ').' archive'.$sok.' de bilan'.$sok.' officiel'.$sok.' importée'.$sok.'.</label></li>'.NL);
  }
  else
  {
    Json::add_str('<li><label class="alerte">Aucune archive de bilan officiel importée !</label></li>'.NL);
  }
  // On termine avec les images
  $tab_image = unserialize( file_get_contents($dossier_temp_import.'images.txt') );
  $nb_ok = count($tab_image);
  // Lancer les requêtes à l'unité & résultat
  if($nb_ok)
  {
    foreach($tab_image as $image_md5 => $image_hexa)
    {
      $image_blob = hex2bin($image_hexa);
      $livret_archive_id = DB_STRUCTURE_OFFICIEL::DB_ajouter_officiel_archive_image( $image_md5 , $image_blob );
    }
  }
  // Affichage des pbs éventuels
  if($nb_pb)
  {
    $txt_problemes = trim( file_get_contents($dossier_temp_import.'problemes.txt') );
    $nb_motifs = substr_count( $txt_problemes , NL );
    $spb = ($nb_pb>1)     ? 's' : '' ;
    $smo = ($nb_motifs>1) ? 's' : '' ;
    Json::add_str('<li><label class="erreur">'.number_format($nb_pb,0,'',' ').' saisie'.$spb.' d\'évaluation'.$spb.' non importée'.$spb.' pour le'.$smo.' motif'.$smo.' suivant'.$smo.' :</label></li>'.NL);
    Json::add_str($txt_problemes);
  }
  // Supprimer le dossier temporaire
  // FileSystem::supprimer_dossier($dossier_temp_import);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
