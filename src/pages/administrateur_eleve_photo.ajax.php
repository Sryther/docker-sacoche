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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='afficher')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action      = (isset($_POST['f_action']))      ? $_POST['f_action']                      : '';
$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::lettres($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])    : 0;
$user_id     = (isset($_POST['f_user_id']))     ? Clean::entier($_POST['f_user_id'])      : 0;
$masque      = (isset($_POST['f_masque']))      ? Clean::texte($_POST['f_masque'])        : '';

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

$tab_ext_images = array('gif','jpg','jpeg','png');

$dossier_temp = CHEMIN_DOSSIER_IMPORT.$_SESSION['BASE'].DS;

/**
 * Traiter un fichier photo : le vérifier, redimensionner l'image, enregistrer en BDD son contenu, effacer les fichiers correspondants
 *
 * @param int      $user_id
 * @param string   $fichier_chemin
 * @return string|array   un message d'erreur ou un tableau avec [ contenu_base_64 , largeur , hauteur ]
 */
function photo_file_to_base($user_id,$fichier_chemin)
{
  // vérifier la conformité du fichier image, récupérer les infos le concernant
  $tab_infos = @getimagesize($fichier_chemin);
  if($tab_infos==FALSE)
  {
    FileSystem::supprimer_fichier($fichier_chemin);
    return'Le fichier tansmis n\'est pas une image valide !';
  }
  list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    FileSystem::supprimer_fichier($fichier_chemin);
    return'Le fichier transmis n\'est pas un fichier image !';
  }
  // vérifier les dimensions
  if( ($image_largeur>2048) || ($image_hauteur>2048) )
  {
    FileSystem::supprimer_fichier($fichier_chemin);
    return'Le fichier transmis a des dimensions trop grandes ('.$image_largeur.' sur '.$image_hauteur.') !';
  }
  if( ($image_largeur==0) && ($image_hauteur==0) )
  {
    FileSystem::supprimer_fichier($fichier_chemin);
    return'Le fichier transmis a des dimensions indéterminables !';
  }
  // C'est bon, on continue
  $fichier_chemin_vignette = $fichier_chemin.'.mini.jpeg';
  $image_format = $tab_extension_types[$image_type];
  $coef = PHOTO_DIMENSION_MAXI / max($image_largeur,$image_hauteur);
  $largeur_new = round($image_largeur*$coef);
  $hauteur_new = round($image_hauteur*$coef);
  $image_new    = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($largeur_new,$hauteur_new) : imagecreate($largeur_new,$hauteur_new) ;
  $couleur_fond = imagecolorallocate($image_new,255,255,255); // Le premier appel à imagecolorallocate() remplit la couleur de fond si imagecreate().
  $couleur_fill = imagefill($image_new, 0, 0, $couleur_fond); // Si imagecreatetruecolor(), l'image est noire et il faut la remplir explicitement.
  $image_depart = call_user_func( 'imagecreatefrom'.$image_format, $fichier_chemin );
  imageinterlace($image_depart, FALSE); // supprimer l'entrelacement éventuel afin d'éviter l'erreur ultérieure "Fatal error: Uncaught Exception: FPDF error: Interlacing not supported:..."
  imagecopyresampled($image_new , $image_depart , 0 /* dest_x */ , 0 /* dest_y */ , 0 /* dep_x */ , 0 /* dep_y */ , $largeur_new , $hauteur_new , $image_largeur , $image_hauteur );
  imagedestroy($image_depart);
  imagejpeg($image_new , $fichier_chemin_vignette , JPEG_QUALITY );
  imagedestroy($image_new);
  // stocker l'image dans la base
  $image_contenu_base_64 = base64_encode(file_get_contents($fichier_chemin_vignette)) ;
  DB_STRUCTURE_IMAGE::DB_modifier_image( $user_id , 'photo' , $image_contenu_base_64 , 'jpeg' , $largeur_new , $hauteur_new );
  // effacer les fichiers images
  FileSystem::supprimer_fichier($fichier_chemin);
  FileSystem::supprimer_fichier($fichier_chemin_vignette);
  // retour des informations
  return array( $image_contenu_base_64 , $largeur_new , $hauteur_new );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les élèves et leurs photos si existantes (et en fonction le bouton "ajouter" ou "supprimer")
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='afficher')
{
  if( (!$groupe_id) || (!isset($tab_types[$groupe_type])) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // On récupère les élèves
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ ) ;
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun élève trouvé dans ce regroupement !' );
  }
  $tab_vignettes = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_vignettes[$DB_ROW['user_id']] = array(
      'identite' => html($DB_ROW['user_nom']).'<br />'.html($DB_ROW['user_prenom']),
      'image'    => '<q id="q_'.$DB_ROW['user_id'].'" class="ajouter" title="Ajouter une photo."></q><img width="1" height="1" src="./_img/auto.gif" alt="" />',
    );
  }
  // On récupère les photos
  $listing_user_id = implode(',',array_keys($tab_vignettes));
  $DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images( $listing_user_id , 'photo' );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_vignettes[$DB_ROW['user_id']]['image'] = '<img width="'.$DB_ROW['image_largeur'].'" height="'.$DB_ROW['image_hauteur'].'" src="data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$DB_ROW['image_contenu'].'" alt="" /><q class="supprimer" title="Supprimer cette photo (aucune confirmation ne sera demandée)."></q>';
  }
  // On affiche tout ça
  foreach($tab_vignettes as $user_id => $tab)
  {
    Json::add_str('<div id="div_'.$user_id.'" class="photo"><div>'.$tab['image'].'</div>'.$tab['identite'].'</div>');
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un zip de photos
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='envoyer_zip') ) //  $masque non encore testé car non récupéré si fichier envoyé trop volumineux
{
  // Récupération du fichier
  $fichier_nom = 'photos_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('zip') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // vérification du masque
  if(!$masque)
  {
    Json::end( FALSE , 'Masque des noms de fichiers non transmis !' );
  }
  $masque_filename  = '#\[(sconet_id|sconet_num|reference|nom|prenom|login|ent_id)\]#';
  $masque_extension = '#\.(gif|jpg|jpeg|png)$#';
  if( (!preg_match($masque_filename,$masque)) || (!preg_match($masque_extension,$masque)) )
  {
    Json::end( FALSE , 'Masque des noms de fichiers non conforme !' );
  }
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp);
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( CHEMIN_DOSSIER_IMPORT.$fichier_nom , $dossier_temp , FALSE /*use_ZipArchive*/ , NULL /*tab_extensions_autorisees*/ , FileSystem::$tab_extensions_interdites );
  FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_nom);
  if($code_erreur)
  {
    FileSystem::supprimer_dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , 'Erreur d\'extraction du contenu ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  // Récupérer la liste des élèves et fabriquer le nom de fichier attendu correspondant à chacun
  $tab_bad = array( '[sconet_id]' , '[sconet_num]' , '[reference]' , '[nom]' , '[prenom]' , '[login]' , '[ent_id]' );
  $champs = 'user_id, user_id_ent, user_sconet_id, user_sconet_elenoet, user_reference, user_nom, user_prenom, user_login' ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , 'all' /*groupe_type*/ , 0 /*groupe_id*/ , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_bon = array( $DB_ROW['user_sconet_id'] , $DB_ROW['user_sconet_elenoet'] , Clean::fichier($DB_ROW['user_reference']) , Clean::fichier($DB_ROW['user_nom']) , Clean::fichier($DB_ROW['user_prenom']) , Clean::fichier($DB_ROW['user_login']) , Clean::fichier($DB_ROW['user_id_ent']) );
      $tab_fichier_masque[$DB_ROW['user_id']] = Clean::fichier(str_replace( $tab_bad , $tab_bon , $masque ));
    }
  }
  // Pour l'affichage du retour
  $thead = '<tr><td colspan="2">Import d\'un fichier de photos zippées le '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = array( 'r' => '' , 'v' => '' );
  // Traiter les fichier un à un
  $tab_fichier = FileSystem::lister_contenu_dossier($dossier_temp);
  foreach($tab_fichier as $fichier_nom)
  {
    $tab_user_id = array_keys( $tab_fichier_masque , $fichier_nom );
    $nb_user_find = count($tab_user_id);
    if($nb_user_find == 0)
    {
      $tbody['r'] .= '<tr><td class="r">'.html($fichier_nom).'</td><td>Pas de correspondance trouvée.</td></tr>';
    }
    elseif($nb_user_find > 1)
    {
      $tbody['r'] .= '<tr><td class="r">'.html($fichier_nom).'</td><td>Plusieurs correspondances trouvées.</td></tr>';
    }
    else
    {
      list($inutile,$user_id) = each($tab_user_id);
      // traiter l'image : la vérifier, la redimensionner, l'enregistrer en BDD, et effacer les fichiers temporaires
      $result = photo_file_to_base($user_id,$dossier_temp.$fichier_nom);
      if(is_string($result))
      {
        $tbody['r'] .= '<tr><td class="r">'.html($fichier_nom).'</td><td>'.$result.'</td></tr>';
      }
      else
      {
        $tbody['v'] .= '<tr><td class="v">'.html($fichier_nom).'</td><td>Image prise en compte.</td></tr>';
      }
    }
  }
  // Supprimer le dossier temporaire
  FileSystem::supprimer_dossier($dossier_temp);
  // Enregistrement du rapport
  $fichier_nom = 'rapport_zip_photos_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody['r'].$tbody['v'] );
  // retour
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader une photo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='envoyer_photo') && $user_id )
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , NULL /*fichier_nom*/ , $tab_ext_images /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 500 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // traiter l'image : la vérifier, la redimensionner, l'enregistrer en BDD, et effacer les fichiers temporaires
  $result = photo_file_to_base($user_id,CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  if(is_string($result))
  {
    Json::end( FALSE , $result );
  }
  // retour
  list( $image_contenu_base_64 , $largeur_new , $hauteur_new) = $result;
  Json::add_tab( array(
    'user_id'    => $user_id ,
    'img_width'  => $largeur_new ,
    'img_height' => $hauteur_new ,
    'img_src'    => 'data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$image_contenu_base_64 ,
  ) );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une photo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer_photo') && $user_id )
{
  DB_STRUCTURE_IMAGE::DB_supprimer_image( $user_id , 'photo' );
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
