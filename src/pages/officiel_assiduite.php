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
$TITRE = html(Lang::_("Absences / Retards"));

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :<div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Formulaire de choix d'une période (utilisé deux fois)
// Formulaire des classes
if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || ($_SESSION['USER_JOIN_GROUPES']=='all') ) // Ce dernier test laisse par exemple passer les directeurs et les CPE, ces derniers ayant un 'USER_PROFIL_TYPE' à 'professeur'.
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);
}
else // Ne passent ici que les professeurs
{
  $tab_groupes = (Outil::test_droit_specifique_restreint($_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'],'ONLY_PP')) ? DB_STRUCTURE_COMMUN::DB_OPT_classes_prof_principal($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_professeur($_SESSION['USER_ID']) ;
}

$select_periode = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() ,      FALSE /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
$select_groupe  = HtmlForm::afficher_select($tab_groupes                                 , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql = "'.TODAY_MYSQL.'";' );
// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__assiduite">DOC : Absences &amp; Retards</a></span></div>

<hr />

<h2>Import de fichier</h2>
<form action="#" method="post" id="form_fichier">
  <p>
    <label class="tab" for="f_periode_import">Période :</label><select id="f_periode_import" name="f_periode_import"><?php echo $select_periode ?></select><br />
    <label class="tab" for="f_choix_principal">Origine :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value="">&nbsp;</option>
      <option value="import_siecle">Siècle Vie Scolaire</option>
      <option value="import_sconet">Sconet Absences</option>
      <option value="import_gepi">GEPI Absences 2</option>
      <option value="import_pronote">Pronote</option>
      <option value="import_moliere">Molière</option>
      <option value="import_entlibre">ENT libre du 77</option>
      <option value="import_entelyco">ENT e-lyco</option>
    </select>
    <input id="f_import" type="file" name="userfile" />
    <input type="hidden" id="f_upload_action" name="f_action" value="" />
    <input type="hidden" id="f_upload_periode" name="f_periode" value="" />
  </p>
  <ul class="puce hide" id="puce_import_sconet">
    <li><span class="danger">Le ministère a remplacé <em>Sconet Absences</em> par <em>Siècle Vie Scolaire</em> à la rentrée 2014.</span></li>
    <li>Indiquer le fichier <em>SIECLE_exportAbsence.xml</em> : <button type="button" id="import_sconet" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_sconet">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_siecle">
    <li><span class="astuce"><em>Siècle Vie Scolaire</em> permet un export des absences et des retards à compter de sa version 15.1 de février 2015.</span></li>
    <li>Indiquer le fichier <em>eleves_JJMMAAAA_HHhMM.xml</em> : <button type="button" id="import_siecle" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_siecle">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_gepi">
    <li>Indiquer le fichier <em>extraction_abs_plus_*.csv</em> : <button type="button" id="import_gepi" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_gepi">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_pronote">
    <li>Indiquer le fichier <em>EXP_AbsencesEleves.xml</em> ou <em>EXP_Retards.xml</em> : <button type="button" id="import_pronote" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_pronote">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_moliere">
    <li>Indiquer le fichier <em>Export.txt</em> : <button type="button" id="import_moliere" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_moliere">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_entlibre">
    <li>Indiquer le fichier <em>tableau de bord.csv</em> : <button type="button" id="import_entlibre" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_entlibre">&nbsp;</label></li>
  </ul>
  <ul class="puce hide" id="puce_import_entelyco">
    <li>Indiquer le fichier <em>STATS.csv</em> : <button type="button" id="import_entelyco" class="fichier_import">Parcourir...</button><label id="ajax_msg_import_entelyco">&nbsp;</label></li>
  </ul>
</form>

<hr />

<h2>Saisie / Modification manuelle</h2>
<form action="#" method="post" id="form_manuel">
  <p>
    <label class="tab" for="f_groupe">Classe :</label><?php echo $select_groupe ?><br />
    <label class="tab" for="f_periode">Période :</label><select id="f_periode" name="f_periode" class="hide"><?php echo $select_periode ?></select><br />
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="afficher_formulaire_manuel" /><button id="valider_manuel" type="submit" class="modifier">Saisir.</button><label id="ajax_msg_manuel">&nbsp;</label>
  </p>
</form>

<hr />

<div id="zone_confirmer" class="hide">
  <h2>Confirmation d'import</h2>
  <div class="hide" id="comfirm_import_sconet">
    <p class="astuce">Ce fichier, généré le <b id="sconet_date_export"></b>, comporte les données de la période <b id="sconet_libelle"></b>, allant du <b id="sconet_date_debut"></b> au <b id="sconet_date_fin"></b>.</p>
  </div>
  <div class="hide" id="comfirm_import_siecle">
  </div>
  <div class="hide" id="comfirm_import_gepi">
    <p class="astuce">Ce fichier comporte les données de <b id="gepi_eleves_nb"></b> élève(s).</p>
  </div>
  <div class="hide" id="comfirm_import_pronote">
    <p class="astuce">Ce fichier comporte les <b id="pronote_objet_1"></b> de <b id="pronote_eleves_nb"></b> élève(s) entre le <b id="pronote_date_debut"></b> et le <b id="pronote_date_fin"></b></p>
    <p class="danger">Pronote n'exportant pas les élèves sans saisie, on forcera <b id="pronote_objet_2"></b> pour tous les élèves absents du fichier.</p>
  </div>
  <div class="hide" id="comfirm_import_moliere">
    <p class="astuce">Ce fichier comporte les données de <b id="moliere_eleves_nb"></b> élève(s).</p>
  </div>
  <div class="hide" id="comfirm_import_entlibre">
    <p class="astuce">Ce fichier comporte les données de <b id="entlibre_eleves_nb"></b> élève(s).</p>
  </div>
  <div class="hide" id="comfirm_import_entelyco">
    <p class="astuce">Ce fichier comporte les données de <b id="entelyco_eleves_nb"></b> élève(s) entre le <b id="entelyco_date_debut"></b> et le <b id="entelyco_date_fin"></b></p>
  </div>
  <p>Confirmez-vous vouloir importer ces données dans <em>SACoche</em> pour la période <b id="periode_import"></b> ?</p>
  <form action="#" method="post">
    <p class="ml">
      <button id="confirmer_import" type="button" class="valider">Confirmer.</button> <button id="fermer_zone_confirmer" type="button" class="annuler">Annuler.</button><label id="ajax_msg_confirm">&nbsp;</label>
    </p>
  </form>
</div>

<div id="zone_saisir" class="hide">
  <h2>Saisie des absences et retards | Résultat du traitement</h2>
  <p>
    <b id="titre_saisir"></b>
  </p>
  <table id="table_saisir" class="bilan">
    <thead><tr><th>Élève</th><th>Absences<br />nb &frac12; journées</th><th>dont &frac12; journées<br />non justifiées</th><th>Nb retards</th><th>dont retards<br />non justifiés</th></tr></thead>
    <tbody><tr><td colspan="5"></td></tr></tbody>
  </table>
  <form action="#" method="post">
    <p class="ml">
      <button id="Enregistrer_saisies" type="button" class="valider">Enregistrer les saisies</button> <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button><label id="ajax_msg_saisir"></label>
    </p>
  </form>
</div>
