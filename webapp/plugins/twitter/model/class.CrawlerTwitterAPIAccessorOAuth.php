<?php
/**
 * Crawler TwitterAPI Accessor, via OAuth
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CrawlerTwitterAPIAccessorOAuth extends TwitterAPIAccessorOAuth {
    var $api_calls_to_leave_unmade;
    var $api_calls_to_leave_unmade_per_minute;
    var $available_api_calls_for_crawler = null;
    var $available_api_calls_for_twitter = null;
    var $api_hourly_limit = null;
    var $archive_limit;

    public function __construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret,
    $instance, $archive_limit) {
        parent::__construct($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret);
        $this->api_calls_to_leave_unmade_per_minute = $instance->api_calls_to_leave_unmade_per_minute;
        $this->archive_limit = $archive_limit;
    }

    public function init() {
        $logger = Logger::getInstance();
        $status_message = "";

        $account_status = $this->cURL_source['rate_limit'];
        list($cURL_status, $twitter_data) = $this->apiRequest($account_status);
        $this->available_api_calls_for_crawler++; //status check doesnt' count against balance

        if ($cURL_status > 200) {
            $this->available = false;
        } else {
            try {
                # Parse file
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
                if ($next_reset_in_minutes > $current_time_in_minutes)
                $minutes_left_in_hour = $next_reset_in_minutes - $current_time_in_minutes;
                elseif ($next_reset_in_minutes < $current_time_in_minutes)
                $minutes_left_in_hour = 60 - ($current_time_in_minutes - $next_reset_in_minutes);

                $this->api_calls_to_leave_unmade = $minutes_left_in_hour * $this->api_calls_to_leave_unmade_per_minute;
                $this->available_api_calls_for_crawler = $this->available_api_calls_for_twitter -
                round($this->api_calls_to_leave_unmade);
            } catch(Exception $e) {
                $status_message = 'Could not parse account status: '.$e->getMessage();
            }
        }
        $logger->logStatus($status_message, get_class($this));
        $logger->logStatus($this->getStatus(), get_class($this));
    }

    public function apiRequest($url, $args = array(), $auth = true) {
        $logger = Logger::getInstance();
        if ($auth) {
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
                $status_message .= "\n\n$content\n\n";
                if ($status != 404 && $status != 403) {
                    //$this->available = false;
                    if ($this->total_errors_so_far >= $this->total_errors_to_tolerate) {
                        $this->available = false;
                    } else {
                        $this->total_errors_so_far = $this->total_errors_so_far + 1;
                        $logger->logStatus('Total API errors so far: ' . $this->total_errors_so_far .
                ' | Total errors to tolerate '. $this->total_errors_to_tolerate, get_class($this));
                    }

                }
                $logger->logStatus($status_message, get_class($this));
                $status_message = "";
            } else {
                $url = Utils::getURLWithParams($url, $args);
                $status_message = "API request: ".$url;
            }

            $logger->logStatus($status_message, get_class($this));
            $status_message = "";

            if ($url != "https://api.twitter.com/1/account/rate_limit_status.xml") {
                $status_message = $this->getStatus();
                $logger->logStatus($status_message, get_class($this));
                $status_message = "";
            }
        } else {
            $logger->logStatus("OAuth-free request: $url", get_class($this));
            $content = $this->to->noAuthRequest($url);
            $status = $this->to->lastStatusCode();
            //$logger->logStatus("no OAuth content returned: $content", get_class($this));
        }
        return array($status, $content);
    }

    public function getStatus() {
        return $this->available_api_calls_for_twitter." of ".$this->api_hourly_limit." API calls left this hour; ".
        round($this->available_api_calls_for_crawler)." for crawler until ".
        date('H:i:s', (int) $this->next_api_reset);
    }
}
