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

$action                      = (isset($_POST['f_action']))                      ? $_POST['f_action']                                    : '';
$geo1                        = (isset($_POST['f_geo1']))                        ? Clean::entier($_POST['f_geo1'])                        : 0;
$geo2                        = (isset($_POST['f_geo2']))                        ? Clean::entier($_POST['f_geo2'])                        : 0;
$geo3                        = (isset($_POST['f_geo3']))                        ? Clean::entier($_POST['f_geo3'])                        : 0;
$uai                         = (isset($_POST['f_uai']))                         ? Clean::uai($_POST['f_uai'])                            : '';

$contact_nom                 = (isset($_POST['f_contact_nom']))                 ? Clean::nom($_POST['f_contact_nom'])                    : '';
$contact_prenom              = (isset($_POST['f_contact_prenom']))              ? Clean::prenom($_POST['f_contact_prenom'])              : '';
$contact_courriel            = (isset($_POST['f_contact_courriel']))            ? Clean::courriel($_POST['f_contact_courriel'])          : '';

$sesamath_id                 = (isset($_POST['f_sesamath_id']))                 ? Clean::entier($_POST['f_sesamath_id'])                 : 0;
$sesamath_uai                = (isset($_POST['f_sesamath_uai']))                ? Clean::uai($_POST['f_sesamath_uai'])                   : '';
$sesamath_type_nom           = (isset($_POST['f_sesamath_type_nom']))           ? Clean::texte($_POST['f_sesamath_type_nom'])            : '';
$sesamath_key                = (isset($_POST['f_sesamath_key']))                ? Clean::texte($_POST['f_sesamath_key'])                 : '';

$etablissement_denomination  = (isset($_POST['f_etablissement_denomination']))  ? Clean::texte($_POST['f_etablissement_denomination'])   : '';
$etablissement_adresse1      = (isset($_POST['f_etablissement_adresse1']))      ? Clean::texte($_POST['f_etablissement_adresse1'])       : '';
$etablissement_adresse2      = (isset($_POST['f_etablissement_adresse2']))      ? Clean::texte($_POST['f_etablissement_adresse2'])       : '';
$etablissement_adresse3      = (isset($_POST['f_etablissement_adresse3']))      ? Clean::texte($_POST['f_etablissement_adresse3'])       : '';
$etablissement_telephone     = (isset($_POST['f_etablissement_telephone']))     ? Clean::texte($_POST['f_etablissement_telephone'])      : '';
$etablissement_fax           = (isset($_POST['f_etablissement_fax']))           ? Clean::texte($_POST['f_etablissement_fax'])            : '';
$etablissement_courriel      = (isset($_POST['f_etablissement_courriel']))      ? Clean::courriel($_POST['f_etablissement_courriel'])    : '';
$etablissement_url           = (isset($_POST['f_etablissement_url']))           ? Clean::url($_POST['f_etablissement_url'])              : '';
$etablissement_langue        = (isset($_POST['f_etablissement_langue']))        ? Clean::texte($_POST['f_etablissement_langue'])         : '';

$mois_bascule_annee_scolaire = (isset($_POST['f_mois_bascule_annee_scolaire'])) ? Clean::entier($_POST['f_mois_bascule_annee_scolaire']) : 0;

