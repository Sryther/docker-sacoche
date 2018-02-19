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
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Autres chaines spécifiques...
$listing_matieres = (isset($_POST['f_listing_matieres'])) ? $_POST['f_listing_matieres'] : '' ;
$tab_matiere_id = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere_id);

$groupe_id      = ($is_sous_groupe) ? $groupe_id : $classe_id ; // Le groupe = le groupe transmis ou sinon la classe (cas le plus fréquent).

$separateur = ';';

$tab_objet  = array('modifier','tamponner');
$tab_action = array('generer_csv_vierge','uploader_saisie_csv','enregistrer_saisie_csv');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!in_array($OBJET,$tab_objet)) || !$classe_id )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

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

if( !in_array($OBJET.$BILAN_ETAT,array('modifier2rubrique','modifier3mixte','tamponner3mixte','tamponner4synthese')) || ($BILAN_TYPE_ETABL!='college') || ($PAGE_PERIODICITE!='periode') )
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}

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

if( !in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Élèves concernés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;

$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                             : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
}
$csv_lignes_eleves = array( 0 => 'groupe_'.$groupe_id.$separateur.'"Classe / Groupe"'.$separateur );
$tab_eleve_id      = array( 0 => 'Classe / Groupe' );
foreach($DB_TAB as $DB_ROW)
{
  $csv_lignes_eleves[$DB_ROW['user_id']] = 'eleve_'.$DB_ROW['user_id'].$separateur.'"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
  $tab_eleve_id[$DB_ROW['user_id']]      = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
}
$liste_eleve_id = implode(',',array_keys($tab_eleve_id));

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rubriques concernées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubriques = array() ;
if($OBJET=='modifier')
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' /*liste_rubrique_type*/ , $liste_eleve_id , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_id = explode(' ',$DB_ROW['listing_profs']);
    if(in_array($_SESSION['USER_ID'],$tab_id))
    {
      $tab_rubriques['eval'][$DB_ROW['rubrique_id']] = $DB_ROW['rubrique_id'];
    }
  }
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if( isset($tab_rubriques['eval'][$DB_ROW['livret_rubrique_id']]) )
    {
      $tab_rubriques['eval'][$DB_ROW['livret_rubrique_id']] = $DB_ROW['rubrique'];
    }
  }
  if( $PAGE_EPI )
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi( $classe_id , $PAGE_REF );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_id = explode(' ',$DB_ROW['matiere_prof_id']);
      foreach($tab_id as $key => $ids)
      {
        list( $matiere_id , $user_id ) = explode('_',$ids);
        if($user_id==$_SESSION['USER_ID'])
        {
          $tab_rubriques['epi'][$DB_ROW['livret_epi_id']] = 'EPI '.$DB_ROW['livret_epi_titre'];
        }
      }
    }
  }
  if( $PAGE_AP )
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_ap( $classe_id , $PAGE_REF );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_id = explode(' ',$DB_ROW['matiere_prof_id']);
      foreach($tab_id as $key => $ids)
      {
        list( $matiere_id , $user_id ) = explode('_',$ids);
        if($user_id==$_SESSION['USER_ID'])
        {
          $tab_rubriques['ap'][$DB_ROW['livret_ap_id']] = 'AP '.$DB_ROW['livret_ap_titre'];
        }
      }
    }
  }
  if( $PAGE_PARCOURS )
  {
    $tab_parcours_code = explode(',',$PAGE_PARCOURS);
    foreach($tab_parcours_code as $parcours_code)
    {
      $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours( $parcours_code ,  $classe_id , $PAGE_REF );
      if(!empty($DB_TAB))
      {
        $DB_ROW = $DB_TAB[0];
        $tab_id = explode(' ',$DB_ROW['prof_id']);
        if(in_array($_SESSION['USER_ID'],$tab_id))
        {
          $tab_rubriques['parcours'][$DB_ROW['livret_parcours_id']] = $DB_ROW['livret_parcours_type_nom'];
        }
      }
    }
  }
}
else if($OBJET=='tamponner')
{
  $tab_rubriques['bilan'][0] = 'Synthèse générale';
}



// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1 : générer un CSV pour une saisie déportée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 * Le CSV proposé est VIERGE à la fois par facilité, parce qu'il ne n'agit pas d'un archivage,
 * et pour éviter de figer des positionnements sans trop le vouloir.
