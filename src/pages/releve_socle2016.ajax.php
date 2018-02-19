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

$cycle_id                  = (isset($_POST['f_cycle']))                    ? Clean::entier($_POST['f_cycle'])                   : 0;
$cycle_nom                 = (isset($_POST['f_cycle_nom']))                ? Clean::texte($_POST['f_cycle_nom'])                : '';
$socle_detail              = (isset($_POST['f_socle_detail']))             ? Clean::texte($_POST['f_socle_detail'])             : '';
$socle_individuel_format   = (isset($_POST['f_socle_individuel_format']))  ? Clean::texte($_POST['f_socle_individuel_format'])  : '';
$socle_synthese_format     = (isset($_POST['f_socle_synthese_format']))    ? Clean::texte($_POST['f_socle_synthese_format'])    : '';
$socle_synthese_affichage  = (isset($_POST['f_socle_synthese_affichage'])) ? Clean::texte($_POST['f_socle_synthese_affichage']) : '';
$tableau_tri_maitrise_mode = (isset($_POST['f_tri_maitrise_mode']))        ? Clean::texte($_POST['f_tri_maitrise_mode'])        : '';
$groupe_id                 = (isset($_POST['f_groupe']))                   ? Clean::entier($_POST['f_groupe'])                  : 0;
$groupe_nom                = (isset($_POST['f_groupe_nom']))               ? Clean::texte($_POST['f_groupe_nom'])               : '';
$groupe_type               = (isset($_POST['f_groupe_type']))              ? Clean::lettres($_POST['f_groupe_type'])            : '';
$eleves_ordre              = (isset($_POST['f_eleves_ordre']))             ? Clean::texte($_POST['f_eleves_ordre'])             : '';
$mode                      = (isset($_POST['f_mode']))                     ? Clean::texte($_POST['f_mode'])                     : '';
$matiere_nom               = (isset($_POST['f_matiere_nom']))              ? Clean::texte($_POST['f_matiere_nom'])              : '';
$aff_socle_items_acquis    = (isset($_POST['f_socle_items_acquis']))       ? 1                                                  : 0;
$aff_socle_position        = (isset($_POST['f_socle_position']))           ? 1                                                  : 0;
$aff_socle_points_DNB      = (isset($_POST['f_socle_points_dnb']))         ? 1                                                  : 0;
$only_presence             = (isset($_POST['f_only_presence']))            ? 1                                                  : 0;
$aff_lien                  = (isset($_POST['f_lien']))                     ? 1                                                  : 0;
$aff_start                 = (isset($_POST['f_start']))                    ? 1                                                  : 0;
$couleur                   = (isset($_POST['f_couleur']))                  ? Clean::texte($_POST['f_couleur'])                  : '';
$fond                      = (isset($_POST['f_fond']))                     ? Clean::texte($_POST['f_fond'])                     : '';
$legende                   = (isset($_POST['f_legende']))                  ? Clean::texte($_POST['f_legende'])                  : '';
$marge_min                 = (isset($_POST['f_marge_min']))                ? Clean::entier($_POST['f_marge_min'])               : 0;
$pages_nb                  = (isset($_POST['f_pages_nb']))                 ? Clean::texte($_POST['f_pages_nb'])                 : '';
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve   = (isset($_POST['f_eleve']))   ? ( is_array( $_POST['f_eleve'])  ? $_POST['f_eleve']   : explode(',',$_POST['f_eleve'])   ) : array() ;
$tab_matiere = (isset($_POST['f_matiere'])) ? ( is_array($_POST['f_matiere']) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_type    = (isset($_POST['f_type']))    ? ( is_array( $_POST['f_type'])   ? $_POST['f_type']    : explode(',',$_POST['f_type'])    ) : array() ;
$tab_eleve   = array_filter( Clean::map('entier',$tab_eleve)   , 'positif' );
$tab_matiere = array_filter( Clean::map('entier',$tab_matiere) , 'positif' );
$tab_type  = Clean::map('texte',$tab_type);

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $aff_socle_position   = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PROPOSITION_POSITIONNEMENT']) ? $aff_socle_position   : 0 ;
  $aff_socle_points_DNB = Outil::test_user_droit_specifique($_SESSION['DROIT_SOCLE_PREVISION_POINTS_BREVET'])    ? $aff_socle_points_DNB : 0 ;
  $tab_type          = array('individuel');
  $only_presence     = 0;
  // Pour un élève on surcharge avec les données de session
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $groupe_id    = $_SESSION['ELEVE_CLASSE_ID'];
    $tab_eleve = array($_SESSION['USER_ID']);
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

if( !$cycle_id || !$cycle_nom || !$groupe_id || !$groupe_nom || !$groupe_type || !count($tab_eleve) || !count($tab_type) || !$tableau_tri_maitrise_mode || !in_array($mode,array('auto','manuel')) || !$couleur || !$fond || !$legende || !$marge_min || !$pages_nb || !$eleves_ordre || ( $type_synthese && !in_array($socle_synthese_affichage,array('pourcentage','position','points')) ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

Form::save_choix('releve_socle2016');

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

$aff_socle_points_DNB = ( ($cycle_id==4) && ($socle_detail=='livret') ) ? $aff_socle_points_DNB : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_officiel = FALSE;
$make_livret   = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;

require(CHEMIN_DOSSIER_INCLUDE.'noyau_socle2016.php');

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
    $retour .= '</ul>'.NL;
  }
  if($type_synthese)
  {
    $retour .= '<h2>Synthèse collective</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.str_replace('<REPLACE>','synthese',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    $retour .= '</ul>'.NL;
  }
}

Json::add_tab( array(
  'direct' => $affichage_direct ,
  'bilan'  => $retour ,
) );
Json::end( TRUE );

?>
