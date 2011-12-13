Hosting Providers
=================

We built ThinkUp so that it can run on the most common and widely-available LAMP-based hosting providers. Some
providers offer simple custom ThinkUp installers and others have known incompatibilities with ThinkUp. 

Simple Installation
-------------------

If you don't have a web server hosting package and don't want to get one, one of these options is best for you:

*  `PHP Fog <http://phpfog.com>`_ offers `both free hosting and a simple ThinkUp installer
   <http://expertlabs.org/2011/12/php-fog-adds-free-thinkup-hosting.html>`_.
*  Our `Amazon EC2 launcher <http://expertlabs.aaas.org/thinkup-launcher/>`_ spins up an Amazon web server and installs
   ThinkUp on it for you; free for the first year and costs around $15/month after that.

Plans and Providers with Known Problems
---------------------------------------

Several ThinkUp users report that:

*   Dreamhost's least expenive shared hosting package :doc:`may time out when gathering data for busy
    accounts </troubleshoot/common/prematureend>`. Dreamhost's 300MB VPS server (which costs around $15/month) will not.
*   GoDaddy's shared hosting plan triggers :doc:`database server timeout errors </troubleshoot/common/mysqlgoneaway>`.

Have notes about your ThinkUp hosting provider that should appear on this page? Please post them to the
`ThinkUp mailing list <http://groups.google.com/group/thinkupapp>`_.