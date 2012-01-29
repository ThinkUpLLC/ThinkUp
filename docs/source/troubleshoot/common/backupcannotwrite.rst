Cannot back up or export data
=============================

While running a database backup or export either during the test run or using the application, you may get the error:

``It looks like the MySQL user does not have the proper permissions to grant back up/export access``

or 

``It looks like the MySQL user does not have the proper file permissions to back up/export data``

or

``PDOException: SQLSTATE[HY000]: General error: 1 Can't create/write to file 
'<thinkup>/data/backup/tu_encoded_locations.txt' (Errcode: 13)``

ThinkUp's backup or export tool doesn't have the permissions it needs to back up your files. Make sure that the MySQL 
user has GRANT FILE and LOCK TABLE privileges in the database as well as write privileges to :doc:`ThinkUp's data 
directory </install/perms>`.

`Find out more about MySQL GRANT permission <http://dev.mysql.com/doc/refman/5.1/en/grant.html>`_.

If you still get the error, make sure that there aren't restrictive permissions set in any of the 
compiled_view folder's parent folders up the tree which would keep the MySQL user from writing to that directory.
