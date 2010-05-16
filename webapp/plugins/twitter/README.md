Twitter ThinkTank Plugin
========================

The Twitter ThinkTank plugin retrieves tweets from specified users and adds them to the ThinkTank database.

Installation
------------

Log into Twitter and [register your ThinkTank instance](http://twitter.com/oauth_clients/). 

Set the callback URL to:
    http://yourserver.com/path-to-thinktank-webapp/plugins/twitter/auth.php

Write down the items labeled “Consumer key” and “Consumer secret” and add them  to ThinkTank's config.inc.php file.

In ThinkTank's configuration area, authorize the Twitter account(s) ThinkTank should crawl.

Crawler Plugin
--------------

During the crawl process, the Twitter plugin retrieves tweets and mentions for authorized users and inserts them into the ThinkTank database.