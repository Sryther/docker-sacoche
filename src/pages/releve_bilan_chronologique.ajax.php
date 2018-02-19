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

$objet             = (isset($_POST['f_objet']))             ? Clean::texte($_POST['f_objet'])                  : '';
$matiere_id        = (isset($_POST['f_matiere']))           ? Clean::entier($_POST['f_matiere'])               : 0;
$matiere_nom       = (isset($_POST['f_matiere_nom']))       ? Clean::texte($_POST['f_matiere_nom'])            : '';
$mode_synthese     = (isset($_POST['f_mode_synthese']))     ? Clean::texte($_POST['f_mode_synthese'])          : '';
$fusion_niveaux    = (isset($_POST['f_fusion_niveaux']))    ? 1                                                : 0;
$indicateur        = (isset($_POST['f_indicateur']))        ? Clean::texte($_POST['f_indicateur'])             : '';
$conversion_sur_20 = (isset($_POST['f_conversion_sur_20'])) ? 1                                                : 0;
$with_coef         = 1; // Il n'y a que des courbes par matière et pas de courbe commune : on prend en compte les coefficients pour chaque courbe matière.
$groupe_id         = (isset($_POST['f_groupe']))            ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_type       = (isset($_POST['f_groupe_type']))       ? Clean::lettres($_POST['f_groupe_type'])          : ''; // En vérité, ne sert pas ici.
$eleve_id          = (isset($_POST['f_eleve']))             ? Clean::entier($_POST['f_eleve'])                 : 0;
$periode_id        = (isset($_POST['f_periode']))           ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut        = (isset($_POST['f_date_debut']))        ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin          = (isset($_POST['f_date_fin']))          ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif        = (isset($_POST['f_retroactif']))        ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$only_socle        = (isset($_POST['f_only_socle']))        ? 1                                                : 0;
$eleves_ordre      = (isset($_POST['f_eleves_ordre']))      ? Clean::texte($_POST['f_eleves_ordre'])           : ''; // En vérité, ne sert pas ici.
$echelle           = (isset($_POST['f_echelle']))           ? Clean::texte($_POST['f_echelle'])                : '';

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_matiere = (isset($_POST['f_matieres']))     ? ( (is_array($_POST['f_matieres']))     ? $_POST['f_matieres']     : explode(',',$_POST['f_matieres'])     ) : array() ;
$tab_items   = (isset($_POST['f_compet_liste'])) ? ( (is_array($_POST['f_compet_liste'])) ? $_POST['f_compet_liste'] : explode('_',$_POST['f_compet_liste']) ) : array() ;
$tab_matiere = array_filter( Clean::map('entier',$tab_matiere) , 'positif' );
$tab_items   = array_filter( Clean::map('entier',$tab_items)   , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere);
$liste_item_id    = implode(',',$tab_items);

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  if(!Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE']))      { $indicateur = 'pourcentage_acquis'; }
  if(!Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS'])) { $indicateur = 'moyenne_scores'; }
  if(!Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION']))   { $indicateur = ''; }
  $conversion_sur_20 = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20']) ? $conversion_sur_20 : 0 ;
  // Pour un élève on surcharge avec les données de session
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $groupe_id = $_SESSION['ELEVE_CLASSE_ID'];
    $eleve_id  = $_SESSION['USER_ID'];
  }
  // Pour un parent on vérifie que c'est bien un de ses enfants
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $is_enfant_legitime = FALSE;
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      if($DB_ROW['valeur']==$eleve_id)
      {
        $is_enfant_legitime = TRUE;
        break;
      }
    }
    if(!$is_enfant_legitime)
    {
      Json::end( FALSE , 'Enfant non rattaché à votre compte parent !' );
    }
  }
}
if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') )
{
  // Pour un professeur on vérifie que c'est bien un de ses élèves
  if(!in_array($eleve_id, $_SESSION['PROF_TAB_ELEVES']))
  {
    // On vérifie de nouveau, au cas où l'admin viendrait d'ajouter une affectation
    $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    if(!in_array($eleve_id, $_SESSION['PROF_TAB_ELEVES']))
    {
      Json::end( FALSE , 'Élève non rattaché à votre compte enseignant !' );
    }
  }
}

