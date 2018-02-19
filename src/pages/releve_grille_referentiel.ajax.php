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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$remplissage             = (isset($_POST['f_remplissage']))     ? Clean::texte($_POST['f_remplissage'])            : '';
$colonne_bilan           = (isset($_POST['f_colonne_bilan']))   ? Clean::texte($_POST['f_colonne_bilan'])          : '';
$colonne_vide            = (isset($_POST['f_colonne_vide']))    ? Clean::entier($_POST['f_colonne_vide'])          : 0;
$tableau_synthese_format = (isset($_POST['f_synthese_format'])) ? Clean::texte($_POST['f_synthese_format'])        : '';
$tableau_tri_etat_mode   = (isset($_POST['f_tri_etat_mode']))   ? Clean::texte($_POST['f_tri_etat_mode'])          : '';
$repeter_entete          = (isset($_POST['f_repeter_entete']))  ? 1                                                : 0;
$matiere_id              = (isset($_POST['f_matiere']))         ? Clean::entier($_POST['f_matiere'])               : 0;
$matiere_nom             = (isset($_POST['f_matiere_nom']))     ? Clean::texte($_POST['f_matiere_nom'])            : '';
$groupe_id               = (isset($_POST['f_groupe']))          ? Clean::entier($_POST['f_groupe'])                : 0;
$groupe_nom              = (isset($_POST['f_groupe_nom']))      ? Clean::texte($_POST['f_groupe_nom'])             : '';
$groupe_type             = (isset($_POST['f_groupe_type']))     ? Clean::lettres($_POST['f_groupe_type'])          : '';
$niveau_id               = (isset($_POST['f_niveau']))          ? Clean::entier($_POST['f_niveau'])                : 0;
$niveau_nom              = (isset($_POST['f_niveau_nom']))      ? Clean::texte($_POST['f_niveau_nom'])             : '';
$periode_id              = (isset($_POST['f_periode']))         ? Clean::entier($_POST['f_periode'])               : 0;
$date_debut              = (isset($_POST['f_date_debut']))      ? Clean::date_fr($_POST['f_date_debut'])           : '';
$date_fin                = (isset($_POST['f_date_fin']))        ? Clean::date_fr($_POST['f_date_fin'])             : '';
$retroactif              = (isset($_POST['f_retroactif']))      ? Clean::calcul_retroactif($_POST['f_retroactif']) : '';
$only_etat               = (isset($_POST['f_only_etat']))       ? Clean::texte($_POST['f_only_etat'])              : '';
$only_arbo               = (isset($_POST['f_only_arbo']))       ? Clean::texte($_POST['f_only_arbo'])              : '';
$only_socle              = (isset($_POST['f_only_socle']))      ? 1                                                : 0;
$aff_reference           = (isset($_POST['f_reference']))       ? 1                                                : 0;
$aff_coef                = (isset($_POST['f_coef']))            ? 1                                                : 0;
$aff_socle               = (isset($_POST['f_socle']))           ? 1                                                : 0;
$aff_comm                = (isset($_POST['f_comm']))            ? 1                                                : 0;
$aff_lien                = (isset($_POST['f_lien']))            ? 1                                                : 0;
$orientation             = (isset($_POST['f_orientation']))     ? Clean::texte($_POST['f_orientation'])            : '';
$couleur                 = (isset($_POST['f_couleur']))         ? Clean::texte($_POST['f_couleur'])                : '';
$fond                    = (isset($_POST['f_fond']))            ? Clean::texte($_POST['f_fond'])                   : '';
$legende                 = (isset($_POST['f_legende']))         ? Clean::texte($_POST['f_legende'])                : '';
$marge_min               = (isset($_POST['f_marge_min']))       ? Clean::texte($_POST['f_marge_min'])              : '';
$pages_nb                = (isset($_POST['f_pages_nb']))        ? Clean::texte($_POST['f_pages_nb'])               : '';
$cases_auto              = (isset($_POST['f_cases_auto']))      ? 1                                                : 0;
$cases_nb                = (isset($_POST['f_cases_nb']))        ? Clean::entier($_POST['f_cases_nb'])              : -1;
$cases_largeur           = (isset($_POST['f_cases_larg']))      ? Clean::entier($_POST['f_cases_larg'])            : 0;
$eleves_ordre            = (isset($_POST['f_eleves_ordre']))    ? Clean::texte($_POST['f_eleves_ordre'])           : '';

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve_id = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_type     = (isset($_POST['f_type']))  ? ( (is_array($_POST['f_type']))  ? $_POST['f_type']  : explode(',',$_POST['f_type'])  ) : array() ;
$tab_eleve_id = array_filter( Clean::map('entier',$tab_eleve_id) , 'positif' );
$tab_type     = Clean::map('texte',$tab_type);

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $tab_type = array('individuel');
  // Pour un élève on surcharge avec les données de session
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $groupe_id    = $_SESSION['ELEVE_CLASSE_ID'];
    $tab_eleve_id = array($_SESSION['USER_ID']);
  }
  // Pour un parent on vérifie que c'est bien un de ses enfants
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $is_enfant_legitime = FALSE;
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      if($DB_ROW['valeur']==$tab_eleve_id[0])
      {
        $is_enfant_legitime = TRUE;
        break;
      }
    }
    if(!$is_enfant_legitime)
    {
      Json::end( FALSE , 'Enfant non rattaché à votre compte parent !' );
    }
  }
}
if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') )
{
  // Pour un professeur on vérifie que ce sont bien ses élèves
  $tab_eleves_non_rattaches = array_diff( $tab_eleve_id , $_SESSION['PROF_TAB_ELEVES'] );
  if(!empty($tab_eleves_non_rattaches))
  {
    // On vérifie de nouveau, au cas où l'admin viendrait d'ajouter une affectation
    $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    $tab_eleves_non_rattaches = array_diff( $tab_eleve_id , $_SESSION['PROF_TAB_ELEVES'] );
    if(!empty($tab_eleves_non_rattaches))
    {
      Json::end( FALSE , 'Élève(s) non rattaché(s) à votre compte enseignant !' );
    }
  }
}

