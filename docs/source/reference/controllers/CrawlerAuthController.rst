CrawlerAuthController
=====================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.CrawlerAuthController.php

Copyright (c) 2009-2011 Gina Trapani

CrawlerAuth Controller

Runs crawler from the command line given valid command line credentials.


Properties
----------

argc
~~~~



argv
~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** boolean $session_started


Constructor

.. code-block:: php5

    <?php
        public function __construct($argc, $argv) {
            parent::__construct(true);
            $this->argc = $argc;
            $this->argv = $argv;
        }


control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $output = "";
            $authorized = false;
    
            if (isset($this->argc) && $this->argc > 1) { // check for CLI credentials
                $session = new Session();
                $username = $this->argv[1];
                if ($this->argc > 2) {
                    $pw = $this->argv[2];
                } else {
                    $pw = getenv('THINKUP_PASSWORD');
                }
    
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($username);
                $passcheck = $owner_dao->getPass($username);
                if ($session->pwdCheck($pw, $passcheck)) {
                    $authorized = true;
                    Session::completeLogin($owner);
                } else {
                    $output = "ERROR: Incorrect username and password.";
                }
            } else { // check user is logged in on the web
                if ( $this->isLoggedIn() ) {
                    $authorized = true;
                } else {
                    $output = "ERROR: Invalid or missing username and password.";
                }
            }
    
            if ($authorized) {
                $crawler = Crawler::getInstance();
                $crawler->crawl();
            }
    
            return $output;
        }




