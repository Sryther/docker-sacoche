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
$TITRE = html(Lang::_("Ordre d'affichage des matières"));
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_ordre_matieres">DOC : Réglages synthèses &amp; bilans &rarr; Ordre d'affichage des matières</a></span></div>

<hr />

<form action="#" method="post" id="form_ordonner"><fieldset>
  <?php
  // liste des matières
  $DB_TAB = DB_STRUCTURE_MATIERE::DB_lister_matieres_etablissement( FALSE /*order_by_name*/ , TRUE /*with_siecle*/ );
  if(empty($DB_TAB))
  {
    echo'<p class="danger">Aucune matière enregistrée ou associée à l\'établissement !</p>'.NL; // impossible vu qu'il y a au moins la matière transversale...
  }
  else
  {
    echo'<ul id="sortable_v">'.NL;
    foreach($DB_TAB as $DB_ROW)
    {
      echo'<li id="m_'.$DB_ROW['matiere_id'].'">'.html($DB_ROW['matiere_nom']).'</li>'.NL;
    }
    echo'</ul>'.NL;
    echo'<p><span class="tab"></span><button id="Enregistrer_ordre" type="button" class="valider">Enregistrer cet ordre</button><label id="ajax_msg_ordre">&nbsp;</label></p>'.NL;
  }
  ?>
</fieldset></form>
