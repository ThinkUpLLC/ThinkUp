MarkParentController
====================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.MarkParentController.php

Copyright (c) 2009-2011 Gina Trapani

Mark Parent Controller

Mark a post the parent of a reply.


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
                $template = $_GET["t"];
                $cache_key = $_GET["ck"];
                $pid = $_GET["pid"];
                $oid =  $_GET["oid"];
                $network = $_GET['n'];
                $config = Config::getInstance();
    
                $post_dao = DAOFactory::getDAO('PostDAO');
                foreach ($oid as $o) {
                    if ( isset($_GET["fp"])) {
                        $result = $post_dao->assignParent($pid, $o, $network, $_GET["fp"]);
                    } else {
                        $result = $post_dao->assignParent($pid, $o, $network);
                    }
                }
    
                $s = new SmartyThinkUp();
                $s->clear_cache($template, $cache_key);
                if ($result > 0 ) {
                    $this->addToView('result', 'Assignment successful.');
                } else {
                    $this->addToView('result', 'No data was changed.');
                }
            }
            return $this->generateView();
        }




