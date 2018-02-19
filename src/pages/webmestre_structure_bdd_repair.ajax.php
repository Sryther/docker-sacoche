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

$num    = (isset($_POST['num']))      ? Clean::entier($_POST['num'])     : 0 ;  // Numéro de l'étape en cours
$max    = (isset($_POST['max']))      ? Clean::entier($_POST['max'])     : 0 ;  // Nombre d'étapes à effectuer

require(CHEMIN_DOSSIER_INCLUDE.'fonction_dump.php');

$file_memo = CHEMIN_DOSSIER_EXPORT.'webmestre_bdd_repair_'.session_id().'.txt';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des structures avant recherche des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $nb_bases )
{
  // Mémoriser les données des structures
  $tab_memo = array(
    'base_id'                => array(),
    'structure_denomination' => array(),
    'niveau_alerte'          => array(),
    'messages'               => array(),
    'infos'                  => array(),
  );
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( implode(',',$tab_base_id) );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_memo['infos']['base_id'][]                = $DB_ROW['sacoche_base'];
    $tab_memo['infos']['structure_denomination'][] = $DB_ROW['structure_denomination'];
  }
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  $max = $nb_bases + 1 ; // La dernière étape consistera à vider la session temporaire et à renvoyer les totaux
  Json::end( TRUE , $max );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape de récupération des recherches des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $num && $max && ($num<$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  $base_id                = $tab_memo['infos']['base_id'               ][$num-1];
  $structure_denomination = $tab_memo['infos']['structure_denomination'][$num-1];
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  // Lancer analyse et réparation si besoin
  list( $niveau_alerte , $messages ) = analyser_et_reparer_tables_base_etablissement();
  // Retenir le résultat
  $tab_couleurs = array( 0=>'v' , 1=>'b' , 2=>'r' );
  $tab_memo['infos']['niveau_alerte'][$num-1] = $niveau_alerte;
  $tab_memo['infos']['messages'     ][$num-1] = '<tr class="'.$tab_couleurs[$niveau_alerte].'"><td>n°'.$base_id.' - '.html($structure_denomination).'</td><td>'.$messages.'</td></tr>';
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement du rapport de récupération des recherches des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $num && $max && ($num==$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Trier les lignes
  array_multisort(
    $tab_memo['infos']['niveau_alerte'], SORT_DESC,SORT_NUMERIC,
    $tab_memo['infos']['base_id']      , SORT_ASC,SORT_NUMERIC,
    $tab_memo['infos']['messages']
  );
  // Enregistrement du rapport
  $fichier_nom = 'rapport_analyser_et_reparer_tables_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.html';
  $thead = '<tr><td colspan="2">Analyse et réparation éventuelle des tables de bases de données par établissement - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = implode('',$tab_memo['infos']['messages']);
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody );
  // Supprimer les informations provisoires
  FileSystem::supprimer_fichier( $file_memo );
  // Retour
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
