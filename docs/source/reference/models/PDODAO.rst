PDODAO
======

ThinkUp/webapp/_lib/model/class.PDODAO.php

Copyright (c) 2009-2011 Mark Wilkie, Christoffer Viken, Gina Trapani

PDO DAO
Parent class for PDO DAOs


Properties
----------

logger
~~~~~~

Logger

config
~~~~~~

Configuration

PDO
~~~

PDO instance

prefix
~~~~~~

Table Prefix

gmt_offset
~~~~~~~~~~

GMT offset

profiler_enabled
~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'GMT_offset', 'db_type', 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
* **@return** PDODAO


Constructor

.. code-block:: php5

    <?php
        public function __construct($cfg_vals=null){
            $this->logger = Logger::getInstance();
            $this->config = Config::getInstance($cfg_vals);
            if(is_null(self::$PDO)) {
                $this->connect();
            }
            self::$prefix = $this->config->getValue('table_prefix');
            self::$gmt_offset = $this->config->getGMTOffset();
            $this->profiler_enabled = Profiler::isEnabled();
        }


connect
~~~~~~~

Connection initiator

.. code-block:: php5

    <?php
        public final function connect(){
            if(is_null(self::$PDO)) {
                self::$PDO = new PDO(
                self::getConnectString($this->config),
                $this->config->getValue('db_user'),
                $this->config->getValue('db_password')
                );
                self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // if THINKUP_CFG var 'set_pdo_charset' is set to true, set the connection charset to utf8
                if ($this->config->getValue('set_pdo_charset')) {
                    self::$PDO->exec('SET CHARACTER SET utf8');
                }
            }
        }


getConnectString
~~~~~~~~~~~~~~~~
* **@param** Config $config
* **@return** string PDO connect string


Generates a connect string to use when creating a PDO object.

