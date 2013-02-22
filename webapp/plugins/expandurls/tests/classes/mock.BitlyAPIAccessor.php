<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/classes/mock.BitlyAPIAccessor.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 */
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Randi Miller <techrandy[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class BitlyAPIAccessor {
    /**
     * @var str
     */
    var $api_url = "http://api.bitly.com/v3/";
    /**
     * @var str
     */
    var $format = "json";
    /**
     * @var str
     */
    var $bitly_api_key;
    /**
     * @var str
     */
    var $bitly_username;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var bool
     */
    var $debug;
    /**
     * @var str
     */
    var $test_data_path;

    public function __construct($bitly_api_key, $bitly_login) {
        $this->bitly_api_key = $bitly_api_key;
        $this->bitly_username = $bitly_login;
        $this->logger = Logger::getInstance();
        $this->debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        $this->test_data_path = THINKUP_WEBAPP_PATH.'plugins/expandurls/tests/testdata/';
    }

    public function getBitlyLinkData($u) {
        $expanded_url = '';
        $clicks = 0;
        $title = '';
        $error = '';
        if ($this->bitly_api_key != '') {
            $this->logger->logInfo("Bitly API key set", __METHOD__.','.__LINE__);
            $params = array('shortUrl'=>$u, 'login'=>$this->bitly_username, 'apiKey'=>$this->bitly_api_key,
            'format'=>$this->format);

            $encoded_params = array();

            foreach ($params as $k=>$v) {
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }

            //Get link info
            $bit_link = $this->apiRequest('info?', $encoded_params);
            if (isset($bit_link['status_code']) && $bit_link['status_code'] == '200'){
                if (isset($bit_link['data']['info'][0]['title'])) {
                    $title = $bit_link['data']['info'][0]['title'];
                }
            } else {
                $error = (isset($bit_link->status_txt))?$bit_link->status_txt:'No response from http://bit.ly API';
            }
            //Get expanded link
            $bit_link = $this->apiRequest('expand?', $encoded_params);
            if (isset($bit_link['status_code']) && $bit_link['status_code'] == '200'){
                if (isset($bit_link['data']['expand'][0]['long_url'])) {
                    $expanded_url = $bit_link['data']['expand'][0]['long_url'];
                } else {
                    $error = (isset($bit_link->status_txt))?$bit_link->status_txt:'No response from http://bit.ly API';
                }
            }
            //Get link clicks
            $bit_link = $this->apiRequest('clicks?', $encoded_params);
            if (isset($bit_link['status_code']) && $bit_link['status_code'] == '200'){
                if (isset($bit_link['data']['clicks'][0]['user_clicks'])) {
                    $clicks = $bit_link['data']['clicks'][0]['user_clicks'];
                } else {
                    $error = (isset($bit_link->status_txt))?$bit_link->status_txt:'No response from http://bit.ly API';
                }
            }
        } else {
            $this->logger->logInfo("ERROR: Bit.ly API key is not set", __METHOD__.','.__LINE__);
        }
        return array("expanded_url"=>$expanded_url, "title"=>$title, "clicks"=>$clicks, "error"=>$error);
    }

    private function apiRequest($method, $encoded_params) {
        $api_call = $this->api_url.$method.implode('&', $encoded_params);
        $this->logger->logInfo("Bit.ly API call: $api_call", __METHOD__.','.__LINE__);

        //$resp = Utils::getURLContents($api_call);
        $api_call = str_replace('http://', '', $api_call);
        $api_call = str_replace('/', '_', $api_call);
        $api_call = str_replace('?', '-', $api_call);
        $api_call = str_replace('&', '-', $api_call);

        if ($this->debug) {
            echo "READING LOCAL DATA FILE: ".$this->test_data_path.$api_call . "\n";
        }

        if (file_exists($this->test_data_path.$api_call)) {
            $resp = file_get_contents($this->test_data_path.$api_call);
            $bit_link = json_decode($resp, true);
            $this->logger->logInfo("API RESPONSE DECODED: ".Utils::varDumpToString($bit_link), __METHOD__.','.__LINE__);
            return $bit_link;
        } else {
            $this->logger->logInfo("ERROR: No response from Bit.ly API", __METHOD__.','.__LINE__);
            return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>'No response from Bit.ly API');
        }
    }
}
