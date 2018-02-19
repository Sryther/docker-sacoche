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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( Clean::map('entier', explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action']) : '';  // "exporter" ou "importer_csv" ou "importer_zip" ou "importer"
$num    = (isset($_POST['num']))       ? (int)$_POST['num']              : 0 ;  // Numéro de l'étape en cours
$max    = (isset($_POST['max']))       ? (int)$_POST['max']              : 0 ;  // Nombre d'étapes à effectuer

$file_memo = CHEMIN_DOSSIER_EXPORT.'webmestre_structure_transfert_'.session_id().'.txt';

if( ( ($action=='exporter') && $nb_bases ) || ($action=='importer_csv') || (!isset($_SESSION['alea'])) )
{
  $_SESSION['datetime'] = date('Y-m-d_H-i-s');
  $_SESSION['alea']     = mt_rand();
}

$dossier_temp_sql = CHEMIN_DOSSIER_DUMP.$_SESSION['alea'].'_sql'.DS; // Pour les sql d'une base
$dossier_temp_zip = CHEMIN_DOSSIER_DUMP.$_SESSION['alea'].'_zip'.DS; // Pour les zip des sql des bases (à l'export mais pas à l'import sinon ce dossier n'est pas vidé si l'opération n'arrive pas à son terme).
$fichier_csv_nom  = 'bases_dump_'.$_SESSION['datetime'].'_'.$_SESSION['alea'].'.csv';
$fichier_zip_nom  = 'bases_dump_'.$_SESSION['datetime'].'_'.$_SESSION['alea'].'.zip';

$separateur = ';';

require(CHEMIN_DOSSIER_INCLUDE.'fonction_dump.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des structures avant export des bases
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='exporter') && $nb_bases )
{
  // Mémoriser dans un fichier les données des structures concernées par les stats
  $fichier_texte = 'Id_Export'.$separateur.'Id_Import'.$separateur.'Id_Zone'.$separateur.'Localisation'.$separateur.'Dénomination'.$separateur.'UAI'.$separateur.'Contact_Nom'.$separateur.'Contact_Prénom'.$separateur.'Contact_Courriel'.$separateur.'Date_Inscription'.$separateur.'Nom_fichier'."\r\n";
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( implode(',',$tab_base_id) );
  foreach($DB_TAB as $DB_ROW)
  {
    $fichier_nom = 'dump_SACoche_'.$DB_ROW['sacoche_base'].'_'.$_SESSION['datetime'].'_'.mt_rand().'.zip';
    $fichier_texte .= $DB_ROW['sacoche_base'].$separateur.$separateur.$DB_ROW['geo_id'].$separateur.$DB_ROW['structure_localisation'].$separateur.$DB_ROW['structure_denomination'].$separateur.$DB_ROW['structure_uai'].$separateur.$DB_ROW['structure_contact_nom'].$separateur.$DB_ROW['structure_contact_prenom'].$separateur.$DB_ROW['structure_contact_courriel'].$separateur.$DB_ROW['structure_inscription_date'].$separateur.$fichier_nom."\r\n";
  }
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_EXPORT.$fichier_csv_nom,$fichier_texte);
  $max = $nb_bases + 1 ; // La dernière étape consiste à zipper les fichiers de sauvegarde et à faire le ménage.
  // Créer ou vider le dossier temporaire qui contiendra le zip des zip
  FileSystem::creer_ou_vider_dossier($dossier_temp_zip);
  Json::end( TRUE , $max );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape d'export d'une base
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='exporter') && $num && $max && ($num<$max) )
{
  // Récupérer la ligne de données
  $fichier_texte = file_get_contents(CHEMIN_DOSSIER_EXPORT.$fichier_csv_nom);
  $tab_ligne = explode("\r\n",$fichier_texte);
  // Récupérer une série d'infos, sachant que seuls $export_id et $fichier_nom sont utiles
  list( $export_id, $import_id, $geo_id, $localisation, $denomination, $uai, $contact_nom, $contact_prenom, $contact_courriel, $date, $fichier_nom ) = explode( $separateur , $tab_ligne[$num] );
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  DBextra::charger_parametres_mysql_supplementaires($export_id);
  // Créer ou vider le dossier temporaire des sql
  FileSystem::creer_ou_vider_dossier($dossier_temp_sql);
  // Bloquer l'application
  LockAcces::bloquer_application( 'automate' , $export_id , 'Sauvegarde de la base en cours.' );
  // Remplir le dossier temporaire avec les fichiers de svg des tables
  sauvegarder_tables_base_etablissement($dossier_temp_sql,0);
  // Débloquer l'application
  LockAcces::debloquer_application( 'automate' , $export_id );
  // Zipper les fichiers de svg
  $result = FileSystem::zip_fichiers($dossier_temp_sql,$dossier_temp_zip,$fichier_nom);
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Appel suivant
  Json::end( TRUE );
}
elseif( ($action=='exporter') && $num && $max && ($num==$max) )
{
  // Supprimer le dossier temporaire des sql
  FileSystem::supprimer_dossier($dossier_temp_sql);
  // Zipper les zip de svg
  $result = FileSystem::zip_fichiers( $dossier_temp_zip , CHEMIN_DOSSIER_DUMP , $fichier_zip_nom );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Supprimer le dossier temporaire des zip
  FileSystem::supprimer_dossier($dossier_temp_zip);
  // Game over
  unset($_SESSION['datetime'],$_SESSION['alea']);
  Json::end( TRUE , array( 'csv' => URL_DIR_EXPORT.$fichier_csv_nom , 'zip' => URL_DIR_DUMP.$fichier_zip_nom ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Import d'un fichier CSV avec le listing des bases exportées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='importer_csv')
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_csv_nom /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // On récupère les zones géographiques pour vérifier que l'identifiant transmis est cohérent
  $tab_geo = array();
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_zones();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_geo[$DB_ROW['geo_id']] = TRUE;
  }
  // Tester si le contenu est correct, et mémoriser les infos en session
  // Extraire les lignes du fichier
  $tab_lignes = FileSystem::extraire_lignes_csv(CHEMIN_DOSSIER_IMPORT.$fichier_csv_nom);
  // Supprimer la 1e ligne
  unset($tab_lignes[0]);
  $tab_nouvel_uai = array();
  $tab_nouvel_id  = array();
  $nb_lignes_trouvees = 0;
  $tab_erreur = array(
    'info'    => array('nb'=>0,'txt'=>' manquant d\'informations !') ,
    'geo'     => array('nb'=>0,'txt'=>' avec identifiant géographique incorrect !') ,
    'uai'     => array('nb'=>0,'txt'=>' avec UAI déjà présent ou en double ou incorrect !') ,
    'mail'    => array('nb'=>0,'txt'=>' avec adresse de courriel incorrecte !') ,
    'date'    => array('nb'=>0,'txt'=>' avec date d\'inscription incorrecte !') ,
    'fichier' => array('nb'=>0,'txt'=>' avec nom de fichier de sauvegarde incorrect !') ,
    'id'      => array('nb'=>0,'txt'=>' avec identifiant de base déjà utilisé ou en double !') ,
  );
  foreach ($tab_lignes as $tab_elements)
  {
    $tab_elements = array_slice($tab_elements,0,11);
    if(count($tab_elements)==11)
    {
      $nb_lignes_trouvees++;
      list( $export_id, $import_id, $geo_id, $localisation, $denomination, $uai, $contact_nom, $contact_prenom, $contact_courriel, $date, $fichier_nom ) = $tab_elements;
      $import_id        = Clean::entier($import_id);
      $geo_id           = Clean::entier($geo_id);
      $localisation     = Clean::texte($localisation);
      $denomination     = Clean::texte($denomination);
      $uai              = Clean::uai($uai);
      $contact_nom      = Clean::nom($contact_nom);
      $contact_prenom   = Clean::prenom($contact_prenom);
      $contact_courriel = Clean::courriel($contact_courriel);
      $tab_memo[$nb_lignes_trouvees] = array(
        'import_id'        => $import_id ,
        'geo_id'           => $geo_id ,
        'localisation'     => $localisation ,
        'denomination'     => $denomination ,
        'uai'              => $uai ,
        'contact_nom'      => $contact_nom ,
        'contact_prenom'   => $contact_prenom ,
        'contact_courriel' => $contact_courriel ,
        'date'             => $date ,
        'fichier_nom'      => $fichier_nom ,
      );
      // Vérifier la présence des informations
      if( !$geo_id || !$localisation || !$denomination || !$contact_nom || !$contact_prenom || !$contact_courriel || !$date || !$fichier_nom )
      {
        $tab_erreur['info']['nb']++;
      }
      // Vérifier que l'id géographique est correct
      if(!isset($tab_geo[$geo_id]))
      {
        $tab_erreur['geo']['nb']++;
      }
      // Vérifier que le n°UAI est disponible
      if($uai)
      {
        if( (!Outil::tester_UAI($uai)) || (isset($tab_nouvel_uai[$uai])) || DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_UAI($uai) )
        {
          $tab_erreur['uai']['nb']++;
        }
        $tab_nouvel_uai[$uai] = TRUE;
      }
      // Vérifier que l'adresse de courriel est correcte
      if(!Outil::tester_courriel($contact_courriel))
      {
        $tab_erreur['mail']['nb']++;
      }
      // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
      list($mail_domaine,$is_domaine_valide) = Outil::tester_domaine_courriel_valide($contact_courriel);
      if(!$is_domaine_valide)
      {
        $tab_erreur['mail']['nb']++;
      }
      // Vérifier que la date est correcte
      if(!Outil::tester_date($date))
      {
        $tab_erreur['date']['nb']++;
      }
      // Vérifier que le nom de fichier est cohérent
      if( (substr($fichier_nom,0,13)!='dump_SACoche_') || (substr($fichier_nom,-4)!='.zip') )
      {
        $tab_erreur['fichier']['nb']++;
      }
      // Vérifier que l'identifiant est disponible
      if($import_id)
      {
        if((isset($tab_nouvel_id[$import_id])) || (DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_Id($import_id)!==NULL) )
        {
          $tab_erreur['id']['nb']++;
        }
        $tab_nouvel_id[$import_id] = TRUE;
      }
    }
  }
  FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_csv_nom);
  if(!$nb_lignes_trouvees)
  {
    Json::end( FALSE , 'Aucune ligne du fichier ne semble correcte !' );
  }
  $info_lignes_trouvees = ($nb_lignes_trouvees>1) ? $nb_lignes_trouvees.' lignes trouvées' : '1 ligne trouvée' ;
  foreach($tab_erreur as $key => $tab)
  {
    if($tab['nb'])
    {
      $s = ($tab['nb']>1) ? 's' : '' ;
      Json::end( FALSE , $info_lignes_trouvees.' mais '.$tab['nb'].' ligne'.$s.$tab['txt'] );
    }
  }
  // Nettoyer des restes d'upload de zip éventuels
  foreach($tab_memo as $key => $tab_infos)
  {
    FileSystem::supprimer_fichier( CHEMIN_DOSSIER_DUMP.$tab_infos['fichier_nom'] , TRUE /*verif_exist*/ );
  }
  // Enregistrer ces informations
  FileSystem::enregistrer_fichier_infos_serializees( $file_memo , $tab_memo );
  // Retour
  Json::end( TRUE , $info_lignes_trouvees );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Import d'un fichier ZIP avec le fichier des bases sauvegardées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='importer_zip')
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_zip_nom /*fichier_nom*/ , array('zip') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Dezipper dans le dossier dump (pas dans un sous-dossier "temporaire" sinon ce dossier n'est pas vidé si l'opération n'arrive pas à son terme).
  $code_erreur = FileSystem::unzip( CHEMIN_DOSSIER_IMPORT.$fichier_zip_nom , CHEMIN_DOSSIER_DUMP , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , 'Erreur d\'extraction du contenu ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_zip_nom);
  // Vérifier le contenu : noms des fichiers
  $tab_fichier = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_DUMP);
  $nb_fichiers_introuvables = 0;
  foreach($tab_memo as $key => $tab_infos)
  {
    if(!in_array($tab_infos['fichier_nom'],$tab_fichier))
    {
      $nb_fichiers_introuvables++;
    }
  }
  if($nb_fichiers_introuvables)
  {
    $s = ($nb_fichiers_introuvables>1) ? 's' : '' ;
    Json::end( FALSE , $nb_fichiers_introuvables.' fichier'.$s.' référencé'.$s.' dans le CSV non trouvé'.$s.' dans le ZIP !' );
  }
  // La dernière étape consiste seulement à faire le ménage.
  $max = count($tab_memo) + 1 ;
  Json::end( TRUE , $max );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape d'import d'une base
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='importer') && $num && $max && ($num<$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Récupérer la série d'infos
  extract($tab_memo[$num]); // import_id / geo_id / localisation / denomination / uai / contact_nom / contact_prenom / contact_courriel / date / fichier_nom
  // Préparer le retour en cas de pb
  $retour_cellules_non = '<td class="nu"></td><td>---</td><td>'.html($localisation.' | '.$denomination).'</td><td>'.html($contact_nom.' '.$contact_prenom).'</td>';
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp_sql);
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( CHEMIN_DOSSIER_DUMP.$fichier_nom , $dossier_temp_sql , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , '<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : fichiers de '.html($fichier_nom).' impossible à extraire ('.FileSystem::$tab_zip_error[$code_erreur].') !</label></td>'.'</tr>' );
  }
  // Vérifier le contenu : noms des fichiers
  $fichier_taille_maximale = verifier_dossier_decompression_sauvegarde($dossier_temp_sql);
  if(!$fichier_taille_maximale)
  {
    FileSystem::supprimer_dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , '<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : le contenu de '.html($fichier_nom).' ne semble pas être une sauvegarde de base !</label></td>'.'</tr>' );
  }
  // Vérifier le contenu : taille des requêtes
  if( !verifier_taille_requetes($fichier_taille_maximale) )
  {
    FileSystem::supprimer_dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , '<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : '.html($fichier_nom).' contient au moins un fichier dont la taille ('.FileSystem::afficher_fichier_taille($fichier_taille_maximale).') dépasse la limitation <em>max_allowed_packet</em> de MySQL !</label></td>'.'</tr>' );
  }
  // Vérifier le contenu : version de la base compatible avec la version logicielle
  $version_base_svg = version_base_fichier_svg($dossier_temp_sql);
  if( $version_base_svg > VERSION_BASE_STRUCTURE )
  {
    FileSystem::supprimer_dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , '<tr>'.$retour_cellules_non.'<td><label class="erreur">Erreur : '.html($fichier_nom).' contient une version de base plus récente ('.$version_base_svg.') que celle supportée par cette installation ! Il faut mettre à jour SACoche.</label></td>'.'</tr>' );
  }
  // Insérer l'enregistrement dans la base du webmestre
  // Créer le fichier de connexion de la base de données de la structure
  // Créer la base de données de la structure
  // Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
  $base_id = Webmestre::ajouter_structure( $import_id , $geo_id , $uai , $localisation , $denomination , $contact_nom , $contact_prenom , $contact_courriel , $date );
  // Créer les dossiers de fichiers temporaires par établissement
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier)
  {
    FileSystem::creer_dossier($dossier.$base_id);
    FileSystem::ecrire_fichier_index($dossier.$base_id);
  }
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  DBextra::charger_parametres_mysql_supplementaires($base_id);
  // Restaurer des fichiers de svg et mettre la base à jour si besoin.
  $texte_etape = restaurer_tables_base_etablissement($dossier_temp_sql,0);
  // Supprimer le dossier temporaire
  FileSystem::supprimer_dossier($dossier_temp_sql);
  // Retour du succès, appel suivant
  $retour_cellules_oui = '<td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td><td class="label">'.$base_id.'</td><td class="label">'.html($localisation.' | '.$denomination.' ['.$uai.']').'</td><td class="label">'.html($contact_nom.' '.$contact_prenom.' ('.$contact_courriel.')').'</td>';
  Json::end( TRUE , '<tr>'.$retour_cellules_oui.'<td class="label"><label class="valide">'.$texte_etape.' avec succès.</label></td>'.'</tr>' );
}
elseif( ($action=='importer') && $num && $max && ($num==$max) )
{
  // Récupérer les informations
  $tab_memo = FileSystem::recuperer_fichier_infos_serializees( $file_memo );
  // Supprimer les fichiers zip des bases
  foreach($tab_memo as $key => $tab_infos)
  {
    FileSystem::supprimer_fichier( CHEMIN_DOSSIER_DUMP.$tab_infos['fichier_nom'] , TRUE /*verif_exist*/ );
  }
  // Supprimer les informations provisoires
  FileSystem::supprimer_fichier( $file_memo );
  unset($_SESSION['datetime'],$_SESSION['alea']);
  // Retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer plusieurs structures existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $nb_bases )
{
  foreach($tab_base_id as $base_id)
  {
    Webmestre::supprimer_multi_structure($base_id);
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
