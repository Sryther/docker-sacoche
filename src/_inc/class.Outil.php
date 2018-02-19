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

class Outil
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Fabriquer un login à partir de nom/prénom selon le format paramétré par l'administrateur (reste à tester sa disponibilité).
   * Cette fonction n'est appelée que depuis un espace administrateur ; $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] est donc défini.
   * 
   * @param string $prenom
   * @param string $nom
   * @param string $profil_sigle
   * @return string
   */
  public static function fabriquer_login( $prenom , $nom , $profil_sigle )
  {
    $modele = $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'][$profil_sigle];
    $login_prenom = mb_substr( str_replace(array('.','-','_'),'',Clean::login($prenom)) , 0 , mb_substr_count($modele,'p') );
    $login_nom    = mb_substr( str_replace(array('.','-','_'),'',Clean::login($nom))    , 0 , mb_substr_count($modele,'n') );
    $login_separe = str_replace(array('p','n'),'',$modele);
    $login = ($modele{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
    return $login;
  }

  /**
   * Fabriquer un mot de passe ; 8 caractères imposés.
   * Cette fonction peut être appelée par le webmestre ou à l'installation ; $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] n'est alors pas défini -> dans ce cas, on ne transmet pas de paramètre.
   * 
   * Certains caractères sont évités :
   * "e" sinon un tableur peut interpréter le mot de passe comme un nombre avec exposant
   * "i"j"1"l" pour éviter une confusion entre eux
   * "m"w" pour éviter la confusion avec "nn"vv"
   * "o"0" pour éviter une confusion entre eux
   * 
   * @param string $profil_sigle
   * @return string
   */
  public static function fabriquer_mdp($profil_sigle=NULL)
  {
    $nb_chars = ($profil_sigle) ? $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'][$profil_sigle] : 8 ;
    return mb_substr(str_shuffle('2345678923456789aaaaauuuuubcdfghknpqrstvxyz'),0,$nb_chars);
  }

  /**
   * Crypter un mot de passe avant enregistrement dans la base.
   * 
   * Le "salage" complique la recherche d'un mdp à partir de son empreinte md5 en utilisant une table arc-en-ciel.
   * 
   * @param string $password
   * @return string
   */
  public static function crypter_mdp($password)
  {
    return md5('grain_de_sel'.$password);
  }

  /**
   * Récupérer le numéro de la dernière version de SACoche disponible auprès du serveur communautaire.
   * 
   * @param void
   * @return string 'AAAA-MM-JJi' ou message d'erreur
   */
  public static function recuperer_numero_derniere_version()
  {
    $requete_reponse = cURL::get_contents(SERVEUR_VERSION);
    return (preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[a-z]?$#',$requete_reponse)) ? $requete_reponse : 'Dernière version non détectée&hellip;' ;
  }

  /**
   * Tester si une adresse de courriel semble normale.
   * 
   * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
   * http://fr2.php.net/manual/fr/function.preg-match.php#96910
   * 
   * @param string   $courriel
   * @return bool
   */
  public static function tester_courriel($courriel)
  {
    return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/',$courriel) ? TRUE : FALSE;
  }

  /**
   * Vérifier que le domaine du serveur mail peut recevoir des mails, c'est à dire qu'il a un serveur de mail déclaré dans les DNS).
   * Ça évite tous les domaines avec une coquille du genre @gmaill.com, @hoatmail.com, @gmaol.com, @laoste.net, etc.
   *
   * @param string              $mail_adresse
   * @return array(string,bool) le domaine + TRUE|FALSE
   */
  public static function tester_domaine_courriel_valide($mail_adresse)
  {
    $mail_domaine = mb_substr( $mail_adresse , mb_strpos($mail_adresse,'@')+1 );
    $hasMx = function_exists('getmxrr') && getmxrr($mail_domaine, $tab_mxhosts);
    $isPort25open = FALSE;
    if (!$hasMx)
    {
      $res = @fsockopen($mail_domaine, 25, $errno, $errstr, 5);
      if ($res)
      {
        fclose($res);
        $isPort25open = TRUE;
      }
    }
    $is_domaine_valide = $hasMx || $isPort25open ;
    return array( $mail_domaine , $is_domaine_valide );
  }

  /**
   * Tester si un numéro UAI est valide.
   * 
   * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
   * 
   * @param string   $uai
   * @return bool
   */
  public static function tester_UAI($uai)
  {
    $alphabet = 'ABCDEFGHJKLMNPRSTUVWXYZ';
    $uai_lettre = substr($uai, -1);
    // Base RAMSESE (établissement du système éducatif français) : 7 chiffres suivis d'une lettre de contrôle basée sur le modulo 23 du nombre
    if (preg_match('#^[0-9]{7}['.$alphabet.']{1}$#', $uai))
    {
      $reste = substr($uai, 0, 7) % 23 ;
      return ( $uai_lettre == substr($alphabet, $reste, 1) ) ? TRUE : FALSE ;
    }
    // Base RHODES (organismes de détachement hors éducation) : 6 chiffres suivis du caractère "X" puis d'une lettre de contrôle basée sur le modulo 23 du ( nombre * 8 + 1 )
    if (preg_match('#^[0-9]{6}X['.$alphabet.']{1}$#', $uai))
    {
      $reste = (substr($uai, 0, 6) * 8 + 1 ) % 23 ;
      return ( $uai_lettre == substr($alphabet, $reste, 1) ) ? TRUE : FALSE ;
    }
    return FALSE ;
  }

  /**
   * Tester si une date est valide : format AAAA-MM-JJ par exemple.
   * 
   * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
   * 
   * @param string   $date
   * @return bool
   */
  public static function tester_date($date)
  {
    $date_unix = strtotime($date);
    return ( ($date_unix!==FALSE) && ($date_unix!==-1) ) ? TRUE : FALSE ;
  }

  /**
   * Renvoyer les balises images à afficher et la chaine solution à mettre en session.
   * 
   * @return array   [$html_imgs,$captcha_soluce]
   */
  public static function captcha()
  {
    $tab_base64 = array(
      0 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI4hI+pwe3fDJxPQjStWxV0HxzdpoXliYJMSqpmC0euKM/2TcYrqpv2t3O9PrTcpWikUEpKouKJKAAAOw==',
      1 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAIzhI+pwe3fDJxPznNBtlV3DHHMEnql6YipOqKr21JgJ7OBfdv4vNZw7ssES5Qi0LhRKBMFADs=',
      2 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI4hI+pwe3fDJxP1gRtVOA6xIDbpwUHSW3dmIrhOrlvLM9XiZJ4rqYm/APSdBxesMjyZYTKF+eJKAAAOw==',
      3 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI3hI+pwe3fDJxPQjStWxV0HxzdpoXliYJMebHsio1tHKmZa4t1zvfkB7OZZrof8UZJDpU3hVNRAAA7',
      4 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI2hI+pwe3fDJxPTgRtPvviCngcuH2OFiXdKY0sGLopGwYygyqmXeN63iutREMhiYJEIWG/JqAAADs=',
      5 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI4hI+pwe3fDJxPOmUjqJjzTQUZc4QiSI7T2EFse6EnWsbmbJt1Gsv72QP9gMEhsbiS3TRLF+aZKAAAOw==',
      6 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI5hI+pwe3fDJxPQjStU6D2bR0e+DFiVAYnpaZc5r4jGmpmjIPkjvJ37PmsbpdEEWbrsSjJJY0DPRQAADs=',
      7 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI2hI+pwe3fDJxPRnWcZThfegGaCAYeR1InWq5mCrkvKG+V/U3yrONs7PvdYKMeMVcCJo2dJqAAADs=',
      8 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI4hI+pwe3fDJxPQjStWxV0HxzdpoXliYJMaYIieSVxjI2ky6Y3HOn9t8qpejXeZ/ijKFnKm+KZKAAAOw==',
      9 => 'R0lGODlhFAAUAIAAAGZm/93d/yH5BAEKAAAALAAAAAAUABQAAAI4hI+pwe3fDJxPQjStWxV0HxzdpoXliYJMSqrmt4qRaypuOSckle/9H4PVRh+Zr5ih8Y7Lou1pKAAAOw==',
    );
    $rand_keys   = str_shuffle('0123456789');
    $rand_values = str_shuffle('abcdefghijklmnopqrstuvxyz');
    $tab_values = array();
    $html_imgs = '';
    for( $i=1 ; $i<=6 ; $i++ )
    {
      $key   = $rand_keys{$i};
      $value = $rand_values{$i};
      $tab_values[$key] = $value;
      $html_imgs .= '<img class="captcha" id="cap_'.$value.'" src="data:image/gif;base64,'.$tab_base64[$key].'" />';
    }
    ksort($tab_values);
    $captcha_soluce = implode('',$tab_values);
    return array($html_imgs,$captcha_soluce);
  }

  /**
   * Tester si on est dans la période de rentrée
   * Par défaut : 01/08 -> 30/09
   *
   * @param void
   * @return bool
   */
  public static function test_periode_rentree()
  {
    $mois_actuel    = date('n');
    $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'] ; // par défaut août
    $mois_rentree   = ($mois_bascule<12) ? $mois_bascule+1 : 1 ; // par défaut septembre
    return ($mois_actuel==$mois_bascule) || ($mois_actuel==$mois_rentree) ;
  }

  /**
   * Tester si on est dans la période de sortie
   * Par défaut : 01/06 -> 31/07
   *
   * @param void
   * @return bool
   */
  public static function test_periode_sortie()
  {
    $mois_actuel    = date('n');
    $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'] ; // par défaut août
    $mois_sortie    = ($mois_bascule>1) ? $mois_bascule-1 : 12 ; // par défaut juillet
    $mois_fin_annee = ($mois_sortie>1)  ? $mois_sortie-1  : 12 ; // par défaut juin
    return ($mois_actuel==$mois_sortie) || ($mois_actuel==$mois_fin_annee) ;
  }

  /**
   * Tester si un droit d'accès spécifique comporte une restriction aux PP ou aux coordonnateurs
   *
   * @param string $listing_droits_sigles
   * @param string $restriction   'ONLY_PP' | 'ONLY_COORD'
   * @return bool
   */
  public static function test_droit_specifique_restreint( $listing_droits_sigles , $restriction )
  {
    return (strpos($listing_droits_sigles,$restriction)!==FALSE);
  }

  /**
   * Tester si on a un droit d'accès spécifique
   *
   * @param string $listing_droits_sigles
   * @param int    $matiere_coord_or_groupe_pp_connu   si le droit comporte une restriction aux coordonnateurs matières | professeurs principaux, on peut déja connaitre et transmettre l'info (soit pour au moins une matière | classe, soit pour une matière | classe donnée)
   * @param int    $matiere_id_or_groupe_id_a_tester   si le droit comporte une restriction aux coordonnateurs matières | professeurs principaux, et si $matiere_coord_or_groupe_pp_connu n'est pas transmis, on peut chercher si le droit est bon soit pour une matière | classe donnée, soit pour au moins une matière | classe
   * @param string $forcer_profil                      pour forcer à tester un profil donné au lieu du profil de l'utilisateur
   * @return bool
   */
  public static function test_user_droit_specifique( $listing_droits_sigles , $matiere_coord_or_groupe_pp_connu=NULL , $matiere_id_or_groupe_id_a_tester=0 , $forcer_profil=NULL )
  {
    if( $forcer_profil=='TUT' )
    {
      // profil parent forcé pour des impressions de bilans officiels
      $user_profil_sigle = 'TUT' ;
      $user_profil_type  = 'parent' ;
    }
    elseif( $_SESSION['USER_PROFIL_SIGLE']=='ADM' )
    {
      // cas d'un admin qui archive des données d'un bilan officiel
      $user_profil_sigle = 'DIR' ;
      $user_profil_type  = 'directeur' ;
    }
    else
    {
      $user_profil_sigle = $_SESSION['USER_PROFIL_SIGLE'];
      $user_profil_type  = $_SESSION['USER_PROFIL_TYPE'];
    }
    $tableau_droits_sigles = explode(',',$listing_droits_sigles);
    $test_droit = in_array($user_profil_sigle,$tableau_droits_sigles);
    if( $test_droit && ($user_profil_type=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') && Outil::test_droit_specifique_restreint($listing_droits_sigles,'ONLY_PP') )
    {
      return ($matiere_coord_or_groupe_pp_connu!==NULL) ? (bool)$matiere_coord_or_groupe_pp_connu : DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'],$matiere_id_or_groupe_id_a_tester) ;
    }
    if( $test_droit && ($user_profil_type=='professeur') && ($_SESSION['USER_JOIN_MATIERES']=='config') && Outil::test_droit_specifique_restreint($listing_droits_sigles,'ONLY_COORD') )
    {
      return ($matiere_coord_or_groupe_pp_connu!==NULL) ? (bool)$matiere_coord_or_groupe_pp_connu : DB_STRUCTURE_PROFESSEUR::tester_prof_coordonnateur($_SESSION['USER_ID'],$matiere_id_or_groupe_id_a_tester) ;
    }
    return $test_droit;
  }

  /**
   * Afficher les profils ayant un droit d'accès spécifique
   *
   * @param string $listing_droits_sigles
   * @param string $format   "li" | "br"
   * @return bool
   */
  public static function afficher_profils_droit_specifique( $listing_droits_sigles , $format )
  {
    $tab_profils = array();
    $texte_testriction_pp    = Outil::test_droit_specifique_restreint($listing_droits_sigles,'ONLY_PP')    ? ' restreint aux professeurs principaux'  : '' ;
    $texte_testriction_coord = Outil::test_droit_specifique_restreint($listing_droits_sigles,'ONLY_COORD') ? ' restreint aux coordonnateurs matières' : '' ;
    $tableau_droits_sigles = explode(',',$listing_droits_sigles);
    foreach($tableau_droits_sigles as $droit_sigle)
    {
      if(isset($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]))
      {
        $profil_nom  = $_SESSION['TAB_PROFILS_DROIT']['NOM_LONG_PLURIEL'][$droit_sigle];
        $profil_nom .= ( ($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]=='professeur') && ($_SESSION['TAB_PROFILS_DROIT']['JOIN_GROUPES'][$droit_sigle]=='config')  ) ? $texte_testriction_pp    : '' ;
        $profil_nom .= ( ($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]=='professeur') && ($_SESSION['TAB_PROFILS_DROIT']['JOIN_MATIERES'][$droit_sigle]=='config') ) ? $texte_testriction_coord : '' ;
        $tab_profils[] = $profil_nom;
      }
    }
    if(!count($tab_profils))
    {
      $tab_profils[] = 'aucun !';
    }
    return ($format=='li') ? '<ul class="puce">'.NL.'<li>'.implode('</li>'.NL.'<li>',$tab_profils).'</li>'.NL.'</ul>'.NL : implode('<br />',$tab_profils) ;
  }

  /**
   * Afficher un texte tronqué au dela d'un certain nombre de caractères
   *
   * @param string $texte
   * @param int    $longueur_max
   * @return string
   */
  public static function afficher_texte_tronque( $texte , $longueur_max )
  {
    if( mb_strlen($texte) < $longueur_max )
    {
      return $texte;
    }
    $pos_espace = mb_strpos( $texte , ' ' , $longueur_max-10 );
    $chaine_de_fin = ' [...]';
    if($pos_espace!==FALSE)
    {
      return mb_substr( $texte , 0 , $pos_espace ).$chaine_de_fin;
    }
    return mb_substr( $texte , 0 , $longueur_max-5 ).$chaine_de_fin;
  }

  /**
   * Calculer la distance Levenshtein entre 2 chaines et retourner la réponse sous forme de pourcentage
   * Autre méthode dénichée mais non essayée : http://tonyarchambeau.com/blog/400-php-coefficient-de-dice/
   *
   * @see http://fr.php.net/levenshtein
   * @param string $string1
   * @param string $string2
   * @return floor
   */
  public static function pourcentage_commun( $string1 , $string2 )
  {
    // levenshtein() est sensible à la casse
    $string1 = Clean::lower($string1);
    $string2 = Clean::lower($string2);
    // levenshtein() requiert des arguments < 256 caractères (renvoie -1 sinon)
    $string1_longueur = min( mb_strlen($string1) , 255 );
    if($string1_longueur==255)
    {
      $string1 = substr($string1,0,255);
    }
    $string2_longueur = min( mb_strlen($string2) , 255 );
    if($string2_longueur==255)
    {
      $string2 = substr($string2,0,255);
    }
    // on compare les chaînes tronquées
    $nb_differences = levenshtein( $string1 , $string2 );
    // on calcule le pourcentage en commun ; max(*,0) car levenshtein() compte double les caractères accentués
    $longueur = max( $string1_longueur , $string2_longueur );
    $pourcent_commun = 100 * max( $longueur - $nb_differences , 0 ) / $longueur ;
    return $pourcent_commun;
  }

  /**
   * Formater les liens selon un code perso
   *
   * En attendant un éventuel textarea enrichi pour la saisie des messages (mais est-ce que ça ne risquerait pas de faire une page d'accueil folklorique ?), une petite fonction pour fabriquer des liens...
   * Format attendu : [desciptif|adresse] ou [desciptif|adresse|target]
   *
   * @param string $texte
   * @param int    $longueur_max
   * @param string $contexte   html | mail
   * @return string
   */
  public static function make_lien( $texte , $contexte )
  {
    $masque_recherche = '#\['.'([^\|\]]+)'.'\|'.'([^\|\]]+)'.'\|'.'([^\|\]]+)'.'\]#' ;
    $masque_remplacement = ($contexte=='html') ? '<a href="$2" target="$3">$1</a>' : '$1 [$2]' ;
    $texte = preg_replace( $masque_recherche , $masque_remplacement , $texte );
    $masque_recherche = '#\['.'([^\|\]]+)'.'\|'.'([^\|\]]+)'.'\]#' ;
    $masque_remplacement = ($contexte=='html') ? '<a href="$2">$1</a>' : '$1 [$2]' ;
    $texte = preg_replace( $masque_recherche , $masque_remplacement , $texte );
    return $texte;
  }

  /**
   * Ajout d'un log PHP dans le fichier error-log du serveur Web
   * 
   * @param string $log_objet       objet du log
   * @param string $log_contenu     contenu du log
   * @param string $log_fichier     transmettre __FILE__
   * @param string $log_ligne       transmettre __LINE__
   * @param bool   $only_sesamath   [TRUE] pour un log uniquement sur le serveur Sésamath, [FALSE] sinon
   * @return void
   */
  public static function ajouter_log_PHP( $log_objet , $log_contenu , $log_fichier , $log_ligne , $only_sesamath )
  {
    if( (!$only_sesamath) || (strpos(URL_INSTALL_SACOCHE,SERVEUR_PROJET)===0) )
    {
      $SEP = ' ║ ';
      $log_intro = ($only_sesamath) ? 'SACoche DEBUG' : 'SACoche INFO' ;
      error_log( $log_intro . $SEP . $log_objet . $SEP . 'base '.$_SESSION['BASE'] . $SEP . 'user '.$_SESSION['USER_ID'] . $SEP . basename($log_fichier).' '.$log_ligne . $SEP . $log_contenu , 0 );
    }
  }

}
?>