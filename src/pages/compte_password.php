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
$TITRE = html(Lang::_("Changer son mot de passe"));

// Javascript
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MIN = '.$_SESSION['USER_MDP_LONGUEUR_MINI'].';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );

if( !in_array($_SESSION['USER_PROFIL_TYPE'],array('administrateur','webmestre','partenaire')) && !Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_MODIFIER_MDP'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if($_SESSION['CONNEXION_MODE']!='normal')
{
  echo'<p class="astuce">Le mode de connexion est configuré pour utiliser une authentification externe.<br />Ce formulaire ne modifiera pas le mode de passe correspondant, il ne concerne qu\'un mot de passe spécifique à l\'application.</p><hr />'.NL;
}
?>


<p><?php echo html(Lang::_("Entrer le mot de passe actuel, puis deux fois le nouveau mot de passe choisi.")) ?></p>
<form action="#" method="post"><fieldset>
  <label class="tab" for="f_password0">Actuel :</label><input id="f_password0" name="f_password0" size="40" maxlength="<?php echo PASSWORD_LONGUEUR_MAX ?>" type="password" value="" /><br />
  <label class="tab" for="f_password1"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La robustesse du mot de passe indiqué dans ce champ est estimée ci-dessous." /> Nouveau 1/2 :</label><input id="f_password1" name="f_password1" size="40" maxlength="<?php echo PASSWORD_LONGUEUR_MAX ?>" type="password" value="" /><br />
  <label class="tab" for="f_password2">Nouveau 2/2 :</label><input id="f_password2" name="f_password2" size="40" maxlength="<?php echo PASSWORD_LONGUEUR_MAX ?>" type="password" value="" /><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="mdp_perso">Valider le changement.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
<p><span class="astuce"><?php echo html(Lang::_("Un mot de passe est considéré comme robuste s'il comporte de nombreux caractères, mélangeant des lettres minuscules et majuscules, des chiffres et d'autres symboles.")) ?></span></p>
<div id="robustesse"><?php echo html(Lang::_("indicateur de robustesse")) ?> : <span>0</span> / 12</div>
