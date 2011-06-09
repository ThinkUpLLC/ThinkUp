CheckCrawlerController
======================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.CheckCrawlerController.php

Copyright (c) 2009-2011 Gina Trapani

CheckCrawler Controller
Outputs a message if crawler hasn't run in over 3 hours.


Properties
----------

threshold
~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** boolean $session_started


Constructor

.. code-block:: php5

    <?php
        public function __construct($session_started=false, $argc = null, $argv = null) {
            parent::__construct($session_started);
            $this->setViewTemplate('crawler.checkcrawler.tpl');
            $this->disableCaching();
            $this->profiler_enabled = false;
    
            $this->threshold = isset($argv[1]) ? floatval($argv[1]) : 3.0;
        }


control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $hours_since_last_crawl = $instance_dao->getHoursSinceLastCrawlerRun();
            if (isset($hours_since_last_crawl) && $hours_since_last_crawl > $this->threshold)  {
                $this->addToView('message', "Crawler hasn't run in ".round($hours_since_last_crawl)." hours");
            }
            return $this->generateView();
        }




