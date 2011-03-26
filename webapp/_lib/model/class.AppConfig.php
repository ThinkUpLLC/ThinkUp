<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.AppConfig.php
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
 * Application config options defaults, and validation settings.
 * class.Config.php will use to determine what configs to pull from the database, and
 * class.AppConfigController will use config data for input validation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Mark Wilkie
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