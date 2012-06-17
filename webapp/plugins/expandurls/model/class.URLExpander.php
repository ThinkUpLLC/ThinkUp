<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/model/class.URLExpander.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 *
 * URL Expander
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class URLExpander {
    /**
     * Return the expanded version of a given short URL or save an error for the $original_link in links table and
     * return an empty string.
     *
     * @param str $tinyurl Shortened URL
     * @param str $original_link
     * @param int $current_number Current link number
     * @param int $total_number Total links in group
     * @return str Expanded URL
     */
    public static function expandURL($tinyurl, $original_link, $current_number, $total_number, $link_dao, $logger) {
        if (getenv("MODE")=="TESTS") { //for testing without actually making requests
            return $original_link.'/expandedversion';
        }
        $error_log_prefix = $current_number." of ".$total_number." links: ";
        $url = parse_url($tinyurl);
        if (isset($url['host'])) {
            $host = $url['host'];
        } else {
            $error_msg = $tinyurl.": No host found.";
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            return '';
        }
        $port = isset($url['port']) ? ':'.$url['port'] : '';
        $query = isset($url['query']) ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        if (empty($url['path'])) {
            $path = '';
        } else {
            $path = $url['path'];
        }
        $scheme = isset($url['scheme'])?$url['scheme']:'http';

        $reconstructed_url = $scheme."://$host$port".$path.$query.$fragment;
        $logger->logInfo("Making cURL request for ".$reconstructed_url, __METHOD__.','.__LINE__);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $reconstructed_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        $response = curl_exec($ch);
        if ($response === false) {
            $error_msg = $reconstructed_url." cURL error: ".curl_error($ch);
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            $tinyurl = '';
        }
        curl_close($ch);

        $lines = explode("\r\n", $response);
        foreach ($lines as $line) {
            if (stripos($line, 'Location:') === 0) {
                list(, $location) = explode(':', $line, 2);
                $result = ltrim($location);
                //If this is a relative redirect, add the host
                $dest_url = parse_url($result);
                if (!isset($dest_url['host'])) {
                    $result = $scheme."://$host$port".$result;
                }
                return $result;
            }
        }

        if (strpos($response, 'HTTP/1.1 404 Not Found') === 0) {
            $error_msg = $reconstructed_url." returned '404 Not Found'";
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            return '';
        }
        return $tinyurl;
    }
}