Post API
========

How does it work?
-----------------

The file to query for API results is located in <thinkup>/api/v1/post.php. There are a number of API call 
"types" and these can be specified in the "type" GET variable. Different API call types have their own set of required 
and optional parameters. Some of these are mapped to work in exactly the same way as the Twitter API but some are 
ThinkUp-specific.

The output from the API is going has been modelled to look as much like the
`Twitter Mentions API  <http://dev.twitter.com/doc/get/statuses/mentions>`_ as possible. Any ThinkUp specific data can
be found in a "thinkup" variable in each post and user when the output has been JSON decoded.

**Important:** The ThinkUp API currently does not support any authentication methods. Because of this, you will not be
able to retrieve protected posts with the API. The API will only return posts that are public on Twitter
or published on a Facebook Page. (ThinkUp automatically marksl all Facebook user profile posts private.)

How do I use it?
----------------

Using the API is quite simple. Let's say you wanted to make an API call of type "user_posts" for the user "samwhoo", your
request would look like this:

`http://example.com/your_thinkup_install/api/v1/post.php?type=user_posts&username=samwhoo`

That call will output the latest 20 posts made by samwhoo (as the default number of posts to return is 20).

Consistency
-----------

Unlike the Twitter API, the ThinkUp API tries its best to return to you the same format of data for every call to the
API. The format that posts (tweets) are returned in remains consistent regardless of what API call you are making.

This `Anatomy of a Tweet <http://www.scribd.com/doc/30146338/map-of-a-tweet>`_ PDF was one of our reference documents.
It's a very good start for anyone wishing to learn the Twitter or ThinkUp API.

Facebook Support
----------------

As of this moment, you **can** use the API to search for Facebook posts but it is untested and experimental. The aim of
this first iteration of the API was to nail Twitter support.

Max Posts Returned Capped at 200
--------------------------------

We decided to cap the maximum number of posts to return at 200 because this is something that the Twitter API also
does. Requests for over 200 posts tend to take quite a long time anyway. If you do want to enable your installation
to return more than 200 posts at a time, look at line 170 in ``webapp/_lib/controller/class.PostAPIController.php``.

The API call type ``user_posts_in_range`` does not adhere to this count upper limit. Be careful when querying large
time ranges.

Post API Call Reference
-----------------------

.. toctree::
   :maxdepth: 1
   
   post
   post_replies
   post_retweets
   related_posts
   user_mentions
   user_posts
   user_posts_in_range
   user_questions
   user_replies
   ../errors/index