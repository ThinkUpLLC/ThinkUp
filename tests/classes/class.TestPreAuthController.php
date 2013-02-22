<?php
/**
 *
 * ThinkUp/tests/classes/class.TestPreAuthController.php
 *
 * Copyright (c) 2011-2013 Mark Wilkie
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
 * Test PreAuthController
 *
 * Test auth controller to try the ThinkUpAuthController abstract class and Controller interface
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class TestPreAuthController extends ThinkUpAuthController {
    public function authControl() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'We are not preauthed!');
        return $this->generateView();
    }

    protected function preAuthControl() {
        if (isset($_GET['preauth'])) {
            $this->addToView('test', 'We are preauthed!');
        } else {
            return false;
        }
    }
}