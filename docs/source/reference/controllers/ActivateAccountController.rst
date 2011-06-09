ActivateAccountController
=========================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.ActivateAccountController.php

Copyright (c) 2009-2011 Gina Trapani

Activate Account Controller
When a user registers for a ThinkUp account s/he receives an email with an activation link that lands here.


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
* **@param** bool $session_started
* **@return** ActivateAccountController


Constructor

.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
            foreach ($this->REQUIRED_PARAMS as $param) {
                if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                    $this->is_missing_param = true;
                }
            }
        }


control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $controller = new LoginController(true);
            if ($this->is_missing_param) {
                $controller->addErrorMessage('Invalid account activation credentials.');
            } else {
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $acode = $owner_dao->getActivationCode($_GET['usr']);
    
                if ($_GET['code'] == $acode['activation_code']) {
                    $owner_dao->activateOwner($_GET['usr']);
                    $controller->addSuccessMessage("Success! Your account has been activated. Please log in.");
                } else {
                    $controller->addErrorMessage('Houston, we have a problem: Account activation failed.');
                }
            }
            return $controller->go();
        }




