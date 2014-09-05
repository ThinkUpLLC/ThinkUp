<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfNewDictionaryWordsInsight.php
 *
 * Copyright (c) Chris Moyer
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
 * Test of LOL Count Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/newdictionarywords.php';

class TestOfNewDictionaryWordsInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'testy';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(1410739200); // Set it to the September 2014 words timeframe
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new NewDictionaryWordsInsight();
        $this->assertIsA($insight_plugin, 'NewDictionaryWordsInsight' );
    }

    public function testNoMatch() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => "I only use historically wordfull words."));

        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_dictionary_words', 10, $today);
        $this->assertNull($result);
    }

    public function testOneMatch() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is hard.  FML.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool.', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before "FML" went legit', $result->headline);
        $this->assertEqual('@testy used the word "FML" once since July 2014, and it appears to have caught on: '.
            'It\'s <a href="http://blog.oxforddictionaries.com/2014/08/oxford-dictionaries-update-august-2014/">'.
            "just been added</a> to the Oxford English Dictionary.", $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'http://www.flickr.com/photos/bethanyking/822518337');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Bethany King');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-08/new_dictionary_words.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwoMatches() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE CLICKBAIT!', 'pub_date' => '-580d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I have a lot of figurines.  #hummelbrag.  #humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool.', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before "humblebrag" went legit', $result->headline);
        $this->assertEqual('The Oxford English Dictionary '
            . '<a href="http://blog.oxforddictionaries.com/2014/08/oxford-dictionaries-update-august-2014/">'
            . 'just added</a> "humblebrag" and "clickbait" to their online '
            . 'dictionary, but no one has to explain them to @testy. Since February 2013, @testy used "humblebrag" 4 '
            . 'times and "clickbait" once.', $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'http://www.flickr.com/photos/bethanyking/822518337');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Bethany King');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-08/new_dictionary_words.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFourMatches() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE CLICKBAIT!', 'pub_date' => '-380d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE CLICKBAIT!', 'pub_date' => '-380d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hot mess.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hot mess.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hot mess.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Write some tests.  yolo!', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before "hot mess" went legit', $result->headline);
        $this->assertEqual('The Oxford English Dictionary '
            . '<a href="http://blog.oxforddictionaries.com/2014/08/oxford-dictionaries-update-august-2014/">'
            . 'just added</a> "hot mess", "clickbait", "YOLO", and "humblebrag" '
            . 'to their online dictionary, but no one has to explain them to @testy. Since May 2013, @testy used '
            . '"hot mess" 3 times, "clickbait" twice, "YOLO" once, and "humblebrag" once.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'http://www.flickr.com/photos/bethanyking/822518337');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Bethany King');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-08/new_dictionary_words.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
