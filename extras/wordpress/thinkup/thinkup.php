<?php
/*
 Plugin Name: ThinkUp WP Plugin
 Plugin URI: http://thinkup.com
 Description: Displays ThinkUp data on your WordPress blog.
 Version: 0.8
 Author: Gina Trapani and the ThinkUp community
 Author URI: http://thinkup.com
 */

/**
 *
 * ThinkUp/extras/wordpress/thinkup/thinkup.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Sam Rose, Mark Jaquith
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
 * The main driver class to the ThiknUp WordPress plugin.
 *
 * @author Sam Rose
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once 'classes/ThinkUpShortcodeHandler.class.php';
require_once 'classes/ThinkUpAdminPages.class.php';
require_once 'classes/ThinkUpPost.class.php';
require_once 'classes/ThinkUpUser.class.php';

class ThinkUpWordPressPlugin {

    /**
     * Where the options array is stored after a call to getOptionsArray().
     *
     * @var Array Cache for the options array.
     */
    private static $options;

    /**
     * Stores the database connection for the WordPress plugin. May or may not be the same as the global $wpdb
     *  depending on the user's settings.
     *
     * @var wpdb
     */
    private static $db_connection;

    public function __construct() {
        add_action( 'admin_menu',array('ThinkUpAdminPages', 'addOptionsPage'));
        // initiate the shortcode handler, constructor adds the actions
        new ThinkUpShortcodeHandler();
    }

    /**
     * Checks if the thinkup_server value has been set and if it has, creates a new database connection to that server
     * based on the values supplied on the options page of the plugin.
     *
     * This is useful for maintaining the ability to have your WordPress and ThinkUp install on different servers.
     *
     * @global wpdb $wpdb
     * @return wpdb
     */
    public static function getDatabaseConnection() {
        if (is_null(self::$db_connection)) {
            $options_array = self::getOptionsArray();

            if ($options_array['thinkup_server']['value'] != '') {
                self::$db_connection = new wpdb(
                $options_array['thinkup_dbusername']['value'],
                $options_array['thinkup_dbpw']['value'],
                $options_array['thinkup_db']['value'],
                $options_array['thinkup_server']['value']);
            } else {
                global $wpdb;
                self::$db_connection = $wpdb;
            }
        }

        return self::$db_connection;
    }

    /**
     * Generates and returns the ThinkUp options array. Caches the result after the first call to the function to speed
     *  up future calls.
     *
     * If the $force_update (first argument) is set to 'force-update', the function will update the cached options
     * array and return it.
     *
     * @return Array options array
     */
    public static function getOptionsArray($force_update = null) {
        if (!is_array(self::$options) || $force_update == 'force-update') {
            self::$options = array(
                'thinkup_twitter_username' =>
            array(
                    'key' => 'thinkup_twitter_username',
                    'label' => __('Default Twitter username:', 'thinkup-wp-plugin'),
                    'description' => __('(Required) Override this by using the "username" parameter in the shortcodes.',
                    'thinkup-wp-plugin'), 
                    'type' => 'text',
                    'value' => get_option('thinkup_twitter_username')
            ),
                'thinkup_table_prefix' =>
            array(
                    'key' => 'thinkup_table_prefix',
                    'label' => __('ThinkUp table prefix:', 'thinkup-wp-plugin'),
                    'description' => __('(Optional) The prefix on your ThinkUp database tables, e.g. <i>tu_</i>',
                    'thinkup-wp-plugin'),
                    'type' => 'text',
                    'value' => get_option('thinkup_table_prefix')
            ),
                'thinkup_server' =>
            array(
                    'key' => 'thinkup_server',
                    'label' => __('ThinkUp database server:', 'thinkup-wp-plugin'),
                    'description' => __('Required only if the ThinkUp database tables are located in a different '.
                    'databasethan the WordPress tables.', 'thinkup-wp-plugin'),
                    'type' => 'text',
                    'value' => get_option('thinkup_server')
            ),
                'thinkup_db' =>
            array(
                    'key' => 'thinkup_db',
                    'label' => __('ThinkUp database name:', 'thinkup-wp-plugin'),
                    'description' => __('Required only if the ThinkUp database tables are located in a different '.
                    'database than the WordPress tables.', 'thinkup-wp-plugin'),
                    'type' => 'text',
                    'value' => get_option('thinkup_db')
            ),
                'thinkup_dbusername' =>
            array(
                    'key' => 'thinkup_dbusername',
                    'label' => __('ThinkUp database username:', 'thinkup-wp-plugin'),
                    'description' => __('Required only if the ThinkUp database tables are located in a different '.
                    'database than the WordPress tables.', 'thinkup-wp-plugin'),
                    'type' => 'text',
                    'value' => get_option('thinkup_dbusername')
            ),
                'thinkup_dbpw' =>
            array(
                    'key' => 'thinkup_dbpw',
                    'label' => __('ThinkUp database password:', 'thinkup-wp-plugin'),
                    'description' => __('Required only if the ThinkUp database tables are located in a different '.
                    'database than the WordPress tables.', 'thinkup-wp-plugin'),
                    'type' => 'password',
                    'value' => ThinkUpWordPressPlugin::unscramblePassword( (get_option('thinkup_dbpw')))) );
        }
        return self::$options;
    }

    /**
     * In an effort to not store passwords as plain text in the database this function uses obfuscation techniques to
     *  mess up the string. Not perfect but better than clear text.
     *
     * @return str Scrambled password.
     */
    public static function scramblePassword($password) {
        $salt = substr(str_pad(dechex(mt_rand()), 8, '0', STR_PAD_LEFT), -8);
        $modified = $password.$salt;
        $secured = $salt.base64_encode(bin2hex(strrev(str_rot13($modified))));
        return $secured;
    }

    /**
     * Unscrambles the obfuscated password from the database.
     *
     * @return str Plain text password.
     */
    public static function unscramblePassword($stored_password) {
        $salt = substr($stored_password, 0, 8);
        $modified = substr($stored_password, 8, strlen($stored_password) - 8);
        $modified = str_rot13(strrev(pack("H*", base64_decode($modified))));
        $password = substr($modified, 0, strlen($modified) - 8);
        return $password;
    }

    /**
     * Returns the plugin's unique identifier for use in i18n and creation of option pages.
     *
     * @return str Plugin's unique string identifier.
     */
    public static function uniqueIdentifier() {
        return 'thinkup-wp-plugin';
    }

    /**
     * Returns the version number of the plugin as a string. Remember to keep this updated in subsequent versions.
     *
     * Does not have a use as of yet.
     *
     * @return str Version number
     */
    public static function version() {
        return '0.7';
    }

    /**
     * Returns a string representing a capability required to access the plugin settings for this plugin.
     * Currently set to "edit_plugins" which more or less means admins only.
     *
     * More info on user levels and roles:
     * http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @return str User role required to access plugin options.
     */
    public static function settingsAccessLevel() {
        return 'edit_plugins';
    }

    /**
     * Returns a string representing a capability required to access the plugin menu.
     *  Currently set to "publish_posts" which means authors and up can view it. This is for viewing the help section.
     *
     * More info on user levels and roles:
     * http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @return str User role required to access plugin menu.
     */
    public static function accessLevel() {
        return 'publish_posts';
    }

    /**
     * Returns a string to be used as the name for our nonce fields.
     *
     * For more information on nonce fields:
     * http://codex.wordpress.org/Function_Reference/wp_nonce_field
     *
     * @return str md5 hash of the unique identifier to use as a nonce.
     */
    public static function nonceName() {
        return md5(ThinkUpWordPressPlugin::uniqueIdentifier());
    }

    /**
     * Returns the absolute file path to this plugin's main/root directory.
     *
     * @return str The absolute file path to this plugin's root directory.
     */
    public static function pluginDirectory() {
        return dirname(__FILE__);
    }
}

//initiate the plugin
new ThinkUpWordPressPlugin();

// proof of concept: function prints out 94083951404072966, 1 less than expected
// echo sprintf('%0.0f', 9408395140407297);
?>
