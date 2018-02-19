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

// Réception d'id transmis via un lien de [Évaluer un élève à la volée].
$auto_voir_devoir_id   = isset($_GET['devoir_id'])   ? Clean::entier($_GET['devoir_id'])    : 'false' ;
$auto_voir_groupe_type = isset($_GET['groupe_type']) ? Clean::lettres($_GET['groupe_type']) : 'E' ;
$auto_voir_groupe_id   = isset($_GET['groupe_id'])   ? Clean::entier($_GET['groupe_id'])    : 'false' ;

// Réception d'un formulaire depuis un tableau de synthèse bilan
// Dans ce cas il s'agit d'une évaluation sur une sélection d'élèves.
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF

$tab_reqs = ( isset($_POST['id_req']) && is_array($_POST['id_req']) ) ? $_POST['id_req'] : array() ;
if(!empty($tab_reqs))
{
  // réception de user x item
  $tab_users = array() ;
  $tab_items = array() ;
  $_SESSION['TMP']['req_user_item'] = array();
  foreach($tab_reqs as $req)
  {
    list($user,$item) = explode('x',$req);
    $user = Clean::entier($user);
    $item = Clean::entier($item);
    if( $user && $item )
    {
      $tab_users[$user] = $user;
      $tab_items[$item] = $item;
      $_SESSION['TMP']['req_user_item'][] = $user.'x'.$item;
    }
  }
}
else
{
  unset($_SESSION['TMP']['req_user_item']);
  // réception d'élèves
  $tab_users = ( isset($_POST['id_user']) && is_array($_POST['id_user']) ) ? $_POST['id_user'] : array() ;
  $tab_users = Clean::map('entier',$tab_users);
  $tab_users = array_filter($tab_users,'positif');
  // réception d'items
  $tab_items = ( isset($_POST['id_item']) && is_array($_POST['id_item']) ) ? $_POST['id_item'] : array() ;
  $tab_items = Clean::map('entier',$tab_items);
  $tab_items = array_filter($tab_items,'positif');
}
$nb_users  = count($tab_users);
$txt_users = ($nb_users) ? ( ($nb_users>1) ? $nb_users.' élèves' : $nb_users.' élève' ) : 'aucun' ;
$nb_items  = count($tab_items);
$txt_items = ($nb_items) ? ( ($nb_items>1) ? $nb_items.' items' : $nb_items.' item' ) : 'aucun' ;
$reception_todo = ($nb_items || $nb_users) ? 'true' : 'false' ;

// $TYPE vaut "groupe" ou "selection"
$TYPE = ($nb_items || $nb_users)                    ? 'selection' : $SECTION ;
$TYPE = in_array($TYPE,array('groupe','selection')) ? $TYPE       : 'groupe' ;

$TITRE = ($TYPE=='groupe') ? html(Lang::_("Évaluer une classe ou un groupe")) : html(Lang::_("Évaluer des élèves sélectionnés")) ;

// Dates par défaut
$date_autoeval = date('d/m/Y',mktime(0,0,0,date('m'),date('d')+7,date('Y'))); // 1 semaine après

// Images pour une saisie à la souris : 8 codes + 2~6 notes.
/*
nn ab n1 n5
ne di n2 n6
nf re n3
nr xx n4
*/
$tab_complement = (count($_SESSION['NOTE_ACTIF'])!=4) ? array_fill( 0 , ( 8 - count($_SESSION['NOTE_ACTIF']) ) % 4 , '' ) : array() ; // Test inutile à compter de PHP 5.6.0 (mais avant le paramètre du nombre d'éléments ne pouvait valoir 0.
$tab_notes = array_merge( array( 'NN' , 'NE' , 'NF' , 'NR' , 'AB' , 'DI' , 'PA' , 'X' ) , $_SESSION['NOTE_ACTIF'] , $tab_complement );
$tab_souris_ligne = array_fill( 0 , 4 , '' );
foreach($tab_notes as $key => $note)
{
  $ligne = $key % 4;
  $tab_souris_ligne[$ligne] .= ($note) ? '<img alt="'.$note.'" src="'.Html::note_src($note).'" />' : '<img alt="" src="'.Html::note_src('X').'" style="visibility:hidden" />' ;
}
$souris_ligne = implode('<br />',$tab_souris_ligne);

