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

/** 
 * Fonctions de nettoyage des chaînes avant stockage ou affichage.
 * 
 * Les conseils à suivre que l'on donne génréralement sont les suivants :
 * + Desactiver magic_quotes_gpc() pour ne pas avoir à jouer conditionnellement avec stripslashes() et addslashes()
 * + Avant stockage dans la BDD utiliser mysql_real_escape_string() et intval()
 * + Avant affichage utiliser htmlspecialchars() couplé à nl2br() si on veut les sauts de ligne (hors textarea)
 * Ici c'est inutile, les fonctions mises en place et la classe PDO s'occupent de tout.
 * 
 */

/*
 * Attention ! strtr() renvoie n'importe quoi en UTF-8 car il fonctionne octet par octet et non caractère par caractère, or l'UTF-8 est multi-octets...
*/
define( 'LETTER_CHARS',        utf8_decode(            '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') );
define( 'LETTER_NUMBER_CHARS', utf8_decode( '-0123456789_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') );
define( 'FILENAME_CHARS'     , utf8_decode('.-0123456789_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') );
define( 'LATIN1_LC_CHARS' , utf8_decode('abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïñòóôõöœøŕšùúûüýÿžðþ') );
define( 'LATIN1_UC_CHARS' , utf8_decode('ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖŒØŔŠÙÚÛÜÝŸŽÐÞ') );
define( 'LATIN1_YES_ACCENT' , utf8_decode('ÀÁÂÃÄÅàáâãäåÞþÇçÐðÈÉÊËèéêëÌÍÎÏìíîïÑñÒÓÔÕÖØòóôõöøŔŕŠšßÙÚÛÜùúûüÝŸýÿŽž') );
define( 'LATIN1_NOT_ACCENT' , utf8_decode('AAAAAAaaaaaaBbCcDdEEEEeeeeIIIIiiiiNnOOOOOOooooooRrSssUUUUuuuuYYyyZz') );

class Clean
{

  // @see https://fr.wikipedia.org/wiki/Table_des_caract%C3%A8res_Unicode/U0000
  private static $tab_ctrl = array(
      "\x00" , // NUL Null (caractère de bourrage)
      "\x01" , // SOH Start of Header (début d'en-tête)
      "\x02" , // STX Start of Text (début de texte)
      "\x03" , // ETX End of Text (fin de texte)
      "\x04" , // EOT End of Transmission (fin de transmission)
      "\x05" , // ENQ Enquiry (requête / demande)
      "\x06" , // ACK Acknowledge (accusé de réception positif)
      "\x07" , // BEL Bell (appel / sonnerie)
      "\x08" , // BS  BackSpace (retour arrière)
   // "\x09" , // HT  Horizontal Tabulation (tabulation horizontale)
   // "\x0A" , // LF  Line Feed (saut de ligne / ligne suivante)
      "\x0B" , // VT  Vertical Tabulation (tabulation verticale)
      "\x0C" , // FF  Form Feed (saut de page / page suivante)
   // "\x0D" , // CR  Carriage Return (retour de chariot)
      "\x0E" , // SO  Shift Out (hors code, remplacement verrouillé un)
      "\x0F" , // SI  Shift In (en code, remplacement verrouillé zéro)
      "\x10" , // DLE Data Link Escape (échappement de transmission)
      "\x11" , // DC1 Device Control 1 (commande de dispositif 1)
      "\x12" , // DC2 Device Control 2 (commande de dispositif 2)
      "\x13" , // DC3 Device Control 3 (commande de dispositif 3)
      "\x14" , // DC4 Device Control 4 (commande de dispositif 4)
      "\x15" , // NAK Negative acknowledge (accusé de réception négatif)
      "\x16" , // SYN Synchronous Idle (synchronisation)
      "\x17" , // ETB End of Transmission Block (fin de bloc de transmission)
      "\x18" , // CAN Cancel (annulation)
      "\x19" , // EM  End of Medium (fin de support)
      "\x1A" , // SUB Substitute (substitution)
      "\x1B" , // ESC Escape (échappement)
      "\x1C" , // FS  File Separator (séparateur de fichiers)
      "\x1D" , // GS  Group Separator (séparateur de groupes)
      "\x1E" , // RS  Record Separator (séparateur d'enregistrements, séparateur d'articles)
      "\x1F" , // US  Unit Separator (séparateur de sous-articles)
      "\x7F" , // DEL (suppression)
    );

