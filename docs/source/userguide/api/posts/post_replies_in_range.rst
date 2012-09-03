Post Replies
============
Gets the replies to a post in a given time range.

**API call type slug:** ``post_replies_in_range``

**Example Usage:** ``api/v1/post.php?type=post_replies_in_range&from=29-03-2011&until=04-04-2011&post_id=12345``

==================
Required arguments
==================

* **post_id**

    The ID of the post to retrieve replies to.

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

    The column to order the results by. Defaults to chronological order ("date"). The default direction to order
    results from this call are descending.

* **unit**

    Sets the unit of measurement to return the ``reply_retweet_distance`` in. Can be either "mi" for miles or "km"
    for kilometres. Defaults to "km".

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
    This, user_replies_in_range, user_questions_in_range, user_mentions_in_range and user_posts_in_range are the ThinkUp Post API methods which do not enforce a cap of 200 post results returned per call. 
    As such, when querying time ranges which contain more than 200 posts, keep in mind that processing that amount of
    data may exceed your server's memory limits.
   

==============
Example output
==============

``/api/v1/post.php?type=post_replies_in_range&post_id=242576686674223106&from=2012-09-03T11:00:00GMT+02:00&until=2012-09-03T017:00:00%20GMT+02:00&include_entities=t&include_replies=t``::



[
    {
        "id":242578744764690432,
        "author_follower_count":null,
        "source":"web",
        "location":"Tordera-Barcelona",
        "place":null,
        "place_id":null,
        "geo":null,
        "in_reply_to_user_id":227641758,
        "in_reply_to_post_id":242576686674223106,
        "is_reply_by_friend":false,
        "is_retweet_by_friend":false,
        "reply_retweet_distance":0,
        "in_rt_of_user_id":null,
        "retweet_count_api":0,
        "favlike_count_cache":0,
        "links":[
            
        ],
        "favorited":false,
        "all_retweets":0,
        "text":"@penia19 #fcb",
        "created_at":"Mon Sep 03 11:04:14 +0200 2012",
        "annotations":null,
        "truncated":false,
        "protected":false,
        "thinkup":{
            "retweet_count_cache":0,
            "retweet_count_api":0,
            "reply_count_cache":0,
            "old_retweet_count_cache":0,
            "is_geo_encoded":0
        },
        "user":{
            "id":256559225,
            "location":"Tordera-Barcelona",
            "description":"Llicenciada en Ci\u00e8ncies Pol\u00edtiques i de l'Administraci\u00f3, a la Universtat Pompeu Fabra. Membre de la JNC, Deba-t i R\u00e0dio Tordera",
            "url":"",
            "friend_count":520,
            "last_updated":"2012-09-03 13:23:58",
            "followers_count":283,
            "profile_image_url":"http://a0.twimg.com/profile_images/2169909420/ji_normal.jpg",
            "name":"Judith",
            "screen_name":"judithtoronjo",
            "statuses_count":585,
            "created_at":"Wed Feb 23 15:58:39 +0100 2011",
            "avg_tweets_per_day":1.05,
            "thinkup":{
                "last_post":"0000-00-00 00:00:00",
                "last_post_id":"",
                "found_in":"mentions"
            }
        },
        "entities":{
            "hashtags":[
                {
                    "text":"fcb",
                    "indices":[
                        9,
                        13
                    ]
                }
            ],
            "user_mentions":[
                {
                    "name":"Daniel Pe\u00f1a Pizarro",
                    "id":227641758,
                    "screen_name":"penia19",
                    "indices":[
                        0,
                        8
                    ]
                }
            ]
        }
    },
    {
        "id":242579576025403392,
        "author_follower_count":null,
        "source":"web",
        "location":"Barcelona",
        "place":null,
        "place_id":null,
        "geo":null,
        "in_reply_to_user_id":227641758,
        "in_reply_to_post_id":242576686674223106,
        "is_reply_by_friend":false,
        "is_retweet_by_friend":false,
        "reply_retweet_distance":0,
        "in_rt_of_user_id":null,
        "retweet_count_api":0,
        "favlike_count_cache":0,
        "links":[
            
        ],
        "favorited":false,
        "all_retweets":0,
        "text":"@penia19 me too!",
        "created_at":"Mon Sep 03 11:07:32 +0200 2012",
        "annotations":null,
        "truncated":false,
        "protected":false,
        "thinkup":{
            "retweet_count_cache":0,
            "retweet_count_api":0,
            "reply_count_cache":0,
            "old_retweet_count_cache":0,
            "is_geo_encoded":0
        },
        "user":{
            "id":302708860,
            "location":"Barcelona",
            "description":"Research Project Manager @ TVC - I never think of the future. It comes soon enough. Albert Einstein\n",
            "url":"http://es.linkedin.com/in/eusebiocarasusan",
            "friend_count":247,
            "last_updated":"2012-09-03 13:23:58",
            "followers_count":113,
            "profile_image_url":"http://a0.twimg.com/profile_images/2432460341/810fonvgxd8c9z65pgdi_normal.jpeg",
            "name":"Eusebio Carasus\u00e1n",
            "screen_name":"ecarasusan",
            "statuses_count":417,
            "created_at":"Sat May 21 16:40:17 +0200 2011",
            "avg_tweets_per_day":0.89,
            "thinkup":{
                "last_post":"2012-08-23 17:51:19",
                "last_post_id":"",
                "found_in":"mentions"
            }
        },
        "entities":{
            "hashtags":[
                
            ],
            "user_mentions":[
                {
                    "name":"Daniel Pe\u00f1a Pizarro",
                    "id":227641758,
                    "screen_name":"penia19",
                    "indices":[
                        0,
                        8
                    ]
                }
            ]
        }
    }
]    
