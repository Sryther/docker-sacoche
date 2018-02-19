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

// On pourrait essayer de verifier que l'appel est légitime. Mais :
// - on ne peut utiliser $_SERVER['HTTP_REFERER'] qui n'est pas une variable de confiance
// - ouvrir et vérifier la session pour chaque image serait lourd
// - passer des paramètres supplémentaires (genre id session + repère temporel) complexifie pour pas grand chose
// Du coup j'ai laissé tel quel. De toutes façons les paramètres sont nettoyés, et utiliser ce fichier depuis l'extérieur n'a pas vraiment d'intérêt...

/**
 * Fabrique, ou récupère si elle existe, une image png inclinée à 90° à partir du nom et du prénom de l'élève.
 * Pour tourner le texte de 90° il y a 2 solutions :
 * 1. mettre à 90 le 3e paramètre de imagettftext() ; problème -> il y a des décalages par exemple quand le prénom comporte un "y"
 * 2. utiliser imagerotate() ; problème -> on perd la transparence alpha car le 4e paramètre de imagerotate() ne passe pas sur les png
 * Une issue à consister à utiliser la méthode 2 en ajoutant ensuite imagealphablending() et imagesavealpha() [http://fr.php.net/manual/fr/function.imagerotate.php#64531]
 * Et il faut aussi appeler ces deux fonctions au chargement de l'image déjà créée...
 * Mais un nouveau problème apparait : certains serveurs (celui de Montpellier par exemple) gèrent mal ces fonctions de transparence (pour des librairies GD identiques...).
 * Du coup, comme les images sont de toutes façons toujours utilisées sur un même fond (CCCCFF / 204:204:255), le plus simple (et la solution) a été de renoncer à utiliser la transparence.
 * Donc imagecolorallocatealpha() a été remplacé par imagecolorallocate() et imagealphablending($image_finale, false); + imagesavealpha($image_finale, true); ont été retirés.
 */

// Ce fichier est directement appelé par le navigateur et n'inclut pas tous les fichiers habituels.
header("Content-type: image/png");

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('../../_inc/_loader.php');
// Non chargé par le loader dont on ne prend que le début
define(     'NOM_LONGUEUR_MAX',  30);
define(  'PRENOM_LONGUEUR_MAX',  30);
require(CHEMIN_DOSSIER_INCLUDE.'class.Clean.php');
require(CHEMIN_DOSSIER_INCLUDE.'class.Image.php');

$dossier   = isset($_GET['dossier']) ? Clean::entier($_GET['dossier']) : 'x' ;
$nom       = isset($_GET['nom'])     ? Clean::nom($_GET['nom'])        : ' ' ;
$prenom    = isset($_GET['prenom'])  ? Clean::prenom($_GET['prenom'])  : ' ' ;
$item      = isset($_GET['item'])    ? Clean::texte($_GET['item'])     : '' ;
$br_line   = isset($_GET['br'])      ? 2                               : 1 ; // 2 pour nom / retour à la ligne / prénom ; 1 pour nom / prénom à la suite
$font_size = isset($_GET['size'])    ? Clean::entier($_GET['size'])    : 10 ;

