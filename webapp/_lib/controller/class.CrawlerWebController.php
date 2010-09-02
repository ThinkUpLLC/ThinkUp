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
        $this->addSuccessMessage('You just started the ThinkUp crawler. Its log output will appear below. '.
        'This may take awhile.<br /> Please wait...');
        $this->addInfoMessage('<b>Hint</b><br > Run the crawler at your server\'s command line '.
        'instead of on this slow web page using this command:<br />'.
        '<code style="font-family:Courier">cd '.THINKUP_WEBAPP_PATH.
        'crawler/;export THINKUP_PASSWORD=yourtupassword; php crawl.php '.
        $this->getLoggedInUser().'</code><br />'.
        'Just replace yourtupassword with your actual ThinkUp password. Cron that job to run hourly.');
        echo $this->generateView();
        echo '<br /><br /><textarea rows="65" cols="110">';
        $config = Config::getInstance();
        $config->setValue('log_location', false); //this forces output to just echo to page
        $crawler = Crawler::getInstance();
        $crawler->crawl();
        echo '</textarea>';
        $this->setViewTemplate('crawler.run-bottom.tpl');
        echo $this->generateView();
    }
}