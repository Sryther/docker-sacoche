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
$TITRE = "Lettre d'information"; // Pas de traduction car pas de choix de langue pour ce profil.

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : FALSE ; // demande de newsletter depuis webmestre_structure_multi.php ou webmestre_statistiques.php
$select_structure = HtmlForm::afficher_select( DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , 'f_base' /*select_nom*/ , FALSE /*option_first*/ , $selection , 'zones_geo' /*optgroup*/ , TRUE /*multiple*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__publipostage">DOC : Lettre d'information (multi-structures).</a></span></p>

<hr />

<div id="ajax_info" class="hide">
  <h2>Envoi de la lettre</h2>
  <label id="ajax_msg1"></label>
  <ul class="puce"><li id="ajax_msg2"></li></ul>
  <span id="ajax_num" class="hide"></span>
  <span id="ajax_max" class="hide"></span>
</div>

<form action="#" method="post" id="newsletter"><fieldset>
  <label class="tab" for="f_base">Destinataire(s) :</label><span id="f_base" class="select_multiple"><?php echo $select_structure ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
  <label class="tab" for="f_titre">Titre :</label><input id="f_titre" name="f_titre" value="" size="50" /><br />
  <label class="tab" for="f_contenu">Contenu :</label><textarea id="f_contenu" name="f_contenu" rows="15" cols="100">message ici, sans bonjour ni au revoir, car l'en-tête et le pied du message sont automatiquement ajoutés</textarea><br />
  <span class="tab"></span><button id="bouton_valider" type="button" class="mail_envoyer">Envoyer la lettre.</button><label id="ajax_msg">&nbsp;</label>
  <hr />
</fieldset></form>

<form action="#" method="post" id="structures">
  <p id="zone_actions">
    Pour les structures sélectionnées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
    <button id="bouton_stats" type="button" class="stats">Calculer les statistiques.</button>
    <button id="bouton_transfert" type="button" class="fichier_export">Exporter données &amp; bases.</button>
    <button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
    <label id="ajax_supprimer">&nbsp;</label>
  </p>
</form>
