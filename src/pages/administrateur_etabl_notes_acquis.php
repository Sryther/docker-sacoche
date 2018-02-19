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
$TITRE = html(Lang::_("Codes de notation / États d'acquisition"));

// codes de notation

$DB_TAB_NOTE = DB_STRUCTURE_PARAMETRE::DB_lister_parametres_note( FALSE /*priority_actifs*/ );

$tab_notes = array();
foreach($DB_TAB_NOTE as $DB_ROW_NOTE)
{
  $note_id = (int)$DB_ROW_NOTE['note_id'];
  $check = ($DB_ROW_NOTE['note_actif']) ? ' checked' : '' ;
  $class = ($DB_ROW_NOTE['note_actif']) ? 'show' : 'hide' ;
  $tab_notes[] = '<li id="N'.$note_id.'">'
               .   '<div><label class="tab mini" for="note_actif_'.$note_id.'"><span class="t9 notnow">#'.$note_id.' :</span></label><label for="note_actif_'.$note_id.'"><input type="checkbox" id="note_actif_'.$note_id.'" name="note_actif_'.$note_id.'" value="1"'.$check.' />&nbsp;Activer</label></div>'
               .   '<div class="'.$class.'">'
               .     '<div><label class="tab mini" for="note_valeur_'.$note_id.'">valeur :</label><input type="number" min="0" max="200" class="hc" id="note_valeur_'.$note_id.'" name="note_valeur_'.$note_id.'" value="'.$DB_ROW_NOTE['note_valeur'].'" /></div>'
               .     '<div><label class="tab mini">symbole :</label><img alt="#'.$note_id.'" src="'.Html::note_src_couleur($DB_ROW_NOTE['note_image'],'h').'" /><input type="hidden" id="note_image_'.$note_id.'" name="note_image_'.$note_id.'" value="'.$DB_ROW_NOTE['note_image'].'" /><q class="modifier" title="Modifier ce choix."></q></div>'
               .     '<div><label class="tab mini" for="note_sigle_'.$note_id.'">sigle :</label><input type="text" size="2" maxlength="3" class="hc" id="note_sigle_'.$note_id.'" name="note_sigle_'.$note_id.'" value="'.html($DB_ROW_NOTE['note_sigle']).'" /></div>'
               .     '<div><label class="tab mini" for="note_legende_'.$note_id.'">légende :</label><input type="text" size="15" maxlength="40" id="note_legende_'.$note_id.'" name="note_legende_'.$note_id.'" value="'.html($DB_ROW_NOTE['note_legende']).'" /></div>'
               .     '<div><label class="tab mini" for="note_clavier_'.$note_id.'">touche :</label><input type="text" size="2" maxlength="1" class="hc" id="note_clavier_'.$note_id.'" name="note_clavier_'.$note_id.'" value="'.$DB_ROW_NOTE['note_clavier'].'" /></div>'
               .   '</div>'
               . '</li>';
}

// états d'acquisition

$DB_TAB_ACQUIS = DB_STRUCTURE_PARAMETRE::DB_lister_parametres_acquis( FALSE /*only_actifs*/ );

$tab_acquis = array();
foreach($DB_TAB_ACQUIS as $DB_ROW_ACQUIS)
{
  $acquis_id = (int)$DB_ROW_ACQUIS['acquis_id'];
  $check = ($DB_ROW_ACQUIS['acquis_actif']) ? ' checked' : '' ;
  $class = ($DB_ROW_ACQUIS['acquis_actif']) ? 'show' : 'hide' ;
  $tab_acquis[] = '<li id="A'.$acquis_id.'">'
                .   '<div><label class="tab mini" for="acquis_actif_'.$acquis_id.'"><span class="t9 notnow">#'.$acquis_id.' :</span></label><label for="acquis_actif_'.$acquis_id.'"><input type="checkbox" id="acquis_actif_'.$acquis_id.'" name="acquis_actif_'.$acquis_id.'" value="1"'.$check.' />&nbsp;Activer</label></div>'
                .   '<div class="'.$class.'">'
                .     '<div><label class="tab mini">seuils :</label><input type="number" min="0" max="100" class="hc" id="acquis_seuil_'.$acquis_id.'_min" name="acquis_seuil_'.$acquis_id.'_min" value="'.html($DB_ROW_ACQUIS['acquis_seuil_min']).'" />~<input type="number" min="0" max="100" class="hc" id="acquis_seuil_'.$acquis_id.'_max" name="acquis_seuil_'.$acquis_id.'_max" value="'.html($DB_ROW_ACQUIS['acquis_seuil_max']).'" /></div>'
                .     '<div><label class="tab mini" for="acquis_color_'.$acquis_id.'">couleur :</label><input type="text" class="stretch" size="8" id="acquis_color_'.$acquis_id.'" name="acquis_color_'.$acquis_id.'" value="'.$DB_ROW_ACQUIS['acquis_couleur'].'" style="background-color:'.$DB_ROW_ACQUIS['acquis_couleur'].'" /></div>'
                .     '<div><span class="tab mini"></span><button type="button" id="report_color_'.$acquis_id.'" name="color_'.$acquis_id.'" value="'.$DB_ROW_ACQUIS['acquis_couleur'].'" class="colorer">Valeur enregistrée</button></div>'
                .     '<div><label class="tab mini" for="acquis_sigle_'.$acquis_id.'">sigle :</label><input type="text" size="2" maxlength="3" class="hc" id="acquis_sigle_'.$acquis_id.'" name="acquis_sigle_'.$acquis_id.'" value="'.html($DB_ROW_ACQUIS['acquis_sigle']).'" /></div>'
                .     '<div><label class="tab mini" for="acquis_legende_'.$acquis_id.'">légende :</label><input type="text" size="15" maxlength="40" id="acquis_legende_'.$acquis_id.'" name="acquis_legende_'.$acquis_id.'" value="'.html($DB_ROW_ACQUIS['acquis_legende']).'" /></div>'
                .     '<div><label class="tab mini" for="acquis_valeur_'.$acquis_id.'">valeur :</label><input type="number" min="0" max="100" class="hc" id="acquis_valeur_'.$acquis_id.'" name="acquis_valeur_'.$acquis_id.'" value="'.$DB_ROW_ACQUIS['acquis_valeur'].'" /></div>'
                .   '</div>'
                . '</li>';
}

