ToggleActivePluginController
============================
Inherits from `ThinkUpAdminController <./ThinkUpAdminController.html>`_.

ThinkUp/webapp/_lib/controller/class.ToggleActivePluginController.php

Copyright (c) 2009-2011 Gina Trapani

Toggle Active Plugin Controller
Activate or deactivat a plugin.


Properties
----------

REQUIRED_PARAMS
~~~~~~~~~~~~~~~

Required query string parameters

is_missing_param
~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
            $this->setViewTemplate('session.toggle.tpl');
            foreach ($this->REQUIRED_PARAMS as $param) {
                if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                    $this->addInfoMessage('Missing required parameters.');
                    $this->is_missing_param = true;
                }
            }
        }


adminControl
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function adminControl(){
            if (!$this->is_missing_param) {
                $is_active = ($_GET["a"] != 1)?false:true;
                $plugin_dao = DAOFactory::getDAO('PluginDAO');
                $result = $plugin_dao->setActive($_GET["pid"], $is_active);
                if ($result > 0 ) {
                    $plugin_folder = $plugin_dao->getPluginFolder($_GET["pid"]);
                    $webapp = Webapp::getInstance();
                    try {
                        $plugin_class_name = $webapp->getPluginObject($plugin_folder);
                        $p = new $plugin_class_name;
                        if ($is_active) {
                            $p->activate();
                        } else {
                            $p->deactivate();
                        }
                    } catch (Exception $e) {
                        //plugin object isn't registered, do nothing
                        //echo $e->getMessage();
                    }
                }
                $this->addToView('result', $result);
                $this->view_mgr->clear_all_cache();
            }
            return $this->generateView();
        }




