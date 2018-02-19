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

$tab_action = array('initialiser','charger','voir_detail');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || !$classe_id )
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

if( in_array($BILAN_ETAT,array('1vide','5complet')) )
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}

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
// Cas 3 : re-générer une appréciation / un positionnement / des éléments du programme (soit effacé - NULL - soit figé car reporté manuellement)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='voir_detail')
{
  $rubrique_type = (isset($_POST['f_rubrique_type'])) ? Clean::texte($_POST['f_rubrique_type'])       : '';
  $rubrique_id   = (isset($_POST['f_rubrique_id']))   ? Clean::entier($_POST['f_rubrique_id'])        : 0;
  if( !$eleve_id || !$rubrique_id || ($rubrique_type!='eval') )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // list( $reussite , $origine , $contenu ) = calculer_et_enregistrer_donnee_eleve_rubrique_objet( $saisie_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $rubrique_type , $rubrique_id , $cible_nature , $classe_id , $groupe_id , $eleve_id , $saisie_objet , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES'] , $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['LIVRET_RETROACTIF'] );
  // if(!$reussite)
  // {
    // Json::end( FALSE , $contenu );
  // }
  Json::end( TRUE , 'bla bla bla' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage des données d'un élève indiqué (si initialisation, alors le groupe classe, sauf socle)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Si besoin, fabriquer le formulaire avec la liste des élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves
$groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;

if($ACTION=='initialiser')
{
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                               : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
  }
  $tab_eleve_id = array();
  $form_choix_eleve = '<form action="#" method="post" id="form_choix_eleve"><div><b>'.html($periode_nom.' | '.$classe_nom).' :</b> <button id="go_premier_eleve" type="button" class="go_premier">Premier</button> <button id="go_precedent_eleve" type="button" class="go_precedent">Précédent</button> <select id="go_selection_eleve" name="go_selection" class="b">';
  $form_choix_eleve.= ($PAGE_PERIODICITE!=='cycle') ? '<option value="0">'.html($groupe_nom).'</option>' : '' ;
  foreach($DB_TAB as $DB_ROW)
  {
    $form_choix_eleve .= '<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
    $tab_eleve_id[] = $DB_ROW['user_id'];
  }
  $form_choix_eleve .= '</select> <button id="go_suivant_eleve" type="button" class="go_suivant">Suivant</button> <button id="go_dernier_eleve" type="button" class="go_dernier">Dernier</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button>';
  $form_choix_eleve .= ($PAGE_PERIODICITE!='cycle') ? ( ($mode=='texte') ? ' <button id="change_mode" type="button" class="stats">Interface graphique</button>' : ' <button id="change_mode" type="button" class="texte">Interface détaillée</button>' ) : '' ;
  $form_choix_eleve .= '</div></form><hr />';
  $eleve_id = ($PAGE_PERIODICITE!=='cycle') ? 0 : $DB_TAB[0]['user_id'] ;
  // (re)calculer les données du livret
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

if(!$eleve_id)
{
  $is_appreciation_groupe = TRUE;
}

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  $is_bouton_test_impression = ($eleve_id) ? TRUE : FALSE ;
}

// Récupérer les saisies déjà effectuées ou enregistrées pour la période en cours

$tab_saisie = array();  // [eleve_id][rubrique_type][rubrique_id][saisie_objet] => array(prof_id,saisie_valeur,saisie_origine,listing_profs); avec eleve_id=0 pour position ou appréciation sur la classe
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$eleve_id][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
    'prof_id'       => $DB_ROW['user_id'] ,
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'saisie_origine'=> $DB_ROW['saisie_origine'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
    'acquis_detail' => $DB_ROW['acquis_detail'] ,
  );
}
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
    'prof_id'       => $DB_ROW['user_id'] ,
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'saisie_origine'=> $DB_ROW['saisie_origine'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
  );
}

// Récupérer les professeurs/personnels rattachés aux saisies
// En collège on peut aussi avoir besoin d'autres profs rattachés aux AP ou EPI

$tab_profs = array();
$tab_profs_autres = array();

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
  else if($BILAN_TYPE_ETABL=='college')
  {
    $tab_profs_autres[$DB_ROW['user_id']] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
  }
}

// Récupérer les absences / retards

$affichage_assiduite = ($PAGE_VIE_SCOLAIRE) ? TRUE : FALSE ;

if( $affichage_assiduite && $eleve_id )
{
  $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_assiduite( $periode_id , $eleve_id );
  $tab_assiduite[$eleve_id] = (empty($DB_ROW)) ? array( 'absence' => NULL , 'absence_nj' => NULL , 'retard' => NULL , 'retard_nj' => NULL ) : array( 'absence' => $DB_ROW['assiduite_absence'] , 'absence_nj' => $DB_ROW['assiduite_absence_nj'] , 'retard' => $DB_ROW['assiduite_retard'] , 'retard_nj' => $DB_ROW['assiduite_retard_nj'] ) ;
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
    $texte_prof_principal = 'Professeur principal : sans objet.';
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_action   = 'consulter';
$make_html     = ( ($PAGE_PERIODICITE!='cycle') && ($mode=='graphique') ) ? FALSE : TRUE ;
$make_pdf      = FALSE;
$make_csv      = FALSE;
$make_graph    = ( ($PAGE_PERIODICITE!='cycle') && ($mode=='graphique') ) ? TRUE : FALSE ;

$droit_corriger_appreciation = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_CORRIGER_APPRECIATION'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );

$groupe_type              = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
$eleves_ordre             = 'alpha';
$tab_eleve                = array($eleve_id); // tableau de l'unique élève à considérer
$liste_eleve              = (string)$eleve_id;

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
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Json::add_row( 'script' , ...) a déjà eu lieu

if( ($PAGE_COLONNE!='maitrise') && empty($tab_saisie[$eleve_id]) && empty($is_appreciation_groupe) )
{
  $indication_periode = ($PAGE_PERIODICITE=='periode') ? ' sur la période '.$date_debut.' ~ '.$date_fin : '';
  $releve_HTML = '<div class="danger">Aucun item évalué'.$indication_periode.' selon les paramètres choisis !</div>' ;
}

if($ACTION=='initialiser')
{
  Json::add_row( 'html' , '<h2>Consulter le contenu</h2>' );
  Json::add_row( 'html' , $form_choix_eleve );
  Json::add_row( 'html' , '<div id="zone_resultat_eleve">'.$releve_HTML.'</div>' );
}
else
{
  Json::add_row( 'html' , $releve_HTML );
}

Json::end( TRUE );

?>
