<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTopWordsInsight.php
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
 * Test of Top Words Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/topwords.php';

class TestOfTopWordsInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'bookworm';
        $instance->network = 'twitter';
        $this->instance = $instance;

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new TopWordsInsight();
        $this->assertIsA($insight_plugin, 'TopWordsInsight' );
    }

    public function testWeeklyOneWord() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = $this->generatePost("I love love love love books.", $i);
            $builders[] = $this->generatePost("love for wind and cheese too.", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNull($result);
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;love&#8221;</b> more than any '
            .'other on Test_no_monthly last week.');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used word last week');
        $this->assertNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyTwoWords() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = $this->generatePost("I love love love love books cheese.", $i);
            $builders[] = $this->generatePost("love for wind and books and book and cheese too.", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNull($result);
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;love&#8221;</b> more than any '
            .'other on Test_no_monthly last week, followed by &#8220;books.&#8221;');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used words last week');
        $this->assertNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyThreeWords() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = $this->generatePost("I love love love love books cheese cheese.", $i);
            $builders[] = $this->generatePost("love for wind and books and book and cheese too.", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNull($result);
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;love&#8221;</b> more than any '
            .'other on Test_no_monthly last week, followed by &#8220;cheese&#8221; and &#8220;books.&#8221;');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used words last week');
        $this->assertNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyMultipleWords() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I love books.", $i);
            $builders[] = $this->generatePost("Words are cool.", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNull($result);
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;Words&#8221;</b> more than any '
            .'other on Test_no_monthly last week, followed by &#8220;love,&#8221; &#8220;books,&#8221;  '
            .'and &#8220;cool.&#8221;');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used words last week');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMonthly() {
        $builders[] = $this->generatePost("I like cheese!", 1);
        $builders[] = $this->generatePost("I like cheese!", 1);
        $builders[] = $this->generatePost("I like cheese!", 1);
        $builders[] = $this->generatePost("I like Gouda cheese.", 5);
        $builders[] = $this->generatePost("I like Gouda cheese.", 5);
        $builders[] = $this->generatePost("I like Gouda cheese.", 5);
        $builders[] = $this->generatePost("I love cheddar cheese.", 5);
        $builders[] = $this->generatePost("I love cheddar cheese.", 5);
        $builders[] = $this->generatePost("I love cheddar cheese.", 5);
        $builders[] = $this->generatePost("Want to eat a banana?", 10);
        $builders[] = $this->generatePost("Want to eat a banana?", 10);
        $builders[] = $this->generatePost("Want to eat a banana?", 10);
        $builders[] = $this->generatePost("Cheese is often paired with wine.", 15);
        $builders[] = $this->generatePost("Cheese is often paired with wine.", 15);
        $builders[] = $this->generatePost("Cheese is often paired with wine.", 15);
        $builders[] = $this->generatePost("Where is the cheese?", 20);
        $builders[] = $this->generatePost("Where is the cheese?", 20);
        $builders[] = $this->generatePost("Where is the cheese?", 20);

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNull($result);
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, "@bookworm said the word <b>&#8220;cheese&#8221;</b> more than any other "
            ."on Twitter last month, followed by &#8220;eat,&#8221; &#8220;Gouda,&#8221; &#8220;paired,&#8221;  "
            ."and &#8220;love.&#8221;");
        $this->assertEqual($result->headline, '@bookworm\'s most-used words last month');
        $this->assertNotNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfHistoricWeekWithLotsOfWords() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I love books.", $i);
            $builders[] = $this->generatePost("Words are cool.", $i);
        }
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("Old old old old.", $i+8);
            $builders[] = $this->generatePost("This tweet is so last week.", $i+8);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;Words&#8221;</b> more than any '
            . 'other on Test_no_monthly last week, followed by &#8220;love,&#8221; &#8220;books,&#8221;  '
            . 'and &#8220;cool.&#8221; '
            . 'That\'s compared to the week before, when Jane Wordsmith\'s most-used words were &#8220;old,&#8221; '
            .'&#8220;last,&#8221; &#8220;tweet,&#8221; and &#8220;week.&#8221;');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used words last week');
        $this->assertNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfHistoricWeekWithTwoWords() {
        $this->instance->network = 'test_no_monthly';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I love books.", $i);
            $builders[] = $this->generatePost("Words are cool.", $i);
        }
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("Old old old old.", $i+8);
            $builders[] = $this->generatePost("wine wine wine wine wine", $i+8);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_week', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, 'Jane Wordsmith said the word <b>&#8220;Words&#8221;</b> more than any '
            . 'other on Test_no_monthly last week, followed by &#8220;love,&#8221; &#8220;books,&#8221;  '
            . 'and &#8220;cool.&#8221; '
            . 'That\'s compared to the week before, when Jane Wordsmith\'s most-used words were &#8220;wine&#8221; '
            . 'and &#8220;old.&#8221;');
        $this->assertEqual($result->headline, 'Jane Wordsmith\'s most-used words last week');
        $this->assertNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfHistoricMonthWithOneWord() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I love books.", $i);
            $builders[] = $this->generatePost("Words are cool.", $i);
        }
        $builders[] = $this->generatePost("Asimov Asimov Asimov Asimov.", 45);

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, '@bookworm said the word <b>&#8220;Words&#8221;</b> more than any other '
            . 'on Twitter last month, followed by &#8220;love,&#8221; &#8220;books,&#8221;  and &#8220;cool.&#8221; '
            . 'That\'s compared to the month before, when @bookworm\'s most-used word was &#8220;Asimov.&#8221;');
        $this->assertEqual($result->headline, '@bookworm\'s most-used words last month');
        $this->assertNotNull($result->related_data["hero_image"]);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfHtmlEntities() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("Letters &amp; symbols are my favorite things!", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, '@bookworm said the word <b>&#8220;favorite&#8221;</b> more than any other '
            . 'on Twitter last month, followed by &#8220;Letters,&#8221; &#8220;symbols,&#8221;  and '
            . '&#8220;things.&#8221;');
        $this->assertEqual($result->headline, '@bookworm\'s most-used words last month');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfEmailAndURLs() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("help@thinkup.com if you need help. @help", $i);
            $builders[] = $this->generatePost("help@thinkup.com is what you want @help", $i);
            $builders[] = $this->generatePost("http://awesome.com!", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, '@bookworm said the word <b>&#8220;help@thinkup.com&#8221;</b> more than '
            . 'any other on Twitter last month, followed by &#8220;want,&#8221; &#8220;need,&#8221; '
            . '&#8220;help,&#8221;  and &#8220;http://awesome.com.&#8221;');
        $this->assertEqual($result->headline, '@bookworm\'s most-used words last month');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOfHashtagExclusion() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I have so many hashtags. #blessed", $i);
            $builders[] = $this->generatePost("My kid is the cutest. #kidpost helps spread the cute. ", $i);
        }

        $insight_plugin = new TopWordsInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight('top_words_month', $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->text, '@bookworm said the word <b>&#8220;many&#8221;</b> more than any other '
            .'on Twitter last month, followed by &#8220;hashtags,&#8221; &#8220;spread,&#8221; &#8220;kid,&#8221;  '
            .'and &#8220;helps.&#8221;');
        $this->assertEqual($result->headline, '@bookworm\'s most-used words last month');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function generatePost($text, $days_ago) {
        static $i = 1;
        return FixtureBuilder::build('posts', array(
            'post_id' => $i++,
            'geo' => $is_geo ? '1.12345678,2.12345678' : '-1.123456,-2.1234567',
            'network' => $this->instance->network,
            'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'post_text' => $text,
            'pub_date' => (-1*$days_ago).'d'

        ));
    }
}
