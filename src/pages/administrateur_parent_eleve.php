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
$TITRE = html(Lang::_("Parents & élèves"));

// Fabrication des éléments select du formulaire
$select_f_groupes = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl()         , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_f_parents = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_parents_etabl($user_statut=1) ,      FALSE /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var select_parent = "'.str_replace('"','\"',$select_f_parents).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></p>

<hr />

<form action="#" method="post">
  <fieldset id="fieldset_eleves">
    <label class="tab" for="f_groupe">Élève :</label><?php echo $select_f_groupes ?> <select id="select_eleve" name="select_eleve"></select> <label id="ajax_msg">&nbsp;</label>
  </fieldset>
  <hr />
  <fieldset id="fieldset_parents">
  </fieldset>
  <p id="p_valider" class="hide"><span class="tab"></span><button id="Enregistrer" type="button" class="valider">Enregistrer les modifications</button><label id="ajax_msg2">&nbsp;</label></p>
</form>
