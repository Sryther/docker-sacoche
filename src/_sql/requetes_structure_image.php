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
// Ces méthodes ne concernent que les tables "sacoche_image" et "sacoche_image_note".

class DB_STRUCTURE_IMAGE extends DB
{

/**
 * recuperer_image
 *
 * @param int    $user_id       0 pour le logo ou le tampon de l'établissement
 * @param string $image_objet   "photo" | "signature" | "logo"
 * @return array
 */
public static function DB_recuperer_image($user_id,$image_objet)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_image ';
  $DB_SQL.= 'WHERE user_id=:user_id AND image_objet=:image_objet ';
  $DB_VAR = array(
    ':user_id'     => $user_id,
    ':image_objet' => $image_objet,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_images
 *
 * @param string   $listing_user_id
 * @param string   $image_objet   "photo" | "signature" | "logo"
 * @return array
 */
public static function DB_lister_images($listing_user_id,$image_objet)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_image ';
  $DB_SQL.= 'WHERE user_id IN ('.$listing_user_id.') AND image_objet=:image_objet ';
  $DB_VAR = array(':image_objet'=>$image_objet);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_signatures_avec_identite
 *
 * @return array
 */
public static function DB_lister_signatures_avec_identite()
{
  $DB_SQL = 'SELECT sacoche_image.*, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_image ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE image_objet="signature" ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_image
 *
 * @param int    $user_id       0 pour le logo ou le tampon de l'établissement
 * @param string $image_objet   "photo" | "signature" | "logo"
 * @param string $image_contenu
 * @param string $image_format   "jpeg" sauf peut-être pour le logo
 * @param int    $image_largeur
 * @param int    $image_hauteur
 * @return void
 */
public static function DB_modifier_image($user_id,$image_objet,$image_contenu,$image_format,$image_largeur,$image_hauteur)
{
  // INSERT ON DUPLICATE KEY UPDATE est plus performant que REPLACE et mieux par rapport aux id autoincrémentés ou aux contraintes sur les clefs étrangères
  // @see http://stackoverflow.com/questions/9168928/what-are-practical-differences-between-replace-and-insert-on-duplicate-ke
  $DB_SQL = 'INSERT INTO sacoche_image( user_id,  image_objet,  image_contenu,  image_format,  image_largeur,  image_hauteur) ';
  $DB_SQL.= 'VALUES                   (:user_id, :image_objet, :image_contenu, :image_format, :image_largeur, :image_hauteur) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE ';
  $DB_SQL.= 'image_contenu=:image_contenu, image_format=:image_format, image_largeur=:image_largeur, image_hauteur=:image_hauteur ';
  $DB_VAR = array(
    ':user_id'       => $user_id,
    ':image_objet'   => $image_objet,
    ':image_contenu' => $image_contenu,
    ':image_format'  => $image_format,
    ':image_largeur' => $image_largeur,
    ':image_hauteur' => $image_hauteur,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_image
 *
 * @param int    $user_id       0 pour le logo ou le tampon de l'établissement
 * @param string $image_objet   "photo" | "signature" | "logo"
 * @return void
 */
public static function DB_supprimer_image($user_id,$image_objet)
{
  $DB_SQL = 'DELETE FROM sacoche_image ';
  $DB_SQL.= 'WHERE user_id=:user_id AND image_objet=:image_objet ';
  $DB_VAR = array(
    ':user_id'     => $user_id,
    ':image_objet' => $image_objet,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_images_notes
 *
 * @param void
 * @return array
 */
public static function DB_lister_images_notes()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_image_note ';
  $DB_SQL.= 'ORDER BY image_note_id ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_image_note
 *
 * @param string $image_contenu_h
 * @param string $image_contenu_v
 * @return int
 */
public static function DB_ajouter_image_note($image_contenu_h,$image_contenu_v)
{
  $DB_SQL = 'INSERT INTO sacoche_image_note( image_contenu_h, image_contenu_v) ';
  $DB_SQL.= 'VALUES                        (:image_contenu_h,:image_contenu_v) ';
  $DB_VAR = array(
    ':image_contenu_h' => $image_contenu_h,
    ':image_contenu_v' => $image_contenu_v,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_image_note
 *
 * @param int    $image_note_id
 * @return void
 */
public static function DB_supprimer_image_note($image_note_id)
{
  $DB_SQL = 'DELETE FROM sacoche_image_note ';
  $DB_SQL.= 'WHERE image_note_id=:image_note_id ';
  $DB_VAR = array(
    ':image_note_id' => $image_note_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>