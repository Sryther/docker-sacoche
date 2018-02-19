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
$TITRE = html(Lang::_("Synthèse d'items"));

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && (!$_SESSION['NB_ENFANTS']) )
{
  echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_synthese_predefini = (Form::$tab_choix['mode_synthese']=='predefini') ? ' checked' : '' ;
$check_synthese_domaine   = (Form::$tab_choix['mode_synthese']=='domaine')   ? ' checked' : '' ;
$check_synthese_theme     = (Form::$tab_choix['mode_synthese']=='theme')     ? ' checked' : '' ;
$check_fusion_niveaux     = (Form::$tab_choix['fusion_niveaux'])             ? ' checked' : '' ;
$check_retroactif_auto    = (Form::$tab_choix['retroactif']=='auto')         ? ' checked' : '' ;
$check_retroactif_non     = (Form::$tab_choix['retroactif']=='non')          ? ' checked' : '' ;
$check_retroactif_oui     = (Form::$tab_choix['retroactif']=='oui')          ? ' checked' : '' ;
$check_retroactif_annuel  = (Form::$tab_choix['retroactif']=='annuel')       ? ' checked' : '' ;
$check_only_socle         = (Form::$tab_choix['only_socle'])                 ? ' checked' : '' ;
$check_only_niveau        = (Form::$tab_choix['only_niveau'])                ? ' checked' : '' ;
$check_aff_coef           = (Form::$tab_choix['aff_coef'])                   ? ' checked' : '' ;
$check_aff_socle          = (Form::$tab_choix['aff_socle'])                  ? ' checked' : '' ;
$check_aff_lien           = (Form::$tab_choix['aff_lien'])                   ? ' checked' : '' ;
$check_aff_start          = (Form::$tab_choix['aff_start'])                  ? ' checked' : '' ;
$bouton_modifier_matieres = '';
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_matieres = 'Choisir d\'abord un groupe ci-dessous...'; // maj en ajax suivant le choix du groupe
  $sel_obj=FALSE; $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $sel_obj=FALSE; $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
  $bouton_modifier_matieres = '<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $sel_obj='multimatiere'; $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option></option></select>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 0; // volontaire
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $sel_obj='multimatiere'; $of_g = FALSE; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_form_option = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option></select>';
  $is_select_multiple = 0;
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $sel_obj='multimatiere'; $of_g = FALSE; $sel_g = TRUE;  $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_form_option = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option></select>';
  $is_select_multiple = 0;
}

$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$tab_select_objet_releve = array(
    array('valeur' => 'matiere'      , 'texte' => Lang::_("Synthèse d'une matière")) ,
    array('valeur' => 'multimatiere' , 'texte' => Lang::_("Synthèse pluridisciplinaire")) ,
);

$select_objet_releve = HtmlForm::afficher_select($tab_select_objet_releve       , 'f_objet'        /*select_nom*/ ,                      '' /*option_first*/ , $sel_obj                         /*selection*/ ,              '' /*optgroup*/ );
$select_groupe       = HtmlForm::afficher_select($tab_groupes                   , 'f_groupe'       /*select_nom*/ ,                   $of_g /*option_first*/ , $sel_g                           /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleves_ordre = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre , 'f_eleves_ordre' /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre'] /*selection*/ ,              '' /*optgroup*/ );
$select_matiere      = HtmlForm::afficher_select($tab_matieres                  , 'f_matiere'      /*select_nom*/ ,                      '' /*option_first*/ , Form::$tab_choix['matiere_id']   /*selection*/ ,              '' /*optgroup*/ );
$select_periode      = HtmlForm::afficher_select($tab_periodes                  , 'f_periode'      /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                            /*selection*/ ,              '' /*optgroup*/ );
$select_marge_min    = HtmlForm::afficher_select(Form::$tab_select_marge_min    , 'f_marge_min'    /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['marge_min']    /*selection*/ ,              '' /*optgroup*/ );
$select_couleur      = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_couleur'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['couleur']      /*selection*/ ,              '' /*optgroup*/ );
$select_fond         = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_fond'         /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['fond']         /*selection*/ ,              '' /*optgroup*/ );
$select_legende      = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_legende'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['legende']      /*selection*/ ,              '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql  = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var is_multiple = '.$is_select_multiple.';' );

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
// Fabrication du tableau javascript "tab_groupe_niveau" pour les jointures groupes/niveaux
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , TRUE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_items">DOC : Synthèse d'items.</a></span></div>
<div class="astuce">Un administrateur ou un directeur doit préalablement choisir l'ordre d'affichage des matières (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_releves_bilans#toggle_ordre_matieres">DOC</a></span>) et le type de synthèse adapté à chaque référentiel (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_releves_bilans#toggle_type_synthese">DOC</a></span>).</div>
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

<form action="#" method="post" id="form_select"><fieldset>

  <div>
    <label class="tab" for="f_objet">Objet :</label><?php echo $select_objet_releve ?>
  </div>

  <div id="choix_matiere" class="hide">
    <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><?php echo $bouton_modifier_matieres ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" />
  </div>

  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj">&nbsp;</label><br />
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
  <div id="zone_options" class="<?php echo $class_form_option ?>">
    <div class="toggle">
      <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
    </div>
    <div class="toggle hide">
      <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
      <label class="tab">Mode de synthèse :</label><label for="f_fusion_niveaux"><input type="checkbox" id="f_fusion_niveaux" name="f_fusion_niveaux" value="1"<?php echo $check_fusion_niveaux ?> /> Ne pas indiquer le niveau et fusionner les synthèses de même intitulé</label><br />
      <span id="span_not_multimatiere">
        <span class="tab"></span><label for="f_mode_synthese_predefini"><input type="radio" id="f_mode_synthese_predefini" name="f_mode_synthese" value="predefini"<?php echo $check_synthese_predefini ?> /> tel que prédéfini</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_mode_synthese_domaine"><input type="radio" id="f_mode_synthese_domaine" name="f_mode_synthese" value="domaine"<?php echo $check_synthese_domaine ?> /> forcé par domaines</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_mode_synthese_theme"><input type="radio" id="f_mode_synthese_theme" name="f_mode_synthese" value="theme"<?php echo $check_synthese_theme ?> /> forcé par thèmes</label><br />
      </span>
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format HTML, le détail des items peut être affiché." /> Indications :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_start"><input type="checkbox" id="f_start" name="f_start" value="1"<?php echo $check_aff_start ?> /> Détails affichés par défaut</label><br />
      <label class="tab">Restrictions :</label><label for="f_restriction_socle"><input type="checkbox" id="f_restriction_socle" name="f_restriction_socle" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés au socle</label><br />
      <span class="tab"></span><label for="f_restriction_niveau"><input type="checkbox" id="f_restriction_niveau" name="f_restriction_niveau" value="1"<?php echo $check_only_niveau ?> /> Utiliser uniquement les items du niveau <em id="niveau_nom"></em></label><input type="hidden" id="f_niveau" name="f_niveau" value="" /><br />
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?>
    </div>
  </div>
  <p>
    <span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label>
  </p>
</fieldset></form>

<div id="bilan"></div>
