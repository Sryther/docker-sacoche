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
// Ces méthodes ne concernent que la table "sacoche_jointure_devoir_eleve".

class DB_STRUCTURE_COMMENTAIRE extends DB
{

/**
 * lister_commentaires_eleves_dates
 *
 * @param int    $prof_id
 * @param string $liste_eleve_id   id des élèves séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @return array
 */
public static function DB_lister_commentaires_eleves_dates( $prof_id , $liste_eleve_id , $date_mysql_debut , $date_mysql_fin)
{
  $DB_SQL = 'SELECT eleve_id, devoir_info, devoir_date, jointure_texte ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_eleve USING (devoir_id) ';
  $DB_SQL.= 'WHERE proprio_id=:proprio_id AND devoir_date>=:date_debut AND devoir_date<=:date_fin AND eleve_id IN('.$liste_eleve_id.') ';
  $DB_SQL.= 'ORDER BY devoir_date ASC ';
  $DB_VAR = array(
    ':proprio_id' => $prof_id,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_devoir_commentaires
 *
 * @param int   $devoir_id
 * @return array
 */
public static function DB_lister_devoir_commentaires($devoir_id)
{
  $DB_SQL = 'SELECT eleve_id, jointure_texte, jointure_audio ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_devoir_commentaires
 *
 * @param int    $devoir_id
 * @param int    $eleve_id
 * @return array
 */
public static function DB_recuperer_devoir_commentaires($devoir_id,$eleve_id)
{
  $DB_SQL = 'SELECT jointure_texte, jointure_audio ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id ';
  $DB_VAR = array(
    ':devoir_id' => $devoir_id,
    ':eleve_id'  => $eleve_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_devoir_commentaire
 *
 * @param int    $devoir_id
 * @param int    $eleve_id
 * @param string $msg_objet   texte | audio
 * @return array
 */
public static function DB_recuperer_devoir_commentaire($devoir_id,$eleve_id,$msg_objet)
{
  $jointure = 'jointure_'.$msg_objet;
  $DB_SQL = 'SELECT '.$jointure.' ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id ';
  $DB_VAR = array(
    ':devoir_id' => $devoir_id,
    ':eleve_id'  => $eleve_id,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * remplacer_devoir_commentaire
 *
 * @param int    $devoir_id
 * @param int    $eleve_id
 * @param string $msg_objet   texte | audio
 * @param string $msg_url
 * @return void
 */
public static function DB_remplacer_devoir_commentaire($devoir_id,$eleve_id,$msg_objet,$msg_url)
{
  $jointure = 'jointure_'.$msg_objet;
  $DB_SQL = 'INSERT INTO sacoche_jointure_devoir_eleve( devoir_id, eleve_id, '.$jointure.') ';
  $DB_SQL.= 'VALUES                                   (:devoir_id,:eleve_id,:'.$jointure.') ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE '.$jointure.'=:'.$jointure.' ';
  $DB_VAR = array(
    ':devoir_id'  => $devoir_id,
    ':eleve_id'   => $eleve_id,
    ':'.$jointure => $msg_url,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_devoir_commentaire
 *
 * @param int    $devoir_id
 * @param int    $eleve_id
 * @return void
 */
public static function DB_supprimer_devoir_commentaire($devoir_id,$eleve_id)
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id ';
  $DB_VAR = array(
    ':devoir_id' => $devoir_id,
    ':eleve_id'  => $eleve_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}


}
?>