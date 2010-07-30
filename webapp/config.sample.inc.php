<?php

/************************************************/
/***  APPLICATION CONFIG                      ***/
/************************************************/

// Public path of thinkup's /webapp/ folder on your web server.
// For example, if the /webapp/ folder is located at http://yourdomain/tweets/, set to '/tweets/'.
$THINKUP_CFG['site_root_path']            = '/';

// Full server path to /thinkup/ folder.
$THINKUP_CFG['source_root_path']          = '/your-server-path-to/thinkup/';

// Your GMT offset, not (necessarily) your web server's. Pacific: 7, Eastern: 4
$THINKUP_CFG['GMT_offset']                = 7;

// Your timezone
$THINKUP_CFG['timezone']                  = 'America/Los_Angeles';

// Toggle Smarty caching. true: Smarty caching on, false: Smarty caching off
$THINKUP_CFG['cache_pages']               = true;

// Application title
$THINKUP_CFG['app_title']                 = 'ThinkUp';

// Full server path to crawler.log.
$THINKUP_CFG['log_location']              = $THINKUP_CFG['source_root_path'].'logs/crawler.log';

// Full server path to sql.log. To not log queries, set to null.
$THINKUP_CFG['sql_log_location']          = $THINKUP_CFG['source_root_path'].'logs/sql.log';

// How many seconds does a query take before it gets logged as a slow query?
$THINKUP_CFG['slow_query_log_threshold']  = 2.0;

// Full server path to bundled Smarty template library.
$THINKUP_CFG['smarty_path']               = $THINKUP_CFG['source_root_path'].'extlib/Smarty-2.6.26/libs/';

// Set whether or not your site's registration page is available.
// @TODO Build email invitation system so this isn't simply a binary choice.
$THINKUP_CFG['is_registration_open']      = true;

$THINKUP_CFG['debug']                     = true;

$THINKUP_CFG['enable_profiler']           = false;

/************************************************/
/***  DATABASE CONFIG                         ***/
/************************************************/

$THINKUP_CFG['db_host']                   = 'localhost';  // On a shared host? Try mysql.yourdomain.com, or see your web host's documentation.
$THINKUP_CFG['db_type']                   = 'mysql';
$THINKUP_CFG['db_user']                   = 'your_database_username';
$THINKUP_CFG['db_password']               = 'your_database_password';
$THINKUP_CFG['db_name']                   = 'your_thinkup_database_name';
$THINKUP_CFG['db_socket']                 = '';
$THINKUP_CFG['db_port']                   = '';
$THINKUP_CFG['table_prefix']              = 'tu_';


/************************************************/
/***  PLUGIN CONFIG                           ***/
/************************************************/

/*------------------------------------------------
  TWITTER
  @TODO Put the Twitter-specific settings into 
        the Twitter plugin's configuration page.
------------------------------------------------*/

// Explanation at http://apiwiki.twitter.com/Things-Every-Developer-Should-Know#6Therearepaginationlimits
$THINKUP_CFG['archive_limit']             = 3200;

// To integrate with Twitter, get the following by registering at http://twitter.com/oauth_clients/.
// Otherwise, set both to ''.
$THINKUP_CFG['oauth_consumer_key']        = 'your_consumer_key';
$THINKUP_CFG['oauth_consumer_secret']     = 'your_consumer_secret';

/*------------------------------------------------
  FACEBOOK
  @TODO Put the Facebook-specific settings into 
        the Facebook plugin's configuration page.
-------------------------------------------------*/

// Base URL for Facebook Connect
$THINKUP_CFG['facebook_base_fb_url']      = 'connect.facebook.com';

// To integrate with Facebook, get a Facebook API key and fill in the values below.
// Otherwise, set all three to ''.
$THINKUP_CFG['facebook_api_key']          = 'your_facebook_api_key';
$THINKUP_CFG['facebook_api_secret']       = 'your_facebook_api_secret';
$THINKUP_CFG['facebook_callback_url']     = 'http://yourdomain/path-to-webapp/account/';

/*------------------------------------------------
  BIT.LY
  @TODO Abstract into a plugin.
  @TODO Put the bit.ly-specific settings into
        the bit.ly plugin's configuration page.
------------------------------------------------*/

// To integrate with bit.ly, get a bit.ly API key at http://bit.ly and fill in the values below.
// Otherwise, set both to ''.
$THINKUP_CFG['bitly_api_key']             = 'your_key';
$THINKUP_CFG['bitly_login']               = 'your_bitly_login';

/*------------------------------------------------
  FLICKR
  @TODO Put the Flickr-specific settings into the
        Flickr plugin's configuration page.
------------------------------------------------*/

// To integrate with Flickr, get a Flickr API key at http://flickr.com and fill in the value below.
// Otherwise, set to ''.
$THINKUP_CFG['flickr_api_key']            = 'your_flickr_api_key';

/*------------------------------------------------
  RECAPTCHA
  @TODO Abstract into a plugin.
  @TODO Put the reCAPTCHA-specific settings into
        the reCAPTCHA plugin's configuration page.
------------------------------------------------*/

// To enable reCAPTCHA, set enable to true and fill in your keys and libpath; more info at http://recaptcha.net/plugins/php/
// Otherwise, leave these settings as-is.
$THINKUP_CFG['recaptcha_enable']          = false;
$THINKUP_CFG['recaptcha_public_key']      = '';
$THINKUP_CFG['recaptcha_private_key']     = '';
?>
