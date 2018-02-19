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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Voir')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action         = (isset($_POST['f_action']))         ? $_POST['f_action']                                : '';
$matiere_id     = (isset($_POST['f_matiere_id']))     ? Clean::entier($_POST['f_matiere_id'])             : 0;
$matiere_nom    = (isset($_POST['f_matiere_nom']))    ? Clean::texte($_POST['f_matiere_nom'])             : '';
$niveau_id      = (isset($_POST['f_niveau_id']))      ? Clean::entier($_POST['f_niveau_id'])              : 0;
$niveau_nom     = (isset($_POST['f_niveau_nom']))     ? Clean::texte($_POST['f_niveau_nom'])              : '';
$structure_id   = (isset($_POST['f_structure_id']))   ? Clean::entier($_POST['f_structure_id'])           : 0;
$maj_date_fr    = (isset($_POST['f_maj_date']))       ? Clean::date_fr($_POST['f_maj_date'])              : '' ;
$nb_demandes    = (isset($_POST['f_nb_demandes']))    ? Clean::entier($_POST['f_nb_demandes'])            : -1; // Changer le nb de demandes
$partage        = (isset($_POST['f_partage']))        ? Clean::referentiel_partage($_POST['f_partage'])   : NULL; // Changer l'état de partage
$methode        = (isset($_POST['f_methode']))        ? Clean::calcul_methode($_POST['f_methode'])        : NULL; // Changer le mode de calcul
$limite         = (isset($_POST['f_limite']))         ? Clean::calcul_limite($_POST['f_limite'],$methode) : NULL; // Changer le nb d'items pris en compte
$retroactif     = (isset($_POST['f_retroactif']))     ? Clean::calcul_retroactif($_POST['f_retroactif'])  : NULL; // Changer la rétroactivité
$information    = (isset($_POST['f_information']))    ? Clean::texte($_POST['f_information'])             : '';
$referentiel_id = (isset($_POST['f_referentiel_id'])) ? Clean::entier($_POST['f_referentiel_id'])         : -1; // Référence du référentiel importé (0 si vierge), ou référence du référentiel à consulter
$ids            = (isset($_POST['f_ids']))            ? $_POST['f_ids']                                   : '';

