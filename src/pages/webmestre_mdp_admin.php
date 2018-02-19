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
$TITRE = "Mot de passe administrateur"; // Pas de traduction car pas de choix de langue pour ce profil.

// Uniquement pour une installation de type mono-structure ; pour du multi-structures, cela se fait à la page de gestion des établissements.
$select_admin = HtmlForm::afficher_select(DB_STRUCTURE_WEBMESTRE::DB_OPT_administrateurs_etabl() , 'f_admin' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
?>

<form action="#" method="post"><fieldset>
  <label class="tab" for="f_admin">Administrateur :</label><?php echo $select_admin ?><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="mdp_perso">Générer un nouveau mot de passe.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
