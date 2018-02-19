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
$TITRE = html(Lang::_("Professeurs & classes / Professeurs principaux"));
?>

<?php
// Fabrication des éléments select du formulaire
$select_prof   = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl('config')       , 'f_prof'   /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ );
$select_classe = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/) , 'f_classe' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_classes">DOC : Gestion des classes</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Professeurs :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="f_prof" class="select_multiple"><?php echo $select_prof ?></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Classes :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></span><br />
      <span id="f_classe" class="select_multiple"><?php echo $select_classe; ?></span>
    </td>
    <td class="nu" style="width:25em">
      <p><button id="ajouter" type="button" class="groupe_ajouter">Ajouter ces associations.</button></p>
      <p><button id="retirer" type="button" class="groupe_retirer">Retirer ces associations.</button></p>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<?php
// Deux requêtes préliminaires pour ne pas manquer les classes sans professeurs et les professeurs sans classes
$tab_js               = 'var tab_join = new Array();'; // [i_classe][i_prof] => 1 | 2
$tab_classes          = array();
$tab_profs            = array();
$tab_profs_par_classe = array();
$tab_classes_par_prof = array();
$tab_lignes_classes   = array();
$tab_lignes_profs     = array();

// Récupérer la liste des classes
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_avec_niveaux();
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucune classe trouvée !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_js .= 'tab_join['.$DB_ROW['groupe_id'].'] = new Array();';
  $tab_classes[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
  $tab_profs_par_classe[$DB_ROW['groupe_id']] = '';
  $tab_lignes_classes[$DB_ROW['niveau_id']][] = $DB_ROW['groupe_id'];
}

// Récupérer la liste des professeurs
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'professeur' , 1 /*only_actuels*/ , 'user_id,user_nom,user_prenom,user_profil_join_groupes' /*liste_champs*/ , FALSE /*with_classe*/ );
if(!empty($DB_TAB))
{
  $compteur = 0 ;
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['user_profil_join_groupes']=='config')
    {
      $tab_profs[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
      $tab_classes_par_prof[$DB_ROW['user_id']] = '';
      $tab_lignes_profs[floor($compteur/8)][] = $DB_ROW['user_id'];
      $compteur++;
    }
  }
}
if(!count($tab_profs))
{
  echo'<p class="danger">Aucun compte professeur trouvé !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Récupérer la liste des jointures
$liste_profs_id   = implode(',',array_keys($tab_profs));
$liste_classes_id = implode(',',array_keys($tab_classes));
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_jointure_professeurs_groupes($liste_profs_id,$liste_classes_id);
foreach($DB_TAB as $DB_ROW)
{
  $checked = ($DB_ROW['jointure_pp']) ? ' checked' : '' ;
  $classe  = ($DB_ROW['jointure_pp']) ? 'on' : 'off' ;
  $js_val  = ($DB_ROW['jointure_pp']) ? 2 : 1 ;
  $tab_js .= 'tab_join['.$DB_ROW['groupe_id'].']['.$DB_ROW['user_id'].']='.$js_val.';';
  $tab_profs_par_classe[$DB_ROW['groupe_id']] .= '<div id="cp_'.$DB_ROW['groupe_id'].'_'.$DB_ROW['user_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['groupe_id'].'cp'.$DB_ROW['user_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['groupe_id'].'cp'.$DB_ROW['user_id'].'">'.$tab_profs[$DB_ROW['user_id']].'</label></div>';
  $tab_classes_par_prof[$DB_ROW['user_id']]   .= '<div id="pc_'.$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['user_id'].'pc'.$DB_ROW['groupe_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['user_id'].'pc'.$DB_ROW['groupe_id'].'">'.$tab_classes[$DB_ROW['groupe_id']].'</label></div>';
}

// Assemblage du tableau des profs par classe
$TH = array();
$TB = array();
$TF = array();
foreach($tab_lignes_classes as $niveau_id => $tab_classe)
{
  $TH[$niveau_id] = '';
  $TB[$niveau_id] = '';
  $TF[$niveau_id] = '';
  foreach($tab_classe as $classe_id)
  {
    $nb = mb_substr_count($tab_profs_par_classe[$classe_id],'</div>','UTF-8');
    $s = ($nb>1) ? 's' : '' ;
    $TH[$niveau_id] .= '<th id="cph_'.$classe_id.'">'.$tab_classes[$classe_id].'</th>';
    $TB[$niveau_id] .= '<td id="cpb_'.$classe_id.'">'.$tab_profs_par_classe[$classe_id].'</td>';
    $TF[$niveau_id] .= '<td id="cpf_'.$classe_id.'">'.$nb.' professeur'.$s.'</td>';
  }
}

// Affichage du tableau des profs par classe
echo'<hr /><h2>Bilan des professeurs par classe</h2>'.NL;
echo'<div class="astuce">Cocher les professeurs principaux.</div>'.NL;
foreach($tab_lignes_classes as $niveau_id => $tab_classe)
{
  echo'<table class="affectation">'.NL;
  echo  '<thead><tr>'.$TH[$niveau_id].'</tr></thead>'.NL;
  echo  '<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>'.NL;
  echo  '<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>'.NL;
  echo'</table>'.NL;
}

// Assemblage du tableau des classes par prof
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
    $nb = mb_substr_count($tab_classes_par_prof[$user_id],'</div>','UTF-8');
    $s = ($nb>1) ? 's' : '' ;
    $TH[$ligne_id] .= '<th id="pch_'.$user_id.'">'.$tab_profs[$user_id].'</th>';
    $TB[$ligne_id] .= '<td id="pcb_'.$user_id.'">'.$tab_classes_par_prof[$user_id].'</td>';
    $TF[$ligne_id] .= '<td id="pcf_'.$user_id.'">'.$nb.' classe'.$s.'</td>';
  }
}

// Affichage du tableau des classes par prof
echo'<hr /><h2>Bilan des classes par professeur</h2>'.NL;
echo'<div class="astuce">Cocher les professeurs principaux.</div>'.NL;
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