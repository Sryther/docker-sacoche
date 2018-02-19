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

// Ces méthodes ne concernent que les tables "sacoche_livret_*".

class DB_STRUCTURE_LIVRET extends DB
{

/**
 * lister_classes_livret_sans_chef
 *
 * @param void
 * @return string
 */
public static function DB_lister_classes_livret_sans_chef()
{
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id) ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(groupe_id) ';
  $DB_SQL.= 'WHERE groupe_type="classe" AND groupe_chef_id=0 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Vérifier qu'il y a au moins une classe dans l'établissement
 * Vérifier qu'il y a au moins une classe associée au livret, et sinon essayer de faire le boulot automatiquement
 *
 * @param void
 * @return bool | int
 */
public static function DB_initialiser_jointures_livret_classes()
{
  // Vérifier qu'il y a au moins une classe dans l'établissement
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type="classe" ';
  $nb_classes_structure = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  if( !$nb_classes_structure )
  {
    return FALSE;
  }
  // Vérifier que les classes sont associées au livret
  $DB_SQL = 'SELECT COUNT(DISTINCT groupe_id) ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $nb_classes_livret = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Mais sans tenir compte des classes d'un niveau inférieur ou supérieur à l'objet du Livret Scolaire
  $DB_SQL = 'SELECT COUNT(DISTINCT groupe_id) ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type="classe" AND NOT (niveau_id BETWEEN 1033 AND 200000) ';
  $nb_classes_hors_lsu = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_classes_concernees = $nb_classes_structure - $nb_classes_hors_lsu;
  if( $nb_classes_livret >= $nb_classes_concernees )
  {
    return TRUE;
  }
  // Fonction pour essayer de déterminer le bon type de période associé à une classe
  function determiner_periode_probable( $listing_periodes , $periode_nb )
  {
    if( substr_count( $listing_periodes , '3' ) )
    { return array('31','32','33'); }
    if( substr_count( $listing_periodes , '2' ) )
    { return array('21','22'); }
    if( substr_count( $listing_periodes , '4' ) )
    { return array('41','42','43','44'); }
    if( substr_count( $listing_periodes , '5' ) )
    { return array('51','52','53','54','55'); }
    if( $periode_nb == 3 )
    { return array('31','32','33'); }
    if( $periode_nb == 2 )
    { return array('21','22'); }
    if( $periode_nb == 4 )
    { return array('41','42','43','44'); }
    if( $periode_nb == 5 )
    { return array('51','52','53','54','55'); }
    return array('31','32','33');
  }
  // Initialiser les jointures livret / classes
  $nb_associations = 0;
  $DB_SQL = 'SELECT groupe_id, niveau_ordre, ';
  $DB_SQL.= 'COUNT( periode_id ) AS periode_nb, ';
  $DB_SQL.= 'GROUP_CONCAT(SUBSTRING(periode_livret,1,1)) AS listing_periodes, ';
  $DB_SQL.= 'GROUP_CONCAT(jointure_periode) AS listing_jointures ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_groupe_periode USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING(periode_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type="classe" ';
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'HAVING listing_jointures IS NULL ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  foreach($DB_TAB as $DB_ROW)
  {
    $jointure_periode = determiner_periode_probable( $DB_ROW['listing_periodes'] , $DB_ROW['periode_nb'] );
    switch($DB_ROW['niveau_ordre'])
    {
      case 3 : // GS
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle1' , 'cycle' , array(NULL) );
        $nb_associations += 1;
        break;
      case 11 : // CP
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cp' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 21 : // CE1
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'ce1' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 22 : // CE2
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'ce2' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle2' , 'cycle' , array(NULL) );
        $nb_associations += 2;
        break;
      case 31 : // CM1
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cm1' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 32 : // CM2
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cm2' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 100 : // Sixièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '6e' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle3' , 'cycle' , array(NULL) );
        $nb_associations += 2;
        break;
      case 101 : // Cinquièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '5e' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 102 : // Quatrièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '4e' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 103 : // Troisièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '3e' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle4' , 'cycle' , array(NULL) );
        $nb_associations += 2;
        break;
    }
  }
  return $nb_associations;
}

/**
 * Vérifier qu'il y a un chef associé à chaque classe, et sinon essayer de faire le boulot automatiquement
 *
 * @param void
 * @return bool | int
 */
public static function DB_initialiser_jointures_classe_chef()
{
  // Nettoyer des classes avec un chef supprimé ou au compte inactif
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_groupe.groupe_chef_id=sacoche_user.user_id ';
  $DB_SQL.= 'SET groupe_chef_id=0 ';
  $DB_SQL.= 'WHERE groupe_type="classe" AND groupe_chef_id!=0 AND ( user_id IS NULL OR user_sortie_date<=NOW() ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Chercher si des classes associées au livret sont sans chef
  $listing_groupe = DB_STRUCTURE_LIVRET::DB_lister_classes_livret_sans_chef();
  if( empty($listing_groupe) )
  {
    return TRUE;
  }
  $nb_classes_concernees = substr_count($listing_groupe,',')+1;
  // On essaye de trouver le chef d'établissement / directeur d'école tout seul, s'il n'y en a qu'un.
  $DB_SQL = 'SELECT user_id FROM sacoche_user WHERE user_profil_sigle="DIR" AND user_sortie_date>NOW() ';
  $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  if( !empty($DB_COL) && (count($DB_COL)==1) )
  {
    $chef_id = (int)current($DB_COL);
    $DB_SQL = 'UPDATE sacoche_groupe ';
    $DB_SQL.= 'SET groupe_chef_id='.$chef_id.' ';
    $DB_SQL.= 'WHERE groupe_id IN('.$listing_groupe.') ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    return $nb_classes_concernees;
  }
  // Sinon on prend celui qui est déjà le plus rattaché aux classes, si tel est le cas.
  $DB_SQL = 'SELECT groupe_chef_id, COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type="classe" AND groupe_chef_id!=0 ';
  $DB_SQL.= 'GROUP BY groupe_chef_id ';
  $DB_SQL.= 'ORDER BY nombre DESC ';
  $DB_SQL.= 'LIMIT 1 ';
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  if(!empty($DB_ROW))
  {
    $chef_id = $DB_ROW['groupe_chef_id'];
    $DB_SQL = 'UPDATE sacoche_groupe ';
    $DB_SQL.= 'SET groupe_chef_id='.$chef_id.' ';
    $DB_SQL.= 'WHERE groupe_id IN('.$listing_groupe.') ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    return $nb_classes_concernees;
  }
  return 0;
}

/**
 * lister_pages
 *
 * @param bool   $with_info_classe
 * @return array
 */
