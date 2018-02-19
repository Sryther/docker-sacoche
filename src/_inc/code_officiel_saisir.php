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

$OBJET        = (isset($_POST['f_objet']))        ? Clean::texte($_POST['f_objet'])               : '';
$ACTION       = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])              : '';
$BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])          : '';
$mode         = (isset($_POST['f_mode']))         ? Clean::texte($_POST['f_mode'])                : '';
$periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])            : 0;
$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])             : 0;
$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])             : 0;
$eleve_id     = (isset($_POST['f_user']))         ? Clean::entier($_POST['f_user'])               : 0;
$rubrique_id  = (isset($_POST['f_rubrique']))     ? Clean::entier($_POST['f_rubrique'])           : 0;
$prof_id      = (isset($_POST['f_prof']))         ? Clean::entier($_POST['f_prof'])               : 0; // id du prof dont on corrige l'appréciation
$appreciation = (isset($_POST['f_appreciation'])) ? Clean::appreciation($_POST['f_appreciation']) : '';
$moyenne      = (isset($_POST['f_moyenne']))      ? Clean::decimal($_POST['f_moyenne'])           : -1;
$import_info  = (isset($_POST['f_import_info']))  ? Clean::texte($_POST['f_import_info'])         : '';
// Autres chaines spécifiques...
$listing_matieres = (isset($_POST['f_listing_matieres'])) ? $_POST['f_listing_matieres'] : '' ;
$tab_matiere_id = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;
$groupe_id      = ($groupe_id) ? $groupe_id : $classe_id ; // Le groupe = le groupe transmis ou sinon la classe (cas le plus fréquent).

