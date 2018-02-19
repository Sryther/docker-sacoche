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

// Fichier appelé pour générer et afficher une archive PDF d'un bilan officiel.
// Passage en GET d'un paramètre pour savoir quelle archive est concernée.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
require(CHEMIN_FICHIER_CONFIG_INSTALL);

// Ouverture de la session et gestion des droits d'accès
if(!Session::recuperer_droit_acces(SACoche))
{
  exit_error( 'Droits manquants' /*titre*/ , 'Droits de la page "'.SACoche.'" manquants.<br />Les droits de cette page n\'ont pas été attribués dans le fichier "'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php').'".' /*contenu*/ , '' /*lien*/ );
}
Session::execute();

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');
require(CHEMIN_DOSSIER_INCLUDE.'fonction_livret.php'); // Pour elements_programme_extraction()

// Paramètre transmis
$officiel_archive_id = (isset($_GET['id'])) ? Clean::entier($_GET['id']) : 0 ;

// Vérifications
if(!$officiel_archive_id)
{
  exit_error( 'Paramètre manquant' /*titre*/ , 'Page appelée sans indiquer la référence de l\'archive PDF à récupérer.' /*contenu*/ , '' /*lien*/ );
}
if( !isset($_SESSION['tmp_droit_voir_archive'][$officiel_archive_id]) || !isset($_SESSION['BASE']) )
{
  exit_error( 'Accès non autorisé' /*titre*/ , 'Cet appel n\'est valide que pour un utilisateur précis, connecté, et ayant affiché la page listant les archives disponibles.<br />Veuillez ne pas appeler ce lien dans un autre contexte (ni le transmettre à un tiers).' /*contenu*/ , '' /*lien*/ );
}

// Connexion à la base de données adaptée (à ce stade, plus besoin de vérif, il s'agit d'une install bien en place...).
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  $fichier_mysql_config = 'serveur_sacoche_structure_'.$_SESSION['BASE'];
  $fichier_class_config = 'class.DB.config.sacoche_structure';
}
elseif(HEBERGEUR_INSTALLATION=='mono-structure')
{
  $fichier_mysql_config = 'serveur_sacoche_structure';
  $fichier_class_config = 'class.DB.config.sacoche_structure';
}
// Chargement du fichier de connexion à la BDD
require(CHEMIN_DOSSIER_MYSQL.$fichier_mysql_config.'.php');
require(CHEMIN_DOSSIER_INCLUDE.$fichier_class_config.'.php');

// Récupération de l'archive
$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_archive_precise( $officiel_archive_id );
if(empty($DB_ROW))
{
  exit_error( 'Paramètre manquant' /*titre*/ , 'Archive n°'.$officiel_archive_id.' non trouvée.' /*contenu*/ , '' /*lien*/ );
}

// Ajout sur le document de la mention "Vu et pris connaissance"
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && isset($_SESSION['ENFANT_NUM_RESP'][$DB_ROW['user_id']]) )
{
  $num_resp = $_SESSION['ENFANT_NUM_RESP'][$DB_ROW['user_id']];
  $find_me  = '"resp'.$num_resp.'":null';
  if( strpos( $DB_ROW['archive_contenu'] , $find_me ) )
  {
    $replace_by = '"resp'.$num_resp.'":'.json_encode('Pris connaissance le '.TODAY_FR.' : '.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],FALSE,$_SESSION['USER_GENRE']));
    $DB_ROW['archive_contenu'] = str_replace( $find_me , $replace_by , $DB_ROW['archive_contenu'] );
    DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_archive_consultation( $officiel_archive_id , $DB_ROW['archive_contenu'] );
  }
}

// Enregistrement de la date d'accès
if( in_array( $_SESSION['USER_PROFIL_TYPE'] , array('eleve','parent') ) && is_null($DB_ROW['archive_date_consultation_'.$_SESSION['USER_PROFIL_TYPE']]) )
{
  DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_archive_date_consultation( $officiel_archive_id , $_SESSION['USER_PROFIL_TYPE'] );
}

// Remplacement des md5 par les images
for( $image_num=1 ; $image_num<=4 ; $image_num++)
{
  $image_md5 = $DB_ROW['archive_md5_image'.$image_num];
  if( $image_md5 )
  {
    $image_base64 = $DB_ROW['image'.$image_num.'_contenu'];
    $DB_ROW['archive_contenu'] = str_replace( $image_md5 , $image_base64 , $DB_ROW['archive_contenu'] );
  }
}

// Instanciation de la classe
$tab_classname = array(
  'livret'   => 'PDF_livret_scolaire',
  'bulletin' => 'PDF_item_synthese',
  'releve'   => 'PDF_item_releve',
);
$key = ($DB_ROW['archive_type']=='sacoche') ? $DB_ROW['archive_ref'] : 'livret' ;
$classname = $tab_classname[$key];
$archive_PDF = new $classname();

// Fabrication de l'archive PDF
$tab_archive = json_decode($DB_ROW['archive_contenu'], TRUE);
foreach($tab_archive as $archive)
{
  list( $methode , $tab_param ) = $archive;
  call_user_func_array( array( $archive_PDF , $methode ) , $tab_param );
}

// Écriture du PDF
$fichier_nom     = 'archive_'.Clean::fichier($DB_ROW['structure_uai']).'_'.Clean::fichier($DB_ROW['annee_scolaire']).'_'.$DB_ROW['archive_type'].'_'.$DB_ROW['archive_ref'].'_'.Clean::fichier($DB_ROW['periode_nom']).'_'.Clean::fichier($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
$fichier_fin_ext = '_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.pdf';
FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.$fichier_fin_ext , $archive_PDF  );

// Redirection du navigateur
header('Status: 302 Found', TRUE, 302);
header('Location: '.URL_DIR_EXPORT.$fichier_nom.$fichier_fin_ext);
header('Content-Length: 0'); // Varnish (le frontal web), en cas de redirection dans le header HTTP avec un body HTTP vide, envoie le header tout de suite mais attends 5s un éventuel body avant de couper la connexion si la réponse ne précise pas sa taille ; or Firefox, qui reçoit une redirection, dit au serveur de fermer la connexion TCP et attend la réponse à cette demande de fermeture avant de suivre la redirection ; moralité, quand on fait une redirection via les headers il faudrait toujours ajouter 'Content-Length: 0'
exit();
?>
