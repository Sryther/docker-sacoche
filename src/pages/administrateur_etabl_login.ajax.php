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
$login  = (isset($_POST['f_login']))  ? Clean::texte($_POST['f_login'])    : '' ;
$mdp    = (isset($_POST['f_mdp']))    ? Clean::entier($_POST['f_mdp'])     : 0  ;
$birth  = (isset($_POST['f_birth']))  ? Clean::entier($_POST['f_birth'])   : -1 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix d'un format de noms d'utilisateurs et/ou de la longueur minimale d'un mot de passe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($profil && $login && $mdp && ($birth!=-1))
{
  if( ($profil!='ALL') && !isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
  {
    Json::end( FALSE , 'Profil incorrect !' );
  }
  if( ($mdp<4) || ($mdp>8) )
  {
    Json::end( FALSE , 'Longueur du mot de passe incorrecte !' );
  }
  $test_profil_1 = (preg_match("#^p+[._-]?n+$#", $login)) ? TRUE : FALSE ; // prénom puis nom
  $test_profil_2 = (preg_match("#^n+[._-]?p+$#", $login)) ? TRUE : FALSE ; // nom puis prénom
  $test_profil_3 = (preg_match("#^p+$#", $login)) ? TRUE : FALSE ; // prénom seul
  $test_profil_4 = (preg_match("#^n+$#", $login)) ? TRUE : FALSE ; // nom seul
  if( !$test_profil_1 && !$test_profil_2 && !$test_profil_3 && !$test_profil_4 )
  {
    Json::end( FALSE , 'Modèle de nom d\'utilisateur incorrect !' );
  }
  // Mettre à jour les paramètres dans la base
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre( $profil , 'user_profil_login_modele'    , $login );
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre( $profil , 'user_profil_mdp_longueur_mini' , $mdp );
  if($profil=='ELV')
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre( $profil , 'user_profil_mdp_date_naissance' , $birth );
  }
  // Mettre aussi à jour la session
  if($profil=='ALL')
  {
    $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE']      = array_fill_keys ( array_keys($_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE']     ) , $login );
    $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] = array_fill_keys ( array_keys($_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI']) , $mdp   );
  }
  else
  {
    $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'][$profil]      = $login;
    $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'][$profil] = $mdp;
  }
  if($profil=='ELV')
  {
    $_SESSION['TAB_PROFILS_ADMIN']['MDP_DATE_NAISSANCE'][$profil] = $birth;
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
