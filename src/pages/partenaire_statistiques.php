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
$TITRE = "Statistiques d'utilisation (partenariat ENT)"; // Pas de traduction car pas de choix de langue pour ce profil.
?>

<p class="astuce">
  Seuls les établissements utilisant le connecteur ENT conventionné sont comptabilisés.
</p>

<div id="ajax_info" class="hide">
  <label id="ajax_msg1"></label>
  <ul class="puce"><li id="ajax_msg2"></li></ul>
  <span id="ajax_num" class="hide"></span>
  <span id="ajax_max" class="hide"></span>
</div>

<form action="#" method="post" id="structures" class="hide">
  <table class="hsort" id="table_action">
    <thead>
      <tr>
        <th>Géographie</th>
        <th>Structure</th>
        <th>connexion ENT</th>
        <th>personnels<br />connectés</th>
        <th>élèves<br />connectés</th>
        <th>evaluations<br />récentes</th>
        <th>bilans SACoche<br />récents</th>
        <th>bilans LSU<br />récents</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td class="nu" colspan="8"></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <td class="nu" colspan="8"></td>
      </tr>
    </tbody>
  </table>
</form>
