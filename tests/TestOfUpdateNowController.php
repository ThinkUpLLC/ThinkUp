<?php
/**
 *
 * ThinkUp/tests/TestOfUpdateNowController.php
 *
 * Copyright (c) 2009-2011 Mark Wilkie
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
 * Test of RSSController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUpdateNowController extends ThinkUpUnitTestCase {
    public function testLoadProperRSSUrl() {
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com', true, true);
        $controller = new UpdateNowController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->control();
        $this->assertPattern('/rss.php\?un=me@example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c/', $result);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'me@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'
            ));
            return array($owner_builder);
    }
}
