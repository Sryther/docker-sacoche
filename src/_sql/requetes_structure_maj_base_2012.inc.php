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
// MAJ 2011-12-30 => 2012-01-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-12-30')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-01-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // la validation d'une association de PP aux classes supprimait les associations des propriétaires de groupes de besoin et sans doute aussi des évaluations sur une sélection d'élèves ; problème potentiel probablement depuis 2010-10-29
    $DB_SQL = 'SELECT groupe_id, user_id, SUM(jointure_pp) as nb_pp ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
    $DB_SQL.= 'WHERE groupe_type IN ("besoin","eval") AND user_profil="professeur" ';
    $DB_SQL.= 'GROUP BY groupe_id ';
    $DB_SQL.= 'HAVING nb_pp=0 ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    // Lors de la rectification, si plusieurs profs sont associés aux groupes, il n'est pas dit que l'on tombe sur le bon...
    $DB_SQL = 'INSERT IGNORE INTO sacoche_jointure_user_groupe (user_id,groupe_id,jointure_pp) VALUES(:user_id,:groupe_id,1) ';
    foreach($DB_TAB as $DB_ROW)
    {
      $DB_VAR = array(
        ':user_id'   => $DB_ROW['user_id'],
        ':groupe_id' => $DB_ROW['groupe_id'],
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-01-04 => 2012-01-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-01-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-01-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("mdp_longueur_mini" , "6")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-01-13 => 2012-01-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-01-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-01-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de la syntaxe de mémorisation des profs associés à un devoir (le caractère "," est préférable à "_" car dans une recherche LIKE ce dernier signifie "un caractère" (même si on peut l'échapper).
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_partage=REPLACE(devoir_partage,"_",",")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-01-16 => 2012-01-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-01-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-01-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un champ de "sacoche_parametre"
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""' );
    // récupération de paramètre
    $denomination = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="denomination"' );
    // modification de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="webmestre_uai" WHERE parametre_nom="uai"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="webmestre_denomination" WHERE parametre_nom="denomination"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_denomination"     , :denomination )' , array(':denomination'=>$denomination) );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_adresse1"         , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_adresse2"         , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_adresse3"         , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_telephone"        , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_fax"              , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_courriel"         , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "mois_bascule_annee_scolaire"    , "8" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_horizontal_gauche"    , "110" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_horizontal_milieu"    , "100" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_horizontal_droite"    , "20" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_vertical_haut"        , "50" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_vertical_milieu"      , "45" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "enveloppe_vertical_bas"         , "20" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_infos_etablissement"   , "adresse,telephone,fax,courriel" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_infos_responsables"    , "non" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_nombre_exemplaires"    , "un" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_marge_gauche"          , "5" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_marge_droite"          , "5" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_marge_haut"            , "5" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_marge_bas"             , "10" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_bulletin_appreciation_generale" , "directeur,profprincipal" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_bulletin_impression_pdf"        , "directeur,aucunprof" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_modele_defaut"                        , "item_synthese" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_appreciation_matiere_presence"   , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_appreciation_matiere_longueur"   , "150" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_appreciation_generale_presence"  , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_pourcentage_acquis_presence"     , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_pourcentage_acquis_modifiable"   , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_pourcentage_acquis_classe"       , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_note_moyenne_score_presence"     , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_note_moyenne_score_modifiable"   , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_item_note_moyenne_score_classe"       , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_socle_pourcentage_acquis_presence"    , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_socle_etat_validation_presence"       , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "bulletin_socle_appreciation_generale_presence" , "1" )' );
    // ajouter de deux entrées dans sacoche_jointure_groupe_periode pour paramétrer les bulletins
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode ADD bulletin_modele     ENUM("item_detail","item_synthese","socle_detail","socle_synthese")   COLLATE utf8_unicode_ci NOT NULL DEFAULT "item_synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode ADD bulletin_etat       ENUM("ferme_vierge","ouvert_profs","ouvert_synthese","ferme_complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "ferme_vierge" ' );
    // ajout d'une table pour les bulletins
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_bulletin' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_bulletin ( periode_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, matiere_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, prof_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, eleve_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, bulletin_note TINYINT(3) UNSIGNED NOT NULL DEFAULT 0, bulletin_pourcentage TINYINT(3) UNSIGNED NOT NULL DEFAULT 0, bulletin_appreciation VARCHAR(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", UNIQUE KEY bulletin_key (periode_id,matiere_id,prof_id,eleve_id), KEY periode_id (periode_id), KEY matiere_id (matiere_id), KEY prof_id (prof_id), KEY eleve_id (eleve_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-01-28 => 2012-02-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-01-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // maj liens suite passage https
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://sacoche.sesamath.net","https://sacoche.sesamath.net")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-08 => 2012-02-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // récupération des informations sur les matières
    $listing_matieres_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="matieres"' );
    $DB_TAB_communes     = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id, matiere_nb_demandes, matiere_ordre FROM sacoche_matiere WHERE matiere_id IN('.$listing_matieres_id.')');
    $DB_TAB_specifiques  = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id, matiere_nb_demandes, matiere_ordre, matiere_ref, matiere_nom FROM sacoche_matiere WHERE matiere_partage=0');
    // nouvelles tables sacoche_matiere (intégration native de 1900 matières) et sacoche_matiere_famille
    $reload_sacoche_matiere = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_matiere.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    $reload_sacoche_matiere_famille = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_matiere_famille.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // incrément des ids pour éviter tout souci
    $increment = 11000;
    $tab_tables = array('sacoche_bulletin','sacoche_jointure_user_matiere','sacoche_demande','sacoche_referentiel','sacoche_referentiel_domaine');
    foreach($tab_tables as $table_nom)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET matiere_id=matiere_id+'.$increment );
    }
    // Nettoyage champ devenu obsolète
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="matieres"' );
    // mise à jour des ids & infos des matières de l'établissement
    $tab_id_old_to_id_new = array(1=>901,2=>316,3=>328,4=>315,5=>327,6=>50,7=>813,8=>1001,9=>320,10=>332,11=>207,12=>406,13=>201,14=>613,15=>623,16=>629,17=>708,18=>54,19=>51,20=>202,21=>321,22=>333,23=>414,24=>40,25=>41,26=>42,27=>43,28=>46,29=>103,30=>507,31=>2757,32=>326,33=>338,34=>9991,35=>323,36=>335,37=>318,38=>330,39=>399,40=>3128,41=>711,42=>9992,43=>606,90=>9901,91=>9902,92=>9903,93=>9904,94=>9905,95=>9906,96=>9907,97=>9908,99=>9999);
    foreach($DB_TAB_communes as $DB_ROW)
    {
      $id_old_incremente = $DB_ROW['matiere_id']+$increment;
      $id_new = $tab_id_old_to_id_new[$DB_ROW['matiere_id']];
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_active=1, matiere_nb_demandes='.$DB_ROW['matiere_nb_demandes'].', matiere_ordre='.$DB_ROW['matiere_ordre'].' WHERE matiere_id='.$id_new );
      foreach($tab_tables as $table_nom)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET matiere_id='.$id_new.' WHERE matiere_id='.$id_old_incremente );
      }
    }
    foreach($DB_TAB_specifiques as $DB_ROW)
    {
      $id_old_incremente = $DB_ROW['matiere_id']+$increment;
      $matiere_existante_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM sacoche_matiere WHERE matiere_ref="'.$DB_ROW['matiere_ref'].'"' );
      if(!$matiere_existante_id)
      {
        $id_new = 10000 + $DB_ROW['matiere_id'] - 100;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES('.$id_new.', 1, 0, 0, '.$DB_ROW['matiere_nb_demandes'].', '.$DB_ROW['matiere_ordre'].', "'.$DB_ROW['matiere_ref'].'", "'.$DB_ROW['matiere_nom'].'")' );
      }
      else
      {
        $id_new = $matiere_existante_id;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_active=1, matiere_nb_demandes='.$DB_ROW['matiere_nb_demandes'].', matiere_ordre='.$DB_ROW['matiere_ordre'].' WHERE matiere_id='.$id_new );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_etat="non" WHERE matiere_id='.$id_old_incremente );
      }
      foreach($tab_tables as $table_nom)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET matiere_id='.$id_new.' WHERE matiere_id='.$id_old_incremente );
      }
    }
    // Nettoyage des entrées qui resteraient (anciennes matières plus utilisées)
    foreach($tab_tables as $table_nom)
    {
      $DB_TAB_obsoletes = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM '.$table_nom.' WHERE matiere_id>'.$increment.' GROUP BY matiere_id');
      foreach($DB_TAB_obsoletes as $DB_ROW)
      {
        $id_old_decremente = $DB_ROW['matiere_id'] - $increment;
        $id_new = isset($tab_id_old_to_id_new[$id_old_decremente]) ? $tab_id_old_to_id_new[$id_old_decremente] : 0 ;
        if($id_new)
        {
          DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET matiere_id='.$id_new.' WHERE matiere_id='.$DB_ROW['matiere_id'] );
        }
        else
        {
          DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM '.$table_nom.' WHERE matiere_id='.$DB_ROW['matiere_id'] );
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-13 => 2012-02-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Inversion des noms de deux matières
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="P3A", matiere_nom="3 Principaux éléments de mathématiques"  WHERE matiere_id=9903');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="P3B", matiere_nom="3 Culture scientifique et technologique" WHERE matiere_id=9904');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-15 => 2012-02-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // récupération des informations sur les niveaux, cycles, paliers
    $listing_niveaux_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="niveaux"' );
    $listing_cycles_id  = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="cycles"' );
    $listing_paliers_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="paliers"' );
    // nouvelles tables sacoche_niveau et sacoche_niveau_famille
    $reload_sacoche_niveau = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_niveau.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    $reload_sacoche_niveau_famille = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_niveau_famille.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout champ table sacoche_socle_palier
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_palier ADD palier_actif BOOLEAN NOT NULL DEFAULT 0 AFTER palier_id , ADD INDEX ( palier_actif )' );
    // Nettoyage champs devenu obsolète
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN ( "niveaux","cycles","paliers" )' );
    // Mise à jour des champs utilisés
    if($listing_niveaux_id)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_actif=1 WHERE niveau_id IN('.$listing_niveaux_id.')');
    }
    if($listing_cycles_id)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_actif=1 WHERE niveau_id IN('.$listing_cycles_id.')');
    }
    if($listing_paliers_id)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_palier SET palier_actif=1 WHERE palier_id IN('.$listing_paliers_id.')');
    }
    // modification / suppression de clefs
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_bulletin DROP INDEX bulletin_key , DROP INDEX eleve_id , ADD PRIMARY KEY ( eleve_id , periode_id , matiere_id , prof_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande DROP INDEX user_id');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_devoir_item DROP INDEX devoir_item_key , DROP INDEX devoir_id , ADD PRIMARY KEY ( devoir_id , item_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode DROP INDEX groupe_periode_key , DROP INDEX groupe_id , ADD PRIMARY KEY ( groupe_id , periode_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_parent_eleve DROP INDEX parent_eleve_key , DROP INDEX parent_id , ADD PRIMARY KEY ( parent_id , eleve_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_entree DROP INDEX validation_entree_key , DROP INDEX user_id , ADD PRIMARY KEY ( user_id , entree_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_groupe DROP INDEX user_groupe_key , DROP INDEX user_id , ADD PRIMARY KEY ( user_id , groupe_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_matiere DROP INDEX user_matiere_key , DROP INDEX user_id , ADD PRIMARY KEY ( user_id , matiere_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier DROP INDEX validation_pilier_key , DROP INDEX user_id , ADD PRIMARY KEY ( user_id , pilier_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel DROP INDEX referentiel_id , DROP INDEX matiere_id , ADD PRIMARY KEY ( matiere_id , niveau_id ) ');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie DROP INDEX saisie_key , DROP INDEX devoir_id , ADD PRIMARY KEY ( devoir_id , eleve_id , item_id ) ');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-19 => 2012-02-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // La dernière version n'installait pas la table sacoche_socle_palier (virgule mal placée)
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_socle_palier"');
    if(empty($DB_TAB))
    {
      $reload_sacoche_socle_palier = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_palier.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-22 => 2012-02-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Les 2 dernières versions n'installaient pas la table sacoche_jointure_user_pilier (mot ADD en trop)
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_jointure_user_pilier"');
    if(empty($DB_TAB))
    {
      $reload_sacoche_jointure_user_pilier = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_user_pilier.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-23 => 2012-02-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-02-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une table sacoche_selection_item
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_selection_item' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_selection_item ( selection_item_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, selection_item_nom   VARCHAR(60)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "", selection_item_liste TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY (selection_item_id), KEY user_id (user_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-02-29 => 2012-03-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-02-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-03-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de 2 références d'ENT
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"scolastance02","ent_02") WHERE parametre_nom="connexion_nom"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"entea","ent_alsace") WHERE parametre_nom="connexion_nom"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-03-05 => 2012-03-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-03-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-03-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un champ pour paramétrer une date limite d'autoévaluation, et de deux champs pour l'adresse d'un sujet ou d'un corrigé
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_autoeval_date DATE NOT NULL DEFAULT "0000-00-00" AFTER devoir_visible_date ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_doc_sujet   VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_doc_corrige VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-03-12 => 2012-03-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-03-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-03-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification des deux champs pour l'adresse d'un sujet ou d'un corrigé
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_doc_sujet devoir_doc_sujet     VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_doc_corrige devoir_doc_corrige VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    $base_id = (strpos(SACOCHE_STRUCTURE_BD_NAME,'sac_base_')===0) ? substr(SACOCHE_STRUCTURE_BD_NAME,9) : '0' ;
    $url_dossier_devoirs = URL_DIR_DEVOIR.$base_id.'/';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_doc_sujet   = CONCAT("'.$url_dossier_devoirs.'",devoir_doc_sujet)   WHERE devoir_doc_sujet!="" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_doc_corrige = CONCAT("'.$url_dossier_devoirs.'",devoir_doc_corrige) WHERE devoir_doc_corrige!="" ' );
    // ajout d'un champ pour ajouter un message à une demande d'évaluation
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande ADD demande_messages TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // fusionner user_statut + user_statut_date = user_sortie_date
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_statut_date user_sortie_date DATE NOT NULL DEFAULT "9999-12-31" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_sortie_date="9999-12-31" WHERE user_statut=1 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP user_statut ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_sortie_date ( user_sortie_date ) ' );
    // modification des deux champs pour les identifiants externes
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_id_ent  user_id_ent  VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l\'ENT...)." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_id_gepi user_id_gepi VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Login de l\'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin." ' );
    // ajout d'une table sacoche_message
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_message' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_message ( message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT, user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, message_debut_date DATE NOT NULL DEFAULT "0000-00-00", message_fin_date DATE NOT NULL DEFAULT "0000-00-00", message_destinataires TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT "", message_contenu TINYTEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY (message_id), KEY user_id (user_id) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-03-29 => 2012-05-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-03-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-05-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_jointure_groupe_periode
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode DROP bulletin_modele, DROP bulletin_etat' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode ADD officiel_releve ENUM( "", "1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", ADD officiel_bulletin ENUM( "", "1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", ADD officiel_palier1 ENUM( "", "1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", ADD officiel_palier2 ENUM( "", "1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", ADD officiel_palier3 ENUM( "", "1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""' );
    // modification sacoche_parametre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_infos_etablissement" WHERE parametre_nom="bulletin_infos_etablissement"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_infos_responsables"  WHERE parametre_nom="bulletin_infos_responsables" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_nombre_exemplaires"  WHERE parametre_nom="bulletin_nombre_exemplaires" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_marge_gauche"        WHERE parametre_nom="bulletin_marge_gauche"       ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_marge_droite"        WHERE parametre_nom="bulletin_marge_droite"       ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_marge_haut"          WHERE parametre_nom="bulletin_marge_haut"         ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_marge_bas"           WHERE parametre_nom="bulletin_marge_bas"          ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_bulletin_appreciation_generale" WHERE parametre_nom="droit_bulletin_appreciation_generale"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_bulletin_impression_pdf" WHERE parametre_nom="droit_bulletin_impression_pdf"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_voir_officiel_releve_archive"            , "directeur,professeur" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_voir_officiel_bulletin_archive"          , "directeur,professeur" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_voir_officiel_socle_archive"             , "directeur,professeur" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_releve_appreciation_generale"   , "directeur,profprincipal" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_releve_impression_pdf"          , "directeur,aucunprof" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_socle_appreciation_generale"    , "directeur,profprincipal" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_socle_impression_pdf"           , "directeur,aucunprof" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN ( "bulletin_modele_defaut","cycles","paliers","bulletin_item_appreciation_matiere_presence","bulletin_item_appreciation_matiere_longueur","bulletin_item_appreciation_generale_presence","bulletin_item_pourcentage_acquis_presence","bulletin_item_pourcentage_acquis_modifiable","bulletin_item_pourcentage_acquis_classe","bulletin_item_note_moyenne_score_presence","bulletin_item_note_moyenne_score_modifiable","bulletin_item_note_moyenne_score_classe","bulletin_socle_pourcentage_acquis_presence","bulletin_socle_etat_validation_presence","bulletin_socle_appreciation_generale_presence" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_tampon_signature"                     , "remplacer" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_rubrique"         , "300" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_generale"         , "400" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_moyenne_scores"                , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_pourcentage_acquis"            , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_cases_nb"                      , "4" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_coef"                      , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_socle"                     , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_domaine"                   , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_theme"                     , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_couleur"                       , "oui" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_legende"                       , "oui" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_rubrique"       , "200" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_generale"       , "400" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_moyenne_scores"              , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_note_sur_20"                 , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_moyenne_classe"              , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_couleur"                     , "oui" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_legende"                     , "oui" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_rubrique"          , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_generale"          , "400" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_only_presence"                  , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_pourcentage_acquis"             , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_etat_validation"                , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_couleur"                        , "oui" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_legende"                        , "oui" )' );
    // suppression sacoche_bulletin
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_bulletin' );
    // ajout d'une table sacoche_officiel_saisie
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_officiel_saisie' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_officiel_saisie ( officiel_type ENUM("releve","bulletin","palier1","palier2","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin", periode_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, eleve_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, rubrique_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT "matiere_id ou pilier_id ; 0 pour l\'appréciation générale", prof_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0 pour la note, avec commentaire dans saisie_appreciation si report non automatique", saisie_note DECIMAL(3,1) UNSIGNED DEFAULT NULL COMMENT "sur 20, à multiplier par 5 pour avoir le pourcentage", saisie_appreciation TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY ( eleve_id , officiel_type , periode_id , rubrique_id , prof_id ), KEY officiel_type (officiel_type), KEY periode_id (periode_id), KEY rubrique_id (rubrique_id), KEY prof_id (prof_id) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    // ajout d'une table sacoche_officiel_archive
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_officiel_archive' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_officiel_archive ( user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, officiel_type ENUM("releve","bulletin","palier1","palier","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin", periode_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "mis à zéro lors d\'un changement d\'année", archive_date DATE NOT NULL DEFAULT "0000-00-00", archive_document MEDIUMBLOB NOT NULL, KEY user_id (user_id), KEY officiel_type (officiel_type), KEY periode_id (periode_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    // ajout d'une table sacoche_signature
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_signature' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_signature ( user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0 pour le tampon de l\'établissement", signature_contenu  MEDIUMBLOB NOT NULL, signature_format CHAR(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", signature_largeur SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, signature_hauteur SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY (user_id) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    // modification des deux champs pour les identifiants externes ; déjà fait le 29/03/2012 mais oublié pour les nouveaux établissements
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_id_ent  user_id_ent  VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l\'ENT...)." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_id_gepi user_id_gepi VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Login de l\'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin." ' );
    // ajout d'une ligne dans sacoche_parametre pour retenir la liste des paliers car on en a besoin pour personnaliser certains menus
    $listing_paliers_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT GROUP_CONCAT(palier_id SEPARATOR ",") AS listing_paliers_id FROM sacoche_socle_palier WHERE palier_actif=1 ORDER BY palier_ordre ASC' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "liste_paliers_actifs" , "'.$listing_paliers_id.'" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-05-01 => 2012-05-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-05-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-05-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_moyenne_generale"      , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_releve_modifier_statut"   , "directeur,aucunprof" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_bulletin_modifier_statut" , "directeur,aucunprof" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_socle_modifier_statut"    , "directeur,aucunprof" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-05-14 => 2012-05-21
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-05-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-05-21';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage d'un champ de sacoche_parametre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"restreindre","tampon") WHERE parametre_nom="officiel_tampon_signature"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"remplacer","signature_ou_tampon") WHERE parametre_nom="officiel_tampon_signature"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"superposer","signature_ou_tampon") WHERE parametre_nom="officiel_tampon_signature"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"juxtaposer","signature_ou_tampon") WHERE parametre_nom="officiel_tampon_signature"' );
    // renommage de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_releve_voir_archive" WHERE parametre_nom="droit_voir_officiel_releve_archive"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_bulletin_voir_archive" WHERE parametre_nom="droit_voir_officiel_bulletin_archive"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_socle_voir_archive" WHERE parametre_nom="droit_voir_officiel_socle_archive"' );
    // 7 dates de saisies trouvées vides dans ma base pour des évals sur des élèves sélectionnés vers le 10/10/2010...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_date="2010-10-10", saisie_visible_date="2010-10-10" WHERE saisie_date="0000-00-00" ' );
    // modification mineure de sacoche_officiel_saisie
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie CHANGE periode_id periode_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT 0 ' );
    // conversion de sacoche_officiel_archive (vide) en sacoche_officiel_fichier
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_officiel_archive' );
    // La supprimer si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette ancienne table éventuellement existante ne serait pas réinitialisée.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_officiel_fichier' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_officiel_fichier ( user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, officiel_type ENUM("releve","bulletin","palier1","palier","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin", periode_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, fichier_date DATE NOT NULL DEFAULT "0000-00-00", UNIQUE KEY user_id (user_id,officiel_type,periode_id), KEY officiel_type (officiel_type), KEY periode_id (periode_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-05-21 => 2012-06-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-05-21')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-06-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // enregistrement des signatures en base64 sinon souci lors de sauvegarde/restauration de bases
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT user_id, signature_contenu FROM sacoche_signature');
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_signature SET signature_contenu=:signature_contenu WHERE user_id='.$DB_ROW['user_id'] , array(':signature_contenu'=>base64_encode($DB_ROW['signature_contenu'])) );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-06-03 => 2012-06-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-06-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-06-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un champs pour enregistrer les préférences de l'utilisateur relatives aux messages d'accueil
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_param_accueil VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "user,alert,info,help,ecolo"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-06-07 => 2012-06-25
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-06-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-06-25';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // valeur renommée dans sacoche_niveau_famille
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau_famille SET niveau_famille_nom="Cycles (primaire, collège, lycée)" WHERE niveau_famille_id=1' );
    }
    // ajout de matières
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9911, 0, 1,  99, 0, 255, "APS"  , "Apprendre à porter secours") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9912, 0, 1,  99, 0, 255, "PSC1" , "Prévention et secours civiques de niveau 1") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9913, 0, 1,  99, 0, 255, "PSC2" , "Prévention et secours civiques de niveau 2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9921, 0, 1,  99, 0, 255, "APER" , "Attestation de première éducation à la route") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9922, 0, 1,  99, 0, 255, "ASSR1", "Attestation scolaire de sécurité routière de niveau 1") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9923, 0, 1,  99, 0, 255, "ASSR2", "Attestation scolaire de sécurité routière de niveau 2") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-06-25 => 2012-06-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-06-25')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-06-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correctifs de champs mal initialisés
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_releve_modifier_statut"   WHERE parametre_nom="droit_officiel_releve_changer_etat"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_bulletin_modifier_statut" WHERE parametre_nom="droit_officiel_bulletin_changer_etat"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_officiel_socle_modifier_statut"    WHERE parametre_nom="droit_officiel_socle_changer_etat"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-06-28 => 2012-07-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-06-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-07-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // déplacement matière FLE
    $tab_tables = array(
      'sacoche_matiere'               => 'matiere_id',
      'sacoche_jointure_user_matiere' => 'matiere_id',
      'sacoche_demande'               => 'matiere_id',
      'sacoche_referentiel'           => 'matiere_id',
      'sacoche_referentiel_domaine'   => 'matiere_id',
      'sacoche_officiel_saisie'       => 'rubrique_id',
    );
    foreach($tab_tables as $table_nom => $table_champ)
    {
      // Ignore dans le cas où il s'agirait d'une maj d'une très vieille base et que du coup lors de la MAJ 2012-02-08 => 2012-02-13 on ait déjà récupéré une base de matières correcte.
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE IGNORE '.$table_nom.' SET '.$table_champ.'=300 WHERE '.$table_champ.'=397' );
    }
    // ajout d'une famille de matières
    if(empty($reload_sacoche_matiere_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 45, 3, "Métiers des arts appliqués (suite)") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere_famille SET matiere_famille_nom="Métiers des arts appliqués" WHERE matiere_famille_id=27 ' );
    }
    // ajout de matières
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  74, 0, 0, 100, 0, 255, "DECPR", "Découverte professionnelle") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  91, 0, 0, 100, 0, 255, "EPE"  , "Etude personnalisée encadrée") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  92, 0, 0, 100, 0, 255, "ATELP", "Atelier de professionnalisation") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  93, 0, 0, 100, 0, 255, "FILOC", "Formation d\'initiative locale") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  94, 0, 0, 100, 0, 255, "AT-PX", "Ateliers principaux") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (  95, 0, 0, 100, 0, 255, "AT-CO", "Ateliers complémentaires") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 387, 0, 0,   3, 0, 255, "ALL8" , "Littérature étrangère en allemand") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 388, 0, 0,   3, 0, 255, "AGL8" , "Littérature étrangère en anglais") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 389, 0, 0,   3, 0, 255, "ARA8" , "Littérature étrangère en arabe") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 390, 0, 0,   3, 0, 255, "CHI8" , "Littérature étrangère en chinois") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 391, 0, 0,   3, 0, 255, "DAN8" , "Littérature étrangère en danois") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 392, 0, 0,   3, 0, 255, "ESP8" , "Littérature étrangère en espagnol") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 393, 0, 0,   3, 0, 255, "ITA8" , "Littérature étrangère en italien") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 394, 0, 0,   3, 0, 255, "JAP8" , "Littérature étrangère en japonais") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 395, 0, 0,   3, 0, 255, "POR8" , "Littérature étrangère en portugais") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 396, 0, 0,   3, 0, 255, "NEE8" , "Littérature étrangère en néerlandais") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 397, 0, 0,   3, 0, 255, "NEE8" , "Littérature étrangère en néerlandais") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 398, 0, 0,   3, 0, 255, "RUS8" , "Littérature étrangère en russe") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 521, 0, 0,   5, 0, 255, "SSPOL", "Sciences sociales et politiques") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 522, 0, 0,   5, 0, 255, "ECOAP", "Economie approfondie") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 601, 0, 0,   6, 0, 255, "EIST" , "Enseignement intégré de science et technologie") ' ); // Créée en attendant que la matière apparaisse officiellement...
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 720, 0, 0,   7, 0, 255, "TREAL", "Technologie de réalisation") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 738, 0, 0,   7, 0, 255, "STECH", "Sciences et technologie") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 739, 0, 0,   7, 0, 255, "TFABR", "Technologie de fabrication") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 918, 0, 0,   9, 0, 255, "CDESG", "Culture du design graphique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 919, 0, 0,   9, 0, 255, "CTYPO", "Culture typographique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 920, 0, 0,   9, 0, 255, "PPGRA", "Pratique plastique & graphique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 921, 0, 0,   9, 0, 255, "DANAL", "Dessin analytique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (1234, 0, 0,  12, 0, 255, "CDIRP", "Conception dévelop. industrialisation realisation produits") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (1753, 0, 0,  17, 0, 255, "AHUDD", "Architecture, habitat & urbanisme, développement durable") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3088, 0, 0,  30, 0, 255, "ECAGT", "Ecologie agronomie et territoires") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3089, 0, 0,  30, 0, 255, "MAPHY", "Microbiologie appliquée & physiopathologie") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3244, 0, 0,  32, 0, 255, "AOCCL", "Anatomie-occlusodontie") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3657, 0, 0,  36, 0, 255, "DGEMC", "Droit et grands enjeux du monde contemporain") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3659, 0, 0,  36, 0, 255, "ENVEJ", "Environnement économique et juridique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3660, 0, 0,  36, 0, 255, "COJAT", "Cadre organisationnel juridique activité touristique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3661, 0, 0,  36, 0, 255, "DR-VD", "Droit et veille juridique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3731, 0, 0,  37, 0, 255, "ISCNU", "Informatique et sciences du numérique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (3831, 0, 0,  38, 0, 255, "ECOOR", "Economie et organisation") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4059, 0, 0,  40, 0, 255, "MERTC", "Tourisme et territoire") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4060, 0, 0,  40, 0, 255, "MCPT" , "Mercatique conception prestation touristique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4154, 0, 0,  41, 0, 255, "GRCLI", "Gestion de la relation client") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4155, 0, 0,  41, 0, 255, "GINFT", "Gestion de l\'information touristique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4156, 0, 0,  41, 0, 255, "IMMED", "Information et multimedias") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4157, 0, 0,  41, 0, 255, "ITOUR", "Information et tourismatique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4158, 0, 0,  41, 0, 255, "CCOMM", "Culture de la communication") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4501, 0, 0,  45, 0, 255, "HUMOD", "Humanités modernes") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4502, 0, 0,  45, 0, 255, "SMAJU", "Stratégie marketing juridique") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4503, 0, 0,  45, 0, 255, "CPRTE", "Cultures et pratiques techniques") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4504, 0, 0,  45, 0, 255, "PPLME", "Pratiques plastiques et médiations") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4505, 0, 0,  45, 0, 255, "IPRRE", "Innovation, prospective et recherche") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4506, 0, 0,  45, 0, 255, "LEXRE", "Laboratoire expérimentation et recherche") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4507, 0, 0,  45, 0, 255, "MACPR", "Macro-projet") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4508, 0, 0,  45, 0, 255, "MREPR", "Mémoire de recherche professionnel") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (4509, 0, 0,  45, 0, 255, "MELVE", "Mémoire en langue vivante etrangère") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-07-07 => 2012-09-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-07-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-09-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage et modification de la table sacoche_signature (pour sacoche_image)
    // supprimer la table sacoche_image si elle existe : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), cette table éventuellement existante gène.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_image' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'RENAME TABLE sacoche_signature TO sacoche_image' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image CHANGE signature_contenu image_contenu MEDIUMBLOB NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image CHANGE signature_format image_format CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image CHANGE signature_largeur image_largeur SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT 0' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image CHANGE signature_hauteur image_hauteur SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT 0' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image ADD image_objet ENUM( "signature", "photo" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "photo" AFTER user_id' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_image SET image_objet="signature"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image ADD INDEX image_objet( image_objet )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-09-10 => 2012-10-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-09-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-10-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modifier un paramètre CAS pour l'ENT Mirabelle
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='mirabelle')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.ent-place.fr" WHERE parametre_nom="cas_serveur_host"' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-10-06 => 2012-10-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-10-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-10-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre de département pour les connexions ENT (sert dans l'affichage du menu déroulant) ; à initialiser.
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    $tab_correspondance = array
    (
      'sacoche' => '', 'perso' => '', 'saml' => '',
      'ent_02' => '02', 'ent_02_v2' => '02',
      'ent_04' => '04', 'ent_04_v2' => '04',
      'ent_52' => '52', 'ent_52_v2' => '52',
      'ent_06' => '06',
      'elie' => '23',
      'ent_27' => '27',
      'ent_38' => '38',
      'cybercolleges42' => '42',
      'mirabelle' => '57',
      'ent_60' => '60',
      'laclasse' => '69',
      'cartabledesavoie' => '73',
      'ent_77' => '77',
      'ent_80' => '80',
      'ent_90' => '90',
      'ent_92' => '92',
      'celia' => '93',
      'lilie' => '93',
      'ent_95' => '95',
      'ent_reunion' => '974',
    );
    if(isset($tab_correspondance[$connexion_nom]))
    {
      $connexion_departement = $tab_correspondance[$connexion_nom];
    }
    else
    {
      $webmestre_uai = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="webmestre_uai"' );
      $connexion_departement = ($webmestre_uai) ? substr($webmestre_uai,1,2) : '' ;
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "connexion_departement" , "'.$connexion_departement.'" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-10-09 => 2012-10-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-10-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-10-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Intégration des champs professionnels de SEGPA comme nouvelles matières.
    if(empty($reload_sacoche_matiere_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 98, 3, "Champs professionnels en SEGPA")' );
    }
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9801, 0, 0,  98, 0, 255, "CPHAB", "Habitat")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9802, 0, 0,  98, 0, 255, "CPHAS", "Hygiène - Alimentation - Services")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9803, 0, 0,  98, 0, 255, "CPERE", "Espace rural et environnement")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9804, 0, 0,  98, 0, 255, "CPVDM", "Vente - Distribution - Magasinage")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9805, 0, 0,  98, 0, 255, "CPPI" , "Production industrielle")' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-10-10 => 2012-10-26
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-10-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-10-26';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Passage de champs DATE à NULL possible et par défaut.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user   CHANGE user_connexion_date  user_connexion_date  DATETIME NULL DEFAULT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user   CHANGE user_tentative_date  user_tentative_date  DATETIME NULL DEFAULT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_autoeval_date devoir_autoeval_date DATE     NULL DEFAULT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user   SET  user_connexion_date=NULL WHERE  user_connexion_date="0000-00-00 00:00:00"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user   SET  user_tentative_date=NULL WHERE  user_tentative_date="0000-00-00 00:00:00"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_autoeval_date=NULL WHERE devoir_autoeval_date="0000-00-00"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-10-26 => 2012-11-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-10-26')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-11-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_only_socle"           , "0"   )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_retroactif"           , "non" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_conv_sur20"           , "0"   )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_only_socle"         , "0"   )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_retroactif"         , "non" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_barre_acquisitions" , "1"   )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "calcul_retroactif"                    , "non" )' );
    // ajouter une entrée dans sacoche_referentiel pour paramétrer la prise en compte des évaluations antérieures par défaut
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_calcul_retroactif ENUM("non","oui") COLLATE utf8_unicode_ci NOT NULL DEFAULT "non" COMMENT "Avec ou sans prise en compte des évaluations antérieures. Valeur surclassant la configuration par défaut." AFTER referentiel_calcul_limite' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-11-05 => 2012-11-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-11-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-11-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres et renommage d'autres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_releve_etat_acquisition"    , "parent,eleve" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_etat_acquisition" , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_releve_moyenne_score"          WHERE parametre_nom="droit_bilan_moyenne_score"'      );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_releve_pourcentage_acquis"     WHERE parametre_nom="droit_bilan_pourcentage_acquis"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_releve_conversion_sur_20"      WHERE parametre_nom="droit_bilan_note_sur_vingt"'     );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_releve_conversion_sur_20"   WHERE parametre_nom="officiel_releve_conv_sur20"'     );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_bulletin_conversion_sur_20" WHERE parametre_nom="officiel_bulletin_note_sur_20"'  );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-11-12 => 2012-11-17
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-11-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-11-17';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif champ table sacoche_parametre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_valeur parametre_valeur VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    // nouvelle table sacoche_officiel_assiduite
    $reload_sacoche_officiel_assiduite = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_assiduite.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_assiduite"   , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_assiduite" , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_assiduite"    , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_ligne_supplementaire"   , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_ligne_supplementaire" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_ligne_supplementaire"    , "" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-11-17 => 2012-12-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-11-17')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-12-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modifier un paramètre CAS pour l'ENT de Haute-Marne sur ItsLearning
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='ent_52_v2')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas-enthautemarne" WHERE parametre_nom="cas_serveur_root"' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-12-01 => 2012-12-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-12-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-12-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif index et champ table sacoche_image
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image DROP INDEX image_objet' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image DROP PRIMARY KEY , ADD UNIQUE (user_id , image_objet )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_image CHANGE image_objet image_objet ENUM( "signature", "photo", "logo" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "photo"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-12-12 => 2012-12-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-12-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-12-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif champ table sacoche_message
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message CHANGE message_contenu message_contenu TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ' );
    // modif champ table sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_param_accueil user_param_accueil VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "user,alert,messages,demandes,help,ecolo" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_accueil=REPLACE(user_param_accueil,"info","messages,demandes")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2012-12-14 => 2012-12-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-12-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2012-12-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modifier un paramètre CAS pour l'ENT Agora06
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='ent_06')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.agora06.fr" WHERE parametre_nom="cas_serveur_host"' );
    }
  }
}

?>