$chemin  = CHEMIN_DOSSIER_BADGE.$dossier.DS;
$debut   = ($item) ? $item : $nom.'_'.$prenom ;
$fichier = $chemin.Clean::login($debut).'_'.$br_line.'_'.$font_size.'.png';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Créer l'image si elle n'existe pas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!file_exists($fichier))
{
  // S'assurer que le dossier, lui, existe bien au moins, sinon ce n'est pas la peine de poursuivre
  if(!is_dir($chemin))
  {
    header('Status: 404 Not Found', true, 404);
    exit();
  }
  // On commence par créer une image temporaire plus large et plus haute que nécessaire
  $interligne     = $font_size*1.2;
  $hauteur_tmp    = $font_size*2*$br_line;
  $largeur_tmp    = $font_size*40;
  // imagecreatetruecolor() n'est pas utilisé ici sinon imagecopy() utilisé ensuite peut faire apparaitre une bande noire...
  // $image_tmp      = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($largeur_tmp,$hauteur_tmp) : imagecreate($largeur_tmp,$hauteur_tmp) ;
  $image_tmp      = imagecreate($largeur_tmp,$hauteur_tmp) ;
  $couleur_fond   = imagecolorallocate($image_tmp,221,221,255); // Le premier appel à imagecolorallocate() remplit la couleur de fond si imagecreate().
  $couleur_fill   = imagefill($image_tmp, 0, 0, $couleur_fond); // Si imagecreatetruecolor(), l'image est noire et il faut la remplir explicitement.
  $couleur_texte  = imagecolorallocate($image_tmp,0,0,0);
  $police         = './arial.ttf'; // Dans le même dossier que ce script.
  // imagettftext() : 3e param = angle de rotation ; 4e et 5e param = coordonnées du coin inférieur gauche du premier caractère
  if($br_line==1)
  {
    $texte = ($item) ? $item : $nom.' '.$prenom ;
    $b_g_y_demande = $hauteur_tmp - 5 ;
    list($b_g_x , $b_g_y , $b_d_x , $b_d_y , $h_d_x , $h_d_y , $h_g_x , $h_g_y) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$texte);
  }
  else
  {
    // en deux fois au lieu d'utiliser $nom."\r\n".$prenom car l'interligne est sinon trop important
    $b_g_y_demande = 5 + $interligne ;
    list($b_g_x1 , $delete , $b_d_x1 , $delete , $h_d_x1 , $h_d_y1 , $h_g_x1 , $h_g_y ) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$nom);
    $b_g_y_demande += $interligne ;
    list($b_g_x2 , $b_g_y2 , $b_d_x2 , $b_d_y  , $h_d_x2 , $delete , $h_g_x2 , $delete) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$prenom);
    $b_d_x = max($b_d_x1,$b_d_x2);
    $h_g_x = min($h_g_x1,$h_g_x2);
  }
  // Maintenant on peut connaître les dimensions de l'image finale
  $largeur_finale = $b_d_x - $h_g_x + 3 ; // +2 car 1px de marge en bordure
  $hauteur_finale = $b_d_y - $h_g_y + 2 ; // idem
  // Les caractères minuscules parmi g,j,p,q,y provoquent un décalage non pris en compte par imagettftext()
  // sur certains serveurs pour des librairies gd pourtant rigoureusement identiques (gd_info() renvoyant [GD Version] => bundled 2.0.34 compatible).
  // On peut lire qu'appeler avant imagealphablending() est censé réglé le problème [http://fr.php.net/manual/fr/function.imagettftext.php#100184], mais un décalage demeure.
  // Enfin, sur les serveurs qui prennent en compte le décalage, il est d'1px trop grand.
  $test_pb_serveur = ($b_g_y_demande == $b_d_y) ? true : false ;
  $prenom_amoindri = str_replace( array('g','j','p','q','y') , '' , mb_substr($prenom,1) , $test_pb_lettres );
  if($test_pb_lettres && !$test_pb_serveur)
  {
    if($br_line==2) {$hauteur_finale -= 1;}
    if($br_line==1) {$h_g_y -= 1;}
  }
  elseif($test_pb_lettres && $test_pb_serveur)
  {
    if($br_line==2) {$hauteur_finale += 3;}
    if($br_line==1) {$h_g_y += 3;}
  }
  // Dans le cas d'une seule colonne, prévoir exactement 15px de hauteur (donc de largeur une fois tourné, ce qui centre le texte et normalise la largeur de la colonne).
  if($br_line==1)
  {
    $marge_complementaire = 15 - $hauteur_finale;
    if($marge_complementaire)
    {
      $arrondi_pair = ($test_pb_lettres) ? 1 : -1 ;
      $h_g_y -= ($marge_complementaire%2) ? ($marge_complementaire+$arrondi_pair)/2 : $marge_complementaire/2 ;
      $hauteur_finale = 15;
    }
  }
  // Créer l'image finale aux bonnes dimensions
  $image_finale = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($largeur_finale,$hauteur_finale) : imagecreate($largeur_finale,$hauteur_finale) ;
  // imagettftext() : 3e et 4e param = coordonnées du point de destination ; 5e et 6e param = coordonnées du point source
  imagecopy($image_finale,$image_tmp,0,0,$h_g_x-1,$h_g_y-1,$largeur_finale,$hauteur_finale);
  imagedestroy($image_tmp);
  // Tourner l'image de 90°
  $image_finale = Image::imagerotateEmulation($image_finale);
  // L'enregistrer comme fichier
  imagepng($image_finale,$fichier);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer l'image si elle existe déjà
// ////////////////////////////////////////////////////////////////////////////////////////////////////

else
{
  $image_finale = imagecreatefrompng($fichier);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Envoyer l'image au navigateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

imagepng($image_finale);
imagedestroy($image_finale);

?>