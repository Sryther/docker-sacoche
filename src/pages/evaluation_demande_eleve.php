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
$TITRE = html(Lang::_("Demandes d'évaluations formulées"));

// Lister le nb de demandes d'évaluations autorisées suivant les matières
$infobulle = '';
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
if(!is_array($DB_TAB))
{
  $infobulle .= $DB_TAB;
}
else
{
  foreach($DB_TAB as $key => $DB_ROW)
  {
    $infobulle .= $DB_ROW['texte'].' : '.$DB_ROW['info'].'<br />';
  }
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
  <li><span class="astuce"><a title="<?php echo $infobulle ?>" href="#">Nombre de demandes autorisées par matière.</a></span></li>
</ul>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Date</th>
      <th>Matière</th>
      <th>Destinaire(s)</th>
      <th>Item</th>
      <th>Score</th>
      <th>Statut</th>
      <th>Messages</th>
      <th>Fichier</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les demandes d'évaluation
    $DB_TAB = DB_STRUCTURE_DEMANDE::DB_lister_demandes_eleve($_SESSION['USER_ID']);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $destinataires = ($DB_ROW['prof_id']) ? html(To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre'])) : 'enseignants concernés' ;
        $score  = ($DB_ROW['demande_score']!==null) ? $DB_ROW['demande_score'] : FALSE ;
        $statut = ($DB_ROW['demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
        $texte_lien_avant = ($DB_ROW['item_lien']) ? '<a target="_blank" rel="noopener noreferrer" href="'.html($DB_ROW['item_lien']).'">' : '' ;
        $texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '' ;
        $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['ref_perso'] : $DB_ROW['ref_auto'] ;
        $commentaire = ($DB_ROW['demande_messages']) ? 'oui <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.convertCRtoBR(html(html($DB_ROW['demande_messages']))).'" />' : 'non' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        $document    = ($DB_ROW['demande_doc'])      ? '<a href="'.html($DB_ROW['demande_doc']).'" target="_blank" rel="noopener noreferrer">oui</a>' : 'non' ;
        // Afficher une ligne du tableau 
        echo'<tr id="ids_'.$DB_ROW['demande_id'].'_'.$DB_ROW['item_id'].'_'.$DB_ROW['matiere_id'].'_'.$DB_ROW['prof_id'].'">';
        echo  '<td>'.To::date_mysql_to_french($DB_ROW['demande_date']).'</td>';
        echo  '<td>'.html($DB_ROW['matiere_nom']).'</td>';
        echo  '<td>'.$destinataires.'</td>';
        echo  '<td>'.$texte_lien_avant.html($item_ref).$texte_lien_apres.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($DB_ROW['item_nom'])).'" /></td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        echo  str_replace( '</td>' , ' <q class="actualiser" title="Actualiser le score (enregistré lors de la demande)." data-debut_date="'.$DB_ROW['periode_debut_date'].'"></q></td>' , Html::td_score( $score , 'score' /*methode_tri*/ , '' /*pourcent*/ ) );
        echo  '<td>'.$statut.'</td>';
        echo  '<td>'.$commentaire.'</td>';
        echo  '<td>'.$document.'</td>';
        echo  '<td class="nu"><q class="supprimer" title="Supprimer cette demande d\'évaluation."></q></td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="8"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
  </table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Supprimer une demande d'évaluation</h2>
  <p>Confirmez-vous la suppression de la demande &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_demande_id" name="f_demande_id" type="hidden" value="" /><input id="f_item_id" name="f_item_id" type="hidden" value="" /><input id="f_matiere_id" name="f_matiere_id" type="hidden" value="" /><input id="f_prof_id" name="f_prof_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
