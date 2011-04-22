User Posts
==========
Gets a user's posts.

**API call type slug:** ``user_posts``

**Example Usage:** ``api/v1/post.php?type=user_posts&username=samwhoo``

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


``api/v1/post.php?type=user_posts&username=samwhoo&count=5&order_by=date&direction=ASC``
(this is getting the first 5 posts I ever made on Twitter! :))::

    [
        {
            "id":15719632017,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":21075943,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":15719242236,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@barnsleysime Spotify invite, wasn't it? :)",
            "created_at":"Tue Jun 08 17:12:42 +0100 2010",
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
            "id":15735656159,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":21075943,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":15720735591,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@barnsleysime Thanks man, appreciated :) Could you give me a shout when it's sent? It's for my sister :)",
            "created_at":"Tue Jun 08 22:08:35 +0100 2010",
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
            "id":15753982331,
            "source":"<a href=\"http://dev.twitter.com/\" rel=\"nofollow\">API</a>",
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
            "text":"Interesting...",
            "created_at":"Wed Jun 09 03:20:46 +0100 2010",
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
            "id":15779270312,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":20668363,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":15772812067,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@Tadhg17 Everything alright, mate? I did see :(",
            "created_at":"Wed Jun 09 12:47:55 +0100 2010",
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
            "id":15779305392,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":21075943,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":15765794056,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@barnsleysime Thanks, man :)",
            "created_at":"Wed Jun 09 12:48:32 +0100 2010",
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
        }
    ]
