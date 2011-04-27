Installing ThinkUp on Dreamhost
===============================

***NOTE:** Every instance of “yourusername” and “yourdomain.com” you’ll
have to replace with the values appropriate to you. “yourusername”
always refers to your DreamHost username, **not** your Twitter username.
I’m making certain assumptions about what your DreamHost environment
looks like and your mileage may vary. If you want to access ThinkUp at a
URL other than yourdomain.com/thinkup, or on a subdomain, you’ll have to
adjust accordingly.*

*I’m sure there are errors to be found and clarifications to be
made—please help clean this page up!*

The short version:
------------------

- In the DreamHost Panel set your domain_ to use PHP5.
- Get `shell access`_ and SSH in.
- ``git clone http://github.com/ginatrapani/ThinkUp.git``
- Make a database_, then:
    - ``cd thinkup``
    - ``mysql -u tt_thinkup -p -h mysql.yourhostname.com tt_thinkup_db < sql/build-db_mysql.sql``
- ``ln -s ~/thinkup/webapp ~/yourdomain.com/thinkup``
- `Register the app with Twitter`_
    - **Application Website:** ``http://yourdomain.com/thinkup``
    - **Application Type:** Browser
    - **Callback URL:** ``http://yourdomain.com/thinkup/plugins/twitter/auth.php``
    - Copy the **Consumer key** and **Consumer secret**
- Rename webapp/config.sample.inc.php to webapp/config.inc.php, and enter the **oauth_consumer_key** and
    - **oauth_consumer_secret** as above, plus:
    - *log_location:* ``/home/yourusername/thinkup/logs/``
    - **site_root_path:** ``/thinkup/``
    - And the db_host, db_user, db_password, and db\_name from Step 2.
- Browse to ``http://yourdomain.com/thinkup/session/register.php`` and create an account.
- Wait for the activation email, click on the link it contains to activate your account and log in.
- Jump through the hoops to authorize ThinkUp to access your Twitter and other social networking accounts.
- Back in SSH, crawl your tweets:
    - ``cd ~/thinkup/crawler/ && /usr/local/php5/bin/php ~/thinkup/crawler/crawl.php yourttemail yourttpassword``
- Set up a `Cron Job`_ to run this command every hour:
    - cd /home/username/thinkup && /usr/local/php5/bin/php /home/username/thinkup/crawler/crawl.php email at domain
        password
- Enjoy!

The long version:
-----------------

1. Set your domain to use PHP 5
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Go to `Manage Domains`_ on the `DreamHost Panel`_.
Click on **Edit** next to the domain you want to use (we’ll call it
**yourdomain.com** from now on) and scroll down to **PHP mode** under
**Web Options**. If it doesn’t say **PHP 5 CGI** or **PHP 5 FastCGI**,
change it so it does (either will work; DreamHost recommends FastCGI).
If you’re afraid this will break some of your existing scripts, you
could create a new subdomain for ThinkUp instead and set that to PHP 5.
Scroll down and click on **Change settings**.

2. Make sure you have shell access
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the DreamHost Panel’s `Manage Users`_ page, find the
username that you usually use to upload files to your site and click
**Edit** next to it. Next to **User Account Type** check the **Shell
account** radio button. Scroll to the bottom and click **Save changes**.

3. SSH to your shell account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On Linux, Unix or OS X you can just type **ssh
`yourusername@yourdomain.com <mailto:yourusername@yourdomain.com>`_** in
a terminal window. In Windows you’ll need to download an SSH client like
`PuTTY`_ (free). Once you’re connected you should be in your
home directory (if you type ``pwd`` (“print working directory”) it
should tell you something like **/home/yourusername**).

4. Clone the GitHub repository
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Inside your home directory you will have a subdirectory for each of your
domains and subdomains (type ``ls`` if you want to see them listed).
Your first impulse may be to switch to one of those subdirectories
(domains) to install ThinkUp there, but I recommend putting it its own
subdirectory so that not all of its files will be accessible from the
web (later I’ll show you how to make the “webapp” directory publicly
accessible). So, to download the ThinkUp source, type
``git clone http://github.com/ginatrapani/ThinkUp.git``. Git will work
its magic and put its files in a subdirectory called **ThinkUp**, e.g.
**/home/yourusername/ThinkUp**.

