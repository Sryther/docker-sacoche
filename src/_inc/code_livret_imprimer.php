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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='initialiser')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$groupe_id      = ($is_sous_groupe) ? $groupe_id : $classe_id ; // Le groupe = le groupe transmis ou sinon la classe (cas le plus fréquent).

// Autres chaines spécifiques...
$listing_eleves = (isset($_POST['f_listing_eleves']))  ? $_POST['f_listing_eleves']  : '' ;
$tab_eleve_id   = array_filter( Clean::map('entier', explode(',',$listing_eleves) )  , 'positif' );
$liste_eleve_id = implode(',',$tab_eleve_id);

$tab_objet  = array('imprimer','voir_archive');
$tab_action = array('initialiser','imprimer');

$file_memo = CHEMIN_DOSSIER_EXPORT.'imprimer_officiel_'.FileSystem::generer_nom_structure_session().'.txt';

// On vérifie les paramètres principaux

if( !in_array($OBJET,$tab_objet) || !in_array($ACTION,$tab_action) || !$classe_id || ( (!$liste_eleve_id)&&($ACTION!='initialiser') ) )
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
$classe_chef_id      = $DB_ROW['groupe_chef_id'];
$DATE_VERROU         = is_null($DB_ROW['jointure_date_verrou']) ? TODAY_FR : To::datetime_mysql_to_french( $DB_ROW['jointure_date_verrou'] , FALSE /*return_time*/ ) ;
$BILAN_TYPE_ETABL    = in_array($PAGE_RUBRIQUE_TYPE,array('c3_matiere','c4_matiere','c3_socle','c4_socle')) ? 'college' : 'ecole' ;
$CONFIGURATION_REF   = $DB_ROW['configuration_ref'];

if( ($BILAN_ETAT!='5complet') && empty($is_test_impression) )
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}
if( !empty($is_test_impression) && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  Json::end( FALSE , 'Droits insuffisants pour cette action !' );
}

$annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
$annee_scolaire = To::annee_scolaire('code'  ,$annee_decalage);
$annee_siecle   = To::annee_scolaire('siecle',$annee_decalage);

