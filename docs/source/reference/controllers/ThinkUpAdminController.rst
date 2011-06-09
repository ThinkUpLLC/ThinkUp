ThinkUpAdminController
======================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.ThinkUpAdminController.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp Admin Controller

Parent controller for all logged-in admin user-only actions.



Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
        }


authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            if ($this->isAdmin()) {
                return $this->adminControl();
            } else {
                throw new Exception("You must be a ThinkUp admin to do this");
            }
        }




