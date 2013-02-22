<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/model/class.FlickrAPIAccessor.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
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
        if ($this->api_key != '') {
            $this->logger->logInfo("Flickr API key set", __METHOD__.','.__LINE__);
            $photo_short_id = substr($u, strlen('http://flic.kr/p/'));
            $photo_id = $this->base_decode($photo_short_id);
            $params = array('method'=>$this->method, 'photo_id'=>$photo_id, 'api_key'=>$this->api_key,
            'format'=>$this->format, );

            $encoded_params = array();

            foreach ($params as $k=>$v) {
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }

            $api_call = $this->api_url.implode('&', $encoded_params);

            $this->logger->logInfo("Flickr API call: $api_call", __METHOD__.','.__LINE__);

            $resp = Utils::getURLContents($api_call);
            if ($resp != false) {
                $fphoto = unserialize($resp);

                if ($fphoto['stat'] == 'ok') {
                    $src = '';
                    foreach ($fphoto['sizes']['size'] as $s) {
                        if ($s['label'] == 'Small') {
                            $src = $s['source'];
                        }
                    }
                    return array("image_src"=>$src, "error"=>'');
                } else {
                    $this->logger->logInfo("ERROR: '".$fphoto['message']."'", __METHOD__.','.__LINE__);
                    return array("image_src"=>'', "error"=>$fphoto['message']);
                }

            } else {
                $this->logger->logInfo("ERROR: No response from Flickr API", __METHOD__.','.__LINE__);
                return array("image_src"=>'', "error"=>'No response from Flickr API');
            }
        } else {
            $this->logger->logInfo("ERROR: Flickr API key is not set", __METHOD__.','.__LINE__);
            return array("image_src"=>'', "error"=>'');
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