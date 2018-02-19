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
$TITRE = html(Lang::_("Liste des évaluations"));

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && (!$_SESSION['NB_ENFANTS']) )
{
  echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Réception d'id transmis via un lien en page d'accueil.
$auto_voir_devoir_id   = isset($_GET['devoir_id']) ? Clean::entier($_GET['devoir_id']) : 'false' ;
$auto_select_eleve_id  = isset($_GET['eleve_id'])  ? Clean::entier($_GET['eleve_id'])  : FALSE ;
$auto_select_classe_id = isset($_GET['classe_id']) ? Clean::entier($_GET['classe_id']) : FALSE ;
$auto_mode             = isset($_GET['autoeval'])  ? 'saisir'                          : 'voir' ;

$bouton_modifier_profs    = '';

// Fabrication des éléments select du formulaire
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_eleves  = array(); // maj en ajax suivant le choix du groupe
  $tab_profs   = array( 0 => array( 'valeur'=>0 , 'texte'=>'Tous les enseignants' ) );
  $of_groupe  = '';
  $of_eleve   = FALSE;
  $of_prof    = FALSE;
  $sel_groupe = FALSE;
  $sel_eleve  = FALSE;
  $class_form_eleve = 'show';
  $class_bloc_eleve = 'hide';
  $class_form_prof  = 'hide';
  $js_aff_nom_eleve = 'true';
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_eleves  = array(); // maj en ajax suivant le choix du groupe
  $tab_profs   = array( 0 => array( 'valeur'=>$_SESSION['USER_ID'] , 'texte'=>To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']) ) );
  $of_groupe  = '';
  $of_eleve   = FALSE;
  $of_prof    = FALSE;
  $sel_groupe = FALSE;
  $sel_eleve  = FALSE;
  $class_form_eleve = 'show';
  $class_bloc_eleve = 'hide';
  $class_form_prof  = 'hide';
  $js_aff_nom_eleve = 'true';
  $bouton_modifier_profs = '<button id="modifier_prof" type="button" class="form_ajouter">&plusmn;</button>';
}

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']>1) )
{
  if( $auto_select_eleve_id && !$auto_select_classe_id )
  {
    // Par exemple via un lien dans un mail de notification (car pour un lien depuis la page d'accueil c'est bon)
    foreach( $_SESSION['OPT_PARENT_ENFANTS'] as $tab )
    {
      if( $tab['valeur'] == $auto_select_eleve_id )
      {
        $auto_select_classe_id = $tab['classe_id'];
        break;
      }
    }
  }
  $tab_groupes = $_SESSION['OPT_PARENT_CLASSES'];
  $tab_eleves  = ($auto_select_eleve_id) ? DB_STRUCTURE_COMMUN::DB_OPT_eleves_regroupement( 'classe' , $auto_select_classe_id , 1 /*statut*/ , 'alpha' /*ordre*/ ) : array() ; // maj en ajax suivant le choix du groupe
  $tab_profs   = ($auto_select_eleve_id) ? DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe('classe',$auto_select_classe_id) : array() ;
  $of_groupe  = '';
  $of_eleve   = FALSE;
  $of_prof    = 'tous_profs';
  $sel_groupe = $auto_select_classe_id;
  $sel_eleve  = $auto_select_eleve_id;
  $class_form_eleve = 'show';
  $class_bloc_eleve = ($auto_select_eleve_id) ? 'show' : 'hide' ;
  $class_form_prof  = ($auto_select_eleve_id) ? 'show' : 'hide' ;
  $js_aff_nom_eleve = 'true';
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes = array( 0 => array( 'valeur'=>$_SESSION['ELEVE_CLASSE_ID'] , 'texte'=>$_SESSION['ELEVE_CLASSE_NOM'] , 'optgroup'=>'classe' ) );
  $tab_eleves  = array( 0 => array( 'valeur'=>$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'] , 'texte'=>$_SESSION['OPT_PARENT_ENFANTS'][0]['texte'] ) );
  $tab_profs   = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe('classe',$_SESSION['ELEVE_CLASSE_ID']);
  $of_groupe  = FALSE;
  $of_eleve   = '';
  $of_prof    = 'tous_profs';
  $sel_groupe = $_SESSION['ELEVE_CLASSE_ID'];
  $sel_eleve  = $_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'];
  $class_form_eleve = 'hide';
  $class_bloc_eleve = 'hide';
  $class_form_prof  = 'show';
  $js_aff_nom_eleve = 'false';
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes = array( 0 => array( 'valeur'=>$_SESSION['ELEVE_CLASSE_ID'] , 'texte'=>$_SESSION['ELEVE_CLASSE_NOM'] , 'optgroup'=>'classe' ) );
  $tab_eleves  = array( 0 => array( 'valeur'=>$_SESSION['USER_ID'] , 'texte'=>$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'] ) );
  $tab_profs   = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe( 'classe' , $_SESSION['ELEVE_CLASSE_ID'] );
  $of_groupe  = FALSE;
  $of_eleve   = FALSE;
  $of_prof    = 'tous_profs';
  $sel_groupe = $_SESSION['ELEVE_CLASSE_ID'];
  $sel_eleve  = $_SESSION['USER_ID'];
  $class_form_eleve = 'hide';
  $class_bloc_eleve = 'hide';
  $class_form_prof  = 'show';
  $js_aff_nom_eleve = 'false';
}

$select_groupe     = HtmlForm::afficher_select($tab_groupes , 'f_groupe' /*select_nom*/ , $of_groupe /*option_first*/ , $sel_groupe          /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleve      = HtmlForm::afficher_select($tab_eleves  , 'f_eleve' /*select_nom*/  , $of_eleve  /*option_first*/ , $sel_eleve           /*selection*/ ,              '' /*optgroup*/ );
$select_professeur = HtmlForm::afficher_select($tab_profs   , 'f_prof'   /*select_nom*/ , $of_prof   /*option_first*/ , $_SESSION['USER_ID'] /*selection*/ ,              '' /*optgroup*/ );

$bouton_valider_autoeval = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? '<button id="valider_saisir" type="button" class="valider">Enregistrer les saisies</button>' : '<button type="button" class="valider" disabled>Réservé à l\'élève.</button>' ;

if(Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_ETAT_ACQUISITION_AVEC_EVALUATION']))
{
  $score_texte    = '<th>Score<br />cumulé</th>';
  $colonne_nombre = 4;
}
else
{
  $score_texte    = '';
  $colonne_nombre = 3;
}

// Ajout exceptionnel de css pour simuler une ligne cliquable (le js traite l'événement)
Layout::add( 'css_inline' , '#table_action tbody td:hover {cursor:pointer}' );

// Javascript
Layout::add( 'js_inline_before' , 'var tab_dates = new Array();' );
Layout::add( 'js_inline_before' , 'var aff_nom_eleve = '.$js_aff_nom_eleve.';' );
Layout::add( 'js_inline_before' , 'var auto_voir_devoir_id = '.$auto_voir_devoir_id.';' );
Layout::add( 'js_inline_before' , 'var auto_mode = "'.$auto_mode.'";' );
Layout::add( 'js_inline_before' , 'var user_id     = '.$_SESSION['USER_ID'].';' );
Layout::add( 'js_inline_before' , 'var user_texte  = "'.html(To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE'])).'";' );
Layout::add( 'js_inline_before' , 'var user_profil = "'.$_SESSION['USER_PROFIL_TYPE'].'";' );
?>

<?php if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent'))): ?>
<p class="astuce">Les "évaluations" sont les "blocs-conteneurs" utilisés pour renseigner les résultats <span class="u">de l'année scolaire en cours</span>.</p>
<hr />
<?php endif; ?>

<form action="#" method="post" id="form"><fieldset>
  <div class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
    <span id="bloc_eleve" class="<?php echo $class_bloc_eleve ?>"><label class="tab" for="f_eleve">Élève :</label><?php echo $select_eleve ?></span>
  </div>

  <div id="zone_profs" class="<?php echo $class_form_prof ?>">
    <label class="tab" for="f_prof">Enseignant :</label><?php echo $select_professeur ?><?php echo $bouton_modifier_profs ?>
  </div>

  <label class="tab">Période :</label>du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q> au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo To::jour_fin_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
  <span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>


<form action="#" method="post" id="zone_eval_choix" class="hide">
  <hr />
  <h2></h2>
  <table id="table_action" class="form hsort vm_nug">
    <thead>
      <tr>
        <th>Date</th>
        <th>Professeur</th>
        <th>Description</th>
        <th>Rempli</th>
        <th class="nu"></th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="5"></td></tr>
    </tbody>
  </table>
</form>

<div id="zone_eval_voir" class="hide">
  <h2>Voir les items et les notes (si saisies) d'une évaluation</h2>
  <p id="titre_voir" class="b"></p>
  <table id="table_voir" class="hsort">
    <thead>
      <tr>
        <th>Ref.</th>
        <th>Nom de l'item</th>
        <th>Note à<br />ce devoir</th>
        <?php echo $score_texte ?>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="<?php echo $colonne_nombre ?>"></td></tr>
    </tbody>
  </table>
  <div id="report_legende">
  </div>
  <div id="report_texte">
  </div>
  <div id="report_audio">
  </div>
</div>

<form action="#" method="post" id="zone_eval_saisir" class="hide" onsubmit="return false">
  <h2>S'auto-évaluer</h2>
  <p id="titre_saisir" class="b"></p>
  <table id="table_saisir" class="vm_nug">
    <thead>
      <tr>
        <th colspan="6">Note</th>
        <th>Item</th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="7"></td></tr>
    </tbody>
  </table>
  <?php echo Html::legende( array('codes_notation'=>TRUE) ); ?>
  <div>
    <h3>Commentaire éventuel</h3>
    <textarea name="f_msg_data" id="f_msg_texte" rows="5" cols="100"></textarea><br />
    <span class="tab"></span><label id="f_msg_texte_reste"></label>
  </div>
  <p class="astuce">Auto-évaluation possible jusqu'au <span id="report_date" class="b"></span> (les notes peuvent ensuite être modifiées par le professeur).</p>
  <p class="ti"><?php echo $bouton_valider_autoeval ?><input type="hidden" name="f_devoir" id="f_devoir" value="" /><input type="hidden" name="f_msg_url" id="f_msg_url" value="" /><input type="hidden" name="f_msg_autre" id="f_msg_autre" value="" /> <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button><label id="msg_saisir"></label></p>
</form>
