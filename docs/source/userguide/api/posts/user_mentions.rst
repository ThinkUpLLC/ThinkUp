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

    The number of results to display from this API call. Defaults to 20.

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

``api/v1/post.php?type=user_mentions&username=samwhoo&count=5``::

    [
        {
            "id":54361082340458498,
            "source":"<a href=\"http://github.com/drdrang/drtwoot\" rel=\"nofollow\">Dr. Twoot</a>",
            "location":"Naperville, Illinois",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54355802038878208,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Well, the export button for search results doesn\u2019t seem to do anything. Am I missing something obvious?",
            "created_at":"Sun Apr 03 01:54:26 +0100 2011",
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
                "id":10697232,
                "location":"Naperville, Illinois",
                "description":"Retired snowman from Santa's Village.",
                "url":"http://www.leancrew.com/all-this",
                "friend_count":79,
                "followers_count":373,
                "profile_image_url":"http://a3.twimg.com/profile_images/74036670/snowman2_normal.jpg",
                "name":"Dr. Drang",
                "screen_name":"drdrang",
                "statuses_count":4337,
                "created_at":"Thu Nov 29 03:56:42 +0000 2007",
                "favourites_count":134,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"mentions"
                }
            }
        },
        {
            "id":54351904683200513,
            "source":"web",
            "location":"Milky Way Galaxy",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54351245707722752,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Degrees from the University of Open Sauce are more common I'm afraid . . .",
            "created_at":"Sun Apr 03 01:17:58 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":2
            },
            "user":{
                "id":20635230,
                "location":"Milky Way Galaxy",
                "description":"Moderator of Galaxy Zoo & co-founder of Skeptics in the Pub in Wales; citizen science & astronomy enthusiast; humanist & skeptic who writes too much",
                "url":"http://www.aliceingalaxyland.blogspot.com",
                "friend_count":473,
                "followers_count":1377,
                "profile_image_url":"http://a2.twimg.com/profile_images/1207391142/penguin_shrunk_SDSS_wise_normal.jpg",
                "name":"Alice Sheppard",
                "screen_name":"PenguinGalaxy",
                "statuses_count":26562,
                "created_at":"Wed Feb 11 22:27:37 +0000 2009",
                "favourites_count":264,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-04-03 01:34:06",
                    "last_post_id":53902343061778432,
                    "found_in":"retweets"
                }
            }
        },
        {
            "id":54319541915881472,
            "source":"web",
            "location":"Wales",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54212753145069568,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Your broke think up with 11 characters. that's impressive",
            "created_at":"Sat Apr 02 23:09:22 +0100 2011",
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
                "id":221187763,
                "location":"Wales",
                "description":"Second year computer forensics student, promoter for flirt and all round drunk guy. ",
                "url":"",
                "friend_count":36,
                "followers_count":9,
                "profile_image_url":"http://a1.twimg.com/profile_images/1178797185/60388_10150291371470193_585435192_15028818_5822008_n_normal.jpg",
                "name":"Carl Lewis",
                "screen_name":"Carlos13th",
                "statuses_count":19,
                "created_at":"Tue Nov 30 00:02:26 +0000 2010",
                "favourites_count":0,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-03-10 02:04:41",
                    "last_post_id":53460536481955840,
                    "found_in":"mentions"
                }
            }
        },
        {
            "id":54200520822374400,
            "source":"<a href=\"http://itunes.apple.com/app/twitter/id333903271?mt=8\" rel=\"nofollow\">Twitter for iPad</a>",
            "location":"Cardiff",
            "place":null,
            "geo":{
                "coordinates":[
                    51.4813069,
                    -3.1804979
                ]
            },
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54199405577904128,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":119,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo That much is true.",
            "created_at":"Sat Apr 02 15:16:25 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "coordinates":{
                "coordinates":[
                    51.4813069,
                    -3.1804979
                ]
            },
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":1
            },
            "user":{
                "id":15040935,
                "location":"Cardiff",
                "description":"16 year old. I do a podcast, SDWFD(w/c!) http://v.gd/superwooduo. Skeptic.",
                "url":"http://thewelshboyo.co.uk",
                "friend_count":304,
                "followers_count":1367,
                "profile_image_url":"http://a3.twimg.com/profile_images/1295858459/4aed4901-d81b-490d-a35a-8babff8a4d48_normal.png",
                "name":"Rhys Morgan",
                "screen_name":"rhysmorgan",
                "statuses_count":31551,
                "created_at":"Sat Jun 07 19:42:58 +0100 2008",
                "favourites_count":23,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-04-03 00:16:41",
                    "last_post_id":53932036381089792,
                    "found_in":"retweets"
                }
            }
        },
        {
            "id":54189744225124352,
            "source":"web",
            "location":"Wales",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54001484991430656,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo what happened?",
            "created_at":"Sat Apr 02 14:33:36 +0100 2011",
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
                "id":221187763,
                "location":"Wales",
                "description":"Second year computer forensics student, promoter for flirt and all round drunk guy. ",
                "url":"",
                "friend_count":36,
                "followers_count":9,
                "profile_image_url":"http://a1.twimg.com/profile_images/1178797185/60388_10150291371470193_585435192_15028818_5822008_n_normal.jpg",
                "name":"Carl Lewis",
                "screen_name":"Carlos13th",
                "statuses_count":19,
                "created_at":"Tue Nov 30 00:02:26 +0000 2010",
                "favourites_count":0,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-03-10 02:04:41",
                    "last_post_id":53460536481955840,
                    "found_in":"mentions"
                }
            }
        }
    ]
