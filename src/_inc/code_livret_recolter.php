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

$tab_rubrique_type = array('eval','socle','epi','ap','parcours','bilan','viesco');
$tab_saisie_objet = array('position','appreciation','elements');
$tab_lsu_civilite = array( 'I'=>'' , 'M'=>'M' , 'F'=>'MME' );
$tab_compte_rendu = array(
  'erreur' => array() ,
  'alerte' => array() ,
);
$tab_objet_used = array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Extraction des infos sur la période + détermination des informations principales
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(substr($periode,0,7)=='periode')
{
  $PAGE_PERIODICITE = 'periode';
  $JOINTURE_PERIODE = substr($periode,7);
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_periode_info( $JOINTURE_PERIODE , $classe_id );
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Jointure période/classe transmise indéfinie !' );
  }
  $periode_id       = $DB_ROW['periode_id'];
  $cycle_id         = 0;
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = $date_mysql_debut;
  $date_fin   = $date_mysql_fin;
}
else
{
  $PAGE_PERIODICITE = $periode;
  $JOINTURE_PERIODE = '';
  $periode_id       = 0;
  $cycle_id         = substr($PAGE_REF,-1);
  $date_mysql_debut = NULL;
  $date_mysql_fin   = NULL;
  $date_debut = '';
  $date_fin   = '';
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_page_groupe_info( $classe_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE );

if(empty($DB_ROW))
{
  Json::end( FALSE , 'Association classe / livret introuvable !' );
}
if($DB_ROW['jointure_etat']!='5complet')
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}
if(is_null($DB_ROW['jointure_date_verrou']))
{
  Json::end( FALSE , 'Bilan non verrouillé !' );
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
$classe_ref          = $DB_ROW['groupe_ref'];
$classe_chef_id      = $DB_ROW['groupe_chef_id'];
$DATE_VERROU         = $DB_ROW['jointure_date_verrou'];
$BILAN_TYPE_ETABL    = in_array($PAGE_RUBRIQUE_TYPE,array('c3_matiere','c4_matiere','c3_socle','c4_socle')) ? 'college' : 'ecole' ;
$CONFIGURATION_REF   = $DB_ROW['configuration_ref'];

// Forcer la récupération des paramètres du bilan, au cas où un changement de paramétrage viendrait d'être effectué.
// La mémorisation se fait quand même en session pour des raisons historiques (les premiers bilans archivés utilisent cette variable) et un peu pratique (variable globale accessible partout).
$tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'livret' , $CONFIGURATION_REF );
foreach($tab_configuration as $key => $val)
{
  $_SESSION['OFFICIEL']['LIVRET_'.Clean::upper($key)] = $val;
}
$_SESSION['OFFICIEL']['LIVRET_CONFIG_REF'] = $CONFIGURATION_REF;

$champ_classe     = ($BILAN_TYPE_ETABL=='college') ? 'code-division'    : 'classe-ref' ;
$champ_chef_etabl = ($BILAN_TYPE_ETABL=='college') ? 'responsable-etab' : 'directeur' ;
if($BILAN_TYPE_ETABL=='college')
{
  // Au 2D : 8 caractères dans SIECLE BEE, mais peut avoir été modifié car 10 max dans SACoche à cause du 1D.
  $classe_value = mb_substr($classe_ref,0,8);
}
else
{
  // Au 1D : 5 à 6 chiffres dans ONDE, mais complété dans SACoche avec le niveau.
  list( $classe_value , $classe_niveau ) = explode('_',$classe_ref,2) + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
}
$tab_classe = array();

$annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
$millesime = To::annee_scolaire('siecle',$annee_decalage);

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
// Récupérer la période
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Au 1D, il faut transmettre les dates de début / fin avec la période, et celles-ci peuvent en théorie différer d'une classe à une autre.
// Une clef d'unicité étant sur millésime x indice x nb période x dates, la solution consiste à insérer tout ça ou presque dans l'identifiant

$tab_periode = array();
$key_periode = ($BILAN_TYPE_ETABL=='college') ? 'PER'.$periode_id : 'PER'.$periode_id.'D'.substr($date_debut,-5,2).substr($date_debut,-2,2).'F'.substr($date_fin,-5,2).substr($date_fin,-2,2) ;
$affichage_periode = ($PAGE_PERIODICITE=='periode') ? TRUE : FALSE ;