$tab_objet  = array('modifier','tamponner','voir'); // "voir" car on peut corriger une appréciation dans ce mode
$tab_action = array('initialiser','charger','enregistrer_appr','corriger_faute','enregistrer_note','supprimer_appr','supprimer_note','recalculer_note');
$tab_mode  = array('texte','graphique');

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!isset($tab_types[$BILAN_TYPE])) || (!in_array($OBJET,$tab_objet)) || (!in_array($mode,$tab_mode)) || !$periode_id || !$classe_id )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Avant ce n'était que pour les bulletins, maintenant c'est pour tous les bilans officiels
if(!$eleve_id)
{
  $is_appreciation_groupe = TRUE;
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
if(!in_array($OBJET.$BILAN_ETAT,array('modifier2rubrique','modifier3mixte','tamponner3mixte','tamponner4synthese','voir2rubrique','voir3mixte','voir4synthese'))) //  'voir*' est transmis dans le cas d'une correction de faute
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

// Si un personnel accède à la saisie de synthèse, il ne faut pas seulement récupérer les données qui concerne ses matières.
$liste_matiere_id = ( ($OBJET=='modifier') || ($BILAN_ETAT=='2rubrique') ) ? $liste_matiere_id : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1 : enregistrement d'une appréciation ou d'une note
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='enregistrer_appr')
{
  if( (!$appreciation) || (($BILAN_ETAT=='2rubrique')&&($rubrique_id==0)) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  enregistrer_appreciation( $BILAN_TYPE , $periode_id , $eleve_id , $classe_id , $groupe_id , $rubrique_id , $_SESSION['USER_ID'] , $appreciation );
  $prof_info = To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
  $ACTION = ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
  Json::end( TRUE , '<div class="notnow">'.html($prof_info).$ACTION.'</div><div class="appreciation">'.html($appreciation).'</div>' );
}

if($ACTION=='corriger_faute')
{
  if( (!$appreciation) || ($prof_id==0) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  enregistrer_appreciation( $BILAN_TYPE , $periode_id , $eleve_id , $classe_id , $groupe_id , $rubrique_id , $prof_id , $appreciation );
  Json::end( TRUE , html($appreciation) );
}

if($ACTION=='enregistrer_note')
{
  if( ($moyenne<0) || ($ACTION=='tamponner') || ($BILAN_TYPE!='bulletin') || (!$rubrique_id) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  list( $note , $appreciation ) = enregistrer_note( $BILAN_TYPE , $periode_id , $eleve_id , $rubrique_id , $moyenne );
  $note = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $note : ($note*5).'&nbsp;%' ;
  $action = ' <button type="button" class="modifier">Modifier</button> <button type="button" class="nettoyer">Effacer et recalculer.</button> <button type="button" class="supprimer">Supprimer sans recalculer</button>' ;
  Json::end( TRUE , '<td class="now moyenne">'.$note.'</td><td class="now"><span class="notnow">'.html($appreciation).$action.'</span></td>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2 : suppression d'une appréciation ou d'une note
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='supprimer_appr')
{
  if( ($BILAN_ETAT=='2rubrique') && ($rubrique_id==0) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // élève ou classe
  $saisie_type        = ($eleve_id) ? 'eleve'   : 'classe' ;
  $eleve_ou_classe_id = ($eleve_id) ? $eleve_id : $classe_id ;
  $saisie_groupe_id   = ($eleve_id) ? 0         : $groupe_id ;
  $texte_classe       = empty($is_appreciation_groupe) ? '' : ' sur la classe' ;
  DB_STRUCTURE_OFFICIEL::DB_supprimer_bilan_officiel_saisie( $BILAN_TYPE , $periode_id , $eleve_ou_classe_id , $saisie_groupe_id , $rubrique_id , $_SESSION['USER_ID'] , $saisie_type );
  $ACTION = ($rubrique_id!=0) ? '<button type="button" class="ajouter">Ajouter une appréciation'.$texte_classe.'.</button>' : '<button type="button" class="ajouter">Ajouter l\'appréciation générale'.$texte_classe.'.</button>' ;
  Json::end( TRUE , '<div class="hc">'.$ACTION.'</div>' );
}

if($ACTION=='supprimer_note')
{
  // Il s'agit de la supprimer définitivement et de ne pas la recalculer : on insère une note vide
  if( ($ACTION=='tamponner') || ($BILAN_TYPE!='bulletin') || (!$rubrique_id) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  $note = NULL;
  $appreciation = 'Moyenne effacée par '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
  DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_saisie( $BILAN_TYPE , $periode_id , $eleve_id , 0 /*groupe_id*/ , $rubrique_id , 0 /*prof_id*/ , 'eleve' , $note , $appreciation );
  Json::end( TRUE , '<td class="now moyenne">-</td><td class="now"><span class="notnow">'.html($appreciation).' <button type="button" class="modifier">Modifier</button> <button type="button" class="nettoyer">Effacer et recalculer.</button></span></td>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3 : recalculer une note (soit effacée - NULL - soit figée car reportée manuellement)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='recalculer_note')
{
  if( ($ACTION=='tamponner') || ($BILAN_TYPE!='bulletin') || (!$rubrique_id) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  $note = calculer_et_enregistrer_moyenne_precise_bulletin( $periode_id , $classe_id , $eleve_id , $rubrique_id , $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'] );
  if($note===FALSE)
  {
    Json::end( FALSE , 'Absence de données permettant de calculer cette moyenne !' );
  }
  $note = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $note : ($note*5).'&nbsp;%' ;
  $appreciation = 'Moyenne calculée / reportée / actualisée automatiquement.' ;
  Json::end( TRUE , '<td class="now moyenne">'.$note.'</td><td class="now"><span class="notnow">'.html($appreciation).' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer sans recalculer</button></span></td>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 4 & 5 : affichage des données d'un élève indiqué (si initialisation, alors le groupe classe)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Si besoin, fabriquer le formulaire avec la liste des élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves
$groupe_nom = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;

if($ACTION=='initialiser')
{
  $separateur = ';';
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                               : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
  }
  $tab_eleve_id = array();
  $form_choix_eleve = '<form action="#" method="post" id="form_choix_eleve"><div><b>'.html($periode_nom.' | '.$classe_nom).' :</b> <button id="go_premier_eleve" type="button" class="go_premier">Premier</button> <button id="go_precedent_eleve" type="button" class="go_precedent">Précédent</button> <select id="go_selection_eleve" name="go_selection" class="b">';
  $form_choix_eleve.= '<option value="0">'.html($groupe_nom).'</option>';
  foreach($DB_TAB as $DB_ROW)
  {
    $form_choix_eleve .= '<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
    $tab_eleve_id[] = $DB_ROW['user_id'];
  }
  $form_choix_eleve .= '</select> <button id="go_suivant_eleve" type="button" class="go_suivant">Suivant</button> <button id="go_dernier_eleve" type="button" class="go_dernier">Dernier</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button>';
  $form_choix_eleve .= ( ($BILAN_TYPE=='bulletin') && ($OBJET=='tamponner') ) ? ( ($mode=='texte') ? ' <button id="change_mode" type="button" class="stats">Interface graphique</button>' : ' <button id="change_mode" type="button" class="texte">Interface détaillée</button>' ) : '' ;
  $form_choix_eleve .= '</div></form><hr />';
  $eleve_id = 0;
  // sous-titre
  if($ACTION=='tamponner')
  {
    $sous_titre = 'Éditer l\'appréciation générale';
  }
  else
  {
    switch($BILAN_TYPE)
    {
      case 'releve'   : $sous_titre = 'Éditer les appréciations par matière'; break;
      case 'bulletin' : $sous_titre = 'Éditer les notes et les appréciations'; break;
      default         : $sous_titre = 'Éditer les appréciations par compétence';
    }
  }
  // (re)calculer les moyennes des élèves
  if( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] )
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
    calculer_et_enregistrer_moyennes_eleves_bulletin( $periode_id , $classe_id , $liste_eleve_id , $liste_matiere_id , $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'] , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] );
  }
}

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné, pour la période en cours et les périodes antérieures

$tab_saisie       = array();  // [eleve_id][rubrique_id]][prof_id] => array(prof_info,appreciation,note); avec eleve_id=0 pour note ou appréciation sur la classe
$tab_saisie_avant = array();  // [eleve_id][rubrique_id][periode_id]][prof_id] => array(periode_nom_avant,prof_info,appreciation,note);
$tab_saisie_groupe = array();  // [groupe_id][prof_id] => array(groupe_info,prof_info,appreciation);
$tab_saisie_groupe_avant = array();  // [periode_id][groupe_id][prof_id] => array(periode_nom_avant,groupe_info,prof_info,appreciation);
$tab_moyenne_exception_matieres = ( ($BILAN_TYPE!='bulletin') || !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'] ) ? array() : explode(',',$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES']) ;
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , TRUE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $prof_info = ($DB_ROW['prof_id']) ? To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) : '' ;
  $note = in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) ? NULL : $DB_ROW['saisie_note'] ;
  if($DB_ROW['periode_id']==$periode_id)
  {
    $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$note );
  }
  else
  {
    $tab_saisie_avant[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['periode_id']][$DB_ROW['prof_id']] = array( 'periode_nom_avant'=>$DB_ROW['periode_nom'] , 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$note );
  }
}
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $BILAN_TYPE , $periode_id , $classe_id , 0 /*prof_id*/ , TRUE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $prof_info = ($DB_ROW['prof_id']) ? To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) : '' ;
  if(!$DB_ROW['groupe_id'])
  {
    $note = !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) ? $DB_ROW['saisie_note'] : NULL ;
    if($DB_ROW['periode_id']==$periode_id)
    {
      $tab_saisie[0][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$note );
    }
    else
    {
      $tab_saisie_avant[0][$DB_ROW['rubrique_id']][$DB_ROW['periode_id']][$DB_ROW['prof_id']] = array( 'periode_nom_avant'=>$DB_ROW['periode_nom'] , 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$note );
    }
  }
  else
  {
    // TODO : non utilisé pour l'instant, à voir si on le gère ainsi ou pas
    // Cas d'une appréciation sur le groupe ; géré après coup et compliqué à intégrer au tableau existant sans tout casser
    if($DB_ROW['periode_id']==$periode_id)
    {
      $tab_saisie_groupe[$DB_ROW['groupe_id']][$DB_ROW['prof_id']] = array( 'groupe_info'=>$DB_ROW['groupe_nom'] , 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] );
    }
    else
    {
      $tab_saisie_groupe_avant[$DB_ROW['periode_id']][$DB_ROW['groupe_id']][$DB_ROW['prof_id']] = array( 'periode_nom_avant'=>$DB_ROW['periode_nom'] , 'groupe_info'=>$DB_ROW['groupe_nom'] , 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] );
    }
  }

}