// Images pour une saisie tactile : 4 flèches + 8 codes + 2~6 notes + ok / del
/*
f1 f2 f3 f4
nn ne nf nr
ab di re xx
  puis
n1 n2 del ok
  ou
n1 n2 n3 del
         ok
  ou
n1 n2 n3 n4
  del ok
  ou
n1 n2 n3 n4
n5 del ok
  ou
n1 n2 n3 n4
n5 n6 del ok
*/
$tab_ascii = array(
  'fleche' => array(
    'g' => array(  37 , 'Gauche' ),
    'd' => array(  39 , 'Droite' ),
    'h' => array(  38 , 'Haut' ),
    'b' => array(  40 , 'Bas' ),
  ),
  'note_speciale' => array(
    'NN' => array( 78 ), // N
    'NE' => array( 69 ), // E
    'NF' => array( 70 ), // F
    'NR' => array( 82 ), // R
    'AB' => array( 65 ), // A
    'DI' => array( 68 ), // D
    'PA' => array( 80 ), // P
    'X'  => array( 46 , 8 ), // suppr backspace
  ),
  'note_usuelle' => array(
    0 => array(  96 , 48 ), // 0 à
    1 => array(  97 , 49 ), // 1 &
    2 => array(  98 , 50 ), // 2 é
    3 => array(  99 , 51 ), // 3 "
    4 => array( 100 , 52 ), // 4 '
    5 => array( 101 , 53 ), // 5 (
    6 => array( 102 , 54 ), // 6 -
    7 => array( 103 , 55 ), // 7 è
    8 => array( 104 , 56 ), // 8 _
    9 => array( 105 , 57 ), // 9 ç
  ),
  'action' => array(
    'valider'   => 13,
    'retourner' => 27,
  ),
);
$nb_lignes = ($_SESSION['NOMBRE_CODES_NOTATION']>2) ? 5 : 4 ;
$tab_tactile_ligne = array_fill( 0 , $nb_lignes , '' );
foreach($tab_ascii['fleche'] as $key => $tab_infos)
{
  list( $keycode , $alt ) = $tab_infos;
  $tab_tactile_ligne[0] .= '<span id="kbd_'.$keycode.'"><img alt="'.$alt.'" src="./_img/fleche/fleche_'.$key.'1.gif" /></span>';
}
$nombre = 0;
foreach($tab_ascii['note_speciale'] as $alt => $tab_infos)
{
  $ligne = 1 + floor($nombre / 4);
  $keycode = $tab_infos[0];
  $tab_tactile_ligne[$ligne] .= '<span id="kbd_'.$keycode.'"><img alt="'.$alt.'" src="'.Html::note_src($alt).'" /></span>';
  $nombre++;
}
foreach( $_SESSION['NOTE_ACTIF'] as $key => $note_id )
{
  $ligne = 3 + floor($key / 4);
  $keycode = $tab_ascii['note_usuelle'][$_SESSION['NOTE'][$note_id]['CLAVIER']][0];
  $alt     = $_SESSION['NOTE'][$note_id]['SIGLE'];
  $tab_tactile_ligne[$ligne] .= '<span id="kbd_'.$keycode.'"><img alt="'.$alt.'" src="'.Html::note_src($note_id).'" /></span>';
}
$bloc_invisible = '<span style="visibility:hidden"></span>';
$bloc_retourner = '<span id="kbd_27" class="img retourner"></span>';
$bloc_valider   = '<span id="kbd_13" class="img valider"></span>';
switch($_SESSION['NOMBRE_CODES_NOTATION'])
{
  case 2:
    $tab_tactile_ligne[3] .= $bloc_retourner.$bloc_valider;
    break;
  case 3:
    $tab_tactile_ligne[3] .= $bloc_retourner;
    $tab_tactile_ligne[4] .= $bloc_invisible.$bloc_invisible.$bloc_invisible.$bloc_valider;
    break;
  case 4:
    $tab_tactile_ligne[4] .= $bloc_invisible.$bloc_retourner.$bloc_valider.$bloc_invisible;
    break;
  case 5:
    $tab_tactile_ligne[4] .= $bloc_retourner.$bloc_valider.$bloc_invisible;
    break;
  case 5:
    $tab_tactile_ligne[4] .= $bloc_retourner.$bloc_valider;
    break;
}
$tactile_ligne = '<div>'.implode('<div>'."\r\n".'</div>',$tab_tactile_ligne).'</div>';

// Tableau js des liens keycode => note_id
// Chaîne js récapitulative des keycode à surveiller
$tab_keycode = array();
foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
{
  foreach( $tab_ascii['note_usuelle'][$_SESSION['NOTE'][$note_id]['CLAVIER']] as $keycode )
  {
    $tab_keycode[$keycode] = 'tab_keycode_note['.$keycode.']="'.$note_id.'";';
  }
}

// Formulaires de choix des élèves et de choix d'une période dans le cas d'une évaluation sur un groupe
$select_eleve   = '';
$select_periode = '';

// Javascript
Layout::add( 'js_inline_before' , 'var tab_items      = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_profs      = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_eleves     = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_sujets     = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_corriges   = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_groupe     = new Array();' );

if($TYPE=='groupe')
{
  // Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri initial
  // Fabrication du tableau javascript "tab_groupe" indiquant le nom d'un groupe
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  $tab_options = array('classe'=>'','groupe'=>'','besoin'=>'');
  foreach($DB_TAB as $DB_ROW)
  {
    $groupe = Clean::upper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
    $tab_options[$DB_ROW['groupe_type']] .= '<option value="'.$groupe.'">'.html($DB_ROW['groupe_nom']).'</option>';
    Layout::add( 'js_inline_before' , 'tab_groupe["'.$groupe.'"]="'.html($DB_ROW['groupe_nom']).'";' );
  }
  foreach($tab_options as $type => $contenu)
  {
    if($contenu)
    {
      $select_eleve .= '<optgroup label="'.ucwords($type).'s">'.$contenu.'</optgroup>';
    }
  }
  // Élément de formulaire "f_aff_periode" pour le choix d'une période
  $select_periode = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() , 'f_aff_periode' /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
  // On désactive les périodes prédéfinies pour le choix "toute classe / tout groupe" initialement sélectionné
  $select_periode = preg_replace( '#'.'value="([1-9].*?)"'.'#' , 'value="$1" disabled' , $select_periode );
}

