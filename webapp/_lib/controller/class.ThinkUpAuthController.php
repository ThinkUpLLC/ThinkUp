<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ThinkUpAuthController.php
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
 * ThinkUp Authorized Controller
 *
 * Parent controller for all logged-in user-only actions
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkUpAuthController extends ThinkUpController {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function control() {
        $response = $this->preAuthControl();
        if (!$response) {
            if ($this->isLoggedIn()) {
                return $this->authControl();
            } else {
                return $this->bounce();
            }
        } else {
            return $response;
        }
    }

    /**
     * A child class can override this method to define other auth mechanisms.
     * If the return is not false it assumes the child class has validated the user and has called authControl()
     * @return boolean PreAuthed
     */
    protected function preAuthControl() {
        return false;
    }

    /**
     * Bounce user to public page or to error page.
     * @TODO bounce back to original action once signed in
     */
    protected function bounce() {
        $config = Config::getInstance();

        if (get_class($this)=='InsightStreamController' || get_class($this)=='PostController') {
            $controller = new InsightStreamController(true);
            return $controller->go();
        } else {
            throw new ControllerAuthException('You must log in to access this controller: ' . get_class($this));
        }
    }
}