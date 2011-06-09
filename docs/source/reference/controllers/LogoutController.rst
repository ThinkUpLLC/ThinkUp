LogoutController
================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.LogoutController.php

Copyright (c) 2009-2011 Gina Trapani

Logout Controller

Log out of ThinkUp.



Methods
-------

authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            $this->app_session->logout();
            $controller = new DashboardController(true);
            $controller->addSuccessMessage("You have successfully logged out.");
            return $controller->go();
        }




