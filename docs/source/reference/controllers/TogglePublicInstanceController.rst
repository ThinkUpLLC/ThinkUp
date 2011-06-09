TogglePublicInstanceController
==============================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.TogglePublicInstanceController.php

Copyright (c) 2009-2011 Gina Trapani

Toggle Public Instance Controller
Add/remove an instance from the public timeline.


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


authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl(){
            if (!$this->is_missing_param) {
                $is_public = ($_GET["p"] != 1)?false:true;
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $this->addToView('result', $instance_dao->setPublic($_GET["u"], $is_public));
            }
            return $this->generateView();
        }