$type_generique  = (in_array('generique',$tab_type))  ? 1 : 0 ;
$type_individuel = (in_array('individuel',$tab_type)) ? 1 : 0 ;
$type_synthese   = (in_array('synthese',$tab_type))   ? 1 : 0 ;

if($type_generique)
{
  $groupe_id    = 0;
  $tab_eleve_id = array();
}

$liste_eleve = implode(',',$tab_eleve_id);

// Ces 3 choix sont passés de modifiables à imposés pour élèves & parents (25 février 2012) ; il faut les rétablir à leur bonnes valeurs si besoin (1ère soumission du formulaire depuis ce changement).
// Remarque : pour le profil élève on a besoin des notes pour le panier afin de solliciter une évaluation.
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  $remplissage   = 'plein';
  $colonne_bilan = 'oui';
  $colonne_vide  = 0;
}

// Si pas grille générique et si notes demandées ou besoin pour colonne bilan ou besoin pour synthèse
$besoin_notes = ( !$type_generique && ( ($remplissage=='plein') || ($colonne_bilan=='oui') || $type_synthese ) ) ? TRUE : FALSE ;

if( !$matiere_id || ( !$type_generique && ( !$groupe_id || !$groupe_nom || !$groupe_type || !count($tab_eleve_id) ) ) || !$niveau_id || !$matiere_nom || !$niveau_nom || !$remplissage || !$colonne_bilan || ( $besoin_notes && !$periode_id && (!$date_debut || !$date_fin) ) || ( $besoin_notes && ( !$retroactif || !$only_etat || !$only_arbo ) ) || !$orientation || !$couleur || !$fond || !$legende || !$marge_min || !$pages_nb || ($cases_nb<0) || !$cases_largeur || !count($tab_type) || !$eleves_ordre )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Enregistrer les préférences utilisateurs
Form::save_choix('grille_referentiel');

if($type_generique)
{
  $remplissage   = 'vide';
  $colonne_bilan = 'non';
  $colonne_vide  = 0;
  $type_individuel = 0 ;
  $type_synthese   = 0 ;
}

if($type_generique && $cases_auto)
{
  $cases_nb = 4;
  $cases_largeur = 7;
}

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Initialisation de tableaux

$tab_domaine           = array();  // [domaine_id] => array(domaine_ref,domaine_nom,domaine_nb_lignes,used);
$tab_theme             = array();  // [domaine_id][theme_id] => array(theme_ref,theme_nom,theme_nb_lignes,used);
$tab_item              = array();  // [theme_id][item_id] => array(item_ref,item_nom,item_coef,item_cart,item_s2016,item_lien,used);
$tab_item_synthese     = array();  // [item_id] => array(item_ref,item_nom);
$tab_liste_item        = array();  // [i] => item_id
$tab_eleve_infos       = array();  // [eleve_id] => array(eleve_nom,eleve_prenom)
$tab_eval              = array();  // [eleve_id][item_id] => array(note,date,info)
$tab_theme_for_item    = array();  // [item_id] => theme_id
$tab_domaine_for_theme = array();  // [theme_id] => domaine_id

