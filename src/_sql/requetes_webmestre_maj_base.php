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

// Ces méthodes ne concernent que la base WEBMESTRE (donc une installation multi-structures).
// Ces méthodes ne concernent que le webmestre.

// Ce script est appelé automatiquement si besoin lorsque :
// - un webmestre vient de se connecter

class DB_WEBMESTRE_MAJ_BASE extends DB
{

/**
 * Retourner la version de la base du webmestre
 *
 * @param void
 * @return string
 */
public static function DB_version_base()
{
  // Au début, la table avec l'info de version n'existait pas
  $DB_TAB = DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_parametre"');
  if(empty($DB_TAB))
  {
    return '2010-06-24';
  }
  $DB_SQL = 'SELECT parametre_valeur ';
  $DB_SQL.= 'FROM sacoche_parametre ';
  $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
  $DB_VAR = array(':parametre_nom'=>'version_base');
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}


/**
 * Mettre à jour la base du webmestre
 *
 * @param string   $version_base_webmestre_actuelle
 * @return void
 */
public static function DB_maj_base($version_base_webmestre_actuelle)
{

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // On s'arrête si c'est un pb de fichier non récupéré ou de base inaccessible
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if( !VERSION_BASE_WEBMESTRE || !$version_base_webmestre_actuelle )
  {
    $message = (!VERSION_BASE_WEBMESTRE) ? 'Fichier avec version de la base webmestre manquant.' : 'Base de données inaccessible (valeur sacoche_parametre.version_base non récupérée).' ;
    exit_error( 'Erreur MAJ BDD' /*titre*/ , $message /*contenu*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2010-06-24 => 2013-02-15
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2010-06-24')
  {
    // Créer la table supplémentaire
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE TABLE sacoche_parametre ( parametre_nom    VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "", parametre_valeur VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY (parametre_nom) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "version_base" , "" ) ' );
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-02-15';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification de champs
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE geo_id geo_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_localisation structure_localisation VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_id geo_id SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT  ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_ordre geo_ordre SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0"  ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_nom geo_nom VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  ' );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-02-15 => 2013-05-21
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-02-15')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-05-21';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_convention
    $reload_sacoche_convention = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_convention.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-05-21 => 2013-06-01
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-05-21')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-01';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_partenaire
    $reload_sacoche_partenaire = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_partenaire.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-01 => 2013-06-08
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-01')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-08';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à la table sacoche_convention
    if(empty($reload_sacoche_convention))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention ADD convention_creation DATE DEFAULT NULL AFTER convention_date_fin ');
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-08 => 2013-06-09
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-08')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-09';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // rechargement de la table sacoche_convention si inexistante (bug à la création)
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_convention.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-09 => 2013-12-03
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-09')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-12-03';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification d'un champ
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_contact_courriel structure_contact_courriel VARCHAR(63)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-12-03 => 2014-04-22
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-12-03')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2014-04-22';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à la table sacoche_convention
    if(empty($reload_sacoche_convention))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention ADD convention_commentaire TEXT COLLATE utf8_unicode_ci AFTER convention_activation ');
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2014-04-22 => 2014-06-19
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2014-04-22')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2014-06-19';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // oubli d'une colonne à la table sacoche_convention sur certaines install
    $DB_TAB = DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , 'SHOW COLUMNS FROM sacoche_convention LIKE "convention_relance"');
    if(empty($DB_TAB))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention ADD convention_relance DATE DEFAULT NULL AFTER convention_paiement ');
    }
    // ajout d'une colonne à la table sacoche_convention
    if(empty($reload_sacoche_convention))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention ADD convention_mail_renouv DATE DEFAULT NULL AFTER convention_activation ');
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2014-06-19 => 2015-02-22
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2014-06-19')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2015-02-22';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression du champ [partenaire_tentative_date] de la table [sacoche_partenaire]
    if(empty($reload_sacoche_partenaire))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_partenaire DROP partenaire_tentative_date ' );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2015-02-22 => 2016-02-27
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2015-02-22')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2016-02-27';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification des champs DATE car en mode SQL strict des valeurs telles 0000-00-00 ne sont pas tolérées
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_inscription_date structure_inscription_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention CHANGE convention_date_debut convention_date_debut DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention CHANGE convention_date_fin   convention_date_fin   DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention CHANGE convention_creation   convention_creation   DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2016-02-27 => 2017-10-10
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2016-02-27')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2017-10-10';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Augmentation de la longueur de certains champs
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_contact_nom      structure_contact_nom      VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_contact_prenom   structure_contact_prenom   VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_contact_courriel structure_contact_courriel VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_partenaire CHANGE partenaire_denomination partenaire_denomination VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_partenaire CHANGE partenaire_nom          partenaire_nom          VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_partenaire CHANGE partenaire_prenom       partenaire_prenom       VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_partenaire CHANGE partenaire_courriel     partenaire_courriel     VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ');
  }

}

}
?>