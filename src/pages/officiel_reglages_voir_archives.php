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
$TITRE = html(Lang::_("Archives des bilans officiels"));
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__archives">DOC : Archives consultables.</a></span></li>
</ul>

<hr />

<?php
// Fabrication des éléments select du formulaire

$tab_type_ref = DB_STRUCTURE_COMMUN::DB_OPT_officiel_archive_type_ref();
if(empty($tab_type_ref))
{
  echo'<p class="danger">Aucune archive de bilan officiel trouvée !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$tab_structure = DB_STRUCTURE_COMMUN::DB_OPT_officiel_archive_structure();
$tab_annees    = DB_STRUCTURE_COMMUN::DB_OPT_officiel_archive_annee();

// Limiter les types de bilan aux selon les droits

if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
{
  foreach($tab_type_ref as $key => $tab)
  {
    $objet = ($tab['optgroup']=='livret') ? 'LIVRET' : strtoupper(substr($tab['valeur'],8)) ; // sacoche_
    if(!Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$objet.'_VOIR_ARCHIVE']))
    {
      unset($tab_type_ref[$key]);
    }
  }
}
if(empty($tab_type_ref))
{
  echo'<p class="danger">Vous n\'avez pas les droits pour consulter les types d\'archives disponibles !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !in_array($_SESSION['USER_PROFIL_SIGLE'],array('EDU','ADF')) )
{
  $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
}
else // directeur ou administrateur
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( TRUE /*sans*/ , TRUE /*tout*/ , TRUE /*ancien*/ );
}

$select_groupe    = HtmlForm::afficher_select($tab_groupes   , 'f_groupe'    /*select_nom*/ , ''                  /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_structure = HtmlForm::afficher_select($tab_structure , 'f_structure' /*select_nom*/ , 'toutes_structures' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
$select_annee     = HtmlForm::afficher_select($tab_annees    , 'f_annee'     /*select_nom*/ , 'toutes_annees'     /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
$select_type_ref  = HtmlForm::afficher_select($tab_type_ref  , 'f_type_ref'  /*select_nom*/ , FALSE               /*option_first*/ , FALSE /*selection*/ , 'officiel_type' /*optgroup*/ , TRUE /*multiple*/ );

?>

<form action="#" method="post" id="form_select"><fieldset>
  <p>
    <label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?><label id="ajax_msg_groupe">&nbsp;</label><input id="listing_ids" name="listing_ids" type="hidden" value="" /><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span><br />
    <label id="ajax_msg_uai_origine">&nbsp;</label>
    <span id="bloc_uai_origine" class="hide"><label class="tab" for="f_uai_origine">Scolarité antérieure :</label><select id="f_uai_origine" name="f_uai_origine"><option></option></select></span>
  </p>
  <p>
    <label class="tab" for="f_structure">Établissement :</label><?php echo $select_structure ?><br />
    <label class="tab" for="f_annee">Année(s) :</label><?php echo $select_annee ?><label id="ajax_msg_annee">&nbsp;</label><br />
    <span id="bloc_periode" class="hide"><label class="tab" for="f_periode">Période(s) :</label><select id="f_periode" name="f_periode"><option></option></select></span>
  </p>
  <p>
    <label class="tab" for="f_type_ref">Type(s) :</label><span id="f_type_ref" class="select_multiple"><?php echo $select_type_ref ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="generer_pdf" /><button id="bouton_valider" type="submit" class="valider">Accéder à ces archives</button><label id="ajax_msg">&nbsp;</label>
  </p>
</fieldset></form>

<hr />

<p id="lien_zip"></p>

<table id="statistiques" class="form hide">
  <thead>
    <tr>
      <th>Année scolaire</th>
      <th>Période</th>
      <th>Établissement</th>
      <th>Objet</th>
      <th>Élève</th>
      <th>Lien</th>
    </tr>
  </thead>
  <tbody>
    <tr class="vide"><td class="nu" colspan="6"></td></tr>
  </tbody>
</table>
</div>
