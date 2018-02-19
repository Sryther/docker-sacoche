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
$TITRE = html(Lang::_("Importer des fichiers d'utilisateurs"));
?>

<?php
$alerte = !empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? '<p class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</p>' : '' ;
$alerte.= DB_STRUCTURE_ADMINISTRATEUR::DB_compter_niveaux_etabl( FALSE /*with_specifiques*/ ) ? '' : '<p class="danger b">Aucun niveau de classe choisi pour l\'établissement&nbsp;!<br />Commencez par <a href="./index.php?page=administrateur_etabl_niveau">indiquer les niveaux de classe de votre établissement</a>.</p>' ;

$test_UAI = ($_SESSION['WEBMESTRE_UAI']) ? 'oui' : 'non' ;

$annee_siecle = To::annee_scolaire('siecle');
$nom_fin_fichier = $_SESSION['WEBMESTRE_UAI'].'_'.$annee_siecle;

$auto_select_categorie = isset($_GET['categorie']) ? Clean::fichier($_GET['categorie']) : '' ;

// Javascript
$jour_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql');
$check_eleve      = ( $jour_debut_annee_scolaire > $_SESSION['DATE_LAST_IMPORT_ELEVES']      ) ? 'complet' : 'partiel' ;
$check_parent     = ( $jour_debut_annee_scolaire > $_SESSION['DATE_LAST_IMPORT_PARENTS']     ) ? 'complet' : 'partiel' ;
$check_professeur = ( $jour_debut_annee_scolaire > $_SESSION['DATE_LAST_IMPORT_PROFESSEURS'] ) ? 'complet' : 'partiel' ;
Layout::add( 'js_inline_before' , 'var check_eleve      = "'.$check_eleve.'";' );
Layout::add( 'js_inline_before' , 'var check_parent     = "'.$check_parent.'";' );
Layout::add( 'js_inline_before' , 'var check_professeur = "'.$check_professeur.'";' );
Layout::add( 'js_inline_before' , 'var auto_select_categorie = "'.$auto_select_categorie.'";' );

?>

