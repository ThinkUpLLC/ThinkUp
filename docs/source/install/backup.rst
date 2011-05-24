Back Up ThinkUp's Data
======================

ThinkUp provides both a web-based and command line tool for backing up your installation's data. The best method
depends on how large your ThinkUp installation's database has grown.

Small Databases: Web-Based Backup (Logged-in admin only)
--------------------------------------------------------

If your ThinkUp installation only has 1 or 2 moderately active social media accounts set up in it, and none of your
database tables have more than 1 million rows, then you should use the easy web-based backup tool. (Hint:
you can see the sizes of your tables using a tool like phpMyAdmin or the ``mysql`` command line tool.)

To use the web-based backup tool, log into ThinkUp as an administrator. Under :doc:`Settings>Application
</userguide/settings/application>`, click on 
the "Backup ThinkUp's database" link. On the Backup page, click on the "Backup Now" button.

The web based backup tool has two permissions requirements. 

1. Your ThinkUp installation's database user must have "GRANT FILE ON" permissions
2. The MySQL user must have write permissions to the ``thinkup/_lib/view/compiled_view`` directory.

If you don't have those permissions, you can use `mysqldump` or a tool like phpMyAdmin to back up your database
manually.

When running a web-based backup, here's what to do if you see the error :doc:`Can't create/write to file
</troubleshoot/common/backupcannotwrite>`.

Large Databases: Command Line Backup
------------------------------------

If your ThinkUp installation has more than 2 very active social media accounts set up, chances are your database tables
are large. (We consider a ThinkUp database with any table over 1 million rows large.)

Depending on your server speed and utilization, it can take a very long time for database structure updates to 
complete on very large installations; so the web-based backup tool can time out. To be on the safe side,
large installation administrators should use the command line backup tool to avoid potential time-outs..

To use the CLI backup, SSH into your web server and ``cd`` into the ``thinkup/install/cli/`` folder.
Then, run:

``$ php backup.php``

This command will back up your current database.
