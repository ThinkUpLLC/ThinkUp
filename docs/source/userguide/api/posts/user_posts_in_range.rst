User Posts In Range
===================
Gets a user's posts in a given time range.

**API call type slug:** ``user_posts_in_range``

**Example Usage:** ``api/v1/post.php?type=user_posts_in_range&from=29-03-2011&until=04-04-2011&username=samwhoo``

==================
Required arguments
==================

* **user_id** or **username**

    Only one of these is required. They are to specify the user to gather posts for in this call.

* **from**

    The date/time to start searching from. This can either be a
    `valid date string <http://www.php.net/manual/en/datetime.formats.php>`_ or a Unix timestamp.

* **until**

    The date/time to search until. This can either be a
    `valid date string <http://www.php.net/manual/en/datetime.formats.php>`_ or a Unix timestamp.

==================
Optional Arguments
==================

* **network**

    The network to use in the call. Defaults to 'twitter'.

* **order_by**

    The column to order the results by. Defaults to chronological order ("date").

* **direction**

    The direction to order the results in. Can be either DESC or ASC. Defaults to DESC.

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

``api/v1/post.php?type=user_posts_in_range&from=02-04-2011&until=04-04-2011&username=samwhoo``::


    [
        {
            "id":54365021995663360,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":10697232,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54361082340458498,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":6117,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@drdrang Hm. Doesn't seem to be doing anything for me either. Wanna post this to the mailing list and help us improve the app? :)",
            "created_at":"Sun Apr 03 02:10:05 +0100 2011",
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
            "id":54355802038878208,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":10697232,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54316403053969408,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":6117,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@drdrang @matthewmcvickar How are you guys finding ThinkUp? Any suggestions for features or fixes? :)",
            "created_at":"Sun Apr 03 01:33:27 +0100 2011",
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
            "id":54355320696356864,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":20635230,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54351904683200513,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@PenguinGalaxy Haha! Mm, lots of kids just do a degree in easy so they can hide from the world for another 3 years. Sucks hard :(",
            "created_at":"Sun Apr 03 01:31:32 +0100 2011",
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
            "id":54351245707722752,
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
            "text":"When I finish my education, I want a first class honours degree from the University of Open Source.",
            "created_at":"Sun Apr 03 01:15:21 +0100 2011",
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
            "id":54346303643189248,
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
            "in_rt_of_user_id":838211,
            "reply_retweet_distance":5760,
            "is_retweet_by_friend":true,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @digitalvision: \"Oh my God, Becky. Look at that pizza. It's so.. Big. So round. Like one of those rap guy's pizzas or something.\"",
            "created_at":"Sun Apr 03 00:55:43 +0100 2011",
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
            },
            "retweeted_status":{
                "id":54345143683264513,
                "source":"<a href=\"http://twitter.com/\" rel=\"nofollow\">Twitter for iPhone</a>",
                "location":"Detroit, MI, USA",
                "place":null,
                "geo":{
                    "coordinates":[
                        42.331427,
                        -83.0457538
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
                "text":"\"Oh my God, Becky. Look at that pizza. It's so.. Big. So round. Like one of those rap guy's pizzas or something.\"",
                "created_at":"Sun Apr 03 00:51:06 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "coordinates":{
                    "coordinates":[
                        42.331427,
                        -83.0457538
                    ]
                },
                "thinkup":{
                    "retweet_count_cache":1,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":1
                },
                "user":{
                    "id":838211,
                    "location":"Detroit, MI",
                    "description":"1/2 cup Urbanist, 3/4 cup Digital Marketing Pro, 1/2 cup Geek Culture, dash of baseball fan with a tablespoon of awesome. First Detroit #techkaraoke champ.",
                    "url":"http://www.portagemedia.com",
                    "friend_count":2001,
                    "followers_count":2194,
                    "profile_image_url":"http://a2.twimg.com/profile_images/1297333462/twitter-export_normal.jpg",
                    "name":"Jeremiah Staes",
                    "screen_name":"digitalvision",
                    "statuses_count":15570,
                    "created_at":"Fri Mar 09 17:13:01 +0000 2007",
                    "favourites_count":103,
                    "utc_offset":3600,
                    "thinkup":{
                        "last_post":"2011-03-30 18:00:01",
                        "last_post_id":54240564530528257,
                        "found_in":"Friends"
                    }
                }
            }
        },
        {
            "id":54212753145069568,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":221187763,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54189744225124352,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@Carlos13th I broke ThinkUp's ability to store posts in its database :p I only did it locally, but it only took 11 misplaced characters :p",
            "created_at":"Sat Apr 02 16:05:02 +0100 2011",
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
            "id":54199405577904128,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":15040935,
            "is_reply_by_friend":true,
            "in_reply_to_post_id":54193366124085249,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":119,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@rhysmorgan The scout is amazing if you're quick on the headshots :) Makes you look pro, too.",
            "created_at":"Sat Apr 02 15:11:59 +0100 2011",
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
            "id":54173992705204224,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":19544379,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @_L_M_C_: If this audiobook doesn't download properly I'll never hear the end of it.",
            "created_at":"Sat Apr 02 13:31:01 +0100 2011",
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
            },
            "retweeted_status":{
                "id":54168009958367232,
                "source":"web",
                "location":null,
                "place":null,
                "geo":null,
                "in_reply_to_user_id":null,
                "is_reply_by_friend":false,
                "in_reply_to_post_id":null,
                "in_rt_of_user_id":null,
                "reply_retweet_distance":0,
                "is_retweet_by_friend":false,
                "favorited":false,
                "all_retweets":8,
                "text":"If this audiobook doesn't download properly I'll never hear the end of it.",
                "created_at":"Sat Apr 02 13:07:14 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "thinkup":{
                    "retweet_count_cache":8,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":0
                },
                "user":null
            }
        },
        {
            "id":54025293215711232,
            "source":"web",
            "location":"Wales, United Kingdom",
            "place":null,
            "geo":{
                "coordinates":[
                    52.4699784,
                    -3.8303771
                ]
            },
            "in_reply_to_user_id":32372003,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54023437231980544,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":5415,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@MaizieFellows @ben_hay I wouldn't worry, Maizie, I wasn't included either :&lt;",
            "created_at":"Sat Apr 02 03:40:08 +0100 2011",
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
            "id":54001484991430656,
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
            "text":"Wow. It's remarkable how much damage 11 characters can do when they're put somewhere they don't belong. #wondersofcode",
            "created_at":"Sat Apr 02 02:05:31 +0100 2011",
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
        }
    ]
