Can't create/write to file
==========================

While running a database backup either during the test run or using the application, you may get the error:

``PDOException: SQLSTATE[HY000]: General error: 1 Can't 
create/write to file '<thinkup>/webapp/_lib/view/compiled_view/tu_encoded_locations.txt' (Errcode: 13)``

ThinkUp's backup tool doesn't have the permissions it needs to back up your files. Make sure that the mysql user
has GRANT FILE ON privileges in the database as well as write privileges to the compiled_view directory. If you still
get the error, make sure that there aren't restrictive permissions set in any of the compiled_view folder's 
parent folders up the tree which would keep the mysql user from writing to that directory.