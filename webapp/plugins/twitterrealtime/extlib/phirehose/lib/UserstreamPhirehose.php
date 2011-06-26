<?php

require_once 'Phirehose.php';

abstract class UserstreamPhirehose extends Phirehose {

    const URL_BASE         = 'https://userstream.twitter.com/2/';
    const METHOD_USER      = 'user';
    const CONNECT_OAUTH    = 'oauth';
    const CONNECT_BASIC    = 'basic';

    protected $status_length_base = 16;	// for some reason, the userstream uses hexadecimal status lengths
    protected $auth_method;


    public static function Initialize($basic_username = NULL, $basic_password = NULL, $oauth_token = NULL, $oauth_secret = NULL) {

        if(! self::$instance instanceof UserstreamPhirehose) {
            self::$instance = new UserstreamPhirehose(
            $basic_username,
            $basic_password,
            UserstreamPhirehose::METHOD_USER
            );
        }

    }


    public function __construct($username, $password, $method = UserstreamPhirehose::METHOD_USER, $format = self::FORMAT_JSON,
    $auth_method = UserstreamPhirehose::CONNECT_OAUTH)
    {
        parent::__construct($username, $password, $method, $format);
        $this->auth_method = $auth_method;
    }


    protected function connect() {
        if($this->auth_method === UserstreamPhirehose::CONNECT_OAUTH) {
            $this->connect_oauth();
        } else {
            $this->connect_basic();
        }
    }


