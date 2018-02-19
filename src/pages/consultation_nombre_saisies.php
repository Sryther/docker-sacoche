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
$TITRE = html(Lang::_("Nombre de saisies"));
?>

<div class="astuce">Il s'agit du nombre de notes saisies au cours de cette année scolaire.</div>
<p class="danger">Ce n'est qu'un simple indicateur d'utilisation, pas un gage d'efficacité !<br >La performance de l'évaluation ne se résumant pas à la multiplicité des items évalués&hellip;</p>

<table id="bilan" class="hsort">
  <thead>
    <tr>
      <th>Professeur</th>
      <th>Classe</th>
      <th>Saisies</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $nb_lignes = 0;
    $DB_TAB = DB_STRUCTURE_DIRECTEUR::DB_compter_saisies_prof_classe();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        if($DB_ROW['nombre'])
        {
          // Afficher une ligne du tableau
          echo'<tr>';
          echo  '<td>'.html($DB_ROW['professeur']).'</td>';
          echo  '<td>'.html($DB_ROW['groupe_nom']).'</td>';
          echo  '<td class="hc">'.$DB_ROW['nombre'].'</td>';
          echo'</tr>'.NL;
          $nb_lignes++;
        }
      }
    }
    if(!$nb_lignes)
    {
      echo'<tr><td colspan="3" class="hc">Aucune saisie effectuée...</td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