<form action="#" method="post" id="form_choix">

  <ul class="puce">
    <li><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></li>
    <li><span class="astuce">Pour un traitement individuel on peut utiliser les pages de gestion [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Professeurs / Directeurs / Personnels</a>].</span></li>
    <li><span class="astuce">Les administrateurs ne se gèrent qu'individuellement depuis la page [<a href="./index.php?page=administrateur_administrateur">Administrateurs</a>].</span></li>
  </ul>
  <?php echo $alerte ?>

  <hr />

  <div>
    <input type="hidden" id="f_action" name="f_action" value="" /><input type="hidden" id="f_step" name="f_step" value="10" /><input id="f_import" type="file" name="userfile" />
    <label class="tab" for="f_choix_principal">Catégorie :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value="">&nbsp;</option>
      <optgroup label="Fichiers extraits de Siècle / STS-Web (recommandé pour le second degré)">
        <option value="siecle_commun_<?php echo $test_UAI ?>">Importer le fichier "Communs" (pour le LSU).</option>
        <option value="siecle_nomenclature_<?php echo $test_UAI ?>">Importer les nomenclatures (pour le LSU).</option>
        <option value="siecle_professeurs_directeurs_<?php echo $test_UAI ?>">Importer les professeurs &amp; directeurs (avec leurs affectations).</option>
        <option value="siecle_eleves_<?php echo $test_UAI ?>">Importer les élèves (avec leurs affectations).</option>
        <option value="siecle_parents_<?php echo $test_UAI ?>">Importer les parents (avec adresses et responsabilités).</option>
      </optgroup>
      <optgroup label="Fichiers extraits de ONDE ex-BE1D (recommandé pour le premier degré)">
        <option value="onde_eleves">Importer les élèves (avec leurs affectations).</option>
        <option value="onde_parents">Importer les parents (avec adresses et responsabilités).</option>
      </optgroup>
      <optgroup label="Fichiers extraits de Factos (recommandé pour les établissements français à l'étranger)">
        <option value="factos_eleves">Importer les élèves (avec leurs affectations).</option>
        <option value="factos_parents">Importer les parents (avec adresses et responsabilités).</option>
      </optgroup>
      <optgroup label="Fichiers fabriqués avec un tableur (hors Éducation Nationale française)">
        <option value="tableur_professeurs_directeurs">Importer les professeurs &amp; directeurs (avec leurs affectations).</option>
        <option value="tableur_eleves">Importer les élèves (avec leurs affectations).</option>
        <option value="tableur_parents">Importer les parents (avec adresses et responsabilités).</option>
      </optgroup>
    </select><br />
    <span id="span_mode" class="hide">
      <label class="tab">Affichage :</label>
      <label for="f_mode_complet"><input type="radio" id="f_mode_complet" name="f_mode" value="complet" /> bilan complet (import de début d'année)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_mode_partiel"><input type="radio" id="f_mode_partiel" name="f_mode" value="partiel" /> seulement les différences trouvées (mise à jour en cours d'année)</label>
    </span>
  </div>

  <fieldset id="fieldset_siecle_commun_non" class="hide">
    <hr />
    <label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
    <div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voir la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
  </fieldset>

  <fieldset id="fieldset_siecle_commun_oui" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle">DOC : Import d'utilisateurs depuis Siècle / STS-Web</a></span></li>
      <li>Indiquez le fichier <em>Communs.xml</em> (ou <em>Communs.zip</em>) : <button id="siecle_commun" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_siecle_nomenclature_non" class="hide">
    <hr />
    <label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
    <div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voir la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
  </fieldset>

  <fieldset id="fieldset_siecle_nomenclature_oui" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle">DOC : Import d'utilisateurs depuis Siècle / STS-Web</a></span></li>
      <li>Indiquez le fichier <em>Nomenclature.xml</em> (ou <em>Nomenclature.zip</em>) : <button id="siecle_nomenclature" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_siecle_professeurs_directeurs_non" class="hide">
    <hr />
    <label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
    <div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voir la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
  </fieldset>

  <fieldset id="fieldset_siecle_professeurs_directeurs_oui" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle">DOC : Import d'utilisateurs depuis Siècle / STS-Web</a></span></li>
      <li>Indiquez le fichier <em>sts_emp_<?php echo $nom_fin_fichier ?>.xml</em> (ou <em>sts_emp_<?php echo $nom_fin_fichier ?>.zip</em>) : <button id="siecle_professeurs_directeurs" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_siecle_eleves_non" class="hide">
    <hr />
    <label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
    <div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voir la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
  </fieldset>

  <fieldset id="fieldset_siecle_eleves_oui" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle">DOC : Import d'utilisateurs depuis Siècle / STS-Web</a></span></li>
      <li>Indiquez le fichier <em>ExportXML_ElevesSansAdresses.zip</em> (ou <em>ElevesSansAdresses.xml</em>) : <button id="siecle_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_siecle_parents_non" class="hide">
    <hr />
    <label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
    <div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voir la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
  </fieldset>

  <fieldset id="fieldset_siecle_parents_oui" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle">DOC : Import d'utilisateurs depuis Siècle / STS-Web</a></span></li>
      <li>Indiquer le fichier <em>ResponsablesAvecAdresses.zip</em> (ou <em>ResponsablesAvecAdresses.xml</em>) : <button id="siecle_parents" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_onde_eleves" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_onde">DOC : Import d'utilisateurs depuis ONDE (ex-BE1D)</a></span></li>
      <li>Indiquer le fichier <em>CSVExtraction.csv</em> : <button id="onde_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_onde_parents" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_onde">DOC : Import d'utilisateurs depuis ONDE (ex-BE1D)</a></span></li>
      <li>Indiquer le fichier <em>CSVExtraction Parents.csv</em> : <button id="onde_parents" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_factos_eleves" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_factos">DOC : Import d'utilisateurs depuis Factos</a></span></li>
      <li>Indiquer le fichier <em>nom-du-fichier.csv</em> : <button id="factos_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_factos_parents" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_factos">DOC : Import d'utilisateurs depuis Factos</a></span></li>
      <li>Indiquer le fichier <em>nom-du-fichier.csv</em> : <button id="factos_parents" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_tableur_professeurs_directeurs" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_tableur#toggle_importer_profs">DOC : Import d'utilisateurs avec un tableur</a></span></li>
      <li>Indiquer le fichier <em>nom-du-fichier-profs.csv</em> (ou <em>nom-du-fichier-profs.txt</em>) : <button id="tableur_professeurs_directeurs" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_tableur_eleves" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_tableur#toggle_importer_eleves">DOC : Import d'utilisateurs avec un tableur</a></span></li>
      <li>Indiquer le fichier <em>nom-du-fichier-eleves.csv</em> (ou <em>nom-du-fichier-eleves.txt</em>) : <button id="tableur_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_tableur_parents" class="hide">
    <hr />
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_tableur#toggle_importer_parents">DOC : Import d'utilisateurs avec un tableur</a></span></li>
      <li>Indiquer le fichier <em>nom-du-fichier-parents.csv</em> (ou <em>nom-du-fichier-parents.txt</em>) : <button id="tableur_parents" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
  </fieldset>

</form>


<form action="#" method="post" id="form_bilan"><fieldset>
  <hr />
  <label id="ajax_msg">&nbsp;</label>
</fieldset></form>