// Forcer la récupération des paramètres du bilan, au cas où un changement de paramétrage viendrait d'être effectué.
// La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
$tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'livret' , $CONFIGURATION_REF );
foreach($tab_configuration as $key => $val)
{
  $_SESSION['OFFICIEL']['LIVRET_'.Clean::upper($key)] = $val;
}
$_SESSION['OFFICIEL']['LIVRET_CONFIG_REF'] = $CONFIGURATION_REF;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les infos sur les seuils enregistrés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
{
  if($PAGE_COLONNE=='maitrise')
  {
    $cycle_id = substr($PAGE_REF,-1);
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos('cycle'.$cycle_id);
  }
  else
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos( $PAGE_REF , $PAGE_COLONNE );
  }
  foreach($DB_TAB as $DB_ROW)
  {
    $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
    $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
    $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
    $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage de la liste des élèves + recalcul des moyennes dans le cas d'impression (sans incidence tant qu'on n'imprime pas, sauf pour la visualisation graphique)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='initialiser')
{
  // Besoin de connaitre le chef d'établissement
  if( ($PAGE_PERIODICITE=='cycle') || ($BILAN_TYPE_ETABL=='college') )
  {
    $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_chef_infos($classe_chef_id);
    if(empty($DB_ROW))
    {
      Json::end( FALSE , "Absence de désignation du chef d'établissement ou directeur d'école pour cette classe !" );
    }
    // Besoin de connaitre au moins un prof principal
    if($BILAN_TYPE_ETABL=='college')
    {
      $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_profs_principaux($classe_id);
      if(empty($DB_TAB))
      {
        Json::end( FALSE , 'Absence de désignation du professeur principal pour la classe '.$classe_nom.' !' );
      }
    }
  }
  // élèves
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                               : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun élève évalué sur la période trouvé dans ce regroupement !' );
  }
  $tab_eleve_id = array();
  $tab_eleve_td = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_id[] = $DB_ROW['user_id'];
    $tab_eleve_td[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  }
  // (re)calculer les données du livret
  if($OBJET=='imprimer')
  {
    // Attention ! On doit calculer des moyennes de classe, pas de groupe !
    if(!$is_sous_groupe)
    {
      $liste_eleve_id = implode(',',$tab_eleve_id);
    }
    else
    {
      $tab_eleve_id_tmp = array();
      $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_eleve_id_tmp[] = $DB_ROW['user_id'];
      }
      $liste_eleve_id = implode(',',$tab_eleve_id_tmp);
    }
    calculer_et_enregistrer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $classe_id , $liste_eleve_id , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES'] , $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['LIVRET_RETROACTIF'] );
  }
  // lister les bilans officiels archivés de l'année courante, affichage du retour
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_archive( $_SESSION['WEBMESTRE_UAI'] , $annee_scolaire , 'livret' /*archive_type*/ , $PAGE_REF /*archive_ref*/ , $periode_id , $tab_eleve_id , FALSE /*with_infos*/ );
  $_SESSION['tmp_droit_voir_archive'] = array(); // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
  foreach($tab_eleve_id as $eleve_id)
  {
    if($OBJET=='imprimer')
    {
      $checked            = (isset($DB_TAB[$eleve_id])) ? '' : ' checked' ;
      $td_date_generation = (isset($DB_TAB[$eleve_id])) ? 'Oui, le '.To::date_mysql_to_french($DB_TAB[$eleve_id][0]['archive_date_generation']) : 'Non' ;
      Json::add_str('<tr id="id_'.$eleve_id.'">');
      Json::add_str(  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$eleve_id.'"'.$checked.' /></td>');
      Json::add_str(  '<td class="label">'.$tab_eleve_td[$eleve_id].'</td>');
      Json::add_str(  '<td class="label hc">'.$td_date_generation.'</td>');
      Json::add_str('</tr>');
    }
    elseif($OBJET=='voir_archive')
    {
      if(!isset($DB_TAB[$eleve_id]))
      {
        $td_date_generation = 'Non, pas encore imprimé' ;
        $td_date_consult_eleve = $td_date_consult_parent = 'Sans objet' ;
      }
      else
      {
        $clef = $DB_TAB[$eleve_id][0]['officiel_archive_id'];
        $_SESSION['tmp_droit_voir_archive'][$clef] = TRUE; // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et a donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
        $td_date_generation = '<a href="acces_archive.php?id='.$clef.'" target="_blank" rel="noopener noreferrer">Oui, le '.To::date_mysql_to_french($DB_TAB[$eleve_id][0]['archive_date_generation']).'</a>' ;
        $td_date_consult_eleve  = in_array( 'ELV' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ) ? ( ($DB_TAB[$eleve_id][0]['archive_date_consultation_eleve'])  ? To::date_mysql_to_french($DB_TAB[$eleve_id][0]['archive_date_consultation_eleve'])  : '-' ) : 'Non autorisé' ;
        $td_date_consult_parent = in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ) ? ( ($DB_TAB[$eleve_id][0]['archive_date_consultation_parent']) ? To::date_mysql_to_french($DB_TAB[$eleve_id][0]['archive_date_consultation_parent']) : '-' ) : 'Non autorisé' ;
      }
      Json::add_str('<tr>');
      Json::add_str(  '<td>'.$tab_eleve_td[$eleve_id].'</td>');
      Json::add_str(  '<td class="hc">'.$td_date_generation.'</td>');
      Json::add_str(  '<td class="hc">'.$td_date_consult_eleve.'</td>');
      Json::add_str(  '<td class="hc">'.$td_date_consult_parent.'</td>');
      Json::add_str('</tr>');
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 2/4 - Le PDF complet est généré ; on archive individuellement les bilans anonymes (qui vont y rester une année scolaire)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==2) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  /* NOUVELLE METHODE */
  // Enregistrement des images nécessaires
  foreach($tab_memo['tab_archive']['image'] as $image_md5 => $image_contenu)
  {
    DB_STRUCTURE_OFFICIEL::DB_ajouter_officiel_archive_image( $image_md5 , $image_contenu );
  }
  unset($tab_memo['tab_archive']['image']);
  // Récupérer les bilans déjà existants pour savoir s'il faut faire un INSERT ou un UPDATE (sinon, un REPLACE efface les dates de consultation)
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_archive( $_SESSION['WEBMESTRE_UAI'] , $annee_scolaire , 'livret' /*archive_type*/ , $PAGE_REF /*archive_ref*/ , $periode_id , array_keys($tab_memo['tab_pages_decoupe_pdf']) /*tab_eleve_id*/ , FALSE /*with_infos*/ );
  $tab_notif = array();
  foreach($tab_memo['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    $tab_image_md5 = $tab_memo['tab_archive']['user'][$eleve_id]['image_md5'];
    unset($tab_memo['tab_archive']['user'][$eleve_id]['image_md5']);
    $tab_contenu = array_merge( $tab_memo['tab_archive']['user'][0] , $tab_memo['tab_archive']['user'][$eleve_id] );
    $archive_contenu = json_encode($tab_contenu);
    if(!isset($DB_TAB[$eleve_id]))
    {
      $livret_archive_id = DB_STRUCTURE_OFFICIEL::DB_ajouter_officiel_archive( $eleve_id , $_SESSION['WEBMESTRE_UAI'] , $annee_scolaire , 'livret' /*archive_type*/ , $PAGE_REF /*archive_ref*/ , $periode_id , $periode_nom , $_SESSION['WEBMESTRE_DENOMINATION'] , VERSION_PROG , $archive_contenu , $tab_image_md5 );
      $tab_notif[$eleve_id] = $livret_archive_id;
    }
    else
    {
      // On ne met pas à jour la date de génération pour conserver la date supposée du conseil de classe et éviter des malentendus ; la date de dernière impression reste archivée en petit sous le bloc titre.
      DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_archive( $DB_TAB[$eleve_id][0]['officiel_archive_id'] , $periode_nom , $_SESSION['WEBMESTRE_DENOMINATION'] , VERSION_PROG , $archive_contenu , $tab_image_md5 );
      $tab_notif[$eleve_id] = $DB_TAB[$eleve_id][0]['officiel_archive_id'];
    }
    // retrait du bilan anonyme des tirages nominatifs (étape suivante)
    unset( $tab_memo['tab_pages_decoupe_pdf'][$eleve_id][0] );
  }
  unset( $tab_memo['tab_archive'] );
  // Notifications (rendues visibles ultérieurement parce que plus simple comme cela)
  if(!empty($tab_notif))
  {
    $abonnement_ref = 'bilan_officiel_visible';
    $is_acces_parent = in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ) ? TRUE : FALSE ;
    $is_acces_enfant = in_array( 'ELV' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ) ? TRUE : FALSE ;
    if( $is_acces_parent || $is_acces_enfant )
    {
      $listing_eleves = implode( ',' , array_keys($tab_notif) );
      $listing_parents = DB_STRUCTURE_NOTIFICATION::DB_lister_parents_listing_id($listing_eleves);
      $listing_users = ($listing_parents) ? $listing_eleves.','.$listing_parents : $listing_eleves ;
      // On peut forcer une notification par mail à tous les parents
      if($notification)
      {
        $listing_abonnes_eleves = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_eleves );
        $listing_abonnes = ($listing_abonnes_eleves) ? $listing_parents.$listing_abonnes_eleves : $listing_parents ;
      }
      else
      {
        $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_users );
      }
      if($listing_abonnes)
      {
        $notification_contenu = 'Bilan officiel disponible : ['.$classe_nom.'] [Livret scolaire] ['.$periode_nom.'].'."\r\n\r\n";
        $tab_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_detail_abonnes_envois( $listing_abonnes , $listing_eleves , $listing_parents );
        foreach($tab_abonnes as $abonne_id => $tab_abonne)
        {
          foreach($tab_abonne as $eleve_id => $notification_intro_eleve)
          {
            if( ( $is_acces_parent && $notification_intro_eleve ) || ( $is_acces_enfant && !$notification_intro_eleve ) )
            {
              $notification_lien = 'Y accéder :'."\r\n".Sesamail::adresse_lien_profond('page=officiel_voir_archive&archive_id='.$tab_notif[$eleve_id])."\r\n";
              DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu.$notification_lien );
            }
          }
        }
      }
    }
  }
  // Enregistrer les informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 3/4 - Le PDF complet est généré ; on découpe individuellement les bilans par responsables puis on zippe l'ensemble
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==3) )
{
  Erreur500::prevention_et_gestion_erreurs_fatales( FALSE /*memory*/ , TRUE /*time*/ );
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  $date = date('Y-m-d');
  $tab_pages_non_anonymes     = array();
  $tab_pages_nombre_par_bilan = array();
  $chemin_temp_pdf = CHEMIN_DOSSIER_EXPORT.'pdf_'.mt_rand().DS;
  FileSystem::creer_ou_vider_dossier($chemin_temp_pdf);
  foreach($tab_memo['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    foreach($tab_tirages as $numero_tirage => $tab)
    {
      list( $eleve_identite , $page_plage , $page_nombre ) = $tab;
      $tab_pages_non_anonymes[]     = $page_plage;
      $tab_pages_nombre_par_bilan[] = $page_nombre;
      $fichier_extraction_chemin = $chemin_temp_pdf.'livret_'.$PAGE_REF.'_'.Clean::fichier($eleve_identite).'_'.$date.'_resp'.$numero_tirage.'.pdf';
      $releve_pdf = new PDFMerger;
      $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$tab_memo['fichier_nom'].'.pdf' , $page_plage ) -> merge( 'file' , $fichier_extraction_chemin );
    }
  }
  $result = FileSystem::zip_fichiers( $chemin_temp_pdf , CHEMIN_DOSSIER_EXPORT , $tab_memo['fichier_nom'].'.zip' );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  FileSystem::supprimer_dossier($chemin_temp_pdf);
  $tab_memo['pages_non_anonymes']     = implode(',',$tab_pages_non_anonymes);
  $tab_memo['pages_nombre_par_bilan'] = implode(' ; ',$tab_pages_nombre_par_bilan);
  unset($tab_memo['tab_pages_decoupe_pdf']);
  // Enregistrer les informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 4/4 - Le PDF complet est généré ; on n'en garde que les bilans non anonymes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==4) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  $releve_pdf = new PDFMerger;
  if($tab_memo['pages_non_anonymes']!='') // Potentiellement possible si on veut imprimer un ou plusieurs bulletins d'élèves sans aucune donnée, ce qui provoque l'erreur "FPDF error: Pagenumber is wrong!"
  {
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$tab_memo['fichier_nom'].'.pdf' , $tab_memo['pages_non_anonymes'] ) -> merge( 'file' , CHEMIN_DOSSIER_EXPORT.$tab_memo['fichier_nom'].'.pdf' );
  }
  Json::add_str('<ul class="puce">');
  Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$tab_memo['fichier_nom'].'.pdf"><span class="file file_pdf">Récupérer, <span class="u">pour impression</span>, l\'ensemble des bilans officiels en un seul document <b>[x]</b>.</span></a></li>');
  Json::add_str(  '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$tab_memo['fichier_nom'].'.zip"><span class="file file_zip">Récupérer, <span class="u">pour archivage</span>, les bilans officiels dans des documents individuels.</span></a></li>');
  Json::add_str('</ul>');
  Json::add_str('<p class="astuce"><b>[x]</b> Nombre de pages par bilan (y prêter attention avant de lancer une impression recto-verso en série) :<br />'.$tab_memo['pages_nombre_par_bilan'].'</p>');
  unset( $tab_memo['fichier_nom'] , $tab_memo['pages_non_anonymes'] , $tab_memo['pages_nombre_par_bilan'] );
  // Supprimer les informations provisoires
  FileSystem::supprimer_fichier( $file_memo );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 1/4 - Génération de l'impression PDF (archive + responsables)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION!='imprimer') || ($etape!=1) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Récupérer le chef d'établissement

$affichage_chef_etabl = ( ($PAGE_PERIODICITE=='cycle') || ($BILAN_TYPE_ETABL=='college') ) ? TRUE : FALSE ;
$texte_chef_etabl = '';

if($affichage_chef_etabl)
{
  $tab_chef_etabl = DB_STRUCTURE_LIVRET::DB_recuperer_chef_infos($classe_chef_id);
  if(empty($tab_chef_etabl))
  {
    Json::end( FALSE , "Absence de désignation du chef d'établissement ou directeur d'école pour cette classe !" );
  }
  $texte_chef_etabl = To::texte_identite($tab_chef_etabl['user_nom'],FALSE,$tab_chef_etabl['user_prenom'],TRUE,$tab_chef_etabl['user_genre']);
}

// Récupérer les professeurs principaux

$affichage_prof_principal = ($BILAN_TYPE_ETABL=='college') ? TRUE : FALSE ;
$texte_prof_principal = '';

if( $affichage_prof_principal )
{
  $tab_pp = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_profs_principaux($classe_id);
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Absence de désignation du professeur principal pour la classe '.$classe_nom.' !' );
  }
  else if(count($DB_TAB)==1)
  {
    $DB_ROW = $DB_TAB[0];
    $tab_pp[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    $texte_prof_principal = 'Professeur principal : '.$tab_pp[$DB_ROW['user_id']];
  }
  else
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_pp[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
    $texte_prof_principal = 'Professeurs principaux : '.implode(' ; ',$tab_pp);
  }
}

// Récupérer les saisies déjà effectuées ou enregistrées pour la page de livret concernée
// Comme c'est pour l'impression PDF, on laisse tomber 'saisie_origine' et 'acquis_detail'

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note);

