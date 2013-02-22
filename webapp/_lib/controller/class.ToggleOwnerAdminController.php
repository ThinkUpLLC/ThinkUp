<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ToggleOwnerAdminController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Toggle Owner Admin Controller
 * Promote or demote an owner from/to admin status.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ToggleOwnerAdminController extends ThinkUpAdminController {
    /**
     * Required query string parameters
     * @var array oid = owner ID, a = 1 or 0, active or inactive
     */
    var $REQUIRED_PARAMS = array('oid', 'a');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('session.toggle.tpl');
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('Missing required parameters.');
                $this->is_missing_param = true;
            }
        }
    }

    public function adminControl(){
        if (!$this->is_missing_param) {
            // verify CSRF token
            $this->validateCSRFToken();
            $is_admin = ($_GET["a"] != 1)?false:true;
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $this->addToView('result', $owner_dao->setOwnerAdmin($_GET["oid"], $is_admin));
        }
        return $this->generateView();
    }
}