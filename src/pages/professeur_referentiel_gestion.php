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
$TITRE = html(Lang::_("Créer / paramétrer les référentiels"));

if(!Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_REFERENTIEL'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_REFERENTIEL'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Pour remplir la cellule avec la méthode de calcul par défaut en cas de création d'un nouveau référentiel
if($_SESSION['CALCUL_LIMITE']==1)  // si une seule saisie prise en compte
{
  $calcul_texte = 'Seule la dernière saisie compte';
}
elseif($_SESSION['CALCUL_METHODE']=='classique')  // si moyenne classique
{
  $calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Moyenne de toutes les saisies' : 'Moyenne des '.$_SESSION['CALCUL_LIMITE'].' dernières saisies';
}
elseif(in_array($_SESSION['CALCUL_METHODE'],array('geometrique','arithmetique')))  // si moyenne geometrique | arithmetique
{
  $seize = (($_SESSION['CALCUL_METHODE']=='geometrique')&&($_SESSION['CALCUL_LIMITE']==5)) ? 1 : 0 ;
  $coefs = ($_SESSION['CALCUL_METHODE']=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$_SESSION['CALCUL_LIMITE']-19) : substr('1/2/4/8/16/',0,2*$_SESSION['CALCUL_LIMITE']-12+$seize) ;
  $calcul_texte = 'Les '.$_SESSION['CALCUL_LIMITE'].' dernières saisies &times;'.$coefs;
}
elseif($_SESSION['CALCUL_METHODE']=='bestof1')  // si meilleure note
{
  $calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Seule la meilleure saisie compte' : 'Meilleure des '.$_SESSION['CALCUL_LIMITE'].' dernières saisies';
}
elseif(in_array($_SESSION['CALCUL_METHODE'],array('bestof2','bestof3')))  // si 2 | 3 meilleures notes
{
  $nb_best = (int)substr($_SESSION['CALCUL_METHODE'],-1);
  $calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Moyenne des '.$nb_best.' meilleures saisies' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$_SESSION['CALCUL_LIMITE'].' dernières';
}
elseif(in_array($_SESSION['CALCUL_METHODE'],array('frequencemin','frequencemax'))) // si note la plus fréquente
{
  $note_si_egalite = (substr($_SESSION['CALCUL_METHODE'],-3)=='min') ? 'malus' : 'bonus' ;
  $calcul_texte = ($_SESSION['CALCUL_LIMITE']==0) ? 'Saisie la plus fréquente, '.$note_si_egalite.' si égalité' : 'Saisie la plus fréquente ('.$note_si_egalite.' si égalité) parmi les '.$_SESSION['CALCUL_LIMITE'].' dernières';
}
    if($_SESSION['CALCUL_RETROACTIF']=='non')    { $calcul_texte .= ' (sur la période).';       }
elseif($_SESSION['CALCUL_RETROACTIF']=='oui')    { $calcul_texte .= ' (rétroactivement).';      }
elseif($_SESSION['CALCUL_RETROACTIF']=='annuel') { $calcul_texte .= ' (de l\'année scolaire).'; }

// Javascript
Layout::add( 'js_inline_before' , 'var calcul_methode          = "'.$_SESSION['CALCUL_METHODE'].'";' );
Layout::add( 'js_inline_before' , 'var calcul_limite           = "'.$_SESSION['CALCUL_LIMITE'].'";' );
Layout::add( 'js_inline_before' , 'var calcul_retroactif       = "'.$_SESSION['CALCUL_RETROACTIF'].'";' );
Layout::add( 'js_inline_before' , 'var calcul_texte            = "'.$calcul_texte.'";' );
Layout::add( 'js_inline_before' , 'var ID_MATIERE_PARTAGEE_MAX = '.ID_MATIERE_PARTAGEE_MAX.';' );
Layout::add( 'js_inline_before' , 'var ID_NIVEAU_PARTAGE_MAX   = '.ID_NIVEAU_PARTAGE_MAX.';' );
Layout::add( 'js_inline_before' , 'var tab_partage_etat      = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_calcul_methode    = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_calcul_limite     = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_calcul_retroactif = new Array();' );
Layout::add( 'js_inline_before' , 'var tab_information       = new Array();' );
?>

<form action="#" method="post" id="form_instance">

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_creer_parametrer">DOC : Créer / paramétrer les référentiels.</a></span></li>
  <li><span class="danger">Supprimer un référentiel efface les résultats associés de tous les élèves !</span></li>
</ul>
<hr />

<div id="div_tableaux">

<?php
// Séparé en plusieurs requêtes sinon on ne s'en sort pas (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
$tab_colonne = array();

$texte_profil = Outil::afficher_profils_droit_specifique($_SESSION['DROIT_GERER_REFERENTIEL'],'br');
// On récupère la liste des matières où le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB_MATIERES = DB_STRUCTURE_PROFESSEUR::DB_lister_matieres_professeur_infos_referentiel($_SESSION['USER_ID']);
if(empty($DB_TAB_MATIERES))
{
  $nb_matieres = 0;
  echo'<p><span class="danger">Vous n\'êtes rattaché à aucune matière de l\'établissement !</span></p>';
}
else
{
  $nb_matieres = count($DB_TAB_MATIERES);
  foreach($DB_TAB_MATIERES as $DB_ROW)
  {
    $tab_matiere[$DB_ROW['matiere_id']] = array(
      'nom'         => html($DB_ROW['matiere_nom']),
      'nb_demandes' => $DB_ROW['matiere_nb_demandes'],
      'coord'       => $DB_ROW['jointure_coord'],
    );
  }
  // On récupère la liste des niveaux utilisés par l'établissement, que l'on conserve pour un formulaire
  $DB_TAB_NIVEAUX = DB_STRUCTURE_COMMUN::DB_OPT_niveaux_etabl();
  if(empty($DB_TAB_NIVEAUX))
  {
    $nb_niveaux = 0;
    echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
  }
  else
  {
    $nb_niveaux = count($DB_TAB_NIVEAUX);
    foreach($DB_TAB_NIVEAUX as $DB_ROW)
    {
      $tab_niveau[$DB_ROW['valeur']] = html($DB_ROW['texte']);
    }
    // On récupère la liste des référentiels par matière et niveau
    $tab_partage = array(
      'oui' => '<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/etat/partage_oui.gif" />',
      'non' => '<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/etat/partage_non.gif" />',
      'bof' => '<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/etat/partage_non.gif" />',
      'hs'  => '<img title="Référentiel dont le partage est sans objet (matière ou niveau spécifique)." alt="" src="./_img/etat/partage_non.gif" />',
    );
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_referentiels_infos_details_matieres_niveaux();
    if(!empty($DB_TAB))
    {
      Layout::add( 'js_inline_before' , '// <![CDATA[' );
      foreach($DB_TAB as $DB_ROW)
      {
        // Définition de $methode_calcul_texte
        if($DB_ROW['referentiel_calcul_limite']==1)  // si une seule saisie prise en compte
        {
          $methode_calcul_texte = 'Seule la dernière saisie compte';
        }
        elseif($DB_ROW['referentiel_calcul_methode']=='classique')  // si moyenne classique
        {
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne de toutes les saisies' : 'Moyenne des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies';
        }
        elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('geometrique','arithmetique')))  // si moyenne geometrique | arithmetique
        {
          $seize = (($DB_ROW['referentiel_calcul_methode']=='geometrique')&&($DB_ROW['referentiel_calcul_limite']==5)) ? 1 : 0 ;
          $coefs = ($DB_ROW['referentiel_calcul_methode']=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$DB_ROW['referentiel_calcul_limite']-19) : substr('1/2/4/8/16/',0,2*$DB_ROW['referentiel_calcul_limite']-12+$seize) ;
          $methode_calcul_texte = 'Les '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies &times;'.$coefs;
        }
        elseif($DB_ROW['referentiel_calcul_methode']=='bestof1')  // si meilleure note
        {
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Seule la meilleure saisie compte' : 'Meilleure des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies';
        }
        elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('bestof2','bestof3')))  // si 2 | 3 meilleures notes
        {
          $nb_best = (int)substr($DB_ROW['referentiel_calcul_methode'],-1);
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne des '.$nb_best.' meilleures saisies' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$DB_ROW['referentiel_calcul_limite'].' dernières';
        }
        elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('frequencemin','frequencemax'))) // si note la plus fréquente
        {
          $note_si_egalite = (substr($DB_ROW['referentiel_calcul_methode'],-3)=='min') ? 'malus' : 'bonus' ;
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Saisie la plus fréquente, '.$note_si_egalite.' si égalité' : 'Saisie la plus fréquente ('.$note_si_egalite.' si égalité) parmi les '.$DB_ROW['referentiel_calcul_limite'].' dernières';
        }
            if($DB_ROW['referentiel_calcul_retroactif']=='non')    { $methode_calcul_texte .= ' (sur la période).';       }
        elseif($DB_ROW['referentiel_calcul_retroactif']=='oui')    { $methode_calcul_texte .= ' (rétroactivement).';      }
        elseif($DB_ROW['referentiel_calcul_retroactif']=='annuel') { $methode_calcul_texte .= ' (de l\'année scolaire).'; }
        $tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td class="hc">'.str_replace('◄DATE►',Html::date_texte($DB_ROW['referentiel_partage_date']),$tab_partage[$DB_ROW['referentiel_partage_etat']]).'</td>'.'<td>'.$methode_calcul_texte.'</td>';
        Layout::add( 'js_inline_before' , '     tab_partage_etat["'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'"] = "'.$DB_ROW['referentiel_partage_etat'].'";' );
        Layout::add( 'js_inline_before' , '   tab_calcul_methode["'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'"] = "'.$DB_ROW['referentiel_calcul_methode'].'";' );
        Layout::add( 'js_inline_before' , '    tab_calcul_limite["'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'"] = "'.$DB_ROW['referentiel_calcul_limite'].'";' );
        Layout::add( 'js_inline_before' , 'tab_calcul_retroactif["'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'"] = "'.$DB_ROW['referentiel_calcul_retroactif'].'";' );
        Layout::add( 'js_inline_before' , '      tab_information["'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'"] = "'.str_replace('"','\"',$DB_ROW['referentiel_information']).'";' );
      }
      Layout::add( 'js_inline_before' , '// ]]>' );
    }
    // Construction du formulaire select du nombre de demandes
    $select_demandes = '<select name="f_eleve_demandes" class="t9">';
    for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
    {
      $texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes' : '1 seule demande' ) : 'aucune demande' ;
      $select_demandes .= '<option value="'.$nb_demandes.'">'.$texte.'</option>';
    }
    $select_demandes .= '</select>';
    $infobulle = ' <img src="./_img/bulle_aide.png" width="16" height="16" alt="" title="Nombre maximal de demandes d\'évaluations simultanées autorisées pour un élève." />';
    $label = '<label>&nbsp;</label>';
    // On construit et affiche les tableaux résultants
    foreach($tab_matiere as $matiere_id => $tab)
    {
      $matiere_nom    = $tab['nom'];
      $matiere_coord  = $tab['coord'];
      $matiere_droit  = Outil::test_user_droit_specifique( $_SESSION['DROIT_GERER_REFERENTIEL'] , $matiere_coord /*matiere_coord_or_groupe_pp_connu*/ );
      $matiere_ajout  = ($matiere_droit) ? '<q class="ajouter" title="Créer un référentiel vierge ou importer un référentiel existant."></q>' : '<q class="ajouter_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q>' ;
      echo'<h2 id="h2_'.$matiere_id.'">'.$matiere_nom.'</h2>'.NL;
      echo'<table id="mat_'.$matiere_id.'" class="vm_nug"><thead>'.NL.'<tr><th>Niveau</th><th>Partage</th><th>Méthode de calcul</th><th class="nu" id="th_'.$matiere_id.'">'.$matiere_ajout.'</th></tr>'.NL.'</thead><tbody>'.NL;
      if(isset($tab_colonne[$matiere_id]))
      {
        foreach($tab_colonne[$matiere_id] as $niveau_id => $referentiel_info)
        {
          $partageable = ( ( $matiere_id <= ID_MATIERE_PARTAGEE_MAX ) && ( $niveau_id <= ID_NIVEAU_PARTAGE_MAX ) ) ? TRUE : FALSE ;
          $ids = 'ids'.'_'.$matiere_id.'_'.$niveau_id;
          if($matiere_droit)
          {
            $partager = ($partageable) ? '<q class="partager" title="Modifier le partage de ce référentiel."></q>' : '<q class="partager_non" title="Le référentiel d\'une matière ou d\'un niveau spécifique à l\'établissement ne peut être partagé."></q>' ;
            $envoyer = (strpos($tab_colonne[$matiere_id][$niveau_id],'partage_oui.gif')) ? '<q class="envoyer" title="Mettre à jour sur le serveur de partage la dernière version de ce référentiel."></q>' : '<q class="envoyer_non" title="Un référentiel non partagé ne peut pas être transmis à la collectivité."></q>' ;
            $colonnes = $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q>'.$partager.$envoyer.'<q class="calculer" title="Modifier le mode de calcul associé à ce référentiel."></q><q class="supprimer" title="Supprimer ce référentiel."></q></td>';
          }
          else
          {
            $colonnes = $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q><q class="partager_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q><q class="envoyer_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q><q class="calculer_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q><q class="supprimer_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q></td>' ;
          }
          echo'<tr><td>'.$tab_niveau[$niveau_id].'</td>'.$colonnes.'</tr>'.NL;
        }
      }
      else
      {
        echo'<tr class="absent"><td class="r hc">---</td><td class="r hc">---</td><td class="r hc">---</td><td class="nu"></td></tr>'.NL;
      }
      $matiere_nombre = str_replace('value="'.$tab['nb_demandes'].'"','value="'.$tab['nb_demandes'].'" selected',$select_demandes) ;
      $matiere_nombre = ( ($matiere_droit) && (isset($tab_colonne[$matiere_id])) ) ? $matiere_nombre : str_replace('<select','<select disabled',$matiere_nombre) ;
      echo'<tr><td colspan="3" class="nu">'.$matiere_nombre.$infobulle.$label.'</td><td class="nu">&nbsp;</td>'.'</tr>'.NL; // En 2 cellules pour résoudre un pb de bordures sous Chrome
      echo'</tbody></table><hr />'.NL;
    }
  }
}
?>

