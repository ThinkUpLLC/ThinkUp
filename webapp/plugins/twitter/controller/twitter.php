<?php
/*
 Plugin Name: Twitter
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/twitter/
 Description: Crawler plugin fetches data from Twitter.com for the authorized user.
 Icon: assets/img/twitter_icon.png
 Version: 0.01
 Author: Gina Trapani
 */
$config = Config::getInstance();
//@TODO: For the testing sake, check if mock class has already been loaded
//@TODO: Figure out a better way to do this
if (!class_exists('TwitterOAuth')) {
    require_once $config->getValue('source_root_path').'extlib/twitteroauth/twitteroauth.php';
}

$webapp = Webapp::getInstance();
$webapp->registerPlugin('twitter', 'TwitterPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('TwitterPlugin');
