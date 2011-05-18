Applies to ThinkUp 0.9 (Beta 9)
-------------------------------

Last revised: 03/24/11

The following is a general tutorial for how to install ThinkUp on
an arbitrary web host. This document assumes some familiar with
basic web hosting concepts; you may need to refer to your hosting
company's documentation for specifics, or find an
environment-specific tutorial to assist you (see the appendix).

Pre-Requisites
==============


-  File-system access to a web host, preferably over FTP or SFTP.
-  `PHP 5.2 <http://php.net>`_ or higher with cURL, GD, JSON, and
   the PDO MySQL driver enabled.
-  `MySQL 5.0.3 <http://mysql.com/>`_ or higher.
-  A publicly available web server. (Twitter authorization requires
   a public callback URL, so you'll need to expose non-public servers
   to the internet for initial authorization; after that, the server
   no longer needs to be publicly available.)

Installation
============

Download
--------

.. image:: http://vjarmy.com/images/thinkup/download.png

You can download the
`latest distribution <http://github.com/ginatrapani/ThinkUp/downloads>`_
of ThinkUp from GitHub. The most recent packages are located at the
top of the page.

Extract
-------

.. image:: http://vjarmy.com/images/thinkup/extract.png

Once the download has completed, you should extract the contents
using whatever tools your operating system provides. When
completed, you should be left with a folder named "thinkup".

If your operating system does not automatically remove the .zip
installation archive, you can delete it at this time.

Upload
------

.. image:: http://vjarmy.com/images/thinkup/upload.png

With the installation extracted, connect to your web host using
your usual FTP/SFTP client. Navigate to the root folder of your
website and upload the "thinkup" folder into it.

(There's no requirement to put ThinkUp in the root directory of
your website - we just find it easier. If you place it somewhere
else, remember what folder it's in - you'll need to recall this
later.)

Recommended: Create Database
----------------------------

At this point, you might want to create a MySQL database for
ThinkUp to use. Instructions for how to do this varies from host to
host - many web hosting companies provide a control panel for
database management, others may give you direct access into MySQL.
Please contact your web host's support desk if you're unclear on
how to do this.

If you're unable to create a new database but already have an
existing one, that's okay too! Be sure to pay attention later, as
there's an extra configuration variable you may need to change.

Also, if you have permissions to create a database directly through
MySQL, you can skip this step - ThinkUp can create the database
during the install procedure.

Before proceeding to the next step, make sure you have:


-  the address of the MySQL server you have access to, also known
   as the *host*;
-  the name of the database you either just created, are already
   using, or want to use;
-  a username and password that has rights to manipulate this
   database

Launch The Installer
--------------------

.. image:: http://vjarmy.com/images/thinkup/launchinstaller.png

You're ready to begin the installation process. If you put the
ThinkUp installation in the root document folder of your web site,
then visit the following URL (replacing yoursite.com with the
appropriate domain name):

``http://yoursite.com/thinkup/``

If you put ThinkUp into a different folder than the site root, you
may need to add additional folders to the URL.

Seeing A Permissions Error Message?
-----------------------------------

.. image:: http://vjarmy.com/images/thinkup/permissionserror2.png

If you're not seeing an error about the permissions being wrong,
congrats! Move on to the next step.

But if you are - ThinkUp needs to be able to write to a few files
within its own installation, and many web hosts don't allow this by
default. If you're comfortable working in a terminal session, you
can connect via SSH and execute the recommended commands to resolve
the issue.

If you're not that technical, don't worry - this is still easy to
fix! Reconnect to your FTP/SFTP session, and find the ThinkUp
folder you uploaded. Select it, and then look for a menu command
named something like "Get Info" or "Manage Permissions". You will
likely find a list of "permission bits" you can assign to the
folder - just enable the "World/Write" permission and apply it to
the folder. (If your client gives you the option of applying the
permission to the enclosed items, do so.)

Create The Configuration File
-----------------------------

.. image:: http://vjarmy.com/images/thinkup/startinstall.png

ThinkUp will now prompt you to create a configuration file. Click
the "installing ThinkUp" link to begin.

Requirements Check
------------------

.. image:: http://vjarmy.com/images/thinkup/reqcheck.png

The first screen in the install process is a requirements check, to
ensure your environment matches the requirements listed above. If
you see any "No" items here, you will probably need to speak to
your web hosting company about getting additional PHP modules
enabled.

Configuration Details
---------------------

The second screen asks you for some information to help configure
ThinkUp.

.. image:: http://vjarmy.com/images/thinkup/createaccount.png

The opening section creates your administrative account for the
system:


-  Type your name into the *Name* field.
-  Type your preferred email address into the *Email Address*
   field. (Note that you will need to receive an email to activate
   your account, so don't type just anything here.)
-  Enter your preferred password twice, once in *Choose Password*
   and again into *Confirm Password*.
-  Select the nearest city to you in "Your Time Zone".

.. image:: http://vjarmy.com/images/thinkup/configdb.png

The second section is where ThinkUp need the details about your
database:


-  Under *Database Host*, enter the address of the server for your
   database.
-  Under *Database Name*, type the name of the database you created
   earlier, *or* the name of the database you plan on reusing, *or*
   the name of the database you'd like to create (if you have
   permissions to create them directly through MySQL).
-  Under *User Name*, type the MySQL user name you have been given
   to access the database.
-  Under *Password*, type the MySQL password you have been given to
   access the database.

.. image:: http://vjarmy.com/images/thinkup/configadvanced.png

A third section is available, entitled "Advanced Options", which
may be necessary for some hosting environments where you are given
a specific MySQL socket or port to connect against. This section
also allows you to set a *table prefix*, which can be very useful
if you're reusing an existing database. Most people can leave this
section alone.

Activate Your Account
---------------------

.. image:: http://vjarmy.com/images/thinkup/activate.png

You're in the home stretch! You should now receive an email with a
subject of "Activate Your New ThinkUp Account". Click the link
found within the email and your account will be activated - and
you'll be ready to use ThinkUp!

You're Done!
============

Congratulations! (That wasn't so bad, was it?)

At this point you're probably interested in actually using ThinkUp
- and there's still some more configuration to do - but for those
details, you should visit the :doc:`User Guide </userguide/index>`

If You Get Stuck
================

Installing web software is always difficult - every host is a
little different, and small things can cause large problems.
Luckily, help is here!


-  :doc:`Troubleshooting ThinkUp: Common Problems and Solutions </troubleshoot/common>`
   contains answers to the most common installation issues.
-  Live help is available around the clock from the ThinkUp
   community on :doc:`IRC </contact>`.
-  Non-live (but still pretty snappy!) help is available on the
   `ThinkUp mailing list <http://groups.google.com/group/thinkupapp>`_.

Appendix: Environment-Specific Tutorials
========================================


-  :doc:`Installing ThinkUp on Amazon EC2 </install/specific/amazonec2>`
-  :doc:`Installation: Dreamhost </install/specific/dreamhost>`
-  :doc:`Installation: Local Computer </install/specific/local>`
-  :doc:`Installation: Mac OS X </install/specific/mac>`
-  :doc:`Installation: Windows </install/specific/pc>`
