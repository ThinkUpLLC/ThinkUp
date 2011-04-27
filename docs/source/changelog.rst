Changes in ThinkUp
==================

Beta 0.11 - 25 April 2011
-------------------------

**Bugfix Release**

Beta 11 is a **REQUIRED UPDATE** for all installations of ThinkUp prior to April 25.

* Fixes a potential information vulnerability in older versions of ThinkUp that could reveal private posts that are stored by the application.
* Fixes a PHP Notice on ThinkUp API calls in some server environments and updates API documentation.
* Adds Security and Data Privacy documentation.
* Fixes bug where Dashboard menu links are incorrect after logout.


Beta 0.10 - 20 April 2011
-------------------------
**New:**

*   Dashboard, post, and user page redesign: Lefthand menu has a new active tab style; users include links to both
    their profile on the originating service (i.e., Twitter.com) and internal ThinkUp page. The user page is now in the
    app-wide ThinkUp template.
*   Post API: ThinkUp's posts, replies, and retweets are now available in a JSON-based API. See the complete
    :doc:`complete Post API documentation </userguide/api/index>`.
*   Top 20 words redesign: On a given post's page, the top 20 most-frequently mentioned words display by default
    (you no longer have to click on "Top 20 words" in the menu, which has been removed). The Top 20 words have been
    redesigned to be more "tappable."
*   Twitter Web Intents: Easily reply, retweet, or favorite any tweet you see in ThinkUp, directly from ThinkUp's
    interface. More info: http://expertlabs.org/2011/04/twitters-web-intents.html
*   Sticky dashboard navigation: On multi-account installations, when viewing an individual post, clicking on the
    Dashboard link you will return you to the current instance (not the most recently updated instance).
*   Application documentation: Launched official application documentation which developers will submit along with
    each patch to the project. Eventually these docs will be hosted on thinkupapp.com, but the initial version is
    available at http://readthedocs.org/docs/thinkup/en/latest/. Background information:
    http://groups.google.com/group/thinkupapp/browse_thread/thread/aee02b16d968c8ed/656b0849117acd0b
*   (Developers) Error-level only logging: New config file value, ``$THINKUP_CFG['log_verbosity']``, lets you set the
    log to only log errors.

**Fixed:**

*   Broken link in ThinkUp user activation email.
*   Inaccurate rendering of the Broadcaster/Conversationalist bar chart on the Dashboard.
*   Favorites search via SlickGrid.
*   Google Map display of post replies and retweets: Replies and retweets are no longer cut off on the page.
*   SlickGrid's export button: This mostly works; but it's still an open issue which needs a better solution. You
    can export posts from SlickGrid's search results. Currently it opens a new tab; we're working on making it work
    within the current tab/window.
*   Missing Zip library error message in Backup controller: The Backup controller now gracefully handles a server setup
    without the Zip library installed.
*   Follows table indexes optimized for faster retrieval.
*   CrawlerLockedException on a server with multiple installs but different mutexes: Multiple crawls can now run
    side-by-side on a server with multiple installations if they talk to different databases.
*   Fixed "No plugin object defined" error when deactivating a plugin.
*   SlickGrid search results for Facebook; also added permalinks to both Twitter.com and inside ThinkUp to SlickGrid
    Twitter results.
*   Several potential security issues in ThinkUp's WordPress plugin: Download the latest version at
    https://github.com/downloads/ginatrapani/ThinkUp/thinkup_for_wordpress_0.8.zip
*   Renamed Windows-hostile filenames.
*   Developers: Fixed several test failures; upgraded the testing framework to SimpleTest 1.1 alpha, which lets
    developers turn on E_STRICT error level reporting for bulletproof coding and testing.
*   Developers: More tests are now using the FixtureBuilder library instead of raw SQL inserts.

Beta 0.9 - 17 Mar 2011
----------------------

Twitter plugin:
~~~~~~~~~~~~~~~

*   Reduced Twitter API 502 errors
    When you run beta 9, you'll see a greatly reduced number of red
    Twitter API 502 errors in your crawler log. Turns out that if you
    request 200 tweets per Twitter API call, it often times out and issues
    a 502. Beta 9 only requests 100 tweets per call--which requires more
    calls, but results in fewer errors. The number of tweets your ThinkUp
    installation retrieves per API call is now configurable in the Twitter
    plugin's Advanced Settings area (though it's not something you should
    have to change unless you're troubleshooting or developing). More
    info:
    https://groups.google.com/forum/?pli=1#!topic/twitter-development-talk/_0mDiNCbZ0o

