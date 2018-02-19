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
$TITRE = html(Lang::_("Évaluer un élève à la volée"));

$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
$tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;

$select_groupe  = HtmlForm::afficher_select($tab_groupes  , 'f_classe'  /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_matiere = HtmlForm::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );

// boutons radio
$tab_radio_boutons = array();
$tab_notes = array_merge( $_SESSION['NOTE_ACTIF'] , array( 'NN' , 'NE' , 'NF' , 'NR' , 'AB' , 'DI' , 'PA' , 'X' ) );
foreach($tab_notes as $note)
{
  $tab_radio_boutons[] = '<label for="note_'.$note.'"><span class="td"><input type="radio" id="note_'.$note.'" name="f_note" value="'.$note.'"> <img alt="'.$note.'" src="'.Html::note_src($note).'" /></span></label>';
}
$radio_boutons = implode(' ',$tab_radio_boutons);

// Alerte initialisation annuelle non effectuée (test !empty() car un passage par la page d'accueil n'est pas obligatoire)
if(!empty($_SESSION['NB_DEVOIRS_ANTERIEURS']))
{
  echo'<p class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</p><hr />';
}
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_ponctuelles">DOC : Évaluer un élève à la volée.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p>
  <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><label id="ajax_maj_matiere">&nbsp;</label><br />
  <span id="bloc_niveau" class="hide"><label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><option></option></select><label id="ajax_maj_niveau">&nbsp;</label><br /></span>
  <span id="bloc_item" class="hide"><label class="tab" for="f_item">Item :</label><select id="f_item" name="f_item"><option></option></select></span>
  </p>
  <p>
    <label class="tab" for="f_classe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj_groupe">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève :</label><select id="f_eleve" name="f_eleve"><option></option></select></span>
  </p>
  <div id="zone_validation" class="p hide">
    <label class="tab">Note :</label><?php echo $radio_boutons ?>
    <input id="f_devoir" name="f_devoir" type="hidden" value="0" />
    <input id="f_groupe" name="f_groupe" type="hidden" value="0" />
    <p>
      <label class="tab" for="f_classe">Intitulé :</label><input id="box_autodescription" name="box_autodescription" type="checkbox" checked /> <label for="box_autodescription">automatique</label><span class="hide"><input id="f_description" name="f_description" type="text" value="" size="50" maxlength="60" /></span>
    </p>
    <p>
      <span class="tab"></span><button id="bouton_valider" type="button" class="valider">Enregistrer.</button><label id="ajax_msg_enregistrement">&nbsp;</label>
    </p>
  </div>
</fieldset></form>
<hr />
<div id="bilan" class="hide">
  <ul class="puce">
    <li>Une fois toutes vos notes saisies, vous pouvez <a id="bilan_lien" href="./index.php?page=evaluation&amp;section=gestion_selection&amp;devoir_id=0&amp;groupe_type=E&amp;groupe_id=0">voir l'évaluation correspondante ainsi obtenue</a>.</li>
  </ul>
</div>
