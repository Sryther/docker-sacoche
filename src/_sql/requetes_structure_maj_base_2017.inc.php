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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-12-31 => 2017-01-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-12-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_jointure_user_module]
    $reload_sacoche_jointure_user_module = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_user_module.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-11 => 2017-01-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // prise en compte d'un problème se produisant en cas de saisie manuelle d'éléments de programme travaillés avec plusieurs saut de lignes consécutifs intermédiaires.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , ",\"ELd41d8cd98f00b204e9800998ecf8427e\":\"\"" , "" ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":5," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":4," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":3," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":2," , "," ) WHERE saisie_objet="elements" ' );
    // correction d'un positionnement modifié sur certaines installations suite à un bug
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_colonne = "maitrise" WHERE livret_page_periodicite="cycle" AND livret_page_ref != "cycle1" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-18 => 2017-01-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_enscompl]
    $reload_sacoche_livret_enscompl = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_enscompl.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_enscompl_eleve]
    $reload_sacoche_livret_jointure_enscompl_eleve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_enscompl_eleve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // erreur dans la définition d'une colonne de la table [sacoche_livret_jointure_referentiel]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_referentiel CHANGE livret_rubrique_ou_matiere_id livret_rubrique_ou_matiere_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
    // retrait d'enregistrement d'infos de saisies de fin de cycle dans le livret scolaire au cas où (alors que l'accès n'était pas encore opérationnel)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_jointure_prof, sacoche_livret_saisie_memo_detail FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) WHERE livret_page_periodicite="cycle" ' );
    // retrait éventuel de scories d'utilisateurs supprimés dans les saisies du Livret Scolaire
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_user ON sacoche_livret_saisie.prof_id=sacoche_user.user_id WHERE sacoche_livret_saisie.prof_id!=0 AND sacoche_user.user_id IS NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie_jointure_prof LEFT JOIN sacoche_user ON sacoche_livret_saisie_jointure_prof.prof_id=sacoche_user.user_id WHERE sacoche_user.user_id IS NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_user ON sacoche_livret_saisie.cible_id=sacoche_user.user_id WHERE cible_nature="eleve" AND sacoche_user.user_id IS NULL' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_officiel_livret_positionner_socle" , "DIR,ENS")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-22 => 2017-02-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-02-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif légère format exports LSU archivés
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , "\"bilan\":{" , "\"bilan\":{\"type\":\"periode\"," ) ' );
    // nettoyage incomplet d'une révision précédente
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , ",\"ELd41d8cd98f00b204e9800998ecf8427e\"," , "," ) ' );
    // modif colonne de la table [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE jointure_date_verrou jointure_date_verrou DATETIME NULL DEFAULT NULL ' );
    }
    // recharger [sacoche_livret_rubrique] (modifs de codes et d'indices de positionnement ou d'éléments travaillés)
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // champ trouvé à "0000-00-00" au lieu de NULL sur une base
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_naissance_date = NULL WHERE user_naissance_date < "1000-01-01" ' ); // Le test ="0000-00-00" plante sur une config MySQL stricte.
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-02-10 => 2017-03-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-02-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à [sacoche_referentiel]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_mode_livret ENUM("domaine","theme","item") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "domaine" AFTER referentiel_mode_synthese' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_mode_livret = "theme" WHERE referentiel_mode_synthese = "theme" ' );
    // ajout clefs à [sacoche_livret_saisie]
    if(empty($reload_sacoche_livret_saisie))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie ADD INDEX rubrique (rubrique_type, rubrique_id) ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie ADD INDEX cible (cible_nature, cible_id) ' );
    }
    // ajout de paramètres
    $droit_gerer_referentiel = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_gerer_referentiel"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_mode_synthese"    , "'.$droit_gerer_referentiel.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_elements"  , "'.$droit_gerer_referentiel.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_epi"       , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_ap"        , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_parcours"  , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_modaccomp" , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_enscompl"  , "")' );
    // ceux-ci ont été oubliés dans le remplissage de la table lors de maj précédentes !
    $droit_socle_proposition_positionnement = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_proposition_positionnement"' );
    if(is_null($droit_socle_proposition_positionnement))
    {
      $droit_socle_pourcentage_acquis = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_pourcentage_acquis"' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_proposition_positionnement" , "'.$droit_socle_pourcentage_acquis.'")' );
    }
    $droit_voir_score_maitrise = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_voir_score_maitrise"' );
    if(is_null($droit_voir_score_maitrise))
    {
      $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_voir_score_bilan" ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_score_maitrise" , "'.$droit.'")' );
    }
    $droit_socle_prevision_points_brevet = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_prevision_points_brevet"' );
    if(is_null($droit_socle_prevision_points_brevet))
    {
      $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_socle_proposition_positionnement" ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_prevision_points_brevet" , "'.$droit.'")' );
    }
    $droit_officiel_livret_positionner_socle = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_officiel_livret_positionner_socle"' );
    if(is_null($droit_officiel_livret_positionner_socle))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_officiel_livret_positionner_socle" , "DIR,ENS")' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-01 => 2017-03-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // STSWeb 11.1.0 de janvier 2017 : passage du code groupe à 20 caractères + ajout d'un nouveau type de personnel "dir" et modification du format des nom d'usage et patronymique et du prénom
    // SIÈCLE BEE 17.2.1.0 du 15/05/2017 : agrandissement des lignes d'adresse des élèves et des responsables de 32 caractères à 38 caractères. -> RAS car déjà à 50 dans SACoche
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe CHANGE groupe_ref groupe_ref VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Passage de 8 à 20 caractères pour les groupes dans SIÈCLE BEE (mais pas pour les classes)." ' );
    // correction d'un positionnement modifié sur certaines installations suite à un bug
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_colonne = "reussite" WHERE livret_page_ref = "cycle1" ' );
    // ajout d'une ligne à [sacoche_siecle_import]
    if(empty($reload_sacoche_siecle_import))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_siecle_import VALUES ("Onde" , NULL, NULL, "") ' );
    }
    // ajout de colonne oublié dans la définition de la table lors d'une maj précédente !
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_referentiel LIKE "referentiel_mode_livret" ');
    if(empty($DB_TAB))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_mode_livret ENUM("domaine","theme","item") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "domaine" AFTER referentiel_mode_synthese' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-06 => 2017-03-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_element_cycle]
    $reload_sacoche_livret_element_cycle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_cycle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_domaine]
    $reload_sacoche_livret_element_domaine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_domaine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_niveau]
    $reload_sacoche_livret_element_niveau = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_niveau.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_theme]
    $reload_sacoche_livret_element_theme = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_theme.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_item]
    $reload_sacoche_livret_element_item = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_item.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout de paramètres
    $officiel_bulletin_appreciation_rubrique_longueur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_bulletin_appreciation_rubrique_longueur"' );
    $officiel_bulletin_appreciation_rubrique_longueur = min( max( $officiel_bulletin_appreciation_rubrique_longueur , 300 ) , 600 );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_appreciation_rubrique_longueur" , "'.$officiel_bulletin_appreciation_rubrique_longueur.'")' );
    $officiel_bulletin_appreciation_generale_longueur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_bulletin_appreciation_generale_longueur"' );
    $officiel_bulletin_appreciation_generale_longueur = max( $officiel_bulletin_appreciation_generale_longueur , 300 );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_appreciation_generale_longueur" , "'.$officiel_bulletin_appreciation_generale_longueur.'")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-15 => 2017-03-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à sacoche_officiel_saisie en espérant arriver un jour à l'exploiter...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie ADD groupe_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 COMMENT "pour une appréciation sur un groupe, précise le groupe" AFTER eleve_ou_classe_id , ADD INDEX ( groupe_id ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie DROP PRIMARY KEY, ADD PRIMARY KEY (eleve_ou_classe_id, groupe_id, officiel_type, periode_id, rubrique_id, prof_id, saisie_type)  ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-23 => 2017-03-30
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-30';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_jointure_parcours_prof]
    $reload_sacoche_livret_jointure_parcours_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_parcours_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // suppression d'un champ de [sacoche_livret_parcours] + déplacement du nom du prof de [sacoche_livret_parcours] vers [sacoche_livret_jointure_parcours_prof]
    if(empty($reload_sacoche_livret_parcours))
    {
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT livret_parcours_id, prof_id FROM sacoche_livret_parcours');
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_livret_jointure_parcours_prof VALUES ( '.$DB_ROW['livret_parcours_id'].' , '.$DB_ROW['prof_id'].' )' );
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours DROP INDEX prof_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours DROP prof_id ' );
    }
    if(empty($reload_sacoche_matiere_famille))
    {
      // Ajout de familles de matières
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_matiere_famille VALUES ( 96, 4, "Spécialités de baccalauréat professionnel (suite)") ');
      // réordonner la table sacoche_matiere_famille (ligne à déplacer vers la dernière MAJ lors d'ajouts dans sacoche_matiere_famille)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere_famille ORDER BY matiere_famille_id' );
    }
    if(empty($reload_sacoche_matiere))
    {
      // Matières renommées
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Indonésien-malais" WHERE matiere_id = 9342 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Peul"              WHERE matiere_id = 9344 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BPAMS", matiere_nom = "Artisanat et métiers d\'art - option mét. enseigne signalétique" WHERE matiere_id = 9713 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BPTDM", matiere_nom = "Traitements des matériaux"                                       WHERE matiere_id = 9784 ' );
      // Ajout de matières
      $insert = '
        ( 237, 0, 0, 0,   2, 0, 255, "023700", "LCUCO", "Langue, culture et communication"),
        ( 743, 0, 0, 0,   7, 0, 255, "074300", "TECNO", "Technologies"),
        (3473, 0, 0, 0,  34, 0, 255, "347300", "VDECO", "Vente et développement commercial"),
        (4167, 0, 0, 0,  41, 0, 255, "416700", "RCLSI", "Relation client sinistres"),
        (4212, 0, 0, 0,  42, 0, 255, "421200", "CUPRA", "Culture professionnelle appliquée"),
        (4213, 0, 0, 0,  42, 0, 255, "421300", "GESIN", "Gestion des sinistres"),
        (4704, 0, 0, 0,  47, 0, 255, "470400", "PAREX", "Parcours d\'excellence"),
        (9601, 0, 0, 0,  96, 0, 255,       "", "BPAAV", "Aéronautique - option avionique"),
        (9602, 0, 0, 0,  96, 0, 255,       "", "BPAST", "Aéronautique - option structure"),
        (9603, 0, 0, 0,  96, 0, 255,       "", "BPASY", "Aéronautique - option systèmes"),
        (9604, 0, 0, 0,  96, 0, 255,       "", "BPERA", "Étude et réalisation d\'agencement"),
        (9605, 0, 0, 0,  96, 0, 255,       "", "BPMCS", "Métiers du cuir option sellerie garnissage"),
        (9606, 0, 0, 0,  96, 0, 255,       "", "BPMAP", "Métiers et arts de la pierre"),
        (9607, 0, 0, 0,  96, 0, 255,       "", "BPAMV", "Artisanat et métiers d\'art - option verrerie scient. et techn."),
        (9608, 0, 0, 0,  96, 0, 255,       "", "BPEEC", "Métiers de l\'électricité et de ses environnements connectés"),
        (9609, 0, 0, 0,  96, 0, 255,       "", "BPSNA", "Systèmes numériques A - Sûreté et sécu. infra., hab. et tert."),
        (9610, 0, 0, 0,  96, 0, 255,       "", "BPSNB", "Systèmes numériques B - Audiovisuels, réseau et équip. domest."),
        (9611, 0, 0, 0,  96, 0, 255,       "", "BPSNC", "Systèmes numériques C - Réseaux inform. et syst. communicants"),
        (9612, 0, 0, 0,  96, 0, 255,       "", "BPTAO", "Technicien en appareillage orthopédique") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
      // Déplacement de l'EIST
      $id_avant = 600;
      $id_apres = 4802;
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_id = '.$id_apres.', matiere_usuelle = 1, matiere_famille_id = 48, matiere_code = "480200" WHERE matiere_id = '.$id_avant.' ' );
      DB_STRUCTURE_MATIERE::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
      SACocheLog::ajouter('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-30 => 2017-04-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-30')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // oubli de modif de table suite à la maj 2016-09-23
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_calcul_methode="frequencemax" WHERE referentiel_calcul_methode=""' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_methode referentiel_calcul_methode ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3","frequencemin","frequencemax") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores, ou de la plus fréquente. Valeur surclassant la configuration par défaut." ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-04-03 => 2017-04-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-04-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    if(empty($reload_sacoche_livret_export))
    {
      // Modification de clef après retrait de doublons qui pourraient poser pb
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT user_id,livret_page_periodicite,jointure_periode, COUNT(*) AS nombre FROM sacoche_livret_export GROUP BY user_id,livret_page_periodicite,jointure_periode HAVING nombre>1');
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_export WHERE user_id='.$DB_ROW['user_id'].' AND livret_page_periodicite="'.$DB_ROW['livret_page_periodicite'].'" AND jointure_periode="'.$DB_ROW['jointure_periode'].'" ' );
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export DROP INDEX export_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export ADD UNIQUE export_id ( user_id , livret_page_periodicite , jointure_periode )' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-04-18 => 2017-04-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-04-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout de familles de niveaux
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_niveau_famille VALUES (900, 3,  7, "LMD (Diplôme d\'enseignement supérieur)") ');
    }
    // Ajout de niveaux
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900001, 0, 0, 900, 900, "L1", "", "Licence, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900002, 0, 0, 900, 900, "L2", "", "Licence, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900003, 0, 0, 900, 900, "L3", "", "Licence, 3ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900011, 0, 0, 900, 900, "M1", "", "Master, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900012, 0, 0, 900, 900, "M2", "", "Master, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900021, 0, 0, 900, 900, "D1", "", "Doctorat, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900022, 0, 0, 900, 900, "D2", "", "Doctorat, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900023, 0, 0, 900, 900, "D3", "", "Doctorat, 3ème année") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-04-29 => 2017-05-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-04-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-05-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de colonne à [sacoche_demande]
    if(empty($reload_sacoche_demande))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande ADD periode_debut_date DATE DEFAULT NULL COMMENT "Date de début de la période considérée pour calculer le score." AFTER prof_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-05-13 => 2017-05-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-05-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-05-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // inversion ordre lignes dans maj 2017-04-03
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_methode referentiel_calcul_methode ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3","frequencemin","frequencemax") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores, ou de la plus fréquente. Valeur surclassant la configuration par défaut." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_calcul_methode="frequencemax" WHERE referentiel_calcul_methode=""' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-05-15 => 2017-05-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-05-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-05-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // retrait de la page "Brevet"
    if(empty($reload_sacoche_livret_page))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_page WHERE livret_page_ref="brevet" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_page CHANGE livret_page_periodicite livret_page_periodicite ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode" ' );
    }
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_groupe WHERE livret_page_periodicite="college" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE livret_page_periodicite livret_page_periodicite ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode" ' );
    }
    if(empty($reload_sacoche_livret_export))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export CHANGE livret_page_periodicite livret_page_periodicite ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode" ' );
    }
    if(empty($reload_sacoche_livret_saisie))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie CHANGE livret_page_periodicite livret_page_periodicite ENUM("periode","cycle") COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-05-18 => 2017-05-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-05-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-05-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_parametre_profil]
    $reload_sacoche_parametre_profil = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_parametre_profil.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout de champs à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_param_menu TEXT COLLATE utf8_unicode_ci COMMENT "Ce qui est masqué (et non ce qui est affiché)." AFTER user_param_accueil' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_param_favori TINYTEXT COLLATE utf8_unicode_ci AFTER user_param_menu' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_menu = NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_favori = NULL' );
    // Modification de la façon dont on gère user_param_accueil
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_param_accueil user_param_accueil VARCHAR(127) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Ce qui est masqué (et non ce qui est affiché)." ' );
    $tab_liste = array('user','messages','previsions','resultats','faiblesses','reussites','demandes','saisies','officiel','socle','help','ecolo');
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT user_id, user_param_accueil FROM sacoche_user');
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_actif = explode(',',$DB_ROW['user_param_accueil']);
      $tab_base_inactif = array_diff($tab_liste,$tab_base_actif);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_accueil = "'.implode(',',$tab_base_inactif).'" WHERE user_id = '.$DB_ROW['user_id'] );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-05-27 => 2017-06-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-05-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-06-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif colonne de la table [sacoche_livret_export]
    if(empty($reload_sacoche_livret_export))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export CHANGE jointure_periode jointure_periode ENUM("","T1","T2","T3","S1","S2","B1","B2","B3","B4") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
    }
    // modif colonne de la table [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE jointure_periode jointure_periode ENUM("","T1","T2","T3","S1","S2","B1","B2","B3","B4") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
    }
    // modif colonne de la table [sacoche_livret_saisie]
    if(empty($reload_sacoche_livret_saisie))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie CHANGE jointure_periode jointure_periode ENUM("","T1","T2","T3","S1","S2","B1","B2","B3","B4") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
    }
    // modif colonne de la table [sacoche_periode]
    if(empty($reload_sacoche_periode))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_livret periode_livret ENUM("","T1","T2","T3","S1","S2","B1","B2","B3","B4") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire." ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-06-08 => 2017-07-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-06-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-07-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='lea_hautenormandie')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="nero.l-educdenormandie.fr" WHERE parametre_nom="cas_serveur_host" ' );
    }
    // Ajout de familles de niveaux
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_niveau_famille VALUES (  4, 2,  4, "Niveaux adaptés (pour élèves à besoins particuliers)") ');
      // réordonner la table sacoche_niveau (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_niveau)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau_famille ORDER BY niveau_famille_id' );
    }
    // Ajout de niveaux
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 401, 0, 0, 4,   0, "TPSADA", "", "TPS adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 402, 0, 0, 4,   1,  "PSADA", "", "PS adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 403, 0, 0, 4,   2,  "MSADA", "", "MS adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 404, 0, 0, 4,   3,  "GSADA", "", "GS adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 405, 0, 1, 4,   5,  "P1ADA", "", "Cycle 1 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 406, 0, 0, 4,  11,  "CPADA", "", "CP adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 407, 0, 0, 4,  21, "CE1ADA", "", "CE1 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 408, 0, 0, 4,  22, "CE2ADA", "", "CE2 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 409, 0, 1, 4,  30,  "P2ADA", "", "Cycle 2 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 410, 0, 0, 4,  31, "CM1ADA", "", "CM1 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 411, 0, 0, 4,  32, "CM2ADA", "", "CM2 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 412, 0, 1, 4,  90,  "P3ADA", "", "Cycle 3 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 413, 0, 0, 4, 100,   "6ADA", "", "Sixième adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 414, 0, 0, 4, 101,   "5ADA", "", "Cinquième adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 415, 0, 0, 4, 102,   "4ADA", "", "Quatrième adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 416, 0, 0, 4, 103,   "3ADA", "", "Troisième adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 417, 0, 1, 4, 150,  "P4ADA", "", "Cycle 4 adapté") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 418, 0, 0, 4, 200,   "2ADA", "", "Seconde adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 419, 0, 0, 4, 201,   "1ADA", "", "Première adaptée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 420, 0, 0, 4, 202,   "TADA", "", "Terminale adaptée") ' );
      // réordonner la table sacoche_niveau (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_niveau)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau ORDER BY niveau_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-07-05 => 2017-08-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-07-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-08-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif colonne de la table [sacoche_livret_export]
    if(empty($reload_sacoche_livret_export))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export CHANGE jointure_periode jointure_periode VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET jointure_periode = REPLACE (jointure_periode, "T", "3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET jointure_periode = REPLACE (jointure_periode, "S", "2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET jointure_periode = REPLACE (jointure_periode, "B", "4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET jointure_periode = "0" WHERE jointure_periode="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export CHANGE jointure_periode jointure_periode TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET jointure_periode = NULL WHERE jointure_periode=0 ' );
    }
    // modif colonne de la table [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe DROP PRIMARY KEY, ADD UNIQUE jointure_id (groupe_id, livret_page_ref, livret_page_periodicite, jointure_periode) ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE jointure_periode jointure_periode VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_periode = REPLACE (jointure_periode, "T", "3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_periode = REPLACE (jointure_periode, "S", "2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_periode = REPLACE (jointure_periode, "B", "4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_periode = "0" WHERE jointure_periode="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE jointure_periode jointure_periode TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_groupe SET jointure_periode = NULL WHERE jointure_periode=0 ' );
    }
    // modif colonne de la table [sacoche_livret_saisie]
    if(empty($reload_sacoche_livret_saisie))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie CHANGE jointure_periode jointure_periode VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET jointure_periode = REPLACE (jointure_periode, "T", "3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET jointure_periode = REPLACE (jointure_periode, "S", "2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET jointure_periode = REPLACE (jointure_periode, "B", "4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET jointure_periode = "0" WHERE jointure_periode="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie CHANGE jointure_periode jointure_periode TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET jointure_periode = NULL WHERE jointure_periode=0 ' );
    }
    // modif colonne de la table [sacoche_periode]
    if(empty($reload_sacoche_periode))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_livret periode_livret VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "T", "3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "S", "2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "B", "4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = "0" WHERE periode_livret="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_livret periode_livret TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "Période officielle utilisable pour le livret scolaire." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = NULL WHERE periode_livret=0 ' );
    }
    // suppression d'un paramètre pour le déplacer vers sacoche_groupe
    $etablissement_chef_id = (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="etablissement_chef_id"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="etablissement_chef_id"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD groupe_chef_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 AFTER fiche_brevet' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_groupe SET groupe_chef_id = '.$etablissement_chef_id.' WHERE groupe_type="classe" ' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='itslearning_corse')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.itslearning.com" WHERE parametre_nom="cas_serveur_host" ' );
    }
    if($connexion_nom=='logica_lilie')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas" WHERE parametre_nom="cas_serveur_root" ' );
    }
    if($connexion_nom=='parisclassenumerique')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas" WHERE parametre_nom="cas_serveur_root" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-08-09 => 2017-08-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-08-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-08-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correction d'une requête précédente initialement trop large
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_groupe SET groupe_chef_id = 0 WHERE groupe_type!="classe" ' );
    // suppression de tables et d'entrées en lien avec l'ancien socle commun et l'ancien DNB
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_brevet_epreuve ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_brevet_fichier ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_brevet_saisie ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_brevet_serie ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_user_entree ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_user_pilier ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_socle_palier ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_socle_pilier ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_socle_section ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_socle_entree ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_officiel_fichier ' );
    // L'index suivant n'existait que sur des installations récentes...
    $Row_index = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SHOW INDEX FROM sacoche_user WHERE Key_name="eleve_brevet_serie" ' );
    if(!empty($Row_index))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP INDEX eleve_brevet_serie ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP eleve_brevet_serie ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP eleve_langue ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item DROP INDEX entree_id ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item DROP entree_id ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe DROP fiche_brevet ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode DROP officiel_palier1 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode DROP officiel_palier2 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode DROP officiel_palier3 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("droit_affecter_langue","droit_validation_entree","droit_validation_pilier","droit_annulation_pilier","droit_socle_pourcentage_acquis","droit_socle_etat_validation")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("droit_officiel_socle_modifier_statut","droit_officiel_socle_corriger_appreciation","droit_officiel_socle_appreciation_generale","droit_officiel_socle_impression_pdf","droit_officiel_socle_voir_archive")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("droit_fiche_brevet_modifier_statut","droit_fiche_brevet_corriger_appreciation","droit_fiche_brevet_appreciation_generale","droit_fiche_brevet_impression_pdf","droit_fiche_brevet_voir_archive")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("officiel_socle_etat_validation","officiel_socle_pourcentage_acquis","officiel_socle_only_presence","officiel_socle_prof_principal")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("officiel_socle_appreciation_generale_longueur","officiel_socle_appreciation_generale_report","officiel_socle_appreciation_generale_modele")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("officiel_socle_appreciation_rubrique_longueur","officiel_socle_appreciation_rubrique_report","officiel_socle_appreciation_rubrique_modele")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN("officiel_socle_assiduite","officiel_socle_couleur","officiel_socle_fond","officiel_socle_legende","officiel_socle_ligne_supplementaire")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom = "liste_paliers_actifs" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_abonnement WHERE abonnement_ref = "fiche_brevet_statut" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_notification WHERE abonnement_ref = "fiche_brevet_statut" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_jointure_user_abonnement WHERE abonnement_ref = "fiche_brevet_statut" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-08-16 => 2017-08-25
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-08-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-08-25';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // recharger [sacoche_livret_rubrique] (passage à une rubrique unique pour la LV aux cycles 2 et 3)
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // Adaptations aux nouveaux indices de [sacoche_livret_rubrique]
    $tab_modifs = array(
      'c2_domaine' => array(
         122 => 121,
         123 => 121,
         124 => 121,
      ),
      'c3_domaine' => array(
        202 => 201,
        203 => 201,
        204 => 201,
        205 => 201,
        206 => 201,
      ),
    );
    foreach( $tab_modifs as $rubrique_type => $tab_ids )
    {
      foreach( $tab_ids as $id_old => $id_new )
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id='.$id_new.' WHERE livret_rubrique_ou_matiere_id='.$id_old.' AND livret_rubrique_type="'.$rubrique_type.'" ' );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-08-25 => 2017-09-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-08-25')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-09-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='parisclassenumerique')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="ent.parisclassenumerique.fr" WHERE parametre_nom="cas_serveur_host" ' );
    }
    // Pour une raison que j'ignore la colonne [groupe_chef_id] de la table [sacoche_groupe] n'a pas été créée sur toutes les installations...
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_groupe LIKE "groupe_chef_id" ');
    if(empty($DB_TAB))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD groupe_chef_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 AFTER niveau_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-09-05 => 2017-09-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-09-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-09-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    if(empty($reload_sacoche_matiere))
    {
      // Matières visiblement oubliées sur les anciennes installations
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (238, 0, 0, 0, 2, 0, 255, "023800", "FRLET", "Français langue étrangère") ON DUPLICATE KEY UPDATE matiere_id=matiere_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (239, 0, 0, 0, 2, 0, 255, "023900", "FRLSE", "Français langue seconde") ON DUPLICATE KEY UPDATE matiere_id=matiere_id ' );
    }
    // modification sacoche_parametre (nom connecteur CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='parisclassenumerique')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="openent_pcn" WHERE parametre_nom="connexion_nom" ' );
    }
    if($connexion_nom=='logica_ent77')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="openent_ent77" WHERE parametre_nom="connexion_nom" ' );
    }
    if($connexion_nom=='logica_lilie')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="openent_monlycee" WHERE parametre_nom="connexion_nom" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-09-19 => 2017-10-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-09-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-10-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_niveau (ordre des SEGPA)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre=100 WHERE niveau_id=164001 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre=101 WHERE niveau_id=165001 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre=102 WHERE niveau_id=166001 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre=103 WHERE niveau_id=167001 ' );
    // EPI étendus au niveau 6e (rentrée 2017)
    if(empty($reload_sacoche_livret_page))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_epi = 1 WHERE livret_page_ref = "6e" ' );
    }
    // Thèmes des EPI personnalisables (rentrée 2017)
    if(empty($reload_sacoche_livret_epi_theme))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_epi_theme ADD livret_epi_theme_origine TINYINT(1) UNSIGNED NOT NULL DEFAULT 2 COMMENT "1 pour les thématiques nationales, 2 pour les thématiques personnalisées" AFTER livret_epi_theme_code ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_epi_theme SET livret_epi_theme_origine = 1 ' );
    }
    // Correction d'un possible état de partage d'un incohérent en cas de déplacement d'un référentiel d'une matière partagée vers une matière spécifique.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_etat="hs" WHERE matiere_id>'.ID_MATIERE_PARTAGEE_MAX );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-10-01 => 2017-10-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-10-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-10-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // table sacoche_user, augmentation de la longueur de certains champs
    if(empty($reload_sacoche_user))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_nom     user_nom    VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Longueur max 100 dans Siècle..." ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_prenom  user_prenom VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Longueur max 100 dans Siècle..." ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_login   user_login  VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  COMMENT "Voir aussi sacoche_user_profil.user_profil_login_modele" ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_email   user_email  VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Longueur max 254 dans Siècle... ; @see https://mydnic.be/post/longueur-dun-varchar-pour-un-champ-email" ');
    }
    if(empty($reload_sacoche_user_profil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user_profil CHANGE user_profil_login_modele user_profil_login_modele VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "ppp.nnnnnnnn" ');
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-10-10 => 2017-10-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-10-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-10-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Correction d'un possible état de partage d'un incohérent d'un référentiel (partagé alors que sur un niveau spécifique... pas compris pourquoi...)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_etat="hs" WHERE niveau_id>'.ID_NIVEAU_PARTAGE_MAX );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-10-12 => 2017-11-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-10-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-11-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout du profil "personnel socio-éducatif"
    if(empty($reload_sacoche_user_profil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_user_profil VALUES ("PSE", 1, 1, 0, 0, "professeur" , "all" , "config" , "socioéducatif" , "socioéducatifs" , "personnel socio-éducatif" , "personnels socio-éducatifs" , "ppp.nnnnnnnn", 6, 0, 30) ');
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-11-16 => 2017-11-25
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-11-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-11-25';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_bulletin_envoi_mail_parent" , "0")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_envoi_mail_parent" , "0")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_releve_envoi_mail_parent" , "0")' );
    // modif champ sacoche_matiere
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere CHANGE matiere_nom matiere_nom VARCHAR(67) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    }
    // ajout de matières
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9951, 0, 0, 1,  99, 0, 255, "", "C1LAN", "C1 Mobiliser le langage dans toutes ses dimensions") ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9952, 0, 0, 1,  99, 0, 255, "", "C1AP" , "C1 Agir, s\'exprimer, comprendre à travers l\'activité physique") ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9953, 0, 0, 1,  99, 0, 255, "", "C1AA" , "C1 Agir, s\'exprimer, comprendre à travers les activités artistiques") ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9954, 0, 0, 1,  99, 0, 255, "", "C1OSP", "C1 Construire les premiers outils pour structurer sa pensée") ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9955, 0, 0, 1,  99, 0, 255, "", "C1EM" , "C1 Explorer le monde") ' );
    // réordonner la table sacoche_matiere (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_matiere)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ORDER BY matiere_id' );
    // renommer des matières
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="C23 Questionner le monde"      WHERE matiere_id=9941 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="C23 Sciences et technologie"   WHERE matiere_id=9942 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="C23 Enseignements artistiques" WHERE matiere_id=9943 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="C23 Langue vivante"            WHERE matiere_id=9944 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-11-25 => 2017-12-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-11-25')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-12-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (nom connecteur CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='openent_ent77')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="entlibre_ent77" WHERE parametre_nom="connexion_nom" ' );
    }
    // ajout de 2 entrées dans sacoche_parametre
    $officiel_releve_cases_nb = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_releve_cases_nb"' );
    $officiel_releve_cases_auto = ($officiel_releve_cases_nb) ? 1 : 0 ;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_releve_cases_auto" , "'.$officiel_releve_cases_auto.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_releve_cases_largeur" , "5")' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-12-01 => 2017-12-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-12-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-12-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de colonnes dans sacoche_groupe
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD groupe_configuration_livret   VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Valeur \'defaut\' imposée par défaut pour les classes." AFTER groupe_chef_id' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD groupe_configuration_bulletin VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Valeur \'defaut\' imposée par défaut pour les classes." AFTER groupe_chef_id' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD groupe_configuration_releve   VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Valeur \'defaut\' imposée par défaut pour les classes." AFTER groupe_chef_id' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_groupe SET groupe_configuration_livret="defaut", groupe_configuration_bulletin="defaut", groupe_configuration_releve="defaut" WHERE groupe_type="classe" ' );
    // nouvelle table [sacoche_officiel_configuration]
    $reload_sacoche_officiel_configuration = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_configuration.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // Reporter les configurations présentes dans sacoche_parametre
    $DB_SQL = 'UPDATE sacoche_officiel_configuration ';
    $DB_SQL.= 'SET configuration_contenu=:configuration_contenu ';
    $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
    $tab_type = array( 'releve' , 'bulletin' , 'livret' );
    foreach($tab_type as $type)
    {
      $tab_config = array();
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_parametre WHERE parametre_nom LIKE "officiel_'.$type.'%" ');
      foreach($DB_TAB as $DB_ROW)
      {
        $param_nom = substr( $DB_ROW['parametre_nom'], 10+strlen($type) );
        $param_val = ctype_digit($DB_ROW['parametre_valeur']) ? (int) $DB_ROW['parametre_valeur']  : $DB_ROW['parametre_valeur'] ;
        $tab_config[$param_nom] = $param_val;
      }
      $DB_VAR = array(
        ':officiel_type'         => $type,
        ':configuration_ref'     => 'defaut',
        ':configuration_contenu' => json_encode($tab_config),
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom LIKE "officiel_'.$type.'%" ');
    }
    // On a trouvé une base sur laquelle cette modification de 2017-08-09 n'est pas passée, du coup on teste et on la relance si besoin
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_periode LIKE "periode_livret" ' );
    if(strpos($DB_ROW['Type'],'tinyint')===FALSE)
    {
      // modif colonne de la table [sacoche_periode]
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_livret periode_livret VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "T", "3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "S", "2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = REPLACE (periode_livret, "B", "4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = "0" WHERE periode_livret="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_livret periode_livret TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "Période officielle utilisable pour le livret scolaire." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_periode SET periode_livret = NULL WHERE periode_livret=0 ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-12-09 => 2017-12-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-12-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-12-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correction de données incorrectes dans sacoche_officiel_configuration
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_officiel_configuration SET configuration_contenu = REPLACE( configuration_contenu , "\\"appreciation_rubrique_report\\":\\"\\"" , "\\"appreciation_rubrique_report\\":0" ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_officiel_configuration SET configuration_contenu = REPLACE( configuration_contenu , "\\"appreciation_generale_report\\":\\"\\"" , "\\"appreciation_generale_report\\":0" ) ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-12-16 => 2017-12-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-12-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-12-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // on recommence le correctif précédent car l'origine est maintenant connue et du coup le problème a encore pu survenir entre temps
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_officiel_configuration SET configuration_contenu = REPLACE( configuration_contenu , "\\"appreciation_rubrique_report\\":\\"\\"" , "\\"appreciation_rubrique_report\\":0" ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_officiel_configuration SET configuration_contenu = REPLACE( configuration_contenu , "\\"appreciation_generale_report\\":\\"\\"" , "\\"appreciation_generale_report\\":0" ) ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-12-19 => 2017-12-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-12-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-12-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une ligne à [sacoche_siecle_import]
    if(empty($reload_sacoche_siecle_import))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_siecle_import VALUES ("Communs" , NULL, NULL, "") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// NE PAS OUBLIER de modifier aussi le nécessaire dans ./_sql/structure/ en fonction des évolutions !!!
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// ///////////////////////////////////////////////////////////////////////////////////////////////////////
// NE PAS OUBLIER de maj les 2 tables de la base sacoche_projet en cas modif de matières ou de niveaux !!!
// ///////////////////////////////////////////////////////////////////////////////////////////////////////

?>