*   Fixed Follower Count charts where there is missing data
    If there's a gap in the follower count data (meaning, your crawler
    hasn't run every day the chart represents), those gaps are now
    reflected properly on the X-axis of the follower count graphs on the
    Main Dashboard as well as on the Follower Count page. Screenshot:
    https://skitch.com/ginatrapani/rigsh/ginatrapani-on-twitter-thinkup

*   Added Follower Count milestones
    Wondering how long it will take to reach 1,000 followers? 5,000
    followers? 100,0000 followers? Beta 9 adds a Follower count "next
    milestone" message that calculates how long it will take to reach the
    next level. Here's a screenshot of that in action:
    https://skitch.com/ginatrapani/rigar/mathowies-dashboard-thinkup

*   Corrected Retweet count ceiling at 100 RTs
    Thanks to Amy, retweet counts are no longer capped at 100; if there
    are more than 100 retweets for a post, that's reflected in the UI.

*   Resolved Twitter favorites crawler problem
    If you have no favorites or Twitter is reporting an inaccurate number,
    the crawler handles that more gracefully.

*   Fixed follower deactivation bug
    If a follower account has been deactivated, ThinkUp's crawler doesn't
    count that as an error; rather it deactivates the relationship and
    moves on.


Facebook plugin:
~~~~~~~~~~~~~~~~

*   Fixed broken Facebook avatar images
*   Facebook plugin now pages back to capture all comments on a status
    update, doesn't just get 25


Expand URLs plugin:
~~~~~~~~~~~~~~~~~~~

*   Folded the Flickr Thumbnails plugin into the Expand URLs plugin
    You now set your Flickr API key in the Expand URLs settings; the
    database migration for beta 9 takes care of that for you.
    https://skitch.com/ginatrapani/rig2n/configure-your-account-thinkup


ThinkUp application:
~~~~~~~~~~~~~~~~~~~~

*   Simplified the plugins listing
    Before:
    https://skitch.com/ginatrapani/rig2a/configure-your-account-thinkup
    After:
    https://skitch.com/ginatrapani/rig2c/configure-your-account-thinkup

*   Improved indexes on tu_follows table to speed up queries
    Related mailing list thread:
    http://groups.google.com/group/thinkupapp/browse_thread/thread/78bbafc3e0efb754/738e61a3ad9f6833?hl=en&lnk=gst&q=tu_follows#738e61a3ad9f6833%20%20

*   Fixed several broken/out-of-date links and bad markup throughout the app
*   Fixed base URL calculation logic which generated undefined index errors
*   Fixed Export to CSV file errors
*   Improved email and URL validation
*   Improved installation checks for the PHP and MySQL versions ThinkUp requires
*   Password reset bugfix
*   Corrected CSS file source order
*   SlickGrid reply search is now embedded in-page on a post page
*   Top 20 words now displays yes/no/maybe for polls. Screenshot:
    http://www.flickr.com/photos/ginatrapani/5413706109/


Developer goodies:
~~~~~~~~~~~~~~~~~~

*   The FixtureBuilder library now supports MySQL functions
*   Tests are now completely PHP 5.2 compatible
*   Fully deprecated and removed the Database class from tests,
    everything is PDO/FixtureBuilder-based
*   Added test environment check which prevents devs from accidentally
    wiping their TU data
*   Added nightly test runs to thinkupapp.com server with results
    emailed to the dev list

Beta 0.8 - 28 Jan 2011
-----------------------

**New:**

*   Top 20 words
    My absolute favorite new ThinkUp feature is courtesy of Mark Wilkie:
    on any post that has more than 20 replies, click on the "Top 20 words"
    link in the sidebar menu. ThinkUp will display a summary of most
    frequently-used words in a reply set. Click on one to see all the
    replies which contain the word. Here's an example of Top 20 words in
    action:
    http://smarterware.org/thinkup/post/?t=25077429986&n=twitter

    Fun fact: This feature includes word stemming capabilities, so words
    like reply, replies, and replied all get grouped together. It uses the
    Snowball JavaScript library to do this. Fantastic work, Mark. Next up:
    phrase frequency, so the reply "Big Bang Theory" gets listed as one
    item in the example above.

