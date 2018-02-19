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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action       = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])      : '';
$id           = (isset($_POST['f_id']))          ? Clean::entier($_POST['f_id'])         : 0;
$id_ent       = (isset($_POST['f_id_ent']))      ? Clean::id_ent($_POST['f_id_ent'])     : '';
$id_gepi      = (isset($_POST['f_id_gepi']))     ? Clean::id_ent($_POST['f_id_gepi'])    : '';
$profil       = 'ADM';
$genre        = (isset($_POST['f_genre']))       ? Clean::lettres($_POST['f_genre'])     : '';
$nom          = (isset($_POST['f_nom']))         ? Clean::nom($_POST['f_nom'])           : '';
$prenom       = (isset($_POST['f_prenom']))      ? Clean::prenom($_POST['f_prenom'])     : '';
$login        = (isset($_POST['f_login']))       ? Clean::login($_POST['f_login'])       : '';
$password     = (isset($_POST['f_password']))    ? Clean::password($_POST['f_password']) : '' ;
$box_login    = (isset($_POST['box_login']))     ? Clean::entier($_POST['box_login'])    : 0;
$box_password = (isset($_POST['box_password']))  ? Clean::entier($_POST['box_password']) : 0;
$courriel     = (isset($_POST['f_courriel']))    ? Clean::courriel($_POST['f_courriel']) : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouvel administrateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && isset(Html::$tab_genre['adulte'][$genre]) && $nom && $prenom && ($box_login || $login) && ($box_password || $password) )
{
  // Vérifier que l'identifiant ENT est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_ent)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent) )
    {
      Json::end( FALSE , 'Identifiant ENT déjà utilisé !' );
    }
  }
  // Vérifier que l'identifiant GEPI est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_gepi)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi) )
    {
      Json::end( FALSE , 'Identifiant Gepi déjà utilisé !' );
    }
  }
  if($box_login)
  {
    // Construire puis tester le login (parmi tous les utilisateurs de l'établissement)
    $login = Outil::fabriquer_login($prenom,$nom,$profil);
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
    {
      // Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
      $login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
    }
  }
  else
  {
    // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
    {
      Json::end( FALSE , 'Login déjà utilisé !' );
    }
  }
  if($box_password)
  {
    // Générer un mdp aléatoire
    $password = Outil::fabriquer_mdp($profil);
  }
  else
  {
    // Vérifier que le mdp transmis est d'une longueur compatible
    if(mb_strlen($password)<$_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'][$profil])
    {
      Json::end( FALSE , 'Mot de passe trop court pour ce profil !' );
    }
  }
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if($courriel)
  {
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
      if(!$is_domaine_valide)
      {
        Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
      }
    }
  }
  $user_email_origine = ($courriel) ? 'admin' : '' ;
  // Insérer l'enregistrement
  $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur( 0 /*user_sconet_id*/ , 0 /*sconet_num*/ , '' /*reference*/ , $profil , $genre , $nom , $prenom , NULL /*user_naissance_date*/ , $courriel , $user_email_origine , $login , Outil::crypter_mdp($password) , $id_ent , $id_gepi );
  // Pour les admins, abonnement obligatoire aux contacts effectués depuis la page d'authentification
  DB_STRUCTURE_NOTIFICATION::DB_ajouter_abonnement( $user_id , 'contact_externe' , 'accueil' );
  // Afficher le retour
  Json::add_str('<tr id="id_'.$user_id.'" class="new">');
  Json::add_str(  '<td>'.html($id_ent).'</td>');
  Json::add_str(  '<td>'.html($id_gepi).'</td>');
  Json::add_str(  '<td>'.Html::$tab_genre['adulte'][$genre].'</td>');
  Json::add_str(  '<td>'.html($nom).'</td>');
  Json::add_str(  '<td>'.html($prenom).'</td>');
  Json::add_str(  '<td class="new">'.html($login).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le login !" /></td>');
  Json::add_str(  '<td class="new">'.html($password).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le mot de passe !" /></td>');
  Json::add_str(  '<td>'.html($courriel).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cet administrateur."></q>');
  Json::add_str(    '<q class="supprimer" title="Retirer cet administrateur."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un administrateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && isset(Html::$tab_genre['adulte'][$genre]) && $nom && $prenom && ($box_login || $login) && ( $box_password || $password ) )
{
  $tab_donnees = array();
  // Vérifier que l'identifiant ENT est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_ent)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent,$id) )
    {
      Json::end( FALSE , 'Identifiant ENT déjà utilisé !' );
    }
  }
  // Vérifier que l'identifiant GEPI est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_gepi)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi,$id) )
    {
      Json::end( FALSE , 'Identifiant Gepi déjà utilisé !' );
    }
  }
  // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
  if(!$box_login)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$id) )
    {
      Json::end( FALSE , 'Login déjà utilisé !' );
    }
    $tab_donnees[':login'] = $login;
  }
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if($courriel)
  {
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
      if(!$is_domaine_valide)
      {
        Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
      }
    }
    $tab_donnees[':email_origine'] = 'admin';
  }
  else
  {
    $tab_donnees[':email_origine'] = '';
  }
  // Cas du mot de passe
  if(!$box_password)
  {
    $tab_donnees[':password'] = Outil::crypter_mdp($password);
  }
  // Mettre à jour l'enregistrement
  $tab_donnees += array(
    ':genre'    => $genre,
    ':nom'      => $nom,
    ':prenom'   => $prenom,
    ':courriel' => $courriel,
    ':id_ent'   => $id_ent,
    ':id_gepi'  => $id_gepi,
  );
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id , $tab_donnees );
  // Mettre à jour aussi éventuellement la session
  if($id==$_SESSION['USER_ID'])
  {
    $_SESSION['USER_GENRE']         = $genre ;
    $_SESSION['USER_NOM']           = $nom ;
    $_SESSION['USER_PRENOM']        = $prenom ;
    $_SESSION['USER_EMAIL']         = $courriel ;
    $_SESSION['USER_EMAIL_ORIGINE'] = isset($tab_donnees[':email_origine']) ? $tab_donnees[':email_origine'] : $_SESSION['USER_EMAIL_ORIGINE'] ; // si le mail n'a pas été changé alors il ne faut pas non plus modifier cette valeur
    $_SESSION['USER_LOGIN']         = $login ;
    $_SESSION['USER_ID_ENT']        = $id_ent ;
    $_SESSION['USER_ID_GEPI']       = $id_gepi ;
  }
  // Afficher le retour
  Json::add_str('<td>'.html($id_ent).'</td>');
  Json::add_str('<td>'.html($id_gepi).'</td>');
  Json::add_str('<td>'.Html::$tab_genre['adulte'][$genre].'</td>');
  Json::add_str('<td>'.html($nom).'</td>');
  Json::add_str('<td>'.html($prenom).'</td>');
  Json::add_str('<td>'.html($login).'</td>');
  Json::add_str( ($box_password) ? '<td class="i">champ crypté</td>' : '<td class="new">'.$password.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le mot de passe !" /></td>');
  Json::add_str('<td>'.html($courriel).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier ce administrateur."></q>');
  Json::add_str(  ($id!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Retirer cet administrateur."></q>' : '<q class="supprimer_non" title="Un administrateur ne peut pas supprimer son propre compte."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un administrateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom && $prenom )
{
  if($id==$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Un administrateur ne peut pas supprimer son propre compte !' );
  }
  // Supprimer l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $id , $profil );
  // Log de l'action
  SACocheLog::ajouter('Suppression de l\'utilisateur '.$nom.' '.$prenom.' ('.$profil.' '.$id.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé l\'utilisateur '.$nom.' '.$prenom.' ('.$profil.' '.$id.').'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