$tab_titre_etat = array(
  'tous'       => 'évalués' ,
  'acquis'     => 'réussis' ,
  'non_acquis' => 'échoués' ,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Période concernée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($besoin_notes)
{
  if($periode_id==0)
  {
    $date_mysql_debut = To::date_french_to_mysql($date_debut);
    $date_mysql_fin   = To::date_french_to_mysql($date_fin);
  }
  else
  {
    $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
    if(empty($DB_ROW))
    {
      Json::end( FALSE , 'Le regroupement et la période ne sont pas reliés !' );
    }
    $date_mysql_debut = $DB_ROW['jointure_date_debut'];
    $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
    $date_debut = To::date_mysql_to_french($date_mysql_debut);
    $date_fin   = To::date_mysql_to_french($date_mysql_fin);
  }
  if($date_mysql_debut>$date_mysql_fin)
  {
    Json::end( FALSE , 'La date de début est postérieure à la date de fin !' );
  }

  $tab_precision_retroactif = array
  (
    'auto'   => 'notes antérieures selon référentiels',
    'oui'    => 'avec notes antérieures',
    'non'    => 'sans notes antérieures',
    'annuel' => 'notes antérieures de l\'année scolaire',
  );
  $precision_socle = $only_socle ? ', restriction au socle' : '' ;
  $texte_periode = 'Du '.$date_debut.' au '.$date_fin.'';
  $texte_precision = '('.$tab_precision_retroactif[$retroactif].$precision_socle.')';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items pour la matière et le niveau sélectionné
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$lignes_nb = 0;
$longueur_ref_max = 0;
$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , $only_socle , FALSE /*only_item*/ , TRUE /*s2016_count*/ , TRUE /*item_comm*/ );
if(!empty($DB_TAB))
{
  $domaine_id = 0;
  $theme_id   = 0;
  $item_id    = 0;
  foreach($DB_TAB as $DB_ROW)
  {
    if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
    {
      $domaine_id  = $DB_ROW['domaine_id'];
      $domaine_ref = ($DB_ROW['domaine_ref']) ? $DB_ROW['domaine_ref'] : $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_code'] ;
      $tab_domaine[$domaine_id] = array(
        'domaine_ref'       => $domaine_ref,
        'domaine_nom'       => $DB_ROW['domaine_nom'],
        'domaine_nb_lignes' => 2,
        'domaine_used'      => FALSE,
      );
      $longueur_ref_max = max( $longueur_ref_max , strlen($domaine_ref) );
      $lignes_nb++;
    }
    if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
    {
      $theme_id  = $DB_ROW['theme_id'];
      $theme_ref = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'] : $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_code'].$DB_ROW['theme_ordre'] ;
      $first_theme_of_domaine = (isset($tab_theme[$domaine_id])) ? FALSE : TRUE ;
      $tab_theme[$domaine_id][$theme_id] = array(
        'theme_ref'       => $theme_ref,
        'theme_nom'       => $DB_ROW['theme_nom'],
        'theme_nb_lignes' => 1,
        'theme_used'      => FALSE,
      );
      $tab_domaine_for_theme[$theme_id] = $domaine_id;
      $longueur_ref_max = max( $longueur_ref_max , strlen($theme_ref) );
      $lignes_nb++;
    }
    if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
    {
      $item_id = $DB_ROW['item_id'];
      $item_ref = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref'] || $DB_ROW['item_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'].$DB_ROW['item_ref'] : $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_code'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'] ;
      $tab_item[$theme_id][$item_id] = array(
        'item_ref'   => $item_ref,
        'item_nom'   => $DB_ROW['item_nom'],
        'item_coef'  => $DB_ROW['item_coef'],
        'item_cart'  => $DB_ROW['item_cart'],
        'item_s2016' => $DB_ROW['s2016_nb'],
        'item_comm'  => $DB_ROW['item_comm'],
        'item_lien'  => $DB_ROW['item_lien'],
        'item_used'  => FALSE,
      );
      $tab_theme_for_item[$item_id] = $theme_id;
      $tab_item_synthese[$item_id] = array(
        'item_ref'  => $DB_ROW['matiere_ref'].'.'.$item_ref,
        'item_nom'  => $DB_ROW['item_nom'],
        'item_coef' => $DB_ROW['item_coef']
      );
      $tab_theme[$domaine_id][$theme_id]['theme_nb_lignes']++;
      if($first_theme_of_domaine)
      {
        $tab_domaine[$domaine_id]['domaine_nb_lignes']++;
      }
      $tab_liste_item[] = $item_id;
      $longueur_ref_max = max( $longueur_ref_max , strlen($item_ref) );
      $lignes_nb++;
    }
  }
}
$item_nb = count($tab_liste_item);
if(!$item_nb)
{
  Json::end( FALSE , 'Aucun item référencé pour cette matière et ce niveau !' );
}
$liste_item = implode(',',$tab_liste_item);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves (si demandé)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array(
    'eleve_nom'    => $_SESSION['USER_NOM'],
    'eleve_prenom' => $_SESSION['USER_PRENOM'],
  );
}
elseif(count($tab_eleve_id))
{
  $eleves_ordre = ($groupe_type=='Classes') ? 'alpha' : $eleves_ordre ;
  $tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve , $eleves_ordre );
  if(!is_array($tab_eleve_infos))
  {
    Json::end( FALSE , 'Aucun élève trouvé correspondant aux identifiants transmis !' );
  }
}
else
{
  $tab_eleve_infos[0] = array(
    'eleve_nom'    => '',
    'eleve_prenom' => '',
  );
}
$eleve_nb = count( $tab_eleve_infos , COUNT_NORMAL );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($besoin_notes)
{
  // Récupération de calcul_methode ; calcul_limite ; calcul_retroactif
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_referentiels_infos_details_matieres_niveaux( $matiere_id , $niveau_id );
  $calcul_methode    = $DB_TAB[0]['referentiel_calcul_methode'];
  $calcul_limite     = $DB_TAB[0]['referentiel_calcul_limite'];
  $calcul_retroactif = $DB_TAB[0]['referentiel_calcul_retroactif'];
  // Détermination de la date de départ
  $retroactif = ($retroactif=='auto') ? $calcul_retroactif : $retroactif ; // Ne peut plus valoir que "oui" | "non" | "annuel" à présent
  $date_mysql_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql');
      if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
  elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
  else                          { $date_mysql_start = FALSE; } // forcément 'oui' puisque le cas 'auto' a déjà été écarté (possible car un unique référentiel est considéré ici)
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items( $liste_eleve , $liste_item , $matiere_id , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*onlyprof*/ , FALSE /*onlynote*/ ) ;
  if( empty($DB_TAB) && ($only_arbo!='tous') )
  {
    Json::end( FALSE , 'Aucune note trouvée alors que votre paramétrage requiert la présence de résultats !' );
  }
  foreach($DB_TAB as $DB_ROW)
  {
    $user_id = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? $_SESSION['USER_ID'] : $DB_ROW['eleve_id'] ;
    $item_id = $DB_ROW['item_id'];
    $tab_eval[$user_id][$item_id][] = array(
      'note' => $DB_ROW['note'],
      'date' => $DB_ROW['date'],
      'info' => $DB_ROW['info'],
    );
    $theme_id = $tab_theme_for_item[$item_id];
    $domaine_id = $tab_domaine_for_theme[$theme_id];
    $tab_item[$theme_id][$item_id]['item_used'] = TRUE;
    $tab_theme[$domaine_id][$theme_id]['theme_used'] = TRUE;
    $tab_domaine[$domaine_id]['domaine_used'] = TRUE;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Restriction de la grille si demandé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $besoin_notes && ($only_arbo!='tous') )
{
  // Pour chaque domaine...
  if(count($tab_domaine))
  {
    foreach($tab_domaine as $domaine_id => $tab)
    {
      $delete_theme = FALSE;
      extract($tab);  // $domaine_ref $domaine_nom $domaine_nb_lignes $domaine_used
      if( !$domaine_used && ( ($only_arbo=='domaine') || ($only_arbo=='theme') || ($only_arbo=='item') ) )
      {
        $delete_theme = TRUE;
        unset($tab_domaine[$domaine_id]);
        $lignes_nb--;
      }
      // Pour chaque thème...
      if(isset($tab_theme[$domaine_id]))
      {
        foreach($tab_theme[$domaine_id] as $theme_id => $tab)
        {
          $delete_item = FALSE;
          extract($tab);  // $theme_ref $theme_nom $theme_nb_lignes $theme_used
          if( $delete_theme || ( !$theme_used && ( ($only_arbo=='theme') || ($only_arbo=='item') ) ) )
          {
            $delete_item = TRUE;
            unset($tab_theme[$domaine_id][$theme_id]);
            $lignes_nb--;
          }
          // Pour chaque item...
          if(isset($tab_item[$theme_id]))
          {
            foreach($tab_item[$theme_id] as $item_id => $tab)
            {
              extract($tab);  // $item_ref $item_nom $item_coef $item_cart $item_s2016 $item_lien $item_used
              if( $delete_item || ( !$item_used && ($only_arbo=='item') ) )
              {
                unset($tab_item[$theme_id][$item_id]);
                $lignes_nb--;
              }
            }
          }
        }
      }
    }
  }
}

// Nombre de demandes d'évaluation autorisées pour la matière concernée
$nb_demandes_autorisees = DB_STRUCTURE_DEMANDE::DB_recuperer_demandes_autorisees_matiere($matiere_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////

DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier = 'grille_item_'.Clean::fichier($matiere_nom).'_'.Clean::fichier($niveau_nom).'_<REPLACE>_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
$fichier_nom_type1 = ($type_generique) ? str_replace( '<REPLACE>' , 'generique' , $fichier ) : str_replace( '<REPLACE>' , Clean::fichier($groupe_nom).'_individuel' , $fichier ) ;
$fichier_nom_type2 = str_replace( '<REPLACE>' , Clean::fichier($groupe_nom).'_synthese' , $fichier ) ;

$tab_score_eleve_item         = array();  // Retenir les scores / élève / item
$tab_score_item_eleve         = array();  // Retenir les scores / item / élève
$tab_moyenne_scores_eleve     = array();  // Retenir la moyenne des scores d'acquisitions / élève
$tab_pourcentage_acquis_eleve = array();  // Retenir le pourcentage d'items acquis / élève
$tab_moyenne_scores_item      = array();  // Retenir la moyenne des scores d'acquisitions / item
$tab_pourcentage_acquis_item  = array();  // Retenir le pourcentage d'items acquis / item
$moyenne_moyenne_scores       = 0;  // moyenne des moyennes des scores d'acquisitions
$moyenne_pourcentage_acquis   = 0;  // moyenne des moyennes des pourcentages d'items acquis

/*
  Calcul des états d'acquisition (si besoin) et des données nécessaires pour le tableau de synthèse (si besoin).
  $tab_score_eleve_item[$eleve_id][$item_id]
  $tab_score_item_eleve[$item_id][$eleve_id]
  $tab_moyenne_scores_eleve[$eleve_id]
  $tab_pourcentage_acquis_eleve[$eleve_id]
*/

if(count($tab_eval))
{
  foreach($tab_eval as $eleve_id => $tab_items) // Ne pas écraser $tab_item déjà utilisé
  {
    foreach($tab_items as $item_id => $tab_devoirs)
    {
      // calcul du bilan de l'item
      $score = OutilBilan::calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , $date_mysql_debut );
      if( ($only_etat=='tous') || OutilBilan::tester_acquisition( $score , $only_etat ) )
      {
        $tab_score_eleve_item[$eleve_id][$item_id] = $score;
        if($type_synthese)
        {
          $tab_score_item_eleve[$item_id][$eleve_id] = $score;
        }
      }
      else
      {
        unset( $tab_eval[$eleve_id][$item_id] );
      }
    }
    if( ($only_etat=='tous') || !empty($tab_score_eleve_item[$eleve_id]) )
    {
      if($type_synthese)
      {
        // calcul des bilans des scores
        $tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id],'non_vide');
        $nb_scores = count( $tableau_score_filtre );
        // la moyenne peut être pondérée par des coefficients
        $somme_scores_ponderes = 0;
        $somme_coefs = 0;
        if($nb_scores)
        {
          foreach($tableau_score_filtre as $item_id => $item_score)
          {
            $somme_scores_ponderes += $item_score*$tab_item_synthese[$item_id]['item_coef'];
            $somme_coefs += $tab_item_synthese[$item_id]['item_coef'];
          }
        }
        // ... un pour la moyenne des pourcentages d'acquisition
        if($somme_coefs)
        {
          $tab_moyenne_scores_eleve[$eleve_id] = round($somme_scores_ponderes/$somme_coefs,0);
        }
        else
        {
          $tab_moyenne_scores_eleve[$eleve_id] = FALSE;
        }
        // ... un pour le nombre d\'items considérés acquis ou pas
        if($nb_scores)
        {
          $tab_acquisitions = OutilBilan::compter_nombre_acquisitions_par_etat( $tableau_score_filtre );
          $tab_pourcentage_acquis_eleve[$eleve_id] = OutilBilan::calculer_pourcentage_acquisition_items( $tab_acquisitions , $nb_scores );
        }
        else
        {
          $tab_pourcentage_acquis_eleve[$eleve_id] = FALSE;
        }
      }
    }
    else
    {
      // Cas où, à cause de la restriction à des items échoués ou réussis, on n'a plus d'évaluation pour un élève
      unset( $tab_eval[$eleve_id] );
    }
  }
}

