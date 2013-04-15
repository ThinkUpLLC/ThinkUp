<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php
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
 *
 *
 * Crawler TwitterAPI Accessor, via OAuth
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerTwitterAPIAccessorOAuth extends TwitterAPIAccessorOAuth {
    /**
     * @var int
     */
    var $archive_limit;
    /**
     * @var int
     */
    var $num_retries = 2;
    /**
     * Maxiumum percent of available API calls that should be used for endpoint.
     * @var int
     */
    var $percent_use_ceiling = 90;
    /**
     * Constructor
     * @param str $oauth_token
     * @param str $oauth_token_secret
     * @param str $oauth_consumer_key
     * @param str $oauth_consumer_secret
     * @param Instance $instance
     * @param int $archive_limit
     * @param int $num_twitter_errors
     * @return CrawlerTwitterAPIAccessorOAuth
     */
    public function __construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret,
    $archive_limit, $num_twitter_errors) {
        parent::__construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret,
        $num_twitter_errors);
        $this->archive_limit = $archive_limit;
        self::initializeEndpointRateLimits();
    }
    /**
     * Set per-endpoint rate limits and next reset time.
     * @return void
     */
    public function initializeEndpointRateLimits() {
        $endpoint = $this->endpoints['rate_limits'];
        $args = array();
        $args["resources"] = 'account,statuses,users,followers,lists,friends,favorites,friendships,application,search';
        list($http_status, $payload) = $this->apiRequest($endpoint, $args);
        $rate_limit_data_array = JSONDecoder::decode($payload, true);
        $rate_limit_data_array = $rate_limit_data_array["resources"];
        $limits = array();
        foreach ($rate_limit_data_array as $resource) {
            foreach ($resource as $key=>$values) {
                $limits[$key] = $values;
            }
        }
        foreach ($this->endpoints as $endpoint) {
            $endpoint->setRemaining($limits[$endpoint->getShortPath()]['remaining']);
            $endpoint->setLimit($limits[$endpoint->getShortPath()]['limit']);
            $endpoint->setReset($limits[$endpoint->getShortPath()]['reset']);
        }
    }
    /**
     * Make a Twitter API request.
     * @param TwitterAPIEndpoint $endpoint
     * @param arr $args URL query string parameters
     * @param str $id ID for use in endpoint path
     * @param bool $suppress_404_error Defaults to false, don't log 404 errors from deleted tweets
     * @return arr HTTP status code, payload
     */
    public function apiRequest(TwitterAPIEndpoint $endpoint, $args=array(), $id=null, $suppress_404_error=false) {
        $logger = Logger::getInstance();
        $attempts = 0;
        $continue = true;
        $url = $endpoint->getPathWithID($id);

        $is_rate_limit_check = ($endpoint->getShortPath() == "/application/rate_limit_status");

        if ($is_rate_limit_check || $endpoint->isAvailable($this->percent_use_ceiling)) {
            while ($attempts <= $this->num_retries && $continue) {
                $content = $this->to->OAuthRequest($url, 'GET', $args);
                $status = $this->to->lastStatusCode();

                if (!$is_rate_limit_check) {
                    $endpoint->decrementRemaining();
                    $logger->logInfo($endpoint->getStatus(), __METHOD__.','.__LINE__);
                }

                $status_message = "";
                if ($status > 200) {
                    $status_message = "Could not retrieve $url";
                    if (sizeof($args) > 0) {
                        $status_message .= "?";
                    }
                    foreach ($args as $key=>$value) {
                        $status_message .= $key."=".$value."&";
                    }
                    $translated_status_code = $this->translateErrorCode($status);
                    $status_message .= " | API ERROR: $translated_status_code";

                    //we expect a 404 when checking a tweet deletion, so suppress log line if defined
                    if ($status == 404) {
                        if ($suppress_404_error === false ) {
                            $logger->logUserError($status_message, __METHOD__.','.__LINE__);
                        }
                    } else { //do log any other kind of error
                        $logger->logUserError($status_message, __METHOD__.','.__LINE__);
                    }

                    $status_message = "";
                    if ($status != 404 && $status != 403) {
                        $attempts++;
                        if ($this->total_errors_so_far >= $this->total_errors_to_tolerate) {
                            $continue = false;
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
                }
            }
            return array($status, $content);
        } else {
            throw new APICallLimitExceededException("API call allocation limit reached. ".$endpoint->getStatus());
        }
    }
}