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
     * In time range API calls, this is the starting date. Can be a Unix timestamp or a valid time string.
     * Defaults to 0 which represents midnight on Jan 1st 1970.
     * @var mixed 
     */
    public $from = 0;
    /**
     * In time range API calls, this is the end date. Can be a Unix timestamp or a valid time string.
     * @var mixed
     */
    public $until = null;
    /**
     * ThinkUp API Key to access the insight.
     * @var str
     */
    private $api_key;
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
        if (isset($_GET['from'])) {
            $this->from = $_GET['from'];
        }
        if (isset($_GET['until'])) {
            $this->until = $_GET['until'];
        }
        if (isset($_GET['api_key'])) {
            $this->api_key = $_GET['api_key'];
        }
        /*
         * END READ IN OF QUERY STRING VARS
         */
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

        // Fetch the correct InsightDAO from the DAOFactory
        $this->insight_dao = DAOFactory::getDAO('InsightDAO');

        //Privacy checks
        $email = $this->getLoggedInUser();
        $owner = parent::getOwner($email);
        if ($this->api_key != $owner->api_key) {
            $m = 'An insight request requires a valid ThinkUp API Key to be specified.';
            throw new APIOAuthException($m);
        }

        $data = $this->insight_dao->getInsightsForInstancesInRange($this->from, $this->until,
        $this->count, $this->page, false);

        $this->setJsonData($data);
        return $this->generateView();
    }
}
