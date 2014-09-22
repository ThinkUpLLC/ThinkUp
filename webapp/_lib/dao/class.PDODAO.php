<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PDODAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Christoffer Viken, Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * PDO DAO
 * Parent class for PDO DAOs
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Christoffer Viken, Gina Trapani
 * @author Christoffer Viken <christoffer@viken.me>
 * @author Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Piyush Mishra <me[at]piyushmishra[dot]com>
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
    protected $profiler_enabled = false;
    /**
     * Constructor
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'GMT_offset', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @return PDODAO
     * @throws PDOException
     * @throws DataExceedsColumnWidthException
     */
    public function __construct($cfg_vals=null){
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance($cfg_vals);
        if (is_null(self::$PDO)) {
            $this->connect();
        }
        self::$prefix = $this->config->getValue('table_prefix');
        self::$gmt_offset = $this->config->getGMTOffset();
        $this->profiler_enabled = Profiler::isEnabled();
    }

    /**
     * Instantiate singleton instance of the logger pointing at the specified file.
     * @param str $log_location
     * @return void
     */
    public function setLogger($log_location) {
        $this->logger = Logger::getInstance($log_location);
    }

    /**
     * Set the logger instance used by the DAO.
     * @param Logger $logger
     * @return void
     */
    public function setLoggerInstance($logger) {
        $this->logger = $logger;
    }

    /**
     * Return the singleton Logger instance.
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Connection initiator
     */
    public final function connect(){
        if (is_null(self::$PDO)) {
            self::$PDO = new PDO(
            self::getConnectString($this->config),
            $this->config->getValue('db_user'),
            $this->config->getValue('db_password')
            );
            self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            // if THINKUP_CFG var 'set_pdo_charset' is set to true, set the connection charset to utf8
            if ($this->config->getValue('set_pdo_charset')) {
                self::$PDO->exec('SET CHARACTER SET utf8');
            }
            $timezone = $this->config->getValue('timezone');
            $time = new DateTime("now", new DateTimeZone($timezone) );
            $tz_offset = $time->format('P');
            try {
                self::$PDO->exec("SET time_zone = '$timezone'");
            } catch (PDOException $e) {
                // Time zone couldn't be set; use offset instead, but if the timezone is one for which offset changes
                // throughout year, such as during daylight saving time, dates converted from/to UTC by the database
                // from a different offset will be incorrect.
                self::$PDO->exec("SET time_zone = '$tz_offset'");
            }
        }
    }

    /**
     * Generates a connect string to use when creating a PDO object.
     * @param Config $config
     * @return string PDO connect string
     */
    public static function getConnectString($config) {
        //set default db type to mysql if not set
        $db_type = $config->getValue('db_type');
        if (!$db_type) { $db_type = 'mysql'; }
        $db_socket = $config->getValue('db_socket');
        if ( !$db_socket) {
            $db_port = $config->getValue('db_port');
            if (!$db_port) {
                $db_socket = '';
            } else {
                $db_socket = ";port=".$config->getValue('db_port');
            }
        } else {
            $db_socket=";unix_socket=".$db_socket;
        }
        $db_string = sprintf(
            "%s:dbname=%s;host=%s%s",
        $db_type,
        $config->getValue('db_name'),
        $config->getValue('db_host'),
        $db_socket
        );
        return $db_string;
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
            $start_time = microtime(true);
        }
        $sql = preg_replace("/#prefix#/", self::$prefix, $sql);
        $sql = preg_replace("/#gmt_offset#/", self::$gmt_offset, $sql);

        $stmt = self::$PDO->prepare($sql);
        if (is_array($binds) and count($binds) >= 1) {
            foreach ($binds as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
        }
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $config = Config::getInstance();
            $exception_details = 'Database error! ';
            if ($config->getValue('debug') !== false) {
                $exception_details .= 'ThinkUp could not execute the following query: '.
                str_replace(chr(10), "", $stmt->queryString) . ' Details: '. $e->getMessage();
            } else {
                if (!Utils::isThinkUpLLC()) {
                    $exception_details .=
                    ' To see the technical details of what went wrong, set debug = true in ThinkUp\'s config file.';
                } else {
                    $exception_details .=
                    ' Contact us at help@thinkup.com.';

                }
            }
            if ( strpos($e->getMessage(),'Data too long for column') !== false) {
                throw new DataExceedsColumnWidthException($exception_details);
            } else {
                throw new PDOException ($exception_details);
            }
        }
        if ($this->profiler_enabled) {
            $end_time = microtime(true);
            $total_time = $end_time - $start_time;
            $profiler = Profiler::getInstance();
            $sql_with_params = Utils::mergeSQLVars($stmt->queryString, $binds);
            $profiler->add($total_time, $sql_with_params, true, $stmt->rowCount());
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
     * Gets a single row and closes cursor.
     * @param PDOStatement $ps
     * @return various array,object depending on context
     */
    protected final function fetchAndClose($ps){
        $row = $ps->fetch();
        $ps->closeCursor();
        return $row;
    }
    /**
     * Gets a multiple rows and closes cursor.
     * @param PDOStatement $ps
     * @return array of arrays/objects depending on context
     */
    protected final function fetchAllAndClose($ps){
        $rows = $ps->fetchAll();
        $ps->closeCursor();
        return $rows;
    }
    /**
     * Gets the rows returned by a statement as array of objects.
     * @param PDOStatement $ps
     * @param str $obj
     * @return array numbered keys, with objects
     */
    protected final function getDataRowAsObject($ps, $obj){
        $ps->setFetchMode(PDO::FETCH_CLASS,$obj);
        $row = $this->fetchAndClose($ps);
        if (!$row){
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
        $ps->setFetchMode(PDO::FETCH_ASSOC);
        $row = $this->fetchAndClose($ps);
        if (!$row){
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
        $ps->setFetchMode(PDO::FETCH_CLASS,$obj);
        $data = $this->fetchAllAndClose($ps);
        return $data;
    }

    /**
     * Gets the rows returned by a statement as array with arrays
     * @param PDOStatement $ps
     * @return array numbered keys, with array named keys
     */
    protected final function getDataRowsAsArrays($ps){
        $ps->setFetchMode(PDO::FETCH_ASSOC);
        $data = $this->fetchAllAndClose($ps);
        return $data;
    }

    /**
     * Gets the result returned by a count query
     * (value of col count on first row)
     * @param PDOStatement $ps
     * @param int Count
     */
    protected final function getDataCountResult($ps){
        $ps->setFetchMode(PDO::FETCH_ASSOC);
        $row = $this->fetchAndClose($ps);
        if (!$row or !isset($row['count'])){
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
        $row = $this->fetchAndClose($ps);
        $ret = false;
        if ($row && count($row) > 0) {
            $ret = true;
        }
        return $ret;
    }

    /**
     * Gets data "insert ID" from a statement
     * @param PDOStatement $ps
     * @return int|bool Inserted ID or false if there is none.
     */
    protected final function getInsertId($ps){
        $rc = $this->getUpdateCount($ps);
        $id = self::$PDO->lastInsertId();
        if ($rc > 0 and $id > 0) {
            return $id;
        } else {
            return false;
        }
    }

    /**
     * Proxy for getUpdateCount
     * @param PDOStatement $ps
     * @return int Insert count
     */
    protected final function getInsertCount($ps){
        //Alias for getUpdateCount
        return $this->getUpdateCount($ps);
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
