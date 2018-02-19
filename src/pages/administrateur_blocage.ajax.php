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

$action     = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])     : '';
$motif      = (isset($_POST['f_motif']))  ? Clean::texte($_POST['f_motif'])      : '';
$tab_profil = (isset($_POST['f_profil'])) ? Clean::map('ref',$_POST['f_profil']) : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquer l'application
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='bloquer') && $motif && !empty($tab_profil) )
{
  $profils = in_array('ALL',$tab_profil) ? 'ALL' : implode(',',$tab_profil) ;
  LockAcces::bloquer_application( $_SESSION['USER_PROFIL_TYPE'] , $_SESSION['BASE'] , $motif.NL.$profils );
  Json::end( TRUE , '<label class="erreur">Application fermée : '.html($motif).'</label>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Débloquer l'application
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='debloquer')
{
  LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],$_SESSION['BASE']);
  Json::end( TRUE , '<label class="valide">Application accessible.</label>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
