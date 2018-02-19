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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Enseignements Pratiques Interdisciplinaires"));

if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_LIVRET_EPI'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) en complément des personnels de direction :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_EPI'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Indication des profils autorisés
$puce_profils_autorises = ($_SESSION['USER_PROFIL_TYPE']!='professeur') ? '' : '<li><span class="astuce"><a title="administrateurs (de l\'établissement)<br />personnels de direction<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_LIVRET_EPI'],'br').'" href="#">Profils pouvant accéder à ce menu de configuration.</a></span></li>';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_epi">DOC : Administration du Livret Scolaire &rarr; Enseignements Pratiques Interdisciplinaires</a></span></li>
  <li><span class="astuce">Les <b>Enseignements Pratiques Interdisciplinaires</b> mis en place à compter de la rentrée 2017 concernent les <b>élèves du Collège</b> (seuls ceux de Cycle 4 étaient concernés à la rentrée 2016).</span></li>
  <li><span class="astuce">Ce menu ne sert que pour les <b>bilans périodiques</b> (sans objet pour les <b>bilans de fin de cycle</b>).</span></li>
  <?php echo $puce_profils_autorises ?>
</ul>

<hr />

<?php
$tab_objet = array(
 'theme'      => 'Thématiques',
 'dispositif' => 'Dispositifs',
);
// On récupère l'éventuel sous-menu transmis et on vérifie sa validité
$objet = isset($_GET['objet']) ? Clean::id($_GET['objet']) : '' ;
$objet = in_array($objet,array('theme','dispositif')) ? $objet : 'dispositif' ;

// On complète le Sous-Menu d'en-tête
$SOUS_MENU .= '<hr />';
foreach($tab_objet as $key => $txt)
{
  $class = ($key==$objet) ? ' class="actif"' : '' ;
  $SOUS_MENU .= '<a'.$class.' href="./index.php?page=livret&amp;section=epi&amp;objet='.$key.'">'.html($txt).'</a>'.NL;
}

