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
$TITRE = html(Lang::_("Adresses des parents"));
?>

<?php
// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$nom_prenom   = (isset($_POST['f_nomprenom']))    ? TRUE                                    : FALSE ;
$levenshtein  = (isset($_POST['f_levenshtein']))  ? TRUE                                    : FALSE ;
$recherche    = ( $nom_prenom || $levenshtein )   ? TRUE                                    : FALSE ;
$debut_nom    = (isset($_POST['f_debut_nom']))    ? Clean::nom($_POST['f_debut_nom'])       : '' ;
$debut_prenom = (isset($_POST['f_debut_prenom'])) ? Clean::prenom($_POST['f_debut_prenom']) : '' ;
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></li>
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_siecle#toggle_responsables_doublons_adresses">DOC : Import d'utilisateurs depuis Siècle / STS-Web - Doublons d'adresses responsables</a></span></li>
</ul>

<hr />

<form action="./index.php?page=administrateur_parent&amp;section=adresse" method="post" id="form_recherche">
  <div class="ti"><button id="f_nomprenom" name="f_nomprenom" type="submit" class="rechercher">Rechercher</button> des responsables dont le nom commence par <input type="text" id="f_debut_nom" name="f_debut_nom" value="<?php echo html($debut_nom) ?>" size="5" /> et/ou le prénom commence par <input type="text" id="f_debut_prenom" name="f_debut_prenom" value="<?php echo html($debut_prenom) ?>" size="5" />.</div>
  <div class="ti"><button id="f_levenshtein" name="f_levenshtein" type="submit" class="rechercher">Rechercher</button> des responsables d'un même élève dont les adresses sont proches sans être identiques.</div>
</form>

<hr />

<?php

if(!$recherche)
{
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Lister les parents, par nom / prénom ou recherche d'adresses proches
if($nom_prenom)
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( TRUE /*with_adresse*/ , TRUE /*statut*/ , $debut_nom , $debut_prenom );
}
elseif($levenshtein) // (forcément)
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_adresses_par_enfant();
  if(!empty($DB_TAB))
  {
    $tab_parents_id = array();
    foreach($DB_TAB as $enfant_id => $DB_TAB_parents)
    {
      if(count($DB_TAB_parents)>1)
      {
        $adresse_parent0 = $DB_TAB_parents[0]['adresse_ligne1'].$DB_TAB_parents[0]['adresse_ligne2'].$DB_TAB_parents[0]['adresse_ligne3'].$DB_TAB_parents[0]['adresse_ligne4'].$DB_TAB_parents[0]['adresse_postal_code'].$DB_TAB_parents[0]['adresse_postal_libelle'].$DB_TAB_parents[0]['adresse_pays_nom'];
        $adresse_parent1 = $DB_TAB_parents[1]['adresse_ligne1'].$DB_TAB_parents[1]['adresse_ligne2'].$DB_TAB_parents[1]['adresse_ligne3'].$DB_TAB_parents[1]['adresse_ligne4'].$DB_TAB_parents[1]['adresse_postal_code'].$DB_TAB_parents[1]['adresse_postal_libelle'].$DB_TAB_parents[1]['adresse_pays_nom'];
        if($adresse_parent0!=$adresse_parent1)
        {
          if( Outil::pourcentage_commun( $adresse_parent0 , $adresse_parent1 ) > 60 )
          {
            $parent_id0 = $DB_TAB_parents[0]['parent_id'];
            $parent_id1 = $DB_TAB_parents[1]['parent_id'];
            if( !isset($tab_parents_id[$parent_id0]) && !isset($tab_parents_id[$parent_id1]) )
            {
              $tab_parents_id[$parent_id0] = $parent_id0;
              $tab_parents_id[$parent_id1] = $parent_id1;
            }
          }
        }
      }
    }
    $DB_TAB = count($tab_parents_id) ? DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( TRUE /*with_adresse*/ , TRUE /*statut*/ , '' /*debut_nom*/ , '' /*debut_prenom*/ , implode(',',$tab_parents_id) ) : array() ;
    // La requête précédente ne permet pas de trier sur le nom de l'élève ; du coup on trie a posteriori...
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $key => $DB_ROW)
      {
        $tab_parents_id[$DB_ROW['parent_id']] = $DB_ROW;
      }
      $DB_TAB = $tab_parents_id;
    }
    // Préparation de l'export CSV
    $separateur = ';';
    $export_csv = 'NOM PRENOM'.$separateur.'ADRESSE L1'.$separateur.'ADRESSE L2'.$separateur.'ADRESSE L3'.$separateur.'ADRESSE L4'.$separateur.'ADRESSE CP'.$separateur.'ADRESSE COMMUNE'.$separateur.'ADRESSE PAYS'.$separateur.'RESPONSABILITES'."\r\n\r\n";
  }
}

