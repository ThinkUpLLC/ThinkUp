# ThinkUp

ThinkUp is a free installable web application that captures
the insights and expertise of your social network by collecting and
organizing replies to your conversations on Twitter, Facebook and
(soon!) other networks. See screenshots and more at  [http://thinkupapp.com](http://thinkupapp.com)

ThinkUp is sponsored by [Expert Labs](http://expertlabs.org), led by
[Gina Trapani](http://ginatrapani.org), and used to be named
ThinkTank and Twitalytic. 

*WARNING: Pre-alpha code, PROCEED AT YOUR OWN RISK!*

This is not production code. This is a pre-alpha web application. The
intended audience is server administrators with experience installing
and troubleshooting  PHP/MySQL hosted web applications. While
ThinkUp uses OAuth to access Twitter, the webapp's login is not as
secure as it should be. Right now  this code is for experimentation
and tinkering only. Do not run on a production server. You
have been warned. 

## INSTALL

In future versions, this will get easier.

### System Requirements

- [PHP 5.2](http://php.net) with cURL and GD enabled
- [MySQL 5](http://mysql.com/)
- A public web server. (Twitter authorization requires a public
   callback URL, so you'll need to expose a local dev server to the
   internet for initial authorization; after that the server doesn't
   have to be publicly available.) 

### Install application files

*Note for upgraders:* If you're upgrading a previous installation, you should delete your cookies (in Firefox under `Preferences / Privacy / delete individual cookies`.  In Chrome, you can delete individual cookies under `Preferences / Under the Hood / Content Settings / Cookies / Show Cookies and other site data`).

1. Download source code. Save the `thinkup` directory one level above your web site's DocumentRoot. For example, if your site's DocumentRoot is  `/var/www/vhosts/example.com/httpdocs/` Put the `thinkup` directory here:  `/var/www/vhosts/example.com/thinkup/`
2. Create a symbolic link to the `thinkup/webapp` directory in your site's DocumentRoot folder. To do so, `cd` to the DocumentRoot, and use the command: `ln -s ../thinkup/webapp/ thinkup`
3. Make the following directories writable by the web server:

    `thinkup/webapp/view/compiled_view/`
    
    `thinkup/webapp/view/compiled_view/cache/`
    
    `thinkup/logs/`
       
### Set up database

1. Create a database and select it, i.e., 
  `CREATE DATABASE thinkup`

2. Build tables with `thinkup/sql/build-db_mysql.sql`

### Configure the application and its plugins

Rename `thinkup/webapp/config.sample.inc.php` to `config.inc.php`
and set the appropriate application, database, and plugin values for
your environment and  plugins.

#### Twitter Plugin Support: Register your app with Twitter (for OAuth support)

Log into Twitter and [register your ThinkUp instance](http://twitter.com/oauth_clients/). Set the callback URL to 
    http://yourserver.com/path-to-thinkup-webapp/plugins/twitter/auth.php
Write down the items labeled "Consumer key" and "Consumer secret" and add them to the `config.inc.php` file.

#### Facebook Plugin Support: Create a new Facebook application

[Create a new Facebook Application](http://facebook.com/developers/) and set the Connect URL to 
    http://yourserver.com/path-to-thinkup-webapp/plugins/facebook/ 
and the Post-Remove and Post-Authorize URLs to 
    http://yourserver.com/path-to-thinkup-webapp/account/?p=facebook

Write down your API Key and Application Secret, and enter those values into the `config.inc.php` file.

#### Flickr, Bit.ly, and ReCAPTCHA Plugins/Support (In-Progress)

Add the appropriate API keys for each service in your `config.inc.php` file.


## RUN

Visit the web application on your server, register and log in. On the
Plugins page, activate Twitter, Facebook, and any other plugins you
want. Once they're activated, click on the plugin link and authorize
your Twitter and/or Facebook accounts in ThinkUp. 

Then, to run the crawler to load your social network data, `cd` to `/your-path-to-thinkup/webapp/crawler/`, and run:

    $ php crawl.php you@example.com yourttpassword

Where `you@example.com` is your ThinkUp login email address, and `yourttpassword` is your ThinkUp password.

To view what's going on with the crawler, use this command:

    $ tail -f /your-path-to-thinkank/logs/crawler.log

Cron the crawler's run command to go at least once an hour. Hint: you may configure and cron this pre-fab bash script, which will run the crawler and rotate its logs:

    /thinkup/extras/cron/cron

See the script's [README](http://github.com/ginatrapani/thinkup/blob/master/extras/cron/README) for more information on configuring it.

## SUPPORT AND MORE INFORMATION

To discuss ThinkUp, [post to the project mailing list](http://groups.google.com/group/thinkupapp). For deeper documentation, see [the ThinkUp wiki](http://wiki.github.com/ginatrapani/thinkup).


## LICENSE

ThinkUp's source code is licensed under the
[GNU General Public License](http://github.com/ginatrapani/thinkup/blob/master/GPL-LICENSE.txt),
except for the  external libraries listed below.


## EXTERNAL LIBRARIES

- [Facebook Platform PHP5 client](http://wiki.developers.facebook.com/index.php/PHP) (Included) 
- [SimpleTest](http://www.simpletest.org/) (Included)
- [Smarty](http://smarty.net) (Included)
- [Twitter OAuth by Abraham Williams](http://github.com/abraham/twitteroauth) (Included)
- [ReCAPTCHA PHP library](http://recaptcha.net/plugins/php/) (Included)
- [PHP Lorem Ipsum Generator](http://tinsology.net/scripts/php-lorem-ipsum-generator/) (Included)

## CREDITS

Social icons provided by [Function](http://wefunction.com/2009/05/free-social-icons-app-icons/).
