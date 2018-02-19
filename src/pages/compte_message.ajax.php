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
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && (!in_array($_POST['f_action'],array('afficher_users','afficher_destinataires'))) ) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$action          = (isset($_POST['f_action']))          ? Clean::texte($_POST['f_action'])          : '';
$profil_type     = (isset($_POST['f_profil_type']))     ? Clean::lettres($_POST['f_profil_type'])   : '';
$groupe_type     = (isset($_POST['f_groupe_type']))     ? Clean::lettres($_POST['f_groupe_type'])   : ''; // d n c g b
$groupe_id       = (isset($_POST['f_groupe_id']))       ? Clean::entier($_POST['f_groupe_id'])      : 0;
$message_id      = (isset($_POST['f_id']))              ? Clean::entier($_POST['f_id'])             : 0;
$date_debut_fr   = (isset($_POST['f_debut_date']))      ? Clean::date_fr($_POST['f_debut_date'])    : '';
$date_fin_fr     = (isset($_POST['f_fin_date']))        ? Clean::date_fr($_POST['f_fin_date'])      : '';
$message_contenu = (isset($_POST['f_message_contenu'])) ? Clean::texte($_POST['f_message_contenu']) : '' ;
$mode_discret    = (isset($_POST['f_mode_discret']))    ? TRUE                                      : FALSE ;

$abonnement_ref = 'message_accueil';

$tab_destinataires_transmis = (isset($_POST['f_destinataires_liste'])) ? explode(',',$_POST['f_destinataires_liste']) : array() ;
$tab_destinataires_valides  = array() ;

// Profils
$tab_profils = array(
  'administrateur' => 'Administrateurs',
  'directeur'      => 'Directeurs',
  'professeur'     => 'Professeurs',
  'personnel'      => 'Personnels autres',
  'eleve'          => 'Élèves',
  'parent'         => 'Responsables légaux',
);

// Types de regroupements et choix affectés
$tab_types = array(
  'all'    =>array() ,
  'niveau' =>array() ,
  'classe' =>array() ,
  'groupe' =>array() ,
  'besoin' =>array() ,
  'user'   =>array() ,
);

$tab_types_abreges = array(
  'd'=>'all'    ,
  'n'=>'niveau' ,
  'c'=>'classe' ,
  'g'=>'groupe' ,
  'b'=>'besoin' ,
);

