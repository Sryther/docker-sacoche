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
 * [./_inc/code_livret_***.php]
 */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Chemin d'enregistrement

$fichier_nom = Clean::fichier('livret_'.$PAGE_REF.'_'.$JOINTURE_PERIODE.'_'.$groupe_nom.'_').FileSystem::generer_fin_nom_fichier__date_et_alea();

// Initialisation de tableaux

$tab_saisie_initialisation = array( 'saisie_id'=>0 , 'prof_id'=>NULL , 'saisie_valeur'=>NULL , 'saisie_origine'=>NULL , 'listing_profs'=>NULL , 'acquis_detail'=>NULL );
$tab_parent_lecture        = array( 'resp1'=>NULL , 'resp2'=>NULL , 'resp3'=>NULL , 'resp4'=>NULL );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve_infos      = array();  // [eleve_id] => array(eleve_INE,eleve_nom,eleve_prenom,date_naissance)

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array(
    'eleve_nom'      => $_SESSION['USER_NOM'],
    'eleve_prenom'   => $_SESSION['USER_PRENOM'],
    'eleve_genre'    => $_SESSION['USER_GENRE'],
    'date_naissance' => $_SESSION['USER_NAISSANCE_DATE'],
    'eleve_INE'      => NULL,
  );
}
elseif(empty($is_appreciation_groupe))
{
  $eleves_ordre = ($groupe_type=='Classes') ? 'alpha' : $eleves_ordre ;
  $tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve , $eleves_ordre );
  if(!is_array($tab_eleve_infos))
  {
    Json::end( FALSE , 'Aucun élève trouvé correspondant aux identifiants transmis !' );
  }
}
else
{
  $tab_eleve_infos[0] = array(
    'eleve_nom'      => '',
    'eleve_prenom'   => '',
    'eleve_genre'    => 'I',
    'date_naissance' => NULL,
    'eleve_INE'      => NULL,
  );
}
$eleve_nb = count( $tab_eleve_infos , COUNT_NORMAL );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des rubriques et des profs ayant accès aux différentes rubriques, et de leur usage
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubriques = array( 'eval' => array() );
$tab_id_rubrique = array();
$tab_join_rubrique_profs = array();
$tab_join_eval_eleve_profs = array();
$tab_used_eval_eleve_rubrique = array();

// Suivi des acquis scolaires (évaluations)

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucune matiere du livret n\'est associée aux référentiels ! Il faut configurer un minimum le livret avant son édition...' );
}
foreach($DB_TAB as $DB_ROW)
{
  if( ($make_action!='examiner') || isset($tab_exam_rubrique['eval'][$DB_ROW['livret_rubrique_id']]) )
  {
    $tab_rubriques['eval'][$DB_ROW['livret_rubrique_id']] = array(
      'partie'       => $DB_ROW['rubrique'],
      'sous_partie'  => $DB_ROW['sous_rubrique'],
      'livret_code'  => $DB_ROW['rubrique_id_livret'],
      'elements'     => $DB_ROW['rubrique_id_elements'],
      'appreciation' => $DB_ROW['rubrique_id_appreciation'],
      'position'     => $DB_ROW['rubrique_id_position'],
    );
  }
}
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  foreach($tab_rubriques['eval'] as $rubrique_id => $tab_rubrique)
  {
    if(isset($tab_saisie[$eleve_id]['eval'][$rubrique_id]))
    {
      $tab_used_eval_eleve_rubrique[0][$rubrique_id] = TRUE;
      if( ( isset($tab_saisie[$eleve_id]['eval'][$rubrique_id]['elements']) ) || isset($tab_saisie[$eleve_id]['eval'][$rubrique_id]['position']) )
      {
        $tab_id_rubrique[$eleve_id]['elements'    ][$tab_rubriques['eval'][$rubrique_id]['elements'    ]][$rubrique_id] = $rubrique_id;
        $tab_id_rubrique[$eleve_id]['appreciation'][$tab_rubriques['eval'][$rubrique_id]['appreciation']][$rubrique_id] = $rubrique_id;
        $tab_id_rubrique[$eleve_id]['position'    ][$tab_rubriques['eval'][$rubrique_id]['position'    ]][$rubrique_id] = $rubrique_id;
        $tab_used_eval_eleve_rubrique[$eleve_id][$rubrique_id] = TRUE;
      }
    }
  }
}

// EPI

if( $PAGE_EPI && ( ($make_action!='examiner') || isset($tab_exam_rubrique['epi']) ) )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi( $classe_id , $PAGE_REF );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_rubriques['epi'][$DB_ROW['livret_epi_id']] = array(
      'titre'        => $DB_ROW['livret_epi_titre'] ,
      'theme_code'   => $DB_ROW['livret_epi_theme_code'] ,
      'theme_nom'    => $DB_ROW['livret_epi_theme_nom'] ,
      'mat_prof_id'  => $DB_ROW['matiere_prof_id'] ,
      'mat_prof_txt' => array() ,
    );
    $tab_id  = explode(' '   ,$DB_ROW['matiere_prof_id']);
    $tab_txt = explode('§BR§',$DB_ROW['matiere_prof_texte']);
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      list($matiere_nom,) = explode(' - ',$tab_txt[$key],2);
      $prof_nom = isset($tab_profs[$user_id]) ? $tab_profs[$user_id] : $tab_profs_autres[$user_id] ;
      $tab_join_rubrique_profs['epi'][$DB_ROW['livret_epi_id']][$user_id] = $user_id;
      $tab_rubriques['epi'][$DB_ROW['livret_epi_id']]['mat_prof_txt'][] = $matiere_nom.' '.$prof_nom;
    }
  }
}

// AP

if( $PAGE_AP && ( ($make_action!='examiner') || isset($tab_exam_rubrique['ap']) ) )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_ap( $classe_id , $PAGE_REF );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_rubriques['ap'][$DB_ROW['livret_ap_id']] = array(
      'titre'        => $DB_ROW['livret_ap_titre'] ,
      'mat_prof_id'  => $DB_ROW['matiere_prof_id'] ,
      'mat_prof_txt' => array() ,
    );
    $tab_id  = explode(' '   ,$DB_ROW['matiere_prof_id']);
    $tab_txt = explode('§BR§',$DB_ROW['matiere_prof_texte']);
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      list($matiere_nom,) = explode(' - ',$tab_txt[$key],2);
      $prof_nom = isset($tab_profs[$user_id]) ? $tab_profs[$user_id] : $tab_profs_autres[$user_id] ;
      $tab_join_rubrique_profs['ap'][$DB_ROW['livret_ap_id']][$user_id] = $user_id;
      $tab_rubriques['ap'][$DB_ROW['livret_ap_id']]['mat_prof_txt'][] = $matiere_nom.' '.$prof_nom;
    }
  }
}

// Parcours

if( $PAGE_PARCOURS && ( ($make_action!='examiner') || isset($tab_exam_rubrique['parcours']) ) )
{
  $tab_parcours_code = explode(',',$PAGE_PARCOURS);
  foreach($tab_parcours_code as $parcours_code)
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours( $parcours_code ,  $classe_id , $PAGE_REF );
    // 1 parcours de chaque type au maximum par classe
    if(!empty($DB_TAB))
    {
      $DB_ROW = $DB_TAB[0];
      $projet = isset($tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']) ? $tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']['saisie_valeur'] : NULL ;
      $tab_rubriques['parcours'][$DB_ROW['livret_parcours_id']] = array(
        'type_code' => $parcours_code ,
        'type_nom'  => $DB_ROW['livret_parcours_type_nom'] ,
        'projet'    => $projet ,
        'prof_id'   => $DB_ROW['prof_id'] ,
        'prof_txt'  => array() ,
      );
      $tab_id  = explode(' ',$DB_ROW['prof_id']);
      foreach($tab_id as $key => $user_id)
      {
        $prof_nom = isset($tab_profs[$user_id]) ? $tab_profs[$user_id] : $tab_profs_autres[$user_id] ;
        $tab_join_rubrique_profs['parcours'][$DB_ROW['livret_parcours_id']][$user_id] = $user_id;
        $tab_rubriques['parcours'][$DB_ROW['livret_parcours_id']]['prof_txt'][] = $prof_nom;
      }
    }
  }
}

// Modalités d'accompagnement

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_modaccomp( $liste_eleve );
foreach($DB_TAB as $DB_ROW)
{
  $tab_rubriques['modaccomp'][$DB_ROW['user_id']][$DB_ROW['livret_modaccomp_code']] = $DB_ROW['livret_modaccomp_code'].' ('.Clean::lower($DB_ROW['livret_modaccomp_nom']).')' ;
  if($DB_ROW['info_complement'])
  {
    $tab_rubriques['modaccomp_ppre'][$DB_ROW['user_id']] = $DB_ROW['info_complement'] ;
  }
}

// Communication avec la famille