// Listing des symboles colorés fournis avec <em>SACoche

$chemin_dossier = CHEMIN_DOSSIER_IMG.'note'.DS.'choix'.DS.'h'.DS;
$tab_fichiers = FileSystem::lister_contenu_dossier($chemin_dossier);
$tab_notes_sacoche = array();
foreach($tab_fichiers as $fichier_nom)
{
  if( $fichier_nom != 'X.gif' )
  {
    list( $fichier_partie_1 , $fichier_partie_2 ) = explode( '_' , $fichier_nom , 2 );
    $image_nom = substr($fichier_nom,0,-4);
    if(!isset($tab_notes_sacoche[$fichier_partie_1]))
    {
      $tab_notes_sacoche[$fichier_partie_1] = '';
    }
    $tab_notes_sacoche[$fichier_partie_1] .= '<div class="p"><a href="#" id="s_'.$image_nom.'"><img alt="'.$image_nom.'" src="'.Html::note_src_couleur($image_nom,'h','sacoche').'" /></a></div>';
  }
}

// Listing des symboles persos uploadés par l'établissement

$tab_notes_perso = array();
$DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images_notes();
foreach($DB_TAB as $DB_ROW)
{
  $image_nom = 'upload_'.$DB_ROW['image_note_id'];
  // Enregistrer les fichiers sur le disque
  FileSystem::ecrire_fichier( FileSystem::chemin_fichier_symbole($image_nom,'h','perso') , base64_decode($DB_ROW['image_contenu_h']) );
  FileSystem::ecrire_fichier( FileSystem::chemin_fichier_symbole($image_nom,'v','perso') , base64_decode($DB_ROW['image_contenu_v']) );
  // Générer la balise html pour afficher l'image
  $tab_notes_perso[] = '<span class="note_liste"><a href="#" id="p_'.$image_nom.'"><img alt="'.$image_nom.'" src="'.Html::note_src_couleur($image_nom,'h','perso').'" /></a><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></span>';
}

?>

<form action="#" method="post" id="form_notes">
  <h2>Codes de notation : nombre, valeur, symbole coloré, sigle, légende, clavier</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__codes_notation">DOC : Codes de notation</a></span></p>
  <ul id="sortable_h_note">
    <?php echo implode('',$tab_notes); ?>
  </ul>
  <p><span class="tab"></span><input type="hidden" id="notes_actif" name="notes_actif" value="" /><input type="hidden" id="notes_ordre" name="notes_ordre" value="" /><button id="bouton_valider_notes" type="button" class="parametre">Enregistrer ces choix.</button><label id="ajax_msg_notes">&nbsp;</label></p>
</form>

<hr />

<form action="#" method="post" id="form_acquis">
  <h2>États d'acquisitions : nombre, seuils, couleur, sigle, légende, valeur</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__etats_acquisition">DOC : États d'acquisitions</a></span></p>
  <?php /* Pas mis dans un tableau, sinon colorpicker bugue avec IE */ ?>
  <ul id="sortable_h_acquis">
    <?php echo implode('',$tab_acquis); ?>
    <li class="colorpicker"><div id="colorpicker" class="hide"></div></li>
  </ul>
  <p><span class="tab"></span><input type="hidden" id="acquis_actif" name="acquis_actif" value="" /><input type="hidden" id="acquis_ordre" name="acquis_ordre" value="" /><button id="bouton_valider_acquis" type="button" class="parametre">Enregistrer ces choix.</button><label id="ajax_msg_acquis">&nbsp;</label></p>
</form>

<hr />

<form action="#" method="post" id="form_symbole" class="hide">
  <p class="astuce">Cliquer sur un symbole coloré ou <button id="bouton_annuler_note" type="button" class="retourner">Annuler / Retour</button>.</p>
  <h3>Symboles fournis avec <em>SACoche</em></h3>
  <div id="notes_sacoche" class="note_liste"><?php echo implode('</div>'.NL.'<div class="note_liste">',$tab_notes_sacoche) ?></div>
  <h3 style="clear:both">Symboles spécifiques (établissement)</h3>
  <p><label class="tab" for="f_symbole">Uploader image :</label><input type="hidden" name="f_action" value="upload_symbole" /><input id="f_symbole" type="file" name="userfile" /><button id="bouton_choisir_symbole" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_symbole">&nbsp;</label></p>
  <div id="notes_perso" class="note_liste"><?php echo implode('',$tab_notes_perso) ?></div>
</form>
