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
$TITRE = html(Lang::_("Bascule entre comptes"));

// Protection contre les attaques par force brute (laissé même pour cette page requiérant une authentification car la réponse en cas d'erreur de mdp y fait référence)
$_SESSION['FORCEBRUTE'][$PAGE] = array(
  'TIME'  => $_SERVER['REQUEST_TIME'] ,
  'DELAI' => 2, // en secondes, est ensuite incrémenté en cas d'erreur
);

// Javascript
Layout::add( 'js_inline_before' , 'var    LOGIN_LONGUEUR_MAX = '.   LOGIN_LONGUEUR_MAX.';' );
Layout::add( 'js_inline_before' , 'var PASSWORD_LONGUEUR_MAX = '.PASSWORD_LONGUEUR_MAX.';' );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__comptes_multiples">DOC : Bascule entre comptes</a></span></p>

<hr />

<p span class="astuce">Consulter la documentation (lien ci-dessus) pour comprendre les enjeux de cette fonctionnalité !</p>

<table id="table_action" class="form">
  <thead>
    <tr>
      <th>Identité</th>
      <th>Profil</th>
      <th class="nu"><q class="ajouter" title="Ajouter une liaison vers un autre compte."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Par sécurité et pour actualiser une éventuelle liaison (dé)faite depuis un autre compte, on ne stocke en session que l'identifiant de la clef des associations
    // La méthode appelée ci-dessous effectue de multiples vérifications complémentaires
    list( $_SESSION['USER_SWITCH_ID'] , $user_liste ) = DB_STRUCTURE_SWITCH::DB_recuperer_et_verifier_listing_comptes_associes( $_SESSION['USER_ID'] , $_SESSION['USER_SWITCH_ID'] );
    if($_SESSION['USER_SWITCH_ID'])
    {
      $DB_TAB = DB_STRUCTURE_SWITCH::DB_recuperer_informations_comptes_associes($user_liste);
    }
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $balise_avant = ($DB_ROW['user_id']!=$_SESSION['USER_ID']) ? '<a href="#id_'.$DB_ROW['user_id'].'" title="Basculer vers ce compte.">' : '<span class="notnow" title="Compte actuel.">' ;
        $balise_apres = ($DB_ROW['user_id']!=$_SESSION['USER_ID']) ? '</a>' : '</span>' ;
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['user_id'].'">';
        echo  '<td>'.$balise_avant.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).$balise_apres.'</td>';
        echo  '<td>'.$balise_avant.$DB_ROW['user_profil_nom_court_singulier'].$balise_apres.'</td>';
        echo  '<td class="nu">';
        echo    ($DB_ROW['user_id']!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Supprimer la liaison vers ce compte."></q>' : '<q class="supprimer_non" title="Sans objet (compte actuel)."></q>' ;
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="2">Compte actuellement relié à aucun autre.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Supprimer une liaison</h2>
  <div id="gestion_ajouter">
    <p span class="astuce">Saisir les identifiants <em>SACoche</em> du compte concerné.</p>
    <p>
      <label class="tab" for="f_login">Nom d'utilisateur :</label><input id="f_login" name="f_login" size="40" maxlength="<?php echo LOGIN_LONGUEUR_MAX ?>" type="text" value="" tabindex="2" autocomplete="off" /><br />
      <label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="40" maxlength="<?php echo PASSWORD_LONGUEUR_MAX ?>" type="password" value="" tabindex="3" autocomplete="off" />
    </p>
  </div>
  <div id="gestion_supprimer">
    <p>Confirmez-vous la suppression de la liaison vers &laquo;&nbsp;<b id="gestion_delete_liaison"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_user_id" name="f_user_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
