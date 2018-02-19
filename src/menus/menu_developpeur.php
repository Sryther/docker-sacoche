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

// Attention : en cas de changement d'indice d'un menu, répercuter la modif dans la partie Adaptations (en-dessous).

$tab_menu = array
(
  'information'   => "Informations",
  'param_serveur' => "Paramétrages serveur",
);

$tab_sous_menu = array
(
  'information' => array
  (
    'compte_accueil'          => array( 'texte' => "Accueil"                     , 'class' => 'compte_accueil' , 'href' => 'page=compte_accueil'          ),
    'webmestre_info_serveur'  => array( 'texte' => "Caractéristiques du serveur" , 'class' => 'serveur_info'   , 'href' => 'page=webmestre_info_serveur'  ),
    'webmestre_statistiques'  => array( 'texte' => "Statistiques d'utilisation"  , 'class' => 'statistiques'   , 'href' => 'page=webmestre_statistiques'  ), // multi-structures uniquement
    'webmestre_log_actions'   => array( 'texte' => "Log des actions sensibles"   , 'class' => 'log_actions'    , 'href' => 'page=webmestre_log_actions'   ),
    'developpeur_superviseur' => array( 'texte' => "Compte Superviseur"          , 'class' => 'administrateur' , 'href' => 'page=developpeur_superviseur' ),
  ),
  'param_serveur' => array
  (
    'webmestre_configuration_proxy'      => array( 'texte' => "Configuration d'un proxy"         , 'class' => 'serveur_proxy'        , 'href' => 'page=webmestre_configuration_proxy'      ),
    'webmestre_mail_bounces'             => array( 'texte' => "Adresse de rebond & Test mail"    , 'class' => 'newsletter'           , 'href' => 'page=webmestre_mail_bounces'             ),
    'webmestre_database_test'            => array( 'texte' => "Test des droits MySQL"            , 'class' => 'serveur_database'     , 'href' => 'page=webmestre_database_test'            ), // multi-structures uniquement
    'webmestre_configuration_filesystem' => array( 'texte' => "Droits du système de fichiers"    , 'class' => 'serveur_erreur'       , 'href' => 'page=webmestre_configuration_filesystem' ),
    'webmestre_maintenance'              => array( 'texte' => "Maintenance & Mise à jour"        , 'class' => 'serveur_maintenance'  , 'href' => 'page=webmestre_maintenance'              ),
    'webmestre_structure_bdd_repair'     => array( 'texte' => "Analyser / Réparer les bases"     , 'class' => 'structure_bdd_repair' , 'href' => 'page=webmestre_structure_bdd_repair'     ),
    'webmestre_debug'                    => array( 'texte' => "Débogueur"                        , 'class' => 'serveur_debug'        , 'href' => 'page=webmestre_debug'                    ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Différences de menu [mono-structure] / [multi-structures]
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  unset(
    $tab_sous_menu['information']['webmestre_statistiques'] ,
    $tab_sous_menu['param_serveur']['webmestre_database_test']
  );
}

?>