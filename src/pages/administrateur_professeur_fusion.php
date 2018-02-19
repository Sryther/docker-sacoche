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
$TITRE = html(Lang::_("Fusion de comptes professeurs / personnels"));

// Javascript
Layout::add( 'js_inline_before' , 'var input_date = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var date_mysql = "'.TODAY_MYSQL.'";' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs#toggle_fusionner_comptes">DOC : Fusionner deux comptes professeurs / personnels</a></span></p>

<hr />

<form action="#" method="post" id="form_recherche">
  <h3>Compte professeur / personnel actuel :</h3>
  <p>
    <label class="tab" for="nom_actuel">Nom de famille :</label><input id="nom_actuel" name="nom_actuel" type="text" value="" size="40" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" /> <button id="bouton_chercher_actuel" type="button" class="rechercher">Chercher.</button><label id="ajax_msg_actuel">&nbsp;</label><br />
    <span class="tab"></span><select id="id_actuel" name="id_actuel" class="hide"><option value=""></option></select><br />
  </p>
  <h3>Compte professeur / personnel précédent :</h3>
  <p>
    <label class="tab" for="nom_ancien">Nom de famille :</label><input id="nom_ancien" name="nom_ancien" type="text" value="" size="40" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" /> <button id="bouton_chercher_ancien" type="button" class="rechercher">Chercher.</button><label id="ajax_msg_ancien">&nbsp;</label><br />
    <span class="tab"></span><select id="id_ancien" name="id_ancien" class="hide"><option value=""></option></select><br />
  </p>
  <p>
    <span class="tab"></span><button id="bouton_selectionner" type="button" class="valider" disabled>Confirmer la fusion des comptes sélectionnés.</button><label id="ajax_msg_selection">&nbsp;</label>
  </p>
</form>