*/
if($ACTION=='generer_csv_vierge')
{
  $groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $export_csv = 'livret_'.$PAGE_REF.'_'.$JOINTURE_PERIODE.'_'.$BILAN_ETAT.'_'.$_SESSION['USER_ID'].'_'.$groupe_id.$separateur.'Saisie déportée - Livret scolaire - '.$periode_nom.' - '.$groupe_nom."\r\n\r\n";
  if($PAGE_COLONNE=='objectif')        { $type_note = 'Objectif de 1 à 4'; }
  elseif($PAGE_COLONNE=='position')    { $type_note = 'Position de 1 à 4'; }
  elseif($PAGE_COLONNE=='moyenne')     { $type_note = 'Moyenne sur 20'; }
  elseif($PAGE_COLONNE=='pourcentage') { $type_note = 'Pourcentage'; }
  foreach($tab_rubriques as $rubrique_type => $tab_rubrique_type)
  {
    $with_note = ( ($rubrique_type=='eval') && ($OBJET=='modifier') ) ? TRUE : FALSE ;
    foreach($tab_rubrique_type as $rubrique_id => $rubrique_nom)
    {
      $rubrique_ligne_fin = ($with_note) ? $type_note.$separateur.'Appréciation' : 'Appréciation' ;
      $export_csv .= $rubrique_type.'_'.$rubrique_id.$separateur.'"'.$rubrique_nom.'"'.$separateur.$rubrique_ligne_fin."\r\n";
      foreach($csv_lignes_eleves as $eleve_id => $eleve_ligne_debut)
      {
        $export_csv .= $eleve_ligne_debut;
        $export_csv .= ($with_note) ? $separateur."\r\n" : "\r\n" ;
      }
      $export_csv .= "\r\n"; // une valeur NULL donnera une chaine vide
    }
  }
  $fnom_export = 'saisie_deportee_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.'livret_'.$PAGE_REF.'_'.Clean::fichier($periode_nom).'_'.Clean::fichier($groupe_nom).'_'.$BILAN_ETAT.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.csv';
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom_export , To::csv($export_csv) );
  Json::end( TRUE , $fnom_export );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2 : réception d'un import csv (saisie déportée)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='uploader_saisie_csv')
{
  // Récupération du fichier
  $fichier_nom = Clean::fichier('saisie_deportee_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.'livret_'.$PAGE_REF.'_'.$JOINTURE_PERIODE.'_'.$groupe_id.'_'.$BILAN_ETAT.'_').FileSystem::generer_fin_nom_fichier__date_et_alea();
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom.'.<EXT>' /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Extraire les lignes du fichier
  $tab_lignes = FileSystem::extraire_lignes_csv(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  if(count($tab_lignes)<4)
  {
    Json::end( FALSE , 'Absence de données suffisantes (fichier comportant moins de 4 lignes) !' );
  }
  // Données de la ligne d'en-tête
  $tab_elements = $tab_lignes[0];
  // Supprimer la 1e ligne
  unset($tab_lignes[0]);
  list( $string_references , $titre ) = $tab_elements + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  $tab_references = explode('_',$string_references);
  if(count($tab_references)!=6)
  {
    Json::end( FALSE , 'Ligne d\'en-tête invalide !' );
  }
  list( $del , $csv_page_ref , $csv_periode_jointure , $csv_bilan_etat , $csv_user_id , $csv_groupe_id ) = $tab_references;
  if($csv_page_ref!=$PAGE_REF)
  {
    Json::end( FALSE , 'Objet de document non concordant ('.$csv_page_ref.' reçu / '.$PAGE_REF.' attendu) !' );
  }
  if($csv_periode_jointure!=$JOINTURE_PERIODE)
  {
    Json::end( FALSE , 'Fichier transmis d\'une autre période ('.$csv_periode_jointure.' reçu / '.$JOINTURE_PERIODE.' attendu) !' );
  }
  if($csv_bilan_etat!=$BILAN_ETAT)
  {
    Json::end( FALSE , 'Étape du bilan non concordante ('.$csv_bilan_etat.' reçu / '.$BILAN_ETAT.' attendu) !' );
  }
  if($csv_user_id!=$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre utilisateur !' );
  }
  if($csv_groupe_id!=$groupe_id)
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre regroupement d\'élèves !' );
  }
  // On y va
  $rubrique_type = NULL;
  $rubrique_id   = NULL;
  $tab_donnees_csv = array(); // [rubrique_type][rubrique_id][eleve_id][type(position|appreciation)] => [valeur][idem|insert|update]
  // $nb_colonnes = ($with_note) ? 4 : 3 ;
  foreach ($tab_lignes as $tab_elements)
  {
    $tab_elements = array_slice($tab_elements,0,4);
    if(count($tab_elements)>=3)
    {
      list( $col1 , $col2 , $col3 , $col4 ) = $tab_elements + array_fill(0,4,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
      $tab_ref1 = explode('_',$col1);
      if(count($tab_ref1)==2)
      {
        list( $ref1_objet , $ref1_valeur ) = $tab_ref1;
        $ref1_valeur = (int)$ref1_valeur;
        // Une nouvelle rubrique ; on vérifie sa validité
        if( isset($tab_rubriques[$ref1_objet]) )
        {
          if( isset($tab_rubriques[$ref1_objet][$ref1_valeur]) )
          {
            $rubrique_type = $ref1_objet;
            $rubrique_id   = $ref1_valeur;
            $longueur_maxi = ($rubrique_id) ? 600 : 1000 ;
            $with_note = ( ($rubrique_type=='eval') && ($OBJET=='modifier') ) ? TRUE : FALSE ;
          }
          else
          {
            $rubrique_id = NULL;
          }
        }
        // Un nouveau groupe (appréciation sur le groupe) ; on vérifie sa validité
        elseif( ($ref1_objet=='groupe') && ($ref1_valeur==$groupe_id) && ($rubrique_id!==NULL) )
        {
          $eleve_id = 0;
          $appreciation = ($with_note) ? Clean::appreciation($col4) : Clean::appreciation($col3) ;
          if($appreciation)
          {
            $tab_donnees_csv[$rubrique_type][$rubrique_id][$eleve_id]['appreciation'] = array( 'val'=>mb_substr($appreciation,0,$longueur_maxi) , 'id'=>0 );
          }
        }
        // Un nouvel élève ; on vérifie sa validité
        elseif( ($ref1_objet=='eleve') && $ref1_valeur && isset($tab_eleve_id[$ref1_valeur]) && ($rubrique_id!==NULL) )
        {
          $eleve_id = $ref1_valeur;
          if( ($with_note) && ($col3!=='') )
          {
            $position = Clean::decimal($col3);
            if( !in_array($PAGE_COLONNE,array('objectif','position')) || in_array($position,array(1,2,3,4)) )
            {
              $tab_donnees_csv[$rubrique_type][$rubrique_id][$eleve_id]['position'] = array( 'val'=>$position , 'id'=>0 );
            }
          }
          $appreciation = ($with_note) ? Clean::appreciation($col4) : Clean::appreciation($col3) ;
          if($appreciation)
          {
            $tab_donnees_csv[$rubrique_type][$rubrique_id][$eleve_id]['appreciation'] = array( 'val'=>mb_substr($appreciation,0,$longueur_maxi) , 'id'=>0 );
          }
        }
      }
    }
  }
  if(!count($tab_donnees_csv))
  {
    Json::end( FALSE , 'Aucune saisie trouvée dans le fichier transmis !' );
  }
  // On compare avec ce qui est enregistré dans la base pour distinguer s'il s'agit d'UPDATE, d'INSERT, ou si cela n'a pas changé.
  // Cette partie de code est inspirée de [code_livret_archiver.php]
  $DB_TAB = array_merge
  (
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id      , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ ) ,
    DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ )
  );
  // Les appréciations
  foreach($DB_TAB as $key => $DB_ROW)
  {
    if($DB_ROW['saisie_objet']=='appreciation')
    {
      $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
      if(isset($tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['appreciation']))
      {
        $appreciation = $tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['appreciation']['val'];
        $tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['appreciation']['id'] = ( $appreciation == $DB_ROW['saisie_valeur'] ) ? NULL : $DB_ROW['livret_saisie_id'] ;
      }
      unset($DB_TAB[$key]);
    }
  }
  // Les positionnements
  if($OBJET=='modifier')
  {
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if( ($DB_ROW['rubrique_type']=='eval') && ($DB_ROW['saisie_objet']=='position') )
      {
        $eleve_id = isset($DB_ROW['eleve_id']) ? $DB_ROW['eleve_id'] : 0 ;
        if(isset($tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['position']))
        {
          $position = $tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['position']['val'];
          if($PAGE_COLONNE=='pourcentage') { $saisie_valeur = round($position,1); }
          else if($PAGE_COLONNE=='moyenne') { $saisie_valeur = round($position*5,1); }
          else  { $saisie_valeur = ($_SESSION['LIVRET'][$position]['SEUIL_MIN']+$_SESSION['LIVRET'][$position]['SEUIL_MAX'])/2; }
          $tab_donnees_csv[$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$eleve_id]['position']['id'] = ( $saisie_valeur == $DB_ROW['saisie_valeur'] ) ? NULL : $DB_ROW['livret_saisie_id'] ;
        }
        unset($DB_TAB[$key]);
      }
    }
  }
  // Affichage du résultat de l'analyse et suppression au passage des absences de modifs
  $nb_modifs = 0;
  Json::add_row( 'html' , '<thead><tr><th colspan="3">'.html($titre).'</th></tr></thead><tbody>' );
  foreach($tab_rubriques as $rubrique_type => $tab_rubrique_type)
  {
    foreach($tab_rubrique_type as $rubrique_id => $rubrique_nom)
    {
      if(isset($tab_donnees_csv[$rubrique_type][$rubrique_id]))
      {
        Json::add_row( 'html' , '<tr><th colspan="3">'.html($rubrique_nom).'</th></tr>' );
        foreach($tab_donnees_csv[$rubrique_type][$rubrique_id] as $eleve_id => $tab_infos)
        {
          if(isset($tab_infos['position']))
          {
            $mode = is_null($tab_infos['position']['id']) ? 'idem' : ( ($tab_infos['position']['id']===0) ? 'insert' : 'update' ) ;
            $note = in_array($PAGE_COLONNE,array('objectif','position')) ? $tab_infos['position']['val'].'/4' : ( ($PAGE_COLONNE=='moyenne') ? $tab_infos['position']['val'].'/20' : $tab_infos['position']['val'].'&nbsp;%' ) ;
            Json::add_row( 'html' , '<tr class="'.$mode.'"><td>'.html($tab_eleve_id[$eleve_id]).'</td><td>Positionnement</td><td>'.$note.'</td></tr>' );
            if($mode!='idem')
            {
              $nb_modifs++;
            }
            else
            {
              unset($tab_donnees_csv[$rubrique_type][$rubrique_id][$eleve_id]['position']);
            }
          }
          if(isset($tab_infos['appreciation']))
          {
            $mode = is_null($tab_infos['appreciation']['id']) ? 'idem' : ( ($tab_infos['appreciation']['id']===0) ? 'insert' : 'update' ) ;
            $appreciation = $tab_infos['appreciation']['val'];
            Json::add_row( 'html' , '<tr class="'.$mode.'"><td>'.html($tab_eleve_id[$eleve_id]).'</td><td>Appreciation</td><td>'.html($appreciation).'</td></tr>' );
            if($mode!='idem')
            {
              $nb_modifs++;
            }
            else
            {
              unset($tab_donnees_csv[$rubrique_type][$rubrique_id][$eleve_id]['appreciation']);
            }
          }
        }
      }
    }
  }
  Json::add_row( 'html' , '</tbody>' );
  if(!$nb_modifs)
  {
    Json::end( FALSE , 'Aucune différence trouvée avec ce qui est déjà enregistré !' );
  }
  // On enregistre
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_nom.'_'.session_id().'.txt',serialize($tab_donnees_csv));
  // On affiche le retour
  Json::add_row( 'filename' , $fichier_nom );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3 : confirmer le traitement d'un import csv (saisie déportée)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='enregistrer_saisie_csv')
{
  if(!$import_info)
  {
    Json::end( FALSE , 'Variable pour récupérer les informations manquante !' );
  }
  $tab_infos = explode('_',$import_info);
  if(count($tab_infos)!=12)
  {
    Json::end( FALSE , 'Variable pour récupérer les informations mal formée !' );
  }
  list( $del , $del , $info_base , $info_user , $del , $info_page_ref , $info_periode , $info_groupe , $info_etat , $del , $del , $del ) = $tab_infos;
  if($info_base!=$_SESSION['BASE'])
  {
    Json::end( FALSE , 'Données d\'un autre établissement !' );
  }
  if($info_user!=$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Données d\'un autre utilisateur !' );
  }
  if($info_page_ref!=Clean::fichier($PAGE_REF))
  {
    Json::end( FALSE , 'Objet de document non concordant ('.$info_page_ref.' reçu / '.$PAGE_REF.' attendu) !' );
  }
  if($info_etat!=Clean::fichier($BILAN_ETAT))
  {
    Json::end( FALSE , 'Étape du bilan non concordante ('.$info_etat.' reçu / '.$BILAN_ETAT.' attendu) !' );
  }
  if($info_periode!=Clean::fichier($JOINTURE_PERIODE))
  {
    Json::end( FALSE , 'Données d\'une autre période ('.$info_periode.' reçu / '.$JOINTURE_PERIODE.' attendu) !' );
  }
  if($info_groupe!=$groupe_id)
  {
    Json::end( FALSE , 'Données d\'un autre regroupement d\'élèves !' );
  }
  $fichier_chemin = CHEMIN_DOSSIER_IMPORT.$import_info.'_'.session_id().'.txt';
  if(!is_file($fichier_chemin))
  {
    Json::end( FALSE , 'Données introuvables !' );
  }
  $contenu = file_get_contents($fichier_chemin);
  $tab_donnees_csv = @unserialize($contenu);
  if($tab_donnees_csv===FALSE)
  {
    Json::end( FALSE , 'Données syntaxiquement incorrectes !' );
  }
  $nb_modifs = 0;
  foreach($tab_donnees_csv as $rubrique_type => $tab_rubrique_type)
  {
    foreach($tab_rubrique_type as $rubrique_id => $tab_eleves)
    {
      if(isset($tab_rubriques[$rubrique_type][$rubrique_id]))
      {
        foreach($tab_eleves as $eleve_id => $tab_objet)
        {
          $cible_nature = ($eleve_id) ? 'eleve'   : 'classe' ;
          $cible_id     = ($eleve_id) ? $eleve_id : $classe_id ;
          foreach($tab_objet as $saisie_objet => $tab_infos)
          {
            $saisie_id = $tab_infos['id'];
            $saisie_valeur = NULL;
            if( ($saisie_objet=='position') && ($tab_infos['val']>=0) && ($rubrique_type=='eval') && ($OBJET=='modifier') && ($cible_nature=='eleve') )
            {
              if($PAGE_COLONNE=='pourcentage') { $saisie_valeur = round($tab_infos['val'],1); }
              else if($PAGE_COLONNE=='moyenne') { $saisie_valeur = round($tab_infos['val']*5,1); }
              else  { $saisie_valeur = ($_SESSION['LIVRET'][$tab_infos['val']]['SEUIL_MIN']+$_SESSION['LIVRET'][$tab_infos['val']]['SEUIL_MAX'])/2; }
            }
            else if( ($saisie_objet=='appreciation') && ($tab_infos['val']) && ( ($rubrique_id>0) || ($OBJET=='tamponner') ) )
            {
              $saisie_valeur = $tab_infos['val'];
            }
            if(!is_null($saisie_valeur))
            {
              if($saisie_id)
              {
                DB_STRUCTURE_LIVRET::DB_modifier_saisie( $saisie_id , $saisie_objet , $saisie_valeur , 'saisie' , $_SESSION['USER_ID'] );
              }
              else
              {
                $saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $rubrique_type , $rubrique_id , $cible_nature , $cible_id , $saisie_objet , $saisie_valeur , 'saisie' , $_SESSION['USER_ID'] );
              }
              $nb_modifs++;
            }
          }
        }
      }
    }
  }
  if(!$nb_modifs)
  {
    Json::end( FALSE , 'Aucune donnée trouvée à enregistrer !' );
  }
  FileSystem::supprimer_fichier( $fichier_chemin , FALSE /*verif_exist*/ );
  $s = ($nb_modifs>1) ? 's' : '' ;
  Json::end( TRUE , $nb_modifs.' donnée'.$s.' enregistrée'.$s.'.' );
}

?>
