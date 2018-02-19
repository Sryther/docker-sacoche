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
 * Pour une page de livret d'une période et d'une classe donnée, calculer et mettre à jour tous les positionnements & éléments travaillés qui en ont besoin et qui ne sont pas figés manuellement.
 * Si demandé, calcule et met en session les moyennes de classe.
 * 
 * @param string $PAGE_REF
 * @param string $PAGE_PERIODICITE
 * @param int    $JOINTURE_PERIODE
 * @param string $PAGE_RUBRIQUE_TYPE
 * @param string $PAGE_RUBRIQUE_JOIN
 * @param string $PAGE_COLONNE
 * @param int    $periode_id
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param int    $classe_id
 * @param string $liste_eleve_id
 * @param string $import_bulletin_notes   tous|reel|rien
 * @param string $only_socle   0|1
 * @param string $retroactif   oui|non|annuel|auto
 * @return void
 */
function calculer_et_enregistrer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $classe_id , $liste_eleve_id , $import_bulletin_notes , $only_socle , $retroactif )
{
  if(!$liste_eleve_id) return FALSE;
  $RUBRIQUE_TYPE = ($PAGE_COLONNE=='maitrise') ? 'socle' : 'eval' ;
  $afficher_score = TRUE; // Outil::test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] ) peut potentiellement poser pb pour un admin et dans cette partie du livret réservée aux profs des références chiffrées sont de toutes façon affichées.
  // Si on connait les langues associées aux élèves, alors on essaye de limiter les confusions LV1 / LV2.
  // (car si un référenfiel "Anglais" est relié à Anglais LV1 et Anglais LV2 alors les 2 lignes remplies à l'identique vont sinon apparaître sur le livret de l'élève)
  // (idem si un prof d'anglais est relié à Anglais LV1 et Anglais LV2)
  if($RUBRIQUE_TYPE=='eval')
  {
    $tab_rubrique_a_eviter = array();
    $tab_id_lv1 = array_fill ( 315 , 12 , TRUE ); // de 315 à 326
    $tab_id_lv2 = array_fill ( 327 , 12 , TRUE ); // de 327 à 338
    // Attention : l'identifiant de langue enregistré est le code du pays, pas l'identifiant matière de SACoche...
    require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_vivantes.php');
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( $liste_eleve_id , 'user_id,eleve_lv1,eleve_lv2' );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_rubrique_a_eviter[$DB_ROW['user_id']] = array();
      $matiere_id_lv1 = ($DB_ROW['eleve_lv1']!=100) ? $tab_langues[$DB_ROW['eleve_lv1']]['tab_matiere_id'][2] : 0 ;
      if(isset($tab_id_lv1[$matiere_id_lv1]))
      {
        $tab_rubrique_a_eviter[$DB_ROW['user_id']] += $tab_id_lv1;
        unset($tab_rubrique_a_eviter[$DB_ROW['user_id']][$matiere_id_lv1]);
      }
      $matiere_id_lv2 = ($DB_ROW['eleve_lv2']!=100) ? $tab_langues[$DB_ROW['eleve_lv2']]['tab_matiere_id'][3] : 0 ;
      if( isset($tab_id_lv2[$matiere_id_lv2]) || ( $matiere_id_lv1 && !$matiere_id_lv2 ) )
      {
        $tab_rubrique_a_eviter[$DB_ROW['user_id']] += $tab_id_lv2;
        unset($tab_rubrique_a_eviter[$DB_ROW['user_id']][$matiere_id_lv2]);
      }
    }
  }
  //
  // Récupérer les données déjà enregistrées, concernant les élèves et la classe
  //
  $tab_donnees_livret = array();
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"'.$RUBRIQUE_TYPE.'","bilan","viesco"' , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $clef = $DB_ROW['rubrique_type'].$DB_ROW['rubrique_id'].'eleve'.$DB_ROW['eleve_id'].$DB_ROW['saisie_objet'];
    $saisie_valeur = ($DB_ROW['saisie_objet']=='position') ? ( ($DB_ROW['saisie_valeur']!==NULL) ? (float)$DB_ROW['saisie_valeur'] : NULL ) : (string)$DB_ROW['saisie_valeur'] ;
    $tab_donnees_livret[$clef] = array(
      'id'            => $DB_ROW['livret_saisie_id'] ,
      'valeur'        => $saisie_valeur ,
      'origine'       => $DB_ROW['saisie_origine'] ,
      'prof'          => $DB_ROW['user_id'] ,
      'listing_profs' => $DB_ROW['listing_profs'] ,
      'acquis_detail' => $DB_ROW['acquis_detail'] ,
    );
  }
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"'.$RUBRIQUE_TYPE.'","bilan","viesco"' , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $clef = $DB_ROW['rubrique_type'].$DB_ROW['rubrique_id'].'classe'.$classe_id.$DB_ROW['saisie_objet'];
    $saisie_valeur = ($DB_ROW['saisie_objet']=='position') ? ( ($DB_ROW['saisie_valeur']!==NULL) ? (float)$DB_ROW['saisie_valeur'] : NULL ) : (string)$DB_ROW['saisie_valeur'] ;
    $tab_donnees_livret[$clef] = array(
      'id'            => $DB_ROW['livret_saisie_id'] ,
      'valeur'        => $saisie_valeur ,
      'origine'       => $DB_ROW['saisie_origine'] ,
      'prof'          => $DB_ROW['user_id'] ,
      'listing_profs' => $DB_ROW['listing_profs'] ,
      'acquis_detail' => '' ,
    );
  }
  //
  // Récupérer les données du bulletin correspondant (s'il existe, et correspondance de matière à matière), concernant les élèves et la classe
  //
  $is_recup_bulletin = FALSE;
  if( (substr($PAGE_RUBRIQUE_TYPE,3)=='matiere') && ($PAGE_RUBRIQUE_JOIN=='matiere') )
  {
    $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,'bulletin');
    if( !empty($DB_ROW) && !in_array( $DB_ROW['officiel_bulletin'] , array('','1vide') ) ) // "0absence" est enregistré comme une chaine vide en BDD
    {
      $is_recup_bulletin = TRUE;
      // Récupération de $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'], pas mis en session ici car seule variable utilisée ponctuellement
      $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'bulletin' /*bilan_type*/ , $DB_ROW['configuration_ref'] );
      $BULLETIN_MOYENNE_SCORES = $tab_configuration['moyenne_scores'];
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_correspondances_matieres_uniques( $PAGE_RUBRIQUE_TYPE );
      if(!empty($DB_TAB))
      {
        $tab_matiere_bulletin_to_livret[ 0][0] = TRUE; // synthèse
        $tab_matiere_bulletin_to_livret[54][0] = TRUE; // vie scolaire
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_matiere_bulletin_to_livret[$DB_ROW['matiere_referentiel_id']][$DB_ROW['matiere_livret_id']] = TRUE; // Il peut y avoir plusieurs liaisons [même matiere bulletin] => [différentes rubriques siècle livret], en particulier pour les langues
        }
        $tab_tmp = array();
        for( $i=0 ; $i<2 ; $i++ )
        {
          // Un passage pour les saisies concernant les élèves, un autre pour les saisies concernant la classe
          if($i==0)
          {
            $cible_nature = 'eleve';
            $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( 'bulletin' , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
          }
          else
          {
            $cible_nature = 'classe';
            $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( 'bulletin' , $periode_id , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
          }
          foreach($DB_TAB as $DB_ROW)
          {
            if(isset($tab_matiere_bulletin_to_livret[$DB_ROW['rubrique_id']]))
            {
              foreach($tab_matiere_bulletin_to_livret[$DB_ROW['rubrique_id']] as $rubrique_id => $bool)
              {
                $cible_id      = ($cible_nature=='eleve') ? $DB_ROW['eleve_id'] : $classe_id ;
                $prof_id       = $DB_ROW['prof_id']; // toujours 0 pour une note dans les bulletins
                $rubrique_type = ($rubrique_id) ? 'eval' : ( $DB_ROW['rubrique_id'] ? 'viesco' : 'bilan' ) ;
                $saisie_objet  = ($prof_id) ? 'appreciation' : 'position' ;
                $saisie_valeur = ($prof_id) ? (string)$DB_ROW['saisie_appreciation'] : ( ($DB_ROW['saisie_note']!==NULL) ? (float)$DB_ROW['saisie_note']*5 : NULL ) ; // Le bulletin enregistre sur 20, le livret enregistre sur 100.
                if( ( ($import_bulletin_notes=='tous') || ($saisie_objet=='appreciation') || ( ($import_bulletin_notes=='reel') && ($saisie_valeur!==NULL) ) ) && (  ($cible_nature=='classe') || !isset($tab_rubrique_a_eviter[$cible_id][$rubrique_id]) ) )
                {
                  $clef = $rubrique_type.$rubrique_id.$cible_nature.$cible_id.$saisie_objet;
                  if($prof_id)
                  {
                    // Pour gérer le pb des appéciations multiples à concaténer
                    $key = $rubrique_type.$rubrique_id.'x'.$cible_id;
                    if(isset($tab_tmp[$key]))
                    {
                      $saisie_valeur = $tab_tmp[$key]."\n".$saisie_valeur;
                    }
                    $tab_tmp[$key] = $saisie_valeur;
                  }
                  if(!isset($tab_donnees_livret[$clef]))
                  {
                    $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $rubrique_type , $rubrique_id , $cible_nature , $cible_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id );
                    $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $saisie_valeur , 'origine' => 'bulletin' , 'prof' => $prof_id , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
                  }
                  else if( ($tab_donnees_livret[$clef]['valeur']!==$saisie_valeur) && ($tab_donnees_livret[$clef]['origine']!='saisie') )
                  {
                    $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
                    DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id );
                    $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $saisie_valeur , 'origine' => 'bulletin' , 'prof' => $prof_id , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
                  }
                  else
                  {
                    $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
                    $tab_donnees_livret[$clef]['find'] = TRUE;
                  }
                }
              }
            }
          }
        }
        unset($tab_tmp);
      }
    }
  }
  //
  // On continue par une recherche dans le relevé d'évaluations correspondant (s'il existe, et uniquement pour une appréciation de synthèse générale)
  //
  if( ($RUBRIQUE_TYPE=='eval') && !$is_recup_bulletin )
  {
    $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,'releve');
    // un test ci-dessous a pour but d'éviter une récupération obsolète en cas de données issues d'un relevé d'évaluations qui depuis a été abandonné
    if( !empty($DB_ROW) && !in_array( $DB_ROW['officiel_releve'] , array('','1vide') ) ) // "0absence" est enregistré comme une chaine vide en BDD
    {
      $is_recup_bulletin = TRUE;
      $tab_tmp = array();
      $rubrique_type = 'bilan';
      $rubrique_id   = 0;
      $saisie_objet  = 'appreciation';
      for( $i=0 ; $i<2 ; $i++ )
      {
        // Un passage pour les saisies concernant les élèves, un autre pour les saisies concernant la classe
        if($i==0)
        {
          $cible_nature = 'eleve';
          $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( 'releve' , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , TRUE /*only_synthese_generale*/ );
        }
        else
        {
          $cible_nature = 'classe';
          $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( 'releve' , $periode_id , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , TRUE /*only_synthese_generale*/ );
        }
        foreach($DB_TAB as $DB_ROW)
        {
          $cible_id      = ($cible_nature=='eleve') ? $DB_ROW['eleve_id'] : $classe_id ;
          $prof_id       = $DB_ROW['prof_id']; // forcément renseigné pour une appréciation
          $saisie_valeur = (string)$DB_ROW['saisie_appreciation'];
          $clef = $rubrique_type.$rubrique_id.$cible_nature.$cible_id.$saisie_objet;
          // Pour gérer le pb des appéciations multiples à concaténer
          $key = $rubrique_type.$rubrique_id.'x'.$cible_id;
          if(isset($tab_tmp[$key]))
          {
            $saisie_valeur = $tab_tmp[$key]."\n".$saisie_valeur;
          }
          $tab_tmp[$key] = $saisie_valeur;
          if(!isset($tab_donnees_livret[$clef]))
          {
            $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $rubrique_type , $rubrique_id , $cible_nature , $cible_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id ); // on laisse "bulletin" comme origine...
            $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $saisie_valeur , 'origine' => 'bulletin' , 'prof' => $prof_id , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
          }
          else if( ($tab_donnees_livret[$clef]['valeur']!==$saisie_valeur) && ($tab_donnees_livret[$clef]['origine']!='saisie') )
          {
            $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
            DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id ); // on laisse "bulletin" comme origine...
            $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $saisie_valeur , 'origine' => 'bulletin' , 'prof' => $prof_id , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
          }
          else
          {
            $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
            $tab_donnees_livret[$clef]['find'] = TRUE;
          }
        }
      }
      unset($tab_tmp);
    }
  }
  //
  // Cas 1/2 : Positionnement du socle en fin de cycle
  //
  if($PAGE_COLONNE=='maitrise')
  {
    $cycle_id = $PAGE_RUBRIQUE_TYPE{1};
    // Récupération de la liste des items et des liaisons items / composantes
    $tab_join_item_socle = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_associations_items_composantes( $cycle_id );
    if(!$DB_TAB) return FALSE;
    foreach($DB_TAB as $DB_ROW)
    {
      $socle_composante_id = ($DB_ROW['socle_domaine_id']==1) ? $DB_ROW['socle_composante_id'] : $DB_ROW['socle_domaine_id']*10 ; // 11 12 13 14 20 30 40 50
      $tab_join_item_socle[$DB_ROW['item_id']][$socle_composante_id] = $socle_composante_id;
    }
    $liste_item_id = implode(',',array_keys($tab_join_item_socle));
    // Il faut au moins connaître le mode de calcul associé à chaque item
    $tab_item_infos = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , TRUE /*detail*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['ref_perso'] : $DB_ROW['ref_auto'] ;
      $tab_item_infos[$DB_ROW['item_id']] = array(
        'item_ref'       => $DB_ROW['matiere_ref'].'.'.$item_ref,
        'item_nom'       => $DB_ROW['item_nom'],
        'calcul_methode' => $DB_ROW['calcul_methode'],
        'calcul_limite'  => $DB_ROW['calcul_limite'],
      );
    }
    // Récupération de la liste des résultats
    $tab_eval = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , -1 /*matiere_id*/ , FALSE /*date_mysql_debut*/ , FALSE /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , TRUE /*onlynote*/ , FALSE /*first_order_by_date*/ );
    if(!$DB_TAB) return FALSE;
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note']; // pas besoin de la date ici
    }
    // Initialiser les tableaux pour retenir les données
    $tab_init_score = array_fill_keys( array_keys($_SESSION['ACQUIS']) , 0 ) + array( 'nb' => 0 ) + array( 'detail' => array() );
    $tab_score_eleve_composante = array();  // [eleve_id][composante_id] => array([etats],nb,%)   // Retenir le nb d'items acquis ou pas / élève / composante
    $tab_eleve_id = explode(',',$liste_eleve_id);
    $tab_composante = array(11,12,13,14,20,30,40,50);
    foreach($tab_eleve_id as $eleve_id)
    {
      foreach($tab_composante as $socle_composante_id)
      {
        $tab_score_eleve_composante[$eleve_id][$socle_composante_id] = $tab_init_score;
      }
    }
    // Déterminer les scores, et donc états d'acquisition
    foreach($tab_eval as $eleve_id => $tab_eval_eleve)
    {
      // Pour chaque item évalué...
      foreach($tab_eval_eleve as $item_id => $tab_devoirs)
      {
        extract($tab_item_infos[$item_id]);  // $calcul_methode $calcul_limite $item_ref $item_nom
        // calcul du bilan de l'item
        $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , NULL /*date_mysql_debut*/ );
        if($score!==FALSE)
        {
          // on détermine si il est acquis ou pas
          $indice = OutilBilan::determiner_etat_acquisition( $score );
          // Pour le détail des items évalués et leur score (sera associé aux ids de rubriques des positionnements)
          $pourcentage = ($afficher_score) ? $score.'%' : '&nbsp;' ;
          $item_detail = '<div><span class="pourcentage A'.$indice.'">'.$pourcentage.'</span> '.html($item_ref.' - '.$item_nom).'</div>';
          // on enregistre les infos
          foreach($tab_join_item_socle[$item_id] as $socle_composante_id)
          {
            $tab_score_eleve_composante[$eleve_id][$socle_composante_id][$indice]++;
            $tab_score_eleve_composante[$eleve_id][$socle_composante_id]['nb']++;
            $tab_score_eleve_composante[$eleve_id][$socle_composante_id]['detail'][] = $item_detail;
          }
        }
      }
    }
    // Calculer les pourcentages d'acquisition à partir du nombre d'items de chaque état
    $tab_positions_calculees = array();
    foreach($tab_score_eleve_composante as $eleve_id=>$tab_score_composante)
    {
      foreach($tab_score_composante as $socle_composante_id=>$tab_score)
      {
        $pourcentage = ($tab_score['nb']) ? OutilBilan::calculer_pourcentage_acquisition_items( $tab_score , $tab_score['nb'] ) :  NULL ;
        $tab_positions_calculees[$socle_composante_id][$eleve_id] = $pourcentage;
      }
    }
  }
  //
  // Cas 2/2 : Positionnement sur un relevé périodique (quel que soit le mode d'alimentation)
  //
  else
  {
    // Cas de liaisons à des éléments de référentiels
    if( $PAGE_RUBRIQUE_JOIN != 'user' )
    {
      // Récupération de la liste des items et des liaisons items / composantes
      $tab_join_item_rubrique_position = array();
      $tab_join_item_rubrique_elements = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_items_jointures_rubriques( $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $only_socle );
      if(!$DB_TAB) return FALSE;
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_join_item_rubrique_position[$DB_ROW['item_id']][$DB_ROW['rubrique_id_position']] = $DB_ROW['rubrique_id_position'];
        $tab_join_item_rubrique_elements[$DB_ROW['item_id']][$DB_ROW['rubrique_id_elements']] = $DB_ROW['rubrique_id_elements'];
      }
      $liste_item_id = implode(',',array_keys($tab_join_item_rubrique_position));
      // Il faut au moins connaître le coefficient et le mode de calcul associé à chaque item
      $tab_item_infos = array();
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , TRUE /*detail*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_item_infos[$DB_ROW['item_id']] = array(
          'item_nom'          => $DB_ROW['item_nom'],
          'item_coef'         => $DB_ROW['item_coef'],
          'calcul_methode'    => $DB_ROW['calcul_methode'],
          'calcul_limite'     => $DB_ROW['calcul_limite'],
          'calcul_retroactif' => $DB_ROW['calcul_retroactif'],
        );
      }
      // Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
      $tab_score_a_garder = array();
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items( $liste_eleve_id , $liste_item_id );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
      }
      $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
      $date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql',$annee_decalage);
          if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
      elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
      else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
      // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
      // Récupération au passage des profs associés aux saisies, mais uniquement sur la période en cours !!!
      $tab_eval = array();
      $tab_prof = array();
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ , FALSE /*first_order_by_date*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
        {
          $retro_item = $tab_item_infos[$DB_ROW['item_id']]['calcul_retroactif'];
          if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
          {
            // on enregistre les infos
            foreach($tab_join_item_rubrique_position[$DB_ROW['item_id']] as $rubrique_id)
            {
              if(!isset($tab_rubrique_a_eviter[$DB_ROW['eleve_id']][$rubrique_id]))
              {
                $tab_eval[$DB_ROW['eleve_id']][$rubrique_id][$DB_ROW['item_id']][] = array(
                  'note' => $DB_ROW['note'],
                  'date' => $DB_ROW['date'],
                );
                if($DB_ROW['date']>=$date_mysql_debut)
                {
                  $tab_prof[$rubrique_id][$DB_ROW['eleve_id']][$DB_ROW['prof_id']] = $DB_ROW['prof_id'];
                }
              }
            }
          }
        }
      }
      if(empty($tab_eval)) return FALSE;
    }
    // Cas de la récupération d'items évalués par des enseignants
    else
    {
      // Récupération de la liste des liaisons profs / rubriques
      $tab_join_prof_rubrique = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_jointures_rubriques_référentiels( $PAGE_RUBRIQUE_TYPE );
      if(empty($DB_TAB)) return FALSE;
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_prof_id = explode(',',$DB_ROW['listing_elements']);
        foreach($tab_prof_id as $prof_id)
        {
          $tab_join_prof_rubrique[$prof_id][$DB_ROW['livret_rubrique_ou_matiere_id']] = $DB_ROW['livret_rubrique_ou_matiere_id'];
        }
      }
      $liste_prof_id = implode(',',array_keys($tab_join_prof_rubrique));
      // Récupération de la liste des items évalués par ces enseignants sur la période ; au passage on récupère le coefficient et le mode de calcul associé à chaque item
      $tab_join_item_prof = array();
      $tab_item_infos = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_items_profs( $liste_eleve_id , $liste_prof_id , $only_socle , $date_mysql_debut , $date_mysql_fin );
      if(!$DB_TAB) return FALSE;
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_join_item_prof[$DB_ROW['item_id']][$DB_ROW['prof_id']] = $DB_ROW['prof_id'];
        $tab_item_infos[$DB_ROW['item_id']] = array(
          'item_nom'          => $DB_ROW['item_nom'],
          'item_coef'         => $DB_ROW['item_coef'],
          'calcul_methode'    => $DB_ROW['calcul_methode'],
          'calcul_limite'     => $DB_ROW['calcul_limite'],
          'calcul_retroactif' => $DB_ROW['calcul_retroactif'],
        );
      }
      $liste_item_id = implode(',',array_keys($tab_item_infos));
      // Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
      // Remarque : la requête suivante n'élimine cependant pas toutes les possibilités car on ne vérifie pas les enseignants...
      $tab_score_a_garder = array();
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items( $liste_eleve_id , $liste_item_id );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
      }
      $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
      $date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql',$annee_decalage);
          if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
      elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
      else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
      // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
      // Récupération au passage des profs associés aux saisies, mais uniquement sur la période en cours !!!
      $tab_eval = array();
      $tab_prof = array();
      $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ , FALSE /*first_order_by_date*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        if( $tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] && isset($tab_join_item_prof[$DB_ROW['item_id']][$DB_ROW['prof_id']]) )
        {
          $retro_item = $tab_item_infos[$DB_ROW['item_id']]['calcul_retroactif'];
          if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
          {
            // on enregistre les infos
            foreach($tab_join_prof_rubrique[$DB_ROW['prof_id']] as $rubrique_id)
            {
              if(!isset($tab_rubrique_a_eviter[$DB_ROW['eleve_id']][$rubrique_id]))
              {
                $tab_eval[$DB_ROW['eleve_id']][$rubrique_id][$DB_ROW['item_id']][] = array(
                  'note' => $DB_ROW['note'],
                  'date' => $DB_ROW['date'],
                );
                if($DB_ROW['date']>=$date_mysql_debut)
                {
                  $tab_prof[$rubrique_id][$DB_ROW['eleve_id']][$DB_ROW['prof_id']] = $DB_ROW['prof_id'];
                }
              }
            }
          }
        }
      }
      if(empty($tab_eval)) return FALSE;
    }
    // Déterminer les scores
    $tab_score_eleve_rubrique  = array();
    $tab_eleve_item_rubrique   = array(); // Pour plus tard (éléments de programme)
    $tab_eleve_item_detail     = array(); // Pour le détail des items évalués et leur score (sera associé aux ids de rubriques des éléments de programme) ; le 3ème indice [rubrique_id] est obligatoire dans le cas d'une liaison aux enseignants (pour un item travaillé par plusieurs enseignants)
    foreach($tab_eval as $eleve_id => $tab_eval_eleve)
    {
      foreach($tab_eval_eleve as $rubrique_id => $tab_eval_rubrique)
      {
        foreach($tab_eval_rubrique as $item_id => $tab_devoirs)
        {
          extract($tab_item_infos[$item_id]);  // $calcul_methode $calcul_limite $item_nom
          $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , $date_mysql_debut );
          $tab_score_eleve_rubrique[$eleve_id][$rubrique_id][$item_id] = $score;
          if( $PAGE_RUBRIQUE_JOIN != 'user' )
          {
            foreach($tab_join_item_rubrique_elements[$item_id] as $rubrique_id_elements)
            {
              $tab_eleve_item_rubrique[$eleve_id][$item_id][$rubrique_id_elements] = $rubrique_id_elements;
            }
          }
          else
          {
            foreach($tab_join_item_prof[$item_id] as $prof_id)
            {
              foreach($tab_join_prof_rubrique[$prof_id] as $rubrique_id_prof)
              {
                $tab_eleve_item_rubrique[$eleve_id][$item_id][$rubrique_id_prof] = $rubrique_id_prof; // est-ce que cela correspond à rubrique_id_elements ???
              }
            }
          }
          if($score!==FALSE)
          {
            $indice = OutilBilan::determiner_etat_acquisition( $score );
            $pourcentage = ($afficher_score) ? $score.'%' : '&nbsp;' ;
            $tab_eleve_item_detail[$eleve_id][$item_id][$rubrique_id] = '<div><span class="pourcentage A'.$indice.'">'.$pourcentage.'</span> '.html($item_nom).'</div>';
          }
          else
          {
            $tab_eleve_item_detail[$eleve_id][$item_id][$rubrique_id] = '<div><span class="pourcentage">-</span> '.html($item_nom).'</div>';
          }
        }
      }
    }
    // Calculer les moyennes des pourcentages
    $tab_positions_calculees = array();  // $tab_position_rubrique_eleve[$rubrique_id][$eleve_id]         Retenir la moyenne des scores d'acquisitions / rubrique / élève
    foreach($tab_score_eleve_rubrique as $eleve_id => $tab_score_rubrique)
    {
      foreach($tab_score_rubrique as $rubrique_id => $tab_score)
      {
        $tableau_score_filtre = array_filter($tab_score,'non_vide');
        $nb_scores = count( $tableau_score_filtre );
        // la moyenne peut être pondérée par des coefficients
        $somme_scores_ponderes = 0;
        $somme_coefs = 0;
        if($nb_scores)
        {
          foreach($tableau_score_filtre as $item_id => $item_score)
          {
            $somme_scores_ponderes += $item_score*$tab_item_infos[$item_id]['item_coef'];
            $somme_coefs += $tab_item_infos[$item_id]['item_coef'];
          }
        }
        // et voilà la moyenne des pourcentages d'acquisition
        $tab_positions_calculees[$rubrique_id][$eleve_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : NULL ;
      }
    }
  }
  // Comparer les positions calculées avec les positions enregistrées
  // Mémoriser le détail des acquisitions des items dans le cas d'un bilan de fin de cycle : les enregistrements sont associés aux id des positionnements
  $detail_determine = NULL;
  foreach($tab_positions_calculees as $rubrique_id => $tab_positions)
  {
    foreach($tab_positions as $eleve_id => $position_calculee)
    {
      $clef = $RUBRIQUE_TYPE.$rubrique_id.'eleve'.$eleve_id.'position';
      $detail_determine = ( ($PAGE_COLONNE=='maitrise') && !empty($tab_score_eleve_composante[$eleve_id][$rubrique_id]['detail']) ) ? implode('',$tab_score_eleve_composante[$eleve_id][$rubrique_id]['detail']) : NULL ;
      if(!isset($tab_donnees_livret[$clef]))
      {
        $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $RUBRIQUE_TYPE , $rubrique_id , 'eleve' , $eleve_id , 'position' , $position_calculee , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
        if( ($PAGE_COLONNE=='maitrise') && !is_null($detail_determine) )
        {
          DB_STRUCTURE_LIVRET::DB_ajouter_saisie_memo_detail( $livret_saisie_id , $detail_determine );
        }
        $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $position_calculee , 'origine' => 'calcul' , 'prof' => 0 , 'acquis_detail' => $detail_determine , 'listing_profs'=>'' , 'find' => TRUE );
      }
      // un test ci-dessous a pour but d'éviter une non mise en jour en cas de données issues d'un bulletin qui depuis a été abandonné
      // un test ci-dessous a pour but d'éviter une non mise en jour en cas de données issues d'un bulletin qui depuis a été déclaré sans moyenne (qui ne sont donc plus recalculées)
      // un test ci-dessous a pour but d'éviter une non mise en jour en cas de données supprimées issues d'un bulletin et que depuis la configuration est de recaluler un positionnement dans ce cas
      else
      {
        $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
        if( ($tab_donnees_livret[$clef]['valeur']!==$position_calculee) && ( ($tab_donnees_livret[$clef]['origine']=='calcul') || ( ($tab_donnees_livret[$clef]['origine']=='bulletin') && !$is_recup_bulletin ) || ( ($tab_donnees_livret[$clef]['origine']=='bulletin') && !$BULLETIN_MOYENNE_SCORES ) || ( ($tab_donnees_livret[$clef]['origine']=='bulletin') && is_null($tab_donnees_livret[$clef]['valeur']) && ($import_bulletin_notes=='reel') ) ) )
        {
          DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , 'position' /*$saisie_objet*/ , $position_calculee , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
          $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $position_calculee , 'origine' => 'calcul' , 'prof' => 0 , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
          // On ne supprime pas une position NULL pour conserver les liaisons des profs aux élèves notés ABS etc.
          /*
          if(is_null($position_calculee))
          {
            DB_STRUCTURE_LIVRET::DB_supprimer_saisie( $livret_saisie_id );
            unset($tab_donnees_livret[$clef]);
          }
          */
        }
        else
        {
          $tab_donnees_livret[$clef]['find'] = TRUE;
          $tab_positions_calculees[$rubrique_id][$eleve_id] = $tab_donnees_livret[$clef]['valeur']; // Si une moyenne élève a été modifiée manuellement, il faut en tenir compte pour la moyenne du regroupement.
        }
        // détail des acquisitions
        if( ($PAGE_COLONNE=='maitrise') && !is_null($detail_determine) && ( $tab_donnees_livret[$clef]['acquis_detail'] !== $detail_determine ) )
        {
          if(!is_null($tab_donnees_livret[$clef]['acquis_detail']))
          {
            DB_STRUCTURE_LIVRET::DB_modifier_saisie_memo_detail( $livret_saisie_id , $detail_determine );
          }
          else
          {
            DB_STRUCTURE_LIVRET::DB_ajouter_saisie_memo_detail( $livret_saisie_id , $detail_determine );
          }
        }
      }
      if($PAGE_COLONNE!='maitrise')
      {
        // Il arrive que le tableau suivant soit indéfini, peut-être à cause d'un élève changé de classe, je ne sais pas trop...
        $tab_prof_rubrique_eleve = isset($tab_prof[$rubrique_id][$eleve_id]) ? $tab_prof[$rubrique_id][$eleve_id] : array() ;
        if( $tab_donnees_livret[$clef]['listing_profs'] != implode(',',$tab_prof_rubrique_eleve) )
        {
          $tab_profs_livret = explode(',',$tab_donnees_livret[$clef]['listing_profs']);
          $tab_add = array_diff( $tab_prof_rubrique_eleve , $tab_profs_livret );
          $tab_del = array_diff( $tab_profs_livret , $tab_prof_rubrique_eleve );
          foreach($tab_add as $prof_id)
          {
            DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id );
          }
          foreach($tab_del as $prof_id)
          {
            DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id , TRUE /*delete*/ );
          }
        }
      }
    }
  }
  // On passe aux moyennes de classe, que l'on calcule dans tous les cas, car leur livret_saisie_id indique aussi l'accès des profs aux rubriques
  // Calculer les moyennes de classe
  $tab_moyennes_calculees = array();
  foreach($tab_positions_calculees as $rubrique_id => $tab_positions)
  {
    $somme   = array_sum($tab_positions);
    $nombre  = count( array_filter($tab_positions,'non_vide') );
    $moyenne = ($nombre) ? round($somme/$nombre,0) : NULL ;
    $tab_moyennes_calculees[$rubrique_id] = $moyenne;
  }
  // Comparer les moyennes calculées avec les moyennes enregistrées
  foreach($tab_moyennes_calculees as $rubrique_id => $moyenne_calculee)
  {
    $delete_saisie = FALSE;
    $clef = $RUBRIQUE_TYPE.$rubrique_id.'classe'.$classe_id.'position';
    if(!isset($tab_donnees_livret[$clef]))
    {
      $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $RUBRIQUE_TYPE , $rubrique_id , 'classe' , $classe_id , 'position' , $moyenne_calculee , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
      $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $moyenne_calculee , 'origine' => 'calcul' , 'prof' => 0 , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
    }
    else if($tab_donnees_livret[$clef]['valeur']!==$moyenne_calculee)
    {
      $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
      DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , 'position' /*$saisie_objet*/ , $moyenne_calculee , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
      $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $moyenne_calculee , 'origine' => 'calcul' , 'prof' => 0 , 'listing_profs'=>'' , 'find' => TRUE , 'acquis_detail' =>'' );
      // On ne supprime pas une moyenne NULL pour conserver les liaisons des profs aux élèves notés ABS etc.
      /*
      if(is_null($moyenne_calculee))
      {
        DB_STRUCTURE_LIVRET::DB_supprimer_saisie( $livret_saisie_id );
        unset($tab_donnees_livret[$clef]);
        $delete_saisie = TRUE;
      }
      */
    }
    else
    {
      $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
      $tab_donnees_livret[$clef]['find'] = TRUE;
    }
    if( !$delete_saisie && ($PAGE_COLONNE!='maitrise') )
    {
      $tab_prof_classe = array();
      if(!empty($tab_prof[$rubrique_id]))
      {
        foreach($tab_prof[$rubrique_id] as $eleve_id => $tab)
        {
          foreach($tab as $prof_id)
          {
            $tab_prof_classe[$prof_id] = $prof_id;
          }
        }
      }
      if( $tab_donnees_livret[$clef]['listing_profs'] != implode(',',$tab_prof_classe) )
      {
        $tab_profs_livret = explode(',',$tab_donnees_livret[$clef]['listing_profs']);
        $tab_add = array_diff( $tab_prof_classe , $tab_profs_livret );
        $tab_del = array_diff( $tab_profs_livret , $tab_prof_classe );
        foreach($tab_add as $prof_id)
        {
          DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id );
        }
        foreach($tab_del as $prof_id)
        {
          DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $livret_saisie_id , $prof_id , TRUE /*delete*/ );
        }
      }
    }
  }
  //
  // Déterminer les principaux éléments du programme travaillés durant la période
  // Mémoriser le détail des acquisitions des items dans le cas d'un bilan périodique : les enregistrements sont associés aux id des éléments travaillés
  //
  if( ($PAGE_COLONNE!='maitrise') && $liste_item_id )
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_elements_programme( $liste_eleve_id , $liste_item_id , $date_mysql_debut , $date_mysql_fin );
    $tab_eleve_rubrique_element = array();
    $tab_eleve_rubrique_detail = array();
    foreach($DB_TAB as $DB_ROW)
    {
      if(isset($tab_eleve_item_rubrique[$DB_ROW['eleve_id']][$DB_ROW['item_id']]))
      {
        $synthese_nom = $DB_ROW[$DB_ROW['mode_livret'].'_nom'];
        foreach($tab_eleve_item_rubrique[$DB_ROW['eleve_id']][$DB_ROW['item_id']] as $rubrique_id)
        {
          if(!isset($tab_rubrique_a_eviter[$DB_ROW['eleve_id']][$rubrique_id]))
          {
            if(!isset($tab_eleve_rubrique_element[$DB_ROW['eleve_id']][$rubrique_id][$synthese_nom]))
            {
              $tab_eleve_rubrique_element[$DB_ROW['eleve_id']][$rubrique_id][$synthese_nom] = $DB_ROW['eval_nb'];
            }
            else
            {
              $tab_eleve_rubrique_element[$DB_ROW['eleve_id']][$rubrique_id][$synthese_nom] += $DB_ROW['eval_nb'];
            }
            $tab_eleve_rubrique_detail[$DB_ROW['eleve_id']][$rubrique_id][] = $tab_eleve_item_detail[$DB_ROW['eleve_id']][$DB_ROW['item_id']][$rubrique_id];
          }
        }
      }
    }
    $tab_memo_rubriques_used = array();
    foreach($tab_eleve_rubrique_element as $eleve_id => $tab_rubrique)
    {
      foreach($tab_rubrique as $rubrique_id => $tab_synthese)
      {
        arsort($tab_synthese,SORT_NUMERIC);
        $tab_eleve_rubrique_element[$eleve_id][$rubrique_id] = json_encode($tab_synthese);
        $tab_memo_rubriques_used[$rubrique_id] = $rubrique_id;
      }
    }
    // Comparer les éléments déterminés avec les éléments enregistrés
    foreach($tab_eleve_rubrique_element as $eleve_id => $tab_rubrique)
    {
      foreach($tab_rubrique as $rubrique_id => $element_determine)
      {
        $clef = $RUBRIQUE_TYPE.$rubrique_id.'eleve'.$eleve_id.'elements';
        $detail_determine = implode('',$tab_eleve_rubrique_detail[$eleve_id][$rubrique_id]);
        if(!isset($tab_donnees_livret[$clef]))
        {
          $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $RUBRIQUE_TYPE , $rubrique_id , 'eleve' , $eleve_id , 'elements' , $element_determine , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
          DB_STRUCTURE_LIVRET::DB_ajouter_saisie_memo_detail( $livret_saisie_id , $detail_determine );
          $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $element_determine , 'origine' => 'calcul' , 'prof' => 0 , 'acquis_detail' => $detail_determine , 'find' => TRUE );
        }
        else
        {
          $livret_saisie_id = $tab_donnees_livret[$clef]['id'];
          if( ($tab_donnees_livret[$clef]['valeur']!==$element_determine) && ($tab_donnees_livret[$clef]['origine']=='calcul') )
          {
            DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , 'elements' /*$saisie_objet*/ , $element_determine , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
            $tab_donnees_livret[$clef] = array( 'id' => $livret_saisie_id , 'valeur' => $element_determine , 'origine' => 'calcul' , 'prof' => 0 , 'acquis_detail' => $detail_determine , 'find' => TRUE );
          }
          else
          {
            $tab_donnees_livret[$clef]['find'] = TRUE;
          }
          // détail des acquisitions
          if( $tab_donnees_livret[$clef]['acquis_detail'] !== $detail_determine )
          {
            DB_STRUCTURE_LIVRET::DB_modifier_saisie_memo_detail( $livret_saisie_id , $detail_determine );
          }
          // On ne supprime pas des éléments NULL pour conserver le détail des items notés ABS etc.
          /*
          if(is_null($element_determine))
          {
            DB_STRUCTURE_LIVRET::DB_supprimer_saisie( $livret_saisie_id );
            unset($tab_donnees_livret[$clef]);
          }
          */
        }
      }
    }
    // Initialiser éventuellement les éléments à appliquer sur une classe (requis pour l'affichage des sous-rubriques au 1er degré)
    foreach($tab_memo_rubriques_used as $rubrique_id)
    {
      $clef = 'eval'.$rubrique_id.'classe'.$classe_id.'elements';
      if(!isset($tab_donnees_livret[$clef]))
      {
        $livret_saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $RUBRIQUE_TYPE , $rubrique_id , 'classe' , $classe_id , 'elements' , NULL , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
      }
    }
  }
  // Il peut aussi falloir supprimer des données calculées ou imposées précédemment mais qui n'ont plus lieu d'être car les notes ont été supprimées, ou les items déplacés, ou supprimés depuis...
  // Pour les appréciations, on les laisse au cas où...
  // Pour les éléments de programme travaillés il faut aussi conserver ceux imposés à une classe...
  if(!empty($tab_donnees_livret))
  {
    foreach($tab_donnees_livret as $clef => $tab)
    {
      if( !isset($tab['find']) && ( (substr($clef,-8)=='position') || ( (substr($clef,-8)=='elements') && !strpos($clef,'classe') ) ) )
      {
        DB_STRUCTURE_LIVRET::DB_supprimer_saisie( $tab['id'] );
      }
    }
  }
}

