PluginOptionMySQLDAO
====================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.PluginOptionMySQLDAO.php

Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani

Plugin Option Data Access Object

The data access object for retrieving and saving plugin options.


Properties
----------

cached_options
~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct() {
            $this->option_dao = DAOFactory::getDAO('OptionDAO');
            $this->namespace = OptionDAO::PLUGIN_OPTIONS;
    
        }


deleteOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteOption($id) {
            $count = $this->option_dao->deleteOption($id);
            if($count == 1) {
                return true;
            } else {
                return false;
            }
        }


insertOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function insertOption($plugin_id, $name, $value) {
            $namespace = $this->namespace . '-' . $plugin_id;
            return $this->option_dao->insertOption($namespace, $name, $value);
        }


updateOption
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updateOption($id, $name, $value) {
    
            $cnt = $this->option_dao->updateOption($id, $value, $name);
            if($cnt > 0) {
                return true;
            } else {
                return false;
            }
        }


getOptions
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOptions($plugin_folder, $cached = false) {
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $plugin_id = $plugin_dao->getPluginId($plugin_folder);
            if($plugin_id) {
                $namespace = $this->namespace . '-' . $plugin_id;
                $options =  $this->option_dao->getOptions($namespace, $cached);
                $plugin_opts = array();
                if($options) {
                    foreach($options as $option) {
                        $plugin_opt = new PluginOption();
                        $plugin_opt->id = $option->option_id;
                        $plugin_opt->plugin_id = $plugin_id;
                        $plugin_opt->option_name = $option->option_name;
                        $plugin_opt->option_value = $option->option_value;
                        array_push($plugin_opts, $plugin_opt);
                    }
                }
                return $plugin_opts;
            } else {
                return null;
            }
        }


getOptionsHash
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOptionsHash($plugin_folder, $cached = false) {
            $options = $this->getOptions($plugin_folder, $cached);
            $options_hash = array();
            if (count( $options) > 0 ) {
                foreach ($options as $option) {
                    $options_hash[ $option->option_name ] = $option;
                }
            }
            return $options_hash;
        }




