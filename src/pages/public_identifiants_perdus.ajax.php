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

$BASE     = (isset($_POST['f_base']))     ? Clean::entier(  $_POST['f_base']    ) : 0 ;
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : '';
$captcha  = (isset($_POST['f_captcha']))  ? Clean::lettres( $_POST['f_captcha'] ) : '';
$user_id  = (isset($_POST['f_user']))     ? Clean::entier(  $_POST['f_user']    ) : 0 ;

if( !$courriel ||  !$user_id || ( (HEBERGEUR_INSTALLATION=='multi-structures') && !$BASE ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Première soumission : rechercher le courriel et lister les utilisateurs correspondants
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($user_id==-1)
{
  // Protection contre les attaques par force brute des robots (piratage compte ou envoi intempestif de courriels)
  if(!isset($_SESSION['FORCEBRUTE'][$PAGE]))
  {
    Json::end( FALSE , 'Session perdue ou absence de cookie : merci d\'actualiser la page.' );
  }
  else if( $_SERVER['REQUEST_TIME'] - $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] < $_SESSION['FORCEBRUTE'][$PAGE]['DELAI'] )
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Sécurité : patienter '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s avant une nouvelle tentative.' );
  }
  // On vérifie le captcha.
  if( $captcha != $_SESSION['FORCEBRUTE'][$PAGE]['CAPTCHA'] )
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Ordre incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
  }
  // Vérifier le domaine du serveur mail même en mode mono-structure parce que de toutes façons il faudra ici envoyer un mail, donc l'installation doit être ouverte sur l'extérieur.
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
  }
  // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    DBextra::charger_parametres_mysql_supplementaires($BASE);
  }
  // On cherche des utilisateurs ayant cette adresse mail
  $DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_user_for_mail($courriel);
  if(empty($DB_TAB))
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Adresse inconnue ! Autre tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
  }
  // On retourne les utilisateurs trouvés
  if(count($DB_TAB)==1)
  {
    Json::add_str('<option value="'.$DB_TAB[0]['user_id'].'">'.html($DB_TAB[0]['user_nom'].' '.$DB_TAB[0]['user_prenom'].' ('.$DB_TAB[0]['user_profil_nom_court_singulier'].')').'</option>');
  }
  else
  {
    Json::add_str('<option value="">&nbsp;</option>');
    foreach($DB_TAB as $DB_ROW)
    {
      Json::add_str('<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_profil_nom_court_singulier'].')').'</option>');
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Deuxième soumission : récupérer les informations sur l'utilisateur et envoyer le courriel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($user_id)
{
  // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    DBextra::charger_parametres_mysql_supplementaires($BASE);
  }
  // On récupère les données de l'utilisateur
  $DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_user_for_new_mdp('user_id',$user_id);
  if(empty($DB_ROW))
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Utilisateur inconnu ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
  }
  // On vérifie que l'adresse mail concorde
  if( $DB_ROW['user_email'] != $courriel )
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Adresse mail non concordante ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
  }
  // On enregistre un ticket pour cette demande
  $user_pass_key = Outil::crypter_mdp($DB_ROW['user_id'].$DB_ROW['user_email'].$DB_ROW['user_password'].$DB_ROW['user_connexion_date']);
  $code_mdp = ($BASE) ? $user_pass_key.'g'.$BASE : $user_pass_key ;
  DB_STRUCTURE_PUBLIC::DB_modifier_user_password_or_key ($DB_ROW['user_id'] , '' /*user_password*/ , $user_pass_key /*user_pass_key*/ );
  // On envoi le courriel à l'utilisateur
  $mail_contenu = 'Bonjour,'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Une demande de nouveaux identifiants a été formulée pour le compte SACoche de '.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'.'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Pour confirmer la génération d\'un nouveau mot de passe, veuillez cliquer sur ce lien :'."\r\n";
  $mail_contenu.= URL_DIR_SACOCHE.'?code_mdp='.$code_mdp."\r\n";
  $mail_contenu.= Sesamail::texte_pied_courriel( array('excuses_derangement','info_connexion','no_reply','signature') , $DB_ROW['user_email'] );
  $courriel_bilan = Sesamail::mail( $DB_ROW['user_email'] , 'Demande de nouveaux identifiants' , $mail_contenu , NULL );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
  }
  // OK !
  Json::end( TRUE );
}

?>