<?php 
/* 
 Plugin Name: Hello ThinkUp
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/hellothinkup/
 Description: The "Hello, world!" of ThinkUp plugins.
 Version: 0.01
 Icon: assets/img/plugin_icon.png
 Author: Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('HelloThinkUpPlugin');
