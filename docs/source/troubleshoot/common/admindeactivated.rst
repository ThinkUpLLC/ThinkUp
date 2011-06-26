My administrator account is deactivated
=======================================

Normally an administrator can reactivate deactivated user accounts in 
:doc:`Settings > All Accounts </userguide/settings/allaccounts>`. However, that's not
possible if the administrator's account has been deactivated. Manually reactivate an administrator's ThinkUp account
by setting the is_activated field equal to 1 in the ThinkUp installation's owners table.


1.  Login to phpMyAdmin with your username and password for your account

.. image:: http://www.freeimagehosting.net/uploads/7c537547e4.png

2.  Click on the thinkup database in the left hand column - whatever you named it

.. image:: http://www.freeimagehosting.net/uploads/7cb8c55813.png

3.  Click on the tu_owners table in the left hand column

.. image:: http://www.freeimagehosting.net/uploads/14c16cf9b4.png



4.  Choose the line in the right hand column you want to edit and click Edit that is in that line

.. image:: http://www.freeimagehosting.net/uploads/1804661445.png

5. Look for the is_activated column

.. image:: http://www.freeimagehosting.net/uploads/914eb7a300.png

6.  Change the 0 to a 1 in the is_activated column. and click Go
 
.. image:: http://www.freeimagehosting.net/uploads/1041b1757e.png

7.  Looking at the line you changed the is_activated column should now have a 1 in it.

.. image:: http://www.freeimagehosting.net/uploads/2183ac542e.png


TODO Fill in how to manually access the MySQL database via the terminal 













