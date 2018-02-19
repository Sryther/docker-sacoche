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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$OBJET        = (isset($_POST['f_objet']))        ? Clean::texte($_POST['f_objet'])        : '';
$ACTION       = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])       : '';
$BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])   : '';
$periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])     : 0;
$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;
$import_info  = (isset($_POST['f_import_info']))  ? Clean::texte($_POST['f_import_info'])  : '';
// Autres chaines spécifiques...
$listing_matieres = (isset($_POST['f_listing_matieres'])) ? $_POST['f_listing_matieres'] : '' ;
$listing_piliers  = (isset($_POST['f_listing_piliers']))  ? $_POST['f_listing_piliers']  : '' ;
$tab_matiere_id = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$tab_pilier_id  = array_filter( Clean::map('entier', explode(',',$listing_piliers) )  , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere_id);
$liste_pilier_id  = implode(',',$tab_pilier_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;
$groupe_id      = ($groupe_id) ? $groupe_id : $classe_id ; // Le groupe = le groupe transmis ou sinon la classe (cas le plus fréquent).

$separateur = ';';

$tab_objet  = array('modifier','tamponner');
$tab_action = array('generer_csv_vierge','uploader_saisie_csv','enregistrer_saisie_csv');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!isset($tab_types[$BILAN_TYPE])) || (!in_array($OBJET,$tab_objet)) || !$periode_id || !$classe_id )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
if(empty($DB_ROW))
{
  Json::end( FALSE , 'Association classe / période introuvable !' );
}
$date_debut        = $DB_ROW['jointure_date_debut'];
$date_fin          = $DB_ROW['jointure_date_fin'];
$BILAN_ETAT        = $DB_ROW['officiel_'.$BILAN_TYPE];
$periode_nom       = $DB_ROW['periode_nom'];
$classe_nom        = $DB_ROW['groupe_nom'];
$CONFIGURATION_REF = $DB_ROW['configuration_ref'];

if(!$BILAN_ETAT)
{
  Json::end( FALSE , 'Bilan introuvable !' );
}
if(!in_array($OBJET.$BILAN_ETAT,array('modifier2rubrique','modifier3mixte','tamponner3mixte','tamponner4synthese')))
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}

// Récupérer, si besoin, les paramètres du bilan (on ne force pas l'actualisation si déjà en session car on est déjà en train de consulter le bilan).
// La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
if( !isset($_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE).'_CONFIG_REF']) || ($_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE).'_CONFIG_REF']!=$CONFIGURATION_REF) )
{
  $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( $BILAN_TYPE , $CONFIGURATION_REF );
  foreach($tab_configuration as $key => $val)
  {
    $_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE.'_'.$key)] = $val;
  }
  $_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE).'_CONFIG_REF'] = $CONFIGURATION_REF;
}

if($ACTION!='enregistrer_saisie_csv')
{

  // Période concernée
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Le regroupement et la période ne sont pas reliés !' );
  }
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];

  // Rubriques concernées
  $tab_rubriques = array() ;
  if($OBJET=='modifier')
  {
    $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_matieres_travaillees( $classe_id , $liste_matiere_id , $date_mysql_debut , $date_mysql_fin , FALSE /*only_if_synthese*/ , $_SESSION['USER_ID'] );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_rubriques[$DB_ROW['rubrique_id']] = $DB_ROW['rubrique_nom'];
    }
  }
  else if($OBJET=='tamponner')
  {
    $tab_rubriques = array( 0 => 'Synthèse générale' ) ;
  }

  // Élèves concernés
  $groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                               : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
  }
  $csv_lignes_eleves = ($BILAN_TYPE=='bulletin') ? array( 0 => 'groupe_'.$groupe_id.$separateur.'"Classe / Groupe"'.$separateur ) : array() ;
  $tab_eleve_id      = ($BILAN_TYPE=='bulletin') ? array( 0 => 'Classe / Groupe' )                                                : array() ;
  foreach($DB_TAB as $DB_ROW)
  {
    $csv_lignes_eleves[$DB_ROW['user_id']] = 'eleve_'.$DB_ROW['user_id'].$separateur.'"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
    $tab_eleve_id[$DB_ROW['user_id']]      = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
  }

}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1 : générer un CSV pour une saisie déportée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 * Le CSV proposé est VIERGE à la fois par facilité, parce qu'il ne n'agit pas d'un archivage,
 * et pour éviter - dans le cas d'un bulletin - de figer des moyennes sans trop le vouloir.
