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

$groupe_id     = (isset($_POST['f_groupe']))    ? Clean::entier($_POST['f_groupe'])    : NULL ;
$periode       = (isset($_POST['f_periode']))   ? Clean::id($_POST['f_periode'])       : NULL ;
$jointure      = (isset($_POST['f_jointure']))  ? Clean::entier($_POST['f_jointure'])  : NULL ;
$cycle         = (isset($_POST['f_cycle']))     ? Clean::id($_POST['f_cycle'])         : NULL ;
$chef_id       = (isset($_POST['f_chef']))      ? Clean::entier($_POST['f_chef'])      : NULL;
$etabl_chef_id = (isset($_POST['f_chefetabl'])) ? Clean::entier($_POST['f_chefetabl']) : NULL;

if( !$etabl_chef_id && ( !$groupe_id || is_null($periode) || is_null($cycle) || is_null($chef_id) || ( $periode && !$jointure ) || ( !$chef_id && ( $periode || $cycle ) ) ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imposer le chef d'établ à toutes les classes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $etabl_chef_id )
{
  DB_STRUCTURE_REGROUPEMENT::DB_modifier_chef( $etabl_chef_id );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier les jointures au livret pour une classe donnée, ainsi que le chef responsable
// ////////////////////////////////////////////////////////////////////////////////////////////////////

DB_STRUCTURE_REGROUPEMENT::DB_modifier_chef( $chef_id , $groupe_id );

// On récupère déjà l'existant
$tab_jointures_old = array(
  'periode' => FALSE ,
  'cycle'   => FALSE ,
);
$jointure_old = FALSE;
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_classes_avec_jointures_livret( $groupe_id );
foreach($DB_TAB as $DB_ROW)
{
  if(!is_null($DB_ROW['livret_page_ref']))
  {
    $tab_jointures_old[$DB_ROW['periodicite']] = $DB_ROW['livret_page_ref'];
    if($DB_ROW['listing_periodes'])
    {
      $jointure_old = $DB_ROW['listing_periodes']{0};
    }
  }
}

// Maintenant on compare et on modifie en fonction
// C'est plus simple de DELETE / INSERT que UPDATE, en particulier pour les périodes ou la fréquence peut être différente.
// On n'efface pas les saisies éventuelles, mais l'état revient sur "1vide".
$tab_jointure_periode = array(
  2 => array(21,22),
  3 => array(31,32,33),
  4 => array(41,42,43,44),
  5 => array(51,52,53,54,55),
);

foreach($tab_jointures_old as $periodicite => $page_ref_old)
{
  $page_ref_new = ${$periodicite};
  $test_update_jointure = ( $periodicite == 'periode') && ( $jointure_old != $jointure ) ;
  $test_delete = $page_ref_old && !$page_ref_new ;
  $test_insert = $page_ref_new && !$page_ref_old ;
  $test_update = $page_ref_old && $page_ref_new && ( ( $page_ref_old != $page_ref_new ) || $test_update_jointure ) ;
  if( $test_delete || $test_update )
  {
    DB_STRUCTURE_LIVRET::DB_supprimer_jointure_groupe( $groupe_id , $page_ref_old , $periodicite );
  }
  if( $test_insert || $test_update )
  {
    $jointure_periode = ( $periodicite != 'periode') ? array(NULL) : $tab_jointure_periode[$jointure] ;
    DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $groupe_id , $page_ref_new , $periodicite , $jointure_periode );
  }
}

Json::end( TRUE );

?>
