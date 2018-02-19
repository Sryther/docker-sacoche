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
// MAJ 2015-12-16 => 2016-01-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-12-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-01-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage du champ [domaine_ref] en [domaine_code] de la table [sacoche_referentiel_domaine]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine CHANGE domaine_ref domaine_code CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // ajout de champs [domaine_ref] [theme_ref] [item_ref] [item_abbr] pour pouvoir imposer une référence
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine ADD domaine_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER domaine_code' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_theme ADD theme_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER theme_ordre' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER item_ordre' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_abbr VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER item_nom' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_reference" , "1" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-01-11 => 2016-02-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-01-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-02-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Pour Notanet et les fiches brevet, remplacement de "Éducation civique" par "Enseignement moral et civique"
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,414,406,6926,421,6925" WHERE brevet_serie_ref="G" AND brevet_epreuve_code=122 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Histoire-géographie Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,421,6925,406,6926,414" WHERE brevet_serie_ref="P" AND brevet_epreuve_code=121 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Histoire-géographie Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,421,6925,406,6926,414" WHERE brevet_serie_ref="P-Agri" AND brevet_epreuve_code=121 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-02-01 => 2016-02-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-02-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-02-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification des champs DATE car en mode SQL strict des valeurs telles 0000-00-00 ne sont pas tolérées
    if(empty($reload_sacoche_brevet_fichier))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_brevet_fichier CHANGE fichier_date fichier_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    if(empty($reload_sacoche_demande))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande CHANGE demande_date demande_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode CHANGE jointure_date_debut jointure_date_debut DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode CHANGE jointure_date_fin   jointure_date_fin   DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_entree CHANGE validation_entree_date validation_entree_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    if(empty($reload_sacoche_jointure_user_pilier))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier CHANGE validation_pilier_date validation_pilier_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_date devoir_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE devoir_visible_date devoir_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message CHANGE message_debut_date message_debut_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE message_fin_date message_fin_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_notification CHANGE notification_date notification_date DATETIME DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier CHANGE fichier_date_generation fichier_date_generation DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_partage_date referentiel_partage_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    // Pour la table sacoche_saisie , un UPDATE quand il y a plus d'un million de lignes dépasse très largement le max_execution_time de PHP
    // Solution : on reporte via plusieurs appels ajax qui seront appelés depuis la page d'accueil du compte
    $nb_notes = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT COUNT(*) FROM sacoche_saisie ' );
    if($nb_notes<100000)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_date saisie_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE saisie_visible_date saisie_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    else
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'a" WHERE parametre_nom="version_base_maj_complementaire" AND parametre_valeur="" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-02-27 => 2016-03-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-02-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-03-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage du champ [item_abbr] en [item_abrev] de la table [sacoche_referentiel_item]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item CHANGE item_abbr item_abrev VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // nouvelle table [sacoche_socle_cycle]
    $reload_sacoche_socle_cycle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_cycle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_socle_domaine]
    $reload_sacoche_socle_domaine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_domaine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_socle_composante]
    $reload_sacoche_socle_composante = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_composante.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_jointure_referentiel_socle]
    $reload_sacoche_jointure_referentiel_socle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_referentiel_socle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-03-10 => 2016-03-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-03-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-03-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 composante du socle table [sacoche_socle_composante]
    if(empty($reload_sacoche_socle_composante))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_composante VALUES (44, 4, 4, NULL, "Connaissances à mobiliser"), (54, 5, 4, NULL, "Connaissances à mobiliser") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_composante ORDER BY socle_composante_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-03-22 => 2016-04-17
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-03-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-04-17';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // retrait de 2 paramètres
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN ( "officiel_archive_retrait_tampon_signature","officiel_archive_ajout_message_copie" )' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_chef_id" , "0" )' );
    // ajout de champs à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_lv1 TINYINT(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT "Langue vivante 1 pour le livret scolaire." AFTER eleve_langue' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_lv2 TINYINT(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT "Langue vivante 2 pour le livret scolaire." AFTER eleve_lv1' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_uai_origine CHAR(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Pour un envoi de documents officiels à l\'établissement d\'origine." AFTER eleve_lv2' );
    // ajout d'un champ à la table [sacoche_periode]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode ADD periode_lsun VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "T1 | T2 | T3 | S1 | S2 ; période officielle utilisable pour le livret scolaire." AFTER periode_nom' );
    // nouvelle table [sacoche_officiel_archive]
    $reload_sacoche_officiel_archive = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_archive.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_officiel_archive_image]
    $reload_sacoche_officiel_archive_image = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_archive_image.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_structure_origine]
    $reload_sacoche_structure_origine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_structure_origine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout d'un champ (matiere_code) prérempli à la table sacoche_matiere
    if(empty($reload_sacoche_matiere))
    {
      // récupération des informations sur les matières
      $DB_TAB_communes    = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id, matiere_nb_demandes, matiere_ordre FROM sacoche_matiere WHERE matiere_active=1 AND matiere_id<='.ID_MATIERE_PARTAGEE_MAX);
      $DB_TAB_specifiques = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_matiere WHERE matiere_id>'.ID_MATIERE_PARTAGEE_MAX);
      // rechargement de la table sacoche_matiere
      $reload_sacoche_matiere = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_matiere.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
      // on remet en place les matières partagées
      foreach($DB_TAB_communes as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_active=1, matiere_nb_demandes='.$DB_ROW['matiere_nb_demandes'].', matiere_ordre='.$DB_ROW['matiere_ordre'].' WHERE matiere_id='.$DB_ROW['matiere_id'] );
      }
      // on remet en place les matières spécifiques
      foreach($DB_TAB_specifiques as $DB_ROW)
      {
        $DB_SQL = 'INSERT INTO sacoche_matiere(matiere_id, matiere_active, matiere_usuelle, matiere_famille_id, matiere_nb_demandes, matiere_ordre, matiere_code, matiere_ref, matiere_nom) ';
        $DB_SQL.= 'VALUES(:matiere_id, :matiere_active, 0, 0, :matiere_nb_demandes, :matiere_ordre, 0, :matiere_ref, :matiere_nom) ';
        $DB_VAR = array(
          ':matiere_id'          => $DB_ROW['matiere_id'],
          ':matiere_active'      => $DB_ROW['matiere_active'],
          ':matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
          ':matiere_ordre'       => $DB_ROW['matiere_ordre'],
          ':matiere_ref'         => $DB_ROW['matiere_ref'],
          ':matiere_nom'         => $DB_ROW['matiere_nom'],
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-04-17 => 2016-04-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($version_base_structure_actuelle=='2016-04-17') || ($version_base_structure_actuelle=='2016-04-23') ) // Il a dû y avoir une erreur à un moment donné car des bases se sont retrouvées en version 2016-04-23.
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-04-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un champ et à jout d'une clef à la table [sacoche_periode]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_lsun periode_livret ENUM("","T1","T2","T3","S1","S2") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode ADD INDEX periode_livret (periode_livret) ' );
    // modification d'un champ de la table [sacoche_officiel_archive]
    if(empty($reload_sacoche_officiel_archive))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_archive CHANGE archive_type archive_type ENUM("sacoche","livret") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "sacoche" ' );
    }
    // modification d'un champ de la table [sacoche_socle_composante]
    if(empty($reload_sacoche_socle_composante))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_composante CHANGE socle_composante_ordre_lsun socle_composante_ordre_livret TINYINT(3) UNSIGNED NULL DEFAULT NULL ' );
    }
    // modification d'un champ de la table [sacoche_socle_domaine]
    if(empty($reload_sacoche_socle_domaine))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_domaine CHANGE socle_domaine_ordre_lsun socle_domaine_ordre_livret TINYINT(3) UNSIGNED NULL DEFAULT NULL ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-04-29 => 2016-05-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-04-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-05-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_ap]
    $reload_sacoche_livret_ap = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_ap.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_epi]
    $reload_sacoche_livret_epi = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_epi.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_epi_theme]
    $reload_sacoche_livret_epi_theme = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_epi_theme.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_epi_prof]
    $reload_sacoche_livret_jointure_epi_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_epi_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_modaccomp_eleve]
    $reload_sacoche_livret_jointure_modaccomp_eleve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_modaccomp_eleve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_modaccomp]
    $reload_sacoche_livret_modaccomp = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_modaccomp.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parcours]
    $reload_sacoche_livret_parcours = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parcours.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parcours_type]
    $reload_sacoche_livret_parcours_type = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parcours_type.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_page]
    $reload_sacoche_livret_page = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parametre_colonne] => supprimée peu après (modifiée et renommée)
    /*
    $reload_sacoche_livret_parametre_colonne = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parametre_colonne.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    */
    // nouvelle table [sacoche_livret_parametre_rubrique] => supprimée peu après (modifiée et renommée)
    /*
    $reload_sacoche_livret_parametre_rubrique = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parametre_rubrique.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    */
    // nouvelle table [sacoche_livret_jointure_groupe]
    $reload_sacoche_livret_jointure_groupe = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_groupe.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_referentiel]
    $reload_sacoche_livret_jointure_referentiel = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_referentiel.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-05-10 => 2016-06-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-05-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-06-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout de matières
    if(empty($reload_sacoche_matiere))
    {
      $insert = '
        ( 236, 0, 0,   2, 0, 255,  23600, "LGLIT", "Langue et littérature"),
        (3222, 0, 0,  32, 0, 255, 322200, "HANCY", "Hématologie - anatomocythopathologie"),
        (3737, 0, 0,  37, 0, 255, 373700, "ICN"  , "Informatique et création numérique"),
        (4702, 0, 0,  47, 0, 255, 470200, "MODAS", "Module d\'aides spécifiques"),
        (4703, 0, 0,  47, 0, 255, 470300, "ENSHL", "Enseignement non suivi hors langue vivante"),
        (9374, 0, 0,  93, 0, 255,  37400, "WAL", "Walissien-futunien"),
        (9380, 0, 0,  93, 0, 255,  38000, "LNS", "Langue non suivie"),
        (9381, 0, 0,  93, 0, 255,  38100, "LIT", "Lituanien"),
        (9382, 0, 0,  93, 0, 255,  38200, "EST", "Estonien") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
    }
    // Ajout / Modification de niveaux
    if(empty($reload_sacoche_niveau))
    {
      $insert = '
        ( 100027, 0, 0, 100, 100,    "6BC", "1001002711.", "Sixième bilangue de continuité"),
        ( 100028, 0, 0, 100, 100,   "6BCD", "1001002811.", "Sixième bilangue de continuité danse"),
        ( 100029, 0, 0, 100, 100,   "6BCM", "1001002911.", "Sixième bilangue de continuité musique"),
        ( 100030, 0, 0, 100, 100,   "6BCT", "1001003011.", "Sixième bilangue de continuité théâtre"),
        ( 247291, 0, 0, 247, 247, "2NDPRO", "2472230631.", "2ndPro artisanat et métiers d\'art facteur d\'orgues, 2nde commune"),
        ( 247292, 0, 0, 247, 247, "2NDPRO", "2472240431.", "2ndPro artisanat et métiers d\'art, 2nde commune"),
        ( 247293, 0, 0, 247, 247, "2NDPRO", "2472300631.", "2ndPro technicien d\'études du bâtiment, 2nde commune"),
        ( 247294, 0, 0, 247, 247, "2NDPRO", "2472320931.", "2ndPro interventions sur le patrimoine bâti, 2nde commune"),
        ( 247295, 0, 0, 247, 247, "1ERPRO", "2472430332.", "1erPro métiers du cuir : sellerie garnissage"),
        ( 247296, 0, 0, 247, 247, "TLEPRO", "2472430333.", "TlePro métiers du cuir : sellerie garnissage"),
        ( 247297, 0, 0, 247, 247, "2NDPRO", "2472430431.", "2ndPro métiers du cuir, 2nde commune"),
        ( 247298, 0, 0, 247, 247, "2NDPRO", "2472521731.", "2ndPro maintenance des matériels, 2nde commune"),
        ( 247299, 0, 0, 247, 247, "2NDPRO", "2472521831.", "2ndPro maintenance des véhicules, 2nde commune"),
        ( 247300, 0, 0, 247, 247, "2NDPRO", "2472530631.", "2ndPro aéronautique, 2nde commune"),
        ( 247301, 0, 0, 247, 247, "2NDPRO", "2472551031.", "2ndPro métiers de l\'électricité et de ses environnements connectés"),
        ( 247302, 0, 0, 247, 247, "1ERPRO", "2472551032.", "1erPro métiers de l\'électricité et de ses environnements connectés"),
        ( 247303, 0, 0, 247, 247, "TLEPRO", "2472551033.", "TlePro métiers de l\'électricité et de ses environnements connectés"),
        ( 247304, 0, 0, 247, 247, "1ERPRO", "2472551332.", "1erPro systèmes numériques option A sûreté sécurité infras. hab. tertiaire"),
        ( 247305, 0, 0, 247, 247, "TLEPRO", "2472551333.", "TlePro systèmes numériques option A sûreté sécurité infras. hab. tertiaire"),
        ( 247306, 0, 0, 247, 247, "1ERPRO", "2472551432.", "1erPro systèmes numériques option B audiovisuels réseau équip. domestiques"),
        ( 247307, 0, 0, 247, 247, "TLEPRO", "2472551433.", "TlePro systèmes numériques option B audiovisuels réseau équip. domestiques"),
        ( 247308, 0, 0, 247, 247, "1ERPRO", "2472551532.", "1erPro systèmes numériques option C réseaux inform. syst. communicants"),
        ( 247309, 0, 0, 247, 247, "TLEPRO", "2472551533.", "TlePro systèmes numériques option C réseaux inform. syst. communicants"),
        ( 247310, 0, 0, 247, 247, "2NDPRO", "2472551631.", "2ndPro systèmes numériques, 2nde commune"),
        ( 247311, 0, 0, 247, 247, "2NDPRO", "2473220931.", "2ndPro réalisation de produits imprimés et plurimédia, 2nde commune"),
        ( 247312, 0, 0, 247, 247, "2NDPRO", "2473300531.", "2ndPro accompagnement soins et services à la personne, 2nde commune"),
        ( 247313, 0, 0, 247, 247, "2NDPRO", "2472551031.", "2ndPro technicien en appareillage orthopédique"),
        ( 247314, 0, 0, 247, 247, "1ERPRO", "2472551032.", "1erPro technicien en appareillage orthopédique"),
        ( 247315, 0, 0, 247, 247, "TLEPRO", "2472551033.", "TlePro technicien en appareillage orthopédique"),
        ( 253053, 0, 0, 253, 253,     "MC", "2532500311.", "MC mécatronique navale (mc4)"),
        ( 253054, 0, 0, 253, 253,     "MC", "2532540611.", "MC technicien(ne) en soudage"),
        ( 271038, 0, 0, 271, 271, "1CAP2A", "2712123721.", "1CAP2a palefrenier soigneur"),
        ( 271039, 0, 0, 271, 271, "2CAP2A", "2712123722.", "2CAP2a palefrenier soigneur"),
        ( 276048, 0, 0, 276, 276, "2DPROA", "2762111231.", "2dProA agricole productions"),
        ( 293005, 0, 0, 290, 293,   "1PD5", "2933320411.", "1PD5-1 Accompagnant éducatif et social accompagnement de la vie à domicile"),
        ( 293006, 0, 0, 290, 293,   "1PD5", "2933320511.", "1PD5-1 Accompagnant éducatif et social Accomp. vie en structure collective"),
        ( 293007, 0, 0, 290, 293,   "1PD5", "2933320611.", "1PD5-1 Accompagnant éducatif et social éducation inclusive & vie ordinaire"),
        ( 310112, 0, 0, 310, 310,  "1BTS1", "3102200311.", "1BTS1 pilotage de procédés"),
        ( 310113, 0, 0, 310, 310,  "1BTS1", "3102220811.", "1BTS1 métiers de la chimie"),
        ( 310114, 0, 0, 310, 310,  "1BTS1", "3102231711.", "1BTS1 concept. des proces. de réal. de prod. option B produc. sérielle"),
        ( 310115, 0, 0, 310, 310,  "1BTS1", "3102231811.", "1BTS1 concept. des proces. de réal. de prod. option A produc. unitaire"),
        ( 310116, 0, 0, 310, 310,  "1BTS1", "3102250511.", "1BTS1 EuroPlastics et composites option CO conception outillage"),
        ( 310117, 0, 0, 310, 310,  "1BTS1", "3102250611.", "1BTS1 EuroPlastics et composites option POP pilotage optimisation product."),
        ( 310118, 0, 0, 310, 310,  "1BTS1", "3102521511.", "1BTS1 maintenance des véhicules option A voitures particulières"),
        ( 310119, 0, 0, 310, 310,  "1BTS1", "3102521611.", "1BTS1 maintenance des véhicules option B véhicules transport routier"),
        ( 310120, 0, 0, 310, 310,  "1BTS1", "3102521711.", "1BTS1 maintenance des véhicules option C motocycles"),
        ( 310121, 0, 0, 310, 310,  "1BTS1", "3102541211.", "1BTS1 forge"),
        ( 311221, 0, 0, 311, 311,  "1BTS2", "3112200321.", "1BTS2 pilotage de procédés"),
        ( 311222, 0, 0, 311, 311,  "2BTS2", "3112200322.", "2BTS2 pilotage de procédés"),
        ( 311223, 0, 0, 311, 311,  "1BTS2", "3112220821.", "1BTS2 métiers de la chimie"),
        ( 311224, 0, 0, 311, 311,  "2BTS2", "3112220822.", "2BTS2 métiers de la chimie"),
        ( 311225, 0, 0, 311, 311,  "1BTS2", "3112231621.", "1BTS2 conception des processus réalisation produits 1ère année commune"),
        ( 311226, 0, 0, 311, 311,  "2BTS2", "3112231722.", "2BTS2 concept. des proces. de réal. de prod. option B produc. sérielle"),
        ( 311227, 0, 0, 311, 311,  "2BTS2", "3112231822.", "2BTS2 concept. des proces. de réal. de prod. option A produc. unitaire"),
        ( 311228, 0, 0, 311, 311,  "1BTS2", "3112250521.", "1BTS2 EuroPlastics et composites option CO conception outillage"),
        ( 311229, 0, 0, 311, 311,  "2BTS2", "3112250522.", "2BTS2 EuroPlastics et composites option CO conception outillage"),
        ( 311230, 0, 0, 311, 311,  "1BTS2", "3112250621.", "1BTS2 EuroPlastics et composites option POP pilotage optimisation product."),
        ( 311231, 0, 0, 311, 311,  "2BTS2", "3112250622.", "2BTS2 EuroPlastics et composites option POP pilotage optimisation product."),
        ( 311232, 0, 0, 311, 311,  "1BTS2", "3112521521.", "1BTS2 maintenance des véhicules option A voitures particulières"),
        ( 311233, 0, 0, 311, 311,  "2BTS2", "3112521522.", "2BTS2 maintenance des véhicules option A voitures particulières"),
        ( 311234, 0, 0, 311, 311,  "1BTS2", "3112521621.", "1BTS2 maintenance des véhicules option B véhicules transport routier"),
        ( 311235, 0, 0, 311, 311,  "2BTS2", "3112521622.", "2BTS2 maintenance des véhicules option B véhicules transport routier"),
        ( 311236, 0, 0, 311, 311,  "1BTS2", "3112521721.", "1BTS2 maintenance des véhicules option C motocycles"),
        ( 311237, 0, 0, 311, 311,  "2BTS2", "3112521722.", "2BTS2 maintenance des véhicules option C motocycles"),
        ( 311238, 0, 0, 311, 311,  "1BTS2", "3112541221.", "1BTS2 forge"),
        ( 311239, 0, 0, 311, 311,  "2BTS2", "3112541222.", "2BTS2 forge") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES '.$insert );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom = REPLACE(niveau_nom,"1BTS2","2BTS2") WHERE niveau_ref="2BTS2" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2402213911.", niveau_nom="1CAP1 cuisine" WHERE niveau_id=240011' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412213921.", niveau_nom="1CAP2 cuisine" WHERE niveau_id=241022' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412213922.", niveau_nom="2CAP2 cuisine" WHERE niveau_id=241023' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2402522111.", niveau_nom="1CAP1 maintenance des matériels option A matériels agricoles" WHERE niveau_id=240121' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2402522211.", niveau_nom="1CAP1 maintenance des matériels option B matériels TP et manutention" WHERE niveau_id=240122' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2402522311.", niveau_nom="1CAP1 maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=240123' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522121.", niveau_nom="1CAP2 maintenance des matériels option A matériels agricoles" WHERE niveau_id=241239' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522122.", niveau_nom="2CAP2 maintenance des matériels option A matériels agricoles" WHERE niveau_id=241240' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522221.", niveau_nom="1CAP2 maintenance des matériels option B matériels TP et manutention" WHERE niveau_id=241241' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522222.", niveau_nom="2CAP2 maintenance des matériels option B matériels TP et manutention" WHERE niveau_id=241242' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522321.", niveau_nom="1CAP2 maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=241243' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2412522322.", niveau_nom="2CAP2 maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=241244' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472521131.", niveau_nom="2ndPro maintenance des matériels option A matériels agricoles" WHERE niveau_id=247155' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472521932.", niveau_nom="1erPro maintenance des matériels option A matériels agricoles" WHERE niveau_id=247156' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472521933.", niveau_nom="TlePro maintenance des matériels option A matériels agricoles" WHERE niveau_id=247157' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472521231.", niveau_nom="2ndPro maintenance des matériels option B TP et manutention" WHERE niveau_id=247158' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472522032.", niveau_nom="1erPro maintenance des matériels option B TP et manutention" WHERE niveau_id=247159' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472522033.", niveau_nom="TlePro maintenance des matériels option B TP et manutention" WHERE niveau_id=247160' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472521331.", niveau_nom="2ndPro maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=247161' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472522132.", niveau_nom="1erPro maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=247162' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472522133.", niveau_nom="TlePro maintenance des matériels option C matériels d\'espaces verts" WHERE niveau_id=247163' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551332.", niveau_nom="1erPro systèmes numériques option A sûreté sécurité infras. hab. tertiaire" WHERE niveau_id=247304' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551333.", niveau_nom="TlePro systèmes numériques option A sûreté sécurité infras. hab. tertiaire" WHERE niveau_id=247305' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551432.", niveau_nom="1erPro systèmes numériques option B audiovisuels réseau équip. domestiques" WHERE niveau_id=247306' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551433.", niveau_nom="TlePro systèmes numériques option B audiovisuels réseau équip. domestiques" WHERE niveau_id=247307' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551532.", niveau_nom="1erPro systèmes numériques option C réseaux inform. syst. communicants" WHERE niveau_id=247308' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2472551533.", niveau_nom="TlePro systèmes numériques option C réseaux inform. syst. communicants" WHERE niveau_id=247309' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2542211021.", niveau_nom="1BP2 boucher" WHERE niveau_id=254010' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="2542211022.", niveau_nom="2BP2 boucher" WHERE niveau_id=254011' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102000911.", niveau_nom="1BTS1 conception des produits industriels" WHERE niveau_id=310001' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102011311.", niveau_nom="1BTS1 contrôle industriel et régulation automatique" WHERE niveau_id=310004' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102231411.", niveau_nom="1BTS1 fonderie" WHERE niveau_id=310016' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102521411.", niveau_nom="1BTS1 moteurs à combustion interne" WHERE niveau_id=310050' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102310811.", niveau_nom="1BTS1 métiers du géomètre-topographe et de la modélisation numérique" WHERE niveau_id=310030' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102320511.", niveau_nom="1BTS1 enveloppe des bâtiments : conception et réalisation" WHERE niveau_id=310032' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3102330511.", niveau_nom="1BTS1 étude et réalisation d\'agencement" WHERE niveau_id=310033' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112000921.", niveau_nom="1BTS2 conception des produits industriel" WHERE niveau_id=311003' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112000922.", niveau_nom="2BTS2 conception des produits industriel" WHERE niveau_id=311004' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112011321.", niveau_nom="1BTS2 contrôle industriel et régulation automatique" WHERE niveau_id=311009' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112011322.", niveau_nom="2BTS2 contrôle industriel et régulation automatique" WHERE niveau_id=311010' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112310821.", niveau_nom="1BTS2 métiers du géomètre-topographe et de la modélisation numérique" WHERE niveau_id=311064' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112310822.", niveau_nom="2BTS2 métiers du géomètre-topographe et de la modélisation numérique" WHERE niveau_id=311065' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112320521.", niveau_nom="1BTS2 enveloppe des bâtiments : conception et réalisation" WHERE niveau_id=311068' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112320522.", niveau_nom="2BTS2 enveloppe des bâtiments : conception et réalisation" WHERE niveau_id=311069' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112330521.", niveau_nom="1BTS2 étude et réalisation d\'agencement" WHERE niveau_id=311070' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112330522.", niveau_nom="2BTS2 étude et réalisation d\'agencement" WHERE niveau_id=311071' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112521421.", niveau_nom="1BTS2 moteurs à combustion interne" WHERE niveau_id=311104' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET code_mef="3112521422.", niveau_nom="2BTS2 moteurs à combustion interne" WHERE niveau_id=311105' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-06-07 => 2016-06-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-06-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-06-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout du champ [item_comm]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_comm TEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Commentaire associé à l\'item, par exemple des échelles descriptives." AFTER item_lien' );
    // Ajout de matières
    if(empty($reload_sacoche_matiere))
    {
      $insert = '
        (9789, 0, 0,  97, 0, 255, 0, "BPAOO", "Artisanat et métiers d\'art - facteur d\'orgues option organier"),
        (9790, 0, 0,  97, 0, 255, 0, "BPAOT", "Artisanat et métiers d\'art - facteur d\'orgues option tuyautier"),
        (9791, 0, 0,  97, 0, 255, 0, "BPMVA", "Maintenance de véhicules - option A voitures particulières"),
        (9792, 0, 0,  97, 0, 255, 0, "BPMVB", "Maintenance de véhicules - option B véh. de transport routier"),
        (9793, 0, 0,  97, 0, 255, 0, "BPMVC", "Maintenance de véhicules - option C motocycles"),
        (9794, 0, 0,  97, 0, 255, 0, "BPMAV", "Menuiserie aluminium verre"),
        (9795, 0, 0,  97, 0, 255, 0, "BPMS" , "Métiers de la sécurité"),
        (9796, 0, 0,  97, 0, 255, 0, "BPRPG", "Réal. de prod. imprimés et plurimédia option A prod. graphiques"),
        (9797, 0, 0,  97, 0, 255, 0, "BPRPI", "Réal. de prod. imprimés et plurimédia option B prod. imprimées"),
        (9798, 0, 0,  97, 0, 255, 0, "BPTIN", "Techniques d\'interventions sur installations nucléaires") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-06-14 => 2016-06-24
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-06-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-06-24';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression de la table [sacoche_livret_parametre_colonne]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_livret_parametre_colonne ' );
    // suppression de la table [sacoche_livret_parametre_rubrique]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_livret_parametre_rubrique ' );
    // nouvelle table [sacoche_livret_colonne]
    $reload_sacoche_livret_colonne = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_colonne.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_seuil]
    $reload_sacoche_livret_seuil = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_seuil.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_rubrique]
    $reload_sacoche_livret_rubrique = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_ap_prof]
    $reload_sacoche_livret_jointure_ap_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_ap_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // suppression de champs de la table [sacoche_livret_ap]
    if(empty($reload_sacoche_livret_ap))
    {
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT livret_ap_id, matiere_id, prof_id FROM sacoche_livret_ap');
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_livret_jointure_ap_prof VALUES ( '.$DB_ROW['livret_ap_id'].' , '.$DB_ROW['matiere_id'].' , '.$DB_ROW['prof_id'].' )' );
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap DROP INDEX livret_ap ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap DROP INDEX matiere_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap DROP INDEX prof_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap ADD INDEX livret_page_ref (livret_page_ref) ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap DROP matiere_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap DROP prof_id ' );
    }
    // recharger [sacoche_livret_page]
    if(empty($reload_sacoche_livret_page))
    {
      $reload_sacoche_livret_page = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-06-24 => 2016-06-30
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-06-24')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-06-30';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // recharger [sacoche_livret_page]
    if(empty($reload_sacoche_livret_page))
    {
      $reload_sacoche_livret_page = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_seuil]
    if(empty($reload_sacoche_livret_seuil))
    {
      $reload_sacoche_livret_seuil = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_seuil.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-06-30 => 2016-07-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-06-30')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-07-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nettoyer les intitulés des référentiels plus en profondeur que les seuls caractères NULL
    $tab_bad = array( "\x00" , "\x01" , "\x02" , "\x03" , "\x04" , "\x05" , "\x06" , "\x07" , "\x08" , "\x0B" , "\x0C" , "\x0E" , "\x0F" , "\x10" , "\x11" , "\x12" , "\x13" , "\x14" , "\x15" , "\x16" , "\x17" , "\x18" , "\x19" , "\x1A" , "\x1B" , "\x1C" , "\x1D" , "\x1E" , "\x1F" , "\x7F" );
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT item_id, item_nom, item_comm FROM sacoche_referentiel_item');
    $DB_SQL = 'UPDATE sacoche_referentiel_item SET item_nom=:item_nom, item_comm=:item_comm WHERE item_id=:item_id';
    foreach($DB_TAB as $DB_ROW)
    {
      $item_nom  = str_replace( $tab_bad , "" , $DB_ROW['item_nom']  , $count1 );
      $item_comm = str_replace( $tab_bad , "" , $DB_ROW['item_comm'] , $count2 );
      if( $count1 || $count2 )
      {
        $DB_VAR = array(
          ':item_id'   => $DB_ROW['item_id'],
          ':item_nom'  => $item_nom,
          ':item_comm' => $item_comm,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
      }
    }
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT theme_id, theme_nom FROM sacoche_referentiel_theme');
    $DB_SQL = 'UPDATE sacoche_referentiel_theme SET theme_nom=:theme_nom WHERE theme_id=:theme_id';
    foreach($DB_TAB as $DB_ROW)
    {
      $theme_nom  = str_replace( $tab_bad , "" , $DB_ROW['theme_nom']  , $count );
      if( $count )
      {
        $DB_VAR = array(
          ':theme_id'   => $DB_ROW['theme_id'],
          ':theme_nom'  => $theme_nom,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
      }
    }
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT domaine_id, domaine_nom FROM sacoche_referentiel_domaine');
    $DB_SQL = 'UPDATE sacoche_referentiel_domaine SET domaine_nom=:domaine_nom WHERE domaine_id=:domaine_id';
    foreach($DB_TAB as $DB_ROW)
    {
      $domaine_nom  = str_replace( $tab_bad , "" , $DB_ROW['domaine_nom']  , $count );
      if( $count )
      {
        $DB_VAR = array(
          ':domaine_id'   => $DB_ROW['domaine_id'],
          ':domaine_nom'  => $domaine_nom,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR );
      }
    }
    // recharger [sacoche_livret_jointure_referentiel] (car ajout de clefs + colonne trouvée manquante sur une installation)
    if(empty($reload_sacoche_livret_jointure_referentiel))
    {
      $reload_sacoche_livret_jointure_referentiel = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_referentiel.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-07-19 => 2016-08-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-07-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-08-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_matiere] --> retirée par la suite
    /*
    $reload_sacoche_livret_matiere = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_matiere.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    */
    // nouvelle table [sacoche_siecle_import]
    $reload_sacoche_siecle_import = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_siecle_import.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // recharger [sacoche_livret_page]
    if(empty($reload_sacoche_livret_page))
    {
      $reload_sacoche_livret_page = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_rubrique]
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_colonne]
    if(empty($reload_sacoche_livret_colonne))
    {
      $reload_sacoche_livret_colonne = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_colonne.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_seuil]
    if(empty($reload_sacoche_livret_seuil))
    {
      $reload_sacoche_livret_seuil = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_seuil.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_jointure_referentiel]
    if(empty($reload_sacoche_livret_jointure_referentiel))
    {
      $reload_sacoche_livret_jointure_referentiel = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_referentiel.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-08-12 => 2016-08-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-08-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-08-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    // Le serveur CAS change pour l'ENT du 77
    if($connexion_nom=='logica_ent77')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas" WHERE parametre_nom="cas_serveur_root" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-08-29 => 2016-09-21
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-08-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-09-21';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre
    $droit_socle_pourcentage_acquis = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_pourcentage_acquis"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_proposition_positionnement" , "'.$droit_socle_pourcentage_acquis.'")' );
    // Ajout de matières
    if(empty($reload_sacoche_matiere))
    {
      $insert = '
        (9941, 0, 1,  99, 0, 255,      0, "C23QM", "Questionner le monde (cycles 2-3)"),
        (9942, 0, 1,  99, 0, 255,      0, "C23ST", "Sciences et technologie (cycles 2-3)"),
        (9943, 0, 1,  99, 0, 255,      0, "C23EA", "Enseignements artistiques (cycles 2-3)"),
        (9944, 0, 1,  99, 0, 255,      0, "C23LV", "Langue vivante (cycles 2-3)") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
    }
    // recharger [sacoche_siecle_import] qui comportait un défaut de définition
    if(empty($reload_sacoche_siecle_import))
    {
      $reload_sacoche_siecle_import = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_siecle_import.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-09-21 => 2016-09-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-09-21')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-09-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un mode de calcul
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_methode referentiel_calcul_methode ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3","frequencemin","frequencemax") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores, ou de la plus fréquente. Valeur surclassant la configuration par défaut." ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-09-23 => 2016-10-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-09-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-10-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_nom, parametre_valeur FROM sacoche_parametre WHERE parametre_nom LIKE "droit_officiel_bulletin_%" ');
    foreach($DB_TAB as $DB_ROW)
    {
      $parametre_nom  = str_replace( 'bulletin' , 'livret' , $DB_ROW['parametre_nom'] );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("'.$parametre_nom.'" , "'.$DB_ROW['parametre_valeur'].'")' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-10-03 => 2016-10-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-10-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-10-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre
    $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_voir_score_bilan" ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_score_maitrise" , "'.$droit.'")' );
    // correction d'une valeur dans sacoche_livret_colonne
    if(empty($reload_sacoche_livret_colonne))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_colonne SET livret_colonne_seuil_defaut_max=80 WHERE livret_colonne_id=33' );
    }
    // recharger [sacoche_socle_domaine]
    if(empty($reload_sacoche_socle_domaine))
    {
      $reload_sacoche_socle_domaine = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_domaine.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_socle_composante]
    if(empty($reload_sacoche_socle_composante))
    {
      $reload_sacoche_socle_composante = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_composante.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_rubrique]
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // adapter les ids de rubrique dans [sacoche_livret_jointure_referentiel]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="c2_domaine" AND livret_rubrique_ou_matiere_id=82' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id=82 WHERE livret_rubrique_type="c2_domaine" AND livret_rubrique_ou_matiere_id=81' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id=76 WHERE livret_rubrique_type="c2_domaine" AND livret_rubrique_ou_matiere_id=74' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id=74 WHERE livret_rubrique_type="c2_domaine" AND livret_rubrique_ou_matiere_id=73' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id=132 WHERE livret_rubrique_type="c3_domaine" AND livret_rubrique_ou_matiere_id=131' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id=131 WHERE livret_rubrique_type="c3_domaine" AND livret_rubrique_ou_matiere_id=112' );
    // modif colonne de [sacoche_livret_jointure_modaccomp_eleve]
    if(empty($reload_sacoche_livret_jointure_modaccomp_eleve))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_modaccomp_eleve CHANGE info_complement info_complement TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT "Dans le cas où la modalité d\'accompagnement est PPRE."' );
    }
    // modif colonne de [sacoche_livret_ap]
    if(empty($reload_sacoche_livret_ap))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_ap CHANGE livret_ap_titre livret_ap_titre VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    }
    // modif colonne de [sacoche_livret_epi]
    if(empty($reload_sacoche_livret_epi))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_epi CHANGE livret_epi_titre livret_epi_titre VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    }
    // suppr colonnes de [sacoche_livret_parcours_type]
    if(empty($reload_sacoche_livret_parcours_type))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours_type DROP livret_parcours_type_url_sitegouv ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours_type DROP livret_parcours_type_url_txtofficiel ' );
    }
    // modifier [sacoche_matiere]
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere CHANGE matiere_code matiere_code CHAR(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Issu de SIECLE <MATIERE CODE> ; Id de la BCN requis pour l\'export LSUN (à précéder de zéros). Si hors BCN, on envoie à LSU matiere_id (à précéder de X)." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_code="" WHERE matiere_code="0" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_code=LPAD(matiere_code,6,"0") WHERE matiere_code!="" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ADD matiere_siecle TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Si présente dans import SIECLE." AFTER matiere_active ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ADD INDEX matiere_siecle (matiere_siecle) ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="BPMV" WHERE matiere_id=9739 ' );
      // avant de poser une clef unique sur matiere_ref, il faut s'assurer qu'il n'y a pas de doublon à cause d'une matière personnalisée (normalement non, mais qd la matière officielle a été rajoutée après coup...)
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT GROUP_CONCAT(matiere_id SEPARATOR ",") AS liste_matiere_id, matiere_ref, COUNT(matiere_id) AS nombre FROM sacoche_matiere GROUP BY matiere_ref HAVING nombre>1 ');
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_matiere_id = explode(',',$DB_ROW['liste_matiere_id']);
          sort($tab_matiere_id);
          unset($tab_matiere_id[0]);
          foreach($tab_matiere_id as $i => $matiere_id)
          {
            $fin = $i+1;
            $matiere_ref = (strlen($DB_ROW['matiere_ref'])<5) ? $DB_ROW['matiere_ref'].$fin : substr($DB_ROW['matiere_ref'],0,4).$fin;
            DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="'.$matiere_ref.'" WHERE matiere_id='.$matiere_id );
          }
        }
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ADD UNIQUE matiere_ref (matiere_ref) ' );
    }
    // modifier [sacoche_livret_jointure_referentiel]
    if(empty($reload_sacoche_livret_jointure_referentiel))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_referentiel CHANGE livret_rubrique_ou_matiere_id livret_rubrique_ou_matiere_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
    }
    // supprimer [sacoche_livret_matiere] et reporter son contenu dans [sacoche_matiere]
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_livret_matiere"');
    if(!empty($DB_TAB))
    {
      $DB_TAB_SIECLE = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_livret_matiere WHERE livret_siecle_code_gestion IS NOT NULL ');
      $DB_TAB_PERSO  = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_livret_matiere WHERE livret_siecle_code_gestion IS NULL ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_livret_matiere' );
      $tab_rubrique_type = array("c3_matiere","c4_matiere");
      if(!empty($DB_TAB_SIECLE))
      {
        $tab_id_conversion = array();
        foreach($DB_TAB_SIECLE as $DB_ROW)
        {
          $id_find = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM sacoche_matiere WHERE matiere_ref="'.$DB_ROW['livret_siecle_code_gestion'].'"' );
          if($id_find)
          {
            $tab_id_conversion[ $DB_ROW['livret_matiere_id'] ] = $id_find;
            DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_siecle="1" WHERE matiere_id='.$id_find );
          }
          else
          {
            $DB_SQL = 'INSERT INTO sacoche_matiere(matiere_active, matiere_siecle, matiere_usuelle, matiere_famille_id, matiere_nb_demandes, matiere_ordre, matiere_code, matiere_ref, matiere_nom) ';
            $DB_SQL.= 'VALUES(                    :matiere_active,:matiere_siecle,:matiere_usuelle,:matiere_famille_id,:matiere_nb_demandes,:matiere_ordre,:matiere_code,:matiere_ref,:matiere_nom)';
            $DB_VAR = array(
              ':matiere_active'      => 0,
              ':matiere_siecle'      => 1,
              ':matiere_usuelle'     => 0,
              ':matiere_famille_id'  => 0,
              ':matiere_nb_demandes' => 0,
              ':matiere_ordre'       => 255,
              ':matiere_code'        => str_pad( $DB_ROW['livret_siecle_code_matiere'] , 6 , "0" , STR_PAD_LEFT ),
              ':matiere_ref'         => $DB_ROW['livret_siecle_code_gestion'],
              ':matiere_nom'         => $DB_ROW['livret_siecle_libelle'],
            );
            DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
            $tab_id_conversion[ $DB_ROW['livret_matiere_id'] ] = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
          }
        }
        foreach($tab_rubrique_type as $rubrique_type)
        {
          $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="'.$rubrique_type.'" ');
          foreach($DB_TAB as $DB_ROW)
          {
            if(isset($tab_id_conversion[$DB_ROW['livret_rubrique_ou_matiere_id']]))
            {
              DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id='.$tab_id_conversion[$DB_ROW['livret_rubrique_ou_matiere_id']].' WHERE livret_rubrique_type="'.$rubrique_type.'" AND livret_rubrique_ou_matiere_id='.$DB_ROW['livret_rubrique_ou_matiere_id'].' AND element_id='.$DB_ROW['element_id'] );
            }
            else
            {
              DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="'.$rubrique_type.'" AND livret_rubrique_ou_matiere_id='.$DB_ROW['livret_rubrique_ou_matiere_id'].' AND element_id='.$DB_ROW['element_id'] );
            }
          }
        }
      }
      if(!empty($DB_TAB_PERSO))
      {
        foreach($tab_rubrique_type as $rubrique_type)
        {
          $rubrique_join = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT livret_page_rubrique_join FROM sacoche_livret_page WHERE livret_page_rubrique_type="'.$rubrique_type.'" LIMIT 1' );
          if($rubrique_join!='matiere')
          {
            DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="'.$rubrique_type.'"' );
          }
          $join_nb = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT COUNT(*) FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="'.$rubrique_type.'"' );
          if($join_nb)
          {
            DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type="'.$rubrique_type.'"' );
            $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM sacoche_matiere WHERE matiere_active=1 ');
            foreach($DB_TAB as $DB_ROW)
            {
              $DB_SQL = 'INSERT INTO sacoche_livret_jointure_referentiel(livret_rubrique_type, livret_rubrique_ou_matiere_id, element_id) ';
              $DB_SQL.= 'VALUES(                                        :livret_rubrique_type,:livret_rubrique_ou_matiere_id,:element_id)';
              $DB_VAR = array(
                ':livret_rubrique_type'          => $rubrique_type,
                ':livret_rubrique_ou_matiere_id' => $DB_ROW['matiere_id'],
                ':element_id'                    => $DB_ROW['matiere_id'],
              );
              DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
            }
          }
        }
      }
      // adapter [sacoche_livret_jointure_ap_prof] et [sacoche_livret_jointure_epi_prof] qui ne doivent être reliés qu'à des matières du LSU (donc auxquelles sont reliés des éléments de référentiels)
      $tab_matiere_ok = array();
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT livret_rubrique_ou_matiere_id FROM sacoche_livret_jointure_referentiel WHERE livret_rubrique_type IN ("c3_matiere","c4_matiere") ');
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_matiere_ok[$DB_ROW['livret_rubrique_ou_matiere_id']] = $DB_ROW['livret_rubrique_ou_matiere_id'];
      }
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_livret_jointure_ap_prof ');
      foreach($DB_TAB as $DB_ROW)
      {
        if(!isset($tab_matiere_ok[$DB_ROW['matiere_id']]))
        {
          DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_ap_prof WHERE livret_ap_id='.$DB_ROW['livret_ap_id'] );
        }
      }
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_livret_jointure_epi_prof ');
      foreach($DB_TAB as $DB_ROW)
      {
        if(!isset($tab_matiere_ok[$DB_ROW['matiere_id']]))
        {
          DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_jointure_epi_prof WHERE livret_epi_id='.$DB_ROW['livret_epi_id'] );
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-10-10 => 2016-10-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-10-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-10-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if( ($connexion_nom=='entlibre_essonne') || ($connexion_nom=='entlibre_picardie') )
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas" WHERE parametre_nom="cas_serveur_root" ' );
    }
    else if($connexion_nom=='pentila_nero')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="ent.pentilanero.fr" WHERE parametre_nom="cas_serveur_host" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-10-19 => 2016-10-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-10-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-10-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='itop_marne')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.ent-marne.fr" WHERE parametre_nom="cas_serveur_host" ' );
    }
    // recharger [sacoche_livret_modaccomp]
    if(empty($reload_sacoche_livret_modaccomp))
    {
      $reload_sacoche_livret_modaccomp = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_modaccomp.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // recharger [sacoche_livret_rubrique]
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // Adaptations aux nouveaux indices de [sacoche_livret_rubrique]
    $tab_modifs = array(
      'c3_domaine' => array(
        146 => 206,
        145 => 205,
        144 => 204,
        143 => 203,
        142 => 202,
        141 => 201,
        133 => 191,
        132 => 181,
        131 => 171,
        123 => 163,
        122 => 162,
        121 => 161,
        111 => 151,
        103 => 143,
        102 => 142,
        101 => 141,
         94 => 134,
         93 => 133,
         92 => 132,
         91 => 131,
      ),
      'c2_domaine' => array(
         84 => 124,
         83 => 123,
         82 => 122,
         81 => 121,
         76 => 111,
         75 => 102,
         74 => 101,
         73 =>  92,
         72 =>  91,
         71 =>  81,
         63 =>  73,
         62 =>  72,
         61 =>  71,
         54 =>  64,
         53 =>  63,
         52 =>  62,
         51 =>  61,
      ),
      'c1_theme' => array(
         45 =>  55,
         44 =>  54,
         43 =>  53,
         42 =>  52,
         41 =>  51,
         34 =>  44,
         33 =>  43,
         32 =>  42,
         31 =>  41,
         24 =>  32,
         23 =>  31,
      ),
    );
    foreach( $tab_modifs as $rubrique_type => $tab_ids )
    {
      foreach( $tab_ids as $id_old => $id_new )
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_jointure_referentiel SET livret_rubrique_ou_matiere_id='.$id_new.' WHERE livret_rubrique_ou_matiere_id='.$id_old.' AND livret_rubrique_type="'.$rubrique_type.'" ' );
      }
    }
    // nouvelle table [sacoche_livret_saisie]
    $reload_sacoche_livret_saisie = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_saisie.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_saisie_jointure_prof]
    $reload_sacoche_livret_saisie_jointure_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_saisie_jointure_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-10-29 => 2016-11-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-10-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-11-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout colonne à [sacoche_livret_page]
    if(empty($reload_sacoche_livret_page))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_page ADD livret_page_moyenne_classe TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Modifiable pour 6e 5e 4e 3e." AFTER livret_page_colonne ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_moyenne_classe=1 WHERE livret_page_colonne="moyenne" ' );
    }
    // ajout colonne à [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe ADD jointure_date_verrou DATE DEFAULT NULL AFTER jointure_etat ' );
    }
    // nouvelle table [sacoche_livret_saisie_memo_detail]
    $reload_sacoche_livret_saisie_memo_detail = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_saisie_memo_detail.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-11-06 => 2016-11-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-11-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-11-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout colonnes à [sacoche_officiel_archive]
    if(empty($reload_sacoche_officiel_archive))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_archive ADD archive_md5_image4 CHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER archive_md5_image3 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_archive ADD officiel_archive_id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (officiel_archive_id) ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-11-13 => 2016-11-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-11-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-11-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif colonne de [sacoche_officiel_archive]
    if(empty($reload_sacoche_officiel_archive))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_archive CHANGE archive_contenu archive_contenu MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ' );
      // et suppression des entrées tronquées corrompues
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_officiel_archive WHERE LENGTH(archive_contenu) = 65535 ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-11-14 => 2016-11-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-11-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-11-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout colonne à [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe ADD jointure_date_export DATE DEFAULT NULL AFTER jointure_date_verrou ' );
    }
    // modif colonne de [sacoche_livret_colonne]
    if(empty($reload_sacoche_livret_colonne))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_colonne CHANGE livret_colonne_legende livret_colonne_legende VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramétrable pour l\'échelle." ' );
    }
    // ajout lignes à [sacoche_livret_seuil]
    if(empty($reload_sacoche_livret_seuil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_livret_seuil VALUES ("6e" , 21,  0,  34),("6e" , 22, 35,  64),("6e" , 23, 65,  89),("6e" , 24, 90, 100),("5e" , 21,  0,  34),("5e" , 22, 35,  64),("5e" , 23, 65,  89),("5e" , 24, 90, 100),("4e" , 21,  0,  34),("4e" , 22, 35,  64),("4e" , 23, 65,  89),("4e" , 24, 90, 100),("3e" , 21,  0,  34),("3e" , 22, 35,  64),("3e" , 23, 65,  89),("3e" , 24, 90, 100) ' );
    }
    // recharger [sacoche_livret_modaccomp]
    if(empty($reload_sacoche_livret_modaccomp))
    {
      $reload_sacoche_livret_modaccomp = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_modaccomp.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-11-18 => 2016-11-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-11-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-11-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_export]
    $reload_sacoche_livret_export = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_export.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // modif colonne de [sacoche_livret_page]
    if(empty($reload_sacoche_livret_page))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_page CHANGE livret_page_parcours livret_page_parcours VARCHAR(31) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Chaîne de livret_parcours_type_code (PAR_AVN,PAR_CIT,PAR_ART,PAR_SAN)." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_parcours = REPLACE(livret_page_parcours,"P_","PAR_") ' );
    }
    // modif colonne de [sacoche_livret_parcours]
    if(empty($reload_sacoche_livret_parcours))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours CHANGE livret_parcours_type_code livret_parcours_type_code VARCHAR(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_parcours SET livret_parcours_type_code = REPLACE(livret_parcours_type_code,"P_","PAR_") ' );
    }
    // recharger [sacoche_livret_parcours_type]
    if(empty($reload_sacoche_livret_parcours_type))
    {
      $reload_sacoche_livret_parcours_type = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parcours_type.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-11-27 => 2016-12-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-11-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-12-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre
    $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_socle_proposition_positionnement" ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_prevision_points_brevet" , "'.$droit.'")' );
    // recharger [sacoche_livret_page] qui comportait un défaut d'initialisation
    $nb_lignes = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT COUNT(*) FROM sacoche_livret_page ');
    if(!$nb_lignes)
    {
      $reload_sacoche_livret_page = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-12-05 => 2016-12-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-12-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-12-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif valeurs [sacoche_livret_rubrique]
    if(empty($reload_sacoche_livret_rubrique))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_rubrique SET livret_rubrique_id_elements=livret_rubrique_id WHERE livret_rubrique_id IN(92,102,122,123,124,162,163,202,203,204,205,206) ' );
    }
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_couleur" , "oui")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_fond" , "gris")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_only_socle" , "0")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_import_bulletin_notes" , "oui")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_retroactif" , "non")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-12-15 => 2016-12-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-12-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-12-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // retrait clef unique de [sacoche_livret_epi]
    if(empty($reload_sacoche_livret_epi))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_epi DROP INDEX livret_epi ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_epi ADD INDEX livret_epi_theme_code (livret_epi_theme_code) ' );
    }
  }
}

?>
