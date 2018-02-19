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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$f_action                     = (isset($_POST['f_action']))                     ? Clean::texte($_POST['f_action'])                      : '';
$f_annee                      = (isset($_POST['f_annee']))                      ? Clean::entier($_POST['f_annee'])                      : -1;
$f_convention_id              = (isset($_POST['f_convention_id']))              ? Clean::entier($_POST['f_convention_id'])              : 0 ;
$f_connexion_mode             = (isset($_POST['f_connexion_mode']))             ? Clean::texte($_POST['f_connexion_mode'])              : '';
$f_connexion_ref              = (isset($_POST['f_connexion_ref']))              ? Clean::texte($_POST['f_connexion_ref'])               : '';
$cas_serveur_host             = (isset($_POST['cas_serveur_host']))             ? Clean::texte($_POST['cas_serveur_host'])              : '';
$cas_serveur_port             = (isset($_POST['cas_serveur_port']))             ? Clean::entier($_POST['cas_serveur_port'])             : 0 ;
$cas_serveur_root             = (isset($_POST['cas_serveur_root']))             ? Clean::texte($_POST['cas_serveur_root'])              : '';
$cas_serveur_url_login        = (isset($_POST['cas_serveur_url_login']))        ? Clean::texte($_POST['cas_serveur_url_login'])         : '';
$cas_serveur_url_logout       = (isset($_POST['cas_serveur_url_logout']))       ? Clean::texte($_POST['cas_serveur_url_logout'])        : '';
$cas_serveur_url_validate     = (isset($_POST['cas_serveur_url_validate']))     ? Clean::texte($_POST['cas_serveur_url_validate'])      : '';
$cas_serveur_verif_certif_ssl = (isset($_POST['cas_serveur_verif_certif_ssl'])) ? Clean::entier($_POST['cas_serveur_verif_certif_ssl']) : NULL;
$serveur_host_subdomain       = (isset($_POST['serveur_host_subdomain']))       ? Clean::texte($_POST['serveur_host_subdomain'])        : '';
$serveur_host_domain          = (isset($_POST['serveur_host_domain']))          ? Clean::texte($_POST['serveur_host_domain'])           : '';
$serveur_port                 = (isset($_POST['serveur_port']))                 ? Clean::entier($_POST['serveur_port'])                 : 0 ;
$gepi_saml_url                = (isset($_POST['gepi_saml_url']))                ? Clean::texte($_POST['gepi_saml_url'])                 : '';
$gepi_saml_rne                = (isset($_POST['gepi_saml_rne']))                ? Clean::uai($_POST['gepi_saml_rne'])                   : '';
$gepi_saml_certif             = (isset($_POST['gepi_saml_certif']))             ? Clean::texte($_POST['gepi_saml_certif'])              : '';
$f_first_time                 = (isset($_POST['f_first_time']))                 ? Clean::texte($_POST['f_first_time'])                  : '';

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mode de connexion (normal, SSO...)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($f_action=='enregistrer_mode_identification')
{

  if(!isset($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]))
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }

  if( ($f_connexion_mode=='cas') && ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_host_subdomain']=='*') && !$serveur_host_subdomain )
  {
    Json::end( FALSE , 'Sous-domaine manquant !' );
  }

  if( ($f_connexion_mode=='cas') && ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_port']=='*') && !$serveur_port )
  {
    Json::end( FALSE , 'Port manquant !' );
  }

  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);

  if( ($f_connexion_mode=='normal') || ($f_connexion_mode=='shibboleth') )
  {
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    Json::end( TRUE );
  }

  if($f_connexion_mode=='cas')
  {
    // Soit le host est saisi manuellement, soit il faut le recomposer si sous-domaine saisi (dans la situation majoritaire où il n'y a pas de sous-domaine variable, le résultat est le même)
    $cas_serveur_host = ($f_connexion_nom=='perso') ? $cas_serveur_host : ( ($serveur_host_subdomain!='') ? ( (substr($serveur_host_subdomain,-1)!='.') ? $serveur_host_subdomain.'.'.$serveur_host_domain : $serveur_host_subdomain.$serveur_host_domain ) : $serveur_host_domain ) ;
    // Cas du port
    $cas_serveur_port = ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_port']!='*') ? $cas_serveur_port : $serveur_port ;
    // Vérifier les paramètres CAS en reprenant le code de phpCAS
    if ( empty($cas_serveur_host) || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/',$cas_serveur_host) )
    {
      Json::end( FALSE , 'Syntaxe du domaine incorrecte !' );
    }
    if ( ($cas_serveur_port == 0) || !is_int($cas_serveur_port) )
    {
      Json::end( FALSE , 'Numéro du port incorrect !' );
    }
    if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/',$cas_serveur_root) )
    {
      Json::end( FALSE , 'Syntaxe du chemin incorrecte !' );
    }
    // Expression régulière pour tester une URL (pas trop compliquée)
    $masque = '#^http(s)?://[\w-]+[\w.-]+\.[a-zA-Z]{2,6}(:[0-9]+)?#';
    if ( $cas_serveur_url_login && !preg_match($masque,$cas_serveur_url_login) )
    {
      Json::end( FALSE , 'Syntaxe URL login incorrecte !' );
    }
    if ( $cas_serveur_url_logout && !preg_match($masque,$cas_serveur_url_logout) )
    {
      Json::end( FALSE , 'Syntaxe URL logout incorrecte !' );
    }
    if ( $cas_serveur_url_validate && !preg_match($masque,$cas_serveur_url_validate) )
    {
      Json::end( FALSE , 'Syntaxe URL validate incorrecte !' );
    }
    if( is_null($cas_serveur_verif_certif_ssl) )
    {
      Json::end( FALSE , 'Paramètre vérif. certificat SSL manquant !' );
    }
    // Deux tests sauf pour les établissements destinés à tester les connecteurs ENT
    if( !IS_HEBERGEMENT_SESAMATH || ($_SESSION['BASE']<CONVENTION_ENT_ID_ETABL_MAXI) )
    {
      // Ne pas dupliquer en paramétrage CAS-perso un paramétrage CAS-ENT existant (utiliser la connexion CAS officielle)
      if($f_connexion_nom=='perso')
      {
        foreach($tab_serveur_cas as $cas_nom => $tab_cas_param)
        {
          if($cas_nom)
          {
            $is_param_defaut_identiques = ( (strpos($cas_serveur_host,$tab_cas_param['serveur_host_domain'])!==FALSE) && ($cas_serveur_root==$tab_cas_param['serveur_root']) ) ? TRUE : FALSE ; // Pas de test sur le sous-domaine ni le port car ils peuvent varier
            $is_param_force_identiques  = ( ($cas_serveur_url_login!='') && ( ($cas_serveur_url_login==$tab_cas_param['serveur_url_login']) || (strpos($cas_serveur_url_login,$tab_cas_param['serveur_host_domain'].':'.$tab_cas_param['serveur_port'].'/'.$tab_cas_param['serveur_root'])!==FALSE) ) ) ? TRUE : FALSE ;
            if( $is_param_defaut_identiques || $is_param_force_identiques )
            {
              Json::end( FALSE , 'Paramètres d\'un ENT référencé : sélectionnez-le !' );
            }
          }
        }
      }
      // Sur le serveur Sésamath, ne pas autoriser un paramétrage CAS correspondant à un hébergement académique (ne devrait pas se produire, Sésamath n'hébergeant pas ces établissements).
      else if(IS_HEBERGEMENT_SESAMATH)
      {
        if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
        {
          Json::end( FALSE , 'Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !' );
        }
        require(CHEMIN_FICHIER_WS_SESAMATH_ENT); // Charge les tableaux   $tab_connecteurs_hebergement & $tab_connecteurs_convention
        if( isset($tab_connecteurs_hebergement[$f_connexion_ref]) )
        {
          Json::end( FALSE , 'Paramètres d\'un serveur CAS à utiliser sur l\'hébergement académique dédié !' );
        }
      }
    }
    // C'est ok
    $tab_parametres = array(
      'connexion_mode'               => $f_connexion_mode,
      'connexion_nom'                => $f_connexion_nom,
      'connexion_departement'        => $f_connexion_departement,
      'cas_serveur_host'             => $cas_serveur_host,
      'cas_serveur_port'             => $cas_serveur_port,
      'cas_serveur_root'             => $cas_serveur_root,
      'cas_serveur_url_login'        => $cas_serveur_url_login,
      'cas_serveur_url_logout'       => $cas_serveur_url_logout,
      'cas_serveur_url_validate'     => $cas_serveur_url_validate,
      'cas_serveur_verif_certif_ssl' => $cas_serveur_verif_certif_ssl,
    );
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( $tab_parametres );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']                  = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']                   = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT']           = $f_connexion_departement;
    $_SESSION['CAS_SERVEUR']['HOST']             = $cas_serveur_host;
    $_SESSION['CAS_SERVEUR']['PORT']             = $cas_serveur_port;
    $_SESSION['CAS_SERVEUR']['ROOT']             = $cas_serveur_root;
    $_SESSION['CAS_SERVEUR']['URL_LOGIN']        = $cas_serveur_url_login;
    $_SESSION['CAS_SERVEUR']['URL_LOGOUT']       = $cas_serveur_url_logout;
    $_SESSION['CAS_SERVEUR']['URL_VALIDATE']     = $cas_serveur_url_validate;
    $_SESSION['CAS_SERVEUR']['VERIF_CERTIF_SSL'] = $cas_serveur_verif_certif_ssl;
    Json::end( TRUE );
  }

  if($f_connexion_mode=='gepi')
  {
    // Vérifier les paramètres GEPI-SAML
    // Le RNE n'étant pas obligatoire, et pas forcément un vrai RNE dans Gepi (pour les établ sans UAI, c'est un identifiant choisi...), on ne vérifie rien.
    // Pas de vérif particulière de l'empreinte du certificat non plus, ne sachant pas s'il peut y avoir plusieurs formats.
    // Donc on va se contenter de vraiment vérifier l'URL de Gepi via une requête cURL
    if(strlen($gepi_saml_url)<8)
    {
      Json::end( FALSE , 'Adresse de GEPI manquante !' );
    }
    if(empty($gepi_saml_certif))
    {
      Json::end( FALSE , 'Signature (empreinte du certificat) manquante !' );
    }
    $gepi_saml_url = (substr($gepi_saml_url,-1)=='/') ? substr($gepi_saml_url,0,-1) : $gepi_saml_url ;
    $fichier_distant = cURL::get_contents($gepi_saml_url.'/bandeau.css'); // Le mieux serait d'appeler le fichier du web-services... si un jour il y en a un...
    if(substr($fichier_distant,0,6)=='Erreur')
    {
      Json::end( FALSE , 'Adresse de Gepi incorrecte [ '.$fichier_distant.' ] !' );
    }
    // C'est ok
    DB_STRUCTURE_PARAMETRE::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement,'gepi_url'=>$gepi_saml_url,'gepi_rne'=>$gepi_saml_rne,'gepi_certificat_empreinte'=>$gepi_saml_certif) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    $_SESSION['GEPI_URL'] = $gepi_saml_url;
    $_SESSION['GEPI_RNE'] = $gepi_saml_rne;
    $_SESSION['GEPI_CERTIFICAT_EMPREINTE'] = $gepi_saml_certif;
    Json::end( TRUE );
  }

}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( IS_HEBERGEMENT_SESAMATH && ($f_action=='ajouter_convention') && $f_connexion_mode && $f_connexion_ref && in_array($f_annee,array(0,1)) )
{
  if( ($f_connexion_mode!='cas') || (!isset($tab_connexion_info['cas'][$f_connexion_ref])) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Extraire les infos
  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);
  $date_debut_mysql = To::jour_debut_annee_scolaire('mysql',$f_annee);
  $date_fin_mysql   = To::jour_fin_annee_scolaire(  'mysql',$f_annee);
  // Vérifier que la convention n'existe pas déjà
  DBextra::charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  if(DB_WEBMESTRE_ADMINISTRATEUR::DB_tester_convention_precise( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql ))
  {
    Json::end( FALSE , 'Convention déjà existante pour ce service sur cette période !' );
  }
  // Insérer l'enregistrement
  $convention_id = DB_WEBMESTRE_ADMINISTRATEUR::DB_ajouter_convention( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql , $date_fin_mysql );
  // Afficher le retour
  $tr = '<tr id="id_'.$convention_id.'" class="new">'
      .   '<td>'.html($f_connexion_nom).'</td>'
      .   '<td>du '.To::date_mysql_to_french($date_debut_mysql).' au '.To::date_mysql_to_french($date_fin_mysql).'</td>'
      .   '<td>'.TODAY_FR.'</td>'
      .   '<td class="br">Non réceptionné</td>'
      .   '<td class="br">Non réceptionné</td>'
      .   '<td class="br">Non</td>'
      .   '<td class="nu"><q class="voir_archive" title="Récupérer / Imprimer les documents associés."></q></td>'
      . '</tr>';
  Json::end( TRUE , array( 'convention_id'=>$convention_id , 'tr'=>$tr ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer les documents associés à une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( IS_HEBERGEMENT_SESAMATH && ($f_action=='imprimer_documents') && $f_convention_id && in_array($f_first_time,array('oui','non')) )
{
  // Récupération et vérification des infos de la convention
  DBextra::charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  $DB_ROW = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_convention($f_convention_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Convention non trouvée !' );
  }
  if($DB_ROW['sacoche_base']!=$_SESSION['BASE'])
  {
    Json::end( FALSE , 'Convention d\'une autre structure !' );
  }
  // Coordonnées de l'établissement
  $tab_etabl_coords = array( 0 => $_SESSION['ETABLISSEMENT']['DENOMINATION'] );
  if($_SESSION['ETABLISSEMENT']['ADRESSE1'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE2'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE3'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
  if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords[] = 'Tél : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
  if($_SESSION['ETABLISSEMENT']['FAX'])       { $tab_etabl_coords[] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
  if($_SESSION['ETABLISSEMENT']['COURRIEL'])  { $tab_etabl_coords[] = 'Mél : '.$_SESSION['ETABLISSEMENT']['COURRIEL']; } // @see http://www.langue-fr.net/Courriel-E-Mail-Mel | https://fr.wiktionary.org/wiki/m%C3%A9l | https://fr.wikipedia.org/wiki/Courrier_%C3%A9lectronique#.C3.89volution_des_termes_employ.C3.A9s_par_les_utilisateurs
  if($_SESSION['ETABLISSEMENT']['URL'])       { $tab_etabl_coords[] = 'Web : '.$_SESSION['ETABLISSEMENT']['URL']; }
  // Coordonnées du contact référent
  $DB_ROW2 = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_contact_infos($_SESSION['BASE']);
  $tab_etabl_coords[] = '';
  $tab_etabl_coords[] = 'Contact référent pour SACoche :';
  $tab_etabl_coords[] = $DB_ROW2['structure_contact_nom'].' '.$DB_ROW2['structure_contact_prenom'];
  $tab_etabl_coords[] = 'Mél : '.$DB_ROW2['structure_contact_courriel']; // @see http://www.langue-fr.net/Courriel-E-Mail-Mel | https://fr.wiktionary.org/wiki/m%C3%A9l | https://fr.wikipedia.org/wiki/Courrier_%C3%A9lectronique#.C3.89volution_des_termes_employ.C3.A9s_par_les_utilisateurs
  // référence du connecteur
  $connecteur_ref = $_SESSION['BASE'].' . '.$f_convention_id.' . '.$DB_ROW['connexion_nom'];
  // Charge $tab_moratoire_conventions_etablissements
  if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
  {
    Json::end( FALSE , 'Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !' );
  }
  require(CHEMIN_FICHIER_WS_SESAMATH_ENT);
  $montant = is_moratoire($DB_ROW['convention_date_debut']) ? 0 : 50 ;
  // Charge la classe PDF
  require(CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_conventions_sacoche_etablissement_pdf.class.php');
  //
  // Imprimer le contrat.
  //
  $contrat_PDF = new PDF_convention( NULL /*make_officiel*/ , 'portrait' /*orientation*/ , 15 /*marge_gauche*/ , 15 /*marge_droite*/ , 7.5 /*marge_haut*/ , 10 /*marge_bas*/ , 'oui' /*couleur*/ );
  // Boucle pour l'exemplaire à conserver et l'exemplaire à renvoyer
  for( $numero_exemplaire=0 ; $numero_exemplaire<2 ; $numero_exemplaire++ )
  {
    $contrat_PDF->initialiser();
    $contrat_PDF->contrat_entete_sesamath();
    $contrat_PDF->bloc_etablissement( $f_convention_id , $tab_etabl_coords );
    $contrat_PDF->contrat_type_exemplaire( $numero_exemplaire );
    $contrat_PDF->contrat_objet_et_reference( $connecteur_ref );
    $contrat_PDF->contrat_texte( $montant , $DB_ROW );
    $contrat_PDF->contrat_signature();
    $contrat_PDF->contrat_numero_page( 1 );
    $contrat_PDF->contrat_annexe();
    $contrat_PDF->contrat_numero_page( 2 );
  }
  // On enregistre la sortie PDF
  $contrat_fichier_nom = 'convention_contrat_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.pdf';
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$contrat_fichier_nom , $contrat_PDF );
  //
  // Imprimer la facture.
  //
  $facture_PDF = new PDF_convention( NULL /*make_officiel*/ , 'portrait' /*orientation*/ , 15 /*marge_gauche*/ , 15 /*marge_droite*/ , 7.5 /*marge_haut*/ , 10 /*marge_bas*/ , 'oui' /*couleur*/ );
  $facture_PDF->initialiser();
  $facture_PDF->facture_entete_sesamath();
  $facture_PDF->bloc_etablissement( $f_convention_id , $tab_etabl_coords );
  $facture_PDF->facture_lieu_et_date( $DB_ROW );
  $facture_PDF->facture_objet_et_reference_periode_et_cout( $DB_ROW , $connecteur_ref , $montant );
  $facture_PDF->facture_dates_activation_et_reglement( $DB_ROW , $montant );
  if($montant)
  {
    $facture_PDF->facture_references_bancaires();
  }
  // On enregistre la sortie PDF
  $facture_fichier_nom = 'convention_facture_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.pdf';
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$facture_fichier_nom , $facture_PDF );
  //
  // Envoyer un courriel au contact.
  //
  if($f_first_time=='oui')
  {
    $titre = 'Convention connecteur ENT établissement - Documents générés';
    $texte = 'Bonjour '.$DB_ROW2['structure_contact_prenom'].' '.$DB_ROW2['structure_contact_nom'].','."\r\n";
    $texte.= "\r\n";
    $texte.= 'Vous venez de générer les documents associés à une convention pour un connecteur ENT.'."\r\n";
    $texte.= '- Référence : '.$connecteur_ref."\r\n";
    $texte.= '- Établissement : '.$_SESSION['ETABLISSEMENT']['DENOMINATION']."\r\n";
    $texte.= '- Période : du '.To::date_mysql_to_french($DB_ROW['convention_date_debut']).' au '.To::date_mysql_to_french($DB_ROW['convention_date_fin'])."\r\n";
    $texte.= "\r\n";
    $texte.= 'Ces documents vous resteront accessibles en vous connectant comme administrateur puis en vous rendant dans le menu [Paramétrages établissement] [Mode d\'identification / Connecteur ENT] (cliquer alors sur l\'icône en bout de ligne du tableau).'."\r\n";
    $texte.= URL_DIR_SACOCHE.'?id='.$_SESSION['BASE']."\r\n";
    $texte.= "\r\n";
    $texte.= 'Le contrat est en deux exemplaires.'."\r\n";
    $texte.= 'L\'un est à conserver par votre établissement.'."\r\n";
    $texte.= 'L\'autre est à retourner signé au responsable des conventions de l\'association :'."\r\n";
    $texte.= '- soit par courrier postal à l\'adresse'."\r\n";
    $texte.= implode( ' ; ' , $facture_PDF->tab_responsable_conventions['adresse_postale'] )."\r\n";
    $texte.= '- soit par courriel (scanné, en pièce jointe) à l\'adresse '.$facture_PDF->tab_responsable_conventions['courriel']."\r\n";
    $texte.= "\r\n";
    if($montant)
    {
      $texte.= 'La facture comporte les coordonnées bancaires de l\'association.'."\r\n";
      $texte.= 'Votre service gestionnaire peut régler par mandat administratif.'."\r\n";
      $texte.= 'En cas de nécessité particulière notre trésorier est joignable :'."\r\n";
      $texte.= '- par courriel à l\'adresse '.$facture_PDF->tab_tresorier['courriel']."\r\n";
      $texte.= '- par téléphone (selon disponibilités) au numéro '.$facture_PDF->tab_tresorier['tel_portable']."\r\n";
    }
    else
    {
      $texte.= 'Pour l\'année scolaire correspondante il n\'y a pas de règlement à acquitter.'."\r\n";
      $texte.= 'Une facture de 0 € est à votre disposition.'."\r\n";
    }
    $texte.= "\r\n";
    if($DB_ROW['convention_date_debut']<TODAY_MYSQL)
    {
      $or_reglement = ($montant) ? ' (ou perception du règlement)' : '' ;
      $texte.= 'Dès réception du contrat'.$or_reglement.' votre connecteur ENT sera automatiquement activé.'."\r\n";
    }
    else
    {
      $or_reglement = ($montant) ? ' (ou la perception du règlement)' : '' ;
      $texte.= 'La réception du contrat'.$or_reglement.' entrainera l\'activation automatique de votre connecteur ENT au '.To::date_mysql_to_french($DB_ROW['convention_date_debut']).' (changement d\'année scolaire).'."\r\n";
    }
    $texte.= 'Un courriel est alors envoyé au contact référent pour l\'en informer.'."\r\n";
    if($montant)
    {
      $texte.= 'Vous disposez de 2 mois à compter de l\'activation du connecteur ENT pour le tester et nous faire parvenir votre règlement (ou le contrat).'."\r\n";
    }
    $texte.= "\r\n";
    $texte.= 'Nous vous remercions de votre confiance et de votre soutien.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Remarque : si vous ne souhaitez pas donner suite à cette convention, il vous suffit de ne rien envoyer et de sélectionner une connexion avec les identifiants de SACoche.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Cordialement,'."\r\n";
    $texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n";
    $texte.= 'Responsable SACoche pour Sésamath'."\r\n";
    $texte.= "\r\n";
    $courriel_bilan = Sesamail::mail( $DB_ROW2['structure_contact_courriel'] , $titre , $texte ); // Ce serait mieux si le Reply-To était MAIL_SACOCHE_CONTACT mais cette contante n'est pas connue ici...
    if(!$courriel_bilan)
    {
      Json::end( FALSE , 'Envoi du courriel infructueux !' );
    }
  }
  // Retour des informations.
  Json::end( TRUE ,  array( 'fichier_contrat'=>URL_DIR_EXPORT.$contrat_fichier_nom , 'fichier_facture'=>URL_DIR_EXPORT.$facture_fichier_nom ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
