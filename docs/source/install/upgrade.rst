Upgrade ThinkUp
===============

To upgrade your existing installation to the latest version of ThinkUp, simply replace your current ThinkUp folder with
the most recent release while preserving your existing configuration file.

.. Tip:: First things first: back up ThinkUp's data. Before you begin, :doc:`back up your current ThinkUp installation's
    data </install/backup>` in case anything goes wrong during the upgrade process. 

To upgrade ThinkUp, you'll need to update your installation's application code and its database structure.

Update ThinkUp's Application Code
---------------------------------

First, `download ThinkUp's latest release <http://thinkupapp.com/downloads/>`_ and extract the zip archive on your
computer.  Then, log into your ThinkUp installation as an administrator. 

Using your favorite FTP program, connect to your web hosting provider, and rename your existing ThinkUp folder to
something like ``thinkup.old``. Then, upload the new ThinkUp folder you just extracted from your computer to your web
server.

Finally, copy your existing configuration file--i.e., ``thinkup.old/config.inc.php``--into the new ThinkUp folder.

Reload ThinkUp in your web browser. Follow the on-screen instructions on how to set :doc:`ThinkUp's required folder
permissions </install/perms>` in your updated installation.

Update ThinkUp's Database Structure
-----------------------------------

Now that your installation has the most up-to-date code, that code may have to update your database structure to match
it. Relaod ThinkUp in your web browser. When you see the message "ThinkUp's database needs an update" click on the 
"Update Now" link.

If the message reads "ThinkUp is currently in the process of upgrading. Please try back again in a little while", here's
:doc:`how to continue the upgrade process </troubleshoot/messages/upgrading>`.

Small Databases: Web-Based Upgrade
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The Upgrade page will let you know how many database migrations have to run to get up-to-date. 

If your ThinkUp installation only has 1 or 2 moderately active social media accounts set up in it, and none of your
database tables have more than half a million rows, then you should use the easy web-based upgrader. ThinkUp will let
you know if any of your tables are this large when you begin the upgrade process.

Click on the "Update now" button to update ThinkUp's database structure.

Large Databases: Command Line Upgrade
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If your ThinkUp installation has more than 2 very active social media accounts set up, chances are your database tables
are large. (We consider a ThinkUp database with any table over half a million rows large.)

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
after you update ThinkUp's files from git, you'll need to catch up on any necessary database migrations.

As of beta 16 (v0.16), developers can use the CLI upgrade tool to run any new database migrations using the argument
"--with-new-sql":

``$ php upgrade.php --with-new-sql``

The CLI tool will keep track of any migrations that have been applied and only run new migrations. Developers can just
run the tool with the "--with-new-sql" option to get their install up to date. This also applies to migration files
rolled into the release builds.