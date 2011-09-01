<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/model/class.BitlyAPIAccessor.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2011 Gina Trapani
 */
class BitlyAPIAccessor {
    var $api_url = "http://api.bitly.com/v3/";
    var $format = "json";
    var $api_key;
    var $login;
    var $logger;
    var $bitly_limit;

    public function BitlyAPIAccessor($bitly_api_key, $bitly_login, $bitly_limit) {
        $this->api_key = $bitly_api_key;
        $this->login = $bitly_login;
        $this->logger = Logger::getInstance();
        $this->bitly_limit = $bitly_limit;
    }

    public function getBitlyLinkData($u) {
        if ($this->api_key != '') {
            $this->logger->logInfo("Bitly API key set", __METHOD__.','.__LINE__);
            $params = array('shortUrl'=>$u, 'login'=>$this->login, 'apiKey'=>$this->api_key,
            'format'=>$this->format);

            $encoded_params = array();

            foreach ($params as $k=>$v) {
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }
            
            $bit_link = $this->bitlyAPISwitcher('info?', $encoded_params);
                if ($bit_link['status_code'] == '200') {
                    if (isset($bit_link['data']['info'][0]['title'])) {
                        $title = $bit_link['data']['info'][0]['title'];
                    } else {
                        $title = '';
                    }
                } else {
                    $this->logger->logInfo("ERROR: '".$bit_link->status_txt."'", __METHOD__.','.__LINE__);
                    return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>$bit_link->status_txt);
                }
                
            $bit_link = $this->bitlyAPISwitcher('expand?', $encoded_params);
                if ($bit_link['status_code'] == '200') {
                    if (isset($bit_link['data']['expand'][0]['long_url'])) {
                        $expanded_url = $bit_link['data']['expand'][0]['long_url'];
                    } else {
                        $expanded_url = '';
                    }
                } else {
                    $this->logger->logInfo("ERROR: '".$bit_link['status_txt']."'", __METHOD__.','.__LINE__);
                    return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>$bit_link['status_txt']);
                }
                
            $bit_link = $this->bitlyAPISwitcher('clicks?', $encoded_params);
                if ($bit_link['status_code'] == '200') {
                    if (isset($bit_link['data']['clicks'][0]['global_clicks'])) {
                        $clicks = $bit_link['data']['clicks'][0]['global_clicks'];
                    } else {
                        $clicks = 0;
                    }
                } else {
                    $this->logger->logInfo("ERROR: '".$bit_link->status_txt."'", __METHOD__.','.__LINE__);
                    return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>$bit_link->status_txt);
                }

        } else {
            $this->logger->logInfo("ERROR: Bit.ly API key is not set", __METHOD__.','.__LINE__);
            return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>'');
        }
        return array("expanded_url"=>$expanded_url, "title"=>$title, "clicks"=>$clicks, "error"=>'');
    }
    
    public function bitlyAPISwitcher($method, $encoded_params) {
        $api_call = $this->api_url.$method.implode('&', $encoded_params);
            $this->logger->logInfo("Bit.ly API call: $api_call", __METHOD__.','.__LINE__);

            $resp = Utils::getURLContents($api_call);
            if ($resp != false) {
                $bit_link = json_decode($resp, true);
                return $bit_link;
            } else {
                $this->logger->logInfo("ERROR: No response from Bit.ly API", __METHOD__.','.__LINE__);
                return array("expanded_url"=>'', "title"=>'', "clicks"=>'', "error"=>'No response from Bit.ly API');
            }

    }
}
