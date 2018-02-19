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

$action = (isset($_POST['f_action'])) ? $_POST['f_action']              : '';
$mode   = (isset($_POST['f_mode']))   ? $_POST['f_mode']                : '';
$STEP   = (isset($_POST['f_step']))   ? Clean::entier($_POST['f_step']) : 0;

$tab_action = array(
  'siecle_commun_oui'                 => array('siecle'  ,'commun'      ),
  'siecle_nomenclature_oui'           => array('siecle'  ,'nomenclature'),
  'siecle_professeurs_directeurs_oui' => array('siecle'  ,'professeur'  ),
  'siecle_eleves_oui'                 => array('siecle'  ,'eleve'       ),
  'siecle_parents_oui'                => array('siecle'  ,'parent'      ),
  'onde_eleves'                       => array('onde'    ,'eleve'       ),
  'onde_parents'                      => array('onde'    ,'parent'      ),
  'factos_eleves'                     => array('factos'  ,'eleve'       ),
  'factos_parents'                    => array('factos'  ,'parent'      ),
  'tableur_professeurs_directeurs'    => array('tableur' ,'professeur'  ),
  'tableur_eleves'                    => array('tableur' ,'eleve'       ),
  'tableur_parents'                   => array('tableur' ,'parent'      ),
);

$tab_step = array(
  10 => "Récupération du fichier (tous les cas)",
  20 => "Extraction des données (tous les cas)",
  31 => "Analyse des données des classes (siecle_professeurs_directeurs | siecle_eleves | onde_eleves | factos_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  32 => "Traitement des actions à effectuer sur les classes (siecle_professeurs_directeurs | siecle_eleves | onde_eleves | factos_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  41 => "Analyse des données des groupes (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  42 => "Traitement des actions à effectuer sur les groupes (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  51 => "Analyse des données des utilisateurs (tous les cas)",
  52 => "Traitement des actions à effectuer sur les utilisateurs (tous les cas)",
  53 => "Récupérer les identifiants des nouveaux utilisateurs (tous les cas)",
  61 => "Modification d'affectations éventuelles (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  62 => "Traitement des ajouts d'affectations éventuelles (siecle_professeurs_directeurs | siecle_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  71 => "Adresses des parents (siecle_parents | onde_parents | factos_parents | tableur_parents)",
  72 => "Traitement des ajouts/modifications d'adresses éventuelles (siecle_parents | onde_parents | factos_parents | tableur_parents)",
  81 => "Liens de responsabilités des parents (siecle_parents | onde_parents | factos_parents | tableur_parents)",
  82 => "Traitement des liens de responsabilités des parents (siecle_parents | onde_parents | factos_parents | tableur_parents)",
  90 => "Nettoyage des fichiers temporaires (tous les cas)",
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifications / Initialisations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !isset($tab_action[$action]) || !isset($tab_step[$STEP]) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

list( $import_origine , $import_profil ) = $tab_action[$action];

$tab_extensions_autorisees = ($import_origine=='siecle') ? array('zip','xml') : array('txt','csv') ;
$extension_fichier_dest    = ($import_origine=='siecle') ? 'xml'              : 'txt' ;
$fichier_nom_debut   = 'import_'.$import_origine.'_'.$import_profil.'_'.FileSystem::generer_nom_structure_session().'_';
$fichier_dest_nom    = $fichier_nom_debut.'import.'.$extension_fichier_dest ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Quelques fonctions utiles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_vivantes.php');

function afficher_etapes($import_origine,$import_profil)
{
  $puces = '<ul id="step">'.NL;
  switch($import_origine.'+'.$import_profil)
  {
    case  'siecle+commun' :
    case  'siecle+nomenclature' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step9">Étape 3 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case  'siecle+professeur' :
    case 'tableur+professeur' :
    case  'siecle+eleve'      :
    case 'tableur+eleve'      :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step4">Étape 4 - Groupes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 5 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step6">Étape 6 - Affectations (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 7 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case      'siecle+parent' :
    case        'onde+parent' :
    case      'factos+parent' :
    case     'tableur+parent' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step5">Étape 3 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step7">Étape 4 - Adresses (ajouts / modifications)</li>'.NL;
      $puces .= '<li id="step8">Étape 5 - Responsabilités (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 6 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case         'onde+eleve' :
    case       'factos+eleve' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 4 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 5 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
  }
  $puces .= '</ul>'.NL;
  return $puces;
}

function aff_champ( $profil , $type , $val )
{
  global $tab_langues;
  if( ($type=='lv1') || ($type=='lv2') )
  {
    return $tab_langues[$val]['texte'];
  }
  else if($type!='genre')
  {
    return html($val);
  }
  else if($profil=='eleve')
  {
    return Html::$tab_genre['enfant'][$val];
  }
  else
  {
    return Html::$tab_genre['adulte'][$val];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Le fichier étant très gros (>175Ko) on l'a découpé en morceaux plus digestes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

require(CHEMIN_DOSSIER_PAGES.$PAGE.'.ajax.step'.$STEP.'.php');
Json::end( TRUE );

?>