/**
 * Pour une page de livret d'une période / d'un élève / d'une rubrique donnée, calculer et mettre à jour uune appréciation / un positionnement / des éléments du programme (effacé ou figé).
 * 
 * @param int    $livret_saisie_id
 * @param string $PAGE_REF
 * @param string $PAGE_PERIODICITE
 * @param int    $JOINTURE_PERIODE
 * @param string $PAGE_RUBRIQUE_TYPE
 * @param string $PAGE_RUBRIQUE_JOIN
 * @param string $PAGE_COLONNE
 * @param int    $periode_id
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param string $rubrique_type
 * @param int    $rubrique_id
 * @param string $cible_nature
 * @param int    $classe_id
 * @param int    $groupe_id
 * @param int    $eleve_id
 * @param string $saisie_objet
 * @param string $import_bulletin_notes   tous|reel|rien
 * @param string $only_socle   0|1
 * @param string $retroactif   oui|non|annuel|auto
 * @return array   [ (bool) $reussite , (string) $origine , (mixed) $contenu ]
 */
function calculer_et_enregistrer_donnee_eleve_rubrique_objet( $livret_saisie_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $rubrique_type , $rubrique_id , $cible_nature , $classe_id , $groupe_id , $eleve_id , $saisie_objet , $import_bulletin_notes , $only_socle , $retroactif )
{
  //
  // On commence par une recherche dans le bulletin correspondant (s'il existe, et correspondance de matière à matière)
  // Sauf pour des éléments de programme qui n'y figurent pas
  //
  if( (substr($PAGE_RUBRIQUE_TYPE,3)=='matiere') && ($PAGE_RUBRIQUE_JOIN=='matiere') && ($saisie_objet!='elements') )
  {
    $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,'bulletin');
    // Récupération de $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'], pas mis en session ici car seule variable utilisée ponctuellement
    $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'bulletin' /*bilan_type*/ , $DB_ROW['configuration_ref'] );
    $BULLETIN_MOYENNE_SCORES = $tab_configuration['moyenne_scores'];
    // un test ci-dessous a pour but d'éviter une récupération obsolète en cas de données issues d'un bulletin qui depuis a été abandonné
    // un test ci-dessous a pour but d'éviter une récupération obsolète en cas de données issues d'un bulletin qui depuis a été déclaré sans moyenne (qui ne sont donc plus recalculées)
    if( !empty($DB_ROW) && !in_array( $DB_ROW['officiel_bulletin'] , array('','1vide') ) && ( ($saisie_objet!='position') || $BULLETIN_MOYENNE_SCORES ) ) // "0absence" est enregistré comme une chaine vide en BDD
    {
      $bulletin_matiere_id = ($rubrique_type=='bilan') ? 0 : ( ($rubrique_type=='viesco') ? 54 : DB_STRUCTURE_LIVRET::DB_recuperer_correspondance_matiere_unique( $PAGE_RUBRIQUE_TYPE , $rubrique_id ) ) ;
      if(!is_null($bulletin_matiere_id))
      {
        $cible_id = ($eleve_id) ? $eleve_id : $classe_id ;
        $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisie_precise( 'bulletin' , $periode_id , $cible_id , $groupe_id , $bulletin_matiere_id , $cible_nature , $saisie_objet );
        $tmp_valeur = '';
        foreach($DB_TAB as $key => $DB_ROW)
        {
          $prof_id       = $DB_ROW['prof_id']; // toujours 0 pour une note dans les bulletins
          $saisie_valeur = ($saisie_objet=='appreciation') ? (string)$DB_ROW['saisie_valeur'] : ( ($DB_ROW['saisie_valeur']!==NULL) ? (float)$DB_ROW['saisie_valeur']*5 : NULL ) ; // Le bulletin enregistre sur 20, le livret enregistre sur 100.
          if( ($import_bulletin_notes=='tous') || ($saisie_objet=='appreciation') || ( ($import_bulletin_notes=='reel') && ($saisie_valeur!==NULL) ) )
          {
            if($prof_id)
            {
              // Pour gérer le pb des appéciations multiples à concaténer
              if($tmp_valeur)
              {
                $saisie_valeur = $tmp_valeur."\n".$saisie_valeur;
              }
              $tmp_valeur = $saisie_valeur;
            }
            DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id );
          }
          else
          {
            unset($DB_TAB[$key]);
          }
        }
        if(count($DB_TAB))
        {
          return array( TRUE , 'bulletin' , $saisie_valeur );
        }
      }
    }
  }
  //
  // On continue par une recherche dans le relevé d'évaluations correspondant (s'il existe, et uniquement pour une appréciation de synthèse générale)
  //
  if( ($rubrique_type=='bilan') && ($saisie_objet=='appreciation') )
  {
    $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,'releve');
    // un test ci-dessous a pour but d'éviter une récupération obsolète en cas de données issues d'un relevé d'évaluations qui depuis a été abandonné
    if( !empty($DB_ROW) && !in_array( $DB_ROW['officiel_releve'] , array('','1vide') ) ) // "0absence" est enregistré comme une chaine vide en BDD
    {
      $cible_id = ($eleve_id) ? $eleve_id : $classe_id ;
      $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisie_precise( 'releve' , $periode_id , $cible_id , $groupe_id , 0 /*rubrique_id*/ , $cible_nature , $saisie_objet );
      $tmp_valeur = '';
      foreach($DB_TAB as $key => $DB_ROW)
      {
        $prof_id       = $DB_ROW['prof_id']; // forcément renseigné pour une appréciation
        $saisie_valeur = (string)$DB_ROW['saisie_valeur'];
        // Pour gérer le pb des appéciations multiples à concaténer
        if($tmp_valeur)
        {
          $saisie_valeur = $tmp_valeur."\n".$saisie_valeur;
        }
        $tmp_valeur = $saisie_valeur;
        DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $saisie_valeur , 'bulletin' /*saisie_origine*/ , $prof_id ); // on laisse "bulletin" comme origine...
      }
      if(count($DB_TAB))
      {
        return array( TRUE , 'bulletin' , $saisie_valeur );
      }
    }
  }
  // Pour une recherche d'appréciation, si on n'a rien trouvé dans le bulletin alors ce n'est pas la peine de continuer
  if($saisie_objet=='appreciation')
  {
    return array( FALSE , NULL , "Pas d'appréciation trouvée dans un bulletin correspondant." );
  }
  // Reste donc le cas d'un positionnement à recalculer ou d'éléments de programme à déterminer (pour un élève)
  // Dans les deux cas il faut récupérer les items évalués
  //
  // Cas 1/2 : Positionnement du socle en fin de cycle
  //
  if($PAGE_COLONNE=='maitrise') // Ici on a forcément ($saisie_objet=='position')
  {
    $cycle_id = $PAGE_RUBRIQUE_TYPE{1};
    // Récupération de la liste des items et des liaisons items / composantes
    $tab_join_item_socle = array();
    $domaine_id    = ($rubrique_id<15) ? NULL : $rubrique_id/10 ;
    $composante_id = ($rubrique_id<15) ? $rubrique_id : NULL ;
    $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_associations_items_composantes( $cycle_id , FALSE /*with_detail*/ , $domaine_id , $composante_id );
    if(!$DB_TAB) return array( FALSE , NULL , "Pas de données trouvées pour estimer ce positionnement." );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_join_item_socle[$DB_ROW['item_id']] = $DB_ROW['item_id'];
    }
    $liste_item_id = implode(',',array_keys($tab_join_item_socle));
    // Il faut au moins connaître le mode de calcul associé à chaque item
    $tab_item_infos = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , FALSE /*detail*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_item_infos[$DB_ROW['item_id']] = array(
        'calcul_methode' => $DB_ROW['calcul_methode'],
        'calcul_limite'  => $DB_ROW['calcul_limite'],
      );
    }
    // Récupération de la liste des résultats
    $tab_eval = array();
    $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $eleve_id , $liste_item_id , -1 /*matiere_id*/ , FALSE /*date_mysql_debut*/ , FALSE /*date_mysql_fin*/ , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , TRUE /*onlynote*/ , FALSE /*first_order_by_date*/ );
    if(!$DB_TAB) return array( FALSE , NULL , "Pas de données trouvées pour estimer ce positionnement." );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eval[$DB_ROW['item_id']][]['note'] = $DB_ROW['note']; // pas besoin de la date ici
    }
    // Déterminer les scores, et donc états d'acquisition
    $tab_score = array_fill_keys( array_keys($_SESSION['ACQUIS']) , 0 ) + array('nb'=>0) ;
    foreach($tab_eval as $item_id => $tab_devoirs)
    {
      extract($tab_item_infos[$item_id]);  // $calcul_methode $calcul_limite
      // calcul du bilan de l'item
      $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , NULL /*date_mysql_debut*/ );
      if($score!==FALSE)
      {
        // on détermine si il est acquis ou pas
        $indice = OutilBilan::determiner_etat_acquisition( $score );
        // on enregistre les infos
        $tab_score[$indice]++;
        $tab_score['nb']++;
      }
    }
    // Calculer le pourcentages d'acquisition à partir du nombre d'items de chaque état
    if(!$tab_score['nb']) return array( FALSE , NULL , "Pas de données trouvées pour estimer ce positionnement." );
    $position_calculee = OutilBilan::calculer_pourcentage_acquisition_items( $tab_score , $tab_score['nb'] );
  }
  //
  // Cas 2/2 : Positionnement sur un relevé périodique (quels que soient le mode d'alimentation), ou éléments de programme travaillés
  //
  else
  {
    $texte_objet = ($saisie_objet=='position') ? "Pas de données trouvées pour estimer ce positionnement." : "Pas d'éléments de programme travaillés trouvé." ;
    // Cas de liaisons à des éléments de référentiels
    if( $PAGE_RUBRIQUE_JOIN != 'user' )
    {
      // Récupération de la liste des items et des liaisons items / composantes
      $tab_join_item_rubrique = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_items_jointures_rubriques( $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $only_socle , $rubrique_id );
      if(!$DB_TAB) return array( FALSE , NULL , $texte_objet );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_join_item_rubrique[$DB_ROW['item_id']] = $DB_ROW['item_id'];
      }
      $liste_item_id = implode(',',array_keys($tab_join_item_rubrique));
      if($saisie_objet=='position')
      {
        // Il faut au moins connaître le coefficient et le mode de calcul associé à chaque item
        $tab_item_infos = array();
        $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_infos_items( $liste_item_id , TRUE /*detail*/ );
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_item_infos[$DB_ROW['item_id']] = array(
            'item_coef'         => $DB_ROW['item_coef'],
            'calcul_methode'    => $DB_ROW['calcul_methode'],
            'calcul_limite'     => $DB_ROW['calcul_limite'],
            'calcul_retroactif' => $DB_ROW['calcul_retroactif'],
          );
        }
        $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
        $date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql',$annee_decalage);
            if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
        elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
        else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
        // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
        $tab_eval = array();
        $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ , FALSE /*first_order_by_date*/ );
        foreach($DB_TAB as $DB_ROW)
        {
          $retro_item = $tab_item_infos[$DB_ROW['item_id']]['calcul_retroactif'];
          if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
          {
            $tab_eval[$DB_ROW['item_id']][] = array(
              'note' => $DB_ROW['note'],
              'date' => $DB_ROW['date'],
            );
          }
        }
        if(empty($tab_eval)) return array( FALSE , NULL , $texte_objet );
      }
    }
    // Cas de la récupération d'items évalués par des enseignants
    else
    {
      // Récupération de la liste des liaisons profs / rubriques
      $tab_join_prof_rubrique = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_jointures_rubriques_référentiels( $PAGE_RUBRIQUE_TYPE , $rubrique_id );
      if(empty($DB_TAB)) return array( FALSE , NULL , $texte_objet );
      $liste_prof_id = $DB_TAB[0]['listing_elements'];
      if(empty($liste_prof_id)) return array( FALSE , NULL , $texte_objet );
      // Récupération de la liste des items évalués par ces enseignants sur la période ; au passage on récupère le coefficient et le mode de calcul associé à chaque item
      $tab_join_item_prof = array();
      $tab_item_infos = array();
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_items_profs( $eleve_id , $liste_prof_id , $only_socle , $date_mysql_debut , $date_mysql_fin );
      if(!$DB_TAB) return array( FALSE , NULL , $texte_objet );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_join_item_prof[$DB_ROW['item_id']][$DB_ROW['prof_id']] = $DB_ROW['prof_id'];
        $tab_item_infos[$DB_ROW['item_id']] = array(
          'item_coef'         => $DB_ROW['item_coef'],
          'calcul_methode'    => $DB_ROW['calcul_methode'],
          'calcul_limite'     => $DB_ROW['calcul_limite'],
          'calcul_retroactif' => $DB_ROW['calcul_retroactif'],
        );
      }
      $liste_item_id = implode(',',array_keys($tab_item_infos));
      if($saisie_objet=='position')
      {
        $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
        $date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql',$annee_decalage);
            if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
        elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
        else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
        // Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
        $tab_eval = array();
        $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $eleve_id , $liste_item_id , -1 /*matiere_id*/ , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ , FALSE /*first_order_by_date*/ );
        foreach($DB_TAB as $DB_ROW)
        {
          if( isset($tab_join_item_prof[$DB_ROW['item_id']][$DB_ROW['prof_id']]) )
          {
            $retro_item = $tab_item_infos[$DB_ROW['item_id']]['calcul_retroactif'];
            if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
            {
              $tab_eval[$DB_ROW['item_id']][] = array(
                'note' => $DB_ROW['note'],
                'date' => $DB_ROW['date'],
              );
            }
          }
        }
        if(empty($tab_eval)) return array( FALSE , NULL , $texte_objet );
      }
    }
    if($saisie_objet=='position')
    {
      // Déterminer les scores
      $tab_score = array();
      foreach($tab_eval as $item_id => $tab_devoirs)
      {
        extract($tab_item_infos[$item_id]);  // $calcul_methode $calcul_limite
        $tab_score[$item_id] = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , $date_mysql_debut );
      }
      // calculer les moyennes des pourcentages
      $tableau_score_filtre = array_filter($tab_score,'non_vide');
      $nb_scores = count( $tableau_score_filtre );
      // la moyenne peut être pondérée par des coefficients
      $somme_scores_ponderes = 0;
      $somme_coefs = 0;
      if($nb_scores)
      {
        foreach($tableau_score_filtre as $item_id => $item_score)
        {
          $somme_scores_ponderes += $item_score*$tab_item_infos[$item_id]['item_coef'];
          $somme_coefs += $tab_item_infos[$item_id]['item_coef'];
        }
      }
      // et voilà la moyenne des pourcentages d'acquisition
      if(!$somme_coefs) return array( FALSE , NULL , $texte_objet );
      $position_calculee = round($somme_scores_ponderes/$somme_coefs,0);
    }
  }
  if($saisie_objet=='position')
  {
    // enregistrement et retour
    DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $position_calculee , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
    return array( TRUE , 'calcul' , $position_calculee );
  }
  else if($saisie_objet=='elements')
  {
    //
    // Déterminer les principaux éléments du programme travaillés durant la période
    //
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_elements_programme( $eleve_id , $liste_item_id , $date_mysql_debut , $date_mysql_fin );
    $tab_synthese = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $synthese_nom = $DB_ROW[$DB_ROW['mode_livret'].'_nom'];
      if(!isset($tab_synthese[$synthese_nom]))
      {
        $tab_synthese[$synthese_nom] = $DB_ROW['eval_nb'];
      }
      else
      {
        $tab_synthese[$synthese_nom] += $DB_ROW['eval_nb'];
      }
    }
    arsort($tab_synthese,SORT_NUMERIC);
    $element_determine = json_encode($tab_synthese);
    // enregistrement et retour
    DB_STRUCTURE_LIVRET::DB_modifier_saisie( $livret_saisie_id , $saisie_objet , $element_determine , 'calcul' /*saisie_origine*/ , 0 /*prof_id*/ );
    return array( TRUE , 'calcul' , $element_determine );
  }
}