if( $PAGE_VIE_SCOLAIRE )
{
  $is_cpe = ($_SESSION['USER_PROFIL_SIGLE']=='EDU') ? TRUE : FALSE ;
  $is_dir = ($_SESSION['USER_PROFIL_SIGLE']=='DIR') ? TRUE : FALSE ;
  $is_pp  = ( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'],$classe_id) ) ? $_SESSION['USER_ID'] : 0 ;
  $is_acces_viesco = $is_cpe || $is_dir || $is_pp ;
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
// Compter le nombre de lignes à afficher par élève et par rubrique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_caract_max_par_ligne   = 150;
$nb_caract_max_par_colonne = 50;

if($make_pdf)
{
  $tab_nb_lignes = array();
  $tab_nb_lignes_eleve_eval = array();
  $tab_nb_lignes_eleve_eval_total = array();
  $tab_nb_lignes_eleve_autre = array();
  $tab_nb_lignes_eleve_autre_total = array();
  $tab_deja_affiche = array();
  $nb_lignes_marge     = 1;
  $nb_lignes_intitule  = 1.5;
  $nb_lignes_eval_tete = 2;
  $nb_lignes_pos_legende = in_array($PAGE_COLONNE,array('moyenne','pourcentage')) ? 0 : 1 ;
  $app_rubrique_longueur = $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'];
  $app_generale_longueur = $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'];

  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    // AQUIS SCOLAIRES
    foreach($tab_rubriques['eval'] as $livret_rubrique_id => $tab_rubrique)
    {
      if(isset($tab_used_eval_eleve_rubrique[$eleve_id][$livret_rubrique_id]))
      {
        // récup éléments travaillés
        $id_rubrique_elements = $livret_rubrique_id ; // On force une ligne par sous-rubrique, donc pas $tab_rubriques['eval'][$livret_rubrique_id]['elements'];
        $elements_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_elements]['elements']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_elements]['elements'] : $tab_saisie_initialisation ;
        // récup appréciation
        $id_rubrique_appreciation = $tab_rubriques['eval'][$livret_rubrique_id]['appreciation'];
        $appreciation_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation'] : $tab_saisie_initialisation ;
        $tab_profs_appreciation = is_null($appreciation_info['listing_profs']) ? array() : explode(',',$appreciation_info['listing_profs']) ;
        // récup positionnement
        $id_rubrique_position = $tab_rubriques['eval'][$livret_rubrique_id]['position'];
        $position_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_position]['position']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
        $tab_profs_position = is_null($position_info['listing_profs']) ? array() : explode(',',$position_info['listing_profs']) ;
        // Domaine d’enseignement
        $id_premiere_sous_rubrique = $tab_rubriques['eval'][$livret_rubrique_id]['appreciation'];
        $tab_prof_domaine = !empty($tab_profs_appreciation) ? $tab_profs_appreciation : $tab_profs_position ;
        if($BILAN_TYPE_ETABL=='college')
        {
          $nb_lignes_domaine = max( 3 , 1+count($tab_prof_domaine) ); // 3 = forfait pour matière + nom prof(s)
        }
        else
        {
          $nombre_sous_rubriques = isset($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) ? count($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) : 0 ;
          // forfait pour une rubrique dégressif en fonction du nb de sous-rubriques
          if( $nombre_sous_rubriques == 1 )
          {
            $nb_lignes_domaine = 3; 
          }
          else if( $nombre_sous_rubriques == 2 )
          {
            $nb_lignes_domaine = 2;
          }
          else
          {
            $nb_lignes_domaine = 1.5;
          }
        }
        // Principaux éléments du programme travaillés durant la période
        $nb_lignes_elements = ($elements_info['saisie_valeur']) ? elements_programme_extraction( $elements_info['saisie_valeur'] , $nb_caract_max_par_colonne , 'nombre_lignes' /*objet_retour*/ ) : 0 ;
        // Acquisitions, progrès et difficultés éventuelles
        $nombre_rubriques_regroupees = isset($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) ? count($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) : 0 ;
        $nb_lignes_appreciation = 0;
        if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$eleve_id][$id_rubrique_appreciation]) )
        {
          if($appreciation_info['saisie_valeur'])
          {
            $appreciation = $appreciation_info['saisie_valeur'];
            $nb_lignes_appreciation += max( 1 , ceil(strlen($appreciation)/$nb_caract_max_par_colonne), min( substr_count($appreciation,"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_colonne ) );
          }
        }
        $tab_deja_affiche[$eleve_id][$id_premiere_sous_rubrique] = TRUE;
        if( ($livret_rubrique_id==$id_premiere_sous_rubrique) || !isset($tab_nb_lignes_eleve_eval[$eleve_id][$id_premiere_sous_rubrique]) )
        {
          $tab_nb_lignes_eleve_eval[$eleve_id][$id_premiere_sous_rubrique] = array( $nb_lignes_domaine , $nb_lignes_elements , $nb_lignes_appreciation );
        }
        else
        {
          $tab_nb_lignes_eleve_eval[$eleve_id][$id_premiere_sous_rubrique][0] += $nb_lignes_domaine;
          $tab_nb_lignes_eleve_eval[$eleve_id][$id_premiere_sous_rubrique][1] += $nb_lignes_elements;
        }
      }
    }
    if(isset($tab_nb_lignes_eleve_eval[$eleve_id]))
    {
      foreach($tab_nb_lignes_eleve_eval[$eleve_id] as $id_premiere_sous_rubrique => $tab_nb)
      {
        $tab_nb_lignes_eleve_eval[$eleve_id][$id_premiere_sous_rubrique] = max($tab_nb[0],$tab_nb[1],$tab_nb[2]);
      }
    }
    else
    {
      // Il arrive que l'on passe par ici... pas trouvé dans quel cas particulier...
      $tab_nb_lignes_eleve_eval[$eleve_id] = array(1);
    }
    $tab_nb_lignes_eleve_eval_total[$eleve_id] = $nb_lignes_marge + $nb_lignes_intitule + $nb_lignes_eval_tete + array_sum($tab_nb_lignes_eleve_eval[$eleve_id]) + $nb_lignes_pos_legende ;
    // EPI
    if( $PAGE_EPI && isset($tab_rubriques['epi']) )
    {
      $tab_nb_lignes_eleve_autre[$eleve_id]['epi'] = $nb_lignes_marge + $nb_lignes_intitule ;
      foreach($tab_rubriques['epi'] as $livret_epi_id => $tab_epi)
      {
        $saisie_classe = isset($tab_saisie[    0    ]['epi'][$livret_epi_id]['appreciation']) ? $tab_saisie[    0    ]['epi'][$livret_epi_id]['appreciation'] : $tab_saisie_initialisation ;
        $saisie_eleve  = isset($tab_saisie[$eleve_id]['epi'][$livret_epi_id]['appreciation']) ? $tab_saisie[$eleve_id]['epi'][$livret_epi_id]['appreciation'] : $tab_saisie_initialisation ;
        if( $saisie_eleve['saisie_valeur'] || $saisie_classe['saisie_valeur'] )
        {
          $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $tab_nb_lignes_eleve_autre[$eleve_id]['epi'] += 2 + $nb_lignes_classe + $nb_lignes_eleve; // [ titre - thème ] + profs + saisies
        }
      }
    }
    // AP
    if( $PAGE_AP && isset($tab_rubriques['ap']) )
    {
      $tab_nb_lignes_eleve_autre[$eleve_id]['ap'] = $nb_lignes_marge + $nb_lignes_intitule ;
      foreach($tab_rubriques['ap'] as $livret_ap_id => $tab_ap)
      {
        $saisie_classe = isset($tab_saisie[    0    ]['ap'][$livret_ap_id]['appreciation']) ? $tab_saisie[    0    ]['ap'][$livret_ap_id]['appreciation'] : $tab_saisie_initialisation ;
        $saisie_eleve  = isset($tab_saisie[$eleve_id]['ap'][$livret_ap_id]['appreciation']) ? $tab_saisie[$eleve_id]['ap'][$livret_ap_id]['appreciation'] : $tab_saisie_initialisation ;
        if( $saisie_eleve['saisie_valeur'] || $saisie_classe['saisie_valeur'] )
        {
          $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $tab_nb_lignes_eleve_autre[$eleve_id]['ap'] += 2 + $nb_lignes_classe + $nb_lignes_eleve; // titre + profs + saisies
        }
      }
    }
    // Parcours
    if( $PAGE_PARCOURS && isset($tab_rubriques['parcours']) )
    {
      $tab_nb_lignes_eleve_autre[$eleve_id]['parcours'] = $nb_lignes_marge + $nb_lignes_intitule ;
      foreach($tab_rubriques['parcours'] as $livret_parcours_id => $tab_parcours)
      {
        $saisie_classe = isset($tab_saisie[    0    ]['parcours'][$livret_parcours_id]['appreciation']) ? $tab_saisie[    0    ]['parcours'][$livret_parcours_id]['appreciation'] : $tab_saisie_initialisation ;
        $saisie_eleve  = isset($tab_saisie[$eleve_id]['parcours'][$livret_parcours_id]['appreciation']) ? $tab_saisie[$eleve_id]['parcours'][$livret_parcours_id]['appreciation'] : $tab_saisie_initialisation ;
        if( $saisie_eleve['saisie_valeur'] || $saisie_classe['saisie_valeur'] )
        {
          $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 0 ;
          $tab_nb_lignes_eleve_autre[$eleve_id]['parcours'] += 1 + $nb_lignes_classe + $nb_lignes_eleve; // type_nom / prof + saisies
        }
      }
    }
    // Modalités d'accompagnement
    if( isset($tab_rubriques['modaccomp'][$eleve_id]) )
    {
      $tab_nb_lignes_eleve_autre[$eleve_id]['modaccomp'] = $nb_lignes_marge + $nb_lignes_intitule ;
      $tab_nb_lignes_eleve_autre[$eleve_id]['modaccomp'] += 1; // modalité
      if(isset($tab_rubriques['modaccomp_ppre'][$eleve_id]))
      {
        $tab_nb_lignes_eleve_autre[$eleve_id]['modaccomp'] += max( ceil(strlen($tab_rubriques['modaccomp_ppre'][$eleve_id])/$nb_caract_max_par_ligne) , min( substr_count($tab_rubriques['modaccomp_ppre'][$eleve_id],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) );
      }
    }
    // Bilan de l’acquisition des connaissances et compétences
    $tab_nb_lignes_eleve_autre[$eleve_id]['bilan'] = $nb_lignes_marge + $nb_lignes_intitule ;
    $tab_nb_lignes_eleve_autre[$eleve_id]['bilan'] += 1; // texte introductif
    $tab_nb_lignes_eleve_autre[$eleve_id]['bilan'] += ($BILAN_TYPE_ETABL=='college') ? 1 : 0 ; // prof principal
    $bilan_info = isset($tab_saisie[$eleve_id]['bilan'][0]['appreciation']) ? $tab_saisie[$eleve_id]['bilan'][0]['appreciation'] : $tab_saisie_initialisation ;
    $nb_lignes = ($bilan_info['saisie_valeur']) ? max( 6 , ceil(strlen($bilan_info['saisie_valeur'])/$nb_caract_max_par_ligne), min( substr_count($bilan_info['saisie_valeur'],"\n") + 1 , $app_generale_longueur / $nb_caract_max_par_ligne ) ) : 6 ; // On prévoit un emplacement par défaut
    $tab_nb_lignes_eleve_autre[$eleve_id]['bilan'] += $nb_lignes;
    // Communication avec la famille
    if( $PAGE_VIE_SCOLAIRE )
    {
      // Collège
      $tab_nb_lignes_eleve_autre[$eleve_id]['viesco'] = $nb_lignes_marge + $nb_lignes_intitule ;
      $tab_nb_lignes_eleve_autre[$eleve_id]['viesco'] += 1; // texte introductif
      $viesco_info = isset($tab_saisie[$eleve_id]['viesco'][0]['appreciation']) ? $tab_saisie[$eleve_id]['viesco'][0]['appreciation'] : $tab_saisie_initialisation ;
      $nb_lignes = ($viesco_info['saisie_valeur']) ? max( 6 , ceil(strlen($viesco_info['saisie_valeur'])/$nb_caract_max_par_ligne), min( substr_count($viesco_info['saisie_valeur'],"\n") + 1 , $app_rubrique_longueur / $nb_caract_max_par_ligne ) ) : 6 ; // On prévoit un emplacement par défaut
      $tab_nb_lignes_eleve_autre[$eleve_id]['viesco'] += $nb_lignes + $affichage_assiduite;
      $tab_nb_lignes_eleve_autre[$eleve_id]['viesco'] += $nb_lignes_marge + 4; // cadre famille
    }
    else
    {
      // 1er degré
      $tab_nb_lignes_eleve_autre[$eleve_id]['viesco'] = $nb_lignes_marge + $nb_lignes_intitule + max( count($tab_profs) , 3 ) + 2;
    }
    $tab_nb_lignes_eleve_autre_total[$eleve_id] = array_sum($tab_nb_lignes_eleve_autre[$eleve_id]);
  }

}

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
// Elaboration du relevé périodique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_graph_data = array();
$tab_deja_affiche = array();