// Récupérer les absences / retards

$affichage_assiduite = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_ASSIDUITE']) ? TRUE : FALSE ;

if( $affichage_assiduite && $eleve_id )
{
  $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_assiduite( $periode_id , $eleve_id );
  $tab_assiduite[$eleve_id] = (empty($DB_ROW)) ? array( 'absence' => NULL , 'absence_nj' => NULL , 'retard' => NULL , 'retard_nj' => NULL ) : array( 'absence' => $DB_ROW['assiduite_absence'] , 'absence_nj' => $DB_ROW['assiduite_absence_nj'] , 'retard' => $DB_ROW['assiduite_retard'] , 'retard_nj' => $DB_ROW['assiduite_retard_nj'] ) ;
}

// Récupérer les professeurs principaux

$affichage_prof_principal = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_PROF_PRINCIPAL']) ? TRUE : FALSE ;

if( $affichage_prof_principal )
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_profs_principaux($classe_id);
  if(empty($DB_TAB))
  {
    $texte_prof_principal = 'Professeur principal : sans objet.';
  }
  else if(count($DB_TAB)==1)
  {
    $texte_prof_principal = 'Professeur principal : '.To::texte_identite($DB_TAB[0]['user_nom'],FALSE,$DB_TAB[0]['user_prenom'],TRUE,$DB_TAB[0]['user_genre']);
  }
  else
  {
    $tab_pp = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_pp[] = To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']);
    }
    $texte_prof_principal = 'Professeurs principaux : '.implode(' ; ',$tab_pp);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$make_officiel = TRUE;