  // //////////////////////////////////////////////////
  // Méthode publique car la construction du tableau requière des instructions
  // //////////////////////////////////////////////////

  // @see https://fr.wikipedia.org/wiki/Fin_de_ligne
  // LS et PS n'existent qu'en Unicode d'où cette définition un peu tordue
  // à compter de PHP 7 c'est plus simple : https://secure.php.net/manual/fr/migration70.new-features.php#migration70.new-features.unicode-codepoint-escape-syntax
  public static function tab_crlf()
  {
    return (version_compare(PHP_VERSION,'7','>=')) ?
      array(
        "\u{000D}\u{000A}", // CRLF
        "\u{000D}", // CR
        "\u{000A}", // LF
        "\u{000B}", // VT
        "\u{000C}", // FF
        "\u{2028}", // LS
        "\u{2029}", // PS
      ) :
      array(
        json_decode('"\u000D\u000A"'), // CRLF
        json_decode('"\u000D"'), // CR
        json_decode('"\u000A"'), // LF
        json_decode('"\u000B"'), // VT
        json_decode('"\u000C"'), // FF
        json_decode('"\u2028"'), // LS
        json_decode('"\u2029"'), // PS
      ) ;
  }

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Pour supprimer les caractères de contrôle invisibles indésirables dans une chaîne.
   *
   * Il est arrivé d'en trouver dans des chaînes copiées-collées et ça pose problème, par exemple 
   * - NUL pour les noms de fichiers "is_file() expects parameter 1 to be a valid path, string given"
   * - RS | US pour la validation XML "Erreur XML ligne iii (> required)"
   *
   * Seuls quelques-uns (U+0009, U+000A, U+000D) sont normalisés pour le codage de textes
   * et ont un comportement bien défini par Unicode.
   *
   * @param string
   * @return string
   */
  private static function ctrl($text)
  {
    return str_replace( Clean::$tab_ctrl , "" , $text );
  }

  /**
   * Ajout d'espaces insécables judicieux et retrait d'espaces de mise en forme inappropriés.
   *
   * @param string
   * @return string
   */
  private static function espaces($text)
  {
    $e = chr(0xC2).chr(0xA0); // espace insécable en UTF-8 (http://fr.wikipedia.org/wiki/Espace_ins%C3%A9cable ; http://fr.wikipedia.org/wiki/UTF-8)
    $tab_bad = array(   ' !' ,   ' ?' ,   ' :' ,   ' ;' ,   ' %' , ' .' , ' ,' );
    $tab_bon = array( $e.'!' , $e.'?' , $e.':' , $e.';' , $e.'%' ,  '.' ,  ',' );
    return str_replace( $tab_bad , $tab_bon , $text );
  }

  /**
   * Pour harmoniser les retours chariots.
   * La classe PDF ne compte ensuite que le nb de "\n"
   *
   * @param string
   * @return string
   */
  private static function lignes($text)
  {
    return str_replace( Clean::tab_crlf() , "\n" , $text );
  }

