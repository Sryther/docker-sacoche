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
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
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

// boutons radio
$tab_radio_boutons = array();
$tab_notes = array_merge( $_SESSION['NOTE_ACTIF'] , array( 'NN' , 'NE' , 'NF' , 'NR' , 'AB' , 'DI' ) ); // , 'PA' , 'X'
foreach($tab_notes as $note)
{
  $tab_radio_boutons[] = '<label for="note_'.$note.'"><span class="td"><input type="radio" id="note_'.$note.'" name="f_note" value="'.$note.'"> <img alt="'.$note.'" src="'.Html::note_src($note).'" /></span></label>';
}
$radio_boutons = implode(' ',$tab_radio_boutons);

// Dates par défaut
$date_autoeval = date('d/m/Y',mktime(0,0,0,date('m'),date('d')+7,date('Y'))); // 1 semaine après

// Fabrication des éléments select du formulaire
$tab_matieres   = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']) ;
$tab_groupes    = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
$select_matiere = HtmlForm::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ ,    'toutes_matieres' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );
$select_groupe  = HtmlForm::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ , 'tous_regroupements' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var input_visible  = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var input_autoeval = "'.$date_autoeval.'";' );

// Alerte initialisation annuelle non effectuée (test !empty() car un passage par la page d'accueil n'est pas obligatoire)
if(!empty($_SESSION['NB_DEVOIRS_ANTERIEURS']))
{
  echo'<p class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</p><hr />';
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
  <li><span class="astuce">Tenez-vous au courant des demandes <a href="index.php?page=compte_email">en vous abonnant aux notifications</a> ou grace à <a target="_blank" rel="noopener noreferrer" href="<?php echo RSS::url_prof($_SESSION['USER_ID']); ?>"><span class="rss">un flux RSS dédié</span></a> !</span></li>
  <li><span class="astuce"><a title="<?php echo $infobulle ?>" href="#">Nombre de demandes autorisées par matière.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form_prechoix"><fieldset>
  <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
  <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><br />
  <label class="tab" for="f_prof">Destinataire :</label><select id="f_prof" name="f_prof"><option value="<?php echo $_SESSION['USER_ID'] ?>">Demandes qui me concernent (pour traitement).</option><option value="0">Demandes à tous les enseignants (pour information).</option></select><br />
  <span class="tab"></span><input type="hidden" name="f_action" value="Afficher_demandes" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg_prechoix">&nbsp;</label>
</fieldset></form>

<form action="#" method="post" id="form_gestion" class="hide">
  <hr />
  <table class="bilan_synthese" style="float:right;margin-left:1em;margin-right:1ex">
    <thead><tr><th>élève(s) sans demande</th></tr></thead>
    <tbody><tr id="tr_sans"><td class="nu"></td></tr></tbody>
  </table>
  <table id="table_action" class="form hsort t9">
    <thead>
      <tr>
        <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
        <th>Matière</th>
        <th>Item</th>
        <th>Popularité</th>
        <th>Classe / Groupe</th>
        <th>Élève</th>
        <th>Score</th>
        <th>Date</th>
        <th>Destinaire(s)</th>
        <th>Statut</th>
        <th>Messages</th>
        <th>Fichier</th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="11"></td></tr>
    </tbody>
  </table>
  <hr />
  <ul class="puce">
    <li><a id="voir_messages" href="#"><span class="file file_htm">Voir tous les messages à la fois.</span></a></li>
    <li><a id="export_fichier" href=""><span class="file file_txt">Récupérer / Manipuler les informations (fichier <em>csv</em> pour tableur).</span></a></li>
  </ul>
  <hr />
  <div id="zone_actions" class="hide">
    <h2>Avec les demandes cochées :<input type="hidden" id="ids" name="ids" value="" /></h2>
    <fieldset>
      <label class="tab" for="f_quoi">Action :</label><select id="f_quoi" name="f_quoi">
        <option value=""></option>
        <option value="creer">Créer une nouvelle évaluation.</option>
        <option value="completer">Compléter une évaluation existante.</option>
        <option value="saisir">Évaluer à la volée.</option>
        <option value="changer_prof">Changer le statut pour "évaluation en préparation".</option>
        <option value="changer_eleve">Changer le statut pour "demande non traitée".</option>
        <option value="retirer">Retirer de la liste des demandes.</option>
      </select>
    </fieldset>
    <fieldset id="step_qui" class="p hide">
      <label class="tab" for="f_qui">Élève(s) :</label><select id="f_qui" name="f_qui"><option value="select">Élèves sélectionnés</option><option value="groupe"></option></select><input type="hidden" id="f2_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f2_groupe_type" name="f_groupe_type" value="" />
    </fieldset>
    <fieldset id="step_creer" class="p hide">
      <label class="tab" for="f_date">Date du devoir :</label><input id="f_date" name="f_date" size="8" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
      <label class="tab" for="f_date_visible">Date de visibilité :</label><input id="box_date" type="checkbox" checked /> <span>identique</span><span class="hide"><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_date_autoeval">Fin auto-évaluation :</label><input id="box_autoeval" type="checkbox" checked /> <span>sans objet</span><span class="hide"><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="00/00/0000" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_prof_nombre">Partage collègues :</label><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="non" readonly /><input id="f_prof_liste" name="f_prof_liste" type="text" value="" class="invisible" /><q id="choisir_prof" class="choisir_prof" title="Voir ou choisir les collègues."></q><br />
      <label class="tab" for="f_description">Description :</label><input id="f_description" name="f_description" size="30" type="text" value="" />
    </fieldset>
    <fieldset id="step_completer" class="p hide">
      <label class="tab" for="f_devoir">Évaluation :</label><select id="f_devoir" name="f_devoir"><option></option></select><label id="ajax_maj1">&nbsp;</label>
    </fieldset>
    <fieldset id="step_saisir" class="p hide">
      <label class="tab">Note :</label><?php echo $radio_boutons ?>
      <input id="f_saisir_devoir" type="hidden" value="0" />
      <input id="f_saisir_groupe" type="hidden" value="0" />
    </fieldset>
    <fieldset id="step_suite" class="hide">
      <label class="tab" for="f_suite">Suite :</label><select id="f_suite" name="f_suite"><option value="changer">Changer ensuite le statut pour "évaluation en préparation".</option><option value="retirer">Retirer ensuite de la liste des demandes.</option></select>
    </fieldset>
    <fieldset id="step_message" class="hide">
      <label class="tab" for="f_message">Message <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="facultatif" /> :</label><textarea id="f_message" name="f_message" rows="3" cols="75"></textarea><br />
      <span class="tab"></span><label id="f_message_reste"></label>
    </fieldset>
    <p id="step_valider" class="hide">
      <span class="tab"></span><button id="bouton_valider" type="button" class="valider">Valider.</button><label id="ajax_msg_gestion">&nbsp;</label>
    </p>
  </div>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
  <div class="astuce">Résumé des différents niveaux de droits (les plus élevés incluent les plus faibles)&nbsp;:</div>
  <ul class="puce">
    <li>0 &rarr; <span class="select_img droit_x">&nbsp;</span> aucun droit</li>
    <li>1 &rarr; <span class="select_img droit_v">&nbsp;</span> visualiser le devoir (et le dupliquer)</li>
    <li>2 &rarr; <span class="select_img droit_s">&nbsp;</span> co-saisir les notes du devoir</li>
    <li>3 &rarr; <span class="select_img droit_m">&nbsp;</span> modifier les paramètres (élèves, items, &hellip;) <span class="danger">Risqué : à utiliser en connaissance de cause&nbsp;!</span></li>
  </ul>
  <hr />
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion#toggle_evaluations_profs">DOC : Associer des collègues à une évaluation.</a></span>
  <hr />
  <?php echo HtmlForm::afficher_select_collegues( TRUE /*only_profs*/ , array( 1=>'v' , 2=>'s' , 3=>'m' ) ) ?>
  <div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<div id="zone_messages" class="hide"></div>

<div id="bilan" class="hide">
  <hr />
  <ul class="puce">
    <li>Vous pouvez ensuite <a id="bilan_lien" href="./index.php?page=evaluation&amp;section=gestion_selection&amp;devoir_id=0&amp;groupe_id=0">voir l'évaluation correspondante ainsi obtenue</a>.</li>
  </ul>
</div>
