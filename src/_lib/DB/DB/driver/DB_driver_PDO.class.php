<?php

/**
 * Couche d'abstraction DAO utilisant PDO
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */
class DB_driver_PDO {

    /**
     * Identifiant de l'objet de connexion
     * @var PDO $id
     */
    protected $id;

    /**
     * Nom de la base de données
     * @var string $databaseName
     */
    public $databaseName;

    /**
     * Objet contenant le résultat de la dernère requête préparée
     * @var PDOStatement $result
     */
    public $stmt;

    /**
     * Tableau contenant le résultat de la dernière requête. Tableau à 2 niveaux (champs, lignes)
     * @var mixed $data
     */
    public $data;

    /**
     * Requête en cours
     * @var string $query
     */
    public $query;

    /**
     * Tableau contenant les variables bind de la requête en cours
     * @var array $param
     */
    public $param;

    /**
     * Commiter automatiquement ou non les requêtes (true par défaut)
     * @var boolean $autoCommit
     */
    public $autoCommit = true;

    /**
     * True si une une transaction est en cours, pour exécuter un commit ou un rollback à la fin de la connexion
     * @var boolean $transaction
     */
    public $transaction = false;

    /**
     * Constructeur. Permet de se connecter à la base de données
     *
     * @param string  $databaseName  le nom de la base de données
     * @param string  $username   le nom de l'utilisateur servant à la connexion
     * @param string  $password   le mot de passe de connexion
     * @param string  $host    l'adresse IP du serveur
     * @param string  $port    le port de connexion (optionnel) : 3306 par défaut
     * @param array		$driverOptions	paramètres spécifiques de connexion au driver (persistance, encodage...)
     * @return void
     */
    function __construct($databaseType, $databaseName, $username, $password, $host, $port = "3306", $driverOptions = array()) {

        $this->databaseType = $databaseType;
        $this->databaseName = $databaseName;

        // Construction de la chaîne de connexion
        switch ($this->databaseType) {
            case 'mysql': case 'sybase': case 'mssql':
                $dsn = $this->databaseType . ':host=' . $host . ';dbname=' . $this->databaseName . ';port=' . $port;
                break;
            case 'oracle':
                $dsn = 'oci:dbname=//' . $host . ':' . $port . '/' . $this->databaseName;
                break;
            case 'firebird':
                $dsn = $this->databaseType . ':User=' . $username . ';Password=' . $password . ';Database=' . $this->databaseName . ';DataSource=' . $host . ';Port=' . $port . ';';
                break;
            case 'pgsql':
                $dsn = $this->databaseType . ':host=' . $host . ' port=' . $port . ' dbname=' . $this->databaseName . ' user=' . $username . ' password=' . $password;
                break;
            default:
                throw new DataBaseException('Type de base "' . $this->databaseType . '" non pris en charge par DAO !');
        }

        try {
            $driverOptions[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
            $driverOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $this->id = new PDO($dsn, $username, $password, $driverOptions);
        } catch (PDOException $e) {
            //Génération d'une DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Définit le jeu de caractères par défaut du client
     */
    public function setCharset($charset) {
        try {
            $this->id->query('SET NAMES ' . $charset);
        } catch (PDOException $e) {
            //Génération d'une DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Permet d'exécuter une requête.
     *
     * Aucun résultat n'est renvoyé par cette fonction. Elle doit être utilisé pour effectuer
     * des insertions, des updates... Elle est de même utilisée par les
     * autres fonction de la classe comme queryRow() et queryTab().
     *
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @return void
     */
    public function query($query, $param = array()) {

        global $sysNbQuery;

        // execution de la requête
        $this->query = (isset($_SERVER['HOSTNAME']) ? '/*SIG' . $_SERVER['HOSTNAME'] . 'SIG*/ ' : '') . $query;
        $this->param = $param;
        $this->stmt = null;

        $this->id->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->getCommitMode());
        $this->id->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        $sysNbQuery = (!isset($sysNbQuery) || $sysNbQuery <= 0) ? 1 : $sysNbQuery + 1;

        try {

            // Prepare de la requête
            $this->stmt = $this->id->prepare($this->query);

            if ($this->stmt !== false) {
                // Bind des paramètres
                if (!empty($param)) {
                    foreach ($param as $key => $val) {
                        // ATTENTION, il faut $param[$key] et pas $val car c'est un passage par référence
                        // les valeurs seront évaluées à l'exécution du statement
                        if (strpos($query, $key) !== false) {
                            if ($param[$key] === null) {
                                $this->stmt->bindParam(trim($key), $param[$key], PDO::PARAM_NULL);
                            } else {
                                $this->stmt->bindParam(trim($key), $param[$key]);
                            }
                        } // sinon rien, clés en trop autorisées
                    }
                }

                // Execution de la requête
                $this->data = $this->stmt->execute();

                if ($this->stmt->errorCode() != PDO::ERR_NONE) {
                    //Génération d'une DataBaseException
                    $error = $this->stmt->errorInfo();
                    throw new DataBaseException($error[2], $error[0]);
                }

                return $this->data;
            } else {
                //Génération d'une DataBaseException
                $error = $this->stmt->errorInfo();
                throw new DataBaseException($error[2], $error[0]);
            }
        } catch (PDOException $e) {
            // Transformation de PDOException en DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * Permet d'exécuter une requête devant renvoyer une seule ligne de résultat.
     * le tableau de résultat est à 2 niveaux (lignes, champs)
     *
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @return mixed
     */
    public function queryRow($query, $param = array()) {

        $this->query($query, $param);
        //Récupération des données issues d'une requête
        $this->fetch(false, false, false);

        return ($this->data);
    }

    /**
     *
     * Permet d'exécuter une requête devant renvoyer plusieurs lignes de résultat.
     * le tableau de résultat est à 2 niveaux (lignes, champs)
     *
     * @param string $query chaine SQL
     * @param mixed $param variables bind de type array(":bind"=>"value")
     * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
     * @param bool $indexkey_is_uniq si true (et $indexkey==true) alors la clé sera considérée comme unique (le tableau renvoyé n'aura donc que 2 niveaux au lieu de 3)
     * @return mixed
     */
    public function queryTab($query, $param = array(), $indexkey = false, $indexkey_is_uniq = false) {

        $this->query($query, $param);
        //Récupération des données issues d'une requête
        $this->fetch(true, $indexkey, $indexkey_is_uniq);

        return ($this->data);
    }

    /**
     * Récupération des données issues d'une requête (lignes, champs, types de champs, lignes affectées)
     *
     * @return void
     * @param bool $fetchAll si true alors ne fait qu'un seul fetch
     * @param bool $indexkey si true alors prend la première colonne des resultats comme indice du tableau de resultats
     * @param bool $indexkey_is_uniq si true (et $indexkey==true) alors la clé sera considérée comme unique (le tableau renvoyé n'aura donc que 2 niveaux au lieu de 3)
     */
    private function fetch($fetchAll = true, $indexkey = false, $indexkey_is_uniq = false) {
        try {
            if (!$indexkey) {
                if ($fetchAll) {
                    $this->data = @$this->stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $this->data = @$this->stmt->fetch(PDO::FETCH_ASSOC);
                }
            } else {
                $this->data = @$this->stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
                if ($indexkey_is_uniq && is_array($this->data)) {
                    foreach ($this->data as &$row) {
                        $row = $row[0];
                    }
                }
            }

            if (!is_array($this->data))
                $this->data = array();
        } catch (PDOException $e) {
            //Génération d'une DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Permet de fermer la connexion avec la base
     *
     * @return void
     */
    private function close() {

        // Si une transaction a été ouverte, il faut faire commit ou un rollback
        if ($this->transaction) {
            if (!connection_aborted()) {
                $this->commit();
            } else {
                $this->rollback();
            }
        }

        // Fermeture de la connexion
        $this->id = null;
        unset($this->id);
        return null;
    }

    /**
     * Définition du commit automatique ou non
     *
     * @param boolean activation du commit (true par défaut)
     * @return void
     */
    public function setAutoCommit($bCommit = true) {
        $this->autoCommit = $bCommit;
    }

    /**
     * Retourne la constante d'exécution d'une requête : commit immédiat ou non
     *
     * @return bool
     */
    public function getCommitMode() {
        if ($this->autoCommit) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initialise le début d'une transaction (autocommit à false par défaut)
     *
     * @return boolean
     */
    public function beginTransaction() {
        if ($this->id && !$this->transaction) {
            try {
                $this->autoCommit = false;
                $temp = @$this->id->beginTransaction();
                $this->transaction = true;
            } catch (PDOException $e) {
                //Génération d'une DataBaseException
                throw new DataBaseException($e->getMessage(), $e->getCode());
            }
            return $temp;
        } else {
            return true;
        }
    }

    /**
     * Commit des requêtes exécutées
     *
     * @return boolean
     */
    public function commit() {
        if ($this->id) {
            try {
                $temp = @$this->id->commit();
                $this->transaction = false;
                $this->autoCommit = true;
            } catch (PDOException $e) {
                //Génération d'une DataBaseException
                throw new DataBaseException($e->getMessage(), $e->getCode());
            }
            return $temp;
        } else {
            return true;
        }
    }

    /**
     * Rollback des requêtes exécutées
     *
     * @return boolean
     */
    public function rollback() {
        if ($this->id) {
            try {
                $temp = @$this->id->rollBack();
                $this->transaction = false;
            } catch (PDOException $e) {
                //Génération d'une DataBaseException
                throw new DataBaseException($e->getMessage(), $e->getCode());
            }
            return $temp;
        } else {
            return true;
        }
    }

    /**
     * Permet de récupérer l'id du dernier objet inséré dans la base
     *
     * @return int $lastInsertedId
     */
    public function getLastOid() {
        $lastInsertedId = "";
        try {
            $lastInsertedId = $this->id->lastInsertId();
        } catch (PDOException $e) {
            //Génération d'une DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
        return $lastInsertedId > 0 ? $lastInsertedId : null;
    }

    /**
     * Permet de récupérer le nombre de lignes affectées par la dernière requete
     *
     * @return int $lastInsertedId
     */
    public function rowCount() {
        $numRows = 0;
        try {
            if ($this->stmt) {
                $numRows = $this->stmt->rowCount();
            }
        } catch (PDOException $e) {
            //Génération d'une DataBaseException
            throw new DataBaseException($e->getMessage(), $e->getCode());
        }
        return $numRows;
    }

    /**
     *  Permet de sélectionner la base de données concernée
     *
     * @param string $dbname
     */
    public function selectDB($dbname) {

        if ($dbname != $this->databaseName) {
            $this->databaseName = $dbname;
            switch ($this->databaseType) {
                case 'mysql':
                    $this->id->exec("use " . $dbname . ";");
                    break;
                case 'mssql':
                    $this->id->exec("use " . $dbname . " GO");
                    break;
                case 'oracle':
                    $dsn = 'oci:dbname=//' . $host . ':' . $port . '/' . $this->databaseName;
                    $this->id = new PDO($this->dsn, $this->username, $this->password);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Renvoi le code de la dernière erreur
     *
     */
    public function errorCode() {
        if (is_object($this->stmt)) {
            $code = $this->stmt->errorCode();
            return PDO::ERR_NONE == $code ? null : $code;
        }
    }

    /**
     * Destructeur de la classe
     *
     */
    public function __destruct() {
        $this->close();
    }

}

?>
