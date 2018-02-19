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
$TITRE = html(Lang::_("Professeurs / Personnels"));

// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$statut = (isset($_POST['f_statut'])) ? Clean::entier($_POST['f_statut']) : 1  ;
// Construire et personnaliser le formulaire pour restreindre l'affichage
$select_f_statuts = HtmlForm::afficher_select(Form::$tab_select_statut , 'f_statut' /*select_nom*/ , FALSE /*option_first*/ , $statut /*selection*/ , '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var input_date      = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var date_mysql      = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var LOGIN_LONGUEUR_MAX = '.LOGIN_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var ID_ENT_LONGUEUR_MAX = '.ID_ENT_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var ID_GEPI_LONGUEUR_MAX = '.ID_GEPI_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var tab_profil            = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_login_modele      = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_mdp_longueur_mini = new Array();' );

// Options du formulaire de profils (en tableau js pour le retour ajax)
$options   = '';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_long_singulier' /*listing_champs*/ , TRUE /*only_actif*/ , array('professeur','directeur') /*only_listing_profils_types*/ );
foreach($DB_TAB as $DB_ROW)
{
  $options   .= '<option value="'.$DB_ROW['user_profil_sigle'].'">'.$DB_ROW['user_profil_sigle'].' &rarr; '.$DB_ROW['user_profil_nom_long_singulier'].'</option>';
  Layout::add( 'js_inline_before' , 'tab_profil["'.$DB_ROW['user_profil_sigle'].'"]="'.html(html($DB_ROW['user_profil_nom_long_singulier'])).'";' );
}

foreach($_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] as $profil_sigle => $login_modele)
{
  Layout::add( 'js_inline_before' , 'tab_login_modele["'.$profil_sigle.'"] = "'.$login_modele.'";' );
}
foreach($_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] as $profil_sigle => $mdp_longueur_mini)
{
  Layout::add( 'js_inline_before' , 'tab_mdp_longueur_mini["'.$profil_sigle.'"] = '.$mdp_longueur_mini.';' );
}
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs">DOC : Gestion des professeurs et personnels</a></span></p>
<p><span class="danger">Si votre établissement dépend d'une base administrative <em>Siècle</em> (2D), alors évitez au maximum les ajouts manuels : utilisez <a href="./index.php?page=administrateur_fichier_user" target="_blank" rel="noopener noreferrer">des imports de fichiers</a>.</span></p>

<form action="./index.php?page=administrateur_professeur&amp;section=gestion" method="post" id="form_prechoix">
  <div><label class="tab" for="f_statut">Statut :</label><?php echo $select_f_statuts ?></div>
</form>

<hr />

<table id="table_action" class="form t9 hsort">
  <thead>
    <tr>
      <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></th>
      <th>Id. ENT</th>
      <th>Id. GEPI</th>
      <th>Id Sconet</th>
      <th>Référence</th>
      <th>Profil</th>
      <th>Civ.</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Login</th>
      <th>Mot de passe</th>
      <th>Courriel</th>
      <th>Date sortie</th>
      <th class="nu"><q class="ajouter" title="Ajouter un personnel."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les personnels (professeurs, directeurs, etc.)
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( array('professeur','directeur') , $statut , 'user_id,user_id_ent,user_id_gepi,user_sconet_id,user_reference,user_profil_sigle,user_profil_nom_long_singulier,user_genre,user_nom,user_prenom,user_login,user_email,user_sortie_date' /*liste_champs*/ , FALSE /*with_classe*/ );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Formater la date
        $date_mysql  = $DB_ROW['user_sortie_date'];
        $date_sortie = ($date_mysql!=SORTIE_DEFAUT_MYSQL) ? To::date_mysql_to_french($date_mysql) : '-' ;
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['user_id'].'">';
        echo  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['user_id'].'" /></td>';
        echo  '<td class="label">'.html($DB_ROW['user_id_ent']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_id_gepi']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_sconet_id']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_reference']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_profil_sigle']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($DB_ROW['user_profil_nom_long_singulier'])).'" /></td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        echo  '<td class="label">'.Html::$tab_genre['adulte'][$DB_ROW['user_genre']].'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_nom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_prenom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_login']).'</td>';
        echo  '<td class="label i">champ crypté</td>';
        echo  '<td class="label">'.html($DB_ROW['user_email']).'</td>';
        echo  '<td class="label">'.$date_sortie.'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce personnel."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="13"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<div id="zone_actions" class="p ml">
  <div class="p"><span class="u">Pour les utilisateurs cochés :</span> <input id="listing_ids" name="listing_ids" type="hidden" value="" /><label id="ajax_msg_actions">&nbsp;</label></div>
  <button id="retirer" type="button" class="user_desactiver">Retirer</button> (date de sortie au <?php echo TODAY_FR ?>).<br />
  <button id="reintegrer" type="button" class="user_ajouter">Réintégrer</button> (retrait de la date de sortie).<br />
  <button id="supprimer" type="button" class="supprimer">Supprimer</button> sans attendre 3 ans (uniquement si déjà sortis).
</div>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier un utilisateur</h2>
  <p>
    <label class="tab" for="f_id_ent">Id. ENT <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement en cas d'identification via un ENT." /> :</label><input id="f_id_ent" name="f_id_ent" type="text" value="" size="40" maxlength="<?php echo ID_ENT_LONGUEUR_MAX ?>" /><br />
    <label class="tab" for="f_id_gepi">Id. GEPI <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement en cas d'utilisation du logiciel GEPI." /> :</label><input id="f_id_gepi" name="f_id_gepi" type="text" value="" size="40" maxlength="<?php echo ID_GEPI_LONGUEUR_MAX ?>" /><br />
    <label class="tab" for="f_sconet_id">Id Sconet <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Champ de STS-Web INDIVIDU.ID (laisser vide ou à 0 si inconnu)." /> :</label><input id="f_sconet_id" name="f_sconet_id" type="text" value="" size="15" maxlength="8" /><br />
    <label class="tab" for="f_reference">Référence <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Sconet : champ inutilisé (laisser vide).<br />Tableur : référence dans l'établissement." /> :</label><input id="f_reference" name="f_reference" type="text" value="" size="15" maxlength="11" />
  </p>
  <p>
    <label class="tab" for="f_profil">Profil :</label><select id="f_profil" name="f_profil"><?php echo $options ?></select>
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
  <p>
    <label class="tab" for="f_sortie_date">Date de sortie :</label><input id="box_date" name="box_date" value="1" type="checkbox" /> <label for="box_date">sans objet</label><span><input id="f_sortie_date" name="f_sortie_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span>
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><input id="f_check" name="f_check" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
