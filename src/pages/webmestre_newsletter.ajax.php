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

// Transmis en tableau pour la newsletter, mais en chaine pour la suppression
$tab_base_id = (isset($_POST['f_base'])) ? ( (is_array($_POST['f_base'])) ? $_POST['f_base'] : explode(',',$_POST['f_base']) ) : array() ;
$tab_base_id = array_filter( Clean::map('entier',$tab_base_id) , 'positif' );
$nb_bases    = count($tab_base_id);

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '';
$titre   = (isset($_POST['f_titre']))   ? Clean::texte($_POST['f_titre'])   : '';
$contenu = (isset($_POST['f_contenu'])) ? Clean::texte($_POST['f_contenu']) : '';
$num     = (isset($_POST['num']))       ? Clean::entier($_POST['num'])      : 0 ;  // Numéro de l'étape en cours
$max     = (isset($_POST['max']))       ? Clean::entier($_POST['max'])      : 0 ;  // Nombre d'étapes à effectuer
$pack    = 10 ;  // Nombre de mails envoyés à chaque étape

$file_memo = CHEMIN_DOSSIER_EXPORT.'webmestre_newsletter_'.session_id().'.txt';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Préparation d'une lettre d'informations avant envoi
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='envoyer') && $titre && $contenu && $nb_bases )
{
  // Mémoriser le nb d'envoi / le titre / le contenu de la lettre d'informations / les données des contacts concernés par la lettre
  $tab_memo = array(
    'nombre'  => $nb_bases,
    'titre'   => $titre,
    'contenu' => $contenu,
    'infos'   => array(),
  );
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_contacts_cibles( implode(',',$tab_base_id) );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_memo['infos'][] = array(
      'base_id'          => $DB_ROW['contact_id'] ,
      'contact_nom'      => $DB_ROW['contact_nom'] ,
      'contact_prenom'   => $DB_ROW['contact_prenom'] ,
      'contact_courriel' => $DB_ROW['contact_courriel'] ,
    );
  }
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  $max = 1 + floor($nb_bases/$pack) + 1 ; // La dernière étape consistera uniquement à vider la session temporaire
  Json::end( TRUE , $max );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape d'envoi d'une lettre d'informations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='envoyer') && $num && $max && ($num<$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Envoyer une série de courriels
  $i_min = ($num-1)*10;
  $i_max = min( $tab_memo['nombre'] , $num*10);
  for($i=$i_min ; $i<$i_max ; $i++)
  {
    extract($tab_memo['infos'][$i]); // $base_id $contact_nom $contact_prenom $contact_courriel
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.','."\r\n\r\n";
    $texte.= $tab_memo['contenu']."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
    $texte.= 'Rappel des adresses à utiliser :'."\r\n";
    $texte.= URL_DIR_SACOCHE.'?id='.$base_id.' (hébergement de l\'établissement)'."\r\n";
    $texte.= SERVEUR_PROJET.' (site du projet SACoche)'."\r\n\r\n";
    $courriel_bilan = Sesamail::mail( $contact_courriel , $tab_memo['titre'] , $texte );
    if(!$courriel_bilan)
    {
      Json::end( FALSE , 'Erreur lors de l\'envoi du courriel !' );
    }
  }
  Json::end( TRUE );
}
if( ($action=='envoyer') && $num && $max && ($num==$max) )
{
  // Supprimer les informations provisoires
  FileSystem::supprimer_fichier( $file_memo );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer plusieurs structures existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $nb_bases )
{
  foreach($tab_base_id as $base_id)
  {
    Webmestre::supprimer_multi_structure($base_id);
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
