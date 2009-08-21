<?php
 
/** APPLICATION CONFIG **/
$TWITALYTIC_CFG['debug'] = true;
$TWITALYTIC_CFG['timeZone'] = 'TZ=US/Pacific';
$TWITALYTIC_CFG['archive_limit'] = 3200; //http://apiwiki.twitter.com/Things-Every-Developer-Should-Know#6Therearepaginationlimits
$TWITALYTIC_CFG['cache_pages'] = 1;

// Get the following by registering your instance with Twitter at http://twitter.com/oauth_clients/
$TWITALYTIC_CFG['oauth_consumer_key'] = 'yourconsumerkey';
$TWITALYTIC_CFG['oauth_consumer_secret'] = 'yourconsumersecret';
 
$TWITALYTIC_CFG['app_title'] = 'Twitalytic';
$TWITALYTIC_CFG['log_location'] = '/your-path-to/twitalytic/crawler/logs/';
$TWITALYTIC_CFG['site_root_path'] = '/';
$TWITALYTIC_CFG['smarty_path'] = '/usr/local/php5/lib/php/smarty/libs/';
 
// Get the following here: http://bit.ly
$TWITALYTIC_CFG['bitly_api_key'] = 'yourkey';
$TWITALYTIC_CFG['bitly_login'] = 'yourbitlylogin';
 
/** DATABASE CONFIG **/
$TWITALYTIC_CFG['db_host'] = "localhost";
$TWITALYTIC_CFG['db_user'] = "user";
$TWITALYTIC_CFG['db_password'] = "s3cret";
$TWITALYTIC_CFG['db_name'] ="twitalytic";
 
?>