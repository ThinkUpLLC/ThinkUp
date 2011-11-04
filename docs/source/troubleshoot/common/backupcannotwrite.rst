Can't Backup or Export Data
===========================

While running a database backup or export either during the test run or using the application, you may get the error:

``It looks like the mysql user does not have the proper permissions to grant Backup|Export Access``

or 

``It looks like the mysql user does not have the proper file permissions to Backup|Export Data``

or

``PDOException: SQLSTATE[HY000]: General error: 1 Can't create/write to file 
'<thinkup>/webapp/_lib/view/compiled_view/tu_encoded_locations.txt' (Errcode: 13)``

ThinkUp's backup or export tool doesn't have the permissions it needs to back up your files. Make sure that the mysql 
user has GRANT FILE and LOCK TABLE privileges in the database as well as write privileges to the compiled_view 
directory.

More info about GRANTing mysql user privileges can be found here: http://dev.mysql.com/doc/refman/5.1/en/grant.html

If you still get the error, make sure that there aren't restrictive permissions set in any of the 
compiled_view folder's parent folders up the tree which would keep the mysql user from writing to that directory.