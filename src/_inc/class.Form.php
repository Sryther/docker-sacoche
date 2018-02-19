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

class Form
{

  private static $dossier_cookie = '';
  private static $fichier_cookie = '';

  // //////////////////////////////////////////////////
  // Tableaux prédéfinis
  // //////////////////////////////////////////////////

  public static $tab_select_socle_detail = array(
    array('valeur' => 'livret' , 'texte' => 'comme le Livret Scolaire (les 4 composantes du premier domaine + les 4 autres domaines dans leur globalité)') ,
    array('valeur' => 'detail' , 'texte' => 'détaillé par composante (pour chacun des 5 domaines)') ,
  );

  public static $tab_echelle = array(
    array('valeur' => 'fixe' , 'texte' => 'gradué de 0 à 20 (ou 100%)') ,
    array('valeur' => 'auto' , 'texte' => 'recentré en fonction des valeurs') ,
  );

  public static $tab_select_eleves_ordre = array(
    array('valeur' => 'alpha'  , 'texte' => 'ordonnés alphabétiquement') ,
    array('valeur' => 'classe' , 'texte' => 'ordonnés par classe d\'origine') ,
  );

  public static $tab_select_individuel_format = array(
    array('valeur' => 'eleve' , 'texte' => 'présenté par élève') ,
    array('valeur' => 'item'  , 'texte' => 'présenté par item') ,
  );

  public static $tab_select_synthese_format = array(
    array('valeur' => 'eleve' , 'texte' => 'élèves en lignes (triés pour un item donné)') ,
    array('valeur' => 'item'  , 'texte' => 'items en lignes (triés pour un élève donné)') ,
  );

  public static $tab_select_socle_individuel_format = array(
    array('valeur' => 'eleve'      , 'texte' => 'présenté par élève') ,
    array('valeur' => 'composante' , 'texte' => 'présenté par composante') ,
  );

  public static $tab_select_socle_synthese_format = array(
    array('valeur' => 'eleve'      , 'texte' => 'élèves en lignes (triés pour une composante donnée)') ,
    array('valeur' => 'composante' , 'texte' => 'composantes en lignes (triées pour un élève donné)') ,
  );

  public static $tab_select_only_etat = array(
    array('valeur' => 'tous'       , 'texte' => 'tous les états de réussite') ,
    array('valeur' => 'acquis'     , 'texte' => 'seulement les items réussis') ,
    array('valeur' => 'non_acquis' , 'texte' => 'seulement les items échoués') ,
  );

  public static $tab_select_only_arbo = array(
    array('valeur' => 'tous'    , 'texte' => 'toute la grille') ,
    array('valeur' => 'domaine' , 'texte' => 'seulement les domaines évalués') ,
    array('valeur' => 'theme'   , 'texte' => 'seulement les thèmes évalués') ,
    array('valeur' => 'item'    , 'texte' => 'seulement les items évalués') ,
  );

  public static $tab_select_tri_etat_mode = array(
    array('valeur' => 'score' , 'texte' => 'tri par score d\'acquisition (pour un tri unique)') ,
    array('valeur' => 'etat'  , 'texte' => 'tri par état d\'acquisition (pour un tri multiple)') ,
  );

  public static $tab_select_tri_maitrise_mode = array(
    array('valeur' => 'score' , 'texte' => 'tri par pourcentage de maîtrise (pour un tri unique)') ,
    array('valeur' => 'etat'  , 'texte' => 'tri par degré de maîtrise (pour un tri multiple)') ,
  );

  public static $tab_select_orientation = array(
    array('valeur' => 'portrait'  , 'texte' => 'Portrait (vertical)') ,
    array('valeur' => 'landscape' , 'texte' => 'Paysage (horizontal)') ,
  );

