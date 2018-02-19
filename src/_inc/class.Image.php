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

class Image
{

  /**
   * Renvoyer les dimensions d'une image à mettre dans les attributs HTML si on veut limiter son affichage à une largeur / hauteur données.
   * 
   * @param int   $largeur_reelle
   * @param int   $hauteur_reelle
   * @param int   $largeur_maxi
   * @param int   $hauteur_maxi
   * @return array   [$largeur_imposee,$hauteur_imposee]
   */
  public static function dimensions_affichage($largeur_reelle,$hauteur_reelle,$largeur_maxi,$hauteur_maxi)
  {
    if( ($largeur_reelle>$largeur_maxi) || ($hauteur_reelle>$hauteur_maxi) )
    {
      $coef_reduction_largeur = $largeur_maxi/$largeur_reelle;
      $coef_reduction_hauteur = $hauteur_maxi/$hauteur_reelle;
      $coef_reduction = min($coef_reduction_largeur,$coef_reduction_hauteur);
      $largeur_imposee = round($largeur_reelle*$coef_reduction);
      $hauteur_imposee = round($hauteur_reelle*$coef_reduction);
      return array($largeur_imposee,$hauteur_imposee);
    }
    return array($largeur_reelle,$hauteur_reelle);
  }

  /**
   * imagerotateEmulation
   *
   * La fonction imagerotate() n'est disponible que si PHP est compilé avec la version embarquée de la bibliothèque GD. 
   *
   * @param resource   $image_depart
   * @return resource
   */
  public static function imagerotateEmulation($image_depart)
  {
    if(function_exists("imagerotate"))
    {
      return imagerotate($image_depart,90,0);
    }
    else
    {
      $largeur = imagesx($image_depart);
      $hauteur = imagesy($image_depart);
      $image_tournee = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($hauteur,$largeur) : imagecreate($hauteur,$largeur) ;
      if($image_tournee)
      {
        for( $i=0 ; $i<$largeur ; $i++)
        {
          for( $j=0 ; $j<$hauteur ; $j++)
          {
            imagecopy($image_tournee , $image_depart , $j , $largeur-1-$i , $i , $j , 1 , 1);
          }
        }
      }
      return $image_tournee;
    }
  }

  /**
   * imagecreatefrombmp
   *
   * @see http://php.net/manual/fr/function.imagecreatefromwbmp.php#86214
   *
   * @param string   $image_file
   * @return bool
   */
  public static function imagecreatefrombmp($image_file)
  {
    //  Load the image into a string
    $file = fopen($image_file,"rb");
    $read = fread($file,10);
    while(!feof($file)&&($read<>""))
    {
      $read .= fread($file,1024);
    }
    $temp   = unpack("H*",$read);
    $hex    = $temp[1];
    $header = substr($hex,0,108);
    // Process the header
    // Structure: http://www.fastgraph.com/help/bmp_header_format.html
    if (substr($header,0,4)=="424d")
    {
      // Cut it in parts of 2 bytes
      $header_parts = str_split($header,2);
      // Get the width 4 bytes
      $width = hexdec($header_parts[19].$header_parts[18]);
      // Get the height 4 bytes
      $height = hexdec($header_parts[23].$header_parts[22]);
      // Unset the header params
      unset($header_parts);
    }
    // Define starting X and Y
    $x = 0;
    $y = 1;
    // Create newimage
    $image = imagecreatetruecolor($width,$height);
    // Grab the body from the image
    $body = substr($hex,108);
    // Calculate if padding at the end-line is needed
    // Divided by two to keep overview.
    // 1 byte = 2 HEX-chars
    $body_size   = (strlen($body)/2);
    $header_size = ($width*$height);
    // Use end-line padding? Only when needed
    $usePadding = ($body_size>($header_size*3)+4);
    // Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
    // Calculate the next DWORD-position in the body
    for ($i=0;$i<$body_size;$i+=3)
    {
      // Calculate line-ending and padding
      if ($x>=$width)
      {
        // If padding needed, ignore image-padding
        // Shift i to the ending of the current 32-bit-block
        if ($usePadding)
          $i += $width%4;
        // Reset horizontal position
        $x = 0;
        // Raise the height-position (bottom-up)
        $y++;
        // Reached the image-height? Break the for-loop
        if ($y>$height)
          break;
      }
      // Calculation of the RGB-pixel (defined as BGR in image-data)
      // Define $i_pos as absolute position in the body
      $i_pos = $i*2;
      $r = hexdec($body[$i_pos+4].$body[$i_pos+5]);
      $g = hexdec($body[$i_pos+2].$body[$i_pos+3]);
      $b = hexdec($body[$i_pos].$body[$i_pos+1]);
      // Calculate and draw the pixel
      $color = imagecolorallocate($image,$r,$g,$b);
      imagesetpixel($image,$x,$height-$y,$color);
      // Raise the horizontal position
      $x++;
    }
    // Unset the body / free the memory
    unset($body);
    // Return image-object
    return $image;
  }

}
?>