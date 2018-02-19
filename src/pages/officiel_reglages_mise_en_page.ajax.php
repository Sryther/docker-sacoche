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

$action                           = (isset($_POST['f_action']))                           ? $_POST['f_action']                                          : '';

$tab_coordonnees                  = (isset($_POST['f_coordonnees']))                      ? $_POST['f_coordonnees']                                     : array();
$infos_responsables               = (isset($_POST['f_infos_responsables']))               ? Clean::texte($_POST['f_infos_responsables'])                : '';
$horizontal_gauche                = (isset($_POST['f_horizontal_gauche']))                ? Clean::entier($_POST['f_horizontal_gauche'])                : 0;
$horizontal_milieu                = (isset($_POST['f_horizontal_milieu']))                ? Clean::entier($_POST['f_horizontal_milieu'])                : 0;
$horizontal_droite                = (isset($_POST['f_horizontal_droite']))                ? Clean::entier($_POST['f_horizontal_droite'])                : 0;
$vertical_haut                    = (isset($_POST['f_vertical_haut']))                    ? Clean::entier($_POST['f_vertical_haut'])                    : 0;
$vertical_milieu                  = (isset($_POST['f_vertical_milieu']))                  ? Clean::entier($_POST['f_vertical_milieu'])                  : 0;
$vertical_bas                     = (isset($_POST['f_vertical_bas']))                     ? Clean::entier($_POST['f_vertical_bas'])                     : 0;
$nombre_exemplaires               = (isset($_POST['f_nombre_exemplaires']))               ? Clean::texte($_POST['f_nombre_exemplaires'])                : '';
$marge_gauche                     = (isset($_POST['f_marge_gauche']))                     ? Clean::entier($_POST['f_marge_gauche'])                     : 0;
$marge_droite                     = (isset($_POST['f_marge_droite']))                     ? Clean::entier($_POST['f_marge_droite'])                     : 0;
$marge_haut                       = (isset($_POST['f_marge_haut']))                       ? Clean::entier($_POST['f_marge_haut'])                       : 0;
$marge_bas                        = (isset($_POST['f_marge_bas']))                        ? Clean::entier($_POST['f_marge_bas'])                        : 0;
$archive_ajout_message_copie      = (isset($_POST['f_archive_ajout_message_copie']))      ? Clean::entier($_POST['f_archive_ajout_message_copie'])      : 0;
$archive_retrait_tampon_signature = (isset($_POST['f_archive_retrait_tampon_signature'])) ? Clean::entier($_POST['f_archive_retrait_tampon_signature']) : 0;
$tampon_signature                 = (isset($_POST['f_tampon_signature']))                 ? Clean::texte($_POST['f_tampon_signature'])                  : '';
$user_id                          = (isset($_POST['f_user_id']))                          ? Clean::entier($_POST['f_user_id'])                          : -1;
$user_texte                       = (isset($_POST['f_user_texte']))                       ? Clean::texte($_POST['f_user_texte'])                        : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_mise_en_page, partie "coordonnees"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='coordonnees')
{
  $tab_parametres = array();
  $tab_parametres['officiel_infos_etablissement'] = implode(',',$tab_coordonnees);
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] = implode(',',$tab_coordonnees) ;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_mise_en_page, partie "responsables"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='responsables') && $infos_responsables && $nombre_exemplaires )
{
  $tab_parametres = array();
  $tab_parametres['officiel_infos_responsables'] = $infos_responsables;
  $tab_parametres['officiel_nombre_exemplaires'] = $nombre_exemplaires;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['OFFICIEL']['INFOS_RESPONSABLES'] = $infos_responsables ;
  $_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES'] = $nombre_exemplaires ;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_mise_en_page, partie "positionnement"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='positionnement') && $infos_responsables && $horizontal_gauche && $horizontal_milieu && $horizontal_droite && $vertical_haut && $vertical_milieu && $vertical_bas && $marge_gauche && $marge_droite && $marge_haut && $marge_bas )
{
  $tab_parametres = array();
  $tab_parametres['officiel_marge_gauche']                     = $marge_gauche;
  $tab_parametres['officiel_marge_droite']                     = $marge_droite;
  $tab_parametres['officiel_marge_haut']                       = $marge_haut;
  $tab_parametres['officiel_marge_bas']                        = $marge_bas;
  if($infos_responsables=='oui_force')
  {
    $tab_parametres['enveloppe_horizontal_gauche'] = $horizontal_gauche;
    $tab_parametres['enveloppe_horizontal_milieu'] = $horizontal_milieu;
    $tab_parametres['enveloppe_horizontal_droite'] = $horizontal_droite;
    $tab_parametres['enveloppe_vertical_haut']     = $vertical_haut;
    $tab_parametres['enveloppe_vertical_milieu']   = $vertical_milieu;
    $tab_parametres['enveloppe_vertical_bas']      = $vertical_bas;
  }
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['OFFICIEL']['MARGE_GAUCHE']                     = $marge_gauche ;
  $_SESSION['OFFICIEL']['MARGE_DROITE']                     = $marge_droite ;
  $_SESSION['OFFICIEL']['MARGE_HAUT']                       = $marge_haut ;
  $_SESSION['OFFICIEL']['MARGE_BAS']                        = $marge_bas ;
  if($infos_responsables=='oui_force')
  {
    $_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] = $horizontal_gauche ;
    $_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] = $horizontal_milieu ;
    $_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] = $horizontal_droite ;
    $_SESSION['ENVELOPPE']['VERTICAL_HAUT']     = $vertical_haut ;
    $_SESSION['ENVELOPPE']['VERTICAL_MILIEU']   = $vertical_milieu ;
    $_SESSION['ENVELOPPE']['VERTICAL_BAS']      = $vertical_bas ;
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_mise_en_page, partie "signature"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='signature') && $tampon_signature )
{
  $tab_parametres = array();
  $tab_parametres['officiel_tampon_signature'] = $tampon_signature;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['OFFICIEL']['TAMPON_SIGNATURE'] = $tampon_signature ;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_tampon (upload d'un fichier image)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='upload_signature') && ($user_id>=0) && ($user_texte!='') )
{
  // Récupération du fichier
  $fichier_nom = 'signature_'.$_SESSION['BASE'].'_'.$user_id.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.<EXT>';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('gif','jpg','jpeg','png') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
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
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_image);
   Json::end( FALSE , 'Le fichier n\'est pas un fichier image (type '.$image_type.') !' );
  }
  $image_format = $tab_extension_types[$image_type];
  // supprimer l'entrelacement éventuel afin d'éviter l'erreur ultérieure "Fatal error: Uncaught Exception: FPDF error: Interlacing not supported:..."
  $image = call_user_func( 'imagecreatefrom'.$image_format , CHEMIN_DOSSIER_IMPORT.$fichier_image );
  imageinterlace($image, FALSE);
  call_user_func( 'image'.$image_format , $image , CHEMIN_DOSSIER_IMPORT.$fichier_image );
  imagedestroy($image);
  // stocker l'image dans la base
  DB_STRUCTURE_IMAGE::DB_modifier_image( $user_id , 'signature' , base64_encode(file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_image)) , $image_format , $image_largeur , $image_hauteur );
  // Générer la balise html et afficher le retour
  list($width,$height) = Image::dimensions_affichage( $image_largeur , $image_hauteur , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
  $user_texte = ($user_id) ? 'Signature '.$user_texte : $user_texte ;
  Json::end( TRUE , '<li id="sgn_'.$user_id.'">'.html($user_texte).' : <img src="'.URL_DIR_IMPORT.$fichier_image.'" alt="'.html($user_texte).'" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un fichier image (tampon de l'établissement ou signature)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='delete_signature') && ($user_id>=0) )
{
  DB_STRUCTURE_IMAGE::DB_supprimer_image( $user_id , 'signature' );
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
