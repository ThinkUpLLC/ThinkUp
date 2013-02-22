<?php
/**
 *
 * ThinkUp/tests/classes/class.TestAuthAPIController.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau
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
 * Test AuthAPIController
 *
 * Test auth API controller to try the ThinkUpAuthAPIController abstract class and Controller interface
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 */
class TestAuthAPIController extends ThinkUpAuthAPIController {
    public function authControl() {
        if ($this->isAPICall()) {
            $this->setContentType('application/json; charset=UTF-8');
            return '{"result":"success"}';
        } else {
            return '<html><body>Success</body></html>';
        }
    }
}