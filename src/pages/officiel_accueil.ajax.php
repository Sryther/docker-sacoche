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
// Pas de test d'établissement de démo ici mais dans les sous-fichiers, sauf dans 2 cas précis
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && in_array($_POST['f_action'],array('signaler_faute','corriger_faute')) ) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir    : affichage des données d'un élève | enregistrement/suppression d'une appréciation ou d'une note | recalculer une note
// Examiner  : recherche des saisies manquantes (notes et appréciations)
// Consulter : affichage des données d'un élève (HTML)
// Imprimer  : affichage de la liste des élèves | étape d'impression PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_types = array
(
  'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
  'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
);

if( in_array( $section , array('officiel_saisir','officiel_examiner','officiel_consulter','officiel_imprimer','officiel_importer') ) )
{
  if( ($section=='officiel_consulter') && ($action=='imprimer') )
  {
    // Il s'agit d'un test d'impression d'un bilan non encore clos (on vérifiera quand même par la suite que les conditions sont respectées (état du bilan, droit de l'utilisateur)
    $section = 'officiel_imprimer';
    $_POST['f_objet'] = 'imprimer';
    $is_test_impression = TRUE;
  }
  require(CHEMIN_DOSSIER_INCLUDE.'fonction_bulletin.php');
  require(CHEMIN_DOSSIER_INCLUDE.'code_'.$section.'.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Signaler une faute
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='signaler_faute') || ($action=='corriger_faute') )
{
  $destinataire_id = (isset($_POST['f_destinataire_id'])) ? Clean::entier($_POST['f_destinataire_id']) : 0;
  $message_contenu = (isset($_POST['f_message_contenu'])) ? Clean::texte($_POST['f_message_contenu'])  : '' ;
  if( !$destinataire_id || !$message_contenu )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Notification (qui est envoyée de suite)
  $abonnement_ref = 'bilan_officiel_appreciation';
  $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_avec_informations( $abonnement_ref , $destinataire_id );
  $destinataires_nb = count($DB_TAB);
  if(!$destinataires_nb)
  {
    // Normalement impossible, l'abonnement des personnels à ce type de de notification étant obligatoire
    Json::end( FALSE , 'Destinataire non trouvé ! !' );
  }
  $notification_debut = ($action=='signaler_faute') ? 'Signalement effectué par ' : 'Correction apportée par ' ;
  $notification_contenu = $notification_debut.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']).' :'."\r\n\r\n".$message_contenu."\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    // 1 seul passage en fait
    $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
    DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_visible( $DB_ROW['user_id'] , $abonnement_ref , $notification_statut , $notification_contenu );
    if($notification_statut=='envoyée')
    {
      $destinataire = $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>';
      $notification_contenu .= Sesamail::texte_pied_courriel( array('no_reply','notif_individuelle','signature') , $DB_ROW['user_email'] );
      $courriel_bilan = Sesamail::mail( $destinataire , 'Notification - Erreur appréciation bilan officiel' , $notification_contenu , NULL );
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un archivage des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_actions = array
(
  'imprimer_donnees_eleves_prof'            => 'Mes appréciations pour chaque élève et le groupe classe',
  'imprimer_donnees_eleves_collegues'       => 'Appréciations des collègues pour chaque élève',
  'imprimer_donnees_classe_collegues'       => 'Appréciations des collègues sur le groupe classe',
  'imprimer_donnees_eleves_syntheses'       => 'Appréciations de synthèse générale pour chaque élève',
  'imprimer_donnees_eleves_positionnements' => 'Tableau des positionnements pour chaque élève',
  'imprimer_donnees_eleves_recapitulatif'   => 'Récapitulatif annuel des positionnements et appréciations par élève',
);

if( isset($tab_actions[$action]) )
{
  require(CHEMIN_DOSSIER_INCLUDE.'code_officiel_archiver.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
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
