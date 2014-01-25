<?php
/**
 * Will be deployed at /vagrant/config.inc.php after the database is created
 * and user access configured. This file is a copy of config.sample.inc.php
 * with the TESTS overrides removed
 */
$THINKUP_CFG['app_title_prefix']            = "";
$THINKUP_CFG['site_root_path']              = '/';
$THINKUP_CFG['source_root_path']            = dirname( __FILE__ ) . '/';
$THINKUP_CFG['datadir_path']                = '/thinkup-data/'; // Writeable folder shared from the host
$THINKUP_CFG['timezone']                    = 'UTC';
$THINKUP_CFG['cache_pages']                 = true;
$THINKUP_CFG['cache_lifetime']              = 600;
$THINKUP_CFG['rss_crawler_refresh_rate']    = 20;
$THINKUP_CFG['mandrill_api_key']            = '';

$THINKUP_CFG['db_host']         = 'localhost';
$THINKUP_CFG['db_type']         = 'mysql';
$THINKUP_CFG['db_user']         = 'thinkup_sql';
$THINKUP_CFG['db_password']     = 'thinkup_password';
$THINKUP_CFG['db_name']         = 'thinkup';
$THINKUP_CFG['db_socket']       = '';
$THINKUP_CFG['db_port']         = '';
$THINKUP_CFG['table_prefix']    = 'tu_';

$THINKUP_CFG['log_location']              = false;
$THINKUP_CFG['log_verbosity']             = 0;
$THINKUP_CFG['stream_log_location']       = false;
$THINKUP_CFG['sql_log_location']          = null;
$THINKUP_CFG['slow_query_log_threshold']  = 2.0;
$THINKUP_CFG['debug']                     = false;
$THINKUP_CFG['enable_profiler']           = false;
$THINKUP_CFG['set_pdo_charset']           = false;

// Set aggressive time limit for long crawls
set_time_limit(500);
