<?php

/************************************************/
/***  APPLICATION CONFIG                      ***/
/************************************************/

// Application title
$THINKUP_CFG['app_title']                 = 'ThinkUp';

// Public path of thinkup's /webapp/ folder on your web server.
// For example, if the /webapp/ folder is located at http://yourdomain/thinkup/, set to '/thinkup/'.
$THINKUP_CFG['site_root_path']            = '/';

// Full server path to /thinkup/ folder.
$THINKUP_CFG['source_root_path']          = '/your-server-path-to/thinkup/';

// Your GMT offset, not (necessarily) your web server's. Pacific: 7, Eastern: 4
$THINKUP_CFG['GMT_offset']                = 7;

// Your timezone
$THINKUP_CFG['timezone']                  = 'America/Los_Angeles';

// Toggle Smarty caching. true: Smarty caching on, false: Smarty caching off
$THINKUP_CFG['cache_pages']               = true;

// Set whether or not your site's registration page is available.
// @TODO Build email invitation system so this isn't simply a binary choice.
$THINKUP_CFG['is_registration_open']      = true;

// To enable reCAPTCHA on registration, set enable to true and fill in your keys and libpath;
// Otherwise, leave these settings as-is.
// More info at http://recaptcha.net/plugins/php/
$THINKUP_CFG['recaptcha_enable']          = false;
$THINKUP_CFG['recaptcha_public_key']      = '';
$THINKUP_CFG['recaptcha_private_key']     = '';

/************************************************/
/***  DATABASE CONFIG                         ***/
/************************************************/

$THINKUP_CFG['db_host']                   = 'localhost'; //On a shared host? Try mysql.yourdomain.com, or see your web host's documentation.
$THINKUP_CFG['db_type']                   = 'mysql';
$THINKUP_CFG['db_user']                   = 'your_database_username';
$THINKUP_CFG['db_password']               = 'your_database_password';
$THINKUP_CFG['db_name']                   = 'your_thinkup_database_name';
$THINKUP_CFG['db_socket']                 = '';
$THINKUP_CFG['db_port']                   = '';
$THINKUP_CFG['table_prefix']              = 'tu_';

/************************************************/
/***  DEVELOPER CONFIG                        ***/
/************************************************/

// Full server path to crawler.log.
$THINKUP_CFG['log_location']              = false;

// Full server path to sql.log. To not log queries, set to null.
$THINKUP_CFG['sql_log_location']          = null;

// How many seconds does a query take before it gets logged as a slow query?
$THINKUP_CFG['slow_query_log_threshold']  = 2.0;

$THINKUP_CFG['debug']                     = true;

$THINKUP_CFG['enable_profiler']           = false;
