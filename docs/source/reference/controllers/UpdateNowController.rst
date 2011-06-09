UpdateNowController
===================
Inherits from `ThinkUpAuthAPIController <./ThinkUpAuthAPIController.html>`_.

ThinkUp/webapp/_lib/controller/class.UpdateNowController.php

Copyright (c) 2009-2011 Gina Trapani

Update Now Controller

Runs crawler from the web for the logged-in user and outputs logging into a text area.



Methods
-------

authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            Utils::defineConstants();
            $this->setContentType('text/html; charset=UTF-8');
            $this->setPageTitle("ThinkUp Crawler");
            $this->setViewTemplate('crawler.updatenow.tpl');
            $whichphp = @exec('which php');
            $php_path =  (!empty($whichphp))?$whichphp:'php';
            $rss_url = THINKUP_BASE_URL.'rss.php?'.ThinkUpAuthAPIController::getAuthParameters($this->getLoggedInUser());
            $this->addInfoMessage('<b>Hint</b><br />You can automate ThinkUp crawls by subscribing to '.
                '<strong><a href="'.$rss_url.'" target="_blank">this RSS feed</a></strong> '.
                'in your favorite RSS reader.<br /><br /> Alternately, use the command below to set up a cron job that '.
                'runs hourly to update your posts. (Be sure to change yourpassword to your real password!)<br /><br />'.
                '<code style="font-family:Courier">cd '.THINKUP_WEBAPP_PATH.
                'crawler/;export THINKUP_PASSWORD=yourpassword; '.$php_path.' crawl.php '.$this->getLoggedInUser().
                '</code>');
            if (isset($_GET['log']) && $_GET['log'] == 'full') {
                $this->addToView('log', 'full');
            }
            return $this->generateView();
        }




