Webapp
======
Inherits from `PluginHook <./PluginHook.html>`_.

ThinkUp/webapp/_lib/model/class.Webapp.php

Copyright (c) 2009-2011 Gina Trapani

Webapp

Singleton provides hooks for webapp plugins.


Properties
----------

instance
~~~~~~~~



active_plugin
~~~~~~~~~~~~~



active_plugins
~~~~~~~~~~~~~~



post_detail_menus
~~~~~~~~~~~~~~~~~



dashboard_menus
~~~~~~~~~~~~~~~





Methods
-------

getInstance
~~~~~~~~~~~
* **@return** Webapp


Get the singleton instance of Webapp

.. code-block:: php5

    <?php
        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new Webapp();
            }
            return self::$instance;
        }


destroyInstance
~~~~~~~~~~~~~~~

Provided only for tests that want to kill object in tearDown()

.. code-block:: php5

    <?php
        public static function destroyInstance() {
            if (isset(self::$instance)) {
                self::$instance = null;
            }
        }


getActivePlugin
~~~~~~~~~~~~~~~
* **@return** str Name of active plugin (like "twitter" or "facebook")


Returns active plugin

.. code-block:: php5

    <?php
        public function getActivePlugin() {
            return $this->active_plugin;
        }


setActivePlugin
~~~~~~~~~~~~~~~
* **@param** string $ap


Sets active plugin

.. code-block:: php5

    <?php
        public function setActivePlugin($ap) {
            $this->active_plugin = $ap;
        }


getDashboardMenu
~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getDashboardMenu($instance) {
            if ($this->dashboard_menus === null) {
                $this->dashboard_menus = array();
                $plugin_class_name = $this->getPluginObject($this->active_plugin);
                $p = new $plugin_class_name;
                if ($p instanceof DashboardPlugin) {
                    $this->dashboard_menus = $p->getDashboardMenuItems($instance);
                }
            }
            return $this->dashboard_menus;
        }


getPostDetailMenu
~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getPostDetailMenu($post) {
            if ($this->post_detail_menus === null) {
                $this->post_detail_menus = array();
                //Get all active plugins
                $plugin_dao = DAOFactory::getDAO('PluginDAO');
                $this->active_plugins = $plugin_dao->getActivePlugins();
                //For each active plugin, check if getPostDetailMenu method exists
                foreach ($this->active_plugins as $plugin) {
                    $plugin_class_name = $this->getPluginObject($plugin->folder_name);
                    //if so, add to sidebar_menu
                    $p = new $plugin_class_name;
                    if ($p instanceof PostDetailPlugin) {
                        $menus = $p->getPostDetailMenuItems($post);
                        $this->post_detail_menus = array_merge($this->post_detail_menus, $menus);
                    }
                }
            }
            return $this->post_detail_menus;
        }


getDashboardMenuItem
~~~~~~~~~~~~~~~~~~~~
* **@param** str $menu_item_short_name
* **@param** Instance $instance
* **@return** MenuItem for instance, null if none available for given short name


Get individual Dashboard MenuItem

.. code-block:: php5

    <?php
        public function getDashboardMenuItem($menu_item_short_name, $instance) {
            if ($this->dashboard_menus === null) {
                $this->getDashboardMenu($instance);
            }
            if ( isset($this->dashboard_menus[$menu_item_short_name]) ) {
                return $this->dashboard_menus[$menu_item_short_name];
            } else {
                return null;
            }
        }


getPostDetailMenuItem
~~~~~~~~~~~~~~~~~~~~~
* **@param** str $menu_item_short_name
* **@param** Post $post
* **@return** MenuItem for instance, null if none available for given short name


Get individual post detail MenuItem

.. code-block:: php5

    <?php
        public function getPostDetailMenuItem($menu_item_short_name, $post) {
            if ($this->post_detail_menus === null) {
                $this->getPostDetailMenu($post);
            }
            if ( isset($this->post_detail_menus[$menu_item_short_name]) ) {
                return $this->post_detail_menus[$menu_item_short_name];
            } else {
                return null;
            }
        }




