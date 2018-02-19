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

/**
 * Code inclus commun aux pages
 * [./pages/releve_socle2016.ajax.php]
 * [./pages/export_fichier.ajax.php]
 * [ livret scolaire en prévision... ]
 */

/* TODO -> À RETIRER SI INUTILE : $make_officiel + $make_livret + $make_action + $make_html + $make_pdf + $tab_destinataires */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

/*
$type_individuel | $type_synthese
*/

// Chemins d'enregistrement

// $fichier_nom = ($make_action!='imprimer') ? 'releve_socle2016_'.$releve_modele.'_'.Clean::fichier($groupe_nom).'_<REPLACE>_'.FileSystem::generer_fin_nom_fichier__date_et_alea() : 'officiel_'.$BILAN_TYPE.'_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea() ;
$fichier_nom = 'releve_socle2016_'.Clean::fichier($groupe_nom).'_<REPLACE>_'.FileSystem::generer_fin_nom_fichier__date_et_alea();

// Si positionnement demandé ou besoin pour synthèse
$calcul_positionnement = ( $type_synthese || $aff_socle_position || $aff_socle_points_DNB ) ? TRUE : FALSE ;

// Initialisation de tableaux

$tab_socle_domaine    = array();  // [socle_domaine_id] => domaine_nom;
$tab_socle_composante = array();  // [socle_domaine_id][socle_composante_id] => composante_nom;
$tab_join_item_socle  = array();  // [item_id] => socle_composante_id;
$tab_eleve_infos  = array();  // [eleve_id] => array(eleve_INE,eleve_nom,eleve_prenom,date_naissance)
$tab_item_infos   = array();  // [item_id] => array(item_ref,item_nom,item_cart,item_lien,matiere_id,calcul_methode,calcul_limite);
$tab_eval         = array();  // [eleve_id][item_id][]['note'] => note

// Initialisation de variables

$liste_eleve_id = implode(',',$tab_eleve);

