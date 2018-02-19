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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On vérifie les paramètres principaux et on récupère les infos associées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !$classe_id )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

$DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_page_groupe_info( $classe_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE );

if(empty($DB_ROW))
{
  Json::end( FALSE , 'Association classe / livret introuvable !' );
}

$BILAN_ETAT          = $DB_ROW['jointure_etat'];
$PAGE_MOMENT         = $DB_ROW['livret_page_moment'];
$PAGE_TITRE_CLASSE   = $DB_ROW['livret_page_titre_classe'];
$PAGE_RESUME         = $DB_ROW['livret_page_resume'];
$PAGE_RUBRIQUE_TYPE  = $DB_ROW['livret_page_rubrique_type'];
$PAGE_RUBRIQUE_JOIN  = $DB_ROW['livret_page_rubrique_join'];
$PAGE_COLONNE        = $DB_ROW['livret_page_colonne'];
$PAGE_MOYENNE_CLASSE = $DB_ROW['livret_page_moyenne_classe'];
$PAGE_EPI            = $DB_ROW['livret_page_epi'];
$PAGE_AP             = $DB_ROW['livret_page_ap'];
$PAGE_PARCOURS       = $DB_ROW['livret_page_parcours'];
$PAGE_VIE_SCOLAIRE   = $DB_ROW['livret_page_vie_scolaire'];
$classe_nom          = $DB_ROW['groupe_nom'];
$DATE_VERROU         = is_null($DB_ROW['jointure_date_verrou']) ? TODAY_FR : To::datetime_mysql_to_french( $DB_ROW['jointure_date_verrou'] , FALSE /*return_time*/ ) ;
$BILAN_TYPE_ETABL    = in_array($PAGE_RUBRIQUE_TYPE,array('c3_matiere','c4_matiere','c3_socle','c4_socle')) ? 'college' : 'ecole' ;
$CONFIGURATION_REF   = $DB_ROW['configuration_ref'];

// Récupérer, si besoin, les paramètres du bilan (on ne force pas l'actualisation si déjà en session car on est déjà en train de consulter le bilan).
// La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
if( !isset($_SESSION['OFFICIEL']['LIVRET_CONFIG_REF']) || ($_SESSION['OFFICIEL']['LIVRET_CONFIG_REF']!=$CONFIGURATION_REF) )
{
  $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'livret' , $CONFIGURATION_REF );
  foreach($tab_configuration as $key => $val)
  {
    $_SESSION['OFFICIEL']['LIVRET_'.Clean::upper($key)] = $val;
  }
  $_SESSION['OFFICIEL']['LIVRET_CONFIG_REF'] = $CONFIGURATION_REF;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les infos sur les seuils enregistrés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($PAGE_PERIODICITE=='periode') && !in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos( $PAGE_REF , $PAGE_COLONNE );
  foreach($DB_TAB as $DB_ROW)
  {
    $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
    $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
    $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
    $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
  }
}
elseif($PAGE_PERIODICITE=='cycle')
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos($PAGE_REF);
  foreach($DB_TAB as $DB_ROW)
  {
    $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
    $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
    $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
    $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des élèves (on pourrait se faire transmettre les ids par l'envoi ajax, mais on a aussi besoin des noms-prénoms).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;
$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                             : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
if(empty($DB_TAB))
{
  $groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
}
$tab_eleve_id    = array( 0 => array( 'eleve_nom' => $classe_nom ,  'eleve_prenom' => '' ) );
$tab_saisie_init = array( 0 => array( 'note'=>NULL , 'appreciation'=>'' ) );
foreach($DB_TAB as $DB_ROW)
{
  $tab_eleve_id[$DB_ROW['user_id']] = array( 'eleve_nom' => $DB_ROW['user_nom'] ,  'eleve_prenom' => $DB_ROW['user_prenom'] );
  $tab_saisie_init[$DB_ROW['user_id']] = array( 'note'=>NULL , 'appreciation'=>'' );
}
$liste_eleve_id = implode(',',array_keys($tab_eleve_id));

// Fonctions utilisées.

function suppression_sauts_de_ligne($texte)
{
  $tab_bad = Clean::tab_crlf();
  $tab_bon = ' ';
  return str_replace( $tab_bad , $tab_bon , $texte );
}

function nombre_de_ligne_supplémentaires($texte)
{
  return max( 2 , ceil(mb_strlen($texte)/125) ) - 2;
}

function nombre_de_lignes($texte)
{
  return ceil(mb_strlen($texte)/125);
}

function recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF )
{
  global $tab_rubrique;
  if(isset($tab_rubrique['eval']))
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      if(isset($tab_rubrique['eval'][$DB_ROW['livret_rubrique_id']]))
      {
        $tab_rubrique['eval'][$DB_ROW['livret_rubrique_id']] = $DB_ROW['rubrique'];
      }
    }
  }
  if(isset($tab_rubrique['epi']))
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi( $classe_id , $PAGE_REF );
    foreach($DB_TAB as $DB_ROW)
    {
      if(isset($tab_rubrique['epi'][$DB_ROW['livret_epi_id']]))
      {
        $tab_rubrique['epi'][$DB_ROW['livret_epi_id']] = 'EPI '.$DB_ROW['livret_epi_titre'];
      }
    }
  }
  if(isset($tab_rubrique['ap']))
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_ap( $classe_id , $PAGE_REF );
    foreach($DB_TAB as $DB_ROW)
    {
      if(isset($tab_rubrique['ap'][$DB_ROW['livret_ap_id']]))
      {
        $tab_rubrique['ap'][$DB_ROW['livret_ap_id']] = 'AP '.$DB_ROW['livret_ap_titre'];
      }
    }
  }
  if(isset($tab_rubrique['parcours']))
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours( '' /*parcours_code*/ ,  $classe_id , $PAGE_REF );
    foreach($DB_TAB as $DB_ROW)
    {
      if(isset($tab_rubrique['parcours'][$DB_ROW['livret_parcours_id']]))
      {
        $tab_rubrique['parcours'][$DB_ROW['livret_parcours_id']] = $DB_ROW['livret_parcours_type_nom'];
      }
    }
  }
}