</div>

<div id="choisir_referentiel" class="hide">
  <h2>Créer un référentiel &rarr; <span></span><input id="matiere_id" name="matiere_id" type="hidden" value="" /></h2>
  <p>
  <?php
  if($nb_matieres && $nb_niveaux);
  {
    $select_niveau = HtmlForm::afficher_select($DB_TAB_NIVEAUX , 'f_niveau_create' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ );
    echo'<label class="tab" for="f_niveau_create">Niveau :</label>'.$select_niveau.'<label id="ajax_msg_choisir">&nbsp;</label>'.NL;
  }
  ?>
  </p>
  <p><button id="choisir_initialiser" type="button" value="id_0" class="valider">Démarrer avec un référentiel vierge.</button></p>
  <?php
  if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
  {
    echo'<p><label class="erreur">Pour pouvoir effectuer la recherche d\'un référentiel partagé sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure">DOC : Gestion de l\'identité de l\'établissement</a></span>).</label></p>'.NL;
  }
  else
  {
    echo'<p><button id="choisir_rechercher" type="button" class="rechercher">Rechercher parmi les référentiels partagés sur le serveur communautaire.</button></p>'.NL;
    echo'<p><button id="choisir_importer" type="button" value="id_x" class="valider">Démarrer avec ce référentiel : <b id="reporter"></b></button></p>'.NL;
  }
  ?>
  <p><button id="choisir_annuler" type="button" class="annuler">Annuler la création d'un référentiel.</button></p>
