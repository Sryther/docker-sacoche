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
$TITRE = html(Lang::_("Format des identifiants de connexion"));

// Options du formulaire select
$options = '';
for($mdp_length=4 ; $mdp_length<9 ; $mdp_length++)
{
  $options .= '<option value="'.$mdp_length.'">'.$mdp_length.' caractères</option>';
}

// Lister les profils de l'établissement
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_court_pluriel,user_profil_nom_long_pluriel,user_profil_login_modele,user_profil_mdp_longueur_mini,user_profil_mdp_date_naissance' /*listing_champs*/ , TRUE /*only_actif*/ );

?>
<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_format_logins">DOC : Format des identifiants</a></span></div>

<hr />

<h2>Appliquer à tous les profils</h2>

<form action="#" method="post">
  <p>
    <label class="tab">Identifiants :</label>Modèle du nom d'utilisateur <input type="text" id="f_login_ALL" name="f_login_ALL" value="ppp.nnnnnnnn" size="<?php echo LOGIN_LONGUEUR_MAX ?>" maxlength="<?php echo LOGIN_LONGUEUR_MAX ?>" /><br />
    <span class="tab"></span>Longueur minimale du mot de passe <select id="f_mdp_ALL" name="f_mdp_ALL"><?php echo str_replace('value="6"','value="6" selected',$options) ?></select><br />
    <span class="tab"></span><button id="bouton_valider_ALL" type="button" class="parametre">Valider.</button><label id="ajax_msg_ALL">&nbsp;</label>
  </p>
</form>

<hr />

<h2>Affiner selon les profils</h2>

<?php
foreach($DB_TAB as $DB_ROW)
{
  echo'<form action="#" method="post">'.NL;
  echo'<p>'.NL;
  echo  '<label class="tab">'.$DB_ROW['user_profil_nom_court_pluriel'].' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.$DB_ROW['user_profil_nom_long_pluriel'].'" /> :</label>Modèle du nom d\'utilisateur <input type="text" id="f_login_'.$DB_ROW['user_profil_sigle'].'" name="f_login_'.$DB_ROW['user_profil_sigle'].'" value="'.$DB_ROW['user_profil_login_modele'].'" size="'.LOGIN_LONGUEUR_MAX.'" maxlength="'.LOGIN_LONGUEUR_MAX.'" /><br />'.NL;
  echo  '<span class="tab"></span>Longueur minimale du mot de passe <select id="f_mdp_'.$DB_ROW['user_profil_sigle'].'" name="f_mdp_'.$DB_ROW['user_profil_sigle'].'">'.str_replace('value="'.$DB_ROW['user_profil_mdp_longueur_mini'].'"','value="'.$DB_ROW['user_profil_mdp_longueur_mini'].'" selected',$options).'</select><br />'.NL;
  if($DB_ROW['user_profil_sigle']=='ELV')
  {
    $checked = ($DB_ROW['user_profil_mdp_date_naissance']) ? ' checked' : '' ;
    echo  '<span class="tab"></span><label for="f_birth_'.$DB_ROW['user_profil_sigle'].'"><input type="checkbox" id="f_birth_'.$DB_ROW['user_profil_sigle'].'" name="f_birth_'.$DB_ROW['user_profil_sigle'].'"'.$checked.'> Prendre la date de naissance comme mot de passe (format JJMMAAAA).</label><br />'.NL;
  }
  echo  '<span class="tab"></span><button id="bouton_valider_'.$DB_ROW['user_profil_sigle'].'" type="button" class="parametre">Valider.</button><label id="ajax_msg_'.$DB_ROW['user_profil_sigle'].'">&nbsp;</label>'.NL;
  echo'</p>'.NL;
  echo'</form>'.NL;
}
?>

<hr />
