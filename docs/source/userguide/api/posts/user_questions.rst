User Questions
==============
Gets question posts by a user. This will return all of the posts a user has made that contain questions.

**API call type slug:** ``user_questions``

**Example Usage:** ``api/v1/post.php?type=user_questions&username=samwhoo``

==================
Required arguments
==================

* **user_id** or **username**

    Only one of these is required. They are to specify the user to gather posts for in this call.

==================
Optional Arguments
==================

* **network**

    The network to use in the call. Defaults to 'twitter'.

* **order_by**

    The column to order the results by. Defaults to chronological order ("date").

* **direction**

    The direction to order the results in. Can be either DESC or ASC. Defaults to DESC.

* **count**

    The number of results to display from this API call. Defaults to 20. Max is 200. If you supply something that is
    not a valid number, this argument will revert to its default value of 20.

* **page**

    The page of results to display for this API call. Defaults to 1.

* **include_entities**

    Whether or not to include `Tweet Entities <http://dev.twitter.com/pages/tweet_entities>`_ in the output. Defaults
    to false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

* **include_replies**

    Whether or not to include replies to this post in the output. This argument is recursive and will retrieve replies
    to replies also. Defaults to false. This argument can be set to true by making it equal to either **1**, **t** or
    **true**.

* **trim_user**

    If set to true, this flag strips the user part of the output to just the user's ID and nothing else. Defaults to
    false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

==============
Example output
==============

``api/v1/post.php?type=user_questions&username=samwhoo&count=5``::


    [
        {
            "id":61257731159490560,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"Time to hibernate while I download this massive file containing my next all-nighter. Honestly, I do love this stuff :) Masochist or what?",
            "created_at":"Fri Apr 22 02:39:15 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":1,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":234,
                "followers_count":103,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":921,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.53,
                "last_updated":"2011-04-22 13:00:10",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":60884841750732800,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":15040935,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":true,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @rhysmorgan: A-HERP-DERP. PEOPLE DYING, CHILDREN CRYING. WHAT CAN I DO? PRAY! IT WILL MAKE IT ALL GO AWAY! HERPY DERP. DERPETTY HERP.",
            "created_at":"Thu Apr 21 01:57:32 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":234,
                "followers_count":103,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":921,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.53,
                "last_updated":"2011-04-22 13:00:10",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            },
            "retweeted_status":{
                "id":60884527941296128,
                "source":"<a href=\"http://www.tweetdeck.com\" rel=\"nofollow\">TweetDeck</a>",
                "location":"Cardiff",
                "place":null,
                "geo":null,
                "in_reply_to_user_id":null,
                "is_reply_by_friend":false,
                "in_reply_to_post_id":null,
                "in_rt_of_user_id":null,
                "reply_retweet_distance":0,
                "is_retweet_by_friend":false,
                "favorited":false,
                "all_retweets":1,
                "text":"A-HERP-DERP. PEOPLE DYING, CHILDREN CRYING. WHAT CAN I DO? PRAY! IT WILL MAKE IT ALL GO AWAY! HERPY DERP. DERPETTY HERP.",
                "created_at":"Thu Apr 21 01:56:17 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "thinkup":{
                    "retweet_count_cache":1,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":0
                },
                "user":{
                    "id":15040935,
                    "location":"Cardiff",
                    "description":"16 year old. I do a podcast, SDWFD(w/c!) http://v.gd/superwooduo. Skeptic.",
                    "url":"http://thewelshboyo.co.uk",
                    "friend_count":310,
                    "followers_count":1377,
                    "profile_image_url":"http://a3.twimg.com/profile_images/1295858459/4aed4901-d81b-490d-a35a-8babff8a4d48_normal.png",
                    "name":"Rhys Morgan",
                    "screen_name":"rhysmorgan",
                    "statuses_count":32396,
                    "created_at":"Sat Jun 07 19:42:58 +0100 2008",
                    "utc_offset":3600,
                    "avg_tweets_per_day":30.88,
                    "last_updated":"2011-04-22 13:01:31",
                    "thinkup":{
                        "last_post":"2011-04-22 11:33:42",
                        "last_post_id":0,
                        "found_in":"retweets"
                    }
                }
            }
        },
        {
            "id":60841137652514816,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":1,
            "text":"A @thinkupapp API you say? Don't mind if I do. :D https://github.com/ginatrapani/ThinkUp/commit/61008ceb5f38ac5a71aa9d8a0f56484125982b19",
            "created_at":"Wed Apr 20 23:03:52 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":1,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":234,
                "followers_count":103,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":921,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.53,
                "last_updated":"2011-04-22 13:00:10",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":59051247554146304,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":1,
            "text":"Anyone know a good way to reliably reproduce the following errors in the Twitter API: 304, 400, 401, 403, 404, 406, 420, 500, 502 and 503?",
            "created_at":"Sat Apr 16 00:31:29 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":1,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":234,
                "followers_count":103,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":921,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.53,
                "last_updated":"2011-04-22 13:00:10",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":58893004492120064,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"Talking about our favourite Doritos in @thinkupapp_irc. Who said programmers can't have a whimsical side? :)",
            "created_at":"Fri Apr 15 14:02:41 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":234,
                "followers_count":103,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":921,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.53,
                "last_updated":"2011-04-22 13:00:10",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        }
    ]
