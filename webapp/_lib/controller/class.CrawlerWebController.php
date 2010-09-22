<?php
/**
 * Crawler Web Controller
 *
 * Runs crawler from the web for the logged-in user and outputs logging into a text area.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerWebController extends ThinkUpAuthAPIController {

    public function authControl() {
        Utils::defineConstants();

        if ($this->isAPICall()) {
            // If the request comes from an API call, output JSON instead of HTML
            $this->setContentType('application/json; charset=UTF-8');
        } else {
            $this->setPageTitle("ThinkUp Crawler");
            $this->setViewTemplate('crawler.run-top.tpl');
            $whichphp = exec('which php');
            $php_path =  (!empty($whichphp))?$whichphp:'php';
            $this->addSuccessMessage('ThinkUp has just started to collect your posts. This is going to take a little '.
            'while, but if you want to see the technical details of what\'s going on, there\'s a log below. ');
            $rss_url = THINKUP_BASE_URL.'rss.php?'.ThinkUpAuthAPIController::getAuthParameters($this->getLoggedInUser());
            $this->addInfoMessage('<b>Hint</b><br />You can automate ThinkUp crawls by subscribing to '.
            '<strong><a href="'.$rss_url.'" target="_blank">this RSS feed</a></strong> '.
            'in your favorite RSS reader.<br /><br /> Alternately, use the command below to set up a cron job that '.
            'runs hourly to update your posts. (Be sure to change yourpassword to your real password!)<br /><br />'.
            '<code style="font-family:Courier">cd '.THINKUP_WEBAPP_PATH.
            'crawler/;export THINKUP_PASSWORD=yourpassword; '.$php_path.' crawl.php '.$this->getLoggedInUser().
            '</code>');
            echo $this->generateView();
            echo '<br /><br /><textarea rows="65" cols="110">';

            $config = Config::getInstance();
            $config->setValue('log_location', false); //this forces output to just echo to page
            $logger = Logger::getInstance();
            $logger->close();
        }

        try {
            $crawler = Crawler::getInstance();
            $crawler->crawl();
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
            echo '</textarea>';
            $this->setViewTemplate('crawler.run-bottom.tpl');
            echo $this->generateView();
        }
    }
}