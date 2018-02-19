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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Modalités d'accompagnement"));

if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_LIVRET_MODACCOMP'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) en complément des personnels de direction :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_MODACCOMP'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Indication des profils autorisés
$puce_profils_autorises = ($_SESSION['USER_PROFIL_TYPE']!='professeur') ? '' : '<li><span class="astuce"><a title="administrateurs (de l\'établissement)<br />personnels de direction<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_MODACCOMP'],'br').'" href="#">Profils pouvant accéder à ce menu de configuration.</a></span></li>';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_modaccomp">DOC : Administration du Livret Scolaire &rarr; Modalités d'accompagnement</a></span></li>
  <li><span class="astuce">Ce menu ne sert que pour les <b>bilans périodiques</b> (sans objet pour les <b>bilans de fin de cycle</b>).</span></li>
  <?php echo $puce_profils_autorises ?>
</ul>

<hr />

<?php
if( !DB_STRUCTURE_LIVRET::DB_tester_classes_periode() )
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'un administrateur ou directeur doit commencer' : 'commencez' ;
  echo'<p class="danger">Aucune classe n\'est associée à une page du livret avec un bilan périodique !<br />Si besoin, '.$consigne.' par <a href="./index.php?page=livret&amp;section=classes">associer les classes au livret scolaire</a>.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$tab_groupe_js = array();
// Fabrication des éléments select du formulaire
if( ($_SESSION['USER_PROFIL_TYPE']!='professeur') || ($_SESSION['USER_JOIN_GROUPES']=='all') ) // Directeurs et CPE, ces derniers ayant un 'USER_PROFIL_TYPE' à 'professeur'.
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = '';
}
else // Ne passent ici que les professeurs
{
  if(Outil::test_droit_specifique_restreint($_SESSION['DROIT_GERER_LIVRET_MODACCOMP'],'ONLY_PP'))
  {
    $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_prof_principal($_SESSION['USER_ID']);
    $of_g = FALSE;
  }
  else
  {
    $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']);
    $of_g = '';
  }
  foreach($tab_groupes as $tab_groupe_option)
  {
    if( ($tab_groupe_option['optgroup']=='classe') && !isset($tab_groupe_js[$tab_groupe_option['valeur']]) )
    {
      $tab_groupe_js[$tab_groupe_option['valeur']] = $tab_groupe_option['valeur'];
    }
  }
}
$tab_modaccomp = DB_STRUCTURE_LIVRET::DB_OPT_modaccomp();

$select_eleve     = HtmlForm::afficher_select($tab_groupes   , 'select_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_modaccomp = HtmlForm::afficher_select($tab_modaccomp , 'f_modaccomp'   /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );

// Javascript
$listing_classe_id = implode(',',$tab_groupe_js);
Layout::add( 'js_inline_before' , 'var only_groupes_id="'.$listing_classe_id.'";' );
Layout::add( 'js_inline_before' , 'var tab_commentaire = new Array();' );
?>

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <?php echo $select_eleve ?><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Modalité d'accompagnement :</b><br />
      <?php echo $select_modaccomp ?>
      <p id="p_commentaire" class="hide">
        <b>Commentaire :</b><br />
        <textarea name="f_commentaire" id="input_commentaire" rows="5" cols="40"></textarea><br />
        <label id="input_commentaire_reste"></label>
      </p>
    </td>
    <td class="nu" style="width:25em">
      <button id="bouton_associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Classe</th>
      <th>Élève</th>
      <th>Dispositif</th>
      <th>Commentaire</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <tr class="vide"><td class="nu" colspan="4"></td><td class="nu"></td></tr>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Modifier le descriptif du PPRE</h2>
  <p>
    <label class="tab">Classe :</label><b id="b_classe"></b><br />
    <label class="tab">Élève :</label><b id="b_eleve"></b><br />
    <label class="tab" for="f_commentaire">Commentaire :</label><textarea name="f_commentaire" id="f_commentaire" rows="5" cols="40"></textarea><br />
    <span class="tab"></span><label id="f_commentaire_reste"></label>
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="modifier_commentaire" /><input id="ppre_eleve" name="f_eleve" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

