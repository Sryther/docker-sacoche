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
$TITRE = html(Lang::_("Matières"));

// Formulaire des familles de matières, en 3 catégories
$select_matiere_famille = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_matieres() , 'f_famille' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_matieres' /*optgroup*/ );

// Lister les matières de l'établissement
$DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
$matieres_options = '<option value="0"></option>';
foreach($DB_TAB as $DB_ROW)
{
  $matieres_options .= '<option value="'.$DB_ROW['matiere_id'].'">'.html($DB_ROW['matiere_nom'].' ('.$DB_ROW['matiere_ref'].')').'</option>' ;
}

// Javascript
Layout::add( 'js_inline_before' , 'var ID_MATIERE_PARTAGEE_MAX = '.ID_MATIERE_PARTAGEE_MAX.';' );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_matieres">DOC : Matières</a></span></div>

<div id="zone_partage">
  <hr />
  <h2>Matières partagées (officielles)</h2>
  <table class="form hsort">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Nom complet</th>
        <th class="nu"><q class="ajouter" title="Ajouter une matière."></q></th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Lister les matières partagées
      $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres( FALSE /*is_specifique*/ );
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          // Afficher une ligne du tableau
          echo'<tr id="id_'.$DB_ROW['matiere_id'].'">';
          echo  '<td>'.html($DB_ROW['matiere_ref']).'</td>';
          echo  '<td>'.html($DB_ROW['matiere_nom']).'</td>';
          echo  '<td class="nu">';
          echo    '<q class="supprimer" title="Supprimer cette matière."></q>';
          echo  '</td>';
          echo'</tr>'.NL;
        }
      }
      else
      {
        echo'<tr class="vide"><td class="nu" colspan="2"></td><td class="nu"></td></tr>'.NL;
      }
      ?>
    </tbody>
  </table>
</div>

<div id="zone_perso">
  <hr />
  <h2>Matières spécifiques (établissement)</h2>
  <table class="form hsort">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Nom complet</th>
        <th class="nu"><q class="ajouter" title="Ajouter une matière."></q></th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Lister les matières spécifiques
      $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres( TRUE /*is_specifique*/ );
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          // Afficher une ligne du tableau
          echo'<tr id="id_'.$DB_ROW['matiere_id'].'">';
          echo  '<td>'.html($DB_ROW['matiere_ref']).'</td>';
          echo  '<td>'.html($DB_ROW['matiere_nom']).'</td>';
          echo  '<td class="nu">';
          echo    '<q class="modifier" title="Modifier cette matière."></q>';
          echo    '<q class="supprimer" title="Supprimer cette matière."></q>';
          echo  '</td>';
          echo'</tr>'.NL;
        }
      }
      else
      {
        echo'<tr class="vide"><td class="nu" colspan="2"></td><td class="nu"></td></tr>'.NL;
      }
      ?>
    </tbody>
  </table>
</div>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer une matière spécifique (ou partagée si supprimer)</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_ref">Référence :</label><input id="f_ref" name="f_ref" type="text" value="" size="5" maxlength="5" /><br />
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="45" maxlength="50" />
    </p>
  </div>
  <div id="gestion_delete_partage">
    <p class="danger">Les référentiels et les résultats associés ne seront plus accessibles !</p>
    <p>Confirmez-vous le retrait de la matière &laquo;&nbsp;<b id="gestion_delete_identite_partage"></b>&nbsp;&raquo; ?</p>
  </div>
  <div id="gestion_delete_perso">
    <p class="danger">Les référentiels et les résultats associés seront perdus !</p>
    <p>Confirmez-vous la suppression de la matière &laquo;&nbsp;<b id="gestion_delete_identite_perso"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="form_move">
  <hr />
  <h2>Déplacer les référentiels d'une matière vers une autre</h2>
  <p class="astuce">Ce menu peut par exemple servir à convertir une matière spécifique en matière officielle afin de pouvoir en partager les référentiels.</p>
  <ul class="puce">
    <li>Les deux matières doivent être visibles ci-dessus.</li>
    <li>La nouvelle matière doit être vierge de tout référentiel.</li>
    <li>L'ancienne matière sera retirée / supprimée après l'opération.</li>
  </ul>
  <p class="danger">Cette opération est sensible : il est recommandé de l'effectuer en période creuse et de sauvegarder la base avant.</p>
  <fieldset>
    <label class="tab" for="f_matiere_avant">Ancienne matière :</label><select id="f_matiere_avant" name="f_matiere_avant"><?php echo $matieres_options ?></select><br />
    <label class="tab" for="f_matiere_apres">Nouvelle matière :</label><select id="f_matiere_apres" name="f_matiere_apres"><?php echo $matieres_options ?></select><br />
    <span class="tab"></span><button id="deplacer_referentiels" type="button" class="parametre">Déplacer les référentiels.</button><label id="ajax_msg_move">&nbsp;</label>
  </fieldset>
</form>

<form action="#" method="post" id="zone_ajout_form" onsubmit="return false" class="hide">
  <hr />
  <h2>Rechercher une matière officielle</h2>
  <p><span class="tab"></span><button id="ajout_annuler" type="button" class="annuler">Annuler / Retour.</button></p>
  <p id="f_recherche_mode">
    <label class="tab">Technique :</label><label for="f_mode_famille"><input type="radio" id="f_mode_famille" name="f_mode" value="famille" /> recherche par famille de matières</label>&nbsp;&nbsp;&nbsp;<label for="f_mode_motclef"><input type="radio" id="f_mode_motclef" name="f_mode" value="motclef" /> recherche par mots-clefs</label>
  </p>
  <fieldset id="f_recherche_famille" class="hide">
    <label class="tab" for="f_famille">Famille :</label><?php echo $select_matiere_famille ?><br />
  </fieldset>
  <fieldset id="f_recherche_motclef" class="hide">
    <label class="tab" for="f_motclef">Mots-clefs :</label><input id="f_motclef" name="f_motclef" size="50" type="text" value="" /><br />
    <span class="tab"></span><button id="rechercher_motclef" type="button" class="rechercher">Lancer la recherche.</button><br />
  </fieldset>
  <span class="tab"></span><label id="ajax_msg_recherche">&nbsp;</label>
  <ul id="f_recherche_resultat" class="puce hide">
    <li></li>
  </ul>
</form>

<p>&nbsp;</p>
