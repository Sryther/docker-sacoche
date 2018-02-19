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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des données transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// info groupe
$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::lettres($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])    : 0;
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? Clean::texte($_POST['f_groupe_nom'])    : '';

$critere_objet = (isset($_POST['f_critere_objet'])) ? Clean::texte($_POST['f_critere_objet']) : '';
$with_coef     = (isset($_POST['f_with_coef']))     ? 1                                      : 0;

// item(s) matière(s)
$tab_compet_liste = (isset($_POST['f_matiere_items_liste'])) ? explode('_',$_POST['f_matiere_items_liste']) : array() ;
$tab_compet_liste = Clean::map('entier',$tab_compet_liste);
$compet_liste  = implode(',',$tab_compet_liste);
$compet_nombre = count($tab_compet_liste);

// domaine ou composante du socle 2016
$cycle_id = $domaine_id = $composante_id = 0 ;
if( ($critere_objet=='socle2016_domaine_maitrise') && !empty($_POST['f_select_domaine']) )
{
  list( $cycle_id , $domaine_id ) = explode('_',$_POST['f_select_domaine']) + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  $cycle_id   = Clean::entier($cycle_id);
  $domaine_id = Clean::entier($domaine_id);
}
if( ($critere_objet=='socle2016_composante_maitrise') && !empty($_POST['f_select_composante']) )
{
  list( $cycle_id , $composante_id ) = explode('_',$_POST['f_select_composante']) + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  $cycle_id      = Clean::entier($cycle_id);
  $composante_id = Clean::entier($composante_id);
}

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$critere_tab_seuil_acquis   = ( isset($_POST['f_critere_seuil_acquis'])   && is_array($_POST['f_critere_seuil_acquis'])   ) ? $_POST['f_critere_seuil_acquis']   : array();
$critere_tab_seuil_maitrise = ( isset($_POST['f_critere_seuil_maitrise']) && is_array($_POST['f_critere_seuil_maitrise']) ) ? $_POST['f_critere_seuil_maitrise'] : array();
$nb_criteres_acquis   = count($critere_tab_seuil_acquis);
$nb_criteres_maitrise = count($critere_tab_seuil_maitrise);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des données transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$is_matiere_items_bilanMS     = ( ($critere_objet=='matiere_items_bilanMS')         && $compet_nombre              && $nb_criteres_acquis )   ? TRUE : FALSE ;
$is_matiere_items_bilanPA     = ( ($critere_objet=='matiere_items_bilanPA')         && $compet_nombre              && $nb_criteres_acquis )   ? TRUE : FALSE ;
$is_s2016_domaine_maitrise    = ( ($critere_objet=='socle2016_domaine_maitrise')    && $cycle_id && $domaine_id    && $nb_criteres_maitrise ) ? TRUE : FALSE ;
$is_s2016_composante_maitrise = ( ($critere_objet=='socle2016_composante_maitrise') && $cycle_id && $composante_id && $nb_criteres_maitrise ) ? TRUE : FALSE ;
$critere_valide = ( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA || $is_s2016_domaine_maitrise || $is_s2016_composante_maitrise ) ? TRUE : FALSE ;

$tab_types = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

if( !$critere_valide || !$groupe_id || !$groupe_nom || !isset($tab_types[$groupe_type]) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$champs = 'user_id, user_nom, user_prenom';
$tab_eleve = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs ) ;
$eleve_nb = count($tab_eleve);
if(!$eleve_nb)
{
  Json::end( FALSE , 'Aucun élève trouvé dans le regroupement indiqué !' );
}
$tab_eleve_id = array();
foreach($tab_eleve as $DB_ROW)
{
  $tab_eleve_id[] = $DB_ROW['user_id'];
}
$liste_eleve_id = implode(',',$tab_eleve_id);

