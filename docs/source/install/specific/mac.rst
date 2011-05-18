Installing ThinkUp on Mac OSX
=============================

Getting Started
---------------

This guide will give you the basics for getting all the necessary
components working so that you can successfully install and make
configuration changes for the ThinkUp app as a local application on your
Mac.

#. `Assumptions <#assumptions>`_
#. `Needed Tools <#tools>`_
#. `Install MAMP <#mamp-install>`_
#. `Configure MAMP <#mamp-cfg>`_
#. `Where does ThinkUp go? <#tu-unzip>`_
#. `Configure ThinkUp <#tu-cfg>`_
#. `Install ThinkUp <#tu-install>`_
#. `Other Notes <#notes>`_

Assumptions
-----------

-  You are running at least OS X 10.5.8
-  You have administrator access on your machine.
-  You can find the Terminal app and copy/paste some commands.
-  You can edit some text-based files.

Tools
-----

-  Good text editor. I prefer `TextWrangler <http://www.barebones.com/products/textwrangler/>`_,
   but any decent text editor will do. Stay away from editors that put
   their own formatting on like Word or Pages. You want to work with
   text only files!
-  The `MAMP package <http://www.mamp.info/)>`_.
   MAMP will make it easy to install and configure Apache (web server),
   MySQL (database) and PHP (code engine) on your local machine. You
   don’t need MAMP Pro. The basic version has everything necessary.
-  Terminal application to access the command line. There are some 3rd
   party applications that give you some more control, but I use the
   Terminal app that ships with OS X.

Install MAMP
------------

Download the most recent version of MAMP, unzip the file somewhere you
can find it, and double-click on the .dmg file. You should see a screen
that looks something like this:

.. image :: http://farm5.static.flickr.com/4109/5033974249_efd06d797a.jpg

Figure 1.1 MAMP Install Screen

Click and drag the MAMP folder icon to the Applications folder icon to
install MAMP. When that finishes, you should have a MAMP directory
listing under Applications.

.. image :: http://farm5.static.flickr.com/4105/5034595154_137d6cb535.jpg

Figure 1.2 MAMP Directory listing

Double click on the MAMP application to start MAMP - this will
automatically launch the Apache and MySQL servers. It will also load the
MAMP start page in your browser.

.. image :: http://farm5.static.flickr.com/4104/5033974755_545469766a.jpg

Figure 1.3 MAMP Application Control Window

.. image :: http://farm5.static.flickr.com/4152/5033975023_767f9d7c43.jpg

Figure 1.4 MAMP Start Page

From the start page, you can navigate to the PHPinfo screen to see what
versions of software MAMP has configured and installed. You will also be
able to go to PHPMyAdmin and create your database table for ThinkUp,
which we will cover in the next section!

Configure MAMP
--------------

Click on PHPMyAdmin on the MAMP start page (it is located in the bar
near the top of the page, under the logo).

.. image :: http://farm5.static.flickr.com/4090/5033975247_cb66ddefe2.jpg

Figure 2.1 MAMP PHPMyAdmin Page

Go to the area “Create New Database”. Type tt*thinkup*db in the text box
and click the Create button.

.. image :: http://farm5.static.flickr.com/4124/5033975439_3d912211c9.jpg

Figure 2.2 Create ThinkUp Database

**A brief word on security:** MAMP installs with default passwords.
This install guide is not yet going to go through the process of
changing passwords, because that could potentially make it more
difficult to install ThinkUp. Eventually, we will try to add in that
process. If you are going to try and expose your computer to the outside
world, you REALLY need to change these to something more secure.

TODO: Changing passwords in MAMP & setting up the config files to
recognize the new passwords.

Where Does ThinkUp Go?
----------------------

Grab the most recent install of ThinkUp (ThinkUp–0.1.zip as of Sept 28,
2010). You can also follow the instructions for setting up Git (`Working
with ThinkUp and
Git <http://github.com/ginatrapani/ThinkUp/wiki/Working-with-ThinkUp-and-Git)>`_
to stay up to date with the most recent version.

For the purposes of this documentation, we will assume that you have
chose to get the most recent archive directly from the downloads
section.

Unzip the ThinkUp–0.1.zip file to the htdocs folder of MAMP
(/applications/MAMP/htdocs). The htdocs folder should look like this
when you’re done:

.. image :: http://farm5.static.flickr.com/4092/5035196859_0e7dcfa927.jpg

Figure 3.1 ThinkUp unzip

Configure ThinkUp
-----------------

Before we install ThinkUp, we need to change the permissions on the
ThinkUp folder in htdocs. Launch the terminal app and type the
following:

``chmod -R 777 /applications/mamp/htdocs/thinkup/`` then hit return. This
makes sure that all files and folders in the ThinkUp folder will have
full read, write and execute access.

Install ThinkUp
---------------

Open your favorite web browser and go to this address:

http://127.0.0.1:8888/thinkup/install/

That will start you on the process for installing ThinkUp.

Future Proof Database Repair
----------------------------

After install, open config.inc.php in your text editor of choice and add
this line:

$THINKUP\_CFG[‘repair’] = true; somewhere before the end of the file and
save the file. This will future-proof your install for any database
repairs.

Other Notes
-----------

-  I have tested all this using OS X 10.5.8 - things may vary slightly
   in Snow Leopard. If anyone finds that something is different in Snow
   Leopard, please annotate these instructions where appropriate.
-  Now that you have installed ThinkUp locally, you will need to follow
   the instructions for authorizing your ThinkUp app for twitter.
   Instructions can be found at http://github.com/ginatrapani/ThinkUp/wiki/Installation:-Local-Computer
