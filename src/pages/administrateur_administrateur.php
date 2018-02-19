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
$TITRE = html(Lang::_("Gérer les administrateurs"));

// Javascript
Layout::add( 'js_inline_before' , 'var LOGIN_LONGUEUR_MAX = '.LOGIN_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var ID_ENT_LONGUEUR_MAX = '.ID_ENT_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var ID_GEPI_LONGUEUR_MAX = '.ID_GEPI_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var tab_login_modele      = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_mdp_longueur_mini = new Array();' );
foreach($_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] as $profil_sigle => $login_modele)
{
  Layout::add( 'js_inline_before' , 'tab_login_modele["'.$profil_sigle.'"] = "'.$login_modele.'";' );
}
foreach($_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] as $profil_sigle => $mdp_longueur_mini)
{
  Layout::add( 'js_inline_before' , 'tab_mdp_longueur_mini["'.$profil_sigle.'"] = '.$mdp_longueur_mini.';' );
}
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_administrateurs">DOC : Gestion des administrateurs</a></span></p>
<?php if(HEBERGEUR_INSTALLATION=='multi-structures'): ?>
<p class="astuce">Si besoin vous pouvez aussi <a href="./index.php?page=administrateur_etabl_identite">indiquer un nouveau contact référent</a>.</p>
<?php endif; ?>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Id. ENT</th>
      <th>Id. GEPI</th>
      <th>Civ.</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Login</th>
      <th>Mot de passe</th>
      <th>Courriel</th>
      <th class="nu"><q class="ajouter" title="Ajouter un administrateur."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les administrateurs
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'administrateur' , 1 /*only_actuels*/ , 'user_id,user_id_ent,user_id_gepi,user_genre,user_nom,user_prenom,user_login,user_email' /*liste_champs*/ , FALSE /*with_classe*/ );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['user_id'].'">';
        echo  '<td>'.html($DB_ROW['user_id_ent']).'</td>';
        echo  '<td>'.html($DB_ROW['user_id_gepi']).'</td>';
        echo  '<td>'.Html::$tab_genre['adulte'][$DB_ROW['user_genre']].'</td>';
        echo  '<td>'.html($DB_ROW['user_nom']).'</td>';
        echo  '<td>'.html($DB_ROW['user_prenom']).'</td>';
        echo  '<td>'.html($DB_ROW['user_login']).'</td>';
        echo  '<td class="i">champ crypté</td>';
        echo  '<td>'.html($DB_ROW['user_email']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cet administrateur."></q>';
        echo    ($DB_ROW['user_id']!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Retirer cet administrateur."></q>' : '<q class="supprimer_non" title="Un administrateur ne peut pas supprimer son propre compte."></q>' ;
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      // Normalement impossible, puisqu'on est justement connecté comme administrateur !
      echo'<tr class="vide"><td class="nu" colspan="8"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un utilisateur</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_id_ent">Id. ENT <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement en cas d'identification via un ENT." /> :</label><input id="f_id_ent" name="f_id_ent" type="text" value="" size="40" maxlength="63" /><br />
      <label class="tab" for="f_id_gepi">Id. GEPI <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement en cas d'utilisation du logiciel GEPI." /> :</label><input id="f_id_gepi" name="f_id_gepi" type="text" value="" size="40" maxlength="63" />
    </p>
    <p>
      <label class="tab" for="f_genre">Civilité :</label><select id="f_genre" name="f_genre"><option value="I"></option><option value="M">Monsieur</option><option value="F">Madame</option></select><br />
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="40" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" /><br />
      <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" value="" size="40" maxlength="<?php echo PRENOM_LONGUEUR_MAX ?>" /><br />
      <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="" size="40" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" />
    </p>
    <p>
      <label class="tab" for="f_login">Login :</label><input id="box_login" name="box_login" value="1" type="checkbox" checked /> <label for="box_login">automatique | inchangé</label><span><input id="f_login" name="f_login" type="text" value="" size="40" maxlength="<?php echo LOGIN_LONGUEUR_MAX ?>" /></span><br />
      <label class="tab" for="f_password">Mot de passe :</label><input id="box_password" name="box_password" value="1" type="checkbox" checked /> <label for="box_password">aléatoire | inchangé</label><span><input id="f_password" name="f_password" size="40" maxlength="<?php echo PASSWORD_LONGUEUR_MAX ?>" type="text" value="" /></span>
    </p>
  </div>
  <p id="gestion_delete">
    Confirmez-vous la suppression du compte administrateur &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
