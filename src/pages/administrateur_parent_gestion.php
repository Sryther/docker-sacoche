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
$TITRE = html(Lang::_("Gérer les parents"));

// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$statut       = (isset($_POST['f_statut']))       ? Clean::entier($_POST['f_statut'])       : 1  ;
$debut_nom    = (isset($_POST['f_debut_nom']))    ? Clean::nom($_POST['f_debut_nom'])       : '' ;
$debut_prenom = (isset($_POST['f_debut_prenom'])) ? Clean::prenom($_POST['f_debut_prenom']) : '' ;
$find_doublon = (isset($_POST['f_doublon']))      ? TRUE                                    : FALSE ;
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

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></li>
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle#toggle_responsables_doublons_comptes">DOC : Import d'utilisateurs depuis Siècle / STS-Web - Doublons de comptes responsables</a></span></li>
</ul>
<p><span class="danger">Si votre établissement dépend d'une base administrative <em>Siècle</em> (2D) ou <em>Onde</em> (1D), alors évitez au maximum les ajouts manuels : utilisez <a href="./index.php?page=administrateur_fichier_user" target="_blank" rel="noopener noreferrer">des imports de fichiers</a>.</span></p>

<hr />

<form action="./index.php?page=administrateur_parent&amp;section=gestion" method="post" id="form_prechoix">
  <div><label class="tab" for="f_debut_nom">Recherche :</label>le nom commence par <input type="text" id="f_debut_nom" name="f_debut_nom" value="<?php echo html($debut_nom) ?>" size="5" /> le prénom commence par <input type="text" id="f_debut_prenom" name="f_debut_prenom" value="<?php echo html($debut_prenom) ?>" size="5" /> <input type="hidden" id="f_afficher" name="f_afficher" value="1" /><button id="actualiser" type="submit" class="actualiser">Actualiser.</button></div>
  <div><label class="tab" for="f_statut">Statut :</label><?php echo $select_f_statuts ?></div>
  <p class="ti"><button id="f_doublon" name="f_doublon" type="submit" class="rechercher">Rechercher</button> des responsables homonymes susceptibles d'être des comptes en double.</p>
</form>

<hr />

<?php

if(empty($_POST['f_afficher']))
{
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Options du formulaire de profils
$options = '';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_long_singulier' /*listing_champs*/ , TRUE /*only_actif*/ , 'parent' /*only_listing_profils_types*/ );
foreach($DB_TAB as $DB_ROW)
{
  $options .= '<option value="'.$DB_ROW['user_profil_sigle'].'">'.$DB_ROW['user_profil_sigle'].' &rarr; '.$DB_ROW['user_profil_nom_long_singulier'].'</option>';
}

// Lister les parents, par nom / prénom ou recherche d'homonymies
if(!$find_doublon)
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( FALSE /*with_adresse*/ , $statut , $debut_nom , $debut_prenom );
}
elseif($find_doublon) // (forcément)
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_homonymes();
  if(!empty($DB_TAB))
  {
    $tab_parents_id = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_parents_id = array_merge( $tab_parents_id , explode(',',$DB_ROW['identifiants']) );
    }
    $DB_TAB = count($tab_parents_id) ? DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( FALSE /*with_adresse*/ , TRUE /*statut*/ , '' /*debut_nom*/ , '' /*debut_prenom*/ , implode(',',$tab_parents_id) ) : array() ;
    // Préparation de l'export CSV
    $separateur = ';';
    // ajout du préfixe 'ENT_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
    $export_csv = 'ENT_ID'.$separateur.'SCONET_ID'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'RESPONSABILITES'."\r\n\r\n";
  }
}
?>

<table id="table_action" class="form t9 hsort">
  <thead>
    <tr>
      <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></th>
      <th>Resp</th>
      <th>Id. ENT</th>
      <th>Id. GEPI</th>
      <th>Id Sconet</th>
      <th>N° Sconet</th>
      <th>Référence</th>
      <th>Profil</th>
      <th>Civ.</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Login</th>
      <th>Mot de passe</th>
      <th>Courriel</th>
      <th>Date sortie</th>
      <th class="nu"><q class="ajouter" title="Ajouter un parent."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
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
        echo  ($DB_ROW['enfants_nombre']) ? '<td>'.$DB_ROW['enfants_nombre'].' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace('§BR§','<br />',html(html($DB_ROW['enfants_liste']))).'" /></td>' : '<td>0 <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Aucun lien de responsabilité !" /></td>' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        echo  '<td class="label">'.html($DB_ROW['user_id_ent']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_id_gepi']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_sconet_id']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_sconet_elenoet']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_reference']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_profil_sigle']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$DB_ROW['user_profil_sigle']].'" /></td>';
        echo  '<td class="label">'.Html::$tab_genre['adulte'][$DB_ROW['user_genre']].'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_nom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_prenom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_login']).'</td>';
        echo  '<td class="label i">champ crypté</td>';
        echo  '<td class="label">'.html($DB_ROW['user_email']).'</td>';
        echo  '<td class="label">'.$date_sortie.'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce parent."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
        // Export CSV
        if($find_doublon)
        {
          $export_csv .= $DB_ROW['user_id_ent'].$separateur.$DB_ROW['user_sconet_id'].$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.str_replace('§BR§',$separateur,$DB_ROW['enfants_liste'])."\r\n";
        }
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="15"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<?php
if( $find_doublon && !empty($DB_TAB) )
{
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'extraction_doublons_responsables_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  echo'<p><ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données dans un fichier (format <em>csv</em></span>).</a></li></ul></p>'.NL;
}
?>

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
    <label class="tab" for="f_sconet_id">Id Sconet <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Champ de Sconet PERSONNE.PERSONNE_ID (laisser vide ou à 0 si inconnu)." /> :</label><input id="f_sconet_id" name="f_sconet_id" type="text" value="" size="15" maxlength="8" /><br />
    <label class="tab" for="f_sconet_num">N° Sconet <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Champ de Factos IDENTIFIANT GEP (laisser vide ou à 0 si inconnu)." /> :</label><input id="f_sconet_num" name="f_sconet_num" type="text" value="" size="15" maxlength="5" /><br />
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

<div id="temp_td" class="hide"></div>
