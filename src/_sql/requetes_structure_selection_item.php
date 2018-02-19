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
// Ces méthodes ne concernent que les tables "sacoche_selection_item" / "sacoche_jointure_selection_prof" / "sacoche_jointure_selection_item".

class DB_STRUCTURE_SELECTION_ITEM extends DB
{

/**
 * recuperer_prorietaire_id
 *
 * @param int $selection_item_id
 * @return int
 */
public static function DB_recuperer_prorietaire_id($selection_item_id)
{
  $DB_SQL = 'SELECT proprio_id ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
  $DB_VAR = array(':selection_item_id'=>$selection_item_id);
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_prorietaire_identite
 *
 * @param int $selection_item_id
 * @return string
 */
public static function DB_recuperer_prorietaire_identite($selection_item_id)
{
  $DB_SQL = 'SELECT user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_selection_item.proprio_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
  $DB_VAR = array(':selection_item_id'=>$selection_item_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister
 * Retourner les sélections d'items associées à un prof
 *
 * @param int   $user_id
 * @return array
 */
public static function DB_lister($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaîne de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // Il faut commencer par lister les ids des sélections sinon malgré les jointures on ne récupère pas la liste des autres profs items associés à la sélection.
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT selection_item_id SEPARATOR ",") AS selections_listing ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_prof USING (selection_item_id) ';
  $DB_SQL.= 'WHERE ( sacoche_selection_item.proprio_id=:proprio_id OR sacoche_jointure_selection_prof.prof_id=:prof_id ) ';
  $DB_VAR = array(
    ':proprio_id' => $user_id,
    ':prof_id'    => $user_id,
  );
  $selections_listing = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(empty($selections_listing))
  {
    return array();
  }
  // On passe à la requête principale
  // Deux DISTINCT ont été ajoutés sinon, curieusement, après suppression d'un item dans un référentiel, cela retourne plein de fois le même résultat...
  $DB_SQL = 'SELECT selection_item_id, selection_item_nom, proprio_id, ';
  $DB_SQL.= 'CONCAT(prof.user_nom," ",prof.user_prenom) AS proprietaire, ';
  $DB_SQL.= 'GROUP_CONCAT(DISTINCT CONCAT(SUBSTRING(sacoche_jointure_selection_prof.jointure_droit,1,1),sacoche_jointure_selection_prof.prof_id) SEPARATOR "_") AS partage_listing, ';
  $DB_SQL.= 'GROUP_CONCAT(DISTINCT item_id SEPARATOR "_") AS item_listing ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_prof USING (selection_item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_item USING (selection_item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS prof ON sacoche_selection_item.proprio_id=prof.user_id ';
  $DB_SQL.= 'WHERE selection_item_id IN ('.$selections_listing.') ';
  $DB_SQL.= 'GROUP BY sacoche_selection_item.selection_item_id ';
  $DB_SQL.= 'ORDER BY selection_item_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_nom
 *
 * @param string $selection_item_nom
 * @param int    $selection_item_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return string | NULL
 */
public static function DB_tester_nom($selection_item_nom,$selection_item_id=FALSE)
{
  $DB_SQL = 'SELECT CONCAT(prof.user_prenom," ",prof.user_nom) ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS prof ON sacoche_selection_item.proprio_id=prof.user_id ';
  $DB_SQL.= 'WHERE selection_item_nom=:selection_item_nom ';
  $DB_SQL.= ($selection_item_id) ? 'AND selection_item_id!=:selection_item_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':selection_item_nom' => $selection_item_nom,
    ':selection_item_id'  => $selection_item_id,
  );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter
 *
 * @param int    $proprio_id
 * @param string $selection_item_nom
 * @param string $tab_id_items   tableau des id des items
 * @return int
 */
public static function DB_ajouter($proprio_id,$selection_item_nom)
{
  $DB_SQL = 'INSERT INTO sacoche_selection_item( proprio_id, selection_item_nom) ';
  $DB_SQL.= 'VALUES                            (:proprio_id,:selection_item_nom) ';
  $DB_VAR = array(
    ':proprio_id'         => $proprio_id,
    ':selection_item_nom' => $selection_item_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier
 *
 * @param int    $selection_item_id
 * @param string $selection_item_nom
 * @return void
 */
public static function DB_modifier($selection_item_id,$selection_item_nom)
{
  $DB_SQL = 'UPDATE sacoche_selection_item ';
  $DB_SQL.= 'SET selection_item_nom=:selection_item_nom ';
  $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
  $DB_VAR = array(
    ':selection_item_id'  => $selection_item_id,
    ':selection_item_nom' => $selection_item_nom,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_liaison_item
 *
 * @param int    $selection_item_id
 * @param array  $tab_items   tableau [key->id_item]
 * @param string $mode        "creer" | "substituer"
 * @return void
 */
public static function DB_modifier_liaison_item($selection_item_id,$tab_items,$mode)
{
  $tab_old_items = array();
  // On récupère la liste des items actuels pour comparer
  if($mode=='substituer')
  {
    $DB_SQL = 'SELECT item_id ';
    $DB_SQL.= 'FROM sacoche_jointure_selection_item ';
    $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
    $DB_VAR = array(':selection_item_id'=>$selection_item_id);
    $tab_old_items = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  // On compare
  $tab_items_a_ajouter = array_diff( $tab_items , $tab_old_items );
  $tab_items_a_retirer = array_diff( $tab_old_items , $tab_items );
  // On ajoute si besoin
  if(count($tab_items_a_ajouter))
  {
    $DB_SQL = 'INSERT INTO sacoche_jointure_selection_item( selection_item_id, item_id) ';
    $DB_SQL.= 'VALUES                                     (:selection_item_id,:item_id) ';
    foreach($tab_items_a_ajouter as $item_id)
    {
      $DB_VAR = array(
        ':selection_item_id' => $selection_item_id,
        ':item_id'           => $item_id,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    }
  }
  // On retire si besoin
  if(count($tab_items_a_retirer))
  {
    $chaine_item_id = implode(',',$tab_items_a_retirer);
    $DB_SQL = 'DELETE FROM sacoche_jointure_selection_item ';
    $DB_SQL.= 'WHERE selection_item_id=:selection_item_id AND item_id IN('.$chaine_item_id.')';
    $DB_VAR = array(':selection_item_id'=>$selection_item_id);
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * modifier_liaison_prof
 *
 * @param int    $selection_item_id
 * @param array  $tab_profs   tableau [id_prof->droit]
 * @param string $mode        {creer} => insertion dans une nouvelle sélection || {substituer} => maj avec update / delete / insert
 * @return array   sert pour ensuite effectuer des mises à jour de notifications
 */
public static function DB_modifier_liaison_prof($selection_item_id,$tab_profs,$mode)
{
  $tab_retour = array();
  if($mode=='creer')
  {
    // Insertion des droits
    $DB_SQL = 'INSERT INTO sacoche_jointure_selection_prof(selection_item_id,prof_id,jointure_droit) ';
    $DB_SQL.= 'VALUES(:selection_item_id,:prof_id,:droit)';
    foreach($tab_profs as $prof_id => $droit)
    {
      $DB_VAR = array(
        ':selection_item_id' => $selection_item_id,
        ':prof_id'           => $prof_id,
        ':droit'             => $droit,
      );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      $tab_retour[$prof_id] = 'insert';
    }
    return $tab_retour;
  }
  elseif($mode=='substituer')
  {
    // On récupère la liste des droits déjà présents, et on étudie les différences pour faire des UPDATE / DELETE / INSERT sélectifs
    // -> on récupère les droits actuels
    $DB_SQL = 'SELECT prof_id, jointure_droit ';
    $DB_SQL.= 'FROM sacoche_jointure_selection_prof ';
    $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
    $DB_VAR = array(':selection_item_id'=>$selection_item_id);
    $tab_old_droits = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, TRUE, TRUE);
    // -> on parcourt $tab_profs pour comparer avec ce qui est enregistré
    foreach($tab_profs as $prof_id => $droit)
    {
      if(isset($tab_old_droits[$prof_id]))
      {
        if($tab_old_droits[$prof_id]['jointure_droit']!=$droit)
        {
          // -> modification de droit
          $DB_SQL = 'UPDATE sacoche_jointure_selection_prof ';
          $DB_SQL.= 'SET jointure_droit=:droit ';
          $DB_SQL.= 'WHERE selection_item_id=:selection_item_id AND prof_id=:prof_id ';
          $DB_VAR = array(
            ':selection_item_id' => $selection_item_id,
            ':prof_id'           => $prof_id,
            ':droit'             => $droit,
          );
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
          $tab_retour[$prof_id] = 'update';
        }
        unset($tab_old_droits[$prof_id]);
      }
      else
      {
        // -> ajout de droit
        $DB_SQL = 'INSERT INTO sacoche_jointure_selection_prof(selection_item_id,prof_id,jointure_droit) ';
        $DB_SQL.= 'VALUES(:selection_item_id,:prof_id,:droit)';
        $DB_VAR = array(
          ':selection_item_id' => $selection_item_id,
          ':prof_id'           => $prof_id,
          ':droit'             => $droit,
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
        $tab_retour[$prof_id] = 'insert';
      }
    }
    // -> on observe $tab_old_droits pour rechercher ce qui reste
    if(count($tab_old_droits))
    {
      $chaine_prof_id = implode(',',array_keys($tab_old_droits));
      // -> suppression de droit
      $DB_SQL = 'DELETE FROM sacoche_jointure_selection_prof ';
      $DB_SQL.= 'WHERE selection_item_id=:selection_item_id AND prof_id IN('.$chaine_prof_id.')';
      $DB_VAR = array(':selection_item_id'=>$selection_item_id);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      foreach($tab_old_droits as $prof_id => $tab)
      {
        $tab_retour[$prof_id] = 'delete';
      }
    }
    return $tab_retour;
  }
}

/**
 * supprimer
 *
 * @param int   $selection_item_id
 * @return void
 */
public static function DB_supprimer($selection_item_id)
{
  // Il faut aussi supprimer les jointures de la sélection d'items avec les profs
  $DB_SQL = 'DELETE sacoche_selection_item, sacoche_jointure_selection_prof, sacoche_jointure_selection_item ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_prof  USING (selection_item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_item  USING (selection_item_id) ';
  $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
  $DB_VAR = array(':selection_item_id'=>$selection_item_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_liaison_prof
 *
 * @param int   $devoir_id
 * @return void
 */
public static function DB_supprimer_liaison_prof($selection_item_id)
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_selection_prof ';
  $DB_SQL.= 'WHERE selection_item_id=:selection_item_id ';
  $DB_VAR = array(':selection_item_id'=>$selection_item_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_jointures_items_obsoletes
 *
 * Est appelé lors de l'initialisation annuelle ou sur demande "recherche et suppression de correspondances anormales".
 *
 * @param  void
 * @return int
 */
public static function DB_supprimer_jointures_items_obsoletes()
{
  $DB_SQL = 'DELETE sacoche_jointure_selection_item ';
  $DB_SQL.= 'FROM sacoche_jointure_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item ON sacoche_jointure_selection_item.item_id = sacoche_referentiel_item.item_id ';
  $DB_SQL.= 'WHERE sacoche_referentiel_item.item_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_selections_items_obsoletes
 *
 * Est appelé lors de l'initialisation annuelle ou sur demande "recherche et suppression de correspondances anormales".
 *
 * @param  void
 * @return int
 */
public static function DB_supprimer_selections_items_obsoletes()
{
  // On recherche les sélections d'items sans item
  $DB_SQL = 'SELECT sacoche_selection_item.selection_item_id ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_selection_item ON sacoche_selection_item.selection_item_id = sacoche_jointure_selection_item.selection_item_id ';
  $DB_SQL.= 'WHERE sacoche_jointure_selection_item.selection_item_id IS NULL ';
  $DB_SQL.= 'GROUP BY sacoche_selection_item.selection_item_id ';
  $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $nb_obsoletes = count($DB_COL);
  if($nb_obsoletes)
  {
    // On supprime les sélections d'items sans item
    foreach($DB_COL as $selection_item_id)
    {
      DB_STRUCTURE_SELECTION_ITEM::DB_supprimer( $selection_item_id );
    }
  }
  return $nb_obsoletes;
}

}
?>