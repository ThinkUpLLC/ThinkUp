Post Replies
============
Gets the replies to a post.

**API call type slug:** ``post_replies``

**Example Usage:** ``webapp/api/v1/post.php?type=post_replies&post_id=12345``

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


``webapp/api/v1/post.php?type=post_replies&post_id=52490798066958336&include_entities=true``::


    [
        {
            "id":52495440951771136,
            "source":"<a href=\"http://mobile.twitter.com\" rel=\"nofollow\">Twitter for Android</a>",
            "location":"Seattle, WA, USA",
            "place":null,
            "geo":{
                "coordinates":[
                    47.6062095,
                    -122.3320708
                ]
            },
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":52490798066958336,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":7457,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "short_location":"Seattle, WA, USA",
            "text":"@samwhoo Webfinger: http://t.co/zmlLgeG",
            "created_at":"Mon Mar 28 22:21:03 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "coordinates":{
                "coordinates":[
                    47.6062095,
                    -122.3320708
                ]
            },
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":1,
                "old_retweet_count_cache":0,
                "is_geo_encoded":1
            },
            "user":{
                "id":13205432,
                "location":"Seattle, WA, USA",
                "description":"Beloved by cats",
                "url":"http://trevorbramble.com/",
                "friend_count":198,
                "followers_count":261,
                "profile_image_url":"http://a1.twimg.com/profile_images/1256194278/Untitled_normal.png",
                "name":"Trevor Bramble",
                "screen_name":"TrevorBramble",
                "statuses_count":5209,
                "created_at":"Thu Feb 07 14:32:32 +0000 2008",
                "favourites_count":8,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-03-30 15:44:29",
                    "last_post_id":54009144910430208,
                    "found_in":"Friends"
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
            "location":"Atlanta, GA, USA",
            "place":null,
            "geo":{
                "coordinates":[
                    33.7489954,
                    -84.3879824
                ]
            },
            "in_reply_to_user_id":69410725,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":52490798066958336,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":6496,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "short_location":"Atlanta, GA, USA",
            "text":"@samwhoo on it right now",
            "created_at":"Mon Mar 28 22:24:55 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "coordinates":{
                "coordinates":[
                    33.7489954,
                    -84.3879824
                ]
            },
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":1
            },
            "user":{
                "id":19941670,
                "location":"Atlanta, Georgia",
                "description":"Living Life the way it shouldn't be lived... did that make sense??? :) Writer, programmer, technology enthusiast by nature.",
                "url":"http://intety.com",
                "friend_count":75,
                "followers_count":162,
                "profile_image_url":"http://a0.twimg.com/profile_images/1287940880/eightbit-f848a5c3-d78a-4a54-9488-20eed7fd5990_normal.png",
                "name":"randi miller",
                "screen_name":"randi2kewl",
                "statuses_count":2264,
                "created_at":"Mon Feb 02 23:34:49 +0000 2009",
                "favourites_count":1,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":54261744004108288,
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
