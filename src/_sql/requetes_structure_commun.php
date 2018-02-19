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
// Ces méthodes ne concernent que les requêtes communes à plusieurs profils et inclasssables facilement...

class DB_STRUCTURE_COMMUN extends DB
{

/**
 * Exécuter des requêtes MySQL
 *
 * Utilisé dans le cadre d'une restauration de sauvegarde
 *
 * @param string $requetes
 * @return void
 */
public static function DB_executer_requetes_MySQL($requetes)
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
}

/**
 * Analyser les tables
 * @see http://dev.mysql.com/doc/refman/5.0/fr/check-table.html
 *
 * @param string $listing_tables
 * @return void
 */
public static function DB_analyser_tables($listing_tables)
{
  $DB_SQL = 'CHECK TABLE '.$listing_tables;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Réparer une table
 * @see http://dev.mysql.com/doc/refman/5.0/fr/repair-table.html
 *
 * @param string $table
 * @return void
 */
public static function DB_reparer_table($table)
{
  $DB_SQL = 'REPAIR TABLE '.$table;
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer les informations concernant les tables présentes dans la base
 * @see http://dev.mysql.com/doc/refman/5.0/fr/show-table-status.html
 *
 * Retourne une ligne par table, avec pour chacune les champs Engine / Version / Row_format / Rows / Avg_row_length / Data_length / Max_data_length / Index_length / Data_free / Auto_increment / Create_time / Update_time / Check_time / Collation / Checksum / Create_options / Comment
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_tables_informations()
{
  $DB_SQL = 'SHOW TABLE STATUS ';
  $DB_SQL.= 'LIKE "sacoche_%" ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la commande MySQL pour créer une table existante
 *
 * Retourne un tableau a deux entrées : "Table" (le nom de la table) et "Create Table" (la commande MySQL).
 *
 * @param string $table_nom
 * @return array
 */
public static function DB_recuperer_table_structure($table_nom)
{
  $DB_SQL = 'SHOW CREATE TABLE '.$table_nom;
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer n lignes d'une table
 *
 * @param string $table_nom
 * @param int    $limit_depart
 * @param int    $limit_nombre
 * @return array
 */
public static function DB_recuperer_table_donnees( $table_nom , $limit_depart , $limit_nombre )
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM '.$table_nom.' ';
  $DB_SQL.= 'LIMIT '.$limit_depart.','.$limit_nombre;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la valeur d'une variable système de MySQL
 *
 * Retourne un tableau a deux entrées : "Variable_name" (le nom de la variable) et "Value" (sa valeur).
 *
 * @param string   $variable_nom   max_allowed_packet | max_user_connections | group_concat_max_len
 * @return array
 */
public static function DB_recuperer_variable_MySQL($variable_nom)
{
  $DB_SQL = 'SHOW VARIABLES LIKE "'.$variable_nom.'"';
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la version de MySQL
 *
 * Avec une connexion classique style mysql_connect() on peut utiliser mysql_get_server_info() .
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_version_MySQL()
{
  $DB_SQL = 'SELECT VERSION()';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer le mode SQL
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_mode_SQL()
{
  $DB_SQL = 'SELECT @@sql_mode';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_devoirs_annees_scolaires_precedentes
 *
 * @param void
 * @return int
 */
public static function DB_compter_devoirs_annees_scolaires_precedentes()
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'WHERE devoir_date<:devoir_date ';
  $DB_VAR = array( ':devoir_date' => To::jour_debut_annee_scolaire('mysql') );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_dates_periode
 *
 * @param int    $groupe_id    id du groupe
 * @param int    $periode_id   id de la période
 * @return array
 */
public static function DB_recuperer_dates_periode( $groupe_id , $periode_id )
{
  $DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
  $DB_VAR = array(
    ':groupe_id'  => $groupe_id,
    ':periode_id' => $periode_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner l'arborescence d'un référentiel (tableau issu de la requête SQL)
 * + pour une matière donnée / pour toutes les matières d'un professeur donné
 * + pour un niveau donné / pour tous les niveaux concernés
 *
 * @param int  $prof_id      passer 0 pour une recherche sur toutes les matières de l'établissement (profil directeur) plutôt que d'un prof donné
 * @param int  $matiere_id   passer 0 pour une recherche sur toutes les matières d'un prof plutôt que sur une matière
 * @param int  $niveau_id    passer 0 pour une recherche sur tous les niveaux
 * @param bool $only_socle   "TRUE" pour ne retourner que les items reliés au socle (TODO : ne tester à terme que le socle 2016)
 * @param bool $only_item    "TRUE" pour ne retourner que les lignes d'items, "FALSE" pour l'arborescence complète, sans forcément descendre jusqu'à l'items (valeurs NULL retournées)
 * @param bool $s2016_count  avec ou pas le nb de liaisons au socle 2016
 * @param bool $item_comm    avec ou pas les commentaires associés aux items
 * @return array
 */
public static function DB_recuperer_arborescence( $prof_id , $matiere_id , $niveau_id , $only_socle , $only_item , $s2016_count , $item_comm )
{
  // Depuis MySQL 5.7.5 la directive ONLY_FULL_GROUP_BY est activée ce qui plantait la requête ci-dessous
  // (SELECT list is not in GROUP BY clause and contains nonaggregated column 'sacoche.sacoche_referentiel.matiere_id' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by)
  // à moins d'inverser l'ordre de parcours des tables (partir de sacoche_referentiel_item) ce qui ne me semble pas cool au vu du WHERE
  // ou de remplacer les LEFT JOIN par des INNER JOIN jusqu'à atteindre sacoche_referentiel_item
  // (INNER JOIN n'est cependant pas généralisé car dans certains cas on souhaite pouvoir récupérer des arborescences incomplètes)
  $type_join         = ($s2016_count) ? 'INNER JOIN' : 'LEFT JOIN' ;
  $select_item_comm  = ($item_comm)   ? 'item_comm, ' : '' ;
  $select_s2016_nb   = ($s2016_count) ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS s2016_nb, ' : '' ;
  $join_user_matiere = ($prof_id)     ? 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ' : '' ;
  $join_s2016        = ($s2016_count) ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_user        = ($prof_id)     ? 'AND user_id=:user_id ' : '' ;
  $where_matiere     = ($matiere_id)  ? 'AND matiere_id=:matiere_id ' : '' ;
  $where_niveau      = ($niveau_id)   ? 'AND niveau_id=:niveau_id ' : 'AND niveau_actif=1 ' ;
  $where_item        = ($only_item)   ? 'AND item_id IS NOT NULL ' : '' ;
  $where_socle       = ($only_socle)  ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  $group_s2016       = ($s2016_count) ? 'GROUP BY sacoche_referentiel_item.item_id ' : '' ;
  $order_matiere     = (!$matiere_id) ? 'matiere_nom ASC, '  : '' ;
  $order_niveau      = (!$niveau_id)  ? 'niveau_ordre ASC, ' : '' ;
  $DB_SQL = 'SELECT ';
  $DB_SQL.= 'matiere_id, matiere_ref, matiere_nom, ';
  $DB_SQL.= 'niveau_id, niveau_ref, niveau_nom, ';
  $DB_SQL.= 'domaine_id, domaine_ordre, domaine_code, domaine_ref, domaine_nom, ';
  $DB_SQL.= 'theme_id, theme_ordre, theme_ref, theme_nom, ';
  $DB_SQL.= $select_item_comm.$select_s2016_nb;
  $DB_SQL.= 'item_id, item_ordre, item_ref, item_nom, item_abrev, item_coef, item_cart, item_lien ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= $join_user_matiere;
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= $type_join.' sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= $type_join.' sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= $type_join.' sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE matiere_active=1 '.$where_user.$where_matiere.$where_niveau.$where_item.$where_socle;
  $DB_SQL.= $group_s2016;
  $DB_SQL.= 'ORDER BY '.$order_matiere.$order_niveau.'domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':user_id'    => $prof_id,
    ':matiere_id' => $matiere_id,
    ':niveau_id'  => $niveau_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner un tableau [valeur texte optgroup] de l'arborescence d'un référentiel, pour une matière donnée et pour un niveau donné
 *
 * @param int  $matiere_id
 * @param int  $niveau_id
 * @return array
 */
public static function DB_OPT_arborescence( $matiere_id , $niveau_id )
{
  $longueur_max = 125;
  $DB_SQL = 'SELECT item_id AS valeur, item_nom AS texte, CONCAT(domaine_id,"_",theme_id) AS optgroup, CONCAT(domaine_nom," || ",theme_nom) AS optgroup_info ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
  $DB_SQL.= 'ORDER BY domaine_ordre ASC, theme_ordre ASC, item_ordre ASC ';
  $DB_VAR = array(
    ':matiere_id' => $matiere_id,
    ':niveau_id'  => $niveau_id,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $tab_optgroup = array();
  foreach($DB_TAB as $key => $DB_ROW)
  {
    if(mb_strlen($DB_TAB[$key]['texte'])>$longueur_max)
    {
      $DB_TAB[$key]['texte'] = mb_substr($DB_TAB[$key]['texte'],0,$longueur_max-5).' [...]';
    }
    if(mb_strlen($DB_TAB[$key]['optgroup'])>$longueur_max)
    {
      $DB_TAB[$key]['optgroup'] = mb_substr($DB_TAB[$key]['optgroup'],0,$longueur_max-5).' [...]';
    }
    $tab_optgroup[$DB_ROW['optgroup']] = $DB_ROW['optgroup_info'];
    unset($DB_TAB[$key]['optgroup_info']);
  }
  Form::$tab_select_optgroup['referentiel'] = $tab_optgroup;
  return !empty($DB_TAB) ? $DB_TAB : 'Ce référentiel ne comporte aucun item !' ;
}

/**
 * recuperer_socle2016_cycles
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_socle2016_cycles()
{
  $DB_SQL = 'SELECT socle_cycle_id, socle_cycle_nom, socle_cycle_description ';
  $DB_SQL.= 'FROM sacoche_socle_cycle ';
  $DB_SQL.= 'ORDER BY socle_cycle_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_socle2016_elements_livret
 * Lister les codes et intitulés des domaines ou composantes
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_socle2016_elements_livret()
{
  $DB_SQL = 'SELECT socle_domaine_ordre_livret, socle_domaine_code_livret, socle_domaine_nom_simple, socle_domaine_nom_officiel, ';
  $DB_SQL.= 'socle_composante_ordre_livret, socle_composante_code_livret, socle_composante_nom_simple, socle_composante_nom_officiel ';
  $DB_SQL.= 'FROM sacoche_socle_domaine ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_domaine_id) ';
  $DB_SQL.= 'ORDER BY socle_domaine_ordre ASC, socle_composante_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_socle2016_arborescence
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_socle2016_arborescence()
{
  $DB_SQL = 'SELECT socle_domaine_id, socle_domaine_ordre, socle_domaine_nom_simple, socle_composante_id, socle_composante_nom_simple ';
  $DB_SQL.= 'FROM sacoche_socle_domaine ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_domaine_id) ';
  $DB_SQL.= 'ORDER BY socle_domaine_ordre ASC, socle_composante_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_groupe_nom
 *
 * @param int   $groupe_id
 * @return string
 */
public static function DB_recuperer_groupe_nom($groupe_id)
{
  $DB_SQL = 'SELECT groupe_nom ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(':groupe_id'=>$groupe_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_matieres_etabl
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_matieres_etabl()
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT(matiere_id SEPARATOR ",") AS listing_matieres_id ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_active=1 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_matieres_professeur
 *
 * @param int $user_id
 * @return string
 */
public static function DB_recuperer_matieres_professeur($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT(matiere_id SEPARATOR ",") AS listing_matieres_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 ';
  $DB_VAR = array(':user_id'=>$user_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les tables de SACoche en base de données
 * @see http://dev.mysql.com/doc/refman/5.0/fr/show-tables.html
 *
 * @param void
 * @return array
 */
public static function DB_lister_tables()
{
  $DB_SQL = 'SHOW TABLES ';
  $DB_SQL.= 'LIKE "sacoche_%" ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * lister_identite_coordonnateurs_par_matiere
 *
 * @param void
 * @return array   matiere_id et coord_liste avec identités séparées par "]["
 */
public static function DB_lister_identite_coordonnateurs_par_matiere()
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT matiere_id, GROUP_CONCAT(CONCAT(user_nom," ",user_prenom) SEPARATOR "][") AS coord_liste ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE matiere_active=1 AND jointure_coord=:coord AND user_sortie_date>NOW() '; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
  $DB_SQL.= 'GROUP BY matiere_id';
  $DB_VAR = array(':coord'=>1);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_regroupement
 *
 * @param string $profil_type   eleve | parent | professeur | personnel | directeur | administrateur
 * @param int    $statut        1 pour actuels, 0 pour anciens, 2 pour tout le monde
 * @param string $groupe_type   all | sdf | niveau | classe | groupe | besoin
 * @param int    $groupe_id     id du niveau ou de la classe ou du groupe
 * @param string $eleves_ordre  valeur parmi [alpha] [classe]
 * @param string $champs        par défaut user_id,user_nom,user_prenom
 * @param int    $periode_id    id de la période dans le cas où on récupère tous les élèves ayant une évaluation sur la période (0 pour le cycle)
 * @return array
 */
public static function DB_lister_users_regroupement( $profil_type , $statut , $groupe_type , $groupe_id , $eleves_ordre , $champs='user_id,user_nom,user_prenom' , $periode_id=NULL )
{
  $as      = ($profil_type!='parent') ? '' : ' AS enfant' ;
  $prefixe = ($profil_type!='parent') ? 'sacoche_user.' : 'enfant.' ;
  $test_date_sortie = ($statut==1) ? 'AND '.$prefixe.'user_sortie_date>NOW()' : ( ($statut==0) ? 'AND '.$prefixe.'user_sortie_date<NOW()' : '' ) ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $from  = 'FROM sacoche_user'.$as.' ' ; // Peut être modifié ensuite (requête optimisée si on commence par une autre table)
  $ljoin = '';
  $where = 'WHERE sacoche_user_profil.user_profil_type=:profil_type '.$test_date_sortie.' ';
  $group = ($profil_type!='parent') ? 'GROUP BY user_id ' : 'GROUP BY parent.user_id, enfant.user_id ' ;
  $order = 'ORDER BY '.$prefixe.'user_nom ASC, '.$prefixe.'user_prenom ASC'; // Peut être modifié ensuite (si besoin de tri des élèves par classe d'origine)
  if(in_array($profil_type,array('directeur','administrateur')))
  {
    $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  }
  else
  {
    switch ($groupe_type)
    {
      case 'all' :  // On veut tous les users de l'établissement
        $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
        switch ($profil_type)
        {
          case 'professeur' :
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'sdf' :  // On veut les users non affectés dans une classe (élèves seulement)
        $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
        $where .= 'AND '.$prefixe.'eleve_classe_id=:classe ';
        break;
      case 'niveau' :  // On veut tous les users d'un niveau
        switch ($profil_type)
        {
          case 'eleve' :
          case 'parent' :
            $from   = 'FROM sacoche_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user'.$as.' ON sacoche_groupe.groupe_id='.$prefixe.'eleve_classe_id ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil ON '.$prefixe.'user_profil_sigle=sacoche_user_profil.user_profil_sigle ';
            $where .= 'AND niveau_id=:niveau ';
            break;
          case 'professeur' :
            $from   = 'FROM sacoche_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND niveau_id=:niveau ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'classe' :  // On veut tous les users d'une classe
        switch ($profil_type)
        {
          case 'eleve' :
          case 'parent' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND '.$prefixe.'eleve_classe_id=:groupe ';
            break;
          case 'professeur' :
            $from   = 'FROM sacoche_jointure_user_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND groupe_id=:groupe ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'groupe' :  // On veut tous les users d'un groupe
      case 'besoin' :  // On veut tous les users d'un groupe de besoin (élèves | parents seulements)
      case 'eval'   :  // On veut tous les users d'un groupe utilisé pour une évaluation (élèves seulements)
        switch ($profil_type)
        {
          case 'eleve' :
          case 'parent' :
          case 'professeur' :
            $from   = 'FROM sacoche_jointure_user_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user'.$as.' USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil ON '.$prefixe.'user_profil_sigle=sacoche_user_profil.user_profil_sigle ';
            $where .= 'AND sacoche_jointure_user_groupe.groupe_id=:groupe ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            if( ($profil_type=='eleve') && ($eleves_ordre=='classe') )
            {
              $ljoin .= 'LEFT JOIN sacoche_groupe AS classe_origine ON sacoche_user.eleve_classe_id=classe_origine.groupe_id ';
              $ljoin .= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
              $order = 'ORDER BY niveau_ordre ASC, classe_origine.groupe_nom ASC, '.$prefixe.'user_nom ASC, '.$prefixe.'user_prenom ASC';
            }
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
    }
    if( ($statut==2) && ($profil_type=='eleve') && in_array($groupe_type,array('classe','groupe')) && !is_null($periode_id) )
    {
      // On restreint aux élèves ayant été évalués ! Pour un bilan de fin de cycle on considère comme période l'année scolaire.
      $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
      $DB_ROW = ($periode_id) ? DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id)
                              : array( 'jointure_date_debut' => To::jour_debut_annee_scolaire('mysql',$annee_decalage) , 'jointure_date_fin' => To::jour_fin_annee_scolaire('mysql') ) ;
      if(!empty($DB_ROW))
      {
        $date_mysql_debut = $DB_ROW['jointure_date_debut'];
        $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
        $ljoin .= 'LEFT JOIN sacoche_saisie ON sacoche_user.user_id=sacoche_saisie.eleve_id ';
        $where .= 'AND saisie_date>="'.$date_mysql_debut.'" AND saisie_date<="'.$date_mysql_fin.'" ';
      }
    }
  }
  if($profil_type=='parent')
  {
    // INNER JOIN pour obliger une jointure avec un parent
    $ljoin .= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
    $ljoin .= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
    $where .= 'AND parent.user_sortie_date>NOW() ';
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = 'SELECT '.$champs.' '.$from.$ljoin.$where.$group.$order;
  $DB_VAR = array(
    ':profil_type'  => str_replace( array('parent','personnel') , array('eleve','professeur') , $profil_type ),
    ':join_groupes' => 'config',
    ':groupe'       => $groupe_id,
    ':niveau'       => $groupe_id,
    ':classe'       => 0,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleves_classe_et_groupe
 * Utilisé dans le cas particulier des bilans officiels
 *
 * @param int   $classe_id
 * @param int   $groupe_id
 * @param int   $statut        1 pour actuels, 0 pour anciens, 2 pour tout le monde
 * @param int   $periode_id    id de la période dans le cas où on récupère tous les élèves ayant une évaluation sur la période (0 pour le cycle)
 * @return array
 */
public static function DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , $statut , $periode_id=NULL )
{
  $join_saisie = $where_sortie_date = $where_saisie_date = $groupby = '' ;
  if($statut==1)
  {
    $where_sortie_date = 'AND user_sortie_date>NOW() '; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  }
  else if($statut==0)
  {
    $where_sortie_date = 'AND user_sortie_date<NOW() '; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  }
  else if( ($statut==2) && !is_null($periode_id) )
  {
    // On restreint aux élèves ayant été évalués ! Pour un bilan de fin de cycle on considère comme période l'année scolaire.
    $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
    $DB_ROW = ($periode_id) ? DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id)
                            : array( 'jointure_date_debut' => To::jour_debut_annee_scolaire('mysql',$annee_decalage) , 'jointure_date_fin' => To::jour_fin_annee_scolaire('mysql') ) ;
    if(!empty($DB_ROW))
    {
      $date_mysql_debut = $DB_ROW['jointure_date_debut'];
      $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
      $join_saisie = 'LEFT JOIN sacoche_saisie ON sacoche_user.user_id=sacoche_saisie.eleve_id ';
      $where_saisie_date = 'AND saisie_date>="'.$date_mysql_debut.'" AND saisie_date<="'.$date_mysql_fin.'" ';
      $groupby = 'GROUP BY user_id ';
    }
  }
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= $join_saisie;
  $DB_SQL.= 'WHERE groupe_id=:groupe AND eleve_classe_id=:classe AND user_profil_type=:profil_type ';
  $DB_SQL.= $where_sortie_date.$where_saisie_date;
  $DB_SQL.= $groupby;
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':groupe'      => $groupe_id,
    ':classe'      => $classe_id,
    ':profil_type' => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_referentiels_infos_details_matieres_niveaux
 *
 * @param int    $matiere_id   0 par défaut pour toutes les matières
 * @param int    $niveau_id    0 par défaut pour tous les niveaux
 * @return array
 */
public static function DB_lister_referentiels_infos_details_matieres_niveaux( $matiere_id=0 , $niveau_id=0 )
{
  $DB_SQL = 'SELECT matiere_id, niveau_id, niveau_nom, referentiel_partage_etat, referentiel_partage_date, referentiel_calcul_methode, referentiel_calcul_limite, referentiel_calcul_retroactif, referentiel_information ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= ($matiere_id) ? 'WHERE matiere_id='.$matiere_id.' ' : 'WHERE matiere_active=1 ' ; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
  $DB_SQL.= ($niveau_id)  ? 'AND niveau_id='.$niveau_id.' '     : 'AND niveau_actif=1 ' ;
  $DB_SQL.= 'ORDER BY matiere_id ASC, niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_dates_saisies_items
 * Retourner les dates de saisies de notes pour des items donnés (on ne restreint pas aux élèves au compte actif à cause des sortants du niveau le plus élevé).
 * Groupement par date car une évaluation couvre des saisies d'un même item pour plusieurs élèves => ça fait 30 fois moins de lignes retournées et on évite des erreurs 500 pour dépassement de mémoire.
 *
 * @param string   $liste_item_id   id des items séparés par des virgules
 * @return array
 */
public static function DB_lister_dates_saisies_items($liste_item_id)
{
  $DB_SQL = 'SELECT item_id , saisie_date AS date , COUNT(saisie_note) AS nombre ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE item_id IN('.$liste_item_id.') ';
  $DB_SQL.= 'GROUP BY item_id , saisie_date ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_utilisateur
 *
 * @param int         $user_sconet_id
 * @param int         $user_sconet_elenoet
 * @param string      $user_reference
 * @param string      $user_profil_sigle
 * @param string      $user_genre
 * @param string      $user_nom
 * @param string      $user_prenom
 * @param string|NULL $user_naissance_date
 * @param string      $user_email
 * @param string      $user_email_origine
 * @param string      $user_login
 * @param string      $password_crypte
 * @param string      $user_id_ent  facultatif
 * @param string      $user_id_gepi facultatif
 * @param int         $eleve_classe_id   facultatif, 0 si pas de classe ou profil non élève
 * @param string      $eleve_uai_origine facultatif, '' sinon
 * @param int         $eleve_lv1         facultatif, 100 si pas de LV ou profil non élève
 * @param int         $eleve_lv2         facultatif, 100 si pas de LV ou profil non élève
 * @return int
 */
public static function DB_ajouter_utilisateur( $user_sconet_id , $user_sconet_elenoet , $user_reference , $user_profil_sigle , $user_genre , $user_nom , $user_prenom , $user_naissance_date , $user_email , $user_email_origine , $user_login , $password_crypte , $user_id_ent='' , $user_id_gepi='' , $eleve_classe_id=0 , $eleve_uai_origine='' , $eleve_lv1=100 , $eleve_lv2=100 )
{
  $DB_SQL = 'INSERT INTO sacoche_user(user_sconet_id, user_sconet_elenoet, user_reference, user_profil_sigle, user_genre, user_nom, user_prenom, user_naissance_date, user_email, user_email_origine, user_login, user_password,   eleve_classe_id, eleve_lv1, eleve_lv2, eleve_uai_origine, user_id_ent, user_id_gepi, user_param_menu, user_param_favori) ';
  $DB_SQL.= 'VALUES(                 :user_sconet_id,:user_sconet_elenoet,:user_reference,:user_profil_sigle,:user_genre,:user_nom,:user_prenom,:user_naissance_date,:user_email,:user_email_origine,:user_login,:password_crypte,:eleve_classe_id,:eleve_lv1,:eleve_lv2,:eleve_uai_origine,:user_id_ent,:user_id_gepi,:user_param_menu,:user_param_favori)';
  $DB_VAR = array(
    ':user_sconet_id'      => $user_sconet_id,
    ':user_sconet_elenoet' => $user_sconet_elenoet,
    ':user_reference'      => $user_reference,
    ':user_profil_sigle'   => $user_profil_sigle,
    ':user_genre'          => $user_genre,
    ':user_nom'            => $user_nom,
    ':user_prenom'         => $user_prenom,
    ':user_naissance_date' => $user_naissance_date,
    ':user_email'          => $user_email,
    ':user_email_origine'  => $user_email_origine,
    ':user_login'          => $user_login,
    ':password_crypte'     => $password_crypte,
    ':eleve_classe_id'     => $eleve_classe_id,
    ':eleve_lv1'           => $eleve_lv1,
    ':eleve_lv2'           => $eleve_lv2,
    ':eleve_uai_origine'   => $eleve_uai_origine,
    ':user_id_ent'         => $user_id_ent,
    ':user_id_gepi'        => $user_id_gepi,
    ':user_param_menu'     => NULL, // pas de valeur par défaut possible
    ':user_param_favori'   => NULL, // pas de valeur par défaut possible
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier un de ses paramètres utilisateurs (adresse e-mail, langue, daltonisme, configuration page d'accueil).
 * La modification du mdp est gérée par une autre fonction
 *
 * @param int    $user_id
 * @param string $champ_nom
 * @param mixed  $champ_val
 * @return void
 */
public static function DB_modifier_user_parametre( $user_id , $champ_nom , $champ_val )
{
  $user_email_origine = ($champ_val) ? 'user' : '' ;
  $set_email_origine  = ($champ_nom=='user_email') ? ', user_email_origine=:mail_origine ' : '' ;
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET '.$champ_nom.'=:champ_val '.$set_email_origine;
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(
    ':user_id'      => $user_id,
    ':champ_val'    => $champ_val,
    ':mail_origine' => $user_email_origine,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_mdp_utilisateur
 *
 * @param int    $user_id
 * @param string $password_ancien_crypte
 * @param string $password_nouveau_crypte
 * @return bool   TRUE si ok | FALSE si le mot de passe actuel est incorrect.
 */
public static function DB_modifier_mdp_utilisateur( $user_id , $password_ancien_crypte , $password_nouveau_crypte )
{
  // Tester si l'ancien mot de passe correspond à celui enregistré
  $DB_SQL = 'SELECT user_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:user_id AND user_password=:password_crypte ';
  $DB_VAR = array(
    ':user_id'         => $user_id,
    ':password_crypte' => $password_ancien_crypte,
  );
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(empty($DB_ROW))
  {
    return FALSE;
  }
  // Remplacer par le nouveau mot de passe
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_password=:password_crypte ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(
    ':user_id'         => $user_id,
    ':password_crypte' => $password_nouveau_crypte,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return TRUE;
}

/**
 * Créer les tables de la base d'une structure et les remplir
 *
 * @param void
 * @return void
 */
public static function DB_creer_remplir_tables_structure()
{
  $tab_files = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_SQL_STRUCTURE);
  foreach($tab_files as $file)
  {
    $extension = pathinfo($file,PATHINFO_EXTENSION);
    if($extension=='sql')
    {
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.$file);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      /*
      La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
      La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
      */
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

/**
 * Retourner un tableau [valeur texte] des matières de l'établissement (communes choisies ou spécifiques ajoutées)
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_matieres_etabl()
{
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune matière n\'est rattachée à l\'établissement.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des familles de matières
 *
 * @param void
 * @return array
 */
public static function DB_OPT_familles_matieres()
{
  $DB_SQL = 'SELECT matiere_famille_id AS valeur, matiere_famille_nom AS texte, matiere_famille_categorie AS optgroup ';
  $DB_SQL.= 'FROM sacoche_matiere_famille ';
  $DB_SQL.= 'ORDER BY matiere_famille_categorie ASC, matiere_famille_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des matières communes d'une famille donnée
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int   matiere_famille_id
 * @return array
 */
public static function DB_OPT_matieres_famille($matiere_famille_id)
{
  Form::$tab_select_option_first['matieres_famille'] = array( ID_MATIERE_PARTAGEE_MAX+$matiere_famille_id , 'Toutes les matières de cette famille' );
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= ($matiere_famille_id==ID_FAMILLE_MATIERE_USUELLE) ? 'WHERE matiere_usuelle=1 ' : 'WHERE matiere_famille_id='.$matiere_famille_id.' ' ;
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte info] des matières du professeur identifié ; info représente le nb de demandes (utilisé par ailleurs)
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_matieres_professeur($user_id)
{
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte, matiere_nb_demandes AS info ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':user_id'=>$user_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'êtes rattaché à aucune matière.' ;
}

/**
 * Retourner un tableau [valeur texte info] des matières d'un élève identifié ; info représente le nb de demandes (utilisé par ailleurs)
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_matieres_eleve($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($_SESSION['ELEVE_CLASSE_ID']), donc on commence par récupérer les groupes éventuels associés à l'élève
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type2 ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(
    ':user_id' => $user_id,
    ':type2'   => 'groupe',
  );
  $liste_groupes_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( (!$_SESSION['ELEVE_CLASSE_ID']) && (!$liste_groupes_id) )
  {
    // élève sans classe et sans groupe
    return 'Aucune classe ni aucun groupe ne vous est affecté !';
  }
  if(!$liste_groupes_id)
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'];
  }
  elseif(!$_SESSION['ELEVE_CLASSE_ID'])
  {
    $liste_groupes = $liste_groupes_id;
  }
  else
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'].','.$liste_groupes_id;
  }
  // Ensuite on récupère les matières des professeurs (actuels !) qui sont associés à la liste des groupes récupérés
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte, matiere_nb_demandes AS info ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() AND matiere_active=1 ';
  $DB_SQL.= 'GROUP BY matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':partage'=>0);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'avez pas de professeur rattaché à une matière !' ;
}

/**
 * Retourner un tableau [valeur texte] des matières d'une classe ou d'un groupe
 *
 * @param int $groupe_id     id de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_matieres_groupe($groupe_id)
{
  // On récupère les matières des professeurs qui sont associés au groupe
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_profil_type=:profil_type AND matiere_id IS NOT NULL ';
  $DB_SQL.= 'GROUP BY matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(
    ':groupe_id'   => $groupe_id,
    ':profil_type' => 'professeur',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur du groupe est rattaché à une matière.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des familles de niveaux
 *
 * @param void
 * @return array
 */
public static function DB_OPT_familles_niveaux()
{
  $DB_SQL = 'SELECT niveau_famille_id AS valeur, niveau_famille_nom AS texte, niveau_famille_categorie AS optgroup ';
  $DB_SQL.= 'FROM sacoche_niveau_famille ';
  $DB_SQL.= 'ORDER BY niveau_famille_categorie ASC, niveau_famille_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux de l'établissement
 *
 * @param void
 * @return array
 */
public static function DB_OPT_niveaux_etabl()
{
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'WHERE niveau_actif=1 ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux (choisis ou pas par l'établissement)
 *
 * @param void
 * @return array
 */
public static function DB_OPT_niveaux()
{
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux d'une famille donnée
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int   niveau_famille_id
 * @return array
 */
public static function DB_OPT_niveaux_famille($niveau_famille_id)
{
  Form::$tab_select_option_first['niveaux_famille'] = array(ID_NIVEAU_PARTAGE_MAX+$niveau_famille_id,'Tous les niveaux de cette famille');
  // Ajouter, si pertinent, les niveaux spécifiques qui sinon ne sont pas trouvés car à part...
  // Attention en cas de modification : ce tableau est dans 3 fichiers différents (dépôt SACoche x2 + dépôt portail x1).
  $tab_sql = array(
      1 => '',
      2 => '',
      3 => '',
      4 => '',
     60 => 'OR niveau_id IN(1,2,3,201) ',
    100 => 'OR niveau_id IN(3,4,10,202,203) ',
    160 => 'OR niveau_id IN(16,202,203) ',
    200 => 'OR niveau_id IN(20,204,205,206) ',
    210 => 'OR niveau_id IN(20,204,205,206) ',
    220 => 'OR niveau_id = 23 ',
    240 => 'OR niveau_id = 24 ',
    241 => 'OR niveau_id = 24 ',
    242 => 'OR niveau_id = 24 ',
    243 => 'OR niveau_id = 25 ',
    247 => 'OR niveau_id = 26 ',
    250 => 'OR niveau_id = 27 ',
    251 => 'OR niveau_id = 27 ',
    253 => '',
    254 => 'OR niveau_id = 28 ',
    271 => 'OR niveau_id = 29 ',
    276 => 'OR niveau_id = 30 ',
    290 => '',
    301 => 'OR niveau_id = 31 ',
    310 => 'OR niveau_id = 32 ',
    311 => 'OR niveau_id = 32 ',
    312 => 'OR niveau_id = 32 ',
    313 => '',
    315 => 'OR niveau_id = 33 ',
    316 => 'OR niveau_id = 33 ',
    350 => 'OR niveau_id = 35 ',
    370 => 'OR niveau_id = 37 ',
    371 => 'OR niveau_id = 37 ',
    390 => '',
    740 => '',
  );
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($niveau_famille_id==ID_FAMILLE_NIVEAU_USUEL) ? 'WHERE niveau_usuel=1 ' : 'WHERE niveau_famille_id='.$niveau_famille_id.' '.$tab_sql[$niveau_famille_id] ;
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':niveau_famille_id'=>$niveau_famille_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner un tableau [valeur texte] des niveaux des référentiels d'une matière
 *
 * @param int $matiere_id
 * @return array|string
 */
public static function DB_OPT_niveaux_matiere($matiere_id)
{
  // On récupère les matières des professeurs qui sont associés au groupe
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun référentiel est rattaché à cette matière.' ;
}

/**
 * Retourner un tableau [valeur texte] des cycles du socle 2016
 *
 * @param bool   $only_used   TRUE pour restreindre à ceux auxquels au moins un item est relié
 * @return array|string
 */
public static function DB_OPT_socle2016_cycles($only_used=FALSE)
{
  if($only_used)
  {
    $join  = 'LEFT JOIN sacoche_jointure_referentiel_socle USING(socle_cycle_id) ';
    $where = 'WHERE item_id IS NOT NULL ';
    $group = 'GROUP BY socle_cycle_id ';
  }
  else
  {
    $join  = $where =  $group = '' ;
  }
  $DB_SQL = 'SELECT socle_cycle_id AS valeur, socle_cycle_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_socle_cycle ';
  $DB_SQL.= $join;
  $DB_SQL.= $where;
  $DB_SQL.= $group;
  $DB_SQL.= 'ORDER BY socle_cycle_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun item de référentiel n\'est relié au nouveau socle commun.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des domaines du socle 2016 pour les référentiels en place dans l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_socle2016_domaines()
{
  $DB_SQL = 'SELECT CONCAT(socle_cycle_id,"_",socle_domaine_id) AS valeur, CONCAT(socle_cycle_nom," - Domaine ",socle_domaine_ordre," : ",socle_domaine_nom_simple) AS texte, socle_cycle_id AS optgroup, socle_cycle_nom AS optgroup_info ';
  $DB_SQL.= 'FROM sacoche_jointure_referentiel_socle ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_cycle USING(socle_cycle_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_composante_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_domaine USING (socle_domaine_id) ';
  $DB_SQL.= 'GROUP BY socle_cycle_id, socle_domaine_id ';
  $DB_SQL.= 'ORDER BY socle_cycle_ordre ASC, socle_domaine_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_optgroup = array();
  foreach($DB_TAB as $key => $DB_ROW)
  {
    $tab_optgroup[$DB_ROW['optgroup']] = $DB_ROW['optgroup_info'];
    unset($DB_TAB[$key]['optgroup_info']);
  }
  Form::$tab_select_optgroup['cycles'] = $tab_optgroup;
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun item de référentiel n\'est rattaché au socle 2016.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des composantes du socle 2016 pour les référentiels en place dans l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_socle2016_composantes()
{
  $DB_SQL = 'SELECT CONCAT(socle_cycle_id,"_",socle_composante_id) AS valeur, CONCAT(socle_cycle_nom," - Domaine ",socle_domaine_ordre," - Composante ",socle_composante_ordre," : ",socle_composante_nom_simple) AS texte, CONCAT(socle_cycle_id,"_",socle_domaine_id) AS optgroup, CONCAT(socle_cycle_nom," - Domaine ",socle_domaine_ordre," : ",socle_domaine_nom_simple) AS optgroup_info ';
  $DB_SQL.= 'FROM sacoche_jointure_referentiel_socle ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_cycle USING(socle_cycle_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_composante_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_domaine USING (socle_domaine_id) ';
  $DB_SQL.= 'GROUP BY socle_cycle_id, socle_composante_id ';
  $DB_SQL.= 'ORDER BY socle_cycle_ordre ASC, socle_domaine_ordre ASC, socle_composante_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_optgroup = array();
  foreach($DB_TAB as $key => $DB_ROW)
  {
    $tab_optgroup[$DB_ROW['optgroup']] = $DB_ROW['optgroup_info'];
    unset($DB_TAB[$key]['optgroup_info']);
  }
  Form::$tab_select_optgroup['domaines'] = $tab_optgroup;
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun item de référentiel n\'est rattaché au socle 2016.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des niveaux / classes / groupes d'un établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param bool   $sans   TRUE  par défaut => pour avoir un choix "Sans classe affectée"
 * @param bool   $tout   TRUE  par défaut => pour avoir un choix "Tout l'établissement"
 * @param bool   $ancien FALSE par défaut => pour avoir un choix "Anciens élèves"
 * @return array|string
 */
public static function DB_OPT_regroupements_etabl( $sans=TRUE , $tout=TRUE , $ancien=FALSE )
{
  // Options du select : catégorie "Divers"
  $DB_TAB_divers = array();
  if($sans)
  {
    $DB_TAB_divers[] = array(
      'valeur'   => 'd1',
      'texte'    => 'Sans classe affectée',
      'optgroup' => 'divers',
    );
  }
  if($tout)
  {
    $DB_TAB_divers[] = array(
      'valeur'   => 'd2',
      'texte'    => 'Tout l\'établissement',
      'optgroup' => 'divers',
    );
  }
  if($ancien)
  {
    $DB_TAB_divers[] = array(
      'valeur'   => 'd3',
      'texte'    => 'Anciens élèves',
      'optgroup' => 'divers',
    );
  }
  // Options du select : catégorie "Niveaux" (contenant des classes ou des groupes)
  $DB_SQL = 'SELECT CONCAT("n",niveau_id) AS valeur, niveau_nom AS texte, "niveau" AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'GROUP BY niveau_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':type'=>'classe');
  $DB_TAB_niveau = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Options du select : catégories "Classes" et "Groupes"
  $DB_SQL = 'SELECT CONCAT(LEFT(groupe_type,1),groupe_id) AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':type1' => 'classe',
    ':type2' => 'groupe',
  );
  $DB_TAB_classe_groupe = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // On assemble tous ces tableaux à la suite
  $DB_TAB = array_merge($DB_TAB_divers,$DB_TAB_niveau,$DB_TAB_classe_groupe);
  return $DB_TAB ;

}

/**
 * Retourner un tableau [valeur texte optgroup] des groupes d'un établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_groupes_etabl()
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type'=>'groupe');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun groupe n\'est enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes d'un professeur identifié
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_groupes_professeur($user_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type!=:type4 ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $user_id,
    ':type4'   => 'eval',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne vous sont affectés.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes d'un professeur identifié
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_classes_professeur($user_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type1 ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $user_id,
    ':type1'   => 'classe',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne vous sont affectés.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes de l'établissement
 *
 * @param bool   $with_ref             Avec la référence de la classe entre parenthèses.
 * @param string $with_configuration   Avec la référence de la configuration du bilan officiel ; dans ce cas indiquer le type de bilan.
 * @return array|string
 */
public static function DB_OPT_classes_etabl( $with_ref , $with_configuration=NULL )
{
  $nom_groupe    = ($with_ref) ? 'CONCAT(groupe_nom," (",groupe_ref,")")' : 'groupe_nom' ;
  $select_config = ($with_configuration) ? ', groupe_configuration_'.$with_configuration.' AS configuration_ref ' : '' ;
  $DB_SQL = 'SELECT groupe_id AS valeur, '.$nom_groupe.' AS texte '.$select_config;
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type'=>'classe');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe n\'est enregistrée.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes de l'établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_classes_groupes_etabl()
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':type1' => 'classe',
    ':type2' => 'groupe',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne sont enregistrés.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes où un professeur identifié est professeur principal
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_classes_prof_principal($user_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type1 AND jointure_pp=:pp ';
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $user_id,
    ':type1'   => 'classe',
    ':pp'      => 1,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'êtes professeur principal d\'aucune classe.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes des enfants d'un parent
 *
 * @param int   $parent_id
 * @return array|string
 */
public static function DB_OPT_classes_parent($parent_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, "classe" AS optgroup ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() AND groupe_id IS NOT NULL '; // Not NULL sinon pb qd un parent est rattaché à un enfant affecté dans aucune classe.
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':parent_id'   => $parent_id,
    ':profil_type' => 'eleve',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe ne comporte un élève associé à ce compte.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des sélections d'items d'un professeur identifié
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_selection_items($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT(item_id SEPARATOR "_") AS valeur, selection_item_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_prof USING (selection_item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_item USING (selection_item_id) ';
  $DB_SQL.= 'WHERE ( sacoche_selection_item.proprio_id=:proprio_id OR sacoche_jointure_selection_prof.prof_id=:prof_id ) ';
  $DB_SQL.= 'GROUP BY sacoche_selection_item.selection_item_id ';
  $DB_SQL.= 'ORDER BY selection_item_nom ASC';
  $DB_VAR = array(
    ':proprio_id' => $user_id,
    ':prof_id'    => $user_id,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'avez mémorisé aucune sélection d\'items.' ;
}

/**
 * Retourner un tableau [valeur texte] des périodes de l'établissement, indépendamment des rattachements aux classes
 *
 * @param bool   $alerte   affiche un message d'erreur si aucune periode n'est trouvée
 * @return array|string
 */
public static function DB_OPT_periodes_etabl($alerte=FALSE)
{
  $DB_SQL = 'SELECT periode_id AS valeur, periode_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : ( ($alerte) ? 'Aucune période n\'est enregistrée.' : array() ) ;
}

/**
 * Retourner un tableau [valeur texte] des profils activés et accessibles à un utilisateur (admin, directeur, prof, personnel... mais pas élève / parent)
 *
 * @param string $user_profil_type
 * @return array|string
 */
public static function DB_OPT_profils_types($user_profil_type)
{
  $DB_SQL = 'SELECT CONCAT(user_profil_sigle,"_",user_profil_join_groupes) AS valeur, user_profil_nom_court_singulier AS texte ';
  $DB_SQL.= 'FROM sacoche_user_profil ';
  $DB_SQL.= 'WHERE user_profil_structure=:user_profil_structure AND user_profil_actif=:user_profil_actif ';
  if($user_profil_type!='administrateur')
  {
    $DB_SQL.= 'AND user_profil_type!="administrateur" ';
    if($user_profil_type!='directeur')
    {
      $DB_SQL.= 'AND user_profil_type!="directeur" ';
    }
  }
  $DB_VAR = array(
    ':user_profil_structure' => 1,
    ':user_profil_actif'     => 1,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner un tableau [valeur texte] des directeurs actuels de l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_directeurs_etabl()
{
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil_type'=>'directeur');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun directeur enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs actuels de l'établissement
 *
 * @param string $groupe_type   facultatif ; valeur parmi [all] [niveau] [classe] [groupe] [config]
 * @param int    $groupe_id     facultatif ; id du niveau ou de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_professeurs_etabl( $groupe_type='all' , $groupe_id=0 )
{
  $select = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
  $where  = 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $ljoin  = '';
  $group  = '';
  $order  = 'ORDER BY user_nom ASC, user_prenom ASC';
  switch($groupe_type)
  {
    case 'all' :
      $from  = 'FROM sacoche_user ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      break;
    case 'config' : // équivalent de [all] mais sans les personnels automatiquement rattachés à tous les groupes (documentalistes, CPE, etc.)
      $from  = 'FROM sacoche_user ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      $where.= 'AND user_profil_join_groupes="config" ';
      break;
    case 'niveau' :
      $from  = 'FROM sacoche_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user USING (user_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      $where.= 'AND niveau_id=:niveau ';
      $group.= 'GROUP BY user_id ';
      break;
    case 'classe' :
    case 'groupe' :
      $from  = 'FROM sacoche_jointure_user_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user USING (user_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      $where.= 'AND groupe_id=:groupe ';
      break;
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = $select.$from.$ljoin.$where.$group.$order;
  $DB_VAR = array(
    ':profil_type' => 'professeur',
    ':niveau'      => $groupe_id,
    ':groupe'      => $groupe_id,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte] des profs ayant évalué les élèves d'une classe ou d'un groupe
 *
 * On cherche les évals des profs sur les élèves du regroupement.
 * On récupère donc aussi les profs qui ne sont pas forcément rattachés au regroupement.
 * Ce qui est utile pour un élève d'une classe si un prof n'est rattaché qu'à un groupe, et inversement.
 *
 * @param string $groupe_type   valeur parmi 'classe' ou 'groupe'
 * @param int    $groupe_id     id de la classe ou du groupe
 * @return array
 */
public static function DB_OPT_profs_groupe( $groupe_type , $groupe_id )
{
  $DB_SQL = 'SELECT prof.user_id AS valeur, prof.user_genre AS prof_genre, prof.user_nom AS prof_nom, prof.user_prenom AS prof_prenom ';
  switch ($groupe_type)
  {
    case 'classe' :  // On veut tous les élèves d'une classe (on utilise "eleve_classe_id" de "sacoche_user")
      $DB_SQL.= 'FROM sacoche_user AS eleve ';
      $WHERE  = 'WHERE eleve.eleve_classe_id=:classe ';
      $DB_VAR = array(':classe'=>$groupe_id);
      break;
    case 'groupe' :  // On veut tous les élèves d'un groupe (on utilise la jointure de "sacoche_jointure_user_groupe")
      $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
      $DB_SQL.= 'LEFT JOIN sacoche_user AS eleve USING (user_id) ';
      $WHERE  = 'WHERE sacoche_jointure_user_groupe.groupe_id=:groupe ';
      $DB_VAR = array(':groupe'=>$groupe_id);
      break;
  }
  $DB_SQL.= 'LEFT JOIN sacoche_saisie ON eleve.user_id=sacoche_saisie.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS prof ON sacoche_devoir.proprio_id=prof.user_id ';
  $DB_SQL.= $WHERE.'AND eleve.user_sortie_date>NOW() AND prof.user_id IS NOT NULL ';
  $DB_SQL.= 'GROUP BY prof.user_id ';
  $DB_SQL.= 'ORDER BY prof_nom ASC, prof_prenom ASC ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $key => $DB_ROW)
    {
      $texte = To::texte_identite( $DB_ROW['prof_nom'] , FALSE , $DB_ROW['prof_prenom'] , TRUE , $DB_ROW['prof_genre'] );
      unset( $DB_TAB[$key]['prof_nom'], $DB_TAB[$key]['prof_prenom'], $DB_TAB[$key]['prof_genre'] );
      $DB_TAB[$key]['texte'] = $texte;
    }
  }
  return $DB_TAB;
}

/**
 * Retourner un tableau [valeur texte] des profs (au statut actif) associés à un groupe (classe) et une matière (éventuellement)
 *
 * @param int    $groupe_id     id du groupe classe
 * @param int    $matiere_id    id de la matière (facultatif)
 * @return array
 */
public static function DB_OPT_profs_groupe_matiere( $groupe_id , $matiere_id=FALSE )
{
  $join  = ($matiere_id) ? 'LEFT JOIN sacoche_jointure_user_matiere USING (user_id) ' : '' ;
  $where = ($matiere_id) ? 'AND matiere_id=:matiere_id ' : '' ;
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= $join;
  $DB_SQL.= 'WHERE groupe_id=:groupe_id '.$where.'AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':groupe_id'  => $groupe_id,
    ':matiere_id' => $matiere_id,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs et directeurs de l'établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $statut   statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @return array|string
 */
public static function DB_OPT_professeurs_directeurs_etabl($statut)
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte, user_profil_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type IN(:profil_type1,:profil_type2) AND '.$test_date_sortie.' ';
  $DB_SQL.= 'ORDER BY user_profil_type DESC, user_nom ASC, user_prenom ASC';
  $DB_VAR = array(
    ':profil_type1' => 'professeur',
    ':profil_type2' => 'directeur',
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur ou directeur trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des parents de l'établissement
 *
 * @param int    $statut        statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @param string $groupe_type   facultatif ; valeur parmi [all] [niveau] [classe] [groupe] 
 * @param int    $groupe_id     facultatif ; id du niveau ou de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_parents_etabl( $statut , $groupe_type='all' , $groupe_id=0 )
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $select = 'SELECT parent.user_id AS valeur, CONCAT(parent.user_nom," ",parent.user_prenom," (",parent.user_login,")") AS texte ';
  $where  = 'WHERE parent_profil.user_profil_type=:profil_type AND parent.'.$test_date_sortie.' ';
  $ljoin  = '';
  $group  = '';
  $order  = 'ORDER BY parent.user_nom ASC, parent.user_prenom ASC';
  switch($groupe_type)
  {
    case 'all' :
      $from  = 'FROM sacoche_user AS parent ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      break;
    case 'niveau' :
      $from  = 'FROM sacoche_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_groupe.groupe_id=enfant.eleve_classe_id ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND niveau_id=:niveau ';
      $group.= 'GROUP BY parent.user_id ';
      break;
    case 'classe' :
      $from  = 'FROM sacoche_user AS enfant ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND enfant.eleve_classe_id=:classe ';
      $group.= 'GROUP BY parent.user_id ';
      break;
    case 'groupe' :
      $from  = 'FROM sacoche_jointure_user_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user AS enfant USING (user_id) ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND groupe_id=:groupe ';
      $group.= 'GROUP BY parent.user_id ';
      break;
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = $select.$from.$ljoin.$where.$group.$order;
  $DB_VAR = array(
    ':profil_type' => 'parent',
    ':niveau'      => $groupe_id,
    ':classe'      => $groupe_id,
    ':groupe'      => $groupe_id,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun responsable trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des élèves d'un regroupement préselectionné
 *
 * @param string $groupe_type    valeur parmi [sdf] [all] [niveau] [classe] [groupe] [besoin] 
 * @param int    $groupe_id      id du niveau ou de la classe ou du groupe
 * @param int    $statut         statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @param string $eleves_ordre   valeur parmi [alpha] [classe]
 * @return array|string
 */
public static function DB_OPT_eleves_regroupement( $groupe_type , $groupe_id , $statut , $eleves_ordre )
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $DB_TAB = $_SESSION['OPT_PARENT_ENFANTS'];
    foreach($DB_TAB as $key=>$tab)
    {
      if($tab['classe_id']!=$groupe_id)
      {
        unset($DB_TAB[$key]);
      }
    }
  }
  else
  {
    $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
    switch ($groupe_type)
    {
      case 'sdf' :  // On veut les élèves non affectés dans une classe
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND eleve_classe_id=:classe ';
        $DB_VAR = array(':profil_type'=>'eleve',':classe'=>0);
        break;
      case 'all' :  // On veut tous les élèves de l'établissement
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' ';
        $DB_VAR = array(':profil_type'=>'eleve');
        break;
      case 'niveau' :  // On veut tous les élèves d'un niveau
        $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND niveau_id=:niveau ';
        $DB_VAR = array(':profil_type'=>'eleve',':niveau'=>$groupe_id);
        break;
      case 'classe' :  // On veut tous les élèves d'une classe (on utilise "eleve_classe_id" de "sacoche_user")
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND eleve_classe_id=:classe ';
        $DB_VAR = array(':profil_type'=>'eleve',':classe'=>$groupe_id);
        break;
      case 'groupe' :  // On veut tous les élèves d'un groupe (on utilise la jointure de "sacoche_jointure_user_groupe")
      case 'besoin' :  // On veut tous les élèves d'un groupe de besoin (on utilise la jointure de "sacoche_jointure_user_groupe")
        $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
        if($eleves_ordre=='classe')
        {
          $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
          $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
        }
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND sacoche_jointure_user_groupe.groupe_id=:groupe ';
        $DB_VAR = array(':profil_type'=>'eleve',':groupe'=>$groupe_id);
        break;
    }
    // Ordonner par ordre alphabétique ou par classe d'origine les élèves d'un groupe
    $DB_SQL.= ( ( ($groupe_type!='groupe') && ($groupe_type!='besoin') ) || ($eleves_ordre!='classe') ) ? 'ORDER BY user_nom ASC, user_prenom ASC' : 'ORDER BY niveau_ordre ASC, groupe_nom ASC, user_nom ASC, user_prenom ASC' ;
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des enfants d'un parent
 *
 * @param int   $parent_id
 * @return array|string
 */
public static function DB_OPT_enfants_parent($parent_id)
{
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte, eleve_classe_id AS classe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  // Test "eleve_classe_id!=0" pour éviter les enfants non affectés à une classe
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_sortie_date>NOW() AND eleve_classe_id!=0 ';
  $DB_SQL.= 'ORDER BY resp_legal_num ASC, user_nom ASC, user_prenom ASC ';
  $DB_VAR = array( ':parent_id' => $parent_id );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève affecté dans une classe n\'est associé à votre compte !' ;
}

/**
 * Retourner un tableau [valeur texte] des structures des bilans officiels archivés
 *
 * @param void
 * @return array
 */
public static function DB_OPT_officiel_archive_structure()
{
  $DB_SQL = 'SELECT DISTINCT structure_uai AS valeur, CONCAT(structure_uai," - ",structure_denomination) AS texte ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des années scolaires des bilans officiels archivés
 *
 * @param void
 * @return array
 */
public static function DB_OPT_officiel_archive_annee()
{
  $DB_SQL = 'SELECT DISTINCT annee_scolaire AS valeur, annee_scolaire AS texte ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'ORDER BY annee_scolaire DESC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des années scolaires des bilans officiels archivés
 * MAX() pour éviter la remontée de "Trimestre 1/3" et de "Premier trimestre" qui ont le même identifiant.
 * TODO : A TERME IL FAUDRA REPENSER UNE SELECTION SUR L'ETABLISSEMENT EN AMONT (SI PLUSIEURS ETABLISSEMENTS ALORS PAS DE CHOIX DE PERIODE)
 *
 * @param string $annee_scolaire
 * @return array
 */
public static function DB_OPT_officiel_periode($annee_scolaire)
{
  $DB_SQL = 'SELECT DISTINCT periode_id AS valeur, MAX(periode_nom) AS texte ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'WHERE annee_scolaire=:annee_scolaire ';
  $DB_SQL.= 'GROUP BY periode_id ';
  $DB_SQL.= 'ORDER BY periode_id ASC ';
  $DB_VAR = array( ':annee_scolaire' => $annee_scolaire );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner un tableau [valeur texte] des périodes des données exportables du livret scolaire
 * MAX() pour éviter l'erreur "SELECT list is not in GROUP BY clause and contains nonaggregated column 'sacoche_mono.sacoche_periode.periode_nom' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by"
 *
 * @param void
 * @return array
 */
public static function DB_OPT_livret_periode_export()
{
  $DB_SQL = 'SELECT CONCAT(livret_page_periodicite,jointure_periode) AS valeur, MAX(periode_nom) AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_export ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode ON sacoche_livret_export.jointure_periode = sacoche_periode.periode_livret AND periode_livret!="" ';
  $DB_SQL.= 'GROUP BY livret_page_periodicite, jointure_periode ';
  $DB_SQL.= 'ORDER BY livret_page_periodicite, jointure_periode ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  foreach($DB_TAB as $key => $DB_ROW)
  {
    if(is_null($DB_ROW['texte']))
    {
      $DB_TAB[$key]['texte'] = 'Cycle';
    }
  }
  return $DB_TAB;
}

/**
 * Retourner un tableau [valeur texte optgroup] des types et références de bilans officiels archivés
 * TODO : A TERME IL FAUDRA TRIER SUR archive_ref ET PREVOIR UN TEXTE PLUS PRESENTABLE
 *
 * @param void
 * @return array
 */
public static function DB_OPT_officiel_archive_type_ref()
{
  $DB_SQL = 'SELECT DISTINCT CONCAT(archive_type,"_",archive_ref) AS valeur, CONCAT(archive_type," ",archive_ref) AS texte, archive_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'ORDER BY archive_type DESC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des structures d'origines connues
 *
 * @param string $listing_eleve_id
 * @return array
 */
public static function DB_OPT_structure_origine($listing_eleve_id)
{
  $DB_SQL = 'SELECT DISTINCT structure_uai AS valeur, CONCAT( SUBSTRING(structure_uai,1,3) , " - " , structure_localisation , " - " , structure_denomination ) AS texte ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'INNER JOIN sacoche_structure_origine ON sacoche_user.eleve_uai_origine = sacoche_structure_origine.structure_uai '; // Pour éviter les élèves sans établissement d'origine renseigné
  $DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') ';
  $DB_SQL.= 'ORDER BY texte ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

}
?>