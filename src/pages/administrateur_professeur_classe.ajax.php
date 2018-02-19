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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action     = (isset($_POST['f_action']))   ? $_POST['f_action']                 : '';
$classe_id  = (isset($_POST['classe_id']))  ? Clean::entier($_POST['classe_id']) : 0 ;
$prof_id    = (isset($_POST['prof_id']))    ? Clean::entier($_POST['prof_id'])   : 0 ;
$tab_modifs = (isset($_POST['tab_modifs'])) ? explode(',',$_POST['tab_modifs'])  : array() ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter | Retirer un professeur à une classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array($action,array('ajouter','retirer')) && count($tab_modifs) )
{
  $etat = ($action=='ajouter') ? TRUE : FALSE ;
  foreach($tab_modifs as $key => $id_modifs)
  {
    list($classe_id,$prof_id) = explode('_',$id_modifs);
    $classe_id = Clean::entier($classe_id);
    $prof_id   = Clean::entier($prof_id);
    if($classe_id && $prof_id)
    {
      DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_user_groupe_par_admin( $prof_id , 'professeur' , $classe_id , 'classe' , $etat );
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter | Retirer une affectation en tant que professeur principal
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array($action,array('ajouter_pp','retirer_pp')) && ($classe_id) && ($prof_id) )
{
  $etat = ($action=='ajouter_pp') ? TRUE : FALSE ;
  DB_STRUCTURE_REGROUPEMENT::DB_modifier_liaison_professeur_principal( $prof_id , $classe_id , $etat );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>