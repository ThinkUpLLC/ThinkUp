<?php
/**
 *
 * ThinkUp/tests/migration-assertions.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 * Database migration assertions to test during WebTestOfUpgradeDatabase
 */
$LATEST_VERSION = '2.0-beta.9';
$TOTAL_MIGRATION_COUNT = 293;

$MIGRATIONS = array(
    /* beta 0.1 */
    '0.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.1.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.2.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.3.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.4.1.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.5.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.6.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.7.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.8.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.9.zip',
        'migrations' => 1,
        'setup_sql' => array("INSERT INTO tu_plugins (name, folder_name, is_active) VALUES ('Flickr Thumbnails', ".
                " 'flickthumbnails', 1);"),
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.10.1.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.11.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.12.1.zip',
        'migrations' => 1,
        'setup_sql' => array("INSERT INTO tu_plugins (name, folder_name, is_active) VALUES ('Embed Thread', ".
        "'embedthread', 1);"),
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.13.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.14.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.15.zip',
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
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.16.zip',
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
     ),

     /* beta 0.17 */
    '0.17' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-0.17.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_groups id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_group_members group_id',
                    'match' => "/varchar\(50\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_group_member_count member_user_id',
                    'match' => "/varchar\(30\)/",
                    'column' => 'Type',
                ),
            )
        )
     ),

     /* 1.0 */
    '1.0' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.zip',
        'migrations' => 0,
     ),

     /* 1.0.1 */
    '1.0.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.1.zip',
        'migrations' => 0,
     ),

     /* 1.0.2 */
    '1.0.2' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.2.zip',
        'migrations' => 0,
     ),

     /* 1.0.3 */
    '1.0.3' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.3.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'DESCRIBE tu_links_short id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_links_short link_id',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_links_short short_url',
                    'match' => "/varchar\(100\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_links_short click_count',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'DESCRIBE tu_links_short first_seen',
                    'match' => "/timestamp/",
                    'column' => 'Type',
                ),
                array(
                    'query' => 'SHOW INDEX FROM tu_links_short WHERE Key_name = \'short_url\';',
                    'match' => "/short_url/",
                    'column' => 'Key_name',
                ),
                array(
                    'query' => 'SHOW INDEX FROM tu_links_short WHERE Key_name = \'link_id\';',
                    'match' => "/link_id/",
                    'column' => 'Key_name',
                ),
                array(
                    'query' => 'DESCRIBE tu_links clicks',
                    'no_match' => true
                 ),
            )
         )
     ),

     /* 1.0.4 */
    '1.0.4' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.4.zip',
        'migrations' => 0,
     ),

     /* 1.0.5 */
    '1.0.5' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.5.zip',
        'migrations' => 0,
     ),

     /* 1.0.6 */
    '1.0.6' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.6.zip',
        'migrations' => 0,
     ),

     /* 1.0.7 */
    '1.0.7' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.7.zip',
        'migrations' => 0,
     ),

     /* 1.0.8.1 */
    '1.0.8.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.0.8.1.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                 array(
                // Created tu_links table
                'query' => 'DESCRIBE tu_insights id;',
                'match' => "/int\(11\)/",
                'column' => 'Type',
                ),
                // Added network_follower_user index to tu_follows
                array(
                    'query' => 'SHOW INDEX FROM tu_follows WHERE Key_name = \'network_follower_user\';',
                    'match' => "/network_follower_user/",
                    'column' => 'Key_name',
                ),
                // Added auth_error field to tu_owner_instances
                array(
                    'query' => 'DESCRIBE tu_owner_instances auth_error;',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
            )
        )
     ),

     /* 1.1 */
    '1.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.1.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Changed tu_insights.text to text
                    'query' => 'DESCRIBE tu_insights text;',
                    'match' => "/text/",
                    'column' => 'Type',
                ),
                array(
                    // Added prefix field to tu_insights
                    'query' => 'DESCRIBE tu_insights prefix;',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
                array(
                    // Added map image field to tu_places
                    'query' => 'DESCRIBE tu_places map_image;',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
                array(
                    // Added icon field to tu_places
                    'query' => 'DESCRIBE tu_places icon;',
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
                array(
                    // Added is_archive_loaded_posts field to tu_instances
                    'query' => 'DESCRIBE tu_instances is_archive_loaded_posts;',
                    'match' => "/tinyint\(1\)/",
                    'column' => 'Type',
                ),
                array(
                    // Default value for tu_links expanded_url
                    'query' => 'DESCRIBE tu_links expanded_url;',
                    'match' => "/^$/",
                    'column' => 'Default',
                ),
                array(
                    // Default value for tu_users last_post_id
                    'query' => 'DESCRIBE tu_users last_post_id;',
                    'match' => "/^$/",
                    'column' => 'Default',
                ),
                array(
                    // Default value for tu_links image_src
                    'query' => 'DESCRIBE tu_links image_src;',
                    'match' => "/^$/",
                    'column' => 'Default',
                ),
                array(
                    // Created tu_insight_baselines table
                    'query' => 'DESCRIBE tu_insight_baselines instance_id;',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
            )
        )
     ),

     /* 1.1.1 */
    '1.1.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.1.1.zip',
        'migrations' => 0,
     ),

     /* 1.2 */
    '1.2' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.2.zip',
        'migrations' => 0,
     ),

     /* 1.2.1 */
    '1.2.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/thinkup-1.2.1.zip',
        'migrations' => 0,
     ),

     /* 2.0-beta.1 */
    '2.0-beta.1' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.1.zip',
        'migrations' => 3,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Added tu_insights.filename
                    'query' => 'DESCRIBE tu_insights filename;',
                    'match' => "/varchar\(100\)/",
                    'column' => 'Type',
                ),
                array(
                    // Default value for tu_plugins is_active
                    'query' => 'DESCRIBE tu_plugins is_active;',
                    'match' => '/1/',
                    'column' => 'Default',
                ),
                array(
                    // All plugins are active
                    'query' => "SELECT id FROM tu_plugins WHERE is_active = 0",
                    'no_match' => true,
                ),
             )
        )
     ),

     /* 2.0-beta.2 */
    '2.0-beta.2' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.2.zip',
        'migrations' => 0,
     ),

     /* 2.0-beta.3 */
    '2.0-beta.3' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.3.zip',
        'migrations' => 3,
        'setup_sql' => array("INSERT INTO tu_options (option_id, option_name, option_value) " .
                            "VALUES (2345, 'favs_older_pages', 'test_plugin_value');".
                            "INSERT INTO tu_options (option_id, option_name, option_value) " .
                            "VALUES (2346, 'favs_cleanup_pages', 'test_plugin_value');"
                            ),
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => "DESCRIBE tu_instances_twitter last_unfav_page_checked", // field dropped
                    'no_match' => true,
                ),
                array(
                    'query' => "DESCRIBE tu_instances_twitter last_page_fetched_favorites", // field dropped
                    'no_match' => true,
                ),
                array(
                    // Options no longer exist
                    'query' => "SELECT * FROM tu_options WHERE option_name='favs_older_pages' OR ".
                    "option_name='favs_cleanup_pages';",
                    'no_match' => true,
                ),
             )
        )
     ),

     /* 2.0-beta.4 */
    '2.0-beta.4' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.4.zip',
        'migrations' => 0,
     ),

     /* 2.0-beta.5 */
    '2.0-beta.5' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.4.zip',
        'migrations' => 0,
     ),

     /* 2.0-beta.6 */
    '2.0-beta.6' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.6.zip',
        'migrations' => 0,
     ),

     /* 2.0-beta.7 */
    '2.0-beta.7' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.7.zip',
        'migrations' => 0,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Created tu_instances_hashtags table
                    'query' => 'DESCRIBE tu_instances_hashtags id;',
                    'match' => "/int\(20\)/",
                    'column' => 'Type',
                ),
                array(
                    // Added time_generated field
                    'query' => 'DESCRIBE tu_insights time_generated;',
                    'match' => "/datetime/",
                    'column' => 'Type',
                ),
                array(
                    // Added time_generated field
                    'query' => 'DESCRIBE tu_insights time_updated;',
                    'match' => "/timestamp/",
                    'column' => 'Type',
                ),
             )
        )
     ),

     /* 2.0-beta.8 */
    '2.0-beta.8' => array(
        'zip_url' => 'https://thinkup.com/downloads/beta/thinkup-2.0-beta.8.zip',
        'migrations' => 0,
    ),

     /* 2.0-beta.9 */
    '2.0-beta.9' => array(
        'zip_url' => 'file://./build/thinkup.zip',
        'migrations' => 0,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    // Add tu_users.is_verified field
                    'query' => 'DESCRIBE tu_users is_verified;',
                    'match' => "/tinyint\(1\)/",
                    'column' => 'Type',
                ),
                array(
                    // Create tu_count_history table
                    'query' => 'DESCRIBE tu_count_history count;',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    // Drop tu_follower_count table
                    'query' => "SHOW TABLES LIKE 'tu_follower_count'", // table is dropped;',
                    'no_match' => true
                ),
                array(
                    // Add tu_count_history index network_user_id
                    'query' => "SHOW INDEX FROM tu_count_history WHERE Key_name = 'network_user_id' and Column_name = ".
                    "'network_user_id' and Non_unique = 1;",
                    'match' => "/network_user_id/",
                    'column' => 'Key_name',
                ),
                array(
                    // Add tu_count_history index on post_id
                    'query' => "SHOW INDEX FROM tu_count_history WHERE Key_name = 'post_id' and Column_name = ".
                    "'post_id' and Non_unique = 1;",
                    'match' => "/post_id/",
                    'column' => 'Key_name',
                ),
                array(
                    // Add tu_count_history index on date
                    'query' => "SHOW INDEX FROM tu_count_history WHERE Key_name = 'date' and Column_name = ".
                    "'date' and Non_unique = 1;",
                    'match' => "/date/",
                    'column' => 'Key_name',
                ),
                array(
                    // Add tu_instances_twitter.last_reply_id
                    'query' => "DESCRIBE tu_instances_twitter last_reply_id;",
                    'match' => "/varchar\(80\)/",
                    'column' => 'Type',
                ),
                array(
                    // Drop tu_instances_twitter.last_page_fetched_tweets
                    'query' => "DESCRIBE tu_instances_twitter last_page_fetched_tweets;",
                    'no_match' => true
                ),
                array(
                    // Drop tu_instances_twitter.last_page_fetched_replies
                    'query' => "DESCRIBE tu_instances_twitter last_page_fetched_replies;",
                    'no_match' => true
                ),
                array(
                    // Add tu_owners.api_key_private
                    'query' => "DESCRIBE tu_owners api_key_private;",
                    'match' => "/varchar\(32\)/",
                    'column' => 'Type',
                ),
                array(
                    // Add tu_owners.email_notification_frequency
                    'query' => "DESCRIBE tu_owners email_notification_frequency;",
                    'match' => "/varchar\(10\)/",
                    'column' => 'Type',
                ),
                array(
                    // Create tu_photos table
                    'query' => 'DESCRIBE tu_photos id;',
                    'match' => "/int\(11\)/",
                    'column' => 'Type',
                ),
                array(
                    // Add tu_posts.permalink field
                    'query' => "DESCRIBE tu_posts permalink;",
                    'match' => "/text/",
                    'column' => 'Type',
                ),
                array(
                    // Rename insights.prefix to headline field
                    'query' => "DESCRIBE tu_insights headline;",
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
                array(
                    // Rename insights.prefix to headline field
                    'query' => "DESCRIBE tu_insights prefix;",
                    'no_match' => true,
                ),
                array(
                    // Add insights.header_image
                    'query' => "DESCRIBE tu_insights header_image;",
                    'match' => "/varchar\(255\)/",
                    'column' => 'Type',
                ),
            )
        )
    )
);
