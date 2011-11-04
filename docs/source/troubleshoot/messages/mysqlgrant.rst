The MySQL user does not have the proper permissions to grant backup/export access
=================================================================================

The Thinkup MySQL user needs GRANT permission to backup or export data. Make sure the MySQL user has been granted both
FILE and LOCK TABLE permissions to the ThinkUp database. 
`Find out more about MySQL GRANT permission <http://dev.mysql.com/doc/refman/5.1/en/grant.html>`_.
