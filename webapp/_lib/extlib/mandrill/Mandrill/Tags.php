<?php

class Mandrill_Tags {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Return all of the user-defined tag information
     * @return array a list of user-defined tags
     *     - return[] struct a user-defined tag
     *         - tag string the actual tag as a string
     *         - reputation integer the tag's current reputation on a scale from 0 to 100.
     *         - sent integer the total number of messages sent with this tag
     *         - hard_bounces integer the total number of hard bounces by messages with this tag
     *         - soft_bounces integer the total number of soft bounces by messages with this tag
     *         - rejects integer the total number of rejected messages with this tag
     *         - complaints integer the total number of spam complaints received for messages with this tag
     *         - unsubs integer the total number of unsubscribe requests received for messages with this tag
     *         - opens integer the total number of times messages with this tag have been opened
     *         - clicks integer the total number of times tracked URLs in messages with this tag have been clicked
     *         - unique_opens integer the number of unique opens for emails sent with this tag
     *         - unique_clicks integer the number of unique clicks for emails sent with this tag
     */
    public function getList() {
        $_params = array();
        return $this->master->call('tags/list', $_params);
    }

    /**
     * Deletes a tag permanently. Deleting a tag removes the tag from any messages
that have been sent, and also deletes the tag's stats. There is no way to
undo this operation, so use it carefully.
     * @param string $tag a tag name
     * @return struct the tag that was deleted
     *     - tag string the actual tag as a string
     *     - reputation integer the tag's current reputation on a scale from 0 to 100.
     *     - sent integer the total number of messages sent with this tag
     *     - hard_bounces integer the total number of hard bounces by messages with this tag
     *     - soft_bounces integer the total number of soft bounces by messages with this tag
     *     - rejects integer the total number of rejected messages with this tag
     *     - complaints integer the total number of spam complaints received for messages with this tag
     *     - unsubs integer the total number of unsubscribe requests received for messages with this tag
     *     - opens integer the total number of times messages with this tag have been opened
     *     - clicks integer the total number of times tracked URLs in messages with this tag have been clicked
     *     - unique_opens integer the number of unique opens for emails sent with this tag
     *     - unique_clicks integer the number of unique clicks for emails sent with this tag
     */
    public function delete($tag) {
        $_params = array("tag" => $tag);
        return $this->master->call('tags/delete', $_params);
    }

