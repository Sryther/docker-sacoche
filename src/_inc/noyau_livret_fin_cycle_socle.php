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

/**
 * Code inclus commun aux pages
 * [./_inc/code_livret_***.php]
 */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Chemin d'enregistrement

$fichier_nom = Clean::fichier('livret_'.$PAGE_REF.'_'.$JOINTURE_PERIODE.'_'.$groupe_nom.'_').FileSystem::generer_fin_nom_fichier__date_et_alea();

// Initialisation de tableaux

$tab_saisie_initialisation = array( 'saisie_id'=>0 , 'prof_id'=>NULL , 'saisie_valeur'=>NULL , 'saisie_origine'=>NULL , 'listing_profs'=>NULL , 'acquis_detail'=>NULL );
$tab_parent_lecture        = array( 'resp1'=>NULL , 'resp2'=>NULL , 'resp3'=>NULL , 'resp4'=>NULL );

$socle_detail = 'livret';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve_infos      = array();  // [eleve_id] => array(eleve_INE,eleve_nom,eleve_prenom,date_naissance)

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array(
    'eleve_nom'      => $_SESSION['USER_NOM'],
    'eleve_prenom'   => $_SESSION['USER_PRENOM'],
    'eleve_genre'    => $_SESSION['USER_GENRE'],
    'date_naissance' => $_SESSION['USER_NAISSANCE_DATE'],
    'eleve_INE'      => NULL,
  );
}
elseif(empty($is_appreciation_groupe))
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
    'eleve_nom'      => '',
    'eleve_prenom'   => '',
    'eleve_genre'    => 'I',
    'date_naissance' => NULL,
    'eleve_INE'      => NULL,
  );
}
$eleve_nb = count( $tab_eleve_infos , COUNT_NORMAL );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des domaines et composantes du socle (indépendant du cycle sélectionné)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_socle_domaine    = array();  // [socle_domaine_id] => domaine_nom;
$tab_socle_composante = array();  // [socle_domaine_id][socle_composante_id] => composante_nom;

