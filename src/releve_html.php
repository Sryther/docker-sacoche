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

// Fichier appelé pour l'affichage d'un relevé HTML enregistré temporairement.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Paramètre transmis ; attention à l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/)
$FICHIER = (isset($_GET['fichier'])) ? Clean::fichier($_GET['fichier']) : ''; // On ne nettoie pas le caractère "." car le paramètre peut le contenir.

// Fonctions
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

ob_start();
// Chargement de la page concernée
$filename_html = CHEMIN_DOSSIER_EXPORT.$FICHIER.'.html';
if(is_file($filename_html))
{
  require($filename_html);
}
else
{
  echo'<h2>Relevé manquant</h2>';
  echo'Les relevés sont conservés sur le serveur pendant une durée limitée...';
}
// Affichage dans une variable
$CONTENU_PAGE = trim(ob_get_contents());
ob_end_clean();

// Extraire le css perso <style type="text/css">...</style> (de la session) pour le faire afficher par le Layout au bon endroit
// Si présent, il est au tout début du contenu.
$position_css = mb_strpos($CONTENU_PAGE,'</style>');
if($position_css)
{
  Layout::add( 'css_inline' , mb_substr($CONTENU_PAGE,23,$position_css-23) );
  $CONTENU_PAGE = mb_substr($CONTENU_PAGE,$position_css+8);
}

// Extraire le js perso <script type="text/javascript">...</script> (destiné à trier les tableaux) pour le faire afficher par le Layout au bon endroit
// Si présent, il est à la toute fin du contenu.
$position_js = mb_strpos($CONTENU_PAGE,'<script type="text/javascript">');
if($position_js)
{
  Layout::add( 'js_inline_after' , mb_substr($CONTENU_PAGE,$position_js+31,-9) );
  $CONTENU_PAGE = mb_substr($CONTENU_PAGE,0,$position_js);
}

// Titre du navigateur
Layout::add( 'browser_title' , 'Relevé HTML' );

// Fichiers à inclure
Layout::add( 'css_file' , './_css/style.css'            , 'mini' );
Layout::add( 'js_file'  , './_js/jquery1-librairies.js' , 'comm' );
Layout::add( 'js_file'  , './_js/script.js'             , 'mini' );
Layout::add( 'js_file'  , './pages/releve_html.js'      , 'mini' );

// Ultimes constantes javascript
$display = (substr($FICHIER,0,10)=='evaluation') ? 'inline-block' : 'block' ; // 'inline-block' permet d'avoir le checkbox sur la même ligne, mais 'block' est plus adapté pour gagner en largeur ou quand le contenu est centré
Layout::add( 'js_inline_before' , 'var PAGE = "public_anti_maj_clock";' ); // Préfixe "public" pour indiquer que c'est une page accessible sans authentification.
Layout::add( 'js_inline_before' , 'var display_mode = "'.$display.'";' );

// Affichage
echo Layout::afficher_page_entete('light');
echo $CONTENU_PAGE.NL;
echo Layout::afficher_page_pied();
?>