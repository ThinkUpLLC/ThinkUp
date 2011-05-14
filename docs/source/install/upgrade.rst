Upgrade ThinkUp
===============

If you're already running ThinkUp beta 0.1 or higher, to upgrade to the latest version you simply replace
your ThinkUp folder with the new one while preserving your existing configuration file. Then, use ThinkUp's
automatic database backup and upgrader. Here's how.

Install ThinkUp's Newest Application Code
-----------------------------------------

.. sidebar:: Update Notifications as of Beta 12

    From ThinkUp beta 12 on, you can find out if there's a new version of the application available by logging in as an
    administrator. If there's a newer version than the one you're currently running available for download, you'll 
    see a message on the right side of ThinkUp's status bar, between "Logged in as admin" and the "Settings" link. 
    
    For example, If you're running beta 12 and beta 13 is available, the message will read "Version 0.13 available." 
    Click on it to download beta 13.

First, `download ThinkUp's latest version <http://thinkupapp.com>`_ and extract the zip archive.

Using your favorite FTP program, rename your existing ThinkUp folder to something like ``thinkup.old``. Then, upload
the new ThinkUp folder you just extracted.

Then, copy your existing configuration file--i.e., ``thinkup.old/config.inc.php``--into the new ThinkUp folder.

Your ThinkUp application code is now up-to-date. Great!

Next, you will back up your database and upgrade it to the latest version. The best method depends on how large your 
ThinkUp installation's database has grown.

Small Databases: Web-Based Backup and Upgrade
---------------------------------------------

If your ThinkUp installation only has 1 or 2 moderately active social media accounts set up in it, and none of your
database tables have more than 1 million rows, then you should use the easy web-based backup and upgrade tool. (Hint:
you can see the sizes of your tables using a tool like phpMyAdmin or the ``mysql`` command line tool.)

To use the web-based backup and upgrader, visit your newly-updated ThinkUp installation in your web browser. 
It will prompt you to run any necessary database upgrades, with the option to back up your existing database first. 
(To do that, click on the "Backup Now" button.)

The web based backup tool has two permissions requirements. 

1. Your ThinkUp installation's database user must have "GRANT FILE ON" permissions
2. The MySQL user must have write permissions to the ``thinkup/_lib/view/compiled_view`` directory.

If you don't have those permissions, you can use `mysqldump` or a tool like phpMyAdmin to back up your database
manually.

Large Databases: Command Line Backup and Upgrade
------------------------------------------------

If your ThinkUp installation has more than 2 very active social media accounts set up, chances are your database tables
are large. (We consider a ThinkUp database with any table over 1 million rows large.)

Depending on your server speed and utilization, it can take a very long time for database structure updates to 
complete on very large installations; so the web-based upgrade and backup tool can time out. To be on the safe side,
large installation administrators should use the command line backup and upgrader to avoid potential time-outs..

To use the CLI backup and upgrader, SSH into your web server and ``cd`` into the ``thinkup/install/cli/`` folder.
Then, run:

``$ php upgrade.php``

This command will tell you whether or not you need a database update, and should you choose to proceed with it, give
you the option to back up your current data first.

Developers Running Nightly Code
-------------------------------

If you're a developer running nightly code from ThinkUp's git repository, after you update ThinkUp's files from git,
make sure you run any necessary database migrations by hand. Look for those in the 
``thinkup/install/sql/mysql_migrations/`` folder. It's up to you to keep track of which migrations you've run by hand,
and which you need to run.

If you're updating to a new release of ThinkUp and you've run all the database migrations manually already, all you
have to do is update ThinkUp's version number in the database. Do this by running the following query on your ThinkUp
database (first replace x.xx with the current version you're upgrading to):

``UPDATE tu_options SET option_value='x.xx' WHERE namespace='application_options' AND option_name='database_version';``
