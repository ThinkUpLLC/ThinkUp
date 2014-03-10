<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpInsightUnitTestCase.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 * ThinkUp Insight Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpInsightUnitTestCase extends ThinkUpUnitTestCase {

    /**
     * Set up necessary owner, instance, user, and owner_instance data to see fully-rendered insight markup
     * on debug.
     * @param Instance $instance Must have id, network, and network_username set
     * @return arr FixtureBuilders
     */
    protected function setUpPublicInsight(Instance $instance) {
        if (!isset($instance->network_user_id)) {
            $instance->network_user_id = '1001';
        }

        $builders = array();

        //Owner
        $pwd = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', 'salt');
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd, 'pwd_salt'=>'salt'));

        //Public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>$instance->id,
        'network_user_id'=>$instance->network_user_id, 'network_username'=>$instance->network_username ,
        'network'=>$instance->network, 'network_viewer_id'=>'10', 'crawler_last_run'=>'1988-01-20 12:00:00',
        'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11, 'posts_per_week'=>77));

        //Owner instance
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => $instance->id, 'owner_id'=>1) );

        //User
        $builders[] = FixtureBuilder::build('users', array('user_id' => $instance->network_user_id,
            'network'=>$instance->network) );

        return $builders;
    }

    /**
     * Get fully-rendered email markup for this insight.
     * @param  Insight $insight Test insight to render in email HTML.
     * @return str Insight email HTML with this insight
     */
    protected function getRenderedInsightInEmail(Insight $insight) {
        $view = new ViewManager();
        $view->caching=false;
        $view->assign('insights', array($insight));
        $view->assign('application_url', Utils::getApplicationURL());
        $view->assign('header_text', 'Test header text');
        $view->assign('unsub_url', Utils::getApplicationURL().'account/index.php?m=manage#instances');
        $view->assign('weekly_or_daily', 'Daily');
        $email_insight = $view->fetch(THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/_email.insights_html.tpl');
        return $email_insight;
    }
    /**
     * Get a variably long list of posts for insight tests.
     * @param str  $network
     * @param int  $min: Smallest number of posts returned
     * @param int  $max: Largest number of posts returned
     * @param bool $serialize: Needs to be optional for insights that have multiple related data elements
     * @return str Serialized related_data or unserialized array of posts
     */
    protected function getRelatedDataListOfPosts($network='twitter',$min=1,$max=6,$serialize=true) {
        $tweets = array();
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "435522083305836544",
            "author_user_id" => "100127476",
            "author_username" => "thinkup",
            "author_fullname" => "ThinkUp",
            "author_avatar" => "http://pbs.twimg.com/profile_images/2600723193/rvi01vw1b4mtq8gudcs6_normal.png",
            "author_follower_count" => null,
            "post_text" => "Entrepreneur named us a Startup to Watch in 2014. Officially we're skeptical of these ".
                "lists, but thanks @EntMagazine! http://t.co/Euiv1aMgVD",
            "is_protected" => null,
            "source" => "Twitter for Mac",
            "location" => "New York, NY",
            "place" => null,
            "place_id" => null,
            "geo" => null,
            "pub_date" => "2014-02-17 21:12:10",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 1,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 0,
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 2,
            "retweet_count_api" => 2,
            "favlike_count_cache" => 3,
            "author" => null,
            "links" => array(new Link(array(
                "id" => 1510,
                "url" => "http://t.co/Euiv1aMgVD",
                "expanded_url" => null,
                "title" => null,
                "description" => null,
                "image_src" => null,
                "caption" => null,
                "post_key" => 6739,
                "error" => null,
                "container_post" => null,
                "other" => array()
            ))),
            "favorited" => null,
            "all_retweets" => 2,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2014-02-17 16:12:10"
        ));
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "174270132116406274",
            "author_user_id" => "5504",
            "author_username" => "capndesign",
            "author_fullname" => "Matt Jacobs",
            "author_avatar" => "http://pbs.twimg.com/profile_images/14177592/twitter_normal.jpg",
            "author_follower_count" => null,
            "post_text" => "The one and only @zefrank wants to bring back The Show. Help him fund it. I'll give you a ".
                "hug. http://t.co/tFdZbL4Y",
            "is_protected" => null,
            "source" => "Tweet Button",
            "location" => "Brooklyn, NY, USA",
            "place" => null,
            "place_id" => null,
            "geo" => "40.65,-73.95",
            "pub_date" => "2012-02-27 23:10:11",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 1,
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 0,
            "retweet_count_api" => 7,
            "favlike_count_cache" => 3,
            "author" => null,
            "links" => array(new Link(array(
                "id" => "539",
                "url" => "http://t.co/tFdZbL4Y",
                "expanded_url" => "http://www.kickstarter.com/projects/zefrank/a-show-with-ze-frank",
                "title" => "A Show with Ze Frank by Ze Frank — Kickstarter",
                "description" => "Ze Frank is raising funds for A Show with Ze Frank on Kickstarter!\n\nIn 2006 I ".
                    "created a show called \"The Show with Ze Frank.\" With your help I'd like to start a new show. ".
                    "Same, same... but different.",
                "image_src" => "",
                "caption" => "",
                "post_key" => "2171",
                "error" => "",
                "container_post" => "",
                "other" => array()
            ))),
            "favorited" => null,
            "all_retweets" => 7,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2012-02-27 18:10:11",
        ));
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "438739700409712640",
            "author_user_id" => "5504",
            "author_username" => "capndesign",
            "author_fullname" => "Matt Jacobs",
            "author_avatar" => "http://pbs.twimg.com/profile_images/14177592/twitter_normal.jpg",
            "author_follower_count" => null,
            "post_text" => "Sneckdown (noun): Accumulation of snow on the street that reveals space that cars ".
                "don’t use. via @backspace http://t.co/VErShzyTLd",
            "is_protected" => null,
            "source" => "Buffer",
            "location" => "Brooklyn, NY",
            "place" => null,
            "place_id" => null,
            "geo" => null,
            "pub_date" => "2014-02-26 18:17:50",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 0,
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 3,
            "retweet_count_api" => 3,
            "favlike_count_cache" => 3,
            "author" => null,
            "links" => array(new Link(array(
                "id" => "2263",
                "url" => "http://t.co/VErShzyTLd",
                "expanded_url" => null,
                "title" => null,
                "description" => null,
                "image_src" => null,
                "caption" => null,
                "post_key" => "13784",
                "error" => null,
                "container_post" => null,
                "other" => array()
            ))),
            "favorited" => null,
            "all_retweets" => 3,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2014-02-26 13:17:50"
        ));
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "425973984883376128",
            "author_user_id" => "100127476",
            "author_username" => "thinkup",
            "author_fullname" => "ThinkUp",
            "author_avatar" => "http://pbs.twimg.com/profile_images/2600723193/rvi01vw1b4mtq8gudcs6_normal.png",
            "author_follower_count" => null,
            "post_text" => "RT @jamesluscombe: Really loving @thinkup so far and it's only been 6 hrs. ".
                "http://t.co/LJX175ezAg",
            "is_protected" => null,
            "source" => "TweetDeck",
            "location" => "New York, NY",
            "place" => null,
            "place_id" => null,
            "geo" => null,
            "pub_date" => "2014-01-22 12:51:26",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => "425888371366703105",
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 0,
            "in_rt_of_user_id" => "5812192",
            "retweet_count_cache" => 0,
            "retweet_count_api" => 0,
            "favlike_count_cache" => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2014-01-22 07:51:26"
        ));
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "439426788683689984",
            "author_user_id" => "2236986848",
            "author_username" => "dadkellan",
            "author_fullname" => "Kellan",
            "author_avatar" => "http://abs.twimg.com/sticky/default_profile_images/default_profile_5_normal.png",
            "author_follower_count" => null,
            "post_text" => "Dad being funny http://t.co/OdVVzWV6jl",
            "is_protected" => 1,
            "source" => "iOS",
            "location" => null,
            "place" => null,
            "place_id" => null,
            "geo" => null,
            "pub_date" => "2014-02-28 15:48:05",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 0,
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 0,
            "retweet_count_api" => 0,
            "favlike_count_cache" => 5,
            "author" => null,
            "links" => array(new Link(array(
                "id" => "2300",
                "url" => "http://t.co/OdVVzWV6jl",
                "expanded_url" => null,
                "title" => null,
                "description" => null,
                "image_src" => null,
                "caption" => null,
                "post_key" => "13955",
                "error" => null,
                "container_post" => null,
                "other" => array()
            ))),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2014-02-28 20:48:05"
        ));
        $tweets[] = new Post(array(
            "id" => 1,
            "post_id" => "439422552164007936",
            "author_user_id" => "1900720267",
            "author_username" => "MakingOfs",
            "author_fullname" => "Behind the Scenes",
            "author_avatar" => "http://pbs.twimg.com/profile_images/378800000771067498/".
                "7229e32203e92559e972f13dd949b138_normal.jpeg",
            "author_follower_count" => null,
            "post_text" => "Yoda, Stuart Freeborn and Irvin Kershner http://t.co/tMk0L1GoSX",
            "is_protected" => null,
            "source" => "Buffer",
            "location" => null,
            "place" => null,
            "place_id" => null,
            "geo" => null,
            "pub_date" => "2014-02-28 15:31:15",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "twitter",
            "is_geo_encoded" => 0,
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 1,
            "retweet_count_api" => 101,
            "favlike_count_cache" => 145,
            "author" => null,
            "links" => array(new Link(array(
                "id" => "2299",
                "url" => "http://t.co/tMk0L1GoSX",
                "expanded_url" => null,
                "title" => null,
                "description" => null,
                "image_src" => null,
                "caption" => null,
                "post_key" => "13948",
                "error" => null,
                "container_post" => null,
                "other" => array()
            ))),
            "favorited" => null,
            "all_retweets" => 101,
            "rt_threshold" => 1,
            "permalink" => null,
            "adj_pub_date" => "2014-02-28 20:31:15",
        ));

        // Facebook posts!
        $fb_posts = array();
        $fb_posts[] = new Post(array(
            "id" => 1,
            "post_id" => "10151443363283490",
            "author_user_id" => "502783489",
            "author_username" => "Matt Jacobs",
            "author_fullname" => "Matt Jacobs",
            "author_avatar" => "https://graph.facebook.com/502783489/picture",
            "author_follower_count" => null,
            "post_text" => "Out of my comfort zone. http://t.co/AbjqqbHMOv",
            "is_protected" => 1,
            "source" => null,
            "location" => "New York, NY, USA",
            "place" => null,
            "place_id" => null,
            "geo" => "40.7143528,-74.0059731",
            "pub_date" => "2013-02-28 22:53:38",
            "in_reply_to_user_id" => null,
            "in_reply_to_post_id" => null,
            "reply_count_cache" => 0,
            "is_reply_by_friend" => null,
            "in_retweet_of_post_id" => null,
            "old_retweet_count_cache" => 0,
            "is_retweet_by_friend" => null,
            "reply_retweet_distance" => 0,
            "network" => "facebook",
            "is_geo_encoded" => "1",
            "in_rt_of_user_id" => null,
            "retweet_count_cache" => 0,
            "retweet_count_api" => 0,
            "favlike_count_cache" => 2,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            "permalink" => null,
            "adj_pub_date" => "2013-02-28 17:53:38",
            "place_obj" => new Place(array(
                "id" => "10713",
                "place_id" => null,
                "place_type" => null,
                "name" => null,
                "full_name" => null,
                "country_code" => null,
                "country" => null,
                "network" => "facebook",
                "longlat" => null,
                "bounding_box" => null,
                "icon" => null,
                "map_image" => null,
            ))
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152091246453490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'Earlier. The sky. http://t.co/qzMfGVVsov',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-05 01:34:23',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 2,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152087870783490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'I am officially raising the #ShackFlag for 12:30pm today. Join me for a #snowburger. '.
                'http://t.co/jINn08Y03',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-03 15:08:46',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152083771718490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'Pillowy mounds of mashed potatoes http://t.co/RuNVJM1RbU',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-01 20:51:29',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152082278478490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'has uploaded a photo to Flickr',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-01 07:27:18',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152082263408490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'Happy new year. I\'m in bed. Good work, everybody. http://t.co/OGcP85XPR5',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-01 07:16:24',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 1,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152081964948490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'Like a Cucumber, But Fleshier #freepornmovietitles',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2014-01-01 03:59:13',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152076585498490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'It feels like the Bears are going to need 10 turnovers to win this game.',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2013-12-29 22:27:31',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152076528568490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'BEAR DOWN! http://t.co/3TX9ieEtRu',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2013-12-29 21:54:42',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 1,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));
        $fb_posts[] = new Post(array(
            'id' => 1,
            'post_id' => '10152075876848490',
            'author_user_id' => '502783489',
            'author_username' => 'Matt Jacobs',
            'author_fullname' => 'Matt Jacobs',
            'author_avatar' => 'https://graph.facebook.com/502783489/picture',
            'author_follower_count' => 0,
            'post_text' => 'Bruno Mars may be the best wedding band of all time.',
            'is_protected' => 1,
            'source' => null,
            'location' => 'New York, NY, USA',
            'place' => NULL,
            'place_id' => NULL,
            'geo' => '40.7143528,-74.0059731',
            'pub_date' => '2013-12-29 16:03:58',
            'in_reply_to_user_id' => NULL,
            'in_reply_to_post_id' => NULL,
            'reply_count_cache' => 0,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => NULL,
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'facebook',
            'is_geo_encoded' => 1,
            'in_rt_of_user_id' => NULL,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0,
            "author" => null,
            "links" => array(),
            "favorited" => null,
            "all_retweets" => 0,
            "rt_threshold" => 0,
            'permalink' => NULL
        ));

        if ($network == 'facebook') {
            $posts = $fb_posts;
        } else {
            $posts = $tweets;
        }
        if ($serialize) {
            $related_data = array();
            $related_data["posts"] = array_slice($posts, 0, rand($min,$max));
            return serialize($related_data);
        } else {
            return array_slice($posts, 0, rand($min,$max));
        }
    }

    /**
     * Get serialized list of users for insight tests.
     * @param str  $network
     * @param int  $min Smallest number of users returned
     * @param int  $max Largest number of users returned
     * @param bool $serialize Needs to be optional for insights that have multiple related data elements
     * @return str Serialized related_data or unserialized array of users
     */
    protected function getRelatedDataListOfUsers($network='twitter',$min=1,$max=6,$serialize=true) {
        $tw_users = array();
        $tw_users[] = new User(array(
            "id" => 2756,
            "username" => "Gjetting",
            "full_name" => "Jon Angelo Gjetting",
            "avatar" => "http://pbs.twimg.com/profile_images/436950275908055040/0Z8Pa9fD_normal.jpeg",
            "location" => null,
            "description" => "Creative Director thriving at the intersection of creativity, strategy, and technology.",
            "url" => "http://t.co/kTimxNUWE7",
            "is_verified" => 0,
            "is_protected" => 0,
            "follower_count" => 3682,
            "friend_count" => 1741,
            "favorites_count" => 10257,
            "post_count" => 5077,
            "last_updated" => "2014-03-03 17:56:37",
            "found_in" => "Follows",
            "last_post" => "0000-00-00 00:00:00",
            "joined" => "2009-07-25 10:36:19",
            "last_post_id" => null,
            "network" => "twitter",
            "user_id" => "60031833",
            "other" => array(
                "likelihood_of_follow" => "47.2841",
                "avg_tweets_per_day" => "3.02"
            )
        ), 'Test Insert');
        $tw_users[] = new User(array(
            "id" => 2760,
            "username" => "blakesamic",
            "full_name" => "Blake Samic",
            "avatar" => "http://pbs.twimg.com/profile_images/2218712648/6625198569_2f790a5a46_o_normal.jpeg",
            "location" => "San Francisco",
            "description" => "Product and Partnerships at @Shoutlet. Fascinated by creative people, product design, ".
                "tech, music, & travel.",
            "url" => "http://t.co/iepIgg4Ia6",
            "is_verified" => 0,
            "is_protected" => 0,
            "follower_count" => 1996,
            "friend_count" => 963,
            "favorites_count" => 1125,
            "post_count" => 6550,
            "last_updated" => "2014-03-03 17:56:37",
            "found_in" => "Follows",
            "last_post" => "0000-00-00 00:00:00",
            "joined" => "2007-11-06 09:03:17",
            "last_post_id" => null,
            "network" => "twitter",
            "user_id" => "9991942",
            "other" => array(
                "likelihood_of_follow" => "48.2465",
                "avg_tweets_per_day" => "2.84",
            )
        ), 'Test Insert');
        $tw_users[] = new User(array(
            "id" => 49,
            "username" => "mulegirl",
            "full_name" => "Erika Hall",
            "avatar" => "http://pbs.twimg.com/profile_images/418628800293793792/FO4GD-Gj_normal.jpeg",
            "location" => "San Francisco",
            "description" => "Sneckdown (noun): Accumulation of snow on the street that reveals space that cars ".
                "don’t use. via @backspace http://t.co/VErShzyTLd",
            "url" => "http://t.co/wgppL6J8lV",
            "is_verified" => 0,
            "is_protected" => 0,
            "follower_count" => 9453,
            "friend_count" => 1204,
            "favorites_count" => 13689,
            "post_count" => 23538,
            "last_updated" => "2014-02-28 18:10:20",
            "found_in" => "replies",
            "last_post" => "0000-00-00 00:00:00",
            "joined" => "2006-07-18 19:12:35",
            "last_post_id" => null,
            "network" => "twitter",
            "user_id" => "2391",
            "other" => array()
        ), 'Test Insert');

        // Now some Facebook users
        $fb_users = array();
        $fb_users[] = new User(array(
          'id' => 1312,
          'user_id' => '101531',
          'user_name' => 'Jonathan Wegener',
          'full_name' => 'Jonathan Wegener',
          'avatar' => 'https://graph.facebook.com/101531/picture',
          'location' => '',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:13',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:13',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1204,
          'user_id' => '502783489',
          'user_name' => 'Matt Jacobs',
          'full_name' => 'Matt Jacobs',
          'avatar' => 'https://graph.facebook.com/502783489/picture',
          'location' => 'New York, New York',
          'description' => 'Hi.',
          'url' => 'http://capndesign.com',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:17:46',
          'found_in' => 'Post stream',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:17:46',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1317,
          'user_id' => '404729',
          'user_name' => 'Youngna Park',
          'full_name' => 'Youngna Park',
          'avatar' => 'https://graph.facebook.com/404729/picture',
          'location' => 'Brooklyn, New York',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:15',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:15',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1321,
          'user_id' => '512599',
          'user_name' => 'Chris Alden',
          'full_name' => 'Chris Alden',
          'avatar' => 'https://graph.facebook.com/512599/picture',
          'location' => 'San Francisco, California',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:16',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:16',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1323,
          'user_id' => '706509',
          'user_name' => 'James Kenji Lopez-Alt',
          'full_name' => 'James Kenji Lopez-Alt',
          'avatar' => 'https://graph.facebook.com/706509/picture',
          'location' => 'New York, New York',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:17',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:17',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1332,
          'user_id' => '808437',
          'user_name' => 'Robyn Lee',
          'full_name' => 'Robyn Lee',
          'avatar' => 'https://graph.facebook.com/808437/picture',
          'location' => 'Brooklyn, New York',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:20',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:20',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1344,
          'user_id' => '1008248',
          'user_name' => 'Finn Smith',
          'full_name' => 'Finn Smith',
          'avatar' => 'https://graph.facebook.com/1008248/picture',
          'location' => '',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:25',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:25',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1346,
          'user_id' => '1013094',
          'user_name' => 'Jesse Chan-Norris',
          'full_name' => 'Jesse Chan-Norris',
          'avatar' => 'https://graph.facebook.com/1013094/picture',
          'location' => '',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:25',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:25',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1352,
          'user_id' => '1404613',
          'user_name' => 'Erin Zimmer',
          'full_name' => 'Erin Zimmer',
          'avatar' => 'https://graph.facebook.com/1404613/picture',
          'location' => 'Brooklyn, New York',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:28',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:28',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');

        $fb_users[] = new User(array(
          'id' => 1353,
          'user_id' => '1906147',
          'user_name' => 'Abe Hassan',
          'full_name' => 'Abe Hassan',
          'avatar' => 'https://graph.facebook.com/1906147/picture',
          'location' => '',
          'description' => '',
          'url' => '',
          'is_verified' => 0,
          'is_protected' => 1,
          'follower_count' => 0,
          'friend_count' => 0,
          'post_count' => 0,
          'last_updated' => '2014-03-03 17:14:28',
          'found_in' => '',
          'last_post' => '0000-00-00 00:00:00',
          'joined' => '2014-03-03 17:14:28',
          'last_post_id' => '',
          'network' => 'facebook',
          'favorites_count' => null,
          'other' => array()
        ), 'Test Insert');
        if ($network == 'facebook') {
            $users = $fb_users;
        } else {
            $users = $tw_users;
        }
        if ($serialize) {
            $related_data = array();
            $related_data["people"] = array_slice($users, 0, rand($min,$max));
            return serialize($related_data);
        } else {
            return array_slice($users, 0, rand($min,$max));
        }
    }
}
