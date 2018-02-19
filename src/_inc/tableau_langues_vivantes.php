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

// Tableau avec la liste des langues vivantes qu'il est possible d'affecter à l'élève

$tab_code_bcn_to_pays = array(
  301 => 109, // Allemand
  302 => 132, // Anglais
  303 => 201, // Arabe
  304 => 216, // Chinois
  305 => 101, // Danois
  306 => 134, // Espagnol
  309 => 127, // Italien
  310 => 217, // Japonais
  312 => 135, // Néerlandais
  313 => 122, // Polonais
  311 => 139, // Portugais
  314 => 123, // Russe
);

$tab_langues[109] = array( 'valeur'=>109 , 'texte'=>'Allemand'        , 'tab_matiere_id'=>array(9902, 301, 315, 327, 339, 351, 363, 375, 387) );
$tab_langues[132] = array( 'valeur'=>132 , 'texte'=>'Anglais'         , 'tab_matiere_id'=>array(9902, 302, 316, 328, 340, 352, 364, 376, 388) );
$tab_langues[201] = array( 'valeur'=>201 , 'texte'=>'Arabe'           , 'tab_matiere_id'=>array(9902, 303, 317, 329, 341, 353, 365, 377, 389) );
$tab_langues[216] = array( 'valeur'=>216 , 'texte'=>'Chinois'         , 'tab_matiere_id'=>array(9902, 304, 318, 330, 342, 354, 366, 378, 390) );
$tab_langues[101] = array( 'valeur'=>101 , 'texte'=>'Danois'          , 'tab_matiere_id'=>array(9902, 305, 319, 331, 343, 355, 367, 379, 391) );
$tab_langues[134] = array( 'valeur'=>134 , 'texte'=>'Espagnol'        , 'tab_matiere_id'=>array(9902, 306, 320, 332, 344, 356, 368, 380, 392) );
$tab_langues[127] = array( 'valeur'=>127 , 'texte'=>'Italien'         , 'tab_matiere_id'=>array(9902, 309, 321, 333, 345, 357, 369, 381, 393) );
$tab_langues[217] = array( 'valeur'=>217 , 'texte'=>'Japonais'        , 'tab_matiere_id'=>array(9902, 310, 322, 334, 346, 358, 370, 382, 394) );
$tab_langues[135] = array( 'valeur'=>135 , 'texte'=>'Néerlandais'     , 'tab_matiere_id'=>array(9902, 312, 324, 336, 348, 360, 372, 384, 396) );
$tab_langues[122] = array( 'valeur'=>122 , 'texte'=>'Polonais'        , 'tab_matiere_id'=>array(9902, 313, 325, 337, 349, 361, 373, 385, 397) );
$tab_langues[139] = array( 'valeur'=>139 , 'texte'=>'Portuguais'      , 'tab_matiere_id'=>array(9902, 311, 323, 335, 347, 359, 371, 383, 395) );
$tab_langues[123] = array( 'valeur'=>123 , 'texte'=>'Russe'           , 'tab_matiere_id'=>array(9902, 314, 326, 338, 350, 362, 374, 386, 398) );
$tab_langues[100] = array( 'valeur'=>100 , 'texte'=>'LV non affectée' , 'tab_matiere_id'=>array_unique(array_merge(
  $tab_langues[109]['tab_matiere_id'],
  $tab_langues[132]['tab_matiere_id'],
  $tab_langues[201]['tab_matiere_id'],
  $tab_langues[216]['tab_matiere_id'],
  $tab_langues[101]['tab_matiere_id'],
  $tab_langues[134]['tab_matiere_id'],
  $tab_langues[127]['tab_matiere_id'],
  $tab_langues[217]['tab_matiere_id'],
  $tab_langues[135]['tab_matiere_id'],
  $tab_langues[122]['tab_matiere_id'],
  $tab_langues[139]['tab_matiere_id'],
  $tab_langues[123]['tab_matiere_id']
)) );
?>