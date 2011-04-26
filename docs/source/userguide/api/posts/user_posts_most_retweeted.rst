Most Retweeted Posts
====================

Gets a user's most retweeted posts.

**API call type slug:** ``user_posts_most_reweeted``

**Example Usage:** ``webapp/api/v1/post.php?type=user_posts_most_retweeted&username=samwhoo``

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

* **count**

    The number of results to display from this API call. Defaults to 20.

* **page**

    The page of results to display for this API call. Defaults to 1.

* **include_entities**

    Whether or not to include `Tweet Entities <http://dev.twitter.com/pages/tweet_entities>`_ in the output. Defaults
    to false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

* **include_replies**

    Whether or not to include replies to this post in the output. This argument is recursive and will retrieve replies
    to replies also. Defaults to false. This argument can be set to true by making it equal to either **1**,
    **t** or **true**.

* **trim_user**

    If set to true, this flag strips the user part of the output to just the user's ID and nothing else. Defaults to
    false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

==============
Example output
==============

``webapp/api/v1/post.php?type=user_posts_most_retweeted&username=samwhoo&count=5``::

    [
        {
            "id":55040741323448320,
            "source":"<a href=\"http://mobile.twitter.com\" rel=\"nofollow\">Twitter for Android</a>",
            "location":"Wales, UK",
            "place":"Rhondda Cynon Taff, Rhondda Cynon Taff",
            "geo":{
                "coordinates":[
                    51.594253,
                    -3.3257351
                ]
            },
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":4,
            "text":"My house mate is trying to convert me so I keep fixing his propaganda. http://t.co/kW6STHd",
            "created_at":"Mon Apr 04 22:55:09 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "coordinates":{
                "coordinates":[
                    51.594253,
                    -3.3257351
                ]
            },
            "thinkup":{
                "retweet_count_cache":4,
                "reply_count_cache":1,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":237,
                "followers_count":102,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":941,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-26 15:00:05",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":21760224817840128,
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
            "all_retweets":2,
            "text":"\"Vaginas\" are trending! I feel so left out.",
            "created_at":"Mon Jan 03 02:50:16 +0000 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":2,
                "reply_count_cache":1,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":237,
                "followers_count":102,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":941,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-26 15:00:05",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":4329245409021953,
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
            "all_retweets":2,
            "text":"Had a great time tonight at #Cardiff #SitP My post about it: http://lbak.co.uk/blog/16/11/2010/losing-my-skeptic-virginity/",
            "created_at":"Tue Nov 16 00:25:47 +0000 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":2,
                "reply_count_cache":2,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725,
                "location":"Wales, UK",
                "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                "url":"http://lbak.co.uk",
                "friend_count":237,
                "followers_count":102,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":941,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-26 15:00:05",
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
                "friend_count":237,
                "followers_count":102,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":941,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-26 15:00:05",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":55589617977663488,
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
            "text":"http://isblackmesareleased.com/releasedate/ - Absolutely brilliant ^_^",
            "created_at":"Wed Apr 06 11:16:12 +0100 2011",
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
                "friend_count":237,
                "followers_count":102,
                "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                "name":"Sam Rose",
                "screen_name":"samwhoo",
                "statuses_count":941,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.55,
                "last_updated":"2011-04-26 15:00:05",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        }
    ]
