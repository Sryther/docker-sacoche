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

$action               = (isset($_POST['f_action']))               ? Clean::texte($_POST['f_action'])                 : '';
$denomination         = (isset($_POST['f_denomination']))         ? Clean::texte($_POST['f_denomination'])           : '';
$uai                  = (isset($_POST['f_uai']))                  ? Clean::uai($_POST['f_uai'])                      : '';
$adresse_site         = (isset($_POST['f_adresse_site']))         ? Clean::url($_POST['f_adresse_site'])             : '';
$logo                 = (isset($_POST['f_logo']))                 ? Clean::texte($_POST['f_logo'])                   : '';
$cnil_numero          = (isset($_POST['f_cnil_numero']))          ? Clean::entier($_POST['f_cnil_numero'])           : 0;
$cnil_date_engagement = (isset($_POST['f_cnil_date_engagement'])) ? Clean::date_fr($_POST['f_cnil_date_engagement']) : '';
$cnil_date_recepisse  = (isset($_POST['f_cnil_date_recepisse']))  ? Clean::date_fr($_POST['f_cnil_date_recepisse'])  : '';
$nom                  = (isset($_POST['f_nom']))                  ? Clean::nom($_POST['f_nom'])                      : '';
$prenom               = (isset($_POST['f_prenom']))               ? Clean::prenom($_POST['f_prenom'])                : '';
$courriel             = (isset($_POST['f_courriel']))             ? Clean::courriel($_POST['f_courriel'])            : '';

$tab_ext_images = array('bmp','gif','jpg','jpeg','png');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Contenu du select avec la liste des logos disponibles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='select_logo')
{
  $tab_files = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_LOGO);
  $options_logo = '';
  foreach($tab_files as $file)
  {
    $extension = Clean::lower(pathinfo($file,PATHINFO_EXTENSION));
    if(in_array($extension,$tab_ext_images))
    {
      $selected = ($file==HEBERGEUR_LOGO) ? ' selected' : '' ;
      $options_logo .= '<option value="'.html($file).'"'.$selected.'>'.html($file).'</option>';
    }
  }
  $options_logo = ($options_logo) ? '<option value="">&nbsp;</option>'.$options_logo : '<option value="" disabled>Aucun fichier image trouvé !</option>';
  Json::end( TRUE , $options_logo );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Contenu du ul avec la liste des logos disponibles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='listing_logos')
{
  $tab_files = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_LOGO);
  $li_logos = '';
  foreach($tab_files as $file)
  {
    $extension = Clean::lower(pathinfo($file,PATHINFO_EXTENSION));
    if(in_array($extension,$tab_ext_images))
    {
      $li_logos .= '<li>'.html($file).' <img alt="'.html($file).'" src="'.URL_DIR_LOGO.html($file).'" /><q class="supprimer" title="Supprimer cette image du serveur (aucune confirmation ne sera demandée)."></q></li>';
    }
  }
  $li_logos = ($li_logos) ? $li_logos : '<li>Aucun fichier image trouvé !</li>';
  Json::end( TRUE , $li_logos );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_logo')
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_LOGO /*fichier_chemin*/ , NULL /*fichier_nom*/ , $tab_ext_images /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // vérifier la conformité du fichier image, récupérer les infos le concernant
  $tab_infos = @getimagesize(CHEMIN_DOSSIER_LOGO.FileSystem::$file_saved_name);
  if($tab_infos==FALSE)
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_LOGO.FileSystem::$file_saved_name);
    Json::end( FALSE , 'Le fichier image ne semble pas valide !' );
  }
  list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' , IMAGETYPE_BMP=>'bmp' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    FileSystem::supprimer_fichier(CHEMIN_DOSSIER_LOGO.FileSystem::$file_saved_name);
    Json::end( FALSE , 'Le fichier transmis n\'est pas un fichier image !' );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='delete_logo') && $logo )
{
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_LOGO.$logo , TRUE /*verif_exist*/ );
  // Si on supprime l'image actuellement utilisée, alors la retirer du fichier
  if($logo==HEBERGEUR_LOGO)
  {
    $result = FileSystem::fabriquer_fichier_hebergeur_info( array('HEBERGEUR_LOGO'=>'') );
    if($result!==TRUE)
    {
      Json::end( FALSE , $result );
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer le nouveau fichier de paramètres
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='enregistrer') && ( !$uai || Outil::tester_UAI($uai) ) && $denomination && $nom && $prenom && $courriel )
{
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
    if(!$is_domaine_valide)
    {
      Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
    }
  }
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array(
    'HEBERGEUR_DENOMINATION'   => $denomination,
    'HEBERGEUR_UAI'            => $uai,
    'HEBERGEUR_ADRESSE_SITE'   => $adresse_site,
    'HEBERGEUR_LOGO'           => $logo,
    'CNIL_NUMERO'              => $cnil_numero,
    'CNIL_DATE_ENGAGEMENT'     => $cnil_date_engagement,
    'CNIL_DATE_RECEPISSE'      => $cnil_date_recepisse,
    'WEBMESTRE_NOM'            => $nom,
    'WEBMESTRE_PRENOM'         => $prenom,
    'WEBMESTRE_COURRIEL'       => $courriel,
  ) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  if(HEBERGEUR_INSTALLATION=='mono-structure')
  {
    // Personnaliser certains paramètres de la structure (pour une installation de type multi-structures, ça se fait à la page de gestion des établissements)
    $tab_parametres = array();
    $tab_parametres['webmestre_uai']          = $uai;
    $tab_parametres['webmestre_denomination'] = $denomination;
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  }
  // On modifie aussi la session
  $_SESSION['USER_NOM']    = $nom ;
  $_SESSION['USER_PRENOM'] = $prenom ;
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
