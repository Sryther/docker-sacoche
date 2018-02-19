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
$TITRE = html(Lang::_("Niveaux"));

// Formulaire des familles de niveaux, en 2 catégories
$select_niveau_famille = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_niveaux() , 'f_famille' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_niveaux' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var ID_NIVEAU_PARTAGE_MAX = '.ID_NIVEAU_PARTAGE_MAX.';' );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_niveaux">DOC : Niveaux</a></span></div>

<div id="zone_partage">
  <hr />
  <h2>Niveaux partagés (officiels)</h2>
  <table class="form hsort">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Nom complet</th>
        <th class="nu"><q class="ajouter" title="Ajouter un niveau."></q></th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Lister les niveaux
      $DB_TAB = DB_STRUCTURE_NIVEAU::DB_lister_niveaux(FALSE /*is_specifique*/);
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          echo'<tr id="id_'.$DB_ROW['niveau_id'].'">';
          echo  '<td>'.html($DB_ROW['niveau_ref']).'</td>';
          echo  '<td>'.html($DB_ROW['niveau_nom']).'</td>';
          echo  '<td class="nu">';
          echo    '<q class="supprimer" title="Supprimer ce niveau."></q>';
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
  <h2>Niveaux spécifiques (établissement)</h2>
  <table class="form hsort">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Nom complet</th>
        <th class="nu"><q class="ajouter" title="Ajouter un niveau."></q></th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Lister les niveaux spécifiques
      $DB_TAB = DB_STRUCTURE_NIVEAU::DB_lister_niveaux(TRUE /*is_specifique*/);
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          // Afficher une ligne du tableau
          echo'<tr id="id_'.$DB_ROW['niveau_id'].'">';
          echo  '<td>'.html($DB_ROW['niveau_ref']).'</td>';
          echo  '<td>'.html($DB_ROW['niveau_nom']).'</td>';
          echo  '<td class="nu">';
          echo    '<q class="modifier" title="Modifier ce niveau."></q>';
          echo    '<q class="supprimer" title="Supprimer ce niveau."></q>';
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
  <h2>Ajouter | Modifier | Supprimer un niveau spécifique (ou partagé si supprimer)</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_ref">Référence :</label><input id="f_ref" name="f_ref" type="text" value="" size="6" maxlength="6" /><br />
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="45" maxlength="50" />
    </p>
  </div>
  <div id="gestion_delete_partage">
    <p class="danger">Les référentiels et les résultats associés ne seront plus accessibles !</p>
    <p>Confirmez-vous le retrait du niveau &laquo;&nbsp;<b id="gestion_delete_identite_partage"></b>&nbsp;&raquo; ?</p>
  </div>
  <div id="gestion_delete_perso">
    <p class="danger">Les référentiels et les résultats associés seront perdus !</p>
    <p>Confirmez-vous la suppression du niveau &laquo;&nbsp;<b id="gestion_delete_identite_perso"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_ajout_form" onsubmit="return false" class="hide">
  <hr />
  <h2>Rechercher un niveau</h2>
  <p><span class="tab"></span><button id="ajout_annuler" type="button" class="annuler">Annuler / Retour.</button></p>
  <fieldset id="f_recherche_famille">
    <label class="tab" for="f_famille">Famille :</label><?php echo $select_niveau_famille ?><br />
  </fieldset>
  <span class="tab"></span><label id="ajax_msg_recherche">&nbsp;</label>
  <ul id="f_recherche_resultat" class="puce hide">
    <li></li>
  </ul>
</form>

<p>&nbsp;</p>
