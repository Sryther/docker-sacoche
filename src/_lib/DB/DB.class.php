<?php
/**
 * Façade permettant de simplifier l'accès aux objets de connexion par des méthodes statiques
 *
 * Cet objet permet d'accéder directement aux méthodes begin, query, queryTab, queryRow, commit, rollback
 * en passant le nom de la connection
 *
 * @version 2.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */
// Classe de gestion des connexions aux pools
require( dirname(__FILE__) .'/DB/DB_Manager.class.php');

class DB {

    /**
     * Constructeur privé
     */
    private function __construct() {

    }

    /**
     * Permet d'exécuter une requête.
     *
     * Aucun résultat n'est renvoyé par cette fonction. Elle doit être utilisé pour effectuer
     * des insertions, des updates... Elle est de même utilisée par les
     * autres fonctions de la classe comme queryRow() et queryTab().
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @param string $query reqête SQL où les valeurs sont désignées par :ma_variable, sans apostrophe/guillemet (quoting ajouté au besoin)
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @return $res
     *   Le résultat de la méthode query sur l'objet PDO
     */
    public static function query($connection_name, $query, $param = "") {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            if (is_object($connection)) {
                $time_start = microtime(true);
                $res = $connection->query($query, $param);
                DB_Manager::debug($connection, microtime(true) - $time_start);
                return $res;
            }
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Permet d'exécuter une requête devant renvoyer une seule ligne de résultat.
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @param string $query reqête SQL où les valeurs sont désignées par :ma_variable, sans apostrophe/guillemet (quoting ajouté au besoin)
     * @param mixed $param variables bind de type array(":ma_variable"=>"sa_valeur")
     * @return array
     *   Un tableau associatif à 1 niveau (champs ou alias du select comme clés), FALSE si erreur
     */
    public static function queryRow($connection_name, $query, $param = "") {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            $time_start = microtime(true);
            $rs = $connection->queryRow($query, $param);
            DB_Manager::debug($connection, microtime(true) - $time_start);
            return $rs;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     *
     * Permet d'exécuter une requête devant renvoyer plusieurs lignes de résultat.
     * le tableau de résultat est à 2 niveaux (lignes, champs)
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
     * @param bool $indexkey_is_uniq si true (et $indexkey==true) alors la clé sera considérée comme unique (le tableau renvoyé n'aura donc que 2 niveaux au lieu de 3)
     * @return mixed
     */
    public static function queryTab($connection_name, $query, $param = "", $indexkey = false, $indexkey_is_uniq = false) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            $time_start = microtime(true);
            $rs = $connection->queryTab($query, $param, $indexkey, $indexkey_is_uniq);
            DB_Manager::debug($connection, microtime(true) - $time_start);
            return $rs;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     *
     * Permet d'exécuter une requête devant renvoyer une seule colonne de résultat.
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @return mixed
     */
    public static function queryCol($connection_name, $query, $param = "") {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            $time_start = microtime(true);
            $rs = $connection->queryTab($query, $param);
            foreach ($rs as &$val) {
                $val = array_shift($val);
            }
            DB_Manager::debug($connection, microtime(true) - $time_start);
            return $rs;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     *
     * Permet d'exécuter une requête devant renvoyer une seule chaine de résultat.
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @return mixed
     */
    public static function queryOne($connection_name, $query, $param = "") {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            $time_start = microtime(true);
            $rs = $connection->queryRow($query, $param);
            DB_Manager::debug($connection, microtime(true) - $time_start);
            return array_shift($rs);
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Initialise le début d'une transaction et passe l'autocommit à false
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @return boolean
     */
    public static function begin($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            return $connection->beginTransaction();
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Commit des requêtes exécutées (query() lance les $statement->exec(),
     * mais aussi les commit si DB_driver_PDO->autoCommit vaut true
     * (ce qui est le cas par défaut, mis à false dans begin)
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @return boolean
     */
    public static function commit($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            return $connection->commit();
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Rollback des requêtes exécutées
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @return boolean
     */
    public static function rollback($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            return $connection->rollback();
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Permet de récupérer l'id du dernier objet inséré dans la base, si la requête est de type INSERT
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @return mixed
     */
    public static function getLastOid($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            return $connection->getLastOid();
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Permet de récupérer le nombre d'enregistrements affectés par la dernière requete
     *
     * @param string $connection_name nom de la connection définie dans le fichier de configuration
     * @return mixed
     */
    public static function rowCount($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            return $connection->rowCount();
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Pour récupérer le code d'erreur de la dernière requête exécutée sur cette connexion
     * @param $connection_name
     */
    public static function errorCode($connection_name) {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);

            return $connection->errorCode();
		
    }

    /**
     * Permet de fermer une connexion à la base de données (ne sert que dans des cas bien préçis, ne pas utiliser si pas necessaire)
     *
     * @param string $connection_name Nom de la connexion définie dans le fichier de configuration
     */
    public static function close($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            return $databaseManager->closeConnexion($connection_name);
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);
            return false;
        }
    }

    /**
     * Retourne le type d'erreur de la connexion
     * @param string $connection_name Nom de la connexion définie dans le fichier de configuration
     * @return string exception|silent|warning
     */
    public static function getErrorType($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);

            return $connection->error_type;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);

            return false;
        }
    }

    /**
     * Affecte le type d'erreur de la connexion
     * @param string $connection_name Nom de la connexion définie dans le fichier de configuration
     * @param string $error_type      exception|silent|warning (si autre sera mis à warning)
     * @return boolean
     */
    public static function setErrorType($connection_name, $error_type) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);
            $connection->error_type = $error_type;

            return true;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);

            return false;
        }
    }

    /**
     * Retourne true si une transaction est en cours sur la connexion $connection_name
     * @param type $connection_name Nom de la connexion définie dans le fichier de configuration
     * @return boolean|null Null si la récupération de la connexion plante
     */
    public static function isTransactionStarted($connection_name) {
        $connection = null;
        try {
            $databaseManager = DB_Manager::getInstance();
            $connection = $databaseManager->getConnexion($connection_name);

            return $connection->transaction;
        } catch (DatabaseException $e) {
            DB_Manager::handleError($connection, $e);

            return null;
        }
    }

}
