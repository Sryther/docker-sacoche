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
$TITRE = html(Lang::_("Adresse e-mail & Abonnements"));

$info_origine = '';
$info_edition = '';
$disabled = '';

if( $_SESSION['USER_EMAIL'] && $_SESSION['USER_EMAIL_ORIGINE'] )
{
  if($_SESSION['USER_EMAIL_ORIGINE']=='user')
  {
    $info_origine = '<span class="astuce">'.html(Lang::_("L'adresse enregistrée a été saisie par vous-même.")).'</span>';
  }
  else
  {
    $info_origine = '<span class="astuce">'.html(Lang::_("L'adresse enregistrée a été importée ou saisie par un administrateur.")).'</span>';
    if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
    {
      $info_edition = '<span class="astuce">'.html(Lang::_("Vous êtes habilité à modifier cette adresse si vous le souhaitez.")).'</span>';
    }
    else
    {
      $info_edition = '<span class="danger">'.html(Lang::_("Vous n'êtes pas habilité à modifier l'adresse vous-même ! Veuillez contacter un administrateur.")).'</span>';
      $disabled = ' disabled';
    }
  }
}
else
{
  $info_origine = '<span class="astuce">'.html(Lang::_("Il n'y a pas d'adresse actuellement enregistrée.")).'</span>';
}

if(COURRIEL_NOTIFICATION=='non')
{
  $info_envoi_notifications = '<label class="alerte">'.html(Lang::_("Le webmestre du serveur a désactivé l'envoi des notifications par courriel.")).'</label>' ;
}
elseif(!$_SESSION['USER_EMAIL'])
{
  $info_envoi_notifications = '<label class="alerte">'.html(Lang::_("Les envois par courriel seront remplacés par des indications en page d'accueil tant que votre adresse de courriel ne sera pas renseignée.")).'</label>' ;
}
else
{
  $info_envoi_notifications = '<label class="valide">'.html(Lang::_("Votre adresse étant renseignée, vous pouvez opter pour des envois par courriel.")).'</label>' ;
}

// Javascript
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__email_notifications">DOC : Adresse e-mail / Abonnements / Notifications</a></span></p>

<p class="astuce">Les adresses e-mail ne sont utilisées que par l'application et ne sont pas visibles des autres utilisateurs à l'exception des administrateurs.</p>

<hr />

<h2><?php echo html(Lang::_("Adresse associée à votre compte")) ?></h2>

<p id="info_adresse">
  <?php echo $info_origine ?><br />
  <?php echo $info_edition ?>
</p>
<form id="form_courriel" action="#" method="post"><fieldset>
  <p><label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="<?php echo html($_SESSION['USER_EMAIL']); ?>" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" /></p>
  <p><span class="tab"></span><input name="f_action" type="hidden" value="courriel" /><button id="bouton_valider" type="submit" class="mdp_perso"<?php echo $disabled ?>>Valider.</button><label id="ajax_msg_courriel">&nbsp;</label></p>
</fieldset></form>

<hr />

<h2><?php echo html(Lang::_("Abonnement aux notifications")) ?> (profil <?php echo html($_SESSION['USER_PROFIL_TYPE']); ?>)</h2>

<p id="info_abonnement_mail">
  <?php echo $info_envoi_notifications ?>
</p>

<form id="form_abonnements" action="#" method="post">
  <table id="table_abonnements" class="form">
    <thead>
      <tr>
        <th class="hc">Description</th>
        <th class="hc">non merci</th>
        <th class="hc">indication<br />en page d'accueil</th>
        <th class="hc">envoi<br />par courriel</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $tab_choix = array( 'non' , 'accueil' , 'courriel' );
      $DB_TAB  = DB_STRUCTURE_NOTIFICATION::DB_lister_abonnements_profil( $_SESSION['USER_PROFIL_TYPE'] , $_SESSION['USER_ID'] );
      $DB_JOIN = DB_STRUCTURE_NOTIFICATION::DB_lister_abonnements_user( $_SESSION['USER_ID'] );
      foreach($DB_TAB as $DB_ROW)
      {
        $DB_ROW['jointure_mode'] = isset($DB_JOIN[$DB_ROW['abonnement_ref']]) ? $DB_JOIN[$DB_ROW['abonnement_ref']]['jointure_mode'] : 'non' ;
        if( ($DB_ROW['abonnement_ref']=='bilan_officiel_visible') && ($_SESSION['USER_PROFIL_TYPE']=='parent') )
        {
          $DB_ROW['abonnement_descriptif'] .= ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La direction peut forcer l\'envoi de cette information par courriel aux familles.<br />Dans ce cas cela surcharge votre paramétrage." />' ;
        }
        echo'<tr><td>'.$DB_ROW['abonnement_descriptif'].'</td>';
        foreach($tab_choix as $radio_key)
        {
          $checked  = ($radio_key==$DB_ROW['jointure_mode']) ? ' checked' : '' ;
          if($radio_key=='non')
          {
            $disabled = ($DB_ROW['abonnement_obligatoire']) ? ' disabled' : '' ;
          }
          else if($radio_key=='accueil')
          {
            $disabled = ($DB_ROW['abonnement_courriel_only']) ? ' disabled' : '' ;
          }
          else
          {
            $disabled = '' ;
          }
          echo'<td class="hc"><input type="radio" name="'.$DB_ROW['abonnement_ref'].'" id="'.$DB_ROW['abonnement_ref'].'X'.$radio_key.'" value="'.$radio_key.'"'.$checked.$disabled.' /></td>';
        }
        echo'</tr>';
      }
      ?>
    </tbody>
  </table>
  <p>
    <span class="tab"></span><button id="bouton_abonner" type="button" class="parametre">Valider.</button><label id="ajax_msg_abonnements">&nbsp;</label>
  </p>
</form>

<hr />

<p class="astuce">
  Les notifications archivées sont accessibles par le menu <a href="./index.php?page=consultation_notifications">[<?php echo html(Lang::_("Informations")) ?>] [<?php echo html(Lang::_("Notifications reçues")) ?>]</a>.
</p>
