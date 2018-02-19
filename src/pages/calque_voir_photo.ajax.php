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

// On récupère l'identifiant de l'élève
$user_id = (isset($_GET['user_id'])) ? (int)$_GET['user_id'] : -1 ;
if($user_id==-1)
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// On récupère la photo
// $user_id=0 possible si mode bulletin et consultation des données sur le groupe classe (pas un élève en particulier)
$DB_ROW = ($user_id) ? DB_STRUCTURE_IMAGE::DB_recuperer_image( $user_id , 'photo' ) : NULL ;
$image = (!empty($DB_ROW)) ? '<img width="'.$DB_ROW['image_largeur'].'" height="'.$DB_ROW['image_hauteur'].'" src="data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$DB_ROW['image_contenu'].'" alt="" />' : '<img width="'.(PHOTO_DIMENSION_MAXI*2/3).'" height="'.PHOTO_DIMENSION_MAXI.'" src="./_img/trombinoscope_vide.png" alt="" title="absence de photo" />' ;

// On affiche le résultat
Json::end( TRUE , $image );

?>
