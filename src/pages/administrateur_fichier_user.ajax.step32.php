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
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 32 - Traitement des actions à effectuer sur les classes (siecle_professeurs_directeurs | siecle_eleves | onde_eleves | factos_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
// Récupérer les éléments postés
$tab_del = (!empty($_POST['f_del'])) ? Clean::map('entier',explode(',',$_POST['f_del'])) : array() ;
$tab_add = array();
$tab_tmp = (!empty($_POST['f_add'])) ? explode(',',$_POST['f_add']) : array() ;
if(count($tab_tmp))
{
  foreach($tab_tmp as $add_infos)
  {
    list( $i , $niv , $ref , $nom ) =  explode(']¤[',$add_infos);
    $tab_add[$i]['ref'] = Clean::ref($ref);
    $tab_add[$i]['nom'] = Clean::texte( $nom);
    $tab_add[$i]['niv'] = Clean::entier($niv);
  }
}
// Ajouter des classes éventuelles
$nb_add = 0;
if(count($tab_add))
{
  foreach($tab_add as $i => $tab)
  {
    if( $tab['ref'] && $tab['nom'] && $tab['niv'] )
    {
      $classe_id = DB_STRUCTURE_REGROUPEMENT::DB_ajouter_groupe_par_admin( 'classe' , $tab['ref'] , $tab['nom'] , $tab['niv'] );
      $nb_add++;
      $tab_i_classe_TO_id_base[$i] = (int) $classe_id;
    }
  }
}
// Supprimer des classes éventuelles
$nb_del = 0;
$notification_contenu = '';
if(count($tab_del))
{
  $notification_intro = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'];
  foreach($tab_del as $groupe_id)
  {
    if( $groupe_id )
    {
      DB_STRUCTURE_REGROUPEMENT::DB_supprimer_groupe_par_admin( $groupe_id , 'classe' , TRUE /*with_devoir*/ );
      $nb_del++;
      // Log de l'action
      SACocheLog::ajouter('Suppression d\'une classe (n°'.$groupe_id.') lors d\'un import de fichier, et donc des devoirs associés.');
      $notification_contenu .= $notification_intro.' a supprimé une classe (n°'.$groupe_id.') lors d\'un import de fichier, et donc les devoirs associés.'."\r\n";
    }
  }
}
// Notifications (rendues visibles ultérieurement)
if($notification_contenu)
{
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
}

// On enregistre (tableau mis à jour)
$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt', $tab_liens_id_base );
// Afficher le bilan
$lignes = '';
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_avec_niveaux();
if($mode=='complet')
{
  foreach($DB_TAB as $DB_ROW)
  {
    $lignes .= '<tr><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td>'.html($DB_ROW['groupe_nom']).'</td></tr>'.NL;
  }
}
$nb_fin = count($DB_TAB);
$nb_ras = $nb_fin - $nb_add + $nb_del;
$s_ras = ($nb_ras>1) ? 's' : '';
$s_add = ($nb_add>1) ? 's' : '';
$s_del = ($nb_del>1) ? 's' : '';
$s_fin = ($nb_fin>1) ? 's' : '';
Json::add_str('<p><label class="valide">'.$nb_ras.' classe'.$s_ras.' présente'.$s_ras.' + '.$nb_add.' classe'.$s_add.' ajoutée'.$s_add.' &minus; '.$nb_del.' classe'.$s_del.' supprimée'.$s_del.' = '.$nb_fin.' classe'.$s_fin.' résultante'.$s_fin.'.</label></p>'.NL);
if($mode=='complet')
{
  Json::add_str('<table>'.NL);
  Json::add_str(  '<thead>'.NL);
  Json::add_str(    '<tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>'.NL);
  Json::add_str(  '</thead>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    $lignes);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str('</table>'.NL);
}
$STEP = ( ( ($import_origine=='onde') || ($import_origine=='factos') ) && ($import_profil=='eleve') ) ? '5' : '4' ;
Json::add_str('<ul class="puce p"><li><a href="#step'.$STEP.'1" id="passer_etape_suivante">Passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
