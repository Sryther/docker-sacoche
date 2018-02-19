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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Menu [webmestre] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$s = (HEBERGEUR_INSTALLATION=='multi-structures') ? 's' : '' ;

// Attention : en cas de changement d'indice d'un menu, répercuter la modif dans la partie Adaptations (en-dessous).

$tab_menu = array
(
  'informations'         => "Informations",
  'param_techniques'     => "Paramétrages techniques",
  'param_installation'   => "Paramétrages installation",
  'param_etablissement'  => "Paramétrages établissement".$s,
  'gestion_inscriptions' => "Gestion des inscriptions", // multi-structures uniquement
);

$tab_sous_menu = array
(
  'informations' => array
  (
    'compte_accueil'         => array( 'texte' => "Accueil"                     , 'class' => 'compte_accueil' , 'href' => 'page=compte_accueil'         ),
    'webmestre_info_serveur' => array( 'texte' => "Caractéristiques du serveur" , 'class' => 'serveur_info'   , 'href' => 'page=webmestre_info_serveur' ),
    'webmestre_statistiques' => array( 'texte' => "Statistiques d'utilisation"  , 'class' => 'statistiques'   , 'href' => 'page=webmestre_statistiques' ),
    'webmestre_log_actions'  => array( 'texte' => "Log des actions sensibles"   , 'class' => 'log_actions'    , 'href' => 'page=webmestre_log_actions'  ),
  ),
  'param_techniques' => array
  (
    'webmestre_configuration_proxy'      => array( 'texte' => "Configuration d'un proxy"         , 'class' => 'serveur_proxy'        , 'href' => 'page=webmestre_configuration_proxy'      ),
    'webmestre_mail_bounces'             => array( 'texte' => "Adresse de rebond & Test mail"    , 'class' => 'newsletter'           , 'href' => 'page=webmestre_mail_bounces'             ),
    'webmestre_database_test'            => array( 'texte' => "Test des droits MySQL"            , 'class' => 'serveur_database'     , 'href' => 'page=webmestre_database_test'            ), // multi-structures uniquement
    'webmestre_configuration_filesystem' => array( 'texte' => "Droits du système de fichiers"    , 'class' => 'serveur_erreur'       , 'href' => 'page=webmestre_configuration_filesystem' ),
    'webmestre_structure_bdd_repair'     => array( 'texte' => "Analyser / Réparer les bases"     , 'class' => 'structure_bdd_repair' , 'href' => 'page=webmestre_structure_bdd_repair'     ),
    'webmestre_debug'                    => array( 'texte' => "Débogueur"                        , 'class' => 'serveur_debug'        , 'href' => 'page=webmestre_debug'                    ),
  ),
  'param_installation' => array
  (
    'webmestre_maintenance'           => array( 'texte' => "Maintenance & Mise à jour"     , 'class' => 'serveur_maintenance' , 'href' => 'page=webmestre_maintenance'           ),
    'webmestre_geographie'            => array( 'texte' => "Zones géographiques"           , 'class' => 'serveur_geographie'  , 'href' => 'page=webmestre_geographie'            ), // multi-structures uniquement
    'webmestre_partenariats'          => array( 'texte' => "Partenaires ENT conventionnés" , 'class' => 'administrateur'      , 'href' => 'page=webmestre_partenariats'          ), // multi-structures et serveur Sésamath uniquement
    'webmestre_identite_installation' => array( 'texte' => "Identité de l'installation"    , 'class' => 'serveur_identite'    , 'href' => 'page=webmestre_identite_installation' ),
    'compte_password'                 => array( 'texte' => "Mot de passe du webmestre"     , 'class' => 'compte_password'     , 'href' => 'page=compte_password'                 ),
  ),
  'param_etablissement' => array
  (
    'webmestre_envoi_notifications'  => array( 'texte' => "Courriels de notification"    , 'class' => 'newsletter'       , 'href' => 'page=webmestre_envoi_notifications'  ),
    'webmestre_fichiers_deposes'     => array( 'texte' => "Fichiers déposés"             , 'class' => 'serveur_fichiers' , 'href' => 'page=webmestre_fichiers_deposes'     ),
    'webmestre_contact_modification' => array( 'texte' => "Coordonnées contact référent" , 'class' => 'mail'             , 'href' => 'page=webmestre_contact_modification' ), // multi-structures uniquement
    'webmestre_mdp_admin'            => array( 'texte' => "Mot de passe administrateur"  , 'class' => 'mdp_admin'        , 'href' => 'page=webmestre_mdp_admin'            ), // mono-structure uniquement
    'webmestre_resilier'             => array( 'texte' => "Résilier l'inscription"       , 'class' => 'resilier'         , 'href' => 'page=webmestre_resilier'             ), // mono-structure uniquement
  ),
  'gestion_inscriptions' => array
  (
    'webmestre_structure_gestion'   => array( 'texte' => "Gestion des établissements" , 'class' => 'structure_gestion'   , 'href' => 'page=webmestre_structure_gestion'   ),
    'webmestre_structure_ajout_csv' => array( 'texte' => "Ajout CSV d'établissements" , 'class' => 'structure_ajout_csv' , 'href' => 'page=webmestre_structure_ajout_csv' ),
    'webmestre_structure_transfert' => array( 'texte' => "Transfert d'établissements" , 'class' => 'structure_transfert' , 'href' => 'page=webmestre_structure_transfert' ),
    'webmestre_newsletter'          => array( 'texte' => "Lettre d'information"       , 'class' => 'newsletter'          , 'href' => 'page=webmestre_newsletter'          ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Différences de menu [mono-structure] / [multi-structures]
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  unset(
    $tab_sous_menu['param_techniques']['webmestre_database_test'] ,
    $tab_sous_menu['param_installation']['webmestre_geographie'] ,
    $tab_sous_menu['param_installation']['webmestre_partenariats'] ,
    $tab_sous_menu['param_etablissement']['webmestre_contact_modification'] ,
    $tab_menu['gestion_inscriptions']
  );
}
else
{
  unset(
    $tab_sous_menu['param_etablissement']['webmestre_mdp_admin'] ,
    $tab_sous_menu['param_etablissement']['webmestre_resilier']
  );
  if(!IS_HEBERGEMENT_SESAMATH)
  {
    unset( $tab_sous_menu['param_installation']['webmestre_partenariats'] );
  }
}

?>