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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent essentiellement que la table "sacoche_officiel_configuration".

class DB_STRUCTURE_OFFICIEL_CONFIG extends DB
{

/**
 * DB_initialiser_configuration
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @return array
 */
public static function DB_initialiser_configuration( $officiel_type )
{
  // Valeurs par défaut
  if($officiel_type=='bulletin')
  {
    $tab_init = array(
      'acquis_texte_code'              => 1,
      'acquis_texte_nombre'            => 1,
      'appreciation_generale_longueur' => 400,
      'appreciation_generale_report'   => 0,
      'appreciation_generale_modele'   => '',
      'appreciation_rubrique_longueur' => 400,
      'appreciation_rubrique_report'   => 0,
      'appreciation_rubrique_modele'   => '',
      'assiduite'                      => 0,
      'barre_acquisitions'             => 1,
      'conversion_sur_20'              => 1,
      'couleur'                        => 'oui',
      'envoi_mail_parent'              => 0,
      'fond'                           => 'gris',
      'fusion_niveaux'                 => 1,
      'legende'                        => 'oui',
      'ligne_supplementaire'           => '',
      'moyenne_classe'                 => 1,
      'moyenne_exception_matieres'     => '',
      'moyenne_generale'               => 0,
      'moyenne_scores'                 => 1,
      'only_socle'                     => 0,
      'prof_principal'                 => 0,
      'retroactif'                     => 'non',
    );
  }
  else if($officiel_type=='livret')
  {
    $tab_init = array(
      'appreciation_generale_longueur'   => 400,
      'appreciation_rubrique_longueur'   => 400,
      'couleur'                          => 'oui',
      'envoi_mail_parent'                => 0,
      'fond'                             => 'gris',
      'import_bulletin_notes'            => 'oui',
      'only_socle'                       => 0,
      'retroactif'                       => 'non',
    );
  }
  else if($officiel_type=='releve')
  {
    $tab_init = array(
      'aff_coef'                         => 0,
      'aff_domaine'                      => 0,
      'aff_reference'                    => 1,
      'aff_socle'                        => 1,
      'aff_theme'                        => 0,
      'appreciation_generale_longueur'   => 400,
      'appreciation_generale_report'     => 0,
      'appreciation_generale_modele'     => '',
      'appreciation_rubrique_longueur'   => 400,
      'appreciation_rubrique_report'     => 0,
      'appreciation_rubrique_modele'     => '',
      'assiduite'                        => 0,
      'cases_auto'                       => 1,
      'cases_largeur'                    => 5,
      'cases_nb'                         => 4,
      'conversion_sur_20'                => 0,
      'couleur'                          => 'oui',
      'envoi_mail_parent'                => 0,
      'fond'                             => 'gris',
      'etat_acquisition'                 => 1,
      'legende'                          => 'oui',
      'ligne_supplementaire'             => '',
      'moyenne_scores'                   => 1,
      'only_etat'                        => 'tous',
      'only_socle'                       => 0,
      'pages_nb'                         => 'optimise',
      'pourcentage_acquis'               => 1,
      'prof_principal'                   => 0,
      'retroactif'                       => 'non',
    );
  }
  // On insère le json dans la base
  $DB_SQL = 'UPDATE sacoche_officiel_configuration ';
  $DB_SQL.= 'SET configuration_contenu=:configuration_contenu ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
  $DB_VAR = array(
    ':officiel_type'         => $officiel_type,
    ':configuration_ref'     => 'defaut',
    ':configuration_contenu' => json_encode($tab_init),
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Et on retourne le tableau
  return $tab_init;
}

/**
 * DB_recuperer_configuration
 *
 * @param string $officiel_type
 * @param string $configuration_ref
 * @return array
 */
public static function DB_recuperer_configuration( $officiel_type , $configuration_ref )
{
  $DB_SQL = 'SELECT configuration_contenu ';
  $DB_SQL.= 'FROM sacoche_officiel_configuration ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
  $DB_VAR = array(
    ':officiel_type'     => $officiel_type,
    ':configuration_ref' => $configuration_ref,
  );
  $configuration_json = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(!$configuration_json)
  {
    return DB_STRUCTURE_OFFICIEL_CONFIG::DB_initialiser_configuration( $officiel_type );
  }
  else
  {
    return json_decode($configuration_json, TRUE);
  }
}

/**
 * DB_recuperer_classe_config_ref
 *
 * @param int    $groupe_id
 * @param string $bilan_type
 * @return string
 */
public static function DB_recuperer_classe_config_ref( $groupe_id , $bilan_type )
{
  $DB_SQL = 'SELECT groupe_configuration_'.$bilan_type.' AS configuration_ref ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(':groupe_id'=>$groupe_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);;
}

/**
 * DB_lister_classes_avec_configurations
 *
 * @param void
 * @return array
 */
public static function DB_lister_classes_avec_configurations()
{
  $DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_configuration_releve, groupe_configuration_bulletin, groupe_configuration_livret ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type'=>'classe');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_configurations
 *
 * @param void
 * @return array
 */
public static function DB_lister_configurations()
{
  $DB_SQL = 'SELECT officiel_type, configuration_ref, configuration_nom ';
  $DB_SQL.= 'FROM sacoche_officiel_configuration ';
  $DB_SQL.= 'ORDER BY officiel_type ASC, configuration_ref ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);;
}

/**
 * DB_tester_reference
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @param string $configuration_ref
 * @return int
 */
public static function DB_tester_reference( $officiel_type , $configuration_ref )
{
  $DB_SQL = 'SELECT configuration_ref ';
  $DB_SQL.= 'FROM sacoche_officiel_configuration ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
  $DB_VAR = array(
    ':officiel_type'     => $officiel_type,
    ':configuration_ref' => $configuration_ref,
  );
  DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_tester_nom
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @param string $configuration_nom
 * @param string $configuration_ref   inutile si recherche pour un ajout, mais ref à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_nom( $officiel_type , $configuration_nom , $configuration_ref=FALSE )
{
  $DB_SQL = 'SELECT configuration_ref ';
  $DB_SQL.= 'FROM sacoche_officiel_configuration ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_nom=:configuration_nom ';
  $DB_SQL.= ($configuration_ref) ? 'AND configuration_ref!=:configuration_ref ' : '' ;
  $DB_VAR = array(
    ':officiel_type'     => $officiel_type,
    ':configuration_nom' => $configuration_nom,
    ':configuration_ref' => $configuration_ref,
  );
  DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * DB_ajouter_configuration
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @param string $configuration_ref
 * @param string $configuration_nom
 * @param array  $tab_contenu
 * @return void
 */
public static function DB_ajouter_configuration( $officiel_type , $configuration_ref , $configuration_nom , $tab_contenu )
{
  $DB_SQL = 'INSERT INTO sacoche_officiel_configuration( officiel_type, configuration_ref, configuration_nom, configuration_contenu) ';
  $DB_SQL.= 'VALUES                                    (:officiel_type,:configuration_ref,:configuration_nom,:configuration_contenu) ';
  $DB_VAR = array(
    ':officiel_type'         => $officiel_type,
    ':configuration_ref'     => $configuration_ref,
    ':configuration_nom'     => $configuration_nom,
    ':configuration_contenu' => json_encode($tab_contenu),
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_configuration
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @param string $configuration_ref
 * @param string $configuration_nom
 * @param array  $tab_contenu
 * @return void
 */
public static function DB_modifier_configuration( $officiel_type , $configuration_ref , $configuration_nom , $tab_contenu )
{
  $DB_SQL = 'UPDATE sacoche_officiel_configuration ';
  $DB_SQL.= 'SET configuration_nom=:configuration_nom, configuration_contenu=:configuration_contenu ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
  $DB_VAR = array(
    ':officiel_type'         => $officiel_type,
    ':configuration_ref'     => $configuration_ref,
    ':configuration_nom'     => $configuration_nom,
    ':configuration_contenu' => json_encode($tab_contenu),
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_configuration
 *
 * @param string $officiel_type   bulletin | livret | releve
 * @param string $configuration_ref
 * @return void
 */
public static function DB_supprimer_configuration( $officiel_type , $configuration_ref )
{
  $DB_SQL = 'DELETE FROM sacoche_officiel_configuration ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND configuration_ref=:configuration_ref ';
  $DB_VAR = array(
    ':officiel_type'     => $officiel_type,
    ':configuration_ref' => $configuration_ref,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // On applique la configuration par défaut aux classes concernées
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_configuration_'.$officiel_type.'="defaut" ';
  $DB_SQL.= 'WHERE groupe_configuration_'.$officiel_type.'=:configuration_ref ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_classe_config_ref
 *
 * @param int    $groupe_id
 * @param string $bilan_type
 * @param string $reference
 * @return void
 */
public static function DB_modifier_classe_config_ref( $groupe_id , $bilan_type , $reference )
{
  $DB_SQL = 'UPDATE sacoche_groupe ';
  $DB_SQL.= 'SET groupe_configuration_'.$bilan_type.'=:reference ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(
    ':reference' => $reference,
    ':groupe_id' => $groupe_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>