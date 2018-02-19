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

$action   = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])      : '';
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : NULL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour son adresse e-mail (éventuellement vide pour la retirer)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='courriel') && ($courriel!==NULL) )
{
  // Vérifier que l'utilisateur a les droits de la modifier / retirer
  if( ($_SESSION['USER_EMAIL_ORIGINE']=='admin') && ($_SESSION['USER_PROFIL_TYPE']!=='administrateur') && !Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
  {
    Json::end( FALSE , 'Droit insuffisant, contactez un administrateur !' );
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
    $email_origine = 'user';
  }
  else
  {
    $email_origine = '';
  }
  // C'est ok
  DB_STRUCTURE_COMMUN::DB_modifier_user_parametre( $_SESSION['USER_ID'] , 'user_email' , $courriel );
  $_SESSION['USER_EMAIL']         = $courriel ;
  $_SESSION['USER_EMAIL_ORIGINE'] = isset($email_origine) ? $email_origine : $_SESSION['USER_EMAIL_ORIGINE'] ; // si le mail n'a pas été changé alors il ne faut pas non plus modifier cette valeur
  // Construction du retour
  $info_origine = '';
  $info_edition = '';
  if( $_SESSION['USER_EMAIL'] && $_SESSION['USER_EMAIL_ORIGINE'] )
  {
    if($_SESSION['USER_EMAIL_ORIGINE']=='user')
    {
      $info_origine = '<span class="astuce">L\'adresse enregistrée a été saisie par vous-même.</span>';
    }
    else
    {
      $info_origine = '<span class="astuce">L\'adresse enregistrée a été importée ou saisie par un administrateur.</span>';
      if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
      {
        $info_edition = '<span class="astuce">Vous êtes habilité à modifier cette adresse si vous le souhaitez.</span>';
      }
      else
      {
        $info_edition = '<span class="danger">Vous n\'êtes pas habilité à modifier l\'adresse vous-même ! Veuillez contacter un administrateur.</span>';
        $disabled = ' disabled';
      }
    }
  }
  else
  {
    $info_origine = '<span class="astuce">Il n\'y a pas d\'adresse actuellement enregistrée.</span>';
  }
  if(COURRIEL_NOTIFICATION=='non')
  {
    $info_envoi_notifications = '<label class="alerte">Le webmestre du serveur a désactivé l\'envoi des notifications par courriel.</label>' ;
  }
  elseif(!$_SESSION['USER_EMAIL'])
  {
    $info_envoi_notifications = '<label class="alerte">Les envois par courriel seront remplacés par des indications en page d\'accueil tant que votre adresse de courriel ne sera pas renseignée.</label>' ;
  }
  else
  {
    $info_envoi_notifications = '<label class="valide">Votre adresse étant renseignée, vous pouvez opter pour des envois par courriel.</label>' ;
  }
  Json::end( TRUE ,  array( 'info_adresse'=>$info_origine.'<br />'.$info_edition , 'info_abonnement_mail'=>$info_envoi_notifications ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour ses abonnements aux notifications
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='enregistrer_abonnements')
{
  $tab_insert = array();
  $tab_update = array();
  $tab_delete = array();
  $tab_choix = array( 'non' , 'accueil' , 'courriel' );
  $DB_TAB  = DB_STRUCTURE_NOTIFICATION::DB_lister_abonnements_profil( $_SESSION['USER_PROFIL_TYPE'] , $_SESSION['USER_ID'] );
  $DB_JOIN = DB_STRUCTURE_NOTIFICATION::DB_lister_abonnements_user( $_SESSION['USER_ID'] );
  foreach($DB_TAB as $DB_ROW)
  {
    $DB_ROW['jointure_mode'] = isset($DB_JOIN[$DB_ROW['abonnement_ref']]) ? $DB_JOIN[$DB_ROW['abonnement_ref']]['jointure_mode'] : 'non' ;
    if( !isset($_POST[$DB_ROW['abonnement_ref']]) || !in_array($_POST[$DB_ROW['abonnement_ref']],$tab_choix) )
    {
      Json::end( FALSE , 'Donnée transmise manquante ou incorrecte !' );
    }
    if( ( $DB_ROW['abonnement_obligatoire'] && ($_POST[$DB_ROW['abonnement_ref']]=='non') ) || ( $DB_ROW['abonnement_courriel_only'] && ($_POST[$DB_ROW['abonnement_ref']]=='accueil') ) )
    {
      Json::end( FALSE , 'Donnée transmise interdite !' );
    }
    if( $DB_ROW['jointure_mode'] != $_POST[$DB_ROW['abonnement_ref']] )
    {
      if( $_POST[$DB_ROW['abonnement_ref']] == 'non' )
      {
        $tab_delete[$DB_ROW['abonnement_ref']] = $_POST[$DB_ROW['abonnement_ref']];
      }
      elseif( $DB_ROW['jointure_mode'] == 'non' )
      {
        $tab_insert[$DB_ROW['abonnement_ref']] = $_POST[$DB_ROW['abonnement_ref']];
      }
      else
      {
        $tab_update[$DB_ROW['abonnement_ref']] = $_POST[$DB_ROW['abonnement_ref']];
      }
    }
  }
  if(count($tab_delete))
  {
    foreach($tab_delete as $abonnement_ref => $jointure_mode)
    {
      DB_STRUCTURE_NOTIFICATION::DB_supprimer_abonnement( $_SESSION['USER_ID'] , $abonnement_ref );
    }
  }
  if(count($tab_insert))
  {
    foreach($tab_insert as $abonnement_ref => $jointure_mode)
    {
      DB_STRUCTURE_NOTIFICATION::DB_ajouter_abonnement( $_SESSION['USER_ID'] , $abonnement_ref , $jointure_mode );
    }
  }
  if(count($tab_update))
  {
    foreach($tab_update as $abonnement_ref => $jointure_mode)
    {
      DB_STRUCTURE_NOTIFICATION::DB_modifier_abonnement( $_SESSION['USER_ID'] , $abonnement_ref , $jointure_mode );
    }
  }
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
