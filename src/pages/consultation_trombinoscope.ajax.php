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

$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::lettres($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])    : 0;
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? Clean::texte($_POST['f_groupe_nom'])    : '';

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les élèves et leurs photos si existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (!$groupe_id) || (!$groupe_nom) || (!isset($tab_types[$groupe_type])) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}
// On récupère les élèves
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ ) ;
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucun élève trouvé dans ce regroupement.' );
}
$tab_vignettes = array();
$img_height = PHOTO_DIMENSION_MAXI;
$img_width  = PHOTO_DIMENSION_MAXI*2/3;
foreach($DB_TAB as $DB_ROW)
{
  $tab_vignettes[$DB_ROW['user_id']] = array(
    'user_nom'    => $DB_ROW['user_nom'],
    'user_prenom' => $DB_ROW['user_prenom'],
    'img_width'   => $img_width,
    'img_height'  => $img_height,
    'img_src'     => '',
    'img_title'   => TRUE,
  );
}
// On récupère les photos
$listing_user_id = implode(',',array_keys($tab_vignettes));
$DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images( $listing_user_id , 'photo' );
if(!empty($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_vignettes[$DB_ROW['user_id']]['img_width']  = $DB_ROW['image_largeur'];
    $tab_vignettes[$DB_ROW['user_id']]['img_height'] = $DB_ROW['image_hauteur'];
    $tab_vignettes[$DB_ROW['user_id']]['img_src']    = $DB_ROW['image_contenu'];
    $tab_vignettes[$DB_ROW['user_id']]['img_title']  = FALSE;
  }
}
// Génération de la sortie HTML (affichée directement) et de la sortie PDF (enregistrée dans un fichier)
$fnom_pdf = 'trombinoscope_'.$_SESSION['BASE'].'_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.pdf';
$trombinoscope_HTML = '<h2>'.html($groupe_nom).'</h2><p><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fnom_pdf.'"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a> &rarr; <span class="noprint">Afin de préserver l\'environnement, n\'imprimer que si nécessaire !</span></p>';
$trombinoscope_PDF = new PDF_trombinoscope( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 5 /*marge_gauche*/ , 5 /*marge_droite*/ , 5 /*marge_haut*/ , 7 /*marge_bas*/ );
$trombinoscope_PDF->initialiser($groupe_nom);
// On passe les élèves en revue (on a toutes les infos déjà disponibles)
foreach($tab_vignettes as $user_id => $tab)
{
  $trombinoscope_PDF->vignette($tab);
  $img_src   = ($tab['img_src'])   ? ' src="data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$tab['img_src'].'"' : ' src="./_img/trombinoscope_vide.png"' ;
  $img_title = ($tab['img_title']) ? ' title="absence de photo"' : '' ;
  $trombinoscope_HTML .= '<div id="div_'.$user_id.'" class="photo"><img width="'.$tab['img_width'].'" height="'.$tab['img_height'].'" alt=""'.$img_src.$img_title.' /><br />'.html($tab['user_nom']).'<br />'.html($tab['user_prenom']).'</div>';
}
// Enregistrement du PDF
FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fnom_pdf , $trombinoscope_PDF );
// Affichage du HTML
Json::end( TRUE , $trombinoscope_HTML );

?>