  public static $tab_select_marge_min = array(
    array('valeur' =>  5 , 'texte' => 'marges de 5 mm')  ,
    array('valeur' =>  6 , 'texte' => 'marges de 6 mm')  ,
    array('valeur' =>  7 , 'texte' => 'marges de 7 mm')  ,
    array('valeur' =>  8 , 'texte' => 'marges de 8 mm')  ,
    array('valeur' =>  9 , 'texte' => 'marges de 9 mm')  ,
    array('valeur' => 10 , 'texte' => 'marges de 10 mm') ,
    array('valeur' => 11 , 'texte' => 'marges de 11 mm') ,
    array('valeur' => 12 , 'texte' => 'marges de 12 mm') ,
    array('valeur' => 13 , 'texte' => 'marges de 13 mm') ,
    array('valeur' => 14 , 'texte' => 'marges de 14 mm') ,
    array('valeur' => 15 , 'texte' => 'marges de 15 mm') ,
  );

  public static $tab_select_pages_nb = array(
    array('valeur' => 'optimise' , 'texte' => 'nombre de pages optimisé') ,
    array('valeur' => 'augmente' , 'texte' => 'nombre de pages augmenté') ,
  );

  public static $tab_select_couleur = array(
    array('valeur' => 'oui' , 'texte' => 'en couleurs') ,
    array('valeur' => 'non' , 'texte' => 'en niveaux de gris') ,
  );

  public static $tab_select_fond = array(
    array('valeur' => 'gris'  , 'texte' => 'fonds grisés') ,
    array('valeur' => 'blanc' , 'texte' => 'fonds blancs') ,
  );

  public static $tab_select_legende = array(
    array('valeur' => 'oui' , 'texte' => 'avec légende') ,
    array('valeur' => 'non' , 'texte' => 'sans légende') ,
  );

  public static $tab_select_cases_nb = array(
    array('valeur' =>  0 , 'texte' =>  '0 case')  ,
    array('valeur' =>  1 , 'texte' =>  '1 case')  ,
    array('valeur' =>  2 , 'texte' =>  '2 cases') ,
    array('valeur' =>  3 , 'texte' =>  '3 cases') ,
    array('valeur' =>  4 , 'texte' =>  '4 cases') ,
    array('valeur' =>  5 , 'texte' =>  '5 cases') ,
    array('valeur' =>  6 , 'texte' =>  '6 cases') ,
    array('valeur' =>  7 , 'texte' =>  '7 cases') ,
    array('valeur' =>  8 , 'texte' =>  '8 cases') ,
    array('valeur' =>  9 , 'texte' =>  '9 cases') ,
    array('valeur' => 10 , 'texte' => '10 cases') ,
    array('valeur' => 11 , 'texte' => '11 cases') ,
    array('valeur' => 12 , 'texte' => '12 cases') ,
    array('valeur' => 13 , 'texte' => '13 cases') ,
    array('valeur' => 14 , 'texte' => '14 cases') ,
    array('valeur' => 15 , 'texte' => '15 cases') ,
  );

  public static $tab_select_cases_size = array(
    array('valeur' =>  5 , 'texte' =>  '5 mm') ,
    array('valeur' =>  6 , 'texte' =>  '6 mm') ,
    array('valeur' =>  7 , 'texte' =>  '7 mm') ,
    array('valeur' =>  8 , 'texte' =>  '8 mm') ,
    array('valeur' =>  9 , 'texte' =>  '9 mm') ,
    array('valeur' => 10 , 'texte' => '10 mm') ,
    array('valeur' => 11 , 'texte' => '11 mm') ,
    array('valeur' => 12 , 'texte' => '12 mm') ,
    array('valeur' => 13 , 'texte' => '13 mm') ,
    array('valeur' => 14 , 'texte' => '14 mm') ,
    array('valeur' => 15 , 'texte' => '15 mm') ,
  );

  public static $tab_select_remplissage = array(
    array('valeur' => 'vide'  , 'texte' => 'sans les dernières notes') ,
    array('valeur' => 'plein' , 'texte' => 'avec les dernières notes') ,
  );

