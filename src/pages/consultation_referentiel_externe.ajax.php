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

$action         = (isset($_POST['f_action']))         ? $_POST['f_action']                        : '';
$matiere_id     = (isset($_POST['f_matiere_id']))     ? Clean::entier($_POST['f_matiere_id'])     : 0;
$niveau_id      = (isset($_POST['f_niveau_id']))      ? Clean::entier($_POST['f_niveau_id'])      : 0;
$structure_id   = (isset($_POST['f_structure_id']))   ? Clean::entier($_POST['f_structure_id'])   : 0;
$referentiel_id = (isset($_POST['f_referentiel_id'])) ? Clean::entier($_POST['f_referentiel_id']) : 0;
$maj_date_fr    = (isset($_POST['f_maj_date']))       ? Clean::date_fr($_POST['f_maj_date'])      : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire des structures ayant partagées au moins un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Afficher_structures') // La vérification concernant le nombre de contraintes s'effectue après
{
  Json::end( TRUE , ServeurCommunautaire::afficher_formulaire_structures_communautaires( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les référentiels partagés trouvés selon les critères retenus (matière / niveau / structure)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Lister_referentiels') // La vérification concernant le nombre de contraintes s'effectue après
{
  $maj_date_mysql = To::date_french_to_mysql($maj_date_fr);
  Json::end( TRUE , ServeurCommunautaire::afficher_liste_referentiels( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , $structure_id , $maj_date_mysql ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir le contenu d'un référentiel partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Voir_referentiel') && $referentiel_id )
{
 Json::end( TRUE , ServeurCommunautaire::afficher_contenu_referentiel( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $referentiel_id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On en devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
