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
$TITRE = "Droits du système de fichiers"; // Pas de traduction car pas de choix de langue pour ce profil.

// Select umask
$tab_umask = array(
  '000' => '777 pour les dossiers ; 666 pour les fichiers',
  '002' => '775 pour les dossiers ; 664 pour les fichiers',
  '022' => '755 pour les dossiers ; 644 pour les fichiers',
  '026' => '751 pour les dossiers ; 640 pour les fichiers',
);
$options_umask = '';
foreach($tab_umask as $option_val => $option_txt)
{
  $selected = ($option_val==SYSTEME_UMASK) ? ' selected' : '' ;
  $options_umask .= '<option value="'.$option_val.'"'.$selected.'>'.$option_txt.'</option>';
}
// Tableau chmod
$tab_chmod = array(
  '000' => '777 / 666',
  '002' => '775 / 664',
  '022' => '755 / 644',
  '026' => '751 / 640',
);
?>

<h2>Droits du système de fichiers</h2>

<form action="#" method="post" id="form_chmod"><fieldset>
  <label class="tab">Nouveaux fichiers :</label><select id="select_umask" name="select_umask"><?php echo $options_umask ?></select> <button id="bouton_umask" type="button" class="parametre">Enregistrer ce choix.</button><label id="ajax_umask">&nbsp;</label><br />
  <label class="tab">Fichiers actuels :</label><button id="bouton_chmod" type="button" class="parametre">Appliquer les droits <span id="info_chmod"><?php echo $tab_chmod[SYSTEME_UMASK] ?></span> à toute l'arborescence de l'application.</button><label id="ajax_chmod">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Vérification des droits en écriture</h2>

<form action="#" method="post" id="form_maj"><fieldset>
  <span class="tab"></span><button id="bouton_droit" type="button" class="parametre">Lancer la vérification des droits.</button><label id="ajax_droit">&nbsp;</label>
</fieldset></form>

<hr />
