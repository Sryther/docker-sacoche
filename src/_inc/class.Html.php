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

class Html
{

  // //////////////////////////////////////////////////
  // Tableaux prédéfinis
  // //////////////////////////////////////////////////

  // sert pour le tri d'un tableau de notes Lomer
  // correspond à $tab_tri_note = array_flip(array('1','2','3','4','5','6','AB','NE','NF','NN','NR','DI','PA','-',''));
  private static $tab_tri_note = array('1'=>0,'2'=>1,'3'=>2,'4'=>3,'5'=>4,'6'=>5,'AB'=>6,'NE'=>7,'NF'=>8,'NN'=>9,'NR'=>10,'DI'=>11,'PA'=>12,'-'=>13,''=>14);

  // sert pour le tri du tableau de scores bilans dans le cas d'un tri par état d'acquisition
  // correspond à $tab_tri_note = array_flip(array('A1','A2','A3','A4','A5','A6'));
  private static $tab_tri_etat = array( 'A1'=>0 , 'A2'=>1 , 'A3'=>2 , 'A4'=>3 , 'A5'=>4 , 'A6'=>5 );

  // sert pour indiquer la classe css d'un état d'acquisition
  public static $tab_couleur = array( '1'=>'A1' , '2'=>'A2' , '3'=>'A3' , '4'=>'A4' , '5'=>'A5' , '6'=>'A6' );

  // sert pour indiquer la légende des notes spéciales
  private static $tab_legende_notes_speciales_texte  = array('AB'=>'Absent','DI'=>'Dispensé','NE'=>'Non évalué','NF'=>'Non fait','NN'=>'Non noté','NR'=>'Non rendu');
  public  static $tab_legende_notes_speciales_nombre = array('AB'=>0       ,'DI'=>0         ,'NE'=>0           ,'NF'=>0         ,'NN'=>0         ,'NR'=>0          );

  // remarque : des tableaux réciproques sont aussi utilisés en javascript
  public static $tab_genre = array(
    'enfant' => array( 'I'=>'' , 'M'=>'Masculin' , 'F'=>'Féminin' ) ,
    'adulte' => array( 'I'=>'' , 'M'=>'M.'       , 'F'=>'Mme'     ) ,
  );
  
  // A renseigner une fois au début mais pas à chaque appel
  public static $afficher_score = NULL;
  
  // A renseigner une fois au début mais pas à chaque appel
  public static $afficher_degre = NULL;

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Convertir une date MySQL ou française en un texte avec le nom du mois en toutes lettres.
   *
   * @param string $date   AAAA-MM-JJ ou JJ/MM/AAAA
   * @return string        JJ nom_du mois AAAA
   */
  public static function date_texte($date)
  {
    if(mb_strpos($date,'-')) { list($annee,$mois,$jour) = explode('-',$date); }
    else                     { list($jour,$mois,$annee) = explode('/',$date); }
    $tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
    return $jour.' '.$tab_mois[$mois].' '.$annee;
  }

  /**
   * Renvoyer le chemin d'une note (code couleur) pour une sortie HTML.
   * Le daltonisme a déjà été pris en compte pour forger $_SESSION['NOTE'][i]['FICHIER']
   *
   * @param string $note
   * @return string
   */
  public static function note_src( $note )
  {
    return (isset($_SESSION['NOTE'][$note])) ? $_SESSION['NOTE'][$note]['FICHIER'] : Html::note_src_commun($note) ;
  }

  /**
   * Valeur de l'attribut "src" pour un symbole de notation
   * Chemin relatif à la racine ; pas besoin de rajouter '.' pour une feuille de style car contenu de session personnalisé dans le fichier PHP et non dans le fichier CSS.
   *
   * @param string $symbole_nom
   * @param string $symbole_orientation   h | v
   * @param string $symbole_type          sacoche | perso
   * @return string
   */
  public static function note_src_couleur( $symbole_nom , $symbole_orientation='h' , $symbole_type='' )
  {
    if(!$symbole_type)
    {
      $symbole_type = (substr($symbole_nom,0,6)=='upload') ? 'perso' : 'sacoche' ;
    }
    if($symbole_type=='sacoche')
    {
      return './_img/note/choix/'.$symbole_orientation.'/'.$symbole_nom.'.gif';
    }
    if($symbole_type=='perso')
    {
      return './__tmp/symbole/'.$_SESSION['BASE'].'/'.$symbole_orientation.'_'.$symbole_nom.'.gif';
    }
    // On ne devrait pas arriver ici
    return'';
  }

