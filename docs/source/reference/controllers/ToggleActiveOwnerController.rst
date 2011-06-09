ToggleActiveOwnerController
===========================
Inherits from `ThinkUpAdminController <./ThinkUpAdminController.html>`_.

ThinkUp/webapp/_lib/controller/class.ToggleActiveOwnerController.php

Copyright (c) 2009-2011 Gina Trapani

Toggle Active Owner Controller
Activate or deactivate an owner.


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
                $is_activated = ($_GET["a"] != 1)?false:true;
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $this->addToView('result', $owner_dao->setOwnerActive($_GET["oid"], $is_activated));
            }
            return $this->generateView();
        }




