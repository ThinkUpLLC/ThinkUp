My web server cannot send email
===============================

We strongly recommend running ThinkUp on a web server which can send email.

If your web host is unable to send email via `PHP's mail function <http://php.net/manual/en/function.mail.php>`_, 
several ThinkUp functions are affected: 

* You won't receive the initial account activation email during ThinkUp installation
* New users will not receive account activation email when they fill out the registration form
* You won't receive the authorization link to upgrade your ThinkUp installation when updating the application
* Users won't receive an email to reset their password when using the "Forgot Password" link

You can manuallly activate user accounts by setting the is_activated field equal to 1 in ThinkUp's owners table.

A.  How to manually access the MySQL database via the terminal(command-line)

Likely you will not be root on a host system, so you will need to have been created in the mysql database as a user and have created a database with a name of your own choosing.

1.  Login to your database at the command line

 mysql -u=<database owner> -p=<password>

 ***** Output ********************************
 Welcome to the MySQL monitor. Commands end with ; or \g.
 Your MySQL connection id is 135
 Server version: 5.1.41-3ubuntu12.10 (Ubuntu)
 Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.
 *********************************************

2.  Change to your database

mysql> use <database name you created or the thinkup install created for you>

 [ Hint: if you don't know the name then type this at the mysql>  show databases;  ]        

 mysql>  use thinkup;

 ***** Output ********************************
 Reading table information for completion of table and column names
 You can turn off this feature to get a quicker startup with -A
 Database changed
 *********************************************

3.  Check the value of the is_activated column 

mysql> select id,full_name, is_activated from tu_owners;
+----+---------------+--------------+
| id | full_name | is_activated |
+----+---------------+--------------+
| 1 | Rick Ehlinger | 0 |
+----+---------------+--------------+
1 row in set (0.00 sec) 

4.  Change the value of the is_activated column to 1

mysql> update tu_owners set is_activated=1 where id=<the id of the person you are activating>;


Query OK, 1 row affected (0.00 sec)
Rows matched: 1 Changed: 1 Warnings: 0


mysql> commit;

Query OK, 0 rows affected (0.00 sec)


5.  Check the value of is_activated one last time

mysql> select id,full_name, is_activated from tu_owners;

+----+---------------+--------------+
| id | full_name | is_activated |
+----+---------------+--------------+
| 1 | Rick Ehlinger | 1 |
+----+---------------+--------------+
1 row in set (0.00 sec)

6.  Exit to the command prompt

mysql> exit


B.  How to access the MySQL database via phpMyAdmin



TODO Fill in how to access the MySQL database via phpMyAdmin