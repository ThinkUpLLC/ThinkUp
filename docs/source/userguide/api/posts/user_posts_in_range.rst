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

.. warning::
    This, user_replies_in_range, user_questions_in_range, user_mentions_in_range and post_replies_in_range are the ThinkUp Post API methods which do not enforce a cap of 200 post results returned per call. 
    As such, when querying time ranges which contain more than 200 posts, keep in mind that processing that amount of
    data may exceed your server's memory limits.

==============
Example output
==============

``api/v1/post.php?type=user_posts_in_range&from=02-04-2011&until=04-04-2011&username=samwhoo``::


    [
        {
            "id":54682603856474112,
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
            "all_retweets":0,
            "text":"That was absolutely terrifying. #westboro",
            "created_at":"Sun Apr 03 23:12:03 +0100 2011",
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
        },
        {
            "id":54651076317687809,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":19228261,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @RayPeacock: There is no \"heaven\" up in the sky.  That is called \"space\".  We have been up there and checked.  The bible people were  ...",
            "created_at":"Sun Apr 03 21:06:46 +0100 2011",
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
            },
            "retweeted_status":{
                "id":54650142262964224,
                "source":"web",
                "location":"UK",
                "place":null,
                "geo":null,
                "in_reply_to_user_id":null,
                "is_reply_by_friend":false,
                "in_reply_to_post_id":null,
                "in_rt_of_user_id":null,
                "reply_retweet_distance":0,
                "is_retweet_by_friend":false,
                "favorited":false,
                "all_retweets":20,
                "text":"There is no \"heaven\" up in the sky.  That is called \"space\".  We have been up there and checked.  The bible people were making it up.",
                "created_at":"Sun Apr 03 21:03:03 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "thinkup":{
                    "retweet_count_cache":20,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":0
                },
                "user":{
                    "id":19228261,
                    "location":"UK",
                    "description":"Comedian, actor, writer, warm-up, prick. I do that Peacock & Gamble Podcast that's free on iTunes that you pretend not to like.",
                    "url":"http://www.peacockandgamble.com",
                    "friend_count":178,
                    "followers_count":1957,
                    "profile_image_url":"http://a2.twimg.com/profile_images/1316595931/Photo_on_2010-09-07_at_01.02__3_2_normal.jpg",
                    "name":"Ray Peacock",
                    "screen_name":"RayPeacock",
                    "statuses_count":2515,
                    "created_at":"Tue Jan 20 10:36:13 +0000 2009",
                    "utc_offset":3600,
                    "avg_tweets_per_day":3.06,
                    "last_updated":"2011-04-22 02:02:06",
                    "thinkup":{
                        "last_post":"2011-04-20 23:57:31",
                        "last_post_id":61226639987720193,
                        "found_in":"Friends"
                    }
                }
            }
        },
        {
            "id":54631742396579840,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":20474878,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @garwboy: Thanks to Channel 4 news, I now know what the respected philosopher Liam Gallagher thinks of the devastation in Japan. \"... ...",
            "created_at":"Sun Apr 03 19:49:57 +0100 2011",
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
            },
            "retweeted_status":{
                "id":54630960607657984,
                "source":"web",
                "location":"Cardiff",
                "place":null,
                "geo":null,
                "in_reply_to_user_id":null,
                "is_reply_by_friend":false,
                "in_reply_to_post_id":null,
                "in_rt_of_user_id":null,
                "reply_retweet_distance":0,
                "is_retweet_by_friend":false,
                "favorited":false,
                "all_retweets":4,
                "text":"Thanks to Channel 4 news, I now know what the respected philosopher Liam Gallagher thinks of the devastation in Japan. \"... ... Big, innit!\"",
                "created_at":"Sun Apr 03 19:46:50 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "thinkup":{
                    "retweet_count_cache":4,
                    "reply_count_cache":0,
                    "old_retweet_count_cache":0,
                    "is_geo_encoded":0
                },
                "user":{
                    "id":20474878,
                    "location":"Cardiff",
                    "description":"Neuroscience Doctor (on paper), sort of\ncomedian, skeptic, human, writer of Science Digestive. Applied to be a homeopath once, not heard back yet.",
                    "url":"http://sciencedigestive.blogspot.com",
                    "friend_count":423,
                    "followers_count":1735,
                    "profile_image_url":"http://a2.twimg.com/profile_images/1195827475/Dean_headshot_normal.JPG",
                    "name":"Dean Burnett",
                    "screen_name":"garwboy",
                    "statuses_count":11322,
                    "created_at":"Mon Feb 09 22:45:43 +0000 2009",
                    "utc_offset":3600,
                    "avg_tweets_per_day":14.12,
                    "last_updated":"2011-04-22 01:09:22",
                    "thinkup":{
                        "last_post":"2011-04-20 15:42:32",
                        "last_post_id":61076030407966720,
                        "found_in":"Friends"
                    }
                }
            }
        },
        {
            "id":54390296020135936,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":10697232,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54368439489413120,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@drdrang Woot :D Thanks for the feedback!",
            "created_at":"Sun Apr 03 03:50:31 +0100 2011",
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
        },
        {
            "id":54383212843106304,
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
            "all_retweets":0,
            "text":"OH: I mean the 20 words; it does nothing, just silently mocks me when I click - @chartier",
            "created_at":"Sun Apr 03 03:22:22 +0100 2011",
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
        },
        {
            "id":54365021995663360,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":10697232,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54361082340458498,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@drdrang Hm. Doesn't seem to be doing anything for me either. Wanna post this to the mailing list and help us improve the app? :)",
            "created_at":"Sun Apr 03 02:10:05 +0100 2011",
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
        },
        {
            "id":54356409298587648,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
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
        },
        {
            "id":54355802038878208,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":10697232,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54316403053969408,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@drdrang @matthewmcvickar How are you guys finding ThinkUp? Any suggestions for features or fixes? :)",
            "created_at":"Sun Apr 03 01:33:27 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":2,
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
        },
        {
            "id":54351245707722752,
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
            "text":"When I finish my education, I want a first class honours degree from the University of Open Source.",
            "created_at":"Sun Apr 03 01:15:21 +0100 2011",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":1,
                "reply_count_cache":1,
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
        },
        {
            "id":54346303643189248,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":null,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":null,
            "in_rt_of_user_id":838211,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"RT @digitalvision: \"Oh my God, Becky. Look at that pizza. It's so.. Big. So round. Like one of those rap guy's pizzas or something.\"",
            "created_at":"Sun Apr 03 00:55:43 +0100 2011",
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
            },
            "retweeted_status":{
                "id":54345143683264513,
                "source":"<a href=\"http://twitter.com/\" rel=\"nofollow\">Twitter for iPhone</a>",
                "location":"Detroit, MI",
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
                "text":"\"Oh my God, Becky. Look at that pizza. It's so.. Big. So round. Like one of those rap guy's pizzas or something.\"",
                "created_at":"Sun Apr 03 00:51:06 +0100 2011",
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
                    "id":838211,
                    "location":"Detroit, MI",
                    "description":"1/2 cup Urbanist, 3/4 cup Digital Marketing Pro, 1/2 cup Geek Culture, dash of baseball fan with a tablespoon of awesome. First Detroit #techkaraoke champ.",
                    "url":"http://www.portagemedia.com",
                    "friend_count":2019,
                    "followers_count":2229,
                    "profile_image_url":"http://a2.twimg.com/profile_images/1297333462/twitter-export_normal.jpg",
                    "name":"Jeremiah Staes",
                    "screen_name":"digitalvision",
                    "statuses_count":16044,
                    "created_at":"Fri Mar 09 17:13:01 +0000 2007",
                    "utc_offset":3600,
                    "avg_tweets_per_day":10.66,
                    "last_updated":"2011-04-22 01:02:06",
                    "thinkup":{
                        "last_post":"2011-04-20 22:14:55",
                        "last_post_id":61217238421733376,
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
            "is_reply_by_friend":false,
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
        },
        {
            "id":54199405577904128,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":15040935,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54193366124085249,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@rhysmorgan The scout is amazing if you're quick on the headshots :) Makes you look pro, too.",
            "created_at":"Sat Apr 02 15:11:59 +0100 2011",
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
                "all_retweets":10,
                "text":"If this audiobook doesn't download properly I'll never hear the end of it.",
                "created_at":"Sat Apr 02 13:07:14 +0100 2011",
                "annotations":null,
                "truncated":false,
                "protected":false,
                "thinkup":{
                    "retweet_count_cache":10,
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
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":32372003,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":54023437231980544,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@MaizieFellows @ben_hay I wouldn't worry, Maizie, I wasn't included either :&lt;",
            "created_at":"Sat Apr 02 03:40:08 +0100 2011",
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
        },
        {
            "id":54001484991430656,
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
            "all_retweets":0,
            "text":"Wow. It's remarkable how much damage 11 characters can do when they're put somewhere they don't belong. #wondersofcode",
            "created_at":"Sat Apr 02 02:05:31 +0100 2011",
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
    ]
