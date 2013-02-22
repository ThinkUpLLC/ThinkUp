<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.TogglePublicInstanceController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Toggle Public Instance Controller
 * Add/remove an instance from the public timeline.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TogglePublicInstanceController extends ThinkUpAuthController {
    /**
     * Required query string parameters
     * @var array u = instance username, p = 1 or 0, active or inactive
     */
    var $REQUIRED_PARAMS = array('u', 'p');

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

    public function authControl(){
        if (!$this->is_missing_param) {
            // verify CSRF token
            $this->validateCSRFToken();
            $is_public = ($_GET["p"] != 1)?false:true;
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $this->addToView('result', $instance_dao->setPublic($_GET["u"], $is_public));
        }
        return $this->generateView();
    }
}