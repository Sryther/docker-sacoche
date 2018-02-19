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
$listing_matieres = (isset($_POST['f_listing_matieres'])) ? $_POST['f_listing_matieres'] : '' ;
$tab_matiere_id = array_filter( Clean::map('entier', explode(',',$listing_matieres) ) , 'positif' );
$liste_matiere_id = implode(',',$tab_matiere_id);

$tab_objet  = array('modifier','tamponner','voir'); // "voir" car on peut corriger une appréciation dans ce mode
$tab_action = array('initialiser','charger','ajouter_saisie','modifier_saisie','supprimer_saisie','recalculer_saisie','corriger_faute');
$tab_mode  = array('texte','graphique');
$tab_rubrique_type = array('eval','socle','epi','ap','parcours','bilan','viesco','enscompl','attitude');
$tab_saisie_objet = array('position','appreciation','elements','saisiejointure');

// On vérifie les paramètres principaux

if( !in_array($OBJET,$tab_objet) || !in_array($ACTION,$tab_action) || !in_array($mode,$tab_mode) || !$classe_id )
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

if(!in_array($OBJET.$BILAN_ETAT,array('modifier2rubrique','modifier3mixte','tamponner3mixte','tamponner4synthese','voir2rubrique','voir3mixte','voir4synthese'))) //  'voir*' est transmis dans le cas d'une correction de faute
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

// On récupère et vérifie d'autres paramètres communs à certaines actions

