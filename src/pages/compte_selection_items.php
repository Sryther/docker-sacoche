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
$TITRE = html(Lang::_("Gérer ses regroupements d'items"));

// Javascript
Layout::add( 'js_inline_before' , 'var user_id   = '.$_SESSION['USER_ID'].';' );
Layout::add( 'js_inline_before' , 'var tab_items = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_profs = new Array();' );
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__gestion_regroupements_items">DOC : Gestion des regroupements d'items.</a></span></li>
</ul>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Nom</th>
      <th>Items</th>
      <th>Partage</th>
      <th class="nu"><q class="ajouter" title="Ajouter un regroupement d'items."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $DB_TAB = DB_STRUCTURE_SELECTION_ITEM::DB_lister($_SESSION['USER_ID']);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Profs avec qui on partage des droits
        if(!$DB_ROW['partage_listing'])
        {
          $profs_liste = '';
          $profs_nombre = 'non';
          $profs_bulle  = '';
        }
        else
        {
          $profs_liste  = $DB_ROW['partage_listing'];
          $nb_profs = mb_substr_count($profs_liste,'_')+1;
          $profs_nombre = ($nb_profs+1).' collègues';
          $profs_bulle  = ($nb_profs<10) ? ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" class="bulle_profs" />' : '' ;
        }
        // Droit sur cette évaluation
        if($DB_ROW['proprio_id']==$_SESSION['USER_ID'])
        {
          $niveau_droit = 4; // propriétaire
        }
        elseif($profs_liste) // forcément
        {
          $search_liste = '_'.$profs_liste.'_';
          if( strpos( $search_liste, '_m'.$_SESSION['USER_ID'].'_' ) !== FALSE )
          {
            $niveau_droit = 3; // modifier
          }
          elseif( strpos( $search_liste, '_v'.$_SESSION['USER_ID'].'_' ) !== FALSE )
          {
            $niveau_droit = 1; // voir
          }
          else
          {
            exit('Erreur : droit attribué sur le regroupement n°'.$DB_ROW['selection_item_id'].' non trouvé !');
          }
        }
        else
        {
          exit('Erreur : vous n\'êtes ni propriétaire ni bénéficiaire de droits sur le regroupement n°'.$DB_ROW['selection_item_id'].' !');
        }
        // Liste des items
        $items_liste  = $DB_ROW['item_listing'];
        $items_nombre = ($DB_ROW['item_listing']) ? mb_substr_count($items_liste,'_')+1 : 0 ;
        $items_texte  = ($items_nombre>1) ? $items_nombre.' items' : ( ($items_nombre) ? '1 item' : 'aucun' ) ;
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['selection_item_id'].'">';
        echo  '<td>'.html($DB_ROW['selection_item_nom']).'</td>';
        echo  '<td>'.$items_texte.'</td>';
        echo  '<td id="proprio_'.$DB_ROW['proprio_id'].'">'.$profs_nombre.$profs_bulle.'</td>';
        echo  '<td class="nu">';
        echo    ($niveau_droit>=3) ? '<q class="modifier" title="Modifier cette sélection d\'items."></q>' : '<q class="modifier_non" title="Action nécessitant le droit de modification (voir '.html($DB_ROW['proprietaire']).')."></q>' ;
        echo    '<q class="dupliquer" title="Dupliquer cette sélection d\'items."></q>';
        echo    ($niveau_droit==4) ? '<q class="supprimer" title="Supprimer cette sélection d\'items."></q>' : '<q class="supprimer_non" title="Suppression restreinte au propriétaire de la sélection ('.html($DB_ROW['proprietaire']).')."></q>' ;
        echo  '</td>';
        echo'</tr>'.NL;
        // Javascript
        Layout::add( 'js_inline_before' , 'tab_items["'.$DB_ROW['selection_item_id'].'"]="'.$items_liste.'";' );
        Layout::add( 'js_inline_before' , 'tab_profs["'.$DB_ROW['selection_item_id'].'"]="'.$profs_liste.'";' );
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="3">Cliquer sur l\'icône ci-dessus (symbole "+" dans un rond vert) pour ajouter un regroupement.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Dupliquer | Supprimer un regroupements d'items</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="50" maxlength="60" />
    </p>
    <p>
      <label class="tab" for="f_compet_nombre">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="" readonly /><q class="choisir_compet" title="Voir ou choisir les items."></q><input id="f_compet_liste" name="f_compet_liste" type="text" value="" class="invisible" />
    </p>
    <p>
      <label class="tab" for="f_prof_nombre">Partage collègues :</label><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="" readonly /><q id="choisir_prof" class="choisir_prof" title="Voir ou choisir les collègues."></q><input id="f_prof_liste" name="f_prof_liste" type="text" value="" class="invisible" />
      <span id="choisir_prof_non" class="astuce">Choix restreint au propriétaire de l'évaluation.</span>
    </p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression du regroupements d'items &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><input id="f_origine" name="f_origine" type="hidden" value="<?php echo $PAGE ?>" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <div id="arborescence"><label class="loader">Chargement&hellip;</label></div>
  <div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
  <div class="astuce">Résumé des différents niveaux de droits (les plus élevés incluent les plus faibles)&nbsp;:</div>
  <ul class="puce">
    <li>0 &rarr; <span class="select_img droit_x">&nbsp;</span> aucun droit</li>
    <li>1 &rarr; <span class="select_img droit_v">&nbsp;</span> visualiser le regroupement (et le dupliquer)</li>
    <li>3 &rarr; <span class="select_img droit_m">&nbsp;</span> modifier le regroupement (nom, items) <span class="danger">Risqué : à utiliser en connaissance de cause&nbsp;!</span></li>
  </ul>
  <hr />
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion#toggle_evaluations_profs">DOC : Associer des collègues à un regroupement.</a></span>
  <hr />
  <?php echo HtmlForm::afficher_select_collegues( FALSE /*only_profs*/ , array( 1=>'v' , 3=>'m' ) ) ?>
  <div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>
