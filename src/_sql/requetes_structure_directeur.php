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
// Ces méthodes ne concernent que les directeurs.

class DB_STRUCTURE_DIRECTEUR extends DB
{

/**
 * Retourner à un directeur (ou à un admin) les statistiques globales d'un établissement
 *
 * @param void
 * @return array()
 */
public static function DB_recuperer_statistiques()
{
  $tab_retour = array();
  // nb personnels enregistrés ; nb élèves enregistrés ; nb parents enregistrés
  $DB_SQL = 'SELECT user_profil_type, COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY user_profil_type ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
  $nb_professeurs     = (isset($DB_TAB['professeur'    ])) ? $DB_TAB['professeur'    ]['nombre'] : 0 ;
  $nb_directeurs      = (isset($DB_TAB['directeur'     ])) ? $DB_TAB['directeur'     ]['nombre'] : 0 ;
  $nb_administrateurs = (isset($DB_TAB['administrateur'])) ? $DB_TAB['administrateur']['nombre'] : 0 ;
  $nb_eleves          = (isset($DB_TAB['eleve'         ])) ? $DB_TAB['eleve'         ]['nombre'] : 0 ;
  $nb_parents         = (isset($DB_TAB['parent'        ])) ? $DB_TAB['parent'        ]['nombre'] : 0 ;
  $tab_retour[] = $nb_professeurs + $nb_directeurs + $nb_administrateurs ;
  $tab_retour[] = $nb_eleves;
  $tab_retour[] = $nb_parents;
  // nb personnels connectés ; nb élèves connectés ; nb parents connectés
  $DB_SQL = 'SELECT user_profil_type, COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() AND user_connexion_date>DATE_SUB(NOW(),INTERVAL 6 MONTH) ';
  $DB_SQL.= 'GROUP BY user_profil_type ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
  $nb_professeurs     = (isset($DB_TAB['professeur'    ])) ? $DB_TAB['professeur'    ]['nombre'] : 0 ;
  $nb_directeurs      = (isset($DB_TAB['directeur'     ])) ? $DB_TAB['directeur'     ]['nombre'] : 0 ;
  $nb_administrateurs = (isset($DB_TAB['administrateur'])) ? $DB_TAB['administrateur']['nombre'] : 0 ;
  $nb_eleves          = (isset($DB_TAB['eleve'         ])) ? $DB_TAB['eleve'         ]['nombre'] : 0 ;
  $nb_parents         = (isset($DB_TAB['parent'        ])) ? $DB_TAB['parent'        ]['nombre'] : 0 ;
  $tab_retour[] = $nb_professeurs + $nb_directeurs + $nb_administrateurs ;
  $tab_retour[] = $nb_eleves;
  $tab_retour[] = $nb_parents;
  // nb notes saisies aux évaluations ;
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $tab_retour[] = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // nb notes saisies aux évaluations récemment
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE saisie_date>DATE_SUB(NOW(),INTERVAL 6 MONTH) ';
  $tab_retour[] = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Retour
  return $tab_retour;
}

/**
 * compter_saisies_prof_classe
 *
 * Remarque : on passe par sacoche_devoir pour ne conserver que les évaluations de l'année scolaire en cours.
 *
 * Attention, renvoie aussi des lignes avec juste les noms des profs : il est plus rapide de les écarter a posteriori en PHP
 * que d'ajouter un test groupe_nom IS NOT NULL ou de remplacer la jointure par un INNER JOIN
 * car ces deux procédés allongent le temps de réponse MySQL.
 *
 * @param void
 * @return array
 */
public static function DB_compter_saisies_prof_classe()
{
  $DB_SQL = 'SELECT CONCAT(prof.user_nom," ",prof.user_prenom) AS professeur, groupe_nom, COUNT(saisie_note) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user AS prof ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir ON prof.user_id=sacoche_devoir.proprio_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil ON prof.user_profil_sigle=sacoche_user_profil.user_profil_sigle ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS eleve ON sacoche_saisie.eleve_id=eleve.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON eleve.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type ';
  $DB_SQL.= 'GROUP BY prof.user_id,groupe_nom';
  $DB_VAR = array(':profil_type'=>'professeur');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>