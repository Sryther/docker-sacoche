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
// Ces méthodes sont en rapport avec les matières (tables "sacoche_niveau" + "sacoche_matiere_famille" + "sacoche_jointure_user_matiere").

class DB_STRUCTURE_NIVEAU extends DB
{

/**
 * lister_niveaux
 *
 * @param bool   is_specifique
 * @return array
 */
public static function DB_lister_niveaux($is_specifique)
{
  $DB_SQL = 'SELECT niveau_id, niveau_ref, niveau_nom ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($is_specifique) ? 'WHERE niveau_id>'.ID_NIVEAU_PARTAGE_MAX.' ' : 'WHERE niveau_actif=1 AND niveau_id<='.ID_NIVEAU_PARTAGE_MAX.' ' ;
  $DB_SQL.= ($is_specifique) ? 'ORDER BY niveau_nom ASC' : 'ORDER BY niveau_ordre ASC' ;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_niveaux_etablissement
 *
 * @param bool $with_particuliers
 * @return array
 */
public static function DB_lister_niveaux_etablissement($with_particuliers)
{
  $DB_SQL = 'SELECT niveau_id, niveau_ordre, niveau_ref, code_mef, niveau_nom ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($with_particuliers) ? '' : 'LEFT JOIN sacoche_niveau_famille USING (niveau_famille_id) ';
  $DB_SQL.= 'WHERE niveau_actif=1 ';
  $DB_SQL.= ($with_particuliers) ? '' : 'AND niveau_famille_categorie=3 ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_niveaux_famille
 *
 * @param int   niveau_famille_id
 * @return array
 */
public static function DB_lister_niveaux_famille($niveau_famille_id)
{
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
  $DB_SQL = 'SELECT niveau_id, niveau_ref, niveau_nom, niveau_actif ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($niveau_famille_id==ID_FAMILLE_NIVEAU_USUEL) ? 'WHERE niveau_usuel=1 ' : 'WHERE niveau_famille_id='.$niveau_famille_id.' '.$tab_sql[$niveau_famille_id] ;
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, niveau_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_niveau_reference
 *
 * @param string $niveau_ref
 * @param int    $niveau_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_niveau_reference($niveau_ref,$niveau_id=FALSE)
{
  $DB_SQL = 'SELECT niveau_id ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'WHERE niveau_ref=:niveau_ref ';
  $DB_SQL.= ($niveau_id) ? 'AND niveau_id!=:niveau_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':niveau_ref' => $niveau_ref,
    ':niveau_id'  => $niveau_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_niveau_specifique
 *
 * @param string $niveau_ref
 * @param string $niveau_nom
 * @return int
 */
public static function DB_ajouter_niveau_specifique($niveau_ref,$niveau_nom)
{
  $DB_SQL = 'INSERT INTO sacoche_niveau(niveau_actif, niveau_famille_id, niveau_ordre, niveau_ref, code_mef, niveau_nom) ';
  $DB_SQL.= 'VALUES(                   :niveau_actif,:niveau_famille_id,:niveau_ordre,:niveau_ref,:code_mef,:niveau_nom)';
  $DB_VAR = array(
    ':niveau_actif'      => 1,
    ':niveau_famille_id' => 0,
    ':niveau_ordre'      => 999,
    ':niveau_ref'        => $niveau_ref,
    ':code_mef'          => "",
    ':niveau_nom'        => $niveau_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_niveau_partage
 *
 * @param int    $niveau_id
 * @param int    $niveau_actif   (0/1)
 * @return void
 */
public static function DB_modifier_niveau_partage($niveau_id,$niveau_actif)
{
  $DB_SQL = 'UPDATE sacoche_niveau ';
  $DB_SQL.= 'SET niveau_actif=:niveau_actif ';
  $DB_SQL.= 'WHERE niveau_id=:niveau_id ';
  $DB_VAR = array(
    ':niveau_id'    => $niveau_id,
    ':niveau_actif' => $niveau_actif,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // On laisse les référentiels en sommeil, au cas où...
}

/**
 * modifier_niveau_specifique
 *
 * @param int    $niveau_id
 * @param string $niveau_ref
 * @param string $niveau_nom
 * @return void
 */
public static function DB_modifier_niveau_specifique($niveau_id,$niveau_ref,$niveau_nom)
{
  $DB_SQL = 'UPDATE sacoche_niveau ';
  $DB_SQL.= 'SET niveau_ref=:niveau_ref,niveau_nom=:niveau_nom ';
  $DB_SQL.= 'WHERE niveau_id=:niveau_id ';
  $DB_VAR = array(
    ':niveau_id'  => $niveau_id,
    ':niveau_ref' => $niveau_ref,
    ':niveau_nom' => $niveau_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un niveau spécifique
 *
 * @param int $niveau_id
 * @return void
 */
public static function DB_supprimer_niveau_specifique($niveau_id)
{
  $DB_SQL = 'DELETE sacoche_niveau, sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire ON sacoche_niveau.niveau_id=sacoche_jointure_message_destinataire.destinataire_id AND destinataire_type="niveau" ';
  $DB_SQL.= 'WHERE niveau_id=:niveau_id ';
  $DB_VAR = array(':niveau_id'=>$niveau_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Il faut aussi supprimer les référentiels associés, et donc tous les scores associés (orphelins du niveau)
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_referentiels( 'niveau_id' , $niveau_id );
}

}
?>