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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Édition du livret"));

$tab_puce_info = array();

$tab_periode_livret = array(
  'periode21' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 1/2' ),
  'periode22' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 2/2' ),
  'periode31' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 1/3'),
  'periode32' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 2/3'),
  'periode33' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 3/3'),
  'periode41' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 1/4' ),
  'periode42' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 2/4' ),
  'periode43' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 3/4' ),
  'periode44' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 4/4' ),
  'periode51' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 1/5'  ),
  'periode52' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 2/5'  ),
  'periode53' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 3/5'  ),
  'periode54' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 4/5'  ),
  'periode55' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 5/5'  ),
  'cycle'     => array( 'used' => FALSE , 'defined' => TRUE  , 'dates' => TRUE  , 'nom' => 'Fin de cycle' ),
);

$tab_etats = array
(
  '1vide'     => 'Vide (fermé)',
  '2rubrique' => 'Saisies Profs',
  '3mixte'    => 'Saisies Mixtes',
  '4synthese' => 'Saisie Synthèse',
  '5complet'  => 'Complet (fermé)',
);

// Indication des profils pouvant modifier le statut d'un bilan
$profils_modifier_statut = 'administrateurs (de l\'établissement)<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_MODIFIER_STATUT'],'br');
// Indication des profils ayant accès à l'édition de la maîtrise des composantes du socle
$profils_positionner_socle = Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_POSITIONNER_SOCLE'],'br');
// Indication des profils ayant accès à l'appréciation générale
$profils_appreciation_generale = Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_APPRECIATION_GENERALE'],'br');
// Indication des profils ayant accès à l'impression PDF
$profils_impression_pdf = 'administrateurs (de l\'établissement)<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF'],'br');
// Indication des profils ayant accès aux copies des impressions PDF
$profils_archives_pdf = 'administrateurs (de l\'établissement)<br />'.Outil::afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE'],'br');

// Droit de modifier le statut d'un bilan (dans le cas PP, restera à affiner classe par classe...).
$affichage_formulaire_statut = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_MODIFIER_STATUT']) ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération et traitement des données postées, si formulaire soumis
// Pas de passage par la page ajax.php => protection contre attaques type CSRF ajoutée ici
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($affichage_formulaire_statut) && ($_SESSION['SESAMATH_ID']!=ID_DEMO) )
{
  $tab_ids  = (isset($_POST['listing_ids']))  ? explode(',',$_POST['listing_ids']) : array() ;
  $new_etat = (isset($_POST['etat']))         ? Clean::texte($_POST['etat'])       : '' ;
  $discret  = (isset($_POST['mode_discret'])) ? TRUE                               : FALSE ;
  if( count($tab_ids) && isset($tab_etats[$new_etat]) )
  {
    Session::verifier_jeton_anti_CSRF($PAGE);
    // Concernant les notifications, on liste déjà s'il y a des utilisateurs qui s'y seraient abonnés
    $abonnement_ref = 'bilan_officiel_statut';
    $abonnes_nb = 0;
    if( !$discret && in_array($new_etat,array('2rubrique','3mixte','4synthese')) )
    {
      $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_avec_informations( $abonnement_ref );
      $abonnes_nb = count($DB_TAB);
      if($abonnes_nb)
      {
        $tab_abonnes = array();
        $tab_profils = array();
        // On récupère les infos au passage
        foreach($DB_TAB as $DB_ROW)
        {
          $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
          $tab_abonnes[$DB_ROW['user_id']] = array(
            'statut'   => $notification_statut,
            'mailto'   => $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>',
            'courriel' => $DB_ROW['user_email'],
            'contenu'  => '',
          );
          $tab_profils[$DB_ROW['user_profil_type']][] = $DB_ROW['user_id'];
        }
        // Récupération du nom des classes (sans fignoler)
        $tab_classes = array();
        $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_classes_noms();
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_classes[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
        }
        // Récupération des profs ou directeurs par classe
        $tab_profs_par_classe = array();
        if(!empty($tab_profils['directeur']))
        {
          // Les directeurs sont rattachés à toutes les classes
          foreach($tab_classes as $classe_id => $classe_nom)
          {
            $tab_profs_par_classe[$classe_id] = $tab_profils['directeur'];
          }
        }
        if(!empty($tab_profils['professeur']))
        {
          // Les professeurs ne sont rattachés qu'à certaines classes
          $listing_profs_id   = implode(',',$tab_profils['professeur']);
          $listing_groupes_id = implode(',',array_keys($tab_classes));
          $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_jointure_professeurs_groupes($listing_profs_id,$listing_groupes_id);
          foreach($DB_TAB as $DB_ROW)
          {
            $tab_profs_par_classe[$DB_ROW['groupe_id']][] = $DB_ROW['user_id'];
          }
        }
      }
    }
    // On passe au traitement des données reçues
    $auteur = To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
    foreach($tab_ids as $ids)
    {
      list( , $classe_id , $page_ref , $periode ) = explode('X',str_replace(array('G','R','P'),'X',$ids));
      if( (int)$classe_id && isset($tab_periode_livret[$periode]) )
      {
        if(substr($periode,0,7)=='periode')
        {
          $page_periodicite = 'periode';
          $jointure_periode = substr($periode,7);
        }
        else
        {
          $page_periodicite = $periode;
          $jointure_periode = NULL;
        }
        $is_modif = DB_STRUCTURE_LIVRET::DB_modifier_jointure_groupe( $classe_id , $page_ref , $page_periodicite , $jointure_periode , $new_etat );
        if( $is_modif && $abonnes_nb && isset($tab_profs_par_classe[$classe_id]) )
        {
          $texte = 'Statut ['.$tab_etats[$new_etat].'] appliqué par '.$auteur.' à [Livret scolaire] ['.$tab_periode_livret[$periode]['nom'].'] ['.$tab_classes[$classe_id].'].'."\r\n";
          foreach($tab_profs_par_classe[$classe_id] as $user_id)
          {
            $tab_abonnes[$user_id]['contenu'] .= $texte;
          }
        }
      }
    }
    // On termine par le log et l'envoi des notifications
    if($abonnes_nb)
    {
      foreach($tab_abonnes as $user_id => $tab)
      {
        if($tab['contenu'])
        {
          DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_visible( $user_id , $abonnement_ref , $tab['statut'] , $tab['contenu'] );
          if($tab['statut']=='envoyée')
          {
            $tab['contenu'] .= Sesamail::texte_pied_courriel( array('no_reply','notif_individuelle','signature') , $tab['courriel'] );
            $courriel_bilan = Sesamail::mail( $tab['mailto'] , 'Notification - Bilan officiel, étape de saisie' , $tab['contenu'] , NULL );
          }
        }
      }
    }
  }
}

