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

// //////////////////////////////////////////////////
// Tableau avec les différents droits d'accès aux pages suivant le profil
// Il faut aussi indiquer le format page_section pour les appels ajax
// //////////////////////////////////////////////////

$tab_droits_profil_public                = array( 'public'=>1 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_tous                  = array( 'public'=>1 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 , 'developpeur'=>1 , 'partenaire'=>1 );
$tab_droits_profil_identifie             = array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 , 'developpeur'=>1 , 'partenaire'=>1 );
$tab_droits_profil_etablissement         = array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_eleve_parent_prof_dir = array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_prof_dir_admin        = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_prof_dir              = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_dir_admin             = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>1 , 'inspecteur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_eleve                 = array( 'public'=>0 , 'eleve'=>1 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_parent                = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>1 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_professeur            = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_developpeur           = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>1 , 'partenaire'=>0 );
$tab_droits_profil_directeur             = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>1 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_administrateur        = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_webmestre_developpeur = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 , 'developpeur'=>1 , 'partenaire'=>0 );
$tab_droits_profil_webmestre             = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 , 'developpeur'=>0 , 'partenaire'=>0 );
$tab_droits_profil_partenaire            = array( 'public'=>0 , 'eleve'=>0 , 'parent'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>1 );

$tab_droits_par_page = array
(
  // Tous profils
  'fermer_session'                          => $tab_droits_profil_tous, // Au cas où plusieurs onglets sont ouverts dont l'un a déjà déconnecté
  'webservices'                             => $tab_droits_profil_tous,
  'calque_date_calendrier'                  => $tab_droits_profil_tous, // Aussi utilisé par un espace de gestion Sésamath extérieur
  'public_test_variables'                   => $tab_droits_profil_tous, // Pour permettre un appel à cette page sans que cela déconnecte
  // Profils identifiés
  'conserver_session_active'                => $tab_droits_profil_identifie,
  'compte_accueil'                          => $tab_droits_profil_identifie,
  'compte_password'                         => $tab_droits_profil_identifie,
  'maj_base_complementaire'                 => $tab_droits_profil_identifie,
  // Profils établissement
  'compte_email'                            => $tab_droits_profil_etablissement,
  'compte_langue'                           => $tab_droits_profil_etablissement,
  'compte_switch'                           => $tab_droits_profil_etablissement,
  'consultation_notifications'              => $tab_droits_profil_etablissement,
  // Profil public
  'public_accueil'                          => $tab_droits_profil_public,
  'public_contact_admin'                    => $tab_droits_profil_public,
  'public_identifiants_perdus'              => $tab_droits_profil_public,
  'public_installation'                     => $tab_droits_profil_public,
  'public_sso_logout'                       => $tab_droits_profil_public,
  'public_nouveau_mdp'                      => $tab_droits_profil_public,
  // Profil élève
  'evaluation_demande_eleve'                => $tab_droits_profil_eleve,
  'evaluation_demande_eleve_ajout'          => $tab_droits_profil_eleve,
  // Profil professeur
  '_maj_select_eval'                        => $tab_droits_profil_professeur,
  '_maj_select_items'                       => $tab_droits_profil_professeur,
  '_maj_select_matieres_prof'               => $tab_droits_profil_professeur,
  'evaluation_demande_professeur'           => $tab_droits_profil_professeur,
  'evaluation_gestion'                      => $tab_droits_profil_professeur,
  'evaluation_ponctuelle'                   => $tab_droits_profil_professeur,
  'professeur_groupe_besoin'                => $tab_droits_profil_professeur,
  'professeur_module_externe'               => $tab_droits_profil_professeur,
  'professeur_referentiel'                  => $tab_droits_profil_professeur,
  'professeur_referentiel_gestion'          => $tab_droits_profil_professeur,
  'professeur_referentiel_edition'          => $tab_droits_profil_professeur,
  'professeur_referentiel_format_synthese'  => $tab_droits_profil_professeur,
  'professeur_referentiel_ressources'       => $tab_droits_profil_professeur,
  // Profil directeur
  '_maj_select_matieres'                    => $tab_droits_profil_directeur,
  'consultation_nombre_saisies'             => $tab_droits_profil_directeur,
  'consultation_stats_globales'             => $tab_droits_profil_directeur,
  // Profil administrateur
  '_maj_select_directeurs'                  => $tab_droits_profil_administrateur,
  '_maj_select_parents'                     => $tab_droits_profil_administrateur,
  '_maj_select_professeurs'                 => $tab_droits_profil_administrateur,
  '_maj_select_professeurs_directeurs'      => $tab_droits_profil_administrateur,
  'administrateur_administrateur'           => $tab_droits_profil_administrateur,
  'administrateur_blocage'                  => $tab_droits_profil_administrateur,
  'administrateur_classe'                   => $tab_droits_profil_administrateur,
  'administrateur_classe_gestion'           => $tab_droits_profil_administrateur,
  'administrateur_dump'                     => $tab_droits_profil_administrateur,
  'administrateur_eleve'                    => $tab_droits_profil_administrateur,
  'administrateur_eleve_classe'             => $tab_droits_profil_administrateur,
  'administrateur_eleve_fusion'             => $tab_droits_profil_administrateur,
  'administrateur_eleve_gestion'            => $tab_droits_profil_administrateur,
  'administrateur_eleve_groupe'             => $tab_droits_profil_administrateur,
  'administrateur_eleve_langue'             => $tab_droits_profil_administrateur,
  'administrateur_eleve_photo'              => $tab_droits_profil_administrateur,
  'administrateur_etabl_algorithme'         => $tab_droits_profil_administrateur,
  'administrateur_etabl_autorisations'      => $tab_droits_profil_administrateur,
  'administrateur_etabl_connexion'          => $tab_droits_profil_administrateur,
  'administrateur_etabl_deconnexion'        => $tab_droits_profil_administrateur,
  'administrateur_etabl_duree_inactivite'   => $tab_droits_profil_administrateur,
  'administrateur_etabl_identite'           => $tab_droits_profil_administrateur,
  'administrateur_etabl_login'              => $tab_droits_profil_administrateur,
  'administrateur_etabl_matiere'            => $tab_droits_profil_administrateur,
  'administrateur_etabl_menus_raccourcis'   => $tab_droits_profil_administrateur,
  'administrateur_etabl_notes_acquis'       => $tab_droits_profil_administrateur,
  'administrateur_etabl_niveau'             => $tab_droits_profil_administrateur,
  'administrateur_etabl_profils'            => $tab_droits_profil_administrateur,
  'administrateur_fichier_identifiant'      => $tab_droits_profil_administrateur,
  'administrateur_fichier_sacoche'          => $tab_droits_profil_administrateur,
  'administrateur_fichier_user'             => $tab_droits_profil_administrateur,
  'administrateur_groupe'                   => $tab_droits_profil_administrateur,
  'administrateur_groupe_gestion'           => $tab_droits_profil_administrateur,
  'administrateur_log_actions'              => $tab_droits_profil_administrateur,
  'administrateur_nettoyage'                => $tab_droits_profil_administrateur,
  'administrateur_parent'                   => $tab_droits_profil_administrateur,
  'administrateur_parent_gestion'           => $tab_droits_profil_administrateur,
  'administrateur_parent_adresse'           => $tab_droits_profil_administrateur,
  'administrateur_parent_eleve'             => $tab_droits_profil_administrateur,
  'administrateur_periode'                  => $tab_droits_profil_administrateur,
  'administrateur_periode_classe_groupe'    => $tab_droits_profil_administrateur,
  'administrateur_periode_gestion'          => $tab_droits_profil_administrateur,
  'administrateur_professeur'               => $tab_droits_profil_administrateur,
  'administrateur_professeur_classe'        => $tab_droits_profil_administrateur,
  'administrateur_professeur_coordonnateur' => $tab_droits_profil_administrateur,
  'administrateur_professeur_fusion'        => $tab_droits_profil_administrateur,
  'administrateur_professeur_gestion'       => $tab_droits_profil_administrateur,
  'administrateur_professeur_groupe'        => $tab_droits_profil_administrateur,
  'administrateur_professeur_matiere'       => $tab_droits_profil_administrateur,
  'administrateur_professeur_principal'     => $tab_droits_profil_administrateur,
  'administrateur_resilier'                 => $tab_droits_profil_administrateur,
  'administrateur_user_recherche'           => $tab_droits_profil_administrateur,
  // Profil webmestre | développeur
  'webmestre_configuration_filesystem'      => $tab_droits_profil_webmestre_developpeur,
  'webmestre_configuration_proxy'           => $tab_droits_profil_webmestre_developpeur,
  'webmestre_database_test'                 => $tab_droits_profil_webmestre_developpeur,
  'webmestre_debug'                         => $tab_droits_profil_webmestre_developpeur,
  'webmestre_info_serveur'                  => $tab_droits_profil_webmestre_developpeur,
  'webmestre_log_actions'                   => $tab_droits_profil_webmestre_developpeur,
  'webmestre_mail_bounces'                  => $tab_droits_profil_webmestre_developpeur,
  'webmestre_maintenance'                   => $tab_droits_profil_webmestre_developpeur,
  'webmestre_statistiques'                  => $tab_droits_profil_webmestre_developpeur,
  'webmestre_structure_bdd_repair'          => $tab_droits_profil_webmestre_developpeur,
  // Profil développeur
  'developpeur_superviseur'                 => $tab_droits_profil_developpeur,
  // Profil webmestre
  'webmestre_contact_modification'          => $tab_droits_profil_webmestre,
  'webmestre_envoi_notifications'           => $tab_droits_profil_webmestre,
  'webmestre_fichiers_deposes'              => $tab_droits_profil_webmestre,
  'webmestre_geographie'                    => $tab_droits_profil_webmestre,
  'webmestre_identite_installation'         => $tab_droits_profil_webmestre,
  'webmestre_mdp_admin'                     => $tab_droits_profil_webmestre,
  'webmestre_newsletter'                    => $tab_droits_profil_webmestre,
  'webmestre_partenariats'                  => $tab_droits_profil_webmestre,
  'webmestre_resilier'                      => $tab_droits_profil_webmestre,
  'webmestre_structure_ajout_csv'           => $tab_droits_profil_webmestre,
  'webmestre_structure_gestion'             => $tab_droits_profil_webmestre,
  'webmestre_structure_transfert'           => $tab_droits_profil_webmestre,
  // Profil partenaire
  'partenaire_parametrages' => $tab_droits_profil_partenaire,
  'partenaire_statistiques' => $tab_droits_profil_partenaire,
  // Profil professeur | directeur | administrateur
  'calque_voir_photo'                       => $tab_droits_profil_prof_dir_admin,
  '_maj_select_livret'                      => $tab_droits_profil_prof_dir_admin,
  '_maj_select_officiel_periode'            => $tab_droits_profil_prof_dir_admin,
  '_maj_select_structure_origine'           => $tab_droits_profil_prof_dir_admin,
  'compte_message'                          => $tab_droits_profil_prof_dir_admin,
  'consultation_date_connexion'             => $tab_droits_profil_prof_dir_admin,
  'livret'                                  => $tab_droits_profil_prof_dir_admin,
  'livret_elements'                         => $tab_droits_profil_prof_dir_admin,
  'livret_epi'                              => $tab_droits_profil_prof_dir_admin,
  'livret_ap'                               => $tab_droits_profil_prof_dir_admin,
  'livret_parcours'                         => $tab_droits_profil_prof_dir_admin,
  'livret_modaccomp'                        => $tab_droits_profil_prof_dir_admin,
  'livret_enscompl'                         => $tab_droits_profil_prof_dir_admin,
  'livret_edition'                          => $tab_droits_profil_prof_dir_admin,
  'officiel'                                => $tab_droits_profil_prof_dir_admin,
  'officiel_accueil'                        => $tab_droits_profil_prof_dir_admin,
  'officiel_assiduite'                      => $tab_droits_profil_prof_dir_admin,
  'officiel_reglages_format_synthese'       => $tab_droits_profil_prof_dir_admin,
  'officiel_reglages_voir_archives'         => $tab_droits_profil_prof_dir_admin,
  'export_fichier'                          => $tab_droits_profil_prof_dir_admin,
  // Profil professeur | directeur
  '_load_arborescence'                      => $tab_droits_profil_prof_dir,
  '_maj_select_matieres_famille'            => $tab_droits_profil_prof_dir,
  '_maj_select_niveaux_famille'             => $tab_droits_profil_prof_dir,
  'compte_selection_items'                  => $tab_droits_profil_prof_dir,
  'consultation_referentiel_externe'        => $tab_droits_profil_prof_dir,
  'releve_recherche'                        => $tab_droits_profil_prof_dir,
  'consultation_trombinoscope'              => $tab_droits_profil_prof_dir,
  // Profil directeur | administrateur
  'livret_accueil'                          => $tab_droits_profil_dir_admin,
  'livret_liaisons'                         => $tab_droits_profil_dir_admin,
  'livret_seuils'                           => $tab_droits_profil_dir_admin,
  'livret_classes'                          => $tab_droits_profil_dir_admin,
  'livret_export'                           => $tab_droits_profil_dir_admin,
  'officiel_reglages_ordre_matieres'        => $tab_droits_profil_dir_admin,
  'officiel_reglages_mise_en_page'          => $tab_droits_profil_dir_admin,
  'officiel_reglages_configuration'         => $tab_droits_profil_dir_admin,
  // Profil élève | parent | professeur | directeur
  '_maj_select_profs_groupe'                => $tab_droits_profil_eleve_parent_prof_dir,
  '_maj_select_niveaux'                     => $tab_droits_profil_eleve_parent_prof_dir,
  'compte_cnil'                             => $tab_droits_profil_eleve_parent_prof_dir,
  'compte_daltonisme'                       => $tab_droits_profil_eleve_parent_prof_dir,
  'compte_menus_raccourcis'                 => $tab_droits_profil_eleve_parent_prof_dir,
  'consultation_algorithme'                 => $tab_droits_profil_eleve_parent_prof_dir,
  'consultation_codes_couleurs'             => $tab_droits_profil_eleve_parent_prof_dir,
  'consultation_groupe_periode'             => $tab_droits_profil_eleve_parent_prof_dir,
  'evaluation'                              => $tab_droits_profil_eleve_parent_prof_dir,
  'evaluation_voir'                         => $tab_droits_profil_eleve_parent_prof_dir,
  'releve'                                  => $tab_droits_profil_eleve_parent_prof_dir,
  'releve_bilan_chronologique'              => $tab_droits_profil_eleve_parent_prof_dir,
  'releve_grille_referentiel'               => $tab_droits_profil_eleve_parent_prof_dir,
  'releve_items'                            => $tab_droits_profil_eleve_parent_prof_dir,
  'releve_socle2016'                        => $tab_droits_profil_eleve_parent_prof_dir,
  'releve_synthese'                         => $tab_droits_profil_eleve_parent_prof_dir,
  // Profils particuliers à gérer au cas par cas
  '_maj_select_eleves'                      => array( 'public'=>0 , 'eleve'=>0 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 ),
  'acces_archive'                           => array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 ),
  'officiel_voir_archive'                   => array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>0 , 'directeur'=>0 , 'inspecteur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 ),
  'consultation_referentiel_interne'        => array( 'public'=>0 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 ),
  // Attention à ce dernier cas ! Il faut inclure "public" car un échange est encore effectué avec ce fichier après enregistrement des données de session...
  'public_sso_login'                        => array( 'public'=>1 , 'eleve'=>1 , 'parent'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'inspecteur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 , 'developpeur'=>0 , 'partenaire'=>0 ),
);

?>