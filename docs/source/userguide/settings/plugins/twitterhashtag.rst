Twitter hashtags search
======================

Twitter hashtags search collects all tweets from Twitter API 1.1 tweets/search 
that contain keyword or hashtag specified related to a twitter account.

Set Up the Twitter Plugin (Admin only)
--------------------------------------

To search a keyword or hashtag, you will need to `create a new application on Twitter for ThinkUp 
<https://dev.twitter.com/apps/new>`_ and then configure **Consumer key** and **Consumer secret**
in Settings > Plugins > Twitter > Configure

Set Up a Twitter account
------------------------

Then you will need to add a twitter account in Settings > Plugins > Twitter > Configure > Add a Twitter account
This wiill get you to twitter account authorization page for application created before, 
you have to accept the authorization.


Adding a keyword or hashtag search (Admin only)
-----------------------------------------------

Once completed the setup of twitter application and twitter account you can add a tweets search containing
some hashtags or keywords in Settings > Plugins > Twitter > Configure > Search tweets from a twitter account.
Then you can add a hashtag (ex: #mwc2013) or keyword (ex: Messi) and click on Save Search.
Next time crawler run it will execute this search retrieving the last archive limit tweets.

Viewing a keyword or hashtag search
-----------------------------------

To view tweets retrieved from a search you can click on hashtag or keyword link on account search tweet page.
This link opens a new browser window with ThinkUp POST API with parameters for this search.
ex: http://localhost/thinkup/api/v1/post.php?type=hashtag_posts&hashtag_id=1

Deletting a keyword or hashtag search (Admin only)
--------------------------------------------------

To delete tweets search use the button delete from account search tweet page.
What this action delete ?
* Relation between hashtag and posts
* Relation between instance and hashtag
* Hashtag

What don't delete ?
* Link
* Posts
* Users
