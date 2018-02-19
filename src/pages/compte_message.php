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
$TITRE = html(Lang::_("Messages d'accueil"));

// Fabrication des éléments select du formulaire

$tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;
$select_groupe = HtmlForm::afficher_select($tab_groupes , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );

$select_profil = '<option value="">&nbsp;</option>';
$select_profil.= ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? '<option value="administrateur">Administrateurs</option>' : '' ;
$select_profil.= (in_array($_SESSION['USER_PROFIL_TYPE'],array('administrateur','directeur'))) ? '<option value="directeur">Directeurs</option>' : '' ;
$select_profil.= '<option value="professeur">Professeurs</option>' ;
$select_profil.= '<option value="personnel">Personnels autres</option>' ;
$select_profil.= '<option value="eleve">Élèves</option>' ;
$select_profil.= '<option value="parent">Responsables légaux</option>';

// Javascript
Layout::add( 'js_inline_before' , 'var input_date = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var tab_destinataires = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_msg_contenus  = new Array();' );
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__messages_accueil">DOC : Messages d'accueil.</a></span></li>
</ul>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Date début</th>
      <th>Date fin</th>
      <th>Destinataires</th>
      <th>Contenu</th>
      <th class="nu"><q class="ajouter" title="Ajouter un message."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les messages dont le user est l'auteur
    $DB_TAB = DB_STRUCTURE_MESSAGE::DB_lister_messages_for_user_auteur($_SESSION['USER_ID']);
    if(!empty($DB_TAB))
    {
      Layout::add( 'js_inline_before' , '// <![CDATA[' );
      foreach($DB_TAB as $DB_ROW)
      {
        $date_debut_affich = To::date_mysql_to_french($DB_ROW['message_debut_date']);
        $date_fin_affich   = To::date_mysql_to_french($DB_ROW['message_fin_date']);
        $destinataires_nombre = ($DB_ROW['destinataires_nombre']>1) ? $DB_ROW['destinataires_nombre'].' sélections' : $DB_ROW['destinataires_nombre'].' sélection' ;
        echo'<tr id="id_'.$DB_ROW['message_id'].'">';
        echo  '<td>'.$date_debut_affich.'</td>';
        echo  '<td>'.$date_fin_affich.'</td>';
        echo  '<td>'.$destinataires_nombre.'</td>';
        echo  '<td>'.html(Outil::afficher_texte_tronque($DB_ROW['message_contenu'],60)).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce message."></q>';
        echo    '<q class="supprimer" title="Supprimer ce message."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
        // Javascript
        Layout::add( 'js_inline_before' , 'tab_destinataires['.$DB_ROW['message_id'].']="'.$DB_ROW['message_destinataires'].'";' );
        Layout::add( 'js_inline_before' , 'tab_msg_contenus['.$DB_ROW['message_id'].']="'.convertCRtoJS(html($DB_ROW['message_contenu'])).'";' );
      }
      Layout::add( 'js_inline_before' , '// ]]>' );
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="4"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un message d'accueil</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_debut_date">Date de début :</label><input id="f_debut_date" name="f_debut_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
      <label class="tab" for="f_fin_date">Date de fin :</label><input id="f_fin_date" name="f_fin_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </p>
    <p>
      <label class="tab" for="f_destinataires_nombre">Destinataires :</label><input id="f_destinataires_nombre" name="f_destinataires_nombre" size="13" type="text" value="" readonly /><input id="f_destinataires_liste" name="f_destinataires_liste" type="text" value="" class="invisible" /><q class="choisir_eleve" title="Voir ou choisir les destinataires."></q>
    </p>
    <p>
      <label class="tab" for="f_message_info">Contenu :</label><input id="f_message_info" name="f_message_info" size="45" type="text" value="aucun" readonly /><input id="f_message_longueur" name="f_message_longueur" type="text" value="" class="invisible" /><q class="texte_editer" title="Voir ou modifier le contenu du message."></q><textarea id="f_message_contenu" name="f_message_contenu" class="hide" ></textarea>
    </p>
    <p>
      <label class="tab" for="f_mode_discret">Mode discret :</label><label for="f_mode_discret"><input id="f_mode_discret" name="f_mode_discret" type="checkbox" value="1" /> Cocher pour éviter l'envoi de notifications aux abonnés.</label>
    </p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression du message &laquo;&nbsp;<b id="gestion_delete_identite"></b>&hellip;&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="form_destinataires" class="hide">
  <table><tr>
    <td class="nu" style="width:30em">
      <p>
        <b>Profil :</b><br />
        <select id="f_profil" name="f_profil"><?php echo $select_profil ?></select>
      </p>
      <p id="div_groupe" class="hide">
        <b>Regroupement :</b><br />
        <?php echo $select_groupe ?>
      </p>
      <p id="div_users" class="hide">
        <b>Utilisateur(s) :</b><br />
        <label for="f_all"><input type="radio" id="f_all" name="f_detail" value="all" /> Tous (automatique)</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_indiv"><input type="radio" id="f_indiv" name="f_detail" value="indiv" /> Sélection individuelle</label><br />
        <span id="f_user" class="select_multiple"></span> <span id="span_check" class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span>
      </p>
      <button id="ajouter_destinataires" type="button" class="groupe_ajouter" disabled>Ajouter.</button><br />
      <label id="ajax_msg_destinataires">&nbsp;</label>
    </td>
    <td class="nu" style="width:30em">
      <b>Destinataires :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="f_destinataires" class="select_multiple"></span><br />
      <button id="retirer_destinataires" type="button" class="groupe_retirer" disabled>Retirer.</button>
      <p>
        <button id="valider_destinataires" type="button" class="valider" disabled>Valider ces destinataires</button><br />
        <button id="annuler_destinataires" type="button" class="annuler">Annuler / Retour</button>
      </p>
    </td>
  </tr></table>
</form>

<form action="#" method="post" id="form_message" class="hide">
  <h3>Contenu du message</h3>
  <div>
    <textarea id="f_message" rows="20" cols="80"></textarea><br />
    <span class="tab"></span><label id="f_message_reste"></label><br />
    <span class="tab"></span><button id="valider_message" type="button" class="valider">Valider le contenu</button>&nbsp;&nbsp;&nbsp;<button id="annuler_message" type="button" class="annuler">Annuler / Retour</button>
  </div>
</form>
