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

class To
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Convertir l'utf-8 en windows-1252 pour compatibilité avec FPDF
   * 
   * @param string
   * @return string
   */
  public static function pdf($text)
  {
    mb_substitute_character(0x00A0);  // Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
    return mb_convert_encoding($text,'Windows-1252','UTF-8');
  }

  /**
   * Convertir l'utf-8 en windows-1252 pour un export CSV compatible avec Ooo et Word.
   * 
   * @param string
   * @return string
   */
  public static function csv($text)
  {
    mb_substitute_character(0x00A0);  // Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
    return mb_convert_encoding($text,'Windows-1252','UTF-8');
  }

  /**
   * Convertir un contenu en UTF-8 si besoin ; à effectuer en particulier pour les imports tableur.
   * Remarque : si on utilise utf8_encode() ou mb_convert_encoding() sans le paramètre 'Windows-1252' ça pose des pbs pour '’' 'Œ' 'œ' etc.
   * 
   * @param string
   * @return string
   */
  public static function utf8($text)
  {
    return ( (!perso_mb_detect_encoding_utf8($text)) || (!mb_check_encoding($text,'UTF-8')) ) ? mb_convert_encoding($text,'UTF-8','Windows-1252') : $text ;
  }

  /**
   * Nettoie le BOM éventuel d'un contenu UTF-8.
   * Code inspiré de http://libre-d-esprit.thinking-days.net/2009/03/et-bom-le-script/
   * 
   * @param string
   * @return string
   */
  public static function deleteBOM($text)
  {
    return (substr($text,0,3) == "\xEF\xBB\xBF") ? substr($text,3) : $text ; // Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
  }

  /**
   * Echappe les caractères LaTeX.
   * 
   * @param string
   * @return string
   */
  public static function latex($text)
  {
    $tab_bad = array( '–' ,  '$' ,  '&' ,  '%' ,  '#' ,  '_' ,  '{' ,  '}' ,  '^' , '\\' );
    $tab_bon = array( '-' , '\$' , '\&' , '\%' , '\#' , '\_' , '\{' , '\}' , '\^' , '\textbackslash{}' );
    return str_replace( $tab_bad , $tab_bon , $text );
  }

  /**
   * Afficher un équivalent texte de note pour une sortie CSV ou LaTeX.
   *
   * @param string $note
   * @return string
   */
  public static function note_sigle($note)
  {
    return isset($_SESSION['NOTE'][$note]) ? $_SESSION['NOTE'][$note]['SIGLE'] : $note ;
  }

  /**
   * Passer d'une date MySQL AAAA-MM-JJ à une date française JJ/MM/AAAA.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $date_mysql AAAA-MM-JJ
   * @return string|NULL       JJ/MM/AAAA
   */
  public static function date_mysql_to_french($date_mysql)
  {
    if($date_mysql===NULL) return '00/00/0000';
    list($annee,$mois,$jour) = explode('-',$date_mysql);
    return $jour.'/'.$mois.'/'.$annee;
  }

  /**
   * Passer d'une date MySQL AAAA-MM-JJ HH:MM:SS à une date française JJ/MM/AAAA HH:MM.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $datetime_mysql AAAA-MM-JJ HH:MM:SS
   * @param bool   $return_time
   * @return string                JJ/MM/AAAA HHhMMmin
   */
  public static function datetime_mysql_to_french( $datetime_mysql, $return_time=TRUE )
  {
    list( $partie_jour , $partie_heure ) = explode( ' ' , $datetime_mysql);
    list( $annee , $mois , $jour       ) = explode( '-' , $partie_jour);
    list( $heure , $minute , $seconde  ) = explode( ':' , $partie_heure);
    return ($return_time) ? $jour.'/'.$mois.'/'.$annee.' '.$heure.'h'.$minute.'min' : $jour.'/'.$mois.'/'.$annee ;
  }

  /**
   * Passer d'une date française JJ/MM/AAAA à une date MySQL AAAA-MM-JJ.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $date_fr JJ/MM/AAAA
   * @return string|NULL    AAAA-MM-JJ
   */
  public static function date_french_to_mysql($date_fr)
  {
    if(!(int)substr($date_fr,0,2)) return NULL;
    list($jour,$mois,$annee) = explode('/',$date_fr);
    return $annee.'-'.$mois.'-'.$jour;
  }

  /**
   * Passer d'une date française JJ/MM/AAAA à une date J mois AAAA.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $date_fr JJ/MM/AAAA
   * @return string         J mois AAAA
   */
  public static function date_french_to_texte($date_fr)
  {
    $tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
    if(!(int)substr($date_fr,0,2)) return '';
    list($jour,$mois,$annee) = explode('/',$date_fr);
    return intval($jour).' '.$tab_mois[$mois].' '.$annee;
  }

  /**
   * Déterminer le nombre d'années et de mois séparant deux dates françaises JJ/MM/AAAA.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * Il existe des fonctions utilisables qu'à partir de PHP 5.2 ou 5.3.
   * @see http://fr.php.net/manual/fr/class.datetime.php
   * Ceci dit, le code élaboré est assez simple.
   *
   * @param string $date_deb_fr JJ/MM/AAAA
   * @param string $date_fin_fr JJ/MM/AAAA
   * @return array   { nb_annees , nb_mois }
   */
  public static function ecarts_mois_annee( $date_deb_fr , $date_fin_fr )
  {
    list($jour_deb,$mois_deb,$annee_deb) = explode('/',$date_deb_fr);
    list($jour_fin,$mois_fin,$annee_fin) = explode('/',$date_fin_fr);
    $nb_annees = $annee_fin - $annee_deb;
    $nb_mois   = $mois_fin - $mois_deb;
    if( ($mois_deb>$mois_fin) || (($mois_deb==$mois_fin)&&($jour_deb>$jour_fin)) )
    {
      $nb_annees-=1;
      $nb_mois+=12;
    }
    if($jour_deb>$jour_fin)
    {
      $nb_mois-=1;
    }
    return array($nb_annees,$nb_mois);
  }

  /**
   * Passer d'une date française JJ/MM/AAAA à l'âge X ans et Y mois.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $date_fr JJ/MM/AAAA
   * @return string         X ans et Y mois
   */
  public static function texte_age($date_fr)
  {
    list($nb_annees,$nb_mois) = To::ecarts_mois_annee( $date_fr , TODAY_FR );
    $s_annee = ($nb_annees>1) ? 's' : '' ;
    $aff_mois = ($nb_mois) ? ' et '.$nb_mois.' mois' : '' ;
    return $nb_annees.' an'.$s_annee.$aff_mois;
  }

  /**
   * Passer d'une date française JJ/MM/AAAA à l'affichage de la date de naissance et de l'âge.
   * Remarque : l'année peut éventuellement être sur 2 chiffres.
   *
   * @param string $date_fr JJ/MM/AAAA
   * @return string
   */
  public static function texte_ligne_naissance($date_fr)
  {
    return 'Âge : '.To::texte_age($date_fr).' ('.To::date_french_to_texte($date_fr).').';
  }

  /**
   * Retourner un nom suivi d'un prénom (ou le contraire) dont l'un ou les deux sont éventuellement remplacés par leur initiale.
   * En cas de civilité présente, la deuxième partie sera retirée si seule son initiale est demandée.
   *
   * @param string $partie1
   * @param bool   $is_initiale1
   * @param string $partie2
   * @param bool   $is_initiale2
   * @param string $genre   (facultatif)
   * @return string
   */
  public static function texte_identite( $partie1 , $is_initiale1 , $partie2 , $is_initiale2 , $genre='I' )
  {
    $tab_civilite = array( 'M'=>'M.' , 'F'=>'Mme' );
    $civilite = ($genre!='I') ? $tab_civilite[$genre] : '' ;
    $partie1 = ( $is_initiale1 && strlen($partie1) ) ? $partie1{0}.'.' : $partie1 ;
    $partie2 = ( $is_initiale2 && strlen($partie2) ) ? $partie2{0}.'.' : $partie2 ;
    return ($civilite && $is_initiale2) ? trim($civilite.' '.$partie1) : trim($civilite.' '.$partie1.' '.$partie2) ;
  }

  /**
   * Renvoyer le 1er jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
   *
   * @param string $format           'mysql'|'french'
   * @param int    $annee_decalage   facultatif, pour les années scolaires précédentes ou suivantes
   * @return string
   */
  public static function jour_debut_annee_scolaire( $format , $annee_decalage=0 )
  {
    $jour  = '01';
    $mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
    $annee = (date('n')<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date('Y')+$annee_decalage-1 : date('Y')+$annee_decalage ;
    return ($format=='mysql') ? $annee.'-'.$mois.'-'.$jour : $jour.'/'.$mois.'/'.$annee ;
  }

  /**
   * Renvoyer le dernier jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
   *
   * @param string $format           'mysql'|'french'
   * @param int    $annee_decalage   facultatif, pour les années scolaires précédentes ou suivantes
   * @return string
   */
  public static function jour_fin_annee_scolaire( $format , $annee_decalage=0 )
  {
    $jour  = '01';
    $mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
    $annee = (date('n')<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date('Y')+$annee_decalage : date('Y')+$annee_decalage+1 ;
    $date_veille_stamp = strtotime($annee.'-'.$mois.'-'.$jour.' -1 day');
    return ($format=='mysql') ? date('Y-m-d',$date_veille_stamp) : date('d/m/Y',$date_veille_stamp) ;
  }

  /**
   * Retourner le nb de mois restants avant la fin de l'année scolaire
   *
   * @param void
   * @return int
   */
  public static function mois_restants_annee_scolaire()
  {
    list($nb_annees,$nb_mois) = To::ecarts_mois_annee( TODAY_FR , To::jour_fin_annee_scolaire('french') );
    return $nb_annees*12 + $nb_mois;
  }

  /**
   * Renvoyer l'année de session du DNB.
   * N'EST PLUS UTILISÉ AU 08/2017
   *
   * @param void
   * @return string
   */
  public static function annee_session_brevet()
  {
    $mois_actuel    = date('n');
    $annee_actuelle = date('Y');
    $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'];
    if($mois_bascule==1)
    {
      return $annee_actuelle;
    }
    else if($mois_actuel < $mois_bascule)
    {
      return $annee_actuelle;
    }
    else
    {
      return $annee_actuelle+1;
    }
  }

  /**
   * Renvoyer l'année scolaire en cours.
   *
   * format 'texte'  : Année scolaire 2016 / 2017
   * format 'code'   : 2016-2017
   * format 'siecle' : 2016
   *
   * @param string   $format           'texte' | 'code' | 'siecle'
   * @param int      $annee_decalage   facultatif, pour les années scolaires précédentes ou suivantes
   * @return string
   */
  public static function annee_scolaire( $format , $annee_decalage=0 )
  {
    $mois_actuel    = date('n');
    $annee_actuelle = date('Y')+$annee_decalage;
    $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'];
    if($format=='siecle')
    {
      return ($mois_actuel >= $mois_bascule) ? (string)$annee_actuelle : (string)($annee_actuelle-1) ;
    }
    $sep = ($format=='code') ? '-' : ' / ' ;
    $txt = ($format=='code') ? '' : Lang::_("Année scolaire").' ' ;
    if($mois_bascule==1)
    {
      return $txt.$annee_actuelle;
    }
    else if($mois_actuel < $mois_bascule)
    {
      return $txt.($annee_actuelle-1).$sep.$annee_actuelle;
    }
    else
    {
      return $txt.$annee_actuelle.$sep.($annee_actuelle+1);
    }
  }

}

?>