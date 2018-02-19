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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action    = (isset($_POST['f_action']))    ? Clean::texte($_POST['f_action'])  : '';
$type      = (isset($_POST['f_type']))      ? Clean::texte($_POST['f_type'])    : '';
$reference = (isset($_POST['f_reference'])) ? Clean::id($_POST['f_reference'])  : '';
$nom       = (isset($_POST['f_nom']))       ? Clean::texte($_POST['f_nom'])     : '';
$classe_id = (isset($_POST['f_classe']))    ? Clean::entier($_POST['f_classe']) : 0;

// On nettoie encore un peu plus pour ne garder que lettres et chiffres
$reference = str_replace( array('-','_') , '' , $reference );

$tab_types = array(
  'releve'   => array( 'ordre'=>1 , 'nom' => "Relevé d'évaluations" ) ,
  'bulletin' => array( 'ordre'=>2 , 'nom' => "Bulletin scolaire"    ) ,
  'livret'   => array( 'ordre'=>3 , 'nom' => "Livret Scolaire"      ) ,
);

if(!isset($tab_types[$type]))
{
  Json::end( FALSE , 'Type de bilan inconnu ("'.$type.'") !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une configuration afin de la modifier ou de la dupliquer pour un ajout
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ( ($action=='afficher_ajouter') || ($action=='afficher_modifier') ) && $reference && $nom )
{
  // Récupérer, si besoin, les paramètres du bilan (on ne force pas l'actualisation si déjà en session car on est justement dans l'interface de gestion).
  // La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
  if( !isset($_SESSION['OFFICIEL'][Clean::upper($type).'_CONFIG_REF']) || ($_SESSION['OFFICIEL'][Clean::upper($type).'_CONFIG_REF']!=$reference) )
  {
    $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( $type , $reference );
    foreach($tab_configuration as $key => $val)
    {
      $_SESSION['OFFICIEL'][Clean::upper($type.'_'.$key)] = $val;
    }
    $_SESSION['OFFICIEL'][Clean::upper($type).'_CONFIG_REF'] = $reference;
  }
  // Début du retour
  $input_reference = ($action=='afficher_modifier') ? '<input id="f_reference" name="f_reference" type="text" value="'.$reference.'" size="15" maxlength="15" readonly />' : '<input id="f_reference" name="f_reference" type="text" value="" size="15" maxlength="15" />' ;
  $value_nom       = ($action=='afficher_modifier') ? $nom : '' ;
  Json::add_str('<label class="tab">Type de bilan :</label><b>'.$tab_types[$type]['nom'].'</b><br />'.NL);
  Json::add_str('<label class="tab" for="f_reference">Référence :</label>'.$input_reference.'<br />'.NL);
  Json::add_str('<label class="tab" for="f_nom">Nom / Commentaire :</label><input id="f_nom" name="f_nom" type="text" value="'.html($value_nom).'" size="40" maxlength="60" /><br />'.NL);
  // Test commun à tous les types de bilans
  $txt_absence_droit = ($_SESSION['USER_PROFIL_TYPE']=='administrateur')
    ? 'Notifier aux parents un lien permettant de récupérer le bilan généré requiert de <a href="index.php?page=administrateur_etabl_autorisations">leur autoriser l\'accès aux archives</a>.'
    : 'Notifier aux parents un lien permettant de récupérer le bilan généré requiert de leur autoriser l\'accès aux archives (<a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_autorisations#toggle_bilans_officiels">DOC</a></span>).'
    ;
  if($type=='releve')
  {
    $select_releve_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_releve_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_releve_only_etat                      = HtmlForm::afficher_select(Form::$tab_select_only_etat    , 'f_releve_only_etat'                      /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_ONLY_ETAT']                      /*selection*/ , '' /*optgroup*/ );
    $select_releve_cases_nb                       = HtmlForm::afficher_select(Form::$tab_select_cases_nb     , 'f_releve_cases_nb'                       /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_CASES_NB']                       /*selection*/ , '' /*optgroup*/ );
    $select_releve_cases_larg                     = HtmlForm::afficher_select(Form::$tab_select_cases_size   , 'f_releve_cases_larg'                     /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_CASES_LARGEUR']                  /*selection*/ , '' /*optgroup*/ );
    $select_releve_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_releve_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
    $select_releve_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_releve_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_FOND']                           /*selection*/ , '' /*optgroup*/ );
    $select_releve_legende                        = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_releve_legende'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_LEGENDE']                        /*selection*/ , '' /*optgroup*/ );
    $select_releve_pages_nb                       = HtmlForm::afficher_select(Form::$tab_select_pages_nb     , 'f_releve_pages_nb'                       /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_PAGES_NB']                       /*selection*/ , '' /*optgroup*/ );
    $check_releve_appreciation_rubrique_report =  $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_REPORT'] ? ' checked' : '' ;
    $check_releve_appreciation_generale_report =  $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_REPORT'] ? ' checked' : '' ;
    $check_releve_ligne_supplementaire         =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']         ? ' checked' : '' ;
    $check_releve_assiduite                    =  $_SESSION['OFFICIEL']['RELEVE_ASSIDUITE']                    ? ' checked' : '' ;
    $check_releve_prof_principal               =  $_SESSION['OFFICIEL']['RELEVE_PROF_PRINCIPAL']               ? ' checked' : '' ;
    $check_releve_only_socle                   =  $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE']                   ? ' checked' : '' ;
    $check_releve_retroactif_auto              = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='auto')          ? ' checked' : '' ;
    $check_releve_retroactif_non               = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='non')           ? ' checked' : '' ;
    $check_releve_retroactif_oui               = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='oui')           ? ' checked' : '' ;
    $check_releve_retroactif_annuel            = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='annuel')        ? ' checked' : '' ;
    $check_releve_cases_auto                   =  $_SESSION['OFFICIEL']['RELEVE_CASES_AUTO']                   ? ' checked' : '' ;
    $check_releve_etat_acquisition             =  $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION']             ? ' checked' : '' ;
    $check_releve_moyenne_scores               =  $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES']               ? ' checked' : '' ;
    $check_releve_pourcentage_acquis           =  $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS']           ? ' checked' : '' ;
    $check_releve_conversion_sur_20            =  $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20']            ? ' checked' : '' ;
    $check_releve_aff_reference                =  $_SESSION['OFFICIEL']['RELEVE_AFF_REFERENCE']                ? ' checked' : '' ;
    $check_releve_aff_coef                     =  $_SESSION['OFFICIEL']['RELEVE_AFF_COEF']                     ? ' checked' : '' ;
    $check_releve_aff_socle                    =  $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE']                    ? ' checked' : '' ;
    $check_releve_aff_domaine                  =  $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE']                  ? ' checked' : '' ;
    $check_releve_aff_theme                    =  $_SESSION['OFFICIEL']['RELEVE_AFF_THEME']                    ? ' checked' : '' ;
    $check_releve_envoi_mail_parent            =  $_SESSION['OFFICIEL']['RELEVE_ENVOI_MAIL_PARENT']            ? ' checked' : '' ;
    $class_span_releve_appreciation_rubrique_report = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_LONGUEUR'] ? 'show' : 'hide' ;
    $class_span_releve_appreciation_rubrique_modele = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_REPORT']   ? 'show' : 'hide' ;
    $class_span_releve_appreciation_generale_report = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_LONGUEUR'] ? 'show' : 'hide' ;
    $class_span_releve_appreciation_generale_modele = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_REPORT']   ? 'show' : 'hide' ;
    $class_input_releve_ligne_factice        = !$_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;
    $class_input_releve_ligne_supplementaire =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;
    $class_span_releve_cases_auto            =  $_SESSION['OFFICIEL']['RELEVE_CASES_AUTO'] ? 'show' : 'hide' ;
    $class_span_releve_cases_manuel          =  $_SESSION['OFFICIEL']['RELEVE_CASES_AUTO'] ? 'hide' : 'show' ;
    $class_span_releve_etat_acquisition      = ($check_releve_etat_acquisition)            ? 'show' : 'hide' ;
    $class_label_releve_conversion_sur_20    = ($check_releve_moyenne_scores || $check_releve_pourcentage_acquis) ? 'show' : 'hide' ;
    $texte_releve_envoi_mail_parent = (in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_RELEVE_VOIR_ARCHIVE']) ))
      ? '<label for="f_releve_envoi_mail_parent"><input type="checkbox" id="f_releve_envoi_mail_parent" name="f_releve_envoi_mail_parent" value="1"'.$check_releve_envoi_mail_parent.' /> Envoyer aux parents un courriel avec un lien permettant de récupérer le bilan généré.</label>'
      : '<input type="checkbox" id="f_releve_envoi_mail_parent" name="f_releve_envoi_mail_parent" value="1"'.$check_releve_envoi_mail_parent.' class="hide" /><span class="i">'.$txt_absence_droit.'</span>'
      ;
    Json::add_str('<label class="tab">Appr. matière :</label>'.$select_releve_appreciation_rubrique_longueur.NL);
    Json::add_str('<span id="span_releve_appreciation_rubrique_report" class="'.$class_span_releve_appreciation_rubrique_report.'">'.NL);
    Json::add_str('  <label for="f_releve_appreciation_rubrique_report"><input type="checkbox" id="f_releve_appreciation_rubrique_report" name="f_releve_appreciation_rubrique_report" value="1"'.$check_releve_appreciation_rubrique_report.' /> à préremplir avec &hellip;</label>'.NL);
    Json::add_str('  <span id="span_releve_appreciation_rubrique_modele" class="'.$class_span_releve_appreciation_rubrique_modele.'">'.NL);
    Json::add_str('    <textarea id="f_releve_appreciation_rubrique_modele" name="f_releve_appreciation_rubrique_modele" rows="3" cols="50" maxlength="255">'.html($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_MODELE']).'</textarea>'.NL);
    Json::add_str('  </span>'.NL);
    Json::add_str('</span><br />'.NL);
    Json::add_str('<label class="tab">Appr. générale :</label>'.$select_releve_appreciation_generale_longueur.NL);
    Json::add_str('<span id="span_releve_appreciation_generale_report" class="'.$class_span_releve_appreciation_generale_report.'">'.NL);
    Json::add_str('  <label for="f_releve_appreciation_generale_report"><input type="checkbox" id="f_releve_appreciation_generale_report" name="f_releve_appreciation_generale_report" value="1"'.$check_releve_appreciation_generale_report.' /> à préremplir avec &hellip;</label>'.NL);
    Json::add_str('  <span id="span_releve_appreciation_generale_modele" class="'.$class_span_releve_appreciation_generale_modele.'">'.NL);
    Json::add_str('    <textarea id="f_releve_appreciation_generale_modele" name="f_releve_appreciation_generale_modele" rows="3" cols="50" maxlength="255">'.html($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_MODELE']).'</textarea>'.NL);
    Json::add_str('  </span>'.NL);
    Json::add_str('</span><br />'.NL);
    Json::add_str('<label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_releve_check_supplementaire" name="f_releve_check_supplementaire" value="1"'.$check_releve_ligne_supplementaire.' /> <input id="f_releve_ligne_factice" name="f_releve_ligne_factice" type="text" size="10" value="Sans objet." class="'.$class_input_releve_ligne_factice.'" disabled /><input id="f_releve_ligne_supplementaire" name="f_releve_ligne_supplementaire" type="text" size="120" maxlength="255" value="'.html($_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']).'" class="'.$class_input_releve_ligne_supplementaire.'" /><br />'.NL);
    Json::add_str('<label class="tab">Assiduité :</label><label for="f_releve_assiduite"><input type="checkbox" id="f_releve_assiduite" name="f_releve_assiduite" value="1"'.$check_releve_assiduite.' /> Reporter le nombre d\'absences et de retards</label><br />'.NL);
    Json::add_str('<label class="tab">Prof. Principal :</label><label for="f_releve_prof_principal"><input type="checkbox" id="f_releve_prof_principal" name="f_releve_prof_principal" value="1"'.$check_releve_prof_principal.' /> Indiquer le ou les professeurs principaux de la classe</label><br />'.NL);
    Json::add_str('<span class="radio">Prise en compte des évaluations antérieures :</span>'.NL);
    Json::add_str('  <label for="f_releve_retroactif_auto"><input type="radio" id="f_releve_retroactif_auto" name="f_releve_retroactif" value="auto"'.$check_releve_retroactif_auto.' /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_releve_retroactif_non"><input type="radio" id="f_releve_retroactif_non" name="f_releve_retroactif" value="non"'.$check_releve_retroactif_non.' /> non</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_releve_retroactif_oui"><input type="radio" id="f_releve_retroactif_oui" name="f_releve_retroactif" value="oui"'.$check_releve_retroactif_oui.' /> oui (sans limite)</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_releve_retroactif_annuel"><input type="radio" id="f_releve_retroactif_annuel" name="f_releve_retroactif" value="annuel"'.$check_releve_retroactif_annuel.' /> de l\'année scolaire</label><br />'.NL);
    Json::add_str('<label class="tab">Restrictions :</label>'.$select_releve_only_etat.'<br />'.NL);
    Json::add_str('<span class="tab"></span><label for="f_releve_only_socle"><input type="checkbox" id="f_releve_only_socle" name="f_releve_only_socle" value="1"'.$check_releve_only_socle.' /> Uniquement les items liés au socle</label><br />'.NL);
    Json::add_str('<label class="tab">Indications :</label><label for="f_releve_cases_auto"><input type="checkbox" id="f_releve_cases_auto" name="f_releve_cases_auto" value="1"'.$check_releve_cases_auto.' /> <span id="span_releve_cases_auto" class="'.$class_span_releve_cases_auto.'">cases d\'évaluation automatiques</span></label><span id="span_releve_cases_manuel" class="'.$class_span_releve_cases_manuel.'">'.$select_releve_cases_nb.' d\'évaluation de largeur '.$select_releve_cases_larg.'</span>&nbsp;&nbsp;&nbsp;<label for="f_releve_etat_acquisition"><input type="checkbox" id="f_releve_etat_acquisition" name="f_releve_etat_acquisition" value="1"'.$check_releve_etat_acquisition.' /> Colonne état d\'acquisition</label><span id="span_releve_etat_acquisition" class="'.$class_span_releve_etat_acquisition.'">&nbsp;&nbsp;&nbsp;<label for="f_releve_moyenne_scores"><input type="checkbox" id="f_releve_moyenne_scores" name="f_releve_moyenne_scores" value="1"'.$check_releve_moyenne_scores.' /> Ligne moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_pourcentage_acquis"><input type="checkbox" id="f_releve_pourcentage_acquis" name="f_releve_pourcentage_acquis" value="1"'.$check_releve_pourcentage_acquis.' /> Ligne pourcentage d\'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_conversion_sur_20" class="'.$class_label_releve_conversion_sur_20.'"><input type="checkbox" id="f_releve_conversion_sur_20" name="f_releve_conversion_sur_20" value="1"'.$check_releve_conversion_sur_20.' /> Conversion en note sur 20</label></span><br />'.NL);
    Json::add_str('<label class="tab">Infos items :</label><label for="f_releve_aff_reference"><input type="checkbox" id="f_releve_aff_reference" name="f_releve_aff_reference" value="1"'.$check_releve_aff_reference.' /> Références</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_coef"><input type="checkbox" id="f_releve_aff_coef" name="f_releve_aff_coef" value="1"'.$check_releve_aff_coef.' /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_socle"><input type="checkbox" id="f_releve_aff_socle" name="f_releve_aff_socle" value="1"'.$check_releve_aff_socle.' /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_domaine"><input type="checkbox" id="f_releve_aff_domaine" name="f_releve_aff_domaine" value="1"'.$check_releve_aff_domaine.' /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_theme"><input type="checkbox" id="f_releve_aff_theme" name="f_releve_aff_theme" value="1"'.$check_releve_aff_theme.' /> Thèmes</label><br />'.NL);
    Json::add_str('<label class="tab">Impression :</label>'.$select_releve_couleur.' '.$select_releve_fond.' '.$select_releve_legende.' '.$select_releve_pages_nb.'<br />'.NL);
    Json::add_str('<label class="tab">Envoi par courriel :</label>'.$texte_releve_envoi_mail_parent.NL);
    Json::end( TRUE );
  }
  if($type=='bulletin')
  {
    $select_bulletin_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_bulletin_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_bulletin_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_bulletin_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
    $select_bulletin_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_bulletin_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_FOND']                           /*selection*/ , '' /*optgroup*/ );
    $select_bulletin_legende                        = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_bulletin_legende'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_LEGENDE']                        /*selection*/ , '' /*optgroup*/ );
    $check_bulletin_appreciation_rubrique_report =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_REPORT'] ? ' checked' : '' ;
    $check_bulletin_appreciation_generale_report =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_REPORT'] ? ' checked' : '' ;
    $check_bulletin_ligne_supplementaire         =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']         ? ' checked' : '' ;
    $check_bulletin_assiduite                    =  $_SESSION['OFFICIEL']['BULLETIN_ASSIDUITE']                    ? ' checked' : '' ;
    $check_bulletin_prof_principal               =  $_SESSION['OFFICIEL']['BULLETIN_PROF_PRINCIPAL']               ? ' checked' : '' ;
    $check_bulletin_retroactif_auto              = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='auto')          ? ' checked' : '' ;
    $check_bulletin_retroactif_non               = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='non')           ? ' checked' : '' ;
    $check_bulletin_retroactif_oui               = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='oui')           ? ' checked' : '' ;
    $check_bulletin_retroactif_annuel            = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='annuel')        ? ' checked' : '' ;
    $check_bulletin_only_socle                   =  $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE']                   ? ' checked' : '' ;
    $check_bulletin_fusion_niveaux               =  $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX']               ? ' checked' : '' ;
    $check_bulletin_barre_acquisitions           =  $_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS']           ? ' checked' : '' ;
    $check_bulletin_acquis_texte_nombre          =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_NOMBRE']          ? ' checked' : '' ;
    $check_bulletin_acquis_texte_code            =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_CODE']            ? ' checked' : '' ;
    $check_bulletin_moyenne_scores               =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']               ? ' checked' : '' ;
    $check_bulletin_conversion_sur_20            =  $_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']            ? ' checked' : '' ;
    $check_bulletin_pourcentage                  = !$_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']            ? ' checked' : '' ;
    $check_bulletin_moyenne_classe               =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']               ? ' checked' : '' ;
    $check_bulletin_moyenne_generale             =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']             ? ' checked' : '' ;
    $check_bulletin_envoi_mail_parent            =  $_SESSION['OFFICIEL']['BULLETIN_ENVOI_MAIL_PARENT']            ? ' checked' : '' ;
    $class_span_bulletin_appreciation_rubrique_report = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR'] ? 'show' : 'hide' ;
    $class_span_bulletin_appreciation_rubrique_modele = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_REPORT']   ? 'show' : 'hide' ;
    $class_span_bulletin_appreciation_generale_report = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR'] ? 'show' : 'hide' ;
    $class_span_bulletin_appreciation_generale_modele = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_REPORT']   ? 'show' : 'hide' ;
    $class_input_bulletin_ligne_factice        = !$_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;
    $class_input_bulletin_ligne_supplementaire =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;
    $class_span_bulletin_moyennes              =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']                  ? 'show' : 'hide' ;
    $class_span_bulletin_moyenne_generale      =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR']  ? 'show' : 'hide' ;
    $texte_bulletin_envoi_mail_parent = (in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_BULLETIN_VOIR_ARCHIVE']) ))
      ? '<label for="f_bulletin_envoi_mail_parent"><input type="checkbox" id="f_bulletin_envoi_mail_parent" name="f_bulletin_envoi_mail_parent" value="1"'.$check_bulletin_envoi_mail_parent.' /> Envoyer aux parents un courriel avec un lien permettant de récupérer le bilan généré.</label>'
      : '<input type="checkbox" id="f_bulletin_envoi_mail_parent" name="f_bulletin_envoi_mail_parent" value="1"'.$check_bulletin_envoi_mail_parent.' class="hide" /><span class="i">'.$txt_absence_droit.'</span>'
      ;
    if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'])
    {
      $matiere_nombre = 'Sans exception (toutes matières avec moyennes)';
    }
    else
    {
      $nombre = substr_count($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'],',') + 1 ;
      $matiere_nombre = ($nombre==1) ? 'Une exception (matière sans moyenne)' : ' '.$nombre.' exceptions (matières sans moyennes)' ;
    }
    $matiere_liste = str_replace( ',' , '_' , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'] );
    Json::add_str('<label class="tab">Appr. matière :</label>'.$select_bulletin_appreciation_rubrique_longueur.NL);
    Json::add_str('<span id="span_bulletin_appreciation_rubrique_report" class="'.$class_span_bulletin_appreciation_rubrique_report.'">'.NL);
    Json::add_str('  <label for="f_bulletin_appreciation_rubrique_report"><input type="checkbox" id="f_bulletin_appreciation_rubrique_report" name="f_bulletin_appreciation_rubrique_report" value="1"'.$check_bulletin_appreciation_rubrique_report.' /> à préremplir avec &hellip;</label>'.NL);
    Json::add_str('  <span id="span_bulletin_appreciation_rubrique_modele" class="'.$class_span_bulletin_appreciation_rubrique_modele.'">'.NL);
    Json::add_str('    <textarea id="f_bulletin_appreciation_rubrique_modele" name="f_bulletin_appreciation_rubrique_modele" rows="3" cols="50" maxlength="255">'.html($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_MODELE']).'</textarea>'.NL);
    Json::add_str('  </span>'.NL);
    Json::add_str('</span><br />'.NL);
    Json::add_str('<label class="tab">Appr. générale :</label>'.$select_bulletin_appreciation_generale_longueur.NL);
    Json::add_str('<span id="span_bulletin_appreciation_generale_report" class="'.$class_span_bulletin_appreciation_generale_report.'">'.NL);
    Json::add_str('  <label for="f_bulletin_appreciation_generale_report"><input type="checkbox" id="f_bulletin_appreciation_generale_report" name="f_bulletin_appreciation_generale_report" value="1"'.$check_bulletin_appreciation_generale_report.' /> à préremplir avec &hellip;</label>'.NL);
    Json::add_str('  <span id="span_bulletin_appreciation_generale_modele" class="'.$class_span_bulletin_appreciation_generale_modele.'">'.NL);
    Json::add_str('    <textarea id="f_bulletin_appreciation_generale_modele" name="f_bulletin_appreciation_generale_modele" rows="3" cols="50" maxlength="255">'.html($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_MODELE']).'</textarea>'.NL);
    Json::add_str('  </span>'.NL);
    Json::add_str('</span><br />'.NL);
    Json::add_str('<label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_bulletin_check_supplementaire" name="f_bulletin_check_supplementaire" value="1"'.$check_bulletin_ligne_supplementaire.' /> <input id="f_bulletin_ligne_factice" name="f_bulletin_ligne_factice" type="text" size="10" value="Sans objet." class="'.$class_input_bulletin_ligne_factice.'" disabled /><input id="f_bulletin_ligne_supplementaire" name="f_bulletin_ligne_supplementaire" type="text" size="120" maxlength="255" value="'.html($_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']).'" class="'.$class_input_bulletin_ligne_supplementaire.'" /><br />'.NL);
    Json::add_str('<label class="tab">Assiduité :</label><label for="f_bulletin_assiduite"><input type="checkbox" id="f_bulletin_assiduite" name="f_bulletin_assiduite" value="1"'.$check_bulletin_assiduite.' /> Reporter le nombre d\'absences et de retards</label><br />'.NL);
    Json::add_str('<label class="tab">Prof. Principal :</label><label for="f_bulletin_prof_principal"><input type="checkbox" id="f_bulletin_prof_principal" name="f_bulletin_prof_principal" value="1"'.$check_bulletin_prof_principal.' /> Indiquer le ou les professeurs principaux de la classe</label><br />'.NL);
    Json::add_str('<span class="radio">Prise en compte des évaluations antérieures :</span>'.NL);
    Json::add_str('  <label for="f_bulletin_retroactif_auto"><input type="radio" id="f_bulletin_retroactif_auto" name="f_bulletin_retroactif" value="auto"'.$check_bulletin_retroactif_auto.' /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_bulletin_retroactif_non"><input type="radio" id="f_bulletin_retroactif_non" name="f_bulletin_retroactif" value="non"'.$check_bulletin_retroactif_non.' /> non</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_bulletin_retroactif_oui"><input type="radio" id="f_bulletin_retroactif_oui" name="f_bulletin_retroactif" value="oui"'.$check_bulletin_retroactif_oui.' /> oui</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_bulletin_retroactif_annuel"><input type="radio" id="f_bulletin_retroactif_annuel" name="f_bulletin_retroactif" value="annuel"'.$check_bulletin_retroactif_annuel.' /> de l\'année scolaire</label><br />'.NL);
    Json::add_str('<label class="tab">Restriction :</label><label for="f_bulletin_only_socle"><input type="checkbox" id="f_bulletin_only_socle" name="f_bulletin_only_socle" value="1"'.$check_bulletin_only_socle.' /> Uniquement les items liés au socle</label><br />'.NL);
    Json::add_str('<label class="tab">Mode de synthèse :</label><label for="f_bulletin_fusion_niveaux"><input type="checkbox" id="f_bulletin_fusion_niveaux" name="f_bulletin_fusion_niveaux" value="1"'.$check_bulletin_fusion_niveaux.' /> Ne pas indiquer le niveau et fusionner les synthèses de même intitulé</label><br />'.NL);
    Json::add_str('<label class="tab">Acquisitions :</label><label for="f_bulletin_barre_acquisitions"><input type="checkbox" id="f_bulletin_barre_acquisitions" name="f_bulletin_barre_acquisitions" value="1"'.$check_bulletin_barre_acquisitions.' /> Barre avec le total des états acquisitions par matière</label>'.NL);
    Json::add_str('&nbsp;&nbsp;&nbsp;<label for="f_bulletin_acquis_texte_nombre"><input type="checkbox" id="f_bulletin_acquis_texte_nombre" name="f_bulletin_acquis_texte_nombre" value="1"'.$check_bulletin_acquis_texte_nombre.' /> Écrire le nombre d\'items par catégorie</label>'.NL);
    Json::add_str('&nbsp;&nbsp;&nbsp;<label for="f_bulletin_acquis_texte_code"><input type="checkbox" id="f_bulletin_acquis_texte_code" name="f_bulletin_acquis_texte_code" value="1"'.$check_bulletin_acquis_texte_code.' /> Écrire la nature des catégories</label><br />'.NL);
    Json::add_str('<label class="tab">Moyennes :</label><label for="f_bulletin_moyenne_scores"><input type="checkbox" id="f_bulletin_moyenne_scores" name="f_bulletin_moyenne_scores" value="1"'.$check_bulletin_moyenne_scores.' /> Moyenne des scores</label>'.NL);
    Json::add_str('<span id="span_moyennes" class="'.$class_span_bulletin_moyennes.'">'.NL);
    Json::add_str('  [ <label for="f_bulletin_conversion_sur_20"><input type="radio" id="f_bulletin_conversion_sur_20" name="f_bulletin_conversion_sur_20" value="1"'.$check_bulletin_conversion_sur_20.' /> en note sur 20</label> | <label for="f_bulletin_pourcentage"><input type="radio" id="f_bulletin_pourcentage" name="f_bulletin_conversion_sur_20" value="0"'.$check_bulletin_pourcentage.' /> en pourcentage</label> ]&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_bulletin_moyenne_classe"><input type="checkbox" id="f_bulletin_moyenne_classe" name="f_bulletin_moyenne_classe" value="1"'.$check_bulletin_moyenne_classe.' /> Moyenne de la classe</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <span id="span_moyenne_generale" class="'.$class_span_bulletin_moyenne_generale.'">'.NL);
    Json::add_str('    <label for="f_bulletin_moyenne_generale"><input type="checkbox" id="f_bulletin_moyenne_generale" name="f_bulletin_moyenne_generale" value="1"'.$check_bulletin_moyenne_generale.' /> Moyenne générale</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  </span><br />'.NL);
    Json::add_str('  <span class="tab"></span><input id="f_matiere_nombre" name="f_matiere_nombre" size="40" type="text" value="'.$matiere_nombre.'" readonly /><input id="f_matiere_liste" name="f_matiere_liste" type="text" value="'.$matiere_liste.'" class="invisible" /><q class="choisir_compet" title="Voir ou choisir les matieres sans moyennes."></q>'.NL);
    Json::add_str('</span><br />'.NL);
    Json::add_str('<label class="tab">Impression :</label>'.$select_bulletin_couleur.' '.$select_bulletin_fond.' '.$select_bulletin_legende.'<br />'.NL);
    Json::add_str('<label class="tab">Envoi par courriel :</label>'.$texte_bulletin_envoi_mail_parent.NL);
    Json::end( TRUE );
  }
  if($type=='livret')
  {
    $select_livret_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_livret_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_livret_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_livret_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
    $select_livret_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_livret_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
    $select_livret_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_livret_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_FOND']                           /*selection*/ , '' /*optgroup*/ );
    $select_livret_import_bulletin_notes          = HtmlForm::afficher_select(Form::$tab_select_import_notes , 'f_livret_import_bulletin_notes'          /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES']          /*selection*/ , '' /*optgroup*/ );
    $check_livret_retroactif_auto              = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='auto')          ? ' checked' : '' ;
    $check_livret_retroactif_non               = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='non')           ? ' checked' : '' ;
    $check_livret_retroactif_oui               = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='oui')           ? ' checked' : '' ;
    $check_livret_retroactif_annuel            = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='annuel')        ? ' checked' : '' ;
    $check_livret_only_socle                   =  $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE']                   ? ' checked' : '' ;
    $check_livret_envoi_mail_parent            =  $_SESSION['OFFICIEL']['LIVRET_ENVOI_MAIL_PARENT']            ? ' checked' : '' ;
    $texte_livret_envoi_mail_parent = (in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ))
      ? '<label for="f_livret_envoi_mail_parent"><input type="checkbox" id="f_livret_envoi_mail_parent" name="f_livret_envoi_mail_parent" value="1"'.$check_livret_envoi_mail_parent.' /> Envoyer aux parents un courriel avec un lien permettant de récupérer le bilan généré.</label>'
      : '<input type="checkbox" id="f_livret_envoi_mail_parent" name="f_livret_envoi_mail_parent" value="1"'.$check_livret_envoi_mail_parent.' class="hide" /><span class="i">'.$txt_absence_droit.'</span>'
      ;
    // Limitation LSUN : appréciation matière non vide et max 600
    $tab_bad = array('value="0"'         ,'value="700"'         ,'value="800"'         ,'value="900"'         ,'value="999"'         );
    $tab_bon = array('value="0" disabled','value="700" disabled','value="800" disabled','value="900" disabled','value="999" disabled');
    $select_livret_appreciation_rubrique_longueur = str_replace( $tab_bad , $tab_bon , $select_livret_appreciation_rubrique_longueur );
    // Limitation LSUN : appréciation synthèse non vide et max 1000
    $tab_bad = array('value="0"'         );
    $tab_bon = array('value="0" disabled');
    $select_livret_appreciation_generale_longueur = str_replace( $tab_bad , $tab_bon , $select_livret_appreciation_generale_longueur );
    Json::add_str('<label class="tab">Appr. matière :</label>'.$select_livret_appreciation_rubrique_longueur.'<br />'.NL);
    Json::add_str('<label class="tab">Appr. générale :</label>'.$select_livret_appreciation_generale_longueur.'<br />'.NL);
    Json::add_str('<label class="tab">Impression :</label>'.$select_livret_couleur.' '.$select_livret_fond.'<br />'.NL);
    Json::add_str('<label class="tab">Envoi par courriel :</label>'.$texte_livret_envoi_mail_parent.NL);
    Json::add_str('<h3>Si récupération possible depuis un bulletin scolaire</h3>'.NL);
    Json::add_str('<label class="tab">Positionnement :</label>'.$select_livret_import_bulletin_notes.NL);
    Json::add_str('<h3>Si récupération impossible depuis un bulletin scolaire</h3>'.NL);
    Json::add_str('<span class="radio">Prise en compte des évaluations antérieures :</span>'.NL);
    Json::add_str('  <label for="f_livret_retroactif_auto"><input type="radio" id="f_livret_retroactif_auto" name="f_livret_retroactif" value="auto"'.$check_livret_retroactif_auto.' /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_livret_retroactif_non"><input type="radio" id="f_livret_retroactif_non" name="f_livret_retroactif" value="non"'.$check_livret_retroactif_non.' /> non</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_livret_retroactif_oui"><input type="radio" id="f_livret_retroactif_oui" name="f_livret_retroactif" value="oui"'.$check_livret_retroactif_oui.' /> oui</label>&nbsp;&nbsp;&nbsp;'.NL);
    Json::add_str('  <label for="f_livret_retroactif_annuel"><input type="radio" id="f_livret_retroactif_annuel" name="f_livret_retroactif" value="annuel"'.$check_livret_retroactif_annuel.' /> de l\'année scolaire</label><br />'.NL);
    Json::add_str('<label class="tab">Restriction :</label><label for="f_livret_only_socle"><input type="checkbox" id="f_livret_only_socle" name="f_livret_only_socle" value="1"'.$check_livret_only_socle.' /> Uniquement les items liés au socle</label>'.NL);
    Json::end( TRUE );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les paramètres transmis
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') || ($action=='modifier') )
{
  $tab_configuration = array();
  if($type=='releve')
  {
    $tab_configuration['appreciation_rubrique_longueur'] = (isset($_POST['f_releve_appreciation_rubrique_longueur'])) ? Clean::entier($_POST['f_releve_appreciation_rubrique_longueur']) : 0;
    $tab_configuration['appreciation_rubrique_report']   = (isset($_POST['f_releve_appreciation_rubrique_report']))   ? 1                                                                : 0;
    $tab_configuration['appreciation_rubrique_modele']   = (isset($_POST['f_releve_appreciation_rubrique_modele']))   ? Clean::texte($_POST['f_releve_appreciation_rubrique_modele'])    : '';
    $tab_configuration['appreciation_generale_longueur'] = (isset($_POST['f_releve_appreciation_generale_longueur'])) ? Clean::entier($_POST['f_releve_appreciation_generale_longueur']) : 0;
    $tab_configuration['appreciation_generale_report']   = (isset($_POST['f_releve_appreciation_generale_report']))   ? 1                                                                : 0;
    $tab_configuration['appreciation_generale_modele']   = (isset($_POST['f_releve_appreciation_generale_modele']))   ? Clean::texte($_POST['f_releve_appreciation_generale_modele'])    : '';
    $tab_configuration['ligne_supplementaire']           = (isset($_POST['f_releve_check_supplementaire']))           ? Clean::texte($_POST['f_releve_ligne_supplementaire'])            : '';
    $tab_configuration['assiduite']                      = (isset($_POST['f_releve_assiduite']))                      ? 1                                                                : 0;
    $tab_configuration['prof_principal']                 = (isset($_POST['f_releve_prof_principal']))                 ? 1                                                                : 0;
    $tab_configuration['retroactif']                     = (isset($_POST['f_releve_retroactif']))                     ? Clean::calcul_retroactif($_POST['f_releve_retroactif'])          : '';
    $tab_configuration['only_etat']                      = (isset($_POST['f_releve_only_etat']))                      ? Clean::texte($_POST['f_releve_only_etat'])                       : '';
    $tab_configuration['only_socle']                     = (isset($_POST['f_releve_only_socle']))                     ? 1                                                                : 0;
    $tab_configuration['etat_acquisition']               = (isset($_POST['f_releve_etat_acquisition']))               ? 1                                                                : 0;
    $tab_configuration['moyenne_scores']                 = (isset($_POST['f_releve_moyenne_scores']))                 ? 1                                                                : 0;
    $tab_configuration['pourcentage_acquis']             = (isset($_POST['f_releve_pourcentage_acquis']))             ? 1                                                                : 0;
    $tab_configuration['conversion_sur_20']              = (isset($_POST['f_releve_conversion_sur_20']))              ? 1                                                                : 0;
    $tab_configuration['cases_auto']                     = (isset($_POST['f_releve_cases_auto']))                     ? 1                                                                : 0;
    $tab_configuration['cases_nb']                       = (isset($_POST['f_releve_cases_nb']))                       ? Clean::entier($_POST['f_releve_cases_nb'])                       : 0;
    $tab_configuration['cases_largeur']                  = (isset($_POST['f_releve_cases_largeur']))                  ? Clean::entier($_POST['f_releve_cases_largeur'])                  : 0;
    $tab_configuration['aff_reference']                  = (isset($_POST['f_releve_aff_reference']))                  ? 1                                                                : 0;
    $tab_configuration['aff_coef']                       = (isset($_POST['f_releve_aff_coef']))                       ? 1                                                                : 0;
    $tab_configuration['aff_socle']                      = (isset($_POST['f_releve_aff_socle']))                      ? 1                                                                : 0;
    $tab_configuration['aff_domaine']                    = (isset($_POST['f_releve_aff_domaine']))                    ? 1                                                                : 0;
    $tab_configuration['aff_theme']                      = (isset($_POST['f_releve_aff_theme']))                      ? 1                                                                : 0;
    $tab_configuration['couleur']                        = (isset($_POST['f_releve_couleur']))                        ? Clean::texte($_POST['f_releve_couleur'])                         : '';
    $tab_configuration['fond']                           = (isset($_POST['f_releve_fond']))                           ? Clean::texte($_POST['f_releve_fond'])                            : '';
    $tab_configuration['legende']                        = (isset($_POST['f_releve_legende']))                        ? Clean::texte($_POST['f_releve_legende'])                         : '';
    $tab_configuration['pages_nb']                       = (isset($_POST['f_releve_pages_nb']))                       ? Clean::texte($_POST['f_releve_pages_nb'])                        : '';
    $tab_configuration['envoi_mail_parent']              = (isset($_POST['f_releve_envoi_mail_parent']))              ? 1                                                                : 0;
  }
  if($type=='bulletin')
  {
    $tab_configuration['appreciation_rubrique_longueur'] = (isset($_POST['f_bulletin_appreciation_rubrique_longueur'])) ? Clean::entier($_POST['f_bulletin_appreciation_rubrique_longueur']) : 0;
    $tab_configuration['appreciation_rubrique_report']   = (isset($_POST['f_bulletin_appreciation_rubrique_report']))   ? 1                                                                  : 0;
    $tab_configuration['appreciation_rubrique_modele']   = (isset($_POST['f_bulletin_appreciation_rubrique_modele']))   ? Clean::texte($_POST['f_bulletin_appreciation_rubrique_modele'])    : '';
    $tab_configuration['appreciation_generale_longueur'] = (isset($_POST['f_bulletin_appreciation_generale_longueur'])) ? Clean::entier($_POST['f_bulletin_appreciation_generale_longueur']) : 0;
    $tab_configuration['appreciation_generale_report']   = (isset($_POST['f_bulletin_appreciation_generale_report']))   ? 1                                                                  : 0;
    $tab_configuration['appreciation_generale_modele']   = (isset($_POST['f_bulletin_appreciation_generale_modele']))   ? Clean::texte($_POST['f_bulletin_appreciation_generale_modele'])    : '';
    $tab_configuration['ligne_supplementaire']           = (isset($_POST['f_bulletin_check_supplementaire']))           ? Clean::texte($_POST['f_bulletin_ligne_supplementaire'])            : '';
    $tab_configuration['assiduite']                      = (isset($_POST['f_bulletin_assiduite']))                      ? 1                                                                  : 0;
    $tab_configuration['prof_principal']                 = (isset($_POST['f_bulletin_prof_principal']))                 ? 1                                                                  : 0;
    $tab_configuration['retroactif']                     = (isset($_POST['f_bulletin_retroactif']))                     ? Clean::calcul_retroactif($_POST['f_bulletin_retroactif'])          : '';
    $tab_configuration['only_socle']                     = (isset($_POST['f_bulletin_only_socle']))                     ? 1                                                                  : 0;
    $tab_configuration['fusion_niveaux']                 = (isset($_POST['f_bulletin_fusion_niveaux']))                 ? 1                                                                  : 0;
    $tab_configuration['barre_acquisitions']             = (isset($_POST['f_bulletin_barre_acquisitions']))             ? 1                                                                  : 0;
    $tab_configuration['acquis_texte_nombre']            = (isset($_POST['f_bulletin_acquis_texte_nombre']))            ? 1                                                                  : 0;
    $tab_configuration['acquis_texte_code']              = (isset($_POST['f_bulletin_acquis_texte_code']))              ? 1                                                                  : 0;
    $tab_configuration['moyenne_scores']                 = (isset($_POST['f_bulletin_moyenne_scores']))                 ? 1                                                                  : 0;
    $tab_configuration['conversion_sur_20']              = (isset($_POST['f_bulletin_conversion_sur_20']))              ? Clean::entier($_POST['f_bulletin_conversion_sur_20'])              : 0; // Est transmis à 0 si f_bulletin_pourcentage coché
    $tab_configuration['moyenne_classe']                 = (isset($_POST['f_bulletin_moyenne_classe']))                 ? 1                                                                  : 0;
    $tab_configuration['moyenne_generale']               = (isset($_POST['f_bulletin_moyenne_generale']))               ? 1                                                                  : 0;
    $tab_configuration['couleur']                        = (isset($_POST['f_bulletin_couleur']))                        ? Clean::texte($_POST['f_bulletin_couleur'])                         : '';
    $tab_configuration['fond']                           = (isset($_POST['f_bulletin_fond']))                           ? Clean::texte($_POST['f_bulletin_fond'])                            : '';
    $tab_configuration['legende']                        = (isset($_POST['f_bulletin_legende']))                        ? Clean::texte($_POST['f_bulletin_legende'])                         : '';
    $tab_configuration['envoi_mail_parent']              = (isset($_POST['f_bulletin_envoi_mail_parent']))              ? 1                                                                  : 0;
    // Liste de matières transmises
    $tab_matieres = (isset($_POST['f_matiere_liste']))  ? explode('_',$_POST['f_matiere_liste'])  : array() ;
    $tab_matieres = Clean::map('entier',$tab_matieres);
    $tab_matieres = array_filter($tab_matieres,'positif');
    $tab_configuration['moyenne_exception_matieres'] = implode(',',$tab_matieres);
  }
  if($type=='livret')
  {
    $tab_configuration['appreciation_rubrique_longueur'] = (isset($_POST['f_livret_appreciation_rubrique_longueur'])) ? Clean::entier($_POST['f_livret_appreciation_rubrique_longueur']) : 0;
    $tab_configuration['appreciation_generale_longueur'] = (isset($_POST['f_livret_appreciation_generale_longueur'])) ? Clean::entier($_POST['f_livret_appreciation_generale_longueur']) : 0;
    $tab_configuration['import_bulletin_notes']          = (isset($_POST['f_livret_import_bulletin_notes']))          ? Clean::texte($_POST['f_livret_import_bulletin_notes'])           : '';
    $tab_configuration['retroactif']                     = (isset($_POST['f_livret_retroactif']))                     ? Clean::calcul_retroactif($_POST['f_livret_retroactif'])          : '';
    $tab_configuration['only_socle']                     = (isset($_POST['f_livret_only_socle']))                     ? 1                                                                : 0;
    $tab_configuration['couleur']                        = (isset($_POST['f_livret_couleur']))                        ? Clean::texte($_POST['f_livret_couleur'])                         : '';
    $tab_configuration['fond']                           = (isset($_POST['f_livret_fond']))                           ? Clean::texte($_POST['f_livret_fond'])                            : '';
    $tab_configuration['envoi_mail_parent']              = (isset($_POST['f_livret_envoi_mail_parent']))              ? 1                                                                : 0;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle configuration
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $reference && $nom )
{
  // Vérifier que la référence est disponible
  if( DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_reference( $type , $reference ) )
  {
    Json::end( FALSE , 'Référence déjà utilisée !' );
  }
  // Vérifier que la description est disponible
  if( DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_nom( $type , $nom ) )
  {
    Json::end( FALSE , 'Description déjà utilisée !' );
  }
  // Insérer en BDD
  DB_STRUCTURE_OFFICIEL_CONFIG::DB_ajouter_configuration( $type , $reference , $nom , $tab_configuration );
  // Afficher le retour
  Json::add_row( 'html' ,'<tr id="'.$type.'_'.$reference.'" class="new">');
  Json::add_row( 'html' ,  '<td><i>'.$tab_types[$type]['ordre'].'</i>'.$tab_types[$type]['nom'].'</td>');
  Json::add_row( 'html' ,  '<td>'.$reference.'</td>');
  Json::add_row( 'html' ,  '<td>'.html($nom).'</td>');
  Json::add_row( 'html' ,  '<td class="nu">');
  Json::add_row( 'html' ,    '<q class="ajouter" title="Ajouter une configuration (à partir de celle-ci)."></q>');
  Json::add_row( 'html' ,    '<q class="modifier" title="Modifier cette configuration."></q>');
  Json::add_row( 'html' ,    '<q class="supprimer" title="Supprimer cette configuration."></q>');
  Json::add_row( 'html' ,  '</td>');
  Json::add_row( 'html' ,'</tr>');
  Json::add_row( 'option' ,'<option value="'.$reference.'">'.html($nom).'</option>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une configuration existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $reference && $nom )
{
  // Vérifier que la référence est existante
  if( !DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_reference( $type , $reference ) )
  {
    Json::end( FALSE , 'Référence introuvable !' );
  }
  // Vérifier que la description est disponible
  if( DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_nom( $type , $nom , $reference ) )
  {
    Json::end( FALSE , 'Description déjà utilisée !' );
  }
  // Modifier en BDD
  DB_STRUCTURE_OFFICIEL_CONFIG::DB_modifier_configuration( $type , $reference , $nom , $tab_configuration );
  // Actualiser aussi en session
  if( isset($_SESSION['OFFICIEL'][Clean::upper($type).'_CONFIG_REF']) && ($_SESSION['OFFICIEL'][Clean::upper($type).'_CONFIG_REF']==$reference) )
  {
    foreach($tab_configuration as $key => $val)
    {
      $_SESSION['OFFICIEL'][Clean::upper($type.'_'.$key)] = $val;
    }
  }
  // Afficher le retour
  $q_supprimer = ($reference!='defaut') ? '<q class="supprimer" title="Supprimer cette configuration."></q>' : '<q class="supprimer_non" title="La configuration par défaut ne peut pas être supprimée."></q>' ;
  Json::add_row( 'html' ,'<td><i>'.$tab_types[$type]['ordre'].'</i>'.$tab_types[$type]['nom'].'</td>');
  Json::add_row( 'html' ,'<td>'.$reference.'</td>');
  Json::add_row( 'html' ,'<td>'.html($nom).'</td>');
  Json::add_row( 'html' ,'<td class="nu">');
  Json::add_row( 'html' ,  '<q class="ajouter" title="Ajouter une configuration (à partir de celle-ci)."></q>');
  Json::add_row( 'html' ,  '<q class="modifier" title="Modifier cette configuration."></q>');
  Json::add_row( 'html' ,  $q_supprimer);
  Json::add_row( 'html' ,'</td>');
  Json::add_row( 'texte' ,html($nom));
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une configuration existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $reference )
{
  if($reference=='defaut')
  {
    Json::end( FALSE , 'La configuration par défaut ne doit pas être supprimée !' );
  }
  // Vérifier que la référence est existante
  if( !DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_reference( $type , $reference ) )
  {
    Json::end( FALSE , 'Référence introuvable !' );
  }
  // Modifier en BDD
  DB_STRUCTURE_OFFICIEL_CONFIG::DB_supprimer_configuration( $type , $reference );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affecter une configuration à une classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='affecter') && $reference && $classe_id )
{
  // Vérifier que la référence est existante
  if( !DB_STRUCTURE_OFFICIEL_CONFIG::DB_tester_reference( $type , $reference ) )
  {
    Json::end( FALSE , 'Référence introuvable !' );
  }
  // Modifier en BDD
  DB_STRUCTURE_OFFICIEL_CONFIG::DB_modifier_classe_config_ref( $classe_id , $type , $reference );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
