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
// Ces méthodes ne concernent que la table "sacoche_user_switch".

class DB_STRUCTURE_SWITCH extends DB
{

/**
 * ajouter_comptes_associes
 *
 * @param string $user_liste   (sans les ',' aux extrémités)
 * @return int
 */
public static function DB_ajouter_comptes_associes($user_liste)
{
  $DB_SQL = 'INSERT INTO sacoche_user_switch( user_switch_liste) ';
  $DB_SQL.= 'VALUES                         (:user_switch_liste)';
  $DB_VAR = array( ':user_switch_liste' => ','.$user_liste.',' );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_comptes_associes
 *
 * @param int    $user_switch_id
 * @param string $user_liste   (sans les ',' aux extrémités)
 * @return void
 */
public static function DB_modifier_comptes_associes($user_switch_id,$user_liste)
{
  $DB_SQL = 'UPDATE sacoche_user_switch ';
  $DB_SQL.= 'SET user_switch_liste=:user_switch_liste ';
  $DB_SQL.= 'WHERE user_switch_id=:user_switch_id ';
  $DB_VAR = array(
    ':user_switch_liste' => ','.$user_liste.',',
    ':user_switch_id'    => $user_switch_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_comptes_associes
 *
 * @param int    $devoir_id
 * @param int    $eleve_id
 * @return void
 */
public static function DB_supprimer_comptes_associes($user_switch_id)
{
  $DB_SQL = 'DELETE FROM sacoche_user_switch ';
  $DB_SQL.= 'WHERE user_switch_id=:user_switch_id ';
  $DB_VAR = array( ':user_switch_id' => $user_switch_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_liaisons_obsoletes
 *
 * Est appelé lors de l'initialisation annuelle ou sur demande "recherche et suppression de correspondances anormales".
 * Le nettoyage est aussi effectué de façon individuelle à chaque afffichage de la page "Bascule entre comptes" par DB_recuperer_et_verifier_listing_comptes_associes()
 *
 * @param void
 * @return int
 */
public static function DB_supprimer_liaisons_obsoletes()
{
  $nb_sortis = 0;
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On récupère la liste des users concernés par une liaison
  $DB_SQL = 'SELECT GROUP_CONCAT( SUBSTRING( user_switch_liste, 2 ) SEPARATOR "" ) AS user_liste_virgule ';
  $DB_SQL.= 'FROM sacoche_user_switch ';
  $user_liste_virgule = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  if($user_liste_virgule)
  {
    $user_liste_switch = substr( $user_liste_virgule , 0 , -1 );
    // On cherche, parmi eux, ceux qui ont un compte actif (donc sans les comptes inactifs ni les comptes supprimés)
    $DB_SQL = 'SELECT GROUP_CONCAT(user_id SEPARATOR ",") AS user_liste_actifs ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'WHERE user_id IN('.$user_liste_switch.') AND user_sortie_date>NOW() ';
    $user_liste_actifs = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    if( strlen($user_liste_switch) > strlen($user_liste_actifs) )
    {
      // On compare à l'aide d'une opération sur des tableaux
      $tab_user_switch = explode( ',' , $user_liste_switch );
      $tab_user_actifs = explode( ',' , $user_liste_actifs );
      $tab_user_sortis = array_diff( $tab_user_switch , $tab_user_actifs );
      $nb_sortis = count($tab_user_sortis);
      if($nb_sortis)
      {
        // On retire ces users du champ [user_switch_liste] de [sacoche_user_switch]
        foreach($tab_user_sortis as $user_sorti)
        {
          $DB_SQL = 'UPDATE sacoche_user_switch ';
          $DB_SQL.= 'SET user_switch_liste = REPLACE( user_switch_liste , ",'.$user_sorti.'," , "," ) ';
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
        }
        // Enfin, on nettoie les listes où il n'y a plus qu'un seul user (donc de la forme ",*,")
        $DB_SQL = 'DELETE FROM sacoche_user_switch ';
        $DB_SQL.= 'WHERE user_switch_liste NOT LIKE ",%,%," ';
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
      }
    }
  }
  return $nb_sortis;
}

/**
 * recuperer_et_verifier_listing_comptes_associes
 *
 * @param int   $user_id
 * @param int   $user_switch_id
 * @return array ($user_switch_id,$user_liste)
 */
public static function DB_recuperer_et_verifier_listing_comptes_associes($user_id,$user_switch_id)
{
  $user_liste = NULL;
  // Un "user_switch_id" est transmis : on recherche sur user_switch_liste, tout en vérifiant quand même que le user_id est dans la liste...
  if($user_switch_id)
  {
    $DB_SQL = 'SELECT user_switch_id AS "0", user_switch_liste AS "1" '; // Pour la récupération avec list()
    $DB_SQL.= 'FROM sacoche_user_switch ';
    $DB_SQL.= 'WHERE user_switch_id = :user_switch_id ';
    $DB_SQL.= 'AND user_switch_liste LIKE :switch_liste_like ';
    $DB_VAR = array(
      ':user_switch_id'    => $user_switch_id,
      ':switch_liste_like' => '%,'.$user_id.',%',
    );
    list( $user_switch_id , $user_switch_liste ) = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) + array(NULL,NULL) ;
  }
  // Soit on n'est pas passé dans le test précédent, soit la liste récupérée ne contenait curieusement pas l'id du user : on recherche sur l'id du user, même s'il n'est pas normal qu'un rapprochement existe et ne soit pas transmis
  if(!$user_switch_id)
  {
    $DB_SQL = 'SELECT user_switch_id AS "0", user_switch_liste AS "1" '; // Pour la récupération avec list()
    $DB_SQL.= 'FROM sacoche_user_switch ';
    $DB_SQL.= 'WHERE user_switch_liste LIKE :switch_liste_like ';
    $DB_SQL.= 'LIMIT 1 '; // Au cas où, même s'il ne devrait pas y avoir un même user sur des regroupement
    $DB_VAR = array( ':switch_liste_like' => '%,'.$user_id.',%' );
    list( $user_switch_id , $user_switch_liste ) = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR) + array(NULL,NULL) ;
  }
  // S'il y a des comptes associés, on vérifie qu'il n'y ait pas que le user_id dedans !
  if( $user_switch_id && ($user_switch_liste==','.$user_id.',') )
  {
    DB_STRUCTURE_SWITCH::DB_supprimer_comptes_associes($user_switch_id);
    list( $user_switch_id , $user_switch_liste ) = array(NULL,NULL) ;
  }
  // S'il y a des comptes associés, on vérifie que ce ne soient pas des comptes supprimés ou désactivés (si c'est le cas, alors on met la liste à jour)
  if($user_switch_id)
  {
    $user_liste = substr($user_switch_liste,1,-1);
    // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
    $DB_SQL = 'SELECT GROUP_CONCAT(user_id ORDER BY user_id ASC SEPARATOR ",") AS user_switch_liste ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'WHERE user_id IN('.$user_liste.') AND user_sortie_date>NOW() ';
    $user_liste = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    if($user_liste != substr($user_switch_liste,1,-1))
    {
      // On a trouvé une différence...
      if($user_liste===$user_id) // si test == le transtypage convertit en entier qui ne garde que la première valeur, donc renvoie toujours TRUE
      {
        // Si seul le compte utilisé est envore actif, alors il n'y a plus de liaison
        DB_STRUCTURE_SWITCH::DB_supprimer_comptes_associes($user_switch_id);
        list( $user_switch_id , $user_liste ) = array(NULL,NULL) ;
      }
      else
      {
        // Sinon on met à jour avec la liste des comptes actifs
        DB_STRUCTURE_SWITCH::DB_modifier_comptes_associes( $user_switch_id , $user_liste );
      }
    }
  }
  return array($user_switch_id,$user_liste);
}

/**
 * recuperer_informations_comptes_associes
 *
 * @param string   $user_liste   (sans les ',' aux extrémités)
 * @return array
 */
public static function DB_recuperer_informations_comptes_associes($user_liste)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom , user_profil_nom_court_singulier ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_id IN('.$user_liste.') '; // La date de sortie des comptes concernés a déjà été vérifiée
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC, user_profil_nom_court_singulier ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

}
?>