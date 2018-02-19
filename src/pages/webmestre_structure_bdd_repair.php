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

require(CHEMIN_DOSSIER_INCLUDE.'fonction_dump.php');
?>

En cas de crash serveur, coupure d'électricité, etc., il peut arriver qu'une base de données soit physiquement corrompue.<br />
Cet outil <a target="_blank" rel="noopener noreferrer" href="http://dev.mysql.com/doc/refman/5.0/fr/check-table.html">analyse</a> et <a target="_blank" rel="noopener noreferrer" href="http://dev.mysql.com/doc/refman/5.0/fr/repair-table.html">répare</a> si besoin, <a target="_blank" rel="noopener noreferrer" href="http://dev.mysql.com/doc/refman/5.0/fr/repair.html">dans la mesure du possible</a>, la ou les bases de données des établissements.

<hr />

<?php if(HEBERGEUR_INSTALLATION=='mono-structure'): /* * * * * * MONO-STRUCTURE DEBUT * * * * * */ ?>

<?php
$TITRE = "Analyser / Réparer la base"; // Pas de traduction car pas de choix de langue pour ce profil.
list( $niveau_alerte , $messages ) = analyser_et_reparer_tables_base_etablissement();
$tab_label = array(
  0 => array( 'class'=>'valide' , 'texte'=>'Aucune anomalie détectée.'                           ) ,
  1 => array( 'class'=>'alerte' , 'texte'=>'Anomalie(s) détectée(s) mais réparée(s).'            ) ,
  2 => array( 'class'=>'erreur' , 'texte'=>'Anomalie(s) détectée(s) et réparation(s) en échec !' ) ,
);
echo'<label class="'.$tab_label[$niveau_alerte]['class'].'">'.$tab_label[$niveau_alerte]['texte'].'</label>'.NL;
echo'<p>'.$messages.'</p>'.NL;
?>

<?php endif /* * * * * * MONO-STRUCTURE FIN * * * * * */ ?>

<?php if(HEBERGEUR_INSTALLATION=='multi-structures'): /* * * * * * MULTI-STRUCTURES DEBUT * * * * * */ ?>

<?php
$TITRE = "Analyser / Réparer les bases"; // Pas de traduction car pas de choix de langue pour ce profil.
$select_structure = HtmlForm::afficher_select( DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , 'f_base' /*select_nom*/ , FALSE /*option_first*/ , FALSE , 'zones_geo' /*optgroup*/ , TRUE /*multiple*/ );
?>

<form action="#" method="post" id="form_repair"><fieldset>
  <label class="tab" for="f_base">Structure(s) :</label><span id="f_base" class="select_multiple"><?php echo $select_structure ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span><br />
  <span class="tab"></span><input type="hidden" id="f_listing_id" name="f_listing_id" value="" /><button id="bouton_valider" type="button" class="nettoyer">Analyser / Réparer.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<div id="ajax_info" class="hide">
  <h2>Opérations en cours</h2>
  <label id="ajax_msg1"></label>
  <ul class="puce"><li id="ajax_msg2"></li></ul>
  <span id="ajax_num" class="hide"></span>
  <span id="ajax_max" class="hide"></span>
</div>

<?php endif /* * * * * * MULTI-STRUCTURES FIN * * * * * */ ?>