$make_brevet   = FALSE;
$make_action   = $OBJET; // 'modifier' || 'tamponner' (et plus seulement 'saisir')
$make_html     = ( ($BILAN_TYPE=='bulletin') && ($OBJET=='tamponner') && ($mode=='graphique') ) ? FALSE : TRUE ;
$make_pdf      = FALSE;
$make_csv      = FALSE;
$make_graph    = ( ($BILAN_TYPE=='bulletin') && ($OBJET=='tamponner') && ($mode=='graphique') ) ? TRUE : FALSE ;
$droit_corriger_appreciation = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_CORRIGER_APPRECIATION'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );

if($BILAN_TYPE=='releve')
{
  $releve_modele            = 'multimatiere';
  $releve_individuel_format = 'eleve';
  $aff_etat_acquisition     = $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION'];
  $aff_moyenne_scores       = $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES'];
  $aff_pourcentage_acquis   = $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS'];
  $conversion_sur_20        = $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20'];
  $with_coef                = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
  $matiere_id               = TRUE;
  $matiere_nom              = '';
  $groupe_id                = $groupe_id;  // Le groupe   = la classe (par défaut) ou le groupe transmis
  $groupe_nom               = $groupe_nom; // Déjà défini avant car on en avait besoin
  $groupe_type              = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
  $date_debut               = '';
  $date_fin                 = '';
  $retroactif               = $_SESSION['OFFICIEL']['RELEVE_RETROACTIF']; // C'est un relevé de notes sur une période donnée : aller chercher les notes antérieures serait curieux !
  $only_etat                = $_SESSION['OFFICIEL']['RELEVE_ONLY_ETAT'];
  $only_socle               = $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE'];
  $aff_reference            = $_SESSION['OFFICIEL']['RELEVE_AFF_REFERENCE'];
  $aff_coef                 = $_SESSION['OFFICIEL']['RELEVE_AFF_COEF'];
  $aff_socle                = $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE'];
  $aff_comm                 = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_lien                 = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_domaine              = $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE'];
  $aff_theme                = $_SESSION['OFFICIEL']['RELEVE_AFF_THEME'];
  $orientation              = 'portrait'; // pas jugé utile de le mettre en option...
  $couleur                  = $_SESSION['OFFICIEL']['RELEVE_COULEUR'];
  $fond                     = $_SESSION['OFFICIEL']['RELEVE_FOND'];
  $legende                  = $_SESSION['OFFICIEL']['RELEVE_LEGENDE'];
  $marge_gauche             = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
  $marge_droite             = $_SESSION['OFFICIEL']['MARGE_DROITE'];
  $marge_haut               = $_SESSION['OFFICIEL']['MARGE_HAUT'];
  $marge_bas                = $_SESSION['OFFICIEL']['MARGE_BAS'];
  $pages_nb                 = $_SESSION['OFFICIEL']['RELEVE_PAGES_NB'];
  $cases_auto               = $_SESSION['OFFICIEL']['RELEVE_CASES_AUTO'];
  $cases_nb                 = $_SESSION['OFFICIEL']['RELEVE_CASES_NB'];
  $cases_largeur            = $_SESSION['OFFICIEL']['RELEVE_CASES_LARGEUR'];
  $eleves_ordre             = 'alpha';
  $highlight_id             = 0; // Ne sert que pour le relevé d'items d'une matière
  $tab_eleve                = array($eleve_id); // tableau de l'unique élève à considérer
  $liste_eleve              = (string)$eleve_id;
  $tab_type[]               = 'individuel';
  $type_individuel          = 1;
  $type_synthese            = 0;
  $type_bulletin            = 0;
  $tab_matiere_id           = $tab_matiere_id;
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');
  $nom_bilan_html           = 'releve_HTML_individuel';
}
elseif($BILAN_TYPE=='bulletin')
{
  $synthese_modele = 'multimatiere' ;
  $matiere_nom     = '';
  $groupe_id       = $groupe_id;  // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom      = $groupe_nom; // Déjà défini avant car on en avait besoin
  $groupe_type     = (!$is_sous_groupe) ? 'Classe'  : 'Groupe' ;
  $date_debut      = '';
  $date_fin        = '';
  $retroactif      = $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'];
  $fusion_niveaux  = $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX'];
  $niveau_id       = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
  $aff_coef        = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_socle       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_lien        = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_start       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $only_socle      = $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'];
  $only_niveau     = 0; // pas jugé utile de le mettre en option...
  $couleur         = $_SESSION['OFFICIEL']['BULLETIN_COULEUR'];
  $fond            = $_SESSION['OFFICIEL']['BULLETIN_FOND'];
  $legende         = $_SESSION['OFFICIEL']['BULLETIN_LEGENDE'];
  $marge_gauche    = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
  $marge_droite    = $_SESSION['OFFICIEL']['MARGE_DROITE'];
  $marge_haut      = $_SESSION['OFFICIEL']['MARGE_HAUT'];
  $marge_bas       = $_SESSION['OFFICIEL']['MARGE_BAS'];
  $eleves_ordre    = 'alpha';
  $tab_eleve       = array($eleve_id); // tableau de l'unique élève à considérer
  $liste_eleve     = (string)$eleve_id;
  $tab_matiere_id  = $tab_matiere_id;
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_synthese.php');
  $nom_bilan_html  = 'releve_HTML';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Json::add_row( 'script' , ...) a déjà eu lieu

if( in_array($BILAN_TYPE,array('releve','bulletin')) && !count($tab_eval) && empty($is_appreciation_groupe) )
{
  ${$nom_bilan_html} = '<div class="danger">Aucun item évalué sur la période '.$date_debut.' ~ '.$date_fin.' selon les paramètres choisis !</div>' ;
}

if($ACTION=='initialiser')
{
  Json::add_row( 'html' , '<h2>'.$sous_titre.'</h2>' );
  Json::add_row( 'html' , $form_choix_eleve );
  Json::add_row( 'html' , '<form action="#" method="post" id="zone_resultat_eleve" onsubmit="return false">'.${$nom_bilan_html}.'</form>' );
}
else
{
  Json::add_row( 'html' , ${$nom_bilan_html} );
}

Json::end( TRUE );

?>