  /**
   * Valeur de l'attribut "src" pour un symbole de notation adapté au daltonisme
   * Chemin relatif à la racine ; pas besoin de rajouter '.' pour une feuille de style car contenu de session personnalisé dans le fichier PHP et non dans le fichier CSS.
   *
   * @param int    $numero
   * @return string
   */
  public static function note_src_daltonisme( $numero )
  {
    return './_img/note/daltonisme/h/'.$_SESSION['NOMBRE_CODES_NOTATION'].$numero.'.gif';
  }

  /**
   * Valeur de l'attribut "src" pour un symbole de notation commun (AB, etc.)
   * Chemin relatif à la racine ; pas besoin de rajouter '.' pour une feuille de style car contenu de session personnalisé dans le fichier PHP et non dans le fichier CSS.
   *
   * @param string $symbole_nom
   * @return string
   */
  public static function note_src_commun( $symbole_nom )
  {
    return './_img/note/commun/h/'.$symbole_nom.'.gif';
  }

  /**
   * Afficher une note (code couleur) pour une sortie HTML.
   *
   * @param string $note
   * @param string $date
   * @param string $info
   * @param bool   $tri
   * @return string
   */
  public static function note_image( $note , $date , $info , $tri=FALSE )
  {
    if(isset(Html::$tab_legende_notes_speciales_nombre[$note])) Html::$tab_legende_notes_speciales_nombre[$note]++;
    $insert_tri = ($tri) ? '<i>'.Html::$tab_tri_note[$note].'</i>' : '';
    $title = ( ($date!='') || ($info!='') ) ? ' title="'.html(html($info)).'<br />'.Html::date_texte($date).'"' : '' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
    return (in_array($note,array('-',''))) ? '&nbsp;' : $insert_tri.'<img'.$title.' alt="'.$note.'" src="'.Html::note_src($note).'" />';
  }

  /**
   * Afficher un score bilan pour une sortie HTML.
   *
   * @param int|FALSE $score
   * @param string    $methode_tri    'score' | 'etat'
   * @param string    $pourcent       '%' | ''
   * @param string    $checkbox_val   pour un éventuel checkbox
   * @return string
   */
  public static function td_score( $score , $methode_tri , $pourcent='' , $checkbox_val='' )
  {
    if( Html::$afficher_score === NULL )
    {
      // En cas de bilan officiel, doit être déterminé avant
      Html::$afficher_score = Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_BILAN']);
    }
    $checkbox = ($checkbox_val) ? ' <input type="checkbox" name="id_req[]" value="'.$checkbox_val.'" />' : '' ;
    if($score===FALSE)
    {
      $affichage = (Html::$afficher_score) ? '-' : '' ;
      return '<td class="hc">'.$affichage.$checkbox.'</td>';
    }
    $class = 'A'.OutilBilan::determiner_etat_acquisition($score);
    $affichage = (Html::$afficher_score) ? $score.$pourcent : '' ;
    $tri = ($methode_tri=='score') ? $score : Html::$tab_tri_etat[$class] ;
    return '<td class="hc '.$class.'" data-sort="'.$tri.'">'.$affichage.$checkbox.'</td>';
  }

  /**
   * Afficher un degré de maîtrise pour une sortie HTML.
   *
   * @param int|FALSE $indice
   * @param int|FALSE $pourcentage
   * @param string    $methode_tri    'score' | 'etat'
   * @param string    $pourcent       '%' | '' | 'pts'
   * @param bool      $all_columns
   * @return string
   */
  public static function td_maitrise( $indice , $pourcentage , $methode_tri='score' , $pourcent='' , $all_columns=TRUE )
  {
    if( Html::$afficher_degre === NULL )
    {
      // En cas de bilan officiel, doit être déterminé avant
      Html::$afficher_degre = Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_MAITRISE']);
    }
    if($pourcentage===FALSE)
    {
      $colspan = ($all_columns) ? ' colspan="4"' : '' ;
      return '<td'.$colspan.' class="hc">-</td>';
    }
    elseif($all_columns)
    {
      $td = '';
      $tri = ($methode_tri=='pourcentage') ? $pourcentage : $indice ;
      for( $i=1 ; $i<5 ; $i++ )
      {
        $class = 'M'.$i;
        if($i==$indice)
        {
          $affichage = (Html::$afficher_degre) ? $pourcentage.$pourcent : 'X' ;
        }
        else
        {
          $affichage = '';
        }
        $td .= '<td class="hc '.$class.'" data-sort="'.$tri.'">'.$affichage.'</td>';
      }
      return $td;
    }
    else
    {
      $class = 'M'.$indice;
      $affichage = (Html::$afficher_degre) ? $pourcentage.$pourcent : '' ;
      $tri = ($methode_tri=='pourcentage') ? $pourcentage : $indice ;
      return '<td class="hc '.$class.'" data-sort="'.$tri.'">'.$affichage.'</td>';
    }
  }

