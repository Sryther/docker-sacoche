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
$TITRE = html(Lang::_("Recherche ciblée"));

// Fabrication des éléments select du formulaire

$select_critere_seuil_maitrise = '';
$tab_options = array( 1=>'Maîtrise insuffisante' , 2=>'Maîtrise fragile' , 3=>'Maîtrise satisfaisante' , 4=>'Très bonne maîtrise' );
foreach($tab_options as $val => $txt)
{
  $class   = ($val==1) ? ' class="check"' : '' ;
  $checked = ($val==1) ? ' checked'       : '' ;
  $select_critere_seuil_maitrise .= '<label for="f_critere_seuil_maitrise_'.$val.'"'.$class.'><input type="checkbox" name="f_critere_seuil_maitrise[]" id="f_critere_seuil_maitrise_'.$val.'" value="'.$val.'"'.$checked.' /> '.html($txt).'</label>';
}

$select_critere_seuil_acquis = '';
foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
{
  $class   = ($tab_acquis_info['SEUIL_MIN']==0) ? ' class="check"' : '' ;
  $checked = ($tab_acquis_info['SEUIL_MIN']==0) ? ' checked'       : '' ;
  $select_critere_seuil_acquis .= '<label for="f_critere_seuil_acquis_'.$acquis_id.'"'.$class.'><input type="checkbox" name="f_critere_seuil_acquis[]" id="f_critere_seuil_acquis_'.$acquis_id.'" value="'.$acquis_id.'"'.$checked.' /> '.html($tab_acquis_info['LEGENDE']).'</label>';
}

$tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;

$select_groupe          = HtmlForm::afficher_select($tab_groupes                                                      , 'f_groupe'            /*select_nom*/ ,    '' /*option_first*/ , FALSE /*selection*/ ,   'regroupements' /*optgroup*/ );
$select_critere_objet   = HtmlForm::afficher_select(Form::$tab_select_recherche_objet                                 , 'f_critere_objet'     /*select_nom*/ ,    '' /*option_first*/ , FALSE /*selection*/ , 'objet_recherche' /*optgroup*/ );
$select_domaines        = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_socle2016_domaines()                  , 'f_select_domaine'    /*select_nom*/ ,    '' /*option_first*/ , FALSE /*selection*/ ,          'cycles' /*optgroup*/ );
$select_composantes     = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_socle2016_composantes()               , 'f_select_composante' /*select_nom*/ ,    '' /*option_first*/ , FALSE /*selection*/ ,        'domaines' /*optgroup*/ );
$select_selection_items = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , 'f_selection_items'   /*select_nom*/ ,    '' /*option_first*/ , FALSE /*selection*/ ,                '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var max_etats_acquis  = '.($_SESSION['NOMBRE_ETATS_ACQUISITION']-1).';' );

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_recherche">DOC : Recherche ciblée.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p><label class="tab" for="f_groupe">Élèves :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /></p>
  <label class="tab" for="f_critere_objet">Critère observé :</label><?php echo $select_critere_objet ?><br />
  <span id="span_matiere_items" class="hide">
    <label class="tab">Item(s) matière(s) :</label><input id="f_matiere_items_nombre" name="f_matiere_items_nombre" size="10" type="text" value="" readonly /><input id="f_matiere_items_liste" name="f_matiere_items_liste" type="text" value="" class="invisible" /><q class="choisir_compet" title="Voir ou choisir les items."></q><br />
  </span>
  <span id="span_domaine_maitrise" class="hide">
    <label class="tab" for="f_select_domaine">Domaine :</label><?php echo $select_domaines ?><br />
  </span>
  <span id="span_composante_maitrise" class="hide">
    <label class="tab" for="f_select_composante">Composante :</label><?php echo $select_composantes ?><br />
  </span>
  <div id="div_matiere_items_bilanMS" class="hide">
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La question se pose notamment dans le cas d'items issus de référentiels de plusieurs matières." /> Coefficients :</label><label for="f_with_coef"><input type="checkbox" id="f_with_coef" name="f_with_coef" value="1" checked /> Prise en compte des coefficients</label><br />
  </div>
  <span id="span_maitrise" class="hide">
    <label class="tab" for="f_critere_seuil_maitrise">Degré(s) :</label><span id="f_critere_seuil_maitrise" class="select_multiple"><?php echo $select_critere_seuil_maitrise ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
  </span>
  <span id="span_acquisition" class="hide">
    <label class="tab" for="f_critere_seuil_acquis">État(s) :</label><span id="f_critere_seuil_acquis" class="select_multiple"><?php echo $select_critere_seuil_acquis ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
  </span>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="rechercher">Rechercher.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <div id="arborescence"><label class="loader">Chargement&hellip;</label></div>
  <p><span class="tab"></span><button id="valider_matieres_items" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_matieres_items" type="button" class="annuler">Annuler / Retour</button></p>
  <hr />
  <p>
    <label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
    <label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
  </p>
</form>

<div id="bilan"></div>
