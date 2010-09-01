<?php
/**
 * Crawler Web Controller
 *
 * Runs crawler from the web for the logged-in user and outputs logging into a text area.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerWebController extends ThinkUpAuthController {

    public function authControl() {
        echo '<p>The ThinkUp bot is running. The crawler log output will appear below.<br />Please wait....</p>';
        echo '<textarea rows="65" cols="125">';
        $config = Config::getInstance();
        $config->setValue('log_location', false); //this forces output to just echo to page
        $crawler = Crawler::getInstance();
        $crawler->crawl();
        echo '</textarea>';
    }
}