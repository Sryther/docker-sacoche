<?php

/**
 * Multiton - Gestion des accès aux bases de données
 *
 * Dans cette classe de type Multiton sont définies les méthodes permettant de gérer une connection unique
 * aux différentes bases de données
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */
class DB_Manager {

    /**
     * Instance de la classe DB_Manager
     *
     * @var DB_Manager $_instance
     */
    private static $_instance;

    /**
     * Tableau de sauvegarde des objets de type AbstractDatabase
     *
     * @var array $connexions
     */
    private $connexions = array();
    private static $queryLog = array();

    /**
     * Une variable "end of line" pour utiliser <br /> ou \n si appel hors web (cli ou cron)
     */
    private static $eol = '<br />';

    /**
     * Constructeur de la classe
     *
     */
    private function __construct() {
        // init de $eol
        if (php_sapi_name() === 'cli' || (defined('TXT_OUTPUT') && TXT_OUTPUT)) {
            self::$eol = "\n";
        }
    }

    /**
     * Cette méthode retourne ou crée l'instance de l'objet DB_Manager
     *
     * @return DB_Manager
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new DB_Manager();
        }
        return self::$_instance;
    }

    /**
     * Cette méthode retourne un objet DatabaseInterface (Couche d'abstraction de connexion à la base de données)
     *
     * @param String $connection_name nom de la connexion à la base de données défini dans le fichier database.conf.php
     * @return DatabaseInterface
     */
    public function getConnexion($connection_name) {
        // par défaut, en attendant de l'initialiser plus loin
        $critical = true;
        try {
            if (!is_string($connection_name)) {
                throw new Exception("Connexion BDD impossible : nom de connexion invalide");
            }

            global $_CONST;
            $pool   = (string) $_CONST["CONNECTION"][$connection_name]["POOL"];
            $dbname = (string) $_CONST["CONNECTION"][$connection_name]["DB_NAME"];
            if (isset($_CONST["POOL"][$pool]["CRITICAL"]) && $_CONST["POOL"][$pool]["CRITICAL"] == false) {
                $critical = false;
            }
            // plus utilisé ?
            // $force_encoding = (isset($_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"]) && $_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"] != '') ? $_CONST["POOL"][$_CONST["CONNECTION"][$connection_name]["POOL"]]["FORCE_ENCODING"] : false;

            if (empty($pool) || empty($dbname)) {
                throw new DatabaseException('Connexion BDD impossible : paramètre manquant (pool ou base)');
            }

            if (!isset($this->connexions[$pool])) {
                // Création de la connection au pool et à la base de données
                $this->connexions[$pool] = self::createConnexion($pool, $dbname);
            } else {
                $this->connexions[$pool]->selectDB($dbname);
            }
            $this->connexions[$pool]->data = false;

            return $this->connexions[$pool];
        } catch (DatabaseException $e) {
            // Il ne faut pas bloquer la suite si la connexion n'est pas critique (par exemple pour une database de log)
            if ($critical) {
                // On log d'abord
                $e->warn();
                exit;
            } else {
                // passe au suivant
                throw $e;
            }
        }
    }

