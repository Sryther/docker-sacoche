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
$TITRE = html(Lang::_("Archives des bilans officiels"));

// Réception d'id transmis via un lien par courriel.
$auto_voir_archive_id = isset($_GET['archive_id']) ? Clean::entier($_GET['archive_id']) : 'false' ;

$tab_types = array
(
  'livret'   => array( 'droit'=>'OFFICIEL_LIVRET'   , 'titre'=>'Livret Scolaire'  ) ,
  'bulletin' => array( 'droit'=>'OFFICIEL_BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
  'releve'   => array( 'droit'=>'OFFICIEL_RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
);

$droit_voir_archives_pdf = FALSE;
foreach($tab_types as $BILAN_TYPE => $tab)
{
  $droit_voir_archives_pdf = $droit_voir_archives_pdf || Outil::test_user_droit_specifique($_SESSION['DROIT_'.$tab['droit'].'_VOIR_ARCHIVE']) ;
}

if(!$droit_voir_archives_pdf)
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<p class="astuce">Profils autorisés (par les administrateurs) :</p>'.NL;
  foreach($tab_types as $BILAN_TYPE => $tab)
  {
    $titre = ($BILAN_TYPE!='palier1') ? $tab['titre'] : 'Maîtrise du socle' ;
    echo'<h3>'.$titre.'</h3>'.NL;
    echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_'.$tab['droit'].'_VOIR_ARCHIVE'],'li');
  }
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// identifiants élèves concernés
$tab_eleve_id = array();
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_id[] = $_SESSION['USER_ID'];
}
else
{
  if(!$_SESSION['NB_ENFANTS'])
  {
    echo'<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  foreach($_SESSION['OPT_PARENT_ENFANTS'] as $tab)
  {
    $tab_eleve_id[] = $tab['valeur'];
  }
}

// marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
$_SESSION['tmp_droit_voir_archive'] = array();

// lister les bilans officiels archivés
$tab_tr = array();
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_archive_sans_infos( implode(',',$tab_eleve_id) );
foreach($DB_TAB as $DB_ROW)
{
  $key_type = ($DB_ROW['archive_type']=='sacoche') ? $DB_ROW['archive_ref'] : 'livret' ;
  if(Outil::test_user_droit_specifique($_SESSION['DROIT_'.$tab_types[$key_type]['droit'].'_VOIR_ARCHIVE']))
  {
    $objet = ($DB_ROW['archive_type']=='sacoche') ? $tab_types[$DB_ROW['archive_ref']]['titre'] : $tab_types[$DB_ROW['archive_type']]['titre'].' '.$DB_ROW['archive_ref'] ;
    $class_tr = is_null($DB_ROW['archive_date_consultation_'.$_SESSION['USER_PROFIL_TYPE']])  ? ' class="new"' : '' ;
    $class_td = is_null($DB_ROW['archive_date_consultation_'.$_SESSION['USER_PROFIL_TYPE']])  ? ' class="b"'   : '' ;
    $clef = $DB_ROW['officiel_archive_id'];
    $_SESSION['tmp_droit_voir_archive'][$clef] = TRUE; // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et a donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
    $tab_tr[] = '<tr'.$class_tr.'><td>'.html($DB_ROW['annee_scolaire']).'</td><td>'.html($DB_ROW['periode_nom']).'</td><td>'.html($DB_ROW['structure_uai'].' - '.$DB_ROW['structure_denomination']).'</td><td>'.$objet.'</td><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</td><td'.$class_td.'><a id="archive_'.$clef.'" href="acces_archive.php?id='.$clef.'" target="_blank" rel="noopener noreferrer">accès au document</a></td></tr>';
  }
}

// Javascript
Layout::add( 'js_inline_before' , 'var auto_voir_archive_id = '.$auto_voir_archive_id.';' );
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__archives">DOC : Archives consultables.</a></span></li>
</ul>

<hr />

<p>Voici, au format numérique <em>pdf</em>, les bilans officiels disponibles.</p>
<p class="astuce">Cliquer sur un lien atteste que vous avez pris connaissance du document correspondant.</p>

<hr />

<table id="statistiques" class="form">
  <thead>
    <tr>
      <th>Année scolaire</th>
      <th>Période</th>
      <th>Établissement</th>
      <th>Objet</th>
      <th>Élève</th>
      <th>Lien</th>
    </tr>
  </thead>
  <tbody>
    <?php echo count($tab_tr) ? implode('',$tab_tr) : '<tr class="vide"><td colspan="6"><label class="alerte">Aucun document trouvé vous concernant.</label></td></tr>' ; ?>
  </tbody>
</table>

