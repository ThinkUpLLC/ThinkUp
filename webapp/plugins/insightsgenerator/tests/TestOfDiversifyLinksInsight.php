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

class TestOfDiversifyLinksInsight extends ThinkUpInsightUnitTestCase {

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

    public function tesNoLinks() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNull($result);
    }

    public function testPopularUrlUnder50Links() {
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
        'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
        'title'=>'Link 1', 'post_key'=>138, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
        'title'=>'Link 1', 'post_key'=>139, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>150, 'post_id'=>150, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
        'title'=>'Link 1', 'post_key'=>150, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>140, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>141, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>142, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>143, 'post_id'=>143, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example3.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example3.com/1',
        'title'=>'Link 1', 'post_key'=>143, 'expanded_url'=>'http://example3.com/1', 'error'=>'', 'image_src'=>''));
        $builders[] = FixtureBuilder::build('posts', array('id'=>144, 'post_id'=>144, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example3.com/1 with a link.', 'source'=>'web',
        'pub_date'=>date('Y-m-d H:i:s', strtotime('-2 weeks')), 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example3.com/1',
        'title'=>'Link 1', 'post_key'=>144, 'expanded_url'=>'http://example3.com/1', 'error'=>'', 'image_src'=>''));

        TimeHelper::setTime(3);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');

        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "@testeriffic shared links to example1.com in 4 tweets this month &mdash; "
            . "more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 4);
        $this->assertEqual($data['url_counts']['example2.com'], 3);
        $this->assertEqual($data['url_counts']['example3.com'], 2);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com','example2.com','example3.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPopularUrlOver50Links() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 162; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.','source'=>'web',
            'pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")), 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        for($i = 163; $i <= 186; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.','source'=>'web',
            'pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")), 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));
        }

        for($i = 189; $i <= 214; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example3.com/1 with a link.','source'=>'web',
            'pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")), 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example3.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example3.com/1', 'error'=>'', 'image_src'=>''));
        }

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');

        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "It's good to spread the link love. @testeriffic shared links equally to "
            . "example1.com and example3.com this month.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 26);
        $this->assertEqual($data['url_counts']['example3.com'], 26);
        $this->assertEqual($data['url_counts']['example2.com'], 24);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com','example3.com','example2.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPopularUrlOver100Links() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 180; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter','post_text'=>'This is an old post http://example1.com/1 with a link.',
            'source'=>'web','pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")), 'reply_count_cache'=>0,
            'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1','post_key'=>$i,'expanded_url'=>'http://example1.com/1', 'error'=>'','image_src'=>''));
        }

        for($i = 181; $i <= 214; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.',
            'source'=>'web','pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")), 'reply_count_cache'=>0,
            'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
            'title'=>'Link 1','post_key'=>$i,'expanded_url'=>'http://example2.com/1', 'error'=>'','image_src'=>''));
        }

        for($i = 215; $i <= 248; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example3.com/1 with a link.',
            'source'=>'web','pub_date'=>date('Y-m-d H:i',strtotime("-$i minutes")),
            'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example3.com/1',
            'title'=>'Link 1','post_key'=>$i,'expanded_url'=>'http://example3.com/1','error'=>'', 'image_src'=>''));
        }

        TimeHelper::setTime(1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');

        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "@testeriffic shared links to example1.com in 44 tweets this month &mdash; "
            . "more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 44);
        $this->assertEqual($data['url_counts']['example2.com'], 34);
        $this->assertEqual($data['url_counts']['example3.com'], 34);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com','example3.com','example2.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test50Majority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 187; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.','source'=>'web',
            'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => NULL,
        'network'=>'twitter', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        TimeHelper::setTime(1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "@testeriffic shared links to example1.com in 51 tweets this month &mdash; "
            . "more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 51);
        $this->assertEqual($data['url_counts']['example2.com'], 1);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com','example2.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test100Majority() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 237; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.','source'=>'web',
            'in_retweet_of_post_id' => $i%2 == 1 ? null : 12,
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

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to site this month");
        $this->assertEqual($result->text, "It was the best of tabs, it was the worst of tabs. @testeriffic tweeted 51 "
            . "links and retweeted 50 links to example1.com this month &mdash; more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 101);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testManyLinks() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 337; $i++) {
            if ($i==337) {
                $builders[] = FixtureBuilder::build('posts', array('id'=>$i+1, 'post_id'=>$i+1,
                'author_user_id'=>7612345, 'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User',
                'author_avatar'=>'avatar.jpg', 'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
                'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.',
                'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
                $builders[] = FixtureBuilder::build('links', array('url'=>'http://test'.$i.'.com/',
                'title'=>'Link 1', 'post_key'=>$i+1, 'expanded_url'=>'http://test'.$i.'.com/', 'error'=>'',
                'image_src'=>''));
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
            'network'=>'twitter', 'post_text'=>'This is an old post http://example1.com/1 with a link.','source'=>'web',
            'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://test'.$i.'.com/',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://test'.$i.'.com/', 'error'=>'', 'image_src'=>''));
        }

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "It was the best of tabs, it was the worst of tabs. @testeriffic tweeted "
            ."2 links to test337.com this month &mdash; more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['test337.com'], 2);
        $this->assertEqual(count($data['url_counts']), 20);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFacebook() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 187; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL, 'in_retweet_of_post_id' => $i==186 ? 1 : null,
            'network'=>'facebook', 'post_text'=>'This is an old post http://example1.com/1 with a link.',
            'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example1.com/1',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://example1.com/1', 'error'=>'', 'image_src'=>''));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>188, 'post_id'=>188, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is an old post http://example2.com/1 with a link.', 'source'=>'web',
        'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/1',
        'title'=>'Link 1', 'post_key'=>188, 'expanded_url'=>'http://example2.com/1', 'error'=>'', 'image_src'=>''));

        TimeHelper::setTime(1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'Linking Lady';
        $instance->network = 'facebook';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "Linking Lady's most linked-to site this month");
        $this->assertEqual($result->text, "Linking Lady shared links to example1.com in 50 status updates and 1 "
            . "reshare this month &mdash; more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['example1.com'], 51);
        $urls = array_keys($data['url_counts']);
        $this->assertEqual(array('example1.com'), $urls);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWwwStripping() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        for($i = 137; $i <= 337; $i++) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i, 'author_user_id'=>7612345,
            'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
            'network'=>'twitter','post_text'=>'This is an old post http://www.'.($i%2==1?'a':'b').'.com/1 with a link.',
            'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://www.'.($i%2==1?'a':'b').'.com/',
            'title'=>'Link 1', 'post_key'=>$i, 'expanded_url'=>'http://www.'.($i%2==1?'a':'b').'.com/',
            'error'=>'', 'image_src'=>''));
        }

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->headline, "@testeriffic's most linked-to sites this month");
        $this->assertEqual($result->text, "It was the best of tabs, it was the worst of tabs. @testeriffic ".
            "tweeted 101 links to a.com this month &mdash; more than to any other site.");
        $data = unserialize($result->related_data);
        $this->assertEqual($data['url_counts']['www.a.com'], 101);
        $this->assertEqual($data['url_counts']['www.b.com'], 100);
        $this->assertEqual(count($data['url_counts']), 2);

        $this->debug($rendered = $this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $this->assertPattern('/>a.com</', $rendered);
        $this->assertNoPattern('/>www.a.com</', $rendered);
        $this->assertPattern('/>b.com</', $rendered);
        $this->assertNoPattern('/>www.b.com</', $rendered);
    }

    public function testOneLinkTwitterV1() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>444, 'post_id'=>444, 'author_user_id'=>7612345,
         'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
         'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
         'network'=>'twitter','post_text'=>'This is an old post http://www.aaa.com/1 with a link.',
         'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://www.aaaa.com/',
         'title'=>'Link 1', 'post_key'=>444, 'expanded_url'=>'http://www.aaaa.com/',
         'error'=>'', 'image_src'=>''));

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->text, 'It was the best of tabs, it was the worst of tabs. @testeriffic tweeted '
            .'1 link to aaaa.com this month.');
        $this->assertEqual("@testeriffic's most linked-to site this month", $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOneLinkFacebookV2() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>444, 'post_id'=>444, 'author_user_id'=>7612345,
         'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
         'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
         'network'=>'facebook','post_text'=>'This is an old post http://www.aaa.com/1 with a link.',
         'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://www.aaaa.com/',
         'title'=>'Link 1', 'post_key'=>444, 'expanded_url'=>'http://www.aaaa.com/',
         'error'=>'', 'image_src'=>''));

        TimeHelper::setTime(3);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertEqual($result->text, 'testeriffic shared a link to aaaa.com in 1 status update this month.');
        $this->assertEqual("testeriffic's most linked-to site this month", $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPhotoSkipping() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>444, 'post_id'=>444, 'author_user_id'=>7612345,
         'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
         'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
         'network'=>'twitter','post_text'=>'This is an old post http://www.aaa.com/1 with a link.',
         'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://www.aaaa.com/',
         'title'=>'Link 1', 'post_key'=>444, 'expanded_url'=>'http://www.aaaa.com/',
         'error'=>'', 'image_src'=>'http://photo.com/photo.jpg'));

        TimeHelper::setTime(2);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new DiversifyLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);
        $this->assertNull($result);

        $builders[] = FixtureBuilder::build('posts', array('id'=>445, 'post_id'=>445, 'author_user_id'=>7612345,
         'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
         'in_reply_to_user_id' => NULL,'in_retweet_of_post_id' => NULL,
         'network'=>'twitter','post_text'=>'This is an old post http://www.aaa.com/1 with a link.',
         'source'=>'web', 'pub_date'=>$days_ago_3, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('url'=>'http://www.aaaa.com/',
         'title'=>'Link 1', 'post_key'=>445, 'expanded_url'=>'http://www.aaaa.com/',
         'error'=>'', 'image_src'=>''));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('diversify_links_monthly', 10, $today);

        $this->assertEqual($result->text, 'It was the best of tabs, it was the worst of tabs. @testeriffic tweeted '
            .'1 link to aaaa.com this month.');
        $this->assertEqual("@testeriffic's most linked-to site this month", $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
