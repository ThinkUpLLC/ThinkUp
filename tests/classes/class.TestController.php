<?php
/**
 *
 * ThinkUp/tests/classes/class.TestController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Test Controller
 * Test controller to try the ThinkUpController abstract class and Controller interface
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestController extends ThinkUpController {

    public function control() {
        if (isset($_GET['json'])) {
            $this->setJsonData( array( 'aname' => 'a value', 'alist' => array('apple', 'pear', 'banana'), 'alink' =>
                    'http://thinkupapp.com'));
        } else if (isset($_GET['text'])) {
            $this->setContentType('text/plain');
        } else if (isset($_GET['png'])) {
            $this->setContentType('image/png');
        } else if (isset($_GET['css'])) {
            $this->addHeaderCSS('assets/css/bla.css');
            $this->setViewTemplate('session.login.tpl');
        }
        if (isset($_GET['throwexception'])) {
            throw new Exception("Testing exception handling!");
        } else if (!isset($_GET['json']) && ! isset($_GET['css'])) {
            $this->setViewTemplate('testme.tpl');
            $this->addToView('test', 'Testing, testing, 123');
        }
        return $this->generateView();
    }
}
