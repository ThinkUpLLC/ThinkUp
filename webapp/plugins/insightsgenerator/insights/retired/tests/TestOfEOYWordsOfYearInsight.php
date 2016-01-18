<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYWordsOfYearInsight.php
 *
 * Copyright (c) Gina Trapani
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
 * @copyright 2014-2016 Gina Trapani
 * @author Gina Trapani <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoywordsofyear.php';

class TestOfEOYWordsOfYearInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'testy';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(1418947200); // Set it to the December 2014 words timeframe
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYWordsOfYearInsight();
        $this->assertIsA($insight_plugin, 'EOYWordsOfYearInsight' );
    }

    public function testNoMatch() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => "I only use historically wordfull words."));

        $insight_plugin = new EOYWordsOfYearInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('eoy_words_of_year', 10, $today);
        $this->assertNull($result);
    }

    public function testOneMatch() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool it has that certain je ne sais quoi', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is hard.  FML.', 'pub_date' => '-3d'));
        $insight_plugin = new EOYWordsOfYearInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('eoy_words_of_year', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@testy was all over 2014\'s Words of the Year', $result->headline);


        $this->assertEqual('@testy said the word "je ne sais quoi" once on Twitter since '.
            $str_earliest_mention.', and it appears to have caught on: '.
            'The Merriam-Webster Dictionary just named it a '.
            '<a href="http://www.merriam-webster.com/info/2014-word-of-the-year.htm">Word of the Year for 2014</a>.',
            $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/crdot/5510506796/');
        $this->assertEqual($data['hero_image']['alt_text'], 'Words of the Year');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Caleb Roenigk');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-12/dictionary-words-of-year.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwoMatches() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE culture CLICKBAIT!', 'pub_date' => '-580d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing culture is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing culture is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing culture is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I have a lot of figurines.  #hummelbrag.  #humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool.', 'pub_date' => '-3d'));
        $insight_plugin = new EOYWordsOfYearInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('eoy_words_of_year', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@testy was all over 2014\'s Words of the Year', $result->headline);
        $this->assertEqual('@testy said the word "culture" 4 times on Twitter since '.$str_earliest_mention.
            ', and it appears to have caught on: The Merriam-Webster Dictionary just named it a '.
            '<a href="http://www.merriam-webster.com/info/2014-word-of-the'.
            '-year.htm">Word of the Year for 2014</a>.', $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/crdot/5510506796/');
        $this->assertEqual($data['hero_image']['alt_text'], 'Words of the Year');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Caleb Roenigk');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-12/dictionary-words-of-year.jpg');

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
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is an insidious hot mess.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Write some tests.  yolo!', 'pub_date' => '-3d'));
        $insight_plugin = new EOYWordsOfYearInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('eoy_words_of_year', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@testy was all over 2014\'s Words of the Year', $result->headline);
        $this->assertEqual('@testy said the word "insidious" once on Twitter since August 2013, and it appears '
            . 'to have caught on: The Merriam-Webster Dictionary just named it a '
            . '<a href="http://www.merriam-webster.com/info/2014-word-of-the'
            . '-year.htm">Word of the Year for 2014</a>.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/crdot/5510506796/');
        $this->assertEqual($data['hero_image']['alt_text'], 'Words of the Year');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: Caleb Roenigk');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-12/dictionary-words-of-year.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
