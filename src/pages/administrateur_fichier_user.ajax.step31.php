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
// Étape 31 - Analyse des données des classes (siecle_professeurs_directeurs | siecle_eleves | onde_eleves | factos_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
// On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
$tab_classes_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'classes.txt' );
// On récupère le contenu de la base pour comparer : $tab_classes_base['ref'] : id -> ref ; $tab_classes_base['nom'] : id -> nom
$tab_classes_base        = array();
$tab_classes_base['ref'] = array();
$tab_classes_base['nom'] = array();
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes();
foreach($DB_TAB as $DB_ROW)
{
  $tab_classes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
  $tab_classes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
}
// Contenu du fichier à conserver
$lignes_ras = '';
foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
{
  $id_base = array_search($ref,$tab_classes_base['ref']);
  if($id_base!==FALSE)
  {
    if($mode=='complet')
    {
      $lignes_ras .= '<tr><th>'.html($tab_classes_base['ref'][$id_base]).'</th><td>'.html($tab_classes_base['nom'][$id_base]).'</td></tr>'.NL;
    }
    $tab_i_classe_TO_id_base[$i_classe] = $id_base;
    unset($tab_classes_fichier['ref'][$i_classe] , $tab_classes_fichier['nom'][$i_classe] ,  $tab_classes_fichier['niveau'][$i_classe] , $tab_classes_base['ref'][$id_base] , $tab_classes_base['nom'][$id_base]);
  }
}
// Contenu du fichier à supprimer
$lignes_del = '';
if(count($tab_classes_base['ref']))
{
  foreach($tab_classes_base['ref'] as $id_base => $ref)
  {
    $lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" /> '.html($tab_classes_base['nom'][$id_base]).'</td></tr>'.NL;
  }
}
// Contenu du fichier à ajouter
$lignes_add = '';
if(count($tab_classes_fichier['ref']))
{
  $select_niveau = '<option value="">&nbsp;</option>';
  $tab_niveau_ref = array();
  $DB_TAB = DB_STRUCTURE_NIVEAU::DB_lister_niveaux_etablissement( FALSE /*with_particuliers*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
    $key = ( ($import_origine=='siecle') && ($import_profil=='eleve') ) ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
    $tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
  }
  foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
  {
    // On préselectionne un niveau :
    // - pour siecle_eleves                 on compare avec un masque d'expression régulière
    // - pour onde_eleves                   on compare avec les niveaux de SACoche
    // - pour siecle_professeurs_directeurs on compare avec le début de la référence de la classe
    // - pour tableur_eleves                on compare avec le début de la référence de la classe
    $id_checked = '';
    foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
    {
      if( ($import_origine=='siecle') && ($import_profil=='eleve') )
      {
        $id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_classes_fichier['niveau'][$i_classe])) ? $niveau_id : '';
      }
      elseif( ($import_origine=='onde') && ($import_profil=='eleve') )
      {
        $id_checked = (mb_strpos($tab_classes_fichier['niveau'][$i_classe],$masque_recherche)===0) ? $niveau_id : '';
      }
      else
      {
        $id_checked = (mb_strpos(str_replace(' ','',$ref),$masque_recherche)===0) ? $niveau_id : '';
      }
      if($id_checked)
      {
        break;
      }
    }
    $nom_classe = ($tab_classes_fichier['nom'][$i_classe]) ? $tab_classes_fichier['nom'][$i_classe] : $ref ;
    $lignes_add .= '<tr><th><input id="add_'.$i_classe.'" name="add_'.$i_classe.'" type="checkbox" checked /> '.html($ref).'<input id="add_ref_'.$i_classe.'" name="add_ref_'.$i_classe.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i_classe.'" name="add_niv_'.$i_classe.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i_classe.'" name="add_nom_'.$i_classe.'" size="15" type="text" value="'.html($nom_classe).'" maxlength="20" /></td></tr>'.NL;
  }
}
// On enregistre (tableau mis à jour)
$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt', $tab_liens_id_base );
// On affiche
Json::add_str('<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des classes.</label></p>'.NL);
// Pour siecle_professeurs_directeurs, les groupes ne figurent pas forcément dans le fichier si les services ne sont pas présents -> on ne procède qu'à des ajouts éventuels.
if($lignes_del)
{
  Json::add_str('<p class="danger">Des classes non trouvées sont proposées à la suppression. Il se peut que les services / affectations manquent dans le fichier. Veuillez cochez ces suppressions pour les confirmer.</p>'.NL);
}
// Avertissement ONDE
if( ($import_origine=='onde') && ($import_profil=='eleve') && ($mode=='partiel') && ($lignes_del) && ($lignes_add) )
{
  Json::add_str('<p class="probleme">Lors du passage de BE1D à ONDE, les identifiants de classes changent.<br />
  SACoche fait au mieux pour établir une correspondance, mais cela peut ne pas fonctionner si vous avez renommé des éléments.<br />
  Ci-dessous, il se peut donc qu\'une liste de classes soit proposée à la suppression, et une autre à l\'ajout.<br />
  Si vous êtes en cours d\'année scolaire, surtout ne validez pas une telle proposition !<br />
  <a href="./index.php?page=administrateur_classe" target="_blank" rel="noopener noreferrer">Ouvrez le menu de gestion des classes dans un nouvel onglet</a> et modifiez manuellement les références de vos classes actuelles en y indiquant celles issues de ONDE.<br />
  Ensuite, reprenez la procédure d\'import au début.</p>'.NL);
}
$ligne_vide = '<tr><td colspan="2">Aucune</td></tr>'.NL;
if(empty($lignes_ras)) { $lignes_ras = $ligne_vide; }
if(empty($lignes_add)) { $lignes_add = $ligne_vide; }
if(empty($lignes_del)) { $lignes_del = $ligne_vide; }
Json::add_str('<table>'.NL);
if($mode=='complet')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="2">Classes actuelles à conserver</th></tr>'.NL);
  Json::add_str(    $lignes_ras);
  Json::add_str(  '</tbody>'.NL);
}
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="2">Classes nouvelles à ajouter<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_add);
Json::add_str(  '</tbody>'.NL);
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="2">Classes anciennes à supprimer<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_del);
Json::add_str(  '</tbody>'.NL);
Json::add_str('</table>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step32" id="envoyer_infos_regroupements">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
