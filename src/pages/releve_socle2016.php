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
$TITRE = html(Lang::_("Maîtrise du socle"));

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && (!$_SESSION['NB_ENFANTS']) )
{
  echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if( !in_array($_SESSION['USER_PROFIL_TYPE'],array('professeur','directeur')) && !Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_ACCES']) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) en complément des professeurs et directeurs :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_SOCLE_ACCES'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$tab_cycles = DB_STRUCTURE_COMMUN::DB_OPT_socle2016_cycles( TRUE /*only_used*/ );
if(is_string($tab_cycles))
{
  echo'<p class="danger">Aucun item de référentiel n\'étant relié au nouveau socle commun, aucun bilan ne peut être obtenu.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

Form::load_choix_memo();
$check_type_individuel      = (Form::$tab_choix['type_individuel']) ? ' checked' : '' ;
$check_type_synthese        = (Form::$tab_choix['type_synthese'])   ? ' checked' : '' ;
$class_form_individuel      = (Form::$tab_choix['type_individuel']) ? 'show'     : 'hide' ;
$class_form_synthese        = (Form::$tab_choix['type_synthese'])   ? 'show'     : 'hide' ;
$class_socle_points_DNB     = ( (Form::$tab_choix['cycle_id']==4) && (Form::$tab_choix['socle_detail']=='livret') ) ? 'show' : 'hide' ;

$check_only_presence        = (Form::$tab_choix['only_presence'])          ? ' checked' : '' ;
$check_aff_lien             = (Form::$tab_choix['aff_lien'])               ? ' checked' : '' ;
$check_aff_start            = (Form::$tab_choix['aff_start'])              ? ' checked' : '' ;
$check_socle_items_acquis   = (Form::$tab_choix['aff_socle_items_acquis']) ? ' checked' : '' ;
$check_socle_position       = (Form::$tab_choix['aff_socle_position'])     ? ' checked' : '' ;
$check_socle_points_DNB     = (Form::$tab_choix['aff_socle_points_DNB'])   ? ' checked' : '' ;
$check_synthese_pourcentage = (Form::$tab_choix['socle_synthese_affichage']=='pourcentage') ? ' checked' : '' ;
$check_synthese_position    = (Form::$tab_choix['socle_synthese_affichage']=='position')    ? ' checked' : '' ;
$check_synthese_points      = (Form::$tab_choix['socle_synthese_affichage']=='points')      ? ' checked' : '' ;
$check_mode_auto            = (Form::$tab_choix['mode']=='auto')           ? ' checked' : '' ;
$check_mode_manuel          = (Form::$tab_choix['mode']=='manuel')         ? ' checked' : '' ;
$class_div_matiere          = (Form::$tab_choix['mode']=='manuel')         ? 'show'     : 'hide' ;

if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_socle_position   = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PROPOSITION_POSITIONNEMENT']) ? ' checked' : '' ;
  $check_socle_points_DNB = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PREVISION_POINTS_BREVET'])    ? ' checked' : '' ;
}
$socle_items_acquis = '<label for="f_socle_items_acquis"><input type="checkbox" id="f_socle_items_acquis" name="f_socle_items_acquis" value="1"'.$check_socle_items_acquis.' /> Acquisition des items</label>';
$socle_position     = '<label for="f_socle_position"><input type="checkbox" id="f_socle_position" name="f_socle_position" value="1"'.$check_socle_position.' /> Proposition de positionnement</label>';
$socle_points_DNB   = '<label for="f_socle_points_dnb"><input type="checkbox" id="f_socle_points_dnb" name="f_socle_points_dnb" value="1"'.$check_socle_points_DNB.' /> Prévision du nombre de points pour le brevet</label>';

if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_option_groupe = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $class_form_type    = 'show';
  $class_option_mode  = 'show';
  $is_select_multiple = 1;
}

elseif($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_option_groupe = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $class_form_type    = 'show';
  $class_option_mode  = 'show';
  $is_select_multiple = 1;
}

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_option_groupe = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option></option></select>'; // maj en ajax suivant le choix du groupe
  $class_form_type    = 'hide';
  $class_option_mode  = 'hide';
  $is_select_multiple = 0; // volontaire
  $socle_position   = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PROPOSITION_POSITIONNEMENT']) ? $socle_position   : '<del>Proposition de positionnement</del>' ;
  $socle_points_DNB = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PREVISION_POINTS_BREVET'])    ? $socle_points_DNB : '<del>Prévision du nombre de points pour le brevet</del>' ;
}

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $of_g = FALSE; $sel_g = TRUE;  $class_form_eleve = 'hide'; $class_option_groupe = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option></select>';
  $class_form_type    = 'hide';
  $class_option_mode  = 'hide';
  $is_select_multiple = 0;
  $socle_position   = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PROPOSITION_POSITIONNEMENT']) ? $socle_position   : '<del>Proposition de positionnement</del>' ;
  $socle_points_DNB = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PREVISION_POINTS_BREVET'])    ? $socle_points_DNB : '<del>Prévision du nombre de points pour le brevet</del>' ;
}

elseif($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $of_g = FALSE; $sel_g = TRUE;  $class_form_eleve = 'hide'; $class_option_groupe = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option></select>';
  $class_form_type    = 'hide';
  $class_option_mode  = 'hide';
  $is_select_multiple = 0;
  $socle_position   = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PROPOSITION_POSITIONNEMENT']) ? $socle_position   : '<del>Proposition de positionnement</del>' ;
  $socle_points_DNB = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PREVISION_POINTS_BREVET'])    ? $socle_points_DNB : '<del>Prévision du nombre de points pour le brevet</del>' ;
}

$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();

