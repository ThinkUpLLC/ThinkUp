<?php
/**
 *
 * ThinkUp/tests/TestOfUpdateNowController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * Test of RSSController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUpdateNowController extends ThinkUpUnitTestCase {

    public function testConstructor() {
        $controller = new UpdateNowController(true);
        $this->assertIsA($controller, 'UpdateNowController');
    }

    public function testHint() {
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com', true, true);

        $controller = new UpdateNowController(true);
        $result = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('info_msg'),
        "<b>Hint</b>: You can set up ThinkUp to capture your data automatically. Visit Settings &rarr; ".
        "Account to find out how.");
    }

    private function buildData() {
        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'me@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'
            ));
            return $builders;
    }

}
