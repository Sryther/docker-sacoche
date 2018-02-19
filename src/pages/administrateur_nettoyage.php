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
$TITRE = html(Lang::_("Nettoyer / Initialiser la base"));

$label = (version_compare($_SESSION['VERSION_BASE'],VERSION_BASE_STRUCTURE,'=')) ? 'valide' : 'alerte' ;
Layout::add( 'js_inline_before' , 'var nb_devoirs_annee_scolaire_precedente = '.( isset($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? $_SESSION['NB_DEVOIRS_ANTERIEURS'] : DB_STRUCTURE_COMMUN::DB_compter_devoirs_annees_scolaires_precedentes() ).';' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage">DOC : Nettoyage et initialisation annuelle de la base</a></span></p>

<hr />

<h2>Version de la base</h2>

<div class="astuce">La base de données est mise à jour automatiquement.</div>
<div>Version attendue : <?php echo VERSION_BASE_STRUCTURE ?>. Version en place : <label class="<?php echo $label ?>"><?php echo $_SESSION['VERSION_BASE'] ?></label>.</div>

<hr />

<h2>Recherche et correction d'anomalies de numérotation</h2>

<div class="astuce">Cet outil n'est utile que si une anomalie spécifique a été constatée (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage#toggle_anomalies_numerotation">DOC</a></span>).</div>
<form action="#" method="post" id="form_numeroter"><fieldset>
  <span class="tab"></span><button id="bouton_numeroter" type="button" class="nettoyer">Lancer le nettoyage d'éventuelles anomalies de numérotation.</button><label id="ajax_msg_numeroter">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Recherche et suppression de correspondances anormales</h2>

<div class="astuce">Cet outil est facultatif ; il ne met pas à jour la structure ni les données de la base (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage#toggle_correspondances_anormales">DOC</a></span>).</div>
<form action="#" method="post" id="form_nettoyer"><fieldset>
  <span class="tab"></span><button id="bouton_nettoyer" type="button" class="nettoyer">Lancer le nettoyage d'éventuelles correspondances anormales.</button><label id="ajax_msg_nettoyer">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Initialisation annuelle des données</h2>

<div class="astuce">Entre deux années scolaires, il faut purger la base avant d'importer les nouveaux utilisateurs (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage#toggle_initialisation_annuelle">DOC</a></span>).</div>
<div class="danger">N'effectuez jamais une initialisation en cours d'année scolaire !</div>
<form action="#" method="post" id="form_purger"><fieldset>
  <span class="tab"></span><button id="bouton_purger" type="button" class="nettoyer">Lancer l'initialisation annuelle des données.</button><label id="ajax_msg_purger">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Suppression des notes</h2>

<div><span class="probleme">Cette suppression ne doit jamais être effectuée dans le cadre d'un usage normal de <em>SACoche</em> sur plusieurs années.</span></div>
<div class="danger">Cette fonctionnalité est à utiliser uniquement pour repartir à zéro après une année de test (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage#toggle_suppression_notes">DOC</a></span>) !</div>
<form action="#" method="post" id="form_supprimer"><fieldset>
  <span class="tab"></span><button id="bouton_supprimer" type="button" class="nettoyer">Supprimer tous les scores aux évaluations.</button><label id="ajax_msg_supprimer">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Effacement des étiquettes nom &amp; prénom</h2>

<div class="astuce">Cet outil n'est utile que si une anomalie spécifique a été constatée (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_nettoyage#toggle_effacement_etiquettes">DOC</a></span>).</div>
<form action="#" method="post" id="form_effacer"><fieldset>
  <span class="tab"></span><button id="bouton_effacer" type="button" class="nettoyer">Effacer les étiquettes nom &amp; prénom.</button><label id="ajax_msg_effacer">&nbsp;</label>
</fieldset></form>

<hr />
