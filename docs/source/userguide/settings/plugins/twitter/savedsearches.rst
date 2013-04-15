Twitter Saved Searches
======================

Twitter saved searches captures tweets from the Twitter API that contain keyword or hashtag specified using an
authorized Twitter account's API calls.

Set Up the Twitter Plugin (Admin only)
--------------------------------------

To search a keyword or hashtag, you will need to `create a new application on Twitter for ThinkUp 
<https://dev.twitter.com/apps/new>`_ and then configure **Consumer key** and **Consumer secret**
in Settings > Plugins > Twitter > Configure

Set Up a Twitter account
------------------------

Then you will need to add a Twitter account in Settings > Plugins > Twitter > Configure > Add a Twitter account.

Adding a keyword or hashtag search (Admin only)
-----------------------------------------------

Once a Twitter account has been authorized, you can save a search for hashtags or keywords in 
Settings > Plugins > Twitter > Configure > Saved searches. Then you can add a hashtag (ex: #mwc2013) or keyword
(ex: Messi) and click on Save Search.

Next time ThinkUp's crawler runs it will execute this search and save the resulting tweets.

Viewing a keyword or hashtag search
-----------------------------------

To view tweets retrieved from a search, use ThinkUp's search box.

Deleting a keyword or hashtag search (Admin only)
-------------------------------------------------

To delete a saved search, on the saved search page, click on the Delete button.

This action deletes:

* The saved search
* The posts relationship to the hashtag
* If another instance has not saved it, the hashtag.

It does not delete:

* Links
* Posts
* Users

Related to the saved search results.
