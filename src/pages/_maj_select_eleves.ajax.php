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

// Mettre à jour l'élément de formulaire "select_eleves"

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::lettres($_POST['f_groupe_type']) : '';
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])    : 0;

// Le code n'est pas exactement le même pour un administrateur que pour un professeur / directeur / parent.

$tab_types = array(
  'd' => 'Divers' ,
  'n' => 'niveau' ,
  'c' => 'classe' ,
  'g' => 'groupe' ,
  'b' => 'besoin' ,
) + array(
  'Classes' => 'classe' ,
  'Groupes' => 'groupe' ,
  'Besoins' => 'besoin' ,
);

if( (!$groupe_id) || (!isset($tab_types[$groupe_type])) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}
$groupe_type = $tab_types[$groupe_type];
if($groupe_type=='Divers')
{
  $groupe_type = ($groupe_id==1) ? 'sdf' : 'all' ;
  if($groupe_id==3)
  {
    $_POST['f_statut'] = 0 ;
  }
}

// Autres valeurs à récupérer ou à définir.

$eleves_ordre = (isset($_POST['f_eleves_ordre'])) ? Clean::texte($_POST['f_eleves_ordre']) : 'alpha' ;
$statut       = (isset($_POST['f_statut']))       ? Clean::entier($_POST['f_statut'])      : 0 ;
$select_nom   = (isset($_POST['f_nom']))          ? Clean::texte($_POST['f_nom'])          : 'f_eleve' ;
$multiple     = (empty($_POST['f_multiple']))     ? FALSE                                  : TRUE ;
$selection    = (empty($_POST['f_selection']))    ? FALSE                                  : ( ($_POST['f_selection']==1) ? TRUE : explode(',',$_POST['f_selection']) ) ;

$eleves_ordre = (($groupe_type=='groupe')||($groupe_type=='besoin')) ? $eleves_ordre : 'alpha' ;
$select_nom   = ($multiple) ? $select_nom : FALSE ;
$option_first = ($multiple) ? FALSE       : ''    ;
$selection    = ($multiple) ? $selection  : FALSE ;

// Affichage du retour.
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_eleves_regroupement( $groupe_type , $groupe_id , $statut , $eleves_ordre );
if(!is_array($DB_TAB))
{
  Json::end( FALSE , $DB_TAB );  // Aucun élève trouvé.
}
else
{
  Json::end( TRUE , HtmlForm::afficher_select( $DB_TAB , $select_nom , $option_first , $selection , '' /*optgroup*/ , $multiple ) );
}

?>