  public static $tab_select_colonne_bilan = array(
    array('valeur' => 'non' , 'texte' => 'sans colonne bilan') ,
    array('valeur' => 'oui' , 'texte' => 'avec colonne bilan') ,
  );

  public static $tab_select_colonne_vide = array(
    array('valeur' =>  0  , 'texte' => 'sans colonne supplémentaire')  ,
    array('valeur' => 30  , 'texte' => 'avec une colonne vide de 1cm') ,
    array('valeur' => 30  , 'texte' => 'avec une colonne vide de 3cm') ,
    array('valeur' => 50  , 'texte' => 'avec une colonne vide de 5cm') ,
    array('valeur' => 70  , 'texte' => 'avec une colonne vide de 7cm') ,
    array('valeur' => 90  , 'texte' => 'avec une colonne vide de 9cm') ,
  );

  public static $tab_select_cart_detail = array(
    array('valeur' => 'complet' , 'texte' => 'avec la dénomination complète de chaque item') ,
    array('valeur' => 'minimal' , 'texte' => 'minimal avec uniquement les références des items') ,
  );

  public static $tab_select_cart_cases_nb = array(
    array('valeur' => 1 , 'texte' => 'avec une seule case par item pour la notation (à remplir)') ,
    array('valeur' => 5 , 'texte' => 'avec plusieurs cases par item pour la notation (à cocher)') ,
  );

  public static $tab_select_cart_contenu = array(
    array('valeur' => 'SANS_nom_SANS_result' , 'texte' => 'SANS les noms d\'élèves et SANS les résultats') ,
    array('valeur' => 'AVEC_nom_SANS_result' , 'texte' => 'AVEC les noms d\'élèves mais SANS les résultats') ,
    array('valeur' => 'AVEC_nom_AVEC_result' , 'texte' => 'AVEC les noms d\'élèves et AVEC les résultats (si saisis)') ,
  );

  public static $tab_select_cart_hauteur = array(
    array('valeur' => 'variable' , 'texte' => 'adaptée au contenu pour chaque élève (optimisation du papier)') ,
    array('valeur' => 'fixe'     , 'texte' => 'fixe quel que soit le contenu (massicotage de l\'ensemble)') ,
  );

  public static $tab_select_repart_ref_pourcentage = array(
    array('valeur' => 'tous'      , 'texte' => 'par rapport à tous les élèves du regroupement') ,
    array('valeur' => 'only_eval' , 'texte' => 'par rapport aux seuls élèves évalués') ,
  );

  public static $tab_select_recherche_objet = array(
    array('valeur' => 'matiere_items_bilanMS'         , 'optgroup'=>1 , 'texte' => 'moyenne des scores d\'acquisition') ,
    array('valeur' => 'matiere_items_bilanPA'         , 'optgroup'=>1 , 'texte' => 'pourcentage d\'items acquis') ,
    array('valeur' => 'socle2016_domaine_maitrise'    , 'optgroup'=>2 , 'texte' => 'degré de maîtrise d\'un domaine') ,
    array('valeur' => 'socle2016_composante_maitrise' , 'optgroup'=>2 , 'texte' => 'degré de maîtrise d\'une composante') ,
  );

  public static $tab_select_statut = array(
    array('valeur' => 1 , 'texte' => 'comptes actuels (date de sortie sans objet ou ultérieure)') ,
    array('valeur' => 0 , 'texte' => 'comptes anciens (date de sortie présente et antérieure)') ,
  );

