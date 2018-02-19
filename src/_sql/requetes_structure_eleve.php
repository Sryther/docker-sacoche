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
// Ces méthodes ne concernent que les élèves (et les parents).

class DB_STRUCTURE_ELEVE extends DB
{

/**
 * compter_demandes_evaluation
 *
 * @param int  $eleve_id
 * @return array
 */
public static function DB_compter_demandes_evaluation($eleve_id)
{
  $DB_SQL = 'SELECT demande_statut, COUNT(demande_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'GROUP BY demande_statut ';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
}

/**
 * Récupérer les informations relatives à un devoir
 *
 * Pas réussi à récupérer en même temps la liste des profs associés en écriture au devoir car
 * une jointure sur LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id)
 * pour avoir GROUP_CONCAT(prof_id SEPARATOR ",") AS partage_id_listing
 * avec GROUP BY devoir_id
 * et la condition AND ( ( prof_id IS NULL ) OR ( jointure_droit != :jointure_droit_non ) )
 * fonctionne si aucun prof n'est associé à l'éval
 * ou si au moin sun prof y est associé en saisie
 * mais pas si tous les profs associés l'y sont uniquement en visualisation !!!
 * (dans ce cas la requête retourne un tableau vide...)
 *
 * @param int   $devoir_id
 * @return array
 */
public static function DB_recuperer_devoir_infos($devoir_id)
{
  $DB_SQL = 'SELECT proprio_id, devoir_date, devoir_info, devoir_visible_date, devoir_autoeval_date ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array( ':devoir_id' => $devoir_id );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * En complément de DB_recuperer_devoir_infos()
 *
 * @param int   $devoir_id
 * @return array
 */
public static function DB_lister_devoir_profs_droit_saisie($devoir_id)
{
  $DB_SQL = 'SELECT prof_id ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_prof ';
  $DB_SQL.= 'WHERE devoir_id = :devoir_id AND jointure_droit != :jointure_droit_non ';
  $DB_VAR = array(
    ':devoir_id'          => $devoir_id,
    ':jointure_droit_non' => 'voir',
  );
  return DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
}

/**
 * Retourner les résultats pour un élève, pour des items donnés
 *
 * @param int    $eleve_id
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $user_profil_type
 * @return array
 */
public static function DB_lister_result_eleve_items( $eleve_id , $liste_item_id , $user_profil_type )
{
  // Cette fonction peut être appelée avec un autre profil.
  $sql_view = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND saisie_visible_date<=NOW() ' : '' ;
  $DB_SQL = 'SELECT item_id, saisie_note AS note ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id IN('.$liste_item_id.') AND saisie_note!="PA" '.$sql_view;
  $DB_SQL.= 'ORDER BY saisie_date ASC, devoir_id ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant la classe ou les groupes d'un élève sur une période donnée
 *
 * @param int    $eleve_id
 * @param int    $prof_id
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $user_profil_type
 * @return array
 */
public static function DB_lister_devoirs_eleve( $eleve_id , $prof_id , $date_debut_mysql , $date_fin_mysql , $user_profil_type )
{
  // Récupérer classe et groupes de l'élève
  $DB_SQL = 'SELECT eleve_classe_id, GROUP_CONCAT(groupe_id SEPARATOR ",") AS eleve_groupes_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(':user_id'=>$eleve_id);
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( !$DB_ROW['eleve_classe_id'] && !$DB_ROW['eleve_groupes_id'] )
  {
    return NULL;
  }
  else
  {
    $virgule = ( $DB_ROW['eleve_classe_id'] && $DB_ROW['eleve_groupes_id'] ) ? ',' : '' ;
    $listing_groupes = $DB_ROW['eleve_classe_id'].$virgule.$DB_ROW['eleve_groupes_id'];
    // Cette fonction peut être appelée avec un autre profil.
    $sql_view   = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND devoir_visible_date<=NOW() ' : '' ;
    $join_prof  = ($prof_id) ? 'LEFT JOIN sacoche_jointure_devoir_prof ON ( sacoche_devoir.devoir_id = sacoche_jointure_devoir_prof.devoir_id) ' : '' ;
    $where_prof = ($prof_id) ? 'AND ( sacoche_devoir.proprio_id=:proprio_id OR sacoche_jointure_devoir_prof.prof_id=:prof_id ) ' : '' ;
    $DB_SQL = 'SELECT sacoche_devoir.* , ';
    $DB_SQL.= 'sacoche_user.user_genre AS prof_genre , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom , ';
    $DB_SQL.= 'jointure_texte , jointure_audio , ';
    $DB_SQL.= 'COUNT(DISTINCT sacoche_jointure_devoir_item.item_id) AS items_nombre ';
    $DB_SQL.= 'FROM sacoche_devoir ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_eleve ON ( sacoche_devoir.devoir_id=sacoche_jointure_devoir_eleve.devoir_id AND sacoche_jointure_devoir_eleve.eleve_id=:eleve_id ) ';
    $DB_SQL.= $join_prof;
    $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item ON ( sacoche_devoir.devoir_id = sacoche_jointure_devoir_item.devoir_id ) ';
    $DB_SQL.= 'WHERE groupe_id IN('.$listing_groupes.') '.$where_prof.'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" '.$sql_view ;
    $DB_SQL.= 'GROUP BY sacoche_devoir.devoir_id ';
    $DB_SQL.= 'ORDER BY devoir_date DESC, sacoche_devoir.devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
    $DB_VAR = array(
      ':eleve_id'   => $eleve_id,
      ':proprio_id' => $prof_id,
      ':prof_id'    => $prof_id,
    );
    return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * lister_nb_saisies_par_evaluation
 *
 * @param int      $eleve_id
 * @param string   $listing_devoir_id   soit une chaine de devoir (string), soit un devoir unique (int)
 * @return array   tableau (devoir_id;saisies_nombre)
 */
public static function DB_lister_nb_saisies_par_evaluation( $eleve_id , $listing_devoir_id )
{
  $DB_SQL = 'SELECT COUNT(saisie_note) AS saisies_nombre, devoir_id ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE devoir_id IN('.$listing_devoir_id.') AND eleve_id=:eleve_id ';
  $DB_SQL.= 'AND saisie_note!="PA" ';
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_VAR = array(
    ':eleve_id'          => $eleve_id,
    ':listing_devoir_id' => $listing_devoir_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève donné sur les derniers jours
 *
 * @param int    $eleve_id
 * @param int    $nb_jours
 * @return array
 */
public static function DB_lister_derniers_devoirs_eleve_avec_notes_saisies( $eleve_id , $nb_jours )
{
  $sql_view = 'AND devoir_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $DB_SQL = 'SELECT devoir_id , devoir_date , devoir_info , sacoche_user.user_genre AS prof_genre , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND saisie_note!="PA" ';
  $DB_SQL.= 'AND devoir_date > DATE_SUB( NOW() , INTERVAL :nb_jours DAY ) '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(
    ':eleve_id' => $eleve_id,
    ':nb_jours' => $nb_jours,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève donné comportant une auto-évaluation en cours
 *
 * @param int    $eleve_id
 * @param int    $classe_id   id de la classe de l'élève ; en effet sacoche_jointure_user_groupe ne contient que les liens aux groupes, donc il faut tester aussi la classe
 * @return array
 */
public static function DB_lister_devoirs_eleve_avec_autoevaluation_en_cours( $eleve_id , $classe_id )
{
  $sql_view = 'AND devoir_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $where_classe = ($classe_id) ? 'sacoche_devoir.groupe_id='.$classe_id.' OR ' : '';
  $DB_SQL = 'SELECT devoir_id , devoir_date , devoir_info , sacoche_user.user_genre AS prof_genre , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM  sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ('.$where_classe.'sacoche_jointure_user_groupe.user_id=:eleve_id) ';
  $DB_SQL.= 'AND devoir_autoeval_date IS NOT NULL AND devoir_autoeval_date >= NOW() '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations à venir concernant un élève donné
 *
 * @param int    $eleve_id
 * @param int    $classe_id   id de la classe de l'élève ; en effet sacoche_jointure_user_groupe ne contient que les liens aux groupes, donc il faut tester aussi la classe
 * @return array
 */
public static function DB_lister_prochains_devoirs_eleve( $eleve_id , $classe_id )
{
  $sql_view = 'AND devoir_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $where_classe = ($classe_id) ? 'sacoche_devoir.groupe_id='.$classe_id.' OR ' : '';
  $DB_SQL = 'SELECT devoir_id , devoir_date , devoir_info , sacoche_user.user_genre AS prof_genre , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM  sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ('.$where_classe.'sacoche_jointure_user_groupe.user_id=:eleve_id) ';
  $DB_SQL.= 'AND devoir_date>NOW() '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date ASC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(
    ':eleve_id' => $eleve_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les résultats concernant un élève sur les derniers jours
 *
 * @param int    $eleve_id
 * @param int    $nb_jours
 * @return array
 */
public static function DB_lister_derniers_resultats_eleve( $eleve_id , $nb_jours )
{
  $sql_view = 'AND saisie_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $DB_SQL = 'SELECT item_id , item_nom , saisie_date , saisie_note , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'matiere_id , niveau_id , matiere_nom, matiere_ref ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'AND saisie_date > DATE_SUB( NOW() , INTERVAL :nb_jours DAY ) '.$sql_view ;
  // Pas de 'GROUP BY item_id ' car le regroupement est effectué avant le tri par date
  $DB_SQL.= 'ORDER BY saisie_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(
    ':eleve_id' => $eleve_id,
    ':nb_jours' => $nb_jours,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE); // TRUE permet d'avoir item_id en clef et, pour un item qui ressortirait plusieurs fois, d'avoir la dernière saisie en [item_id][0]
}

/**
 * Retourner les items d'un devoir et des informations supplémentaires ; les clefs du tableau sont les item_id car on en a besoin
 *
 * @param int  $devoir_id
 * @return array
 */
public static function DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id)
{
  $DB_SQL = 'SELECT item_id, item_nom, COUNT(sacoche_jointure_referentiel_socle.item_id) AS s2016_nb, ';
  $DB_SQL.= 'item_cart, item_comm, item_lien, ';
  $DB_SQL.= 'matiere_id, matiere_nb_demandes, matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'referentiel_calcul_methode, referentiel_calcul_limite, referentiel_calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_SQL.= 'GROUP BY sacoche_jointure_devoir_item.item_id ';
  $DB_SQL.= 'ORDER BY jointure_ordre ASC, matiere_ref ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

/**
 * Retourner les notes obtenues à un élève à un devoir
 *
 * @param int   $devoir_id
 * @param int   $eleve_id
 * @param string $user_profil_type
 * @param bool  $with_marqueurs   // Avec ou sans les marqueurs de demandes d'évaluations
 * @return array
 */
public static function DB_lister_saisies_devoir_eleve( $devoir_id , $eleve_id , $user_profil_type , $with_marqueurs )
{
  // Cette fonction peut être appelée avec un autre profil.
  $sql_view = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND saisie_visible_date<=NOW() ' : '' ;
  $req_view = ($with_marqueurs) ? '' : 'AND saisie_note!="PA" ' ;
  $DB_SQL = 'SELECT item_id, saisie_note ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id '.$sql_view.$req_view;
  $DB_VAR = array(
    ':devoir_id' => $devoir_id,
    ':eleve_id'  => $eleve_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>