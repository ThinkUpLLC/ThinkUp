<?php
/**
 * Crawler Web Controller
 *
 * Runs crawler from the web for the logged-in user and outputs logging into a text area.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerWebController extends ThinkUpAuthController {

    public function authControl() {
        Utils::defineConstants();
        $this->setPageTitle("ThinkUp Crawler");
        $this->setViewTemplate('crawler.run-top.tpl');
        $whichphp = exec('which php');
        $php_path =  (!empty($whichphp))?$whichphp:'php';
        $this->addSuccessMessage('ThinkUp has just started to collect your posts. This is going to take a little '.
        'while, but if you want to see the technical details of what\'s going on, there\'s a log below. ');
        $this->addInfoMessage('<b>Hint</b><br > If you prefer using the command line, use this command to set up a '.
        'cron job that runs hourly to update your posts:<br />'.
        '<code style="font-family:Courier">cd '.THINKUP_WEBAPP_PATH.
        'crawler/;export THINKUP_PASSWORD=yourpassword; '.$php_path.' crawl.php '.
        $this->getLoggedInUser().'</code><br />'.
        '(Be sure to change yourpassword to your real password!)');
        echo $this->generateView();
        echo '<br /><br /><textarea rows="65" cols="110">';
        $config = Config::getInstance();
        $config->setValue('log_location', false); //this forces output to just echo to page
        $logger = Logger::getInstance();
        $logger->close();
        $crawler = Crawler::getInstance();
        $crawler->crawl();
        echo '</textarea>';
        $this->setViewTemplate('crawler.run-bottom.tpl');
        echo $this->generateView();
    }
}