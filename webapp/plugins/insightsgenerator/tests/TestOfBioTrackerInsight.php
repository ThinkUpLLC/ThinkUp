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

        $this->assertEqual($result->headline, "@newlywed changes it up");
        $this->assertEqual($result->text, 
            "@newlywed has an updated Twitter profile. Even small changes can be big news.");
        $this->assertNotNull($result->header_image);
        $this->assertEqual($result->header_image,
            'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg');

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
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Not Anil',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>2,
        'network'=>'twitter', 'description'=>'I am a father, woodworker, sandwich, bird, and pushover. '.
            'RTs != endorsements', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'3', 'user_name'=>'movingperson',
        'post_count' => 101, 'follower_count'=>36000,'is_protected'=>0,'friend_count'=>1, 'full_name'=>'Maybe Anil',
        'avatar'=>'https://pbs.twimg.com/profile_images/476939811702718464/Qq0LPfRy_400x400.jpeg', 'id' =>3,
        'network'=>'twitter', 'description'=>'I live in France.', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'3', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "I am a father, matchmaker, sandwich, bird, and pushover.", 'crawl_time' => '-2d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 3, 'field_name' => 'description',
            'field_value' => "I use Google+", 'crawl_time' => '-3d'));


        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, 'Ch-ch-ch-ch-changes');
        $this->assertEqual($result->text, "2 of @buffy's friends changed their Twitter description. "
            . "Even small changes can be big news.");
        $this->assertNull($result->header_image);

        $data = unserialize($result->related_data);
        $this->assertEqual(2, count($data['changes']));
        $this->assertEqual($data['changes'][0]['user']->username, 'newlywed');
        $this->assertEqual($data['changes'][0]['field_name'], 'description');
        $this->assertEqual($data['changes'][0]['field_description'], 'bio');
        $this->assertEqual($data['changes'][0]['before'], 'I am a father, matchmaker, sandwich, bird, and pushover.');
        $this->assertEqual($data['changes'][0]['after'], 'I am a father, woodworker, sandwich, bird, and pushover. '.
            'RTs != endorsements');

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
            "@newlywed changes it up",
            "@newlywed makes an adjustment",
            "@newlywed tries something new",
            "What's new with @newlywed",
            "Something's different about @newlywed",
        );

        $texts = array(
            "",
            "@newlywed has an updated Twitter profile. Even small changes can be big news.",
            "@newlywed has an updated Twitter profile. Spot the difference?",
            "@newlywed has an updated Twitter profile. Even small changes can be big news.",
            "@newlywed has an updated Twitter profile. Spot the difference?",
            "@newlywed has an updated Twitter profile. Even small changes can be big news.",
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
            "Ch-ch-ch-ch-changes",
            "Change is afoot",
            "Changing of the profile",
            "Ch-ch-ch-ch-changes",
            "Change is afoot",
            "Changing of the profile",
            "Ch-ch-ch-ch-changes",
        );

        $texts = array(
            "",
            "2 of @buffy's friends changed their Twitter description. Even small changes can be big news.",
            "2 of @buffy's friends changed their Twitter description. They might appreciate that someone noticed.",
            "2 of @buffy's friends changed their Twitter description. Spot the difference?",
            "2 of @buffy's friends changed their Twitter description. Even small changes can be big news.",
            "2 of @buffy's friends changed their Twitter description. They might appreciate that someone noticed.",
            "2 of @buffy's friends changed their Twitter description. Spot the difference?",
            "2 of @buffy's friends changed their Twitter description. Even small changes can be big news.",
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

    /**
     * This test will fail on PHP 5.3, which won't display the diff with the bullet point correctly in the rendered HTML
     */
    public function testDiffEncoding() {
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
        'network'=>'twitter', 'description'=>'Cofounder @thinkup & @activateinc • Writer @Medium',
        'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Change
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'description',
            'field_value' => "Cofounder @thinkup & @activateinc • Writer @Medium & @Wired • ".
            'Blog: http://t.co/5p9HDVJDna • anil@dashes.com • 646 833-8659 • Sign up: https://t.co/1m3JNdJKwy',
            'crawl_time' => '-2d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 3, 'field_name' => 'description',
            'field_value' => "Cofounder @thinkup & @activateinc • Writer @Medium & @Wired", 'crawl_time' => '-3d'));

        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);

        $insight_plugin = new BioTrackerInsight();
        $insight_dao = new InsightMySQLDAO();

        TimeHelper::setTime(1);
        $insight_plugin->generateInsight($this->instance, $user, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, 10, $this->today);
        $this->assertNotNull($result);
        $rendered_html = $this->getRenderedInsightInHTML($result);
        $this->assertPattern('/Cofounder @thinkup &amp; @activateinc &bull; Writer /', $rendered_html);
        $this->debug($rendered_html);
    }
}
