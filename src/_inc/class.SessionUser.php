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

class SessionUser
{

  public static $tab_dalton_css = array(
    2 => array( '#9B9B9B', '#E1E1E1' ),
    3 => array( '#909090', '#BEBEBE', '#EAEAEA' ),
    4 => array( '#828282', '#AAAAAA', '#D2D2D2', '#FAFAFA' ),
    5 => array( '#828282', '#A0A0A0', '#BEBEBE', '#DCDCDC', '#FAFAFA' ),
    6 => array( '#7D7D7D', '#969696', '#AFAFAF', '#C8C8C8', '#E1E1E1', '#FAFAFA' ),
  );

  /**
   * Tester si mdp du webmestre transmis convient.
   * 
   * @param string    $password
   * @return array(bool,string)   (TRUE,'') ou (FALSE,message_d_erreur)
   */
  public static function tester_authentification_webmestre($password)
  {
    global $PAGE;
    $password_crypte = Outil::crypter_mdp($password);
    return ($password_crypte==WEBMESTRE_PASSWORD_MD5) ? array( TRUE , '' ) : array( FALSE , 'Mot de passe incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' ) ;
  }

  /**
   * Tester si mdp d'un développeur SACoche transmis convient.
   * On réutilise des éléments de la connexion webmestre.
   * 
   * @param string    $password
   * @return array(bool,string)   (TRUE,'') ou (FALSE,message_d_erreur)
   */
  public static function tester_authentification_developpeur($password)
  {
    $result = ServeurCommunautaire::tester_auth_devel( Outil::crypter_mdp($password) );
    return ($result=='ok') ? array( TRUE , '' ) : array( FALSE , $result ) ;
  }

