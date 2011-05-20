Enable the verbose crawler log
==============================

Create your log and tail during run

TODO: Port from wiki
https://github.com/ginatrapani/ThinkUp/wiki/Configuration:-Enable-the-crawler's-verbose-developer-log



Problem: the crawler log / SQL log are not being created
--------------------------------------------------------

At the moment, ThinkUp will not explicitly create the crawler log and sql log files. They need to be manually created
by you. To do this, execute this command:

``$ touch path/to/log/file.log``

You will need to replace the path with the actual path to where you have set your log files to be in your config. If I
were in my root ThinkUp directory (the one above webapp/) and I wanted to created the log files in log/crawler.log
and log/sql.log, I would execute the following commands from my root ThinkUp directory:

``$ touch log/crawler.log``

``$ touch log/sql.log``