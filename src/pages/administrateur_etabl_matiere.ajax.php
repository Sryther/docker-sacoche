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

$action     = (isset($_POST['f_action']))    ? Clean::texte($_POST['f_action'])    : '';
$famille_id = (isset($_POST['f_famille']))   ? Clean::entier($_POST['f_famille'])  : 0 ;
$motclef    = (isset($_POST['f_motclef']))   ? Clean::texte($_POST['f_motclef'])   : '' ;
$id_avant   = (isset($_POST['f_id_avant']))  ? Clean::entier($_POST['f_id_avant']) : 0;
$id_apres   = (isset($_POST['f_id_apres']))  ? Clean::entier($_POST['f_id_apres']) : 0;
$id         = (isset($_POST['f_id']))        ? Clean::entier($_POST['f_id'])       : 0;
$ref        = (isset($_POST['f_ref']))       ? Clean::ref($_POST['f_ref'])         : '';
$nom        = (isset($_POST['f_nom']))       ? Clean::texte($_POST['f_nom'])       : '';
$nom_avant  = (isset($_POST['f_nom_avant'])) ? Clean::texte($_POST['f_nom_avant']) : '';
$nom_apres  = (isset($_POST['f_nom_apres'])) ? Clean::texte($_POST['f_nom_apres']) : '';

$tab_id = (isset($_POST['tab_id']))   ? Clean::map('entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
sort($tab_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les matières partagées d'une famille donnée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='recherche_matiere_famille') && $famille_id )
{
  $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_famille($famille_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $class = ($DB_ROW['matiere_active']) ? 'ajouter_non' : 'ajouter' ;
    $title = ($DB_ROW['matiere_active']) ? 'Matière déjà choisie.' : 'Ajouter cette matière.' ;
    Json::add_str('<li>'.html($DB_ROW['matiere_nom'].' ('.$DB_ROW['matiere_ref'].')').'<q id="add_'.$DB_ROW['matiere_id'].'" class="'.$class.'" title="'.$title.'"></q></li>');
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les matières partagées à partir d'une recherche par mot clef
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='recherche_matiere_motclef') && $motclef )
{
  $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matiere_motclef($motclef);
  if(!empty($DB_TAB))
  {
    // Le "score" retourné par MySQL via MATCH() AGAINST() ne reflète pas la pertinence d'une chaîne complète.
    // Du coup on repasse derrière avec levenshtein() de PHP.
    $tab_li = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $class = ($DB_ROW['matiere_active']) ? 'ajouter_non' : 'ajouter' ;
      $title = ($DB_ROW['matiere_active']) ? 'Matière déjà choisie.' : 'Ajouter cette matière.' ;
      $pourcent_commun = Outil::pourcentage_commun( $motclef , $DB_ROW['matiere_nom'] );
      $score_retenu = round( max( $DB_ROW['score'] , $pourcent_commun) );
      $tab_li['<li>['.$score_retenu.'%] <i>'.html($DB_ROW['matiere_famille_nom']).'</i> || '.html($DB_ROW['matiere_nom'].' ('.$DB_ROW['matiere_ref'].')').'<q id="add_'.$DB_ROW['matiere_id'].'" class="'.$class.'" title="'.$title.'"></q></li>'] = $score_retenu ;
    }
    arsort($tab_li);
    Json::end( TRUE , implode('',array_keys($tab_li) ) );
  }
  else
  {
    Json::end( TRUE , '<li class="i">Recherche infructueuse...</li>' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un choix de matière partagée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_partage') && $id )
{
  DB_STRUCTURE_MATIERE::DB_modifier_matiere_partagee($id,1);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle matière spécifique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_perso') && $ref && $nom )
{
  // Vérifier que la référence de la matière est disponible
  if( DB_STRUCTURE_MATIERE::DB_tester_matiere_reference($ref) )
  {
    Json::end( FALSE , 'Référence déjà utilisée !' );
  }
  // Insérer l'enregistrement
  $id = DB_STRUCTURE_MATIERE::DB_ajouter_matiere_specifique( $ref , $nom );
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id , 'ref'=>html($ref) , 'nom'=>html($nom) ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une matière spécifique existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $ref && $nom && ($id>ID_MATIERE_PARTAGEE_MAX) )
{
  // Vérifier que la référence de la matière est disponible
  if( DB_STRUCTURE_MATIERE::DB_tester_matiere_reference($ref,$id) )
  {
    Json::end( FALSE , 'Référence déjà utilisée !' );
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_MATIERE::DB_modifier_matiere_specifique($id,$ref,$nom);
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id , 'ref'=>html($ref) , 'nom'=>html($nom) ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer une matière partagée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom && ($id<=ID_MATIERE_PARTAGEE_MAX) )
{
  DB_STRUCTURE_MATIERE::DB_modifier_matiere_partagee($id,0);
  // Log de l'action
  SACocheLog::ajouter('Retrait de la matière partagée "'.$nom.'" (n°'.$id.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a retiré la matière partagée "'.$nom.'" (n°'.$id.').'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une matière spécifique existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom && ($id>ID_MATIERE_PARTAGEE_MAX) )
{
  DB_STRUCTURE_MATIERE::DB_supprimer_matiere_specifique($id);
  // Log de l'action
  SACocheLog::ajouter('Suppression de la matière spécifique "'.$nom.'" (n°'.$id.') et donc des référentiels associés.');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé la matière spécifique "'.$nom.'" (n°'.$id.') et donc les référentiels associés.'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déplacer les référentiels d'une matière vers une autre
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='deplacer_referentiels') && $id_avant && $id_apres && ($id_avant!=$id_apres) && $nom_avant && $nom_apres )
{
  // Déplacement après vérification que c'est possible (matière de destination vierge de données)
  // 
  $is_ok = DB_STRUCTURE_MATIERE::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
  if(!$is_ok)
  {
    Json::end( FALSE , 'La nouvelle matière contient déjà des données !' );
  }
  // Retirer l'ancienne matière partagée || Supprimer l'ancienne matière spécifique existante
  if($id_avant>ID_MATIERE_PARTAGEE_MAX)
  {
    DB_STRUCTURE_MATIERE::DB_supprimer_matiere_specifique($id_avant);
    $log_fin = 'avec suppression de la matière spécifique "'.$nom_avant.'"';
  }
  else
  {
    DB_STRUCTURE_MATIERE::DB_modifier_matiere_partagee($id_avant,0);
    $log_fin = 'avec retrait de la matière partagée "'.$nom_avant.'"';
  }
  // Log de l'action
  SACocheLog::ajouter('Déplacement des référentiels de "'.$nom_avant.'" ('.$id_avant.') vers "'.$nom_apres.'" ('.$id_apres.'), '.$log_fin.'.');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a déplacé des référentiels de "'.$nom_avant.'" ('.$id_avant.') vers "'.$nom_apres.'" ('.$id_apres.'), '.$log_fin.'.'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );
?>
