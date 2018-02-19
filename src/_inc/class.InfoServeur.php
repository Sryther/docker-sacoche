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

class InfoServeur
{

  // //////////////////////////////////////////////////
  // Attributs
  // //////////////////////////////////////////////////

  private static $SACoche_version_dispo = NULL;

  private static $tab_style = array(
    'vert' =>'bv' ,
    'jaune'=>'bj' ,
    'rouge'=>'br' ,
  );

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) de bas niveau
  // //////////////////////////////////////////////////

  /**
   * info_base_complement
   *
   * @param string $type_base
   * @return string
   */
  private static function info_base_complement($type_base)
  {
    if($type_base=='structure')
    {
      return (HEBERGEUR_INSTALLATION=='multi-structures') ? 'La valeur dépend de chaque structure&hellip;<br />' : '' ;
    }
    if($type_base=='webmestre')
    {
      return (HEBERGEUR_INSTALLATION=='mono-structure') ? 'Sans objet pour une installation de type mono-structure.<br />' : '' ;
    }
  }

  /**
   * SACoche_version_dispo
   * Retourne la dernière version disponible de SACoche, si ce n'est pas déjà fait.
   *
   * @param string $contenu
   * @param string $couleur   vert|jaune|rouge
   * @return string
   */
  private static function SACoche_version_dispo()
  {
    return (InfoServeur::$SACoche_version_dispo!==NULL) ? InfoServeur::$SACoche_version_dispo : Outil::recuperer_numero_derniere_version() ;
  }

  /**
   * commentaire
   *
   * @param string $sujet
   * @return string
   */
  private static function commentaire($sujet)
  {
    switch($sujet)
    {
      case 'version_php'                    : return "Version ".PHP_VERSION_MINI_REQUISE." ou ultérieure requise.<br \>Version ".PHP_VERSION_MINI_CONSEILLEE." ou ultérieure conseillée.<br \>PHP 5.1 n'est plus supporté depuis le 24/08/2006.<br />PHP 5.2 n'est plus supporté depuis le 06/01/2011.<br \>PHP 5.3 n'est plus supporté depuis le 14/08/2014.<br \>PHP 5.4 et PHP 5.5 ne reçoivent plus que des correctifs de sécurité.";
      /*
      PHP 5.1 (24/11/2005) n'est plus supporté depuis le 24/08/2006.            @see http://www.php.net/releases/ & http://php.net/eol.php
      PHP 5.2 (02/11/2006) n'est plus supporté depuis le 06/01/2011.            @see http://www.php.net/releases/ & http://php.net/eol.php
      PHP 5.3 (30/06/2009) n'est plus supporté depuis le 14/08/2014.            @see http://www.php.net/releases/ & http://php.net/eol.php
      PHP 5.4 (01/03/2012) ne reçoit plus que des correctifs de sécurité.       @see http://www.php.net/releases/ & https://fr.wikipedia.org/wiki/PHP
      PHP 5.5 (20/06/2013) ne reçoit plus que des correctifs de sécurité.       @see http://www.php.net/releases/ & https://fr.wikipedia.org/wiki/PHP
      PHP 5.6 (28/08/2014) et PHP 7.0 (03/12/2015) sont les versions actuelles. @see http://www.php.net/releases/ & https://fr.wikipedia.org/wiki/PHP
      */
      case 'version_mysql'                  : return "Version ".MYSQL_VERSION_MINI_REQUISE." ou ultérieure requise.<br \>Version ".MYSQL_VERSION_MINI_CONSEILLEE." ou ultérieure conseillée.<br \>MySQL 5.0 est stable depuis le 15/04/2008 (5.0.19).<br \>MySQL 5.1 est stable depuis le 30/06/2009 (5.1.16).<br \>MySQL 5.2 est stable depuis le 30/06/2010 (5.2.25).<br \>MySQL 5.5 est stable depuis le 03/12/2010 (5.5.8).<br \>MySQL 5.6 est stable depuis le 05/02/2013 (5.6.10).<br \>MySQL 5.7 est stable depuis le 21/10/2015 (5.7.9).";
      /*
      Le support de MySQL dépend des systèmes.            @see https://www.mysql.com/support/supportedplatforms/database.html
      MySQL 5.0 est stable depuis le 15/04/2008 (5.0.19). @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-5-0.html
      MySQL 5.1 est stable depuis le 30/06/2009 (5.1.16). @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-5-1.html
      MySQL 5.2 est stable depuis le 30/06/2010 (5.2.25). @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-5-2.html
      MySQL 5.5 est stable depuis le 03/12/2010 (5.5.8).  @see https://dev.mysql.com/doc/relnotes/mysql/5.5/en/
      MySQL 5.6 est stable depuis le 05/02/2013 (5.6.10). @see https://dev.mysql.com/doc/relnotes/mysql/5.6/en/
      MySQL 5.7 est stable depuis le 21/10/2015 (5.7.9).  @see https://dev.mysql.com/doc/relnotes/mysql/5.7/en/
      MySQL 6.0 est stable depuis le 12/08/2013 (6.0.6).  @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-6-0.html
      MySQL 6.1 est stable depuis le 31/03/2014 (6.1.4).  @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-6-1.html
      MySQL 6.2 est stable depuis le 23/09/2014 (6.2.3).  @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-6-2.html
      MySQL 6.3 est stable depuis le 23/04/2015 (6.3.3).  @see https://dev.mysql.com/doc/relnotes/workbench/en/changes-6-3.html
      */
      case 'version_sacoche_prog'           : return "Dernière version disponible : ".InfoServeur::SACoche_version_dispo();
      case 'version_sacoche_base_structure' : return InfoServeur::info_base_complement('structure')."Version attendue : ".VERSION_BASE_STRUCTURE;
      case 'version_sacoche_base_webmestre' : return InfoServeur::info_base_complement('webmestre')."Version attendue : ".VERSION_BASE_WEBMESTRE;
      case 'max_execution_time'             : return "Par défaut 30 secondes.<br />Une valeur trop faible peut gêner les sauvegardes / restaurations de grosses bases ou des générations de bilans PDF.";
      case 'max_input_vars'                 : return "Par défaut 1000.<br />Une valeur inférieure est susceptible de tronquer la transmission de formulaires importants.<br \>Disponible à compter de PHP 5.3.9 uniquement.";
      case 'max_input_time'                 : return "Par défaut -1 (pas de limitation).<br />Disponible à compter de PHP 4.3.0 uniquement.";
      case 'max_input_nesting_level'        : return "Par défaut 64.<br />Disponible à compter de PHP 4.4.8 et PHP 5.2.3 uniquement.";
      case 'memory_limit'                   : return "Par défaut 128Mo (convient très bien).<br />Doit être plus grand que post_max_size (ci-dessous).<br />Une valeur inférieure à 128Mo peut poser problème (pour générer des bilans PDF en particulier).<br />Mais 64Mo voire 32Mo peuvent aussi convenir, tout dépend de l'usage (nombre d'élèves considérés à la fois, quantité de données&hellip;).";
      case 'post_max_size'                  : return "Par défaut 8Mo.<br />Doit être plus grand que upload_max_filesize (ci-dessous).";
      case 'upload_max_filesize'            : return "Par défaut 2Mo.<br />A augmenter si on doit envoyer un fichier d'une taille supérieure.";
      case 'sql_mode'                       : return "Un mode comportant TRADITIONAL ou STRICT_TRANS_TABLES ou STRICT_ALL_TABLES induit un mode SQL strict.";
      case 'max_allowed_packet'             : return "Par défaut 1Mo (1 048 576 octets).<br />Mais 4Mo minimum recommandés (davantage pour être tranquille) afin d'éviter tout problème (archivage des imports SIECLE).<br />Pour restaurer une sauvegarde, les fichiers contenus dans le zip ne doivent pas dépasser cette taille.";
      case 'max_user_connections'           : return "Une valeur inférieure à 5 est susceptible, suivant la charge, de poser problème.";
      case 'group_concat_max_len'           : return "Par défaut 1024 octets.<br />Une telle valeur devrait suffire.";
      case 'safe_mode'                      : return "Fonctionnalité obsolète depuis PHP 5.3.0, à ne plus utiliser.<br />Son activation peut poser problème (pour échanger avec le serveur communautaire).";
      case 'open_basedir'                   : return "Limite les fichiers pouvant être ouverts par PHP à une architecture de dossiers spécifique.<br />Son activation peut poser problème (pour échanger avec le serveur communautaire).";
      case 'ini_set_memory_limit'           : return "Possibilité d'augmenter la mémoire allouée au script.";
      case 'register_globals'               : return "Enregistrer les variables environnement Get/Post/Cookie/Server comme des variables globales.<br />Par défaut désactivé depuis PHP 4.2.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.";
      case 'magic_quotes_gpc'               : return "Échapper les apostrophes pour Get/Post/Cookie.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.";
      case 'magic_quotes_sybase'            : return "Échapper les apostrophes pour Get/Post/Cookie.<br />Remplace la directive magic_quotes_gpc en cas d'activation.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.";
      case 'magic_quotes_runtime'           : return "Échapper les apostrophes pour toutes les données externes, y compris les bases de données et les fichiers texte.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.";
      case 'session_gc_maxlifetime'         : return "Durée de vie des données (session) sur le serveur, en nombre de secondes.<br />Par défaut 1440s soit 24min.<br />SACoche permet de régler une conservation de session plus longue.<br />Mais cela ne fonctionnera que si le serveur est configuré pour une durée minimum de 10min.";
      case 'session_use_trans_sid'          : return "Par défaut désactivé, ce qui rend le support de l'identifiant de session transparent.<br />C'est une protection contre les attaques qui utilisent des identifiants de sessions dans les URL.<br />La configuration session.use_trans_sid=ON couplée à session.use_only_cookies=OFF engendre des dysfonctionnements.";
      case 'session_use_only_cookies'       : return "Par défaut activé, ce qui indique d'utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.<br />C'est une protection contre les attaques qui utilisent des identifiants de sessions dans les URL.<br />La configuration session.use_trans_sid=ON couplée à session.use_only_cookies=OFF engendre des dysfonctionnements.";
      case 'zend_ze1_compatibility_mode'    : return "Activer le mode de compatibilité avec le Zend Engine 1 (PHP 4).<br />C'est incompatible avec classe PDO, et l'utilisation de simplexml_load_string() ou DOMDocument (par exemples) provoquent des erreurs fatales.<br />Fonctionnalité obsolète et supprimée depuis PHP 5.3.";
      case 'server_protocole'               : return "Variable serveur indiquant le protocole ; on regarde dans l'ordre :<br />- HTTPS si définie correctement<br />- HTTP_X_FORWARDED_PROTO si définie correctement<br />- c'est HTTP sinon";
      case 'server_IP_client'               : return "IP cliente présentée au serveur ; on regarde dans l'ordre :<br />- HTTP_X_REAL_IP si définie<br />- HTTP_X_FORWARDED_FOR si définie<br />- REMOTE_ADDR sinon";
      case 'space_disk_total'               : return "Sur le système de fichiers ou la partition.";
      case 'space_disk_free'                : return "Sur le système de fichiers ou la partition.";
      case 'modules_PHP'                    : return "Les modules sur fond coloré sont requis par SACoche.<br />Cliquer sur un module pour consulter le détail des informations.";
      case 'suhosin'                        : return "Module retiré à compter de PHP 5.4 (PHP prenant nativement en charge la plupart des fonctionnalités).";
      default                               : return "";
    }
  }

  /**
   * cellule_coloree_centree
   * Retourne une chaine de la forme '<td class="hc ...">...</td>'
   *
   * @param string $contenu
   * @param string $couleur   vert|jaune|rouge
   * @return string
   */
  private static function cellule_coloree_centree($contenu,$couleur)
  {
    return '<td class="hc '.InfoServeur::$tab_style[$couleur].'">'.$contenu.'</td>';
  }

  /**
   * cellule_centree
   * Retourne une chaine de la forme '<td class="hc">...</td>'
   *
   * @param string $contenu
   * @return string
   */
  private static function cellule_centree($contenu)
  {
    return '<td class="hc">'.$contenu.'</td>';
  }

  /**
   * tableau_deux_colonnes
   *
   * @param string $titre
   * @param string $tab_objets   $nom_objet => $nom_affichage
   * @return string
   */
  private static function tableau_deux_colonnes($titre,$tab_objets)
  {
    $tab_tr = array();
    foreach($tab_objets as $nom_objet => $nom_affichage)
    {
      $cellule  = (version_compare(PHP_VERSION,'5.2.3','>=')) ? call_user_func('InfoServeur::'.$nom_objet) : call_user_func( array('InfoServeur',$nom_objet) ) ;
      $tab_tr[] = '<tr><td><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.InfoServeur::commentaire($nom_objet).'" /> '.$nom_affichage.'</td>'.$cellule.'</tr>';
    }
    return'<table class="p"><thead><tr><th colspan="2">'.$titre.'</th></tr></thead><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) intermédiaires
  // //////////////////////////////////////////////////

  /**
   * version_php
   * Retourne le numéro de la version courante de PHP.
   * La vérification de la version de PHP est effectuée à chaque appel de SACoche.
   * Voir http://fr.php.net/phpversion
   *
   * @param void
   * @return string
   */
  private static function version_php()
  {
    if(version_compare(PHP_VERSION,PHP_VERSION_MINI_CONSEILLEE,'>=')) return InfoServeur::cellule_coloree_centree(PHP_VERSION,'vert');
    if(version_compare(PHP_VERSION,PHP_VERSION_MINI_REQUISE   ,'>=')) return InfoServeur::cellule_coloree_centree(PHP_VERSION,'jaune');
                                                                      return InfoServeur::cellule_coloree_centree(PHP_VERSION,'rouge');
  }

  /**
   * version_mysql
   * Retourne une chaîne indiquant la version courante du serveur MySQL.
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/information-functions.html
   *
   * @param void
   * @return string
   */
  private static function version_mysql()
  {
    $mysql_version = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_version_MySQL() : DB_WEBMESTRE_PUBLIC::DB_recuperer_version_MySQL() ;
    if(version_compare($mysql_version,MYSQL_VERSION_MINI_CONSEILLEE,'>=')) return InfoServeur::cellule_coloree_centree($mysql_version,'vert');
    if(version_compare($mysql_version,MYSQL_VERSION_MINI_REQUISE   ,'>=')) return InfoServeur::cellule_coloree_centree($mysql_version,'jaune');
                                                                           return InfoServeur::cellule_coloree_centree($mysql_version,'rouge');
  }

  /**
   * version_sacoche_prog
   * Retourne une chaîne indiquant la version logicielle des fichiers de SACoche.
   *
   * @param void
   * @return string
   */
  private static function version_sacoche_prog()
  {
    if(VERSION_PROG==InfoServeur::SACoche_version_dispo())
    {
      $couleur = 'vert';
    }
    else
    {
      $tab_version_installee  = explode('-',VERSION_PROG);
      $tab_version_disponible = explode('-',InfoServeur::SACoche_version_dispo());
      if(count($tab_version_disponible)==3)
      {
        $date_unix_version_installee  = mktime( 0 , 0 , 0 , (int)$tab_version_installee[1]  , (int)$tab_version_installee[2]  , (int)$tab_version_installee[0]  );
        $date_unix_version_disponible = mktime( 0 , 0 , 0 , (int)$tab_version_disponible[1] , (int)$tab_version_disponible[2] , (int)$tab_version_disponible[0] );
        $nb_jours_ecart = ( $date_unix_version_disponible - $date_unix_version_installee ) / ( 60 * 60 * 24 ) ;
        $couleur = ($nb_jours_ecart<90) ? 'jaune' : 'rouge' ;
      }
      else
      {
        // Dernière version non détectée…
        $couleur = 'rouge' ;
      }
    }
    return InfoServeur::cellule_coloree_centree(VERSION_PROG,$couleur);
  }

  /**
   * version_sacoche_base_structure
   * Retourne une chaîne indiquant la version logicielle de la base de données de SACoche.
   * En mode multi-structures, celle-ci est propre à chaque établissement.
   *
   * @param void
   * @return string   AAAA-MM-JJ
   */
  private static function version_sacoche_base_structure()
  {
    $version_base = (HEBERGEUR_INSTALLATION=='mono-structure') ? DB_STRUCTURE_MAJ_BASE::DB_version_base() : NULL ;
    if(HEBERGEUR_INSTALLATION=='multi-structures')                return InfoServeur::cellule_coloree_centree('variable'    ,'jaune');
    if(version_compare($version_base,VERSION_BASE_STRUCTURE,'=')) return InfoServeur::cellule_coloree_centree($version_base ,'vert' );
                                                                  return InfoServeur::cellule_coloree_centree($version_base ,'rouge');
  }

  /**
   * version_sacoche_base_webmestre
   * Retourne une chaîne indiquant la version logicielle de la base de données de SACoche.
   * En mode multi-structures, celle-ci est propre à chaque établissement.
   *
   * @param void
   * @return string   AAAA-MM-JJ
   */
  private static function version_sacoche_base_webmestre()
  {
    $version_base = (HEBERGEUR_INSTALLATION=='multi-structures') ? DB_WEBMESTRE_MAJ_BASE::DB_version_base() : NULL ;
    if(HEBERGEUR_INSTALLATION=='mono-structure')                  return InfoServeur::cellule_coloree_centree('sans objet'  ,'jaune');
    if(version_compare($version_base,VERSION_BASE_WEBMESTRE,'=')) return InfoServeur::cellule_coloree_centree($version_base ,'vert' );
                                                                  return InfoServeur::cellule_coloree_centree($version_base ,'rouge');
  }

  /**
   * max_execution_time
   * Cela permet d'éviter que des scripts en boucles infinies saturent le serveur.
   * Lorsque PHP fonctionne depuis la ligne de commande, la valeur par défaut est 0.
   * Voir http://fr.php.net/manual/fr/info.configuration.php#ini.max-execution-time
   *
   * @param void
   * @return string
   */
  private static function max_execution_time()
  {
    $val = ini_get('max_execution_time');
    if( (!$val) || ($val>=30) ) { $couleur = 'vert';  }
    elseif($val>=15)            { $couleur = 'jaune'; }
    else                        { $couleur = 'rouge'; }
    $val = ($val) ? $val.'s' : '<b>&infin;</b>' ;
    return InfoServeur::cellule_coloree_centree( $val , $couleur );
  }

  /**
   * memory_limit
   * Cette option détermine la mémoire limite, en octets, qu'un script est autorisé à allouer.
   * Cela permet de prévenir l'utilisation de toute la mémoire par un script mal codé.
   * Notez que pour n'avoir aucune limite, vous devez définir cette directive à -1.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.memory-limit
   *
   * @param void
   * @return string
   */
  private static function memory_limit()
  {
    $val = ini_get('memory_limit');
    if( ($val==-1) || ($val>=128) ) { $couleur = 'vert';  }
    elseif($val>=64)                { $couleur = 'jaune'; }
    else                            { $couleur = 'rouge'; }
    $val = ($val!=-1) ? $val : '<b>&infin;</b>' ;
    return InfoServeur::cellule_coloree_centree( $val , $couleur );
  }

  /**
   * post_max_size
   * Définit la taille maximale (en octets) des données reçues par la méthode POST.
   * Cette option affecte également les fichiers chargés.
   * Pour charger de gros fichiers, cette valeur doit être plus grande que la valeur de upload_max_filesize.
   * Si la limitation de mémoire est activée par votre script de configuration, memory_limit affectera également les fichiers chargés.
   * De façon générale, memory_limit doit être plus grand que post_max_size.
   * Voir http://fr.php.net/manual/fr/ini.core.php#post_max_size
   *
   * @param void
   * @return string
   */
  private static function post_max_size()
  {
    return InfoServeur::cellule_centree( ini_get('post_max_size') );
  }

  /**
   * upload_max_filesize
   * La taille maximale en octets d'un fichier à charger.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.upload-max-filesize
   *
   * @param void
   * @return string
   */
  private static function upload_max_filesize()
  {
    return InfoServeur::cellule_centree( ini_get('upload_max_filesize') );
  }

  /**
   * max_input_vars
   * Limite le nombre de variables transmises (POST ou GET).
   * Information à recouper avec des limites Suhosin éventuelles.
   * Disponible depuis PHP 5.3.9.
   * Voir http://www.php.net/manual/fr/info.configuration.php#ini.max-input-vars
   *
   * @param void
   * @return string
   */
  private static function max_input_vars()
  {
    $val = (version_compare(phpversion(),'5.3.9','>=')) ? ini_get('max_input_vars') : '---' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * max_input_time
   * Cette option spécifie la durée maximale pour analyser les données d'entrée, via POST et GET.
   * Cette durée est mesurée depuis le moment où toutes les données sont reçues du serveur jusqu'à début de l'exécution du script.
   * Disponible depuis PHP 4.3.0.
   * Voir http://www.php.net/manual/fr/info.configuration.php#ini.max-input-time
   *
   * @param void
   * @return string
   */
  private static function max_input_time()
  {
    $val = (version_compare(phpversion(),'4.3','>=')) ? ini_get('max_input_time') : '---' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * max_input_nesting_level
   * Définit la profondeur maximale des variables d'entrées (i.e. $_GET, $_POST..).
   * Disponible depuis PHP 4.4.8 et PHP 5.2.3.
   * Voir http://www.php.net/manual/fr/info.configuration.php#ini.max-input-nesting-level
   *
   * @param void
   * @return string
   */
  private static function max_input_nesting_level()
  {
    $val = ( (version_compare(phpversion(),'4.4.8','>=')) && (!version_compare(phpversion(),'5.2.3','<')) ) ? ini_get('max_input_nesting_level') : '---' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * max_allowed_packet
   * La taille maximale d'un paquet envoyé à MySQL.
   * Quand on fait un INSERT multiple, il ne faut pas balancer trop d'enregistrements car si la chaîne dépasse cette limitation alors la requête ne passe pas.
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   *
   * @param void
   * @return string
   */
  private static function max_allowed_packet()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('max_allowed_packet') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('max_allowed_packet') ;
    $val = $DB_ROW['Value'];
    if    ($val< 2097152) { $couleur = 'rouge'; } // 2Mo
    elseif($val<=4194304) { $couleur = 'jaune'; } // 4Mo
    else                  { $couleur = 'vert';  }
    return InfoServeur::cellule_coloree_centree( FileSystem::afficher_fichier_taille($val) , $couleur );
  }

  /**
   * max_user_connections
   * Le nombre maximum de connexions actives à MySQL pour un utilisateur particulier (0 = pas de limite).
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   *
   * @param void
   * @return string
   */
  private static function max_user_connections()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('max_user_connections') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('max_user_connections') ;
    $val = ($DB_ROW['Value']) ? $DB_ROW['Value'] : '<b>&infin;</b>' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * group_concat_max_len
   * La taille maximale de la chaîne résultat de GROUP_CONCAT().
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   * Pour lever cette limitation on peut effectuer la pré-requête DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...');
   *
   * @param void
   * @return string
   */
  private static function group_concat_max_len()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('group_concat_max_len') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('group_concat_max_len') ;
    $val = $DB_ROW['Value'];
    return InfoServeur::cellule_centree( number_format($val,0,'',' ') );
  }

  /**
   * safe_mode
   * Le "Safe Mode" est le mode de sécurité de PHP. Fonctionnalité obsolète depuis PHP 5.3.0, à ne plus utiliser.
   * Voir http://www.php.net/manual/fr/features.safe-mode.php
   *
   * @param void
   * @return string
   */
  private static function safe_mode()
  {
    return (ini_get('safe_mode')) ? InfoServeur::cellule_coloree_centree('ON','rouge') : InfoServeur::cellule_coloree_centree('OFF','vert') ;
  }

  /**
   * open_basedir
   * Limite les fichiers pouvant être ouverts par PHP à une architecture de dossiers spécifique, incluant le fichier lui-même.
   * Voir http://php.net/manual/fr/ini.core.php#ini.open-basedir
   *
   * @param void
   * @return string
   */
  private static function open_basedir()
  {
    return (ini_get('open_basedir')) ? InfoServeur::cellule_coloree_centree('ON','rouge') : InfoServeur::cellule_coloree_centree('OFF','vert') ;
  }

  /**
   * ini_set_memory_limit
   * Teste s'il est autorisé d'augmenter la mémoire allouée au script.
   * Voir http://www.php.net/manual/fr/ini.core.php#ini.memory-limit
   * Cette directive peut être interdite dans la conf PHP ou via Suhosin (http://www.hardened-php.net/suhosin/configuration.html#suhosin.memory_limit)
   *
   * @param void
   * @return string
   */
  private static function ini_set_memory_limit()
  {
    $valeur_avant = (int)ini_get('memory_limit');
    if($valeur_avant==-1) return '<td class="hc">---</td>';
    $valeur_nouvelle = $valeur_avant*2;
    @ini_set('memory_limit',$valeur_nouvelle.'M'); @ini_alter('memory_limit',$valeur_nouvelle.'M');
    $valeur_apres = (int)ini_get('memory_limit');
    return ($valeur_apres==$valeur_avant) ? InfoServeur::cellule_coloree_centree('OFF','jaune') : InfoServeur::cellule_coloree_centree('ON','vert') ;
  }

  /**
   * register_globals
   * Ne pas enregistrer les variables environnement Get/Post/Cookie/Server comme des variables globales.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.register-globals
   * register_globals NE PEUT PAS être définie durant le traitement avec "ini_set" : ini_set(register_globals,0)
   *
   * @param void
   * @return string
   */
  private static function register_globals()
  {
    $valeur = (int)ini_get('register_globals');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * magic_quotes_gpc
   * Échapper les apostrophes pour Get/Post/Cookie.
   * Voir http://fr.php.net/manual/fr/info.configuration.php#ini.magic-quotes-gpc
   * Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4. Par défaut à 1 avant !
   *
   * @param void
   * @return string
   */
  private static function magic_quotes_gpc()
  {
    $valeur = (int)ini_get('magic_quotes_gpc');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * magic_quotes_sybase
   * Échapper les apostrophes pour Get/Post/Cookie. Remplace la directive magic_quotes_gpc en cas d'activation.
   * Voir http://fr.php.net/manual/fr/sybase.configuration.php#ini.magic-quotes-sybase
   * Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4. Par défaut à 0 avant.
   *
   * @param void
   * @return string
   */
  private static function magic_quotes_sybase()
  {
    $valeur = (int)ini_get('magic_quotes_sybase');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * magic_quotes_runtime
   * Échapper les apostrophes pour toutes les données externes, y compris les bases de données et les fichiers texte.
   * Voir http://php.net/manual/fr/info.configuration.php#ini.magic-quotes-runtime
   * Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.
   *
   * @param void
   * @return string
   */
  private static function magic_quotes_runtime()
  {
    $valeur = (int)ini_get('magic_quotes_runtime');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * session_gc_maxlifetime
   * Durée de vie des données (session) sur le serveur, en nombre de secondes.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.gc-maxlifetime
   * Par défaut 1440.
   *
   * @param void
   * @return string
   */
  private static function session_gc_maxlifetime()
  {
    $valeur = (int)ini_get('session.gc_maxlifetime');
    $couleur = ($valeur>620) ? 'vert' : 'rouge' ;
    return InfoServeur::cellule_coloree_centree($valeur.'s',$couleur);
  }

  /**
   * session_use_trans_sid
   * Protection contre les attaques qui utilisent des identifiants de sessions dans les URL.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.use-trans-sid
   * Par défaut à 0.
   *
   * @param void
   * @return string
   */
  private static function session_use_trans_sid()
  {
    $valeur = (int)ini_get('session.use_trans_sid');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * session_use_only_cookies
   * Le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.use-only-cookies
   * Par défaut à 1.
   *
   * @param void
   * @return string
   */
  private static function session_use_only_cookies()
  {
    $valeur = (int)ini_get('session.use_only_cookies');
    return ($valeur==1) ? InfoServeur::cellule_coloree_centree('ON','vert') : InfoServeur::cellule_coloree_centree('OFF','rouge') ;
  }

  /**
   * zend_ze1_compatibility_mode
   * Activer le mode de compatibilité avec le Zend Engine 1 (PHP 4).
   * C'est incompatible avec classe PDO, et l'utilisation de "simplexml_load_string()" ou "DOMDocument" (par exemples) provoquent des erreurs fatales.
   * Voir http://www.php.net/manual/fr/ini.core.php#ini.zend.ze1-compatibility-mode
   *
   * @param void
   * @return string
   */
  private static function zend_ze1_compatibility_mode()
  {
    $valeur = (int)ini_get('zend.ze1_compatibility_mode');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * server_protocole
   * Retourne si le protocole est http ou https.
   *
   * @param void
   * @return string
   */
  private static function server_protocole()
  {
    $valeur = strtoupper( substr(HTTP,0,-3) ); // Retirer "://"
    return InfoServeur::cellule_coloree_centree($valeur,'jaune');
  }

  /**
   * server_IP_client
   * Retourne l'IP du client.
   * Utilise la méthode get_IP() définie dans la classe Session.
   *
   * @param void
   * @return string
   */
  private static function server_IP_client()
  {
    $valeur = Session::get_IP();
    return InfoServeur::cellule_coloree_centree($valeur,'jaune');
  }

  /**
   * space_disk_total
   * Retourne l'espace tosque total.
   *
   * @param void
   * @return string
   */
  private static function space_disk_total()
  {
    $valeur = disk_total_space('/'); // ne pas convertir en entier car trop grand nombre
    return InfoServeur::cellule_coloree_centree(FileSystem::afficher_fichier_taille($valeur),'vert');
  }

  /**
   * space_disk_free
   * Retourne l'espace tosque total.
   *
   * @param void
   * @return string
   */
  private static function space_disk_free()
  {
    $valeur = disk_free_space('/'); // ne pas convertir en entier car trop grand nombre
    $min_vert  = (HEBERGEUR_INSTALLATION=='multi-structures') ? '10737418240' : '1073741824' ; // 10Go 1024*1024*1024*10 | 1Go 1024*1024*1024
    $min_jaune = (HEBERGEUR_INSTALLATION=='multi-structures') ?  '1048576000' :  '104857600' ; // 100Mo 1024*1024*100 | 10Mo 1024*1024*10
    $str_val   = sprintf('%020s',$valeur);
    $str_vert  = sprintf('%020s',$min_vert);
    $str_jaune = sprintf('%020s',$min_jaune);
    if($str_val>$str_vert)      { $couleur = 'vert';  }
    elseif($str_val>$str_jaune) { $couleur = 'jaune'; }
    else                        { $couleur = 'rouge'; }
    return InfoServeur::cellule_coloree_centree(FileSystem::afficher_fichier_taille($valeur),$couleur);
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques génériques
  // //////////////////////////////////////////////////

  /**
   * minimum_limitations_upload
   * La taille maximale d'upload d'un fichier est limitée par memory_limit + post_max_size + upload_max_filesize
   * Normalement on a memory_limit > post_max_size > upload_max_filesize
   * Cette fonction retourne le minimum de ces 3 valeurs (attention, ce ne sont pas des entiers mais des chaines avec des unités).
   *
   * @param bool    $avec_explication
   * @return string "min(memory_limit,post_max_size,upload_max_filesize)=..."
   */
  public static function minimum_limitations_upload($avec_explication=TRUE)
  {
    $tab_limit_chaine = array( ini_get('memory_limit') , ini_get('post_max_size') , ini_get('upload_max_filesize') );
    $valeur_mini = 0;
    $chaine_mini = '';
    foreach($tab_limit_chaine as $key => $chaine)
    {
      $chaine = trim($chaine);
      $valeur = (int)substr($chaine,0,-1);
      $unite  = strtoupper($chaine[strlen($chaine)-1]);
      switch($unite)
      {
        case 'G':
            $valeur *= 1000;
        case 'M':
            $valeur *= 1000;
        case 'K':
            $valeur *= 1000;
      }
      if( ($valeur!=0) && ($valeur!=-1) && ( ($valeur_mini==0) || ($valeur<$valeur_mini) ) )
      {
        $valeur_mini = $valeur;
        $chaine_mini = $chaine;
      }
    }
    return ($avec_explication) ? 'min(memory_limit,post_max_size,upload_max_filesize) = '.$chaine_mini : $chaine_mini ;
  }

  /**
   * is_open_basedir
   *
   * @param void
   * @return bool
   */
  public static function is_open_basedir()
  {
    return (ini_get('open_basedir')) ? TRUE : FALSE ;
  }

  /**
   * modules_php
   * Liste de tous les modules compilés et chargés.
   * La présence des modules requis est effectuée à chaque appel de SACoche.
   * Voir http://fr.php.net/get_loaded_extensions
   *
   * @param void
   * @return array
   */
  public static function modules_php()
  {
    $tab_modules = get_loaded_extensions();
    natcasesort($tab_modules);
    return array_values($tab_modules);
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques pour la page "Caractéristiques du serveur"
  // //////////////////////////////////////////////////

  public static function tableau_versions_logicielles()
  {
    $tab_objets = array(
      'version_php'                    => 'PHP',
      'version_mysql'                  => 'MySQL',
      'version_sacoche_prog'           => 'SACoche fichiers',
      'version_sacoche_base_structure' => 'SACoche base structure',
      'version_sacoche_base_webmestre' => 'SACoche base webmestre',
    );
    return InfoServeur::tableau_deux_colonnes( 'Versions logicielles' , $tab_objets );
  }

  public static function tableau_limitations_PHP()
  {
    $tab_objets = array(
      'max_execution_time'      => 'max execution time',
      'memory_limit'            => 'memory limit',
      'post_max_size'           => 'post max size',
      'upload_max_filesize'     => 'upload max filesize',
      'max_input_vars'          => 'max input vars',
      'max_input_time'          => 'max input time',
      'max_input_nesting_level' => 'max input nesting level',
    );
    return InfoServeur::tableau_deux_colonnes( 'Limitations PHP' , $tab_objets );
  }

  public static function tableau_limitations_MySQL()
  {
    $tab_objets = array(
      'max_allowed_packet'   => 'max allowed packet',
      'max_user_connections' => 'max user connections',
      'group_concat_max_len' => 'group concat max len',
    );
    return InfoServeur::tableau_deux_colonnes( 'Limitations MySQL' , $tab_objets );
  }

  public static function tableau_configuration_PHP()
  {
    $tab_objets = array(
      'safe_mode'                   => 'safe_mode',
      'open_basedir'                => 'open_basedir',
      'ini_set_memory_limit'        => 'ini_set(memory_limit)',
      'register_globals'            => 'register_globals',
      'magic_quotes_gpc'            => 'magic_quotes_gpc',
      'magic_quotes_sybase'         => 'magic_quotes_sybase',
      'magic_quotes_runtime'        => 'magic_quotes_runtime',
      'session_gc_maxlifetime'      => 'session.gc_maxlifetime',
      'session_use_trans_sid'       => 'session.use_trans_sid',
      'session_use_only_cookies'    => 'session.use_only_cookies',
      'zend_ze1_compatibility_mode' => 'zend.ze1_compatibility_mode',
    );
    return InfoServeur::tableau_deux_colonnes( 'Configuration de PHP' , $tab_objets );
  }

  public static function tableau_configuration_MySQL()
  {
    $tab_tr = array();
    $tab_tr[] = '<tr><th>Mode SQL <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.InfoServeur::commentaire('sql_mode').'" /></th></tr>';
    // Déterminer le mode SQL.
    // @see http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
    // @see http://dev.mysql.com/doc/refman/5.7/en/faqs-sql-modes.html
    $sql_mode = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_mode_SQL() : DB_WEBMESTRE_PUBLIC::DB_recuperer_mode_SQL() ;
    $tab_mode = explode( ',' , $sql_mode );
    sort($tab_mode);
    foreach($tab_mode as $mode)
    {
      $tab_tr[] = '<tr><td>'.$mode.'</td></tr>';
    }
    return'<table class="p"><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  public static function tableau_verification_variables()
  {
    $tab_objets = array(
      'safe_mode'                => 'safe_mode',
      'open_basedir'             => 'open_basedir',
      'magic_quotes_gpc'         => 'magic_quotes_gpc',
      'magic_quotes_sybase'      => 'magic_quotes_sybase',
      'magic_quotes_runtime'     => 'magic_quotes_runtime',
      'session_use_trans_sid'    => 'session.use_trans_sid',
      'session_use_only_cookies' => 'session.use_only_cookies',
    );
    return InfoServeur::tableau_deux_colonnes( 'Configuration de PHP' , $tab_objets );
  }

  public static function tableau_verification_serveur()
  {
    $tab_objets = array(
      'server_protocole' => 'protocole',
      'server_IP_client' => 'adresse IP',
    );
    return InfoServeur::tableau_deux_colonnes( 'Connexion au serveur' , $tab_objets );
  }

  public static function tableau_espace_disque()
  {
    $tab_objets = array(
      'space_disk_total' => 'total',
      'space_disk_free'  => 'disponible',
    );
    return InfoServeur::tableau_deux_colonnes( 'Espace disque' , $tab_objets );
  }

  public static function tableau_modules_PHP($nb_lignes)
  {
    $tab_extensions_chargees = InfoServeur::modules_php();
    $tab_extensions_requises = explode( ' , ' , trim(PHP_LISTE_EXTENSIONS) );
    $nb_modules  = count($tab_extensions_chargees);
    $nb_colonnes = ceil($nb_modules/$nb_lignes);
    $lignes = '';
    for($numero_ligne=0 ; $numero_ligne<$nb_lignes ; $numero_ligne++)
    {
      $lignes .= '<tr>';
      for($numero_colonne=0 ; $numero_colonne<$nb_colonnes ; $numero_colonne++)
      {
        $indice = $numero_colonne*$nb_lignes + $numero_ligne ;
        $style  = ( ($indice<$nb_modules) && (in_array($tab_extensions_chargees[$indice],$tab_extensions_requises)) ) ? ' class="'.InfoServeur::$tab_style['vert'].'"' : '' ;
        $lignes .= ($indice<$nb_modules) ? '<td'.$style.'><a href="#'.$tab_extensions_chargees[$indice].'">'.$tab_extensions_chargees[$indice].'</a></td>' : '<td class="hc">-</td>' ;
      }
      $lignes .= '</tr>';
    }
    $tr_head = '<tr><th colspan="'.$nb_colonnes.'">Modules PHP compilés et chargés <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.InfoServeur::commentaire('modules_PHP').'" /></th></tr>';
    return'<table id="tab_modules" class="p"><thead>'.$tr_head.'</thead><tbody>'.$lignes.'</tbody></table>';
  }

  public static function tableau_reglages_Suhosin()
  {
    $tab_tr = array();
    $tab_tr[0] = '<tr><th>Suhosin <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.InfoServeur::commentaire('suhosin').'" /></th>';
    if(version_compare(PHP_VERSION,'5.4','>='))
    {
      $tab_tr[1] = '<tr><td class="hc">---</td></tr>';
    }
    else
    {
      $tab_lignes   = array(1=>'get','post','request');
      $tab_colonnes = array(1=>'max_name_length','max_totalname_length','max_value_length','max_vars');
      $tab_suhosin_options = (version_compare(PHP_VERSION,'5.3','<')) ? @ini_get_all( 'suhosin' ) : @ini_get_all( 'suhosin' , FALSE /*details*/ ) ; // http://fr.php.net/ini_get_all
      foreach($tab_lignes as $i_ligne => $categorie)
      {
        $tab_tr[$i_ligne] = '<tr><td class="hc">'.$categorie.'</td>';
        foreach($tab_colonnes as $i_colonne => $option)
        {
          $tab_tr[0] .= ($i_ligne==1) ? '<td class="hc">'.str_replace('_',' ',$option).'</td>' : '' ;
          $option_nom = ( ($categorie!='request') || ($option!='max_name_length') ) ? 'suhosin'.'.'.$categorie.'.'.$option : 'suhosin.request.max_varname_length' ;
          $option_val = (isset($tab_suhosin_options[$option_nom])) ? $tab_suhosin_options[$option_nom] : '---' ;
          $tab_tr[$i_ligne] .= '<td class="hc">'.$option_val.'</td>' ;
        }
        $tab_tr[$i_ligne] .= '</tr>';
      }
    }
    $tab_tr[0] .= '</tr>';
    return'<table class="p"><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  public static function tableau_reglages_GD()
  {
    $jpeg = (version_compare(PHP_VERSION,'5.3','<')) ? 'JPG' : 'JPEG' ;
    $tab_objets = array(
      'GD Version'       => 'Version', // 
      'FreeType Support' => 'Support FreeType', // Requis pour imagettftext()
      $jpeg.' Support'   => 'Support JPEG',
      'PNG Support'      => 'Support PNG',
      'GIF Read Support' => 'Support GIF', // "GIF Create Support" non testé car on n'écrit que des jpg (photos) et des png (étiquettes) de toutes façons.
    );
    $tab_gd_options = gd_info(); // http://fr.php.net/manual/fr/function.gd-info.php
    $tab_tr = array();
    foreach($tab_objets as $nom_objet => $nom_affichage)
    {
      if($nom_objet=='GD Version')
      {
        $search_version = preg_match( '/[0-9.]+/' , $tab_gd_options[$nom_objet] , $tab_match);
        $gd_version = ($search_version) ? $tab_match[0] : '' ;
        $img = ($nom_objet=='GD Version') ? '<img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="La fonction imagecreatetruecolor() requiert la bibliothèque GD version 2.0.1 ou supérieure, 2.0.28 ou supérieure étant recommandée." /> ' : '' ;
             if(version_compare($gd_version,'2.0.28','>=')) $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'vert');
        else if(version_compare($gd_version,'2.0.1' ,'>=')) $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'jaune');
        else                                                $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'rouge');
      }
      else
      {
        $img = '' ;
        $td = ($tab_gd_options[$nom_objet]) ? InfoServeur::cellule_coloree_centree('ON','vert') : InfoServeur::cellule_coloree_centree('OFF','rouge') ;
      }
      $tab_tr[] = '<tr><td>'.$img.$nom_affichage.'</td>'.$td.'</tr>';
    }
    return'<table class="p"><thead><tr><th colspan="2">Bibliothèque GD</th></tr></thead><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  public static function tableau_serveur_et_client()
  {
    $tab_tr = array();
    $tab_tr[] = '<tr><th>Identification du Client</th><td>'.html($_SERVER['HTTP_USER_AGENT']).'</td></tr>';
    $tab_tr[] = '<tr><th>Identification du Serveur</th><td>'.html($_SERVER['SERVER_SOFTWARE'].' '.PHP_SAPI).'</td></tr>';
    $tab_tr[] = '<tr><th>Système d\'exploitation</th><td>'.html(php_uname('s').' '.php_uname('r')).'</td></tr>';
    $tab_tr[] = '<tr><th>Adresse d\'installation</th><td>'.html(URL_INSTALL_SACOCHE).'</td></tr>';
    return'<table class="p"><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  /*
   * Récupérer dans un tableau le résultat d'un phpinfo().
   * @see http://fr2.php.net/manual/fr/function.phpinfo.php
   * 
   * @param const $quoi   liste sur http://fr2.php.net/phpinfo
   * @return array
   */
  // 
  public static function array_phpinfo($quoi=INFO_ALL)
  {
    ob_start();
    phpinfo($quoi); 
    $phpinfo_lignes = explode("\n", strip_tags(ob_get_contents(), '<tr><td><h2>'));
    ob_end_clean(); 
    $categorie = 'Général';
    $tab_infos = array();
    foreach($phpinfo_lignes as $ligne)
    {
      // nouvelle catégorie ?
      preg_match("~<h2>(.*)</h2>~", $ligne, $title) ? $categorie = $title[1] : null;
      // 2 colonnes
      if(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $ligne, $val))
      {
        $tab_infos[$categorie][$val[1]] = $val[2];
      }
      // 3 colonnes
      elseif(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $ligne, $val))
      {
        $tab_infos[$categorie][$val[1]] = array( 'local' => $val[2], 'master' => $val[3] );
      }
    }
    return $tab_infos;
  }

}
?>