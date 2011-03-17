<?php
/**
 *
 * ThinkUp/tests/TestOfFollowerCountMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfFollowerCountMySQLDAO extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('FollowerCountMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $dao = new FollowerCountMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsert() {
        $dao = new FollowerCountMySQLDAO();
        $result = $dao->insert(930061, 'twitter', 1001);

        $this->assertEqual($result, 1, 'One count inserted');
    }

    public function testGetDayHistoryNoGaps() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>140);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>100);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>120);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 3);
        $this->assertEqual(sizeof($result), 5, '5 sets of data returned--history, percentages, Y axis, trend, '.
        'milestone');

        $this->debug(Utils::varDumpToString($result));
        //check history
        $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');

        $date_ago = date ($format, strtotime('-3 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 120);

        $date_ago = date ($format, strtotime('-2 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 100);

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

        //check percentages
        $this->assertEqual(sizeof($result['percentages']), 3, '3 percentages returned');
        $this->assertEqual($result['percentages'][0], 50);
        $this->assertEqual($result['percentages'][1], 0);
        $this->assertEqual($result['percentages'][2], 100);

        //check Y-axis
        $this->assertEqual(sizeof($result['y_axis']), 5, '5 Y axis points returned');
        $this->assertEqual($result['y_axis'][0], 100);
        $this->assertEqual($result['y_axis'][1], 110);
        $this->assertEqual($result['y_axis'][2], 120);
        $this->assertEqual($result['y_axis'][3], 130);
        $this->assertEqual($result['y_axis'][4], 140);

        //check trend
        $this->assertEqual($result['trend'], 7);

        //check milestone
        //latest follower count is 140, next milestone is 1,000 followers
        //with a 7+/day trend, this should take 123 days
        $this->assertEqual($result['milestone']['next_milestone'], 1000);
        $this->assertEqual($result['milestone']['will_take'], 123);
        $this->assertEqual($result['milestone']['units_of_time'], 'DAY');
    }

    public function testGetDayHistoryWeekNoGaps() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>140);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>139);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>138);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>137);
        $builder4 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-5d', 'count'=>136);
        $builder5 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-6d', 'count'=>135);
        $builder6 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-7d', 'count'=>134);
        $builder7 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-8d', 'count'=>133);
        $builder8 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-9d', 'count'=>132);
        $builder9 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-10d', 'count'=>131);
        $builder10 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-11d', 'count'=>130);
        $builder11 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-12d', 'count'=>129);
        $builder12 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-13d', 'count'=>128);
        $builder13 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-14d', 'count'=>127);
        $builder14 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'WEEK', 3);
        $this->assertEqual(sizeof($result), 5, '5 sets of data returned--history, percentages, Y axis, trend, '.
        'milestone');

        $this->debug(Utils::varDumpToString($result));
        //check history
        $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

        //check percentages
        $this->assertEqual(sizeof($result['percentages']), 3, '3 percentages returned');
        //Difficult to test because the values change depending on what day of the week you're running the tests
        //        $this->assertEqual($result['percentages'][0], 0);
        //        $this->assertEqual($result['percentages'][1], 78);
        //        $this->assertEqual($result['percentages'][2], 100);

        //check Y-axis
        $this->assertEqual(sizeof($result['y_axis']), 5, '5 Y axis points returned');
        //Difficult to test because the values change depending on what day of the week you're running the tests
        //        $this->assertEqual($result['y_axis'][0], 131);
        //        $this->assertEqual($result['y_axis'][1], 133.25);
        //        $this->assertEqual($result['y_axis'][2], 135.5);
        //        $this->assertEqual($result['y_axis'][3], 137.75);
        //        $this->assertEqual($result['y_axis'][4], 140);

        //check trend
        //$this->assertEqual($result['trend'], 3);

        //check milestone
        //latest follower count is 140, next milestone is 1,000 followers
        //with a 7+/day trend, this should take 123 days
        $this->assertEqual($result['milestone']['next_milestone'], 1000);
        //$this->assertEqual($result['milestone']['will_take'], 287);
        $this->assertEqual($result['milestone']['units_of_time'], 'WEEK');
    }

    public function testGetDayHistoryWithGaps() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>140);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>100);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-5d', 'count'=>120);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 5);
        $this->assertEqual(sizeof($result), 5, '5 sets of data returned--history, percentages, Y axis, trend, '.
        'milestone');

        //check history
        $this->assertEqual(sizeof($result['history']), 5, '5 counts returned');

        $this->debug(Utils::varDumpToString($result));
        $date_ago = date ($format, strtotime('-5 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 120);

        $date_ago = date ($format, strtotime('-4 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 'no data');

        $date_ago = date ($format, strtotime('-3 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 'no data');

        $date_ago = date ($format, strtotime('-2 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 100);

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

        //check percentages
        $this->assertEqual(sizeof($result['percentages']), 5, '5 percentages returned');
        $this->assertEqual($result['percentages'][0], 50);
        $this->assertEqual($result['percentages'][1], 0);
        $this->assertEqual($result['percentages'][2], 0);
        $this->assertEqual($result['percentages'][3], 0);
        $this->assertEqual($result['percentages'][4], 100);

        //check y-axis
        $this->assertEqual(sizeof($result['y_axis']), 5, '5 Y axis points returned');

        $this->assertEqual($result['y_axis'][0], 100);
        $this->assertEqual($result['y_axis'][1], 110);
        $this->assertEqual($result['y_axis'][2], 120);
        $this->assertEqual($result['y_axis'][3], 130);
        $this->assertEqual($result['y_axis'][4], 140);

        //check trend
        $this->assertFalse($result['trend']);

        //check milestone
        $this->assertFalse($result['milestone']);
    }

    public function testTrendMillionPlusFollowers() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>1772643);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>1771684);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>1771500);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>1761500);
        $builder4 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 4);
        $this->assertEqual(sizeof($result), 5, '5 sets of data returned--history, percentages, Y axis, trend, '.
        'milestone');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        $this->assertEqual($result['milestone']['next_milestone'], 2000000);
        $this->assertEqual($result['milestone']['will_take'], 82);
        $this->assertEqual($result['milestone']['units_of_time'], 'DAY');
    }

    public function testTrendMillionPlusFollowers2() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>1272643);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>1271684);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>1271500);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>1261500);
        $builder4 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 4);
        $this->assertEqual(sizeof($result), 5, '5 sets of data returned--history, percentages, Y axis, trend, '.
        'milestone');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        $this->assertEqual($result['milestone']['next_milestone'], 1500000);
        $this->assertEqual($result['milestone']['will_take'], 82);
        $this->assertEqual($result['milestone']['units_of_time'], 'DAY');
    }
}