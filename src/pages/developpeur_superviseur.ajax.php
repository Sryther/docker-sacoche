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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$action  = (isset($_POST['f_action']))  ? Clean::texte( $_POST['f_action'])  : NULL ;
$base_id = (isset($_POST['f_base_id'])) ? Clean::entier($_POST['f_base_id']) : NULL ;

if( is_null($base_id) || ( (HEBERGEUR_INSTALLATION=='multi-structures') && !$base_id ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  DBextra::charger_parametres_mysql_supplementaires($base_id);
}

$profil = 'ADM';
$login  = 'superviseur';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter le compte "Superviseur"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='ajouter')
{
  // Login
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant( 'login' , $login ) )
  {
    Json::end( FALSE , 'Login "superviseur" déjà utilisé ! Il est probable que ce compte existe déjà...' );
  }
  // Mdp
  $password = Outil::fabriquer_mdp();
  // Insérer l'enregistrement
  $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur( 0 /*user_sconet_id*/ , 0 /*sconet_num*/ , '' /*reference*/ , $profil , 'I' /*genre*/ , 'SUPERVISEUR' /*nom*/ , 'Développeur' /*prenom*/ , TODAY_MYSQL /*user_naissance_date*/ , '' /*courriel*/ , '' /*user_email_origine*/ , $login , Outil::crypter_mdp($password) );
  // Afficher le retour
  Json::add_row( 'label' , 'Compte créé avec succès.' );
  Json::add_row( 'texte' , '<ul class="puce">' );
  Json::add_row( 'texte' , '<li class="p">Login <b>'.$login.'</b> / Password <b>'.$password.'</b> / <a href="'.URL_DIR_SACOCHE.'?base='.$base_id.'&amp;mode=normal&amp;login='.$login.'" target="_blank" rel="noopener noreferrer">Connexion.</a></li>' );
  Json::add_row( 'texte' , '<li class="p"><label class="alerte">N\'oubliez pas de supprimer ce compte une fois les investigations terminées !</label></li>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer le compte "Superviseur"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='supprimer')
{
  // Id
  $user_id = DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant( 'login' , $login );
  if( !$user_id )
  {
    Json::end( FALSE , 'Compte de login "superviseur" introuvable !' );
  }
  // Supprimer l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $user_id , $profil );
  // Afficher le retour
  Json::add_row( 'label' , 'Compte retiré avec succès.' );
  Json::add_row( 'texte' , '' );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
