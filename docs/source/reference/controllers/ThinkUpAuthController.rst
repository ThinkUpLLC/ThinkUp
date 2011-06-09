ThinkUpAuthController
=====================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.ThinkUpAuthController.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp Authorized Controller

Parent controller for all logged-in user-only actions



Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
        }


control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            if ($this->isLoggedIn()) {
                return $this->authControl();
            } else {
                return $this->bounce();
            }
        }


bounce
~~~~~~

Bounce user to public page or to error page.
@TODO bounce back to original action once signed in

.. code-block:: php5

    <?php
        protected function bounce() {
            if (get_class($this)=='DashboardController' || get_class($this)=='PostController') {
                $controller = new DashboardController(true);
                return $controller->go();
            } else {
                $config = Config::getInstance();
                throw new Exception('You must <a href="'.$config->getValue('site_root_path').
                'session/login.php">log in</a> to do this.');
            }
        }




