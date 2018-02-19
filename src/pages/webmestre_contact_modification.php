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
$TITRE = "Coordonnées du contact référent par établissement"; // Pas de traduction car pas de choix de langue pour ce profil.

$checked_user_oui = (CONTACT_MODIFICATION_USER=='oui') ? ' checked' : '' ;
$checked_user_non = (CONTACT_MODIFICATION_USER=='non') ? ' checked' : '' ;
$checked_mail_oui = (CONTACT_MODIFICATION_MAIL=='oui') ? ' checked' : '' ;
$checked_mail_non = (CONTACT_MODIFICATION_MAIL=='non') ? ' checked' : '' ;
$checked_mail_domaine = (!$checked_mail_oui && !$checked_mail_non) ? ' checked' : '' ;
$value_mail_domaine = ($checked_mail_domaine) ? CONTACT_MODIFICATION_MAIL : 'domaine.ext' ;

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__contact_modification">DOC : Contact référent (multi-structures).</a></span></p>

<hr />

<form action="#" method="post" id="form_contact"><fieldset>
  <p>
    <label class="tab">Nom &amp; prénom :</label><label for="f_user_oui"><input type="radio" id="f_user_oui" name="f_user" value="oui"<?php echo $checked_user_oui ?> /> Modifiables</label>&nbsp;&nbsp;&nbsp;<label for="f_user_non"><input type="radio" id="f_user_non" name="f_user" value="non"<?php echo $checked_user_non ?> /> Non modifiables</label>
  </p>
  <p>
    <label class="tab">Courriel :</label><label for="f_mail_oui"><input type="radio" id="f_mail_oui" name="f_mail" value="oui"<?php echo $checked_mail_oui ?> /> Modifiable</label>&nbsp;&nbsp;&nbsp;<label for="f_mail_non"><input type="radio" id="f_mail_non" name="f_mail" value="non"<?php echo $checked_mail_non ?> /> Non modifiable</label>&nbsp;&nbsp;&nbsp;<label for="f_mail_domaine"><input type="radio" id="f_mail_domaine" name="f_mail" value="domaine"<?php echo $checked_mail_domaine ?> /> Modifiable mais restreint au domaine @</label><input id="f_domaine" name="f_domaine" size="30" type="text" value="<?php echo html($value_mail_domaine); ?>" />
  </p>
  <p>
    <span class="tab"></span><button id="f_enregistrer" type="submit" class="parametre">Enregistrer ces paramètres.</button><label id="ajax_msg_enregistrer">&nbsp;</label>
  </p>
</fieldset></form>

<hr />
