Facebook
========

ThinkUp's Facebook plugin collects posts and status updates for Facebook users and the Facebook pages those users like.

Configure the Facebook Plugin (Admin only)
------------------------------------------

To use the Facebook plugin, you'l need to `create a Facebook application on facebook.com 
<https://developers.facebook.com/apps>`_. Set the Web Site > Site URL  as recommended, and the Facebook-provided API
Key, Application Secret and Application ID in the Facebook plugin's settings page in ThinkUp.

Plugin Settings
---------------

**App ID** (required) is the Application ID provided when you `create a Facebook application on facebook.com 
<https://developers.facebook.com/apps>`_ for use with ThinkUp.

**App Secret:** (required) is the Application Secret provided when you `create a Facebook application on
facebook.com <https://developers.facebook.com/apps>`_ for use with ThinkUp.

**Max crawl time in minutes:** (optional) is the maximum amount of time that ThinkUp will spend crawling a single
Facebook user or page. This cap is in place for very busy pages or profiles with deep archives which could take hours
to crawl. The default value is 20 minutes. This means that by default, after 20 minutes of crawling a particular
Facebook profile or page, the crawler will move onto the next one.

Add a Facebook user profile to ThinkUp
--------------------------------------

Click on the "Authorize ThinkUp on Facebook" button to add your Facebook user account to ThinkUp. This button will only
appear if the Facebook plugin is configured.

Add a Facebook page to ThinkUp
------------------------------

ThinkUp's Facebook plugin works with Facebook pages, but it can only connect with regular Facebook user accounts.
To add a Facebook page, connect a regular Facebook user account to ThinkUp. Make sure that user "likes" the page, and
then add it to ThinkUp from the Likes dropdown in ThinkUp.

