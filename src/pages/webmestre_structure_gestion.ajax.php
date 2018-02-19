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

$action           = (isset($_POST['f_action']))           ? Clean::texte($_POST['f_action'])              : '';
$acces            = (isset($_POST['f_acces']))            ? Clean::texte($_POST['f_acces'])               : '';
$check            = (isset($_POST['f_check']))            ? Clean::entier($_POST['f_check'])              : 0;
$base_id          = (isset($_POST['f_base_id']))          ? Clean::entier($_POST['f_base_id'])            : 0;
$listing_base_id  = (isset($_POST['f_listing_id']))       ? $_POST['f_listing_id']                        : '';
$geo_id           = (isset($_POST['f_geo']))              ? Clean::entier($_POST['f_geo'])                : 0;
$localisation     = (isset($_POST['f_localisation']))     ? Clean::texte($_POST['f_localisation'])        : '';
$denomination     = (isset($_POST['f_denomination']))     ? Clean::texte($_POST['f_denomination'])        : '';
$uai              = (isset($_POST['f_uai']))              ? Clean::uai($_POST['f_uai'])                   : '';
$contact_nom      = (isset($_POST['f_contact_nom']))      ? Clean::nom($_POST['f_contact_nom'])           : '';
$contact_prenom   = (isset($_POST['f_contact_prenom']))   ? Clean::prenom($_POST['f_contact_prenom'])     : '';
$contact_courriel = (isset($_POST['f_contact_courriel'])) ? Clean::courriel($_POST['f_contact_courriel']) : '';
$courriel_envoi   = (isset($_POST['f_courriel_envoi']))   ? Clean::entier($_POST['f_courriel_envoi'])     : 0;
$courriel_copie   = (isset($_POST['f_courriel_copie']))   ? Clean::entier($_POST['f_courriel_copie'])     : 0;
$date_fr          = (isset($_POST['f_date_fr']))          ? Clean::date_fr($_POST['f_date_fr'])           : '' ;
$admin_id         = (isset($_POST['f_admin_id']))         ? Clean::entier($_POST['f_admin_id'])           : 0;

// On récupère les zones géographiques pour 2 raisons :
// => vérifier que l'identifiant transmis est cohérent
// => pouvoir retourner la cellule correspondante du tableau
if( ($action!='supprimer') && ($action!='lister_admin') && ($action!='initialiser_mdp') )
{
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_zones();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_geo[$DB_ROW['geo_id']] = array( 'ordre'=>$DB_ROW['geo_ordre'] , 'nom'=>$DB_ROW['geo_nom'] );
  }
}

