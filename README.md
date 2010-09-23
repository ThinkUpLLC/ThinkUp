# ThinkUp

ThinkUp is a free installable web application that captures the insights and expertise of your social network by 
collecting and organizing replies to your conversations on Twitter, Facebook and (soon!) other networks. 
See screenshots and more at  [http://thinkupapp.com](http://thinkupapp.com)

ThinkUp is sponsored by [Expert Labs](http://expertlabs.org), led by [Gina Trapani](http://ginatrapani.org), and used 
to be named ThinkTank and Twitalytic. 

*WARNING: Beta code, PROCEED AT YOUR OWN RISK!*

This is not production code. This is an early beta web application. The intended audience is server administrators with 
experience installing and troubleshooting PHP/MySQL hosted web applications. Right now this code is for 
experimentation and tinkering only. Do not run on a production server. You have been warned. 

## INSTALL

Currently we're testing a new web-based installer. Give it a try, won't you?

### System Requirements

- [PHP 5.2](http://php.net) with cURL, GD, and the PDO MySQL driver enabled
- [MySQL 5](http://mysql.com/)
- A public web server. (Twitter authorization requires a public
   callback URL, so you'll need to expose a local dev server to the
   internet for initial authorization; after that the server doesn't
   have to be publicly available.) 

### Download source code

1. Download the [latest distribution](http://github.com/ginatrapani/ThinkUp/downloads) of ThinkUp. 
2. Extract the zip file into a web-accessible folder.
3. Visit that URL in your browser and proceed through the installation process.

### Configure the application's plugins

Once ThinkUp is installed, log in and visit the Configuration page to activate the plugins of your choice.
Click on each one to visit its settings page and configure any necessary API keys or other settings.

### Run the ThinkUp crawler

Log into ThinkUp, and click the *Update now* link in the top left corner to run the ThinkUp crawler and begin 
capturing your posts and replies.

## SUPPORT AND MORE INFORMATION

To discuss ThinkUp, [post to the project mailing list](http://groups.google.com/group/thinkupapp). For deeper
documentation, see [the ThinkUp wiki](http://wiki.github.com/ginatrapani/thinkup).

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

## CREDITS

Social icons provided by [Function](http://wefunction.com/2009/05/free-social-icons-app-icons/).
