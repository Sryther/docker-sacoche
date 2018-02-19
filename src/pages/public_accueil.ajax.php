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

$action     = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])      : '';
$BASE       = (isset($_POST['f_base']))       ? Clean::entier($_POST['f_base'])       : 0 ;
$profil     = (isset($_POST['f_profil']))     ? Clean::lettres($_POST['f_profil'])    : '';  // structure | webmestre | partenaire
$login      = (isset($_POST['f_login']))      ? Clean::login($_POST['f_login'])       : '';
$password   = (isset($_POST['f_password']))   ? Clean::password($_POST['f_password']) : '';
$partenaire = (isset($_POST['f_partenaire'])) ? Clean::entier($_POST['f_partenaire']) : 0 ;
$courriel   = (isset($_POST['f_courriel']))   ? Clean::courriel($_POST['f_courriel']) : '';

/*
 * Afficher le formulaire de choix des établissements (installation multi-structures)
 */
function afficher_formulaire_etablissement( $BASE , $profil )
{
  $affichage = '';
  $options_structures = HtmlForm::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , FALSE /*select_nom*/ , FALSE /*option_first*/ , $BASE /*selection*/ , 'zones_geo' /*optgroup*/ );
  $affichage .= '<label class="tab" for="f_base">Établissement :</label><select id="f_base" name="f_base" tabindex="1" class="t9">'.$options_structures.'</select><br />'.NL;
  $affichage .= '<span class="tab"></span><button id="f_choisir" type="button" tabindex="2" class="valider">Choisir cet établissement.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
  $affichage .= '<input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" />'.NL;
  return $affichage;
}

/*
 * Afficher le nom d'un établissement, avec l'icône pour changer de structure si installation multi-structures
 */
function afficher_nom_etablissement( $BASE , $denomination )
{
  $changer = (HEBERGEUR_INSTALLATION=='multi-structures') ? ' - <a id="f_changer" href="#">changer</a>' : '' ;
  $affichage = '<label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="'.$BASE.'" /><em>'.html($denomination).'</em>'.$changer.'<br />'.NL;
  return $affichage;
}

/*
 * Afficher la partie du formulaire spécialement dédiée à l'identification :
 * - pour le webmestre -> seulement la saisie du mot de passe pour le webmestre
 * - pour un partenaire -> un select avec la liste des partenaires et la saisie du mot de passe
 * - si le mode de connexion est normal -> saisie login & mot de passe SACoche
 * - si l'établissement est configuré pour un autre mode de connexion -> au choix [ saisie login & mot de passe SACoche ] ou [ utilisation de l'authentification externe ]
 */