// Pour un professeur on vérifie que ce sont bien ses élèves
if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') )
{
  $tab_eleves_non_rattaches = array_diff( $tab_eleve_id , $_SESSION['PROF_TAB_ELEVES'] );
  if(!empty($tab_eleves_non_rattaches))
  {
    // On vérifie de nouveau, au cas où l'admin viendrait d'ajouter une affectation
    $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    $tab_eleves_non_rattaches = array_diff( $tab_eleve_id , $_SESSION['PROF_TAB_ELEVES'] );
    if(!empty($tab_eleves_non_rattaches))
    {
      Json::end( FALSE , 'Élève(s) non rattaché(s) à votre compte enseignant !' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Suite du code un peu en vrac avec des reprises et des adaptations de morceaux existants...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_checkbox = ( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && (SACoche!='webservices') ) ? TRUE : FALSE ;

$tab_eval         = array();  // [eleve_id][item_id][]['note'] => note   [type "pourcentage" uniquement]
$tab_item         = array();  // [item_id] => array(calcul_methode,calcul_limite); [type "pourcentage" uniquement]

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// =====> Cas n°1 : moyenne des scores d'acquisition d'items matières sélectionnés
// =====> Cas n°2 : pourcentage d'items acquis d'items matières sélectionnés

if( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA )
{
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $compet_liste , TRUE /*detail*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_item[$DB_ROW['item_id']] = array(
      'item_coef'      => $DB_ROW['item_coef'],
      'calcul_methode' => $DB_ROW['calcul_methode'],
      'calcul_limite'  => $DB_ROW['calcul_limite'],
    );
  }
  // Un directeur effectuant une recherche sur un grand nombre d'items pour tous les élèves de l'établissement peut provoquer un dépassement de mémoire.
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $compet_liste , 0 /*matiere_id*/ , NULL /*date_mysql_debut*/ , NULL /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , TRUE /*onlynote*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
  }
}

// =====> Cas n°3 : degré de maîtrise d'un domaine du socle 2016
// =====> Cas n°4 : degré de maîtrise d'une composante du socle 2016

if( $is_s2016_domaine_maitrise || $is_s2016_composante_maitrise )
{
  // Récupération de la liste des items
  $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_associations_items_composantes( $cycle_id , FALSE /*with_detail*/ , $domaine_id , $composante_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_item[$DB_ROW['item_id']] = $DB_ROW['item_id'];
  }
  if(count($tab_item))
  {
    $liste_item_id = implode(',',$tab_item);
    $tab_item = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , 0 /*matiere_id*/ , FALSE /*date_mysql_debut*/ , FALSE /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , TRUE /*onlynote*/ , FALSE /*first_order_by_date*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
      $tab_item[$DB_ROW['item_id']] = TRUE;
    }
    if(count($tab_item))
    {
      $liste_item_id = implode(',',array_keys($tab_item));
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , FALSE /*detail*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_item[$DB_ROW['item_id']] = array(
          'calcul_methode' => $DB_ROW['calcul_methode'],
          'calcul_limite'  => $DB_ROW['calcul_limite'],
        );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////
DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement des données => remplissage du tableau $tab_tr[]
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// =====> Cas n°1 : moyenne des scores d'acquisition d'items matières sélectionnés
// =====> Cas n°2 : pourcentage d'items acquis d'items matières sélectionnés

if( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA )
{
  $tab_eleve_moy_scores  = array();
  $tab_eleve_pourcentage = array();
  $tab_init = array_fill_keys( array_keys($_SESSION['ACQUIS']) , 0 ) + array( 'nb'=>0 , '%'=>FALSE );
  // Pour chaque élève...
  foreach($tab_eleve_id as $eleve_id)
  {
    $tab_eleve_moy_scores[$eleve_id]  = FALSE;
    $tab_eleve_pourcentage[$eleve_id] = $tab_init;
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      // Pour chaque item...
      $tab_score_item = array();
      foreach($tab_eval[$eleve_id] as $item_id => $tab_devoirs)
      {
        extract($tab_item[$item_id]); // $item_coef $calcul_methode $calcul_limite
        // calcul du bilan de l'item
        $tab_score_item[$item_id] = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , NULL /*date_mysql_debut*/ );
      }
      // calcul des bilans des scores
      $tableau_score_filtre = array_filter($tab_score_item,'non_vide');
      $nb_scores = count( $tableau_score_filtre );
      // la moyenne peut être pondérée par des coefficients
      $somme_scores_ponderes = 0;
      $somme_coefs = 0;
      if($nb_scores)
      {
        foreach($tableau_score_filtre as $item_id => $item_score)
        {
          $somme_scores_ponderes += $item_score*$tab_item[$item_id]['item_coef'];
          $somme_coefs += $tab_item[$item_id]['item_coef'];
        }
        $somme_scores_simples = array_sum($tableau_score_filtre);
      }
      // ... un pour la moyenne des pourcentages d'acquisition
      if($with_coef) { $tab_eleve_moy_scores[$eleve_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : FALSE ; }
      else           { $tab_eleve_moy_scores[$eleve_id] = ($nb_scores)   ? round($somme_scores_simples/$nb_scores,0)    : FALSE ; }
      // ... un pour le nombre d\'items considérés acquis ou pas
      if($nb_scores)
      {
        $tab_eleve_pourcentage[$eleve_id]       = OutilBilan::compter_nombre_acquisitions_par_etat( $tableau_score_filtre );
        $tab_eleve_pourcentage[$eleve_id]['nb'] = $nb_scores;
        $tab_eleve_pourcentage[$eleve_id]['%']  = OutilBilan::calculer_pourcentage_acquisition_items( $tab_eleve_pourcentage[$eleve_id] , $nb_scores );
      }
    }
  }
  // On ne garde que les lignes qui satisfont au critère demandé
  $tab_tr = array();
  foreach($tab_eleve as $tab)
  {
    extract($tab);  // $user_id $user_nom $user_prenom
    if($is_matiere_items_bilanMS)
    {
      $user_acquisition_etat = ($tab_eleve_moy_scores[$user_id]===FALSE) ? 0 : OutilBilan::determiner_etat_acquisition($tab_eleve_moy_scores[$user_id]) ;
      if( in_array( $user_acquisition_etat , $critere_tab_seuil_acquis ) )
      {
        $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
        $tab_tr[] = '<tr>'.$checkbox.'<td>'.html($user_nom.' '.$user_prenom).'</td>'.Html::td_score( $tab_eleve_moy_scores[$user_id] , 'score' /*methode_tri*/ , '' /*pourcent*/ ).'</tr>';
      }
    }
    elseif($is_matiere_items_bilanPA)
    {
      $user_acquisition_etat = ($tab_eleve_pourcentage[$user_id]===FALSE) ? 0 : OutilBilan::determiner_etat_acquisition($tab_eleve_pourcentage[$user_id]['%']) ;
      if( in_array( $user_acquisition_etat , $critere_tab_seuil_acquis ) )
      {
        $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
        $tab_tr[] = '<tr>'.$checkbox.'<td>'.html($user_nom.' '.$user_prenom).'</td>'.Html::td_pourcentage( 'td' , $tab_eleve_pourcentage[$user_id] , TRUE /*detail*/ , FALSE /*largeur*/ ).'</tr>';
      }
    }
  }
}

// =====> Cas n°3 : degré de maîtrise d'un domaine du socle 2016
// =====> Cas n°4 : degré de maîtrise d'une composante du socle 2016

if( $is_s2016_domaine_maitrise || $is_s2016_composante_maitrise )
{
  $tab_init_score = array_fill_keys( array_keys($_SESSION['ACQUIS']) , 0 ) + array( 'nb'=>0 , '%'=>FALSE ) ;
  // Pour chaque élève...
  foreach($tab_eleve_id as $eleve_id)
  {
    $tab_score_eleve[$eleve_id] = $tab_init_score;
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      // Pour chaque item évalué...
      foreach($tab_eval[$eleve_id] as $item_id => $tab_devoirs)
      {
        extract($tab_item[$item_id]); // $calcul_methode $calcul_limite
        // calcul du bilan de l'item
        $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , NULL /*date_mysql_debut*/ );
        if($score!==FALSE)
        {
          // on détermine si il est acquis ou pas
          $indice = OutilBilan::determiner_etat_acquisition( $score );
          $tab_score_eleve[$eleve_id][$indice]++;
          $tab_score_eleve[$eleve_id]['nb']++;
        }
      }
      // On calcule les pourcentages d'acquisition à partir du nombre d'items de chaque état
      $tab_score_eleve[$eleve_id]['%'] = ($tab_score_eleve[$eleve_id]['nb']) ? OutilBilan::calculer_pourcentage_acquisition_items( $tab_score_eleve[$eleve_id] , $tab_score_eleve[$eleve_id]['nb'] ) : FALSE ;
    }
  }
  // Récupérer et mettre en session les seuils pour les degrés de maîtrise du livret
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos('cycle'.$cycle_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
    $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
    $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
    $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
  }
  // On ne garde que les lignes qui satisfont au critère demandé
  $tab_tr = array();
  foreach($tab_eleve as $tab)
  {
    extract($tab);  // $user_id $user_nom $user_prenom
    $pourcentage = $tab_score_eleve[$user_id]['%'];
    $indice = OutilBilan::determiner_degre_maitrise($pourcentage) ;
    if( in_array( $indice , $critere_tab_seuil_maitrise ) )
    {
      $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$user_id.'" /></td>' : '' ;
      $tab_tr[] = '<tr>'.$checkbox.'<td>'.html($user_nom.' '.$user_prenom).'</td>'.Html::td_maitrise( $indice , $pourcentage , 'score' /*methode_tri*/ , '%' /*pourcent*/ , FALSE /*all_columns*/ ).'</tr>';
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_resultats = count($tab_tr);
$checkbox = ($affichage_checkbox && $nb_resultats) ? '<td class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></td>' : '' ;
$retour  = '<hr />'.NL;
$retour .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">'.NL : '' ;
$retour .= '<table class="bilan"><thead>'.NL.'<tr>'.$checkbox.'<th>Élève</th><th>État</th></tr>'.NL.'</thead><tbody>'.NL;
$retour .= ($nb_resultats) ? implode(NL,$tab_tr).NL : '<tr><td colspan="2">aucun résultat</td></tr>'.NL ;
$retour .= '</tbody></table>'.NL;
$retour .= ($affichage_checkbox && $nb_resultats) ? HtmlForm::afficher_synthese_exploitation('eleves') : '' ;
$retour .= ($affichage_checkbox) ? '</form>' : '' ;

Json::end( TRUE , $retour );

?>
