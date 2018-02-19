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
$TITRE = html(Lang::_("Blocage des connexions"));

// Initialisation de l'état de l'accès
$blocage_txt = LockAcces::tester_blocage( 'administrateur' , $_SESSION['BASE'] );
list( $blocage_msg , $string_profils ) = explode("\r\n",$blocage_txt) + array(NULL,'ALL') ;
$tab_profils = explode(',',$string_profils);

$label_etat = ($blocage_msg!==NULL) ? '<label class="erreur">Application fermée : '.html($blocage_msg).'</label>' : '<label class="valide">Application accessible.</label>' ;

// Lister les profils de l'établissement
$DB_TAB = array( 0 => array( 'user_profil_sigle'=>'ALL' , 'user_profil_nom_court_pluriel'=>'tous les profils' ) ) + DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_court_pluriel' /*listing_champs*/ , TRUE /*only_actif*/ );

$tab_input = array();
foreach($DB_TAB as $DB_ROW)
{
  $checked = in_array($DB_ROW['user_profil_sigle'],$tab_profils) ? ' checked' : '' ;
  $tab_input[] = '<label for="f_profil_'.$DB_ROW['user_profil_sigle'].'"><input type="checkbox" name="f_profil[]", id="f_profil_'.$DB_ROW['user_profil_sigle'].'" value="'.$DB_ROW['user_profil_sigle'].'"'.$checked.' /> '.$DB_ROW['user_profil_nom_court_pluriel'].'</label>';
}

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__verrouillage">DOC : Verrouillage de l'application</a></span></p>

<hr />

<h2>État de l'accès actuel</h2>

<p id="ajax_acces_actuel"><?php echo $label_etat ?></p>

<hr />

<h2>Demande de modification</h2>

<form action="#" method="post" id="form"><fieldset>
  <label for="f_bloquer"><input type="radio" id="f_bloquer" name="f_action" value="bloquer" /> Bloquer l'application</label><br />
  <span id="span_bloquer" class="hide">
    <label class="tab" for="f_motif">Motif :</label>
      <select id="f_proposition" name="f_proposition">
        <option value="rien" selected>autre motif</option>
        <option value="demenagement">déménagement</option>
      </select>
      <input id="f_motif" name="f_motif" size="50" maxlength="100" type="text" value="" /><br />
    <label class="tab" for="f_profil">Profils :</label><?php echo implode('<br />'.NL.'<span class="tab"></span>',$tab_input) ?><br />
  </span>
  <p><label for="f_debloquer"><input type="radio" id="f_debloquer" name="f_action" value="debloquer" /> Débloquer l'application</label></p>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="parametre">Valider cet état.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

