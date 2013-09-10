<?php
/************************************************/
/***  APPLICATION CONFIG                      ***/
/************************************************/

// Application title prefix - 'ThinkUp' will be appended to it in page titles
$THINKUP_CFG['app_title_prefix']                 = '';

// Public path of thinkup's /webapp/ folder on your web server.
// For example, if the /webapp/ folder is located at http://yourdomain/thinkup/, set to '/thinkup/'.
$THINKUP_CFG['site_root_path']            = '/';

// Server path to /thinkup/ source code folder, dirname( __FILE__ ) . '/'; by default
$THINKUP_CFG['source_root_path']          = dirname(dirname( __FILE__ )) . '/';

// Server path to writable data directory, $THINKUP_CFG['source_root_path'] . 'data/' by default
$THINKUP_CFG['datadir_path']              = $THINKUP_CFG['source_root_path'] . 'webapp/data/';

// Your timezone
$THINKUP_CFG['timezone']                  = 'America/Los_Angeles';

// Toggle Smarty caching. true: Smarty caching on, false: Smarty caching off
$THINKUP_CFG['cache_pages']               = false;

// Smarty file cache lifetime in seconds; defaults to 600 (10 minutes)caching
$THINKUP_CFG['cache_lifetime']               = 600;

// The crawler, when triggered by requests to the RSS feed, will only launch if it's been
// 20 minutes or more since the last crawl.
$THINKUP_CFG['rss_crawler_refresh_rate']  = 20;

/************************************************/
/***  DATABASE CONFIG                         ***/
/************************************************/

$THINKUP_CFG['db_host']                   = 'localhost'; //On a shared host? Try mysql.yourdomain.com, or see your web host's documentation.
$THINKUP_CFG['db_type']                   = 'mysql';
$THINKUP_CFG['db_user']                   = 'root';
$THINKUP_CFG['db_password']               = '';
$THINKUP_CFG['db_name']                   = 'thinkup';
$THINKUP_CFG['db_socket']                 = '';
$THINKUP_CFG['db_port']                   = '';
$THINKUP_CFG['table_prefix']              = 'tu_';

/************************************************/
/***  DEVELOPER CONFIG                        ***/
/************************************************/

// Full server path to crawler.log.
$THINKUP_CFG['log_location']              = $THINKUP_CFG['datadir_path'] . '/logs/crawler.log';
//$THINKUP_CFG['log_location']              = false;

// Verbosity of log. 0 is everything, 1 is user messages, 2 is errors only
$THINKUP_CFG['log_verbosity']             = 0;

// Full server path to stream processor log.
$THINKUP_CFG['stream_log_location']       = $THINKUP_CFG['datadir_path'] . '/logs/stream.log';
//$THINKUP_CFG['stream_log_location']       = false;

// Full server path to sql.log. To not log queries, set to null.
// $THINKUP_CFG['sql_log_location']          = $THINKUP_CFG['datadir_path'] . '/logs/sql.log';
$THINKUP_CFG['sql_log_location']          = null;

// How many seconds does a query take before it gets logged as a slow query?
$THINKUP_CFG['slow_query_log_threshold']  = 2.0;

$THINKUP_CFG['debug']                     = true;

$THINKUP_CFG['enable_profiler']           = false;

// Set this to true if you want your PDO object's database connection's charset to be explicitly set to utf8.
// If false (or unset), the database connection's charset will not be explicitly set.
$THINKUP_CFG['set_pdo_charset']           = false;

//TESTS OVERRIDE: Run against the tests database and use unpackaged developer /thinkup/webapp/ folder structure
if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") && ! isset($_SESSION["RD_MODE"])
|| (getenv("MODE")=="TESTS" && ! getenv("RD_MODE")=="1")) {
// Full server path to /thinkup/ source code folder.
//    $THINKUP_CFG['source_root_path']          = '/your-server-path-to/thinkup/';
//    $THINKUP_CFG['db_user']                   = 'your_test_database_username';
//    $THINKUP_CFG['db_password']               = 'your_test_database_password';
//    $THINKUP_CFG['db_name']                   = 'your_test_database_name'; //by default, thinkup_tests
    $THINKUP_CFG['invalid_db_host']                = 'localcheese';
    ini_set('error_reporting', E_STRICT);
}

//Test RAM disk database override: Set this to run tests against the RAM disk tests database
if (isset($_SESSION["RD_MODE"]) || getenv("RD_MODE")=="1") {
    $THINKUP_CFG['db_user']                   = 'your_ram_disk_test_database_username';
    $THINKUP_CFG['db_password']               = 'your_ram_disk_test_database_password';
    $THINKUP_CFG['db_name']                   = $THINKUP_CFG['db_name'] . '_rd';
}