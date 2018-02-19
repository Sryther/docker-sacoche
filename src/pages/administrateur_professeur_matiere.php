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
$TITRE = html(Lang::_("Personnels & matières / Personnels coordonnateurs"));
?>

<?php
// Fabrication des éléments select du formulaire
$select_prof    = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl('all') , 'f_prof'    /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ );
$select_matiere = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl()         , 'f_matiere' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs#toggle_affecter_matieres">DOC : Gestion des professeurs et personnels</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Professeurs :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="f_prof" class="select_multiple"><?php echo $select_prof ?></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Matières :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="f_matiere" class="select_multiple"><?php echo $select_matiere; ?></span>
    </td>
    <td class="nu" style="width:25em">
      <p><button id="ajouter" type="button" class="groupe_ajouter">Ajouter ces associations.</button></p>
      <p><button id="retirer" type="button" class="groupe_retirer">Retirer ces associations.</button></p>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<?php
// Deux requêtes préliminaires pour ne pas manquer les matières sans professeurs et les professeurs sans matières
$tab_js                = 'var tab_join = new Array();'; // [i_matiere][i_prof] => 1 | 2
$tab_matieres          = array();
$tab_profs             = array();
$tab_profs_par_matiere = array();
$tab_matieres_par_prof = array();
$tab_lignes_matieres   = array();
$tab_lignes_profs      = array();

// Récupérer la liste des matières
$DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucune matière trouvée !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$compteur = 0 ;
foreach($DB_TAB as $DB_ROW)
{
  $tab_js .= 'tab_join['.$DB_ROW['matiere_id'].'] = new Array();';
  $tab_matieres[$DB_ROW['matiere_id']] = html($DB_ROW['matiere_nom']);
  $tab_profs_par_matiere[$DB_ROW['matiere_id']] = '';
  $tab_lignes_matieres[floor($compteur/8)][] = $DB_ROW['matiere_id'];
  $compteur++;
}

// Récupérer la liste des personnels
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'professeur' , 1 /*only_actuels*/ , 'user_id,user_nom,user_prenom' /*liste_champs*/ , FALSE /*with_classe*/ );
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun compte personnel trouvé !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$compteur = 0 ;
foreach($DB_TAB as $DB_ROW)
{
  $tab_principal[0][$DB_ROW['user_id']] = '<th id="th_'.$DB_ROW['user_id'].'"><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'" /></th>';
  $tab_profs[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  $tab_matieres_par_prof[$DB_ROW['user_id']] = '';
  $tab_lignes_profs[floor($compteur/8)][] = $DB_ROW['user_id'];
  $compteur++;
}

// Récupérer la liste des jointures
$DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_jointure_professeurs_matieres();
foreach($DB_TAB as $DB_ROW)
{
  if( (isset($tab_profs[$DB_ROW['user_id']])) && (isset($tab_matieres[$DB_ROW['matiere_id']])) )
  {
    $checked = ($DB_ROW['jointure_coord']) ? ' checked' : '' ;
    $classe  = ($DB_ROW['jointure_coord']) ? 'on' : 'off' ;
    $js_val  = ($DB_ROW['jointure_coord']) ? 2 : 1 ;
    $tab_js .= 'tab_join['.$DB_ROW['matiere_id'].']['.$DB_ROW['user_id'].']='.$js_val.';';
    $tab_profs_par_matiere[$DB_ROW['matiere_id']] .= '<div id="mp_'.$DB_ROW['matiere_id'].'_'.$DB_ROW['user_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['matiere_id'].'mp'.$DB_ROW['user_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['matiere_id'].'mp'.$DB_ROW['user_id'].'">'.$tab_profs[$DB_ROW['user_id']].'</label></div>';
    $tab_matieres_par_prof[$DB_ROW['user_id']]   .= '<div id="pm_'.$DB_ROW['user_id'].'_'.$DB_ROW['matiere_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['user_id'].'pm'.$DB_ROW['matiere_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['user_id'].'pm'.$DB_ROW['matiere_id'].'">'.$tab_matieres[$DB_ROW['matiere_id']].'</label></div>';
  }
}

// Assemblage du tableau des personnels par matière
$TH = array();
$TB = array();
$TF = array();
foreach($tab_lignes_matieres as $niveau_id => $tab_matiere)
{
  $TH[$niveau_id] = '';
  $TB[$niveau_id] = '';
  $TF[$niveau_id] = '';
  foreach($tab_matiere as $matiere_id)
  {
    $nb = mb_substr_count($tab_profs_par_matiere[$matiere_id],'</div>','UTF-8');
    $s = ($nb>1) ? 's' : '' ;
    $TH[$niveau_id] .= '<th id="mph_'.$matiere_id.'">'.$tab_matieres[$matiere_id].'</th>';
    $TB[$niveau_id] .= '<td id="mpb_'.$matiere_id.'">'.$tab_profs_par_matiere[$matiere_id].'</td>';
    $TF[$niveau_id] .= '<td id="mpf_'.$matiere_id.'">'.$nb.' professeur'.$s.'</td>';
  }
}

// Affichage du tableau des personnels par matière
echo'<hr /><h2>Bilan des personnels par matière</h2>'.NL;
echo'<div class="astuce">Cocher les personnels coordonnateurs.</div>'.NL;
foreach($tab_lignes_matieres as $niveau_id => $tab_matiere)
{
  echo'<table class="affectation">'.NL;
  echo  '<thead><tr>'.$TH[$niveau_id].'</tr></thead>'.NL;
  echo  '<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>'.NL;
  echo  '<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>'.NL;
  echo'</table>'.NL;
}

// Assemblage du tableau des matières par personnel
$TH = array();
$TB = array();
$TF = array();
foreach($tab_lignes_profs as $ligne_id => $tab_user)
{
  $TH[$ligne_id] = '';
  $TB[$ligne_id] = '';
  $TF[$ligne_id] = '';
  foreach($tab_user as $user_id)
  {
    $nb = mb_substr_count($tab_matieres_par_prof[$user_id],'</div>','UTF-8');
    $s = ($nb>1) ? 's' : '' ;
    $TH[$ligne_id] .= '<th id="pmh_'.$user_id.'">'.$tab_profs[$user_id].'</th>';
    $TB[$ligne_id] .= '<td id="pmb_'.$user_id.'">'.$tab_matieres_par_prof[$user_id].'</td>';
    $TF[$ligne_id] .= '<td id="pmf_'.$user_id.'">'.$nb.' matière'.$s.'</td>';
  }
}

// Affichage du tableau des matières par personnel
echo'<hr /><h2>Bilan des matières par personnel</h2>'.NL;
echo'<div class="astuce">Cocher les personnels coordonnateurs.</div>'.NL;
foreach($tab_lignes_profs as $ligne_id => $tab_user)
{
  echo'<table class="affectation">'.NL;
  echo  '<thead><tr>'.$TH[$ligne_id].'</tr></thead>'.NL;
  echo  '<tbody><tr>'.$TB[$ligne_id].'</tr></tbody>'.NL;
  echo  '<tfoot><tr>'.$TF[$ligne_id].'</tr></tfoot>'.NL;
  echo'</table>'.NL;
}

// Javascript
Layout::add( 'js_inline_before' , $tab_js);

?>