  /**
   * Equivalent de "strtoupper()" pour mettre en majuscules y compris les caractères accentués
   *
   * @param string
   * @return string
   */
  private static function perso_strtoupper($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_UPPER,'UTF-8') : strtr($text,LATIN1_LC_CHARS,LATIN1_UC_CHARS) ;
  }

  /**
   * Equivalent de "strtolower()" pour mettre en minuscules y compris les caractères accentués
   *
   * @param string
   * @return string
   */
  private static function perso_strtolower($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_LOWER,'UTF-8') : strtr($text,LATIN1_UC_CHARS,LATIN1_LC_CHARS) ;
  }

  /**
   * Séparer les caractères ligaturés
   *
   * @param string
   * @return string
   */
  private static function ligatures($text)
  {
    $bad = array('Æ' ,'æ' ,'Œ' ,'œ' );
    $bon = array('AE','ae','OE','oe');
    return str_replace($bad,$bon,$text);
  }

  /**
   * Enlever les symboles embétants
   *
   * @param string
   * @return string
   */
  private static function symboles($text)
  {
    $bad = array('&','<','>','\\','"','\'','/','`','’');
    $bon = '';
    return str_replace($bad,$bon,$text);
  }

  /**
   * Ne conserver que les lettres minuscules | chiffres et supprimer le reste
   *
   * @param string
   * @return string
   */
  private static function only_letters_numbers($text)
  {
    $caracteres = (perso_mb_detect_encoding_utf8($text)) ? utf8_decode($text) : $text ;
    if(strlen($caracteres))
    {
      $tab_caracteres = str_split($caracteres);
      foreach($tab_caracteres as $key => $caractere)
      {
        $tab_caracteres[$key] = (strpos(LETTER_NUMBER_CHARS,$caractere)!==FALSE) ? $caractere : '' ;
      }
      $caracteres = implode('',$tab_caracteres);
    }
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode($caracteres) : $caracteres ;
  }

  /**
   * Ne conserver que les lettres minuscules | chiffres et supprimer le reste
   *
   * @param string
   * @return string
   */
  private static function only_letters($text)
  {
    $caracteres = (perso_mb_detect_encoding_utf8($text)) ? utf8_decode($text) : $text ;
    if(strlen($caracteres))
    {
      $tab_caracteres = str_split($caracteres);
      foreach($tab_caracteres as $key => $caractere)
      {
        $tab_caracteres[$key] = (strpos(LETTER_CHARS,$caractere)!==FALSE) ? $caractere : '' ;
      }
      $caracteres = implode('',$tab_caracteres);
    }
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode($caracteres) : $caracteres ;
  }

  /**
   * Ne conserver que les lettres minuscules | chiffres points et tirets, et mettre des tirets pour le reste
   *
   * @param string
   * @return string
   */
  private static function only_filechars($text)
  {
    $caracteres = (perso_mb_detect_encoding_utf8($text)) ? utf8_decode($text) : $text ;
    if(strlen($caracteres))
    {
      $tab_caracteres = str_split($caracteres);
      foreach($tab_caracteres as $key => $caractere)
      {
        $tab_caracteres[$key] = (strpos(FILENAME_CHARS,$caractere)!==FALSE) ? $caractere : '-' ;
      }
      $caracteres = implode('',$tab_caracteres);
    }
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode($caracteres) : $caracteres ;
  }

  /**
   * Abréger une expression dépassant le nb de caractères autorisés de façon intelligente.
   * Ainsi le nom de famille "Froilán de Marichalar y de Borbón" ne deviendra pas
   * "Froilán de Marichalar y d" mais "Froilán de M. y de B."
   *
   * @param string
   * @param int   $longueur_totale_maxi
   * @return string
   */
  private static function tronquer_chaine($texte,$longueur_totale_maxi)
  {
    $nb_car = mb_strlen($texte);
    // Expression trop longue
    if($nb_car>$longueur_totale_maxi)
    {
      // On la coupe en morceaux placés dans $tab_sections
      $tab_sections = array();
      $i_section = -1;
      $liste_autres = ' -.\'"`&~,';
      $car_is_autre = NULL;
      for( $i_car=0 ; $i_car<$nb_car ; $i_car++ )
      {
        $car = $texte{$i_car};
        $test_this_car_autre = (strpos($liste_autres,$car)!==FALSE) ? TRUE : FALSE ;
        if($test_this_car_autre!==$car_is_autre)
        {
          $i_section++;
          $car_is_autre = $test_this_car_autre;
          $tab_sections[$i_section] = $car;
        }
        else
        {
          $tab_sections[$i_section] .= $car;
        }
      }
      // On abrège les morceaux nécessaires jusqu'à avoir une taille raisonnable
      $longueur_mot_maxi = 3;
      $nb_sections = count($tab_sections);
      for( $i_section=$nb_sections-1 ; $i_section>=0 ; $i_section-- )
      {
        $longueur_section = mb_strlen($tab_sections[$i_section]);
        if($longueur_section>$longueur_mot_maxi)
        {
          $tab_sections[$i_section] = $tab_sections[$i_section]{0}.'.';
          $nb_car = $nb_car - $longueur_section + 2 ;
          if($nb_car<=$longueur_totale_maxi)
          {
            break;
          }
        }
      }
      // On regroupe les morceaux
      $texte = implode('',$tab_sections);
    }
    return $texte;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques de bas niveau
  // //////////////////////////////////////////////////

  /*
   * Equivalent de "ucwords()" adaptée aux caractères accentués et aux expressions séparées par autre chose qu'une espace (virgule, point, tiret, parenthèse...)
   * Appelé depuis /pages/administrateur_fichier_identifiant.ajax.php
   * 
   * @param string
   * @return string
   */
  public static function perso_ucwords($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_TITLE,'UTF-8') : trim(preg_replace_callback('/([^a-z'.LATIN1_LC_CHARS.']|^)([a-z'.LATIN1_LC_CHARS.'])/', "Clean::callback_ucwords", Clean::perso_strtolower($text)));
  }
  private static function callback_ucwords($matches)
  {
    // comme d'habitude : $matches[0] représente la valeur totale et $matches[1] représente la première parenthèse capturante
    return stripslashes($matches[1].Clean::perso_strtoupper($matches[2]));
  }

  /*
   * Enlever les accents
   * Appelé depuis /pages/administrateur_fichier_identifiant.ajax.php
   * 
   * @param string
   * @return string
   */
  public static function accents($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode(strtr(utf8_decode($text),LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT)) : strtr($text,LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT) ;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques principales
  // //////////////////////////////////////////////////

  /*
    Les fonctions centrales à modifier sans avoir à modifier tous les scripts.
    En général il s'agit d'harmoniser les données de la base ou d'aider l'utilisateur (en évitant les problèmes de casse par exemple).
    Le login est davantage nettoyé car il y a un risque d'engendrer des comportements incertains (à l'affichage ou à l'enregistrement) avec les applications externes (pmwiki, phpbb...).
  */
  public static function lettres($text)          { return Clean::only_letters( Clean::ctrl( trim($text) ) ); }
  public static function lettres_chiffres($text) { return Clean::only_letters_numbers( Clean::ctrl( trim($text) ) ); }

  public static function login($text)        { return str_replace(' ','', Clean::perso_strtolower( Clean::accents( Clean::ligatures( Clean::symboles( Clean::ctrl( trim($text) ) ) ) ) ) ); }
  public static function fichier($text)      { return Clean::only_filechars(       Clean::perso_strtolower( Clean::accents( Clean::ligatures( Clean::ctrl( trim($text) ) ) ) ) ); }
  public static function id($text)           { return Clean::only_letters_numbers( Clean::perso_strtolower( Clean::accents( Clean::ligatures( Clean::ctrl( trim($text) ) ) ) ) ); }
  public static function param_chemin($text) { return str_replace(array('.','/','\\'),'', Clean::ctrl( trim($text) ) ); } // Contre l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
  public static function zip_filename($text) { return perso_mb_detect_encoding_utf8($text) ? Clean::fichier($text) : Clean::fichier(iconv('CP850','UTF-8',$text)) ; } //  filenames stored in the ZIP archives created on non-Unix systems are encoded in CP850 http://fr.php.net/manual/fr/function.zip-entry-name.php#87130
  public static function password($text)     { return Clean::ctrl( trim($text) ); }
  public static function ref($text)          { return Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ); }
  public static function uai($text)          { return Clean::only_letters_numbers( Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ) ); }
  public static function nom($text)          { return Clean::tronquer_chaine( Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ) , NOM_LONGUEUR_MAX); }
  public static function prenom($text)       { return Clean::tronquer_chaine( Clean::perso_ucwords( Clean::ctrl( trim($text) ) ) , PRENOM_LONGUEUR_MAX); }
  public static function structure($text)    { return Clean::perso_ucwords( Clean::ctrl( trim($text) ) ); }
  public static function adresse($text)      { return Clean::tronquer_chaine( Clean::perso_ucwords( Clean::ctrl( trim($text) ) ) , 100); }
  public static function codepostal($text)   { return mb_substr( Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ) , 0 , 10); }
  public static function commune($text)      { return Clean::tronquer_chaine( Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ) , 45); }
  public static function pays($text)         { return Clean::tronquer_chaine( Clean::perso_strtoupper( Clean::ctrl( trim($text) ) ) , 35); }
  public static function code($text)         { return Clean::only_letters_numbers( Clean::perso_strtolower( Clean::ctrl( trim($text) ) ) ); }
  public static function courriel($text)     { return mb_substr( Clean::perso_strtolower( Clean::accents( Clean::ctrl( trim($text) ) ) ) , 0 , COURRIEL_LONGUEUR_MAX); }
  public static function appreciation($text) { return Clean::espaces( Clean::lignes( Clean::ctrl( trim($text) ) ) ); }
  public static function texte($text)        { return Clean::ctrl( trim($text) ); }
  public static function url($text)          { return Clean::ctrl( trim($text) ); }
  public static function id_ent($text)       { return mb_substr( Clean::texte( (string)$text ) ,0,100 ); }
  public static function entier($text)       { return intval($text); }
  public static function decimal($text)      { return floatval(str_replace(',','.',$text)); }
  public static function txt_note($text)     { return Clean::tronquer_chaine( Clean::ctrl( trim($text) ) , 40); }
  public static function upper($text)        { return Clean::perso_strtoupper($text); }
  public static function lower($text)        { return Clean::perso_strtolower($text); }

  public static function date_fr($text)
  {
    $sep = '/';
    list($jour,$mois,$annee) = explode($sep,$text) + array_fill(0,3,0); // Evite des NOTICE en initialisant les valeurs manquantes
    return sprintf("%02u",$jour).$sep.sprintf("%02u",$mois).$sep.sprintf("%04u",$annee);
  }

  public static function date_mysql($text)
  {
    $sep = '-';
    list($annee,$mois,$jour) = explode($sep,$text) + array_fill(0,3,0); // Evite des NOTICE en initialisant les valeurs manquantes
    return sprintf("%04u",$annee).$sep.sprintf("%02u",$mois).$sep.sprintf("%02u",$jour);
  }

  public static function calcul_methode($text)
  {
    $tab = array('geometrique','arithmetique','classique','bestof1','bestof2','bestof3','frequencemin','frequencemax');
    return in_array($text,$tab) ? $text : NULL ;
  }

  public static function calcul_limite($text,$methode)
  {
    $text = Clean::entier($text);
    $tab = array(
      'geometrique'  => array(1,2,3,4,5),
      'arithmetique' => array(1,2,3,4,5,6,7,8,9),
      'classique'    => array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0),
      'bestof1'      => array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0),
      'bestof2'      => array(  2,3,4,5,6,7,8,9,10,15,20,30,40,50,0),
      'bestof3'      => array(    3,4,5,6,7,8,9,10,15,20,30,40,50,0),
      'frequencemin' => array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0),
      'frequencemax' => array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0),
    );
    return ( isset($tab[$methode]) && in_array($text,$tab[$methode]) ) ? $text : NULL ;
  }

  public static function calcul_retroactif($text)
  {
    $tab = array('non','oui','annuel','auto');
    return in_array($text,$tab) ? $text : NULL ;
  }

  public static function synthese_methode($text)
  {
    $tab = array('inconnu','sans','domaine','theme');
    return in_array($text,$tab) ? $text : NULL ;
  }

  public static function synthese_livret($text)
  {
    $tab = array('domaine','theme','item');
    return in_array($text,$tab) ? $text : NULL ;
  }

  public static function referentiel_partage($text)
  {
    $tab = array('oui','non','bof','hs');
    return in_array($text,$tab) ? $text : NULL ;
  }

  /*
    Pour les tableaux
  */
  public static function map($function,$array)
  {
    return array_map( array('Clean',$function) , $array );
  }

}
?>