/**
 * Retourner le texte indiquant les absences et retard à partir des données transmises.
 * 
 * @param array  $tab_assiduite
 * @return string
 */
function texte_ligne_assiduite($tab_assiduite)
{
  $intro = 'Assiduité et ponctualité : ';
  extract($tab_assiduite); // $absence $absence_nj $retard $retard_nj
  $nb_absence_nj = is_null($absence_nj) ? NULL : (int)$absence_nj ;
  $nb_retard_nj  = is_null($retard_nj)  ? NULL : (int)$retard_nj ;
  // on corrige au passage une éventuelle anomalie de nb non justifié > nb total
  $nb_absence    = is_null($absence)    ? NULL : max( (int)$absence , (int)$nb_absence_nj );
  $nb_retard     = is_null($retard)     ? NULL : max( (int)$retard , (int)$retard_nj );
  // Quelques cas particuliers
  if( ($nb_absence===NULL) && ($nb_retard===NULL) )
  {
    return $intro.'sans objet.';
  }
  if( ($nb_absence===0) && ($nb_retard===0) )
  {
    return $intro.'aucune absence ni retard.';
  }
  if( ($nb_absence===0) && ($nb_retard===NULL) )
  {
    return $intro.'aucune absence.';
  }
  if( ($nb_absence===NULL) && ($nb_retard===0) )
  {
    return $intro.'aucun retard.';
  }
  // Les absences
  if($nb_absence===NULL)
  {
    $txt_absences = '';
  }
  else if($nb_absence===0)
  {
    $txt_absences = 'aucune absence';
  }
  else
  {
    $s = ($nb_absence>1) ? 's' : '' ;
    $txt_absences = $nb_absence.' demi-journée'.$s.' d\'absence';
    if($nb_absence_nj===NULL)
    {
      $txt_absences .= '' ;
    }
    else if($nb_absence_nj===0)
    {
      $txt_absences .= ($s) ? ', toutes justifiées' : ', justifiée' ;
    }
    else if($nb_absence_nj==$nb_absence)
    {
      $txt_absences .= ($s) ? ', dont aucune justifiée' : ', non justifiée' ;
    }
    else
    {
      $s = ($nb_absence_nj>1) ? 's' : '' ;
      $txt_absences .= ', dont '.$nb_absence_nj.' non justifiée'.$s;
    }
  }
  // Les retards
  if($nb_retard===NULL)
  {
    $txt_retards = '';
  }
  else if($nb_retard===0)
  {
    $txt_retards = 'aucun retard';
  }
  else
  {
    $s = ($nb_retard>1) ? 's' : '' ;
    $txt_retards = $nb_retard.' retard'.$s;
    if($nb_retard_nj===NULL)
    {
      $txt_retards .= '' ;
    }
    else if($nb_retard_nj===0)
    {
      $txt_retards .= ($s) ? ', tous justifiés' : ', justifié' ;
    }
    else if($nb_retard_nj==$nb_retard)
    {
      $txt_retards .= ($s) ? ', dont aucun justifié' : ', non justifié' ;
    }
    else
    {
      $s = ($nb_retard_nj>1) ? 's' : '' ;
      $txt_retards .= ', dont '.$nb_retard_nj.' non justifié'.$s;
    }
  }
  // On assemble
  $txt_absences_et_retards = ( $txt_absences && $txt_retards ) ? $txt_absences.', et '.$txt_retards : $txt_absences.$txt_retards;
  return $intro.$txt_absences_et_retards.'.';
}

