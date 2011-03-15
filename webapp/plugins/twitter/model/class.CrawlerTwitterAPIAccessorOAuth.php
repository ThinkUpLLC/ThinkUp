<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php
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
 *
 *
 * Crawler TwitterAPI Accessor, via OAuth
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CrawlerTwitterAPIAccessorOAuth extends TwitterAPIAccessorOAuth {
    /**
     *
     * @var int
     */
    var $api_calls_to_leave_unmade;
    /**
     *
     * @var int
     */
    var $api_calls_to_leave_unmade_per_minute;
    /**
     *
     * @var int
     */
    var $available_api_calls_for_crawler = null;
    /**
     *
     * @var int
     */
    var $available_api_calls_for_twitter = null;
    /**
     *
     * @var int
     */
    var $api_hourly_limit = null;
    /**
     *
     * @var int
     */
    var $archive_limit;
    /**
     *
     * @var int
     */
    var $num_retries = 2;
    /**
     * Constructor
     * @param str $oauth_token
     * @param str $oauth_token_secret
     * @param str $oauth_consumer_key
     * @param str $oauth_consumer_secret
     * @param Instance $instance
     * @param int $archive_limit
     * @param int $num_twitter_errors
     * @param int $max_api_calls_per_crawl
     * @return CrawlerTwitterAPIAccessorOAuth
     */
    public function __construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret,
    $api_calls_to_leave_unmade_per_minute, $archive_limit, $num_twitter_errors, $max_api_calls_per_crawl) {
        parent::__construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret,
        $num_twitter_errors, $max_api_calls_per_crawl);
        $this->api_calls_to_leave_unmade_per_minute = $api_calls_to_leave_unmade_per_minute;
        $this->archive_limit = $archive_limit;
    }

    /**
     * Initalize the API accessor.
     */
    public function init() {
        $logger = Logger::getInstance();
        $status_message = "";

        $account_status = $this->cURL_source['rate_limit'];
        list($cURL_status, $twitter_data) = $this->apiRequest($account_status);
        $this->available_api_calls_for_crawler++; //status check doesnt' count against balance

        if ($cURL_status > 200) {
            $this->available = false;
        } else {
            $status_message = "Parsing XML data from $account_status ";
            $status = $this->parseXML($twitter_data);

            if (isset($status['remaining-hits']) && isset($status['hourly-limit']) && isset($status['reset-time'])){
                $this->available_api_calls_for_twitter = $status['remaining-hits'];//get this from API
                $this->api_hourly_limit = $status['hourly-limit'];//get this from API
                $this->next_api_reset = $status['reset-time'];//get this from API
            } else {
                throw new Exception('API status came back malformed');
            }
            //Figure out how many minutes are left in the hour, then multiply that x 1 for api calls to leave unmade
            $next_reset_in_minutes = (int) date('i', (int) $this->next_api_reset);
            $current_time_in_minutes = (int) date("i", time());
            $minutes_left_in_hour = 60;
            if ($next_reset_in_minutes > $current_time_in_minutes) {
                $minutes_left_in_hour = $next_reset_in_minutes - $current_time_in_minutes;
            } elseif ($next_reset_in_minutes < $current_time_in_minutes) {
                $minutes_left_in_hour = 60 - ($current_time_in_minutes - $next_reset_in_minutes);
            }

            $this->api_calls_to_leave_unmade = $minutes_left_in_hour * $this->api_calls_to_leave_unmade_per_minute;
            $this->available_api_calls_for_crawler = $this->available_api_calls_for_twitter -
            round($this->api_calls_to_leave_unmade);
            //Enforce configurable ceiling for whitelisted Twitter accounts
            if ($this->available_api_calls_for_crawler > $this->max_api_calls_per_crawl) {
                $this->available_api_calls_for_crawler = $this->max_api_calls_per_crawl;
            }
        }
        $logger->logUserInfo($this->getStatus(), __METHOD__.','.__LINE__);
    }

    /**
     * Make Twitter API request.
     * @param str $url
     * @param array $args URL query string parameters
     * @param boolean $auth Does it require authorization via OAuth
     * @return array (cURL status, cURL content returned)
     */
    public function apiRequest($url, $args = array(), $auth = true) {
        $logger = Logger::getInstance();
        $attempts = 0;
        $continue = true;

        if ($auth) {
            while ($attempts <= $this->num_retries && $continue) {
                $content = $this->to->OAuthRequest($url, 'GET', $args);
                $status = $this->to->lastStatusCode();

                $this->available_api_calls_for_twitter = $this->available_api_calls_for_twitter - 1;
                $this->available_api_calls_for_crawler = $this->available_api_calls_for_crawler - 1;
                $status_message = "";
                if ($status > 200) {
                    $status_message = "Could not retrieve $url";
                    if (sizeof($args) > 0) {
                        $status_message .= "?";
                    }
                    foreach ($args as $key=>$value) {
                        $status_message .= $key."=".$value."&";
                    }
                    $status_message .= " | API ERROR: $status";
                    //$status_message .= "\n\n$content\n\n";
                    $logger->logUserError($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";
                    if ($status != 404 && $status != 403) {
                        $attempts++;
                        if ($this->total_errors_so_far >= $this->total_errors_to_tolerate) {
                            $this->available = false;
                        } else {
                            $this->total_errors_so_far = $this->total_errors_so_far + 1;
                            $logger->logUserInfo('Total API errors so far: ' . $this->total_errors_so_far .
                            ' | Total errors to tolerate '. $this->total_errors_to_tolerate, __METHOD__.','.__LINE__);
                        }
                    } else {
                        $continue = false;
                    }
                } else {
                    $continue = false;
                    $url = Utils::getURLWithParams($url, $args);
                    $status_message = "API request: ".$url;
                    $logger->logInfo($status_message, __METHOD__.','.__LINE__);
                }

                if ($url != "https://api.twitter.com/1/account/rate_limit_status.xml") {
                    $logger->logInfo($this->getStatus(), __METHOD__.','.__LINE__);
                }
            }
        } else {
            $logger->logInfo("OAuth-free request: $url", __METHOD__.','.__LINE__);
            $content = $this->to->noAuthRequest($url);
            $status = $this->to->lastStatusCode();
            //$logger->logInfo("no OAuth content returned: $content", __METHOD__.','.__LINE__);
        }
        return array($status, $content);
    }

    /**
     * Get API call balance information formatted for logging.
     * @return str
     */
    public function getStatus() {
        return $this->available_api_calls_for_twitter." of ".$this->api_hourly_limit." Twitter API calls ".
        "left this hour; ". round($this->available_api_calls_for_crawler)." budgeted for ThinkUp until ".
        date('H:i', (int) $this->next_api_reset).".";
    }
}
