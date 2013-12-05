How to Modify ThinkUp's Database Structure
==========================================

If you need to alter the structure of the ThinkUp database, you'll need to take
the following steps:

Step 1. Create Your Migration Script
------------------------------------

In the webapp/install/sql/mysql\_migrations/ folder, create a new .sql file. The name
should include the date, issue number, and a short description of what
you're doing.

For example, if I'm altering the database for my work on issue #200 on
May 3, 2010 and I want to add a field called my_field to the posts
table, I'd create a file called:
2010-05-03_add-myfield-to-posts\_issue200.sql.

In that file, add the SQL alter statements. For example, 

:: 

    ALTER TABLE tu_posts ADD myfield VARCHAR ( 255 ) NOT NULL;.

Step 2. Regenerate the Database Creation Script
-----------------------------------------------

Once you have confirmed that your migration script works, regenerate the
sql/build-db\_mysql-upcoming-release.sql file using the automated migratedb shell script.

**Never edit the build-db\_mysql.sql file by hand.**

To do so, run the extras/scripts/migratedb script at the command line.
First you'll need to create and edit your configuration file. Check out
the
`README <http://github.com/ginatrapani/ThinkUp/tree/master/extras/scripts/>`_
for instructions on how to do that.

Run ThinkUp's tests to make sure the database creation script works.
When you commit your work, make sure you add both the new migration
file, and the auto-generated build-db\_mysql-upcoming-release.sql file.

Step 3. 
-----------------------------------------------

There is no step 3.
