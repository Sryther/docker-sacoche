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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])  : '';
$id     = (isset($_POST['f_id']))     ? Clean::entier($_POST['f_id'])     : 0;
$ordre  = (isset($_POST['f_ordre']))  ? Clean::entier($_POST['f_ordre'])  : 0;
$nom    = (isset($_POST['f_nom']))    ? Clean::texte($_POST['f_nom'])     : '';
$livret = !empty($_POST['f_livret'])  ? Clean::entier($_POST['f_livret']) : NULL;

$tab_periode_livret = array(
  NULL => '-',
  21 => 'Semestre 1/2',
  22 => 'Semestre 2/2',
  31 => 'Trimestre 1/3',
  32 => 'Trimestre 2/3',
  33 => 'Trimestre 3/3',
  41 => 'Bimestre 1/4',
  42 => 'Bimestre 2/4',
  43 => 'Bimestre 3/4',
  44 => 'Bimestre 4/4',
  51 => 'Période 1/5',
  52 => 'Période 2/5',
  53 => 'Période 3/5',
  54 => 'Période 4/5',
  55 => 'Période 5/5',
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle période / Dupliquer une pédiode existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='ajouter')||($action=='dupliquer')) && $ordre && $nom && isset($tab_periode_livret[$livret]) )
{
  // Vérifier que le nom de la période est disponible
  if( DB_STRUCTURE_PERIODE::DB_tester_periode_nom($nom) )
  {
    Json::end( FALSE , 'Nom déjà utilisé !' );
  }
  // Vérifier que la référence de Livret Scolaire est disponible
  if( $livret && DB_STRUCTURE_PERIODE::DB_tester_periode_livret($livret) )
  {
    Json::end( FALSE , 'Référence de Livret Scolaire déjà utilisée !' );
  }
  // Insérer l'enregistrement
  $periode_id = DB_STRUCTURE_PERIODE::DB_ajouter_periode( $ordre , $nom , $livret );
  // Afficher le retour
  Json::add_str('<tr id="id_'.$periode_id.'" class="new">');
  Json::add_str(  '<td>'.$ordre.'</td>');
  Json::add_str(  '<td>'.html($nom).'</td>');
  Json::add_str(  '<td>'.$tab_periode_livret[$livret].'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cette période."></q>');
  Json::add_str(    '<q class="dupliquer" title="Dupliquer cette période."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cette période."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une période existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $ordre && $nom && isset($tab_periode_livret[$livret]) )
{
  // Vérifier que le nom de la période est disponible
  if( DB_STRUCTURE_PERIODE::DB_tester_periode_nom( $nom , $id ) )
  {
    Json::end( FALSE , 'Nom déjà utilisé !' );
  }
  // Vérifier que la référence de Livret Scolaire est disponible
  if( $livret && DB_STRUCTURE_PERIODE::DB_tester_periode_livret( $livret , $id ) )
  {
    Json::end( FALSE , 'Référence de Livret Scolaire déjà utilisée !' );
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_PERIODE::DB_modifier_periode( $id , $ordre , $nom , $livret );
  // Afficher le retour
  Json::add_str('<td>'.$ordre.'</td>');
  Json::add_str('<td>'.html($nom).'</td>');
  Json::add_str('<td>'.$tab_periode_livret[$livret].'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cette période."></q>');
  Json::add_str(  '<q class="dupliquer" title="Dupliquer cette période."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer cette période."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une période existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id && $nom )
{
  // Effacer l'enregistrement
  DB_STRUCTURE_PERIODE::DB_supprimer_periode($id);
  // Log de l'action
  SACocheLog::ajouter('Suppression de la période "'.$nom.'" (n°'.$id.'), et donc des bilans officiels associés.');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé la période "'.$nom.'" (n°'.$id.'), et donc les bilans officiels associés.'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