*   Embed Thread plugin
    Copy and paste a bit of JavaScript into any web page to embed a post
    and set of replies sourced from ThinkUp. Activate the Embed Thread
    plugin in the Settings > Plugins area. Then click on "Embed Thread" on
    any post page to get the embed code. This plugin is a work in
    progress, so give it a try and let us know how it goes. A screenshot:
    https://skitch.com/ginatrapani/rmkpm/post-details-thinkup

*   Web-based application-wide settings
    We're continuing to move as many ThinkUp settings out of the
    config.inc.php file and into the database as possible. In ThinkUp's
    Settings area, an admin can now click on the Settings tab to open or
    close the installation's registration page, and set reCAPTCHA keys as
    well. Screenshot:
    https://skitch.com/ginatrapani/rm2tb/configure-your-account-thinkup

    IMPORTANT NOTE: This setting has registration closed by default on all
    new installations and upgraded installations. It overrides anything
    that is currently listed in your config.inc.php file, meaning, it
    deprecates the $THINKUP_CFG['is_registration_open'] variable and
    reCAPTCHA keys set there. If registration is open on your ThinkUp
    installation right now, after you upgrade to beta 8, you MUST log in
    as an admin and check this box to explicitly reopen it and transfer
    your reCAPTCHA keys into the text fields there and save. Apologies for
    the aggressive change here, but we want everyone's installation to be
    closed/more secure by default.

*   Instagr.am support
    Thanks to Amy, all new Instagr.am images that the crawler encounters
    in beta 8 show up as thumbnails inline in ThinkUp's post listings.

*   Activate accounts from the web interface
    Thanks to Randi, if your installation's new account activation email
    is getting spammed, you can now log in as an admin and activate new
    user account by pressing an "Activate" button in the web interface.
    (You can also deactivate accounts as well.) In Settings, you'll see
    this button listed in the "All ThinkUp Accounts" tab.

*   Command line interface to backups and migrations
    Thanks to Mark, advanced users with large databases can now back up
    their ThinkUp installation and run potentially large/slow database
    migrations at the command line. (For example, one of beta 8's
    migrations changes the width of the tu_posts.post_text field; on my
    12M row table, this took over an hour.) To use the command line tools,
    SSH in your server and CD to ThinkUp's install/cli/ folder. There you
    can run php backup.php or php upgrade.php. The Upgrade script will
    show you the total time elapsed at the end of the migration. If the
    crawler is running when you attempt the migration, the upgrade process
    will let you know and tell you to try again later when the crawl
    process is complete.

**Fixed:**

*   Facebook posts no longer cut off
    Speaking of database migrations, Facebook posts, which can be up to
    420 characters in length, are no longer cut off due to the too-small
    size of ThinkUp's post_text field.

*   Twitter usernames linked correctly
    Thanks to suth's ninja regex skills, ThinkUp more accurately links
    Twitter user names, and doesn't do things like link a lone @ symbol
    mid-tweet.

