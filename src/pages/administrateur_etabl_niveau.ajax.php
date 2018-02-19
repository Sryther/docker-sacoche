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

$action     = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])    : '';
$famille_id = (isset($_POST['f_famille']))  ? Clean::entier($_POST['f_famille'])  : 0 ;
$id         = (isset($_POST['f_id']))       ? Clean::entier($_POST['f_id'])       : 0;
$ref        = (isset($_POST['f_ref']))      ? Clean::ref($_POST['f_ref'])         : '';
$nom        = (isset($_POST['f_nom']))      ? Clean::texte($_POST['f_nom'])       : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les niveaux d'une famille donnée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='recherche_niveau_famille') && $famille_id )
{
  $DB_TAB = DB_STRUCTURE_NIVEAU::DB_lister_niveaux_famille($famille_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $class = ($DB_ROW['niveau_actif']) ? 'ajouter_non' : 'ajouter' ;
    $title = ($DB_ROW['niveau_actif']) ? 'Niveau déjà choisi.' : 'Ajouter ce niveau.' ;
    Json::add_str('<li>'.html($DB_ROW['niveau_nom'].' ('.$DB_ROW['niveau_ref'].')').'<q id="add_'.$DB_ROW['niveau_id'].'" class="'.$class.'" title="'.$title.'"></q></li>');
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un choix de niveau partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_partage') && $id )
{
  DB_STRUCTURE_NIVEAU::DB_modifier_niveau_partage($id,1);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau niveau spécifique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_perso') && $ref && $nom )
{
  // Vérifier que la référence de la matière est disponible
  if( DB_STRUCTURE_NIVEAU::DB_tester_niveau_reference($ref) )
  {
    Json::end( FALSE , 'Référence déjà utilisée !' );
  }
  // Insérer l'enregistrement
  $id = DB_STRUCTURE_NIVEAU::DB_ajouter_niveau_specifique($ref,$nom);
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id , 'ref'=>html($ref) , 'nom'=>html($nom) ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un niveau spécifique existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $ref && $nom && ($id>ID_NIVEAU_PARTAGE_MAX) )
{
  // Vérifier que la référence du niveau est disponible
  if( DB_STRUCTURE_NIVEAU::DB_tester_niveau_reference($ref,$id) )
  {
    Json::end( FALSE , 'Référence déjà utilisée !' );
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_NIVEAU::DB_modifier_niveau_specifique($id,$ref,$nom);
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id , 'ref'=>html($ref) , 'nom'=>html($nom) ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un niveau partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom && ($id<=ID_NIVEAU_PARTAGE_MAX) )
{
  DB_STRUCTURE_NIVEAU::DB_modifier_niveau_partage($id,0);
  // Log de l'action
  SACocheLog::ajouter('Retrait du niveau partagé "'.$nom.'" (n°'.$id.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a retiré le niveau partagé "'.$nom.'" (n°'.$id.').'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un niveau spécifique existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom && ($id>ID_NIVEAU_PARTAGE_MAX) )
{
  DB_STRUCTURE_NIVEAU::DB_supprimer_niveau_specifique($id);
  // Log de l'action
  SACocheLog::ajouter('Suppression du niveau spécifique "'.$nom.'" (n°'.$id.') et donc des référentiels associés.');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé le niveau spécifique "'.$nom.'" (n°'.$id.') et donc les référentiels associés.'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE ,  array( 'id'=>$id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );
?>
