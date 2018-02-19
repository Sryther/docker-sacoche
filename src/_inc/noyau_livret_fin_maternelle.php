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

$tab_eleve_infos = array();  // [eleve_id] => array(eleve_INE,eleve_nom,eleve_prenom,date_naissance)

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
// Synthèse des acquis scolaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubriques = array();
$tab_id_rubrique = array();

// Synthèse des acquis scolaires (évaluations)

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucune rubrique du livret n\'est associée aux référentiels ! Il faut configurer un minimum le livret avant son édition...' );
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_rubriques['eval'][$DB_ROW['livret_rubrique_id']] = array(
    'partie'       => $DB_ROW['rubrique'],
    'sous_partie'  => $DB_ROW['sous_rubrique'],
    'elements'     => $DB_ROW['rubrique_id_elements'],
    'appreciation' => $DB_ROW['rubrique_id_appreciation'],
    'position'     => $DB_ROW['rubrique_id_position'],
  );
  $tab_id_rubrique['elements'    ][$DB_ROW['rubrique_id_elements'    ]][$DB_ROW['livret_rubrique_id']] = $DB_ROW['livret_rubrique_id'];
  $tab_id_rubrique['appreciation'][$DB_ROW['rubrique_id_appreciation']][$DB_ROW['livret_rubrique_id']] = $DB_ROW['livret_rubrique_id'];
  $tab_id_rubrique['position'    ][$DB_ROW['rubrique_id_position'    ]][$DB_ROW['livret_rubrique_id']] = $DB_ROW['livret_rubrique_id'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Apprendre ensemble et vivre ensemble
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubriques['attitude'] = array(
  1 => "Maintien de l’attention, persévérance dans une activité",
  2 => "Prise en compte de consignes collectives",
  3 => "Participation aux activités, initiatives, coopération",
  4 => "Prise en compte des règles de la vie commune",
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les seuils pour les niveaux de réussite
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos('cycle1');
foreach($DB_TAB as $DB_ROW)
{
  $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3
  $_SESSION['LIVRET'][$id]['USED']      = TRUE;
  $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
  $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
  $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
}
$_SESSION['LIVRET'][4]['USED'] = FALSE;
$tab_archive['session']['LIVRET'] = $_SESSION['LIVRET']; // on maj du coup

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
// Nombre de boucles par élève : une seule car pas de bloc adresse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  $tab_destinataires[$eleve_id] = array( 0 => TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du relevé de fin de cycle
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_graph_data = array();

// Préparatifs
if( ($make_html) || ($make_graph) )
{
  $bouton_print_test = (isset($is_bouton_test_impression))                  ? ( ($is_bouton_test_impression) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : ' <button id="simuler_disabled" type="button" class="imprimer" disabled>Pour simuler l\'impression, sélectionner un élève</button>' ) : '' ;
  $bouton_archivage  = ''; // Bilans hors maternelle uniquement
  $bouton_import_csv = ''; // Bilans périodiques du collège uniquement
  $releve_HTML = (!$make_graph) ? '<div>'.$bouton_archivage.$bouton_print_test.$bouton_import_csv.'</div>'.NL : '<div id="div_graphique_synthese"></div>'.NL ;
  // légende
  $positionnement_texte = 'Réussite' ;
  $positionnement_title = array();
  foreach($_SESSION['LIVRET'] as $id => $tab)
  {
    if($tab['USED'])
    {
      $positionnement_title[] = html($id.' = '.$tab['LEGENDE']);
    }
  }
  $legende_positionnement = $positionnement_texte.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.implode('<br />',$positionnement_title).'" />';
}

if($make_pdf)
{
  $nb_lignes_eleve_eval_total = 1 /*nb_lignes_marge*/ + 1.5 /*nb_lignes_intitule*/ + 2 /*nb_lignes_eval_tete*/ + (5*1+2*2+2*2+4*1+5*1) /*nb_lignes_eleve_eval*/ + 1 /*nb_lignes_pos_legende*/ ;
  $releve_PDF = new PDF_livret_scolaire( TRUE /*make_officiel*/ , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende , !empty($is_test_impression) /*filigrane*/ );
  $releve_PDF->initialiser( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] , $tab_saisie_initialisation );
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
          $tab_archive['user'][$eleve_id][] = array( 'initialiser' , array( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] , $tab_saisie_initialisation ) );
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
        $releve_PDF->entete( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $nb_lignes_eleve_eval_total );
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
          $tab_archive['user'][$eleve_id][] = array( 'entete' , array( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $nb_lignes_eleve_eval_total ) );
        }
      }

      // Synthèse des acquis scolaires à la fin de l’école maternelle
      $tab_saisie_eleve_eval = isset($tab_saisie[$eleve_id]['eval']) ? $tab_saisie[$eleve_id]['eval'] : array() ;
      if($make_pdf)
      {
        $releve_PDF->bloc_cycle1_eval( $tab_rubriques['eval'] , $tab_id_rubrique , $tab_saisie_eleve_eval );
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_cycle1_eval' , array( $tab_rubriques['eval'] , $tab_id_rubrique , $tab_saisie_eleve_eval ) ); }
      }
      else
      {
        $temp_HTML = '';
        // On passe en revue les rubriques...
        foreach($tab_rubriques['eval'] as $livret_rubrique_id => $tab_rubrique)
        {
          // récup éléments travaillés
          $id_rubrique_elements = $livret_rubrique_id ; // On force une ligne par sous-rubrique, donc pas $tab_rubriques['eval'][$livret_rubrique_id]['elements'];
          $elements_info = isset($tab_saisie_eleve_eval[$id_rubrique_elements]['elements']) ? $tab_saisie_eleve_eval[$id_rubrique_elements]['elements'] : $tab_saisie_initialisation ;
          // récup appréciation
          $id_rubrique_appreciation = $tab_rubriques['eval'][$livret_rubrique_id]['appreciation'];
          $appreciation_info = isset($tab_saisie_eleve_eval[$id_rubrique_appreciation]['appreciation']) ? $tab_saisie_eleve_eval[$id_rubrique_appreciation]['appreciation'] : $tab_saisie_initialisation ;
          // récup positionnement
          $id_rubrique_position = $tab_rubriques['eval'][$livret_rubrique_id]['position'];
          $position_info = isset($tab_saisie_eleve_eval[$id_rubrique_position]['position']) ? $tab_saisie_eleve_eval[$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
          // Interface graphique
          if($make_graph)
          {
            $rubrique_nom = $tab_rubrique['partie'].' - '.$tab_rubrique['sous_partie'];
            $tab_graph_data['categories'][$id_rubrique_position] = '"'.addcslashes($rubrique_nom,'"').'"';
            $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
            $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage ) ;
            $tab_graph_data['series_data_MoyEleve'][$id_rubrique_position] = !is_null($position_info['saisie_valeur']) ? $note : 'null' ;
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
            $tab_temp_HTML = array( 'domaine'=>''  , 'appreciation'=>'' , 'position'=>'');
            // Domaine d’enseignement
            $details = ( $elements_info['acquis_detail'] ) ? '<div><a href="#" class="voir_detail" data-id="'.$id_rubrique_elements.'">[ détail travaillé ]</a></div><div id="detail_'.$id_rubrique_elements.'" class="hide">'.$elements_info['acquis_detail'].'</div>' : '' ;
            $nombre_sous_rubriques = count($tab_id_rubrique['appreciation'][$id_premiere_sous_rubrique]);
            if( $nombre_sous_rubriques == 1 )
            {
              $tab_temp_HTML['domaine'] .= ($tab_rubrique['sous_partie']) ? '<td><b>'.html($tab_rubrique['partie']).'</b></td><td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' : '<td colspan="2" class="hc"><b>'.html($tab_rubrique['partie']).'</b>'.$details.'</td>' ;
            }
            else
            {
              $rowspan = ($nombre_sous_rubriques>1) ? ' rowspan="'.$nombre_sous_rubriques.'"' : '' ;
              $tab_temp_HTML['domaine'] .= isset($tab_deja_affiche[$eleve_id][$id_premiere_sous_rubrique]) ? '<td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' : '<td'.$rowspan.'><b>'.html($tab_rubrique['partie']).'</b></td><td><b>'.html($tab_rubrique['sous_partie']).'</b>'.$details.'</td>' ;
            }
            // Positionnement (réussite)
            $nombre_rubriques_regroupees = count($tab_id_rubrique['position'][$id_rubrique_position]);
            if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$eleve_id][$id_rubrique_position]) )
            {
              if(!is_null($position_info['saisie_valeur']))
              {
                $pourcentage = $position_info['saisie_valeur'];
                $origine = ($position_info['saisie_origine']=='calcul') ? 'Calculé automatiquement' : 'Saisi par '.html($tab_profs[$position_info['prof_id']]) ;
                $actions = ($make_action=='modifier') ? ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button> <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>' : '' ;
                $actions.= ( ($make_action=='modifier') && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
              }
              else
              {
                $pourcentage = FALSE ;
                $origine = ($position_info['saisie_origine']=='saisie') ? 'Supprimé par '.html($tab_profs[$position_info['prof_id']]) : '' ;
                $actions = ($make_action=='modifier') ? ' <button type="button" class="ajouter" title="Ajouter le positionnement">&nbsp;</button>' : '' ;
                $actions.= ( ($make_action=='modifier') && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
              }
              $rowspan = ($nombre_rubriques_regroupees>1) ? ' rowspan="'.$nombre_rubriques_regroupees.'"' : '' ;
              $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
              $origine .= ( $origine && ($position_info['saisie_origine']=='calcul') ) ? ' : '.$pourcentage.' %' : '' ;
              foreach($_SESSION['LIVRET'] as $id => $tab)
              {
                if($tab['USED'])
                {
                  $texte = ($id==$indice) ? '<b>X</b>' : '' ;
                  $tab_temp_HTML['position'] .= '<td'.$rowspan.' id="eval_'.$livret_rubrique_id.'_position_'.$id.'" class="pos'.$id.'">'.$texte.'</td>';
                }
              }
              $tab_temp_HTML['position'] .= '<td'.$rowspan.' id="eval_'.$livret_rubrique_id.'_position_'.$PAGE_COLONNE.'" class="nu"><div class="notnow" data-id="'.$position_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div><i>'.$indice.'</i></td>';
            }
            // Points forts et besoins à prendre en compte
            $nombre_rubriques_regroupees = count($tab_id_rubrique['appreciation'][$id_rubrique_appreciation]);
            if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$eleve_id][$id_rubrique_appreciation]) )
            {
              if($appreciation_info['saisie_valeur'])
              {
                $appreciation = html($appreciation_info['saisie_valeur']);
                $origine = 'Validé par '.html($tab_profs[$appreciation_info['prof_id']]);
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
            $temp_HTML .= '<tr>'.implode('',$tab_temp_HTML).'</tr>'.NL;
            $tab_deja_affiche[$eleve_id][$id_premiere_sous_rubrique] = TRUE;
          }
        }
        if($temp_HTML)
        {
          $head_ligne2 = '';
          $releve_HTML .= '<h4 class="eval">'.rubrique_texte_intro('cycle1',$eleve_id).'</h4>'.NL;
          $releve_HTML .= '<table class="livret"><thead>'.NL.'<tr>';
          $releve_HTML .= '<th colspan="2" rowspan="2" class="hc">Domaines d’enseignement</th>';
          $releve_HTML .= '<th rowspan="2" class="hc">Points forts et besoins à prendre en compte</th>';
          $releve_HTML .= '<th colspan="3" class="eval hc">'.$legende_positionnement.'</th><th class="nu"></th>';
          $tab_th = array();
          foreach($_SESSION['LIVRET'] as $id => $tab)
          {
            if($tab['USED'])
            {
              $tab_th[] = '<th class="pos'.$id.'">'.$id.'<img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="de '.$tab['SEUIL_MIN'].' à '.$tab['SEUIL_MAX'].'" /></th>';
            }
          }
          $tab_th[] = '<th class="nu"></th>';
          $head_ligne2 = '<tr>'.implode('',$tab_th).'</tr>';
          $releve_HTML .= '</tr>'.NL.$head_ligne2.'</thead>'.NL.'<tbody>'.$temp_HTML.'</tbody></table>';
        }
      }

      // Apprendre ensemble et vivre ensemble
      $tab_saisie_eleve_attitude = isset($tab_saisie[$eleve_id]['attitude']) ? $tab_saisie[$eleve_id]['attitude'] : array() ;
      if($make_pdf)
      {
        $releve_PDF->bloc_cycle1_attitude( $tab_rubriques['attitude'] , $tab_saisie_eleve_attitude );
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_cycle1_attitude' , array( $tab_rubriques['attitude'] , $tab_saisie_eleve_attitude ) ); }
      }
      else
      {
        $releve_HTML .= '<br />';
        $releve_HTML .= '<table class="livret"><thead>'.NL.'<tr>';
        $releve_HTML .= '<th rowspan="2">Apprendre ensemble et vivre ensemble</th>';
        $releve_HTML .= '<th rowspan="2">Observations réalisées par l’enseignant(e)</th>';
        $releve_HTML .= '</tr></thead>'.NL.'<tbody>';
        // On passe en revue les rubriques...
        foreach($tab_rubriques['attitude'] as $livret_attitude_id => $attitude_intitule)
        {
          $appreciation_info = isset($tab_saisie_eleve_attitude[$livret_attitude_id]['appreciation']) ? $tab_saisie_eleve_attitude[$livret_attitude_id]['appreciation'] : $tab_saisie_initialisation ;
          if( $make_html || $make_graph )
          {
            if($appreciation_info['saisie_valeur'])
            {
              $appreciation = html($appreciation_info['saisie_valeur']);
              $origine = 'Validé par '.html($tab_profs[$appreciation_info['prof_id']]);
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
            $releve_HTML .= '<tr><td><b>'.html($attitude_intitule).'</b></td><td id="attitude_'.$livret_attitude_id.'_appreciation_'.$appreciation_info['prof_id'].'"><div class="appreciation">'.$appreciation.'</div><div class="notnow" data-id="'.$appreciation_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div></td></tr>'.NL;
          }
        }
        $releve_HTML .= '</tbody></table>';
      }

      // Communication avec la famille
      if($make_pdf)
      {
        $releve_PDF->bloc_cycle_signatures( $DATE_VERROU , $texte_chef_etabl , $tab_profs , $tab_signature['chef'] , $tab_signature['prof'] , $tab_parent_lecture );
        if($is_archive)
        {
          if(!empty($tab_signature['prof']))
          {
            // On remplace l'image par son md5
            $image_contenu_prof = $tab_signature['prof']['contenu'];
            $image_md5          = md5($image_contenu_prof);
            $tab_archive['image'][$image_md5] = $image_contenu_prof;
            $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
            $tab_signature['prof']['contenu'] = $image_md5;
          }
          if(!empty($tab_signature['chef']))
          {
            // On remplace l'image par son md5
            $image_contenu_chef = $tab_signature['chef']['contenu'];
            $image_md5          = md5($image_contenu_chef);
            $tab_archive['image'][$image_md5] = $image_contenu_chef;
            $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
            $tab_signature['chef']['contenu'] = $image_md5;
          }
          $tab_archive['user'][$eleve_id][] = array( 'bloc_cycle_signatures' , array( $DATE_VERROU , $texte_chef_etabl , $tab_profs , $tab_signature['chef'] , $tab_signature['prof'] , $tab_parent_lecture ) );
          if(!empty($tab_signature['prof']))
          {
            // On remet la bonne image pour les tirages suivants
            $tab_signature['prof']['contenu'] = $image_contenu_prof;
          }
          if(!empty($tab_signature['chef']))
          {
            // On remet la bonne image pour les tirages suivants
            $tab_signature['chef']['contenu'] = $image_contenu_chef;
          }
        }
      }

      // Référence Eduscol
      if($make_pdf)
      {
        $releve_PDF->cycle1_ref_eduscol();
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'cycle1_ref_eduscol' , array() ); }
      }

      if( $make_html )
      {
        $releve_HTML .= '<p />'.NL;
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

if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.html' , $releve_HTML ); }
if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf'  , $releve_PDF  ); }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le diagramme graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $make_graph && (count($tab_graph_data)) )
{
  // Rubriques sur l'axe des abscisses
  Json::add_row( 'script' , 'ChartOptions.title.text = null;' );
  Json::add_row( 'script' , 'ChartOptions.xAxis.categories = ['.implode(',',$tab_graph_data['categories']).'];' );
  // Axe des ordonnées pour les positionnements
  $text = 'Réussite';
  $ymin         = 1;
  $ymax         = 3;
  $tickInterval = 1;
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