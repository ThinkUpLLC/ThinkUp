Post Replies
============
Gets the replies to a post.

**API call type slug:** ``post_replies``

**Example Usage:** ``api/v1/post.php?type=post_replies&post_id=12345``

==================
Required arguments
==================

* **post_id**

    The ID of the post to retrieve replies to.

==================
Optional Arguments
==================

* **network**

    The network to use in the call. Defaults to 'twitter'.

* **order_by**

    The column to order the results by. Defaults to chronological order ("date"). The default direction to order
    results from this call are descending.

* **unit**

    Sets the unit of measurement to return the ``reply_retweet_distance`` in. Can be either "mi" for miles or "km"
    for kilometres. Defaults to "km".

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


``api/v1/post.php?type=post_replies&post_id=52490798066958336&include_entities=true``::


    [
        {
            "id":52495440951771136,
            "source":"<a href=\"http://mobile.twitter.com\" rel=\"nofollow\">Twitter for Android</a>",
            "location":"Seattle, WA, USA",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":52490798066958336,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo Webfinger: http://t.co/zmlLgeG",
            "created_at":"Mon Mar 28 22:21:03 +0100 2011",
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
            "id":52496414823038977,
            "source":"<a href=\"http://www.tweetdeck.com\" rel=\"nofollow\">TweetDeck</a>",
            "location":"Atlanta, Georgia",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":52490798066958336,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@samwhoo on it right now",
            "created_at":"Mon Mar 28 22:24:55 +0100 2011",
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
                "id":19941670,
                "location":"Atlanta, Georgia",
                "description":"Living Life the way it shouldn't be lived... did that make sense??? :) Writer, programmer, technology enthusiast by nature. #teamfollowback",
                "url":"http://intety.com",
                "friend_count":124,
                "followers_count":177,
                "profile_image_url":"http://a1.twimg.com/profile_images/1312020176/189294_10150104705558131_531563130_6566492_5694120_n_normal.jpg",
                "name":"randi miller",
                "screen_name":"randi2kewl",
                "statuses_count":2494,
                "created_at":"Mon Feb 02 23:34:49 +0000 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":3.08,
                "last_updated":"2011-04-22 04:02:36",
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
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
