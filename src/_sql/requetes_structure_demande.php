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
// Ces méthodes concernent des actions en lien avec les demandes d'évaluations.

class DB_STRUCTURE_DEMANDE extends DB
{

/**
 * Lister les demandes d'évaluation d'un élève donné
 *
 * @param int    $eleve_id   id de l'élève
 * @return array
 */
public static function DB_lister_demandes_eleve($eleve_id)
{
  $DB_SQL = 'SELECT sacoche_demande.*, ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'item_id , item_nom , item_lien , sacoche_matiere.matiere_id AS matiere_id  , matiere_nom , ';
  $DB_SQL.= 'prof_id , user_genre , user_nom , user_prenom ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_referentiel_domaine.matiere_id=sacoche_matiere.matiere_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_demande.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'ORDER BY sacoche_demande.matiere_id ASC, niveau_ref ASC, domaine_code ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_demandes_prof
 *
 * @param int    $prof_id           id du prof ; si 0 alors chercher les demandes pour tous les profs
 * @param int    $matiere_id        id de la matière du prof ; si 0 alors chercher parmi toutes les matières du prof
 * @param int    $listing_user_id   id des élèves du prof séparés par des virgules
 * @return array
 */
public static function DB_lister_demandes_prof( $prof_id , $matiere_id , $listing_user_id )
{
  $select_matiere = ($matiere_id) ? '' : 'matiere_nom, ';
  $order_matiere  = ($matiere_id) ? '' : 'matiere_nom ASC, ';
  $group_by       = ($prof_id)    ? '' : 'GROUP BY demande_id ';
  $DB_SQL = 'SELECT sacoche_demande.*, '.$select_matiere;
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'item_nom, user_nom, user_prenom, prof_id ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_demande.prof_id=sacoche_user.user_id ';
  if($matiere_id)
  {
    $where_prof = ($prof_id) ? 'AND prof_id IN(0,'.$prof_id.')' : '' ;
    $DB_SQL.= 'WHERE eleve_id IN('.$listing_user_id.') '.$where_prof.' AND sacoche_demande.matiere_id=:matiere_id ';
  }
  else
  {
    $where_prof = ($prof_id) ? 'AND prof_id IN(0,'.$prof_id.') AND sacoche_jointure_user_matiere.user_id=:prof_id' : '' ;
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere ON sacoche_demande.matiere_id=sacoche_jointure_user_matiere.matiere_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_jointure_user_matiere.matiere_id=sacoche_matiere.matiere_id ';
    $DB_SQL.= 'WHERE eleve_id IN('.$listing_user_id.') '.$where_prof.' ';
  }
  $DB_SQL.= $group_by;
  $DB_SQL.= 'ORDER BY '.$order_matiere.'niveau_ref ASC, domaine_code ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':matiere_id' => $matiere_id,
    ':prof_id'    => $prof_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les résultats pour 1 élève donné, pour 1 item matière donné
 *
 * @param int    $eleve_id
 * @param int    $item_id
 * @param string $date_mysql_debut
 * @return array
 */
public static function DB_lister_result_eleve_item( $eleve_id , $item_id , $date_mysql_debut )
{
  $sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
  $DB_SQL = 'SELECT saisie_note AS note , saisie_date AS date , referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id=:item_id AND saisie_note!="PA" '.$sql_debut;
  $DB_SQL.= 'ORDER BY saisie_date ASC, devoir_id ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  $DB_VAR = array(
    ':eleve_id'   => $eleve_id,
    ':item_id'    => $item_id,
    ':date_debut' => $date_mysql_debut,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_item_popularite
 * Calculer pour chaque item sa popularité, i.e. le nb de demandes pour les élèves concernés.
 *
 * @param string $listing_demande_id   id des demandes séparés par des virgules
 * @param string $listing_user_id      id des élèves séparés par des virgules
 * @return array   [i]=>array('item_id','popularite')
 */
public static function DB_recuperer_item_popularite( $listing_demande_id , $listing_user_id )
{
  $DB_SQL = 'SELECT item_id , COUNT(item_id) AS popularite ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') AND eleve_id IN('.$listing_user_id.') ';
  $DB_SQL.= 'GROUP BY item_id ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner une liste de professeurs attachés à un élève identifié et une matière donnée.
 *
 * @param int $eleve_id
 * @param int $eleve_classe_id
 * @param int $matiere_id
 * @return array
 */
public static function DB_recuperer_professeurs_eleve_matiere( $eleve_id , $eleve_classe_id , $matiere_id )
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($eleve_classe_id), donc on commence par récupérer les groupes éventuels associés à l'élève
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type2 ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(
    ':user_id' => $eleve_id,
    ':type2'   => 'groupe',
  );
  $liste_groupe_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( (!$eleve_classe_id) && (!$liste_groupe_id) )
  {
    // élève sans classe et sans groupe
    return FALSE;
  }
  if(!$liste_groupe_id)
  {
    $liste_groupes = $eleve_classe_id;
  }
  elseif(!$eleve_classe_id)
  {
    $liste_groupes = $liste_groupe_id;
  }
  else
  {
    $liste_groupes = $eleve_classe_id.','.$liste_groupe_id;
  }
  // Maintenant qu'on a la matière et la classe / les groupes, on cherche les profs à la fois dans sacoche_jointure_user_matiere et sacoche_jointure_user_groupe .
  // On part de sacoche_jointure_user_matiere qui ne contient que des profs.
  $DB_SQL = 'SELECT DISTINCT(user_id), user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id AND groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer le nombre de demandes d'évaluations autorisées par matière
 *
 * @param int   $matiere_id
 * @return int
 */
public static function DB_recuperer_demandes_autorisees_matiere($matiere_id)
{
  $DB_SQL = 'SELECT matiere_nb_demandes ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les informations relatives à un item donné
 *
 * @param int   $item_id
 * @return array
 */
public static function DB_recuperer_item_infos($item_id)
{
  $DB_SQL = 'SELECT item_nom, item_cart, ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'matiere_ref ';
  $DB_SQL.= 'FROM sacoche_referentiel_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE item_id=:item_id ';
  $DB_VAR = array(':item_id'=>$item_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_demandes_statut
 *
 * @param string $listing_demande_id   id des demandes séparées par des virgules
 * @param string $statut               'prof' | 'eleve'
 * @param string $message              facultatif
 * @return void
 */
public static function DB_modifier_demandes_statut( $listing_demande_id , $statut , $message )
{
  $message_complementaire = ($message) ? "\r\n\r\n".To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE'])."\r\n".$message : '' ;
  $DB_SQL = 'UPDATE sacoche_demande ';
  $DB_SQL.= 'SET demande_statut=:demande_statut, demande_messages=CONCAT(demande_messages,:message_complementaire) ';
  $DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
  $DB_VAR = array(
    ':demande_statut'         => $statut,
    ':message_complementaire' => $message_complementaire
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_demande_score
 *
 * @param int      $demande_id
 * @param int|null $demande_score
 * @return void
 */
public static function DB_modifier_demande_score( $demande_id , $demande_score )
{
  $DB_SQL = 'UPDATE sacoche_demande ';
  $DB_SQL.= 'SET demande_score=:demande_score ';
  $DB_SQL.= 'WHERE demande_id=:demande_id ';
  $DB_VAR = array(
    ':demande_id'    => $demande_id,
    ':demande_score' => $demande_score
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de demandes d'évaluations déjà formulées par un élève pour chaque matière
 *
 * @param int   $eleve_id
 * @param int   $matiere_id
 * @return int
 */
public static function DB_compter_demandes_formulees_eleve_matiere( $eleve_id , $matiere_id )
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND matiere_id=:matiere_id ';
  $DB_SQL.= 'GROUP BY matiere_id';
  $DB_VAR = array(
    ':eleve_id'   => $eleve_id,
    ':matiere_id' => $matiere_id,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Tester si un élève a déjà formulé une demande d'évaluation pour un item donné
 *
 * @param int    $eleve_id
 * @param int    $matiere_id
 * @param int    $item_id
 * @return int
 */
public static function DB_tester_demande_existante( $eleve_id , $matiere_id , $item_id )
{
  $DB_SQL = 'SELECT demande_id ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND matiere_id=:matiere_id AND item_id=:item_id ';
  $DB_VAR = array(
    ':eleve_id'   => $eleve_id,
    ':matiere_id' => $matiere_id,
    ':item_id'    => $item_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une demande d'évaluation
 *
 * @param int      $eleve_id
 * @param int      $matiere_id
 * @param int      $item_id
 * @param int      $prof_id
 * @param string   $debut_date
 * @param int|null $demande_score
 * @param string   $demande_statut
 * @param string   $message
 * @param string   $demande_doc
 * @return int
 */
public static function DB_ajouter_demande( $eleve_id , $matiere_id , $item_id , $prof_id , $debut_date , $demande_score , $demande_statut , $message , $demande_doc )
{
  $demande_messages = ($message) ? To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE)."\r\n".$message : '' ;
  $DB_SQL = 'INSERT INTO sacoche_demande( eleve_id, matiere_id, item_id, prof_id, periode_debut_date, demande_date, demande_score, demande_statut, demande_messages, demande_doc) ';
  $DB_SQL.= 'VALUES                     (:eleve_id,:matiere_id,:item_id,:prof_id,:periode_debut_date,        NOW(),:demande_score,:demande_statut,:demande_messages,:demande_doc)';
  $DB_VAR = array(
    ':eleve_id'           => $eleve_id,
    ':matiere_id'         => $matiere_id,
    ':item_id'            => $item_id,
    ':prof_id'            => $prof_id,
    ':periode_debut_date' => $debut_date,
    ':demande_score'      => $demande_score,
    ':demande_statut'     => $demande_statut,
    ':demande_messages'   => $demande_messages,
    ':demande_doc'        => $demande_doc,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_demande_precise
 *
 * @param int   $demande_id
 * @return int
 */
public static function DB_supprimer_demande_precise_id($demande_id)
{
  $DB_SQL = 'DELETE FROM sacoche_demande ';
  $DB_SQL.= 'WHERE demande_id=:demande_id ';
  $DB_VAR = array(':demande_id'=>$demande_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_demande_precise
 *
 * @param int   $eleve_id
 * @param int   $item_id
 * @return void
 */
public static function DB_supprimer_demande_precise_eleve_item($eleve_id,$item_id)
{
  $DB_SQL = 'DELETE FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id=:item_id ';
  $DB_VAR = array(
    ':eleve_id' => $eleve_id,
    ':item_id'  => $item_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les demandes d'évaluations listées (correspondant à une évaluation)
 *
 * @param string   $listing_demande_id   id des demandes séparées par des virgules
 * @return void
 */
public static function DB_supprimer_demandes_devoir($listing_demande_id)
{
  $DB_SQL = 'DELETE FROM sacoche_demande ';
  $DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

}
?>