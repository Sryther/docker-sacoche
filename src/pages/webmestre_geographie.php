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
$TITRE = "Zones géographiques"; // Pas de traduction car pas de choix de langue pour ce profil.

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__zones_geographiques">DOC : Zones géographiques (multi-structures).</a></span></p>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Identifiant</th>
      <th>Ordre</th>
      <th>Nom</th>
      <th class="nu"><q class="ajouter" title="Ajouter une zone."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les zones
    $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_zones();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['geo_id'].'">';
        echo  '<td>'.$DB_ROW['geo_id'].'</td>';
        echo  '<td>'.$DB_ROW['geo_ordre'].'</td>';
        echo  '<td>'.html($DB_ROW['geo_nom']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cette zone."></q>';
        echo    '<q class="dupliquer" title="Dupliquer cette zone."></q>';
        // La zone d'id 1 ne peut être supprimée, c'est la zone par défaut.
        echo ($DB_ROW['geo_id']!=1) ? '<q class="supprimer" title="Supprimer cette zone."></q>' : '<q class="supprimer_non" title="La zone par défaut ne peut pas être supprimée."></q>' ;
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
  <h2>Ajouter | Modifier | Dupliquer | Supprimer une zone</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_ordre">Ordre :</label><input id="f_ordre" name="f_ordre" type="number" min="0" max="9999" value="" /><br />
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="60" maxlength="65" />
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">Les structures associées seront rattachées à la zone d'identifiant 1 !</p>
    <p>Confirmez-vous la suppression de la zone &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<p>&nbsp;</p>

