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
$TITRE = "Statistiques d'utilisation"; // Pas de traduction car pas de choix de langue pour ce profil.

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__statistiques">DOC : Statistiques d'utilisation.</a></span></p>

<hr />

<?php if(HEBERGEUR_INSTALLATION=='mono-structure'): /* * * * * * MONO-STRUCTURE DEBUT * * * * * */ ?>

<?php
list($personnel_nb , $eleve_nb , $personnel_use , $eleve_use , $evaluation_nb , $officiel_sacoche_nb , $officiel_livret_nb , $evaluation_use , $officiel_sacoche_use , $officiel_livret_use)
  = DB_STRUCTURE_WEBMESTRE::DB_recuperer_statistiques( TRUE /*info_user_nb*/ , TRUE /*info_user_use*/ , TRUE /*info_action_nb*/ , TRUE /*info_action_use*/ , FALSE /*info_connexion*/ );
?>

<ul class="puce">
  <li>Il y a <b><?php echo number_format($personnel_nb       ,0,'',' ') ?> personnel(s)</b>             enregistré(s), dont <b><?php echo number_format($personnel_use ,0,'',' ') ?></b> personnel(s) connecté(s).</li>
  <li>Il y a <b><?php echo number_format($eleve_nb           ,0,'',' ') ?> élève(s)</b>                 enregistré(s), dont <b><?php echo number_format($eleve_use     ,0,'',' ') ?></b> élève(s) connecté(s).</li>
  <li>Il y a <b><?php echo number_format($evaluation_nb      ,0,'',' ') ?> saisie(s)</b> de notes      enregistrée(s), dont <b><?php echo number_format($evaluation_use,0,'',' ') ?></b> récemment.</li>
  <li>Il y a <b><?php echo number_format($officiel_sacoche_nb,0,'',' ') ?> bilan(s) officiels SACoche</b>  archivé(s), dont <b><?php echo number_format($officiel_sacoche_use,0,'',' ') ?></b> récemment.</li>
  <li>Il y a <b><?php echo number_format($officiel_livret_nb ,0,'',' ') ?> bilan(s) de Livret Scolaire</b> archivé(s), dont <b><?php echo number_format($officiel_livret_use,0,'',' ') ?></b> récemment.</li>
</ul>
<hr />
<p id="expli">
  <span class="astuce">Les anciens utilisateurs encore dans la base ne sont pas comptés parmi les <b>utilisateurs enregistrés</b>.</span><br />
  <span class="astuce">Les <b>utilisateurs connectés</b> sont ceux s'étant identifiés au cours du dernier semestre.</span><br />
  <span class="astuce">Les évaluations ou bilans <b>récents</b> sont ceux du dernier semestre.</span>
</p>

<?php endif /* * * * * * MONO-STRUCTURE FIN * * * * * */ ?>

<?php if(HEBERGEUR_INSTALLATION=='multi-structures'): /* * * * * * MULTI-STRUCTURES DEBUT * * * * * */ ?>

<?php
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : FALSE ; // demande de stats depuis structure_multi.php
$select_structure = HtmlForm::afficher_select( DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , 'f_base' /*select_nom*/ , FALSE /*option_first*/ , $selection , 'zones_geo' /*optgroup*/ , TRUE /*multiple*/ );
?>

<form action="#" method="post" id="form_stats"><fieldset>
  <label class="tab" for="f_base">Structure(s) :</label><span id="f_base" class="select_multiple"><?php echo $select_structure ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
  <span class="tab"></span><input type="hidden" id="f_action" name="f_action" value="calculer" /><input type="hidden" id="f_listing_id" name="f_listing_id" value="" /><button id="bouton_valider" type="button" class="stats">Calculer les statistiques.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<div id="ajax_info" class="hide">
  <h2>Calcul des statistiques en cours</h2>
  <label id="ajax_msg1"></label>
  <ul class="puce"><li id="ajax_msg2"></li></ul>
  <span id="ajax_num" class="hide"></span>
  <span id="ajax_max" class="hide"></span>
</div>

<p>&nbsp;</p>

<form action="#" method="post" id="structures" class="hide">
  <hr />
  <table class="form t9 hsort" id="table_action">
    <thead>
      <tr>
        <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></th>
        <th>Id</th>
        <th>géographie</th>
        <th>UAI</th>
        <th>structure</th>
        <th>contact</th>
        <th>ancienneté<br />inscription</th>
        <th>dernière<br />connexion</th>
        <th>personnels<br />enregistrés</th>
        <th>personnels<br />connectés</th>
        <th>élèves<br />enregistrés</th>
        <th>élèves<br />connectés</th>
        <th>evaluations<br />enregistrées</th>
        <th>evaluations<br />récentes</th>
        <th>bilans SACoche<br />enregistrés</th>
        <th>bilans SACoche<br />récents</th>
        <th>bilans LSU<br />enregistrés</th>
        <th>bilans LSU<br />récents</th>
        <th>mode de<br />connexion</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td class="nu" colspan="19"></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <td class="nu" colspan="19"></td>
      </tr>
    </tbody>
  </table>
  <p id="zone_actions">
    Pour les structures cochées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
    <button id="bouton_newsletter" type="button" class="mail_ecrire">Écrire un courriel.</button>
    <button id="bouton_transfert" type="button" class="fichier_export">Exporter données &amp; bases.</button>
    <button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
    <label id="ajax_supprimer">&nbsp;</label>
  </p>
  <div class="astuce">Les anciens utilisateurs encore dans la base ne sont pas comptés parmi les <b>utilisateurs enregistrés</b>.</div>
  <div class="astuce">Les <b>utilisateurs connectés</b> sont ceux s'étant identifiés au cours du dernier semestre.</div>
  <div class="astuce">Les évaluations ou bilans <b>récents</b> sont ceux effectués au cours du dernier semestre.</div>
</form>

<?php endif /* * * * * * MULTI-STRUCTURES FIN * * * * * */ ?>
