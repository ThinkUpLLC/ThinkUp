<?php

class Mandrill_Ips {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

    /**
     * Lists your dedicated IPs.
     * @return array an array of structs for each dedicated IP
     *     - return[] struct information about a single dedicated IP
     *         - ip string the ip address
     *         - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - pool string the name of the pool that this dedicated IP belongs to
     *         - domain string the domain name (reverse dns) of this dedicated IP
     *         - custom_dns struct information about the ip's custom dns, if it has been configured
     *             - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *             - valid boolean whether the ip's custom dns is currently valid
     *             - error string if the ip's custom dns is invalid, this will include details about the error
     *         - warmup struct information about the ip's warmup status
     *             - warming_up boolean whether the ip is currently in warmup mode
     *             - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *             - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function getList() {
        $_params = array();
        return $this->master->call('ips/list', $_params);
    }

    /**
     * Retrieves information about a single dedicated ip.
     * @param string $ip a dedicated IP address
     * @return struct Information about the dedicated ip
     *     - ip string the ip address
     *     - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - pool string the name of the pool that this dedicated IP belongs to
     *     - domain string the domain name (reverse dns) of this dedicated IP
     *     - custom_dns struct information about the ip's custom dns, if it has been configured
     *         - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *         - valid boolean whether the ip's custom dns is currently valid
     *         - error string if the ip's custom dns is invalid, this will include details about the error
     *     - warmup struct information about the ip's warmup status
     *         - warming_up boolean whether the ip is currently in warmup mode
     *         - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function info($ip) {
        $_params = array("ip" => $ip);
        return $this->master->call('ips/info', $_params);
    }

    /**
     * Requests an additional dedicated IP for your account. Accounts may
have one outstanding request at any time, and provisioning requests
are processed within 24 hours.
     * @param boolean $warmup whether to enable warmup mode for the ip
     * @param string $pool the id of the pool to add the dedicated ip to, or null to use your account's default pool
     * @return struct a description of the provisioning request that was created
     *     - requested_at string the date and time that the request was created as a UTC timestamp in YYYY-MM-DD HH:MM:SS format
     */
    public function provision($warmup=false, $pool=null) {
        $_params = array("warmup" => $warmup, "pool" => $pool);
        return $this->master->call('ips/provision', $_params);
    }

    /**
     * Begins the warmup process for a dedicated IP. During the warmup process,
Mandrill will gradually increase the percentage of your mail that is sent over
the warming-up IP, over a period of roughly 30 days. The rest of your mail
will be sent over shared IPs or other dedicated IPs in the same pool.
     * @param string $ip a dedicated ip address
     * @return struct Information about the dedicated IP
     *     - ip string the ip address
     *     - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - pool string the name of the pool that this dedicated IP belongs to
     *     - domain string the domain name (reverse dns) of this dedicated IP
     *     - custom_dns struct information about the ip's custom dns, if it has been configured
     *         - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *         - valid boolean whether the ip's custom dns is currently valid
     *         - error string if the ip's custom dns is invalid, this will include details about the error
     *     - warmup struct information about the ip's warmup status
     *         - warming_up boolean whether the ip is currently in warmup mode
     *         - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function startWarmup($ip) {
        $_params = array("ip" => $ip);
        return $this->master->call('ips/start-warmup', $_params);
    }

    /**
     * Cancels the warmup process for a dedicated IP.
     * @param string $ip a dedicated ip address
     * @return struct Information about the dedicated IP
     *     - ip string the ip address
     *     - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - pool string the name of the pool that this dedicated IP belongs to
     *     - domain string the domain name (reverse dns) of this dedicated IP
     *     - custom_dns struct information about the ip's custom dns, if it has been configured
     *         - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *         - valid boolean whether the ip's custom dns is currently valid
     *         - error string if the ip's custom dns is invalid, this will include details about the error
     *     - warmup struct information about the ip's warmup status
     *         - warming_up boolean whether the ip is currently in warmup mode
     *         - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function cancelWarmup($ip) {
        $_params = array("ip" => $ip);
        return $this->master->call('ips/cancel-warmup', $_params);
    }

    /**
     * Moves a dedicated IP to a different pool.
     * @param string $ip a dedicated ip address
     * @param string $pool the name of the new pool to add the dedicated ip to
     * @param boolean $create_pool whether to create the pool if it does not exist; if false and the pool does not exist, an Unknown_Pool will be thrown.
     * @return struct Information about the updated state of the dedicated IP
     *     - ip string the ip address
     *     - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - pool string the name of the pool that this dedicated IP belongs to
     *     - domain string the domain name (reverse dns) of this dedicated IP
     *     - custom_dns struct information about the ip's custom dns, if it has been configured
     *         - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *         - valid boolean whether the ip's custom dns is currently valid
     *         - error string if the ip's custom dns is invalid, this will include details about the error
     *     - warmup struct information about the ip's warmup status
     *         - warming_up boolean whether the ip is currently in warmup mode
     *         - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function setPool($ip, $pool, $create_pool=false) {
        $_params = array("ip" => $ip, "pool" => $pool, "create_pool" => $create_pool);
        return $this->master->call('ips/set-pool', $_params);
    }

    /**
     * Deletes a dedicated IP. This is permanent and cannot be undone.
     * @param string $ip the dedicated ip to remove from your account
     * @return struct a description of the ip that was removed from your account.
     *     - ip string the ip address
     *     - deleted string a boolean indicating whether the ip was successfully deleted
     */
    public function delete($ip) {
        $_params = array("ip" => $ip);
        return $this->master->call('ips/delete', $_params);
    }

