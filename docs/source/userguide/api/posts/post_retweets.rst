Post Retweets
=============
Gets all retweets of a post.

**API call type slug:** ``post_retweets``

**Example Usage:** ``webapp/api/v1/post.php?type=post_retweets&post_id=12345``

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

``webapp/api/v1/post.php?type=post_retweets&post_id=17393678888738816``::

    [
        {
            "id":17438947407831040,
            "source":"web",
            "location":"Liverpool, Merseyside, UK",
            "place":null,
            "geo":{
                "coordinates":[
                    53.4107766,
                    -2.9778383
                ]
            },
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":69410725,
            "reply_retweet_distance":119,
            "is_retweet_by_friend":true,
            "favorited":false,
            "all_retweets":0,
            "short_location":"Liverpool, Merseyside, UK",
            "text":"RT @samwhoo: Comic Sans is trending? A bit late aren't you, Twitter?",
            "created_at":"Wed Dec 22 04:39:03 +0000 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "coordinates":{
                "coordinates":[
                    53.4107766,
                    -2.9778383
                ]
            },
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":0,
                "old_retweet_count_cache":0,
                "is_geo_encoded":1
            },
            "user":{
                "id":92529373,
                "location":"Liverpool.",
                "description":"Online and pissed off.",
                "url":"",
                "friend_count":38,
                "followers_count":40,
                "profile_image_url":"http://a0.twimg.com/profile_images/1249376120/Photo_1_normal.jpg",
                "name":"David Parry",
                "screen_name":"buildthewall",
                "statuses_count":514,
                "created_at":"Wed Nov 25 14:11:37 +0000 2009",
                "favourites_count":0,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"2011-03-27 22:11:31",
                    "last_post_id":52130655026417664,
                    "found_in":"Friends"
                }
            },
            "retweeted_status":{
                "id":17393678888738816,
                "source":"web",
                "location":"Wales, United Kingdom",
                "place":null,
                "geo":{
                    "coordinates":[
                        52.4699784,
                        -3.8303771
                    ]
                },
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
                "coordinates":{
                    "coordinates":[
                        52.4699784,
                        -3.8303771
                    ]
                },
                "thinkup":{
                    "retweet_count_cache":1,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":1
                },
                "user":{
                    "id":69410725,
                    "location":"Wales, UK",
                    "description":"20 years old. Born and raised in Wales, UK. Programmer, British Mensa member, grapefruit, terrible at writing tag lines.",
                    "url":"http://lbak.co.uk",
                    "friend_count":225,
                    "followers_count":83,
                    "profile_image_url":"http://a1.twimg.com/profile_images/1140823002/28567_10150158194220371_544780370_11863380_6914499_n_normal.jpg",
                    "name":"Sam Rose",
                    "screen_name":"samwhoo",
                    "statuses_count":775,
                    "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                    "favourites_count":36,
                    "utc_offset":3600,
                    "thinkup":{
                        "last_post":"0000-00-00 00:00:00",
                        "last_post_id":0,
                        "found_in":"Owner Status"
                    }
                }
            }
        }
    ]