  public static $tab_select_appreciation = array(
    array('valeur' =>   0 , 'texte' => 'Non → pas de saisie d\'appréciation') ,
    array('valeur' => 100 , 'texte' => 'Oui → 100 caractères maximum (super court)') ,
    array('valeur' => 200 , 'texte' => 'Oui → 200 caractères maximum (très court)') ,
    array('valeur' => 300 , 'texte' => 'Oui → 300 caractères maximum (court)') ,
    array('valeur' => 400 , 'texte' => 'Oui → 400 caractères maximum (assez court)') ,
    array('valeur' => 500 , 'texte' => 'Oui → 500 caractères maximum (moyen)') ,
    array('valeur' => 600 , 'texte' => 'Oui → 600 caractères maximum (long)') ,
    array('valeur' => 700 , 'texte' => 'Oui → 700 caractères maximum (assez long)') ,
    array('valeur' => 800 , 'texte' => 'Oui → 800 caractères maximum (très long)') ,
    array('valeur' => 900 , 'texte' => 'Oui → 900 caractères maximum (super long)') ,
    array('valeur' => 999 , 'texte' => 'Oui → 999 caractères maximum (trop long…)') ,
  );

  public static $tab_select_import_notes = array(
    array('valeur' => 'tous' , 'texte' => 'importer tous les positionnements du bulletin à l\'identique') ,
    array('valeur' => 'reel' , 'texte' => 'n\'importer du bulletin que les positionnements non supprimés et calculer ceux qui manquent') ,
    array('valeur' => 'rien' , 'texte' => 'déterminer automatiquement les positionnements sans rien importer du bulletin') ,
  );

  public static $tab_select_optgroup = array(
    'regroupements' => array(
      'divers' => 'Divers',
      'niveau' => 'Niveaux',
      'classe' => 'Classes',
      'groupe' => 'Groupes',
      'besoin' => 'Besoins',
    ),
    'familles_matieres' => array(
      1 => 'Enseignements usuels',
      2 => 'Enseignements généraux',
      3 => 'Enseignements spécifiques',
      4 => 'Enseignements complémentaires',
    ),
    'familles_niveaux' => array(
      1 => 'Niveaux usuels',
      2 => 'Niveaux particuliers',
      3 => 'Niveaux classes',
    ),
    'profs_directeurs' => array(
      'directeur'  => 'Directeurs',
      'professeur' => 'Professeurs',
    ),
    'langues' => array(
      0 => 'Inconnue',
      1 => 'Enseignées',
      2 => 'Autres',
    ),
    'objet_recherche' => array(
      1 => 'item(s) matière(s)',
      2 => 'socle',
    ),
    'officiel_type' => array(
      'sacoche' => 'SACoche',
      'livret'  => 'Livret Scolaire',
    ),
    'epi_theme' => array(
      1 => 'Thématiques nationales',
      2 => 'Thématiques personnalisées',
    ),
    // complété à partir de la base si besoin (contenu dynamique)
    'zones_geo'  => array(), 
    'continents' => array(),
  );

  public static $tab_select_option_first = array(
    'periode_personnalisee' => array(  0 , 'Personnalisée' ),
    'tous_profs'            => array(  0, 'Tous les enseignants' ),
    'tous_regroupements'    => array(  0 , 'Tous les regroupements' ),
    'toutes_annees'         => array(  0 , 'Toutes les années' ),
    'toutes_periodes'       => array( '' , 'Toutes les périodes' ), // la valeur 0 pouvant indiquer une période de cycle...
    'toutes_matieres'       => array(  0 , 'Toutes les matières' ),
    'toutes_archives'       => array(  0 , 'Toutes les archives officielles' ),
    'toutes_origines'       => array(  0 , 'Toutes structures d\'origine' ),
    'toutes_structures'     => array(  0 , 'Toutes structures' ),
    'fiche_generique'       => array(  0 , 'Fiche générique' ),
    'tampon_structure'      => array(  0 , 'Tampon de l\'établissement' ),
    'structures_partage'    => array(  0 , 'Toutes les structures partageant au moins un référentiel' ),
    // inutilisé
    'tous_niveaux'          => array(  0 , 'Tous les niveaux' ),
    'tous_piliers'          => array(  0 , 'Toutes les compétences' ),
    'tous_domaines'         => array(  0 , 'Tous les domaines' ),
    // complété à partir de la base si besoin (car indice variable)
    'matieres_famille'      => array(), 
    'niveaux_famille'       => array(),
  );

