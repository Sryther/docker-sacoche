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
$TITRE = html(Lang::_("Mise en page des bilans officiels"));

$check_denomination = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'denomination')) ? ' checked' : '' ;
$check_adresse      = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'adresse'))      ? ' checked' : '' ;
$check_telephone    = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'telephone'))    ? ' checked' : '' ;
$check_fax          = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'fax'))          ? ' checked' : '' ;
$check_courriel     = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'courriel'))     ? ' checked' : '' ;
$check_url          = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'url'))          ? ' checked' : '' ;
$check_logo         = (mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'logo'))         ? ' checked' : '' ;

$options_infos_responsables = '<option value="non">ne pas indiquer les coordonnées des responsables</option><option value="oui_libre">indiquer les coordonnées des responsables, emplacement libre</option><option value="oui_force">indiquer les coordonnées des responsables, emplacement forcé (enveloppe à fenêtre)</option>';
$options_infos_responsables = str_replace( '"'.$_SESSION['OFFICIEL']['INFOS_RESPONSABLES'].'"' , '"'.$_SESSION['OFFICIEL']['INFOS_RESPONSABLES'].'" selected' , $options_infos_responsables );

$options_nombre_exemplaires = '<option value="un">un document par élève (pour le responsable n°1)</option><option value="deux_si_besoin">deux documents seulement si les responsables ont des adresses différentes</option><option value="deux_de_force">deux documents par élève (un pour chaque responsable)</option>';
$options_nombre_exemplaires = str_replace( '"'.$_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES'].'"' , '"'.$_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES'].'" selected' , $options_nombre_exemplaires );

$options_marge = '<option value="5">5mm</option><option value="6">6mm</option><option value="7">7mm</option><option value="8">8mm</option><option value="9">9mm</option><option value="10">10mm</option><option value="11">11mm</option><option value="12">12mm</option><option value="13">13mm</option><option value="14">14mm</option><option value="15">15mm</option>';
$options_marge_gauche = str_replace( '"'.$_SESSION['OFFICIEL']['MARGE_GAUCHE'].'"' , '"'.$_SESSION['OFFICIEL']['MARGE_GAUCHE'].'" selected' , $options_marge );
$options_marge_droite = str_replace( '"'.$_SESSION['OFFICIEL']['MARGE_DROITE'].'"' , '"'.$_SESSION['OFFICIEL']['MARGE_DROITE'].'" selected' , $options_marge );
$options_marge_haut   = str_replace( '"'.$_SESSION['OFFICIEL']['MARGE_HAUT']  .'"' , '"'.$_SESSION['OFFICIEL']['MARGE_HAUT']  .'" selected' , $options_marge );
$options_marge_bas    = str_replace( '"'.$_SESSION['OFFICIEL']['MARGE_BAS']   .'"' , '"'.$_SESSION['OFFICIEL']['MARGE_BAS']   .'" selected' , $options_marge );

$options_tampon_signature = '<option value="sans">ne pas utiliser cette fonctionnalité</option><option value="tampon">utiliser uniquement le tampon de l\'établissement</option><option value="signature">utiliser uniquement la signature de l\'utilisateur</option><option value="signature_ou_tampon">utiliser la signature de l\'utilisateur, ou à défaut le tampon de l\'établissement</option>';
$options_tampon_signature = str_replace( '"'.$_SESSION['OFFICIEL']['TAMPON_SIGNATURE'].'"' , '"'.$_SESSION['OFFICIEL']['TAMPON_SIGNATURE'].'" selected' , $options_tampon_signature );

$class_enveloppe = ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') ? '' : ' class="hide"' ;

function fabriquer_chaine_option($mini,$maxi)
{
  $options = '';
  for( $i=$mini ; $i<=$maxi ; $i++ )
  {
    $options .= '<option value="'.$i.'">'.number_format($i/10,1,',','').'cm</option>';
  }
  return $options;
}