if( ($make_html) || ($make_pdf) )
{
  $texte_coef  = ''; // sans objet
  $texte_socle = ''; // sans objet
  if(!$aff_lien)  { $texte_lien_avant = ''; }
  if(!$aff_lien)  { $texte_lien_apres = ''; }
  $toggle_class = ($aff_start) ? 'toggle_moins' : 'toggle_plus' ;
  $toggle_etat  = ($aff_start) ? '' : ' class="hide"' ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des domaines et composantes du socle (indépendant du cycle sélectionné)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_arborescence();
$socle_domaine_id    = 0;
$socle_composante_id = 0;
foreach($DB_TAB as $DB_ROW)
{
  if( $DB_ROW['socle_domaine_id'] != $socle_domaine_id )
  {
    $socle_domaine_id  = $DB_ROW['socle_domaine_id'];
    $tab_socle_domaine[$socle_domaine_id] = $DB_ROW['socle_domaine_nom_simple'];
  }
  $DB_ROW['socle_composante_id']         = ( ($socle_detail=='detail') || ($socle_domaine_id==1) ) ? $DB_ROW['socle_composante_id']         : $socle_domaine_id*10 ;
  $DB_ROW['socle_composante_nom_simple'] = ( ($socle_detail=='detail') || ($socle_domaine_id==1) ) ? $DB_ROW['socle_composante_nom_simple'] : 'Toutes composantes confondues' ;
  $socle_composante_id = $DB_ROW['socle_composante_id'];
  $tab_socle_composante[$socle_domaine_id][$socle_composante_id] = $DB_ROW['socle_composante_nom_simple'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array(
    'eleve_nom'      => $_SESSION['USER_NOM'],
    'eleve_prenom'   => $_SESSION['USER_PRENOM'],
    'eleve_genre'    => $_SESSION['USER_GENRE'],
    'date_naissance' => $_SESSION['USER_NAISSANCE_DATE'],
    'eleve_INE'      => NULL,
    'eleve_ID_BE'    => NULL,
  );
}
else
{
  $eleves_ordre = ($groupe_type=='Classes') ? 'alpha' : $eleves_ordre ;
  $tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve_id , $eleves_ordre );
  if(!is_array($tab_eleve_infos))
  {
    Json::end( FALSE , 'Aucun élève trouvé correspondant aux identifiants transmis !' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items et des liaisons items / composantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_associations_items_composantes($cycle_id);
foreach($DB_TAB as $DB_ROW)
{
  $socle_composante_id = ( ($socle_detail=='detail') || ($DB_ROW['socle_domaine_id']==1) ) ? $DB_ROW['socle_composante_id'] : $DB_ROW['socle_domaine_id']*10 ;
  $tab_join_item_socle[$DB_ROW['item_id']][$socle_composante_id] = $socle_composante_id;
}
$liste_item_id = implode(',',array_keys($tab_join_item_socle));

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($liste_item_id)
{
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , -1 /*matiere_id*/ , FALSE /*date_mysql_debut*/ , FALSE /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , TRUE /*onlynote*/ , FALSE /*first_order_by_date*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($mode=='auto') || in_array($DB_ROW['matiere_id'],$tab_matiere) )
    {
      $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
      $tab_item_infos[$DB_ROW['item_id']] = TRUE;
    }
  }
  if(count($tab_item_infos))
  {
    $liste_item_id = implode(',',array_keys($tab_item_infos));
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , TRUE /*detail*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['ref_perso'] : $DB_ROW['ref_auto'] ;
      $tab_item_infos[$DB_ROW['item_id']] = array(
        'item_ref'            => $DB_ROW['matiere_ref'].'.'.$item_ref,
        'item_nom'            => $DB_ROW['item_nom'],
        'item_coef'           => $DB_ROW['item_coef'],
        'item_cart'           => $DB_ROW['item_cart'],
        'item_lien'           => $DB_ROW['item_lien'],
        'matiere_id'          => $DB_ROW['matiere_id'],
        'matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
        'calcul_methode'      => $DB_ROW['calcul_methode'],
        'calcul_limite'       => $DB_ROW['calcul_limite'],
      );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les seuils pour les degrés de maîtrise du livret
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos('cycle'.$cycle_id);
foreach($DB_TAB as $DB_ROW)
{
  $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
  $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
  $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
  $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
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
// Initialiser les tableaux pour retenir les données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_init_score = array_fill_keys( array_keys($_SESSION['ACQUIS']) , 0 );
$tab_init_bilan = array( 'nb'=>0 , '%'=>FALSE , 'indice'=>FALSE , 'points'=>0 );
$tab_score_eleve_composante = array();  // [eleve_id][composante_id] => array([etats]) // Retenir le nb d'items acquis ou pas / élève / composante
$tab_bilan_eleve_composante = array();  // [eleve_id][composante_id] => array(nb,%,indice) // Retenir les infos sur les valeurs bilan / élève / composante du socle
$tab_infos_eleve_composante = array();  // [eleve_id][composante_id] => array() // Retenir les infos sur les items travaillés et leurs scores / élève / composante du socle
$tab_contenu_presence       = array( 'eleve' => array() , 'composante' => array() , 'detail' => array() );

$tab_points_valeur = array( 0=>0 , 1=>10 , 2=>25 , 3=>40 , 4=>50 );
$tab_points_texte  = array( 0=>'' , 10=>'10 points' , 25=>'25 points' , 40=>'40 points' , 50=>'50 points' );

// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  $tab_contenu_presence['eleve'][$eleve_id] = 0;
  $tab_bilan_eleve_composante[$eleve_id]['total_dnb'] = 0;
  // Pour chaque domaine...
  foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
  {
    // Pour chaque composante...
    foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
    {
      $tab_score_eleve_composante[$eleve_id][$socle_composante_id] = $tab_init_score;
      $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id] = $tab_init_bilan;
      $tab_infos_eleve_composante[$eleve_id][$socle_composante_id] = array();
      $tab_contenu_presence['composante'][$socle_composante_id] = 0;
      $tab_contenu_presence['detail'][$eleve_id][$socle_composante_id] = 0;
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du bilan relatif au socle, en HTML et PDF => Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$afficher_score = Outil::test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] );

// Pour chaque élève évalué...
foreach($tab_eval as $eleve_id => $tab_eval_eleve)
{
  // Pour chaque item évalué...
  foreach($tab_eval_eleve as $item_id => $tab_devoirs)
  {
    extract($tab_item_infos[$item_id]);  // $item_ref $item_nom $item_coef $item_cart $item_lien $matiere_id $matiere_nb_demandes $calcul_methode $calcul_limite
    // calcul du bilan de l'item
    $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , NULL /*date_mysql_debut*/ );
    if($score!==FALSE)
    {
      // on détermine si il est acquis ou pas
      $indice = OutilBilan::determiner_etat_acquisition( $score );
      // le détail HTML
      if($make_html)
      {
        if($aff_lien)
        {
          $texte_lien_avant = ($item_lien) ? '<a target="_blank" rel="noopener noreferrer" href="'.html($item_lien).'">' : '';
          $texte_lien_apres = ($item_lien) ? '</a>' : '';
        }
        if($_SESSION['USER_PROFIL_TYPE']=='parent')    { $texte_demande_eval = '<q class="demander_non" title="Les demandes d\'évaluations s\'effectuent depuis un compte élève."></q>'; }
        elseif($_SESSION['USER_PROFIL_TYPE']!='eleve') { $texte_demande_eval = ''; }
        elseif(!$matiere_nb_demandes)                  { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour les items de cette matière."></q>'; }
        elseif(!$item_cart)                            { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour cet item précis."></q>'; }
        else                                           { $texte_demande_eval = '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>'; }
      }
      // on enregistre les infos
      foreach($tab_join_item_socle[$item_id] as $socle_composante_id)
      {
        if( $make_html && $type_individuel && $aff_socle_items_acquis )
        {
          $pourcentage = ($afficher_score) ? $score.'%' : '&nbsp;' ;
          $tab_infos_eleve_composante[$eleve_id][$socle_composante_id][] = '<div><span class="pourcentage A'.$indice.'">'.$pourcentage.'</span> '.$texte_coef.$texte_socle.$texte_lien_avant.html($item_ref.' - '.$item_nom).$texte_lien_apres.$texte_demande_eval.'</div>';
        }
        $tab_score_eleve_composante[$eleve_id][$socle_composante_id][$indice]++;
        $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['nb']++;
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On calcule les pourcentages d'acquisition à partir du nombre d'items de chaque état
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($calcul_positionnement)
{
  foreach( $tab_score_eleve_composante as $eleve_id => $tab_score_composante )
  {
    $total_dnb = 0;
    foreach( $tab_score_composante as $socle_composante_id => $tab_score )
    {
      $nb_items = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['nb'];
      $pourcentage = ($nb_items) ? OutilBilan::calculer_pourcentage_acquisition_items( $tab_score , $nb_items ) : FALSE ;
      $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
      $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['%'] = $pourcentage;
      $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['indice'] = $indice;
      $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['points'] = ($indice!==FALSE) ? $tab_points_valeur[$indice] : FALSE ;
      $total_dnb += $tab_points_valeur[$indice];
    }
    $tab_bilan_eleve_composante[$eleve_id]['total_dnb'] = $total_dnb;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Restriction de l'affichage aux seuls éléments ayant fait l'objet d'une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$is_resultat = FALSE;
// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  // Pour chaque domaine...
  foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
  {
    // Pour chaque composante...
    foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
    {
      if( $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id]['nb'] || !$only_presence )
      {
        $tab_contenu_presence['eleve'][$eleve_id]++;
        $tab_contenu_presence['composante'][$socle_composante_id]++;
        $tab_contenu_presence['detail'][$eleve_id][$socle_composante_id]++;
        $is_resultat = TRUE;
      }
    }
  }
}

if(!$is_resultat)
{
  Json::end( FALSE , 'Aucun élève trouvé avec un item évalué relié au cycle '.$cycle_id.' du socle !' );
}

$tab = array_filter( $tab_contenu_presence['eleve'] , 'non_zero' );
$eleve_nb = count($tab);
$composante_nb_moyen = array_sum($tab) / $eleve_nb;

$tab = array_filter( $tab_contenu_presence['composante'] , 'non_zero' );
$composante_nb = count($tab);
$eleve_nb_moyen = array_sum($tab) / $composante_nb;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Nombre de boucles par élève (entre 1 et 3 pour les bilans officiels, dans ce cas $tab_destinataires[] est déjà complété ; une seule dans les autres cas).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!isset($tab_destinataires))
{
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    $tab_destinataires[$eleve_id][0] = TRUE ;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On va passer à la production des documents
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_direct   = ( ( ( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent')) ) && (SACoche!='webservices') ) || ($make_officiel) ) ? TRUE : FALSE ;
$affichage_checkbox = ( $type_synthese && ($_SESSION['USER_PROFIL_TYPE']=='professeur') && (SACoche!='webservices') )                             ? TRUE : FALSE ;

$nb_matieres = count($tab_matiere);
$titre_detail  = ($socle_detail=='detail') ? '(toutes composantes)' : '(rubriques du livret)' ;
$titre_matiere = ($mode=='auto') ? 'Toutes matières' : ( ($nb_matieres==1) ? $matiere_nom : $nb_matieres.' matières' ) ;
$titre = 'Estimation de maîtrise du socle commun '.$titre_detail.' - '.$cycle_nom.' - '.$titre_matiere ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du relevé individuel, en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($type_individuel)
{
  if($make_html)
  {
    $releve_HTML_individuel  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
    $releve_HTML_individuel .= $affichage_direct ? '' : '<h1>'.$titre.'</h1>'.NL;
    $releve_HTML_individuel .= $affichage_direct ? '<input type="hidden" id="demande_periode_debut_date" value="'.$date_mysql_debut.'" />'.NL : '' ;
    $separation = (count($tab_eleve_infos)>1) ? '<hr class="breakafter" />'.NL : '' ;
    $releve_HTML_individuel_javascript = '';
    $tab_legende = array(
      'etat_acquisition' => $aff_socle_items_acquis ,
      'degre_maitrise'   => $aff_socle_position ,
      'socle_points'     => $aff_socle_points_DNB ,
    );
  }
  if($make_pdf)
  {
    // Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
    $releve_PDF_individuel = new PDF_socle2016_releve( $make_officiel , 'portrait' /*orientation*/ , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende );
  }
  /*
   * ********************************
   * Cas d'une présentation par élève
   * ********************************
   */
  if($socle_individuel_format=='eleve')
  {
    if($make_pdf)
    {
      $releve_PDF_individuel->initialiser( $socle_individuel_format , $eleve_nb , $composante_nb , $eleve_nb_moyen , $composante_nb_moyen , $pages_nb , $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB );
    }
    // Pour chaque élève...
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
    {
      if($tab_contenu_presence['eleve'][$eleve_id])
      {
        extract($tab_eleve); // $eleve_INE $eleve_ID_BE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
        foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
        {
          // Si cet élève a été évalué...
          if(isset($tab_eval[$eleve_id]))
          {
            $sous_titre = $groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom;
            $sous_titre.= ($aff_socle_points_DNB) ? ' - '.$tab_bilan_eleve_composante[$eleve_id]['total_dnb'].' points sur 400' : '' ;
            // Intitulé
            if($make_html)
            {
              $releve_HTML_individuel .=  $separation.'<h2>'.html($sous_titre).'</h2>'.NL;
              $releve_HTML_individuel .=  '<table class="livret"><tbody>'.NL;
            }
            if($make_pdf)
            {
              $nb_lignes  = $tab_contenu_presence['eleve'][$eleve_id];
              $releve_PDF_individuel->entete( $titre , $sous_titre , $nb_lignes , $pages_nb );
            }
            // Pour chaque domaine / composante...
            foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
            {
              foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
              {
                if($tab_contenu_presence['detail'][$eleve_id][$socle_composante_id])
                {
                  $tab_score = $tab_score_eleve_composante[$eleve_id][$socle_composante_id];
                  $tab_bilan = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id];
                  if($make_html) { $releve_HTML_individuel .= '<tr><td><b>'.html($socle_domaine_id.' '.$socle_domaine_nom).'</b><br />'.html($socle_composante_nom).'</td>'; }
                  if($make_pdf)  { $releve_PDF_individuel->ligne_debut( $socle_domaine_id.' '.$socle_domaine_nom , $socle_composante_nom ); }
                  if($aff_socle_items_acquis)
                  {
                    if($make_html)
                    {
                      if(!empty($tab_infos_eleve_composante[$eleve_id][$socle_composante_id]))
                      {
                        $detail_acquisition = OutilBilan::afficher_nombre_acquisitions_par_etat( $tab_score , TRUE /*detail_couleur*/ );
                        $releve_HTML_individuel .= '<td><a href="#toggle" class="'.$toggle_class.'" title="Voir / masquer le détail des items associés." id="to_'.$eleve_id.'_'.$socle_composante_id.'"></a> '.$detail_acquisition;
                        $releve_HTML_individuel .= '<div id="'.$eleve_id.'_'.$socle_composante_id.'"'.$toggle_etat.'>'.implode('',$tab_infos_eleve_composante[$eleve_id][$socle_composante_id]).'</div></td>';
                      }
                      else
                      {
                        $releve_HTML_individuel .= '<td><span class="notnow">aucun item évalué</span></td>' ;
                      }
                    }
                    if($make_pdf)
                    {
                      $tab_score = array_filter($tab_score,'non_zero');
                      $releve_PDF_individuel->afficher_proportion_acquis( $releve_PDF_individuel->synthese_largeur , $releve_PDF_individuel->cases_hauteur , $tab_score , $tab_bilan['nb'] /*total*/ , TRUE /*avec_texte_nombre*/ , TRUE /*avec_texte_code*/ );
                    }
                  }
                  if($aff_socle_position)
                  {
                    if($make_html) { $releve_HTML_individuel .= Html::td_maitrise( $tab_bilan['indice'] , $tab_bilan['%'] , $tableau_tri_maitrise_mode , '%' /*pourcent*/ , TRUE /*all_columns*/ ); }
                    if($make_pdf)  { $releve_PDF_individuel->afficher_degre_maitrise( $tab_bilan['indice'] , $tab_bilan['%'] , '%' /*pourcent*/ , TRUE /*all_columns*/ ); }
                  }
                  if($aff_socle_points_DNB)
                  {
                    if($make_html) { $releve_HTML_individuel .= '<td><b>'.$tab_points_texte[$tab_bilan['points']].'</b></td>'; }
                    if($make_pdf)  { $releve_PDF_individuel->cellule_nombre_points( $tab_points_texte[$tab_bilan['points']] ); }
                  }
                  if($make_html)
                  {
                    $releve_HTML_individuel .= '</tr>'.NL;
                  }
                  if($make_pdf)
                  {
                    $releve_PDF_individuel->ligne_retour();
                  }
                }
              }
            }
            if($make_html) { $releve_HTML_individuel .= '</tbody></table>'.NL; }
            // Légende
            if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
            {
              if($make_html) { $releve_HTML_individuel .= Html::legende($tab_legende); }
              if($make_pdf)  { $releve_PDF_individuel->legende( $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB ); }
            }
          }
        }
      }
    }
  }
  /*
   * *******************************
   * Cas d'une présentation par composante
   * *******************************
   */
  elseif($socle_individuel_format=='composante')
  {
    if($make_pdf)
    {
      $releve_PDF_individuel->initialiser( $socle_individuel_format , $eleve_nb , $composante_nb , $eleve_nb_moyen , $composante_nb_moyen , $pages_nb , $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB );
    }
    // Pour chaque domaine / composante...
    foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
    {
      foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
      {
        if($tab_contenu_presence['composante'][$socle_composante_id])
        {
          $sous_titre = $socle_domaine_id.' '.$socle_domaine_nom;
          // Intitulé
          if($make_html)
          {
            $releve_HTML_individuel .=  $separation.'<h2>'.html($sous_titre).'</h2>'.NL;
            $releve_HTML_individuel .=  '<h3>'.html($socle_composante_nom).'</h3>'.NL;
            $releve_HTML_individuel .=  '<table class="livret"><tbody>'.NL;
          }
          if($make_pdf)
          {
            $nb_lignes  = $tab_contenu_presence['composante'][$socle_composante_id];
            $releve_PDF_individuel->entete( $titre , $sous_titre.' - '.$socle_composante_nom , $nb_lignes , $pages_nb );
          }
          // Pour chaque élève...
          foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
          {
            if($tab_contenu_presence['detail'][$eleve_id][$socle_composante_id])
            {
              extract($tab_eleve); // $eleve_INE $eleve_ID_BE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
              foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
              {
                // Si cet élève a été évalué...
                if(isset($tab_eval[$eleve_id]))
                {
                  $classe_eleve = $groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom;
                  $classe_eleve.= ($aff_socle_points_DNB) ? ' - '.$tab_bilan_eleve_composante[$eleve_id]['total_dnb'].' points sur 400' : '' ;
                  $tab_score = $tab_score_eleve_composante[$eleve_id][$socle_composante_id];
                  $tab_bilan = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id];
                  if($make_html) { $releve_HTML_individuel .= '<tr><td>'.html($classe_eleve).'</td>'; }
                  if($make_pdf)  { $releve_PDF_individuel->ligne_debut( $classe_eleve ); }
                  if($aff_socle_items_acquis)
                  {
                    if($make_html)
                    {
                      if(!empty($tab_infos_eleve_composante[$eleve_id][$socle_composante_id]))
                      {
                        $detail_acquisition = OutilBilan::afficher_nombre_acquisitions_par_etat( $tab_score , TRUE /*detail_couleur*/ );
                        $releve_HTML_individuel .= '<td><a href="#toggle" class="'.$toggle_class.'" title="Voir / masquer le détail des items associés." id="to_'.$eleve_id.'_'.$socle_composante_id.'"></a> '.$detail_acquisition;
                        $releve_HTML_individuel .= '<div id="'.$eleve_id.'_'.$socle_composante_id.'"'.$toggle_etat.'>'.implode('',$tab_infos_eleve_composante[$eleve_id][$socle_composante_id]).'</div></td>';
                      }
                      else
                      {
                        $releve_HTML_individuel .= '<td><span class="notnow">aucun item évalué</span></td>' ;
                      }
                    }
                    if($make_pdf)
                    {
                      $tab_score = array_filter($tab_score,'non_zero');
                      $releve_PDF_individuel->afficher_proportion_acquis( $releve_PDF_individuel->synthese_largeur , $releve_PDF_individuel->cases_hauteur , $tab_score , $tab_bilan['nb'] /*total*/ , TRUE /*avec_texte_nombre*/ , TRUE /*avec_texte_code*/ );
                    }
                  }
                  if($aff_socle_position)
                  {
                    if($make_html) { $releve_HTML_individuel .= Html::td_maitrise( $tab_bilan['indice'] , $tab_bilan['%'] , $tableau_tri_maitrise_mode , '%' /*pourcent*/ , TRUE /*all_columns*/ ); }
                    if($make_pdf)  { $releve_PDF_individuel->afficher_degre_maitrise( $tab_bilan['indice'] , $tab_bilan['%'] , '%' /*pourcent*/ , TRUE /*all_columns*/ ); }
                  }
                  if($aff_socle_points_DNB)
                  {
                    if($make_html) { $releve_HTML_individuel .= '<td><b>'.$tab_points_texte[$tab_bilan['points']].'</b></td>'; }
                    if($make_pdf)  { $releve_PDF_individuel->cellule_nombre_points( $tab_points_texte[$tab_bilan['points']] ); }
                  }
                  if($make_html)
                  {
                    $releve_HTML_individuel .= '</tr>'.NL;
                  }
                  if($make_pdf)
                  {
                    $releve_PDF_individuel->ligne_retour();
                  }
                }
              }
            }
          }
          if($make_html) { $releve_HTML_individuel .= '</tbody></table>'.NL; }
          // Légende
          if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
          {
            if($make_html) { $releve_HTML_individuel .= Html::legende($tab_legende); }
            if($make_pdf)  { $releve_PDF_individuel->legende( $aff_socle_items_acquis , $aff_socle_position , $aff_socle_points_DNB ); }
          }
        }
      }
    }
  }
  // On enregistre les sorties HTML et PDF et CSV
  if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.html' , $releve_HTML_individuel ); }
  if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf'  , $releve_PDF_individuel ); }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la synthèse collective en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($type_synthese)
{
  if($socle_synthese_affichage=='pourcentage')
  {
    $objet = 'Synthèse du pourcentage d\'items acquis';
    $clef = '%';
    $unit = '%';
  }
  elseif($socle_synthese_affichage=='position')
  {
    $objet = 'Synthèse des pré-positionnements';
    $clef = 'indice';
    $unit = ' / 4';
  }
  else
  {
    $objet = 'Synthèse de la prévision du nombre de points pour le brevet';
    $clef = 'points';
    $unit = ' pts';
  }
  if($make_html)
  {
    $releve_HTML_synthese  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
    $releve_HTML_synthese .= $affichage_direct ? '' : '<h1>'.$titre.'</h1>'.NL;
    $releve_HTML_synthese .= '<hr />'.NL.'<h2>'.html($groupe_nom).' - '.$objet.' (selon l\'objet et le mode de tri choisis)</h2>'.NL;
    $th     = ($socle_synthese_format=='eleve') ? 'Élève' : 'Socle' ;
    $sorter = ($socle_synthese_format=='eleve') ? ' data-sorter="text"' : ' data-sorter="FromData"' ;
    $releve_HTML_table_head = '<thead><tr><th'.$sorter.'>'.$th.'</th>';
    $releve_HTML_table_body = '';
    $releve_HTML_table_foot = '';
  }
  if($make_pdf)
  {
    // Appel de la classe et redéfinition de qqs variables supplémentaires pour la mise en page PDF
    // On définit l'orientation la plus adaptée
    $orientation_auto = ( ( ($eleve_nb>$composante_nb) && ($socle_synthese_format=='eleve') ) || ( ($composante_nb>$eleve_nb) && ($socle_synthese_format=='composante') ) ) ? 'portrait' : 'landscape' ;
    $releve_PDF_synthese = new PDF_socle2016_synthese( $make_officiel , $orientation_auto , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende );
    $releve_PDF_synthese->initialiser( $socle_synthese_format , $eleve_nb , $composante_nb , $socle_synthese_affichage );
    $releve_PDF_synthese->entete( $titre , $groupe_nom , $objet , $socle_synthese_format );
    $releve_PDF_synthese->ligne_tete_cellule_debut();
  }
  if($socle_synthese_format=='eleve')
  {
    // Pour chaque domaine / composante...
    foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
    {
      foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
      {
        if($tab_contenu_presence['composante'][$socle_composante_id])
        {
          $txt_abrev_domaine    = 'Domaine '.$socle_domaine_id;
          $txt_abrev_composante = ($socle_composante_id%10) ? ' - Composante '.($socle_composante_id%10) : '' ;
          $txt_abrev = $txt_abrev_domaine.$txt_abrev_composante;
          if($make_html) { $releve_HTML_table_head .= '<th data-sorter="FromData" data-empty="bottom" title="'.html(html($socle_domaine_id.' '.$socle_domaine_nom)).'<br />'.html(html($socle_composante_nom)).'"><img alt="'.html($txt_abrev).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;item='.urlencode($txt_abrev).'" /></th>'; } // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
          if($make_pdf)  { $releve_PDF_synthese->ligne_tete_cellule_corps( $txt_abrev ); }
        }
      }
    }
    if($socle_synthese_affichage=='points')
    {
      $txt_full  = 'Nombre de points pour le brevet (sur 400)';
      $txt_abrev = 'Nombre de points (sur 400)';
      if($make_html) { $releve_HTML_table_head .= '<th title="'.$txt_full.'"><img alt="'.html($txt_abrev).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;item='.urlencode($txt_abrev).'" /></th>'; }
      if($make_pdf)  { $releve_PDF_synthese->ligne_tete_cellule_corps( $txt_abrev , TRUE /*is_bold*/ ); }
    }
    if($make_html)
    {
      $checkbox_vide = ($affichage_checkbox) ? '<th data-sorter="false" class="nu">&nbsp;</th>' : '' ;
      $releve_HTML_table_head .= $checkbox_vide;
    }
  }
  else
  {
    // Pour chaque élève...
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
    {
      if($tab_contenu_presence['eleve'][$eleve_id])
      {
        extract($tab_eleve); // $eleve_INE $eleve_ID_BE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
        if($make_html) { $releve_HTML_table_head .= '<th data-sorter="FromData" data-empty="bottom"><img alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'&amp;size=9" /></th>'; }
        if($make_pdf)  { $releve_PDF_synthese->ligne_tete_cellule_corps( $eleve_nom.' '.$eleve_prenom ); }
      }
    }
  }
  if($make_html) { $releve_HTML_table_head .= '</tr></thead>'.NL; }
  if($make_pdf)  { $releve_PDF_synthese->ligne_retour(0); }
  // lignes suivantes
  if($socle_synthese_format=='eleve')
  {
    // Pour chaque élève...
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
    {
      if($tab_contenu_presence['eleve'][$eleve_id])
      {
        extract($tab_eleve); // $eleve_INE $eleve_ID_BE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
        if($make_html) { $releve_HTML_table_body .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>'; }
        if($make_pdf)  { $releve_PDF_synthese->ligne_corps_cellule_debut( $eleve_nom.' '.$eleve_prenom ); }
        // Pour chaque domaine / composante...
        foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
        {
          foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
          {
            if($tab_contenu_presence['composante'][$socle_composante_id])
            {
              $tab_bilan = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id];
              if($make_html) { $releve_HTML_table_body .= Html::td_maitrise( $tab_bilan['indice'] , $tab_bilan[$clef] , $tableau_tri_maitrise_mode , $unit /*pourcent*/ , FALSE /*all_columns*/ ); }
              if($make_pdf)  { $releve_PDF_synthese->afficher_degre_maitrise( $tab_bilan['indice'] , $tab_bilan[$clef] , $unit /*pourcent*/ , FALSE /*all_columns*/ ); }
            }
          }
        }
        if($socle_synthese_affichage=='points')
        {
          $points = $tab_bilan_eleve_composante[$eleve_id]['total_dnb'];
          if($make_html) { $releve_HTML_table_body .= '<th class="hc">'.$points.'</th>'; }
          if($make_pdf)  { $releve_PDF_synthese->cellule_total_points( $points ); }
        }
        if($make_html)
        {
          $col_checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>' : '' ;
          $releve_HTML_table_body .= $col_checkbox.'</tr>'.NL;
        }
        if($make_pdf) { $releve_PDF_synthese->ligne_retour($eleve_id); }
      }
    }
  }
  else
  {
    // Pour chaque domaine / composante...
    foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
    {
      foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
      {
        if($tab_contenu_presence['composante'][$socle_composante_id])
        {
          if($make_html) { $releve_HTML_table_body .= '<tr><td data-sort="'.$socle_composante_id.$socle_domaine_id.'"><b>'.html($socle_domaine_id.' '.$socle_domaine_nom).'</b><br />'.html($socle_composante_nom).'</td>'; }
          if($make_pdf)  { $releve_PDF_synthese->ligne_corps_cellule_debut( $socle_domaine_id.' '.$socle_domaine_nom , $socle_composante_nom ); }
          // Pour chaque élève...
          foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
          {
            if($tab_contenu_presence['eleve'][$eleve_id])
            {
              $tab_bilan = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id];
              if($make_html) { $releve_HTML_table_body .= Html::td_maitrise( $tab_bilan['indice'] , $tab_bilan[$clef] , $tableau_tri_maitrise_mode , $unit /*pourcent*/ , FALSE /*all_columns*/ ); }
              if($make_pdf)  { $releve_PDF_synthese->afficher_degre_maitrise( $tab_bilan['indice'] , $tab_bilan[$clef] , $unit /*pourcent*/ , FALSE /*all_columns*/ ); }
            }
          }
          if($make_html) { $releve_HTML_table_body .= '</tr>'.NL; }
          if($make_pdf) { $releve_PDF_synthese->ligne_retour($socle_composante_id); }
        }
      }
    }
    if($socle_synthese_affichage=='points')
    {
      $txt_full  = 'Nombre de points pour le brevet (sur 400)';
      if($make_html) { $releve_HTML_table_body .= '<tr><th>'.$txt_full.'</th>'; }
      if($make_pdf)  { $releve_PDF_synthese->ligne_corps_cellule_debut( $txt_full , NULL /*contenu2*/ , TRUE /*is_bold*/ ); }
      foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
      {
        if($tab_contenu_presence['eleve'][$eleve_id])
        {
          $points = $tab_bilan_eleve_composante[$eleve_id]['total_dnb'];
          if($make_html) { $releve_HTML_table_body .= '<th class="hc">'.$points.'</th>'; }
          if($make_pdf)  { $releve_PDF_synthese->cellule_total_points( $points ); }
        }
      }
      if($make_html) { $releve_HTML_table_body .= '</tr>'.NL; }
      if($make_pdf) { $releve_PDF_synthese->ligne_retour(1); }
    }
  }
  if($make_html) { $releve_HTML_table_body = '<tbody>'.NL.$releve_HTML_table_body.'</tbody>'.NL; }
  // dernière ligne
  if( ($socle_synthese_format=='composante') && $affichage_checkbox )
  {
    if($make_html) { $releve_HTML_table_foot .= '<tfoot>'.NL.'<tr><th class="nu">&nbsp;</th>'; }
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
    {
      if($tab_contenu_presence['eleve'][$eleve_id])
      {
        if($make_html) { $releve_HTML_table_foot .= '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>'; }
      }
    }
    if($make_html) { $releve_HTML_table_foot .= '</tr>'.'</tfoot>'.NL; }
  }
  // assemblage pour la sortie HTML
  if($make_html)
  {
    $releve_HTML_synthese .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">'.NL : '' ;
    $releve_HTML_synthese .= '<table id="table_s" class="bilan_synthese vsort">'.NL.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>'.NL;
  }
  // Légende
  if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
  {
    $tab_legende = array(
      'degre_maitrise' => TRUE ,
      'socle_points'   => ($socle_synthese_affichage=='points') ,
    );
    if($make_html) { $releve_HTML_synthese .= Html::legende($tab_legende); }
    if($make_pdf)  { $releve_PDF_synthese->legende( $socle_synthese_affichage ); }
  }
  $script = $affichage_direct ? '$("#table_s").tablesorter();' : 'function tri(){$("#table_s").tablesorter();}' ;
  if($make_html)
  {
    $releve_HTML_synthese .= ($affichage_checkbox) ? HtmlForm::afficher_synthese_exploitation('eleves').'</form>'.NL : '';
    $releve_HTML_synthese .= '<script type="text/javascript">'.$script.'</script>'.NL;
  }
  // On enregistre les sorties HTML et PDF
  if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.html' , $releve_HTML_synthese ); }
  if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.pdf'  , $releve_PDF_synthese  ); }
}
?>