  /**
   * Initialiser la légende des codes de notation spéciaux
   *
   * @param void
   * @return void
   */
  public static function legende_initialiser()
  {
    Html::$tab_legende_notes_speciales_nombre = array_fill_keys( array_keys(Html::$tab_legende_notes_speciales_nombre) , 0 );
  }

  /**
   * Afficher la légende pour une sortie HTML.
   *
   * "force_nb" pour "etat_acquisition" seulement
   * "force_nb" si un item a été surligné
   *
   * @param array $tab_legende   tableau de clefs parmi "codes_notation" ; "anciennete_notation" ; "score_bilan" ; "etat_acquisition" ; "degre_maitrise" ; "socle_points" ; "force_nb" ; "highlight"
   * @return string
   */
  public static function legende( $tab_legende )
  {
    // initialisation variables
    $retour = '';
    // légende codes_notation
    if(!empty($tab_legende['codes_notation']))
    {
      $retour .= '<div><b>Codes d\'évaluation :</b>';
      foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
      {
        $retour .= '<img alt="'.html($_SESSION['NOTE'][$note_id]['SIGLE']).'" src="'.Html::note_src($note_id).'" />'.html($_SESSION['NOTE'][$note_id]['LEGENDE']);
      }
      foreach(Html::$tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $retour .= '<img alt="'.$note.'" src="'.Html::note_src($note).'" />'.html(Html::$tab_legende_notes_speciales_texte[$note]);
        }
      }
      Html::legende_initialiser();
      $retour .= '</div>'.NL;
    }
    // légende ancienneté notation
    if(!empty($tab_legende['anciennete_notation']))
    {
      $retour .= '<div><b>Ancienneté :</b>';
      $retour .=   '<span class="cadre">Sur la période.</span>';
      $retour .=   '<span class="cadre prev_date">Début d\'année scolaire.</span>';
      $retour .=   '<span class="cadre prev_year">Année scolaire précédente.</span>';
      $retour .= '</div>'.NL;
    }
    // légende scores bilan
    if(!empty($tab_legende['score_bilan']))
    {
      if( Html::$afficher_score === NULL )
      {
        // En cas de bilan officiel, doit être déterminé avant
        Html::$afficher_score = Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_BILAN']);
      }
      $retour .= '<div><b>États d\'acquisitions :</b>';
      foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
      {
        $texte_seuil = (Html::$afficher_score) ? $tab_acquis_info['SEUIL_MIN'].' à '.$tab_acquis_info['SEUIL_MAX'] : '' ;
        $retour .= '<span class="cadre A'.$acquis_id.'">'.$texte_seuil.'</span>'.html($tab_acquis_info['LEGENDE']);
      }
      $retour .= '</div>'.NL;
    }
    // légende etat_acquisition
    if(!empty($tab_legende['etat_acquisition']))
    {
      $force_nb = !empty($tab_legende['force_nb']) ? TRUE : FALSE ;
      $retour .= '<div><b>États d\'acquisitions :</b>';
      foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
      {
        $class = (!$force_nb) ? ' A'.$acquis_id : '' ;
        $retour .= '<span class="cadre'.$class.'">'.html($tab_acquis_info['SIGLE']).'</span>'.html($tab_acquis_info['LEGENDE']);
      }
      $retour .= '</div>'.NL;
    }
    // légende degrés de maîtrise du socle
    if(!empty($tab_legende['degre_maitrise']))
    {
      if( Html::$afficher_degre === NULL )
      {
        // En cas de bilan officiel, doit être déterminé avant
        Html::$afficher_degre = Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_MAITRISE']);
      }
      $retour .= '<div><b>Maîtrise :</b>'; // Degrés de maîtrise
      foreach( $_SESSION['LIVRET'] as $maitrise_id => $tab_maitrise_info )
      {
        !
        $texte_seuil = (Html::$afficher_degre) ? $tab_maitrise_info['SEUIL_MIN'].' à '.$tab_maitrise_info['SEUIL_MAX'] : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;
        $texte_legende = empty($tab_legende['socle_points']) ? $tab_maitrise_info['LEGENDE'] : $tab_maitrise_info['LEGENDE'].' ('.$tab_maitrise_info['POINTS'].' pts)' ;
        $texte_legende = ucfirst( str_replace( array('Maîtrise ','maîtrise ') , '' , $texte_legende ) ); // Peut sinon ne pas rentrer sur une ligne
        $retour .= '<span class="cadre maitrise M'.$maitrise_id.'">'.$texte_seuil.'</span>'.html($texte_legende);
      }
      $retour .= '</div>'.NL;
    }
    // légende surlignage
    if(!empty($tab_legende['highlight']))
    {
      $retour .= '<div><b>Item :</b> <span class="fluo">&nbsp;Intitulé sur lequel vous aviez cliqué !&nbsp;</span></div>'.NL;
    }
    // retour
    return ($retour) ? '<h3>Légende</h3>'.NL.'<div class="legende">'.NL.$retour.'</div>'.NL : '' ;
  }

  /**
   * Afficher une barre colorée de synthèse des états d'acquisition pour une sortie HTML.
   *
   * @param int     $td_width
   * @param array   $tab_infos   array( acquis_id )
   * @param int     $total
   * @return string
   */
  public static function td_barre_synthese( $td_width , $tab_infos , $total , $avec_texte_nombre , $avec_texte_code )
  {
    $span = '';
    foreach($tab_infos as $acquis_id => $nb)
    {
      $span_width = $td_width * $nb / $total ;
          if(  $avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $nb.' '.$_SESSION['ACQUIS'][$acquis_id]['SIGLE']; }
      elseif( !$avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $_SESSION['ACQUIS'][$acquis_id]['TEXTE']; }
      elseif( !$avec_texte_nombre && !$avec_texte_code ) { $texte_complet = '&nbsp;'; }
      elseif(  $avec_texte_nombre && !$avec_texte_code ) { $texte_complet = $nb; }
      $texte = ( (5*strlen($texte_complet)<$span_width) || !$avec_texte_code ) ? $texte_complet : ( ($avec_texte_nombre) ? $nb : '&nbsp;' ) ;
      $span .= '<span class="'.Html::$tab_couleur[$acquis_id].'" style="display:inline-block;width:'.$span_width.'px;padding:2px 0">'.$texte.'</span>';
    }
    return '<td style="padding:0;width:'.$td_width.'px" class="hc">'.$span.'</td>';
  }

  /**
   * Afficher un pourcentage d'items acquis pour une sortie socle HTML ou bulletin.
   *
   * @param string   $type_cellule   'td' | 'th'
   * @param array    $tab_infos      array( acquis_id , 'nb' , '%' )
   * @param bool     $detail
   * @param int|bool $largeur        en nombre de pixels
   * @return string
   */
  public static function td_pourcentage( $type_cellule , $tab_infos , $detail , $largeur )
  {
    if($tab_infos['%']===FALSE)
    {
      $texte = ($detail) ? '---' : '-' ; // Mettre qq chose sinon en mode daltonien le gris de la case se confond avec les autres couleurs.
      return '<'.$type_cellule.' class="hc">'.$texte.'</'.$type_cellule.'>' ;
    }
    $class = 'A'.OutilBilan::determiner_etat_acquisition( $tab_infos['%'] );
    $detail_acquisition = OutilBilan::afficher_nombre_acquisitions_par_etat( $tab_infos , FALSE /*detail_couleur*/ );
    $style = ($largeur) ? ' style="width:'.$largeur.'px"' : '' ;
    $texte = html($tab_infos['%'].'% acquis ('.$detail_acquisition.')');
    return ($detail) ? '<'.$type_cellule.' class="hc '.$class.'"'.$style.'>'.$texte.'</'.$type_cellule.'>' : '<'.$type_cellule.' class="'.$class.'" title="'.$texte.'"></'.$type_cellule.'>';
  }

}
?>