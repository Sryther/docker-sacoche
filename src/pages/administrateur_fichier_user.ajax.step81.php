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
// Étape 81 - Liens de responsabilités des parents (siecle_parents | onde_parents | tableur_parents | factos_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
$tab_users_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt' );
// On convertit les données du fichier parent=>enfant dans un tableau enfant=>parent
$tab_fichier_parents_par_eleve = array();
foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
{
  if( (isset($tab_users_fichier['enfant'][$i_fichier])) && (count($tab_users_fichier['enfant'][$i_fichier])) )
  {
    foreach($tab_users_fichier['enfant'][$i_fichier] as $eleve_id => $resp_legal_num)
    {
      $tab_fichier_parents_par_eleve[$eleve_id][$i_fichier] = $resp_legal_num;
    }
  }
}
// On récupère le contenu de la base pour comparer : $tab_base_parents_par_eleve[eleve_id]=array( 'eleve'=>(eleve_nom,eleve_prenom) , 'parent'=>array(num=>(parent_id,parent_nom,parent_prenom,)) )
$tab_base_parents_par_eleve = array();
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_par_eleve();
foreach($DB_TAB as $DB_ROW)
{
  $tab_infos_eleve = array( 'nom'=>$DB_ROW['eleve_nom'] , 'prenom'=>$DB_ROW['eleve_prenom'] );
  if( ($DB_ROW['parent_id']) && ( ( $DB_ROW['parent_sconet_id'] && $DB_ROW['eleve_sconet_id'] ) || ($import_origine=='onde') ) )
  {
    $tab_infos_parent = array( 'id'=>(int)$DB_ROW['parent_id'] , 'nom'=>$DB_ROW['parent_nom'] , 'prenom'=>$DB_ROW['parent_prenom'] );
    if(!isset($tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']]))
    {
      $tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array((int)$DB_ROW['resp_legal_num']=>$tab_infos_parent) );
    }
    else
    {
      $tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']]['parent'][$DB_ROW['resp_legal_num']] = $tab_infos_parent;
    }
  }
  else
  {
    // Cas d'un élève sans parent affecté ou un élève/parent sans id Sconet avec import Sconet
    if(!isset($tab_base_parents_par_eleve[$DB_ROW['eleve_id']]))
    {
      $tab_base_parents_par_eleve[$DB_ROW['eleve_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array() );
    }
  }
}
// On enregistre une copie du tableau $tab_fichier_parents_par_eleve (on partira de celui-ci pour récupérer les identifiants à ajouter / modifier).
// Il faut le faire maintenant car ensuite tab_base_parents_par_eleve est ensuite peu à peu vidé.
$tab_memo_analyse = array();
foreach($tab_fichier_parents_par_eleve as $eleve_id_base => $tab_parent)
{
  foreach($tab_parent as $i_fichier => $resp_legal_num)
  {
    $parent_id_base = $tab_i_fichier_TO_id_base[$i_fichier];
    $tab_memo_analyse[$eleve_id_base][$parent_id_base] = $resp_legal_num;
  }
}
FileSystem::enregistrer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'memo_analyse.txt', $tab_memo_analyse );
// Pour préparer l'affichage
$lignes_modifier  = '';
$lignes_conserver = '';
// Pour préparer l'enregistrement des données
$tab_users_modifier = array();
// Parcourir chaque élève de la base
foreach($tab_base_parents_par_eleve as $eleve_id_base => $tab_base_eleve_infos)
{
  if(isset($tab_fichier_parents_par_eleve[$eleve_id_base])) // Si on ne trouve aucun parent dans le fichier, on laisse tomber, c'est peut être un vieux compte
  {
    $nb_differences = 0;
    $td_contenu = array();
    // On fait des modifs s'il n'y a pas le même nombre de responsables ou si un responsable est différent ou si l'ordre des responsables est différent
    $num = 1;
    while( count($tab_fichier_parents_par_eleve[$eleve_id_base]) || count($tab_base_eleve_infos['parent']) )
    {
      $parent_i_fichier      = array_search( $num, $tab_fichier_parents_par_eleve[$eleve_id_base] );
      $parent_id_base        = (isset($tab_base_eleve_infos['parent'][$num])) ? $tab_base_eleve_infos['parent'][$num]['id'] : FALSE ;
      $parent_affich_fichier = ($parent_i_fichier===FALSE)  ? 'X' : $tab_users_fichier['nom'][$parent_i_fichier].' '.$tab_users_fichier['prenom'][$parent_i_fichier] ;
      $parent_affich_base    = ($parent_id_base===FALSE)    ? 'X' : $tab_base_eleve_infos['parent'][$num]['nom'].' '.$tab_base_eleve_infos['parent'][$num]['prenom'] ;
      if($tab_i_fichier_TO_id_base[$parent_i_fichier]===$parent_id_base)
      {
        if($parent_affich_base!='X')
        {
          $td_contenu[] = 'Responsable n°'.$num.' : '.html($parent_affich_base);
        }
      }
      else
      {
        $td_contenu[] = 'Responsable n°'.$num.' : <b>'.html($parent_affich_base).' &rarr; '.html($parent_affich_fichier).'</b>';
        $nb_differences++;
      }
      if($parent_i_fichier!==FALSE)
      {
        unset($tab_fichier_parents_par_eleve[$eleve_id_base][$parent_i_fichier]);
      }
      if($tab_i_fichier_TO_id_base[$parent_i_fichier])
      {
        unset($tab_base_eleve_infos['parent'][$num]);
      }
      $num++;
      if($num==5)
      {
        // Il arrive que certains fichiers Sconet soient mal renseignés, avec par exemple plusieurs responsables n°1 (un vieux compte et un nouveau).
        // Si on ne met pas une sortie à ce niveau alors ça boucle à l'infini.
        break;
      }
    }
    if($nb_differences==0)
    {
      // Cas [1] : responsables identiques &rarr; conserver
      if($mode=='complet')
      {
        $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>'.NL;
      }
    }
    else
    {
      // Cas [2] : au moins une différence  &rarr; modifier
      $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$eleve_id_base.'" name="mod_'.$eleve_id_base.'" type="checkbox" checked /></th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>'.NL;
    }
  }
}
// On affiche
$ligne_vide = '<tr><td colspan="3">Aucun</td></tr>'.NL;
if(empty($lignes_modifier )) { $lignes_modifier  = $ligne_vide; }
if(empty($lignes_conserver)) { $lignes_conserver = $ligne_vide; }
Json::add_str('<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des liens de responsabilité.</label></p>'.NL);
Json::add_str('<table>'.NL);
// Cas [2]
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="3">Liens de responsabilité à modifier<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_modifier);
Json::add_str(  '</tbody>'.NL);
// Cas [1]
if($mode=='complet')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Liens de responsabilité à conserver</th></tr>'.NL);
  Json::add_str(    $lignes_conserver);
  Json::add_str(  '</tbody>'.NL);
}
Json::add_str('</table>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step82" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