$select_selection_items = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , 'f_selection_items' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
$tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );

Form::load_choix_memo();

// Élément de formulaire "f_eleves_ordre"
$select_eleves_ordre = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre , 'f_eleves_ordre' /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre'] /*selection*/ , '' /*optgroup*/ );

// Longueur max pour un enregistrement audio (de toutes façons limitée techniquement à 120s).
// Selon les tests effectués la taille du MP3 enregistrée est de 3,9 Ko/s.
$AUDIO_DUREE_MAX = min( 120 , FICHIER_TAILLE_MAX/4 );

// Javascript
Layout::add( 'js_inline_before' , 'var TYPE           = "'.$TYPE.'";' );
Layout::add( 'js_inline_before' , 'var input_visible  = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var input_autoeval = "'.$date_autoeval.'";' );
Layout::add( 'js_inline_before' , 'var date_mysql     = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var user_id        = '.$_SESSION['USER_ID'].';' );
Layout::add( 'js_inline_before' , 'var reception_todo = '.$reception_todo.';' );
Layout::add( 'js_inline_before' , 'var reception_items_texte = "'.$txt_items.'";' );
Layout::add( 'js_inline_before' , 'var reception_users_texte = "'.$txt_users.'";' );
Layout::add( 'js_inline_before' , 'var reception_items_liste = "'.implode('_',$tab_items).'";' );
Layout::add( 'js_inline_before' , 'var reception_users_liste = "'.implode('_',$tab_users).'";' );
Layout::add( 'js_inline_before' , 'var auto_voir_devoir_id   = '.$auto_voir_devoir_id.';' );
Layout::add( 'js_inline_before' , 'var auto_voir_groupe_type = "'.$auto_voir_groupe_type.'";' );
Layout::add( 'js_inline_before' , 'var auto_voir_groupe_id   = '.$auto_voir_groupe_id.';' );
Layout::add( 'js_inline_before' , 'var tab_keycode_note = new Array();'.implode('',$tab_keycode) );
Layout::add( 'js_inline_before' , 'var keycode_search = ".'.implode('.',array_keys($tab_keycode)).'.8.46.65.68.69.70.78.80.82.";' );
Layout::add( 'js_inline_before' , 'var AUDIO_DUREE_MAX = '.$AUDIO_DUREE_MAX.';' );
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var select_groupe = "'.str_replace('"','\"','<option value="">&nbsp;</option>'.$select_eleve).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );

