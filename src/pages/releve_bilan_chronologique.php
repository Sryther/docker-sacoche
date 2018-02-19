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
$TITRE = html(Lang::_("Bilan chronologique"));

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && (!$_SESSION['NB_ENFANTS']) )
{
  echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  if( !Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION']) )
  {
    echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
    echo'<div class="astuce">En effet, les administrateurs n\'ont pas autorisé que vous accédiez aux états d\'acquisitions&hellip;</div>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  if( !Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE']) && !Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) )
  {
    echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
    echo'<div class="astuce">En effet, les administrateurs n\'ont pas autorisé que vous accédiez aux moyennes des scores ni aux pourcentages d\'items acquis&hellip;</div>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_synthese_predefini = (Form::$tab_choix['mode_synthese']=='predefini')       ? ' checked' : '' ;
$check_synthese_domaine   = (Form::$tab_choix['mode_synthese']=='domaine')         ? ' checked' : '' ;
$check_synthese_theme     = (Form::$tab_choix['mode_synthese']=='theme')           ? ' checked' : '' ;
$check_fusion_niveaux     = (Form::$tab_choix['fusion_niveaux'])                   ? ' checked' : '' ;
$check_moyenne_scores     = (Form::$tab_choix['indicateur']=='moyenne_scores')     ? ' checked' : '' ;
$check_pourcentage_acquis = (Form::$tab_choix['indicateur']=='pourcentage_acquis') ? ' checked' : '' ;
$check_conversion_sur_20  = (Form::$tab_choix['conversion_sur_20'])                ? ' checked' : '' ;
$class_conversion_sur_20  = ($check_moyenne_scores || $check_pourcentage_acquis)   ? 'show' : 'hide' ;
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_moyenne_scores     = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? $check_moyenne_scores     : '' ;
  $check_pourcentage_acquis = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? $check_pourcentage_acquis : '' ;
  $check_conversion_sur_20  = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? $check_conversion_sur_20  : '' ;
  $class_conversion_sur_20  = ($check_moyenne_scores || $check_pourcentage_acquis)                     ? 'show' : 'hide' ;
  $moyenne_scores     = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? '<label for="f_indicateur_MS"><input type="radio" id="f_indicateur_MS" name="f_indicateur" value="moyenne_scores"'.$check_moyenne_scores.' /> Moyenne des scores</label>'                                                     : '<del>Moyenne des scores</del>' ;
  $pourcentage_acquis = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? '<label for="f_indicateur_PA"><input type="radio" id="f_indicateur_PA" name="f_indicateur" value="pourcentage_acquis"'.$check_pourcentage_acquis.' /> Pourcentage d\'items acquis</label>'                                    : '<del>Pourcentage d\'items acquis</del>' ;
  $conversion_sur_20  = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? '<label for="f_conversion_sur_20" class="'.$class_conversion_sur_20.'"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"'.$check_conversion_sur_20.' /> Conversion en note sur 20</label>' : '<del>Conversion en note sur 20</del>' ;
}
else
{
  $moyenne_scores     = '<label for="f_indicateur_MS"><input type="radio" id="f_indicateur_MS" name="f_indicateur" value="moyenne_scores"'.$check_moyenne_scores.' /> Moyenne des scores</label>';
  $pourcentage_acquis = '<label for="f_indicateur_PA"><input type="radio" id="f_indicateur_PA" name="f_indicateur" value="pourcentage_acquis"'.$check_pourcentage_acquis.' /> Pourcentage d\'items acquis</label>';
  $conversion_sur_20  = '<label for="f_conversion_sur_20" class="'.$class_conversion_sur_20.'"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"'.$check_conversion_sur_20.' /> Conversion en note sur 20</label>';
}
$check_retroactif_auto    = (Form::$tab_choix['retroactif']=='auto')   ? ' checked' : '' ;
$check_retroactif_non     = (Form::$tab_choix['retroactif']=='non')    ? ' checked' : '' ;
$check_retroactif_oui     = (Form::$tab_choix['retroactif']=='oui')    ? ' checked' : '' ;
$check_retroactif_annuel  = (Form::$tab_choix['retroactif']=='annuel') ? ' checked' : '' ;
$check_only_socle         = (Form::$tab_choix['only_socle'])           ? ' checked' : '' ;
$bouton_modifier_matiere  = '';
$bouton_modifier_matieres = '';
$separateur_check_matieres = '<br />';

