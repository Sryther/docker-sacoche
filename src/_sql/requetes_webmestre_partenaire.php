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
// Ces méthodes ne concernent que les partenaires ENT conventionnés.

class DB_WEBMESTRE_PARTENAIRE extends DB
{

/**
 * modifier_mdp_utilisateur
 *
 * @param int    $partenaire_id
 * @param string $password_ancien_crypte
 * @param string $password_nouveau_crypte
 * @return bool   TRUE si ok | FALSE si le mot de passe actuel est incorrect.
 */
public static function DB_modifier_mdp_partenaire($partenaire_id,$password_ancien_crypte,$password_nouveau_crypte)
{
  // Tester si l'ancien mot de passe correspond à celui enregistré
  $DB_SQL = 'SELECT partenaire_id ';
  $DB_SQL.= 'FROM sacoche_partenaire ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id AND partenaire_password=:password_crypte ';
  $DB_VAR = array(
    ':partenaire_id'   => $partenaire_id,
    ':password_crypte' => $password_ancien_crypte,
  );
  $DB_ROW = DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  if(empty($DB_ROW))
  {
    return FALSE;
  }
  // Remplacer par le nouveau mot de passe
  $DB_SQL = 'UPDATE sacoche_partenaire ';
  $DB_SQL.= 'SET partenaire_password=:password_crypte ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id ';
  $DB_VAR = array(
    ':partenaire_id'   => $partenaire_id,
    ':password_crypte' => $password_nouveau_crypte,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  return TRUE;
}

}
?>