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
$TITRE = html(Lang::_("Daltonisme"));

$checked_normal = $_SESSION['USER_DALTONISME'] ? '' : ' checked' ;
$checked_dalton = $_SESSION['USER_DALTONISME'] ? ' checked' : '' ;

// codes de notation
$td_normal = '<td class="nu">&nbsp;</td>';
$td_dalton = '<td class="nu">&nbsp;</td>';
$numero = 0;
foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
{
  $numero++;
  $td_normal .= '<td>note '.html($_SESSION['NOTE'][$note_id]['SIGLE']).'<br /><img alt="'.html($_SESSION['NOTE'][$note_id]['SIGLE']).'" src="'.Html::note_src_couleur($_SESSION['NOTE'][$note_id]['IMAGE']).'" /></td>';
  $td_dalton .= '<td>note '.html($_SESSION['NOTE'][$note_id]['SIGLE']).'<br /><img alt="'.html($_SESSION['NOTE'][$note_id]['SIGLE']).'" src="'.Html::note_src_daltonisme($numero).'" /></td>';
}

// couleurs des états d'acquisition
$td_normal .= '<td class="nu">&nbsp;</td>';
$td_dalton .= '<td class="nu">&nbsp;</td>';
foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
{
  $td_normal .= '<td style="background-color:'.$tab_acquis_info['COULEUR'].'">acquisition<br />'.html($tab_acquis_info['SIGLE']).'</td>';
  $td_dalton .= '<td style="background-color:'.$tab_acquis_info['GRIS'   ].'">acquisition<br />'.html($tab_acquis_info['SIGLE']).'</td>';
}
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__daltonisme">DOC : Daltonisme</a></span></div>

<hr />

<form action="#" method="post" id="form_notes">
  <table class="simulation">
    <thead>
      <tr>
        <th class="nu"></th>
        <th class="nu"></th>
        <th colspan="<?php echo $_SESSION['NOMBRE_CODES_NOTATION'] ?>"><?php echo html(Lang::_("Notes aux évaluations")) ?></th>
        <th class="nu"></th>
        <th colspan="<?php echo $_SESSION['NOMBRE_ETATS_ACQUISITION'] ?>"><?php echo html(Lang::_("Degrés d'acquisitions")) ?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="<?php echo (3+$_SESSION['NOMBRE_CODES_NOTATION']+$_SESSION['NOMBRE_ETATS_ACQUISITION']); ?>" class="nu" style="font-size:50%"></td>
      </tr>
      <tr>
        <th><label for="note_normal"><?php echo html(Lang::_("Conventions dans l'établissement")) ?></label><br /><input type="radio" id="note_normal" name="daltonisme" value="0"<?php echo $checked_normal ?> /></th>
        <?php echo $td_normal ?>
      </tr>
      <tr>
        <td colspan="<?php echo (7+$_SESSION['NOMBRE_CODES_NOTATION']+$_SESSION['NOMBRE_ETATS_ACQUISITION']); ?>" class="nu" style="font-size:50%"></td>
      </tr>
      <tr>
        <th><label for="note_dalton"><?php echo html(Lang::_("Conventions en remplacement")) ?></label><br /><input type="radio" id="note_dalton" name="daltonisme" value="1"<?php echo $checked_dalton ?> /></th>
        <?php echo $td_dalton ?>
      </tr>
    </tbody>
  </table>
  <fieldset><p><span class="tab"></span><button id="bouton_valider" type="submit" class="parametre">Enregistrer ce choix.</button><label id="ajax_msg">&nbsp;</label></p></fieldset>
</form>
