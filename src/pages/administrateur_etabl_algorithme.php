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
$TITRE = html(Lang::_("Algorithme de calcul"));

// Javascript
Layout::add( 'js_inline_before' , 'var tab_select = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_valeur = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_seuil = new Array();' );

// Option select : méthode de calcul
$tab_options = array();
$tab_options['geometrique']  = 'Coefficient multiplié par 2 à chaque devoir (sur les 5 dernières saisies maximum).';
$tab_options['arithmetique'] = 'Coefficient augmenté de 1 à chaque devoir (sur les 9 dernières saisies maximum).';
$tab_options['classique']    = 'Moyenne classique non pondérée (comptabiliser autant chaque saisie).';
$tab_options['bestof1']      = 'Utiliser uniquement la meilleure saisie (une seule réussite suffit donc).';
$tab_options['bestof2']      = 'Utiliser seulement les 2 meilleures saisies (dont on effectue la moyenne).';
$tab_options['bestof3']      = 'Utiliser seulement les 3 meilleures saisies (dont on effectue la moyenne).';
$tab_options['frequencemin'] = 'Saisie la plus fréquente (en cas d\'égalité la moins bonne est retenue).';
$tab_options['frequencemax'] = 'Saisie la plus fréquente (en cas d\'égalité la meilleure est retenue).';
$options_methode = '';
foreach($tab_options as $value => $texte)
{
  $selected = ($value==$_SESSION['CALCUL_METHODE']) ? ' selected' : '' ;
  $options_methode .= '<option value="'.$value.'"'.$selected.'>'.$texte.'</option>';
}
Layout::add( 'js_inline_before' , 'tab_select["f_methode"] = "'.$_SESSION['CALCUL_METHODE'].'";' );

// Option select : nb de saisies
$tab_options = array(0,1,2,3,4,5,6,7,8,9,10,15,20,30,40,50);
$options_limite = '';
foreach($tab_options as $value)
{
  if($value>1)
  {
    $texte = 'Prendre en compte uniquement les '.$value.' dernières évaluations.';
  }
  else
  {
    $texte = ($value) ? 'Prendre en compte uniquement la dernière évaluation.' : 'Prendre en compte toutes les évaluations.' ;
  }
  $selected = ($value==$_SESSION['CALCUL_LIMITE']) ? ' selected' : '' ;
  $options_limite .= '<option value="'.$value.'"'.$selected.'>'.$texte.'</option>';
}
Layout::add( 'js_inline_before' , 'tab_select["f_limite"] = "'.$_SESSION['CALCUL_LIMITE'].'";' );

// Option select : éval antérieures
$tab_options = array();
$tab_options['non']    = 'Sans prise en compte des évaluations antérieures.';
$tab_options['oui']    = 'Avec prise en compte des évaluations antérieures.';
$tab_options['annuel'] = 'Prise en compte mais restreinte à l\'année scolaire.';
$options_retroactif = '';
foreach($tab_options as $value => $texte)
{
  $selected = ($value==$_SESSION['CALCUL_RETROACTIF']) ? ' selected' : '' ;
  $options_retroactif .= '<option value="'.$value.'"'.$selected.'>'.$texte.'</option>';
}
Layout::add( 'js_inline_before' , 'tab_select["f_retroactif"] = "'.$_SESSION['CALCUL_RETROACTIF'].'";' );

// codes de notation
$tab_notes = array();
foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
{
  $tab_notes[] = '<label class="tab mini" for="N'.$note_id.'">saisie <img alt="" src="'.$_SESSION['NOTE'][$note_id]['FICHIER'].'" /> :</label><input class="hc" type="number" min="0" max="200" id="N'.$note_id.'" name="N'.$note_id.'" value="'.$_SESSION['NOTE'][$note_id]['VALEUR'].'" />';
  Layout::add( 'js_inline_before' , 'tab_valeur["N'.$note_id.'"] = '.$_SESSION['NOTE'][$note_id]['VALEUR'].';' );
}

// états d'acquisition
$tab_acquis = array();
foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
{
  $tab_acquis[] = '<label class="tab mini" for="vide">état <span class="A'.$acquis_id.'">&nbsp;'.$tab_acquis_info['SIGLE'].'&nbsp;</span> :</label><input class="hc" type="number" min="0" max="100" id="A'.$acquis_id.'min" name="A'.$acquis_id.'min" value="'.$tab_acquis_info['SEUIL_MIN'].'" /> ~ <input class="hc" type="number" min="0" max="100" id="A'.$acquis_id.'max" name="A'.$acquis_id.'max" value="'.$tab_acquis_info['SEUIL_MAX'].'" />';
  Layout::add( 'js_inline_before' , 'tab_seuil["A'.$acquis_id.'min"] = '.$tab_acquis_info['SEUIL_MIN'].';' );
  Layout::add( 'js_inline_before' , 'tab_seuil["A'.$acquis_id.'max"] = '.$tab_acquis_info['SEUIL_MAX'].';' );
}

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=notes_acquis__algorithme_calcul_score">DOC : Algorithme de calcul des scores.</a></span></p>

<hr />

<form action="#" method="post" id="form_input">
  <table>
  <thead>
    <tr><th>
      Valeur d'un code (sur 100)
    </th><th>
      Méthode de calcul par défaut (modifiable pour chaque référentiel)
    </th><th>
      Seuils d'acquisition (de 0 à 100)
    </th></tr>
  </thead>
  <tbody>
    <tr><td>
      <?php echo implode('<br />',$tab_notes); ?>
    </td><td>
      <select id="f_methode" name="f_methode"><?php echo $options_methode ?></select><br />
      <select id="f_limite" name="f_limite"><?php echo $options_limite ?></select><br />
      <select id="f_retroactif" name="f_retroactif"><?php echo $options_retroactif ?></select>
    </td><td>
      <?php echo implode('<br />',$tab_acquis); ?>
    </td></tr>
  </tbody>
  </table>
  <p>
    <input type="hidden" id="f_action" name="f_action" value="calculer" />
    <button id="initialiser_etablissement" type="button" class="retourner">Remettre les valeurs de l'établissement.</button>
    <button id="calculer" type="button" class="actualiser">Simuler avec ces valeurs.</button>
    <button id="enregistrer" type="button" class="valider">Enregistrer ces valeurs.</button>
    <label id="ajax_msg">&nbsp;</label>
  </p>
  <p>Réglages <span class="danger">non rétroactifs pour les référentiels déjà en place</span> et <span class="astuce">modifiables pour chaque référentiel par les coordonnateurs</span> : voir la documentation.</p>
</form>

<hr />

<div id="bilan">
<table class="simulation">
  <thead>
    <tr>
      <th colspan="2">Cas de 1 devoir</th>
      <th></th>
      <th colspan="3">Cas de 2 devoirs</th>
      <th></th>
      <th colspan="4">Cas de 3 devoirs</th>
      <th></th>
      <th colspan="5">Cas de 4 devoirs</th>
    </tr>
    <tr>
      <th>unique</th><th>score</th>
      <th></th>
      <th>ancien</th><th>récent</th><th>score</th>
      <th></th>
      <th>ancien</th><th>médian</th><th>récent</th><th>score</th>
      <th></th>
      <th>très ancien</th><th>ancien</th><th>récent</th><th>très récent</th><th>score</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="17"></td>
    </tr>
  </tbody>
</table>

</div>

