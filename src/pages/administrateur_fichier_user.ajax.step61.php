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
// Étape 61 - Modification d'affectations éventuelles (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$lignes_classes_ras   = '';
$lignes_classes_add   = '';
$lignes_classes_del   = '';
$lignes_principal_ras = '';
$lignes_principal_add = '';
$lignes_principal_del = '';
$lignes_matieres_ras  = '';
$lignes_matieres_add  = '';
$lignes_matieres_del  = '';
$lignes_groupes_ras   = '';
$lignes_groupes_add   = '';
$lignes_groupes_del   = '';
// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
$tab_users_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt' );
//
// Pour siecle_professeurs_directeurs, il faut regarder les associations profs/classes & profs/PP + profs/matières + profs/groupes.
// Pour tableur_professeurs_directeurs, il faut regarder les associations profs/classes & profs/groupes.
// Pour siecle_eleves & tableur_eleves, il faut juste à regarder les associations élèves/groupes.
//
if($import_profil=='professeur')
{
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // associations profs/classes
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Garder trace des associations profs/classes pour faire le lien avec les propositions d'ajouts profs/pp
  $tab_asso_prof_classe = array();
  // Garder trace des identités des profs de la base
  $tab_base_prof_identite = array();
  // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE $tab_base_classe[groupe_id]=groupe_nom
  // En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
  $tab_base_classe = array();
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_base_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
  }
  $tab_base_affectation = array();
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_professeurs_avec_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
    $tab_base_prof_identite[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
  }
  // Parcourir chaque entrée du fichier à la recherche d'affectations profs/classes
  foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
  {
    if(count($tab_classes))
    {
      foreach( $tab_classes as $i_classe => $classe_pp )
      {
        // On a trouvé une telle affectation ; comparer avec ce que contient la base
        if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
        {
          $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
          $groupe_id = $tab_i_classe_TO_id_base[$i_classe];
          if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
          {
            $tab_asso_prof_classe[$user_id.'_'.$groupe_id] = TRUE;
            if($mode=='complet')
            {
              $lignes_classes_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
            }
            unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
          }
          else
          {
            $tab_asso_prof_classe[$user_id.'_'.$groupe_id] = TRUE;
            $lignes_classes_add .= '<tr><th>Ajouter <input id="classe_'.$user_id.'_'.$groupe_id.'_1" name="classe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
          }
        }
      }
    }
  }
  // Associations à retirer
  if(count($tab_base_affectation))
  {
    foreach($tab_base_affectation as $key => $bool)
    {
      list($user_id,$groupe_id) = explode('_',$key);
      $lignes_classes_del .= '<tr><th>Supprimer <input id="classe_'.$user_id.'_'.$groupe_id.'_0" name="classe_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_prof_identite[$user_id]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
    }
  }
  if($import_origine=='siecle')
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // associations profs/PP
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE ($tab_base_classe déjà renseigné)
    $tab_base_affectation = array();
    $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_jointure_professeurs_principaux();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
    }
    // Parcourir chaque entrée du fichier à la recherche d'affectations profs/PP
    foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
    {
      if(count($tab_classes))
      {
        foreach( $tab_classes as $i_classe => $classe_pp )
        {
          if($classe_pp=='PP')
          {
            // On a trouvé une telle affectation ; comparer avec ce que contient la base
            if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
            {
              $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
              $groupe_id = $tab_i_classe_TO_id_base[$i_classe];
              if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
              {
                if($mode=='complet')
                {
                  $lignes_principal_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
                }
                unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
              }
              elseif(isset($tab_asso_prof_classe[$user_id.'_'.$groupe_id]))
              {
                $lignes_principal_add .= '<tr><th>Ajouter <input id="pp_'.$user_id.'_'.$groupe_id.'_1" name="pp_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
              }
            }
          }
        }
      }
    }
    // Associations à retirer
    if(count($tab_base_affectation))
    {
      foreach($tab_base_affectation as $key => $bool)
      {
        list($user_id,$groupe_id) = explode('_',$key);
        $lignes_principal_del .= '<tr><th>Supprimer <input id="pp_'.$user_id.'_'.$groupe_id.'_0" name="pp_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_prof_identite[$user_id]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
      }
    }
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // associations profs/matières
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_matiere_id]=TRUE + $tab_base_matiere[matiere_id]=matiere_nom + $tab_matiere_ref_TO_id_base[matiere_ref]=id_base
    // En deux requêtes sinon on ne récupère pas les matieres sans utilisateurs affectés.
    $tab_base_matiere = array();
    $tab_matiere_ref_TO_id_base = array();
    $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
      $tab_matiere_ref_TO_id_base[$DB_ROW['matiere_ref']] = $DB_ROW['matiere_id'];
    }
    $tab_base_affectation = array();
    $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_jointure_professeurs_matieres();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['matiere_id']] = TRUE;
    }
    // Parcourir chaque entrée du fichier à la recherche d'affectations profs/matières
    foreach( $tab_users_fichier['matiere'] as $i_fichier => $tab_matieres )
    {
      if(count($tab_matieres))
      {
        foreach( $tab_matieres as $matiere_code => $type_rattachement ) // $type_rattachement vaut 'discipline' ou 'service'
        {
          // On a trouvé une telle affectation ; comparer avec ce que contient la base
          if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_matiere_ref_TO_id_base[$matiere_code])) )
          {
            $user_id    = $tab_i_fichier_TO_id_base[$i_fichier];
            $matiere_id = $tab_matiere_ref_TO_id_base[$matiere_code];
            if(isset($tab_base_affectation[$user_id.'_'.$matiere_id]))
            {
              if($mode=='complet')
              {
                $lignes_matieres_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>'.NL;
              }
              unset($tab_base_affectation[$user_id.'_'.$matiere_id]);
            }
            else
            {
              $lignes_matieres_add .= '<tr><th>Ajouter <input id="matiere_'.$user_id.'_'.$matiere_id.'_1" name="matiere_'.$user_id.'_'.$matiere_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>'.NL;
            }
          }
        }
      }
    }
    // Retirer des matières semble sans intérêt.
  }
}
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// associations profs/groupes ou élèves/groupes
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Garder trace des identités des utilisateurs de la base
$tab_base_user_identite = array();
// On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE et $tab_base_groupe[groupe_id]=groupe_nom
// En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
$tab_base_groupe = array();
$DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_groupes();
foreach($DB_TAB as $DB_ROW)
{
  $tab_base_groupe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
}
$tab_base_affectation = array();
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_avec_groupe( $import_profil , TRUE /*only_actuels*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
  $tab_base_user_identite[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
}
// Parcourir chaque entrée du fichier à la recherche d'affectations utilisateurs/groupes
foreach( $tab_users_fichier['groupe'] as $i_fichier => $tab_groupes )
{
  if(count($tab_groupes))
  {
    foreach( $tab_groupes as $i_groupe => $groupe_ref )
    {
      // On a trouvé une telle affectation ; comparer avec ce que contient la base
      if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_groupe_TO_id_base[$i_groupe])) )
      {
        $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
        $groupe_id = $tab_i_groupe_TO_id_base[$i_groupe];
        if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
        {
          if($mode=='complet')
          {
            $lignes_groupes_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
          }
          unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
        }
        else
        {
          $lignes_groupes_add .= '<tr><th>Ajouter <input id="groupe_'.$user_id.'_'.$groupe_id.'_1" name="groupe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
        }
      }
    }
  }
}
// Associations à retirer
if(count($tab_base_affectation))
{
  foreach($tab_base_affectation as $key => $bool)
  {
    list($user_id,$groupe_id) = explode('_',$key);
    $lignes_groupes_del .= '<tr><th>Supprimer <input id="groupe_'.$user_id.'_'.$groupe_id.'_0" name="groupe_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_user_identite[$user_id]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
  }
}
// On affiche
Json::add_str('<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des affectations éventuelles.</label></p>'.NL);
if( $lignes_classes_del || $lignes_principal_del || $lignes_groupes_del )
{
  Json::add_str('<p class="danger">Des suppressions sont proposées. Elles peuvent provenir d\'un fichier incomplet ou d\'ajouts manuels antérieurs dans SACoche. Décochez-les si besoin !</p>'.NL);
}
$ligne_vide = '<tr><td colspan="3">Aucune</td></tr>'.NL;
if(empty($lignes_classes_ras  )) { $lignes_classes_ras   = $ligne_vide; }
if(empty($lignes_classes_add  )) { $lignes_classes_add   = $ligne_vide; }
if(empty($lignes_classes_del  )) { $lignes_classes_del   = $ligne_vide; }
if(empty($lignes_principal_ras)) { $lignes_principal_ras = $ligne_vide; }
if(empty($lignes_principal_add)) { $lignes_principal_add = $ligne_vide; }
if(empty($lignes_principal_del)) { $lignes_principal_del = $ligne_vide; }
if(empty($lignes_matieres_ras )) { $lignes_matieres_ras  = $ligne_vide; }
if(empty($lignes_matieres_add )) { $lignes_matieres_add  = $ligne_vide; }
// if(empty($lignes_matieres_del )) { $lignes_matieres_del  = $ligne_vide; }
if(empty($lignes_groupes_ras  )) { $lignes_groupes_ras   = $ligne_vide; }
if(empty($lignes_groupes_add  )) { $lignes_groupes_add   = $ligne_vide; }
if(empty($lignes_groupes_del  )) { $lignes_groupes_del   = $ligne_vide; }
Json::add_str('<table>'.NL);
if($import_profil=='professeur')
{
  if($mode=='complet')
  {
    Json::add_str(  '<tbody>'.NL);
    Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / classes à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
    Json::add_str(    $lignes_classes_ras);
    Json::add_str(  '</tbody>'.NL);
  }
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / classes à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
  Json::add_str(    $lignes_classes_add);
  Json::add_str(  '</tbody>'.NL);
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / classes à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
  Json::add_str(    $lignes_classes_del);
  Json::add_str(  '</tbody>'.NL);
  if($import_origine=='siecle')
  {
    if($mode=='complet')
    {
      Json::add_str(  '<tbody>'.NL);
      Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / p.principal à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
      Json::add_str(    $lignes_principal_ras);
      Json::add_str(  '</tbody>'.NL);
    }
    Json::add_str(  '<tbody>'.NL);
    Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / p.principal à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
    Json::add_str(    $lignes_principal_add);
    Json::add_str(  '</tbody>'.NL);
    Json::add_str(  '<tbody>'.NL);
    Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / p.principal à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
    Json::add_str(    $lignes_principal_del);
    Json::add_str(  '</tbody>'.NL);
    if($mode=='complet')
    {
      Json::add_str(  '<tbody>'.NL);
      Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / matières à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
      Json::add_str(    $lignes_matieres_ras);
      Json::add_str(  '</tbody>'.NL);
    }
    Json::add_str(  '<tbody>'.NL);
    Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / matières à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
    Json::add_str(    $lignes_matieres_add);
    // Json::add_str(  '</tbody>'.NL);
    // Json::add_str(  '<tbody>'.NL);
    // Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / matières à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
    // Json::add_str(    $lignes_matieres_del);
    Json::add_str(  '</tbody>'.NL);
  }
}
if($mode=='complet')
{
  Json::add_str(  '<tbody>');
  Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / groupes à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
  Json::add_str(    $lignes_groupes_ras);
  Json::add_str(  '</tbody>'.NL);
}
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / groupes à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_groupes_add);
Json::add_str(  '</tbody>'.NL);
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="3">Associations utilisateurs / groupes à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_groupes_del);
Json::add_str(  '</tbody>'.NL);
Json::add_str('</table>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step62" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