$tab_periode = (!$affichage_periode) ? array() : array(
  $key_periode => array(
    'millesime'   => $millesime,
    'indice'      => substr($periode_nom,-3,1),
    'nb-periodes' => substr($periode_nom,-1),
    'date-debut'  => $date_debut, // 1D
    'date-fin'    => $date_fin,   // 1D
  )
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer le chef d'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_chef_etabl = array();
$affichage_chef_etabl = TRUE ; // besoin 1D + 2D ; période + cycle

if($affichage_chef_etabl)
{
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_chef_infos($classe_chef_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , "Absence de désignation du chef d'établissement ou directeur d'école pour cette classe !" );
  }
  $key_chef_etabl = 'DIR'.$classe_chef_id;
  if($BILAN_TYPE_ETABL=='college')
  {
    $tab_chef_etabl[$key_chef_etabl] = array(
      'libelle' => To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']) // max 100 caractères, mais pour SACoche ce ne peut pas dépasser 4+25+25
    );
  }
  else
  {
    if($DB_ROW['user_genre']=='I')
    {
      $tab_erreur['civilité'] = 'MME';
      $DB_ROW['user_genre'] = 'F';
    }
    if(!$DB_ROW['user_nom'])
    {
      $tab_erreur['nom'] = 'MACHIN';
      $DB_ROW['user_nom'] = 'MACHIN';
    }
    if(!$DB_ROW['user_prenom'])
    {
      $tab_erreur['prénom'] = '-';
      $DB_ROW['user_prenom'] = '-';
    }
    if(!empty($tab_erreur))
    {
      $tab_compte_rendu['alerte'][$key_prof] = 'Absence de '.implode(' / ',array_keys($tab_erreur)).' pour "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : "'.implode(' ',$tab_erreur).'" imposé pour éviter un rejet bloquant.';
    }
    $tab_chef_etabl[$key_chef_etabl] = array(
      'civilite' => $tab_lsu_civilite[$DB_ROW['user_genre']],
      'nom'      => $DB_ROW['user_nom'],    // max 100 caractères : 25 dans SACoche
      'prenom'   => $DB_ROW['user_prenom'], // max 100 caractères : 25 dans SACoche
    );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Paramètres du bilan
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$champ_profs_eleve_ref = ($BILAN_TYPE_ETABL=='college') ? 'prof-princ-refs'      : 'enseignant-refs' ;
$champ_chef_etabl_ref  = ($BILAN_TYPE_ETABL=='college') ? 'responsable-etab-ref' : 'directeur-ref' ;
$champ_position  = in_array($PAGE_COLONNE,array('moyenne','pourcentage')) ? 'moyenne-eleve' : 'positionnement' ;

$tab_bilan = array(
  'type'                 => $PAGE_PERIODICITE,
  $champ_profs_eleve_ref => '', // Complété ultérieurement pour le 2nd degré
  'periode-ref'          => $key_periode,
  'date-creation'        => substr($DATE_VERROU,0,10), // 1D + cycle
  'date-conseil-classe'  => substr($DATE_VERROU,0,10), // 2D
  'date-scolarite'       => $date_mysql_debut,
  'date-verrou'          => str_replace(' ','T',$DATE_VERROU),
  $champ_chef_etabl_ref  => $key_chef_etabl,
  'position'             => $champ_position,
  'cycle'                => $cycle_id,
  'millesime'            => $millesime,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation Vie scolaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_assiduite = ($PAGE_VIE_SCOLAIRE) ? TRUE : FALSE ;

$tab_viesco = (!$affichage_assiduite) ? array() : array(
  'appreciation'        => '',
  'nb-retards'          => 0,
  'nb-abs-justifiees'   => 0,
  'nb-abs-injustifiees' => 0,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer des infos dans les fichiers SIECLE archivés : ref classe ; epp/local ; modalite-election ; date-scolarite
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_lsu_prof_type    = array_fill_keys( array('epp','local'), TRUE );
$tab_lsu_mod_election = array_fill_keys( array('S','O','F','N','L','R','X'), TRUE );
unset($tab_lsu_mod_election['N']); // Dans SIECLE mais pas dans les spécifications...

if($BILAN_TYPE_ETABL=='college')
{
  // Fichier sts_emp_UAI
  $tab_SIECLE = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('sts_emp_UAI');
  if(empty($tab_SIECLE))
  {
    Json::end( FALSE , 'Fichier sts_emp_UAI manquant ! Voir page précédente pour les consignes.' );
  }
  // Ref classe
  if(empty($tab_SIECLE['DONNEES']['STRUCTURE']['DIVISIONS']['DIVISION']))
  {
    Json::end( FALSE , 'Classes absentes du fichier sts_emp_UAI !' );
  }
  $tab_siecle_division = array();
  foreach($tab_SIECLE['DONNEES']['STRUCTURE']['DIVISIONS']['DIVISION'] as $tab)
  {
    $division_ref = $tab['@attributes']['CODE'];
    if(isset($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']['@attributes']['CODE']))
    {
      $code_mef = $tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']['@attributes']['CODE'] ;
      $tab_siecle_division[$division_ref][$code_mef] = TRUE;
    }
    elseif(is_array($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']))
    {
      foreach($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE'] as $tab_mef)
      {
        $code_mef = $tab_mef['@attributes']['CODE'] ;
        $tab_siecle_division[$division_ref][$code_mef] = TRUE;
      }
    }
  }
  if(!isset($tab_siecle_division[$classe_value]))
  {
    Json::end( FALSE , 'La référence de la classe "'.$classe_value.'" ne figure pas dans celles du fichier SIECLE !<br />SIECLE comporte '.implode(' ',array_keys($tab_siecle_division)) );
  }
  // Type epp | local
  if(empty($tab_SIECLE['DONNEES']['INDIVIDUS']['INDIVIDU']))
  {
    Json::end( FALSE , 'Enseignants absents du fichier sts_emp_UAI !' );
  }
  $tab_siecle_prof_type = array();
  foreach($tab_SIECLE['DONNEES']['INDIVIDUS']['INDIVIDU'] as $tab)
  {
    $tab_siecle_prof_type[ $tab['@attributes']['ID'] ] = $tab['@attributes']['TYPE'];
  }
  // Fichier Nomenclature
  $tab_SIECLE = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Nomenclature');
  if(empty($tab_SIECLE))
  {
    Json::end( FALSE , 'Fichier Nomenclature manquant ! Voir page précédente pour les consignes.' );
  }
  // Modalités d'élection
  if(empty($tab_SIECLE['DONNEES']['PROGRAMMES']['PROGRAMME']))
  {
    Json::end( FALSE , 'Enseignements absents du fichier Nomenclature !' );
  }
  $tab_siecle_modalite_election = array();
  foreach($tab_SIECLE['DONNEES']['PROGRAMMES']['PROGRAMME'] as $tab)
  {
    if( isset($tab_siecle_division[$classe_value][$tab['CODE_MEF']]) && (float)$tab['HORAIRE'] )
    {
      $tab_siecle_modalite_election[ $tab['CODE_MATIERE'] ] = $tab['CODE_MODALITE_ELECT'];
    }
  }
  // Fichier Communs ; n'est utilisé que pour vérifier la date de début d'année scolaire, on ne bloque pas si le fichier manque
  $tab_SIECLE = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Communs');
  if(!empty($tab_SIECLE))
  {
    $date_mysql_debut_annee = To::date_french_to_mysql($tab_SIECLE['DONNEES']['ANNEE_SCOLAIRE']['DATE_DEBUT_ELEVE']);
    if($date_mysql_debut_annee>$date_mysql_debut)
    {
      $date_mysql_debut = $date_mysql_debut_annee;
      $tab_bilan['date-scolarite'] = $date_mysql_debut_annee;
    }
  }
  // Fichier Eleves
  $tab_SIECLE = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Eleves');
  if(empty($tab_SIECLE))
  {
    Json::end( FALSE , 'Fichier ElevesSansAdresses manquant ! Voir page précédente pour les consignes.' );
  }
  if(empty($tab_SIECLE['DONNEES']['ELEVES']['ELEVE']))
  {
    Json::end( FALSE , 'Élèves absents du fichier ElevesSansAdresses !' );
  }
  $tab_siecle_eleve_date = array();
  foreach($tab_SIECLE['DONNEES']['ELEVES']['ELEVE'] as $tab)
  {
    $eleve_id = $tab['@attributes']['ELEVE_ID'];
    if( isset($tab['DATE_ENTREE']) )
    {
      $date_mysql_entree = To::date_french_to_mysql($tab['DATE_ENTREE']);
      $tab_siecle_eleve_date[$eleve_id]['date_entree'] = ($date_mysql_entree<$date_mysql_debut) ? $date_mysql_debut : $date_mysql_entree ;
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifier des infos dans le fichier ONDE archivé : ref classe (récupérer au passage le nom complet de la classe)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($BILAN_TYPE_ETABL=='ecole')
{
  $tab_ONDE = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Onde');
  if(empty($tab_ONDE))
  {
    Json::end( FALSE , 'Fichier CSVExtraction manquant ! Voir page précédente pour les consignes.' );
  }
  // Utiliser la 1e ligne pour repérer les colonnes intéressantes
  $numero_colonne_classe_id  = -1;
  $numero_colonne_classe_nom = -1;
  // Données de la ligne d'en-tête
  $tab_elements = $tab_ONDE[0];
  if(!is_array($tab_elements))
  {
    Json::end( FALSE , 'Fichier CSVExtraction à ré-importer ! Voir page précédente pour les consignes.' );
  }
  foreach ($tab_elements as $numero => $element)
  {
    $numero_colonne_classe_id  = ($element=='Identifiant classe') ? $numero : $numero_colonne_classe_id ;
    $numero_colonne_classe_nom = ($element=='Libellé classe')     ? $numero : $numero_colonne_classe_nom ;
  }
  if( ($numero_colonne_classe_id<0) || ($numero_colonne_classe_nom<0) )
  {
    Json::end( FALSE , 'Données de classe absentes du fichier CSVExtraction !' );
  }
  // Supprimer la 1e ligne
  unset($tab_ONDE[0]);
  $tab_onde_classe = array();
  $numero_colonne_max = max($numero_colonne_classe_id,$numero_colonne_classe_nom);
  foreach ($tab_ONDE as $tab_elements)
  {
    if(count($tab_elements)>$numero_colonne_max)
    {
      $onde_classe_id  = $tab_elements[$numero_colonne_classe_id];
      $onde_classe_nom = $tab_elements[$numero_colonne_classe_nom];
      $tab_onde_classe[$onde_classe_id] = $onde_classe_nom;
    }
  }
  if(!isset($tab_onde_classe[$classe_value]))
  {
    Json::end( FALSE , 'L\'identifiant de la classe "'.$classe_value.'" ne figure pas dans celles du fichier ONDE !<br />ONDE comporte '.implode(' ',array_keys($tab_onde_classe)) );
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Récupérer la classe (1D)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  $key_classe = 'CL'.$classe_value;
  $tab_classe[$key_classe] = array(
    'id-be'   => $classe_value,
    'libelle' => $tab_onde_classe[$classe_value], // max 50 caractères : sans doute pareil dans ONDE
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve = array();
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom,user_sconet_id,user_reference' /*champs*/ , $periode_id );
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$classe_nom.' !' );
}
foreach($DB_TAB as $DB_ROW)
{
  if( ( ($BILAN_TYPE_ETABL=='college') && $DB_ROW['user_sconet_id'] ) || ( ($BILAN_TYPE_ETABL=='ecole') && $DB_ROW['user_reference'] ) )
  {
    $classe_eleve = ($BILAN_TYPE_ETABL=='college') ? $classe_value : $key_classe ;
    $tab_eleve[$DB_ROW['user_id']] = array(
      'eleve'         => array(
        'id'          => 'ELV'.$DB_ROW['user_id'],
        'id-be'       => $DB_ROW['user_sconet_id'], // 2D
        'ine'         => $DB_ROW['user_reference'], // 1D
        $champ_classe => $classe_eleve, // 2D c'est la référence SIECLE ; 1D c'est une chaine alphanumérique qui permet d'obtenir l'identifiant ONDE via un autre tableau
        'nom'         => $DB_ROW['user_nom'], // max 100 caractères : 25 dans SACoche
        'prenom'      => $DB_ROW['user_prenom'], // max 100 caractères : 25 dans SACoche
      ),
      'commun'        => array(
        $champ_chef_etabl  => $tab_chef_etabl,
        'classe'           => $tab_classe, // 1D
        'periode'          => $tab_periode,
        'discipline'       => array(), // 2D
        'enseignant'       => array(),
        'element'          => array(),
        'epi-thematique'   => array(),
        'epi'              => array(),
        'ap'               => array(),
        'parcours'         => array(),
        'viesco'           => array(),
      ),
      'bilan'         => $tab_bilan + array( 'eleve-ref' => 'ELV'.$DB_ROW['user_id'] ) ,
      'acquis'        => array(),
      'epi'           => array(),
      'ap'            => array(),
      'parcours'      => array(),
      'modaccomp'     => array(),
      'synthese'      => array(),
      'viesco'        => $tab_viesco,
      'socle'         => array(),
      'enscompl'      => array(),
      'responsables'  => array(),
    );
    if(isset($tab_siecle_eleve_date[$DB_ROW['user_sconet_id']]['date_entree']))
    {
      $tab_eleve[$DB_ROW['user_id']]['bilan']['date-scolarite'] = $tab_siecle_eleve_date[$DB_ROW['user_sconet_id']]['date_entree'];
    }
  }
  else if($BILAN_TYPE_ETABL=='college')
  {
    $tab_compte_rendu['erreur'][] = 'Absence d\'identifiant SIECLE pour l\'élève "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : données non exportables.';
  }
  else if($BILAN_TYPE_ETABL=='ecole')
  {
    $tab_compte_rendu['erreur'][] = 'Absence d\'identifiant national (INE) pour l\'élève "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : données non exportables.';
  }
}
if(empty($tab_eleve))
{
  Json::end( FALSE , 'Aucun élève trouvé avec un identifiant SIECLE (2D) / ONDE (1D) renseigné !' );
}
$liste_eleve_id = implode(',',array_keys($tab_eleve));

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liste de tous les professeurs / personnels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_prof = array();
$tab_profils_types = array('professeur','directeur');
$listing_champs = 'user_id, user_sconet_id, user_genre, user_nom, user_prenom';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , $listing_champs , FALSE /*with_classe*/ );
foreach($DB_TAB as $DB_ROW)
{
  $key_prof = 'ENS'.$DB_ROW['user_id'];
  if($DB_ROW['user_sconet_id'])
  {
    $id_sts = $DB_ROW['user_sconet_id'];
    $type = ( isset($tab_siecle_prof_type[$id_sts]) && isset($tab_lsu_prof_type[$tab_siecle_prof_type[$id_sts]]) ) ? $tab_siecle_prof_type[$id_sts] : 'epp' ;
  }
  else
  {
    $id_sts = sprintf("%'96u",$DB_ROW['user_id']);
    $type = 'epp' ;
  }
  if( ($BILAN_TYPE_ETABL=='college') && !$DB_ROW['user_sconet_id'] )
  {
    $tab_compte_rendu['alerte'][$key_prof] = 'Absence d\'identifiant SIECLE (STS) pour "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : génèrera un message d\'alerte non bloquant.';
  }
  if($BILAN_TYPE_ETABL=='ecole')
  {
    $tab_erreur = array();
    if($DB_ROW['user_genre']=='I')
    {
      $tab_erreur['civilité'] = 'MME';
      $DB_ROW['user_genre'] = 'F';
    }
    if(!$DB_ROW['user_nom'])
    {
      $tab_erreur['nom'] = 'MACHIN';
      $DB_ROW['user_nom'] = 'MACHIN';
    }
    if(!$DB_ROW['user_prenom'])
    {
      $tab_erreur['prénom'] = '-';
      $DB_ROW['user_prenom'] = '-';
    }
    if(!empty($tab_erreur))
    {
      $tab_compte_rendu['alerte'][$key_prof] = 'Absence de '.implode(' / ',array_keys($tab_erreur)).' pour "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : "'.implode(' ',$tab_erreur).'" imposé pour éviter un rejet bloquant.';
    }
  }
  $tab_prof[$key_prof] = array(
    'type'     => $type, // 2D
    'id-sts'   => $id_sts, // 2D
    'civilite' => $tab_lsu_civilite[$DB_ROW['user_genre']],
    'nom'      => $DB_ROW['user_nom'], // max 100 caractères : 25 dans SACoche
    'prenom'   => $DB_ROW['user_prenom'], // max 100 caractères : 25 dans SACoche
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les professeurs principaux (2D) ou les professeurs associés à la classe (1D)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_profs_eleve = array();

if($BILAN_TYPE_ETABL=='college')
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_profs_principaux($classe_id);
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Absence de désignation du professeur principal pour la classe '.$classe_nom.' !' );
  }
}
else if($BILAN_TYPE_ETABL=='ecole')
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'professeur' , 1 /*statut*/ , 'classe' /*groupe_type*/ , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id, user_genre, user_nom, user_prenom' );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Absence d\'enseignant rattaché à la classe '.$classe_nom.' !' );
  }
}
foreach($DB_TAB as $DB_ROW)
{
  $key_prof = 'ENS'.$DB_ROW['user_id'];
  $tab_profs_eleve[$DB_ROW['user_id']] = $key_prof;
  foreach($tab_eleve as $eleve_id => $tab)
  {
    $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
  }
}
$profs_eleve_refs = implode(' ', $tab_profs_eleve);
foreach($tab_eleve as $eleve_id => $tab)
{
  $tab_eleve[$eleve_id]['bilan'][$champ_profs_eleve_ref] = $profs_eleve_refs;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liste de toutes les rubriques (acquis scolaires) ou des domaines et composantes du socle (fin de cycle)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubrique = array();
if($PAGE_PERIODICITE=='periode')
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $key_rubrique = 'MAT'.$DB_ROW['livret_rubrique_id'];
    if($BILAN_TYPE_ETABL=='college')
    {
      if($DB_ROW['matiere_siecle'])
      {
        $code = sprintf("%06s",$DB_ROW['rubrique_id_livret']); // Pour des matières académiques ce peut être un littéral et non un entier.
        $modalite_election = ( isset($tab_siecle_modalite_election[$code]) && isset($tab_lsu_mod_election[$tab_siecle_modalite_election[$code]]) ) ? $tab_siecle_modalite_election[$code] : 'S' ;
      }
      else
      {
        $code = sprintf("%'96u",$DB_ROW['livret_rubrique_id']);
        $modalite_election = 'X' ;
      }
      if(!$DB_ROW['matiere_siecle'])
      {
        $tab_compte_rendu['alerte'][$key_rubrique] = 'Discipline utilisée "'.html($DB_ROW['rubrique']).'" hors SIECLE : génèrera un message d\'alerte non bloquant.';
      }
      $tab_rubrique[$key_rubrique] = array(
        'libelle'           => mb_substr($DB_ROW['rubrique'],0,40), // max 40 caractères : 63 dans SACoche
        'code'              => $code,
        'modalite-election' => $modalite_election,
      );
    }
    elseif($BILAN_TYPE_ETABL=='ecole')
    {
      $tab_rubrique[$key_rubrique] = $DB_ROW['rubrique_id_livret'];
    }
  }
}
else if($PAGE_PERIODICITE=='cycle')
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_elements_livret();
  foreach($DB_TAB as $DB_ROW)
  {
    if(!empty($DB_ROW['socle_domaine_code_livret']))
    {
      $tab_rubrique['socle'][$DB_ROW['socle_domaine_ordre_livret']] = $DB_ROW['socle_domaine_code_livret'];
    }
    elseif(!empty($DB_ROW['socle_composante_code_livret']))
    {
      $tab_rubrique['socle'][$DB_ROW['socle_composante_ordre_livret']] = $DB_ROW['socle_composante_code_livret'];
    }
  }
  $nb_positionnements_socle = count($tab_rubrique['socle']);
}

$tab_rubrique_a_eviter = array();
if($BILAN_TYPE_ETABL=='college')
{
  // Si on connait les langues associées aux élèves, alors on essaye de limiter les confusions LV1 / LV2 en cas de modification des liaisons aux rubriques en cours de route.
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les saisies déjà effectuées ou enregistrées pour la page de livret concernée
// Pas besoin de 'saisie_id' ni 'prof_id' ni 'saisie_origine' ni 'acquis_detail'
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note);

$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
  );
}
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array(
    'saisie_valeur' => $DB_ROW['saisie_valeur'] ,
    'listing_profs' => $DB_ROW['listing_profs'] ,
  );
}


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_epi = array();
$tab_epi_theme = array();
if($PAGE_EPI)
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi( $classe_id , $PAGE_REF );
  foreach($DB_TAB as $DB_ROW)
  {
    $key_rubrique = 'EPI'.$DB_ROW['livret_epi_id'].$key_periode;
    $description = isset($tab_saisie[0]['epi'][$DB_ROW['livret_epi_id']]['appreciation']) ? $tab_saisie[0]['epi'][$DB_ROW['livret_epi_id']]['appreciation']['saisie_valeur'] : NULL ;
    $tab_epi[$key_rubrique] = array(
      'id'              => $key_rubrique ,
      'intitule'        => $DB_ROW['livret_epi_titre'] , // max 150 caractères : 128 dans SACoche
      'thematique'      => $DB_ROW['livret_epi_theme_code'] ,
      'description'     => $description , // max 600 caractères : idem dans SACoche
      'discipline-refs' => array() ,
      'enseignant-refs' => array() ,
    );
    if($DB_ROW['livret_epi_theme_origine']==2)
    {
      $tab_epi_theme[$key_rubrique][$DB_ROW['livret_epi_theme_code']] = $DB_ROW['livret_epi_theme_nom'];
    }
    $tab_id = explode(' ',$DB_ROW['matiere_prof_id']);
    $tab_mat = array();
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      if(!isset($tab_prof['ENS'.$user_id]))
      {
        $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" associé à un enseignant (n°'.$user_id.') inconnu : non exporté.';
        unset($tab_id[$key]);
      }
      elseif(!isset($tab_rubrique['MAT'.$matiere_id]))
      {
        $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" associé à une matière (n°'.$matiere_id.') inconnue : non exporté.';
        unset($tab_id[$key]);
      }
      else
      {
        $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
        $tab_epi[$key_rubrique]['discipline-refs'][] = 'MAT'.$matiere_id.$modelec;
        $tab_epi[$key_rubrique]['enseignant-refs'][] = 'ENS'.$user_id;
        $tab_mat[$matiere_id] = TRUE;
      }
    }
    if(count($tab_mat)<2)
    {
      $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" avec moins de 2 matières associées : non exporté.';
    }
    else if($description)
    {
      foreach($tab_eleve as $eleve_id => $tab)
      {
        $tab_eleve[$eleve_id]['commun']['epi'][$key_rubrique] = $tab_epi[$key_rubrique];
        if(isset($tab_epi_theme[$key_rubrique]))
        {
          $tab_eleve[$eleve_id]['commun']['epi-thematique'] = array_merge( $tab_eleve[$eleve_id]['commun']['epi-thematique'], $tab_epi_theme[$key_rubrique] );
        }
        $tab_eleve[$eleve_id]['epi'][$key_rubrique] = array(); // Pour le relier à l'élève même en cas d'absence de commentaire sur l'implication de l'élève
        foreach($tab_id as $ids)
        {
          list($matiere_id,$user_id) = explode('_',$ids);
          $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
          $tab_eleve[$eleve_id]['commun']['enseignant']['ENS'.$user_id] = $tab_prof['ENS'.$user_id];
          $tab_eleve[$eleve_id]['commun']['discipline']['MAT'.$matiere_id.$modelec] = $tab_rubrique['MAT'.$matiere_id];
          $tab_objet_used['ENS'.$user_id] = TRUE;
          $tab_objet_used['MAT'.$matiere_id] = TRUE;
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// AP
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_ap = array();
if($PAGE_AP)
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_ap( $classe_id , $PAGE_REF );
  foreach($DB_TAB as $DB_ROW)
  {
    $key_rubrique = 'AP'.$DB_ROW['livret_ap_id'].$key_periode;
    $description = isset($tab_saisie[0]['ap'][$DB_ROW['livret_ap_id']]['appreciation']) ? $tab_saisie[0]['ap'][$DB_ROW['livret_ap_id']]['appreciation']['saisie_valeur'] : NULL ;
    $tab_ap[$key_rubrique] = array(
      'id'              => $key_rubrique ,
      'intitule'        => $DB_ROW['livret_ap_titre'] , // max 150 caractères : 128 dans SACoche
      'description'     => $description , // max 600 caractères : idem dans SACoche
      'discipline-refs' => array() ,
      'enseignant-refs' => array() ,
    );
    $tab_id  = explode(' '   ,$DB_ROW['matiere_prof_id']);
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      if(!isset($tab_prof['ENS'.$user_id]))
      {
        $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" associé à un enseignant (n°'.$user_id.') inconnu : non exporté.';
        unset($tab_id[$key]);
      }
      elseif(!isset($tab_rubrique['MAT'.$matiere_id]))
      {
        $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" associé à une matière (n°'.$matiere_id.') inconnue : non exporté.';
        unset($tab_id[$key]);
      }
      else
      {
        $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
        $tab_ap[$key_rubrique]['discipline-refs'][] = 'MAT'.$matiere_id.$modelec;
        $tab_ap[$key_rubrique]['enseignant-refs'][] = 'ENS'.$user_id;
      }
    }
    if(count($tab_id)<1)
    {
      $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" sans couple {enseignant;matière} associé : non exporté.';
    }
    else if($description)
    {
      foreach($tab_eleve as $eleve_id => $tab)
      {
        $tab_eleve[$eleve_id]['commun']['ap'][$key_rubrique] = $tab_ap[$key_rubrique];
        $tab_eleve[$eleve_id]['ap'][$key_rubrique] = array(); // Pour le relier à l'élève même en cas d'absence de commentaire sur l'implication de l'élève
        foreach($tab_id as $ids)
        {
          list($matiere_id,$user_id) = explode('_',$ids);
          $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
          $tab_eleve[$eleve_id]['commun']['enseignant']['ENS'.$user_id] = $tab_prof['ENS'.$user_id];
          $tab_eleve[$eleve_id]['commun']['discipline']['MAT'.$matiere_id.$modelec] = $tab_rubrique['MAT'.$matiere_id];
          $tab_objet_used['ENS'.$user_id] = TRUE;
          $tab_objet_used['MAT'.$matiere_id] = TRUE;
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_parcours = array();
if($PAGE_PARCOURS)
{
  $tab_parcours_code = explode(',',$PAGE_PARCOURS);
  foreach($tab_parcours_code as $parcours_code)
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours( $parcours_code ,  $classe_id , $PAGE_REF );
    if(!empty($DB_TAB))
    {
      $DB_ROW = $DB_TAB[0]; // 1 parcours de chaque type au maximum par classe
      $key_parcours = ($BILAN_TYPE_ETABL=='college') ? 'PAR'.$DB_ROW['livret_parcours_id'] : substr($parcours_code,-3).$classe_value ; // à cause des parcours définis au 1D sur des bouts de classe... et donc avec des ids différents
      $key_rubrique = $key_parcours.$key_periode;
      $projet = isset($tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']) ? $tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']['saisie_valeur'] : NULL ;
      $tab_parcours[$key_rubrique] = array(
        'periode-ref' => $key_periode ,
        $champ_classe => $classe_eleve ,
        'code'        => $parcours_code ,
        'projet'      => $projet ,
      );
      if($projet)
      {
        foreach($tab_eleve as $eleve_id => $tab)
        {
          $tab_eleve[$eleve_id]['commun']['parcours'][$key_rubrique] = $tab_parcours[$key_rubrique];
        }
      }
      else
      {
        // Au cas où il n'y aurait qu'une appréciation sur le commentaire de l'élève, ce parcours est ajouté après coup, mais s'il n'y a pas de descriptif global LSU n'est alors pas content.
        $tab_parcours[$key_rubrique]['projet'] = '-';
        $tab_compte_rendu['alerte'][$key_rubrique] = 'Absence de descriptif pour le '.html($DB_ROW['livret_parcours_type_nom']).' : champ normalement requis passé à "-".';
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modalités d'accompagnement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($PAGE_PERIODICITE=='periode')
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_modaccomp( $liste_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $value = ($DB_ROW['info_complement']) ? $DB_ROW['info_complement'] : NULL ; // max 600 caractères : 255 dans SACoche
    $tab_eleve[$DB_ROW['user_id']]['modaccomp'][$DB_ROW['livret_modaccomp_code']] = $value;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enseignements de complément
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($PAGE_PERIODICITE=='cycle') && ($cycle_id==4) )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_enscompl( $liste_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve[$DB_ROW['eleve_id']]['enscompl'][$DB_ROW['livret_enscompl_id']] = $DB_ROW['livret_enscompl_code'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_caract_max_par_colonne = 50;
$tab_element_to_rubrique = array();
foreach($tab_saisie as $eleve_id => $tab_tmp_eleve)
{
  if( $eleve_id )
  {
    foreach($tab_tmp_eleve as $rubrique_type => $tab_tmp_rubrique)
    {
      foreach($tab_tmp_rubrique as $rubrique_id => $tab_tmp_saisie)
      {
        // AQUIS SCOLAIRES
        if( ($rubrique_type=='eval') && isset($tab_rubrique['MAT'.$rubrique_id]) && !isset($tab_rubrique_a_eviter[$eleve_id][$rubrique_id]) )
        {
          if($BILAN_TYPE_ETABL=='college')
          {
            $key_rubrique = 'MAT'.$rubrique_id;
            $modelec = $tab_rubrique['MAT'.$rubrique_id]['modalite-election'];
            $tab_eleve[$eleve_id]['commun']['discipline'][$key_rubrique.$modelec] = $tab_rubrique[$key_rubrique];
          }
          elseif($BILAN_TYPE_ETABL=='ecole')
          {
            $key_rubrique = $tab_rubrique['MAT'.$rubrique_id];
            $modelec = '';
          }
          $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec] = array(
            'profs'             => array(),
            'elements'          => array(),
            $champ_position     => NULL, // Si indéfini, utiliser ultérieurement "eleve-non-note"
            'moyenne-structure' => NULL, // Si indéfini, utiliser ultérieurement "structure-non-notee"
            'appreciation'      => NULL,
          );
          $tab_objet_used[$key_rubrique] = TRUE;
          $tab_tmp_profs = array( 'appreciation' => array() , 'position' => array() );
          foreach($tab_tmp_saisie as $saisie_objet => $saisie_info)
          {
            if($saisie_objet=='elements')
            {
              // Pour SACoche on limite afin de ne pas dépasser un recto-verso, mais pour LSU on transmet tout.
              if($saisie_info['saisie_valeur'])
              {
                $tab_valeurs = json_decode($saisie_info['saisie_valeur'], TRUE);
                foreach($tab_valeurs as $texte => $nb_used)
                {
                  $key_element = 'EL'.md5($texte);
                  $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['elements'][] = $key_element;
                  $tab_eleve[$eleve_id]['commun']['element'][$key_element] = $texte; // max 300 caractères : 255 dans SACoche
                  $tab_element_to_rubrique[$key_element][$key_rubrique.$modelec] = TRUE;
                }
              }
            }
            if($saisie_objet=='appreciation')
            {
              $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['appreciation'] = $saisie_info['saisie_valeur'];
              $tab_tmp_profs['appreciation'] = is_null($saisie_info['listing_profs']) ? array() : explode(',',$saisie_info['listing_profs']) ;
            }
            if($saisie_objet=='position')
            {
              if(!is_null($saisie_info['saisie_valeur']))
              {
                $pourcentage = $saisie_info['saisie_valeur'];
                $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ;
                $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec][$champ_position] = $note; // max 8 caractères : 7 depuis SACoche
              }
              if( in_array($PAGE_COLONNE,array('moyenne','pourcentage')) && $PAGE_MOYENNE_CLASSE && isset($tab_saisie[0]['eval'][$rubrique_id]['position']) && !is_null($tab_saisie[0]['eval'][$rubrique_id]['position']['saisie_valeur']) )
              {
                $pourcentage = $tab_saisie[0]['eval'][$rubrique_id]['position']['saisie_valeur'];
                $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ;
                $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['moyenne-structure'] = $note; // max 8 caractères : 7 depuis SACoche
              }
              $tab_tmp_profs['position'] = is_null($saisie_info['listing_profs']) ? array() : explode(',',$saisie_info['listing_profs']) ;
            }
          }
          $tab_tmp_profs = !empty($tab_tmp_profs['appreciation']) ? $tab_tmp_profs['appreciation'] : $tab_tmp_profs['position'] ;
          if( !empty($tab_tmp_profs) && ($BILAN_TYPE_ETABL=='college') )
          {
            foreach($tab_tmp_profs as $prof_id)
            {
              $key_prof = 'ENS'.$prof_id;
              $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['profs'][$prof_id] = $key_prof;
              $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
              $tab_objet_used[$key_prof] = TRUE;
            }
          }
        }
        // EPI
        if( ($rubrique_type=='epi') && isset($tab_epi['EPI'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'EPI'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['commun']['epi'][$key_rubrique] = $tab_epi[$key_rubrique];
          $tab_eleve[$eleve_id]['epi'][$key_rubrique] = array(
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
          if(isset($tab_epi_theme[$key_rubrique]))
          {
            $tab_eleve[$eleve_id]['commun']['epi-thematique'] = array_merge( $tab_eleve[$eleve_id]['commun']['epi-thematique'], $tab_epi_theme[$key_rubrique] );
          }
          // Au cas où il n'y aurait pas eu de descriptif de l'action et où cela serait associé à un prof ou une matière hors enseignement
          foreach($tab_epi[$key_rubrique]['enseignant-refs'] as $key => $key_prof)
          {
            $key_discipline = $tab_epi[$key_rubrique]['discipline-refs'][$key];
            $key_matiere = substr($key_discipline,0,-1);
            $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
            $tab_eleve[$eleve_id]['commun']['discipline'][$key_discipline] = $tab_rubrique[$key_matiere];
            $tab_objet_used[$key_prof] = TRUE;
            $tab_objet_used[$key_matiere] = TRUE;
          }
        }
        // AP
        if( ($rubrique_type=='ap') && isset($tab_ap['AP'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'AP'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['commun']['ap'][$key_rubrique] = $tab_ap[$key_rubrique];
          $tab_eleve[$eleve_id]['ap'][$key_rubrique] = array(
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
          // Au cas où il n'y aurait pas eu de descriptif de l'action et où cela serait associé à un prof ou une matière hors enseignement
          foreach($tab_ap[$key_rubrique]['enseignant-refs'] as $key => $key_prof)
          {
            $key_discipline = $tab_ap[$key_rubrique]['discipline-refs'][$key];
            $key_matiere = substr($key_discipline,0,-1);
            $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
            $tab_eleve[$eleve_id]['commun']['discipline'][$key_discipline] = $tab_rubrique[$key_matiere];
            $tab_objet_used[$key_prof] = TRUE;
            $tab_objet_used[$key_matiere] = TRUE;
          }
        }
        // Parcours
        if( ($rubrique_type=='parcours') && isset($tab_parcours['PAR'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'PAR'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['parcours'][$key_rubrique] = array(
            'code'         => $tab_parcours[$key_rubrique]['code'],
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
          // Au cas où il n'y aurait pas eu de descriptif du projet
          $tab_eleve[$eleve_id]['commun']['parcours'][$key_rubrique] = $tab_parcours[$key_rubrique];
          if( $tab_parcours[$key_rubrique]['projet']=='-' )
          {
            $tab_objet_used[$key_rubrique] = TRUE;
          }
        }
        // Socle sur bilans de fin de cycle
        if( ($rubrique_type=='socle') && isset($tab_rubrique['socle'][$rubrique_id]) && !is_null($tab_tmp_saisie['position']['saisie_valeur']) )
        {
          $pourcentage = $tab_tmp_saisie['position']['saisie_valeur'];
          $indice = ($pourcentage!=='disp') ? OutilBilan::determiner_degre_maitrise($pourcentage) : 0 ; // {0;1;2;3;4}
          $tab_eleve[$eleve_id]['socle'][$rubrique_id] = array(
            'code'           => $tab_rubrique['socle'][$rubrique_id],
            'positionnement' => $indice,
          );
        }
        // Enseignements de complément
        if( ($rubrique_type=='enscompl') && isset($tab_eleve[$eleve_id]['enscompl'][$rubrique_id]) )
        {
          $enscompl_code = $tab_eleve[$eleve_id]['enscompl'][$rubrique_id];
          $pourcentage = !is_null($tab_tmp_saisie['position']['saisie_valeur']) ? $tab_tmp_saisie['position']['saisie_valeur'] : FALSE;
          $indice = OutilBilan::determiner_degre_maitrise($pourcentage); // 3 | 4 | FALSE
          $tab_eleve[$eleve_id]['enscompl'] = array(
            'code'           => $enscompl_code,
            'positionnement' => $indice-2, // 1 | 2 | -2
          );
        }
        // Bilan
        if( ($rubrique_type=='bilan') && !empty($tab_tmp_saisie['appreciation']['saisie_valeur']) )
        {
          // si report depuis bulletins configurés avec présence de moyenne générale, on peut n'avoir dans le bilan que cette info et pas d'appréciation, d'où le 2nd test ci-dessus
          $tab_eleve[$eleve_id]['synthese']['appreciation'] = $tab_tmp_saisie['appreciation']['saisie_valeur']; // max 1000 caractères : idem dans SACoche
        }
        // Communication avec la famille
        if( ($rubrique_type=='viesco') && !empty($tab_tmp_saisie['appreciation']['saisie_valeur']) )
        {
          $tab_eleve[$eleve_id]['viesco']['appreciation'] = $tab_tmp_saisie['appreciation']['saisie_valeur']; // max 600 caractères : idem dans SACoche
        }
      }
    }
  }
}

unset($tab_saisie);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifier qu'il y a au moins :
// pour les bilans périodiques -> une info d'acquisition par élève + un positionnement défini si pas de note ni de pourcentage + au moins un prof rattaché à chaque matière + une appréciation de synthèse
// pour les bilans de fin de cycle -> tous les positionnements définis + une appréciation de synthèse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$is_lsu_17_2 = version_compare(TODAY_MYSQL,'2017-05-14','>') ? TRUE : FALSE ;

/*
 * En cas de retrait d'une rubrique, on retire aussi les éléments de programmes associés, sauf s'ils sont utilisés par une autre rubrique.
 * Ceci n'a ceppendant rien d'obligatoire : un bilan avec des éléments de programmes inutilisés est importé sans aucun alerte.
 */
function retrait_commun_elements( $tab_eleve_acquis_elements , $eleve_id , $discipline_ref )
{
  global $tab_eleve, $tab_element_to_rubrique;
  foreach($tab_eleve_acquis_elements as $key_element)
  {
    unset($tab_element_to_rubrique[$key_element][$discipline_ref]);
    // vérifier que cet élément n'est pas utilisé par une autre discipline
    if(empty($tab_element_to_rubrique[$key_element]))
    {
      unset($tab_eleve[$eleve_id]['commun']['element'][$key_element]);
    }
  }
}

$tab_siecle_count = array( 'ok'=>array() , 'ko'=>array() );
foreach($tab_eleve as $eleve_id => $tab)
{
  if($PAGE_PERIODICITE=='periode')
  {
    if(empty($tab['acquis']))
    {
      $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucune saisie d\'acquis (ni positionnement ni appréciation ni élément travaillé) : données non exportables.';
      unset($tab_eleve[$eleve_id]);
    }
    else
    {
      if($BILAN_TYPE_ETABL=='college')
      {
        $tab_siecle_count['ok'][$eleve_id] = 0;
        $tab_siecle_count['ko'][$eleve_id] = 0;
        foreach($tab['acquis'] as $discipline_ref => $tab_rubrique_info)
        {
          $key_rubrique = substr($discipline_ref,0,-1);
          $key_count = ($tab_rubrique[$key_rubrique]['modalite-election']!='X') ? 'ok' : 'ko' ;
          $tab_siecle_count[$key_count][$eleve_id]++;
          // au moins un enseignant doit être associé à la discipline
          if(empty($tab_rubrique_info['profs']))
          {
            $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucun enseignant rattaché à la discipline "'.$tab_rubrique[$key_rubrique]['libelle'].'" : rubrique non exportée.';
            unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
            retrait_commun_elements( $tab_rubrique_info['elements'] , $eleve_id , $discipline_ref );
          }
          // jusqu'en avril 2016, en cas de positionnement sur 4 niveaux, un élève ne peut pas être non noté
          else if( ($champ_position=='positionnement') && is_null($tab_rubrique_info[$champ_position]) )
          {
            if($is_lsu_17_2)
            {
              $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucun positionnement pour la discipline "'.$tab_rubrique[$key_rubrique]['libelle'].'" : import possible vers LSU &ge; 17.2 uniquement.';
            }
            else
            {
              $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucun positionnement pour la discipline "'.$tab_rubrique[$key_rubrique]['libelle'].'" : rubrique non exportable vers LSU &lt; 17.2.';
              unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
              retrait_commun_elements( $tab_rubrique_info['elements'] , $eleve_id , $discipline_ref );
            }
          }
          elseif( mb_strlen($tab_rubrique_info['appreciation']) > 600 )
          {
            $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Appréciation trop longue pour "'.$tab_rubrique[$key_rubrique]['libelle'].'" (regroupements multiples du bulletin ?) : tronquée à 600 caractères.';
            $tab_eleve[$eleve_id]['acquis'][$discipline_ref]['appreciation'] = mb_substr($tab_rubrique_info['appreciation'],0,600);
          }
          // appréciation obligatoire sauf si élève non noté => "-" ajouté si besoin au moment de la conception du XML
          // élément de prg travaillé obligatoire => "-" ajouté si besoin au moment de la conception du XML
        }
      }
      else if($BILAN_TYPE_ETABL=='ecole')
      {
        $tab_domaines_renseignes = array();
        foreach($tab['acquis'] as $discipline_ref => $tab_rubrique_info)
        {
          $domaine_principal = substr($discipline_ref,0,3);
          // il ne peut pas y avoir des éléments de programme sans positionnement (mais, en cas d'appréciation, il peut y avoir ni l'un ni l'autre)
          if( !empty($tab_rubrique_info['elements']) && is_null($tab_rubrique_info[$champ_position]) )
          {
            if($tab_rubrique_info['appreciation'])
            {
              $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Éléments travaillés sans positionnement pour "'.$discipline_ref.'" : seule l\'appréciation sera transmise.';
              $tab_eleve[$eleve_id]['acquis'][$discipline_ref]['elements'] = array();
              retrait_commun_elements( $tab_rubrique_info['elements'] , $eleve_id , $discipline_ref );
              $tab_domaines_renseignes[$domaine_principal] = TRUE;
            }
            else
            {
              if($is_lsu_17_2)
              {
                $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Éléments travaillés sans positionnement (ni appréciation) pour "'.$discipline_ref.'" : import possible vers LSU &ge; 17.2 uniquement.';
                $tab_domaines_renseignes[$domaine_principal] = TRUE;
              }
              else
              {
                $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Éléments travaillés sans positionnement (ni appréciation) pour "'.$discipline_ref.'" : rubrique non exportable vers LSU &lt; 17.2.';
                unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
                retrait_commun_elements( $tab_rubrique_info['elements'] , $eleve_id , $discipline_ref );
              }
            }
          }
          // il ne peut pas y avoir de positionnement sans élément(s) de programme (mais, en cas d'appréciation, il peut y avoir ni l'un ni l'autre)
          else if( empty($tab_rubrique_info['elements']) && !is_null($tab_rubrique_info[$champ_position]) )
          {
            if($tab_rubrique_info['appreciation'])
            {
              $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Positionnement sans éléments travaillés pour "'.$discipline_ref.'" : : seule l\'appréciation sera transmise';
              $tab_eleve[$eleve_id]['acquis'][$discipline_ref][$champ_position] = NULL;
              $tab_domaines_renseignes[$domaine_principal] = TRUE;
            }
            else
            {
              $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Positionnement sans éléments travaillés (ni appréciation) pour "'.$discipline_ref.'" : rubrique non exportée.';
              unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
              retrait_commun_elements( $tab_rubrique_info['elements'] , $eleve_id , $discipline_ref );
            }
          }
          else
          {
            $tab_domaines_renseignes[$domaine_principal] = TRUE;
          }
        }
      }
      // 2D : un maximum de rubriques doivent coller aux matières issues de SIECLE ; cela peut ne concerner qu'un élève (absent donc sans autre remontée), auquel cas on ne bloque pas tout
      if( ($BILAN_TYPE_ETABL=='college') && ( $tab_siecle_count['ok'][$eleve_id] < $tab_siecle_count['ko'][$eleve_id] ) )
      {
        $s = ($tab_siecle_count['ko'][$eleve_id]>1) ? 's' : '' ;
        $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; '.$tab_siecle_count['ko'][$eleve_id].' discipline'.$s.' sur '.($tab_siecle_count['ok'][$eleve_id]+$tab_siecle_count['ko'][$eleve_id]).' hors SIECLE alors que cela devrait être une exception : données non exportables.';
        unset($tab_eleve[$eleve_id]);
      }
      else if(empty($tab_eleve[$eleve_id]['acquis']))
      {
        $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Plus de rubriques d\'acquis : données non exportables.';
        unset($tab_eleve[$eleve_id]);
      }
      // 1D : Au moins 3 acquis doivent être renseignés, c’est à dire pour au moins 3 domaines (ou 3 sous-domaines de 3 domaines différents)
      else if( ($BILAN_TYPE_ETABL=='ecole') && count($tab_domaines_renseignes)<3 )
      {
        $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Moins de 3 domaines suffisamment renseignés : données non exportables.';
        unset($tab_eleve[$eleve_id]);
      }
      else if(empty($tab_eleve[$eleve_id]['synthese']))
      {
        $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Absence d\'appréciation bilan : champ normalement requis passé à "-".';
        $tab_eleve[$eleve_id]['synthese']['appreciation'] = '-';
      }
    }
  }
  if($PAGE_PERIODICITE=='cycle')
  {
    // Un bilan de fin de cycle est considéré comme complet si les données suivantes sont renseignées :
    // - le positionnement (niveau de maîtrise) pour les 8 composantes / domaines du socle commun
    // - l'appréciation de synthèse des acquis scolaires
    $nb_positionnements_eleve = count($tab_eleve[$eleve_id]['socle']);
    if( $nb_positionnements_eleve < $nb_positionnements_socle )
    {
      $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Positionnements du socle incomplets ('.$nb_positionnements_eleve.'/'.$nb_positionnements_socle.') : données non exportables.';
      unset($tab_eleve[$eleve_id]);
    }
    else
    {
      if($cycle_id==4)
      {
        if( empty($tab_eleve[$eleve_id]['enscompl']) )
        {
          $tab_eleve[$eleve_id]['enscompl'] = array(
            'code'           => 'AUC',
            'positionnement' => NULL,
          );
        }
        else if( empty($tab_eleve[$eleve_id]['enscompl']['positionnement']) || ($tab_eleve[$eleve_id]['enscompl']['positionnement']<0) )
        {
          $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Absence de positionnement pour l\'enseignement de complément : donnée non exportée.';
          $tab_eleve[$eleve_id]['enscompl'] = array(
            'code'           => 'AUC',
            'positionnement' => NULL,
          );
        }
      }
      if(empty($tab_eleve[$eleve_id]['synthese']))
      {
        $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Absence d\'appréciation bilan : champ normalement requis passé à "-".';
        $tab_eleve[$eleve_id]['synthese']['appreciation'] = '-';
      }
    }
  }
}

// un maximum de rubriques doivent coller aux matières issues de SIECLE ; si cela concerne la majorité des élèves c'est qu'il y a un problème de configuration : on bloque tout
if($BILAN_TYPE_ETABL=='college')
{
  $nb_siecle_count_ok = array_sum($tab_siecle_count['ok']);
  $nb_siecle_count_ko = array_sum($tab_siecle_count['ko']);
  if( $nb_siecle_count_ok < $nb_siecle_count_ko )
  {
    $lien_assistance = ($nb_siecle_count_ok) ? 'Étape n°2 configurée correctement ?' : HtmlMail::to(SACOCHE_CONTACT_COURRIEL,'export LSU - référentiels non liés à SIECLE','Prenez contact avec l\'assistance de SACoche','Veuillez joindre votre fichier "sts_emp_'.$_SESSION['WEBMESTRE_UAI'].'_'.$millesime.'.xml".').' si besoin.' ;
    Json::end( FALSE , 'Majorité des disciplines hors SIECLE alors que cela devrait être une exception !<br />'.$lien_assistance );
  }
}

$liste_eleve_id = implode(',',array_keys($tab_eleve)); // si les données n'étaient pas exportables, alors la liste a été restreinte

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les absences / retards
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $affichage_assiduite && !empty($tab_eleve) )
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , $liste_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
     // valeurs 999 maximum : 255 dans SACoche
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-retards']          = (int)$DB_ROW['assiduite_retard'];
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-justifiees']   = (int)$DB_ROW['assiduite_absence'] - (int)$DB_ROW['assiduite_absence_nj'];
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-injustifiees'] = (int)$DB_ROW['assiduite_absence_nj'];
    if($tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-justifiees']<0)
    {
      $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-justifiees'] = 0;
      $tab_compte_rendu['alerte'][] = html($tab_eleve[$DB_ROW['user_id']]['eleve']['nom'].' '.$tab_eleve[$DB_ROW['user_id']]['eleve']['prenom'])." &rarr; Nb d'absences non justifiées supérieur au nb total d'absences : mise en cohérence pour l'export.";
    }
  }
  $nb_eleves = count($tab_eleve);
  $nb_viesco = count($DB_TAB);
  $nb_manque = $nb_eleves - $nb_viesco;
  if( $nb_manque )
  {
    $s = ($nb_manque>1) ? 's' : '' ;
    $tab_compte_rendu['alerte'][] = "Informations de vie scolaire (assiduité / ponctualité)  absentes pour ".$nb_manque." élève".$s." : exportées à zéro.";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les noms et coordonnées des responsables légaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !empty($tab_eleve) )
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_adresses_parents_for_enfants($liste_eleve_id);
  if(!empty($DB_TAB))
  {
    $pays_majoritaire = DB_STRUCTURE_OFFICIEL::DB_recuperer_pays_majoritaire();
    foreach($DB_TAB as $eleve_id => $DB_ROW)
    {
      foreach($DB_ROW as $key => $DB_RESP)
      {
        if( ( ($DB_RESP['resp_legal_num']==1) || ($DB_RESP['resp_legal_num']==2) ) && $tab_lsu_civilite[$DB_RESP['user_genre']] && $DB_RESP['user_nom'] && $DB_RESP['user_prenom'] && $DB_RESP['adresse_ligne1'] && $DB_RESP['adresse_postal_code'] && $DB_RESP['adresse_postal_libelle'] )
        {
          $pays_nom = ($DB_RESP['adresse_pays_nom']==$pays_majoritaire) ? '' : ' - '.$DB_RESP['adresse_pays_nom'] ;
          $tab_eleve[$eleve_id]['responsables'][$DB_RESP['resp_legal_num']] = array(
            'civilite'    => $tab_lsu_civilite[$DB_RESP['user_genre']],
            'nom'         => $DB_RESP['user_nom'], // max 100 caractères : 25 dans SACoche
            'prenom'      => $DB_RESP['user_prenom'], // max 100 caractères : 25 dans SACoche
            'ligne1'      => $DB_RESP['adresse_ligne1'], // max 50 caractères : idem dans SACoche
            'ligne2'      => $DB_RESP['adresse_ligne2'], // max 50 caractères : idem dans SACoche
            'ligne3'      => $DB_RESP['adresse_ligne3'], // max 50 caractères : idem dans SACoche
            'ligne4'      => $DB_RESP['adresse_ligne4'], // max 50 caractères : idem dans SACoche
            'code-postal' => $DB_RESP['adresse_postal_code'], // max 10 caractères : idem dans SACoche
            'commune'     => $DB_RESP['adresse_postal_libelle'].$pays_nom, // max 100 caractères : 45+3+35 dans SACoche
          );
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer en base de données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !empty($tab_eleve) )
{
  foreach($tab_eleve as $eleve_id => $tab)
  {
    DB_STRUCTURE_LIVRET::DB_ajouter_livret_export_eleve( $eleve_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , VERSION_PROG , json_encode($tab) );
  }
  DB_STRUCTURE_LIVRET::DB_modifier_jointure_date_export( $classe_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retour
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On retire les message alertant de prof sans infos suffisantes ou de matière hors Siècle s'ils ne sont finalement pas utilisés
foreach($tab_compte_rendu as $type_alerte => $tab_message)
{
  foreach($tab_message as $key => $message)
  {
    if( !is_int($key) && !isset($tab_objet_used[$key]))
    {
      unset($tab_compte_rendu[$type_alerte][$key]);
    }
  }
}
$retour = '';
foreach($tab_compte_rendu as $type_alerte => $tab_message)
{
  if(!empty($tab_message))
  {
    $retour .= '<label class="'.$type_alerte.'">'.implode('</label><br /><label class="'.$type_alerte.'">',$tab_message).'</label><br />';
  }
}

if( !empty($tab_eleve) )
{
  Json::end( TRUE , $retour );
}
else
{
  Json::end( FALSE , 'Données récoltables pour aucun élève !<br />'.$retour );
}

?>
