<?php
/**
 *
 * ThinkUp/tests/TestOfCountHistoryMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfCountHistoryMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $dao = new CountHistoryMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsert() {
        $dao = new CountHistoryMySQLDAO();
        $result = $dao->insert(930061, 'twitter', 1001, null, 'followers');

        $this->assertEqual($result, 1, 'One count inserted');
    }

    public function testFollowerCountGetDayHistoryNoGapsMilestoneNotInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');

        if ($todays_day_of_the_week == 0 ) {
            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d', 'count'=>14,
            'post_id'=>null, 'type'=>'followers');
            $builder1 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d', 'count'=>10,
            'post_id'=>null, 'type'=>'followers');
            $builder2 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d', 'count'=>12,
            'post_id'=>null, 'type'=>'followers');
            $builder3 = FixtureBuilder::build('count_history', $follower_count);
        } else {
            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d', 'count'=>14,
            'post_id'=>null, 'type'=>'followers');
            $builder1 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d', 'count'=>10,
            'post_id'=>null, 'type'=>'followers');
            $builder2 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d', 'count'=>12,
            'post_id'=>null, 'type'=>'followers');
            $builder3 = FixtureBuilder::build('count_history', $follower_count);
        }

        $dao = new CountHistoryMySQLDAO();
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
        $vis_data = $result['vis_data'];
        $vis_data = preg_replace("/(new Date[^)]+\))/", '"$1"', $vis_data);
        $vis_data = json_decode($vis_data);
        $this->assertEqual(3, count($vis_data->rows));
    }

    public function testFollowerCountGetDayHistoryFromSpecificStartDate() {
        $builders = array();
        $format = 'Y-m-d';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-40d', 'count'=>90,
        'post_id'=>null, 'type'=>'followers');
        $builders[] = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-41d', 'count'=>70,
        'post_id'=>null, 'type'=>'followers');
        $builders[] = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-42d', 'count'=>50,
        'post_id'=>null, 'type'=>'followers');
        $builders[] = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-43d', 'count'=>30,
        'post_id'=>null, 'type'=>'followers');
        $builders[] = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-44d', 'count'=>10,
        'post_id'=>null, 'type'=>'followers');
        $builders[] = FixtureBuilder::build('count_history', $follower_count);

        $dao = new CountHistoryMySQLDAO();
        $date_ago = date ($format, strtotime('-40 day'.$date));
        $this->debug("Getting history starting on ".$date_ago);
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
        //with a 16+/day trend, this should take 1 day
        $this->debug(Utils::varDumpToString($result['milestone']));
        $this->assertEqual($result['milestone']["next_milestone"], 100);
        $this->assertEqual($result['milestone']["will_take"], 1);

        $this->assertNotNull($result['vis_data']);
    }

    public function testFollowerCountGetDayHistoryNoGapsMilestoneInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        if ($todays_day_of_the_week == 0 ) {
            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>940,
            'post_id'=>null, 'type'=>'followers');
            $builder1 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>900,
            'post_id'=>null, 'type'=>'followers');
            $builder2 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>920,
            'post_id'=>null, 'type'=>'followers');
            $builder3 = FixtureBuilder::build('count_history', $follower_count);
        } else {
            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>940,
            'post_id'=>null, 'type'=>'followers');
            $builder1 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>900,
            'post_id'=>null, 'type'=>'followers');
            $builder2 = FixtureBuilder::build('count_history', $follower_count);

            $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>920,
            'post_id'=>null, 'type'=>'followers');
            $builder3 = FixtureBuilder::build('count_history', $follower_count);
        }

        $dao = new CountHistoryMySQLDAO();
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

    public function testFollowerCountGetDayHistoryWeekNoGaps() {
        $format = 'm/j';
        $date = date ( $format );

        //how many days ago was Saturday?  Sun is day 0, Saturday is day 6
        $days_ago = date('w') + 1;
        $this->debug($days_ago." days ago it was Saturday");

        $builders = array();

        //If yesterday wasn't Saturday, fill in the gap from yesterday to last Saturday
        if ($days_ago > 1) {
            $gap = 1;
            while ($gap < $days_ago) {
                $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-'.$gap.'d',
                'count'=>145, 'type'=>'followers', 'post_id'=>null);
                $builders[] = FixtureBuilder::build('count_history', $follower_count);
                $gap++;
            }
        }
        //Starting at last Saturday, fill in 14 days of follower counts
        $day_counter = 0;
        while ($day_counter < 14) {
            $follower_count = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-'.$days_ago.'d',
            'count'=>(140-$day_counter), 'type'=>'followers','post_id'=>null );
            $builders[] = FixtureBuilder::build('count_history', $follower_count);
            $day_counter++;
            $days_ago++;
        }

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'WEEK', 3, null, 'followers');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        if (date('w')  != 1 && date('w')  != 0) { //Don't test on Sunday or Monday
            $this->assertEqual(sizeof($result['history']), 3);

            // Yesterday count was 145
            $date_ago = date ($format, strtotime('-1 day'.$date));
            $this->assertEqual($result['history'][$date_ago], 145);

            // Last Saturday count was 140
            $days_ago_till_saturday = date('w') + 1;
            $last_saturday= date ($format, strtotime('-'.$days_ago_till_saturday.' day'.$date));
            $this->debug('Last Saturday was '.$last_saturday);
            $this->assertEqual($result['history'][$last_saturday], 140);

            // Saturday before last was 133
            $days_ago_till_saturday = date('w') + 8;
            $last_saturday= date ($format, strtotime('-'.$days_ago_till_saturday.' day'.$date));
            $this->debug('Saturday before last was '.$last_saturday);
            $this->assertEqual($result['history'][$last_saturday], 133);

            // Trend is (145 - 133) / 3 = 4
            $this->assertEqual($result['trend'], 4);

            // latest follower count is 145, next milestone is 200 followers
            // with a 4+/day trend, this should take 13.75 days, rounded to 14
            $this->assertEqual($result['milestone']['will_take'], 14);
        }
    }

    public function testFollowerCountGetDayHistoryWithGaps() {
        // Filling gaps was only required by the old visualization library
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>140,
        'post_id'=>null, 'type'=>'followers');
        $builder1 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>100,
        'post_id'=>null, 'type'=>'followers');
        $builder2 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-5d', 'count'=>120,
        'post_id'=>null, 'type'=>'followers');
        $builder3 = FixtureBuilder::build('count_history', $follower_count);

        $dao = new CountHistoryMySQLDAO();
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

    public function testFollowerCountTrendMillionPlusFollowers() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>1772643,
        'post_id'=>null, 'type'=>'followers');
        $builder1 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>1771684,
        'post_id'=>null, 'type'=>'followers');
        $builder2 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>1771500,
        'post_id'=>null, 'type'=>'followers');
        $builder3 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>1761500,
        'post_id'=>null, 'type'=>'followers');
        $builder4 = FixtureBuilder::build('count_history', $follower_count);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 4);
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
    }

    public function testFollowerCountTrendMillionPlusFollowers2() {
        $format = 'n/j';
        $date = date ( $format );

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>1272643,
        'post_id'=>null, 'type'=>'followers');
        $builder1 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>1271684,
        'post_id'=>null, 'type'=>'followers');
        $builder2 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>1271500,
        'post_id'=>null, 'type'=>'followers');
        $builder3 = FixtureBuilder::build('count_history', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-4d', 'count'=>1261500,
        'post_id'=>null, 'type'=>'followers');
        $builder4 = FixtureBuilder::build('count_history', $follower_count);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY', 4);
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, and milestone, and vis_data');

        $this->debug(Utils::varDumpToString($result));

        //check milestone
        //latest follower count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
    }

    public function testGetCountsByPostID() {
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:52:01', 'count'=>125, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-22 11:52:01', 'count'=>45, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-22 11:52:01', 'count'=>12, 'post_id'=>'Hbfgh48', 'type'=>'likes'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-30 11:52:01', 'count'=>12, 'post_id'=>'Jhgndf7', 'type'=>'likes'));

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getCountsByPostID('Hbfgh48');
        // Check we only got 3 results back for this video as the 4th is for a different video
        $this->assertEqual(sizeof($result), 3);
        // Check we got the correct 3 results back
        $this->assertEqual($result[0]['network_user_id'], 930061);
        $this->assertEqual($result[0]['network'], 'youtube');
        $this->assertEqual($result[0]['date'], '2013-04-21');
        $this->assertEqual($result[0]['count'], 125);
        $this->assertEqual($result[0]['post_id'], 'Hbfgh48');
        $this->assertEqual($result[0]['type'], 'views');

        $this->assertEqual($result[1]['network_user_id'], 930061);
        $this->assertEqual($result[1]['network'], 'youtube');
        $this->assertEqual($result[1]['date'], '2013-04-22');
        $this->assertEqual($result[1]['count'], 45);
        $this->assertEqual($result[1]['post_id'], 'Hbfgh48');
        $this->assertEqual($result[1]['type'], 'views');

        $this->assertEqual($result[2]['network_user_id'], 930061);
        $this->assertEqual($result[2]['network'], 'youtube');
        $this->assertEqual($result[2]['date'], '2013-04-22');
        $this->assertEqual($result[2]['count'], 12);
        $this->assertEqual($result[2]['post_id'], 'Hbfgh48');
        $this->assertEqual($result[2]['type'], 'likes');
    }

    public function testGetCountsByPostIDAndType() {
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:52:01', 'count'=>125, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-22 11:52:01', 'count'=>45, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-22 11:52:01', 'count'=>12, 'post_id'=>'Hbfgh48', 'type'=>'likes'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-30 11:52:01', 'count'=>12, 'post_id'=>'Jhgndf7', 'type'=>'likes'));

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getCountsByPostIDAndType('Hbfgh48', 'likes');
        // Check we only got 1 result back for this video
        $this->assertEqual(sizeof($result), 1);
        // Check we got the correct result back
        $this->assertEqual($result[0]['network_user_id'], 930061);
        $this->assertEqual($result[0]['network'], 'youtube');
        $this->assertEqual($result[0]['date'], '2013-04-22');
        $this->assertEqual($result[0]['count'], 12);
        $this->assertEqual($result[0]['post_id'], 'Hbfgh48');
        $this->assertEqual($result[0]['type'], 'likes');
    }

    public function testSumCountsOverTimePeriod() {
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:52:01', 'count'=>125, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-30 11:52:01', 'count'=>45, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-14 11:52:01', 'count'=>12, 'post_id'=>'Hbfgh48', 'type'=>'likes'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-30 11:52:01', 'count'=>12, 'post_id'=>'Jhgndf7', 'type'=>'likes'));

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->sumCountsOverTimePeriod('Hbfgh48', 'views', '2013-04-14', '2013-04-31');
        $this->assertEqual($result, 170);
    }

    public function testGetLatestCountByPostIDAndType() {
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:52:01', 'count'=>125, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-18 11:52:01', 'count'=>45, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-10 11:52:01', 'count'=>25, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-02 11:52:01', 'count'=>10, 'post_id'=>'Jhgndf7', 'type'=>'views'));

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getLatestCountByPostIDAndType('Hbfgh48', 'views');
        // Check we only got the latest result back
        $this->assertEqual($result[1], null);
        // Check we got the correct result back
        $this->assertEqual($result['network_user_id'], 930061);
        $this->assertEqual($result['network'], 'youtube');
        $this->assertEqual($result['date'], '2013-04-21');
        $this->assertEqual($result['count'], 125);
        $this->assertEqual($result['post_id'], 'Hbfgh48');
        $this->assertEqual($result['type'], 'views');
    }

    public function testUpdateGroupMembershipCount() {
        $group_member_dao = new GroupMemberMySQLDAO();
        $group_member_dao->insert('1234', '55555555', 'twitter');
        $group_member_dao->insert('1234', '66666666', 'twitter');

        $count_history_dao = new CountHistoryMySQLDAO();
        $result = $count_history_dao->updateGroupMembershipCount('1234', 'twitter');
        $this->assertEqual($result, 1, 'One count inserted');
        $sql = 'SELECT count FROM ' . $this->table_prefix . 'count_history WHERE ';
        $sql .= 'network_user_id = :network_user_id AND network = :network ';
        $sql .= 'ORDER BY `date` DESC LIMIT 1';

        $stmt = CountHistoryMySQLDAO::$PDO->prepare($sql);
        $stmt->execute(array(':network_user_id' => '1234', ':network' => 'twitter'));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['count'], 2, 'Current group membership count is 2');
    }

    public function testGroupMembershipGetDayHistoryNoGapsMilestoneNotInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        if ($todays_day_of_the_week == 0 ) {
            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
            'type'=>'group_memberships', 'count'=>14);
            $builder1 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
            'type'=>'group_memberships', 'count'=>10);
            $builder2 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d',
            'type'=>'group_memberships', 'count'=>12);
            $builder3 = FixtureBuilder::build('count_history', $count_history);
        } else {
            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
            'type'=>'group_memberships', 'count'=>14);
            $builder1 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
            'type'=>'group_memberships', 'count'=>10);
            $builder2 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
            'type'=>'group_memberships', 'count'=>12);
            $builder3 = FixtureBuilder::build('count_history', $count_history);
        }

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 3, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

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
            $this->debug($result);
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
        //latest group membership count is 14, next milestone is 100 group memberships
        //with a 1+/day trend, this should take 84 days
        //that's over the "don't feel bad about yourself" threshold of 10, so milestone should be null
        $this->assertNull($result['milestone']);

        $this->assertNotNull($result['vis_data']);
    }

    public function testGroupMembershipGetDayHistoryNoGapsMilestoneInSight() {
        $format = 'n/j';
        $date = date ( $format );

        $todays_day_of_the_week = date('w');
        $this->debug("It's currently the ".$todays_day_of_the_week." day of the week");
        if ($todays_day_of_the_week == 0 ) {
            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
            'type'=>'group_memberships', 'count'=>940);
            $builder1 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
            'type'=>'group_memberships', 'count'=>900);
            $builder2 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d',
            'type'=>'group_memberships', 'count'=>920);
            $builder3 = FixtureBuilder::build('count_history', $count_history);
        } else {
            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
            'type'=>'group_memberships', 'count'=>940);
            $builder1 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
            'type'=>'group_memberships', 'count'=>900);
            $builder2 = FixtureBuilder::build('count_history', $count_history);

            $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
            'type'=>'group_memberships', 'count'=>920);
            $builder3 = FixtureBuilder::build('count_history', $count_history);
        }

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 3, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

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
        //latest group membership count is 940, next milestone is 1,000 group memberships
        //with a 7+/day trend, this should take 9 days
        $this->assertEqual($result['milestone']['next_milestone'], 1000);
        $this->assertEqual($result['milestone']['will_take'], 9);
        $this->assertEqual($result['milestone']['units_of_time'], 'DAY');
    }

    public function testGroupMembershipGetDayHistoryWeekNoGaps() {
        $format = 'm/j';
        $date = date ( $format );

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
        'type'=>'group_memberships', 'count'=>140);
        $builder1 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
        'type'=>'group_memberships', 'count'=>139);
        $builder2 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
        'type'=>'group_memberships', 'count'=>138);
        $builder3 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d',
        'type'=>'group_memberships', 'count'=>137);
        $builder4 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-5d',
        'type'=>'group_memberships', 'count'=>136);
        $builder5 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-6d',
        'type'=>'group_memberships', 'count'=>135);
        $builder6 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-7d',
        'type'=>'group_memberships','count'=>134);
        $builder7 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-8d',
        'type'=>'group_memberships', 'count'=>133);
        $builder8 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-9d',
        'type'=>'group_memberships', 'count'=>132);
        $builder9 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-10d',
        'type'=>'group_memberships', 'count'=>131);
        $builder10 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-11d',
        'type'=>'group_memberships', 'count'=>130);
        $builder11 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-12d',
        'type'=>'group_memberships', 'count'=>129);
        $builder12 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-13d',
        'type'=>'group_memberships', 'count'=>128);
        $builder13 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-14d',
        'type'=>'group_memberships', 'count'=>127);
        $builder14 = FixtureBuilder::build('count_history', $count_history);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'WEEK', 3, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

        $todays_day_of_the_week = date('w');

        if ($todays_day_of_the_week != 0) {
            //check history
            $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');
        }

        $date_ago = date ($format, strtotime('-1 day'.$date));
        $this->assertEqual($result['history'][$date_ago], 140);

        $this->debug(Utils::varDumpToString($result));
        //check milestone
        //latest group membership count is 140, next milestone is 1,000 group memberships
        //with a 7+/day trend, this should take 123 days (under 20 weeks)
        //within our "don't feel bad about yourself" threshold of 20, so should not be null
        if ($todays_day_of_the_week != 0) {
            $this->assertNotNull($result['milestone']);
        }
    }

    public function testGroupMembershipGetDayHistoryWithGaps() {
        // Filling gaps was only required by the old visualization library
        $format = 'n/j';
        $date = date ( $format );

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
        'type'=>'group_memberships', 'count'=>140);
        $builder1 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
        'type'=>'group_memberships', 'count'=>100);
        $builder2 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-5d',
        'type'=>'group_memberships', 'count'=>120);
        $builder3 = FixtureBuilder::build('count_history', $count_history);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 5, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

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

    public function testGroupMembershipGetDayHistoryWithGapsTrendMinimum() {
        // Filling gaps was only required by the old visualization library
        $format = 'n/j';
        $date = date ( $format );

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
        'type'=>'group_memberships', 'count'=>140);
        $builder1 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
        'type'=>'group_memberships', 'count'=>100);
        $builder2 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-5d',
        'type'=>'group_memberships', 'count'=>120);
        $builder3 = FixtureBuilder::build('count_history', $count_history);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 5, null, 'group_memberships', 3);
        $this->assertEqual($result['trend'], 7);
        $this->assertEqual($result['milestone']['will_take'], 9);
        $this->assertEqual($result['milestone']['next_milestone'], 200);
        $this->assertEqual(sizeof($result['history']), 3);

        $result = $dao->getHistory('930061', 'twitter', 'DAY', 5, null, 'group_memberships', 2);
        $this->assertEqual($result['trend'], 7);
        $this->assertEqual($result['milestone']['will_take'], 9);
        $this->assertEqual($result['milestone']['next_milestone'], 200);
        $this->assertEqual(sizeof($result['history']), 3);

        $result = $dao->getHistory('930061', 'twitter', 'DAY', 5, null, 'group_memberships', 5);
        $this->assertFalse($result['trend']);
        $this->assertFalse($result['milestone']);
        $this->assertEqual(sizeof($result['history']), 3);
        $vis_data = $result['vis_data'];
        $vis_data = preg_replace("/(new Date[^)]+\))/", '"$1"', $vis_data);
        $vis_data = json_decode($vis_data);
        $this->assertEqual(3, count($vis_data->rows));
    }


    public function testGroupMembershipTrendMillionPlusGroupMemberships() {
        $format = 'n/j';
        $date = date ( $format );

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
        'type'=>'group_memberships', 'count'=>1772643);
        $builder1 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
        'type'=>'group_memberships', 'count'=>1771684);
        $builder2 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
        'type'=>'group_memberships', 'count'=>1771500);
        $builder3 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d',
        'type'=>'group_memberships', 'count'=>1761500);
        $builder4 = FixtureBuilder::build('count_history', $count_history);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 4, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

        //check milestone
        //latest group membership count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
    }

    public function testGroupMembershipTrendMillionPlusGroupMemberships2() {
        $format = 'n/j';
        $date = date ( $format );

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-1d',
        'type'=>'group_memberships', 'count'=>1272643);
        $builder1 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-2d',
        'type'=>'group_memberships', 'count'=>1271684);
        $builder2 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-3d',
        'type'=>'group_memberships', 'count'=>1271500);
        $builder3 = FixtureBuilder::build('count_history', $count_history);

        $count_history = array('network_user_id'=>'930061', 'network'=>'twitter', 'date'=>'-4d',
        'type'=>'group_memberships', 'count'=>1261500);
        $builder4 = FixtureBuilder::build('count_history', $count_history);

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getHistory('930061', 'twitter', 'DAY', 4, null, 'group_memberships');
        $this->assertEqual(sizeof($result), 4, '4 sets of data returned--history, trend, milestone, and vis_data');

        //check milestone
        //latest group membership count is 1.7M, next milestone is 2M
        //with a 2786+/day trend, this should take 82 days
        //beyond our "don't feel bad about yourself" threshold of 10, so should be null
        $this->assertNull($result['milestone']);
    }

    public function testGetLatestCountByNetworkUserIDAndType() {
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:52:01', 'count'=>125, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:51:01', 'count'=>45, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:50:01', 'count'=>25, 'post_id'=>'Hbfgh48', 'type'=>'views'));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>930061, 'network'=>'youtube',
        'date'=>'2013-04-21 11:49:01', 'count'=>10, 'post_id'=>'Jhgndf7', 'type'=>'views'));

        $dao = new CountHistoryMySQLDAO();
        $result = $dao->getLatestCountByNetworkUserIDAndType(930061, 'youtube', 'views');
        // Check we got the correct result back
        $this->assertEqual($result['network_user_id'], 930061);
        $this->assertEqual($result['network'], 'youtube');
        $this->assertEqual($result['date'], '2013-04-21');
        $this->assertEqual($result['count'], 125);
        $this->assertEqual($result['post_id'], 'Hbfgh48');
        $this->assertEqual($result['type'], 'views');
    }
}