.. code-block:: php5

    <?php
        public static function getConnectString($config) {
            //set default db type to mysql if not set
            $db_type = $config->getValue('db_type');
            if(! $db_type) { $db_type = 'mysql'; }
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


disconnect
~~~~~~~~~~

Disconnector
Caution! This will disconnect for ALL DAOs

.. code-block:: php5

    <?php
        protected final function disconnect(){
            self::$PDO = null;
        }


execute
~~~~~~~
* **@param** str $sql
* **@param** array $binds
* **@return** PDOStatement


Executes the query, with the bound values

.. code-block:: php5

    <?php
        protected final function execute($sql, $binds = array()) {
            if ($this->profiler_enabled) {
                $start_time = microtime(true);
            }
            $sql = preg_replace("/#prefix#/", self::$prefix, $sql);
            $sql = preg_replace("/#gmt_offset#/", self::$gmt_offset, $sql);
    
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
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                $config = Config::getInstance();
                $exception_details = 'Database error! ';
                if ($config->getValue('debug')) {
                    $exception_details .= '<br>ThinkUp could not execute the following query:<br> '.
                    str_replace(chr(10), "", $stmt->queryString) . '  <br>PDOException: '. $e->getMessage();
                } else {
                    $exception_details .=
                    '<br>To see the technical details of what went wrong, set debug = true in ThinkUp\'s config file.';
                }
                throw new PDOException ($exception_details);
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


getDeleteCount
~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** int Update Count


Proxy for getUpdateCount

.. code-block:: php5

    <?php
        protected final function getDeleteCount($ps){
            //Alias for getUpdateCount
            return $this->getUpdateCount($ps);
        }


fetchAndClose
~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** various array,object depending on context


Gets a single row and closes cursor.

.. code-block:: php5

    <?php
        protected final function fetchAndClose($ps){
            $row = $ps->fetch();
            $ps->closeCursor();
            return $row;
        }


fetchAllAndClose
~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** array of arrays/objects depending on context


Gets a multiple rows and closes cursor.

.. code-block:: php5

    <?php
        protected final function fetchAllAndClose($ps){
            $rows = $ps->fetchAll();
            $ps->closeCursor();
            return $rows;
        }


getDataRowAsObject
~~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@param** str $obj
* **@return** array numbered keys, with objects


Gets the rows returned by a statement as array of objects.

.. code-block:: php5

    <?php
        protected final function getDataRowAsObject($ps, $obj){
            $ps->setFetchMode(PDO::FETCH_CLASS,$obj);
            $row = $this->fetchAndClose($ps);
            if(!$row){
                $row = null;
            }
            return $row;
        }


getDataRowAsArray
~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** array named keys


Gets the first returned row as array

.. code-block:: php5

    <?php
        protected final function getDataRowAsArray($ps){
            $ps->setFetchMode(PDO::FETCH_ASSOC);
            $row = $this->fetchAndClose($ps);
            if(!$row){
                $row = null;
            }
            return $row;
        }


getDataRowsAsObjects
~~~~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@param** str $obj
* **@return** array numbered keys, with Objects


Returns the first row as an object

.. code-block:: php5

    <?php
        protected final function getDataRowsAsObjects($ps, $obj){
            $ps->setFetchMode(PDO::FETCH_CLASS,$obj);
            $data = $this->fetchAllAndClose($ps);
            return $data;
        }


getDataRowsAsArrays
~~~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** array numbered keys, with array named keys


Gets the rows returned by a statement as array with arrays

.. code-block:: php5

    <?php
        protected final function getDataRowsAsArrays($ps){
            $ps->setFetchMode(PDO::FETCH_ASSOC);
            $data = $this->fetchAllAndClose($ps);
            return $data;
        }


getDataCountResult
~~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@param** int Count


Gets the result returned by a count query
(value of col count on first row)

.. code-block:: php5

    <?php
        protected final function getDataCountResult($ps){
            $ps->setFetchMode(PDO::FETCH_ASSOC);
            $row = $this->fetchAndClose($ps);
            if(!$row or !isset($row['count'])){
                $count = 0;
            } else {
                $count = (int) $row['count'];
            }
            return $count;
        }


getDataIsReturned
~~~~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** bool True if row(s) are returned


Gets whether a statement returned anything

.. code-block:: php5

    <?php
        protected final function getDataIsReturned($ps){
            $row = $this->fetchAndClose($ps);
            $ret = false;
            if ($row && count($row) > 0) {
                $ret = true;
            }
            return $ret;
        }


getInsertId
~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** int|bool Inserted ID or false if there is none.


Gets data "insert ID" from a statement

.. code-block:: php5

    <?php
        protected final function getInsertId($ps){
            $rc = $this->getUpdateCount($ps);
            $id = self::$PDO->lastInsertId();
            if ($rc > 0 and $id > 0) {
                return $id;
            } else {
                return false;
            }
        }


getInsertCount
~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** int Insert count


Proxy for getUpdateCount

.. code-block:: php5

    <?php
        protected final function getInsertCount($ps){
            //Alias for getUpdateCount
            return $this->getUpdateCount($ps);
        }


getUpdateCount
~~~~~~~~~~~~~~
* **@param** PDOStatement $ps
* **@return** int Update Count


Get the number of updated rows

.. code-block:: php5

    <?php
        protected final function getUpdateCount($ps){
            $num = $ps->rowCount();
            $ps->closeCursor();
            return $num;
        }


convertBoolToDB
~~~~~~~~~~~~~~~
* **@internal** 
* **@param** mixed $val
* **@return** int 0 or 1 (false or true)


Converts any form of "boolean" value to a Database usable one

.. code-block:: php5

    <?php
        protected final function convertBoolToDB($val){
            return $val ? 1 : 0;
        }


convertDBToBool
~~~~~~~~~~~~~~~
* **@param** int $val
* **@return** bool


Converts a Database boolean to a PHP boolean

.. code-block:: php5

    <?php
        public final static function convertDBToBool($val){
            return $val == 0 ? false : true;
        }




