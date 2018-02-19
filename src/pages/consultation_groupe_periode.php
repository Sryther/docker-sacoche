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
$TITRE = html(Lang::_("Dates des périodes"));
?>

<p class="astuce">
  Les périodes servent à faciliter les recherches, la navigation, la génération de bilans.<br />
  Les évaluations effectuées en dehors des périodes prédéfinies sont comptabilisées comme les autres.
</p>

<?php

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du bilan des affectations des périodes aux classes & groupes ; en plusieurs requêtes pour récupérer les périodes sans classes-groupes et les classes-groupes sans périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_groupe    = array();
$tab_periode   = array();
$tab_jointure  = array();

// Récupérer la liste des classes & groupes, dans l'ordre des niveaux
switch($_SESSION['USER_PROFIL_TYPE'])
{
  case 'directeur'  : $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_et_groupes_avec_niveaux(); break;
  case 'professeur' : $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']); break;
  case 'parent'     : $DB_TAB = DB_STRUCTURE_REGROUPEMENT::DB_lister_classes_parent($_SESSION['USER_ID']); break;
  case 'eleve'      : $DB_TAB = array( 0 => array( 'groupe_id' => $_SESSION['ELEVE_CLASSE_ID'] , 'groupe_nom' => $_SESSION['ELEVE_CLASSE_NOM'] ) );
}
if(!empty($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupe[$DB_ROW['groupe_id']] = '<th>'.html($DB_ROW['groupe_nom']).'</th>';
  }

  // Récupérer la liste des périodes, dans l'ordre choisi par l'admin
  $DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_periodes();
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_periode[$DB_ROW['periode_id']] = '<th>'.html($DB_ROW['periode_nom']).'</th>';
    }

    // Récupérer la liste des jointures
    $listing_groupes_id = implode(',',array_keys($tab_groupe));
    $DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_jointure_groupe_periode($listing_groupes_id);
    $memo_groupe_id = 0;
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $date_affich_debut = To::date_mysql_to_french($DB_ROW['jointure_date_debut']);
        $date_affich_fin   = To::date_mysql_to_french($DB_ROW['jointure_date_fin']);
        $tab_jointure[$DB_ROW['groupe_id']][$DB_ROW['periode_id']] = html($date_affich_debut).' ~ '.html($date_affich_fin);
      }
    }

    // Fabrication du tableau résultant
    foreach($tab_groupe as $groupe_id => $groupe_text)
    {
      foreach($tab_periode as $periode_id => $periode_text)
      {
        $tab_groupe[$groupe_id] .= (isset($tab_jointure[$groupe_id][$periode_id])) ? '<td>'.$tab_jointure[$groupe_id][$periode_id].'</td>' : '<td class="hc">-</td>' ;
      }
    }

    // Affichage du tableau résultant
    echo'<table>'.NL;
    echo  '<thead>'.NL;
    echo    '<tr><td class="nu"></td>'.implode('',$tab_periode).'</tr>'.NL;
    echo  '</thead>'.NL;
    echo  '<tbody>'.NL;
    echo    '<tr>'.implode('</tr>'.NL.'<tr>',$tab_groupe).'</tr>'.NL;
    echo  '</tbody>'.NL;
    echo'</table>'.NL;

  }
  else
  {
    echo'<p><label class="erreur">Aucune période n\'a été configurée par les administrateurs !</label></p>'.NL;
  }
}
else
{
  echo'<p><label class="erreur">Aucune classe ni aucun groupe associé à votre compte !</label></p>'.NL;
}

?>
