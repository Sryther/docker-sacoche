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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_evaluations')&&($_POST['f_action']!='Voir_notes')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action     = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])       : '';
$eleve_id   = (isset($_POST['f_eleve']))      ? Clean::entier($_POST['f_eleve'])       : 0;
$prof_id    = (isset($_POST['f_prof']))       ? Clean::entier($_POST['f_prof'])        : 0;
$date_debut = (isset($_POST['f_date_debut'])) ? Clean::date_fr($_POST['f_date_debut']) : '';
$date_fin   = (isset($_POST['f_date_fin']))   ? Clean::date_fr($_POST['f_date_fin'])   : '';
$devoir_id  = (isset($_POST['f_devoir']))     ? Clean::entier($_POST['f_devoir'])      : 0;
$msg_data   = (isset($_POST['f_msg_data']))   ? Clean::texte($_POST['f_msg_data'])     : '';
$msg_url    = (isset($_POST['f_msg_url']))    ? Clean::texte($_POST['f_msg_url'])      : '';
$msg_autre  = (isset($_POST['f_msg_autre']))  ? Clean::texte($_POST['f_msg_autre'])    : '';

$chemin_devoir      = CHEMIN_DOSSIER_DEVOIR.$_SESSION['BASE'].DS;
$url_dossier_devoir = URL_DIR_DEVOIR.$_SESSION['BASE'].'/';

