<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ThinkUpAdminController.php
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
 * ThinkUp Admin Controller
 *
 * Parent controller for all logged-in admin user-only actions.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkUpAdminController extends ThinkUpAuthController {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function authControl() {
        if ($this->isAdmin()) {
            return $this->adminControl();
        } else {
            throw new Exception("You must be a ThinkUp admin to do this");
        }
    }
}