$tab_objet = array(
  'matieres'         => "Matières",
  'matiere_niveau'   => "Niveaux d'une matière",
  'matiere_synthese' => "Synthèses d'une matière",
  'selection'        => "Items sélectionnés",
);
$tab_indicateur = array(
  'moyenne_scores'     => "Moyenne des scores",
  'pourcentage_acquis' => "Pourcentage d'items acquis",
);

if(
    !isset($tab_objet[$objet]) || !isset($tab_indicateur[$indicateur]) ||
    ( ($objet=='matieres') && !$liste_matiere_id ) ||
    ( ($objet=='matiere_niveau') && ( !$matiere_id || !$matiere_nom ) ) ||
    ( ($objet=='matiere_synthese') && ( !$matiere_id || !$matiere_nom || !$mode_synthese ) ) ||
    ( ($objet=='selection') && !$liste_item_id ) ||
    !$groupe_id || !$groupe_type || !$eleve_id || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$eleves_ordre || !$echelle
  )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

Form::save_choix('bilan_chronologique');

if($objet=='selection')
{
  $indicateur = 'moyenne_scores';
  $conversion_sur_20 = 0;
}

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Initialisation de tableaux

$tab_item       = array();  // [item_id] => array(item_coef,calcul_methode,calcul_limite,calcul_retroactif);
$tab_liste_item = array();  // [i] => item_id
$tab_rubrique   = array();  // [rubrique_id] => rubrique_nom
$tab_eval       = array();  // [eleve_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.
$tab_date       = array();  // [eleve_id][date_js][item_id] => nb_evals
$tab_info       = array();  // [eleve_id][rubrique_id][date_js][info] => info
$tab_rubrique_for_item = array();  // [item_id] => rubrique_id

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Période concernée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($periode_id==0)
{
  $date_mysql_debut = To::date_french_to_mysql($date_debut);
  $date_mysql_fin   = To::date_french_to_mysql($date_fin);
}
else
{
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Le regroupement et la période ne sont pas reliés !' );
  }
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = To::date_mysql_to_french($date_mysql_debut);
  $date_fin   = To::date_mysql_to_french($date_mysql_fin);
}
if($date_mysql_debut>$date_mysql_fin)
{
  Json::end( FALSE , 'La date de début est postérieure à la date de fin !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items travaillés durant la période choisie, pour l'élève selectionné, pour la ou les matières indiquées, ou les items indiqués
// Récupération de la liste des rubriques (matières, synthèses par thèmes / domaines, niveaux) travaillées (affinée suivant les items trouvés)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($objet=='matieres') || ($objet=='matiere_niveau') )
{
  $rubrique_type    =($objet=='matieres') ? 'matiere'         : 'niveau'    ;
  $liste_matiere_id =($objet=='matieres') ? $liste_matiere_id : $matiere_id ;
  list( $tab_item , $tab_rubrique ) = DB_STRUCTURE_BILAN::DB_recuperer_items_travailles( $eleve_id , $liste_matiere_id , $only_socle , $date_mysql_debut , $date_mysql_fin , $rubrique_type );
}

if($objet=='matiere_synthese')
{
  list( $tab_item , $tab_rubrique ) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_synthese( $eleve_id , $matiere_id , $only_socle , 0 /*only_niveau*/ , $mode_synthese , $fusion_niveaux , $date_mysql_debut , $date_mysql_fin , FALSE /*aff_socle*/ );
}

if($objet=='selection')
{
  list( $tab_item, /*tab_matiere*/ ) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_selection( $eleve_id , $liste_item_id , $date_mysql_debut , $date_mysql_fin , 0 /*aff_domaine*/ , 0 /*aff_theme*/ , 0 /*aff_socle*/ );
}

$item_nb = count($tab_item);
if( !$item_nb && (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ) // Dans le cas d'un professeur / directeur, où l'on regarde les élèves d'un groupe un à un, ce ne doit pas être bloquant.
{
  Json::end( FALSE , 'Aucun item évalué sur la période '.$date_debut.' ~ '.$date_fin.' selon les paramètres choisis !' );
}
$tab_liste_item = array_keys($tab_item);
$liste_item_id  = implode(',',$tab_liste_item);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matières, pour les élèves selectionnés, sur la période sélectionnée
// Comme un seul élève est concerné à chaque appel, il n'y a pas le problème de certains items à éliminer car pouvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
// Il faut aussi retenir, à une date donnée, combien d'évaluations sont concernées.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function date_mysql_to_date_js($date_mysql)
{
  list($annee,$mois,$jour) = explode('-',$date_mysql);
  return 'Date.UTC('.$annee.','.((int)$mois-1).','.(int)$jour.')';
}

$date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql');
    if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
$DB_TAB = ($item_nb) ? DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ , TRUE /*first_order_by_date*/ ) : array() ;
foreach($DB_TAB as $DB_ROW)
{
  $retro_item = $tab_item[$DB_ROW['item_id']][0]['calcul_retroactif'];
  if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
  {
    $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][] = array(
      'note' => $DB_ROW['note'],
      'date' => $DB_ROW['date'],
    );
    if($objet=='matiere_synthese')
    {
      $rubrique_ref = $tab_item[$DB_ROW['item_id']][0]['synthese_ref'];
    }
    elseif($objet=='selection')
    {
      $rubrique_ref = $tab_item[$DB_ROW['item_id']][0]['matiere_ref'].$tab_item[$DB_ROW['item_id']][0]['ref_auto'];
      $tab_rubrique[ $rubrique_ref ] = $tab_item[$DB_ROW['item_id']][0]['item_nom'];
    }
    else
    {
      $rubrique_ref = $DB_ROW[$rubrique_type.'_id'];
    }
    $tab_rubrique_for_item[$DB_ROW['item_id']] = $rubrique_ref;
    $date_mysql = ($DB_ROW['date']>=$date_mysql_debut) ? $DB_ROW['date'] : $date_mysql_debut ;
    $date_js = date_mysql_to_date_js($date_mysql);
    $tab_date[$DB_ROW['eleve_id']][$date_js][$DB_ROW['item_id']] = count($tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']]);
    $tab_info[$DB_ROW['eleve_id']][$rubrique_ref][$date_js][$DB_ROW['info']] = $DB_ROW['info'];

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
// Tableaux et variables pour mémoriser les infos ; dans cette partie on fait les calculs et on remplit le tableau js pour l'affichage au fur et à mesure
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_score_eleve_item      = array();  // [eleve_id][rubrique_id][item_id] => score
$tab_moyenne_eleve_rubrique = array();  // [eleve_id][rubrique_id] => moyenne : Retenir la moyenne des scores d'acquisitions ou le pourcentage d'items acquis / élève / matière (sert pour ajouter une dernière valeur)

$tab_graph_data = array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// C'est parti !!!
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$max_value = ($conversion_sur_20) ? 20 : 100 ;
if(count($tab_date))
{
  // Pour chaque évaluation...
  foreach($tab_date[$eleve_id] as $date_js => $tab_items)
  {
    $tab_rubrique_todo_moyenne = array();
    // On (re)-calcule les scores des items concernés
    foreach($tab_items as $item_id => $nb_evals)
    {
      extract($tab_item[$item_id][0]);  // $item_coef $calcul_methode $calcul_limite $calcul_retroactif
      $rubrique_id = $tab_rubrique_for_item[$item_id];
      $tab_score_eleve_item[$eleve_id][$rubrique_id][$item_id] = OutilBilan::calculer_score( array_slice($tab_eval[$eleve_id][$item_id],0,$nb_evals) , $calcul_methode , $calcul_limite , $date_mysql_debut );
      $tab_rubrique_todo_moyenne[] = $rubrique_id;
    }
    // On (re)-calcule les moyennes des matières concernées
    foreach($tab_rubrique_todo_moyenne as $rubrique_id)
    {
      // calcul des bilans des scores
      $tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id][$rubrique_id],'non_vide');
      $nb_scores = count( $tableau_score_filtre );
      // la moyenne peut être pondérée par des coefficients
      $somme_scores_ponderes = 0;
      $somme_coefs = 0;
      if($nb_scores)
      {
        foreach($tableau_score_filtre as $item_id => $item_score)
        {
          $somme_scores_ponderes += $item_score*$tab_item[$item_id][0]['item_coef'];
          $somme_coefs += $tab_item[$item_id][0]['item_coef'];
        }
        $somme_scores_simples = array_sum($tableau_score_filtre);
      }
      // Soit la moyenne des pourcentages d'acquisition
      if($indicateur=='moyenne_scores')
      {


        if($with_coef) { $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : FALSE ; }
        else           { $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id] = ($nb_scores)   ? round($somme_scores_simples/$nb_scores,0)    : FALSE ; }
      }
      // Soit le nombre d'items considérés acquis ou pas
      elseif($indicateur=='pourcentage_acquis')
      {
        if($nb_scores)
        {

          $tab_acquisitions = OutilBilan::compter_nombre_acquisitions_par_etat( $tableau_score_filtre );
          $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id] = OutilBilan::calculer_pourcentage_acquisition_items( $tab_acquisitions , $nb_scores );
        }
        else
        {
          $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id] = FALSE;
        }
      }
      if($tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id]!==FALSE)
      {
        $valeur = ($conversion_sur_20) ? $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id]/5 : $tab_moyenne_eleve_rubrique[$eleve_id][$rubrique_id] ;
        $tab_graph_data[$rubrique_id][$date_js] = $valeur;
        $max_value = max( $max_value , $valeur);
      }
    }
  }
  // Ajouter un point en fin de période pour chaque rubrique
  /*
  foreach($tab_graph_data as $rubrique_id => $tab_data)
  {
    $last_valeur = end($tab_data);
    $tab_graph_data[$rubrique_id][$date_js] = $last_valeur;
  }
  */
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// $tab_graph_series n'est pas rangé avec des matières classées comme paramétré par l'admin car on avait besoin de récupérer les scores dans l'ordre chnonologique
// Pas grave puisqu'il vaut mieux au contraire avoir la légende par ordre alphabétique des rubriques.
asort($tab_rubrique);

