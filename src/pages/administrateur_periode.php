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
$TITRE = html(Lang::_("Périodes"));

// Par défaut, faire arriver sur la page de gestion des périodes
$SECTION = ($SECTION) ? $SECTION : 'gestion' ;

// Sous-Menu d'en-tête
$SOUS_MENU = '';
$tab_sous_menu = array(
  'gestion'       => Lang::_("Périodes (gestion)"),
  'classe_groupe' => Lang::_("Périodes & classes / groupes"),
);
foreach($tab_sous_menu as $sous_menu_section => $sous_menu_titre)
{
  $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
  $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.html($sous_menu_titre).'</a>'.NL;
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
  echo'<p class="astuce">Choisir une rubrique ci-dessus&hellip;</p>'.NL;
  echo'<p><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes">DOC : Gestion des périodes</a></span></p>'.NL;
}
?>
