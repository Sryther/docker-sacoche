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

$BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])   : '';
$periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])     : 0;
$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;
// Autres chaines spécifiques...
$listing_matieres  = (isset($_POST['f_listing_matieres']))  ? $_POST['f_listing_matieres']  : '' ;
$listing_rubriques = (isset($_POST['f_listing_rubriques'])) ? $_POST['f_listing_rubriques'] : '' ;
$tab_matiere_id  = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$tab_rubrique_id = Clean::map('entier',explode(',',$listing_rubriques) ); // Pas de array_filter(...,'positif') car la valeur 0 est autorisée
$liste_matiere_id  = implode(',',$tab_matiere_id);
$liste_rubrique_id = implode(',',$tab_rubrique_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

// On vérifie les paramètres

if( (!isset($tab_types[$BILAN_TYPE])) || !$periode_id || !$classe_id || (!count($tab_rubrique_id)) )
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
if(!in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')))
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}

// Forcer la récupération des paramètres du bilan, au cas où un changement de paramétrage viendrait d'être effectué.
// La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
$tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( $BILAN_TYPE , $CONFIGURATION_REF );
foreach($tab_configuration as $key => $val)
{
  $_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE.'_'.$key)] = $val;
}
$_SESSION['OFFICIEL'][Clean::upper($BILAN_TYPE).'_CONFIG_REF'] = $CONFIGURATION_REF;

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

// Il ne s'agit pas de simplement récupérer ce qui est présent dans la table sacoche_officiel_saisie ; en effet :
// - pour un relevé de notes ou un bulletin il faut se restreindre à ce qui est vraiment évalué pour l'élève
// - pour une maîtrise du socle on peut se restreindre à ce qui contient des éléments
// Du coup le plus simple est de simuler la génération du document, sans sortie html / pdf, mais en notant au fur et à mesure ce qui manque

// (re)calculer les moyennes des élèves

if( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] )
{
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
  calculer_et_enregistrer_moyennes_eleves_bulletin( $periode_id , $classe_id , $liste_eleve_id_tmp , $liste_rubrique_id , $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'] , FALSE /*memo_moyennes_classe*/ , FALSE /*memo_moyennes_generale*/ );
}

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note);
$tab_moyenne_exception_matieres = ( ($BILAN_TYPE!='bulletin') || !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'] ) ? array() : explode(',',$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES']) ; // sert plus tard
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $prof_info = ($DB_ROW['prof_id']) ? To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) : '' ;
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
}


// Pas besoin de récupérer les absences / retards

$affichage_assiduite = FALSE ;

// Pas besoin de récupérer les professeurs principaux

$affichage_prof_principal = FALSE ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_resultat_examen = array();
$make_officiel = TRUE;
$make_brevet   = FALSE;
$make_action   = 'examiner';
$make_html     = FALSE;
$make_pdf      = FALSE;
$make_csv      = FALSE;
$make_graph    = FALSE;

if($BILAN_TYPE=='releve')
{
  $releve_modele            = 'multimatiere';
  $releve_individuel_format = 'eleve';
  $aff_etat_acquisition     = 0; // Inutile pour un examen de précence des appréciations
  $aff_moyenne_scores       = 0; // Inutile pour un examen de précence des appréciations
  $aff_pourcentage_acquis   = 0; // Inutile pour un examen de précence des appréciations
  $conversion_sur_20        = 0; // Inutile pour un examen de précence des appréciations
  $with_coef                = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
  $matiere_id               = TRUE;
  $matiere_nom              = '';
  $groupe_id                = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe   = la classe (par défaut) ou le groupe transmis
  $groupe_nom               = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $groupe_type              = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
  $date_debut               = '';
  $date_fin                 = '';
  $retroactif               = $_SESSION['OFFICIEL']['RELEVE_RETROACTIF']; // C'est un relevé de notes sur une période donnée : aller chercher les notes antérieures serait curieux !
  $only_etat                = $_SESSION['OFFICIEL']['RELEVE_ONLY_ETAT'];
  $only_socle               = $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE'];
  $aff_reference            = 0;
  $aff_domaine              = 0;
  $aff_theme                = 0;
  $legende                  = 0;
  $eleves_ordre             = 'alpha';
  $highlight_id             = 0; // Ne sert que pour le relevé d'items d'une matière
  $tab_eleve                = $tab_eleve_id;
  $liste_eleve              = $liste_eleve_id;
  $tab_type[]               = 'individuel';
  $type_individuel          = 1;
  $type_synthese            = 0;
  $type_bulletin            = 0;
  $tab_matiere_id           = $tab_rubrique_id; // N'est pas utilisé pour la récupération des résultats mais juste pour tester si on doit vérifier cette partie (ce serait un double souci sinon : il faut tester les bilans élèves qui ont des résultats ailleurs + ce tableau peut contenir la valeur 0).
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');
}
elseif($BILAN_TYPE=='bulletin')
{
  $synthese_modele = 'multimatiere' ;
  $matiere_nom     = '';
  $groupe_id       = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom      = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $groupe_type     = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
  $date_debut      = '';
  $date_fin        = '';
  $aff_socle       = 0;
  $retroactif      = $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'];
  $fusion_niveaux  = $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX'];
  $niveau_id       = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
  $only_socle      = $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'];
  $only_niveau     = 0; // pas jugé utile de le mettre en option...
  $legende         = 0;
  $eleves_ordre    = 'alpha';
  $tab_eleve       = $tab_eleve_id;
  $liste_eleve     = $liste_eleve_id;
  $tab_matiere_id  = $tab_rubrique_id; // N'est pas utilisé pour la récupération des résultats mais juste pour tester si on doit vérifier cette partie (ce serait un double souci sinon : il faut tester les bilans élèves qui ont des résultats ailleurs + ce tableau peut contenir la valeur 0).
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_synthese.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat de l'analyse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_pb_rubriques = count($tab_resultat_examen);
if(!$nb_pb_rubriques)
{
  Json::end( TRUE , '<p class="ti"><label class="valide">Aucune saisie manquante trouvée.</label></p>' );
}
else
{
  // Tentative d'indication des collègues potentiellement concernés
  $tab_rubrique_profs = array();
  if(in_array($BILAN_TYPE,array('releve','bulletin')))
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
        if(isset($tab_matiere[$matiere_id]))
        {
          $rubrique_nom = $tab_matiere[$matiere_id]['matiere_nom'];
          $nb_profs = count($tab_profs);
          if($nb_profs==1)
          {
            $tab_rubrique_profs[$rubrique_nom] = '['.current($tab_profs).']';
          }
          else if($nb_profs<=3)
          {
            $tab_rubrique_profs[$rubrique_nom] = '['.implode(' ; ',$tab_profs).']';
          }
          else
          {
            $tab_rubrique_profs[$rubrique_nom] = '['.$nb_profs.' professeurs]';
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
    $rubrique_indication = isset($tab_rubrique_profs[$rubrique_nom]) ? $rubrique_nom.' '.$tab_rubrique_profs[$rubrique_nom] : $rubrique_nom ;
    Json::add_str('<h3>'.html($rubrique_indication).'</h3>');
    Json::add_str('<ul class="puce"><li>'.implode('</li><li>',$tab).'</li></ul>');
  }
  Json::end( TRUE );
}

?>
