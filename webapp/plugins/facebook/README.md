Facebook ThinkUp Plugin
=========================

The Facebook ThinkUp plugin retrieves posts from Facebook user profiles and Facebook pages.

Installation
------------

[Create a new Facebook Application](http://facebook.com/developers/) and set the Connect URL to: 
    http://yourserver.com/path-to-thinkup-webapp/plugins/facebook/
 
Set the Post-Remove and Post-Authorize URLs to:
    http://yourserver.com/path-to-thinkup-webapp/account/?p=facebook

Write down your Facebook API Key and Application Secret, and enter those values into ThinkUp's config.inc.php file.

In ThinkUp's configuration area, authorize the Facebook account(s) ThinkUp should crawl.

Crawler Plugin
--------------

During the crawl process, the Facebook plugin retrieves posts on user profiles and pages and inserts them and their comments into the ThinkUp database.