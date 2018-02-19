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

$f_user    = (isset($_POST['f_user']))    ? Clean::texte($_POST['f_user'])    : '' ;
$f_mail    = (isset($_POST['f_mail']))    ? Clean::texte($_POST['f_mail'])    : '' ;
$f_domaine = (isset($_POST['f_domaine'])) ? Clean::texte($_POST['f_domaine']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer des nouveaux réglages
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array($f_user,array('oui','non')) && in_array($f_mail,array('oui','non','domaine')) && ( ($f_mail!='domaine') || ($f_domaine) ) )
{
  if($f_mail=='domaine')
  {
    // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
    list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide('username@'.$f_domaine);
    if(!$is_domaine_valide)
    {
      Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
    }
    $f_mail = $f_domaine;
  }
  $result = FileSystem::fabriquer_fichier_hebergeur_info( array(
    'CONTACT_MODIFICATION_USER' => $f_user,
    'CONTACT_MODIFICATION_MAIL' => $f_mail,
  ) );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
