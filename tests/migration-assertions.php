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
 * @copyright 2009-2011 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 * Database migration assertions to test during WebTestOfUpgradeDatabase
 */
$LATEST_VERSION = '0.16';
$TOTAL_MIGRATION_COUNT = 215;

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
                    'query' => "SELECT namespace FROM tu_options WHERE namespace = 'plugin_options-2345'",
                    'match' => "/plugin_options-2345/",
                    'column' => 'namespace', 
                )
            )
        )
    ),

    /* beta 0.8 */
    '0.8' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.8.zip',
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

    /* beta 0.9 */
    '0.9' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.9.zip',
        'migrations' => 1,
        'setup_sql' => array(
                      "INSERT INTO tu_plugins (name, folder_name) VALUES ('Flickr Thumbnails', 'flickthumbnails');"),
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Deleted plugin row
                    'query' => "SELECT id FROM tu_plugins WHERE folder_name = 'flickrthumbnails'; ", 
                    'no_match' => true,
                ),
                array(
                    // Dropped column from tu_instance
                    'query' => 'DESCRIBE tu_instances api_calls_to_leave_unmade_per_minute',
                    'no_match' => true 
                 )
            )
        )
    ),

    /* beta 0.10 */
    '0.10' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.10.1.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Modified user_id index
                    'query' => "SHOW INDEX FROM tu_follows WHERE Key_name = 'user_id' and Column_name = ".
                    "'network' and Non_unique = 0;",
                    'match' => "/user_id/",
                    'column' => 'Key_name', 
                 ),
                array(
                    // Added active index
                    'query' => "SHOW INDEX FROM tu_follows WHERE Key_name = 'active' and Column_name = ".
                    "'network' and Non_unique = 1;",
                    'match' => "/active/",
                    'column' => 'Key_name', 
                 ),
                array(
                    // Added last_seen index
                    'query' => "SHOW INDEX FROM tu_follows WHERE Key_name = 'network' and Column_name = ".
                    "'network' and Non_unique = 1;",
                    'match' => "/network/",
                    'column' => 'Key_name', 
                 ),
                array(
                    // Modified field definition
                    'query' => "DESCRIBE tu_instances is_archive_loaded_replies",
                    'match' => "/int\(1\)/",
                    'column' => 'Type',
                ),
                array(
                    // Modified field definition
                    'query' => "DESCRIBE tu_instances is_archive_loaded_follows", 
                    'match' => "/int\(1\)/",
                    'column' => 'Type',
                ),
                array(
                    // Removed column
                    'query' => 'DESCRIBE tu_instances total_users_in_system',
                    'no_match' => true 
                 ),
                array(
                    // Moved this column out of this table...
                    'query' => "DESCRIBE tu_instances last_page_fetched_replies", 
                    'no_match' => true 
                ),
                 array(
                    // ...and into this table.
                    'query' => "DESCRIBE tu_instances_twitter last_page_fetched_replies", 
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
            )
        )
    ),

    /* beta 0.11 */
    '0.11' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.11.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Back-protect private posts
                    'query' => "SELECT a.user_name, p.post_id, p.post_text, p.is_protected, a.is_protected FROM ".
                    "tu_posts p INNER JOIN tu_users a ON p.author_user_id=a.user_id AND p.network=a.network WHERE ".
                    "a.is_protected=1 AND p.is_protected=0; ", 
                    'no_match' => true,
                )
            )
        )
    ),

    /* beta 0.12 */
    '0.12' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.12.1.zip',
        'migrations' => 1,
        'setup_sql' => array("INSERT INTO tu_plugins (name, folder_name) VALUES ('Embed Thread', 'embedthread');"),
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Created invites table
                    'query' => 'DESCRIBE tu_invites invite_code',
                    'match' => "/varchar\(10\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created invites table
                    'query' => 'DESCRIBE tu_invites created_time',
                    'match' => "/timestamp/",
                    'column' => 'Type', 
                ),
                array(
                    // Deleted Embed Thread plugin
                    'query' => "SELECT * FROM tu_plugins WHERE folder_name='embedthread'",
                    'no_match' => true,
                )
            )
        )
    ),

    /* beta 0.13 */
    '0.13' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.13.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Added retweet_count_api field
                    'query' => 'DESCRIBE tu_posts retweet_count_api',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Added unique user_id/network key to tu_users
                    'query' => 'show index from tu_users where Key_name = \'user_id\' and Column_name = '.
                    '\'user_id\' and Non_unique = 0;',
                    'match' => "/user_id/",
                    'column' => 'Key_name', 
                )
            )
        )
    ),

    /* beta 0.14 */
    '0.14' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.14.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Created tu_mentions table
                    'query' => 'DESCRIBE tu_mentions count_cache',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_mentions_posts table
                    'query' => 'DESCRIBE tu_mentions_posts mention_id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_hashtags table
                    'query' => 'DESCRIBE tu_hashtags count_cache',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_hashtags_posts table
                    'query' => 'DESCRIBE tu_hashtags_posts hashtag_id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_places table
                    'query' => 'DESCRIBE tu_places place_id',
                    'match' => "/varchar\(100\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_places_posts table
                    'query' => 'DESCRIBE tu_places_posts post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_stream_data table
                    'query' => 'DESCRIBE tu_stream_data data',
                    'match' => "/text/",
                    'column' => 'Type', 
                ),
                array(
                    // Created tu_stream_procs table
                    'query' => 'DESCRIBE tu_stream_procs instance_id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Added fav_timestamp field
                    'query' => 'DESCRIBE tu_favorites fav_timestamp',
                    'match' => "/timestamp/",
                    'column' => 'Type', 
                ),
                array(
                    // Added api_key field
                    'query' => 'DESCRIBE tu_owners api_key',
                    'match' => "/varchar\(32\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Changed pub_date from timestamp to datetime
                    'query' => 'DESCRIBE tu_posts pub_date',
                    'match' => "/datetime/",
                    'column' => 'Type', 
                )
            )
        )
    ),

    /* beta 0.15 */
    '0.15' => array(
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup_0.15.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Created first_seen column
                    'query' => 'DESCRIBE tu_follows first_seen',
                    'match' => "/timestamp/",
                    'column' => 'Type', 
                ),
                array(
                    // Created pwd_salt column
                    'query' => 'DESCRIBE tu_owners pwd_salt',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created description column
                    'query' => 'DESCRIBE tu_links description',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created image_src column
                    'query' => 'DESCRIBE tu_links image_src',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Created caption column
                    'query' => 'DESCRIBE tu_links caption',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type', 
                ),
                array(
                    // Dropped is_image column
                    'query' => 'DESCRIBE tu_links is_image',
                    'no_match' => true
                ),
                array(
                    // Created favlike_count_cache column
                    'query' => 'DESCRIBE tu_posts favlike_count_cache',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
            )
        )
     ),

    /* beta 0.16 */
    '0.16' => array(
        'zip_url' => 'file://./build/thinkup.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_instances network_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances network_viewer_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_favorites author_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_favorites fav_of_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_users user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_users last_post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts author_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_reply_to_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_rt_of_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_reply_to_post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts in_retweet_of_post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_posts post_text',
                    'match' => "/text/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_follows follower_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_follows user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_follower_count network_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_user_errors user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_user_errors error_issued_to_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_post_errors post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_post_errors error_issued_to_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_mentions user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_mentions_posts author_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_favorites post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_hashtags_posts post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_instances last_post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_mentions_posts post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_places_posts post_id',
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_links post_key',
                    'match' => "/int\(11\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'DESCRIBE tu_links post_id',
                    'no_match' => true 
                 ),
                array(
                    'query' => 'DESCRIBE tu_links network',
                    'no_match' => true 
                 ),
                array(
                    'query' => 'SHOW INDEX FROM tu_links WHERE Key_name = \'url\' AND Column_name = '.
                    '\'url\' AND Non_unique = 0;',
                    'match' => "/url/",
                    'column' => 'Key_name', 
                ),
                array(
                    'query' => 'SHOW INDEX FROM tu_links WHERE Key_name = \'post_key\' AND Column_name = '.
                    '\'post_key\' AND Non_unique = 1;',
                    'match' => "/post_key/",
                    'column' => 'Key_name', 
                ),
            )
        )
     )
);
