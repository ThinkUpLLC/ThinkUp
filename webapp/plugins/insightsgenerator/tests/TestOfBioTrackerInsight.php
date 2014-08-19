<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfBioTrackerInsight.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test for BioTrackerInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/biotracker.php';

class TestOfBioTrackerInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();
        $this->today = date('Y-m-d');

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'buffy';
        $this->instance->network = 'twitter';

        TimeHelper::setTime(1);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new BioTrackerInsight();
        $this->assertIsA($insight_plugin, 'BioTrackerInsight' );
    }

    public function testWithNoChanges() {
        $insight_plugin = new BioTrackerInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 10, $today);
        $this->assertNull($result);
    }

    public function testWithOneChange() {
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'nosey',
        'full_name'=>'Twitter User', 'follower_count'=>1, 'is_protected'=>1, 'id' => 1,
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg',
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Friend
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'user_name'=>'newlywed',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>2,
        'network'=>'twitter', 'description'=>'I just got married!', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "I'm getting married soon.", 'crawl_time' => '-2d'));


        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Something's different about @newlywed.");
        $this->assertEqual($result->text, "@newlywed has a new Twitter bio. Even small changes can be big news.");

        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['changes']));
        $this->assertEqual($data['changes'][0]['user']->username, 'newlywed');
        $this->assertEqual($data['changes'][0]['field_name'], 'description');
        $this->assertEqual($data['changes'][0]['field_description'], 'bio');
        $this->assertEqual($data['changes'][0]['before'], 'I\'m getting married soon.');
        $this->assertEqual($data['changes'][0]['after'], 'I just got married!');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWithMultipleChanges() {
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'nosey',
        'full_name'=>'Twitter User', 'follower_count'=>1, 'is_protected'=>1, 'id' => 1,
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg',
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Friends
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'user_name'=>'newlywed',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>2,
        'network'=>'twitter', 'description'=>'I just got married!', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'3', 'user_name'=>'movingperson',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>3,
        'network'=>'twitter', 'description'=>'I live in France.', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'3', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "I'm getting married soon.", 'crawl_time' => '-2d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 3, 'field_name' => 'description',
            'field_value' => "I use Google+", 'crawl_time' => '-3d'));


        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, 'Changing of the Bio.');
        $this->assertEqual($result->text, "2 of @buffy's friends changed their Twitter bios. "
            . "Even small changes can be big news.");

        $data = unserialize($result->related_data);
        $this->assertEqual(2, count($data['changes']));
        $this->assertEqual($data['changes'][0]['user']->username, 'newlywed');
        $this->assertEqual($data['changes'][0]['field_name'], 'description');
        $this->assertEqual($data['changes'][0]['field_description'], 'bio');
        $this->assertEqual($data['changes'][0]['before'], 'I\'m getting married soon.');
        $this->assertEqual($data['changes'][0]['after'], 'I just got married!');

        $this->assertEqual($data['changes'][1]['user']->username, 'movingperson');
        $this->assertEqual($data['changes'][1]['field_name'], 'description');
        $this->assertEqual($data['changes'][1]['field_description'], 'bio');
        $this->assertEqual($data['changes'][1]['before'], 'I use Google+');
        $this->assertEqual($data['changes'][1]['after'], 'I live in France.');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateSingleText() {
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'nosey',
        'full_name'=>'Twitter User', 'follower_count'=>1, 'is_protected'=>1, 'id' => 1,
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg',
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Friend
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'user_name'=>'newlywed',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>2,
        'network'=>'twitter', 'description'=>'I just got married!', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "I'm getting married soon.", 'crawl_time' => '-2d'));


        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_dao = new InsightMySQLDAO();

        $headlines = array(
            "",
            "Something's different about @newlywed.",
            "What's changed about @newlywed?",
            "Something's happening with @newlywed.",
            "Did anyone notice what's different about @newlywed?",
            "What's new with @newlywed?",
        );

        $texts = array(
            "",
            "@newlywed has a new Twitter bio. Even small changes can be big news.",
            "@newlywed has a new Twitter bio. @newlywed might appreciate that someone noticed.",
            "@newlywed has a new Twitter bio. Spot the difference?",
            "@newlywed has a new Twitter bio. Even small changes can be big news.",
            "@newlywed has a new Twitter bio. @newlywed might appreciate that someone noticed.",
        );

        for ($i=1; $i<6; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
            $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
            $this->assertNotNull($result);
            $this->assertEqual($result->headline, $headlines[$i]);
            $this->assertEqual($result->text, $texts[$i]);
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testAlternateMultiText() {
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'nosey',
        'full_name'=>'Twitter User', 'follower_count'=>1, 'is_protected'=>1, 'id' => 1,
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg',
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Friend
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'user_name'=>'newlywed',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>2,
        'network'=>'twitter', 'description'=>'I just got married!', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'3', 'user_name'=>'movingperson',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Popular Gal',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>3,
        'network'=>'twitter', 'description'=>'I live in France.', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'3', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "I'm getting married soon.", 'crawl_time' => '-2d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 3, 'field_name' => 'description',
            'field_value' => "I use Google+", 'crawl_time' => '-3d'));


        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_dao = new InsightMySQLDAO();

        $headlines = array(
            "",
            "Changing of the Bio.",
            "Auto Biography.",
            "Ch-ch-ch-ch-changes!",
            "Bio(nic) Vision.",
            "Mapping the Twitter Bio-me.",
            "Hi Profile Changes!",
            "Changing of the Bio.",
        );

        $texts = array(
            "",
            "2 of @buffy's friends changed their Twitter bios. Even small changes can be big news.",
            "2 of @buffy's friends changed their Twitter bios. They might appreciate that someone noticed.",
            "2 of @buffy's friends changed their Twitter bios. Spot the difference?",
            "2 of @buffy's friends changed their Twitter bios. Even small changes can be big news.",
            "2 of @buffy's friends changed their Twitter bios. They might appreciate that someone noticed.",
            "2 of @buffy's friends changed their Twitter bios. Spot the difference?",
            "2 of @buffy's friends changed their Twitter bios. Even small changes can be big news.",
        );

        for ($i=1; $i<8; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
            $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
            $this->assertNotNull($result);
            $this->assertEqual($result->headline, $headlines[$i]);
            $this->assertEqual($result->text, $texts[$i]);
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }
}
