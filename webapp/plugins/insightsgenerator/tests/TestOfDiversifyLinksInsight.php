<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfDiversifyLinksInsight.php
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
 * TestOfDiversifyLinks
 *
 * Tests the diversify links Insight.
 *
 * Copyright (c) Gareth Brady
 *
 * @author Gareth Brady gareth.brady92@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/diversifylinks.php';

class TestOfDiversifyLinksInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new DiversifyLinksInsight();
        $this->assertIsA($insight_plugin, 'DiversifyLinksInsight');
      }

    public function testLessThan5Links() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/1',
        'title'=>'Link 1', 'post_key'=>137, 'expanded_url'=>'http://example.com/1', 'error'=>'', 'image_src'=>''));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_weekly', 10, $today);
        $this->assertNull($result);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNull($result);
    }

    public function testNoMajority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
        'title'=>'Link 1', 'post_key'=>137, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>138, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example3.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example3.com/1',
        'title'=>'Link 1', 'post_key'=>139, 'expanded_url'=>'http://example3.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example4.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example4.com/1',
        'title'=>'Link 1', 'post_key'=>140, 'expanded_url'=>'http://example4.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example5.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example5.com/1',
        'title'=>'Link 1', 'post_key'=>141, 'expanded_url'=>'http://example5.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example6.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example6.com/1',
        'title'=>'Link 1', 'post_key'=>142, 'expanded_url'=>'http://example6.com/1', 'error'=>'', 'image_src'=>''));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_weekly', 10, $today);
        $this->assertNull($result);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNull($result);
    }

    public function test50Majority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 187; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
            'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>188, 'post_id'=>188, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>188, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));



        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);


        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_weekly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $this->assertNotEqual(false, strpos($result->text, "followers"));
        $related_data = 'a:1:{s:9:"bar_chart";s:134:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":50}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $this->assertNotEqual(false, strpos($result->text, "followers"));
        $related_data = 'a:1:{s:9:"bar_chart";s:134:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":50}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);
    }

    public function test100Majority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 237; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
            'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>238, 'post_id'=>238, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>238, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));



        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_weekly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $related_data = 'a:1:{s:9:"bar_chart";s:135:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":100}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);


        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $related_data = 'a:1:{s:9:"bar_chart";s:135:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":100}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);
    }

    public function test50FacebookMajority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 187; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
            'network'=>'facebook', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
            'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>188, 'post_id'=>188, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>188, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));



        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_weekly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $this->assertNotEqual(false, strpos($result->text, "friends"));
        $related_data = 'a:1:{s:9:"bar_chart";s:134:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":50}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);


        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNotEqual(false, strpos($result->text, "<strong>example1.com</strong>"));
        $this->assertNotEqual(false, strpos($result->text, "friends"));
        $related_data = 'a:1:{s:9:"bar_chart";s:134:"{"rows":[{"c":[{"v":"example1.com"}';
        $related_data .= ',{"v":50}]}],"cols":[{"type":"string","label":"Url"}';
        $related_data .= ',{"type":"number","label":"Number of Shares"}]}";}';
        $this->assertEqual($related_data, $result->related_data);
    }

}

