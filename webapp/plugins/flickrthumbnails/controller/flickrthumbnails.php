<?php
/*
 Plugin Name: Flickr Thumbnails
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/flickr/
 Icon: assets/img/flickr_icon.png
 Description: Expands shortened Flickr photo links to thumbnail locations.
 Version: 0.01
 Author: Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('flickrthumbnails', 'FlickrThumbnailsPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('FlickrThumbnailsPlugin');
