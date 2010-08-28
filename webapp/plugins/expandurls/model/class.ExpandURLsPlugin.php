<?php
/**
 * ExpandURLs Crawler Plugin
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ExpandURLsPlugin implements CrawlerPlugin {
    /**
     * Run when the crawler does
     */
    public function crawl() {
        $logger = Logger::getInstance();
        $ldao = DAOFactory::getDAO('LinkDAO');
        //@TODO Set limit on total number of links to expand per crawler run in the plugin settings, for now 1500
        $linkstoexpand = $ldao->getLinksToExpand(1500);

        $logger->logStatus(count($linkstoexpand)." links to expand", "Expand URLs Plugin");

        foreach ($linkstoexpand as $l) {
            if (Utils::validateURL($l)) {
                $eurl = self::untinyurl($l, $ldao);
                if ($eurl != '') {
                    $ldao->saveExpandedUrl($l, $eurl);
                }
            } else {
                $logger->logStatus($l." is not a valid URL; skipping expansion", "Expand URLs Plugin");
            }
        }
        $logger->logStatus("URL expansion complete for this run", "Expand URLs Plugin");
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        //@TODO: Write controller class, echo its results
        //Set the number of links to expand per run in the options panel
    }

    /**
     * Expand a given short URL
     *
     * @param str $tinyurl Shortened URL
     * @param LinkDAO $ldao
     * @return str Expanded URL
     */
    private function untinyurl($tinyurl, $ldao) {
        $logger = Logger::getInstance();
        $url = parse_url($tinyurl);
        $host = $url['host'];
        $port = isset($url['port']) ? $url['port'] : 80;
        $query = isset($url['query']) ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        if (empty($url['path'])) {
            $logger->logstatus("$tinyurl has no path", "Expand URLs Plugin");
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
            return '';
        } else {
            $path = $url['path'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://$host:$port".$path.$query.$fragment);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $response = curl_exec($ch);
        if ($response === false) {
            $logger->logstatus("cURL error: ".curl_error($ch), "Expand URLs Plugin");
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
            $tinyurl = '';
        }
        curl_close($ch);

        $lines = explode("\r\n", $response);
        foreach ($lines as $line) {
            if (stripos($line, 'Location:') === 0) {
                list(, $location) = explode(':', $line, 2);
                return ltrim($location);
            }
        }

        if (strpos($response, 'HTTP/1.1 404 Not Found') === 0) {
            $logger->logstatus("Short URL returned '404 Not Found'", "Expand URLs Plugin");
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
            return '';
        }
        return $tinyurl;
    }

    /**
     * Safe wrapper for the feof function that implements a timeout.
     * See Example #1:
     * http://php.net/manual/en/function.feof.php
     * @param socket $fp Open socket
     * @param mixed $start Int or null
     */
    private function safe_feof($fp, &$start = null) {
        $start = microtime(true);
        return feof($fp);
    }
}