if($objet=='matieres')
{
  $titre = $tab_objet[$objet].' - '.$tab_indicateur[$indicateur];
}
elseif($objet=='selection')
{
  $titre = $tab_objet[$objet].' - '.'Scores calculés';
}
else
{
  $titre = $tab_objet[$objet].' - '.$matiere_nom.' - '.$tab_indicateur[$indicateur] ;
}

Json::add_row( 'titre' , $titre );

// Échelle sur l'axe des ordonnées
$min_max = ($echelle=='fixe') ? 'min: 0, max: '.$max_value : 'minPadding: 0, maxPadding: 0' ;
Json::add_row( 'script' , 'ChartOptions.yAxis = [ { '.$min_max.', title: null } , { '.$min_max.', title: null, opposite: true } ];' );

// Séries de valeurs
$tab_graph_series = array();
if(count($tab_graph_data))
{
  foreach($tab_rubrique as $rubrique_id => $rubrique_nom)
  {
    if(isset($tab_graph_data[$rubrique_id]))
    {
      $tab_serie = array();
      foreach($tab_graph_data[$rubrique_id] as $date_js => $valeur)
      {
        $name = addcslashes(implode('<br />',$tab_info[$eleve_id][$rubrique_id][$date_js]),'"');
        // $tab_serie[] = '['.$date_js.','.$valeur.']';
        $tab_serie[] = '{x:'.$date_js.',y:'.$valeur.',name:"'.$name.'"}';
      }
      $tab_graph_series[] = '{name:"'.addcslashes($rubrique_nom,'"').'",data:['.implode(',',$tab_serie).']}';
    }
  }
}
Json::add_row( 'script' , 'ChartOptions.series = ['.implode(',',$tab_graph_series).'];' );
Json::add_row( 'script' , 'graphique = new Highcharts.Chart(ChartOptions);' );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( TRUE );

?>
