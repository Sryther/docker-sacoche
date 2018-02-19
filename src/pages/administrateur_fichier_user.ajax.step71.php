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
// Étape 71 - Adresses des parents (siecle_parents | onde_parents | tableur_parents | factos_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'liens_id_base.txt' );
$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
$tab_users_fichier = FileSystem::recuperer_fichier_infos_serializees( CHEMIN_DOSSIER_IMPORT.$fichier_nom_debut.'users.txt' );
// On récupère le contenu de la base pour comparer : $tab_base_adresse[user_id]=array()
$tab_base_adresse = array();
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_adresses_parents();
foreach($DB_TAB as $DB_ROW)
{
  $tab_base_adresse[$DB_ROW['parent_id']] = array(
    $DB_ROW['adresse_ligne1'] ,
    $DB_ROW['adresse_ligne2'] ,
    $DB_ROW['adresse_ligne3'] ,
    $DB_ROW['adresse_ligne4'] ,
    $DB_ROW['adresse_postal_code'] ,
    $DB_ROW['adresse_postal_libelle'] ,
    $DB_ROW['adresse_pays_nom'] ,
  );
}
// Pour préparer l'affichage
$lignes_ajouter   = '';
$lignes_modifier  = '';
$lignes_conserver = '';
// Pour préparer l'enregistrement des données
$tab_users_ajouter = array();
$tab_users_modifier = array();
// Parcourir chaque entrée du fichier
foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
{
  // Cas [1] : parent présent dans le fichier, adresse absente de la base : il vient d'être ajouté, on ajoute aussi son adresse, sauf si elle est vide (on ne teste pas le pays qui vaut FRANCE par défaut dans l'export Sconet).
  if(!isset($tab_base_adresse[$id_base]))
  {
    if( $tab_users_fichier['adresse'][$i_fichier][0] || $tab_users_fichier['adresse'][$i_fichier][1] || $tab_users_fichier['adresse'][$i_fichier][2] || $tab_users_fichier['adresse'][$i_fichier][3] || $tab_users_fichier['adresse'][$i_fichier][4] || $tab_users_fichier['adresse'][$i_fichier][5] )
    {
      $lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_users_fichier['adresse'][$i_fichier][0].' / '.$tab_users_fichier['adresse'][$i_fichier][1].' / '.$tab_users_fichier['adresse'][$i_fichier][2].' / '.$tab_users_fichier['adresse'][$i_fichier][3].' / '.$tab_users_fichier['adresse'][$i_fichier][4].' / '.$tab_users_fichier['adresse'][$i_fichier][5].' / '.$tab_users_fichier['adresse'][$i_fichier][6]).'</td></tr>';
    }
  }
  // Cas [2] : parent présent dans le fichier, adresse présente de la base
  else
  {
    $nb_differences = 0;
    $td_contenu = array();
    for($indice=0 ; $indice<7 ; $indice++)
    {
      // Test === en particulier pour les codes postaux : 7270 != 07270
      if($tab_users_fichier['adresse'][$i_fichier][$indice] === $tab_base_adresse[$id_base][$indice])
      {
        $td_contenu[] = html($tab_base_adresse[$id_base][$indice]);
      }
      else
      {
        $td_contenu[] = '<b>'.html($tab_base_adresse[$id_base][$indice]).' &rarr; '.html($tab_users_fichier['adresse'][$i_fichier][$indice]).'</b>';
        $nb_differences++;
      }
    }
    if($nb_differences==0)
    {
      // Cas [2a] : adresses identiques &rarr; conserver
      if($mode=='complet')
      {
        $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>'.NL;
      }
    }
    else
    {
      // Cas [2b] : adresses différentes &rarr; modifier
      $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$i_fichier.'" name="mod_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>'.NL;
    }
  }
}

// On affiche
$ligne_vide = '<tr><td colspan="3">Aucune</td></tr>'.NL;
if(empty($lignes_ajouter  )) { $lignes_ajouter   = $ligne_vide; }
if(empty($lignes_modifier )) { $lignes_modifier  = $ligne_vide; }
if(empty($lignes_conserver)) { $lignes_conserver = $ligne_vide; }
Json::add_str('<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des adresses.</label></p>'.NL);
Json::add_str('<table>'.NL);
// Cas [1]
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="3">Adresses à ajouter<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_ajouter);
Json::add_str(  '</tbody>'.NL);
// Cas [2b]
Json::add_str(  '<tbody>'.NL);
Json::add_str(    '<tr><th colspan="3">Adresses à modifier<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL);
Json::add_str(    $lignes_modifier);
Json::add_str(  '</tbody>'.NL);
// Cas [2a]
if($mode=='complet')
{
  Json::add_str(  '<tbody>'.NL);
  Json::add_str(    '<tr><th colspan="3">Adresses à conserver</th></tr>'.NL);
  Json::add_str(    $lignes_conserver);
  Json::add_str(  '</tbody>'.NL);
}
Json::add_str('</table>'.NL);
Json::add_str('<ul class="puce p"><li><a href="#step72" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL);

?>
