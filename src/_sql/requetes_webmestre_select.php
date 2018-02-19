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

// Ces méthodes ne concernent que la base WEBMESTRE (donc une installation multi-structures).
// Ces méthodes ne concernent que les options pour un SELECT.

class DB_WEBMESTRE_SELECT extends DB
{

/**
 * Retourner un tableau [valeur texte optgroup] des structures (choix d'établissements en page d'accueil)
 * l'indice géographique sert à regrouper les options
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_structures_sacoche()
{
  $DB_SQL = 'SELECT sacoche_base, structure_localisation, structure_denomination, geo_id, geo_nom ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
  $DB_SQL.= 'ORDER BY geo_ordre ASC, structure_localisation ASC, structure_denomination ASC';
  $DB_TAB = DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
  if(!empty($DB_TAB))
  {
    $tab_retour_champs = array();
    foreach($DB_TAB as $DB_ROW)
    {
      Form::$tab_select_optgroup['zones_geo'][$DB_ROW['geo_id']] = $DB_ROW['geo_nom'];
      $tab_retour_champs[] = array('valeur'=>$DB_ROW['sacoche_base'],'texte'=>$DB_ROW['structure_localisation'].' | '.$DB_ROW['structure_denomination'],'optgroup'=>$DB_ROW['geo_id']);
    }
    return $tab_retour_champs;
  }
  else
  {
    return 'Aucun établissement n\'est enregistré !';
  }
}

/**
 * Retourner un tableau [valeur texte] des partenaires conventionnés (authentification en page d'accueil)
 *
 * @param void
 * @return array
 */
public static function DB_OPT_partenaires_conventionnes()
{
  $DB_SQL = 'SELECT partenaire_id AS valeur, partenaire_denomination AS texte ';
  $DB_SQL.= 'FROM sacoche_partenaire ';
  $DB_SQL.= 'ORDER BY partenaire_denomination ASC';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les zones géographiques
 *
 * @param void
 * @return array
 */
public static function DB_OPT_lister_zones()
{
  $DB_SQL = 'SELECT geo_id AS valeur , geo_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_geo ';
  $DB_SQL.= 'ORDER BY geo_ordre ASC';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

}
?>