Related Posts
=============
Gets posts that are related to a post. By this we mean replies and retweets.

**API call type slug:** ``related_posts``

**Example Usage:** ``api/v1/post.php?type=related_posts&post_id=12345``

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

    The number of results to display from this API call. Defaults to 20. If you supply something that is
    not a valid number, this argument will revert to its default value of 20. For performance reasons, the maximum
    number of posts the ThinkUp API returns per call is 200.

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

``api/v1/post.php?type=related_posts&post_id=4329245409021953&include_entities=true``::

    [
        {
            "id":5658882579898369,
            "source":"<a href=\"http://itunes.apple.com/app/twitter/id333903271?mt=8\" rel=\"nofollow\">Twitter for iPad</a>",
            "location":"Cardiff",
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
            "text":"@samwhoo Very, very annoyed with my parents for not letting me go :(",
            "created_at":"Fri Nov 19 16:29:17 +0000 2010",
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
                "is_geo_encoded":0
            },
            "user":{
                "id":20635230,
                "location":"Milky Way Galaxy",
                "description":"Moderator of Galaxy Zoo & co-founder of Skeptics in the Pub in Wales; citizen science & astronomy enthusiast; humanist & skeptic who writes too much",
                "url":"http://www.aliceingalaxyland.blogspot.com",
                "friend_count":475,
                "followers_count":1416,
                "profile_image_url":"http://a2.twimg.com/profile_images/1207391142/penguin_shrunk_SDSS_wise_normal.jpg",
                "name":"Alice Sheppard",
                "screen_name":"PenguinGalaxy",
                "statuses_count":27569,
                "created_at":"Wed Feb 11 22:27:37 +0000 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":34.46,
                "last_updated":"2011-04-22 13:02:34",
                "thinkup":{
                    "last_post":"2011-04-22 11:31:57",
                    "last_post_id":0,
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