$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
    'prof_id'       => $DB_ROW['user_id'] ,
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
  );
}
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
    'prof_id'       => $DB_ROW['user_id'] ,
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
  );
}

// Récupérer les professeurs/personnels rattachés aux saisies OU instituteurs-trices rattaché(e)s à la classe
// En collège on peut aussi avoir besoin d'autres profs rattachés aux AP ou EPI

$tab_profs = array();
$tab_profs_autres = array();

if($BILAN_TYPE_ETABL=='ecole')
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'professeur' , 1 /*statut*/ , 'classe' /*groupe_type*/ , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id, user_genre, user_nom, user_prenom' );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Absence d\'enseignant rattaché à la classe '.$classe_nom.' !' );
  }
  else
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_profs[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
  }
}
else if($BILAN_TYPE_ETABL=='college')
{
  foreach($tab_saisie as $tab_tmp_eleve)
  {
    foreach($tab_tmp_eleve as $tab_tmp_rubrique)
    {
      foreach($tab_tmp_rubrique as $tab_tmp_saisie)
      {
        foreach($tab_tmp_saisie as $tab_tmp_infos)
        {
          if($tab_tmp_infos['prof_id'])
          {
            $tab_profs[$tab_tmp_infos['prof_id']] = $tab_tmp_infos['prof_id'];
          }
          if($tab_tmp_infos['listing_profs'])
          {
            $tab = explode(',',$tab_tmp_infos['listing_profs']);
            foreach($tab as $prof_id)
            {
              $tab_profs[$prof_id] = $prof_id;
            }
          }
        }
      }
    }
  }
  $tab_profils_types = array('professeur','directeur');
  $listing_champs = 'user_id, user_sconet_id, user_genre, user_nom, user_prenom';
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , $listing_champs , FALSE /*with_classe*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if(isset($tab_profs[$DB_ROW['user_id']]))
    {
      $tab_profs[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
    else
    {
      $tab_profs_autres[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
  }
}

// Récupérer les absences / retards

$affichage_assiduite = ($PAGE_VIE_SCOLAIRE) ? TRUE : FALSE ;
$tab_assiduite = array_fill_keys( $tab_eleve_id , array( 'absence' => NULL , 'absence_nj' => NULL , 'retard' => NULL , 'retard_nj' => NULL ) );  // [eleve_id] => array(absence,absence_nj,retard,retard_nj);

if($affichage_assiduite)
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , $liste_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_assiduite[$DB_ROW['user_id']] = array( 'absence' => $DB_ROW['assiduite_absence'] , 'absence_nj' => $DB_ROW['assiduite_absence_nj'] , 'retard' => $DB_ROW['assiduite_retard'] , 'retard_nj' => $DB_ROW['assiduite_retard_nj'] );
  }
}

// Signatures numériques

$tab_signature = array( 'chef'=>NULL , 'prof'=>NULL , 'tmp'=>array() );
if($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']!='sans')
{
  // On les récupère
  if($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='tampon')
  {
    $listing_prof_id = '0'; // tampon
  }
  else
  {
    // tampon
    $tab_id = array(0);
    // chef
    if($affichage_chef_etabl)
    {
      $tab_id[] = $classe_chef_id;
    }
    // P.P.
    if( $affichage_prof_principal && ($PAGE_PERIODICITE=='cycle') )
    {
      $tab_id = array_merge( $tab_id , array_keys($tab_pp) );
    }
    // instit
    if($BILAN_TYPE_ETABL=='ecole')
    {
      $tab_id = array_merge( $tab_id , array_keys($tab_profs) );
    }
    $listing_prof_id = implode(',',$tab_id);
  }
  $DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images( $listing_prof_id , 'signature' );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_signature['tmp'][$DB_ROW['user_id']] = array(
      'contenu' => $DB_ROW['image_contenu'] ,
      'format'  => $DB_ROW['image_format']  ,
      'largeur' => $DB_ROW['image_largeur'] ,
      'hauteur' => $DB_ROW['image_hauteur'] ,
    );
  }
  // On affecte la bonne image à chacun
  if( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='tampon') || ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature_ou_tampon') )
  {
    if(!empty($tab_signature['tmp'][0]))
    {
      $tab_signature['chef'] = $tab_signature['tmp'][0];
      $tab_signature['prof'] = $tab_signature['tmp'][0];
    }
  }
  if( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') || ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature_ou_tampon') )
  {
    if( $affichage_chef_etabl && !empty($tab_signature['tmp'][$classe_chef_id]) )
    {
      $tab_signature['chef'] = $tab_signature['tmp'][$classe_chef_id];
    }
    if( $affichage_prof_principal && ($PAGE_PERIODICITE=='cycle') )
    {
      foreach($tab_pp as $user_id => $user_txt )
      {
        if(!empty($tab_signature['tmp'][$user_id]))
        {
          $tab_signature['prof'] = $tab_signature['tmp'][$user_id];
          break;
        }
      }
    }
    if($BILAN_TYPE_ETABL=='ecole')
    {
      foreach($tab_profs as $user_id => $user_txt )
      {
        if(!empty($tab_signature['tmp'][$user_id]))
        {
          $tab_signature['prof'] = $tab_signature['tmp'][$user_id];
          break;
        }
      }
    }
  }
}
unset($tab_signature['tmp']);

// Récupérer les noms et coordonnées des responsables, ou simplement l'info de savoir si leurs adresses sont différentes

$tab_destinataires = array();  // [eleve_id][i] => array(...) | 'archive' | NULL ;
$tab_civilite = array( 'I'=>'' , 'M'=>'M.' , 'F'=>'Mme' );
$pays_majoritaire = DB_STRUCTURE_OFFICIEL::DB_recuperer_pays_majoritaire();
$DB_TAB = ( ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']!='non') || ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') ) ? DB_STRUCTURE_OFFICIEL::DB_lister_adresses_parents_for_enfants($liste_eleve_id) : array() ;
foreach($tab_eleve_id as $eleve_id)
{
  if( (isset($DB_TAB[$eleve_id][0]['adresse_pays_nom'])) && ($DB_TAB[$eleve_id][0]['adresse_pays_nom']==$pays_majoritaire) ) {$DB_TAB[$eleve_id][0]['adresse_pays_nom']='';}
  if( (isset($DB_TAB[$eleve_id][1]['adresse_pays_nom'])) && ($DB_TAB[$eleve_id][1]['adresse_pays_nom']==$pays_majoritaire) ) {$DB_TAB[$eleve_id][1]['adresse_pays_nom']='';}
  $tab_coords_resp1 = (isset($DB_TAB[$eleve_id][0])) ? array_filter(array($tab_civilite[$DB_TAB[$eleve_id][0]['user_genre']].' '.$DB_TAB[$eleve_id][0]['user_nom'].' '.$DB_TAB[$eleve_id][0]['user_prenom'],$DB_TAB[$eleve_id][0]['adresse_ligne1'],$DB_TAB[$eleve_id][0]['adresse_ligne2'],$DB_TAB[$eleve_id][0]['adresse_ligne3'],$DB_TAB[$eleve_id][0]['adresse_ligne4'],$DB_TAB[$eleve_id][0]['adresse_postal_code'].' '.$DB_TAB[$eleve_id][0]['adresse_postal_libelle'],$DB_TAB[$eleve_id][0]['adresse_pays_nom'])) : NULL ;
  $tab_coords_resp2 = (isset($DB_TAB[$eleve_id][1])) ? array_filter(array($tab_civilite[$DB_TAB[$eleve_id][1]['user_genre']].' '.$DB_TAB[$eleve_id][1]['user_nom'].' '.$DB_TAB[$eleve_id][1]['user_prenom'],$DB_TAB[$eleve_id][1]['adresse_ligne1'],$DB_TAB[$eleve_id][1]['adresse_ligne2'],$DB_TAB[$eleve_id][1]['adresse_ligne3'],$DB_TAB[$eleve_id][1]['adresse_ligne4'],$DB_TAB[$eleve_id][1]['adresse_postal_code'].' '.$DB_TAB[$eleve_id][1]['adresse_postal_libelle'],$DB_TAB[$eleve_id][1]['adresse_pays_nom'])) : NULL ;
  // La copie du bilan qui sera 'archivée' jusqu'à la fin de l'année scolaire.
  $tab_destinataires[$eleve_id][0] = 'archive' ;
  // Tirage pour le 1er responsable
  $tab_destinataires[$eleve_id][1] = ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='non') ? NULL : $tab_coords_resp1 ;
  // Tirage pour le 2e responsable
  if( ( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_de_force') && ($tab_coords_resp2!=NULL) ) || ( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') && ($tab_coords_resp2!=NULL) && ( ($tab_coords_resp1==NULL) || (array_slice($tab_coords_resp2,1)!=array_slice($tab_coords_resp1,1)) ) ) )
  {
    $tab_destinataires[$eleve_id][2] = ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='non') ? NULL : $tab_coords_resp2 ;
  }
  // Ajouter sur le 1er tirage le nom du 2e responsable si les adresses sont identiques et que le 2e bilan n'est imprimé qu'en cas d'adresses différentes
  if( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') && ($tab_destinataires[$eleve_id][1]!=NULL) && (!isset($tab_destinataires[$eleve_id][2])) )
  {
    array_unshift($tab_destinataires[$eleve_id][1], $tab_coords_resp2[0]);
  }
}

// Logo du MEN
$chemin_fichier_menesr = CHEMIN_DOSSIER_IMG.'logo_men_2017.png';
$tab_infos = getimagesize($chemin_fichier_menesr);
list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
$tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
$image_format = $tab_extension_types[$image_type];
$tab_menesr_logo = array(
  'contenu' => base64_encode(file_get_contents($chemin_fichier_menesr)), // iVBORw0KGgoAAAANSUhEUgAAAq0AAAN/CAMAAADDAxEJAAABLFBMVEX...
  'format'  => $image_format  , // png
  'largeur' => $image_largeur , // 685
  'hauteur' => $image_hauteur , // 895
);

$tab_etabl_logo = NULL;
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'logo'))
{
  $DB_ROW = DB_STRUCTURE_IMAGE::DB_recuperer_image( 0 /*user_id*/ , 'logo' );
  if(!empty($DB_ROW))
  {
    $tab_etabl_logo = array(
      'contenu' => $DB_ROW['image_contenu'] ,
      'format'  => $DB_ROW['image_format']  ,
      'largeur' => $DB_ROW['image_largeur'] ,
      'hauteur' => $DB_ROW['image_hauteur'] ,
    );
  }
}

