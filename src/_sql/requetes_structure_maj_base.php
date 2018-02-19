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
// Ces méthodes servent à mettre à jour la base.

// Ce script est appelé automatiquement si besoin lorsque :
// - un administrateur vient de restaurer une base
// - un utilisateur vient de se connecter

// La méthode DB_version_base(), déjà définie dans la classe DB_STRUCTURE_PUBLIC, est redéfinie ici.
// Elle est invoquée systématiquement à chaque étape, au cas où des mises à jour simultanées seraient lancées (c'est déjà arrivé) malgré les précautions prises (fichier de blocage).

class DB_STRUCTURE_MAJ_BASE extends DB
{

  /**
   * Retourner la version de la base de l'établissement
   *
   * @param void
   * @return string
   */
  public static function DB_version_base()
  {
    $DB_SQL = 'SELECT parametre_valeur ';
    $DB_SQL.= 'FROM sacoche_parametre ';
    $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
    $DB_VAR = array(':parametre_nom'=>'version_base');
    return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }

  /**
   * Retourner la version de la mise à jour complémentaire requise de l'établissement
   *
   * @param void
   * @return string
   */
  public static function DB_version_base_maj_complementaire()
  {
    $DB_SQL = 'SELECT parametre_valeur ';
    $DB_SQL.= 'FROM sacoche_parametre ';
    $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
    $DB_VAR = array(':parametre_nom'=>'version_base_maj_complementaire');
    return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }


  /**
   * Mettre à jour la base de l'établissement
   *
   * @param string   $version_base_structure_actuelle
   * @return void
   */
  public static function DB_maj_base($version_base_structure_actuelle)
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // On s'arrête si c'est un pb de fichier non récupéré ou de base inaccessible
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( !VERSION_BASE_STRUCTURE || !$version_base_structure_actuelle )
    {
      Cookie::effacer(COOKIE_STRUCTURE);
      $message = (!VERSION_BASE_STRUCTURE) ? 'Fichier avec version de la base manquant.' : 'Base de données inaccessible (valeur sacoche_parametre.version_base non récupérée).' ;
      exit_error( 'Erreur MAJ BDD' /*titre*/ , $message /*contenu*/ );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Le fichier commençant à devenir volumineux, les mises à jour ont été archivées par années dans des fichiers séparés.
    // Lors d'un changement d'année n -> n+1, la mise à jour s'effectue dans le fichier n+1 (les 2 sont en fait possible, mais cela évite d'oublier sa création).
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $annee_version_actuelle = (int)substr($version_base_structure_actuelle,0,4);
    $annee_version_derniere = (int)substr(VERSION_BASE_STRUCTURE,0,4);

    for( $annee=$annee_version_actuelle ; $annee<=$annee_version_derniere ; $annee++ )
    {
      require(CHEMIN_DOSSIER_SQL.'requetes_structure_maj_base_'.$annee.'.inc.php');
    }

  }


  /**
   * Mise à jour complémentaire de la base par morceaux pour éviter un dépassement du temps d'exécution alloué au script
   *
   * @param void
   * @return void
   */
  public static function DB_maj_base_complement()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16a
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16a';
    $version_to   = '2015-10-16b';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        // Les 4 premières requêtes sont à cause des serveurs en mode STRICT qui sinon recrachent "#1406 - Data too long for column saisie_note" si on veut directement convertir en CHAR(2)
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'].'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_note saisie_note enum("VV","V","R","RR","ABS","DISP","NE","NF","NN","NR","REQ","AB","DI","PA") COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16b
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16b';
    $version_to   = '2015-10-16c';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        // Il ne sert à rien de compter le nombre de modifs à effectuer puis d'ordonner un LIMIT à l'UPDATE en conséquence pour le faire par morceaux
        // car ce n'est pas la modification qui prend du temps, c'est le parcours de la table.
        // Ainsi avec un LIMIT les premières modifs iront très vite car des lignes seront trouvées rapidement,
        // mais le dernier remplacement dépasse les 10s même s'il n'y a plus de ligne à modifier à cause du temps de parcours de la table.
        // Quand à ajouter un INDEX provisoire sur le champ "saisie_note", cela prend 20s,
        // donc même si après les UPDATE vont + vite on est bloqué avant.
        // Du coup on lance un UPDATE sans LIMIT ; en cas de dépassement du max_execution_time, l'erreur PHP ne sera pas visible (requête AJAX)
        // et la requête SQL sera quand même exécutée en entier, ce qui est le plus important.
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'].'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="AB"  WHERE saisie_note="ABS" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16c
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16c';
    $version_to   = '2015-10-16d';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'].'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="DI"  WHERE saisie_note="DISP" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16d
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16d';
    $version_to   = '2015-10-16e';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'].'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="PA"  WHERE saisie_note="REQ" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16e
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16e';
    $version_to   = '2015-10-16f';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'].'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_note saisie_note CHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16f
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16f';
    $version_to   = '2015-10-16g';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = '2015-10-16f';
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="1" WHERE saisie_note="RR" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16g
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16g';
    $version_to   = '2015-10-16h';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="2" WHERE saisie_note="R" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16h
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16h';
    $version_to   = '2015-10-16i';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="3" WHERE saisie_note="V" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2015-10-16i
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2015-10-16i';
    $version_to   = '2016-02-27a'; // à modifier si autre traitement ultérieur

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_saisie SET saisie_note="4" WHERE saisie_note="VV" ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2016-02-27a
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2016-02-27a';
    $version_to   = '2016-02-27b';

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_date saisie_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement 2016-02-27b
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $version_from = '2016-02-27b';
    $version_to   = ''; // à modifier si autre traitement ultérieur

    if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==$version_from)
    {
      if($_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE']==DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire())
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_to.'" WHERE parametre_nom="version_base_maj_complementaire"' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_visible_date saisie_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
        return;
      }
      else
      {
        $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = $version_to;
      }
    }

  }

}
?>
