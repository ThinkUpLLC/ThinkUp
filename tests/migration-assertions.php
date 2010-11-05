<?php

$LATEST_VERSION = 0.5;

$MIGRATIONS = array(

    /* beta 0.2 */
    '0.2' => array(
        'zip_url' => 'http://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.2.zip',
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'desc tu_owners email',
                    'match' => "/varchar\(200\)/",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_posts post_id',
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
                    'query' => 'desc tu_posts in_retweet_of_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_posts post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_posts in_reply_to_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_links post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_post_errors post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_users last_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
                array(
                    'query' => 'desc tu_instances last_post_id',
                    'match' => "/bigint\(20\) UNSIGNED/i",
                    'column' => 'Type', 
                ),
            )
        )
    ),

    /* beta 0.4 */
    '0.4' => array(
        'zip_url' => 'file://./build/thinkup.zip',
        'migrations' => 1,
        'migration_assertions' => array(
            'sql' => array(
                array(
                    'query' => 'desc tu_options option_id',
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
);
