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
$TITRE = "Identifiants perdus"; // Pas de traduction car pas de choix de langue à ce niveau.

// Récupération du profil
$PROFIL = (isset($_GET['profil'])) ? Clean::lettres($_GET['profil']) : '' ;

if( !in_array( $PROFIL , array('structure','webmestre','partenaire') ) )
{
  exit_error( 'Information manquante' /*titre*/ , 'Profil incorrect ou non transmis dans l\'adresse.' /*contenu*/ );
}

// Récupération du numéro de base
$BASE = (isset($_GET['base'])) ? Clean::entier($_GET['base']) : 0 ;

if( ($PROFIL=='structure') && (HEBERGEUR_INSTALLATION=='multi-structures') && !$BASE )
{
  exit_error( 'Information manquante' /*titre*/ , 'Numéro de base incorrect ou non transmis dans l\'adresse.' /*contenu*/ );
}

// Récupérer la dénomination de l'établissement
if($PROFIL=='structure')
{
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
}

// Récupération d'une éventuelle adresse mail de qqun qui voudrait obtenir un accès
$courriel = isset($_GET['courriel']) ? Clean::courriel($_GET['courriel']) : '' ;

// Protection contre les attaques par force brute des robots (piratage compte ou envoi intempestif de courriels)
list($html_imgs,$captcha_soluce) = Outil::captcha();
$_SESSION['FORCEBRUTE'][$PAGE] = array(
  'TIME'    => $_SERVER['REQUEST_TIME'] ,
  'DELAI'   => 4, // en secondes, est ensuite incrémenté en cas d'erreur
  'CAPTCHA' => $captcha_soluce,
);

$is_etablissement_virtuel = IS_HEBERGEMENT_SESAMATH && ( ($BASE==ID_DEMO) || ($BASE>=CONVENTION_ENT_ID_ETABL_MAXI) || (substr($structure_denomination,0,5)=='Voir ') ) ? TRUE : FALSE ;

// Javascript
Layout::add( 'js_inline_before' , 'var COURRIEL_LONGUEUR_MAX = '.COURRIEL_LONGUEUR_MAX.';' );
?>

<?php if( ($PROFIL=='structure') && (!$is_etablissement_virtuel) ): ?>
<form id="form_lost" action="#" method="post">
  <h2>Cas n°1 : une adresse de courriel est associée à votre compte</h2>
  <div id="step1">
    <p>Alors utilisez ce formulaire afin d'obtenir de nouveaux identifiants :</p>
    <div><label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="<?php echo $BASE ?>" /><em><?php echo html($structure_denomination) ?></em></div>
    <div><label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="<?php echo html($courriel) ?>" size="50" maxlength="<?php echo COURRIEL_LONGUEUR_MAX ?>" /></div>
    <div><label class="tab">Anti-robot :</label><span id="captcha_game">Cliquer du plus petit au plus grand <?php echo $html_imgs ?></span><span id="captcha_init" class="hide">Ordre enregistré. <button type="button" class="actualiser">Recommencer.</button></span><input id="f_captcha" name="f_captcha" type="text" value="" class="invisible" /></div>
    <p><span class="tab"></span><button id="f_bouton_rechercher" type="submit" class="rechercher">Rechercher.</button><label id="ajax_msg_rechercher"></label></p>
  </div>
  <div id="step2" class="hide">
    <p>Confirmez ou sélectionnez le compte concerné :</p>
    <label class="tab" for="f_user">Utilisateur :</label><select id="f_user" name="f_user"><option value="-1"></option></select>
    <p><span class="tab"></span><button id="f_bouton_envoyer" type="submit" class="mail_envoyer">Envoyer.</button><label id="ajax_msg_envoyer"></label></p>
  </div>
  <hr />
  <h2>Cas n°2 : vous n'aviez pas d'adresse de courriel renseignée</h2>
  <p>
    Alors suivre, selon votre profil, <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__identifiants#toggle_oubli_mdp">la procédure décrite dans la documentation</a></span>.
  </p>
</form>
<p id="lost_confirmation" class="hide">
  <label class="valide">Courriel envoyé à l'adresse indiquée : consultez votre boite aux lettres électronique.</label>
</p>
<?php endif; ?>

<?php if( ($PROFIL=='structure') && ($is_etablissement_virtuel) ): ?>

<p class="danger">Vous vous êtes visiblement égaré&nbsp;!</p>
<p class="astuce">Il n'y a aucune raison de demander un nouveau mot de passe pour un utilisateur de l'établissement <em>"<?php echo html($structure_denomination) ?>"</em> car il s'agit d'une structure virtuelle&hellip;</p>
<ul class="puce">
  <li class="p">Consulter <a class="b" href="<?php echo SERVEUR_PROJET ?>/index.php?page=utilisation__serveur_sesamath__demo" target="_blank" rel="noopener noreferrer">le site officiel du projet <em>SACoche</em></a> pour tout renseignement concernant l'établissement de démonstration.</li>
</ul>

<?php endif; ?>

<?php if($PROFIL=='webmestre'): ?>
<p class="astuce">Le webmestre est la personne qui a installé le logiciel sur ce serveur.</p>
<p class="danger">Ne mélangez pas le compte "webmestre du serveur" avec les comptes administrateurs des établissements !</p>
<p>En cas de perte de ce mot de passe "webmestre", suivre <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__identifiants#toggle_oubli_mdp">la procédure décrite dans la documentation</a></span>.</p>
<?php endif; ?>

<?php if($PROFIL=='partenaire'): ?>
<p class="astuce">Un "partenaire" est ici une collectivité qui a signé une convention avec <em>Sésamath</em> pour l'usage d'un connecteur ENT sur cet hébergement.</p>
<p>Si vraiment vous êtes dans cette situation, alors prenez contact avec <em>Sésamath</em>...</p><?php /* Les personnes concernées se comptent sur le doigt de la main et sauront nous trouver si besoin ! */ ?>
<?php endif; ?>

<hr />

<div class="hc"><a href="./index.php?base=<?php echo $BASE ?>&amp;<?php echo $PROFIL ?>">[ Retour en page d'accueil ]</a></div>
