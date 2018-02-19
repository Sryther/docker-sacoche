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
// MAJ 2012-12-27 => 2013-01-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2012-12-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-01-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // distinction des ENT agora06 et ent_nice
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='ent_06')
    {
      $sesamath_uai = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="sesamath_uai"' );
      if(substr($sesamath_uai,0,3)=='006')
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="agora06" WHERE parametre_nom="connexion_nom"' );
      }
      else
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="ent_nice" WHERE parametre_nom="connexion_nom"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.enteduc.fr" WHERE parametre_nom="cas_serveur_host"' );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-01-05 => 2013-01-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-01-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-01-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // re-suppression ligne dans sacoche_parametre car figurait toujours dans le sql de création.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="droit_eleve_demandes"' );
    // nouvelle table sacoche_user_profil
    $reload_sacoche_user_profil = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_user_profil.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // modif champ table sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_profil user_profil_sigle CHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Nomenclature issue de la BCN (table n_fonction_filiere) et de user_profils SDET." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_profil_sigle="ADM" WHERE user_profil_sigle="adm" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_profil_sigle="ELV" WHERE user_profil_sigle="ele" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_profil_sigle="TUT" WHERE user_profil_sigle="par" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_profil_sigle="ENS" WHERE user_profil_sigle="pro" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_profil_sigle="DIR" WHERE user_profil_sigle="dir" ' );
    // transfert ligne "mdp_longueur_mini" de sacoche_parametre vers sacoche_user_profil
    $mdp_longueur_mini = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="mdp_longueur_mini"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user_profil SET user_profil_mdp_longueur_mini="'.$mdp_longueur_mini.'" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="mdp_longueur_mini"' );
    // transfert ligne "duree_inactivite" de sacoche_parametre vers sacoche_user_profil
    $duree_inactivite = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="duree_inactivite"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user_profil SET user_profil_duree_inactivite="'.$duree_inactivite.'" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="duree_inactivite"' );
    // transfert lignes "modele_*" de sacoche_parametre vers sacoche_user_profil
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_nom, parametre_valeur FROM sacoche_parametre WHERE parametre_nom LIKE "modele_%"');
    foreach($DB_TAB as $DB_ROW)
    {
      $profil_type = substr($DB_ROW['parametre_nom'],7);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user_profil SET user_profil_login_modele="'.$DB_ROW['parametre_valeur'].'" WHERE user_profil_type="'.$profil_type.'"' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom LIKE "modele_%"' );
    // modification valeurs "droit_*" de sacoche_parametre
    $tab_corresp = array( 'directeur'=>'DIR' , 'professeur'=>'ENS,DOC,EDU' , 'profprincipal'=>'ENS,ONLY_PP' , 'profcoordonnateur'=>'ENS,DOC,EDU,ONLY_COORD' , 'parent'=>'TUT' , 'eleve'=>'ELV' );
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_nom, parametre_valeur FROM sacoche_parametre WHERE parametre_nom LIKE "droit_%"');
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_valeurs = explode(',',$DB_ROW['parametre_valeur']);
      foreach($tab_valeurs as $key => $valeur)
      {
        if( ($valeur=='aucunprof') || ($valeur=='') )
        {
          unset($tab_valeurs[$key]);
        }
        else
        {
          $tab_valeurs[$key] = $tab_corresp[$valeur];
        }
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.implode(',',$tab_valeurs).'" WHERE parametre_nom="'.$DB_ROW['parametre_nom'].'"' );
    }
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_saisir_assiduite" , "DIR,EDU" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-01-18 => 2013-01-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-01-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-01-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de clefs, sauf si bonnes requêtes de création de la table déjà passées
    if(empty($reload_sacoche_user_profil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user_profil ADD INDEX user_profil_obligatoire ( user_profil_obligatoire ) ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user_profil ADD INDEX user_profil_type ( user_profil_type ) ');
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-01-28 => 2013-01-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-01-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-01-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_fusion_niveaux"              , "1"   )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_releve_corriger_appreciation"   , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_bulletin_corriger_appreciation" , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_officiel_socle_corriger_appreciation"    , "DIR" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-01-31 => 2013-02-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-01-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-02-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout champ table sacoche_message
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message ADD message_dests_cache TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_message SET message_dests_cache="," ' );
    // modifications table sacoche_officiel_saisie
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie CHANGE eleve_id eleve_ou_classe_id  MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "id élève ou classe suivant le champ saisie_type" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie ADD saisie_type ENUM("eleve","classe") COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" COMMENT "indique si la saisie concerne un élève ou une classe" AFTER prof_id , ADD INDEX ( saisie_type ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie DROP PRIMARY KEY , ADD PRIMARY KEY ( eleve_ou_classe_id , officiel_type , periode_id , rubrique_id , prof_id , saisie_type )  ' );
    // ajout index table sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX eleve_classe_id ( eleve_classe_id ) ' );
    // correctif nouvelles entrées table sacoche_matiere
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_usuelle=0 WHERE matiere_id>'.ID_MATIERE_PARTAGEE_MAX );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-02-04 => 2013-02-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-02-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-02-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_acquis_texte_nombre" , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_acquis_texte_code"   , "1" )' );
    // ajout champ table sacoche_devoir
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_fini TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 ' );
    // ajout champs table sacoche_user (en prévision, car pas encore utilisé à ce jour)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_naissance_date DATE NULL DEFAULT NULL AFTER user_prenom ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_email VARCHAR(63) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER user_naissance_date ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-02-22 => 2013-03-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-02-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-03-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "cas_serveur_url_login"    , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "cas_serveur_url_logout"   , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "cas_serveur_url_validate" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_affecter_langue" , "DIR,ENS,ONLY_LV" )' );
    // Intégration des niveaux APSA comme nouveaux niveaux.
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau_famille VALUES ( 10, 2, 3, "APSA (activités physiques, sportives et artistiques)") ' );
    }
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 211, 0, 10,  32, "N1", "", "Niveau 1") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 212, 0, 10,  50, "N2", "", "Niveau 2") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 213, 0, 10,  80, "N3", "", "Niveau 3") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 214, 0, 10,  90, "N4", "", "Niveau 4") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 215, 0, 10, 180, "N5", "", "Niveau 5") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-03-20 => 2013-04-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-03-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-04-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_brevet_serie
    $reload_sacoche_brevet_serie = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_brevet_serie.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table sacoche_brevet_epreuve
    $reload_sacoche_brevet_epreuve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_brevet_epreuve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table sacoche_brevet_saisie
    $reload_sacoche_brevet_saisie = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_brevet_saisie.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table sacoche_geo_academie
    $reload_sacoche_geo_academie = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_geo_academie.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table sacoche_geo_departement
    $reload_sacoche_geo_departement = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_geo_departement.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout champ table sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_brevet_serie VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT "X" COMMENT "Série du brevet pour Notanet." AFTER eleve_langue ' );
    // modification sacoche_groupe
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD fiche_brevet ENUM( "","1vide","2rubrique","3synthese","4complet" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // modification sacoche_parametre (paramètres CAS pour ENT Toutatice)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='toutatice')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="casshib/shib/toutatice" WHERE parametre_nom="cas_serveur_root" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="https://www.toutatice.fr/casshib/shib/666666/serviceValidate" WHERE parametre_nom="cas_serveur_url_validate" ' );
    }
    // retirer une ligne qui ne correspond à rien
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="annee_utilisation_numero" ' );
  }
}

