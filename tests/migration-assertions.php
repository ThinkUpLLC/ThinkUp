<?php
/**
 *
 * ThinkUp/tests/migration-assertions.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
        'zip_url' => 'https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.4.1.zip',
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
