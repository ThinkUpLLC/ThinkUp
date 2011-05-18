Plugin Architecture Wishlist
============================

What should the plugin architecture grow into? This page is an ongoing
draft of an answer to that question.

Proposed Plugin Types
---------------------

-  Crawler plugin/Network Plugin — this is done, demonstrated with
   Twitter and Facebook plugins

-  Statistical analysis (runs during cron)

   -  User Quality Metering (network specific)
   -  Universal User quality metering (needs to be bolted down to
      certain network according to config)

-  Webapp Plugins

   -  Data view plugin
   -  Registration\_plugin (useful if you want to make a paid, or invite
      only version)

Plugin Hooks to Build
---------------------

TODO

* Register Webapp Data View
* Add Datafield: User, Post, Thread

Done

* crawl: Call per crawler run (like for follow trustrank calculating,
  and actual crawling)
* configuration: Call in the plugin configuration screen for user to
  set plugin options