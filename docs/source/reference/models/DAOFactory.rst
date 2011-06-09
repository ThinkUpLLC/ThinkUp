DAOFactory
==========

ThinkUp/webapp/_lib/model/class.DAOFactory.php

Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani

Data Access Object Factory

Inits a DAO based on the ThinkUp config db_type and $dao_mapping definitions.
db_type is defined in webapp/config.inc.php as:

    $THINKUP_CFG['db_type'] = 'somedb';

Example of use:

<code>
 DAOFactory::getDAO('SomeDAO');
</code>


Properties
----------

dao_mapping
~~~~~~~~~~~

Maps DAO from db_type and defines interface names and class implementation



Methods
-------

getDAO
~~~~~~



.. code-block:: php5

    <?php
        public static function getDAO($dao_key, $cfg_vals=null) {
            $db_type = self::getDBType($cfg_vals);
            if(! isset(self::$dao_mapping[$dao_key]) ) {
                throw new Exception("No DAO mapping defined for: " . $dao_key);
            }
            if(! isset(self::$dao_mapping[$dao_key][$db_type])) {
                throw new Exception("No db mapping defined for '" . $dao_key . "' with db type: " . $db_type);
            }
            $class_name = self::$dao_mapping[$dao_key][$db_type];
            $dao = new $class_name($cfg_vals);
            return $dao;
        }


getDBType
~~~~~~~~~
* **@param** array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'db_type', 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
* **@return** string db_type, will default to 'mysql' if not defined


Gets the db_type for our configured ThinkUp instance, defaults to mysql,
db_type can optionally be defined in webapp/config.inc as:

<code>
    $THINKUP_CFG['db_type'] = 'somedb';
</code>

.. code-block:: php5

    <?php
        public static function getDBType($cfg_vals=null) {
            $type = Config::getInstance($cfg_vals)->getValue('db_type');
            $type = is_null($type) ? 'mysql' : $type;
            return $type;
        }