$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_arborescence();
$socle_domaine_id    = 0;
$socle_composante_id = 0;
foreach($DB_TAB as $DB_ROW)
{
  if( $DB_ROW['socle_domaine_id'] != $socle_domaine_id )
  {
    $socle_domaine_id  = $DB_ROW['socle_domaine_id'];
    $tab_socle_domaine[$socle_domaine_id] = $DB_ROW['socle_domaine_nom_simple'];
  }
  $DB_ROW['socle_composante_id']         = ( ($socle_detail=='detail') || ($socle_domaine_id==1) ) ? $DB_ROW['socle_composante_id']         : $socle_domaine_id*10 ;
  $DB_ROW['socle_composante_nom_simple'] = ( ($socle_detail=='detail') || ($socle_domaine_id==1) ) ? $DB_ROW['socle_composante_nom_simple'] : 'Toutes composantes confondues' ;
  $socle_composante_id = $DB_ROW['socle_composante_id'];
  $tab_socle_composante[$socle_domaine_id][$socle_composante_id] = $DB_ROW['socle_composante_nom_simple'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les seuils pour les degrés de maîtrise du livret
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$cycle_id = substr($PAGE_REF,-1);
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos('cycle'.$cycle_id);
foreach($DB_TAB as $DB_ROW)
{
  $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
  $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
  $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
  $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
}
$tab_archive['session']['LIVRET'] = $_SESSION['LIVRET']; // on maj du coup

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les codes et intitulés des domaines ou composantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubriques = array();
$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_elements_livret();
foreach($DB_TAB as $DB_ROW)
{
  if(!empty($DB_ROW['socle_domaine_code_livret']))
  {
    $tab_rubriques['socle'][$DB_ROW['socle_domaine_ordre_livret']] = array(
      'code'         => $DB_ROW['socle_domaine_code_livret'],
      'nom_simple'   => $DB_ROW['socle_domaine_nom_simple'],
      'nom_officiel' => $DB_ROW['socle_domaine_nom_officiel'],
    );
  }
  elseif(!empty($DB_ROW['socle_composante_code_livret']))
  {
    $tab_rubriques['socle'][$DB_ROW['socle_composante_ordre_livret']] = array(
      'code'         => $DB_ROW['socle_composante_code_livret'],
      'nom_simple'   => $DB_ROW['socle_composante_nom_simple'],
      'nom_officiel' => $DB_ROW['socle_composante_nom_officiel'],
    );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les enseignements complémentaires pour les élèves concernés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_enscompl( $liste_eleve );
foreach($DB_TAB as $DB_ROW)
{
  // 1 enseignement complémentaire au maximum par élève
  $tab_rubriques['enscompl'][$DB_ROW['eleve_id']] = array(
    'id'   => $DB_ROW['livret_enscompl_id'] ,
    'code' => $DB_ROW['livret_enscompl_code'] ,
    'nom'  => $DB_ROW['livret_enscompl_nom'] ,
  );
}

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
// Nombre de boucles par élève (entre 1 et 3 pour les bilans officiels, dans ce cas $tab_destinataires[] est déjà complété ; une seule dans les autres cas).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!isset($tab_destinataires))
{
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    $tab_destinataires[$eleve_id][0] = TRUE ;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du relevé de fin de cycle
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Pas de saisie pour la classe (donc $eleve_id obligé)
// Pas de graphique

// Permettre une édition des positionnements sur le socle au moment de la saisie de la synthèse
$acces_modif_positionnement = ( ($make_action=='modifier') || ( ($make_action=='tamponner') && ($BILAN_ETAT=='3mixte') && Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_POSITIONNER_SOCLE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) ) ) ? TRUE : FALSE ;

$tab_deja_affiche = array();

// Préparatifs
if($make_html)
{
  $bouton_print_test = isset($is_bouton_test_impression) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : '' ;
  $bouton_archivage  = ' <button id="archiver_imprimer" type="button" class="imprimer">Archiver / Imprimer des données</button>';
  $bouton_import_csv = ''; // Bilans périodiques du collège uniquement
  $releve_HTML = '<div class="ti">'.$bouton_archivage.$bouton_print_test.$bouton_import_csv.'</div>'.NL;
}

if($make_pdf)
{
  $releve_PDF = new PDF_livret_scolaire( TRUE /*make_officiel*/ , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende , !empty($is_test_impression) /*filigrane*/ );
  $releve_PDF->initialiser( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] , $tab_saisie_initialisation );
  $tab_archive['user'][0][] = array( '__construct' , array( TRUE /*make_officiel*/ , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , 'oui' /*couleur*/ , $fond , $legende , !empty($is_test_impression) /*filigrane*/ , $tab_archive['session'] ) );
}
// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  extract($tab_eleve);  // $eleve_INE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
  $date_naissance = ($date_naissance) ? To::date_mysql_to_french($date_naissance) : '' ;
  foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
  {
    $is_archive = ( ($numero_tirage==0) && empty($is_test_impression) ) ? TRUE : FALSE ;
    $is_memo_points = ($cycle_id==4) ? TRUE : FALSE ;
    // Intitulé
    if($make_pdf)
    {
      if($is_archive)
      {
        $tab_archive['user'][$eleve_id]['image_md5'] = array();
        $tab_archive['user'][$eleve_id][] = array( 'initialiser' , array( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] , $tab_saisie_initialisation ) );
      }
      $tab_infos_entete = 
        array(
          'tab_menesr_logo'          => $tab_menesr_logo ,
          'tab_etabl_coords'         => $tab_etabl_coords ,
          'tab_etabl_logo'           => $tab_etabl_logo ,
          'tab_bloc_titres'          => $tab_bloc_titres ,
          'tab_adresse'              => $tab_adresse ,
          'tag_date_heure_initiales' => $tag_date_heure_initiales ,
          'eleve_genre'              => $eleve_genre ,
          'date_naissance'           => $date_naissance ,
        ) ;
      $releve_PDF->entete( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , 0 /*nb_lignes_eleve_eval_total*/ );
      if($is_archive)
      {
        if(!empty($tab_etabl_logo))
        {
          // On remplace l'image par son md5
          $image_contenu = $tab_etabl_logo['contenu'];
          $image_md5     = md5($image_contenu);
          $tab_archive['image'][$image_md5] = $image_contenu;
          $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
          $tab_infos_entete['tab_etabl_logo']['contenu'] = $image_md5;
        }
        // Idem pour le logo du Menesr
        $image_contenu = $tab_menesr_logo['contenu'];
        $image_md5     = md5($image_contenu);
        $tab_archive['image'][$image_md5] = $image_contenu;
        $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
        $tab_infos_entete['tab_menesr_logo']['contenu'] = $image_md5;
        $tab_archive['user'][$eleve_id][] = array( 'entete' , array( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , 0 /*nb_lignes_eleve_eval_total*/ ) );
      }
      if($is_memo_points)
      {
        $tab_page_decompte_points = array(
          'titre' => $tab_eleve['eleve_nom'].' '.$tab_eleve['eleve_prenom'].' ('.$tab_bloc_titres[3].')',
          'tag'   => $tag_date_heure_initiales ,
        );
      }
    }

    // Maîtrise des composantes du socle
    if($make_pdf)
    {
      $tab_saisie_eleve_socle = isset($tab_saisie[$eleve_id]['socle']) ? $tab_saisie[$eleve_id]['socle'] : array();
      $tab_page_decompte_points['controle_continu'] = $releve_PDF->bloc_socle( $tab_rubriques['socle'] , $tab_saisie_eleve_socle , $is_memo_points )
                                                    +  array( 99 => array( 'Enseignement de complément' , FALSE , '(20)' ) );
      if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_socle' , array( $tab_rubriques['socle'] , $tab_saisie_eleve_socle , FALSE ) ); }
    }
    if($make_html)
    {
      $releve_HTML .= '<h4 class="eval">'.rubrique_texte_intro('socle',$cycle_id).'</h4>'.NL;
      $releve_HTML .= '<table class="livret"><thead>'.NL.'<tr>';
      $releve_HTML .= '<th class="nu"></th>';
      $releve_HTML .= '<th class="pos0">Dispensé</th>';
      foreach($_SESSION['LIVRET'] as $id => $tab)
      {
        $releve_HTML .= '<th class="pos'.$id.'" style="min-width:7em">'.str_replace(' ','<br />',str_replace('Très bonne','Très&nbsp;bonne',html($tab['LEGENDE']))).'<img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="de '.$tab['SEUIL_MIN'].' à '.$tab['SEUIL_MAX'].'" /></th>';
      }
      $releve_HTML .= '<th class="nu"></th>';
      $releve_HTML .= '</tr>';
      $releve_HTML .= '</thead>'.NL.'<tbody>'.NL;
      // On passe en revue les rubriques...
      foreach($tab_rubriques['socle'] as $livret_rubrique_id => $tab_rubrique)
      {
        // récup positionnement
        $id_rubrique_position = $livret_rubrique_id;
        $position_info = isset($tab_saisie[$eleve_id]['socle'][$id_rubrique_position]['position']) ? $tab_saisie[$eleve_id]['socle'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
        $details = ( $position_info['acquis_detail'] ) ? ' <a href="#" class="voir_detail" data-id="'.$livret_rubrique_id.'">[ détail travaillé ]</a><div id="detail_'.$livret_rubrique_id.'" class="hide">'.$position_info['acquis_detail'].'</div>' : '' ;
        if(!is_null($position_info['saisie_valeur']))
        {
          $pourcentage = $position_info['saisie_valeur'];
          $origine = ($position_info['saisie_origine']=='calcul') ? 'Calculé automatiquement' : 'Saisi par '.html($tab_profs[$position_info['prof_id']]) ;
          $actions = ( $eleve_id && $acces_modif_positionnement ) ? ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button> <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>' : '' ;
          $actions.= ( $acces_modif_positionnement && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
        }
        else
        {
          $pourcentage = FALSE ;
          $origine = ($position_info['saisie_origine']=='saisie') ? 'Supprimé par '.html($tab_profs[$position_info['prof_id']]) : '' ;
          $actions = ( $eleve_id && $acces_modif_positionnement ) ? ' <button type="button" class="ajouter" title="Ajouter le positionnement">&nbsp;</button>' : '' ;
          $actions.= ( $acces_modif_positionnement && ($position_info['saisie_origine']=='saisie') ) ? ' <button type="button" class="eclair" title="Re-générer le positionnement">&nbsp;</button>' : '' ;
        }
        $indice = ($pourcentage!=='disp') ? OutilBilan::determiner_degre_maitrise($pourcentage) : 0 ;
        $origine .= ( $origine && ($position_info['saisie_origine']=='calcul') ) ? ' : '.$pourcentage.' %' : '' ;
        $releve_HTML .= '<tr>';
        $releve_HTML .= '<td><b>'.html($tab_rubrique['nom_simple']).'</b>'.$details.'</td>';
        // Codage dispensé
        if($tab_rubrique['code']!='CPD_ETR')
        {
          $releve_HTML .= '<td class="nu"></td>';
        }
        else
        {
          $texte = ($pourcentage=='disp') ? '<b>X</b>' : '' ;
          $releve_HTML .= '<td id="socle_'.$livret_rubrique_id.'_position_0" class="pos0">'.$texte.'</td>';
        }
        foreach($_SESSION['LIVRET'] as $id => $tab)
        {
          $texte = ($id==$indice) ? '<b>X</b>' : '' ;
          $releve_HTML .= '<td id="socle_'.$livret_rubrique_id.'_position_'.$id.'" class="pos'.$id.'">'.$texte.'</td>';
        }
        $releve_HTML .= '<td id="socle_'.$livret_rubrique_id.'_position_'.$PAGE_COLONNE.'" class="nu"><div class="notnow" data-id="'.$position_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div><i>'.$indice.'</i></td>';
        $releve_HTML .= '</tr>'.NL;
      }
    }
    // Enseignement de complément
    if( ($cycle_id==4) && $eleve_id && isset($tab_rubriques['enscompl'][$eleve_id]) )
    {
      $tab_rubrique = $tab_rubriques['enscompl'][$eleve_id];
      $livret_rubrique_id = $tab_rubrique['id'];
      // récup positionnement
      $id_rubrique_position = $livret_rubrique_id;
      $position_info = isset($tab_saisie[$eleve_id]['enscompl'][$id_rubrique_position]['position']) ? $tab_saisie[$eleve_id]['enscompl'][$id_rubrique_position]['position'] : $tab_saisie_initialisation ;
      // pour les boucles
      $tab_enscompl_etat = array(
        3 => 'Objectif atteint',
        4 => 'Objectif dépassé',
      );
      // Sorties PDF & HTML
      if($make_pdf)
      {
        $tab_page_decompte_points['controle_continu'][99] = $releve_PDF->bloc_enscompl( $tab_rubrique['nom'] , $position_info , $is_memo_points );
        if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_enscompl' , array( $tab_rubrique['nom'] , $position_info, FALSE ) ); }
      }
      if($make_html)
      {
        $releve_HTML .= '<tr>';
        $releve_HTML .= '<th colspan="6" class="nu"><th class="nu"></th>';
        $releve_HTML .= '</tr>'.NL;
        $releve_HTML .= '<tr>';
        $releve_HTML .= '<th colspan="4" class="nu"></th>';
        foreach($tab_enscompl_etat as $id => $legende)
        {
          $releve_HTML .= '<th class="pos'.$id.'">'.str_replace(' ','<br />',$legende).'</th>';
        }
        $releve_HTML .= '<th class="nu"></th>';
        $releve_HTML .= '</tr>'.NL;
        if(!is_null($position_info['saisie_valeur']))
        {
          $pourcentage = $position_info['saisie_valeur'];
          $origine = 'Saisi par '.html($tab_profs[$position_info['prof_id']]); // Pas de prépositionnement automatique
          $actions = ( $eleve_id && $acces_modif_positionnement ) ? ' <button type="button" class="modifier" title="Modifier le positionnement">&nbsp;</button> <button type="button" class="supprimer" title="Supprimer le positionnement">&nbsp;</button>' : '' ;
        }
        else
        {
          $pourcentage = FALSE ;
          $origine = ($position_info['saisie_origine']=='saisie') ? 'Supprimé par '.html($tab_profs[$position_info['prof_id']]) : '' ;
          $actions = ( $eleve_id && $acces_modif_positionnement ) ? ' <button type="button" class="ajouter" title="Ajouter le positionnement">&nbsp;</button>' : '' ;
        }
        $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
        $origine .= ( $origine && ($position_info['saisie_origine']=='calcul') ) ? ' : '.$pourcentage.' %' : '' ;
        $releve_HTML .= '<tr>';
        $releve_HTML .= '<td colspan="4"><b>Enseignement de complément &rarr; '.html($tab_rubrique['nom']).'</b></td>';
        foreach($tab_enscompl_etat as $id => $legende)
        {
          $texte = ($id==$indice) ? '<b>X</b>' : '' ;
          $releve_HTML .= '<td id="enscompl_'.$livret_rubrique_id.'_position_'.$id.'" class="pos'.$id.'">'.$texte.'</td>';
        }
        $releve_HTML .= '<td id="enscompl_'.$livret_rubrique_id.'_position_'.$PAGE_COLONNE.'" class="nu"><div class="notnow" data-id="'.$position_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div><i>'.$indice.'</i></td>';
        $releve_HTML .= '</tr>'.NL;
      }
    }
    if($make_html)
    {
      $releve_HTML .= '</tbody></table>';
    }

    // Synthèse des acquis scolaires de l’élève en fin de cycle
    $bilan_info = isset($tab_saisie[$eleve_id]['bilan'][0]['appreciation']) ? $tab_saisie[$eleve_id]['bilan'][0]['appreciation'] : $tab_saisie_initialisation ;
    if($make_pdf)
    {
      $releve_PDF->bloc_bilan( $bilan_info['saisie_valeur'] , $texte_prof_principal );
      if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'bloc_bilan' , array( $bilan_info['saisie_valeur'] , $texte_prof_principal ) ); }
    }
    else if($make_html)
    {
      if( ($make_action=='tamponner') || ($make_action=='consulter') )
      {
        $titre = 
        $releve_HTML .= '<h4 class="bilan">'.Lang::_("Synthèse des acquis scolaires de l’élève en fin de cycle").' '.$cycle_id.'</h4>'.NL;
        $releve_HTML .= '<div class="bilan">'.NL;
        if($bilan_info['saisie_valeur'])
        {
          $appreciation = html($bilan_info['saisie_valeur']);
          $origine = ' Dernière saisie par '.html($tab_profs[$bilan_info['prof_id']]);
          $actions = ($make_action=='tamponner') ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
          if( ($make_action=='consulter') && in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')) && ($bilan_info['prof_id']!=$_SESSION['USER_ID']) )
          {
            $actions .= ' <button type="button" class="signaler">Signaler une faute</button>';
            if($droit_corriger_appreciation) { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
          }
        }
        else
        {
          $appreciation = ($BILAN_ETAT=='2rubrique') ? '<span class="astuce">Absence de saisie.</span>' : '<span class="danger">Absence de saisie !</span>' ;
          $origine = ($bilan_info['saisie_origine']=='saisie') ? ' Supprimé par '.html($tab_profs[$bilan_info['prof_id']]) : '' ;
          $actions = ($make_action=='tamponner') ? ' <button type="button" class="ajouter">Ajouter</button>' : '' ;
        }
        $releve_HTML .= '<div id="bilan_0_appreciation_'.$bilan_info['prof_id'].'">';
        $releve_HTML .=   '<span class="appreciation">'.$appreciation.'</span>';
        $releve_HTML .=   '<div class="notnow" data-id="'.$bilan_info['saisie_id'].'">'.echo_origine($origine).$actions.'</div>';
        $releve_HTML .= '</div>';
        $releve_HTML .= '</div>'.NL;
      }
    }

    // Communication avec la famille
    if($make_pdf)
    {
      $tab_enseignant = ($BILAN_TYPE_ETABL=='college') ? $tab_pp : $tab_profs ;
      $releve_PDF->bloc_cycle_signatures( $DATE_VERROU , $texte_chef_etabl , $tab_enseignant , $tab_signature['chef'] , $tab_signature['prof'] , $tab_parent_lecture );
      if($is_archive)
      {
        if(!empty($tab_signature['prof']))
        {
          // On remplace l'image par son md5
          $image_contenu_prof = $tab_signature['prof']['contenu'];
          $image_md5          = md5($image_contenu_prof);
          $tab_archive['image'][$image_md5] = $image_contenu_prof;
          $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
          $tab_signature['prof']['contenu'] = $image_md5;
        }
        if(!empty($tab_signature['chef']))
        {
          // On remplace l'image par son md5
          $image_contenu_chef = $tab_signature['chef']['contenu'];
          $image_md5          = md5($image_contenu_chef);
          $tab_archive['image'][$image_md5] = $image_contenu_chef;
          $tab_archive['user'][$eleve_id]['image_md5'][] = $image_md5;
          $tab_signature['chef']['contenu'] = $image_md5;
        }
        $tab_archive['user'][$eleve_id][] = array( 'bloc_cycle_signatures' , array( $DATE_VERROU , $texte_chef_etabl , $tab_enseignant , $tab_signature['chef'] , $tab_signature['prof'] , $tab_parent_lecture ) );
        if(!empty($tab_signature['prof']))
        {
          // On remet la bonne image pour les tirages suivants
          $tab_signature['prof']['contenu'] = $image_contenu_prof;
        }
        if(!empty($tab_signature['chef']))
        {
          // On remet la bonne image pour les tirages suivants
          $tab_signature['chef']['contenu'] = $image_contenu_chef;
        }
      }
    }

    // Indication en 2ème page du nombre de points
    if( $make_pdf && $is_memo_points )
    {
      $releve_PDF->page_decompte_points( $tab_page_decompte_points , $annee_siecle );
      if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'page_decompte_points' , array( $tab_page_decompte_points , $annee_siecle ) ); }
    }

    if( $make_html )
    {
      $releve_HTML .= '<p />'.NL;
    }
    // Professeurs principaux
    if( ($affichage_prof_principal) && $make_html )
    {
      $releve_HTML .= '<div class="i">'.$texte_prof_principal.'</div>'.NL;
    }
    // Date de naissance
    if( ($date_naissance) && $make_html )
    {
      $releve_HTML .= '<div class="i">'.To::texte_ligne_naissance($date_naissance).'</div>'.NL;
    }
    if( $make_html )
    {
      $releve_HTML .= '<p>&nbsp;</p>'.NL;
    }
    // Indiquer a posteriori le nombre de pages par élève
    if($make_pdf)
    {
      $page_nb = $releve_PDF->reporter_page_nb();
      if($is_archive){ $tab_archive['user'][$eleve_id][] = array( 'reporter_page_nb' , array() ); }
    }
    // Mémorisation des pages de début et de fin pour chaque élève pour découpe et archivage ultérieur
    if($make_action=='imprimer')
    {
      $page_debut  = (isset($page_fin)) ? $page_fin+1 : 1 ;
      $page_fin    = $releve_PDF->page;
      $page_nombre = $page_fin - $page_debut + 1;
      $tab_pages_decoupe_pdf[$eleve_id][$numero_tirage] = array( $eleve_nom.' '.$eleve_prenom , $page_debut.'-'.$page_fin , $page_nombre );
    }

  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On enregistre les sorties HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.html' , $releve_HTML ); }
if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf'  , $releve_PDF  ); }

?>