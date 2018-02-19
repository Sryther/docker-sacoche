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
$TITRE = html(Lang::_("Sauvegarder / Restaurer la base"));
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_dump">DOC : Sauvegarde et restauration de la base</a></span></p>

<hr />

<h2>Sauvegarder la base</h2>
<form action="#" method="post" id="form_sauvegarde"><fieldset>
  <span class="tab"></span><button id="bouton_sauvegarde" type="button" class="dump_export">Lancer la sauvegarde.</button><label id="ajax_msg_sauvegarde">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Restaurer la base</h2>
<div class="danger b">Restaurer une sauvegarde antérieure écrasera irrémédiablement les données actuelles !</div>
<form action="#" method="post" id="form_restauration"><fieldset>
  <label class="tab" for="bouton_restauration">Uploader le fichier :</label><input type="hidden" name="f_action" value="uploader" /><input id="f_restauration" type="file" name="userfile" /><button id="bouton_restauration" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_restauration">&nbsp;</label>
</fieldset></form>

<hr />

<ul class="puce" id="ajax_info">
</ul>
<p>&nbsp;</p>
