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
$TITRE = "Adresse de rebond &amp; Test mail"; // Pas de traduction car pas de choix de langue pour ce profil.

// Javascript
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<p>
  Les courriels envoyés doivent comporter des en-têtes adaptées.<br />
  Ainsi, un courriel envoyé par un automate ne devrait pas faire croire qu'il l'a été par une personne physique.
</p>
<p>
  De plus, les notifications internes à un établissement ne sont pas evoyées par le webmestre.<br />
  Elles ne devraient donc pas comporter son courriel comme adresse d'expéditeur ou de réponse.
</p>
<p>
  Pour ces raisons, il est recommander d'indiquer ci-dessous <a href="http://fr.wikipedia.org/wiki/Bounce_address" target="_blank" rel="noopener noreferrer">une adresse de rebond</a>.<br />
  Si vous ignorez celle de votre serveur, vous pouvez faire envoyer un courriel puis regarder le code source du message et y chercher l'en-tête <em>Return-Path</em>.
</p>
<p>
  L'adresse de rebond étant parfois utilisée par <em>SACoche</em> comme adresse d'expéditeur, elle doit correspondre à un domaine valide.<br />
  En effet, les DNS du domaine de l'adresse d'expéditeur sont susceptibles d'être interrogés par le serveur recevant le mail pour vérifier son authenticité.
</p>

<?php if($_SESSION['USER_PROFIL_TYPE']=='webmestre'): ?>
<p class="astuce">
  Consultez aussi le menu <a href="./index.php?page=webmestre_envoi_notifications">[Paramétrages établissement] [Courriels de notification]</a>.
</p>
<?php endif; ?>

<hr />

<form action="#" method="post" id="form_gestion"><fieldset>
  <h2>Adresse mail de rebond</h2>
  <p><label class="tab" for="f_bounce">Courriel :</label><input id="f_bounce" name="f_bounce" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="<?php echo html(HEBERGEUR_MAILBOX_BOUNCE); ?>" /></p>
  <p><span class="tab"></span><input name="f_action" type="hidden" value="EnregistrerBounce" /><button id="f_submit" type="submit" class="parametre">Valider ce réglage.</button><label id="ajax_msg_1">&nbsp;</label></p>
</fieldset></form>

<hr />

<form action="#" method="post" id="form_test"><fieldset>
  <h2>Test d'envoi de mail</h2>
  <label class="tab" for="f_courriel">Destinataire :</label><input id="f_courriel" name="f_courriel" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="" />
  <p><span class="tab"></span><input name="f_action" type="hidden" value="TestEnvoiCourriel" /><button id="f_submit" type="submit" class="mail_envoyer">Envoyer.</button><label id="ajax_msg_2">&nbsp;</label></p>
</fieldset></form>

