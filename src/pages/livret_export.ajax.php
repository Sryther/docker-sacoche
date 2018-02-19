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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action!='recolter') && ($action!='generer_export') )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

$PAGE_REF   = (isset($_POST['f_page_ref']))   ? Clean::texte($_POST['f_page_ref'])   : '';
$periode    = (isset($_POST['f_periode']))    ? Clean::texte($_POST['f_periode'])    : '';
$classe_id  = (isset($_POST['f_classe']))     ? Clean::entier($_POST['f_classe'])    : 0;
$groupe_nom = (isset($_POST['f_groupe_nom'])) ? Clean::texte($_POST['f_groupe_nom']) : '';
$tab_eleve  = (isset($_POST['listing_ids']))  ? explode(',',$_POST['listing_ids'])   : array() ;
$tab_eleve  = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

// Tableaux communs utiles

$tab_periode_livret = array(
  'periode21' => 'Semestre 1/2' ,
  'periode22' => 'Semestre 2/2' ,
  'periode31' => 'Trimestre 1/3',
  'periode32' => 'Trimestre 2/3',
  'periode33' => 'Trimestre 3/3',
  'periode41' => 'Bimestre 1/4' ,
  'periode42' => 'Bimestre 2/4' ,
  'periode43' => 'Bimestre 3/4' ,
  'periode44' => 'Bimestre 4/4' ,
  'periode51' => 'Période 1/5'  ,
  'periode52' => 'Période 2/5'  ,
  'periode53' => 'Période 3/5'  ,
  'periode54' => 'Période 4/5'  ,
  'periode55' => 'Période 5/5'  ,
  'cycle'     => 'Fin de cycle' ,
);

$tab_export_type = array(
  'cycle1' => 'maternelle',
  'cp'     => 'ecole',
  'ce1'    => 'ecole',
  'ce2'    => 'ecole',
  'cycle2' => 'ecole',
  'cm1'    => 'ecole',
  'cm2'    => 'ecole',
  '6e'     => 'college',
  'cycle3' => 'college',
  '5e'     => 'college',
  '4e'     => 'college',
  '3e'     => 'college',
  'cycle4' => 'college',
);

$version_xsd_college = '3.0'; // $version_xsd_college = (version_compare(TODAY_MYSQL,'2017-03-01','<')) ? '2.0' : '3.0' ;

$tab_xml_param = array(
  'ecole' => array(
    'schema'   => 'xsi:schemaLocation="urn:fr:edu:scolarite:lsun:bilans:import import-bilan-1d.xsd" schemaVersion="1.0"',
    'file_xsd' => 'livret-scolaire_1d_schema_1.0b.xsd',
  ),
  'college' => array(
    'schema'   => 'xsi:schemaLocation="urn:fr:edu:scolarite:lsun:bilans:import import-bilan-complet.xsd" schemaVersion="'.$version_xsd_college.'"',
    'file_xsd' => 'livret-scolaire_2d_schema_'.$version_xsd_college.'c.xsd',
  ),
);

// Vérification période

