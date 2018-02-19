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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$objet      = (isset($_POST['f_objet']))       ? Clean::texte( $_POST['f_objet'])       : '';
$action     = (isset($_POST['f_action']))      ? Clean::texte( $_POST['f_action'])      : '';
$theme_code = (isset($_POST['f_code']))        ? 'EPI_'.Clean::ref($_POST['f_code'])    : '';
$theme_nom  = (isset($_POST['f_nom']))         ? Clean::texte( $_POST['f_nom'])         : '';
$theme_used = (isset($_POST['f_theme_usage'])) ? Clean::entier($_POST['f_theme_usage']) : 0;
$epi_id     = (isset($_POST['f_id']))          ? Clean::entier($_POST['f_id'])          : 0;
$epi_used   = (isset($_POST['f_usage']))       ? Clean::entier($_POST['f_usage'])       : 0;
$page_ref   = (isset($_POST['f_page']))        ? Clean::id(    $_POST['f_page'])        : '';
$groupe_id  = (isset($_POST['f_groupe']))      ? Clean::entier($_POST['f_groupe'])      : 0;
$theme      = (isset($_POST['f_theme']))       ? Clean::ref(   $_POST['f_theme'])       : '';
$titre      = (isset($_POST['f_titre']))       ? Clean::texte( $_POST['f_titre'])       : '';
$nombre     = (isset($_POST['f_nombre']))      ? Clean::entier($_POST['f_nombre'])      : 0;

if($objet=='epi')
{
  $test_matiere_prof = TRUE;
  $tab_matiere_prof  = array();
  $tab_matiere       = array();
  for( $num=1 ; $num<=$nombre ; $num++ )
  {
    ${'matiere_id_'.$num} = (isset($_POST['f_matiere_'.$num])) ? Clean::entier($_POST['f_matiere_'.$num]) : 0;
    ${'prof_id_'.$num}    = (isset($_POST['f_prof_'.$num]))    ? Clean::entier($_POST['f_prof_'.$num])    : 0;
    $test_matiere_prof = $test_matiere_prof && ${'matiere_id_'.$num} && ${'prof_id_'.$num} ;
    $tab_matiere_prof[] = ${'matiere_id_'.$num}.'~'.${'prof_id_'.$num};
    $tab_matiere[${'matiere_id_'.$num}] = TRUE;
  }
}