(If you want the subdirectory to be called something different, you can
instead type
``git clone http://github.com/ginatrapani/ThinkUp.git somedirectory``.)

5. Set up your database
~~~~~~~~~~~~~~~~~~~~~~~

On the DreamHost Panel’s `MySQL Databases`_ page scroll
down to **Create a new MySQL database**. For **Database Name** choose
something unique like ``tt_thinkup_db``. In the **Use Hostname**
drop-down either choose an existing hostname, if you have one, or choose
**Create a new hostname now…** If you create a new hostname, just choose
e.g. “yourdomainname.com” from the drop-down on the right and in the
text box to its left enter “mysql,” which will create a hostname called
“mysql.yourhostname.com”. Next to **First User** you can either choose
an existing user or create a new one. If you create a new one you’ll
have to pick something unique, and of course come up with a
new[STRIKEOUT:[STRIKEOUT:strong!]]password and enter it in both password
boxes. Make note of all of these values (you’ll need them later) and
click on **Add new database now!**

Back in SSH, change to the ThinkUp directory with ``cd ThinkUp``. Then
import the database structure from **sql/build-db\_mysql.sql** into the
database with the following command:

::

    mysql -u username -p -h mysql.yourhostname.com tt_thinkup_db < sql/build-db_mysql.sql

Make sure you fill in the correct values for the database you just
created, and enter the password when prompted.

6. Set up a symbolic link to *webapp*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now it’s time to make the **webapp** directory publicly accessible on
the web. Let’s say you want to access ThinkUp at
**`http://yourdomain.com/thinkup\*, <http://yourdomain.com/thinkup*,>`_
so you’ll make a symbolic link or shortcut
to**/home/yourusername/ThinkUp/webapp\* from
**/home/username/yourdomain.com/thinkup**. To do this, first switch to
your home directory by typing ``cd ~`` (“~” is synonymous with your home
directory). Now make the link by typing
``ln -s ThinkUp/webapp yourdomain.com/thinkup``.

You can check to make sure the directory has been created by typing
``cd ~/yourdomain.com/thinkup``. If you don’t get a “No such file or
directory” error, it worked.

7. Register your app with Twitter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since you’re hosting ThinkUp yourself, you have to register it as an app
with Twitter. Head over to Twitter’s `Applications page <Applications page>`_
and click on `Register a new application <twitternewapp>`_. For
**Application Name** put something unique like “John’s ThinkUp.” Enter
anything in **Description** (it just can’t be blank). For **Application
Website** put the URL you set up in the previous step, e.g.
``http://yourdomain.com/thinkup``. For **Application Type** choose
**Browser**. For **Callback URL** put e.g.
``http://yourserver.com/path-to-thinkup-webapp/plugins/twitter/auth.php``.
For Default Access type, choose ``Read-only``. Leave the checkbox next
to ``Use Twitter for login`` unchecked. Finally, click on **Save**.

On the next page Twitter will give you some information. Copy down the
**Consumer key** and the **Consumer secret** for later.

8. Edit ThinkUp configuration file
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Rename *webapp/config.sample.inc.php* to *webapp/config.inc.php* and
edit it.

Type ``nano -w webapp/config.inc.php``. This will give you a nice
Notepad/TextEdit-like editor showing the configuration file. An
important thing to remember here is that the values you enter here must
be enclosed in quotation marks (single or double, but they must match),
and each line must end with a semicolon (``;``). Find the following
lines.

::

    $THINKUP_CFG['oauth_consumer_key']        = 'your_consumer_key';
    $THINKUP_CFG['oauth_consumer_secret']     = 'your_consumer_secret';

Replace **your_consumer_key** with the **Consumer key** you copied down
in the last step. Replace **your_consumer_secret** with the **Consumer
secret** you got.

Now scroll to this line:

::

    $THINKUP_CFG['site_root_path'] = '/';

Replace **/** with e.g. ``/thinkup/`` (i.e. the part of the URL after
“yourdomain.com”).

And on this line:

::

    $THINKUP_CFG['source_root_path']          = '/your-server-path-to/ThinkUp/';

Replace **/your-server-path-to/ThinkUp/** with e.g.
``/home/your_dreamhost_username/path-to-thinkup/``

Now, remember the database we set up? Have those values ready. Scroll
down to these lines:

::

    $THINKUP_CFG['db_host']                   = 'localhost';
    $THINKUP_CFG['db_type']                   = 'mysql';
    $THINKUP_CFG['db_user']                   = 'your_database_username';
    $THINKUP_CFG['db_password']               = 'your_database_password';
    $THINKUP_CFG['db_name']                   = 'your_thinkup_database_name';

Replace **localhost** with the hostname you chose, e.g.
``mysql.yourdomain.com``. Replace ``your_database_username`` with the
username you chose, e.g. ``tt_thinkup``, and replace
**your_database_password** with the corresponding password. Replace the
database name, **your_thinkup_database\_name**, with the database you
chose, e.g. ``tt_thinkup_db``.

Finally, save the file by pressing **Ctrl+X**, then pressing **Y** when
it asks you if you want to “Save modified buffer”, and pressing enter
when it asks for the “File Name to Write” (so it will save over the
already-existing file).

9. Create a ThinkUp account
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Open your web browser and navigate to e.g.
``http://yourdomain.com/thinkup/webapp/session/register.php`` Fill it
out and submit it, whereupon you’ll be sent an email with an activation
link. (If your activation link doesn’t arrive quickly, check your spam
folder.) Click on the link to finish setting up your ThinkUp account.

10. Set up social networking plugins
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

[Instructions TBD; basically, authorize ThinkUp to access your Twitter,
Facebook, etc. accounts.]

11. Crawl your tweets
~~~~~~~~~~~~~~~~~~~~~

ThinkUp won’t do anything until the crawler has run. To run it manually,
go back to your SSH window (Step 4) and from the ThinkUp directory (e.g.
``cd ~/ThinkUp/webapp/crawler/``) run the crawler like this:

::

    /usr/local/php5/bin/php crawl.php yourttusername@example.com yourttpassword

(We have to give the full path to PHP 5 because otherwise DreamHost
defaults to PHP 4 and falls over.)

Nothing will happen for a few minutes, and then you’ll be returned to
the command prompt. When you go back to ThinkUp in your web browser you
should see some of your recent tweets. That means it’s working!

12. Set up a cron job to crawl your data periodically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Because Twitter limits the number of data requests an app makes each
hour, it probably won’t be able to crawl all of your tweets and replies
in one go. This means you’ll have to crawl periodically to get all of
your tweets, and to get new tweets. Instead of having to enter the crawl
command every hour, you can tell DreamHost to do it automatically for
you.

Go to the DreamHost Panel and on the `Cron Jobs`_ page click
**Add New Cron Job**. For **User** choose the same shell user you chose
in Step 3. Give it a meaningful **Title** like “ThinkUp Crawler.” For
**Email output to** put in your email address—you’ll want to remove this
later, but for now it’s useful to make sure everything is running
smoothly. Make sure **Status** is “Enabled.” For **Command to run**
enter the following:

::

    cd /home/username//thinkup &&
    /usr/local/php5/bin/php /home/username/thinkup/crawler/crawl.php email at domain password

I had to edit line 30 of crawl.php and on line 30 put the full path to
init.php /home/username/thinkup/init.php for the job to run

Check the **Use locking** box and for **When to run** choose **Hourly**.
Then click on **Edit** to save your changes.

13. Enjoy!
~~~~~~~~~~

.. _`DreamHost Panel`: https://panel.dreamhost.com/
.. _`domain`: https://panel.dreamhost.com/index.cgi?tree=domain.manage
.. _`Manage Domains`: https://panel.dreamhost.com/index.cgi?tree=domain.manage
.. _`MySQL Databases`: https://panel.dreamhost.com/index.cgi?tree=goodies.mysql
.. _`database`: https://panel.dreamhost.com/index.cgi?tree=goodies.mysql
.. _`shell access`: https://panel.dreamhost.com/index.cgi?tree=users.users
.. _`PuTTY`: http://www.chiark.greenend.org.uk/~sgtatham/PuTTY/
.. _`Applications page`: http://twitter.com/oauth_clients/
.. _`Register the app with Twitter`: http://twitter.com/apps/new
.. _`Cron Jobs`: https://panel.dreamhost.com/index.cgi?tree=goodies.cron
.. _`Cron Job`: https://panel.dreamhost.com/index.cgi?tree=goodies.cron
.. _`Manage Users`: https://panel.dreamhost.com/index.cgi?tree=users.users

Cron enhancement
----------------

If your cron job is successful there will be no output, so you won’t get
an email. If you want it to email you the log of the most recent crawl,
you can instead paste the following in the **Command to run** box
(replacing **yourusername**, of course):

::

    lytic=/home/yourusername/ThinkUp &&
    log=$lytic/logs/crawler.log &&
    set -- $(wc -l $log) && lines=$1 &&
    cd $lytic &&
    /usr/local/php5/bin/php $lytic/crawler/crawl.php &&
    set -- $(wc -l $log) &&
    tail -n $((lines - $1)) $log

This is my first-ever attempt at a bash script, so be gentle. Basically
what it does is count the lines in the log file, run the crawler, counts
the lines again and calculates the difference, then shows that number of
lines from the end of the log.

You might also take a look inside the extras/cron folder with an
enchanted version of this script with automatic logrolling. (but a bit
trickier to set up) - in the latest code drop (0.8) I don’t see an
extras folder
