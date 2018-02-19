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

// Peut être appelé depuis plusieurs pages d'un administrateur

$action     = (isset($_POST['f_action']))     ? $_POST['f_action']     : '';
$listing_id = (isset($_POST['f_listing_id'])) ? $_POST['f_listing_id'] : '';

$tab_user_id = array_filter( Clean::map('entier', explode(',',$listing_id) ) , 'positif' );
$nb_user = count($tab_user_id);

if( !$nb_user )
{
  Json::end( FALSE , 'Aucun compte récupéré !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer des comptes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='retirer')
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_users_statut($tab_user_id,FALSE);
  Json::end( TRUE , implode(',',$tab_user_id) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réintégrer des comptes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='reintegrer')
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_users_statut($tab_user_id,TRUE);
  Json::end( TRUE , implode(',',$tab_user_id) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer des comptes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='supprimer')
{
  // Récupérer le profil des utilisateurs indiqués, vérifier qu'ils sont déjà sortis et qu'on y a pas glissé l'id d'un administrateur
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( implode(',',$tab_user_id) , 'user_id,user_nom,user_prenom,user_profil_sigle,user_sortie_date' , '' /*avec_info*/ );
  $tab_user_id = array();
  $notification_contenu = '';
  $notification_intro = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'];
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($DB_ROW['user_sortie_date']<=TODAY_MYSQL) && ($DB_ROW['user_profil_sigle']!='ADM') )
    {
      DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $DB_ROW['user_id'] , $DB_ROW['user_profil_sigle'] );
      $tab_user_id[] = $DB_ROW['user_id'];
      // Log de l'action
      SACocheLog::ajouter('Suppression de l\'utilisateur '.$DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_profil_sigle'].' '.$DB_ROW['user_id'].').');
      $notification_contenu .= $notification_intro.' a supprimé l\'utilisateur '.$DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_profil_sigle'].' '.$DB_ROW['user_id'].').'."\r\n";
    }
  }
  // Notifications (rendues visibles ultérieurement)
  if($notification_contenu)
  {
    DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  }
  // Retour
  if(count($tab_user_id))
  {
    Json::end( TRUE , implode(',',$tab_user_id) );
  }
  else
  {
    Json::end( FALSE , 'Aucun compte coché n\'est supprimable ! Il faut d\'abord les retirer pour leur affecter une date de sortie.' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
