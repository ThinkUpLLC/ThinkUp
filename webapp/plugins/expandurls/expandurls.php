<?php 
/*
 Plugin Name: Expand URLs
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/expandurls/
 Description: Expands shortened links.
 Icon: plugin_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

function expandurls_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $ldao = new LinkDAO($db, $logger);
	//TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 500
    $linkstoexpand = $ldao->getLinksToExpand(500);
    
	$logger->logStatus(count($linkstoexpand)." links to expand", "Expand URLs Plugin");
	
    foreach ($linkstoexpand as $l) {
        $eurl = untinyurl($l, $logger, $ldao);
        if ($eurl != '') {
            $ldao->saveExpandedUrl($l, $eurl);
        }
    }
	$logger->logStatus("URL expansion complete for this run", "Expand URLs Plugin");
    $logger->close(); # Close logging
}

function expandurls_webapp_configuration() {

}

//Thanks to Probably Programming
//http://probablyprogramming.com/2009/04/11/untiny-that-url/
function untinyurl($tinyurl, $logger, $ldao) {
    $url = parse_url($tinyurl);
    $host = $url['host'];
    $port = isset($url['port']) ? $url['port'] : 80;
    $query = isset($url['query']) ? '?'.$url['query'] : '';
    $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
    
    $sock = @fsockopen($host, $port);
    if (!$sock) {
        return $tinyurl;
    }
    
    if (!isset($url['path'])) {
        $logger->logstatus("$tinyurl has no path", "Expand URLs Plugin");
        $ldao->saveExpansionError($tinyurl, "Error expanding URL");
        return '';
    } else {
        $url = $url['path'].$query.$fragment;
        $request = "HEAD {$url} HTTP/1.0\r\nHost: {$host}\r\nConnection: Close\r\n\r\n";
        
        fwrite($sock, $request);
        $response = '';
        while (!feof($sock)) {
            $response .= fgets($sock, 128);
        }
        $lines = explode("\r\n", $response);
        foreach ($lines as $line) {
            if (strpos(strtolower($line), 'location:') === 0) {
                list(, $location) = explode(':', $line, 2);
                return ltrim($location);
            }
        }
        return $tinyurl;
    }
}

$crawler->registerCallback('expandurls_crawl', 'crawl');

$webapp->addToConfigMenu('expandurls', 'Expand URLs');

$webapp->registerCallback('expandurls_webapp_configuration', 'configuration|expandurls');
?>
