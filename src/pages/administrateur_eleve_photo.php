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
$TITRE = html(Lang::_("Photos des élèves"));

// Fabrication des éléments select du formulaire
$select_groupe = HtmlForm::afficher_select( DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( FALSE /*sans*/ , FALSE /*tout*/ ) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__photos_eleves">DOC : Photos des élèves</a></span></p>
<div class="danger">Respectez les conditions légales d'utilisation (droit à l'image précisé dans la documentation ci-dessus).</div>

<hr />

<h2>Ajout multiple</h2>

<form action="#" method="post" id="form_photos"><fieldset>
  <p class="astuce">
    Taille maximale du fichier : <b><?php echo InfoServeur::minimum_limitations_upload(FALSE /*avec_explication*/) ?></b> <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Le webmestre du serveur a accès au détail de la configuration PHP expliquant cette limite." />.
  </p>
  <label class="tab" for="f_masque">Forme noms fichiers :</label><input id="f_masque" name="f_masque" size="50" maxlength="50" type="text" value="" /><br />
  <label class="tab" for="f_photos">Upload fichier <em>zip</em> :</label><input type="hidden" name="f_action" value="envoyer_zip" /><input id="f_photos" type="file" name="userfile" /><button id="bouton_photos" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_photos">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Gestion individuelle</h2>

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?> <label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<p id="liste_eleves">
</p>

<form action="#" method="post" id="form_photo" class="hide"><fieldset>
  <input type="hidden" id="f_user_id" name="f_user_id" value="" /><input type="hidden" name="f_action" value="envoyer_photo" /><input id="f_photo" type="file" name="userfile" /><button id="bouton_photo" type="button" class="fichier_import">Parcourir...</button>
</fieldset></form>