    /**
     * Lists your dedicated IP pools.
     * @return array the dedicated IP pools for your account, up to a maximum of 1,000
     *     - return[] struct information about each dedicated IP pool
     *         - name string this pool's name
     *         - created_at string the date and time that this pool was created as a UTC timestamp in YYYY-MM-DD HH:MM:SS format
     *         - ips array the dedicated IPs in this pool
     *             - ips[] struct information about each dedicated IP
     *                 - ip string the ip address
     *                 - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *                 - pool string the name of the pool that this dedicated IP belongs to
     *                 - domain string the domain name (reverse dns) of this dedicated IP
     *                 - custom_dns struct information about the ip's custom dns, if it has been configured
     *                     - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *                     - valid boolean whether the ip's custom dns is currently valid
     *                     - error string if the ip's custom dns is invalid, this will include details about the error
     *                 - warmup struct information about the ip's warmup status
     *                     - warming_up boolean whether the ip is currently in warmup mode
     *                     - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *                     - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function listPools() {
        $_params = array();
        return $this->master->call('ips/list-pools', $_params);
    }

    /**
     * Describes a single dedicated IP pool.
     * @param string $pool a pool name
     * @return struct Information about the dedicated ip pool
     *     - name string this pool's name
     *     - created_at string the date and time that this pool was created as a UTC timestamp in YYYY-MM-DD HH:MM:SS format
     *     - ips array the dedicated IPs in this pool
     *         - ips[] struct information about each dedicated IP
     *             - ip string the ip address
     *             - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *             - pool string the name of the pool that this dedicated IP belongs to
     *             - domain string the domain name (reverse dns) of this dedicated IP
     *             - custom_dns struct information about the ip's custom dns, if it has been configured
     *                 - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *                 - valid boolean whether the ip's custom dns is currently valid
     *                 - error string if the ip's custom dns is invalid, this will include details about the error
     *             - warmup struct information about the ip's warmup status
     *                 - warming_up boolean whether the ip is currently in warmup mode
     *                 - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *                 - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function poolInfo($pool) {
        $_params = array("pool" => $pool);
        return $this->master->call('ips/pool-info', $_params);
    }

    /**
     * Creates a pool and returns it. If a pool already exists with this
name, no action will be performed.
     * @param string $pool the name of a pool to create
     * @return struct Information about the dedicated ip pool
     *     - name string this pool's name
     *     - created_at string the date and time that this pool was created as a UTC timestamp in YYYY-MM-DD HH:MM:SS format
     *     - ips array the dedicated IPs in this pool
     *         - ips[] struct information about each dedicated IP
     *             - ip string the ip address
     *             - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *             - pool string the name of the pool that this dedicated IP belongs to
     *             - domain string the domain name (reverse dns) of this dedicated IP
     *             - custom_dns struct information about the ip's custom dns, if it has been configured
     *                 - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *                 - valid boolean whether the ip's custom dns is currently valid
     *                 - error string if the ip's custom dns is invalid, this will include details about the error
     *             - warmup struct information about the ip's warmup status
     *                 - warming_up boolean whether the ip is currently in warmup mode
     *                 - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *                 - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function createPool($pool) {
        $_params = array("pool" => $pool);
        return $this->master->call('ips/create-pool', $_params);
    }

    /**
     * Deletes a pool. A pool must be empty before you can delete it, and you cannot delete your default pool.
     * @param string $pool the name of the pool to delete
     * @return struct information about the status of the pool that was deleted
     *     - pool string the name of the pool
     *     - deleted boolean whether the pool was deleted
     */
    public function deletePool($pool) {
        $_params = array("pool" => $pool);
        return $this->master->call('ips/delete-pool', $_params);
    }

    /**
     * Tests whether a domain name is valid for use as the custom reverse
DNS for a dedicated IP.
     * @param string $ip a dedicated ip address
     * @param string $domain the domain name to test
     * @return struct validation results for the domain
     *     - valid string whether the domain name has a correctly-configured A record pointing to the ip address
     *     - error string if valid is false, this will contain details about why the domain's A record is incorrect
     */
    public function checkCustomDns($ip, $domain) {
        $_params = array("ip" => $ip, "domain" => $domain);
        return $this->master->call('ips/check-custom-dns', $_params);
    }

    /**
     * Configures the custom DNS name for a dedicated IP.
     * @param string $ip a dedicated ip address
     * @param string $domain a domain name to set as the dedicated IP's custom dns name.
     * @return struct information about the dedicated IP's new configuration
     *     - ip string the ip address
     *     - created_at string the date and time that the dedicated IP was created as a UTC string in YYYY-MM-DD HH:MM:SS format
     *     - pool string the name of the pool that this dedicated IP belongs to
     *     - domain string the domain name (reverse dns) of this dedicated IP
     *     - custom_dns struct information about the ip's custom dns, if it has been configured
     *         - enabled boolean a boolean indicating whether custom dns has been configured for this ip
     *         - valid boolean whether the ip's custom dns is currently valid
     *         - error string if the ip's custom dns is invalid, this will include details about the error
     *     - warmup struct information about the ip's warmup status
     *         - warming_up boolean whether the ip is currently in warmup mode
     *         - start_at string the start time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     *         - end_at string the end date and time for the warmup process as a UTC string in YYYY-MM-DD HH:MM:SS format
     */
    public function setCustomDns($ip, $domain) {
        $_params = array("ip" => $ip, "domain" => $domain);
        return $this->master->call('ips/set-custom-dns', $_params);
    }

}


