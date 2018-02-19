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
$TITRE = "Contacter les administrateurs d'un établissement scolaire"; // Pas de traduction car pas de choix de langue à ce niveau.

// Récupération du numéro de base
$BASE = isset($_GET['base']) ? Clean::entier($_GET['base']) : 0 ;

if( (HEBERGEUR_INSTALLATION=='multi-structures') && !$BASE )
{
  exit_error( 'Information manquante' /*titre*/ , 'Absence de numéro de base transmis dans l\'adresse.' /*contenu*/ );
}

// Récupérer la dénomination de l'établissement
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  $structure_denomination = DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_nom_for_Id($BASE);
  if($structure_denomination===NULL)
  {
    exit_error( 'Établissement manquant' /*titre*/ , 'Établissement non trouvé dans la base d\'administration !' /*contenu*/ );
  }
}
else
{
  $DB_TAB = DB_STRUCTURE_PARAMETRE::DB_lister_parametres('"webmestre_denomination"');
  if(!empty($DB_TAB))
  {
    $structure_denomination = $DB_TAB[0]['parametre_valeur'];
  }
  else
  {
    exit_error( 'Base incomplète' /*titre*/ , 'Base de l\'établissement incomplète ou non encore installée !' /*contenu*/ );
  }
}

$message = '';

// Récupération d'une éventuelle adresse mail de qqun qui signalerait un envoi anormal
$courriel = isset($_GET['courriel']) ? Clean::courriel($_GET['courriel']) : '' ;

if($courriel)
{
  $message .= 'Bonjour,'."\r\n";
  $message .= 'J\'ai reçu à mon adresse '.$courriel.' un courriel en provenance de votre instance SACoche alors que je ne n\'ai pas de compte sur ce logiciel.'."\r\n";
  $message .= 'Sans doute la conséquence une adresse erronée dans votre base d\'utilisateurs ?'."\r\n";
  $message .= 'Merci d\'y regarder et me tenir au courant.'."\r\n";
  $message .= 'Cordialement.'."\r\n";
}

// Récupération d'un texte éventuel de qqun qui signalerait un pb d'authentification
$msg_html = isset($_GET['msg']) ? Clean::texte($_GET['msg']) : '' ;

if($msg_html)
{
  $tab_bad = array( '<br />' , '&nbsp;' , '&hellip;' , '<b>' , '</b>' );
  $tab_bon = array( "\r\n"   , ' '      , '...'      , ''    , ''     );
  $message .= 'Bonjour,'."\r\n";
  $message .= 'Je rencontre le problème d\'authentification suivant.'."\r\n";
  $message .= 'Merci d\'y regarder et me tenir au courant.'."\r\n";
  $message .= 'Cordialement.'."\r\n";
  $message .= '- - - - - - - - - - - - - - - - - - - -'."\r\n";
  $message .= str_replace($tab_bad,$tab_bon,$msg_html);
}

// Protection contre les robots (pour éviter des envois intempestifs de courriels)
list($html_imgs,$captcha_soluce) = Outil::captcha();
$_SESSION['TMP']['CAPTCHA'] = array(
  'TIME'   => $_SERVER['REQUEST_TIME'] ,
  'DELAI'  => 4, // en secondes, est ensuite incrémenté en cas d'erreur
  'SOLUCE' => $captcha_soluce,
);

$is_etablissement_virtuel = IS_HEBERGEMENT_SESAMATH && ( ($BASE==ID_DEMO) || ($BASE>=CONVENTION_ENT_ID_ETABL_MAXI) || (substr($structure_denomination,0,5)=='Voir ') ) ? TRUE : FALSE ;

// Javascript
Layout::add( 'js_inline_before' , 'var NOM_LONGUEUR_MAX = '.NOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PRENOM_LONGUEUR_MAX = '.PRENOM_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<?php if(!$is_etablissement_virtuel): ?>

<form id="form_contact" action="#" method="post">
  <div id="step1">
    <h2>Étape 1/2 - Saisie des informations</h2>
    <label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="<?php echo $BASE ?>" /><input id="f_denomination" name="f_denomination" size="40" type="text" value="<?php echo html($structure_denomination) ?>" readonly /><br />
    <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="50" maxlength="<?php echo NOM_LONGUEUR_MAX ?>" /><br />
    <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" value="" size="50" maxlength="<?php echo PRENOM_LONGUEUR_MAX ?>" /><br />
    <label class="tab" for="f_courriel"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Un code de confirmation y sera envoyé.<br />Vérifiez bien votre saisie !" /> Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="<?php echo html($courriel) ?>" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" /><br />
    <label class="tab">Anti-robot :</label><span id="captcha_game">Cliquer du plus petit au plus grand <?php echo $html_imgs ?></span><span id="captcha_init" class="hide">Ordre enregistré. <button type="button" class="actualiser">Recommencer.</button></span><input id="f_captcha" name="f_captcha" type="text" value="" class="invisible" /><br />
    <label for="f_message" class="tab">Message :</label><textarea name="f_message" id="f_message" rows="9" cols="55"><?php echo html($message) ?></textarea><br />
    <span class="tab"></span><label id="f_message_reste"></label><br />
    <span class="tab"></span><button id="f_bouton_envoyer" type="submit" class="mail_envoyer">Enregistrer.</button><label id="ajax_msg_envoyer" class="astuce">Un code de confirmation vous sera alors envoyé.</label>
  </div>
  <div id="step2" class="hide">
    <h2>Étape 2/2 - Confirmation</h2>
    <p class="astuce">Veuillez saisir le code qui vient d'être envoyé à l'adresse <b id="report_courriel"></b>.</p>
    <label class="tab" for="f_code">Code :</label><input id="f_code" name="f_code" type="text" value="" size="10" maxlength="8" /><input id="f_md5" name="f_md5" type="hidden" value="" /><br />
    <span class="tab"></span><button id="f_bouton_confirmer" type="submit" class="valider">Valider.</button><label id="ajax_msg_confirmer" class="astuce">Votre message sera ensuite transmis aux administrateurs.</label>
  </div>
  <div id="step3" class="hide">
    <p><label class="valide">Votre message a été transmis <span id="span_admin_nb"></span> (établissement <em><?php echo html($structure_denomination) ?></em>).</label></p>
  </div>
</form>

<?php else: ?>

<p class="danger">Vous vous êtes visiblement égaré&nbsp;!</p>
<p class="astuce">Il n'y a aucune raison de contacter les administrateurs de l'établissement <em>"<?php echo html($structure_denomination) ?>"</em> car il s'agit d'une structure virtuelle&hellip;</p>
<ul class="puce">
  <li class="p">Consulter <a class="b" href="<?php echo SERVEUR_PROJET ?>" target="_blank" rel="noopener noreferrer">le site officiel du projet <em>SACoche</em></a> pour tout renseignement ou besoin de contact.</li>
</ul>

<?php endif; ?>

<hr />

<div class="hc"><a href="./index.php?base=<?php echo $BASE ?>">[ Retour en page d'accueil ]</a></div>
