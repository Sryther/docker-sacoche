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
$TITRE = html(Lang::_("Statistiques globales"));

list( $personnel_nb , $eleve_nb , $parent_nb , $personnel_use , $eleve_use , $parent_use , $evaluation_nb , $evaluation_use ) = DB_STRUCTURE_DIRECTEUR::DB_recuperer_statistiques();
?>

<h2>Utilisateurs</h2>

<ul class="puce">
  <li>Il y a <b><?php echo number_format($personnel_nb ,0,'',' ') ?> personnel(s)</b> enregistré(s),  dont <b><?php echo number_format($personnel_use ,0,'',' ') ?></b> personnel(s) connecté(s).</li>
  <li>Il y a <b><?php echo number_format($eleve_nb     ,0,'',' ') ?> élève(s)</b>     enregistré(s),  dont <b><?php echo number_format($eleve_use     ,0,'',' ') ?></b> élève(s) connecté(s).</li>
  <li>Il y a <b><?php echo number_format($parent_nb    ,0,'',' ') ?> parent(s)</b>    enregistré(s),  dont <b><?php echo number_format($parent_use    ,0,'',' ') ?></b> parent(s) connecté(s).</li>
</ul>
<p class="astuce">
  Les anciens utilisateurs encore dans la base ne sont pas comptés parmi les <b>utilisateurs enregistrés</b>.<br />
  Les <b>utilisateurs connectés</b> sont ceux s'étant identifiés au cours du dernier semestre.
</p>
<ul class="puce">
  <li>Vous pouvez aussi consulter <a href="?page=consultation_date_connexion">la date de dernière connexion</a> de chaque utilisateur.</li>
</ul>

<hr />

<h2>Utilisation</h2>

<ul class="puce">
  <li>Il y a <b><?php echo number_format($evaluation_nb,0,'',' ') ?> saisie(s)</b> de notes enregistrée(s), dont <b><?php echo number_format($evaluation_use,0,'',' ') ?></b> récemment.</li>
</ul>
<p class="astuce">
  Les évaluations <b>récentes</b> sont celles effectuées au cours du dernier semestre.
</p>
<ul class="puce">
  <li>Vous pouvez aussi consulter <a href="?page=consultation_nombre_saisies">le nombre de notes saisies par classe et enseignant</a> au cours de cette année scolaire.</li>
</ul>

<hr />