function recuperer_prof_nom( $user_id )
{
  global $tab_prof;
  if(empty($tab_prof))
  {
    $tab_profils_types = array('professeur','directeur');
    $listing_champs = 'user_id, user_genre, user_nom, user_prenom';
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , $listing_champs , FALSE /*with_classe*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_prof[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
  }
  return isset($tab_prof[$user_id]) ? $tab_prof[$user_id] : '?' ;
}

// Quelques autres variables utiles communes.

$nb_eleves = count($tab_eleve_id);
$prof_nom = ($action=='imprimer_donnees_eleves_prof') ? $_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'] : 'Équipe enseignante' ;
$tab_moyenne_exception_matieres = array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1/9 imprimer_donnees_eleves_prof : Mes appréciations pour chaque élève et le groupe classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_prof')
{
  // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour le prof concerné
  $DB_TAB = array_merge
  (
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id      , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ ) ,
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ )
  );
  // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par rubrique car on imprimera une page par rubrique avec tous les élèves de la classe
  $tab_saisie = array(
    'eval'     => NULL ,
    'epi'      => NULL ,
    'ap'       => NULL ,
    'parcours' => NULL ,
    'bilan'    => NULL ,
    'viesco'   => NULL ,
  );  // [rubrique_type][rubrique_id][eleve_id] => array(note,appreciation);
  $nb_lignes_supplémentaires = array(); // On compte 2 lignes par rubrique par élève, il peut falloir plus si l'appréciation est longue
  // La requête renvoie les appréciations du prof et les notes / éléments travaillés de toutes les rubriques.
  // Il ne faut prendre que les notes qui vont avec les appréciations, i.e. des rubriques du prof.
  // Ainsi, on commence dans une première boucle par lister les appréciations et les rubriques...
  $tab_rubrique = array();
  foreach($DB_TAB as $key => $DB_ROW)
  {
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      if(!isset($tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]))
      {
        $nb_lignes_supplémentaires[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = 0;
        // Prévoir une ligne pour la classe et une autre par élève même si rien n'est saisi.
        $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = $tab_saisie_init;
      }
      $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
      $tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? FALSE : 'Synthèse générale' ;
      $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['appreciation'] = suppression_sauts_de_ligne($DB_ROW['saisie_valeur']);
      $nb_lignes_supplémentaires[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] += nombre_de_ligne_supplémentaires($DB_ROW['saisie_valeur']);
      unset($DB_TAB[$key]);
    }
  }
  // ... puis dans une seconde on ajoute les seules notes à garder.
  foreach($DB_TAB as $DB_ROW)
  {
    if( ($DB_ROW['saisie_objet']=='position') && isset($tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]) )
    {
      $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
      if( ($DB_ROW['rubrique_id']) && ( $eleve_id || $PAGE_MOYENNE_CLASSE ) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) )
      {
        $pourcentage = $DB_ROW['saisie_valeur'] ;
        $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['note'] = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ) : '-' ;
      }
    }
  }
  $nb_rubriques = count($tab_rubrique);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune saisie trouvée pour aucun élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  foreach($tab_saisie as $rubrique_type => $tab_saisie_rubrique)
  {
    if(!is_null($tab_saisie_rubrique))
    {
      $with_moyenne = ($rubrique_type=='eval') ? TRUE : FALSE ;
      foreach($tab_saisie_rubrique as $rubrique_id => $tab)
      {
        if(isset($tab_rubrique[$rubrique_type][$rubrique_id]))
        {
          $rubrique_nom = $tab_rubrique[$rubrique_type][$rubrique_id];
          $archivage_tableau_PDF->appreciation_initialiser_eleves_prof( $nb_eleves , $nb_lignes_supplémentaires[$rubrique_type][$rubrique_id] , $with_moyenne );
          $archivage_tableau_PDF->appreciation_intitule( 'Livret scolaire - '.$classe_nom.' - '.$periode_nom.' - Appréciations de '.$prof_nom.' - '.$rubrique_nom );
          // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
          foreach($tab_eleve_id as $eleve_id => $tab_eleve)
          {
            extract($tab_eleve);  // $eleve_nom $eleve_prenom
            if(isset($tab[$eleve_id]))
            {
              extract($tab[$eleve_id]);  // $note $appreciation
              $archivage_tableau_PDF->appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , str_replace('.',',',$note) , $appreciation , $with_moyenne , 'livret' /*objet_document*/ );
            }
          }
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2/9 imprimer_donnees_eleves_collegues : Appréciations des collègues pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_collegues')
{
  // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par élève
  $tab_saisie   = array();  // [eleve_id][rubrique_type][rubrique_id] => array(note,appreciation);
  $tab_rubrique = array();
  $tab_prof     = array();
  $nb_lignes_rubriques = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
  foreach($DB_TAB as $DB_ROW)
  {
    if(!isset($tab_saisie[$DB_ROW['eleve_id']]))
    {
      $tab_saisie[$DB_ROW['eleve_id']] = array(
        'eval'     => NULL ,
        'epi'      => NULL ,
        'ap'       => NULL ,
        'parcours' => NULL ,
        'bilan'    => NULL ,
        'viesco'   => NULL ,
      );
    }
    if(!isset($tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]))
    {
      // Initialisation
      if(!isset($tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]))
      {
        $nb_lignes_supplémentaires[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = 0;
        $tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? FALSE : 'Synthèse générale' ;
      }
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = array( 'note'=>NULL , 'appreciation'=>'' );
    }
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      $tab_prof_appreciation = explode(',',$DB_ROW['listing_profs']);
      if(count($tab_prof_appreciation)==1)
      {
        $tab_prof_appreciation = array( To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) );
      }
      else
      {
        foreach($tab_prof_appreciation as $key => $prof_id)
        {
          $tab_prof_appreciation[$key] = recuperer_prof_nom( $prof_id );
        }
      }
      $texte = implode(' / ',$tab_prof_appreciation).' - '.$DB_ROW['saisie_valeur'];
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]['appreciation'] = suppression_sauts_de_ligne($texte);
      $nb_lignes_rubriques += nombre_de_ligne_supplémentaires($texte);
    }
    if($DB_ROW['saisie_objet']=='position')
    {
      if( ($DB_ROW['rubrique_id']) && ( $DB_ROW['eleve_id'] || $PAGE_MOYENNE_CLASSE ) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) )
      {
        $pourcentage = $DB_ROW['saisie_valeur'] ;
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]['note'] = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ) : '-' ;
        $nb_lignes_rubriques += 2;
      }
    }
  }
  $nb_rubriques = count($tab_rubrique);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune saisie trouvée pour aucun élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_eleves_collegues( $nb_eleves , $nb_lignes_rubriques );
  $archivage_tableau_PDF->appreciation_intitule( 'Livret scolaire - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations par élève' );
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    if(isset($tab_saisie[$eleve_id]))
    {
      foreach($tab_saisie[$eleve_id] as $rubrique_type => $tab_saisie_rubrique)
      {
        if(!is_null($tab_saisie_rubrique))
        {
          $with_moyenne = ($rubrique_type=='eval') ? TRUE : FALSE ;
          foreach($tab_saisie_rubrique as $rubrique_id => $tab)
          {
            if(isset($tab_rubrique[$rubrique_type][$rubrique_id]))
            {
              extract($tab);  // $note $appreciation
              $rubrique_nom = $tab_rubrique[$rubrique_type][$rubrique_id];
              $archivage_tableau_PDF->appreciation_rubrique_eleves_collegues( $eleve_nom , $eleve_prenom , $rubrique_nom , str_replace('.',',',$note) , $appreciation , $with_moyenne , 'livret' /*objet_document*/ );
              $eleve_nom = $eleve_prenom = '' ;
            }
          }
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3/9 imprimer_donnees_classe_collegues : Appréciations des collègues sur le groupe classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_classe_collegues')
{
  // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id      , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par rubrique
  $tab_saisie = array(
    'eval'     => NULL ,
    'epi'      => NULL ,
    'ap'       => NULL ,
    'parcours' => NULL ,
    'bilan'    => NULL ,
    'viesco'   => NULL ,
  );  // [rubrique_type][rubrique_id][eleve_id] => array(note,appreciation);
  $tab_rubrique = array();
  $tab_prof     = array();
  $nb_lignes_supplémentaires = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
  foreach($DB_TAB as $DB_ROW)
  {
    if(!isset($tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]))
    {
      // Initialisation
      $tab_rubrique[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? FALSE : 'Synthèse générale' ;
      $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']] = array( 'note'=>NULL , 'appreciation'=>'' );
    }
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      $tab_prof_appreciation = explode(',',$DB_ROW['listing_profs']);
      if(count($tab_prof_appreciation)==1)
      {
        $tab_prof_appreciation = array( To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) );
      }
      else
      {
        foreach($tab_prof_appreciation as $key => $prof_id)
        {
          $tab_prof_appreciation[$key] = recuperer_prof_nom( $prof_id );
        }
      }
      $texte = implode(' / ',$tab_prof_appreciation).' - '.$DB_ROW['saisie_valeur'];
      $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]['appreciation'] = suppression_sauts_de_ligne($texte);
      $nb_lignes_supplémentaires += nombre_de_ligne_supplémentaires($texte);
    }
    if($DB_ROW['saisie_objet']=='position')
    {
      if( ($DB_ROW['rubrique_id']) && ($PAGE_MOYENNE_CLASSE) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) )
      {
        $pourcentage = $DB_ROW['saisie_valeur'] ;
        $tab_saisie[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']]['note'] = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ) : '-' ;
      }
    }
  }
  $nb_rubriques = count($tab_rubrique);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune saisie trouvée pour aucun élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_classe_collegues( $nb_eleves , $nb_rubriques , $nb_lignes_supplémentaires );
  $archivage_tableau_PDF->appreciation_intitule( 'Livret scolaire - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations du groupe classe' );
  foreach($tab_saisie as $rubrique_type => $tab_saisie_rubrique)
  {
    if(!is_null($tab_saisie_rubrique))
    {
      $with_moyenne = ($rubrique_type=='eval') ? TRUE : FALSE ;
      foreach($tab_saisie_rubrique as $rubrique_id => $tab)
      {
        if(isset($tab_rubrique[$rubrique_type][$rubrique_id]))
        {
          extract($tab);  // $note $appreciation
          $rubrique_nom = $tab_rubrique[$rubrique_type][$rubrique_id];
          $archivage_tableau_PDF->appreciation_rubrique_classe_collegues( $rubrique_nom , str_replace('.',',',$note) , $appreciation , $with_moyenne , 'livret' /*objet_document*/ );
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 4/9 imprimer_donnees_eleves_syntheses : Appréciations de synthèse générale pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_syntheses')
{
  // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
  $DB_TAB = array_merge
  (
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"bilan"' /*liste_rubrique_type*/ , $classe_id      , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ ) ,
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"bilan"' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ )
  );
  // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par élève
  $tab_saisie = array();  // [eleve_id] => appreciation;
  $tab_prof   = array();
  $nb_lignes_supplémentaires = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
  foreach($DB_TAB as $DB_ROW)
  {
    $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      $tab_prof_appreciation = explode(',',$DB_ROW['listing_profs']);
      if(count($tab_prof_appreciation)==1)
      {
        $tab_prof_appreciation = array( To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) );
      }
      else
      {
        foreach($tab_prof_appreciation as $key => $prof_id)
        {
          $tab_prof_appreciation[$key] = recuperer_prof_nom( $prof_id );
        }
      }
      $texte = implode(' / ',$tab_prof_appreciation).' - '.$DB_ROW['saisie_valeur'];
      $tab_saisie[$eleve_id] = suppression_sauts_de_ligne($texte);
      $nb_lignes_supplémentaires += nombre_de_ligne_supplémentaires($texte);
    }
  }
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_eleves_syntheses( $nb_eleves , $nb_lignes_supplémentaires , FALSE /*with_moyenne*/ );
  $archivage_tableau_PDF->appreciation_intitule( 'Livret scolaire - '.$classe_nom.' - '.$periode_nom.' - '.'Synthèses générales' );
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $appreciation = isset($tab_saisie[$eleve_id]) ? $tab_saisie[$eleve_id] : '' ;
    $archivage_tableau_PDF->appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , NULL /*note*/ , $appreciation , FALSE /*with_moyenne*/ , 'livret' /*objet_document*/ );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 5/9 imprimer_donnees_eleves_positionnements : Tableau des positionnements pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_positionnements')
{
  // Rechercher les positionnements enregistrés pour les élèves
  $DB_TAB = array_merge
  (
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ ) ,
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' /*liste_rubrique_type*/ , $classe_id      , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ )
  );
  // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par élève
  $tab_saisie   = array();  // [eleve_id][rubrique_id] => note
  $tab_rubrique = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['saisie_objet']=='position')
    {
      $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
      if(!isset($tab_rubrique['eval'][$DB_ROW['rubrique_id']]))
      {
        $tab_rubrique['eval'][$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? FALSE : 'Synthèse générale' ;
      }
      if( ($DB_ROW['rubrique_id']) && ( $eleve_id || $PAGE_MOYENNE_CLASSE ) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) )
      {
        $pourcentage = $DB_ROW['saisie_valeur'] ;
        $tab_saisie[$eleve_id][$DB_ROW['rubrique_id']] = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage.'%' ) ) : '-' ;
      }
    }
  }
  $nb_rubriques = count($tab_rubrique['eval']);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune rubrique trouvée avec un positionnement pour un élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // ( mettre le groupe classe en dernier )
  if(!$PAGE_MOYENNE_CLASSE)
  {
    unset($tab_eleve_id[0]);
    $nb_eleves--;
  }
  else
  {
    unset($tab_eleve_id[0]); // Pas de array_shift() ici sinon il renumérote et on perd les indices des élèves
    $tab_eleve_id[0] = array( 'eleve_nom' => $classe_nom ,  'eleve_prenom' => '' );
  }
  // Fabrication du PDF ; on a besoin de tourner du texte à 90°
  // Fabrication d'un CSV en parallèle
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->moyennes_initialiser( $nb_eleves , $nb_rubriques );
  $archivage_tableau_CSV = '';
  $separateur = ';';
  // 1ère ligne : intitulés, noms rubriques
  $archivage_tableau_PDF->moyennes_intitule( $classe_nom , $periode_nom , 'livret' /*objet_document*/ );
  $archivage_tableau_CSV .= '"'.$classe_nom.' | '.$periode_nom.'"';
  foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
  {
    $archivage_tableau_PDF->moyennes_reference_rubrique( $rubrique_id , $rubrique_nom );
    $archivage_tableau_CSV .= $separateur.'"'.$rubrique_nom.'"';
  }
  $archivage_tableau_CSV .= "\r\n";
  // ligne suivantes : élèves, positionnements
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->marge_haut+$archivage_tableau_PDF->etiquette_hauteur );
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $archivage_tableau_PDF->moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
    $archivage_tableau_CSV .= '"'.$eleve_nom.' '.$eleve_prenom.'"';
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $note = (isset($tab_saisie[$eleve_id][$rubrique_id])) ? str_replace('.',',',$tab_saisie[$eleve_id][$rubrique_id]) : '-' ;
      $archivage_tableau_PDF->moyennes_note( $eleve_id , $rubrique_id , $note , 'livret' /*objet_document*/ );
      $archivage_tableau_CSV .= $separateur.'"'.$note.'"'; // Remplacer le point décimal par une virgule pour le tableur.
    }
    $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->GetY()+$archivage_tableau_PDF->cases_hauteur );
    $archivage_tableau_CSV .= "\r\n";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 6/9 imprimer_donnees_eleves_recapitulatif : Récapitulatif annuel des positionnements et appréciations par élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_recapitulatif')
{
  // Rechercher et mémoriser les données enregistrées ; on laisse tomber les appréciations de synthèses / EPI / AP / Parcours, ce n'est pas dans les modèles de livret scolaire CAP ou Bac Pro
  $tab_saisie   = array();  // [eleve_id][rubrique_id] => array(note[periode],appreciation[periode],professeur[id])
  $tab_periode  = array();
  $tab_rubrique = array();
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , TRUE /*with_periodes_avant*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    // Initialisation
    if(!isset($tab_rubrique['eval'][$DB_ROW['rubrique_id']]))
    {
      $tab_rubrique['eval'][$DB_ROW['rubrique_id']] = FALSE;
    }
    if(!isset($tab_periode[$DB_ROW['jointure_periode']]))
    {
      $tab_periode[$DB_ROW['jointure_periode']] = $tab_periode_livret['periode'.$DB_ROW['jointure_periode']];
    }
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      $tab_prof_appreciation = explode(',',$DB_ROW['listing_profs']);
      if(count($tab_prof_appreciation)==1)
      {
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['professeur'][$DB_ROW['listing_profs']] = To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] );
      }
      else
      {
        foreach($tab_prof_appreciation as $key => $prof_id)
        {
          $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['professeur'][$prof_id] = recuperer_prof_nom( $prof_id );
        }
      }
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['appreciation'][$DB_ROW['jointure_periode']] = suppression_sauts_de_ligne($DB_ROW['saisie_valeur']);
    }
    if($DB_ROW['saisie_objet']=='position')
    {
      $pourcentage = $DB_ROW['saisie_valeur'] ;
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['note'][$DB_ROW['jointure_periode']] = $pourcentage;
    }
  }
  $nb_rubriques = count($tab_rubrique['eval']);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune donnée trouvée pour aucun élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // Calcul des moyennes annuelles et de classe
  $tab_moyennes = array();  // [rubrique_id][eleve_id|0] => moyenne
  foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
  {
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      $tab_moyennes[$rubrique_id][$eleve_id] = isset($tab_saisie[$eleve_id][$rubrique_id]['note']) ? round( array_sum($tab_saisie[$eleve_id][$rubrique_id]['note']) / count($tab_saisie[$eleve_id][$rubrique_id]['note']) , 1 ) : NULL ;
    }
    $somme  = array_sum($tab_moyennes[$rubrique_id]);
    $nombre = count( array_filter($tab_moyennes[$rubrique_id],'non_vide') );
    $tab_moyennes[$rubrique_id][0] = ($nombre) ? round($somme/$nombre,1) : NULL ;
  }
  // Calcul du nb de lignes requises par élève
  // Regrouper note et appréciation, insérer le nom de la période dans l'appréciation
  $tab_nb_lignes = array();  // [eleve_id][rubrique_id] => nb
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    $nombre = 0;
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $nb_lignes_premiere_colonne = isset($tab_saisie[$eleve_id][$rubrique_id]['professeur']) ? 1 + count($tab_saisie[$eleve_id][$rubrique_id]['professeur']) : 1 ;
      $nb_lignes_derniere_colonne = 0 ;
      foreach($tab_periode as $jointure_periode => $periode_nom)
      {
        if(isset($tab_saisie[$eleve_id][$rubrique_id]['note'][$jointure_periode]))
        {
          $pourcentage = $tab_saisie[$eleve_id][$rubrique_id]['note'][$jointure_periode];
          $note = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ) : '-' ;
          $appreciation = isset($tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode]) ? ' - '.$tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode] : '' ;
          $tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode] = $note.$appreciation;
        }
        if(isset($tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode]))
        {
          $tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode] = $periode_nom.' - '.$tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode];
          $nb_lignes_derniere_colonne += nombre_de_lignes($tab_saisie[$eleve_id][$rubrique_id]['appreciation'][$jointure_periode]);
        }
      }
      $tab_nb_lignes[$eleve_id][$rubrique_id] = max($nb_lignes_premiere_colonne,$nb_lignes_derniere_colonne);
    }
    $tab_nb_lignes[$eleve_id][0] = isset($tab_nb_lignes[$eleve_id]) ? array_sum($tab_nb_lignes[$eleve_id]) : 1 ;
  }
  // Bloc des coordonnées de l'établissement (code repris de [code_officiel_imprimer.php] )
  $tab_etabl_coords = array();
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'denomination'))
  {
    $tab_etabl_coords['denomination'] = $_SESSION['ETABLISSEMENT']['DENOMINATION'];
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'adresse'))
  {
    if($_SESSION['ETABLISSEMENT']['ADRESSE1']) { $tab_etabl_coords['adresse1'] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE2']) { $tab_etabl_coords['adresse2'] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE3']) { $tab_etabl_coords['adresse3'] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'telephone'))
  {
    if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords['telephone'] = 'Tél : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'fax'))
  {
    if($_SESSION['ETABLISSEMENT']['FAX']) { $tab_etabl_coords['fax'] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'courriel'))
  {
    if($_SESSION['ETABLISSEMENT']['COURRIEL']) { $tab_etabl_coords['courriel'] = 'Mél : '.$_SESSION['ETABLISSEMENT']['COURRIEL']; } // @see http://www.langue-fr.net/Courriel-E-Mail-Mel | https://fr.wiktionary.org/wiki/m%C3%A9l | https://fr.wikipedia.org/wiki/Courrier_%C3%A9lectronique#.C3.89volution_des_termes_employ.C3.A9s_par_les_utilisateurs
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'url'))
  {
    if($_SESSION['ETABLISSEMENT']['URL']) { $tab_etabl_coords['url'] = 'Web : '.$_SESSION['ETABLISSEMENT']['URL']; }
  }
  // Indication de l'année scolaire (code repris de [code_officiel_imprimer.php] )
  $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
  $annee_affichee = To::annee_scolaire('texte',$annee_decalage);
  // Tag date heure initiales (code repris de [code_officiel_imprimer.php] )
  $tag_date_heure_initiales = date('d/m/Y H:i').' '.To::texte_identite($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( TRUE /*officiel*/ , 'portrait' /*orientation*/ , 5 /*marge_gauche*/ , 5 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  unset($tab_eleve_id[0]);
  $classe_effectif = count($tab_eleve_id);
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    $archivage_tableau_PDF->recapitulatif_initialiser( $tab_etabl_coords , $tab_eleve , $classe_nom , $classe_effectif , $annee_affichee , $tag_date_heure_initiales , $tab_nb_lignes[$eleve_id][0] , 'livret' /*objet_document*/ );
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $tab_prof = isset($tab_saisie[$eleve_id][$rubrique_id]['professeur']) ? $tab_saisie[$eleve_id][$rubrique_id]['professeur'] : NULL ;
      $moyenne_eleve  = !is_null($tab_moyennes[$rubrique_id][$eleve_id]) ? round(($tab_moyennes[$rubrique_id][$eleve_id]/5),1) : NULL ; // Forcé sur 20 dans tous les cas
      $moyenne_classe = !is_null($tab_moyennes[$rubrique_id][0]) ? round(($tab_moyennes[$rubrique_id][0]/5),1) : NULL ; // Forcé sur 20 dans tous les cas
      $tab_appreciations = isset($tab_saisie[$eleve_id][$rubrique_id]['appreciation']) ? $tab_saisie[$eleve_id][$rubrique_id]['appreciation'] : array() ;
      $archivage_tableau_PDF->recapitulatif_rubrique( $tab_nb_lignes[$eleve_id][$rubrique_id] , $rubrique_nom , $tab_prof , $moyenne_eleve , $moyenne_classe , $tab_appreciations );
    }
  }
  $periode_nom = 'Année Scolaire';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 7/9 imprimer_donnees_eleves_affelnet : Récapitulatif des points calculés pour saisie dans Affelnet si hors LSU
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_affelnet')
{
  // Rechercher et mémoriser les données enregistrées ; on laisse tomber les appréciations de synthèses / EPI / AP / Parcours, ce n'est pas dans les modèles de livret scolaire CAP ou Bac Pro
  $tab_point    = array( 1=>3 , 2=>8 , 3=>13 , 4=>16 );
  $tab_saisie   = array();  // [eleve_id][rubrique_id] => array(note[periode],point[periode],professeur[id])
  $tab_periode  = array();
  $tab_rubrique = array();
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , TRUE /*with_periodes_avant*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    // Initialisation
    if(!isset($tab_rubrique['eval'][$DB_ROW['rubrique_id']]))
    {
      $tab_rubrique['eval'][$DB_ROW['rubrique_id']] = FALSE;
    }
    if(!isset($tab_periode[$DB_ROW['jointure_periode']]))
    {
      $tab_periode[$DB_ROW['jointure_periode']] = $tab_periode_livret['periode'.$DB_ROW['jointure_periode']];
    }
    if($DB_ROW['saisie_objet']=='position')
    {
      $pourcentage = $DB_ROW['saisie_valeur'] ;
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['note'][$DB_ROW['jointure_periode']] = $pourcentage;
      // Calcul des points Affelnet
      if(!is_null($pourcentage))
      {
        if(in_array($PAGE_COLONNE,array('objectif','position')))
        {
          $point = $tab_point[ OutilBilan::determiner_degre_maitrise($pourcentage) ];
        }
        else
        {
          $point = $tab_point[ min( 4 , 1 + floor($pourcentage/25) ) ];
        }
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['point'][$DB_ROW['jointure_periode']] = $point;
      }
    }
  }
  $nb_rubriques = count($tab_rubrique['eval']);
  if(!$nb_rubriques)
  {
    Json::end( FALSE , 'Aucune donnée trouvée pour aucun élève !' );
  }
  recuperer_intitules_rubriques( $PAGE_RUBRIQUE_TYPE , $classe_id , $PAGE_REF );
  // Calcul des moyennes annuelles des points Affelnet
  $tab_moyennes = array();  // [rubrique_id][eleve_id|0] => moyenne
  foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
  {
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      $tab_moyennes[$rubrique_id][$eleve_id] = isset($tab_saisie[$eleve_id][$rubrique_id]['point']) ? round( array_sum($tab_saisie[$eleve_id][$rubrique_id]['point']) / count($tab_saisie[$eleve_id][$rubrique_id]['point']) , 1 ) : NULL ;
    }
    $somme  = array_sum($tab_moyennes[$rubrique_id]);
    $nombre = count( array_filter($tab_moyennes[$rubrique_id],'non_vide') );
    $tab_moyennes[$rubrique_id][0] = ($nombre) ? round($somme/$nombre,1) : NULL ;
  }
  // Calcul du nb de lignes requises par élève
  $tab_nb_lignes = array();  // [eleve_id][rubrique_id] => nb
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $nb_lignes_premiere_colonne = isset($tab_saisie[$eleve_id][$rubrique_id]['professeur']) ? 1 + count($tab_saisie[$eleve_id][$rubrique_id]['professeur']) : 1 ;
      foreach($tab_periode as $jointure_periode => $periode_nom)
      {
        $pourcentage = isset($tab_saisie[$eleve_id][$rubrique_id]['note'][$jointure_periode]) ? $tab_saisie[$eleve_id][$rubrique_id]['note'][$jointure_periode] : NULL ;
        $note = !is_null($pourcentage) ? ( in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage).'/4' : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ) : '-' ;
        $tab_saisie[$eleve_id][$rubrique_id]['note'][$jointure_periode] = $periode_nom.' : '.$note;
      }
      $nb_lignes_periodes = isset($tab_saisie[$eleve_id][$rubrique_id]['note']) ? count($tab_saisie[$eleve_id][$rubrique_id]['note']) : 1 ;
      $tab_nb_lignes[$eleve_id][$rubrique_id] = max( $nb_lignes_premiere_colonne , $nb_lignes_periodes );
    }
    $tab_nb_lignes[$eleve_id][0] = isset($tab_nb_lignes[$eleve_id]) ? array_sum($tab_nb_lignes[$eleve_id]) : 1 ;
  }
  // Bloc des coordonnées de l'établissement (code repris de [code_officiel_imprimer.php] )
  $tab_etabl_coords = array();
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'denomination'))
  {
    $tab_etabl_coords['denomination'] = $_SESSION['ETABLISSEMENT']['DENOMINATION'];
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'adresse'))
  {
    if($_SESSION['ETABLISSEMENT']['ADRESSE1']) { $tab_etabl_coords['adresse1'] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE2']) { $tab_etabl_coords['adresse2'] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE3']) { $tab_etabl_coords['adresse3'] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'telephone'))
  {
    if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords['telephone'] = 'Tél : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'fax'))
  {
    if($_SESSION['ETABLISSEMENT']['FAX']) { $tab_etabl_coords['fax'] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'courriel'))
  {
    if($_SESSION['ETABLISSEMENT']['COURRIEL']) { $tab_etabl_coords['courriel'] = 'Mél : '.$_SESSION['ETABLISSEMENT']['COURRIEL']; } // @see http://www.langue-fr.net/Courriel-E-Mail-Mel | https://fr.wiktionary.org/wiki/m%C3%A9l | https://fr.wikipedia.org/wiki/Courrier_%C3%A9lectronique#.C3.89volution_des_termes_employ.C3.A9s_par_les_utilisateurs
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'url'))
  {
    if($_SESSION['ETABLISSEMENT']['URL']) { $tab_etabl_coords['url'] = 'Web : '.$_SESSION['ETABLISSEMENT']['URL']; }
  }
  // Indication de l'année scolaire (code repris de [code_officiel_imprimer.php] )
  $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
  $annee_affichee = To::annee_scolaire('texte',$annee_decalage);
  // Tag date heure initiales (code repris de [code_officiel_imprimer.php] )
  $tag_date_heure_initiales = date('d/m/Y H:i').' '.To::texte_identite($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( TRUE /*officiel*/ , 'portrait' /*orientation*/ , 5 /*marge_gauche*/ , 5 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  unset($tab_eleve_id[0]);
  $classe_effectif = count($tab_eleve_id);
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    $archivage_tableau_PDF->recapitulatif_initialiser( $tab_etabl_coords , $tab_eleve , $classe_nom , $classe_effectif , $annee_affichee , $tag_date_heure_initiales , $tab_nb_lignes[$eleve_id][0] , 'affelnet' /*objet_document*/ );
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $tab_prof   = isset($tab_saisie[$eleve_id][$rubrique_id]['professeur']) ? $tab_saisie[$eleve_id][$rubrique_id]['professeur'] : NULL ;
      $tab_notes  = isset($tab_saisie[$eleve_id][$rubrique_id]['note'])  ? $tab_saisie[$eleve_id][$rubrique_id]['note']  : array() ;
      $tab_points = isset($tab_saisie[$eleve_id][$rubrique_id]['point']) ? $tab_saisie[$eleve_id][$rubrique_id]['point'] : array() ;
      $moyenne_eleve = !is_null($tab_moyennes[$rubrique_id][$eleve_id]) ? $tab_moyennes[$rubrique_id][$eleve_id] : NULL ;
      ksort($tab_notes);
      ksort($tab_points);
      $archivage_tableau_PDF->recapitulatif_rubrique_affelnet( $tab_nb_lignes[$eleve_id][$rubrique_id] , $rubrique_nom , $tab_prof , $tab_notes , $tab_points , $moyenne_eleve );
    }
  }
  // On ajoute un tableau récapitulatif ; on a besoin de tourner du texte à 90°
  $periode_nom = 'Année Scolaire';
  $archivage_tableau_PDF->moyennes_initialiser( $nb_eleves , $nb_rubriques );
  $separateur = ';';
  // 1ère ligne : intitulés, noms rubriques
  $archivage_tableau_PDF->moyennes_intitule( $classe_nom , $periode_nom , 'affelnet' /*objet_document*/ );
  foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
  {
    $archivage_tableau_PDF->moyennes_reference_rubrique( $rubrique_id , $rubrique_nom );
  }
  // ligne suivantes : élèves, positionnements
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->marge_haut+$archivage_tableau_PDF->etiquette_hauteur );
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $archivage_tableau_PDF->moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
    foreach($tab_rubrique['eval'] as $rubrique_id => $rubrique_nom)
    {
      $moyenne_eleve = !is_null($tab_moyennes[$rubrique_id][$eleve_id]) ? str_replace('.',',',$tab_moyennes[$rubrique_id][$eleve_id]) : '-' ;
      $archivage_tableau_PDF->moyennes_note( $eleve_id , $rubrique_id , $moyenne_eleve , 'affelnet' /*objet_document*/ );
    }
    $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->GetY()+$archivage_tableau_PDF->cases_hauteur );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 8/9 imprimer_donnees_eleves_socle_maitrise : Tableau des positionnements sur le socle pour chaque élève
