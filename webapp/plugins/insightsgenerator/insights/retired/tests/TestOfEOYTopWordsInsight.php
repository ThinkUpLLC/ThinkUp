<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYTopWordsInsight.php
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoytopwords.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/topwords.php';

class TestOfEOYTopWordsInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'bookworm';
        $instance->network = 'twitter';
        $this->instance = $instance;

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYTopWordsInsight();
        $this->assertIsA($insight_plugin, 'EOYTopWordsInsight' );
    }

    public function testSeveralWordsTwitter() {
        $builders = array();
        for ($i=0; $i<5; $i++) {
            $builders[] = $this->generatePost("I love love love love books.", $i);
            $builders[] = $this->generatePost("love for wine and cheese too.", $i);
        }

        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@bookworm's most-used words on Twitter, $year");
        $this->assertEqual($result->text, "Would you say it's been a &#8220;love&#8221; year? @bookworm might. "
            . "@bookworm mentioned <strong>&#8220;love&#8221; 20 times</strong> on Twitter in $year. That's more than "
            . "any other word this year &mdash; followed by &#8220;wine,&#8221; &#8220;books,&#8221; "
            . "and &#8220;cheese.&#8221;");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertEqual(4, count($data['bar_chart']['rows']));

        $result->id=1;
        $this->dumpRenderedInsight($result, $this->instance, "Several Words, Twitter");
    }

    public function testSeveralWordsFacebook() {
        $this->instance->network = 'facebook';
        $this->instance->network_username = 'Jane Wordsmith';
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = $this->generatePost("I created an array of arrays. I am creating of things.", $i);
            $builders[] = $this->generatePost("Things are cool. So are arrays. And creating them.  Creator is me.", $i);
            $builders[] = $this->generatePost("I wrote medium piece (127 minutes) about PHP Arrays. And things.", $i);
        }

        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Jane Wordsmith's most-used words on Facebook, $year");
        $this->assertEqual($result->text, "How to describe $year? Jane Wordsmith used <strong>&#8220;arrays&#8221; "
            . "4 times</strong> on Facebook this year. That's more than any other word &mdash; "
            . "followed by &#8220;creating&#8221; and &#8220;things.&#8221; Sound about right?");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertEqual(3, count($data['bar_chart']['rows']));

        $result->id=2;
        $this->dumpRenderedInsight($result, $this->instance, "Several Words, Facebook");
    }

    public function testOneWordTwitter() {
        $builders = array();
        for ($i=0; $i<5; $i++) {
            $builders[] = $this->generatePost("Singularity, singularity, singularity.", $i);
        }

        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@bookworm's most-used word on Twitter, $year");
        $this->assertEqual($result->text, "Would you say it's been a &#8220;singularity&#8221; year? @bookworm might. "
            . "@bookworm mentioned <strong>&#8220;singularity&#8221; 12 times</strong> on Twitter in $year. That's "
            . "more than any other word this year.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertEqual(1, count($data['bar_chart']['rows']));

        $result->id=3;
        $this->dumpRenderedInsight($result, $this->instance, "Several Words, Twitter");
    }

    public function testOneWordFacebook() {
        $builders = array();
        for ($i=0; $i<5; $i++) {
            $builders[] = $this->generatePost("Loneliness, loneliness, loneliness", $i);
        }

        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@bookworm's most-used word on Twitter, $year");
        $this->assertEqual($result->text, "Would you say it's been a &#8220;loneliness&#8221; year? @bookworm might. "
            . "@bookworm mentioned <strong>&#8220;loneliness&#8221; 12 times</strong> on Twitter in $year. That's "
            . "more than any other word this year.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertEqual(1, count($data['bar_chart']['rows']));

        $result->id=4;
        $this->dumpRenderedInsight($result, $this->instance, "Several Words, Twitter");
    }

    public function testOfHashtagExclusion() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost("I have so many hashtags. #blessed", $i);
            $builders[] = $this->generatePost("My kid is the cutest. #kidpost helps spread the cute. ", $i);
        }

        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@bookworm's most-used words on Twitter, 2014");
        $this->assertEqual($result->text, "Would you say it's been a &#8220;many&#8221; year? @bookworm might. "
            . "@bookworm mentioned <strong>&#8220;many&#8221; 5 times</strong> on Twitter in 2014. That's more than "
            . "any other word this year &mdash; followed by &#8220;hashtags,&#8221; &#8220;spread,&#8221; "
            . "&#8220;kid,&#8221; and &#8220;helps.&#8221;");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertEqual(7, count($data['bar_chart']['rows']));

        $result->id=5;
        $this->dumpRenderedInsight($result, $this->instance, "Look ma, no hashtags!");
    }

    public function testNoWords() {
        $insight_plugin = new EOYTopWordsInsight();
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;

        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNull($result);
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
