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
// Ces méthodes sont en rapport avec les matières (tables "sacoche_matiere" + "sacoche_matiere_famille" + "sacoche_jointure_user_matiere").

class DB_STRUCTURE_MATIERE extends DB
{

/**
 * lister_matieres_famille
 *
 * @param int   famille_id
 * @return array
 */
public static function DB_lister_matieres_famille($famille_id)
{
  $DB_SQL = 'SELECT matiere_id, matiere_ref, matiere_nom, matiere_active ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= ($famille_id==ID_FAMILLE_MATIERE_USUELLE) ? 'WHERE matiere_usuelle=1 ' : 'WHERE matiere_famille_id='.$famille_id.' ' ;
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_matiere_motclef
 *
 * @param string   findme
 * @return array
 */
public static function DB_lister_matiere_motclef($findme)
{
  $DB_SQL = 'SELECT matiere_id, matiere_ref, matiere_nom, matiere_active, matiere_famille_nom, ';
  $DB_SQL.= 'MATCH(matiere_nom) AGAINST(:matiere_nom) AS score ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere_famille USING (matiere_famille_id) ';
  $DB_SQL.= 'WHERE matiere_id<='.ID_MATIERE_PARTAGEE_MAX.' AND MATCH(matiere_nom) AGAINST(:matiere_nom)';
  $DB_VAR = array(':matiere_nom'=>$findme);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_matieres
 *
 * @param bool   is_specifique
 * @return array
 */
public static function DB_lister_matieres($is_specifique)
{
  $DB_SQL = 'SELECT matiere_id, matiere_ref, matiere_nom ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= ($is_specifique) ? 'WHERE matiere_id>'.ID_MATIERE_PARTAGEE_MAX.' ' : 'WHERE matiere_active=1 AND matiere_id<='.ID_MATIERE_PARTAGEE_MAX.' ' ;
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_matieres_etablissement
 *
 * @param bool   $order_by_name      si FALSE, prendre le champ matiere_ordre
 * @param bool   $with_siecle        si TRUE, prendre aussi les matières trouvées dans un import SIECLE
 * @return array
 */
public static function DB_lister_matieres_etablissement( $order_by_name , $with_siecle=FALSE )
{
  $matiere_nom  = ($with_siecle) ? 'CONCAT( REPLACE( REPLACE(matiere_siecle,"1","SIECLE") , "0","REFÉRENTIEL AUTRE" ) , " - ", matiere_nom ) AS matiere_nom ' : 'matiere_nom ' ;
  $where_siecle = ($with_siecle) ? ' OR matiere_siecle=1 ' : '' ;
  $DB_SQL = 'SELECT matiere_id, matiere_nb_demandes, matiere_ordre, matiere_ref, '.$matiere_nom;
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_active=1 '.$where_siecle;
  $DB_SQL.= ($order_by_name) ? 'ORDER BY matiere_nom ASC' : 'ORDER BY matiere_ordre ASC, matiere_nom ASC' ;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_matiere_siecle
 *
 * @param void
 * @return array
 */
public static function DB_lister_matiere_siecle()
{
  $DB_SQL = 'SELECT matiere_id , matiere_ref ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_siecle=1 ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_jointure_professeurs_matieres
 *
 * @param void
 * @return array
 */
public static function DB_lister_jointure_professeurs_matieres()
{
  $DB_SQL = 'SELECT user_id, matiere_id, jointure_coord ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC, user_nom ASC, user_prenom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_matiere_reference
 *
 * @param string $matiere_ref
 * @param int    $matiere_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_matiere_reference($matiere_ref,$matiere_id=FALSE)
{
  $DB_SQL = 'SELECT matiere_id ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_ref=:matiere_ref ';
  $DB_SQL.= ($matiere_id) ? 'AND matiere_id!=:matiere_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':matiere_ref' => $matiere_ref,
    ':matiere_id'  => $matiere_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_matiere_siecle
 *
 * @param void
 * @return bool
 */
public static function DB_tester_matiere_siecle()
{
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_siecle=1 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_matiere_specifique
 *
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @param string $matiere_code si transmis, matière issue d'un import SIECLE
 * @return int
 */
public static function DB_ajouter_matiere_specifique( $matiere_ref , $matiere_nom , $matiere_code="" )
{
  $matiere_active = ($matiere_code) ? 0 : 1 ;
  $matiere_siecle = ($matiere_code) ? 1 : 0 ;
  $DB_SQL = 'INSERT INTO sacoche_matiere(matiere_active, matiere_siecle, matiere_usuelle, matiere_famille_id, matiere_nb_demandes, matiere_ordre, matiere_code, matiere_ref, matiere_nom) ';
  $DB_SQL.= 'VALUES(                    :matiere_active,:matiere_siecle,:matiere_usuelle,:matiere_famille_id,:matiere_nb_demandes,:matiere_ordre,:matiere_code,:matiere_ref,:matiere_nom)';
  $DB_VAR = array(
    ':matiere_active'      => $matiere_active,
    ':matiere_siecle'      => $matiere_siecle,
    ':matiere_usuelle'     => 0,
    ':matiere_famille_id'  => 0,
    ':matiere_nb_demandes' => 0,
    ':matiere_ordre'       => 255,
    ':matiere_code'        => $matiere_code,
    ':matiere_ref'         => $matiere_ref,
    ':matiere_nom'         => $matiere_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_matiere_partagee
 *
 * @param int    $matiere_id
 * @param int    $matiere_active   (0/1)
 * @return void
 */
public static function DB_modifier_matiere_partagee($matiere_id,$matiere_active)
{
  $DB_SQL = 'UPDATE sacoche_matiere ';
  $DB_SQL.= 'SET matiere_active=:matiere_active ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(
    ':matiere_id'     => $matiere_id,
    ':matiere_active' => $matiere_active,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(!$matiere_active)
  {
    // On supprime aussi les jointures avec les enseignants.
    // Mais on laisse les référentiels / le livret scolaire / les bilans officiels en sommeil, au cas où...
    $DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
    $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
    $DB_VAR = array(':matiere_id'=>$matiere_id);
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * modifier_matiere_specifique
 *
 * @param int    $matiere_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return void
 */
public static function DB_modifier_matiere_specifique($matiere_id,$matiere_ref,$matiere_nom)
{
  $DB_SQL = 'UPDATE sacoche_matiere ';
  $DB_SQL.= 'SET matiere_ref=:matiere_ref,matiere_nom=:matiere_nom ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(
    ':matiere_id'  => $matiere_id,
    ':matiere_ref' => $matiere_ref,
    ':matiere_nom' => $matiere_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_matiere_ordre
 *
 * @param int   $matiere_id
 * @param int   $matiere_ordre
 * @return void
 */
public static function DB_modifier_matiere_ordre($matiere_id,$matiere_ordre)
{
  $DB_SQL = 'UPDATE sacoche_matiere ';
  $DB_SQL.= 'SET matiere_ordre=:matiere_ordre ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(
    ':matiere_id'    => $matiere_id,
    ':matiere_ordre' => $matiere_ordre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_matiere_ordre
 *
 * @param int   $matiere_id
 * @param int   $matiere_siecle
 * @return void
 */
public static function DB_modifier_matiere_siecle($matiere_id,$matiere_siecle)
{
  $DB_SQL = 'UPDATE sacoche_matiere ';
  $DB_SQL.= 'SET matiere_siecle=:matiere_siecle ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(
    ':matiere_id'     => $matiere_id,
    ':matiere_siecle' => $matiere_siecle,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_professeur_coordonnateur
 *
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public static function DB_modifier_liaison_professeur_coordonnateur($user_id,$matiere_id,$etat)
{
  $coord = ($etat) ? 1 : 0 ;
  $DB_SQL = 'UPDATE sacoche_jointure_user_matiere ';
  $DB_SQL.= 'SET jointure_coord=:coord ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
  $DB_VAR = array(
    ':user_id'    => $user_id,
    ':matiere_id' => $matiere_id,
    ':coord'      => $coord,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_professeur_matiere
 *
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public static function DB_modifier_liaison_professeur_matiere($user_id,$matiere_id,$etat)
{
  if($etat)
  {
    // On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd la valeur de jointure_coord.
    $DB_SQL = 'SELECT 1 ';
    $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
    $DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
    $DB_VAR = array(
      ':user_id'    => $user_id,
      ':matiere_id' => $matiere_id,
    );
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    if(empty($DB_ROW))
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_user_matiere (user_id,matiere_id,jointure_coord) ';
      $DB_SQL.= 'VALUES(:user_id,:matiere_id,:coord)';
      $DB_VAR = array(
        ':user_id'    => $user_id,
        ':matiere_id' => $matiere_id,
        ':coord'      => 0,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
  else
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
    $DB_SQL.= 'WHERE user_id=:user_id AND matiere_id=:matiere_id ';
    $DB_VAR = array(
      ':user_id'    => $user_id,
      ':matiere_id' => $matiere_id,
    );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * Supprimer une matière spécifique
 *
 * Le ménage dans sacoche_livret_jointure_referentiel est un peu pénible ici, il est effectué ailleurs.
 *
 * @param int $matiere_id
 * @return void
 */
public static function DB_supprimer_matiere_specifique($matiere_id)
{
  $tab_tables = array(
    'sacoche_matiere' ,
    'sacoche_jointure_user_matiere' ,
    'sacoche_livret_jointure_ap_prof' ,
    'sacoche_livret_jointure_epi_prof' ,
  );
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  foreach( $tab_tables as $table )
  {
    $DB_SQL = 'DELETE FROM '.$table.' WHERE matiere_id=:matiere_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // Il faut aussi supprimer les référentiels associés, et donc tous les scores associés (orphelins de la matière)
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_referentiels( 'matiere_id' , $matiere_id );
}

/**
 * Déplacer les référentiels d'une matière vers une autre, après vérification que c'est possible (matière de destination vierge de données)
 *
 * @param int   $matiere_id_avant
 * @param int   $matiere_id_apres
 * @return bool
 */
public static function DB_deplacer_referentiel_matiere($matiere_id_avant,$matiere_id_apres)
{
  // Vérification que c'est possible (matière de destination vierge de données)
  $nb_pbs = 0;
  $tab_tables = array(
    'sacoche_referentiel'=>'matiere_id',
    'sacoche_referentiel_domaine'=>'matiere_id',
  );
  foreach($tab_tables as $table_nom => $table_champ)
  {
    $nb_pbs += DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT COUNT(*) AS nombre FROM '.$table_nom.' WHERE '.$table_champ.'='.$matiere_id_apres );
  }
  if($nb_pbs)
  {
    return FALSE;
  }
  // Déplacer les référentiels d'une matière vers une autre
  $tab_tables = array(
    'sacoche_referentiel'              => 'matiere_id',
    'sacoche_referentiel_domaine'      => 'matiere_id',
    'sacoche_demande'                  => 'matiere_id',
    'sacoche_livret_jointure_ap_prof'  => 'matiere_id',
    'sacoche_livret_jointure_epi_prof' => 'matiere_id',
    'sacoche_officiel_saisie'          => 'rubrique_id',
    'sacoche_livret_saisie'            => 'rubrique_id',
  );
  foreach($tab_tables as $table_nom => $table_champ)
  {
    $where_add = ($table_nom=='sacoche_livret_saisie') ? ' AND rubrique_type="eval"' : '' ;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET '.$table_champ.'='.$matiere_id_apres.' WHERE '.$table_champ.'='.$matiere_id_avant.$where_add );
  }
  $tab_champs = array(
    'livret_rubrique_ou_matiere_id',
    'element_id',
  );
  foreach($tab_champs as $champ)
  {
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel LEFT JOIN sacoche_livret_page ON sacoche_livret_jointure_referentiel.livret_rubrique_type = sacoche_livret_page.livret_page_rubrique_type SET '.$champ.' = '.$matiere_id_apres.' WHERE '.$champ.' = '.$matiere_id_avant.' AND livret_page_rubrique_join="matiere" ' );
  }
  // Pour "sacoche_jointure_user_matiere" c'est un peu particulier : il ne faut pas déclencher d'erreur si le user est déjà rattaché à la nouvelle matière.
  // UPDATE ... ON DUPLICATE KEY DELETE ...  n'existe pas, il faut s'y prendre en deux fois avec UPDATE IGNORE ... puis DELETE ...
  $table_nom   = 'sacoche_jointure_user_matiere';
  $table_champ = 'matiere_id';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE IGNORE '.$table_nom.' SET '.$table_champ.'='.$matiere_id_apres.' WHERE '.$table_champ.'='.$matiere_id_avant );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM '.$table_nom.' WHERE '.$table_champ.'='.$matiere_id_avant );
  // On termine avec l'état de partage
  if( ($matiere_id_avant>ID_MATIERE_PARTAGEE_MAX) && ($matiere_id_apres<=ID_MATIERE_PARTAGEE_MAX) )
  {
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_etat="non" WHERE matiere_id='.$matiere_id_apres.' AND referentiel_partage_etat="hs"' );
  }
  if( ($matiere_id_avant<=ID_MATIERE_PARTAGEE_MAX) && ($matiere_id_apres>ID_MATIERE_PARTAGEE_MAX) )
  {
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_etat="hs" WHERE matiere_id='.$matiere_id_apres.' AND referentiel_partage_etat!="hs"' );
  }
  return TRUE;
}

}
?>