// Préparatifs
if( ($make_html) || ($make_graph) )
{
  $bouton_print_test = (isset($is_bouton_test_impression))                  ? ( ($is_bouton_test_impression) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : ' <button id="simuler_disabled" type="button" class="imprimer" disabled>Pour simuler l\'impression, sélectionner un élève</button>' ) : '' ;
  $bouton_archivage  = (!$make_graph && ($BILAN_TYPE_ETABL=='college'))     ? ' <button id="archiver_imprimer" type="button" class="imprimer">Archiver / Imprimer des données</button>' : '' ;
  $bouton_import_csv = (in_array($make_action,array('modifier','tamponner')) && ($BILAN_TYPE_ETABL=='college')) ? ' <button id="saisir_deport" type="button" class="fichier_export">Saisie déportée</button>' : '' ;
  $info_details      = (!$make_graph && !empty($tab_saisie_avant))          ? 'Cliquer sur <span class="toggle_plus"></span> / <span class="toggle_moins"></span> pour afficher / masquer le détail (<a href="#" id="montrer_details">tout montrer</a>).' : '' ;
  $releve_HTML = (!$make_graph) ? '<div class="ti">'.$info_details.$bouton_archivage.$bouton_print_test.$bouton_import_csv.'</div>'.NL : '<div id="div_graphique_synthese"></div>'.NL ;
  // légende
  if( ($PAGE_COLONNE=='objectif') || ($PAGE_COLONNE=='position') )
  {
    $positionnement_texte = ($PAGE_COLONNE=='objectif') ? 'Objectifs d’apprentissage' : 'Positionnement' ;
    $positionnement_title = array();
    foreach($_SESSION['LIVRET'] as $id => $tab)
    {
      $positionnement_title[] = html($id.' = '.$tab['LEGENDE']);
    }
    $legende_positionnement = $positionnement_texte.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.implode('<br />',$positionnement_title).'" />';
  }
}

