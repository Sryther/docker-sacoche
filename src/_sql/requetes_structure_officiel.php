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
// Ces méthodes ne concernent essentiellement que les tables "sacoche_officiel_saisie", "sacoche_officiel_archive", "sacoche_officiel_assiduite".

class DB_STRUCTURE_OFFICIEL extends DB
{

/**
 * recuperer_bilan_officiel_infos
 *
 * @param int    $classe_id
 * @param int    $periode_id
 * @param string $bilan_type
 * @return array
 */
public static function DB_recuperer_bilan_officiel_infos( $classe_id , $periode_id , $bilan_type )
{
  $DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin, officiel_'.$bilan_type.', periode_nom, groupe_nom, groupe_configuration_'.$bilan_type.' AS configuration_ref ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE groupe_id=:classe_id AND periode_id=:periode_id ';
  $DB_VAR = array(
    ':classe_id'  => $classe_id,
    ':periode_id' => $periode_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_pays_majoritaire
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_pays_majoritaire()
{
  $DB_SQL = 'SELECT adresse_pays_nom ';
  $DB_SQL.= 'FROM sacoche_parent_adresse ';
  $DB_SQL.= 'WHERE adresse_pays_nom!="" ';
  $DB_SQL.= 'GROUP BY adresse_pays_nom ';
  $DB_SQL.= 'ORDER BY COUNT(adresse_pays_nom) DESC ';
  $DB_SQL.= 'LIMIT 1 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_bilan_officiel_saisies_eleves
 *
 * @param string $officiel_type
 * @param int    $periode_id
 * @param string $liste_eleve_id
 * @param int    $prof_id     Pour restreindre aux saisies d'un prof.
 * @param bool   $with_rubrique_nom      On récupère aussi le nom de la matière correspondante (pas prévu pour le socle).
 * @param bool   $with_periodes_avant    On récupère aussi les données des périodes antérieures.
 * @param bool   $only_synthese_generale Pour restreindre aux synthèses générales.
 * @return array
 */
public static function DB_recuperer_bilan_officiel_saisies_eleves( $officiel_type , $periode_id , $liste_eleve_id , $prof_id , $with_rubrique_nom , $with_periodes_avant , $only_synthese_generale )
{
  // TODO : retirer la récupération des groupes si la gestion d'appréciation par groupe n'est finalement pas possible
  $periode_where = ($with_periodes_avant) ? '' : 'AND periode_id=:periode_id' ;
  $DB_SQL = 'SELECT prof_id, eleve_ou_classe_id AS eleve_id, rubrique_id, saisie_note, saisie_appreciation, user_genre, user_nom, user_prenom ';
  $DB_SQL.= ', 0 AS groupe_id, NULL AS groupe_nom ';
  $DB_SQL.= ($with_rubrique_nom)   ? ', matiere_nom as rubrique_nom ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? ', periode_id , periode_ordre , periode_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= ($with_rubrique_nom)   ? 'LEFT JOIN sacoche_matiere ON sacoche_officiel_saisie.rubrique_id=sacoche_matiere.matiere_id ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? 'LEFT JOIN sacoche_periode USING(periode_id) ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type '.$periode_where.' AND eleve_ou_classe_id IN('.$liste_eleve_id.') AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? ( ( ($officiel_type=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ) ? 'AND prof_id IN(:prof_id,0) ' :  'AND prof_id=:prof_id ' ) : '' ;
  $DB_SQL.= ($only_synthese_generale) ? 'AND rubrique_id=0 ' : '' ;
  $DB_SQL.= 'ORDER BY ';
  $DB_SQL.= ($with_rubrique_nom)   ? 'matiere_ordre ASC, ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? 'periode_ordre ASC, ' : '' ;
  $DB_SQL.= 'user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
    ':prof_id'       => $prof_id,
    ':saisie_type'   => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_saisies_classe
 *
 * @param string $officiel_type
 * @param int    $periode_id
 * @param int    $classe_id
 * @param int    $prof_id     Pour restreindre aux saisies d'un prof.
 * @param bool   $with_periodes_avant   On récupère aussi les données des périodes antérieures.
 * @param bool   $only_synthese_generale Pour restreindre aux synthèses générales.
 * @return array
 */
public static function DB_recuperer_bilan_officiel_saisies_classe( $officiel_type , $periode_id , $classe_id , $prof_id , $with_periodes_avant , $only_synthese_generale )
{
  // TODO : retirer la jointure et la récupération des groupes si la gestion d'appréciation par groupe n'est finalement pas possible
  $periode_where = ($with_periodes_avant) ? '' : 'AND periode_id=:periode_id' ;
  $DB_SQL = 'SELECT prof_id, 0 AS eleve_id, rubrique_id, saisie_note, saisie_appreciation, user_genre, user_nom, user_prenom ';
  $DB_SQL.= ', groupe_id, groupe_nom ';
  $DB_SQL.= ', matiere_nom as rubrique_nom ';
  $DB_SQL.= ($with_periodes_avant) ? ', periode_id , periode_ordre , periode_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_officiel_saisie.rubrique_id=sacoche_matiere.matiere_id ';
  $DB_SQL.= ($with_periodes_avant) ? 'LEFT JOIN sacoche_periode USING(periode_id) ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type '.$periode_where.' AND eleve_ou_classe_id=:classe_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? ( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) ? 'AND prof_id IN(:prof_id,0) ' :  'AND prof_id=:prof_id ' ) : '' ;
  $DB_SQL.= ($only_synthese_generale) ? 'AND rubrique_id=0 ' : '' ;
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, ';
  $DB_SQL.= ($with_periodes_avant) ? 'periode_ordre ASC, ' : '' ;
  $DB_SQL.= 'user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
    ':classe_id'     => $classe_id,
    ':prof_id'       => $prof_id,
    ':saisie_type'   => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_recuperer_bilan_officiel_saisie_precise
 *
 * @param string $officiel_type   bulletin | releve
 * @param int    $periode_id
 * @param int    $eleve_ou_classe_id
 * @param int    $groupe_id
 * @param int    $rubrique_id
 * @param string $saisie_type   cible_nature
 * @param string $saisie_objet   position | appreciation
 * @return array  tableau dans le cas d'appréciations multiples
 */
public static function DB_recuperer_bilan_officiel_saisie_precise( $officiel_type , $periode_id , $eleve_ou_classe_id , $groupe_id , $rubrique_id , $saisie_type , $saisie_objet )
{
  $groupe_id = 0; // TODO : à supprimer lorsqu'une telle gestion d'appréciation par groupe sera effectivement possible
  $select = ($saisie_objet=='position') ? 'saisie_note' : 'saisie_appreciation' ;
  $where  = ($saisie_objet=='position') ? 'prof_id=0'   : 'prof_id!=0' ;
  $DB_SQL = 'SELECT prof_id , '.$select.' AS saisie_valeur ';
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id=:eleve_ou_classe_id AND groupe_id=:groupe_id AND rubrique_id=:rubrique_id AND '.$where.' AND saisie_type=:saisie_type ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':officiel_type'      => $officiel_type,
    ':periode_id'         => $periode_id,
    ':eleve_ou_classe_id' => $eleve_ou_classe_id,
    ':groupe_id'          => $groupe_id,
    ':rubrique_id'        => $rubrique_id,
    ':saisie_type'        => $saisie_type,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_notes_eleves_periode
 *
 * @param int     $periode_id
 * @param string  $liste_eleve_id
 * @param bool    $tri_matiere
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes_eleves_periode( $periode_id , $liste_eleve_id , $tri_matiere )
{
  $DB_SQL = 'SELECT eleve_ou_classe_id AS eleve_id, rubrique_id, saisie_note, saisie_appreciation ';
  $DB_SQL.= ($tri_matiere)   ? ', matiere_nom as rubrique_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= ($tri_matiere) ? 'LEFT JOIN sacoche_matiere ON sacoche_officiel_saisie.rubrique_id=sacoche_matiere.matiere_id ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id IN ('.$liste_eleve_id.') AND prof_id=:prof_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($tri_matiere) ? 'ORDER BY matiere_ordre ASC ' : '' ;
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':periode_id'    => $periode_id,
    ':prof_id'       => 0,
    ':saisie_type'   => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_notes_classe
 *
 * @param int    $periode_id
 * @param int    $classe_id
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes_classe( $periode_id , $classe_id )
{
  $DB_SQL = 'SELECT rubrique_id, saisie_note ';
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id=:classe_id AND prof_id=:prof_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':periode_id'    => $periode_id,
    ':classe_id'     => $classe_id,
    ':prof_id'       => 0,
    ':saisie_type'   => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_officiel_assiduite
 *
 * @param int    $periode_id
 * @param array  $eleve_id
 * @return array
 */
public static function DB_recuperer_officiel_assiduite( $periode_id , $eleve_id )
{
  $DB_SQL = 'SELECT assiduite_absence, assiduite_absence_nj, assiduite_retard, assiduite_retard_nj ';
  $DB_SQL.= 'FROM sacoche_officiel_assiduite ';
  $DB_SQL.= 'WHERE periode_id=:periode_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':periode_id' => $periode_id,
    ':user_id'    => $eleve_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner une liste de professeurs rattachés à des élèves (d'une classe donnée) et des matières données.
 *
 * @param int    $classe_id
 * @param string $liste_eleve_id
 * @param string $liste_matiere_id
 * @return array
 */
public static function DB_recuperer_professeurs_eleves_matieres( $classe_id , $liste_eleve_id , $liste_matiere_id )
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($classe_id), donc on commence par récupérer les groupes éventuels associés à aux élèves
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id IN('.$liste_eleve_id.') AND groupe_type=:type2 ';
  $DB_VAR = array( ':type2' => 'groupe' );
  $liste_groupe_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $liste_groupes = (!$liste_groupe_id) ? $classe_id : $classe_id.','.$liste_groupe_id ;
  // Maintenant qu'on a les matières et la classe / les groupes, on cherche les profs à la fois dans sacoche_jointure_user_matiere et sacoche_jointure_user_groupe .
  // On part de sacoche_jointure_user_matiere qui ne contient que des profs.
  $DB_SQL = 'SELECT DISTINCT CONCAT(matiere_id,"_",user_id) AS concat_id, matiere_id, user_id, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE matiere_id IN('.$liste_matiere_id.') AND groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_officiel_structure_origine
 *
 * @param string $structure_uai
 * @return array
 */
public static function DB_recuperer_officiel_structure_origine( $structure_uai )
{
  $DB_SQL = 'SELECT sacoche_structure_origine.* ';
  $DB_SQL.= 'FROM sacoche_structure_origine ';
  $DB_SQL.= 'WHERE structure_uai=:structure_uai ';
  $DB_VAR = array( ':structure_uai' => $structure_uai );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_officiel_archive_precise
 *
 * @param int   $officiel_archive_id
 * @return array
 */
public static function DB_recuperer_officiel_archive_precise( $officiel_archive_id )
{
  $DB_SQL = 'SELECT sacoche_officiel_archive.*, user_nom, user_prenom, ';
  $DB_SQL.= 'image1.archive_image_contenu AS image1_contenu, image2.archive_image_contenu AS image2_contenu, image3.archive_image_contenu AS image3_contenu, image4.archive_image_contenu AS image4_contenu ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive_image AS image1 ON archive_md5_image1=image1.archive_image_md5 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive_image AS image2 ON archive_md5_image2=image2.archive_image_md5 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive_image AS image3 ON archive_md5_image3=image3.archive_image_md5 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive_image AS image4 ON archive_md5_image4=image4.archive_image_md5 ';
  $DB_SQL.= 'WHERE officiel_archive_id=:officiel_archive_id ';
  $DB_VAR = array( ':officiel_archive_id' => $officiel_archive_id );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_officiel_archive_avec_infos
 *
 * @param string $listing_eleve
 * @param string $structure_uai  rien pour toutes les structures
 * @param string $annee_scolaire rien pour toutes les années
 * @param string $listing_type   rien pour tous les types
 * @param string $listing_ref    rien pour toutes les références
 * @param int    $periode_id     0 pour un cycle, NULL pour toutes les périodes
 * @param string $uai_origine    rien pour tous les établissements d'origine
 * @return array( $DB_TAB_Archives , $DB_TAB_Images )
 */
public static function DB_recuperer_officiel_archive_avec_infos( $listing_eleve , $structure_uai='' , $annee_scolaire='' , $listing_type='' , $listing_ref='' , $periode_id=NULL , $uai_origine='' )
{
  // where
  $where_archive_type = (!$listing_type)     ? '' : 'AND archive_type IN('.$listing_type.') ';
  $where_archive_ref  = (!$listing_ref)      ? '' : 'AND archive_ref IN('.$listing_ref.') ';
  $where_etabl        = (!$structure_uai)    ? '' : 'AND sacoche_officiel_archive.structure_uai=:structure_uai ';
  $where_annee        = (!$annee_scolaire)   ? '' : 'AND annee_scolaire=:annee_scolaire ';
  $where_periode      = is_null($periode_id) ? '' : 'AND periode_id=:periode_id ';
  $where_origine      = (!$uai_origine)      ? '' : 'AND sacoche_user.eleve_uai_origine=:uai_origine ';
  // join
  $join_origine       = (!$uai_origine)      ? '' : 'LEFT JOIN sacoche_structure_origine ON sacoche_user.eleve_uai_origine=sacoche_structure_origine.structure_uai ';
  // on assemble
  $DB_SQL = 'SELECT sacoche_officiel_archive.*, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) '.$join_origine;
  $DB_SQL.= 'WHERE user_id IN ('.$listing_eleve.') ';
  $DB_SQL.= $where_archive_type.$where_archive_ref.$where_etabl.$where_annee.$where_periode.$where_origine;
  $DB_SQL.= 'ORDER BY annee_scolaire ASC, periode_id ASC, structure_uai ASC, archive_type ASC, archive_ref ASC, user_nom ASC , user_prenom ASC ';
  $DB_VAR = array(
    ':structure_uai'  => $structure_uai,
    ':annee_scolaire' => $annee_scolaire,
    ':periode_id'     => $periode_id,
    ':uai_origine'    => $uai_origine,
  );
  $DB_TAB_Archives = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // On récupère le contenu des images à part pour ne pas multiplier leur volume par le nombre de bilans
  $tab_md5 = $DB_TAB_Images = array();
  if(!empty($DB_TAB_Archives))
  {
    foreach($DB_TAB_Archives as $DB_ROW)
    {
      $tab_md5[$DB_ROW['archive_md5_image1']] = $DB_ROW['archive_md5_image1'];
      $tab_md5[$DB_ROW['archive_md5_image2']] = $DB_ROW['archive_md5_image2'];
      $tab_md5[$DB_ROW['archive_md5_image3']] = $DB_ROW['archive_md5_image3'];
      $tab_md5[$DB_ROW['archive_md5_image4']] = $DB_ROW['archive_md5_image4'];
    }
    unset($tab_md5[NULL]);
    if(!empty($tab_md5))
    {
      $DB_SQL = 'SELECT archive_image_md5, archive_image_contenu ';
      $DB_SQL.= 'FROM sacoche_officiel_archive_image ';
      $DB_SQL.= 'WHERE archive_image_md5 IN ("'.implode('","',$tab_md5).'") ';
      $DB_TAB_Images = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE);
      unset($tab_md5);
    }
  }
  return array($DB_TAB_Archives,$DB_TAB_Images);
}

/**
 * recuperer_officiel_archive_sans_infos
 *
 * @param string $listing_eleve
 * @return array
 */
public static function DB_recuperer_officiel_archive_sans_infos( $listing_eleve )
{
  // on assemble
  $DB_SQL = 'SELECT sacoche_officiel_archive.*, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) ';
  $DB_SQL.= 'WHERE user_id IN ('.$listing_eleve.') ';
  $DB_SQL.= 'ORDER BY annee_scolaire DESC, periode_id DESC, structure_uai ASC, archive_type ASC, archive_ref ASC, user_nom ASC , user_prenom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_officiel_archive
 *
 * @param string $structure_uai
 * @param string $annee_scolaire rien pour toutes les années
 * @param string $archive_type   rien pour tous les types
 * @param string $archive_ref    rien pour toutes les références
 * @param int    $periode_id     0 pour un cycle, NULL pour toutes les périodes
 * @param array  $tab_eleve_id
 * @param bool   $with_infos
 * @param string $only_profil_non_vu   '' par défaut ; 'parent' | 'eleve' sinon
 * @return array    array( [eleve_id] => array( 0 => array( 'archive_date_generation' , 'archive_date_consultation_eleve' , 'archive_date_consultation_parent' ) ) )
 *               OU array( array( 'user_id' , '...' , 'archive_date_generation' , 'archive_date_consultation_eleve' , 'archive_date_consultation_parent' ) )
 */
public static function DB_lister_officiel_archive( $structure_uai , $annee_scolaire , $archive_type , $archive_ref , $periode_id , $tab_eleve_id , $with_infos , $only_profil_non_vu='' )
{
  $champ_consultation = 'archive_date_consultation_'.$only_profil_non_vu;
  $select         = ($with_infos) ? 'sacoche_officiel_archive.* ' : 'user_id, officiel_archive_id, archive_date_generation, archive_date_consultation_eleve, archive_date_consultation_parent ' ;
  // where
  $where_etabl    = ($structure_uai)      ? 'structure_uai=:structure_uai AND '   : '' ;
  $where_annee    = ($annee_scolaire)     ? 'annee_scolaire=:annee_scolaire AND ' : '' ;
  $where_type     = ($archive_type)       ? 'archive_type=:archive_type AND '     : '' ;
  $where_ref      = ($archive_ref)        ? 'archive_ref=:archive_ref AND '       : '' ;
  $where_periode  = !is_null($periode_id) ? 'periode_id=:periode_id AND '         : '' ;
  $where_profil   = ($only_profil_non_vu) ? $champ_consultation.' IS NULL AND '   : '' ;
  // order
  $order_type     = (!$archive_type)     ? 'archive_type ASC, '   : '' ;
  $order_ref      = (!$archive_ref)      ? 'archive_ref ASC, '    : '' ;
  $order_annee    = (!$annee_scolaire)   ? 'annee_scolaire ASC, ' : '' ;
  $order_periode  = is_null($periode_id) ? 'periode_id ASC, '     : '' ;
  // key
  $key_eleve_id   = ( $annee_scolaire && $archive_type && $archive_ref && !is_null($periode_id) ) ? TRUE : FALSE ; // Pas de test sur $structure_uai qui peut être une chaine vide
  // on assemble
  $DB_SQL = 'SELECT '.$select;
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'WHERE '.$where_etabl.$where_annee.$where_type.$where_ref.$where_periode.$where_profil.'user_id IN ('.implode(',',$tab_eleve_id).') ';
  $DB_SQL.= 'ORDER BY '.$order_type.$order_ref.$order_annee.$order_periode.'user_id ASC ';
  $DB_VAR = array(
    ':structure_uai'  => $structure_uai,
    ':annee_scolaire' => $annee_scolaire,
    ':archive_type'   => $archive_type,
    ':archive_ref'    => $archive_ref,
    ':periode_id'     => $periode_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $key_eleve_id);
}

/**
 * lister_officiel_assiduite
 *
 * @param int    $periode_id
 * @param string  $liste_eleve_id
 * @return array
 */
public static function DB_lister_officiel_assiduite( $periode_id , $liste_eleve_id )
{
  $DB_SQL = 'SELECT user_id, assiduite_absence, assiduite_absence_nj, assiduite_retard, assiduite_retard_nj ';
  $DB_SQL.= 'FROM sacoche_officiel_assiduite ';
  $DB_SQL.= 'WHERE periode_id=:periode_id AND user_id IN ('.$liste_eleve_id.') ';
  $DB_VAR = array(':periode_id'=>$periode_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_profs_principaux
 *
 * @param int   $classe_id
 * @return array
 */
public static function DB_lister_profs_principaux($classe_id)
{
  $DB_SQL = 'SELECT user_id, user_sconet_id, user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND jointure_pp=:pp AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':groupe_id' => $classe_id,
    ':pp'        => 1,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_adresses_parents_for_enfants
 *
 * @param string   $listing_user_id
 * @return array   array( eleve_id => array( i => array(info_resp) ) )
 */
public static function DB_lister_adresses_parents_for_enfants($listing_user_id)
{
  $DB_SQL = 'SELECT eleve_id, resp_legal_num, parent.user_genre, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.* ';
  $DB_SQL.= 'FROM sacoche_user AS enfant ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_parent_adresse USING (parent_id) ';
  // Pas de restriction sur parent.user_sortie_date car on édite aussi des bulletins à des élèves sortis en cours de période
  // A priori un responsable retiré car n'ayant plus de lien avec l'enfant a non seulement une date de sortie mais aussi sa jointure sacoche_jointure_parent_eleve retirée donc il n'est pas censé être récupéré ici
  $DB_SQL.= 'WHERE enfant.user_id IN ('.$listing_user_id.') ';
  $DB_SQL.= 'ORDER BY eleve_id ASC, parent.user_sortie_date DESC, resp_legal_num ASC '; // On ne gardera ensuite que les 2 premiers résultats par enfant
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * ajouter_officiel_archive_image
 *
 * @param string  $image_md5
 * @param string  $image_contenu
 * @return void
 */
public static function DB_ajouter_officiel_archive_image( $image_md5 , $image_contenu )
{
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_officiel_archive_image(archive_image_md5, archive_image_contenu) ';
  $DB_SQL.= 'VALUES                                    (       :image_md5,        :image_contenu) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE archive_image_contenu=:image_contenu ';
  $DB_VAR = array(
    ':image_md5'     => $image_md5,
    ':image_contenu' => $image_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_officiel_archive
 *
 * @param int     $user_id
 * @param string  $structure_uai
 * @param string  $annee_scolaire
 * @param string  $archive_type
 * @param string  $archive_ref
 * @param int     $periode_id
 * @param string  $periode_nom
 * @param string  $structure_denomination
 * @param string  $sacoche_version
 * @param string  $archive_contenu
 * @param array   $tab_image_md5
 * @param array   $tab_date   uniquement transmis en cas de transfert de données entre SACoche
 * @return int
 */
public static function DB_ajouter_officiel_archive( $user_id , $structure_uai , $annee_scolaire , $archive_type , $archive_ref , $periode_id , $periode_nom , $structure_denomination , $sacoche_version , $archive_contenu , $tab_image_md5 , $tab_date=array() )
{
  $tab_date      = $tab_date      + array_fill(0,3,NULL);
  $tab_image_md5 = $tab_image_md5 + array_fill(0,4,NULL);
  $date_generation = is_null($tab_date[0]) ? 'NOW()' : ':date_generation' ;
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_officiel_archive( user_id, structure_uai, annee_scolaire, archive_type, archive_ref, periode_id, periode_nom, structure_denomination, sacoche_version, archive_date_generation, archive_date_consultation_eleve, archive_date_consultation_parent, archive_contenu, archive_md5_image1, archive_md5_image2, archive_md5_image3, archive_md5_image4) ';
  $DB_SQL.= 'VALUES                              (:user_id,:structure_uai,:annee_scolaire,:archive_type,:archive_ref,:periode_id,:periode_nom,:structure_denomination,:sacoche_version,    '.$date_generation.',                     :date_eleve,                     :date_parent,:archive_contenu,:archive_md5_image1,:archive_md5_image2,:archive_md5_image3,:archive_md5_image4) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE periode_nom=:periode_nom , structure_denomination=:structure_denomination , sacoche_version=:sacoche_version , archive_contenu=:archive_contenu , ';
  $DB_SQL.= ' archive_md5_image1=:archive_md5_image1 , archive_md5_image2=:archive_md5_image2 , archive_md5_image3=:archive_md5_image3 , archive_md5_image4=:archive_md5_image4 ';
  $DB_VAR = array(
    ':user_id'                => $user_id,
    ':structure_uai'          => $structure_uai,
    ':annee_scolaire'         => $annee_scolaire,
    ':archive_type'           => $archive_type,
    ':archive_ref'            => $archive_ref,
    ':periode_id'             => $periode_id,
    ':periode_nom'            => $periode_nom,
    ':structure_denomination' => $structure_denomination,
    ':sacoche_version'        => $sacoche_version,
    ':date_generation'        => $tab_date[0],
    ':date_eleve'             => $tab_date[1],
    ':date_parent'            => $tab_date[2],
    ':archive_contenu'        => $archive_contenu,
    ':archive_md5_image1'     => $tab_image_md5[0],
    ':archive_md5_image2'     => $tab_image_md5[1],
    ':archive_md5_image3'     => $tab_image_md5[2],
    ':archive_md5_image4'     => $tab_image_md5[3],
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_officiel_archive
 *
 * @param int     $officiel_archive_id
 * @param string  $periode_nom
 * @param string  $structure_denomination
 * @param string  $sacoche_version
 * @param string  $archive_contenu
 * @param array   $tab_image_md5
 * @return void
 */
public static function DB_modifier_officiel_archive( $officiel_archive_id , $periode_nom , $structure_denomination , $sacoche_version , $archive_contenu , $tab_image_md5 )
{
  $tab_image_md5 = $tab_image_md5 + array_fill(0,4,NULL);
  $DB_SQL = 'UPDATE sacoche_officiel_archive ';
  $DB_SQL.= 'SET periode_nom=:periode_nom , structure_denomination=:structure_denomination , sacoche_version=:sacoche_version , archive_contenu=:archive_contenu , ';
  $DB_SQL.= ' archive_date_generation=NOW() , archive_date_consultation_eleve=NULL , archive_date_consultation_parent=NULL , ';
  $DB_SQL.= ' archive_md5_image1=:archive_md5_image1 , archive_md5_image2=:archive_md5_image2 , archive_md5_image3=:archive_md5_image3 , archive_md5_image4=:archive_md5_image4 ';
  $DB_SQL.= 'WHERE officiel_archive_id=:officiel_archive_id ';
  $DB_VAR = array(
    ':officiel_archive_id'    => $officiel_archive_id,
    ':periode_nom'            => $periode_nom,
    ':structure_denomination' => $structure_denomination,
    ':sacoche_version'        => $sacoche_version,
    ':archive_contenu'        => $archive_contenu,
    ':archive_md5_image1'     => $tab_image_md5[0],
    ':archive_md5_image2'     => $tab_image_md5[1],
    ':archive_md5_image3'     => $tab_image_md5[2],
    ':archive_md5_image4'     => $tab_image_md5[3],
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_officiel_archive_contenu_seul
 *
 * @param int     $officiel_archive_id
 * @param string  $archive_contenu
 * @return void
 */
public static function DB_modifier_officiel_archive_consultation( $officiel_archive_id , $archive_contenu )
{
  $DB_SQL = 'UPDATE sacoche_officiel_archive ';
  $DB_SQL.= 'SET archive_contenu=:archive_contenu ';
  $DB_SQL.= 'WHERE officiel_archive_id=:officiel_archive_id ';
  $DB_VAR = array(
    ':officiel_archive_id' => $officiel_archive_id,
    ':archive_contenu'     => $archive_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_officiel_archive_date_consultation
 *
 * @param int     $officiel_archive_id
 * @param string  $profil   "eleve" | "parent"
 * @return void
 */
public static function DB_modifier_officiel_archive_date_consultation( $officiel_archive_id , $profil )
{
  $DB_SQL = 'UPDATE sacoche_officiel_archive ';
  $DB_SQL.= 'SET archive_date_consultation_'.$profil.'=NOW() ';
  $DB_SQL.= 'WHERE officiel_archive_id=:officiel_archive_id ';
  $DB_VAR = array(
    ':officiel_archive_id' => $officiel_archive_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_ou_classe_id
 * @param int     $groupe_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @param string  $saisie_type
 * @param decimal $note
 * @param string  $appreciation
 * @return void
 */
public static function DB_modifier_bilan_officiel_saisie( $officiel_type , $periode_id , $eleve_ou_classe_id , $groupe_id , $rubrique_id , $prof_id , $saisie_type , $note , $appreciation )
{
  $groupe_id = 0; // TODO : à supprimer lorsqu'une telle gestion d'appréciation par groupe sera effectivement possible
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_officiel_saisie ( officiel_type,  periode_id,  eleve_ou_classe_id,  groupe_id,  rubrique_id,  prof_id,  saisie_type,  saisie_note,  saisie_appreciation) ';
  $DB_SQL.= 'VALUES                              (:officiel_type, :periode_id, :eleve_ou_classe_id, :groupe_id, :rubrique_id, :prof_id, :saisie_type, :saisie_note, :saisie_appreciation) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE saisie_note=:saisie_note, saisie_appreciation=:saisie_appreciation ';
  $DB_VAR = array(
    ':officiel_type'       => $officiel_type,
    ':periode_id'          => $periode_id,
    ':eleve_ou_classe_id'  => $eleve_ou_classe_id,
    ':groupe_id'           => $groupe_id,
    ':rubrique_id'         => $rubrique_id,
    ':prof_id'             => $prof_id,
    ':saisie_type'         => $saisie_type,
    ':saisie_note'         => $note,
    ':saisie_appreciation' => $appreciation,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_officiel_assiduite
 *
 * @param string   $mode     sconet | siecle | gepi | pronote | manuel
 * @param int      $periode_id
 * @param int      $user_id
 * @param int|null $nb_absence
 * @param int|null $nb_absence_nj
 * @param int|null $nb_retard
 * @param int|null $nb_retard_nj
 * @return void
 */
public static function DB_modifier_officiel_assiduite( $mode , $periode_id , $user_id , $nb_absence , $nb_absence_nj , $nb_retard , $nb_retard_nj )
{
  // Pronote exporte un fichier pour les absences, et un autre pour les retards, il ne faut donc pas réinitialiser ce qui n'est pas importé.
  $update_absences = 'assiduite_absence=:assiduite_absence, assiduite_absence_nj=:assiduite_absence_nj';
  $update_retards  = 'assiduite_retard=:assiduite_retard, assiduite_retard_nj=:assiduite_retard_nj';
  $update = ($mode!='pronote') ? $update_absences.', '.$update_retards : ( ($nb_absence!==NULL) ? $update_absences : $update_retards ) ;
  $DB_SQL = 'INSERT INTO sacoche_officiel_assiduite ( periode_id,  user_id,  assiduite_absence,  assiduite_absence_nj,  assiduite_retard,  assiduite_retard_nj) ';
  $DB_SQL.= 'VALUES                                 (:periode_id, :user_id, :assiduite_absence, :assiduite_absence_nj, :assiduite_retard, :assiduite_retard_nj) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE '.$update;
  $DB_VAR = array(
    ':periode_id'           => $periode_id,
    ':user_id'              => $user_id,
    ':assiduite_absence'    => $nb_absence,
    ':assiduite_absence_nj' => $nb_absence_nj,
    ':assiduite_retard'     => $nb_retard,
    ':assiduite_retard_nj'  => $nb_retard_nj,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_ou_classe_id
 * @param int     $groupe_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @param string  $saisie_type
 * @return void
 */
public static function DB_supprimer_bilan_officiel_saisie( $officiel_type , $periode_id , $eleve_ou_classe_id , $groupe_id , $rubrique_id , $prof_id , $saisie_type )
{
  $groupe_id = 0; // TODO : à supprimer lorsqu'une telle gestion d'appréciation par groupe sera effectivement possible
  $DB_SQL = 'DELETE FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id=:eleve_ou_classe_id AND groupe_id=:groupe_id AND rubrique_id=:rubrique_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($rubrique_id>0) ? 'AND prof_id=:prof_id ' : '' ;
  $DB_VAR = array(
    ':officiel_type'      => $officiel_type,
    ':periode_id'         => $periode_id,
    ':eleve_ou_classe_id' => $eleve_ou_classe_id,
    ':groupe_id'          => $groupe_id,
    ':rubrique_id'        => $rubrique_id,
    ':prof_id'            => $prof_id,
    ':saisie_type'        => $saisie_type,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>