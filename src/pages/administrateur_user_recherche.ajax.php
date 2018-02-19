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

$action          = (isset($_POST['f_action']))        ? Clean::texte($_POST['f_action'])         : '';
$champ_nom       = (isset($_POST['champ_nom']))       ? Clean::lettres($_POST['champ_nom'])      : '';
$champ_val       = (isset($_POST['champ_val']))       ? Clean::texte($_POST['champ_val'])        : '';
$id              = (isset($_POST['f_id']))            ? Clean::entier($_POST['f_id'])            : 0 ;
$id_ent          = (isset($_POST['f_id_ent']))        ? Clean::id_ent($_POST['f_id_ent'])        : '';
$id_gepi         = (isset($_POST['f_id_gepi']))       ? Clean::id_ent($_POST['f_id_gepi'])       : '';
$sconet_id       = (isset($_POST['f_sconet_id']))     ? Clean::entier($_POST['f_sconet_id'])     : 0 ;
$sconet_num      = (isset($_POST['f_sconet_num']))    ? Clean::entier($_POST['f_sconet_num'])    : 0 ;
$reference       = (isset($_POST['f_reference']))     ? Clean::ref($_POST['f_reference'])        : '';
$profil          = (isset($_POST['f_profil']))        ? Clean::lettres($_POST['f_profil'])       : '';
$genre           = (isset($_POST['f_genre']))         ? Clean::lettres($_POST['f_genre'])        : '';
$nom             = (isset($_POST['f_nom']))           ? Clean::nom($_POST['f_nom'])              : '';
$prenom          = (isset($_POST['f_prenom']))        ? Clean::prenom($_POST['f_prenom'])        : '';
$login           = (isset($_POST['f_login']))         ? Clean::login($_POST['f_login'])          : '';
$courriel        = (isset($_POST['f_courriel']))      ? Clean::courriel($_POST['f_courriel'])    : '';
$sortie_date     = (isset($_POST['f_sortie_date']))   ? Clean::date_fr($_POST['f_sortie_date'])  : '';
$box_sortie_date = (isset($_POST['box_sortie_date'])) ? Clean::entier($_POST['box_sortie_date']) : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher un utilisateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='rechercher') && in_array($champ_nom,array('id_ent','id_gepi','sconet_id','sconet_elenoet','reference','login','email','nom','prenom')) && $champ_val )
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_users( $champ_nom , $champ_val );
  $nb_reponses = count($DB_TAB) ;
  if($nb_reponses==0)
  {
    Json::end( FALSE , 'Aucun utilisateur trouvé selon ce critère.' );
  }
  else if($nb_reponses>100)
  {
    Json::end( FALSE , $nb_reponses.' réponses : restreignez votre recherche !' );
  }
  else
  {
    // Tableau avec noms des profils en session pour usage si modification de l'utilisateur
    foreach($DB_TAB as $DB_ROW)
    {
      $genre_key = ($DB_ROW['user_profil_sigle']=='ELV') ? 'enfant' : 'adulte' ;
      // Formater la date
      $date_mysql  = $DB_ROW['user_sortie_date'];
      $date_sortie = ($date_mysql!=SORTIE_DEFAUT_MYSQL) ? To::date_mysql_to_french($date_mysql) : '-' ;
      // Afficher une ligne du tableau
      Json::add_str('<tr id="id_'.$DB_ROW['user_id'].'">');
      Json::add_str(  '<td>'.html($DB_ROW['user_id_ent']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_id_gepi']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_sconet_id']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_sconet_elenoet']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_reference']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_profil_sigle']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($DB_ROW['user_profil_nom_long_singulier'])).'" /></td>'); // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
      Json::add_str(  '<td>'.Html::$tab_genre[$genre_key][$DB_ROW['user_genre']].'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_nom']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_prenom']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_login']).'</td>');
      Json::add_str(  '<td>'.html($DB_ROW['user_email']).'</td>');
      Json::add_str(  '<td>'.$date_sortie.'</td>');
      Json::add_str(  '<td class="nu">');
      Json::add_str(    '<q class="modifier" title="Modifier cet utilisateur."></q>');
      Json::add_str(  '</td>');
      Json::add_str('</tr>'.NL);
    }
    Json::end( TRUE );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un utilisateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $profil && isset(Html::$tab_genre['adulte'][$genre]) && $nom && $prenom && $login && ($box_sortie_date || $sortie_date) )
{
  $tab_donnees = array();
  // Vérifier le profil
  if(!isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]))
  {
    Json::end( FALSE , 'Profil incorrect !' );
  }
  // Vérifier que l'identifiant ENT est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_ent)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent,$id) )
    {
      Json::end( FALSE , 'Identifiant ENT déjà utilisé !' );
    }
  }
  // Vérifier que l'identifiant GEPI est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_gepi)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi,$id) )
    {
      Json::end( FALSE , 'Identifiant Gepi déjà utilisé !' );
    }
  }
  // Vérifier que l'identifiant sconet est disponible (parmi les utilisateurs de même type de profil)
  if($sconet_id)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_id',$sconet_id,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      Json::end( FALSE , 'Identifiant Sconet déjà utilisé !' );
    }
  }
  // Vérifier que le n° sconet est disponible (parmi les utilisateurs de même type de profil)
  if($sconet_num)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_elenoet',$sconet_num,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      Json::end( FALSE , 'Numéro Sconet déjà utilisé !' );
    }
  }
  // Vérifier que la référence est disponible (parmi les utilisateurs de même type de profil)
  if($reference)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('reference',$reference,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      Json::end( FALSE , 'Référence déjà utilisée !' );
    }
  }
  // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$id) )
  {
    Json::end( FALSE , 'Login déjà utilisé !' );
  }
  // Vérifier le domaine du serveur mail seulement en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
  if($courriel)
  {
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($courriel);
      if(!$is_domaine_valide)
      {
        Json::end( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
      }
    }
    $tab_donnees[':email_origine'] = 'admin';
  }
  else
  {
    $tab_donnees[':email_origine'] = '';
  }
  // Cas de la date de sortie
  if($box_sortie_date)
  {
    $sortie_date = '-' ;
    $sortie_date_mysql = SORTIE_DEFAUT_MYSQL;
  }
  else
  {
    $sortie_date_mysql = To::date_french_to_mysql($sortie_date);
  }
  // Mettre à jour l'enregistrement
  $tab_donnees += array(
    ':sconet_id'   => $sconet_id,
    ':sconet_num'  => $sconet_num,
    ':reference'   => $reference,
    ':genre'       => $genre,
    ':nom'         => $nom,
    ':prenom'      => $prenom,
    ':courriel'    => $courriel,
    ':login'       => $login,
    ':id_ent'      => $id_ent,
    ':id_gepi'     => $id_gepi,
    ':sortie_date' => $sortie_date_mysql,
  );
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id , $tab_donnees );
  // En cas de sortie d'un élève, retirer les notes AB etc ultérieures à cette date de sortie, afin d'éviter des bulletins totalement vides
  if( ($profil=='ELV') && !$box_sortie_date )
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_user_saisies_absences_apres_sortie( $id , $sortie_date_mysql );
  }
  // Mettre à jour aussi éventuellement la session
  if($id==$_SESSION['USER_ID'])
  {
    $_SESSION['USER_GENRE']         = $genre ;
    $_SESSION['USER_NOM']           = $nom ;
    $_SESSION['USER_PRENOM']        = $prenom ;
    $_SESSION['USER_EMAIL']         = $courriel ;
    $_SESSION['USER_EMAIL_ORIGINE'] = isset($tab_donnees[':email_origine']) ? $tab_donnees[':email_origine'] : $_SESSION['USER_EMAIL_ORIGINE'] ; // si le mail n'a pas été changé alors il ne faut pas non plus modifier cette valeur
    $_SESSION['USER_LOGIN']         = $login ;
    $_SESSION['USER_ID_ENT']        = $id_ent ;
    $_SESSION['USER_ID_GEPI']       = $id_gepi ;
  }
  // Afficher le retour
  $genre_key = ($profil=='ELV') ? 'enfant' : 'adulte' ;
  Json::add_str('<td>'.html($id_ent).'</td>');
  Json::add_str('<td>'.html($id_gepi).'</td>');
  Json::add_str('<td>'.html($sconet_id).'</td>');
  Json::add_str('<td>'.html($sconet_num).'</td>');
  Json::add_str('<td>'.html($reference).'</td>');
  Json::add_str('<td>'.html($profil).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="{{PROFIL}}" /></td>');
  Json::add_str('<td>'.Html::$tab_genre[$genre_key][$genre].'</td>');
  Json::add_str('<td>'.html($nom).'</td>');
  Json::add_str('<td>'.html($prenom).'</td>');
  Json::add_str('<td>'.html($login).'</td>');
  Json::add_str('<td>'.html($courriel).'</td>');
  Json::add_str('<td>'.$sortie_date.'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cet utilisateur."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
