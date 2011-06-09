PluginDAO
=========

ThinkUp/webapp/_lib/model/interface.PluginDAO.php

Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani

Plugin Data Access Object interface



Methods
-------

getAllPlugins
~~~~~~~~~~~~~
* **@param** bool Only get active plugins
* **@return** array A list of Plugin objects


Get all plugins

.. code-block:: php5

    <?php
        public function getAllPlugins($isactive = false);


getActivePlugins
~~~~~~~~~~~~~~~~
* **@return** array A list of active Plugin objects


Get all active plugins

.. code-block:: php5

    <?php
        public function getActivePlugins();


isPluginActive
~~~~~~~~~~~~~~
* **@param** int A plugin ID
* **@return** bool


Determine if a plugin is active

.. code-block:: php5

    <?php
        public function isPluginActive($id);


insertPlugin
~~~~~~~~~~~~
* **@throws** BadArgumentException If param is not a Plugin object
* **@param** Plugin A plugin data object
* **@return** bool Whether or not it was insertedss


Inserts a plugin record

.. code-block:: php5

    <?php
        public function insertPlugin($plugin);


updatePlugin
~~~~~~~~~~~~
* **@throws** BadArgumentException If param is not a Plugin object
* **@return** bool Successfully updated


Updates a plugin record

.. code-block:: php5

    <?php
        public function updatePlugin($plugin);


getPluginId
~~~~~~~~~~~
* **@param** str A folder name
* **@return** int A plugin id


Gets a plugin record by folder name

.. code-block:: php5

    <?php
        public function getPluginId($folder_name);


getPluginFolder
~~~~~~~~~~~~~~~
* **@param** int A plugin id
* **@return** str A plugin folder name


Gets a plugin folder name by id

.. code-block:: php5

    <?php
        public function getPluginFolder($plugin_id);


setActive
~~~~~~~~~
* **@param** int Plugin ID
* **@param** bool Active flag, 1 if activating, 0 if deactivating
* **@return** int number of updated rows


Set a plugin's active flag

.. code-block:: php5

    <?php
        public function setActive($plugin_id, $is_active);


getInstalledPlugins
~~~~~~~~~~~~~~~~~~~
* **@param** str Plugin path
* **@return** array Installed plugins


Detect what plugins exist in the filesystem; parse their header comments for plugin metadata

.. code-block:: php5

    <?php
        public function getInstalledPlugins($plugin_path);


isValidPluginId
~~~~~~~~~~~~~~~
* **@param** int A plugin id
* **@return** bool If valid


Validate a plugin id

.. code-block:: php5

    <?php
        public function isValidPluginId($plugin_id);




