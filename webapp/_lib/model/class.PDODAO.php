<?php
/**
 * PDO DAO
 * Parent class for PDO DAOs
 * @author Christoffer Viken <christoffer@viken.me>
 * @author Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

abstract class PDODAO {
    /**
     * Logger
     * @var Logger Object
     */
    var $logger;
    /**
     * Configuration
     * @var Config Object
     */
    var $config;
    /**
     * PDO instance
     * @var PDO Object
     */
    static $PDO = null;
    /**
     * Table Prefix
     * @var str
     */
    static $prefix;
    /**
     * GMT offset
     * @var int
     */
    static $gmt_offset;
    /**
     *
     * @var bool
     */
    private $profiler_enabled = false;

    /**
     * Constructor
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'GMT_offset', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @return PDODAO
     */
    public function __construct($cfg_vals=null){
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance($cfg_vals);
        if(is_null(self::$PDO)) {
            $this->connect();
        }
        $this->prefix = $this->config->getValue('table_prefix');
        $this->gmt_offset = $this->config->getValue('GMT_offset');
        $this->profiler_enabled = Profiler::isEnabled();
    }

    /**
     * Connection initiator
     */
    public final function connect(){
        if(is_null(self::$PDO)) {
            //set default db type to mysql if not set
            $db_type = $this->config->getValue('db_type');
            if(! $db_type) { $db_type = 'mysql'; }
            $db_socket = $this->config->getValue('db_socket');
            if ( !$db_socket) {
                $db_port = $this->config->getValue('db_port');
                if (!$db_port) {
                    $db_socket = '';
                } else {
                    $db_socket = ";port=".$this->config->getValue('db_port');
                }
            } else {
                $db_socket=";unix_socket=".$db_socket;
            }
            $db_string = sprintf(
                "%s:dbname=%s;host=%s%s", 
            $db_type,
            $this->config->getValue('db_name'),
            $this->config->getValue('db_host'),
            $db_socket
            );
            self::$PDO = new PDO(
            $db_string,
            $this->config->getValue('db_user'),
            $this->config->getValue('db_password')
            );
            self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /**
     * Disconnector
     * Caution! This will disconnect for ALL DAOs
     */
    protected final function disconnect(){
        self::$PDO = null;
    }

    /**
     * Executes the query, with the bound values
     * @param str $sql
     * @param array $binds
     * @return PDOStatement
     */
    protected final function execute($sql, $binds = array()) {
        if ($this->profiler_enabled) {
            $start_time = microtime(true);;
        }
        $sql = preg_replace("/#prefix#/", $this->prefix, $sql);
        $sql = preg_replace("/#gmt_offset#/", $this->gmt_offset, $sql);
        $stmt = self::$PDO->prepare($sql);
        if(is_array($binds) and count($binds) >= 1) {
            foreach ($binds as $key => $value) {
                if(is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
        }
        $stmt->execute();
        if ($this->profiler_enabled) {
            $end_time = microtime(true);
            $total_time = $end_time - $start_time;
            $profiler = Profiler::getInstance();
            $profiler->add($total_time, $sql, true, $stmt->rowCount());
        }
        return $stmt;
    }

    /**
     * Proxy for getUpdateCount
     * @param PDOStatement $ps
     * @return int Update Count
     */
    protected final function getDeleteCount($ps){
        //Alias for getUpdateCount
        return $this->getUpdateCount($ps);
    }

    /**
     * Gets the rows returned by a statement as array of objects.
     * @param PDOStatement $ps
     * @param str $obj
     * @return array numbered keys, with objects
     */
    protected final function getDataRowAsObject($ps, $obj){
        $row = $ps->fetchObject($obj);
        $ps->closeCursor();
        if(!$row){
            $row = null;
        }
        return $row;
    }

    /**
     * Gets the first returned row as array
     * @param PDOStatement $ps
     * @return array named keys
     */
    protected final function getDataRowAsArray($ps){
        $row = $ps->fetch(PDO::FETCH_ASSOC);
        $ps->closeCursor();
        if(!$row){
            $row = null;
        }
        return $row;
    }

    /**
     * Returns the first row as an object
     * @param PDOStatement $ps
     * @param str $obj
     * @return array numbered keys, with Objects
     */
    protected final function getDataRowsAsObjects($ps, $obj){
        $data = array();
        while($row = $ps->fetchObject($obj)){
            $data[] = $row;
        }
        $ps->closeCursor();
        return $data;
    }

    /**
     * Gets the rows returned by a statement as array with arrays
     * @param PDOStatement $ps
     * @return array numbered keys, with array named keys
     */
    protected final function getDataRowsAsArrays($ps){
        $data = $ps->fetchAll(PDO::FETCH_ASSOC);
        $ps->closeCursor();
        return $data;
    }

    /**
     * Gets the result returned by a count query
     * (value of col count on first row)
     * @param PDOStatement $ps
     * @param int Count
     */
    protected final function getDataCountResult($ps){
        $row = $ps->fetch(PDO::FETCH_ASSOC);
        $ps->closeCursor();
        if(!$row or !isset($row['count'])){
            $count = 0;
        } else {
            $count = (int) $row['count'];
        }
        return $count;
    }

    /**
     * Gets whether a statement returned anything
     * @param PDOStatement $ps
     * @return bool True if row(s) are returned
     */
    protected final function getDataIsReturned($ps){
        $count = $ps->rowCount();
        $ps->closeCursor();
        if ($count > 0) {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Gets data "insert ID" from a statement
     * @param PDOStatement $ps
     * @return int|bool Inserted ID or false if there is none.
     */
    protected final function getInsertId($ps){
        $rc = $ps->rowCount();
        $id = self::$PDO->lastInsertId();
        $ps->closeCursor();
        if ($rc > 0 and $id > 0) {
            return $id;
        } else {
            return false;
        }
    }

    /**
     * Gets the number of inserted rows by a statement
     * @param PDOStatement $ps
     * @return int Insert count
     */
    protected final function getInsertCount($ps){
        $rc = $ps->rowCount();
        $ps->closeCursor();
        return $rc;
    }

    /**
     * Get the number of updated rows
     * @param PDOStatement $ps
     * @return int Update Count
     */
    protected final function getUpdateCount($ps){
        $num = $ps->rowCount();
        $ps->closeCursor();
        return $num;
    }

    /**
     * Converts any form of "boolean" value to a Database usable one
     * @internal
     * @param mixed $val
     * @return int 0 or 1 (false or true)
     */
    protected final function convertBoolToDB($val){
        return $val ? 1 : 0;
    }

    /**
     * Converts a Database boolean to a PHP boolean
     * @param int $val
     * @return bool
     */
    public final static function convertDBToBool($val){
        return $val == 0 ? false : true;
    }

}