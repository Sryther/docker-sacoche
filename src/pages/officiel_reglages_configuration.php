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
$TITRE = html(Lang::_("Configuration des bilans officiels"));
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">DOC : Réglages synthèses &amp; bilans &rarr; Configuration des bilans officiels</a></span></div>

<hr />

<div id="zone_tableaux">

<h2>Liste des configurations</h2>

<table id="table_action" class="form hsort p">
  <thead>
    <tr>
      <th>Type de bilan</th>
      <th>Référence</th>
      <th>Dénomination / Commentaire</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Tableau pour retenir au passage les différentes configurations existantes
    $tab_types = array(
      'releve'   => array( 'ordre'=>1 , 'nom' => "Relevé d'évaluations" , 'options' => array() ) ,
      'bulletin' => array( 'ordre'=>2 , 'nom' => "Bulletin scolaire"    , 'options' => array() ) ,
      'livret'   => array( 'ordre'=>3 , 'nom' => "Livret Scolaire"      , 'options' => array() ) ,
    );
    // Lister les configurations
    $DB_TAB = DB_STRUCTURE_OFFICIEL_CONFIG::DB_lister_configurations();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_types[$DB_ROW['officiel_type']]['options'][$DB_ROW['configuration_ref']] = '<option value="'.$DB_ROW['configuration_ref'].'">'.html($DB_ROW['configuration_nom']).'</option>';
      echo'<tr id="'.$DB_ROW['officiel_type'].'_'.$DB_ROW['configuration_ref'].'">';
      echo  '<td><i>'.$tab_types[$DB_ROW['officiel_type']]['ordre'].'</i>'.$tab_types[$DB_ROW['officiel_type']]['nom'].'</td>';
      echo  '<td>'.$DB_ROW['configuration_ref'].'</td>';
      echo  '<td>'.html($DB_ROW['configuration_nom']).'</td>';
      echo  '<td class="nu">';
      echo    '<q class="ajouter" title="Ajouter une configuration (à partir de celle-ci)."></q>';
      echo    '<q class="modifier" title="Modifier cette configuration."></q>';
      echo    ($DB_ROW['configuration_ref']!='defaut') ? '<q class="supprimer" title="Supprimer cette configuration."></q>' : '<q class="supprimer_non" title="La configuration par défaut ne peut pas être supprimée."></q>' ;
      echo  '</td>';
      echo'</tr>'.NL;
    }
    ?>
  </tbody>
</table>


<hr />

<h2>Affectation des configurations aux classes</h2>

<p class="astuce">L'enregistrement d'une modification est automatique.</p>

<table id="table_affectation" class="form hsort p">
  <thead>
    <tr>
      <th>Classe</th>
      <?php
      foreach($tab_types as $type => $tab)
      {
        echo'<th>'.$tab['nom'].'</th>';
        $tab_types[$type]['options'] = '<select name="'.$type.'">'.implode('',$tab['options']).'</select>';
      }
      ?>
    </tr>
  </thead>
  <tbody>
    <?php
    $tab_classes = array();
    // Lister les configurations
    $DB_TAB = DB_STRUCTURE_OFFICIEL_CONFIG::DB_lister_classes_avec_configurations();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_configuration_releve, groupe_configuration_bulletin, groupe_configuration_livret ';
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['groupe_id'].'">';
        echo  '<td>'.html($DB_ROW['groupe_nom']).'</td>';
        echo  '<td>'.str_replace( 'value="'.$DB_ROW['groupe_configuration_releve'  ].'"' , 'value="'.$DB_ROW['groupe_configuration_releve'  ].'" selected' , $tab_types['releve'  ]['options'] ).'</td>';
        echo  '<td>'.str_replace( 'value="'.$DB_ROW['groupe_configuration_bulletin'].'"' , 'value="'.$DB_ROW['groupe_configuration_bulletin'].'" selected' , $tab_types['bulletin']['options'] ).'</td>';
        echo  '<td>'.str_replace( 'value="'.$DB_ROW['groupe_configuration_livret'  ].'"' , 'value="'.$DB_ROW['groupe_configuration_livret'  ].'" selected' , $tab_types['livret'  ]['options'] ).'</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="4"><label class="erreur">Aucune classe n\'est enregistrée.</label></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<p>&nbsp;</p>

</div>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer une configuration</h2>
  <div id="form_contenu">
  </div>
  <p>
    <span class="tab"></span><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_matieres" class="hide">
  <h3>Matieres sans moyennes</h3>
  <?php echo HtmlForm::afficher_checkbox_matieres() ?>
  <div style="clear:both"><button id="valider_matieres" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_matieres" type="button" class="annuler">Annuler / Retour</button></div>
</form>
