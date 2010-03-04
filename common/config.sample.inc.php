<?php
 
/** APPLICATION CONFIG **/
$THINKTANK_CFG['debug'] = true;
$THINKTANK_CFG['GMT_offset'] = 7; //Pacific: 7, Eastern: 4
$THINKTANK_CFG['archive_limit'] = 3200; //http://apiwiki.twitter.com/Things-Every-Developer-Should-Know#6Therearepaginationlimits
$THINKTANK_CFG['cache_pages'] = 1;
 
// Get the following by registering your instance with Twitter at http://twitter.com/oauth_clients/
$THINKTANK_CFG['oauth_consumer_key'] = 'yourconsumerkey';
$THINKTANK_CFG['oauth_consumer_secret'] = 'yourconsumersecret';
 
$THINKTANK_CFG['app_title'] = 'ThinkTank';
$THINKTANK_CFG['log_location'] = '/your-path-to/thinktank/crawler/logs/crawler.log';

$THINKTANK_CFG['sql_log_location'] = '/your-path-to/thinktank/crawler/logs/sql.log'; //Set to null to not log queries
$THINKTANK_CFG['slow_query_log_threshold'] = 2.0; //how many seconds does a query take before it gets logged as a slow query

$THINKTANK_CFG['smarty_path'] = '/usr/local/php5/lib/php/smarty/libs/';

// Set the following to the public path of the webapp on your web server
// For example, if it will belocated at http://example.com/tweets/, set this to '/tweets/'
$THINKTANK_CFG['site_root_path'] = '/';

// Set whether or not your site's registration page is available
// TODO: Build email invitation system so this isn't a binary thing
$THINKTANK_CFG['is_registration_open'] = true;

// Get the following here: http://bit.ly
$THINKTANK_CFG['bitly_api_key'] = 'yourkey';
$THINKTANK_CFG['bitly_login'] = 'yourbitlylogin';
 
// To see Flickr image thumbnails, set to '' if not
// Get the following at http://flickr.com
$THINKTANK_CFG['flickr_api_key'] = '';

// To enable recaptcha set enable to TRUE 
// and fill in your keys and libpath
// More info: http://recaptcha.net/plugins/php/
$THINKTANK_CFG['recaptcha_enable'] = false;
$THINKTANK_CFG['recaptcha_path'] = "";
$THINKTANK_CFG['recaptcha_public_key'] = "";
$THINKTANK_CFG['recaptcha_private_key'] = "";

/** DATABASE CONFIG **/
$THINKTANK_CFG['db_host'] = "localhost";
$THINKTANK_CFG['db_user'] = "user";
$THINKTANK_CFG['db_password'] = "s3cret";
$THINKTANK_CFG['db_name'] ="thinktank";
$THINKTANK_CFG['table_prefix'] = 'tt_';
 
?>
