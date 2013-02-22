<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfRetweetDetector.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 */
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.RetweetDetector.php';

class TestOfRetweetDetector extends ThinkUpBasicUnitTestCase {
    var $logger;
     
    public function setUp() {
        $this->logger = Logger::getInstance();
    }

    public function tearDown() {
        $this->logger->close();
    }

    public function testIsRetweet() {
        $owner = 'ginatrapani';

        // Test all variations of the RT @ username format
        $start_with_colon =
        "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $no_start_no_colon =
        "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight";
        $start_with_colon_spaces =
        "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $start_with_colon_cutoff =
        "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lower_case =
        "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";

        $this->assertTrue(RetweetDetector::isRetweet($start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($no_start_no_colon, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($start_with_colon_spaces, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($start_with_colon_cutoff, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($lower_case, $owner));

        // Test all variations of the MT @ username format
        $mt_start_with_colon =
        "MT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_start_with_colon =
        "Agreed: MT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight";
        $mt_start_with_colon_spaces =
        "MT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_start_with_colon_cutoff =
        "MT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $mt_lower_case =
        "mt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";

        $this->assertTrue(RetweetDetector::isRetweet($mt_start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($mt_start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($mt_start_with_colon_spaces, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($mt_start_with_colon_cutoff, $owner));
        $this->assertTrue(RetweetDetector::isRetweet($mt_lower_case, $owner));

        // Test the quoted retweet style
        $quoted_retweet = '“@ginatrapani: how to do (almost) everything in Google Buzz, including turn it off”';
        $this->assertTrue(RetweetDetector::isRetweet($quoted_retweet, $owner));
    }

    public function testIsStandardRetweet() {
        $owner = 'ginatrapani';

        // Test all variations of the RT @ username format
        $start_with_colon =
        "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $no_start_no_colon =
        "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight";
        $start_with_colon_spaces =
        "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $start_with_colon_cutoff =
        "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lower_case =
        "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";

        $this->assertTrue(RetweetDetector::isStandardRetweet($start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isStandardRetweet($no_start_no_colon, $owner));
        $this->assertTrue(RetweetDetector::isStandardRetweet($start_with_colon_spaces, $owner));
        $this->assertTrue(RetweetDetector::isStandardRetweet($start_with_colon_cutoff, $owner));
        $this->assertTrue(RetweetDetector::isStandardRetweet($lower_case, $owner));
    }

    public function testIsMTRetweet() {
        $owner = 'ginatrapani';

        // Test all variations of the MT @ username format
        $mt_start_with_colon =
        "MT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_start_with_colon =
        "Agreed: MT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight";
        $mt_start_with_colon_spaces =
        "MT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_start_with_colon_cutoff =
        "MT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $mt_lower_case =
        "mt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";

        $this->assertTrue(RetweetDetector::isMTRetweet($mt_start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isMTRetweet($mt_start_with_colon, $owner));
        $this->assertTrue(RetweetDetector::isMTRetweet($mt_start_with_colon_spaces, $owner));
        $this->assertTrue(RetweetDetector::isMTRetweet($mt_start_with_colon_cutoff, $owner));
        $this->assertTrue(RetweetDetector::isMTRetweet($mt_lower_case, $owner));
    }

    public function testIsQuotedRetweet() {
        $owner = 'ginatrapani';

        // Test the quoted retweet style
        $quoted_retweet = '“@ginatrapani: how to do (almost) everything in Google Buzz, including turn it off”';
        $this->assertTrue(RetweetDetector::isQuotedRetweet($quoted_retweet, $owner));
    }

    public function testDetectRetweets() {
        $recent_tweets = array(
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'in_rt_of_user_id' => '', 'old_retweet_count_cache' => 0,
        'post_id'=>9021481076, 'is_protected'=>0, 'place_id'=>null, 'favlike_count_cache'=>0,
        'post_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation', 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 
        'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'in_rt_of_user_id' => '', 'old_retweet_count_cache' => 0,
        'post_id'=>9020176425, 'is_protected'=>0, 'place_id'=>null,'favlike_count_cache'=>0,
        'post_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least'.
        ' they're iterating quickly and openly.", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'in_rt_of_user_id' => '', 'old_retweet_count_cache' => 0,
        'post_id'=>9031523906, 'is_protected'=>0, 'place_id'=>null,'favlike_count_cache'=>0,
        'post_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx '.
        '@voiceofsandiego, @dagnysalas, & @samuelhodgson", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'in_rt_of_user_id' => '', 'old_retweet_count_cache' => 0,
        'post_id'=>8925077246, 'is_protected'=>0, 'place_id'=>null,'favlike_count_cache'=>0,
        'post_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)));

        // Test standard format retweet
        $start_with_colon =
        "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $no_start_no_colon =
        "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight '.
        'people, mid-conversation";
        $start_with_colon_spaces =
        "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $start_with_colon_cutoff =
        "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lower_case =
        "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $non_existent = "rt @ginatrapani this is a non-existent tweet";

        $this->assertTrue(RetweetDetector::detectOriginalTweet($start_with_colon, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($no_start_no_colon, $recent_tweets) == 9021481076);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($start_with_colon_spaces, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($start_with_colon_cutoff, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($lower_case, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($non_existent, $recent_tweets) === false);

        // Test MT format retweet
        $mt_start_with_colon =
        "MT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_no_start_no_colon =
        "Agreed: MT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight '.
        'people, mid-conversation";
        $mt_start_with_colon_spaces =
        "MT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $mt_start_with_colon_cutoff =
        "MT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $mt_lower_case =
        "mt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $mt_non_existent = "mt @ginatrapani this is a non-existent tweet";

        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_start_with_colon, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_no_start_no_colon, $recent_tweets) == 9021481076);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_start_with_colon_spaces, $recent_tweets)
        == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_start_with_colon_cutoff, $recent_tweets)
        == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_lower_case, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($mt_non_existent, $recent_tweets) === false);

        // Test quoted retweet
        $quoted_retweet = '“@ginatrapani: how to do (almost) everything in Google Buzz, including turn it off”';
        $this->assertTrue(RetweetDetector::detectOriginalTweet($quoted_retweet, $recent_tweets) == 8925077246);
    }
}
