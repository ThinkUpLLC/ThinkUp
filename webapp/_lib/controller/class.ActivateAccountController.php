<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ActivateAccountController.php
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
 * Activate Account Controller
 * When a user registers for a ThinkUp account s/he receives an email with an activation link that lands here.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ActivateAccountController extends ThinkUpController {
    /**
     * Required query string parameters
     * @var array usr = instance email address, code = activation code
     */
    var $REQUIRED_PARAMS = array('usr', 'code');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;
    /**
     * Constructor
     * @param bool $session_started
     * @return ActivateAccountController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->is_missing_param = true;
            }
        }
    }

    public function control() {
        $controller = new LoginController(true);
        if ($this->is_missing_param) {
            $controller->addErrorMessage('Invalid account activation credentials.');
        } else {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $acode = $owner_dao->getActivationCode($_GET['usr']);

            if ($_GET['code'] == $acode['activation_code']) {
                $owner = $owner_dao->getByEmail($_GET['usr']);
                if (isset($owner) && isset($owner->is_activated)) {
                    if ($owner->is_activated == 1) {
                        $controller->addSuccessMessage("You have already activated your account. Please log in.");
                    } else {
                        $owner_dao->activateOwner($_GET['usr']);
                        $controller->addSuccessMessage("Success! Your account has been activated. Please log in.");
                    }
                } else {
                    $controller->addErrorMessage('Houston, we have a problem: Account activation failed.');
                }
            } else {
                $controller->addErrorMessage('Houston, we have a problem: Account activation failed.');
            }
        }
        return $controller->go();
    }
}