if( !isset($tab_periode_livret[$periode]) && ( ($action=='recolter') || ($periode!='') ) )
{
  Json::end( FALSE , 'Période "'.html($periode).'" inconnue !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récolter les données pour les élèves d'une classes et d'une période
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='recolter')
{
  $periode_nom = $tab_periode_livret[$periode];
  require(CHEMIN_DOSSIER_INCLUDE.'code_livret_recolter.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer le fichier d'export en fonction des critères sélectionnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Elèves
if( empty($tab_eleve) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}
$liste_eleve_id = implode(',',$tab_eleve);

// Période
if(substr($periode,0,7)=='periode')
{
  $PAGE_PERIODICITE = 'periode';
  $JOINTURE_PERIODE = substr($periode,7);
}
else
{
  $PAGE_PERIODICITE = ($periode!=='') ? $periode : '' ;
  $JOINTURE_PERIODE = NULL;
}

// Au cas où...
Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , TRUE /*time*/ );

// Données
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_livret_export_eleves( $liste_eleve_id , $PAGE_PERIODICITE , $JOINTURE_PERIODE );
if( empty($DB_TAB) )
{
  Json::end( FALSE , 'Aucun bilan trouvé selon les critères transmis !' );
}

// On extrait les données élèves et on mutualise les données communes
$tab_export_objet   = array();
$tab_export_eleve   = array();
$tab_export_donnees = array();
$tab_export_commun  = array(
  'periode'          => array(),
  'directeur'        => array(), // 1D
  'classe'           => array(), // 1D
  'responsable-etab' => array(), // 2D
  'discipline'       => array(), // 2D
  'enseignant'       => array(),
  'element'          => array(),
  'epi-thematique'   => array(), // 2D
  'epi'              => array(), // 2D
  'ap'               => array(), // 2D
  'parcours'         => array(),
  'viesco'           => array(), // 2D
);

foreach($DB_TAB as $key => $DB_ROW)
{
  $export_objet = $tab_export_type[$DB_ROW['livret_page_ref']];
  $tab_export_objet[ $export_objet ] = TRUE;
  $tab_export_donnees[$key] = json_decode($DB_ROW['export_contenu'], TRUE);
  $tab_export_eleve[$DB_ROW['user_id']] = $tab_export_donnees[$key]['eleve'];
  if($export_objet=='college')
  {
    $tab_export_commun['responsable-etab'] = array_merge( $tab_export_commun['responsable-etab'] , $tab_export_donnees[$key]['commun']['responsable-etab'] );
    // ajouté en octobre 2017, des données peuvent avoir été récoltées sans cette entrée --- TODO : à supprimer à compter de la prochaine rentrée
    if(isset($tab_export_donnees[$key]['commun']['epi-thematique']))
    {
      
      $tab_export_commun['epi-thematique']   = array_merge( $tab_export_commun['epi-thematique']   , $tab_export_donnees[$key]['commun']['epi-thematique']   );
    }
    $tab_export_commun['epi']              = array_merge( $tab_export_commun['epi']              , $tab_export_donnees[$key]['commun']['epi']              );
    $tab_export_commun['ap']               = array_merge( $tab_export_commun['ap']               , $tab_export_donnees[$key]['commun']['ap']               );
    $tab_export_commun['viesco']           = array_merge( $tab_export_commun['viesco']           , $tab_export_donnees[$key]['commun']['viesco']           );
    $tab_export_commun['discipline']       = array_merge( $tab_export_commun['discipline']       , $tab_export_donnees[$key]['commun']['discipline']       );
  }
  if($export_objet=='ecole')
  {
    $tab_export_commun['directeur']        = array_merge( $tab_export_commun['directeur']        , $tab_export_donnees[$key]['commun']['directeur']        );
    $tab_export_commun['classe']           = array_merge( $tab_export_commun['classe']           , $tab_export_donnees[$key]['commun']['classe']           );
    // START - Avril 2017 - patch correctif pour les identifiants de périodes au 1D - TODO : à supprimer à compter de la prochaine rentrée
    $tab_periode_tmp = array();
    $tab_periode_key = array();
    if(!empty($tab_export_donnees[$key]['commun']['periode']))
    {
      // périodes
      foreach($tab_export_donnees[$key]['commun']['periode'] as $id_periode => $tab)
      {
        $pos_cl = strpos($id_periode,'CL');
        if($pos_cl)
        {
          $key_periode = 'PER'.substr($id_periode,3,$pos_cl-3).'D'.substr($tab['date-debut'],-5,2).substr($tab['date-debut'],-2,2).'F'.substr($tab['date-fin'],-5,2).substr($tab['date-fin'],-2,2) ;
          $tab_periode_key[$id_periode] = $key_periode;
          $tab_periode_tmp[$key_periode] = $tab;
        }
      }
      if(!empty($tab_periode_key))
      {
        $tab_export_donnees[$key]['commun']['periode'] = $tab_periode_tmp;
        $tab_bad = array_keys($tab_periode_key);
        $tab_bon = array_values($tab_periode_key);
        // parcours
        if(!empty($tab_export_donnees[$key]['commun']['parcours']))
        {
          foreach($tab_export_donnees[$key]['commun']['parcours'] as $id_parcours => $tab)
          {
            $tab_export_donnees[$key]['commun']['parcours'][$id_parcours]['periode-ref'] = str_replace( $tab_bad , $tab_bon , $tab['periode-ref'] );
          }
        }
        // bilan
        if($tab_export_donnees[$key]['bilan']['type']=='periode')
        {
          $tab_export_donnees[$key]['bilan']['periode-ref'] = str_replace( $tab_bad , $tab_bon , $tab_export_donnees[$key]['bilan']['periode-ref'] );
        }
      }
    }
    // END - Avril 2017 - patch correctif pour les identifiants de périodes au 1D - TODO : à supprimer à compter de la prochaine rentrée
  }
  $tab_export_commun['periode']          = array_merge( $tab_export_commun['periode']          , $tab_export_donnees[$key]['commun']['periode']          );
  $tab_export_commun['enseignant']       = array_merge( $tab_export_commun['enseignant']       , $tab_export_donnees[$key]['commun']['enseignant']       );
  $tab_export_commun['element']          = array_merge( $tab_export_commun['element']          , $tab_export_donnees[$key]['commun']['element']          );
  $tab_export_commun['parcours']         = array_merge( $tab_export_commun['parcours']         , $tab_export_donnees[$key]['commun']['parcours']         );
  unset( $tab_export_donnees[$key]['eleve'] , $tab_export_donnees[$key]['commun'] );
}

if( isset($tab_export_objet['cycle_maternelle']) )
{
  Json::end( FALSE , 'L\'application nationale ne gère pas le bilan de maternelle (qui ne fait pas partie du Livret Scolaire) !' );
}

if( count($tab_export_objet) > 1 )
{
  Json::end( FALSE , 'Il est impossible d\'exporter dans un fichier unique des bilans de différents formats('.implode(' / ',array_keys($tab_export_objet)).') !' );
}

$export_objet = key($tab_export_objet);

// On attaque le XML : prologue + élément racine

$tab_xml = array();
$tab_xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
$tab_xml[] = '<lsun-bilans xmlns="urn:fr:edu:scolarite:lsun:bilans:import" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.$tab_xml_param[$export_objet]['schema'].'>';
// entête
$tab_xml[] = ' <entete>';
$tab_xml[] = '  <editeur>Sésamath</editeur>';
$tab_xml[] = '  <application>SACoche</application>';
$tab_xml[] = '  <etablissement>'.html($_SESSION['WEBMESTRE_UAI']).'</etablissement>';
$tab_xml[] = ' </entete>';
$tab_xml[] = ' <donnees>';

// Attention, pour la conformité du XML, l'odre est important (j'aurais bien mis <eleves> en premier mais ce n'est pas permis).

$key_classe        = ($export_objet=='college') ? 'code-division'        : 'classe-ref' ;
$key_profs_eleve   = ($export_objet=='college') ? 'prof-princ-refs'      : 'enseignant-refs' ;
$key_chef_etabl    = ($export_objet=='college') ? 'responsable-etab-ref' : 'directeur-ref' ;
$key_appr_synthese = ($export_objet=='college') ? 'acquis-conseils'      : 'appreciation-generale' ;

// responsables-etab (2D)
if($export_objet=='college')
{
  $tab_xml[] = '  <responsables-etab>';
  foreach($tab_export_commun['responsable-etab'] as $id => $tab)
  {
    $tab_xml[] = '   <responsable-etab id="'.$id.'" libelle="'.html($tab['libelle']).'" />';
  }
  $tab_xml[] = '  </responsables-etab>';
}

// directeurs & classes (1D)
if($export_objet=='ecole')
{
  $tab_xml[] = '  <directeurs>';
  foreach($tab_export_commun['directeur'] as $id => $tab)
  {
    $tab_xml[] = '   <directeur id="'.$id.'" civilite="'.$tab['civilite'].'" nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />';
  }
  $tab_xml[] = '  </directeurs>';
  $tab_xml[] = '  <classes>';
  foreach($tab_export_commun['classe'] as $id => $tab)
  {
    $tab_xml[] = '   <classe id="'.$id.'" id-be="'.$tab['id-be'].'" libelle="'.html($tab['libelle']).'" />';
  }
  $tab_xml[] = '  </classes>';
}

// eleves
$nb_eleves = count($tab_export_eleve);
$tab_xml[] = '  <eleves>';
foreach($tab_export_eleve as $eleve_id => $tab)
{
  switch($export_objet)
  {
    case 'college' : $tab_xml[] = '   <eleve id="'.$tab['id'].'" id-be="'.$tab['id-be'].'" nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" '.$key_classe.'="'.html($tab[$key_classe]).'" />'; break;
    case 'ecole'   : $tab_xml[] = '   <eleve id="'.$tab['id'].'" ine="'.$tab['ine'].'" '.$key_classe.'="'.html($tab[$key_classe]).'" nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
  }
}
$tab_xml[] = '  </eleves>';
unset( $tab_export_eleve );

// periodes (uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['periode']))
{
  $tab_xml[] = '  <periodes>';
  foreach($tab_export_commun['periode'] as $id => $tab)
  {
    $date_debut = ($export_objet=='college') ? '' : ' date-debut="'.$tab['date-debut'].'"' ;
    $date_fin   = ($export_objet=='college') ? '' : ' date-fin="'.$tab['date-fin'].'"' ;
    $tab_xml[] = '   <periode id="'.$id.'" millesime="'.$tab['millesime'].'" indice="'.$tab['indice'].'" nb-periodes="'.$tab['nb-periodes'].'"'.$date_debut.$date_fin.' />';
  }
  $tab_xml[] = '  </periodes>';
}

// disciplines (2D & uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['discipline']))
{
  $tab_xml[] = '  <disciplines>';
  foreach($tab_export_commun['discipline'] as $id => $tab)
  {
    $tab_xml[] = '   <discipline id="'.$id.'" code="'.$tab['code'].'" modalite-election="'.$tab['modalite-election'].'" libelle="'.html($tab['libelle']).'" />';
  }
  $tab_xml[] = '  </disciplines>';
}

// enseignants
$tab_xml[] = '  <enseignants>';
foreach($tab_export_commun['enseignant'] as $id => $tab)
{
  $civilite = $tab['civilite'] ? ' civilite="'.$tab['civilite'].'"' : '' ;
  switch($export_objet)
  {
    case 'college' : $tab_xml[] = '   <enseignant id="'.$id.'" type="'.$tab['type'].'" id-sts="'.$tab['id-sts'].'"'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
    case 'ecole'   : $tab_xml[] = '   <enseignant id="'.$id.'"'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
  }
}
$tab_xml[] = '  </enseignants>';

// elements-programme (facultatif + uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['element']))
{
  $tab_xml[] = '  <elements-programme>';
  foreach($tab_export_commun['element'] as $id => $libelle)
  {
    $tab_xml[] = '   <element-programme id="'.$id.'" libelle="'.html($libelle).'" />';
  }
  $tab_xml[] = '  </elements-programme>';
}

// parcours-communs (appréciation sur la classe) (facultatif + uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['parcours']))
{
  $tab_parcours = array();
  foreach($tab_export_commun['parcours'] as $key => $tab)
  {
    $tab_parcours[$tab['periode-ref'].'¤'.$tab[$key_classe]][] = $tab;
  }
  $tab_xml[] = '  <parcours-communs>';
  foreach($tab_parcours as $key => $tab_ensemble)
  {
    list( $periode_ref , $champ_classe ) = explode('¤',$key,2);
    $tab_xml[] = '   <parcours-commun periode-ref="'.$periode_ref.'" '.$key_classe.'="'.html($champ_classe).'">';
    foreach($tab_ensemble as $tab)
    {
      $tab_xml[] = '    <parcours code="'.$tab['code'].'">'.html($tab['projet']).'</parcours>';
    }
    $tab_xml[] = '   </parcours-commun>';
  }
  $tab_xml[] = '  </parcours-communs>';
}

// vies-scolaires-communs (appréciation sur la classe) (facultatif + uniquement si présence de bilans périodiques)
// --> pas de saisie proposée dans SACoche


// epi-thematique (2D & uniquement si présence de thématiques personnalisées dans des bilans périodiques)
if(!empty($tab_export_commun['epi-thematique']))
{
  $tab_xml[] = '  <epis-thematiques>';
  foreach($tab_export_commun['epi-thematique'] as $code => $libelle)
  {
    $tab_xml[] = '   <epi-thematique code="'.$code.'" libelle="'.html($libelle).'" />';
  }
  $tab_xml[] = '  </epis-thematiques>';
}

// epis & epis-groupes (facultatif + uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['epi']))
{
  $tab_xml[] = '  <epis>';
  foreach($tab_export_commun['epi'] as $key => $tab)
  {
    $tab_xml[] = '   <epi id="'.$tab['id'].'" intitule="'.html($tab['intitule']).'" thematique="'.$tab['thematique'].'" discipline-refs="'.implode(' ',array_unique($tab['discipline-refs'])).'">';
    if($tab['description'])
    {
      $tab_xml[] = '    <description>'.html($tab['description']).'</description>';
    }
    $tab_xml[] = '   </epi>';
  }
  $tab_xml[] = '  </epis>';
    // Pas de sous-groupes gérés dans SACoche, l'EPI est défini sur une classe...
  $tab_xml[] = '  <epis-groupes>';
  foreach($tab_export_commun['epi'] as $key => $tab)
  {
    $tab_xml[] = '   <epi-groupe id="'.$key.'GR" intitule="'.html($tab['intitule']).'" epi-ref="'.$tab['id'].'">';
    // Pour le commentaire sur le groupe, on reporte la description (c'est ce que veut LSU de toutes façons).
    if($tab['description'])
    {
      $tab_xml[] = '    <commentaire>'.html($tab['description']).'</commentaire>';
    }
    $tab_xml[] = '    <enseignants-disciplines>';
    foreach($tab['discipline-refs'] as $key => $discipline_ref)
    {
      $enseignant_ref = $tab['enseignant-refs'][$key];
      $tab_xml[] = '     <enseignant-discipline discipline-ref="'.$discipline_ref.'" enseignant-ref="'.$enseignant_ref.'" />';
    }
    $tab_xml[] = '    </enseignants-disciplines>';
    $tab_xml[] = '   </epi-groupe>';
  }
  $tab_xml[] = '  </epis-groupes>';
}

// acc-persos & acc-persos-groupes (facultatif + uniquement si présence de bilans périodiques)
if(!empty($tab_export_commun['ap']))
{
  $tab_xml[] = '  <acc-persos>';
  foreach($tab_export_commun['ap'] as $key => $tab)
  {
    $tab_xml[] = '   <acc-perso id="'.$tab['id'].'" intitule="'.html($tab['intitule']).'" discipline-refs="'.implode(' ',array_unique($tab['discipline-refs'])).'">';
    if($tab['description'])
    {
      $tab_xml[] = '    <description>'.html($tab['description']).'</description>';
    }
    $tab_xml[] = '   </acc-perso>';
  }
  $tab_xml[] = '  </acc-persos>';
    // Pas de sous-groupes gérés dans SACoche, l'AP est défini sur une classe...
  $tab_xml[] = '  <acc-persos-groupes>';
  foreach($tab_export_commun['ap'] as $key => $tab)
  {
    $tab_xml[] = '   <acc-perso-groupe id="'.$key.'GR" intitule="'.html($tab['intitule']).'" acc-perso-ref="'.$tab['id'].'">';
    // Pour le commentaire sur le groupe, on reporte la description (c'est ce que veut LSU de toutes façons).
    if($tab['description'])
    {
      $tab_xml[] = '    <commentaire>'.html($tab['description']).'</commentaire>';
    }
    $tab_xml[] = '    <enseignants-disciplines>';
    foreach($tab['discipline-refs'] as $key => $discipline_ref)
    {
      $enseignant_ref = $tab['enseignant-refs'][$key];
      $tab_xml[] = '     <enseignant-discipline discipline-ref="'.$discipline_ref.'" enseignant-ref="'.$enseignant_ref.'" />';
    }
    $tab_xml[] = '    </enseignants-disciplines>';
    $tab_xml[] = '   </acc-perso-groupe>';
  }
  $tab_xml[] = '  </acc-persos-groupes>';
}

unset( $tab_export_commun );

// responsables (dans une fonction car commun aux bilans périodiques et de fin de cycle)
function xml_responsables($tab_donnee_bilan_responsables)
{
  global $export_objet;
  $tab_xml = array();
  $tab_xml[] = '    <responsables>';
  foreach($tab_donnee_bilan_responsables as $resp_num => $tab)
  {
    $legal_num = ($export_objet=='college') ? ' legal'.$resp_num.'="true"' : '' ;
    $civilite  = $tab['civilite'] ? ' civilite="'.$tab['civilite'].'"'   : '' ;
    $ligne2    = $tab['ligne2']   ? ' ligne2="'.html($tab['ligne2']).'"' : '' ;
    $ligne3    = $tab['ligne3']   ? ' ligne3="'.html($tab['ligne3']).'"' : '' ;
    $ligne4    = $tab['ligne4']   ? ' ligne4="'.html($tab['ligne4']).'"' : '' ;
    $tab_xml[] = '     <responsable'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'"'.$legal_num.'>'; // lien-parente non géré (facultatif)
    $tab_xml[] = '      <adresse ligne1="'.html($tab['ligne1']).'"'.$ligne2.$ligne3.$ligne4.' code-postal="'.html($tab['code-postal']).'" commune="'.html($tab['commune']).'" />';
    $tab_xml[] = '     </responsable>';
  }
  $tab_xml[] = '    </responsables>';
  return $tab_xml;
}

// bilans périodiques ou de fin de cycle
$nb_bilans = count($tab_export_donnees);
$tab_xml_bilans = array ( 'periodique' => array() , 'cycle' => array() );
$rubrique_sans_element = FALSE ;
foreach($tab_export_donnees as $key => $tab_donnee_bilan)
{
  $tab_bilan = $tab_donnee_bilan['bilan'];
  // bilan périodique
  if($tab_bilan['type']=='periode')
  {
    $date_bilan  = ($export_objet=='college') ? 'date-conseil-classe="'.$tab_bilan['date-conseil-classe'].'"' : 'date-creation="'.$tab_bilan['date-creation'].'"' ;
    $profs_eleve_refs = !empty($tab_bilan[$key_profs_eleve]) ? ' '.$key_profs_eleve.'="'.$tab_bilan[$key_profs_eleve].'"' : '' ;
    $tab_xml_bilans['periodique'][] = '   <bilan-periodique'.$profs_eleve_refs.' eleve-ref="'.$tab_bilan['eleve-ref'].'" periode-ref="'.$tab_bilan['periode-ref'].'" '.$date_bilan.' date-scolarite="'.$tab_bilan['date-scolarite'].'" date-verrou="'.$tab_bilan['date-verrou'].'" '.$key_chef_etabl.'="'.$tab_bilan[$key_chef_etabl].'">';
    $champ_position = $tab_bilan['position'];
    // liste-acquis
    $tab_xml_bilans['periodique'][] = '    <liste-acquis>';
    // 2D
    if($export_objet=='college')
    {
      foreach($tab_donnee_bilan['acquis'] as $discipline_ref => $tab)
      {
        // élément de prg travaillé obligatoire
        $element_programme_refs = !empty($tab['elements']) ? implode(' ',$tab['elements']) : 'EL0' ;
        $position_eleve  = is_null($tab[$champ_position]) ? ' eleve-non-note="true"' : ' '.$champ_position.'="'.$tab[$champ_position].'"' ;
        $position_classe = ($champ_position=='positionnement') ? '' : ( is_null($tab['moyenne-structure']) ? ' structure-non-notee="true"' : ' moyenne-structure="'.$tab['moyenne-structure'].'"' ) ;
        $tab_xml_bilans['periodique'][] = '     <acquis discipline-ref="'.$discipline_ref.'" enseignant-refs="'.implode(' ',$tab['profs']).'" element-programme-refs="'.$element_programme_refs.'"'.$position_eleve.$position_classe.' >';
        // appréciation obligatoire sauf si élève non noté
        if( $tab['appreciation'] || !is_null($tab[$champ_position]) )
        {
          $tab['appreciation'] = !empty($tab['appreciation']) ? $tab['appreciation'] : '-' ;
          $tab_xml_bilans['periodique'][] = '      <appreciation>'.html($tab['appreciation']).'</appreciation>';
        }
        $rubrique_sans_element = $rubrique_sans_element || ($element_programme_refs=='EL0') ;
        $tab_xml_bilans['periodique'][] = '     </acquis>';
      }
    }
    // 1D
    if($export_objet=='ecole')
    {
      // on fait 2 passages parce que c'est compliqué sinon
      $tab_acquis_domaine = array();
      foreach($tab_donnee_bilan['acquis'] as $code => $tab)
      {
        list( $domaine , $sous_domaine ) = explode('-',$code);
        $element_programme_refs = !empty($tab['elements']) ? ' element-programme-refs="'.implode(' ',$tab['elements']).'"' : '' ;
        $positionnement = ($tab['positionnement']) ? ' positionnement="'.$tab['positionnement'].'"' : ' non-evalue="true"' ;
        if( $sous_domaine == 'RAC' )
        {
          $tab_acquis_domaine[$domaine] = array(
            'nb_sous_domaine'        => 0,
            'element_programme_refs' => $element_programme_refs,
            'positionnement'         => $positionnement,
            'appreciation'           => $tab['appreciation'],
          );
        }
        else
        {
          if(!isset($tab_acquis_domaine[$domaine]))
          {
            $tab_acquis_domaine[$domaine]['nb_sous_domaine'] = 1;
            $tab_acquis_domaine[$domaine]['nb_sous_domaine_avec_infos'] = 0;
            $tab_acquis_domaine[$domaine]['appreciation'] = $tab['appreciation'];
          }
          else
          {
            $tab_acquis_domaine[$domaine]['nb_sous_domaine'] += 1;
            $tab_acquis_domaine[$domaine]['appreciation'] .= $tab['appreciation'];
          }
          $tab_acquis_domaine[$domaine]['sous_domaine'][$sous_domaine] = array(
            'element_programme_refs' => $element_programme_refs,
            'positionnement'         => $positionnement,
          );
          if( $element_programme_refs || strpos($positionnement,'positionnement') )
          {
            $tab_acquis_domaine[$domaine]['nb_sous_domaine_avec_infos'] += 1;
          }
        }
      }
      foreach($tab_acquis_domaine as $code_domaine => $tab_info_domaine)
      {
        if($tab_info_domaine['nb_sous_domaine']==0)
        {
          $tab_xml_bilans['periodique'][] = '     <acquis code-domaine="'.$code_domaine.'-RAC"'.$tab_info_domaine['element_programme_refs'].$tab_info_domaine['positionnement'].'>';
          if($tab_info_domaine['appreciation'])
          {
            $tab_xml_bilans['periodique'][] = '      <appreciation>'.html($tab_info_domaine['appreciation']).'</appreciation>';
          }
          $tab_xml_bilans['periodique'][] = '     </acquis>';
        }
        else
        {
          $tab_xml_bilans['periodique'][] = '     <acquis code-domaine="'.$code_domaine.'-RAC">';
          if($tab_info_domaine['appreciation'])
          {
            $tab_xml_bilans['periodique'][] = '      <appreciation>'.html($tab_info_domaine['appreciation']).'</appreciation>';
          }
          if($tab_info_domaine['nb_sous_domaine'])
          {
            $tab_xml_bilans['periodique'][] = '      <sous-domaines>';
            foreach($tab_info_domaine['sous_domaine'] as $code_sous_domaine => $tab_info_sous_domaine)
            {
              // test pour éviter de passer ici en cas de domaine avec en théorie des sous-domaines mais en réalité seulement une appréciation transmise rattachée au 1er élément bien qu'il n'apparaisse pas
              // attention, il peut aussi s'agir d'un sous-domaine unique sans positionnement ni éléments de prg travaillés donc avec seulement une appréciation !
              if( $tab_info_sous_domaine['element_programme_refs'] || strpos($tab_info_sous_domaine['positionnement'],'positionnement') || ($tab_info_domaine['nb_sous_domaine']==1) || ($tab_info_domaine['nb_sous_domaine_avec_infos']==0) )
              {
                $tab_xml_bilans['periodique'][] = '       <sous-domaine code-domaine="'.$code_domaine.'-'.$code_sous_domaine.'"'.$tab_info_sous_domaine['element_programme_refs'].$tab_info_sous_domaine['positionnement'].' />';            }
            }
            $tab_xml_bilans['periodique'][] = '      </sous-domaines>';
          }
          $tab_xml_bilans['periodique'][] = '     </acquis>';
        }
      }
    }
    $tab_xml_bilans['periodique'][] = '    </liste-acquis>';
    // epis-eleve (2D)
    if(!empty($tab_donnee_bilan['epi']))
    {
      $tab_xml_bilans['periodique'][] = '    <epis-eleve>';
      foreach($tab_donnee_bilan['epi'] as $id => $tab)
      {
        $tab_xml_bilans['periodique'][] = '     <epi-eleve epi-groupe-ref="'.$id.'GR">';
        if(!empty($tab['appreciation']))
        {
          $tab_xml_bilans['periodique'][] = '      <commentaire>'.html($tab['appreciation']).'</commentaire>';
        }
        $tab_xml_bilans['periodique'][] = '     </epi-eleve>';
      }
      $tab_xml_bilans['periodique'][] = '    </epis-eleve>';
    }
    // acc-persos-eleve (2D)
    if(!empty($tab_donnee_bilan['ap']))
    {
      $tab_xml_bilans['periodique'][] = '    <acc-persos-eleve>';
      foreach($tab_donnee_bilan['ap'] as $id => $tab)
      {
        $tab_xml_bilans['periodique'][] = '     <acc-perso-eleve acc-perso-groupe-ref="'.$id.'GR">';
        if(!empty($tab['appreciation']))
        {
          $tab_xml_bilans['periodique'][] = '      <commentaire>'.html($tab['appreciation']).'</commentaire>';
        }
        $tab_xml_bilans['periodique'][] = '     </acc-perso-eleve>';
      }
      $tab_xml_bilans['periodique'][] = '    </acc-persos-eleve>';
    }
    // parcours
    if(!empty($tab_donnee_bilan['parcours']))
    {
      $tab_xml_bilans['periodique'][] = '    <liste-parcours>';
      foreach($tab_donnee_bilan['parcours'] as $id => $tab)
      {
        $tab['appreciation'] = !empty($tab['appreciation']) ? $tab['appreciation'] : '-' ;
        $tab_xml_bilans['periodique'][] = '     <parcours code="'.$tab['code'].'">'.html($tab['appreciation']).'</parcours>';
      }
      $tab_xml_bilans['periodique'][] = '    </liste-parcours>';
    }
    // modalites-accompagnement
    if(!empty($tab_donnee_bilan['modaccomp']))
    {
      $tab_xml_bilans['periodique'][] = '    <modalites-accompagnement>';
      foreach($tab_donnee_bilan['modaccomp'] as $code => $info_complement)
      {
        if( ($code!='PPRE') || !$info_complement )
        {
          $tab_xml_bilans['periodique'][] = '     <modalite-accompagnement code="'.$code.'" />';
        }
        else
        {
          $tab_xml_bilans['periodique'][] = '     <modalite-accompagnement code="'.$code.'"><complement-ppre>'.html($info_complement).'</complement-ppre></modalite-accompagnement>';
        }
      }
      $tab_xml_bilans['periodique'][] = '    </modalites-accompagnement>';
    }
    // acquis-conseils (2D) | appreciation-generale (1D)
    $tab_synthese = $tab_donnee_bilan['synthese'];
    $tab_xml_bilans['periodique'][] = '    <'.$key_appr_synthese.'>'.html($tab_synthese['appreciation']).'</'.$key_appr_synthese.'>';
    // vie-scolaire (2D)
    if(!empty($tab_donnee_bilan['viesco']))
    {
      $tab_viesco = $tab_donnee_bilan['viesco'];
      $tab_xml_bilans['periodique'][] = '    <vie-scolaire nb-retards="'.$tab_viesco['nb-retards'].'" nb-abs-justifiees="'.$tab_viesco['nb-abs-justifiees'].'" nb-abs-injustifiees="'.$tab_viesco['nb-abs-injustifiees'].'">'; // nb-heures-manquees non géré
      if(!empty($tab_viesco['appreciation']))
      {
        $tab_xml_bilans['periodique'][] = '     <commentaire>'.html($tab_viesco['appreciation']).'</commentaire>';
      }
      $tab_xml_bilans['periodique'][] = '    </vie-scolaire>';
    }
    // socle
    // --> pas géré pour l'instant
    // responsables
    if(!empty($tab_donnee_bilan['responsables']))
    {
      $tab_xml_bilans['periodique'] = array_merge( $tab_xml_bilans['periodique'] , xml_responsables($tab_donnee_bilan['responsables']) );
    }
    $tab_xml_bilans['periodique'][] = '   </bilan-periodique>';
  }
  // bilan de fin de cycle
  else if($tab_bilan['type']=='cycle')
  {
    $profs_eleve_refs = !empty($tab_bilan[$key_profs_eleve]) ? ' '.$key_profs_eleve.'="'.$tab_bilan[$key_profs_eleve].'"' : '' ;
    $tab_xml_bilans['cycle'][] = '   <bilan-cycle'.$profs_eleve_refs.' eleve-ref="'.$tab_bilan['eleve-ref'].'" cycle="'.$tab_bilan['cycle'].'" millesime="'.$tab_bilan['millesime'].'" date-creation="'.$tab_bilan['date-creation'].'" date-verrou="'.$tab_bilan['date-verrou'].'" '.$key_chef_etabl.'="'.$tab_bilan[$key_chef_etabl].'">';
    // domaines du socle
    $tab_xml_bilans['cycle'][] = '    <socle>';
    foreach($tab_donnee_bilan['socle'] as $id => $tab)
    {
      $tab_xml_bilans['cycle'][] = '     <domaine code="'.$tab['code'].'" positionnement="'.$tab['positionnement'].'" />';
    }
    $tab_xml_bilans['cycle'][] = '    </socle>';
    // synthèse
    $tab_synthese = $tab_donnee_bilan['synthese'];
    $tab_xml_bilans['cycle'][] = '    <synthese>'.html($tab_synthese['appreciation']).'</synthese>';
    // enseignement de complément (cycle 4 uniquement)
    if(!empty($tab_donnee_bilan['enscompl']))
    {
      $tab_enscompl = $tab_donnee_bilan['enscompl'];
      $positionnement = ($tab_enscompl['code']=='AUC') ? '' : ' positionnement="'.$tab_enscompl['positionnement'].'"' ;
      $tab_xml_bilans['cycle'][] = '    <enseignement-complement code="'.$tab_enscompl['code'].'"'.$positionnement.' />';
    }
    // responsables
    if(!empty($tab_donnee_bilan['responsables']))
    {
      $tab_xml_bilans['cycle'] = array_merge( $tab_xml_bilans['cycle'] , xml_responsables($tab_donnee_bilan['responsables']) );
    }
    $tab_xml_bilans['cycle'][] = '   </bilan-cycle>';
  }
  unset($tab_export_donnees[$key]);
}
// assemblage
if(!empty($tab_xml_bilans['periodique']))
{
  $tab_xml[] = '  <bilans-periodiques>';
  $tab_xml[] = implode(NL,$tab_xml_bilans['periodique']);
  $tab_xml[] = '  </bilans-periodiques>';
}
if(!empty($tab_xml_bilans['cycle']))
{
  $tab_xml[] = '  <bilans-cycle>';
  $tab_xml[] = implode(NL,$tab_xml_bilans['cycle']);
  $tab_xml[] = '  </bilans-cycle>';
}

// fermeture des balises
$tab_xml[] = ' </donnees>';
$tab_xml[] = '</lsun-bilans>';

// Le xml
$export_xml = implode(NL,$tab_xml);
if($rubrique_sans_element)
{
  $export_xml = str_replace( '<elements-programme>' , '<elements-programme>'.NL.'   <element-programme id="EL0" libelle="-" />' , $export_xml );
}

// Vérification XML valide

// @see http://php.net/manual/fr/domdocument.schemavalidate.php#62032
function libxml_display_error($error)
{
  $return = '<p>';
  switch ($error->level)
  {
    case LIBXML_ERR_WARNING:
      $return .= "<b>Warning $error->code</b> : ";
      break;
    case LIBXML_ERR_ERROR:
      $return .= "<b>Error $error->code</b> : ";
      break;
    case LIBXML_ERR_FATAL:
      $return .= "<b>Fatal Error $error->code</b> : ";
      break;
  }
  $return .= trim($error->message);
  /*
  if ($error->file) {
    $return .=    " in <b>$error->file</b>";
  }
  $return .= " on line <b>$error->line</b>\n";
  */
  $return .= '</p>'.NL;
  return $return;
}

libxml_use_internal_errors(TRUE);
$dom_xml= new DOMDocument();
$dom_xml->loadXML($export_xml);
if(!$dom_xml->schemaValidate(CHEMIN_DOSSIER_XSD.$tab_xml_param[$export_objet]['file_xsd']))
{
  $errors = libxml_get_errors();
  Json::add_str( '<p><label class="erreur">Fichier XML obtenu détecté invalide ; veuillez '.HtmlMail::to(SACOCHE_CONTACT_COURRIEL,'export LSU - fichier XML invalide','prendre contact avec l\'assistance de SACoche','Veuillez joindre une sauvegarde de votre base de données et préciser la classe ou l\'élève concerné.').'.</label></p>' );
  foreach ($errors as $error)
  {
    Json::add_str( libxml_display_error($error) );
  }
  libxml_clear_errors();
  // En PROD on ne donne pas ce fichier qui de toutes façons sera rejeté par LSU.
  // Hors PROD on laisse la possibilité de récupérer le fichier pour l'analyser.
  if(SERVEUR_TYPE == 'PROD')
  {
    Json::end( FALSE );
  }
}

// Enregistrement du fichier
$periode_nom = ($periode) ? $tab_periode_livret[$periode] : 'Toutes périodes' ;
$fichier_extension = 'xml';
$fichier_nom = 'import-lsun-'.Clean::fichier($_SESSION['WEBMESTRE_UAI'].'_'.$periode_nom.'_'.$groupe_nom).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$fichier_extension; // LSU recommande le modèle "import-lsun-{timestamp}.xml"
FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , $export_xml );
$fichier_lien = './force_download.php?fichier='.$fichier_nom;

// Retour
$sb = ($nb_bilans>1) ? 's' : '' ;
$se = ($nb_eleves>1) ? 's' : '' ;
Json::add_str('<p><label class="valide">Fichier d\'export généré : '.$nb_bilans.' bilan'.$sb.' concernant '.$nb_eleves.' élève'.$se.'.</label></p>'.NL);
Json::add_str('<p><a target="_blank" rel="noopener noreferrer" href="'.$fichier_lien.'"><span class="file file_'.$fichier_extension.'">Récupérer le fichier au format <em>'.$fichier_extension.'</em>.</span></a></p>'.NL);
Json::add_str('<p><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></p>'.NL);
Json::end( TRUE );

?>
