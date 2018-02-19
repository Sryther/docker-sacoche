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

$type_export = (isset($_POST['f_type']))        ? Clean::texte($_POST['f_type'])          : '';
$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::lettres($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? Clean::texte($_POST['f_groupe_nom'])    : '';
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])    : 0;
$matiere_id  = (isset($_POST['f_matiere']))     ? Clean::entier($_POST['f_matiere'])      : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? Clean::texte($_POST['f_matiere_nom'])   : '';
$cycle_id    = (isset($_POST['f_cycle']))       ? Clean::entier($_POST['f_cycle'])        : 0;
$cycle_nom   = (isset($_POST['f_cycle_nom']))   ? Clean::texte($_POST['f_cycle_nom'])     : '';
$periode_id  = (isset($_POST['f_periode']))     ? Clean::entier($_POST['f_periode'])      : 0;
$periode_nom = (isset($_POST['f_periode_nom'])) ? Clean::texte($_POST['f_periode_nom'])   : '';
$date_debut  = (isset($_POST['f_date_debut']))  ? Clean::date_fr($_POST['f_date_debut'])  : '';
$date_fin    = (isset($_POST['f_date_fin']))    ? Clean::date_fr($_POST['f_date_fin'])    : '';

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV & PDF des commentaires écrits aux évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='devoirs_commentaires') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom && ( ( $periode_id && $periode_nom ) || ($date_debut && $date_fin) ) )
{
  // Période concernée
  if($periode_id==0)
  {
    $date_mysql_debut = To::date_french_to_mysql($date_debut);
    $date_mysql_fin   = To::date_french_to_mysql($date_fin);
    $periode_nom = $date_mysql_debut.'_'.$date_mysql_fin;
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
  // Récupérer les élèves de la classe ou du groupe, puis les données
  $tab_eleve = array();
  $champs = 'user_id, user_nom, user_prenom';
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleve[$DB_ROW['user_id']] = array(
        'nom'       => $DB_ROW['user_nom'],
        'prenom'    => $DB_ROW['user_prenom'],
        'donnees'   => array(),
        'nb_lignes' => 1,
      );
    }
    $liste_eleve_id = implode(',',array_keys($tab_eleve));
    // Récupérer les infos et le contenu des commentaires
    $DB_TAB = DB_STRUCTURE_COMMENTAIRE::DB_lister_commentaires_eleves_dates( $_SESSION['USER_ID'] , $liste_eleve_id , $date_mysql_debut , $date_mysql_fin );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $devoir_nom = $DB_ROW['devoir_info'];
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
        $nb_lignes = max( 2 , ceil(strlen($msg_data)/75) , substr_count($saisie_classe['saisie_valeur'],"\n") + 1 );
        $tab_eleve[$DB_ROW['eleve_id']]['donnees'][] = array(
          'date'   => To::date_mysql_to_french($DB_ROW['devoir_date']),
          'titre'  => $DB_ROW['devoir_info'],
          'comm'   => $msg_data,
          'lignes' => $nb_lignes,
        );
        $tab_eleve[$DB_ROW['eleve_id']]['nb_lignes'] += $nb_lignes;
      }
    }
  }
  // Préparation de la sortie PDF
  $export_PDF = new PDF_evaluation_commentaires( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 5 /*marge_gauche*/ , 5 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $export_PDF->initialiser();
  // Préparation de l'export CSV
  $separateur = ';';
  $export_csv  = 'NOM'.$separateur.'PRENOM'.$separateur.'DATE'.$separateur.'DEVOIR'.$separateur.'COMMENTAIRE'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr><th>Nom</th><th>Prénom</th><th>Date</th><th>Devoir</th><th>Commentaire</th></tr>'.NL.'</thead><tbody>'.NL;
  // On remplit
  if(!empty($DB_TAB))
  {
    foreach($tab_eleve as $eleve_id => $tab_user)
    {
      if(!empty($tab_user['donnees']))
      {
        $export_PDF->eleve_commentaires( $tab_user['nom'] , $tab_user['prenom'] , $tab_user['nb_lignes'] , $tab_user['donnees'] );
        $export_csv_debut  = $tab_user['nom'].$separateur.$tab_user['prenom'].$separateur;
        $export_html_debut = '<tr>'.'<td>'.html($tab_user['nom']).'</td>'.'<td>'.html($tab_user['prenom']).'</td>';
        foreach($tab_user['donnees'] as $tab_donnees)
        {
          $export_csv  .= $export_csv_debut.$tab_donnees['date'].$separateur.$tab_donnees['titre'].$separateur.$tab_donnees['comm']."\r\n";
          $export_html .= $export_html_debut.'<td>'.html($tab_donnees['date']).'</td>'.'<td>'.html($tab_donnees['titre']).'</td>'.'<td>'.html($tab_donnees['comm']).'</td>'.'</tr>'.NL;
        }
      }
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_devoirs-commentaires_'.Clean::fichier($groupe_nom).'_'.Clean::fichier($periode_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Finalisation de l'export PDF (archivage dans un fichier)
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fnom.'.pdf' , $export_PDF );
  // Affichage
  $puce_download_csv = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  $puce_download_pdf = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="'.URL_DIR_EXPORT.$fnom.'.pdf"><span class="file file_pdf">Récupérer les données (fichier <em>pdf</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download_csv.$puce_download_pdf.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données des élèves d'un regroupement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='listing_eleves') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'.$separateur.'LOGIN'.$separateur.'GENRE'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'DATE_NAISSANCE'.$separateur.'GROUPE'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr><th>Id</th><th>Login</th><th>Genre</th><th>Nom</th><th>Prénom</th><th>Date Naiss.</th><th>Groupe</th></tr>'.NL.'</thead><tbody>'.NL;
  // Récupérer les élèves de la classe ou du groupe
  $champs = 'user_id, user_login, user_genre, user_nom, user_prenom, user_naissance_date';
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $date_fr = To::date_mysql_to_french($DB_ROW['user_naissance_date']);
      $export_csv .= $DB_ROW['user_id']
        .$separateur.$DB_ROW['user_login']
        .$separateur.Html::$tab_genre['enfant'][$DB_ROW['user_genre']]
        .$separateur.$DB_ROW['user_nom']
        .$separateur.$DB_ROW['user_prenom']
        .$separateur.$date_fr
        .$separateur.$groupe_nom
        ."\r\n";
      $export_html .= '<tr>'
                       .'<td>'.$DB_ROW['user_id'].'</td>'
                       .'<td>'.html($DB_ROW['user_login']).'</td>'
                       .'<td>'.Html::$tab_genre['enfant'][$DB_ROW['user_genre']].'</td>'
                       .'<td>'.html($DB_ROW['user_nom']).'</td>'
                       .'<td>'.html($DB_ROW['user_prenom']).'</td>'
                       .'<td>'.$date_fr.'</td>'
                       .'<td>'.html($groupe_nom).'</td>'
                     .'</tr>'.NL;
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_listing-eleves_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données des items d'une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='listing_matiere') && $matiere_id && $matiere_nom )
{
  Form::save_choix('export_fichier');
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'ITEM_ID'
    .$separateur.'MATIERE'
    .$separateur.'NIVEAU'
    .$separateur.'DOMAINE'
    .$separateur.'THEME'
    .$separateur.'REFERENCE'
    .$separateur.'ITEM'
    .$separateur.'COEF'
    .$separateur.'DEMANDE_EVAL'
    .$separateur.'LIEN'
    .$separateur.'SOCLE_2016'
    .$separateur.'COMMENTAIRE'
    ."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr>'
                   .'<th>Id</th>'
                   .'<th>Matière</th>'
                   .'<th>Niveau</th>'
                   .'<th>Domaine</th>'
                   .'<th>Thème</th>'
                   .'<th>Référence</th>'
                   .'<th>Item</th>'
                   .'<th>Coef</th>'
                   .'<th>Demande</th>'
                   .'<th>Lien</th>'
                   .'<th>Socle</th>'
                   .'<th>Socle 2016</th>'
                   .'<th>Commentaire</th>'
                 .'</tr>'.NL.'</thead><tbody>'.NL;

  $DB_TAB_socle2016 = DB_STRUCTURE_REFERENTIEL::DB_recuperer_socle2016_for_referentiels_matiere($matiere_id);
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , TRUE /*only_item*/ , FALSE /*s2016_count*/ , TRUE /*item_comm*/ );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $item_ref = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref'] || $DB_ROW['item_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'].$DB_ROW['item_ref'] : $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_code'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'] ;
      $demande_eval = ($DB_ROW['item_cart']) ? 'oui' : 'non' ;
      $s2016_texte = isset($DB_TAB_socle2016[$DB_ROW['item_id']]) ? implode("\r\n",$DB_TAB_socle2016[$DB_ROW['item_id']]['nom']) : 'Hors-socle.' ;
      $export_csv .= $DB_ROW['item_id']
        .$separateur.$matiere_nom
        .$separateur.'"'.$DB_ROW['niveau_nom'].'"'
        .$separateur.'"'.$DB_ROW['domaine_nom'].'"'
        .$separateur.'"'.$DB_ROW['theme_nom'].'"'
        .$separateur.$DB_ROW['matiere_ref'].'.'.$item_ref
        .$separateur.'"'.$DB_ROW['item_nom'].'"'
        .$separateur.$DB_ROW['item_coef']
        .$separateur.$demande_eval
        .$separateur.'"'.$DB_ROW['item_lien'].'"'
        .$separateur.'"'.$s2016_texte.'"'
        .$separateur.'"'.$DB_ROW['item_comm'].'"'
        ."\r\n";
      $export_html .= '<tr>'
                       .'<td>'.$DB_ROW['item_id'].'</td>'
                       .'<td>'.html($matiere_nom).'</td>'
                       .'<td>'.html($DB_ROW['niveau_nom']).'</td>'
                       .'<td>'.html($DB_ROW['domaine_nom']).'</td>'
                       .'<td>'.html($DB_ROW['theme_nom']).'</td>'
                       .'<td>'.html($item_ref).'</td>'
                       .'<td>'.html($DB_ROW['item_nom']).'</td>'
                       .'<td>'.html($DB_ROW['item_coef']).'</td>'
                       .'<td>'.html($demande_eval).'</td>'
                       .'<td>'.html($DB_ROW['item_lien']).'</td>'
                       .'<td>'.convertCRtoBR(html($s2016_texte)).'</td>'
                       .'<td>'.convertCRtoBR(html($DB_ROW['item_comm'])).'</td>'
                     .'</tr>'.NL;
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_listing-items_'.Clean::fichier($matiere_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des items d'une matière avec leur nombre d'utilisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='item_matiere_usage') && $matiere_id && $matiere_nom )
{
  Form::save_choix('export_fichier');
  // Préparation de l'export CSV
  $separateur = ';';
  $export_csv_entete  = 'ITEM_ID'
    .$separateur.'MATIERE'
    .$separateur.'NIVEAU'
    .$separateur.'DOMAINE'
    .$separateur.'THEME'
    .$separateur.'REFERENCE'
    .$separateur.'ITEM'
    .$separateur.'TOTAL';
  $tab_export_csv  = array();
  // Préparation de l'export HTML
  $export_html_entete = '<table class="p"><thead>'.NL.'<tr>'
                          .'<th>Id</th>'
                          .'<th>Matière</th>'
                          .'<th>Niveau</th>'
                          .'<th>Domaine</th>'
                          .'<th>Thème</th>'
                          .'<th>Référence</th>'
                          .'<th>Item</th>'
                          .'<th>Notes<br />Total</th>';
  $tab_export_html = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , TRUE /*only_item*/ , FALSE /*s2016_count*/ , FALSE /*item_comm*/ );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $item_ref = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref'] || $DB_ROW['item_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'].$DB_ROW['item_ref'] : $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_code'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'] ;
      $tab_export_csv[$DB_ROW['item_id']]  = $DB_ROW['item_id']
                                              .$separateur.$matiere_nom
                                              .$separateur.'"'.$DB_ROW['niveau_nom'].'"'
                                              .$separateur.'"'.$DB_ROW['domaine_nom'].'"'
                                              .$separateur.'"'.$DB_ROW['theme_nom'].'"'
                                              .$separateur.$DB_ROW['matiere_ref'].'.'.$item_ref
                                              .$separateur.'"'.$DB_ROW['item_nom'].'"';
      $tab_export_html[$DB_ROW['item_id']] = '<tr>'
                                              .'<td>'.$DB_ROW['item_id'].'</td>'
                                              .'<td>'.html($matiere_nom).'</td>'
                                              .'<td>'.html($DB_ROW['niveau_nom']).'</td>'
                                              .'<td>'.html($DB_ROW['domaine_nom']).'</td>'
                                              .'<td>'.html($DB_ROW['theme_nom']).'</td>'
                                              .'<td>'.html($DB_ROW['matiere_ref'].'.'.$item_ref).'</td>'
                                              .'<td>'.html($DB_ROW['item_nom']).'</td>';
    }
  }
  // On compte maintenant le nombre de saisies par item et par année scolaire.
  $tab_count = array();
  if(!empty($DB_TAB))
  {
    $tab_item = array_keys($tab_export_csv);
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_dates_saisies_items( implode(',',$tab_item) );
    if(!empty($DB_TAB))
    {
      $annee_decalage = 0;
      do
      {
        $export_csv_entete  .= ($annee_decalage) ? $separateur.'ANNEE -'.$annee_decalage : $separateur.'ANNEE' ;
        $export_html_entete .= ($annee_decalage) ? '<th>Notes<br />Année &minus;'.$annee_decalage.'</th>' : '<th>Notes<br />Année</th>' ;
        foreach($tab_item as $item_id)
        {
          $tab_count[$item_id][$annee_decalage] = 0;
        }
        $date_min = To::jour_debut_annee_scolaire('mysql',-$annee_decalage);
        foreach($DB_TAB as $key => $DB_ROW)
        {
          if( $date_min <= $DB_ROW['date'] )
          {
            $tab_count[$DB_ROW['item_id']][$annee_decalage] += $DB_ROW['nombre'];
            unset($DB_TAB[$key]);
          }
        }
        $annee_decalage++;
      }
      while( count($DB_TAB) && ($annee_decalage<10) );
      // On ajoute tout ça aux sorties
      foreach($tab_item as $item_id)
      {
        $total = array_sum($tab_count[$item_id]);
        $tab_export_csv[$item_id]  .= $separateur.$total;
        $tab_export_html[$item_id] .= '<td>'.$total.'</td>';
        for( $annee=0 ; $annee<$annee_decalage ; $annee++ )
        {
          $nombre = $tab_count[$item_id][$annee];
          $tab_export_csv[$item_id]  .= $separateur.$nombre;
          $tab_export_html[$item_id] .= '<td>'.$nombre.'</td>';
        }
      }
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $export_csv  = $export_csv_entete."\r\n\r\n".implode( "\r\n" , $tab_export_csv );
  $fnom = 'export_listing-items_'.Clean::fichier($matiere_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html = $export_html_entete.NL.'</thead><tbody>'.NL.implode( NL , $tab_export_html ).NL.'</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV de l'arborescence des items d'une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='arbre_matiere') && $matiere_id && $matiere_nom )
{
  Form::save_choix('matiere');
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'MATIERE'.$separateur.'NIVEAU'.$separateur.'DOMAINE'.$separateur.'THEME'.$separateur.'ITEM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<div id="zone_matieres_items" class="arbre_dynamique p">'.NL;

  $tab_niveau  = array();
  $tab_domaine = array();
  $tab_theme   = array();
  $tab_item    = array();
  $niveau_id = 0;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*s2016_count*/ , FALSE /*item_comm*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['niveau_id']!=$niveau_id)
    {
      $niveau_id = $DB_ROW['niveau_id'];
      $tab_niveau[$niveau_id] = $DB_ROW['niveau_ref'].' - '.$DB_ROW['niveau_nom'];
      $domaine_id = 0;
      $theme_id   = 0;
      $item_id    = 0;
    }
    if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
    {
      $domaine_id = $DB_ROW['domaine_id'];
      $reference  = ($DB_ROW['domaine_ref']) ? $DB_ROW['domaine_ref'] : $DB_ROW['domaine_code'] ;
      $tab_domaine[$niveau_id][$domaine_id] = $reference.' - '.$DB_ROW['domaine_nom'];
    }
    if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
    {
      $theme_id = $DB_ROW['theme_id'];
      $reference = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'] : $DB_ROW['domaine_code'].$DB_ROW['theme_ordre'] ;
      $tab_theme[$niveau_id][$domaine_id][$theme_id] = $reference.' - '.$DB_ROW['theme_nom'];
    }
    if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
    {
      $item_id = $DB_ROW['item_id'];
      $reference = ($DB_ROW['domaine_ref'] || $DB_ROW['theme_ref'] || $DB_ROW['item_ref']) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ref'].$DB_ROW['item_ref'] : $DB_ROW['domaine_code'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'] ;
      $tab_item[$niveau_id][$domaine_id][$theme_id][$item_id] = $reference.' - '.$DB_ROW['item_nom'];
    }
  }
  $export_csv .= $DB_ROW['matiere_ref'].' - '.$matiere_nom."\r\n";
  $export_html .= '<ul class="ul_m1">'.NL;
  $export_html .=   '<li class="li_m1"><span>'.html($DB_ROW['matiere_ref'].' - '.$matiere_nom).'</span>'.NL;
  $export_html .=     '<ul class="ul_m2">'.NL;
  foreach($tab_niveau as $niveau_id => $niveau_nom)
  {
    $export_csv .= $separateur.$niveau_nom."\r\n";
    $export_html .=       '<li class="li_m2"><span>'.html($niveau_nom).'</span>'.NL;
    $export_html .=         '<ul class="ul_n1">'.NL;
    if(isset($tab_domaine[$niveau_id]))
    {
      foreach($tab_domaine[$niveau_id] as $domaine_id => $domaine_nom)
      {
        $export_csv .= $separateur.$separateur.$domaine_nom."\r\n";
        $export_html .=           '<li class="li_n1"><span>'.html($domaine_nom).'</span>'.NL;
        $export_html .=             '<ul class="ul_n2">'.NL;
        if(isset($tab_theme[$niveau_id][$domaine_id]))
        {
          foreach($tab_theme[$niveau_id][$domaine_id] as $theme_id => $theme_nom)
          {
            $export_csv .= $separateur.$separateur.$separateur.$theme_nom."\r\n";
            $export_html .=               '<li class="li_n2"><span>'.html($theme_nom).'</span>'.NL;
            $export_html .=                 '<ul class="ul_n3">'.NL;
            if(isset($tab_item[$niveau_id][$domaine_id][$theme_id]))
            {
              foreach($tab_item[$niveau_id][$domaine_id][$theme_id] as $item_id => $item_nom)
              {
                $export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$item_nom.'"'."\r\n";
                $export_html .=                   '<li class="li_n3">'.html($item_nom).'</li>'.NL;
              }
            }
            $export_html .=                 '</ul>'.NL;
            $export_html .=               '</li>'.NL;
          }
        }
        $export_html .=             '</ul>'.NL;
        $export_html .=           '</li>'.NL;
      }
    }
    $export_html .=         '</ul>'.NL;
    $export_html .=       '</li>'.NL;
  }
  $export_html .=     '</ul>'.NL;
  $export_html .=   '</li>'.NL;
  $export_html .= '</ul>'.NL;
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_arbre-matiere_'.Clean::fichier($matiere_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html.= '</div>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer l\'arborescence (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des liens des matières rattachés aux liens du socle 2016
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='jointure_socle2016_matiere') && $cycle_id && $cycle_nom )
{
  Form::save_choix('cycle');
  // Préparation de l'export CSV
  $separateur = ';';
  $export_csv  = 'SOCLE CYCLE'.$separateur.'SOCLE DOMAINE'.$separateur.'SOCLE COMPOSANTE'.$separateur.'MATIERE ITEM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<div id="zone_cycles" class="arbre_dynamique p">'.NL;
  // Récupération des données du socle
  $tab_socle_domaine    = array();
  $tab_socle_composante = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_arborescence();
  foreach($DB_TAB as $DB_ROW)
  {
    $socle_domaine_id    = $DB_ROW['socle_domaine_id'];
    $socle_composante_id = $DB_ROW['socle_composante_id'];
    $tab_socle_domaine[$socle_domaine_id] = $DB_ROW['socle_domaine_ordre'].' '.$DB_ROW['socle_domaine_nom_simple'];
    $tab_socle_composante[$socle_domaine_id][$socle_composante_id] = $DB_ROW['socle_composante_nom_simple'];
  }
  // Récupération des données des référentiels liés aux composantes du socle
  $tab_jointure = array();
  $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_associations_items_composantes($cycle_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $item_ref = ($DB_ROW['ref_perso']) ? $DB_ROW['ref_perso'] : $DB_ROW['ref_auto'] ;
    $tab_jointure[$DB_ROW['socle_composante_id']][] = $DB_ROW['matiere_ref'].'.'.$item_ref.' - '.$DB_ROW['item_nom'];
  }
  // Elaboration de la sortie
  $export_csv .= $cycle_nom."\r\n";
  $export_html .= '<ul class="ul_m1">'.NL;
  $export_html .=   '<li class="li_m1"><span>'.html($cycle_nom).'</span>'.NL;
  $export_html .=     '<ul class="ul_n1">'.NL;
  foreach($tab_socle_domaine as $socle_domaine_id => $domaine_nom)
  {
    $export_csv .= $separateur.$domaine_nom."\r\n";
    $export_html .=       '<li class="li_n1"><span>'.html($domaine_nom).'</span>'.NL;
    $export_html .=         '<ul class="ul_n2">'.NL;
    foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
    {
      $export_csv .= $separateur.$separateur.'"'.$socle_composante_nom.'"'."\r\n";
      $export_html .=           '<li class="li_n2"><span>'.html($socle_composante_nom).'</span>'.NL;
      if(isset($tab_jointure[$socle_composante_id]))
      {
        $export_html .=             '<ul class="ul_m2">'.NL;
        foreach($tab_jointure[$socle_composante_id] as $item_descriptif)
        {
          $export_csv .= $separateur.$separateur.$separateur.'"'.$item_descriptif.'"'."\r\n";
          $export_html .=               '<li class="li_m2">'.html($item_descriptif).'</li>'.NL;
        }
        $export_html .=             '</ul>'.NL;
      }
      else
      {
        $export_csv .= $separateur.$separateur.$separateur.'"AUCUN ITEM ASSOCIÉ"'."\r\n";
        $export_html .=             '<br /><label class="alerte"><span style="background-color:#EE7">Aucun item associé.</span></label>'.NL;
      }
      $export_html .=           '</li>'.NL;
    }
    $export_html .=         '</ul>'.NL;
    $export_html .=       '</li>'.NL;
  }
  $export_html .=     '</ul>'.NL;
  $export_html .=   '</li>'.NL;
  $export_html .= '</ul>'.NL;
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_jointures_'.Clean::fichier($cycle_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html.= '</div>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les associations (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données d'élèves (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_eleves') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'
    .$separateur.'ID_ENT'
    .$separateur.'ID_GEPI'
    .$separateur.'SCONET_ID'
    .$separateur.'SCONET_NUM'
    .$separateur.'REFERENCE'
    .$separateur.'LOGIN'
    .$separateur.'GENRE'
    .$separateur.'NOM'
    .$separateur.'PRENOM'
    .$separateur.'DATE_NAISSANCE'
    .$separateur.'COURRIEL'
    .$separateur.'CLASSE_REF'
    .$separateur.'CLASSE_NOM'
    ."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr>'
                   .'<th>Id</th>'
                   .'<th>Id. ENT</th>'
                   .'<th>Id. GEPI</th>'
                   .'<th>Id. Sconet</th>'
                   .'<th>Num. Sconet</th>'
                   .'<th>Référence</th>'
                   .'<th>Login</th>'
                   .'<th>Genre</th>'
                   .'<th>Nom</th>'
                   .'<th>Prénom</th>'
                   .'<th>Date Naiss.</th>'
                   .'<th>Courriel</th>'
                   .'<th>Classe Ref.</th>'
                   .'<th>Classe Nom</th>'
                 .'</tr>'.NL.'</thead><tbody>'.NL;

  // Récupérer la liste des classes
  $tab_groupe = array();
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupe[$DB_ROW['groupe_id']] = array( 'ref'=>$DB_ROW['groupe_ref'] , 'nom'=>$DB_ROW['groupe_nom'] );
  }
  // Récupérer les données des élèves
  $champs = 'user_id, user_id_ent, user_id_gepi, user_sconet_id, user_sconet_elenoet, user_reference, user_genre, user_nom, user_prenom, user_naissance_date, user_email, user_login, eleve_classe_id';
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $date_fr = To::date_mysql_to_french($DB_ROW['user_naissance_date']);
      $export_csv .= $DB_ROW['user_id']
        .$separateur.$DB_ROW['user_id_ent']
        .$separateur.$DB_ROW['user_id_gepi']
        .$separateur.$DB_ROW['user_sconet_id']
        .$separateur.$DB_ROW['user_sconet_elenoet']
        .$separateur.$DB_ROW['user_reference']
        .$separateur.$DB_ROW['user_login']
        .$separateur.Html::$tab_genre['enfant'][$DB_ROW['user_genre']]
        .$separateur.$DB_ROW['user_nom']
        .$separateur.$DB_ROW['user_prenom']
        .$separateur.$date_fr
        .$separateur.$DB_ROW['user_email']
        .$separateur.$tab_groupe[$DB_ROW['eleve_classe_id']]['ref']
        .$separateur.$tab_groupe[$DB_ROW['eleve_classe_id']]['nom']
        ."\r\n";
      $export_html .= '<tr>'
                       .'<td>'.$DB_ROW['user_id'].'</td>'
                       .'<td>'.html($DB_ROW['user_id_ent']).'</td>'
                       .'<td>'.html($DB_ROW['user_id_gepi']).'</td>'
                       .'<td>'.$DB_ROW['user_sconet_id'].'</td>'
                       .'<td>'.$DB_ROW['user_sconet_elenoet'].'</td>'
                       .'<td>'.html($DB_ROW['user_reference']).'</td>'
                       .'<td>'.html($DB_ROW['user_login']).'</td>'
                       .'<td>'.Html::$tab_genre['enfant'][$DB_ROW['user_genre']].'</td>'
                       .'<td>'.html($DB_ROW['user_nom']).'</td>'
                       .'<td>'.html($DB_ROW['user_prenom']).'</td>'
                       .'<td>'.$date_fr.'</td>'
                       .'<td>'.html($DB_ROW['user_email']).'</td>'
                       .'<td>'.html($tab_groupe[$DB_ROW['eleve_classe_id']]['ref']).'</td>'
                       .'<td>'.html($tab_groupe[$DB_ROW['eleve_classe_id']]['nom']).'</td>'
                     .'</tr>'.NL;
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_infos-eleves_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données de responsables légaux (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_parents') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'
    .$separateur.'ID_ENT'
    .$separateur.'ID_GEPI'
    .$separateur.'SCONET_ID'
    .$separateur.'SCONET_NUM'
    .$separateur.'REFERENCE'
    .$separateur.'LOGIN'
    .$separateur.'CIVILITE'
    .$separateur.'NOM'
    .$separateur.'PRENOM'
    .$separateur.'COURRIEL'
    .$separateur.'ENFANT_ID'
    .$separateur.'ENFANT_NOM'
    .$separateur.'ENFANT_PRENOM'
    .$separateur.'ENFANT_CLASSE_REF'
    .$separateur.'ENFANT_CLASSE_NOM'
    ."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr>'
                   .'<th>Id</th>'
                   .'<th>Id. ENT</th>'
                   .'<th>Id. GEPI</th>'
                   .'<th>Id. Sconet</th>'
                   .'<th>Num. Sconet</th>'
                   .'<th>Référence</th>'
                   .'<th>Login</th>'
                   .'<th>Civilité</th>'
                   .'<th>Nom</th>'
                   .'<th>Prénom</th>'
                   .'<th>Courriel</th>'
                   .'<th>Enfant Id.</th>'
                   .'<th>Enfant Nom</th>'
                   .'<th>Enfant Prénom</th>'
                   .'<th>Enfant Classe Ref.</th>'
                   .'<th>Enfant Classe Nom</th>'
                 .'</tr>'.NL.'</thead><tbody>'.NL;

  // Récupérer la liste des classes
  $tab_groupe = array();
  $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupe[$DB_ROW['groupe_id']] = array( 'ref'=>$DB_ROW['groupe_ref'] , 'nom'=>$DB_ROW['groupe_nom'] );
  }
  // Récupérer les données des responsables
  $champs = 'parent.user_id AS parent_id, parent.user_id_ent AS parent_id_ent, parent.user_id_gepi AS parent_id_gepi,
             parent.user_sconet_id AS parent_sconet_id, parent.user_sconet_elenoet AS parent_sconet_elenoet, parent.user_reference AS parent_reference,
             parent.user_genre AS parent_genre, parent.user_nom AS parent_nom, parent.user_prenom AS parent_prenom, parent.user_email AS parent_email, parent.user_login AS parent_login,
             enfant.user_id AS enfant_id,enfant.user_nom AS enfant_nom,enfant.user_prenom AS enfant_prenom,enfant.eleve_classe_id AS enfant_classe_id' ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'parent' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $export_csv .= $DB_ROW['parent_id']
        .$separateur.$DB_ROW['parent_id_ent']
        .$separateur.$DB_ROW['parent_id_gepi']
        .$separateur.$DB_ROW['parent_sconet_id']
        .$separateur.$DB_ROW['parent_sconet_elenoet']
        .$separateur.$DB_ROW['parent_reference']
        .$separateur.$DB_ROW['parent_login']
        .$separateur.Html::$tab_genre['adulte'][$DB_ROW['parent_genre']]
        .$separateur.$DB_ROW['parent_nom']
        .$separateur.$DB_ROW['parent_prenom']
        .$separateur.$DB_ROW['parent_email']
        .$separateur.$DB_ROW['enfant_id']
        .$separateur.$DB_ROW['enfant_nom']
        .$separateur.$DB_ROW['enfant_prenom']
        .$separateur.$tab_groupe[$DB_ROW['enfant_classe_id']]['ref']
        .$separateur.$tab_groupe[$DB_ROW['enfant_classe_id']]['nom']
        ."\r\n";
      $export_html .= '<tr>'
                       .'<td>'.$DB_ROW['parent_id'].'</td>'
                       .'<td>'.html($DB_ROW['parent_id_ent']).'</td>'
                       .'<td>'.html($DB_ROW['parent_id_gepi']).'</td>'
                       .'<td>'.$DB_ROW['parent_sconet_id'].'</td>'
                       .'<td>'.$DB_ROW['parent_sconet_elenoet'].'</td>'
                       .'<td>'.html($DB_ROW['parent_reference']).'</td>'
                       .'<td>'.html($DB_ROW['parent_login']).'</td>'
                       .'<td>'.Html::$tab_genre['adulte'][$DB_ROW['parent_genre']].'</td>'
                       .'<td>'.html($DB_ROW['parent_nom']).'</td>'
                       .'<td>'.html($DB_ROW['parent_prenom']).'</td>'
                       .'<td>'.html($DB_ROW['parent_email']).'</td>'
                       .'<td>'.$DB_ROW['enfant_id'].'</td>'
                       .'<td>'.html($DB_ROW['enfant_nom']).'</td>'
                       .'<td>'.html($DB_ROW['enfant_prenom']).'</td>'
                       .'<td>'.html($tab_groupe[$DB_ROW['enfant_classe_id']]['ref']).'</td>'
                       .'<td>'.html($tab_groupe[$DB_ROW['enfant_classe_id']]['nom']).'</td>'
                     .'</tr>'.NL;
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_infos-parents_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données de professeurs (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_professeurs') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'
    .$separateur.'ID_ENT'
    .$separateur.'ID_GEPI'
    .$separateur.'SCONET_ID'
    .$separateur.'SCONET_NUM'
    .$separateur.'REFERENCE'
    .$separateur.'LOGIN'
    .$separateur.'CIVILITE'
    .$separateur.'NOM'
    .$separateur.'PRENOM'
    .$separateur.'COURRIEL'
    .$separateur.'PROFIL'
    ."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr>'
                   .'<th>Id</th>'
                   .'<th>Id. ENT</th>'
                   .'<th>Id. GEPI</th>'
                   .'<th>Id. Sconet</th>'
                   .'<th>Num. Sconet</th>'
                   .'<th>Référence</th>'
                   .'<th>Login</th>'
                   .'<th>Civilité</th>'
                   .'<th>Nom</th>'
                   .'<th>Prénom</th>'
                   .'<th>Courriel</th>'
                   .'<th>Profil</th>'
                 .'</tr>'.NL.'</thead><tbody>'.NL;

  // Récupérer les données des professeurs et des personnels
  $tab_profil = array('professeur','personnel');
  $champs = 'user_id, user_id_ent, user_id_gepi, user_sconet_id, user_sconet_elenoet, user_reference, user_genre, user_nom, user_prenom, user_email, user_login, user_profil_sigle' ;
  foreach($tab_profil as $profil)
  {
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $profil /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $export_csv .= $DB_ROW['user_id']
          .$separateur.$DB_ROW['user_id_ent']
          .$separateur.$DB_ROW['user_id_gepi']
          .$separateur.$DB_ROW['user_sconet_id']
          .$separateur.$DB_ROW['user_sconet_elenoet']
          .$separateur.$DB_ROW['user_reference']
          .$separateur.$DB_ROW['user_login']
          .$separateur.Html::$tab_genre['adulte'][$DB_ROW['user_genre']]
          .$separateur.$DB_ROW['user_nom']
          .$separateur.$DB_ROW['user_prenom']
          .$separateur.$DB_ROW['user_email']
          .$separateur.$DB_ROW['user_profil_sigle']
          ."\r\n";
        $export_html .= '<tr>'
                         .'<td>'.$DB_ROW['user_id'].'</td>'
                         .'<td>'.html($DB_ROW['user_id_ent']).'</td>'
                         .'<td>'.html($DB_ROW['user_id_gepi']).'</td>'
                         .'<td>'.$DB_ROW['user_sconet_id'].'</td>'
                         .'<td>'.$DB_ROW['user_sconet_elenoet'].'</td>'
                         .'<td>'.html($DB_ROW['user_reference']).'</td>'
                         .'<td>'.html($DB_ROW['user_login']).'</td>'
                         .'<td>'.Html::$tab_genre['adulte'][$DB_ROW['user_genre']].'</td>'
                         .'<td>'.html($DB_ROW['user_nom']).'</td>'
                         .'<td>'.html($DB_ROW['user_prenom']).'</td>'
                         .'<td>'.html($DB_ROW['user_email']).'</td>'
                         .'<td>'.$DB_ROW['user_profil_sigle'].'</td>'
                       .'</tr>'.NL;
      }
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier)
  $fnom = 'export_infos-professeurs_'.Clean::fichier($groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'.NL;
  // Affichage
  $puce_download = '<ul class="puce"><li><a target="_blank" rel="noopener noreferrer" href="./force_download.php?fichier='.$fnom.'.csv"><span class="file file_txt">Récupérer les données (fichier <em>csv</em></span>).</a></li></ul>'.NL;
  Json::end( TRUE , $puce_download.$export_html );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter une estimation des degrés de maîtrise du nouveau socle (administrateur ou directeur)
// Le code utilisé est extrait de ./_inc/noyau_socle2016.php
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || ($_SESSION['USER_PROFIL_TYPE']=='directeur') ) && ($type_export=='socle2016_gepi') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom && $cycle_id && $cycle_nom )
{
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead>'.NL.'<tr>'
                   .'<th>Élève</th>'
                   .'<th>1.1</th>'
                   .'<th>1.2</th>'
                   .'<th>1.3</th>'
                   .'<th>1.4</th>'
                   .'<th>&nbsp;2&nbsp;</th>'
                   .'<th>&nbsp;3&nbsp;</th>'
                   .'<th>&nbsp;4&nbsp;</th>'
                   .'<th>&nbsp;5&nbsp;</th>'
                 .'</tr>'.NL.'</thead><tbody>'.NL;
  // Récupérer les données des élèves
  $tab_eleve = array();
  $champs = 'user_id';
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 1 /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'alpha' /*eleves_ordre*/ , $champs );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleve[] = $DB_ROW['user_id'];
    }
  }
  if(!count($tab_eleve))
  {
    Json::end( FALSE , 'Aucun élève trouvé dans ce regroupement !' );
  }
  // Codes des domaines et composantes du socle
  $tab_code_livret = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_socle2016_elements_livret();
  foreach($DB_TAB as $DB_ROW)
  {
    if(!empty($DB_ROW['socle_domaine_code_livret']))
    {
      $tab_code_livret[$DB_ROW['socle_domaine_ordre_livret']] = $DB_ROW['socle_domaine_code_livret'];
    }
    elseif(!empty($DB_ROW['socle_composante_code_livret']))
    {
      $tab_code_livret[$DB_ROW['socle_composante_ordre_livret']] = $DB_ROW['socle_composante_code_livret'];
    }
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  $tab_matiere              = array();
  $groupe_nom               = $groupe_nom;
  $groupe_type              = $groupe_type;
  $eleves_ordre             = 'alpha';
  $socle_detail             = 'livret';
  $cycle_nom                = $cycle_nom;
  $socle_synthese_format    = '';
  $socle_synthese_affichage = '';
  $mode                     = 'auto';
  $aff_socle_items_acquis   = FALSE;
  $aff_socle_position       = FALSE;
  $aff_socle_points_DNB     = FALSE;
  $only_presence            = TRUE;
  $type_individuel          = 0;
  $type_synthese            = 1;
  $make_officiel = FALSE;
  $make_livret   = FALSE;
  $make_action   = '';
  $make_html     = FALSE;
  $make_pdf      = FALSE;
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_socle2016.php');
  // On construit le fichier json à partir des infos maintenant à disposition
  $tab_gepi = array(
    'cycle' => $cycle_id,
    'eleve' => array(),
  );
  $nb_eleves    = 0;
  $nb_positions = 0;
  // Pour chaque élève...
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    if( $tab_contenu_presence['eleve'][$eleve_id] && $tab_eleve['eleve_ID_BE'] )
    {
      extract($tab_eleve); // $eleve_INE $eleve_ID_BE $eleve_nom $eleve_prenom $eleve_genre $date_naissance
      $tab_gepi['eleve'][$eleve_id] = array(
        'id_be'    => $eleve_ID_BE,
        'nom'      => $eleve_nom,
        'prenom'   => $eleve_prenom,
        'position' => array(),
      );
      $export_html .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
      $nb_eleves++;
      // Pour chaque domaine / composante...
      foreach($tab_socle_domaine as $socle_domaine_id => $socle_domaine_nom)
      {
        foreach($tab_socle_composante[$socle_domaine_id] as $socle_composante_id => $socle_composante_nom)
        {
          if($tab_contenu_presence['composante'][$socle_composante_id])
          {
            $tab_bilan = $tab_bilan_eleve_composante[$eleve_id][$socle_composante_id];
            if($tab_bilan['indice'])
            {
              $tab_gepi['eleve'][$eleve_id]['position'][$tab_code_livret[$socle_composante_id]] = $tab_bilan['indice'];
              $export_html .= '<td>'.$tab_bilan['indice'].'</td>';
              $nb_positions++;
            }
            else
            {
              $export_html .= '<td></td>';
            }
          }
          else
          {
            $export_html .= '<td></td>';
          }
        }
      }
      $export_html .= '</tr>'.NL;
    }
  }
  $export_html .= '</tbody></table>'.NL;
  // On enregistre le résultat
  $fichier_contenu = json_encode($tab_gepi);
  $fichier_extension = 'json';
  $fichier_nom = 'socle_import_gepi_'.Clean::fichier($_SESSION['WEBMESTRE_UAI']).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$fichier_extension;
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , $fichier_contenu );
  $fichier_lien = './force_download.php?fichier='.$fichier_nom;
  // Afficher le retour
  $se = ($nb_eleves>1)    ? 's' : '' ;
  $sp = ($nb_positions>1) ? 's' : '' ;
  Json::add_str('<ul class="puce">'.NL);
  Json::add_str('<li><label class="valide">Fichier d\'export généré : '.$nb_positions.' positionnement'.$sp.' concernant '.$nb_eleves.' élève'.$se.'.</label></li>'.NL);
  Json::add_str('<li><a target="_blank" rel="noopener noreferrer" href="'.$fichier_lien.'"><span class="file file_'.$fichier_extension.'">Récupérer le fichier au format <em>'.$fichier_extension.'</em>.</span></a></li>'.NL);
  Json::add_str('<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>'.NL);
  Json::add_str('</ul>'.NL);
  Json::add_str($export_html);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas arriver jusque là.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
