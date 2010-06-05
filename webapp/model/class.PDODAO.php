<?php
abstract class PDODAO {
    var $logger;
    var $config;
    static $PDO = null;
    static $prefix;
    static $gmt_offset;

    public function __construct(){
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        if(is_null(self::$PDO)) {
            $this->connect();
        }
        $this->prefix = $this->config->getValue('table_prefix');
        $this->gmt_offset = $this->config->getValue('GMT_offset');
    }

    public final function connect(){
        if(is_null(self::$PDO)) {
            //set default db type to mysql if not set
            $db_type = $this->config->getValue('db_type');
            if(! $db_type) { $db_type = 'mysql'; }
            $db_socket = $this->config->getValue('db_socket');
            if ( !$db_socket) {
                $db_socket = '';
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

    // Not really currently needed, and somebody might call it
    // to close off one DAO, and thereby kill all of them.
    protected final function disconnect(){
        self::$PDO = null;
    }

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

    protected final function execute($sql, $binds = array()) {
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
        return $stmt;
    }

    protected final function getInsertCount($ps){
        $rc = $ps->rowCount();
        $id = self::$PDO->lastInsertId();
        $ps->closeCursor();
        if ($rc > 0) {
            return $rc;
        } else {
            return false;
        }
    }

    protected final function getUpdateCount($ps){
        return (int) $ps->rowCount();
    }

    protected final function getDeleteCount($ps){
        //Alias for getUpdateCount
        return $this->getUpdateCount($ps);
    }

    protected final function getDataRowAsObject($ps, $obj){
        $row = $ps->fetchObject($obj);
        $ps->closeCursor();
        if(!$row){
            $row = null;
        }
        return $row;
    }

    protected final function getDataRowAsArray($ps){
        $row = $ps->fetch(PDO::FETCH_ASSOC);
        $ps->closeCursor();
        if(!$row){
            $row = null;
        }
        return $row;
    }

    protected final function getDataRowsAsObjects($ps, $obj){
        $data = array();
        while($row = $ps->fetchObject($obj)){
            $data[] = $row;
        }
        $ps->closeCursor();
        return $data;
    }

    protected final function getDataRowsAsArrays($ps){
        $data = $ps->fetchAll(PDO::FETCH_ASSOC);
        $ps->closeCursor();
        return $data;
    }

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

    protected final function convertBoolToDB($val){
        return $val ? 1 : 0;
    }

    public final function convertDBToBool($val){
        return $val == 0 ? false : true;
    }

}