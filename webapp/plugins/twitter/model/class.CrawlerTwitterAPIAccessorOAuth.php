<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani
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

    /*
     * @var array List of crawl limits, and calls remaining based on function caller names
     */
    var $function_api_call_limits = false;

    /**
     * A list of Twitter API error codes and their explanations.
     * @var array
     */
    private $error_codes = array(
         '304' => 'There was no new data to return.',
         '400' => 'The request was invalid.',
         '401' => 'Authentication credentials were missing or incorrect.',
         '403' => 'The request is understood, but it has been refused.',
         '404' => 'The URI requested is invalid or the resource requested, such as a user, does not exists.',
         '406' => 'Invalid format specified in the request.',
         '420' => 'You are being rate limited.',
         '500' => 'Something is broken on Twitter\'s end.',
         '502' => 'Twitter is down or being upgraded.',
         '503' => 'The Twitter servers are up, but overloaded with requests. Try again later.'
         );

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

                 if (isset($status['remaining-hits']) && isset($status['hourly-limit']) &&
                 isset($status['reset-time'])){
                     $this->available_api_calls_for_twitter = $status['remaining-hits'];//get this from API
                     $this->api_hourly_limit = $status['hourly-limit'];//get this from API
                     $this->next_api_reset = $status['reset-time'];//get this from API
                 } else {
                     throw new Exception('Unable to obtain account status. Twitter API returned: "'.
                     Utils::varDumpToString($status).'"');
                 }
                 //Figure out how many minutes are left in the hour, then multiply that x 1 for api calls
                 //to leave unmade
                 $next_reset_in_minutes = (int) date('i', (int) $this->next_api_reset);
                 $current_time_in_minutes = (int) date("i", time());
                 $minutes_left_in_hour = 60;
                 if ($next_reset_in_minutes > $current_time_in_minutes) {
                     $minutes_left_in_hour = $next_reset_in_minutes - $current_time_in_minutes;
                 } elseif ($next_reset_in_minutes < $current_time_in_minutes) {
                     $minutes_left_in_hour = 60 - ($current_time_in_minutes - $next_reset_in_minutes);
                 }

                 $this->api_calls_to_leave_unmade =
                 $minutes_left_in_hour * $this->api_calls_to_leave_unmade_per_minute;
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
          * @param bool $auth Defaults to true, does it require authorization via OAuth
          * @param bool $suppress_404_error Defaults to false, don't log 404 errors from deleted tweets
          * @return array (cURL status, cURL content returned)
          */
         public function apiRequest($url, $args = array(), $auth = true, $suppress_404_error = false) {
             $logger = Logger::getInstance();
             $attempts = 0;
             $continue = true;

             // check for api function caller limits
             $caller_data = debug_backtrace();
             $calling_function = $caller_data[1]['function'];
             $calling_line = $caller_data[1]['line'];

             if ($this->function_api_call_limits && isset($this->function_api_call_limits[$calling_function])) {
                 if ($this->function_api_call_limits[$calling_function]['remaining'] == 0) {
                     $message = 'Exceeded the API call limit ' .
                     $this->function_api_call_limits[$calling_function]['count'] .
                     " for the function call $calling_function on line $calling_line";
                     $logger->logInfo($message,__METHOD__.','.__LINE__);
                     throw new APICallLimitExceededException($message);
                 }
             }

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
                                 $this->available = false;
                             } else {
                                 $this->total_errors_so_far = $this->total_errors_so_far + 1;
                                 $logger->logUserInfo('Total API errors so far: ' . $this->total_errors_so_far .
                            ' | Total errors to tolerate '. $this->total_errors_to_tolerate, __METHOD__.','.__LINE__);
                             }
                         } else {
                             $continue = false;
                             if ($this->function_api_call_limits
                             && isset($this->function_api_call_limits[$calling_function])) {
                                 $this->function_api_call_limits[$calling_function]['remaining']--;
                             }
                         }
                     } else {
                         $continue = false;
                         $url = Utils::getURLWithParams($url, $args);
                         $status_message = "API request: ".$url;
                         $logger->logInfo($status_message, __METHOD__.','.__LINE__);
                         if ($this->function_api_call_limits
                         && isset($this->function_api_call_limits[$calling_function])) {
                             $this->function_api_call_limits[$calling_function]['remaining']--;
                         }
                     }

                     if ($url != "https://api.twitter.com/1/account/rate_limit_status.xml") {
                         $logger->logInfo($this->getStatus($calling_function), __METHOD__.','.__LINE__);
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
          * @param str $calling_function Calling function name
          * @return str
          */
         public function getStatus($calling_function = null) {
             $status = $this->available_api_calls_for_twitter." of ".$this->api_hourly_limit." Twitter API calls ".
            "left this hour; ". round($this->available_api_calls_for_crawler)." budgeted for ThinkUp until ".
             date('H:i', (int) $this->next_api_reset).".";
             if (isset($calling_function) && isset($this->function_api_call_limits[$calling_function]['remaining'])) {
                 $status .= " ".$this->function_api_call_limits[$calling_function]['remaining'] . " left for ".
                 $calling_function.".";
             }
             return $status;
         }

         /**
          * Translates a Twitter API code to its corresponding explanation, as described in this link:
          * http://dev.twitter.com/pages/responses_errors
          *
          * @param <type> $error_code The error code.
          * @param <type> $include_code Whether or not to include the code in the output.
          * @return string Translated error code.
          */
         public function translateErrorCode($error_code, $include_code = true) {
             $translation = '';
             $error_code = strval($error_code);
             if (array_key_exists($error_code, $this->error_codes)) {
                 $translation = $this->error_codes[$error_code];
             }
             // if the $include_code flag is set, append the error code to the explanation
             if ($include_code) {
                 $translation = $error_code . ' ' . $translation;
             }
             return $translation;
         }

         /**
          * Returns an associative array of error_code => explanation pairs.
          *
          * @return array key => pairs of error codes that can be returned from the Twitter API.
          */
         public function getTwitterErrorCodes() {
             return $this->error_codes;
         }

         /**
          * Sets function caller limits
          */
         public function setCallerLimits($limits) {
             $this->function_api_call_limits = $limits;
         }

         /**
          * gets function caller limits
          */
         public function getCallerLimit($function) {
             $limit = false;
             if ($this->function_api_call_limits && isset($this->function_api_call_limits[$function])) {
                 $limit = $this->function_api_call_limits[$function];
             }
             return $limit;
         }
}