    /**
     * Return more detailed information about a single tag, including aggregates of recent stats
     * @param string $tag an existing tag name
     * @return struct the detailed information on the tag
     *     - tag string the actual tag as a string
     *     - sent integer the total number of messages sent with this tag
     *     - hard_bounces integer the total number of hard bounces by messages with this tag
     *     - soft_bounces integer the total number of soft bounces by messages with this tag
     *     - rejects integer the total number of rejected messages with this tag
     *     - complaints integer the total number of spam complaints received for messages with this tag
     *     - unsubs integer the total number of unsubscribe requests received for messages with this tag
     *     - opens integer the total number of times messages with this tag have been opened
     *     - clicks integer the total number of times tracked URLs in messages with this tag have been clicked
     *     - stats struct an aggregate summary of the tag's sending stats
     *         - today struct stats with this tag so far today
     *             - sent integer the number of emails sent with this tag so far today
     *             - hard_bounces integer the number of emails hard bounced with this tag so far today
     *             - soft_bounces integer the number of emails soft bounced with this tag so far today
     *             - rejects integer the number of emails rejected for sending this tag so far today
     *             - complaints integer the number of spam complaints with this tag so far today
     *             - unsubs integer the number of unsubscribes with this tag so far today
     *             - opens integer the number of times emails have been opened with this tag so far today
     *             - unique_opens integer the number of unique opens for emails sent with this tag so far today
     *             - clicks integer the number of URLs that have been clicked with this tag so far today
     *             - unique_clicks integer the number of unique clicks for emails sent with this tag so far today
     *         - last_7_days struct stats with this tag in the last 7 days
     *             - sent integer the number of emails sent with this tag in the last 7 days
     *             - hard_bounces integer the number of emails hard bounced with this tag in the last 7 days
     *             - soft_bounces integer the number of emails soft bounced with this tag in the last 7 days
     *             - rejects integer the number of emails rejected for sending this tag in the last 7 days
     *             - complaints integer the number of spam complaints with this tag in the last 7 days
     *             - unsubs integer the number of unsubscribes with this tag in the last 7 days
     *             - opens integer the number of times emails have been opened with this tag in the last 7 days
     *             - unique_opens integer the number of unique opens for emails sent with this tag in the last 7 days
     *             - clicks integer the number of URLs that have been clicked with this tag in the last 7 days
     *             - unique_clicks integer the number of unique clicks for emails sent with this tag in the last 7 days
     *         - last_30_days struct stats with this tag in the last 30 days
     *             - sent integer the number of emails sent with this tag in the last 30 days
     *             - hard_bounces integer the number of emails hard bounced with this tag in the last 30 days
     *             - soft_bounces integer the number of emails soft bounced with this tag in the last 30 days
     *             - rejects integer the number of emails rejected for sending this tag in the last 30 days
     *             - complaints integer the number of spam complaints with this tag in the last 30 days
     *             - unsubs integer the number of unsubscribes with this tag in the last 30 days
     *             - opens integer the number of times emails have been opened with this tag in the last 30 days
     *             - unique_opens integer the number of unique opens for emails sent with this tag in the last 30 days
     *             - clicks integer the number of URLs that have been clicked with this tag in the last 30 days
     *             - unique_clicks integer the number of unique clicks for emails sent with this tag in the last 30 days
     *         - last_60_days struct stats with this tag in the last 60 days
     *             - sent integer the number of emails sent with this tag in the last 60 days
     *             - hard_bounces integer the number of emails hard bounced with this tag in the last 60 days
     *             - soft_bounces integer the number of emails soft bounced with this tag in the last 60 days
     *             - rejects integer the number of emails rejected for sending this tag in the last 60 days
     *             - complaints integer the number of spam complaints with this tag in the last 60 days
     *             - unsubs integer the number of unsubscribes with this tag in the last 60 days
     *             - opens integer the number of times emails have been opened with this tag in the last 60 days
     *             - unique_opens integer the number of unique opens for emails sent with this tag in the last 60 days
     *             - clicks integer the number of URLs that have been clicked with this tag in the last 60 days
     *             - unique_clicks integer the number of unique clicks for emails sent with this tag in the last 60 days
     *         - last_90_days struct stats with this tag in the last 90 days
     *             - sent integer the number of emails sent with this tag in the last 90 days
     *             - hard_bounces integer the number of emails hard bounced with this tag in the last 90 days
     *             - soft_bounces integer the number of emails soft bounced with this tag in the last 90 days
     *             - rejects integer the number of emails rejected for sending this tag in the last 90 days
     *             - complaints integer the number of spam complaints with this tag in the last 90 days
     *             - unsubs integer the number of unsubscribes with this tag in the last 90 days
     *             - opens integer the number of times emails have been opened with this tag in the last 90 days
     *             - unique_opens integer the number of unique opens for emails sent with this tag in the last 90 days
     *             - clicks integer the number of URLs that have been clicked with this tag in the last 90 days
     *             - unique_clicks integer the number of unique clicks for emails sent with this tag in the last 90 days
     */
    public function info($tag) {
        $_params = array("tag" => $tag);
        return $this->master->call('tags/info', $_params);
    }

    /**
     * Return the recent history (hourly stats for the last 30 days) for a tag
     * @param string $tag an existing tag name
     * @return array the array of history information
     *     - return[] struct the stats for a single hour
     *         - time string the hour as a UTC date string in YYYY-MM-DD HH:MM:SS format
     *         - sent integer the number of emails that were sent during the hour
     *         - hard_bounces integer the number of emails that hard bounced during the hour
     *         - soft_bounces integer the number of emails that soft bounced during the hour
     *         - rejects integer the number of emails that were rejected during the hour
     *         - complaints integer the number of spam complaints received during the hour
     *         - unsubs integer the number of unsubscribes received during the hour
     *         - opens integer the number of emails opened during the hour
     *         - unique_opens integer the number of unique opens generated by messages sent during the hour
     *         - clicks integer the number of tracked URLs clicked during the hour
     *         - unique_clicks integer the number of unique clicks generated by messages sent during the hour
     */
    public function timeSeries($tag) {
        $_params = array("tag" => $tag);
        return $this->master->call('tags/time-series', $_params);
    }

    /**
     * Return the recent history (hourly stats for the last 30 days) for all tags
     * @return array the array of history information
     *     - return[] struct the stats for a single hour
     *         - time string the hour as a UTC date string in YYYY-MM-DD HH:MM:SS format
     *         - sent integer the number of emails that were sent during the hour
     *         - hard_bounces integer the number of emails that hard bounced during the hour
     *         - soft_bounces integer the number of emails that soft bounced during the hour
     *         - rejects integer the number of emails that were rejected during the hour
     *         - complaints integer the number of spam complaints received during the hour
     *         - unsubs integer the number of unsubscribes received during the hour
     *         - opens integer the number of emails opened during the hour
     *         - unique_opens integer the number of unique opens generated by messages sent during the hour
     *         - clicks integer the number of tracked URLs clicked during the hour
     *         - unique_clicks integer the number of unique clicks generated by messages sent during the hour
     */
    public function allTimeSeries() {
        $_params = array();
        return $this->master->call('tags/all-time-series', $_params);
    }

}


