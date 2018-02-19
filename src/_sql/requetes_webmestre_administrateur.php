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
// Ces méthodes ne concernent que les administrateurs d'un établissement donné.

class DB_WEBMESTRE_ADMINISTRATEUR extends DB
{

/**
 * Modifier le contact d'un établissement
 *
 * @param int    $base_id
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @return void
 */
public static function DB_modifier_contact_infos( $base_id , $contact_nom , $contact_prenom , $contact_courriel )
{
  $DB_SQL = 'UPDATE sacoche_structure ';
  $DB_SQL.= 'SET structure_contact_nom=:contact_nom, structure_contact_prenom=:contact_prenom, structure_contact_courriel=:contact_courriel ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(
    ':base_id'          => $base_id,
    ':contact_nom'      => $contact_nom,
    ':contact_prenom'   => $contact_prenom,
    ':contact_courriel' => $contact_courriel
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les coordonnées du contact référent d'un établissement
 *
 * @param int base_id
 * @return array
 */
public static function DB_recuperer_contact_infos($base_id)
{
  $DB_SQL = 'SELECT structure_contact_nom, structure_contact_prenom, structure_contact_courriel ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(':base_id'=>$base_id);
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les conventions d'un établissement
 *
 * @param int base_id
 * @return array
 */
public static function DB_lister_conventions_structure($base_id)
{
  $DB_SQL = 'SELECT convention_id, connexion_nom, convention_date_debut, convention_date_fin, convention_creation, convention_signature, convention_paiement, convention_activation ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_SQL.= 'ORDER BY convention_date_debut DESC ';
  $DB_VAR = array(':base_id'=>$base_id);
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_convention_precise
 *
 * @param int    $base_id
 * @param string $connexion_nom
 * @param string $convention_date_debut
 * @return int
 */
public static function DB_tester_convention_precise( $base_id , $connexion_nom , $convention_date_debut )
{
  $DB_SQL = 'SELECT convention_id ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id AND connexion_nom=:connexion_nom AND convention_date_debut=:convention_date_debut ';
  $DB_VAR = array(
    ':base_id'               => $base_id,
    ':connexion_nom'         => $connexion_nom,
    ':convention_date_debut' => $convention_date_debut,
  );
  return (int)DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_convention
 *
 * @param int    $base_id
 * @param string $connexion_nom
 * @param string $convention_date_debut
 * @param string $convention_date_fin
 * @return int
 */
public static function DB_ajouter_convention( $base_id , $connexion_nom , $convention_date_debut , $convention_date_fin )
{
  $DB_SQL = 'INSERT INTO sacoche_convention(sacoche_base, connexion_nom, convention_date_debut, convention_date_fin,convention_creation, convention_signature, convention_paiement, convention_activation, convention_mail_renouv, convention_commentaire) ';
  $DB_SQL.= 'VALUES(                            :base_id,:connexion_nom,:convention_date_debut,:convention_date_fin,              NOW(),:convention_signature,:convention_paiement,:convention_activation,:convention_mail_renouv,:convention_commentaire)';
  $DB_VAR = array(
    ':base_id'                => $base_id,
    ':connexion_nom'          => $connexion_nom,
    ':convention_date_debut'  => $convention_date_debut,
    ':convention_date_fin'    => $convention_date_fin,
    ':convention_signature'   => NULL,
    ':convention_paiement'    => NULL,
    ':convention_activation'  => 0,
    ':convention_mail_renouv' => NULL,
    ':convention_commentaire' => NULL,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
}

/**
 * recuperer_convention
 *
 * @param int    $convention_id
 * @return array
 */
public static function DB_recuperer_convention($convention_id)
{
  $DB_SQL = 'SELECT sacoche_base, connexion_nom, convention_date_debut, convention_date_fin, convention_creation, convention_signature, convention_paiement, convention_activation ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'WHERE convention_id=:convention_id ';
  $DB_VAR = array(':convention_id'=>$convention_id);
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>