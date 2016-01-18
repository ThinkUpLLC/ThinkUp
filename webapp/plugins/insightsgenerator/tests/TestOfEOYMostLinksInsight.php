<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYMostLinksInsight.php
 *
 * Copyright (c) 2014-2016 Adam Pash
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
 * Test of EOYMostLinksInsight
 *
 * Test for the EOYMostLinksInsight class.
 *
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymostlinks.php';

class TestOfEOYMostLinksInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->network_user_id = '7612345';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYMostLinksInsight();
        $this->assertIsA($insight_plugin, 'EOYMostLinksInsight' );
    }

    public function testIsIntraNetwork() {
        $insight_plugin = new EOYMostLinksInsight();

        $url = "https://twitter.com/ginatrapani/status/456469734515408897/photo/1";
        $network = 'twitter';
        $result = $insight_plugin->isIntraNetwork($url, $network);
        $this->assertTrue($result);

        $network = 'facebook';
        $result = $insight_plugin->isIntraNetwork($url, $network);
        $this->assertFalse($result);

        $network = 'facebook';
        $url = "https://twitter.com/ginatrapani/status/456469734515408897/photo/1";
        $result = $insight_plugin->isIntraNetwork($url, $network);
        $this->assertFalse($result);

        $network = 'facebook';
        $url = "https://www.facebook.com/somepage";
        $result = $insight_plugin->isIntraNetwork($url, $network);
        $this->assertTrue($result);

        $url = "https://twitter.com/ginatrapani/status/456469734515408897";
        $network = 'twitter';
        $result = $insight_plugin->isIntraNetwork($url, $network);
        $this->assertTrue($result);
    }

    public function testLinkUtilities() {
        $year = date('Y');
        $user_id = $this->instance->network_user_id;
        $counter = 12;
        $days = 0;

        // set up most links
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'retweet_count_cache' => $days,
                'reply_count_cache' => $days,
                'favlike_count_cache' => $days,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'post_text'=>'Link post http://lifehacker.com/'.$counter, 'pub_date'=>'-1d'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://lifehacker.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://lifehacker.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        // set up fewer links
        $counter = 10;
        $days = 0;
        while ($counter != 0) {
            $post_key = $counter + 1860;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'post_text'=>'Link post http://nytimes.com/'.$counter, 'pub_date'=>'-1d'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://nytimes.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://nytimes.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        $insight_plugin = new EOYMostLinksInsight();
        $post_dao = new PostMySQLDAO();
        $it_posts = $post_dao->getThisYearOfPostsWithLinksIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );
        $posts = array();
        foreach ($it_posts as $post) {
            $posts[] = $post;
        }
        $this->assertEqual(count($posts), 22);

        $domain_counts = $insight_plugin->getDomainCounts($posts);
        $this->debug(Utils::varDumpToString($domain_counts));
        $sorted_domains = array(
            0 => array('lifehacker.com' => 12),
            1 => array('nytimes.com' => 10)
        );

        $i = 0;
        foreach ($domain_counts as $domain => $count) {
            $this->assertEqual($sorted_domains[$i][$domain], $count);
            $i++;
        }

        $domain = $insight_plugin->getPopularDomain($domain_counts);
        $this->assertEqual('lifehacker.com', $domain);

        $posts = $insight_plugin->getMostPopularPostsLinkingTo($this->instance, $domain);
        $this->debug(Utils::varDumpToString($posts));
        $this->assertEqual(3, count($posts));
        $this->assertEqual($posts[0]->id, 1761);
        $this->assertEqual($posts[2]->id, 1763);
    }

    public function testTwitterNormalCase() {
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $user_id = $this->instance->network_user_id;
        $counter = 12;
        $days = 0;

        // set up most links
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'retweet_count_cache' => $days,
                'reply_count_cache' => $days,
                'favlike_count_cache' => $days,
                'post_text'=>'Link post #' . $counter . ' http://lifehacker.com/'.$counter, 'pub_date'=>'-1d'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://lifehacker.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://lifehacker.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        // set up fewer links
        $counter = 10;
        $days = 0;
        while ($counter != 0) {
            $post_key = $counter + 1860;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'post_text'=>'Link post http://nytimes.com/'.$counter, 'pub_date'=>'-1d'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://nytimes.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://nytimes.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        $posts = array();
        $insight_plugin = new EOYMostLinksInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_links', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("ICYMI: @ev's most linked-to site of $year", $result->headline);
        $this->assertEqual("What's Twitter without the tabs? In $year, @ev shared " .
            "more #content from <strong>lifehacker.com</strong> than from any other web site. " .
            "These were @ev's most popular tweets with a link to <strong>lifehacker.com</strong>.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterOneMatch() {
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $user_id = $this->instance->network_user_id;
        $counter = 1;
        $days = 0;

        // set up most links
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'retweet_count_cache' => $days,
                'reply_count_cache' => $days,
                'favlike_count_cache' => $days,
                'post_text'=>'Link post #' . $counter . ' http://lifehacker.com/'.$counter, 'pub_date'=>'-1d'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://lifehacker.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://lifehacker.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        $posts = array();
        $insight_plugin = new EOYMostLinksInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_links', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("ICYMI: @ev's most linked-to site of $year", $result->headline);
        $this->assertEqual("What's Twitter without the tabs? In $year, @ev shared " .
            "more #content from <strong>lifehacker.com</strong> than from any other web site. " .
            "This was @ev's most popular tweet with a link to <strong>lifehacker.com</strong>.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One post, Twitter");
    }

    public function testTwitterNoMatch() {
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $user_id = $this->instance->network_user_id;
        $counter = 1;
        $days = 0;

        // set up no links
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'user',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'retweet_count_cache' => $days,
                'reply_count_cache' => $days,
                'favlike_count_cache' => $days,
                'post_text'=>'Link post #' . $counter . ' http://lifehacker.com/'.$counter, 'pub_date'=>'-1d'));

            $counter--;
        }

        $posts = array();
        $insight_plugin = new EOYMostLinksInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight didn't get inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_links', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNull($result);
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $user_id = $this->instance->network_user_id;
        $counter = 12;
        $days = 0;

        // set up most links
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'Mark Zuckerberg',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'retweet_count_cache' => $days,
                'reply_count_cache' => $days,
                'favlike_count_cache' => $days,
                'post_text'=>'Link post #' . $counter . ' http://lifehacker.com/'.$counter, 'pub_date'=>'2015-10-01'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://lifehacker.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://lifehacker.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        // set up fewer links
        $counter = 10;
        $days = 0;
        while ($counter != 0) {
            $post_key = $counter + 1860;
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
                'network'=>$this->instance->network, 'author_user_id'=>$user_id, 'author_username'=>'Mark Zuckerberg',
                'in_reply_to_user_id' => NULL,
                'in_retweet_of_post_id' => NULL,
                'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
                'post_text'=>'Link post http://nytimes.com/'.$counter, 'pub_date'=>'2015-10-01'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://nytimes.com/'.$counter,
                'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'http://nytimes.com/'.$counter,
                'error'=>'', 'image_src'=>''));
            $counter--;
        }

        $posts = array();
        $insight_plugin = new EOYMostLinksInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_links', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's most-shared site of $year", $result->headline);
        $this->assertEqual("Looks like <strong>lifehacker.com</strong> owes Mark Zuckerberg a thank you. " .
            "In $year, Mark Zuckerberg directed friends to <strong>lifehacker.com</strong> more than to " .
            "any other site. Here are the posts with links to <strong>lifehacker.com</strong>.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }
}

