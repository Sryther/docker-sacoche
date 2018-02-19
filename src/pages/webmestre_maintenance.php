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
$TITRE = "Maintenance &amp; Mise à jour"; // Pas de traduction car pas de choix de langue pour ce profil.

$titre_verifi_dossiers_additionnels = (HEBERGEUR_INSTALLATION=='multi-structures') ? 'Vérification des dossiers additionnels par établissement' : 'Vérification des dossiers additionnels' ;

// Initialisation de l'état de l'accès
$blocage_msg = LockAcces::tester_blocage( 'webmestre' , 0 );
if($blocage_msg!==NULL)
{
  $label_acces = '<label class="erreur">Application fermée : '.html($blocage_msg).'</label>';
}
else
{
  $label_acces = '<label class="valide">Application accessible.</label>';
}

// Pas de bouton maj automatique si LCS ou serveur Sésamath
if(IS_HEBERGEMENT_SESAMATH)
{
  $disabled = ' disabled';
  $label_maj = '<label id="ajax_maj" class="erreur">La mise à jour de SACoche sur le serveur Sésamath doit s\'effectuer en déployant le SVN.</label>';
}
else if(is_file(CHEMIN_FICHIER_WS_LCS))
{
  $disabled = ' disabled';
  $label_maj = '<label id="ajax_maj" class="erreur">La mise à jour du module LCS-SACoche doit s\'effectuer via le LCS.</label>';
}
else
{
  $disabled = '';
  $label_maj = '<label id="ajax_maj">&nbsp;</label>';
}

?>

<p>
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__maj">DOC : Mise à jour de l'application.</a></span><br />
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__verrouillage">DOC : Verrouillage de l'application.</a></span>
</p>

<hr />

<h2>Version de SACoche</h2>

<ul class="puce">
  <li>Version actuellement installée : <label id="ajax_version_installee"><?php echo VERSION_PROG ?></label></li>
  <li>Dernière version disponible : <span id="ajax_version_disponible" class="astuce"><?php echo Outil::recuperer_numero_derniere_version() ?></span></li>
</ul>

<hr />

<h2>Mise à jour automatique des fichiers</h2>

<form action="#" method="post" id="form_maj"><fieldset>
  <span class="tab"></span><button id="bouton_maj" type="button" class="parametre"<?php echo $disabled ?>>Lancer la mise à jour automatique.</button><?php echo $label_maj ?>
</fieldset></form>

<hr />

<h2>Vérification des fichiers de l'application en place</h2>

<form action="#" method="post" id="form_verif_file_appli"><fieldset>
  <span class="tab"></span><button id="bouton_verif_file_appli" type="button" class="parametre">Lancer la vérification.</button><label id="ajax_verif_file_appli">&nbsp;</label>
</fieldset></form>

<hr />

<h2><?php echo $titre_verifi_dossiers_additionnels ?></h2>

<form action="#" method="post" id="form_verif_dir_etabl"><fieldset>
  <span class="tab"></span><button id="bouton_verif_dir_etabl" type="button" class="parametre">Lancer la vérification.</button><label id="ajax_verif_dir_etabl">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Verrouillage de l'application</h2>

<form action="#" method="post" id="form"><fieldset>
  <label class="tab">État actuel :</label><span id="ajax_acces_actuel"><?php echo $label_acces ?></span><br />
  <label class="tab">Action :</label><label for="f_bloquer"><input type="radio" id="f_bloquer" name="f_action" value="bloquer" /> Bloquer l'application</label>&nbsp;&nbsp;&nbsp;<label for="f_debloquer"><input type="radio" id="f_debloquer" name="f_action" value="debloquer" /> Débloquer l'application</label><br />
  <div id="span_motif" class="hide"><label class="tab" for="f_motif">Motif :</label><select id="f_proposition" name="f_proposition"><option value="rien">autre motif</option><option value="mise-a-jour" selected>mise à jour</option><option value="maintenance">maintenance</option><option value="demenagement">déménagement</option></select> <input id="f_motif" name="f_motif" size="50" maxlength="100" type="text" value="Mise à jour des fichiers en cours." /></div>
  <span class="tab"></span><button id="bouton_verrouillage" type="submit" class="parametre">Valider cet état.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<?php if(HEBERGEUR_INSTALLATION=='multi-structures'): /* * * * * * MULTI-STRUCTURES DEBUT * * * * * */ ?>

<hr />

<h2>Forcer la mise à jour des bases des établissements</h2>

<form action="#" method="post" id="form_maj_bases_etabl"><fieldset>
  <p class="astuce">
    Les bases de chaque établissement sont mises à jour automatiquement à la connexion du premier utilisateur suivant une mise à jour logicielle.
  </p>
  <p class="danger">
    Il est donc inutile de lancer cette procédure, relativement lourde, à chaque mise à jour logicielle !
  </p>
  Forcer la mise à jour de toutes les bases peut être utile dans les cas suivants :
  <ul class="puce">
    <li>mettre à jour des bases d'établissements inutilisées, afin d'éviter un saut de version ultérieur trop important (potentiellement risqué)</li>
    <li>faire passer une mise à jour majeure à un moment ou quasiment personne n'est connecté</li>
  </ul>
  <p>
    <span class="tab"></span><button id="bouton_maj_bases_etabl" type="button" class="parametre">Forcer la maj des bases établissements.</button><label id="ajax_maj_bases_etabl">&nbsp;</label>
  </p>
</fieldset></form>

<hr />

<h2>Forcer la suppression des fichiers temporaires obsolètes</h2>

<form action="#" method="post" id="form_clean_file_temp"><fieldset>
  <p class="astuce">
    Les fichiers temporaires obsolètes de chaque établissement sont supprimés lors de la connexion d'un utilisateur.<br />
    Forcer leur suppression ne sert qu'à faire le ménage pour les établissements sans connexion prolongée.
  </p>
  <p class="danger">
    Il est donc inutile de lancer cette procédure régulièrement, une fois par an suffit !
  </p>
  <p>
    <span class="tab"></span><button id="bouton_clean_file_temp" type="button" class="parametre">Forcer cette suppression.</button><label id="ajax_clean_file_temp">&nbsp;</label>
  </p>
</fieldset></form>

<?php endif /* * * * * * MULTI-STRUCTURES FIN * * * * * */ ?>

<hr />
