Facebook ThinkTank Plugin
=========================

The Facebook ThinkTank plugin retrieves posts from Facebook user profiles
and Facebook pages.

Installation
------------

[Create a new Facebook Application](http://facebook.com/developers/) and set the 
Connect URL to: 
http://yourserver.com/path-to-thinktank-webapp/plugins/facebook/
 
Set the Post-Remove and Post-Authorize URLs to:
http://yourserver.com/path-to-thinktank-webapp/account/?p=facebook

Write down your Facebook API Key and Application Secret, 
and enter those values into ThinkTank's config.inc.php file.

In ThinkTank's configuration area, authorize the Facebook account(s) ThinkTank should crawl.

Crawler Plugin
--------------

During the crawl process, the Facebook plugin retrieves posts on user profiles
and pages and inserts them and their comments into the ThinkTank database.