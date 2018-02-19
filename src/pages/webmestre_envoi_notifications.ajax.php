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

$action            = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])          : '';
$send_notification = (isset($_POST['f_send']))         ? Clean::texte($_POST['f_send'])            : '';
$base_id           = (isset($_POST['f_base_id']))      ? Clean::entier($_POST['f_base_id'])        : 0;
$courriel_old      = (isset($_POST['f_courriel_old'])) ? Clean::courriel($_POST['f_courriel_old']) : '';
$courriel_new      = (isset($_POST['f_courriel_new'])) ? Clean::courriel($_POST['f_courriel_new']) : '';
$change            = (isset($_POST['f_change']))       ? Clean::texte($_POST['f_change'])          : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer le choix effectué pour l'envoi des notifications
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='choix_envoi') && in_array( $send_notification , array('oui','non') ) )
{
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array( 'COURRIEL_NOTIFICATION' => $send_notification ) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande de retrait ou changement d'un courriel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modif_mail') && (HEBERGEUR_INSTALLATION=='multi-structures') && $base_id && $courriel_old && ( ($change=='remove') || ( ($change=='replace') && $courriel_new ) ) )
{
  $tab_donnees = array();
  if( ($change=='replace') && $courriel_new )
  {
    // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
    list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel_new);
    if(!$is_domaine_valide)
    {
      Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
    }
    $tab_donnees[':courriel']      = $courriel_new;
    $tab_donnees[':email_origine'] = 'admin';
  }
  else
  {
    $tab_donnees[':courriel']      = '';
    $tab_donnees[':email_origine'] = '';
  }
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  if(DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_Id($base_id)===NULL)
  {
    Json::end( FALSE , 'Numéro de base '.$base_id.' non trouvé sur cette installation.' );
  }
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  // Chercher les users avec cet ancien mail
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_users( 'email' , $courriel_old );
  $nb_reponses = count($DB_TAB) ;
  if(!$nb_reponses)
  {
    Json::end( FALSE , 'Aucun utilisateur trouvé avec cette adresse de courriel.' );
  }
  // Effectuer le changement
  foreach($DB_TAB as $DB_ROW)
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $DB_ROW['user_id'] , $tab_donnees );
  }
  $s = ($nb_reponses==1) ? '' : 's' ;
  Json::end( TRUE , $nb_reponses.' compte'.$s.' modifié'.$s );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