function compter_items($DB_TAB)
{
  $nb_item = 0;
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['item_id']!==NULL)
    {
      $nb_item++;
    }
  }
  return $nb_item;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le nb de demandes autorisées pour une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier_nombre_demandes') && $matiere_id && ($nb_demandes!=-1) && ($nb_demandes<10) )
{
  DB_STRUCTURE_REFERENTIEL::DB_modifier_matiere_nb_demandes($matiere_id,$nb_demandes);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire des structures ayant partagées au moins un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='afficher_structures_partage')
{
  Json::end( TRUE , ServeurCommunautaire::afficher_formulaire_structures_communautaires( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les référentiels partagés trouvés selon les critères retenus (matière / niveau / structure)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='lister_referentiels_communautaires') // La vérification concernant le nombre de contraintes s'effectue après
{
  $maj_date_mysql = To::date_french_to_mysql($maj_date_fr);
  Json::end( TRUE , ServeurCommunautaire::afficher_liste_referentiels( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , $structure_id , $maj_date_mysql ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir le contenu d'un référentiel partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir_referentiel_communautaire') && $referentiel_id )
{
  Json::end( TRUE , ServeurCommunautaire::afficher_contenu_referentiel( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $referentiel_id ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Pour les autres cas on doit récupérer le paramètre ids
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(mb_substr_count($ids,'_')!=2)
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

list($prefixe,$matiere_id,$niveau_id) = explode('_',$ids);
$matiere_id  = Clean::entier($matiere_id);
$niveau_id   = Clean::entier($niveau_id);
$partageable = ( ( $matiere_id <= ID_MATIERE_PARTAGEE_MAX ) && ( $niveau_id <= ID_NIVEAU_PARTAGE_MAX ) ) ? TRUE : FALSE ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du détail d'un référentiel pour une matière et un niveau donnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir_referentiel_etablissement') && $matiere_id && $niveau_id )
{
  $DB_TAB_socle2016 = DB_STRUCTURE_REFERENTIEL::DB_recuperer_socle2016_for_referentiel_matiere_niveau( $matiere_id , $niveau_id , 'texte' /*format*/ );
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , TRUE /*item_comm*/ );
  Json::end( TRUE , HtmlArborescence::afficher_matiere_from_SQL( $DB_TAB , $DB_TAB_socle2016 , FALSE /*dynamique*/ , FALSE /*reference*/ , TRUE /*aff_coef*/ , TRUE /*aff_cart*/ , 'image' /*aff_socle*/ , 'image' /*aff_lien*/ , TRUE /*aff_comm*/ , FALSE /*aff_input*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le partage d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='partager') && $matiere_id && $niveau_id && $partageable && $partage && ($partage!='hs') )
{
  if( ($partage=='oui') && ( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) ) )
  {
    Json::end( FALSE , 'Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.' );
  }
  // Envoyer le référentiel (éventuellement vide pour l'effacer) vers le serveur de partage, sauf si passage non<->bof
  if($partage=='oui')
  {
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , TRUE /*item_comm*/ );
    $nb_item = compter_items($DB_TAB);
    if($nb_item<5)
    {
      $s = ($nb_item>1) ? 's' : '' ;
      Json::end( FALSE , 'Référentiel avec '.$nb_item.' item'.$s.' : son partage n\'apparaît pas pertinent.' );
    }
    $DB_TAB_socle2016 = DB_STRUCTURE_REFERENTIEL::DB_recuperer_socle2016_for_referentiel_matiere_niveau( $matiere_id , $niveau_id , 'ids' /*format*/ );
    $arbreXML = ServeurCommunautaire::exporter_arborescence_to_XML( $DB_TAB , $DB_TAB_socle2016 );
    $reponse  = ServeurCommunautaire::envoyer_arborescence_XML( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , $arbreXML , $information );
  }
  else
  {
    $partage_avant = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiel_partage_etat($matiere_id,$niveau_id);
    $reponse = ($partage_avant=='oui') ? ServeurCommunautaire::envoyer_arborescence_XML( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , '' , $information ) : 'ok' ;
  }
  // Analyse de la réponse retournée par le serveur de partage
  if($reponse!='ok')
  {
    Json::end( FALSE , $reponse );
  }
  // Tout s'est bien passé si on arrive jusque là...
  $tab_modifs = array(
    ':partage_etat' => $partage,
    ':partage_date' => TODAY_MYSQL,
    ':information'  => $information,
  );
  $is_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , $tab_modifs );
  // Retour envoyé
  $tab_partage = array(
    'oui' => '<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/etat/partage_oui.gif" />',
    'non' => '<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/etat/partage_non.gif" />',
    'bof' => '<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/etat/partage_non.gif" />',
    'hs'  => '<img title="Référentiel dont le partage est sans objet (matière ou niveau spécifique)." alt="" src="./_img/etat/partage_non.gif" />',
  );
  Json::end( TRUE , str_replace('◄DATE►',Html::date_texte(TODAY_MYSQL),$tab_partage[$partage]) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour sur le serveur de partage la dernière version d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='envoyer') && $matiere_id && $niveau_id && $partageable )
{
  if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
  {
    Json::end( FALSE , 'Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.' );
  }
  // Envoyer le référentiel vers le serveur de partage
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , TRUE /*item_comm*/ );
  $nb_item = count($DB_TAB);
  if($nb_item<5)
  {
    $s = ($nb_item>1) ? 's' : '' ;
    Json::end( FALSE , 'Référentiel avec '.$nb_item.' item'.$s.' : son partage n\'apparaît pas pertinent.' );
  }
  $DB_TAB_socle2016 = DB_STRUCTURE_REFERENTIEL::DB_recuperer_socle2016_for_referentiel_matiere_niveau( $matiere_id , $niveau_id , 'ids' /*format*/ );
  $arbreXML = ServeurCommunautaire::exporter_arborescence_to_XML( $DB_TAB , $DB_TAB_socle2016 );
  $reponse  = ServeurCommunautaire::envoyer_arborescence_XML( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , $arbreXML , $information );
  // Analyse de la réponse retournée par le serveur de partage
  if($reponse!='ok')
  {
    Json::end( FALSE , $reponse );
  }
  // Tout s'est bien passé si on arrive jusque là...
  $tab_modifs = array(
    ':partage_date' => TODAY_MYSQL,
    ':information'  => $information,
  );
  $is_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , $tab_modifs );
  // Retour envoyé
  Json::end( TRUE , '<img title="Référentiel partagé sur le serveur communautaire (MAJ le '.Html::date_texte(TODAY_MYSQL).')." alt="" src="./_img/etat/partage_oui.gif" />' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées pour enregistrer des notifications
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function notifications_referentiel_edition($matiere_id,$notification_contenu)
{
  $abonnement_ref = 'referentiel_edition';
  $listing_profs = DB_STRUCTURE_REFERENTIEL::DB_recuperer_autres_professeurs_matiere( $matiere_id, $_SESSION['USER_ID'] );
  if($listing_profs)
  {
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_profs );
    if($listing_abonnes)
    {
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , FALSE /*sep*/ );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_referentiel_etablissement') && $matiere_id && $niveau_id && $matiere_nom && $niveau_nom )
{
  if( DB_STRUCTURE_REFERENTIEL::DB_tester_referentiel($matiere_id,$niveau_id) )
  {
    Json::end( FALSE , 'Ce référentiel existe déjà ! Un autre administrateur de la même matière vient probablement de l\'importer... Actualisez cette page.' );
  }
  if($referentiel_id==0)
  {
    // C'est une demande de partir d'un référentiel vierge : on ne peut que créer un nouveau référentiel
    $partage = ($partageable) ? 'non' : 'hs' ;
    DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel($matiere_id,$niveau_id,$partage);
  }
  elseif($referentiel_id>0)
  {
    // C'est une demande de récupérer un référentiel provenant du serveur communautaire pour se le dupliquer
    if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
    {
      Json::end( FALSE , 'Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.' );
    }
    // Récupérer le référentiel
    $arbreXML = ServeurCommunautaire::recuperer_arborescence_XML( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $referentiel_id );
    if(substr($arbreXML,0,6)=='Erreur')
    {
      Json::end( FALSE , $arbreXML );
    }
    // L'analyser
    $test_XML_valide = ServeurCommunautaire::verifier_arborescence_XML($arbreXML);
    if($test_XML_valide!==TRUE)
    {
      Json::end( FALSE , $test_XML_valide );
    }
    DB_STRUCTURE_REFERENTIEL::DB_importer_arborescence_from_XML($arbreXML,$matiere_id,$niveau_id);
    $partage = ($partageable) ? 'bof' : 'hs' ;
    DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel($matiere_id,$niveau_id,$partage);
  }
  // Notifications (rendues visibles ultérieurement)
  $action = ($referentiel_id) ? 'a importé un nouveau référentiel' : 'a créé un nouveau référentiel vierge' ;
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' '.$action.' ['.$matiere_nom.'] ['.$niveau_nom.'].'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $matiere_id && $niveau_id && $partage && $matiere_nom && $niveau_nom )
{
  // S'il était partagé, il faut le retirer du serveur communautaire
  if($partage=='oui')
  {
    if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
    {
      Json::end( FALSE , 'Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.' );
    }
    $reponse = ServeurCommunautaire::envoyer_arborescence_XML( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $matiere_id , $niveau_id , '' , $information );
    if($reponse!='ok')
    {
      Json::end( FALSE , $reponse );
    }
  }
  DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_matiere_niveau($matiere_id,$niveau_id);
  // Log de l'action
  SACocheLog::ajouter('Suppression du référentiel ['.$matiere_nom.'] ['.$niveau_nom.'].');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé le référentiel ['.$matiere_nom.'] ['.$niveau_nom.'].'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_sensible($notification_contenu);
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le mode de calcul d'un référentiel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='calculer') && $matiere_id && $niveau_id && $matiere_nom && $niveau_nom )
{
  if( is_null($methode) || is_null($limite) || is_null($retroactif) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  $tab_modifs = array(
    ':calcul_methode'    => $methode,
    ':calcul_limite'     => $limite,
    ':calcul_retroactif' => $retroactif,
  );
  $is_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , $tab_modifs );
  if($limite==1)  // si une seule saisie prise en compte
  {
    $retour = 'Seule la dernière saisie compte';
  }
  elseif($methode=='classique')  // si moyenne classique
  {
    $retour = ($limite==0) ? 'Moyenne de toutes les saisies' : 'Moyenne des '.$limite.' dernières saisies';
  }
  elseif(in_array($methode,array('geometrique','arithmetique')))  // si moyenne geometrique | arithmetique
  {
    $seize = (($methode=='geometrique')&&($limite==5)) ? 1 : 0 ;
    $coefs = ($methode=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$limite-19) : substr('1/2/4/8/16/',0,2*$limite-12+$seize) ;
    $retour = 'Les '.$limite.' dernières saisies &times;'.$coefs;
  }
  elseif($methode=='bestof1')  // si meilleure note
  {
    $retour = ($limite==0) ? 'Seule la meilleure saisie compte' : 'Meilleure des '.$limite.' dernières saisies';
  }
  elseif(in_array($methode,array('bestof2','bestof3')))  // si 2 | 3 meilleures notes
  {
    $nb_best = (int)substr($methode,-1);
    $retour = ($limite==0) ? 'Moyenne des '.$nb_best.' meilleures saisies' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$limite.' dernières';
  }
  elseif(in_array($methode,array('frequencemin','frequencemax'))) // si note la plus fréquente
  {
    $note_si_egalite = (substr($methode,-3)=='min') ? 'malus' : 'bonus' ;
    $retour = ($limite==0) ? 'Saisie la plus fréquente, '.$note_si_egalite.' si égalité' : 'Saisie la plus fréquente ('.$note_si_egalite.' si égalité) parmi les '.$limite.' dernières';
  }
      if($retroactif=='non')    { $retour .= ' (sur la période).';       }
  elseif($retroactif=='oui')    { $retour .= ' (rétroactivement).';      }
  elseif($retroactif=='annuel') { $retour .= ' (de l\'année scolaire).'; }
  // Notifications (rendues visibles ultérieurement)
  if($is_modif)
  {
    $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a modifié le mode de calcul du référentiel ['.$matiere_nom.'] ['.$niveau_nom.'] par :'."\r\n".str_replace('&times;','x',$retour)."\r\n";
    notifications_referentiel_edition( $matiere_id , $notification_contenu );
  }
  // Retour
  Json::end( TRUE , $retour );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On en devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
