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
$TITRE = "Identité de l'installation"; // Pas de traduction car pas de choix de langue pour ce profil.

$exemple_denomination = (HEBERGEUR_INSTALLATION=='mono-structure') ? 'Collège de Trucville' : 'Rectorat du paradis' ;
$exemple_adresse_web  = (HEBERGEUR_INSTALLATION=='mono-structure') ? 'http://www.college-trucville.com' : 'http://www.ac-paradis.fr' ;
$uai_div_hide_avant   = (HEBERGEUR_INSTALLATION=='mono-structure') ? '' : '<div class="hide">' ;
$uai_div_hide_apres   = (HEBERGEUR_INSTALLATION=='mono-structure') ? '' : '</div>' ;
$cnil_check_oui       = intval(CNIL_NUMERO) ? ' checked' : '' ;
$cnil_check_non       = intval(CNIL_NUMERO) ? '' : ' checked' ;
$cnil_numero          = intval(CNIL_NUMERO) ? CNIL_NUMERO : '' ;
$cnil_dates_class     = intval(CNIL_NUMERO) ? 'show' : 'hide' ;

// Javascript
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__identite_installation">DOC : Identité de l'installation.</a></span></p>

<hr />

<form action="#" method="post" id="form_gestion"><fieldset>
  <h2>Caractéristiques de l'hébergement</h2>
  <label class="tab" for="f_denomination">Dénomination <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Exemple : <?php echo $exemple_denomination ?>" /> :</label><input id="f_denomination" name="f_denomination" size="50" type="text" value="<?php echo html(HEBERGEUR_DENOMINATION); ?>" /><br />
  <?php echo $uai_div_hide_avant ?>
  <label class="tab" for="f_uai">n° UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Ce champ est facultatif." /> :</label><input id="f_uai" name="f_uai" size="8" type="text" value="<?php echo html(HEBERGEUR_UAI); ?>" /><br />
  <?php echo $uai_div_hide_apres ?>
  <label class="tab" for="f_adresse_site">Adresse web <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Exemple : <?php echo $exemple_adresse_web ?>" /> :</label><input id="f_adresse_site" name="f_adresse_site" size="50" type="text" value="<?php echo html(HEBERGEUR_ADRESSE_SITE); ?>" /><br />
  <label class="tab" for="f_logo">Logo :</label><select id="f_logo" name="f_logo"></select><label id="ajax_logo"></label><br />
  <h2>Déclaration C.N.I.L. <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Voir la documentation." /></h2>
  <label class="tab">État :</label><label for="f_cnil_non"><input type="radio" id="f_cnil_non" name="f_cnil_etat" value="non"<?php echo $cnil_check_non ?> /> non renseignée</label>&nbsp;&nbsp;&nbsp;<label for="f_cnil_oui"><input type="radio" id="f_cnil_oui" name="f_cnil_etat" value="oui"<?php echo $cnil_check_oui ?> /> n°</label><input id="f_cnil_numero" name="f_cnil_numero" size="10" type="text" value="<?php echo $cnil_numero ?>" />
  <div id="cnil_dates" class="<?php echo $cnil_dates_class; ?>">
    <label class="tab" for="f_cnil_date_engagement">Date engagement <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Date à laquelle la demande a été effectuée auprès de la CNIL." /> :</label><input id="f_cnil_date_engagement" name="f_cnil_date_engagement" size="9" type="text" value="<?php echo html(CNIL_DATE_ENGAGEMENT); ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
    <label class="tab" for="f_cnil_date_recepisse">Date récépissé <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Date à laquelle la CNIL a retourné le récépissé de déclaration." /> :</label><input id="f_cnil_date_recepisse" name="f_cnil_date_recepisse" size="9" type="text" value="<?php echo html(CNIL_DATE_RECEPISSE); ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
  </div>
  <h2>Coordonnées du webmestre</h2>
  <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" size="50" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" value="<?php echo html(WEBMESTRE_NOM); ?>" /><br />
  <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" size="50" maxlength="<?php echo PRENOM_LONGUEUR_MAX ?>" value="<?php echo html(WEBMESTRE_PRENOM); ?>" /><br />
  <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" value="<?php echo html(WEBMESTRE_COURRIEL); ?>" />
  <p><span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="enregistrer" /><button id="f_submit" type="submit" class="parametre">Valider ces réglages.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<hr />

<h2>Logos disponibles</h2>
<form action="#" method="post" id="form_logo"><fieldset>
  <label class="tab" for="f_import_logo">Uploader image :</label><input type="hidden" name="f_action" value="upload_logo" /><input id="f_import_logo" type="file" name="userfile" /><button id="bouton_choisir_logo" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_logo">&nbsp;</label>
  <p><label id="ajax_listing"></label></p>
  <ul class="puce" id="listing_logos">
    <li></li>
  </ul>
</fieldset></form>

