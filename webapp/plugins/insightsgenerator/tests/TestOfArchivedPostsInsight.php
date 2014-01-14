<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfArchivedPostsInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of ArchivedPostsInsight
 *
 * Test for the ArchivedPostsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/archivedposts.php';

class TestOfArchivedPostsInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testArchivedPostsInsightTwitter() {
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'twitter';
        $instance->network_username = 'marypoppins';
        $instance->total_posts_in_system = 1684
        ;
        $insight_plugin = new ArchivedPostsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/1,600 tweets/', $result->headline);
        //Assert singlular minute
        $this->assertPattern('/7 hours 1 minute\</', $result->text);
        //No seconds
        $this->assertNoPattern('/second/', $result->text);

        // Increase number of posts in system for this instance
        $instance->total_posts_in_system = 167676;
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/167,600 tweets/', $result->headline);
    }

    public function testArchivedPostsInsightFacebook() {
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'facebook';
        $instance->network_username = 'Mary Poppins';
        $instance->total_posts_in_system = 1500;
        $insight_plugin = new ArchivedPostsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/1,500 status updates/', $result->headline);

        // Increase number of posts in system for this instance
        $instance->total_posts_in_system = 167676;
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/167,600 status updates/', $result->headline);
    }

    public function testArchivedPostsInsightGooglePlus() {
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'google+';
        $instance->network_username = 'Mary Poppins';
        $instance->total_posts_in_system = 1500;
        $insight_plugin = new ArchivedPostsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/1,500 posts/', $result->headline);

        // Increase number of posts in system for this instance
        $instance->total_posts_in_system = 167676;
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/167,600 posts/', $result->headline);
    }

    public function testArchivedPostsInsightFoursquare() {
        // Get data ready that insight requires
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'foursquare';
        $instance->network_username = 'mary@poppins.com';
        $instance->total_posts_in_system = 1500;
        $insight_plugin = new ArchivedPostsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/1,500 checkins/', $result->headline);

        // Increase number of posts in system for this instance
        $instance->total_posts_in_system = 167676;
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('archived_posts', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/ThinkUp captured/', $result->headline);
        $this->assertPattern('/167,600 checkins/', $result->headline);
    }
}
