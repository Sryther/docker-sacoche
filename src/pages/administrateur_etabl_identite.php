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
$TITRE = html(Lang::_("Identité de l'établissement"));

// Formulaire SELECT du mois de bascule de l'année scolaire
$options_mois = '<option value="1">calquée sur l\'année civile</option>'
              . '<option value="2">bascule au 1er février</option>'
              . '<option value="3">bascule au 1er mars</option>'
              . '<option value="4">bascule au 1er avril</option>'
              . '<option value="5">bascule au 1er mai</option>'
              . '<option value="6">bascule au 1er juin</option>'
              . '<option value="7">bascule au 1er juillet</option>'
              . '<option value="8">bascule au 1er août (par défaut)</option>'
              . '<option value="9">bascule au 1er septembre</option>'
              . '<option value="10">bascule au 1er octobre</option>'
              . '<option value="11">bascule au 1er novembre</option>'
              . '<option value="12">bascule au 1er décembre</option>';
$options_mois = str_replace( '"'.$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'].'"' , '"'.$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'].'" selected' , $options_mois );

// Charger $tab_langues_traduction
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_traduction.php');
// Formulaire SELECT du choix de la langue
$options_langue = '';
foreach($tab_langues_traduction as $tab_langue)
{
  if($tab_langue['statut']!=0)
  {
    $langue_pays_code = $tab_langue['langue']['code'].'_'.$tab_langue['pays']['code'];
    $langue_pays_nom  = $tab_langue['langue']['nom'].' - '.$tab_langue['pays']['nom'];
    $selected = ($langue_pays_code==$_SESSION['ETABLISSEMENT']['LANGUE']) ? ' selected' : '' ;
    $options_langue .= '<option value="'.$langue_pays_code.'"'.$selected.'>'.$langue_pays_nom.' ['.$langue_pays_code.']</option>';
  }
}

// Récupérer le logo, si présent.
$li_logo = '<li>Pas de logo actuellement enregistré.</li>';
$DB_ROW = DB_STRUCTURE_IMAGE::DB_recuperer_image( 0 /*user_id*/ , 'logo' );
if(!empty($DB_ROW))
{
  // Enregistrer temporairement le fichier sur le disque
  $fichier_nom = 'logo_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$DB_ROW['image_format'];
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , base64_decode($DB_ROW['image_contenu']) );
  // Générer la balise html pour afficher l'image
  list($width,$height) = Image::dimensions_affichage( $DB_ROW['image_largeur'] , $DB_ROW['image_hauteur'] , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
  $li_logo = '<li><img src="'.URL_DIR_EXPORT.$fichier_nom.'" alt="Logo établissement" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>';
}

// Info contact du webmestre si multi-structures
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  $contact_class_zone = 'show';
  DBextra::charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  $DB_ROW = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_contact_infos($_SESSION['BASE']);
  $contact_nom      = $DB_ROW['structure_contact_nom'];
  $contact_prenom   = $DB_ROW['structure_contact_prenom'];
  $contact_courriel = $DB_ROW['structure_contact_courriel'];
  $user_readonly = (CONTACT_MODIFICATION_USER!='non') ? '' : ' readonly' ;
  $mail_readonly = (CONTACT_MODIFICATION_MAIL!='non') ? '' : ' readonly' ;
  $user_title    = (CONTACT_MODIFICATION_USER=='oui') ? '' : ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable directement.<br />Utiliser le lien ci-dessous." />' ;
  $mail_title    = (CONTACT_MODIFICATION_MAIL=='oui') ? '' : ( (CONTACT_MODIFICATION_MAIL=='non') ? ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable directement.<br />Utiliser le lien ci-dessous." />' : ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur modifiable mais restreinte par le webmestre au domaine \''.CONTACT_MODIFICATION_MAIL.'\'." />' ) ;
  $contact_class_valider = ( $user_readonly && $mail_readonly ) ? 'hide' : 'show' ;
  $contact_class_mailto  = ( $user_readonly || $mail_readonly ) ? 'show' : 'hide' ;
  Layout::add( 'js_inline_before' , 'var CONTACT_MODIFICATION_USER = "'.CONTACT_MODIFICATION_USER.'";' );
  Layout::add( 'js_inline_before' , 'var CONTACT_MODIFICATION_MAIL = "'.CONTACT_MODIFICATION_MAIL.'";' );
}
else
{
  $contact_class_zone = $contact_class_valider = $contact_class_mailto = 'hide';
  $contact_nom = $contact_prenom = $contact_courriel = $user_title = $mail_title = $user_readonly = $mail_readonly = '';
}

