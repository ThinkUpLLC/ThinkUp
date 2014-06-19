<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfPhotoPromptInsight.php
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
 * Test for PhotoPromptInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/photoprompt.php';

class TestOfPhotoPromptInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'anseladams';
        $this->instance->network = 'twitter';

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
        TimeHelper::setTime(2);
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new PhotoPromptInsight();
        $this->assertIsA($insight_plugin, 'PhotoPromptInsight' );
    }

    public function testNoPhotosAtAll() {
        $insight_plugin = new PhotoPromptInsight();
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $posts = array();

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('photoprompt', 10, $today);
        $this->assertNull($result);
    }

    public function testNoPhotosPastWeek() {
        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135,
            'author_user_id'=>1234, 'author_username'=>$this->instance->network_username,
            'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>date('Y-m-d', strtotime('-10 days')), 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://pic.twitter.com/vx4YL7Yz',
            'image_src' => 'http://pic.twitter.com.foo.jpg', 'expanded_url' => ''));

        $insight_plugin = new PhotoPromptInsight();
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $posts = array();

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('photoprompt', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Missed a photo opportunity?', $result->headline);
        $this->assertEqual("@anseladams hasn't posted a photo in 10 days. "
            . "It might be worth finding something to share.", $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadline() {
        TimeHelper::setTime(1);
        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135,
            'author_user_id'=>1234, 'author_username'=>$this->instance->network_username,
            'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>date('Y-m-d', strtotime('-12 days')), 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://pic.twitter.com/vx4YL7Yz',
            'image_src' => 'http://pic.twitter.com.foo.jpg', 'expanded_url' => ''));

        $insight_plugin = new PhotoPromptInsight();
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $posts = array();

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('photoprompt', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Picture this...', $result->headline);
        $this->assertEqual("@anseladams hasn't posted a photo in 12 days. "
            . "It might be worth finding something to share.", $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadline2() {
        TimeHelper::setTime(3);
        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135,
            'author_user_id'=>1234, 'author_username'=>$this->instance->network_username,
            'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>date('Y-m-d', strtotime('-8 days')), 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://pic.twitter.com/vx4YL7Yz',
            'image_src' => 'http://pic.twitter.com.foo.jpg', 'expanded_url' => ''));

        $insight_plugin = new PhotoPromptInsight();
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $posts = array();

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('photoprompt', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual("They're worth a thousand words...", $result->headline);
        $this->assertEqual("@anseladams hasn't posted a photo in 8 days. "
            . "It might be worth finding something to share.", $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
