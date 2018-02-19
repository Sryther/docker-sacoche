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
$TITRE = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? html(Lang::_("Définir le format de synthèse par référentiel")) : html(Lang::_("Format de synthèse par référentiel")) ;

if( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_MODE_SYNTHESE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_MODE_SYNTHESE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_type_synthese">DOC : Réglages synthèses &amp; bilans &rarr; Format de synthèse adapté suivant chaque référentiel</a></span></div>

<hr />

<?php
$tab_matiere = array();
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  // On récupère la liste des référentiels des matières auxquelles le professeur est rattaché, et s'il en est coordonnateur
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_matieres_niveaux_referentiels_professeur($_SESSION['USER_ID']);
  if(empty($DB_TAB))
  {
    echo'<hr />'.NL;
    echo'<ul class="puce">'.NL;
    echo  '<li><span class="danger">Aucun référentiel présent parmi les matières qui vous sont rattachées !</span></li>'.NL;
    echo  '<li><span class="astuce">Commencer par <a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer ou importer un référentiel</a>.</span></li>'.NL;
    echo'</ul>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  // On récupère les données
  foreach($DB_TAB as $DB_ROW)
  {
    if( !isset($tab_matiere[$DB_ROW['matiere_id']]) && Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_MODE_SYNTHESE'] , $DB_ROW['jointure_coord'] /*matiere_coord_or_groupe_pp_connu*/ ) )
    {
      $tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_id'];
    }
  }
  if(empty($tab_matiere))
  {
    echo'<ul class="puce">'.NL;
    echo  '<li><span class="danger">Aucun référentiel présent parmi les matières que vous avez le droit de gérer !</span></li>'.NL;
    echo'</ul>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}

$listing_matiere_id = implode(',',$tab_matiere);
$DB_TAB = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels($listing_matiere_id);
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun référentiel enregistré !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$tab_sousmenu = array();
$tab_sousform = array();
$tab_choix = array( 'domaine'=>'synthèse par domaine' , 'theme'=>'synthèse par thème' , 'sans'=>'pas de synthèse' );
// Récupérer la liste des domaines de chaque référentiel
$tab_domaines = array();
$DB_TAB_DOMAINES = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels_domaines();
foreach($DB_TAB_DOMAINES as $DB_ROW)
{
  $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
  $tab_domaines[$ids][] = '<li class="li_n1">'.html($DB_ROW['domaine_nom']).'</li>';
}
// Récupérer la liste des thèmes de chaque référentiel
$tab_themes = array();
$DB_TAB_THEMES = DB_STRUCTURE_REFERENTIEL::DB_recuperer_referentiels_themes();
foreach($DB_TAB_THEMES as $DB_ROW)
{
  $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
  $tab_themes[$ids][] = '<li class="li_n2">'.html($DB_ROW['theme_nom']).'</li>';
}
// Passer en revue les référentiels
$memo_matiere_id = 0;
foreach($DB_TAB as $DB_ROW)
{
  if($memo_matiere_id!=$DB_ROW['matiere_id'])
  {
    if(!$memo_matiere_id)
    {
      $tab_sousform[$memo_matiere_id][] = '<fieldset id="fieldset_0">';
      $tab_sousform[$memo_matiere_id][] = '<div class="astuce">Choisir une matière ci-dessus.</div>';
    }
    $memo_matiere_id = $DB_ROW['matiere_id'];
    $tab_sousmenu[$memo_matiere_id] = '<a href="#fieldset_'.$memo_matiere_id.'">'.html($DB_ROW['matiere_nom']).'</a>';
    $tab_sousform[$memo_matiere_id][] = '</fieldset>';
    $tab_sousform[$memo_matiere_id][] = '<fieldset id="fieldset_'.$memo_matiere_id.'" class="hide">';
  }
  $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
  // Titre + boutons radio + bouton validation
  $tab_sousform[$memo_matiere_id][] = '<h2>'.html($DB_ROW['matiere_nom'].' - '.$DB_ROW['niveau_nom']).'</h2>';
  $puce = '<ul class="puce"><li>Traitement :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  foreach($tab_choix as $option_valeur => $option_texte)
  {
    $checked = ($DB_ROW['referentiel_mode_synthese']==$option_valeur) ? ' checked' : '' ;
    $puce .= '<label for="f_'.$ids.'_'.$option_valeur.'"><input type="radio" id="f_'.$ids.'_'.$option_valeur.'" name="f_'.$ids.'" value="'.$option_valeur.'"'.$checked.' /> '.$option_texte.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  }
  $puce .= ($DB_ROW['referentiel_mode_synthese']=='inconnu') ? '<button id="bouton_'.$ids.'" type="button" class="valider" disabled>Valider.</button><label id="label_'.$ids.'" class="erreur">Choix manquant !</label>' : '<button id="bouton_'.$ids.'" type="button" class="valider">Valider.</button><label id="label_'.$ids.'" class="valide">ok</label>' ;
  $puce .= '</li></ul>';
  $tab_sousform[$memo_matiere_id][] = $puce;
  // Div avec ses domaines
  $class = ($DB_ROW['referentiel_mode_synthese']=='domaine') ? '' : ' class="hide"' ;
  $tab_sousform[$memo_matiere_id][] = '<div id="domaine_'.$ids.'"'.$class.'>';
  if(isset($tab_domaines[$ids]))
  {
    $tab_sousform[$memo_matiere_id][] = '<ul class="ul_n1">'.implode('',$tab_domaines[$ids]).'</ul>';
  }
  $tab_sousform[$memo_matiere_id][] = '</div>';
  // Div avec ses thèmes
  $class = ($DB_ROW['referentiel_mode_synthese']=='theme') ? '' : ' class="hide"' ;
  $tab_sousform[$memo_matiere_id][] = '<div id="theme_'.$ids.'"'.$class.'>';
  if(isset($tab_themes[$ids]))
  {
    $tab_sousform[$memo_matiere_id][] = '<ul class="ul_n1">'.implode('',$tab_themes[$ids]).'</ul>';
  }
  $tab_sousform[$memo_matiere_id][] = '</div>';
  $tab_sousform[$memo_matiere_id][] = '<hr />';
}
$tab_sousform[$memo_matiere_id][] = '</fieldset>';

// affichage
echo'<div id="sousmenu" class="sousmenu">'.NL.implode(NL,$tab_sousmenu).'</div>'.NL;
echo'<hr />'.NL;
echo'<form action="#" method="post" id="form_synthese">'.NL;
foreach($tab_sousform as $tab_sousform_matiere)
{
  echo implode(NL,$tab_sousform_matiere);
}
echo'</form>'.NL;
?>
<p id="force_scroll" />
