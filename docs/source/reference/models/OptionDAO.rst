OptionDAO
=========

ThinkUp/webapp/_lib/model/interface.OptionDAO.php

Copyright (c) 2009-2011 Mark Wilkie

Option Data Access Object interface



Methods
-------

insertOption
~~~~~~~~~~~~
* **@param** $str Namespace
* **@param** $str A name
* **@param** $str A option value
* **@throws** DuplicateOptionException
* **@return** $int Inserted option ID


Add/Insert a plugin option by nanmespace and name

.. code-block:: php5

    <?php
        public function insertOption($namespace, $name, $value);


updateOption
~~~~~~~~~~~~
* **@param** int A option id
* **@param** str A option value
* **@param** str An optional name value
* **@return** int Number of records updated


Update a plugin option by id

.. code-block:: php5

    <?php
        public function updateOption($id, $value, $name = null);


getOptionByName
~~~~~~~~~~~~~~~
* **@param** str namespace
* **@param** str A key/name
* **@param** bool $cached Whether or not to retrieved cached option, default to false
* **@return** Option An Option object


Get a plugin option

.. code-block:: php5

    <?php
        public function getOptionByName($namespace, $name);


getOption
~~~~~~~~~
* **@param** int Option id
* **@param** bool $cached Whether or not to retrieved cached option, default to false
* **@return** Option An Option object


Get a plugin option by id

.. code-block:: php5

    <?php
        public function getOption($option_id);


deleteOption
~~~~~~~~~~~~
* **@param** int A option id
* **@return** bool If successful or not


Delete a option by id

.. code-block:: php5

    <?php
        public function deleteOption($option_id);


deleteOptionByName
~~~~~~~~~~~~~~~~~~
* **@param** str A namespace
* **@param** str A names
* **@return** bool If successful or not


Delete a option by namespace and name

.. code-block:: php5

    <?php
        public function deleteOptionByName($namespace, $name);


getOptions
~~~~~~~~~~
* **@param** str namespace
* **@param** bool $cached Whether or not to retrieved cached options, (optional) defaults to false
* **@return** array A hash table of Options with option_name as the key


Get a hash of Option objects keyed on option name

.. code-block:: php5

    <?php
        public function getOptions($namespace, $cached = false);


getOptionValue
~~~~~~~~~~~~~~
* **@param** str namespace
* **@param** str name
* **@param** bool Return a cached version if in the cache, (optional) defaults to false.
* **@return** str Option value


Get a option value by namespace and name

.. code-block:: php5

    <?php
        public function getOptionValue($namespace, $name, $cached = false);


isOptionsTable
~~~~~~~~~~~~~~
* **@return** bool Whether or not an options table exists


Check if the options table exists

.. code-block:: php5

    <?php
        public function isOptionsTable();