$ip_variable                 = (isset($_POST['f_ip_variable']))                 ? Clean::entier($_POST['f_ip_variable'])                 : NULL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo1
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Afficher_form_geo1')
{
  Json::end( TRUE , ServeurCommunautaire::Sesamath_afficher_formulaire_geo1() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo2
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_form_geo2') && ($geo1>0) )
{
  Json::end( TRUE , ServeurCommunautaire::Sesamath_afficher_formulaire_geo2($geo1) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo3
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_form_geo3') && ($geo1>0) && ($geo2>0) )
{
  Json::end( TRUE , ServeurCommunautaire::Sesamath_afficher_formulaire_geo3($geo1,$geo2) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le résultat de la recherche de structure, soit à partir du n°UAI soit à partir du code de commune
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_structures') && ( ($geo3>0) || Outil::tester_UAI($uai) ) )
{
  $retour = ($geo3) ? ServeurCommunautaire::Sesamath_lister_structures_by_commune($geo3) : ServeurCommunautaire::Sesamath_recuperer_structure_by_UAI($uai) ;
  Json::end( TRUE , $retour );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les informations form_sesamath
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $sesamath_id && $sesamath_type_nom && $sesamath_key )
{
  if( $sesamath_uai && !Outil::tester_UAI($sesamath_uai) )
  {
    Json::end( FALSE , 'Erreur avec le numéro UAI transmis !' );
  }
  $result = ServeurCommunautaire::Sesamath_enregistrer_structure($sesamath_id,$sesamath_key);
  if($result!=='ok')
  {
    Json::end( FALSE , $result );
  }
  // Si on arrive là, alors tout s'est bien passé.
  $tab_parametres = array();
  $tab_parametres['sesamath_id']       = $sesamath_id;
  $tab_parametres['sesamath_uai']      = $sesamath_uai;
  $tab_parametres['sesamath_type_nom'] = $sesamath_type_nom;
  $tab_parametres['sesamath_key']      = $sesamath_key;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['SESAMATH_ID']       = $sesamath_id ;
  $_SESSION['SESAMATH_UAI']      = $sesamath_uai ;
  $_SESSION['SESAMATH_TYPE_NOM'] = $sesamath_type_nom ;
  $_SESSION['SESAMATH_KEY']      = $sesamath_key ;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les informations form_contact
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (HEBERGEUR_INSTALLATION=='multi-structures') && ( $contact_nom || $contact_prenom || $contact_courriel ) )
{
  // Vérifier les variables récupérées en focntion de ce qui est autorisé et donc requis.
  if( (CONTACT_MODIFICATION_USER!='non') && ( !$contact_nom || !$contact_prenom ) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  if(CONTACT_MODIFICATION_MAIL!='non') // oui ou domaine restreint
  {
    if(!$contact_courriel)
    {
      Json::end( FALSE , 'Erreur avec les données transmises !' );
    }
    if( (CONTACT_MODIFICATION_MAIL!='oui') && strpos($contact_courriel,CONTACT_MODIFICATION_MAIL)===FALSE )
    {
      Json::end( FALSE , 'Erreur avec le domaine qui est restreint à "'.CONTACT_MODIFICATION_MAIL.'" par le webmestre.' );
    }
    // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
    list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($contact_courriel);
    if(!$is_domaine_valide)
    {
      Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
    }
  }
  // On met à jour dans la base du webmestre, sans écraser l'existant.
  DBextra::charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  $DB_ROW = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_contact_infos($_SESSION['BASE']);
  $contact_nom      = (CONTACT_MODIFICATION_USER!='non') ? $contact_nom      : $DB_ROW['structure_contact_nom'] ;
  $contact_prenom   = (CONTACT_MODIFICATION_USER!='non') ? $contact_prenom   : $DB_ROW['structure_contact_prenom'] ;
  $contact_courriel = (CONTACT_MODIFICATION_MAIL!='non') ? $contact_courriel : $DB_ROW['structure_contact_courriel'] ;
  DB_WEBMESTRE_ADMINISTRATEUR::DB_modifier_contact_infos($_SESSION['BASE'],$contact_nom,$contact_prenom,$contact_courriel);
  // Si on arrive là, alors tout s'est bien passé.
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les informations form_etablissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $etablissement_denomination )
{
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if( ($etablissement_courriel) && (HEBERGEUR_INSTALLATION=='multi-structures') )
  {
    list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($etablissement_courriel);
    if(!$is_domaine_valide)
    {
      Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
    }
  }
  $tab_parametres = array();
  $tab_parametres['etablissement_denomination'] = $etablissement_denomination;
  $tab_parametres['etablissement_adresse1']     = $etablissement_adresse1;
  $tab_parametres['etablissement_adresse2']     = $etablissement_adresse2;
  $tab_parametres['etablissement_adresse3']     = $etablissement_adresse3;
  $tab_parametres['etablissement_telephone']    = $etablissement_telephone;
  $tab_parametres['etablissement_fax']          = $etablissement_fax;
  $tab_parametres['etablissement_courriel']     = $etablissement_courriel;
  $tab_parametres['etablissement_url']          = $etablissement_url;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['ETABLISSEMENT']['DENOMINATION'] = $etablissement_denomination;
  $_SESSION['ETABLISSEMENT']['ADRESSE1']     = $etablissement_adresse1;
  $_SESSION['ETABLISSEMENT']['ADRESSE2']     = $etablissement_adresse2;
  $_SESSION['ETABLISSEMENT']['ADRESSE3']     = $etablissement_adresse3;
  $_SESSION['ETABLISSEMENT']['TELEPHONE']    = $etablissement_telephone;
  $_SESSION['ETABLISSEMENT']['FAX']          = $etablissement_fax;
  $_SESSION['ETABLISSEMENT']['COURRIEL']     = $etablissement_courriel;
  $_SESSION['ETABLISSEMENT']['URL']          = $etablissement_url;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_logo (upload d'un fichier image)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_logo')
{
  // Récupération du fichier
  $fichier_nom = 'logo_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.<EXT>';
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
  DB_STRUCTURE_IMAGE::DB_modifier_image( 0 /*user_id*/ , 'logo' , base64_encode(file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_image)) , $image_format , $image_largeur , $image_hauteur );
  // Générer la balise html et afficher le retour
  list($width,$height) = Image::dimensions_affichage( $image_largeur , $image_hauteur , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
  Json::end( TRUE , '<li><img src="'.URL_DIR_IMPORT.$fichier_image.'" alt="Logo établissement" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un fichier image (logo de l'établissement)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='delete_logo')
{
  DB_STRUCTURE_IMAGE::DB_supprimer_image( 0 /*user_id*/ , 'logo' );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour mois_bascule_annee_scolaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $mois_bascule_annee_scolaire )
{
  $tab_parametres = array();
  $tab_parametres['mois_bascule_annee_scolaire'] = $mois_bascule_annee_scolaire;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'] = $mois_bascule_annee_scolaire;
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour la langue par défaut
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $etablissement_langue )
{
  // Vérifications
  if(!is_dir(LOCALE_DIR.DS.$etablissement_langue))
  {
    Json::end( FALSE , 'Dossier de langue "'.$etablissement_langue.'" non trouvé !' );
  }
  // C'est ok...
  $tab_parametres = array();
  $tab_parametres['etablissement_langue'] = $etablissement_langue;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['ETABLISSEMENT']['LANGUE'] = $etablissement_langue;
  // sans oublier le menu
  Lang::setlocale( LC_MESSAGES, Lang::get_locale_used() );
  SessionUser::memoriser_menu();
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour la vérification de l'IP
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !is_null($ip_variable) )
{
  $tab_parametres = array();
  $tab_parametres['etablissement_ip_variable'] = $ip_variable;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['ETABLISSEMENT']['IP_VARIABLE'] = $ip_variable;
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
