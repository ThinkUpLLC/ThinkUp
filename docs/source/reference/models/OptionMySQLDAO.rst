OptionMySQLDAO
==============
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.OptionMySQLDAO.php

Copyright (c) 2009-2011 Mark Wilkie

Option Data Access Object

The data access object for retrieving and saving generic ThinkUp options and their values.



Methods
-------

insertOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function insertOption($namespace, $name, $value) {
            $option = $this->getOptionByName($namespace, $name);
            if($option) {
                throw new DuplicateOptionException("An option with the namespace $namespace and name $name exists");
            }
            $q = 'INSERT INTO #prefix#options
                    (namespace, option_name, option_value, created, last_updated)
                VALUES
                    (:namespace, :option_name, :option_value, now(), now())';
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q,
            array(':namespace' => $namespace, ':option_name' => $name, ':option_value' => $value) );
            $this->clearSessionData($namespace);
            return $this->getInsertId($stmt);
        }


updateOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updateOption($id, $value, $name = null) {
            $option = $this->getOption($id);
            if($option) {
                $q = 'UPDATE #prefix#options set option_value = :option_value, last_updated = now() ';
                if($name) {
                    $q .= ', option_name  = :option_name';
                }
                $q .= ' WHERE option_id = :option_id';
                $data = array(':option_id' => $id, ':option_value' => $value);
                if($name) {
                    $data[':option_name'] = $name;
                }
                if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
                $stmt = $this->execute($q, $data);
                $this->clearSessionData($option->namespace);
                return $this->getUpdateCount($stmt);
            } else {
                return 0;
            }
        }


updateOptionByName
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updateOptionByName($namespace, $name, $value) {
            $q = 'UPDATE #prefix#options set option_value = :option_value, last_updated = now()
                WHERE namespace = :namespace AND option_name = :option_name';
            $binds = array(':namespace' => $namespace, ':option_name' => $name, 'option_value' => $value);
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q, $binds);
            $this->clearSessionData($namespace);
            return $this->getUpdateCount($stmt);
        }


getOptionByName
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOptionByName($namespace, $name){
            $q = 'SELECT option_id, namespace, option_name, option_value FROM #prefix#options
                WHERE namespace = :namespace AND option_name = :option_name';
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q, array(':namespace' => $namespace, ':option_name' => $name));
            $option = $this->getDataRowAsObject($stmt, 'Option');
            return $option;
        }


getOption
~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOption($option_id){
            $q = 'SELECT option_id, namespace, option_name, option_value FROM #prefix#options
                WHERE option_id = :option_id';
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q, array(':option_id' => $option_id));
            $option = $this->getDataRowAsObject($stmt, 'Option');
            return $option;
        }


deleteOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteOption($option_id){
            $option = $this->getOption($option_id);
            if($option) {
                $q = 'DELETE FROM #prefix#options WHERE option_id = :option_id';
                if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
                $stmt = $this->execute($q, array(':option_id' => $option_id));
                $this->clearSessionData($option->namespace);
                return $this->getUpdateCount($stmt);
                $this->clearSessionData($namespace);
            } else {
                return 0;
            }
        }


deleteOptionByName
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteOptionByName($namespace, $name){
            $q = 'DELETE FROM #prefix#options WHERE namespace = :namespace AND option_name = :name';
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q, array(':namespace' => $namespace, ':name' => $name));
            $this->clearSessionData($namespace);
            return $this->getUpdateCount($stmt);
        }


getOptions
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOptions($namespace, $cached = false) {
            $data = null;
            if($cached) {
                $data = $this->getSessionData($namespace);
            }
            if(is_null($data)) {
                $q = 'SELECT option_id, namespace,  option_name, option_value
                        FROM #prefix#options 
                        WHERE namespace = :namespace';
                if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
                $stmt = $this->execute($q, array(':namespace' => $namespace));
                $res = $this->getDataRowsAsArrays($stmt);
                if(count($res ) == 0) {
                    $data = null;
                } else {
                    $data = array();
                    foreach($res as $option_array) {
                        $option = new Option($option_array);
                        $data[$option->option_name] = $option;
                    }
                }
            }
            if($cached) {
                $this->setSessionData($namespace, $data);
            }
            return $data;
        }


getOptionValue
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOptionValue($namespace, $name, $cached = false) {
            $options = $this->getOptions($namespace, $cached);
            if($options && isset($options[$name])) {
                return $options[$name]->option_value;
            } else {
                return null;
            }
    
        }


getSessionData
~~~~~~~~~~~~~~
* **@param** $namespace
* **@retrun** $array Hash of option data


Gets option data from session using namespace as a key

.. code-block:: php5

    <?php
        public function getSessionData($namespace) {
            $key = 'options_data:' . $namespace;
            if(SessionCache::isKeySet($key) ) {
                return SessionCache::get($key);
            } else {
                return null;
            }
        }


setSessionData
~~~~~~~~~~~~~~
* **@param** $namespace
* **@param** array Hash of option data
* **@retrun** $array Hash of option data


Sets option data in the session using namespace as a key

.. code-block:: php5

    <?php
        public function setSessionData($namespace, $data) {
            $key = 'options_data:' . $namespace;
            SessionCache::put($key, $data);
        }


clearSessionData
~~~~~~~~~~~~~~~~
* **@param** $namespace


Clears session data by namespace

.. code-block:: php5

    <?php
        public function clearSessionData($namespace) {
            $key = 'options_data:' . $namespace;
            if( SessionCache::isKeySet($key)) {
                SessionCache::unsetKey($key);
            }
        }


isOptionsTable
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function isOptionsTable() {
            $q = "show tables like '#prefix#options'";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q);
            $data = $this->getDataRowAsArray($stmt);
            if($data) {
                return true;
            } else {
                return false;
            }
        }




