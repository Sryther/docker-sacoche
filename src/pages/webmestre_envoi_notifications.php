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
$TITRE = "Courriels de notification"; // Pas de traduction car pas de choix de langue pour ce profil.

$send_check_oui = (COURRIEL_NOTIFICATION=='oui') ? ' checked' : '' ;
$send_check_non = (COURRIEL_NOTIFICATION=='non') ? ' checked' : '' ;

// Javascript
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<p>
  Sur les installations de type multi-structures, <em>SACoche</em> a besoin d'envoyer des courriels (inscription, newsletter, envoi identifiants administrateur, &hellip;).<br />
  <em>SACoche</em> dispose aussi d'une fonctionnalité de renvoi d'un mot de passe par courriel en cas de perte, sans intervention d'une tierce personne.<br />
  Ces fonctionnalités, essentielles, ne peuvent pas être désactivées.
</p>
<p>
  Au sein des établissements, <em>SACoche</em> dispose aussi d'une fonctionnalité de réception par courriel de diverses notifications, au choix des utilisateurs.<br />
  Sur un serveur de test local dans lequel on aurait restauré une base réelle, il est gênant de laisser partir de tels envois inappropriés.<br />
  Cela peut ainsi être désactivé ci-dessous.
</p>
<p class="astuce">
  Consultez aussi le menu <a href="./index.php?page=webmestre_mail_bounces">[Paramétrages techniques] [Adresse de rebond &amp; Test mail]</a>.
</p>

<hr />

<form action="#" method="post" id="form_choix_envoi"><fieldset>
  <p>
    <label class="tab">Envoi des courriels :</label>
    <label for="f_send_oui"><input type="radio" id="f_send_oui" name="f_send" value="oui"<?php echo $send_check_oui ?> /> Oui (installation en production)</label>
    &nbsp;&nbsp;&nbsp;
    <label for="f_send_non"><input type="radio" id="f_send_non" name="f_send" value="non"<?php echo $send_check_non ?> /> Non (serveur de test)</label>
  </p>
  <p><span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="choix_envoi" /><button id="f_submit" type="submit" class="parametre">Valider ce réglage.</button><label id="ajax_msg_choix_envoi">&nbsp;</label></p>
</fieldset></form>

<?php if(HEBERGEUR_INSTALLATION=='multi-structures'): /* * * * * * MULTI-STRUCTURES DEBUT * * * * * */ ?>

<hr />

<p>
  Si l'adresse de rebond reçoit des notifications de courriels en erreur pour un établissement, il est possible d'intervenir sur sa base.
</p>

<hr />

<form action="#" method="post" id="form_modif_mail"><fieldset>
  <p>
    <label class="tab" for="f_base_id">Structure Id :</label><input id="f_base_id" name="f_base_id" size="6" type="text" value="" /><br />
    <label class="tab" for="f_courriel_old">Courriel actuel :</label><input id="f_courriel_old" name="f_courriel_old" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="" /><br />
    <label class="tab">Action :</label>
    <label for="f_change_remove"><input type="radio" id="f_change_remove" name="f_change" value="remove" checked /> Retirer</label>
    &nbsp;&nbsp;&nbsp;
    <label for="f_change_replace"><input type="radio" id="f_change_replace" name="f_change" value="replace" /> Remplacer</label><br />
    <span id="span_replace" class="hide">
      <label class="tab" for="f_courriel_new">Nouveau courriel :</label><input id="f_courriel_new" name="f_courriel_new" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" type="text" value="" />
    </span>
  </p>
  <p><span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="modif_mail" /><button id="f_submit" type="submit" class="parametre">Valider ce changement.</button><label id="ajax_msg_modif">&nbsp;</label></p>
</fieldset></form>

<?php endif /* * * * * * MULTI-STRUCTURES FIN * * * * * */ ?>
