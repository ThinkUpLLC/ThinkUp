Back Up and Export
==================

ThinkUp provides two tools for exporting and restoring your data: Backup and Export.

Back Up and Restore ThinkUp's Entire Database
---------------------------------------------

ThinkUp provides both a web-based and command line tool for backing up your installation's data. The best method
depends on how large your ThinkUp installation's database has grown.

**Small Databases: Web-Based Backup (Logged-in admin only)** 

If your ThinkUp installation only has 1 or 2 moderately active social media accounts set up in it, and none of your
database tables have more than a half million rows, then you should use the easy web-based backup tool. ThinkUp will
let you know if a table is larger than that when you begin the backup process.

To use the web-based backup tool, log into ThinkUp as an administrator. Under :doc:`Settings>Application
</userguide/settings/application>`, click on 
the "Backup ThinkUp's database" link. On the Backup page, click on the "Backup Now" button.

The web based backup tool has two permissions requirements. 

1. Your ThinkUp installation's database user must have "GRANT FILE ON" permissions
2. The MySQL user must have write permissions to the data directory (``data`` by default, or defined in 
   ``config.inc.php``'s ``$THINKUP_CFG['datadir_path']`` value).

If you don't have those permissions, you can use `mysqldump` or a tool like phpMyAdmin to back up your database
manually.

When running a web-based backup, here's what to do if you see the error :doc:`Can't create/write to file
</troubleshoot/common/backupcannotwrite>`.

**Large Databases: Command Line Backup** 

If your ThinkUp installation has more than 2 very active social media accounts set up, chances are your database tables
are large. (We consider a ThinkUp database with any table over half a million rows large.)

Depending on your server speed and utilization, it can take a very long time for database structure updates to 
complete on very large installations; so the web-based backup tool can time out. To be on the safe side,
large installation administrators should use the command line backup tool to avoid potential time-outs.

To use the CLI backup, SSH into your web server and ``cd`` into the ``thinkup/install/cli/`` folder.
Then, run:

``$ php backup.php``

This command will back up your current database.

**Restore Your ThinkUp Backup**

In :doc:`Settings>Application
</userguide/settings/application>`, you can upload a ThinkUp backup file under the "Restore Your Thinkup Database."
Click on the "Choose File" button to upload your ThinkUp backup file and restore it. This restore operation will
overwrite your entire existing database; use with caution.

Export a Single Service User's Data
-----------------------------------

If you want to move a single service user's ThinkUp archive to another ThinkUp installation--if, say, your database
has become too big and unwieldy, or a user has set up a new ThinkUp installation and wants to import their
existing archive--you can do that.

Under :doc:`Settings>Application
</userguide/settings/application>`, click on the "Export a single service user's data" link. Choose a service user
to export and click on the "Export User Data" button.

You will download a zip file. Extract it, and refer to the README.txt contained inside that zip file for how to import
the data into another ThinkUp installation.

When to Back Up and When to Export
----------------------------------

ThinkUp's Backup tool exports the entire database, including internal database ID's, to a file. Use the backup
tool when you are starting with a completely fresh, new database and want to restore everything: including ThinkUp
users, passwords, and plugin settings.

ThinkUp's Service User Export tool only exports the data associated with a particular service user, without internal
ID's: posts, friends, followers, links, mentions, replies, retweets, and favorites. This export file can be imported
into an existing ThinkUp installation with established ThinkUp users and existing service users. Because the export
file doesn't include internal ID's, the data will be appended to existing data rather than replacing the entire 
database. Use the Export tool when you only want to transfer a single service user to another ThinkUp installation.