</div>

</form>

<form action="#" method="post" id="form_communautaire" class="hide">

<?php
// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_famille_matiere = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_matieres() , 'f_famille_matiere' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_matieres' /*optgroup*/ );
$select_famille_niveau  = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_niveaux()  , 'f_famille_niveau'  /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'familles_niveaux'  /*optgroup*/ );
?>

<div id="choisir_referentiel_communautaire">
  <h2>Rechercher un référentiel partagé sur le serveur communautaire</h2>
  <fieldset class="p">
    <label class="tab" for="f_famille_matiere">Famille de matières :</label><?php echo $select_famille_matiere ?><label id="ajax_maj_matiere">&nbsp;</label><br />
    <label class="tab" for="f_matiere">Matières :</label><select id="f_matiere" name="f_matiere"><option value="0">Toutes les matières</option></select>
  </fieldset>
  <fieldset class="p">
    <label class="tab" for="f_famille_niveau">Famille de niveaux :</label><?php echo $select_famille_niveau ?><label id="ajax_maj_niveau">&nbsp;</label><br />
    <label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><option value="0">Tous les niveaux</option></select>
  </fieldset>
  <fieldset class="p">
    <label class="tab" for="f_maj_date">Date de mise à jour :</label><input id="box_date" name="box_date" value="1" type="checkbox" checked /> <label for="box_date">sans restriction dans le temps</label><span class="hide">postérieure au <input id="f_maj_date" name="f_maj_date" size="8" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
    <label class="tab" for="f_structure"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Seules les structures partageant au moins un référentiel apparaissent." /> Structure :</label><select id="f_structure" name="f_structure"><option></option></select>
  </fieldset>
  <fieldset class="p">
    <span class="tab"></span><button id="rechercher" type="button" class="rechercher">Lancer / Actualiser la recherche.</button><label id="ajax_msg">&nbsp;</label><br />
    <span class="tab"></span><button id="rechercher_annuler" type="button" class="annuler">Annuler la recherche d'un référentiel.</button>
  </fieldset>
  <hr />
  <div id="lister_referentiel_communautaire" class="hide">
    <h2>Liste des référentiels trouvés</h2>
    <p>
      <span class="danger">Les référentiels partagés ne sont pas des modèles à suivre ! Ils peuvent être améliorables, inadaptés, obsolètes&hellip;</span><br />
      <span class="astuce">Le nombre de reprises ne présage pas de l'intérêt ni de la pertinence d'un référentiel.</span>
    </p>
    <table id="table_action" class="form hsort">
      <thead>
        <tr>
          <th>Matière</th>
          <th>Niveau</th>
          <th>Établissement<br />Localisation</th>
          <th>Établissement<br />Dénomination</th>
          <th>Info</th>
          <th>Date MAJ</th>
          <th>Nombre<br />reprises</th>
          <th class="nu"></th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="nu" colspan="8"></td></tr>
      </tbody>
    </table>
  </div>
