<?php

/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightAPIController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani, Nilaksh Das
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
 * Insight API Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani, Nilaksh Das
 * @author Nilaksh Das <nilakshdas@gmail.com>
 *
 */
class InsightAPIController extends ThinkUpAuthAPIController {
    /**
     * The network to query. Defaults to twitter.
     * @var str
     */
    public $network = 'twitter';
    /**
     * The user ID to use. No default value. In requests that require user data, either this or username must be set.
     * @var int
     */
    public $user_id;
    /**
     * The username to use. No default value. In requests that require user data, either this or user ID must be set.
     * @var str
     */
    public $username;
    /**
     * Internal unique ID.
     * @var int
     */
    public $insight_id;
    /**
     * Prefix to the text content of the alert.
     * @var str
     */
    public $prefix;
    /**
     * ThinkUp API Key to access the insight.
     * @var str
     */
    private $api_key;
    /**
     * The number of results to return. Defaults to 20.
     * @var int
     */
    public $count = 20;
    /**
     * The page of results to return. Defaults to 1 (the first page).
     * @var int
     */
    public $page = 1;
    /**
     * What to order the results by. Does not work on all calls. Defaults to "default". Different calls handle this
     * value differently.
     * @var str
     */
    public $order_by = 'date';
    /**
     * The direction to order the results in. Defaults to DESC for descending order.
     * @var str DESC or ASC
     */
    public $direction = 'DESC';
    /**
     * In time range API calls, this is the starting date. Can be a Unix timestamp or a valid time string. Defaults to
     * 0 which represents midnight on Jan 1st 1970.
     * @var mixed 
     */
    public $from = 0;
    /**
     * In time range API calls, this is the end date. Can be a Unix timestamp or a valid time string.
     * @var mixed
     */
    public $until_time;
    /**
     * Whether or not to trim the user to just the user ID. Defaults to false.
     * @var bool
     */
    public $trim_user = false;
    /**
     * A User object set when either the user_id or username variables are set. If you are using User data at any point
     * in this class, you should use this object.
     * @var User
     */
    private $user;
    /**
     * @var InsightDAO
     */
    private $insight_dao;
    /**
     * @var UserDAO
     */
    private $user_dao;
    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setContentType('application/json');
        $this->view_mgr->cache_lifetime = 600;

        /*
         * START READ IN OF QUERY STRING VARS
         */
        if (isset($_GET['network'])) {
            $this->network = $_GET['network'];
        }
        if (isset($_GET['user_id'])) {
            if (is_numeric($_GET['user_id'])) {
                $this->user_id = $_GET['user_id'];
            }
        }
        if (isset($_GET['username'])) {
            $this->username = $_GET['username'];
        }
        if (isset($_GET['insight_id'])) {
            if (is_numeric($_GET['insight_id'])) {
                $this->insight_id = $_GET['insight_id'];
            }
        }
        if (isset($_GET['prefix'])) {
            $this->prefix = $_GET['prefix'];
        }
        if (isset($_GET['api_key'])) {
            $this->api_key = $_GET['api_key'];
        }
        if (isset($_GET['count'])) {
            if (is_numeric($_GET['count'])) {
                $this->count = (int) $_GET['count'] > 200 ? 200 : (int) $_GET['count'];
            }
        }
        if (isset($_GET['page'])) {
            if (is_numeric($_GET['page'])) {
                $this->page = (int) $_GET['page'];
            }
        }
        if (isset($_GET['order_by'])) {
            $this->order_by = $this->parseOrderBy($_GET['order_by']);
        }
        if (isset($_GET['direction'])) {
            $this->direction = $_GET['direction'] == 'DESC' ? 'DESC' : 'ASC';
        }
        if (isset($_GET['from'])) {
            $this->from = $_GET['from'];
        }
        if (isset($_GET['until'])) {
            $this->until = $_GET['until'];
        }
        if (isset($_GET['trim_user'])) {
            $this->trim_user = $this->isTrue($_GET['trim_user']);
        }
        /*
         * END READ IN OF QUERY STRING VARS
         */
    }

    /**
     * Convert the order_by option to database column.
     *
     * For example, 'date' gets converted into the appropriate database colum name: 'pub_date'.
     *
     * @param string $order_by The value from $_GET['order_by']
     * @return string A valid database column.
     */
    private function parseOrderBy($order_by) {
        switch ($order_by) {
            case 'date': $order_by = 'date';
            break;

            default: $order_by = $this->order_by;
            break;
        }

        return $order_by;
    }

    /**
     * Determine whether the given value represents true or not. Used for the boolean $_GET values such as
     * trim_user and include_entities.
     *
     * @param string $var The value to determine.
     * @return bool True if $var is 't', 'true' or '1'.
     */
    private function isTrue($var) {
        if (isset($var) && !is_null($var)) {
            return $var == 'true' || $var == 't' || $var == '1';
        } else {
            return false;
        }
    }

    public function control() {
        /*
         * Check if the view is cached and, if it is, return the cached version before any of the application login
         * is executed.
         */
        if ($this->view_mgr->isViewCached()) {
            if ($this->view_mgr->is_cached('json.tpl', $this->getCacheKeyString())) {
                // set the json data to keep the ThinkUpController happy.
                $this->setJsonData(array());
                return $this->generateView();
            }
        }

        /*
         * Check if the API is disabled and, if it is, throw the appropriate exception.
         *
         * Docs: http://thinkup.com/docs/userguide/api/errors/apidisabled.html
         */
        $is_api_disabled = Config::getInstance()->getValue('is_api_disabled');
        if ($is_api_disabled) {
            throw new APIDisabledException();
        }

        // fetch the correct PostDAO and UserDAO from the DAOFactory
        $this->post_dao = DAOFactory::getDAO('InsightDAO');
        $this->user_dao = DAOFactory::getDAO('UserDAO');

        /*
         * Use the information gathered from the query string to retrieve a
         * User object. This will be the standard object with which to get
         * User information from in API calls.
         */
        if ($this->user_id != null) {
            $this->user = $this->user_dao->getDetails($this->user_id, $this->network);
        } else if ($this->username != null) {
            $this->user = $this->user_dao->getUserByName($this->username, $this->network);
        } else {
            $this->user = null;
        }
        //Privacy checks
        $email = $this->getLoggedInUser();
        $owner = parent::getOwner($email);
        if (!(isset($_GET['api_key']) && $this->api_key == $owner->api_key)) {
            $m = 'An insight request requires a valid ThinkUp API Key to be specified.';
            throw new APIOAuthException($m);
        }

        $data = $this->insight_dao->getAllInstanceInsights($count,$page);

        // Insights based on parameters entered by the user to be added.

        $this->setJsonData($data);
        return $this->generateView();
    }
}
