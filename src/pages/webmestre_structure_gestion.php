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
$TITRE = "Gestion des établissements"; // Pas de traduction car pas de choix de langue pour ce profil.

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$geo_id = (isset($_POST['f_geo_id'])) ? Clean::entier($_POST['f_geo_id']) : -1 ;

// Construire et personnaliser le formulaire "f_geo" pour le choix d'une zone géographique ainsi que le formulaire "f_geo_id" pour restreindre l'affichage
$select_f_geo    = HtmlForm::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_lister_zones() ,      FALSE /*select_nom*/ , '' /*option_first*/ , FALSE   /*selection*/ , '' /*optgroup*/ );
$select_f_geo_id = HtmlForm::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_lister_zones() , 'f_geo_id' /*select_nom*/ , '' /*option_first*/ , $geo_id /*selection*/ , '' /*optgroup*/ );
$selected = ($geo_id===0) ? ' selected' : '' ;
$select_f_geo_id = str_replace( '<option value="">&nbsp;</option>' , '<option value="">&nbsp;</option><option value="0"'.$selected.'>Toutes les zones</option>' , $select_f_geo_id );

$geo_ordre_longueur = 6;
$geo_ordre_format   = '%0'.$geo_ordre_longueur.'u';

// Javascript
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var input_date = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var date_mysql = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var geo_defaut = '.$geo_id.';' );
Layout::add( 'js_inline_before' , 'var geo_ordre_longueur = '.$geo_ordre_longueur.';' );
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var options_geo = "'.str_replace('"','\"',$select_f_geo).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__gestion_multi_etablissements">DOC : Gestion des établissements (multi-structures).</a></span></p>

<hr />

<form action="./index.php?page=webmestre_structure_gestion" method="post" id="form_prechoix">
  <div><label class="tab" for="f_groupe">Zone géographique :</label><?php echo $select_f_geo_id ?><input type="hidden" id="f_afficher" name="f_afficher" value="1" /></div>
</form>

