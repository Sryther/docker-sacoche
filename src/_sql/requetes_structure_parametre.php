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
// Ces méthodes ne concernent que les tables "sacoche_parametre" + "sacoche_parametre_note" + "sacoche_parametre_acquis" + "sacoche_parametre_profil".

class DB_STRUCTURE_PARAMETRE extends DB
{

/**
 * Lister des paramètres d'une structure (contenu de la table 'sacoche_parametre')
 *
 * @param string   $listing_param   nom des paramètres entourés de guillemets et séparés par des virgules (tout si rien de transmis)
 * @return array
 */
public static function DB_lister_parametres($listing_param='')
{
  $DB_SQL = 'SELECT parametre_nom, parametre_valeur ';
  $DB_SQL.= 'FROM sacoche_parametre ';
  $DB_SQL.= ($listing_param) ? 'WHERE parametre_nom IN('.$listing_param.') ' : '' ;
  // Pas de queryRow prévu car toujours au moins 2 paramètres demandés jusqu'à maintenant.
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister des paramètres d'une structure liés aux codes de notation (contenu de la table 'sacoche_parametre_note')
 *
 * @param bool   $priority_actifs   prioritairement ceux actifs
 * @return array
 */
public static function DB_lister_parametres_note($priority_actifs)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_parametre_note ';
  $DB_SQL.= ($priority_actifs) ? 'ORDER BY note_actif DESC, note_ordre ASC ' : 'ORDER BY note_ordre ASC ' ;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister des paramètres d'une structure liés aux états d'acquisition (contenu de la table 'sacoche_parametre_acquis')
 *
 * @param bool   $only_actifs   seuls ceux actifs
 * @return array
 */
public static function DB_lister_parametres_acquis($only_actifs)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_parametre_acquis ';
  $DB_SQL.= ($only_actifs) ? 'WHERE acquis_actif=1 ' : '' ;
  $DB_SQL.= 'ORDER BY acquis_ordre ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister des paramètres d'une structure liés aux menu / favori d'un profil donné
 *
 * @param string   $user_profil_type
 * @return array
 */
public static function DB_recuperer_parametres_profil($user_profil_type)
{
  $DB_SQL = 'SELECT profil_param_menu , profil_param_favori ';
  $DB_SQL.= 'FROM sacoche_parametre_profil ';
  $DB_SQL.= 'WHERE user_profil_type=:user_profil_type ' ;
  $DB_VAR = array(':user_profil_type'=>$user_profil_type);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_parametres
 *
 * @param array $tab_parametres   tableau parametre_nom => parametre_valeur
 * @return void
 */
public static function DB_modifier_parametres($tab_parametres)
{
  $DB_SQL = 'UPDATE sacoche_parametre ';
  $DB_SQL.= 'SET parametre_valeur=:parametre_valeur ';
  $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
  foreach($tab_parametres as $parametre_nom => $parametre_valeur)
  {
    $DB_VAR = array(
      ':parametre_nom'    => $parametre_nom,
      ':parametre_valeur' => $parametre_valeur,
    );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * modifier_parametre_note
 *
 * @param int    $note_id
 * @param int    $note_actif
 * @param int    $note_ordre
 * @param int    $note_valeur
 * @param string $note_image
 * @param string $note_sigle
 * @param string $note_legende
 * @param int    $note_clavier
 * @return void
 */
public static function DB_modifier_parametre_note( $note_id , $note_actif , $note_ordre , $note_valeur , $note_image , $note_sigle , $note_legende , $note_clavier )
{
  $DB_SQL = 'UPDATE sacoche_parametre_note ';
  $DB_SQL.= 'SET note_actif=:note_actif, note_ordre=:note_ordre, note_valeur=:note_valeur, note_image=:note_image, note_sigle=:note_sigle, note_legende=:note_legende, note_clavier=:note_clavier ';
  $DB_SQL.= 'WHERE note_id=:note_id ';
  $DB_VAR = array(
    ':note_id'      => $note_id,
    ':note_actif'   => $note_actif,
    ':note_ordre'   => $note_ordre,
    ':note_valeur'  => $note_valeur,
    ':note_image'   => $note_image,
    ':note_sigle'   => $note_sigle,
    ':note_legende' => $note_legende,
    ':note_clavier' => $note_clavier,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * remplacer_parametre_note_image
 *
 * @param string $note_image
 * @return void
 */
public static function DB_remplacer_parametre_note_image( $note_image )
{
  $DB_SQL = 'UPDATE sacoche_parametre_note ';
  $DB_SQL.= 'SET note_image="X" ';
  $DB_SQL.= 'WHERE note_image=:note_image ';
  $DB_VAR = array(
    ':note_image' => $note_image,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_parametre_acquis
 *
 * @param int    $acquis_id
 * @param int    $acquis_actif
 * @param int    $acquis_ordre
 * @param int    $acquis_seuil_min
 * @param int    $acquis_seuil_max
 * @param int    $acquis_valeur
 * @param string $acquis_couleur
 * @param string $acquis_sigle
 * @param string $acquis_legende
 * @return void
 */
public static function DB_modifier_parametre_acquis( $acquis_id , $acquis_actif , $acquis_ordre , $acquis_seuil_min , $acquis_seuil_max , $acquis_valeur , $acquis_couleur , $acquis_sigle , $acquis_legende )
{
  $DB_SQL = 'UPDATE sacoche_parametre_acquis ';
  $DB_SQL.= 'SET acquis_actif=:acquis_actif, acquis_ordre=:acquis_ordre, acquis_seuil_min=:acquis_seuil_min, acquis_seuil_max=:acquis_seuil_max, acquis_valeur=:acquis_valeur, acquis_couleur=:acquis_couleur, acquis_sigle=:acquis_sigle, acquis_legende=:acquis_legende ';
  $DB_SQL.= 'WHERE acquis_id=:acquis_id ';
  $DB_VAR = array(
    ':acquis_id'        => $acquis_id,
    ':acquis_actif'     => $acquis_actif,
    ':acquis_ordre'     => $acquis_ordre,
    ':acquis_seuil_min' => $acquis_seuil_min,
    ':acquis_seuil_max' => $acquis_seuil_max,
    ':acquis_valeur'    => $acquis_valeur,
    ':acquis_couleur'   => $acquis_couleur,
    ':acquis_sigle'     => $acquis_sigle,
    ':acquis_legende'   => $acquis_legende,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_parametre_note_valeur
 *
 * @param int $note_id
 * @param int $note_valeur
 * @return void
 */
public static function DB_modifier_parametre_note_valeur( $note_id , $note_valeur )
{
  $DB_SQL = 'UPDATE sacoche_parametre_note ';
  $DB_SQL.= 'SET note_valeur=:note_valeur ';
  $DB_SQL.= 'WHERE note_id=:note_id ';
  $DB_VAR = array(
    ':note_id'     => $note_id,
    ':note_valeur' => $note_valeur,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_parametre_acquis_seuils
 *
 * @param int $acquis_id
 * @param int $acquis_seuil_min
 * @param int $acquis_seuil_max
 * @return void
 */
public static function DB_modifier_parametre_acquis_seuils( $acquis_id , $acquis_seuil_min , $acquis_seuil_max )
{
  $DB_SQL = 'UPDATE sacoche_parametre_acquis ';
  $DB_SQL.= 'SET acquis_seuil_min=:acquis_seuil_min , acquis_seuil_max=:acquis_seuil_max ';
  $DB_SQL.= 'WHERE acquis_id=:acquis_id ';
  $DB_VAR = array(
    ':acquis_id'     => $acquis_id,
    ':acquis_seuil_min' => $acquis_seuil_min,
    ':acquis_seuil_max' => $acquis_seuil_max,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier les paramètres de menu / favori d'un profil par un admin
 *
 * @param string $user_profil_type
 * @param string $profil_param_menu
 * @param string $profil_param_favori
 * @return void
 */
public static function DB_modifier_parametre_profil( $user_profil_type , $profil_param_menu , $profil_param_favori )
{
  $DB_SQL = 'UPDATE sacoche_parametre_profil ';
  $DB_SQL.= 'SET profil_param_menu=:profil_param_menu, profil_param_favori=:profil_param_favori ';
  $DB_SQL.= 'WHERE user_profil_type=:user_profil_type ';
  $DB_VAR = array(
    ':user_profil_type'    => $user_profil_type,
    ':profil_param_menu'   => $profil_param_menu,
    ':profil_param_favori' => $profil_param_favori,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}


}
?>