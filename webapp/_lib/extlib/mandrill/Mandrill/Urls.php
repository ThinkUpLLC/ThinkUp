<?php

class Mandrill_Urls {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Get the 100 most clicked URLs
     * @return array the 100 most clicked URLs and their stats
     *     - return[] struct the individual URL stats
     *         - url string the URL to be tracked
     *         - sent integer the number of emails that contained the URL
     *         - clicks integer the number of times the URL has been clicked from a tracked email
     *         - unique_clicks integer the number of unique emails that have generated clicks for this URL
     */
    public function getList() {
        $_params = array();
        return $this->master->call('urls/list', $_params);
    }

    /**
     * Return the 100 most clicked URLs that match the search query given
     * @param string $q a search query
     * @return array the 100 most clicked URLs matching the search query
     *     - return[] struct the URL matching the query
     *         - url string the URL to be tracked
     *         - sent integer the number of emails that contained the URL
     *         - clicks integer the number of times the URL has been clicked from a tracked email
     *         - unique_clicks integer the number of unique emails that have generated clicks for this URL
     */
    public function search($q) {
        $_params = array("q" => $q);
        return $this->master->call('urls/search', $_params);
    }

    /**
     * Return the recent history (hourly stats for the last 30 days) for a url
     * @param string $url an existing URL
     * @return array the array of history information
     *     - return[] struct the information for a single hour
     *         - time string the hour as a UTC date string in YYYY-MM-DD HH:MM:SS format
     *         - sent integer the number of emails that were sent with the URL during the hour
     *         - clicks integer the number of times the URL was clicked during the hour
     *         - unique_clicks integer the number of unique clicks generated for emails sent with this URL during the hour
     */
    public function timeSeries($url) {
        $_params = array("url" => $url);
        return $this->master->call('urls/time-series', $_params);
    }

}


