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
$TITRE = html(Lang::_("Évaluations"));

// Sous-Menu d'en-tête
if( ($_SESSION['USER_PROFIL_TYPE']!='parent') && ($_SESSION['USER_PROFIL_TYPE']!='directeur') )
{
  $SOUS_MENU = '';
  if($_SESSION['USER_PROFIL_TYPE']=='professeur')
  {
    $tab_sous_menu = array(
      'demande_professeur' => Lang::_("Demandes d'évaluations formulées"),
      'gestion_groupe'     => Lang::_("Évaluer une classe ou un groupe"),
      'gestion_selection'  => Lang::_("Évaluer des élèves sélectionnés"),
      'ponctuelle'         => Lang::_("Évaluer un élève à la volée"),
      'voir'               => Lang::_("Liste des évaluations"),
    );
  }
  else if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $tab_sous_menu = array(
      'voir'          => Lang::_("Liste des évaluations"),
      'demande_eleve' => Lang::_("Demandes d'évaluations formulées"),
    );
  }
  $tab_class_differente = array(
    'demande_eleve'      => 'evaluation_demande',
    'demande_professeur' => 'evaluation_demande',
    'gestion_groupe'     => 'evaluation_gestion',
    'gestion_selection'  => 'evaluation_gestion',
    'ponctuelle'         => 'evaluation_gestion',
  );
  foreach($tab_sous_menu as $sous_menu_section => $sous_menu_titre)
  {
    $sous_menu_class = isset($tab_class_differente[$sous_menu_section]) ? $tab_class_differente[$sous_menu_section] : 'evaluation_'.$sous_menu_section ;
    // Certains menus peuvent être interdits d'accès ou d'aspect désactivés
    if( strpos( $_SESSION['MENU'] , 'class="'.$sous_menu_class.'"' ) )
    {
      $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
    }
    else
    {
      $class = ' class="disabled"';
    }
    $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.html($sous_menu_titre).'</a>'.NL;
  }
}

// Afficher la bonne page et appeler le bon js / ajax par la suite
if(substr($SECTION,0,8)=='gestion_')
{
  $PAGE = 'evaluation_gestion';
  $SECTION = substr($SECTION,8);
  require(CHEMIN_DOSSIER_PAGES.$PAGE.'.php');
}
else
{
  $fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
  if(is_file($fichier_section))
  {
    $PAGE = $PAGE.'_'.$SECTION ;
    require($fichier_section);
  }
  else
  {
    echo'<p class="astuce">Choisir un sous-menu ci-dessus&hellip;</p>'.NL;
  }
}
?>
