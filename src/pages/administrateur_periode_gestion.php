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
$TITRE = html(Lang::_("Gérer les périodes"));

$tab_periode_livret = array(
  NULL => '-',
  21 => 'Semestre 1/2',
  22 => 'Semestre 2/2',
  31 => 'Trimestre 1/3',
  32 => 'Trimestre 2/3',
  33 => 'Trimestre 3/3',
  41 => 'Bimestre 1/4',
  42 => 'Bimestre 2/4',
  43 => 'Bimestre 3/4',
  44 => 'Bimestre 4/4',
  51 => 'Période 1/5',
  52 => 'Période 2/5',
  53 => 'Période 3/5',
  54 => 'Période 4/5',
  55 => 'Période 5/5',
);

$select_livret = '';
foreach($tab_periode_livret as $livret_value => $livret_texte)
{
  $select_livret .= '<option value="'.$livret_value.'">'.$livret_texte.'</option>';
}

// Javascript
Layout::add( 'js_inline_before' , 'var select_livret="'.str_replace('"','\"',$select_livret).'";' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_periodes">DOC : Gestion des périodes</a></span></p>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Ordre</th>
      <th>Nom</th>
      <th>Livret Scolaire</th>
      <th class="nu"><q class="ajouter" title="Ajouter une période."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les périodes
    $DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_periodes();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['periode_id'].'">';
        echo  '<td>'.$DB_ROW['periode_ordre'].'</td>';
        echo  '<td>'.html($DB_ROW['periode_nom']).'</td>';
        echo  '<td>'.$tab_periode_livret[$DB_ROW['periode_livret']].'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cette période."></q>';
        echo    '<q class="dupliquer" title="Dupliquer cette période."></q>';
        echo    '<q class="supprimer" title="Supprimer cette période."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="3"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Dupliquer | Supprimer une période</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_ordre">Ordre :</label><input id="f_ordre" name="f_ordre" value="" type="number" min="1" max="99" /><br />
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="40" maxlength="40" /><br />
      <label class="tab" for="f_livret">Livret Scolaire <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Le Livret Scolaire requière de connaître le numéro et le nombre de bilans périodiques.<br />En dehors de ce cas précis, ce n'est pas nécessaire au fonctionnement de <em>SACoche</em>." /> :</label><select id="f_livret" name="f_livret"><?php echo $select_livret ?></select>
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">Les bilans officiels associés seront perdus !</p>
    <p>Confirmez-vous la suppression de la période &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<p>&nbsp;</p>
