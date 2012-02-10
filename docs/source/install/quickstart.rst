Quick Start
===========

To run ThinkUp, you'll need a public web server running:

* `PHP 5.2 <http://php.net/>`_ or higher with `cURL <http://php.net/manual/en/book.curl.php>`_, `GD <http://php.net/manual/en/book.image.php>`_, and the `PDO <http://php.net/manual/en/book.pdo.php>`_ `MySQL driver <http://www.php.net/manual/en/ref.pdo-mysql.php>`_ enabled
* `MySQL 5.0.3 <http://mysql.com/>`_ or higher

Most users purchase server access from a web hosting provider.

Hosting Providers
-----------------

We built ThinkUp so that it can run on the most common and widely-available LAMP-based hosting plans. Some providers
offer simple custom ThinkUp launchers. 

Automatic ThinkUp Launchers
^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you don't have a web server hosting package and don't want to get one, one of these options is best for you:

*  `PHP Fog <http://phpfog.com>`_ offers `free hosting (up to 20MB) and a simple ThinkUp installer
   <http://expertlabs.org/2011/12/php-fog-adds-free-thinkup-hosting.html>`_.
*  Our `Amazon EC2 launcher <http://expertlabs.aaas.org/thinkup-launcher/>`_ spins up an Amazon web server and installs
   ThinkUp on it for you; free for the first year and costs around $15/month after that.

Known Incompatibilities
^^^^^^^^^^^^^^^^^^^^^^^

Some web hosting providers or plans have known incompatibilities with ThinkUp. Several ThinkUp users report that:

*   Dreamhost's least expensive shared hosting package :doc:`may time out when gathering data for busy
    accounts </troubleshoot/common/prematureend>`. Dreamhost's 300MB VPS server (which costs around $15/month) will not.
*   GoDaddy's shared hosting plan triggers :doc:`database server timeout errors </troubleshoot/common/mysqlgoneaway>`.

Have notes about your ThinkUp hosting provider that should appear on this page? Please post them to the
`ThinkUp mailing list <http://groups.google.com/group/thinkupapp>`_.

Install ThinkUp in Three Steps
------------------------------

Once you have access to a public web server to install ThinkUp, install it in three easy steps.

1. `Download the latest distribution of ThinkUp <https://thinkupapp.com/download/>`_.
2. Extract the zip file into a web-accessible folder on your web server via FTP.
3. Visit that URL in your browser to proceed through ThinkUp's simple installer.

Trouble? Check out the `detailed installation guide <install.html>`_.
