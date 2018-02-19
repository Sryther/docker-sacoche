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
$TITRE = "Fichiers déposés"; // Pas de traduction car pas de choix de langue pour ce profil.

$tab_select_taille_max = array(
   100=>'100 Ko / audio 25 s',
   200=>'200 Ko / audio 50 s',
   500=>'500 Ko / audio 120 s',
  1000=>'1 Mo / audio 120 s',
  2000=>'2 Mo / audio 120 s',
  5000=>'5 Mo / audio 120 s',
);

$select_taille_max = '';
foreach($tab_select_taille_max as $option_value => $option_texte)
{
  $selected = ($option_value==FICHIER_TAILLE_MAX) ? ' selected' : '' ;
  $select_taille_max .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

$tab_select_duree_conservation = array(
   1=>'1 mois',
   3=>'3 mois',
   6=>'6 mois',
   9=>'9 mois',
  12=>'1 an',
  24=>'2 ans',
  36=>'3 ans',
);

$select_duree_conservation = '';
foreach($tab_select_duree_conservation as $option_value => $option_texte)
{
  $selected = ($option_value==FICHIER_DUREE_CONSERVATION) ? ' selected' : '' ;
  $select_duree_conservation .= '<option value="'.$option_value.'"'.$selected.'>'.$option_texte.'</option>';
}

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__fichiers_deposes">DOC : Fichiers déposés.</a></span></p>
<hr />

<form action="#" method="post" id="form_fichiers"><fieldset>
  <label class="tab" for="f_taille_max">Taille maximale :</label><select id="f_taille_max" name="f_taille_max"><?php echo $select_taille_max ?></select>
  <p>
    <span class="astuce">Il faut aussi tenir compte de la configuration du serveur : <b><?php echo InfoServeur::minimum_limitations_upload() ?></b>.</span>
  </p>
  <hr />
  <label class="tab" for="f_duree_conservation">Durée conservation :</label><select id="f_duree_conservation" name="f_duree_conservation"><?php echo $select_duree_conservation ?></select>
  <p>
    <span class="astuce">Une initialisation annuelle des données supprime de toutes façons le référencement des documents concernés.</span>
  </p>
  <hr />
  <p>
    <span class="tab"></span><button id="f_enregistrer" type="submit" class="parametre">Enregistrer ces paramètres.</button><label id="ajax_msg_enregistrer">&nbsp;</label><br />
  </p>
</fieldset></form>
