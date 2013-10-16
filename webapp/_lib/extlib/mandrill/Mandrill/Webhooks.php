<?php

class Mandrill_Webhooks {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Get the list of all webhooks defined on the account
     * @return array the webhooks associated with the account
     *     - return[] struct the individual webhook info
     *         - id integer a unique integer indentifier for the webhook
     *         - url string The URL that the event data will be posted to
     *         - description string a description of the webhook
     *         - auth_key string the key used to requests for this webhook
     *         - events array The message events that will be posted to the hook
     *             - events[] string the individual message event (send, hard_bounce, soft_bounce, open, click, spam, unsub, or reject)
     *         - created_at string the date and time that the webhook was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - last_sent_at string the date and time that the webhook last successfully received events as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - batches_sent integer the number of event batches that have ever been sent to this webhook
     *         - events_sent integer the total number of events that have ever been sent to this webhook
     *         - last_error string if we've ever gotten an error trying to post to this webhook, the last error that we've seen
     */
    public function getList() {
        $_params = array();
        return $this->master->call('webhooks/list', $_params);
    }

    /**
     * Add a new webhook
     * @param string $url the URL to POST batches of events
     * @param string $description an optional description of the webhook
     * @param array $events an optional list of events that will be posted to the webhook
     *     - events[] string the individual event to listen for
     * @return struct the information saved about the new webhook
     *     - id integer a unique integer indentifier for the webhook
     *     - url string The URL that the event data will be posted to
     *     - description string a description of the webhook
     *     - auth_key string the key used to requests for this webhook
     *     - events array The message events that will be posted to the hook
     *         - events[] string the individual message event (send, hard_bounce, soft_bounce, open, click, spam, unsub, or reject)
     *     - created_at string the date and time that the webhook was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - last_sent_at string the date and time that the webhook last successfully received events as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - batches_sent integer the number of event batches that have ever been sent to this webhook
     *     - events_sent integer the total number of events that have ever been sent to this webhook
     *     - last_error string if we've ever gotten an error trying to post to this webhook, the last error that we've seen
     */
    public function add($url, $description=null, $events=array()) {
        $_params = array("url" => $url, "description" => $description, "events" => $events);
        return $this->master->call('webhooks/add', $_params);
    }

    /**
     * Given the ID of an existing webhook, return the data about it
     * @param integer $id the unique identifier of a webhook belonging to this account
     * @return struct the information about the webhook
     *     - id integer a unique integer indentifier for the webhook
     *     - url string The URL that the event data will be posted to
     *     - description string a description of the webhook
     *     - auth_key string the key used to requests for this webhook
     *     - events array The message events that will be posted to the hook
     *         - events[] string the individual message event (send, hard_bounce, soft_bounce, open, click, spam, unsub, or reject)
     *     - created_at string the date and time that the webhook was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - last_sent_at string the date and time that the webhook last successfully received events as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - batches_sent integer the number of event batches that have ever been sent to this webhook
     *     - events_sent integer the total number of events that have ever been sent to this webhook
     *     - last_error string if we've ever gotten an error trying to post to this webhook, the last error that we've seen
     */
    public function info($id) {
        $_params = array("id" => $id);
        return $this->master->call('webhooks/info', $_params);
    }

    /**
     * Update an existing webhook
     * @param integer $id the unique identifier of a webhook belonging to this account
     * @param string $url the URL to POST batches of events
     * @param string $description an optional description of the webhook
     * @param array $events an optional list of events that will be posted to the webhook
     *     - events[] string the individual event to listen for
     * @return struct the information for the updated webhook
     *     - id integer a unique integer indentifier for the webhook
     *     - url string The URL that the event data will be posted to
     *     - description string a description of the webhook
     *     - auth_key string the key used to requests for this webhook
     *     - events array The message events that will be posted to the hook
     *         - events[] string the individual message event (send, hard_bounce, soft_bounce, open, click, spam, unsub, or reject)
     *     - created_at string the date and time that the webhook was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - last_sent_at string the date and time that the webhook last successfully received events as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - batches_sent integer the number of event batches that have ever been sent to this webhook
     *     - events_sent integer the total number of events that have ever been sent to this webhook
     *     - last_error string if we've ever gotten an error trying to post to this webhook, the last error that we've seen
     */
    public function update($id, $url, $description=null, $events=array()) {
        $_params = array("id" => $id, "url" => $url, "description" => $description, "events" => $events);
        return $this->master->call('webhooks/update', $_params);
    }

    /**
     * Delete an existing webhook
     * @param integer $id the unique identifier of a webhook belonging to this account
     * @return struct the information for the deleted webhook
     *     - id integer a unique integer indentifier for the webhook
     *     - url string The URL that the event data will be posted to
     *     - description string a description of the webhook
     *     - auth_key string the key used to requests for this webhook
     *     - events array The message events that will be posted to the hook
     *         - events[] string the individual message event (send, hard_bounce, soft_bounce, open, click, spam, unsub, or reject)
     *     - created_at string the date and time that the webhook was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - last_sent_at string the date and time that the webhook last successfully received events as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - batches_sent integer the number of event batches that have ever been sent to this webhook
     *     - events_sent integer the total number of events that have ever been sent to this webhook
     *     - last_error string if we've ever gotten an error trying to post to this webhook, the last error that we've seen
     */
    public function delete($id) {
        $_params = array("id" => $id);
        return $this->master->call('webhooks/delete', $_params);
    }

}


