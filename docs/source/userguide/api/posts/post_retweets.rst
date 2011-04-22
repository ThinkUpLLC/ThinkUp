Post Retweets
=============
Gets all retweets of a post.

**API call type slug:** ``post_retweets``

**Example Usage:** ``api/v1/post.php?type=post_retweets&post_id=12345``

==================
Required arguments
==================

* **post_id**

    The ID of the post to retrieve retweets of.

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

``api/v1/post.php?type=post_retweets&post_id=17393678888738816``::

    [
        {
            "id":17438947407831040,
            "source":"web",
            "location":"Liverpool.",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":69410725,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":true,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @samwhoo: Comic Sans is trending? A bit late aren't you, Twitter?",
            "created_at":"Wed Dec 22 04:39:03 +0000 2010",
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
                "id":92529373,
                "location":"Liverpool.",
                "description":"Online and pissed off.",
                "url":"",
                "friend_count":38,
                "followers_count":42,
                "profile_image_url":"http://a0.twimg.com/profile_images/1249376120/Photo_1_normal.jpg",
                "name":"David Parry",
                "screen_name":"buildthewall",
                "statuses_count":515,
                "created_at":"Wed Nov 25 14:11:37 +0000 2009",
                "utc_offset":3600,
                "avg_tweets_per_day":1.00,
                "last_updated":"2011-04-22 03:05:21",
                "thinkup":{
                    "last_post":"2011-04-12 03:46:18",
                    "last_post_id":57650725152493569,
                    "found_in":"Friends"
                }
            },
            "retweeted_status":{
                "id":17393678888738816,
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
                "text":"Comic Sans is trending? A bit late aren't you, Twitter?",
                "created_at":"Wed Dec 22 01:39:10 +0000 2010",
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
            }
        }
    ]