/**
 * Retourner le texte introductif d'une zone du livret
 * 
 * @param string $rubrique_type
 * @param int    $eleve_id | $cycle_id
 * @param string $bilan_type_etabl
 * @return string
 */
function rubrique_texte_intro( $rubrique_type , $for_id=0 , $bilan_type_etabl='' )
{
  switch($rubrique_type)
  {
    case 'socle' :
      return Lang::_("Maîtrise des composantes du socle en fin de cycle").' '.$for_id;
    case 'eval' :
      return ($for_id) ? Lang::_("Suivi des acquis scolaires de l’élève") : Lang::_("Suivi des acquis de la classe") ;
    case 'cycle1' :
      return Lang::_("Synthèse des acquis scolaires à la fin de l’école maternelle");
    case 'epi' :
      return ($for_id) ? Lang::_("Implication de l’élève").' : ' : Lang::_("Projet réalisé").' : ' ;
    case 'ap' :
      return ($for_id) ? Lang::_("Implication de l’élève").' : ' : Lang::_("Action réalisée").' : ' ;
    case 'parcours' :
      return ($for_id) ? Lang::_("Implication de l’élève").' : ' : Lang::_("Projet mis en oeuvre").' : ' ;
    case 'bilan' :
      if($bilan_type_etabl=='college')
      {
        return ($for_id) ? Lang::_("Synthèse de l’évolution des acquis scolaires et conseils pour progresser").' : ' : Lang::_("Synthèse de l’évolution des acquis de la classe").' : ' ;
      }
      else
      {
        return ($for_id) ? Lang::_("Appréciation générale sur la progression de l’élève").' : ' : Lang::_("Appréciation générale sur la progression de la classe").' : ' ;
      }
    case 'viesco' :
      return Lang::_("Vie scolaire (assiduité, ponctualité ; respect du règlement ; participation à la vie de l’établissement)").' : ';
  }
}