function afficher_formulaire_identification( $profil , $mode='normal' , $nom='' )
{
  $get_mode  = (isset($_POST['get_mode']))  ? Clean::id(   $_POST['get_mode'])  : 'ent' ;
  $get_login = (isset($_POST['get_login'])) ? Clean::login($_POST['get_login']) : '' ;
  $affichage = '';
  if($profil=='webmestre')
  {
    $affichage .= '<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="30" maxlength="'.PASSWORD_LONGUEUR_MAX.'" type="password" value="" tabindex="1" autocomplete="off" /><br />'.NL;
    $affichage .= '<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="'.$profil.'" /><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="2" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    $affichage .= '<span class="tab"></span><a id="lien_lost" href="#webmestre">[ Identifiants perdus ]</a>'.NL;
  }
  elseif($profil=='partenaire')
  {
    // Lecture d'un cookie sur le poste client servant à retenir le dernier partenariat sélectionné si identification avec succès
    $selection = (isset($_COOKIE[COOKIE_PARTENAIRE])) ? Clean::entier($_COOKIE[COOKIE_PARTENAIRE]) : FALSE ;
    $options_partenaires = HtmlForm::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_partenaires_conventionnes() , FALSE /*select_nom*/ , '' /*option_first*/ , $selection , '' /*optgroup*/ );
    $affichage .= '<label class="tab" for="f_partenaire">Partenariat :</label><select id="f_partenaire" name="f_partenaire" tabindex="1" class="t9">'.$options_partenaires.'</select><br />'.NL;
    $affichage .= '<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="30" maxlength="'.PASSWORD_LONGUEUR_MAX.'" type="password" value="" tabindex="2" autocomplete="off" /><br />'.NL;
    $affichage .= '<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="3" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    $affichage .= '<span class="tab"></span><a id="lien_lost" href="#partenaire">[ Identifiants perdus ]</a>'.NL;
  }
  elseif($profil=='developpeur')
  {
    $affichage .= '<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="30" maxlength="'.PASSWORD_LONGUEUR_MAX.'" type="password" value="" tabindex="1" autocomplete="off" /><br />'.NL;
    $affichage .= '<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="'.$profil.'" /><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="2" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
  }
  elseif($mode=='normal')
  {
    $affichage .= '<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="30" maxlength="'.LOGIN_LONGUEUR_MAX.'" type="text" value="'.html($get_login).'" tabindex="2" autocomplete="off" /><br />'.NL;
    $affichage .= '<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="30" maxlength="'.PASSWORD_LONGUEUR_MAX.'" type="password" value="" tabindex="3" autocomplete="off" /><br />'.NL;
    $affichage .= '<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="structure" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    $affichage .= '<span class="tab"></span><a id="lien_lost" href="#structure">[ Identifiants perdus ]</a> <a id="contact_admin" href="#contact_admin">[ Contact établissement ]</a>'.NL;
  }
  else
  {
    $check_normal  = ( $get_mode == 'normal' ) ? ' checked' : '' ;
    $check_externe = ( $get_mode == 'ent' )    ? ' checked' : '' ;
    $class_normal  = ( $get_mode == 'normal' ) ? '' : ' class="hide"' ;
    $affichage .= '<label class="tab">Mode de connexion :</label>';
    $affichage .=   '<label for="f_mode_normal"><input type="radio" id="f_mode_normal" name="f_mode" value="normal"'.$check_normal.' /> formulaire <em>SACoche</em></label>&nbsp;&nbsp;&nbsp;';
    $affichage .=   '<label for="f_mode_'.$mode.'"><input type="radio" id="f_mode_'.$mode.'" name="f_mode" value="'.$mode.'"'.$check_externe.' /> authentification extérieure <em>'.html($mode.'-'.$nom).'</em></label><br />'.NL;
    $affichage .= '<fieldset id="fieldset_normal"'.$class_normal.'>'.NL;
    $affichage .= '<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="30" maxlength="'.LOGIN_LONGUEUR_MAX.'" type="text" value="'.html($get_login).'" tabindex="2" autocomplete="off" /><br />'.NL;
    $affichage .= '<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="30" maxlength="'.PASSWORD_LONGUEUR_MAX.'" type="password" value="" tabindex="3" autocomplete="off" /><br />'.NL;
    $affichage .= '</fieldset>'.NL;
    $affichage .= '<span class="tab"></span><input id="f_profil" name="f_profil" type="hidden" value="structure" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    $affichage .= '<span class="tab"></span><a id="lien_lost"'.$class_normal.' href="#structure">[ Identifiants perdus ]</a> <a id="contact_admin" href="#contact_admin">[ Contact établissement ]</a>'.NL;
  }
  return $affichage;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher la dernière version disponible
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='tester_version')
{
  $version_last = Outil::recuperer_numero_derniere_version();
  if(!preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[a-z]?$#', $version_last))
  {
    Json::add_tab( array(
      'class' => 'alerte' ,
      'texte' => $version_last ,
      'after' => '' ,
    ) );
  }
  elseif($version_last==VERSION_PROG)
  {
    Json::add_tab( array(
      'class' => 'valide' ,
      'texte' => 'Cette version est la dernière disponible.' ,
      'after' => '' ,
    ) );
  }
  else
  {
    // Compte approximativement le nombre de mois qui sépare ces 2 versions (sans s'occuper des jours).
    $class = ( (substr($version_last,0,4)-substr(VERSION_PROG,0,4))*12 - substr($version_last,5,2) + substr(VERSION_PROG,5,2) < 12 ) ? '' : ' class="probleme"' ;
    Json::add_tab( array(
      'class' => 'alerte' ,
      'texte' => '<span'.$class.'>Dernière version disponible <em>'.$version_last.'</em>.</span>' ,
      'after' => ' &rarr; <a target="_blank" rel="noopener noreferrer" href="'.SERVEUR_NEWS.'">Nouveautés.</a>' ,
    ) );
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger un formulaire d'identification
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Charger le formulaire pour le webmestre d'un serveur, ou un développeur

if( ($action=='initialiser') && ( ($profil=='webmestre') || ($profil=='developpeur') ) )
{
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    // Mettre à jour la base du webmestre si besoin
    $version_base_webmestre = DB_WEBMESTRE_MAJ_BASE::DB_version_base();
    if($version_base_webmestre != VERSION_BASE_WEBMESTRE)
    {
      DB_WEBMESTRE_MAJ_BASE::DB_maj_base($version_base_webmestre);
    }
  }
  Json::end( TRUE , afficher_formulaire_identification($profil,'normal') );
}

// Charger le formulaire pour un partenaire

if( ($action=='initialiser') && ($profil=='partenaire') )
{
  Json::end( TRUE , afficher_formulaire_identification($profil,'normal') );
}

// Charger le formulaire pour un établissement donné (installation mono-structure)

if( ($action=='initialiser') && (HEBERGEUR_INSTALLATION=='mono-structure') && $profil )
{
  // Mettre à jour la base si nécessaire
  DBextra::maj_base_structure_si_besoin($BASE);
  // Requête pour récupérer la dénomination et le mode de connexion
  $DB_TAB = DB_STRUCTURE_PARAMETRE::DB_lister_parametres('"webmestre_denomination","connexion_mode","connexion_nom"');
  foreach($DB_TAB as $DB_ROW)
  {
    ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
  }
  if(isset($webmestre_denomination,$connexion_mode,$connexion_nom)==FALSE)
  {
    Json::end( FALSE , 'Base de l\'établissement incomplète !' );
  }
  Json::end( TRUE , afficher_nom_etablissement($BASE=0,$webmestre_denomination) . afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

// Charger le formulaire de choix des établissements (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE==0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='choisir') && $profil )
{
  Json::end( TRUE , afficher_formulaire_etablissement($BASE,$profil) );
}

// Charger le formulaire pour un établissement donné (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE>0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='charger') && $profil )
{
  // Une première requête sur SACOCHE_WEBMESTRE_BD_NAME pour vérifier que la structure est référencée
  $structure_denomination = DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_nom_for_Id($BASE);
  if($structure_denomination===NULL)
  {
    // Sans doute un établissement supprimé, mais le cookie est encore là
    Cookie::effacer(COOKIE_STRUCTURE);
    Json::end( FALSE , 'Établissement non trouvé dans la base d\'administration !' );
  }
  // Mettre à jour la base si nécessaire
  DBextra::charger_parametres_mysql_supplementaires($BASE);
  DBextra::maj_base_structure_si_besoin($BASE);
  // Une deuxième requête sur SACOCHE_STRUCTURE_BD_NAME pour savoir si le mode de connexion est SSO ou pas
  $DB_TAB = DB_STRUCTURE_PARAMETRE::DB_lister_parametres('"connexion_mode","connexion_nom"');
  foreach($DB_TAB as $DB_ROW)
  {
    ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
  }
  if(isset($connexion_mode,$connexion_nom)==FALSE)
  {
    Json::end( FALSE , 'Base de l\'établissement incomplète !' );
  }
  Json::end( TRUE , afficher_nom_etablissement($BASE,$structure_denomination) . afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traiter une demande d'identification
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function adresse_redirection_apres_authentification($BASE)
{
  if( empty($_SESSION['MEMO_GET']) || !isset($_SESSION['MEMO_GET']['page']) )
  {
    $_SESSION['MEMO_GET']['page'] = 'compte_accueil';
  }
  // Le numéro de la base est transmis en GET simplement pour avoir une trace dans les logs "accès www" de l'établissement concerné en cas d'erreur MySQL (dans les logs "erreurs php", où cette info n'apparaît pas...).
  // Cela demeure peu pratique dans la mesure où il faut d'abord essayer de trouver l'IP correspondante via une recherche sur l'heure (pour faire le lien "erreurs php"->"accès www").
  // Il y aurait mieux à faire avec $_CONST['POOL'][...]['DEBUG'] à 'file' car cette infos est alors logguée...
  $tab_get = ($BASE) ? array('log_id='.$BASE) : array() ;
  foreach($_SESSION['MEMO_GET'] as $key => $val)
  {
    $tab_get[] = $key.'='.urlencode($val);
  }
  unset($_SESSION['MEMO_GET']);
  return URL_DIR_SACOCHE.'index.php?'.implode('&',$tab_get);
}

if($action=='identifier')
{
  // Protection contre les attaques par force brute des robots (piratage compte ou envoi intempestif de courriels)
  if(!isset($_SESSION['FORCEBRUTE'][$PAGE]))
  {
    Json::end( FALSE , 'Session perdue ou absence de cookie : merci d\'actualiser la page.' );
  }
  else if( $_SERVER['REQUEST_TIME'] - $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] < $_SESSION['FORCEBRUTE'][$PAGE]['DELAI'] )
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , 'Sécurité : patienter '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s avant une nouvelle tentative.' );
  }
  // initialisation
  $auth_SUCCESS = FALSE;
  $auth_DATA = 'Erreur avec les données transmises !';
  // 1/4 Pour un utilisateur d'établissement, y compris un administrateur
  if( ($profil=='structure') && ($login!='') && ($password!='') )
  {
    list( $auth_SUCCESS , $auth_DATA ) = SessionUser::tester_authentification_utilisateur( $BASE , $login , $password , 'normal' /*mode_connection*/ );
    if($auth_SUCCESS===TRUE)
    {
      SessionUser::initialiser_utilisateur( $BASE , $auth_DATA );
    }
  }
  // 2/4 Pour le webmestre d'un serveur
  else if( ($profil=='webmestre') && ($login=='webmestre') && ($password!='') )
  {
    list( $auth_SUCCESS , $auth_DATA ) = SessionUser::tester_authentification_webmestre($password);
    if($auth_SUCCESS===TRUE)
    {
      SessionUser::initialiser_webmestre();
      // On allège le fichier de logs si nécessaire
      SACocheLog::alleger(0);
    }
  }
  // 3/4 Pour un développeur
  else if( ($profil=='developpeur') && ($login=='developpeur') && ($password!='') )
  {
    list( $auth_SUCCESS , $auth_DATA ) = SessionUser::tester_authentification_developpeur($password);
    if($auth_SUCCESS===TRUE)
    {
      SessionUser::initialiser_developpeur();
    }
  }
  // 4/4 Pour un partenaire conventionné (serveur Sésamath uniquement)
  else if( ($profil=='partenaire') && ($partenaire!=0) && ($password!='') && IS_HEBERGEMENT_SESAMATH && (HEBERGEUR_INSTALLATION=='multi-structures') )
  {
    list( $auth_SUCCESS , $auth_DATA ) = SessionUser::tester_authentification_partenaire($partenaire,$password);
    if($auth_SUCCESS===TRUE)
    {
      SessionUser::initialiser_partenaire( $auth_DATA );
    }
  }
  // Conclusion & Retour
  if($auth_SUCCESS===TRUE)
  {
    Json::end( TRUE , adresse_redirection_apres_authentification($BASE) );
  }
  else
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    Json::end( FALSE , $auth_DATA );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );
?>