// Alerte initialisation annuelle non effectuée (test !empty() car un passage par la page d'accueil n'est pas obligatoire)
if(!empty($_SESSION['NB_DEVOIRS_ANTERIEURS']))
{
  echo'<p class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</p><hr />';
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form_prechoix" class="hide"><fieldset>
<?php if($TYPE=='groupe'): ?>
  <label class="tab" for="f_aff_classe">Classe / groupe :</label><select id="f_aff_classe" name="f_aff_classe"><option value="d2">Toute classe / tout groupe</option><?php echo $select_eleve ?></select>
<?php endif; ?>
  <div id="zone_periodes">
    <label class="tab" for="f_aff_periode">Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo To::jour_fin_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="tab"></span><input type="hidden" name="f_action" value="lister_evaluations" /><input type="hidden" name="f_type" value="<?php echo $TYPE ?>" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg_prechoix">&nbsp;</label>
  </div>
</fieldset><hr />
</form>

<table id="table_action" class="form hsort t9 hide">
  <thead>
    <tr>
      <th>Date devoir</th>
      <th>Date visible</th>
      <th>Fin auto-éval.</th>
      <th><?php echo($TYPE=='groupe')?'Classe / Groupe':'Élèves'; ?></th>
      <th>Partage</th>
      <th>Description</th>
      <th>Items</th>
      <th>Fichiers</th>
      <th>Rempli</th>
      <th class="nu"><q class="ajouter" title="Ajouter une évaluation."></q></th>
    </tr>
  </thead>
  <tbody>
    <tr class="vide"><td class="nu probleme" colspan="9">Cliquer sur l'icône ci-dessus (symbole "+" dans un rond vert) pour ajouter une évaluation.</td><td class="nu"></td></tr>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Dupliquer | Supprimer une évaluation</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_date">Date du devoir :</label><input id="f_date" name="f_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </p>
    <p>
      <label class="tab" for="f_date_visible">Date de visibilité :</label><input id="box_visible" type="checkbox" checked /> <label for="box_visible">identique</label><span><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_date_autoeval">Fin auto-évaluation :</label><input id="box_autoeval" type="checkbox" checked /> <label for="box_autoeval">sans objet</label><span><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span>
    </p>
    <p>
      <?php if($TYPE=='groupe'): ?>
        <label class="tab" for="f_groupe">Classe / groupe :</label><select id="f_groupe" name="f_groupe"><option></option></select> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><br />
        <span id="alerte_groupe" class="hide danger b">Attention : si vous modifiez le groupe, alors les notes de l'évaluation seront effacées !<br />En cas de même évaluation sur plusieurs groupes, il faut la <span class="u">dupliquer</span> et non la <span class="u">modifier</span>.</span><br />
      <?php endif; ?>
      <?php if($TYPE=='selection'): ?>
        <label class="tab" for="f_eleve_nombre">Élèves :</label><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="" readonly /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q> <?php echo $select_eleves_ordre ?><input id="f_eleve_liste" name="f_eleve_liste" type="text" value="" class="invisible" /><br />
      <?php endif; ?>
      <label class="tab" for="f_prof_nombre">Partage collègues :</label><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="" readonly /><q id="choisir_prof" class="choisir_prof" title="Voir ou choisir les collègues."></q><input id="f_prof_liste" name="f_prof_liste" type="text" value="" class="invisible" />
      <span id="choisir_prof_non" class="astuce">Choix restreint au propriétaire de l'évaluation.</span>
    </p>
    <p>
      <label class="tab" for="f_description">Description :</label><input id="f_description" name="f_description" type="text" value="" size="50" maxlength="60" /><br />
      <label class="tab" for="f_compet_nombre">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="" readonly /><q class="choisir_compet" title="Voir ou choisir les items."></q><input id="f_compet_liste" name="f_compet_liste" type="text" value="" class="invisible" />
    </p>
    <p>
      <label class="tab" for="f_mode_discret">Mode discret :</label><label for="f_mode_discret"><input id="f_mode_discret" name="f_mode_discret" type="checkbox" value="1" /> Cocher pour éviter l'envoi de notifications aux abonnés.</label>
    </p>
    <p class="astuce">
      Sujet et corrigé de l'évaluation peuvent être joints depuis l'interface principale.
      <input id="f_doc_sujet" name="f_doc_sujet" type="hidden" value="" />
      <input id="f_doc_corrige" name="f_doc_corrige" type="hidden" value="" />
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">Les notes associées à l'évaluation seront effacées !</p>
    <p>Confirmez-vous la suppression de l'évaluation &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_ref" name="f_ref" type="hidden" value="" /><input id="f_type" name="f_type" type="hidden" value="<?php echo $TYPE; ?>" /><input id="f_fini" name="f_fini" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <div id="arborescence"><label class="loader">Chargement&hellip;</label></div>
  <p id="alerte_items" class="fluo"><span class="danger b">Une évaluation dont la saisie a commencé ne devrait pas voir ses items modifiés.<br />En particulier, retirer des items d'une évaluation efface les scores correspondants déjà saisis !</span></p>
  <p id="info_items" class="fluo"><span class="astuce b">Cette évaluation comporte des items ne faisant pas partie des référentiels qui vous sont rattachés.<br />Ces items (ici invisibles) seront conservés lors de la validation des items ci-dessus.</span></p>
  <div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
  <hr />
  <p>
    <label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
    <label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
  <div class="astuce">Résumé des différents niveaux de droits (les plus élevés incluent les plus faibles)&nbsp;:</div>
  <ul class="puce">
    <li>0 &rarr; <span class="select_img droit_x">&nbsp;</span> aucun droit</li>
    <li>1 &rarr; <span class="select_img droit_v">&nbsp;</span> visualiser le devoir (et le dupliquer)</li>
    <li>2 &rarr; <span class="select_img droit_s">&nbsp;</span> co-saisir les notes du devoir</li>
    <li>3 &rarr; <span class="select_img droit_m">&nbsp;</span> modifier les paramètres (élèves, items, &hellip;) <span class="danger">Risqué : à utiliser en connaissance de cause&nbsp;!</span></li>
  </ul>
  <hr />
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion#toggle_evaluations_profs">DOC : Associer des collègues à une évaluation.</a></span>
  <hr />
  <?php echo HtmlForm::afficher_select_collegues( TRUE /*only_profs*/ , array( 1=>'v' , 2=>'s' , 3=>'m' ) ) ?>
  <div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<?php if($TYPE=='selection'): ?>
<form action="#" method="post" id="zone_eleve" class="arbre_dynamique hide">
  <div><button id="indiquer_eleves_deja" type="button" class="eclair">Indiquer les élèves associés à une évaluation de même nom</button> depuis le <input id="f_date_deja" name="f_date_deja" size="9" type="text" value="<?php echo To::jour_debut_annee_scolaire('french'); ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><label id="msg_indiquer_eleves_deja"></label></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <?php echo HtmlForm::afficher_checkbox_eleves_professeur(TRUE /*with_pourcent*/); ?>
  <p id="alerte_eleves" class="fluo"><span class="danger b">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves modifiés.<br />En particulier, retirer des élèves d'une évaluation efface les scores correspondants déjà saisis !</span></p>
  <div><span class="tab"></span><button id="valider_eleve" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_eleve" type="button" class="annuler">Annuler / Retour</button></div>
</form>
<?php endif; ?>

<form action="#" method="post" id="zone_upload" class="hide">
  <h2>Ajouter / Retirer un sujet ou une correction d'une évaluation</h2>
  <p class="hc b" id="titre_upload"></p>
  <p>
    <label class="tab">Sujet :</label><span id="span_sujet"></span> <button id="bouton_supprimer_sujet" type="button" class="supprimer">Retirer</button><br />
    <span class="tab"></span><button id="bouton_referencer_sujet" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_sujet" name="f_adresse_sujet" maxlength="256" size="50" type="text" value="" /><br />
    <span class="tab"></span><input id="f_uploader_sujet" type="file" name="userfile" /><button id="bouton_uploader_sujet" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
  </p>
  <p>
    <label class="tab">Corrigé :</label><span id="span_corrige"></span> <button id="bouton_supprimer_corrige" type="button" class="supprimer">Retirer</button><br />
    <span class="tab"></span><button id="bouton_referencer_corrige" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_corrige" name="f_adresse_corrige" maxlength="256" size="50" type="text" value="" /><br />
    <span class="tab"></span><input id="f_uploader_corrige" type="file" name="userfile" /><button id="bouton_uploader_corrige" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
  </p>
  <p>
    <span class="tab"></span><button id="fermer_zone_upload" type="button" class="retourner">Retour</button><label id="ajax_document_upload">&nbsp;</label>
    <input type="hidden" name="f_action" value="uploader_document" /><input type="hidden" id="f_doc_objet" name="f_doc_objet" value="" /><input type="hidden" id="uploader_ref" name="f_ref" value="" />
  </p>
</form>

<form action="#" method="post" id="zone_ordonner" class="hide">
  <h2>Réordonner les items d'une évaluation</h2>
  <p class="b" id="titre_ordonner"></p>
  <ul id="sortable_v">
    <li></li>
  </ul>
  <p>
    <button id="valider_ordre" type="button" class="valider">Enregistrer cet ordre</button> <button id="fermer_zone_ordonner" type="button" class="retourner">Retour</button> <label id="ajax_msg_ordonner">&nbsp;</label>
    <input id="ordre_ref" type="hidden" value="" />
  </p>
</form>

<?php
// Fabrication des éléments select du formulaire
Form::load_choix_memo();
$check_aff_reference    = (Form::$tab_choix['aff_reference'])    ? ' checked' : '' ;
$check_aff_coef         = (Form::$tab_choix['aff_coef'])         ? ' checked' : '' ;
$check_aff_socle        = (Form::$tab_choix['aff_socle'])        ? ' checked' : '' ;
$check_cart_restriction_item  = (Form::$tab_choix['cart_restriction_item'])  ? ' checked' : '' ;
$check_cart_restriction_eleve = (Form::$tab_choix['cart_restriction_eleve']) ? ' checked' : '' ;
$check_repart_categorie_autre = (Form::$tab_choix['repart_categorie_autre']) ? ' checked' : '' ;
$select_cart_detail            = HtmlForm::afficher_select(Form::$tab_select_cart_detail            , 'f_detail'          /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_detail']            /*selection*/ , '' /*optgroup*/ );
$select_cart_cases_nb          = HtmlForm::afficher_select(Form::$tab_select_cart_cases_nb          , 'f_cases_nb'        /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_cases_nb']          /*selection*/ , '' /*optgroup*/ );
$select_cart_contenu           = HtmlForm::afficher_select(Form::$tab_select_cart_contenu           , 'f_contenu'         /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_contenu']           /*selection*/ , '' /*optgroup*/ );
$select_orientation            = HtmlForm::afficher_select(Form::$tab_select_orientation            , 'f_orientation'     /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['orientation']            /*selection*/ , '' /*optgroup*/ );
$select_cart_hauteur           = HtmlForm::afficher_select(Form::$tab_select_cart_hauteur           , 'f_hauteur'         /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['cart_hauteur']           /*selection*/ , '' /*optgroup*/ );
$select_couleur                = HtmlForm::afficher_select(Form::$tab_select_couleur                , 'f_couleur'         /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['couleur']                /*selection*/ , '' /*optgroup*/ );
$select_fond                   = HtmlForm::afficher_select(Form::$tab_select_fond                   , 'f_fond'            /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['fond']                   /*selection*/ , '' /*optgroup*/ );
$select_marge_min              = HtmlForm::afficher_select(Form::$tab_select_marge_min              , 'f_marge_min'       /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['marge_min']              /*selection*/ , '' /*optgroup*/ );
$select_repart_ref_pourcentage = HtmlForm::afficher_select(Form::$tab_select_repart_ref_pourcentage , 'f_ref_pourcentage' /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['repart_ref_pourcentage'] /*selection*/ , '' /*optgroup*/ );
?>

<form action="#" method="post" id="zone_imprimer" class="hide"><fieldset>
  <h2>Imprimer le cartouche d'une évaluation</h2>
  <p class="b" id="titre_imprimer"></p>
  <label class="tab" for="f_detail">Détail :</label><?php echo $select_cart_detail ?><br />
  <label class="tab" for="f_cases_nb">Nombre de cases :</label><?php echo $select_cart_cases_nb ?><br />
  <label class="tab" for="f_contenu">Remplissage :</label><?php echo $select_cart_contenu ?><br />
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_marge_min ?><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement pour un choix de détail complet." /> Indications :</label><label for="f_reference_cart"><input type="checkbox" id="f_reference_cart" name="f_reference" value="1"<?php echo $check_aff_reference ?> /> Références</label>&nbsp;&nbsp;&nbsp;<label for="f_coef_cart"><input type="checkbox" id="f_coef_cart" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_cart"><input type="checkbox" id="f_socle_cart" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label><br />
    <label class="tab">Restriction :</label><input type="checkbox" id="f_restriction_item" name="f_restriction_item" value="1"<?php echo $check_cart_restriction_item ?> /> <label for="f_restriction_item">Uniquement les items dont une note est saisie ou faisant l'objet d'une demande d'évaluation.</label><br />
    <span class="tab"></span><input type="checkbox" id="f_restriction_eleve" name="f_restriction_eleve" value="1"<?php echo $check_cart_restriction_eleve ?> /> <label for="f_restriction_eleve">Uniquement les élèves ayant fait l'objet d'une saisie de code couleur (pas ABS etc.).</label><br />
    <label class="tab" for="f_detail">Hauteur des blocs :</label><?php echo $select_cart_hauteur ?><br />
  </div>
  <span class="tab"></span><button id="valider_imprimer" type="button" class="valider">Générer le cartouche</button> <button id="fermer_zone_imprimer" type="button" class="retourner">Retour</button> <label id="ajax_msg_imprimer">&nbsp;</label>
  <input id="imprimer_ref"          name="f_ref"          type="hidden" value="" />
  <input id="imprimer_date_fr"      name="f_date_fr"      type="hidden" value="" />
  <input id="imprimer_groupe_nom"   name="f_groupe_nom"   type="hidden" value="" />
  <input id="imprimer_eleves_ordre" name="f_eleves_ordre" type="hidden" value="" />
  <input id="imprimer_description"  name="f_description"  type="hidden" value="" />
  <p id="zone_imprimer_retour"></p>
</fieldset></form>

<form action="#" method="post" id="zone_voir_repart" class="hide"><fieldset>
  <h2>Voir les répartitions des élèves à une évaluation</h2>
  <p class="b" id="titre_voir_repart"></p>
  <label class="tab" for="f_categorie_autre">Catégorie "Autre" :</label><input type="checkbox" id="f_categorie_autre" name="f_categorie_autre" value="1"<?php echo $check_repart_categorie_autre ?> /> <label for="f_categorie_autre">Tenir compte de cette catégorie.</label><br />
  <label class="tab" for="f_ref_pourcentage">Pourcentage :</label><?php echo $select_repart_ref_pourcentage ?>
  <hr />
  <h3>Répartition quantitative des scores</h3>
  <table id="table_voir_repart_quantitative" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <hr />
  <h3>Répartition nominative des scores</h3>
  <table id="table_voir_repart_nominative" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <hr />
  <h3>Exploitation</h3>
  <p><a id="lien_repart_nominative" target="_blank" rel="noopener noreferrer" href=""><span class="file file_htm">Préparer une évaluation / Constituer un groupe de besoin (format <em>html</em>).</span></a></p>
  <hr />
  <h3>Archivage PDF</h3>
  <input id="repart_ref"         name="f_ref"         type="hidden" value="" />
  <input id="repart_date_fr"     name="f_date_fr"     type="hidden" value="" />
  <input id="repart_groupe_nom"  name="f_groupe_nom"  type="hidden" value="" />
  <input id="repart_description" name="f_description" type="hidden" value="" />
  <button id="archiver_repart" type="button" class="imprimer">Archiver / Imprimer</button> le tableau avec la 
  <select id="repart_type" name="f_repartition_type"><option value="nominative">répartition nominative</option><option value="quantitative">répartition quantitative</option></select>
  des scores 
  <?php echo str_replace( 'id="f_couleur"' , 'id="f_repart_couleur"' , $select_couleur); ?>
  <?php echo str_replace( 'id="f_fond"'    , 'id="f_repart_fond"'    , $select_fond); ?>
  <p>
    <span class="noprint">Afin de préserver l'environnement, n'imprimer que si nécessaire !</span>
    <label id="ajax_msg_archiver_repart"></label>
  </p>
</fieldset></form>

<div id="zone_saisir_voir" class="hide">
  <h2>Saisir / Voir les acquisitions d'une évaluation</h2>
  <p>
    <b id="titre_saisir_voir"></b> <button id="valider_saisir" type="button" class="valider">Enregistrer les saisies</button> <button id="fermer_zone_saisir_voir" type="button" class="retourner">Retour</button> <label id="ajax_msg_saisir_voir"></label>
  </p>
  <table id="table_saisir_voir" class="scor_eval">
    <tbody><tr><td></td></tr></tbody>
  </table>
  <p id="para_report_note" class="ti">Note à reporter dans &hellip;
    <label for="f_report_cellule">[ <input type="radio" id="f_report_cellule" name="f_endroit_report_note" value="cellule" checked /> la cellule ]</label>
    <label for="f_report_colonne">[ <input type="radio" id="f_report_colonne" name="f_endroit_report_note" value="colonne" /> la <span class="u">C</span>olonne ]</label>
    <label for="f_report_ligne">[ <input type="radio" id="f_report_ligne" name="f_endroit_report_note" value="ligne" /> la <span class="u">L</span>igne ]</label>
    <label for="f_report_tableau">[ <input type="radio" id="f_report_tableau" name="f_endroit_report_note" value="tableau" /> le <span class="u">T</span>ableau ]</label>.
    <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_resultats#toggle_saisies_multiples ">DOC : Report multiple.</a></span>
  </p>
  <p class="ti"><button id="afficher_deport_archivage" type="button" class="parametre">Saisie déportée &amp; Archivage.</button></p>
</div>

<form action="#" method="post" id="zone_deport_archivage" class="hide"><fieldset>
  <h2>Saisie déportée &amp; Archivage</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></p>
  <input id="saisir_voir_ref"          name="f_ref"          type="hidden" value="" />
  <input id="saisir_voir_date_fr"      name="f_date_fr"      type="hidden" value="" />
  <input id="saisir_voir_date_visible" name="f_date_visible" type="hidden" value="" />
  <input id="saisir_voir_groupe_nom"   name="f_groupe_nom"   type="hidden" value="" />
  <input id="saisir_voir_eleves_ordre" name="f_eleves_ordre" type="hidden" value="" />
  <input id="saisir_voir_description"  name="f_description"  type="hidden" value="" />
  <input id="saisir_voir_fini"         name="f_fini"         type="hidden" value="" />
  <ul class="puce p">
    <li><button id="generer_tableau_scores_vierge_csv" type="button" class="fichier_export">Récupérer un fichier vierge pour une saisie déportée (format <em>csv</em>).</button>
    <li><button id="generer_tableau_scores_rempli_csv" type="button" class="fichier_export">Récupérer un fichier complété avec les scores <b>enregistrés</b> (format <em>csv</em>).</button>
    <li class="saisir"><input id="f_importer_scores" type="file" name="userfile" /><button id="bouton_importer_scores" type="button" class="fichier_import">Envoyer un fichier de notes complété (format <em>csv</em>).</button></li>
    <li class="voir"><span class="astuce">Pour importer un fichier <em>csv</em> de notes complété, choisir "<em>Saisir les acquisitions</em>".</span></li>
  </ul>
  <ul class="puce p">
    <li><button id="generer_tableau_scores_vierge_pdf" type="button" class="imprimer">Imprimer un tableau vierge utilisable pour un report manuel des notes (format <em>pdf</em>).</button>
    <li><button id="generer_tableau_scores_rempli_pdf" type="button" class="imprimer">Archiver / Imprimer le tableau complété avec les scores <b>enregistrés</b> (format <em>pdf</em>).</button>
  </ul>
  <div>
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label> <?php echo str_replace( 'id="f_couleur"' , 'id="f_deport_archivage_couleur"' , $select_couleur); ?> <?php echo str_replace( 'id="f_fond"'    , 'id="f_deport_archivage_fond"'    , $select_fond); ?><br />
    <label class="tab" for="f_detail">Détail :</label><?php echo $select_cart_detail ?><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Uniquement pour un choix de détail complet." /> Indications :</label><label for="f_reference_deport"><input type="checkbox" id="f_reference_deport" name="f_reference" value="1"<?php echo $check_aff_reference ?> /> Références</label>&nbsp;&nbsp;&nbsp;<label for="f_coef_deport"><input type="checkbox" id="f_coef_deport" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_deport"><input type="checkbox" id="f_socle_deport" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>
  </div>
  <p class="ti">
    <span class="noprint">Afin de préserver l'environnement, n'imprimer que si nécessaire !</span><br />
  </p>

  <hr />
  <p><label id="ajax_msg_deport_archivage">&nbsp;</label><input type="hidden" id="f_archivage_action" name="f_action" value="" /></p>
<!--
<a target="_blank" rel="noopener noreferrer" href=""><span class="file file_txt"></span></a></li>
<a id="" target="_blank" rel="noopener noreferrer" href=""><span class="file file_pdf">.</span></a></li>
-->
</fieldset></form>

<div id="zone_voir_commentaires" class="hide">
  <h2>Consulter un commentaire pour un élève à une évaluation</h2>
  <p id="titre_voir_commentaires" class="b"></p>
  <div id="report_texte">
    <h3>Commentaire écrit</h3>
    <textarea id="f_voir_texte" rows="10" cols="60" readonly></textarea>
  </div>
  <div id="report_audio">
    <h3>Commentaire audio</h3>
    <audio id="f_ecouter_audio" controls="" class="prof"><span class="probleme">Votre navigateur est trop ancien, il ne supporte pas la balise [audio] !</span></audio>
  </div>
</div>

<form action="#" method="post" id="zone_enregistrer_texte" class="hide"><fieldset>
  <h2>Commentaire écrit personnalisé</h2>
  <hr />
  <ul class="puce">
    <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion#toggle_evaluations_commentaire_texte">DOC : Commentaire écrit personnalisé.</a></span></li>
  </ul>
  <hr />
  <p class="b">
    <label class="tab">Élève :</label><span id="titre_enregistrer_texte"></span>
  </p>
  <div>
    <label for="f_msg_texte" class="tab">Message :</label><textarea name="f_msg_data" id="f_msg_texte" rows="10" cols="60"></textarea><br />
    <span class="tab"></span><label id="f_msg_texte_reste"></label>
  </div>
  <p>
    <span class="tab"></span><button id="valider_enregistrer_texte" type="button" class="valider">Valider</button> <button id="annuler_enregistrer_texte" type="button" class="annuler">Annuler</button> <label id="ajax_msg_enregistrer_texte">&nbsp;</label>
    <input id="enregistrer_texte_ref"       name="f_ref"       type="hidden" value="" />
    <input id="enregistrer_texte_eleve_id"  name="f_eleve_id"  type="hidden" value="" />
    <input id="enregistrer_texte_msg_url"   name="f_msg_url"   type="hidden" value="" />
    <input id="enregistrer_texte_msg_autre" name="f_msg_autre" type="hidden" value="" />
  </p>
  <p id="report_tableau_texte"></p>
</fieldset></form>

<form action="#" method="post" id="zone_enregistrer_audio" class="hide"><fieldset>
  <h2>Commentaire audio personnalisé</h2>
  <hr />
  <ul class="puce">
    <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion#toggle_evaluations_commentaire_audio">DOC : Commentaire audio personnalisé.</a></span></li>
    <li><span class="danger">Fonctionnalité expérimentale ! <span class="fluo">Utiliser Chrome en HTTPS | Opera en HTTPS | Edge à jour.</span></span></li>
    <li><span class="astuce">Enregistrement de <?php echo $AUDIO_DUREE_MAX ?> s maximum, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre.<br />Dans tous les cas l'enregistrement ne peut techniquement pas dépasser 120 secondes." /></span></li>
  </ul>
  <hr />
  <p class="b">
    <label class="tab">Élève :</label><span id="titre_enregistrer_audio"></span>
  </p>
  <p>
    <label class="tab">Enregistrement :</label><span id="record_start" class="hide"><button id="audio_enregistrer_start" type="button" class="enregistrer_start">Démarrer</button></span><span id="record_stop" class="hide"><button id="audio_enregistrer_stop" type="button" class="enregistrer_stop">Arrêter</button></span> <label id="ajax_msg_enregistrer_audio">&nbsp;</label>
  </p>
  <p>
    <label class="tab">Lecture :</label><span id="record_play" class="hide"><audio id="audio_lecture" controls="" class="prof"><span class="probleme">Votre navigateur est trop ancien, il ne supporte pas la balise [audio] !</span></audio></span> <span id="record_delete" class="hide"><button id="audio_enregistrer_supprimer" type="button" class="supprimer">Supprimer</button></span>
  </p>
  <div>
    <span class="tab"></span><button id="fermer_enregistrer_audio" type="button" class="retourner">Retour</button>
    <input id="enregistrer_audio_ref"       name="f_ref"       type="hidden" value="" />
    <input id="enregistrer_audio_eleve_id"  name="f_eleve_id"  type="hidden" value="" />
    <input id="enregistrer_audio_msg_url"   name="f_msg_url"   type="hidden" value="" />
    <input id="enregistrer_audio_msg_autre" name="f_msg_autre" type="hidden" value="" />
    <input id="enregistrer_audio_msg_data"  name="f_msg_data"  type="hidden" value="" />
  </div>
  <p id="report_tableau_audio"></p>
</fieldset></form>

<div id="zone_confirmer_fermer_saisir" class="hide">
  <p class="danger">Des saisies ont été effectuées, mais n'ont pas été enregistrées.</p>
  <p>Confirmez-vous vouloir quitter l'interface de saisie ?</p>
  <p>
    <button id="confirmer_fermer_zone_saisir" type="button" class="valider">Oui, je ne veux pas enregistrer</button>
    <button id="annuler_fermer_zone_saisir" type="button" class="annuler">Non, je reste sur l'interface</button>
  </p>
</div>

<?php /*  Pour la saisie des notes à la souris */ ?>
<div id="td_souris_container"><div class="td_souris">
<?php echo $souris_ligne ?>
</div></div>

<?php /*  Clavier virtuel pour les dispositifs tactiles */ ?>
<div id="cadre_tactile">
<?php echo $tactile_ligne ?>
</div>