  // //////////////////////////////////////////////////
  // Méthodes
  // //////////////////////////////////////////////////

  public static $tab_choix = array();

  /**
   * Initialiser deux propriétés...
   * 
   * @param void
   * @return void
   */
  private static function init_variables()
  {
    Form::$dossier_cookie = CHEMIN_DOSSIER_COOKIE.$_SESSION['BASE'].DS;
    Form::$fichier_cookie = Form::$dossier_cookie.'user'.$_SESSION['USER_ID'].'.txt';
  }

  /**
   * Initialiser les choix d'un formulaire (certains choix sont présélectionnés ou imposés suivant les statuts).
   * Ce tableau sera ensuite surchargé avec les choix mémorisés éventuels (enregistré dans un fichier texte).
   * En cas d'ajout ultérieur d'une fonctionnalité, compléter cette fonction permet de ne pas générer d'erreur.
   * 
   * @param void
   * @return void
   */
  private static function init_tab_choix()
  {
    Form::init_variables();
    $check_type_individuel    = (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ? 1 : 0 ;
    $check_etat_acquisition   = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION'])   ) ? 1 : 0 ;
    $check_moyenne_score      = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ) ? 1 : 0 ;
    $check_pourcentage_acquis = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ) ? 1 : 0 ;
    $check_conversion_sur_20  = Outil::test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20']) ? 1 : 0 ;
    $check_aff_lien           = (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ? 1 : 0 ;
    Form::$tab_choix = array(
      'eleves_ordre'              => 'alpha' ,
      'matiere_id'                => 0 ,
      'niveau_id'                 => 0 ,
      'cycle_id'                  => 0 ,
      'orientation'               => 'portrait' ,
      'couleur'                   => 'oui' ,
      'fond'                      => 'gris' ,
      'legende'                   => 'oui' , 
      'marge_min'                 => 5 ,
      'pages_nb'                  => 'optimise' ,
      'cart_detail'               => 'complet' ,
      'cart_cases_nb'             => 1 ,
      'cart_contenu'              => 'AVEC_nom_SANS_result' ,
      'cart_hauteur'              => 'variable' ,
      'cart_restriction_item'     => 0 ,
      'cart_restriction_eleve'    => 0 ,
      'repart_categorie_autre'    => 1 ,
      'repart_ref_pourcentage'    => 'tous' ,
      'only_etat'                 => 'tous',
      'only_arbo'                 => 'tous',
      'only_niveau'               => 0 ,
      'only_presence'             => 0 ,
      'only_socle'                => 0 ,
      'aff_reference'             => 1 ,
      'aff_coef'                  => 0 ,
      'aff_socle'                 => 1 ,
      'aff_comm'                  => 0 ,
      'aff_lien'                  => $check_aff_lien ,
      'aff_start'                 => 0 ,
      'aff_domaine'               => 0 ,
      'aff_theme'                 => 0 ,
      'cases_auto'                => 1 ,
      'cases_nb'                  => 4 ,
      'cases_largeur'             => 5 ,
      'remplissage'               => 'plein' ,
      'colonne_bilan'             => 'oui' ,
      'colonne_vide'              => 0 ,
      'type_generique'            => 0 ,
      'type_individuel'           => $check_type_individuel ,
      'type_synthese'             => 0 ,
      'type_bulletin'             => 0 ,
      'aff_etat_acquisition'      => $check_etat_acquisition ,
      'aff_moyenne_scores'        => $check_moyenne_score ,
      'aff_pourcentage_acquis'    => $check_pourcentage_acquis ,
      'conversion_sur_20'         => $check_conversion_sur_20 ,
      'indicateur'                => 'moyenne_scores' ,
      'releve_individuel_format'  => 'eleve',
      'tableau_synthese_format'   => 'eleve',
      'socle_detail'              => 'livret',
      'socle_individuel_format'   => 'eleve',
      'socle_synthese_format'     => 'eleve',
      'socle_synthese_affichage'  => 'pourcentage',
      'tableau_tri_etat_mode'     => 'score',
      'tableau_tri_maitrise_mode' => 'score',
      'repeter_entete'            => 0 ,
      'with_coef'                 => 1 ,
      'retroactif'                => 'auto' ,
      'mode_synthese'             => 'predefini' ,
      'fusion_niveaux'            => 1 ,
      'aff_socle_items_acquis'    => 1 ,
      'aff_socle_position'        => 1 ,
      'aff_socle_points_DNB'      => 1 ,
      'type'                      => '' ,
      'mode'                      => 'auto' ,
      'echelle'                   => 'fixe' ,
    );
  }

