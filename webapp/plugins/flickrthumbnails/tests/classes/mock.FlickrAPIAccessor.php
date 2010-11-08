<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/tests/classes/mock.FlickrAPIAccessor.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
class FlickrAPIAccessor {
    var $api_url = "http://api.flickr.com/services/rest/?";
    var $format = "php_serial";
    var $method = "flickr.photos.getSizes";
    var $api_key;
    var $logger;

    public function FlickrAPIAccessor($flickr_api_key) {
        $this->api_key = $flickr_api_key;
        $this->logger = Logger::getInstance();
    }

    public function getFlickrPhotoSource($u) {
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . 'webapp/plugins/flickrthumbnails/tests/testdata/';

        if ($this->api_key != '') {
            $this->logger->logStatus("Flickr API key set", get_class($this));
            $photo_short_id = substr($u, strlen('http://flic.kr/p/'));
            $photo_id = $this->base_decode($photo_short_id);
            $params = array('method'=>$this->method, 'photo_id'=>$photo_id, 'api_key'=>$this->api_key,
            'format'=>$this->format, );

            $encoded_params = array();

            foreach ($params as $k=>$v) {
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }

            $api_call = $this->api_url.implode('&', $encoded_params);

            $this->logger->logStatus("Flickr API call: $api_call", get_class($this));

            //$resp = Utils::getURLContents($api_call);

            $api_call = str_replace('http://', '', $api_call);
            $api_call = str_replace('/', '_', $api_call);
            $api_call = str_replace('?', '-', $api_call);
            $api_call = str_replace('&', '-', $api_call);
            //echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$api_call;
            $resp = file_get_contents($FAUX_DATA_PATH.$api_call);

            if ($resp === "NONRESPONSE") {
                $resp = false;
            }

            if ($resp != false) {
                $fphoto = unserialize($resp);

                if ($fphoto['stat'] == 'ok') {
                    foreach ($fphoto['sizes']['size'] as $s) {
                        if ($s['label'] == 'Small')
                        $src = $s['source'];
                    }
                    return array("expanded_url"=>$src, "error"=>'');
                } else {
                    $this->logger->logStatus("ERROR: '".$fphoto['message']."'", get_class($this));
                    return array("expanded_url"=>'', "error"=>$fphoto['message']);
                }

            } else {
                $this->logger->logStatus("ERROR: No response from Flickr API", get_class($this));
                return array("expanded_url"=>'', "error"=>'No response from Flickr API');
            }
        } else {
            $this->logger->logStatus("ERROR: Flickr API key is not set", get_class($this));
            return array("expanded_url"=>'', "error"=>'');
        }
    }


    public function base_decode($num, $alphabet = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ") {
        $decoded = 0;
        $multi = 1;
        while (strlen($num) > 0) {
            $digit = $num[strlen($num) - 1];
            $decoded += $multi * strpos($alphabet, $digit);
            $multi = $multi * strlen($alphabet);
            $num = substr($num, 0, -1);
        }

        return $decoded;
    }
}