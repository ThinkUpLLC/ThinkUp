<?php
/**
 *
 * ThinkUp/tests/migration-assertions.php
 *
 * Copyright (c) 2009-2011 Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Upgrade Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 * Database migration assertions to test during WebTestOfUpgradeDatabase
 */
$LATEST_VERSION = '0.8';

$MIGRATIONS = array(
    /* beta 0.1 */
    '0.1' => array(
        'zip_url' => 'http://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.1.zip',
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_owners email',
                    'match' => "/varchar\(200\)/",
                    'column' => 'Type', 
                )
            )
        )
    ),

    /* beta 0.2 */
    '0.2' => array(
        'zip_url' => 'http://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.2.zip',
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_owners email',
                    'match' => "/varchar\(200\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts post_id',
                    'match' => "/bigint\(11\)/",
                    'column' => 'Type', 
                )
            )
        )
    ),

    /* beta 0.3 */
    '0.3' => array(
        'zip_url' => 'http://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.3.zip',
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_posts in_retweet_of_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_reply_to_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_links post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_post_errors post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_users last_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances last_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
            )
        )
    ),

    /* beta 0.4 */
    '0.4' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.4.1.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_options option_id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 
                        "SELECT option_value FROM tu_options WHERE namespace = 'application_options' " .
                        "AND option_name = 'database_version'",
                    'match' => "/^0\.4$/",
                    'column' => 'option_value', 
                )
            )
        )
    ),

    /* beta 0.5 */
    '0.5' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.5.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'show index from tu_posts where Key_name = \'postnetwk\' and Column_name = '.
                    '\'post_id\' and Non_unique = 0;',
                    'match' => "/postnetwk/",
                    'column' => 'Key_name', 
                ),
                array(
                    'query' => 'show index from tu_links where Key_name = \'url\' and Column_name = '.
                    '\'url\' and Non_unique = 0;',
                    'match' => "/url/",
                    'column' => 'Key_name', 
                ),
            )
        )
    ),

    /* beta 0.6 */
    '0.6' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.6.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_instances last_favorite_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances last_unfav_page_checked',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances last_page_fetched_favorites',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances favorites_profile',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances owner_favs_in_system',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_users favorites_count',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_favorites post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
           )
        )
    ),

    /* beta 0.7 */
    '0.7' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.7.zip',
        'migrations' => 1,
        'setup_sql' => array("DROP TABLE IF EXISTS tu_plugin_options",
                            "CREATE TABLE  `tu_plugin_options` (" .
                            "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ," .
                            "`plugin_id` INT NOT NULL ," .
                            "`option_name` VARCHAR( 255 ) NOT NULL ," .
                            "`option_value` VARCHAR( 255 ) NOT NULL ," .
                            "INDEX (  `plugin_id` )" .
                            ") ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin",
                            "INSERT INTO tu_plugin_options (plugin_id, option_name, option_value) " .
                            "VALUES (2345, 'test_plugin_name', 'test_plugin_value')" 
        ),
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_owners failed_logins',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_owners account_status',
                    'match' => "/varchar\(150\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_rt_of_user_id',
                    'match' => "/bigint\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts old_retweet_count_cache',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => "SELECT namespace FROM tu_options WHERE namespace LIKE 'plugin_options-2345' ",
                    'match' => "/plugin_options-2345/",
                    'column' => 'namespace', 
                )
           )
        )
    ),

    /* beta 0.8 */
    '0.8' => array(
        'zip_url' => 'file://./build/thinkup.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => "SHOW TABLES LIKE 'tu_plugin_options'", // table is dropped
                    'no_match' => true,
                ),
                array(
                    'query' => "DESCRIBE tu_posts post_text", // enlarged post_text field
                    'match' => "/varchar\(420\)/",
                    'column' => 'Type',
                )
           )
        )
    ),
);
