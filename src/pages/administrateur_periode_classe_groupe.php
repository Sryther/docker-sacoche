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
$TITRE = html(Lang::_("Affecter les périodes aux classes & groupes"));
?>

<?php
// Fabrication des éléments select du formulaire
$select_periodes        = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl(TRUE /*alerte*/) , 'select_periodes'        /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/ );
$select_classes_groupes = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl()         , 'select_classes_groupes' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ , TRUE /*multiple*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_periodes">DOC : Gestion des périodes</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Périodes :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="select_periodes" class="select_multiple"><?php echo $select_periodes; ?></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Classes &amp; groupes :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <span id="select_classes_groupes" class="select_multiple"><?php echo $select_classes_groupes; ?></span>
    </td>
    <td class="nu" style="width:25em">
      <p>
        <button id="ajouter" type="button" class="periode_ajouter">Ajouter / Modifier ces associations.</button><br />
        du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
        au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      </p>
      <hr />
      <p>
        <button id="retirer" type="button" class="periode_retirer">Retirer ces associations.</button><br />
        <span class="danger">Les bilans officiels associés seront perdus !</span>
      </p>
      <hr />
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<div id="bilan">
</div>
