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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])   : '';
$profil = (isset($_POST['f_profil'])) ? Clean::lettres($_POST['f_profil']) : '';
// Avant c'était un tableau qui est transmis, mais à cause d'une limitation possible "suhosin" / "max input vars", on est passé à une concaténation en chaine...
$tab_user = (isset($_POST['f_user'])) ? ( (is_array($_POST['f_user'])) ? $_POST['f_user'] : explode(',',$_POST['f_user']) ) : array() ;
$tab_user = array_filter( Clean::map('entier',$tab_user) , 'positif' );
$tab_profils = array('eleves','parents','professeurs','directeurs');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialiser plusieurs noms d'utilisateurs élèves | parents | professeurs | directeurs
// Initialiser plusieurs mots de passe élèves | parents | professeurs | directeurs
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='generer_login')||($action=='generer_mdp')||($action=='forcer_mdp_birth')) && (in_array($profil,$tab_profils)) && count($tab_user) )
{
  $prefixe = ($profil!='parents') ? 'user_' : 'parent_' ;
  // Nom sans extension des fichiers de sortie
  $fnom = 'identifiants_'.$_SESSION['BASE'].'_'.$profil.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  // La classe n'est affichée que pour l'élève
  $avec_info = ($profil=='eleves') ? 'classe' : ( ($profil=='parents') ? 'enfant' : '' ) ;
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Initialiser plusieurs noms d'utilisateurs
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  if($action=='generer_login')
  {
    $tab_login = array();
    // Récupérer les données des utilisateurs concernés (besoin de le faire maintenant, on a besoin des infos pour générer le login)
    $listing_champs = ($profil!='parents')
      ? 'user_id, user_sconet_id, user_sconet_elenoet, user_reference, user_profil_sigle, user_nom, user_prenom, user_email'
      : 'parent.user_id AS parent_id, parent.user_sconet_id AS parent_sconet_id, parent.user_sconet_elenoet AS parent_sconet_elenoet, parent.user_reference AS parent_reference, parent.user_profil_sigle AS parent_profil_sigle, parent.user_nom AS parent_nom, parent.user_prenom AS parent_prenom, parent.user_email AS parent_email' ;
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles(implode(',',$tab_user),$listing_champs,$avec_info);
    // Mettre à jour les noms d'utilisateurs des utilisateurs concernés
    foreach($DB_TAB as $DB_ROW)
    {
      // Construire le login
      $login = Outil::fabriquer_login($DB_ROW[$prefixe.'prenom'] , $DB_ROW[$prefixe.'nom'] , $DB_ROW[$prefixe.'profil_sigle']);
      // Puis tester le login
      if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$DB_ROW[$prefixe.'id']) )
      {
        // Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
        $login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
      }
      DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $DB_ROW[$prefixe.'id'] , array(':login'=>$login) );
      $tab_login[$DB_ROW[$prefixe.'id']] = $login;
    }
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Initialiser plusieurs mots de passe
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  if($action=='generer_mdp')
  {
    $tab_password = array();
    // Récupérer les données des utilisateurs concernés (besoin de le faire maintenant, on a besoin des infos pour générer le mdp)
    $listing_champs = ($profil!='parents')
      ? 'user_id, user_sconet_id, user_sconet_elenoet, user_reference, user_profil_sigle, user_nom, user_prenom, user_email, user_login'
      : 'parent.user_id AS parent_id, parent.user_sconet_id AS parent_sconet_id, parent.user_sconet_elenoet AS parent_sconet_elenoet, parent.user_reference AS parent_reference, parent.user_profil_sigle AS parent_profil_sigle, parent.user_nom AS parent_nom, parent.user_prenom AS parent_prenom, parent.user_email AS parent_email, parent.user_login AS parent_login' ;
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles(implode(',',$tab_user),$listing_champs,$avec_info);
    // Mettre à jour les mots de passe des utilisateurs concernés
    foreach($DB_TAB as $DB_ROW)
    {
      $password = Outil::fabriquer_mdp($DB_ROW[$prefixe.'profil_sigle']);
      DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $DB_ROW[$prefixe.'id'] , array(':password'=>Outil::crypter_mdp($password)) );
      $tab_password[$DB_ROW[$prefixe.'id']] = $password;
    }
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Forcer plusieurs mots de passe avec la date de naissance
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  if($action=='forcer_mdp_birth')
  {
    if($profil!='eleves')
    {
      Json::end( FALSE , 'Fonctionnalité disponible uniquement pour les élèves !' );
    }
    $tab_password = array();
    // Récupérer les données des utilisateurs concernés (besoin de le faire maintenant, on a besoin des infos pour générer le mdp)
    $listing_champs = 'user_id, user_sconet_id, user_sconet_elenoet, user_reference, user_profil_sigle, user_nom, user_prenom, user_naissance_date, user_email, user_login';
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles(implode(',',$tab_user),$listing_champs,$avec_info);
    // Mettre à jour les mots de passe des utilisateurs concernés
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if($DB_ROW['user_naissance_date'])
      {
        $password = str_replace('/','',To::date_mysql_to_french($DB_ROW['user_naissance_date']));
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $DB_ROW[$prefixe.'id'] , array(':password'=>Outil::crypter_mdp($password)) );
        $tab_password[$DB_ROW[$prefixe.'id']] = $password;
      }
    }
    if(!count($tab_password))
    {
      Json::end( FALSE , 'Les mots de passe de ces élèves ne sont pas dans la base !' );
    }
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Générer une sortie csv (login ou mdp) (élève ou prof)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  $separateur = ';';
  $champ_nom = ($profil=='eleves') ? 'CLASSE' : 'PROFIL' ;
  $fcontenu = 'SCONET_ID'.$separateur.'SCONET_N°'.$separateur.'REFERENCE'.$separateur.'PROFIL'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'COURRIEL'.$separateur.'LOGIN'.$separateur.'MOT DE PASSE'.$separateur.'INFO'."\r\n\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    $login = ($action=='generer_login') ? $tab_login[$DB_ROW[$prefixe.'id']] : $DB_ROW[$prefixe.'login'] ;
    $mdp   = ($action=='generer_login') ? 'inchangé' : $tab_password[$DB_ROW[$prefixe.'id']] ;
    $info  = (isset($DB_ROW['info']))   ? $DB_ROW['info'] : '' ;
    $fcontenu .= '"'.$DB_ROW[$prefixe.'sconet_id'].'"'.$separateur.'"'.$DB_ROW[$prefixe.'sconet_elenoet'].'"'.$separateur.'"'.$DB_ROW[$prefixe.'reference'].'"'.$separateur.$DB_ROW[$prefixe.'profil_sigle'].$separateur.$DB_ROW[$prefixe.'nom'].$separateur.$DB_ROW[$prefixe.'prenom'].$separateur.$DB_ROW[$prefixe.'email'].$separateur.$login.$separateur.'"'.$mdp.'"'.$separateur.$info."\r\n";
  }
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.csv' , To::csv($fcontenu) );
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Générer une sortie pdf : classe fpdf + script étiquettes (login ou mdp) (élève ou prof)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  $font_size = ($profil!='parents') ? 11 : 10 ;
  $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>$font_size));
  $pdf -> AddFont('Arial','' ,'arial.php');
  $pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
  $pdf -> AddPage();
  $pdf -> SetFillColor(245,245,245);
  $pdf -> SetDrawColor(145,145,145);
  foreach($DB_TAB as $DB_ROW)
  {
    $ligne1 = $DB_ROW[$prefixe.'profil_sigle'] ;
    $ligne1.= (isset($DB_ROW['info']))   ? ' : '.Clean::perso_ucwords($DB_ROW['info']) : '' ;
    $ligne2 = $DB_ROW[$prefixe.'nom'].' '.$DB_ROW[$prefixe.'prenom'];
    $ligne3 = ($action=='generer_login') ? 'Utilisateur : '.$tab_login[$DB_ROW[$prefixe.'id']] : 'Utilisateur : '.$DB_ROW[$prefixe.'login'] ;
    $ligne4 = ($action=='generer_login') ? 'Mot de passe : inchangé' : 'Mot de passe : '.$tab_password[$DB_ROW[$prefixe.'id']] ;
    $pdf -> Add_Label(To::pdf($ligne1."\r\n".$ligne2."\r\n".$ligne3."\r\n".$ligne4));
  }
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.pdf' , $pdf );
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Affichage du résultat
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  Json::add_str('<ul class="puce">'.NL);
  Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_LOGINPASS.$fnom.'.pdf"><span class="file file_pdf">Nouveaux identifiants &rarr; Archiver / Imprimer (étiquettes <em>pdf</em>)</span></a></li>'.NL);
  Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?auth&amp;fichier='.$fnom.'.csv"><span class="file file_txt">Nouveaux identifiants &rarr; Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</span></a></li>'.NL);
  if($action=='generer_mdp')
  {
    Json::add_str('<li><label class="alerte">Les mots de passe, cryptés, ne seront plus accessibles ultérieurement !</label></li>'.NL);
  }
  Json::add_str('</ul>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV du contenu de la base des utilisateurs (login nom prénom de SACoche)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='user_export')
{
  $separateur = ';';
  // Récupérer les données des utilisateurs
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 1 /*only_actuels*/ , 'user_login,user_nom,user_prenom,user_profil_nom_court_singulier' /*liste_champs*/ , TRUE /*with_classe*/ );
  // Générer le csv
  $fcontenu_csv = 'LOGIN'.$separateur.'MOT DE PASSE'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'PROFIL (INFO)'.$separateur.'CLASSE (INFO)'."\r\n\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    $fcontenu_csv .= $DB_ROW['user_login'].$separateur.''.$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$DB_ROW['user_profil_nom_court_singulier'].$separateur.$DB_ROW['groupe_ref']."\r\n";
  }
  // On archive dans un fichier tableur (csv tabulé)
  $fnom = 'export_'.$_SESSION['BASE'].'_mdp_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($fcontenu_csv) );
  // Retour
  Json::end( TRUE , '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer le fichier exporté de la base SACoche (format <em>csv</em>).</span></a></li></ul>' );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Import CSV du contenu d'un fichier pour forcer les logins ou/et mdp utilisateurs de SACoche
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='import_loginmdp')
{
  // Récupération du fichier
  $fichier_nom = $action.'_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.txt' ;
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Pour récupérer les données des utilisateurs
  $tab_users_fichier           = array();
  $tab_users_fichier['login']  = array();
  $tab_users_fichier['mdp']    = array();
  $tab_users_fichier['nom']    = array();
  $tab_users_fichier['prenom'] = array();
  // Extraire les lignes du fichier
  $tab_lignes = FileSystem::extraire_lignes_csv(CHEMIN_DOSSIER_IMPORT.$fichier_nom);
  // Supprimer la 1e ligne
  unset($tab_lignes[0]);
  foreach ($tab_lignes as $tab_elements)
  {
    $tab_elements = array_slice($tab_elements,0,4);
    if(count($tab_elements)==4)
    {
      list($login,$mdp,$nom,$prenom) = $tab_elements;
      if( ($nom!='') && ($prenom!='') )
      {
        $tab_users_fichier['login'][]  = mb_substr(Clean::login($login),0,LOGIN_LONGUEUR_MAX);
        $tab_users_fichier['mdp'][]    = ($mdp!='inchangé') ? mb_substr(Clean::password($mdp),0,PASSWORD_LONGUEUR_MAX) : '';
        $tab_users_fichier['nom'][]    = Clean::nom($nom);
        $tab_users_fichier['prenom'][] = Clean::prenom($prenom);
      }
    }
  }
  // On trie
  array_multisort(
    $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
    $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
    $tab_users_fichier['login'],
    $tab_users_fichier['mdp']
  );
  // On récupère le contenu de la base pour comparer, y compris les professeurs afin de comparer avec leurs logins, et y compris les classes pour les étiquettes pdf
  $tab_users_base           = array();
  $tab_users_base['login']  = array();
  $tab_users_base['mdp']    = array();
  $tab_users_base['nom']    = array();
  $tab_users_base['prenom'] = array();
  $tab_users_base['info']   = array();
  $tab_parents = array();
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $listing_champs = 'user_id, user_login, user_password, user_nom, user_prenom, user_profil_type, user_profil_nom_court_singulier';
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , $listing_champs , TRUE /*with_classe*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['login'][$DB_ROW['user_id']]  = $DB_ROW['user_login'];
    $tab_users_base['mdp'][$DB_ROW['user_id']]    = $DB_ROW['user_password'];
    $tab_users_base['nom'][$DB_ROW['user_id']]    = $DB_ROW['user_nom'];
    $tab_users_base['prenom'][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
    $tab_users_base['info'][$DB_ROW['user_id']]   = ($DB_ROW['user_profil_type']=='eleve') ? 'élève '.$DB_ROW['groupe_nom'] : $DB_ROW['user_profil_nom_court_singulier'] ;
    if($DB_ROW['user_profil_type']=='parent')
    {
      $tab_parents[$DB_ROW['user_id']] = $DB_ROW['user_id'];
    }
  }
  // Une 2e requête pour récupérer classe et enfants des parents
  if(count($tab_parents))
  {
    $listing_parent_id = implode(',',$tab_parents);
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_info_enfants_par_parent($listing_parent_id);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_users_base['info'][$DB_ROW['parent_id']] .= ' '.$DB_ROW['info'] ;
    }
  }
  // Observer le contenu du fichier et comparer avec le contenu de la base
  $fcontenu_pdf_tab = array();
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  foreach($tab_users_fichier['login'] as $i_fichier => $login)
  {
    if( ($tab_users_fichier['login'][$i_fichier]=='') && ($tab_users_fichier['mdp'][$i_fichier]=='') )
    {
      // Contenu du fichier à ignorer : login et mdp non indiqués
      $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur et mot de passe non imposés</td></tr>'.NL;
    }
    else
    {
      // On recherche l'id de l'utilisateur de la base de même nom et prénom
      $tab_id_nom    = array_keys( $tab_users_base['nom'   ] , $tab_users_fichier['nom'   ][$i_fichier] );
      $tab_id_prenom = array_keys( $tab_users_base['prenom'] , $tab_users_fichier['prenom'][$i_fichier] );
      $tab_id_commun = array_intersect( $tab_id_nom , $tab_id_prenom );
      if(count($tab_id_commun))
      {
        list($inutile,$id_base) = each($tab_id_commun);
      }
      else
      {
        $id_base = FALSE;
      }
      if(!$id_base)
      {
        // Contenu du fichier à ignorer : utilisateur non trouvé dans la base
        $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom et prénom non trouvés dans la base</td></tr>'.NL;
      }
      elseif($tab_users_fichier['login'][$i_fichier]=='')
      {
        // login non indiqué (mdp forcément indiqué)...
        if(md5($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base])
        {
          // Contenu du fichier à ignorer : login non indiqué et mdp identiques
          $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">mot de passe identique et nom d\'utilisateur non imposé</td></tr>'.NL;
        }
        else
        {
          // Contenu du fichier à modifier : login non indiqué et mdp différents
          $password = $tab_users_fichier['mdp'][$i_fichier];
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':password'=>Outil::crypter_mdp($password)) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="i">Utilisateur : inchangé</td><td class="b">Mot de passe : '.html($password).'</td></tr>'.NL;
          $fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id_base]."\r\n".'Mot de passe : '.$password;
        }
      }
      elseif($tab_users_fichier['login'][$i_fichier]==$tab_users_base['login'][$id_base])
      {
        // login identique...
        if($tab_users_fichier['mdp'][$i_fichier]=='')
        {
          // Contenu du fichier à ignorer : logins identiques et mdp non indiqué
          $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur identique et mot de passe non imposé</td></tr>'.NL;
        }
        elseif(Outil::crypter_mdp($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base])
        {
          // Contenu du fichier à ignorer : logins identiques et mdp identique
          $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur et mot de passe identiques</td></tr>'.NL;
        }
        else
        {
          // Contenu du fichier à modifier : logins identiques et mdp différents
          $password = $tab_users_fichier['mdp'][$i_fichier];
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':password'=>Outil::crypter_mdp($password)) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="i">Utilisateur : inchangé</td><td class="b">Mot de passe : '.html($password).'</td></tr>'.NL;
          $fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id_base]."\r\n".'Mot de passe : '.$password;
        }
      }
      else
      {
        // logins différents...
        if(in_array($tab_users_fichier['login'][$i_fichier],$tab_users_base['login']))
        {
          // Contenu du fichier à problème : login déjà pris
          $lignes_doublon .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur proposé déjà affecté à un autre utilisateur</td></tr>'.NL;
        }
        elseif( ($tab_users_fichier['mdp'][$i_fichier]=='') || (Outil::crypter_mdp($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base]) )
        {
          // Contenu du fichier à modifier : logins différents et mdp identiques on non imposé
          $login = $tab_users_fichier['login'][$i_fichier];
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':login'=>$login) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Utilisateur : '.html($login).'</td><td class="i">Mot de passe : inchangé</td></tr>'.NL;
          $fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : inchangé';
          $tab_users_base['login'][$id_base] = $login; // Prendre en compte cette modif de login dans les comparaisons futures
        }
        else
        {
          // Contenu du fichier à modifier : logins différents et mdp différents
          $login = $tab_users_fichier['login'][$i_fichier];
          $password = $tab_users_fichier['mdp'][$i_fichier];
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':login'=>$login,':password'=>Outil::crypter_mdp($password)) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Utilisateur : '.html($login).'</td><td class="b">Mot de passe : '.html($password).'</td></tr>'.NL;
          $fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : '.$password;
          $tab_users_base['login'][$id_base] = $login; // Prendre en compte cette modif de login dans les comparaisons futures
        }
      }
    }
  }
  // On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
  Json::add_str('<ul class="puce">'.NL);
  if(count($fcontenu_pdf_tab))
  {
    $fnom = 'identifiants_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
    $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
    $pdf -> AddFont('Arial','' ,'arial.php');
    $pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
    $pdf -> AddPage();
    $pdf -> SetFillColor(245,245,245);
    $pdf -> SetDrawColor(145,145,145);
    sort($fcontenu_pdf_tab);
    foreach($fcontenu_pdf_tab as $text)
    {
      $pdf -> Add_Label(To::pdf($text));
    }
    FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.pdf' , $pdf );
    Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_LOGINPASS.$fnom.'.pdf"><span class="file file_pdf">Archiver / Imprimer les identifiants modifiés (étiquettes <em>pdf</em>).</span></a></li>'.NL);
    Json::add_str(  '<li><label class="alerte">Les mots de passe, cryptés, ne seront plus accessibles ultérieurement !</label></li>'.NL);
  }
  // On complète le bilan et on affiche le retour
  $ligne_vide = '<tr><td colspan="3">Aucun</td></tr>'.NL;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($lignes_doublon)) { $lignes_doublon = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  Json::add_str(  '<li><b>Résultat de l\'analyse et des opérations effectuées :</b></li>'.NL);
  Json::add_str('</ul>'.NL);
  Json::add_str('<table class="p">'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Comptes trouvés dans le fichier dont les identifiants ont été modifiés.</th></tr>'.NL);
  Json::add_str(    $lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Comptes trouvés dans le fichier dont les identifiants n\'ont pas pu être modifiés.</th></tr>'.NL);
  Json::add_str(    $lignes_doublon);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Comptes trouvés dans le fichier dont les identifiants sont inchangés.</th></tr>'.NL);
  Json::add_str(    $lignes_ras);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Import CSV du contenu d'un fichier pour forcer les identifiants élèves ou professeurs de GEPI
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import_gepi_profs') || ($action=='import_gepi_parents') || ($action=='import_gepi_eleves') )
{
  // Récupération du fichier
  $fichier_nom = $action.'_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.txt';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Vérification du nom du fichier obtenu.
  $tab_fnom_attendu = array(
    'import_gepi_profs'   => array('base_professeur_gepi.csv','base_cpe_gepi.csv') ,
    'import_gepi_parents' => array('base_responsable_gepi.csv') ,
    'import_gepi_eleves'  => array('base_eleve_gepi.csv')
  );
  if(!in_array(FileSystem::$file_upload_name,$tab_fnom_attendu[$action]))
  {
    Json::end( FALSE , 'Le nom du fichier n\'est pas "'.$tab_fnom_attendu[$action][0].'" !' );
  }
  // Pour récupérer les données des utilisateurs
  $tab_users_fichier               = array();
  $tab_users_fichier['id_gepi']    = array();
  $tab_users_fichier['nom']        = array();
  $tab_users_fichier['prenom']     = array();
  $tab_users_fichier['sconet_num'] = array(); // Ne servira que pour les élèves
  // Extraire les lignes du fichier
  $tab_lignes = FileSystem::extraire_lignes_csv(CHEMIN_DOSSIER_IMPORT.$fichier_nom);
  // Pas de ligne d'en-tête à supprimer
  // Récupérer les données du fichier
  foreach ($tab_lignes as $tab_elements)
  {
    if(count($tab_elements)>2)
    {
      $id_gepi    = $tab_elements[2];
      $nom        = $tab_elements[0];
      $prenom     = $tab_elements[1];
      $sconet_num = (isset($tab_elements[4])) ? $tab_elements[4] : 0;
      if( ($id_gepi!='') && ($nom!='') && ($prenom!='') )
      {
        $tab_users_fichier['id_gepi'][] = Clean::id_ent($id_gepi);
        $tab_users_fichier['nom'][]     = Clean::nom($nom);
        $tab_users_fichier['prenom'][]  = Clean::prenom($prenom);
        $tab_users_fichier['sconet_num'][] = Clean::entier($sconet_num);
      }
    }
  }
  // On trie
  array_multisort(
    $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
    $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
    $tab_users_fichier['id_gepi'],
    $tab_users_fichier['sconet_num']
  );
  // On récupère le contenu de la base pour comparer (la recherche d'éventuels doublons d'ids gepi ne se fera que sur les profs...)
  $tab_users_base               = array();
  $tab_users_base['id_gepi']    = array();
  $tab_users_base['nom']        = array();
  $tab_users_base['prenom']     = array();
  $tab_users_base['sconet_num'] = array(); // Ne servira que pour les élèves
  $profil_type = ($action=='import_gepi_profs') ? array('professeur','directeur') : substr($action,12,-1) ;
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 1 /*only_actuels*/ , 'user_id,user_sconet_elenoet,user_id_gepi,user_nom,user_prenom' /*liste_champs*/ , FALSE /*with_classe*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['id_gepi'][$DB_ROW['user_id']]    = $DB_ROW['user_id_gepi'];
    $tab_users_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
    $tab_users_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
    $tab_users_base['sconet_num'][$DB_ROW['user_id']] = $DB_ROW['user_sconet_elenoet'];
  }
  // Observer le contenu du fichier et comparer avec le contenu de la base
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  $lignes_homo    = '';
  $lignes_inconnu = '';
  foreach($tab_users_fichier['id_gepi'] as $i_fichier => $id_gepi)
  {
    if($tab_users_fichier['id_gepi'][$i_fichier]=='')
    {
      // Contenu du fichier à ignorer : id_gepi non indiqué
      $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>identifiant de GEPI non indiqué</td></tr>'.NL;
    }
    else
    {
      $id_base = 0;
      // Si sconet_num (elenoet) est renseigné (élèves uniquement), on recherche l'id de l'utilisateur de la base de même sconet_num
      if($tab_users_fichier['sconet_num'][$i_fichier])
      {
        $id_base = array_search( $tab_users_fichier['sconet_num'][$i_fichier] , $tab_users_base['sconet_num'] );
      }
      if(!$id_base)
      {
        // Sinon on recherche l'id de l'utilisateur de la base de même nom et prénom
        $tab_id_nom    = array_keys( $tab_users_base['nom'   ] , $tab_users_fichier['nom'   ][$i_fichier] );
        $tab_id_prenom = array_keys( $tab_users_base['prenom'] , $tab_users_fichier['prenom'][$i_fichier] );
        $tab_id_commun = array_intersect( $tab_id_nom , $tab_id_prenom );
        $nb_homonymes  = count($tab_id_commun);
        if($nb_homonymes == 0)
        {
          // Contenu du fichier à ignorer : utilisateur non trouvé dans la base
          $lignes_inconnu .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>nom et prénom non trouvés dans la base</td></tr>'.NL;
        }
        elseif($nb_homonymes > 1)
        {
          // Contenu du fichier à ignorer : plusieurs homonymes trouvés dans la base
          $lignes_homo .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>'.NL;
        }
        else
        {
          list($inutile,$id_base) = each($tab_id_commun);
        }
      }
      if($id_base)
      {
        if($tab_users_fichier['id_gepi'][$i_fichier]==$tab_users_base['id_gepi'][$id_base])
        {
          // Contenu du fichier à ignorer : id_gepi identique
          $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>identifiant de GEPI identique</td></tr>'.NL;
        }
        else
        {
          // id_gepi différents...
          if(in_array($tab_users_fichier['id_gepi'][$i_fichier],$tab_users_base['id_gepi']))
          {
            // Contenu du fichier à problème : id_gepi déjà pris
            $lignes_doublon .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>identifiant de GEPI déjà affecté à un autre utilisateur</td></tr>'.NL;
          }
          else
          {
            // Contenu du fichier à modifier : id_gepi nouveau
            DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':id_gepi'=>$id_gepi) );
            $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td class="b">Id Gepi : '.html($id_gepi).'</td></tr>'.NL;
          }
        }
      }
    }
  }
  // On affiche le retour
  $ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
  $ligne_probleme = $lignes_doublon.$lignes_homo.$lignes_inconnu;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($ligne_probleme)) { $ligne_probleme = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  Json::add_str('<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>'.NL);
  Json::add_str('<table class="p">'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant Gepi a été modifié.</th></tr>'.NL);
  Json::add_str($lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant Gepi n\'a pas pu être modifié.</th></tr>'.NL);
  Json::add_str($ligne_probleme);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant Gepi est inchangé.</th></tr>'.NL);
  Json::add_str($lignes_ras);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Import CSV du contenu d'un fichier pour forcer les identifiants d'un ENT
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='import_ent')
{
  // Récupération du fichier
  $fichier_nom  = 'identifiants_'.$action.'_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.txt';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Récupérer les infos sur le CSV associé à l'ENT
  require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');
  $tab_infos_csv = $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_DEPARTEMENT'].'|'.$_SESSION['CONNEXION_NOM']];
  // Pour récupérer les données des utilisateurs
  $tab_users_fichier              = array();
  $tab_users_fichier['id_ent']    = array();
  $tab_users_fichier['nom']       = array();
  $tab_users_fichier['prenom']    = array();
  $tab_users_fichier['id_sconet'] = array();
  // Extraire les lignes du fichier
  $tab_lignes = FileSystem::extraire_lignes_csv(CHEMIN_DOSSIER_IMPORT.$fichier_nom);
  // CSV avec ordre des champs variables : utiliser la 1ère ligne pour déterminer l'emplacement des données
  if( $tab_infos_csv['csv_entete'] && !$tab_infos_csv['csv_ordre'] )
  {
    $tab_numero_colonne = array(
      'csv_nom'    => -100 ,
      'csv_prenom' => -100 ,
      'csv_id_ent' => -100 ,
    );
    $tab_elements = $tab_lignes[0];
    $numero_max = 0;
    foreach ($tab_elements as $numero=>$element)
    {
      switch($element)
      {
        case $tab_infos_csv['csv_nom'   ] : $tab_numero_colonne['csv_nom'   ] = $numero; $numero_max = max($numero_max,$numero); break;
        case $tab_infos_csv['csv_prenom'] : $tab_numero_colonne['csv_prenom'] = $numero; $numero_max = max($numero_max,$numero); break;
        case $tab_infos_csv['csv_id_ent'] : $tab_numero_colonne['csv_id_ent'] = $numero; $numero_max = max($numero_max,$numero); break;
      }
    }
    if(array_sum($tab_numero_colonne)<0)
    {
      Json::end( FALSE , 'Les champs nécessaires n\'ont pas pu être repérés !' );
    }
    $tab_infos_csv['csv_nom'   ] = $tab_numero_colonne['csv_nom'   ];
    $tab_infos_csv['csv_prenom'] = $tab_numero_colonne['csv_prenom'];
    $tab_infos_csv['csv_id_ent'] = $tab_numero_colonne['csv_id_ent'];
  }
  // Supprimer la ou les première(s) ligne(s) ou aucune
  $tab_lignes = array_slice( $tab_lignes , $tab_infos_csv['csv_entete'] );
  // Récupérer les données
  foreach ($tab_lignes as $tab_elements)
  {
    if(count($tab_elements)>2)
    {
      $id_ent    = $tab_elements[ $tab_infos_csv['csv_id_ent'] ];
      $nom       = $tab_elements[ $tab_infos_csv['csv_nom']    ];
      $prenom    = $tab_elements[ $tab_infos_csv['csv_prenom'] ];
      $id_sconet = ($tab_infos_csv['csv_id_sconet']==NULL) ? '' : $tab_elements[ $tab_infos_csv['csv_id_sconet'] ] ;
      if( ($id_ent!='') && ($nom!='') && ($prenom!='') )
      {
        if(substr($_SESSION['CONNEXION_NOM'],0,7)=='logica_')
        {
          // Dans les CSV de Lilie & Celi@ & PCN, il faut remplacer "ID : " par "UT" (exemple : "ID : 75185265" devient "UT75185265").
          // 06/06/2014 - Dans PCN c'est maintenant bon, ils exportent un CSV propre et compatible avec tous les utilisateurs.
          $id_ent = str_replace('ID : ','UT',$id_ent);
        }
        $tab_users_fichier['id_ent'][]    = Clean::id_ent($id_ent);
        $tab_users_fichier['nom'][]       = Clean::nom(Clean::accents($nom)); // En cas de comparaison sur nom / prénom, maieux vaut éviter les accents
        $tab_users_fichier['prenom'][]    = Clean::prenom(Clean::accents($prenom));
        $tab_users_fichier['id_sconet'][] = Clean::entier($id_sconet);
      }
    }
  }
  // On trie
  array_multisort(
    $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
    $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
    $tab_users_fichier['id_ent'],
    $tab_users_fichier['id_sconet']
  );
  // On récupère le contenu de la base pour comparer
  $tab_users_base              = array();
  $tab_users_base['id_ent']    = array();
  $tab_users_base['nom']       = array();
  $tab_users_base['prenom']    = array();
  $tab_users_base['id_sconet'] = array();
  $tab_users_base['info']      = array();
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_id,user_id_ent,user_nom,user_prenom,user_profil_type,user_profil_nom_court_singulier' /*liste_champs*/ , TRUE /*with_classe*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['id_ent'][$DB_ROW['user_id']]    = $DB_ROW['user_id_ent'];
    $tab_users_base['nom'][$DB_ROW['user_id']]       = Clean::accents($DB_ROW['user_nom']);
    $tab_users_base['prenom'][$DB_ROW['user_id']]    = Clean::accents($DB_ROW['user_prenom']);
    $tab_users_base['id_sconet'][$DB_ROW['user_id']] = $DB_ROW['user_sconet_id'];
    $tab_users_base['info'][$DB_ROW['user_id']]      = ($DB_ROW['user_profil_type']=='eleve') ? $DB_ROW['groupe_nom'] : $DB_ROW['user_profil_nom_court_singulier'] ;
  }
  // Observer le contenu du fichier et comparer avec le contenu de la base
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  $lignes_homo    = '';
  $lignes_inconnu = '';
  foreach($tab_users_fichier['id_ent'] as $i_fichier => $id_ent)
  {
    if($tab_users_fichier['id_ent'][$i_fichier]=='')
    {
      // Contenu du fichier à ignorer : id_ent non indiqué
      $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>identifiant d\'ENT non imposé</td></tr>'.NL;
    }
    else
    {
      // Dans les CSV de Lilie & Celi@ les noms/prénoms ne sont pas accentués, mais par contre on a l'id Sconet
      if($tab_users_fichier['id_sconet'][$i_fichier])
      {
        $id_base = array_search( $tab_users_fichier['id_sconet'][$i_fichier] , $tab_users_base['id_sconet'] );
        if($id_base == FALSE)
        {
          // Contenu du fichier à ignorer : utilisateur non trouvé dans la base
          $lignes_inconnu .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant Sconet '.$tab_users_fichier['id_sconet'][$i_fichier].' non trouvé dans la base</td></tr>'.NL;
        }
        else
        {
          if($tab_users_fichier['id_ent'][$i_fichier]==$tab_users_base['id_ent'][$id_base])
          {
            // Contenu du fichier à ignorer : id_ent identique
            $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT identique</td></tr>'.NL;
          }
          else
          {
            // id_ent différents...
            if(in_array($tab_users_fichier['id_ent'][$i_fichier],$tab_users_base['id_ent']))
            {
              // Contenu du fichier à problème : id_ent déjà pris
              $lignes_doublon .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT déjà affecté à un autre utilisateur</td></tr>'.NL;
            }
            else
            {
              // Contenu du fichier à modifier : id_ent nouveau
              DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':id_ent'=>$id_ent) );
              $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Id ENT : '.html($id_ent).'</td></tr>'.NL;
            }
          }
        }
      }
      else
      {
        // On recherche l'id de l'utilisateur de la base de même nom et prénom
        $tab_id_nom    = array_keys( $tab_users_base['nom'   ] , $tab_users_fichier['nom'   ][$i_fichier] );
        $tab_id_prenom = array_keys( $tab_users_base['prenom'] , $tab_users_fichier['prenom'][$i_fichier] );
        $tab_id_commun = array_intersect( $tab_id_nom , $tab_id_prenom );
        $nb_homonymes  = count($tab_id_commun);
        if($nb_homonymes == 0)
        {
          // Contenu du fichier à ignorer : utilisateur non trouvé dans la base
          $lignes_inconnu .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>nom et prénom non trouvés dans la base</td></tr>'.NL;
        }
        elseif($nb_homonymes > 1)
        {
          // Contenu du fichier à ignorer : plusieurs homonymes trouvés dans la base
          $lignes_homo .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>'.NL;
        }
        else
        {
          list($inutile,$id_base) = each($tab_id_commun);
          if($tab_users_fichier['id_ent'][$i_fichier]==$tab_users_base['id_ent'][$id_base])
          {
            // Contenu du fichier à ignorer : id_ent identique
            $lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT identique</td></tr>'.NL;
          }
          else
          {
            // id_ent différents...
            if(in_array($tab_users_fichier['id_ent'][$i_fichier],$tab_users_base['id_ent']))
            {
              // Contenu du fichier à problème : id_ent déjà pris
              $lignes_doublon .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT déjà affecté à un autre utilisateur</td></tr>'.NL;
            }
            else
            {
              // Contenu du fichier à modifier : id_ent nouveau
              DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':id_ent'=>$id_ent) );
              $lignes_modif .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Id ENT : '.html($id_ent).'</td></tr>'.NL;
            }
          }
        }
      }
    }
  }
  // On affiche le retour
  $ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
  $ligne_probleme = $lignes_doublon.$lignes_homo.$lignes_inconnu;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($ligne_probleme)) { $ligne_probleme = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  Json::add_str('<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>'.NL);
  Json::add_str('<table class="p">'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant ENT a été modifié.</th></tr>'.NL);
  Json::add_str($lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>'.NL);
  Json::add_str($ligne_probleme);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes trouvés dans le fichier dont l\'identifiant ENT est inchangé.</th></tr>'.NL);
  Json::add_str($lignes_ras);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Dupliquer l'identifiant de Gepi enregistré comme identifiant de l'ENT (COPY_id_gepi_TO_id_ent)
// Dupliquer le login de SACoche enregistré comme identifiant de l'ENT (COPY_login_TO_id_ent)
// Dupliquer l'identifiant de l'ENT enregistré comme identifiant de Gepi (COPY_id_ent_TO_id_gepi)
// Dupliquer le login de SACoche enregistré comme identifiant de Gepi (COPY_login_TO_id_gepi)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='COPY_id_gepi_TO_id_ent') || ($action=='COPY_login_TO_id_ent') || ($action=='COPY_id_ent_TO_id_gepi') || ($action=='COPY_login_TO_id_gepi') )
{
  list($champ_depart,$champ_arrive) = explode('_TO_',substr($action,5));
  DB_STRUCTURE_ADMINISTRATEUR::DB_recopier_identifiants($champ_depart,$champ_arrive);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Dupliquer l'identifiant récupéré du LCS comme identifiant de l'ENT (COPY_id_lcs_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='COPY_id_lcs_TO_id_ent')
{
  if(IS_HEBERGEMENT_SESAMATH)
  {
    Json::end( FALSE , 'Cette fonctionnalité est sans objet sur le serveur Sésamath !' );
  }
  if(!is_file(CHEMIN_FICHIER_WS_LCS))
  {
    Json::end( FALSE , 'Le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_LCS).'" n\'a pas été trouvé !' );
  }
  require(CHEMIN_FICHIER_WS_LCS); // Charge la fonction "recuperer_infos_user_LCS()"
  // On récupère le contenu de la base, on va passer les users en revue un par un
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 1 /*only_actuels*/ , 'user_id,user_sconet_id,user_sconet_elenoet,user_id_ent,user_nom,user_prenom,user_profil_type,user_profil_nom_court_singulier' /*liste_champs*/ , TRUE /*with_classe*/ );
  // Pour chaque user de la base, rechercher son uid dans le LCS
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  $lignes_erreur  = '';
  $lignes_inconnu = ''; // de SACoche non trouvé dans LCS
  foreach($DB_TAB as $DB_ROW)
  {
    if(!in_array($DB_ROW['user_profil_type'],array('eleve','professeur')))
    {
      // Contenu de SACoche à ignorer : utilisateur non cherché car non présent dans le LCS
      $lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car profil '.$DB_ROW['user_profil_nom_court_singulier'].' absent du LCS</td></tr>'.NL;
    }
    elseif( ($DB_ROW['user_profil_type']=='eleve') && (!$DB_ROW['user_sconet_elenoet']) )
    {
      // Contenu de SACoche à ignorer : élève non cherché dans le LCS car pas d'Elenoet (numéro Sconet)
      $lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car '.$DB_ROW['user_profil_nom_court_singulier'].' sans Elenoet</td></tr>'.NL;
    }
    elseif( ($DB_ROW['user_profil_type']=='professeur') && (!$DB_ROW['user_sconet_id']) )
    {
      // Contenu de SACoche à ignorer : prof non cherché dans le LCS car pas d'Id Sconet
      $lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car '.$DB_ROW['user_profil_nom_court_singulier'].' sans Id Sconet</td></tr>'.NL;
    }
    else
    {
      list($code_erreur,$tab_valeurs_retournees) = recuperer_infos_user_LCS($DB_ROW['user_profil_type'],$DB_ROW['user_sconet_elenoet'],$DB_ROW['user_sconet_id']);
      if($code_erreur)
      {
        // Contenu de SACoche à problème : retour erroné du LCS
        $lignes_erreur .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non trouvé : erreur LCS n°'.html($code_erreur).'</td></tr>'.NL;
      }
      elseif(count($tab_valeurs_retournees)==0)
      {
        // Contenu de SACoche à ignorer : utilisateur non trouvé dans le LCS
        $identifiant = ($DB_ROW['user_profil_type']=='eleve') ? $DB_ROW['user_sconet_elenoet'] : $DB_ROW['user_sconet_id'] ;
        $lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>Identifiant '.html($identifiant).' non trouvé dans le LCS</td></tr>'.NL;
      }
      elseif(count($tab_valeurs_retournees)!=1)
      {
        // Contenu de SACoche à problème : plusieurs réponses retournées par le LCS
        $identifiant = ($DB_ROW['user_profil_type']=='eleve') ? $DB_ROW['user_sconet_elenoet'] : $DB_ROW['user_sconet_id'] ;
        $lignes_doublon .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>Identifiant '.html($identifiant).' trouvé plusieurs fois dans le LCS</td></tr>'.NL;
      }
      else
      {
        $id_ent_LCS = Clean::id_ent($tab_valeurs_retournees[0]);
        if($DB_ROW['user_id_ent']==$id_ent_LCS)
        {
          // Contenu de SACoche à ignorer : id_ent identique
          $lignes_ras .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>identifiant du LCS identique</td></tr>'.NL;
        }
        else
        {
          // Contenu de SACoche à modifier : id_ent nouveau
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $DB_ROW['user_id'] , array(':id_ent'=>$id_ent_LCS) );
          $user_info = ($DB_ROW['user_profil_type']=='eleve') ? $DB_ROW['groupe_nom'] : $DB_ROW['user_profil_nom_court_singulier'] ;
          $lignes_modif .= '<tr class="new"><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td class="b">Id ENT : '.html($id_ent_LCS).'</td></tr>'.NL;
        }
      }
    }
  }
  // On affiche le bilan
  $ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
  $ligne_probleme = $lignes_doublon.$lignes_erreur;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($ligne_probleme)) { $ligne_probleme = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  if(empty($lignes_inconnu)) { $lignes_inconnu = $ligne_vide; }
  Json::add_str('<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>'.NL);
  Json::add_str('<table>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans le LCS dont l\'identifiant ENT a été modifié.</th></tr>'.NL);
  Json::add_str($lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>'.NL);
  Json::add_str($ligne_probleme);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans le LCS dont l\'identifiant ENT est inchangé.</th></tr>'.NL);
  Json::add_str($lignes_ras);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche non récupérés dans le LCS.</th></tr>'.NL);
  Json::add_str($lignes_inconnu);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer l'identifiant ENT Argos (COPY_id_argos_*_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='COPY_id_argos_profs_TO_id_ent') || ($action=='COPY_id_argos_eleves_TO_id_ent') || ($action=='COPY_id_argos_parents_TO_id_ent') )
{
  if(IS_HEBERGEMENT_SESAMATH)
  {
    Json::end( FALSE , 'Cette fonctionnalité est sans objet sur le serveur Sésamath !' );
  }
  if(!in_array( substr($_SESSION['WEBMESTRE_UAI'],0,3) , array('024','033','040','047','064') ))
  {
    Json::end( FALSE , 'Cette fonctionnalité est réservée aux établissements de l\'académie de Bordeaux (et votre numéro UAI n\'y correspond pas) !' );
  }
  if(!is_file(CHEMIN_FICHIER_WS_ARGOS))
  {
    Json::end( FALSE , 'Le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ARGOS).'" n\'a pas été trouvé !' );
  }
  require(CHEMIN_FICHIER_WS_ARGOS); // Charge la fonction "recuperer_infos_LDAP()"
  $qui = substr($action,14,-10); // profs | eleves | parents
  // Appelle le serveur LDAP et retourne un tableau [ ['nom'][i] , ['prenom'][i] , ['id_ent'][i] ]
  $tab_users_ENT = recuperer_infos_LDAP($_SESSION['WEBMESTRE_UAI'],$qui);
  // On récupère le contenu de la base pour comparer
  $profil_type = ($qui=='profs') ? array('professeur','directeur') : substr($qui,0,-1) ;
  $with_classe = ($qui=='profs') ? FALSE : TRUE ;
  $NEXT_RecupUsersBase_CompareUsersENT_PrintBilan = TRUE;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer l'identifiant ENT Libre des collèges de l'Essonne  (COPY_id_entlibre_essonne_TO_id_ent)
// Récupérer l'identifiant ENT Libre LÉO des lycées de Picardie (COPY_id_entlibre_picardie_TO_id_ent)
// Récupérer l'identifiant ENT Libre Plateforme de test         (COPY_id_entlibre_test_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='COPY_id_entlibre_essonne_TO_id_ent') || ($action=='COPY_id_entlibre_picardie_TO_id_ent') || ($action=='COPY_id_entlibre_test_TO_id_ent') )
{
  // Vérif hébergement
  if( IS_HEBERGEMENT_SESAMATH && ($action=='COPY_id_entlibre_essonne_TO_id_ent') )
  {
    Json::end( FALSE , 'Cette fonctionnalité est sans objet sur le serveur Sésamath !' );
  }
  if( !IS_HEBERGEMENT_SESAMATH && ($action!='COPY_id_entlibre_essonne_TO_id_ent') )
  {
    Json::end( FALSE , 'Cette fonctionnalité est sans objet sur un autre serveur que Sésamath !' );
  }
  // Vérif UAI
  if( ($action=='COPY_id_entlibre_essonne_TO_id_ent') && (substr($_SESSION['WEBMESTRE_UAI'],0,3)!='091') )
  {
    Json::end( FALSE , 'Cette fonctionnalité est réservée aux établissements du département de l\'Essonne (et votre numéro UAI n\'y correspond pas) !' );
  }
  if( ($action=='COPY_id_entlibre_picardie_TO_id_ent') && !in_array( substr($_SESSION['WEBMESTRE_UAI'],0,3) , array('002','060','080') ) )
  {
    Json::end( FALSE , 'Cette fonctionnalité est réservée aux lycées de Picardie (et votre numéro UAI n\'y correspond pas) !' );
  }
  // Vérif fichier
  $tab_fichier_ws = array(
    'COPY_id_entlibre_essonne_TO_id_ent'  => CHEMIN_FICHIER_WS_ENTLIBRE_ESSONNE ,
    'COPY_id_entlibre_picardie_TO_id_ent' => CHEMIN_FICHIER_WS_ENTLIBRE_PICARDIE ,
    'COPY_id_entlibre_test_TO_id_ent'     => CHEMIN_FICHIER_WS_ENTLIBRE_TEST ,
  );
  $CHEMIN_FICHIER_WS = $tab_fichier_ws[$action];
  if(!is_file($CHEMIN_FICHIER_WS))
  {
    Json::end( FALSE , 'Le fichier "'.FileSystem::fin_chemin($CHEMIN_FICHIER_WS).'" n\'a pas été trouvé !' );
  }
  require($CHEMIN_FICHIER_WS); // Charge la fonction "EntLibre_RecupId()"
  // Appelle le serveur et retourne un tableau [ ['nom'][i] , ['prenom'][i] , ['id_ent'][i] ]
  $tab_users_ENT = EntLibre_RecupId($_SESSION['WEBMESTRE_UAI']);
  // On récupère le contenu de la base pour comparer
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $with_classe = TRUE ;
  $NEXT_RecupUsersBase_CompareUsersENT_PrintBilan = TRUE;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Suite des cas précédents
// @use   $tab_users_ENT   +   $profil_type   +   $with_classe
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( isset($NEXT_RecupUsersBase_CompareUsersENT_PrintBilan) )
{
  $tab_users_base           = array();
  $tab_users_base['id_ent'] = array();
  $tab_users_base['nom']    = array();
  $tab_users_base['prenom'] = array();
  $tab_users_base['info']   = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 1 /*only_actuels*/ , 'user_id,user_id_ent,user_nom,user_prenom,user_profil_type,user_profil_nom_court_singulier' /*liste_champs*/ , $with_classe );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['id_ent'][$DB_ROW['user_id']] = $DB_ROW['user_id_ent'];
    $tab_users_base['nom'   ][$DB_ROW['user_id']] = $DB_ROW['user_nom'];
    $tab_users_base['prenom'][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
    $tab_users_base['info'  ][$DB_ROW['user_id']] = ($DB_ROW['user_profil_type']=='eleve') ? $DB_ROW['groupe_nom'] : $DB_ROW['user_profil_nom_court_singulier'] ;
  }
  // Observer le contenu de l'ENT et comparer avec le contenu de la base
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  $lignes_homo    = '';
  $lignes_inconnu = ''; // de SACoche non trouvé dans l'ENT
  $lignes_reste   = ''; // de l'ENT non trouvé dans SACoche
  foreach($tab_users_base['id_ent'] as $user_id => $id_ent_SACoche)
  {
    // Pour chaque user SACoche on recherche un utilisateur de l'ENT de même nom et prénom
    $tab_id_nom    = array_keys( $tab_users_ENT['nom'   ] , $tab_users_base['nom'   ][$user_id] );
    $tab_id_prenom = array_keys( $tab_users_ENT['prenom'] , $tab_users_base['prenom'][$user_id] );
    $tab_id_commun = array_intersect( $tab_id_nom , $tab_id_prenom );
    $nb_homonymes  = count($tab_id_commun);
    if($nb_homonymes == 0)
    {
      // Contenu de SACoche à ignorer : utilisateur non trouvé dans l'ENT
      $lignes_inconnu .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>nom et prénom non trouvés dans l\'ENT</td></tr>'.NL;
    }
    elseif($nb_homonymes > 1)
    {
      // Contenu de SACoche à problème : plusieurs homonymes trouvés dans l'ENT
      $lignes_homo .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>'.NL;
    }
    else
    {
      list($inutile,$i_ENT) = each($tab_id_commun);
      $id_ent_ENT = $tab_users_ENT['id_ent'][$i_ENT];
      if($id_ent_SACoche==$id_ent_ENT)
      {
        // Contenu de SACoche à ignorer : id_ent identique
        $lignes_ras .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT identique</td></tr>'.NL;
      }
      else
      {
        // id_ent différents...
        if(in_array($id_ent_ENT,$tab_users_base['id_ent']))
        {
          // Contenu de SACoche à problème : id_ent déjà pris
          $lignes_doublon .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT ['.html($id_ent_ENT).'] déjà affecté à un autre utilisateur</td></tr>'.NL;
        }
        else
        {
          // Contenu de SACoche à modifier : id_ent nouveau
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $user_id , array(':id_ent'=>$id_ent_ENT) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ('.$tab_users_base['info'][$user_id].')').'</td><td class="b">Id ENT : '.html($id_ent_ENT).'</td></tr>'.NL;
        }
      }
      unset($tab_users_ENT['id_ent'][$i_ENT] , $tab_users_ENT['nom'][$i_ENT] , $tab_users_ENT['prenom'][$i_ENT]);
    }
  }
  if(count($tab_users_ENT['id_ent']))
  {
    foreach($tab_users_ENT['id_ent'] as $i_ENT => $id_ent_ENT)
    {
      $lignes_reste .= '<tr><td>'.html($tab_users_ENT['nom'][$i_ENT].' '.$tab_users_ENT['prenom'][$i_ENT].' ['.$id_ent_ENT.']').'</td><td>nom et prénom non trouvés dans SACoche</td></tr>'.NL;
    }
  }
  // On affiche le bilan
  $ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
  $ligne_probleme = $lignes_doublon.$lignes_homo;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($ligne_probleme)) { $ligne_probleme = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  if(empty($lignes_inconnu)) { $lignes_inconnu = $ligne_vide; }
  if(empty($lignes_reste  )) { $lignes_reste   = $ligne_vide; }
  Json::add_str('<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>'.NL);
  Json::add_str('<table>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT a été modifié.</th></tr>'.NL);
  Json::add_str($lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>'.NL);
  Json::add_str($ligne_probleme);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT est inchangé.</th></tr>'.NL);
  Json::add_str($lignes_ras);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche non trouvé dans l\'ENT.</th></tr>'.NL);
  Json::add_str($lignes_inconnu);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes de l\'ENT non trouvés dans SACoche.</th></tr>'.NL);
  Json::add_str($lignes_reste);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer l'identifiant ENT de Laclasse.com (COPY_id_laclasse_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='COPY_id_laclasse_TO_id_ent')
{
  if(IS_HEBERGEMENT_SESAMATH)
  {
    Json::end( FALSE , 'Cette fonctionnalité est sans objet sur le serveur Sésamath !' );
  }
  if(substr($_SESSION['WEBMESTRE_UAI'],0,3)!='069')
  {
    Json::end( FALSE , 'Cette fonctionnalité est réservée aux établissements du département du Rhône (et votre numéro UAI n\'y correspond pas) !' );
  }
  if(!is_file(CHEMIN_FICHIER_WS_LACLASSE))
  {
    Json::end( FALSE , 'Le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_LACLASSE).'" n\'a pas été trouvé !' );
  }
  require(CHEMIN_FICHIER_WS_LACLASSE); // Charge la fonction "recuperer_infos_Laclasse()"
  // Appelle l'annuaire ENT Laclasse.com et retourne un tableau [ ['profil'][i] , ['id_ent'][i]  , ['nom'][i] , ['prenom'][i] , ['id_sconet'][i] ]
  $tab_users_ENT = recuperer_infos_Laclasse('0693331W');
  // $tab_users_ENT = recuperer_infos_Laclasse($_SESSION['WEBMESTRE_UAI']);  // ****************************************************************************
  // On récupère le contenu de la base pour comparer
  $tab_users_base              = array();
  $tab_users_base['id']        = array();
  $tab_users_base['ordre']     = array();
  $tab_users_base['profil']    = array();
  $tab_users_base['id_ent']    = array();
  $tab_users_base['nom']       = array();
  $tab_users_base['prenom']    = array();
  $tab_users_base['id_sconet'] = array(); // Ne servira que pour les élèves
  $tab_profils_types = array('eleve','parent','professeur','directeur','inspecteur');
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 1 /*only_actuels*/ , 'user_id,user_id_ent,user_sconet_id,user_nom,user_prenom,user_profil_sigle' /*liste_champs*/ , FALSE /*with_classe*/ );
  $tab_ordre = array( 'DIR'=>1, 'ENS'=>1, 'DOC'=>1, 'EDU'=>1, 'ELV'=>2, 'TUT'=>3, 'AVS'=>4, 'IEX'=>4, 'AED'=>4, 'SUR'=>4, 'ORI'=>4, 'MDS'=>4, 'ADF'=>4 );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['id'       ][] = (int)$DB_ROW['user_id'];
    $tab_users_base['ordre'    ][] = $tab_ordre[$DB_ROW['user_profil_sigle']];
    $tab_users_base['profil'   ][] = $DB_ROW['user_profil_sigle'];
    $tab_users_base['id_ent'   ][] = $DB_ROW['user_id_ent'];
    $tab_users_base['nom'      ][] = $DB_ROW['user_nom'];
    $tab_users_base['prenom'   ][] = $DB_ROW['user_prenom'];
    $tab_users_base['id_sconet'][] = (int)$DB_ROW['user_sconet_id'];
  }
  // On trie
  array_multisort(
    $tab_users_base['ordre'] , SORT_ASC,SORT_NUMERIC,
    $tab_users_base['profil'], SORT_ASC,SORT_STRING,
    $tab_users_base['nom']   , SORT_ASC,SORT_STRING,
    $tab_users_base['prenom'], SORT_ASC,SORT_STRING,
    $tab_users_base['id'],
    $tab_users_base['id_ent'],
    $tab_users_base['id_sconet']
  );
  // On retire l'ordre dont on n'a plus besoin
  unset($tab_users_base['ordre']);
  // Lister les profils ; ne peut être récupéré via la requête précédente à cause de profils présents dans l'ENT dont il n'y aurait aucun utilisateur dans la base SACoche
  $tab_profils = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_court_singulier' /*listing_champs*/ , FALSE /*only_actif*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_profils[$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_nom_court_singulier'];
  }
  // Observer le contenu de l'ENT et comparer avec le contenu de la base
  $lignes_ras     = '';
  $lignes_modif   = '';
  $lignes_doublon = '';
  $lignes_homo    = '';
  $lignes_inconnu = ''; // de SACoche non trouvé dans l'ENT
  $lignes_reste   = ''; // de l'ENT non trouvé dans SACoche
  // Pour chaque user SACoche ...
  foreach($tab_users_base['id_ent'] as $i_base => $id_ent_SACoche)
  {
    $i_ENT = 0;
    // Si id_sconet (user_sconet_id) est renseigné (élèves uniquement), on recherche l'utilisateur de l'ENT de même id_sconet
    if( ($tab_users_base['profil'][$i_base]=='ELV') && ($tab_users_base['id_sconet'][$i_base]) )
    {
      $i_ENT = array_search( $tab_users_base['id_sconet'][$i_base] , $tab_users_ENT['id_sconet'] );
    }
    if(!$i_ENT)
    {
      // Sinon on recherche un utilisateur de l'ENT de même nom et prénom
      $tab_id_nom    = array_keys( $tab_users_ENT['nom'   ] , $tab_users_base['nom'   ][$i_base] );
      $tab_id_prenom = array_keys( $tab_users_ENT['prenom'] , $tab_users_base['prenom'][$i_base] );
      $tab_id_commun = array_intersect( $tab_id_nom , $tab_id_prenom );
      $nb_homonymes  = count($tab_id_commun);
      if($nb_homonymes == 0)
      {
        // Contenu de SACoche à ignorer : utilisateur non trouvé dans l'ENT
        $lignes_inconnu .= '<tr><td>'.html($tab_profils[$tab_users_base['profil'][$i_base]].' | '.$tab_users_base['nom'][$i_base].' '.$tab_users_base['prenom'][$i_base].' ['.$id_ent_SACoche.']').'</td><td>nom et prénom non trouvés dans l\'ENT</td></tr>'.NL;
      }
      elseif($nb_homonymes > 1)
      {
        // Contenu de SACoche à problème : plusieurs homonymes trouvés dans l'ENT
        $lignes_homo .= '<tr><td>'.html($tab_profils[$tab_users_base['profil'][$i_base]].' | '.$tab_users_base['nom'][$i_base].' '.$tab_users_base['prenom'][$i_base].' ['.$id_ent_SACoche.']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>'.NL;
      }
      else
      {
        list($inutile,$i_ENT) = each($tab_id_commun);
      }
    }
    if($i_ENT)
    {
      $id_ent_ENT = $tab_users_ENT['id_ent'][$i_ENT];
      if($id_ent_SACoche==$id_ent_ENT)
      {
        // Contenu de SACoche à ignorer : id_ent identique
        $lignes_ras .= '<tr><td>'.html($tab_profils[$tab_users_base['profil'][$i_base]].' | '.$tab_users_base['nom'][$i_base].' '.$tab_users_base['prenom'][$i_base].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT identique</td></tr>'.NL;
      }
      else
      {
        // id_ent différents...
        if(in_array($id_ent_ENT,$tab_users_base['id_ent']))
        {
          // Contenu de SACoche à problème : id_ent déjà pris
          $lignes_doublon .= '<tr><td>'.html($tab_profils[$tab_users_base['profil'][$i_base]].' | '.$tab_users_base['nom'][$i_base].' '.$tab_users_base['prenom'][$i_base].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT ['.html($id_ent_ENT).'] déjà affecté à un autre utilisateur</td></tr>'.NL;
        }
        else
        {
          // Contenu de SACoche à modifier : id_ent nouveau
          DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $tab_users_base['id'][$i_base] , array(':id_ent'=>$id_ent_ENT) );
          $lignes_modif .= '<tr class="new"><td>'.html($tab_profils[$tab_users_base['profil'][$i_base]].' | '.$tab_users_base['nom'][$i_base].' '.$tab_users_base['prenom'][$i_base]).'</td><td class="b">Id ENT : '.html($id_ent_ENT).'</td></tr>'.NL;
        }
      }
      unset($tab_users_ENT['profil'][$i_ENT] , $tab_users_ENT['id_ent'][$i_ENT] , $tab_users_ENT['nom'][$i_ENT] , $tab_users_ENT['prenom'][$i_ENT] , $tab_users_ENT['id_sconet'][$i_ENT]);
    }
  }
  if(count($tab_users_ENT['id_ent']))
  {
    foreach($tab_users_ENT['id_ent'] as $i_ENT => $id_ent_ENT)
    {
      $lignes_reste .= '<tr><td>'.html($tab_profils[$tab_users_ENT['profil'][$i_ENT]].' | '.$tab_users_ENT['nom'][$i_ENT].' '.$tab_users_ENT['prenom'][$i_ENT].' ['.$id_ent_ENT.']').'</td><td>nom et prénom non trouvés dans SACoche</td></tr>'.NL;
    }
  }
  // On affiche le bilan
  $ligne_vide = '<tr><td colspan="2">Aucun</td></tr>'.NL;
  $ligne_probleme = $lignes_doublon.$lignes_homo;
  if(empty($lignes_modif  )) { $lignes_modif   = $ligne_vide; }
  if(empty($ligne_probleme)) { $ligne_probleme = $ligne_vide; }
  if(empty($lignes_ras    )) { $lignes_ras     = $ligne_vide; }
  if(empty($lignes_inconnu)) { $lignes_inconnu = $ligne_vide; }
  if(empty($lignes_reste  )) { $lignes_reste   = $ligne_vide; }
  Json::add_str('<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>'.NL);
  Json::add_str('<table>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT a été modifié.</th></tr>'.NL);
  Json::add_str($lignes_modif);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>'.NL);
  Json::add_str($ligne_probleme);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche trouvés dans l\'ENT dont l\'identifiant ENT est inchangé.</th></tr>'.NL);
  Json::add_str($lignes_ras);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes SACoche non trouvé dans l\'ENT.</th></tr>'.NL);
  Json::add_str($lignes_inconnu);
  Json::add_str(  '</tbody><tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Comptes de l\'ENT non trouvés dans SACoche.</th></tr>'.NL);
  Json::add_str($lignes_reste);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