?>

<table id="table_action" class="form t9 hsort">
  <thead>
    <tr>
      <th>Resp</th>
      <th>Nom Prénom</th>
      <th>Adresse (4 lignes)</th>
      <th>C.P.</th>
      <th>Commune</th>
      <th>Pays</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $parent_id = ($DB_ROW['parent_id']) ? 'M' : 'A' ; // Indiquer si le parent a une adresse dans la base ou pas.
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$parent_id.$DB_ROW['user_id'].'">';
        echo  ($DB_ROW['enfants_nombre']) ? '<td>'.$DB_ROW['enfants_nombre'].' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace('§BR§','<br />',html(html($DB_ROW['enfants_liste']))).'" /></td>' : '<td>0 <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Aucun lien de responsabilité !" /></td>' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        echo  '<td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</td>';
        echo  '<td><span>'.html($DB_ROW['adresse_ligne1']).'</span> ; <span>'.html($DB_ROW['adresse_ligne2']).'</span> ; <span>'.html($DB_ROW['adresse_ligne3']).'</span> ; <span>'.html($DB_ROW['adresse_ligne4']).'</span></td>';
        echo  '<td>'.html($DB_ROW['adresse_postal_code']).'</td>';
        echo  '<td>'.html($DB_ROW['adresse_postal_libelle']).'</td>';
        echo  '<td>'.html($DB_ROW['adresse_pays_nom']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cette adresse."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
        // Export CSV
        if($levenshtein)
        {
          $export_csv .= $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].$separateur.$DB_ROW['adresse_ligne1'].$separateur.$DB_ROW['adresse_ligne2'].$separateur.$DB_ROW['adresse_ligne3'].$separateur.$DB_ROW['adresse_ligne4'].$separateur.$DB_ROW['adresse_postal_code'].$separateur.$DB_ROW['adresse_postal_libelle'].$separateur.$DB_ROW['adresse_pays_nom'].$separateur.str_replace('§BR§',$separateur,$DB_ROW['enfants_liste'])."\r\n";
        }
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="6"></td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<?php
if( $levenshtein && !empty($DB_TAB) )
{
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'extraction_ressemblances_adresses_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  echo'<p><ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données dans un fichier (format <em>csv</em></span>).</a></li></ul></p>'.NL;
}
?>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Modifier une adresse</h2>
  <p>
    <label class="tab">Nom Prénom :</label><b id="gestion_identite"></b><br />
    <label class="tab" for="f_ligne1">Ligne 1 :</label><input id="f_ligne1" name="f_ligne1" type="text" value="" size="50" maxlength="50" /><br />
    <label class="tab" for="f_ligne2">Ligne 2 :</label><input id="f_ligne2" name="f_ligne2" type="text" value="" size="50" maxlength="50" /><br />
    <label class="tab" for="f_ligne3">Ligne 3 :</label><input id="f_ligne3" name="f_ligne3" type="text" value="" size="50" maxlength="50" /><br />
    <label class="tab" for="f_ligne4">Ligne 4 :</label><input id="f_ligne4" name="f_ligne4" type="text" value="" size="50" maxlength="50" /><br />
    <label class="tab" for="f_code_postal">Code postal :</label><input id="f_code_postal" name="f_code_postal" type="text" value="" size="6" maxlength="10" /><br />
    <label class="tab" for="f_commune">Commune :</label><input id="f_commune" name="f_commune" type="text" value="" size="45" maxlength="45" /><br />
    <label class="tab" for="f_pays">Pays :</label><input id="f_pays" name="f_pays" type="text" value="" size="35" maxlength="35" />
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<div id="temp_td" class="hide"></div>
