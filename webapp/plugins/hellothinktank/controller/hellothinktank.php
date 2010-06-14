<?php 
/* 
 Plugin Name: Hello ThinkTank
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/webapp/plugins/hellothinktank/
 Description: The "Hello, world!" of ThinkTank plugins.
 Version: 0.01
 Icon: assets/img/plugin_icon.png
 Author: Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('hellothinktank', 'HelloThinkTankPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('HelloThinkTankPlugin');
