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
$TITRE = html(Lang::_("Synthèses / Bilans"));

// Sous-Menu d'en-tête
if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || (($_SESSION['USER_PROFIL_TYPE']=='directeur')&&(substr($SECTION,0,8)=='reglages')) )
{
  $SOUS_MENU = '';
  $tab_sous_menu = array(
    'reglages_ordre_matieres'  => Lang::_("Ordre d'affichage des matières"),
    'reglages_format_synthese' => Lang::_("Format de synthèse par référentiel"),
    'reglages_configuration'   => Lang::_("Configuration des bilans officiels"),
    'reglages_mise_en_page'    => Lang::_("Mise en page des bilans officiels"),
  );
  foreach($tab_sous_menu as $sous_menu_section => $sous_menu_titre)
  {
    $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
    $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.html($sous_menu_titre).'</a>'.NL;
  }
}

if($SECTION=='reglages')
{
  echo'<p class="astuce">Choisir une rubrique ci-dessus&hellip;</p>'.NL;
  // Avertissement mode de synthèse non configuré ou configuré sans synthèse
  $tab_mode = array(
    'inconnu' => 'dont le format de synthèse est inconnu',
    'sans'    => 'volontairement définis sans format de synthèse',
  );
  $is_alerte = FALSE;
  foreach($tab_mode as $mode => $explication)
  {
    $nb = DB_STRUCTURE_BILAN::DB_compter_modes_synthese($mode);
    if($nb)
    {
      $is_alerte = TRUE;
      $s = ($nb>1) ? 's' : '' ;
      echo'<label class="alerte">Il y a '.$nb.' référentiel'.$s.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace('§BR§','<br />',html(html(DB_STRUCTURE_BILAN::DB_recuperer_modes_synthese($mode)))).'" /> '.$explication.' (donc non pris en compte).</label> <a href="./index.php?page='.$PAGE.'&amp;section=reglages_format_synthese">&rarr; Configurer les formats de synthèse.</a><br />'.NL; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    }
  }
  if(!$is_alerte)
  {
    echo'<label class="valide">Tous les référentiels ont un format de synthèse prédéfini.</label><br />'.NL;
  }
}
elseif($SECTION=='assiduite')
{
  $fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
  $PAGE = $PAGE.'_'.$SECTION ;
  require($fichier_section);
}
else
{
  if(substr($SECTION,0,8)=='accueil_')
  {
    $BILAN_TYPE = substr($SECTION,8);
    $SECTION = 'accueil';
  }
  // Afficher la bonne page et appeler le bon js / ajax par la suite
  $fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
  if(!is_file($fichier_section))
  {
    echo'<p class="danger">Page introuvable (paramètre manquant ou incorrect) !</p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  if( !isset($BILAN_TYPE) || in_array($BILAN_TYPE,array('releve','bulletin')) )
  {
    $PAGE = $PAGE.'_'.$SECTION ;
    require($fichier_section);
  }
  else
  {
    echo'<p class="danger">Page introuvable (paramètre manquant ou incorrect) !</p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}
?>
