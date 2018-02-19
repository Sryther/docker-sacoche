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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Reporter des notes -> redirection vers la page pour le traiter
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (isset($_POST['f_action'])) && ($_POST['f_action']=='reporter_notes') )
{
  require(CHEMIN_DOSSIER_INCLUDE.'code_report_notes_releve_to_bulletin.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Autres cas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$releve_modele            = (isset($_POST['f_objet']))              ? Clean::texte($_POST['f_objet'])                  : '';
$releve_individuel_format = (isset($_POST['f_individuel_format']))  ? Clean::texte($_POST['f_individuel_format'])      : '';
$aff_etat_acquisition     = (isset($_POST['f_etat_acquisition']))   ? 1                                                : 0;
$aff_moyenne_scores       = (isset($_POST['f_moyenne_scores']))     ? 1                                                : 0;
$aff_pourcentage_acquis   = (isset($_POST['f_pourcentage_acquis'])) ? 1                                                : 0;
$conversion_sur_20        = (isset($_POST['f_conversion_sur_20']))  ? 1                                                : 0;
$tableau_synthese_format  = (isset($_POST['f_synthese_format']))    ? Clean::texte($_POST['f_synthese_format'])        : '';
$tableau_tri_etat_mode    = (isset($_POST['f_tri_etat_mode']))      ? Clean::texte($_POST['f_tri_etat_mode'])          : '';
$repeter_entete           = (isset($_POST['f_repeter_entete']))     ? 1                                                : 0;
$with_coef                = (isset($_POST['f_with_coef']))          ? 1                                                : 0;
$groupe_id                = (isset($_POST['f_groupe']))             ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_nom               = (isset($_POST['f_groupe_nom']))         ? Clean::texte($_POST['f_groupe_nom'])             : '';
$groupe_type              = (isset($_POST['f_groupe_type']))        ? Clean::lettres($_POST['f_groupe_type'])          : '';
$matiere_id               = (isset($_POST['f_matiere']))            ? Clean::entier($_POST['f_matiere'])               : 0;
$matiere_nom              = (isset($_POST['f_matiere_nom']))        ? Clean::texte($_POST['f_matiere_nom'])            : '';
$periode_id               = (isset($_POST['f_periode']))            ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut               = (isset($_POST['f_date_debut']))         ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin                 = (isset($_POST['f_date_fin']))           ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif               = (isset($_POST['f_retroactif']))         ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$only_etat                = (isset($_POST['f_only_etat']))          ? Clean::texte($_POST['f_only_etat'])              : '';
$only_socle               = (isset($_POST['f_only_socle']))         ? 1                                                : 0;
$aff_reference            = (isset($_POST['f_reference']))          ? 1                                                : 0;
$aff_coef                 = (isset($_POST['f_coef']))               ? 1                                                : 0;
$aff_socle                = (isset($_POST['f_socle']))              ? 1                                                : 0;
$aff_comm                 = (isset($_POST['f_comm']))               ? 1                                                : 0;
$aff_lien                 = (isset($_POST['f_lien']))               ? 1                                                : 0;
$aff_domaine              = (isset($_POST['f_domaine']))            ? 1                                                : 0;
$aff_theme                = (isset($_POST['f_theme']))              ? 1                                                : 0;
$orientation              = (isset($_POST['f_orientation']))        ? Clean::texte($_POST['f_orientation'])            : '';
$couleur                  = (isset($_POST['f_couleur']))            ? Clean::texte($_POST['f_couleur'])                : '';
$fond                     = (isset($_POST['f_fond']))               ? Clean::texte($_POST['f_fond'])                   : '';
$legende                  = (isset($_POST['f_legende']))            ? Clean::texte($_POST['f_legende'])                : '';
$marge_min                = (isset($_POST['f_marge_min']))          ? Clean::entier($_POST['f_marge_min'])             : 0;
$pages_nb                 = (isset($_POST['f_pages_nb']))           ? Clean::texte($_POST['f_pages_nb'])               : '';
$cases_auto               = (isset($_POST['f_cases_auto']))         ? 1                                                : 0;
$cases_nb                 = (isset($_POST['f_cases_nb']))           ? Clean::entier($_POST['f_cases_nb'])              : -1;
$cases_largeur            = (isset($_POST['f_cases_larg']))         ? Clean::entier($_POST['f_cases_larg'])            : 0;
$eleves_ordre             = (isset($_POST['f_eleves_ordre']))       ? Clean::texte($_POST['f_eleves_ordre'])           : '';
$prof_id                  = (isset($_POST['f_prof']))               ? Clean::entier($_POST['f_prof'])                  : 0;
$prof_texte               = (isset($_POST['f_prof_texte']))         ? Clean::texte($_POST['f_prof_texte'])             : '';
$highlight_id             = (isset($_POST['f_highlight_id']))       ? Clean::entier($_POST['f_highlight_id'])          : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve = (isset($_POST['f_eleve']))        ? ( (is_array($_POST['f_eleve']))        ? $_POST['f_eleve']        : explode(',',$_POST['f_eleve'])        ) : array() ;
$tab_type  = (isset($_POST['f_type']))         ? ( (is_array($_POST['f_type']))         ? $_POST['f_type']         : explode(',',$_POST['f_type'])         ) : array() ;
$tab_items = (isset($_POST['f_compet_liste'])) ? ( (is_array($_POST['f_compet_liste'])) ? $_POST['f_compet_liste'] : explode('_',$_POST['f_compet_liste']) ) : array() ;
$tab_evals = (isset($_POST['f_evaluation']))   ? ( (is_array($_POST['f_evaluation']))   ? $_POST['f_evaluation']   : explode(',',$_POST['f_evaluation'])   ) : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );
$tab_items = array_filter( Clean::map('entier',$tab_items) , 'positif' );
$tab_evals = array_filter( Clean::map('entier',$tab_evals) , 'positif' );
$tab_type  = Clean::map('texte',$tab_type);

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
if($releve_modele=='multimatiere')
{
  $tab_type = array('individuel');
}
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $releve_individuel_format = 'eleve';
  $aff_moyenne_scores       = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? $aff_moyenne_scores     : 0 ;
  $aff_pourcentage_acquis   = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? $aff_pourcentage_acquis : 0 ;
  $conversion_sur_20        = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? $conversion_sur_20      : 0 ;
  $tab_type                 = array('individuel');
  // Pour un élève on surcharge avec les données de session
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $groupe_id  = $_SESSION['ELEVE_CLASSE_ID'];
    $groupe_nom = $_SESSION['ELEVE_CLASSE_NOM'];
    $tab_eleve  = array($_SESSION['USER_ID']);
  }
  // Pour un parent on vérifie que c'est bien un de ses enfants
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $is_enfant_legitime = FALSE;
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      if($DB_ROW['valeur']==$tab_eleve[0])
      {
        $is_enfant_legitime = TRUE;
        break;
      }
    }
    if(!$is_enfant_legitime)
    {
      Json::end( FALSE , 'Enfant non rattaché à votre compte parent !' );
    }
  }
}
if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') )
{
  // Pour un professeur on vérifie que ce sont bien ses élèves
  $tab_eleves_non_rattaches = array_diff( $tab_eleve , $_SESSION['PROF_TAB_ELEVES'] );
  if(!empty($tab_eleves_non_rattaches))
  {
    // On vérifie de nouveau, au cas où l'admin viendrait d'ajouter une affectation
    $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    $tab_eleves_non_rattaches = array_diff( $tab_eleve , $_SESSION['PROF_TAB_ELEVES'] );
    if(!empty($tab_eleves_non_rattaches))
    {
      Json::end( FALSE , 'Élève(s) non rattaché(s) à votre compte enseignant !' );
    }
  }
}

