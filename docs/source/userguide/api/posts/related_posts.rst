Related Posts
=============
Gets posts that are related to a post. By this we mean replies and retweets.

**API call type slug:** ``related_posts``

**Example Usage:** ``webapp/api/v1/post.php?type=related_posts&post_id=12345``

==================
Required arguments
==================

* **post_id**

    The ID of the post to find related posts for.

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
    to replies also. Defaults to false. This argument can be set to true by making it equal to either **1**, **t** or
    **true**.

* **trim_user**

    If set to true, this flag strips the user part of the output to just the user's ID and nothing else. Defaults to
    false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

==============
Example output
==============

``webapp/api/v1/post.php?type=related_posts&post_id=4329245409021953&include_entities=true``::

    [
        {
            "id":5639421072244736,
            "source":"web",
            "location":"Milky Way Galaxy",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":4329245409021953,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Hey, only just saw your great blogpost! Thanks so much, and heck I'm useless at coming to talk to people so please do talk to me!",
            "created_at":"Fri Nov 19 15:11:57 +0000 2010",
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
            },
            "entities":{
                "urls":[

                ],
                "hashtags":[

                ],
                "user_mentions":[
                    {
                        "name":"Sam Rose",
                        "id":69410725,
                        "screen_name":"samwhoo",
                        "indices":[
                            0,
                            8
                        ]
                    }
                ]
            }
        },
        {
            "id":5658882579898369,
            "source":"<a href=\"http://itunes.apple.com/app/twitter/id333903271?mt=8\" rel=\"nofollow\">Twitter for iPad</a>",
            "location":"Cardiff, UK",
            "place":null,
            "geo":{
                "coordinates":[
                    51.4813069,
                    -3.1804979
                ]
            },
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":4329245409021953,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":119,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Very, very annoyed with my parents for not letting me go :(",
            "created_at":"Fri Nov 19 16:29:17 +0000 2010",
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
            },
            "entities":{
                "urls":[

                ],
                "hashtags":[

                ],
                "user_mentions":[
                    {
                        "name":"Sam Rose",
                        "id":69410725,
                        "screen_name":"samwhoo",
                        "indices":[
                            0,
                            8
                        ]
                    }
                ]
            }
        }
    ]
