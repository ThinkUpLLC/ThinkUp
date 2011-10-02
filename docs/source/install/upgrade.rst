Upgrade ThinkUp
===============

If you're already running ThinkUp beta 0.1 or higher, to upgrade to the latest version, simply replace
your ThinkUp folder with the new one while preserving your existing configuration file. Here's how.

First Things First: Back Up ThinkUp's Data
------------------------------------------

.. sidebar:: Update Notifications as of Beta 12

    From ThinkUp beta 12 on, you can find out if there's a new version of the application available by logging in as an
    administrator. If there's a newer version than the one you're currently running available for download, you'll 
    see a message on the right side of ThinkUp's status bar, between "Logged in as admin" and the "Settings" link. 
    
    For example, If you're running beta 12 and beta 13 is available, the message will read "Version 0.13 available." 
    Click on it to download beta 13.

Before you begin, :doc:`back up your current ThinkUp installation's data </install/backup>`. 

Install ThinkUp's Newest Application Code
-----------------------------------------

Log into your ThinkUp installation as an administrator. Then, `download ThinkUp's latest version
<http://thinkupapp.com>`_ and extract the zip archive.

Using your favorite FTP program, rename your existing ThinkUp folder to something like ``thinkup.old``. Then, upload
the new ThinkUp folder you just extracted.

Then, copy your existing configuration file--i.e., ``thinkup.old/config.inc.php``--into the new ThinkUp folder. Finally,
set :doc:`ThinkUp's required folder permissions </install/perms>` in your fresh installation.

Upgrade ThinkUp's Database Structure
------------------------------------

Now that your installation has the most up-to-date code, that code may have to update your database structure to match
it. Visit your ThinkUp installation and if you see the message "ThinkUp's database needs an update" click on the 
"Update Now" link.

If the message reads "ThinkUp is currently in the process of upgrading. Please try back again in a little while", here's
:doc:`how to continue the upgrade process </troubleshoot/messages/upgrading>`.

**Small Databases: Web-Based Upgrade**

The Upgrade page will let you know how many database migrations have to run to get up-to-date. 

If your ThinkUp installation only has 1 or 2 moderately active social media accounts set up in it, and none of your
database tables have more than 1 million rows, then you should use the easy web-based upgrader. (Hint:
you can see the sizes of your tables using a tool like phpMyAdmin or the ``mysql`` command line tool.)

Click on the "Update now" page to update ThinkUp's database structure.

**Large Databases: Command Line Upgrade**

If your ThinkUp installation has more than 2 very active social media accounts set up, chances are your database tables
are large. (We consider a ThinkUp database with any table over 1 million rows large.)

Depending on your server speed and utilization, it can take a very long time for database structure updates to 
complete on very large installations; so the web-based upgrader can time out. To be on the safe side,
large installation administrators should use the command line upgrader to avoid potential time-outs..

To use the CLI upgrader, SSH into your web server and ``cd`` into the ``thinkup/install/cli/`` folder.
Then, run:

``$ php upgrade.php``

This command will upgrade your database structure (and give you the option to back it up first as well).

Your ThinkUp application code and database is now up-to-date. Great!

Developers Running Nightly Code
-------------------------------

If you're a developer :doc:`running nightly code from ThinkUp's git repository </contribute/developers/devfromsource>`,
after you update ThinkUp's files from git, make sure you run any necessary database migrations by hand. Look for
those in the ``thinkup/install/sql/mysql_migrations/`` folder. It's up to you to keep track of which migrations you've
run by hand, and which you need to run.

If you're updating to a new release of ThinkUp and you've run all the database migrations manually already, all you
have to do is update ThinkUp's version number in the database. Do this by running the following query on your ThinkUp
database (first replace x.xx with the current version you're upgrading to):

``UPDATE tu_options SET option_value='x.xx' WHERE namespace='application_options' AND option_name='database_version';``

Upgrading A Development Database
-------------------------------

As of version 0.16 beta, developers can use the CLI upgrade tool to run any new database migrations using the argument "--with-new-sql":

``$ php upgrade.php --with-new-sql``

The CLI tool will keep track of any migrations that have been applied and only run new migrations. Developers can just run the tool with the "--with-new-sql" option to get their install up to date. This also applies to migration files rolled into the release builds.