$options_horizontal_gauche = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'].'" selected' , fabriquer_chaine_option(90,120) );
$options_horizontal_milieu = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'].'" selected' , fabriquer_chaine_option(85,115) );
$options_horizontal_droite = str_replace( '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'].'"' , '"'.$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'].'" selected' , fabriquer_chaine_option(15,25) );
$options_vertical_haut     = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_HAUT']    .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_HAUT']    .'" selected' , fabriquer_chaine_option(40,60) );
$options_vertical_milieu   = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']  .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']  .'" selected' , fabriquer_chaine_option(35,55) );
$options_vertical_bas      = str_replace( '"'.$_SESSION['ENVELOPPE']['VERTICAL_BAS']     .'"' , '"'.$_SESSION['ENVELOPPE']['VERTICAL_BAS']     .'" selected' , fabriquer_chaine_option(15,25) );

// Formulaire avec la liste des directeurs et professeurs
$select_user = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_professeurs_directeurs_etabl( 1 /*statut*/) , 'f_user' /*select_nom*/ , 'tampon_structure' /*option_first*/ , FALSE /*selection*/ , 'profs_directeurs' /*optgroup*/ );

// Récupérer les signatures existantes, dont le tampon de l'établissement.
$li_signatures = '';
$DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_signatures_avec_identite();
foreach($DB_TAB as $DB_ROW)
{
  // Enregistrer temporairement le fichier sur le disque
  $texte = ($DB_ROW['user_id']) ? 'Signature '.$DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'] : 'Tampon de l\'établissement' ;
  $fichier_nom = 'signature_'.$_SESSION['BASE'].'_'.$DB_ROW['user_id'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$DB_ROW['image_format'];
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , base64_decode($DB_ROW['image_contenu']) );
  // Générer la balise html pour afficher l'image
  list($width,$height) = Image::dimensions_affichage( $DB_ROW['image_largeur'] , $DB_ROW['image_hauteur'] , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
  $li_signatures .= '<li id="sgn_'.$DB_ROW['user_id'].'">'.html($texte).' : <img src="'.URL_DIR_EXPORT.$fichier_nom.'" alt="'.html($texte).'" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>';
}
$li_signatures = ($li_signatures) ? $li_signatures : '<li id="sgn_none">Aucun fichier image trouvé !</li>' ;

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_mise_en_page">DOC : Réglages synthèses &amp; bilans &rarr; Mise en page des bilans officiels</a></span></div>

<hr />

<form action="#" method="post" id="form_mise_en_page">
  <h2>En-tête du document</h2>
  <p>
    <label class="tab">Infos établissement :</label>
    <label for="f_coordonnees_denomination"><input type="checkbox" id="f_coordonnees_denomination" name="f_coordonnees[]" value="denomination"<?php echo $check_denomination ?> /> dénomination</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_adresse"><input type="checkbox" id="f_coordonnees_adresse" name="f_coordonnees[]" value="adresse"<?php echo $check_adresse ?> /> adresse</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_telephone"><input type="checkbox" id="f_coordonnees_telephone" name="f_coordonnees[]" value="telephone"<?php echo $check_telephone ?> /> téléphone</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_fax"><input type="checkbox" id="f_coordonnees_fax" name="f_coordonnees[]" value="fax"<?php echo $check_fax ?> /> fax</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_courriel"><input type="checkbox" id="f_coordonnees_courriel" name="f_coordonnees[]" value="courriel"<?php echo $check_courriel ?> /> courriel</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_url"><input type="checkbox" id="f_coordonnees_url" name="f_coordonnees[]" value="url"<?php echo $check_url ?> /> site internet</label>&nbsp;&nbsp;&nbsp;
    <label for="f_coordonnees_logo"><input type="checkbox" id="f_coordonnees_logo" name="f_coordonnees[]" value="logo"<?php echo $check_logo ?> /> logo</label>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_coordonnees" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_coordonnees">&nbsp;</label>
  </p>
  <hr />
  <h2>Responsables légaux</h2>
  <p>
    <label class="tab" for="f_infos_responsables">Coord. responsables :</label><select id="f_infos_responsables" name="f_infos_responsables"><?php echo $options_infos_responsables; ?></select>
  </p>
  <p>
    <label class="tab" for="f_nombre_exemplaires">Nb d'exemplaires :</label><select id="f_nombre_exemplaires" name="f_nombre_exemplaires"><?php echo $options_nombre_exemplaires; ?></select>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_responsables" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_responsables">&nbsp;</label>
  </p>
  <hr />
  <h2>Positionnement des données</h2>
  <p id="p_enveloppe"<?php echo $class_enveloppe ?>>
    <img src="./_img/enveloppe.png" alt="envelopppe" width="230" height="115" class="fd" />
    <label class="tab">Dim. enveloppe :</label><span class="astuce i">Voir légende sur le schéma ci-contre.</span><br />
    <span class="tab"></span>
    <label for="f_horizontal_gauche">HG </label><select id="f_horizontal_gauche" name="f_horizontal_gauche"><?php echo $options_horizontal_gauche; ?></select>&nbsp;&nbsp;&nbsp;
    <label for="f_horizontal_milieu">HM </label><select id="f_horizontal_milieu" name="f_horizontal_milieu"><?php echo $options_horizontal_milieu; ?></select>&nbsp;&nbsp;&nbsp;
    <label for="f_horizontal_droite">HD </label><select id="f_horizontal_droite" name="f_horizontal_droite"><?php echo $options_horizontal_droite; ?></select><br />
    <span class="tab"></span>
    <label for="f_vertical_haut">VH </label><select id="f_vertical_haut" name="f_vertical_haut"><?php echo $options_vertical_haut; ?></select>&nbsp;&nbsp;&nbsp;
    <label for="f_vertical_milieu">VM </label><select id="f_vertical_milieu" name="f_vertical_milieu"><?php echo $options_vertical_milieu; ?></select>&nbsp;&nbsp;&nbsp;
    <label for="f_vertical_bas">VB </label><select id="f_vertical_bas" name="f_vertical_bas"><?php echo $options_vertical_bas; ?></select>
  </p>
  <p>
    <label class="tab">Marges bord page :</label>
      <label for="f_marge_gauche">à gauche </label><select id="f_marge_gauche" name="f_marge_gauche"><?php echo $options_marge_gauche; ?></select>&nbsp;&nbsp;&nbsp;
      <label for="f_marge_droite">à droite </label><select id="f_marge_droite" name="f_marge_droite"><?php echo $options_marge_droite; ?></select>&nbsp;&nbsp;&nbsp;
      <label for="f_marge_haut">en haut </label><select id="f_marge_haut" name="f_marge_haut"><?php echo $options_marge_haut; ?></select>&nbsp;&nbsp;&nbsp;
      <label for="f_marge_bas">en bas </label><select id="f_marge_bas" name="f_marge_bas"><?php echo $options_marge_bas; ?></select>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_positionnement" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_positionnement">&nbsp;</label>
  </p>
  <hr />
  <h2>Tampon &amp; Signatures</h2>
  <p>
    <label class="tab" for="f_tampon_signature">Usage :</label><select id="f_tampon_signature" name="f_tampon_signature"><?php echo $options_tampon_signature; ?></select>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_signature" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_signature">&nbsp;</label>
  </p>
</form>
<form action="#" method="post" id="form_tampon">
  <hr />
  <h2></h2>
  <p>
    <label class="tab" for="f_signature">Uploader image :</label> concernant <?php echo $select_user ?> <input id="f_signature" type="file" name="userfile" /><button id="bouton_choisir_signature" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_upload_signature">&nbsp;</label>
    <input type="hidden" name="f_action" value="upload_signature" />
    <input type="hidden" id="f_upload_user_id" name="f_user_id" value="0" />
    <input type="hidden" id="f_upload_user_texte" name="f_user_texte" value="Tampon de l'établissement" />
  </p>
</form>
<h3>Images enregistrées</h3>
<ul class="puce" id="listing_signatures"><?php echo $li_signatures ?></ul>
<hr />
