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
$TITRE = "Procédure d'installation"; // Pas de traduction car pas de choix de langue à ce niveau.

// Javascript
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );
?>

<ul id="step">
  <li id="step1">Étape 1 - Création de dossiers supplémentaires et de leurs droits</li>
  <li id="step2">Étape 2 - Remplissage de ces dossiers avec le contenu approprié</li>
  <li id="step3">Étape 3 - Choix du type d'installation</li>
  <li id="step4">Étape 4 - Informations concernant l'hébergement et le webmestre</li>
  <li id="step5">Étape 5 - Création de dossiers additionnels (cas d'un seul établissement)</li>
  <li id="step6">Étape 6 - Indication des paramètres de connexion MySQL</li>
  <li id="step7">Étape 7 - Installation des tables de la base de données</li>
</ul>

<hr />

<form action="#" method="post" id="zone_consignes"><?php /* on prend un <form> pour avoir le style du <span class="tab"> */ ?>
  <h2>Bienvenue dans la procédure d'installation de <em>SACoche</em> !</h2>
  <p class="astuce"><em>SACoche</em> est une web-application distribuée gratuitement dans l’espoir qu’elle vous sera utile, mais sans aucune garantie, conformément à la <a target="_blank" rel="noopener noreferrer" href="https://www.gnu.org/licenses/agpl-3.0.html">licence libre GNU AGPL3</a>.</p>
  <p class="danger">Webmestre et administrateurs sont responsables de toute mauvaise manipulation ou négligence qui entraînerait des pertes de données.</p>
  <p><span class="tab"></span><a href="#" class="step1">Passer à l'étape 1.</a><label id="ajax_msg">&nbsp;</label></p>
</form>
<form action="#" method="post" id="form_info_heberg">
</form>
<form action="#" method="post" id="form_param_mysql">
</form>
