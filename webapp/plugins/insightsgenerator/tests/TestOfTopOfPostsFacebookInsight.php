<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfTopOfPostsFacebookInsight.php
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
 * TopOfPostsFacebook (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2013 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Anna Shkerina
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/topofpostsfacebook.php';

class TestOfTopOfPostsFacebookInsight extends ThinkUpUnitTestCase {

	public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $topofposts_insight_plugin = new TopOfPostsFacebookInsight();
        $this->assertIsA($topofposts_insight_plugin, 'TopOfPostsFacebookInsight' );
    }
}

