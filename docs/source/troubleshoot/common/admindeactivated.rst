My administrator account is deactivated
=======================================

Normally an administrator can reactivate deactivated user accounts in 
:doc:`Settings > All Accounts </userguide/settings/allaccounts>`. However, that's not
possible if the administrator's account has been deactivated. Manually reactivate an administrator's ThinkUp account
by setting the is_activated field equal to 1 in the ThinkUp installation's owners table.


**A.  How to manually access the MySQL database via the terminal**

Likely you will not be root on a host system, so you will need to have been created in the mysql database as a user and have created a database with a name of your own choosing or the name of the default database installed by the thinkup install script.

1.  **Login to your database at the command line.**

 mysql -u<database owner> -p<password>

     Welcome to the MySQL monitor. Commands end with ; or \g.
    
      Your MySQL connection id is 135
    
     Server version: 5.1.41-3ubuntu12.10 (Ubuntu)
    
     Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.
    
2.  **Change to your database.**

mysql> use <database name you created or the thinkup install created for you>

 [ Hint: if you don't know the name then type this at the mysql>  show databases;  ]        

 mysql>  use thinkup

     Reading table information for completion of table and column names
    
     You can turn off this feature to get a quicker startup with -A
    	
    
     Database changed

3.  **Check the value of the is_activated column.** 

mysql> select id,full_name, is_activated from tu_owners;

    +----+---------------+--------------+
    
    | id | full_name | is_activated |
    
    +----+---------------+--------------+
    
    | 1 | Rick Ehlinger | 0 |
    
    +----+---------------+--------------+

1 row in set (0.00 sec) 

4.  **Change the value of the is_activated column to 1 AND commit.** 

mysql> update tu_owners set is_activated=1 where id=<the id of the person you are activating>;

    Query OK, 1 row affected (0.00 sec)
    
    Rows matched: 1 Changed: 1 Warnings: 0

mysql> commit;

    Query OK, 0 rows affected (0.00 sec)

5.  **Check the value of is_activated one last time.**

mysql> select id,full_name, is_activated from tu_owners;

    +----+---------------+--------------+
    
    | id | full_name | is_activated |
    
    ++----+---------------+--------------+
    
    | 1 | Rick Ehlinger | 1 |
    
    +----+---------------+--------------+

    1 row in set (0.00 sec)
	

6. ** Exit to the command prompt.**

mysql> exit


**B.  How to access the MySQL database via phpMyAdmin**

1.  **Login to phpMyAdmin with your username and password for your account.**

.. image::  http://www.freeimagehosting.net/uploads/7c537547e4.png

2.  **Click on the thinkup database in the left hand column - whatever you named it**

.. image:: http://www.freeimagehosting.net/uploads/7cb8c55813.png

3.  **Click on the tu_owners table in the left hand column.**

.. image:: http://www.freeimagehosting.net/uploads/14c16cf9b4.png

4.  **Choose the line in the right hand column you want to edit and click Edit that is in that line.**

.. image:: http://www.freeimagehosting.net/uploads/1804661445.png
**
5. Look for the is_activated column.**

.. image:: http://www.freeimagehosting.net/uploads/914eb7a300.png

6. ** Change the 0 to a 1 in the is_activated column. and click Go.**
 
.. image:: http://www.freeimagehosting.net/uploads/1041b1757e.png

7.  L**ooking at the line you changed the is_activated column should now have a 1 in it.**

.. image:: http://www.freeimagehosting.net/uploads/2183ac542e.png

