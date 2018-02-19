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
$TITRE = "Configuration d'un proxy"; // Pas de traduction car pas de choix de langue pour ce profil.
?>

<?php
$check_proxy_used = SERVEUR_PROXY_USED ? ' checked' : '' ;
$class_proxy_used = SERVEUR_PROXY_USED ? 'show' : 'hide' ;

$check_proxy_auth_used = SERVEUR_PROXY_AUTH_USED ? ' checked' : '' ;
$class_proxy_auth_used = SERVEUR_PROXY_AUTH_USED ? 'show' : 'hide' ;

$tab_select_proxy_type = array('CURLPROXY_HTTP'=>'HTTP','CURLPROXY_SOCKS5'=>'SOCKS5');
$select_proxy_type = '';
foreach($tab_select_proxy_type as $option_value => $option_texte)
{
  $selected = ($option_value==SERVEUR_PROXY_TYPE) ? ' selected' : '' ;
  $select_proxy_type .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

$tab_select_proxy_auth_method = array('CURLAUTH_BASIC'=>'BASIC','CURLAUTH_DIGEST'=>'DIGEST','CURLAUTH_GSSNEGOTIATE'=>'GSSNEGOTIATE','CURLAUTH_NTLM'=>'NTLM','CURLAUTH_ANY'=>'ANY','CURLAUTH_ANYSAFE'=>'ANYSAFE');
$select_proxy_auth_method = '';
foreach($tab_select_proxy_auth_method as $option_value => $option_texte)
{
  $selected = ($option_value==SERVEUR_PROXY_AUTH_METHOD) ? ' selected' : '' ;
  $disabled = (($option_value=='CURLAUTH_BASIC')||($option_value=='CURLAUTH_NTLM')) ? '' : ' disabled' ;
  $select_proxy_auth_method .= '<option value="'.$option_value.'"'.$selected.$disabled.'>'.$option_texte.'</option>';
}

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__configuration_proxy">DOC : Configuration d'un proxy.</a></span></p>

<hr />

<form action="#" method="post" id="form_proxy"><fieldset>
  <span class="tab"></span><label for="f_proxy_used"><input type="checkbox" id="f_proxy_used" name="f_proxy_used" value="1"<?php echo $check_proxy_used ?> /> Mon serveur nécessite d'utiliser un proxy.</label>
  <div id="div_proxy_used" class="<?php echo $class_proxy_used ?>">
    <label class="tab" for="f_proxy_name">Nom du proxy <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Exemple : proxy2" /> :</label><input id="f_proxy_name" name="f_proxy_name" size="10" type="text" value="<?php echo html(SERVEUR_PROXY_NAME); ?>" /><br />
    <label class="tab" for="f_proxy_port">Numéro du port <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Exemple : 8080" /> :</label><input id="f_proxy_port" name="f_proxy_port" size="5" type="text" value="<?php echo html(SERVEUR_PROXY_PORT); ?>" /><br />
    <label class="tab" for="f_proxy_type">Type de proxy <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Par défaut HTTP" /> :</label><select id="f_proxy_type" name="f_proxy_type"><?php echo $select_proxy_type ?></select><br />
    &nbsp;<br />
    <span class="tab"></span><label for="f_proxy_auth_used"><input type="checkbox" id="f_proxy_auth_used" name="f_proxy_auth_used" value="1"<?php echo $check_proxy_auth_used ?> /> Ce proxy nécessite une authentification.</label>
    <div id="div_proxy_auth_used" class="<?php echo $class_proxy_auth_used ?>">
      <label class="tab" for="f_proxy_auth_method">Méthode <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Par défaut BASIC.<br />Seuls deux protocoles sont actuellement supportés par cURL." /> :</label><select id="f_proxy_auth_method" name="f_proxy_auth_method"><?php echo $select_proxy_auth_method ?></select><br />
      <label class="tab" for="f_proxy_auth_user">Nom d'utilisateur :</label><input id="f_proxy_auth_user" name="f_proxy_auth_user" size="10" type="text" value="<?php echo html(SERVEUR_PROXY_AUTH_USER); ?>" /><br />
      <label class="tab" for="f_proxy_auth_pass">Mot de passe : </label><input id="f_proxy_auth_pass" name="f_proxy_auth_pass" size="10" type="text" value="<?php echo html(SERVEUR_PROXY_AUTH_PASS); ?>" />
    </div>
  </div>
  <p>
    <span class="tab"></span><button id="f_enregistrer" type="submit" class="parametre">Enregistrer ces réglages.</button><label id="ajax_msg_enregistrer">&nbsp;</label><br />
    <span class="tab"></span><button id="f_tester" type="button" class="parametre">Tester les réglages <span class="u">actuellement enregistrés</span>.</button><label id="ajax_msg_tester">&nbsp;</label>
  </p>
</fieldset></form>

<hr />

<div id="retour_test"></div>
