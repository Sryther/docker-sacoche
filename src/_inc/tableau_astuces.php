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

// Tableau avec la liste des astuces "Le saviez-vous ?" affichées après authentification.

$astuce_menus_raccourcis_admin          = 'Vous pouvez <a href="index.php?page=administrateur_etabl_menus_raccourcis"><b>désactiver des menus et choisir des raccourcis favoris</b></a> de menu pour les pages d\'accueil des utilisateurs. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__menus_raccourcis">Documentation</a></span>';
$astuce_menus_raccourcis                = 'Vous pouvez <a href="index.php?page=compte_menus_raccourcis"><b>désactiver des menus et choisir des raccourcis favoris</b></a> de menu pour votre page d\'accueil. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__menus_raccourcis">Documentation</a></span>';
$astuce_livret_scolaire_export_2d       = '<span class="important"><em>SACoche</em> est <a href="http://eduscol.education.fr/cid108536/interface-editeurs-avec-les-applications-nationales.html" target="_blank" rel="noopener noreferrer">validée par le ministère pour l\'export LSU 1er + 2nd degré</a>.</span>';
$astuce_bascule_compte                  = 'Si vous avez plusieurs comptes, vous pouvez <a href="index.php?page=compte_switch"><b>basculer de l\'un à l\'autre</b></a> sans vous ré-identifier. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__comptes_multiples">Documentation</a></span>';
$astuce_partage_regroupements           = 'Vous pouvez <b>partager</b> avec des collègues vos <a href="index.php?page=compte_selection_items"><b>regroupements d\'items</b></a>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__gestion_regroupements_items#toggle_partage">Documentation</a></span>';
$astuce_officiel_saisie_mixte           = 'Les bilans officiels et les fiches brevet disposent d\'un nouveau statut <b>"saisie mixte"</b> (permettant l\'accès simultané en saisie aux appréciations intermédiaires et de synthèse). <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__bulletin_scolaire#toggle_etapes_processus">Documentation</a></span>';
$astuce_email_notifications             = sprintf_lang(html(Lang::_("Vous pouvez %1s|renseigner votre adresse e-mail|%2s et vous abonner pour %3s|recevoir des notifications|%2s.")),array('<a href="index.php?page=compte_email"><b>','</b></a>','<a href="index.php?page=consultation_notifications"><b>')).' <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__email_notifications#toggle_gestion_utilisateur">Documentation</a></span>';
$astuce_traductions                     = sprintf_lang(html(Lang::_("Le menu est disponible dans %1s|plusieurs langues|%2s.")),array('<a href="index.php?page=compte_langue"><b>','</b></a>')).' <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__traduction">Documentation</a></span>';
$astuce_bilans_officiels_appreciations  = 'Les <b>appréciations</b> intermédiaires et générales des <b>bilans officiels</b> peuvent être <b>préremplies</b> avec un modèle de contenu. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">Documentation</a></span>';
$astuce_bulletin_sans_moyennes          = 'Vous pouvez paramétrer <b>l\'absence de moyennes</b> pour <b>certaines matières</b> sur les <b>bulletins scolaires</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">Documentation</a></span>';
$astuce_autoevaluation_commentaire      = 'Lorsqu\'il s\'<b>auto-évalue</b>, l\'élève peut <b>commenter/justifier</b> ses choix. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_autoeval">Documentation</a></span>';
$astuce_demande_evaluation              = sprintf_lang(html(Lang::_("Lorsqu'il formule une %1s|demande d'évaluation|%2s, l'élève peut appuyer sa demande par %1s|un commentaire|%2s et/ou joindre %1s|un document|%2s.")),array('<b>','</b>')).' <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__demandes_evaluations#toggle_cote_eleves">Documentation</a></span>';
$astuce_devoir_commentaire              = 'Vous pouvez associer à une évaluation, pour chaque élève, <b>un commentaire texte ou audio personnalisé</b> (consultable par les familles). <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_commentaire_texte">Documentation</a></span>';
$astuce_evaluation_taux_remplissage     = '<em>SACoche</em> comporte un indicateur du <b>taux de saisie</b> par évaluation. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_indicateur_remplissage">Documentation</a></span>';
$astuce_previsualisation_bulletin       = '<em>SACoche</em> permet de <b>simuler l\'impression PDF d\'un bilan officiel</b> sans attendre que toutes les données soient saisies. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__simuler_impression">Documentation</a></span>';
$astuce_corriger_appreciation           = '<em>SACoche</em> permet d\'attribuer le droit de <b>corriger l\'appréciation d\'un collègue</b> sur un bulletin. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__erreurs_appreciations">Documentation</a></span>';
$astuce_gestion_profils                 = 'Vous pouvez gérer des profils supplémentaires : <b>documentaliste, CPE, médecin, conseiller d\'orientation</b> , etc. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_profils">Documentation</a></span>';
$astuce_duree_session                   = 'Vous pouvez paramétrer <b>la durée de vie d\'une session</b> (avant déconnexion) <b>jusqu\'a 2 heures</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_delai_deconnexion">Documentation</a></span>';
$astuce_photos                          = '<em>SACoche</em> permet de gérer les <b>photos des élèves</b> : outil trombinoscope, aide pour les bulletins. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__photos_eleves">Documentation</a></span>';
$astuce_bulletin_scolaire               = '<em>SACoche</em> permet d\'éditer des <b>bulletins scolaires</b> (avec appréciations, moyennes, coordonnées établissement et parents, signature numérique). <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__bulletin_scolaire">Documentation</a></span>';
$astuce_panneau_affichage               = 'Vous pouvez <b>programmer l\'affichage d\'un message</b> à destination d\'utilisateurs ciblés. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__messages_accueil">Documentation</a></span>';
$astuce_memorisation_selection_items    = 'Vous pouvez <b>mémoriser des regroupements d\'items</b> pour les années suivantes ou des bilans ciblés. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__gestion_regroupements_items">Documentation</a></span>';
$astuce_devoir_autoevaluation           = 'Vous pouvez permettre aux élèves de <b>s\'autoévaluer sur un devoir</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_autoeval">Documentation</a></span>';
$astuce_devoir_joindre_fichiers         = 'Vous pouvez joindre ou référencer <b>un sujet et un corrigé</b> à une évaluation. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_fichiers">Documentation</a></span>';
$astuce_devoir_partage                  = 'Vous pouvez <b>partager une évaluation avec des collègues</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_profs">Documentation</a></span>';
$astuce_devoir_ordonner_items           = 'Vous pouvez <b>choisir l\'ordre des items d\'une évaluation</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_ordonner">Documentation</a></span>';
$astuce_devoir_saisies_multiples        = 'Vous pouvez <b>saisir une note dans plusieurs cellules</b> simultanément. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_saisie_resultats#toggle_saisies_multiples">Documentation</a></span>';
$astuce_referentiel_lier_ressources     = 'Vous pouvez <b>associer aux items des ressources</b> pour un travail en autonomie des élèves. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__referentiel_lier_ressources">Documentation</a></span>';
$astuce_referentiel_uploader_ressources = 'Vous pouvez <b>mettre en ligne des ressources</b> pour ensuite les associer aux items. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__referentiel_uploader_ressources">Documentation</a></span>';
$astuce_socle_choisir_langue            = 'Vous pouvez <b>indiquer la langue étrangère</b> des élèves pour le socle commun. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__socle_choisir_langue">Documentation</a></span>';
$astuce_dates_periodes                  = 'Vous pouvez <a href="./index.php?page=consultation_groupe_periode"><b>consulter les dates des périodes</b></a> associées aux classes et aux groupes.';
$astuce_date_connexion                  = 'Vous pouvez <a href="./index.php?page=consultation_date_connexion"><b>consulter la date de dernière connexion</b></a> des élèves.';
$astuce_authentification_ent            = 'On peut se connecter à <em>SACoche</em> en utilisant <b>l\'authentification de plusieurs ENT</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_mode_identification">Documentation</a></span>';

