The ThinkUp API
===============

An `Application Programming Interface <http://en.wikipedia.org/wiki/API>`_ is a set of rules and specifications which
let software programs communicate with each other. ThinkUp's API exposes the data stored by a given ThinkUp 
installation in a machine-readable format, `JSON <http://en.wikipedia.org/wiki/JSON>`_, 
via simple `REST <http://en.wikipedia.org/wiki/REST>`_ calls for use by other applications or mashups.

Example API Request
-------------------

For example, to make a Post API request from your ThinkUp installations to see posts by "samwhoo", the URL would
look like this:

http://example.com/your_thinkup_install/api/v1/post.php?type=user_posts&username=samwhoo

To try it yourself, replace example.com with your domain name and your_thinkup_install with your installation's path.

Private Data and Authentication
-------------------------------

ThinkUp's API currently does not support authentication. Therefore, you cannot retrieve private information using the
API. The API will only return posts that are public on Twitter or published on a Facebook Page.

API Reference
-------------

Currently ThinkUp offers a Post API, which provides methods to retrieve information about posts, such as replies,
retweets, user mentions, and hashtags. 

.. toctree::
   :maxdepth: 2
   
   posts/index
   errors/index