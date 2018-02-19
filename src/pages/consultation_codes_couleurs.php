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
$TITRE = html(Lang::_("Codes de notation / États d'acquisition"));

// codes de notation
$tab_notes = array();
foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
{
  $tab_notes[] = '<li id="N'.$note_id.'" style="display:inline-block" class="sortable">'
               . '<div class="t9 notnow">#'.$note_id.'</div>'
               . '<div><label class="tab mini">valeur :</label>'.$_SESSION['NOTE'][$note_id]['VALEUR'].'</div>'
               . '<div><label class="tab mini">symbole :</label><img alt="#'.$note_id.'" src="'.Html::note_src_couleur($_SESSION['NOTE'][$note_id]['IMAGE']).'" /></div>'
               . '<div><label class="tab mini">sigle :</label>'.html($_SESSION['NOTE'][$note_id]['SIGLE']).'</div>'
               . '<div><label class="tab mini">légende :</label>'.html($_SESSION['NOTE'][$note_id]['LEGENDE']).'</div>'
               . '<div><label class="tab mini">touche :</label><kbd>'.$_SESSION['NOTE'][$note_id]['CLAVIER'].'</kbd></div>'
               . '</li>';
}

// états d'acquisition
$tab_acquis = array();
foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
{
  $tab_acquis[] = '<li id="A'.$acquis_id.'" style="display:inline-block" class="sortable">'
               . '<div class="t9 notnow">#'.$acquis_id.'</div>'
               . '<div><label class="tab mini">seuils :</label>'.$tab_acquis_info['SEUIL_MIN'].' ~ '.$tab_acquis_info['SEUIL_MAX'].'</div>'
               . '<div><label class="tab mini">couleur :</label><span style="background-color:'.$tab_acquis_info['COULEUR'].'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>'
               . '<div><label class="tab mini">sigle :</label>'.html($tab_acquis_info['SIGLE']).'</div>'
               . '<div><label class="tab mini">légende :</label>'.html($tab_acquis_info['LEGENDE']).'</div>'
               . '<div><label class="tab mini">valeur :</label>'.($tab_acquis_info['VALEUR']/100).'</div>'
               . '</li>';
}
?>

<h2><?php echo html(Lang::_("Codes de notation")) ?></h2>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__codes_notation">DOC : Codes de notation</a></span></p>

<p><?php echo sprintf_lang(html(Lang::_("L'établissement utilise %1s|%2d codes de notation|%3s pour les évaluations")),array('<b>',$_SESSION['NOMBRE_CODES_NOTATION'],'</b>')) ?>&nbsp;:</p>

<form action="#" method="post" id="form_notes">
<ul>
  <?php echo implode('',$tab_notes); ?>
</ul>
</form>

<hr />

<h2><?php echo html(Lang::_("États d'acquisition")) ?></h2>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__etats_acquisition">DOC : États d'acquisitions</a></span></p>

<p><?php echo sprintf_lang(html(Lang::_("L'établissement utilise %1s|%2d états d'acquisition|%3s pour les bilans")),array('<b>',$_SESSION['NOMBRE_ETATS_ACQUISITION'],'</b>')) ?>&nbsp;:</p>

<form action="#" method="post" id="form_acquis">
<ul>
  <?php echo implode('',$tab_acquis); ?>
</ul>
</form>

<hr />

<?php
if(Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_PARAM_ALGORITHME']))
{
  echo'<p class="astuce">Vous pouvez aussi consulter les algorithmes de calcul depuis votre menu <a href="?page=consultation_algorithme">['.html(Lang::_("Informations")).'] ['.html(Lang::_("Algorithme de calcul")).']</a>.</p>'.NL;
  echo'<hr />'.NL;
}
?>