<?php
if(empty($_POST['f_afficher']))
{
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<table id="table_action" class="form bilan_synthese vm_nug hsort">
  <thead>
    <tr>
      <th class="nu"></th>
      <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></th>
      <th>Id</th>
      <th>Localisation</th>
      <th>Établissement</th>
      <th>Contact</th>
      <th>Date</th>
      <th class="nu"><q class="ajouter" title="Ajouter un établissement."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les structures
    $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( FALSE /*listing_base_id*/ , $geo_id );
    foreach($DB_TAB as $DB_ROW)
    {
      // Afficher une ligne du tableau
      $img = (LockAcces::tester_blocage('webmestre',$DB_ROW['sacoche_base'])===NULL) ? '<img class="bloquer" src="./_img/etat/acces_oui.png" title="Bloquer cet établissement." />' : '<img class="debloquer" src="./_img/etat/acces_non.png" title="Débloquer cet établissement." />' ;
      echo'<tr id="id_'.$DB_ROW['sacoche_base'].'">';
      echo  '<td class="nu"><a href="#id_0">'.$img.'</a></td>';
      echo  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['sacoche_base'].'" /></td>';
      echo  '<td class="label">'.$DB_ROW['sacoche_base'].'</td>';
      echo  '<td class="label"><i>'.sprintf($geo_ordre_format,$DB_ROW['geo_ordre']).'</i>'.html($DB_ROW['geo_nom']).'<br />'.html($DB_ROW['structure_localisation']).'</td>';
      echo  '<td class="label">'.html($DB_ROW['structure_denomination']).'<br />'.html($DB_ROW['structure_uai']).'</td>';
      echo  '<td class="label"><span>'.html($DB_ROW['structure_contact_nom']).'</span> <span>'.html($DB_ROW['structure_contact_prenom']).'</span><div>'.html($DB_ROW['structure_contact_courriel']).'</div></td>';
      echo  '<td class="label">'.To::date_mysql_to_french($DB_ROW['structure_inscription_date']).'</td>';
      echo  '<td class="nu">';
      echo    '<q class="modifier" title="Modifier cet établissement."></q>';
      echo    '<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>';
      echo    '<q class="supprimer" title="Supprimer cet établissement."></q>';
      echo  '</td>';
      echo'</tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un établissement</h2>
  <div id="gestion_edit">
    <p id="p_ajout">
      <label class="tab" for="f_base_id">Id <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Numéro de la base de l'établissement.<br />Saisie facultative et déconseillée.<br />Nombre entier auto-incrémenté par défaut." /> :</label><input id="f_base_id" name="f_base_id" type="text" value="" size="4" maxlength="8" />
    </p>
    <p>
      <label class="tab" for="f_geo">Zone géographique :</label><select id="f_geo" name="f_geo"><option></option></select><br />
      <label class="tab" for="f_localisation">Localisation :</label><input id="f_localisation" name="f_localisation" type="text" value="" size="50" maxlength="50" /><br />
      <label class="tab" for="f_denomination">Dénomination :</label><input id="f_denomination" name="f_denomination" type="text" value="" size="50" maxlength="50" /><br />
      <label class="tab" for="f_uai">UAI :</label><input id="f_uai" name="f_uai" type="text" value="" size="10" maxlength="8" />
    </p>
    <p>
      <label class="tab" for="f_contact_nom">Contact Nom :</label><input id="f_contact_nom" name="f_contact_nom" type="text" value="" size="50" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" /><br />
      <label class="tab" for="f_contact_prenom">Contact Prénom :</label><input id="f_contact_prenom" name="f_contact_prenom" type="text" value="" size="50" maxlength="<?php echo PRENOM_LONGUEUR_MAX ?>" /><br />
      <label class="tab" for="f_contact_courriel">Contact Courriel :</label><input id="f_contact_courriel" name="f_contact_courriel" type="text" value="" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" /><br />
      <span id="span_envoi">
        <span class="tab"></span><input id="f_courriel_envoi" name="f_courriel_envoi" type="checkbox" value="1" checked /><label for="f_courriel_envoi"> envoyer le courriel d'inscription</label><br />
        <span class="tab"></span><input id="f_ajouter_copie" name="f_courriel_copie" type="checkbox" value="1" /><label for="f_ajouter_copie"> envoyer une copie à <?php echo html(WEBMESTRE_COURRIEL); ?></label>
      </span>
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">La base sera supprimée, donc tout le travail de l'établissement sera effacé !</p>
    <p>Confirmez-vous la suppression de l'établissement &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_date_fr" name="f_date_fr" type="hidden" value="" /><input id="f_acces" name="f_acces" type="hidden" value="" /><input id="f_check" name="f_check" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_generer_mdp" class="hide">
  <h2>Générer un nouveau mot de passe pour un administrateur d'établissement</h2>
  <p class="b" id="titre_generer_mdp"></p>
  <p>
    <label class="tab" for="f_admin_id">Administrateur :</label><select id="f_admin_id" name="f_admin_id"><option></option></select>
  </p>
  <p>
    <span class="tab"></span><input id="f_mdp_copie" name="f_courriel_copie" type="checkbox" value="1" /><label for="f_mdp_copie"> envoyer une copie à <?php echo html(WEBMESTRE_COURRIEL); ?></label>
  </p>
  <p>
    <button id="valider_generer_mdp" type="button" class="valider">Valider.</button> <button id="fermer_zone_generer_mdp" type="button" class="annuler">Annuler.</button> <label id="ajax_msg_generer_mdp">&nbsp;</label>
    <input id="generer_base_id" name="f_base_id" type="hidden" value="" />
  </p>
</form>

<form action="#" method="post" id="structures">
<div id="zone_actions" class="p">
  Pour les structures cochées :<input id="listing_ids" name="listing_ids" type="hidden" value="" />
  <button id="bouton_newsletter" type="button" class="mail_ecrire">Écrire un courriel.</button>
  <button id="bouton_stats" type="button" class="stats">Calculer les statistiques.</button>
  <button id="bouton_transfert" type="button" class="fichier_export">Exporter données &amp; bases.</button>
  <button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
  <label id="ajax_supprimer">&nbsp;</label>
</div>
</form>

