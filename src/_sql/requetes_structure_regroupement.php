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
// Ces méthodes sont en rapport avec les matières (tables "sacoche_groupe" + "sacoche_jointure_user_groupe").

class DB_STRUCTURE_REGROUPEMENT extends DB
{

/**
 * lister_groupes_sauf_classes
 *
 * @param void
 * @return array
 */
public static function DB_lister_groupes_sauf_classes()
{
  $DB_SQL = 'SELECT groupe_id, groupe_type ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type!=:type ';
  $DB_SQL.= 'ORDER BY groupe_ref ASC';
  $DB_VAR = array(':type'=>'classe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes
 *
 * @param void
 * @return array
 */
public static function DB_lister_classes()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY groupe_ref ASC';
  $DB_VAR = array(':type'=>'classe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes
 *
 * @param void
 * @return array
 */
public static function DB_lister_groupes()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY groupe_ref ASC';
  $DB_VAR = array(':type'=>'groupe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_avec_niveaux
 *
 * @param string $niveau_ordre     facultatif, ASC par défaut, DESC possible
 * @param string $only_groupes_id  facultatif, pour restreindre à un ensemble de groupes
 * @return array
 */
public static function DB_lister_classes_avec_niveaux( $niveau_ordre = 'ASC' , $only_groupes_id = NULL )
{
  $where_groupe = ($only_groupes_id) ? 'AND groupe_id IN('.$only_groupes_id.') ' : '' ;
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type '.$where_groupe;
  $DB_SQL.= 'ORDER BY niveau_ordre '.$niveau_ordre.', groupe_ref ASC';
  $DB_VAR = array(':type'=>'classe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_groupes_avec_niveaux
 *
 * @param void
 * @return array
 */
public static function DB_lister_groupes_avec_niveaux()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC';
  $DB_VAR = array(':type'=>'groupe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_et_groupes_avec_niveaux
 *
 * @param void
 * @return array
 */
public static function DB_lister_classes_et_groupes_avec_niveaux()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':type1' => 'classe',
    ':type2' => 'groupe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_classes_groupes_professeur
 *
 * @param int $prof_id
 * @param string $user_join_groupes
 * @return array
 */
public static function DB_lister_classes_groupes_professeur( $prof_id , $user_join_groupes )
{
  if($user_join_groupes=='config')
  {
    $DB_SQL = 'SELECT groupe_id, groupe_type, groupe_nom, jointure_pp ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type IN (:type1,:type2) ';
  }
  else
  {
    $DB_SQL = 'SELECT groupe_id, groupe_type, groupe_nom, 0 AS jointure_pp ';
    $DB_SQL.= 'FROM sacoche_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  }
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $prof_id,
    ':type1'   => 'classe',
    ':type2'   => 'groupe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les classes et groupes associés à un professeur
 *
 * @param int    $prof_id
 * @param string $user_join_groupes
 * @return array
 */
public static function DB_lister_groupes_professeur( $prof_id , $user_join_groupes )
{
  if($user_join_groupes=='config')
  {
    $DB_SQL = 'SELECT groupe_id, groupe_type, groupe_nom, niveau_ordre ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type!=:type4 ';
  }
  else
  {
    $DB_SQL = 'SELECT DISTINCT groupe_id, groupe_type, groupe_nom, niveau_ordre ';
    $DB_SQL.= 'FROM sacoche_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE ( groupe_type IN (:type1,:type2) ) OR ( groupe_type=:type3 AND user_id=:user_id ) ';
  }
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':user_id' => $prof_id,
    ':type1'   => 'classe',
    ':type2'   => 'groupe',
    ':type3'   => 'besoin',
    ':type4'   => 'eval',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les classes des élèves associés à un parent
 *
 * @param int $parent_id
 * @return array
 */
public static function DB_lister_classes_parent($parent_id)
{
  $DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_type ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON eleve_classe_id=groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  // Test "eleve_classe_id!=0" pour éviter les enfants non affectés à une classe (on peut aussi utiliser INNER JOIN sur sacoche_groupe)
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() AND eleve_classe_id!=0 ';
  $DB_SQL.= 'GROUP BY groupe_id '; // si plusieurs enfants dans la même classe
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(
    ':parent_id'   => $parent_id,
    ':profil_type' => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_professeurs_principaux
 *
 * @param void
 * @return array
 */
public static function DB_lister_jointure_professeurs_principaux()
{
  $DB_SQL = 'SELECT user_id, groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE jointure_pp=:pp AND user_sortie_date>NOW() AND groupe_type=:type '; // groupe_type pour éviter les groupes de besoin
  $DB_VAR = array(
    ':pp'   => 1,
    ':type' => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_professeurs_groupes
 *
 * @param string   $listing_profs_id     id des profs séparés par des virgules
 * @param string   $listing_groupes_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_jointure_professeurs_groupes( $listing_profs_id , $listing_groupes_id )
{
  $DB_SQL = 'SELECT groupe_id,user_id,jointure_pp ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id IN('.$listing_profs_id.') AND groupe_id IN('.$listing_groupes_id.') ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_professeurs_avec_classes
 *
 * @param void
 * @return array
 */
public static function DB_lister_professeurs_avec_classes()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND groupe_type=:type AND user_sortie_date>NOW() ';
  $DB_VAR = array(
    ':profil_type' => 'professeur',
    ':type'        => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_classe_reference
 *
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_classe_reference( $groupe_ref , $groupe_id=FALSE )
{
  $DB_SQL = 'SELECT groupe_id ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_ref=:groupe_ref ';
  $DB_SQL.= ($groupe_id) ? 'AND groupe_id!=:groupe_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':groupe_type' => 'classe',
    ':groupe_ref'  => $groupe_ref,
    ':groupe_id'   => $groupe_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_groupe_reference
 *
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_groupe_reference( $groupe_ref , $groupe_id=FALSE )
{
  $DB_SQL = 'SELECT groupe_id ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type=:groupe_type AND groupe_ref=:groupe_ref ';
  $DB_SQL.= ($groupe_id) ? 'AND groupe_id!=:groupe_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':groupe_type' => 'groupe',
    ':groupe_ref'  => $groupe_ref,
    ':groupe_id'   => $groupe_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_groupe_par_admin
 *
 * @param string $groupe_type   'classe' | 'groupe'
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */
public static function DB_ajouter_groupe_par_admin( $groupe_type , $groupe_ref , $groupe_nom , $niveau_id )
{
  $config_defaut = ($groupe_type=='classe') ? 'defaut' : '' ;
  $DB_SQL = 'INSERT INTO sacoche_groupe(groupe_type, groupe_ref, groupe_nom, niveau_id, groupe_configuration_releve, groupe_configuration_bulletin, groupe_configuration_livret) ';
  $DB_SQL.= 'VALUES(                   :groupe_type,:groupe_ref,:groupe_nom,:niveau_id,              :config_releve,              :config_bulletin,              :config_livret) ';
  $DB_VAR = array(
    ':groupe_type'     => $groupe_type,
    ':groupe_ref'      => $groupe_ref,
    ':groupe_nom'      => $groupe_nom,
    ':niveau_id'       => $niveau_id,
    ':config_releve'   => $config_defaut,
    ':config_bulletin' => $config_defaut,
    ':config_livret'   => $config_defaut,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_groupe_par_prof
 *
 * @param int    $prof_id
 * @param string $groupe_type   'besoin' | 'eval'
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */
public static function DB_ajouter_groupe_par_prof( $prof_id , $groupe_type , $groupe_nom , $niveau_id )
{
  $DB_SQL = 'INSERT INTO sacoche_groupe(groupe_type, groupe_ref, groupe_nom, niveau_id) ';
  $DB_SQL.= 'VALUES(                   :groupe_type,:groupe_ref,:groupe_nom,:niveau_id) ';
  $DB_VAR = array(
    ':groupe_type' => $groupe_type,
    ':groupe_ref'  => '',
    ':groupe_nom'  => $groupe_nom,
    ':niveau_id'   => $niveau_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $groupe_id = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
  // Y associer automatiquement le prof, en responsable du groupe
  $DB_SQL = 'INSERT INTO sacoche_jointure_user_groupe ( user_id, groupe_id, jointure_pp) ';
  $DB_SQL.= 'VALUES                                   (:user_id,:groupe_id,:jointure_pp)';
  $DB_VAR = array(
    ':user_id'     => $prof_id,
    ':groupe_id'   => $groupe_id,
    ':jointure_pp' => 1,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Retour de l'id du groupe
  return $groupe_id;
}

/**
 * modifier_chef ; pour une classe donnée ou toutes les classes
 *
 * @param int $chef_id
 * @param int $groupe_id   facultatif (toutes les classes sinon)
 * @return void
 */
public static function DB_modifier_chef( $chef_id , $groupe_id = NULL )
{
  $where = ($groupe_id) ? 'groupe_id=:groupe_id' : 'groupe_type="classe"' ;
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_chef_id=:chef_id ';
  $DB_SQL.= 'WHERE '.$where;
  $DB_VAR = array(
    ':chef_id'   => $chef_id,
    ':groupe_id' => $groupe_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_groupe_par_admin ; on ne touche pas à 'groupe_type'
 *
 * @param int    $groupe_id
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */
public static function DB_modifier_groupe_par_admin( $groupe_id , $groupe_ref , $groupe_nom , $niveau_id )
{
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_ref=:groupe_ref,groupe_nom=:groupe_nom,niveau_id=:niveau_id ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':groupe_id'  => $groupe_id,
    ':groupe_ref' => $groupe_ref,
    ':groupe_nom' => $groupe_nom,
    ':niveau_id'  => $niveau_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_groupe_par_prof ; on ne touche pas à "groupe_type" (ni à "groupe_ref" qui reste vide)
 *
 * @param int    $groupe_id
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */
public static function DB_modifier_groupe_par_prof( $groupe_id , $groupe_nom , $niveau_id )
{
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_nom=:groupe_nom,niveau_id=:niveau_id ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':groupe_id'  => $groupe_id,
    ':groupe_nom' => $groupe_nom,
    ':niveau_id'  => $niveau_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_user_groupe_par_admin
 *
 * @param int    $user_id
 * @param string $user_profil_type   'eleve' ou 'professeur'
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' ou 'groupe'
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public static function DB_modifier_liaison_user_groupe_par_admin( $user_id , $user_profil_type , $groupe_id , $groupe_type , $etat )
{
  // Dans le cas d'un élève et d'une classe, ce n'est pas dans la table de jointure mais dans la table user que ça se passe
  if( ($user_profil_type=='eleve') && ($groupe_type=='classe') )
  {
    $DB_SQL = 'UPDATE sacoche_user ';
    if($etat)
    {
      $DB_SQL.= 'SET eleve_classe_id=:groupe_id ';
      $DB_SQL.= 'WHERE user_id=:user_id ';
    }
    else
    {
      $DB_SQL.= 'SET eleve_classe_id=0 ';
      $DB_SQL.= 'WHERE user_id=:user_id AND eleve_classe_id=:groupe_id ';
    }
  }
  else
  {
    if($etat)
    {
      $DB_SQL = 'INSERT IGNORE INTO sacoche_jointure_user_groupe ( user_id,  groupe_id) ';
      $DB_SQL.= 'VALUES                                          (:user_id, :groupe_id) ';
    }
    else
    {
      $DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
      $DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
    }
  }
  $DB_VAR = array(
    ':user_id'   => $user_id,
    ':groupe_id' => $groupe_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_user_groupe_par_prof
 * Utilisé pour [1] la gestion d'évaluations de type 'eval', ainsi que [2] la gestion de groupes de besoin.
 *
 * @param int    $prof_id
 * @param int    $groupe_id
 * @param array  $tab_eleves   tableau des id des élèves
 * @param array  $tab_profs    tableau des id des profs (sans objet pour [1]), SANS le responsable du groupe
 * @param string $mode         'creer' pour un insert dans un nouveau groupe || 'substituer' pour une maj delete / insert || 'ajouter' pour maj insert uniquement (sans objet pour [2])
 * @param int    $devoir_id    pour supprimer les notes saisies associées (uniquement pour [1]) ; sert aussi à savoir si on est dans le cas [1] ou [2]
 * @return void
 */
public static function DB_modifier_liaison_user_groupe_par_prof( $prof_id , $groupe_id , $tab_eleves , $tab_profs , $mode , $devoir_id )
{
  $tab_users = array_merge($tab_eleves,$tab_profs);
  // -> on récupère la liste des users actuels déjà associés au groupe (pour la comparer à la liste transmise)
  if($mode!='creer')
  {
    // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
    $DB_SQL = 'SELECT GROUP_CONCAT(user_id SEPARATOR " ") AS users_listing ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_user USING(user_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
    $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
    $DB_SQL.= ($devoir_id) ? 'AND user_profil_type=:profil_type ' : 'AND user_id!=:prof_id ' ; // Pour [1] on ne s'intéresse qu'aux élèves ; pour [2] on s'intéresse à tout le monde sauf au prof responsable du groupe (non transmis)
    
    $DB_SQL.= 'GROUP BY groupe_id';
    $DB_VAR = array(
      ':groupe_id'   => $groupe_id,
      ':profil_type' => 'eleve',
      ':prof_id'     => $prof_id,
    );
    $users_listing = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $tab_users_avant = ($users_listing) ? explode(' ',$users_listing) : array() ;
  }
  else
  {
    $tab_users_avant = array() ;
  }
  // -> on supprime si besoin les anciens élèves associés à ce groupe qui ne sont plus dans la liste transmise
  // -> on supprime si besoin les saisies des anciens élèves associés à ce devoir qui ne sont plus dans la liste transmise
  //   (pour les saisies superflues concernant les items, voir DB_modifier_liaison_devoir_item() )
  if($mode=='substituer')
  {
    $tab_users_moins = array_diff($tab_users_avant,$tab_users);
    if(count($tab_users_moins))
    {
      $chaine_user_id = implode(',',$tab_users_moins);
      $DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
      $DB_SQL.= 'WHERE user_id IN('.$chaine_user_id.') AND groupe_id=:groupe_id ';
      $DB_VAR = array(':groupe_id'=>$groupe_id);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      if($devoir_id)
      {
        $DB_SQL = 'DELETE FROM sacoche_saisie ';
        $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id IN('.$chaine_user_id.')';
        $DB_VAR = array(':devoir_id'=>$devoir_id);
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
  }
  // -> on ajoute si besoin les nouveaux élèves dans la liste transmise qui n'étaient pas déjà associés à ce groupe
  $tab_users_plus = array_diff($tab_users,$tab_users_avant);
  if(count($tab_users_plus))
  {
    foreach($tab_users_plus as $user_id)
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_user_groupe (user_id,groupe_id) ';
      $DB_SQL.= 'VALUES(:user_id,:groupe_id)';
      $DB_VAR = array(
        ':user_id'   => $user_id,
        ':groupe_id' => $groupe_id,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
}

/**
 * modifier_liaison_professeur_principal
 *
 * @param int    $user_id
 * @param int    $groupe_id
 * @param bool   $etat          TRUE pour ajouter/modifier une liaison ; FALSE pour retirer une liaison
 * @return void
 */
public static function DB_modifier_liaison_professeur_principal($user_id,$groupe_id,$etat)
{
  $pp = ($etat) ? 1 : 0 ;
  $DB_SQL = 'UPDATE sacoche_jointure_user_groupe ';
  $DB_SQL.= 'SET jointure_pp=:pp ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':user_id'   => $user_id,
    ':groupe_id' => $groupe_id,
    ':pp'        => $pp,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un groupe
 *
 * On ne supprime pas les jointures avec les bulletins (qui peuvent être accessibles depuis un autre groupe...).
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=FALSE) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=FALSE) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 *
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' | 'groupe' | 'besoin' | 'eval'
 * @param bool   $with_devoir
 * @return void
 */
public static function DB_supprimer_groupe_par_admin( $groupe_id , $groupe_type , $with_devoir=TRUE )
{
  $tab_tables = array( 'sacoche_groupe' , 'sacoche_jointure_user_groupe' );
  if( ($groupe_type=='classe') || ($groupe_type=='groupe') )
  {
    $tab_tables[] = 'sacoche_jointure_groupe_periode';
  }
  if($groupe_type=='classe')
  {
    $tab_tables[] = 'sacoche_livret_jointure_groupe';
  }
  $DB_VAR = array(':groupe_id'=>$groupe_id);
  foreach( $tab_tables as $table )
  {
    $DB_SQL = 'DELETE FROM '.$table.' WHERE groupe_id=:groupe_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // Il faut aussi supprimer les ap / epi / parcours portant sur le groupe, avec les jointures aux profs/matières
  $tab_tables = array( 'ap' , 'epi' , 'parcours' );
  foreach( $tab_tables as $table )
  {
    $DB_SQL = 'DELETE sacoche_livret_'.$table.', sacoche_livret_jointure_'.$table.'_prof ';
    $DB_SQL.= 'FROM sacoche_livret_'.$table.' ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_'.$table.'_prof USING (livret_'.$table.'_id) ';
    $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // Il faut aussi supprimer les évaluations portant sur le groupe
  if($with_devoir)
  {
    $DB_SQL = 'DELETE sacoche_devoir , sacoche_jointure_devoir_item , sacoche_jointure_devoir_prof , sacoche_jointure_devoir_eleve ';
    $DB_SQL.= 'FROM sacoche_devoir ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_eleve USING (devoir_id) ';
    $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // Il faut aussi supprimer les destinataires de messages portant sur le groupe
  if($groupe_type!='eval')
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_message_destinataire ';
    $DB_SQL.= 'WHERE destinataire_id=:groupe_id AND destinataire_type="'.$groupe_type.'" ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // Sans oublier le champ pour les affectations des élèves dans une classe
  if($groupe_type=='classe')
  {
    $DB_SQL = 'UPDATE sacoche_user ';
    $DB_SQL.= 'SET eleve_classe_id=0 ';
    $DB_SQL.= 'WHERE eleve_classe_id=:groupe_id';
    $DB_VAR = array(':groupe_id'=>$groupe_id);
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * supprimer_groupe_par_prof
 * Par défaut, on supprime aussi les devoirs associés ($with_devoir=TRUE), mais on conserve les notes, qui deviennent orphelines et non éditables ultérieurement.
 * Mais on peut aussi vouloir dans un second temps ($with_devoir=FALSE) supprimer les devoirs associés avec leurs notes en utilisant DB_supprimer_devoir_et_saisies().
 *
 * @param int    $groupe_id
 * @param string $groupe_type   'besoin' | 'eval'
 * @param bool   $with_devoir
 * @return void
 */
public static function DB_supprimer_groupe_par_prof( $groupe_id , $groupe_type , $with_devoir=TRUE )
{
  // Il faut aussi supprimer les jointures avec les utilisateurs
  // Pas de jointures avec les périodes pour ces regroupements
  // Il faut aussi supprimer les évaluations portant sur le groupe
  $jointure_devoir_delete = ($with_devoir) ? ', sacoche_devoir , sacoche_jointure_devoir_item , sacoche_jointure_devoir_prof , sacoche_jointure_devoir_eleve ' : '' ;
  $jointure_devoir_join   = ($with_devoir) ? 'LEFT JOIN sacoche_devoir USING (groupe_id) LEFT JOIN sacoche_jointure_devoir_item USING (devoir_id) LEFT JOIN sacoche_jointure_devoir_prof USING (devoir_id) LEFT JOIN sacoche_jointure_devoir_eleve USING (devoir_id) ' : '' ;
  // Il faut aussi supprimer les destinataires de messages portant sur le groupe
  $jointure_message_delete = ($groupe_type!='eval') ? ', sacoche_jointure_message_destinataire ' : '' ;
  $jointure_message_join   = ($groupe_type!='eval') ? 'LEFT JOIN sacoche_jointure_message_destinataire ON sacoche_groupe.groupe_id=sacoche_jointure_message_destinataire.destinataire_id AND destinataire_type="'.$groupe_type.'" ' : '' ;
  // Let's go
  $DB_SQL = 'DELETE sacoche_groupe , sacoche_jointure_user_groupe '.$jointure_devoir_delete.$jointure_message_delete;
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= $jointure_devoir_join.$jointure_message_join;
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(':groupe_id'=>$groupe_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>