// Cas 9/9 imprimer_donnees_eleves_socle_points_dnb : Tableau des points du brevet pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='imprimer_donnees_eleves_socle_maitrise') || ($action=='imprimer_donnees_eleves_socle_points_dnb') )
{
  $tab_points_valeur   = array( 0=>0 , 1=>10 , 2=>25 , 3=>40 , 4=>50 );
  $tab_points_enscompl = array( 0=>0                 , 3=>10 , 4=>20 );
  // Récupération de la liste des domaines et composantes du socle (indépendant du cycle sélectionné)
  $tab_rubrique = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_arborescence();
  foreach($DB_TAB as $DB_ROW)
  {
    $rubrique_id = ($DB_ROW['socle_domaine_id']==1) ? $DB_ROW['socle_composante_id'] : $DB_ROW['socle_domaine_id']*10 ;
    $txt_abrev_domaine = 'Domaine '.$DB_ROW['socle_domaine_id'];
    $txt_abrev_composante = ($DB_ROW['socle_domaine_id']==1) ? ' - Composante '.($DB_ROW['socle_composante_id']%10) : '' ;
    $txt_abrev = $txt_abrev_domaine.$txt_abrev_composante;
    $tab_rubrique[$rubrique_id] = $txt_abrev;
  }
  if($PAGE_REF=='cycle4')
  {
    $tab_rubrique['enscompl'] = 'Enseignement de complément' ;
  }
  if($action=='imprimer_donnees_eleves_socle_points_dnb')
  {
    $tab_rubrique[0] = 'Nombre de points (sur 400)' ;
  }
  $nb_rubriques = count($tab_rubrique);
  // Récupérer les enseignements complémentaires suivi pour les élèves concernés
  if($PAGE_REF=='cycle4')
  {
    $tab_enscompl_eleve = array();
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_enscompl( $liste_eleve_id );
    foreach($DB_TAB as $DB_ROW)
    {
      // 1 enseignement complémentaire au maximum par élève
      $tab_enscompl_eleve[$DB_ROW['eleve_id']][$DB_ROW['livret_enscompl_id']] = TRUE;
    }
  }
  // Rechercher les positionnements enregistrés pour les élèves
  $liste_rubrique_type = ($PAGE_REF=='cycle4') ? '"socle","enscompl"' : '"socle"' ;
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $liste_rubrique_type , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  // Répertorier les saisies dans le tableau $tab_saisie
  $tab_saisie   = array();  // [eleve_id][rubrique_id] => positionnement
  foreach($DB_TAB as $DB_ROW)
  {
    // Test pour vérifier, en cas d'enseignement de complément, que c'est bien celui suivi par l'élève
    if( ($PAGE_REF!='cycle4') || ($DB_ROW['rubrique_type']!='enscompl') || isset($tab_enscompl_eleve[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]) )
    {
      $pourcentage = !is_null($DB_ROW['saisie_valeur']) ? $DB_ROW['saisie_valeur'] : FALSE ;
      $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
      if($DB_ROW['rubrique_type']=='socle')
      {
        $rubrique_id = $DB_ROW['rubrique_id'] ;
        $tab_saisie[$DB_ROW['eleve_id']][$rubrique_id]['indice'] = $indice;
        $tab_saisie[$DB_ROW['eleve_id']][$rubrique_id]['points'] = ($indice!==FALSE) ? $tab_points_valeur[$indice] : FALSE ;
      }
      elseif($DB_ROW['rubrique_type']=='enscompl')
      {
        $rubrique_id = 'enscompl' ;
        $tab_saisie[$DB_ROW['eleve_id']][$rubrique_id]['indice'] = $indice;
        $tab_saisie[$DB_ROW['eleve_id']][$rubrique_id]['points'] = ($indice!==FALSE) ? $tab_points_enscompl[$indice] : FALSE ;
      }
      if($action=='imprimer_donnees_eleves_socle_points_dnb')
      {
        if(!isset($tab_saisie[$DB_ROW['eleve_id']][0]))
        {
          $tab_saisie[$DB_ROW['eleve_id']][0]['points'] = 0;
        }
        $tab_saisie[$DB_ROW['eleve_id']][0]['points'] += $tab_saisie[$DB_ROW['eleve_id']][$rubrique_id]['points'] ;
      }
    }
  }
  // Fabrication du PDF ; on a besoin de tourner du texte à 90°
  // Fabrication d'un CSV en parallèle
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'oui' /*couleur*/ );
  $archivage_tableau_PDF->moyennes_initialiser( $nb_eleves , $nb_rubriques );
  $archivage_tableau_CSV = '';
  $separateur = ';';
  if($action=='imprimer_donnees_eleves_socle_maitrise')
  {
    $archivage_tableau_CSV .= '"I = Maîtrise insuffisante"'.$separateur.'"F = Maîtrise fragile"'.$separateur.'"S = Maîtrise satisfaisante"'.$separateur.'"T = Très bonne maîtrise"'.$separateur.'"A = Atteint"'.$separateur.'"D = Dépassé"'."\r\n"."\r\n";
    $tab_codes = array(
      'socle' => array(
        FALSE => '',
        1 => 'I',
        2 => 'F',
        3 => 'S',
        4 => 'T',
      ),
      'enscompl' => array(
        FALSE => '',
        1 => 'A',
        2 => 'D',
      ),
    );
  }
  // 1ère ligne : intitulés, noms rubriques
  $periode_nom .= ' '.substr($PAGE_REF,-1);
  $objet_document = ($action=='imprimer_donnees_eleves_socle_points_dnb') ? 'points_dnb' : 'livret' ;
  $objet_csv = ($action=='imprimer_donnees_eleves_socle_points_dnb') ? 'Points pour le brevet' : 'Positionnements sur le socle' ;
  $archivage_tableau_PDF->moyennes_intitule( $classe_nom , $periode_nom , $objet_document );
  $archivage_tableau_CSV .= '"'.$classe_nom.' | '.$periode_nom.'"'.' | '.$objet_document.'"';
  foreach($tab_rubrique as $rubrique_id => $rubrique_nom)
  {
    $archivage_tableau_PDF->moyennes_reference_rubrique( $rubrique_id , $rubrique_nom );
    $archivage_tableau_CSV .= $separateur.'"'.$rubrique_nom.'"';
  }
  $archivage_tableau_CSV .= "\r\n";
  // ligne suivantes : élèves, positionnements
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->marge_haut+$archivage_tableau_PDF->etiquette_hauteur );
  unset($tab_eleve_id[0]);
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $archivage_tableau_PDF->moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
    $archivage_tableau_CSV .= '"'.$eleve_nom.' '.$eleve_prenom.'"';
    foreach($tab_rubrique as $rubrique_id => $rubrique_nom)
    {
      if($rubrique_id)
      {
        $indice = !empty($tab_saisie[$eleve_id][$rubrique_id]) ? $tab_saisie[$eleve_id][$rubrique_id]['indice'] : FALSE ;
        if($action=='imprimer_donnees_eleves_socle_maitrise')
        {
          if($rubrique_id!='enscompl')
          {
            $archivage_tableau_PDF->afficher_degre_maitrise( $indice , $indice /*valeur*/ , ' / 4' /*unit*/ , FALSE /*all_columns*/ );
            $archivage_tableau_CSV .= $separateur.'"'.$tab_codes['socle'][$indice].'"'; // Remplacer le point décimal par une virgule pour le tableur.
          }
          else
          {
            $valeur = ($indice!==FALSE) ? $indice-2 : FALSE ;
            $archivage_tableau_PDF->afficher_degre_maitrise( $indice , $valeur , ' / 2' /*unit*/ , FALSE /*all_columns*/ );
            $archivage_tableau_CSV .= $separateur.'"'.$tab_codes['enscompl'][$valeur].'"'; // Remplacer le point décimal par une virgule pour le tableur.
          }
        }
        else
        {
          $points = !empty($tab_saisie[$eleve_id][$rubrique_id]) ? $tab_saisie[$eleve_id][$rubrique_id]['points'] : FALSE ;
          $archivage_tableau_PDF->afficher_degre_maitrise( $indice , $points /*valeur*/ , ' pts' /*unit*/ , FALSE /*all_columns*/ );
          $archivage_tableau_CSV .= $separateur.'"'.$points.'"'; // Remplacer le point décimal par une virgule pour le tableur.
        }
      }
      else
      {
        $points = !empty($tab_saisie[$eleve_id][$rubrique_id]) ? $tab_saisie[$eleve_id][$rubrique_id]['points'] : '-' ;
        $archivage_tableau_PDF-> moyennes_note( $eleve_id , $rubrique_id , $points , $objet_document );
        $archivage_tableau_CSV .= $separateur.'"'.$points.'"'; // Remplacer le point décimal par une virgule pour le tableur.
      }
    }
    $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->GetY()+$archivage_tableau_PDF->cases_hauteur );
    $archivage_tableau_CSV .= "\r\n";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement et affichage du retour.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_export = Clean::fichier('livret_'.$PAGE_REF.'_'.$JOINTURE_PERIODE.'_'.$classe_nom.'_'.$action.'_').FileSystem::generer_fin_nom_fichier__date_et_alea();
FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.pdf' , $archivage_tableau_PDF );
Json::add_str('<a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_export.'.pdf"><span class="file file_pdf">'.$tab_actions[$action].' (format <em>pdf</em>).</span></a>');
// Et le csv éventuel
if( ($action=='imprimer_donnees_eleves_positionnements') || ($action=='imprimer_donnees_eleves_socle_maitrise') || ($action=='imprimer_donnees_eleves_socle_points_dnb') )
{
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.csv' , To::csv($archivage_tableau_CSV) );
  Json::add_str('<br />'.NL.'<a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fichier_export.'.csv"><span class="file file_txt">'.$tab_actions[$action].' (format <em>csv</em>).</span></a>');
}
Json::end( TRUE );

?>