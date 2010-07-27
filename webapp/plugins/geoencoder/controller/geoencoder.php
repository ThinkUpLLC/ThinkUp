<?php 
/*
 Plugin Name: GeoEncoder
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/geoencoder/
 Description: Geo encodes location data
 Icon: assets/img/geoencoder_icon.png
 Version: 0.01
 Author: Ekansh Preet Singh, Mark Wilkie
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('geoencoder', 'GeoEncoderPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('GeoEncoderPlugin');
