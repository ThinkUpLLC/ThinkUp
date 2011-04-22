Post API
========

ThinkUp's Post API provides methods to retrieve information about posts, such as replies, retweets, user mentions, and
hashtags.

How does it work?
-----------------

The page to request post API results from is located in <thinkup>/api/v1/post.php. There are a number of API call 
"types" and these can be specified in a "type" URL parameter. Different API call types have their own set of required 
and optional parameters. Some of these are mapped to work in exactly the same way as the Twitter API but some are 
ThinkUp-specific.

The output from the API is modelled after the 
`Twitter Mentions API  <http://dev.twitter.com/doc/get/statuses/mentions>`_ and it's overloaded with ThinkUp data. 
ThinkUp- specific data can be found in a "thinkup" namespace in each post and user JSON object.

**Important:** The ThinkUp API currently does not support authentication. Therefore, you cannot retrieve private
posts via the API. The API will only return posts that are public on Twitter or published on a Facebook Page. 
(ThinkUp automatically marks Facebook user profile posts private.)

How do I use it?
----------------

Using the API is quite simple. Let's say you wanted to make an API call of type "user_posts" for the user "samwhoo",
your request would look like this:

`http://example.com/your_thinkup_install/api/v1/post.php?type=user_posts&username=samwhoo`

That URL will output the latest 20 posts made by samwhoo (as the default number of posts to return is 20) in JSON.

Post API Method Reference
-------------------------

Refer to each API method's definition below to see its parameters and example return data.

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