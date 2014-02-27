Advanced Configuration
======================

ThinkUp's configuration file, ``config.inc.php``, includes advanced settings for administrators to customize
their installation.

app_title_prefix
----------------

ThinkUp prepends this value to all instances of the application title. For example, to name your installation
``Jane Smith's ThinkUp``, set this value to ``"Jane Smith's "``. When you do, all of the page titles
and email notification copy will refer to your installation as Jane Smith's ThinkUp.

The default value is '', or no prefix at all.

datadir_path
------------

This is the path to ThinkUp's writable file cache directory. By default, this folder is located in ThinkUp's root. To
customize the location of the folder where ThinkUp writes its cache and data backup files, set it here.

The default value is ``$THINKUP_CFG['source_root_path'] . 'data/'``.

use_db_sessions
---------------

Defaults to true as of 2.0 beta 11.

To store ``$_SESSION`` data in the database instead of on the filesystem (PHP's default) or elswhere, set this to true.

The `PHP Security Consortium recommends storing session data in a database <http://phpsec.org/projects/guide/5.html>`_
versus on the filesystem to avoid potential exposure of sensitive app data to other apps or users on shared servers.

mandrill_api_key
----------------

If your web server is unable to send email via PHP's built-in mail() function, ThinkUp can send email via
`Mandrill <http://mandrillapp.com>`_, a transactional email service.

To set ThinkUp to send email via Mandrill, in ``config.inc.php``, set ``mandrill_api_key`` to your Mandrill API key.
To get an API key, sign up for a Mandrill account, log in, and create a new API key in Settings > SMTP & API
Credentials.