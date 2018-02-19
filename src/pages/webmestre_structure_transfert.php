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
$TITRE = "Transfert d'établissements"; // Pas de traduction car pas de choix de langue pour ce profil.

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : FALSE ; // demande d'exports depuis structure_multi.php
$select_structure = HtmlForm::afficher_select( DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , 'f_base' /*select_nom*/ , FALSE /*option_first*/ , $selection , 'zones_geo' /*optgroup*/ , TRUE /*multiple*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__structure_transfert">DOC : Transfert d'établissements (multi-structures).</a></span></p>

<hr />

<h2>Exporter des établissements (données &amp; bases)</h2>

<form action="#" method="post" id="form_exporter">
  <p>
    <label class="tab" for="f_base">Structure(s) :</label><span id="f_base" class="select_multiple"><?php echo $select_structure ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
    <span class="tab"></span><button id="bouton_exporter" type="button" class="dump_export">Créer les fichiers d'export.</button><label id="ajax_msg_export">&nbsp;</label>
  </p>
  <div id="div_info_export" class="hide">
    <ul id="puce_info_export" class="puce"><li></li></ul>
    <span id="ajax_export_num" class="hide"></span>
    <span id="ajax_export_max" class="hide"></span>
  </div>
  <p id="zone_actions_export" class="hide">
    <label class="alerte">Ces deux fichiers sont nécessaires pour toute importation ; vérifiez leur validité une fois récupérés.</label><br />
    <label class="alerte">Pour des raisons de sécurité et de confidentialité, ces fichiers seront effacés du serveur dans 1h.</label><br />
    Pour les structures sélectionnées :<?php /* input listing_ids plus bas */ ?>
    <button id="bouton_newsletter_export" type="button" class="mail_ecrire">Écrire un courriel.</button>
    <button id="bouton_stats_export" type="button" class="stats">Calculer les statistiques.</button>
    <button id="bouton_supprimer_export" type="button" class="supprimer">Supprimer.</button>
    <label id="ajax_supprimer_export">&nbsp;</label>
  </p>
</form>


<hr />

<h2>Importer des établissements (données &amp; bases)</h2>

<form action="#" method="post" id="form_importer"><fieldset>
    <input id="f_import" type="file" name="userfile" />
    <input type="hidden" id="f_upload_action" name="f_action" value="" />
    <label class="tab" for="importer_csv">Uploader fichier CSV :</label><button id="importer_csv" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_importer_csv">&nbsp;</label>
  <div id="div_zip" class="hide">
    <label class="tab" for="importer_zip">Uploader fichier ZIP :</label><button id="importer_zip" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_importer_zip">&nbsp;</label>
  </div>
  <div id="div_import" class="hide">
    <span class="tab"></span><button id="bouton_importer" type="button" class="valider">Restaurer les établissements.</button><label id="ajax_msg_import">&nbsp;</label>
  </div>
</fieldset></form>
<div id="div_info_import" class="hide">
  <ul id="puce_info_import" class="puce"><li></li></ul>
  <span id="ajax_import_num" class="hide"></span>
  <span id="ajax_import_max" class="hide"></span>
</div>

<p>&nbsp;</p>

<form action="#" method="post" id="structures" class="hide">
  <table class="form" id="table_action">
    <thead>
      <tr>
        <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
        <th>Id</th>
        <th>Structure</th>
        <th>Contact</th>
        <th>Bilan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
      </tr>
    </tbody>
  </table>
  <p id="zone_actions_import">
    Pour les structures cochées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
    <button id="bouton_newsletter_import" type="button" class="mail_ecrire">Écrire un courriel.</button>
    <button id="bouton_stats_import" type="button" class="stats">Calculer les statistiques.</button>
    <button id="bouton_supprimer_import" type="button" class="supprimer">Supprimer.</button>
    <label id="ajax_supprimer_import">&nbsp;</label>
  </p>
</form>
