User Mentions
=============
Gets posts that a user is mentioned in.

**API call type slug:** ``user_mentions``

**Example Usage:** ``api/v1/post.php?type=user_mentions&username=samwhoo``

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

* **include_rts**

    Whether or not to include retweets as mentions. Defaults to false. This argument can be set to true by making it
    equal to either **1**, **t** or **true**.

* **count**

    The number of results to display from this API call. Defaults to 20. If you supply something that is
    not a valid number, this argument will revert to its default value of 20. For performance reasons, the maximum
    number of posts the ThinkUp API returns per call is 200.

* **page**

    The page of results to display for this API call. Defaults to 1. When you get to the end of the pages of results,
    API calls will just return empty JSON. No error is generated.

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

``api/v1/post.php?type=user_mentions&username=samwhoo&count=5``::

    [
        {
            "id":61263346028122114,
            "source":"web",
            "location":"Canada",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":61257731159490560,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Passionate",
            "created_at":"Fri Apr 22 03:01:34 +0100 2011",
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
                "id":85760550,
                "location":"Canada",
                "description":"Hah!",
                "url":"",
                "friend_count":18,
                "followers_count":18,
                "profile_image_url":"http://a3.twimg.com/profile_images/855291577/twitterProfilePhoto_normal.jpg",
                "name":"Benoit Landry",
                "screen_name":"Salvidrim",
                "statuses_count":837,
                "created_at":"Wed Oct 28 06:50:42 +0000 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-22 07:00:53",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"retweets"
                }
            }
        },
        {
            "id":61263078871937024,
            "source":"web",
            "location":"Lehi, Utah",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":61238661223682048,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo I'm glad i got my degree, but the every day skills came from open source and other in-the-trenches stuff.  Congrats again!",
            "created_at":"Fri Apr 22 03:00:30 +0100 2011",
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
                "id":9905392,
                "location":"Lehi, Utah",
                "description":"A software toolsmith that creates, customizes, and masters great software tools.",
                "url":"http://findme.travishartwell.net/",
                "friend_count":805,
                "followers_count":1620,
                "profile_image_url":"http://a3.twimg.com/profile_images/35267502/n882175547_27194_normal.jpg",
                "name":"Travis B. Hartwell",
                "screen_name":"travisbhartwell",
                "statuses_count":1744,
                "created_at":"Sat Nov 03 02:50:41 +0000 2007",
                "utc_offset":3600,
                "avg_tweets_per_day":1.38,
                "last_updated":"2011-04-22 04:01:12",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"mentions"
                }
            }
        },
        {
            "id":61214633675067392,
            "source":"<a href=\"http://mobile.twitter.com\" rel=\"nofollow\">Twitter for Android</a>",
            "location":"",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":61136478058708992,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo l kicked off quite a thread. Sorry! :)",
            "created_at":"Thu Apr 21 23:48:00 +0100 2011",
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
                "id":18326200,
                "location":"",
                "description":"",
                "url":"http://pdurbin.freeshell.org",
                "friend_count":100,
                "followers_count":51,
                "profile_image_url":"http://a0.twimg.com/profile_images/68449525/6b686fe7f07115890ca63099d088948d-2_normal.jpg",
                "name":"Philip Durbin",
                "screen_name":"philipdurbin",
                "statuses_count":364,
                "created_at":"Tue Dec 23 04:17:49 +0000 2008",
                "utc_offset":3600,
                "avg_tweets_per_day":0.43,
                "last_updated":"2011-04-22 01:00:21",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"mentions"
                }
            }
        },
        {
            "id":61185698706886657,
            "source":"web",
            "location":"Seattle, WA, USA",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":61179112676528128,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Perhaps you can soothe your wounded heart with the warm microprocessors of a brand new, free, iPad 2? ;^)",
            "created_at":"Thu Apr 21 21:53:02 +0100 2011",
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
                "id":13205432,
                "location":"Seattle, WA, USA",
                "description":"Habitual edge case",
                "url":"http://trevorbramble.com/",
                "friend_count":187,
                "followers_count":270,
                "profile_image_url":"http://a1.twimg.com/profile_images/1304895448/trevor_nyc_bw_normal.png",
                "name":"Trevor Bramble",
                "screen_name":"TrevorBramble",
                "statuses_count":5374,
                "created_at":"Thu Feb 07 14:32:32 +0000 2008",
                "utc_offset":3600,
                "avg_tweets_per_day":4.59,
                "last_updated":"2011-04-22 05:01:49",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":60224776932835328,
                    "found_in":"retweets"
                }
            }
        },
        {
            "id":61134153202151424,
            "source":"<a href=\"http://www.tweetdeck.com\" rel=\"nofollow\">TweetDeck</a>",
            "location":"Montreal, Canada",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":61133719125237760,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo I know, same here! You should hear us speak component part codes out loud here at work, sounds even sillier. OH-Pa-Five-Five-One!",
            "created_at":"Thu Apr 21 18:28:12 +0100 2011",
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
                "id":15496351,
                "location":"Montreal, Canada",
                "description":"Prefer to enjoy the big picture than examine the individual pictures; take photos because I'm rubbish with a paintbrush and canvas.",
                "url":"http://angelostavrow.com",
                "friend_count":1122,
                "followers_count":774,
                "profile_image_url":"http://a0.twimg.com/profile_images/1177837673/bluemountains_normal.jpg",
                "name":"Angelo Stavrow",
                "screen_name":"AngeloStavrow",
                "statuses_count":8859,
                "created_at":"Sat Jul 19 23:01:16 +0100 2008",
                "utc_offset":3600,
                "avg_tweets_per_day":8.80,
                "last_updated":"2011-04-21 20:00:41",
                "thinkup":{
                    "last_post":"2011-04-20 20:29:01",
                    "last_post_id":60338425013878784,
                    "found_in":"mentions"
                }
            }
        }
    ]
