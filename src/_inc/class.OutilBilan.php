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

class OutilBilan
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Déterminer l'état de maitrise d'une composante du socle au vu du pourcentage d'items acquis transmis.
   * 
   * @param int    $pourcentage
   * @param array  $SESSION_LIVRET   pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return int
   */
  public static function determiner_degre_maitrise( $pourcentage , $SESSION_LIVRET=NULL )
  {
    if($pourcentage === FALSE)
    {
      return FALSE;
    }
    $tab_livret_seuil = ($SESSION_LIVRET==NULL) ? $_SESSION['LIVRET'] : $SESSION_LIVRET ;
    foreach( $tab_livret_seuil as $maitrise_id => $tab_maitrise_info )
    {
      if($pourcentage<=$tab_maitrise_info['SEUIL_MAX']) // On ne teste pas ($pourcentage>=$tab_maitrise_info['SEUIL_MIN']) car une valeur décimale saisie manuellement peut tomber entre les deux.
      {
        return $maitrise_id;
      }
    }
    return $maitrise_id; // Dans le cas d'un pourcentage > 100
  }

  /**
   * Déterminer l'état d'acquisition d'un item au vu du score transmis.
   * 
   * @param int    $score
   * @param array  $tab_acquis_seuil   facultatif (par défaut les valeurs en session, sauf pour une simulation)
   * @param array  $SESSION_ACQUIS     pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return int
   */
  public static function determiner_etat_acquisition( $score , $tab_acquis_seuil=NULL , $SESSION_ACQUIS=NULL )
  {
    $tab_acquis_seuil = (is_null($tab_acquis_seuil)) ? ( ($SESSION_ACQUIS==NULL) ? $_SESSION['ACQUIS'] : $SESSION_ACQUIS ) : $tab_acquis_seuil ;
    $score = min($score,100);
    foreach( $tab_acquis_seuil as $acquis_id => $tab_acquis_info )
    {
      if( ($score<=$tab_acquis_info['SEUIL_MAX']) && ($score>=$tab_acquis_info['SEUIL_MIN']) )
      {
        return $acquis_id;
      }
    }
  }

  /**
   * Tester si l'acquisition d'un item correspond à l'attente au vu du score transmis.
   * 
   * @param int    $score
   * @param string $etat_attendu     'acquis' | 'non_acquis'
   * @param array  $SESSION_ACQUIS   pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return bool
   */
  public static function tester_acquisition( $score , $etat_attendu , $SESSION_ACQUIS=NULL )
  {
    if($score === FALSE)
    {
      return FALSE;
    }
    $score = min($score,100);
    $SESSION_ACQUIS = ($SESSION_ACQUIS==NULL) ? $_SESSION['ACQUIS'] : $SESSION_ACQUIS ;
    foreach( $SESSION_ACQUIS as $acquis_id => $tab_acquis_info )
    {
      if( ($score<=$tab_acquis_info['SEUIL_MAX']) && ($score>=$tab_acquis_info['SEUIL_MIN']) )
      {
        return ( ($etat_attendu=='acquis') && ($tab_acquis_info['VALEUR']>50) ) || ( ($etat_attendu=='non_acquis') && ($tab_acquis_info['VALEUR']<50) ) ;
      }
    }
  }

  /**
   * Compter le nb d'états d'acquisition de chaque catégorie à partir d'un tableau de scores transmis.
   * 
   * @param array  $tab_score
   * @param array  $SESSION_ACQUIS   pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return array
   */
  public static function compter_nombre_acquisitions_par_etat( $tab_score , $SESSION_ACQUIS=NULL )
  {
    $SESSION_ACQUIS = ($SESSION_ACQUIS==NULL) ? $_SESSION['ACQUIS'] : $SESSION_ACQUIS ;
    $tab_result = array_fill_keys( array_keys($SESSION_ACQUIS) , 0 );
    foreach( $tab_score as $score )
    {
      $score = min($score,100);
      foreach( $SESSION_ACQUIS as $acquis_id => $tab_acquis_info )
      {
        if( ($score<=$tab_acquis_info['SEUIL_MAX']) && ($score>=$tab_acquis_info['SEUIL_MIN']) )
        {
          $tab_result[$acquis_id]++;
          break;
        }
      }
    }
    return $tab_result;
  }

  /**
   * Calculer le pourcentage d'acquisition des items à partir de l'état d'acquisition de chacun d'eux 
   * 
   * @param array  $tab_acquis
   * @param int    $nb_items
   * @param array  $SESSION_ACQUIS   pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return int
   */
  public static function calculer_pourcentage_acquisition_items( $tab_acquis , $nb_items , $SESSION_ACQUIS=NULL )
  {
    $total = 0;
    $SESSION_ACQUIS = ($SESSION_ACQUIS==NULL) ? $_SESSION['ACQUIS'] : $SESSION_ACQUIS ;
    foreach( $SESSION_ACQUIS as $acquis_id => $tab_acquis_info )
    {
      $acquis_nb = $tab_acquis[$acquis_id];
      $total += $acquis_nb * $tab_acquis_info['VALEUR'] ;
    }
    return round( $total / $nb_items ,0);
  }

  /**
   * Afficher le nb d'états d'acquisition de chaque catégorie à partir du nombre d'items acquis de chaque catégorie.
   * 
   * @param array  $tab_acquis
   * @param array  $SESSION_ACQUIS   pour surcharger une éventuelle valeur de session via l'impression d'une archive
   * @return string
   */
  public static function afficher_nombre_acquisitions_par_etat( $tab_acquis , $detail_couleur , $SESSION_ACQUIS=NULL )
  {
    $tab_texte = array();
    $SESSION_ACQUIS = ($SESSION_ACQUIS==NULL) ? $_SESSION['ACQUIS'] : $SESSION_ACQUIS ;
    foreach( $SESSION_ACQUIS as $acquis_id => $tab_acquis_info )
    {
      $span_avant = ($detail_couleur) ? '<span class="'.Html::$tab_couleur[$acquis_id].'" style="display:inline-block;padding:2px 1px">' : '' ;
      $span_apres = ($detail_couleur) ? '</span>' : '' ;
      $tab_texte[] = $span_avant.$tab_acquis[$acquis_id].$tab_acquis_info['SIGLE'].$span_apres;
    }
    krsort($tab_texte); // On commence par les acquis
    return implode(' ',$tab_texte);
  }

  /**
   * Calculer le score d'un item, à partir des notes transmises et des paramètres de calcul.
   * 
   * N'est pas appelé depuis une classe PDF donc pas besoin de surcharge de $_SESSION['NOTE']
   * 
   * @param array  $tab_devoirs      $tab_devoirs[$i]['note'] = note
   * @param string $calcul_methode   "geometrique" | "arithmetique" | "classique" | "bestof1" | "bestof2" | "bestof3" | "frequencemin" | "frequencemax"
   * @param int    $calcul_limite    nb maxi d'éval à prendre en compte
   * @param string $date_mysql_debut date de début de période, pour vérifier qu'il y a au moins une vraie note sur cette période
   * @return int|FALSE
   */
  public static function calculer_score( $tab_devoirs , $calcul_methode , $calcul_limite , $date_mysql_debut=NULL )
  {
    // on passe en revue les évaluations disponibles, et on retient les notes exploitables
    $tab_note = array(); // pour retenir les notes en question
    $is_note_periode = FALSE;
    $nb_devoir = count($tab_devoirs);
    for($i=0;$i<$nb_devoir;$i++)
    {
      if(isset($_SESSION['NOTE'][$tab_devoirs[$i]['note']])) // Note {1;...;6} prise en compte dans le calcul du score
      {
        $tab_note[] = $_SESSION['NOTE'][$tab_devoirs[$i]['note']]['VALEUR'];
        if( is_null($date_mysql_debut) || ($tab_devoirs[$i]['date']>=$date_mysql_debut) )
        {
          $is_note_periode = TRUE;
        }
      }
    }
    // si pas de notes exploitables, on arrête de suite (sinon, on est certain de pouvoir renvoyer un score)
    // idem si les seules notes exploitables le sont en dehors de la période considérée
    $nb_note = count($tab_note);
    if( !$nb_note || !$is_note_periode )
    {
      return FALSE;
    }
    // si le paramétrage du référentiel l'indique, on tronque pour ne garder que les derniers résultats
    if( ($calcul_limite) && ($nb_note>$calcul_limite) )
    {
      $tab_note = array_slice($tab_note,-$calcul_limite);
      $nb_note = $calcul_limite;
    }
    // 1. Calcul de la note en fonction de la méthode du référentiel : "geometrique" | "arithmetique" | "classique"
    if(in_array($calcul_methode,array('geometrique','arithmetique','classique')))
    {
      // 1a. Initialisation
      $somme_point = 0;
      $somme_coef = 0;
      $coef = 1;
      // 1b. Pour chaque devoir (note)...
      for($num_devoir=1 ; $num_devoir<=$nb_note ; $num_devoir++)
      {
        $somme_point += $tab_note[$num_devoir-1]*$coef;
        $somme_coef += $coef;
        $coef = ($calcul_methode=='geometrique') ? $coef*2 : ( ($calcul_methode=='arithmetique') ? $coef+1 : 1 ) ; // Calcul du coef de l'éventuel devoir suivant
      }
      // 1c. Calcul final du score
      return round( $somme_point/$somme_coef , 0 );
    }
    // 2. Calcul de la note en fonction de la méthode du référentiel : "bestof1" | "bestof2" | "bestof3" | "frequencemin" | "frequencemax"
    else
    {
      // 2a. Initialisation
      $tab_notes = array();
      $nb_best = (int)substr($calcul_methode,-1);
      // 2b. Pour chaque devoir (note)...
      for($num_devoir=1 ; $num_devoir<=$nb_note ; $num_devoir++)
      {
        $tab_notes[] = $tab_note[$num_devoir-1];
      }
      // 2c. Calcul final du score
      if( substr($calcul_methode,0,6) == 'bestof' )
      {
        // "bestof1" | "bestof2" | "bestof3"
        rsort($tab_notes);
        $tab_notes = array_slice( $tab_notes , 0 , $nb_best );
        return round( array_sum($tab_notes)/count($tab_notes) , 0 );
      }
      elseif( substr($calcul_methode,0,9) == 'frequence' )
      {
        // "frequencemin" | "frequencemax"
        $tab_frequences = array_count_values($tab_notes);
        arsort($tab_frequences);
        list( $score , $frequence_max ) = each($tab_frequences);
        unset($tab_frequences[$score]);
        foreach($tab_frequences as $score_autre => $frequence)
        {
          if($frequence!=$frequence_max)
          {
            break;
          }
          if( ( ($calcul_methode=='frequencemin') && ($score_autre<$score) ) || ( ($calcul_methode=='frequencemax') && ($score_autre>$score) ) )
          {
            $score = $score_autre;
          }
        }
        return $score;
      }
    }
  }

}
?>