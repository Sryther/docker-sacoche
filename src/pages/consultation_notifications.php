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
$TITRE = html(Lang::_("Notifications reçues"));

$menu = ($_SESSION['USER_PROFIL_TYPE']!='administrateur') ? '['.html(Lang::_("Paramétrages")).']' : '['.html(Lang::_("Paramétrages personnels")).']' ;
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__email_notifications">DOC : Adresse e-mail / Abonnements / Notifications</a></span></p>

<p>
  <span class="astuce">Pour gérer ses abonnements aux notifications, utiliser le menu <a href="./index.php?page=compte_email"><?php echo $menu ?> [<?php echo html(Lang::_("Adresse e-mail & Abonnements")) ?>]</a>.</span><br />
  <span class="astuce"><?php echo html(Lang::_("Les notifications consultées sont automatiquement retirées passé un délai de 2 mois.")) ?></span>
</p>

<hr />

<table id="table_notifications" class="form hsort">
  <thead>
    <tr>
      <th>Date</th>
      <th>Statut</th>
      <th>Objet</th>
      <th>Contenu</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Javascript
    Layout::add( 'js_inline_before' , 'var tab_notif_contenu  = new Array();' );
    // Lister les notifications qu'un utilisateur peut consulter
    $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_notifications_consultables_for_user( $_SESSION['USER_ID'] );
    if(!empty($DB_TAB))
    {
      Layout::add( 'js_inline_before' , '// <![CDATA[' );
      foreach($DB_TAB as $DB_ROW)
      {
        $class = ($DB_ROW['notification_statut']=='consultable') ? ' class="new"' : '' ;
        $datetime_affich = To::datetime_mysql_to_french($DB_ROW['notification_date']);
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['notification_id'].'"'.$class.'>';
        echo  '<td>'.$datetime_affich.'</td>';
        echo  '<td>'.$DB_ROW['notification_statut'].'</td>';
        echo  '<td>'.$DB_ROW['abonnement_objet'].'</td>';
        echo  '<td class="i">'.html(Outil::afficher_texte_tronque($DB_ROW['notification_contenu'],60)).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="voir" title="Consulter la notification complète."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
        // Javascript
        Layout::add( 'js_inline_before' , 'tab_notif_contenu['.$DB_ROW['notification_id'].']="'.convertCRtoJS(html($DB_ROW['notification_contenu'])).'";' );
      }
      Layout::add( 'js_inline_before' , '// ]]>' );
    }
    else
    {
      echo'<tr><td colspan="4">Aucune notification actuellement enregistrée.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>
<p>&nbsp;</p>

<div id="div_notification" class="hide">
  <h3>Notification du <span id="report_date"></span></h3>
  <textarea id="textarea_notification" rows="25" cols="100"></textarea><br />
  <label id="ajax_save">&nbsp;</label>
</form>
