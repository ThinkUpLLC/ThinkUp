Expand URLs ThinkUp Plugin
============================

The Expand URLs ThinkUp plugin expands shortened URLs in the body of ThinkUp posts to their original long form. 

Crawler Plugin
--------------
During the crawl process, Expand URLs selects the unique URLs from the last 1500 links in the links table which have not been expanded before, retrieves the expanded form, and saves it to the database.