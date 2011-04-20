Upgrade
=======

If you've already got ThinkUp beta 0.1 or above installed, upgrading to the newest version is as simple as overwriting
your existing files with the latest version and using ThinkUp's automatic upgrader. Here's how.

Back Up Your Data
-----------------

To be on the safe side, first you should back up your current ThinkUp data.

**Web-based Database Backup**

To use ThinkUp's web-based database backup tool, visit http://yourthinkupinstall/install/backup.php on your ThinkUp
installation (substituting in your domain name and path) and click on the "Backup Now" button.

The web based backup tool has two permissions requirements. 

1. Your ThinkUp installation's database user must have "GRANT FILE ON" permissions
2. The MySQL user must have write permissions to the ``thinkup/_lib/view/compiled_view`` directory.

**Manual Database Backup**

If you don't have the correct permissions to run ThinkUp's web-based backup tool, you can back up your database
manually. To do so, at your server's command line or using a tool like phpMyAdmin, run a mysqldump of your ThinkUp
database to file.

Install Updated Files
----------------------

Now, `download ThinkUp's latest version <http://thinkupapp.com>`_, extract the zip archive, and overwrite your
existing ThinkUp folder with the new ones on your web server your web server using FTP or SCP. 

Upgrade ThinkUp's Data Structure
--------------------------------

Finally, visit your ThinkUp installation to walk through any structual updates to the database the newest version
requires.


Developers Running Nightly Code
-------------------------------

If you're a developer running nightly code from ThinkUp's git repository, after you update ThinkUp's files from git,
make sure you run any necessary database migrations by hand. Look for those in the 
thinkup/install/sql/mysql_migrations/ folder. It's up to you to keep track of which migrations you've run by hand,
and which you need to run.

If you're updating to a new release of ThinkUp and you've run all the database migrations manually already, all you
have to do is update ThinkUp's version number in the database. Do this by running the following query on your ThinkUp
database (first replace xxx with the current version you're upgrading to):

``UPDATE tu_options SET option_value='xx' WHERE namespace='application_options' AND option_name='database_version';`` 

