CheckVersionController
======================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.CheckVersionController.php

Copyright (c) 2011 Gina Trapani

Check Version Controller
Generates the JavaScript to display "New version available" message in the status bar.



Methods
-------

authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            $this->setViewTemplate('install.checkversion.tpl');
            return $this->generateView();
        }




