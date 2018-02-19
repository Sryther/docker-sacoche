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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Classes / Périodicité"));

if( ($_SESSION['USER_PROFIL_TYPE']!='directeur') && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Seuils les administrateurs et les personnels de direction peuvent consulter cette page.</div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Vérifier qu'il y a au moins une classe dans l'établissement
// Vérifier qu'il y a au moins une classe associée au livret, et sinon essayer de faire le boulot automatiquement
$result = DB_STRUCTURE_LIVRET::DB_initialiser_jointures_livret_classes();
if( $result === FALSE )
{
  echo'<p class="danger">Aucune classe enregistrée ! Commencez par peupler <em>SACoche</em> (importer les élèves crée et remplit les classes).</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
if( is_int($result) )
{
  $s = ($result>1) ? 's' : '' ;
  $compte_rendu = ($result) ? '<em>SACoche</em> les a initialisées : '.$result.' association'.$s.' effectuée'.$s.'<br />' : 'Mais <em>SACoche</em> n\'a pas su les initialiser automatiquement.<br />' ;
  echo'<p class="danger">Des associations de classe au livret scolaire n\'était pas enregistrées.<br />'.$compte_rendu.'Vérifiez et ajustez si besoin (voir ci-dessous).</p>'.NL;
}

// Vérifier qu'il y a un chef associé à chaque classe, et sinon essayer de faire le boulot automatiquement
$result = DB_STRUCTURE_LIVRET::DB_initialiser_jointures_classe_chef();
if( is_int($result) )
{
  $s = ($result>1) ? 's' : '' ;
  $compte_rendu = ($result) ? '<em>SACoche</em> les a initialisées : '.$result.' association'.$s.' effectuée'.$s.'<br />' : 'Mais <em>SACoche</em> n\'a pas su les initialiser automatiquement.<br />' ;
  echo'<p class="danger">Des associations de chef / directeur aux classes n\'était pas enregistrées.<br />'.$compte_rendu.'Vérifiez et ajustez si besoin (voir ci-dessous).</p>'.NL;
}

// Fabriquer les options de formulaires
$tab_bad = array( "Bilan de l'acquisition des connaissances et compétences" );
$tab_bon = array( "Bilan des acquisitions" );
$tab_option = array(
  'periode'  => '<option value="">Non concerné</option>' ,
  'cycle'    => '<option value="">Non concerné</option>' ,
  'jointure' => '<option value="2">2 périodes (semestres)</option><option value="3">3 périodes (trimestres)</option><option value="4">4 périodes</option><option value="5">5 périodes</option>' ,
);
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages( FALSE /*with_info_classe*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_option[$DB_ROW['livret_page_periodicite']] .= '<option value="'.$DB_ROW['livret_page_ref'].'">'.html($DB_ROW['livret_page_titre_classe'].' || '.$DB_ROW['livret_page_moment'].' || '.str_replace($tab_bad,$tab_bon,$DB_ROW['livret_page_resume'])).'</option>';
}

// Formulaire SELECT pour le chef d'établissement responsable d'un bilan
$options_chef = '<option value="0"></option>';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'directeur' , 1 /*statut*/ , 'user_id,user_nom,user_prenom' , FALSE /*with_classe*/ );
if(!empty($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $options_chef .= '<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
  }
}
else
{
  $options_chef .= '<option value="0" disabled>Aucun personnel actif de profil "directeur" n\'est enregistré !</option>';
}

// Javascript
Layout::add( 'js_inline_before' , 'var SERVEUR_LSU_PDF = "'.SERVEUR_LSU_PDF.'";' );
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_classes">DOC : Administration du Livret Scolaire &rarr; Affectation des classes</a></span></li>
  <li><span class="astuce">Rappel : la <a href="./index.php?page=administrateur_periode&amp;section=gestion">définition des périodes du Livret Scolaire</a> et leur <a href="./index.php?page=administrateur_periode&amp;section=classe_groupe">jointure aux classes</a> s'effectuent depuis les interfaces dédiées.</span></li>
</ul>

<hr />

<form action="#" method="post" id="form_chefetabl">
  <p class="astuce">
    Pour le <em>Livret Scolaire</em>, il est requis de désigner le chef d'établissement / directeur d'école responsable des bilans.<br />
    Cela peut-être ajusté ci-dessous classe par classe, ou forcé d'un coup pour toutes les classes : <select id="f_chefetabl" name="f_chefetabl"><?php echo $options_chef; ?></select> <button id="bouton_valider_chefetabl" type="button" class="parametre">Forcer le report.</button><label id="ajax_msg_chefetabl">&nbsp;</label>
  </p>
</form>

<hr />

<form action="#" method="post">
<table id="table_action">
  <thead>
    <tr>
      <th>Classe</th>
      <th>Livret Scolaire</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les classes avec leurs jointures au livret
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_classes_avec_jointures_livret();
    // Il y a forcément des résultats retournés car il existe au moins une classe
    foreach($DB_TAB as $classe_id => $DB_TAB_Classe)
    {
      $classe_nom = $DB_TAB_Classe[0]['groupe_nom'];
      $tab_option_classe = $tab_option;
      $tab_image_classe = array( 'periode' => '' , 'cycle' => '' );
      $periode_type = FALSE;
      foreach($DB_TAB_Classe as $DB_ROW)
      {
        if(!is_null($DB_ROW['livret_page_ref']))
        {
          $td_class = 'bv';
          $tab_option_classe[$DB_ROW['periodicite']] = str_replace( 'value="'.$DB_ROW['livret_page_ref'].'"' , 'value="'.$DB_ROW['livret_page_ref'].'" selected' , $tab_option_classe[$DB_ROW['periodicite']] );
          $tab_image_classe[$DB_ROW['periodicite']] = '<a href="'.SERVEUR_LSU_PDF.'livret_'.$DB_ROW['livret_page_ref'].'.pdf" class="fancybox" rel="gallery_'.$classe_id.'" data-titre="'.html($DB_ROW['livret_page_titre_classe'].' || '.$DB_ROW['livret_page_moment'].' || '.str_replace($tab_bad,$tab_bon,$DB_ROW['livret_page_resume'])).'"><span class="livret livret_'.$DB_ROW['livret_page_ref'].'"></span></a>';
          if($DB_ROW['listing_periodes'])
          {
            $periode_type = $DB_ROW['listing_periodes']{0};
            $tab_option_classe['jointure'] = str_replace( 'value="'.$periode_type.'"' , 'value="'.$periode_type.'" selected' , $tab_option_classe['jointure'] );
          }
        }
        else
        {
          $td_class = 'bj';
        }
      }
      // Responsable du bilan d'une classe
      $options_chef_classe = str_replace( 'value="'.$DB_ROW['groupe_chef_id'].'"' , 'value="'.$DB_ROW['groupe_chef_id'].'" selected' , $options_chef );
      // Afficher une ligne du tableau
      $class_jointure = ($periode_type) ? 'show' : 'hide' ;
      echo'<tr>';
      echo  '<td class="'.$td_class.'">'.html($classe_nom).'</td>';
      echo  '<td data-id="'.$classe_id.'">';
      echo    '<label class="tab mini" for="f_periode_'.$classe_id.'">Périodique :</label><select id="f_periode_'.$classe_id.'" name="f_periode">'.$tab_option_classe['periode'].'</select> <select id="f_jointure_'.$classe_id.'" name="f_jointure" class="'.$class_jointure.'">'.$tab_option_classe['jointure'].'</select><br />';
      echo    '<label class="tab mini" for="f_cycle_'.$classe_id.'">Fin de cycle :</label><select id="f_cycle_'.$classe_id.'" name="f_cycle">'.$tab_option_classe['cycle'].'</select><br />';
      echo    '<label class="tab mini" for="f_chef_'.$classe_id.'">Principal(e) :</label><select id="f_chef_'.$classe_id.'" name="f_chef">'.$options_chef_classe.'</select>&nbsp;<span></span>';
      echo  '</td>';
      echo  '<td class="nu">'.implode('',$tab_image_classe).'</td>';
      echo'</tr>'.NL;
    }
    ?>
  </tbody>
</table>
</form>