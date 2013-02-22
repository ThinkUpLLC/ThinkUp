<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Reporter.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 */
class Reporter {
    /**
     * Report installation version back to thinkup.com. If usage reporting is enabled, include instance username
     * and network.
     * @param Instance $instance
     * @return array ($report_back_url, $referer_url, $status, $contents)
     */
    public static function reportVersion(Instance $instance) {
        //Build URLs with appropriate parameters
        $config = Config::getInstance();
        $report_back_url = 'http://thinkup.com/version.php?v='.$config->getValue('THINKUP_VERSION');

        //Explicity set referer for when this is called by a command line script
        $referer_url = Utils::getApplicationURL();

        //If user hasn't opted out, report back username and network
        if ( $config->getValue('is_opted_out_usage_stats') === true) {
            $report_back_url .= '&usage=n';
        } else {
            $referer_url .= "?u=".urlencode($instance->network_username)."&n=". urlencode($instance->network);
        }

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        if (!$in_test_mode) { //only make live request if we're not running the test suite
            //Make the cURL request
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $report_back_url);
            curl_setopt($c, CURLOPT_REFERER, $referer_url);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            $contents = curl_exec($c);
            $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
            curl_close($c);
        } else {
            $contents = '';
            $status = 200;
        }
        return array($report_back_url, $referer_url, $status, $contents);
    }
}