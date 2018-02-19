<?php
/**
 * Couche d'abstraction DB utilisant Mysqli
 *
 * @version 1.0
 * @author Sébastien ROMMENS
 * @package Lib
 * @subpackage Database
 * @since Thu Apr 13 10:28:49 CEST 2006
 */


class DB_driver_mysqli {

  /**
   * Identifiant de l'objet de connexion
   * @var DatabaseInterface $id
   */
  protected  $id;

  /**
   * Nom de la base de données
   * @var string $databaseName
   */
  public $databaseName;

  /**
   * Objet contenant le résultat de la dernère requête préparée
   * @var Mysqli_STMT $result
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
   * Indique le début d'une transaction pour exécuter un commit ou un rollback à la fin de la connexion
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


    $this->id = @new mysqli($host, $username, $password, $this->databaseName, $port); // <-- pas d'exception levée avec mysqli, pfff
    if(!mysqli_connect_error()) {
      $this->id->autocommit($this->getCommitMode());
    } else {
      //Génération d'une DataBaseException
      throw new DataBaseException(mysqli_connect_error(), mysqli_connect_errno());
    }
  }

  /**
   * Définit le jeu de caractères par défaut du client
   */
  public function setCharset($charset){
    $this->id->set_charset($charset);
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
    $this->query = (isset($_SERVER['HOSTNAME']) ? '/*SIG'.$_SERVER['HOSTNAME'].'SIG*/ ' : '').$query;
    $this->param = $param;
    $this->stmt = null;

    $sysNbQuery = (!isset($sysNbQuery) || $sysNbQuery<=0) ? 1 : $sysNbQuery+1;

    // Prepare de la requête
    if(!empty($param)) {
      foreach($param as $key => $val) {
        if (strpos($query, $key) !== false) {
          if($param[$key]===null) $this->query = str_replace($key, 'NULL', $this->query);
          else $this->query = str_replace($key, "'".$this->id->real_escape_string($param[$key])."'", $this->query);
        }
      }
    }

    // Execution de la requête
    $this->stmt = $this->id->query($this->query);

    if (!$this->stmt) {
      //Génération d'une DataBaseException
      throw new DataBaseException(mysqli_error($this->id), mysqli_sqlstate($this->id));
    }

    return $this->stmt;
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

    $rs = $this->query($query, $param);
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
    if(!$indexkey) {
      if($fetchAll) {
        $this->data = $this->stmt->fetch_all(MYSQLI_ASSOC);
      } else {
        $this->data = $this->stmt->fetch_assoc();
      }
    }
    else {
      $this->data = array();
      $data = $this->stmt->fetch_all(MYSQLI_ASSOC);
      if(!empty($data)) {
        $key = array_keys($data[0]);
        $key = $key[0];
        foreach($data as $row) {
          $rkey = $row[$key];
          if($indexkey_is_uniq) {
          	if(!isset($this->data[$rkey])) $this->data[$rkey] = $row;
          } else {
          	$this->data[$rkey][] = $row;
          }
        }
      }
    }

    if(!is_array($this->data)) $this->data=array();
  }

  /**
   * Permet de fermer la connexion avec la base
   *
   * @return void
   */
  private function close() {

    // Si une transaction a été ouverte, il faut faire commit ou un rollback
    if($this->transaction){
      if (!connection_aborted()) {
        $this->commit();
      } else {
        $this->rollback();
      }
    }

    // Fermeture de la connexion
    $this->id->close();
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
  public function beginTransaction (){
    $this->id->autocommit(false);
  }

  /**
   * Commit des requêtes exécutées
   *
   * @return boolean
   */
  public function commit() {
    $this->id->commit();
  }

  /**
   * Rollback des requêtes exécutées
   *
   * @return boolean
   */
  public function rollback() {
    $this->id->rollback();
  }

  /**
   * Permet de récupérer l'id du dernier objet inséré dans la base
   *
   * @return int $lastInsertedId
   */
  public function getLastOid() {
    $lastInsertedId = $this->id->insert_id;
    return $lastInsertedId > 0 ? $lastInsertedId : null;
  }

  /**
   * Permet de récupérer le nombre de lignes affectées par la dernière requete
   *
   * @return int $lastInsertedId
   */
  public function rowCount() {
    $numRows = 0;
    if($this->id) {
      $numRows = $this->id->affected_rows;
    }
    return $numRows;
  }

  /**
   *  Permet de sélectionner la base de données concernée
   *
   * @param unknown_type $dbname
   */
  public function selectDB($dbname) {

    if($dbname != $this->databaseName){
      $this->databaseName = $dbname;
      $this->id->select_db($dbname);
    }
  }

  /**
   * Renvoi le code de la dernière erreur
   *
   */
  public function errorCode() {
    if(is_object($this->id)) {
      $code = mysqli_sqlstate($this->id);
      return empty($code) ? null : $code;
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
