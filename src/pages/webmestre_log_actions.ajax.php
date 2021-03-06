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

$base_id = (isset($_POST['f_base_id'])) ? Clean::entier($_POST['f_base_id']) : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération du log des actions sensibles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($base_id)
{
  $fichier_log_contenu = SACocheLog::lire($base_id);
  if($fichier_log_contenu===NULL)
  {
    Json::end( TRUE , '<p class="danger">Le fichier n\'existe pas : probablement qu\'aucune action sensible n\'a encore été effectuée !</p>' );
  }
  else
  {
    
    // 1 En extraire le plus récent (les 100 derniers enregistrements)
    $table_log_extrait = '<table class="p"><thead><tr><th>Date &amp; Heure</th><th>Utilisateur</th><th>Action</th></tr></thead><tbody>';
    $tab_lignes = SACocheLog::extraire_lignes($fichier_log_contenu);
    $indice_ligne_debut = count($tab_lignes)-1 ;
    $indice_ligne_fin   = max(-1 , $indice_ligne_debut-100) ;
    $nb_lignes          = $indice_ligne_debut - $indice_ligne_fin ;
    $s = ($nb_lignes>1) ? 's' : '' ;
    for( $indice_ligne=$indice_ligne_debut ; $indice_ligne>$indice_ligne_fin ; $indice_ligne-- )
    {
      list( $balise_debut , $date_heure , $utilisateur , $action , $balise_fin ) = explode("\t",$tab_lignes[$indice_ligne]);
      $table_log_extrait .= '<tr><td>'.$date_heure.'</td><td>'.$utilisateur.'</td><td>'.$action.'</td></tr>'; // Pas de html(), cela a déjà été fait lors de l'enregistrement des logs
    }
    $table_log_extrait .= '</tbody></table>';
    // 2 Enregistrer un csv récupérable
    $fichier_log_contenu = str_replace(array('<?php /*','*/ ?>'),'',$fichier_log_contenu);
    $fichier_export_nom = 'log_'.$base_id.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
    FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_export_nom.'.csv' , To::csv($fichier_log_contenu) );
    // Afficher tout ça
    Json::add_str('<ul class="puce">'.NL);
    Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fichier_export_nom.'.csv"><span class="file file_txt">Récupérer le fichier complet (format <em>csv</em>).</span></a></li>'.NL);
    Json::add_str(  '<li>Consulter les derniers logs ('.$nb_lignes.' ligne'.$s.') :</li>'.NL);
    Json::add_str('</ul>'.NL);
    Json::add_str($table_log_extrait);
    Json::end( TRUE );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
