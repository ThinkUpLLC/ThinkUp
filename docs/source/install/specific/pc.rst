Installing ThinkUp on Windows
=============================

**NOTE:** We're making certain assumptions about what your environment looks like and your mileage may vary. If you want
to access ThinkUp at a URL other than yourdomain.com/thinkup, or on a subdomain, you'll have to adjust accordingly.

I'm sure there are errors to be found and clarifications to be made--please help clean this page up!

General Assumptions
-------------------

-  You have a Windows computer that is accessible from the internet. For best security, your computer should be up to
   date with all available patches and updates.

-  You have access to an SMTP (mail) server, either locally on your server or accessible via your network. Otherwise,
   ThinkUp can't send any email and much of its functionality will be disabled.

The short version
-----------------

-  Install Git for Windows (http://help.github.com/win-git-installation/)
-  Generate SSH Keys (http://help.github.com/msysgit-key-setup/)
-  Install XAMPP (http://www.apachefriends.org/en/xampp.html)
-  Get the latest ThinkUp files from GitHub ``git clone git://github.com/ginatrapani/ThinkUp.git``
-  Make a MySQL database
-  Edit httpd.conf to point to webapp directory
-  `Register the app with Twitter`_
    - *Application Website:* ``http://yourdomain.com/thinkup``
    - *Application Type:* Browser *Callback URL:*  ``http://yourdomain.com/thinkup/plugins/twitter/auth.php`` Copy the
      *Consumer key* and *Consumer secret*
    - Run the web-based install at ``http://yourdomain.com/thinkup/install/`` and follow its steps
    - Authorize ThinkUp to use your Twitter account # Create a batch file
      to run crawler automatically

The long version
----------------

1. Install GIT for Windows
~~~~~~~~~~~~~~~~~~~~~~~~~~

In a nutshell, just follow the instructions for downloading and
installing GIT for Windows at http://help.github.com/win-git-installation/
. Don't worry about any of the installation options - simply
choosing all of the defaults during installation will give you a
GIT environment that will work just fine with ThinkUp.

2. Generate SSH Keys
~~~~~~~~~~~~~~~~~~~~

Once again, just follow instructions at http://help.github.com/msysgit-key-setup/
. Be sure that it is setup correctly before continuing (i.e.: you
can ssh git@github.com and receive a "successful authentication"
message).

3. Install XAMPP
~~~~~~~~~~~~~~~~

XAMPP is an nicely packaged and easy to install Apache distribution
containing, among other programs, MySQL and PHP, which are
necessary for ThinkUp. For the purposes of this guide, we're going
to assume that you're using XAMPP as your serving platform.

You can download XAMPP from
`http://www.apachefriends.org/en/xampp-windows.html <http://www.apachefriends.org/en/xampp-windows.html>`_
. Get the basic (not XAMPP Lite) package.

Go ahead and start the XAMPP installation. Simply choosing the
defaults will give you all the tools you need to run ThinkUp. Note:
by default, the XAMPP will say that it will install at c:\ .
However, this will actually create the folder c:\xampp and install all
files into it. For the rest of this guide, we'll assume this is
where the XAMPP files are.

After the installation is finished, press 1 (start control panel).
This opens up the XAMPP Control Panel. (Note: you should also have
shortcuts on your desktop to launch the XAMPP Control Panel as
well). We're going to start the Apache and MySQL servers, which are
all you need to run ThinkUp. First, start the Apache server. You'll
probably get a Windows firewall warning stating that the program's
trying to communicate to the network. You'll have the option to
allow access to local and public networks. If you want your server
to be accessible from the internet (probably yes), make sure the
"public" option is selected. Next, start the MySQL server. Once
again, you'll probably get another firewall warning. However, the
MySQL server does NOT have to be accessible on the network for
ThinkUp to work. Therefore, we'd recommend setting the firewall to
block all network access.

ThinkUp needs PHP's Curl extension to work. However, by default
this extension is disabled. Enabling it is easy, though. Just open
the php.ini file in your favorite text editor (located at c:\\xampp\\php\\php.ini,
if you're following the defaults. Just find this line:

``;extension=php_curl.dll``

And replace it with this:

``extension=php_curl.dll``

Go back to the XAMPP Control Panel, and stop Apache. Then, start it
again.

One last thing - We highly recommend you read XAMPP's `security
notes <http://www.apachefriends.org/en/xampp-windows.html#1221>`_,
especially if you're going to be live on the internet.

4. Get the latest ThinkUp files from github
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, create a folder on your hard drive to store the files from
git for ThinkUp (say, c:\\git). Then, start Git Bash. At the command
line, change into your directory:

``cd c:\git``

and type this command to get the ThinkUp files:

``git clone git://github.com/ginatrapani/ThinkUp.git``

You now have all of the latest ThinkUp files in the folder c:\\git\\ThinkUp .

5. Make a database for ThinkUp
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default installation of XAMPP includes phpMyAdmin, a web-based
MySQL client. We'll use this to create and setup the database for
ThinkUp.


-  In a web browser, go to phpMyAdmin: http://127.0.0.1/phpmyadmin/
-  From the main page, create a new database (we'll call it
   tt\_thinkup\_db for the rest of this guide).
-  In the left panel, select the ThinkUp database; then in the
   right pane, click the Import tab.
-  In the box "File to Import", browse for the ThinkUp database
   creation script (c:\\git\\ThinkUp\\webapp\\install\\sql\\build-db_mysql.sql). Then, press Go. This creates
   all of the tables and such ThinkUp needs.

Now we need to create a special database user to access the ThinkUp
database


-  Go back to phpMyAdmin's home screen.
-  Click the Privileges tab to create a new user for accessing the
   database (say, **thinkup**). Give this new user a good password.
   (Leave all of the Global privileges checkboxes blank, then press
   Go.
-  In the Database-specific privileges section, choose
   tt\_thinkup\_db, then select the SELECT, INSERT, UPDATE and DELETE
   privileges and press Go.

6. Alter httpd.conf to point to webapp directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This one's a bit tricky. If you followed this guide closely, your
Apache installation is separate from your ThinkUp installation.
This is great for security reasons, but as it stands right now your
webserver can't access the ThinkUp webfiles. Now, you could just
manually copy and paste the files from the webapp directory to
apache's web directory (c:\\xampp\\htdocs), but you'd have to do this every time
ThinkUp is updated. A better solution would be to edit Apache's
configuration file to point automatically to the webapp directory.

For example, lets say you want your ThinkUp installation to be
accessible at http://www.yourserver.com/thinkup/ . With your
favorite text editor, edit Apache's configuration file (c:\\xampp\\apache\\conf\\httpd.conf)
and add these lines at the end::

  <Directory "c:/git/ThinkUp/webapp">
  Order allow,deny
  Allow from all
  Alias /thinkup "c:/git/ThinkUp/webapp"

Now, restart Apache. Your ThinkUp installation webfiles are now
accessible to your webserver.

7. Register your app with Twitter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since you're hosting ThinkUp yourself, you have to register it as
an app with Twitter. Head over to Twitter's `Applications page <http://twitter.com/apps/new>`_
and click on "Register a new
application". For *Application Name* put something
unique like "John's ThinkUp." Enter anything in *Description* (it
just can't be blank). For *Application Website* put the URL you set
up in the previous step, e.g. ``http://yourdomain.com/thinkup``. For
*Application Type* choose *Browser*. For *Callback URL* put e.g.
``http://yourdomain.com/thinkup/plugins/twitter/auth.php``. For
Default Access type, choose ``Read-only``. Leave the checkbox next to
``Use Twitter for login`` unchecked. Finally, click on *Save*.

On the next page Twitter will give you some information. Copy down
the *Consumer key* and the *Consumer secret* for later.

8. Run the install
~~~~~~~~~~~~~~~~~~

Go to your ThinkUp installation website (i.e.:
``http://yoursite.com/thinkup/install/``)

Make sure "Check System Requirements" shows everything as green. If
so, press "Next Step" to go to, well, the next step.

Under "Create your ThinkUp Account," enter your name, your email
address and a password for your ThinkUp webapp. Under "Connect
ThinkUp to Your Database," enter your database information from
step 5. Assuming you're using the installation defaults, you don't
need to change the values under "Advanced Options." Press "Next
Step."

If everything installed correctly, you received a congratulations
message. Congratulations! You'll receive an email to activate your
account. Activate it and log into your ThinkUp site (i.e.:
``http://yoursite.com/thinkup/login.php``)

Now, you'll want to enable the Twitter plugin. Click the
"Configuration" button in the upper right corner, then click
"Twitter" on the next page. This will bring up the configuration
page for Twitter. Enter the *Consumer key* and the
*Consumer secret* from step 7. Press "save options."

9. Link your Twitter account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you've entered your secret and key correctly, you should now see
a page with a button that says "Authorize ThinkUp on Twitter."
Click on the button to set up your Twitter account and jump through
the hoops to authorize ThinkUp to access your Twitter account.

10. Crawl your tweets
~~~~~~~~~~~~~~~~~~~~~

ThinkUp won't do anything until the crawler has run. To run it
manually, go to a Windows Command Prompt and from the ThinkUp
crawler directory (e.g. ``cd c:\git\thinkup\webapp\crawler\``) run the crawler like this::

  c:\path\to\php\php.exe c:\path\to\twitalytic\crawler\crawl.php yourttusername@example.com yourttpassword

Nothing will happen for a few seconds, and then you'll be returned
to the command prompt. When you go back to ThinkUp in your web
browser you should see some of your recent tweets. That means it's
working!

11. Set up a cron job to crawl your data periodically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Because Twitter limits the number of data requests an app makes
each hour, it probably won't be able to crawl all of your tweets
and replies in one go. This means you'll have to crawl periodically
to get all of your tweets, and to get new tweets. Instead of having
to enter the crawl command every hour, you can tell Windows to do
it automatically for you.

First, you'll need to create a "batch" file that Windows' scheduler
will refer to. Create a new text file (say, c:\\git\\thinkup.bat) and add these
lines::

  cd c:\git\thinkup\webapp\crawler\
  c:\path\to\php\php.exe c:\path\to\twitalytic\crawler\crawl.php yourttusername@example.com yourttpassword

Now, go to Windows' Scheduled Tasks control panel and create a new
task using the batch file you created above. By default, you can’t
schedule tasks to run more than once daily, but you can get around
that: http://support.microsoft.com/kb/226795

12. Enjoy!
~~~~~~~~~~

Additional Notes
----------------

`Notes from Mark on the mailing
list <http://groups.google.com/group/twitalytic/browse_thread/thread/50fbdb9b2700200b>`_

I thought I'd try installing it on my Windows 2003 server box, and
it looks like I've got the webapp portion working. If you've been
having problems installing it on a windows box, here's some of my
observations from my install:

(Note: this assumes you already have PHP and MySQL installed and
running correctly on your server. Yes, that's a pretty big
assumption, but you can google lots of tutorials to help get you
started. Also, I'm using IIS and the SMTP server built into Windows
- you may need to install these if they're not already running on
your system)


-  After you've downloaded the web files and set up permissions for
   the ThinkUp files, you need to grant MODIFY permissions on your
   template\_c directory (webapp/template\_c) to your internet guest
   account (SERVER\_NAME\_SERVER). Read and write privileges are not
   enough. Otherwise, you'll get lots of strange and frustrating
   errors from Smarty :)

-  The SMTP server built into Windows 2003 doesn't like "complex"
   email addresses. I had to modify line 59 in register.php from:
   "From: "Auto-Response" <notifications@$host>" to "From: notifications@$host".

-  In your php.ini file, to enable curl and gd, uncomment the lines
   "extension=php\_curl.dll" and "extension=php\_gd2.dll"

-  To schedule the crawler, I created a one-line .bat file:
     ``c:\path\to\php\php.exe “c:\path\to\twitalytic\crawler\crawl.php”``

By default, you can't schedule tasks to run more than once daily,
but you can get around that:
`http://support.microsoft.com/kb/226795 <http://support.microsoft.com/kb/226795>`_


.. _`Register the app with Twitter`: http://twitter.com/apps/new