<?php 
/*
 Plugin Name: LongURL
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/longurl/
 Description: Expands shortened links using the LongURL.org API. (NOT YET IMPLEMENTED; DOES NOTHING RIGHT NOW.)
 Icon: plugin_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

function longurl_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
	
	//TODO Select all URLs from the links table that don't have an expanded url value and are shortened links
	// for each, expand and save
    
}

function longurl_webapp_configuration() {
}


$crawler->registerCallback('longurl_crawl', 'crawl');

$webapp->addToConfigMenu('longurl', 'Twitter');

$webapp->registerCallback('longurl_webapp_configuration', 'configuration|longurl');
?>
