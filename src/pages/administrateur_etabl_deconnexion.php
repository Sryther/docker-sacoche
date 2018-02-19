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
$TITRE = html(Lang::_("Redirection après déconnexion"));
?>

<p>
  Lorsque l'utilisateur clique sur le bouton de déconnexion, il est par défaut redirigé vers la page d'authentification de <em>SACoche</em>.<br />
  Ce formulaire permet de le rediriger vers une autre adresse (portail de l'établissement&hellip;).<br />
  Laisser le champ vide pour conserver le fonctionnement par défaut.
</p>

<form id="form_adresse" action="#" method="post"><fieldset>
  <label class="tab" for="url_deconnexion">Adresse (URL) <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="De la forme http://... ou https://..." /> :</label><input id="url_deconnexion" name="url_deconnexion" size="30" type="text" value="<?php echo html($_SESSION['DECONNEXION_ADRESSE_REDIRECTION']) ?>" />
  <p><span class="tab"></span><button id="bouton_valider" type="button" class="parametre">Valider cette adresse.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>
