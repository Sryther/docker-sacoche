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

$action        = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])       : '';
$partenaire_id = (isset($_POST['f_id']))           ? Clean::entier($_POST['f_id'])          : 0;
$denomination  = (isset($_POST['f_denomination'])) ? Clean::texte($_POST['f_denomination']) : '';
$nom           = (isset($_POST['f_nom']))          ? Clean::nom($_POST['f_nom'])            : '';
$prenom        = (isset($_POST['f_prenom']))       ? Clean::prenom($_POST['f_prenom'])      : '';
$courriel      = (isset($_POST['f_courriel']))     ? Clean::courriel($_POST['f_courriel'])  : '';
$connecteurs   = (isset($_POST['f_connecteurs']))  ? Clean::texte($_POST['f_connecteurs'])  : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau partenaire conventionné
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $denomination && $nom && $prenom && $courriel && $connecteurs )
{
  // Vérifier le domaine du serveur mail (hébergement Sésamath donc serveur ouvert sur l'extérieur).
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
  }
  // Verifier que la liste des connecteurs commence et se termine par une virgule (corriger sinon)
  $connecteurs = (mb_substr($connecteurs,0,1)==',') ? $connecteurs : ','.$connecteurs ;
  $connecteurs = (mb_substr($connecteurs,-1) ==',') ? $connecteurs : $connecteurs.',' ;
  // Générer un mdp aléatoire
  $password = Outil::fabriquer_mdp();
  // Insérer l'enregistrement
  $partenaire_id = DB_WEBMESTRE_WEBMESTRE::DB_ajouter_partenaire_conventionne( $denomination , $nom , $prenom , $courriel , Outil::crypter_mdp($password) , $connecteurs );
  // Envoyer un courriel
  $texte = Webmestre::contenu_courriel_partenaire_ajout( $denomination , $nom , $prenom , $password , URL_DIR_SACOCHE );
  $courriel_bilan = Sesamail::mail( $courriel , 'Création compte partenaire ENT' , $texte );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
  }
  // Afficher le retour
  Json::add_str('<tr id="id_'.$partenaire_id.'" class="new">');
  Json::add_str(  '<td>'.$partenaire_id.'</td>');
  Json::add_str(  '<td>'.html($denomination).'</td>');
  Json::add_str(  '<td>'.html($nom).'</td>');
  Json::add_str(  '<td>'.html($prenom).'</td>');
  Json::add_str(  '<td>'.html($courriel).'</td>');
  Json::add_str(  '<td>'.html($connecteurs).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier ce partenaire."></q>');
  Json::add_str(    '<q class="initialiser_mdp" title="Générer un nouveau mdp pour ce partenaire."></q>');
  Json::add_str(    '<q class="supprimer" title="Retirer ce partenaire."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un partenaire conventionné existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $partenaire_id && $denomination && $nom && $prenom && $courriel && $connecteurs )
{
  // Vérifier le domaine du serveur mail (hébergement Sésamath donc serveur ouvert sur l'extérieur).
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
  }
  // Verifier que la liste des connecteurs commence et se termine par une virgule (corriger sinon)
  $connecteurs = (mb_substr($connecteurs,0,1)==',') ? $connecteurs : ','.$connecteurs ;
  $connecteurs = (mb_substr($connecteurs,-1) ==',') ? $connecteurs : $connecteurs.',' ;
  // Mettre à jour l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_partenaire_conventionne( $partenaire_id , $denomination , $nom , $prenom , $courriel , $connecteurs );
  // Afficher le retour
  Json::add_str('<td>'.$partenaire_id.'</td>');
  Json::add_str('<td>'.html($denomination).'</td>');
  Json::add_str('<td>'.html($nom).'</td>');
  Json::add_str('<td>'.html($prenom).'</td>');
  Json::add_str('<td>'.html($courriel).'</td>');
  Json::add_str('<td>'.html($connecteurs).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier ce partenaire."></q>');
  Json::add_str(  '<q class="initialiser_mdp" title="Générer un nouveau mdp pour ce partenaire."></q>');
  Json::add_str(  '<q class="supprimer" title="Retirer ce partenaire."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un nouveau mdp d'un partenaire conventionné et lui envoyer par courriel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='initialiser_mdp') && $partenaire_id && $denomination && $nom && $prenom && $courriel )
{
  // Générer un nouveau mdp
  $password = Outil::fabriquer_mdp();
  // Mettre à jour l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_partenaire_conventionne_mdp( $partenaire_id , Outil::crypter_mdp($password) );
  // Envoyer un courriel
  $courriel_contenu = Webmestre::contenu_courriel_partenaire_nouveau_mdp( $denomination , $nom , $prenom , $password , URL_DIR_SACOCHE );
  $courriel_bilan = Sesamail::mail( $courriel , 'Modification mdp compte partenaire ENT' , $courriel_contenu );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
  }
  // On affiche le retour
  Json::add_str('Le mot de passe de<br />'.html($prenom.' '.$nom).',<br />partenaire conventionné<br />"'.html($denomination).'",<br />vient d\'être réinitialisé.<br /><br />');
  Json::add_str('Les nouveaux identifiants<br />ont été envoyés<br />à son adresse de courriel<br />'.html($courriel).'.');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un partenaire conventionné existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $partenaire_id )
{
  // Supprimer l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_supprimer_partenaire_conventionne($partenaire_id);
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
