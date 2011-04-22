Post
=====
Gets a single post.

**API type slug:** ``post``

**Example usage:** ``api/v1/post.php?type=post&post_id=12345``

==================
Required arguments
==================

* **post_id**

    The ID of the post to retrieve.

==================
Optional Arguments
==================

* **network**

    The network to use in the call. Defaults to 'twitter'.

* **include_entities**

    Whether or not to include `Tweet Entities <http://dev.twitter.com/pages/tweet_entities>`_ in the output.
    Defaults to false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

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

``api/v1/post.php?post_id=18152896965124096`` (the API type defaults to ``post``)::

    {
        "id":18152896965124096,
        "source":"web",
        "location":"Wales, UK",
        "place":null,
        "geo":null,
        "in_reply_to_user_id":20636385,
        "is_reply_by_friend":false,
        "in_reply_to_post_id":17764087211491328,
        "in_rt_of_user_id":null,
        "reply_retweet_distance":0,
        "is_retweet_by_friend":false,
        "favorited":false,
        "all_retweets":0,
        "text":"@Stellar190 Application in astronomy, you say? Do you have any examples? :) (I don't doubt it, I'm just curious)",
        "created_at":"Fri Dec 24 03:56:02 +0000 2010",
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
    }