// Formulaire SELECT pour la vérification de l'adresse IP
$tab_ip_variable = array(
  0 => 'Oui = Sécurité optimale (réseau d\'établissement normalement configuré)',
  1 => 'Non = Sécurité diminuée (réseau d\'établissement derrière un routeur mal configuré)',
);
$options_ip_variable = '';
foreach($tab_ip_variable as $option_value => $option_texte)
{
  $selected = ($option_value==$_SESSION['ETABLISSEMENT']['IP_VARIABLE']) ? ' selected' : '' ;
  $options_ip_variable .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

// Javascript
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<div id="div_instance">

  <div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_informations_structure">DOC : Identité de l'établissement</a></span></div>

  <form action="#" method="post" id="form_webmestre">
    <hr />
    <h2>Données saisies par le webmestre</h2>
    <p>
      <label class="tab" for="f_webmestre_uai">Code UAI (ex-RNE) :</label><input id="f_webmestre_uai" name="f_webmestre_uai" size="8" type="text" value="<?php echo html($_SESSION['WEBMESTRE_UAI']); ?>" disabled /><br />
      <label class="tab" for="f_webmestre_denomination">Dénomination :</label><input id="f_webmestre_denomination" name="f_webmestre_denomination" size="50" type="text" value="<?php echo html($_SESSION['WEBMESTRE_DENOMINATION']); ?>" disabled />
    </p>
    <ul class="puce"><li>En cas d'erreur, <?php echo HtmlMail::to(WEBMESTRE_COURRIEL,'Modifier données SACoche '.$_SESSION['BASE'].' ['.$_SESSION['WEBMESTRE_UAI'].']','contacter le webmestre'); ?> responsable des installations sur ce serveur.</li></ul>
  </form>

  <form action="#" method="post" id="form_sesamath">
    <hr />
    <h2>Identification de l'établissement dans la base Sésamath</h2>
    <ul class="puce"><li><a id="ouvrir_recherche" href="#"><img width="16" height="16" src="./_img/find.png" alt="Rechercher" /> Rechercher l'établissement dans la base Sésamath</a> afin de pouvoir échanger ensuite avec le serveur communautaire.</li></ul>
    <p>
      <label class="tab" for="f_sesamath_id2">Identifiant <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable manuellement.<br />Utiliser le lien ci-dessus." /> :</label><input id="f_sesamath_id2" name="f_sesamath_id2" size="8" type="text" value="<?php echo html($_SESSION['SESAMATH_ID']); ?>" readonly /><input id="f_sesamath_id" name="f_sesamath_id" size="5" type="text" value="<?php echo html($_SESSION['SESAMATH_ID']); ?>" class="invisible" /><br />
      <label class="tab" for="f_sesamath_uai2">Code UAI <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable manuellement.<br />Utiliser le lien ci-dessus." /> :</label><input id="f_sesamath_uai2" name="f_sesamath_uai2" size="8" type="text" value="<?php echo html($_SESSION['SESAMATH_UAI']); ?>" readonly /><input id="f_sesamath_uai" name="f_sesamath_uai" size="8" type="text" value="<?php echo html($_SESSION['SESAMATH_UAI']); ?>" class="invisible" /><br />
      <label class="tab" for="f_sesamath_type_nom2">Dénomination <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable manuellement.<br />Utiliser le lien ci-dessus." /> :</label><input id="f_sesamath_type_nom2" name="f_sesamath_type_nom2" size="50" type="text" value="<?php echo html($_SESSION['SESAMATH_TYPE_NOM']); ?>" readonly /><input id="f_sesamath_type_nom" name="f_sesamath_type_nom" size="50" type="text" value="<?php echo html($_SESSION['SESAMATH_TYPE_NOM']); ?>" class="invisible" /><br />
      <label class="tab" for="f_sesamath_key2">Clef de contrôle <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Valeur non modifiable manuellement.<br />Utiliser le lien ci-dessus." /> :</label><input id="f_sesamath_key2" name="f_sesamath_key2" size="50" type="text" value="<?php echo html($_SESSION['SESAMATH_KEY']); ?>" readonly /><input id="f_sesamath_key" name="f_sesamath_key" size="35" type="text" value="<?php echo html($_SESSION['SESAMATH_KEY']); ?>" class="invisible" /><br />
      <span class="tab"></span><button id="bouton_valider_sesamath" type="submit" class="parametre">Valider.</button><label id="ajax_msg_sesamath">&nbsp;</label>
    </p>
  </form>

  <form action="#" method="post" id="form_contact" class="<?php echo $contact_class_zone ?>">
    <hr />
    <h2>Contact référent de l'établissement</h2>
    <div class="astuce">Dans le cas d'un serveur <em>SACoche</em> de type multi-structures, il y a un contact référent <em>SACoche</em> par établissement :</div>
    <ul class="puce">
      <li>il réceptionne les identifiants du premier administrateur créé (à son nom) et les informations associées</li>
      <li>il est destinataire des lettres d'informations que peut envoyer le webmestre</li>
      <li>il reçoit une régénération de mot de passe administrateur effectuée par le webmestre</li>
      <li>il reçoit les courriels de gestion d'une éventuelle convention ENT-établissement sur le serveur Sésamath</li>
    </ul>
    <p>
      <label class="tab" for="f_contact_nom">Nom<?php echo $user_title ?> :</label><input id="f_contact_nom" name="f_contact_nom" size="50" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" type="text" value="<?php echo html($contact_nom); ?>"<?php echo $user_readonly ?> /><br />
      <label class="tab" for="f_contact_prenom">Prénom<?php echo $user_title ?> :</label><input id="f_contact_prenom" name="f_contact_prenom" size="50" maxlength="<?php echo PRENOM_LONGUEUR_MAX ?>" type="text" value="<?php echo html($contact_prenom); ?>"<?php echo $user_readonly ?> /><br />
      <label class="tab" for="f_contact_courriel">Courriel<?php echo $mail_title ?> :</label><input id="f_contact_courriel" name="f_contact_courriel" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="<?php echo html($contact_courriel); ?>"<?php echo $mail_readonly ?> /><br />
      <span class="<?php echo $contact_class_valider ?>"><span class="tab"></span><button id="bouton_valider_contact" type="submit" class="parametre">Valider.</button><label id="ajax_msg_contact">&nbsp;</label></span>
    </p>
    <ul class="puce <?php echo $contact_class_mailto ?>"><li>Si besoin, <?php echo HtmlMail::to(WEBMESTRE_COURRIEL,'Modifier contact SACoche n°'.$_SESSION['BASE'].' ['.$_SESSION['WEBMESTRE_UAI'].']','demander une modification au webmestre'); ?>.</li></ul>
  </form>

  <form action="#" method="post" id="form_etablissement">
    <hr />
    <h2>Coordonnées de l'établissement</h2>
    <p>
      <label class="tab" for="f_etablissement_denomination">Dénomination :</label><input id="f_etablissement_denomination" name="f_etablissement_denomination" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['DENOMINATION']); ?>" /><br />
      <label class="tab" for="f_etablissement_adresse1">Adresse ligne 1 :</label><input id="f_etablissement_adresse1" name="f_etablissement_adresse1" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE1']); ?>" /><br />
      <label class="tab" for="f_etablissement_adresse2">Adresse ligne 2 :</label><input id="f_etablissement_adresse2" name="f_etablissement_adresse2" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE2']); ?>" /><br />
      <label class="tab" for="f_etablissement_adresse3">Adresse ligne 3 :</label><input id="f_etablissement_adresse3" name="f_etablissement_adresse3" size="50" maxlength="50" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['ADRESSE3']); ?>" /><br />
      <label class="tab" for="f_etablissement_telephone">Téléphone :</label><input id="f_etablissement_telephone" name="f_etablissement_telephone" size="25" maxlength="25" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['TELEPHONE']); ?>" /><br />
      <label class="tab" for="f_etablissement_fax">Fax :</label><input id="f_etablissement_fax" name="f_etablissement_fax" size="25" maxlength="25" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['FAX']); ?>" /><br />
      <label class="tab" for="f_etablissement_courriel">Courriel :</label><input id="f_etablissement_courriel" name="f_etablissement_courriel" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['COURRIEL']); ?>" /><br />
      <label class="tab" for="f_etablissement_url">Site internet :</label><input id="f_etablissement_url" name="f_etablissement_url" size="50" maxlength="100" type="text" value="<?php echo html($_SESSION['ETABLISSEMENT']['URL']); ?>" /><br />
      <span class="tab"></span><button id="bouton_valider_etablissement" type="submit" class="parametre">Valider.</button><label id="ajax_msg_etablissement">&nbsp;</label>
    </p>
  </form>

  <form action="#" method="post" id="form_logo">
    <hr />
    <h2>Logo de l'établissement</h2>
    <p><label class="tab" for="f_logo">Uploader image :</label><input type="hidden" name="f_action" value="upload_logo" /><input id="f_logo" type="file" name="userfile" /><button id="bouton_choisir_logo" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_logo">&nbsp;</label></p>
  </form>
  <ul class="puce" id="puce_logo"><?php echo $li_logo ?></ul>

  <form action="#" method="post" id="form_annee_scolaire">
    <hr />
    <h2>Année scolaire</h2>
    <p>
      <label class="tab" for="f_mois_bascule_annee_scolaire">Fonctionnement :</label><select id="f_mois_bascule_annee_scolaire" name="f_mois_bascule_annee_scolaire"><?php echo $options_mois; ?></select><br />
      <label class="tab">Affichage obtenu :</label><span class="i">&laquo;&nbsp;Année scolaire <span id="span_simulation"></span>&nbsp;&raquo;</span><br />
      <span class="tab"></span><button id="bouton_valider_annee_scolaire" type="button" class="parametre">Valider.</button><label id="ajax_msg_annee_scolaire">&nbsp;</label>
    </p>
  </form>

  <form action="#" method="post" id="form_langue">
    <hr />
    <h2>Langue par défaut</h2>
    <p>
      <label class="tab" for="f_etablissement_langue">Langue :</label><select id="f_etablissement_langue" name="f_etablissement_langue"><?php echo $options_langue; ?></select><br />
      <span class="tab"></span><button id="bouton_valider_langue" type="button" class="parametre">Valider.</button><label id="ajax_msg_langue">&nbsp;</label>
    </p>
  </form>

  <form action="#" method="post" id="form_ip_variable">
    <hr />
    <h2>Vérification de l'adresse IP</h2>
    <p>
      <label class="tab" for="f_ip_variable">Vérification IP :</label><select id="f_ip_variable" name="f_ip_variable"><?php echo $options_ip_variable; ?></select><br />
      <span class="tab"></span><button id="bouton_valider_ip_variable" type="button" class="parametre">Valider.</button><label id="ajax_msg_ip_variable">&nbsp;</label>
    </p>
  </form>

  <hr />

