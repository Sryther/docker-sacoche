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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Enseignements de complément"));

if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_LIVRET_ENSCOMPL'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) en complément des personnels de direction :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_ENSCOMPL'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Indication des profils autorisés
$puce_profils_autorises = ($_SESSION['USER_PROFIL_TYPE']!='professeur') ? '' : '<li><span class="astuce"><a title="administrateurs (de l\'établissement)<br />personnels de direction<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_ELEMENTS'],'br').'" href="#">Profils pouvant accéder à ce menu de configuration.</a></span></li>';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_enscompl">DOC : Administration du Livret Scolaire &rarr; Enseignements de complément</a></span></li>
  <li><span class="astuce">Ce menu ne sert que pour le <b>bilan de fin de cycle 4</b> (sans objet pour les <b>bilans périodiques</b> ou de <b>fin d'autres cycles</b>).</span></li>
  <?php echo $puce_profils_autorises ?>
</ul>

<hr />

<?php
if( !DB_STRUCTURE_LIVRET::DB_tester_classes_enscompl() )
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'un administrateur ou directeur doit commencer' : 'commencez' ;
  echo'<p class="danger">Aucune classe n\'est associée à une page du livret concernée par ce dispositif !<br />Si besoin, '.$consigne.' par <a href="./index.php?page=livret&amp;section=classes">associer les classes au livret scolaire</a>.</p>'.NL;
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
  if(Outil::test_droit_specifique_restreint($_SESSION['DROIT_GERER_LIVRET_ENSCOMPL'],'ONLY_PP'))
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
$tab_enscompl = DB_STRUCTURE_LIVRET::DB_OPT_enscompl();

$select_eleve    = HtmlForm::afficher_select($tab_groupes  , 'select_groupe' /*select_nom*/ , $of_g /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_enscompl = HtmlForm::afficher_select($tab_enscompl , 'f_enscompl'    /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );

// Javascript
$listing_classe_id = implode(',',$tab_groupe_js);
Layout::add( 'js_inline_before' , 'var only_groupes_id="'.$listing_classe_id.'";' );
?>

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <?php echo $select_eleve ?><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Enseignements de complément :</b><br />
      <?php echo $select_enscompl ?>
    </td>
    <td class="nu" style="width:25em">
      <button id="bouton_associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<hr />

<div id="bilan">
</div>
