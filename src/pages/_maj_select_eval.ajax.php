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

$objet       = (isset($_POST['f_objet']))       ? Clean::texte($_POST['f_objet'])        : ''; // 'demande_eval_prof' | 'releve_items'
$eval_type   = (isset($_POST['f_eval_type']))   ? Clean::texte($_POST['f_eval_type'])    : ''; // 'groupe' ou 'select'
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])   : 0;  // utile uniquement pour $eval_type='groupe'

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel depuis [releve_items.js] pour mettre à jour l'élément de formulaire "f_evaluation"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($objet=='releve_items')
{
  if(!$groupe_id)
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Affichage du retour.
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_OPT_eleves_devoirs_prof_groupe( $_SESSION['USER_ID'] , $groupe_id );
  if(!is_array($DB_TAB))
  {
    Json::end( FALSE , $DB_TAB );  // Aucune évaluation trouvée.
  }
  else
  {
    foreach($DB_TAB as $key => $DB_ROW)
    {
      $date_affich   = To::date_mysql_to_french($DB_ROW['devoir_date']).' || ';
      $groupe_affich = ($DB_ROW['groupe_nom']) ? $DB_ROW['groupe_nom'].' || ' : 'sélection || ' ;
      $DB_TAB[$key]['texte'] = $date_affich.$groupe_affich.$DB_TAB[$key]['texte'];
      unset( $DB_TAB[$key]['devoir_date'] , $DB_TAB[$key]['groupe_nom'] );
    }
    Json::end( TRUE , HtmlForm::afficher_select( $DB_TAB , 'f_evaluation' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ ) );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel depuis [evaluation_demande_professeur.js] pour mettre à jour l'élément de formulaire "f_devoir"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($objet=='demande_eval_prof')
{
  $tab_types = array('groupe','select');
  if( ( (!$groupe_id) && ($eval_type=='groupe') ) || (!in_array($eval_type,$tab_types)) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Lister les dernières évaluations d'une classe ou d'un groupe ou d'un groupe de besoin
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_devoirs_prof_groupe_sans_infos_last($_SESSION['USER_ID'],$groupe_id,$eval_type);

  if(empty($DB_TAB))
  {
    Json::end( TRUE , '<option value="" disabled>Aucun devoir n\'a été trouvé pour ce groupe d\'élèves !</option>' );
  }
  // Affichage du retour.
  foreach($DB_TAB as $key => $DB_ROW)
  {
    // Le code js a besoin qu'une option soit sélectionnée
    $selected = $key ? '' : ' selected' ;
    // Formater la date et la référence de l'évaluation
    $date_affich         = To::date_mysql_to_french($DB_ROW['devoir_date']);
    $date_visible_affich = To::date_mysql_to_french($DB_ROW['devoir_visible_date']);
    $option_val = $DB_ROW['devoir_id'].'_'.$DB_ROW['groupe_id'];
    $option_txt = $date_affich.' || '.$date_visible_affich.' || '.html($DB_ROW['devoir_info']);
    Json::add_str('<option value="'.$option_val.'"'.$selected.'>'.$option_txt.'</option>');
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );
?>
