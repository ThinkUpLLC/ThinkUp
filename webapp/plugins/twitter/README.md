Twitter ThinkUp Plugin
========================

The Twitter ThinkUp plugin retrieves tweets from specified users and adds them to the ThinkUp database.

Installation
------------

Log into Twitter and [register your ThinkUp instance](http://twitter.com/oauth_clients/). 

Set the callback URL to:
    http://yourserver.com/path-to-thinkup-webapp/plugins/twitter/auth.php

Write down the items labeled "Consumer key" and "Consumer secret" and add them  to ThinkUp's config.inc.php file.

In ThinkUp's configuration area, authorize the Twitter account(s) ThinkUp should crawl.

Crawler Plugin
--------------

During the crawl process, the Twitter plugin retrieves tweets and mentions for authorized users and inserts them into the ThinkUp database.