if( ($ACTION!='initialiser') && ($ACTION!='charger') )
{
  // Récup
  $rubrique_type = (isset($_POST['f_rubrique_type'])) ? Clean::texte($_POST['f_rubrique_type'])       : '';
  $rubrique_id   = (isset($_POST['f_rubrique_id']))   ? Clean::entier($_POST['f_rubrique_id'])        : 0;
  $saisie_objet  = (isset($_POST['f_saisie_objet']))  ? Clean::texte($_POST['f_saisie_objet'])        : '';
  $page_colonne  = (isset($_POST['f_page_colonne']))  ? Clean::texte($_POST['f_page_colonne'])        : '';
  $appreciation  = (isset($_POST['f_appreciation']))  ? Clean::appreciation($_POST['f_appreciation']) : '';
  $elements      = (isset($_POST['f_elements']))      ? Clean::appreciation($_POST['f_elements'])     : '';
  $position      = (isset($_POST['f_position']))      ? Clean::decimal($_POST['f_position'])          : -1;
  // Vérif globale
  $test_pb_rubrique  = ( !in_array($rubrique_type,$tab_rubrique_type) || (($rubrique_type=='bilan')&&($BILAN_ETAT=='2rubrique')) || (!in_array($rubrique_type,array('bilan','viesco'))&&($rubrique_id==0)) ) ? TRUE : FALSE ;
  $test_pb_colonne   = ( !in_array($saisie_objet,$tab_saisie_objet) || ( ($saisie_objet=='position') && ($page_colonne!=$PAGE_COLONNE) ) ) ? TRUE : FALSE ;
  $test_pb_page      = ( (($rubrique_type=='epi')&&!$PAGE_EPI) || (($rubrique_type=='ap')&&!$PAGE_AP) || (($rubrique_type=='parcours')&&!$PAGE_PARCOURS) || (($rubrique_type=='viesco')&&!$PAGE_VIE_SCOLAIRE) ) ? TRUE : FALSE ;
  $test_pb_saisie_id = ( in_array($ACTION,array('modifier_saisie','supprimer_saisie','recalculer_saisie','corriger_faute')) && !$saisie_id ) ? TRUE : FALSE ;
  if( $test_pb_rubrique || $test_pb_colonne || $test_pb_page || $test_pb_saisie_id )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
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
// Cas 1 : enregistrement d'une appréciation / d'un positionnement / d'éléments du programme
// ////////////////////////////////////////////////////////////////////////////////////////////////////


if( ($ACTION=='ajouter_saisie') || ($ACTION=='modifier_saisie') )
{
  // Vérif saisie
  $test_pb_saisie   = ( (($saisie_objet=='appreciation')&&!$appreciation) || (($saisie_objet=='elements')&&!$elements) || (in_array($page_colonne,array('objectif','position'))&&!in_array($position,array(1,2,3,4))) || (($page_colonne=='reussite')&&!in_array($position,array(1,2,3))) ) ? TRUE : FALSE ;
  $test_pb_position = ( ($saisie_objet=='position') && ( ($position<0) || !$rubrique_id || !in_array($rubrique_type,array('eval','socle','enscompl')) || ($ACTION=='tamponner') ) ) ? TRUE : FALSE ;
  $test_pb_maitrise = ( ($page_colonne=='maitrise') && ( !in_array($position,array(0,1,2,3,4)) || ( !$position && ($rubrique_id!=12) /*dispensé*/ ) || ( ($position<3) && ($rubrique_type=='enscompl') ) ) ) ? TRUE : FALSE ;
  if( ( ($ACTION=='modifier_saisie') && !$saisie_id ) || $test_pb_saisie || $test_pb_position || $test_pb_maitrise )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Formater la saisie à enregistrer
  if($saisie_objet=='appreciation')
  {
    $saisie_valeur = $appreciation;
  }
  else if($saisie_objet=='elements')
  {
    // Un élément par ligne
    $elements = str_replace( Clean::tab_crlf() , '‡‡‡' , trim($elements) );
    $tab_elements = explode( '‡‡‡' , $elements );
    $tab_elements = array_filter($tab_elements,'non_chaine_vide'); // Pour éviter des sauts de ligne entre les éléments
    $tab_elements = array_unique($tab_elements); // Pas indispensable car ce sont ensuite les éléments qui sont pris comme clefs
    $tab_elements = array_values($tab_elements); // Pour ré-indexer les clefs proprement
    $nb_elements  = count($tab_elements);
    $tab_saisie   = array();
    foreach($tab_elements as $key => $element)
    {
      $tab_saisie[$element] = $nb_elements-$key;
    }
    $saisie_valeur = json_encode($tab_saisie);
  }
  else if($page_colonne=='pourcentage')
  {
    $saisie_valeur = round($position,1);
    $affich_note = round($position,1).'&nbsp;%';
  }
  else if($page_colonne=='moyenne')
  {
    $saisie_valeur = round($position*5,1);
    $affich_note = round($position,1);
  }
  else if(in_array($page_colonne,array('objectif','position','maitrise','reussite')))
  {
    if( $position )
    {
      $saisie_valeur = ($_SESSION['LIVRET'][$position]['SEUIL_MIN']+$_SESSION['LIVRET'][$position]['SEUIL_MAX'])/2;
    }
    else
    {
      $saisie_valeur = 'disp';
    }
  }
  // Enregistrer la saisie
  if($ACTION=='modifier_saisie')
  {
    DB_STRUCTURE_LIVRET::DB_modifier_saisie( $saisie_id , $saisie_objet , $saisie_valeur , 'saisie' , $_SESSION['USER_ID'] );
  }
  else
  {
    $cible_nature = ($eleve_id) ? 'eleve'   : 'classe' ;
    $cible_id     = ($eleve_id) ? $eleve_id : $classe_id ;
    $saisie_id = DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $rubrique_type , $rubrique_id , $cible_nature , $cible_id , $saisie_objet , $saisie_valeur , 'saisie' , $_SESSION['USER_ID'] );
  }
  // Saisie d'un élément de programme pour un regroupement : répercuter à tous les élèves du regroupement
  if( ($rubrique_type=='eval') && ($saisie_objet=='elements') && !$eleve_id )
  {
    $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                                 : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
    if(empty($DB_TAB))
    {
      Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
    }
    foreach($DB_TAB as $DB_ROW)
    {
      // On ne distingue pas s'il faut modifier ou ajouter au cas par cas, trop compliqué, et pas nécessaire car il y a un ON DUPLICATE KEY UPDATE dans la requête...
      DB_STRUCTURE_LIVRET::DB_ajouter_saisie( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $rubrique_type , $rubrique_id , 'eleve' , $DB_ROW['user_id'] , $saisie_objet , $saisie_valeur , 'saisie' , $_SESSION['USER_ID'] );
    }
  }
  // Retourner le HTML adapté
  $prof_info = To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
  $origine = ' Dernière saisie par '.html($prof_info);
  $origine_eval_txt = ' Validé par '.html($prof_info);
  $origine_position = ' Saisi par '.html($prof_info);
  $bouton_modifier  = ' <button type="button" class="modifier">Modifier</button>';
  $bouton_supprimer = ' <button type="button" class="supprimer">Supprimer</button>';
  $bouton_generer = ( ( ($saisie_objet=='elements') && $eleve_id ) || ($rubrique_type=='bilan') || ( ($saisie_objet!='elements') && ($BILAN_TYPE_ETABL=='college') && ($PAGE_RUBRIQUE_JOIN=='matiere') ) ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
  $bouton_modifier_position  = ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button>';
  $bouton_supprimer_position = ' <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>';
  $bouton_generer_position   = ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>';
  if( ($rubrique_type=='eval') && ($saisie_objet=='elements') )
  {
    $saisie_valeur = elements_programme_extraction( $saisie_valeur , 50 /*nb_caract_max_par_colonne*/ , 'html' /*objet_retour*/ );
    $alerte = ( !$eleve_id ) ? '<div class="danger">Une action sur le regroupement l\'impose à tous les élèves.</div>' : '' ;
    Json::end( TRUE , '<div class="elements">'.$saisie_valeur.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine_eval_txt).$bouton_modifier.$bouton_supprimer.$bouton_generer.$alerte.'</div>' );
  }
  else if( in_array($rubrique_type,array('eval','attitude')) && ($saisie_objet=='appreciation') )
  {
    Json::end( TRUE , '<div class="appreciation">'.$saisie_valeur.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine_eval_txt).$bouton_modifier.$bouton_supprimer.$bouton_generer.'</div>' );
  }
  else if( ($rubrique_type=='eval') && in_array($page_colonne,array('moyenne','pourcentage')) )
  {
    Json::end( TRUE , '<div class="position">'.$affich_note.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine_position).$bouton_modifier_position.$bouton_supprimer_position.$bouton_generer_position.'</div>' );
  }
  else if( in_array($rubrique_type,array('eval','socle','enscompl')) && in_array($page_colonne,array('objectif','position','maitrise','reussite')) ) // forcément 'maitrise' pour 'enscompl'
  {
    if($rubrique_type=='enscompl')
    {
      $id_debut = 3;
      $bouton_generer_position = '';
    }
    else if( ($rubrique_type=='socle') && ($rubrique_id==12) ) // langue étrangère avec positionnement dispensé possible
    {
      $id_debut = 0;
    }
    else
    {
      $id_debut = 1;
    }
    $id_fin = ($page_colonne!='reussite') ? 4 : 3 ;
    for( $id=$id_debut ; $id<=$id_fin ; $id++ )
    {
      $texte = ($id==$position) ? '<b>X</b>' : '' ;
      Json::add_row( 'td_'.$id , $texte );
    }
    Json::add_row( 'td_'.$page_colonne , '<div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine_position).$bouton_modifier_position.$bouton_supprimer_position.$bouton_generer_position.'</div><i>'.$position.'</i>' );
    Json::end( TRUE );
  }
  else if($rubrique_type=='epi')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('epi',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if($rubrique_type=='ap')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('ap',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if($rubrique_type=='parcours')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('parcours',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if($rubrique_type=='bilan')
  {
    $texte_intro = ($rubrique_type!='socle') ? '<span class="notnow">'.rubrique_texte_intro('bilan',$eleve_id).'</span><br />' : '';
    Json::end( TRUE , $texte_intro.'<span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.$bouton_generer.'</div>' );
  }
  else if($rubrique_type=='viesco')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('viesco').'</span><br /><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.$bouton_generer.'</div>' );
    // Il y a aussi le contenu de #div_assiduite qui est mis de côté puis rajouté en js
  }
  else
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2 : suppression d'une appréciation / d'un positionnement / d'éléments du programme / d'un rattachement à une appréciation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='supprimer_saisie')
{
  // Cas particulier de la suppression d'un rattachement à une appréciation saisie par erreur
  if($saisie_objet=='saisiejointure')
  {
    DB_STRUCTURE_LIVRET::DB_modifier_saisie_jointure_prof( $saisie_id , $_SESSION['USER_ID'] , TRUE /*delete*/ );
    Json::end( TRUE , 'jointure_'.$saisie_id.'_'.$_SESSION['USER_ID'] );
  }
  // Enregistrer la suppression
  DB_STRUCTURE_LIVRET::DB_modifier_saisie( $saisie_id , $saisie_objet , NULL /*saisie_valeur*/ , 'saisie' , $_SESSION['USER_ID'] );
  // Suppression d'un élément de programme pour un regroupement : répercuter à tous les élèves du regroupement, il faut du coup recalculer les contenus...
  if( ($rubrique_type=='eval') && ($saisie_objet=='elements') && !$eleve_id )
  {
    $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom' /*champs*/ , $periode_id )
                                 : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe( $classe_id , $groupe_id , 2 /*actuels_et_anciens*/ , $periode_id ) ;
    if(empty($DB_TAB))
    {
      Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$groupe_nom.' !' );
    }
    $tab_eleve_id = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleve_id[] = $DB_ROW['user_id'];
    }
    $liste_eleve_id = implode(',',$tab_eleve_id);
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '"eval"' , $liste_eleve_id , $_SESSION['USER_ID'] /*prof_id*/ , FALSE /*with_periodes_avant*/ );
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if($DB_ROW['saisie_objet']=='elements')
      {
        calculer_et_enregistrer_donnee_eleve_rubrique_objet( $DB_ROW['livret_saisie_id'] , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $rubrique_type , $rubrique_id , 'eleve' /*cible_nature*/ , $classe_id , 0 /*saisie_groupe_id*/ , $DB_ROW['eleve_id'] , $saisie_objet , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES'] , $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['LIVRET_RETROACTIF'] );
      }
    }
  }
  // Retourner le HTML adapté
  $prof_info = To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
  $origine = ' Supprimé par '.html($prof_info);
  $bouton_ajouter = ' <button type="button" class="ajouter">Ajouter</button>';
  $bouton_generer = ( ($saisie_objet=='elements') || ($rubrique_type=='bilan') || ( ($BILAN_TYPE_ETABL=='college') && ($PAGE_RUBRIQUE_JOIN=='matiere') ) ) ? ' <button type="button" class="eclair">Re-générer</button>' : '' ;
  $bouton_ajouter_position = ' <button type="button" class="ajouter" title="Ajouter le positionnement">&nbsp;</button>';
  $bouton_generer_position = ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>';
  $saisie_eval_danger = '<div class="danger">Absence de saisie !</div>' ;
  $saisie_valeur_danger = '<span class="danger">Absence de saisie !</span>' ;
  $saisie_valeur_astuce = '<span class="astuce">Absence de saisie.</span>' ;
  if( ($rubrique_type=='eval') && ($saisie_objet=='elements') )
  {
    $saisie_eval_danger = ( !$eleve_id ) ? '<div class="danger">Saisir des éléments pour le regroupement l\'impose à tous les élèves et annule leur récolte automatisée pour chacun.</div>' : $saisie_eval_danger ;
    Json::end( TRUE , '<div class="elements">'.$saisie_eval_danger.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.$bouton_generer.'</div>' );
  }
  else if( in_array($rubrique_type,array('eval','attitude')) && ($saisie_objet=='appreciation') )
  {
    Json::end( TRUE , '<div class="appreciation">'.$saisie_eval_danger.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.$bouton_generer.'</div>' );
  }
  else if( ($rubrique_type=='eval') && in_array($page_colonne,array('moyenne','pourcentage')) )
  {
    $saisie = '-';
    Json::end( TRUE , '<div class="position">'.$saisie.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter_position.$bouton_generer_position.'</div>' );
  }
  else if( in_array($rubrique_type,array('eval','socle','enscompl')) && in_array($page_colonne,array('objectif','position','maitrise','reussite')) ) // forcément 'maitrise' pour 'enscompl'
  {
    if($rubrique_type=='enscompl')
    {
      $id_debut = 3;
      $bouton_generer_position = '';
    }
    else if( ($rubrique_type=='socle') && ($rubrique_id==12) ) // langue étrangère avec positionnement dispensé possible
    {
      $id_debut = 0;
    }
    else
    {
      $id_debut = 1;
    }
    $id_fin = ($page_colonne!='reussite') ? 4 : 3 ;
    for( $id=$id_debut ; $id<=$id_fin ; $id++ )
    {
      Json::add_row( 'td_'.$id , '' );
    }
    Json::add_row( 'td_'.$page_colonne , '<div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter_position.$bouton_generer_position.'</div><i></i>' );
    Json::end( TRUE );
  }
  else if($rubrique_type=='epi')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('epi',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur_danger.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.'</div>' );
  }
  else if($rubrique_type=='ap')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('ap',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur_danger.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.'</div>' );
  }
  else if($rubrique_type=='parcours')
  {
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('parcours',$eleve_id).'</span><span class="appreciation">'.$saisie_valeur_danger.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.'</div>' );
  }
  else if($rubrique_type=='bilan')
  {
    $saisie_valeur = ($BILAN_ETAT=='2rubrique') ? $saisie_valeur_astuce : $saisie_valeur_danger ;
    $texte_intro = ($rubrique_type!='socle') ? '<span class="notnow">'.rubrique_texte_intro('bilan',$eleve_id).'</span>' : '';
    Json::end( TRUE , $texte_intro.'<span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.$bouton_generer.'</div>' );
  }
  else if($rubrique_type=='viesco')
  {
    $saisie_valeur = ($BILAN_ETAT=='2rubrique') ? $saisie_valeur_astuce : $saisie_valeur_danger ;
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('viesco').'</span><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_ajouter.$bouton_generer.'</div>' );
    // Il y a aussi le contenu de #div_assiduite qui est mis de côté puis rajouté en js
  }
  else
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3 : re-générer une appréciation / un positionnement / des éléments du programme (soit effacé - NULL - soit figé car reporté manuellement)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='recalculer_saisie')
{
  if($ACTION=='tamponner')
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  $cible_nature = ($eleve_id) ? 'eleve' : 'classe' ;
  $saisie_groupe_id = ( $eleve_id || ($saisie_objet=='position') ) ? 0 : $groupe_id ;
  list( $reussite , $origine , $contenu ) = calculer_et_enregistrer_donnee_eleve_rubrique_objet( $saisie_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , $PAGE_RUBRIQUE_TYPE , $PAGE_RUBRIQUE_JOIN , $PAGE_COLONNE , $periode_id , $date_mysql_debut , $date_mysql_fin , $rubrique_type , $rubrique_id , $cible_nature , $classe_id , $saisie_groupe_id , $eleve_id , $saisie_objet , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES'] , $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE'] , $_SESSION['OFFICIEL']['LIVRET_RETROACTIF'] );
  if(!$reussite)
  {
    Json::end( FALSE , $contenu );
  }
  // Retourner le HTML adapté
  $origine = ($origine=='bulletin') ? 'Reporté du bulletin' : ( ($saisie_objet=='position') ? 'Calculé automatiquement' : 'Généré automatiquement' ) ;
  $bouton_modifier  = ' <button type="button" class="modifier">Modifier</button>';
  $bouton_supprimer = ' <button type="button" class="supprimer">Supprimer</button>';
  $bouton_modifier_position  = ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button>';
  $bouton_supprimer_position = ' <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>';
  if( ($rubrique_type=='eval') && ($saisie_objet=='elements') )
  {
    $saisie_valeur = elements_programme_extraction( $contenu , 50 /*nb_caract_max_par_colonne*/ , 'html' /*objet_retour*/ );
    Json::end( TRUE , '<div class="elements">'.$saisie_valeur.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if( ($rubrique_type=='eval') && ($saisie_objet=='appreciation') )
  {
    $appreciation = html($contenu);
    Json::end( TRUE , '<div class="appreciation">'.$appreciation.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if( ($rubrique_type=='eval') && in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
  {
    $note = ($PAGE_COLONNE=='moyenne') ? round(($contenu/5),1) : $contenu.'&nbsp;%' ;
    Json::end( TRUE , '<div class="position">'.$note.'</div><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier_position.$bouton_supprimer_position.'</div>' );
  }
  else if( in_array($rubrique_type,array('eval','socle')) && in_array($page_colonne,array('objectif','position','maitrise','reussite')) ) // pas d'automatisation pour 'enscompl'
  {
    $indice = OutilBilan::determiner_degre_maitrise($contenu); // pas de valeur "dispensé" qui puisse être générée automatiquement
    $origine .= ' : '.$contenu.' %';
    if( ($rubrique_type=='socle') && ($rubrique_id==12) ) // langue étrangère avec positionnement dispensé possible
    {
      $id_debut = 0;
    }
    else
    {
      $id_debut = 1;
    }
    $id_fin = ($page_colonne!='reussite') ? 4 : 3 ;
    for( $id=$id_debut ; $id<=$id_fin ; $id++ )
    {
      $texte = ($id==$indice) ? '<b>X</b>' : '' ;
      Json::add_row( 'td_'.$id , $texte );
    }
    Json::add_row( 'td_'.$PAGE_COLONNE , '<div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier_position.$bouton_supprimer_position.'</div><i>'.$indice.'</i>' );
    Json::end( TRUE );
  }
  else if($rubrique_type=='bilan')
  {
    $saisie_valeur = html($contenu);
    $texte_intro = ($rubrique_type!='socle') ? '<span class="notnow">'.rubrique_texte_intro('bilan',$eleve_id).'</span><br />' : '';
    Json::end( TRUE , $texte_intro.'<span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else if($rubrique_type=='viesco')
  {
    $saisie_valeur = html($contenu);
    Json::end( TRUE , '<span class="notnow">'.rubrique_texte_intro('viesco',$eleve_id).'</span><br /><span class="appreciation">'.$saisie_valeur.'</span><div class="notnow" data-id="'.$saisie_id.'">'.echo_origine($origine).$bouton_modifier.$bouton_supprimer.'</div>' );
  }
  else
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 4 : corriger une appréciation saisie par un collègue
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='corriger_faute')
{
  if( !$appreciation || !$prof_id )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  DB_STRUCTURE_LIVRET::DB_modifier_saisie( $saisie_id , 'appreciation' , $appreciation , 'saisie' , $prof_id );
  Json::end( TRUE , html($appreciation) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 5 & 6 : affichage des données d'un élève indiqué (si initialisation, alors le groupe classe, sauf socle)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Si un personnel accède à la saisie de synthèse, il ne faut pas seulement récupérer les données qui concerne ses matières.
$liste_matiere_id = ( ($OBJET=='modifier') || ($BILAN_ETAT=='2rubrique') ) ? $liste_matiere_id : '' ;

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
  $form_choix_eleve .= ( ($PAGE_PERIODICITE!='cycle') && ($OBJET=='tamponner') ) ? ( ($mode=='texte') ? ' <button id="change_mode" type="button" class="stats">Interface graphique</button>' : ' <button id="change_mode" type="button" class="texte">Interface détaillée</button>' ) : '' ;
  $form_choix_eleve .= '</div></form><hr />';
  $eleve_id = ($PAGE_PERIODICITE!=='cycle') ? 0 : $DB_TAB[0]['user_id'] ;
  // sous-titre
  if($ACTION=='tamponner')
  {
    $sous_titre = 'Éditer l\'appréciation de synthèse';
  }
  else
  {
    $tab_modif_rubrique = array
    (
      'c1_theme'   => 'le positionnement et les appréciations par rubrique',
      'c2_domaine' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
      'c2_socle'   => 'le degré de maîtrise des composantes du socle',
      'c3_domaine' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
      'c3_matiere' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
      'c3_socle'   => 'le degré de maîtrise des composantes du socle',
      'c4_matiere' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
      'c4_socle'   => 'le degré de maîtrise des composantes du socle',
    );
    $sous_titre = 'Éditer '.$tab_modif_rubrique[$PAGE_RUBRIQUE_TYPE];
  }
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

// Récupérer les saisies déjà effectuées ou enregistrées pour la période en cours et les périodes antérieures

$tab_saisie       = array();  // [eleve_id][rubrique_type][rubrique_id][saisie_objet] => array(prof_id,saisie_valeur,saisie_origine,listing_profs); avec eleve_id=0 pour position ou appréciation sur la classe
$tab_saisie_avant = array();  // [eleve_id][rubrique_type][rubrique_id][jointure_periode][saisie_objet] => array(prof_id,saisie_valeur);
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $eleve_id , 0 /*prof_id*/ , TRUE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  if($DB_ROW['jointure_periode']==$JOINTURE_PERIODE)
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
  else
  {
    $tab_saisie_avant[$eleve_id][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']][$DB_ROW['jointure_periode']] = $DB_ROW['saisie_valeur'];
  }
}
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id , 0 /*prof_id*/ , TRUE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  if($DB_ROW['jointure_periode']==$JOINTURE_PERIODE)
  {
    $tab_saisie[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
      'saisie_id'     => $DB_ROW['livret_saisie_id'] ,
      'prof_id'       => $DB_ROW['user_id'] ,
      'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
      'saisie_origine'=> $DB_ROW['saisie_origine'] ,
      'listing_profs' => $DB_ROW['listing_profs'] ,
    );
  }
  else
  {
    $tab_saisie_avant[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']][$DB_ROW['jointure_periode']] = $DB_ROW['saisie_valeur'];
  }
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

$make_action   = $OBJET; // 'modifier' || 'tamponner' (et plus seulement 'saisir')
$make_html     = ( ($PAGE_PERIODICITE!='cycle') && ($OBJET=='tamponner') && ($mode=='graphique') ) ? FALSE : TRUE ;
$make_pdf      = FALSE;
$make_csv      = FALSE;
$make_graph    = ( ($PAGE_PERIODICITE!='cycle') && ($OBJET=='tamponner') && ($mode=='graphique') ) ? TRUE : FALSE ;

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
  Json::add_row( 'html' , '<h2>'.$sous_titre.'</h2>' );
  Json::add_row( 'html' , $form_choix_eleve );
  Json::add_row( 'html' , '<form action="#" method="post" id="zone_resultat_eleve" onsubmit="return false">'.$releve_HTML.'</form>' );
}
else
{
  Json::add_row( 'html' , $releve_HTML );
}

Json::end( TRUE );

?>