// En cas de manipulation du formulaire (avec les outils de développements intégrés au navigateur ou un module complémentaire)...
// Merci @ Mathieu Degrange <Mathieu-Gilbert.Degrange@ac-nice.fr> d'avoir inspecté le code et signalé ce manquement.
if( $eleve_id && in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')) )
{
  // Pour un élève on surcharge avec les données de session
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $eleve_id = $_SESSION['USER_ID'];
  }
  // Pour un parent on vérifie que c'est bien un de ses enfants
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $is_enfant_legitime = FALSE;
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      if($DB_ROW['valeur']==$eleve_id)
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
  // Pour un professeur on vérifie que c'est bien un de ses élèves
  if(!in_array($eleve_id, $_SESSION['PROF_TAB_ELEVES']))
  {
    // On vérifie de nouveau, au cas où l'admin viendrait d'ajouter une affectation
    $_SESSION['PROF_TAB_ELEVES'] = DB_STRUCTURE_PROFESSEUR::DB_lister_ids_eleves_professeur( $_SESSION['USER_ID'] , $_SESSION['USER_JOIN_GROUPES'] , 'array' /*format_retour*/ );
    if(!in_array($eleve_id, $_SESSION['PROF_TAB_ELEVES']))
    {
      Json::end( FALSE , 'Élève non rattaché à votre compte enseignant !' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_evaluations') && $eleve_id && $date_debut && $date_fin )
{
  // Formater les dates
  $date_debut_mysql = To::date_french_to_mysql($date_debut);
  $date_fin_mysql   = To::date_french_to_mysql($date_fin);
  // Vérifier que la date de début est antérieure à la date de fin
  if($date_debut_mysql>$date_fin_mysql)
  {
    Json::end( FALSE , 'La date de début est postérieure à la date de fin !' );
  }
  // Lister les évaluations
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_devoirs_eleve( $eleve_id , $prof_id , $date_debut_mysql , $date_fin_mysql , $_SESSION['USER_PROFIL_TYPE'] );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucune évaluation trouvée sur la période '.$date_debut.' ~ '.$date_fin.' !' );
  }
  // Récupérer le nb de saisies déjà effectuées par évaluation (ça posait trop de problème dans la requête précédente : saisies comptées plusieurs fois, évaluations sans saisies non retournées...)
  $tab_devoir_id = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_devoir_id[$DB_ROW['devoir_id']] = $DB_ROW['devoir_id'];
  }
  $tab_nb_saisies_effectuees = array_fill_keys($tab_devoir_id,0);
  $DB_TAB2 = DB_STRUCTURE_ELEVE::DB_lister_nb_saisies_par_evaluation( $eleve_id , implode(',',$tab_devoir_id) );
  foreach($DB_TAB2 as $DB_ROW)
  {
    $tab_nb_saisies_effectuees[$DB_ROW['devoir_id']] = $DB_ROW['saisies_nombre'];
  }
  foreach($DB_TAB as $DB_ROW)
  {
    $nb_saisies_possibles = $DB_ROW['items_nombre'];
    $date_affich = To::date_mysql_to_french($DB_ROW['devoir_date']);
    $remplissage_info = ($tab_nb_saisies_effectuees[$DB_ROW['devoir_id']]>=$nb_saisies_possibles) ? 'oui' : ( ($tab_nb_saisies_effectuees[$DB_ROW['devoir_id']]) ? 'partiel' : 'non' ) ;
    $image_sujet   = ($DB_ROW['devoir_doc_sujet'])   ? ' <a href="'.$DB_ROW['devoir_doc_sujet'].'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Voir l\'énoncé." /></a>' : '' ;
    $image_corrige = ($DB_ROW['devoir_doc_corrige']) ? ' <a href="'.$DB_ROW['devoir_doc_corrige'].'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Voir le corrigé." /></a>' : '' ;
    $q_texte       = ($DB_ROW['jointure_texte'])     ? '<q class="texte_consulter" title="Voir le commentaire écrit."></q>' : '' ;
    $q_audio       = ($DB_ROW['jointure_audio'])     ? '<q class="audio_ecouter" title="Voir le commentaire audio."></q>'   : '' ;
    // Afficher une ligne du tableau
    Json::add_row( 'html' , '<tr>' );
    Json::add_row( 'html' ,   '<td>'.html($date_affich).'</td>' );
    Json::add_row( 'html' ,   '<td>'.html(To::texte_identite($DB_ROW['prof_nom'],FALSE,$DB_ROW['prof_prenom'],TRUE,$DB_ROW['prof_genre'])).'</td>' );
    Json::add_row( 'html' ,   '<td>'.html($DB_ROW['devoir_info']).'</td>' );
    Json::add_row( 'html' ,   '<td class="hc">'.$remplissage_info.'</td>' );
    Json::add_row( 'html' ,   '<td class="nu" id="devoir_'.$DB_ROW['devoir_id'].'">' );
    Json::add_row( 'html' ,     '<q class="voir" title="Voir les items et les notes (si saisies)."></q>' );
    Json::add_row( 'html' ,     $image_sujet.$image_corrige );
    Json::add_row( 'html' ,     $q_texte );
    Json::add_row( 'html' ,     $q_audio );
    if($DB_ROW['devoir_autoeval_date']===NULL)
    {
      Json::add_row( 'html' , '' ); // Devoir sans auto-évaluation.
    }
    elseif($DB_ROW['devoir_autoeval_date']<TODAY_MYSQL)
    {
      Json::add_row( 'html' , '<q class="saisir_non" title="Auto-évaluation terminée le '.To::date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'."></q>' );
    }
    else
    {
      Json::add_row( 'html' , '<q class="saisir" title="Auto-évaluation possible jusqu\'au '.To::date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'."></q>' );
      Json::add_row( 'script' , 'tab_dates['.$DB_ROW['devoir_id'].']="'.To::date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'";' );
    }
    Json::add_row( 'html' ,   '</td>' );
    Json::add_row( 'html' , '</tr>' );
  }
  Json::add_row( 'script' , '' ); // définir la variable où il n'y aurait rien
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir les notes saisies à un devoir
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Voir_notes') && $eleve_id && $devoir_id )
{
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_ELEVE::DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id);
  // Normalement, un devoir est toujours lié à au moins un item... sauf si l'item a été supprimé dans le référentiel !
  if(empty($DB_TAB_COMP))
  {
    Json::end( FALSE , 'Ce devoir n\'est associé à aucun item !' );
  }
  $tab_liste_item = array_keys($DB_TAB_COMP);
  $liste_item_id = implode(',',$tab_liste_item);
  // Si l'élève peut formuler des demandes d'évaluations, on doit calculer le score.
  // Du coup, on choisit de récupérer les notes et de calculer les scores pour tout le monde.
  $tab_devoirs = array();
  $tab_scores  = array();
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_result_eleve_items( $eleve_id , $liste_item_id , $_SESSION['USER_PROFIL_TYPE'] );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_devoirs[$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
  }
  // préparer les lignes
  $tab_affich  = array();
  foreach($tab_liste_item as $item_id)
  {
    $DB_ROW = $DB_TAB_COMP[$item_id][0];
    $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['matiere_ref'].'.'.$DB_ROW['ref_perso'] : $DB_ROW['matiere_ref'].'.'.$DB_ROW['ref_auto'] ;
    $texte_s2016 = ($DB_ROW['s2016_nb'])  ? '[S] ' : '[–] ';
    $texte_comm  = ($DB_ROW['item_comm']) ? ' <img src="./_img/etat/comm_oui.png" title="'.convertCRtoBR(html(html($DB_ROW['item_comm']))).'" />' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $texte_lien_avant = ($DB_ROW['item_lien']) ? '<a target="_blank" rel="noopener noreferrer" href="'.html($DB_ROW['item_lien']).'">' : '';
    $texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
    $tab_scores[$item_id] = (isset($tab_devoirs[$item_id])) ? OutilBilan::calculer_score( $tab_devoirs[$item_id]  ,$DB_ROW['referentiel_calcul_methode'] , $DB_ROW['referentiel_calcul_limite'] , NULL /*date_mysql_debut*/ ) : FALSE ;
    if($_SESSION['USER_PROFIL_TYPE']=='parent')    { $texte_demande_eval = '<q class="demander_non" title="Les demandes d\'évaluations s\'effectuent depuis un compte élève."></q>'; }
    elseif($_SESSION['USER_PROFIL_TYPE']!='eleve') { $texte_demande_eval = ''; }
    elseif(!$DB_ROW['matiere_nb_demandes'])        { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour les items de cette matière."></q>'; }
    elseif(!$DB_ROW['item_cart'])                  { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour cet item précis."></q>'; }
    else                                           { $texte_demande_eval = '<q class="demander_add" id="demande_'.$DB_ROW['matiere_id'].'_'.$item_id.'_'.$tab_scores[$item_id].'" title="Ajouter aux demandes d\'évaluations."></q>'; }
    $tab_affich[$item_id] = '<td>'.html($item_ref).'</td><td>'.$texte_s2016.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.$texte_comm.$texte_demande_eval.'</td>';
  }
  // récupérer les saisies et les ajouter
  $tab_notes = array();
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $eleve_id , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*with_marqueurs*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_notes[$DB_ROW['item_id']] = $DB_ROW['saisie_note'];
  }
  foreach($tab_liste_item as $item_id)
  {
    $tab_affich[$item_id] .= (isset($tab_notes[$item_id])) ? '<td class="hc">'.Html::note_image($tab_notes[$item_id],'','',TRUE /*tri*/).'</td>' : '<td class="hc">-</td>' ;
  }
  // ajouter les états d'acquisition
  if(Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_ETAT_ACQUISITION_AVEC_EVALUATION']))
  {
    foreach($tab_liste_item as $item_id)
    {
      $tab_affich[$item_id] .= Html::td_score( $tab_scores[$item_id] , 'score' /*methode_tri*/ , '' /*pourcent*/ );
    }
  }
  $affichage = '<tr>'.implode('</tr><tr>',$tab_affich).'</tr>';
  // la légende, qui peut être personnalisée (codes AB, NN, etc.)
  $score_legende  = (Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_ETAT_ACQUISITION_AVEC_EVALUATION'])) ? TRUE : FALSE ;
  $legende = Html::legende( array( 'codes_notation'=>TRUE , 'score_bilan'=>$score_legende ) );
  // Les commentaires texte ou audio
  $commentaire_texte = '';
  $commentaire_audio = '';
  $DB_ROW = DB_STRUCTURE_COMMENTAIRE::DB_recuperer_devoir_commentaires($devoir_id,$eleve_id);
  if(!empty($DB_ROW))
  {
    if($DB_ROW['jointure_texte'])
    {
      $msg_url = $DB_ROW['jointure_texte'];
      if(strpos($msg_url,URL_DIR_SACOCHE)===0)
      {
        $fichier_chemin = url_to_chemin($msg_url);
        $msg_data = is_file($fichier_chemin) ? file_get_contents($fichier_chemin) : 'Erreur : fichier avec le contenu du commentaire non trouvé.' ;
      }
      else
      {
        $msg_data = cURL::get_contents($msg_url);
      }
      $commentaire_texte = '<h3>Commentaire écrit</h3><textarea rows="10" cols="100" readonly>'.html($msg_data).'</textarea>';
    }
    if($DB_ROW['jointure_audio'])
    {
      $msg_url = $DB_ROW['jointure_audio'];
      if(strpos($msg_url,URL_DIR_SACOCHE)!==0)
      {
        // Violation des directives CSP si on essaye de le lire sur un serveur distant -> on le récupère et le copie localement temporairement
        $msg_data = cURL::get_contents($msg_url);
        $fichier_nom = 'devoir_'.$devoir_id.'_eleve_'.$eleve_id.'_audio_copie.mp3';
        FileSystem::ecrire_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_nom , $msg_data );
        $msg_url = URL_DIR_IMPORT.$fichier_nom;
      }
      $commentaire_audio = '<h3>Commentaire audio</h3><audio id="audio_lecture" controls="" src="'.$msg_url.'" class="eleve"><span class="probleme">Votre navigateur est trop ancien, il ne supporte pas la balise [audio] !</span></audio>';
    }
  }
  // Retour
  Json::add_tab( array(
    'lignes'  => $affichage ,
    'legende' => $legende ,
    'texte'   => $commentaire_texte ,
    'audio'   => $commentaire_audio ,
  ) );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir les notes d'un devoir (auto-évaluation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Saisir_notes') && $eleve_id && $devoir_id )
{
  // liste des items
  $DB_TAB_COMP = DB_STRUCTURE_ELEVE::DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id);
  // Normalement, un devoir est toujours lié à au moins un item... sauf si l'item a été supprimé dans le référentiel !
  if(empty($DB_TAB_COMP))
  {
    Json::end( FALSE , 'Ce devoir n\'est associé à aucun item !' );
  }
  // Pas de demandes d'évaluations formulées depuis ce formulaire, pas de score affiché non plus
  $tab_liste_item = array_keys($DB_TAB_COMP);
  $liste_item_id = implode(',',$tab_liste_item);
  // boutons radio
  $tab_radio_boutons = array();
  $tab_notes = array_merge( $_SESSION['NOTE_ACTIF'] , array( 'X' , 'AB' ) ); // , 'NN' , 'NE' , 'NF' , 'NR' , 'DI' , 'PA'
  foreach($tab_notes as $note)
  {
    $tab_radio_boutons[] = '<label for="item_X_'.$note.'"><input type="radio" id="item_X_'.$note.'" name="item_X" value="'.$note.'"><br /><img alt="'.$note.'" src="'.Html::note_src($note).'" /></label>';
  }
  $radio_boutons = '<td class="hc">'.implode('</td><td class="hc">',$tab_radio_boutons).'</td>';
  // récupérer les saisies
  $tab_radio = array();
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $eleve_id , $_SESSION['USER_PROFIL_TYPE'] , FALSE /*with_marqueurs*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_radio[$DB_ROW['item_id']] = str_replace( 'value="'.$DB_ROW['saisie_note'].'"' , 'value="'.$DB_ROW['saisie_note'].'" checked' , $radio_boutons );
  }
  // récupérer les commentaires texte ou audio
  $msg_texte_url   = '';
  $msg_texte_data  = '';
  $msg_audio_autre = 'non';
  $DB_ROW = DB_STRUCTURE_COMMENTAIRE::DB_recuperer_devoir_commentaires($devoir_id,$eleve_id);
  if(!empty($DB_ROW))
  {
    if($DB_ROW['jointure_texte'])
    {
      $msg_texte_url = $DB_ROW['jointure_texte'];
      if(strpos($msg_texte_url,URL_DIR_SACOCHE)===0)
      {
        $fichier_chemin = url_to_chemin($msg_texte_url);
        $msg_texte_data = is_file($fichier_chemin) ? file_get_contents($fichier_chemin) : 'Erreur : fichier avec le contenu du commentaire non trouvé.' ;
      }
      else
      {
        $msg_texte_data = cURL::get_contents($msg_texte_url);
      }
    }
    if($DB_ROW['jointure_audio'])
    {
      $msg_audio_autre = 'oui';
    }
  }
  // lignes du tableau à retourner
  $lignes = '';
  foreach($tab_liste_item as $item_id)
  {
    $DB_ROW = $DB_TAB_COMP[$item_id][0];
    $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['matiere_ref'].'.'.$DB_ROW['ref_perso'] : $DB_ROW['matiere_ref'].'.'.$DB_ROW['ref_auto'] ;
    $texte_s2016 = ($DB_ROW['s2016_nb'])  ? '[S] ' : '[–] ';
    $texte_comm  = ($DB_ROW['item_comm']) ? ' <img src="./_img/etat/comm_oui.png" title="'.convertCRtoBR(html(html($DB_ROW['item_comm']))).'" />' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    $texte_lien_avant = ($DB_ROW['item_lien']) ? '<a target="_blank" rel="noopener noreferrer" href="'.html($DB_ROW['item_lien']).'">' : '';
    $texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
    $boutons = (isset($tab_radio[$item_id])) ? $tab_radio[$item_id] : str_replace( 'value="X"' , 'value="X" checked' , $radio_boutons ) ;
    $boutons = str_replace( 'item_X' , 'item_'.$item_id , $boutons );
    $lignes .= '<tr>'.$boutons.'<td>'.html($item_ref).'<br />'.$texte_s2016.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.$texte_comm.'</td></tr>';
  }
  // Retour
  Json::add_tab( array(
    'lignes'      => $lignes ,
    'audio_autre' => $msg_audio_autre ,
    'texte_url'   => $msg_texte_url ,
    'texte_data'  => $msg_texte_data ,
  ) );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer des notes saisies (auto-évaluation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_saisies') && $devoir_id && in_array($msg_autre,array('oui','non')) )
{
  // On récupère les informations associées à ce devoir et on vérifie que l'élève est en droit de s'y auto-évaluer.
  $DB_ROW = DB_STRUCTURE_ELEVE::DB_recuperer_devoir_infos($devoir_id);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Devoir introuvable !' );
  }
  if($DB_ROW['devoir_autoeval_date']===NULL)
  {
    Json::end( FALSE , 'Devoir sans auto-évaluation !' );
  }
  if($DB_ROW['devoir_autoeval_date']<TODAY_MYSQL)
  {
    Json::end( FALSE , 'Auto-évaluation terminée le '.To::date_mysql_to_french($DB_ROW['devoir_autoeval_date']).' !' );
  }
  $devoir_proprio_id  = $DB_ROW['proprio_id'];
  $devoir_date_mysql  = $DB_ROW['devoir_date'];
  $devoir_description = $DB_ROW['devoir_info'];
  $date_visible_mysql = $DB_ROW['devoir_visible_date'];
  // Tout est transmis : il faut comparer avec le contenu de la base pour ne mettre à jour que ce dont il y a besoin
  // On récupère les notes transmises dans $tab_post
  $tab_post = array();
  foreach($_POST as $key => $val)
  {
    if(substr($key,0,5)=='item_')
    {
      $item_id = (int)substr($key,5);
      $note    = $val;
      $tab_post[$item_id] = $note;
    }
  }
  if(!count($tab_post))
  {
    Json::end( FALSE , 'Aucune saisie récupérée !' );
  }
  // On recupère le contenu de la base déjà enregistré pour le comparer ; on remplit au fur et à mesure $tab_nouveau_modifier / $tab_nouveau_supprimer
  // $tab_demande_supprimer sert à supprimer des demandes d'élèves dont on met une note.
  $tab_nouveau_modifier  = array();
  $tab_nouveau_supprimer = array();
  $tab_demande_supprimer = array();
  $DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $_SESSION['USER_ID'] , $_SESSION['USER_PROFIL_TYPE'] , TRUE /*with_marqueurs*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $item_id = (int)$DB_ROW['item_id'];
    if(isset($tab_post[$item_id])) // Test nécessaire si élève ou item évalués dans ce devoir, mais retiré depuis (donc non transmis dans la nouvelle saisie, mais à conserver).
    {
      if($tab_post[$item_id]!=$DB_ROW['saisie_note'])
      {
        if($tab_post[$item_id]=='X')
        {
          // valeur de la base à supprimer
          $tab_nouveau_supprimer[$item_id] = $item_id;
        }
        else
        {
          // valeur de la base à modifier
          $tab_nouveau_modifier[$item_id] = $tab_post[$item_id];
          if($DB_ROW['saisie_note']=='PA')
          {
            // demande d'évaluation à supprimer
            $tab_demande_supprimer[$item_id] = $item_id;
          }
        }
      }
      unset($tab_post[$item_id]);
    }
  }
  // Il reste dans $tab_post les données à ajouter (mises dans $tab_nouveau_ajouter) et les données qui ne servent pas (non enregistrées et non saisies)
  $tab_nouveau_ajouter = array_filter($tab_post,'sans_rien');
  //
  // Il n'y a plus qu'à mettre à jour la base
  //
  // L'information associée à la note comporte le nom de l'évaluation + celui du professeur (c'est une information statique, conservée sur plusieurs années)
  $info = $devoir_description.' ('.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']).')';
  foreach($tab_nouveau_ajouter as $item_id => $note)
  {
    DB_STRUCTURE_PROFESSEUR::DB_ajouter_saisie( $devoir_proprio_id , $_SESSION['USER_ID'] , $devoir_id , $item_id , $devoir_date_mysql , $note , $info , $date_visible_mysql );
  }
  foreach($tab_nouveau_modifier as $item_id => $note)
  {
    DB_STRUCTURE_PROFESSEUR::DB_modifier_saisie( $devoir_proprio_id , $_SESSION['USER_ID'] , $devoir_id , $item_id , $note , $info );
  }
  foreach($tab_nouveau_supprimer as $item_id)
  {
    DB_STRUCTURE_PROFESSEUR::DB_supprimer_saisie( $_SESSION['USER_ID'] , $devoir_id , $item_id );
  }
  foreach($tab_demande_supprimer as $item_id)
  {
    DB_STRUCTURE_DEMANDE::DB_supprimer_demande_precise_eleve_item( $_SESSION['USER_ID'] , $item_id );
  }
  // Ajout aux flux RSS des profs concernés
  $tab_profs_rss = array_merge( array($devoir_proprio_id) , DB_STRUCTURE_ELEVE::DB_lister_devoir_profs_droit_saisie($devoir_id) );
  $titre = 'Autoévaluation effectuée par '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE);
  $texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' s\'auto-évalue sur le devoir "'.$devoir_description.'".'."\r\n";
  $texte.= ($msg_data) ? 'Commentaire :'."\r\n".$msg_data."\r\n" : 'Pas de commentaire saisi.'."\r\n" ;
  $guid  = 'autoeval_'.$devoir_id.'_'.$_SESSION['USER_ID'].'_'.$_SERVER['REQUEST_TIME']; // obligé d'ajouter un time pour unicité au cas où un élève valide 2x l'autoévaluation
  foreach($tab_profs_rss as $prof_id)
  {
    RSS::modifier_fichier_prof($prof_id,$titre,$texte,$guid);
  }
  // Notifications (rendues visibles ultérieurement) ; on récupère des données conçues pour le flux RSS ($texte , $tab_profs_rss)
  $abonnement_ref = 'devoir_autoevaluation_eleve';
  $listing_profs = implode(',',$tab_profs_rss);
  if($listing_profs)
  {
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_profs );
    if($listing_abonnes)
    {
      $notification_contenu = $texte;
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , TRUE /*sep*/ );
      }
    }
  }
  //
  // On passe maintenant au commentaire texte
  //
  // Supprimer un éventuel fichier précédent
  if( $msg_url && (mb_strpos($msg_url,$url_dossier_devoir)===0) )
  {
    // Il peut ne pas être présent sur le serveur en cas de restauration de base ailleurs, etc.
    FileSystem::supprimer_fichier( url_to_chemin($msg_url) , TRUE /*verif_exist*/ );
  }
  // Mise à jour dans la base
  if($msg_data)
  {
    $fichier_nom = 'devoir_'.$devoir_id.'_eleve_'.$_SESSION['USER_ID'].'_'.'texte'.'_'.$_SERVER['REQUEST_TIME'].'.'.'txt'; // pas besoin de le rendre inaccessible -> FileSystem::generer_fin_nom_fichier__date_et_alea() inutilement lourd
    DB_STRUCTURE_COMMENTAIRE::DB_remplacer_devoir_commentaire( $devoir_id , $_SESSION['USER_ID'] , 'texte' , $url_dossier_devoir.$fichier_nom );
    // et enregistrement du fichier
    FileSystem::ecrire_fichier( $chemin_devoir.$fichier_nom , $msg_data );
  }
  else
  {
    if($msg_autre=='oui')
    {
      DB_STRUCTURE_COMMENTAIRE::DB_remplacer_devoir_commentaire( $devoir_id , $_SESSION['USER_ID'] , 'texte' , '' );
    }
    else
    {
      DB_STRUCTURE_COMMENTAIRE::DB_supprimer_devoir_commentaire( $devoir_id , $_SESSION['USER_ID'] );
    }
  }
  // Terminé
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
