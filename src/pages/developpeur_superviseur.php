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
$TITRE = "Compte Superviseur"; // Pas de traduction car pas de choix de langue pour ce profil.

$select_structure = (HEBERGEUR_INSTALLATION=='multi-structures') ? '<label class="tab" for="f_base">Structure :</label>'.HtmlForm::afficher_select( DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , 'f_base' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'zones_geo' /*optgroup*/ , FALSE /*multiple*/ ).'<br />' : '' ;

// Javascript
Layout::add( 'js_inline_before' , 'var installation_type="'.HEBERGEUR_INSTALLATION.'";' );
?>

<p class="astuce">Un compte "superviseur" est un compte d'établissement de profil "administrateur" créé temporairement par un développeur afin de pouvoir analyser une anomalie.</p>

<hr />

<form action="#" method="post"><fieldset>
  <?php echo $select_structure ?>
  <label class="tab">Action :</label><button id="bouton_ajouter" type="button" class="ajouter">Ajouter ce compte</button> <button id="bouton_retirer" type="button" class="supprimer">Retirer ce compte</button><br />
  <span class="tab"></span><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
<div id="ajax_retour" class="p">
</div>
