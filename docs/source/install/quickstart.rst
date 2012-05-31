Quick Start
===========

To run ThinkUp, you'll need a web server running PHP and MySQL. Some providers offer easy ThinkUp launchers. 

One-Click ThinkUp Launchers
---------------------------

If you don't already have a web server hosting package and don't want to get one, one of these options is best for you:

*  `PHP Fog <https://phpfog.com/thinkup?a_aid=24990363>`_ offers a simple ThinkUp installer and free hosting up to
   20MB.
*  Our `Amazon EC2 launcher <http://expertlabs.aaas.org/thinkup-launcher/>`_ spins up an Amazon web server and installs
   ThinkUp on it for you; free for the first year and costs around $15/month after that.


Install ThinkUp on Your Web Server in Three Steps
-------------------------------------------------

Most ThinkUp users purchase or already have server access from a web hosting provider. We built ThinkUp so that it can
run on the most common and widely-available LAMP-based hosting plans. 

Once you have access to a public web server to install ThinkUp, you install it in three easy steps.

1. `Download the latest distribution of ThinkUp <http://thinkupapp.com/download/>`_.
2. Extract the zip file into a web-accessible folder on your web server via FTP.
3. Visit that URL in your browser to proceed through ThinkUp's simple installer.

Trouble? Check out the `detailed installation guide <install.html>`_.

Known Incompatibilities
^^^^^^^^^^^^^^^^^^^^^^^

Some web hosting providers or plans have known incompatibilities with ThinkUp. Several ThinkUp users report that:

*   Dreamhost's least expensive shared hosting package :doc:`may time out when gathering data for busy
    accounts </troubleshoot/common/prematureend>`. Dreamhost's 300MB VPS server (which costs around $15/month) will not.
*   GoDaddy's shared hosting plan triggers :doc:`database server timeout errors </troubleshoot/common/mysqlgoneaway>`.

Have notes about your ThinkUp hosting provider that should appear on this page? Please post them to the
`ThinkUp mailing list <http://groups.google.com/group/thinkupapp>`_.
