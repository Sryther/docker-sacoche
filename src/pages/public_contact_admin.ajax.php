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
$nom      = (isset($_POST['f_nom']))      ? Clean::nom(     $_POST['f_nom']     ) : '';
$prenom   = (isset($_POST['f_prenom']))   ? Clean::prenom(  $_POST['f_prenom']  ) : '';
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : '';
$message  = (isset($_POST['f_message']))  ? Clean::texte(   $_POST['f_message'] ) : '';
$captcha  = (isset($_POST['f_captcha']))  ? Clean::lettres( $_POST['f_captcha'] ) : '';
$code     = (isset($_POST['f_code']))     ? Clean::entier(  $_POST['f_code']    ) : 0 ;
$md5      = (isset($_POST['f_md5']))      ? Clean::login(   $_POST['f_md5']     ) : '';

if( !$nom || !$prenom || !$courriel || !$message || ( (HEBERGEUR_INSTALLATION=='multi-structures') && !$BASE ) || ( $code && !$md5 ) || ( $md5 && !$code ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Protection contre les robots (pour éviter des envois intempestifs de courriels)
if(!isset($_SESSION['TMP']['CAPTCHA']))
{
  Json::end( FALSE , 'Session perdue ou absence de cookie : merci d\'actualiser la page.' );
}
else if( $_SERVER['REQUEST_TIME'] - $_SESSION['TMP']['CAPTCHA']['TIME'] < $_SESSION['TMP']['CAPTCHA']['DELAI'] )
{
  $_SESSION['TMP']['CAPTCHA']['TIME'] = $_SERVER['REQUEST_TIME'];
  Json::end( FALSE , 'Sécurité : patienter '.$_SESSION['TMP']['CAPTCHA']['DELAI'].'s avant une nouvelle tentative.' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Première soumission : envoyer un code de confirmation par courriel et conserver un code de contrôle
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!$code)
{
  // On vérifie le captcha.
  if( $captcha != $_SESSION['TMP']['CAPTCHA']['SOLUCE'] )
  {
    $_SESSION['TMP']['CAPTCHA']['DELAI']++;
    $_SESSION['TMP']['CAPTCHA']['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Ordre incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['TMP']['CAPTCHA']['DELAI'].'s.' );
  }
  // Vérifier le domaine du serveur mail même en mode mono-structure parce que de toutes façons il faudra ici envoyer un mail, donc l'installation doit être ouverte sur l'extérieur.
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
  }
  // Le code envoyé est un nombre à 8 chiffres
  $code = mt_rand(10000000,99999999);
  // Le md5 pour vérifier le code et la concordance des informations
  $md5 = md5($code.$BASE.$courriel);
  // Le courriel
  $mail_contenu = 'Bonjour,'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Pour confirmer l\'envoi du message aux administrateurs SACoche de l\'établissement scolaire sélectionné, veuillez saisir le code suivant dans le formulaire :'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= $code."\r\n";
  $mail_contenu.= Sesamail::texte_pied_courriel( array('excuses_derangement','info_connexion','no_reply','signature') , $courriel );
  $courriel_bilan = Sesamail::mail( $courriel , 'Contact administrateurs - Code de confirmation' , $mail_contenu , NULL );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
  }
  Json::end( TRUE , $md5 );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Deuxième soumission : vérifier le code de contrôle et prendre en compte la demande de contact
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($code)
{
  // On vérifie le code
  if( md5($code.$BASE.$courriel) != $md5 )
  {
    $_SESSION['TMP']['CAPTCHA']['DELAI']++;
    $_SESSION['TMP']['CAPTCHA']['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Code incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['TMP']['CAPTCHA']['DELAI'].'s.' );
  }
  // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    $result = DBextra::charger_parametres_mysql_supplementaires($BASE,FALSE);
    if(!$result)
    {
      Json::end( FALSE , 'Paramètres de connexion à la base de données non trouvés.' );
    }
  }
  // Notification (qui a la particularité d'être envoyée de suite, et avec tous les admins en destinataires du mail)
  $abonnement_ref = 'contact_externe';
  $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_avec_informations( $abonnement_ref );
  $destinataires_nb = count($DB_TAB);
  if(!$destinataires_nb)
  {
    // Normalement impossible, l'abonnement des admins à ce type de de notification étant obligatoire
    Json::end( FALSE , 'Aucun destinataire trouvé.' );
  }
  // Récupérer la dénomination de l'établissement
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    $structure_denomination = DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_nom_for_Id($BASE);
  }
  else
  {
    $DB_TAB = DB_STRUCTURE_PARAMETRE::DB_lister_parametres('"webmestre_denomination"');
    $structure_denomination = $DB_TAB[0]['parametre_valeur'];
  }
  $text_intro = ($destinataires_nb>1) ? 'Aux '.$destinataires_nb.' administrateurs' : 'À l\'administrateur' ;
  $text_intro.= ' de '.$structure_denomination.' (base n°'.$BASE.').';
  $tab_destinataires = array();
  $notification_contenu = $text_intro."\r\n".'Message de '.$prenom.' '.$nom.' ('.$courriel.') :'."\r\n\r\n".$message."\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
    DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_visible( $DB_ROW['user_id'] , $abonnement_ref , $notification_statut , $notification_contenu );
    if($notification_statut=='envoyée')
    {
      $tab_destinataires[] = $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>';
    }
  }
  if(count($tab_destinataires))
  {
    /**
     * L'envoi d'un contact externe depuis la page d'authentification est une exception à plusieurs titres :
     * - le numéro de base n'est pas en session
     * - envoi possible à plusieurs destinataires simultanéments
     * - notification obligatoire et immédiate
     * Du coup, le paramètre 'notif_individuelle' n'est pas transmis dans le tableau pour texte_pied_courriel().
     */
    $notification_contenu .= Sesamail::texte_pied_courriel( array('no_reply','signature') );
    $courriel_bilan = Sesamail::mail( $tab_destinataires , 'Notification - Contact externe' , $notification_contenu , NULL );
  }
  $admin_txt = ($destinataires_nb>1) ? 'aux '.$destinataires_nb.' administrateurs' : 'à l\'administrateur' ;
  Json::end( TRUE , $admin_txt );
}

?>