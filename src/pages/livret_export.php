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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Export LSU"));

if( ($_SESSION['USER_PROFIL_TYPE']!='directeur') && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') )
{
  echo'<p class="danger">'.html(Lang::_("Vous n'êtes pas habilité à accéder à cette fonctionnalité !")).'</p>'.NL;
  echo'<div class="astuce">Seuils les administrateurs et les personnels de direction peuvent consulter cette page.</div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

$step = isset($_GET['step']) ? Clean::entier($_GET['step']) : 0 ;

// On complète le Sous-Menu d'en-tête
$tab_step = array(
  0 => array( 'titre' => "Accueil interface d'export" ) ,
  1 => array( 'titre' => "Récolter les données dans SACoche"    , 'consigne' => "à effectuer classe par classe une fois le bilan complet / fermé" ) ,
  2 => array( 'titre' => "Générer un fichier d'export pour LSU" , 'consigne' => "à effectuer par exemple à chaque période pour tous les élèves, ou pour un élève qui quitte l'établissement" ) ,
);
$SOUS_MENU .= '<hr />';
foreach($tab_step as $key => $tab)
{
  $class = ($key==$step) ? ' class="actif"' : '' ;
  $numero = ($key) ? $key.'/2 ' : '' ;
  $SOUS_MENU .= '<a'.$class.' href="./index.php?page=livret&amp;section=export&amp;step='.$key.'">'.$numero.$tab['titre'].'</a>'.NL;
  // On complète le titre de la page
  if($key==$step)
  {
    $TITRE .= ' &rarr; '.html($numero.$tab['titre']);
  }
}
unset($tab_step[0]);

// Javascript
Layout::add( 'js_inline_before' , 'var TODAY_FR = "'.TODAY_FR.'";' );

// Vérif UAI
if(!$_SESSION['WEBMESTRE_UAI'])
{
  $webmestre_menu_uai  = (HEBERGEUR_INSTALLATION=='multi-structures') ? '[Gestion des inscriptions] [Gestion des établissements]' : '[Paramétrages installation] [Identité de l\'installation]' ;
  $webmestre_menu_doc  = (HEBERGEUR_INSTALLATION=='multi-structures') ? 'support_webmestre__structure_gestion#toggle_modif_etabl' : 'support_webmestre__identite_installation' ;
  echo'<p><label class="erreur">Numéro UAI non renseigné par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier='.$webmestre_menu_doc.'">DOC</a></span>&nbsp;&nbsp;&nbsp;'.HtmlMail::to(WEBMESTRE_COURRIEL,'SACoche - référence UAI','contact','Bonjour,<br />La référence UAI de notre établissement (base n°'.$_SESSION['BASE'].') n\'est pas renseignée.<br />Pouvez-vous faire le nécessaire depuis votre menu '.$webmestre_menu_uai.' ?<br />Merci.').'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

function afficher_entete_puce_doc_et_info( $tab_puces_supplementaires=array() )
{
  echo'<ul class="puce">'.NL;
  echo  '<li><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__livret_scolaire_administration#toggle_export">DOC : Administration du Livret Scolaire &rarr; Export LSU</a></span></li>'.NL;
  echo  '<li><span class="astuce">Numéro UAI d\'établissement : <b>'.$_SESSION['WEBMESTRE_UAI'].'</b></span></li>'.NL;
  echo implode(NL,$tab_puces_supplementaires);
  echo'</ul>'.NL;
  echo'<hr />'.NL;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// 1/2 Récolter les données dans SACoche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($step==1)
{

  // Tableaux utiles

  $tab_puce_info = array();

  $tab_periode_livret = array(
    'periode21' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 1/2' ),
    'periode22' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 2/2' ),
    'periode31' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 1/3'),
    'periode32' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 2/3'),
    'periode33' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 3/3'),
    'periode41' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 1/4' ),
    'periode42' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 2/4' ),
    'periode43' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 3/4' ),
    'periode44' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Bimestre 4/4' ),
    'periode51' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 1/5'  ),
    'periode52' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 2/5'  ),
    'periode53' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 3/5'  ),
    'periode54' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 4/5'  ),
    'periode55' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Période 5/5'  ),
    'cycle'     => array( 'used' => FALSE , 'defined' => TRUE  , 'dates' => TRUE  , 'nom' => 'Fin de cycle' ),
  );

  $tab_etats = array
  (
    '1vide'     => 'Vide (fermé)',
    '2rubrique' => 'Saisies Profs',
    '3mixte'    => 'Saisies Mixtes',
    '4synthese' => 'Saisie Synthèse',
    '5complet'  => 'Complet (fermé)',
  );

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Récupération de la liste des jointures livret / classes / périodes
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_jointures_classes_livret();
  if(empty($DB_TAB))
  {
    echo'<p><label class="erreur">Aucune association de classe au livret scolaire enregistrée !</label></p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  $tab_page_ref = array();
  $tab_join_classe_periode = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $periode = $DB_ROW['livret_page_periodicite'].$DB_ROW['jointure_periode'];
    $tab_periode_livret[$periode]['used'] = TRUE;
    if($DB_ROW['periode_id'])
    {
      $tab_periode_livret[$periode]['defined'] = TRUE;
    }
    if( $DB_ROW['jointure_date_debut'] && $DB_ROW['jointure_date_fin'] )
    {
      $tab_periode_livret[$periode]['dates'] = TRUE;
    }
    $tab_join_classe_periode[$DB_ROW['groupe_id']][$periode] = array(
      'page_ref'      => $DB_ROW['livret_page_ref'],
      'etat'          => $DB_ROW['jointure_etat'],
      'rubrique_type' => $DB_ROW['livret_page_rubrique_type'],
      'periode_id'    => $DB_ROW['periode_id'],
      'date_debut'    => $DB_ROW['jointure_date_debut'],
      'date_fin'      => $DB_ROW['jointure_date_fin'],
      'date_export'   => $DB_ROW['jointure_date_export'],
      'nb_impression' => 0,
    );
    $tab_page_ref[] = $DB_ROW['livret_page_ref'];
  }
  $tab_periode_pb = array( 'undefined' => array() , 'pbdates' => 0 );
  foreach($tab_periode_livret as $periode => $tab)
  {
    if(!$tab['used'])
    {
      unset($tab_periode_livret[$periode]);
    }
    else if(!$tab['defined'])
    {
      $tab_periode_pb['undefined'][] = $tab['nom'];
    }
    else if(!$tab['dates'])
    {
      $tab_periode_pb['pbdates']++;
    }
  }
  if(!empty($tab_periode_pb['undefined']))
  {
    $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode">Paramétrer les périodes.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_gestion_periodes">paramétrer les périodes</a></span>.' ;
    echo'<p><label class="erreur">Désignation des périodes pour le livret scolaire non effectuée pour "'.implode(' + ',$tab_periode_pb['undefined']).'" !'.$consigne.'</label></p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  if($tab_periode_pb['pbdates'])
  {
    $s = ( $tab_periode_pb['pbdates'] > 1 ) ? 's' : '' ;
    $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode&section=classe_groupe">Effectuer les associations.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_affecter_periodes">effectuer les associations</a></span>.' ;
    echo'<p><label class="erreur">Association datée des périodes aux classes non effectuée pour '.$tab_periode_pb['pbdates'].' période'.$s.' !'.$consigne.'</label></p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  // On compte aussi le nb de bilans déjà imprimé pour l'année scolaire en cours (PDF) pour limiter ceux qui tente d'exporter sans édition donc même parfois sans aucune tentative de remplissage automatique
  $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
  $annee_scolaire = To::annee_scolaire('code',$annee_decalage);
  $tab_periode_livret_key = array(
    21 => 'periode21',
    22 => 'periode22',
    31 => 'periode31',
    32 => 'periode32',
    33 => 'periode33',
    41 => 'periode41',
    42 => 'periode42',
    43 => 'periode43',
    44 => 'periode44',
    51 => 'periode51',
    52 => 'periode52',
    53 => 'periode53',
    54 => 'periode54',
    55 => 'periode55',
    NULL => 'cycle' ,
  );
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_compter_impression_archives($annee_scolaire);
  foreach($DB_TAB as $DB_ROW)
  {
    $periode = $tab_periode_livret_key[$DB_ROW['periode_livret']];
    $tab_join_classe_periode[$DB_ROW['eleve_classe_id']][$periode]['nb_impression'] = $DB_ROW['nombre'];
  }

  // Besoin de connaitre le chef d'établissement
  if( count( array_intersect( $tab_page_ref , array('6e','5e','4e','3e','cycle1','cycle2','cycle3','cycle4') ) ) )
  {
    $listing_groupe = DB_STRUCTURE_LIVRET::DB_lister_classes_livret_sans_chef();
    if(!empty($listing_groupe))
    {
      $nb_classes_concernees = substr_count($listing_groupe,',')+1;
      $s = ($nb_classes_concernees>1) ? 's' : '' ;
      $consigne = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || ($_SESSION['USER_PROFIL_TYPE']=='directeur') ) ? ' <a href="./index.php?page=livret&amp;section=classes">Renseigner cette information.</a>' : '<br />Un administrateur ou un directeur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__livret_scolaire_administration#toggle_classes">configurer un responsable par classe</a></span>.' ;
      echo'<p><label class="erreur">Chef d\'établissement ou directeur d\'école non désigné pour '.$nb_classes_concernees.' classe'.$s.' !'.$consigne.'</label></p>'.NL;
      return; // Ne pas exécuter la suite de ce fichier inclus.
    }
  }

  // Vérif présence Id SIECLE | INE
  if( count( array_intersect( $tab_page_ref , array('6e','5e','4e','3e','cycle3','cycle4') ) ) )
  {
    $nb_eleves_sans_sconet = DB_STRUCTURE_LIVRET::DB_compter_eleves_actuels_sans_id_sconet();
    if($nb_eleves_sans_sconet)
    {
      $s = ($nb_eleves_sans_sconet>1) ? 's' : '' ;
      $tab_puce_info[] = '<li><span class="danger">'.$nb_eleves_sans_sconet.' élève'.$s.' trouvé'.$s.' sans identifiant Siècle.</span> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_siecle">DOC</a></span></li>';
    }
  }
  if( count( array_intersect( $tab_page_ref , array('cp','ce1','ce2','cm1','cm2','cycle2') ) ) )
  {
    $nb_eleves_sans_INE = DB_STRUCTURE_LIVRET::DB_compter_eleves_actuels_sans_INE();
    if($nb_eleves_sans_INE)
    {
      $s = ($nb_eleves_sans_INE>1) ? 's' : '' ;
      $tab_puce_info[] = '<li><span class="danger">'.$nb_eleves_sans_INE.' élève'.$s.' trouvé'.$s.' sans Identifiant National Élève (INE).</span> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_onde">DOC</a></span></li>';
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Récupération de la liste des classes de l'établissement.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);

  $tab_classe = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_classe[$DB_ROW['valeur']] = $DB_ROW['texte'];
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Renseigner le contenu des jointures classes / périodes.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  $tab_affich = array();
  $listing_classes_id = implode(',',array_keys($tab_classe));
  foreach($tab_periode_livret as $periode => $tab)
  {
    foreach($tab_classe as $classe_id => $classe_nom)
    {
      $tab_join = isset($tab_join_classe_periode[$classe_id][$periode]) ? $tab_join_classe_periode[$classe_id][$periode] : NULL ;
      if(!$tab_join)
      {
        $tab_affich[$classe_id][$periode] = '<td class="hc notnow"">-</td>';
      }
      else if( $tab_join['page_ref'] == 'cycle1' )
      {
        $tab_affich[$classe_id][$periode] = '<td class="hc notnow"">sans objet (hors LSU)</td>';
      }
      else
      {
        $etat = $tab_join['etat'];
        if($etat!='5complet')
        {
          $tab_affich[$classe_id][$periode] = '<td class="hc"><label class="erreur">'.$tab_etats[$etat].'</label></td>';
        }
        elseif(!$tab_join['nb_impression'])
        {
          $tab_affich[$classe_id][$periode] = '<td class="hc"><label class="erreur">Pas de <em>pdf</em> généré</label></td>';
        }
        else
        {
          $info_export = ($tab_join['date_export']) ? '<div class="astuce">Récolté le '.To::date_mysql_to_french($tab_join['date_export']).'.</div>' : '<div><label class="alerte">Données non récoltées</label></div>' ;
          $page_ref = $tab_join['page_ref'];
          $id = '_'.$classe_id.'_'.$page_ref.'_'.$periode;
          $bouton_recolte = ($page_ref!='cycle1') ? '<button id="ids'.$id.'" type="button" class="generer">Récolter les données</button>' : '' ;
          $tab_affich[$classe_id][$periode] = '<td class="hc">'.$info_export.$bouton_recolte.'</td>';
        }
      }
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Affichage.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  afficher_entete_puce_doc_et_info($tab_puce_info);

  echo'<table id="table_accueil" class="p">'.NL;
  echo'<thead><tr><td class="nu"></td>';
  foreach($tab_periode_livret as $periode => $tab)
  {
    echo'<th class="hc">'.$tab['nom'].'</th>';
  }
  echo'</tr></thead>'.NL;
  echo'<tbody>'.NL;
  foreach($tab_classe as $classe_id => $classe_nom)
  {
    echo'<tr><th>'.html($classe_nom).'</th>'.implode('',$tab_affich[$classe_id]).'</tr>'.NL;
  }
  echo'</tbody></table>'.NL;

  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// 2/2 Générer un fichier d'export pour LSU
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($step==2)
{
  // Fabrication des éléments select du formulaire
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( TRUE /*sans*/ , TRUE /*tout*/ , TRUE /*ancien*/ );
  $tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_livret_periode_export();

  $select_groupe    = HtmlForm::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ , ''                /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
  $select_periode   = HtmlForm::afficher_select($tab_periodes , 'f_periode' /*select_nom*/ , 'toutes_periodes' /*option_first*/ , FALSE /*selection*/ , ''              /*optgroup*/ );

  // Affichage.
  afficher_entete_puce_doc_et_info();
  ?>
  <form action="#" method="post" id="form_select"><fieldset>
    <p>
      <label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?><label id="ajax_msg_groupe">&nbsp;</label><input id="listing_ids" name="listing_ids" type="hidden" value="" /><br />
      <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
    </p>
    <p>
      <span id="bloc_periode"><label class="tab" for="f_periode">Période(s) :</label><?php echo $select_periode ?></span>
    </p>
    <p>
      <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="generer_export" /><input id="f_groupe_nom" name="f_groupe_nom" type="hidden" value="" /><button id="bouton_valider" type="submit" class="valider">Générer le fichier</button><label id="ajax_msg">&nbsp;</label>
    </p>
  </fieldset></form>
  <div id="bilan"></div>
  <?php
  return; // Ne pas exécuter la suite de ce fichier inclus.

}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Introduction
// ////////////////////////////////////////////////////////////////////////////////////////////////////

afficher_entete_puce_doc_et_info();

// Info sur les fichiers requis issus de SIECLE ou de ONDE
$test_college = DB_STRUCTURE_LIVRET::DB_tester_jointure_classe_livret('"6e","5e","4e","3e","cycle3","cycle4"');
$test_ecole   = DB_STRUCTURE_LIVRET::DB_tester_jointure_classe_livret('"cp","ce1","ce2","cm1","cm2","cycle2"');
if( $test_college || $test_ecole )
{
  $annee_decalage = empty($_SESSION['NB_DEVOIRS_ANTERIEURS']) ? 0 : -1 ;
  $annee_scolaire = To::annee_scolaire('siecle',$annee_decalage);
  echo'<h2>Avertissement</h2>'.NL;
  echo'<p>Pour la transmission des informations à LSU, la connaissance d\'informations issues de <em>Siècle</em> (2D) ou de <em>ONDE</em> (1D) pour l\'année scolaire en cours est nécessaire.</p>'.NL;
  $tab_fichier = array(
    'Onde' => array(
      'use' => $test_ecole ,
      'nom' => 'CSVExtraction.csv' ,
      'doc' => 'support_administrateur__import_users_onde#toggle_exporter_eleves' ,
      'opt' => 'onde_eleves' ,
    ),
    'Eleves' => array(
      'use' => $test_college ,
      'nom' => 'ExportXML_ElevesSansAdresses.zip' ,
      'doc' => 'support_administrateur__import_users_siecle#toggle_exporter_eleves' ,
      'opt' => 'siecle_eleves_oui' ,
    ),
    'sts_emp_UAI' => array(
      'use' => $test_college ,
      'nom' => 'sts_emp_'.$_SESSION['WEBMESTRE_UAI'].'_'.$annee_scolaire.'.xml' ,
      'doc' => 'support_administrateur__import_users_siecle#toggle_exporter_profs' ,
      'opt' => 'siecle_professeurs_directeurs_oui' ,
    ),
    'Nomenclature' => array(
      'use' => $test_college ,
      'nom' => 'Nomenclature.xml' ,
      'doc' => 'support_administrateur__import_users_siecle#toggle_nomenclatures' ,
      'opt' => 'siecle_nomenclature_oui' ,
    ),
    'Communs' => array(
      'use' => $test_college ,
      'nom' => 'Communs.xml' ,
      'doc' => 'support_administrateur__import_users_siecle#toggle_communs' ,
      'opt' => 'siecle_commun_oui' ,
    ),
  );
  foreach($tab_fichier as $fichier_id => $tab)
  {
    if($tab['use'])
    {
      echo'<h3>Fichier [ '.$tab['nom'].' ]</h3>'.NL;
      echo'<ul class="puce">'.NL;
      $DB_ROW = DB_STRUCTURE_SIECLE::DB_recuperer_import_date_annee($fichier_id);
      if( empty($DB_ROW) || is_null($DB_ROW['siecle_import_date']) )
      {
        echo'<li id="etat_'.$fichier_id.'"><span class="danger">Absence de fichier !</span></li>'.NL;
      }
      else if( $annee_scolaire != $DB_ROW['siecle_import_annee'] )
      {
        echo'<li id="etat_'.$fichier_id.'"><span class="danger">Dernier fichier connu antérieur à cette année scolaire !</span></li>'.NL;
      }
      else if( ($tab['opt']=='onde_eleves') && ($DB_ROW['siecle_import_date']<'2017-05-22') )
      {
        echo'<li id="etat_'.$fichier_id.'"><span class="danger">Protocole modifié depuis votre dernier import ONDE ! Merci de renouveller l\'import de ce ficher.</span></li>'.NL;
      }
      else
      {
        echo'<li id="etat_'.$fichier_id.'"><span class="astuce">Dernier import en date du <b>'.To::date_mysql_to_french($DB_ROW['siecle_import_date']).'</b>.</span></li>'.NL;
      }
      $precision = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? '' : ' (à importer depuis un compte administrateur)';
      echo'<li><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier='.$tab['doc'].'">Explications pour récupérer ce fichier'.$precision.'.</a></span></li>'.NL;
      if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
      {
        echo'<li><span class="sousmenu"><a href="./index.php?page=administrateur_fichier_user&amp;categorie='.$tab['opt'].'">Menu administrateur pour importer ce fichier.</a></span></li>'.NL;
      }
      echo'</ul>'.NL;
    }
  }
}

echo'<hr />'.NL;
echo'<h2>Accès aux étapes</h2>'.NL;
echo'<p>Il y a deux étapes :</p>'.NL;
echo'<ul class="puce">'.NL;
foreach($tab_step as $key => $tab)
{
  echo'<li class="p"><a href="./index.php?page=livret&amp;section=export&amp;step='.$key.'">'.$key.'/2 '.$tab['titre'].'</a> &rarr; '.$tab['consigne'].'.</li>'.NL;
}
echo'</ul>'.NL;
