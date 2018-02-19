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
// MAJ 2010-11-28 => 2011-01-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-01-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajouter la matière Sciences
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 43, 1, 0, 255, "SCIEN", "Sciences")' );
    // convertir la matière "Vie sociale et professionnelle" en "Prévention-Santé-Environnement" (http://media.education.gouv.fr/file/special_2/25/5/prevention_sante_environnement_44255.pdf)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref="PSE", matiere_nom="Prévention-Santé-Environnement" WHERE matiere_id=40' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-01-06 => 2011-03-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-01-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-03-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // changement des dénominations par défaut associées aux codes de couleur
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Très insuffisant."  WHERE parametre_valeur="A complètement échoué."' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Insuffisant."       WHERE parametre_valeur="A plutôt échoué."' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Satisfaisant."      WHERE parametre_valeur="A plutôt réussi."' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Très satisfaisant." WHERE parametre_valeur="A complètement réussi."' );
    // ajout de la possibilité de personnaliser suivant les matières le nombre maximal de demandes autorisées
    $valeur = (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_eleve_demandes"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="droit_eleve_demandes"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ADD matiere_nb_demandes TINYINT UNSIGNED NOT NULL DEFAULT "0" AFTER matiere_transversal ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nb_demandes='.$valeur );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-03-08 => 2011-03-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-03-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-03-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correction de bug : champ défini à tinyint(1) au lieu de tinyint(3) pour les nouvelles structures.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere CHANGE matiere_nb_demandes matiere_nb_demandes TINYINT(3) UNSIGNED NOT NULL DEFAULT "0" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-03-16 => 2011-04-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-03-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-04-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout des matières correspondant aux piliers, utiles pour organiser les référentiels à l'école primaire
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 90, 1, 0, 0, 255,  "P1", "1 Maîtrise de la langue française")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 91, 1, 0, 0, 255,  "P2", "2 Pratique d\'une langue vivante étrangère")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 92, 1, 0, 0, 255, "P3A", "3 Principaux éléments de mathématiques")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 93, 1, 0, 0, 255, "P3B", "3 Culture scientifique et technologique")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 94, 1, 0, 0, 255,  "P4", "4 Maîtrise des TICE")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 95, 1, 0, 0, 255,  "P5", "5 Culture humaniste")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 96, 1, 0, 0, 255,  "P6", "6 Compétences sociales et civiques")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES ( 97, 1, 0, 0, 255,  "P7", "7 Autonomie et l\'initiative")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-04-04 => 2011-05-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-04-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-05-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 champs pour paramétrer la date à partir de laquelle les élèves ont accès aux notes d'une évaluation
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_visible_date DATE NOT NULL DEFAULT "0000-00-00" AFTER devoir_info' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie ADD saisie_visible_date DATE NOT NULL DEFAULT "0000-00-00" AFTER saisie_info' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_visible_date=devoir_date' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_visible_date=saisie_date' );
    // suppression de cette clef qui n'accélère probablement rien et qui prend de la place (grosse table)
    // remarque : cette modification de table n'est pas anodine et prends un certain temps si beaucoup de notes sont saisies
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie DROP INDEX saisie_key' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-05-10 => 2011-05-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-05-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-05-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // mise à jour d'un champ du socle
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Adopter des comportements favorables à sa santé et sa sécurité." WHERE entree_id=268' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-05-20 => 2011-05-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-05-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-05-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une entrée pour gérer les droits de validation
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_annulation_pilier" , "directeur,aucunprof")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-05-22 => 2011-05-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-05-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-05-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification/réorganisation de la table user pour gérer les champs de sconet eleve_id et individu_fonction
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_sconet_id MEDIUMINT UNSIGNED NOT NULL DEFAULT "0" COMMENT "ELEVE.ELEVE.ID pour un élève ; INDIVIDU_ID pour un prof" AFTER user_num_sconet' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_sconet_elenoet SMALLINT UNSIGNED NOT NULL DEFAULT "0" COMMENT "ELENOET pour un élève (entre 2000 et 5000 ; parfois appelé n° GEP avec un 0 devant)" AFTER user_sconet_id' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_sconet_id=user_num_sconet WHERE user_profil!="eleve"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_sconet_elenoet=user_num_sconet WHERE user_profil="eleve"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP user_num_sconet' );
    // ajout d'un champ dans la table user pour gérer le choix de la langue du socle
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_langue TINYINT(3) UNSIGNED NOT NULL DEFAULT "132" COMMENT "Langue choisie pour le socle." AFTER eleve_classe_id' );
    // ajout d'un champ dans la table user pour gérer la date de sortie (CNIL) ; pas de date par défaut possible dans la création du champ ou pour l'insertion d'une nouvelle ligne...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_statut_date DATE NOT NULL DEFAULT "0000-00-00" AFTER user_connexion_date' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_statut_date=NOW()' );
    // ajout de 2 niveaux "Première générale" et "Terminale générale"
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (  68, 0,  84,     "1", "2011....11.", "Première générale")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (  69, 0,  94,     "T", "2021....11.", "Terminale générale")' );
    }
    // Je renonce aux fonctions car la liste n'est pas claire + ça ne sert à rien sans le libellé court de la matière (qui ne correspond pas à ce qu'on trouve dans STS, il est dans Nomenclature.xml mais pas dans SACoche) + ça fait assez de modifs comme ça + ça pose problème pour le 1er degré + un prof peut avoir quitté l'établissement + on peut faire un export LPC sans spécifier qui a validé.
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_fonction ENUM( "", "ENS", "DIR", "EDU", "DOC", "COP", "ORI", "FIJ", "AED", "SUR" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "INDIVIDU.FONCTION dans le fichier Sconet-STS des personnels" AFTER user_reference' );
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_fonction="ENS" WHERE user_profil="professeur"' );
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_fonction="DIR" WHERE user_profil="directeur"' );
    // correctif : passage du champ pilier_id de SMALLINT en TINYINT
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_pilier CHANGE pilier_id pilier_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier CHANGE pilier_id pilier_id TINYINT(3) UNSIGNED NOT NULL DEFAULT "0" ' );
    // correctif : dénominations d'items
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Organisation et gestion de données : reconnaître des situations de proportionnalité, utiliser des pourcentages, des tableaux, des graphiques. Exploiter des données statistiques et aborder des situations simples de probabilité." WHERE entree_id=197' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Nombres et calculs : connaître et utiliser les nombres entiers, décimaux et fractionnaires. Mener à bien un calcul : mental, à la main, à la calculatrice, avec un ordinateur." WHERE entree_id=202' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Géométrie : connaître et représenter des figures géométriques et des objets de l’espace. Utiliser leurs propriétés." WHERE entree_id=205' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant du temps : les différentes périodes de l’histoire de l’humanité - Les grands traits de l’histoire (politique, sociale, économique, littéraire, artistique, culturelle) de la France et de l’Europe." WHERE entree_id=249' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant de la culture civique : Droits de l’Homme - Formes d’organisation politique, économique et sociale dans l’Union européenne - Place et rôle de l’État en France - Mondialisation - Développement durable." WHERE entree_id=285' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Respecter des comportements favorables à sa santé et sa sécurité." WHERE entree_id=268' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Respecter quelques notions juridiques de base." WHERE entree_id=269' );
    // modification des identifiants de compétence pour qu'ils correspondent à ceux de LPC (pas d'info sur le palier 1...)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_id=pilier_id+20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_id=pilier_id-31 WHERE pilier_id>31' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_id=pilier_id-16 WHERE pilier_id>23' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_id=pilier_id-5  WHERE pilier_id>20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET pilier_id=pilier_id+20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET pilier_id=pilier_id-31 WHERE pilier_id>31' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET pilier_id=pilier_id-16 WHERE pilier_id>23' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET pilier_id=pilier_id-5  WHERE pilier_id>20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_pilier SET pilier_id=pilier_id+20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_pilier SET pilier_id=pilier_id-31 WHERE pilier_id>31' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_pilier SET pilier_id=pilier_id-16 WHERE pilier_id>23' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_pilier SET pilier_id=pilier_id-5  WHERE pilier_id>20' );
    // modification des identifiants d'item pour qu'ils correspondent à ceux de LPC (pas d'info sur le palier 1...)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_id=entree_id+1000' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=entree_id+1000' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_entree SET entree_id=entree_id+1000' );
    $tab_conv_items = array(1=>3111,2=>3112,3=>3113,4=>3121,5=>3122,6=>3123,7=>3124,8=>3125,9=>3131,10=>3132,11=>3133,12=>3141,13=>3142,14=>3143,15=>3144,16=>3145,17=>3151,18=>3152,19=>3153,20=>3154,21=>3161,22=>3162,23=>3163,24=>3311,25=>3312,26=>3313,27=>3314,28=>3315,29=>3316,30=>3317,31=>3318,32=>3321,33=>3322,34=>3323,35=>3324,36=>3325,37=>3326,38=>3331,39=>3332,40=>3333,41=>3341,42=>3342,43=>3611,44=>3621,45=>3622,46=>3623,47=>2111,48=>2112,49=>2113,50=>2114,51=>2115,52=>2121,53=>2122,54=>2123,55=>2124,56=>2125,57=>2126,58=>2127,59=>2128,60=>2129,61=>21210,62=>2131,63=>2132,64=>2133,65=>2134,66=>2141,67=>2142,68=>2143,69=>2144,70=>2151,71=>2152,72=>2153,73=>2161,74=>2162,75=>2163,76=>2211,77=>2212,78=>2213,79=>2214,80=>2221,81=>2222,82=>2223,83=>2231,84=>2232,85=>2233,86=>2241,87=>2242,88=>2251,89=>2252,90=>2253,91=>2254,92=>2255,93=>2311,94=>2312,95=>2313,96=>2314,97=>2315,98=>2316,99=>2317,100=>2318,101=>2321,102=>2322,103=>2323,104=>2324,105=>2331,106=>2332,107=>2333,108=>2334,109=>2341,110=>2342,111=>2343,112=>2351,113=>2352,114=>2353,115=>2361,116=>2362,117=>2363,118=>2364,119=>2365,120=>2366,121=>2367,122=>2368,123=>2371,124=>2411,125=>2421,126=>2431,127=>2432,128=>2441,129=>2442,130=>2443,131=>2451,133=>2511,134=>2512,135=>2513,136=>2514,137=>2521,138=>2522,132=>2531,139=>2541,140=>2542,141=>2543,142=>2544,143=>2545,144=>2611,145=>2612,146=>2613,147=>2621,148=>2622,149=>2711,150=>2712,151=>2713,152=>2714,153=>2721,154=>2731,155=>2732,156=>2733,157=>111,282=>112,158=>113,159=>114,160=>115,163=>121,164=>122,166=>123,167=>124,283=>131,170=>132,171=>133,172=>134,177=>211,178=>212,179=>213,180=>214,181=>221,182=>222,183=>231,184=>232,185=>233,186=>241,187=>242,188=>251,189=>252,190=>253,191=>254,192=>255,193=>311,194=>312,195=>313,196=>314,197=>321,202=>322,205=>323,208=>324,210=>331,211=>332,212=>333,213=>334,214=>335,215=>341,218=>411,220=>412,216=>413,222=>421,223=>422,225=>423,228=>424,229=>431,235=>432,231=>433,234=>434,236=>441,239=>442,238=>443,243=>453,242=>454,284=>455,245=>511,249=>512,251=>513,252=>514,285=>515,286=>521,256=>522,287=>523,259=>524,255=>531,293=>532,254=>533,288=>541,289=>542,290=>543,291=>544,260=>611,261=>612,262=>613,263=>614,264=>615,265=>616,266=>621,267=>622,268=>623,269=>624,270=>625,272=>711,273=>712,292=>713,274=>721,275=>722,277=>723,276=>724,278=>731,279=>732,280=>733,281=>734);
    foreach($tab_conv_items as $old => $new)
    {
      $old+=1000;
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_id='.$new.' WHERE entree_id='.$old.'' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id='.$new.' WHERE entree_id='.$old );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_entree SET entree_id='.$new.' WHERE entree_id='.$old );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-05-31 => 2011-06-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-05-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-06-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // champ oublié dans la création de table user depuis la maj précédente
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SHOW CREATE TABLE sacoche_user' );
    if(!strpos($DB_ROW['Create Table'],'eleve_langue'))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_langue TINYINT(3) UNSIGNED NOT NULL DEFAULT "132" COMMENT "Langue choisie pour le socle." AFTER eleve_classe_id' );
    }
    // ajout de 2 tables pour les parents
    // Les supprimer si elles existent : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), ces anciennes tables éventuellement existantes ne seraient pas réinitialisées.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_parent_eleve' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_jointure_parent_eleve ( parent_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, eleve_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, resp_legal_num ENUM("1","2") COLLATE utf8_unicode_ci NOT NULL DEFAULT "1", UNIQUE KEY parent_eleve_key (parent_id,eleve_id), KEY parent_id (parent_id), KEY eleve_id (eleve_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_parent_adresse' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_parent_adresse ( parent_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, adresse_ligne1 VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", adresse_ligne2 VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", adresse_ligne3 VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", adresse_ligne4 VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", adresse_postal_code MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0, adresse_postal_libelle VARCHAR(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", adresse_pays_nom VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY (parent_id) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    // ajout du profil parent
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_profil user_profil ENUM("eleve","parent","professeur","directeur","administrateur") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" ' );
    // ajout des droits parents
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"eleve","parent,eleve") WHERE parametre_nom IN("droit_modifier_mdp","droit_voir_referentiels","droit_voir_score_bilan","droit_voir_algorithme") ' );
    // modification de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    $eleve_bilans = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_eleve_bilans"' );
    $eleve_socle  = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_eleve_socle" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="droit_eleve_bilans"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="droit_eleve_socle" ' );
    $droit_bilan_moyenne_score      = (strpos($eleve_bilans,'BilanMoyenneScore')     !==FALSE) ? 'parent,eleve' : '' ;
    $droit_bilan_pourcentage_acquis = (strpos($eleve_bilans,'BilanPourcentageAcquis')!==FALSE) ? 'parent,eleve' : '' ;
    $droit_bilan_note_sur_vingt     = (strpos($eleve_bilans,'BilanNoteSurVingt')     !==FALSE) ? 'parent,eleve' : '' ;
    $droit_socle_acces              = (strpos($eleve_socle ,'SocleAcces')            !==FALSE) ? 'parent,eleve' : '' ;
    $droit_socle_pourcentage_acquis = (strpos($eleve_socle ,'SoclePourcentageAcquis')!==FALSE) ? 'parent,eleve' : '' ;
    $droit_socle_etat_validation    = (strpos($eleve_socle ,'SocleEtatValidation')   !==FALSE) ? 'parent,eleve' : '' ;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_bilan_moyenne_score"      , "'.$droit_bilan_moyenne_score.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_bilan_pourcentage_acquis" , "'.$droit_bilan_pourcentage_acquis.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_bilan_note_sur_vingt"     , "'.$droit_bilan_note_sur_vingt.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_acces"              , "'.$droit_socle_acces.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_pourcentage_acquis" , "'.$droit_socle_pourcentage_acquis.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_etat_validation"    , "'.$droit_socle_etat_validation.'")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-06-05 => 2011-06-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-06-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-06-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de la valeur par défaut pour eleve_langue
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE eleve_langue eleve_langue TINYINT(3) UNSIGNED NOT NULL DEFAULT "100" COMMENT "Langue choisie pour le socle." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 5000');
    $listing_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT GROUP_CONCAT(DISTINCT user_id SEPARATOR ",") AS listing_id FROM sacoche_user LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id WHERE niveau_id NOT IN(35,36,44,51)' );
    if($listing_id)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET eleve_langue=100 WHERE user_id IN('.$listing_id.')' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-06-06 => 2011-06-08
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-06-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-06-08';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correctif de la modification des identifiants d'item du 2011-05-31
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=0 WHERE entree_id=1000' );
    // ajout de 2 paramètres
    $valeur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="modele_eleve"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("modele_parent" , "'.$valeur.'")' );
    $valeur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="modele_professeur"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("modele_directeur" , "'.$valeur.'")' );
    // modification de sacoche_jointure_parent_eleve
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_parent_eleve CHANGE resp_legal_num resp_legal_num ENUM( "0", "1", "2" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "0" ' );
    // suppression des vignettes (sans rapport avec la base, mais à effectuer, à cause du changement de la couleur de fond)
    list($x,$y,$dossier) = (HEBERGEUR_INSTALLATION=='multi-structures') ? explode('_',SACOCHE_STRUCTURE_BD_NAME) : '0' ;
    FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_BADGE.$dossier , 0);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-06-08 => 2011-06-24
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-06-08')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-06-24';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de sacoche_jointure_parent_eleve
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_parent_eleve CHANGE resp_legal_num resp_legal_num TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_parent_eleve ADD resp_legal_envoi TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 ' );
    // type de la colonne section_id
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_section CHANGE section_id section_id TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_entree  CHANGE section_id section_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT 0 ' );
    // type des colonnes user_nom et user_prenom
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_nom    user_nom    VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_prenom user_prenom VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-06-24 => 2011-07-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-06-24')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-07-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de niveaux BTS et modification du champ correspondant
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre = 199 WHERE niveau_id = 4 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau CHANGE niveau_ref niveau_ref VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 121, 0, 161,  "1BTS1", "310.....11.", "BTS 1 an") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 122, 0, 162,  "1BTS2", "311.....21.", "BTS 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 123, 0, 163,  "2BTS2", "311.....22.", "BTS 2 ans, 2e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 124, 0, 164,  "1BTS3", "312.....31.", "BTS 3 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 125, 0, 165,  "2BTS3", "312.....32.", "BTS 3 ans, 2e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 126, 0, 166,  "3BTS3", "312.....33.", "BTS 3 ans, 3e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 131, 0, 171, "1BTS1A", "370.....11.", "BTS Agricole 1 an") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 132, 0, 172, "1BTS2A", "371.....21.", "BTS Agricole 2 ans, 1e année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 133, 0, 173, "2BTS2A", "371.....22.", "BTS Agricole 2 ans, 2e année") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-07-03 => 2011-07-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-07-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-07-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification des identifiants d'item pour qu'ils correspondent à ceux de LPC : infos enfin disponibles pour le palier 1 ! (RAS pour les identifiants des compétences)
    $tab_conv_items = array(1=>3111,2=>3112,3=>3113,4=>3121,5=>3122,6=>3123,7=>3124,8=>3125,9=>3131,10=>3132,11=>3133,12=>3141,13=>3142,14=>3143,15=>3144,16=>3145,17=>3151,18=>3152,19=>3153,20=>3154,21=>3161,22=>3162,23=>3163,24=>3311,25=>3312,26=>3313,27=>3314,28=>3315,29=>3316,30=>3317,31=>3318,32=>3321,33=>3322,34=>3323,35=>3324,36=>3325,37=>3326,38=>3331,39=>3332,40=>3333,41=>3341,42=>3342,43=>3611,44=>3621,45=>3622,46=>3623);
    foreach($tab_conv_items as $new => $old)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_id='.$new.' WHERE entree_id='.$old.'' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id='.$new.' WHERE entree_id='.$old );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_entree SET entree_id='.$new.' WHERE entree_id='.$old );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-07-18 => 2011-08-02
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-07-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-08-02';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 3 paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("gepi_url" , "")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("gepi_rne" , "")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("gepi_certificat_empreinte" , "")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-08-02 => 2011-08-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-08-02')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-08-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression de fichiers temporaires déplacés (sans rapport avec la base, mais à effectuer)
    FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_COOKIE,0);
    FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_RSS,0);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-08-18 => 2011-08-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-08-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-08-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification de sacoche_jointure_parent_eleve
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_parent_eleve DROP resp_legal_envoi ' );
    // retrait des contacts
    $DB_SQL = 'DELETE sacoche_jointure_parent_eleve ';
    $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
    $DB_SQL.= 'WHERE resp_legal_num>2 ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    $DB_SQL = 'DELETE sacoche_parent_adresse ';
    $DB_SQL.= 'FROM sacoche_parent_adresse ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve USING (parent_id) ';
    $DB_SQL.= 'WHERE eleve_id IS NULL ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    $DB_SQL = 'DELETE sacoche_user ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON sacoche_user.user_id=sacoche_jointure_parent_eleve.parent_id ';
    $DB_SQL.= 'WHERE user_profil="parent" AND parent_id IS NULL ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-08-20 => 2011-10-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-08-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-10-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Le nom de l'évaluation n'était plus associé aux notes mémorisées...
    $DB_SQL = 'SELECT sacoche_saisie.prof_id,eleve_id,devoir_id,item_id,devoir_info,user_nom,user_prenom ';
    $DB_SQL.= 'FROM sacoche_saisie ';
    $DB_SQL.= 'INNER JOIN sacoche_devoir USING (devoir_id) ';
    $DB_SQL.= 'INNER JOIN sacoche_user ON sacoche_saisie.prof_id = sacoche_user.user_id ';
    $DB_SQL.= 'WHERE saisie_info LIKE " (%"';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    $DB_SQL = 'UPDATE sacoche_saisie SET saisie_info=:saisie_info WHERE prof_id=:prof_id AND eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id';
    foreach($DB_TAB as $DB_ROW)
    {
      $saisie_info = $DB_ROW['devoir_info'].' ('.To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE).')';
      $DB_VAR = array(
        ':prof_id'     => $DB_ROW['prof_id'],
        ':eleve_id'    => $DB_ROW['eleve_id'],
        ':devoir_id'   => $DB_ROW['devoir_id'],
        ':item_id'     => $DB_ROW['item_id'],
        ':saisie_info' => $saisie_info,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-10-01 => 2011-10-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-10-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-10-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression du champ groupe_prof_id pour un groupe de besoin ou un groupe d'évaluation (utilisation de la table de jointure existante à la place)
    $DB_SQL = 'SELECT groupe_id,groupe_prof_id FROM sacoche_groupe WHERE groupe_type IN ("besoin","eval")';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    $DB_SQL = 'INSERT IGNORE INTO sacoche_jointure_user_groupe (user_id,groupe_id,jointure_pp) VALUES(:user_id,:groupe_id,1) ';
    foreach($DB_TAB as $DB_ROW)
    {
      $DB_VAR = array(
        ':user_id'   => $DB_ROW['groupe_prof_id'],
        ':groupe_id' => $DB_ROW['groupe_id'],
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe DROP groupe_prof_id' );
    // ajout d'un champ pour lister avec qui on partage un devoir
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir ADD devoir_partage TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // corrections d'entrées tronquées à cause d'un fichier SQL pas en UTF8
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Très insuffisant." WHERE parametre_nom="note_legende_RR" AND parametre_valeur="Tr" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="Très satisfaisant." WHERE parametre_nom="note_legende_VV" AND parametre_valeur="Tr" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-10-09 => 2011-10-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-10-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-10-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 index
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie ADD UNIQUE saisie_key ( eleve_id , devoir_id , item_id )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX ( user_id_gepi )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-10-23 => 2011-11-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-10-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-11-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // depuis le 05/06/2010 les nouvelles bases voyaient le champ "sacoche_parametre" défini à VARCHAR(25) au lieu de VARCHAR(30)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_bilan_pourcentage_acquis" WHERE parametre_nom="droit_bilan_pourcentage_a"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_bilan_note_sur_vingt"     WHERE parametre_nom="droit_bilan_note_sur_ving"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_socle_pourcentage_acquis" WHERE parametre_nom="droit_socle_pourcentage_a"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_socle_etat_validation"    WHERE parametre_nom="droit_socle_etat_validati"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-11-12 => 2011-11-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-11-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-11-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_grilles_items" , "directeur,professeur,parent,eleve")' );
    // ajout d'un niveau cycle
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_ordre=niveau_ordre+3 WHERE niveau_id IN(14,15)' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (5,1,5,"P0","","Cycle 1 (PS-GS)")' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-11-20 => 2011-12-24
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-11-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-12-24';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_referentiel" , "profcoordonnateur")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_ressource" , "professeur")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2011-12-24 => 2011-12-30
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2011-12-24')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2011-12-30';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // maj liens
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D10","http://sacoche.sesamath.net/__ress_html/2354/117.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D11","http://sacoche.sesamath.net/__ress_html/2354/118.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D12","http://sacoche.sesamath.net/__ress_html/2354/119.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D20","http://sacoche.sesamath.net/__ress_html/2354/120.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D21","http://sacoche.sesamath.net/__ress_html/2354/121.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6D22","http://sacoche.sesamath.net/__ress_html/2354/122.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N10","http://sacoche.sesamath.net/__ress_html/2354/123.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N11","http://sacoche.sesamath.net/__ress_html/2354/124.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N12","http://sacoche.sesamath.net/__ress_html/2354/125.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N13","http://sacoche.sesamath.net/__ress_html/2354/126.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N14","http://sacoche.sesamath.net/__ress_html/2354/127.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N20","http://sacoche.sesamath.net/__ress_html/2354/128.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N21","http://sacoche.sesamath.net/__ress_html/2354/129.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N22","http://sacoche.sesamath.net/__ress_html/2354/130.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N23","http://sacoche.sesamath.net/__ress_html/2354/131.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N24","http://sacoche.sesamath.net/__ress_html/2354/132.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N25","http://sacoche.sesamath.net/__ress_html/2354/133.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N26","http://sacoche.sesamath.net/__ress_html/2354/134.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N27","http://sacoche.sesamath.net/__ress_html/2354/135.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N28","http://sacoche.sesamath.net/__ress_html/2354/136.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N29","http://sacoche.sesamath.net/__ress_html/2354/137.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N30","http://sacoche.sesamath.net/__ress_html/2354/138.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N31","http://sacoche.sesamath.net/__ress_html/2354/139.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N32","http://sacoche.sesamath.net/__ress_html/2354/140.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N33","http://sacoche.sesamath.net/__ress_html/2354/141.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6N34","http://sacoche.sesamath.net/__ress_html/2354/142.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G10","http://sacoche.sesamath.net/__ress_html/2354/143.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G11","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G12","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G13","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G20","http://sacoche.sesamath.net/__ress_html/2354/147.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G21","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G22","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G23","http://sacoche.sesamath.net/__ress_html/2354/150.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G30","http://sacoche.sesamath.net/__ress_html/2354/151.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G31","http://sacoche.sesamath.net/__ress_html/2354/152.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G32","http://sacoche.sesamath.net/__ress_html/2354/153.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G33","http://sacoche.sesamath.net/__ress_html/2354/154.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G34","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G40","http://sacoche.sesamath.net/__ress_html/2354/155.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G41","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G42","http://sacoche.sesamath.net/__ress_html/2354/157.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G43","http://sacoche.sesamath.net/__ress_html/2354/158.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G50","http://sacoche.sesamath.net/__ress_html/2354/159.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G51","http://sacoche.sesamath.net/__ress_html/2354/160.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G52","http://sacoche.sesamath.net/__ress_html/2354/161.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G53","http://sacoche.sesamath.net/__ress_html/2354/162.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G54","http://sacoche.sesamath.net/__ress_html/2354/163.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G55","http://sacoche.sesamath.net/__ress_html/2354/164.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G60","http://sacoche.sesamath.net/__ress_html/2354/165.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G61","http://sacoche.sesamath.net/__ress_html/2354/166.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6G62","http://sacoche.sesamath.net/__ress_html/2354/167.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M10","http://sacoche.sesamath.net/__ress_html/2354/168.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M11","http://sacoche.sesamath.net/__ress_html/2354/169.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M12","http://sacoche.sesamath.net/__ress_html/2354/170.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M13","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M20","http://sacoche.sesamath.net/__ress_html/2354/172.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M21","http://sacoche.sesamath.net/__ress_html/2354/173.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M22","http://sacoche.sesamath.net/__ress_html/2354/174.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M23","http://sacoche.sesamath.net/__ress_html/2354/175.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M30","http://sacoche.sesamath.net/__ress_html/2354/176.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M31","http://sacoche.sesamath.net/__ress_html/2354/177.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M32","http://sacoche.sesamath.net/__ress_html/2354/178.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M33","http://sacoche.sesamath.net/__ress_html/2354/179.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M34","http://sacoche.sesamath.net/__ress_html/2354/180.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M35","")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M36","http://sacoche.sesamath.net/__ress_html/2354/182.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M40","http://sacoche.sesamath.net/__ress_html/2354/183.html")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET item_lien=REPLACE(item_lien,"http://college-guitres.com/cahier-de-textes/mathenpoche.php?compet=6M41","http://sacoche.sesamath.net/__ress_html/2354/184.html")' );
  }
}

?>