/*
  On renseigne (uniquement utile pour le tableau de synthèse) :
  $tab_moyenne_scores_item[$item_id]
  $tab_pourcentage_acquis_item[$item_id]
*/

if($type_synthese)
{
  // Pour chaque item...
  foreach($tab_liste_item as $item_id)
  {
    $tableau_score_filtre = isset($tab_score_item_eleve[$item_id]) ? array_filter($tab_score_item_eleve[$item_id],'non_vide') : array() ; // Test pour éviter de rares "array_filter() expects parameter 1 to be array, null given"
    $nb_scores = count( $tableau_score_filtre );
    if($nb_scores)
    {
      $somme_scores = array_sum($tableau_score_filtre);
      $tab_acquisitions = OutilBilan::compter_nombre_acquisitions_par_etat( $tableau_score_filtre );
      $tab_moyenne_scores_item[$item_id]     = round($somme_scores/$nb_scores,0);
      $tab_pourcentage_acquis_item[$item_id] = OutilBilan::calculer_pourcentage_acquisition_items( $tab_acquisitions , $nb_scores );
    }
    else
    {
      $tab_moyenne_scores_item[$item_id]     = FALSE;
      $tab_pourcentage_acquis_item[$item_id] = FALSE;
    }
  }
}

/*
  On renseigne (utile pour le tableau de synthèse et le bulletin) :
  $moyenne_moyenne_scores
  $moyenne_pourcentage_acquis
*/
/*
  on pourrait calculer de 2 façons chacune des deux valeurs...
  pour la moyenne des moyennes obtenues par élève : c'est simple car les coefs ont déjà été pris en compte dans le calcul pour chaque élève
  pour la moyenne des moyennes obtenues par item : c'est compliqué car il faudrait repondérer par les coefs éventuels de chaque item
  donc la 1ère technique a été retenue, à défaut d'essayer de calculer les deux et d'en faire la moyenne ;-)
*/