$type_individuel = (in_array('individuel',$tab_type)) ? 1 : 0 ;
$type_synthese   = (in_array('synthese',$tab_type))   ? 1 : 0 ;
$type_bulletin   = (in_array('bulletin',$tab_type))   ? 1 : 0 ;

$liste_eleve = implode(',',$tab_eleve);

$tab_modele = array(
  'matiere'      => TRUE,
  'multimatiere' => TRUE,
  'selection'    => TRUE,
  'evaluation'   => TRUE,
  'professeur'   => TRUE,
);

if( !isset($tab_modele[$releve_modele]) || !$orientation || !$couleur || !$fond || !$legende || !$marge_min || !$pages_nb || ($cases_nb<0) || !$cases_largeur || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$only_etat || ( ($releve_modele=='matiere') && ( !$matiere_id || !$matiere_nom ) ) || ( ($releve_modele=='professeur') && !$prof_id ) || ( ($releve_modele=='evaluation') && !count($tab_evals) ) || ( ($releve_modele=='selection') && !count($tab_items) ) || !$groupe_id || !$groupe_nom || !$groupe_type || !count($tab_eleve) || !count($tab_type) || !$eleves_ordre )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

Form::save_choix('releve_items');

if($releve_modele=='evaluation')
{
  $retroactif = 'non';
}

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_officiel = FALSE;
$make_brevet   = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;
$make_csv      = ( ( ($releve_modele=='multimatiere') && ($releve_individuel_format == 'eleve') ) || $type_synthese ) ? TRUE : FALSE ;
$make_graph    = FALSE;