public static function DB_lister_pages( $with_info_classe )
{
  $DB_SQL = 'SELECT sacoche_livret_page.* ';
  if($with_info_classe)
  {
    $DB_SQL.= ', COUNT( DISTINCT groupe_id ) AS groupe_nb ';
    $DB_SQL.= ', GROUP_CONCAT( DISTINCT groupe_nom SEPARATOR "<br />" ) AS listing_groupe_nom ';
    $DB_SQL.= ', COUNT( DISTINCT element_id ) AS element_nb ';
  }
  $DB_SQL.= 'FROM sacoche_livret_page ';
  if($with_info_classe)
  {
    $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_groupe USING(livret_page_ref) ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_referentiel ON sacoche_livret_page.livret_page_rubrique_type = sacoche_livret_jointure_referentiel.livret_rubrique_type ';
    $DB_SQL.= 'GROUP BY livret_page_ref ';
    // Ajouter un ordre sur le nom du groupe ne fonctionne pas : traité en PHP après
  }
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Informations diverses
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * recuperer_chef_infos
 *
 * @param int    $classe_chef_id
 * @return array
 */
public static function DB_recuperer_chef_infos( $classe_chef_id )
{
  $DB_SQL = 'SELECT user_id, user_sconet_id, user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:user_id AND user_profil_sigle="DIR" AND user_sortie_date>NOW() ';
  $DB_VAR = array(
    ':user_id' => $classe_chef_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_page_info
 *
 * @param string   $page_ref
 * @return array
 */
public static function DB_recuperer_page_info($page_ref)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_periode_info
 *
 * @param string   $periode_livret
 * @param int      $classe_id
 * @return array
 */
public static function DB_recuperer_periode_info( $periode_livret , $classe_id )
{
  $DB_SQL = 'SELECT periode_id, jointure_date_debut, jointure_date_fin ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_groupe_periode USING(periode_id) ';
  $DB_SQL.= 'WHERE periode_livret=:periode_livret AND groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':periode_livret' => $periode_livret,
    ':groupe_id'      => $classe_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_page_groupe_info
 *
 * @param int      $groupe_id
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @param int      $jointure_periode
 * @return array
 */
public static function DB_recuperer_page_groupe_info( $groupe_id , $page_ref , $page_periodicite , $jointure_periode )
{
  $where_periode = ($jointure_periode) ? 'AND jointure_periode=:jointure_periode ' : '' ; // sinon il faut le test "AND jointure_periode IS NULL"
  $DB_SQL = 'SELECT jointure_etat, jointure_date_verrou, sacoche_livret_page.*, groupe_ref, groupe_nom, groupe_chef_id, groupe_configuration_livret AS configuration_ref ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING (livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND livret_page_ref=:page_ref AND sacoche_livret_page.livret_page_periodicite=:page_periodicite '.$where_periode;
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
    ':jointure_periode' => $jointure_periode,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_dgesco_elements_arborescence
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_dgesco_elements_arborescence()
{
  $DB_SQL = 'SELECT livret_element_cycle_nom, livret_element_domaine_nom, livret_element_niveau_nom, livret_element_theme_nom, livret_element_item_nom, ';
  $DB_SQL.= 'sacoche_livret_element_cycle.livret_element_cycle_id, livret_element_domaine_id, livret_element_niveau_id, livret_element_theme_id, livret_element_item_id ';
  $DB_SQL.= 'FROM sacoche_livret_element_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_element_theme USING(livret_element_theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_element_domaine USING(livret_element_domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_element_niveau USING(livret_element_niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_element_cycle ON sacoche_livret_element_domaine.livret_element_cycle_id = sacoche_livret_element_cycle.livret_element_cycle_id ';
  $DB_SQL.= 'ORDER BY sacoche_livret_element_cycle.livret_element_cycle_id, livret_element_domaine_id, livret_element_niveau_id, livret_element_theme_id, livret_element_item_id ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rubriques & Jointures rubriques / référentiels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_correspondances_matieres_uniques
 *
 * @param string   $rubrique_type
 * @return array
 */
public static function DB_lister_correspondances_matieres_uniques( $rubrique_type )
{
  $DB_SQL = 'SELECT livret_rubrique_ou_matiere_id AS matiere_livret_id, GROUP_CONCAT(element_id) AS matiere_referentiel_id '; // matiere_referentiel_id sera en fait un id unique, on utilise GROUP_CONCAT et GROUP BY pour le COUNT() 
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type ';
  $DB_SQL.= 'GROUP BY matiere_livret_id ';
  $DB_SQL.= 'HAVING COUNT(element_id)=1 ';
  $DB_VAR = array( ':rubrique_type' => $rubrique_type );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_correspondances_matieres_uniques
 *
 * @param string   $rubrique_type
 * @param int      $matiere_livret_id
 * @return int
 */
public static function DB_recuperer_correspondance_matiere_unique( $rubrique_type , $matiere_livret_id )
{
  $DB_SQL = 'SELECT GROUP_CONCAT(element_id) AS matiere_referentiel_id '; // matiere_referentiel_id sera en fait un id unique, on utilise GROUP_CONCAT et GROUP BY pour le COUNT() 
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type AND livret_rubrique_ou_matiere_id=:matiere_livret_id ';
  $DB_SQL.= 'GROUP BY livret_rubrique_ou_matiere_id ';
  $DB_SQL.= 'HAVING COUNT(element_id)=1 ';
  $DB_VAR = array(
    ':rubrique_type'     => $rubrique_type,
    ':matiere_livret_id' => $matiere_livret_id,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_matieres_alimentees
 *
 * @param void
 * @return array
 */
public static function DB_lister_matieres_alimentees()
{
  $DB_SQL = 'SELECT livret_rubrique_type, matiere_id, matiere_nom ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'INNER JOIN sacoche_matiere ON sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id = sacoche_matiere.matiere_id ';
  $DB_SQL.= 'WHERE livret_rubrique_type IN("c3_matiere","c4_matiere") AND ( matiere_siecle=1 OR matiere_active=1 ) ';
  $DB_SQL.= 'GROUP BY livret_rubrique_type, matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_rubriques
 *
 * CONCAT_WS a une syntaxe un peu différente de CONCAT et ici 2 avantages :
 * - si un champ est NULL, les autres éléments sont retournés, avec que CONCAT renvoie NULL dans ce cas
 * - si un champ est NULL ou vide, il n'est pas concaténé, alors qu'avec CONCAT on obtient un séparateur suivi de rien
 *
 * @param string   $rubrique_type
 * @param bool     $for_edition
 * @return array
 */
public static function DB_lister_rubriques( $rubrique_type , $for_edition )
{
  if( substr($rubrique_type,3) == 'matiere' )
  {
    if($for_edition)
    {
      $DB_SQL = 'SELECT matiere_id AS livret_rubrique_id, matiere_nom AS rubrique, NULL AS sous_rubrique, ';
      $DB_SQL.= 'matiere_id AS rubrique_id_elements, matiere_id AS rubrique_id_appreciation, matiere_id AS rubrique_id_position, ';
      $DB_SQL.= 'matiere_code AS rubrique_id_livret, matiere_siecle ';
      $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
      $DB_SQL.= 'INNER JOIN sacoche_matiere ON sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id = sacoche_matiere.matiere_id ';
      $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type AND matiere_siecle=1 OR matiere_active=1 ';
      $DB_SQL.= 'ORDER BY matiere_ordre ASC ';
    }
    else
    {
      $DB_SQL = 'SELECT matiere_id AS livret_rubrique_id, CONCAT( REPLACE( REPLACE(matiere_siecle,"1","SIECLE") , "0","REFÉRENTIEL AUTRE" ) , " - ", matiere_nom ) AS livret_rubrique_nom ';
      $DB_SQL.= 'FROM sacoche_matiere ';
      $DB_SQL.= 'WHERE matiere_siecle=1 OR matiere_active=1 ';
      $DB_SQL.= 'ORDER BY matiere_siecle DESC, matiere_ordre ASC ';
    }
  }
  else
  {
    if($for_edition)
    {
      $rubrique_nom = 'livret_rubrique_domaine AS rubrique, livret_rubrique_sous_domaine AS sous_rubrique ' ;
      $rubrique_ids = ', livret_rubrique_id_elements AS rubrique_id_elements, livret_rubrique_id_appreciation AS rubrique_id_appreciation, livret_rubrique_id_position AS rubrique_id_position ' ;
      $livret_ids   = ', livret_rubrique_code_livret AS rubrique_id_livret ' ;
    }
    else
    {
      $rubrique_nom = 'CONCAT_WS( " - ", livret_rubrique_domaine, livret_rubrique_sous_domaine) AS livret_rubrique_nom ' ;
      $rubrique_ids = '' ;
      $livret_ids   = '' ;
    }
    $DB_SQL = 'SELECT livret_rubrique_id, '.$rubrique_nom.$rubrique_ids.$livret_ids;
    $DB_SQL.= 'FROM sacoche_livret_rubrique ';
    $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type ';
    $DB_SQL.= 'ORDER BY livret_rubrique_ordre ASC ';
  }
  $DB_VAR = array( ':rubrique_type' => $rubrique_type );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointures_rubriques_référentiels
 *
 * @param string   $rubrique_type
 * @param int      $rubrique_id   facultatif, pour restreindre à une rubrique
 * @return array
 */
public static function DB_lister_jointures_rubriques_référentiels( $rubrique_type , $rubrique_id=NULL )
{
  $where_rubrique = ($rubrique_id) ? 'AND livret_rubrique_ou_matiere_id=:rubrique_id ' : '' ;
  $DB_SQL = 'SELECT livret_rubrique_ou_matiere_id, GROUP_CONCAT(element_id SEPARATOR ",") AS listing_elements ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type ';
  $DB_SQL.= 'GROUP BY livret_rubrique_ou_matiere_id ';
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_id'   => $rubrique_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_arborescence_professeur
 * Retourner l'arborescence des items travaillés par des élèves donnés (ou un seul), durant une période donnée, par un professeur donné
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param string $liste_prof_id    id du prof
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $aff_domaine      1 pour préfixer avec les noms des domaines, 0 sinon
 * @param int    $aff_theme        1 pour préfixer avec les noms des thèmes, 0 sinon
 * @param int    $aff_socle        1 pour afficher si liaison au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int    $with_abrev       1 pour récupérer l'abréviation éventuelle pour une synthèse, 0 sinon
 * @return array
 */
public static function DB_recuperer_items_profs( $liste_eleve_id , $liste_prof_id , $only_socle , $date_mysql_debut , $date_mysql_fin )
{
  $join_s2016       = ($only_socle)   ? 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_prof       = (strpos($liste_prof_id,','))  ? 'prof_id IN('.$liste_prof_id.') '      : 'prof_id='.$liste_prof_id.' ' ;   // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_socle      = ($only_socle)                 ? 'AND socle_composante_id IS NOT NULL ' : '' ;
  $where_date_debut = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '        : '';
  $where_date_fin   = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '          : '';
  $DB_SQL = 'SELECT item_id , prof_id , item_nom , ';
  $DB_SQL.= 'item_coef , referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite , referentiel_calcul_retroactif AS calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE matiere_active=1 AND niveau_actif=1 AND '.$where_eleve.'AND '.$where_prof.$where_socle.$where_date_debut.$where_date_fin;
  $DB_SQL.= 'GROUP BY item_id, prof_id ';
  $DB_VAR = array(
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_items_jointures_rubriques
 *
 * @param string   $rubrique_type
 * @param string   $rubrique_join
 * @param int      $only_socle    1 pour ne retourner que les items reliés au socle, 0 sinon (TODO : ne tester à terme que le socle 2016)
 * @param int      $rubrique_id   facultatif, pour restreindre à une rubrique
 * @return array
 */
public static function DB_recuperer_items_jointures_rubriques( $rubrique_type , $rubrique_join , $only_socle , $rubrique_id=NULL )
{
  $champ_position = (substr($rubrique_type,3)=='domaine') ? 'livret_rubrique_id_position' : 'livret_rubrique_ou_matiere_id' ;
  $champ_elements = (substr($rubrique_type,3)=='domaine') ? 'livret_rubrique_id_elements' : 'livret_rubrique_ou_matiere_id' ;
  $join_rubrique  = (substr($rubrique_type,3)=='domaine') ? 'INNER JOIN sacoche_livret_rubrique ON sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id = sacoche_livret_rubrique.livret_rubrique_id ' : '' ;
  $join_s2016     = ($only_socle)  ? 'INNER JOIN sacoche_jointure_referentiel_socle USING (item_id) ' : '' ;
  $where_socle    = ($only_socle)  ? 'AND socle_composante_id IS NOT NULL '  : '' ;
  $where_rubrique = ($rubrique_id) ? 'AND '.$champ_position.'=:rubrique_id ' : '' ;
  $DB_SQL = 'SELECT '.$champ_position.' AS rubrique_id_position , '.$champ_elements.' AS rubrique_id_elements , item_id ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= $join_rubrique;
  if( $rubrique_join == 'matiere' )
  {
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_domaine ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_domaine.matiere_id ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_theme USING (domaine_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_item USING (theme_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_matiere USING (matiere_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_niveau USING (niveau_id) ';
  }
  if( $rubrique_join == 'domaine' )
  {
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_theme ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_theme.domaine_id ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_item USING (theme_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_domaine USING (domaine_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_matiere USING (matiere_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_niveau USING (niveau_id) ';
  }
  if( $rubrique_join == 'theme' )
  {
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_item ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_item.theme_id ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_theme USING (theme_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_domaine USING (domaine_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_matiere USING (matiere_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_niveau USING (niveau_id) ';
  }
  if( $rubrique_join == 'item' )
  {
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_item ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_item.item_id ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_theme USING (theme_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_referentiel_domaine USING (domaine_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_matiere USING (matiere_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_niveau USING (niveau_id) ';
  }
  $DB_SQL.= $join_s2016;
  $DB_SQL.= 'WHERE matiere_active=1 AND niveau_actif=1 AND sacoche_livret_jointure_referentiel.livret_rubrique_type=:rubrique_type '.$where_socle.$where_rubrique;
  $DB_SQL.= 'GROUP BY '.$champ_position.', '.$champ_elements.', item_id ';
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_id'   => $rubrique_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_profs_jointure_rubrique
 *
 * @param string   $rubrique_type
 * @param string   $rubrique_join
 * @param int      $matiere_id
 * @param int      $classe_id   facultatif, pour restreindre à une classe
 * @return array
 */
public static function DB_recuperer_profs_jointure_rubrique( $rubrique_type , $rubrique_join , $matiere_id , $classe_id )
{
  $join_classe  = ($classe_id) ? 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ' : '' ;
  $where_classe = ($classe_id) ? 'AND groupe_id=:groupe_id ' : '' ;
  $DB_SQL = 'SELECT DISTINCT user_id ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  if( $rubrique_join == 'user' )
  {
    $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_referentiel.element_id = sacoche_user.user_id ';
  }
  else
  {
    if( $rubrique_join == 'matiere' )
    {
      $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_livret_jointure_referentiel.element_id = sacoche_matiere.matiere_id ';
    }
    if( $rubrique_join == 'domaine' )
    {
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_domaine.domaine_id ';
    }
    if( $rubrique_join == 'theme' )
    {
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_theme.theme_id ';
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
    }
    if( $rubrique_join == 'item' )
    {
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel_item.item_id ';
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
      $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
    }
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  }
  $DB_SQL.= $join_classe;
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type AND livret_rubrique_ou_matiere_id=:matiere_id AND user_sortie_date>NOW() '.$where_classe;
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':matiere_id'    => $matiere_id,
    ':groupe_id'     => $classe_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE);
}

/**
 * recuperer_profs_classe
 *
 * @param int      $classe_id
 * @return array
 */
public static function DB_recuperer_profs_classe( $classe_id )
{
  $DB_SQL = 'SELECT user_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom, user_prenom ';
  $DB_VAR = array(
    ':groupe_id'  => $classe_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE);
}

/**
 * tester_page_jointure_rubrique
 *
 * @param string $rubrique_type
 * @param string $rubrique_join
 * @param int    $rubrique_id
 * @return bool
 */

public static function DB_tester_page_jointure_rubrique( $rubrique_type , $rubrique_join , $rubrique_id )
{
  if( in_array( $rubrique_type , array('c1_theme','c2_domaine','c3_domaine') ) )
  {
    $DB_SQL = 'SELECT 1 ';
    $DB_SQL.= 'FROM sacoche_livret_page ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_rubrique ON livret_page_rubrique_type = livret_rubrique_type ';
    $DB_SQL.= 'WHERE livret_page_rubrique_type=:rubrique_type AND livret_page_rubrique_join=:rubrique_join AND livret_rubrique_id=:rubrique_id ';
    $DB_VAR = array(
      ':rubrique_type' => $rubrique_type,
      ':rubrique_join' => $rubrique_join,
      ':rubrique_id'   => $rubrique_id,
    );
    return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  else
  {
    $DB_SQL1 = 'SELECT 1 ';
    $DB_SQL1.= 'FROM sacoche_livret_page ';
    $DB_SQL1.= 'WHERE livret_page_rubrique_type=:rubrique_type AND livret_page_rubrique_join=:rubrique_join ';
    $DB_SQL2 = 'SELECT 1 ';
    $DB_SQL2.= 'FROM sacoche_matiere ';
    $DB_SQL2.= 'WHERE matiere_id=:rubrique_id AND ( matiere_active=1 OR matiere_siecle=1 ) ';
    $DB_VAR = array(
      ':rubrique_type' => $rubrique_type,
      ':rubrique_join' => $rubrique_join,
      ':rubrique_id'   => $rubrique_id,
    );
    return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL1 , $DB_VAR) && DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL2 , $DB_VAR);
  }
}

/**
 * modifier_jointure_référentiel
 *
 * @param string $rubrique_type
 * @param int    $rubrique_id
 * @param int    $element_id
 * @param bool   $etat   TRUE pour ajouter ; FALSE pour retirer
 * @return void
 */
public static function DB_modifier_jointure_référentiel( $rubrique_type , $rubrique_id , $element_id , $etat )
{
  if($etat)
  {
    // IGNORE car comme on peut cocher parmi les éléments déjà utilisés on n'est pas à l'abri d'une demande de liaison déjà existante.
    $DB_SQL = 'INSERT IGNORE INTO sacoche_livret_jointure_referentiel ( livret_rubrique_type , livret_rubrique_ou_matiere_id , element_id ) ';
    $DB_SQL.= 'VALUES( :rubrique_type , :rubrique_id , :element_id ) ';
  }
  else
  {
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type AND livret_rubrique_ou_matiere_id=:rubrique_id AND element_id=:element_id ';
  }
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_id'   => $rubrique_id,
    ':element_id'    => $element_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_type_jointure
 *
 * @param string $rubrique_type
 * @param string $rubrique_join
 * @return void
 */
public static function DB_modifier_type_jointure( $rubrique_type , $rubrique_join )
{
  $DB_SQL = 'UPDATE sacoche_livret_page ';
  $DB_SQL.= 'SET livret_page_rubrique_join=:rubrique_join ';
  $DB_SQL.= 'WHERE livret_page_rubrique_type=:rubrique_type ';
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_join' => $rubrique_join,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_jointures_référentiel
 *
 * @param string $rubrique_type
 * @return void
 */
public static function DB_supprimer_jointures_référentiel( $rubrique_type )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type ';
  $DB_VAR = array( ':rubrique_type' => $rubrique_type );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Seuils / Paramétrages de colonnes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_colonnes_infos
 *
 * @param string $livret_colonne_type (facultatif)
 * @return array
 */
public static function DB_lister_colonnes_infos($livret_colonne_type=NULL)
{
  $select = ($livret_colonne_type) ? '' : 'livret_colonne_type, ' ;
  $where  = ($livret_colonne_type) ? 'WHERE livret_colonne_type=:livret_colonne_type ' : '' ;
  $DB_SQL = 'SELECT '.$select.'livret_colonne_id, livret_colonne_ordre, livret_colonne_titre, livret_colonne_legende, livret_colonne_seuil_defaut_min, livret_colonne_seuil_defaut_max, livret_colonne_couleur_1 ';
  $DB_SQL.= 'FROM sacoche_livret_colonne ';
  $DB_SQL.= $where;
  $DB_SQL.= 'ORDER BY livret_colonne_ordre ASC ';
  $DB_VAR = array( ':livret_colonne_type' => $livret_colonne_type );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE );
}

/**
 * lister_seuils_valeurs
 *
 * @return array
 */
public static function DB_lister_seuils_valeurs()
{
  $DB_SQL = 'SELECT CONCAT(livret_page_ref,"_",livret_colonne_id) AS livret_colonne_id , livret_seuil_min , livret_seuil_max ';
  $DB_SQL.= 'FROM sacoche_livret_seuil ';
  $DB_SQL.= 'ORDER BY livret_colonne_id ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE );
}

/**
 * lister_page_seuils_infos
 *
 * @param string $livret_page_ref
 * @param string $livret_page_colonne facultatif et requis uniquement pour 6e 5e 4e 3e où il peut y avoir position ou objectif
 * @return array
 */
public static function DB_lister_page_seuils_infos( $livret_page_ref , $livret_page_colonne=NULL )
{
  $where  = (!$livret_page_colonne) ? '' : 'AND livret_colonne_type=:livret_colonne_type ' ;
  $DB_SQL = 'SELECT livret_colonne_id , livret_seuil_min , livret_seuil_max , livret_colonne_legende ';
  $DB_SQL.= 'FROM sacoche_livret_seuil ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_colonne USING (livret_colonne_id) ';
  $DB_SQL.= 'WHERE livret_page_ref=:livret_page_ref '.$where;
  $DB_SQL.= 'ORDER BY livret_colonne_id ASC ';
  $DB_VAR = array(
    ':livret_page_ref'     => $livret_page_ref,
    ':livret_colonne_type' => $livret_page_colonne,
    );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
}

/**
 * modifier_page_colonne
 *
 * @param string $page_ref
 * @param string $colonne
 * @param int    $moy_classe
 * @return void
 */
public static function DB_modifier_page_colonne( $page_ref , $colonne , $moy_classe )
{
  $DB_SQL = 'UPDATE sacoche_livret_page ';
  $DB_SQL.= 'SET livret_page_colonne=:colonne, livret_page_moyenne_classe=:moy_classe ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_VAR = array(
    ':page_ref'   => $page_ref,
    ':colonne'    => $colonne,
    ':moy_classe' => $moy_classe,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_seuils
 *
 * @param string $page_ref
 * @param array  $tab_seuils
 * @return void
 */
public static function DB_modifier_seuils( $page_ref , $tab_seuils )
{
  $DB_SQL = 'UPDATE sacoche_livret_seuil ';
  $DB_SQL.= 'SET livret_seuil_min=:seuil_min, livret_seuil_max=:seuil_max ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref AND livret_colonne_id=:colonne_id ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  foreach($tab_seuils as $colonne_id => $tab)
  {
    $DB_VAR[':colonne_id'] = $colonne_id;
    $DB_VAR[':seuil_min'] = $tab['min'];
    $DB_VAR[':seuil_max'] = $tab['max'];
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * DB_modifier_legende
 *
 * @param int    $colonne_id
 * @param string $colonne_legende
 * @return void
 */
public static function DB_modifier_legende( $colonne_id , $colonne_legende )
{
  $DB_SQL = 'UPDATE sacoche_livret_colonne ';
  $DB_SQL.= 'SET livret_colonne_legende=:colonne_legende ';
  $DB_SQL.= 'WHERE livret_colonne_id=:colonne_id ';
  $DB_VAR = array(
    ':colonne_id'      => $colonne_id,
    ':colonne_legende' => $colonne_legende,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Jointures livret / groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * compter_impression_archives
 *
 * @param string $annee_scolaire
 * @return array
 */
public static function DB_compter_impression_archives($annee_scolaire)
{
  $DB_SQL = 'SELECT COUNT(officiel_archive_id) AS nombre, eleve_classe_id, periode_livret ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING(periode_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) ';
  $DB_SQL.= 'WHERE archive_type=:archive_type AND annee_scolaire=:annee_scolaire ';
  $DB_SQL.= 'GROUP BY eleve_classe_id, periode_livret ';
  $DB_VAR = array(
    ':archive_type'   => "livret",
    ':annee_scolaire' => $annee_scolaire,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_classes_periode
 *
 * @param void
 * @return bool
 */
public static function DB_tester_classes_periode()
{
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'WHERE livret_page_periodicite=:periodicite ';
  $DB_VAR = array( ':periodicite' => 'periode' );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_jointure_classe_livret
 *
 * @param string $liste_page_ref
 * @return int
 */
public static function DB_tester_jointure_classe_livret($liste_page_ref)
{
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'WHERE livret_page_ref IN('.$liste_page_ref.') ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_jointures_classes_livret
 *
 * @param void
 * @return array
 */
public static function DB_lister_jointures_classes_livret()
{
  $DB_SQL = 'SELECT groupe_id, livret_page_ref, sacoche_livret_jointure_groupe.livret_page_periodicite, jointure_periode, jointure_etat, jointure_date_export, ';
  $DB_SQL.= 'livret_page_rubrique_type, periode_id, jointure_date_debut, jointure_date_fin ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode ON ( sacoche_livret_jointure_groupe.livret_page_periodicite = "periode" AND sacoche_livret_jointure_groupe.jointure_periode = sacoche_periode.periode_livret ) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_groupe_periode USING(groupe_id, periode_id) ';
  $DB_SQL.= 'ORDER BY groupe_id,livret_page_ordre, periode_ordre ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_classes_avec_jointures_livret
 *
 * @param int   $groupe_id   facultatif, pour restreindre à une classe donnée
 * @return array
 */
public static function DB_lister_classes_avec_jointures_livret( $groupe_id = NULL )
{
  $where_groupe = ($groupe_id) ? 'AND groupe_id=:groupe_id ' : '' ;
  $index_classe = ($groupe_id) ? FALSE : TRUE ;
  // À partir de MySQL 5.7 on peut utiliser ANY_VALUE()
  // @see http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_only_full_group_by
  // @see http://dev.mysql.com/doc/refman/5.7/en/miscellaneous-functions.html#function_any-value
  // Le test s'est révélé incorrect sur un serveur utilisant MariaDB, d'où la détermination du moteur en plus si possible
  // @see http://stackoverflow.com/questions/37317869/determine-if-mysql-or-percona-or-mariadb
  // Enfin, MariaDB n'a pas implémenté la fonction ANY_VALUE...
  // @see https://mariadb.com/kb/en/mariadb/functions-and-modifiers-for-use-with-group-by/
  
  if( empty($_SESSION['sql_any_value']) )
  {
    $sql_version = DB_STRUCTURE_COMMUN::DB_recuperer_version_MySQL();
    $sql_engine  = stripos( $sql_version , 'MariaDB' ) ? 'MariaDB' : 'MySQL' ;
    $_SESSION['sql_any_value'] = ( ($sql_engine=='MySQL') && version_compare($sql_version,'5.7','>=') ) ? 'ANY_VALUE' : 'MAX' ;
  }
  $DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_chef_id, livret_page_ref, livret_page_moment, livret_page_titre_classe, livret_page_resume, '.$_SESSION['sql_any_value'].'(sacoche_livret_jointure_groupe.livret_page_periodicite) AS periodicite, GROUP_CONCAT(jointure_periode) AS listing_periodes ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'WHERE groupe_type="classe" '.$where_groupe;
  $DB_SQL.= 'GROUP BY groupe_id, livret_page_ref ';
  $DB_SQL.= 'ORDER BY niveau_id ASC, groupe_nom ASC ';
  $DB_VAR = array( ':groupe_id' => $groupe_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $index_classe);
}

/**
 * ajouter_jointure_groupe
 *
 * @param int      $groupe_id
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @param array    $tab_jointure_periode
 * @return array
 */
public static function DB_ajouter_jointure_groupe( $groupe_id , $page_ref , $page_periodicite , $tab_jointure_periode )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_groupe( groupe_id, livret_page_ref, livret_page_periodicite, jointure_periode, jointure_etat) ';
  $DB_SQL.= 'VALUES                                    (:groupe_id,       :page_ref,       :page_periodicite,:jointure_periode,:jointure_etat)';
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
    ':jointure_etat'    => '1vide',
  );
  foreach($tab_jointure_periode as $jointure_periode)
  {
    $DB_VAR[':jointure_periode'] = $jointure_periode;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * modifier_jointure_groupe
 *
 * @param int      $groupe_id    id du groupe (en fait, obligatoirement une classe)
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @param int      $jointure_periode
 * @param string   $etat         nouvel état
 * @return int     0 ou 1 si modifié
 */
public static function DB_modifier_jointure_groupe( $groupe_id , $page_ref , $page_periodicite , $jointure_periode , $etat )
{
  $where_periode = ($jointure_periode) ? 'AND jointure_periode=:jointure_periode ' : '' ; // sinon il faut le test "AND jointure_periode IS NULL"
  $update_date = ($etat=='5complet') ? ', jointure_date_verrou=NOW() ' : '' ;
  $DB_SQL = 'UPDATE sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'SET jointure_etat=:etat '.$update_date;
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND livret_page_ref=:page_ref AND livret_page_periodicite=:page_periodicite '.$where_periode;
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
    ':jointure_periode' => $jointure_periode,
    ':etat'             => $etat,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_jointure_date_export
 *
 * @param int      $groupe_id    id du groupe (en fait, obligatoirement une classe)
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @param int      $jointure_periode
 * @return void
 */
public static function DB_modifier_jointure_date_export( $groupe_id , $page_ref , $page_periodicite , $jointure_periode )
{
  $where_periode = ($jointure_periode) ? 'AND jointure_periode=:jointure_periode ' : '' ; // sinon il faut le test "AND jointure_periode IS NULL"
  $DB_SQL = 'UPDATE sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'SET jointure_date_export=NOW() ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND livret_page_ref=:page_ref AND livret_page_periodicite=:page_periodicite '.$where_periode;
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
    ':jointure_periode' => $jointure_periode,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_jointure_groupe
 *
 * @param int      $groupe_id
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @return void
 */
public static function DB_supprimer_jointure_groupe( $groupe_id , $page_ref , $page_periodicite )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND livret_page_ref=:page_ref AND livret_page_periodicite=:page_periodicite';
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI | AP | Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * pages_for_dispositif
 *
 * @param string   $dispositif     ap | epi | parcours
 * @param string   $parcours_code  facultatif, seulement si parcours
 * @return array
 */
public static function DB_lister_pages_for_dispositif( $dispositif , $parcours_code=NULL )
{
  $test = ($dispositif!='parcours') ? ' = 1 ' : ' LIKE "%'.$parcours_code.'%" ' ;
  $DB_SQL = 'SELECT livret_page_ref, livret_page_ordre, livret_page_moment, livret_page_rubrique_join ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref) ';
  $DB_SQL.= 'WHERE livret_page_'.$dispositif.$test;
  $DB_SQL.= 'GROUP BY livret_page_ref ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_page_avec_dispositif
 *
 * @param string $page_ref
 * @param string $dispositif     epi | ap | parcours
 * @param string $parcours_code  facultatif, seulement si parcours
 * @return int
 */

public static function DB_tester_page_avec_dispositif( $page_ref , $dispositif , $parcours_code=NULL )
{
  $test = ($dispositif!='parcours') ? ' = 1 ' : ' LIKE "%'.$parcours_code.'%" ' ;
  $DB_SQL = 'SELECT livret_page_ordre ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref AND livret_page_'.$dispositif.$test;
  $DB_VAR = array( ':page_ref' => $page_ref );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les jointures prof / dispositif dépendant d'une matière plus alimentée
 *
 * @param string   $dispositif     ap | epi
 * @return int
 */
public static function DB_nettoyer_jointure_dispositif_matiere( $dispositif )
{
  $DB_SQL = 'DELETE sacoche_livret_jointure_'.$dispositif.'_prof ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_'.$dispositif.'_prof ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_'.$dispositif.' USING(livret_'.$dispositif.'_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_referentiel ON sacoche_livret_page.livret_page_rubrique_type = sacoche_livret_jointure_referentiel.livret_rubrique_type AND sacoche_livret_jointure_'.$dispositif.'_prof.matiere_id = livret_rubrique_ou_matiere_id ';
  $DB_SQL.= 'WHERE livret_rubrique_ou_matiere_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Supprimer les dispositifs non reliés à un enseignant
 *
 * @param string   $dispositif     ap | epi
 * @return int
 */
public static function DB_nettoyer_dispositif_sans_prof( $dispositif )
{
  $nb_prof_min = ($dispositif=='ap') ? 1 : 2 ;
  $DB_SQL = 'SELECT livret_'.$dispositif.'_id AS dispositif_id ';
  $DB_SQL.= 'FROM sacoche_livret_'.$dispositif.' ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_'.$dispositif.'_prof USING(livret_'.$dispositif.'_id) ';
  $DB_SQL.= 'GROUP BY livret_'.$dispositif.'_id ';
  $DB_SQL.= 'HAVING COUNT(prof_id)<'.$nb_prof_min;
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_delete = count($DB_TAB);
  if($nb_delete)
  {
    foreach($DB_TAB as $DB_ROW)
    {
      call_user_func_array( array('DB_STRUCTURE_LIVRET', 'DB_supprimer_'.$dispositif), array($DB_ROW['dispositif_id']) );
    }
  }
  return $nb_delete;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_epi
 *
 * @param int|string $classe_id_or_listing_classe_id facultatif, pour restreindre à une classe ou un ensemble de classes
 * @param string     $page_ref                       facultatif, pour restreindre à une page du livret
 * @return array
 */
public static function DB_lister_epi( $classe_id_or_listing_classe_id = NULL , $page_ref = NULL )
{
  if($classe_id_or_listing_classe_id && $page_ref)
  {
    $where = 'WHERE livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  }
  elseif($classe_id_or_listing_classe_id)
  {
    $where = 'WHERE groupe_id IN('.$classe_id_or_listing_classe_id.') ';
  }
  else
  {
    $where = ''; // Profil administrateur ou directeur
  }
  $saisie_count = ($page_ref) ? '' : 'COUNT(livret_saisie_id) AS epi_used, ' ;
  $saisie_join  = ($page_ref) ? '' : 'LEFT JOIN sacoche_livret_saisie ON sacoche_livret_epi.livret_epi_id=sacoche_livret_saisie.rubrique_id AND rubrique_type="epi" ' ;
  $DB_SQL = 'SELECT sacoche_livret_epi.*, sacoche_livret_epi_theme.*, livret_page_ordre, livret_page_moment, groupe_nom, '.$saisie_count;
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_id,"_",user_id) SEPARATOR " ") AS matiere_prof_id, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_nom," - ",user_nom," ",user_prenom) SEPARATOR "§BR§") AS matiere_prof_texte ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_epi_theme USING(livret_epi_theme_code) ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_epi_prof USING(livret_epi_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING(matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_epi_prof.prof_id = sacoche_user.user_id ';
  $DB_SQL.= $saisie_join;
  $DB_SQL.= $where;
  $DB_SQL.= 'GROUP BY livret_epi_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC, livret_epi_theme_nom ASC ';
  $DB_VAR = array(
    ':page_ref'  => $page_ref ,
    ':groupe_id' => $classe_id_or_listing_classe_id ,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_epi_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_epi_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, livret_epi_theme_code, COUNT(DISTINCT livret_epi_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref, livret_epi_theme_code ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_epi
 *
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $epi_id   inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
// Clef unique UNIQUE KEY livret_epi (livret_epi_theme_code, livret_page_ref, groupe_id) retirée : on tolère plusieurs EPI avec la même thématique pour un élève.
/*
public static function DB_tester_epi( $theme_code , $page_ref , $groupe_id , $epi_id=FALSE )
{
  $DB_SQL = 'SELECT livret_epi_id ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code AND livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  $DB_SQL.= ($epi_id) ? 'AND livret_epi_id!=:epi_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_id'     => $epi_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}
*/

/**
 * ajouter_epi
 *
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $epi_titre
 * @return int
 */
public static function DB_ajouter_epi( $theme_code , $page_ref , $groupe_id , $epi_titre )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_epi( livret_epi_theme_code,livret_page_ref, groupe_id,livret_epi_titre) ';
  $DB_SQL.= 'VALUES                        (           :theme_code,      :page_ref,:groupe_id,      :epi_titre)';
  $DB_VAR = array(
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_titre'  => $epi_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_epi_jointure
 *
 * @param int    $epi_id
 * @param int    $matiere_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_ajouter_epi_jointure( $epi_id , $matiere_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_epi_prof( livret_epi_id, matiere_id, prof_id) ';
  $DB_SQL.= 'VALUES                                      (       :epi_id,:matiere_id,:prof_id)';
  $DB_VAR = array(
    ':epi_id'     => $epi_id,
    ':matiere_id' => $matiere_id,
    ':prof_id'    => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_epi
 *
 * @param int    $epi_id
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $epi_titre
 * @return void
 */
public static function DB_modifier_epi( $epi_id , $theme_code , $page_ref , $groupe_id , $epi_titre )
{
  $DB_SQL = 'UPDATE sacoche_livret_epi ';
  $DB_SQL.= 'SET livret_epi_theme_code=:theme_code, livret_page_ref=:page_ref, groupe_id=:groupe_id, livret_epi_titre=:epi_titre ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array(
    ':epi_id'     => $epi_id,
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_titre'  => $epi_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_epi
 *
 * @param int    $epi_id
 * @return void
 */
public static function DB_supprimer_epi( $epi_id )
{
  // le dispositif
  $DB_SQL = 'DELETE sacoche_livret_epi, sacoche_livret_jointure_epi_prof ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_epi_prof USING (livret_epi_id) ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array( ':epi_id' => $epi_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // les saisies
  DB_STRUCTURE_LIVRET::DB_supprimer_saisies_dispositif( 'epi' , $epi_id );
}

/**
 * supprimer_epi_jointure
 *
 * @param int    $epi_id
 * @return void
 */
public static function DB_supprimer_epi_jointure( $epi_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_epi_prof ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array( ':epi_id' => $epi_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI Thématiques
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * tester_epi_theme
 *
 * @param string $theme_code
 * @return int
 */
public static function DB_tester_epi_theme( $theme_code )
{
  $DB_SQL = 'SELECT livret_epi_theme_origine ';
  $DB_SQL.= 'FROM sacoche_livret_epi_theme ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code ';
  $DB_VAR = array( ':theme_code' => $theme_code );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_epi_theme
 *
 * @param string $theme_code
 * @param string $theme_nom
 * @return void
 */
public static function DB_ajouter_epi_theme( $theme_code , $theme_nom )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_epi_theme( livret_epi_theme_code, livret_epi_theme_origine, livret_epi_theme_nom) ';
  $DB_SQL.= 'VALUES                              (           :theme_code,           :theme_origine,           :theme_nom)';
  $DB_VAR = array(
    ':theme_code'    => $theme_code,
    ':theme_origine' => 2,
    ':theme_nom'     => $theme_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_epi_theme
 *
 * @param string $theme_code
 * @param string $theme_nom
 * @return void
 */
public static function DB_modifier_epi_theme( $theme_code , $theme_nom )
{
  $DB_SQL = 'UPDATE sacoche_livret_epi_theme ';
  $DB_SQL.= 'SET livret_epi_theme_nom=:theme_nom ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code ';
  $DB_VAR = array(
    ':theme_code' => $theme_code,
    ':theme_nom'  => $theme_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_epi_theme
 *
 * @param string $theme_code
 * @param int    $theme_used
 * @return void
 */
public static function DB_supprimer_epi_theme( $theme_code , $theme_used )
{
  // la thématique
  $DB_SQL = 'DELETE FROM sacoche_livret_epi_theme ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code ';
  $DB_VAR = array( ':theme_code' => $theme_code );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // les dispositifs et les saisies
  if($theme_used)
  {
    $DB_SQL = 'SELECT livret_epi_id ';
    $DB_SQL.= 'FROM sacoche_livret_epi ';
    $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code ';
    $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    foreach($DB_COL as $livret_epi_id)
    {
      DB_STRUCTURE_LIVRET::DB_supprimer_epi( $livret_epi_id );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// AP
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_ap
 *
 * @param int|string $classe_id_or_listing_classe_id facultatif, pour restreindre à une classe ou un ensemble de classes
 * @param string     $page_ref                       facultatif, pour restreindre à une page du livret
 * @return array
 */
public static function DB_lister_ap( $classe_id_or_listing_classe_id = NULL , $page_ref = NULL )
{
  if($classe_id_or_listing_classe_id && $page_ref)
  {
    $where = 'WHERE livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  }
  elseif($classe_id_or_listing_classe_id)
  {
    $where = 'WHERE groupe_id IN('.$classe_id_or_listing_classe_id.') ';
  }
  else
  {
    $where = ''; // Profil administrateur ou directeur
  }
  $saisie_count = ($page_ref) ? '' : 'COUNT(livret_saisie_id) AS ap_used, ' ;
  $saisie_join  = ($page_ref) ? '' : 'LEFT JOIN sacoche_livret_saisie ON sacoche_livret_ap.livret_ap_id=sacoche_livret_saisie.rubrique_id AND rubrique_type="ap" ' ;
  $DB_SQL = 'SELECT sacoche_livret_ap.*, livret_page_ordre, livret_page_moment, groupe_nom, '.$saisie_count;
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_id,"_",user_id) SEPARATOR " ") AS matiere_prof_id, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_nom," - ",user_nom," ",user_prenom) SEPARATOR "§BR§") AS matiere_prof_texte ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_ap_prof USING(livret_ap_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING(matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_ap_prof.prof_id = sacoche_user.user_id ';
  $DB_SQL.= $saisie_join;
  $DB_SQL.= $where;
  $DB_SQL.= 'GROUP BY livret_ap_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC ';
  $DB_VAR = array(
    ':page_ref'  => $page_ref ,
    ':groupe_id' => $classe_id_or_listing_classe_id ,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_ap_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_ap_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, COUNT(DISTINCT livret_ap_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_ap
 *
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $ap_titre
 * @return int
 */
public static function DB_ajouter_ap( $page_ref , $groupe_id , $ap_titre )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_ap( livret_page_ref, groupe_id, livret_ap_titre) ';
  $DB_SQL.= 'VALUES                       (       :page_ref,:groupe_id,       :ap_titre)';
  $DB_VAR = array(
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':ap_titre'   => $ap_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_ap_jointure
 *
 * @param int    $ap_id
 * @param int    $matiere_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_ajouter_ap_jointure( $ap_id , $matiere_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_ap_prof( livret_ap_id, matiere_id, prof_id) ';
  $DB_SQL.= 'VALUES                                     (       :ap_id,:matiere_id,:prof_id)';
  $DB_VAR = array(
    ':ap_id'     => $ap_id,
    ':matiere_id' => $matiere_id,
    ':prof_id'    => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_ap
 *
 * @param int    $ap_id
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $ap_titre
 * @return void
 */
public static function DB_modifier_ap( $ap_id , $page_ref , $groupe_id , $ap_titre )
{
  $DB_SQL = 'UPDATE sacoche_livret_ap ';
  $DB_SQL.= 'SET livret_page_ref=:page_ref, groupe_id=:groupe_id, livret_ap_titre=:ap_titre ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array(
    ':ap_id'     => $ap_id,
    ':page_ref'  => $page_ref,
    ':groupe_id' => $groupe_id,
    ':ap_titre'  => $ap_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_ap
 *
 * @param int    $ap_id
 * @return void
 */
public static function DB_supprimer_ap( $ap_id )
{
  // le dispositif
  $DB_SQL = 'DELETE sacoche_livret_ap, sacoche_livret_jointure_ap_prof ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_ap_prof USING (livret_ap_id) ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array( ':ap_id' => $ap_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // les saisies
  DB_STRUCTURE_LIVRET::DB_supprimer_saisies_dispositif( 'ap' , $ap_id );
}

/**
 * supprimer_ap_jointure
 *
 * @param int    $ap_id
 * @return void
 */
public static function DB_supprimer_ap_jointure( $ap_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_ap_prof ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array( ':ap_id' => $ap_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_parcours_type
 *
 * @param void
 * @return array
 */
public static function DB_lister_parcours_type()
{
  $DB_SQL = 'SELECT sacoche_livret_parcours_type.* ';
  $DB_SQL.= 'FROM sacoche_livret_parcours_type ';
  $DB_SQL.= 'ORDER BY livret_parcours_type_nom ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * lister_parcours
 *
 * @param string     $parcours_code                  facultatif, pour restreindre à un type de parcours
 * @param int|string $classe_id_or_listing_classe_id facultatif, pour restreindre à une classe ou un ensemble de classes
 * @param string     $page_ref                       facultatif, pour restreindre à une page du livret
 * @return array
 */
public static function DB_lister_parcours( $parcours_code , $classe_id_or_listing_classe_id = NULL , $page_ref = NULL )
{
  $tab_where = array();
  if($parcours_code)
  {
    $tab_where[] = 'livret_parcours_type_code=:parcours_code ';
  }
  if($classe_id_or_listing_classe_id && $page_ref)
  {
    $tab_where[] = 'livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  }
  else if($classe_id_or_listing_classe_id)
  {
    $tab_where[] = 'groupe_id IN('.$classe_id_or_listing_classe_id.') ';
  }
  $saisie_count = ($page_ref) ? '' : 'COUNT(livret_saisie_id) AS parcours_used, ' ;
  $saisie_join  = ($page_ref) ? '' : 'LEFT JOIN sacoche_livret_saisie ON sacoche_livret_parcours.livret_parcours_id=sacoche_livret_saisie.rubrique_id AND rubrique_type="parcours" ' ;
  $DB_SQL = 'SELECT sacoche_livret_parcours.*, livret_parcours_type_nom, livret_page_ordre, livret_page_moment, groupe_nom, '.$saisie_count;
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT user_id SEPARATOR " ") AS prof_id, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(user_nom," ",user_prenom) SEPARATOR "§BR§") AS prof_texte ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_parcours_type USING(livret_parcours_type_code) ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_parcours_prof USING(livret_parcours_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_parcours_prof.prof_id = sacoche_user.user_id ';
  $DB_SQL.= $saisie_join;
  $DB_SQL.= 'WHERE '.implode('AND ',$tab_where);
  $DB_SQL.= 'GROUP BY livret_parcours_type_code, livret_page_ref, groupe_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC ';
  $DB_VAR = array(
    ':parcours_code' => $parcours_code ,
    ':page_ref'      => $page_ref ,
    ':groupe_id'     => $classe_id_or_listing_classe_id ,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_parcours_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_parcours_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, livret_parcours_type_code, COUNT(DISTINCT livret_parcours_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref, livret_parcours_type_code ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_parcours
 *
 * Remarque : Il n'y a qu'un parcours par classe, on ne teste pas l'enseignant.
 *
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $matiere_id
 * @param int    $parcours_id   inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_parcours( $parcours_code , $page_ref , $groupe_id , $parcours_id=FALSE )
{
  $DB_SQL = 'SELECT livret_parcours_id ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'WHERE livret_parcours_type_code=:parcours_code AND livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  $DB_SQL.= ($parcours_id) ? 'AND livret_parcours_id!=:parcours_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
    ':parcours_id'   => $parcours_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_parcours
 *
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @return int
 */
public static function DB_ajouter_parcours( $parcours_code , $page_ref , $groupe_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_parcours( livret_parcours_type_code, livret_page_ref, groupe_id) ';
  $DB_SQL.= 'VALUES                             (            :parcours_code,       :page_ref,:groupe_id)';
  $DB_VAR = array(
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_parcours_jointure
 *
 * @param int    $parcours_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_ajouter_parcours_jointure( $parcours_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_parcours_prof(livret_parcours_id, prof_id) ';
  $DB_SQL.= 'VALUES                                           (      :parcours_id,:prof_id)';
  $DB_VAR = array(
    ':parcours_id' => $parcours_id,
    ':prof_id'     => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_parcours
 *
 * @param int    $parcours_id
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @return void
 */
public static function DB_modifier_parcours( $parcours_id , $parcours_code , $page_ref , $groupe_id )
{
  $DB_SQL = 'UPDATE sacoche_livret_parcours ';
  $DB_SQL.= 'SET livret_parcours_type_code=:parcours_code, livret_page_ref=:page_ref, groupe_id=:groupe_id ';
  $DB_SQL.= 'WHERE livret_parcours_id=:parcours_id ';
  $DB_VAR = array(
    ':parcours_id'   => $parcours_id,
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_parcours
 *
 * @param int    $parcours_id
 * @return void
 */
public static function DB_supprimer_parcours( $parcours_id )
{
  // le dispositif
  $DB_SQL = 'DELETE sacoche_livret_parcours, sacoche_livret_jointure_parcours_prof ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_parcours_prof USING (livret_parcours_id) ';
  $DB_SQL.= 'WHERE livret_parcours_id=:parcours_id ';
  $DB_VAR = array( ':parcours_id' => $parcours_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // les saisies
  DB_STRUCTURE_LIVRET::DB_supprimer_saisies_dispositif( 'parcours' , $parcours_id );
}

/**
 * supprimer_parcours_jointure
 *
 * @param int    $parcours_id
 * @return void
 */
public static function DB_supprimer_parcours_jointure( $parcours_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_parcours_prof ';
  $DB_SQL.= 'WHERE livret_parcours_id=:parcours_id ';
  $DB_VAR = array( ':parcours_id' => $parcours_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modalités d'accompagnement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * tester_modaccomp
 *
 * @param string $modaccomp_code
 * @return bool
 */
public static function DB_tester_modaccomp( $modaccomp_code )
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_livret_modaccomp ';
  $DB_SQL.= 'WHERE livret_modaccomp_code=:modaccomp_code ';
  $DB_VAR = array( ':modaccomp_code' => $modaccomp_code );
  return (bool) DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleve_modaccomp
 *
 * @param string $liste_eleves_id   facultatif, pour restreindre à des élèves donnés
 * @param string $only_groupes_id   facultatif, pour restreindre à un ensemble de groupes
 * @return array
 */
public static function DB_lister_eleve_modaccomp( $liste_eleves_id = NULL , $only_groupes_id = NULL )
{
  $where_eleve  = ($liste_eleves_id) ? 'AND eleve_id IN('.$liste_eleves_id.') '  : '' ;
  $where_groupe = ($only_groupes_id) ? 'AND groupe_id IN('.$only_groupes_id.') ' : '' ;
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, groupe_nom, livret_modaccomp_code, livret_modaccomp_nom, info_complement ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_modaccomp_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_modaccomp USING(livret_modaccomp_code) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_modaccomp_eleve.eleve_id = sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id = sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() AND groupe_id IS NOT NULL '.$where_eleve.$where_groupe;
  $DB_SQL.= 'ORDER BY groupe_nom ASC, user_nom ASC, user_prenom ASC, livret_modaccomp_code ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_eleve_modaccomp
 *
 * @param string $modaccomp_code
 * @param int    $eleve_id
 * @param string $info_complement
 * @return void
 */
public static function DB_modifier_eleve_modaccomp( $modaccomp_code , $eleve_id , $info_complement )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_modaccomp_eleve ( livret_modaccomp_code , eleve_id , info_complement) ';
  $DB_SQL.= 'VALUES                                              (       :modaccomp_code ,:eleve_id ,:info_complement)';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE info_complement=:info_complement ';
  $DB_VAR = array(
    ':modaccomp_code'  => $modaccomp_code,
    ':eleve_id'        => $eleve_id,
    ':info_complement' => $info_complement,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_eleve_modaccomp
 *
 * @param string $modaccomp_code
 * @param int    $eleve_id
 * @return void
 */
public static function DB_supprimer_eleve_modaccomp( $modaccomp_code , $eleve_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_modaccomp_eleve ';
  $DB_SQL.= 'WHERE livret_modaccomp_code=:modaccomp_code AND eleve_id=:eleve_id ';
  $DB_VAR = array(
    ':modaccomp_code' => $modaccomp_code,
    ':eleve_id'       => $eleve_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enseignements de complément
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * tester_classes_enscompl
 *
 * @param void
 * @return bool
 */
public static function DB_tester_classes_enscompl()
{
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_VAR = array( ':page_ref' => '3e' );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_enscompl
 *
 * @param string $enscompl_code
 * @return bool
 */
public static function DB_tester_enscompl( $enscompl_code )
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_livret_enscompl ';
  $DB_SQL.= 'WHERE livret_enscompl_code=:enscompl_code ';
  $DB_VAR = array( ':enscompl_code' => $enscompl_code );
  return (bool) DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleve_enscompl
 *
 * @param string $liste_eleve    facultatif, pour restreindre à des élèves donnés
 * @return array
 */
public static function DB_lister_eleve_enscompl( $liste_eleve = NULL )
{
  $where = ($liste_eleve) ? 'AND eleve_id IN('.$liste_eleve.') ' : '' ;
  $DB_SQL = 'SELECT eleve_id, livret_enscompl_id, livret_enscompl_code , livret_enscompl_nom ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_enscompl_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_enscompl USING(livret_enscompl_code) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_enscompl_eleve.eleve_id = sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id = sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() AND groupe_id IS NOT NULL '.$where;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_eleve_enscompl
 *
 * @param string $enscompl_code
 * @param int    $eleve_id
 * @return void
 */
public static function DB_modifier_eleve_enscompl( $enscompl_code , $eleve_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_enscompl_eleve ( livret_enscompl_code , eleve_id ) ';
  $DB_SQL.= 'VALUES                                             (       :enscompl_code ,:eleve_id )';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE livret_enscompl_code=:enscompl_code ';
  $DB_VAR = array(
    ':enscompl_code'  => $enscompl_code,
    ':eleve_id'       => $eleve_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_eleve_enscompl
 *
 * @param string $listing_eleve_id
 * @return void
 */
public static function DB_supprimer_eleve_enscompl( $listing_eleve_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_enscompl_eleve ';
  $DB_SQL.= 'WHERE eleve_id IN('.$listing_eleve_id.') ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL);
  // on n'efface pas la saisie éventuelle pour la retrouver en cas de fausse manip ; son reliquat ne gène ni la saisie ni la collecte
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Options de formulaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Retourner un tableau [valeur texte optgroup] des thématiques d'EPI
 *
 * @param test_used   Pour demander à tester s'il y a un EPI associé à une thématique
 * @return array
 */
public static function DB_OPT_epitheme($test_used)
{
  $saisie_count = (!$test_used) ? '' : ', COUNT(livret_epi_id) AS theme_used ' ;
  $saisie_join  = (!$test_used) ? '' : 'LEFT JOIN sacoche_livret_epi USING(livret_epi_theme_code) ' ;
  $group_by     = (!$test_used) ? '' : 'GROUP BY livret_epi_theme_code ' ;
  $DB_SQL = 'SELECT livret_epi_theme_code AS valeur, livret_epi_theme_nom AS texte, livret_epi_theme_origine AS optgroup '.$saisie_count;
  $DB_SQL.= 'FROM sacoche_livret_epi_theme ';
  $DB_SQL.= $saisie_join;
  $DB_SQL.= $group_by;
  $DB_SQL.= 'ORDER BY livret_epi_theme_origine ASC, livret_epi_theme_nom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des modalités d'accompagnement
 *
 * @param void
 * @return array
 */
public static function DB_OPT_modaccomp()
{
  $DB_SQL = 'SELECT livret_modaccomp_code AS valeur, CONCAT( livret_modaccomp_code ," : " , livret_modaccomp_nom ) AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_modaccomp ';
  $DB_SQL.= 'ORDER BY livret_modaccomp_code ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des enseignements de complément
 *
 * @param void
 * @return array
 */
public static function DB_OPT_enscompl()
{
  $DB_SQL = 'SELECT livret_enscompl_code AS valeur, CONCAT( livret_enscompl_code ," : " , livret_enscompl_nom ) AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_enscompl ';
  $DB_SQL.= 'ORDER BY livret_enscompl_code ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des classes associées à une page du livret
 *
 * @param string $page_ref
 * @param string $only_groupes_id   facultatif, pour restreindre à un ensemble de groupes
 * @return array
 */
public static function DB_OPT_groupes_for_page( $page_ref , $only_groupes_id = NULL )
{
  $where_groupe = ($only_groupes_id) ? 'AND groupe_id IN('.$only_groupes_id.') ' : '' ;
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref '.$where_groupe;
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY niveau_id ASC, groupe_nom ASC ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : ( ($where_groupe) ? 'Aucune classe trouvée sur laquelle vous ayez les droits suffisants.' : 'Aucune classe trouvée.' ) ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Nettoyage
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * supprimer_jointure_referentiel_obsolete
 *
 * Lors de la suppression de référentiels ou d'éléments de référentiels, la table sacoche_livret_jointure_referentiel n'est pas nettoyée car c'est un peu pénible, on le fait donc ici.
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_jointure_referentiel_obsolete()
{
  // Les liaisons aux éléments de référentiels
  $tab_element = array(
    'matiere' => 'sacoche_matiere',
    'domaine' => 'sacoche_referentiel_domaine',
    'theme'   => 'sacoche_referentiel_theme',
    'item'    => 'sacoche_referentiel_item',
    'user'    => "sacoche_user",
  );
  foreach($tab_element as $element => $table)
  {
    $DB_SQL = 'DELETE sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_page ON sacoche_livret_jointure_referentiel.livret_rubrique_type = sacoche_livret_page.livret_page_rubrique_type ';
    $DB_SQL.= 'LEFT JOIN '.$table.' ON sacoche_livret_jointure_referentiel.element_id = '.$table.'.'.$element.'_id ';
    $DB_SQL.= 'WHERE livret_page_rubrique_join="'.$element.'" AND '.$table.'.'.$element.'_id IS NULL ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  }
  // Les liaisons aux matières du Livret Scolaire ; test si la matière n'existe plus ou n'est plus active
  $DB_SQL = 'DELETE sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page ON sacoche_livret_jointure_referentiel.livret_rubrique_type = sacoche_livret_page.livret_page_rubrique_type ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere AS matiere1 ON sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id = matiere1.matiere_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere AS matiere2 ON sacoche_livret_jointure_referentiel.element_id = matiere2.matiere_id ';
  $DB_SQL.= 'WHERE livret_page_rubrique_join="matiere" AND ( (matiere1.matiere_id IS NULL) OR (matiere2.matiere_id IS NULL) OR ( matiere1.matiere_active=0 AND matiere1.matiere_siecle=0 ) OR ( matiere2.matiere_active=0 AND matiere2.matiere_siecle=0 ) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Les liaisons aux matières du Livret Scolaire ; test s'il existe au moins un référentiel dans cette matière
  $DB_SQL = 'DELETE sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page ON sacoche_livret_jointure_referentiel.livret_rubrique_type = sacoche_livret_page.livret_page_rubrique_type ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel ON sacoche_livret_jointure_referentiel.element_id = sacoche_referentiel.matiere_id ';
  $DB_SQL.= 'WHERE livret_page_rubrique_join="matiere" AND sacoche_referentiel.matiere_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_bilans_officiels
 *
 * @param void
 * @return void
 */
public static function DB_vider_livret()
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_ap'       , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_epi'      , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_export'   , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_parcours' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_ap_prof'       , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_epi_prof'      , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_parcours_prof' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_enscompl_eleve'  , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_modaccomp_eleve' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_saisie' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_saisie_jointure_prof' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_saisie_memo_detail'   , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_etat="1vide", jointure_date_verrou=NULL, jointure_date_export=NULL' , NULL);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * recuperer_donnees_eleves
 *
 * @param string $livret_page_ref
 * @param string $livret_page_periodicite
 * @param int    $jointure_periode
 * @param string $liste_rubrique_type   Vide pour toutes les rubriques
 * @param string $liste_eleve_id
 * @param int    $prof_id               Pour restreindre aux saisies d'un prof.
 * @param bool   $with_periodes_avant   On récupère aussi les données des périodes antérieures.
 * @return array
 */
public static function DB_recuperer_donnees_eleves( $livret_page_ref , $livret_page_periodicite , $jointure_periode , $liste_rubrique_type , $liste_eleve_id , $prof_id , $with_periodes_avant )
{
  $select_periode = ($with_periodes_avant) ? ', jointure_periode ' : '' ;
  $where_periode  = ($with_periodes_avant || !$jointure_periode) ? '' : 'AND jointure_periode=:jointure_periode ' ;
  $where_rubrique = ($liste_rubrique_type) ? 'AND rubrique_type IN('.$liste_rubrique_type.') ' : '' ;
  $where_prof     = ($prof_id) ? 'AND ( sacoche_livret_saisie.prof_id IN('.$prof_id.',0) OR sacoche_livret_saisie_jointure_prof.prof_id='.$prof_id.' ) ' : '' ;
  $order_periode  = ($with_periodes_avant) ? 'ORDER BY jointure_periode ASC ' : '' ;
  $DB_SQL = 'SELECT livret_saisie_id, rubrique_type, rubrique_id, cible_id AS eleve_id, saisie_objet, saisie_valeur, saisie_origine, ';
  $DB_SQL.= 'sacoche_livret_saisie.prof_id AS user_id, user_genre, user_nom, user_prenom, ';
  $DB_SQL.= 'GROUP_CONCAT(sacoche_livret_saisie_jointure_prof.prof_id) AS listing_profs, ';
  $DB_SQL.= 'acquis_detail '.$select_periode;
  $DB_SQL.= 'FROM sacoche_livret_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING(livret_saisie_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_memo_detail USING(livret_saisie_id) ';
  $DB_SQL.= 'WHERE livret_page_ref=:livret_page_ref AND livret_page_periodicite=:livret_page_periodicite '.$where_periode.$where_rubrique.' AND cible_nature=:cible_nature AND cible_id IN ('.$liste_eleve_id.') '.$where_prof;
  $DB_SQL.= 'GROUP BY (livret_saisie_id) ';
  $DB_SQL.= $order_periode;
  $DB_VAR = array(
    ':livret_page_ref'         => $livret_page_ref,
    ':livret_page_periodicite' => $livret_page_periodicite,
    ':jointure_periode'        => $jointure_periode,
    ':cible_nature'            => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_donnees_classe
 *
 * @param string $livret_page_ref
 * @param string $livret_page_periodicite
 * @param int    $jointure_periode
 * @param string $liste_rubrique_type   Vide pour toutes les rubriques
 * @param int    $classe_id
 * @param int    $prof_id               Pour restreindre aux saisies d'un prof.
 * @param bool   $with_periodes_avant   On récupère aussi les données des périodes antérieures.
 * @return array
 */
public static function DB_recuperer_donnees_classe( $livret_page_ref , $livret_page_periodicite , $jointure_periode , $liste_rubrique_type , $classe_id , $prof_id , $with_periodes_avant )
{
  $select_periode = ($with_periodes_avant) ? ', jointure_periode ' : '' ;
  $where_periode  = ($with_periodes_avant || !$jointure_periode) ? '' : 'AND jointure_periode=:jointure_periode ' ;
  $where_rubrique = ($liste_rubrique_type) ? 'AND rubrique_type IN('.$liste_rubrique_type.') ' : '' ;
  $where_prof     = ($prof_id) ? 'AND ( sacoche_livret_saisie.prof_id='.$prof_id.' OR sacoche_livret_saisie_jointure_prof.prof_id='.$prof_id.' ) ' : '' ;
  $order_periode  = ($with_periodes_avant) ? 'ORDER BY jointure_periode ASC ' : '' ;
  $DB_SQL = 'SELECT livret_saisie_id, rubrique_type, rubrique_id, saisie_objet, saisie_valeur, saisie_origine, sacoche_livret_saisie.prof_id AS user_id, user_genre, user_nom, user_prenom, GROUP_CONCAT(sacoche_livret_saisie_jointure_prof.prof_id) AS listing_profs  '.$select_periode;
  $DB_SQL.= 'FROM sacoche_livret_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING(livret_saisie_id) ';
  $DB_SQL.= 'WHERE livret_page_ref=:livret_page_ref AND livret_page_periodicite=:livret_page_periodicite '.$where_periode.$where_rubrique.' AND cible_nature=:cible_nature AND cible_id=:cible_id '.$where_prof;
  $DB_SQL.= 'GROUP BY (livret_saisie_id) ';
  $DB_SQL.= $order_periode;
  $DB_VAR = array(
    ':livret_page_ref'         => $livret_page_ref,
    ':livret_page_periodicite' => $livret_page_periodicite,
    ':jointure_periode'        => $jointure_periode,
    ':cible_nature'            => 'classe',
    ':cible_id'                => $classe_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_ajouter_saisie
 *
 * @param string  $livret_page_ref
 * @param string  $livret_page_periodicite
 * @param int     $jointure_periode
 * @param string  $rubrique_type
 * @param int     $rubrique_id
 * @param string  $cible_nature
 * @param int     $cible_id
 * @param string  $saisie_objet
 * @param decimal $saisie_valeur
 * @param decimal $saisie_origine
 * @param int     $prof_id
 * @return int
 */
public static function DB_ajouter_saisie( $livret_page_ref , $livret_page_periodicite , $jointure_periode , $rubrique_type , $rubrique_id , $cible_nature , $cible_id , $saisie_objet , $saisie_valeur , $saisie_origine , $prof_id )
{
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_livret_saisie ( livret_page_ref,  livret_page_periodicite,  jointure_periode,  rubrique_type,  rubrique_id,  cible_nature,  cible_id,  saisie_objet,  saisie_valeur,  saisie_origine,  prof_id) ';
  $DB_SQL.= 'VALUES                            (:livret_page_ref, :livret_page_periodicite, :jointure_periode, :rubrique_type, :rubrique_id, :cible_nature, :cible_id, :saisie_objet, :saisie_valeur, :saisie_origine, :prof_id) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE saisie_valeur=:saisie_valeur, saisie_origine=:saisie_origine, prof_id=:prof_id ';
  $DB_VAR = array(
    ':livret_page_ref'         => $livret_page_ref,
    ':livret_page_periodicite' => $livret_page_periodicite,
    ':jointure_periode'        => $jointure_periode,
    ':rubrique_type'           => $rubrique_type,
    ':rubrique_id'             => $rubrique_id,
    ':cible_nature'            => $cible_nature,
    ':cible_id'                => $cible_id,
    ':saisie_objet'            => $saisie_objet,
    ':saisie_valeur'           => $saisie_valeur,
    ':saisie_origine'          => $saisie_origine,
    ':prof_id'                 => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $livret_saisie_id = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
  if( $prof_id && ($saisie_objet=='appreciation') )
  {
    DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id );
  }
  return $livret_saisie_id;
}

/**
 * DB_modifier_saisie
 *
 * @param int     $livret_saisie_id
 * @param string  $saisie_objet
 * @param decimal $saisie_valeur
 * @param decimal $saisie_origine
 * @param int     $prof_id
 * @return void
 */
public static function DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $saisie_valeur , $saisie_origine , $prof_id )
{
  $DB_SQL = 'UPDATE sacoche_livret_saisie ';
  $DB_SQL.= 'SET saisie_valeur=:saisie_valeur, saisie_origine=:saisie_origine, prof_id=:prof_id ';
  $DB_SQL.= 'WHERE livret_saisie_id=:livret_saisie_id ';
  $DB_VAR = array(
    ':livret_saisie_id' => $livret_saisie_id,
    ':saisie_valeur'    => $saisie_valeur,
    ':saisie_origine'   => $saisie_origine,
    ':prof_id'          => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( $prof_id && ($saisie_objet=='appreciation') )
  {
    DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id );
  }
}

/**
 * DB_modifier_saisie_jointure_prof
 *
 * @param int     $livret_saisie_id
 * @param int     $prof_id
 * @param bool    $delete
 * @return void
 */
public static function DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id , $delete=FALSE )
{
  if(!$delete)
  {
    $DB_SQL = 'INSERT IGNORE INTO sacoche_livret_saisie_jointure_prof ( livret_saisie_id,  prof_id) VALUES (:livret_saisie_id, :prof_id) ';
  }
  else
  {
    $DB_SQL = 'DELETE FROM sacoche_livret_saisie_jointure_prof WHERE livret_saisie_id=:livret_saisie_id AND prof_id=:prof_id ';
  }
  $DB_VAR = array(
    ':livret_saisie_id' => $livret_saisie_id,
    ':prof_id'          => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_ajouter_saisie_memo_detail
 *
 * @param int     $livret_saisie_id
 * @param string  $acquis_detail
 * @return void
 */
public static function DB_ajouter_saisie_memo_detail( $livret_saisie_id , $acquis_detail )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_saisie_memo_detail ( livret_saisie_id,  acquis_detail) ';
  $DB_SQL.= 'VALUES                                        (:livret_saisie_id, :acquis_detail) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE acquis_detail=:acquis_detail ';
  $DB_VAR = array(
    ':livret_saisie_id' => $livret_saisie_id,
    ':acquis_detail'    => $acquis_detail,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_saisie_memo_detail
 *
 * @param int     $livret_saisie_id
 * @param string  $acquis_detail
 * @return void
 */
public static function DB_modifier_saisie_memo_detail( $livret_saisie_id , $acquis_detail )
{
  $DB_SQL = 'UPDATE sacoche_livret_saisie_memo_detail ';
  $DB_SQL.= 'SET acquis_detail=:acquis_detail ';
  $DB_SQL.= 'WHERE livret_saisie_id=:livret_saisie_id ';
  $DB_VAR = array(
    ':livret_saisie_id' => $livret_saisie_id,
    ':acquis_detail'    => $acquis_detail,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_saisie
 * Ne peut être qu'une note calculée automatiquement, car pour le reste un champ vide est enregistré
 *
 * @param int     $livret_saisie_id
 * @param string  $saisie_objet
 * @return void
 */
public static function DB_supprimer_saisie( $livret_saisie_id )
{
  $DB_SQL = 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_jointure_prof, sacoche_livret_saisie_memo_detail ';
  $DB_SQL.= 'FROM sacoche_livret_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) ';
  $DB_SQL.= 'WHERE livret_saisie_id=:livret_saisie_id ';
  $DB_VAR = array(
    ':livret_saisie_id' => $livret_saisie_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_saisies_dispositif
 *
 * @param string  $rubrique_type
 * @param int     $rubrique_id
 * @return void
 */
public static function DB_supprimer_saisies_dispositif( $rubrique_type , $rubrique_id )
{
  $DB_SQL = 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_jointure_prof ';
  $DB_SQL.= 'FROM sacoche_livret_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) ';
  $DB_SQL.= 'WHERE rubrique_type=:rubrique_type AND rubrique_id=:rubrique_id ';
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_id'   => $rubrique_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_recuperer_elements_programme
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id
 * @param int    $liste_item_id    id de items séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @return array
 */
public static function DB_recuperer_elements_programme( $liste_eleve_id , $liste_item_id , $date_mysql_debut , $date_mysql_fin )
{
  $where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') ' : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_item       = (strpos($liste_item_id ,',')) ? 'item_id IN('. $liste_item_id .') ' : 'item_id='. $liste_item_id .' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
  $where_date_debut = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '     : '' ;
  $where_date_fin   = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '       : '' ;
  $DB_SQL = 'SELECT eleve_id , item_id , item_nom , theme_id , theme_nom , domaine_id , domaine_nom , ';
  $DB_SQL.= 'COUNT(*) AS eval_nb , referentiel_mode_livret AS mode_livret ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'WHERE '.$where_eleve.'AND '.$where_item.$where_date_debut.$where_date_fin;
  $DB_SQL.= 'GROUP BY eleve_id, item_id ';
  $DB_SQL.= 'ORDER BY item_id ASC'; // Pour conserver le même ordre lors des différents appel
  $DB_VAR = array(
    ':date_debut' => $date_mysql_debut,
    ':date_fin'   => $date_mysql_fin,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export LSU
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Compter les élèves n'ayant pas d'identifiant Sconet renseigné
 *
 * @param void
 * @return int
 */
public static function DB_compter_eleves_actuels_sans_id_sconet()
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() AND user_sconet_id=:sconet_id ';
  $DB_VAR = array(
    ':profil_type' => 'eleve',
    ':sconet_id'   => 0,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter les élèves n'ayant pas d'identifiant national renseigné
 *
 * @param void
 * @return int
 */
public static function DB_compter_eleves_actuels_sans_INE()
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() AND (user_reference NOT REGEXP "^[0-9]{9}[0-9A-Z]{1}[A-Z]{1}$") '; // au 2D c'est 10 chiffres et 1 lettre mais au 1D c'est 9 chiffres et 2 lettres
  $DB_VAR = array(':profil_type'=>'eleve');
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_ajouter_livret_export_eleve
 *
 * @param int     $user_id
 * @param string  $livret_page_ref
 * @param string  $livret_page_periodicite
 * @param int     $jointure_periode
 * @param string  $sacoche_version
 * @param string  $export_contenu
 * @return int
 */
public static function DB_ajouter_livret_export_eleve( $user_id , $livret_page_ref , $livret_page_periodicite , $jointure_periode , $sacoche_version , $export_contenu )
{
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_livret_export ( user_id,  livret_page_ref,  livret_page_periodicite,  jointure_periode,  sacoche_version,  export_contenu) ';
  $DB_SQL.= 'VALUES                            (:user_id, :livret_page_ref, :livret_page_periodicite, :jointure_periode, :sacoche_version, :export_contenu) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE livret_page_ref=:livret_page_ref, sacoche_version=:sacoche_version, export_contenu=:export_contenu ';
  $DB_VAR = array(
    ':user_id'                 => $user_id,
    ':livret_page_ref'         => $livret_page_ref,
    ':livret_page_periodicite' => $livret_page_periodicite,
    ':jointure_periode'        => $jointure_periode,
    ':sacoche_version'         => $sacoche_version,
    ':export_contenu'          => $export_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_livret_export_eleves
 *
 * @param string  $liste_eleve
 * @param string  $livret_page_periodicite   facultatif, chaine vide pour toutes périodes
 * @param int     $jointure_periode   facultatif, chaine vide pour toutes périodes
 * @return array
 */
public static function DB_lister_livret_export_eleves( $liste_eleve , $livret_page_periodicite , $jointure_periode )
{
  $where_page_periodicite = ($livret_page_periodicite) ? 'AND livret_page_periodicite=:livret_page_periodicite ' : '' ;
  $where_jointure_periode = ($jointure_periode)        ? 'AND jointure_periode=:jointure_periode '               : '' ;
  $DB_SQL = 'SELECT user_id, livret_page_ref, sacoche_version, export_contenu ';
  $DB_SQL.= 'FROM sacoche_livret_export ';
  $DB_SQL.= 'WHERE user_id IN('.$liste_eleve.') '.$where_page_periodicite.$where_jointure_periode;
  $DB_SQL.= 'ORDER BY livret_page_ref ASC, jointure_periode ASC, user_id ASC ';
  $DB_VAR = array(
    ':livret_page_periodicite' => $livret_page_periodicite,
    ':jointure_periode'        => $jointure_periode,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>