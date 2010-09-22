<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterOAuth.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
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
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
*/
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
if (!class_exists('twitterOAuth')) {
    require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
}

class TestOfTwitterOAuth extends UnitTestCase {
    function TestOfTwitterOAuth() {
        $this->UnitTestCase('Mock Twitter OAuth test');
    }

    function testMakingAPICall() {
        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/users/show/anildash.xml', 'GET', array());
        $this->assertWantedPattern('/Anil Dash/', $result);

    }
}
?>
