Common Problems and Solutions
=============================

ThinkUp is a work in progress, and certain known problems which can occur on different server setups have simple 
solutions. Here's a list. If you've got one which doesn't appear here, please add it to this page.

Account data always out of date
-------------------------------

ThinkUp won't update itself unless you have a scheduled task running on the server or are requesting the crawler's RSS
feed. Instructions for setting up both appear on the crawler update page. Click on the last updated date in ThinkUp's
status bar and see the instructions at the bottom of the page.

Crawler constantly encounters Twitter API errors (ERROR 502)
------------------------------------------------------------

Twitter's API serves a lot of "fail whales", or HTTP errors. Every time ThinkUp encounters one, you will see it listed
in red in your crawler log. By default, the ThinkUp crawler will give up after receiving 5 error responses from
Twitter.com. To increase the number of errors ThinkUp tolerates, in the Twitter plugin's advanced options area,
increase the number to something higher (like 50).

Friends/followers or other Twitter data never shows up no matter how many times ThinkUp crawls
----------------------------------------------------------------------------------------------

Twitter's API serves a lot of "fail whales", or HTTP errors. Every time ThinkUp encounters one, you will see it listed
in red in your crawler log. By default, the ThinkUp crawler will give up after receiving 5 error responses from
Twitter.com. To increase the number of errors ThinkUp tolerates, in the Twitter plugin's advanced options area,
increase the number to something higher (like 50). That will help the crawler run longer and progress beyond tweets
and mentions to your social graph.

Can't add a Facebook page to ThinkUp
------------------------------------

ThinkUp's Facebook plugin works with Facebook pages, but it can only connect with regular Facebook user accounts.
To add a Facebook page, connect a regular Facebook user account to ThinkUp. Make sure that user "likes" the page, and
then add it to ThinkUp from the Likes dropdown in ThinkUp.

Something's going wrong during crawls, but the log on the updatenow.php page doesn't give enough information
------------------------------------------------------------------------------------------------------------

To closely troubleshoot crawler activity, `enable the crawler's verbose developer log
<https://github.com/ginatrapani/ThinkUp/wiki/Configuration:-Enable-the-crawler's-verbose-developer-log>`_,
which provides detailed information like memory usage, class and method names, and line numbers.

Error starting crawler; another crawl is already in progress
------------------------------------------------------------

ThinkUp's crawler won't start if a previous crawl process is still running. If you run into this error, make sure you
wait for the first crawl to complete and try again. If you constantly get this error always and ThinkUp's data isn't
updating, something is wrong. There are a few things you can try:

1. Run `ps -ax | grep crawl` on your server and manually kill any crawler process you see there. Then, delete the
crawl.pid file from the crawler/logs/ directory.
2. Restart your MySQL server to clear away any MUTEX locks which are being held.
3. More troubleshooting on `the mailing list 
<http://groups.google.com/group/thinkupapp/browse_thread/thread/cb5c3c8b9a98bef6/04c2f1e6ee24f59f>`_.

"Fatal error: Allowed memory size of XXXX bytes exhausted (tried to allocate 16 bytes)"
---------------------------------------------------------------------------------------

ThinkUp's crawler script can require a lot of memory due to all the data certain APIs return; at times, more memory
than PHP is allocated. If you run into this error, set ThinkUp to allow scripts to use more memory. To do so, add the
following line anywhere in your config.inc.php file:

``ini_set('memory_limit', '32M');``

"Fatal error: Maximum execution time of 30 seconds exceeded"
------------------------------------------------------------

ThinkUp's crawler script can take a long time because it can make hundreds of requests to external web sites during
the course of one crawl. By default PHP is set to only allow a script take 30 seconds. If you run into this error,
you must set ThinkUp to allow scripts to run longer. To do so,  add the following line anywhere in your
config.inc.php file
``set_time_limit ( 500 );``

"SSL certificate problem, verify that the CA cert is OK"
--------------------------------------------------------

Best solution: update cURL's SSL certificates on your server. Less secure workaround: set cURL to not verify HTTPS
connections.

`Mailing list thread about SSL certs <http://groups.google.com/group/thinkupapp/browse_thread/thread/b86557dbd6747ee7>`_

Exporting posts outputs gibberish
----------------------------------

This is a problem with non-Latin characters written to the datastore using a latin1 PDO connection but read using
UTF-8. To fix it, make sure 'set_pdo_charset' is set to true in your config file to set the PDO connection to UTF-8.
To convert an existing datastore to UTF-8, do the following:

(Notes: This won't work on Windows due to UTF-8 incompatibilities.)

1. Back up your DB the normal way just in case you need to revert to this.

``mysqldump --opt thinkupDB > thinkup.sql``

2. Back up your DB using latin1.  This will decode the gibberish into proper "text".

``mysqldump --opt --default-character-set=latin1 thinkupDB > thinkup.latin.sql``

3. Remove the following line or change latin1 to utf8 in thinkup.latin.sql. Note: This file is huge. Use a text editor
which is able to handle huge files.

``/*!40101 SET NAMES latin1 */;``

4. Restore the decoded SQL data as UTF8.  (--default-character-set=utf8 is only necessary if UTF8 is not default)

``mysql --default-character-set=utf8 thinkupDB < thinkup.latin.sql``

5. Set Thinkup to use UTF8 by adding the following line to config.inc.php.

``$THINKUP_CFG['set_pdo_charset'] = true;``

`Mailing list thread "Gibberish in TU database" <https://groups.google.com/d/topic/thinkupapp/Ql-zzUOnQmA/discussion>`_

Can't change my account's email address
---------------------------------------

If you find your email isn't being received and you are sure it's because of the destination address, you can change
it in the database. 
You will need to log in to mysql, locally or via ssh:

``$ mysql -uroot -p  database_name (assuming user is root, you will be prompted for the password)``

The first statement will show you all owners in the database. The second line will change the email and set the owner
as activated; This is fin if there's a single owner in the table.
   
``mysql> select * from tu_owners;``

``mysql> update tu_owners set email='your_new_email@somewhere.com', is_activated = '1' ;``

If there are plural owners in the table, you'll need to find the actual owner record and update it:

``mysql> update tu_owners set email='your_new_email@somewhere.com', is_activated = '1' WHERE 
email='oldbad_email@somewhere.com' LIMIT 1;``

Repeated errors: "Warning: require_once(): Unable to allocate memory for pool."
-------------------------------------------------------------------------------

ThinkUp 0.9 has a known compatibility issue with the Alternative PHP Cache (APC).  A known workaround is to disable
APC for ThinkUp by adding the following line anywhere in your config.inc.php file:

``ini_set('apc.cache_by_default',0);``

Crawler log / SQL log not being created.
----------------------------------------

At the moment, ThinkUp will not explicitly create the crawler log and sql log files. They need to be manually created
by you. To do this, execute this command:

``$ touch path/to/log/file.log``

You will need to replace the path with the actual path to where you have set your log files to be in your config. If I
were in my root ThinkUp directory (the one above webapp/) and I wanted to created the log files in log/crawler.log
and log/sql.log, I would execute the following commands from my root ThinkUp directory:

``$ touch log/crawler.log``

``$ touch log/sql.log``