</div>

<form action="#" method="post" id="form_communautaire" class="hide">
  <h2>Rechercher l'établissement dans la base Sésamath</h2>
  <p><button id="rechercher_annuler" type="button" class="annuler">Annuler la recherche.</button></p>
  <p id="f_recherche_mode">
    <label class="tab">Technique :</label><label for="f_mode_geo"><input type="radio" id="f_mode_geo" name="f_mode" value="geo" /> recherche sur critères géographiques</label>&nbsp;&nbsp;&nbsp;<label for="f_mode_uai"><input type="radio" id="f_mode_uai" name="f_mode" value="uai" /> recherche à partir du numéro UAI (ex-RNE)</label>
  </p>
  <fieldset id="f_recherche_geo" class="hide">
    <label class="tab" for="f_geo1">Etape 1/3 :</label><select id="f_geo1" name="f_geo1"></select><br />
    <label class="tab" for="f_geo2">Etape 2/3 :</label><select id="f_geo2" name="f_geo2"></select><br />
    <label class="tab" for="f_geo3">Etape 3/3 :</label><select id="f_geo3" name="f_geo3"></select><br />
  </fieldset>
  <fieldset id="f_recherche_uai" class="hide">
    <label class="tab" for="f_uai2">Code UAI (ex-RNE) :</label><input id="f_uai2" name="f_uai2" size="8" type="text" value="" /><br />
    <span class="tab"></span><button id="rechercher_uai" type="button" class="rechercher">Lancer la recherche.</button>
  </fieldset>
  <ul id="f_recherche_resultat" class="puce p hide">
    <li></li>
  </ul>
  <span class="tab"></span><label id="ajax_msg_communautaire">&nbsp;</label>
</form>
