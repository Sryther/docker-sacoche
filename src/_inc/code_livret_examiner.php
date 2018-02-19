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
$listing_matieres  = (isset($_POST['f_listing_matieres']))  ? $_POST['f_listing_matieres']  : '' ;
$listing_rubriques = (isset($_POST['f_listing_rubriques'])) ? $_POST['f_listing_rubriques'] : '' ;
$tab_matiere_id    = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$tab_exam_rubrique      = array();
$tab_exam_rubrique_tmp  = explode(',',$listing_rubriques);
$tab_exam_rubrique_type = array('eval','epi','ap','parcours','bilan');
foreach($tab_exam_rubrique_tmp as $rubrique)
{
  list( $rubrique_type , $rubrique_id ) = explode('_',$rubrique) + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  if( in_array($rubrique_type,$tab_exam_rubrique_type) && ( (int)$rubrique_id || ($rubrique_type!='eval') ) )
  {
    $tab_exam_rubrique[$rubrique_type][$rubrique_id] = $rubrique_id ;
  }
}
$liste_matiere_id  = implode(',',$tab_matiere_id);
$liste_rubrique_id = isset($tab_exam_rubrique['eval']) ? implode(',',$tab_exam_rubrique['eval']) : '' ;

// On vérifie les paramètres

if( !$classe_id || (!count($tab_exam_rubrique)) )
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

if( !in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) || ($BILAN_TYPE_ETABL!='college') || ($PAGE_PERIODICITE!='periode') )
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

// Lister les élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves

$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                             : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
if(empty($DB_TAB))
{
  $groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
}
$tab_eleve_id = array();
foreach($DB_TAB as $DB_ROW)
{
  $tab_eleve_id[] = $DB_ROW['user_id'];
}
$liste_eleve_id = implode(',',$tab_eleve_id);

// Il ne s'agit pas de simplement récupérer ce qui est présent dans la table sacoche_livret_saisie ; en effet il faut se restreindre à ce qui est vraiment évalué pour l'élève.
// Du coup le plus simple est de simuler la génération du document, sans sortie html / pdf, mais en notant au fur et à mesure ce qui manque

// (re)calculer les données du livret
// Attention ! On doit calculer des moyennes de classe, pas de groupe !
if(!$is_sous_groupe)
{
  $liste_eleve_id_tmp = $liste_eleve_id;
}
else
{
  $tab_eleve_id_tmp = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_id_tmp[] = $DB_ROW['user_id'];
  }
  $liste_eleve_id_tmp = implode(',',$tab_eleve_id_tmp);
}
calculer_et_enregistrer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $classe_id , $liste_eleve_id_tmp , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES'] , $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['LIVRET_RETROACTIF'] );

// Récupérer les saisies déjà effectuées ou enregistrées pour la période en cours et les périodes antérieures

$tab_saisie       = array();  // [eleve_id][rubrique_type][rubrique_id][saisie_objet] => array(prof_id,saisie_valeur,saisie_origine,listing_profs); avec eleve_id=0 pour position ou appréciation sur la classe
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
    'prof_id'       => $DB_ROW['user_id'] ,
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'saisie_origine'=> $DB_ROW['saisie_origine'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
    'acquis_detail' => $DB_ROW['acquis_detail'] ,
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

// Pas besoin de récupérer les absences / retards

$affichage_assiduite = FALSE ;

// Pas besoin de récupérer les professeurs principaux

$affichage_prof_principal = FALSE ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_resultat_examen = array();
$make_action   = 'examiner';
$make_html     = FALSE;
$make_pdf      = FALSE;
$make_csv      = FALSE;
$make_graph    = FALSE;

$groupe_nom   = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
$groupe_type  = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
$eleves_ordre = 'alpha';
$tab_eleve    = $tab_eleve_id;
$liste_eleve  = $liste_eleve_id;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Inclusion du code commun à plusieurs pages
// ////////////////////////////////////////////////////////////////////////////////////////////////////

require(CHEMIN_DOSSIER_INCLUDE.'noyau_livret_releve_periodique.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat de l'analyse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_pb_rubriques = count($tab_resultat_examen);
if(!$nb_pb_rubriques)
{
  Json::end( TRUE , '<p class="ti"><label class="valide">Aucune saisie manquante trouvée.</label></p>'.print_r($tab_rubriques['ap'],TRUE) );
}
else
{
  // Tentative d'indication des collègues potentiellement concernés
  $tab_exam_rubrique_profs = array();
  if(isset($tab_exam_rubrique['eval']))
  {
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_professeurs_eleves_matieres( $classe_id , $liste_eleve_id , $liste_rubrique_id );
    if(!empty($DB_TAB))
    {
      $tab_tmp = array();
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_tmp[$DB_ROW['matiere_id']][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      }
      foreach($tab_tmp as $matiere_id => $tab_profs)
      {
        // On peut avoir des matières qui n'apparaissent pas sur le bilan officiel
        if(isset($tab_rubriques['eval'][$matiere_id]))
        {
          $rubrique_nom = $tab_rubriques['eval'][$matiere_id]['partie'];
          $nb_profs = count($tab_profs);
          if($nb_profs==1)
          {
            $tab_exam_rubrique_profs[$rubrique_nom] = '['.current($tab_profs).']';
          }
          else if($nb_profs<=3)
          {
            $tab_exam_rubrique_profs[$rubrique_nom] = '['.implode(' ; ',$tab_profs).']';
          }
          else
          {
            $tab_exam_rubrique_profs[$rubrique_nom] = '['.$nb_profs.' professeurs]';
          }
        }
      }
    }
  }
  // Affichage du retour
  $nb_pb_saisies = count($tab_resultat_examen,COUNT_RECURSIVE) - $nb_pb_rubriques ;
  $sr = ($nb_pb_rubriques>1) ? 's' : '' ;
  $ss = ($nb_pb_saisies>1)   ? 's' : '' ;
  Json::add_str('<p class="ti"><label class="danger">'.$nb_pb_saisies.' saisie'.$ss.' manquante'.$ss.' répartie'.$ss.' parmi '.$nb_pb_rubriques.' rubrique'.$sr.' !</label></p>');
  foreach($tab_resultat_examen as $rubrique_nom => $tab)
  {
    $rubrique_indication = isset($tab_exam_rubrique_profs[$rubrique_nom]) ? $rubrique_nom.' '.$tab_exam_rubrique_profs[$rubrique_nom] : $rubrique_nom ;
    Json::add_str('<h3>'.html($rubrique_indication).'</h3>');
    Json::add_str('<ul class="puce"><li>'.implode('</li><li>',$tab).'</li></ul>');
  }
  Json::add_str('<p>&nbsp;</p>');
  Json::end( TRUE );
}

?>