$test_theme = ( ($objet=='theme') && $theme_code && $theme_nom ) ? TRUE : FALSE ;
$test_epi   = ( ($objet=='epi')   && $page_ref && $titre && DB_STRUCTURE_LIVRET::DB_tester_epi_theme( $theme ) && DB_STRUCTURE_LIVRET::DB_tester_page_avec_dispositif( $page_ref , 'epi' ) ) ? TRUE : FALSE ;
if( !$test_theme && !$test_epi )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle thématique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_theme && ($action=='ajouter') )
{
  // Vérifier que le code est disponible
  if( DB_STRUCTURE_LIVRET::DB_tester_epi_theme( $theme_code ) )
  {
    Json::end( FALSE , 'Code de thématique déjà utilisé !' );
  }
  // Insérer l'enregistrement
  DB_STRUCTURE_LIVRET::DB_ajouter_epi_theme( $theme_code , $theme_nom );
  // Afficher le retour
  Json::add_str('<tr id="id_'.$theme_code.'" data-used="0" class="new">');
  Json::add_str(  '<td>Personnalisée</td>');
  Json::add_str(  '<td>'.$theme_code.'</td>');
  Json::add_str(  '<td>'.html($theme_nom).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cette thématique."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cette thématique."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouvel enseignement pratique interdisciplinaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_epi && in_array($action,array('ajouter','dupliquer')) && $groupe_id && $test_matiere_prof && ($nombre>=2) && ($nombre<=15) )
{
  if( count(array_unique($tab_matiere_prof)) != $nombre )
  {
    Json::end( FALSE , 'Couples { matière / enseignant } identiques !' );
  }
  if( count($tab_matiere) < 2 )
  {
    Json::end( FALSE , 'Il faut au moins 2 matières distinctes !' );
  }
  // Vérifier que l'enseignement pratique interdisciplinaire est disponible
  // Clef unique UNIQUE KEY livret_epi (livret_epi_theme_code, livret_page_ref, groupe_id) retirée : on tolère plusieurs EPI avec la même thématique pour un élève.
  /*
  if( DB_STRUCTURE_LIVRET::DB_tester_epi( $theme , $page_ref , $groupe_id ) )
  {
    Json::end( FALSE , 'E.P.I. classe / thème déjà existant !' );
  }
  */
  // Insérer l'enregistrement
  $epi_id = DB_STRUCTURE_LIVRET::DB_ajouter_epi( $theme , $page_ref , $groupe_id , $titre );
  $tab_matiere_prof_id = array();
  for( $i=1 ; $i<=$nombre ; $i++ )
  {
    $tab_matiere_prof_id[$i] = ${'matiere_id_'.$i}.'_'.${'prof_id_'.$i};
    DB_STRUCTURE_LIVRET::DB_ajouter_epi_jointure( $epi_id , ${'matiere_id_'.$i} , ${'prof_id_'.$i} );
  }
  // Afficher le retour
  Json::add_str('<tr id="id_'.$epi_id.'" data-used="0" class="new">');
  Json::add_str(  '<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str(  '<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str(  '<td data-id="'.$theme.'">{{THEME_NOM}}</td>');
  Json::add_str(  '<td data-id="'.implode(' ',$tab_matiere_prof_id).'">{{MATIERE_PROF_NOM}}</td>');
  Json::add_str(  '<td>'.html($titre).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cet E.P.I."></q>');
  Json::add_str(    '<q class="dupliquer" title="Dupliquer cet E.P.I."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cet E.P.I."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une thématique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_theme && ($action=='modifier') )
{
  // Vérifier que le code est celui d'une thématique personnalisée
  if( DB_STRUCTURE_LIVRET::DB_tester_epi_theme( $theme_code ) != 2 )
  {
    Json::end( FALSE , 'Code de thématique national ou inconnu !' );
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_LIVRET::DB_modifier_epi_theme( $theme_code , $theme_nom );
  // Afficher le retour
  Json::add_str('<td>Personnalisée</td>');
  Json::add_str('<td>'.$theme_code.'</td>');
  Json::add_str('<td>'.html($theme_nom).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cette thématique."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer cette thématique."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un enseignement pratique interdisciplinaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_epi && ($action=='modifier') && $epi_id && $groupe_id && $test_matiere_prof && ($nombre>=2) && ($nombre<=15) )
{
  if( count(array_unique($tab_matiere_prof)) != $nombre )
  {
    Json::end( FALSE , 'Couples { matière / enseignant } identiques !' );
  }
  if( count($tab_matiere) < 2 )
  {
    Json::end( FALSE , 'Il faut au moins 2 matières distinctes !' );
  }
  // Vérifier que l'enseignement pratique interdisciplinaire est disponible
  // Clef unique UNIQUE KEY livret_epi (livret_epi_theme_code, livret_page_ref, groupe_id) retirée : on tolère plusieurs EPI avec la même thématique pour un élève.
  /*
  if( DB_STRUCTURE_LIVRET::DB_tester_epi( $theme , $page_ref , $groupe_id , $epi_id ) )
  {
    Json::end( FALSE , 'E.P.I. classe / thème déjà existant !' );
  }
  */
  // Mettre à jour l'enregistrement
  // Remarque : il est possible qu'il n'y ait aucun changement, on ne s'en préoccupe pas.
  // Remarque : on ne fait pas dans la dentelle pour les jointures : on les supprime et on les crée de nouveau.
  DB_STRUCTURE_LIVRET::DB_modifier_epi( $epi_id , $theme , $page_ref , $groupe_id , $titre );
  DB_STRUCTURE_LIVRET::DB_supprimer_epi_jointure( $epi_id );
  for( $i=1 ; $i<=$nombre ; $i++ )
  {
    $tab_matiere_prof_id[$i] = ${'matiere_id_'.$i}.'_'.${'prof_id_'.$i};
    DB_STRUCTURE_LIVRET::DB_ajouter_epi_jointure( $epi_id , ${'matiere_id_'.$i} , ${'prof_id_'.$i} );
  }
  // Afficher le retour
  Json::add_str('<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str('<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str('<td data-id="'.$theme.'">{{THEME_NOM}}</td>');
  Json::add_str('<td data-id="'.implode(' ',$tab_matiere_prof_id).'">{{MATIERE_PROF_NOM}}</td>');
  Json::add_str('<td>'.html($titre).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cet E.P.I."></q>');
  Json::add_str(  '<q class="dupliquer" title="Dupliquer cet E.P.I."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer cet E.P.I."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une thématique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_theme && ($action=='supprimer') )
{
  // Vérifier que le code est celui d'une thématique personnalisée
  if( DB_STRUCTURE_LIVRET::DB_tester_epi_theme( $theme_code ) != 2 )
  {
    Json::end( FALSE , 'Code de thématique national ou inconnu !' );
  }
  // Effacer l'enregistrement
  DB_STRUCTURE_LIVRET::DB_supprimer_epi_theme( $theme_code , $theme_used );
  // Log d'une action sensible
  if($theme_used)
  {
    // Log de l'action
    SACocheLog::ajouter('Suppression d\'une thématique d\'E.P.I. utilisée ['.$theme_code.'] ['.$theme_nom.'].');
    // Notifications (rendues visibles ultérieurement)
    $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé une thématique d\'E.P.I. utilisée ['.$theme_code.'] ['.$theme_nom.'], et donc aussi les saisies associées.'."\r\n";
    DB_STRUCTURE_NOTIFICATION::enregistrer_action_sensible($notification_contenu);
  }
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un enseignement pratique interdisciplinaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $test_epi && ($action=='supprimer') && $epi_id )
{
  // Effacer l'enregistrement
  DB_STRUCTURE_LIVRET::DB_supprimer_epi( $epi_id );
  // Log d'une action sensible
  if($epi_used)
  {
    // Log de l'action
    SACocheLog::ajouter('Suppression d\'un E.P.I. utilisé ['.$page_ref.'] ['.$titre.'].');
    // Notifications (rendues visibles ultérieurement)
    $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé un E.P.I. utilisé ['.$page_ref.'] ['.$titre.'], et donc aussi les saisies associées.'."\r\n";
    DB_STRUCTURE_NOTIFICATION::enregistrer_action_sensible($notification_contenu);
  }
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
