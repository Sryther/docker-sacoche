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
// Générer une synthèse d'items
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$synthese_modele = (isset($_POST['f_objet']))              ? Clean::texte($_POST['f_objet'])                  : '';
$matiere_id      = (isset($_POST['f_matiere']))            ? Clean::entier($_POST['f_matiere'])               : 0;
$matiere_nom     = (isset($_POST['f_matiere_nom']))        ? Clean::texte($_POST['f_matiere_nom'])            : '';
$groupe_id       = (isset($_POST['f_groupe']))             ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_nom      = (isset($_POST['f_groupe_nom']))         ? Clean::texte($_POST['f_groupe_nom'])             : '';
$groupe_type     = (isset($_POST['f_groupe_type']))        ? Clean::lettres($_POST['f_groupe_type'])          : '';
$periode_id      = (isset($_POST['f_periode']))            ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut      = (isset($_POST['f_date_debut']))         ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin        = (isset($_POST['f_date_fin']))           ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif      = (isset($_POST['f_retroactif']))         ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$niveau_id       = (isset($_POST['f_niveau']))             ? Clean::entier($_POST['f_niveau'])                : 0; // Niveau transmis uniquement si on restreint sur un niveau
$fusion_niveaux  = (isset($_POST['f_fusion_niveaux']))     ? 1                                                : 0;
$aff_coef        = (isset($_POST['f_coef']))               ? 1                                                : 0;
$aff_socle       = (isset($_POST['f_socle']))              ? 1                                                : 0;
$aff_lien        = (isset($_POST['f_lien']))               ? 1                                                : 0;
$aff_start       = (isset($_POST['f_start']))              ? 1                                                : 0;
$only_socle      = (isset($_POST['f_restriction_socle']))  ? 1                                                : 0;
$only_niveau     = (isset($_POST['f_restriction_niveau'])) ? $niveau_id                                       : 0;
$mode_synthese   = (isset($_POST['f_mode_synthese']))      ? Clean::texte($_POST['f_mode_synthese'])          : '';
$couleur         = (isset($_POST['f_couleur']))            ? Clean::texte($_POST['f_couleur'])                : '';
$fond            = (isset($_POST['f_fond']))               ? Clean::texte($_POST['f_fond'])                   : '';
$legende         = (isset($_POST['f_legende']))            ? Clean::texte($_POST['f_legende'])                : '';
$marge_min       = (isset($_POST['f_marge_min']))          ? Clean::entier($_POST['f_marge_min'])             : 0;
$eleves_ordre    = (isset($_POST['f_eleves_ordre']))       ? Clean::texte($_POST['f_eleves_ordre'])           : '';
// Normalement c'est un tableau qui est transmis, mais au cas où...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  // Pour un élève on surcharge avec les données de session
  $groupe_id  = $_SESSION['ELEVE_CLASSE_ID'];
  $groupe_nom = $_SESSION['ELEVE_CLASSE_NOM'];
  $tab_eleve  = array($_SESSION['USER_ID']);
}
if($_SESSION['USER_PROFIL_TYPE']=='parent')
{
  // Pour un parent on vérifie que c'est bien un de ses enfants
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

$liste_eleve   = implode(',',$tab_eleve);

$tab_modele = array(
  'matiere'      => TRUE,
  'multimatiere' => TRUE,
);

if( !isset($tab_modele[$synthese_modele]) || ( ($synthese_modele=='matiere') && ( !$matiere_id || !$matiere_nom || !$mode_synthese ) ) || !$groupe_id || !$groupe_nom || !$groupe_type || !count($tab_eleve) || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$couleur || !$fond || !$legende || !$marge_min || !$eleves_ordre )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

Form::save_choix('releve_synthese');

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_officiel = FALSE;
$make_brevet   = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;
$make_graph    = FALSE;

require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_synthese.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$retour = '';

if($affichage_direct)
{
  $retour .= '<hr />'.NL;
  $retour .= '<ul class="puce">'.NL;
  $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  $retour .= '</ul>'.NL;
  $retour .= $releve_HTML;
}
else
{
  $retour .= '<ul class="puce">'.NL;
  $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.$fichier_nom.'"><span class="file file_htm">Explorer / Détailler (format <em>html</em>).</span></a></li>'.NL;
  $retour .= '</ul>'.NL;
}

Json::add_tab( array(
  'direct' => $affichage_direct ,
  'bilan'  => $retour ,
) );
Json::end( TRUE );

?>
