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
// Ces méthodes ne concernent surtout les tables "sacoche_message" et "sacoche_jointure_message_destinataire".

class DB_STRUCTURE_MESSAGE extends DB
{

/**
 * lister_messages_for_user_auteur
 *
 * @param int    $user_id
 * @return array
 */
public static function DB_lister_messages_for_user_auteur($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT message_id, message_debut_date, message_fin_date, message_contenu, ';
  $DB_SQL.= 'GROUP_CONCAT( CONCAT(user_profil_type,"_",destinataire_type,"_",destinataire_id) SEPARATOR ",") AS message_destinataires, ';
  $DB_SQL.= 'COUNT(destinataire_id) AS destinataires_nombre ';
  $DB_SQL.= 'FROM sacoche_message ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire USING (message_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_SQL.= 'GROUP BY message_id ';
  $DB_SQL.= 'ORDER BY message_fin_date DESC, message_debut_date DESC';
  $DB_VAR = array(':user_id'=>$user_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_messages_for_user_destinataire
 *
 * @param int    $user_id
 * @param string $user_profil_type
 * @return array
 */
public static function DB_lister_messages_for_user_destinataire( $user_id , $user_profil_type )
{
  $WHERE = '';
  // récupérer la liste des niveaux / classes / groupes / besoins rattachés à un prof / élève / parent
  if(in_array($user_profil_type,array('eleve','parent','professeur')))
  {
    $DB_SQL = 'SELECT groupe_type, groupe_id, niveau_id ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
    $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type!="eval" ';
    $DB_VAR = array( ':user_id' => $user_id );
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    // Il reste encore, pour les élèves et les parents, les associations à partir de la classe dans sacoche_user.eleve_classe_id
    $tab_classe_id = array();
    if($_SESSION['ELEVE_CLASSE_ID'])
    {
      $tab_classe_id[] = $_SESSION['ELEVE_CLASSE_ID'];
    }
    elseif( ($_SESSION['USER_PROFIL_TYPE']=='parent') && is_array($_SESSION['OPT_PARENT_CLASSES']) )
    {
      foreach($_SESSION['OPT_PARENT_CLASSES'] as $tab)
      {
        $tab_classe_id[] = $tab['valeur'];
      }
    }
    if(!empty($tab_classe_id))
    {
      $DB_SQL = 'SELECT groupe_type, groupe_id, niveau_id ';
      $DB_SQL.= 'FROM sacoche_groupe ';
      $DB_SQL.= 'WHERE groupe_id IN('.implode(',',$tab_classe_id).') ';
      $DB_TAB = array_merge( $DB_TAB , DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL) );
    }
    // On récupère les résulats
    $tab_regroupements = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_regroupements[$DB_ROW['groupe_type']][$DB_ROW['groupe_id']] = $DB_ROW['groupe_id'];
      $tab_regroupements['niveau'              ][$DB_ROW['niveau_id']] = $DB_ROW['niveau_id'];
    }
    // On construit le complément de requête
    foreach($tab_regroupements as $destinataire_type => $tab_destinataire_id)
    {
      $WHERE .= 'OR ( destinataire_type="'.$destinataire_type.'" AND destinataire_id IN('.implode(',',$tab_destinataire_id).') ) ';
    }
  }
  $DB_SQL = 'SELECT message_id, user_genre, user_nom, user_prenom, message_contenu, message_dests_cache ';
  $DB_SQL.= 'FROM sacoche_message ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire USING (message_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE user_profil_type=:user_profil_type AND message_debut_date<NOW() AND message_fin_date > DATE_SUB( NOW() , INTERVAL 1 DAY ) '; // NOW() renvoie un datetime
  $DB_SQL.= 'AND ( ( destinataire_type="all" ) OR ( destinataire_type="user" AND destinataire_id=:destinataire_id ) '.$WHERE.' ) ';
  $DB_SQL.= 'ORDER BY message_debut_date DESC, message_fin_date ASC';
  $DB_VAR = array(
    ':user_profil_type' => $user_profil_type,
    ':destinataire_id'  => $user_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_message
 *
 * @param int    $user_id
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $message_contenu
 * @return int
 */
public static function DB_ajouter_message( $user_id , $date_debut_mysql , $date_fin_mysql , $message_contenu )
{
  $DB_SQL = 'INSERT INTO sacoche_message( user_id, message_debut_date, message_fin_date, message_contenu, message_dests_cache) ';
  $DB_SQL.= 'VALUES                     (:user_id,:message_debut_date,:message_fin_date,:message_contenu,:message_dests_cache)';
  $DB_VAR = array(
    ':user_id'               => $user_id,
    ':message_debut_date'    => $date_debut_mysql,
    ':message_fin_date'      => $date_fin_mysql,
    ':message_contenu'       => $message_contenu,
    ':message_dests_cache'   => ',',
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_message
 * Rmq : à chaque modification de message, le champ "message_dests_cache" est réinitialisé ; ce n'est pas une limitation mais bien un comportement souhaité (c'est potentiellement un message différent, donc on le rend visible).
 *
 * @param int    $message_id
 * @param int    $user_id
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $message_contenu
 * @return void
 */
public static function DB_modifier_message( $message_id , $user_id , $date_debut_mysql , $date_fin_mysql , $message_contenu )
  {
  $DB_SQL = 'UPDATE sacoche_message ';
  $DB_SQL.= 'SET message_debut_date=:message_debut_date, message_fin_date=:message_fin_date, message_contenu=:message_contenu, message_dests_cache=:message_dests_cache ';
  $DB_SQL.= 'WHERE message_id=:message_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':message_debut_date'    => $date_debut_mysql,
    ':message_fin_date'      => $date_fin_mysql,
    ':message_contenu'       => $message_contenu,
    ':message_dests_cache'   => ',',
    ':message_id'            => $message_id,
    ':user_id'               => $user_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_message_destinataires
 *
 * @param int    $message_id
 * @param array  $tab_destinataires
 * @param string $mode   "creer" | "substituer"
 * @return void
 */
public static function DB_modifier_message_destinataires($message_id,$tab_destinataires,$mode)
{
  $tab_old_destinataires = array();
  // On récupère la liste des destinataires actuels pour comparer
  if($mode=='substituer')
  {
    $DB_SQL = 'SELECT CONCAT(user_profil_type,"_",destinataire_type,"_",destinataire_id) AS destinataire_info ';
    $DB_SQL.= 'FROM sacoche_jointure_message_destinataire ';
    $DB_SQL.= 'WHERE message_id=:message_id ';
    $DB_VAR = array(':message_id'=>$message_id);
    $tab_old_destinataires = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // On compare
  $tab_destinataires_a_ajouter = array_diff( $tab_destinataires , $tab_old_destinataires );
  $tab_destinataires_a_retirer = array_diff( $tab_old_destinataires , $tab_destinataires );
  // On ajoute si besoin
  if(count($tab_destinataires_a_ajouter))
  {
    $DB_SQL = 'INSERT INTO sacoche_jointure_message_destinataire( message_id, user_profil_type, destinataire_type, destinataire_id) ';
    $DB_SQL.= 'VALUES                                           (:message_id,:user_profil_type,:destinataire_type,:destinataire_id) ';
    foreach($tab_destinataires_a_ajouter as $destinataire_infos)
    {
      list( $user_profil_type , $destinataire_type , $destinataire_id ) = explode('_',$destinataire_infos);
      $DB_VAR = array(
        ':message_id'        => $message_id,
        ':user_profil_type'  => $user_profil_type,
        ':destinataire_type' => $destinataire_type,
        ':destinataire_id'   => $destinataire_id,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
  // On retire si besoin
  if(count($tab_destinataires_a_retirer))
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_message_destinataire ';
    $DB_SQL.= 'WHERE message_id=:message_id AND user_profil_type=:user_profil_type AND destinataire_type=:destinataire_type AND destinataire_id=:destinataire_id ';
    foreach($tab_destinataires_a_retirer as $destinataire_infos)
    {
      list( $user_profil_type , $destinataire_type , $destinataire_id ) = explode('_',$destinataire_infos);
      $DB_VAR = array(
        ':message_id'        => $message_id,
        ':user_profil_type'  => $user_profil_type,
        ':destinataire_type' => $destinataire_type,
        ':destinataire_id'   => $destinataire_id,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
}

/**
 * modifier_message_dests_cache
 *
 * @param int    $message_id
 * @param int    $user_id
 * @param bool   $etat   FALSE pour masquer | TRUE ou voir
 * @return void
 */
public static function DB_modifier_message_dests_cache($message_id,$user_id,$etat)
{
  $commande = ($etat) ? 'REPLACE(message_dests_cache,CONCAT(",",:user_id,","),",")' : 'CONCAT(message_dests_cache,:user_id,",")' ; // Attention : ne pas mettre d'espaces !
  $DB_SQL = 'UPDATE sacoche_message ';
  $DB_SQL.= 'SET message_dests_cache = '.$commande.' ';
  $DB_SQL.= 'WHERE message_id=:message_id ';
  $DB_VAR = array(
    ':message_id' => $message_id,
    ':user_id'    => $user_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_message
 *
 * @param int   $message_id
 * @param int   $user_id
 * @return int
 */
public static function DB_supprimer_message($message_id,$user_id)
{
  $DB_SQL = 'DELETE FROM sacoche_message ';
  $DB_SQL.= 'WHERE message_id=:message_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':message_id' => $message_id,
    ':user_id'    => $user_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_message_destinataires
 *
 * @param int   $message_id
 * @return void
 */
public static function DB_supprimer_message_destinataires($message_id)
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'WHERE message_id=:message_id ';
  $DB_VAR = array(
    ':message_id' => $message_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_destinataires_texte
 *
 * @param string $destinataire_type   'niveau' | 'classe' | 'groupe' | 'besoin' | 'user'
 * @param string $listing_id
 * @return array
 */
public static function DB_recuperer_destinataires_texte( $destinataire_type , $listing_id )
{
  if($destinataire_type=='niveau')
  {
    $DB_SQL = 'SELECT niveau_id AS id, CONCAT(" '.ucfirst($destinataire_type).' ",niveau_nom) AS texte '; // espace en début de texte pour le tri ultérieur des lignes
    $DB_SQL.= 'FROM sacoche_niveau ';
    $DB_SQL.= 'WHERE niveau_id IN('.$listing_id.') ';
  }
  elseif($destinataire_type=='user')
  {
    $DB_SQL = 'SELECT user_id AS id, CONCAT(user_nom," ",user_prenom) AS texte ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'WHERE user_id IN('.$listing_id.') ';
  }
  else // 'classe' | 'groupe' | 'besoin'
  {
    $DB_SQL = 'SELECT groupe_id AS id, CONCAT(" '.ucfirst($destinataire_type).' ",groupe_nom) AS texte ';
    $DB_SQL.= 'FROM sacoche_groupe ';
    $DB_SQL.= 'WHERE groupe_id IN('.$listing_id.') AND groupe_type="'.$destinataire_type.'" ';
  }
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_user_id_from_destinataires
 *
 * @param array $tab_destinataires
 * @return array
 */
public static function DB_recuperer_user_id_from_destinataires( $tab_destinataires )
{
  $tab_user_id = array();
  foreach($tab_destinataires as $destinataire_infos)
  {
    list( $user_profil_type , $destinataire_type , $destinataire_id ) = explode('_',$destinataire_infos);
    if($destinataire_type=='user')
    {
      $tab_user_id[$destinataire_id] = $destinataire_id;
    }
    else
    {
      $champs = ($user_profil_type!='parent') ? 'user_id AS destinataire_id' : 'parent.user_id AS destinataire_id' ;
      $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $user_profil_type /*profil_type*/ , 1 /*statut*/ , $destinataire_type , $destinataire_id , 'alpha' /*eleves_ordre*/ , $champs ) ;
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_user_id[$DB_ROW['destinataire_id']] = $DB_ROW['destinataire_id'];
      }
    }
  return $tab_user_id;
  }
}

}
?>