// Contrôler la liste des destinataires transmis
if(!empty($tab_destinataires_transmis))
{
  foreach($tab_destinataires_transmis as $destinataire_infos)
  {
    list( $user_profil_type , $destinataire_type , $destinataire_id ) = explode('_',$destinataire_infos) + array_fill(0,3,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
    if( isset($tab_profils[$user_profil_type]) && isset($tab_types[$destinataire_type]) && $destinataire_id )
    {
      $tab_types[$destinataire_type][$destinataire_id] = $destinataire_id;
      $tab_destinataires_valides[] = $user_profil_type.'_'.$destinataire_type.'_'.$destinataire_id;
    }
  }
}
$nb_destinataires_valides  = count($tab_destinataires_valides);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste de destinataires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='afficher_destinataires') && $nb_destinataires_valides )
{
  // Récupérer les noms des niveaux / classes / groupes nécessaires
  foreach($tab_types as $destinataire_type => $tab_ids)
  {
    if(!empty($tab_ids))
    {
      if($destinataire_type=='all')
      {
        $tab_types[$destinataire_type][2] = '  Tous'; // double espace devant le mot pour le tri ultérieur des lignes
      }
      else
      {
        $listing_id = implode(',',$tab_types[$destinataire_type]);
        $DB_TAB = DB_STRUCTURE_MESSAGE::DB_recuperer_destinataires_texte($destinataire_type,$listing_id);
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_types[$destinataire_type][$DB_ROW['id']] = $DB_ROW['texte'];
        }
      }
    }
  }
  // Le tableau avec les options du formulaire SELECT
  $tab_select_destinataires = array( 'valeur'=>array() , 'texte'=>array() );
  foreach($tab_destinataires_valides as $destinataire_infos)
  {
    list( $user_profil_type , $destinataire_type , $destinataire_id ) = explode('_',$destinataire_infos);
    $tab_select_destinataires['valeur'][] = $user_profil_type.'_'.$destinataire_type.'_'.$destinataire_id;
    $tab_select_destinataires['texte' ][] = $tab_profils[$user_profil_type].' | '.$tab_types[$destinataire_type][$destinataire_id];
  }
  array_multisort(
    $tab_select_destinataires['texte' ], SORT_ASC,SORT_STRING,
    $tab_select_destinataires['valeur']
  );
  foreach($tab_select_destinataires['valeur'] as $key => $truc)
  {
    $tab_select_destinataires[$key] = array( 'valeur' => $tab_select_destinataires['valeur'][$key] , 'texte' => $tab_select_destinataires['texte'][$key] );
  }
  unset( $tab_select_destinataires['valeur'] , $tab_select_destinataires['texte'] );
  Json::end( TRUE , HtmlForm::afficher_select( $tab_select_destinataires , 'f_destinataires' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste d'utilisateurs
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='afficher_users') && isset($tab_profils[$profil_type]) && $groupe_id && isset($tab_types_abreges[$groupe_type]) )
{
  $champs = ($profil_type!='parent') ? 'CONCAT(user_nom," ",user_prenom) AS texte , CONCAT(user_profil_type,"_user_",user_id) AS valeur' : 'CONCAT(parent.user_nom," ",parent.user_prenom," (",enfant.user_nom," ",enfant.user_prenom,")") AS texte , CONCAT("parent_user_",parent.user_id) AS valeur' ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $profil_type /*profil_type*/ , 1 /*statut*/ , $tab_types_abreges[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs ) ;
  Json::end( TRUE , HtmlForm::afficher_select( $DB_TAB , 'f_user' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ , TRUE /*multiple*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau message
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $date_debut_fr && $date_fin_fr && $message_contenu && $nb_destinataires_valides )
{
  $date_debut_mysql  = To::date_french_to_mysql($date_debut_fr);
  $date_fin_mysql    = To::date_french_to_mysql($date_fin_fr);
  if($date_fin_mysql<$date_debut_mysql)
  {
    Json::end( FALSE , 'Date de fin antérieure à la date de début !' );
  }
  if($nb_destinataires_valides>100)
  {
    Json::end( FALSE , 'Trop de sélections : choisir "Tous (automatique)" sur des regroupements !' );
  }
  $message_id = DB_STRUCTURE_MESSAGE::DB_ajouter_message( $_SESSION['USER_ID'] , $date_debut_mysql , $date_fin_mysql , $message_contenu );
  DB_STRUCTURE_MESSAGE::DB_modifier_message_destinataires( $message_id , $tab_destinataires_valides , 'creer' );
  // Notifications (rendues visibles ultérieurement)
  if(!$mode_discret)
  {
    $tab_user_id = DB_STRUCTURE_MESSAGE::DB_recuperer_user_id_from_destinataires( $tab_destinataires_valides );
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , implode(',',$tab_user_id) );
    if($listing_abonnes)
    {
      $notification_date = ( TODAY_MYSQL < $date_debut_mysql ) ? $date_debut_mysql : NULL ;
      $notification_contenu = 'Message de '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']).' :'."\r\n\r\n".Outil::make_lien($message_contenu,'mail')."\r\n";
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_attente( $abonne_id , $abonnement_ref , $message_id , $notification_date , $notification_contenu );
      }
    }
  }
  // Afficher le retour
  $destinataires_nombre = ($nb_destinataires_valides>1) ? $nb_destinataires_valides.' sélections' : $nb_destinataires_valides.' sélection' ;
  Json::add_row( 'html' , '<tr id="id_'.$message_id.'" class="new">' );
  Json::add_row( 'html' ,   '<td>'.$date_debut_fr.'</td>' );
  Json::add_row( 'html' ,   '<td>'.$date_fin_fr.'</td>' );
  Json::add_row( 'html' ,   '<td>'.$destinataires_nombre.'</td>' );
  Json::add_row( 'html' ,   '<td>'.html(Outil::afficher_texte_tronque($message_contenu,60)).'</td>' );
  Json::add_row( 'html' ,   '<td class="nu">' );
  Json::add_row( 'html' ,     '<q class="modifier" title="Modifier ce message."></q>' );
  Json::add_row( 'html' ,     '<q class="supprimer" title="Supprimer ce message."></q>' );
  Json::add_row( 'html' ,   '</td>' );
  Json::add_row( 'html' , '</tr>' );
  Json::add_row( 'script' , 'tab_destinataires['.$message_id.']="'.implode(',',$tab_destinataires_valides).'";' );
  Json::add_row( 'script' , 'tab_msg_contenus['.$message_id.']="'.convertCRtoJS(html($message_contenu)).'";' );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un message existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $message_id && $date_debut_fr && $date_fin_fr && $message_contenu && $nb_destinataires_valides )
{
  $date_debut_mysql  = To::date_french_to_mysql($date_debut_fr);
  $date_fin_mysql    = To::date_french_to_mysql($date_fin_fr);
  if($date_fin_mysql<$date_debut_mysql)
  {
    Json::end( FALSE , 'Date de fin antérieure à la date de début !' );
  }
  if($nb_destinataires_valides>100)
  {
    Json::end( FALSE , 'Trop de sélections : choisir "Tous (automatique)" sur des regroupements !' );
  }
  DB_STRUCTURE_MESSAGE::DB_modifier_message( $message_id , $_SESSION['USER_ID'] , $date_debut_mysql , $date_fin_mysql , $message_contenu );
  DB_STRUCTURE_MESSAGE::DB_modifier_message_destinataires( $message_id , $tab_destinataires_valides , 'substituer' );
  // Notifications (rendues visibles ultérieurement)
  if(!$mode_discret)
  {
    $tab_user_id = DB_STRUCTURE_MESSAGE::DB_recuperer_user_id_from_destinataires( $tab_destinataires_valides );
    DB_STRUCTURE_NOTIFICATION::DB_supprimer_log_attente( $abonnement_ref , $message_id );
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , implode(',',$tab_user_id) );
    if($listing_abonnes)
    {
      $notification_date = ( TODAY_MYSQL < $date_debut_mysql ) ? $date_debut_mysql : NULL ;
      $notification_contenu = 'Message de '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']).' :'."\r\n\r\n".Outil::make_lien($message_contenu,'mail')."\r\n";
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_attente( $abonne_id , $abonnement_ref , $message_id , $notification_date , $notification_contenu );
      }
    }
  }
  // Afficher le retour
  $destinataires_nombre = ($nb_destinataires_valides>1) ? $nb_destinataires_valides.' sélections' : $nb_destinataires_valides.' sélection' ;
  Json::add_row( 'html' , '<td>'.$date_debut_fr.'</td>' );
  Json::add_row( 'html' , '<td>'.$date_fin_fr.'</td>' );
  Json::add_row( 'html' , '<td>'.$destinataires_nombre.'</td>' );
  Json::add_row( 'html' , '<td>'.html(Outil::afficher_texte_tronque($message_contenu,60)).'</td>' );
  Json::add_row( 'html' , '<td class="nu">' );
  Json::add_row( 'html' ,   '<q class="modifier" title="Modifier ce message."></q>' );
  Json::add_row( 'html' ,   '<q class="supprimer" title="Supprimer ce message."></q>' );
  Json::add_row( 'html' , '</td>' );
  Json::add_row( 'script' , 'tab_destinataires['.$message_id.']="'.implode(',',$tab_destinataires_valides).'";' );
  Json::add_row( 'script' , 'tab_msg_contenus['.$message_id.']="'.convertCRtoJS(html($message_contenu)).'";' );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un message existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $message_id )
{
  $nb_suppression = DB_STRUCTURE_MESSAGE::DB_supprimer_message($message_id,$_SESSION['USER_ID']);
  if(!$nb_suppression)
  {
    Json::end( FALSE , 'Message introuvable ou dont vous n\'êtes pas l\'auteur !' );
  }
  DB_STRUCTURE_MESSAGE::DB_supprimer_message_destinataires($message_id);
  // Notifications (rendues visibles ultérieurement)
  DB_STRUCTURE_NOTIFICATION::DB_supprimer_log_attente( $abonnement_ref , $message_id );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