    /**
     * Cette méthode permet de créer l'objet de connexion à une base de données
     *
     * @param string $pool Nom du pool de connection
     * @param string $dbname Nom de la base de données
     * @return DatabaseInterface La connexion, un objet DB_driver_mysqli ou DB_driver_PDO (suivant $_CONST["POOL"][$pool]["ABSTRACTION"])
     */
    private static function createConnexion($pool, $dbname) {
        global $_CONST;

        if (isset($dbname) && isset($_CONST["POOL"][$pool]["ABSTRACTION"])) {
            $driverOptions = array();

            // Connexion
            if ($_CONST["POOL"][$pool]["ABSTRACTION"] == "PDO") {
                // Classe d'abstraction utilisant PDO
                require_once("driver/DB_driver_PDO.class.php");

                // Gestion des options du driver PDO
                if (isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"] != '') {
                    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                        $driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $_CONST["POOL"][$pool]["FORCE_ENCODING"];
                        $fix_force_encoding_bug = false;
                    } else {
                        $fix_force_encoding_bug = true;
                    }
                }
                if (isset($_CONST["POOL"][$pool]["PERSISTENT"]) && $_CONST["POOL"][$pool]["PERSISTENT"] === true) {
                    $driverOptions[PDO::ATTR_PERSISTENT] = true;
                }

                // Ouverture d'une connexion avec PDO
                $connexion = new DB_driver_PDO($_CONST["POOL"][$pool]["TYPE"], $dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);

                if (isset($fix_force_encoding_bug) && $fix_force_encoding_bug) {
                    $connexion->setCharset($_CONST["POOL"][$pool]["FORCE_ENCODING"]);
                }
            } elseif ($_CONST["POOL"][$pool]["ABSTRACTION"] == "MYSQL") {
                /** Classe d'abstraction MYSQL */
                require_once("driver/DB_driver_mysqli.class.php");

                // Ouverture d'une connexion avec MYSQL
                $connexion = new DB_driver_mysqli($_CONST["POOL"][$pool]["TYPE"], $dbname, $_CONST["POOL"][$pool]["USER"], $_CONST["POOL"][$pool]["PASS"], $_CONST["POOL"][$pool]["HOST"], $_CONST["POOL"][$pool]["PORT"], $driverOptions);

                if (isset($_CONST["POOL"][$pool]["FORCE_ENCODING"]) && $_CONST["POOL"][$pool]["FORCE_ENCODING"] != '') {
                    $connexion->setCharset($_CONST["POOL"][$pool]["FORCE_ENCODING"]);
                }
            } else {
                // Génération d'une DataBaseException
                throw new DataBaseException("La couche d'abstraction '" . $_CONST["POOL"][$pool]["ABSTRACTION"] . "' n'est pas implémentée !");
            }
            // On ajoute nos propriétés sup
            $connexion->error_type = isset($_CONST["POOL"][$pool]["ERROR"]) ? $_CONST["POOL"][$pool]["ERROR"] : null;
            $connexion->debug_type = !empty($_CONST["POOL"][$pool]["DEBUG"]) ? $_CONST["POOL"][$pool]["DEBUG"] : null;
        } else {
            // Génération d'une DataBaseException
            throw new DataBaseException("La base de données  '" . $dbname . "' n'est pas configurée !");
        }
        return $connexion;
    }

    public function closeConnexion($connection_name) {
        global $_CONST;
        $pool = $_CONST["CONNECTION"][$connection_name]["POOL"];

        if (isset($this->connexions[$pool])) {
            unset($this->connexions[$pool]);
            return true;
        }
        return false;
    }

    /**
     * Traite les erreurs (suivant le param $_CONST['POOL'][$conn]['ERROR'])
     * @param DB_driver_PDO     $connection Peut être null
     * @param DatabaseException $exception
     * @throws DatabaseException Si error_type == 'exception'
     */
    public static function handleError($connection, $exception) {
        // $connection peut être null s'il n'a pas encore été initialisé
        if (!$connection) {
            $exception->warn();
        } else {
            if ($connection->debug_type) {
                self::debug($connection, 0, $exception);
            }

            switch ($connection->error_type) {
                case 'exception':
                    throw $exception;
                    break;

                case 'silent':
                    $exception->log();
                    break;

                case 'warning':
                default:
                    $exception->warn();
            }
        }
    }

    /**
     * Construit le tableau de log qui sera envoyé par le destructeur pour debug (évitez au maximum de l'utiliser en PROD)
     */
    public static function debug($connection, $time = 0, $databaseException = null) {

        if (!empty($connection->debug_type)) {
            //$error = !empty($connection->errorParams['errorInfo']) ? $connection->errorParams['errorInfo'] : null;
            //if($connection->debug_type=='errfile' && empty($error)) return;
            $aBacktrace = array();
            foreach (debug_backtrace() as $val) {
                if (!isset($val['class']) || strpos($val['class'], 'DB') !== 0) { // On prend pas les class des autres...
                    $str = '';
                    if (isset($val['class'])) {
                        $str .= $val['class'] . '::';
                    }
                    if (isset($val['function'])) {
                        $str .= $val['function'];
                    }
                    $str .= ' called at [';
                    if (isset($val['file'])) {
                        $str .= $val['file'];
                    }
                    if (isset($val['line'])) {
                        $str .= ' line ' . $val['line'];
                    }
                    $str .= ']';
                    $aBacktrace[] = $str;
                }
            }

            self::$queryLog[$connection->databaseName . '|' . $connection->debug_type][] = array(
                'time' => sprintf("%01.5f", $time),
                'query' => $connection->query,
                'bind' => $connection->param,
                'result' => $databaseException === null ? $connection->data : $databaseException->getMessage(),
                'backtrace' => $aBacktrace,
                'error' => $databaseException !== null
            );
        }
    }

    /**
     * Fusionne le tableau des paramètres dans la requête, pour les logs
     *
     * @param string $query La requête SQL initiale
     * @param array $params Le tableau de paramètres
     * @return string La requête SQL avec ses valeurs
     */
    private static function mergeBindInQuery($query, $params = array()) {
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if ($v === null) {
                    $query = str_replace($k, "NULL", $query);
                } else {
                    $query = str_replace($k, "'" . $v . "'", $query);
                }
            }
        }

        return $query;
    }

    /**
     * Destructeur de la classe
     * Traite le debug,  attention pour le mode "firebug"
     * - verifiez "output_buffering = On" dans votre conf PHP
     * - la taille des headers peut dépasser le max autorisé (suivant conf web)
     */
    function __destruct() {

        if (!empty(self::$queryLog)) {
            try {
                foreach (self::$queryLog as $databaseName_logType => $qLogs) {
                    list($databaseName, $logType) = explode('|', $databaseName_logType);

                    switch ($logType) {
                        case 'firebug':
                            // On verifie que la bufferisation est activée (sinon => erreur "Headers already sent ...") et la classe FirePHP chargée
                            $buffer_status = ob_get_status();
                            if (!empty($buffer_status) && class_exists('FirePHP')) {
                                $firephp = FirePHP::getInstance(true);

                                $table = array();
                                $table[] = array('Time', 'Query', 'Bind', 'Result');

                                $totalTime = 0;
                                foreach ($qLogs as $qLog) {
                                    $table[] = array($qLog['time'], $qLog['query'], $qLog['bind'], $qLog['result']);
                                    $totalTime += $qLog['time'];
                                }

                                $firephp->table('SQL: ' . $databaseName . ' (' . count($qLogs) . ' @ ' . $totalTime . ')', $table);
                            }
                            break;

                        case 'screen':
                            foreach ($qLogs as $qLog) {
                                echo $databaseName . ' (' . $qLog['time'] . '): ' . self::mergeBindInQuery($qLog['query'], $qLog['bind']) . self::$eol;
                                if ($qLog['error'])
                                    echo " => " . $qLog['result'] . self::$eol;
                            }
                            break;

                        case 'file':
                            $log = '';
                            foreach ($qLogs as $qLog) {
                                $log .= date('d/m/Y H:i:s') . ': ' . $databaseName . ' (' . $qLog['time'] . ")\n";
                                $log .= strip_tags(implode("\n", $qLog['backtrace'])) . "\n";
                                $log .= self::mergeBindInQuery($qLog['query'], $qLog['bind']);
                                $log .= (!empty($qLog['error']) ? "\nERROR: " . $qLog['error'][2] : '') . "\n\n";
                            }

                            if (defined('DEBUG_LOG')) {
                                file_put_contents(DEBUG_LOG, $log, FILE_APPEND);
                            } elseif (is_dir('/var/log/sesamath')) {
                                file_put_contents("/var/log/sesamath/$databaseName.DB.log", $log, FILE_APPEND);
                            } else {
                                trigger_error($log);
                            }
                            break; /* */
                    }
                }
            } catch (DatabaseException $e) {
                $e->warn();
            }
        }
    }

}

