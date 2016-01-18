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
 * @copyright 2014-2016 Chris Moyer
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

        TimeHelper::setTime(1425329077); // Set it to the March 2015 words timeframe
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
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is hawt', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool.', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before &ldquo;hawt&rdquo; went legit', $result->headline);
        $this->assertEqual('@testy used the word "hawt" once since '.
            $str_earliest_mention.', and it appears to have caught on: '.
            'It\'s <a href="http://blog.oxforddictionaries.com/2014/12/oxford-dictionaries-new-words-december-2014/">'.
            "just been added</a> to OxfordDictionariesOnline.com.", $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/seeminglee/4041872282');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: See-ming Lee');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2015-03/dictionarywordsomglol.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwoMatches() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE DUCKFACE!', 'pub_date' => '-580d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is my man crush.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is my man crush.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is my man crush.  No humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I have a lot of figurines.  #hummelbrag.  #humblebrag.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ThinkUp is cool.', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before &ldquo;man crush&rdquo; went legit', $result->headline);
        $this->assertEqual('OxfordDictionariesOnline.com <a href="http://blog.oxforddictionaries.com/2014/12/oxford'.
            '-dictionaries-new-words-december-2014/">just added</a> "man crush" and "duckface" to their online '.
            'dictionary, but no one has to explain them to @testy. Since '.$str_earliest_mention.
            ', @testy used "man crush" 3 times '.
            'and "duckface" once.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/seeminglee/4041872282');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: See-ming Lee');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2015-03/dictionarywordsomglol.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFourMatches() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE DUCK FACE!', 'pub_date' => '-380d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I click ALL THE DUCK FACE!', 'pub_date' => '-380d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hawt.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hawt.', 'pub_date' => '-480d'));
        $earliest_mention_builder = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'This test is a hawt.', 'pub_date' => '-480d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I love testing.', 'pub_date' => '-58d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Testing is really easy.  No man crush.', 'pub_date' => '-38d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Write some tests.  lolcat!', 'pub_date' => '-3d'));
        $insight_plugin = new NewDictionaryWordsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $earliest_mention = $earliest_mention_builder->columns["pub_date"];
        $str_earliest_mention = date('F Y', strtotime($earliest_mention));
        $result = $insight_dao->getInsight('new_dictionary_words', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual('Before &ldquo;hawt&rdquo; went legit', $result->headline);
        $this->assertEqual('OxfordDictionariesOnline.com '
            . '<a href="http://blog.oxforddictionaries.com/2014/12/oxford-dictionaries-new-words-december-2014/">'
            . 'just added</a> "hawt", "duck face", "man crush", and "lolcat" '
            . 'to their online dictionary, but no one has to explain them to @testy. Since '.$str_earliest_mention.
            ', @testy used "hawt" 3 times, "duck face" twice, "man crush" once, and "lolcat" once.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/seeminglee/4041872282');
        $this->assertEqual($data['hero_image']['alt_text'], 'New dictionary words');
        $this->assertEqual($data['hero_image']['credit'], 'Photo: See-ming Lee');
        $this->assertEqual($data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2015-03/dictionarywordsomglol.jpg');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
