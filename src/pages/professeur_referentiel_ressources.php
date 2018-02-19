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
$TITRE = html(Lang::_("Associer des ressources aux items"));

// Acces serveur communautaire
$acces_serveur_communautaire = ( $_SESSION['SESAMATH_ID'] && $_SESSION['SESAMATH_KEY'] ) ? TRUE : FALSE ;

// Javascript
Layout::add( 'js_inline_before' , 'var etablissement_identifie = '.(int)$acces_serveur_communautaire.';' );

if(!Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_RESSOURCE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_RESSOURCE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_lier_ressources">DOC : Associer aux items des ressources pour travailler.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="zone_choix_referentiel" onsubmit="return false;">
<?php
$texte_profil = Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_RESSOURCE'],'br');
// On récupère la liste des référentiels des matières auxquelles le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_matieres_niveaux_referentiels_professeur($_SESSION['USER_ID']);
if(empty($DB_TAB))
{
  echo'<ul class="puce">'.NL;
  echo  '<li><span class="danger">Aucun référentiel présent parmi les matières qui vous sont rattachées !</span></li>'.NL;
  echo  '<li><span class="astuce">Commencer par <a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer ou importer un référentiel</a>.</span></li>'.NL;
  echo'</ul>'.NL;
}
else
{
  // On récupère les données
  $tab_matiere = array();
  $tab_colonne = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if(!isset($tab_matiere[$DB_ROW['matiere_id']]))
    {
      $matiere_droit = Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_RESSOURCE'] , $DB_ROW['jointure_coord'] /*matiere_coord_or_groupe_pp_connu*/ );
      $icone_action  = ($matiere_droit) ? '<q class="modifier" title="Modifier les ressources de ce référentiel."></q>' : '<q class="modifier_non" title="Droit d\'accès : '.$texte_profil.'."></q>' ;
      $tab_matiere[$DB_ROW['matiere_id']] = array(
        'matiere_nom' => html($DB_ROW['matiere_nom']),
        'matiere_ref' => Clean::id($DB_ROW['matiere_ref']),
        'matiere_act' => $icone_action,
      );
    }
    $tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td>'.html($DB_ROW['niveau_nom']).'</td><td class="nu" id="td_'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'">'.$tab_matiere[$DB_ROW['matiere_id']]['matiere_act'].'</td>';
  }
  // On construit et affiche le tableau résultant
  $affichage = '<table class="vm_nug"><thead>'.NL.'<tr><th>Matière</th><th>Niveau</th><th class="nu"></th></tr>'.NL.'</thead><tbody>'.NL;
  foreach($tab_matiere as $matiere_id => $tab)
  {
    $rowspan = count($tab_colonne[$matiere_id]);
    foreach($tab_colonne[$matiere_id] as $niveau_id => $cellules)
    {
      if($rowspan)
      {
        $affichage .= '<tr class="tr_'.$tab['matiere_ref'].'"><td rowspan="'.$rowspan.'">'.$tab['matiere_nom'].'</td>'.$cellules.'</tr>'.NL;
        $rowspan = 0;
      }
      else
      {
        $affichage .= '<tr class="tr_'.$tab['matiere_ref'].'">'.$cellules.'</tr>'.NL;
      }
    }
  }
  $affichage .= '</tbody></table>'.NL;
  echo $affichage;
}
?>
</form>

<form action="#" method="post" id="zone_elaboration_referentiel" onsubmit="return false;" class="arbre_dynamique">
</form>

<div id="zone_ressources" class="hide">
  <form action="#" method="post" id="zone_ressources_form">
    <h2>Liens (ressources pour travailler)</h2>
    <p><label class="tab">Item :</label><span class="f_nom i"></span><input type="hidden" id="page_mode" value="" /></p>
    <ul id="sortable_v">
      <li></li>
    </ul>
    <div><span class="tab"></span><button class="annuler" type="button" id="choisir_ressources_annuler">Annuler / Retour.</button> <button class="valider" type="button" id="choisir_ressources_valider">Valider et enregistrer ces liens.</button> <label id="ajax_ressources_msg">&nbsp;</label></div>
    <hr />
    <h2>Ajouter un paragraphe</h2>
    <div class="sortable"><label class="tab">Sous-titre :</label><input id="paragraphe_nom" value="" size="80" maxlength="256" /><q id="paragraphe_ajouter" class="ajouter" title="Ajouter ce paragraphe"></q><label for="paragraphe_nom"></label></div>
    <h2>Ajouter un lien</h2>
    <div class="sortable"><label class="tab">Adresse :</label><input id="lien_url" value="" size="80" maxlength="256" /><?php echo $acces_serveur_communautaire ? '<q id="afficher_zone_ressources_upload" class="ress_ajouter" title="Déposer une ressource sur le serveur communautaire (afin de pouvoir faire pointer un lien vers celle-ci)."></q>' : '<q class="partager_non" title="Pour pouvoir mettre en ligne une ressource sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath."></q>' ;?><label for="lien_url"></label><br /><label class="tab">Intitulé :</label><input id="lien_nom" value="" size="80" maxlength="256" /><q id="lien_ajouter" class="ajouter" title="Ajouter ce lien"></q><label for="lien_nom"></label></div>
    <h2>Recherche de liens existants</h2>
    <div class="sortable"><label class="tab">Mots clefs :</label><input id="chaine_recherche" value="" size="80" maxlength="256" /> <button id="liens_rechercher" type="button" class="rechercher">Chercher.</button></div>
    <div id="zone_resultat_recherche_liens"></div>
  </form>
  <form action="#" method="post" id="zone_ressources_upload" class="hide">
    <h2>Mettre en ligne une ressource</h2>
    <ul class="puce">
      <li><span class="danger">Lisez la documentation afin de prendre connaissance des conditions d'utilisation !</span></li>
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_uploader_ressources">DOC : Mettre en ligne des ressources pour travailler.</a></span></li>
    </ul>
    <p>
      <input type="checkbox" id="acceptation_conditions" name="acceptation_conditions" /><label for="acceptation_conditions"> J'ai lu, j'accepte et je respecte les conditions d'utilisation de ce service.</label><br />
      <input type="hidden" id="f_ressource_action" name="f_action" value="Uploader_document" />
      <input type="hidden" id="f_ressource_matiere" name="matiere_ref" value="" />
      <input id="f_ressource" type="file" name="userfile" />
      <button id="bouton_choisir_ressource" type="button" class="fichier_import" disabled>Parcourir...</button>
      <button id="afficher_zone_ressources_form" type="button" class="retourner">Annuler.</button><label id="ajax_ressources_upload">&nbsp;</label>
    </p>
    <p>
      <button id="ressources_rechercher" type="button" class="rechercher">Voir les fichiers mis en ligne dans mon établissement.</button>
    </p>
    <div id="zone_resultat_recherche_ressources"></div>
  </form>
</div>
