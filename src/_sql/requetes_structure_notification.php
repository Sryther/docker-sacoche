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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes concernent des actions en lien avec les notifications.

class DB_STRUCTURE_NOTIFICATION extends DB
{

/**
 * Lister les destinataires de notifications ; infos complètes pour un envoi immédiat
 *
 * @param string   $abonnement_ref
 * @param string   $listing_id   facultatif : liste d'id utilisateurs soit dans laquelle piocher soit à éviter
 * @param bool     $oui_ou_non   facultatif : si le paramètre précédent est renseigné, alors TRUE pour piocher dedans ou FALSE pour piocher ailleurs
 * @return array
 */
public static function DB_lister_destinataires_avec_informations( $abonnement_ref , $listing_id=NULL , $oui_ou_non=TRUE )
{
  $not = ($oui_ou_non) ? '' : 'NOT ' ;
  $where_user_id = ($listing_id) ? 'AND user_id '.$not.'IN('.$listing_id.') ' : '' ;
  $DB_SQL = 'SELECT user_profil_type, user_id, user_nom, user_prenom, user_email, jointure_mode ';
  $DB_SQL.= 'FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE abonnement_ref=:abonnement_ref AND user_sortie_date>NOW() '.$where_user_id;
  $DB_VAR = array( ':abonnement_ref' => $abonnement_ref );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les destinataires de notifications ; seulement les id pour un envoi différé
 *
 * @param string   $abonnement_ref
 * @param string   $listing_id   facultatif : liste d'id utilisateurs soit dans laquelle piocher soit à éviter
 * @param bool     $oui_ou_non   facultatif : si le paramètre précédent est renseigné, alors TRUE pour piocher dedans ou FALSE pour piocher ailleurs
 * @return string
 */
public static function DB_lister_destinataires_listing_id( $abonnement_ref , $listing_id=NULL , $oui_ou_non=TRUE )
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // Go
  $not = ($oui_ou_non) ? '' : 'NOT ' ;
  $where_user_id = ($listing_id) ? 'AND user_id '.$not.'IN('.$listing_id.') ' : '' ;
  $DB_SQL = 'SELECT CONVERT( GROUP_CONCAT(user_id SEPARATOR ",") , CHAR) AS identifiants ';
  $DB_SQL.= 'FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE abonnement_ref=:abonnement_ref AND user_sortie_date>NOW() '.$where_user_id;
  $DB_VAR = array( ':abonnement_ref' => $abonnement_ref );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les parents d'élèves donnés ; seulement les id pour un envoi différé
 *
 * @param string   $listing_eleves_id   liste des élèves
 * @return string
 */
public static function DB_lister_parents_listing_id( $listing_eleves_id )
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // Go
  $DB_SQL = 'SELECT CONVERT( GROUP_CONCAT(DISTINCT parent_id SEPARATOR ",") , CHAR) AS identifiants ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.parent_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE eleve_id IN('.$listing_eleves_id.') AND user_sortie_date>NOW() ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Dans la liste des abonnés, distinguer, les élèves, les parents, et les associations
 *
 * @param string   $listing_abonnes_id   liste des abonnés
 * @param string   $listing_eleves_id   liste des élèves
 * @param string   $listing_parents_id   liste des parents
 * @return array
 */
public static function DB_lister_detail_abonnes_envois( $listing_abonnes_id , $listing_eleves_id , $listing_parents_id )
{
  $tab_abonnes_id = explode(',',$listing_abonnes_id);
  $tab_eleves_id  = explode(',',$listing_eleves_id);
  $tab_parents_id = explode(',',$listing_parents_id);
  // le tableau qui sera retourné
  $tab_abonnes_detail = array();
  // on complète déjà avec les élèves
  $tab_eleves_abonnes = array_intersect($tab_eleves_id,$tab_abonnes_id);
  if(!empty($tab_eleves_abonnes))
  {
    foreach($tab_eleves_abonnes as $eleve_id)
    {
      $tab_abonnes_detail[$eleve_id][$eleve_id] = '';
    }
  }
  // on récupère ensuite les parents, uniquement abonnés, et reliés aux élèves, uniquement concernés
  $tab_parents_abonnes = array_intersect($tab_parents_id,$tab_abonnes_id);
  if(!empty($tab_parents_abonnes))
  {
    $listing_parents_abonnes =  implode(',',$tab_parents_abonnes);
    $DB_SQL = 'SELECT parent_id, eleve_id, user_nom, user_prenom ';
    $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
    $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
    $DB_SQL.= 'WHERE parent_id IN('.$listing_parents_abonnes.') AND eleve_id IN('.$listing_eleves_id.') ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_abonnes_detail[$DB_ROW['parent_id']][$DB_ROW['eleve_id']] = 'Notification concernant '.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'.'."\r\n\r\n";
    }
  }
  // game over
  return $tab_abonnes_detail;
}

/**
 * Lister les abonnements pour un profil donné
 *
 * @param string   $profil_type
 * @return array
 */
public static function DB_lister_abonnements_profil( $profil_type )
{
  $DB_SQL = 'SELECT abonnement_ref, abonnement_obligatoire, abonnement_courriel_only, abonnement_descriptif ';
  $DB_SQL.= 'FROM sacoche_abonnement ';
  $DB_SQL.= 'WHERE abonnement_profils LIKE "%'.$profil_type.'%" ';
  $DB_SQL.= 'ORDER BY abonnement_descriptif ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les abonnements d'un utilisateur
 *
 * @param int      $user_id
 * @return array
 */
public static function DB_lister_abonnements_user( $user_id )
{
  $DB_SQL = 'SELECT abonnement_ref, jointure_mode ';
  $DB_SQL.= 'FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
}

/**
 * Lister les notifications qu'un utilisateur peut consulter
 *
 * @param int   $user_id
 * @return array
 */
public static function DB_lister_notifications_consultables_for_user( $user_id )
{
  $DB_SQL = 'SELECT notification_id, notification_statut, notification_date, notification_contenu, abonnement_objet ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'LEFT JOIN sacoche_abonnement USING (abonnement_ref) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND notification_statut IN("consultable","consultée","envoyée") ';
  $DB_SQL.= 'ORDER BY notification_date DESC ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les notifications en attente à rendre visibles (interface ou envoi mail)
 *
 * Le LIMIT sert à ne pas envoyer trop de mails à la fois.
 *
 * @param void
 * @return array
 */
public static function DB_lister_notifications_a_publier()
{
  $DB_SQL = 'SELECT notification_id, notification_contenu, abonnement_objet, user_id, user_nom, user_prenom, user_email, jointure_mode, user_connexion_date ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_abonnement USING (user_id,abonnement_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_abonnement USING (abonnement_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE notification_statut="attente" AND notification_date < DATE_SUB( NOW() , INTERVAL 1 HOUR ) AND user_sortie_date>NOW() ';
  $DB_SQL.= 'LIMIT 25 ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_classes_noms
 *
 * @param void
 * @return array
 */
public static function DB_lister_classes_noms()
{
  $DB_SQL = 'SELECT groupe_id, groupe_nom ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_VAR = array(':type'=>'classe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de notifications qu'un utilisateur n'a pas consulté
 *
 * @param int   $user_id
 * @return int
 */
public static function DB_compter_notifications_non_vues( $user_id )
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'WHERE user_id=:user_id AND notification_statut="consultable" ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $jointure_mode
 * @return void
 */
public static function DB_ajouter_abonnement( $user_id , $abonnement_ref , $jointure_mode )
{
  $DB_SQL = 'INSERT INTO sacoche_jointure_user_abonnement(user_id, abonnement_ref, jointure_mode) ';
  $DB_SQL.= 'VALUES(                                     :user_id,:abonnement_ref,:jointure_mode)';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
    ':jointure_mode'  => $jointure_mode,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter l'enregistrement d'une notification publiée (consultable ou envoyée).
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $notification_statut   'envoyée' | 'consultable'
 * @param string   $notification_contenu
 * @return void
 */
public static function DB_ajouter_log_visible( $user_id , $abonnement_ref , $notification_statut , $notification_contenu )
{
  $DB_SQL = 'INSERT INTO sacoche_notification( user_id, abonnement_ref, notification_attente_id, notification_statut, notification_date, notification_contenu) ';
  $DB_SQL.= 'VALUES                          (:user_id,:abonnement_ref,:notification_attente_id,:notification_statut, NOW()            ,:notification_contenu)';
  $DB_VAR = array(
    ':user_id'                 => $user_id,
    ':abonnement_ref'          => $abonnement_ref,
    ':notification_attente_id' => NULL,
    ':notification_statut'     => $notification_statut,
    ':notification_contenu'    => $notification_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter l'enregistrement d'une notification en attente.
 *
 * Est appelé par DB_modifier_log_attente() et aussi directement.
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @param string   $notification_date         éventuellement NULL -> NOW() dans ce cas
 * @param string   $notification_contenu
 * @return void
 */
public static function DB_ajouter_log_attente( $user_id , $abonnement_ref , $notification_attente_id , $notification_date , $notification_contenu )
{
  $indice_date = ($notification_date==NULL) ? 'NOW()' : ':notification_date' ;
  $DB_SQL = 'INSERT INTO sacoche_notification( user_id, abonnement_ref, notification_attente_id, notification_statut, notification_date, notification_contenu) ';
  $DB_SQL.= 'VALUES                          (:user_id,:abonnement_ref,:notification_attente_id,:notification_statut,'. $indice_date .',:notification_contenu)';
  $DB_VAR = array(
    ':user_id'                 => $user_id,
    ':abonnement_ref'          => $abonnement_ref,
    ':notification_attente_id' => $notification_attente_id,
    ':notification_statut'     => 'attente',
    ':notification_date'       => $notification_date,
    ':notification_contenu'    => $notification_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Remplacer l'enregistrement d'une notification en attente par une autre.
 *
 * Est appelé par DB_modifier_log_attente().
 *
 * @param int      $notification_id
 * @param string   $notification_date   éventuellement NULL -> NOW() dans ce cas
 * @param string   $notification_contenu
 * @return void
 */
public static function DB_remplacer_log_attente( $notification_id , $notification_date , $notification_contenu )
{
  $indice_date = ($notification_date==NULL) ? 'NOW()' : ':notification_date' ;
  $DB_SQL = 'UPDATE sacoche_notification ';
  $DB_SQL.= 'SET notification_date='. $indice_date .', notification_contenu=:notification_contenu ';
  $DB_SQL.= 'WHERE notification_id=:notification_id ';
  $DB_VAR = array(
    ':notification_id'      => $notification_id,
    ':notification_date'    => $notification_date,
    ':notification_contenu' => $notification_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compléter l'enregistrement d'une notification en attente avec une autre.
 *
 * Est appelé par DB_modifier_log_attente().
 *
 * @param int      $notification_id
 * @param string   $notification_date   éventuellement NULL -> NOW() dans ce cas
 * @param string   $contenu_supplementaire
 * @param bool     $sep   Si $mode_maj='compléter', présence d'une séparation ou pas
 * @return void
 */
public static function DB_completer_log_attente( $notification_id , $notification_date , $contenu_supplementaire , $sep )
{
  $indice_date = ($notification_date==NULL) ? 'NOW()' : ':notification_date' ;
  $separateur = ($sep) ? "\r\n".'______________________________________________________________________'."\r\n\r\n" : "\r\n" ;
  $DB_SQL = 'UPDATE sacoche_notification ';
  $DB_SQL.= 'SET notification_date='. $indice_date .', notification_contenu=CONCAT(notification_contenu,:contenu_supplementaire) ';
  $DB_SQL.= 'WHERE notification_id=:notification_id ';
  $DB_VAR = array(
    ':notification_id'        => $notification_id,
    ':notification_date'      => $notification_date,
    ':contenu_supplementaire' => $separateur.$contenu_supplementaire,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier l'enregistrement d'une notification en attente (remplacer, ou compléter).
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @param string   $notification_date         éventuellement NULL -> NOW() dans ce cas
 * @param string   $notification_contenu
 * @param string   $mode_maj   'remplacer' | 'compléter'
 * @param bool     $sep   Si $mode_maj='compléter', présence d'une séparation ou pas
 * @return void
 */
public static function DB_modifier_log_attente( $user_id , $abonnement_ref , $notification_attente_id , $notification_date , $notification_contenu , $mode_maj , $sep=FALSE )
{
  // On cherche si une autre notification du même type est en attente
  $DB_SQL = 'SELECT notification_id ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'WHERE user_id=:user_id AND abonnement_ref=:abonnement_ref AND notification_attente_id=:notification_attente_id AND notification_statut=:notification_statut ';
  $DB_SQL.= 'LIMIT 1 ';
  $DB_VAR = array(
    ':user_id'                 => $user_id,
    ':abonnement_ref'          => $abonnement_ref,
    ':notification_attente_id' => $notification_attente_id,
    ':notification_statut'     => 'attente',
  );
  $notification_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(!$notification_id)
  {
    // Ajout
    DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_attente( $user_id , $abonnement_ref , $notification_attente_id , $notification_date , $notification_contenu );
  }
  elseif($mode_maj=='remplacer')
  {
    // Remplacement
    DB_STRUCTURE_NOTIFICATION::DB_remplacer_log_attente( $notification_id , $notification_date , $notification_contenu );
  }
  elseif($mode_maj=='compléter')
  {
    // Complément
    DB_STRUCTURE_NOTIFICATION::DB_completer_log_attente( $notification_id , $notification_date , $notification_contenu , $sep );
  }
}

/**
 * Modifier le statut d'une notification
 *
 * @param int      $notification_id
 * @param int      $user_id   transmis uniquement pour s'assurer que quelqu'un n'essaye pas de modifier la notification d'un autre
 * @param string   $notification_statut   'envoyée' | 'consultable' | 'consultée'
 * @return int
 */
public static function DB_modifier_statut( $notification_id , $user_id , $notification_statut )
{
  $update_attente_null = ($notification_statut=='consultée') ? '' : ', notification_attente_id=NULL ' ;
  $DB_SQL = 'UPDATE sacoche_notification ';
  $DB_SQL.= 'SET notification_statut=:notification_statut '.$update_attente_null;
  $DB_SQL.= 'WHERE notification_id=:notification_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':notification_id'     => $notification_id,
    ':user_id'             => $user_id,
    ':notification_statut' => $notification_statut,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier la date de notifications en attente dépendantes de la date de visibilité d'un devoir
 *
 * @param int      $devoir_id
 * @param string   $notification_date   éventuellement NULL -> NOW() dans ce cas
 * @return void
 */
public static function DB_modifier_attente_date_devoir( $devoir_id , $notification_date )
{
  $indice_date = ($notification_date==NULL) ? 'NOW()' : ':notification_date' ;
  $DB_SQL = 'UPDATE sacoche_notification ';
  $DB_SQL.= 'SET notification_date='. $indice_date .' ';
  $DB_SQL.= 'WHERE notification_attente_id=:notification_attente_id AND notification_statut=:notification_statut ';
  $DB_SQL.= 'AND abonnement_ref IN("devoir_edition","devoir_saisie","demande_evaluation_prof") ';
  $DB_VAR = array(
    ':notification_date'       => $notification_date,
    ':notification_attente_id' => $devoir_id,
    ':notification_statut'     => 'attente',
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $jointure_mode
 * @return void
 */
public static function DB_modifier_abonnement( $user_id , $abonnement_ref , $jointure_mode )
{
  $DB_SQL = 'UPDATE sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'SET jointure_mode=:jointure_mode ';
  $DB_SQL.= 'WHERE user_id=:user_id AND abonnement_ref=:abonnement_ref ';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
    ':jointure_mode'  => $jointure_mode,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @return void
 */
public static function DB_supprimer_abonnement( $user_id , $abonnement_ref )
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'WHERE user_id=:user_id AND abonnement_ref=:abonnement_ref ';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer l'enregistrement d'une notification précise.
 *
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @param int      $user_id   facultatif
 * @return void
 */
public static function DB_supprimer_log_id( $notification_id )
{
  $DB_SQL = 'DELETE FROM sacoche_notification ';
  $DB_SQL.= 'WHERE notification_id=:notification_id ';
  $DB_VAR = array( ':notification_id' => $notification_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer l'enregistrement de notifications en attente.
 *
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @param int      $user_id   facultatif
 * @return void
 */
public static function DB_supprimer_log_attente( $abonnement_ref , $notification_attente_id , $user_id=NULL )
{
  $where_user_id = ($user_id) ? 'AND user_id=:user_id ' : '' ;
  $DB_SQL = 'DELETE FROM sacoche_notification ';
  $DB_SQL.= 'WHERE abonnement_ref=:abonnement_ref AND notification_attente_id=:notification_attente_id AND notification_statut=:notification_statut '.$where_user_id;
  $DB_VAR = array(
    ':abonnement_ref'          => $abonnement_ref,
    ':notification_attente_id' => $notification_attente_id,
    ':notification_statut'     => 'attente',
    ':user_id'                 => $user_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer l'enregistrement de notifications visibles de plus de 2 mois.
 *
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @return void
 */
public static function DB_supprimer_log_vus_anciens()
{
  $DB_SQL = 'DELETE FROM sacoche_notification ';
  $DB_SQL.= 'WHERE notification_statut IN("consultée","envoyée") AND notification_date < DATE_SUB( NOW() , INTERVAL 2 MONTH ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Requête qui permet de récupérer les ids libérés s'il n'y a pas eu de nouvel enregistrement depuis
  $DB_SQL = 'ALTER TABLE sacoche_notification AUTO_INCREMENT=1 ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Supprimer l'enregistrement de notifications périmées (année précédente), sauf administrateurs.
 *
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id
 * @return void
 */
public static function DB_supprimer_log_sauf_admin()
{
  $DB_SQL = 'DELETE sacoche_notification ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE user_profil_sigle!="ADM" ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Requête qui permet de récupérer les ids libérés s'il n'y a pas eu de nouvel enregistrement depuis
  $DB_SQL = 'ALTER TABLE sacoche_notification AUTO_INCREMENT=1 ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Enregistrer une action sensible.
 *
 * @param string   $notification_contenu
 * @return void
 */
public static function enregistrer_action_sensible($notification_contenu)
{
  $abonnement_ref = 'action_sensible';
  $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref );
  if($listing_abonnes)
  {
    $tab_abonnes = explode(',',$listing_abonnes);
    foreach($tab_abonnes as $abonne_id)
    {
      DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , FALSE /*sep*/ );
    }
  }
}

/**
 * Enregistrer une action effectuée par un autre administrateur.
 *
 * @param string   $notification_contenu
 * @param int      $admin_id
 * @return void
 */
public static function enregistrer_action_admin($notification_contenu,$admin_id)
{
  $abonnement_ref = 'action_admin';
  $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $admin_id , FALSE );
  if($listing_abonnes)
  {
    $tab_abonnes = explode(',',$listing_abonnes);
    foreach($tab_abonnes as $abonne_id)
    {
      DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , FALSE /*sep*/ );
    }
  }
}

}
?>