// Récupérer le logo de l'établissement

$tab_etabl_logo = NULL;
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'logo'))
{
  $DB_ROW = DB_STRUCTURE_IMAGE::DB_recuperer_image( 0 /*user_id*/ , 'logo' );
  if(!empty($DB_ROW))
  {
    $tab_etabl_logo = array(
      'contenu' => $DB_ROW['image_contenu'] ,
      'format'  => $DB_ROW['image_format']  ,
      'largeur' => $DB_ROW['image_largeur'] ,
      'hauteur' => $DB_ROW['image_hauteur'] ,
    );
  }
}

// Bloc des coordonnées de l'établissement

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

// Bloc des titres du document
$titre_moment  = ($PAGE_PERIODICITE=='cycle') ? $PAGE_MOMENT : 'Cycle '.substr($PAGE_RUBRIQUE_TYPE,1,1).' - '.$PAGE_TITRE_CLASSE ;
$titre_periode = ($PAGE_PERIODICITE=='cycle') ? To::annee_scolaire('texte',$annee_decalage) : To::annee_scolaire('texte',$annee_decalage).' - '.$periode_nom ;
$tab_bloc_titres = array( 0 => Clean::upper(Lang::_("Livret Scolaire")) , 1 => $titre_moment , 2 => $titre_periode , 3 =>$classe_nom );

