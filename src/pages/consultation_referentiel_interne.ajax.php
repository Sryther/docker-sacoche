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

$ids = (isset($_POST['ids'])) ? $_POST['ids'] : '';

if(mb_substr_count($ids,'_')!=2)
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

list($prefixe,$matiere_id,$niveau_id) = explode('_',$ids);
$matiere_id  = Clean::entier($matiere_id);
$niveau_id   = Clean::entier($niveau_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retourner le référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $matiere_id && $niveau_id )
{
  $DB_TAB_socle2016 = DB_STRUCTURE_REFERENTIEL::DB_recuperer_socle2016_for_referentiel_matiere_niveau( $matiere_id , $niveau_id , 'texte' /*format*/ );
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , TRUE /*item_comm*/ );
  Json::end( TRUE , HtmlArborescence::afficher_matiere_from_SQL( $DB_TAB , $DB_TAB_socle2016 , FALSE /*dynamique*/ , FALSE /*reference*/ , TRUE /*aff_coef*/ , TRUE /*aff_cart*/ , 'image' /*aff_socle*/ , 'image' /*aff_lien*/ , TRUE /*aff_comm*/ , FALSE /*aff_input*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
