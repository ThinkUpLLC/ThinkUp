User Mentions
=============
Gets posts that a user is mentioned in a given time range.

**API call type slug:** ``user_mentions_in_range``

**Example Usage:** ``api/v1/post.php?type=user_mentions_in_range&from=29-03-2011&until=04-04-2011&username=samwhoo``

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

* **include_rts**

    Whether or not to include retweets as mentions. Defaults to false. This argument can be set to true by making it
    equal to either **1**, **t** or **true**.

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
    This, user_replies_in_range, user_questions_in_range, post_replies_in_range and user_posts_in_range are the ThinkUp Post API method which do not enforce a cap of 200 post results returned per call. 
    As such, when querying time ranges which contain more than 200 posts, keep in mind that processing that amount of
    data may exceed your server's memory limits.
s
