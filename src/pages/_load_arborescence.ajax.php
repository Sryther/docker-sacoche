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

// Non chargé par la page initiale car potentiellement lourd pour les directeurs et les PP

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$objet     = (isset($_POST['f_objet']))   ? Clean::texte($_POST['f_objet']) : '' ;
$item_comm = empty($_POST['f_item_comm']) ? FALSE : TRUE ;
$all_if_pp = empty($_POST['f_all_if_pp']) ? FALSE : TRUE ;

if( $objet == 'referentiels' )
{
  // Sur une installation avec seulement 32 Mo de mémoire il arrive que la simple récupération de l'arborescence complète dépasse cette limite !
  Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

  // Affichage de la liste des items pour toutes les matières d'un professeur ou toutes les matières de l'établissement si directeur ou PP, sur tous les niveaux
  $user_id = ( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ( !$all_if_pp || !DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'],0) ) ) ? $_SESSION['USER_ID'] : 0 ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( $user_id , 0 /*matiere_id*/ , 0 /*niveau_id*/, FALSE /*only_socle*/ , FALSE /*only_item*/ , TRUE /*s2016_count*/ , $item_comm );
  if(empty($DB_TAB))
  {
      $phrase_debut =  ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'Vous n\'êtes rattaché à' : 'L\'établissement n\'a mis en place' ;
      Json::end( TRUE , '<p class="danger">'.$phrase_debut.' aucune matière, ou des matières sans référentiel, ou des référentiels sans items !</p>' );
  }
  else
  {
    $phrase_debut =  ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'Vous êtes rattaché à' : 'L\'établissement a mis en place' ;
    $arborescence = HtmlArborescence::afficher_matiere_from_SQL( $DB_TAB , NULL /*DB_TAB_socle2016*/ , TRUE /*dynamique*/ , TRUE /*reference*/ , FALSE /*aff_coef*/ , FALSE /*aff_cart*/ , 'texte' /*aff_socle*/ , FALSE /*aff_lien*/ , $item_comm , TRUE /*aff_input*/ );
    $arborescence = strpos($arborescence,'<input') ? $arborescence : '<p class="danger">'.$phrase_debut.' des matières dont les référentiels ne comportent aucun item !</p>' ;
    Json::end( TRUE , $arborescence );
  }
}

if( $objet == 'elements_dgesco' )
{
  Json::end( TRUE , HtmlArborescence::afficher_dgesco_elements() );
}

// On ne devrait pas en arriver là...
Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
