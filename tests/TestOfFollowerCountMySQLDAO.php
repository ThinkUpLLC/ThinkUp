<?php
/**
 *
 * ThinkUp/tests/TestOfFollowerCountMySQLDAO.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfFollowerCountMySQLDAO extends ThinkUpUnitTestCase {

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

    public function testGetDayHistoryNoGapsMilestoneNotInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        if ($todays_day_of_the_week == 0 ) {
            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d', 'count'=>14);
            $builder1 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d', 'count'=>10);
            $builder2 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d', 'count'=>12);
            $builder3 = FixtureBuilder::build('follower_count', $follower_count);
        } else {
            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d', 'count'=>14);
            $builder1 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d', 'count'=>10);
            $builder2 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d', 'count'=>12);
            $builder3 = FixtureBuilder::build('follower_count', $follower_count);
        }

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 3);
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));
        //check history
        $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');
        $format = 'm/d/Y';
        if ($todays_day_of_the_week == 0 ) {
            $date_ago = date ($format, strtotime('-4 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 12);

            $date_ago = date ($format, strtotime('-3 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 10);

            $date_ago = date ($format, strtotime('-2 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 14);
        } else  {
            $date_ago = date ($format, strtotime('-3 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 12);

            $date_ago = date ($format, strtotime('-2 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 10);

            $date_ago = date ($format, strtotime('-1 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 14);
        }

        //check trend
        $this->assertEqual($result['trend'], 1);

        //check milestone
        //latest follower count is 14, next milestone is 100 followers
        //with a 1+/day trend, this should take 86 days
        //that's over the "don't feel bad about yourself" threshold of 10, so milestone should be null
        $this->assertNull($result['milestone']);

        $this->assertNotNull($result['vis_data']);
    }

    public function testGetDayHistoryFromSpecificStartDate() {
        $builders = array();
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-40d', 'count'=>90);
        $builders[] = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-41d', 'count'=>70);
        $builders[] = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-42d', 'count'=>50);
        $builders[] = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-43d', 'count'=>30);
        $builders[] = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-44d', 'count'=>10);
        $builders[] = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $date_ago = date ($format, strtotime('-40 day'.$date));
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 5, $date_ago);
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));
        //check history
        $this->assertEqual(sizeof($result['history']), 5);

        $format = 'm/d/Y';
        if ($todays_day_of_the_week == 0 ) {
            $date_ago = date ($format, strtotime('-40 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 90);

            $date_ago = date ($format, strtotime('-41 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 70);

            $date_ago = date ($format, strtotime('-42 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 50);

            $date_ago = date ($format, strtotime('-43 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 30);
        } else  {
            $date_ago = date ($format, strtotime('-41 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 70);

            $date_ago = date ($format, strtotime('-42 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 50);

            $date_ago = date ($format, strtotime('-43 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 30);
        }

        //check trend
        $this->assertEqual($result['trend'], 16);

        //check milestone
        //latest follower count is 90, next milestone is 100 followers
        //with a 20+/day trend, this should take 1 day
        $this->debug(Utils::varDumpToString($result['milestone']));
        $this->assertEqual($result['milestone']["next_milestone"], 100);
        $this->assertEqual($result['milestone']["will_take"], 1);

        $this->assertNotNull($result['vis_data']);
    }

    public function testGetDayHistoryNoGapsMilestoneInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        if ($todays_day_of_the_week == 0 ) {
            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>940);
            $builder1 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>900);
            $builder2 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>920);
            $builder3 = FixtureBuilder::build('follower_count', $follower_count);
        } else {
            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>940);
            $builder1 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>900);
            $builder2 = FixtureBuilder::build('follower_count', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>920);
            $builder3 = FixtureBuilder::build('follower_count', $follower_count);
        }

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 3);
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));
        //check history
        $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');
        $format = 'm/d/Y';
        if ($todays_day_of_the_week == 0 ) {
            $date_ago = date ($format, strtotime('-4 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 920);

            $date_ago = date ($format, strtotime('-3 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 900);

            $date_ago = date ($format, strtotime('-2 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 940);
        } else  {
            $date_ago = date ($format, strtotime('-3 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 920);

            $date_ago = date ($format, strtotime('-2 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 900);

            $date_ago = date ($format, strtotime('-1 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 940);
        }

        //check trend
        $this->assertEqual($result['trend'], 7);

        //check milestone
        //latest follower count is 940, next milestone is 1,000 followers
        //with a 7+/day trend, this should take 9 days
        $this->assertEqual($result['milestone']['next_milestone'], 1000);
        $this->assertEqual($result['milestone']['will_take'], 9);
        $this->assertEqual($result['milestone']['units_of_time'], 'DAY');
    }

    public function testGetDayHistoryWeekNoGaps() {
        $format = 'm/j';
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
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently ".$todays_day_of_the_week." day of the week. You can test all days except ".
        "Sunday");
        if ($todays_day_of_the_week != 0) {
            //check history
            $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');
        }

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

        //check trend
        //$this->assertEqual($result['trend'], 3);

        //check milestone
        if ($todays_day_of_the_week != 0) {
            $this->assertEqual($result['milestone']['will_take'], 20);
        }
    }

    public function testGetDayHistoryWithGaps() {
        // Filling gaps was only required by the old visualization library
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
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        //check history
        $this->assertEqual(sizeof($result['history']), 3);
        $format = 'm/d/Y';
        $this->debug(Utils::varDumpToString($result));
        $date_ago = date ($format, strtotime('-5 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 120);

        $date_ago = date ($format, strtotime('-4 day'.$date));
        $this->assertTrue(!isset($result['history'][$date_ago]), 'gap filled');

        $date_ago = date ($format, strtotime('-3 day'.$date));
        $this->assertTrue(!isset($result['history'][$date_ago]), 'gap filled');

        $date_ago = date ($format, strtotime('-2 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 100);

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

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
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
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
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
    }
}