/**
 * Gestion des Exceptions de base de données
 *
 * @package Lib
 * @subpackage Database
 */
class DatabaseException extends Exception {

    public function __construct($message = '', $code = 0) {
        parent::__construct($message, (int) $code);
    }

    /**
     * Log l'erreur message + trace résumée) avec notre trigger d'erreur qui écrit sur stdout (conf warning)
     */
    public function warn() {
        set_error_handler(array('DatabaseException', '_errorHandler'));
        trigger_error('BDD - '.$this->getUserMessage(), E_USER_WARNING);
        restore_error_handler();
    }

    /**
     * Log l'erreur (message + trace résumée) avec error_log (conf silent)
     */
    public function log() {
        error_log($this->getUserMessage());
    }

    /**
     * Renvoie la 1re trace hors DB_* dans la pile d'appel
     * @return boolean
     */
    private function _getUserTrace() {
        $completTrace = $this->getTrace();
        if (!empty($completTrace)) {
            // On renvoie la 1re trace sans class ou avec class ≠ DB_*
            foreach ($completTrace as $trace) {
                if (empty($trace['class']) || substr($trace['class'], 0, 3) !== 'DB_') {

                    return $trace;
                }
            }
        }

        return false;
    }

    /**
     * Ajoute au message initial de l'exception la 1re trace trouvée dans la pile
     * @return string
     */
    public function getUserMessage($forceTxt = false) {
        $message = $this->message;
        if (defined('DEBUG') && DEBUG) {
            // trace complete
            $message .= "\nAvec la trace complète\n";
            $message .= $this->getTraceAsString();
        } else {
            // On ajoute notre, trace résumée au dernier appel avant nous
            $userTrace = $this->_getUserTrace();
            if ($userTrace) {
                if ($forceTxt || php_sapi_name() === 'cli' || (defined('TXT_OUTPUT') && TXT_OUTPUT)) {
                    // txt
                    $message .= ' in ' . $userTrace['file'] . ' on line ' . $userTrace['line'] . "\n";
                } else {
                    // html
                    $message .= ' in <strong>' . $userTrace['file'] . '</strong> on line <strong>' . $userTrace['line'] . '</strong><br />';
                }
            }
        }

        return $message;
    }

    /**
     * Notre errorHandler qui affiche sur stdout avec un préfixe DB_Warning (et évite de remonter au dessus,
     * ça indique la ligne qui nous appelle et pas celle d'où on lance le trigger_error)
     * @param type $level
     * @param type $message
     * @param type $file
     * @param type $line
     * @param type $context
     * @return boolean
     */
    private static function _errorHandler($level, $message, $file, $line, $context) {
        if ($level === E_USER_WARNING) {
            // On gère txt / html avec l'existence de cette constante
            // (trop lourd de passer par des params dans un errorHandler)
            if (php_sapi_name() === 'cli' || (defined('TXT_OUTPUT') && TXT_OUTPUT)) {
                echo "DB_Warning : $message\n";
            } else {
                echo '<strong>DB_Warning :</strong> '.utf8_encode($message).'<br />'."\n";exit;
            }

            return true; //And prevent the PHP error handler from continuing
        }

        return false; //Otherwise, use PHP's error handler
    }
}