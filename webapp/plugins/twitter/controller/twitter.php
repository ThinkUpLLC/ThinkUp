<?php 
/*
 Plugin Name: Twitter
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/webapp/plugins/twitter/
 Description: Crawler plugin fetches data from Twitter.com for the authorized user.
 Icon: assets/img/twitter_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

$webapp->registerPlugin('twitter', 'TwitterPlugin');

$crawler->registerCrawlerPlugin('TwitterPlugin');
?>
