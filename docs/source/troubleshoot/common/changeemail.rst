Can't change my account's email address
=======================================

If you find your email isn't being received and you are sure it's because of the destination address, you can change
it in the database. 

You will need to log in to mysql locally or via ssh:

``$ mysql -uroot -p  database_name (assuming user is root, you will be prompted for the password)``

The first statement will show you all owners in the database. The second line will change the email and set the owner
as activated; This is fin if there's a single owner in the table.
   
``mysql> select * from tu_owners;``

``mysql> update tu_owners set email='your_new_email@somewhere.com', is_activated = '1' ;``

If there are plural owners in the table, you'll need to find the actual owner record and update it:

``mysql> update tu_owners set email='your_new_email@somewhere.com', is_activated = '1' WHERE 
email='oldbad_email@somewhere.com' LIMIT 1;``
