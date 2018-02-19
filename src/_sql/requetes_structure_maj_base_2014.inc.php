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
// MAJ 2013-12-15 => 2014-01-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2013-12-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_pages_nb" , "optimise" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-07 => 2014-01-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-01-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_professeurs" , "0000-00-00" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_eleves"      , "0000-00-00" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_parents"     , "0000-00-00" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-14 => 2014-01-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-01-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT Cartable de Savoie)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='cartabledesavoie')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.cartabledesavoie.com" WHERE parametre_nom="cas_serveur_host" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-20 => 2014-01-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-01-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression de la Note de Vie Scolaire des fiches brevet
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_brevet_epreuve WHERE brevet_epreuve_code=112 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_brevet_saisie ' ); // retirer seulement brevet_epreuve_code IN(112,255) ne suffit pas
    // matière renommée
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="7 Autonomie et initiative" WHERE matiere_id=9908 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-31 => 2014-02-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-01-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // valeur renommée dans sacoche_niveau
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Classe pour l\'inclusion scolaire" WHERE niveau_id=21' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-07 => 2014-02-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-02-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // niveau ajouté
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 6, 0, 1, 68, "P3S", "", "Cycle SEGPA") ' );
    }
    // table sacoche_demande modifiée
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande CHANGE user_id eleve_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande ADD prof_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Dans le cas où l\'élève adresse sa demande à un prof donné." AFTER item_id ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-11 => 2014-02-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-02-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Intégration des spécialités de baccalauréat professionnel comme nouvelles matières.
    if(empty($reload_sacoche_matiere_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 97, 4, "Spécialités de baccalauréat professionnel")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere_famille SET matiere_famille_categorie=4 WHERE matiere_famille_id=98 ' );
    }
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9701, 0, 0,  97, 0, 255, "BPASS", "Accompagnement soins et services à la personne")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9702, 0, 0,  97, 0, 255, "BPARC", "Accueil - relation clients et usagers")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9703, 0, 0,  97, 0, 255, "BPAOA", "Aéronautique - option mécanicien systèmes avionique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9704, 0, 0,  97, 0, 255, "BPAOC", "Aéronautique - option mécanicien système cellule")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9705, 0, 0,  97, 0, 255, "BPA3O", "Aéronautique à trois options (avionique - syst. et structures)")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9706, 0, 0,  97, 0, 255, "BPAEA", "Agencement de l\'espace architectural")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9707, 0, 0,  97, 0, 255, "BPAFB", "Aménagement et finition du bâtiment")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9708, 0, 0,  97, 0, 255, "BPAMP", "Artisanat et métiers d\'art - option arts de la pierre")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9709, 0, 0,  97, 0, 255, "BPAMC", "Artisanat et métiers d\'art - option comm. visuelle pluri média")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9710, 0, 0,  97, 0, 255, "BPAME", "Artisanat et métiers d\'art - option ébéniste")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9711, 0, 0,  97, 0, 255, "BPAMM", "Artisanat et métiers d\'art - option marchandisage visuel")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9712, 0, 0,  97, 0, 255, "BPAMT", "Artisanat et métiers d\'art - option tapissier d\'ameublement")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9713, 0, 0,  97, 0, 255, "BPAMV", "Artisanat et métiers d\'art - verrerie + enseigne / signalétique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9714, 0, 0,  97, 0, 255, "BPAG" , "Aviation générale")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9715, 0, 0,  97, 0, 255, "BPBIT", "Bio-industries de transformation")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9716, 0, 0,  97, 0, 255, "BPBP" , "Boulanger - pâtissier")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9717, 0, 0,  97, 0, 255, "BPBCT", "Boucher charcutier traiteur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9718, 0, 0,  97, 0, 255, "BPCOM", "Commerce")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9719, 0, 0,  97, 0, 255, "BPCSR", "Commercialisation et services en restauration")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9720, 0, 0,  97, 0, 255, "BPCPT", "Comptabilité")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9721, 0, 0,  97, 0, 255, "BPCTR", "Conducteur transport routier marchandises")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9722, 0, 0,  97, 0, 255, "BPCR" , "Construction des carrosseries")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9723, 0, 0,  97, 0, 255, "BPCUI", "Cuisine")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9724, 0, 0,  97, 0, 255, "BPCM" , "Cultures marines")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9725, 0, 0,  97, 0, 255, "BPEEE", "Électrotechnique, énergie, équipement communicants")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9726, 0, 0,  97, 0, 255, "BPEN" , "Environnement nucléaire")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9727, 0, 0,  97, 0, 255, "BPECP", "Esthétique cosmétique parfumerie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9728, 0, 0,  97, 0, 255, "BPEPI", "Étude et définition de produits industriels")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9729, 0, 0,  97, 0, 255, "BPFPI", "Façonnage de produits imprimés, routage")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9730, 0, 0,  97, 0, 255, "BPFON", "Fonderie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9731, 0, 0,  97, 0, 255, "BPGA" , "Gestion-administration")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9732, 0, 0,  97, 0, 255, "BPGP" , "Gestion des pollutions et protection de l\'environnement")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9733, 0, 0,  97, 0, 255, "BPHE" , "Hygiène et environnement")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9734, 0, 0,  97, 0, 255, "BPHPS", "Hygiène, propreté et stérilisation")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9735, 0, 0,  97, 0, 255, "BPIP" , "Industrie de procédés")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9736, 0, 0,  97, 0, 255, "BPIPC", "Industries des pates, papiers et cartons")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9737, 0, 0,  97, 0, 255, "BPIPB", "Interventions sur le patrimoine bâti")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9738, 0, 0,  97, 0, 255, "BPLOG", "Logistique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9739, 0, 0,  97, 0, 255, "BPMVA", "Maintenance de véhicules automobiles")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9740, 0, 0,  97, 0, 255, "BPMEI", "Maintenance des équipements industriels")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9741, 0, 0,  97, 0, 255, "BPMM" , "Maintenance des matériels")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9742, 0, 0,  97, 0, 255, "BPMN" , "Maintenance nautique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9743, 0, 0,  97, 0, 255, "BPMMV", "Métiers de la mode - vêtements")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9744, 0, 0,  97, 0, 255, "BPMCC", "Métiers du cuir option chaussures")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9745, 0, 0,  97, 0, 255, "BPMCM", "Métiers du cuir option maroquinerie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9746, 0, 0,  97, 0, 255, "BPMPB", "Métiers du pressing et de la blanchisserie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9747, 0, 0,  97, 0, 255, "BPMIC", "Microtechniques")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9748, 0, 0,  97, 0, 255, "BPMIT", "Mise en œuvre des matériaux option industries textiles")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9749, 0, 0,  97, 0, 255, "BPMMC", "Mise en œuvre des matériaux opt. mat. métal. moulés, céramiques")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9750, 0, 0,  97, 0, 255, "BPOL" , "Optique lunetterie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9751, 0, 0,  97, 0, 255, "BPOBS", "Ouvrage du bâtiment : aluminium, verre et matériaux de synthèse")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9752, 0, 0,  97, 0, 255, "BPOBM", "Ouvrage du bâtiment : métallerie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9753, 0, 0,  97, 0, 255, "BPPP" , "Perruquier posticheur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9754, 0, 0,  97, 0, 255, "BPPHO", "Photographie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9755, 0, 0,  97, 0, 255, "BPPSP", "Pilotage de systèmes de production automatisée")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9756, 0, 0,  97, 0, 255, "BPPLP", "Pilote de ligne de production")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9757, 0, 0,  97, 0, 255, "BPPC" , "Plastiques et composites")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9758, 0, 0,  97, 0, 255, "BPPET", "Poissonnier écailler traiteur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9759, 0, 0,  97, 0, 255, "BPPCE", "Procédés de la chimie, de l\'eau et des papiers-cartons")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9760, 0, 0,  97, 0, 255, "BPPG" , "Production graphique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9761, 0, 0,  97, 0, 255, "BPPI" , "Production imprimée")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9762, 0, 0,  97, 0, 255, "BPPMD", "Productique mécanique - option décolletage")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9763, 0, 0,  97, 0, 255, "BPPD" , "Prothèse dentaire")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9764, 0, 0,  97, 0, 255, "BPRC" , "Réparation des carrosseries")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9765, 0, 0,  97, 0, 255, "BPSEC", "Secrétariat")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9766, 0, 0,  97, 0, 255, "BPSP" , "Sécurité prévention")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9767, 0, 0,  97, 0, 255, "BPSPL", "Services de proximité et vie locale")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9768, 0, 0,  97, 0, 255, "BPSEN", "Systèmes électroniques numériques")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9769, 0, 0,  97, 0, 255, "BPTA" , "Technicien aérostructure")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9770, 0, 0,  97, 0, 255, "BPTCB", "Technicien constructeur bois")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9771, 0, 0,  97, 0, 255, "BPTFB", "Technicien de fabrication bois et matériaux associes")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9772, 0, 0,  97, 0, 255, "BPTEC", "Technicien de maintenance de syst. énergétiques et climatiques")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9773, 0, 0,  97, 0, 255, "BPTSC" , "Technicien de scierie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9774, 0, 0,  97, 0, 255, "BPTB" , "Technicien du bâtiment : organisation réalisation du gros œuvre")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9775, 0, 0,  97, 0, 255, "BPTFC", "Technique du froid et du conditionnement de l\'air")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9776, 0, 0,  97, 0, 255, "BPTEB", "Technicien d\'études du bâtiment")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9777, 0, 0,  97, 0, 255, "BPTU" , "Technicien d\'usinage")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9778, 0, 0,  97, 0, 255, "BPTCI", "Technicien en chaudronnerie industrielle")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9779, 0, 0,  97, 0, 255, "BPTIC", "Technicien en installation de syst. énergétiques et climatiques")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9780, 0, 0,  97, 0, 255, "BPTGT", "Technicien géomètre topographe")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9781, 0, 0,  97, 0, 255, "BPTMA", "Technicien menuisier agenceur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9782, 0, 0,  97, 0, 255, "BPTM" , "Technicien modeleur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9783, 0, 0,  97, 0, 255, "BPTO" , "Technicien outilleur")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9784, 0, 0,  97, 0, 255, "BPTSU", "Traitements de surfaces")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9785, 0, 0,  97, 0, 255, "BPTRA", "Transport")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9786, 0, 0,  97, 0, 255, "BPTF" , "Transport fluvial")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9787, 0, 0,  97, 0, 255, "BPTP" , "Travaux publics")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9788, 0, 0,  97, 0, 255, "BPV"  , "Vente (prospection-négociation-suivi de clientèle)")' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-12 => 2014-02-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-02-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // La table sacoche_demande manquait sur les nouvelles installations suite à un bug de création de cette table en date du 2014-02-11.
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_demande"');
    if(empty($DB_TAB))
    {
      $reload_sacoche_demande = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_demande.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-15 => 2014-02-17
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-02-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-17';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Encore une boulette de commise sur la table sacoche_demande
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande DROP INDEX demande_key , ADD UNIQUE demande_key (eleve_id,matiere_id,item_id) ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-17 => 2014-03-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-02-17')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-03-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification format champ niveau_id
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau              CHANGE niveau_id niveau_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe              CHANGE niveau_id niveau_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel         CHANGE niveau_id niveau_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine CHANGE niveau_id niveau_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-03-09 => 2014-03-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-03-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-03-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "deconnexion_adresse_redirection" , "" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-03-18 => 2014-04-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-03-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-04-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification champ sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_param_accueil user_param_accueil VARCHAR(83) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "user,alert,messages,resultats,faiblesses,demandes,saisies,officiel,socle,help,ecolo" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_accueil=CONCAT(user_param_accueil,",resultats,faiblesses,saisies,officiel,socle") ' );
    // Modification champ sacoche_parent_adresse
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parent_adresse CHANGE adresse_postal_code adresse_postal_code VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "@see http://fr.wikipedia.org/wiki/Code_postal" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parent_adresse SET adresse_postal_code="" WHERE adresse_postal_code="0" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-04-01 => 2014-04-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-04-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-04-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification champ sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_param_accueil user_param_accueil VARCHAR(93) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "user,alert,messages,resultats,faiblesses,reussites,demandes,saisies,officiel,socle,help,ecolo" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_accueil=CONCAT(user_param_accueil,",reussites") ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-04-04 => 2014-05-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-04-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-05-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification champs sacoche_officiel_assiduite
    if(empty($reload_sacoche_officiel_assiduite))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_assiduite CHANGE assiduite_non_justifie assiduite_absence_nj TINYINT(3) UNSIGNED NULL DEFAULT NULL COMMENT "nombre d\'absences non justifiées" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_assiduite ADD assiduite_retard_nj TINYINT UNSIGNED NULL DEFAULT NULL COMMENT "nombre de retards non justifiés" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-05-03 => 2014-05-24
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-05-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-05-24';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Erreur champ sacoche_officiel_fichier
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier CHANGE officiel_type officiel_type ENUM("releve","bulletin","palier1","palier2","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-05-24 => 2014-07-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-05-24')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-07-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    // Lille passe de Itop à Kosmos
    if($connexion_nom=='itop_savoirsnumeriques5962')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_savoirsnumeriques5962" WHERE parametre_nom="connexion_nom" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.savoirsnumeriques5962.fr" WHERE parametre_nom="cas_serveur_host" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=""                             WHERE parametre_nom="cas_serveur_root" ' );
    }
    // Le 76 passe de Logica à Kosmos mais l'ENT garde le même nom Arsène76
    if($connexion_nom=='logica_arsene76')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_arsene76" WHERE parametre_nom="connexion_nom" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.arsene76.fr" WHERE parametre_nom="cas_serveur_host" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=""                WHERE parametre_nom="cas_serveur_root" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-07-08 => 2014-07-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-07-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-07-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    // Le serveur CAS de ITOP pour l'Isère change
    if($connexion_nom=='itop_isere')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.colleges-isere.fr" WHERE parametre_nom="cas_serveur_host" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-07-16 => 2014-09-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-07-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-09-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    // Le 93 passe de Logica à Kosmos
    if($connexion_nom=='logica_celia')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="kosmos_monecollege93" WHERE parametre_nom="connexion_nom" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.monecollege.fr"   WHERE parametre_nom="cas_serveur_host" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=""                     WHERE parametre_nom="cas_serveur_root" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-09-08 => 2014-09-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-09-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-09-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    // Le serveur scolastance_52 n'existe plus, on le remplace définitivement par itslearning_52 qui était déjà proposé
    if($connexion_nom=='scolastance_52')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itslearning_52"       WHERE parametre_nom="connexion_nom" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.itslearning.com"  WHERE parametre_nom="cas_serveur_host" ' );
    }
    // Le serveur scolastance_90 n'existe plus, on le remplace définitivement par itslearning_90 qui n'était pas proposé
    if($connexion_nom=='scolastance_90')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="itslearning_90"       WHERE parametre_nom="connexion_nom" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas.itslearning.com"  WHERE parametre_nom="cas_serveur_host" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-09-27 => 2014-11-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-09-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-11-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_jointure_devoir_droit] renommée dans la mise à jour suivante [sacoche_jointure_devoir_prof]
    $table_nom = file_exists(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_devoir_prof.sql') ? 'sacoche_jointure_devoir_prof' : 'sacoche_jointure_devoir_droit' ;
    $reload_sacoche_jointure_devoir_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.$table_nom.'.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_jointure_devoir_audio] renommée dans la mise à jour suivante [sacoche_jointure_devoir_eleve]
    // $reload_sacoche_jointure_devoir_audio = TRUE;
    // $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_devoir_audio.sql');
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    // DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // report des valeurs des champs [prof_id] et [devoir_partage] de la table [sacoche_devoir] dans la table de jointure [ sacoche_jointure_devoir_droit | sacoche_jointure_devoir_prof ]
    $DB_SQL = 'SELECT devoir_id,prof_id,devoir_partage FROM sacoche_devoir';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    $DB_SQL = 'INSERT INTO '.$table_nom.' (devoir_id,prof_id,jointure_droit) VALUES(:devoir_id,:prof_id,:jointure_droit)';
    foreach($DB_TAB as $DB_ROW)
    {
      if($DB_ROW['devoir_partage'])
      {
        $tab_prof_id = explode( ',' , substr($DB_ROW['devoir_partage'],1,-1) );
        foreach($tab_prof_id as $prof_id)
        {
          if( $prof_id != $DB_ROW['prof_id'] )
          {
            $DB_VAR = array(
              ':devoir_id'      => $DB_ROW['devoir_id'],
              ':prof_id'        => $prof_id,
              ':jointure_droit' => 'saisir',
            );
            DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
          }
        }
      }
    }
    // renommage du champ [prof_id] de la table [sacoche_devoir] en [proprio_id]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE prof_id proprio_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir DROP INDEX prof_id , ADD INDEX proprio_id(proprio_id)' );
    // suppression du champ [devoir_partage] de la table [sacoche_devoir] (utilisation de la table de jointure [sacoche_jointure_devoir_droit] à la place)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir DROP devoir_partage' );
    // ajout du champ [devoir_eleves_ordre] à la table [sacoche_devoir]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_eleves_ordre ENUM("alpha","classe") COLLATE utf8_unicode_ci NOT NULL DEFAULT "alpha" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-11-01 => 2014-11-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-11-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-11-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // table [sacoche_jointure_devoir_droit] renommée en [sacoche_jointure_devoir_prof]
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_jointure_devoir_droit"');
    if(!empty($DB_TAB))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'RENAME TABLE sacoche_jointure_devoir_droit TO sacoche_jointure_devoir_prof' );
    }
    // nouvelle table [sacoche_jointure_devoir_eleve] en remplacement éventuel de [sacoche_jointure_devoir_audio]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_devoir_audio' );
    $reload_sacoche_jointure_devoir_eleve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_devoir_eleve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-11-09 => 2014-11-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-11-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-11-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout du champ [user_genre] à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_genre ENUM("I","M","F") COLLATE utf8_unicode_ci NOT NULL DEFAULT "I" COMMENT "Indéterminé / Masculin / Féminin" AFTER user_profil_sigle ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-11-18 => 2014-11-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-11-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-11-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Correction de coquilles détectées sur la table sacoche_matiere.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="TTERR" WHERE matiere_id=4059' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="POL8", matiere_nom="Littérature étrangère en polonais" WHERE matiere_id=397' );
    // Intégration de langues vivantes régionales ou spécifiques comme nouvelles matières.
    if(empty($reload_sacoche_matiere_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 93, 4, "Langues vivantes régionales ou spécifiques")' );
    }
    if(empty($reload_sacoche_matiere))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9307, 0, 0,  93, 0, 255, "GRE", "Grec moderne")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9308, 0, 0,  93, 0, 255, "HEB", "Hébreu")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9315, 0, 0,  93, 0, 255, "ARM", "Arménien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9316, 0, 0,  93, 0, 255, "AMH", "Amharique")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9317, 0, 0,  93, 0, 255, "ARD", "Arabe dialectal")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9318, 0, 0,  93, 0, 255, "BER", "Berbère")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9319, 0, 0,  93, 0, 255, "BUL", "Bulgare")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9320, 0, 0,  93, 0, 255, "CAM", "Cambodgien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9321, 0, 0,  93, 0, 255, "VIE", "Vietnamien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9322, 0, 0,  93, 0, 255, "FIN", "Finnois")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9323, 0, 0,  93, 0, 255, "HON", "Hongrois")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9324, 0, 0,  93, 0, 255, "ISL", "Islandais")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9325, 0, 0,  93, 0, 255, "NOR", "Norvégien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9326, 0, 0,  93, 0, 255, "MLG", "Malgache")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9327, 0, 0,  93, 0, 255, "ROU", "Roumain")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9328, 0, 0,  93, 0, 255, "TCH", "Tchèque")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9330, 0, 0,  93, 0, 255, "PER", "Persan")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9331, 0, 0,  93, 0, 255, "TUR", "Turc")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9332, 0, 0,  93, 0, 255, "LAO", "Laotien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9333, 0, 0,  93, 0, 255, "SUE", "Suédois")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9334, 0, 0,  93, 0, 255, "AME", "Américain")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9335, 0, 0,  93, 0, 255, "ALB", "Albanais")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9336, 0, 0,  93, 0, 255, "SER", "Serbe")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9337, 0, 0,  93, 0, 255, "CRO", "Croate")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9338, 0, 0,  93, 0, 255, "BAM", "Bambara")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9339, 0, 0,  93, 0, 255, "COE", "Coréen")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9340, 0, 0,  93, 0, 255, "HAO", "Haoussa")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9341, 0, 0,  93, 0, 255, "HIN", "Hindi")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9342, 0, 0,  93, 0, 255, "INM", "Indonésien-malaysien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9343, 0, 0,  93, 0, 255, "MAC", "Macédonien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9344, 0, 0,  93, 0, 255, "PEU", "Peuhl")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9345, 0, 0,  93, 0, 255, "SLQ", "Slovaque")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9346, 0, 0,  93, 0, 255, "SLN", "Slovène")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9347, 0, 0,  93, 0, 255, "SWA", "Swahili")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9348, 0, 0,  93, 0, 255, "TAM", "Tamoul")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9350, 0, 0,  93, 0, 255, "AUV", "Auvergnat")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9351, 0, 0,  93, 0, 255, "BAS", "Basque")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9352, 0, 0,  93, 0, 255, "BRE", "Breton")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9353, 0, 0,  93, 0, 255, "CAT", "Catalan")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9354, 0, 0,  93, 0, 255, "COR", "Corse")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9355, 0, 0,  93, 0, 255, "GAL", "Gallo")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9356, 0, 0,  93, 0, 255, "GAS", "Gascon")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9357, 0, 0,  93, 0, 255, "LAN", "Languedocien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9358, 0, 0,  93, 0, 255, "OCC", "Langue occitane")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9359, 0, 0,  93, 0, 255, "LRA", "Langues régionales d\'alsace")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9360, 0, 0,  93, 0, 255, "LIM", "Limousin")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9361, 0, 0,  93, 0, 255, "NIS", "Nissart")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9362, 0, 0,  93, 0, 255, "PRV", "Provençal")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9363, 0, 0,  93, 0, 255, "TAH", "Tahitien")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9364, 0, 0,  93, 0, 255, "VAL", "Vivaro-alpin")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9367, 0, 0,  93, 0, 255, "MOS", "Langues régionales des pays mosellans")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9368, 0, 0,  93, 0, 255, "MEL", "Langues mélanésiennes")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9369, 0, 0,  93, 0, 255, "LMJ", "Mélanésien ajie")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9370, 0, 0,  93, 0, 255, "LMR", "Mélanésien drehu")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9371, 0, 0,  93, 0, 255, "LMN", "Mélanésien nengone")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9372, 0, 0,  93, 0, 255, "LMD", "Mélanésien paici")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (9373, 0, 0,  93, 0, 255, "CRE", "Créole")' );
    }
    // Si une matière similaire spécifique est trouvée, alors la convertir...
    $tab_convert = array();
    // 1ère recherche sur la référence
    $DB_SQL = 'SELECT matiere_ref , ';
    $DB_SQL.= 'CONVERT( GROUP_CONCAT(matiere_id SEPARATOR "$") , CHAR ) AS liste_matiere_id , ';
    $DB_SQL.= 'GROUP_CONCAT(matiere_nom SEPARATOR "$") AS liste_matiere_nom , ';
    $DB_SQL.= 'COUNT(*) AS nombre ';
    $DB_SQL.= 'FROM sacoche_matiere ';
    $DB_SQL.= 'GROUP BY matiere_ref ';
    $DB_SQL.= 'HAVING nombre=2 ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        list($id1,$id2) = explode('$',$DB_ROW['liste_matiere_id']);
        if( ($id1>9300) && ($id1<9400) && ($id2>ID_MATIERE_PARTAGEE_MAX) )
        {
          $tab_convert[$id2] = $id1 ;
        }
        else if( ($id2>9300) && ($id2<9400) && ($id1>ID_MATIERE_PARTAGEE_MAX) )
        {
          $tab_convert[$id1] = $id2 ;
        }
      }
    }
    // 2ème recherche sur le nom
    $DB_SQL = 'SELECT matiere_nom , ';
    $DB_SQL.= 'CONVERT( GROUP_CONCAT(matiere_id SEPARATOR "$") , CHAR ) AS liste_matiere_id , ';
    $DB_SQL.= 'GROUP_CONCAT(matiere_ref SEPARATOR "$") AS liste_matiere_ref , ';
    $DB_SQL.= 'COUNT(*) AS nombre ';
    $DB_SQL.= 'FROM sacoche_matiere ';
    $DB_SQL.= 'GROUP BY matiere_nom ';
    $DB_SQL.= 'HAVING nombre=2 ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        list($id1,$id2) = explode('$',$DB_ROW['liste_matiere_id']);
        if( ($id1>9300) && ($id1<9400) && ($id2>ID_MATIERE_PARTAGEE_MAX) && !isset($tab_convert[$id2]) )
        {
          $tab_convert[$id2] = $id1 ;
        }
        else if( ($id2>9300) && ($id2<9400) && ($id1>ID_MATIERE_PARTAGEE_MAX) && !isset($tab_convert[$id1]) )
        {
          $tab_convert[$id1] = $id2 ;
        }
      }
    }
    // On lance la conversion
    if(!empty($tab_convert))
    {
      foreach($tab_convert as $id_avant => $id_apres)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_active=1 WHERE matiere_id='.$id_apres );
        DB_STRUCTURE_MATIERE::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
        SACocheLog::ajouter('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
        DB_STRUCTURE_MATIERE::DB_supprimer_matiere_specifique($id_avant);
        SACocheLog::ajouter('Suppression d\'une matière spécifique (n°'.$id_avant.').');
        SACocheLog::ajouter('Suppression des référentiels associés (matière '.$id_avant.').');
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-11-29 => 2014-12-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-11-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-12-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout d'un champ à la table sacoche_demande
    if(empty($reload_sacoche_demande))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande ADD demande_doc VARCHAR( 255 ) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-12-12 => 2014-12-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-12-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-12-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_bulletin_appreciation_generale_longueur" WHERE parametre_nom="officiel_bulletin_appreciation_generale" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_bulletin_appreciation_rubrique_longueur" WHERE parametre_nom="officiel_bulletin_appreciation_rubrique" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_releve_appreciation_generale_longueur" WHERE parametre_nom="officiel_releve_appreciation_generale" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_releve_appreciation_rubrique_longueur" WHERE parametre_nom="officiel_releve_appreciation_rubrique" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_socle_appreciation_generale_longueur" WHERE parametre_nom="officiel_socle_appreciation_generale" ' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_url" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_moyenne_exception_matieres" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_generale_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_generale_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_rubrique_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_appreciation_rubrique_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_generale_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_generale_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_rubrique_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_appreciation_rubrique_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_generale_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_generale_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_rubrique_report" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_appreciation_rubrique_modele" , "" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_bulletin_fond" , "gris" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_fond"   , "gris" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_socle_fond"    , "gris" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-12-19 => 2014-12-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-12-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-12-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de paramètre (oublié à la MAJ d'avant...)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="officiel_socle_appreciation_rubrique_longueur" WHERE parametre_nom="officiel_socle_appreciation_rubrique" ' );
    // récupération de paramètre
    $image_style = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="note_image_style" ' );
    // ajout de paramètres
    $tab_notes_info = array
    (
      'Lomer'        => array( 'RR' =>'disque-double_rouge'         , 'R' =>'disque_rouge'                , 'V' =>'disque_vert-fonce'             , 'VV' =>'disque-double_vert-fonce' ),
      'Albert'       => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_orange'               , 'V' =>'disque_vert-fonce'             , 'VV' =>'disque-double_vert-fonce' ),
      'Alienor'      => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_orange'               , 'V' =>'disque_vert-fonce'             , 'VV' =>'etoile_jaune' ),
      'Aubrac'       => array( 'RR' =>'disque_jaune'                , 'R' =>'disque_vert-fonce'           , 'V' =>'disque_bleu-fonce'             , 'VV' =>'disque_noir' ),
      'Berthelot'    => array( 'RR' =>'disque_noir'                 , 'R' =>'disque_rouge'                , 'V' =>'disque_bleu-fonce'             , 'VV' =>'disque_vert-fonce' ),
      'Blaye'        => array( 'RR' =>'carre_rouge'                 , 'R' =>'carre_orange'                , 'V' =>'carre_jaune'                   , 'VV' =>'carre_vert' ),
      'Brassens'     => array( 'RR' =>'symbole-couleur_moins'       , 'R' =>'symbole-couleur_moyen-moins' , 'V' =>'symbole-couleur_moyen-plus'    , 'VV' =>'symbole-couleur_plus' ),
      'Brel'         => array( 'RR' =>'rectangle-texte_rouge-NR'    , 'R' =>'rectangle-texte_orange_PR'   , 'V' =>'rectangle-texte_vert_R'        , 'VV' =>'rectangle-texte_violet-M' ),
      'CitesUnies'   => array( 'RR' =>'carre_rouge'                 , 'R' =>'carre_orange'                , 'V' =>'carre_vert'                    , 'VV' =>'carre_jaune' ),
      'Courbet'      => array( 'RR' =>'disque_bleu-fonce'           , 'R' =>'disque_jaune'                , 'V' =>'disque_orange'                 , 'VV' =>'disque_rouge' ),
      'Descartes'    => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_orange'               , 'V' =>'disque_vert-fonce'             , 'VV' =>'etoile_violet' ),
      'Deutsch'      => array( 'RR' =>'rectangle_rose'              , 'R' =>'rectangle_orange-fonce'      , 'V' =>'rectangle_vert-fonce'          , 'VV' =>'rectangle_bleu-fonce' ),
      'Europe'       => array( 'RR' =>'texte_lettre-D'              , 'R' =>'texte_lettre-C'              , 'V' =>'texte_lettre-B'                , 'VV' =>'texte_lettre-A' ),
      'Indonesie'    => array( 'RR' =>'carre_rouge'                 , 'R' =>'carre_orange'                , 'V' =>'carre_vert'                    , 'VV' =>'carre-double_vert' ),
      'JeanZay'      => array( 'RR' =>'rectangle_bleu-clair'        , 'R' =>'rectangle_jaune'             , 'V' =>'rectangle_vert-clair'          , 'VV' =>'rectangle_vert-fonce' ),
      'Koeberle'     => array( 'RR' =>'texte-couleur_rouge-NM'      , 'R' =>'texte-couleur_orange-MI'     , 'V' =>'texte-couleur_vert-MS'         , 'VV' =>'texte-couleur_bleu-EX' ),
      'LaFontaine'   => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_jaune'                , 'V' =>'disque_vert-clair'             , 'VV' =>'disque_vert-fonce' ),
      'Langevin'     => array( 'RR' =>'texte_niveau-1'              , 'R' =>'texte_niveau-2'              , 'V' =>'texte_niveau-3'                , 'VV' =>'texte_niveau-4' ),
      'Leon'         => array( 'RR' =>'disque_noir'                 , 'R' =>'disque_rouge'                , 'V' =>'disque_orange'                 , 'VV' =>'disque_vert-fonce' ),
      'LomerBiseau'  => array( 'RR' =>'triangle-rectangle_2-rouge'  , 'R' =>'triangle-rectangle_rouge'    , 'V' =>'triangle-rectangle_vert-clair' , 'VV' =>'triangle-rectangle_2-vert-clair' ),
      'Luys'         => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_orange'               , 'V' =>'disque_vert-fonce'             , 'VV' =>'disque_bleu-fonce' ),
      'Marly'        => array( 'RR' =>'remplissage-triangle_0'      , 'R' =>'remplissage-triangle_1'      , 'V' =>'remplissage-triangle_2'        , 'VV' =>'remplissage-triangle_3' ),
      'MendesFrance' => array( 'RR' =>'disque_rouge'                , 'R' =>'disque_bleu-fonce'           , 'V' =>'disque_vert-fonce'             , 'VV' =>'etoile_jaune' ),
      'Mistral'      => array( 'RR' =>'disque_blanc'                , 'R' =>'disque_rouge'                , 'V' =>'disque_orange'                 , 'VV' =>'disque_vert-fonce' ),
      'Padawan'      => array( 'RR' =>'disque-cercle-double_orange' , 'R' =>'disque-cercle_orange'        , 'V' =>'etoile_vert'                   , 'VV' =>'etoile-double_vert' ),
      'Prevert'      => array( 'RR' =>'symbole_moins'               , 'R' =>'symbole_moyen'               , 'V' =>'symbole_plus'                  , 'VV' =>'symbole_plus-plus' ),
      'Reval'        => array( 'RR' =>'rectangle_rouge'             , 'R' =>'rectangle_orange-clair'      , 'V' =>'rectangle_bleu-fonce'          , 'VV' =>'rectangle_vert-fonce' ),
      'SaintExupery' => array( 'RR' =>'rectangle-texte_rouge-NA'    , 'R' =>'rectangle-texte_orange-ECA'  , 'V' =>'rectangle-texte_jaune-AR'      , 'VV' =>'rectangle-texte_vert-A' ),
      'Salengro'     => array( 'RR' =>'carre-etire_rouge'           , 'R' =>'carre-etire_orange'          , 'V' =>'carre-etire_bleu-fonce'        , 'VV' =>'carre-etire_vert' ),
      'Sand'         => array( 'RR' =>'rectangle_rose'              , 'R' =>'rectangle_jaune'             , 'V' =>'rectangle_vert-fonce'          , 'VV' =>'rectangle_bleu-fonce' ),
      'Sun'          => array( 'RR' =>'old-style_0-cercle-vide'     , 'R' =>'old-style_1-cercle-point'    , 'V' =>'old-style_2-cercle-plein'      , 'VV' =>'old-style_3-etoile' ),
      'Tillion'      => array( 'RR' =>'rectangle_jaune'             , 'R' =>'rectangle_vert-fonce'        , 'V' =>'rectangle_bleu-fonce'          , 'VV' =>'rectangle_noir' ),
      'Trappes'      => array( 'RR' =>'texte-couleur_noir-XX'       , 'R' =>'texte-couleur_noir-X'        , 'V' =>'texte-couleur_vert-V'          , 'VV' =>'texte-couleur_vert-VV' ),
      'Ulis'         => array( 'RR' =>'remplissage-cercle_0'        , 'R' =>'remplissage-cercle_1'        , 'V' =>'remplissage-cercle_2'          , 'VV' =>'remplissage-cercle_3' ),
      'Verne'        => array( 'RR' =>'texte_chiffre-4'             , 'R' =>'texte_chiffre-3'             , 'V' =>'texte_chiffre-2'               , 'VV' =>'texte_chiffre-1' ),
      'Voltaire'     => array( 'RR' =>'rectangle_rouge'             , 'R' =>'rectangle_orange-clair'      , 'V' =>'rectangle_vert-fonce'          , 'VV' =>'rectangle_jaune' ),
    );
    foreach($tab_notes_info[$image_style] as $note_code => $note_texte)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "note_image_'.$note_code.'" , "'.$note_texte.'" )' );
    }
    // suppression de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="note_image_style" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-12-22 => 2014-12-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-12-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-12-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout du champ [user_langue] à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_langue VARCHAR(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER user_password ' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_langue" , "fr_FR" )' );
  }
}

?>
