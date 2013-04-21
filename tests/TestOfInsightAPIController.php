<?php

/**
 *
 * ThinkUp/tests/TestOfPostInsightAPIController.php
 *
 * Copyright (c) 2009-2013 Nilaksh Das
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
 * Test of InsightAPIController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Nilaksh Das <nilakshdas@gmail.com>
 */
require_once dirname(__FILE__) . '/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsightAPIController extends ThinkUpUnitTestCase {
	
	public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected static function buildData() {
    	$builders = array();

    	$hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

    	// Add owner
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'pwd'=>$hashed_pass,
        'pwd_salt'=> OwnerMySQLDAO::$default_salt, 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        // Add instance
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

    	// Add insights
    	$builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>date("Y-m-d H:i:s")));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>date("Y-m-d H:i:s")));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-03', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>date("Y-m-d H:i:s")));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-04', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>date("Y-m-d H:i:s")));
    }

    public function testInsight() {
        $_GET['api_key'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        $controller = new InsightAPIController(true);
        $output = json_decode($controller->go());

        // Test correct number of insights were retrieved
        $this->assertEqual(count($output), 4);
    }

    public function testAPIDisabled() {
        $_GET['api_key'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        // test default option
        $controller = new InsightAPIController(true);
        $output = json_decode($controller->go());

        $this->assertFalse(isset($output->error));

        // test option true
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'true');

        $controller = new InsightAPIController(true);
        $output = json_decode($controller->go());

        $this->assertEqual($output->error->type, 'APIDisabledException');

        // test option false
        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'false');
        
        $controller = new InsightAPIController(true);
        $output = json_decode($controller->go());

        $this->assertFalse(isset($output->error));
    }

    public function testAPIOAuth() {
        // test missing api_key
        $controller = new InsightAPIController(true);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, 'APIOAuthException');

        // test incorrect api_key
        $_GET['api_key'] = 'abcd';

        $controller = new InsightAPIController(true);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, 'APIOAuthException');

        // test correct api_key
        $_GET['api_key'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        $controller = new InsightAPIController(true);
        $output = json_decode($output);

        $this->assertFalse(isset($output->error));
    }

    public function testInsightNotFound() {
        $_GET['api_key'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $_GET['until'] = strtotime('2012-04-01');

        $controller = new InsightAPIController(true);
        $output = json_decode($controller->go());

        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "InsightNotFoundException");
    }
}