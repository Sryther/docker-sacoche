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
// Ces méthodes ne concernent que le webmestre.

class DB_WEBMESTRE_WEBMESTRE extends DB
{

/**
 * Retourner les informations d'une structure donnée (complémentaire à lister_structures car utilisation de queryRow à la place de queryTab)
 *
 * @param int base_id
 * @return array
 */
public static function DB_recuperer_structure_by_Id($base_id)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(':base_id'=>$base_id);
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les informations d'une convention
 *
 * @param int convention_id
 * @return array
 */
public static function DB_recuperer_convention_structure_info($convention_id)
{
  $DB_SQL = 'SELECT sacoche_base, connexion_nom, convention_date_debut, convention_date_fin, ';
  $DB_SQL.= 'convention_signature, convention_paiement, convention_relance, convention_activation, convention_mail_renouv, ';
  $DB_SQL.= 'structure_denomination, structure_contact_nom, structure_contact_prenom, structure_contact_courriel ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'LEFT JOIN sacoche_structure USING (sacoche_base) ';
  $DB_SQL.= 'WHERE convention_id=:convention_id ';
  $DB_VAR = array(':convention_id'=>$convention_id);
  return DB::queryRow(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les zones géographiques
 *
 * @param void
 * @return array
 */
public static function DB_lister_zones()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_geo ';
  $DB_SQL.= 'ORDER BY geo_ordre ASC ';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les ids des structures
 *
 * @param void
 * @return array
 */
public static function DB_lister_structures_id()
{
  $DB_SQL = 'SELECT sacoche_base ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'ORDER BY sacoche_base ASC ';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * Lister les structures avec leurs infos
 *
 * @param void|string $listing_base_id   id des bases séparés par des virgules (tout si rien de transmis)
 * @param int         $geo_id            id d'une zone géographique si restriction à une zone (tout si rien ou 0 transmis)
 * @return array
 */
public static function DB_lister_structures( $listing_base_id=FALSE , $geo_id=0 )
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
  $DB_SQL.= ($listing_base_id==FALSE) ? '' : 'WHERE sacoche_base IN('.$listing_base_id.') ' ;
  $DB_SQL.= ($geo_id==0)              ? '' : 'WHERE geo_id=:geo_id ' ;
  $DB_SQL.= 'ORDER BY geo_ordre ASC, structure_localisation ASC, structure_denomination ASC ';
  $DB_VAR = array(':geo_id'=>$geo_id);
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister des contacts
 *
 * @param string $listing_base_id   id des bases séparés par des virgules
 * @return array                    le tableau est de la forme [i] => array('contact_id'=>...,'contact_nom'=>...,'contact_prenom'=>...,'contact_courriel'=>...);
 */
public static function DB_lister_contacts_cibles($listing_base_id)
{
  $DB_SQL = 'SELECT sacoche_base AS contact_id , structure_contact_nom AS contact_nom , structure_contact_prenom AS contact_prenom , structure_contact_courriel AS contact_courriel ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base IN('.$listing_base_id.') ';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les partenaires ENT conventionnés
 *
 * @param void
 * @return array
 */
public static function DB_lister_partenaires_conventionnes()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_partenaire ';
  $DB_SQL.= 'ORDER BY partenaire_denomination ASC';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Lister les conventions des établissements
 *
 * @param string $where_string
 * @return array
 */
public static function DB_lister_conventions_structures($where_string)
{
  $DB_SQL = 'SELECT sacoche_base, convention_id, connexion_nom, convention_date_debut, convention_date_fin,  ';
  $DB_SQL.= 'convention_signature, convention_paiement, convention_relance, convention_activation, convention_mail_renouv, convention_commentaire, ';
  $DB_SQL.= 'structure_uai, structure_localisation, structure_denomination, structure_contact_nom, structure_contact_prenom, structure_contact_courriel, ';
  $DB_SQL.= 'geo_ordre, geo_nom ';
  $DB_SQL.= 'FROM sacoche_convention ';
  $DB_SQL.= 'LEFT JOIN sacoche_structure USING (sacoche_base) ';
  $DB_SQL.= 'LEFT JOIN sacoche_geo USING (geo_id) ';
  if($where_string)
  {
    $DB_SQL.= 'WHERE '.$where_string.' ';
  }
  $DB_SQL.= 'ORDER BY convention_date_debut DESC, geo_ordre ASC, structure_localisation ASC, structure_denomination ASC ';
  return DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner le nom d'un établissement si le numéro de la base d'une structure est présent (mode multi-structures)
 *
 * @param int $base_id
 * @return string | NULL
 */
public static function DB_tester_structure_Id($base_id)
{
  $DB_SQL = 'SELECT structure_denomination ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(':base_id'=>$base_id);
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Tester si le nom d'une zone géographique est déjà pris
 *
 * @param string $geo_nom
 * @param int    $geo_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_zone_nom( $geo_nom , $geo_id=FALSE )
{
  $DB_SQL = 'SELECT geo_id ';
  $DB_SQL.= 'FROM sacoche_geo ';
  $DB_SQL.= 'WHERE geo_nom=:geo_nom ';
  $DB_SQL.= ($geo_id) ? 'AND geo_id!=:geo_id ' : '' ;
  $DB_VAR = array(
    ':geo_nom' => $geo_nom,
    ':geo_id'  => $geo_id,
  );
  return (int)DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Tester si l'UAI d'une structure est déjà pris
 *
 * @param string $structure_uai
 * @param int    $base_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_structure_UAI( $structure_uai , $base_id=FALSE )
{
  $DB_SQL = 'SELECT sacoche_base ';
  $DB_SQL.= 'FROM sacoche_structure ';
  $DB_SQL.= 'WHERE structure_uai=:structure_uai ';
  $DB_SQL.= ($base_id) ? 'AND sacoche_base!=:base_id ' : '' ;
  $DB_VAR = array(
    ':structure_uai' => $structure_uai,
    ':base_id'       => $base_id,
  );
  return (int)DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une zone géographique
 *
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return int
 */
public static function DB_ajouter_zone( $geo_ordre , $geo_nom )
{
  $DB_SQL = 'INSERT INTO sacoche_geo( geo_ordre, geo_nom) ';
  $DB_SQL.= 'VALUES(                 :geo_ordre,:geo_nom)';
  $DB_VAR = array(
    ':geo_ordre' => $geo_ordre,
    ':geo_nom'   => $geo_nom,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
}

/**
 * Insérer l'enregistrement d'une nouvelle structure dans la base du webmestre (mode multi-structures)
 *
 * @param int    $base_id   Pour forcer l'id de la base de la structure ; normalement transmis à 0 (=> auto-increment), sauf dans un cadre de gestion interne à Sésamath
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @param string $inscription_date   Pour forcer la date d'inscription, par exemple en cas de transfert de bases académiques (facultatif).
 * @return int
 */
public static function DB_ajouter_structure( $base_id , $geo_id , $structure_uai , $localisation , $denomination , $contact_nom , $contact_prenom , $contact_courriel , $inscription_date=NULL )
{
  $chaine_date = ($inscription_date) ? ':inscription_date' : 'NOW()' ;
  if($base_id==0)
  {
    $DB_SQL = 'INSERT INTO sacoche_structure( geo_id, structure_uai,structure_localisation,structure_denomination,structure_contact_nom,structure_contact_prenom,structure_contact_courriel,structure_inscription_date) ';
    $DB_SQL.= 'VALUES(                       :geo_id,:structure_uai,         :localisation,         :denomination,         :contact_nom,         :contact_prenom,         :contact_courriel,          '.$chaine_date.')';
    $DB_VAR = array(
      ':geo_id'           => $geo_id,
      ':structure_uai'    => $structure_uai,
      ':localisation'     => $localisation,
      ':denomination'     => $denomination,
      ':contact_nom'      => $contact_nom,
      ':contact_prenom'   => $contact_prenom,
      ':contact_courriel' => $contact_courriel,
      ':inscription_date' => $inscription_date,
    );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
    $base_id = DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
  }
  else
  {
    $DB_SQL = 'INSERT INTO sacoche_structure(sacoche_base, geo_id, structure_uai,structure_localisation,structure_denomination,structure_contact_nom,structure_contact_prenom,structure_contact_courriel,structure_inscription_date) ';
    $DB_SQL.= 'VALUES(                           :base_id,:geo_id,:structure_uai,         :localisation,         :denomination,         :contact_nom,         :contact_prenom,         :contact_courriel,          '.$chaine_date.')';
    $DB_VAR = array(
      ':base_id'          => $base_id,
      ':geo_id'           => $geo_id,
      ':structure_uai'    => $structure_uai,
      ':localisation'     => $localisation,
      ':denomination'     => $denomination,
      ':contact_nom'      => $contact_nom,
      ':contact_prenom'   => $contact_prenom,
      ':contact_courriel' => $contact_courriel,
      ':inscription_date' => $inscription_date,
    );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  return $base_id;
}

/**
 * Insérer l'enregistrement d'un partenaires ENT conventionné
 *
 * @param string $denomination
 * @param string $nom
 * @param string $prenom
 * @param string $courriel
 * @param string $password_crypte
 * @param string $connecteurs
 * @return int
 */
public static function DB_ajouter_partenaire_conventionne( $denomination , $nom , $prenom , $courriel , $password_crypte , $connecteurs )
{
  $DB_SQL = 'INSERT INTO sacoche_partenaire(partenaire_denomination,partenaire_nom,partenaire_prenom,partenaire_courriel,partenaire_password,partenaire_connecteurs) ';
  $DB_SQL.= 'VALUES(                                  :denomination,          :nom,          :prenom,          :courriel,   :password_crypte,          :connecteurs)';
  $DB_VAR = array(
    ':denomination'    => $denomination,
    ':nom'             => $nom,
    ':prenom'          => $prenom,
    ':courriel'        => $courriel,
    ':password_crypte' => $password_crypte,
    ':connecteurs'     => $connecteurs,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_WEBMESTRE_BD_NAME);
}

/**
 * Ajouter la base de données d'une structure, un utilisateur MySQL, et lui attribuer ses droits (mode multi-structures)
 *
 * @param int    $base_id   Pour forcer l'id de la base de la structure ; normalement transmis à 0 (=> auto-increment), sauf dans un cadre de gestion interne à Sésamath
 * @param string $BD_name
 * @param string $BD_user
 * @param string $BD_pass
 * @return void
 */
public static function DB_ajouter_base_structure_et_user_mysql( $base_id , $BD_name , $BD_user , $BD_pass )
{
  // Créer la base de données de la structure
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE DATABASE sac_base_'.$base_id );
  // Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
  // On doit créer en réalité un user sur "localhost" et un autre sur "%" car on doit pouvoir se connecter suivant les configurations depuis la machine locale comme depuis n'importe quel autre serveur (http://dev.mysql.com/doc/refman/5.0/fr/adding-users.html).
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE USER '.$BD_user.'@"localhost" IDENTIFIED BY "'.$BD_pass.'"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE USER '.$BD_user.'@"%" IDENTIFIED BY "'.$BD_pass.'"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.$BD_name.'.* TO '.$BD_user.'@"localhost"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE ON '.$BD_name.'.* TO '.$BD_user.'@"%"' );
}

/**
 * Modifier la version de la base
 *
 * @param string $parametre_nom
 * @param string $parametre_valeur
 * @return void
 */
public static function DB_modifier_parametre( $parametre_nom , $parametre_valeur )
{
  $DB_SQL = 'UPDATE sacoche_parametre ';
  $DB_SQL.= 'SET parametre_valeur=:parametre_valeur ';
  $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
  $DB_VAR = array(
    ':parametre_nom'    => $parametre_nom,
    ':parametre_valeur' => $parametre_valeur,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier les informations d'une structure
 *
 * @param int    $base_id
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @return void
 */
public static function DB_modifier_structure( $base_id , $geo_id , $structure_uai , $localisation , $denomination , $contact_nom , $contact_prenom , $contact_courriel )
{
  $DB_SQL = 'UPDATE sacoche_structure ';
  $DB_SQL.= 'SET geo_id=:geo_id, structure_uai=:structure_uai, structure_localisation=:localisation, structure_denomination=:denomination, ';
  $DB_SQL.= 'structure_contact_nom=:contact_nom, structure_contact_prenom=:contact_prenom, structure_contact_courriel=:contact_courriel ';
  $DB_SQL.= 'WHERE sacoche_base=:base_id ';
  $DB_VAR = array(
    ':base_id'          => $base_id,
    ':geo_id'           => $geo_id,
    ':structure_uai'    => $structure_uai,
    ':localisation'     => $localisation,
    ':denomination'     => $denomination,
    ':contact_nom'      => $contact_nom,
    ':contact_prenom'   => $contact_prenom,
    ':contact_courriel' => $contact_courriel
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier un partenaires ENT conventionné
 *
 * @param int    $partenaire_id
 * @param string $denomination
 * @param string $nom
 * @param string $prenom
 * @param string $courriel
 * @param string $connecteurs
 * @return int
 */
public static function DB_modifier_partenaire_conventionne( $partenaire_id , $denomination , $nom , $prenom , $courriel , $connecteurs )
{
  $DB_SQL = 'UPDATE sacoche_partenaire ';
  $DB_SQL.= 'SET partenaire_denomination=:denomination, partenaire_nom=:nom, partenaire_prenom=:prenom, partenaire_courriel=:courriel, partenaire_connecteurs=:connecteurs ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id ';
  $DB_VAR = array(
    ':partenaire_id' => $partenaire_id,
    ':denomination'  => $denomination,
    ':nom'           => $nom,
    ':prenom'        => $prenom,
    ':courriel'      => $courriel,
    ':connecteurs'   => $connecteurs,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le mdp d'un partenaires ENT conventionné
 *
 * @param int     $partenaire_id
 * @param string  $password_crypte
 * @return void
 */
public static function DB_modifier_partenaire_conventionne_mdp( $partenaire_id , $password_crypte )
{
  $DB_SQL = 'UPDATE sacoche_partenaire ';
  $DB_SQL.= 'SET partenaire_password=:password_crypte ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id ';
  $DB_VAR = array(
    ':partenaire_id'   => $partenaire_id,
    ':password_crypte' => $password_crypte,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier une zone géographique
 *
 * @param int    $geo_id
 * @param int    $geo_ordre
 * @param string $geo_nom
 * @return void
 */
public static function DB_modifier_zone( $geo_id , $geo_ordre , $geo_nom )
{
  $DB_SQL = 'UPDATE sacoche_geo ';
  $DB_SQL.= 'SET geo_ordre=:geo_ordre, geo_nom=:geo_nom ';
  $DB_SQL.= 'WHERE geo_id=:geo_id ';
  $DB_VAR = array(
    ':geo_id'    => $geo_id,
    ':geo_ordre' => $geo_ordre,
    ':geo_nom'   => $geo_nom,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier une date d'une convention
 *
 * @param int    convention_id
 * @param string objet         signature | paiement | relance | mail_renouv
 * @param string date_mysql
 * @return void
 */
public static function DB_modifier_convention_date( $convention_id , $objet , $date_mysql )
{
  $DB_SQL = 'UPDATE sacoche_convention ';
  $DB_SQL.= 'SET convention_'.$objet.'=:date_mysql ';
  $DB_SQL.= 'WHERE convention_id=:convention_id ';
  $DB_VAR = array(
    ':convention_id' => $convention_id,
    ':date_mysql'    => $date_mysql,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier l'état d'activation d'une convention
 *
 * @param int convention_id
 * @param int activation_etat
 * @return void
 */
public static function DB_modifier_convention_activation( $convention_id , $activation_etat )
{
  $DB_SQL = 'UPDATE sacoche_convention ';
  $DB_SQL.= 'SET convention_activation=:activation_etat ';
  $DB_SQL.= 'WHERE convention_id=:convention_id ';
  $DB_VAR = array(
    ':convention_id'   => $convention_id,
    ':activation_etat' => $activation_etat,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le commentaire d'une convention
 *
 * @param int    convention_id
 * @param string commentaire
 * @return void
 */
public static function DB_modifier_convention_commentaire( $convention_id , $commentaire )
{
  $DB_SQL = 'UPDATE sacoche_convention ';
  $DB_SQL.= 'SET convention_commentaire=:commentaire ';
  $DB_SQL.= 'WHERE convention_id=:convention_id ';
  $DB_VAR = array(
    ':convention_id' => $convention_id,
    ':commentaire'   => $commentaire,
  );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer une zone géographique
 *
 * @param int $geo_id
 * @return void
 */
public static function DB_supprimer_zone($geo_id)
{
  $DB_SQL = 'DELETE FROM sacoche_geo ';
  $DB_SQL.= 'WHERE geo_id=:geo_id ';
  $DB_VAR = array(':geo_id'=>$geo_id);
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
  // Il faut aussi mettre à jour les jointures avec les structures
  $DB_SQL = 'UPDATE sacoche_structure ';
  $DB_SQL.= 'SET geo_id=1 ';
  $DB_SQL.= 'WHERE geo_id=:geo_id ';
  $DB_VAR = array(':geo_id'=>$geo_id);
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retirer l'enregistrement d'une structure dans la base du webmestre (mode multi-structures)
 *
 * @param int    $BASE 
 * @return void
 */
public static function DB_supprimer_structure($BASE)
{
  $DB_SQL = 'DELETE FROM sacoche_structure ';
  $DB_SQL.= 'WHERE sacoche_base=:base ';
  $DB_VAR = array(':base'=>$BASE);
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer la base de données d'une structure, et son utilisateur MySQL une fois défait de ses droits (mode multi-structures)
 *
 * @param string $BD_name
 * @param string $BD_user
 * @return void
 */
public static function DB_supprimer_base_structure_et_user_mysql( $BD_name , $BD_user )
{
  // Supprimer la base associée à la structure
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP DATABASE '.$BD_name );
  // Retirer les droits et supprimer l'utilisateur pour la base de données de la structure
  // Apparemment et curieusement, il faut le droit 'CREATE TEMPORARY TABLES' pour pouvoir effectuer un REVOKE...
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.$BD_user.'@"localhost"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM '.$BD_user.'@"%"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP USER '.$BD_user.'@"localhost"' );
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'DROP USER '.$BD_user.'@"%"' );
}

/**
 * Retirer un partenaires ENT conventionné
 *
 * @param int    $partenaire_id 
 * @return void
 */
public static function DB_supprimer_partenaire_conventionne($partenaire_id)
{
  $DB_SQL = 'DELETE FROM sacoche_partenaire ';
  $DB_SQL.= 'WHERE partenaire_id=:partenaire_id ';
  $DB_VAR = array(':partenaire_id'=>$partenaire_id);
  DB::query(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>