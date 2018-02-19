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
$TITRE = html(Lang::_("Date de dernière connexion"));
?>

<?php
// Fabrication des éléments select du formulaire
$tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;
$select_groupe = HtmlForm::afficher_select($tab_groupes , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
?>

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab">Profil :</label>
    <?php if($_SESSION['USER_PROFIL_TYPE']=='administrateur'): ?>
    <label for="f_profil_administrateurs"><input id="f_profil_administrateurs" name="f_profil" type="radio" value="administrateur" /> Administrateurs</label>
    &nbsp;&nbsp;&nbsp;
    <label for="f_profil_directeurs"><input id="f_profil_directeurs" name="f_profil" type="radio" value="directeur" /> Directeurs</label>
    &nbsp;&nbsp;&nbsp;
    <?php endif; ?>
    <?php if(in_array($_SESSION['USER_PROFIL_TYPE'],array('administrateur','directeur'))): ?>
    <label for="f_profil_professeurs"><input id="f_profil_professeurs" name="f_profil" type="radio" value="professeur" /> Professeurs</label>
    &nbsp;&nbsp;&nbsp;
    <label for="f_profil_personnels"><input id="f_profil_personnels" name="f_profil" type="radio" value="personnel" /> Personnels autres</label>
    &nbsp;&nbsp;&nbsp;
    <?php endif; ?>
    <label for="f_profil_eleves"><input id="f_profil_eleves" name="f_profil" type="radio" value="eleve" /> Élèves</label>
    &nbsp;&nbsp;&nbsp;
    <label for="f_profil_parents"><input id="f_profil_parents" name="f_profil" type="radio" value="parent" /> Responsables légaux</label><br />
  <label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?> <label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<hr />

<div id="div_bilan" class="hide">
  <table id="bilan" class="hsort">
    <thead>
      <tr>
        <th data-sorter="text">Utilisateur</th>
        <th data-sorter="date_fr">Date</th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="2"></td></tr>
    </tbody>
  </table>
</div>
