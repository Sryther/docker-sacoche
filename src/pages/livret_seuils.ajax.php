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

$action     = (isset($_POST['f_action']))           ? Clean::texte($_POST['f_action'])            : '';
$page_ref   = (isset($_POST['f_page_ref']))         ? Clean::id($_POST['f_page_ref'])             : '';
$colonne    = (isset($_POST['choix_'.$page_ref]))   ? Clean::id($_POST['choix_'.$page_ref])       : '';
$moy_classe = (isset($_POST['moyenne_'.$page_ref])) ? Clean::entier($_POST['moyenne_'.$page_ref]) : 0; // pour le 1er degré
$colonne_id = (isset($_POST['f_colonne_id']))       ? Clean::entier($_POST['f_colonne_id'])       : '';
$legende    = (isset($_POST['f_colonne_legende']))  ? Clean::texte($_POST['f_colonne_legende'])   : '';

$tab_colonne_choix = array('moyenne','pourcentage','position','objectif');
$tab_colonne_id = array(
  'reussite' => array(11,12,13),
  'objectif' => array(21,22,23,24),
  'maitrise' => array(31,32,33,34),
  'position' => array(41,42,43,44),
);

if( ($action=='memoriser_legende') && in_array($colonne_id,$tab_colonne_id['position']) && $legende )
{
  DB_STRUCTURE_LIVRET::DB_modifier_legende( $colonne_id , $legende );
  Json::end( TRUE );
}

if( ($action!='enregistrer_choix') || !$page_ref )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

$DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_page_info($page_ref);

if( empty($DB_ROW) || !$DB_ROW['livret_page_rubrique_type'] /*brevet*/ )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

if( in_array( $DB_ROW['livret_page_colonne'] , $tab_colonne_choix ) )
{
  if( !in_array( $colonne , $tab_colonne_choix ) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  else if( ($moy_classe !== 0 ) && ( $moy_classe !== 1 ) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  else
  {
    $tab_verif_id = ( ($colonne == 'position') || ($colonne == 'objectif') ) ? $tab_colonne_id[$colonne] : NULL ;
  }
}
else
{
  $tab_verif_id = $tab_colonne_id[$DB_ROW['livret_page_colonne']];
}

if($tab_verif_id)
{
  // On récupère les valeurs, on vérifie leur présence, mais on ne revérifie pas toutes les conditions (valeurs distinctes ou croissantes, etc.).
  $tab_seuils = array();
  foreach($tab_verif_id as $colonne_id)
  {
    $clef_debut = 'seuil_'.$page_ref.'_'.$colonne_id;
    $tab_seuils[$colonne_id]['min'] = isset($_POST[$clef_debut.'_min']) ? Clean::entier($_POST[$clef_debut.'_min']) : NULL ;
    $tab_seuils[$colonne_id]['max'] = isset($_POST[$clef_debut.'_max']) ? Clean::entier($_POST[$clef_debut.'_max']) : NULL ;
    if( is_null($tab_seuils[$colonne_id]['min']) || is_null($tab_seuils[$colonne_id]['max']) )
    {
      Json::end( FALSE , 'Erreur avec les données transmises !' );
    }
  }
  DB_STRUCTURE_LIVRET::DB_modifier_seuils( $page_ref , $tab_seuils );
}
if( in_array( $colonne , $tab_colonne_choix )  && ( ( $colonne != $DB_ROW['livret_page_colonne'] ) || ( $moy_classe != $DB_ROW['livret_page_moyenne_classe'] ) ) )
{
  DB_STRUCTURE_LIVRET::DB_modifier_page_colonne( $page_ref , $colonne , $moy_classe );
}

Json::end( TRUE );


?>