/**
 * Retourner le nombre de lignes requises ou le contenu affichable pour des éléments de programme travaillés
 * 
 * @param string $elements_json
 * @param int    $nb_caract_max_par_colonne
 * @param string $objet_retour   'nombre_lignes' | 'html' | 'pdf'
 * @return int|string
 */
function elements_programme_extraction( $elements_json , $nb_caract_max_par_colonne , $objet_retour )
{
  $nb_elements_mini = ($objet_retour=='html') ? 10 : 2 ;
  $nb_lignes_maxi   = ($objet_retour=='html') ? 30 : 6 ;
  $nb_elements = 0;
  $nb_lignes_elements = 0;
  $tab_elements = array();
  $tab_valeurs = json_decode($elements_json, TRUE);
  foreach($tab_valeurs as $texte => $nb_used)
  {
    $nb_lignes_element = ceil(strlen($texte)/$nb_caract_max_par_colonne);
    if( ($nb_lignes_elements+1 >= $nb_lignes_maxi) && ($nb_elements >= $nb_elements_mini) && ($nb_lignes_element>3) )
    {
      break;
    }
    if($objet_retour=='html')
    {
      $tab_elements[] = '<div><span class="notnow">[#'.$nb_used.']</span> '.html($texte).'</div>';
    }
    elseif($objet_retour=='pdf')
    {
      $tab_elements[] = '- '.$texte;
    }
    $nb_elements++;
    $nb_lignes_elements += min( 3 , $nb_lignes_element );
    if( ($nb_lignes_elements>=$nb_lignes_maxi) && ($nb_elements>$nb_elements_mini) )
    {
      break;
    }
  }
  if($objet_retour=='nombre_lignes')
  {
    return max( 1 , $nb_lignes_elements );
  }
  elseif($objet_retour=='html')
  {
    return implode('',$tab_elements);
  }
  elseif($objet_retour=='pdf')
  {
    return implode("\n",$tab_elements);
  }
}

function echo_origine($origine)
{
  return ($origine) ? '<span title="'.$origine.'"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" />Origine</span>' : '' ;
}

?>
