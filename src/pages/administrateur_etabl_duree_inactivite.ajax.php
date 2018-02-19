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

$profil = (isset($_POST['f_profil'])) ? Clean::lettres($_POST['f_profil']) : '' ;
$delai  = (isset($_POST['f_delai']))  ? Clean::entier($_POST['f_delai'])   : 0  ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix du délai avant une déconnexion automatique pour inactivité
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($profil && $delai)
{
  if( ($profil!='ALL') && !isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
  {
    Json::end( FALSE , 'Profil incorrect !' );
  }
  if( ($delai%10) || ($delai>120) )
  {
    Json::end( FALSE , 'Délai incorrect !' );
  }
  // Mettre à jour les paramètres dans la base
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre( $profil , 'user_profil_duree_inactivite' , $delai );
  // Mettre aussi à jour la session
  if( ($profil=='ALL') || ($profil=='ADM') )
  {
    $_SESSION['USER_DUREE_INACTIVITE'] = $delai;
  }
  if($profil=='ALL')
  {
    $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'] = array_fill_keys ( $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'] , $delai );
  }
  else
  {
    $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'][$profil] = $delai;
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
