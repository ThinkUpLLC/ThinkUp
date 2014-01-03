<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLinkPromptInsight.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * Test of LinkPromptInsight
 *
 * Test for the LinkPromptInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/linkprompt.php';

class TestOfLinkPromptInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLinkPromptInsight() {
        // Get data ready that insight requires
        $builders = self::buildData();

        $today = date('Y-m-d H:i:s');
        $days_ago_1 = date('Y-m-d H:i:s', strtotime('yesterday'));
        $days_ago_2 = date('Y-m-d H:i:s', strtotime('-2 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$today, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$days_ago_1, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$days_ago_1, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$days_ago_2, 'reply_count_cache'=>0, 'is_protected'=>0));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new LinkPromptInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('link_prompt', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic hasn\'t tweeted a link in the last 2 days/', $result->headline);
    }

    public function testLinkPromptInsighPromptWithRecentTweet() {
        // Get data ready that insight requires
        $builders = self::buildData();

        $days_ago_1 = date('Y-m-d H:i:s', strtotime('1 hour ago'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>241, 'post_id'=>241, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>"I don't always tweet holiday gift guides, but when I do, it's from @overthinkingit: http://t.co/jZTI9PGm9b\nIdeas for the geek on your list.", 'source'=>'web',
        'pub_date'=>$days_ago_1, 'reply_count_cache'=>0, 'is_protected'=>0));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new LinkPromptInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that no insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('link_prompt', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    public function testLinkPromptInsightNoPromptForRecentLinkPost() {
        // Get data ready that insight requires
        $builders = self::buildData();

        $days_ago_1 = date('Y-m-d H:i:s', strtotime('yesterday'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>241, 'post_id'=>241, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_1, 'reply_count_cache'=>0, 'is_protected'=>0));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new LinkPromptInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that no insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('link_prompt', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    public function testLinkPromptInsightNoPromptForNoRecentPosts() {
        // Get data ready that insight requires
        $builders = self::buildData();

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new LinkPromptInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that no insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('link_prompt', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    private function buildData() {
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $days_ago_40 = date('Y-m-d H:i:s', strtotime('-40 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/2 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/3 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/4 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));

        // Insert 100 posts without any links, but more than 30 days old
        for ($i = 0; $i < 100; $i++) {
            $post_key = 141 + $i;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
            'author_user_id'=>7612345, 'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg', 'network'=>'twitter', 'post_text'=>'This is a very old post.',
            'source'=>'web', 'pub_date'=>$days_ago_40, 'reply_count_cache'=>0, 'is_protected'=>0));
        }

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/1',
        'title'=>'Link 1', 'post_key'=>137, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/2',
        'title'=>'Link 2', 'post_key'=>138, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/3',
        'title'=>'Link 3', 'post_key'=>139, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/4',
        'title'=>'Link 4', 'post_key'=>140, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        return $builders;
    }
}
