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
// Ces méthodes ne concernent que les utilisateurs non identifiés (sauf pour DB_version_base() lors de la MAJ d'une base après restauration).

class DB_STRUCTURE_PUBLIC extends DB
{

/**
 * Récuperer, à partir d'un identifiant, les données d'un utilisateur tentant de se connecter (le mdp est comparé ensuite)
 *
 * @param string $mode_connection   'normal' | 'cas' | 'shibboleth' | 'siecle' | 'vecteur_parent' | 'gepi' | 'switch'
 * @param string $user_identifiant
 * @param string $parent_nom      facultatif, seulement pour $mode_connection = 'vecteur_parent'
 * @param string $parent_prenom   facultatif, seulement pour $mode_connection = 'vecteur_parent'
 * @return array
 */
public static function DB_recuperer_donnees_utilisateur($mode_connection,$user_identifiant,$parent_nom='',$parent_prenom='')
{
  switch($mode_connection)
  {
    case 'normal'         : $champ = 'user_login';     break;
    case 'cas'            : $champ = 'user_id_ent';    break;
    case 'shibboleth'     : $champ = 'user_id_ent';    break;
    case 'siecle'         : $champ = 'user_sconet_id'; break;
    case 'vecteur_parent' : $champ = 'user_id';        break; // C'est le user_sconet_id de l'élève qui est transmis, mais le user_id du parent trouvé qui est finalement utilisé dans la requête.
    case 'gepi'           : $champ = 'user_id_gepi';   break;
    case 'switch'         : $champ = 'user_id';        break;
  }
  if($mode_connection=='vecteur_parent')
  {
    // On cherche le parent à partir de l'Id Sconet de l'enfant
    // LIKE utilisé pour la restriction sur nom / prénom afin d'essayer d'éviter des pbs potentiels de prénoms composés ou de noms patronymiques non uniformisés...
    $DB_SQL = 'SELECT parent.user_id ';
    $DB_SQL.= 'FROM sacoche_user AS eleve ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
    $DB_SQL.= 'WHERE eleve.user_sconet_id=:eleve_sconet_id AND parent.user_nom LIKE :parent_nom_like AND parent.user_prenom LIKE :parent_prenom_like ';
    $DB_VAR = array(
      ':eleve_sconet_id'    => $user_identifiant,
      ':parent_nom_like'    => '%'.$parent_nom.'%',
      ':parent_prenom_like' => '%'.$parent_prenom.'%',
    );
    $user_identifiant = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    if(empty($user_identifiant)) return NULL;
  }
  $DB_SQL = 'SELECT sacoche_user.*, sacoche_user_profil.*, sacoche_groupe.groupe_nom, user_switch_id, ';
  $DB_SQL.= 'TIME_TO_SEC(TIMEDIFF(NOW(),sacoche_user.user_connexion_date)) AS delai_connexion_secondes  '; // TIMEDIFF() est plafonné à 839h, soit ~35j, mais peu importe ici.
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_switch ON user_switch_liste LIKE CONCAT("%,",user_id,",%") ';
  $DB_SQL.= 'WHERE '.$champ.'=:identifiant ';
  // LIMIT 1 a priori pas utile, et de surcroît queryRow ne renverra qu'une ligne
  $DB_VAR = array(':identifiant'=>$user_identifiant);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les utilisateurs à partir d'une adresse mail
 *
 * @param string $user_email
 * @return array
 */
public static function DB_lister_user_for_mail($user_email)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_profil_nom_court_singulier ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_email=:user_email ';
  $DB_VAR = array(':user_email'=>$user_email);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récuperer, à partir d'un identifiant ou d'un code transmis, quelques données d'un utilisateur demandant la génération d'un nouveau mdp
 *
 * @param string $champ_nom   user_id | user_pass_key
 * @param void   $champ_val
 * @return array
 */
public static function DB_recuperer_user_for_new_mdp($champ_nom,$champ_val)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_email, user_login, user_password, user_connexion_date ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE '.$champ_nom.'=:champ_val ';
  // LIMIT 1 a priori pas utile, et de surcroît queryRow ne renverra qu'une ligne
  $DB_VAR = array(':champ_val'=>$champ_val);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier la date de connexion
 *
 * @param int     $user_id
 * @return void
 */
public static function DB_enregistrer_date_connexion($user_id)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_connexion_date=NOW(), user_pass_key="" ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le mdp d'un utilisateur ou la clef pour en obtenir un autre (demande de génération si mdp perdu)
 *
 * @param int     $user_id
 * @param string  $user_password   vide pour ne pas le modifier
 * @param string  $user_pass_key
 * @return void
 */
public static function DB_modifier_user_password_or_key($user_id,$user_password,$user_pass_key)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_pass_key=:user_pass_key ';
  if($user_password)
  {
    $DB_SQL.= ', user_password=:user_password ';
  }
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(
    ':user_id'       => $user_id,
    ':user_pass_key' => $user_pass_key,
    ':user_password' => $user_password,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>