  /**
   * Charger les choix mémorisées d'un formulaire.
   * 
   * On commence par initialiser les valeurs (certains choix sont présélectionnés ou imposés suivant les statuts).
   * Puis on surcharge avec un choix mémorisé éventuel (enregistré dans un fichier texte).
   * 
   * @param void
   * @return void
   */

  public static function load_choix_memo()
  {
    Form::init_tab_choix();
    // Récupération du contenu du "cookie"
    if(is_file(Form::$fichier_cookie))
    {
      $contenu = file_get_contents(Form::$fichier_cookie);
      $tab_choix_cookie = @unserialize($contenu);
      if(is_array($tab_choix_cookie))
      {
        Form::$tab_choix = array_merge( Form::$tab_choix , $tab_choix_cookie );
      }
    }
  }

  /**
   * Enregistrer les choix mémorisées d'un formulaire.
   * 
   * @param string $page
   * @return void
   */

   public static function save_choix($page)
  {
    switch($page)
    {
      case 'grille_referentiel' :
        global $eleves_ordre,$matiere_id,$niveau_id,$type_generique,$type_individuel,$type_synthese,$tableau_synthese_format,$tableau_tri_etat_mode,$repeter_entete,$retroactif,$only_etat,$only_arbo,$only_socle,$aff_reference,$aff_coef,$aff_socle,$aff_comm,$aff_lien,$cases_auto,$cases_nb,$cases_largeur,$remplissage,$colonne_bilan,$colonne_vide,$orientation,$couleur,$fond,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('eleves_ordre','matiere_id','niveau_id','type_generique','type_individuel','type_synthese','tableau_synthese_format','tableau_tri_etat_mode','repeter_entete','retroactif','only_etat','only_arbo','only_socle','aff_reference','aff_coef','aff_socle','aff_comm','aff_lien','cases_auto','cases_nb','cases_largeur','remplissage','colonne_bilan','colonne_vide','orientation','couleur','fond','legende','marge_min','pages_nb');
        break;
      case 'releve_items' :
        global $eleves_ordre,$matiere_id,$type_individuel,$type_synthese,$type_bulletin,$releve_individuel_format,$aff_etat_acquisition,$aff_moyenne_scores,$aff_pourcentage_acquis,$conversion_sur_20,$tableau_synthese_format,$tableau_tri_etat_mode,$repeter_entete,$with_coef,$retroactif,$only_etat,$only_socle,$aff_reference,$aff_coef,$aff_socle,$aff_comm,$aff_lien,$aff_domaine,$aff_theme,$cases_auto,$cases_nb,$cases_largeur,$orientation,$couleur,$fond,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('eleves_ordre','matiere_id','type_individuel','type_synthese','type_bulletin','releve_individuel_format','aff_etat_acquisition','aff_moyenne_scores','aff_pourcentage_acquis','conversion_sur_20','tableau_synthese_format','tableau_tri_etat_mode','repeter_entete','with_coef','retroactif','only_etat','only_socle','aff_reference','aff_coef','aff_socle','aff_comm','aff_lien','aff_domaine','aff_theme','cases_auto','cases_nb','cases_largeur','orientation','couleur','fond','legende','marge_min','pages_nb');
        break;
      case 'bilan_chronologique' :
        global $eleves_ordre,$mode_synthese,$fusion_niveaux,$indicateur,$conversion_sur_20,$retroactif,$only_socle,$echelle;
        $tab_choix_new = compact('eleves_ordre','mode_synthese','fusion_niveaux','indicateur','conversion_sur_20','retroactif','only_socle','echelle');
        break;
      case 'releve_synthese' :
        global $eleves_ordre,$matiere_id,$mode_synthese,$fusion_niveaux,$retroactif,$only_socle,$only_niveau,$aff_coef,$aff_socle,$aff_lien,$aff_start,$couleur,$fond,$legende,$marge_min;
        $tab_choix_new = compact('eleves_ordre','matiere_id','mode_synthese','fusion_niveaux','retroactif','only_socle','only_niveau','aff_coef','aff_socle','aff_lien','aff_start','couleur','fond','legende','marge_min');
        break;
      case 'releve_socle2016' :
        global $eleves_ordre,$cycle_id,$socle_detail,$type_individuel,$type_synthese,$socle_individuel_format,$socle_synthese_format,$socle_synthese_affichage,$tableau_tri_maitrise_mode,$aff_socle_items_acquis,$aff_socle_position,$aff_socle_points_DNB,$only_presence,$aff_lien,$aff_start,$mode,$couleur,$fond,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('eleves_ordre','cycle_id','socle_detail','type_individuel','type_synthese','socle_individuel_format','socle_synthese_format','socle_synthese_affichage','tableau_tri_maitrise_mode','aff_socle_items_acquis','aff_socle_position','aff_socle_points_DNB','only_presence','aff_lien','aff_start','mode','couleur','fond','legende','marge_min','pages_nb');
        break;
      case 'matiere' :
        global $matiere_id;
        $tab_choix_new = compact('matiere_id');
        break;
      case 'cycle' :
        global $cycle_id;
        $tab_choix_new = compact('cycle_id');
        break;
      case 'evaluation_cartouche' :
        global $orientation,$couleur,$fond,$legende,$marge_min,$cart_detail,$cart_cases_nb,$cart_contenu,$cart_restriction_item,$cart_restriction_eleve,$cart_hauteur,$aff_reference,$aff_coef,$aff_socle;
        $tab_choix_new = compact('orientation','couleur','fond','legende','marge_min','cart_detail','cart_cases_nb','cart_contenu','cart_restriction_item','cart_restriction_eleve','cart_hauteur','aff_reference','aff_coef','aff_socle');
        break;
      case 'evaluation_statistiques' :
        global $couleur,$fond,$repart_categorie_autre,$repart_ref_pourcentage,$cart_detail,$aff_reference,$aff_coef,$aff_socle;
        $tab_choix_new = compact('couleur','fond','repart_categorie_autre','repart_ref_pourcentage');
        break;
      case 'evaluation_archivage' :
        global $couleur,$fond;
        $tab_choix_new = compact('couleur','fond');
        break;
      case 'evaluation_gestion' :
        global $eleves_ordre;
        $tab_choix_new = compact('eleves_ordre');
        break;
      default :
        $tab_choix_new = array();
    }
    // Récupération du contenu du "cookie", surchargé avec les choix effectués
    Form::load_choix_memo();
    Form::$tab_choix = array_merge( Form::$tab_choix , $tab_choix_new );
    /*
      Remarque : il y a un problème de serialize avec les type float : voir http://fr2.php.net/manual/fr/function.serialize.php#85988
      Dans ce cas il faut remplacer
      serialize(Form::$tab_choix)
      par
      private static function callback_float($matches) { return "'d:'.(round($matches[1],9)).';'"; }
      preg_replace_callback( '/d:([0-9]+(\.[0-9]+)?([Ee][+-]?[0-9]+)?);/', "Form::callback_float", serialize(Form::$tab_choix) );
    */
    FileSystem::ecrire_fichier(Form::$fichier_cookie,serialize(Form::$tab_choix));
  }

}
?>