// Pour variables js POURCENTAGE_MAXI & MOYENNE_MAXI
$valeur_maxi = 0;
foreach( $_SESSION['NOTE'] as $note_id => $tab_note_info )
{
  $valeur_maxi = ($tab_note_info['ACTIF']) ? max($valeur_maxi,$tab_note_info['VALEUR']) : $valeur_maxi ;
}

// Javascript
Layout::add( 'js_inline_before' , 'var USER_ID               = '.$_SESSION['USER_ID'].';' );
Layout::add( 'js_inline_before' , 'var TODAY_FR              = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var URL_IMPORT            = "'.URL_DIR_IMPORT.'";' );
Layout::add( 'js_inline_before' , 'var POURCENTAGE_MAXI      = '.$valeur_maxi.';' );
Layout::add( 'js_inline_before' , 'var MOYENNE_MAXI          = '.($valeur_maxi/5).';' );

// Alerte initialisation annuelle non effectuée (test !empty() car un passage par la page d'accueil n'est pas obligatoire)
if(!empty($_SESSION['NB_DEVOIRS_ANTERIEURS']))
{
  $tab_puce_info[] = '<li><span class="probleme">Année scolaire précédente non archivée&nbsp;!<br />Au changement d\'année scolaire un administrateur doit <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.<br />Ne poursuivez pas tant que cela n\'est pas fait&nbsp;!</span></li>';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des jointures livret / classes / périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_jointures_classes_livret();
if(empty($DB_TAB))
{
  echo'<p><label class="erreur">Aucune association de classe au livret scolaire enregistrée !</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
$tab_page_ref = array();
$tab_join_classe_periode = array();
foreach($DB_TAB as $DB_ROW)
{
  // La requête ne peut restreindre aux jointures classe/période renseignées à cause des bilans de cycles, donc il faut aussi vérifier les dates.
  if( ($DB_ROW['livret_page_periodicite']=='cycle') || ( !is_null($DB_ROW['jointure_date_debut']) && !is_null($DB_ROW['jointure_date_fin']) ) )
  {
    $periode = $DB_ROW['livret_page_periodicite'].$DB_ROW['jointure_periode'];
    $tab_periode_livret[$periode]['used'] = TRUE;
    if($DB_ROW['periode_id'])
    {
      $tab_periode_livret[$periode]['defined'] = TRUE;
    }
    if( $DB_ROW['jointure_date_debut'] && $DB_ROW['jointure_date_fin'] )
    {
      $tab_periode_livret[$periode]['dates'] = TRUE;
    }
    $tab_join_classe_periode[$DB_ROW['groupe_id']][$periode] = array(
      'page_ref'      => $DB_ROW['livret_page_ref'],
      'etat'          => $DB_ROW['jointure_etat'],
      'rubrique_type' => $DB_ROW['livret_page_rubrique_type'],
      'periode_id'    => $DB_ROW['periode_id'],
      'date_debut'    => $DB_ROW['jointure_date_debut'],
      'date_fin'      => $DB_ROW['jointure_date_fin'],
    );
    $tab_page_ref[] = $DB_ROW['livret_page_ref'];
  }
}
$tab_periode_pb = array( 'undefined' => array() , 'pbdates' => 0 );
foreach($tab_periode_livret as $periode => $tab)
{
  if(!$tab['used'])
  {
    unset($tab_periode_livret[$periode]);
  }
  else if(!$tab['defined'])
  {
    $tab_periode_pb['undefined'][] = $tab['nom'];
  }
  else if(!$tab['dates'])
  {
    $tab_periode_pb['pbdates']++;
  }
}
if(!empty($tab_periode_pb['undefined']))
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode">Paramétrer les périodes</a> et/ou <a href="./index.php?page=livret&amp;section=classes">Choisir une autre périodicité</a>.' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_gestion_periodes">paramétrer les périodes</a></span> et/ou <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__livret_scolaire_administration#toggle_classes">choisir une autre périodicité</a></span>.' ;
  echo'<p><label class="erreur">Désignation des périodes pour le livret scolaire non effectuée pour "'.implode(' + ',$tab_periode_pb['undefined']).'" !'.$consigne.'</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
if($tab_periode_pb['pbdates'])
{
  $s = ( $tab_periode_pb['pbdates'] > 1 ) ? 's' : '' ;
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode&section=classe_groupe">Effectuer les associations.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_affecter_periodes">effectuer les associations</a></span>.' ;
  echo'<p><label class="erreur">Association datée des périodes aux classes non effectuée pour '.$tab_periode_pb['pbdates'].' période'.$s.' !'.$consigne.'</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Besoin de connaitre le chef d'établissement
if( count( array_intersect( $tab_page_ref , array('6e','5e','4e','3e','cycle1','cycle2','cycle3','cycle4') ) ) )
{
  $listing_groupe = DB_STRUCTURE_LIVRET::DB_lister_classes_livret_sans_chef();
  if(!empty($listing_groupe))
  {
    $nb_classes_concernees = substr_count($listing_groupe,',')+1;
    $s = ($nb_classes_concernees>1) ? 's' : '' ;
    $consigne = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || ($_SESSION['USER_PROFIL_TYPE']=='directeur') ) ? ' <a href="./index.php?page=livret&amp;section=classes">Renseigner cette information.</a>' : '<br />Un administrateur ou un directeur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__livret_scolaire_administration#toggle_classes">configurer un responsable par classe</a></span>.' ;
    echo'<p><label class="erreur">Chef d\'établissement ou directeur d\'école non désigné pour '.$nb_classes_concernees.' classe'.$s.' !'.$consigne.'</label></p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_edition">DOC : Bilan officiel &rarr; Livret Scolaire</a></span></li>
  <?php echo implode('',$tab_puce_info); ?>
  <li><span class="astuce"><?php echo($affichage_formulaire_statut) ? 'Vous pouvez utiliser l\'outil d\'<a href="./index.php?page=compte_message">affichage de messages en page d\'accueil</a> pour informer les professeurs de l\'ouverture à la saisie.' : '<a title="'.$profils_modifier_statut.'" href="#">Profils pouvant modifier le statut d\'un bilan.</a>' ; ?></span></li>
</ul>

<div id="cadre_photo"><button id="voir_photo" type="button" class="voir_photo">Photo</button></div>

<hr />

<?php

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des classes de l'établissement.
// Utile pour les profils administrateurs / directeurs, et requis concernant les professeurs pour une recherche s'il est affecté à des groupes.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl( FALSE /*with_ref*/ , 'livret' /*with_configuration*/ );

$tab_classe_etabl = array(); // tableau temporaire avec les noms des classes de l'établissement
if(is_array($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_classe_etabl[$DB_ROW['valeur']] = array( 'nom' => $DB_ROW['texte'] , 'config' => $DB_ROW['configuration_ref'] );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des classes accessibles à l'utilisateur.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'appréciation générale.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'impression PDF.
//
// Pour les administrateurs et les directeurs, ce sont les classes de l'établissement.
// Mais attention, les bilans ne sont définis que sur les classes, pas sur des groupes (car il ne peut y avoir qu'un type de bilan par élève / période).
// Alors quand les professeurs sont associés à des groupes, il faut chercher de quelle(s) classe(s) proviennent les élèves et proposer autant de choix partiels... sur ces classes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Javascript : tableau utilisé pour mémoriser la référence de la configuration de chaque classe
Layout::add( 'js_inline_before' , 'var tab_classe_config_ref = new Array();' );

$tab_classe = array(); // tableau important avec les droits [classe_id][0|groupe_id]
$tab_groupe = array(); // tableau temporaire avec les noms des groupes du prof
$tab_config = array(); // tableau avec les configurations de bilans à récolter, depuis que chaque classe peut avoir sa propre configuration
$tab_options_classes = array(); // Pour un futur formulaire select

// Préparation du tableau avec les cellules à afficher
$tab_affich = array(); // [classe_id_groupe_id][periode_id] (ligne colonne) ; les indices [check] sont ceux des checkbox multiples ; les indices [title] sont ceux des intitulés
$tab_affich['check']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['check']['title'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['title'] = '<td class="nu"></td>' ;
foreach($tab_periode_livret as $periode => $tab)
{
  $tab_affich['check'][$periode] = ($affichage_formulaire_statut) ? '<th class="nu"><q id="id_fin1_P'.$periode.'" class="cocher_tout" title="Tout cocher."></q><q id="id_fin2_P'.$periode.'" class="cocher_rien" title="Tout décocher."></q></th>' : '' ;
  $tab_affich['title'][$periode] = '<th class="hc" id="periode_'.$periode.'">'.html($tab['nom']).'</th>' ;
}

if($_SESSION['USER_PROFIL_TYPE']!='professeur') // administrateur | directeur
{
  $droit_modifier_statut       = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_MODIFIER_STATUT'])       );
  $droit_appreciation_generale = ( ($_SESSION['USER_PROFIL_TYPE']=='directeur')      && Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_APPRECIATION_GENERALE']) );
  $droit_positionner_socle     = ( ($_SESSION['USER_PROFIL_TYPE']=='directeur')      && Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_POSITIONNER_SOCLE'])     );
  $droit_impression_pdf        = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF'])        );
  $droit_voir_archives_pdf     = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || Outil::test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE'])          );
  foreach($tab_classe_etabl as $classe_id => $tab)
  {
    $tab_classe[$classe_id][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_positionner_socle' , 'droit_impression_pdf' , 'droit_voir_archives_pdf' );
    $tab_config[$classe_id] = $tab['config'];
    $tab_affich[$classe_id.'_0']['check'] = '<th class="nu"><q id="id_deb1_G'.$classe_id.'R" class="cocher_tout" title="Tout cocher."></q><q id="id_deb2_G'.$classe_id.'R" class="cocher_rien" title="Tout décocher."></q></th>' ;
    $tab_affich[$classe_id.'_0']['title'] = '<th id="groupe_'.$classe_id.'_0">'.html($tab['nom']).'</th>' ;
    $tab_options_classes[$classe_id.'_0'] = '<option value="'.$classe_id.'_0">'.html($tab['nom']).'</option>';
    Layout::add( 'js_inline_before' , 'tab_classe_config_ref["'.$classe_id.'"] = "'.$tab_config[$classe_id].'";' );
  }
}
else // professeur
{
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  foreach($DB_TAB as $DB_ROW)
  {
    $droit_voir_archives_pdf     = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']);
    if($DB_ROW['groupe_type']=='classe')
    {
      // Pour les classes, RAS
      $classe_id = $DB_ROW['groupe_id'];
      $droit_modifier_statut       = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_MODIFIER_STATUT']       , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_appreciation_generale = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_APPRECIATION_GENERALE'] , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_positionner_socle     = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_POSITIONNER_SOCLE']     , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_impression_pdf        = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF']        , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $tab_classe[$classe_id][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_positionner_socle' , 'droit_impression_pdf' );
      $tab_config[$classe_id] = $tab_classe_etabl[$classe_id]['config'];
      $tab_affich[$classe_id.'_0']['check'] = ($affichage_formulaire_statut) ? ( ($droit_modifier_statut) ? '<th class="nu"><q id="id_deb1_G'.$classe_id.'R" class="cocher_tout" title="Tout cocher."></q><q id="id_deb2_G'.$classe_id.'R" class="cocher_rien" title="Tout décocher."></q></th>' : '<th class="nu"></th>' ) : '' ;
      $tab_affich[$classe_id.'_0']['title'] = '<th id="groupe_'.$classe_id.'_0">'.html($DB_ROW['groupe_nom']).'</th>' ;
      $tab_options_classes[$classe_id.'_0'] = '<option value="'.$classe_id.'_0">'.html($DB_ROW['groupe_nom']).'</option>';
      Layout::add( 'js_inline_before' , 'tab_classe_config_ref["'.$classe_id.'"] = "'.$tab_config[$classe_id].'";' );
    }
    else
    {
      // Pour les groupes, il faudra récupérer les classes dont sont issues les élèves
      $tab_groupe[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
    }
  }
  if(count($tab_groupe))
  {
    // On récupère les classes dont sont issues les élèves des groupes et on complète $tab_classe
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_eleves_from_groupes( implode(',',array_keys($tab_groupe)) );
    foreach($tab_groupe as $groupe_id => $groupe_nom)
    {
      if(isset($DB_TAB[$groupe_id]))
      {
        foreach($DB_TAB[$groupe_id] as $tab)
        {
          $classe_id = $tab['eleve_classe_id'];
          $droit_modifier_statut       = FALSE ;
          $droit_appreciation_generale = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_APPRECIATION_GENERALE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
          $droit_positionner_socle     = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_POSITIONNER_SOCLE']     , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
          $droit_impression_pdf        = Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_LIVRET_IMPRESSION_PDF']        , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
          $tab_classe[$classe_id][$groupe_id] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_positionner_socle' , 'droit_impression_pdf' );
          $tab_config[$classe_id] = $tab_classe_etabl[$classe_id]['config'];
          $tab_affich[$classe_id.'_'.$groupe_id]['check'] =  ($affichage_formulaire_statut) ? '<th class="nu"></th>' : '' ;
          $tab_affich[$classe_id.'_'.$groupe_id]['title'] = '<th id="groupe_'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id]['nom']).'<br />'.html($groupe_nom).'</th>' ;
          $tab_options_classes[$classe_id.'_'.$groupe_id] = '<option value="'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id]['nom'].' - '.$groupe_nom).'</option>';
          Layout::add( 'js_inline_before' , 'tab_classe_config_ref["'.$classe_id.'"] = "'.$tab_config[$classe_id].'";' );
        }
      }
    }
  }
}

if(!count($tab_classe))
{
  echo'<p><label class="erreur">Aucune classe ni aucun groupe associé à votre compte !</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les infos utiles par rapport à la configuration des bilans (soit pour élaborer cette page, soit ensuite en js).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Javascript : tableau utilisé pour mémoriser les configurations des bilans
Layout::add( 'js_inline_before' , 'var tab_config = new Array();' );

$tab_config_ref = array_unique($tab_config);
foreach($tab_config_ref as $config_ref)
{
  $tab_configuration = DB_STRUCTURE_OFFICIEL_CONFIG::DB_recuperer_configuration( 'livret' , $config_ref );
  Layout::add( 'js_inline_before' , 'tab_config["'.$config_ref.'"] = new Array();' );
  Layout::add( 'js_inline_before' , 'tab_config["'.$config_ref.'"]["APP_RUBRIQUE_LONGUEUR"] = '.$tab_configuration['appreciation_rubrique_longueur'].';' );
  Layout::add( 'js_inline_before' , 'tab_config["'.$config_ref.'"]["APP_GENERALE_LONGUEUR"] = '.$tab_configuration['appreciation_generale_longueur'].';' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Renseigner le contenu des jointures classes / périodes.
// Pour les groupes, on prend les dates de classes dont les élèves sont issus.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_modif_rubrique = array
(
  'c1_theme'   => 'le positionnement et les appréciations par rubrique',
  'c2_domaine' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
  'c2_socle'   => 'les degrés de maîtrise des composantes du socle',
  'c3_domaine' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
  'c3_matiere' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
  'c3_socle'   => 'les degrés de maîtrise des composantes du socle',
  'c4_matiere' => 'les éléments de programme, les appréciations et le positionnement par rubrique',
  'c4_socle'   => 'les degrés de maîtrise des composantes du socle',
);

// Javascript : tableau utilisé pour désactiver des options d'un select.
Layout::add( 'js_inline_before' , 'var tab_disabled = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_bilan_page_ref = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["examiner"] = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["imprimer"] = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["voir_pdf"] = new Array();' );

$listing_classes_id = implode(',',array_keys($tab_classe));
$DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_jointure_groupe_periode($listing_classes_id);
foreach($tab_classe as $classe_id => $tab)
{
  foreach($tab_periode_livret as $periode => $tab)
  {
    $tab_join = isset($tab_join_classe_periode[$classe_id][$periode]) ? $tab_join_classe_periode[$classe_id][$periode] : NULL ;
    if($tab_join)
    {
      $etat = $tab_join['etat'];
      $affich_dates = (substr($periode,0,7)=='periode') ? To::date_mysql_to_french($tab_join['date_debut']).' ~ '.To::date_mysql_to_french($tab_join['date_fin']).'<br />' : '' ;
      $affich_etat  = '<span class="off_etat '.substr($etat,1).'"><span>'.$tab_etats[$etat].'</span></span>';
      // images action : vérification
      if($etat=='2rubrique')
      {
        $icone_verification = (substr($tab_join['rubrique_type'],3)=='matiere') ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet pour ce document."></q>' ;
      }
      elseif(in_array($etat,array('3mixte','4synthese')))
      {
        $icone_verification = (substr($tab_join['rubrique_type'],3)=='matiere') ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet pour ce document."></q>' ;
      }
      else
      {
        $icone_verification = '<q class="detailler_non" title="La recherche de saisies manquantes est sans objet lorsque l\'accès en saisie est fermé."></q>';
      }
      // images action : consultation contenu en cours d'élaboration (bilans HTML)
      if($etat=='1vide')
      {
        $icone_voir_html = '<q class="voir_non" title="Consultation du contenu sans objet (document déclaré vide)."></q>';
      }
      elseif($etat=='5complet')
      {
        $icone_voir_html = '<q class="voir_non" title="Consultation du contenu inopportun (document finalisé : utiliser les archives PDF)."></q>';
      }
      else
      {
        $icone_voir_html = '<q class="voir" title="Consulter le contenu (format HTML)."></q>';
      }
      // images action : consultation contenu finalisé (bilans PDF)
      if(!$droit_voir_archives_pdf)
      {
        $icone_voir_pdf = '<q class="voir_archive_non" title="Accès restreint aux copies des impressions PDF :<br />'.$profils_archives_pdf.'."></q>';
      }
      elseif($etat!='5complet')
      {
        $icone_voir_pdf = '<q class="voir_archive_non" title="Consultation du document imprimé sans objet (document déclaré non finalisé)."></q>';
      }
      else
      {
        $icone_voir_pdf = '<q class="voir_archive" title="Consulter une copie du document imprimé finalisé (format PDF)."></q>';
      }
    }
    // Il n'y a pas que la ligne de la classe, il y a les lignes des groupes dont des élèves font partie de la classe
    // Les images action de saisie et d'impression dépendent du groupe
    foreach($tab_classe[$classe_id] as $groupe_id=> $tab_droits)
    {
      if($tab_join)
      {
        $page_ref = $tab_join['page_ref'];
        // checkbox de gestion
        if( ($affichage_formulaire_statut) && ($tab_droits['droit_modifier_statut']) )
        {
          $id = 'G'.$classe_id.'R'.$page_ref.'P'.$periode;
          $label_avant = '<label for="'.$id.'">' ;
          $checkbox    = ' <input id="'.$id.'" name="'.$id.'" type="checkbox" />';
          $label_apres = '</label>' ;
        }
        else
        {
          $label_avant = $checkbox = $label_apres = '' ;
        }
        // images action : saisie
        if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
        {
          if(in_array($etat,array('2rubrique','3mixte')))
          {
            if($periode=='cycle')
            {
              $icone_saisie = ($tab_droits['droit_positionner_socle']) ? '<q class="modifier" title="Renseigner '.$tab_modif_rubrique[$tab_join['rubrique_type']].'."></q>' : '<q class="modifier_non" title="Accès restreint à la maîtrise des composantes du socle :<br />'.$profils_positionner_socle.'."></q>' ;
            }
            else
            {
              $icone_saisie = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<q class="modifier" title="Renseigner '.$tab_modif_rubrique[$tab_join['rubrique_type']].'."></q>' : '<q class="modifier_non" title="Accès réservé aux professeurs."></q>' ;
            }
          }
          else
          {
            $icone_saisie = '<q class="modifier_non" title="Accès fermé aux saisies intermédiaires."></q>';
          }
        }
        else
        {
          $icone_saisie = '';
        }
        // images action : tamponner
        if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
        {
          if( $page_ref=='cycle1' )
          {
            $icone_tampon = '<q class="tamponner_non" title="Sans objet pour ce bilan (pas de synthèse à saisir)."></q>';
          }
          else if(in_array($etat,array('3mixte','4synthese')))
          {
            $icone_tampon = ($tab_droits['droit_appreciation_generale']) ? '<q class="tamponner" title="Saisir l\'appréciation générale."></q>' : '<q class="tamponner_non" title="Accès restreint à la saisie de l\'appréciation générale :<br />'.$profils_appreciation_generale.'."></q>' ;
          }
          else
          {
            $icone_tampon = '<q class="tamponner_non" title="Accès fermé à la saisie de synthèse."></q>';
          }
        }
        else
        {
          $icone_tampon = '';
        }
        // images action : impression
        if($tab_droits['droit_impression_pdf'])
        {
          $icone_impression = ($etat=='5complet') ? '<q class="imprimer" title="Imprimer le bilan (PDF)."></q>' : '<q class="imprimer_non" title="L\'impression est possible une fois le bilan déclaré complet."></q>' ;
        }
        else
        {
          $icone_impression = '<q class="imprimer_non" title="Accès restreint à l\'impression PDF :<br />'.$profils_impression_pdf.'."></q>';
        }
        $tab_affich[$classe_id.'_'.$groupe_id][$periode] = '<td id="cgrp_'.$classe_id.'_'.$groupe_id.'_'.$page_ref.'_'.$periode.'" class="hc notnow">'.$label_avant.$affich_dates.$affich_etat.$checkbox.$label_apres.'<br />'.$icone_saisie.$icone_tampon.$icone_verification.$icone_voir_html.$icone_impression.$icone_voir_pdf.'</td>';
        // tableau javascript pour desactiver ce qui est inaccessible
        $disabled_examiner = strpos($icone_verification,'detailler_non') ? 'true' : 'false' ;
        $disabled_imprimer = strpos($icone_impression  ,'imprimer_non')  ? 'true' : 'false' ;
        $disabled_voir_pdf = strpos($icone_voir_pdf    ,'archive_non')   ? 'true' : 'false' ;
        Layout::add( 'js_inline_before' , 'tab_disabled["examiner"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]='.$disabled_examiner.';' );
        Layout::add( 'js_inline_before' , 'tab_disabled["imprimer"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]='.$disabled_imprimer.';' );
        Layout::add( 'js_inline_before' , 'tab_disabled["voir_pdf"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]='.$disabled_voir_pdf.';' );
        Layout::add( 'js_inline_before' , 'tab_bilan_page_ref["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]="'.$page_ref.'";' );
      }
      else
      {
        $tab_affich[$classe_id.'_'.$groupe_id][$periode] = '<td class="hc">-</td>';
        Layout::add( 'js_inline_before' , 'tab_disabled["examiner"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]=true;' );
        Layout::add( 'js_inline_before' , 'tab_disabled["imprimer"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]=true;' );
        Layout::add( 'js_inline_before' , 'tab_disabled["voir_pdf"]["'.$classe_id.'_'.$groupe_id.'_'.$periode.'"]=true;' );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du tableau.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'<table id="table_accueil"><thead>'.NL;
foreach($tab_affich as $ligne_id => $tab_colonne)
{
  echo ( ($ligne_id!='check') || ($affichage_formulaire_statut) ) ? '<tr>'.implode('',$tab_colonne).'</tr>'.NL : '' ;
  echo ($ligne_id=='title') ? '</thead><tbody>'.NL : '' ;
}
echo'</tbody></table>'.NL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du formulaire pour modifier les états d'accès.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($affichage_formulaire_statut)
{
  $tab_radio = array();
  foreach($tab_etats as $etat_id => $etat_text)
  {
    $tab_radio[] = '<label for="etat_'.$etat_id.'"><input id="etat_'.$etat_id.'" name="etat" type="radio" value="'.$etat_id.'" /> <span class="off_etat '.substr($etat_id,1).'"><span>'.$etat_text.'</span></span></label>';
  }
  echo'
    <form action="#" method="post" id="cadre_statut">
      <h3>Accès / Statut : <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour les cases cochées du tableau (classes uniquement)." /></h3>
      <div>'.implode('</div><div>',$tab_radio).'</div>
      <p><label for="mode_discret"><input id="mode_discret" name="mode_discret" type="checkbox" value="1" /> Mode discret <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Cocher pour éviter l\'envoi de notifications aux abonnés." /></label></p>
      <p><input id="listing_ids" name="listing_ids" type="hidden" value="" /><input id="csrf" name="csrf" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider</button><label id="ajax_msg_gestion">&nbsp;</label></p>
    </form>
  ';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire de choix des enseignements pour une recherche de saisies manquantes. -> zone_chx_rubriques
// Paramètres supplémentaires envoyés pour éviter d'avoir à les retrouver à chaque fois. -> form_hidden
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$form_hidden = '';
$tab_checkbox_rubriques = array();
// Lister les matières rattachées au prof
$listing_matieres_id = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? DB_STRUCTURE_COMMUN::DB_recuperer_matieres_professeur($_SESSION['USER_ID']) : '' ;
$form_hidden .= '<input type="hidden" id="f_listing_matieres" name="f_listing_matieres" value="'.$listing_matieres_id.'" />';
$tab_matieres_id = explode(',',$listing_matieres_id);
// Lister les matières du livret
$DB_TAB = array_merge
(
  DB_STRUCTURE_LIVRET::DB_lister_rubriques( 'c3_matiere' , TRUE /*for_edition*/ ) ,
  DB_STRUCTURE_LIVRET::DB_lister_rubriques( 'c4_matiere' , TRUE /*for_edition*/ )
);
foreach($DB_TAB as $DB_ROW)
{
  $checked = ( ($_SESSION['USER_PROFIL_TYPE']!='professeur') || in_array($DB_ROW['livret_rubrique_id'],$tab_matieres_id) ) ? ' checked' : '' ;
  $tab_checkbox_rubriques[$DB_ROW['livret_rubrique_id']] = '<label for="eval_'.$DB_ROW['livret_rubrique_id'].'"><input type="checkbox" name="f_rubrique[]" id="eval_'.$DB_ROW['livret_rubrique_id'].'" value="eval_'.$DB_ROW['livret_rubrique_id'].'"'.$checked.' /> '.html($DB_ROW['rubrique']).'</label><br />';
}
$commentaire_selection = '<div class="astuce">La recherche sera dans tous les cas aussi restreinte aux matières evaluées au cours de la période.</div>';
// Choix de vérifier ou pas l'appréciation générale ainsi que les autres dispositifs ; le test (in_array($etat,array('3mixte','4synthese'))) dépend de chaque classe...
$tab_checkbox_rubriques[] = '<label for="epi_0"><input type="checkbox" name="f_rubrique[]" id="epi_0" value="epi_0" /> <i>Enseignements Pratiques Interdisciplinaires</i></label><br />';
$tab_checkbox_rubriques[] = '<label for="ap_0"><input type="checkbox" name="f_rubrique[]" id="ap_0" value="ap_0" /> <i>Accompagnements Personnalisés</i></label><br />';
$tab_checkbox_rubriques[] = '<label for="parcours_0"><input type="checkbox" name="f_rubrique[]" id="parcours_0" value="parcours_0" /> <i>Parcours</i></label><br />';
$tab_checkbox_rubriques[] = '<label for="bilan_0"><input type="checkbox" name="f_rubrique[]" id="bilan_0" value="bilan_0" /> <i>Appréciation de synthèse générale</i></label><br />';
// Présenter les rubriques en colonnes de hauteur raisonnables
$tab_checkbox_rubriques    = array_values($tab_checkbox_rubriques);
$nb_rubriques              = count($tab_checkbox_rubriques);
$nb_rubriques_maxi_par_col = 10 ;
$nb_cols                   = floor(($nb_rubriques-1)/$nb_rubriques_maxi_par_col)+1;
$nb_rubriques_par_col      = ceil($nb_rubriques/$nb_cols);
$tab_div = array_fill(0,$nb_cols,'');
foreach($tab_checkbox_rubriques as $i => $contenu)
{
  $tab_div[floor($i/$nb_rubriques_par_col)] .= $contenu;
}

// Envoi des notifications
if(COURRIEL_NOTIFICATION=='non')
{
  $info_envoi_notifications = '<label class="alerte">Le webmestre du serveur a désactivé l\'envoi des notifications par courriel.</label>';
}
elseif(!in_array( 'TUT' , explode(',',$_SESSION['DROIT_OFFICIEL_LIVRET_VOIR_ARCHIVE']) ))
{
  $info_envoi_notifications = ($_SESSION['USER_PROFIL_TYPE']=='administrateur')
    ? '<label class="alerte">Pas de notifications par courriel aux parents car ils n\'ont pas <a href="index.php?page=administrateur_etabl_autorisations">l\'autorisation d\'accéder aux archives</a>.</label>'
    : '<label class="alerte">Pas de notifications par courriel aux parents car ils n\'ont pas l\'autorisation d\'accéder aux archives (<a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_autorisations#toggle_bilans_officiels">DOC</a></span>).</label>'
    ;
}
elseif(!$_SESSION['OFFICIEL']['LIVRET_ENVOI_MAIL_PARENT'])
{
  $info_envoi_notifications = '<label class="alerte">Pas de <a href="index.php?page=officiel&amp;section=reglages_configuration">notifications par courriel aux parents</a> imposé (seuls seront notifiés ceux qui l\'ont paramétré).</label>';
}
else
{
  $info_envoi_notifications = '<label for="f_envoi_notif_parent"><input type="checkbox" id="f_envoi_notif_parent" name="f_envoi_notif_parent" value="1" checked /> Envoyer aux parents un courriel avec un lien permettant de récupérer le bilan généré (pour ceux dont le courriel est connu, évidemment).</label>';
}
?>

<form action="#" method="post" id="zone_chx_rubriques" class="hide">
  <h2>Rechercher des saisies manquantes</h2>
  <?php echo $commentaire_selection ?>
  <p><a href="#zone_chx_rubriques" id="rubrique_check_all" class="cocher_tout">Toutes</a>&nbsp;&nbsp;&nbsp;<a href="#zone_chx_rubriques" id="rubrique_uncheck_all" class="cocher_rien">Aucune</a></p>
  <div class="prof_liste"><?php echo implode('</div><div class="prof_liste">',$tab_div) ?></div>
  <p style="clear:both"><span class="tab"></span><button id="lancer_recherche" type="button" class="rechercher">Lancer la recherche</button> <button id="fermer_zone_chx_rubriques" type="button" class="annuler">Annuler</button><label id="ajax_msg_recherche">&nbsp;</label></p>
</form>

<form action="#" method="post" id="form_hidden" class="hide">
  <div>
    <?php echo $form_hidden ?>
    <input type="hidden" id="f_objet" name="f_objet" value="" />
    <input type="hidden" id="f_listing_rubriques" name="f_listing_rubriques" value="" />
    <input type="hidden" id="f_listing_eleves" name="f_listing_eleves" value="" />
    <input type="hidden" id="f_mode" name="f_mode" value="texte" />
    <input type="hidden" id="f_notification" name="f_notification" value="" />
  </div>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaires utilisés pour les opérations ultérieures sur les bilans.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_action_eleve">
</div>

<div id="zone_action_classe" class="hide">
  <h2>Recherche de saisies manquantes | Imprimer le bilan (PDF)</h2>
  <form action="#" method="post" id="form_choix_classe"><div><b id="report_periode">Période :</b> <button id="go_precedent_classe" type="button" class="go_precedent">Précédent</button> <select id="go_selection_classe" name="go_selection_classe" class="b"><?php echo implode('',$tab_options_classes) ?></select> <button id="go_suivant_classe" type="button" class="go_suivant">Suivant</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_classe" type="button" class="retourner">Retour</button></div></form>
  <hr />
  <div id="zone_resultat_classe"></div>
  <div id="zone_imprimer" class="hide">
    <p>
      <span class="danger b">L'impression finale devrait être effectuée une unique fois lorsque le bilan est complet.</span><br />
      <span class="astuce">Pour tester l'impression d'un bilan non finalisé, utiliser la fonctionnalité de <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__simuler_impression">simulation de l'impression finale</a></span>.</span>
    </p>
    <p>
      <?php echo $info_envoi_notifications ?>
    </p>
    <form action="#" method="post" id="form_choix_eleves">
      <table id="table_action" class="form t9">
        <thead>
          <tr>
            <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
            <th>Élèves</th>
            <th class="hc">Généré</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="nu" colspan="3"></td></tr>
        </tbody>
      </table>
    </form>
    <p class="ti">
      <button id="valider_imprimer" type="button" class="valider">Lancer l'impression</button><label id="ajax_msg_imprimer">&nbsp;</label>
    </p>
  </div>
  <div id="zone_voir_archive" class="hide">
    <p>
      <span class="astuce">Ces bilans sont les exemplaires archivés sans les coordonnées des responsables légaux.</span><br />
      <span class="danger">Les autres exemplaires doivent être conservés par la personne ayant effectué l'impression PDF.</span>
    </p>
    <table class="t9">
      <thead>
        <tr>
          <th>Élèves</th>
          <th class="hc">Généré</th>
          <th class="hc">Visualisation élève</th>
          <th class="hc">Visualisation parent</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="nu" colspan="4"></td></tr>
      </tbody>
    </table>
    <p class="ti">
      <label id="ajax_msg_voir_archive">&nbsp;</label>
    </p>
  </div>
</div>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire pour afficher le résultat de l'analyse d'un fichier CSV et demander confirmation.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
Layout::add( 'css_inline' , '.insert{color:green}.update{color:red}.idem{color:grey}' ); // Pour le rapport d'analyse
?>

<form action="#" method="post" id="zone_action_deport" class="hide" onsubmit="return false">
  <h2>Saisie déportée</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__saisies_deportees">DOC : Saisie déportée.</a></span></p>
  <ul class="puce">
    <li><a id="export_file_saisie_deportee" target="_blank" rel="noopener noreferrer" href=""><span class="file file_txt">Récupérer un fichier vierge à compléter pour une saisie déportée (format <em>csv</em>).</span></a></li>
    <li><input id="f_saisie_deportee" type="file" name="userfile" /><button id="bouton_choisir_saisie_deportee" type="button" class="fichier_import">Envoyer un fichier d'appréciations complété (format <em>csv</em>).</button></li>
  </ul>
  <p class="ti">
    <label id="msg_import">&nbsp;</label>
    <input type="hidden" name="f_action" value="uploader_saisie_csv" />
    <input type="hidden" name="f_section" value="livret_importer" />
    <input type="hidden" id="f_upload_classe" name="f_classe" value="" />
    <input type="hidden" id="f_upload_groupe" name="f_groupe" value="" />
    <input type="hidden" id="f_upload_page_ref" name="f_page_ref" value="" />
    <input type="hidden" id="f_upload_periode" name="f_periode" value="" />
    <input type="hidden" id="f_upload_objet" name="f_objet" value="" />
    <input type="hidden" id="f_upload_mode" name="f_mode" value="" />
  </p>
</form>

<form action="#" method="post" id="zone_action_import" class="hide" onsubmit="return false">
  <h2>Analyse des données à importer</h2>
  <p class="astuce">Les informations <span class="insert">en vert seront ajoutées</span>, <span class="update">celles en rouge modifiées</span>, et <span class="idem">celles en gris inchangées</span>.</p>
  <table id="table_import_analyse" class="t9">
    <thead>
      <tr><td class="nu" colspan="3"></td></tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="3"></td></tr>
    </tbody>
  </table>
  <p class="ti">
    <input type="hidden" value="" name="f_import_info" id="f_import_info" /><button id="valider_importer" type="button" class="valider">Confirmer</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_importer" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_importer">&nbsp;</label>
  </p>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire pour signaler ou corriger une faute dans une appréciation.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<form action="#" method="post" id="zone_signaler_corriger" class="hide" onsubmit="return false">
  <h2>Signaler | Corriger une faute</h2>
  <div id="section_corriger">
  </div>
  <div id="section_signaler">
    <div>
      <input type="hidden" value="" name="f_destinataire_id" id="f_destinataire_id" />
      <input type="hidden" value="signaler_faute|corriger_faute" name="f_action" id="f_action" />
      <label for="f_message_contenu" class="tab">Message informatif :</label><textarea name="f_message_contenu" id="f_message_contenu" rows="5" cols="100"></textarea><br />
      <span class="tab"></span><label id="f_message_contenu_reste"></label>
    </div>
  </div>
  <p>
    <span class="tab"></span><button id="valider_signaler_corriger" type="button" class="valider">Valider</button>&nbsp;&nbsp;&nbsp;<button id="annuler_signaler_corriger" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_signaler_corriger">&nbsp;</label>
  </p>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liens pour archiver / imprimer des saisies.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_archiver_imprimer" class="hide">
  <h2>Archiver / Imprimer des données</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__imprimer_saisies">DOC : Tableaux de positionnements / d'appréciations.</a></span></p>
  <p class="noprint">Afin de préserver l'environnement, n'imprimer que si nécessaire !</p>
  <ul class="puce">
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_prof"             type="button" class="imprimer">Archiver / Imprimer</button> mes appréciations pour chaque élève et le groupe classe.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_collegues"        type="button" class="imprimer">Archiver / Imprimer</button> les appréciations des collègues pour chaque élève.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_classe_collegues"        type="button" class="imprimer">Archiver / Imprimer</button> les appréciations des collègues sur le groupe classe.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_syntheses"        type="button" class="imprimer">Archiver / Imprimer</button> les appréciations de synthèse générale pour chaque élève.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_positionnements"  type="button" class="imprimer">Archiver / Imprimer</button> le tableau des positionnements pour chaque élève.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_recapitulatif"    type="button" class="imprimer">Archiver / Imprimer</button> un récapitulatif annuel des positionnements et appréciations par élève.</li>
    <li data-periodicite="periode"><button id="imprimer_donnees_eleves_affelnet"         type="button" class="imprimer">Archiver / Imprimer</button> un récapitulatif des points calculés pour saisie dans <em>Affelnet</em> si hors <em>LSU</em>.</li>
    <li data-periodicite="cycle"  ><button id="imprimer_donnees_eleves_socle_maitrise"   type="button" class="imprimer">Archiver / Imprimer</button> le tableau des positionnements sur le socle pour chaque élève.</li>
    <li data-periodicite="cycle"  ><button id="imprimer_donnees_eleves_socle_points_dnb" type="button" class="imprimer">Archiver / Imprimer</button> le tableau des points du brevet pour chaque élève.</li>
  </ul>
  <hr />
  <p><label id="ajax_msg_archiver_imprimer">&nbsp;</label></p>
</div>

<div id="zone_elements" class="arbre_dynamique hide">
  <p>Choisir ci-dessous des éléments à ajouter (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <div id="arborescence"><label class="loader">Chargement&hellip;</label></div>
</div>