if(!$objet)
{
  echo'<p>Choisir un sous-menu :</p>'.NL;
  echo'<ul class="puce">'.NL;
  foreach($tab_objet as $key => $txt)
  {
    echo'<li class="p"><a href="./index.php?page=livret&amp;section=epi&amp;objet='.$key.'">'.html($txt).'</a></li>'.NL;
  }
  echo'</ul>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// On complète le titre de la page
$TITRE .= ' &rarr; '.html($tab_objet[$objet]);

// On liste les thèmes des epis (besoin dans les 2 situations)
$test_used = ($objet=='theme') ? TRUE : FALSE ;
$tab_epi_themes = DB_STRUCTURE_LIVRET::DB_OPT_epitheme( $test_used );

// ////////////////////////////////////////////////////////////////////////////////////////////////
// Thématiques
// ////////////////////////////////////////////////////////////////////////////////////////////////

if($objet=='theme'):

?>

<p class="astuce">
  Les 8 thématiques nationales officielles sont intégrées dans <em>SACoche</em>.<br />
  Depuis la rentrée 2017, le ministère autorise les établissements à ajouter d'autres thématiques si besoin.<br />
  Ne pas confondre la thématique avec l'intitulé de l'EPI, qui demeure une saisie libre.
</p>

<table id="table_theme" class="form hsort">
  <thead>
    <tr>
      <th>Origine</th>
      <th>Code</th>
      <th>Intitulé</th>
      <th class="nu"><q class="ajouter" title="Ajouter une thématique d'établissement."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach($tab_epi_themes as $DB_ROW)
    {
      $origine = ($DB_ROW['optgroup']==1) ? 'Nationale' : 'Personnalisée' ;
      // Afficher une ligne du tableau
      echo'<tr id="id_'.$DB_ROW['valeur'].'" data-used="'.$DB_ROW['theme_used'].'">';
      echo  '<td>'.$origine.'</td>';
      echo  '<td>'.$DB_ROW['valeur'].'</td>';
      echo  '<td>'.html($DB_ROW['texte']).'</td>';
      echo  '<td class="nu">';
      if($origine=='Personnalisée')
      {
        echo    '<q class="modifier" title="Modifier cette thématique."></q>';
        echo    '<q class="supprimer" title="Supprimer cette thématique."></q>';
      }
      echo  '</td>';
      echo'</tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_theme" class="hide">
  <h2><span id="theme_titre_action">Ajouter | Modifier | Supprimer</span> une thématique</h2>
  <div id="theme_edit">
    <p>
      <label class="tab" for="f_national">Origine :</label><input id="f_national" name="f_national" type="text" size="15" maxlength="15" value="Personnalisée" readonly /><br />
      <label class="tab" for="f_code">Code :</label>EPI_<input id="f_code" name="f_code" type="text" value="" size="4" maxlength="3" /><br />
      <label class="tab" for="f_nom">Intitulé :</label><input id="f_nom" name="f_nom" type="text" value="" size="50" maxlength="50" />
    </p>
  </div>
  <div id="theme_delete">
    <p>Confirmez-vous la suppression de la thématique &laquo;&nbsp;<b id="gestion_delete_theme"></b>&nbsp;&raquo; ?</p>
  </div>
  <p id="alerte_theme_used" class="fluo"><input id="f_theme_usage" name="f_theme_usage" type="hidden" value="" /><span class="danger b">Cette thématique est associée à un ou plusieurs E.P.I.<br />Une thématique déjà utilisée ne devrait pas être modifiée, et encore moins supprimée.</span></p>
  <p>
    <span class="tab"></span><input name="f_objet" type="hidden" value="theme" /><input id="f_theme_action" name="f_action" type="hidden" value="" /><button id="bouton_theme_valider" type="button" class="valider">Valider.</button> <button id="bouton_theme_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_theme">&nbsp;</label>
  </p>
</form>

<?php
endif;

// ////////////////////////////////////////////////////////////////////////////////////////////////
// Dispositifs
// ////////////////////////////////////////////////////////////////////////////////////////////////

if($objet=='dispositif'):

$tab_classe = array();
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  // On récupère la liste des classes / groupes auxquelles le professeur est rattaché, et s'il en est coordonnateur
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  if(empty($DB_TAB))
  {
    echo'<ul class="puce">'.NL;
    echo  '<li><span class="danger">Aucune classe trouvée parmi celles qui vous sont rattachées !</span></li>'.NL;
    echo'</ul>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($DB_ROW['groupe_type']=='classe') && !isset($tab_classe[$DB_ROW['groupe_id']]) && Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_LIVRET_EPI'] , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ ) )
    {
      $tab_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_id'];
    }
  }
  if(empty($tab_classe))
  {
    echo'<ul class="puce">'.NL;
    echo  '<li><span class="danger">Aucune classe trouvée parmi celles que vous avez le droit de gérer !</span></li>'.NL;
    echo'</ul>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}

$page_ordre_longueur = 3;
$page_ordre_format   = '%0'.$page_ordre_longueur.'u';

// Formulaire select_page avec ordres associés, si au moins une classe est associée à la page
$select_page = '<option value="">&nbsp;</option>';
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages_for_dispositif( 'epi' );
if(empty($DB_TAB))
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'un administrateur ou directeur doit commencer' : 'commencez' ;
  echo'<p class="danger">Aucune classe n\'est associée à une page du livret concernée par ce dispositif !<br />Si besoin, '.$consigne.' par <a href="./index.php?page=livret&amp;section=classes">associer les classes au livret scolaire</a>.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
Layout::add( 'js_inline_before' , 'var tab_page_ordre = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_rubrique_join = new Array();' );
foreach($DB_TAB as $DB_ROW)
{
  $select_page .= '<option value="'.$DB_ROW['livret_page_ref'].'">'.html($DB_ROW['livret_page_moment']).'</option>';
  Layout::add( 'js_inline_before' , 'tab_page_ordre["'.html($DB_ROW['livret_page_moment']).'"]="'.sprintf($page_ordre_format,$DB_ROW['livret_page_ordre']).'";' );
  Layout::add( 'js_inline_before' , 'tab_rubrique_join["'.html($DB_ROW['livret_page_ref']).'"]="'.$DB_ROW['livret_page_rubrique_join'].'";' );
}

// Formulaire select_matiere si au moins une matière est associée à la page
$select_c3_matiere = '';
$select_c4_matiere = '';
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_matieres_alimentees();
if(empty($DB_TAB))
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'un administrateur ou directeur doit commencer' : 'commencez' ;
  echo'<p class="danger">Aucune matiere du livret n\'est associée aux référentiels !<br />Si besoin, '.$consigne.' par <a href="./index.php?page=livret&amp;section=liaisons">associer les référentiels au livret scolaire</a>.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
foreach($DB_TAB as $DB_ROW)
{
  ${'select_'.$DB_ROW['livret_rubrique_type']} .= '<option value="'.$DB_ROW['matiere_id'].'">'.html($DB_ROW['matiere_nom']).'</option>';
}
$select_c3_matiere = ($select_c3_matiere) ? '<option value="">&nbsp;</option>'.$select_c3_matiere : '<option value="" disabled>aucune matière du livret associée à ce niveau de classe !</option>' ;
$select_c4_matiere = ($select_c4_matiere) ? '<option value="">&nbsp;</option>'.$select_c4_matiere : '<option value="" disabled>aucune matière du livret associée à ce niveau de classe !</option>' ;
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var select_c3_matiere="'.str_replace('"','\"',$select_c3_matiere).'";' );
Layout::add( 'js_inline_before' , 'var select_c4_matiere="'.str_replace('"','\"',$select_c4_matiere).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );

// Liste des personnels de l'établissement ; select_prof en complément du tab_prof[] sinon le parcours du tableau suit l'ordre des ids et perd donc l'ordre alphabétique
$select_prof = '';
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl('all');
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun professeur n\'est enregistré dans l\'établissement !<br />Commencez par importer les utilisateurs.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
Layout::add( 'js_inline_before' , 'var tab_prof = new Array();' );
foreach($DB_TAB as $DB_ROW)
{
  Layout::add( 'js_inline_before' , 'tab_prof['.$DB_ROW['valeur'].']="'.html($DB_ROW['texte']).'";' );
  $select_prof .= '<option value="'.$DB_ROW['valeur'].'">'.html($DB_ROW['texte']).'</option>';
}
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var select_prof="'.str_replace('"','\"',$select_prof).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );

// Nettoyer si EPI associé à une matière qui n'est plus alimentée
$nb_delete = DB_STRUCTURE_LIVRET::DB_nettoyer_jointure_dispositif_matiere( 'epi' );
if($nb_delete)
{
  $s = ($nb_delete>1) ? 's' : '' ;
  echo'<p class="danger">'.$nb_delete.' association'.$s.' d\'enseignant'.$s.' supprimée'.$s.' car matière du livret désormais plus alimentée par les référentiels.</p>'.NL;
}
// Nettoyer si EPI associé à moins de 2 enseignants / matières
$nb_delete = DB_STRUCTURE_LIVRET::DB_nettoyer_dispositif_sans_prof( 'epi' );
if($nb_delete)
{
  $s = ($nb_delete>1) ? 's' : '' ;
  echo'<p class="danger">'.$nb_delete.' dispositif'.$s.' supprimé'.$s.' faute d\'enseignants / matières rattachés.</p>'.NL;
}

// Formulaires f_matiere_* et f_prof_*
$select_f_nombre = '';
$p_matiere_prof = '';
for( $nb=1 ; $nb<=15 ; $nb++)
{
  if($nb>1)
  {
    $select_f_nombre .= '<option value="'.$nb.'">'.$nb.'</option>';
  }
  $p_matiere_prof .= '<p id="join_'.$nb.'" class="hide">';
  $p_matiere_prof .=   '<label class="tab" for="f_matiere_'.$nb.'">Matière '.$nb.' :</label><select id="f_matiere_'.$nb.'" name="f_matiere_'.$nb.'"><option></option></select><br />';
  $p_matiere_prof .=   '<label class="tab" for="f_prof_'.$nb.'">Professeur '.$nb.' :</label><select id="f_prof_'.$nb.'" name="f_prof_'.$nb.'"><option></option></select>';
  $p_matiere_prof .= '</p>'."\r\n";
}

$select_theme = HtmlForm::afficher_select($tab_epi_themes , FALSE /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'epi_theme' /*optgroup*/ , FALSE /*multiple*/ );

?>

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Moment</th>
      <th>Classe</th>
      <th>Thème</th>
      <th>Matière / Professeur</th>
      <th>Titre</th>
      <th class="nu"><q class="ajouter" title="Ajouter un enseignement pratique interdisciplinaire."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $listing_classe_id = implode(',',$tab_classe);
    Layout::add( 'js_inline_before' , 'var only_groupes_id="'.$listing_classe_id.'";' );
    // Lister les enseignements pratiques interdisciplinaires
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi($listing_classe_id);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $nb_mat_prof = substr_count( $DB_ROW['matiere_prof_texte'] , '§BR§' ) + 1 ;
        $epi_used = $DB_ROW['epi_used'] / ( 3 * $nb_mat_prof );
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['livret_epi_id'].'" data-used="'.$epi_used.'">';
        echo  '<td data-id="'.$DB_ROW['livret_page_ref'].'"><i>'.sprintf($page_ordre_format,$DB_ROW['livret_page_ordre']).'</i>'.html($DB_ROW['livret_page_moment']).'</td>';
        echo  '<td data-id="'.$DB_ROW['groupe_id'].'">'.html($DB_ROW['groupe_nom']).'</td>';
        echo  '<td data-id="'.$DB_ROW['livret_epi_theme_code'].'">'.html($DB_ROW['livret_epi_theme_nom']).'</td>';
        echo  '<td data-id="'.$DB_ROW['matiere_prof_id'].'">'.str_replace('§BR§','<br />',html($DB_ROW['matiere_prof_texte'])).'</td>';
        echo  '<td>'.html($DB_ROW['livret_epi_titre']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cet E.P.I."></q>';
        echo    '<q class="dupliquer" title="Dupliquer cet E.P.I."></q>';
        echo    '<q class="supprimer" title="Supprimer cet E.P.I."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="5">Cliquer sur l\'icône ci-dessus (symbole "+" dans un rond vert) pour ajouter un enseignement pratique interdisciplinaire.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2><span id="gestion_titre_action">Ajouter | Modifier | Dupliquer | Supprimer</span> un enseignement pratique interdisciplinaire</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_page">Moment :</label><select id="f_page" name="f_page"><?php echo $select_page ?></select><br />
      <label class="tab" for="f_groupe">Classe :</label><select id="f_groupe" name="f_groupe"><option></option></select><br />
      <label class="tab" for="f_theme">Thème :</label><select id="f_theme" name="f_theme"><?php echo $select_theme ?></select><br />
      <label class="tab" for="f_titre">Titre :</label><input id="f_titre" name="f_titre" type="text" value="" size="50" maxlength="125" /><br />
      <label class="tab" for="f_nombre">Nombre disciplines :</label><select id="f_nombre" name="f_nombre"><?php echo $select_f_nombre ?></select>
    </p>
    <?php echo $p_matiere_prof ?>
    <p class="astuce">Le projet réalisé est renseigné ultérieurement via le commentaire sur la classe.</p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression de l'E.P.I. &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p id="alerte_used" class="fluo"><input id="f_usage" name="f_usage" type="hidden" value="" /><span class="danger b">Ce dispositif comporte des saisies sur le Livret scolaire.<br />Un E.P.I. déjà utilisé ne devrait pas être modifié, et encore moins supprimé.</span></p>
  <p>
    <span class="tab"></span><input name="f_objet" type="hidden" value="epi" /><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<?php
endif;
?>