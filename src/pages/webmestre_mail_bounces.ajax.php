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

$action   = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])      : '';
$bounce   = (isset($_POST['f_bounce']))   ? Clean::courriel($_POST['f_bounce'])   : '';
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer le choix effectué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='EnregistrerBounce')
{
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    if($bounce)
    {
      list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($bounce);
      if(!$is_domaine_valide)
      {
        Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
      }
    }
  }
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array( 'HEBERGEUR_MAILBOX_BOUNCE' => $bounce ) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Test d'envoi de courriel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='TestEnvoiCourriel') && $courriel )
{
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" ou serveur extérieur injoignable !' );
  }
  // Test d'envoi d'un courriel d'inscription
  $courriel_bilan = Sesamail::mail( $courriel , 'Test d\'envoi d\'un courriel d\'inscription' , 'Avec par défaut les coordonnées du webmestre en Reply-To.' );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel de test n°1 !' );
  }
  // Test d'envoi d'un courriel de notification
  $courriel_bilan = Sesamail::mail( $courriel , 'Test d\'envoi d\'une notification' , 'Sans Reply-To.' , NULL /*replyto*/ );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel de test n°2 !' );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
