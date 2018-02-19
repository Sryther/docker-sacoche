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
$TITRE = html(Lang::_("Choix des profils utilisateurs"));
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_profils">DOC : Choix des profils utilisateurs</a></span></div>

<hr />

<form action="#" method="post" id="form_principal">
  <table id="table_action" class="form">
    <thead>
      <tr><th class="nu"></th><th>Sigle</th><th>Profil</th><th>Obligatoire</th><th>Rattachement aux groupes</th><th>Rattachement aux matières</th></tr>
    </thead>
    <tbody>
      <?php
      $tab_txt_groupes  = array( 'sansobjet' => 'sans objet' , 'auto' => 'automatique (selon affectations)' , 'all' => 'automatique (à tous)'   , 'config' => 'à configurer' );
      $tab_txt_matieres = array( 'sansobjet' => 'sans objet' , 'auto' => 'automatique (selon affectations)' , 'all' => 'automatique (à toutes)' , 'config' => 'à configurer (si évaluation)' );
      // Lister les profils
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_actif,user_profil_obligatoire,user_profil_join_groupes,user_profil_join_matieres,user_profil_nom_long_singulier' /*listing_champs*/ , FALSE /*only_actif*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        $checked  = ($DB_ROW['user_profil_actif'])       ? ' checked'  : '' ;
        $disabled = ($DB_ROW['user_profil_obligatoire']) ? ' disabled' : '' ; // readonly ne fonctionne que sur les input de type "text".
        $txt_obligatoire = ($DB_ROW['user_profil_obligatoire']) ? 'oui' : 'non' ;
        echo'<tr>';
        echo  '<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['user_profil_sigle'].'"'.$checked.$disabled.' /></td>';
        echo  '<td class="label">'.html($DB_ROW['user_profil_sigle']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_profil_nom_long_singulier']).'</td>';
        echo  '<td class="label">'.$txt_obligatoire.'</td>';
        echo  '<td class="label">'.$tab_txt_groupes[$DB_ROW['user_profil_join_groupes']].'</td>';
        echo  '<td class="label">'.$tab_txt_matieres[$DB_ROW['user_profil_join_matieres']].'</td>';
        echo'</tr>'.NL;
      }
      ?>
    </tbody>
  </table>
  <p>
    <span class="tab"></span><button id="bouton_valider" type="button" class="parametre">Valider ce choix de profils.</button><label id="ajax_msg">&nbsp;</label>
  </p>
</form>

<hr />


