It looks like the mysql user does not have the proper permissions to grant Backup|Export Access
==========================

The Thinkup MySQL user does not have the proper GRANT permissions to Backup or Export data. Make Sure the MySQL
user has been granted both FILE and LOCK TABLE permissions. More info about MySQL GRANT permisions can be found here:
http://dev.mysql.com/doc/refman/5.1/en/grant.html