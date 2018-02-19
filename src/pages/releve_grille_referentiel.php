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
$TITRE = html(Lang::_("Grille d'items d'un référentiel"));

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && (!$_SESSION['NB_ENFANTS']) )
{
  echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if(!Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_type_generique    = (Form::$tab_choix['type_generique'])       ? ' checked' : '' ;
$check_type_individuel   = (Form::$tab_choix['type_individuel'])      ? ' checked' : '' ;
$check_type_synthese     = (Form::$tab_choix['type_synthese'])        ? ' checked' : '' ;
$check_retroactif_auto   = (Form::$tab_choix['retroactif']=='auto')   ? ' checked' : '' ;
$check_retroactif_non    = (Form::$tab_choix['retroactif']=='non')    ? ' checked' : '' ;
$check_retroactif_oui    = (Form::$tab_choix['retroactif']=='oui')    ? ' checked' : '' ;
$check_retroactif_annuel = (Form::$tab_choix['retroactif']=='annuel') ? ' checked' : '' ;
$check_only_socle        = (Form::$tab_choix['only_socle'])           ? ' checked' : '' ;
$check_aff_reference     = (Form::$tab_choix['aff_reference'])        ? ' checked' : '' ;
$check_aff_coef          = (Form::$tab_choix['aff_coef'])             ? ' checked' : '' ;
$check_aff_socle         = (Form::$tab_choix['aff_socle'])            ? ' checked' : '' ;
$check_aff_comm          = (Form::$tab_choix['aff_comm'])             ? ' checked' : '' ;
$check_aff_lien          = (Form::$tab_choix['aff_lien'])             ? ' checked' : '' ;
$check_repeter_entete    = (Form::$tab_choix['repeter_entete'])       ? ' checked' : '' ;
$check_cases_auto        = (Form::$tab_choix['cases_auto'])           ? ' checked' : '' ;
$class_form_generique    = (Form::$tab_choix['type_generique'])       ? 'hide'     : 'show' ;
$class_form_individuel   = (Form::$tab_choix['type_individuel'])      ? 'show'     : 'hide' ;
$class_form_synthese     = (Form::$tab_choix['type_synthese'])        ? 'show'     : 'hide' ;
$class_cases_auto        = (Form::$tab_choix['cases_auto'])           ? 'show'     : 'hide' ;
$class_cases_manuel      = (Form::$tab_choix['cases_auto'])           ? 'hide'     : 'show' ;

$bouton_modifier_matieres = '';
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = ''; $sel_g = FALSE; $class_form_type = 'show'; $class_form_eleve = $class_form_generique;
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $of_g = ''; $sel_g = FALSE; $class_form_type = 'show'; $class_form_eleve = $class_form_generique;
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
  $bouton_modifier_matieres = '<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>1) )
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $of_g = ''; $sel_g = FALSE; $class_form_type = 'hide'; $class_form_eleve = $class_form_generique;
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option></option></select>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 0; // volontaire
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $of_g = FALSE; $sel_g = TRUE;  $class_form_type = 'hide'; $class_form_eleve = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option></select>';
  $is_select_multiple = 0;
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $of_g = FALSE; $sel_g = TRUE;  $class_form_type = 'hide'; $class_form_eleve = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option></select>';
  $is_select_multiple = 0;
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_synthese_format = HtmlForm::afficher_select(Form::$tab_select_synthese_format , 'f_synthese_format' /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_synthese_format'] /*selection*/ ,              '' /*optgroup*/ );
$select_tri_etat_mode   = HtmlForm::afficher_select(Form::$tab_select_tri_etat_mode   , 'f_tri_etat_mode'   /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_tri_etat_mode']   /*selection*/ ,              '' /*optgroup*/ );
$select_remplissage     = HtmlForm::afficher_select(Form::$tab_select_remplissage     , 'f_remplissage'     /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['remplissage']             /*selection*/ ,              '' /*optgroup*/ );
$select_colonne_bilan   = HtmlForm::afficher_select(Form::$tab_select_colonne_bilan   , 'f_colonne_bilan'   /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['colonne_bilan']           /*selection*/ ,              '' /*optgroup*/ );
$select_colonne_vide    = HtmlForm::afficher_select(Form::$tab_select_colonne_vide    , 'f_colonne_vide'    /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['colonne_vide']            /*selection*/ ,              '' /*optgroup*/ );
$select_matiere         = HtmlForm::afficher_select($tab_matieres                     , 'f_matiere'         /*select_nom*/ ,                      '' /*option_first*/ , Form::$tab_choix['matiere_id']              /*selection*/ ,              '' /*optgroup*/ );
$select_groupe          = HtmlForm::afficher_select($tab_groupes                      , 'f_groupe'          /*select_nom*/ ,                   $of_g /*option_first*/ , $sel_g                                      /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleves_ordre    = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre    , 'f_eleves_ordre'    /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre']            /*selection*/ ,              '' /*optgroup*/ );
$select_periode         = HtmlForm::afficher_select($tab_periodes                     , 'f_periode'         /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                                       /*selection*/ ,              '' /*optgroup*/ );
$select_only_etat       = HtmlForm::afficher_select(Form::$tab_select_only_etat       , 'f_only_etat'       /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['only_etat']               /*selection*/ ,              '' /*optgroup*/ );
$select_only_arbo       = HtmlForm::afficher_select(Form::$tab_select_only_arbo       , 'f_only_arbo'       /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['only_arbo']               /*selection*/ ,              '' /*optgroup*/ );
$select_orientation     = HtmlForm::afficher_select(Form::$tab_select_orientation     , 'f_orientation'     /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['orientation']             /*selection*/ ,              '' /*optgroup*/ );
$select_marge_min       = HtmlForm::afficher_select(Form::$tab_select_marge_min       , 'f_marge_min'       /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['marge_min']               /*selection*/ ,              '' /*optgroup*/ );
$select_pages_nb        = HtmlForm::afficher_select(Form::$tab_select_pages_nb        , 'f_pages_nb'        /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['pages_nb']                /*selection*/ ,              '' /*optgroup*/ );
$select_couleur         = HtmlForm::afficher_select(Form::$tab_select_couleur         , 'f_couleur'         /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['couleur']                 /*selection*/ ,              '' /*optgroup*/ );
$select_fond            = HtmlForm::afficher_select(Form::$tab_select_fond            , 'f_fond'            /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['fond']                    /*selection*/ ,              '' /*optgroup*/ );
$select_legende         = HtmlForm::afficher_select(Form::$tab_select_legende         , 'f_legende'         /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['legende']                 /*selection*/ ,              '' /*optgroup*/ );
$select_cases_nb        = HtmlForm::afficher_select(Form::$tab_select_cases_nb        , 'f_cases_nb'        /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_nb']                /*selection*/ ,              '' /*optgroup*/ );
$select_cases_larg      = HtmlForm::afficher_select(Form::$tab_select_cases_size      , 'f_cases_larg'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_largeur']           /*selection*/ ,              '' /*optgroup*/ );

// Affichage ou non du formulaire de période
if($of_g == 'oui')
{
  $class_form_periode='hide';
}
elseif(Form::$tab_choix['type_generique'])
{
  $class_form_periode='hide';
}
elseif(Form::$tab_choix['type_synthese'])
{
  $class_form_periode='show';
}
elseif(Form::$tab_choix['type_individuel'])
{
  if( (Form::$tab_choix['remplissage']=='plein') || (Form::$tab_choix['colonne_bilan']=='oui') )
  {
    $class_form_periode='show';
  }
  else
  {
    $class_form_periode='hide';
  }
}
else
{
  $class_form_periode='hide';
}
$is_periode_requise = ($class_form_periode=='show') ? 'true' : 'false' ;

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql      = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var is_multiple     = '.$is_select_multiple.';' );
Layout::add( 'js_inline_before' , 'var periode_requise = '.$is_periode_requise.';' );

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_grille_referentiel">DOC : Grille d'items d'un référentiel.</a></span></div>
<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p class="<?php echo $class_form_type ?>">
    <label class="tab">Type de document :</label><label for="f_type_generique"><input type="checkbox" id="f_type_generique" name="f_type[]" value="generique"<?php echo $check_type_generique ?> /> Fiche générique</label><span id="generique_non_1" class="<?php echo $class_form_generique ?>">&nbsp;&nbsp;&nbsp;<label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type[]" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type[]" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label></span><br />
    <span id="generique_non_2" class="<?php echo $class_form_generique ?>">
      <span id="options_individuel" class="<?php echo $class_form_individuel ?>">
        <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Paramétrage du relevé individuel." /> Opt. relevé :</label><?php echo $select_remplissage ?> <?php echo $select_colonne_bilan ?> <?php echo $select_colonne_vide ?><br />
      </span>
      <span id="options_synthese" class="<?php echo $class_form_synthese ?>">
        <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Paramétrage du tableau de synthèse." /> Opt. synthèse :</label><?php echo $select_synthese_format ?> <?php echo $select_tri_etat_mode ?><br />
        <span class="tab"></span><label for="f_repeter_entete"><input type="checkbox" id="f_repeter_entete" name="f_repeter_entete" value="1"<?php echo $check_repeter_entete ?> /> Répéter les entêtes de lignes et de colonnes (grand tableau, format <em>html</em>)</label><br />
      </span>
    </span>
  </p>
  <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><?php echo $bouton_modifier_matieres ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><label id="ajax_maj_matiere">&nbsp;</label><br />
  <label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><option></option></select><input type="hidden" id="f_niveau_nom" name="f_niveau_nom" value="" />
  <p id="generique_non_3" class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj_groupe">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><?php echo $select_eleves ?></span>
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
      <label for="f_retroactif_annuel"><input type="radio" id="f_retroactif_annuel" name="f_retroactif" value="annuel"<?php echo $check_retroactif_annuel ?> /> de l'année scolaire
  </p>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Restrictions :</label><?php echo $select_only_etat ?> <span id="generique_non_4" class="<?php echo $class_form_generique ?>"><?php echo $select_only_arbo ?></span><br />
    <span class="tab"></span><label for="f_only_socle"><input type="checkbox" id="f_only_socle" name="f_only_socle" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab">Indications :</label><label for="f_reference"><input type="checkbox" id="f_reference" name="f_reference" value="1"<?php echo $check_aff_reference ?> /> Références</label>&nbsp;&nbsp;&nbsp;<label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_comm"><input type="checkbox" id="f_comm" name="f_comm" value="1"<?php echo $check_aff_comm ?> /> Commentaires</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?> <?php echo $select_pages_nb ?><br />
    <label class="tab">Évaluations :</label><label for="f_cases_auto"><input type="checkbox" id="f_cases_auto" name="f_cases_auto" value="1"<?php echo $check_cases_auto ?> /> <span id="span_cases_auto" class="<?php echo $class_cases_auto ?>">nombre et largeur des cases automatique</span></label><span id="span_cases_manuel" class="<?php echo $class_cases_manuel ?>"><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?></span>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan"></div>
