ToggleActiveInstanceController
==============================
Inherits from `ThinkUpAdminController <./ThinkUpAdminController.html>`_.

ThinkUp/webapp/_lib/controller/class.ToggleActiveInstanceController.php

Copyright (c) 2009-2011 Gina Trapani

Toggle Active Instance Controller
Set an instance active or inactive.


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
                $is_active = ($_GET["p"] != 1)?false:true;
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $this->addToView('result', $instance_dao->setActive($_GET["u"], $is_active));
            }
            return $this->generateView();
        }




