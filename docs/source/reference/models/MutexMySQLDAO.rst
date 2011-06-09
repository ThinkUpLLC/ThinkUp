MutexMySQLDAO
=============
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.MutexMySQLDAO.php

Copyright (c) 2009-2011 Guillaume Boudreau, Gina Trapani

Mutex Data Access Object implementation



Methods
-------

getMutex
~~~~~~~~

NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.

.. code-block:: php5

    <?php
        public function getMutex($name, $timeout=1) {
            $lock_name = $this->config->getValue('db_name').'.'.$name;
            /*
             $q = "SELECT GET_LOCK(':name', ':timeout') AS result";
             $vars = array(
             ':name' => $lock_name,
             ':timeout' => $timeout
             );
             $ps = $this->execute($q, $vars);
             */
            $q = "SELECT GET_LOCK('".$lock_name."', ".$timeout. ") AS result";
            $ps = $this->execute($q);
            $row = $this->getDataRowAsArray($ps);
            return $row['result'] === '1';
        }


releaseMutex
~~~~~~~~~~~~

NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.

.. code-block:: php5

    <?php
        public function releaseMutex($name) {
            $lock_name = $this->config->getValue('db_name').'.'.$name;
            /*
             $q = "SELECT RELEASE_LOCK(':name') AS result";
             $vars = array(
             ':name' => $lock_name
             );
             $ps = $this->execute($q, $vars);
             */
            $q = "SELECT RELEASE_LOCK('".$lock_name."') AS result";
            $ps = $this->execute($q);
            $row = $this->getDataRowAsArray($ps);
            return $row['result'] === '1';
        }


isMutexFree
~~~~~~~~~~~

NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.

.. code-block:: php5

    <?php
        public function isMutexFree($name) {
            $lock_name = $this->config->getValue('db_name').'.'.$name;
            $q = "SELECT IS_FREE_LOCK('".$lock_name."') AS result";
            $ps = $this->execute($q);
            $row = $this->getDataRowAsArray($ps);
            return $row['result'] === '1';
        }


isMutexUsed
~~~~~~~~~~~

NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.

.. code-block:: php5

    <?php
        public function isMutexUsed($name) {
            $lock_name = $this->config->getValue('db_name').'.'.$name;
            $q = "SELECT IS_USED_LOCK('".$lock_name."') AS result";
            $ps = $this->execute($q);
            $row = $this->getDataRowAsArray($ps);
            return $row['result'] != null;
        }




