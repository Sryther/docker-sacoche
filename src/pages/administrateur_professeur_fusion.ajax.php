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

$action    = (isset($_POST['f_action']))    ? Clean::texte($_POST['f_action'])     : '';
$statut    = (isset($_POST['f_statut']))    ? Clean::texte($_POST['f_statut'])     : '';
$nom       = (isset($_POST['f_nom']))       ? Clean::nom($_POST['f_nom'])          : '';
$id_actuel = (isset($_POST['f_id_actuel'])) ? Clean::entier($_POST['f_id_actuel']) : 0 ;
$id_ancien = (isset($_POST['f_id_ancien'])) ? Clean::entier($_POST['f_id_ancien']) : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher un professeur / personnel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_statut = array(
  'actuel' => 1,
  'ancien' => 0,
);

if( ($action=='chercher') && isset($tab_statut[$statut]) && $nom )
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_user_for_fusion( $nom , 'personnel' , $tab_statut[$statut] );
  $nb_reponses = count($DB_TAB) ;
  if($nb_reponses==0)
  {
    Json::end( FALSE , 'Aucun professeur / personnel trouvé !' );
  }
  else if($nb_reponses==1)
  {
    Json::end( TRUE , '<option value="'.$DB_TAB[0]['user_id'].'">'.html($DB_TAB[0]['user_nom'].' '.$DB_TAB[0]['user_prenom'].' ['.$DB_TAB[0]['user_login'].']').'</option>' );
  }
  else
  {
    Json::add_str('<option value=""></option>');
    foreach($DB_TAB as $DB_ROW)
    {
      Json::add_str('<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_login'].']').'</option>');
    }
    Json::end( TRUE );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fusionner deux comptes professeur / personnel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='fusionner') && $id_actuel && $id_ancien )
{
  $DB_ROW = array(
    0 => DB_STRUCTURE_PUBLIC::DB_recuperer_donnees_utilisateur( 'switch' , $id_ancien ),
    1 => DB_STRUCTURE_PUBLIC::DB_recuperer_donnees_utilisateur( 'switch' , $id_actuel ),
  );
  // Vérifier l'existence / le profil / le statut
  if( empty($DB_ROW[0]) || ( ($DB_ROW[0]['user_profil_type']!='professeur') && ($DB_ROW[0]['user_profil_type']!='directeur') ) || ($DB_ROW[0]['user_sortie_date']>TODAY_MYSQL) )
  {
    Json::end( FALSE , 'Identifiant du compte désactivé incompatible !' );
  }
  if( empty($DB_ROW[1]) || ( ($DB_ROW[1]['user_profil_type']!='professeur') && ($DB_ROW[1]['user_profil_type']!='directeur') ) || ($DB_ROW[1]['user_sortie_date']<TODAY_MYSQL) )
  {
    Json::end( FALSE , 'Identifiant du compte activé incompatible !' );
  }
  if( $DB_ROW[1]['user_profil_sigle'] != $DB_ROW[0]['user_profil_sigle'] )
  {
    Json::end( FALSE , 'Comptes de profils différents ('.$DB_ROW[0]['user_profil_nom_court_singulier'].'/'.$DB_ROW[1]['user_profil_nom_court_singulier'].') !' );
  }
  // On fusionne les données (sauf tables sacoche_user, sacoche_jointure_user_groupe, sacoche_jointure_message_destinataire)
  DB_STRUCTURE_ADMINISTRATEUR::DB_fusionner_donnees_comptes_personnels( $id_ancien , $id_actuel );
  // On supprime l'ancien compte (dont liaisons sacoche_user, sacoche_jointure_user_groupe, sacoche_jointure_message_destinataire)
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $id_ancien , $DB_ROW[0]['user_profil_sigle'] );
  // On met à jour les données du comptes restant
  $tab_donnees = array();
  if( $DB_ROW[0]['user_connexion_date'] > $DB_ROW[1]['user_connexion_date'] )
  {
    $tab_donnees[':login']          = $DB_ROW[0]['user_login'];
    $tab_donnees[':password']       = $DB_ROW[0]['user_password'];
    $tab_donnees[':connexion_date'] = $DB_ROW[0]['user_connexion_date'];
    $tab_donnees[':param_accueil']  = $DB_ROW[0]['user_param_accueil'];
  }
  $tab_presence = array(
    ':sconet_id'     => 'user_sconet_id',
    ':sconet_num'    => 'user_sconet_elenoet',
    ':reference'     => 'user_reference',
    ':birth_date'    => 'user_naissance_date',
    ':courriel'      => 'user_email',
    ':email_origine' => 'user_email_origine',
    ':langue'        => 'user_langue',
    ':daltonisme'    => 'user_daltonisme',
    ':id_ent'        => 'user_id_ent',
    ':id_gepi'       => 'user_id_gepi',
  );
  foreach($tab_presence as $masque => $champ)
  {
    if( $DB_ROW[0][$champ] && !$DB_ROW[1][$champ] )
    {
      $tab_donnees[$masque] = $DB_ROW[0][$champ];
    }
  }
  if( ($DB_ROW[0]['user_genre']!='I') && ($DB_ROW[1]['user_genre']=='I') )
  {
    $tab_donnees[':genre'] = $DB_ROW[0]['user_genre'];
  }
  if(count($tab_donnees))
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_actuel , $tab_donnees );
  }
  // Log de l'action
  SACocheLog::ajouter('Fusion des comptes personnels '.$DB_ROW[0]['user_nom'].' '.$DB_ROW[0]['user_prenom'].' ('.$DB_ROW[0]['user_id'].') et '.$DB_ROW[1]['user_nom'].' '.$DB_ROW[1]['user_prenom'].' ('.$DB_ROW[1]['user_id'].').');
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a fusionné les comptes personnels '.$DB_ROW[0]['user_nom'].' '.$DB_ROW[0]['user_prenom'].' ('.$DB_ROW[0]['user_id'].') et '.$DB_ROW[1]['user_nom'].' '.$DB_ROW[1]['user_prenom'].' ('.$DB_ROW[1]['user_id'].').'."\r\n";
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
