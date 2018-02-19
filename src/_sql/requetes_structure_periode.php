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
// Ces méthodes sont en rapport avec les périodes (tables "sacoche_periode" & "sacoche_jointure_groupe_periode").

class DB_STRUCTURE_PERIODE extends DB
{

/**
 * recuperer_amplitude_periodes
 *
 * @param void
 * @return array  de la forme array('tout_debut'=>... , ['toute_fin']=>... , ['nb_jours_total']=>...)
 */
public static function DB_recuperer_amplitude_periodes()
{
  $DB_SQL = 'SELECT MIN(jointure_date_debut) AS tout_debut , MAX(jointure_date_fin) AS toute_fin ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  if(!empty($DB_ROW))
  {
    // On ajoute un jour pour dessiner les barres jusqu'au jour suivant (accessoirement, ça évite aussi une possible division par 0).
    $DB_SQL = 'SELECT DATEDIFF(DATE_ADD(:toute_fin,INTERVAL 1 DAY),:tout_debut) AS nb_jours_total ';
    $DB_VAR = array(
      ':tout_debut' => $DB_ROW['tout_debut'],
      ':toute_fin'  => $DB_ROW['toute_fin'],
    );
    $DB_ROW['nb_jours_total'] = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  return $DB_ROW;
}

/**
 * lister_periodes
 *
 * @param void
 * @return array
 */
public static function DB_lister_periodes()
{
  $DB_SQL = 'SELECT periode_id, periode_ordre, periode_nom, periode_livret ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_jointure_groupe_periode ; le rangement par ordre de période permet, si les périodes se chevauchent, que javascript choisisse la 1ère par défaut
 *
 * @param string   $listing_groupes_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_jointure_groupe_periode($listing_groupes_id)
{
  $DB_SQL = 'SELECT sacoche_jointure_groupe_periode.* ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE groupe_id IN ('.$listing_groupes_id.') ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_jointure_groupe_periode_avec_infos_graphiques
 *
 * @param string   $tout_debut   date de début
 * @return array
 */
public static function DB_lister_jointure_groupe_periode_avec_infos_graphiques($tout_debut)
{
  $DB_SQL = 'SELECT * , ';
  $DB_SQL.= 'DATEDIFF(jointure_date_debut,:tout_debut) AS position_jour_debut , DATEDIFF(jointure_date_fin,jointure_date_debut) AS nb_jour ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'ORDER BY groupe_id ASC, jointure_date_debut ASC, jointure_date_fin ASC';
  $DB_VAR = array(':tout_debut'=>$tout_debut);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_periode_nom
 *
 * @param string $periode_nom
 * @param int    $periode_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_periode_nom( $periode_nom , $periode_id=FALSE )
{
  $DB_SQL = 'SELECT periode_id ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'WHERE periode_nom=:periode_nom ';
  $DB_SQL.= ($periode_id) ? 'AND periode_id!=:periode_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':periode_nom' => $periode_nom,
    ':periode_id'  => $periode_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_periode_livret
 *
 * @param int $periode_livret
 * @param int $periode_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_periode_livret( $periode_livret , $periode_id=FALSE )
{
  $DB_SQL = 'SELECT periode_id ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'WHERE periode_livret=:periode_livret ';
  $DB_SQL.= ($periode_id) ? 'AND periode_id!=:periode_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':periode_livret' => $periode_livret,
    ':periode_id'     => $periode_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_periode
 *
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @param int    $periode_livret
 * @return int
 */
public static function DB_ajouter_periode( $periode_ordre , $periode_nom , $periode_livret )
{
  $DB_SQL = 'INSERT INTO sacoche_periode(periode_ordre, periode_nom, periode_livret) ';
  $DB_SQL.= 'VALUES(                    :periode_ordre,:periode_nom,:periode_livret)';
  $DB_VAR = array(
    ':periode_ordre'  => $periode_ordre,
    ':periode_nom'    => $periode_nom,
    ':periode_livret' => $periode_livret,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_periode
 *
 * @param int    $periode_id
 * @param int    $periode_ordre
 * @param string $periode_nom
 * @param int    $periode_livret
 * @return void
 */
public static function DB_modifier_periode( $periode_id , $periode_ordre , $periode_nom , $periode_livret )
{
  $DB_SQL = 'UPDATE sacoche_periode ';
  $DB_SQL.= 'SET periode_ordre=:periode_ordre, periode_nom=:periode_nom, periode_livret=:periode_livret ';
  $DB_SQL.= 'WHERE periode_id=:periode_id ';
  $DB_VAR = array(
    ':periode_id'     => $periode_id,
    ':periode_ordre'  => $periode_ordre,
    ':periode_nom'    => $periode_nom,
    ':periode_livret' => $periode_livret,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_groupe_periode
 *
 * @param int    $groupe_id        id du groupe
 * @param int    $periode_id       id de la période
 * @param bool   $etat             TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @param string $date_debut_mysql date de début au format mysql (facultatif : obligatoire uniquement si $etat=TRUE)
 * @param string $date_fin_mysql   date de fin au format mysql (facultatif : obligatoire uniquement si $etat=TRUE)
 * @return void
 */
public static function DB_modifier_liaison_groupe_periode( $groupe_id , $periode_id , $etat , $date_debut_mysql=NULL , $date_fin_mysql=NULL )
{
  if($etat)
  {
    // Ajouter / modifier une liaison : tester si la liaison existe, sinon comme REPLACE = DELETE + INSERT ça fait perdre les infos des bulletins
    $DB_SQL = 'SELECT 1 ';
    $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
    $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
    $DB_VAR = array(
      ':groupe_id'  => $groupe_id,
      ':periode_id' => $periode_id,
    );
    if( DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) )
    {
      $DB_SQL = 'UPDATE sacoche_jointure_groupe_periode ';
      $DB_SQL.= 'SET jointure_date_debut=:date_debut , jointure_date_fin=:date_fin ';
      $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
      $DB_VAR = array(
        ':groupe_id'  => $groupe_id,
        ':periode_id' => $periode_id,
        ':date_debut' => $date_debut_mysql,
        ':date_fin'   => $date_fin_mysql,
      );
    }
    else
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_groupe_periode (groupe_id,periode_id,jointure_date_debut,jointure_date_fin) ';
      $DB_SQL.= 'VALUES(:groupe_id,:periode_id,:date_debut,:date_fin)';
      $DB_VAR = array(
        ':groupe_id'  => $groupe_id,
        ':periode_id' => $periode_id,
        ':date_debut' => $date_debut_mysql,
        ':date_fin'   => $date_fin_mysql,
      );
    }
  }
  else
  {
    // Retirer une liaison ; on ne supprime pas les données des bulletins éventuels associés, par précaution en cas de fausse manoeuvre, et surtout car ils peuvent être accessibles depuis un autre groupe...
    $DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
    $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
    $DB_VAR = array(
      ':groupe_id'  => $groupe_id,
      ':periode_id' => $periode_id,
    );
  }
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_liaisons_groupe_periode
 *
 * Retirer toutes les liaisons ; on ne supprime pas les données des bulletins éventuels associés, par précaution en cas de fausse manoeuvre, et surtout car ils peuvent être accessibles depuis un autre groupe...
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_liaisons_groupe_periode()
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_groupe_periode ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_periode
 *
 * @param int $periode_id
 * @return void
 */
public static function DB_supprimer_periode($periode_id)
{
  $tab_tables = array( 'sacoche_periode' , 'sacoche_jointure_groupe_periode', 'sacoche_officiel_saisie' , 'sacoche_officiel_assiduite' );
  $DB_VAR = array(':periode_id'=>$periode_id);
  foreach( $tab_tables as $table )
  {
    $DB_SQL = 'DELETE FROM '.$table.' WHERE periode_id=:periode_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

}
?>