if( $type_synthese )
{
  // $moyenne_moyenne_scores
  $somme  = array_sum($tab_moyenne_scores_eleve);
  $nombre = count( array_filter($tab_moyenne_scores_eleve,'non_vide') );
  $moyenne_moyenne_scores = ($nombre) ? round($somme/$nombre,0) : FALSE;
  // $moyenne_pourcentage_acquis
  $somme  = array_sum($tab_pourcentage_acquis_eleve);
  $nombre = count( array_filter($tab_pourcentage_acquis_eleve,'non_vide') );
  $moyenne_pourcentage_acquis = ($nombre) ? round($somme/$nombre,0) : FALSE;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On tronque les notes les plus anciennes s'il y en a trop par rapport au nombre de colonnes affichées.
// On tronque les notes antérieures qui n'auraient été récupérées que pour le calcul du score. => Non, on les affiche aussi
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $type_individuel && ($remplissage=='plein') )
{
  if($cases_auto)
  {
    // Déterminer le nombre de colonnes optimal
    $cases_nb = 0;
    foreach($tab_eleve_id as $eleve_id)
    {
      foreach($tab_liste_item as $item_id)
      {
        $eval_nb = (isset($tab_eval[$eleve_id][$item_id])) ? count($tab_eval[$eleve_id][$item_id]) : 0 ;
        $cases_nb = max( $cases_nb , $eval_nb );
      }
    }
    $cases_nb = min( $cases_nb , 15 );
    $cases_largeur = max( 5 , min( 12-$cases_nb , 8 ) );
  }

  foreach($tab_eleve_id as $eleve_id)
  {
    foreach($tab_liste_item as $item_id)
    {
      $eval_nb = (isset($tab_eval[$eleve_id][$item_id])) ? count($tab_eval[$eleve_id][$item_id]) : 0 ;
      // test si trop de notes par rapport au nb de cases
      if($eval_nb>$cases_nb)
      {
        $tab_eval[$eleve_id][$item_id] = array_slice($tab_eval[$eleve_id][$item_id],$eval_nb-$cases_nb);
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la grille d'items d'un référentiel, en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_direct   = ( ( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent')) ) && (SACoche!='webservices') ) ? TRUE : FALSE ;
$affichage_checkbox = ( $type_synthese && ($_SESSION['USER_PROFIL_TYPE']=='professeur') && (SACoche!='webservices') )     ? TRUE : FALSE ;

if( $type_generique || $type_individuel )
{
  $bilan_titre = ($besoin_notes) ? 'Grille d\'items '.$tab_titre_etat[$only_etat].' d\'un référentiel' : 'Grille d\'items d\'un référentiel (vierge)' ;
  $jour_debut_annee_scolaire = To::jour_debut_annee_scolaire('mysql'); // Date de fin de l'année scolaire précédente
  // Initialiser au cas où $aff_coef / $aff_socle / $aff_comm / $aff_lien sont à 0
  $texte_coef       = '';
  $texte_socle      = '';
  $texte_s2016      = '';
  $texte_comm       = '';
  $texte_lien_avant = '';
  $texte_lien_apres = '';
  // Les variables $releve_HTML_individuel et $releve_PDF vont contenir les sorties
  $longueur_ref_max = $aff_reference ? $longueur_ref_max : 0 ;
  $colspan_th_avant = ($longueur_ref_max) ? ' colspan="2"' : '' ;
  $colspan_nb_apres = ($colonne_bilan=='non') ? $cases_nb : $cases_nb+1 ;
  $colspan_th_apres = ($colspan_nb_apres) ? '<th colspan="'.$colspan_nb_apres.'" class="nu"></th>' : '' ;
  $msg_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
  $msg_periode = ($besoin_notes) ? ' - '.$texte_periode : '' ;
  $releve_HTML_individuel  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
  $releve_HTML_individuel .= $affichage_direct ? '' : '<h1>'.$bilan_titre.'</h1>'.NL;
  $releve_HTML_individuel .= $affichage_direct ? '' : '<h2>'.html($matiere_nom.' - Niveau '.$niveau_nom.$msg_socle.$msg_periode).'</h2>'.NL;
  $releve_HTML_individuel .= $affichage_direct ? '<input type="hidden" id="demande_periode_debut_date" value="'.$date_mysql_debut.'" />'.NL : '' ;
  // Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
  $ETABLISSEMENT_NOM = $_SESSION['ETABLISSEMENT']['DENOMINATION'];
  $releve_PDF = new PDF_grille_referentiel( FALSE /*officiel*/ , $orientation , $marge_min /*marge_gauche*/ , $marge_min /*marge_droite*/ , $marge_min /*marge_haut*/ , $marge_min /*marge_bas*/ , $couleur , $fond , $legende , $ETABLISSEMENT_NOM );
  $releve_PDF->initialiser( $cases_nb , $cases_largeur , $lignes_nb , $longueur_ref_max , $colonne_bilan , $colonne_vide , ($retroactif!='non') /*anciennete_notation*/ , ($colonne_bilan=='oui') /*score_bilan*/ , $pages_nb );
  $separation = (count($tab_eleve_infos)>1) ? '<hr />'.NL : '' ;

  // Pour chaque élève...
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    // On met le document au nom de l'élève, ou on établit un document générique
    $releve_PDF->entete( $bilan_titre , $matiere_nom , $niveau_nom , $eleve_id , $eleve_nom , $eleve_prenom );
    $releve_HTML_individuel .= ($eleve_id) ? $separation.'<h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>'.NL : $separation.'<h2>Grille générique</h2>'.NL ;
    $releve_HTML_individuel .= '<table class="bilan">'.NL;
    // Pour chaque domaine...
    if(count($tab_domaine))
    {
      foreach($tab_domaine as $domaine_id => $tab)
      {
        extract($tab);  // $domaine_ref $domaine_nom $domaine_nb_lignes $domaine_used
        $releve_HTML_individuel .= '<tr><th'.$colspan_th_avant.' class="domaine">'.html($domaine_nom).'</th>'.$colspan_th_apres.'</tr>'.NL;
        $releve_PDF->domaine( $domaine_nom , $domaine_nb_lignes );
        // Pour chaque thème...
        if(isset($tab_theme[$domaine_id]))
        {
          foreach($tab_theme[$domaine_id] as $theme_id => $tab)
          {
            extract($tab);  // $theme_ref $theme_nom $theme_nb_lignes $theme_used
            $th_ref = ($longueur_ref_max) ? '<th>'.$theme_ref.'</th>' : '' ;
            $releve_HTML_individuel .= '<tr>'.$th_ref.'<th>'.html($theme_nom).'</th>'.$colspan_th_apres.'</tr>'.NL;
            $releve_PDF->theme( $theme_ref , $theme_nom , $theme_nb_lignes );
            // Pour chaque item...
            if(isset($tab_item[$theme_id]))
            {
              foreach($tab_item[$theme_id] as $item_id => $tab)
              {
                extract($tab);  // $item_ref $item_nom $item_coef $item_cart $item_s2016 $item_lien $item_used
                if($aff_coef)
                {
                  $texte_coef = '['.$item_coef.'] ';
                }
                if($aff_socle)
                {
                  $texte_s2016 = ($item_s2016) ? '[S] ' : '[–] ';
                }
                if($aff_comm)
                {
                  $image_comm  = ($item_comm) ? 'oui' : 'non' ;
                  $title_comm  = ($item_comm) ? convertCRtoBR(html(html($item_comm))) : 'Sans commentaire.' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
                  $texte_comm  = '<img src="./_img/etat/comm_'.$image_comm.'.png" title="'.$title_comm.'" /> ';
                }
                if($aff_lien)
                {
                  $texte_lien_avant = ($item_lien) ? '<a target="_blank" rel="noopener noreferrer" href="'.html($item_lien).'">' : '';
                  $texte_lien_apres = ($item_lien) ? '</a>' : '';
                }
                $score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : FALSE ;
                if($_SESSION['USER_PROFIL_TYPE']=='parent')    { $texte_demande_eval = '<q class="demander_non" title="Les demandes d\'évaluations s\'effectuent depuis un compte élève."></q>'; }
                elseif($_SESSION['USER_PROFIL_TYPE']!='eleve') { $texte_demande_eval = ''; }
                elseif(!$nb_demandes_autorisees)               { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour les items de cette matière."></q>'; }
                elseif(!$item_cart)                            { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour cet item précis."></q>'; }
                else                                           { $texte_demande_eval = '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>'; }
                $td_ref = ($longueur_ref_max) ? '<td>'.$item_ref.'</td>' : '' ;
                $releve_HTML_individuel .= '<tr>'.$td_ref.'<td>'.$texte_coef.$texte_s2016.$texte_comm.$texte_lien_avant.html($item_nom).$texte_lien_apres.$texte_demande_eval.'</td>';
                $releve_PDF->item( $item_ref , $texte_coef.$texte_s2016.$item_nom , $colspan_nb_apres );
                // Pour chaque case...
                if($colspan_nb_apres)
                {
                  for($i=0;$i<$cases_nb;$i++)
                  {
                    if($remplissage=='plein')
                    {
                      if(isset($tab_eval[$eleve_id][$item_id][$i]))
                      {
                        extract($tab_eval[$eleve_id][$item_id][$i]);  // $note $date $info
                      }
                      else
                      {
                        $note = '-'; $date = ''; $info = '';
                      }
                      $pdf_bg = ''; $td_class = '';
                      if( $date && ($date<$jour_debut_annee_scolaire) )
                      {
                        $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_year' : '' ;
                        $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_year"' : '' ;
                      }
                      elseif( $date && ($date<$date_mysql_debut) )
                      {
                        $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_date' : '' ;
                        $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_date"' : '' ;
                      }
                      $releve_HTML_individuel .= '<td'.$td_class.'>'.Html::note_image($note,$date,$info,FALSE).'</td>';
                      $releve_PDF->afficher_note_lomer( $note , 1 /*border*/ , floor(($i+1)/$colspan_nb_apres) /*br*/ , $pdf_bg );
                    }
                    else
                    {
                      $releve_HTML_individuel .= '<td>&nbsp;</td>';
                      $releve_PDF->Cell( $cases_largeur , $releve_PDF->cases_hauteur , '' , 1 , floor(($i+1)/$colspan_nb_apres) , 'C' , TRUE , '' );
                    }
                  }
                  // Case bilan
                  if($colonne_bilan=='oui')
                  {
                    $releve_HTML_individuel .= Html::td_score($score,'score');
                    $releve_PDF->afficher_score_bilan( $score , 1 /*br*/ );
                  }
                }
                $releve_HTML_individuel .= '</tr>'.NL;
              }
            }
          }
        }
      }
    }
    $releve_HTML_individuel .= '</table>'.NL;
    if($legende=='oui')
    {
      $tab_legende = array(
        'codes_notation'      => TRUE ,
        'anciennete_notation' => ($retroactif!='non') ,
        'score_bilan'         => ($colonne_bilan=='oui') ,
      );
      $releve_PDF->legende();
      $releve_HTML_individuel .= Html::legende($tab_legende);
    }
  }
  // On enregistre les sorties HTML et PDF
  FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom_type1.'.html' , $releve_HTML_individuel );
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom_type1.'.pdf'  , $releve_PDF );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la synthèse collective en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($type_synthese)
{
  $bilan_titre = 'd\'items '.$tab_titre_etat[$only_etat].' d\'un référentiel';
  $msg_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
  $msg_periode = ($besoin_notes) ? ' - '.$texte_periode : '' ;
  $matiere_et_niveau = $matiere_nom.' - Niveau '.$niveau_nom.$msg_socle.$msg_periode ;
  $releve_HTML_synthese  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
  $releve_HTML_synthese .= $affichage_direct ? '' : '<h1>Bilan '.$bilan_titre.'</h1>'.NL;
  $releve_HTML_synthese .= '<h2>'.html($matiere_et_niveau).'</h2>'.NL;
  // Appel de la classe et redéfinition de qqs variables supplémentaires pour la mise en page PDF
  // On définit l'orientation la plus adaptée
  $orientation = ( ( ($eleve_nb>$item_nb) && ($tableau_synthese_format=='eleve') ) || ( ($item_nb>$eleve_nb) && ($tableau_synthese_format=='item') ) ) ? 'portrait' : 'landscape' ;
  $releve_PDF = new PDF_item_tableau( FALSE /*officiel*/ , $orientation , $marge_min /*marge_gauche*/ , $marge_min /*marge_droite*/ , $marge_min /*marge_haut*/ , $marge_min /*marge_bas*/ , $couleur , $fond , $legende );
  $releve_PDF->initialiser( $eleve_nb , $item_nb , $tableau_synthese_format );
  $releve_PDF->entete( $bilan_titre , $matiere_et_niveau , '' /*texte_periode*/ );
  // 1ère ligne
  $releve_PDF->ligne_tete_cellule_debut();
  $th = ($tableau_synthese_format=='eleve') ? 'Élève' : 'Item' ;
  $releve_HTML_table_head = '<thead><tr><th data-sorter="text">'.$th.'</th>';
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_liste_item as $item_id)  // Pour chaque item...
    {
      $releve_PDF->ligne_tete_cellule_corps( $tab_item_synthese[$item_id]['item_ref'] );
      $releve_HTML_table_head .= '<th data-sorter="FromData" title="'.html(html($tab_item_synthese[$item_id]['item_nom'])).'"><img alt="'.html($tab_item_synthese[$item_id]['item_ref']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($tab_item_synthese[$item_id]['item_ref']).'&amp;size=8" /></th>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    }
  }
  else
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      $releve_PDF->ligne_tete_cellule_corps( $eleve_nom.' '.$eleve_prenom );
      $releve_HTML_table_head .= '<th data-sorter="FromData"><img alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'&amp;size=8" /></th>';
    }
  }
  $releve_PDF->ligne_tete_cellules_fin();
  $entete_vide   = ($repeter_entete)     ? '<th data-sorter="false" class="nu">&nbsp;</th>' : '' ;
  $checkbox_vide = ($affichage_checkbox) ? '<th data-sorter="false" class="nu">&nbsp;</th>' : '' ;
  $releve_HTML_table_head .= '<th data-sorter="false" class="nu">&nbsp;</th><th data-sorter="FromData" data-empty="bottom">[ * ]</th><th data-sorter="FromData" data-empty="bottom">[ ** ]</th>'.$entete_vide.$checkbox_vide.'</tr></thead>'.NL;
  // lignes suivantes
  $releve_HTML_table_body = '';
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      $releve_PDF->ligne_corps_cellule_debut( $eleve_nom.' '.$eleve_prenom );
      $entete = '<td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
      $releve_HTML_table_body .= '<tr>'.$entete;
      foreach($tab_liste_item as $item_id)  // Pour chaque item...
      {
        $score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : FALSE ;
        $releve_PDF->afficher_score_bilan( $score , 0 /*br*/ );
        $checkbox_val = ($affichage_checkbox) ? $eleve_id.'x'.$item_id : '' ;
        $releve_HTML_table_body .= Html::td_score($score,$tableau_tri_etat_mode,'',$checkbox_val);
      }
      $valeur1 = (isset($tab_moyenne_scores_eleve[$eleve_id])) ? $tab_moyenne_scores_eleve[$eleve_id] : FALSE ;
      $valeur2 = (isset($tab_pourcentage_acquis_eleve[$eleve_id])) ? $tab_pourcentage_acquis_eleve[$eleve_id] : FALSE ;
      $releve_PDF->ligne_corps_cellules_fin( $valeur1 , $valeur2 , FALSE , TRUE );
      $col_entete   = ($repeter_entete) ? $entete : '' ;
      $col_checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>' : '' ;
      $releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.Html::td_score($valeur1,$tableau_tri_etat_mode,'%').Html::td_score($valeur2,$tableau_tri_etat_mode,'%').$col_entete.$col_checkbox.'</tr>'.NL;
    }
  }
  else
  {
    foreach($tab_liste_item as $item_id)  // Pour chaque item...
    {
      $releve_PDF->ligne_corps_cellule_debut( $tab_item_synthese[$item_id]['item_ref'] );
      $entete = '<td title="'.html(html($tab_item_synthese[$item_id]['item_nom'])).'">'.html($tab_item_synthese[$item_id]['item_ref']).'</td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
      $releve_HTML_table_body .= '<tr>'.$entete;
      foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
      {
        $score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : FALSE ;
        $releve_PDF->afficher_score_bilan( $score , 0 /*br*/ );
        $checkbox_val = ($affichage_checkbox) ? $eleve_id.'x'.$item_id : '' ;
        $releve_HTML_table_body .= Html::td_score($score,$tableau_tri_etat_mode,'',$checkbox_val);
      }
      $valeur1 = $tab_moyenne_scores_item[$item_id];
      $valeur2 = $tab_pourcentage_acquis_item[$item_id];
      $releve_PDF->ligne_corps_cellules_fin( $valeur1 , $valeur2 , FALSE , TRUE );
      $col_entete   = ($repeter_entete) ? $entete : '' ;
      $col_checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_item[]" value="'.$item_id.'" /></td>' : '' ;
      $releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.Html::td_score($valeur1,$tableau_tri_etat_mode,'%').Html::td_score($valeur2,$tableau_tri_etat_mode,'%').$col_entete.$col_checkbox.'</tr>'.NL;
    }
  }
  $releve_HTML_table_body = '<tbody>'.NL.$releve_HTML_table_body.'</tbody>'.NL;
  // dernière ligne (doublée)
  $releve_PDF->lignes_pied_cellules_debut( 'pondérée' );
  $releve_HTML_table_foot1 = '<tr><th>moyenne scores [*]</th>';
  $releve_HTML_table_foot2 = '<tr><th>% items acquis [**]</th>';
  $row_entete   = ($repeter_entete)     ? '<tr><th class="nu">&nbsp;</th>' : '' ;
  $row_checkbox = ($affichage_checkbox) ? '<tr><th class="nu">&nbsp;</th>' : '' ;
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_liste_item as $item_id)  // Pour chaque item...
    {
      $valeur1 = $tab_moyenne_scores_item[$item_id];
      $valeur2 = $tab_pourcentage_acquis_item[$item_id];
      $releve_PDF->ligne_corps_cellules_fin( $valeur1 , $valeur2 , TRUE , FALSE );
      $releve_HTML_table_foot1 .= Html::td_score($valeur1,'score','%');
      $releve_HTML_table_foot2 .= Html::td_score($valeur2,'score','%');
      $row_entete   .= ($repeter_entete) ? '<th class="hc" title="'.html(html($tab_item_synthese[$item_id]['item_nom'])).'"><img alt="'.html($tab_item_synthese[$item_id]['item_ref']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($tab_item_synthese[$item_id]['item_ref']).'&amp;size=8" /></th>' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
      $row_checkbox .= ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_item[]" value="'.$item_id.'" /></td>' : '' ;
    }
  }
  else
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      $valeur1 = (isset($tab_moyenne_scores_eleve[$eleve_id]))     ? $tab_moyenne_scores_eleve[$eleve_id]     : FALSE ;
      $valeur2 = (isset($tab_pourcentage_acquis_eleve[$eleve_id])) ? $tab_pourcentage_acquis_eleve[$eleve_id] : FALSE ;
      $releve_PDF->ligne_corps_cellules_fin( $valeur1 , $valeur2 , TRUE , FALSE );
      $releve_HTML_table_foot1 .= Html::td_score($valeur1,'score','%');
      $releve_HTML_table_foot2 .= Html::td_score($valeur2,'score','%');
      $row_entete   .= ($repeter_entete) ? '<th class="hc"><img alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'&amp;size=8" /></th>' : '' ;
      $row_checkbox .= ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>' : '' ;
    }
  }
  // les deux dernières cases (moyenne des moyennes)
  $colspan  = ($tableau_synthese_format=='eleve') ? $item_nb+1 : $eleve_nb+1 ;
  $releve_PDF->ligne_corps_cellules_fin( $moyenne_moyenne_scores , $moyenne_pourcentage_acquis , TRUE , TRUE );
  $releve_HTML_table_foot1 .= '<th class="nu">&nbsp;</th>'.Html::td_score($moyenne_moyenne_scores,'score','%').'<th class="nu">&nbsp;</th>'.$entete_vide.$checkbox_vide.'</tr>'.NL;
  $releve_HTML_table_foot2 .= '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.Html::td_score($moyenne_pourcentage_acquis,'score','%').$entete_vide.$checkbox_vide.'</tr>'.NL;
  $row_entete   .= ($repeter_entete)     ? '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.$entete_vide.$checkbox_vide.'</tr>'.NL : '' ;
  $row_checkbox .= ($affichage_checkbox) ? '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.$entete_vide.$checkbox_vide.'</tr>'.NL : '' ;
  $releve_HTML_table_foot = '<tfoot>'.NL.'<tr class="vide"><td class="nu" colspan="'.$colspan.'">&nbsp;</td><td class="nu"></td><td class="nu"><td class="nu">'.$entete_vide.$checkbox_vide.'</tr>'.NL.$releve_HTML_table_foot1.$releve_HTML_table_foot2.$row_entete.$row_checkbox.'</tfoot>'.NL;
  // sortie HTML
  $releve_HTML_synthese .= '<hr />'.NL.'<h2>SYNTHESE (selon l\'objet et le mode de tri choisis)</h2>'.NL;
  $releve_HTML_synthese .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">'.NL : '' ;
  $releve_HTML_synthese .= '<table id="table_s" class="bilan_synthese vsort">'.NL.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>'.NL;
  // Légende
  if($legende=='oui')
  {
    $releve_PDF->legende();
    $releve_HTML_synthese .= Html::legende( array('score_bilan'=>TRUE) );
  }
  $script = $affichage_direct ? '$("#table_s").tablesorter();' : 'function tri(){$("#table_s").tablesorter();}' ;
  $releve_HTML_synthese .= ($affichage_checkbox) ? HtmlForm::afficher_synthese_exploitation('eleves + eleves-items + items').'</form>'.NL : '';
  $releve_HTML_synthese .= '<script type="text/javascript">'.$script.'</script>'.NL;
  // On enregistre les sorties HTML et PDF
  FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom_type2.'.html' , $releve_HTML_synthese );
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom_type2.'.pdf'  , $releve_PDF );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$retour = '';

if($affichage_direct)
{
  $retour .= '<hr />'.NL;
  $retour .= '<ul class="puce">'.NL;
  $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_nom_type1.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
  $retour .= '</ul>'.NL;
  $retour .= $releve_HTML_individuel;
}
else
{
  if($type_synthese)
  {
    $retour .= '<h2>Synthèse collective</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_nom_type2.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.$fichier_nom_type2.'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    $retour .= '</ul>'.NL;
  }
  if( $type_generique || $type_individuel )
  {
    $h2 = ($type_individuel) ? 'Relevé individuel' : 'Relevé générique' ;
    $retour .= '<h2>'.$h2.'</h2>'.NL;
    $retour .= '<ul class="puce">'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fichier_nom_type1.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>'.NL;
    $retour .=   '<li><a target="_blank" rel="noopener noreferrer" href="./releve_html.php?fichier='.$fichier_nom_type1.'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>'.NL;
    $retour .= '</ul>'.NL;
  }
}

Json::add_tab( array(
  'direct' => $affichage_direct ,
  'bilan'  => $retour ,
) );
Json::end( TRUE );

?>
