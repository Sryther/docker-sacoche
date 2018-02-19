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

class HtmlForm
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Afficher un élément select de formulaire à partir d'un tableau de données et d'options
   * 
   * Les select multiples sont convertis en une liste de checkbox (code plus lourd, mais résultat plus maniable pour l'utilisateur).
   * Cela revient à remplacer ...
   *  <select id="select_nom" name="select_nom[]" multiple size="8">
   *    <optgroup label="Groupe A">
   *      <option value="2204" selected>Option 1</option>
   *      <option value="2206">Option 2</option>
   *    </optgroup>
   *  </select>
   * ... par ...
   *  <span id="select_nom" class="select_multiple">
   *    <span>Groupe A</span>
   *    <label for="select_nom_2204" class="check"><input type="checkbox" name="select_nom[]" id="select_nom_2204" value="2204" checked /> Option 1</label>
   *    <label for="select_nom_2206"><input type="checkbox" name="select_nom[]" id="select_nom_2206" value="2206" /> Option 2</label>
   *  </span>
   * 
   * @param array             $DB_TAB       tableau des données [i] => [valeur texte optgroup]
   * @param string|bool       $select_nom   chaine à utiliser pour l'id/nom du select, ou FALSE si on retourne juste les options sans les encapsuler dans un select (doit être transmis si $multiple l'est aussi)
   * @param string|bool       $option_first 1ère option éventuelle [FALSE] [] [nom_option]
   * @param string|bool|array $selection    préselection éventuelle [FALSE] [TRUE] [val] [ou $...] [ou array(...)]
   * @param string            $optgroup     regroupement d'options éventuel [] [nom_du_regroupement]
   * @param bool              $multiple     TRUE si transmis pour forcer un faux select multiple
   * @return string
   */
  public static function afficher_select( $DB_TAB , $select_nom , $option_first , $selection , $optgroup='' , $multiple=FALSE )
  {
    // On commence par la 1ère option
    if($option_first===FALSE)
    {
      // ... sans option initiale
      $options = '';
    }
    elseif($option_first==='')
    {
      // ... avec une option initiale vierge
      $options = (!$multiple) ? '<option value="">&nbsp;</option>' : '' ; // "&nbsp;" pour éviter l'erreur de conformité HTML ("Element option without attribute label must not be empty").
    }
    else
    {
      // ... avec une option initiale dont le contenu est à récupérer
      list($option_valeur,$option_texte) = Form::$tab_select_option_first[$option_first];
      $options = (!$multiple) ? '<option value="'.$option_valeur.'">'.html($option_texte).'</option>' : '<label for="'.$select_nom.'_'.$option_valeur.'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$option_valeur.'" value="'.$option_valeur.'" /> '.html($option_texte).'</label>' ;
    }
    if(is_array($DB_TAB))
    {
      // On construit les options...
      if(!$optgroup)
      {
        // ... classiquement, sans regroupements
        foreach($DB_TAB as $DB_ROW)
        {
          $options .= (!$multiple) ? '<option value="'.$DB_ROW['valeur'].'">'.html($DB_ROW['texte']).'</option>' : '<label for="'.$select_nom.'_'.$DB_ROW['valeur'].'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" /> '.html($DB_ROW['texte']).'</label>' ;
        }
      }
      else
      {
        // ... en regroupant par optgroup ; $tab_select_optgroup[$optgroup] est alors un tableau à 2 champs
        $tab_options = array();
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_options[$DB_ROW['optgroup']][] = (!$multiple) ? '<option value="'.$DB_ROW['valeur'].'">'.html($DB_ROW['texte']).'</option>' : '<label for="'.$select_nom.'_'.$DB_ROW['valeur'].'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" /> '.html($DB_ROW['texte']).'</label>' ;
        }
        foreach($tab_options as $group_key => $tab_group_options)
        {
          $options .= (!$multiple) ? '<optgroup label="'.html(Form::$tab_select_optgroup[$optgroup][$group_key]).'">'.implode('',$tab_group_options).'</optgroup>' : '<span>'.html(Form::$tab_select_optgroup[$optgroup][$group_key]).'</span>'.implode('',$tab_group_options) ;
        }
      }
      // On sélectionne les options qu'il faut... (fait après le foreach précédent sinon c'est compliqué à gérer simultanément avec les groupes d'options éventuels
      if($selection===FALSE)
      {
        // ... ne rien sélectionner
      }
      elseif($selection===TRUE)
      {
        // ... tout sélectionner
        $options = (!$multiple) ? str_replace( '<option' , '<option selected' , $options ) : str_replace( array('><input','" />') , array(' class="check"><input','" checked />') , $options ) ;
      }
      else
      {
        // ... sélectionner une ou plusieurs option(s) ; $selection contient la valeur ou le tableau de valeurs à sélectionner
        if(!is_array($selection))
        {
          $options = (!$multiple) ? str_replace( 'value="'.$selection.'"' , 'value="'.$selection.'" selected' , $options ) : str_replace( array($selection.'"><input',$selection.'" />') , array($selection.'" class="check"><input',$selection.'" checked />') , $options ) ;
        }
        else
        {
          foreach($selection as $selection_val)
          {
            $options = (!$multiple) ? str_replace( 'value="'.$selection_val.'"' , 'value="'.$selection_val.'" selected' , $options ) : str_replace( array('for="'.$select_nom.'_'.$selection_val.'"','value="'.$selection_val.'"') , array('for="'.$select_nom.'_'.$selection_val.'" class="check"','value="'.$selection_val.'" checked') , $options ) ;
          }
        }
      }
    }
    // Si $DB_TAB n'est pas un tableau alors c'est une chaine avec un message d'erreur affichée sous la forme d'une option disable
    else
    {
      $options .= (!$multiple) ? '<option value="" disabled>'.$DB_TAB.'</option>' : $DB_TAB;
    }
    // On insère dans un select si demandé
    return (!$multiple) ? ( ($select_nom) ? '<select id="'.$select_nom.'" name="'.$select_nom.'">'.$options.'</select>' : $options ) : $options ;
  }

  /**
   * Fabrication de tableau javascript de jointures à partir des groupes
   * 
   * @param array     $tab_groupes          tableau des données [i] => [valeur texte optgroup]
   * @param bool      $tab_groupe_periode   charger ou non "tab_groupe_periode" pour les jointures groupes/périodes
   * @param bool      $tab_groupe_niveau    charger ou non "tab_groupe_niveau"  pour les jointures groupes/niveaux
   * @return void     alimente Layout::$tab_js_inline[]
   */
  public static function fabriquer_tab_js_jointure_groupe( $tab_groupes , $tab_groupe_periode , $tab_groupe_niveau )
  {
    Layout::add( 'js_inline_before' , 'var tab_groupe_periode = new Array();' );
    Layout::add( 'js_inline_before' , 'var tab_groupe_niveau  = new Array();' );
    if(is_array($tab_groupes))
    {
      // On liste les ids des classes et groupes
      $tab_id_classe_groupe = array();
      foreach($tab_groupes as $tab_groupe_infos)
      {
        if( !isset($tab_groupe_infos['optgroup']) || ($tab_groupe_infos['optgroup']!='besoin') )
        {
          $tab_id_classe_groupe[] = $tab_groupe_infos['valeur'];
        }
      }
      if(count($tab_id_classe_groupe))
      {
        $listing_groupe_id = implode(',',$tab_id_classe_groupe);
        // Charger le tableau js $tab_groupe_periode de jointures groupes/périodes
        if($tab_groupe_periode)
        {
          $tab_memo_groupes = array();
          $DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_jointure_groupe_periode($listing_groupe_id);
          foreach($DB_TAB as $DB_ROW)
          {
            if(!isset($tab_memo_groupes[$DB_ROW['groupe_id']]))
            {
              $tab_memo_groupes[$DB_ROW['groupe_id']] = TRUE;
              Layout::add( 'js_inline_before' , 'tab_groupe_periode['.$DB_ROW['groupe_id'].'] = new Array();' );
            }
            Layout::add( 'js_inline_before' , 'tab_groupe_periode['.$DB_ROW['groupe_id'].']['.$DB_ROW['periode_id'].']="'.$DB_ROW['jointure_date_debut'].'_'.$DB_ROW['jointure_date_fin'].'";' );
          }
        }
        // Charger le tableau js $tab_groupe_niveau de jointures groupes/périodes
        if($tab_groupe_niveau)
        {
          $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_niveau_groupes($listing_groupe_id);
          foreach($DB_TAB as $DB_ROW)
          {
            Layout::add( 'js_inline_before' , 'tab_groupe_niveau['.$DB_ROW['groupe_id'].'] = new Array('.$DB_ROW['niveau_id'].',"'.html($DB_ROW['niveau_nom']).'");' );
          }
        }
      }
    }
  }

  /**
   * Retourner une liste HTML ordonnée des élèves (d'un professeur) pour chaque classe et groupe qui lui sont affectés, avec des cases à cocher.
   *
   * @param bool $with_pourcent
   * @return string
   */
  public static function afficher_checkbox_eleves_professeur($with_pourcent)
  {
    $affichage = '';
    $tab_regroupements = array();
    $tab_id = array('classe'=>'','groupe'=>'');
    // Recherche de la liste des classes et des groupes du professeur
    $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_regroupements[$DB_ROW['groupe_id']] = array('nom'=>$DB_ROW['groupe_nom'],'eleve'=>array());
      $tab_id[$DB_ROW['groupe_type']][] = $DB_ROW['groupe_id'];
    }
    // Recherche de la liste des élèves pour chaque classe du professeur
    if(is_array($tab_id['classe']))
    {
      $listing = implode(',',$tab_id['classe']);
      $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_classes($listing);
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_regroupements[$DB_ROW['eleve_classe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      }
    }
    // Recherche de la liste des élèves pour chaque groupe du professeur
    if(is_array($tab_id['groupe']))
    {
      $listing = implode(',',$tab_id['groupe']);
      $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_groupes($listing);
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_regroupements[$DB_ROW['groupe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      }
    }
    // Affichage de la liste des élèves (du professeur) pour chaque classe et groupe
    foreach($tab_regroupements as $groupe_id => $tab_groupe)
    {
      $gradient_pourcent = ($with_pourcent) ? '<span id="groupe_'.$groupe_id.'" class="gradient_pourcent"></span>' : '' ;
      $affichage .= '<ul class="ul_m1">'.NL;
      $affichage .=   '<li class="li_m1"><span class="deja">'.html($tab_groupe['nom']).'</span>'.$gradient_pourcent.NL;
      $affichage .=     '<ul class="ul_n3">'.NL;
      foreach($tab_groupe['eleve'] as $eleve_id => $eleve_nom)
      {
        // C'est plus compliqué que pour les items car un élève peut appartenir à une classe et plusieurs groupes => id du groupe mélé à l'id de l'élève
        $affichage .=       '<li class="li_n3"><input id="id_'.$eleve_id.'_'.$groupe_id.'" name="f_eleves[]" type="checkbox" value="'.$eleve_id.'" /><label for="id_'.$eleve_id.'_'.$groupe_id.'"> '.html($eleve_nom).'</label><span></span></li>'.NL;
      }
      $affichage .=     '</ul>'.NL;
      $affichage .=   '</li>'.NL;
      $affichage .= '</ul>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner, sur une ou plusieurs colonnes, une liste HTML ordonnée des professeurs, avec des cases à cocher.
   *
   * @param void
   * @return string
   */
  public static function afficher_checkbox_collegues()
  {
    $affichage = '';
    // Affichage de la liste des professeurs
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl();
    if(is_string($DB_TAB))
    {
      $affichage = $DB_TAB;
    }
    else
    {
      $nb_profs              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      $nb_profs_maxi_par_col = 20;
      $nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
      $nb_profs_par_col      = ceil($nb_profs/$nb_cols);
      $tab_div = array_fill(0,$nb_cols,'');
      foreach($DB_TAB as $i => $DB_ROW)
      {
        $checked_and_disabled = ($DB_ROW['valeur']!=$_SESSION['USER_ID']) ? '' : ' checked disabled' ; // readonly ne fonctionne pas sur un checkbox
        $tab_div[floor($i/$nb_profs_par_col)] .= '<label for="p_'.$DB_ROW['valeur'].'"><input type="checkbox" name="f_profs[]" id="p_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'"'.$checked_and_disabled.' /> '.html($DB_ROW['texte']).'</label><br />';
      }
      $affichage .= '<p><a href="#prof_liste" id="prof_check_all" class="cocher_tout">Tout le monde</a>&nbsp;&nbsp;&nbsp;<a href="#prof_liste" id="prof_uncheck_all" class="cocher_rien">Seulement moi</a></p>'.NL;
      $affichage .= '<div class="prof_liste">'.implode('</div>'.NL.'<div class="prof_liste">',$tab_div).'</div>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner, sur une ou plusieurs colonnes, une liste HTML ordonnée des professeurs, avec un formulaire de choix d'un attribut pour chacun.
   *
   * @param string  $only_profs
   * @param array   $tab_options
   * @return string
   */
  public static function afficher_select_collegues( $only_profs , $tab_options )
  {
    $affichage = '';
    // Affichage de la liste des professeurs
    $DB_TAB = ($only_profs) ? DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl() : DB_STRUCTURE_COMMUN::DB_OPT_professeurs_directeurs_etabl( 1 /*statut*/ );
    if(is_string($DB_TAB))
    {
      $affichage = $DB_TAB;
    }
    else
    {
      $nb_profs              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      $nb_profs_maxi_par_col = 20;
      $nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
      $nb_profs_par_col      = ceil($nb_profs/$nb_cols);
      $tab_div = array_fill(0,$nb_cols,'');
      $select_options = '<option value="x">0</option>';
      foreach($tab_options as $option_texte => $option_value)
      {
        $select_options .= '<option value="'.$option_value.'">'.$option_texte.'</option>';
      }
      foreach($DB_TAB as $i => $DB_ROW)
      {
        if($DB_ROW['valeur']!=$_SESSION['USER_ID'])
        {
          $tab_div[floor($i/$nb_profs_par_col)] .= '<select id="p_'.$DB_ROW['valeur'].'" name="p_'.$DB_ROW['valeur'].'" class="t9">'.$select_options.'</select><span class="select_img droit_x">&nbsp;</span><label>'.html($DB_ROW['texte']).'</label><br />';
        }
        else
        {
          $tab_div[floor($i/$nb_profs_par_col)] .= '<select id="p_'.$DB_ROW['valeur'].'" name="p_'.$DB_ROW['valeur'].'" class="t9" disabled><option value="z">4</option></select><span class="droit_z">&nbsp;</span><label>'.html($DB_ROW['texte']).'</label><br />';
        }
      }
      $affichage .= '<p class="hc">Choisir <label for="p_0_x"><input type="radio" name="prof_check_all" id="p_0_x" value="x" /><span class="select_img droit_x">&nbsp;</span></label>';
      foreach($tab_options as $option_value)
      {
        $affichage .= ' ou <label for="p_0_'.$option_value.'"><input type="radio" name="prof_check_all" id="p_0_'.$option_value.'" value="'.$option_value.'" /><span class="select_img droit_'.$option_value.'">&nbsp;</span></label>';
      }
      $affichage .= ' pour tout le monde.</p>'.NL;
      $affichage .= '<div class="prof_liste">'.implode('</div>'.NL.'<div class="prof_liste">',$tab_div).'</div>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner, sur une ou plusieurs colonnes, une liste HTML ordonnée des matières, avec des cases à cocher.
   *
   * @param void
   * @return string
   */
  public static function afficher_checkbox_matieres()
  {
    $affichage = '';
    // Affichage de la liste des matières
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
    if(is_string($DB_TAB))
    {
      $affichage = $DB_TAB;
    }
    else
    {
      $nb_matieres              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      $nb_matieres_maxi_par_col = 20;
      $nb_cols                  = floor(($nb_matieres-1)/$nb_matieres_maxi_par_col)+1;
      $nb_matieres_par_col      = ceil($nb_matieres/$nb_cols);
      $tab_div = array_fill(0,$nb_cols,'');
      foreach($DB_TAB as $i => $DB_ROW)
      {
        $tab_div[floor($i/$nb_matieres_par_col)] .= '<label for="m_'.$DB_ROW['valeur'].'"><input type="checkbox" name="f_matieres[]" id="m_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" /> '.html($DB_ROW['texte']).'</label><br />';
      }
      $affichage .= '<div class="matiere_liste">'.implode('</div>'.NL.'<div class="matiere_liste">',$tab_div).'</div>'.NL;
    }
    return $affichage;
  }

  /**
   * Retourner un formulaire à insérer sur un bilan pour enchaîner sur la création d'un groupe de besoin ou d'une évaluation.
   * 
   * @param string $cases   "eleves" | "eleves + eleves-items" | "eleves + eleves-items + items"
   * @return string
   */
  public static function afficher_synthese_exploitation($cases)
  {
    $option_evaluer_items_commun = ($cases=='eleves + eleves-items + items') ? '<option value="evaluer_items_commun">Évaluer des élèves sur des items communs</option>' : '' ;
    return ($cases=='eleves') ?
    '<p>'.
      '<label class="tab">Action <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Cocher auparavant les cases adéquates." /> :</label>'.
      '<button type="button" class="ajouter" name="evaluation_gestion">Préparer une évaluation.</button> '.
      '<button type="button" class="ajouter" name="professeur_groupe_besoin">Constituer un groupe de besoin.</button> '.
      '<label id="check_msg"></label>'.
    '</p>'.NL
    :
    '<p>'.
      '<label class="tab" for="f_action">Action :</label>'.
      '<select id="f_action" name="f_action">'.
        '<option value="">&nbsp;</option>'.
        '<option value="evaluer_items_perso">Évaluer des élèves sur des items personnalisés</option>'.
        $option_evaluer_items_commun.
        '<option value="constituer_groupe_besoin">Constituer un groupe de besoin</option>'.
      '</select><br />'.
      '<span id="span_submit" class="hide">'.
        '<span class="tab"></span>Cocher les cases adéquates puis <button type="button" id="f_submit" class="parametre">accéder au formulaire</button>'.
        '<label id="check_msg"></label>'.
      '</span>'.
    '</p>'.NL
    ;
  }

}
?>