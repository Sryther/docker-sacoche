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

// Ces méthodes ne concernent que la base WEBMESTRE (donc une installation multi-structures).
// Ces méthodes ne concernent que les utilisateurs non identifiés.

class DB_WEBMESTRE_PUBLIC extends DB
{

/**
 * Récupérer les informations concernant les tables présentes dans la base.
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
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}
/**
 * Récupérer la valeur d'une variable système de MySQL
 *
 * Retourne un tableau a deux entrées : "Variable_name" (le nom de la variable) et "Value" (sa valeur).
 *
 * @param string $variable_nom   max_allowed_packet | max_user_connections | group_concat_max_len
 * @return array
 */
public static function DB_recuperer_variable_MySQL($variable_nom)
{
  $DB_SQL = 'SHOW VARIABLES LIKE "'.$variable_nom.'"';
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
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
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
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
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer l'identifiant d'une base d'établissement à partir du numéro UAI d'une structure (mode multi-structures)
 *
 * @param string uai
 * @return int | NULL
 */
public static function DB_recuperer_structure_id_base_for_UAI($uai)
{
  $DB_SQL = 'SELECT sacoche_base ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE structure_uai=:uai ';
  $DB_VAR = array(':uai'=>$uai);
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer le nom d'un établissement à partir du numéro de la base d'une structure (mode multi-structures)
 *
 * @param int base_id
 * @return string | NULL
 */
public static function DB_recuperer_structure_nom_for_Id($base_id)
{
  $DB_SQL = 'SELECT structure_denomination ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(':base_id'=>$base_id);
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récuperer, à partir d'une référence de connexion, l'identifiant d'un partenaire conventionné pour charger sa communication
 *
 * @param string   $connecteur
 * @return int
 */
public static function DB_recuperer_id_partenaire_for_connecteur($connecteur)
{
  $DB_SQL = 'SELECT partenaire_id ';
  $DB_SQL.= 'FROM sacoche_partenaire ';
  $DB_SQL.= 'WHERE partenaire_connecteurs LIKE :connecteur_like ';
  $DB_SQL.= 'LIMIT 1 '; // Au cas où, même s'il ne devrait pas y avoir 2 partenaires pour un même connecteur
  $DB_VAR = array(':connecteur_like'=>'%,'.$connecteur.',%');
  return (int)DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récuperer, à partir d'un identifiant, les données d'un partenaire conventionné tentant de se connecter (le mdp est comparé ensuite)
 *
 * @param int   $partenaire_id
 * @return array
 */
public static function DB_recuperer_donnees_partenaire($partenaire_id)
{
  $DB_SQL = 'SELECT sacoche_partenaire.* ';
  $DB_SQL.= 'FROM sacoche_partenaire ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id ';
  $DB_VAR = array(':partenaire_id'=>$partenaire_id);
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de structure inscrites (mode multi-structures)
 *
 * @param void
 * @return string   n . structure(s)
 */
public static function DB_compter_structure()
{
  $DB_SQL = 'SELECT COUNT(sacoche_base) AS nombre ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
  $s = ($DB_ROW['nombre']>1) ? 's' : '' ;
  return $DB_ROW['nombre'].' structure'.$s;
}

/**
 * Créer les tables de la base du webmestre et les remplir (mode multi-structures)
 *
 * @param void
 * @return void
 */
public static function DB_creer_remplir_tables_webmestre()
{
  $tab_files = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_SQL_WEBMESTRE);
  foreach($tab_files as $file)
  {
    $extension = pathinfo($file,PATHINFO_EXTENSION);
    if($extension=='sql')
    {
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.$file);
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      /*
      La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
      La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
      */
      DB::close(SACOCHE_WEBMESTRE_BD_NAME);
    }
  }
  // Il est arrivé que la fonction DB_modifier_parametre() retourne une erreur disant que la table n'existe pas.
  // Comme si les requêtes précédentes étaient en cache, et pas encore toutes passées (parce qu'au final, quand on va voir la base, toutes les tables sont bien là).
  // Est-ce que c'est possible au vu du fonctionnement de la classe de connexion ? Et, bien sûr, y a-t-il quelque chose à faire pour éviter ce problème ?
  // En attendant une réponse de SebR, j'ai mis ce sleep(1)... sans trop savoir si cela pouvait aider...
  @sleep(1);
  // Renseigner la version de la base du webmestre
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_parametre('version_base',VERSION_BASE_WEBMESTRE);
}

/**
 * tester_convention_active
 *
 * @param int    $base_id
 * @param string $connexion_nom
 * @return int
 */
public static function DB_tester_convention_active($base_id,$connexion_nom)
{
  $DB_SQL = 'SELECT convention_id ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id AND connexion_nom=:connexion_nom AND convention_date_debut<=:today AND convention_date_fin>=:today AND convention_activation=:convention_activation ';
  $DB_VAR = array(
    ':base_id'               => $base_id,
    ':connexion_nom'         => $connexion_nom,
    ':today'                 => TODAY_MYSQL,
    ':convention_activation' => 1,
  );
  return (int)DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>