if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $objet_selection = '';
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'show';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $objet_selection = '';
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'show';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
  $bouton_modifier_matiere  =       '&nbsp;<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
  $bouton_modifier_matieres = '<br />&nbsp;<button id="modifier_matieres" type="button" class="form_ajouter">&plusmn;</button>';
  $separateur_check_matieres = '';

}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>1) )
{
  $objet_selection = ' disabled';
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'hide';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $objet_selection = ' disabled';
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_navig_eleve = 'hide';
  $select_eleve = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $objet_selection = ' disabled';
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_navig_eleve = 'hide';
  $select_eleve = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}

$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$tab_select_objet_releve = array(
    array('valeur' => 'matieres'         , 'texte' => "matières") ,
    array('valeur' => 'matiere_niveau'   , 'texte' => "niveaux d'une matière") ,
    array('valeur' => 'matiere_synthese' , 'texte' => "synthèses d'une matière") ,
    array('valeur' => 'selection'        , 'texte' => "items sélectionnés") ,
);

$select_objet_releve = HtmlForm::afficher_select($tab_select_objet_releve       , 'f_objet'        /*select_nom*/ ,                      '' /*option_first*/ , FALSE                            /*selection*/ ,              '' /*optgroup*/ );
$select_groupe       = HtmlForm::afficher_select($tab_groupes                   , 'f_groupe'       /*select_nom*/ ,                   $of_g /*option_first*/ , $sel_g                           /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleves_ordre = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre , 'f_eleves_ordre' /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre'] /*selection*/ ,              '' /*optgroup*/ );
$select_matieres     = HtmlForm::afficher_select($tab_matieres                  , 'f_matieres'     /*select_nom*/ ,                   FALSE /*option_first*/ , TRUE                             /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/);
$select_matiere      = HtmlForm::afficher_select($tab_matieres                  , 'f_matiere'      /*select_nom*/ ,                      '' /*option_first*/ , Form::$tab_choix['matiere_id']   /*selection*/ ,              '' /*optgroup*/ );
$select_periode      = HtmlForm::afficher_select($tab_periodes                  , 'f_periode'      /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                            /*selection*/ ,              '' /*optgroup*/ );
$select_echelle      = HtmlForm::afficher_select(Form::$tab_echelle             , 'f_echelle'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['echelle']      /*selection*/ ,              '' /*optgroup*/ );

$select_selection_items = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , 'f_selection_items' , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql = "'.TODAY_MYSQL.'";' );
// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div id="zone_preliminaire">
  <div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__bilan_chronologique">DOC : Bilan chronologique.</a></span></div>
  <div class="astuce">Un administrateur / directeur / professeur doit indiquer le type de synthèse adapté suivant chaque référentiel (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_type_synthese">DOC</a></span>).</div>
  <?php
  // Avertissement mode de synthèse non configuré ou configuré sans synthèse
  $tab_mode = array(
    'inconnu' => 'dont le format de synthèse est inconnu',
    'sans'    => 'volontairement définis sans format de synthèse',
  );
  $is_alerte = FALSE;
  foreach($tab_mode as $mode => $explication)
  {
    $nb = DB_STRUCTURE_BILAN::DB_compter_modes_synthese($mode);
    if($nb)
    {
      $is_alerte = TRUE;
      $s = ($nb>1) ? 's' : '' ;
      echo'<label class="alerte">Il y a '.$nb.' référentiel'.$s.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace('§BR§','<br />',html(html(DB_STRUCTURE_BILAN::DB_recuperer_modes_synthese($mode)))).'" /> '.$explication.' (donc non pris en compte).</label>'.NL; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    }
  }
  if(!$is_alerte)
  {
    echo'<label class="valide">Tous les référentiels ont un format de synthèse prédéfini.</label>'.NL;
  }
  ?>
  <hr />
</div>

<form action="#" method="post" id="form_select"><fieldset>

  <div>
    <label class="tab" for="f_objet">Objet :</label><?php echo str_replace( '"selection"' , '"selection"'.$objet_selection , $select_objet_releve); ?>
  </div>

  <div id="zone_matieres" class="hide">
    <label class="tab" for="f_matieres">Matière(s) :</label><span id="f_matieres" class="select_multiple"><?php echo $select_matieres ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><?php echo $separateur_check_matieres ?><q class="cocher_rien" title="Tout décocher."></q><?php echo $bouton_modifier_matieres ?></span>
  </div>

  <div id="zone_matiere" class="hide">
    <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><?php echo $bouton_modifier_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><label id="ajax_maj_matiere">&nbsp;</label><br />
    <span id="zone_synthese" class="hide">
      <label class="tab">Mode de synthèse :</label><label for="f_mode_synthese_predefini"><input type="radio" id="f_mode_synthese_predefini" name="f_mode_synthese" value="predefini"<?php echo $check_synthese_predefini ?> /> tel que prédéfini</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_mode_synthese_domaine"><input type="radio" id="f_mode_synthese_domaine" name="f_mode_synthese" value="domaine"<?php echo $check_synthese_domaine ?> /> forcé par domaines</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_mode_synthese_theme"><input type="radio" id="f_mode_synthese_theme" name="f_mode_synthese" value="theme"<?php echo $check_synthese_theme ?> /> forcé par thèmes</label><br />
      <span class="tab"></span><label for="f_fusion_niveaux"><input type="checkbox" id="f_fusion_niveaux" name="f_fusion_niveaux" value="1"<?php echo $check_fusion_niveaux ?> /> Ne pas indiquer le niveau et fusionner les synthèses de même intitulé</label>
    </span>
  </div>

  <div id="zone_selection" class="hide">
    <label class="tab">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="aucun" readonly /><input id="f_compet_liste" name="f_compet_liste" type="text" value="" class="invisible" /><q class="choisir_compet" title="Voir ou choisir les items."></q>
  </div>

  <p>
    <label class="tab">Indicateur :</label><?php echo $moyenne_scores.'&nbsp;&nbsp;&nbsp;'.$pourcentage_acquis.'&nbsp;&nbsp;&nbsp;'.$conversion_sur_20 ?>
  </p>
  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj">&nbsp;</label><br />
    <label class="tab" for="f_eleve">Élève :</label><select id="f_eleve" name="f_eleve"><?php echo $select_eleve ?></select>
  </p>
  <p id="zone_periodes" class="<?php echo $class_form_periode ?>">
    <label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="radio"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés.<br />En automatique, les paramètres enregistrés pour chaque référentiel s'appliquent." /> Prise en compte des évaluations antérieures :</span>
      <label for="f_retroactif_auto"><input type="radio" id="f_retroactif_auto" name="f_retroactif" value="auto"<?php echo $check_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_non"><input type="radio" id="f_retroactif_non" name="f_retroactif" value="non"<?php echo $check_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_oui"><input type="radio" id="f_retroactif_oui" name="f_retroactif" value="oui"<?php echo $check_retroactif_oui ?> /> oui (sans limite)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_annuel"><input type="radio" id="f_retroactif_annuel" name="f_retroactif" value="annuel"<?php echo $check_retroactif_annuel ?> /> de l'année scolaire</label>
  </p>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Restriction :</label><label for="f_only_socle"><input type="checkbox" id="f_only_socle" name="f_only_socle" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab">Échelle :</label>axe des ordonnées <?php echo $select_echelle ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <div id="arborescence"><label class="loader">Chargement&hellip;</label></div>
  <p><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></p>
  <hr />
  <p>
    <label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
    <label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
  </p>
</form>

<div id="bilan" class="hide">
  <h3 id="report_titre"></h3>
  <div><span id="navigation_eleve" class="<?php echo $class_navig_eleve ?>"><button class="go_premier" type="button" id="go_premier_eleve">Premier</button> <button class="go_precedent" type="button" id="go_precedent_eleve">Précédent</button> <select class="b" name="go_selection" id="go_selection_eleve"><option></option></select> <button class="go_suivant" type="button" id="go_suivant_eleve">Suivant</button> <button class="go_dernier" type="button" id="go_dernier_eleve">Dernier</button></span>&nbsp;&nbsp;&nbsp;<button class="retourner" type="button" id="fermer_zone_bilan">Retour</button></div>
</div>
<div id="div_graphique_chronologique" class="hide"></div>

