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
$TITRE = html(Lang::_("Transfert de saisies depuis SACoche"));

// Fabrication des éléments select du formulaire
$select_f_groupes = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( TRUE /*sans*/ , FALSE /*tout*/ , TRUE /*ancien*/ ) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_sacoche">DOC : Transfert de saisies depuis SACoche</a></span></p>

<hr />

<form action="#" method="post" id="form_principal">
  <p>
    <label class="tab">Procédure :</label>&nbsp;&nbsp;&nbsp;<label for="f_mode_export"><input type="radio" id="f_mode_export" name="f_mode" value="export" /> Export</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_mode_import"><input type="radio" id="f_mode_import" name="f_mode" value="import" /> Import</label>
  </p>
</form>

<form action="#" method="post" id="form_export" class="hide">
  <hr />
  <p>
    <label class="tab">Regroupement :</label><?php echo $select_f_groupes ?><label id="ajax_msg_groupe">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
  </p>
  <p>
    <span class="tab"></span><button type="button" id="bouton_export" class="fichier_export" disabled>Générer le fichier.</button>
  </p>
</form>

<form action="#" method="post" id="form_import" class="hide">
  <hr />
  <p>
    <label class="tab" for="bouton_import">Fichier à importer :</label><input type="hidden" id="f_action" name="f_action" value="import" /><input type="hidden" id="f_etape" name="f_etape" value="0" /><input id="f_import" type="file" name="userfile" /><button type="button" id="bouton_import" class="fichier_import">Parcourir...</button>
  </p>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<ul class="puce p" id="ajax_info">
</ul>
