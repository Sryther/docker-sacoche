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
// Ces méthodes ne concernent que les professeurs.

class DB_STRUCTURE_PROFESSEUR extends DB
{

/**
 * recuperer_devoir_ponctuel_prof_by_date
 *
 * @param int    $prof_id
 * @param string $date_mysql
 * @param string $description
 * @return array
 */
public static function DB_recuperer_devoir_ponctuel_prof_by_date($prof_id,$date_mysql,$description)
{
  $DB_SQL = 'SELECT devoir_id, groupe_id ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE proprio_id=:proprio_id AND groupe_type=:type4 AND devoir_date>=:date_mysql AND devoir_info=:description ' ;
  $DB_SQL.= 'LIMIT 1';
  $DB_VAR = array(
    ':proprio_id'  => $prof_id,
    ':type4'       => 'eval',
    ':date_mysql'  => $date_mysql,
    ':description' => $description,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_devoir_prorietaire_id
 *
 * @param int $devoir_id
 * @return int
 */
public static function DB_recuperer_devoir_prorietaire_id($devoir_id)
{
  $DB_SQL = 'SELECT proprio_id ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_devoir_prorietaire_identite
 *
 * @param int $devoir_id
 * @return string
 */
public static function DB_recuperer_devoir_prorietaire_identite($devoir_id)
{
  $DB_SQL = 'SELECT user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner une chaine avec les id des élèves d'une classe ou d'un groupe
 *
 * @param string $groupe_type    "classe" | "groupe"
 * @param int    $groupe_id      id du niveau ou de la classe ou du groupe
 * @return string
 */
public static function DB_recuperer_listing_eleves_id( $groupe_type , $groupe_id )
{
  if($groupe_type=='classe')
  {
    $join_groupe = '';
    $where_groupe = 'AND eleve_classe_id=:groupe ';
  }
  else
  {
    $join_groupe = 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
    $where_groupe = 'AND sacoche_jointure_user_groupe.groupe_id=:groupe ';
  }
  $DB_SQL = 'SELECT CONVERT( GROUP_CONCAT(user_id SEPARATOR ",") , CHAR) AS identifiants ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= $join_groupe;
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() '.$where_groupe;
  $DB_VAR = array(
    ':profil_type' => 'eleve',
    ':groupe' => $groupe_id,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_devoir_ponctuel_prof_by_ids
 *
 * @param int    $devoir_id
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $description
 * @return bool
 */
public static function DB_tester_devoir_ponctuel_prof_by_ids($devoir_id,$prof_id,$groupe_id,$description)
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND proprio_id=:proprio_id AND groupe_id=:groupe_id AND groupe_type=:type4 AND devoir_info=:description ' ;
  $DB_SQL.= 'LIMIT 1';
  $DB_VAR = array(
    ':devoir_id'   => $devoir_id,
    ':proprio_id'  => $prof_id,
    ':groupe_id'   => $groupe_id,
    ':type4'       => 'eval',
    ':description' => $description,
  );
  return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_demandes_evaluation
 *
 * @param int  $prof_id
 * @param string $user_join_groupes
 * @return array
 */
public static function DB_compter_demandes_evaluation($prof_id,$user_join_groupes)
{
  $listing_eleves_id = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $prof_id , $user_join_groupes , 'string' /*format_retour*/ );
  if(!$listing_eleves_id)
  {
    return array();
  }
  $DB_SQL = 'SELECT demande_statut, COUNT(demande_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere ON sacoche_demande.matiere_id=sacoche_jointure_user_matiere.matiere_id ';
  $DB_SQL.= 'WHERE eleve_id IN('.$listing_eleves_id.') AND prof_id IN(0,:prof_id) AND sacoche_jointure_user_matiere.user_id=:user_id ';
  $DB_SQL.= 'GROUP BY demande_statut ';
  $DB_VAR = array(
    ':prof_id' => $_SESSION['USER_ID'],
    ':user_id' => $_SESSION['USER_ID'],
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
}

/**
 * Retourner les niveaux et matières des référentiels auxquels un professeur a accès.
 *
 * @param int  $prof_id
 * @return array
 */
public static function DB_lister_matieres_niveaux_referentiels_professeur($prof_id)
{
  $DB_SQL = 'SELECT matiere_id, matiere_ref, matiere_nom, niveau_id, niveau_nom, jointure_coord ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 AND niveau_actif=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC';
  $DB_VAR = array(':user_id'=>$prof_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_matieres_professeur_infos_referentiel
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_lister_matieres_professeur_infos_referentiel($prof_id)
{
  $DB_SQL = 'SELECT matiere_id, matiere_nom, matiere_nb_demandes, jointure_coord ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':user_id'=>$prof_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_eleves_from_groupes
 *
 * @param string   $listing_groupe_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_classes_eleves_from_groupes($listing_groupe_id)
{
  $DB_SQL = 'SELECT groupe_id, eleve_classe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE groupe_id IN ('.$listing_groupe_id.') AND user_profil_type=:profil_type AND user_sortie_date>NOW() AND eleve_classe_id!=0 ';
  $DB_SQL.= 'GROUP BY groupe_id, eleve_classe_id ';
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

/**
 * lister_groupes_besoins
 * Il s'agit des groupes de besoins auxquels un prof est rattaché, propriétaire ou pas.
 *
 * @param int    $prof_id
 * @return array
 */
public static function DB_lister_groupes_besoins($prof_id)
{
  $DB_SQL = 'SELECT jointure_pp, groupe_id, groupe_nom, niveau_id, niveau_ordre, niveau_nom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $prof_id,
    ':type'    => 'besoin',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_besoins_non_proprietaire_avec_infos
 * Il s'agit des groupes de besoins dont un prof n'est pas propriétaire, mais qui lui est affecté, avec l'info sur son propriétaire.
 *
 * @param int    $prof_id
 * @return array
 */
/*
public static function DB_lister_groupes_besoins_non_proprietaire_avec_infos($prof_id)
{
  $DB_SQL = 'SELECT groupe_id, groupe_nom, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_user AS locataire ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe ON locataire.user_id=sacoche_jointure_user_groupe.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS locataire ';
  $DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=:proprio AND groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $prof_id,
    ':proprio' => 1,
    ':type'    => 'besoin',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}
*/

/**
 * lister_eleves_classes
 *
 * @param string   $listing_classe_id   id des classes séparés par des virgules
 * @return array
 */
public static function DB_lister_eleves_classes($listing_classe_id)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_login, eleve_classe_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() AND eleve_classe_id IN ('.$listing_classe_id.') ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleves_groupes
 *
 * @param string   $listing_groupe_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_eleves_groupes($listing_groupe_id)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_login, groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() AND groupe_id IN ('.$listing_groupe_id.') ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_avec_groupes_besoins
 *
 * @param string   $listing_groupes_id   liste des ids des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_users_avec_groupes_besoins($listing_groupes_id)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_profil_type, groupe_id, jointure_pp ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE groupe_id IN('.$listing_groupes_id.') AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte optgroup] des élèves d'un professeur identifié
 *
 * @param int    $user_id
 * @param string $user_join_groupes
 * @return array|string
 */
public static function DB_OPT_lister_eleves_professeur($prof_id,$user_join_groupes)
{
  // sous-requêtes [http://dev.mysql.com/doc/refman/5.0/fr/subqueries.html]
  if($user_join_groupes=='config')
  {
    $requete_id_classes = 'SELECT groupe_id FROM sacoche_jointure_user_groupe LEFT JOIN sacoche_groupe USING (groupe_id) WHERE user_id=:user_id AND groupe_type=:type1';
    $requete_id_groupes = 'SELECT groupe_id FROM sacoche_jointure_user_groupe LEFT JOIN sacoche_groupe USING (groupe_id) WHERE user_id=:user_id AND groupe_type=:type2';
  }
  else
  {
    $requete_id_classes = 'SELECT groupe_id FROM sacoche_groupe WHERE groupe_type=:type1';
    $requete_id_groupes = 'SELECT groupe_id FROM sacoche_groupe WHERE groupe_type=:type2';
  }
  // éléments des deux requêtes
  $sql_select = 'SELECT sacoche_user.user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte, groupe_nom AS optgroup , groupe_type, niveau_ordre ';
  $sql_from   = 'FROM sacoche_groupe LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $sql_where  = 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $sql_join_classe = 'LEFT JOIN sacoche_user ON sacoche_groupe.groupe_id = sacoche_user.eleve_classe_id ';
  $sql_join_groupe = 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) LEFT JOIN sacoche_user USING(user_id) ';
  $sql_join_profil = 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  // les deux requêtes
  $DB_SQL_CLASSE = $sql_select.$sql_from.$sql_join_classe.$sql_join_profil.$sql_where.'AND groupe_id IN ('.$requete_id_classes.')';
  $DB_SQL_GROUPE = $sql_select.$sql_from.$sql_join_groupe.$sql_join_profil.$sql_where.'AND groupe_id IN ('.$requete_id_groupes.')';
  // Union des deux requêtes [http://dev.mysql.com/doc/refman/5.0/fr/union.html]
  $DB_SQL = '( '.$DB_SQL_CLASSE.' ) UNION ( '.$DB_SQL_GROUPE.' ) ORDER BY niveau_ordre ASC, groupe_type ASC, optgroup ASC, texte ASC ';
  $DB_VAR = array(
    ':user_id'     => $prof_id,
    ':profil_type' => 'eleve',
    ':type1'       => 'classe',
    ':type2'       => 'groupe',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève ne vous est affecté.' ;
}

/**
 * Retourner le listing des ids des élèves d'un professeur identifié
 *
 * @param int    $user_id
 * @param string $user_join_groupes
 * @param string $format_retour   'string'|'array'
 * @return string | array
 */
public static function DB_lister_ids_eleves_professeur( $prof_id , $user_join_groupes , $format_retour )
{
  // sous-requêtes [http://dev.mysql.com/doc/refman/5.0/fr/subqueries.html]
  if($user_join_groupes=='config')
  {
    $requete_id_classes = 'SELECT groupe_id FROM sacoche_jointure_user_groupe LEFT JOIN sacoche_groupe USING (groupe_id) WHERE user_id=:user_id AND groupe_type=:type1';
    $requete_id_groupes = 'SELECT groupe_id FROM sacoche_jointure_user_groupe LEFT JOIN sacoche_groupe USING (groupe_id) WHERE user_id=:user_id AND groupe_type=:type2';
  }
  else
  {
    $requete_id_classes = 'SELECT groupe_id FROM sacoche_groupe WHERE groupe_type=:type1';
    $requete_id_groupes = 'SELECT groupe_id FROM sacoche_groupe WHERE groupe_type=:type2';
  }
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // éléments des deux requêtes
  $sql_select = 'SELECT GROUP_CONCAT(DISTINCT sacoche_user.user_id SEPARATOR ",") AS listing_eleves_id ';
  $sql_from   = 'FROM sacoche_groupe LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $sql_where  = 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $sql_join_classe = 'LEFT JOIN sacoche_user ON sacoche_groupe.groupe_id = sacoche_user.eleve_classe_id ';
  $sql_join_groupe = 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) LEFT JOIN sacoche_user USING(user_id) ';
  $sql_join_profil = 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  // les deux requêtes
  $DB_SQL_CLASSE = $sql_select.$sql_from.$sql_join_classe.$sql_join_profil.$sql_where.'AND groupe_id IN ('.$requete_id_classes.')';
  $DB_SQL_GROUPE = $sql_select.$sql_from.$sql_join_groupe.$sql_join_profil.$sql_where.'AND groupe_id IN ('.$requete_id_groupes.')';
  // Union des deux requêtes [http://dev.mysql.com/doc/refman/5.0/fr/union.html]
  $DB_SQL = '( '.$DB_SQL_GROUPE.' ) UNION ( '.$DB_SQL_CLASSE.' )';
  $DB_VAR = array(
    ':user_id'     => $prof_id,
    ':profil_type' => 'eleve',
    ':type1'       => 'classe',
    ':type2'       => 'groupe',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $tab_listing_id = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['listing_eleves_id'] !== NULL)
    {
      $tab_listing_id[] = $DB_ROW['listing_eleves_id'];
    }
  }
  $listing_eleves_id = implode(',',$tab_listing_id);
  if($format_retour=='string')
  {
    return $listing_eleves_id;
  }
  else
  {
    return explode( ',' , $listing_eleves_id);
  }
}

/**
 * Lister les élèves ayant déjà fait une évaluation de nom donné avec un prof donné à partir d'une date donnée.
 * Seules les évaluations sur des sélections d'élèves sont prises en compte.
 * MAX() pour éviter l'erreur "SELECT list is not in GROUP BY clause and contains nonaggregated column 'sacoche_devoir.devoir_date' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by"
 *
 * @param int    $prof_id
 * @param string $devoir_info
 * @param string $date_debut_mysql
 * @return array
 */
public static function DB_lister_eleves_devoirs($prof_id,$devoir_info,$date_debut_mysql)
{
  $position_description = mb_strpos( $devoir_info , '#' );
  if(!$position_description) // FALSE ou 0 (aucun des deux n'est intéressant)
  {
    $where_description = 'AND devoir_info = :devoir_info ';
  }
  else
  {
    $devoir_info = mb_substr( $devoir_info , 0 , $position_description ) . '%';
    $where_description = 'AND devoir_info LIKE :devoir_info ';
  }
  $DB_SQL = 'SELECT user_id, MAX(devoir_date) AS devoir_date ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
  $DB_SQL.= 'WHERE ( sacoche_devoir.proprio_id=:proprio_id OR sacoche_jointure_devoir_prof.prof_id=:prof_id ) ';
  $DB_SQL.= $where_description;
  $DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" ';
  $DB_SQL.= 'AND groupe_type=:type4 ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(
    ':proprio_id'  => $prof_id,
    ':prof_id'     => $prof_id,
    ':devoir_info' => $devoir_info,
    ':type4'       => 'eval',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoirs_prof
 *
 * @param int    $prof_id
 * @param int    $groupe_id        id du groupe ou de la classe pour un devoir sur une classe ou un groupe ; 0 pour un devoir sur une sélection d'élèves ; -1 pour les devoirs de toutes les classes / tous les groupes
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @return array
 */
public static function DB_lister_devoirs_prof($prof_id,$groupe_id,$date_debut_mysql,$date_fin_mysql)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // Il faut commencer par lister les ids de devoirs sinon en cas de jointure du prof sur sacoche_jointure_devoir_prof on ne récupère pas la liste des autres profs associés à l'éval.
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT devoir_id SEPARATOR ",") AS devoirs_listing ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
  $DB_SQL.= 'WHERE ( sacoche_devoir.proprio_id=:proprio_id OR sacoche_jointure_devoir_prof.prof_id=:prof_id ) ';
  $DB_VAR = array(
    ':proprio_id' => $prof_id,
    ':prof_id'    => $prof_id,
  );
  $devoirs_listing = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(empty($devoirs_listing))
  {
    return array();
  }
  // Il faut ajouter dans la requête des "DISTINCT" sinon la liaison avec "sacoche_jointure_user_groupe" duplique tout x le nb d'élèves associés pour une évaluation sur une sélection d'élèves.
  $DB_SQL = 'SELECT sacoche_devoir.*, CONCAT(prof.user_nom," ",prof.user_prenom) AS proprietaire, ';
  $DB_SQL.= 'GROUP_CONCAT(DISTINCT sacoche_jointure_devoir_item.item_id SEPARATOR "_") AS items_listing, ';
  $DB_SQL.= 'COUNT(DISTINCT sacoche_jointure_devoir_item.item_id) AS items_nombre, ';
  $DB_SQL.= 'GROUP_CONCAT(DISTINCT CONCAT(SUBSTRING(sacoche_jointure_devoir_prof.jointure_droit,1,1),sacoche_jointure_devoir_prof.prof_id) SEPARATOR "_") AS partage_listing, ';
  if(!$groupe_id)
  {
    $DB_SQL .= 'GROUP_CONCAT(DISTINCT sacoche_jointure_user_groupe.user_id SEPARATOR "_") AS users_listing, ';
    $DB_SQL .= 'COUNT(DISTINCT sacoche_jointure_user_groupe.user_id) AS users_nombre, ';
  }
  $DB_SQL.= 'groupe_type, groupe_nom ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  if(!$groupe_id)
  {
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_user AS eleves ON sacoche_jointure_user_groupe.user_id=eleves.user_id ';
  }
  $DB_SQL.= 'LEFT JOIN sacoche_user AS prof ON sacoche_devoir.proprio_id=prof.user_id ';
  $DB_SQL.= 'WHERE devoir_id IN ('.$devoirs_listing.') ';
  $DB_SQL.= ($groupe_id!=0) ? 'AND groupe_type!=:type4 ' : 'AND groupe_type=:type4 ' ;
  $DB_SQL.= ($groupe_id>0)  ? 'AND groupe_id='.$groupe_id.' ' : '' ;

  $DB_SQL.= ($groupe_id==0) ? 'AND eleves.user_profil_sigle="ELV" ' : '' ; // Sinon les prof (aussi rattachés au groupe du devoir) sont comptés parmi la liste des élèves.

  $DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" ' ;
  $DB_SQL.= 'GROUP BY sacoche_devoir.devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, groupe_nom ASC';
  $DB_VAR = array( ':type4' => 'eval' );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoirs_prof_groupe_sans_infos_last
 * Pour l'enseignant propriétaire du devoir et les enseignants ayant un accès en modification.
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $groupe_type   groupe | select
 * @return array
 */
public static function DB_lister_devoirs_prof_groupe_sans_infos_last($prof_id,$groupe_id,$groupe_type)
{
  $DB_SQL = 'SELECT devoir_id, groupe_id, devoir_info, devoir_date, devoir_visible_date ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE ( sacoche_devoir.proprio_id=:proprio_id OR ( sacoche_jointure_devoir_prof.prof_id=:prof_id AND jointure_droit=:jointure_droit ) ) ';
  $DB_SQL.= ($groupe_type=='groupe') ? 'AND groupe_type!=:type4 AND groupe_id=:groupe_id ' : 'AND groupe_type=:type4 ' ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC ';
  $DB_SQL.= 'LIMIT 20 ';
  $DB_VAR = array(
    ':proprio_id'     => $prof_id,
    ':prof_id'        => $prof_id,
    ':groupe_id'      => $groupe_id,
    ':jointure_droit' => 'modifier',
    ':type4'          => 'eval',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoirs_prof_groupe_sans_infos_last
 * Pour l'enseignant propriétaire du devoir et les enseignants ayant un accès en modification.
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @return array
 */
public static function DB_OPT_eleves_devoirs_prof_groupe($prof_id,$groupe_id)
{
  $DB_SQL = 'SELECT devoir_id AS valeur, devoir_info AS texte , devoir_date , groupe_nom ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE ( sacoche_devoir.proprio_id=:proprio_id OR sacoche_jointure_devoir_prof.prof_id=:prof_id ) ';
  $DB_SQL.= 'AND ( groupe_id=:groupe_id OR groupe_type=:type4 ) ';
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC ';
  $DB_VAR = array(
    ':proprio_id'     => $prof_id,
    ':prof_id'        => $prof_id,
    ':groupe_id'      => $groupe_id,
    ':jointure_droit' => 'modifier',
    ':type4'          => 'eval',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune évaluation trouvée.' ;
}

/**
 * lister_devoir_items
 * Retourner les items d'un devoir
 *
 * @param int   $devoir_id
 * @param bool  $with_socle
 * @param bool  $with_coef
 * @param bool  $with_ref
 * @param bool  $with_comm
 * @param bool  $with_lien
 * @return array
 */
public static function DB_lister_devoir_items( $devoir_id , $with_socle , $with_coef , $with_ref , $with_comm , $with_lien )
{
  // Depuis MySQL 5.7.5 la directive ONLY_FULL_GROUP_BY est activée ce qui plantait la requête ci-dessous
  // (SELECT list is not in GROUP BY clause and contains nonaggregated column 'sacoche_jointure_devoir_item.item_id' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by)
  // d'où le remplacement d'un LEFT JOIN par un INNER JOIN
  $DB_SQL = 'SELECT item_id, ';
  $DB_SQL.= ($with_socle) ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS s2016_nb, ' : '' ;
  $DB_SQL.= ($with_coef)  ? 'item_coef, '   : '' ;
  $DB_SQL.= ($with_lien)  ? 'item_lien, '   : '' ;
  $DB_SQL.= ($with_ref)   ? 'matiere_ref, ' : '' ;
  $DB_SQL.= ($with_ref)   ? 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto, ' : '' ;
  $DB_SQL.= ($with_ref)   ? 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso, ' : '' ;
  $DB_SQL.= ($with_comm)  ? 'item_comm, '   : '' ;
  $DB_SQL.= 'item_nom ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
  $DB_SQL.= 'INNER JOIN sacoche_referentiel_item USING (item_id) '; // 
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= ($with_socle) ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_SQL.= ($with_socle) ? 'GROUP BY sacoche_referentiel_item.item_id ' : '' ;
  $DB_SQL.= 'ORDER BY jointure_ordre ASC, matiere_ref ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array( ':devoir_id' => $devoir_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoir_saisies
 *
 * @param int   $devoir_id
 * @param bool  $with_marqueurs   // Avec ou sans les marqueurs de demandes d'évaluations
 * @return array
 */
public static function DB_lister_devoir_saisies($devoir_id,$with_marqueurs)
{
  // On évite les élèves désactivés pour ces opérations effectuées sur les pages de saisies d'évaluations
  $DB_SQL = 'SELECT eleve_id, item_id, saisie_note, prof_id ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND user_sortie_date>NOW() ';
  $DB_SQL.= ($with_marqueurs) ? '' : 'AND saisie_note!="PA" ' ;
  $DB_VAR = array( ':devoir_id' => $devoir_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les effectifs des classes / groupes
 *
 * @param int    $groupe_id   Pour restreindre à un groupe donné
 * @return array|int   tableau (groupe_id;eleves_nombre) si pas de restriction à un groupe donné, l'effectif du groupe concerné sinon
 */
public static function DB_lister_effectifs_groupes($groupe_id=0)
{
  // éléments des deux requêtes
  $sql_select = 'SELECT COUNT(DISTINCT sacoche_user.user_id) AS eleves_nombre ';
  $sql_select.= ($groupe_id) ? '' : ', groupe_id ';
  $sql_from   = 'FROM sacoche_groupe ';
  $sql_where  = 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $sql_where .= ($groupe_id) ? 'AND groupe_id=:groupe_id ' : '' ;
  $sql_join_classe = 'LEFT JOIN sacoche_user ON sacoche_groupe.groupe_id = sacoche_user.eleve_classe_id ';
  $sql_join_groupe = 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) LEFT JOIN sacoche_user USING(user_id) ';
  $sql_join_profil = 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $sql_group_by = 'GROUP BY groupe_id ';
  // les deux requêtes
  $DB_SQL_CLASSE = $sql_select.$sql_from.$sql_join_classe.$sql_join_profil.$sql_where.'AND groupe_type=:type1 '           .$sql_group_by;
  $DB_SQL_GROUPE = $sql_select.$sql_from.$sql_join_groupe.$sql_join_profil.$sql_where.'AND groupe_type IN(:type2,:type3) '.$sql_group_by;
  // Union des deux requêtes [http://dev.mysql.com/doc/refman/5.0/fr/union.html]
  $DB_SQL = '( '.$DB_SQL_CLASSE.' ) UNION ( '.$DB_SQL_GROUPE.' )';
  $DB_VAR = array(
    ':groupe_id'   => $groupe_id,
    ':profil_type' => 'eleve',
    ':type1'       => 'classe',
    ':type2'       => 'groupe',
    ':type3'       => 'besoin',
  );
  return ($groupe_id) ? (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) : DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) ;
}

/**
 * lister_nb_saisies_par_evaluation
 *
 * @param int|string   $listing_devoir_id   soit une chaine de devoir (string), soit un devoir unique (int)
 * @return array|int   tableau (devoir_id;saisies_nombre) si pas de restriction à un unique devoir, le nb de saisies du devoir concerné sinon
 */
public static function DB_lister_nb_saisies_par_evaluation($listing_devoir_id)
{
  $is_devoir_unique = is_int($listing_devoir_id) ? TRUE : FALSE ; 
  $DB_SQL = 'SELECT COUNT(saisie_note) AS saisies_nombre ';
  $DB_SQL.= ($is_devoir_unique) ? '' : ', devoir_id ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE devoir_id IN('.$listing_devoir_id.') ' ;
  $DB_SQL.= 'AND saisie_note!="PA" AND user_sortie_date>NOW() ' ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_VAR = array( ':devoir_id' => $listing_devoir_id );
  return ($is_devoir_unique) ? (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) : DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) ;
}

/**
 * lister_liaisons_user_module
 *
 * @param int $user_id
 * @return array
 */
public static function DB_lister_liaisons_user_module($user_id)
{
  $tab_module = array();
  $DB_SQL = 'SELECT module_objet , module_url ';
  $DB_SQL.= 'FROM sacoche_jointure_user_module ';
  $DB_SQL.= 'WHERE user_id=user_id ' ;
  $DB_VAR = array( ':user_id' => $user_id );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) ;
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_module[strtoupper($DB_ROW['module_objet'])] = $DB_ROW['module_url'];
    }
  }
  return $tab_module;
}

/**
 * tester_prof_principal
 *
 * @param int $user_id
 * @param int $groupe_id   pour restreindre à une classe donnée
 * @return bool
 */
public static function DB_tester_prof_principal($prof_id,$groupe_id)
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=:pp AND groupe_type=:type ';
  $DB_SQL.= ($groupe_id) ? 'AND groupe_id=:groupe_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':user_id'   => $prof_id,
    ':pp'        => 1,
    ':type'      => 'classe',
    ':groupe_id' => $groupe_id,
  );
  return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_prof_coordonnateur
 *
 * @param int $user_id
 * @param int $matiere_id   pour restreindre à une matière donnée
 * @return bool
 */
public static function tester_prof_coordonnateur($prof_id,$matiere_id)
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'WHERE user_id=:user_id AND jointure_coord=:coord ';
  $DB_SQL.= ($matiere_id) ? 'AND matiere_id=:matiere_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':user_id'    => $prof_id,
    ':coord'      => 1,
    ':matiere_id' => $matiere_id,
  );
  return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_groupe_nom
 *
 * @param string $groupe_nom
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_groupe_nom($groupe_nom,$groupe_id=FALSE)
{
  $DB_SQL = 'SELECT groupe_id ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_nom=:groupe_nom ';
  $DB_SQL.= ($groupe_id) ? 'AND groupe_id!=:groupe_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':groupe_type' => 'besoin',
    ':groupe_nom'  => $groupe_nom,
    ':groupe_id'   => $groupe_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_devoir
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param string $date_mysql
 * @param string $info
 * @param string $date_visible_mysql
 * @param string $date_autoeval_mysql
 * @param string $doc_sujet
 * @param string $doc_corrige
 * @param string $eleves_ordre   'alpha' | 'classe'
 * @return int
 */
public static function DB_ajouter_devoir($prof_id,$groupe_id,$date_mysql,$info,$date_visible_mysql,$date_autoeval_mysql,$doc_sujet,$doc_corrige,$eleves_ordre)
{
  $DB_SQL = 'INSERT INTO sacoche_devoir( proprio_id, groupe_id, devoir_date, devoir_info, devoir_visible_date, devoir_autoeval_date, devoir_doc_sujet, devoir_doc_corrige, devoir_fini, devoir_eleves_ordre) ';
  $DB_SQL.= 'VALUES                    (:proprio_id,:groupe_id,:devoir_date,:devoir_info,:devoir_visible_date,:devoir_autoeval_date,:devoir_doc_sujet,:devoir_doc_corrige,:devoir_fini,:devoir_eleves_ordre)';
  $DB_VAR = array(
    ':proprio_id'          => $prof_id,
    ':groupe_id'           => $groupe_id,
    ':devoir_date'         => $date_mysql,
    ':devoir_info'         => $info,
    ':devoir_visible_date' => $date_visible_mysql,
    ':devoir_autoeval_date'=> $date_autoeval_mysql,
    ':devoir_doc_sujet'    => $doc_sujet,
    ':devoir_doc_corrige'  => $doc_corrige,
    ':devoir_fini'         => 0,
    ':devoir_eleves_ordre' => $eleves_ordre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_saisie
 * Si la note est un marqueur d'évaluation ("PA"), on utilise un REPLACE au lieu d'un INSERT car une saisie peut déjà exister (si le prof ajoute les demandes à un devoir existant).
 *
 * @param int    $prof_id
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $item_id
 * @param string $item_date_mysql
 * @param string $item_note
 * @param string $item_info
 * @param string $item_date_visible_mysql
 * @return void
 */
public static function DB_ajouter_saisie($prof_id,$eleve_id,$devoir_id,$item_id,$item_date_mysql,$item_note,$item_info,$item_date_visible_mysql)
{
  $commande = ($item_note!='PA') ? 'INSERT' : 'REPLACE' ;
  $DB_SQL = $commande.' INTO sacoche_saisie ';
  $DB_SQL.= 'VALUES(:prof_id,:eleve_id,:devoir_id,:item_id,:item_date,:item_note,:item_info,:item_date_visible)';
  $DB_VAR = array(
    ':prof_id'           => $prof_id,
    ':eleve_id'          => $eleve_id,
    ':devoir_id'         => $devoir_id,
    ':item_id'           => $item_id,
    ':item_date'         => $item_date_mysql,
    ':item_note'         => $item_note,
    ':item_info'         => $item_info,
    ':item_date_visible' => $item_date_visible_mysql,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_ordre_item
 *
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des items
 * @return void
 */
public static function DB_modifier_ordre_item($devoir_id,$tab_items)
{
  $DB_SQL = 'UPDATE sacoche_jointure_devoir_item ';
  $DB_SQL.= 'SET jointure_ordre=:ordre ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id=:item_id ';
  $ordre = 1;
  foreach($tab_items as $item_id)
  {
    $DB_VAR = array(
      ':devoir_id' => $devoir_id,
      ':item_id'   => $item_id,
      ':ordre'     => $ordre,
    );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $ordre++;
  }
}

/**
 * modifier_saisie
 *
 * @param int    $prof_id
 * @param int    $eleve_id
 * @param int    $devoir_id
 * @param int    $item_id
 * @param string $saisie_note
 * @param string $saisie_info
 * @return void
 */
public static function DB_modifier_saisie($prof_id,$eleve_id,$devoir_id,$item_id,$saisie_note,$saisie_info)
{
  $DB_SQL = 'UPDATE sacoche_saisie ';
  $DB_SQL.= 'SET prof_id=:prof_id, saisie_note=:saisie_note, saisie_info=:saisie_info ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id ';
  $DB_VAR = array(
    ':eleve_id'    => $eleve_id,
    ':devoir_id'   => $devoir_id,
    ':item_id'     => $item_id,
    ':prof_id'     => $prof_id,
    ':saisie_note' => $saisie_note,
    ':saisie_info' => $saisie_info,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_devoir
 * sont traités à part devoir_doc_sujet / devoir_doc_corrige / devoir_fini : voir DB_modifier_devoir_document() et DB_modifier_devoir_fini()
 *
 * @param int    $devoir_id
 * @param int    $proprio_id
 * @param string $date_mysql
 * @param string $devoir_info
 * @param string $proprietaire_archive
 * @param string $date_visible_mysql
 * @param string $date_autoeval_mysql
 * @param string $eleves_ordre   'alpha' | 'classe'
 * @return void
 */
public static function DB_modifier_devoir( $devoir_id , $proprio_id , $date_mysql , $devoir_info , $proprietaire_archive , $date_visible_mysql , $date_autoeval_mysql , $eleves_ordre )
{
  // sacoche_devoir (maj)
  $DB_SQL = 'UPDATE sacoche_devoir ';
  $DB_SQL.= 'SET devoir_date=:date, devoir_info=:devoir_info, devoir_visible_date=:visible_date, devoir_autoeval_date=:autoeval_date, devoir_eleves_ordre=:eleves_ordre ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND proprio_id=:proprio_id ';
  $DB_VAR = array(
    ':date'          => $date_mysql,
    ':devoir_info'   => $devoir_info,
    ':visible_date'  => $date_visible_mysql,
    ':autoeval_date' => $date_autoeval_mysql,
    ':eleves_ordre'  => $eleves_ordre,
    ':devoir_id'     => $devoir_id,
    ':proprio_id'    => $proprio_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // sacoche_saisie (maj)
  $saisie_info = $devoir_info.' ('.$proprietaire_archive.')';
  $DB_SQL = 'UPDATE sacoche_saisie ';
  $DB_SQL.= 'SET saisie_date=:date, saisie_info=:saisie_info, saisie_visible_date=:visible_date ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(
    ':devoir_id'    => $devoir_id,
    ':date'         => $date_mysql,
    ':saisie_info'  => $saisie_info,
    ':visible_date' => $date_visible_mysql,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_devoir_document
 *
 * @param int    $devoir_id
 * @param string $objet   'sujet' | 'corrige'
 * @param string $fichier_nom
 * @return void
 */
public static function DB_modifier_devoir_document($devoir_id,$objet,$fichier_nom)
{
  $DB_SQL = 'UPDATE sacoche_devoir ';
  $DB_SQL.= 'SET devoir_doc_'.$objet.'=:fichier_nom ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(
    ':fichier_nom' => $fichier_nom,
    ':devoir_id'   => $devoir_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_devoir_fini
 *
 * @param int    $devoir_id
 * @param string $complet   oui | non
 * @return void
 */
public static function DB_modifier_devoir_fini($devoir_id,$fini)
{
  $fini = ($fini=='oui') ? 1 : 0 ;
  $DB_SQL = 'UPDATE sacoche_devoir ';
  $DB_SQL.= 'SET devoir_fini=:fini ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(
    ':fini'      => $fini,
    ':devoir_id' => $devoir_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_devoir_item
 *
 * @param int    $devoir_id
 * @param array  $tab_items   tableau des id des items
 * @param string $mode        {creer;dupliquer} => insertion dans un nouveau devoir || {substituer} => maj avec delete / insert || {ajouter} => maj avec insert uniquement
 * @param int    $devoir_ordonne_id   Dans le cas d'une duplication, id du devoir dont il faut récupérer l'ordre des items.
 * @return void
 */
public static function DB_modifier_liaison_devoir_item($devoir_id,$tab_items,$mode,$devoir_ordonne_id=0)
{
  if( ($mode=='creer') || ($mode=='dupliquer') )
  {
    // Dans le cas d'une duplication, il faut aller rechercher l'ordre éventuel des items de l'évaluation d'origine pour ne pas le perdre
    $tab_ordre = array();
    if($devoir_ordonne_id)
    {
      $DB_SQL = 'SELECT item_id,jointure_ordre FROM sacoche_jointure_devoir_item ';
      $DB_SQL.= 'WHERE devoir_id=:devoir_id AND jointure_ordre>0 ';
      $DB_VAR = array(':devoir_id'=>$devoir_ordonne_id);
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_ordre[$DB_ROW['item_id']] = $DB_ROW['jointure_ordre'];
        }
      }
    }
    // Insertion des items
    $DB_SQL = 'INSERT INTO sacoche_jointure_devoir_item(devoir_id,item_id,jointure_ordre) ';
    $DB_SQL.= 'VALUES(:devoir_id,:item_id,:ordre)';
    foreach($tab_items as $item_id)
    {
      $ordre = (isset($tab_ordre[$item_id])) ? $tab_ordre[$item_id] : 0 ;
      $DB_VAR = array(
        ':devoir_id' => $devoir_id,
        ':item_id'   => $item_id,
        ':ordre'     => $ordre,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
  else
  {
    // On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd l'info sur l'ordre des items.
    // Alors on récupère la liste des items déjà présents, et on étudie les différences pour faire des DELETE et INSERT sélectifs
    // -> on récupère les items actuels
    $DB_SQL = 'SELECT item_id  ';
    $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
    $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
    $DB_VAR = array(':devoir_id'=>$devoir_id);
    $tab_old_items = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    // -> on supprime si besoin les anciens items associés à ce devoir qui ne sont plus dans la liste transmise
    // -> on supprime si besoin les saisies des anciens items associés à ce devoir qui ne sont plus dans la liste transmise
    //   (concernant les saisies superflues concernant les items, voir DB_modifier_liaison_devoir_item() )
    if($mode=='substituer')
    {
      $tab_items_supprimer = array_diff($tab_old_items,$tab_items);
      if(count($tab_items_supprimer))
      {
        $chaine_item_id = implode(',',$tab_items_supprimer);
        $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_item ';
        $DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id IN('.$chaine_item_id.')';
        $DB_VAR = array(':devoir_id'=>$devoir_id);
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
        // sacoche_saisie (retirer superflu concernant les items ; concernant les élèves voir DB_modifier_liaison_devoir_eleve() )
        $DB_SQL = 'DELETE FROM sacoche_saisie ';
        $DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id IN('.$chaine_item_id.')';
        $DB_VAR = array(':devoir_id'=>$devoir_id);
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
    // -> on ajoute les nouveaux items non anciennement présents
    $tab_items_ajouter = array_diff($tab_items,$tab_old_items);
    if(count($tab_items_ajouter))
    {
      foreach($tab_items_ajouter as $item_id)
      {
        $DB_SQL = 'INSERT INTO sacoche_jointure_devoir_item(devoir_id,item_id) ';
        $DB_SQL.= 'VALUES(:devoir_id,:item_id)';
        $DB_VAR = array(
          ':devoir_id' => $devoir_id,
          ':item_id'   => $item_id,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
  }
}

/**
 * modifier_liaison_devoir_prof
 *
 * @param int    $devoir_id
 * @param array  $tab_profs   tableau [id_prof->droit]
 * @param string $mode        {creer} => insertion dans un nouveau devoir || {substituer} => maj avec update / delete / insert
 * @return array   sert pour ensuite effectuer des mises à jour de notifications
 */
public static function DB_modifier_liaison_devoir_prof($devoir_id,$tab_profs,$mode)
{
  $tab_retour = array();
  if($mode=='creer')
  {
    // Insertion des droits
    $DB_SQL = 'INSERT INTO sacoche_jointure_devoir_prof(devoir_id,prof_id,jointure_droit) ';
    $DB_SQL.= 'VALUES(:devoir_id,:prof_id,:droit)';
    foreach($tab_profs as $prof_id => $droit)
    {
      $DB_VAR = array(
        ':devoir_id' => $devoir_id,
        ':prof_id'   => $prof_id,
        ':droit'     => $droit,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      $tab_retour[$prof_id] = 'insert';
    }
    return $tab_retour;
  }
  elseif($mode=='substituer')
  {
    // On récupère la liste des droits déjà présents, et on étudie les différences pour faire des REPLACE / DELETE / INSERT sélectifs
    // -> on récupère les droits actuels
    $DB_SQL = 'SELECT prof_id, jointure_droit ';
    $DB_SQL.= 'FROM sacoche_jointure_devoir_prof ';
    $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
    $DB_VAR = array(':devoir_id'=>$devoir_id);
    $tab_old_droits = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
    // -> on parcourt $tab_profs pour comparer avec ce qui est enregistré
    foreach($tab_profs as $prof_id => $droit)
    {
      if(isset($tab_old_droits[$prof_id]))
      {
        if($tab_old_droits[$prof_id]['jointure_droit']!=$droit)
        {
          // -> modification de droit
          $DB_SQL = 'UPDATE sacoche_jointure_devoir_prof ';
          $DB_SQL.= 'SET jointure_droit=:droit ';
          $DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id=:prof_id ';
          $DB_VAR = array(
            ':devoir_id' => $devoir_id,
            ':prof_id'   => $prof_id,
            ':droit'     => $droit,
          );
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
          $tab_retour[$prof_id] = 'update';
        }
        unset($tab_old_droits[$prof_id]);
      }
      else
      {
        // -> ajout de droit
        $DB_SQL = 'INSERT INTO sacoche_jointure_devoir_prof(devoir_id,prof_id,jointure_droit) ';
        $DB_SQL.= 'VALUES(:devoir_id,:prof_id,:droit)';
        $DB_VAR = array(
          ':devoir_id' => $devoir_id,
          ':prof_id'   => $prof_id,
          ':droit'     => $droit,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
        $tab_retour[$prof_id] = 'insert';
      }
    }
    // -> on observe $tab_old_droits pour rechercher ce qui reste
    if(count($tab_old_droits))
    {
      $chaine_prof_id = implode(',',array_keys($tab_old_droits));
      // -> suppression de droit
      $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_prof ';
      $DB_SQL.= 'WHERE devoir_id=:devoir_id AND prof_id IN('.$chaine_prof_id.')';
      $DB_VAR = array(':devoir_id'=>$devoir_id);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      foreach($tab_old_droits as $prof_id => $tab)
      {
        $tab_retour[$prof_id] = 'delete';
      }
    }
    return $tab_retour;
  }
}

/**
 * modifier_liaison_devoir_eleve
 * Uniquement pour les évaluations de type 'eval' ; voir DB_modifier_liaison_devoir_groupe() pour les autres
 *
 * @param int    $devoir_id
 * @param int    $groupe_id
 * @param array  $tab_eleves   tableau des id des élèves
 * @param string $mode         'creer' pour un insert dans un nouveau devoir || 'substituer' pour une maj delete / insert || 'ajouter' pour maj insert uniquement
 * @return void
 */
public static function DB_modifier_liaison_devoir_eleve($devoir_id,$groupe_id,$tab_eleves,$mode)
{
  DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_prof( $_SESSION['USER_ID'] , $groupe_id , $tab_eleves , array() /*tab_profs*/ , $mode , $devoir_id );
}

/**
 * modifier_liaison_devoir_groupe
 * Uniquement pour les évaluations sur une classe ou un groupe (pas de type 'eval')
 *
 * @param int    $devoir_id
 * @param int    $groupe_id
 * @return void
 */
public static function DB_modifier_liaison_devoir_groupe($devoir_id,$groupe_id)
{
  // -> on récupère l'id du groupe antérieurement associé au devoir
  $DB_SQL = 'SELECT groupe_id ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  if( $groupe_id != DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) )
  {
    // sacoche_devoir (maj)
    $DB_SQL = 'UPDATE sacoche_devoir ';
    $DB_SQL.= 'SET groupe_id=:groupe_id ';
    $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
    $DB_VAR = array(
      ':devoir_id' => $devoir_id,
      ':groupe_id' => $groupe_id,
    );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    // sacoche_saisie : on ne s'embête pas à essayer de voir s'il y aurait intersection entre les deux groupes, on supprime les saisies du groupe antérieur...
    $DB_SQL = 'DELETE FROM sacoche_saisie ';
    $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
    $DB_VAR = array(':devoir_id'=>$devoir_id);
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * modifier_liaison_user_module
 * insert/update ou delete selon que $module_url soit renseigné ou pas
 *
 * @param int    $user_id
 * @param string $module_objet
 * @param string $module_url
 * @return void
 */
public static function DB_modifier_liaison_user_module( $user_id , $module_objet , $module_url )
{
  if($module_url)
  {
    $DB_SQL = 'INSERT INTO sacoche_jointure_user_module( user_id, module_objet, module_url) ';
    $DB_SQL.= 'VALUES                                  (:user_id,:module_objet,:module_url) ';
    $DB_SQL.= 'ON DUPLICATE KEY UPDATE module_url=:module_url ';
  }
  else
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_user_module ';
    $DB_SQL.= 'WHERE user_id=:user_id AND module_objet=:module_objet ';
  }
  $DB_VAR = array(
    ':user_id'      => $user_id,
    ':module_objet' => $module_objet,
    ':module_url'   => $module_url,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_devoir_et_saisies
 *
 * @param int   $devoir_id
 * @return void
 */
public static function DB_supprimer_devoir_et_saisies($devoir_id)
{
  // Il faut aussi supprimer les jointures du devoir avec les items, les profs, les élèves
  $DB_SQL = 'DELETE sacoche_devoir, sacoche_saisie, sacoche_jointure_devoir_item, sacoche_jointure_devoir_prof, sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item  USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof  USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_eleve USING (devoir_id) ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_saisie
 *
 * @param int   $eleve_id
 * @param int   $devoir_id
 * @param int   $item_id
 * @return void
 */
public static function DB_supprimer_saisie($eleve_id,$devoir_id,$item_id)
{
  $DB_SQL = 'DELETE FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id ';
  $DB_VAR = array(
    ':eleve_id'  => $eleve_id,
    ':devoir_id' => $devoir_id,
    ':item_id'   => $item_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_liaison_devoir_prof
 *
 * @param int   $devoir_id
 * @return void
 */
public static function DB_supprimer_liaison_devoir_prof($devoir_id)
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_prof ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>