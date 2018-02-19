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
$TITRE = html(Lang::_("Choisir sa langue"));

// Charger $tab_langues_traduction
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_traduction.php');
// Formulaire SELECT du choix de la langue
$defaut_texte = '' ;
// Langues sélectionnables
$options_langue_selection = '<optgroup label="'.html(Lang::_("Autres langues sélectionnables")).'">';
foreach($tab_langues_traduction as $tab_langue)
{
  if($tab_langue['statut']!=0)
  {
    $langue_pays_code = $tab_langue['langue']['code'].'_'.$tab_langue['pays']['code'];
    $langue_pays_nom  = $tab_langue['langue']['nom'].' - '.$tab_langue['pays']['nom'];
    $selected = ($langue_pays_code==$_SESSION['USER_LANGUE']) ? ' selected' : '' ;
    $options_langue_selection .= '<option value="'.$langue_pays_code.'"'.$selected.'>'.$langue_pays_nom.' ['.$langue_pays_code.']</option>';
    if($langue_pays_code==$_SESSION['ETABLISSEMENT']['LANGUE'])
    {
      $defaut_texte = $langue_pays_nom ;
    }
  }
}
$options_langue_selection .= '</optgroup>';
// Première option qui correspond au choix de l'établissement
$selected = (empty($_SESSION['USER_LANGUE'])) ? ' selected' : '' ;
$options_langue_defaut  = '<optgroup label="'.html(Lang::_("Langue par défaut dans l'établissement")).'">';
$options_langue_defaut .=   '<option value="defaut"'.$selected.'>'.$defaut_texte.' ['.$_SESSION['ETABLISSEMENT']['LANGUE'].']</option>';
$options_langue_defaut .= '</optgroup>';
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__traduction">DOC : Traductions / Langues</a></span></div>

<hr />

<form action="#" method="post"><fieldset>
  <label class="tab" for="f_langue">Langue :</label><select id="f_langue" name="f_langue"><?php echo $options_langue_defaut.$options_langue_selection; ?></select><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="parametre">Valider.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