require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$retour = '';

if($affichage_direct)
{
  $retour .= '<hr />'.NL;
  $retour .= '<ul class="puce">'.NL;
  $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  $retour .= '</ul>'.NL;
  $retour .= $releve_HTML_individuel;
}
else
{
  if($type_individuel)
  {
    $retour .= '<h2>Relevé individuel</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.str_replace('<REPLACE>','individuel',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    if($make_csv)
    {
      $retour .= '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.csv"><span class="file file_txt">Exploitation tableur (format <em>csv</em>).</span></a></li>'.NL;
    }
    $retour .= '</ul>'.NL;
  }
  if($type_synthese)
  {
    $retour .= '<h2>Synthèse collective</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.str_replace('<REPLACE>','synthese',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.csv"><span class="file file_txt">Exploitation tableur (format <em>csv</em>).</span></a></li>'.NL;
    $retour .= '</ul>'.NL;
  }
  if($type_bulletin)
  {
    $retour .= '<h2>Moyenne sur 20 - Élément d\'appréciation</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','bulletin',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.str_replace('<REPLACE>','bulletin',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    $retour .= '</ul>'.NL;
    if($_SESSION['USER_PROFIL_TYPE']=='professeur')
    {
      $retour .= '<h2>Bulletin SACoche</h2>'.NL;
      $retour .= '<ul class="puce">'.NL;
      $retour .= $bulletin_form;
      $retour .= '</ul>'.NL;
      $retour .= $bulletin_alerte;
      $retour .= '<h2>Bulletin Gepi</h2>'.NL;
      $retour .= '<ul class="puce">'.NL;
      $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_note_appreciation'            ,$fichier_nom).'.csv"><span class="file file_txt">Récupérer notes (moyennes scores) et appréciations (% items acquis) à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
      $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_note'                         ,$fichier_nom).'.csv"><span class="file file_txt">Récupérer les notes (moyennes scores) à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
      $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_appreciation_pourcent_acquis' ,$fichier_nom).'.csv"><span class="file file_txt">Récupérer les appréciations (% items acquis) à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
      $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.str_replace('<REPLACE>','bulletin_appreciation_moyennes_scores' ,$fichier_nom).'.csv"><span class="file file_txt">Récupérer les appréciations (moyennes scores) à importer dans GEPI (format <em>csv</em>).</span></a></li>'.NL;
      $retour .= '</ul>'.NL;
      $retour .= '<h2>Devoir Pronote</h2>'.NL;
      $retour .= '<ul class="puce">'.NL;
      $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.str_replace('<REPLACE>','pronote',$fichier_nom).'.csv"><span class="file file_txt">Récupérer les notes (moyennes scores) et appréciations (% items acquis) à importer dans Pronote (format <em>csv</em>).</span></a></li>'.NL;
      $retour .= '</ul>'.NL;
    }
  }
}

Json::add_tab( array(
  'direct' => $affichage_direct ,
  'bilan'  => $retour ,
) );
Json::end( TRUE );

?>
