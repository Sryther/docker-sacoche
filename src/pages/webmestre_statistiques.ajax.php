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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( Clean::map('entier', explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$num    = (isset($_POST['num']))      ? Clean::entier($_POST['num'])     : 0 ;  // Numéro de l'étape en cours
$max    = (isset($_POST['max']))      ? Clean::entier($_POST['max'])     : 0 ;  // Nombre d'étapes à effectuer

$file_memo = CHEMIN_DOSSIER_EXPORT.'webmestre_statistiques_'.session_id().'.txt';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des structures avant recherche des stats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='calculer') && $nb_bases )
{
  // Pour mémoriser les totaux
  $tab_memo['totaux'] = array(
    'personnel_nb'         => 0 ,
    'personnel_use'        => 0 ,
    'eleve_nb'             => 0 ,
    'eleve_use'            => 0 ,
    'evaluation_nb'        => 0 ,
    'evaluation_use'       => 0 ,
    'officiel_sacoche_nb'  => 0 ,
    'officiel_sacoche_use' => 0 ,
    'officiel_livret_nb'   => 0 ,
    'officiel_livret_use'  => 0 ,
    );
  // Mémoriser les données des structures concernées par les stats
  $tab_memo['infos'] = array();
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( implode(',',$tab_base_id) );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_memo['infos'][] = array(
      'base_id'          => $DB_ROW['sacoche_base'] ,
      'geo'              => $DB_ROW['geo_nom'],
      'uai'              => $DB_ROW['structure_uai'],
      'structure'        => $DB_ROW['structure_denomination'],
      'contact'          => $DB_ROW['structure_contact_nom'].' '.$DB_ROW['structure_contact_prenom'] ,
      'inscription_date' => $DB_ROW['structure_inscription_date'] ,
    );
  }
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  $max = $nb_bases + 1 ; // La dernière étape consistera à vider la session temporaire et à renvoyer les totaux
  Json::end( TRUE , $max );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape de récupération des stats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='calculer') && $num && $max && ($num<$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  extract($tab_memo['infos'][$num-1]); // $base_id $geo $uai $structure $contact $inscription_date
  // Récupérer une série de stats
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  list( $personnel_nb , $eleve_nb , $personnel_use , $eleve_use , $evaluation_nb , $officiel_sacoche_nb , $officiel_livret_nb , $evaluation_use , $officiel_sacoche_use , $officiel_livret_use , $connexion_nom , $date_last_connexion )
    = DB_STRUCTURE_WEBMESTRE::DB_recuperer_statistiques( TRUE /*info_user_nb*/ , TRUE /*info_user_use*/ , TRUE /*info_action_nb*/ , TRUE /*info_action_use*/ , TRUE /*info_connexion*/ );
  // maj les totaux
  $tab_memo['totaux']['personnel_nb']         += $personnel_nb;
  $tab_memo['totaux']['personnel_use']        += $personnel_use;
  $tab_memo['totaux']['eleve_nb']             += $eleve_nb;
  $tab_memo['totaux']['eleve_use']            += $eleve_use;
  $tab_memo['totaux']['evaluation_nb']        += $evaluation_nb;
  $tab_memo['totaux']['evaluation_use']       += $evaluation_use;
  $tab_memo['totaux']['officiel_sacoche_nb']  += $officiel_sacoche_nb;
  $tab_memo['totaux']['officiel_sacoche_use'] += $officiel_sacoche_use;
  $tab_memo['totaux']['officiel_livret_nb']   += $officiel_livret_nb;
  $tab_memo['totaux']['officiel_livret_use']  += $officiel_livret_use;
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  $ligne_etabl = '<tr>'.
    '<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td>'.
    '<td class="label">'.$base_id.'</td>'.
    '<td class="label">'.html($geo).'</td>'.
    '<td class="label">'.$uai.'</td>'.
    '<td class="label">'.html($structure).'</td>'.
    '<td class="label">'.html($contact).'</td>'.
    '<td class="label">'.$inscription_date.'</td>'.
    '<td class="label">'.$date_last_connexion.'</td>'.
    '<td class="label">'.$personnel_nb  .'</td>'.
    '<td class="label">'.$personnel_use .'</td>'.
    '<td class="label">'.$eleve_nb .'</td>'.
    '<td class="label">'.$eleve_use.'</td>'.
    '<td class="label">'.number_format($evaluation_nb       ,0,'',' ').'</td>'.
    '<td class="label">'.number_format($evaluation_use      ,0,'',' ').'</td>'.
    '<td class="label">'.number_format($officiel_sacoche_nb ,0,'',' ').'</td>'.
    '<td class="label">'.number_format($officiel_sacoche_use,0,'',' ').'</td>'.
    '<td class="label">'.number_format($officiel_livret_nb  ,0,'',' ').'</td>'.
    '<td class="label">'.number_format($officiel_livret_use ,0,'',' ').'</td>'.
    '<td class="label">'.html($connexion_nom).'</td>'.
    '</tr>';
  Json::end( TRUE , $ligne_etabl );
}
if( ($action=='calculer') && $num && $max && ($num==$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Retour
  $ligne_total = '<tr>'.
    '<td class="nu"></td>'.
    '<th colspan="7" class="nu">Totaux</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['personnel_nb']        ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['personnel_use']       ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['eleve_nb']            ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['eleve_use']           ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['evaluation_nb']       ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['evaluation_use']      ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['officiel_sacoche_nb'] ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['officiel_sacoche_use'],0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['officiel_livret_nb']  ,0,'',' ').'</th>'.
    '<th class="hc">'.number_format($tab_memo['totaux']['officiel_livret_use'] ,0,'',' ').'</th>'.
    '<th class="nu"></th>'.
    '</tr>';
  // Supprimer les informations provisoires
  FileSystem::supprimer_fichier( $file_memo );
  // Retour
  Json::end( TRUE , $ligne_total );
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
