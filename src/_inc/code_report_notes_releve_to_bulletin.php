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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Forcer des reports de notes par un prof
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_periode_eleves  = (isset($_POST['f_periode_eleves']))  ? explode('_',$_POST['f_periode_eleves'])  : '' ;
$tab_eleves_moyennes = (isset($_POST['f_eleves_moyennes'])) ? explode('x',$_POST['f_eleves_moyennes']) : '' ;

$rubrique_id = (isset($_POST['f_rubrique'])) ? Clean::entier($_POST['f_rubrique']) : 0;
$periode_id  = (count($tab_periode_eleves))  ? $tab_periode_eleves[0]              : 0;

// On vérifie les paramètres principaux
// $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] a été mis en session par noyau_items_releve.php lors de l'appel http précédent

if( (!$periode_id) || (!$rubrique_id) || (count($tab_periode_eleves)<2) || (!count($tab_eleves_moyennes)) || ($_SESSION['USER_PROFIL_TYPE']!='professeur') || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// On passe en revue les données

unset($tab_periode_eleves[0]);
$tab_eleve_id = array_filter( Clean::map('entier',$tab_periode_eleves) , 'positif' );
$appreciation = 'Moyenne figée reportée par '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
$nb_reports = 0;

foreach($tab_eleves_moyennes as $eleve_moyenne)
{
  list($eleve_id,$moyenne) = explode('_',$eleve_moyenne);
  $eleve_id = (int)$eleve_id;
  $note = round($moyenne,1);
  // $tab_eleve_id contient la liste des élèves dont il faut changer les notes ; ce peut n'être qu'une intersection groupe x classe
  // $tab_eleves_moyennes contient les moyennes de tous les élèves du groupe ou de la classe
  if(in_array($eleve_id,$tab_eleve_id))
  {
    DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( 'bulletin' /*BILAN_TYPE*/ , $periode_id , $eleve_id , 0 /*groupe_id*/ , $rubrique_id , 0 /*prof_id*/ , 'eleve' , $note , $appreciation );
    $nb_reports++;
  }
}

// On affiche le résultat

if(!$nb_reports)
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}
else
{
  $s = ($nb_reports>1) ? 's' : '' ;
  Json::end( TRUE , 'Note'.$s.' reportée'.$s.' pour '.$nb_reports.' élève'.$s.'.' );
}
?>
