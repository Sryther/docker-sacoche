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
// Menu [administrateur] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Attention : en cas de changement d'indice d'un menu, répercuter la modif dans la partie Adaptations (en-dessous).

$tab_menu = array
(
  'information'              => Lang::_("Informations"),
  'param_etablissement'      => Lang::_("Paramétrages établissement"),
  'param_utilisateurs'       => Lang::_("Paramétrages utilisateurs"),
  'param_personnels'         => Lang::_("Paramétrages personnels"),
  'admin_generale'           => Lang::_("Administration générale"),
  'import'                   => Lang::_("Import de données"),
  'gestion_groupes_periodes' => Lang::_("Gestion regroupements / périodes"),
  'gestion_utilisateurs'     => Lang::_("Gestion des utilisateurs"),
  'gestion_bilans'           => Lang::_("Gestion des bilans officiels"),
);

$tab_sous_menu = array
(
  'information' => array
  (
    'compte_accueil'                   => array( 'texte' => Lang::_("Accueil")                    , 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    'consultation_date_connexion'      => array( 'texte' => Lang::_("Date de dernière connexion") , 'class' => 'date_connexion'   , 'href' => 'page=consultation_date_connexion'      ),
    'export_fichier'                   => array( 'texte' => Lang::_("Extraction de données")      , 'class' => 'fichier_export'   , 'href' => 'page=export_fichier'                   ),
    'administrateur_log_actions'       => array( 'texte' => Lang::_("Log des actions sensibles")  , 'class' => 'log_actions'      , 'href' => 'page=administrateur_log_actions'       ),
    'consultation_notifications'       => array( 'texte' => Lang::_("Notifications reçues")       , 'class' => 'newsletter'       , 'href' => 'page=consultation_notifications'       ),
    'consultation_referentiel_interne' => array( 'texte' => Lang::_("Référentiels en place")      , 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' ),
  ),
  'param_etablissement' => array
  (
    'administrateur_etabl_identite'     => array( 'texte' => Lang::_("Identité de l'établissement")              , 'class' => 'etabl_identite'     , 'href' => 'page=administrateur_etabl_identite'     ),
    'administrateur_etabl_connexion'    => array( 'texte' => Lang::_("Mode d'identification / Connecteur ENT")   , 'class' => 'etabl_connexion'    , 'href' => 'page=administrateur_etabl_connexion'    ),
    'administrateur_etabl_deconnexion'  => array( 'texte' => Lang::_("Redirection après déconnexion")            , 'class' => 'etabl_deconnexion'  , 'href' => 'page=administrateur_etabl_deconnexion'  ),
    'administrateur_etabl_niveau'       => array( 'texte' => Lang::_("Niveaux")                                  , 'class' => 'etabl_choix'        , 'href' => 'page=administrateur_etabl_niveau'       ),
    'administrateur_etabl_matiere'      => array( 'texte' => Lang::_("Matières")                                 , 'class' => 'etabl_choix'        , 'href' => 'page=administrateur_etabl_matiere'      ),
    'administrateur_etabl_notes_acquis' => array( 'texte' => Lang::_("Codes de notation / États d'acquisition")  , 'class' => 'etabl_couleurs'     , 'href' => 'page=administrateur_etabl_notes_acquis' ),
    'administrateur_etabl_algorithme'   => array( 'texte' => Lang::_("Algorithme de calcul")                     , 'class' => 'algorithme_edition' , 'href' => 'page=administrateur_etabl_algorithme'   ),
  ),
  'param_utilisateurs' => array
  (
    'administrateur_etabl_profils'          => array( 'texte' => Lang::_("Choix des profils utilisateurs")       , 'class' => 'directeur'           , 'href' => 'page=administrateur_etabl_profils'          ),
    'administrateur_etabl_autorisations'    => array( 'texte' => Lang::_("Réglage des autorisations")            , 'class' => 'etabl_autorisations' , 'href' => 'page=administrateur_etabl_autorisations'    ),
    'administrateur_etabl_login'            => array( 'texte' => Lang::_("Format des identifiants de connexion") , 'class' => 'etabl_login'         , 'href' => 'page=administrateur_etabl_login'            ),
    'administrateur_etabl_duree_inactivite' => array( 'texte' => Lang::_("Délai avant déconnexion")              , 'class' => 'etabl_duree'         , 'href' => 'page=administrateur_etabl_duree_inactivite' ),
    'administrateur_etabl_menus_raccourcis' => array( 'texte' => Lang::_("Menus et raccourcis")                  , 'class' => 'favori'              , 'href' => 'page=administrateur_etabl_menus_raccourcis' ),
  ),
  'param_personnels' => array
  (
    'compte_password' => array( 'texte' => Lang::_("Mot de passe")                 , 'class' => 'compte_password' , 'href' => 'page=compte_password' ),
    'compte_email'    => array( 'texte' => Lang::_("Adresse e-mail & Abonnements") , 'class' => 'mail'            , 'href' => 'page=compte_email'    ),
    'compte_switch'   => array( 'texte' => Lang::_("Bascule entre comptes")        , 'class' => 'compte_switch'   , 'href' => 'page=compte_switch'   ),
    'compte_langue'   => array( 'texte' => Lang::_("Langue")                       , 'class' => 'compte_langue'   , 'href' => 'page=compte_langue'   ),
  ),
  'admin_generale' => array
  (
    'administrateur_dump'      => array( 'texte' => Lang::_("Sauvegarder / Restaurer la base") , 'class' => 'dump'            , 'href' => 'page=administrateur_dump'      ),
    'administrateur_nettoyage' => array( 'texte' => Lang::_("Nettoyer / Initialiser la base")  , 'class' => 'nettoyage'       , 'href' => 'page=administrateur_nettoyage' ),
    'compte_message'           => array( 'texte' => Lang::_("Messages d'accueil")              , 'class' => 'message_accueil' , 'href' => 'page=compte_message'           ),
    'administrateur_blocage'   => array( 'texte' => Lang::_("Blocage des connexions")          , 'class' => 'blocage'         , 'href' => 'page=administrateur_blocage'   ),
    'administrateur_resilier'  => array( 'texte' => Lang::_("Résilier l'inscription")          , 'class' => 'resilier'        , 'href' => 'page=administrateur_resilier'  ),
  ),
  'import' => array
  (
    'administrateur_fichier_user'        => array( 'texte' => Lang::_("Importer des fichiers d'utilisateurs") , 'class' => 'fichier_import'     , 'href' => 'page=administrateur_fichier_user'        ),
    'administrateur_fichier_identifiant' => array( 'texte' => Lang::_("Importer / Imposer des identifiants")  , 'class' => 'fichier_import'     , 'href' => 'page=administrateur_fichier_identifiant' ),
    'administrateur_fichier_sacoche'     => array( 'texte' => Lang::_("Transfert de saisies depuis SACoche")  , 'class' => 'fichier_import'     , 'href' => 'page=administrateur_fichier_sacoche'     ),
    'officiel_assiduite'                 => array( 'texte' => Lang::_("Absences / Retards")                   , 'class' => 'officiel_assiduite' , 'href' => 'page=officiel&amp;section=assiduite'     ), // Volontairement en doublon
  ),
  'gestion_groupes_periodes' => array
  (
    'administrateur_classe'  => array( 'texte' => Lang::_("Classes")  , 'class' => 'groupe'  , 'href' => 'page=administrateur_classe'  ),
    'administrateur_groupe'  => array( 'texte' => Lang::_("Groupes")  , 'class' => 'groupe'  , 'href' => 'page=administrateur_groupe'  ),
    'administrateur_periode' => array( 'texte' => Lang::_("Périodes") , 'class' => 'periode' , 'href' => 'page=administrateur_periode' ),
  ),
  'gestion_utilisateurs' => array
  (
    'administrateur_eleve'          => array( 'texte' => Lang::_("Élèves")                    , 'class' => 'eleve'          , 'href' => 'page=administrateur_eleve'          ),
    'administrateur_parent'         => array( 'texte' => Lang::_("Parents")                   , 'class' => 'parent'         , 'href' => 'page=administrateur_parent'         ),
    'administrateur_professeur'     => array( 'texte' => Lang::_("Professeurs / Personnels")  , 'class' => 'professeur'     , 'href' => 'page=administrateur_professeur'     ),
    'administrateur_administrateur' => array( 'texte' => Lang::_("Administrateurs")           , 'class' => 'administrateur' , 'href' => 'page=administrateur_administrateur' ),
    'administrateur_user_recherche' => array( 'texte' => Lang::_("Rechercher un utilisateur") , 'class' => 'user_recherche' , 'href' => 'page=administrateur_user_recherche' ),
  ),
  'gestion_bilans' => array
  (
    'officiel_reglages'         => array( 'texte' => Lang::_("Réglages communs")        , 'class' => 'officiel_reglages'     , 'href' => 'page=officiel&amp;section=reglages'         ),
    'officiel_assiduite'        => array( 'texte' => Lang::_("Absences / Retards")      , 'class' => 'officiel_assiduite'    , 'href' => 'page=officiel&amp;section=assiduite'        ), // Volontairement en doublon
    'officiel_accueil_releve'   => array( 'texte' => Lang::_("Relevé d'évaluations")    , 'class' => 'officiel_releve'       , 'href' => 'page=officiel&amp;section=accueil_releve'   ),
    'officiel_accueil_bulletin' => array( 'texte' => Lang::_("Bulletin scolaire")       , 'class' => 'officiel_bulletin'     , 'href' => 'page=officiel&amp;section=accueil_bulletin' ),
    'livret_accueil'            => array( 'texte' => Lang::_("Livret Scolaire")         , 'class' => 'marianne'              , 'href' => 'page=livret&amp;section=accueil'            ),
    'officiel_voir_archive'     => array( 'texte' => Lang::_("Archives consultables")   , 'class' => 'officiel_voir_archive' , 'href' => 'page=officiel_reglages_voir_archives'       ), // Pour élèves et parents c'est "officiel_voir_archive"
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// RAS !

?>