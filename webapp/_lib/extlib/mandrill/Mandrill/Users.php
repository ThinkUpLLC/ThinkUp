<?php

class Mandrill_Users {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Return the information about the API-connected user
     * @return struct the user information including username, key, reputation, quota, and historical sending stats
     *     - username string the username of the user (used for SMTP authentication)
     *     - created_at string the date and time that the user's Mandrill account was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - public_id string a unique, permanent identifier for this user
     *     - reputation integer the reputation of the user on a scale from 0 to 100, with 75 generally being a "good" reputation
     *     - hourly_quota integer the maximum number of emails Mandrill will deliver for this user each hour.  Any emails beyond that will be accepted and queued for later delivery.  Users with higher reputations will have higher hourly quotas
     *     - backlog integer the number of emails that are queued for delivery due to exceeding your monthly or hourly quotas
     *     - stats struct an aggregate summary of the account's sending stats
     *         - today struct stats for this user so far today
     *             - sent integer the number of emails sent for this user so far today
     *             - hard_bounces integer the number of emails hard bounced for this user so far today
     *             - soft_bounces integer the number of emails soft bounced for this user so far today
     *             - rejects integer the number of emails rejected for sending this user so far today
     *             - complaints integer the number of spam complaints for this user so far today
     *             - unsubs integer the number of unsubscribes for this user so far today
     *             - opens integer the number of times emails have been opened for this user so far today
     *             - unique_opens integer the number of unique opens for emails sent for this user so far today
     *             - clicks integer the number of URLs that have been clicked for this user so far today
     *             - unique_clicks integer the number of unique clicks for emails sent for this user so far today
     *         - last_7_days struct stats for this user in the last 7 days
     *             - sent integer the number of emails sent for this user in the last 7 days
     *             - hard_bounces integer the number of emails hard bounced for this user in the last 7 days
     *             - soft_bounces integer the number of emails soft bounced for this user in the last 7 days
     *             - rejects integer the number of emails rejected for sending this user in the last 7 days
     *             - complaints integer the number of spam complaints for this user in the last 7 days
     *             - unsubs integer the number of unsubscribes for this user in the last 7 days
     *             - opens integer the number of times emails have been opened for this user in the last 7 days
     *             - unique_opens integer the number of unique opens for emails sent for this user in the last 7 days
     *             - clicks integer the number of URLs that have been clicked for this user in the last 7 days
     *             - unique_clicks integer the number of unique clicks for emails sent for this user in the last 7 days
     *         - last_30_days struct stats for this user in the last 30 days
     *             - sent integer the number of emails sent for this user in the last 30 days
     *             - hard_bounces integer the number of emails hard bounced for this user in the last 30 days
     *             - soft_bounces integer the number of emails soft bounced for this user in the last 30 days
     *             - rejects integer the number of emails rejected for sending this user in the last 30 days
     *             - complaints integer the number of spam complaints for this user in the last 30 days
     *             - unsubs integer the number of unsubscribes for this user in the last 30 days
     *             - opens integer the number of times emails have been opened for this user in the last 30 days
     *             - unique_opens integer the number of unique opens for emails sent for this user in the last 30 days
     *             - clicks integer the number of URLs that have been clicked for this user in the last 30 days
     *             - unique_clicks integer the number of unique clicks for emails sent for this user in the last 30 days
     *         - last_60_days struct stats for this user in the last 60 days
     *             - sent integer the number of emails sent for this user in the last 60 days
     *             - hard_bounces integer the number of emails hard bounced for this user in the last 60 days
     *             - soft_bounces integer the number of emails soft bounced for this user in the last 60 days
     *             - rejects integer the number of emails rejected for sending this user in the last 60 days
     *             - complaints integer the number of spam complaints for this user in the last 60 days
     *             - unsubs integer the number of unsubscribes for this user in the last 60 days
     *             - opens integer the number of times emails have been opened for this user in the last 60 days
     *             - unique_opens integer the number of unique opens for emails sent for this user in the last 60 days
     *             - clicks integer the number of URLs that have been clicked for this user in the last 60 days
     *             - unique_clicks integer the number of unique clicks for emails sent for this user in the last 60 days
     *         - last_90_days struct stats for this user in the last 90 days
     *             - sent integer the number of emails sent for this user in the last 90 days
     *             - hard_bounces integer the number of emails hard bounced for this user in the last 90 days
     *             - soft_bounces integer the number of emails soft bounced for this user in the last 90 days
     *             - rejects integer the number of emails rejected for sending this user in the last 90 days
     *             - complaints integer the number of spam complaints for this user in the last 90 days
     *             - unsubs integer the number of unsubscribes for this user in the last 90 days
     *             - opens integer the number of times emails have been opened for this user in the last 90 days
     *             - unique_opens integer the number of unique opens for emails sent for this user in the last 90 days
     *             - clicks integer the number of URLs that have been clicked for this user in the last 90 days
     *             - unique_clicks integer the number of unique clicks for emails sent for this user in the last 90 days
     *         - all_time struct stats for the lifetime of the user's account
     *             - sent integer the number of emails sent in the lifetime of the user's account
     *             - hard_bounces integer the number of emails hard bounced in the lifetime of the user's account
     *             - soft_bounces integer the number of emails soft bounced in the lifetime of the user's account
     *             - rejects integer the number of emails rejected for sending this user so far today
     *             - complaints integer the number of spam complaints in the lifetime of the user's account
     *             - unsubs integer the number of unsubscribes in the lifetime of the user's account
     *             - opens integer the number of times emails have been opened in the lifetime of the user's account
     *             - unique_opens integer the number of unique opens for emails sent in the lifetime of the user's account
     *             - clicks integer the number of URLs that have been clicked in the lifetime of the user's account
     *             - unique_clicks integer the number of unique clicks for emails sent in the lifetime of the user's account
     */
    public function info() {
        $_params = array();
        return $this->master->call('users/info', $_params);
    }

    /**
     * Validate an API key and respond to a ping
     * @return string the string "PONG!"
     */
    public function ping() {
        $_params = array();
        return $this->master->call('users/ping', $_params);
    }

    /**
     * Validate an API key and respond to a ping (anal JSON parser version)
     * @return struct a struct with one key "PING" with a static value "PONG!"
     */
    public function ping2() {
        $_params = array();
        return $this->master->call('users/ping2', $_params);
    }

    /**
     * Return the senders that have tried to use this account, both verified and unverified
     * @return array an array of sender data, one for each sending addresses used by the account
     *     - return[] struct the information on each sending address in the account
     *         - address string the sender's email address
     *         - created_at string the date and time that the sender was first seen by Mandrill as a UTC date string in YYYY-MM-DD HH:MM:SS format
     *         - sent integer the total number of messages sent by this sender
     *         - hard_bounces integer the total number of hard bounces by messages by this sender
     *         - soft_bounces integer the total number of soft bounces by messages by this sender
     *         - rejects integer the total number of rejected messages by this sender
     *         - complaints integer the total number of spam complaints received for messages by this sender
     *         - unsubs integer the total number of unsubscribe requests received for messages by this sender
     *         - opens integer the total number of times messages by this sender have been opened
     *         - clicks integer the total number of times tracked URLs in messages by this sender have been clicked
     *         - unique_opens integer the number of unique opens for emails sent for this sender
     *         - unique_clicks integer the number of unique clicks for emails sent for this sender
     */
    public function senders() {
        $_params = array();
        return $this->master->call('users/senders', $_params);
    }

}