  /**
   * Tester si les données transmises permettent d'authentifier un partenaire (convention ENT serveur Sésamath).
   * 
   * @param int       $partenaire_id
   * @param string    $password
   * @return array(bool,array|string)   (TRUE,$DB_ROW) ou (FALSE,message_d_erreur)
   */
  public static function tester_authentification_partenaire($partenaire_id,$password)
  {
    // Récupérer les données associées à ce partenaire.
    $DB_ROW = DB_WEBMESTRE_PUBLIC::DB_recuperer_donnees_partenaire($partenaire_id);
    // Si id non trouvé...
    if(empty($DB_ROW))
    {
      return array( FALSE , 'Partenaire introuvable !' );
    }
    // Si mdp incorrect...
    if($DB_ROW['partenaire_password']!=Outil::crypter_mdp($password))
    {
      global $PAGE;
      return array( FALSE , 'Mot de passe incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
    }
    // Enregistrement d'un cookie sur le poste client servant à retenir le partenariat sélectionné si identification avec succès
    Cookie::definir( COOKIE_PARTENAIRE , $DB_ROW['partenaire_id'] , 31536000 /* 60*60*24*365 = 1 an */ );
    // Si on arrive ici c'est que l'identification s'est bien effectuée !
    return array( TRUE , $DB_ROW );
  }

  /**
   * Tester si les données transmises permettent d'authentifier un utilisateur (sauf webmestre & développeur).
   * 
   * En cas de connexion avec les identifiants SACoche, la reconnaissance s'effectue sur le couple login/password.
   * En cas de connexion depuis un service SSO extérieur type CAS, la reconnaissance s'effectue en comparant l'identifiant transmis (via $login) avec l'id ENT de jointure connu de SACoche.
   * En cas de connexion utilisant GEPI, la reconnaissance s'effectue en comparant le login GEPI transmis avec l'id Gepi de jointure connu de SACoche.
   * 
   * @param int       $BASE
   * @param string    $login
   * @param string    $password
   * @param string    $mode_connection 'normal' | 'cas' | 'shibboleth' | 'siecle' | 'vecteur_parent' | 'gepi' | 'ldap' (?)
   * @param string    $parent_nom      facultatif, seulement pour $mode_connection = 'vecteur_parent'
   * @param string    $parent_prenom   facultatif, seulement pour $mode_connection = 'vecteur_parent'
   * @return array(bool,array|string)   (TRUE,$DB_ROW) ou (FALSE,message_d_erreur)
   */
  public static function tester_authentification_utilisateur($BASE,$login,$password,$mode_connection,$parent_nom='',$parent_prenom='')
  {
    // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
    // Sauf pour une connexion à un ENT, car alors il a déjà fallu les charger pour récupérer les paramètres de connexion à l'ENT
    if( ($BASE) && ($mode_connection=='normal') )
    {
      DBextra::charger_parametres_mysql_supplementaires($BASE);
    }
    // Récupérer les données associées à l'utilisateur.
    $DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_donnees_utilisateur($mode_connection,$login,$parent_nom,$parent_prenom);
    // Si login (ou identifiant SSO) non trouvé...
    if(empty($DB_ROW))
    {
      switch($mode_connection)
      {
        case 'normal'         : $message = 'Nom d\'utilisateur incorrect !'; break;
        case 'cas'            : $message = 'Identification réussie mais identifiant CAS "'       .$login.'" inconnu dans SACoche (base n°'.$BASE.') !<br />Un administrateur doit renseigner que l\'identifiant ENT associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;des&nbsp;utilisateurs] [Élèves|Parents|Professeurs|Administrateurs], et indiquer pour votre compte dans le champ [Id.&nbsp;ENT] la valeur "'.$login.'".<br />Il a aussi la possibilité d\'utiliser le menu [Gestion&nbsp;des&nbsp;utilisateurs] [Rechercher&nbsp;un&nbsp;utilisateur] pour effectuer la modification voulue.'; break;
        case 'shibboleth'     : $message = 'Identification réussie mais identifiant Shibboleth "'.$login.'" inconnu dans SACoche (base n°'.$BASE.') !<br />Un administrateur doit renseigner que l\'identifiant ENT associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;des&nbsp;utilisateurs] [Élèves|Parents|Professeurs|Administrateurs], et indiquer pour votre compte dans le champ [Id.&nbsp;ENT] la valeur "'.$login.'".<br />Il a aussi la possibilité d\'utiliser le menu [Gestion&nbsp;des&nbsp;utilisateurs] [Rechercher&nbsp;un&nbsp;utilisateur] pour effectuer la modification voulue.'; break;
        case 'siecle'         : $message = 'Identification réussie mais identifiant Sconet "'    .$login.'" inconnu dans SACoche (base n°'.$BASE.') !<br />Un administrateur doit renseigner que l\'identifiant Sconet associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;des&nbsp;utilisateurs] [Élèves|Parents|Professeurs|Administrateurs], et indiquer pour votre compte dans le champ [Id.&nbsp;Sconet] la valeur "'.$login.'".<br />Il a aussi la possibilité d\'utiliser le menu [Gestion&nbsp;des&nbsp;utilisateurs] [Rechercher&nbsp;un&nbsp;utilisateur] pour effectuer la modification voulue.'; break;
        case 'vecteur_parent' : $message = 'Identification réussie mais compte parent'.                   ' inconnu dans SACoche (base n°'.$BASE.') !<br />Le compte SACoche d\'un responsable légal dont le nom est "'.$parent_nom.'", le prénom est "'.$parent_prenom.'", et ayant la charge d\'un enfant dont l\'identifiant Sconet est "'.$login.'", n\'a pas été trouvé.'; break;
        case 'gepi'           : $message = 'Identification réussie mais login GEPI "'            .$login.'" inconnu dans SACoche (base n°'.$BASE.') !<br />Un administrateur doit renseigner que l\'identifiant GEPI associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;des&nbsp;utilisateurs] [Élèves|Parents|Professeurs|Administrateurs], et indiquer pour votre compte dans le champ [Id.&nbsp;Gepi] la valeur "'.$login.'".<br />Il a aussi la possibilité d\'utiliser le menu [Gestion&nbsp;des&nbsp;utilisateurs] [Rechercher&nbsp;un&nbsp;utilisateur] pour effectuer la modification voulue.'; break;
      }
      return array( FALSE , $message );
    }
    // Blocage éventuel par le webmestre ou un administrateur ou l'automate
    LockAcces::stopper_si_blocage( $BASE , $DB_ROW['user_profil_sigle'] );
    // Si mdp incorrect...
    if( ($mode_connection=='normal') && ($DB_ROW['user_password']!=Outil::crypter_mdp($password)) )
    {
      global $PAGE;
      return array( FALSE , 'Mot de passe incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
    }
    // Si compte desactivé...
    if($DB_ROW['user_sortie_date']<=TODAY_MYSQL)
    {
      return array( FALSE , 'Identification réussie mais ce compte ('.$DB_ROW['user_login'].') est desactivé (sur la base n°'.$BASE.') !<br />Un administrateur doit ré-activer votre compte si cela est anormal&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;des&nbsp;utilisateurs] [Élèves|Parents|Professeurs], faire afficher les comptes anciens, cocher votre compte et cliquer sur "Réintégrer".<br />Il a aussi la possibilité d\'utiliser le menu [Gestion&nbsp;des&nbsp;utilisateurs] [Rechercher&nbsp;un&nbsp;utilisateur] pour effectuer la modification voulue.' );
    }
    // Mémoriser la date de la (dernière) connexion (pour les autres cas, sera enregistré lors de la confirmation de la prise en compte des infos CNIL).
    if( ($DB_ROW['user_connexion_date']!==NULL) || in_array($DB_ROW['user_profil_type'],array('webmestre','administrateur')) )
    {
      DB_STRUCTURE_PUBLIC::DB_enregistrer_date_connexion($DB_ROW['user_id']);
    }
    // Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
    Cookie::definir( COOKIE_STRUCTURE , $BASE , 31536000 /* 60*60*24*365 = 1 an */ );
    // Enregistrement d'un cookie sur le poste client servant à retenir le dernier mode de connexion utilisé si identification avec succès
    Cookie::definir( COOKIE_AUTHMODE , $mode_connection );
    // Si on arrive ici c'est que l'identification s'est bien effectuée !
    return array( TRUE , $DB_ROW );
  }

  /**
   * Enregistrer en session les informations authentifiant un utilisateur (sauf profils webmestre / développeur / partenaire).
   * 
   * @param int     $BASE
   * @param array   $DB_ROW   ligne issue de la table sacoche_user correspondant à l'utilisateur qui se connecte.
   * @return void
   */
  public static function initialiser_utilisateur($BASE,$DB_ROW)
  {
    // Récupérer et Enregistrer en session les données associées à l'établissement (indices du tableau de session en majuscules).
    $DB_TAB_PARAM = DB_STRUCTURE_PARAMETRE::DB_lister_parametres();
    $tab_type_tableau = array(
      'ETABLISSEMENT',
      'ENVELOPPE',
      'OFFICIEL',
      'CAS_SERVEUR',
    );
    foreach($DB_TAB_PARAM as $DB_ROW_PARAM)
    {
      $parametre_nom = Clean::upper($DB_ROW_PARAM['parametre_nom']);
      // Certains paramètres sont de type entier.
      $parametre_valeur = ctype_digit($DB_ROW_PARAM['parametre_valeur']) ? (int) $DB_ROW_PARAM['parametre_valeur'] : $DB_ROW_PARAM['parametre_valeur'] ;
      // Certains paramètres sont à enregistrer sous forme de tableau.
      $find = FALSE;
      foreach($tab_type_tableau as $key1)
      {
        $longueur_key1 = strlen($key1);
        if(substr($parametre_nom,0,$longueur_key1)==$key1)
        {
          $key2 = substr($parametre_nom,$longueur_key1+1);
          $_SESSION[$key1][$key2] = $parametre_valeur ;
          $find = TRUE;
          break;
        }
      }
      // Les autres paramètres sont à enregistrer tels quels.
      if(!$find)
      {
        $_SESSION[$parametre_nom] = $parametre_valeur ;
      }
    }
    // Enregistrer en session le numéro de la base.
    $_SESSION['BASE']                   = $BASE;
    // C'est un utilisateur d'un établissement.
    $_SESSION['USER_ETABLISSEMENT']     = TRUE;
    // Enregistrer en session les données associées au profil de l'utilisateur.
    $_SESSION['USER_PROFIL_SIGLE']      = $DB_ROW['user_profil_sigle'];
    $_SESSION['USER_PROFIL_TYPE']       = $DB_ROW['user_profil_type'];
    $_SESSION['USER_PROFIL_NOM_COURT']  = $DB_ROW['user_profil_nom_court_singulier'];
    $_SESSION['USER_PROFIL_NOM_LONG']   = $DB_ROW['user_profil_nom_long_singulier'];
    $_SESSION['USER_JOIN_GROUPES']      = $DB_ROW['user_profil_join_groupes'];   // Seuls les enseignants sont rattachés à des classes et groupes définis ; les autres le sont à tout l'établissement.
    $_SESSION['USER_JOIN_MATIERES']     = $DB_ROW['user_profil_join_matieres'] ; // Seuls les directeurs sont rattachés à toutes les matières ; les autres le sont à des matières définies.
    $_SESSION['USER_MDP_LONGUEUR_MINI'] = (int) $DB_ROW['user_profil_mdp_longueur_mini'];
    $_SESSION['USER_DUREE_INACTIVITE']  = (int) $DB_ROW['user_profil_duree_inactivite'];
    // Enregistrer en session les données personnelles de l'utilisateur.
    $_SESSION['USER_ID']                = (int) $DB_ROW['user_id'];
    $_SESSION['USER_SWITCH_ID']         = $DB_ROW['user_switch_id'];
    $_SESSION['USER_GENRE']             = $DB_ROW['user_genre'];
    $_SESSION['USER_NOM']               = $DB_ROW['user_nom'];
    $_SESSION['USER_PRENOM']            = $DB_ROW['user_prenom'];
    $_SESSION['USER_NAISSANCE_DATE']    = $DB_ROW['user_naissance_date'];
    $_SESSION['USER_EMAIL']             = $DB_ROW['user_email'];
    $_SESSION['USER_EMAIL_ORIGINE']     = $DB_ROW['user_email_origine'];
    $_SESSION['USER_LOGIN']             = $DB_ROW['user_login'];
    $_SESSION['USER_LANGUE']            = $DB_ROW['user_langue'];
    $_SESSION['USER_DALTONISME']        = $DB_ROW['user_daltonisme'];
    $_SESSION['USER_ID_ENT']            = $DB_ROW['user_id_ent'];
    $_SESSION['USER_ID_GEPI']           = $DB_ROW['user_id_gepi'];
    $_SESSION['USER_PARAM_ACCUEIL']     = $DB_ROW['user_param_accueil'];
    $_SESSION['USER_PARAM_MENU']        = $DB_ROW['user_param_menu'];
    $_SESSION['USER_PARAM_FAVORI']      = $DB_ROW['user_param_favori'];
    $_SESSION['ELEVE_CLASSE_ID']        = (int) $DB_ROW['eleve_classe_id'];
    $_SESSION['ELEVE_CLASSE_NOM']       = $DB_ROW['groupe_nom'];
    $_SESSION['DELAI_CONNEXION']        = (int) $DB_ROW['delai_connexion_secondes']; // Vaut (int)NULL = 0 à la 1e connexion, mais dans ce cas $_SESSION['FIRST_CONNEXION'] est testé avant.
    $_SESSION['FIRST_CONNEXION']        = ($DB_ROW['user_connexion_date']===NULL) ? TRUE : FALSE ;
    if( ($DB_ROW['user_connexion_date']===NULL) && ($DB_ROW['user_profil_type']!='administrateur') )
    {
      $_SESSION['STOP_CNIL'] = TRUE;
    }
    // Récupérer et Enregistrer en session les données des élèves associées à un responsable légal.
    if($_SESSION['USER_PROFIL_TYPE']=='parent')
    {
      $_SESSION['OPT_PARENT_ENFANTS']   = DB_STRUCTURE_COMMUN::DB_OPT_enfants_parent($_SESSION['USER_ID']);
      $_SESSION['OPT_PARENT_CLASSES']   = DB_STRUCTURE_COMMUN::DB_OPT_classes_parent($_SESSION['USER_ID']);
      $_SESSION['NB_ENFANTS'] = (is_array($_SESSION['OPT_PARENT_ENFANTS'])) ? count($_SESSION['OPT_PARENT_ENFANTS']) : 0 ;
      if($_SESSION['NB_ENFANTS'])
      {
        $_SESSION['ENFANT_NUM_RESP'] = array();
        foreach($_SESSION['OPT_PARENT_ENFANTS'] as $key => $tab)
        {
          $_SESSION['ENFANT_NUM_RESP'][$tab['valeur']] = $key+1;
        }
      }
      if( ($_SESSION['NB_ENFANTS']==1) && (is_array($_SESSION['OPT_PARENT_CLASSES'])) )
      {
        $_SESSION['ELEVE_CLASSE_ID']    = (int) $_SESSION['OPT_PARENT_CLASSES'][0]['valeur'];
        $_SESSION['ELEVE_CLASSE_NOM']   = $_SESSION['OPT_PARENT_CLASSES'][0]['texte'];
      }
    }
    // Récupérer et Enregistrer en session la liste des modules externes associés à un professeur
    if($_SESSION['USER_PROFIL_TYPE']=='professeur')
    {
      $_SESSION['MODULE'] = DB_STRUCTURE_PROFESSEUR::DB_lister_liaisons_user_module( $_SESSION['USER_ID'] );
    }
    // Récupérer et Enregistrer en session la liste des élèves associés à un professeur non rattaché à tous les élèves.
    if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') )
    {
      $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    }
    // Récupérer et Enregistrer en session les données associées aux profils utilisateurs d'un établissement, activés ou non.
    if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
    {
      $_SESSION['TAB_PROFILS_ADMIN'] = array( 'TYPE'=>array() , 'LOGIN_MODELE'=>array() , 'MDP_LONGUEUR_MINI'=>array() , 'DUREE_INACTIVITE'=>array() );
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_type,user_profil_login_modele,user_profil_mdp_longueur_mini,user_profil_mdp_date_naissance,user_profil_duree_inactivite' /*listing_champs*/ , FALSE /*only_actif*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $_SESSION['TAB_PROFILS_ADMIN']['TYPE']              [$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_type'];
        $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE']      [$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_login_modele'];
        $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] [$DB_ROW['user_profil_sigle']] = (int) $DB_ROW['user_profil_mdp_longueur_mini'];
        $_SESSION['TAB_PROFILS_ADMIN']['MDP_DATE_NAISSANCE'][$DB_ROW['user_profil_sigle']] = (int) $DB_ROW['user_profil_mdp_date_naissance'];
        $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE']  [$DB_ROW['user_profil_sigle']] = (int) $DB_ROW['user_profil_duree_inactivite'];
      }
    }
    // Récupérer et Enregistrer en session les noms des profils utilisateurs d'un établissement (activés) pour afficher les droits de certaines pages.
    else
    {
      $_SESSION['TAB_PROFILS_DROIT'] = array(
        'TYPE'             => array() ,
        'JOIN_GROUPES'     => array() ,
        'JOIN_MATIERES'    => array() ,
        'NOM_LONG_PLURIEL' => array(),
      );
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_type,user_profil_join_groupes,user_profil_join_matieres,user_profil_nom_long_pluriel' /*listing_champs*/ , TRUE /*only_actif*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $_SESSION['TAB_PROFILS_DROIT']['TYPE']            [$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_type'];
        $_SESSION['TAB_PROFILS_DROIT']['JOIN_GROUPES']    [$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_join_groupes'];
        $_SESSION['TAB_PROFILS_DROIT']['JOIN_MATIERES']   [$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_join_matieres'];
        $_SESSION['TAB_PROFILS_DROIT']['NOM_LONG_PLURIEL'][$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_nom_long_pluriel'];
      }
    }
    // Enregistrer en session les couleurs et paramètres pour les codes de notation, les états d'acquisition, les degrés de maîtrise
    SessionUser::memoriser_couleurs();
    // Fabriquer $_SESSION['NOTE'][i]['FICHIER'] en fonction de $_SESSION['USER_DALTONISME']
    // remarque : $_SESSION['USER_DALTONISME'] ne peut être utilisé que pour les profils élèves/parents/profs/directeurs, pas les admins ni le webmestre
    SessionUser::adapter_daltonisme();
    // Enregistrer en session le CSS personnalisé
    SessionUser::actualiser_style();
    // Enregistrer en session le menu personnalisé ; détection de la langue remis ici pour le cas de bascule entre comptes.
    Lang::setlocale( LC_MESSAGES, Lang::get_locale_used() );
    SessionUser::memoriser_menu();
    // Juste pour davantage de lisibilité si besoin de debug...
    ksort($_SESSION);
    // On profite de cet événement pour faire du ménage ou simuler une tâche planifiée
    SessionUser::cron();
    // Enfin, on écrit si besoin les fichiers des symboles personnalisées uploadés par l'établissement
    SessionUser::actualiser_fichiers_symboles_perso();
  }

  /**
   * Enregistrer en session les informations authentifiant le webmestre.
   * 
   * @param void
   * @return void
   */
  public static function initialiser_webmestre()
  {
    // Numéro de la base
    $_SESSION['BASE']                          = 0;
    // Ce n'est pas un utilisateur d'un établissement.
    $_SESSION['USER_ETABLISSEMENT']            = FALSE;
    // Données associées au profil de l'utilisateur.
    $_SESSION['USER_PROFIL_SIGLE']             = 'WBM';
    $_SESSION['USER_PROFIL_TYPE']              = 'webmestre';
    $_SESSION['USER_PROFIL_NOM_COURT']         = 'webmestre';
    $_SESSION['USER_PROFIL_NOM_LONG']          = 'responsable du serveur (webmestre)';
    $_SESSION['USER_MDP_LONGUEUR_MINI']        = 6;
    $_SESSION['USER_DUREE_INACTIVITE']         = 15;
    // Données personnelles de l'utilisateur.
    $_SESSION['USER_ID']                       = 0;
    $_SESSION['USER_NOM']                      = WEBMESTRE_NOM;
    $_SESSION['USER_PRENOM']                   = WEBMESTRE_PRENOM;
    $_SESSION['USER_LANGUE']                   = LOCALE_DEFAULT;
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']                   = 0;
    $_SESSION['ETABLISSEMENT']['DENOMINATION'] = 'Gestion '.HEBERGEUR_INSTALLATION;
    $_SESSION['CONNEXION_MODE']                = 'normal';
    // Enregistrer en session le menu personnalisé
    SessionUser::memoriser_menu();
  }

  /**
   * Enregistrer en session les informations authentifiant un développeur.
   * 
   * @param void
   * @return void
   */
  public static function initialiser_developpeur()
  {
    // Numéro de la base
    $_SESSION['BASE']                          = 0;
    // Ce n'est pas un utilisateur d'un établissement.
    $_SESSION['USER_ETABLISSEMENT']            = FALSE;
    // Données associées au profil de l'utilisateur.
    $_SESSION['USER_PROFIL_SIGLE']             = 'DVL';
    $_SESSION['USER_PROFIL_TYPE']              = 'developpeur';
    $_SESSION['USER_PROFIL_NOM_COURT']         = 'devel';
    $_SESSION['USER_PROFIL_NOM_LONG']          = 'développeur SACoche';
    $_SESSION['USER_MDP_LONGUEUR_MINI']        = 6;
    $_SESSION['USER_DUREE_INACTIVITE']         = 15;
    // Données personnelles de l'utilisateur.
    $_SESSION['USER_ID']                       = 0;
    $_SESSION['USER_NOM']                      = 'SACoche';
    $_SESSION['USER_PRENOM']                   = 'développeur';
    $_SESSION['USER_LANGUE']                   = LOCALE_DEFAULT;
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']                   = 0;
    $_SESSION['ETABLISSEMENT']['DENOMINATION'] = HEBERGEUR_INSTALLATION;
    $_SESSION['CONNEXION_MODE']                = 'normal';
    // Enregistrer en session le menu personnalisé
    SessionUser::memoriser_menu();
  }

  /**
   * Enregistrer en session les informations authentifiant un partenaire.
   * 
   * @param array   $DB_ROW   ligne issue de la table sacoche_partenaire correspondant à l'utilisateur qui se connecte.
   * @return void
   */
  public static function initialiser_partenaire($DB_ROW)
  {
    // Numéro de la base
    $_SESSION['BASE']                          = 0;
    // Ce n'est pas un utilisateur d'un établissement.
    $_SESSION['USER_ETABLISSEMENT']            = FALSE;
    // Données associées au profil de l'utilisateur.
    $_SESSION['USER_PROFIL_SIGLE']             = 'ENT';
    $_SESSION['USER_PROFIL_TYPE']              = 'partenaire';
    $_SESSION['USER_PROFIL_NOM_COURT']         = 'partenaire';
    $_SESSION['USER_PROFIL_NOM_LONG']          = 'partenariat conventionné (ENT)';
    $_SESSION['USER_MDP_LONGUEUR_MINI']        = 6;
    $_SESSION['USER_DUREE_INACTIVITE']         = 15;
    // Données personnelles de l'utilisateur.
    $_SESSION['USER_ID']                       = (int) $DB_ROW['partenaire_id'];
    $_SESSION['USER_NOM']                      = $DB_ROW['partenaire_nom'];
    $_SESSION['USER_PRENOM']                   = $DB_ROW['partenaire_prenom'];
    $_SESSION['USER_LANGUE']                   = LOCALE_DEFAULT;
    $_SESSION['USER_CONNECTEURS']              = $DB_ROW['partenaire_connecteurs'];
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']                   = 0;
    $_SESSION['ETABLISSEMENT']['DENOMINATION'] = $DB_ROW['partenaire_denomination'];
    $_SESSION['CONNEXION_MODE']                = 'normal';
    // Enregistrer en session le menu personnalisé
    SessionUser::memoriser_menu();
  }

  /**
   * Compléter la session avec les couleurs et paramètres pour les codes de notation, les états d'acquisition, les degrés de maîtrise
   * 
   * @param void
   * @return void
   */
  public static function memoriser_couleurs()
  {
    // Codes de notation - On récupère aussi les inactifs au cas où ils auraient été utilisés puis retirés.
    $_SESSION['NOTE'] = array();
    $_SESSION['NOTE_ACTIF'] = array();
    $DB_TAB_NOTE = DB_STRUCTURE_PARAMETRE::DB_lister_parametres_note( TRUE /*priority_actifs*/ );
    foreach($DB_TAB_NOTE as $DB_ROW_NOTE)
    {
      $_SESSION['NOTE'][(int)$DB_ROW_NOTE['note_id']] = array(
        'ACTIF'   => (int)$DB_ROW_NOTE['note_actif'],
        'VALEUR'  => (int)$DB_ROW_NOTE['note_valeur'],
        'IMAGE'   => $DB_ROW_NOTE['note_image'],
        'SIGLE'   => $DB_ROW_NOTE['note_sigle'],
        'LEGENDE' => $DB_ROW_NOTE['note_legende'],
        'CLAVIER' => $DB_ROW_NOTE['note_clavier'],
      );
      if($DB_ROW_NOTE['note_actif'])
      {
        $_SESSION['NOTE_ACTIF'][] = (int)$DB_ROW_NOTE['note_id'];
      }
    }
    // États d'acquisition - Les couleurs pour les daltoniens servent aussi pour les impressions PDF en niveau de gris
    $_SESSION['ACQUIS'] = array();
    $DB_TAB_ACQUIS = DB_STRUCTURE_PARAMETRE::DB_lister_parametres_acquis( TRUE /*only_actifs*/ );
    foreach($DB_TAB_ACQUIS as $key => $DB_ROW_ACQUIS)
    {
      $_SESSION['ACQUIS'][(int)$DB_ROW_ACQUIS['acquis_id']] = array(
        'ACTIF'     => (int)$DB_ROW_ACQUIS['acquis_actif'],
        'SEUIL_MIN' => (int)$DB_ROW_ACQUIS['acquis_seuil_min'],
        'SEUIL_MAX' => (int)$DB_ROW_ACQUIS['acquis_seuil_max'],
        'VALEUR'    => (int)$DB_ROW_ACQUIS['acquis_valeur'],
        'COULEUR'   => $DB_ROW_ACQUIS['acquis_couleur'],
        'GRIS'      => SessionUser::$tab_dalton_css[$_SESSION['NOMBRE_ETATS_ACQUISITION']][$key],
        'SIGLE'     => $DB_ROW_ACQUIS['acquis_sigle'],
        'LEGENDE'   => $DB_ROW_ACQUIS['acquis_legende'],
      );
    }
    // Degrés de maîtrise - Pour l'instant juste des couleurs (plus marquées que les originales en 4 colonnes) afin de permettre l'initialisation du style CSS
    $_SESSION['LIVRET'] = array(
      1 => array( 'COULEUR' => '#E5F4FF' , 'GRIS' => SessionUser::$tab_dalton_css[4][3] , 'POINTS' => 10 ), // TSV =(204; 10;100)
      2 => array( 'COULEUR' => '#91CBF2' , 'GRIS' => SessionUser::$tab_dalton_css[4][2] , 'POINTS' => 25 ), // TSV =(204; 40; 95)
      3 => array( 'COULEUR' => '#44A7E5' , 'GRIS' => SessionUser::$tab_dalton_css[4][1] , 'POINTS' => 40 ), // TSV =(204; 70; 90)
      4 => array( 'COULEUR' => '#0082D8' , 'GRIS' => SessionUser::$tab_dalton_css[4][0] , 'POINTS' => 50 ), // TSV =(204;100; 85)
    );
  }

  /**
   * Compléter la session avec les informations de style dépendant du daltonisme + des choix paramétrés au niveau de l'établissement (couleurs, codes de notation).
   * 
   * @param void
   * @return void
   */
  public static function adapter_daltonisme()
  {
    // pour les codes de notation
    $numero = 0;
    foreach( $_SESSION['NOTE'] as $note_id => $tab_note_info )
    {
      if( $_SESSION['USER_DALTONISME'] && $tab_note_info['ACTIF'] )
      {
        $numero++;
        $_SESSION['NOTE'][$note_id]['FICHIER'] = Html::note_src_daltonisme($numero);
      }
      else
      {
        $_SESSION['NOTE'][$note_id]['FICHIER'] = Html::note_src_couleur($tab_note_info['IMAGE']);
      }
    }
  }

  /**
   * Compléter la session avec les informations de style dépendant des choix paramétrés au niveau de l'établissement (couleurs, codes de notation).
   * 
   * @param void
   * @return void
   */
  public static function actualiser_style()
  {
    $key_couleur = $_SESSION['USER_DALTONISME'] ? 'GRIS' : 'COULEUR' ;
    $_SESSION['CSS']  = '';
    // codes de notation
    foreach( $_SESSION['NOTE'] as $note_id => $tab_note_info )
    {
      $_SESSION['CSS'] .= 'table.scor_eval tbody.h td input.N'.$note_id.' {background:#FFF url('.$tab_note_info['FICHIER'].') no-repeat center center;}'.NL;
      $_SESSION['CSS'] .= 'table.scor_eval tbody.v td input.N'.$note_id.' {background:#FFF url('.str_replace(array('/h/','/h_'),array('/v/','/v_'),$tab_note_info['FICHIER']).') no-repeat center center;}'.NL;
    }
    // couleurs des états d'acquisition
    foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
    {
      $_SESSION['CSS'] .= 'table th.A'.$acquis_id.' , table td.A'.$acquis_id.' , div.A'.$acquis_id.' ,span.A'.$acquis_id.' ,label.A'.$acquis_id.' {background-color:'.$tab_acquis_info[$key_couleur].'}'.NL;
    }
    // couleurs des degrés de maîtrise
    foreach( $_SESSION['LIVRET'] as $maitrise_id => $tab_maitrise_info )
    {
      $_SESSION['CSS'] .= 'table th.M'.$maitrise_id.' , table td.M'.$maitrise_id.' , div.M'.$maitrise_id.' ,span.M'.$maitrise_id.' ,label.M'.$maitrise_id.' {background-color:'.$tab_maitrise_info['COULEUR'].'}'.NL;
    }
  }

  /**
   * Enregistrer en session le menu selon le profil et éventuellement les droits de l'utilisateur ou les paramétrages de l'administrateur.
   * 
   * @param void
   * @return void
   */
  public static function memoriser_menu()
  {
    if( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent','professeur','directeur')) )
    {
      if( is_null($_SESSION['USER_PARAM_MENU']) )
      {
        // L'utilisateur n'a pas enregistré de préférences : on surcharge par celles éventuelles de l'admin
        $DB_ROW = DB_STRUCTURE_PARAMETRE::DB_recuperer_parametres_profil($_SESSION['USER_PROFIL_TYPE']);
        $_SESSION['USER_PARAM_MENU']    = $DB_ROW['profil_param_menu'];
        $_SESSION['USER_PARAM_FAVORI']  = $DB_ROW['profil_param_favori'];
        $_SESSION['USER_PARAM_ORIGINE'] = is_null($_SESSION['USER_PARAM_MENU']) ? 'aucun' : 'admin' ;
      }
      else
      {
        $_SESSION['USER_PARAM_ORIGINE'] = 'prof';
      }
    }
    $line_height = 43+1; // @see ./_css/style.css --> #menu li li a {line-height:43px}
    $numero_menu = 0;
    require(CHEMIN_DOSSIER_MENUS.'menu_'.$_SESSION['USER_PROFIL_TYPE'].'.php'); // récupère $tab_menu & $tab_sous_menu
    $_SESSION['MENU'] = '<ul id="menu"><li><a class="boussole" href="#">'.html(Lang::_("MENU")).'</a><ul>'.NL;
    $nombre_menu = count($tab_menu);
    $tab_param_menu = explode(',',$_SESSION['USER_PARAM_MENU']);
    foreach($tab_menu as $menu_id => $menu_titre)
    {
      $disabled_menu = in_array($menu_id,$tab_param_menu) ? ' disabled' : '' ;
      $_SESSION['MENU'] .= '<li><a class="fleche'.$disabled_menu.'" href="#">'.html($menu_titre).'</a><ul>'.NL;
      $nombre_sous_menu = count($tab_sous_menu[$menu_id]);
      $premier_sous_menu = TRUE;
      foreach($tab_sous_menu[$menu_id] as $sous_menu_id => $tab)
      {
        $disabled_sousmenu = ( $disabled_menu || in_array($sous_menu_id,$tab_param_menu) || isset($tab['disabled']) ) ? ' disabled' : '' ;
        $nombre_cases_decalage = min( $numero_menu , $numero_menu-($nombre_menu-$nombre_sous_menu) );
        $style = ($premier_sous_menu && $nombre_cases_decalage) ? ' style="margin-top:-'.($nombre_cases_decalage*$line_height).'px"' : '' ;
        $_SESSION['MENU'] .= '<li><a class="'.$tab['class'].$disabled_sousmenu.'"'.$style.' href="./index.php?'.$tab['href'].'">'.html($tab['texte']).'</a></li>'.NL;
        $premier_sous_menu = FALSE;
      }
      $_SESSION['MENU'] .= '</ul></li>'.NL;
      $numero_menu++;
    }
    $_SESSION['MENU'] .= '</ul></li></ul>'.NL;
    // On profite d'avoir le fichier du menu dispo pour s'occuper aussi des favoris en page d'accueil.
    $_SESSION['FAVORI'] = '';
    if( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent','professeur','directeur')) && $_SESSION['USER_PARAM_FAVORI'] )
    {
      $tab_param_favori = explode(',',$_SESSION['USER_PARAM_FAVORI']);
      $_SESSION['FAVORI'] .= '<div class="sousmenu">';
      foreach($tab_menu as $menu_id => $menu_titre)
      {
        foreach($tab_sous_menu[$menu_id] as $sous_menu_id => $tab)
        {
          if(in_array($sous_menu_id,$tab_param_favori))
          {
            $_SESSION['FAVORI'] .= '<a href="./index.php?'.$tab['href'].'">'.html($tab['texte']).'</a>';
          }
        }
      }
      $_SESSION['FAVORI'] .= '</div>'.NL;
    }
  }

  /**
   * Fichiers des symboles personnalisées uploadés par l'établissement à mettre en place si besoin
   * 
   * @param void
   * @return void
   */
  public static function actualiser_fichiers_symboles_perso()
  {
    $DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images_notes();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $image_nom = 'upload_'.$DB_ROW['image_note_id'];
        FileSystem::ecrire_fichier( FileSystem::chemin_fichier_symbole($image_nom,'h','perso') , base64_decode($DB_ROW['image_contenu_h']) );
        FileSystem::ecrire_fichier( FileSystem::chemin_fichier_symbole($image_nom,'v','perso') , base64_decode($DB_ROW['image_contenu_v']) );
      }
    }
  }

  /**
   * Tâches pseudo-planifiées exécutées lors de la connexion d'un utilisateur d'un établissement
   * 
   * @param void
   * @return void
   */
  public static function cron()
  {
    // On essaye de faire en sorte que plusieurs connexions ne lancent pas ces procédures simultanément
    $fichier_lock = CHEMIN_DOSSIER_TMP.'lock.txt';
    if(!file_exists($fichier_lock))
    {
      // On écrit un marqueur
      FileSystem::ecrire_fichier($fichier_lock,'');
      // On vérifie que des sous-dossiers ajoutés ultétieurement existent
      FileSystem::verifier_existence_dossiers($_SESSION['BASE']);
      // On efface les fichiers temporaires obsolètes
      FileSystem::nettoyer_fichiers_temporaires_commun();
      FileSystem::nettoyer_fichiers_temporaires_etablissement($_SESSION['BASE']);
      // On allège le fichier de logs si nécessaire
      SACocheLog::alleger($_SESSION['BASE']);
      // On rend visibles les notifications en attente et on supprime les notifications plus anciennes
      Sesamail::envoyer_notifications();
      DB_STRUCTURE_NOTIFICATION::DB_supprimer_log_vus_anciens();
      // On efface le marqueur
      FileSystem::supprimer_fichier( $fichier_lock , TRUE /*verif_exist*/ );
    }
    // Si le fichier témoin du nettoyage existe, on vérifie que sa présence n'est pas anormale (cela s'est déjà produit...)
    else
    {
      if( $_SERVER['REQUEST_TIME'] - filemtime($fichier_lock) > 30 )
      {
        FileSystem::supprimer_fichier($fichier_lock);
      }
    }
  }

}
?>