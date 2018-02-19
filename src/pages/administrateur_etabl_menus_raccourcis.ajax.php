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

$profil = isset($_POST['f_profil']) ? Clean::code($_POST['f_profil']) : '';

$tab_profil = array(
  'eleve'      => 'élèves',
  'parent'     => 'parents',
  'professeur' => 'professeurs',
  'directeur'  => 'directeurs',
);

if( !isset($tab_profil[$profil]) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer les menu et favori pour ce profil (si tout est décoché alors rien n'est transmis)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_memo_menu   = array() ;
$tab_memo_favori = array() ;

// récupère $tab_menu & $tab_sous_menu
require(CHEMIN_DOSSIER_MENUS.'menu_'.$profil.'.php');

foreach($tab_menu as $menu_id => $menu_titre)
{
  if( !isset($_POST['menu_'.$menu_id]) )
  {
     $tab_memo_menu[] = $menu_id;
  }
  else
  {
    foreach($tab_sous_menu[$menu_id] as $sous_menu_id => $tab)
    {
      if( !isset($_POST['sousmenu_'.$sous_menu_id]) )
      {
         $tab_memo_menu[] = $sous_menu_id;
      }
      if( isset($_POST['favori_'.$sous_menu_id]) )
      {
         $tab_memo_favori[] = $sous_menu_id;
      }
    }
  }
}

DB_STRUCTURE_PARAMETRE::DB_modifier_parametre_profil( $profil , implode(',',$tab_memo_menu) , implode(',',$tab_memo_favori) );

// Retour
Json::end( TRUE );

?>