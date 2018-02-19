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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '' ;
$umask  = (isset($_POST['f_umask']))  ? Clean::texte($_POST['f_umask'])  : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Droits du système de fichiers - Choix UMASK
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='choix_umask')
{
  $tab_chmod = array(
    '000' => '777 / 666',
    '002' => '775 / 664',
    '022' => '755 / 644',
    '026' => '751 / 640',
  );
  if(!isset($tab_chmod[$umask]))
  {
    Json::end( FALSE , 'Valeur transmise inattendue ('.$umask.') !' );
  }
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array('SYSTEME_UMASK'=>$umask) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Droits du système de fichiers - Appliquer CHMOD
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='appliquer_chmod')
{
  // Récupérer l'arborescence
  $dossier_install = '.';
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , TRUE /*with_first_dir*/ );
  // Pour l'affichage du retour
  $thead = '<tr><td colspan="2">Modification des droits du système de fichiers - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = '';
  // Dossiers
  $mode_dossier = octdec( 777 - SYSTEME_UMASK ); // On ne peut pas passer une variable en octal et chmod() accepte le format décimal (c'est juste que c'est moins lisible).
  ksort(FileSystem::$tab_analyse['dossier']);
  foreach(FileSystem::$tab_analyse['dossier'] as $dossier => $tab)
  {
    $dossier = ($dossier) ? '.'.$dossier : '.'.DS ;
    $tbody .= (@chmod($dossier,$mode_dossier)) ? '<tr><td class="v">Droits appliqués au dossier</td><td>'.$dossier.'</td></tr>' : '<tr><td class="r">Permission insuffisante sur ce dossier pour en modifier les droits</td><td>'.$dossier.'</td></tr>' ;
  }
  // Fichiers
  $mode_fichier = octdec( 666 - SYSTEME_UMASK ); // On ne peut pas passer une variable en octal et chmod() accepte le format décimal (c'est juste que c'est moins lisible).
  ksort(FileSystem::$tab_analyse['fichier']);
  foreach(FileSystem::$tab_analyse['fichier'] as $fichier => $tab)
  {
    $fichier = '.'.$fichier;
    $tbody .= (@chmod($fichier,$mode_fichier)) ? '<tr><td class="v">Droits appliqués au fichier</td><td>'.$fichier.'</td></tr>' : '<tr><td class="r">Permission insuffisante sur ce fichier pour en modifier les droits</td><td>'.$fichier.'</td></tr>' ;
  }
  // Enregistrement du rapport
  $fichier_nom = 'rapport_chmod_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody );
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des droits en écriture
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='verif_droits')
{
  // Récupérer l'arborescence
  $dossier_install = '.';
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , TRUE /*with_first_dir*/ );
  // Pour l'affichage du retour
  $thead = '<tr><td colspan="2">Vérification des droits en écriture - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = '';
  // Dossiers
  ksort(FileSystem::$tab_analyse['dossier']);
  foreach(FileSystem::$tab_analyse['dossier'] as $dossier => $tab)
  {
    $dossier = ($dossier) ? '.'.$dossier : '.'.DS ;
    $tbody .= (@is_writable($dossier)) ? '<tr><td class="v">Dossier accessible en écriture</td><td>'.$dossier.'</td></tr>' : '<tr><td class="r">Dossier aux droits insuffisants</td><td>'.$dossier.'</td></tr>' ;
  }
  // Fichiers
  ksort(FileSystem::$tab_analyse['fichier']);
  foreach(FileSystem::$tab_analyse['fichier'] as $fichier => $tab)
  {
    $fichier = '.'.$fichier;
    $tbody .= (@is_writable($fichier)) ? '<tr><td class="v">Fichier accessible en écriture</td><td>'.$fichier.'</td></tr>' : '<tr><td class="r">Fichier aux droits insuffisants</td><td>'.$fichier.'</td></tr>' ;
  }
  // Enregistrement du rapport
  $fichier_nom = 'rapport_droits_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody );
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
