<?php
/*
 Plugin Name: Facebook
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/facebook/
 Description: Crawler plugin pulls data from Facebook for authorized users and pages.
 Icon: assets/img/facebook_icon.png
 Version: 0.01
 Author: Gina Trapani
 */
$config = Config::getInstance();
require_once $config->getValue('source_root_path').'extlib/facebook/facebook.php';

$webapp = Webapp::getInstance();
$webapp->registerPlugin('facebook', 'FacebookPlugin');
$webapp->registerPlugin('facebook page', 'FacebookPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('FacebookPlugin');
