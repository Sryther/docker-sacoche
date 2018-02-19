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
// Ces méthodes ne concernent que les bilans (génération de relevés, synthèses, ...).

class DB_STRUCTURE_BILAN extends DB
{

/**
 * Lister les items des référentiels reliés au socle
 *
 * @param int    $cycle_id    id du cycle
 * @param bool   $with_detail
 * @param string $liste_domaine_id      facultatif, pour restreindre à 1 ou plusieurs domaines
 * @param string $liste_composante_id   facultatif, pour restreindre à 1 ou plusieurs composantes
 * @return array
 */
public static function DB_recuperer_associations_items_composantes( $cycle_id , $with_detail=TRUE , $liste_domaine_id=NULL , $liste_composante_id=NULL )
{
  $select_id        = (!$with_detail) ? 'item_id ' : 'item_id , item_nom , matiere_ref , socle_composante_id , socle_domaine_id , ' ;
  $select_ref       = (!$with_detail) ? '' : 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso ' ;
  $where_domaine    = ($liste_domaine_id)    ? 'AND socle_domaine_id IN('.$liste_domaine_id.') '       : '' ;
  $where_composante = ($liste_composante_id) ? 'AND socle_composante_id IN('.$liste_composante_id.') ' : '' ;
  $group_by         = ($liste_domaine_id)    ? 'item_id, socle_domaine_id ' : 'item_id, socle_composante_id ' ;
  $DB_SQL = 'SELECT '.$select_id.$select_ref;
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_composante_id) ';
  $DB_SQL.= 'WHERE matiere_active=1 AND niveau_actif=1 AND socle_cycle_id=:cycle_id '.$where_domaine.$where_composante;
  $DB_SQL.= 'GROUP BY '.$group_by;
  $DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array( ':cycle_id' => $cycle_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_niveau_groupes
 *
 * @param string   $listing_groupe_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_recuperer_niveau_groupes($listing_groupe_id)
{
  $DB_SQL = 'SELECT groupe_id, niveau_id, niveau_nom ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_id IN('.$listing_groupe_id.') ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_arborescence_selection
 * Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, pour les items choisis !
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $aff_domaine      1 pour préfixer avec les noms des domaines, 0 sinon
 * @param int    $aff_theme        1 pour préfixer avec les noms des thèmes, 0 sinon
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $with_abrev       1 pour récupérer l'abréviation éventuelle pour une synthèse, 0 sinon
 * @return array
 */
public static function DB_recuperer_arborescence_selection( $liste_eleve_id , $liste_item_id , $date_mysql_debut , $date_mysql_fin , $aff_domaine , $aff_theme , $aff_socle, $with_abrev = 0 )
{
  $select_abrev    = ($with_abrev) ? 'item_abrev , ' : '' ;
  $select_s2016_nb = ($aff_socle)  ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS item_s2016, ' : '' ;
  $join_s2016      = ($aff_socle)  ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $groupby_item    = ($aff_socle)  ? 'GROUP BY item_id ' : '' ;
  switch((string)$aff_domaine.(string)$aff_theme)
  {
    case '00' : $item_nom='item_nom'; break;
    case '10' : $item_nom='CONCAT(domaine_nom," | ",item_nom) AS item_nom'; break;
    case '01' : $item_nom='CONCAT(theme_nom," | ",item_nom) AS item_nom'; break;
    case '11' : $item_nom='CONCAT(domaine_nom," | ",theme_nom," | ",item_nom) AS item_nom'; break;
  }
  $DB_SQL = 'SELECT item_id , matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= $item_nom.' , '.$select_abrev;
  $DB_SQL.= 'item_coef , item_cart , item_lien , '.$select_s2016_nb;
  $DB_SQL.= 'matiere_id , matiere_nom , ';
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND saisie_date>=:date_debut AND saisie_date<=:date_fin ';
  $DB_SQL.= $groupby_item;
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
  $tab_matiere = array();
  foreach($DB_TAB as $item_id => $tab)
  {
    foreach($tab as $key => $DB_ROW)
    {
      unset($DB_TAB[$item_id][$key]['matiere_id'],$DB_TAB[$item_id][$key]['matiere_nom']);
    }
    $tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
  }
  return array($DB_TAB,$tab_matiere);
}

/**
 * recuperer_arborescence_devoirs
 * Retourner l'arborescence des items travaillés par des élèves donnés (ou un seul), pour des évaluations données
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param string $liste_eval_id    id des évaluations séparées par des virgules ; il peut n'y avoir qu'un id
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $aff_domaine      1 pour préfixer avec les noms des domaines, 0 sinon
 * @param int    $aff_theme        1 pour préfixer avec les noms des thèmes, 0 sinon
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $with_abrev       1 pour récupérer l'abréviation éventuelle pour une synthèse, 0 sinon
 * @return array
 */
public static function DB_recuperer_arborescence_devoirs( $liste_eleve_id , $liste_eval_id , $only_socle , $aff_domaine , $aff_theme , $aff_socle , $with_abrev = 0 )
{
  $select_abrev     = ($with_abrev)                 ? 'item_abrev , '                        : '' ;
  $select_s2016_nb  = ($aff_socle)                  ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS item_s2016, ' : '' ;
  $join_s2016       = ($only_socle || $aff_socle)   ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_evals      = (strpos($liste_eval_id ,',')) ? 'devoir_id IN('.$liste_eval_id.') '    : 'devoir_id='.$liste_eval_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_niveau     = 'AND niveau_actif=1 ' ;
  $where_socle      = ($only_socle)                 ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  switch((string)$aff_domaine.(string)$aff_theme)
  {
    case '00' : $item_nom='item_nom'; break;
    case '10' : $item_nom='CONCAT(domaine_nom," | ",item_nom) AS item_nom'; break;
    case '01' : $item_nom='CONCAT(theme_nom," | ",item_nom) AS item_nom'; break;
    case '11' : $item_nom='CONCAT(domaine_nom," | ",theme_nom," | ",item_nom) AS item_nom'; break;
  }
  $DB_SQL = 'SELECT item_id , matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= $item_nom.' , '.$select_abrev;
  $DB_SQL.= 'item_coef , item_cart , item_comm , item_lien , matiere_id , matiere_nom , '.$select_s2016_nb;
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE matiere_active=1 AND '.$where_eleve.' AND '.$where_evals.$where_niveau.$where_socle;
  $DB_SQL.= 'GROUP BY item_id ';
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE);
  // Traiter le résultat de la requête pour en extraire un sous-tableau $tab_matiere
  $tab_matiere = array();
  foreach($DB_TAB as $item_id => $tab)
  {
    foreach($tab as $key => $DB_ROW)
    {
      $tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
      unset($DB_TAB[$item_id][$key]['matiere_id'],$DB_TAB[$item_id][$key]['matiere_nom']);
    }
  }
  return array($DB_TAB,$tab_matiere);
}

/**
 * recuperer_arborescence_professeur
 * Retourner l'arborescence des items travaillés par des élèves donnés (ou un seul), durant une période donnée, par un professeur donné
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param int    $prof_id          id du prof
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $aff_domaine      1 pour préfixer avec les noms des domaines, 0 sinon
 * @param int    $aff_theme        1 pour préfixer avec les noms des thèmes, 0 sinon
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $with_abrev       1 pour récupérer l'abréviation éventuelle pour une synthèse, 0 sinon
 * @return array
 */
public static function DB_recuperer_arborescence_professeur( $liste_eleve_id , $prof_id , $only_socle , $date_mysql_debut , $date_mysql_fin , $aff_domaine , $aff_theme , $aff_socle , $with_abrev = 0 )
{
  $select_abrev     = ($with_abrev)                 ? 'item_abrev , '                        : '' ;
  $select_s2016_nb  = ($aff_socle)                  ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS item_s2016, ' : '' ;
  $join_s2016       = ($only_socle || $aff_socle)   ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_niveau     = 'AND niveau_actif=1 ' ;
  $where_socle      = ($only_socle)                 ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  $where_date_debut = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '        : '';
  $where_date_fin   = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '          : '';
  switch((string)$aff_domaine.(string)$aff_theme)
  {
    case '00' : $item_nom='item_nom'; break;
    case '10' : $item_nom='CONCAT(domaine_nom," | ",item_nom) AS item_nom'; break;
    case '01' : $item_nom='CONCAT(theme_nom," | ",item_nom) AS item_nom'; break;
    case '11' : $item_nom='CONCAT(domaine_nom," | ",theme_nom," | ",item_nom) AS item_nom'; break;
  }
  $DB_SQL = 'SELECT item_id , matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= $item_nom.' , '.$select_abrev;
  $DB_SQL.= 'item_coef , item_cart , item_comm , item_lien , matiere_id , matiere_nom , '.$select_s2016_nb;
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE prof_id=:prof_id AND matiere_active=1 AND '.$where_eleve.$where_niveau.$where_socle.$where_date_debut.$where_date_fin;
  $DB_SQL.= 'GROUP BY item_id ';
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':prof_id'    => $prof_id,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
  // Traiter le résultat de la requête pour en extraire un sous-tableau $tab_matiere
  $tab_matiere = array();
  foreach($DB_TAB as $item_id => $tab)
  {
    foreach($tab as $key => $DB_ROW)
    {
      $tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
      unset($DB_TAB[$item_id][$key]['matiere_id'],$DB_TAB[$item_id][$key]['matiere_nom']);
    }
  }
  return array($DB_TAB,$tab_matiere);
}

/**
 * recuperer_arborescence_bilan
 * Retourner l'arborescence des items travaillés par des élèves donnés (ou un seul), pour une matière donnée (ou toutes), durant une période donnée
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param int    $matiere_id       id de la matière ; -1 pour toutes les matières
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $aff_domaine      1 pour préfixer avec les noms des domaines, 0 sinon
 * @param int    $aff_theme        1 pour préfixer avec les noms des thèmes, 0 sinon
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $with_abrev       1 pour récupérer l'abréviation éventuelle pour une synthèse, 0 sinon
 * @return array
 */
public static function DB_recuperer_arborescence_bilan( $liste_eleve_id , $matiere_id , $only_socle , $date_mysql_debut , $date_mysql_fin , $aff_domaine , $aff_theme , $aff_socle , $with_abrev = 0 )
{
  $select_abrev     = ($with_abrev)                 ? 'item_abrev , '                        : '' ;
  $select_s2016_nb  = ($aff_socle)                  ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS item_s2016, ' : '' ;
  $join_s2016       = ($only_socle || $aff_socle)   ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_matiere    = ($matiere_id>0)               ? 'AND matiere_id=:matiere '             : 'AND matiere_active=1 ' ;
  $where_niveau     = 'AND niveau_actif=1 ' ;
  $where_socle      = ($only_socle)                 ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  $where_date_debut = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '        : '';
  $where_date_fin   = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '          : '';
  $order_matiere    = ($matiere_id<0)               ? 'matiere_ordre ASC, matiere_nom ASC, ' : '' ;
  switch((string)$aff_domaine.(string)$aff_theme)
  {
    case '00' : $item_nom='item_nom'; break;
    case '10' : $item_nom='CONCAT(domaine_nom," | ",item_nom) AS item_nom'; break;
    case '01' : $item_nom='CONCAT(theme_nom," | ",item_nom) AS item_nom'; break;
    case '11' : $item_nom='CONCAT(domaine_nom," | ",theme_nom," | ",item_nom) AS item_nom'; break;
  }
  $DB_SQL = 'SELECT item_id , matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= $item_nom.' , '.$select_abrev;
  $DB_SQL.= 'item_coef , item_cart , item_comm , item_lien , '.$select_s2016_nb;
  $DB_SQL.= ($matiere_id<0) ? 'matiere_id , matiere_nom , matiere_nb_demandes , ' : '' ;
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE '.$where_eleve.$where_matiere.$where_niveau.$where_socle.$where_date_debut.$where_date_fin;
  $DB_SQL.= 'GROUP BY item_id ';
  $DB_SQL.= 'ORDER BY '.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':matiere'    => $matiere_id,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
  if($matiere_id>0)
  {
    return $DB_TAB;
  }
  else
  {
    // Traiter le résultat de la requête pour en extraire un sous-tableau $tab_matiere
    $tab_matiere = array();
    foreach($DB_TAB as $item_id => $tab)
    {
      foreach($tab as $key => $DB_ROW)
      {
        $tab_matiere[$DB_ROW['matiere_id']] = array(
          'matiere_nom'         => $DB_ROW['matiere_nom'],
          'matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
        );
        unset( $DB_TAB[$item_id][$key]['matiere_id'] , $DB_TAB[$item_id][$key]['matiere_nom'] , $DB_TAB[$item_id][$key]['matiere_nb_demandes'] );
      }
    }
    return array($DB_TAB,$tab_matiere);
  }
}

/**
 * recuperer_items_travailles
 * Retourner la liste des items travaillés par des élèves donnés (ou un seul), pour des matières données, durant une période donnée
 * C'est une version simple de DB_recuperer_arborescence_bilan() qui sert pour le calcul des moyennes ou un bilan chronologique
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules
 * @param string $liste_matiere_id id des matières séparés par des virgules (si pas fourni, pas de restriction matières)
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param string $rubrique_type    "matiere" | "niveau"
 * @return array
 */
public static function DB_recuperer_items_travailles( $liste_eleve_id , $liste_matiere_id , $only_socle , $date_mysql_debut , $date_mysql_fin , $rubrique_type )
{
  $join_s2016       = ($only_socle)       ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_matiere    = ($liste_matiere_id) ? 'AND matiere_id IN('.$liste_matiere_id.') ' : 'AND matiere_active=1 ';
  $where_socle      = ($only_socle)       ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  $where_date_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
  $where_date_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
  $DB_SQL = 'SELECT item_id , item_coef , '.$rubrique_type.'_id AS rubrique_id , '.$rubrique_type.'_nom AS rubrique_nom , referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_'.$rubrique_type.' USING ('.$rubrique_type.'_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') '.$where_matiere.$where_socle.$where_date_debut.$where_date_fin;
  $DB_SQL.= 'GROUP BY item_id ';
  $DB_VAR = array(
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
  // Traiter le résultat de la requête pour en extraire un sous-tableau $tab_rubrique
  $tab_rubrique = array();
  foreach($DB_TAB as $item_id => $tab)
  {
    foreach($tab as $key => $DB_ROW)
    {
      $tab_rubrique[$DB_ROW['rubrique_id']] = $DB_ROW['rubrique_nom'];
      unset( $DB_TAB[$item_id][$key]['rubrique_id'] , $DB_TAB[$item_id][$key]['rubrique_nom'] );
    }
  }
  return array($DB_TAB,$tab_rubrique);
}

/**
 * recuperer_matieres_travaillees
 * Retourner la liste des matières travaillées par les élèves d'une classe donnée, pour des matières données, durant une période donnée
 * C'est une version simple de DB_recuperer_arborescence_bilan() qui sert pour les appréciations sur un groupe et l'import CSV d'un bilan officiel
 *
 * @param int    $classe_id   id de la classe (pas d'un sous-groupe)
 * @param string $liste_matiere_id id des matières séparés par des virgules (si pas fourni, pas de restriction matières)
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param bool   $only_if_synthese
 * @param int    $prof_id   pour restreindre aux saisies d'un prof donné (facultatif)
 * @return array
 */
public static function DB_recuperer_matieres_travaillees( $classe_id , $liste_matiere_id , $date_mysql_debut , $date_mysql_fin , $only_if_synthese , $prof_id=NULL )
{
  $where_prof_id    = ($prof_id)          ? 'AND sacoche_saisie.prof_id=:prof_id '                 : '';
  $where_matiere    = ($liste_matiere_id) ? 'AND matiere_id IN('.$liste_matiere_id.') '            : 'AND matiere_active=1 ';
  $where_date_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut '                        : '';
  $where_date_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '                          : '';
  $where_synthese   = ($only_if_synthese) ? 'AND referentiel_mode_synthese IN("domaine","theme") ' : '';
  $DB_SQL = 'SELECT matiere_id as rubrique_id, matiere_nom as rubrique_nom ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie ON sacoche_user.user_id=sacoche_saisie.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE eleve_classe_id=:classe_id '.$where_prof_id.$where_matiere.$where_date_debut.$where_date_fin.$where_synthese;
  $DB_SQL.= 'GROUP BY matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, matiere_nom ASC';
  $DB_VAR = array(
    ':classe_id'  => $classe_id,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
    ':prof_id'    => $prof_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_arborescence_synthese
 * Retourner l'arborescence des items travaillés par des élèves selectionnés, durant la période choisie => pour la synthèse matière ou multi-matières
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param int    $matiere_id       id de la matière ; 0 pour toutes les matières
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $only_niveau      0 pour tous les niveaux, autre pour un niveau donné
 * @param string $mode_synthese    'predefini' ou 'domaine' ou 'theme'
 * @param int    $fusion_niveaux   1 pour ne pas indiquer le niveau dans l'intitulé et fusionner les synthèses de même intitulé, 0 sinon
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @return array
 */
public static function DB_recuperer_arborescence_synthese( $liste_eleve_id , $matiere_id , $only_socle , $only_niveau , $mode_synthese='predefini' , $fusion_niveaux , $date_mysql_debut , $date_mysql_fin , $aff_socle )
{
  $select_matiere    = (!$matiere_id)                ? 'matiere_id , matiere_nom , matiere_nb_demandes , '    : '' ;
  $select_synthese   = ($mode_synthese=='predefini') ? ', referentiel_mode_synthese AS mode_synthese '        : '' ;
  $select_s2016_nb   = ($aff_socle)                  ? 'COUNT(sacoche_jointure_referentiel_socle.item_id) AS item_s2016, ' : '' ;
  $join_s2016        = ($only_socle || $aff_socle)   ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_eleve       = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '                    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_matiere     = ($matiere_id)                 ? 'AND matiere_id=:matiere '                             : 'AND matiere_active=1 ' ;
  $where_socle       = ($only_socle)                 ? 'AND socle_composante_id IS NOT NULL '                 : '' ;
  $where_niveau      = ($only_niveau)                ? 'AND niveau_id='.$only_niveau.' '                      : 'AND niveau_actif=1 ' ;
  $where_date_debut  = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '                        : '';
  $where_date_fin    = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '                          : '';
  $where_synthese    = ($mode_synthese=='predefini') ? 'AND referentiel_mode_synthese IN("domaine","theme") ' : '';
  $order_matiere     = (!$matiere_id)                ? 'matiere_ordre ASC, matiere_nom ASC, '                 : '' ;
  $DB_SQL = 'SELECT item_id , matiere_ref , ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
  $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
  $DB_SQL.= 'item_nom , item_coef , item_cart , item_lien , '.$select_s2016_nb;
  $DB_SQL.= 'theme_id , theme_nom , ';
  $DB_SQL.= 'domaine_id , domaine_nom , ';
  $DB_SQL.= $select_matiere;
  $DB_SQL.= 'niveau_nom , ';
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif '.$select_synthese;
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE '.$where_eleve.$where_matiere.$where_socle.$where_niveau.$where_date_debut.$where_date_fin.$where_synthese;
  $DB_SQL.= 'GROUP BY item_id ';
  $DB_SQL.= 'ORDER BY '.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(
    ':matiere'    => $matiere_id,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
  // Traiter le résultat de la requête pour en extraire des sous-tableaux $tab_synthese et éventuellement $tab_matiere
  $tab_synthese = array();
  $tab_matiere  = array();
  foreach($DB_TAB as $item_id => $tab)
  {
    foreach($tab as $key => $DB_ROW)
    {
      if(!$matiere_id)
      {
        $prefixe_matiere = $DB_ROW['matiere_id'];
        $tab_matiere[$DB_ROW['matiere_id']] = array(
          'matiere_nom'         => $DB_ROW['matiere_nom'],
          'matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
        );
        unset( $DB_TAB[$item_id][$key]['matiere_nom'] , $DB_TAB[$item_id][$key]['matiere_nb_demandes'] );
      }
      else
      {
        $prefixe_matiere = $matiere_id;
      }
      if($mode_synthese=='predefini')
      {
        $prefixe_synthese = $DB_ROW['mode_synthese'];
        unset($DB_TAB[$item_id][$key]['mode_synthese']);
      }
      else
      {
        $prefixe_synthese = $mode_synthese;
      }
      if($fusion_niveaux)
      {
        $synthese_ref = $prefixe_matiere.'_'.Clean::id($DB_ROW[$prefixe_synthese.'_nom']);
        $synthese_nom = $DB_ROW[$prefixe_synthese.'_nom'];
      }
      else
      {
        $synthese_ref = $prefixe_synthese.'_'.$DB_ROW[$prefixe_synthese.'_id'];
        $synthese_nom = $DB_ROW['niveau_nom'].' - '.$DB_ROW[$prefixe_synthese.'_nom'];
      }
      $tab_synthese[$synthese_ref] = $synthese_nom;
      $DB_TAB[$item_id][$key]['synthese_ref'] = $synthese_ref;
      unset($DB_TAB[$item_id][$key]['niveau_nom'],$DB_TAB[$item_id][$key]['domaine_id'],$DB_TAB[$item_id][$key]['domaine_nom'],$DB_TAB[$item_id][$key]['theme_id'],$DB_TAB[$item_id][$key]['theme_nom']);
    }
  }
  if($matiere_id)
  {
    return array($DB_TAB,$tab_synthese);
  }
  else
  {
    return array($DB_TAB,$tab_synthese,$tab_matiere);
  }
}

/**
 * recuperer_arborescence_items
 * Retourner l'arborescence des items insiqués => pour un export/import
 *
 * @param string $liste_item_id   id des éitems séparés par des virgules
 * @return array
 */
public static function recuperer_arborescence_items( $liste_item_id )
{
  $DB_SQL = 'SELECT item_id , item_nom , theme_id , theme_nom , domaine_id , domaine_nom , matiere_id , matiere_nom , niveau_id , niveau_nom ';
  $DB_SQL.= 'FROM sacoche_referentiel_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE item_id IN('.$liste_item_id.') ';
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_modes_synthese
 *
 * @param string $mode 'inconnu' | 'sans'
 * @return string
 */
public static function DB_recuperer_modes_synthese($mode)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT( CONCAT(matiere_nom," / ",niveau_nom) SEPARATOR "§BR§") AS listing ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE referentiel_mode_synthese=:mode AND matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, niveau_ordre ASC ';
  $DB_VAR = array(':mode'=>$mode);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les informations associées à une liste d'items ; au minimum les paramètres de l'algorithme de calcul, sinon davantage
 *
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param bool   $detail
 * @return array
 */
public static function DB_lister_infos_items( $liste_item_id , $detail )
{
  $DB_SQL = 'SELECT item_id , ';
  if($detail)
  {
    $DB_SQL.= 'item_nom , matiere_ref , ';
    $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_code,theme_ordre,item_ordre) AS ref_auto , ';
    $DB_SQL.= 'CONCAT(domaine_ref,theme_ref,item_ref) AS ref_perso , ';
    $DB_SQL.= 'item_coef , item_cart , item_lien , '; // Besoin pour l'élève s'il veut formuler une demande d'évaluation
    $DB_SQL.= 'matiere_id , matiere_nb_demandes , '; // Besoin pour l'élève s'il ajoute l'item aux demandes d'évaluations
  }
  $DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_referentiel_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'WHERE item_id IN('.$liste_item_id.') ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_date_last_eleves_items
 * Retourner, pour des élèves et les items donnés, la date de la dernière évaluation (pour vérifier qu'il faut bien prendre l'item en compte)
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @return array
 */
public static function DB_lister_date_last_eleves_items( $liste_eleve_id , $liste_item_id )
{
  $DB_SQL = 'SELECT eleve_id , item_id , MAX(saisie_date) AS date_last ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND saisie_note!="PA" ';
  $DB_SQL.= 'GROUP BY eleve_id, item_id ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_result_eleves_items
 * Retourner les résultats pour des élèves donnés, pour des items donnés d'une ou plusieurs matieres, sur une période donnée
 *
 * @param string   $liste_eleve_id  id des élèves séparés par des virgules
 * @param string   $liste_item_id   id des items séparés par des virgules
 * @param int      $matiere_id      matiere_id>0 (cas 'matiere' : on retourne cet id) | -1 (cas 'multimatiere' : on retourne l'id matière) | 0 (cas 'selection' : items issus potentiellement de plusieurs matières mais on retourne 0)
 * @param string   $date_mysql_debut
 * @param string   $date_mysql_fin
 * @param string   $user_profil_type
 * @param int|bool $onlyprof        id d'un prof pour restreindre à ses évaluations, ou FALSE sinon
 * @param bool     $onlynote
 * @param bool     $first_order_by_date
 * @return array
 */
public static function DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , $matiere_id , $date_mysql_debut , $date_mysql_fin , $user_profil_type , $onlyprof=FALSE , $onlynote=FALSE , $first_order_by_date=FALSE )
{
  $sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
  $sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
  $sql_view  = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND saisie_visible_date<=NOW() ' : '' ;
  $select_matiere = ($matiere_id>=0) ? $matiere_id.' AS matiere_id ' : 'matiere_id' ;
  $join_matiere   = ($matiere_id<=0) ? 'LEFT JOIN sacoche_matiere USING (matiere_id) ' : '' ;
  $order_matiere  = ($matiere_id<=0) ? 'matiere_ordre ASC, ' : '' ;
  $where_prof     = ($onlyprof) ? 'AND sacoche_saisie.prof_id=:prof_id ' : '' ;
  $DB_SQL = 'SELECT eleve_id , '.$select_matiere.' , niveau_id , item_id , sacoche_saisie.prof_id AS prof_id , '; // niveau_id utilisé seulement par releve_bilan_chronologique.ajax.php dans un cas précis... ; prof_id utilisé seulement pour le livret à partir des enseignants...
  $DB_SQL.= ($onlynote) ? 'saisie_note AS note ' : 'saisie_note AS note , saisie_date AS date , saisie_info AS info ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= $join_matiere;
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') '.$where_prof.'AND niveau_actif=1 AND saisie_note!="PA" '.$sql_debut.$sql_fin.$sql_view;
  $DB_SQL.= (!$first_order_by_date)
            ? 'ORDER BY '.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC, devoir_id ASC '
            : 'ORDER BY saisie_date ASC, devoir_id ASC,'.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  $DB_VAR = array(
    ':prof_id'    => $onlyprof,
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_result_eleves_evals
 * Retourner les résultats pour des élèves donnés, pour des items donnés de devoirs donnés
 *
 * @param string   $liste_eleve_id  id des élèves séparés par des virgules
 * @param string   $liste_item_id   id des items séparés par des virgules
 * @param string   $liste_eval_id   id des devoirs séparés par des virgules
 * @param int      $matiere_id      id de la matière ou 0 si items issus de plusieurs matières
 * @return array
 */
public static function DB_lister_result_eleves_evals( $liste_eleve_id , $liste_item_id , $liste_eval_id , $matiere_id )
{
  $DB_SQL = 'SELECT eleve_id , '.$matiere_id.' AS matiere_id , item_id , ';
  $DB_SQL.= 'saisie_note AS note , saisie_date AS date , saisie_info AS info ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE devoir_id IN('.$liste_eval_id.') AND eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND niveau_actif=1 AND saisie_note!="PA" '; // Pas de "AND saisie_visible_date<=NOW()" car consultation prof uniquement
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC, devoir_id ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_result_eleves
 * Retourner les résultats pour des élèves donnés
 *
 * @param string   $liste_eleve_id  id des élèves séparés par des virgules
 * @return array
 */
public static function DB_lister_result_eleves( $liste_eleve_id )
{
  $DB_SQL = 'SELECT eleve_id AS user_id , item_id , saisie_note AS note , saisie_date AS date , saisie_info AS info , saisie_visible_date AS visible ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND niveau_actif=1 AND matiere_active=1 AND saisie_note!="PA" ';
  $DB_SQL.= 'ORDER BY user_id ASC, item_id ASC, saisie_date ASC, devoir_id ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_eleves_cibles
 *
 * @param string   $listing_eleve_id   id des élèves séparés par des virgules
 * @param string   $eleves_ordre       valeur parmi [alpha] [classe]
 * @return array|string                le tableau est de la forme [eleve_id] => array('eleve_INE'=>...,'eleve_ID_BE'=>...,'eleve_nom'=>...,'eleve_prenom'=>...,'eleve_genre'=>...,'date_naissance'=>...,'eleve_id_gepi'=>...);
 */
public static function DB_lister_eleves_cibles( $listing_eleve_id , $eleves_ordre )
{
  $DB_SQL = 'SELECT user_id AS eleve_id , user_reference AS eleve_INE , user_sconet_id AS eleve_ID_BE , user_nom AS eleve_nom , user_prenom AS eleve_prenom , user_genre AS eleve_genre , user_naissance_date AS date_naissance , user_id_gepi AS eleve_id_gepi ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  if($eleves_ordre=='classe')
  {
    $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  }
  $DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND user_profil_type=:profil_type ';
  $DB_SQL.= ($eleves_ordre=='classe') ? 'ORDER BY niveau_ordre ASC, groupe_nom ASC, user_nom ASC, user_prenom ASC' : 'ORDER BY user_nom ASC, user_prenom ASC' ;
  $DB_VAR = array(':profil_type'=>'eleve');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève ne correspond aux identifiants transmis.' ;
}

/**
 * lister_jointure_groupe_periode ; le rangement par ordre de période permet, si les périodes se chevauchent, que javascript choisisse la 1ère par défaut
 *
 * @param string   $listing_user_id   id des élèves séparés par des virgules
 * @return array
 */
public static function DB_lister_periodes_bulletins_saisies_ouvertes($listing_user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT periode_id, periode_nom, GROUP_CONCAT(user_id SEPARATOR "_") AS eleves_listing ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_groupe_periode ON sacoche_user.eleve_classe_id=sacoche_jointure_groupe_periode.groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE user_id IN ('.$listing_user_id.') AND officiel_bulletin IN("2rubrique","3mixte") ';
  $DB_SQL.= 'GROUP BY periode_id ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_modes_synthese
 *
 * @param string $mode 'inconnu' | 'sans'
 * @return int
 */
public static function DB_compter_modes_synthese($mode)
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE referentiel_mode_synthese=:mode AND matiere_active=1 AND niveau_actif=1 ';
  $DB_VAR = array(':mode'=>$mode);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>