*/
if($ACTION=='generer_csv_vierge')
{
  $groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $export_csv = $BILAN_TYPE.'_'.$BILAN_ETAT.'_'.$_SESSION['USER_ID'].'_'.$periode_id.'_'.$groupe_id.$separateur.'Saisie déportée - '.$tab_types[$BILAN_TYPE]['titre'].' - '.$periode_nom.' - '.$groupe_nom."\r\n\r\n";
  $with_note = ( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] && ($OBJET=='modifier') ) ? TRUE : FALSE ;
  $type_note = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? 'Note' : 'Pourcentage' ;
  $rubrique_ligne_fin = ($with_note) ? $type_note.$separateur.'Appréciation' : 'Appréciation' ;
  foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
  {
    $export_csv .= 'rubrique_'.$rubrique_id.$separateur.'"'.$rubrique_nom.'"'.$separateur.$rubrique_ligne_fin."\r\n";
    foreach($csv_lignes_eleves as $eleve_id => $eleve_ligne_debut)
    {
      $export_csv .= $eleve_ligne_debut;
      $export_csv .= ($with_note) ? $separateur."\r\n" : "\r\n" ;
    }
    $export_csv .= "\r\n"; // une valeur NULL donnera une chaine vide
  }
  $fnom_export = 'saisie_deportee_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.Clean::fichier($BILAN_TYPE).'_'.Clean::fichier($periode_nom).'_'.Clean::fichier($groupe_nom).'_'.$BILAN_ETAT.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.csv';
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom_export , To::csv($export_csv) );
  Json::end( TRUE , $fnom_export );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2 : réception d'un import csv (saisie déportée)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='uploader_saisie_csv')
{
  // Récupération du fichier
  $fichier_nom = 'saisie_deportee_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.Clean::fichier($BILAN_TYPE).'_'.$periode_id.'_'.$groupe_id.'_'.$BILAN_ETAT.'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
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
  list($string_references,$titre) = $tab_elements + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  $tab_references = explode('_',$string_references);
  if(count($tab_references)!=5)
  {
    Json::end( FALSE , 'Ligne d\'en-tête invalide !' );
  }
  list($csv_bilan_type,$csv_bilan_etat,$csv_user_id,$csv_periode_id,$csv_groupe_id) = $tab_references;
  if($csv_bilan_type!=$BILAN_TYPE)
  {
    Json::end( FALSE , 'Type de bilan non concordant ('.$csv_bilan_type.' reçu / '.$BILAN_TYPE.' attendu) !' );
  }
  if($csv_bilan_etat!=$BILAN_ETAT)
  {
    Json::end( FALSE , 'Étape du bilan non concordante ('.$csv_bilan_etat.' reçu / '.$BILAN_ETAT.' attendu) !' );
  }
  if($csv_user_id!=$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre utilisateur !' );
  }
  if($csv_periode_id!=$periode_id)
  {
    Json::end( FALSE , 'Fichier transmis d\'une autre période !' );
  }
  if($csv_groupe_id!=$groupe_id)
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre regroupement d\'élèves !' );
  }
  // On y va
  $rubrique_id = NULL;
  $tab_donnees_csv = array(); // [rubrique_id][eleve_id][type(moyenne|appreciation)] => [valeur][idem|insert|update]
  $with_note = ( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] && ($OBJET=='modifier') ) ? TRUE : FALSE ;
  $nb_colonnes = ($with_note) ? 4 : 3 ;
  foreach ($tab_lignes as $tab_elements)
  {
    $tab_elements = array_slice($tab_elements,0,$nb_colonnes);
    if(count($tab_elements)==$nb_colonnes)
    {
      list($col1,$col2,$col3,$col4) = $tab_elements;
      $tab_ref1 = explode('_',$col1);
      if(count($tab_ref1)==2)
      {
        list( $ref1_objet , $ref1_valeur ) = $tab_ref1;
        $ref1_valeur = (int)$ref1_valeur;
        // Une nouvelle rubrique ; on vérifie sa validité
        if($ref1_objet=='rubrique')
        {
          if( ( $ref1_valeur && ($OBJET=='modifier') && isset($tab_rubriques[$ref1_valeur]) ) || ( !$ref1_valeur && ($OBJET=='tamponner') ) )
          {
            $rubrique_id = $ref1_valeur;
            $longueur_maxi = ($rubrique_id) ? $_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR'] : $_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR'] ;
          }
          else
          {
            $rubrique_id = NULL;
          }
        }
        // Un nouveau groupe (appréciation sur le groupe) ; on vérifie sa validité
        elseif( ($ref1_objet=='groupe') && ($ref1_valeur==$groupe_id) && ($BILAN_TYPE=='bulletin') && ($rubrique_id!==NULL) )
        {
          $eleve_id = 0;
          $appreciation = ($with_note) ? Clean::appreciation($col4) : Clean::appreciation($col3) ;
          if($appreciation)
          {
            $tab_donnees_csv[$rubrique_id][$eleve_id]['appreciation'] = array( 'val'=>mb_substr($appreciation,0,$longueur_maxi) , 'mode'=>'insert' );
          }
        }
        // Un nouvel élève ; on vérifie sa validité
        elseif( ($ref1_objet=='eleve') && $ref1_valeur && isset($tab_eleve_id[$ref1_valeur]) && ($rubrique_id!==NULL) )
        {
          $eleve_id = $ref1_valeur;
          if( ($with_note) && ($col3!=='') )
          {
            $moyenne = Clean::decimal($col3);
            $tab_donnees_csv[$rubrique_id][$eleve_id]['moyenne'] = array( 'val'=>$moyenne , 'mode'=>'insert' );
          }
          $appreciation = ($with_note) ? Clean::appreciation($col4) : Clean::appreciation($col3) ;
          if($appreciation)
          {
            $tab_donnees_csv[$rubrique_id][$eleve_id]['appreciation'] = array( 'val'=>mb_substr($appreciation,0,$longueur_maxi) , 'mode'=>'insert' );
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
  // Cette partie de code est inspirée de [code_officiel_archiver.php]
  $liste_eleve_id = implode(',',array_keys($tab_eleve_id));
  if($BILAN_TYPE=='bulletin')
  {
    $DB_TAB = array_merge
    (
      DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $BILAN_TYPE , $periode_id , $classe_id      , $_SESSION['USER_ID'] , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ ),
      DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , $_SESSION['USER_ID'] , TRUE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ )
    );
  }
  else
  {
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , $_SESSION['USER_ID'] , TRUE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
  }
  // Les appréciations
  foreach($DB_TAB as $key => $DB_ROW)
  {
    if($DB_ROW['prof_id'])
    {
      if(isset($tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['appreciation']))
      {
        if( $DB_ROW['eleve_id'] || ($DB_ROW['groupe_id']==$groupe_id) )
        {
          $appreciation = $tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['appreciation']['val'];
          $tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['appreciation']['mode'] = ( $appreciation == $DB_ROW['saisie_appreciation'] ) ? 'idem' : 'update' ;
        }
      }
      unset($DB_TAB[$key]);
    }
  }
  // Les notes
  if($with_note)
  {
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if(isset($tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['moyenne']))
      {
        $note = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? round($tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['moyenne']['val'],1) : round($tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['moyenne']['val']/5,1) ;
        $tab_donnees_csv[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['moyenne']['mode'] = ( $note == $DB_ROW['saisie_note'] ) ? 'idem' : 'update' ;
      }
      unset($DB_TAB[$key]);
    }
  }
  // Affichage du résultat de l'analyse et suppression au passage des absences de modifs
  $nb_modifs = 0;
  Json::add_row( 'html' , '<thead><tr><th colspan="3">'.html($titre).'</th></tr></thead><tbody>' );
  foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
  {
    if(isset($tab_donnees_csv[$rubrique_id]))
    {
      Json::add_row( 'html' , '<tr><th colspan="3">'.html($rubrique_nom).'</th></tr>' );
      foreach($tab_donnees_csv[$rubrique_id] as $eleve_id => $tab_infos)
      {
        if(isset($tab_infos['moyenne']))
        {
          $note = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $tab_infos['moyenne']['val'] : $tab_infos['moyenne']['val'].'&nbsp;%' ;
          Json::add_row( 'html' , '<tr class="'.$tab_infos['moyenne']['mode'].'"><td>'.html($tab_eleve_id[$eleve_id]).'</td><td>Moyenne</td><td>'.$note.'</td></tr>' );
          if($tab_infos['moyenne']['mode']!='idem')
          {
            $tab_donnees_csv[$rubrique_id][$eleve_id]['moyenne'] = $tab_infos['moyenne']['val'];
            $nb_modifs++;
          }
          else
          {
            unset($tab_donnees_csv[$rubrique_id][$eleve_id]['moyenne']);
          }
        }
        if(isset($tab_infos['appreciation']))
        {
          $appreciation = $tab_infos['appreciation']['val'];
          Json::add_row( 'html' , '<tr class="'.$tab_infos['appreciation']['mode'].'"><td>'.html($tab_eleve_id[$eleve_id]).'</td><td>Appreciation</td><td>'.html($appreciation).'</td></tr>' );
          if($tab_infos['appreciation']['mode']!='idem')
          {
            $tab_donnees_csv[$rubrique_id][$eleve_id]['appreciation'] = $appreciation;
            $nb_modifs++;
          }
          else
          {
            unset($tab_donnees_csv[$rubrique_id][$eleve_id]['appreciation']);
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
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_nom.'_'.FileSystem::generer_nom_structure_session().'.txt',serialize($tab_donnees_csv));
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
    Json::end( FALSE , 'Valeur pour récupérer les informations manquante !' );
  }
  $tab_infos = explode('_',$import_info);
  if(count($tab_infos)!=11)
  {
    Json::end( FALSE , 'Valeur pour récupérer les informations mal formée !' );
  }
  list( $del , $del , $info_base , $info_user , $info_bilan , $info_periode , $info_groupe , $info_etat , $del , $del , $del ) = $tab_infos;
  if($info_base!=$_SESSION['BASE'])
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre établissement !' );
  }
  if($info_user!=$_SESSION['USER_ID'])
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre utilisateur !' );
  }
  if($info_bilan!=$BILAN_TYPE)
  {
    Json::end( FALSE , 'Type de bilan non concordant ('.$info_bilan.' reçu / '.$BILAN_TYPE.' attendu) !' );
  }
  if($info_etat!=$BILAN_ETAT)
  {
    Json::end( FALSE , 'Étape du bilan non concordante ('.$info_etat.' reçu / '.$BILAN_ETAT.' attendu) !' );
  }
  if($info_periode!=$periode_id)
  {
    Json::end( FALSE , 'Fichier transmis d\'une autre période !' );
  }
  if($info_groupe!=$groupe_id)
  {
    Json::end( FALSE , 'Fichier transmis d\'un autre regroupement d\'élèves !' );
  }
  $fichier_chemin = CHEMIN_DOSSIER_IMPORT.$import_info.'_'.FileSystem::generer_nom_structure_session().'.txt';
  if(!is_file($fichier_chemin))
  {
    Json::end( FALSE , 'Le fichier transmis est introuvable !' );
  }
  $contenu = file_get_contents($fichier_chemin);
  $tab_donnees_csv = @unserialize($contenu);
  if($tab_donnees_csv===FALSE)
  {
    Json::end( FALSE , 'Le fichier transmis est syntaxiquement incorrect !' );
  }
  $nb_modifs = 0;
  foreach($tab_donnees_csv as $rubrique_id => $tab_eleves)
  {
    foreach($tab_eleves as $eleve_id => $tab_saisies)
    {
      if(isset($tab_saisies['moyenne']))
      {
        if( ($tab_saisies['moyenne']>=0) && ($OBJET=='modifier') && ($BILAN_TYPE=='bulletin') && ($rubrique_id>0) )
        {
          enregistrer_note( $BILAN_TYPE , $periode_id , $eleve_id , $rubrique_id , $tab_saisies['moyenne'] );
          $nb_modifs++;
        }
      }
      if(isset($tab_saisies['appreciation']))
      {
        if( ($tab_saisies['appreciation']) && ( ($rubrique_id>0) || ($OBJET=='tamponner') ) )
        {
          enregistrer_appreciation( $BILAN_TYPE , $periode_id , $eleve_id , $classe_id , $groupe_id , $rubrique_id , $_SESSION['USER_ID'] , $tab_saisies['appreciation'] );
          $nb_modifs++;
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
