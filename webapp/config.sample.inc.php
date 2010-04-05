<?php
 
/** APPLICATION CONFIG **/
$THINKTANK_CFG['debug'] = true;
$THINKTANK_CFG['GMT_offset'] = 7; //Pacific: 7, Eastern: 4
$THINKTANK_CFG['cache_pages'] = 1; //1 = Smarty caching on; 0 = Smarty caching off

$THINKTANK_CFG['app_title'] = 'ThinkTank';
$THINKTANK_CFG['log_location'] = '/your-path-to/thinktank/logs/crawler.log';

$THINKTANK_CFG['sql_log_location'] = '/your-path-to/thinktank/logs/sql.log'; //Set to null to not log queries
$THINKTANK_CFG['slow_query_log_threshold'] = 2.0; //how many seconds does a query take before it gets logged as a slow query

// Set the following to the public path of the webapp on your web server
// For example, if it will belocated at http://example.com/tweets/, set this to '/tweets/'
$THINKTANK_CFG['site_root_path'] = '/';
$THINKTANK_CFG['source_root_path'] = '/your-path-to/thinktank/';
$THINKTANK_CFG['smarty_path'] = $THINKTANK_CFG['source_root_path'].'extlib/Smarty-2.6.26/libs/';


// Set whether or not your site's registration page is available
// TODO Build email invitation system so this isn't a binary thing
$THINKTANK_CFG['is_registration_open'] = true;

/** DATABASE CONFIG **/
$THINKTANK_CFG['db_host'] = "localhost";
$THINKTANK_CFG['db_user'] = "user";
$THINKTANK_CFG['db_password'] = "s3cret";
$THINKTANK_CFG['db_name'] ="thinktank";
$THINKTANK_CFG['table_prefix'] = 'tt_';


/** PLUGIN CONFIG **/
// TODO Put the Twitter-specific settings into the Twitter plugin's configuration page
$THINKTANK_CFG['archive_limit'] = 3200; //http://apiwiki.twitter.com/Things-Every-Developer-Should-Know#6Therearepaginationlimits
 // Get the following by registering your instance with Twitter at http://twitter.com/oauth_clients/
$THINKTANK_CFG['oauth_consumer_key'] = 'yourconsumerkey';
$THINKTANK_CFG['oauth_consumer_secret'] = 'yourconsumersecret';

// TODO Abstract Bit.ly into a plugin and put these settings into the plugin config page
// Get the following here: http://bit.ly
$THINKTANK_CFG['bitly_api_key'] = 'yourkey';
$THINKTANK_CFG['bitly_login'] = 'yourbitlylogin';

// TODO Put the following in the Flickr plugin config page
// To see Flickr image thumbnails, set to '' if not
// Get the following at http://flickr.com
$THINKTANK_CFG['flickr_api_key'] = '';

// TODO Port Recaptcha to a plugin with in-webapp settings
// To enable recaptcha set enable to TRUE 
// and fill in your keys and libpath
// More info: http://recaptcha.net/plugins/php/
$THINKTANK_CFG['recaptcha_enable'] = false;
$THINKTANK_CFG['recaptcha_path'] = "";
$THINKTANK_CFG['recaptcha_public_key'] = "";
$THINKTANK_CFG['recaptcha_private_key'] = "";

// Facebook plugin settings 
// TODO put these in a plugin options tables
$THINKTANK_CFG['facebook_callback_url']    = 'http://yourdomain/pathtothinktank/account/';
$THINKTANK_CFG['facebook_api_key']         = 'XXX';
$THINKTANK_CFG['facebook_api_secret']      = 'YYY';
$THINKTANK_CFG['facebook_base_fb_url']     = 'connect.facebook.com';
?>
