<?php
/**
 *
 * ThinkUp/webapp/_lib/class.AppUpgraderClient.php
 *
 * Copyright (c) 2012-2013 Mark Wilkie
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class AppUpgraderClient {
    /**
     * @var str URL to retrieve web-based update information
     */
    static $UPDATE_URL = 'http://ginatrapani.github.com/ThinkUp/version.json';
    /**
     * @var str URL to retrieve update info for beta channel
     */
    static $UPDATE_BETA_URL = 'http://ginatrapani.github.com/ThinkUp/version_beta.json';
    /**
     * Constructor
     * @return AppUpgraderClient
     */
    public function __construct() {
        $config = Config::getInstance();
        $is_in_beta = $config->getValue('is_subscribed_to_beta');
        $is_in_beta = isset($is_in_beta)?$is_in_beta:false;
        if ($is_in_beta) {
            self::$UPDATE_URL = self::$UPDATE_BETA_URL;
        }
    }
    /**
     * Get update file information.
     * @throws Exception
     * @return arr Latest version and zip file information
     */
    public function getLatestVersionInfo() {
        $json_string = $this->fetchUrlData(self::$UPDATE_URL);
        if (!$json_string) {
            throw new Exception("Unable to load latest version information from " . self::$UPDATE_URL);
        } else {
            $update_info_array = json_decode($json_string, true);
            if (!is_array($update_info_array) || !isset($update_info_array['version'])) {
                throw new Exception("Invalid data received from update server: " . $json_string);
            }
            $current_version = Config::getInstance()->getValue('THINKUP_VERSION');
            if (version_compare($current_version, $update_info_array['version'], '==')) {
                throw new Exception("You are running the latest version of ThinkUp.");
            }
            return $update_info_array;
        }
    }
    /**
     * Get zip file of ThinkUp's latest version.
     * @throws Exception
     * @param str $url
     * @return Zip file contents
     */
    public function getLatestVersionZip($url) {
        $update_zip = $this->fetchUrlData($url);
        if ($update_zip == false) {
            throw new Exception("Unable to download latest update file " . $url);
        } else {
            return $update_zip;
        }
    }
    /**
     * Abstraction for pulling data from a file or url
     * @throws exception
     * @param str $url
     * @return request response data
     */
    private function fetchURLData($url) {
        if (strpos($url, "/") == 0 || strpos($url, ".") == 0) { // we are a file path, so use file_get_contents
            $contents = file_get_contents($url);
        } else { // else we are a url, so use our Util::getURLContents
            $contents = Utils::getURLContents(URLProcessor::getFinalURL($url));
        }
        if (is_null($contents)) {
            $contents = false;
        }
        return $contents;
    }
}