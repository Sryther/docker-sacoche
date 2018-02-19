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

$action      = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])    : '' ;
$logo        = (isset($_POST['f_logo']))        ? Clean::texte($_POST['f_logo'])      : '' ; // inutilisé
$adresse_web = (isset($_POST['f_adresse_web'])) ? Clean::url($_POST['f_adresse_web']) : '' ;
$message     = (isset($_POST['f_message']))     ? Clean::texte($_POST['f_message'])   : '' ;

$tab_ext_images = array('bmp','gif','jpg','jpeg','png');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_logo')
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , NULL /*fichier_nom*/ , $tab_ext_images /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // vérifier la conformité du fichier image, récupérer les infos le concernant
  $tab_infos = @getimagesize(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  if($tab_infos==FALSE)
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    Json::end( FALSE , 'Le fichier image ne semble pas valide !' );
  }
  list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' , IMAGETYPE_BMP=>'bmp' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    Json::end( FALSE , 'Le fichier transmis n\'est pas un fichier image !');
  }
  // vérifier les dimensions
  if( ($image_largeur>400) || ($image_hauteur>200) )
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    Json::end( FALSE , 'Le fichier transmis a des dimensions trop grandes ('.$image_largeur.' sur '.$image_hauteur.', maximum autorisé 400 sur 200).');
  }
  // On ne met pas encore à jour le logo : on place pour l'instant l'adresse de l'image en session (comme marqueur) en attendant confirmation.
  $_SESSION['tmp']['partenaire_logo_new_filename'] = FileSystem::$file_saved_name;
  $_SESSION['tmp']['partenaire_logo_new_file_ext'] = $tab_extension_types[$image_type];
  // Retour
  Json::end( TRUE , URL_DIR_IMPORT.FileSystem::$file_saved_name );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='delete_logo')
{
  // On ne supprime pas encore le logo : on place pour l'instant l'adresse de l'image vide en session (comme marqueur) en attendant confirmation.
  $_SESSION['tmp']['partenaire_logo_new_filename'] = '';
  // Retour
  Json::end( TRUE , URL_DIR_IMG.'auto.gif' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer le nouveau fichier de paramètres
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='enregistrer')
{
  // Pour le logo, ... 
  if(!isset($_SESSION['tmp']['partenaire_logo_new_filename']))
  {
    // soit on conserve le précédent (éventuellement rien),
  }
  elseif($_SESSION['tmp']['partenaire_logo_new_filename']=='')
  {
    // soit on le supprime,
    FileSystem::supprimer_fichier( CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['tmp']['partenaire_logo_actuel_filename'] , TRUE /*verif_exist*/ );
    $_SESSION['tmp']['partenaire_logo_actuel_filename'] = '';
  }
  elseif(is_file(CHEMIN_DOSSIER_IMPORT.$_SESSION['tmp']['partenaire_logo_new_filename']))
  {
    // soit on prend le nouveau, auquel cas il faut aussi le déplacer dans CHEMIN_DOSSIER_PARTENARIAT, et éventuellement supprimer l'ancien
    if($_SESSION['tmp']['partenaire_logo_actuel_filename'])
    {
      FileSystem::supprimer_fichier( CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['tmp']['partenaire_logo_actuel_filename'] , TRUE /*verif_exist*/ );
    }
    $_SESSION['tmp']['partenaire_logo_actuel_filename'] = 'logo_'.$_SESSION['USER_ID'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$_SESSION['tmp']['partenaire_logo_new_file_ext'];
    copy( CHEMIN_DOSSIER_IMPORT.$_SESSION['tmp']['partenaire_logo_new_filename'] , CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['tmp']['partenaire_logo_actuel_filename'] );
  }
  unset( $_SESSION['tmp']['partenaire_logo_new_filename'] , $_SESSION['tmp']['partenaire_logo_new_file_ext'] );
  // On fabrique le fichier avec les infos et on l'enregistre
  FileSystem::fabriquer_fichier_partenaire_message( $_SESSION['USER_ID'] , $_SESSION['tmp']['partenaire_logo_actuel_filename'] , $adresse_web , $message );
  // Retour
  $partenaire_logo_url = ($_SESSION['tmp']['partenaire_logo_actuel_filename']) ? URL_DIR_PARTENARIAT.$_SESSION['tmp']['partenaire_logo_actuel_filename'] : URL_DIR_IMG.'auto.gif' ;
  $partenaire_lien_ouvrant = ($adresse_web) ? '<a href="'.html($adresse_web).'" target="_blank" rel="noopener noreferrer">' : '' ;
  $partenaire_lien_fermant = ($adresse_web) ? '</a>' : '' ;
  $partenaire_logo    = '<span id="partenaire_logo"><img src="'.html($partenaire_logo_url).'" /></span>';
  $partenaire_message = '<span id="partenaire_message">'.nl2br(html($message)).'</span>';
  Json::end( TRUE , $partenaire_lien_ouvrant.$partenaire_logo.$partenaire_message.$partenaire_lien_fermant.'<hr id="partenaire_hr" />' );
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
