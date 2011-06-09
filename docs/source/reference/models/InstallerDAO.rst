InstallerDAO
============

ThinkUp/webapp/_lib/model/interface.InstallerDAO.php

Copyright (c) 2009-2011 Dwi Widiastuti, Gina Trapani

Installer Data Access Object interface



Methods
-------

getTables
~~~~~~~~~
* **@return** array Table name strings


Get array of tables from current connected database

.. code-block:: php5

    <?php
        public function getTables();


createInstallDatabase
~~~~~~~~~~~~~~~~~~~~~
* **@param** array $cfg_vals The array of config values supplied to the installer.
* **@return** boolean True on success, false on failure.


Attempts to create the database specified in the install process. If it
already exists then nothing happens. If it does not exist, this
function tries to create it.

.. code-block:: php5

    <?php
        public function createInstallDatabase($cfg_vals = null);


checkTable
~~~~~~~~~~
* **@param** str $table_name
* **@return** array If table exists and okay result will be array('Msg_text' => 'OK')


Check table

.. code-block:: php5

    <?php
        public function checkTable($table_name);


repairTable
~~~~~~~~~~~
* **@param** str $table_name Name of table to repair
* **@return** array Row that consists of key Message_text. If table exists and okay it must be array('Msg_text' => 'OK')


Repair table

.. code-block:: php5

    <?php
        public function repairTable($table_name);


describeTable
~~~~~~~~~~~~~
* **@param** str $table_name
* **@return** array table descriptions that consist of following case-sensitive properties:             - Field => name of field             - Type => type of field             - Null => is type allowed to be null             - Default => Default value for field             - Extra => such as auto_increment


Describe table

.. code-block:: php5

    <?php
        public function describeTable($table_name);


showIndex
~~~~~~~~~
* **@param** str $table_name with prefix
* **@return** array Tables indices of $table_name


Get index from particular table

.. code-block:: php5

    <?php
        public function showIndex($table_name);


runMigrationSQL
~~~~~~~~~~~~~~~
* **@param** str $sql SQL command to execute


Run a sql migration command

.. code-block:: php5

    <?php
        public function runMigrationSQL($sql);


diffDataStructure
~~~~~~~~~~~~~~~~~
* **@param** str $desired_structure_sql_string
* **@param** array $existing_tables
* **@return** array Array of 'queries', and 'for_update', what SQL will update the current structure to desired state


Diff the current database table structure with desired table structure.
This is a modified version of WordPress' dbDelta function
More info: http://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table

.. code-block:: php5

    <?php
        public function diffDataStructure($queries = '', $tables = array());


needsSnowflakeUpgrade
~~~~~~~~~~~~~~~~~~~~~

Temporary method to determine if database is 64-bit post ID ready
This method will be deleted for ThinkUp's 1.0 release.

.. code-block:: php5

    <?php
        public function needsSnowflakeUpgrade();




