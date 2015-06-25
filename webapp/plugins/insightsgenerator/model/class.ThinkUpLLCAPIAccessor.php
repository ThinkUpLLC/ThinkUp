<?php
/**
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.ThinkUpLLCAPIAccessor.php
 *
 * Copyright (c) 2015 Gina Trapani
 *
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
 *
 *
 * ThinkUpLLC API Accessor
 *
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 */
class ThinkUpLLCAPIAccessor {
    /**
     * Get the subscription status for a ThinkUp.com member via an API call.
     * @param  str $email
     * @return Object
     */
    public function getSubscriptionStatus($email) {
        $cfg = Config::getInstance();
        $api_url = $cfg->getValue('thinkupllc_api_endpoint')."member/status.php";
        $api_username = $cfg->getValue('thinkupllc_api_endpoint_username');
        $api_password = $cfg->getValue('thinkupllc_api_endpoint_password');
        if (!isset($api_url) || !isset($api_username) || !isset($api_password)) {
            return null;
        } else {
            $params = array('email'=>$email);
            $query = http_build_query($params);
            $api_call = $api_url.'?'.$query;
            //echo $api_call;
            $result = self::getURLContents($api_call, $api_username, $api_password);
            //print_r($result);
            $result_decoded = JSONDecoder::decode($result);
            //print_r($result_decoded);
            return $result_decoded;
        }
    }
    /**
     * Check to see if two avatars cached on ThinkUp.com are different.
     * Sometimes, Twitter stores the same avatar at different URLs, and the files have different checksums, but
     * the images look very similar. This is an extra check for image differences for hosted ThinkUp.com users.
     * @param  str $image1
     * @param  str $image2
     * @return bool
     */
    public function didAvatarsChange($image1, $image2) {
        $config = Config::getInstance();
        if ($config->getValue('image_proxy_enabled') == true) {
            $image_proxy_sig = $config->getValue('image_proxy_sig');
            $api_url = "https://images.thinkup.com/";
            $params = array('image1'=>$image1, 'image2'=>$image2, 's'=>$image_proxy_sig);
            $query = http_build_query($params);
            $api_call = $api_url.'?'.$query;
            //echo $api_call;
            $result = self::getURLContents($api_call);
            //print_r($result);
            try {
                $result_decoded = JSONDecoder::decode($result);
            } catch (JSONDecoderException $e) {
                //Punt - something went very wrong. Err on the side of nothing changed.
                return false;
            }
            //print_r($result_decoded);
            return $result_decoded->show_diff;
        }
        return true;
    }
    /**
     * Get the contents of a URL given an http auth username and password.
     * @param  str $url
     * @param  str $username
     * @param  str $password
     * @return str
     */
    private static function getURLContents($url, $username=null, $password=null) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        if (isset($username) && isset($password)) {
            curl_setopt($c, CURLOPT_USERPWD, $username . ":" . $password);
        }
        $contents = curl_exec($c);
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);

        if (isset($contents)) {
            return $contents;
        } else {
            return null;
        }
    }
}