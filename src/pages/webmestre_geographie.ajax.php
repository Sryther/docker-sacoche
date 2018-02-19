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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$id     = (isset($_POST['f_id']))     ? Clean::entier($_POST['f_id'])    : 0;
$ordre  = (isset($_POST['f_ordre']))  ? Clean::entier($_POST['f_ordre']) : 0;
$nom    = (isset($_POST['f_nom']))    ? Clean::texte($_POST['f_nom'])    : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle zone / Dupliquer une pédiode existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='ajouter')||($action=='dupliquer')) && $ordre )
{
  // Vérifier que le nom de la zone est disponible
  if( DB_WEBMESTRE_WEBMESTRE::DB_tester_zone_nom($nom) )
  {
    Json::end( FALSE , 'Nom de zone déjà existant !' );
  }
  // Insérer l'enregistrement
  $geo_id = DB_WEBMESTRE_WEBMESTRE::DB_ajouter_zone($ordre,$nom);
  // Afficher le retour
  Json::add_str('<tr id="id_'.$geo_id.'" class="new">');
  Json::add_str(  '<td>'.$geo_id.'</td>');
  Json::add_str(  '<td>'.$ordre.'</td>');
  Json::add_str(  '<td>'.html($nom).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cette zone."></q>');
  Json::add_str(    '<q class="dupliquer" title="Dupliquer cette zone."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cette zone."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une zone existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $ordre && $nom )
{
  // Vérifier que le nom de la zone est disponible
  if( DB_WEBMESTRE_WEBMESTRE::DB_tester_zone_nom($nom,$id) )
  {
    Json::end( FALSE , 'Nom de zone déjà existant !' );
  }
  // Mettre à jour l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_zone($id,$ordre,$nom);
  // Afficher le retour
  // La zone d'id 1 ne peut être supprimée, c'est la zone par défaut.
  $q_supprimer = ($id!=1) ? '<q class="supprimer" title="Supprimer cette zone."></q>' : '<q class="supprimer_non" title="La zone par défaut ne peut pas être supprimée."></q>' ;
  Json::add_str('<td>'.$id.'</td>');
  Json::add_str('<td>'.$ordre.'</td>');
  Json::add_str('<td>'.html($nom).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cette zone."></q>');
  Json::add_str(  '<q class="dupliquer" title="Dupliquer cette zone."></q>');
  Json::add_str($q_supprimer);
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une zone existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && ($id>1) && $nom )
{
  // Effacer l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_supprimer_zone($id);
  // Log de l'action
  SACocheLog::ajouter('Suppression de la zone géographique "'.$nom.'" (n°'.$id.').');
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
