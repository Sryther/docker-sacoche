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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action   = (isset($_POST['f_action']))   ? $_POST['f_action']                  : '';
$image_id = (isset($_POST['f_image_id'])) ? Clean::entier($_POST['f_image_id']) : 0;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Codes de notation : nombre, valeur, symbole coloré, sigle, légende, clavier
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='save_notes') && isset($_POST['notes_actif']) && isset($_POST['notes_ordre']) )
{
  // Normalement ce sont des tableaux qui sont transmis, mais au cas où...
  $tab_notes_actif = is_array($_POST['notes_actif']) ? $_POST['notes_actif'] : explode(',',$_POST['notes_actif']);
  $tab_notes_ordre = is_array($_POST['notes_ordre']) ? $_POST['notes_ordre'] : explode(',',$_POST['notes_ordre']);
  $tab_notes_actif = array_intersect ( array_filter( Clean::map('entier',$tab_notes_actif) , 'positif' ) , array(1,2,3,4,5,6) );
  $tab_notes_ordre = array_intersect ( array_filter( Clean::map('entier',$tab_notes_ordre) , 'positif' ) , array(1,2,3,4,5,6) );
  $nombre_codes_notation = count($tab_notes_actif);
  if( ( $nombre_codes_notation < 2 ) || ( count($tab_notes_ordre) != 6 ) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // On récupère les données, on vérifie leur présence, mais on ne revérifie pas toutes les conditions (valeurs distinctes ou croissantes, etc.).
  $TAB_NOTE = array();
  foreach($tab_notes_ordre as $key => $note_id)
  {
    $is_note_actif = in_array($note_id,$tab_notes_actif);
    $TAB_NOTE[$note_id]['ORDRE']   = $key+1;
    $TAB_NOTE[$note_id]['ACTIF']   = ($is_note_actif)                          ? 1                                                 : 0 ;
    $TAB_NOTE[$note_id]['VALEUR']  = (isset($_POST['note_valeur_' .$note_id])) ? Clean::entier(  $_POST['note_valeur_' .$note_id]) : NULL ;
    $TAB_NOTE[$note_id]['IMAGE']   = (isset($_POST['note_image_'  .$note_id])) ? Clean::txt_note($_POST['note_image_'  .$note_id]) : '' ;
    $TAB_NOTE[$note_id]['SIGLE']   = (isset($_POST['note_sigle_'  .$note_id])) ? Clean::txt_note($_POST['note_sigle_'  .$note_id]) : NULL ;
    $TAB_NOTE[$note_id]['LEGENDE'] = (isset($_POST['note_legende_'.$note_id])) ? Clean::txt_note($_POST['note_legende_'.$note_id]) : '' ;
    $TAB_NOTE[$note_id]['CLAVIER'] = (isset($_POST['note_clavier_'.$note_id])) ? Clean::entier(  $_POST['note_clavier_'.$note_id]) : NULL ;
    if($TAB_NOTE[$note_id]['ACTIF'])
    {
      if( is_null($TAB_NOTE[$note_id]['VALEUR']) || !$TAB_NOTE[$note_id]['IMAGE'] || is_null($TAB_NOTE[$note_id]['SIGLE']) || !$TAB_NOTE[$note_id]['LEGENDE'] || is_null($TAB_NOTE[$note_id]['CLAVIER']) )
      {
        Json::end( FALSE , 'Erreur avec les données transmises !' );
      }
      if( !preg_match("/^[0-9a-z_-]+$/i", $TAB_NOTE[$note_id]['IMAGE']) || !is_file(FileSystem::chemin_fichier_symbole($TAB_NOTE[$note_id]['IMAGE'])) )
      {
        Json::end( FALSE , 'Erreur avec les données transmises !' );
      }
    }
  }
  // Mettre à jour la base
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( array('nombre_codes_notation'=>$nombre_codes_notation) );
  foreach($TAB_NOTE as $note_id => $TAB)
  {
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametre_note( $note_id , $TAB['ACTIF'] , $TAB['ORDRE'] , $TAB['VALEUR'] , $TAB['IMAGE'] , $TAB['SIGLE'] , $TAB['LEGENDE'] , $TAB['CLAVIER'] );
    // On prépare pour la session ensuite
    unset($TAB_NOTE[$note_id]['ORDRE']);
  }
  // Mettre à jour la session
  $_SESSION['NOMBRE_CODES_NOTATION'] = $nombre_codes_notation;
  $_SESSION['NOTE'] = $TAB_NOTE;
  $_SESSION['NOTE_ACTIF'] = $tab_notes_actif;
  // Mettre à jour le css perso
  SessionUser::memoriser_couleurs();
  SessionUser::adapter_daltonisme();
  SessionUser::actualiser_style();
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// États d'acquisitions : nombre, seuils, couleur, sigle, légende, valeur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='save_acquis') && isset($_POST['acquis_actif']) && isset($_POST['acquis_ordre']) )
{
  // Normalement ce sont des tableaux qui sont transmis, mais au cas où...
  $tab_acquis_actif = is_array($_POST['acquis_actif']) ? $_POST['acquis_actif'] : explode(',',$_POST['acquis_actif']);
  $tab_acquis_ordre = is_array($_POST['acquis_ordre']) ? $_POST['acquis_ordre'] : explode(',',$_POST['acquis_ordre']);
  $tab_acquis_actif = array_intersect ( array_filter( Clean::map('entier',$tab_acquis_actif) , 'positif' ) , array(1,2,3,4,5,6) );
  $tab_acquis_ordre = array_intersect ( array_filter( Clean::map('entier',$tab_acquis_ordre) , 'positif' ) , array(1,2,3,4,5,6) );
  $nombre_etats_acquisition = count($tab_acquis_actif);
  if( ( $nombre_etats_acquisition < 2 ) || ( count($tab_acquis_ordre) != 6 ) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // On récupère les données, on vérifie leur présence, mais on ne revérifie pas toutes les conditions (valeurs distinctes ou croissantes, etc.).
  $TAB_ACQUIS = array();
  foreach($tab_acquis_ordre as $key => $acquis_id)
  {
    $is_acquis_actif = in_array($acquis_id,$tab_acquis_actif);
    $TAB_ACQUIS[$acquis_id]['ORDRE']     = $key+1;
    $TAB_ACQUIS[$acquis_id]['ACTIF']     = ($is_acquis_actif)                                  ? 1                                                            : 0 ;
    $TAB_ACQUIS[$acquis_id]['SEUIL_MIN'] = (isset($_POST['acquis_seuil_' .$acquis_id.'_min'])) ? Clean::entier(  $_POST['acquis_seuil_'  .$acquis_id.'_min']) : NULL ;
    $TAB_ACQUIS[$acquis_id]['SEUIL_MAX'] = (isset($_POST['acquis_seuil_' .$acquis_id.'_max'])) ? Clean::entier(  $_POST['acquis_seuil_'  .$acquis_id.'_max']) : NULL ;
    $TAB_ACQUIS[$acquis_id]['VALEUR']    = (isset($_POST['acquis_valeur_'.$acquis_id]))        ? Clean::entier(  $_POST['acquis_valeur_' .$acquis_id])        : NULL ;
    $TAB_ACQUIS[$acquis_id]['COULEUR']   = (isset($_POST['acquis_color_'  .$acquis_id]))       ? Clean::texte(   $_POST['acquis_color_'.$acquis_id])          : '' ;
    $TAB_ACQUIS[$acquis_id]['GRIS']      = ($is_acquis_actif) ? SessionUser::$tab_dalton_css[$nombre_etats_acquisition][array_search($acquis_id,$tab_acquis_actif)] : '';
    $TAB_ACQUIS[$acquis_id]['SIGLE']     = (isset($_POST['acquis_sigle_'  .$acquis_id]))       ? Clean::txt_note($_POST['acquis_sigle_'  .$acquis_id])        : NULL ;
    $TAB_ACQUIS[$acquis_id]['LEGENDE']   = (isset($_POST['acquis_legende_'.$acquis_id]))       ? Clean::txt_note($_POST['acquis_legende_'.$acquis_id])        : '' ;
    if($TAB_ACQUIS[$acquis_id]['ACTIF'])
    {
      if( is_null($TAB_ACQUIS[$acquis_id]['SEUIL_MIN']) || is_null($TAB_ACQUIS[$acquis_id]['SEUIL_MAX']) || is_null($TAB_ACQUIS[$acquis_id]['VALEUR']) || !$TAB_ACQUIS[$acquis_id]['COULEUR'] || is_null($TAB_ACQUIS[$acquis_id]['SIGLE']) || !$TAB_ACQUIS[$acquis_id]['LEGENDE'] )
      {
        Json::end( FALSE , 'Erreur avec les données transmises !' );
      }
      $longueur_couleur = mb_strlen($TAB_ACQUIS[$acquis_id]['COULEUR']);
      if( (!preg_match("/^\#[0-9a-f]{3,6}$/i", $TAB_ACQUIS[$acquis_id]['COULEUR'])) || ($longueur_couleur==5) || ($longueur_couleur==6) )
      {
        Json::end( FALSE , 'Erreur avec les données transmises !' );
      }
      // Passer si besoin d'un code hexadécimal à 3 caractères vers un code hexadécimal à 6 caractères.
      if($longueur_couleur==4)
      {
        $TAB_ACQUIS[$acquis_id]['COULEUR'] = '#'.$TAB_ACQUIS[$acquis_id]['COULEUR']{1}.$TAB_ACQUIS[$acquis_id]['COULEUR']{1}.$TAB_ACQUIS[$acquis_id]['COULEUR']{2}.$TAB_ACQUIS[$acquis_id]['COULEUR']{2}.$TAB_ACQUIS[$acquis_id]['COULEUR']{3}.$TAB_ACQUIS[$acquis_id]['COULEUR']{3};
      }
    }
  }
  // Mettre à jour la base
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( array('nombre_etats_acquisition'=>$nombre_etats_acquisition) );
  foreach($TAB_ACQUIS as $acquis_id => $TAB)
  {
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametre_acquis( $acquis_id , $TAB['ACTIF'] , $TAB['ORDRE'] , $TAB['SEUIL_MIN'] , $TAB['SEUIL_MAX'] , $TAB['VALEUR'] , $TAB['COULEUR'] , $TAB['SIGLE'] , $TAB['LEGENDE'] );
    // On prépare pour la session ensuite
    if(!$TAB['ACTIF'])
    {
      unset($TAB_ACQUIS[$acquis_id]);
    }
  }
  // Mettre à jour la session
  $_SESSION['NOMBRE_ETATS_ACQUISITION'] = $nombre_etats_acquisition;
  $_SESSION['ACQUIS'] = $TAB_ACQUIS;
  // Mettre à jour le css perso
  SessionUser::memoriser_couleurs();
  SessionUser::adapter_daltonisme();
  SessionUser::actualiser_style();
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un fichier image
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_symbole')
{
  // Récupération du fichier
  $fichier_nom_tmp = 'symbole_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom_tmp.'.<EXT>' /*fichier_nom*/ , array('gif','png','bmp','jpg','jpeg') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  $fichier_image = FileSystem::$file_saved_name;
  // vérifier la conformité du fichier image, récupérer les infos le concernant
  $tab_infos = @getimagesize(CHEMIN_DOSSIER_IMPORT.$fichier_image);
  if($tab_infos==FALSE)
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_image);
    Json::end( FALSE , 'Le fichier image ne semble pas valide !' );
  }
  list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
  // vérifier les dimensions
  if( $image_largeur != 20 )
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_image);
    Json::end( FALSE , 'Le fichier transmis a '.$image_largeur.' pixels de largeur au lieu de 20 !');
  }
  if( $image_hauteur != 10 )
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_image);
    Json::end( FALSE , 'Le fichier transmis a '.$image_hauteur.' pixels de largeur au lieu de 10 !');
  }
  // vérifier le type 
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_PNG=>'png' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_BMP=>'bmp' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  if(!isset($tab_extension_types[$image_type]))
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_image);
   Json::end( FALSE , 'Le fichier n\'est pas un fichier image (type '.$image_type.') !' );
  }
  $image_format = $tab_extension_types[$image_type];
  // récupérer l'image selon son format et la convertir en gif
  switch($image_format)
  {
    case 'gif' :
      $image_h = imagecreatefromgif(CHEMIN_DOSSIER_IMPORT.$fichier_image);
      break;
    case 'png' :
      $image_h = imagecreatefrompng(CHEMIN_DOSSIER_IMPORT.$fichier_image);
      break;
    case 'jpeg' :
      $image_h = imagecreatefromjpeg(CHEMIN_DOSSIER_IMPORT.$fichier_image);
      break;
    case 'bmp' :
      $image_h = Image::imagecreatefrombmp(CHEMIN_DOSSIER_IMPORT.$fichier_image);
      break;
  }
  imageinterlace($image_h, FALSE); // supprimer l'entrelacement éventuel afin d'éviter l'erreur ultérieure "Fatal error: Uncaught Exception: FPDF error: Interlacing not supported:..."
  imagegif( $image_h , CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_h.gif' );
  // On génère aussi la version tournée à 90° (ne gère pas la transparence...)
  $image_v = Image::imagerotateEmulation($image_h);
  imagegif( $image_v , CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_v.gif' );
  imagedestroy($image_h);
  imagedestroy($image_v);
  // stocker les images dans la base
  $image_id = DB_STRUCTURE_IMAGE::DB_ajouter_image_note( base64_encode(file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_h.gif')) , base64_encode(file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_v.gif')) );
  // stocker les images sur le disque
  $image_nom = 'upload_'.$image_id;
  FileSystem::deplacer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_h.gif' , FileSystem::chemin_fichier_symbole($image_nom,'h','perso') );
  FileSystem::deplacer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom_tmp.'_v.gif' , FileSystem::chemin_fichier_symbole($image_nom,'v','perso') );
  // Générer la balise html et afficher le retour
  Json::end( TRUE , '<span class="note_liste"><a href="#" id="p_'.$image_nom.'"><img alt="'.$image_nom.'" src="'.Html::note_src_couleur($image_nom,'h','perso').'" /></a><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></span>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un fichier image
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='delete_symbole') && $image_id )
{
  // dans la base
  DB_STRUCTURE_IMAGE::DB_supprimer_image_note( $image_id );
  DB_STRUCTURE_PARAMETRE::DB_remplacer_parametre_note_image( 'upload_'.$image_id );
  // sur le disque
  FileSystem::supprimer_fichier( FileSystem::chemin_fichier_symbole('upload_'.$image_id,'h','perso') );
  FileSystem::supprimer_fichier( FileSystem::chemin_fichier_symbole('upload_'.$image_id,'v','perso') );
  // c'est tout :)
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