$geo_ordre_longueur = 6;
$geo_ordre_format   = '%0'.$geo_ordre_longueur.'u';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouvel établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && isset($tab_geo[$geo_id]) && $localisation && $denomination && ( !$uai || Outil::tester_UAI($uai) ) && $contact_nom && $contact_prenom && $contact_courriel && $date_fr )
{
  // Vérifier que le n° de base est disponible (si imposé)
  if($base_id)
  {
    $structure_denomination = DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_Id($base_id);
    if($structure_denomination!==NULL)
    {
      Json::end( FALSE , 'Identifiant déjà utilisé ('.html($structure_denomination).') !' );
    }
  }
  // Vérifier que le n°UAI est disponible
  if($uai)
  {
    if( DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_UAI($uai) )
    {
      Json::end( FALSE , 'Numéro UAI '.$uai.' déjà utilisé !' );
    }
  }
  // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
  list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($contact_courriel);
  if(!$is_domaine_valide)
  {
    Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
  }
  // Insérer l'enregistrement dans la base du webmestre
  // Créer le fichier de connexion de la base de données de la structure
  // Créer la base de données de la structure
  // Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
  $base_id = Webmestre::ajouter_structure( $base_id , $geo_id , $uai , $localisation , $denomination , $contact_nom , $contact_prenom , $contact_courriel );
  // Créer les dossiers de fichiers temporaires par établissement
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier)
  {
    FileSystem::creer_dossier($dossier.$base_id);
    FileSystem::ecrire_fichier_index($dossier.$base_id);
  }
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  // Lancer les requêtes pour créer et remplir les tables
  DB_STRUCTURE_COMMUN::DB_creer_remplir_tables_structure();
  // Il est arrivé que la fonction DB_modifier_parametres() retourne une erreur disant que la table n'existe pas.
  // Comme si les requêtes de DB_creer_remplir_tables_structure() étaient en cache, et pas encore toutes passées (parce qu'au final, quand on va voir la base, toutes les tables sont bien là).
  // Est-ce que c'est possible au vu du fonctionnement de la classe de connexion ? Et, bien sûr, y a-t-il quelque chose à faire pour éviter ce problème ?
  // En attendant une réponse de SebR, j'ai mis ce sleep(1)... sans trop savoir si cela pouvait aider...
  @sleep(1);
  // Personnaliser certains paramètres de la structure
  $tab_parametres = array();
  $tab_parametres['version_base']               = VERSION_BASE_STRUCTURE;
  $tab_parametres['webmestre_uai']              = $uai;
  $tab_parametres['webmestre_denomination']     = $denomination;
  $tab_parametres['etablissement_denomination'] = $denomination;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // Insérer le compte administrateur dans la base de cette structure
  $password = Outil::fabriquer_mdp();
  $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur( 0 /*user_sconet_id*/ , 0 /*user_sconet_elenoet*/ , '' /*reference*/ , 'ADM' , 'I' /*user_genre*/ , $contact_nom , $contact_prenom , NULL /*user_naissance_date*/ , $contact_courriel , 'user' /*user_email_origine*/ , 'admin' /*login*/ , Outil::crypter_mdp($password) );
  // Pour les admins, abonnement obligatoire aux contacts effectués depuis la page d'authentification
  DB_STRUCTURE_NOTIFICATION::DB_ajouter_abonnement( $user_id , 'contact_externe' , 'accueil' );
  // Envoyer un courriel au contact et / ou une copie du courriel au webmestre
  $courriel_contenu = ( $courriel_envoi || $courriel_copie ) ? Webmestre::contenu_courriel_inscription( $base_id , $denomination , $contact_nom , $contact_prenom , 'admin' , $password , URL_DIR_SACOCHE ) : '' ;
  $courriel_titre   = ( $courriel_envoi || $courriel_copie ) ? 'Création compte - Inscription n°'.$base_id : '' ;
  if($courriel_envoi)
  {
    $courriel_bilan = Sesamail::mail( $contact_courriel , $courriel_titre , $courriel_contenu );
    if(!$courriel_bilan)
    {
      Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
    }
  }
  if($courriel_copie)
  {
    $introduction = '================================================================================'."\r\n".'Copie pour information du courriel adressé à '.$contact_courriel."\r\n".'================================================================================'."\r\n\r\n";
    $courriel_bilan = Sesamail::mail( WEBMESTRE_COURRIEL , $courriel_titre , $introduction.$courriel_contenu );
    if(!$courriel_bilan)
    {
      Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
    }
  }
  // On affiche le retour
  Json::add_str('<tr id="id_'.$base_id.'" class="new">');
  Json::add_str(  '<td class="nu"><a href="#id_0"><img class="bloquer" src="./_img/etat/acces_oui.png" title="Bloquer cet établissement." /></a></td>');
  Json::add_str(  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td>');
  Json::add_str(  '<td class="label">'.$base_id.'</td>');
  Json::add_str(  '<td class="label"><i>'.sprintf($geo_ordre_format,$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'<br />'.html($localisation).'</td>');
  Json::add_str(  '<td class="label">'.html($denomination).'<br />'.html($uai).'</td>');
  Json::add_str(  '<td class="label"><span>'.html($contact_nom).'</span> <span>'.html($contact_prenom).'</span><div>'.html($contact_courriel).'</div></td>');
  Json::add_str(  '<td class="label">'.$date_fr.'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cet établissement."></q>');
  Json::add_str(    '<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cet établissement."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un établissement existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $base_id && isset($tab_geo[$geo_id]) && $localisation && $denomination && $contact_nom && $contact_prenom && $contact_courriel && $date_fr )
{
    // Vérifier que le n°UAI est disponible
  if($uai)
  {
    if( DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_UAI($uai,$base_id) )
    {
      Json::end( FALSE , 'Numéro UAI '.$uai.' déjà utilisé !' );
    }
  }
  // On met à jour l'enregistrement dans la base du webmestre
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_structure( $base_id , $geo_id , $uai , $localisation , $denomination , $contact_nom , $contact_prenom , $contact_courriel );
  // On met à jour l'enregistrement dans la base de la structure
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  $tab_parametres = array();
  $tab_parametres['webmestre_uai']          = $uai;
  $tab_parametres['webmestre_denomination'] = $denomination;
  DB_STRUCTURE_PARAMETRE::DB_modifier_parametres($tab_parametres);
  // On affiche le retour
  $img_acces = ($acces=='bloquer') ? '<img class="bloquer" src="./_img/etat/acces_oui.png" title="Bloquer cet établissement." />' : '<img class="debloquer" src="./_img/etat/acces_non.png" title="Débloquer cet établissement." />' ;
  $checked = ($check) ? ' checked' : '' ;
  Json::add_str('<td class="nu"><a href="#id_0">'.$img_acces.'</a></td>');
  Json::add_str('<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'"'.$checked.' /></td>');
  Json::add_str('<td class="label">'.$base_id.'</td>');
  Json::add_str('<td class="label"><i>'.sprintf($geo_ordre_format,$tab_geo[$geo_id]['ordre']).'</i>'.html($tab_geo[$geo_id]['nom']).'<br />'.html($localisation).'</td>');
  Json::add_str('<td class="label">'.html($denomination).'<br />'.html($uai).'</td>');
  Json::add_str('<td class="label"><span>'.html($contact_nom).'</span> <span>'.html($contact_prenom).'</span><div>'.html($contact_courriel).'</div></td>');
  Json::add_str('<td class="label">'.$date_fr.'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cet établissement."></q>');
  Json::add_str(  '<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer cet établissement."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger la liste des administrateurs d'un établissement pour remplir un select (liste d'options)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='lister_admin') && $base_id )
{
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  Json::end( TRUE , HtmlForm::afficher_select(DB_STRUCTURE_WEBMESTRE::DB_OPT_administrateurs_etabl() , FALSE /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le mdp d'un administrateur et envoyer les identifiants par courriel au contact
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='initialiser_mdp') && $base_id && $admin_id )
{
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  // Informations sur la structure, notamment coordonnées du contact.
  $DB_ROW = DB_WEBMESTRE_WEBMESTRE::DB_recuperer_structure_by_Id($base_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Structure introuvable !' );
  }
  $denomination     = $DB_ROW['structure_denomination'];
  $contact_nom      = $DB_ROW['structure_contact_nom'];
  $contact_prenom   = $DB_ROW['structure_contact_prenom'];
  $contact_courriel = $DB_ROW['structure_contact_courriel'];
  // Informations sur l'admin : nom / prénom / login.
  $DB_ROW = DB_STRUCTURE_WEBMESTRE::DB_recuperer_admin_identite($admin_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Administrateur introuvable !' );
  }
  $admin_nom    = $DB_ROW['user_nom'];
  $admin_prenom = $DB_ROW['user_prenom'];
  $admin_login  = $DB_ROW['user_login'];
  // Générer un nouveau mdp de l'admin
  $admin_password = Outil::fabriquer_mdp();
  DB_STRUCTURE_WEBMESTRE::DB_modifier_admin_mdp($admin_id,Outil::crypter_mdp($admin_password));
  // Envoyer un courriel au contact et éventuellement une copie du courriel au webmestre
  $courriel_contenu = Webmestre::contenu_courriel_nouveau_mdp( $base_id , $denomination , $contact_nom , $contact_prenom , $admin_nom , $admin_prenom , $admin_login , $admin_password , URL_DIR_SACOCHE );
  $courriel_titre   = 'Modification mdp administrateur - Inscription n°'.$base_id;
  $courriel_bilan = Sesamail::mail( $contact_courriel , $courriel_titre , $courriel_contenu );
  if(!$courriel_bilan)
  {
    Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
  }
  if($courriel_copie)
  {
    $introduction = '================================================================================'."\r\n".'Copie pour information du courriel adressé à '.$contact_courriel."\r\n".'================================================================================'."\r\n\r\n";
    $courriel_bilan = Sesamail::mail( WEBMESTRE_COURRIEL , $courriel_titre , $introduction.$courriel_contenu );
    if(!$courriel_bilan)
    {
      Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
    }
  }
  // On affiche le retour
  Json::add_str('Le mot de passe de '.html($admin_prenom.' '.$admin_nom).',<br />administrateur de l\'établissement '.html($denomination).',<br />vient d\'être réinitialisé.<br /><br />');
  Json::add_str('Les nouveaux identifiants ont été envoyés au contact '.html($contact_prenom).' '.html($contact_nom).',<br />à son adresse de courriel '.html($contact_courriel).'.');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une structure existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $base_id )
{
  Webmestre::supprimer_multi_structure($base_id);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer plusieurs structures existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $listing_base_id )
{
  $tab_base_id = array_filter( Clean::map('entier', explode(',',$listing_base_id) ) , 'positif' );
  foreach($tab_base_id as $base_id)
  {
    Webmestre::supprimer_multi_structure($base_id);
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquer les accès à une structure
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='bloquer') && $base_id )
{
  LockAcces::bloquer_application( $_SESSION['USER_PROFIL_TYPE'] , $base_id , 'Action ciblée ; contacter le webmestre pour obtenir des précisions.' );
  Json::end( TRUE , '<img class="debloquer" src="./_img/etat/acces_non.png" title="Débloquer cet établissement." />' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Débloquer les accès à une structure
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='debloquer') && $base_id )
{
  LockAcces::debloquer_application( $_SESSION['USER_PROFIL_TYPE'] , $base_id );
  Json::end( TRUE , '<img class="bloquer" src="./_img/etat/acces_oui.png" title="Bloquer cet établissement." />' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
