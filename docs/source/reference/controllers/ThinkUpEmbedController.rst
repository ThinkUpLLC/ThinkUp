ThinkUpEmbedController
======================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/plugins/embedthread/controller/class.ThinkUpEmbedController.php

Copyright (c) 2009-2011 Gina Trapani




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
* **@return** ThinkUpEmbedController


Constructor

.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
            foreach ($this->REQUIRED_PARAMS as $param) {
                if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                    $this->addInfoMessage('No thread data to retrieve.');
                    $this->is_missing_param = true;
                }
            }
        }


control
~~~~~~~
* **@return** str JavaScript source


Generates the calling JavaScript to create embedded thread on calling page.

.. code-block:: php5

    <?php
        public function control() {
            Utils::defineConstants();
            $this->setViewTemplate(THINKUP_WEBAPP_PATH.'_lib/view/api.embed.v1.embed.tpl');
            $this->setContentType('text/javascript');
            if (!$this->is_missing_param) {
                $this->addToView('post_id', $_GET['p']);
                $this->addToView('network', $_GET['n']);
            } else {
                $this->addErrorMessage('No ThinkUp thread specified.');
            }
            return $this->generateView();
        }




