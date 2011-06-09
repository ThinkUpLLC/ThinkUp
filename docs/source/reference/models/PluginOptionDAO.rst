PluginOptionDAO
===============

ThinkUp/webapp/_lib/model/interface.PluginOptionDAO.php

Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani

Plugin Data Access Object interface



Methods
-------

insertOption
~~~~~~~~~~~~
* **@param** int A plugin id
* **@param** str A plugin option name
* **@param** mixed A plugin option value
* **@return** int Inserted plugin option ID


Add/Insert a plugin option by plugin id

.. code-block:: php5

    <?php
        public function insertOption($plugin_id, $name, $value);


updateOption
~~~~~~~~~~~~
* **@param** int A plugin option id
* **@param** str A plugin option name
* **@param** int A plugin option value
* **@return** bool If successful or not


Update a plugin option by id

.. code-block:: php5

    <?php
        public function updateOption($id, $name, $value);


getOptions
~~~~~~~~~~
* **@param** str A plugin folder
* **@param** bool $cached Whether or not to retrieved cached options, default to false
* **@return** array A list of PluginOption objects


Get plugin options

.. code-block:: php5

    <?php
        public function getOptions($plugin_folder, $cached = false);


deleteOption
~~~~~~~~~~~~
* **@param** int A plugin option id
* **@return** bool If successful or not


Delete a plugin option by id

.. code-block:: php5

    <?php
        public function deleteOption($option_id);


getOptionsHash
~~~~~~~~~~~~~~
* **@param** str Plugin folder name
* **@param** bool $cached Whether or not to retrieved cached options, default to false
* **@return** array A hash table of Options with option_name as the key


Get a hash of Option objects keyed on option name

.. code-block:: php5

    <?php
        public function getOptionsHash($plugin_folder, $cached = false);




