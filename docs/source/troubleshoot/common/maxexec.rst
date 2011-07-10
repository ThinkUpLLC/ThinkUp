"Fatal error: Maximum execution time of 30 seconds exceeded"
============================================================

ThinkUp's crawler script can take a long time because it can make hundreds of requests to external web sites during
the course of one crawl. By default PHP is set to only allow a script to take 30 seconds. If you run into this error,
you must set ThinkUp to allow scripts to run longer. To do so,  add the following line anywhere in your
config.inc.php file
``set_time_limit ( 500 );``

