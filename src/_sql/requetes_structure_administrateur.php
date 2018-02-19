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
// Ces méthodes ne concernent qu'un administrateur.

class DB_STRUCTURE_ADMINISTRATEUR extends DB
{

/**
 * rechercher_users
 *
 * @param string   champ_nom
 * @param string   champ_val
 * @return array
 */
public static function DB_rechercher_users($champ_nom,$champ_val)
{
  $DB_SQL = 'SELECT user_id, user_sconet_id, user_sconet_elenoet, user_reference, user_profil_sigle, user_genre, user_nom, user_prenom, user_email, user_login, user_sortie_date, user_id_ent, user_id_gepi, user_profil_nom_long_singulier ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_'.$champ_nom.' LIKE :champ_val ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(':champ_val'=>$champ_val);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * rechercher_eleves
 *
 * @param string   user_nom_like
 * @param string   user_profil_type
 * @param int      user_statut
 * @return array
 */
public static function DB_rechercher_user_for_fusion( $user_nom_like , $user_profil_type , $user_statut )
{
  $where_profil = ($user_profil_type=='eleve') ? 'user_profil_type="eleve"' : 'user_profil_type IN("professeur","directeur")' ;
  $where_statut = ($user_statut) ? 'user_sortie_date>NOW() ' : 'user_sortie_date<NOW() ' ;
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_login ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_nom LIKE :user_nom_like AND '.$where_profil.' AND '.$where_statut;
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array( ':user_nom_like' => $user_nom_like );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_cibles
 *
 * @param string   $listing_user_id   id des utilisateurs séparés par des virgules
 * @param string   $listing_champs    nom des champs séparés par des virgules
 * @param string   $avec_info         facultatif ; "classe" pour récupérer la classe des élèves | "enfant" pour récupérer une classe et un enfant associé à un parent
 * @return array
 */
public static function DB_lister_users_cibles( $listing_user_id , $listing_champs , $avec_info='' )
{
  if($avec_info=='classe')
  {
    $DB_SQL = 'SELECT '.$listing_champs.',groupe_nom AS info ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
    $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, user_nom ASC, user_prenom ASC';
  }
  elseif($avec_info=='enfant')
  {
    // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
    $DB_SQL = 'SELECT '.$listing_champs.',GROUP_CONCAT( CONCAT(groupe_ref," ",enfant.user_nom) SEPARATOR " - ") AS info ';
    $DB_SQL.= 'FROM sacoche_user AS parent ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON parent.user_id=sacoche_jointure_parent_eleve.parent_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_jointure_parent_eleve.eleve_id=enfant.user_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe ON enfant.eleve_classe_id=sacoche_groupe.groupe_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $DB_SQL.= 'WHERE parent.user_id IN('.$listing_user_id.') AND enfant.user_sortie_date>NOW() ';
    $DB_SQL.= 'GROUP BY parent.user_id ' ;
    // retiré car "ORDER BY clause is not in GROUP BY clause and contains nonaggregated column which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by"
    // $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, enfant.user_nom ASC, enfant.user_prenom ASC ';
  }
  else
  {
    $DB_SQL = 'SELECT '.$listing_champs.' ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
    $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  }
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_info_enfants_par_parent
 *
 * @param string   $listing_parent_id   id des parents séparés par des virgules
 * @return array
 */
public static function DB_lister_info_enfants_par_parent($listing_parent_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT parent.user_id as parent_id, GROUP_CONCAT( CONCAT(groupe_ref," ",enfant.user_nom) SEPARATOR " - ") AS info ';
  $DB_SQL.= 'FROM sacoche_user AS parent ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON parent.user_id=sacoche_jointure_parent_eleve.parent_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_jointure_parent_eleve.eleve_id=enfant.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON enfant.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE parent.user_id IN('.$listing_parent_id.') AND enfant.user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY parent.user_id ' ;
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_ref ASC, enfant.user_nom ASC, enfant.user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_adresses_parents
 *
 * @param void
 * @return array
 */
public static function DB_lister_adresses_parents()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_parent_adresse ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_parents_par_eleve
 *
 * @param void
 * @return array
 */
public static function DB_lister_parents_par_eleve()
{
  $DB_SQL = 'SELECT eleve.user_id AS eleve_id,   eleve.user_sconet_id AS eleve_sconet_id,   eleve.user_nom AS eleve_nom,   eleve.user_prenom AS eleve_prenom,   ';
  $DB_SQL.=        'parent.user_id AS parent_id, parent.user_sconet_id AS parent_sconet_id, parent.user_nom AS parent_nom, parent.user_prenom AS parent_prenom, ';
  $DB_SQL.=        'sacoche_jointure_parent_eleve.resp_legal_num ';
  $DB_SQL.= 'FROM sacoche_user AS eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil AS eleve_profil ON eleve.user_profil_sigle=eleve_profil.user_profil_sigle ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'WHERE eleve_profil.user_profil_type="eleve" ';
  $DB_SQL.= 'ORDER BY eleve_nom ASC, eleve_prenom ASC, resp_legal_num ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_parents_actuels_avec_infos_for_eleve
 *
 * @param int   $eleve_id
 * @return array
 */
public static function DB_lister_parents_actuels_avec_infos_for_eleve($eleve_id)
{
  $DB_SQL = 'SELECT parent.user_id, parent.user_nom, parent.user_prenom, parent.user_login, sacoche_parent_adresse.*, resp_legal_num ';
  $DB_SQL.= 'FROM sacoche_user AS eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_parent_adresse ON sacoche_jointure_parent_eleve.parent_id=sacoche_parent_adresse.parent_id ';
  $DB_SQL.= 'WHERE eleve.user_id=:eleve_id AND parent.user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY parent.user_id ';
  $DB_SQL.= 'ORDER BY resp_legal_num ASC ';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  $DB_TAB_parents = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
  if(empty($DB_TAB_parents))
  {
    return array();
  }
  $listing_parent_id = implode(',',array_keys($DB_TAB_parents));
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT parent_id, GROUP_CONCAT( CONCAT(enfant.user_nom," ",enfant.user_prenom," (resp légal ",resp_legal_num,")") SEPARATOR " ; ") AS enfants_liste ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_jointure_parent_eleve.eleve_id=enfant.user_id ';
  $DB_SQL.= 'WHERE sacoche_jointure_parent_eleve.parent_id IN('.$listing_parent_id.') AND enfant.user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY parent_id ';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  $DB_TAB_enfants = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
  $DB_TAB = array();
  foreach($DB_TAB_parents AS $id => $tab)
  {
    $DB_TAB[] = array_merge( $DB_TAB_parents[$id] , $DB_TAB_enfants[$id] , array('parent_id'=>$id) );
  }
  return $DB_TAB;
}

/**
 * lister_users
 *
 * @param string|array   $profil_type   'eleve' / 'parent' / 'professeur' / 'directeur' / 'administrateur' / ou par exemple array('eleve','professeur','directeur')
 * @param int            $statut        1 pour actuels, 0 pour anciens, 2 pour tout le monde
 * @param string         $liste_champs  liste des champs séparés par des virgules
 * @param bool           $with_classe   TRUE pour récupérer le nom de la classe de l'élève / FALSE sinon
 * @param bool           $tri_statut    TRUE pour trier par statut décroissant (les actifs en premier), FALSE par défaut
 * @return array
 */
public static function DB_lister_users( $profil_type , $statut , $liste_champs , $with_classe , $tri_statut=FALSE )
{
  $DB_VAR = array();
  $left_join = 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $where     = '';
  $liste_champs .= ( $tri_statut || ($statut==2) ) ? ', (user_sortie_date>NOW()) AS statut ' : ' ' ;
  $order_by      = ($tri_statut) ? 'statut DESC, ' : '' ;
  if(is_string($profil_type))
  {
    $where .= 'user_profil_type=:profil_type ';
    $DB_VAR[':profil_type'] = $profil_type;
  }
  else
  {
    foreach($profil_type as $key => $val)
    {
      $or = ($key) ? 'OR ' : '( ' ;
      $where .= $or.'user_profil_type=:profil_type'.$key.' ';
      $DB_VAR[':profil_type'.$key] = $val;
    }
    $where .= ') ';
    $order_by .= 'user_profil_type ASC, ';
  }
  if($with_classe)
  {
    $liste_champs .= ', groupe_ref, groupe_nom ';
    $left_join .= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
    $left_join .= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
    $order_by  .= 'niveau_ordre ASC, groupe_ref ASC, ';
  }
  $where .= ($statut==1) ? 'AND user_sortie_date>NOW() ' : ( ($statut==0) ? 'AND user_sortie_date<NOW() ' : '' ) ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = 'SELECT '.$liste_champs;
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= $left_join;
  $DB_SQL.= 'WHERE '.$where;
  $DB_SQL.= 'ORDER BY '.$order_by.'user_nom ASC, user_prenom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_parents_avec_infos_enfants
 *
 * @param bool     $with_adresse
 * @param int      $statut            1 pour actuel, 0 pour ancien
 * @param string   $debut_nom         premières lettres du nom
 * @param string   $debut_prenom      premières lettres du prénom
 * @param string   $liste_parent_id   liste des id de parents
 * @return array
 */
public static function DB_lister_parents_avec_infos_enfants($with_adresse,$statut,$debut_nom='',$debut_prenom='',$liste_parent_id='')
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $test_date_sortie_parent = ($statut) ? 'parent.user_sortie_date>NOW() ' : 'parent.user_sortie_date<NOW() ' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $test_date_sortie_enfant = ($statut) ? 'AND ( eleve.user_sortie_date>NOW() || eleve.user_id IS NULL ) ' : '' ; // Pour un compte parent actif, on compte les enfants actifs, tout en récupérant les parents rattachés à aucun enfant ; pour un compte parent inactif, aucun test afin de pouvoir aussi récupérer les parents rattachés à des enfants inactifs
  $DB_SQL = 'SELECT ' ;
  $DB_SQL.= ($with_adresse) ? 'parent.user_id, parent.user_genre, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.*, ' : 'parent.*, ' ;
  $DB_SQL.= 'GROUP_CONCAT( CONCAT(eleve.user_nom," ",eleve.user_prenom," (resp légal ",resp_legal_num,")") SEPARATOR "§BR§") AS enfants_liste, ';
  $DB_SQL.= 'COUNT(eleve.user_id) AS enfants_nombre ';
  $DB_SQL.= 'FROM sacoche_user AS parent ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
  $DB_SQL.= ($with_adresse) ? 'LEFT JOIN sacoche_parent_adresse ON parent.user_id=sacoche_parent_adresse.parent_id ' : '' ;
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON parent.user_id=sacoche_jointure_parent_eleve.parent_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS eleve ON sacoche_jointure_parent_eleve.eleve_id=eleve.user_id ';
  $DB_SQL.= 'WHERE parent_profil.user_profil_type="parent" AND '.$test_date_sortie_parent.$test_date_sortie_enfant; 
  if(!$liste_parent_id)
  {
    $DB_SQL.= ($debut_nom)    ? 'AND parent.user_nom LIKE :nom ' : '' ;
    $DB_SQL.= ($debut_prenom) ? 'AND parent.user_prenom LIKE :prenom ' : '' ;
  }
  else
  {
    $DB_SQL.= 'AND parent.user_id IN('.$liste_parent_id.') ';
  }
  $DB_SQL.= 'GROUP BY parent.user_id ';
  $DB_SQL.= 'ORDER BY parent.user_nom ASC, parent.user_prenom ASC ';
  $DB_VAR = array(
    ':nom'    => $debut_nom   .'%',
    ':prenom' => $debut_prenom.'%',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_parents_adresses_par_enfant
 *
 * @return array
 */
public static function DB_lister_parents_adresses_par_enfant()
{
  $DB_SQL = 'SELECT eleve_id, parent_id, sacoche_parent_adresse.* ';
  $DB_SQL.= 'FROM sacoche_user AS enfant ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil AS enfant_profil ON enfant.user_profil_sigle=enfant_profil.user_profil_sigle ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
  $DB_SQL.= 'LEFT JOIN sacoche_parent_adresse USING (parent_id) ';
  $DB_SQL.= 'WHERE enfant_profil.user_profil_type="eleve" AND enfant.user_sortie_date>NOW() AND parent_profil.user_profil_type="parent" AND parent.user_sortie_date>NOW() ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * lister_parents_homonymes
 *
 * @return array
 */
public static function DB_lister_parents_homonymes()
{
  $DB_SQL = 'SELECT user_nom, user_prenom, CONVERT( GROUP_CONCAT(user_id SEPARATOR ",") , CHAR) AS identifiants , COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING(user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type="parent" AND user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY user_nom,user_prenom HAVING nombre>1 ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_users_avec_groupe
 *
 * @param string   $profil_type    'eleve' | 'professeur'
 * @param bool     $only_actuels   TRUE pour les actuels uniquement / FALSE pour tout le monde (actuel ou ancien)
 * @return array
 */
public static function DB_lister_users_avec_groupe($profil_type,$only_actuels)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND groupe_type=:type ';
  $DB_SQL.= ($only_actuels) ? 'AND user_sortie_date>NOW() ' : '' ;
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(
    ':profil_type' => $profil_type,
    ':type'        => 'groupe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_desactives_obsoletes
 *
 * @param void
 * @return array
 */
public static function DB_lister_users_desactives_obsoletes()
{
  $DB_SQL = 'SELECT user_id, user_profil_sigle ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_sortie_date < DATE_SUB(NOW(),INTERVAL 3 YEAR) ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_devoirs_id_disponibles
 *
 * @param void
 * @return array
 */
public static function DB_lister_devoirs_id_disponibles()
{
  $DB_SQL = 'SELECT DISTINCT(devoir_id) ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $tab_devoirs_pris = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $DB_SQL = 'SELECT MAX(devoir_id) ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $devoir_id_max = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_devoirs_tous = range(1, $devoir_id_max);
  return array_diff( $tab_devoirs_tous , $tab_devoirs_pris);
}

/**
 * lister_profils_parametres
 *
 * @param string   $listing_champs (sans indiquer user_profil_sigle, par défaut dans la réponse)
 * @param bool     $only_actif
 * @param string   $only_profils_types
 * @return array
 */
public static function DB_lister_profils_parametres( $listing_champs , $only_actif , $only_profils_types=FALSE )
{
  $DB_SQL = 'SELECT user_profil_sigle, '.$listing_champs.' ';
  $DB_SQL.= 'FROM sacoche_user_profil ';
  $DB_SQL.= 'WHERE user_profil_structure=1 AND user_profil_disponible=1 ';
  $DB_SQL.= ($only_actif) ? 'AND ( user_profil_actif=1 OR user_profil_obligatoire=1 ) ' : '' ; // Sécurité au cas où un profil obligatoire aurait été déselectionné...
  $DB_SQL.= ($only_profils_types) ? ( is_string($only_profils_types) ? 'AND user_profil_type="'.$only_profils_types.'" ' : 'AND user_profil_type IN("'.implode('","',$only_profils_types).'") ' ) : '' ;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_matieres_etabl
 *
 * @param void
 * @return int
 */
public static function DB_compter_matieres_etabl()
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_active=1 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_niveaux_etabl
 *
 * @param bool $with_specifiques
 * @return int
 */
public static function DB_compter_niveaux_etabl($with_specifiques)
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($with_specifiques) ? '' : 'LEFT JOIN sacoche_niveau_famille USING (niveau_famille_id) ';
  $DB_SQL.= 'WHERE niveau_actif=1 ';
  $DB_SQL.= ($with_specifiques) ? '' : 'AND niveau_famille_categorie=3 ';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_users_suivant_statut
 *
 * @param string|array   $profil_type   'eleve' / 'professeur' / 'directeur' / 'administrateur' / ou par exemple array('eleve','professeur','directeur')
 * @return array   [0]=>nb actuels , [1]=>nb anciens
 */
public static function DB_compter_users_suivant_statut($profil_type)
{
  $DB_VAR = array();
  if(is_string($profil_type))
  {
    $where = 'user_profil_type=:profil_type ';
    $DB_VAR[':profil_type'] = $profil_type;
  }
  else
  {
    foreach($profil_type as $key => $val)
    {
      $DB_VAR[':profil_type'.$key] = $val;
      $profil_type[$key] = ':profil_type'.$key;
    }
    $where = 'user_profil_type IN('.implode(',',$profil_type).') ';
  }
  $DB_SQL = 'SELECT (user_sortie_date>NOW()) AS statut, COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE '.$where;
  $DB_SQL.= 'GROUP BY statut';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
  $nb_actuels = ( (!empty($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1]['nombre'] : 0 ;
  $nb_anciens = ( (!empty($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0]['nombre'] : 0 ;
  return array($nb_actuels,$nb_anciens);
}

/**
 * Recherche si un identifiant d'utilisateur est déjà pris (sauf éventuellement l'utilisateur concerné)
 *
 * @param string $champ_nom      sans le préfixe 'user_' : login | sconet_id | sconet_elenoet | reference | id_ent | id_gepi
 * @param string $champ_valeur   la valeur testée
 * @param int    $user_id        inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @param string $profil_type    si transmis alors recherche parmi les utilisateurs de même type de profil (sconet_id|sconet_elenoet|reference), sinon alors parmi tous les utilisateurs de l'établissement (login|id_ent|id_gepi)
 * @return null|bool             NULL si pas trouvé, FALSE si trouvé mais identique à $user_id transmis, TRUE si trouvé ($user_id non transmis ou différent), l'id trouvé dans le cas exceptionnel d'un développeur pour le compte administrateur "superviseur"
 */
public static function DB_tester_utilisateur_identifiant($champ_nom,$champ_valeur,$user_id=NULL,$profil_type=NULL)
{
  $DB_SQL = 'SELECT user_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= ($profil_type) ? 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ' : '' ;
  $DB_SQL.= 'WHERE user_'.$champ_nom.'=:champ_valeur ';
  $DB_SQL.= ($profil_type) ? 'AND user_profil_type=:profil_type ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':champ_valeur' => $champ_valeur,
    ':profil_type'  => $profil_type,
    ':user_id'      => $user_id,
  );
  $find_user_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if($find_user_id!==NULL)
  {
    if( $_SESSION['USER_PROFIL_TYPE'] != 'developpeur' )
    {
      $find_user_id = ( $find_user_id && ($user_id!=$find_user_id) ) ? TRUE : FALSE ;
    }
  }
  return $find_user_id;
}

/**
 * rechercher_login_disponible (parmi tout le personnel de l'établissement)
 *
 * @param string $login_pris
 * @return string
 */
public static function DB_rechercher_login_disponible($login_pris)
{
  $nb_chiffres = max(1 , LOGIN_LONGUEUR_MAX-mb_strlen($login_pris) );
  do
  {
    $login_tronque = mb_substr($login_pris,0,LOGIN_LONGUEUR_MAX-$nb_chiffres);
    $DB_SQL = 'SELECT user_login ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'WHERE user_login LIKE :user_login';
    $DB_VAR = array( ':user_login' => $login_tronque.'%' );
    $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $max_result = pow(10,$nb_chiffres);
    $nb_chiffres += 1;
  }
  while (count($DB_COL)>=$max_result);
  $login_nombre = 1;
  do
  {
    $login_disponible = $login_tronque.$login_nombre;
    $login_nombre++;
  }
  while (in_array($login_disponible,$DB_COL));
  return $login_disponible ;
}

/**
 * ajouter_saisies
 *
 * @param array   [ $eleve_id , $item_id , $saisie_note , $saisie_info , $saisie_date , $saisie_visible_date , $devoir_id ]
 * @param int     nombre d'enregistrements à effectuer
 * @return void
 */
public static function DB_ajouter_saisies( $tab_saisies , $nb_saisies )
{
  $TAB_SQL = array();
  $paquet = 1000;
  foreach($tab_saisies as $key => $tab)
  {
    $num = $key+1;
    list( $eleve_id , $item_id , $saisie_note , $saisie_info , $saisie_date , $saisie_visible_date , $devoir_id ) = $tab;
    $TAB_SQL[] = '(0,'.$eleve_id.','.$devoir_id.','.$item_id.',"'.$saisie_date.'","'.$saisie_note.'","'.str_replace('"','\"',$saisie_info).'","'.$saisie_visible_date.'")';
    if( ($num%$paquet==0) || ($num==$nb_saisies) )
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_saisie(prof_id, eleve_id, devoir_id, item_id, saisie_date, saisie_note, saisie_info, saisie_visible_date) VALUES '.implode(',', $TAB_SQL) , NULL);
      $TAB_SQL = array();
    }
  }
}

/**
 * ajouter_adresse_parent
 *
 * @param int    $parent_id
 * @param array  $tab_adresse
 * @return void
 */
public static function DB_ajouter_adresse_parent($parent_id,$tab_adresse)
{
  $DB_SQL = 'INSERT INTO sacoche_parent_adresse(parent_id,adresse_ligne1,adresse_ligne2,adresse_ligne3,adresse_ligne4,adresse_postal_code,adresse_postal_libelle,adresse_pays_nom) ';
  $DB_SQL.= 'VALUES(                           :parent_id,       :ligne1,       :ligne2,       :ligne3,       :ligne4,       :postal_code,       :postal_libelle,       :pays_nom)';
  $DB_VAR = array(
    ':parent_id'      => $parent_id,
    ':ligne1'         => $tab_adresse[0],
    ':ligne2'         => $tab_adresse[1],
    ':ligne3'         => $tab_adresse[2],
    ':ligne4'         => $tab_adresse[3],
    ':postal_code'    => $tab_adresse[4],
    ':postal_libelle' => $tab_adresse[5],
    ':pays_nom'       => $tab_adresse[6],
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_jointure_parent_eleve
 *
 * @param int    $parent_id
 * @param int    $eleve_id
 * @param int    $resp_legal_num
 * @return void
 */
public static function DB_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num)
{
  $DB_SQL = 'INSERT INTO sacoche_jointure_parent_eleve(parent_id, eleve_id, resp_legal_num) ';
  $DB_SQL.= 'VALUES(                                  :parent_id,:eleve_id,:resp_legal_num)';
  $DB_VAR = array(
    ':parent_id'      => $parent_id,
    ':eleve_id'       => $eleve_id,
    ':resp_legal_num' => $resp_legal_num,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * remplacer_structure_origine
 *
 * @param string $uai
 * @param string $denomination
 * @param string $localisation
 * @param string $courriel
 * @return void
 */
public static function DB_remplacer_structure_origine( $uai , $denomination , $localisation , $courriel )
{
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_structure_origine(structure_uai, structure_denomination, structure_localisation, structure_courriel) ';
  $DB_SQL.= 'VALUES(                              :structure_uai,:structure_denomination,:structure_localisation,:structure_courriel) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE ';
  $DB_SQL.= 'structure_denomination=:structure_denomination, structure_localisation=:structure_localisation, structure_courriel=:structure_courriel ';
  $DB_VAR = array(
    ':structure_uai'          => $uai,
    ':structure_denomination' => $denomination,
    ':structure_localisation' => $localisation,
    ':structure_courriel'     => $courriel,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Dupliquer pour tous les utilisateurs une série d'identifiants vers un autre champ (exemples : id_gepi=id_ent | id_gepi=login | id_ent=id_gepi | id_ent=login )
 *
 * @param string $champ_depart
 * @param string $champ_arrive
 * @return void
 */
public static function DB_recopier_identifiants($champ_depart,$champ_arrive)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_'.$champ_arrive.'=user_'.$champ_depart.' ';
  $DB_SQL.= 'WHERE user_'.$champ_depart.'!="" ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_adresse_parent
 *
 * @param string $parent_id
 * @param array  $tab_adresse
 * @return int
 */
public static function DB_modifier_adresse_parent($parent_id,$tab_adresse)
{
  $DB_SQL = 'UPDATE sacoche_parent_adresse ';
  $DB_SQL.= 'SET adresse_ligne1=:ligne1, adresse_ligne2=:ligne2, adresse_ligne3=:ligne3, adresse_ligne4=:ligne4, adresse_postal_code=:postal_code, adresse_postal_libelle=:postal_libelle, adresse_pays_nom=:pays_nom ';
  $DB_SQL.= 'WHERE parent_id=:parent_id ';
  $DB_VAR = array(
    ':parent_id'      => $parent_id,
    ':ligne1'         => $tab_adresse[0],
    ':ligne2'         => $tab_adresse[1],
    ':ligne3'         => $tab_adresse[2],
    ':ligne4'         => $tab_adresse[3],
    ':postal_code'    => $tab_adresse[4],
    ':postal_libelle' => $tab_adresse[5],
    ':pays_nom'       => $tab_adresse[6],
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier un ou plusieurs paramètres d'un utilisateur
 *
 * - Certains champ ("user_langue", "user_daltonisme", "user_connexion_date", "user_param_accueil", "user_param_menu", "user_param_favori") ne sont ici forcés que via la fusion de comptes élèves.
 * - On peut envisager une modification de "profil_sigle" entre personnels.
 * - La mise à jour de la table [sacoche_user_switch] s'effectue lors de l'initialisation annuelle.
 *
 * @param int     $user_id
 * @param array   de la forme ':champ' => $val (voir ci-dessous)
 * @return void
 */
public static function DB_modifier_user($user_id,$DB_VAR)
{
  $tab_set = array();
  foreach($DB_VAR as $key => $val)
  {
    switch($key)
    {
      case ':sconet_id'     : $tab_set[] = 'user_sconet_id='     .$key; break;
      case ':sconet_num'    : $tab_set[] = 'user_sconet_elenoet='.$key; break;
      case ':reference'     : $tab_set[] = 'user_reference='     .$key; break;
      case ':profil_sigle'  : $tab_set[] = 'user_profil_sigle='  .$key; break;
      case ':genre'         : $tab_set[] = 'user_genre='         .$key; break;
      case ':nom'           : $tab_set[] = 'user_nom='           .$key; break;
      case ':prenom'        : $tab_set[] = 'user_prenom='        .$key; break;
      case ':birth_date'    : $tab_set[] = 'user_naissance_date='.$key; break;
      case ':courriel'      : $tab_set[] = 'user_email='         .$key; break;
      case ':email_origine' : $tab_set[] = 'user_email_origine=' .$key; break;
      case ':login'         : $tab_set[] = 'user_login='         .$key; break;
      case ':password'      : $tab_set[] = 'user_password='      .$key; break;
      case ':langue'        : $tab_set[] = 'user_langue='        .$key; break;
      case ':daltonisme'    : $tab_set[] = 'user_daltonisme='    .$key; break;
      case ':connexion_date': $tab_set[] = 'user_connexion_date='.$key; break;
      case ':sortie_date'   : $tab_set[] = 'user_sortie_date='   .$key; break;
      case ':classe'        : $tab_set[] = 'eleve_classe_id='    .$key; break;
      case ':elv_classe'    : $tab_set[] = 'eleve_classe_id='    .$key; break;
      case ':lv1'           : $tab_set[] = 'eleve_lv1='          .$key; break;
      case ':lv2'           : $tab_set[] = 'eleve_lv2='          .$key; break;
      case ':uai_origine'   : $tab_set[] = 'eleve_uai_origine='  .$key; break;
      case ':id_ent'        : $tab_set[] = 'user_id_ent='        .$key; break;
      case ':id_gepi'       : $tab_set[] = 'user_id_gepi='       .$key; break;
      case ':param_accueil' : $tab_set[] = 'user_param_accueil=' .$key; break;
      case ':param_menu'    : $tab_set[] = 'user_param_menu='    .$key; break;
      case ':param_favori'  : $tab_set[] = 'user_param_favori='  .$key; break;
    }
  }
  if(count($tab_set))
  {
    $DB_SQL = 'UPDATE sacoche_user ';
    $DB_SQL.= 'SET '.implode(', ',$tab_set).' ';
    $DB_SQL.= 'WHERE user_id=:user_id ';
    $DB_VAR[':user_id'] = $user_id;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  else
  {
    Outil::ajouter_log_PHP( 'Erreur DB_modifier_user()' /*log_objet*/ , serialize($DB_VAR) /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , TRUE /*only_sesamath*/ );
  }
}

/**
 * Rendre une liste de comptes actifs ou inactifs en changeant la date de sortie
 *
 * La mise à jour de la table [sacoche_user_switch] s'effectue lors de l'initialisation annuelle.
 *
 * @param array   $tab_user_id
 * @param bool    $statut
 * @return void
 */
public static function DB_modifier_users_statut( $tab_user_id , $statut )
{
  $date = ($statut) ? 'DEFAULT' : 'NOW()' ;
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_sortie_date='.$date.' ';
  $DB_SQL.= 'WHERE user_id IN('.implode(',',$tab_user_id).') ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Modifier une langue pour une liste d'élèves
 *
 * @param string $objet   lv1 | lv2
 * @param string $listing_user_id
 * @param int    $langue
 * @return void
 */
public static function DB_modifier_user_langue( $objet , $listing_user_id , $langue )
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET eleve_'.$objet.'=:langue ';
  $DB_SQL.= 'WHERE user_id IN('.$listing_user_id.') ';
  $DB_VAR = array(':langue'=>$langue);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_bilan_officiel
 *
 * @param int      $groupe_id    id du groupe (en fait, obligatoirement une classe)
 * @param int      $periode_id   id de la période
 * @param string   $champ        officiel_releve | officiel_bulletin
 * @param string   $etat         nouvel état
 * @return int     0 ou 1 si modifié
 */
public static function DB_modifier_bilan_officiel( $groupe_id , $periode_id , $champ , $etat )
{
  $DB_SQL = 'UPDATE sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'SET '.$champ.'=:etat ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
  $DB_VAR = array(
    ':groupe_id'  => $groupe_id,
    ':periode_id' => $periode_id,
    ':etat'       => $etat,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_profil_parametre
 *
 * @param string   $profil_sigle
 * @param string   $champ
 * @param mixed    $valeur
 * @return void
 */
public static function DB_modifier_profil_parametre( $profil_sigle , $champ , $valeur )
{
  $DB_SQL = 'UPDATE sacoche_user_profil ';
  $DB_SQL.= 'SET '.$champ.'=:valeur ';
  $DB_SQL.= ($profil_sigle!='ALL') ? 'WHERE user_profil_sigle=:profil_sigle ' : 'WHERE user_profil_structure=1 ' ;
  $DB_VAR = array(
    ':profil_sigle' => $profil_sigle,
    ':valeur'       => $valeur,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les référentiels dépendant d'une matière ou d'un niveau
 *
 * Le ménage dans sacoche_livret_jointure_referentiel est un peu pénible ici, il est effectué ailleurs.
 *
 * @param string $champ_nom   'matiere_id' | 'niveau_id'
 * @param int    $champ_val   $matiere_id  | $niveau_id
 * @return void
 */
public static function DB_supprimer_referentiels( $champ_nom , $champ_val )
{
  $DB_SQL = 'DELETE sacoche_referentiel, sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_demande USING (matiere_id,item_id) ';
  $DB_SQL.= 'WHERE sacoche_referentiel.'.$champ_nom.'=:champ_val ';
  $DB_VAR = array(':champ_val'=>$champ_val);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les devoirs sans les saisies associées (utilisé uniquement dans le cadre d'un nettoyage annuel ; les groupes de types 'besoin' et 'eval' sont supprimés dans un second temps)
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_devoirs_sans_saisies()
{
  $tab_tables = array( 'sacoche_devoir' , 'sacoche_jointure_devoir_item', 'sacoche_jointure_devoir_prof' , 'sacoche_jointure_devoir_eleve' );
  foreach( $tab_tables as $table )
  {
    $DB_SQL = 'TRUNCATE '.$table;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  }
}

/**
 * Supprimer les reliquats de marqueurs d'évaluations dans les devoirs (utilisé uniquement dans le cadre d'un nettoyage annuel)
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_saisies_marqueurs()
{
  $DB_SQL = 'DELETE FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE saisie_note="PA" ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Supprimer les reliquats de marqueurs d'évaluations dans les devoirs (utilisé uniquement dans le cadre d'un nettoyage annuel)
 *
 * @param int    $user_id
 * @param string $sortie_date_mysql
 * @return void
 */
public static function DB_supprimer_user_saisies_absences_apres_sortie( $user_id , $sortie_date_mysql )
{
  $DB_SQL = 'DELETE FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND saisie_date>=:saisie_date AND saisie_note IN ("","AB","DI","NE","NF","NN","NR","PA") ';
  $DB_VAR = array(
    ':eleve_id'    =>$user_id,
    ':saisie_date' =>$sortie_date_mysql,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_bilans_officiels
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_bilans_officiels()
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_officiel_saisie'    , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_officiel_assiduite' , NULL);
}

/**
 * supprimer_officiel_archive_image
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_officiel_archive_image()
{
  $DB_SQL = 'DELETE sacoche_officiel_archive_image ';
  $DB_SQL.= 'FROM sacoche_officiel_archive_image ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive AS t1 ON sacoche_officiel_archive_image.archive_image_md5=t1.archive_md5_image1 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive AS t2 ON sacoche_officiel_archive_image.archive_image_md5=t2.archive_md5_image2 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive AS t3 ON sacoche_officiel_archive_image.archive_image_md5=t3.archive_md5_image3 ';
  $DB_SQL.= 'LEFT JOIN sacoche_officiel_archive AS t4 ON sacoche_officiel_archive_image.archive_image_md5=t4.archive_md5_image4 ';
  $DB_SQL.= 'WHERE t1.archive_md5_image1 IS NULL AND t2.archive_md5_image2 IS NULL AND t3.archive_md5_image3 IS NULL AND t4.archive_md5_image4 IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_saisies
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_saisies()
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_saisie' , NULL);
}

/**
 * Supprimer toutes les demandes d'évaluations résiduelles dans l'établissement
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_demandes_evaluation()
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_demande' , NULL);
}

/**
 * supprimer_jointures_parents_for_eleves
 *
 * @param bool|string   $listing_eleve_id   id des élèves séparés par des virgules
 * @return void
 */
public static function DB_supprimer_jointures_parents_for_eleves($listing_eleve_id)
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'WHERE eleve_id IN('.$listing_eleve_id.') ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Supprimer un utilisateur avec tout ce qui en dépend
 *
 * La mise à jour de la table [sacoche_user_switch] s'effectue lors de l'initialisation annuelle.
 * 
 * @param int    $user_id
 * @param string $user_profil_sigle
 * @return void
 */
public static function DB_supprimer_utilisateur( $user_id , $user_profil_sigle )
{
  $user_profil_type = isset($_SESSION['TAB_PROFILS_ADMIN']) ? $_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$user_profil_sigle] : 'administrateur' ;
  $DB_VAR = array(':user_id'=>$user_id);
  $DB_SQL = 'DELETE FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:user_id';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $DB_SQL = 'DELETE FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'WHERE user_id=:user_id';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Concernant sacoche_jointure_message_destinataire, pour les groupes cela est traité plus loin
  $DB_SQL = 'DELETE FROM sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'WHERE destinataire_type="user" AND destinataire_id=:user_id';
  if( $_SESSION['USER_PROFIL_TYPE'] == 'developpeur' )
  {
    // Cette fonction peut être appelée par un profil développeur pour le compte administrateur "superviseur" ; dans ce cas $_SESSION['TAB_PROFILS_ADMIN'] n'est alors pas défini.
    return;
  }
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if($user_profil_type=='eleve')
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_eleve ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_saisie ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_demande ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_enscompl_eleve ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_modaccomp_eleve ';
    $DB_SQL.= 'WHERE eleve_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_export ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof ';
    $DB_SQL.= 'FROM sacoche_livret_saisie ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) ';
    $DB_SQL.= 'WHERE cible_id=:user_id AND cible_nature="eleve" ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_officiel_saisie ';
    $DB_SQL.= 'WHERE eleve_ou_classe_id=:user_id AND saisie_type="eleve" ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_officiel_archive ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_officiel_assiduite ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if($user_profil_type=='parent')
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_parent_eleve ';
    $DB_SQL.= 'WHERE parent_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_parent_adresse ';
    $DB_SQL.= 'WHERE parent_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if($user_profil_type=='professeur')
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_user_matiere ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_ap_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_epi_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_parcours_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof ';
    $DB_SQL.= 'FROM sacoche_livret_saisie ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) ';
    $DB_SQL.= 'WHERE sacoche_livret_saisie.prof_id=:user_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_livret_saisie_jointure_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE sacoche_jointure_devoir_item ';
    $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
    $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
    $DB_SQL.= 'WHERE proprio_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    // Groupes type "eval" et "besoin", avec jointures devoirs + jointures users + jointures destinataires messages
    $DB_SQL = 'SELECT CONVERT( GROUP_CONCAT(groupe_id SEPARATOR ",") , CHAR) AS listing_groupe_id ';
    $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
    $DB_SQL.= 'WHERE user_id=:user_id AND jointure_pp=1 AND groupe_type IN("besoin","eval") ';
    $listing_groupe_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    if($listing_groupe_id)
    {
      $DB_SQL = 'DELETE sacoche_groupe, sacoche_devoir, sacoche_jointure_user_groupe, sacoche_jointure_message_destinataire ';
      $DB_SQL.= 'FROM sacoche_groupe ';
      $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (groupe_id) ';
      $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
      $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire ON sacoche_groupe.groupe_id=sacoche_jointure_message_destinataire.destinataire_id AND sacoche_groupe.groupe_type=sacoche_jointure_message_destinataire.destinataire_type ';
      $DB_SQL.= 'WHERE proprio_id=:user_id AND groupe_type IN("besoin","eval") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
    // A priori les devoirs concernés ont été supprimés par la requête précédente, mais au cas où...
    $DB_SQL = 'DELETE FROM sacoche_devoir ';
    $DB_SQL.= 'WHERE proprio_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_jointure_devoir_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'UPDATE sacoche_saisie ';
    $DB_SQL.= 'SET prof_id=0 ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_selection_item ';
    $DB_SQL.= 'WHERE proprio_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'DELETE FROM sacoche_jointure_selection_prof ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    $DB_SQL = 'UPDATE sacoche_demande ';
    $DB_SQL.= 'SET prof_id=0 ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if( ($user_profil_type=='eleve') || ($user_profil_type=='professeur') )
  {
    $DB_SQL = 'DELETE FROM sacoche_jointure_user_groupe ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if( ($user_profil_type=='professeur') || ($user_profil_type=='directeur') )
  {
    $DB_SQL = 'DELETE FROM sacoche_officiel_saisie ';
    $DB_SQL.= 'WHERE prof_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if( ($user_profil_type=='eleve') || ($user_profil_type=='professeur') || ($user_profil_type=='directeur') )
  {
    // photo si élève ; signature si professeur ou directeur
    $DB_SQL = 'DELETE FROM sacoche_image ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  if( ($user_profil_type!='eleve') && ($user_profil_type!='parent') )
  {
    $DB_SQL = 'DELETE sacoche_message, sacoche_jointure_message_destinataire ';
    $DB_SQL.= 'FROM sacoche_message ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire USING (message_id) ';
    $DB_SQL.= 'WHERE user_id=:user_id';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * Optimiser les tables d'une base
 *
 * @param void
 * @return void
 */
public static function DB_optimiser_tables_structure()
{
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_%"');
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'OPTIMIZE TABLE '.$DB_ROW['Name']);
    }
  }
}

/**
 * Fusionner les données associées à 2 comptes élèves (sauf tables sacoche_user, sacoche_jointure_user_groupe, sacoche_jointure_parent_eleve, sacoche_jointure_message_destinataire)
 *
 * @param int   $user_id_ancien
 * @param int   $user_id_actuel
 * @return bool
 */
public static function DB_fusionner_donnees_comptes_eleves( $user_id_ancien , $user_id_actuel )
{
  $tab_table_champ = array(
    'sacoche_demande'                         => 'eleve_id' ,
    'sacoche_jointure_devoir_eleve'           => 'eleve_id' ,
    'sacoche_livret_jointure_enscompl_eleve'  => 'eleve_id' ,
    'sacoche_livret_jointure_modaccomp_eleve' => 'eleve_id' ,
    'sacoche_saisie'                          => 'eleve_id' ,
    'sacoche_jointure_user_abonnement'        => 'user_id' ,
    'sacoche_livret_export'                   => 'user_id' ,
    'sacoche_notification'                    => 'user_id' ,
    'sacoche_officiel_archive'                => 'user_id' ,
    'sacoche_officiel_assiduite'              => 'user_id' ,
    'sacoche_officiel_saisie'                 => 'eleve_ou_classe_id' ,
    'sacoche_livret_saisie'                   => 'cible_id' ,
  );
  foreach($tab_table_champ as $table_nom => $champ_nom)
  {
    switch($champ_nom)
    {
      case 'eleve_ou_classe_id' :
        $where_add = ' AND saisie_type="eleve"';
        break;
      case 'cible_id' :
        $where_add = ' AND cible_nature="eleve"';
        break;
      default :
        $where_add = '';
    }
    // UPDATE ... ON DUPLICATE KEY DELETE ...  n'existe pas, il faut s'y prendre en deux fois avec UPDATE IGNORE ... puis DELETE ...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE IGNORE '.$table_nom.' SET '.$champ_nom.'='.$user_id_actuel.' WHERE '.$champ_nom.'='.$user_id_ancien.$where_add );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM '.$table_nom.' WHERE '.$champ_nom.'='.$user_id_ancien.$where_add );
  }
}

/**
 * Fusionner les données associées à 2 comptes professeurs / personnels (sauf tables sacoche_user, sacoche_jointure_user_groupe, sacoche_jointure_message_destinataire)
 *
 * @param int   $user_id_ancien
 * @param int   $user_id_actuel
 * @return bool
 */
public static function DB_fusionner_donnees_comptes_personnels( $user_id_ancien , $user_id_actuel )
{
  $tab_table_champ = array(
    'sacoche_demande'                       => 'prof_id' ,
    'sacoche_jointure_devoir_prof'          => 'prof_id' ,
    'sacoche_jointure_selection_prof'       => 'prof_id' ,
    'sacoche_livret_jointure_ap_prof'       => 'prof_id' ,
    'sacoche_livret_jointure_epi_prof'      => 'prof_id' ,
    'sacoche_livret_jointure_parcours_prof' => 'prof_id' ,
    'sacoche_livret_saisie'                 => 'prof_id' ,
    'sacoche_livret_saisie_jointure_prof'   => 'prof_id' ,
    'sacoche_officiel_saisie'               => 'prof_id' ,
    'sacoche_saisie'                        => 'prof_id' ,
    'sacoche_image'                         => 'user_id' ,
    'sacoche_jointure_user_abonnement'      => 'user_id' ,
    'sacoche_jointure_user_matiere'         => 'user_id' ,
    'sacoche_jointure_user_module'          => 'user_id' ,
    'sacoche_message'                       => 'user_id' ,
    'sacoche_notification'                  => 'user_id' ,
    'sacoche_devoir'                        => 'proprio_id' ,
    'sacoche_selection_item'                => 'proprio_id' ,
  );
  foreach($tab_table_champ as $table_nom => $champ_nom)
  {
    // UPDATE ... ON DUPLICATE KEY DELETE ...  n'existe pas, il faut s'y prendre en deux fois avec UPDATE IGNORE ... puis DELETE ...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE IGNORE '.$table_nom.' SET '.$champ_nom.'='.$user_id_actuel.' WHERE '.$champ_nom.'='.$user_id_ancien );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM '.$table_nom.' WHERE '.$champ_nom.'='.$user_id_ancien );
  }
}

/**
 * Recherche et correction d'anomalies : numérotation des items d'un thème, ou des thèmes d'un domaine, ou des domaines d'un référentiel
 *
 * @param void
 * @return array   tableau avec label et commentaire pour chaque recherche
 */
public static function DB_corriger_numerotations()
{
  function make_where($champ,$valeur)
  {
    return $champ.'='.$valeur;
  }
  $tab_bilan = array();
  $tab_recherche = array();
  $tab_recherche[] = array( 'contenant_nom'=>'référentiel' , 'contenant_tab_champs'=>array('matiere_id','niveau_id') , 'element_nom'=>'domaine' , 'element_champ'=>'domaine' , 'debut'=>1 , 'decalage'=>0 );
  $tab_recherche[] = array( 'contenant_nom'=>'domaine'     , 'contenant_tab_champs'=>array('domaine_id')             , 'element_nom'=>'thème'   , 'element_champ'=>'theme'   , 'debut'=>1 , 'decalage'=>0 );
  $tab_recherche[] = array( 'contenant_nom'=>'thème'       , 'contenant_tab_champs'=>array('theme_id')               , 'element_nom'=>'item'    , 'element_champ'=>'item'    , 'debut'=>0 , 'decalage'=>1 );
  foreach($tab_recherche as $tab_donnees)
  {
    extract($tab_donnees,EXTR_OVERWRITE); // $contenant_nom $contenant_tab_champs $element_nom $element_champ $debut $decalage
    // numéros en double
    $DB_SQL = 'SELECT DISTINCT CONCAT('.implode(',",",',$contenant_tab_champs).') AS contenant_id , COUNT('.$element_champ.'_id) AS nombre ';
    $DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
    $DB_SQL.= 'GROUP BY '.implode(',',$contenant_tab_champs).','.$element_champ.'_ordre ';
    $DB_SQL.= 'HAVING nombre>1 ';
    $DB_TAB1 = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE);
    // numéros manquants ou décalés
    $DB_SQL = 'SELECT DISTINCT CONCAT('.implode(',",",',$contenant_tab_champs).') AS contenant_id , MAX('.$element_champ.'_ordre) AS maximum , COUNT('.$element_champ.'_id) AS nombre ';
    $DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
    $DB_SQL.= 'GROUP BY '.implode(',',$contenant_tab_champs).' ';
    $DB_SQL.= 'HAVING nombre!=maximum+'.$decalage.' ';
    $DB_TAB2 = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE);
    // en réunissant les 2 requêtes on a repéré tous les problèmes possibles
    $tab_bugs = array_unique( array_merge( array_keys($DB_TAB1) , array_keys($DB_TAB2) ) );
    $nb_bugs = count($tab_bugs);
    if($nb_bugs)
    {
      foreach($tab_bugs as $contenant_id)
      {
        $element_ordre = $debut;
        $contenant_tab_valeur = explode(',',$contenant_id);
        $tab_where = array_map('make_where', $contenant_tab_champs, $contenant_tab_valeur);
        $DB_SQL = 'SELECT '.$element_champ.'_id ';
        $DB_SQL.= 'FROM sacoche_referentiel_'.$element_champ.' ';
        $DB_SQL.= 'WHERE '.implode(' AND ',$tab_where).' ';
        $DB_SQL.= 'ORDER BY '.$element_champ.'_ordre ASC ';
        $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
        foreach($DB_COL as $element_champ_id)
        {
          $DB_SQL = 'UPDATE sacoche_referentiel_'.$element_champ.' ';
          $DB_SQL.= 'SET '.$element_champ.'_ordre='.$element_ordre.' ';
          $DB_SQL.= 'WHERE '.$element_champ.'_id='.$element_champ_id.' ';
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
          $element_ordre++;
        }
      }
    }
    $message = (!$nb_bugs) ? 'rien à signaler' : ( ($nb_bugs>1) ? $nb_bugs.' '.$contenant_nom.'s dont le contenu a été renuméroté' : '1 '.$contenant_nom.' dont le contenu a été renuméroté' ) ;
    $classe  = (!$nb_bugs) ? 'valide' : 'alerte' ;
    $tab_bilan[] = '<label class="'.$classe.'">'.ucfirst($element_nom).'s des '.$contenant_nom.'s : '.$message.'.</label>';
  }
  return $tab_bilan;
}

/**
 * Recherche et suppression de correspondances anormales dans la base
 *
 * @param void
 * @return array   tableau avec label et commentaire pour chaque recherche
 */
public static function DB_corriger_anomalies()
{
  $tab_bilan = array();
  // un bout de code utilisé à chaque fois
  function compte_rendu( $nb_modifs , $sujet )
  {
    $message = (!$nb_modifs) ? 'rien à signaler' : ( ($nb_modifs>1) ? $nb_modifs.' anomalies supprimées' : '1 anomalie supprimée' ) ;
    $classe  = (!$nb_modifs) ? 'valide' : 'alerte' ;
    return '<label class="'.$classe.'">'.$sujet.' : '.$message.'.</label>';
  }
  // Référentiels associés à une matière supprimée
  $DB_SQL = 'DELETE sacoche_referentiel,sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_referentiel_socle, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
  $DB_SQL.= 'WHERE sacoche_matiere.matiere_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Référentiels' );
  // Domaines associés à une matière supprimée...
  $DB_SQL = 'DELETE sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_referentiel_socle, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_referentiel_domaine ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
  $DB_SQL.= 'WHERE sacoche_matiere.matiere_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Domaines (arborescence)' );
  // Thèmes associés à un domaine supprimé...
  $DB_SQL = 'DELETE sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_referentiel_socle, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_referentiel_theme ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
  $DB_SQL.= 'WHERE sacoche_referentiel_domaine.domaine_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Thèmes (arborescence)' );
  // Items associés à un thème supprimé...
  $DB_SQL = 'DELETE sacoche_referentiel_item, sacoche_jointure_referentiel_socle, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_referentiel_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_referentiel_socle USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
  $DB_SQL.= 'WHERE sacoche_referentiel_theme.theme_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Items (arborescence)' );
  // Demandes d'évaluations associées à un user ou une matière ou un item supprimé...
  $DB_SQL = 'DELETE sacoche_demande ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_demande.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_matiere.matiere_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
  $DB_SQL = 'UPDATE sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_demande.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'SET prof_id=0 ';
  $DB_SQL.= 'WHERE sacoche_user.user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_modifs += DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
  $tab_bilan[] = compte_rendu( $nb_modifs , 'Demandes d\'évaluations' );
  // Saisies de scores associées à un élève ou un item supprimé...
  // Attention, on ne teste pas le professeur ou le devoir, car les saisies sont conservées au delà
  $DB_SQL = 'DELETE sacoche_saisie ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_saisie.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Scores' );
  // Devoirs associés à un prof ou un groupe supprimé...
  $DB_SQL = 'DELETE sacoche_devoir, sacoche_jointure_devoir_item , sacoche_jointure_devoir_prof , sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item  USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_prof  USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_eleve USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Évaluations' );
  // Messages associés à un utilisateur supprimé...
  $DB_SQL = 'DELETE sacoche_message, sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'FROM sacoche_message ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_message_destinataire USING (message_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE sacoche_user.user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Messages d\'accueil' );
  // Destinataires de messages associés à un utilisateur ou un regroupement supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'FROM sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_jointure_message_destinataire.destinataire_id=sacoche_groupe.groupe_id AND sacoche_jointure_message_destinataire.destinataire_type=sacoche_groupe.groupe_type ';
  $DB_SQL.= 'WHERE destinataire_type IN ("classe","groupe","besoin") AND (sacoche_groupe.groupe_id IS NULL) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_modifs = DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
  $DB_SQL = 'DELETE sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'FROM sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau ON sacoche_jointure_message_destinataire.destinataire_id=sacoche_niveau.niveau_id ';
  $DB_SQL.= 'WHERE destinataire_type="niveau" AND (sacoche_niveau.niveau_id IS NULL) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_modifs += DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
  $DB_SQL = 'DELETE sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'FROM sacoche_jointure_message_destinataire ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_message_destinataire.destinataire_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE destinataire_type="user" AND (sacoche_user.user_id IS NULL) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_modifs += DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
  $tab_bilan[] = compte_rendu( $nb_modifs , 'Jointures message/destinataire' );
  // Bascules vers un compte désactivé ou supprimé...
  $tab_bilan[] = compte_rendu( DB_STRUCTURE_SWITCH::DB_supprimer_liaisons_obsoletes() , 'Bascules entre comptes' );
  // Sélections d'items associées à un professeur supprimé...
  $DB_SQL = 'DELETE sacoche_selection_item ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_selection_item.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE sacoche_user.user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Sélections d\'items sans propriétaire' );
  // Jointures sélection/item à un item supprimé...
  $tab_bilan[] = compte_rendu( DB_STRUCTURE_SELECTION_ITEM::DB_supprimer_jointures_items_obsoletes() , 'Jointures sélection/item' );
  // Sélections d'items associées à aucun item...
  $tab_bilan[] = compte_rendu( DB_STRUCTURE_SELECTION_ITEM::DB_supprimer_selections_items_obsoletes() , 'Sélections d\'items sans item' );
  // Jointures période/groupe associées à une période ou un groupe supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_periode.periode_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures période/groupe' );
  // Jointures période/saisie bilan officiel associées à une période supprimée... (on ne s'occupe volontairement pas de vérifier la jointure période/groupe) (on ne vérifie pas non plus les jointures élève / prof / rubrique ... de toutes façon cette table est vidée annuellement)
  $DB_SQL = 'DELETE sacoche_officiel_saisie ';
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE sacoche_periode.periode_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures période/saisie bilan officiel' );
  // Jointures période/fichier bilan officiel associées à un user supprimé...
  $DB_SQL = 'DELETE sacoche_officiel_archive ';
  $DB_SQL.= 'FROM sacoche_officiel_archive ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE sacoche_user.user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures période/fichier bilan officiel' );
  // Jointures période/assiduité bilan officiel associées à un user ou une période supprimée...
  $DB_SQL = 'DELETE sacoche_officiel_assiduite ';
  $DB_SQL.= 'FROM sacoche_officiel_assiduite ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_periode.periode_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures période/assiduité bilan officiel' );
  // Jointures user/groupe associées à un user ou un groupe supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_user_groupe ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_groupe.groupe_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures utilisateur/groupe' );
  // Jointures user/matière associées à un user ou une matière supprimée...
  $DB_SQL = 'DELETE sacoche_jointure_user_matiere ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_user.user_id IS NULL) OR (sacoche_matiere.matiere_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures utilisateur/matière' );
  // Abonnement notifications associée à un user supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures utilisateur/abonnement notifications' );
  // Jointures item/socle associées à un item supprimé ou un élément de socle inexistant ...
  $DB_SQL = 'DELETE sacoche_jointure_referentiel_socle ';
  $DB_SQL.= 'FROM sacoche_jointure_referentiel_socle ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_cycle USING (socle_cycle_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_composante USING (socle_composante_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_referentiel_item.item_id IS NULL) OR (sacoche_socle_cycle.socle_cycle_id IS NULL) OR (sacoche_socle_composante.socle_composante_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures item/socle' );
  // Jointures devoir/item associées à un devoir ou un item supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_devoir_item ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'WHERE ( (sacoche_devoir.devoir_id IS NULL) OR (sacoche_referentiel_item.item_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures évaluation/item' );
  // Jointures devoir/droit associées à un devoir ou un user supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_devoir_prof ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_prof ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_devoir_prof.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ( (sacoche_devoir.devoir_id IS NULL) OR (sacoche_user.user_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures évaluation/prof' );
  // Jointures devoir/audio associées à un devoir ou un user supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_devoir_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ( (sacoche_devoir.devoir_id IS NULL) OR (sacoche_user.user_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures évaluation/audio' );
  // Adresse associée à un parent supprimé...
  $DB_SQL = 'DELETE sacoche_parent_adresse ';
  $DB_SQL.= 'FROM sacoche_parent_adresse ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_parent_adresse.parent_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures parent/adresse' );
  // Jointures parent/élève associées à un parent ou un élève supprimé...
  $DB_SQL = 'DELETE sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS eleve ON sacoche_jointure_parent_eleve.eleve_id=eleve.user_id ';
  $DB_SQL.= 'WHERE ( (parent.user_id IS NULL) OR (eleve.user_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures parent/enfant' );
  // Élèves associés à une classe supprimée...
  // Attention, l'id de classe à 0 est normal pour un élève non affecté ou un autre statut
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'SET sacoche_user.eleve_classe_id=0 ';
  $DB_SQL.= 'WHERE ( (sacoche_user.eleve_classe_id!=0) AND (sacoche_groupe.groupe_id IS NULL) ) ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures élève/classe' );
  // Signature associée à un user supprimé...
  // Attention, l'id de user à 0 est normal pour le tampon et le logo de l'établissement
  $DB_SQL = 'DELETE sacoche_image ';
  $DB_SQL.= 'FROM sacoche_image ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE sacoche_image.user_id!=0 AND sacoche_user.user_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_bilan[] = compte_rendu( DB::rowCount(SACOCHE_STRUCTURE_BD_NAME) , 'Jointures utilisateur/signature' );
  // Retour
  return $tab_bilan;
}

}
?>