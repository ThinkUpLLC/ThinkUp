ThinkUp Roadmap
===============

Welcome to the ThinkUp roadmap!

This page details the current ThinkUp projects-in-progress. A project
will appear here if it involves a series of steps. Individual next
actions or TODOs as related to a project will go in the `ThinkUp Issues
list <http://github.com/ginatrapani/thinkup/issues>`_.

Join the `project mailing
list <http://groups.google.com/group/thinkupapp>`_ to propose additions
to this list.

Ongoing Project: Creating New Data Source Plugins
-------------------------------------------------

-  LinkedIn — `API
   documentation <http://developer.linkedin.com/docs/DOC-1043>`_ ,
   `Status post with
   comments <http://www.linkedin.com/statusDiscussion?view-&scope-1500&topic-307&a-nIga&trk-eml_sta_com>`_
-  StatusNet
-  Gmail and Yahoo Mail (OAuth/IMAP)
-  Flickr
-  YouTube
-  What else?
-  `VIVO <http://www.vivoweb.org/>`_
-  Google Buzz

Project: Simplify Installation and Upgrades
-------------------------------------------

See notes on this in the [[Google Summer of Code Ideas Page]].

Something to consider as we write the upgrading mechanisms and API:
`Semantic versioning <http://semver.org/>`_

Project: Refine the plugin architecture
---------------------------------------

**Immediate TODOs:**

-  Build plugin options interface so that configuration comes out of
   config.inc.php (`Issue
   #66 <http://github.com/ginatrapani/thinkup/issues#issue/66)>`_
-  Give plugins the ability to register tabs on the front end (`Issue
   #103 <http://github.com/ginatrapani/thinkup/issues#issue/103)>`_
-  Offer database creation methods so that plugins can make new tables,
   store and retrieve data from them

**Background:**

To make TU truly multi-service and enable users to build their own
features into it, it’s got to be pluggable. There should be allowances
for both webapp plugins (data visualizations and custom listings, etc)
as well as crawler plugins (new data sources like Facebook and Buzz).

As we build TU plugins, we will continue to refine the plugin
architecture.

Here’s an update on the current state of the pluggable architecture:

**Files**

-  All plugin files are now located in a single place, webapp/plugins/.
-  Each individual plugin has its own subfolder there, so the Twitter
   plugin is in webapp/plugins/twitter/.
-  Templates for rendering plugin-specific things on the frontend are in
   a templates subdirectory, ie, webapp/plugins/twitter/view/.
-  Classes the plugin requires should be in a lib subdirectory, ie,
   common/plugins/twitter/model/.
-  Right now the only webapp plugin that’s working is the configuration
   screen.

**Database**

-  There are now tt*plugins and tt*plugin*options tables
   \* By default, the Twitter plugin is inserted and set to active in
   the tt*plugins table.

**Future plans:**

ThinkUp plugins will work like WordPress: you drop a folder into the
webapp/plugins/ directory, and it gets listed in the webapp as
“Inactive.” Click a link to activate the plugin (insert its row into the
db, set is\_active to 1, run any relevant installation routines) and the
crawler and webapp will execute the methods it registers from there on
in.

See also: :doc:`Plugins: Architecture Wishlist </contribute/developers/plugins/architecturewishlist>`

Project: Expand Documentation
-----------------------------

Two sections:
* For users — `User guide in
  progress <http://wiki.github.com/ginatrapani/thinkup/user-guide>`_
* Installation guides for Windows, Mac, and Linux
* Configuration help
* Write user guide to app versions as `tagged
  here <http://github.com/ginatrapani/thinkup/downloads>`_

-  For developers - :doc:`Developer guide </contribute/developers/index>` in progress

   -  PHPDocumentor class documentation (See `Issue
      #98 <http://github.com/ginatrapani/thinkup/issues#issue/98)>`_
   -  GitHub help and hints

Project: API
------------

-  Output both JSON and RSS
-  Implement Twitter API?

Project: Code Upgrades
----------------------

-  Develop code style guide for both PHP and Smarty/HTML/CSS
-  Complete regression tests
-  More sanity checks for when objects aren’t initialized, cover all
   cases

Project: Optimize SQL Queries
-----------------------------

-  Using the sql.log to track slow queries, optimize queries to make
   selects as fast as possible.

Project: Localization
---------------------

Make ThinkUp multi-language, possibly by using gettext () and Smarty.
Relevant `mailing list
thread <http://groups.google.com/group/thinkupapp/browse_thread/thread/2e5934d1ec195dbe>`_

Assorted New Features Already in the Issues List
------------------------------------------------

-  Capture friends’ Twitter favorites/Facebook likes, add tab/sub-tab
   displaying those (`Issue
   #20 <http://github.com/ginatrapani/thinkup/issues#issue/20)>`_
-  Lists (`Issue
   #17 <http://github.com/ginatrapani/thinkup/issues#issue/17)>`_

   -  Capture all the lists owner’s Twitter users are on, display this
      somehow (tag cloud?) The Facebook equivalent is groups
   -  Sort replies by list membership (show me only replies by people on
      “my best friends” list or in a Facebook group)

-  Geo-location (`Issue
   #21 <http://github.com/ginatrapani/thinkup/issues#issue/21)>`_

   -  Capture geo tags on tweets when available, fuzz them to change
      them from a city block to a whole city

-  Hashtag/user tracking that’s not the account owner (`Issue
   #23 <http://github.com/ginatrapani/thinkup/issues#issue/23)>`_

   -  Start/stop capture of hashtagged tweets
   -  Keyword search/saved searches, evaluate Sphinx, Solr, MongoDB as
      possibilities (`Issue
      #16 <http://github.com/ginatrapani/thinkup/issues#issue/16)>`_

-  Add paging back to see more than just one page of posts (`Issue
   #25 <http://github.com/ginatrapani/thinkup/issues#issue/25)>`_
-  Add the ability to remove an authorized account once it’s added
   (`Issue
   #27 <http://github.com/ginatrapani/thinkup/issues#issue/27)>`_