$select_cycle                   = HtmlForm::afficher_select($tab_cycles                               , 'f_cycle'                   /*select_nom*/ ,    '' /*option_first*/ , Form::$tab_choix['cycle_id']                  /*selection*/ ,              '' /*optgroup*/ );
$select_socle_individuel_format = HtmlForm::afficher_select(Form::$tab_select_socle_individuel_format , 'f_socle_individuel_format' /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['socle_individuel_format']   /*selection*/ ,              '' /*optgroup*/ );
$select_socle_synthese_format   = HtmlForm::afficher_select(Form::$tab_select_socle_synthese_format   , 'f_socle_synthese_format'   /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['socle_synthese_format']     /*selection*/ ,              '' /*optgroup*/ );
$select_socle_detail            = HtmlForm::afficher_select(Form::$tab_select_socle_detail            , 'f_socle_detail'            /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['socle_detail']              /*selection*/ ,              '' /*optgroup*/ );
$select_tri_maitrise_mode       = HtmlForm::afficher_select(Form::$tab_select_tri_maitrise_mode       , 'f_tri_maitrise_mode'       /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['tableau_tri_maitrise_mode'] /*selection*/ ,              '' /*optgroup*/ );
$select_groupe                  = HtmlForm::afficher_select($tab_groupes                              , 'f_groupe'                  /*select_nom*/ , $of_g /*option_first*/ , $sel_g                                        /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleves_ordre            = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre            , 'f_eleves_ordre'            /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre']              /*selection*/ ,              '' /*optgroup*/ );
$select_matiere                 = HtmlForm::afficher_select($tab_matieres                             , 'f_matiere'                 /*select_nom*/ , FALSE /*option_first*/ , TRUE                                          /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/);
$select_marge_min               = HtmlForm::afficher_select(Form::$tab_select_marge_min               , 'f_marge_min'               /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['marge_min']                 /*selection*/ ,              '' /*optgroup*/ );
$select_pages_nb                = HtmlForm::afficher_select(Form::$tab_select_pages_nb                , 'f_pages_nb'                /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['pages_nb']                  /*selection*/ ,              '' /*optgroup*/ );
$select_couleur                 = HtmlForm::afficher_select(Form::$tab_select_couleur                 , 'f_couleur'                 /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['couleur']                   /*selection*/ ,              '' /*optgroup*/ );
$select_fond                    = HtmlForm::afficher_select(Form::$tab_select_fond                    , 'f_fond'                    /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['fond']                      /*selection*/ ,              '' /*optgroup*/ );
$select_legende                 = HtmlForm::afficher_select(Form::$tab_select_legende                 , 'f_legende'                 /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['legende']                   /*selection*/ ,              '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var is_multiple = '.$is_select_multiple.';' );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__maitrise_socle2016">DOC : Maîtrise du socle.</a></span></div>
<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab" for="f_cycle">Cycle :</label><?php echo $select_cycle ?><input type="hidden" id="f_cycle_nom" name="f_cycle_nom" value="" /><br />
  <label class="tab" for="f_socle_detail">Détail :</label><?php echo $select_socle_detail ?>

  <div class="<?php echo $class_form_type ?> p">
    <div>
      <label class="tab">Type de document :</label>
      <label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type[]" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;
      <label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type[]" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label>
    </div>
    <p id="options_individuel" class="<?php echo $class_form_individuel ?>">
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, deux présentations sont possibles." /> Opt. relevé :</label><?php echo $select_socle_individuel_format ?><br />
      <label class="tab">Indication relevé :</label><?php echo $socle_items_acquis ?>&nbsp;&nbsp;&nbsp;<?php echo $socle_position ?>&nbsp;&nbsp;&nbsp;<span id="span_points_DNB" class="<?php echo $class_socle_points_DNB ?>"><?php echo $socle_points_DNB ?></span>
    </p>
    <p id="options_synthese" class="<?php echo $class_form_synthese ?>">
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Paramétrage du tableau de synthèse." /> Opt. synthèse :</label><?php echo $select_socle_synthese_format ?> <?php echo $select_tri_maitrise_mode ?><br />
      <label class="tab">Indication synthèse :</label>
      <label for="f_socle_synthese_affichage_pourcentage"><input type="radio" id="f_socle_synthese_affichage_pourcentage" name="f_socle_synthese_affichage" value="pourcentage"<?php echo $check_synthese_pourcentage ?> /> Pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_synthese_affichage_position" id="label_affichage_position" class="<?php echo $class_socle_points_DNB ?>"><input type="radio" id="f_socle_synthese_affichage_position" name="f_socle_synthese_affichage" value="position"<?php echo $check_synthese_position ?> />Proposition de positionnement</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_synthese_affichage_points" id="label_affichage_points" class="<?php echo $class_socle_points_DNB ?>"><input type="radio" id="f_socle_synthese_affichage_points" name="f_socle_synthese_affichage" value="points"<?php echo $check_synthese_points ?> /> Prévision du nombre de points pour le brevet</label>
    </p>
  </div>

  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><?php echo $select_eleves ?></span>
  </p>
  <div id="option_groupe" class="<?php echo $class_option_groupe ?>">
    <label class="tab">Restriction :</label><label for="f_only_presence"><input type="checkbox" id="f_only_presence" name="f_only_presence" value="1"<?php echo $check_only_presence ?> /> Uniquement les éléments ayant fait l'objet d'une évaluation</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, le détail des items peut être affiché (sortie HTML)." /> Infos items :</label><label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_start"><input type="checkbox" id="f_start" name="f_start" value="1"<?php echo $check_aff_start ?> /> Détails affichés par défaut</label>
  </div>
  <div id="option_mode" class="<?php echo $class_option_mode ?>">
    <label class="tab">Matières utilisées :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Toutes (recommandé)</label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" />
    <div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></div>
  </div>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?> <?php echo $select_pages_nb ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan">
</div>
