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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Voir_referentiel')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action      = (isset($_POST['f_action']))    ? Clean::texte($_POST['f_action'])    : '';
$matiere_id  = (isset($_POST['matiere_id']))  ? Clean::entier($_POST['matiere_id']) : 0;
$matiere_ref = (isset($_POST['matiere_ref'])) ? Clean::texte($_POST['matiere_ref']) : '';
$niveau_id   = (isset($_POST['niveau_id']))   ? Clean::entier($_POST['niveau_id'])  : 0;
$item_id     = (isset($_POST['item_id']))     ? Clean::entier($_POST['item_id'])    : 0;
$item_nom    = (isset($_POST['item_nom']))    ? Clean::texte($_POST['item_nom'])    : '';
$item_lien   = (isset($_POST['item_lien']))   ? Clean::texte($_POST['item_lien'])   : '';
$objet       = (isset($_POST['page_mode']))   ? Clean::texte($_POST['page_mode'])   : '';
$ressources  = (isset($_POST['ressources']))  ? Clean::texte($_POST['ressources'])  : '';
$findme      = (isset($_POST['findme']))      ? Clean::texte($_POST['findme'])      : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le référentiel d'une matière et d'un niveau
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Voir_referentiel') && $matiere_id && $niveau_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , FALSE /*item_comm*/ );
  Json::end( TRUE , HtmlArborescence::afficher_matiere_from_SQL( $DB_TAB , NULL /*DB_TAB_socle2016*/ , TRUE /*dynamique*/ , TRUE /*reference*/ , FALSE /*aff_coef*/ , FALSE /*aff_cart*/ , FALSE /*aff_socle*/ , 'image' /*aff_lien*/ , FALSE /*aff_comm*/ , FALSE /*aff_input*/ , 'n3' /*aff_id_li*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer une adresse associée à un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_lien') && $item_id )
{
  DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_lien_ressources($item_id,$item_lien);
  // Si le lien est vide, effacer l'éventuelle page de liens associée enregistrée sur le serveur communautaire.
  if(!$item_lien)
  {
    ServeurCommunautaire::fabriquer_liens_ressources( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $item_id , '' , 'page_delete' , '' );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Élaborer ou éditer sur le serveur communautaire une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Charger_ressources') && $item_id )
{
  Json::end( TRUE , ServeurCommunautaire::afficher_liens_ressources( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $item_id , $item_lien , 'html' ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer sur le serveur communautaire le contenu d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_ressources') && $item_id && $item_nom && in_array($objet,array('page_create','page_update')) && $ressources )
{
  $tab_elements = array();
  $tab_ressources = explode('}¤{',$ressources);
  foreach($tab_ressources as $ressource)
  {
    if(strpos($ressource,']¤['))
    {
      list($lien_nom,$lien_url) = explode(']¤[',$ressource);
      $tab_elements[] = array( $lien_nom => $lien_url );
    }
    else
    {
      $tab_elements[] = $ressource;
    }
  }
  Json::end( TRUE , ServeurCommunautaire::fabriquer_liens_ressources( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $item_id , $item_nom , $objet , serialize($tab_elements) ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher sur le serveur communautaire à partir de mots clefs des liens existants de ressources pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Rechercher_liens_ressources') && $item_id && $findme )
{
  Json::end( TRUE , ServeurCommunautaire::rechercher_liens_ressources( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $item_id , $findme ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher sur le serveur communautaire des documents ressources existants uploadés par l'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Rechercher_documents')
{
  Json::end( TRUE , ServeurCommunautaire::rechercher_documents( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader une ressource sur le serveur communautaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*
Pour uploader une ressource directement sur le serveur communautaire, le fichier "professeur_referentiel_edition.php" devrait l'appeller dans une iframe (le bouton d'upload du formulaire devant se trouver sur ce serveur). Mais :
- une fois l'upload effectué, pour faire remonter l'info au serveur où est installé SACoche, se pose un souci de cross-domain javascript.
- il faudrait reproduire côté serveur communautaire tout un environnement (js & css) semblable à celui du serveur d'installation de SACoche
Finalement, j'ai opté pour le plus simple même si ce n'est pas le plus économe : uploader sur le serveur SACoche puis transférer vers le serveur communautaire avec cURL::get_contents().
Ca va qu'une limite de 500Ko est imposée...
*/

if( ($action=='Uploader_document') && $matiere_ref )
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , NULL /*fichier_nom*/ , NULL /*tab_extensions_autorisees*/ , FileSystem::$tab_extensions_interdites , 500 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  $fichier_nom = Clean::fichier(FileSystem::$file_upload_name);
  // Transfert du fichier
  $reponse = ServeurCommunautaire::uploader_ressource( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_ref , $fichier_nom , file_get_contents(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name) );
  // Suppression de l'enregistrement temporaire
  FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  // Retour
  $is_ok = (strpos($reponse,'http')===0) ? TRUE : FALSE ;
  Json::end( $is_ok , $reponse );
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
