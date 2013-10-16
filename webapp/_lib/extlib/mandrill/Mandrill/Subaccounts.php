<?php

class Mandrill_Subaccounts {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Get the list of subaccounts defined for the account, optionally filtered by a prefix
     * @param string $q an optional prefix to filter the subaccounts' ids and names
     * @return array the subaccounts for the account, up to a maximum of 1,000
     *     - return[] struct the individual subaccount info
     *         - id string a unique indentifier for the subaccount
     *         - name string an optional display name for the subaccount
     *         - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *         - status string the current sending status of the subaccount, one of "active" or "paused"
     *         - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *         - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *         - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *         - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function getList($q=null) {
        $_params = array("q" => $q);
        return $this->master->call('subaccounts/list', $_params);
    }

    /**
     * Add a new subaccount
     * @param string $id a unique identifier for the subaccount to be used in sending calls
     * @param string $name an optional display name to further identify the subaccount
     * @param string $notes optional extra text to associate with the subaccount
     * @param integer $custom_quota an optional manual hourly quota for the subaccount. If not specified, Mandrill will manage this based on reputation
     * @return struct the information saved about the new subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function add($id, $name=null, $notes=null, $custom_quota=null) {
        $_params = array("id" => $id, "name" => $name, "notes" => $notes, "custom_quota" => $custom_quota);
        return $this->master->call('subaccounts/add', $_params);
    }

    /**
     * Given the ID of an existing subaccount, return the data about it
     * @param string $id the unique identifier of the subaccount to query
     * @return struct the information about the subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - notes string optional extra text to associate with the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     *     - sent_hourly integer the number of emails the subaccount has sent in the last hour
     *     - hourly_quota integer the current hourly quota for the subaccount, either manual or reputation-based
     *     - last_30_days struct stats for this subaccount in the last 30 days
     *         - sent integer the number of emails sent for this subaccount in the last 30 days
     *         - hard_bounces integer the number of emails hard bounced for this subaccount in the last 30 days
     *         - soft_bounces integer the number of emails soft bounced for this subaccount in the last 30 days
     *         - rejects integer the number of emails rejected for sending this subaccount in the last 30 days
     *         - complaints integer the number of spam complaints for this subaccount in the last 30 days
     *         - unsubs integer the number of unsbuscribes for this subaccount in the last 30 days
     *         - opens integer the number of times emails have been opened for this subaccount in the last 30 days
     *         - unique_opens integer the number of unique opens for emails sent for this subaccount in the last 30 days
     *         - clicks integer the number of URLs that have been clicked for this subaccount in the last 30 days
     *         - unique_clicks integer the number of unique clicks for emails sent for this subaccount in the last 30 days
     */
    public function info($id) {
        $_params = array("id" => $id);
        return $this->master->call('subaccounts/info', $_params);
    }

    /**
     * Update an existing subaccount
     * @param string $id the unique identifier of the subaccount to update
     * @param string $name an optional display name to further identify the subaccount
     * @param string $notes optional extra text to associate with the subaccount
     * @param integer $custom_quota an optional manual hourly quota for the subaccount. If not specified, Mandrill will manage this based on reputation
     * @return struct the information for the updated subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function update($id, $name=null, $notes=null, $custom_quota=null) {
        $_params = array("id" => $id, "name" => $name, "notes" => $notes, "custom_quota" => $custom_quota);
        return $this->master->call('subaccounts/update', $_params);
    }

    /**
     * Delete an existing subaccount. Any email related to the subaccount will be saved, but stats will be removed and any future sending calls to this subaccount will fail.
     * @param string $id the unique identifier of the subaccount to delete
     * @return struct the information for the deleted subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function delete($id) {
        $_params = array("id" => $id);
        return $this->master->call('subaccounts/delete', $_params);
    }

    /**
     * Pause a subaccount's sending. Any future emails delivered to this subaccount will be queued for a maximum of 3 days until the subaccount is resumed.
     * @param string $id the unique identifier of the subaccount to pause
     * @return struct the information for the paused subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function pause($id) {
        $_params = array("id" => $id);
        return $this->master->call('subaccounts/pause', $_params);
    }

    /**
     * Resume a paused subaccount's sending
     * @param string $id the unique identifier of the subaccount to resume
     * @return struct the information for the resumed subaccount
     *     - id string a unique indentifier for the subaccount
     *     - name string an optional display name for the subaccount
     *     - custom_quota integer an optional manual hourly quota for the subaccount. If not specified, the hourly quota will be managed based on reputation
     *     - status string the current sending status of the subaccount, one of "active" or "paused"
     *     - reputation integer the subaccount's current reputation on a scale from 0 to 100
     *     - created_at string the date and time that the subaccount was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - first_sent_at string the date and time that the subaccount first sent as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - sent_weekly integer the number of emails the subaccount has sent so far this week (weeks start on midnight Monday, UTC)
     *     - sent_monthly integer the number of emails the subaccount has sent so far this month (months start on midnight of the 1st, UTC)
     *     - sent_total integer the number of emails the subaccount has sent since it was created
     */
    public function resume($id) {
        $_params = array("id" => $id);
        return $this->master->call('subaccounts/resume', $_params);
    }

}


