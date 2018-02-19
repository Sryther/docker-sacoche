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
$TITRE = html(Lang::_("Livret Scolaire"));

// Sous-Menu d'en-tête
$SOUS_MENU = '';
$tab_sous_menu = array(
  'accueil'   =>  array( 'acces_prof'=>FALSE  , 'prefixe'=>     '' , 'titre'=>Lang::_("Accueil") ),
  'classes'   =>  array( 'acces_prof'=>FALSE  , 'prefixe'=>  '1) ' , 'titre'=>Lang::_("Classes / Périodicité") ),
  'liaisons'  =>  array( 'acces_prof'=>FALSE  , 'prefixe'=>  '2) ' , 'titre'=>Lang::_("Rubriques / Liaisons") ),
  'seuils'    =>  array( 'acces_prof'=>FALSE  , 'prefixe'=>  '3) ' , 'titre'=>Lang::_("Notation / Seuils") ),
  'elements'  =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '4) ' , 'titre'=>Lang::_("Éléments de programme") ),
  'epi'       =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '5) ' , 'titre'=>Lang::_("E.P.I.") ),
  'ap'        =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '6) ' , 'titre'=>Lang::_("A.P.") ),
  'parcours'  =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '7) ' , 'titre'=>Lang::_("Parcours") ),
  'modaccomp' =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '8) ' , 'titre'=>Lang::_("Modalités d'accompagnement") ),
  'enscompl'  =>  array( 'acces_prof'=>'test' , 'prefixe'=>  '9) ' , 'titre'=>Lang::_("Enseignements de complément") ),
  'edition'   =>  array( 'acces_prof'=>TRUE   , 'prefixe'=> '10) ' , 'titre'=>Lang::_("Édition du livret") ),
  'export'    =>  array( 'acces_prof'=>FALSE  , 'prefixe'=> '11) ' , 'titre'=>Lang::_("Export LSU") ),
);
foreach($tab_sous_menu as $sous_menu_section => $tab_sous_menu_section)
{
  if( ($_SESSION['USER_PROFIL_TYPE']!='professeur') || ($tab_sous_menu_section['acces_prof']!==FALSE) )
  {
    if($_SESSION['USER_PROFIL_TYPE']!='professeur')
    {
      $prefixe = $tab_sous_menu_section['prefixe'];
      $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
    }
    else
    {
      $prefixe = '';
      if( is_string($tab_sous_menu_section['acces_prof']) && !Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_LIVRET_'.strtoupper($sous_menu_section)] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
      {
        $class = ' class="disabled"';
      }
      else
      {
        $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
      }
    }
    $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.$prefixe.html($tab_sous_menu_section['titre']).'</a>'.NL;
  }
}

// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
  $PAGE = $PAGE.'_'.$SECTION ;
  require($fichier_section);
}
else
{
  echo'<p class="danger">Page introuvable (paramètre manquant ou incorrect) !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>
