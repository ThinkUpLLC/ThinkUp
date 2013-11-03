<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIEndpoint.php';

class TestOfTwitterAPIAccessorOAuth extends ThinkUpBasicUnitTestCase {

    var $test_data_path = 'webapp/plugins/twitter/tests/data/';

    public function testConstructor() {
        $this->debug(__METHOD__);
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);
        $this->assertNotNull($api);
        $this->assertIsA($api, 'TwitterAPIAccessorOAuth');
    }

    public function testVerifyCredentialsSuccess() {
        $this->debug(__METHOD__);
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //Successful verify
        $api->to->setDataPathFolder('testoftwitterapiaccessoroauth/testverifycredentials1/');
        $result = $api->verifyCredentials();
        $this->assertNotNull($result);
        $this->assertIsA($result, 'array');
        $this->assertEqual($result['user_name'], 'ginatrapani');
        $this->assertEqual($result['user_id'], '930061');
    }

    public function testVerifyCredentialsEmptyResponse() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //JSON is an empty string
        $api->to->setDataPathFolder('testoftwitterapiaccessoroauth/testverifycredentials2/');
        $this->expectException('JSONDecoderException');
        $result = $api->verifyCredentials();
        $this->assertNull($result);
    }

    public function testVerifyCredentialsMalformedJSON() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //Malformed JSON
        $api->to->setDataPathFolder('testoftwitterapiaccessoroauth/testverifycredentials3/');
        $this->expectException('JSONDecoderException');
        $result = $api->verifyCredentials();
        $this->assertNull($result);
    }

    public function testVerifyCredentials404Response() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //Malformed JSON
        $api->to->setDataPathFolder('testoftwitterapiaccessoroauth/testverifycredentials4/');
        $this->expectException('APIErrorException');
        $result = $api->verifyCredentials();
        $this->assertNull($result);
    }

    public function testParseJSONTweet() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/tweet.json');

        $results = $api->parseJSONTweet($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["post_text"],
        "Along with our new #Twitterbird, we've also updated our Display Guidelines: https://t.co/Ed4omjYs  ^JC");
        $this->assertEqual($results["post_id"], "210462857140252672");
        $this->assertEqual($results["user_id"], "6253282");
        $this->assertEqual($results["user_name"], "twitterapi");
        $this->assertEqual($results["author_fullname"], "Twitter API");
        $this->assertEqual($results["location"], "San Francisco, CA");
        $this->assertEqual($results["is_protected"], false);
        $this->assertEqual($results["favlike_count_cache"], 53);
    }

    public function testParseJSONTweetPrivate() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/tweet_private.json');

        $results = $api->parseJSONTweet($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["post_text"],
        "Along with our new #Twitterbird, we've also updated our Display Guidelines: https://t.co/Ed4omjYs  ^JC");
        $this->assertEqual($results["post_id"], "210462857140252672");
        $this->assertEqual($results["user_id"], "6253282");
        $this->assertEqual($results["user_name"], "twitterapi");
        $this->assertEqual($results["author_fullname"], "Twitter API");
        $this->assertEqual($results["location"], "San Francisco, CA");
        $this->assertEqual($results["is_protected"], true);
    }

    public function testParseJSONTweetWithGeo() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/tweet_geo.json');

        $results = $api->parseJSONTweet($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["post_text"],
        "If Jake Knotts weren't a goddamn coward, he'd visit Uday Singh Taunque's grave in Arlington & start talking ".
        "about ragheads. Go for it, Jake.");
        $this->assertEqual($results["post_id"], "15680112737");
        $this->assertEqual($results["user_id"], "36823");
        $this->assertEqual($results["user_name"], "anildash");
        $this->assertEqual($results["author_fullname"], "Anil Dash");
        $this->assertEqual($results["location"], "NYC: 40.739069,-73.987082");
        $this->assertEqual($results["place"], "Stuyvesant Town, New York");
        $this->assertEqual($results["geo"], "40.73410845 -73.97885982");
        $this->assertEqual($results["retweet_count_api"], 11);
    }

    public function testParseJSONTweetRetweet() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/retweet.json');

        $results = $api->parseJSONTweet($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["post_text"],
        "RT @errolmorris: Twitter never suggests that you might enjoy following no one. (Possibly an oversight.)");
        $this->assertEqual($results["post_id"], "300464193944055808");
        $this->assertEqual($results["user_id"], "2768241");
        $this->assertEqual($results["user_name"], "amygdala");
        $this->assertEqual($results["author_fullname"], "amy jo");
        $this->assertEqual($results["in_retweet_of_post_id"], "297179577304875011");
        $this->assertEqual($results["in_rt_of_user_id"], "14248315");
        $this->assertEqual($results["retweet_count_api"], 0);
        $this->assertEqual($results["retweeted_post"]["content"]["post_id"], "297179577304875011");
        $this->assertEqual($results["retweeted_post"]["content"]["post_text"],
        "Twitter never suggests that you might enjoy following no one. (Possibly an oversight.)");
        $this->assertEqual($results["retweeted_post"]["content"]["retweet_count_api"], 114);
    }

    public function testParseJSONTweets() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/tweets.json');

        $results = $api->parseJSONTweets($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results[0]["post_text"],
        "A friendly reminder that this t.co length change will start today, and be in full effect by 2/20 ".
        "https://t.co/nqvYxZAk   ^JC");
        $this->assertEqual($results[0]["post_id"], "299212472030748672");
        $this->assertEqual($results[0]["user_id"], "6253282");
        $this->assertEqual($results[0]["user_name"], "twitterapi");
        $this->assertEqual($results[0]["author_fullname"], "Twitter API");
        $this->assertEqual($results[0]["location"], "San Francisco, CA");

        // assert reply is processed correctly
        $this->assertEqual($results[5]["post_text"],
        "@foetusite minutes after tweeting, a developer was kind enough to submit one: https://t.co/8qkLwLuW ^TS");
        $this->assertEqual($results[5]["post_id"], "292042417505443840");
        $this->assertEqual($results[5]["in_reply_to_user_id"], "93378131");
        $this->assertEqual($results[5]["in_reply_to_post_id"], "292038019752538112");
    }

    public function testParseJSONUser() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/user.json');

        $results = $api->parseJSONUser($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["user_id"], "795649");
        $this->assertEqual($results["user_name"], "rsarver");
        $this->assertEqual($results["full_name"], "Ryan Sarver");
        $this->assertEqual($results["is_verified"], 1);
    }

    public function testParseJSONUsers() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/userslist.json');

        $results = $api->parseJSONUsers($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results[0]["user_id"], "14232986");
        $this->assertEqual($results[0]["user_name"], "robbsala");
        $this->assertEqual($results[0]["full_name"], "robbsala");

        //@TODO Test No cursor
        //$data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/users.json');
        //$results = $api->parseJSONUser($data);
    }

    public function testParseJSONIDs() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/idslist.json');

        $results = $api->parseJSONIDs($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results[0]["id"], 14232986);
        $this->assertEqual($results[1]["id"], 12428572);
        $this->assertEqual($results[2]["id"], 657693);

        //@TODO Test No cursor
        //$data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/ids.json');
        //$results = $api->parseJSONIDs($data);
    }

    public function testParseJSONRelationship() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/relationship.json');

        $results = $api->parseJSONRelationship($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["source_follows_target"], false);
        $this->assertEqual($results["target_follows_source"], false);
    }

    public function testParseJSONLists() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/listslist.json');

        $results = $api->parseJSONLists($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results[0]["group_id"], 31574522);
        $this->assertEqual($results[0]["group_name"], "@lokkomotion/verktyg");
        $this->assertEqual($results[0]["owner_name"], "lokkomotion");

        //@TODO Test No cursor
        //$data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/lists.json');
        //$results = $api->parseJSONLists($data);
    }

    public function testParseJSONError() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/errors.json');

        $results = $api->parseJSONError($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["error"], "Sorry, that page does not exist");
    }

    public function testParseJSONTweetsFromSearch() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/search_tweets.json');
        $this->debug(Utils::varDumpToString($data));
        $results = $api->parseJSONTweetsFromSearch($data);
        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual(sizeof($results),2);
        $this->assertEqual($results[0]["post_text"], "Resumen del #MWC2013 http://t.co/yPMZd3eTNb");
        $this->assertEqual($results[0]["post_id"], "307436813180616704");
        $this->assertEqual($results[0]["user_id"], "2485041");
        $this->assertEqual($results[0]["user_name"], "GinaTost");
        $this->assertEqual($results[0]["author_fullname"], "Gina Tost");
        $this->assertEqual($results[0]["location"], "Barcelona");
        $this->assertEqual($results[1]["pub_date"], "2013-03-01 10:26:24");
        $this->assertEqual($results[1]["follower_count"], 506);
        $this->assertEqual($results[1]["post_count"], 5848);
        $this->assertEqual($results[1]["friend_count"], 713);
    }
    public function testParseJSONErrorCodeAPI() {
        $api = new TwitterAPIAccessorOAuth($oauth_access_token='111', $oauth_access_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=1234, $num_twitter_errors=5, $log=true);

        //List of users with cursor
        $data = file_get_contents(THINKUP_ROOT_PATH . $this->test_data_path.'json/error_source_user.json');

        $results = $api->parseJSONErrorCodeAPI($data);

        $this->debug(Utils::varDumpToString($results));
        $this->assertEqual($results["error"], 163);
    }
}
