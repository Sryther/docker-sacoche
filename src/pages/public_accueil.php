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
$TITRE = ''; // Pas de titre pour que le logo s'affiche à la place

// Alerte non déconnexion de l'ENT si deconnexion de SACoche depuis un compte connecté via un ENT
if( (isset($_COOKIE[COOKIE_STRUCTURE])) && (isset($_COOKIE[COOKIE_AUTHMODE])) && ($_COOKIE[COOKIE_AUTHMODE]!='normal') )
{
  echo'<div class="danger">Vous n\'êtes pas déconnecté du service d\'authentification externe, on peut donc revenir dans <em>SACoche</em> sans s\'identifier !<br />Fermez votre navigateur ou <a href="index.php?page=public_sso_logout&amp;base='.$_COOKIE[COOKIE_STRUCTURE].'">déconnectez-vous de ce service</a>.</div>'.NL.'<hr />'.NL;
}

// Supprimer le cookie avec le mode d'identification, servant à une reconnexion SSO, devenu inutile puisque déconnecté à présent.
if(isset($_COOKIE[COOKIE_AUTHMODE]))
{
  Cookie::effacer(COOKIE_AUTHMODE);
}

// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations.
// Un UAI ou un id de base peut être transmis, mais on peut aussi retrouver l'information dans un cookie.
$BASE = 0;
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  // Lecture d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
  $BASE = (isset($_COOKIE[COOKIE_STRUCTURE])) ? Clean::entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ;
  // Test si id d'établissement transmis dans l'URL ; historiquement "id" si connexion normale et "base" si connexion SSO
  $BASE = (isset($_GET['id']))   ? Clean::entier($_GET['id'])   : $BASE ;
  $BASE = (isset($_GET['base'])) ? Clean::entier($_GET['base']) : $BASE ;
  // Test si UAI d'établissement transmis dans l'URL
  $BASE = (isset($_GET['uai'])) ? DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_id_base_for_UAI(Clean::uai($_GET['uai'])) : $BASE ;
}

// Test si affichage d'un formulaire spécial, autres liens de bascule
$partenaire_possible = ( IS_HEBERGEMENT_SESAMATH && (HEBERGEUR_INSTALLATION=='multi-structures') ) ? TRUE : FALSE ;
if(isset($_GET['webmestre']))
{
  $profil = 'webmestre';
  $h1_identification = '<span style="color:#C00">Accès webmestre</span>';
  $liens_autres_profils = '<a class="anti_h2" href="index.php">profils établissement</a>';
  $liens_autres_profils.= ($partenaire_possible) ? '<a class="anti_h2" href="index.php?partenaire">accès partenaire</a>' : '' ;
}
elseif( isset($_GET['partenaire']) && $partenaire_possible )
{
  $profil = 'partenaire';
  $h1_identification = '<span style="color:#C00">Accès partenaire</span>';
  $liens_autres_profils = '<a class="anti_h2" href="index.php">profils établissement</a><a class="anti_h2" href="index.php?webmestre">accès webmestre</a>';
}
elseif(isset($_GET['developpeur']))
{
  $profil = 'developpeur';
  $h1_identification = '<span style="color:#C00">Accès développeur</span>';
  $liens_autres_profils = '' ;
}
else
{
  $profil = 'structure';
  $h1_identification = 'Identification';
  $liens_autres_profils = '<a class="anti_h2" href="index.php?webmestre">accès webmestre</a>';
  $liens_autres_profils.= ($partenaire_possible) ? '<a class="anti_h2" href="index.php?partenaire">accès partenaire</a>' : '' ;
}

// Paramètres annexes éventuellement passés en GET
$get_mode  = (isset($_GET['mode']))  ? Clean::id(    $_GET['mode']) : 'ent' ;
$get_login = (isset($_GET['login'])) ? Clean::login($_GET['login']) : '' ;

// Javascript
Layout::add( 'js_inline_before' , 'var    LOGIN_LONGUEUR_MAX = '.   LOGIN_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var GET_MODE  = "'.$get_mode.'";' );
Layout::add( 'js_inline_before' , 'var GET_LOGIN = "'.$get_login.'";' );

// Protection contre les attaques par force brute des robots (piratage compte ou envoi intempestif de courriels)
$_SESSION['FORCEBRUTE'][$PAGE] = array(
  'TIME'  => $_SERVER['REQUEST_TIME'] ,
  'DELAI' => 2, // en secondes, est ensuite incrémenté en cas d'erreur
);
?>

<h1 class="identification"><?php echo $h1_identification ?><?php echo $liens_autres_profils ?></h1>
<form id="form_auth" action="#" method="post">
  <fieldset id="fieldset_auth">
  <input id="f_base" name="f_base" type="hidden" value="<?php echo $BASE ?>" />
  <input id="f_profil" name="f_profil" type="hidden" value="<?php echo $profil ?>" />
  <label id="ajax_msg" class="loader">Chargement en cours...</label>
  </fieldset>
</form>

<hr />

<h1 class="hebergement">Hébergement</h1>
<ul class="puce">
  <li><em>SACoche</em> peut être téléchargé et installé sur différents serveurs.</li>
  <li>Cette installation (<?php echo (HEBERGEUR_INSTALLATION=='mono-structure') ? HEBERGEUR_INSTALLATION : DB_WEBMESTRE_PUBLIC::DB_compter_structure() ; ?>) a été effectuée par : <?php echo (HEBERGEUR_ADRESSE_SITE) ? '<a target="_blank" rel="noopener noreferrer" href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.html(HEBERGEUR_DENOMINATION).'</a>' : html(HEBERGEUR_DENOMINATION); ?> (<?php echo HtmlMail::to(WEBMESTRE_COURRIEL,'SACoche - Remplacer ce texte par l\'objet de votre message !!!','webmestre','Attention ! Si vous êtes élève, parent, professeur ou directeur, alors il ne faut pas contacter le webmestre du serveur, mais l\'administrateur de votre établissement qui a créé les comptes utilisateurs.'); ?>).</li>
  <li><a target="_blank" rel="noopener noreferrer" href="<?php echo SERVEUR_CNIL ?>">Informations CNIL</a>. Déclaration <?php echo intval(CNIL_NUMERO) ? 'n°'.CNIL_NUMERO : 'non renseignée' ; ?>.</li>
</ul>

<hr />

<h1 class="informations">Informations</h1>
<ul class="puce">
  <li><em>SACoche</em> est un logiciel <span class="b">gratuit</span>, <span class="b">libre</span>, développé avec le soutien de <a target="_blank" rel="noopener noreferrer" href="<?php echo SERVEUR_ASSO ?>"><em>Sésamath</em></a>.</li>
  <li class="b">Consulter <a href="<?php echo SERVEUR_PROJET ?>" target="_blank" rel="noopener noreferrer">le site officiel du projet <em>SACoche</em></a> pour tout renseignement.</li>
  <li>Version installée <em><?php echo VERSION_PROG ?></em>.<label id="ajax_version"></label></li>
</ul>
