<?php 
/*
 Plugin Name: LongURL
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/longurl/
 Description: Expands shortened links using the LongURL.org API. (ATTN: LongURL.org IS CURRENTLY DOWN; DOES NOT WORK.)
 Icon: plugin_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

function longurl_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $ldao = new LinkDAO($db, $logger);
    $linkstoexpand = $ldao->getLinksToExpand();
    
    $lapi = new LongUrlAPIAccessor($THINKTANK_CFG['app_title'], $logger);
    foreach ($linkstoexpand as $l) {
        $eurl = $lapi->expandUrl($l->url);
        if ($eurl['long-url'] != '') {
            $ldao->saveExpandedUrl($l->id, $eurl['long-url']);
        }
    }
    $logger->close(); # Close logging
}

function longurl_webapp_configuration() {
}


$crawler->registerCallback('longurl_crawl', 'crawl');

$webapp->addToConfigMenu('longurl', 'Twitter');

$webapp->registerCallback('longurl_webapp_configuration', 'configuration|longurl');
?>
