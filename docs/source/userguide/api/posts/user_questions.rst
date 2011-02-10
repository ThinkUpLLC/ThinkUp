User Questions
==============
Gets question posts by a user. This will return all of the posts a user has made that contain questions.

**API call type slug:** ``user_questions``

**Example Usage:** ``webapp/api/v1/post.php?type=user_questions&username=samwhoo``

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

``webapp/api/v1/post.php?type=user_questions&username=samwhoo&count=5``::


    [
        {
            "id":54356409298587648,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":930061,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@ginatrapani \"NEXT MILESTONE: 917 days till you reach 1,000 followers at this rate.\" - Perhaps make this metric a little less ambitious? :p",
            "created_at":"Sun Apr 03 01:35:52 +0100 2011",
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
                "retweet_count_cache":0,
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
                "statuses_count":780,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "favourites_count":36,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":53476079138123776,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":126162586,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@ben_hay What course are you going, again?",
            "created_at":"Thu Mar 31 15:17:45 +0100 2011",
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
                "retweet_count_cache":0,
                "reply_count_cache":1,
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
                "statuses_count":780,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "favourites_count":36,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":52774253690499072,
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
            "all_retweets":0,
            "text":"It seems that an OleDbCommand in C# requires that you explicitly declare the direction of your ORDER BY clause. Why no default? :/",
            "created_at":"Tue Mar 29 16:48:57 +0100 2011",
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
                "retweet_count_cache":0,
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
                "statuses_count":780,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "favourites_count":36,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":52636341976039424,
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
            "all_retweets":0,
            "text":"My new housemate wakes up at 4am. WAKES UP... AT 4AM. And showers. Or takes the longest piss in the world. What fresh hell is this?",
            "created_at":"Tue Mar 29 07:40:56 +0100 2011",
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
                "retweet_count_cache":0,
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
                "statuses_count":780,
                "created_at":"Thu Aug 27 21:32:42 +0100 2009",
                "favourites_count":36,
                "utc_offset":3600,
                "thinkup":{
                    "last_post":"0000-00-00 00:00:00",
                    "last_post_id":0,
                    "found_in":"Owner Status"
                }
            }
        },
        {
            "id":51400369997230080,
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
            "all_retweets":0,
            "text":"Inspired by a friend of mine studying psychology, I just wrote this :) What is software? http://lbak.co.uk/blog/25/03/2011/what-is-software/",
            "created_at":"Fri Mar 25 21:49:37 +0000 2011",
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
                "retweet_count_cache":0,
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
                "statuses_count":780,
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
    ]
