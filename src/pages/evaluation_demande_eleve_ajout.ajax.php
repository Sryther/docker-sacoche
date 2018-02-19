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

$action       = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])          : '';
$matiere_id   = (isset($_POST['f_matiere_id'])) ? Clean::entier($_POST['f_matiere_id'])     : 0;
$item_id      = (isset($_POST['f_item_id']))    ? Clean::entier($_POST['f_item_id'])        : 0;
$prof_id      = (isset($_POST['f_prof_id']))    ? Clean::entier($_POST['f_prof_id'])        : -1;
$debut_date   = (isset($_POST['f_debut_date'])) ? Clean::date_mysql($_POST['f_debut_date']) : '0000-00-00';
$score        = (isset($_POST['f_score']))      ? Clean::entier($_POST['f_score'])          : -2; // normalement entier entre 0 et 100 ou -1 si non évalué
$message      = (isset($_POST['f_message']))    ? Clean::texte($_POST['f_message'])         : '' ;
$document_nom = (isset($_POST['f_doc_nom']))    ? Clean::texte($_POST['f_doc_nom'])         : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les profs associés à l'élève et à une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='lister_profs') && $matiere_id )
{
  $DB_TAB = DB_STRUCTURE_DEMANDE::DB_recuperer_professeurs_eleve_matiere( $_SESSION['USER_ID'] , $_SESSION['ELEVE_CLASSE_ID'] , $matiere_id );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun de vos professeurs n\'étant rattaché à cette matière, personne ne pourrait traiter votre demande.' );
  }
  else
  {
    $options = (count($DB_TAB)==1) ? '' : '<option value="0">Tous les enseignants concernés</option>' ;
    foreach($DB_TAB as $DB_ROW)
    {
      $options .= '<option value="'.$DB_ROW['user_id'].'">'.html(To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre'])).'</option>';
    }
    Json::end( TRUE , $options );
  }
}


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un document pour joindre à une demande
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='uploader_document')
{
  // Récupération du fichier
  $fichier_nom = 'demande_'.$_SESSION['BASE'].'_user_'.$_SESSION['USER_ID'].'_'.$_SERVER['REQUEST_TIME'].'.<EXT>'; // pas besoin de le rendre inaccessible -> FileSystem::generer_fin_nom_fichier__date_et_alea() inutilement lourd
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , NULL /*tab_extensions_autorisees*/ , FileSystem::$tab_extensions_interdites , FICHIER_TAILLE_MAX /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Retour
  Json::end( TRUE , array( 'nom' => FileSystem::$file_saved_name , 'url' => URL_DIR_IMPORT.FileSystem::$file_saved_name ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Un élève confirme l'ajout d'une demande d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='confirmer_ajout') && $matiere_id && $item_id && ($prof_id!==-1) && ($score!==-2) )
{

  // Vérifier que cet item n'est pas déjà en attente d'évaluation pour cet élève
  if( DB_STRUCTURE_DEMANDE::DB_tester_demande_existante( $_SESSION['USER_ID'] , $matiere_id , $item_id ) )
  {
    Json::end( FALSE , 'Cette demande est déjà enregistrée !' );
  }

  // Vérifier que les demandes sont autorisées pour cette matière
  $nb_demandes_autorisees = DB_STRUCTURE_DEMANDE::DB_recuperer_demandes_autorisees_matiere($matiere_id);
  if(!$nb_demandes_autorisees)
  {
    Json::end( FALSE , 'Vous ne pouvez pas formuler de demandes pour les items cette matière.' );
  }

  // Vérifier qu'il reste des demandes disponibles pour l'élève et la matière concernés
  $nb_demandes_formulees = DB_STRUCTURE_DEMANDE::DB_compter_demandes_formulees_eleve_matiere( $_SESSION['USER_ID'] , $matiere_id );
  $nb_demandes_possibles = max( 0 , $nb_demandes_autorisees - $nb_demandes_formulees ) ;
  if(!$nb_demandes_possibles)
  {
    $reponse = ($nb_demandes_formulees>1)
              ? 'Vous avez déjà formulé les '.$nb_demandes_formulees.' demandes autorisées pour cette matière. <a href="./index.php?page=evaluation&amp;section=demande_eleve">Veuillez en supprimer avant d\'en ajouter d\'autres !</a>'
              : 'Vous avez déjà formulé la demande autorisée pour cette matière.<br /><a href="./index.php?page=evaluation&amp;section=demande_eleve">Veuillez la supprimer avant d\'en demander une autre !</a>' ;
    Json::end( FALSE , $reponse );
  }

  // Vérifier que cet item n'est pas interdit à la sollitation ; récupérer au passage sa référence et son nom
  $DB_ROW = DB_STRUCTURE_DEMANDE::DB_recuperer_item_infos($item_id);
  if($DB_ROW['item_cart']==0)
  {
    Json::end( FALSE , 'La demande de cet item est interdite !' );
  }

  // Indiquer, si renseigné, le document déjà uploadé temporairement
  $demande_doc = '';
  if($document_nom)
  {
    if(!is_file(CHEMIN_DOSSIER_IMPORT.$document_nom))
    {
      Json::end( FALSE , 'Le document joint est introuvable !' );
    }
    $fichier_nom = str_replace( 'demande_'.$_SESSION['BASE'].'_' , 'demande_' , $document_nom );
    if(!FileSystem::deplacer_fichier( CHEMIN_DOSSIER_IMPORT.$document_nom , CHEMIN_DOSSIER_DEVOIR.$_SESSION['BASE'].DS.$fichier_nom ))
    {
      Json::end( FALSE , 'Impossible de déplacer le document joint !' );
    }
    $demande_doc = URL_DIR_DEVOIR.$_SESSION['BASE'].'/'.$fichier_nom;
  }

  // Enregistrement de la demande
  $debut_date = ($debut_date!='0000-00-00') ? $debut_date : NULL ;
  $score = ($score!=-1) ? $score : NULL ;
  $demande_id = DB_STRUCTURE_DEMANDE::DB_ajouter_demande( $_SESSION['USER_ID'] , $matiere_id , $item_id , $prof_id , $debut_date , $score , 'eleve' /*statut*/ , $message , $demande_doc );

  // Ajout aux flux RSS des profs concernés
  $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['ref_perso'] : $DB_ROW['ref_auto'] ;
  $titre = 'Demande ajoutée par '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE);
  $texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' ajoute la demande '.$DB_ROW['matiere_ref'].'.'.$item_ref.' "'.$DB_ROW['item_nom'].'".'."\r\n";
  $texte.= ($demande_doc) ? 'Document joint : '.$demande_doc."\r\n" : 'Pas de document joint.'."\r\n" ;
  $texte.= ($message)     ? 'Commentaire :'."\r\n".$message."\r\n" : 'Pas de commentaire saisi.'."\r\n" ;
  $guid  = 'demande_'.$demande_id.'_add';
  if($prof_id)
  {
    RSS::modifier_fichier_prof($prof_id,$titre,$texte,$guid);
  }
  else
  {
    // On récupère les profs...
    $tab_prof_id = array();
    $DB_TAB = DB_STRUCTURE_DEMANDE::DB_recuperer_professeurs_eleve_matiere( $_SESSION['USER_ID'] , $_SESSION['ELEVE_CLASSE_ID'] , $matiere_id );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_prof_id[] = $DB_ROW['user_id'];
        RSS::modifier_fichier_prof($DB_ROW['user_id'],$titre,$texte,$guid);
      }
    }
  }

  // Notifications (rendues visibles ultérieurement) ; on récupère des données conçues pour le flux RSS ($texte , $tab_prof_id)
  $abonnement_ref = 'demande_evaluation_eleve';
  $listing_profs = ($prof_id) ? $prof_id : ( (!empty($tab_prof_id)) ? implode(',',$tab_prof_id) : NULL ) ;
  if($listing_profs)
  {
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_profs );
    if($listing_abonnes)
    {
      $notification_contenu = $texte;
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , TRUE /*sep*/ );
      }
    }
  }

  // Affichage du retour
  $nb_demandes_formulees++;
  $nb_demandes_possibles--;
  $s = ($nb_demandes_possibles>1) ? 's' : '' ;
  $demandes_restantes = ($nb_demandes_possibles==0) ? 'Vous ne pouvez plus formuler d\'autres demandes pour cette matière.' : 'Vous pouvez encore formuler '.$nb_demandes_possibles.' demande'.$s.' pour cette matière.' ;
  Json::end( TRUE , '<label class="valide">Votre demande a été ajoutée.</label><br />'.$demandes_restantes );
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
