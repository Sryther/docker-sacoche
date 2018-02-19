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

class HtmlMail
{

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
   * 
   * Astral planes are supported ie. the ints in the output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
   * Returns FALSE if the input string isn't a valid UTF-8 octet sequence.
   * 
   * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
   * The Original Code is Mozilla Communicator client code.
   * The Initial Developer of the Original Code is Netscape Communications Corporation.
   * Contributor(s): Henri Sivonen, hsivonen@iki.fi
   * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
   * Version 1.0, 2003-05-30
   */
  private static function utf8ToUnicode($str)
  {
    $mState = 0;     // cached expected number of octets after the current octet until the beginning of the next UTF8 character sequence
    $mUcs4  = 0;     // cached Unicode character
    $mBytes = 1;     // cached expected number of octets in the current sequence
    $len = strlen($str);
    for($i = 0; $i < $len; $i++)
    {
      $in = ord($str{$i});
      if (0 == $mState)
      {
        // When mState is zero we expect either a US-ASCII character or a
        // multi-octet sequence.
        if (0 == (0x80 & ($in)))
        {
          // US-ASCII, pass straight through.
          $out[] = $in;
          $mBytes = 1;
        }
        else if (0xC0 == (0xE0 & ($in)))
        {
          // First octet of 2 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x1F) << 6;
          $mState = 1;
          $mBytes = 2;
        }
        else if (0xE0 == (0xF0 & ($in))) 
        {
          // First octet of 3 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x0F) << 12;
          $mState = 2;
          $mBytes = 3;
        }
        else if (0xF0 == (0xF8 & ($in)))
        {
          // First octet of 4 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x07) << 18;
          $mState = 3;
          $mBytes = 4;
        }
        else if (0xF8 == (0xFC & ($in)))
        {
          /* First octet of 5 octet sequence.
           *
           * This is illegal because the encoded codepoint must be either
           * (a) not the shortest form or
           * (b) outside the Unicode range of 0-0x10FFFF.
           * Rather than trying to resynchronize, we will carry on until the end
           * of the sequence and let the later error handling code catch it.
           */
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x03) << 24;
          $mState = 4;
          $mBytes = 5;
        }
        else if (0xFC == (0xFE & ($in)))
        {
          // First octet of 6 octet sequence, see comments for 5 octet sequence.
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 1) << 30;
          $mState = 5;
          $mBytes = 6;
        }
        else 
        {
          /* Current octet is neither in the US-ASCII range nor a legal first
           * octet of a multi-octet sequence.
           */
          return FALSE;
        }
      }
      else 
      {
        // When mState is non-zero, we expect a continuation of the multi-octet
        // sequence
        if (0x80 == (0xC0 & ($in)))
        {
          // Legal continuation.
          $shift = ($mState - 1) * 6;
          $tmp = $in;
          $tmp = ($tmp & 0x0000003F) << $shift;
          $mUcs4 |= $tmp;
          if (0 == --$mState) 
          {
            /* End of the multi-octet sequence. mUcs4 now contains the final
             * Unicode codepoint to be output
             *
             * Check for illegal sequences and codepoints.
             */
            // From Unicode 3.1, non-shortest form is illegal
            if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                (4 < $mBytes) ||
                // From Unicode 3.2, surrogate characters are illegal
                (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                // Codepoints outside the Unicode range are illegal
                ($mUcs4 > 0x10FFFF))
            {
              return FALSE;
            }
            if (0xFEFF != $mUcs4)
            {
              // BOM is legal but we don't want to output it
              $out[] = $mUcs4;
            }
            //initialize UTF8 cache
            $mState = 0;
            $mUcs4  = 0;
            $mBytes = 1;
          }
        } 
        else 
        {
          /* ((0xC0 & (*in) != 0x80) && (mState != 0))
           * 
           * Incomplete multi-octet sequence.
           */
          return FALSE;
        }
      }
    }
    return $out;
  }

  /**
   * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
   * 
   * Astral planes are supported ie. the ints in the input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates are not allowed.
   * Returns FALSE if the input array contains ints that represent surrogates or are outside the Unicode range.
   * 
   * Licence : NPL 1.1/GPL 2.0/LGPL 2.1
   * The Original Code is Mozilla Communicator client code.
   * The Initial Developer of the Original Code is Netscape Communications Corporation.
   * Contributor(s): Henri Sivonen, hsivonen@iki.fi
   * The latest version of this file can be obtained from http://iki.fi/hsivonen/php-utf8/
   * Version 1.0, 2003-05-30
   */
  private static function unicodeToUtf8($arr)
  {
    $dest = '';
    foreach ($arr as $src)
    {
      if($src < 0) 
      {
        return FALSE;
      }
      else if ( $src <= 0x007f) 
      {
        $dest .= chr($src);
      }
      else if ($src <= 0x07ff) 
      {
        $dest .= chr(0xc0 | ($src >> 6));
        $dest .= chr(0x80 | ($src & 0x003f));
      } 
      else if($src == 0xFEFF) 
      {
        // nop -- zap the BOM
      }
      else if ($src >= 0xD800 && $src <= 0xDFFF) 
      {
        // found a surrogate
        return FALSE;
      }
      else if ($src <= 0xffff) 
      {
        $dest .= chr(0xe0 | ($src >> 12));
        $dest .= chr(0x80 | (($src >> 6) & 0x003f));
        $dest .= chr(0x80 | ($src & 0x003f));
      }
      else if ($src <= 0x10ffff) 
      {
        $dest .= chr(0xf0 | ($src >> 18));
        $dest .= chr(0x80 | (($src >> 12) & 0x3f));
        $dest .= chr(0x80 | (($src >> 6) & 0x3f));
        $dest .= chr(0x80 | ($src & 0x3f));
      } else 
      {
        // out of range
        return FALSE;
      }
    }
    return $dest;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Afficher un lien mailto en masquant l'adresse de courriel pour les robots.
   *
   * @param string $mail_adresse
   * @param string $mail_sujet
   * @param string $texte_lien
   * @param string $mail_contenu
   * @param string $mail_copy
   * @return string
   */
  public static function to( $mail_adresse , $mail_sujet , $texte_lien , $mail_contenu='' , $mail_copy='' )
  {
    $mailto = 'mailto:'.$mail_adresse.'?subject='.$mail_sujet;
    $mailto.= ($mail_copy)    ? '&cc='.$mail_copy      : '' ;
    $mailto.= ($mail_contenu) ? '&body='.$mail_contenu : '' ;
    $tab_bad = array( '<br />' , ' '   );
    $tab_bon = array( '%0D%0A' , '%20' );
    $tab_unicode_valeurs = HtmlMail::utf8ToUnicode( str_replace( $tab_bad , $tab_bon , $mailto ) );
    $href = '&#'.implode(';'.'&#',$tab_unicode_valeurs).';';
    if(strpos($texte_lien,'@'))
    {
      $tab_unicode_valeurs = HtmlMail::utf8ToUnicode( str_replace( $tab_bad , $tab_bon , $texte_lien ) );
      $texte_lien = '&#'.implode(';'.'&#',$tab_unicode_valeurs).';';
    }
    return '<a href="'.$href.'" class="lien_mail">'.$texte_lien.'</a>';
  }

}
?>