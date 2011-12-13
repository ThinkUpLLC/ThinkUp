PDOException: SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
=============================================================================

ThinkUp is a data-intensive application that generates large tables and keeps the database connection open for
extended periods of time during its crawl and queries. Therefore, tracking busy accounts on 
low-powered shared web hosting packages in ThinkUp can result in database timeouts.

.. sidebar:: Coming Soon

    We're working on a fix that will make future versions of ThinkUp able to auto-recover from this error and
    re-establish database connectivity regardless of timeout.


To fix the "MySQL server has gone away" error, contact your web host about increasing your server's timeout
configuration to the maximum value. Here's more information:

http://dev.mysql.com/doc/refman/5.0/en/gone-away.html

If that's not possible, export your ThinkUp data and install ThinkUp in a server environment with more liberal timeouts.

