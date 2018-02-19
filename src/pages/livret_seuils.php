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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Notation / Seuils"));

if( ($_SESSION['USER_PROFIL_TYPE']!='directeur') && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Seuils les administrateurs et les personnels de direction peuvent consulter cette page.</div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_seuils">DOC : Administration du Livret Scolaire &rarr; Notation / Seuils</a></span></li>
  <li><span class="astuce">Les seuils de <b>maîtrise des composantes du socle</b> pour les <b>bilans de fin de cycle du Livret Scolaire</b> sont aussi utilisés pour le <b>bilan <em>SACoche</em> "Maîtrise du socle (2016)"</b>.</span></li>
</ul>

<hr />

<?php
// On liste les pages et leurs paramètres principaux
$DB_TAB_pages = DB_STRUCTURE_LIVRET::DB_lister_pages( TRUE /*with_info_classe*/ );

// On récupère les infos sur les paramètres généraux des colonnes
$DB_TAB_colonnes = DB_STRUCTURE_LIVRET::DB_lister_colonnes_infos();

// On récupère les infos sur les seuils enregistrés
$DB_TAB_seuils = DB_STRUCTURE_LIVRET::DB_lister_seuils_valeurs();

$tab_bad = array( " - Bilan de l'acquisition des connaissances et compétences" , " - Synthèse des acquis scolaires" , "Synthèse"    , "Suivi"    , "Maîtrise"    );
$tab_bon = array( ""                                                           , ""                                 , "la synthèse" , "le suivi" , "la maîtrise" );

$tab_etat_txt = array(
  'reussite' => "degrés de réusssite",
  'objectif' => "objectifs d'apprentissage",
  'maitrise' => "degrés de maîtrise",
);

$tab_element_txt = array(
  'theme'   => "thème du livret",
  'domaine' => "domaine du livret",
  'socle'   => "composante du socle",
  'matiere' => "matière du livret",
);

$tab_colonne_choix = array(
  'moyenne'     => "Moyenne sur 20",
  'pourcentage' => "Pourcentage",
  'position'    => "Échelle de 1 à 4",
  'objectif'    => "Objectifs d'apprentissage",
);

$tab_moyenne_choix = array(
  1 => "Avec",
  0 => "Sans",
);

$nb_pages = 0;

foreach($DB_TAB_pages as $DB_ROW_page)
{
  if(!$DB_ROW_page['livret_page_rubrique_type']) // hors-brevet
  {
    continue;
  }
  if($DB_ROW_page['groupe_nb'])
  {
    $nb_pages++;
    $is_page_choix_positionnement = in_array( $DB_ROW_page['livret_page_rubrique_type'] , array('c3_matiere','c4_matiere') ) ? TRUE : FALSE ;
    $tab_indice_colonne = (!$is_page_choix_positionnement) ? array( $DB_ROW_page['livret_page_colonne'] ) : array( 'objectif','position' ) ;
    $etat_nb = (!$is_page_choix_positionnement) ? count($DB_TAB_colonnes[$DB_ROW_page['livret_page_colonne']]) : 4 ;
    $vignette = '<a href="'.SERVEUR_LSU_PDF.'livret_'.$DB_ROW_page['livret_page_ref'].'.pdf" class="fancybox" rel="gallery" data-titre="'.html($DB_ROW_page['livret_page_moment'].' : '.$DB_ROW_page['livret_page_resume']).'"><span class="livret livret_float_seuils livret_'.$DB_ROW_page['livret_page_ref'].'"></span></a>';
    $objet = str_replace($tab_bad,$tab_bon,$DB_ROW_page['livret_page_resume']);
    $etat_txt = (!$is_page_choix_positionnement)
              ? '<b>'.$etat_nb.' '.$tab_etat_txt[$DB_ROW_page['livret_page_colonne']].'</b>'
              : '<b>une moyenne sur 20</b> ou <b>un pourcentage</b> ou <b>une échelle à 4 niveaux</b> ou <b>4 objectifs d\'apprentissages</b>';
    $rubrique_type = substr($DB_ROW_page['livret_page_rubrique_type'],3);
    $element_txt = $tab_element_txt[$rubrique_type];
    $methode_txt = ($rubrique_type!='socle')
              ? 'de <b>la moyenne des scores des items évalués</b>'
              : 'du <b> pourcentage d\'items acquis</b>';
    $choix_txt = (!$is_page_choix_positionnement)
              ? 'Les seuils sont modifiables</b>'
              : 'Le choix du mode de positionnement est modifiable</b>';
    echo'<h2 id="'.$DB_ROW_page['livret_page_ref'].'">'.html($DB_ROW_page['livret_page_moment']).'</h2>';
    echo $vignette;
    echo'<p>Concernant <b>'.$objet.'</b>, le positionnement s\'effectue selon '. $etat_txt.'.</p>';
    echo'<p>Pour chaque <b>'.$element_txt.'</b>, le niveau de l\'élève est prépositionné en fonction '.$methode_txt.' qui y sont reliés.</p>';
    echo'<p>'.$choix_txt.' ci-dessous.</p>';
    echo'<form action="#" method="post" id="form_'.$DB_ROW_page['livret_page_ref'].'">';
    if($is_page_choix_positionnement)
    {
      echo'<p><label class="tab">Positionnement :</label>';
      foreach($tab_colonne_choix as $choix_value => $choix_txt)
      {
        $radio_name = 'choix_'.$DB_ROW_page['livret_page_ref'];
        $radio_id   = $radio_name.'_'.$choix_value;
        $checked = ($choix_value==$DB_ROW_page['livret_page_colonne']) ? ' checked' : '' ;
        echo'<label for="'.$radio_id.'"><input type="radio" name="'.$radio_name.'" id="'.$radio_id.'" value="'.$choix_value.'"'.$checked.' /> '.$choix_txt.'</label>&nbsp;&nbsp;&nbsp;';
      }
      echo'<br />';
      echo'<label class="tab">Moyenne classe :</label>';
      foreach($tab_moyenne_choix as $moyenne_value => $moyenne_txt)
      {
        $radio_name = 'moyenne_'.$DB_ROW_page['livret_page_ref'];
        $radio_id   = $radio_name.'_'.$moyenne_value;
        $checked = ($moyenne_value==$DB_ROW_page['livret_page_moyenne_classe']) ? ' checked' : '' ;
        echo'<label for="'.$radio_id.'"><input type="radio" name="'.$radio_name.'" id="'.$radio_id.'" value="'.$moyenne_value.'"'.$checked.' /> '.$moyenne_txt.'</label>&nbsp;&nbsp;&nbsp;';
      }
      echo'</p>';
    }
    else
    {
      echo'<div class="hide"><input type="hidden" name="choix_'.$DB_ROW_page['livret_page_ref'].'" id="choix_'.$DB_ROW_page['livret_page_ref'].'" value="'.$DB_ROW_page['livret_page_colonne'].'" /></div>';
    }
    foreach( $tab_indice_colonne as $indice_colonne )
    {
      $td = '' ;
      $q_modifier = ($indice_colonne=='position') ? '<q class="modifier" title="Modifier la légende."></q>' : '' ;
      $DB_TAB_colonne = $DB_TAB_colonnes[$indice_colonne];
      foreach( $DB_TAB_colonne as $DB_ROW_colonne )
      {
        $id_page_col = $DB_ROW_page['livret_page_ref'].'_'.$DB_ROW_colonne['livret_colonne_id'];
        $readonly_min = ( $DB_TAB_seuils[$id_page_col][0]['livret_seuil_min'] ==   0 ) ? ' readonly' : '' ;
        $readonly_max = ( $DB_TAB_seuils[$id_page_col][0]['livret_seuil_max'] == 100 ) ? ' readonly' : '' ;
        $input_min = '<input type="number" min="0" max="99"  class="hc" id="seuil_'.$id_page_col.'_min" name="seuil_'.$id_page_col.'_min" value="'.$DB_TAB_seuils[$id_page_col][0]['livret_seuil_min'].'" data-defaut="'.$DB_ROW_colonne['livret_colonne_seuil_defaut_min'].'"'.$readonly_min.' />';
        $input_max = '<input type="number" min="1" max="100" class="hc" id="seuil_'.$id_page_col.'_max" name="seuil_'.$id_page_col.'_max" value="'.$DB_TAB_seuils[$id_page_col][0]['livret_seuil_max'].'" data-defaut="'.$DB_ROW_colonne['livret_colonne_seuil_defaut_max'].'"'.$readonly_max.' />';
        $td .= '<td style="background-color:'.$DB_ROW_colonne['livret_colonne_couleur_1'].';text-align:center;width:12em"><p><b id="'.$id_page_col.'_legende">'.html($DB_ROW_colonne['livret_colonne_legende']).'</b>'.$q_modifier.'</p><p>'.$input_min.'~'.$input_max.'</p></td>';
      }
      $td .= '<td class="nu"><button name="initialiser" type="button" class="retourner">Seuils par défaut</button></td>' ;
      $class = ( !$is_page_choix_positionnement || ($DB_ROW_page['livret_page_colonne']==$indice_colonne) ) ? 'show' : 'hide' ;
      echo'<table id="table_'.$DB_ROW_page['livret_page_ref'].'_'.$indice_colonne.'" class="'.$class.'"><thead><tr><th colspan="'.$etat_nb.'" class="hc">'.html($DB_ROW_colonne['livret_colonne_titre']).'</th><td class="nu"></td></tr></thead><tbody><tr>'.$td.'</tr></tbody></table>';
    }
    echo'<p><span class="tab"></span><button type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_'.$DB_ROW_page['livret_page_ref'].'">&nbsp;</label></p>';
    echo'</form>';
    echo'<p /><hr />';
  }
}

if(!$nb_pages)
{
  echo'<p class="danger">Aucune classe n\'est associée à une page du livret !<br />Commencez par <a href="./index.php?page=livret&amp;section=classes">associer les classes au livret scolaire</a>.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>
