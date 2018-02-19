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

// Contrôler la liste des ids transmis
$tab_id = (isset($_POST['tab_id'])) ? Clean::map('entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier l'ordre des matières
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(count($tab_id))
{
  $nb_modifs = 0;
  // récupérer les ordres des matières pour les comparer (et ne mettre à jour que ce qui a changé).
  $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement( FALSE /*order_by_name*/ , TRUE /*with_siecle*/ );
  $tab_ordre_avant = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_ordre_avant[$DB_ROW['matiere_id']] = $DB_ROW['matiere_ordre'];
  }
  foreach($tab_id as $key => $matiere_id)
  {
    $ordre_apres = $key+1;
    if($ordre_apres!=$tab_ordre_avant[$matiere_id])
    {
      DB_STRUCTURE_MATIERE::DB_modifier_matiere_ordre($matiere_id,$ordre_apres);
      $nb_modifs++;
    }
  }
  if(!$nb_modifs)
  {
    Json::end( FALSE , 'Aucune modification effectuée !' );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