// Ranger de la plus récente à la plus ancienne (la fréquence d'apparition étant liée à l'actualité de l'astuce)
$tab_astuces = array(
  'administrateur' => array(
    $astuce_menus_raccourcis_admin,
    $astuce_livret_scolaire_export_2d,
    $astuce_bascule_compte,
    $astuce_officiel_saisie_mixte,
    $astuce_email_notifications,
    $astuce_traductions,
    $astuce_bilans_officiels_appreciations,
    $astuce_bulletin_sans_moyennes,
    $astuce_previsualisation_bulletin,
    $astuce_corriger_appreciation,
    $astuce_gestion_profils,
    $astuce_duree_session,
    $astuce_photos,
    $astuce_bulletin_scolaire,
    $astuce_panneau_affichage,
    $astuce_socle_choisir_langue,
    $astuce_authentification_ent,
  ),
  'directeur' => array(
    $astuce_menus_raccourcis,
    $astuce_livret_scolaire_export_2d,
    $astuce_bascule_compte,
    $astuce_officiel_saisie_mixte,
    $astuce_email_notifications,
    $astuce_traductions,
    $astuce_bilans_officiels_appreciations,
    $astuce_bulletin_sans_moyennes,
    $astuce_previsualisation_bulletin,
    $astuce_corriger_appreciation,
    $astuce_photos,
    $astuce_bulletin_scolaire,
    $astuce_panneau_affichage,
    $astuce_socle_choisir_langue,
    $astuce_memorisation_selection_items,
    $astuce_dates_periodes,
    $astuce_date_connexion,
  ),
  'professeur' => array(
    $astuce_menus_raccourcis,
    $astuce_livret_scolaire_export_2d,
    $astuce_bascule_compte,
    $astuce_partage_regroupements,
    $astuce_email_notifications,
    $astuce_traductions,
    $astuce_autoevaluation_commentaire,
    $astuce_demande_evaluation,
    $astuce_devoir_commentaire,
    $astuce_evaluation_taux_remplissage,
    $astuce_photos,
    $astuce_bulletin_scolaire,
    $astuce_panneau_affichage,
    $astuce_devoir_autoevaluation,
    $astuce_devoir_joindre_fichiers,
    $astuce_devoir_saisies_multiples,
    $astuce_memorisation_selection_items,
    $astuce_referentiel_lier_ressources,
    $astuce_referentiel_uploader_ressources,
    $astuce_devoir_partage,
    $astuce_dates_periodes,
    $astuce_date_connexion,
    $astuce_devoir_ordonner_items,
  ),
  'parent' => array(
    $astuce_menus_raccourcis,
    $astuce_email_notifications,
    $astuce_traductions,
    $astuce_demande_evaluation,
  ),
  'eleve' => array(
    $astuce_menus_raccourcis,
    $astuce_email_notifications,
    $astuce_traductions,
    $astuce_demande_evaluation,
  ),
);

?>