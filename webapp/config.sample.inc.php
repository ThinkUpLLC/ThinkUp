<?php
/************************************************/
/***  APPLICATION CONFIG                      ***/
/************************************************/

// Application title prefix - 'ThinkUp' will be appended to it in page titles
// For example, to name your ThinkUp "Angelina Jolie's ThinkUp", set this to "Angelina Jolie's "
$THINKUP_CFG['app_title_prefix']                 = "";

// Public path of ThinkUp's source folder on your web server.
// For example, if ThinkUp is located at http://yourdomain/thinkup/, set to '/thinkup/'.
$THINKUP_CFG['site_root_path']            = '/thinkup/';

// Server path to /thinkup/ source code folder, dirname( __FILE__ ) . '/'; by default
$THINKUP_CFG['source_root_path']          = dirname( __FILE__ ) . '/';

// Server path to writable data directory, $THINKUP_CFG['source_root_path'] . 'data/' by default
$THINKUP_CFG['datadir_path']              = $THINKUP_CFG['source_root_path'] . 'data/';

// Your timezone
$THINKUP_CFG['timezone']                  = 'UTC';

// Toggle Smarty caching. true: Smarty caching on, false: Smarty caching off
$THINKUP_CFG['cache_pages']               = true;

// Smarty file cache lifetime in seconds; defaults to 600 (10 minutes) caching
$THINKUP_CFG['cache_lifetime']               = 600;

// The crawler, when triggered by requests to the RSS feed, will only launch if it's been
// 20 minutes or more since the last crawl.
$THINKUP_CFG['rss_crawler_refresh_rate']  = 20;

// Optional Mandrill API key. Set this to a valid key to send email via Mandrill instead of PHP's mail() function..
// Get key at https://mandrillapp.com/settings/ in "SMTP & API Credentials"
$THINKUP_CFG['mandrill_api_key'] = '';

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
// $THINKUP_CFG['log_location']              = $THINKUP_CFG['datadir_path'] . 'logs/crawler.log';
$THINKUP_CFG['log_location']              = false;

// Verbosity of log. 0 is everything, 1 is user messages, 2 is errors only
$THINKUP_CFG['log_verbosity']             = 0;

// Full server path to stream processor log.
// $THINKUP_CFG['stream_log_location']       = $THINKUP_CFG['datadir_path'] . 'logs/stream.log';
$THINKUP_CFG['stream_log_location']       = false;

// Full server path to sql.log. To not log queries, set to null.
// $THINKUP_CFG['sql_log_location']          = $THINKUP_CFG['datadir_path'] . 'logs/sql.log';
$THINKUP_CFG['sql_log_location']          = null;

// How many seconds does a query take before it gets logged as a slow query?
$THINKUP_CFG['slow_query_log_threshold']  = 2.0;

$THINKUP_CFG['debug']                     = false;

$THINKUP_CFG['enable_profiler']           = false;

// Set this to true if you want your PDO object's database connection's charset to be explicitly set to utf8.
// If false (or unset), the database connection's charset will not be explicitly set.
$THINKUP_CFG['set_pdo_charset']           = false;

//TESTS OVERRIDE: Assign variables below to use different settings during test builds
if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") && ! isset($_SESSION["RD_MODE"])
|| (getenv("MODE")=="TESTS" && ! getenv("RD_MODE")=="1")) {
    //    $THINKUP_CFG['source_root_path']          = '/your-server-path-to/thinkup/';
    //    $THINKUP_CFG['db_user']                   = 'your_test_database_username';
    //    $THINKUP_CFG['db_password']               = 'your_test_database_password';
    //    $THINKUP_CFG['db_name']                   = 'your_test_database_name'; //by default, thinkup_tests
    $THINKUP_CFG['cache_pages']               = false;
    $THINKUP_CFG['debug']                     = true;
    $THINKUP_CFG['timezone']                  = 'UTC';
    ini_set('error_reporting', E_STRICT);
}

//Test RAM disk database override: Set this to run tests against the RAM disk tests database
if (isset($_SESSION["RD_MODE"]) || getenv("RD_MODE")=="1") {
    //    $THINKUP_CFG['db_user']                   = 'your_ram_disk_test_database_username';
    //    $THINKUP_CFG['db_password']               = 'your_ram_disk_test_database_password';
    //    $THINKUP_CFG['db_name']                   = $THINKUP_CFG['db_name'] . '_rd';
}

//Set aggressive time limit for long crawls
set_time_limit(500);
