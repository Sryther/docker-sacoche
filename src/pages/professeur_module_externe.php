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
$TITRE = html(Lang::_("Modules externes"));

$module_url = !empty($_SESSION['MODULE']['GENERER_ENONCE']) ? $_SESSION['MODULE']['GENERER_ENONCE'] : '' ;
?>

<p class="travaux">Fonctionnalité expérimentale.</p>

<h2>Générer des énoncés d'évaluation</h2>
<?php /* http://sesaprof.sesamath.net/forum/viewtopic.php?id=2180 */ ?>

<p>
  À partir d'une banque d'exercices pour chaque item, certains mettent en place des outils de génération d'énoncés, souvent à composante aléatoire (<a href="http://revue.sesamath.net/spip.php?article535" target="_blank" rel="noopener noreferrer">exemple</a>).<br />
  Renseigner l'adresse d'un tel module ci-dessous permet de l'appeler depuis une page de gestion des évaluations (icône<q class="module_envoyer" title="Générer un énoncé (module externe)."></q>).<br />
  L'adresse d'un fichier de données est alors transmis en <em>GET</em> avec la variable "json" (http://adresse-du-module?json=...).<br />
  Les données peuvent être récupérées par exemple <a href="#code_recuperer_enonce" class="fancybox">comme ceci en langage <em>PHP</em></a>.<br />
  Le fichier de données, au format <em>JSON</em> donc, contient <a href="#code_generer_enonce" class="fancybox">un tableau selon ce modèle</a> (encodage <em>UTF-8</em>).
</p>

<form action="#" method="post" id="form_module">
  <p>
    <label class="tab" for="f_module_url">Adresse internet :</label><input id="f_module_url" name="f_module_url" size="70" maxlength="255" type="text" value="<?php echo html($module_url) ?>" /><br />
    <span class="tab"></span><input type="hidden" name="f_module_objet" value="generer_enonce" /><button id="bouton_valider_module_url" type="submit" class="parametre">Valider.</button><label id="ajax_msg_module_url">&nbsp;</label>
  </p>
</form>

<pre id="code_generer_enonce" class="hide">
{
  "structure" : {
    "uai" : ...,
    "id"  : ...,
    "nom" : ...,
  },
  "devoir" : {
    "id"       : ...,
    "groupe"   : ...,
    "intitule" : ...,
    "date"     : ...,
  },
  "prof" : {
    "id"     : ...,
    "nom"    : ...,
    "prenom" : ...,
  },
  "item" : {
    id : {
      "id"  : ...,
      "ref" : ...,
      "nom" : ...,
    },
    ...
  },
  "eleve" : {
    id : {
      "id"     : ...,
      "nom"    : ...,
      "prenom" : ...,
    },
    ...
  },
  "panier" : {
    eleve_id : {
      item_id : true,
      ...
    },
    ...
  }
}
</pre>

<pre id="code_recuperer_enonce" class="hide">
&lt;?php
header('Content-Type: text/html; charset=utf-8');
if(isset($_GET['json']))
{
  $file_data = file_get_contents($_GET['json']);
  $array_data = json_decode($file_data,TRUE);
  // affichage pour vérification
  echo'&lt;pre&gt;';
  print_r($array_data);
  echo'&lt;/pre&gt;';
}
?&gt;
</pre>
