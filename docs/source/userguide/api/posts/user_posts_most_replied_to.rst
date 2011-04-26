Most Replied To Posts
=====================

Gets a user's most replied to posts.

**API call type slug:** ``user_posts_most_replied_to``

**Example Usage:** ``webapp/api/v1/post.php?type=user_posts_most_replied_to&username=samwhoo``

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

* **count**

    The number of results to display from this API call. Defaults to 20. If you supply something that is
    not a valid number, this argument will revert to its default value of 20. For performance reasons, the maximum
    number of posts the ThinkUp API returns per call is 200.

* **page**

    The page of results to display for this API call. Defaults to 1. When you get to the end of the pages of results,
    API calls will just return empty JSON. No error is generated.

* **include_entities**

    Whether or not to include `Tweet Entities <http://dev.twitter.com/pages/tweet_entities>`_ in the output.
    Defaults to false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

* **include_replies**

    Whether or not to include replies to this post in the output. This argument is recursive and will retrieve
    replies to replies also. Defaults to false. This argument can be set to true by making it equal to either
    **1**, **t** or **true**.

* **trim_user**

    If set to true, this flag strips the user part of the output to just the user's ID and nothing else. Defaults to
    false. This argument can be set to true by making it equal to either **1**, **t** or **true**.

==============
Example output
==============

``webapp/api/v1/post.php?type=user_posts_most_replied_to&username=samwhoo&count=5&trim_user=t``::

    [
        {
            "id":13719897980805120,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":20108370,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":13716755222368256,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@RAtheist Broken link?",
            "created_at":"Sat Dec 11 22:20:53 +0000 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":3,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725
            }
        },
        {
            "id":12275344295862272,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":20108370,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":12252101350531072,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@RAtheist I selected United Kingdom from a dropdown list O.o",
            "created_at":"Tue Dec 07 22:40:44 +0000 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":3,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725
            }
        },
        {
            "id":1327470519259136,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":20635230,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":1326700147249152,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@PenguinGalaxy Implement a font colour selector if you had access to the theme's code... I don't know if Blogger will let you, though.",
            "created_at":"Sun Nov 07 17:37:48 +0000 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":3,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725
            }
        },
        {
            "id":28922695221,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":15040935,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":28922592976,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@rhysmorgan  @garwboy @PenguinGalaxy I still have absolutely no idea what it is ^_^ Sure, I could Google it. But I prefer conversation.",
            "created_at":"Wed Oct 27 20:41:02 +0100 2010",
            "annotations":null,
            "truncated":false,
            "protected":false,
            "thinkup":{
                "retweet_count_cache":0,
                "reply_count_cache":3,
                "old_retweet_count_cache":0,
                "is_geo_encoded":0
            },
            "user":{
                "id":69410725
            }
        },
        {
            "id":57489200131485696,
            "source":"web",
            "location":"Wales, UK",
            "place":null,
            "geo":null,
            "in_reply_to_user_id":9923162,
            "is_reply_by_friend":false,
            "in_reply_to_post_id":57354343355138048,
            "in_rt_of_user_id":null,
            "reply_retweet_distance":0,
            "is_retweet_by_friend":false,
            "favorited":false,
            "all_retweets":0,
            "text":"@DazMSmith Hopefully someone else at ThinkUp will have a more coherent answer for you soon :) Let us know what you think of the app!",
            "created_at":"Mon Apr 11 17:04:28 +0100 2011",
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
                "id":69410725
            }
        }
    ]