    /**
     * Connects to the stream URL using the configured method.
     */
    protected function connect_basic() {

        // Init state
        $connectFailures = 0;
        $tcpRetry = self::TCP_BACKOFF / 2;
        $httpRetry = self::HTTP_BACKOFF / 2;

        // Keep trying until connected (or max connect failures exceeded)
        do {

            // Check filter predicates for every connect (for filter method)
            if ($this->method == self::METHOD_FILTER) {
                $this->checkFilterPredicates();
            }
             
            // Construct URL/HTTP bits
            $url = self::URL_BASE . $this->method . '.' . $this->format;
            $urlParts = parse_url($url);
            $authCredentials = base64_encode($this->username . ':' . $this->password);
             
            // Setup params appropriately
            $requestParams = array('delimited' => 'length');
             
            // Filter takes additional parameters
            if ($this->method == self::METHOD_USER && count($this->trackWords) > 0) {
                $requestParams['track'] = implode(',', $this->trackWords);
            }
            if ($this->method == self::METHOD_USER && count($this->followIds) > 0) {
                $requestParams['follow'] = implode(',', $this->followIds);
            }

             
            // Debugging is useful
            $this->log('Connecting to twitter stream: ' . $url . ' with params: ' . str_replace("\n", '',
            var_export($requestParams, TRUE)));
             
            /**
             * Open socket connection to make POST request. It'd be nice to use stream_context_create with the native
             * HTTP transport but it hides/abstracts too many required bits (like HTTP error responses).
             */
            $errNo = $errStr = NULL;
            $scheme = ($urlParts['scheme'] == 'https') ? 'ssl://' : 'tcp://';
            $port = ($urlParts['scheme'] == 'https') ? 443 : 80;
             
            /**
             * We must perform manual host resolution here as Twitter's IP regularly rotates (ie: DNS TTL of 60 seconds) and
             * PHP appears to cache it the result if in a long running process (as per Phirehose).
             */
            $streamIPs = gethostbynamel($urlParts['host']);
            if (empty($streamIPs)) {
                throw new Exception("Unable to resolve hostname: '" . $urlParts['host'] . '"');
            }
             
            // Choose one randomly (if more than one)
            $this->log('Resolved host ' . $urlParts['host'] . ' to ' . implode(', ', $streamIPs));
            $streamIP = $streamIPs[rand(0, (count($streamIPs) - 1))];
            $this->log('Connecting to ' . $streamIP);
             
            @$this->conn = fsockopen($scheme . $streamIP, $port, $errNo, $errStr, $this->connectTimeout);
             
            // No go - handle errors/backoff
            if (!$this->conn || !is_resource($this->conn)) {
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $errNo;
                $connectFailures ++;
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'TCP failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg);
                    throw new Exception($msg, $errNo); // Throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $tcpRetry = ($tcpRetry < self::TCP_BACKOFF_MAX) ? $tcpRetry * 2 : self::TCP_BACKOFF_MAX;
                $this->log('TCP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                $errStr . ' (' . $errNo . '). Sleeping for ' . $tcpRetry . ' seconds.');
                sleep($tcpRetry);
                continue;
            }
             
            // TCP connect OK, clear last error (if present)
            $this->log('Connection established to ' . $streamIP);
            $this->lastErrorMsg = NULL;
            $this->lastErrorNo = NULL;
             
            // If we have a socket connection, we can attempt a HTTP request - Ensure blocking read for the moment
            stream_set_blocking($this->conn, 1);
             
            // Encode request data
            $postData = http_build_query($requestParams);
             
            // Do it
            fwrite($this->conn, "POST " . $urlParts['path'] . " HTTP/1.0\r\n");
            fwrite($this->conn, "Host: " . $urlParts['host'] . "\r\n");
            fwrite($this->conn, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($this->conn, "Content-length: " . strlen($postData) . "\r\n");
            fwrite($this->conn, "Accept: */*\r\n");
            fwrite($this->conn, 'Authorization: Basic ' . $authCredentials . "\r\n");
            fwrite($this->conn, 'User-Agent: ' . self::USER_AGENT . "\r\n");
            fwrite($this->conn, "\r\n");
            fwrite($this->conn, $postData . "\r\n");
            fwrite($this->conn, "\r\n");
             
            // First line is response
            list($httpVer, $httpCode, $httpMessage) = preg_split('/\s+/', trim(fgets($this->conn, 1024)), 3);
             
            // Response buffers
            $respHeaders = $respBody = '';

            // Consume each header response line until we get to body
            while ($hLine = trim(fgets($this->conn, 4096))) {
                $respHeaders .= $hLine;
            }
             
            // If we got a non-200 response, we need to backoff and retry
            if ($httpCode != 200) {
                $connectFailures ++;
                 
                // Twitter will disconnect on error, but we want to consume the rest of the response body (which is useful)
                while ($bLine = trim(fgets($this->conn, 4096))) {
                    $respBody .= $bLine;
                }
                 
                // Construct error
                $errStr = 'HTTP ERROR ' . $httpCode . ': ' . $httpMessage . ' (' . $respBody . ')';
                 
                // Set last error state
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $httpCode;
                 
                // Have we exceeded maximum failures?
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'Connection failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg);
                    throw new Exception($msg, $httpCode); // We eventually throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $httpRetry = ($httpRetry < self::HTTP_BACKOFF_MAX) ? $httpRetry * 2 : self::HTTP_BACKOFF_MAX;
                $this->log('HTTP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                $errStr . '. Sleeping for ' . $httpRetry . ' seconds.');
                sleep($httpRetry);
                continue;
                 
            } // End if not http 200
             
            // Loop until connected OK
        } while (!is_resource($this->conn) || $httpCode != 200);
         
        // Connected OK, reset connect failures
        $connectFailures = 0;
        $this->lastErrorMsg = NULL;
        $this->lastErrorNo = NULL;
         
        // Switch to non-blocking to consume the stream (important)
        stream_set_blocking($this->conn, 0);
         
        // Connect always causes the filterChanged status to be cleared
        $this->filterChanged = FALSE;
         
        // Flush stream buffer & (re)assign fdrPool (for reconnect)
        $this->fdrPool = array($this->conn);
        $this->buff = '';

    }
     
     
    protected function connect_oauth() {

        // Init state
        $connectFailures = 0;
        $tcpRetry = self::TCP_BACKOFF / 2;
        $httpRetry = self::HTTP_BACKOFF / 2;

        // Keep trying until connected (or max connect failures exceeded)
        do {

            // Check filter predicates for every connect (for filter method)
            if ($this->method == self::METHOD_FILTER) {
                $this->checkFilterPredicates();
            }
             
            // Construct URL/HTTP bits
            $url = self::URL_BASE . $this->method . '.' . $this->format;
            $urlParts = parse_url($url);
            $authCredentials = base64_encode($this->username . ':' . $this->password);
             
            // Setup params appropriately
            $requestParams = array('delimited' => 'length');
             
            // Filter takes additional parameters
            if ($this->method == self::METHOD_USER && count($this->trackWords) > 0) {
                $requestParams['track'] = implode(',', $this->trackWords);
            }
            if ($this->method == self::METHOD_USER && count($this->followIds) > 0) {
                $requestParams['follow'] = implode(',', $this->followIds);
            }

             
            // Debugging is useful
            $this->log('Connecting to twitter stream: ' . $url . ' with params: ' . str_replace("\n", '',
            var_export($requestParams, TRUE)));
             
            /**
             * Open socket connection to make POST request. It'd be nice to use stream_context_create with the native
             * HTTP transport but it hides/abstracts too many required bits (like HTTP error responses).
             */
            $errNo = $errStr = NULL;
            $scheme = ($urlParts['scheme'] == 'https') ? 'ssl://' : 'tcp://';
            $port = ($urlParts['scheme'] == 'https') ? 443 : 80;
             
            /**
             * We must perform manual host resolution here as Twitter's IP regularly rotates (ie: DNS TTL of 60 seconds) and
             * PHP appears to cache it the result if in a long running process (as per Phirehose).
             */
            $streamIPs = gethostbynamel($urlParts['host']);
            if (empty($streamIPs)) {
                throw new Exception("Unable to resolve hostname: '" . $urlParts['host'] . '"');
            }
             
            // Choose one randomly (if more than one)
            $this->log('Resolved host ' . $urlParts['host'] . ' to ' . implode(', ', $streamIPs));
            $streamIP = $streamIPs[rand(0, (count($streamIPs) - 1))];
            $this->log('Connecting to ' . $streamIP);
             
            @$this->conn = fsockopen($scheme . $streamIP, $port, $errNo, $errStr, $this->connectTimeout);
             
            // No go - handle errors/backoff
            if (!$this->conn || !is_resource($this->conn)) {
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $errNo;
                $connectFailures ++;
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'TCP failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg);
                    throw new Exception($msg, $errNo); // Throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $tcpRetry = ($tcpRetry < self::TCP_BACKOFF_MAX) ? $tcpRetry * 2 : self::TCP_BACKOFF_MAX;
                $this->log('TCP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                $errStr . ' (' . $errNo . '). Sleeping for ' . $tcpRetry . ' seconds.');
                sleep($tcpRetry);
                continue;
            }
             
            // TCP connect OK, clear last error (if present)
            $this->log('Connection established to ' . $streamIP);
            $this->lastErrorMsg = NULL;
            $this->lastErrorNo = NULL;
             
            // If we have a socket connection, we can attempt a HTTP request - Ensure blocking read for the moment
            stream_set_blocking($this->conn, 1);
             
            // Encode request data
            $postData = http_build_query($requestParams);
             
            // Oauth tokens
            $oauthHeader = $this->getOAuthHeader('POST', $url);
             
            // Do it
            fwrite($this->conn, "POST " . $urlParts['path'] . " HTTP/1.1\r\n");
            fwrite($this->conn, "Host: " . $urlParts['host'].':'.$port . "\r\n");
            #fwrite($this->conn, "Content-type: application/x-www-form-urlencoded\r\n");
            #fwrite($this->conn, "Content-length: " . strlen($postData) . "\r\n");
            #fwrite($this->conn, "Accept: */*\r\n");
            #fwrite($this->conn, 'Authorization: Basic ' . $authCredentials . "\r\n");
            fwrite($this->conn, $oauthHeader."\r\n");
            #fwrite($this->conn, 'User-Agent: ' . self::USER_AGENT . "\r\n");
            fwrite($this->conn, "\r\n");
            fwrite($this->conn, $postData . "\r\n");
            fwrite($this->conn, "\r\n");

            $this->log("POST " . $urlParts['path'] . " HTTP/1.1");
            $this->log("Host: " . $urlParts['host'].':'.$port);
            #$this->log("Content-type: application/x-www-form-urlencoded");
            #$this->log("Content-length: " . strlen($postData));
            #$this->log("Accept: */*");
            #$this->log('Authorization: Basic ' . $authCredentials);
            $this->log($oauthHeader);
            #$this->log('User-Agent: ' . self::USER_AGENT);
            $this->log('');
            $this->log($postData);
            $this->log('');
             
            // First line is response
            list($httpVer, $httpCode, $httpMessage) = preg_split('/\s+/', trim(fgets($this->conn, 1024)), 3);
             
            // Response buffers
            $respHeaders = $respBody = '';

            // Consume each header response line until we get to body
            while ($hLine = trim(fgets($this->conn, 4096))) {
                $respHeaders .= $hLine;
            }
             
            // If we got a non-200 response, we need to backoff and retry
            if ($httpCode != 200) {
                $connectFailures ++;
                 
                // Twitter will disconnect on error, but we want to consume the rest of the response body (which is useful)
                while ($bLine = trim(fgets($this->conn, 4096))) {
                    $respBody .= $bLine;
                }
                 
                // Construct error
                $errStr = 'HTTP ERROR ' . $httpCode . ': ' . $httpMessage . ' (' . $respBody . ')';
                 
                // Set last error state
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $httpCode;
                 
                // Have we exceeded maximum failures?
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'Connection failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg);
                    throw new Exception($msg, $httpCode); // We eventually throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $httpRetry = ($httpRetry < self::HTTP_BACKOFF_MAX) ? $httpRetry * 2 : self::HTTP_BACKOFF_MAX;
                $this->log('HTTP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                $errStr . '. Sleeping for ' . $httpRetry . ' seconds.');
                sleep($httpRetry);
                continue;
                 
            } // End if not http 200
             
            // Loop until connected OK
        } while (!is_resource($this->conn) || $httpCode != 200);
         
        // Connected OK, reset connect failures
        $connectFailures = 0;
        $this->lastErrorMsg = NULL;
        $this->lastErrorNo = NULL;
         
        // Switch to non-blocking to consume the stream (important)
        stream_set_blocking($this->conn, 0);
         
        // Connect always causes the filterChanged status to be cleared
        $this->filterChanged = FALSE;
         
        // Flush stream buffer & (re)assign fdrPool (for reconnect)
        $this->fdrPool = array($this->conn);
        $this->buff = '';

    }

     
    protected function prepareParameters($method = null, $url = null, $params = null) {
        if(empty($method) || empty($url))
        return false;

        $oauth['oauth_consumer_key'] = TWITTER_CONSUMER_KEY;
        $oauth['oauth_token'] = $this->username;
        $oauth['oauth_nonce'] = md5(uniqid(rand(), true));
        $oauth['oauth_timestamp'] = time();
        $oauth['oauth_signature_method'] = 'HMAC-SHA1';
        if(isset($params['oauth_verifier']))
        {
            $oauth['oauth_verifier'] = $params['oauth_verifier'];
            unset($params['oauth_verifier']);
        }
        $oauth['oauth_version'] = '1.0';
        // encode all oauth values
        foreach($oauth as $k => $v)
        $oauth[$k] = $this->encode_rfc3986($v);
         
        // encode all non '@' params
        // keep sigParams for signature generation (exclude '@' params)
        // rename '@key' to 'key'
        $sigParams = array();
        $hasFile = false;
        if(is_array($params))
        {
            foreach($params as $k => $v)
            {
                if(strncmp('@',$k,1) !== 0)
                {
                    $sigParams[$k] = $this->encode_rfc3986($v);
                    $params[$k] = $this->encode_rfc3986($v);
                }
                else
                {
                    $params[substr($k, 1)] = $v;
                    unset($params[$k]);
                    $hasFile = true;
                }
            }
             
            if($hasFile === true)
            $sigParams = array();
        }

        $sigParams = array_merge($oauth, (array)$sigParams);

        // sorting
        ksort($sigParams);
         
        print_r($sigParams);

        // signing
        $oauth['oauth_signature'] = $this->encode_rfc3986($this->generateSignature($method, $url, $sigParams));
        return array('request' => $params, 'oauth' => $oauth);
    }


    protected function encode_rfc3986($string) {
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode(($string))));
    }


    protected function generateSignature($method = null, $url = null, $params = null) {
        if(empty($method) || empty($url))
        return false;

        // concatenating and encode
        $concat = '';
        foreach((array)$params as $key => $value)
        $concat .= "{$key}={$value}&";
        $concat = substr($concat, 0, -1);
        $concatenatedParams = $this->encode_rfc3986($concat);

        // normalize url
        $urlParts = parse_url($url);
        $scheme = strtolower($urlParts['scheme']);
        $host   = strtolower($urlParts['host']);
        $port = isset($urlParts['port']) ? intval($urlParts['port']) : 0;
        $retval = strtolower($scheme) . '://' . strtolower($host);
        if(!empty($port) && (($scheme === 'http' && $port != 80) || ($scheme === 'https' && $port != 443)))
        $retval .= ":{$port}";

        $retval .= $urlParts['path'];
        if(!empty($urlParts['query']))
        $retval .= "?{$urlParts['query']}";
         
        $normalizedUrl = $this->encode_rfc3986($retval);
        $method = $this->encode_rfc3986($method); // don't need this but why not?

        $signatureBaseString = "{$method}&{$normalizedUrl}&{$concatenatedParams}";
        var_dump($signatureBaseString);
         
        # sign the signature string
        $key = $this->encode_rfc3986(TWITTER_CONSUMER_SECRET) . '&' . $this->encode_rfc3986($this->password);
        return base64_encode(hash_hmac('sha1', $signatureBaseString, $key, true));
    }


    protected function getOAuthHeader($method, $url) {
        $params = $this->prepareParameters($method, $url);
        $oauthHeaders = $params['oauth'];
        $urlParts = parse_url($url);
        $oauth = 'Authorization: OAuth realm="' . $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'] . '", ';
        foreach($oauthHeaders as $name => $value)
        {
            $oauth .= "{$name}=\"{$value}\", ";
        }
        $oauth = substr($oauth, 0, -2);
        return $oauth;
    }


}