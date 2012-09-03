User Questions
==============
Gets question posts by a user in a given time range. This will return all of the posts a user has made that contain questions in a given time range.

**API call type slug:** ``user_questions_in_range``

**Example Usage:** ``api/v1/post.php?type=user_questions_in_range&from=29-03-2011&until=04-04-2011&username=samwhoo``

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
    This, user_replies_in_range, post_replies_in_range, user_mentions_in_range and user_posts_in_range are the ThinkUp Post API methods which do not enforce a cap of 200 post results returned per call. 
    As such, when querying time ranges which contain more than 200 posts, keep in mind that processing that amount of
    data may exceed your server's memory limits.

==============
Example output
==============    
    
``/api/v1/post.php?type=user_questions_in_range&username=penia19&from=2012-09-03T10:50:00GMT+02:00&until=2012-09-17T017:00:00%20GMT+02:00&include_entities=t&include_replies=t``::


[
    {
        "id":242576991033888768,
        "author_follower_count":null,
        "source":"web",
        "location":"Alcarr\u00e0s",
        "place":null,
        "place_id":null,
        "geo":null,
        "in_reply_to_user_id":null,
        "in_reply_to_post_id":null,
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
        "replies":[
            {
                "id":242578915867111424,
                "author_follower_count":null,
                "source":"web",
                "location":"Tordera-Barcelona",
                "place":null,
                "place_id":null,
                "geo":null,
                "in_reply_to_user_id":227641758,
                "in_reply_to_post_id":242576991033888768,
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
                "text":"@penia19 I don't like Alex Song",
                "created_at":"Mon Sep 03 11:04:55 +0200 2012",
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
                "id":242577856054587392,
                "author_follower_count":null,
                "source":"web",
                "location":null,
                "place":null,
                "place_id":null,
                "geo":null,
                "in_reply_to_user_id":227641758,
                "in_reply_to_post_id":242576991033888768,
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
                "text":"@penia19 I think he's doing great so far. #Song's contributions to the team have only just started #fcb",
                "created_at":"Mon Sep 03 11:00:42 +0200 2012",
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
                    "id":45437435,
                    "location":"",
                    "description":"Powering the next Renaissance",
                    "url":"http://dani.calidos.com",
                    "friend_count":142,
                    "last_updated":"2012-09-03 13:23:59",
                    "followers_count":141,
                    "profile_image_url":"http://a0.twimg.com/profile_images/268758740/dani_normal.jpg",
                    "name":"Daniel Giribet",
                    "screen_name":"danielgiri",
                    "statuses_count":625,
                    "created_at":"Sun Jun 07 22:19:14 +0200 2009",
                    "avg_tweets_per_day":0.53,
                    "thinkup":{
                        "last_post":"0000-00-00 00:00:00",
                        "last_post_id":"",
                        "found_in":"mentions"
                    }
                },
                "entities":{
                    "hashtags":[
                        {
                            "text":"Song",
                            "indices":[
                                42,
                                47
                            ]
                        },
                        {
                            "text":"fcb",
                            "indices":[
                                99,
                                103
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
                "id":242579461676101632,
                "author_follower_count":null,
                "source":"web",
                "location":"Barcelona",
                "place":null,
                "place_id":null,
                "geo":null,
                "in_reply_to_user_id":227641758,
                "in_reply_to_post_id":242576991033888768,
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
                "text":"@penia19 he's gonna win a lot of titles with FCB",
                "created_at":"Mon Sep 03 11:07:05 +0200 2012",
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
        ],
        "text":"#fcb What are your thoughts about Alex Song so far?",
        "created_at":"Mon Sep 03 10:57:16 +0200 2012",
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
            "id":227641758,
            "location":"Alcarr\u00e0s",
            "description":"he anat creixent...",
            "url":"",
            "friend_count":100,
            "last_updated":"2012-09-03 14:43:25",
            "followers_count":45,
            "profile_image_url":"http://a0.twimg.com/profile_images/1830063000/IMG_0539_normal.JPG",
            "name":"Daniel Pe\u00f1a Pizarro",
            "screen_name":"penia19",
            "statuses_count":91,
            "created_at":"Fri Dec 17 11:40:19 +0100 2010",
            "avg_tweets_per_day":0.15,
            "thinkup":{
                "last_post":"0000-00-00 00:00:00",
                "last_post_id":"",
                "found_in":"Owner Status"
            }
        },
        "entities":{
            "hashtags":[
                {
                    "text":"fcb",
                    "indices":[
                        0,
                        4
                    ]
                }
            ],
            "user_mentions":[
                
            ]
        }
    }
]
