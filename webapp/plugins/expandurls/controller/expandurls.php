<?php
/*
 Plugin Name: Expand URLs
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/expandurls/
 Description: Expands shortened links.
 Icon: assets/img/plugin_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('expandurls', 'ExpandURLsPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('ExpandURLsPlugin');
