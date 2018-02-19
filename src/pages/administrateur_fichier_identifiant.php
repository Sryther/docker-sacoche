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
$TITRE = html(Lang::_("Importer / Imposer des identifiants"));
?>

<?php
require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// Fabrication des éléments select du formulaire
$select_groupe = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
?>

<ul class="puce">
  <li><span class="astuce">Pour un traitement individuel on peut utiliser les pages de gestion [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Professeurs / Directeurs / Personnels</a>].</span></li>
  <li><span class="astuce">Les administrateurs ne se gèrent qu'individuellement depuis la page [<a href="./index.php?page=administrateur_administrateur">Gérer les administrateurs</a>].</span></li>
</ul>

<hr />

<form action="#" method="post" id="form_select">

  <fieldset>
    <input type="hidden" id="f_action" name="f_action" value="" /><input id="f_import" type="file" name="userfile" />
    <label class="tab" for="f_choix_principal">Objectif :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value="">&nbsp;</option>
      <optgroup label="Identifiants pour une connexion à SACoche sans ENT">
        <option value="new_loginmdp">Générer de nouveaux identifiants SACoche.</option>
        <option value="import_loginmdp">Importer / Imposer des identifiants SACoche.</option>
      </optgroup>
      <?php /* Remarque : on ne restreint pas l'affichage ni ne désactive les options a priori, car l'on peut vouloir configurer la base utilisateurs avant de sélectionner la connexion sur l'ENT */ ?>
      <optgroup label="Rapprochement automatisé des comptes avec un ENT">
        <option value="import_id_lcs">Environnement LCS.</option>
        <option value="import_id_argos">ENT ARGOS (académie de Bordeaux).</option>
        <option disabled value="import_id_entlibre_essonne">ENT Libre des collèges de l'Essonne.</option><?php /* Webservices non finalisé ??? */ ?>
        <option disabled value="import_id_entlibre_picardie">ENT Libre LÉO des lycées de Picardie.</option><?php /* Webservices non finalisé ??? */ ?>
        <?php if( IS_HEBERGEMENT_SESAMATH && ($_SESSION['BASE']>=CONVENTION_ENT_ID_ETABL_MAXI) ): ?>
          <option value="import_id_entlibre_test">ENT Libre Plateforme de test.</option>
        <?php endif; ?>
        <option disabled value="import_id_laclasse">ENT Laclasse.com (département du Rhône).</option>
      </optgroup>
      <optgroup label="Rapprochement des comptes avec un ENT en important un fichier">
        <option value="import_id_ent_<?php echo $_SESSION['CONNEXION_MODE'] ?>">Importer / Imposer les identifiants d'un ENT.</option>
      </optgroup>
      <optgroup label="Rapprochement avec GEPI (hors ENT)">
        <option value="import_id_gepi">Récupérer les identifiants de Gepi.</option>
      </optgroup>
    </select><br />
  </fieldset>

  <fieldset id="fieldset_new_loginmdp" class="hide">
    <hr />
    <p class="astuce">Les noms d'utilisateurs seront générés selon <a href="./index.php?page=administrateur_etabl_login">le format choisi</a>.</p>
    <table>
      <tr>
        <td class="nu" style="width:30em;text-align:left">
          <div><label class="tab" for="f_profil">Profil :</label><select id="f_profil" name="f_profil">
            <option value="">&nbsp;</option>
            <option value="eleves">élèves</option>
            <option value="parents">responsables légaux</option>
            <option value="professeurs">professeurs</option>
            <option value="directeurs">directeurs</option>
          </select></div>
          <div><label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?></div>
          <div id="div_users" class="hide"><label class="tab" for="f_user">Utilisateur(s) :</label><span id="f_user" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></div>
        </td>
        <td id="td_bouton" class="nu" style="width:25em">
          <p><button id="generer_login" type="button" class="mdp_groupe">Générer de nouveaux noms d'utilisateurs.</button></p>
          <p><button id="generer_mdp" type="button" class="mdp_groupe">Générer de nouveaux mots de passe.</button></p>
          <p id="eleve_birth"><button id="forcer_mdp_birth" type="button" class="mdp_groupe">Prendre la date de naissance comme mot de passe.</button></p>
        </td>
      </tr>
    </table>
  </fieldset>

  <fieldset id="fieldset_import_loginmdp" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__force_login_mdp_tableur">DOC : Imposer identifiants SACoche avec un tableur</a></span></p>
    <p>Vous pouvez <button id="user_export" type="button" class="fichier_export">récupérer un fichier csv avec les noms / prénoms / logins actuels</button> (le mot de passe, crypté, ne peut être restitué).</p>
    <p>Modifiez les identifiants souhaités, puis indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu que vous souhaitez importer.</p>
    <p><label class="tab" for="import_loginmdp">Envoyer le fichier :</label><button id="import_loginmdp" type="button" class="fichier_import">Parcourir...</button></p>
  </fieldset>

  <fieldset id="fieldset_import_id_lcs" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__lcs">DOC : Intégration de SACoche dans un LCS</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée au paquet LCS-SACoche, est sans objet sur le serveur Sésamath !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_LCS))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_LCS).'</b>&nbsp;&raquo; (uniquement présent dans le paquet LCS-SACoche) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_lcs_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant LCS</button> comme identifiant de l\'ENT pour tous les utilisateurs.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_argos" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__argos">DOC : Intégration de SACoche dans Argos</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée à l\'installation académique Argos, est sans objet sur le serveur Sésamath !</div>'.NL;
    }
    else if(!in_array( substr($_SESSION['WEBMESTRE_UAI'],0,3) , array('024','033','040','047','064') ))
    {
      echo'<div class="danger">Cette fonctionnalité est réservée aux établissements de l\'académie de Bordeaux (et votre numéro UAI n\'y correspond pas) !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_ARGOS))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ARGOS).'</b>&nbsp;&raquo; (uniquement présent sur l\'installation académique Argos) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_argos_profs_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Argos</button> pour tous les professeurs &amp; directeurs.<br />'.NL;
      echo'<button name="dupliquer" id="COPY_id_argos_eleves_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Argos</button> pour tous les élèves.<br />'.NL;
      echo'<button name="dupliquer" id="COPY_id_argos_parents_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Argos</button> pour tous les responsables légaux.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_entlibre_essonne" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__entlibre_essonne">DOC : Intégration de SACoche dans l'ENT Libre des collèges de l'Essonne</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée à l\'installation de l\'académie de Versailles, est sans objet sur le serveur Sésamath !</div>'.NL;
    }
    else if(substr($_SESSION['WEBMESTRE_UAI'],0,3)!='091')
    {
      echo'<div class="danger">Cette fonctionnalité est réservée aux établissements du département de l\'Essonne (et votre numéro UAI n\'y correspond pas) !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_ENTLIBRE_ESSONNE))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ENTLIBRE_ESSONNE).'</b>&nbsp;&raquo; (uniquement présent sur l\'installation de l\'académie de Versailles) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_entlibre_essonne_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Libre des collèges de l\'Essonne</button> pour tous les utilisateurs.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_entlibre_picardie" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__entlibre_picardie">DOC : Intégration de SACoche dans l'ENT Libre LÉO des lycées de Picardie</a></span></p>
    <?php
    if(!IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, nécessitant un fichier d\'authentification, est sans objet sur un autre serveur que Sésamath (en l\'absence d\'hébergement par une collectivité) !</div>'.NL;
    }
    else if(!in_array( substr($_SESSION['WEBMESTRE_UAI'],0,3) , array('002','060','080') ))
    {
      echo'<div class="danger">Cette fonctionnalité est réservée aux lycées de Picardie (et votre numéro UAI n\'y correspond pas) !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_ENTLIBRE_PICARDIE))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ENTLIBRE_PICARDIE).'</b>&nbsp;&raquo; (uniquement présent sur l\'installation de Sésamath) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_entlibre_picardie_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Libre LÉO des lycées de Picardie</button> pour tous les utilisateurs.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_entlibre_test" class="hide">
    <hr />
    <?php
    if(!IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, vérifiant l\'origine de l\'appel, est sans objet sur un autre serveur que Sésamath !</div>'.NL;
    }
    else if($_SESSION['BASE']<CONVENTION_ENT_ID_ETABL_MAXI)
    {
      echo'<div class="danger">Cette fonctionnalité est réservée aux établissements de test (et celui-ci a un identifiant qui n\'y correspond pas) !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_ENTLIBRE_TEST))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ENTLIBRE_TEST).'</b>&nbsp;&raquo; (uniquement présent sur l\'installation de Sésamath) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_entlibre_test_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT Libre de la structure 0805432C</button> pour tous les utilisateurs.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_laclasse" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__laclasse">DOC : Intégration de SACoche dans Laclasse.com</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée à l\'installation départementale Laclasse.com, est sans objet sur le serveur Sésamath !</div>'.NL;
    }
    else if(substr($_SESSION['WEBMESTRE_UAI'],0,3)!='069')
    {
      echo'<div class="danger">Cette fonctionnalité est réservée aux établissements du département du Rhône (et votre numéro UAI n\'y correspond pas) !</div>'.NL;
    }
    else if(!is_file(CHEMIN_FICHIER_WS_LACLASSE))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_LACLASSE).'</b>&nbsp;&raquo; (uniquement présent sur l\'installation départementale Laclasse.com) n\'a pas été détecté !</div>'.NL;
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_laclasse_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant ENT de Laclasse.com</button> pour tous les utilisateurs.'.NL;
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_ent_normal" class="hide">
    <hr />
    <div class="astuce">Vous devez commencer par sélectionner votre ENT depuis la page "<a href="./index.php?page=administrateur_etabl_connexion">Mode d'identification / Connecteur ENT</a>".</div>
  </fieldset>

  <fieldset id="fieldset_import_id_ent_shibboleth" class="hide">
    <hr />
    <div class="astuce">Sans objet : utilisez le rapprochement automatisé (choix précédent du formulaire).</div>
  </fieldset>

  <fieldset id="fieldset_import_id_ent_cas" class="hide">
    <hr />
    <h3>En important un fichier</h3>
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__<?php echo $_SESSION['CONNEXION_NOM'] ?>">DOC : <?php echo $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_DEPARTEMENT'].'|'.$_SESSION['CONNEXION_NOM']]['txt'] ?>.</a></span></li>
      <li>Importer le fichier <b>csv</b> provenant de l'ENT : <button id="import_ent" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
    <h3>En dupliquant un autre champ</h3>
    <ul class="puce">
      <li><button name="dupliquer" id="COPY_id_gepi_TO_id_ent" type="button" class="mdp_groupe">Dupliquer l'identifiant de Gepi enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
      <li><button name="dupliquer" id="COPY_login_TO_id_ent" type="button" class="mdp_groupe">Dupliquer le login de SACoche enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_import_id_gepi" class="hide">
    <hr />
    <h3>En important un fichier</h3>
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_identifiant_gepi_sacoche">DOC : Import des identifiants de Gepi dans SACoche.</a></span></li>
      <li>Importer le fichier <b>base_professeur_gepi.csv</b> issu de Gepi : <button id="import_gepi_profs" type="button" class="fichier_import">Parcourir...</button></li>
      <li>Importer le fichier <b>base_responsable_gepi.csv</b> issu de Gepi : <button id="import_gepi_parents" type="button" class="fichier_import">Parcourir...</button></li>
      <li>Importer le fichier <b>base_eleve_gepi.csv</b> issu de Gepi : <button id="import_gepi_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
    <h3>En dupliquant un autre champ</h3>
    <ul class="puce">
      <li><button name="dupliquer" id="COPY_id_ent_TO_id_gepi" type="button" class="mdp_groupe">Dupliquer l'identifiant de l'ENT enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
      <li><button name="dupliquer" id="COPY_login_TO_id_gepi" type="button" class="mdp_groupe">Dupliquer le login de SACoche enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
    </ul>
  </fieldset>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<div id="ajax_retour" class="p"></div>