CrawlerWebController
====================
Inherits from `ThinkUpAuthAPIController <./ThinkUpAuthAPIController.html>`_.

ThinkUp/webapp/_lib/controller/class.CrawlerWebController.php

Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau

Crawler Web Controller

Runs crawler from the web for the logged-in user and outputs logging into a text area.



Methods
-------

authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            Utils::defineConstants();
    
            if ($this->isAPICall()) {
                // If the request comes from an API call, output JSON instead of HTML
                $this->setContentType('application/json; charset=UTF-8');
            } else {
                $this->setContentType('text/html; charset=UTF-8');
                $this->setViewTemplate('crawler.run-top.tpl');
                echo $this->generateView();
                $config = Config::getInstance();
                $config->setValue('log_location', false); //this forces output to just echo to page
                $logger = Logger::getInstance();
                $logger->close();
            }
    
            try {
                $logger = Logger::getInstance();
                if (isset($_GET['log']) && $_GET['log'] == 'full') {
                    $logger->setVerbosity(Logger::ALL_MSGS);
                    echo '<pre style="font-family:Courier;font-size:10px;">';
                } else {
                    $logger->setVerbosity(Logger::USER_MSGS);
                    $logger->enableHTMLOutput();
                }
                $crawler = Crawler::getInstance();
                $crawler->crawl();
                $logger->close();
            } catch (CrawlerLockedException $e) {
                if ($this->isAPICall()) {
                    // Will be caught and handled in ThinkUpController::go()
                    throw $e;
                } else {
                    // Will appear in the textarea of the HTML page
                    echo $e->getMessage();
                }
            }
    
            if ($this->isAPICall()) {
                echo json_encode((object) array('result' => 'success'));
            } else {
                $this->setViewTemplate('crawler.run-bottom.tpl');
                echo $this->generateView();
            }
        }




