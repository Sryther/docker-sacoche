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

$action        = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '';
$selection_id  = (isset($_POST['f_id']))      ? Clean::entier($_POST['f_id'])     : 0;
$selection_nom = (isset($_POST['f_nom']))     ? Clean::texte($_POST['f_nom'])     : '';
$origine       = (isset($_POST['f_origine'])) ? Clean::texte($_POST['f_origine']) : '';

// Contrôler la liste des items transmis
$tab_items = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_items = Clean::map('entier',$tab_items);
$tab_items = array_filter($tab_items,'positif');
$nb_items = count($tab_items);

// Contrôler la liste des profs transmis
$tab_profs   = array();
$tab_droits  = array( 'v'=>'voir' , 'm'=>'modifier' );
$profs_liste = (isset($_POST['f_prof_liste'])) ? $_POST['f_prof_liste'] : '' ;
$tmp_tab     = ($profs_liste) ? explode('_',$profs_liste) : array() ;
foreach($tmp_tab as $valeur)
{
  $droit   = $valeur{0};
  $id_prof = (int)substr($valeur,1);
  if( isset($tab_droits[$droit]) && ($id_prof>0) && ( ($action!='dupliquer') || ($id_prof!=$_SESSION['USER_ID']) ) )
  {
    $tab_profs[$id_prof] = $tab_droits[$droit];
  }
  else
  {
    $profs_liste = str_replace( array( '_'.$valeur , $valeur.'_' , $valeur ) , '' , $profs_liste );
  }
}
$nb_profs   = count($tab_profs);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle sélection d'items
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='ajouter')||(($action=='dupliquer')&&($selection_id))) && $selection_nom && $nb_items && $origine )
{
  // Vérifier que le nom de la sélection d'items est disponible (parmi tous ceux des personnels de l'établissement, à cause du partage)
  if( $proprio = DB_STRUCTURE_SELECTION_ITEM::DB_tester_nom( $selection_nom ) )
  {
    Json::end( FALSE , 'Nom déjà utilisé (par '.html($proprio).') !' );
  }
  // Insérer l'enregistrement ; y associe automatiquement le prof, en propriétaire de la sélection
  $selection_id2 = DB_STRUCTURE_SELECTION_ITEM::DB_ajouter( $_SESSION['USER_ID'] , $selection_nom );
  // Affecter tous les items choisis
  $tab_retour = DB_STRUCTURE_SELECTION_ITEM::DB_modifier_liaison_item( $selection_id2 , $tab_items , 'creer' );
  if($nb_profs)
  {
    // Affecter tous les profs choisis
    $tab_retour = DB_STRUCTURE_SELECTION_ITEM::DB_modifier_liaison_prof( $selection_id2 , $tab_profs , 'creer' );
  }
  // Afficher le retour
  if($origine==$PAGE)
  {
    $items_texte  = ($nb_items>1) ? $nb_items.' items' : '1 item' ;
    $profs_nombre = ($nb_profs) ? ($nb_profs+1).' collègues' : 'non' ;
    $profs_bulle  = ($nb_profs && ($nb_profs<10)) ? ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" class="bulle_profs" />' : '' ;
    Json::add_row( 'html' , '<tr id="id_'.$selection_id2.'" class="new">' );
    Json::add_row( 'html' ,   '<td>'.html($selection_nom).'</td>' );
    Json::add_row( 'html' ,   '<td>'.$items_texte.'</td>' );
    Json::add_row( 'html' ,   '<td id="proprio_'.$_SESSION['USER_ID'].'">'.$profs_nombre.$profs_bulle.'</td>' );
    Json::add_row( 'html' ,   '<td class="nu">' );
    Json::add_row( 'html' ,     '<q class="modifier" title="Modifier cette sélection d\'items."></q>' );
    Json::add_row( 'html' ,     '<q class="dupliquer" title="Dupliquer cette sélection d\'items."></q>' );
    Json::add_row( 'html' ,     '<q class="supprimer" title="Supprimer cette sélection d\'items."></q>' );
    Json::add_row( 'html' ,   '</td>' );
    Json::add_row( 'html' , '</tr>' );
    Json::add_row( 'script' , 'tab_items["'.$selection_id2.'"]="'.implode('_',$tab_items).'";' );
    Json::add_row( 'script' , 'tab_profs["'.$selection_id2.'"]="'.$profs_liste.'";' );
    Json::end( TRUE );
  }
  else
  {
    Json::end( TRUE , '<option value="'.implode('_',$tab_items).'">'.html($selection_nom).'</option>' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une sélection d'items existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $selection_id && $selection_nom && $nb_items )
{
  // Vérifier que le nom de la sélection d'items est disponible (parmi tous ceux des personnels de l'établissement, à cause du partage)
  if( $proprio = DB_STRUCTURE_SELECTION_ITEM::DB_tester_nom( $selection_nom , $selection_id ) )
  {
    Json::end( FALSE , 'Nom déjà utilisé (par '.html($proprio).') !' );
  }
  // Tester les droits
  $proprio_id = DB_STRUCTURE_SELECTION_ITEM::DB_recuperer_prorietaire_id( $selection_id );
  if($proprio_id==$_SESSION['USER_ID'])
  {
    $niveau_droit = 4; // propriétaire
    $proprietaire_genre  = $_SESSION['USER_GENRE'];
    $proprietaire_nom    = $_SESSION['USER_NOM'];
    $proprietaire_prenom = $_SESSION['USER_PRENOM'];
  }
  elseif($profs_liste) // forcément
  {
    $search_liste = '_'.$profs_liste.'_';
    if( strpos( $search_liste, '_m'.$_SESSION['USER_ID'].'_' ) !== FALSE )
    {
      $niveau_droit = 3; // modifier
    }
    elseif( strpos( $search_liste, '_v'.$_SESSION['USER_ID'].'_' ) !== FALSE )
    {
      Json::end( FALSE , 'Droit insuffisant attribué sur la sélection n°'.$selection_id.' (niveau 1 au lieu de 3) !' ); // voir
    }
    else
    {
      Json::end( FALSE , 'Droit attribué sur la sélection n°'.$selection_id.' non trouvé !' );
    }
    $DB_ROW = DB_STRUCTURE_SELECTION_ITEM::DB_recuperer_prorietaire_identite( $selection_id );
    $proprietaire_genre  = $DB_ROW['user_genre'];
    $proprietaire_nom    = $DB_ROW['user_nom'];
    $proprietaire_prenom = $DB_ROW['user_prenom'];
  }
  else
  {
    Json::end( FALSE , 'Vous n\'êtes ni propriétaire ni bénéficiaire de droits sur la sélection n°'.$selection_id.' !' );
  }
  $proprietaire_identite = $proprietaire_nom.' '.$proprietaire_prenom;
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_SELECTION_ITEM::DB_modifier( $selection_id , $selection_nom , $tab_items );
  // Mofifier les affectations des items choisis
  $tab_retour = DB_STRUCTURE_SELECTION_ITEM::DB_modifier_liaison_item( $selection_id , $tab_items , 'substituer' );
  // sacoche_jointure_selection_prof ; à restreindre en cas de modification d'une sélection dont on n'est pas le propriétaire
  if($proprio_id==$_SESSION['USER_ID'])
  {
    if($nb_profs)
    {
      // Mofifier les affectations des profs choisis
      $tab_retour = DB_STRUCTURE_SELECTION_ITEM::DB_modifier_liaison_prof( $selection_id , $tab_profs , 'substituer' );
    }
    else
    {
      // Au cas où on aurait retiré les droits à tous
      DB_STRUCTURE_SELECTION_ITEM::DB_supprimer_liaison_prof( $selection_id );
    }
  }
  // Afficher le retour
  $items_texte  = ($nb_items>1) ? $nb_items.' items' : '1 item' ;
  $profs_nombre = ($nb_profs) ? ($nb_profs+1).' collègues' : 'non' ;
  $profs_bulle  = ($nb_profs && ($nb_profs<10)) ? ' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" class="bulle_profs" />' : '' ;
  $q_modifier   = ($niveau_droit>=3) ? '<q class="modifier" title="Modifier cette sélection d\'items."></q>' : '<q class="modifier_non" title="Action nécessitant le droit de modification (voir '.html($proprietaire_identite).')."></q>' ;
  $q_supprimer  = ($niveau_droit==4) ? '<q class="supprimer" title="Supprimer cette sélection d\'items."></q>' : '<q class="supprimer_non" title="Suppression restreinte au propriétaire de la sélection ('.html($proprietaire_identite).')."></q>' ;
  Json::add_row( 'html' , '<td>'.html($selection_nom).'</td>' );
  Json::add_row( 'html' , '<td>'.$items_texte.'</td>' );
  Json::add_row( 'html' , '<td id="proprio_'.$proprio_id.'">'.$profs_nombre.$profs_bulle.'</td>' );
  Json::add_row( 'html' , '<td class="nu">' );
  Json::add_row( 'html' ,   $q_modifier );
  Json::add_row( 'html' ,   '<q class="dupliquer" title="Dupliquer cette sélection d\'items."></q>' );
  Json::add_row( 'html' ,   $q_supprimer );
  Json::add_row( 'html' , '</td>' );
  Json::add_row( 'script' , 'tab_items["'.$selection_id.'"]="'.implode('_',$tab_items).'";' );
  Json::add_row( 'script' , 'tab_profs["'.$selection_id.'"]="'.$profs_liste.'";' );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une sélection d'items existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $selection_id )
{
  // Vérification des droits
  $proprio_id = DB_STRUCTURE_SELECTION_ITEM::DB_recuperer_prorietaire_id( $selection_id );
  if($proprio_id!=$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Vous n\'êtes pas propriétaire de la sélection n°'.$selection_id.' !' );
  }
  // Effacer l'enregistrement
  DB_STRUCTURE_SELECTION_ITEM::DB_supprimer( $selection_id );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
