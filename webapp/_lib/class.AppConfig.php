<?php
/**
 *
 * ThinkUp/webapp/_lib/class.AppConfig.php
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
 * Application config options defaults, and validation settings.
 * class.Config.php will use to determine what configs to pull from the database, and
 * class.AppConfigController will use config data for input validation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class AppConfig {

    /**
     * Data validation array
     * @var array Collection oif validation string for data input
     */
    static $config_data = array(
        'is_registration_open' => array(
            'type' => 'checkbox',
            'title' => 'Open Registration',
            'required' => false,
            'default' => 'false',
            'match' => '/^(true|false)$/',
            'match_message' => 'Must be true or false'
            ),
        'recaptcha_enable' => array(
            'type' => 'checkbox',
            'title' => 'Enable ReCAPTCHA',
            'required' => false,
            'default' => 'false',
            'match' => '/^true$/',
            'match_message' => 'Must be true',
            'dependencies' => array('recaptcha_public_key','recaptcha_private_key')
            ),
        'recaptcha_public_key' => array(
            'type' => 'text',
            'title' => 'ReCAPTCHA Public Key',
            'required' => false,
            'match' => '/\w/',
            'match_message' => '',
            'default' => '',
            ),
        'recaptcha_private_key' => array(
            'type' => 'text',
            'title' => 'ReCAPTCHA Private Key',
            'required' => false,
            'match' => '/\w/',
            'match_message' => '',
            'default' => '',
            ),

            /**
             * Currently there's a bug with checkboxes which have a default value of true. When you uncheck the box,
             * and save the form, no value gets submitted for the checkbox, so the false value doesn't get saved.
             * As such, right now, checkbox default values must be false.
             * Therefore, for now, making this option 'is_api_disabled' instead of 'is_api_enabled.'
             * @TODO: Once that bug is fixed, change this to Enable JSON API with default value true.
             */
        'is_api_disabled' => array(
            'type' => 'checkbox',
            'title' => 'Disable JSON API',
            'required' => false,
            'default' => 'false',
            'match' => '/^true$/',
            'match_message' => ' be true'
            ),
        'is_embed_disabled' => array(
            'type' => 'checkbox',
            'title' => 'Disable ability to embed threads on external web pages',
            'required' => false,
            'default' => 'false',
            'match' => '/^true$/',
            'match_message' => ' be true'
            ),
        'is_log_verbose' => array(
            'type' => 'checkbox',
            'title' => 'See the verbose, unformatted developer log on the Capture Data screen',
            'required' => false,
            'default' => 'false',
            'match' => '/^(true|false)$/',
            'match_message' => 'Must be true or false'
            ),
        'is_opted_out_usage_stats' => array(
            'type' => 'checkbox',
            'title' => 'Usage reporting helps us improve ThinkUp',
            'required' => false,
            'default' => 'false',
            'match' => '/^(true|false)$/',
            'match_message' => 'Must be true or false'
            ),
        'default_instance' => array(
            'type' => 'text',
            'title' => 'The service user to display by default',
            'required' => false,
            'default' => '0',
            'match' => '/^[0-9]{1,}$/',
            'match_message' => ' be numeric'
            ),
        'is_subscribed_to_beta' => array(
            'type' => 'checkbox',
            'title' => 'Get beta upgrades',
            'required' => false,
            'default' => 'false',
            'match' => '/^true$/',
            'match_message' => 'Must be true'
            )
            );

            /**
             * Getter for db config data array
             * @return array Application settings configuration and validation data array/hash
             */
            public static function getConfigData() {
                return self::$config_data;
            }

            /**
             * Getter for db config data value
             * @param str Key for apllication value
             * @return array Application settings configuration and validation data array/hash
             */
            public static function getConfigValue($key) {
                $value = isset(self::$config_data[$key] ) ? self::$config_data[$key] : false;
                return $value;
            }
}