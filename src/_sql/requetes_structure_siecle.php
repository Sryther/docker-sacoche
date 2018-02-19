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

class DB_STRUCTURE_SIECLE extends DB
{

/**
 * ajouter_import
 * Enregistre en JSON le XML ou le ARRAY transmis.
 * Convertir le XML en JSON permet un gain de place (par exemple 1,5 Mo -> 0,8 Mo ou encore 5,3 Mo -> 1,1 Mo).
 * Il y aurait aussi la possibilité d'utiliser COMPRESS() et UNCOMPRESS() avec un champ de type MEDIUMBLOB :
 * cela réduit bien davantage mais c'est moins lisible en BDD et la manipulation de champs BLOB est peu rassurante.
 *
 * @param string   $import_objet
 * @param string   $import_annee
 * @param mixed    $import_data   xml pour SIECLE, array pour ONDE
 * @return void
 */
public static function DB_ajouter_import( $import_objet , $import_annee , $import_data )
{
  // Certains fichiers sont très lourds -> on enlève des éléments dont on est certain qu'ils ne serviront pas -> cependant c'est le fichier des élèves le plus lourd et je n'ai pas osé y enlever grand chose...
  switch($import_objet)
  {
    case 'Eleves'      : unset( $import_data->DONNEES->BOURSES , $import_data->DONNEES->ADRESSES ); break;
    case 'sts_emp_UAI' : unset( $import_data->DONNEES->HORAIRES , $import_data->DONNEES->ALTERNANCES , $import_data->DONNEES->SALLES_COURS ); break;
    case 'Nomenclature': unset( $import_data->DONNEES->MODALITES_ELECTION , $import_data->DONNEES->REGIMES , $import_data->DONNEES->LIENS_PARENTE , $import_data->DONNEES->BOURSES , $import_data->DONNEES->PROFESSIONS , $import_data->DONNEES->SITUATIONS_EMPLOI , $import_data->DONNEES->PCS_SITUATIONS_EMPLOI , $import_data->DONNEES->PROVENANCES , $import_data->DONNEES->MOTIFS_SORTIE , $import_data->DONNEES->STATUTS_ELEVE , $import_data->DONNEES->CONTRATS , $import_data->DONNEES->TYPES_ETABLISSEMENT ); break;
  }
  // Conversion JSON
  $import_json = json_encode( (array)$import_data );
  if($import_objet=='Eleves')
  {
    // Testé avant la requête et non après avec DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) car un "Warning: PDOStatement::execute(): MySQL server has gone away" ne permet pas de poursuivre.
    $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('max_allowed_packet');
    $json_size = strlen($import_json);
    if( $json_size > $DB_ROW['Value'] )
    {
      Json::end( FALSE , 'Taille des données ('.FileSystem::afficher_fichier_taille($json_size).') dépassant la limitation <em>max_allowed_packet</em> de MySQL !' );
    }
  }
  $DB_SQL = 'UPDATE sacoche_siecle_import ';
  $DB_SQL.= 'SET siecle_import_date=:import_date, siecle_import_annee=:import_annee, siecle_import_contenu=:import_contenu ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array(
    ':import_objet'   => $import_objet,
    ':import_date'    => TODAY_MYSQL,
    ':import_annee'   => $import_annee,
    ':import_contenu' => $import_json,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_import_contenu
 * Retourne en ARRAY le JSON enregistré
 *
 * @param string   $import_objet
 * @return array
 */
public static function DB_recuperer_import_contenu( $import_objet )
{
  $DB_SQL = 'SELECT siecle_import_contenu ';
  $DB_SQL.= 'FROM sacoche_siecle_import ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array( ':import_objet' => $import_objet );
  $import_json = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return ($import_json) ? json_decode( $import_json , TRUE ) : array() ;
}

/**
 * recuperer_import_date_annee
 *
 * @param string   $import_objet
 * @return array
 */
public static function DB_recuperer_import_date_annee( $import_objet )
{
  $DB_SQL = 'SELECT siecle_import_date, siecle_import_annee ';
  $DB_SQL.= 'FROM sacoche_siecle_import ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array( ':import_objet' => $import_objet );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_groupe_ref_1d ; pour une transition de BE1D à ONDE
 * pas de rapport avec la table [sacoche_siecle_import] mais mis ici car requête particulière et provisoire liée aux bases E.N.
 *
 * @param string $groupe_ref_old
 * @param string $groupe_ref_new
 * @return void
 */
public static function DB_modifier_groupe_ref_1d( $groupe_ref_old , $groupe_ref_new )
{
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_ref=:groupe_ref_new ';
  $DB_SQL.= 'WHERE groupe_ref=:groupe_ref_old AND groupe_type="classe" ';
  $DB_VAR = array(
    ':groupe_ref_old' => $groupe_ref_old,
    ':groupe_ref_new' => $groupe_ref_new,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>