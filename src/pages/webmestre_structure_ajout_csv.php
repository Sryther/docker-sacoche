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
$TITRE = "Ajout CSV d'établissements"; // Pas de traduction car pas de choix de langue pour ce profil.

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Créer un csv d'exemple
$separateur  = ';';
$fichier_csv = 'ajout_structures.csv';
$contenu_csv = 'Id_Import'.$separateur.'Id_Zone'.$separateur.'Localisation'.$separateur.'Dénomination'.$separateur.'UAI'.$separateur.'Contact_Nom'.$separateur.'Contact_Prénom'.$separateur.'Contact_Courriel'."\r\n";
$contenu_csv.= ''.$separateur.'1'.$separateur.'Jolieville'.$separateur.'CLG du Bonheur'.$separateur.'0123456A'.$separateur.'EDISON'.$separateur.'Thomas'.$separateur.'t.edison@mail.fr'."\r\n";
FileSystem::ecrire_fichier(CHEMIN_DOSSIER_TMP.$fichier_csv,$contenu_csv);
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__structure_ajout_csv">DOC : Ajout CSV d'établissements (multi-structures).</a></span></p>

<ul class="puce">
  <li><a target="_blank" rel="noopener noreferrer" href="<?php echo URL_DIR_TMP.$fichier_csv ?>"><span class="file file_txt">Récupérer le modèle de fichier <em>CSV</em> à utiliser.</span></a></li>
</ul>

<hr />

<h2>Importer un listing d'établissements</h2>

<form action="#" method="post" id="form_importer"><fieldset>
  <label class="tab" for="f_csv">Uploader fichier CSV :</label><input type="hidden" name="f_action" value="importer_csv" /><input id="f_csv" type="file" name="userfile" /><button id="bouton_choisir_csv" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_csv">&nbsp;</label><br />
  <span class="tab"></span><input id="f_courriel_envoi" name="f_courriel_envoi" type="checkbox" value="1" checked /><label for="f_courriel_envoi"> envoyer le courriel d'inscription</label><br />
  <span class="tab"></span><input id="f_courriel_copie" name="f_courriel_copie" type="checkbox" value="1" /><label for="f_courriel_copie"> envoyer une copie à <?php echo html(WEBMESTRE_COURRIEL); ?></label>
  <div id="div_import" class="hide">
    <span class="tab"></span><button id="bouton_importer" type="button" class="valider">Ajouter les établissements du fichier.</button><label id="ajax_msg_import">&nbsp;</label>
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
      </tr>
    </thead>
    <tbody>
      <tr>
      </tr>
    </tbody>
  </table>
  <p id="zone_actions">
    Pour les structures cochées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
    <button id="bouton_newsletter" type="button" class="mail_ecrire">Écrire un courriel.</button>
    <button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
    <label id="ajax_supprimer">&nbsp;</label>
  </p>
</form>
