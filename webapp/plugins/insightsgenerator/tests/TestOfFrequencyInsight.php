<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFrequencyInsight.php
 *
 * Copyright (c) 2013-2016 Gina Trapani, Anil Dash, Chris Moyer
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
 * Test of FrequencyInsight
 *
 * Test for the FrequencyInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2016 Gina Trapani, Anil Dash, Chris Moyer
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Anil Dash <anil[at]thinkup[dot]com>
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/frequency.php';

class TestOfFrequencyInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFrequencyInsightNoPostsThisWeekTwitter() {
        $insight_dao = new InsightMySQLDAO();

        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        foreach (array(1, 2, 3) as $modded_time) {
            TimeHelper::setTime($modded_time);
            $insight_plugin->generateInsight($instance, $user, $posts, 3);
            // Assert that insight got inserted
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight('frequency', 10, $today);
            //$this->debug(Utils::varDumpToString($result));
            $this->assertNotNull($result);
            $this->assertIsA($result, "Insight");
            $this->assertNotNull($result->time_generated);

            if ($modded_time == 1) {
                $this->assertEqual('@testeriffic didn\'t post anything new on Twitter in the past week',
                $result->headline);
                $this->assertEqual('Sometimes we just don\'t have anything to say. Maybe let someone know you'
                                    . ' appreciate their work?', $result->text);
            } elseif ($modded_time == 2) {
                $this->assertEqual('Seems like @testeriffic was pretty quiet on Twitter this past week',
                $result->headline);
                $this->assertEqual('Nothing wrong with waiting until there\'s something to say.',
                $result->text);
            } else {
                $this->assertEqual('@testeriffic didn\'t have any new tweets this week',
                $result->headline);
                $this->assertEqual('Nothing wrong with waiting until there\'s something to say.',
                $result->text);
            }
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testFrequencyInsightNoPostsThisWeekFacebook() {
        $insight_dao = new InsightMySQLDAO();
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_username = 'Silent Bob';
        $instance->network = 'facebook';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        foreach (array(1, 2, 3) as $modded_time) {
            TimeHelper::setTime($modded_time);
            $insight_plugin->generateInsight($instance, $user, $posts, 3);
            // Assert that insight got inserted
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight('frequency', 1, $today);
            //$this->debug(Utils::varDumpToString($result));
            $this->assertNotNull($result);
            $this->assertIsA($result, "Insight");
            $this->assertNotNull($result->time_generated);

            if ($modded_time == 1) {
                $this->assertEqual('Silent Bob didn\'t post anything new on Facebook in the past week',
                $result->headline);
                $this->assertEqual('Nothing wrong with being quiet. If you want, you could ask your friends what ' .
                    'they\'ve read lately.', $result->text);
            } elseif ($modded_time == 2) {
                $this->assertEqual('Seems like Silent Bob was pretty quiet on Facebook this past week',
                $result->headline);
                $this->assertEqual('Nothing wrong with waiting until there\'s something to say.',
                $result->text);
                $data = unserialize($result->related_data);
                $this->assertEqual($data['button']['label'], 'Or just say hi to your friends?');
            } else {
                $this->assertEqual('Silent Bob didn\'t have any new status updates this week',
                $result->headline);
                $this->assertEqual('Nothing wrong with waiting until there\'s something to say.',
                $result->text);
            }
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testFrequencyInsightNoPostsThisWeekInstagram() {
        $insight_dao = new InsightMySQLDAO();
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_username = 'SilentBob';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        // Assert that insight got inserted
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 1, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertNotNull($result->time_generated);

        $this->assertEqual('@SilentBob didn\'t have any new tweets this week',
        $result->headline);
        $this->assertEqual('Nothing wrong with waiting until there\'s something to say.',
            $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFrequencyInsightExactly1PostTwitter() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $posts = array_slice( $posts, 0, 1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
            'instance_id'=>10, 'value'=>19));

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 10, $today);
        $this->assertNotNull($result);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFrequencyInsightExactly1PostInstagram() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects('instagram');
        $posts = array_slice( $posts, 0, 1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';
        $insight_plugin = new FrequencyInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builders = array();
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
            'instance_id'=>10, 'value'=>19));
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week,
            'slug'=>'frequency_photo_count', 'instance_id'=>10, 'value'=>9));
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week,
            'slug'=>'frequency_video_count', 'instance_id'=>10, 'value'=>6));
        foreach ($posts as $post) {
            $builders[] = FixtureBuilder::build('photos', array('post_key'=>$post->id, 'is_short_video'=>1));
        }

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 10, $today);
        $this->assertNotNull($result);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFrequencyInsightInstagram() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects('instagram');
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';
        $insight_plugin = new FrequencyInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builders = array();
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
            'instance_id'=>10, 'value'=>19));
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week,
            'slug'=>'frequency_photo_count', 'instance_id'=>10, 'value'=>9));
        $builders[] = FixtureBuilder::build('insight_baselines', array('date'=>$last_week,
            'slug'=>'frequency_video_count', 'instance_id'=>10, 'value'=>6));

        foreach ($posts as $post) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post->id, 'post_id'=>$post->post_id,
                'network'=>'instagram'));
            $builders[] = FixtureBuilder::build('photos', array('post_key'=>$post->id, 'post_id'=>$post->post_id,
                'network'=>'instagram', 'is_short_video'=>(($post->id % 2) == 1)));
        }

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 10, $today);
        $this->assertNotNull($result);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFrequencyInsightNoPriorBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();

        // Assert that insight didn't get inserted without a prior baseline
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 10, $today);
        $this->assertNull($result);
    }

    public function testFrequencyInsightNoPriorBaselineFacebook() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 2;
        $instance->network_username = 'Test User';
        $instance->network = 'facebook';
        $insight_plugin = new FrequencyInsight();

        // Assert that insight didn't get inserted without a prior baseline
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 2, $today);
        $this->assertNull($result);
    }

    public function testFrequencyInsightPriorGreaterBy2Baseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
        'instance_id'=>10, 'value'=>19));

        foreach (array(1, 2, 3) as $modded_time) {
            TimeHelper::setTime($modded_time);
            $insight_plugin->generateInsight($instance, $user, $posts, 3);
            // Assert that week-over-week comparison is correct
            $insight_dao = new InsightMySQLDAO();
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight('frequency', 10, $today);
            //$this->debug(Utils::varDumpToString($result));
            $this->assertNotNull($result);
            $this->assertIsA($result, "Insight");
            $this->assertNotNull($result->time_generated);
            if ($modded_time == 3) {
                $this->assertEqual('@testeriffic tweeted <strong>5 times</strong> in the past week',$result->headline);
            } else {
                $this->assertEqual('@testeriffic had <strong>5 tweets</strong> over the past week',$result->headline);
            }
            $this->assertPattern('/14 fewer tweets than the prior week/',$result->text);

            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testFrequencyInsightPriorSmallerBy2Baseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
        'instance_id'=>10, 'value'=>3));

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        foreach (array(1, 2, 3) as $modded_time) {
            TimeHelper::setTime($modded_time);
            $insight_plugin->generateInsight($instance, $user, $posts, 3);
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight('frequency', 10, $today);
            //$this->debug(Utils::varDumpToString($result));
            $this->assertNotNull($result);
            $this->assertIsA($result, "Insight");
            $this->assertNotNull($result->time_generated);
            if ($modded_time == 3) {
                $this->assertEqual('@testeriffic tweeted <strong>5 times</strong> in the past week',$result->headline);
                $this->assertPattern('/2 more tweets than the prior week/',$result->text);
            } else {
                $this->assertEqual('@testeriffic had <strong>5 tweets</strong> over the past week',$result->headline);
                $this->assertPattern('/2 more tweets than the prior week/',$result->text);
            }
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testFrequencyInsightPriorSmallerBy1Baseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();
        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
        'instance_id'=>10, 'value'=>4));

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        foreach (array(1, 2, 3) as $modded_time) {
            TimeHelper::setTime($modded_time);
            $insight_plugin->generateInsight($instance, $user, $posts, 3);
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight('frequency', 10, $today);
            //$this->debug(Utils::varDumpToString($result));
            $this->assertNotNull($result);
            $this->assertIsA($result, "Insight");
            $this->assertNotNull($result->time_generated);
            if ($modded_time == 3) {
                $this->assertEqual('@testeriffic tweeted <strong>5 times</strong> in the past week',$result->headline);
            } else {
                $this->assertEqual('@testeriffic had <strong>5 tweets</strong> over the past week',$result->headline);
            }
            $this->assertPattern('/1 more tweet than the prior week/',$result->text);

            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testFrequencyInsightPriorEqualBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FrequencyInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'frequency',
        'instance_id'=>10, 'value'=>5));

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('frequency', 10, $today);
        $this->assertNull($result);
    }

    /**
     * Get test post objects
     * @return array of post objects for use in testing
     */
    private function getTestPostObjects($network = 'twitter') {
        $post_text_arr = array();
        $post_text_arr[] = "I don't know, really? I thought so.";
        $post_text_arr[] = "Now that I'm back on Android, realizing just how under sung Google Now is. ".
        "I want it everywhere.";
        $post_text_arr[] = "New YearÕs Eve! Feeling very gay today, but not very homosexual.";
        $post_text_arr[] = "Took 1 firearms safety class to realize my ".
        "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde";
        $post_text_arr[] = "When @anildash told me he was writing this I was ".
        "like 'yah whatever cool' then I read it and it knocked my socks off http://bit.ly/W9ASnj ";
        $i = 1;
        $posts = array();
        foreach ($post_text_arr as $test_text) {
            $p = new Post();
            $p->id = $i;
            $p->post_id = 'pid'.$i;
            $p->network = $network;
            $p->post_text = $test_text;
            $posts[] = $p;
            $i++;
        }
        return $posts;
    }
}