if($make_pdf)
{
  $releve_PDF = new PDF_livret_scolaire( TRUE /*make_officiel*/ , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende , !empty($is_test_impression) /*filigrane*/ );
  $releve_PDF->initialiser( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $app_rubrique_longueur , $app_generale_longueur , $tab_saisie_initialisation );
  $tab_archive['user'][0][] = array( '__construct' , array( TRUE /*make_officiel*/ , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , 'oui' /*couleur*/ , $fond , $legende , !empty($is_test_impression) /*filigrane*/ , $tab_archive['session'] ) );
}
// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  extract($tab_eleve);  // $eleve_INE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
  $date_naissance = ($date_naissance) ? To::date_mysql_to_french($date_naissance) : '' ;
  foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
  {
    $is_archive = ( ($numero_tirage==0) && empty($is_test_impression) ) ? TRUE : FALSE ;
    // Si cet élève a été évalué...
    if(isset($tab_saisie[$eleve_id]))
    {
      // Intitulé
      if($make_pdf)
      {
        if($is_archive)
        {
          $tab_archive['user'][$eleve_id]['image_md5'] = array();
          $tab_archive['user'][$eleve_id][] = array( 'initialiser' , array( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $app_rubrique_longueur , $app_generale_longueur , $tab_saisie_initialisation ) );
        }
        $tab_infos_entete = 
          array(
            'tab_menesr_logo'          => $tab_menesr_logo ,
            'tab_etabl_coords'         => $tab_etabl_coords ,
            'tab_etabl_logo'           => $tab_etabl_logo ,
            'tab_bloc_titres'          => $tab_bloc_titres ,
            'tab_adresse'              => $tab_adresse ,
            'tag_date_heure_initiales' => $tag_date_heure_initiales ,
            'eleve_genre'              => $eleve_genre ,
            'date_naissance'           => $date_naissance ,
          ) ;
        $releve_PDF->entete( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $tab_nb_lignes_eleve_eval_total[$eleve_id] );
        if($is_archive)
        {
          if(!empty($tab_etabl_logo))
          {
            // On remplace l'image par son md5
            $image_contenu = $tab_etabl_logo['contenu'];
            $image_md5     = md5($image_contenu);
            $tab_archive['image'][$image_md5] = $image_contenu;
            $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
            $tab_infos_entete['tab_etabl_logo']['contenu'] = $image_md5;
          }
          // Idem pour le logo du Menesr
          $image_contenu = $tab_menesr_logo['contenu'];
          $image_md5     = md5($image_contenu);
          $tab_archive['image'][$image_md5] = $image_contenu;
          $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
          $tab_infos_entete['tab_menesr_logo']['contenu'] = $image_md5;
          $tab_archive['user'][$eleve_id][] = array( 'entete' , array( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $tab_nb_lignes_eleve_eval_total[$eleve_id] ) );
        }
      }

      // AQUIS SCOLAIRES : Suivi des acquis scolaires (évaluations)
      if( isset($tab_saisie[$eleve_id]['eval']) || !$eleve_id )
      {
       if($make_pdf)
        {
          $releve_PDF->bloc_eval( $tab_rubriques['eval'] , $tab_used_eval_eleve_rubrique[$eleve_id] , $tab_id_rubrique[$eleve_id] , $tab_saisie[$eleve_id]['eval'] , $tab_saisie[0]['eval'] , $tab_nb_lignes_eleve_eval[$eleve_id] , $tab_nb_lignes_eleve_autre_total[$eleve_id] , $tab_profs );
          if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_eval' , array( $tab_rubriques['eval'] , $tab_used_eval_eleve_rubrique[$eleve_id] , $tab_id_rubrique[$eleve_id] , $tab_saisie[$eleve_id]['eval'] , $tab_saisie[0]['eval'] , $tab_nb_lignes_eleve_eval[$eleve_id] , $tab_nb_lignes_eleve_autre_total[$eleve_id] , $tab_profs ) ); }
        }
        else
        {
          $temp_HTML = '';
          $avant_td_avant = ($BILAN_TYPE_ETABL=='college') ? '<td class="nu"></td>' : '<td class="nu"></td><td class="nu"></td>' ;
          $avant_colspan  = ( !$eleve_id && !$PAGE_MOYENNE_CLASSE ) ? 2 : ( in_array($PAGE_COLONNE,array('objectif','position')) ? 6 : 7 ) ;
          $avant_td_apres = ( ( $eleve_id || $PAGE_MOYENNE_CLASSE ) && in_array($PAGE_COLONNE,array('objectif','position')) ) ? '<td class="nu"></td>' : '' ;
          // On passe en revue les rubriques...
          foreach($tab_rubriques['eval'] as $livret_rubrique_id => $tab_rubrique)
          {
            // récup éléments travaillés
            $id_rubrique_elements = $livret_rubrique_id ; // On force une ligne par sous-rubrique, donc pas $tab_rubriques['eval'][$livret_rubrique_id]['elements'];
            $elements_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_elements]['elements']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_elements]['elements'] : $tab_saisie_initialisation ;
            // récup appréciation
            $id_rubrique_appreciation = $tab_rubriques['eval'][$livret_rubrique_id]['appreciation'];
            $appreciation_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation'] : $tab_saisie_initialisation ;
            $tab_profs_appreciation = is_null($appreciation_info['listing_profs']) ? array() : explode(',',$appreciation_info['listing_profs']) ;
            // récup positionnement
            $id_rubrique_position = $tab_rubriques['eval'][$livret_rubrique_id]['position'];
            $position_info = isset($tab_saisie[$eleve_id]['eval'][$id_rubrique_position]['position']) ? $tab_saisie[$eleve_id]['eval'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
            $tab_profs_position = is_null($position_info['listing_profs']) ? array() : explode(',',$position_info['listing_profs']) ;
            // test accès à la rubrique
            $is_acces_prof = ( ($BILAN_TYPE_ETABL=='ecole') || in_array($make_action,array('consulter','tamponner','imprimer','examiner')) || in_array($_SESSION['USER_ID'],$tab_profs_position) ) ? TRUE : FALSE ;
            $is_rubrique_used   = isset($tab_used_eval_eleve_rubrique[$eleve_id][$livret_rubrique_id]) ? TRUE : FALSE ;
            if( $is_rubrique_used && $is_acces_prof )
            {
              // Interface graphique
              if($make_graph)
              {
                $rubrique_nom = ( ($BILAN_TYPE_ETABL=='college') || !in_array(substr($tab_rubriques['eval'][$livret_rubrique_id]['livret_code'],0,3),array('FRA','MAT')) ) ? $tab_rubrique['partie'] : $tab_rubrique['partie'].' - '.$tab_rubrique['sous_partie'];
                $tab_graph_data['categories'][$id_rubrique_position] = '"'.addcslashes($rubrique_nom,'"').'"';
                if($eleve_id) // Si appréciation sur le groupe alors pas de courbe élève
                {
                  $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
                  $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage ) ;
                  $tab_graph_data['series_data_MoyEleve'][$id_rubrique_position] = !is_null($position_info['saisie_valeur']) ? $note : 'null' ;
                }
                if($PAGE_MOYENNE_CLASSE)
                {
                  $position_info = isset($tab_saisie[0]['eval'][$id_rubrique_position]['position']) ? $tab_saisie[0]['eval'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
                  $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
                  $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage ) ;
                  $tab_graph_data['series_data_MoyClasse'][$id_rubrique_position] = !is_null($position_info['saisie_valeur']) ? $note : 'null' ;
                }
              }
              // Interface HTML
              $id_premiere_sous_rubrique = $tab_rubriques['eval'][$livret_rubrique_id]['appreciation'];
              if($make_html)
              {
                $tab_temp_HTML = array( 'avant'=>'' , 'domaine'=>'' , 'elements'=>'' , 'appreciation'=>'' , 'position'=>'' );
                // Info saisies périodes antérieures
                if( isset($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation']) || isset($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_position]['position']) )
                {
                  $tab_avant_info = array();
                  if(isset($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation']))
                  {
                    foreach($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_appreciation]['appreciation'] as $jointure_periode => $saisie_valeur)
                    {
                      $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
                    }
                  }
                  if(isset($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_position]['position']))
                  {
                    foreach($tab_saisie_avant[$eleve_id]['eval'][$id_rubrique_position]['position'] as $jointure_periode => $saisie_valeur)
                    {
                      $tab_avant_info[$jointure_periode]['position'] = $saisie_valeur;
                    }
                  }
                  ksort($tab_avant_info);
                  $tab_periode_liens  = array();
                  $tab_periode_textes = array();
                  foreach($tab_avant_info as $jointure_periode => $tab)
                  {
                    $note = '-';
                    $appreciation = '-';
                    if(isset($tab['position']))
                    {
                      $pourcentage = !is_null($tab['position']) ? $tab['position'] : FALSE ;
                      $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ;
                    }
                    if(isset($tab['appreciation']))
                    {
                      $appreciation = html($tab['appreciation']);
                    }
                    $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_eval_'.$livret_rubrique_id.'_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
                    $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_eval_'.$livret_rubrique_id.'_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.$note.' | '.$appreciation.'</div>';
                  }
                  // Il est trop compliqué de faire apparaitre les infos sur les saisies antérieures au 1D à cause des rowspan sur les domaines avec plusieurs sous-domaines
                  if($BILAN_TYPE_ETABL=='college')
                  {
                    $tab_temp_HTML['avant'] .= '<tr>'.$avant_td_avant.'<td colspan="'.$avant_colspan.'" class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</td>'.$avant_td_apres.'</tr>'.NL;
                  }
                }
                // Domaine d’enseignement
                $details = ( $eleve_id && $elements_info['acquis_detail'] ) ? '<div><a href="#" class="voir_detail" data-id="'.$id_rubrique_elements.'">[ détail travaillé ]</a></div><div id="detail_'.$id_rubrique_elements.'" class="hide">'.$elements_info['acquis_detail'].'</div>' : '' ;
                if($BILAN_TYPE_ETABL=='college')
                {
                  // Pour les profs indiqués, on prend ceux qui ont renseigné l'appréciation, ou à défaut ceux qui ont participé à l'évaluation
                  $tab_prof_domaine = !empty($tab_profs_appreciation) ? $tab_profs_appreciation : $tab_profs_position ;
                  $listing_prof_domaine = '';
                  if(!empty($tab_prof_domaine))
                  {
                    $is_saisie_multiple = ( !empty($tab_profs_appreciation) && (count($tab_profs_appreciation)>1) ) ? TRUE : FALSE ;
                    foreach($tab_prof_domaine as $key => $prof_id)
                    {
                      $tab_prof_domaine[$key] = '<span id="jointure_'.$appreciation_info['saisie_id'].'_'.$prof_id.'">'.html($tab_profs[$prof_id]).'</span>';
                      if( $is_saisie_multiple && ($prof_id==$_SESSION['USER_ID']) )
                      {
                        $tab_prof_domaine[$key] .= ' <button type="button" class="supprimer" title="Supprimer mon association à cette rubrique (appréciation saisie par erreur).">&nbsp;</button>';
                      }
                    }
                  }
                  $listing_prof_domaine = implode('<br />',$tab_prof_domaine);
                  $nombre_sous_rubriques = 1;
                  $tab_temp_HTML['domaine'] .= '<td id="eval_'.$id_rubrique_appreciation.'_saisiejointure"><b>'.html($tab_rubrique['partie']).'</b><div class="notnow" data-id="'.$appreciation_info['saisie_id'].'">'.$listing_prof_domaine.'</div>'.$details.'</td>';
                }
                else
                {
                  $nombre_sous_rubriques = isset($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) ? count($tab_id_rubrique[$eleve_id]['appreciation'][$id_premiere_sous_rubrique]) : 0 ;
                  if( $nombre_sous_rubriques == 1 )
                  {
                    $tab_temp_HTML['domaine'] .= ($tab_rubrique['sous_partie']) ? '<td><b>'.html($tab_rubrique['partie']).'</b></td><td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' : '<td colspan="2" class="hc"><b>'.html($tab_rubrique['partie']).'</b>'.$details.'</td>' ;
                  }
                  else
                  {
                    $nb_rowspan = empty($tab_temp_HTML['avant']) ? $nombre_sous_rubriques : 2*$nombre_sous_rubriques - 1 ;
                    $rowspan = ($nombre_sous_rubriques>1) ? ' rowspan="'.$nb_rowspan.'"' : '' ;
                    $tab_temp_HTML['domaine'] .= isset($tab_deja_affiche[$eleve_id][$id_premiere_sous_rubrique]) ? '<td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' : '<td'.$rowspan.'><b>'.html($tab_rubrique['partie']).'</b></td><td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' ;
                  }
                }
                // Principaux éléments du programme travaillés durant la période
                if($elements_info['saisie_valeur'])
                {
                  $elements = elements_programme_extraction( $elements_info['saisie_valeur'] , $nb_caract_max_par_colonne , 'html' /*objet_retour*/ );
                  $origine = ($elements_info['saisie_origine']=='calcul') ? 'Généré automatiquement' : 'Validé par '.html($tab_profs[$elements_info['prof_id']]) ;
                  $actions = ($make_action=='modifier') ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
                  $actions.= ( $eleve_id && ($make_action=='modifier') && ($elements_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
                  $actions.= ( !$eleve_id && ($make_action=='modifier') ) ? '<div class="danger">Une action sur le regroupement l\'impose à tous les élèves.</div>' : '' ;
                }
                else
                {
                  if($eleve_id)
                  {
                    $elements = '<div class="danger">Absence de saisie !</div>' ;
                  }
                  else
                  {
                    $elements = ($make_action=='modifier') ? '<div class="danger">Saisir des éléments pour le regroupement l\'impose à tous les élèves et annule leur récolte automatisée pour chacun.</div>' : '<div class="notnow">Pas de saisie imposée à tous les élèves du regroupement.</div>' ;
                  }
                  $origine = ($elements_info['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$elements_info['prof_id']]) : '' ;
                  if( $eleve_id || !$is_sous_groupe )
                  {
                    $actions = ($make_action=='modifier') ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
                  }
                  else
                  {
                    $actions = ($make_action=='modifier') ? ' <button type="button" disabled>Fonctionnalité pas encore disponible pour les groupes, désolé !</button>' : '' ;
                  }
                  $actions.= ( $eleve_id && ($make_action=='modifier') && ($elements_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
                }
                $tab_temp_HTML['elements'] .= '<td id="eval_'.$livret_rubrique_id.'_elements"><div class="elements">'.$elements.'</div><div class="notnow" data-id="'.$elements_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div></td>';
                // Acquisitions, progrès et difficultés éventuelles
                $nombre_rubriques_regroupees = isset($tab_id_rubrique[$eleve_id]['appreciation'][$id_rubrique_appreciation]) ? count($tab_id_rubrique[$eleve_id]['appreciation'][$id_rubrique_appreciation]) : 0 ;
                if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$eleve_id][$id_rubrique_appreciation]) )
                {
                  if($appreciation_info['saisie_valeur'])
                  {
                    $appreciation = html($appreciation_info['saisie_valeur']);
                    $origine = ($appreciation_info['saisie_origine']=='bulletin') ? 'Reporté du bulletin' : 'Validé par '.html($tab_profs[$appreciation_info['prof_id']]) ;
                    $actions = ($make_action=='modifier') ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
                    $actions.= ( ($make_action=='modifier') && ($appreciation_info['saisie_origine']=='saisie') && ( ($BILAN_TYPE_ETABL=='college') && ($PAGE_RUBRIQUE_JOIN=='matiere') ) ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
                    if( ($make_action!='modifier') && in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && ($appreciation_info['prof_id']!=$_SESSION['USER_ID']) )
                    {
                      $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
                      if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
                    }
                  }
                  else
                  {
                    $appreciation = '<div class="danger">Absence de saisie !</div>' ;
                    $origine = ($appreciation_info['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$appreciation_info['prof_id']]) : '' ;
                    $actions = ($make_action=='modifier') ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
                    $actions.= ( ($make_action=='modifier') && ($appreciation_info['saisie_origine']=='saisie') && ( ($BILAN_TYPE_ETABL=='college') && ($PAGE_RUBRIQUE_JOIN=='matiere') ) ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
                  }
                  $rowspan = ($nombre_rubriques_regroupees>1) ? ' rowspan="'.$nombre_rubriques_regroupees.'"' : '' ;
                  $tab_temp_HTML['appreciation'] .= '<td'.$rowspan.' id="eval_'.$id_rubrique_appreciation.'_appreciation_'.$appreciation_info['prof_id'].'"><div class="appreciation">'.$appreciation.'</div><div class="notnow" data-id="'.$appreciation_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div></td>';
                }
                // Positionnement
                if( $eleve_id || $PAGE_MOYENNE_CLASSE )
                {
                  $nombre_rubriques_regroupees = isset($tab_id_rubrique[$eleve_id]['position'][$id_rubrique_position]) ? count($tab_id_rubrique[$eleve_id]['position'][$id_rubrique_position]) : 0 ;
                  if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$eleve_id][$id_rubrique_position]) )
                  {
                    if(!is_null($position_info['saisie_valeur']))
                    {
                      $pourcentage = $position_info['saisie_valeur'];
                      $origine = ($position_info['saisie_origine']=='bulletin') ? 'Reporté du bulletin' : ( ($position_info['saisie_origine']=='calcul') ? 'Calculé automatiquement' : 'Saisi par '.html($tab_profs[$position_info['prof_id']]) ) ;
                      $actions = ( $eleve_id && ($make_action=='modifier') ) ? ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button> <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>' : '' ;
                      $actions.= ( ($make_action=='modifier') && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
                    }
                    else
                    {
                      $pourcentage = FALSE ;
                      $origine = ($position_info['saisie_origine']=='bulletin') ? 'Reporté du bulletin' : ( ($position_info['saisie_origine']=='saisie') ? 'Supprimé par '.html($tab_profs[$position_info['prof_id']]) : '' ) ;
                      $actions = ( $eleve_id && ($make_action=='modifier') ) ? ' <button type="button" class="ajouter" title="Ajouter le positionnement">&nbsp;</button>' : '' ;
                      $actions.= ( ($make_action=='modifier') && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
                    }
                    $rowspan = ($nombre_rubriques_regroupees>1) ? ' rowspan="'.$nombre_rubriques_regroupees.'"' : '' ;
                    if( in_array($PAGE_COLONNE,array('objectif','position')) )
                    {
                      $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
                      $origine .= ( $origine && ($position_info['saisie_origine']=='calcul') ) ? ' : '.$pourcentage.' %' : '' ;
                      foreach($_SESSION['LIVRET'] as $id => $tab)
                      {
                        $texte = ($id==$indice) ? '<b>X</b>' : '' ;
                        $tab_temp_HTML['position'] .= '<td'.$rowspan.' id="eval_'.$livret_rubrique_id.'_position_'.$id.'" class="pos'.$id.'">'.$texte.'</td>';
                      }
                      $tab_temp_HTML['position'] .= '<td'.$rowspan.' id="eval_'.$livret_rubrique_id.'_position_'.$PAGE_COLONNE.'" class="nu"><div class="notnow" data-id="'.$position_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div><i>'.$indice.'</i></td>';
                    }
                    else if( in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
                    {
                      $note = ($position_info['saisie_valeur']!==NULL) ? ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage.'&nbsp;%' ) : '-' ;
                      $moyenne_classe = '';
                      if( ($make_action=='consulter') && $eleve_id && $PAGE_MOYENNE_CLASSE )
                      {
                        $position_info = isset($tab_saisie[0]['eval'][$id_rubrique_position]['position']) ? $tab_saisie[0]['eval'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
                        $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
                        $note_moyenne = ($position_info['saisie_valeur']!==NULL) ? ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage.'&nbsp;%' ) : '-' ;
                        $moyenne_classe = ' <span class="notnow">(classe '.$note_moyenne.')</span>';
                      }
                      $tab_temp_HTML['position'] .= '<td colspan="5"'.$rowspan.' id="eval_'.$livret_rubrique_id.'_position_'.$PAGE_COLONNE.'"><div class="position">'.$note.$moyenne_classe.'</div><div class="notnow" data-id="'.$position_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div></td>';
                    }
                  }
                }
                $temp_HTML .= '<tr>'.implode('',$tab_temp_HTML).'</tr>'.NL;
                $tab_deja_affiche[$eleve_id][$id_premiere_sous_rubrique] = TRUE;
              }
              // Examen de présence des appréciations intermédiaires et des positionnements
              if( ($make_action=='examiner') && is_null($position_info['saisie_valeur']) )
              {
                $tab_resultat_examen[$tab_rubrique['partie']][] = 'Absence de positionnement pour '.html($eleve_nom.' '.$eleve_prenom);
              }
              if( ($make_action=='examiner') && is_null($appreciation_info['saisie_valeur']) )
              {
                $tab_resultat_examen[$tab_rubrique['partie']][] = 'Absence d\'appréciation pour '.html($eleve_nom.' '.$eleve_prenom);
              }
            }
          }
          if($temp_HTML)
          {
            $rowspan = in_array($PAGE_COLONNE,array('moyenne','pourcentage')) ? '' : ' rowspan="2"' ;
            $input_cycle = ($eleve_id) ? '' : '<input type="hidden" id="cycle_id" value="'.substr($PAGE_RUBRIQUE_TYPE,1,1).'" />' ;
            $head_ligne2 = '';
            $releve_HTML .= '<h4 class="eval">'.rubrique_texte_intro('eval',$eleve_id).'</h4>'.NL;
            $releve_HTML .= '<table class="livret"><thead>'.NL.'<tr>';
            $releve_HTML .= ($BILAN_TYPE_ETABL=='college') ? '<th'.$rowspan.' class="nu"></th>' : '<th colspan="2"'.$rowspan.'>Domaines d’enseignement</th>' ;
            $releve_HTML .= '<th'.$rowspan.'>Principaux éléments du programme travaillés'.$input_cycle.'</th>';
            $releve_HTML .= '<th'.$rowspan.'>Acquisitions, progrès et difficultés éventuelles</th>';
            if( ( ($PAGE_COLONNE=='objectif') || ($PAGE_COLONNE=='position') ) && ( $eleve_id || $PAGE_MOYENNE_CLASSE ) )
            {
              $releve_HTML .= '<th colspan="4" class="eval hc">'.$legende_positionnement.'</th><th class="nu"></th>';
              $tab_th = array();
              foreach($_SESSION['LIVRET'] as $id => $tab)
              {
                $tab_th[] = '<th class="pos'.$id.'">'.$id.'<img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="de '.$tab['SEUIL_MIN'].' à '.$tab['SEUIL_MAX'].'" /></th>';
              }
              $tab_th[] = '<th class="nu"></th>';
              $head_ligne2 = '<tr>'.implode('',$tab_th).'</tr>';
            }
            else if( ($PAGE_COLONNE=='moyenne') && ( $eleve_id || $PAGE_MOYENNE_CLASSE ) )
            {
              $releve_HTML .= '<th colspan="5">Moyenne sur 20</th>';
            }
            else if( ($PAGE_COLONNE=='pourcentage') && ( $eleve_id || $PAGE_MOYENNE_CLASSE ) )
            {
              $releve_HTML .= '<th colspan="5">Pourcentage de réussite</th>';
            }
            $releve_HTML .= '</tr>'.NL.$head_ligne2.'</thead>'.NL.'<tbody>'.$temp_HTML.'</tbody></table>';
          }
        }
      }

      // EPI
      if( $PAGE_EPI && isset($tab_rubriques['epi']) )
      {
        if($make_pdf)
        {
          $tab_saisie_eleve  = isset($tab_saisie[$eleve_id]['epi']) ? $tab_saisie[$eleve_id]['epi'] : array() ;
          $tab_saisie_classe = isset($tab_saisie[    0    ]['epi']) ? $tab_saisie[    0    ]['epi'] : array() ;
          if( !empty($tab_saisie_eleve) || !empty($tab_saisie_classe) )
          {
            $releve_PDF->bloc_epi( $tab_rubriques['epi'] , $tab_saisie_eleve , $tab_saisie_classe );
            if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_epi' , array( $tab_rubriques['epi'] , $tab_saisie_eleve , $tab_saisie_classe ) ); }
          }
        }
        else
        {
          $temp_HTML = '';
          // On passe en revue les rubriques...
          foreach($tab_rubriques['epi'] as $livret_epi_id => $tab_epi)
          {
            $is_epi_prof = isset($tab_join_rubrique_profs['epi'][$livret_epi_id][$_SESSION['USER_ID']]) ? TRUE : FALSE ;
            if( ($make_action=='tamponner') || ( ($make_action=='modifier') && $is_epi_prof ) || ( ($make_action=='examiner') && isset($tab_exam_rubrique['epi']) ) || ($make_action=='consulter') || ($make_action=='imprimer') )
            {
              $epi_saisie = isset($tab_saisie[$eleve_id]['epi'][$livret_epi_id]['appreciation']) ? $tab_saisie[$eleve_id]['epi'][$livret_epi_id]['appreciation'] : $tab_saisie_initialisation ;
              if( $make_html || $make_graph )
              {
                $temp_HTML .= '<div class="epi">';
                $temp_HTML .= '<div class="b notnow">'.html($tab_epi['titre']).'</div>';
                $temp_HTML .= '<div class="b notnow">'.html($tab_epi['theme_nom']).'</div>';
                // Info saisies périodes antérieures
                if( isset($tab_saisie_avant[$eleve_id]['epi'][$livret_epi_id]['appreciation']) )
                {
                  $tab_avant_info = array();
                  foreach($tab_saisie_avant[$eleve_id]['epi'][$livret_epi_id]['appreciation'] as $jointure_periode => $saisie_valeur)
                  {
                    $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
                  }
                  ksort($tab_avant_info);
                  $tab_periode_liens  = array();
                  $tab_periode_textes = array();
                  foreach($tab_avant_info as $jointure_periode => $tab)
                  {
                    $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_epi_'.$livret_epi_id.'_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
                    $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_epi_'.$livret_epi_id.'_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.html($tab['appreciation']).'</div>';
                  }
                  $temp_HTML .= '<div class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</div>'.NL;
                }
                $temp_HTML .= '<div class="notnow">'.html(implode(' ; ',$tab_epi['mat_prof_txt'])).'</div>';
                if($epi_saisie['saisie_valeur'])
                {
                  $appreciation = html($epi_saisie['saisie_valeur']);
                  $origine = ' Dernière saisie par '.html($tab_profs[$epi_saisie['prof_id']]);
                  $actions = ( ($make_action=='modifier') && $is_epi_prof ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
                  if( in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && !$is_epi_prof )
                  {
                    $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
                    if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
                  }
                }
                else
                {
                  $appreciation = '<span class="danger">Absence de saisie !</span>' ;
                  $origine = ($epi_saisie['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$epi_saisie['prof_id']]) : '' ;
                  $actions = ( ($make_action=='modifier') && $is_epi_prof ) ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
                }
                $temp_HTML .= '<div id="epi_'.$livret_epi_id.'_appreciation_'.$epi_saisie['prof_id'].'">';
                $temp_HTML .=   '<span class="notnow">'.rubrique_texte_intro('epi',$eleve_id).'</span>';
                $temp_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
                $temp_HTML .=   '<div class="notnow" data-id="'.$epi_saisie['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
                $temp_HTML .= '</div>';
                $temp_HTML .= '</div>';
              }
              // Examen de présence de l'appréciation EPI
              else if( ($make_action=='examiner') && is_null($epi_saisie['saisie_valeur']) )
              {
                $tab_resultat_examen['Enseignements Pratiques Interdisciplinaires'][] = 'Absence d\'appréciation EPI "'.html($tab_epi['titre']).'" pour '.html($eleve_nom.' '.$eleve_prenom);
              }
            }
          }
          if($temp_HTML)
          {
            $releve_HTML .= '<h4 class="epi">'.Lang::_("Enseignements pratiques interdisciplinaires").'</h4>'.NL.$temp_HTML.NL;
          }
        }
      }

      // AP
      if( $PAGE_AP && isset($tab_rubriques['ap']) )
      {
        if($make_pdf)
        {
          $tab_saisie_eleve  = isset($tab_saisie[$eleve_id]['ap']) ? $tab_saisie[$eleve_id]['ap'] : array() ;
          $tab_saisie_classe = isset($tab_saisie[    0    ]['ap']) ? $tab_saisie[    0    ]['ap'] : array() ;
          if( !empty($tab_saisie_eleve) || !empty($tab_saisie_classe) )
          {
            $releve_PDF->bloc_ap( $tab_rubriques['ap'] , $tab_saisie_eleve , $tab_saisie_classe );
            if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_ap' , array( $tab_rubriques['ap'] , $tab_saisie_eleve , $tab_saisie_classe ) ); }
          }
        }
        else
        {
          $temp_HTML = '';
          // On passe en revue les rubriques...
          foreach($tab_rubriques['ap'] as $livret_ap_id => $tab_ap)
          {
            $is_ap_prof = isset($tab_join_rubrique_profs['ap'][$livret_ap_id][$_SESSION['USER_ID']]) ? TRUE : FALSE ;
            if( ($make_action=='tamponner') || ( ($make_action=='modifier') && $is_ap_prof ) || ( ($make_action=='examiner') && isset($tab_exam_rubrique['ap']) ) || ($make_action=='consulter') || ($make_action=='imprimer') )
            {
              $ap_saisie = isset($tab_saisie[$eleve_id]['ap'][$livret_ap_id]['appreciation']) ? $tab_saisie[$eleve_id]['ap'][$livret_ap_id]['appreciation'] : $tab_saisie_initialisation ;
              if( $make_html || $make_graph )
              {
                $temp_HTML .= '<div class="ap">';
                $temp_HTML .= '<div class="b notnow">'.html($tab_ap['titre']).'</div>';
                // Info saisies périodes antérieures
                if( isset($tab_saisie_avant[$eleve_id]['ap'][$livret_ap_id]['appreciation']) )
                {
                  $tab_avant_info = array();
                  foreach($tab_saisie_avant[$eleve_id]['ap'][$livret_ap_id]['appreciation'] as $jointure_periode => $saisie_valeur)
                  {
                    $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
                  }
                  ksort($tab_avant_info);
                  $tab_periode_liens  = array();
                  $tab_periode_textes = array();
                  foreach($tab_avant_info as $jointure_periode => $tab)
                  {
                    $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_ap_'.$livret_ap_id.'_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
                    $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_ap_'.$livret_ap_id.'_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.html($tab['appreciation']).'</div>';
                  }
                  $temp_HTML .= '<div class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</div>'.NL;
                }
                $temp_HTML .= '<div class="notnow">'.html(implode(' ; ',$tab_ap['mat_prof_txt'])).'</div>';
                if($ap_saisie['saisie_valeur'])
                {
                  $appreciation = html($ap_saisie['saisie_valeur']);
                  $origine = ' Dernière saisie par '.html($tab_profs[$ap_saisie['prof_id']]);
                  $actions = ( ($make_action=='modifier') && $is_ap_prof ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
                  if( in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && !$is_ap_prof )
                  {
                    $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
                    if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
                  }
                }
                else
                {
                  $appreciation = '<span class="danger">Absence de saisie !</span>' ;
                  $origine = ($ap_saisie['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$ap_saisie['prof_id']]) : '' ;
                  $actions = ( ($make_action=='modifier') && $is_ap_prof ) ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
                }
                $temp_HTML .= '<div id="ap_'.$livret_ap_id.'_appreciation_'.$ap_saisie['prof_id'].'">';
                $temp_HTML .=   '<span class="notnow">'.rubrique_texte_intro('ap',$eleve_id).'</span>';
                $temp_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
                $temp_HTML .=   '<div class="notnow" data-id="'.$ap_saisie['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
                $temp_HTML .= '</div>';
                $temp_HTML .= '</div>';
              }
              // Examen de présence de l'appréciation AP
              else if( ($make_action=='examiner') && is_null($ap_saisie['saisie_valeur']) )
              {
                $tab_resultat_examen['Accompagnements Personnalisés'][] = 'Absence d\'appréciation AP "'.html($tab_ap['titre']).'" pour '.html($eleve_nom.' '.$eleve_prenom);
              }
            }
          }
          if($temp_HTML)
          {
            $releve_HTML .= '<h4 class="ap">'.Lang::_("Accompagnement personnalisé").'</h4>'.NL.$temp_HTML.NL;
          }
        }
      }

      // Parcours
      if( $PAGE_PARCOURS && isset($tab_rubriques['parcours']) )
      {
        if($make_pdf)
        {
          $tab_saisie_eleve  = isset($tab_saisie[$eleve_id]['parcours']) ? $tab_saisie[$eleve_id]['parcours'] : array() ;
          $tab_saisie_classe = isset($tab_saisie[    0    ]['parcours']) ? $tab_saisie[    0    ]['parcours'] : array() ;
          if( !empty($tab_saisie_eleve) || !empty($tab_saisie_classe) )
          {
            $releve_PDF->bloc_parcours( $tab_rubriques['parcours'] , $tab_saisie_eleve , $tab_saisie_classe );
            if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_parcours' , array( $tab_rubriques['parcours'] , $tab_saisie_eleve , $tab_saisie_classe ) ); }
          }
        }
        else
        {
          $temp_HTML = '';
          // On passe en revue les rubriques...
          foreach($tab_rubriques['parcours'] as $livret_parcours_id => $tab_parcours)
          {
            $is_parcours_prof = isset($tab_join_rubrique_profs['parcours'][$livret_parcours_id][$_SESSION['USER_ID']]) ? TRUE : FALSE ;
            if( ($make_action=='tamponner') || ( ($make_action=='modifier') && $is_parcours_prof ) || ( ($make_action=='examiner') && isset($tab_exam_rubrique['parcours']) ) || ($make_action=='consulter') || ($make_action=='imprimer') )
            {
              $parcours_saisie = isset($tab_saisie[$eleve_id]['parcours'][$livret_parcours_id]['appreciation']) ? $tab_saisie[$eleve_id]['parcours'][$livret_parcours_id]['appreciation'] : $tab_saisie_initialisation ;
              if( $make_html || $make_graph )
              {
                $temp_HTML .= '<div class="parcours">';
                $temp_HTML .= '<div class="b notnow">'.html($tab_parcours['type_nom']).'</div>';
                // Info saisies périodes antérieures
                if( isset($tab_saisie_avant[$eleve_id]['parcours'][$livret_parcours_id]['appreciation']) )
                {
                  $tab_avant_info = array();
                  foreach($tab_saisie_avant[$eleve_id]['parcours'][$livret_parcours_id]['appreciation'] as $jointure_periode => $saisie_valeur)
                  {
                    $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
                  }
                  ksort($tab_avant_info);
                  $tab_periode_liens  = array();
                  $tab_periode_textes = array();
                  foreach($tab_avant_info as $jointure_periode => $tab)
                  {
                    $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_parcours_'.$livret_parcours_id.'_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
                    $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_parcours_'.$livret_parcours_id.'_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.html($tab['appreciation']).'</div>';
                  }
                  $temp_HTML .= '<div class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</div>'.NL;
                }
                if( $eleve_id && $tab_parcours['projet'] )
                {
                  $temp_HTML .= '<div class="b notnow">'.html($tab_parcours['projet']).'</div>';
                }
                $temp_HTML .= '<div class="notnow">'.html(implode(' ; ',$tab_parcours['prof_txt'])).'</div>';
                if( ($BILAN_TYPE_ETABL=='college') || !$eleve_id )
                {
                  if($parcours_saisie['saisie_valeur'])
                  {
                    $appreciation = html($parcours_saisie['saisie_valeur']);
                    $origine = ' Dernière saisie par '.html($tab_profs[$parcours_saisie['prof_id']]);
                    $actions = ( ($make_action=='modifier') && $is_parcours_prof ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
                    if( in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && !$is_parcours_prof )
                    {
                      $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
                      if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
                    }
                  }
                  else
                  {
                    $appreciation = '<span class="danger">Absence de saisie !</span>' ;
                    $origine = ($parcours_saisie['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$parcours_saisie['prof_id']]) : '' ;
                    $actions = ( ($make_action=='modifier') && $is_parcours_prof ) ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
                  }
                  $temp_HTML .= '<div id="parcours_'.$livret_parcours_id.'_appreciation_'.$parcours_saisie['prof_id'].'">';
                  $temp_HTML .=   '<span class="notnow">'.rubrique_texte_intro('parcours',$eleve_id).'</span>';
                  $temp_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
                  $temp_HTML .=   '<div class="notnow" data-id="'.$parcours_saisie['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
                  $temp_HTML .= '</div>';
                }
                $temp_HTML .= '</div>';
              }
              // Examen de présence de l'appréciation Parcours
              else if( ($make_action=='examiner') && is_null($parcours_saisie['saisie_valeur']) )
              {
                $tab_resultat_examen['Parcours'][] = 'Absence d\'appréciation "'.html($tab_parcours['type_nom']).'" pour '.html($eleve_nom.' '.$eleve_prenom);
              }
            }
          }
          if($temp_HTML)
          {
            $releve_HTML .= '<h4 class="parcours">'.Lang::_("Parcours éducatifs").'</h4>'.NL.$temp_HTML.NL;
          }
        }
      }

      // Modalités d'accompagnement
      if( $eleve_id && isset($tab_rubriques['modaccomp'][$eleve_id]) )
      {
        $information_ppre = isset($tab_rubriques['modaccomp_ppre'][$eleve_id]) ? $tab_rubriques['modaccomp_ppre'][$eleve_id] : NULL ;
        if($make_pdf)
        {
          $releve_PDF->bloc_modaccomp( $tab_rubriques['modaccomp'][$eleve_id] , $information_ppre );
          if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_modaccomp' , array( $tab_rubriques['modaccomp'][$eleve_id] , $information_ppre ) ); }
        }
        else if( $make_html || $make_graph )
        {
          $s = (count($tab_rubriques['modaccomp'][$eleve_id])>1) ? 's' : '' ;
          $releve_HTML .= '<div class="modaccomp">';
          $releve_HTML .='<div><b>'.sprintf_lang(Lang::_("Modalité|%1s spécifique|%1s d’accompagnement"),$s).' :</b> '.implode(', ',$tab_rubriques['modaccomp'][$eleve_id]).'.</div>';
          if($information_ppre)
          {
            $releve_HTML .= '<div><b>Information PPRE :</b> '.html($information_ppre).'</div>';
          }
          $releve_HTML .= '</div>'.NL;
        }
      }

      // Bilan de l’acquisition des connaissances et compétences
      $bilan_info = isset($tab_saisie[$eleve_id]['bilan'][0]['appreciation']) ? $tab_saisie[$eleve_id]['bilan'][0]['appreciation'] : $tab_saisie_initialisation ;
      if($make_pdf)
      {
        $releve_PDF->bloc_bilan( $bilan_info['saisie_valeur'] , $texte_prof_principal );
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_bilan' , array( $bilan_info['saisie_valeur'] , $texte_prof_principal ) ); }
      }
      else if( $make_html || $make_graph )
      {
        if( ($make_action=='tamponner') || ($make_action=='consulter') )
        {
          $titre = 
          $releve_HTML .= '<h4 class="bilan">Bilan de l’acquisition des connaissances et compétences</h4>'.NL;
          // Info saisies périodes antérieures
          if( isset($tab_saisie_avant[$eleve_id]['bilan'][0]['appreciation']) )
          {
            $tab_avant_info = array();
            foreach($tab_saisie_avant[$eleve_id]['bilan'][0]['appreciation'] as $jointure_periode => $saisie_valeur)
            {
              $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
            }
            ksort($tab_avant_info);
            $tab_periode_liens  = array();
            $tab_periode_textes = array();
            foreach($tab_avant_info as $jointure_periode => $tab)
            {
              $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_bilan_0_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
              $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_bilan_0_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.html($tab['appreciation']).'</div>';
            }
            $releve_HTML .= '<div class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</div>'.NL;
          }
          $releve_HTML .= '<div class="bilan">'.NL;
          if($bilan_info['saisie_valeur'])
          {
            $br = '<br />';
            $appreciation = html($bilan_info['saisie_valeur']);
            $origine = ($bilan_info['saisie_origine']=='bulletin') ? ' Report automatique du bulletin' : ' Dernière saisie par '.html($tab_profs[$bilan_info['prof_id']]) ;
            $actions = ($make_action=='tamponner') ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
            $actions.= ( ($make_action=='tamponner') && ($bilan_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
            if( ($make_action=='consulter') && in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && ($bilan_info['prof_id']!=$_SESSION['USER_ID']) )
            {
              $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
              if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
            }
          }
          else
          {
            $br = '';
            $appreciation = ($BILAN_ETAT=='2rubrique') ? '<span class="astuce">Absence de saisie.</span>' : '<span class="danger">Absence de saisie !</span>' ;
            $origine = ($bilan_info['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$bilan_info['prof_id']]) : '' ;
            $actions = ($make_action=='tamponner') ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
            $actions.= ( ($make_action=='tamponner') && ($bilan_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
          }
          $releve_HTML .= '<div id="bilan_0_appreciation_'.$bilan_info['prof_id'].'">';
          $releve_HTML .=   '<span class="notnow">'.rubrique_texte_intro('bilan',$eleve_id,$BILAN_TYPE_ETABL).'</span>'.$br;
          $releve_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
          $releve_HTML .=   '<div class="notnow" data-id="'.$bilan_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
          $releve_HTML .= '</div>';
          $releve_HTML .= '</div>'.NL;
        }
      }
      // Examen de présence de l'appréciation générale
      else if( ($make_action=='examiner') && isset($tab_exam_rubrique['bilan']) && is_null($bilan_info['saisie_valeur']) )
      {
        $tab_resultat_examen['Synthèse générale'][] = 'Absence d\'appréciation générale pour '.html($eleve_nom.' '.$eleve_prenom);
      }

      // Communication avec la famille
      if( $PAGE_VIE_SCOLAIRE && $eleve_id && ( ($make_html) || ($make_pdf) ) )
      {
        // Collège
        $viesco_info = isset($tab_saisie[$eleve_id]['viesco'][0]['appreciation']) ? $tab_saisie[$eleve_id]['viesco'][0]['appreciation'] : $tab_saisie_initialisation ;
        $texte_assiduite = texte_ligne_assiduite($tab_assiduite[$eleve_id]);
        if($make_pdf)
        {
          $releve_PDF->bloc_viesco_2d( $viesco_info['saisie_valeur'] , $texte_assiduite , $DATE_VERROU , $texte_chef_etabl , $tab_signature['chef'] , $tab_parent_lecture );
          if($is_archive)
          {
            if(!empty($tab_signature['chef']))
            {
              // On remplace l'image par son md5
              $image_contenu = $tab_signature['chef']['contenu'];
              $image_md5     = md5($image_contenu);
              $tab_archive['image'][$image_md5] = $image_contenu;
              $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
              $tab_signature['chef']['contenu'] = $image_md5;
            }
            $tab_archive['user'][$eleve_id][] = array( 'bloc_viesco_2d' , array( $viesco_info['saisie_valeur'] , $texte_assiduite , $DATE_VERROU , $texte_chef_etabl , $tab_signature['chef'] , $tab_parent_lecture ) );
            if(!empty($tab_signature['chef']))
            {
              // On remet la bonne image pour les tirages suivants
              $tab_signature['chef']['contenu'] = $image_contenu;
            }
          }
        }
        else
        {
          if( $make_html && ( ($make_action=='consulter') || $is_acces_viesco ) )
          {
            $releve_HTML .= '<h4 class="viesco">'.Lang::_("Communication avec la famille").'</h4>'.NL;
            // Info saisies périodes antérieures
            if( isset($tab_saisie_avant[$eleve_id]['viesco'][0]['appreciation']) )
            {
              $tab_avant_info = array();
              foreach($tab_saisie_avant[$eleve_id]['viesco'][0]['appreciation'] as $jointure_periode => $saisie_valeur)
              {
                $tab_avant_info[$jointure_periode]['appreciation'] = $saisie_valeur;
              }
              ksort($tab_avant_info);
              $tab_periode_liens  = array();
              $tab_periode_textes = array();
              foreach($tab_avant_info as $jointure_periode => $tab)
              {
                $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_viesco_0_'.$jointure_periode.'"></a> '.$tab_periode_livret['periode'.$jointure_periode];
                $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_viesco_0_'.$jointure_periode.'" class="appreciation bordertop hide"><b>'.$tab_periode_livret['periode'.$jointure_periode].'&nbsp;:&nbsp;</b>'.html($tab['appreciation']).'</div>';
              }
              $releve_HTML .= '<div class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</div>'.NL;
            }
            $releve_HTML .= '<div class="viesco">'.NL;
            if($viesco_info['saisie_valeur'])
            {
              $br = '<br />';
              $appreciation = html($viesco_info['saisie_valeur']);
              $origine = ($bilan_info['saisie_origine']=='bulletin') ? ' Report automatique du bulletin' : ' Dernière saisie par '.html($tab_profs[$viesco_info['prof_id']]) ;
              $actions = ( $is_acces_viesco && in_array($make_action,array('modifier','tamponner')) ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
              if( in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && !$is_acces_viesco )
              {
                $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
                if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
              }
            }
            else
            {
              $br = '';
              $appreciation = ($BILAN_ETAT=='2rubrique') ? '<span class="astuce">Absence de saisie.</span>' : '<span class="danger">Absence de saisie !</span>' ;
              $origine = ($viesco_info['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$viesco_info['prof_id']]) : '' ;
              $actions = ( $is_acces_viesco && in_array($make_action,array('modifier','tamponner')) ) ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
              $actions.= ( $is_acces_viesco && in_array($make_action,array('modifier','tamponner')) && ($bilan_info['saisie_origine']=='saisie') && ($BILAN_TYPE_ETABL=='college') && ($PAGE_RUBRIQUE_JOIN=='matiere') ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
            }
            $texte_assiduite = ($affichage_assiduite) ? '<div id="div_assiduite" class="notnow i">'.texte_ligne_assiduite($tab_assiduite[$eleve_id]).'</div>' : '' ;
            $releve_HTML .= '<div id="viesco_0_appreciation_'.$viesco_info['prof_id'].'">';
            $releve_HTML .=   '<span class="notnow">'.rubrique_texte_intro('viesco').'</span>'.$br;
            $releve_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
            $releve_HTML .=   '<div class="notnow" data-id="'.$viesco_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
            $releve_HTML .=   $texte_assiduite;
            $releve_HTML .= '</div>';
            $releve_HTML .= '</div>'.NL;
          }
        }
      }
      else if( ($BILAN_TYPE_ETABL=='ecole') && $make_pdf && $eleve_id )
      {
        // 1er degré
        $releve_PDF->bloc_viesco_1d( $DATE_VERROU , $tab_profs , $tab_signature['prof'] , $tab_parent_lecture );
        if($is_archive)
        {
          if(!empty($tab_signature['prof']))
          {
            // On remplace l'image par son md5
            $image_contenu = $tab_signature['prof']['contenu'];
            $image_md5     = md5($image_contenu);
            $tab_archive['image'][$image_md5] = $image_contenu;
            $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
            $tab_signature['prof']['contenu'] = $image_md5;
          }
          $tab_archive['user'][$eleve_id][] = array( 'bloc_viesco_1d' , array( $DATE_VERROU , $tab_profs , $tab_signature['prof'] , $tab_parent_lecture ) );
          if(!empty($tab_signature['prof']))
          {
            // On remet la bonne image pour les tirages suivants
            $tab_signature['prof']['contenu'] = $image_contenu;
          }
        }
      }

      if( $make_html )
      {
        $releve_HTML .= '<p />'.NL;
      }
      // Absences et retard
      if( $PAGE_VIE_SCOLAIRE && ($affichage_assiduite) && empty($is_appreciation_groupe) && !$is_acces_viesco && ($make_action!='consulter') && ( ($make_html) || ($make_graph) ) )
      {
        $releve_HTML .= '<div class="i">'.texte_ligne_assiduite($tab_assiduite[$eleve_id]).'</div>'.NL;
      }
      // Professeurs principaux
      if( ($affichage_prof_principal) && ( ($make_html) || ($make_graph) ) )
      {
        $releve_HTML .= '<div class="i">'.$texte_prof_principal.'</div>'.NL;
      }
      // Date de naissance
      if( ($date_naissance) && ( ($make_html) || ($make_graph) ) )
      {
        $releve_HTML .= '<div class="i">'.To::texte_ligne_naissance($date_naissance).'</div>'.NL;
      }
      if( $make_html )
      {
        $releve_HTML .= '<p>&nbsp;</p>'.NL;
      }
      // Indiquer a posteriori le nombre de pages par élève
      if($make_pdf)
      {
        $page_nb = $releve_PDF->reporter_page_nb();
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'reporter_page_nb' , array() ); }
        if($page_nb%2)
        {
          $releve_PDF->ajouter_page_blanche();
        }
      }
      // Mémorisation des pages de début et de fin pour chaque élève pour découpe et archivage ultérieur
      if($make_action=='imprimer')
      {
        $page_debut  = (isset($page_fin)) ? $page_fin+1 : 1 ;
        $page_fin    = $releve_PDF->page;
        $page_nombre = $page_fin - $page_debut + 1;
        $tab_pages_decoupe_pdf[$eleve_id][$numero_tirage] = array( $eleve_nom.' '.$eleve_prenom , $page_debut.'-'.$page_fin , $page_nombre );
      }

    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On enregistre les sorties HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Impression du HTML dans un fichier est ici inutile car ne sert pas par la suite.
// if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.html' , $releve_HTML ); }
if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf'  , $releve_PDF  ); }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le diagramme graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $make_graph && (count($tab_graph_data)) )
{
  // Rubriques sur l'axe des abscisses
  Json::add_row( 'script' , 'ChartOptions.title.text = null;' );
  Json::add_row( 'script' , 'ChartOptions.xAxis.categories = ['.implode(',',$tab_graph_data['categories']).'];' );
  // Second axe des ordonnés pour les moyennes
  if(in_array($PAGE_COLONNE,array('objectif','position')))
  {
    $text = 'Positionnement de 1 à 4';
    $ymin         = 1;
    $ymax         = 4;
    $tickInterval = 1;
  }
  else if($PAGE_COLONNE=='moyenne')
  {
    $text = 'Positionnement sur 20';
    $ymin         = 0;
    $ymax         = 20;
    $tickInterval = 5;
  }
  else
  {
    $text = 'Positionnement en pourcentage';
    $ymin         = 0;
    $ymax         = 100;
    $tickInterval = 25;
  }
  Json::add_row( 'script' , 'ChartOptions.yAxis = { min: '.$ymin.', max: '.$ymax.', tickInterval: '.$tickInterval.', gridLineColor: "#C0D0E0", title: { style: { color: "#333" } , text: "'.$text.'" } };' );
  // Séries de valeurs ; la classe avant l'élève pour être positionnée en dessous
  $tab_graph_series = array();
  if(isset($tab_graph_data['series_data_MoyClasse']))
  {
    $tab_graph_series['MoyClasse'] = '{ type: "line", name: "Moyenne classe", data: ['.implode(',',$tab_graph_data['series_data_MoyClasse']).'], marker: {symbol: "circle"}, color: "#999" }';
  }
  if(isset($tab_graph_data['series_data_MoyEleve']))
  {
    $tab_graph_series['MoyEleve']  = '{ type: "line", name: "Positionnement élève", data: ['.implode(',',$tab_graph_data['series_data_MoyEleve']).'], marker: {symbol: "circle"}, color: "#139" }';
  }
  Json::add_row( 'script' , 'ChartOptions.series = ['.implode(',',$tab_graph_series).'];' );
  Json::add_row( 'script' , 'graphique = new Highcharts.Chart(ChartOptions);' );
}

?>