</div>

</form>

<?php
// Fabrication du select f_limite
$select_limite = '<option value="0">de toutes les notes</option><option value="1">de la dernière note</option>'.NL;
$tab_options = array(2,3,4,5,6,7,8,9,10,15,20,30,40,50);
foreach($tab_options as $val)
{
  $select_limite .= '<option value="'.$val.'">des '.$val.' dernières notes</option>'.NL;
}
?>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Modifier le partage d'un référentiel | Mettre à jour sur le serveur de partage la dernière version d'un référentiel | Modifier le mode de calcul d'un référentiel | Supprimer un référentiel</h2>
  <p>
    <label class="tab">Référentiel :</label><em id="referentiel_infos"></em><input id="f_matiere_nom" name="f_matiere_nom" type="hidden" value="" /><input id="f_niveau_nom" name="f_niveau_nom" type="hidden" value="" />
  </p>
  <div id="gestion_partager">
    <div id="ligne_partage">
      <label class="tab" for="f_partage">Partage :</label><select id="f_partage" name="f_partage">
        <option value="oui">Partagé sur le serveur communautaire.</option>
        <option value="bof">Partage sans intérêt (pas novateur).</option>
        <option value="non">Non partagé avec la communauté.</option>
        <option value="hs">Sans objet (matière ou niveau spécifique).</option>
      </select>
    </div>
    <div id="ligne_information">
      <label class="tab" for="f_information"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Ce commentaire sera visible dans le resultat d'une recherche de référentiels partagés.<br />Champ facultatif, à utiliser avec parcimonie : complétez-le seulement pour apporter un éclairage particulier." /> Commentaire :</label><input id="f_information" name="f_information" type="text" size="80" maxlength="120" />
    </div>
  </div>
  <div id="gestion_calculer">
    <label class="tab" for="f_methode">Mode de calcul :</label>
      <select id="f_methode" name="f_methode">
        <option value="geometrique">Coefficients &times;2</option>
        <option value="arithmetique">Coefficients +1</option>
        <option value="classique">Moyenne classique</option>
        <option value="bestof1">La meilleure</option>
        <option value="bestof2">Les 2 meilleures</option>
        <option value="bestof3">Les 3 meilleures</option>
        <option value="frequencemin">La plus fréquente (malus si égalité)</option>
        <option value="frequencemax">La plus fréquente (bonus si égalité)</option>
      </select><select id="f_limite" name="f_limite">
        <?php echo $select_limite ?>
      </select><select id="f_retroactif" name="f_retroactif">
        <option value="non">(sur la période).</option>
        <option value="oui">(rétroactivement).</option>
        <option value="annuel">(rétroactif sur l'année scolaire).</option>
      </select>
  </div>
  <div id="gestion_supprimer">
    <ul class="puce"><li>Confirmez-vous la suppression de ce référentiel ?</li></ul>
    <p>
      <span class="danger">Tous les items et les résultats associés des élèves seront perdus !</span><br />
      <span class="astuce">En cas de référentiel partagé, il sera aussi retiré du serveur communautaire.</span>
    </p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_ids" name="f_ids" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<p>&nbsp;</p>