*   Notification emails less likely to get spammed
    Thanks to Sam, email notifications from ThinkUp have the correct From:
    address set (using your web server's domain name), which makes those
    messages less likely to get shuttled into the spam folder.

*   Invalid Google Maps key error
    When a post is not geoencoded, you will no longer see a JavaScript
    alert about an invalid Google Maps key error when you click on the
    Response Map item in the GeoEncoder plugin menu.

*   Several more little things
    "ThinkUp is in the process of an upgrade" page no longer gets "stuck"
    in cache, JavaScript errors in the switch user dropdown have been
    resolved, the "Your ThinkUp Password" text fields no longer scroll, no
    more error messages when authorizing a Twitter account, the Copyright
    notice is now 2011, lists of links (your own and your friends)
    included the expanded version and now paginate.


Beta 0.7 - 27 Dec 2010
----------------------

**New:**

*   Improved login security: To avoid the potential for brute-force
    password cracking attempts on ThinkUp's login page, there is now a cap
    on the number of failed logins. After 10 failed login attempts, a
    ThinkUp users's account gets deactivated. To reactivate, the user
    resets his/her password via email. (Look for more security-focused
    updates to the system in future releases.)

*   Better retweet crawling: Thanks to Amy, ThinkUp now captures
    the total of new-style retweets more accurately, and displays that
    number plus the number of old-style quoted retweets that ThinkUp
    detects.

*   Tweet reply links: Thanks to Sam, you can now easily reply to a
    given tweet from inside ThinkUp. Rollover any tweet and click on the
    "Reply" link to autofill Twitter's update form with the user name and
    status ID. Screenshot:
    https://skitch.com/ginatrapani/rga5j/ginatrapani-on-twitter-thinkup

*   Picplz support: Thanks to Kyle, photos posted on Twitter from
    http://picplz.com now show up as inline thumbnails in ThinkUp.

*   Tweet photo thumbnails appear on post page: Speaking of image
    thumbnails, they now appear on individual post pages like this one:
    http://smarterware.org/thinkup/post/?t=13426333958807552&n=twitter

*   Configure number of links to expand per crawler run: Thanks to
    Sam, you can now set the number of links the Expand URLs plugin
    attempts per crawler run. This number is 1500 by default and normally
    won't need to be changed. But, if your crawls are taking too long or
    if you've got too many links to expand that aren't happening fast
    enough, you can now dial it up or down in the web interface.
    Screenshot:
    https://skitch.com/ginatrapani/rga5t/configure-your-account-thinkup

*   Followers/Who You Follow lists updated: Twitter's Followers/Who
    You Follow lists have been simplified, and now display some
    interesting stats like how many multiples of followers a user has
    versus friends, and the average number of posts that user has
    published per day since they joined Twitter. Screenshot:
    https://skitch.com/ginatrapani/rga5a/ginatrapani-on-twitter-thinkup

*   New (for developers)! Logger debug mode: Thanks to Amy, developers
    who have debug=true in their config file can write debug statements to
    the log while developing the crawler.

**Fixed:**

*   Gradients in design refresh: Thanks to Andy, everyone on
    every browser sees the new gradients in beta 6's design refresh as we
    intended.

*   Upgrader: Mark fixed a bug that potentially caused problems
    upgrading to ThinkUp's latest version from beta 2. We now have
    automated upgrade tests which run through every single possible
    upgrade path from beta 1 to beta 7 passing.

*   Several more little things: Application options
    have been moved to the generic options table to consolidate our data
    structure; Update your data links no longer throw a 404; Links to
    retweet listings from the Dashboard have been corrected; Plugin
    external libraries are now located in their own extlib folders.



Beta 0.6 - 13 Dec 2010
----------------------

**New:**

*   Favorite tweets: Thanks to an incredible show of perseverance
    by Amy who has been shepherding along this branch since April, ThinkUp
    now captures your favorite tweets (the ones you have starred) and
    lists them under a new Favorites menu on the main dashboard. If you
    like to star tweets with links in them for reading later, you can
    filter your favorites list that way, too. Screenshot:
    https://skitch.com/ginatrapani/rrs81/ginatrapani-on-twitter-thinkup

*   Design refresh: Anil made a few design improvements in this
    release which consolidate the header and status bar, make the sidebar
    menu easier to see and use, remove lots of borders and other clutter,
    and make the replies and retweets buttons more button-like. See the
    new design in action:
    http://smarterware.org/thinkup/

    Note: there are rough spots and CSS/markup mistakes here; I modified
    several of Anil's tweaks so anything that's broken/weird is probably
    my fault. As always, we'll be polishing as we go. CSS mavens, send me
    pull requests with fixes, please. (Please.)

*   Reorganized post page: The post detail page now has a sidebar
    menu just like the dashboard does, a one-stop shop for everything you
    can do with a post, like export replies, search and filter replies,
    list retweets, and see responses on a map. Like the dashboard, plugins
    generate this menu dynamically, which opens the door to
    conversation-specific visualizations and reply listings. Now that the
    stage is set for those kinds of plugins, expect to see more items
    appear in that menu in future releases. Screenshot:
    https://skitch.com/ginatrapani/rrs85/post-details-thinkup

*   Expand/collapse advanced plugin options: A ThinkUp plugin can
    potentially have several settings, and many of them could have default
    values that most users don't need to ever see or change. That's why
    we've set up the ability to hide "advanced" plugin options to simplify
    setup. For example, the only options an admin sees by default for the
    Twitter plugin are the two required values, the rest are nestled away
    comfortably in a hidden div. Just click "Show Advanced Options" to
    reveal them. Screenshot:
    https://skitch.com/ginatrapani/rrs8b/configure-your-account-thinkup

**Fixed:**

*   Twitter inquiries: Thanks to Andy, tweets which contain URLs
    that have question marks in them no longer show up in the Inquires
    list, because they're not questions.

*   New developers tools! Developers can now output custom debugging
    lines while running tests, run an individual test in a given TestCase,
    and see details of a database access error when they set debug = true
    in the config.inc.php file.

*   More little things: Fixed a bug where under certain
    conditions, a user may not get saved to the database correctly. Fixed
    a bug where the web-based crawler page's content-type was not set
    correctly. Fixed a bug where an instance may not get updated correctly
    after a crawl completes. Added a link to the IRC channel to the
    application footer.

Beta 0.5 - 22 Nov 2010
----------------------

**New:**

*   Human readable crawler log: When you click on the "Update now"
    link to run the ThinkUp crawler, the activity log you'll see has been
    totally revamped. You'll have an easier time seeing errors, successes,
    and information about what's working and what's not.

*   Better data integrity: The latest database migration enforces
    some unique indexes which will make sure your datastore is cleaner and
    free of duplicate links and posts. (Related mailing list thread:
    http://groups.google.com/group/thinkupapp/browse_thread/thread/eac7e97f4f81265e)

**Fixed:**

*   Reduced number of Twitter API errors: The order the ThinkUp
    crawler gathers your data from the Twitter API has been adjusted in a
    way that should result in fewer errors and faster data capture. In
    practice, your friends and followers lists will not stay empty for as
    long as they have been anymore. (One of several related mailing list
    threads: http://groups.google.com/group/thinkupapp/browse_thread/thread/cfb9735d6e2ada39/8902e1903b0974ac)

*   The database upgrader: The upgrader now supports custom table
    prefixes (Guillaume, you will be the true test of this fix), and it
    has more understandable messaging about what to do regarding the
    completion email and after the upgrade is complete. (Related mailing
    list messages: http://groups.google.com/group/thinkupapp/msg/021fb00f8f51881e
    http://groups.google.com/group/thinkupapp/msg/9d26ae8574a1b851)

*   Expand URLs hanging bug: The ExpandURLsPlugin used to hang
    indefinitely when it hit a URL that didn't respond quickly enough,
    causing some people to have to deactivate the plugin entirely. The
    timeout has been set so the plugin will move on after a set amount of
    time correctly now.

*   A few more little things: The grid search now works with
    posts which contain Unicode characters, and plugin option errors no
    longer have the endearing but completely uninformative "Sorry, but we
    are unable to process your request at this time" message--instead, you
    get specific details about what's wrong. (Related mailing list thread:
    http://groups.google.com/group/thinkupapp/browse_thread/thread/d4455d0344c8dedd)

Beta 0.4 - 14 Nov 2010
----------------------

**New:**

*   Web-based database upgrader: When you install the new version,
    you'll experience the biggest new ThinkUp feature, our web-based
    database upgrader. Instead of running SQL by hand to update your
    ThinkUp datastore, the app will walk you through the process step by
    step, show you what changes it made, and even give you an option to
    back up your data first. Screenshots here:
    http://www.flickr.com/photos/ginatrapani/sets/72157625383770504/
    This new feature is big and complicated and while we tried our best to
    test every possible scenario, we're depending on you to let us know
    how it goes and report any problems you may have or make any UX
    suggestions. (Thanks in advance for that.)

*   Configurable Twitter API error tolerance: The Twitter API
    serves many fail whales. You can now configure the crawler to tolerate
    up to a certain number of whales--5 by default, but you can increase
    or reduce it now in the plugin settings.
    https://skitch.com/ginatrapani/ryj2n/configure-your-account-thinkup

**Fixed:**

*   Crawler log updates as-it-runs: The "Update now" page updates
    in real-time, instead of spinning and spinning until an entire crawler
    run is complete.

*   Lots of little things: no more exec() PHP warning, the
    WordPress plugin instructions and DB calls are fixed, long URLs now
    wrap correctly, no more bug with deleted accounts because of caching,
    restored missing cache directory causing permissions error, added
    automatic tests for installation and upgrade process, ported several
    tests to the FixtureBuilder library.

Beta 0.3 - 19 Oct 2010
----------------------

**New:**

*   Delete network accounts: If you've added a Twitter or Facebook
    account you want to delete, there's now a handy "Delete" button to do
    so, as shown here:
    http://skitch.com/ginatrapani/dh8na/delete-accounts

*   User-selected timezone: When you install ThinkUp fresh, a
    dropdown of timezones is available for you to choose from, instead of
    the app just defaulting to America/Los_Angeles. This will fix the
    infamous "updated thousands of negative seconds ago" bug that appears
    in the status bar on new installations not located in Southern
    California. Existing users: you should enter your timezone correctly
    by hand into your config.inc.php.
    Warning: the timezone select in the installer is long and scary right
    now. There is an issue filed (#369) to simplify it.

*   Installer attempts to create database: When you install ThinkUp
    fresh, if you enter the name of a database which does not already
    exist, the installer will attempt to create it with the credentials
    you enter. Previously it required that the database already existed.

**Fixed:**

*   Facebook Plugin: Fixed major bugs with new Facebook
    application setup; you can now authorize your FB account and add pages
    you like to ThinkUp with the correct permissions.

*   E_STRICT warnings: If you've got PHP warning set to E_STRICT,
    ThinkUp no longer triggers warnings while developing.

**Refactored:**

*   In preparation for Twitter's new 64-bit "Snowflake" post
    IDs, we've expanded the capacity of ThinkUp's post ID fields.

*   Ported several tests to use our FixtureBuilder library
    instead of raw SQL. The FixtureBuilder lets you create test data very
    easily and using it throughout our tests instead of straight SQL will
    enable us to swap in different DB types and test with custom table
    prefixes later.

Beta 0.2 - 4 Oct 2010
---------------------

*   Facebook Plugin: I gutted all the old Facebook Connect code and
    replaced it with shiny new Open Graph code. The account connection
    experience should be much less bewildering. Give it a try and let me
    know how it goes, especially all of you who have had trouble in beta
    1. Important note for those of you who actually did manage to set up
    Facebook users and pages successfully: the new plugin requires that
    you enter the Facebook Application ID, as shown here:
    http://skitch.com/ginatrapani/d3wu7/configure-your-account-thinkup

*   Twitter plugin: The Twitter API throws a lot of 500 errors (fail
    whales). Amy added a plugin option that lets you set how many whales
    the crawler should tolerate during a given crawl. Also, the crawler
    will now retry a failed API call instead of just moving onto the next
    one. Hopefully this will result in more successful Twitter crawls
    faster. Here's what the new option looks like:
    http://skitch.com/ginatrapani/d34x9/configure-your-account-thinkup

*   Inquiries: See only posts that you've asked an actual question in,
    which often prompts more replies. There's no fancy natural language
    processing going on here, so it's not always perfect, but the new
    "Inquiries" post listing only displays posts that contain question
    marks. An example in action:
    http://smarterware.org/thinkup/index.php%3Fv%3Dtweets-questions%26u%3Dginatrapani%26n%3Dtwitter

Beta 2 also contains several minor bugfixes that restore broken links,
tweak the design consistency, and remove a significant amount of code
that was no longer being used. (This is why the .zip file is smaller
than beta 1's was.)

Beta 0.1 - 27 Sept 2010
-----------------------

This is the last "drop tables and reinstall" release. From now on, you
will be able to upgrade your database smoothly from version to
version.

The major difference between the last alpha and first beta is the UI
interface overhaul discussed here:
http://groups.google.com/group/thinkupapp/browse_thread/thread/9f12e013ee2c4751

Otherwise, compared to the 0.008 alpha, beta 0.1 includes a very long
laundry list of bugfixes and updates. You can see the complete
changelog here:
http://github.com/ginatrapani/thinkup/compare/v0.008...v0.1