if($version_base_structure_actuelle=='2013-04-08')
{
  // Cas d'une installation de SACoche à un moment où le numéro de version de la base n'était pas bien renseigné (entre le 22 et le 24 avril).
  $version_base_structure_actuelle = '2013-04-22';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-04-22 => 2013-04-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-04-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-04-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout de niveaux
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau_famille VALUES ( 11, 1, 8, "Métiers d\'arts") ' );
    }
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (  10, 0,  2,   0,    "TPS", "0041000111.", "Maternelle, très petite section") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (  55, 0,  4,  65,   "DIMA", "115..99911.", "Dispositif d\'initiation des métiers en alternance") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 117, 0,  7, 157,     "MC", "253.....11.", "Mention complémentaire") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 118, 0,  7, 158,   "1BP2", "254.....21.", "Brevet Professionnel 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 119, 0,  7, 159,   "2BP2", "254.....22.", "Brevet Professionnel 2 ans, 2e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 141, 0, 11, 181,  "1BMA1", "250.....11.", "BMA 1 an") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 142, 0, 11, 182,  "1BMA2", "251.....21.", "BMA 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 143, 0, 11, 183,  "2BMA2", "251.....22.", "BMA 2 ans, 2e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 151, 0, 11, 191,  "1DMA1", "315.....11.", "DMA 1 an") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 152, 0, 11, 192,  "1DMA2", "316.....21.", "DMA 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 153, 0, 11, 193,  "2DMA2", "316.....22.", "DMA 2 ans, 2e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 154, 0, 11, 194,  "2DUT1", "350.....21.", "DUT 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 155, 0, 11, 195,  "2DUT2", "350.....22.", "DUT 2 ans, 2e année") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-04-29 => 2013-05-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-04-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-05-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_fiche_brevet_appreciation_generale" , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_fiche_brevet_corriger_appreciation" , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_fiche_brevet_impression_pdf"        , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_fiche_brevet_modifier_statut"       , "DIR" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_fiche_brevet_voir_archive"          , "DIR,ENS,DOC,EDU" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-05-05 => 2013-05-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-05-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-05-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_brevet_fichier
    $reload_sacoche_brevet_fichier = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_brevet_fichier.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // vider table sacoche_brevet_saisie maintenant que les choix de fonctionnement sont arrêtés
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_brevet_saisie' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-05-12 => 2013-05-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-05-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-05-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à la table sacoche_user_profil
    if(empty($reload_sacoche_user_profil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user_profil ADD user_profil_mdp_date_naissance TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 AFTER user_profil_mdp_longueur_mini ');
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-05-14 => 2013-06-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-05-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-06-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une ligne à la table sacoche_user_profil
    if(empty($reload_sacoche_user_profil))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_user_profil VALUES ("ENT", 0, 1, 1, 1, "partenaire" , "sansobjet", "sansobjet", "partenaire" , "partenaires" , "partenariat conventionné (ENT)" , "partenariats conventionnés (ENT)" , "ppp.nnnnnnnn", 6, 0, 15) ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-06-01 => 2013-06-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-06-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-06-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // connecteurs renommés
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='ent_02')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_02"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_02_v2')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itslearning_02"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_auvergne')    { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_auvergne"       WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_04')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_04"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_04_v2')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itslearning_04"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='agora06')         { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_agora06"               WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_nice')        { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_nice"                  WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='entmip')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_entmip"              WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_montpellier') { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="esup_montpellier"           WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='liberscol')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="liberscol_dijon"            WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='elie')            { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="logica_elie"                WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_27')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_eure"                  WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='e-college31')     { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_ecollege31"          WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_38')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_isere"                 WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='cybercolleges42') { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_cybercolleges42"     WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='e-lyco')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_elyco"               WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_52')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_52"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_52_v2')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itslearning_52"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='place')           { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_place"                 WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='mirabelle')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_mirabelle"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_lille')       { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_savoirsnumeriques5962" WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_60')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_oise"                  WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_alsace')      { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_alsace"                WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_77')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="logica_ent77"               WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_80')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_somme"                 WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_90')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_90"             WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_92')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_enc92"                 WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='celia')           { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="logica_celia"               WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='lilie')           { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="logica_lilie"               WHERE parametre_nom="connexion_nom" ' ); }
    if($connexion_nom=='ent_95')          { DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_valdoise"              WHERE parametre_nom="connexion_nom" ' ); }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-06-05 => 2013-06-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-06-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-06-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correctif nom d'un connecteur erroné
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="scolastance_alsace" WHERE parametre_nom="connexion_nom" AND parametre_valeur="itop_alsace"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-06-10 => 2013-06-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-06-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-06-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Il apparait que la table sacoche_image peut manquer sur certaines installations...
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_image"');
    if(empty($DB_TAB))
    {
      $reload_sacoche_image = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_image.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-06-11 => 2013-06-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-06-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-06-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à la table sacoche_referentiel
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_information VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-06-23 => 2013-09-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-06-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-09-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // L'ENT Place prend la place de Mirabelle sur le 57
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itop_place" WHERE parametre_nom="connexion_nom" AND parametre_valeur="itop_mirabelle"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-09-23 => 2013-10-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-09-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-10-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un champ afin de pouvoir repérer les demandes d'évaluations en attente de saisie
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_note saisie_note ENUM( "VV", "V", "R", "RR", "ABS", "DISP", "NE", "NF", "NN", "NR", "REQ" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN" ' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_voir_etat_acquisition_avec_evaluation" , "" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-10-05 => 2013-11-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-10-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-11-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "droit_modifier_email" , "DIR,ENS,DOC,EDU,TUT,ELV" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-11-28 => 2013-12-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-11-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-12-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // valeurs renommées dans sacoche_niveau
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ref="ULIS", niveau_nom="Unité localisée pour l\'inclusion scolaire" WHERE niveau_id=38' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Maternelle, toute petite section" WHERE niveau_id=10' );
    }
    // modification sacoche_officiel_fichier
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier CHANGE fichier_date fichier_date_generation DATE NOT NULL DEFAULT "0000-00-00"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier ADD fichier_date_consultation_eleve  DATE DEFAULT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier ADD fichier_date_consultation_parent DATE DEFAULT NULL' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_archive_ajout_message_copie"      , "1" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_archive_retrait_tampon_signature" , "1" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-12-08 => 2013-12-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-12-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-12-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout champ table sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_pass_key CHAR(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_prof_principal" , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_prof_principal"   , "0" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_prof_principal"    , "0" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-12-13 => 2013-12-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-12-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2013-12-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de paramètre mal initialisé
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="oui" WHERE parametre_nom="calcul_retroactif" AND parametre_valeur="1"' );
    // modification sacoche_referentiel
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_retroactif referentiel_calcul_retroactif ENUM("non","oui","annuel") COLLATE utf8_unicode_ci NOT NULL DEFAULT "non" COMMENT "Avec ou sans prise en compte des évaluations antérieures. Valeur surclassant la configuration par défaut." ' );
  }
}

?>
