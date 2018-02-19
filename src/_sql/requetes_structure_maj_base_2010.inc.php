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
// MAJ 2010-05-15 => 2010-06-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-05-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-06-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // date/heure de dernière connexion effective
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_connexion_date DATETIME NOT NULL AFTER user_statut' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-06-03 => 2010-06-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-06-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-06-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // date/heure de dernière tentative de connexion
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_tentative_date DATETIME NOT NULL AFTER user_statut' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-06-12 => 2010-07-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-06-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'index
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD INDEX groupe_type (groupe_type)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD INDEX groupe_prof_id (groupe_prof_id)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ADD PRIMARY KEY (parametre_nom)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD UNIQUE (user_login)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_profil (user_profil)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_statut (user_statut)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_id_ent (user_id_ent)' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-04 => 2010-07-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // mise à jour majeure du socle
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_section CHANGE section_nom section_nom VARCHAR(165) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_nom=REPLACE(pilier_nom,"Pilier ","Compétence ")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_pilier SET pilier_nom=REPLACE(pilier_nom,"\'","’")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom=REPLACE(section_nom,"\'","’")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom=REPLACE(entree_nom,"\'","’")' );
    // mise à jour du socle - palier 1 - domaines
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - vocabulaire" WHERE section_id=4' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - grammaire" WHERE section_id=5' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - orthographe" WHERE section_id=6' );
    // mise à jour du socle - palier 1 - items
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Lire seul et écouter lire des textes du patrimoine et des œuvres intégrales de la littérature de jeunesse adaptés à son âge." WHERE entree_id=5' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Distinguer le présent du futur et du passé." WHERE entree_id=20' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Repérer des cases, des nœuds d’un quadrillage." WHERE entree_id=36' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Être précis et soigneux dans les mesures et les calculs." WHERE entree_id=39' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Reconnaître les emblèmes et les symboles de la République française." WHERE entree_id=43' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Appliquer les codes de la politesse dans ses relations avec ses camarades, avec les adultes de l’école et hors de l’école, avec le maître au sein de la classe." WHERE entree_id=46' );
    // mise à jour du socle - palier 2 - domaines
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - vocabulaire" WHERE section_id=16' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - grammaire" WHERE section_id=17' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Étude de la langue - orthographe" WHERE section_id=18' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Maîtriser des connaissances dans divers domaines scientifiques et les mobiliser dans des contextes scientifiques différents et dans des activités de la vie courante" WHERE section_id=29' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Environnement et développement durable" WHERE section_id=30' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Avoir des repères relevant du temps et de l’espace" WHERE section_id=36' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_ordre=4 WHERE section_id=38' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_section VALUES ( 73,  9, 3, "Lire et pratiquer différents langages")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Avoir une bonne maîtrise de son corps et une pratique physique (sportive ou artistique)" WHERE section_id=43' );
    // mise à jour du socle - palier 2 - items
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Dire de mémoire, de façon expressive, une dizaine de poèmes et de textes en prose." WHERE entree_id=51' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Lire seul des textes du patrimoine et des œuvres intégrales de la littérature de jeunesse, adaptés à son âge." WHERE entree_id=53' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser des instruments de mesure." WHERE entree_id=105' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Mobiliser ses connaissances pour comprendre quelques questions liées à l’environnement et au développement durable et agir en conséquence." WHERE entree_id=123' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Connaitre et maîtriser les fonctions de base d’un ordinateur et de ses périphériques." WHERE entree_id=124' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET section_id=73 , entree_nom="Lire et utiliser textes, cartes, croquis, graphiques." WHERE entree_id=132' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_ordre=entree_ordre-1 WHERE section_id=36' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Lire des œuvres majeures du patrimoine et de la littérature pour la jeunesse." WHERE entree_id=137' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Reconnaître et décrire des œuvres préalablement étudiées." WHERE entree_id=140' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Interpréter de mémoire une chanson, participer à un jeu rythmique ; repérer des éléments musicaux caractéristiques simples." WHERE entree_id=142' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Inventer et réaliser des textes, des œuvres plastiques, des chorégraphies ou des enchaînements, à visée artistique ou expressive." WHERE entree_id=143' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Respecter les règles de la vie collective." WHERE entree_id=147' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Être persévérant dans toutes les activités." WHERE entree_id=150' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Commencer à savoir s’autoévaluer dans des situations simples." WHERE entree_id=151' );
    // mise à jour du socle - palier 3 - domaines
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Dire" WHERE section_id=46' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_section WHERE section_id=47' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Savoir utiliser des connaissances dans divers domaines scientifiques" WHERE section_id=55' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Environnement et développement durable" WHERE section_id=56' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="S’approprier un environnement informatique de travail" WHERE section_id=57' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Adopter une attitude responsable" WHERE section_id=58' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Créer, produire, traiter, exploiter des données" WHERE section_id=59' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="S’informer, se documenter" WHERE section_id=60' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Communiquer, échanger" WHERE section_id=61' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Avoir des connaissances et des repères" WHERE section_id=62' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Situer dans le temps, l’espace, les civilisations" , section_ordre=2 WHERE section_id=67' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Lire et pratiquer différents langages" , section_ordre=3 WHERE section_id=66' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_section VALUES ( 74, 16, 4, "Faire preuve de sensibilité, d’esprit critique, de curiosité")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_section WHERE section_id=63' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_section WHERE section_id=64' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_section WHERE section_id=65' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Être acteur de son parcours de formation et d’orientation" WHERE section_id=70' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_section SET section_nom="Être capable de mobiliser ses ressources intellectuelles et physiques dans diverses situations" WHERE section_id=71' );
    // mise à jour du socle - palier 3 - items
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Adapter son mode de lecture à la nature du texte proposé et à l’objectif poursuivi." WHERE entree_id=157' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 282, 44,  1, "Repérer les informations dans un texte à partir des éléments explicites et des éléments implicites nécessaires.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser ses capacités de raisonnement, ses connaissances sur la langue, savoir faire appel à des outils appropriés pour lire." , entree_ordre=2 WHERE entree_id=158' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Dégager, par écrit ou oralement, l’essentiel d’un texte lu." , entree_ordre=3 WHERE entree_id=159' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Manifester, par des moyens divers, sa compréhension de textes variés." , entree_ordre=4 WHERE entree_id=160' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=161' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=162' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Reproduire un document sans erreur et avec une présentation adaptée." WHERE entree_id=163' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Écrire lisiblement un texte, spontanément ou sous la dictée, en respectant l’orthographe et la grammaire." WHERE entree_id=164' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=165' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Rédiger un texte bref, cohérent et ponctué, en réponse à une question ou à partir de consignes données." , entree_ordre=2 WHERE entree_id=166' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser ses capacités de raisonnement, ses connaissances sur la langue, savoir faire appel à des outils variés pour améliorer son texte." , entree_ordre=3 WHERE entree_id=167' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=168' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=169' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 283, 46,  0, "Formuler clairement un propos simple.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Développer de façon suivie un propos en public sur un sujet déterminé." , entree_ordre=1 WHERE entree_id=170' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Adapter sa prise de parole à la situation de communication." , entree_ordre=2 WHERE entree_id=171' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Participer à un débat, à un échange verbal." , entree_ordre=3 WHERE entree_id=172' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=173' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=174' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=175' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=176' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Établir un contact social." WHERE entree_id=177' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Écrire un message simple." WHERE entree_id=190' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Écrire un court récit, une description." WHERE entree_id=192' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=198' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=199' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=200' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=201' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Nombres et calculs : connaître et utiliser les nombres entiers, décimaux et fractionnaires ; mener à bien un calcul mental, à la main, à la calculatrice, avec un ordinateur." , entree_ordre=1 WHERE entree_id=202' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=203' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=204' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Géométrie : connaître et représenter des figures géométriques et des objets de l’espace ; utiliser leurs propriétés." , entree_ordre=2 WHERE entree_id=205' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=206' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=207' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Grandeurs et mesures : réaliser des mesures (longueurs, durées, …), calculer des valeurs (volumes, vitesses, …) en utilisant différentes unités." , entree_ordre=3 WHERE entree_id=208' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=209' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="L’univers et la Terre : organisation de l’univers ; structure et évolution au cours des temps géologiques de la Terre, phénomènes physiques." WHERE entree_id=210' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Le vivant : unité d’organisation et diversité ; fonctionnement des organismes vivants, évolution des espèces, organisation et fonctionnement du corps humain." WHERE entree_id=212' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Les objets techniques : analyse, conception et réalisation ; fonctionnement et conditions d’utilisation." WHERE entree_id=214' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Mobiliser ses connaissances pour comprendre des questions liées à l’environnement et au développement durable." WHERE entree_id=215' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser, gérer des espaces de stockage à disposition." , entree_ordre=0 WHERE entree_id=218' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser les périphériques à disposition." , entree_ordre=1 WHERE entree_id=220' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Utiliser les logiciels et les services à disposition." , entree_ordre=2 WHERE entree_id=216' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=217' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=219' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=221' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Connaître et respecter les règles élémentaires du droit relatif à sa pratique." WHERE entree_id=222' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Protéger sa personne et ses données." WHERE entree_id=223' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Faire preuve d’esprit critique face à l’information et à son traitement." , entree_ordre=2 WHERE entree_id=225' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Participer à des travaux collaboratifs en connaissant les enjeux et en respectant les règles." , entree_ordre=3 WHERE entree_id=228' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=224' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=226' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=227' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Saisir et mettre en page un texte." WHERE entree_id=229' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Traiter une image, un son ou une vidéo." , entree_ordre=1 WHERE entree_id=235' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Organiser la composition du document, prévoir sa présentation en fonction de sa destination." WHERE entree_id=231' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Différencier une situation simulée ou modélisée d’une situation réelle." , entree_ordre=3 WHERE entree_id=234' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=230' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=232' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=233' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Consulter des bases de données documentaires en mode simple (plein texte)." WHERE entree_id=236' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Identifier, trier et évaluer des ressources." , entree_ordre=1 WHERE entree_id=239' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Chercher et sélectionner l’information demandée." WHERE entree_id=238' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=237' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=240' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Écrire, envoyer, diffuser, publier." , entree_ordre=0 WHERE entree_id=243' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Recevoir un commentaire, un message y compris avec pièces jointes." WHERE entree_id=242' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 284, 61,  2, "Exploiter les spécificités des différentes situations de communication en temps réel ou différé.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=241' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=244' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant de l’espace : les grands ensembles physiques et humains et les grands types d’aménagements dans le monde, les principales caractéristiques géographiques de la France et de l’Europe." WHERE entree_id=245' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=246' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=247' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant du temps : les différentes périodes de l’histoire de l’humanité ; les grands traits de l’histoire (politique, sociale, économique, littéraire, artistique, culturelle) de la France et de l’Europe." , section_id=62 WHERE entree_id=249' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=248' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant de la culture littéraire : œuvres littéraires du patrimoine." , section_id=62 , entree_ordre=2 WHERE entree_id=251' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=250' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Relevant de la culture artistique : œuvres picturales, musicales, scéniques, architecturales ou cinématographiques du patrimoine." , section_id=62 , entree_ordre=3 WHERE entree_id=252' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=253' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 285, 62,  4, "Relevant de la culture civique : droits de l’Homme ; formes d’organisation politique, économique et sociale dans l’Union européenne ; place et rôle de l’État en France ; mondialisation ; développement durable.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 286, 67,  0, "Situer des événements, des œuvres littéraires ou artistiques, des découvertes scientifiques ou techniques, des ensembles géographiques.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Identifier la diversité des civilisations, des langues, des sociétés, des religions." , entree_ordre=1 WHERE entree_id=256' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 287, 67,  2, "Établir des liens entre les œuvres (littéraires, artistiques) pour mieux les comprendre.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Mobiliser ses connaissances pour donner du sens à l’actualité." WHERE entree_id=259' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=257' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=258' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Lire et employer différents langages : textes – graphiques – cartes – images – musique." WHERE entree_id=255' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 293, 66,  1, "Connaître et pratiquer diverses formes d’expression à visée littéraire.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Connaître et pratiquer diverses formes d’expression à visée artistique." , section_id=66 , entree_ordre=2 WHERE entree_id=254' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 288, 74,  0, "Être sensible aux enjeux esthétiques et humains d’un texte littéraire.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 289, 74,  1, "Être sensible aux enjeux esthétiques et humains d’une œuvre artistique.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 290, 74,  2, "Être capable de porter un regard critique sur un fait, un document, une œuvre.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 291, 74,  3, "Manifester sa curiosité pour l’actualité et pour les activités culturelles ou artistiques.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Principaux droits de l’Homme et du citoyen." WHERE entree_id=260' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Valeurs, symboles, institutions de la République." WHERE entree_id=261' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Règles fondamentales de la démocratie et de la justice." WHERE entree_id=262' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Grandes institutions de l’Union européenne et rôle des grands organismes internationaux." WHERE entree_id=263' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Rôle de la défense nationale." WHERE entree_id=264' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Fonctionnement et rôle de différents médias." WHERE entree_id=265' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Respecter les règles de la vie collective." WHERE entree_id=266' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Se familiariser avec l’environnement économique, les entreprises, les métiers de secteurs et de niveaux de qualification variés." , entree_ordre=0 WHERE entree_id=272' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Connaître les parcours de formation correspondant à ces métiers et les possibilités de s’y intégrer." , entree_ordre=1 WHERE entree_id=273' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_entree WHERE entree_id=271' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_entree VALUES ( 292, 70,  2, "Savoir s’autoévaluer et être capable de décrire ses intérêts, ses compétences et ses acquis.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Être autonome dans son travail : savoir l’organiser, le planifier, l’anticiper, rechercher et sélectionner des informations utiles." WHERE entree_id=274' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Identifier ses points forts et ses points faibles dans des situations variées." WHERE entree_id=275' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Mobiliser à bon escient ses capacités motrices dans le cadre d’une pratique physique (sportive ou artistique) adaptée à son potentiel." , entree_ordre=2 WHERE entree_id=277' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_ordre=3 WHERE entree_id=276' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="S’engager dans un projet individuel." WHERE entree_id=278' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="S’intégrer et coopérer dans un projet collectif." WHERE entree_id=279' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Manifester curiosité, créativité, motivation à travers des activités conduites ou reconnues par l’établissement." WHERE entree_id=280' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Assumer des rôles, prendre des initiatives et des décisions." WHERE entree_id=281' );
    // mise à jour des liens des référentiels
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=0 WHERE entree_id IN(161,162,165,168,169,173,174,175,176,241,257,258)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=197 WHERE entree_id IN(198,199,200,201)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=202 WHERE entree_id IN(203,204)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=205 WHERE entree_id IN(206,207)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=208 WHERE entree_id=209' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=216 WHERE entree_id IN(217,221)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=218 WHERE entree_id=219' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=222 WHERE entree_id=224' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=223 WHERE entree_id IN(226,227)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=229 WHERE entree_id=230' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=197 WHERE entree_id IN(232,233)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=216 WHERE entree_id=237' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=238 WHERE entree_id=240' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=243 WHERE entree_id=244' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=245 WHERE entree_id IN(246,247)' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=249 WHERE entree_id=248' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=251 WHERE entree_id=250' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=252 WHERE entree_id=253' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item SET entree_id=273 WHERE entree_id=271' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'OPTIMIZE TABLE sacoche_socle_pilier');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'OPTIMIZE TABLE sacoche_socle_section');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'OPTIMIZE TABLE sacoche_socle_entree');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-13 => 2010-07-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // paramétrage codes Lomer / background-color
    // La première instruction ayant été oubliée, quelques tentatives d'installations peuvent être corrompues => corrigé dans la v.2010-07-27.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_NA","#ff9999")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_VA","#ffdd33")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_A","#99ff99")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_note_style","Lomer")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-15 => 2010-07-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // oubli d'une modification du socle
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree SET entree_nom="Organisation et gestion de données : reconnaître des situations de proportionnalité, utiliser des pourcentages, des tableaux, des graphiques ; exploiter des données statistiques et aborder des situations simples de probabilité." , entree_ordre=0 WHERE entree_id=197' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-16 => 2010-07-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Correction du bug signalé dans le passage de la v.2010-07-13 à la v.2010-07-15.
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="css_background-color"' );
    if(!empty($DB_ROW))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="css_background-color"' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_NA","#ff9999")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_VA","#ffdd33")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_background-color_A","#99ff99")' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("css_note_style","Lomer")' );
    }
    // ajout de modes de calculs
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_methode referentiel_calcul_methode ENUM( "geometrique", "arithmetique", "classique", "bestof1", "bestof2", "bestof3" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores. Valeur surclassant la configuration par défaut." ' );
    // ajout de 2 tables pour la validation du socle
    // Les supprimer si elles existent : sinon dans le cas d'une restauration de base à une version antérieure (suivie de cette mise à jour), ces anciennes tables éventuellement existantes ne seraient pas réinitialisées.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_user_entree' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_jointure_user_entree (user_id MEDIUMINT(8) UNSIGNED NOT NULL,entree_id SMALLINT(5) UNSIGNED NOT NULL,validation_entree_etat TINYINT(1) NOT NULL COMMENT "1 si validation positive ; 0 si validation négative.",validation_entree_date DATE NOT NULL,validation_entree_info TINYTEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",UNIQUE KEY validation_entree_key (user_id,entree_id),KEY user_id (user_id),KEY entree_id (entree_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE IF EXISTS sacoche_jointure_user_pilier' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'CREATE TABLE sacoche_jointure_user_pilier (user_id MEDIUMINT(8) UNSIGNED NOT NULL,pilier_id SMALLINT(5) UNSIGNED NOT NULL,                                                                                                          validation_pilier_date DATE NOT NULL,validation_pilier_info TINYTEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",UNIQUE KEY validation_pilier_key (user_id,pilier_id),KEY user_id (user_id),KEY pilier_id (pilier_id) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-27 => 2010-07-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un champ qui va finalement servir pour valider les piliers
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier ADD validation_pilier_etat TINYINT(1) NOT NULL COMMENT "1 si validation positive ; 0 si validation négative." AFTER pilier_id' );
    // ajout de 2 entrées pour gérer les droits de validation
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("profil_validation_entree" , "directeur,professeur")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("profil_validation_pilier" , "directeur,profprincipal")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-29 => 2010-07-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-07-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un champ afin de pouvoir repérer les demandes d'évaluations en attente de saisie
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_note saisie_note ENUM( "VV", "V", "R", "RR", "ABS", "NN", "DISP", "REQ" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-07-31 => 2010-08-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-07-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-08-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage d'un champ pour davantage de clarté en vu de l'ajout d'options
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"ms","BilanMoyenneScore") WHERE parametre_nom="eleve_options"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"pv","BilanPourcentageAcquis") WHERE parametre_nom="eleve_options"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"as","SoclePourcentageAcquis") WHERE parametre_nom="eleve_options"' );
    // suppression du palier 4 qui ne sert à rien
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_palier WHERE palier_id=4' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,",4","") WHERE parametre_nom="paliers"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur=REPLACE(parametre_valeur,"4","3") WHERE parametre_nom="paliers"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-08-01 => 2010-08-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-08-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-08-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'un champ pour pouvoir décider si un item est disponible ou non à la réévaluation
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_cart TINYINT(1) NOT NULL DEFAULT "1" COMMENT "0 pour empêcher les élèves de demander une évaluation sur cet item." AFTER item_coef' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-08-04 => 2010-08-05
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-08-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-08-05';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // finalement la table RSS ne va pas servir
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE sacoche_rss' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-08-05 => 2010-08-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-08-05')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-08-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajouter deux matières communes
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (40 , 1 , 0 , "VSPRO" , "Vie sociale et professionnelle")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (41 , 1 , 0 , "G-TPR" , "Enseignement technologique-professionnel")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-08-06 => 2010-09-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-08-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-09-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // oubli d'une entrée à supprimer dans sacoche_niveau() pour l'ex-palier 4
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_niveau WHERE niveau_id=4' );
    // récupérer qq infos pour maj la suite
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="css_note_style"' );
    if(is_file(CHEMIN_DOSSIER_INCLUDE.'tableau_notes_txt.php'))
    {
      require_once(CHEMIN_DOSSIER_INCLUDE.'tableau_notes_txt.php');
      $rr = $tab_notes_info[$DB_ROW['parametre_valeur']]['RR'];
      $r  = $tab_notes_info[$DB_ROW['parametre_valeur']]['R'];
      $v  = $tab_notes_info[$DB_ROW['parametre_valeur']]['V'];
      $vv = $tab_notes_info[$DB_ROW['parametre_valeur']]['VV'];
    }
    else
    {
      // fichier supprimé en décembre 2014
      $rr = 'RR';
      $r  = 'R';
      $v  = 'V';
      $vv = 'VV';
    }
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="eleve_options"' );
    $eleve_bilans = str_replace( array(',SoclePourcentageAcquis',',SocleEtatValidation','SoclePourcentageAcquis,','SocleEtatValidation,','SoclePourcentageAcquis','SocleEtatValidation') , '' , $DB_ROW['parametre_valeur'] );
    $eleve_socle  = 'SocleAcces,'.str_replace( array(',BilanMoyenneScore',',BilanPourcentageAcquis','BilanMoyenneScore,','BilanPourcentageAcquis,','BilanMoyenneScore','BilanPourcentageAcquis') , '' , $DB_ROW['parametre_valeur'] );
    // ajouter des gestions de droits
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_validation_entree" WHERE parametre_nom="profil_validation_entree"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_validation_pilier" WHERE parametre_nom="profil_validation_pilier"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_modifier_mdp"      , "directeur,professeur,eleve")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_referentiels" , "directeur,professeur,eleve")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom="eleve_options"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_eleve_bilans" , "'.$eleve_bilans.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_eleve_socle"  , "'.$eleve_socle.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="droit_eleve_demandes" WHERE parametre_nom="eleve_demandes"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_nom="note_image_style"     WHERE parametre_nom="css_note_style"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_texte_RR" , "'.$rr.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_texte_R"  , "'.$r.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_texte_V"  , "'.$v.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_texte_VV" , "'.$vv.'")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-09-27 => 2010-10-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-09-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-10-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajouter une gestion de droits
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_score_bilan" , "directeur,professeur,eleve")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-10-04 => 2010-10-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-10-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-10-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif de champs TINYTEXT en VARCHAR
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_entree CHANGE entree_nom entree_nom VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_info saisie_info VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du devoir et du professeur, conservé les années suivantes."' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item CHANGE item_nom item_nom VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item CHANGE item_lien item_lien VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_valeur parametre_valeur VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier CHANGE validation_pilier_info validation_pilier_info VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes."' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_entree CHANGE validation_entree_info validation_entree_info VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes."' );
    // réparation de la table "sacoche_socle_section"
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_section CHANGE section_nom section_nom VARCHAR( 165 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_socle_section WHERE section_id>28' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_section VALUES 
                                          ( 29,  7, 2, "Maîtriser des connaissances dans divers domaines scientifiques et les mobiliser dans des contextes scientifiques différents et dans des activités de la vie courante"),
                                          ( 30,  7, 3, "Environnement et développement durable"),
                                          ( 31,  8, 1, "S’approprier un environnement informatique de travail"),
                                          ( 32,  8, 2, "Adopter une attitude responsable"),
                                          ( 33,  8, 3, "Créer, produire, traiter, exploiter des données"),
                                          ( 34,  8, 4, "S’informer, se documenter"),
                                          ( 35,  8, 5, "Communiquer, échanger"),
                                          ( 36,  9, 1, "Avoir des repères relevant du temps et de l’espace"),
                                          ( 37,  9, 2, "Avoir des repères littéraires"),
                                          ( 73,  9, 3, "Lire et pratiquer différents langages"),
                                          ( 38,  9, 4, "Pratiquer les arts et avoir des repères en histoire des arts"),
                                          ( 39, 10, 1, "Connaître les principes et fondements de la vie civique et sociale"),
                                          ( 40, 10, 2, "Avoir un comportement responsable"),
                                          ( 41, 11, 1, "S’appuyer sur des méthodes de travail pour être autonome"),
                                          ( 42, 11, 2, "Faire preuve d’initiative"),
                                          ( 43, 11, 3, "Avoir une bonne maîtrise de son corps et une pratique physique (sportive ou artistique)"),
                                          ( 44, 12, 1, "Lire"),
                                          ( 45, 12, 2, "Écrire"),
                                          ( 46, 12, 3, "Dire"),
                                          ( 48, 13, 1, "Réagir et dialoguer"),
                                          ( 49, 13, 2, "Écouter et comprendre"),
                                          ( 50, 13, 3, "Parler en continu"),
                                          ( 51, 13, 4, "Lire"),
                                          ( 52, 13, 5, "Écrire"),
                                          ( 53, 14, 1, "Pratiquer une démarche scientifique et technologique, résoudre des problèmes"),
                                          ( 54, 14, 2, "Savoir utiliser des connaissances et des compétences mathématiques"),
                                          ( 55, 14, 3, "Savoir utiliser des connaissances dans divers domaines scientifiques"),
                                          ( 56, 14, 4, "Environnement et développement durable"),
                                          ( 57, 15, 1, "S’approprier un environnement informatique de travail"),
                                          ( 58, 15, 2, "Adopter une attitude responsable"),
                                          ( 59, 15, 3, "Créer, produire, traiter, exploiter des données"),
                                          ( 60, 15, 4, "S’informer, se documenter"),
                                          ( 61, 15, 5, "Communiquer, échanger"),
                                          ( 62, 16, 1, "Avoir des connaissances et des repères"),
                                          ( 67, 16, 2, "Situer dans le temps, l’espace, les civilisations"),
                                          ( 66, 16, 3, "Lire et pratiquer différents langages"),
                                          ( 74, 16, 4, "Faire preuve de sensibilité, d’esprit critique, de curiosité"),
                                          ( 68, 17, 1, "Connaître les principes et fondements de la vie civique et sociale"),
                                          ( 69, 17, 2, "Avoir un comportement responsable"),
                                          ( 70, 18, 1, "Être acteur de son parcours de formation et d’orientation"),
                                          ( 71, 18, 2, "Être capable de mobiliser ses ressources intellectuelles et physiques dans diverses situations"),
                                          ( 72, 18, 3, "Faire preuve d’initiative")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-10-16 => 2010-10-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-10-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-10-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajouter une entrée dans sacoche_referentiel pour paramétrer le bulletin de synthèse
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_mode_synthese ENUM( "inconnu", "sans", "domaine", "theme" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "inconnu" ' );
    // ajouter une entrée dans sacoche_matiere pour ordonner les matières dans le bulletin de synthèse
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ADD matiere_ordre TINYINT UNSIGNED NOT NULL DEFAULT "255" AFTER matiere_transversal ' );
    // ajout de valeurs par défaut dans les champs...  en tout cas de la base de la structure...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande CHANGE user_id user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE matiere_id matiere_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE item_id item_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE demande_date demande_date DATE NOT NULL DEFAULT "0000-00-00" , CHANGE demande_statut demande_statut ENUM( "eleve", "prof" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" COMMENT "[eleve] pour une demande d\'élève ; [prof] pour une prévision d\'évaluation par le prof ; une annulation de l\'élève ou du prof efface l\'enregistrement" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE prof_id prof_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE groupe_id groupe_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE devoir_date devoir_date DATE NOT NULL DEFAULT "0000-00-00" , CHANGE devoir_info devoir_info VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe CHANGE groupe_type groupe_type ENUM( "classe", "groupe", "besoin", "eval" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "classe" , CHANGE groupe_prof_id groupe_prof_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Id du prof dans le cas d\'un groupe de type [eval] ; 0 sinon." , CHANGE groupe_ref groupe_ref CHAR( 8 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE groupe_nom groupe_nom VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE niveau_id niveau_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_devoir_item CHANGE devoir_id devoir_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE item_id item_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE jointure_ordre jointure_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode CHANGE groupe_id groupe_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE periode_id periode_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE jointure_date_debut jointure_date_debut DATE NOT NULL DEFAULT "0000-00-00" , CHANGE jointure_date_fin jointure_date_fin DATE NOT NULL DEFAULT "0000-00-00" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_entree CHANGE user_id user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE entree_id entree_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE validation_entree_etat validation_entree_etat TINYINT( 1 ) NOT NULL DEFAULT "1" COMMENT "1 si validation positive ; 0 si validation négative." , CHANGE validation_entree_date validation_entree_date DATE NOT NULL DEFAULT "0000-00-00" , CHANGE validation_entree_info validation_entree_info VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_groupe CHANGE user_id user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE groupe_id groupe_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE jointure_pp jointure_pp TINYINT( 1 ) NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_matiere CHANGE user_id user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE matiere_id matiere_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE jointure_coord jointure_coord TINYINT( 1 ) NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier CHANGE user_id user_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE pilier_id pilier_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE validation_pilier_etat validation_pilier_etat TINYINT( 1 ) NOT NULL DEFAULT "1" COMMENT "1 si validation positive ; 0 si validation négative." , CHANGE validation_pilier_date validation_pilier_date DATE NOT NULL DEFAULT "0000-00-00" , CHANGE validation_pilier_info validation_pilier_info VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere CHANGE matiere_ref matiere_ref VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE matiere_nom matiere_nom VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau CHANGE palier_id palier_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE niveau_ordre niveau_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE niveau_ref niveau_ref VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE code_mef code_mef CHAR( 11 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE niveau_nom niveau_nom VARCHAR( 55 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre CHANGE parametre_nom parametre_nom VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  , CHANGE parametre_valeur parametre_valeur VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_nom periode_nom VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE matiere_id matiere_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE niveau_id niveau_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE referentiel_partage_etat referentiel_partage_etat ENUM( "bof", "non", "oui", "hs" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "non" COMMENT "[oui] = référentiel partagé sur le serveur communautaire ; [non] = référentiel non partagé avec la communauté ; [bof] = référentiel dont le partage est sans intérêt (pas novateur) ; [hs] = référentiel dont le partage est sans objet (matière spécifique)" , CHANGE referentiel_partage_date referentiel_partage_date DATE NOT NULL DEFAULT "0000-00-00" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine CHANGE matiere_id matiere_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE niveau_id niveau_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE domaine_ordre domaine_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "1" COMMENT "Commence à 1." , CHANGE domaine_ref domaine_ref CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE domaine_nom domaine_nom VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item CHANGE theme_id theme_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE entree_id entree_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE item_ordre item_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Commence à 0." , CHANGE item_nom item_nom VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE item_lien item_lien VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_theme CHANGE domaine_id domaine_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE theme_ordre theme_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "1" COMMENT "Commence à 1." , CHANGE theme_nom theme_nom VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE prof_id prof_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE eleve_id eleve_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE devoir_id devoir_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE item_id item_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE saisie_date saisie_date DATE NOT NULL DEFAULT "0000-00-00" , CHANGE saisie_note saisie_note ENUM( "VV", "V", "R", "RR", "ABS", "NN", "DISP", "REQ" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN" , CHANGE saisie_info saisie_info VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du devoir et du professeur, conservé les années suivantes." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_entree CHANGE section_id section_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE entree_ordre entree_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Commence à 0." , CHANGE entree_nom entree_nom VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_palier CHANGE palier_ordre palier_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE palier_nom palier_nom VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_pilier CHANGE palier_id palier_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE pilier_ordre pilier_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "1" COMMENT "Commence à 1." , CHANGE pilier_ref pilier_ref VARCHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE pilier_nom pilier_nom VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_section CHANGE pilier_id pilier_id TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "0" , CHANGE section_ordre section_ordre TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT "1" COMMENT "Commence à 1."  , CHANGE section_nom section_nom VARCHAR( 165 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_num_sconet user_num_sconet MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0" COMMENT "ELENOET pour un élève (entre 2000 et 5000 ; parfois appelé n° GEP avec un 0 devant) ou INDIVIDU_ID pour un prof (dépasse parfois une capacité SMALLINT UNSIGNED)" , CHANGE user_reference user_reference CHAR( 11 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Dans Sconet, ID_NATIONAL pour un élève (pour un prof ce pourrait être le NUMEN mais il n\'est pas renseigné et il faudrait deux caractères de plus). Ce champ sert aussi pour un import tableur." , CHANGE user_profil user_profil ENUM( "eleve", "professeur", "directeur", "administrateur" ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" , CHANGE user_nom user_nom VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE user_prenom user_prenom VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE user_login user_login VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE user_password user_password CHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" , CHANGE user_tentative_date user_tentative_date DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00" , CHANGE user_connexion_date user_connexion_date DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00" , CHANGE eleve_classe_id eleve_classe_id MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT "0"  , CHANGE user_id_ent user_id_ent VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l\'ENT...)." , CHANGE user_id_gepi user_id_gepi VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Login de l\'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin." ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-10-29 => 2010-11-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-10-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_legende_RR"   , "A complètement échoué.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_legende_R"    , "A plutôt échoué.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_legende_V"    , "A plutôt réussi.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("note_legende_VV"   , "A complètement réussi.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_texte_NA"   , "NA")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_texte_VA"   , "VA")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_texte_A"    , "A")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_legende_NA" , "Non acquis.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_legende_VA" , "Partiellement acquis.")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("acquis_legende_A"  , "Acquis.")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-11-03 => 2010-11-04
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-04';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // mise à jour de paramètres (anciennement changés pour les nouveaux, mais modifs oubliées pour les anciens)
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_mode"' );
    if($DB_ROW['parametre_valeur']=='normal')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="sacoche" WHERE parametre_nom="connexion_nom"' );
    }
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if( ($DB_ROW['parametre_valeur']=='argos') || ($DB_ROW['parametre_valeur']=='argos64') )
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="ent-cas.ac-bordeaux.fr" WHERE parametre_nom="cas_serveur_host"' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="cas" WHERE parametre_nom="cas_serveur_root"' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-11-04 => 2010-11-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-04')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // remise du niveau P4 pour que les lycées puissent disposer d'un "niveau transversal"
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES (4,4,159,"P4","","Palier 4 (2nde - Tle)")' );
    // ajout d'une matière "Informatique" pour gérer le b2i
    // Si une matière similaire spécifique est trouvée, la convertir... surtout si sa référence est "INFO" (sinon conflit en vue)
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM sacoche_matiere WHERE matiere_ref="INFO"' );
    if(empty($DB_ROW))
    {
      $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id FROM sacoche_matiere WHERE matiere_ref IN("INFOR","B2I","ORDI","MICRO")' );
    }
    if(!empty($DB_ROW))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_matiere SET matiere_id=42 WHERE matiere_id='.$DB_ROW['matiere_id'] );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_domaine   SET matiere_id=42 WHERE matiere_id='.$DB_ROW['matiere_id'] );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_demande               SET matiere_id=42 WHERE matiere_id='.$DB_ROW['matiere_id'] );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel           SET matiere_id=42 , referentiel_partage_etat="non" WHERE matiere_id='.$DB_ROW['matiere_id'] );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre             SET parametre_valeur=REPLACE(parametre_valeur,",99",",42,99") WHERE parametre_nom="matieres"' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_matiere WHERE matiere_id='.$DB_ROW['matiere_id'].'' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES (42,1,0,255,"INFO","Informatique")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-11-14 => 2010-11-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Niveaux 'longitudinaux' renommés en 'cycles' pour éviter la confusion avec la notion de palier du socle
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Cycle 2 (GS-CE1)"  WHERE niveau_id=1' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Cycle 3 (CE2-CM2)" WHERE niveau_id=2' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Cycle Collège"     WHERE niveau_id=3' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Cycle Lycée"       WHERE niveau_id=4' );
    // Modification du nom d'un champ qui devient en conséquence inapproprié
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET palier_id=1 WHERE palier_id>0' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau CHANGE palier_id cycle_id TINYINT(1) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Indique un niveau \'longitudinal\' nommé \'cycle\'."' );
    // ajout d'un paramètre "cycles" disctinct du paramètre "paliers"
    $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="paliers"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("cycles" , "'.$DB_ROW['parametre_valeur'].'")' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-11-15 => 2010-11-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Double erreur dans la maj précédente : "cycle_id" au lieu de "niveau_cycle" et erreur dans la requête pour créer sacoche_niveau sur les nouvelles installations
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_niveau"');
    if(!empty($DB_TAB))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau CHANGE cycle_id niveau_cycle TINYINT(1) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Indique un niveau \'longitudinal\' nommé \'cycle\'."' );
    }
    else
    {
      $reload_sacoche_niveau = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_niveau.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2010-11-16 => 2010-11-28
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2010-11-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2010-11-28';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajouter une gestion de droits
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_algorithme" , "directeur,professeur,eleve")' );
    // nouvel attribut user : daltonisme
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_daltonisme TINYINT(1) UNSIGNED NOT NULL DEFAULT "0" AFTER user_statut' );
  }
}

?>
