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
$TITRE = "Logo / Lien / Message"; // Pas de traduction car pas de choix de langue pour ce profil.

// Récupérer le fichier d'infos du partenaire
$fichier_chemin = 'info_'.$_SESSION['USER_ID'].'.php';
if(is_file(CHEMIN_DOSSIER_PARTENARIAT.$fichier_chemin))
{
  require(CHEMIN_DOSSIER_PARTENARIAT.$fichier_chemin);
}
else
{
  $partenaire_logo_actuel_filename = $partenaire_adresse_web = $partenaire_message = '' ;
}

// Récupérer le logo du partenaire (ou pas)
if($partenaire_logo_actuel_filename)
{
  $partenaire_logo_url = URL_DIR_PARTENARIAT.$partenaire_logo_actuel_filename;
  $disabled = '';
}
else
{
  $partenaire_logo_url = URL_DIR_IMG.'auto.gif';
  $disabled = ' disabled';
}

// Mise en session du nom du fichier image (pour le retrouver si enregistrement de nouveaux paramètres sans modification du fichier)
$_SESSION['tmp']['partenaire_logo_actuel_filename'] = $partenaire_logo_actuel_filename;

// Balises de lien (ou pas)
if($partenaire_adresse_web)
{
  $partenaire_lien_ouvrant = '<a href="'.html($partenaire_adresse_web).'" target="_blank" rel="noopener noreferrer">';
  $partenaire_lien_fermant = '</a>';
}
else
{
  $partenaire_lien_ouvrant = $partenaire_lien_fermant = '';
}

?>

<p class="astuce">Dans le cadre de la convention ENT, vous pouvez faire afficher un logo, un lien et un message en page d'accueil des utilisateurs connectés.</p>

<h2>Paramétrages</h2>
<form action="#" method="post" id="form_logo"><fieldset>
  <div>
    <label class="tab" for="f_logo">Logo :</label>
    <input type="hidden" id="f_upload_action" name="f_action" value="upload_logo" />
    <img id="image_logo" src="<?php echo html($partenaire_logo_url) ?>" />
    <input id="f_logo" type="file" name="userfile" />
    <button id="bouton_choisir_logo" type="button" class="fichier_import">Parcourir...</button>
    <button id="f_delete_logo" type="button" class="supprimer"<?php echo $disabled ?>>Supprimer.</button>
    <label id="ajax_msg_logo">&nbsp;</label>
  </div>
</fieldset></form>
<form action="#" method="post" id="form_gestion"><fieldset>
  <div><label class="tab" for="f_adresse_web">Adresse web :</label><input id="f_adresse_web" name="f_adresse_web" size="60" type="text" value="<?php echo html($partenaire_adresse_web) ?>" /></div>
  <div><label class="tab" for="f_message">Message :</label><textarea name="f_message" id="f_message" rows="4" cols="58"><?php echo html($partenaire_message) ?></textarea><br /><span class="tab"></span><label id="f_message_reste"></label></div>
  <div><span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="enregistrer" /><button id="f_submit" type="submit" class="parametre">Valider ces réglages.</button><label id="ajax_msg">&nbsp;</label></div>
</fieldset></form>

<hr />

<h2>Résultat</h2>
<div id="resultat">
  <?php echo $partenaire_lien_ouvrant ?>
    <span id="partenaire_logo"><img src="<?php echo html($partenaire_logo_url) ?>" /></span>
    <span id="partenaire_message"><?php echo nl2br(html($partenaire_message)) ?></span>
  <?php echo $partenaire_lien_fermant ?>
  <hr id="partenaire_hr" />
</div>
