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
$TITRE = html(Lang::_("Référentiels partagés (serveur communautaire)"));

if( !$_SESSION['SESAMATH_ID'] || !$_SESSION['SESAMATH_KEY'] )
{
  echo'<p><label class="erreur">Pour pouvoir effectuer la recherche d\'un référentiel partagé sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC : Gestion de l\'identité de l\'établissement</a></span>).</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_famille_matiere = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_matieres() , 'f_famille_matiere' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_matieres' /*optgroup*/ );
$select_famille_niveau  = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_niveaux()  , 'f_famille_niveau'  /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_niveaux'  /*optgroup*/ );
?>

<form action="#" method="post">
  <fieldset class="p">
    <label class="tab" for="f_famille_matiere">Famille de matières :</label><?php echo $select_famille_matiere ?><label id="ajax_maj_matiere">&nbsp;</label><br />
    <label class="tab" for="f_matiere">Matières :</label><select id="f_matiere" name="f_matiere"><option value="0">Toutes les matières</option></select>
  </fieldset>
  <fieldset class="p">
    <label class="tab" for="f_famille_niveau">Famille de niveaux :</label><?php echo $select_famille_niveau ?><label id="ajax_maj_niveau">&nbsp;</label><br />
    <label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><option value="0">Tous les niveaux</option></select>
  </fieldset>
  <fieldset class="p">
    <label class="tab" for="f_maj_date">Date de mise à jour :</label><input id="box_date" name="box_date" value="1" type="checkbox" checked /> <label for="box_date">sans restriction dans le temps</label><span class="hide">postérieure au <input id="f_maj_date" name="f_maj_date" size="8" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
    <label class="tab" for="f_structure"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Seules les structures partageant au moins un référentiel apparaissent." /> Structure :</label><select id="f_structure" name="f_structure"><option></option></select>
  </fieldset>
  <fieldset class="p">
    <span class="tab"></span><button id="rechercher" type="button" class="rechercher" disabled>Lancer / Actualiser la recherche.</button><label id="ajax_msg">&nbsp;</label>
  </fieldset>
</form>

<hr />

<div id="choisir_referentiel_communautaire" class="hide">
  <h2>Liste des référentiels trouvés</h2>
  <p>
    <span class="danger">Les référentiels partagés ne sont pas des modèles à suivre ! Ils peuvent être améliorables, inadaptés, obsolètes&hellip;</span><br />
    <span class="astuce">Le nombre de reprises ne présage pas de l'intérêt ni de la pertinence d'un référentiel.</span>
  </p>
  <table id="table_action" class="form hsort">
    <thead>
      <tr>
        <th>Matière</th>
        <th>Niveau</th>
        <th>Établissement<br />Localisation</th>
        <th>Établissement<br />Dénomination</th>
        <th>Info</th>
        <th>Date MAJ</th>
        <th>Nombre<br />reprises</th>
        <th class="nu"></th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="8"></td></tr>
    </tbody>
  </table>
</div>