// Tag date heure initiales

$tag_date_heure_initiales = date('d/m/Y H:i').' '.To::texte_identite($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_action  = 'imprimer';
$make_html    = FALSE;
$make_pdf     = TRUE;
$make_csv     = FALSE;
$make_graph   = FALSE;

$groupe_nom   = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
$groupe_type  = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
$eleves_ordre = 'alpha';
$tab_eleve    = $tab_eleve_id;
$liste_eleve  = $liste_eleve_id;

$tab_pages_decoupe_pdf = array();
$tab_archive = array(
  'image'   => array(),
  'user'    => array(),
  'session' => array(),
);
// 'ENVELOPPE' => $_SESSION['ENVELOPPE'], // Pas besoin car pas de bloc adresse sur la version archivée
// 'OFFICIEL'  => $_SESSION['OFFICIEL'],  // Pas besoin car pas de bloc adresse sur la version archivée
if( in_array($PAGE_COLONNE,array('objectif','position')) )
{
  $tab_archive['session']['LIVRET'] = $_SESSION['LIVRET']; // Besoin pour OutilBilan::determiner_degre_maitrise() et la légende, en cas de positionnement sans note ni pourcentage ou en cas de maitrise du socle
}

$orientation  = ($PAGE_REF!='cycle1') ? 'portrait' : 'landscape' ;
$couleur      = $_SESSION['OFFICIEL']['LIVRET_COULEUR'];
$fond         = $_SESSION['OFFICIEL']['LIVRET_FOND'];
$legende      = 'oui';
$marge_gauche =  5; // 10 sur le LSU
$marge_droite =  5; // 10 sur le LSU
$marge_haut   =  5; // 10 sur le LSU page 2 uniquement
$marge_bas    = 10; // 15 sur le LSU

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Inclusion du code commun à plusieurs pages
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($PAGE_COLONNE=='maitrise')
{
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_livret_fin_cycle_socle.php');
}
elseif($PAGE_REF=='cycle1')
{
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_livret_fin_maternelle.php');
}
else
{
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_livret_releve_periodique.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat (pas grand chose, car la découpe du PDF intervient lors d'appels ajax ultérieurs, sauf s'il s'agissait d'un test d'impression auquel cas le filigrane a déjà été ajouté et on s'arrête là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($is_test_impression))
{
  if(!count($tab_pages_decoupe_pdf))
  {
    $indication_periode = ($PAGE_PERIODICITE=='periode') ? ' sur la période '.$date_debut.' ~ '.$date_fin : '';
    Json::end( FALSE , 'Aucune donnée trouvée pour le ou les élèves concernés'.$indication_periode.' !' );
  }
  unset($tab_archive['session']);
  $tab_memo = array(
    'tab_archive'           => $tab_archive,
    'fichier_nom'           => $fichier_nom,
    'tab_pages_decoupe_pdf' => $tab_pages_decoupe_pdf,
  );
  // Enregistrer les informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  Json::end( TRUE );
}
else
{
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom.'.pdf' );
}

?>
