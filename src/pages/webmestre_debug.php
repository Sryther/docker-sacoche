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
$TITRE = "Débogueur"; // Pas de traduction car pas de choix de langue pour ce profil.
?>

<?php
// Activation du débogueur
$lignes_debug = '';
$tab_debug = array(
  'PHP'     => 'afficher toutes les erreurs PHP <span class="now">déconseillé en production</span>',
  'PHPCAS'  => 'enregistrer les dialogues avec le serveur CAS <span class="now">le fichier peut vite gonfler &rarr; décocher une fois les tests terminés</span>',
  'SQL'     => 'console FirePHP &rarr; résultats des requêtes SQL <span class="now">double le besoin en mémoire &rarr; risqué pour les générations de bilans</span>',
  'SESSION' => 'console FirePHP &rarr; valeurs de $_SESSION <span class="now">diminue la sécurité en rendant accessible certaines informations</span>',
  'POST'    => 'console FirePHP &rarr; valeurs de $_POST',
  'GET'     => 'console FirePHP &rarr; valeurs de $_GET',
  'FILES'   => 'console FirePHP &rarr; valeurs de $_FILES',
  'COOKIE'  => 'console FirePHP &rarr; valeurs de $_COOKIE <span class="now">diminue la sécurité en rendant accessible certaines informations</span>',
  'SERVER'  => 'console FirePHP &rarr; valeurs de $_SERVER <span class="now">diminue la sécurité en rendant accessible certaines informations</span>',
  'CONST'   => 'console FirePHP &rarr; valeurs des constantes PHP <span class="now">diminue la sécurité en rendant accessible certaines informations</span>',
);
foreach($tab_debug as $debug_mode => $debug_texte)
{
  $checked = constant('DEBUG_'.$debug_mode) ? ' checked' : '' ;
  $lignes_debug .= '<label class="tab" for="f_debug_'.$debug_mode.'">'.$debug_mode.'</label><label for="f_debug_'.$debug_mode.'"><input type="checkbox" id="f_debug_'.$debug_mode.'" name="f_debug_'.$debug_mode.'" value="1"'.$checked.' /> '.$debug_texte.'</label><br />'.NL;
}
// Fichiers de logs phpCAS
$tab_fichiers = array();
$alerte_dossier_invalide = is_dir(PHPCAS_LOGS_CHEMIN) ? '' : '<p class="danger">Le dossier renseigné n\'existe plus ; veuillez le modifier !</p>' ;
if(!$alerte_dossier_invalide)
{
  $tab_files = FileSystem::lister_contenu_dossier(PHPCAS_LOGS_CHEMIN);
  foreach($tab_files as $file)
  {
    if(substr($file,0,9)=='debugcas_')
    {
      if(HEBERGEUR_INSTALLATION=='mono-structure')
      {
        $etabl = 'pour l\'établissement';
        $id_etabl = 0;
      }
      else
      {
        $tab = explode('_',$file);
        $id_etabl = $tab[1];
        $etabl = 'pour la base n°'.$id_etabl;
      }
      $tab_fichiers[$id_etabl] = '<li id="'.html(substr($file,0,-4)).'">Logs présents '.$etabl.', le fichier pesant '.FileSystem::afficher_fichier_taille(PHPCAS_LOGS_CHEMIN.$file).'<q class="voir" title="Récupérer ce fichier."></q><q class="supprimer" title="Supprimer ce fichier."></q></li>'.NL;
    }
    ksort($tab_fichiers);
  }
}
$listing_fichiers = count($tab_fichiers) ? implode('',$tab_fichiers) : '<li>Pas de fichier trouvé.</li>'.NL ;
?>

<h2>Paramétrage du débogueur</h2>

<form action="#" method="post" id="form_debug"><fieldset>
  <p class="danger">Excepté les logs <em>phpCAS</em>, ces fonctionnalités devraient être réservées à un environnement de développement.</p>
  <p class="astuce">Les réglages s'appliquent à tous les utilisateurs ; à n'activer qu'en connaissance de cause.</p>
  <?php echo $lignes_debug; ?>
  <span class="tab"></span><button id="bouton_debug" type="button" class="parametre">Enregistrer.</button><label id="ajax_debug">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Chemin des logs phpCAS</h2>
<form action="#" method="post" id="form_phpCAS"><fieldset>
  <p class="astuce">Si des fichiers de logs phpCAS existent déjà, ils ne seront pas déplacés : penser à les supprimer avant.</p>
  <?php echo $alerte_dossier_invalide ?>
  <label class="tab" for="f_chemin_logs">Chemin</label><input type="text" size="100" id="f_chemin_logs" name="f_chemin_logs" value="<?php echo PHPCAS_LOGS_CHEMIN ?>" /><br />
  <label class="tab" for="f_etabl_id_listing">Établissements <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Si installation de type mono-structure, alors champ sans objet.<br />Sinon, identifiants à prendre dans la page de gestion des établissements (1e colonne).<br />Valeurs à séparer, faire précéder et terminer par des virgules.<br />Laisser le champ vide active les logs pour tous les établissements (déconseillé)." /></label><input type="text" size="100" id="f_etabl_id_listing" name="f_etabl_id_listing" value="<?php echo PHPCAS_LOGS_ETABL_LISTING ?>" /><br />
  <span class="tab"></span><button id="bouton_phpCAS" type="button" class="parametre">Enregistrer.</button><label id="ajax_phpCAS">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Fichiers de logs phpCAS</h2>
<ul id="fichiers_logs" class="puce">
  <?php echo $listing_fichiers; ?>
</ul>

<hr />

<div id="bilan">
</div>
