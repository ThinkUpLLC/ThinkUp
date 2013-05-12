<?php

/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightAPIController.php
 *
 * Copyright (c) 2013 Gina Trapani, Nilaksh Das
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
 * @copyright 2013 Gina Trapani, Nilaksh Das
 * @author Nilaksh Das <nilakshdas@gmail.com>
 *
 */
class InsightAPIController extends ThinkUpAuthAPIController {

    public function authControl() {
        /**
         * Check if the API is disabled and, if it is, throw the appropriate exception.
         *
         * Docs: http://thinkup.com/docs/userguide/api/errors/apidisabled.html
         */
        $is_api_disabled = Config::getInstance()->getValue('is_api_disabled');
        if ($is_api_disabled) {
            $this->setContentType('application/json');
            throw new APIDisabledException();
        }

        if (isset($_GET['since'])) {
            $since = $_GET['since'];
            $since = date("Y-m-d H:i:s", $since);
        } else {
            $since = time();
        }
        /*
         * Check if the view is cached and, if it is, return the cached version before any of the application login
         * is executed.
         */
        if ($this->shouldRefreshCache()) {
            $owner_email = $this->getLoggedInUser();
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($owner_email);

            // Fetch the correct InsightDAO from the DAOFactory
            $this->insight_dao = DAOFactory::getDAO('InsightDAO');
            $data = $this->insight_dao->getAllOwnerInstanceInsightsSince($owner->id, $since);
            if (!count($data)) {
                $this->setContentType('application/json');
                throw new InsightNotFoundException();
            }
            $this->setJsonData($data);
        } else {
            $this->setJsonData(array());
        }

        return $this->generateView();
    }
}
