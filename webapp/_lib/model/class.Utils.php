<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Utils.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * Utils
 *
 * Generic, reusable, common utility methods
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Utils {

    /**
     * Get percentage
     * @param int $numerator
     * @param int $denominator
     * @return int Percentage
     */
    public static function getPercentage($numerator, $denominator) {
        if ((isset($numerator)) && (isset($denominator))) {
            if ($numerator > 0 && $denominator > 0) {
                return ($numerator * 100) / ($denominator);
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Get the contents of a URL
     * @param str $URL
     * @return str contents
     */
    public static function getURLContents($URL) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);

        //echo "URL: ".$URL."\n";
        //echo $contents;
        //echo "STATUS: ".$status."\n";
        if (isset($contents)) {
            return $contents;
        } else {
            return null;
        }
    }

    /**
     * Get plugins that exist in the ThinkUp plugins directory
     * @param str $dir
     * @return array Plugins
     */
    public static function getPlugins($dir) {
        $dh = @opendir($dir);
        $plugins = array();
        if (!$dh) {
            throw new Exception("Cannot open directory $dir");
        } else {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $requiredFile = "$dir/$file";
                    if (is_dir($requiredFile)) {
                        array_push($plugins, $file);
                    }
                }
            }
            closedir($dh);
        }

        unset($dh, $dir, $file, $requiredFile);
        return $plugins;
    }

    /**
     * Get plugin view directory
     * @param str $shortname Plugin short name
     * @return str view path
     */
    public static function getPluginViewDirectory($shortname) {
        self::defineConstants();
        $view_path = THINKUP_WEBAPP_PATH.'plugins/'.$shortname.'/view/';
        return $view_path;
    }

    /**
     * Get URL with params
     * Build URL with params given an array
     * @param str $url
     * @param array $params
     * @return str URL
     */
    public static function getURLWithParams($url, $params){
        $param_str = '';
        foreach ($params as $key=>$value) {
            $param_str .= $key .'=' . $value.'&';
        }
        if ($param_str != '') {
            $url .= '?'.substr($param_str, 0, (strlen($param_str)-1));
        }
        return $url;
    }

    /**
     * Validate email address
     * Note: Local email addresses (without a dot in the domain name) will return false.
     * As of PHP 5.3.3, the FILTER_VALIDATE_EMAIL validates local email addresses. From 5.2 to 5.3.3, it does not.
     * Therefore, this method couples filter_var with the preg_match to return consistent results regardless of PHP
     * version.
     *
     * @param string $email Email address to validate
     * @return bool Whether or not it's a valid address
     */
    public static function validateEmail($email = '') {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
    }

    /**
     * Validate URL
     *
     * @param str $url
     * @return bool Whether or not it's a "valid" URL
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Define application constants
     */
    public static function defineConstants() {
        self::defineConstantRootPath();
        self::defineConstantWebappPath();
        self::defineConstantBaseUrl();
    }

    /**
     * Define the root path to ThinkUp on the filesystem
     */
    public static function defineConstantRootPath() {
        if ( defined('THINKUP_ROOT_PATH') ) return;

        define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
    }

    /**
     * Define the ThinkUp's web root on the filesystem
     */
    public static function defineConstantWebappPath() {
        if ( defined('THINKUP_WEBAPP_PATH') ) return;

        if (file_exists(THINKUP_ROOT_PATH . 'webapp')) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
        } else {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'thinkup/');
        }
    }

    /**
     * Define base URL, the same as $THINKUP_CFG['site_root_path']
     */
    public static function defineConstantBaseUrl() {
        if ( defined('THINKUP_BASE_URL') ) return;

        $dirs_under_root = array('account', 'post', 'session', 'user', 'install');
        $current_script_path = explode('/', $_SERVER['PHP_SELF']);
        array_pop($current_script_path);
        if ( in_array( end($current_script_path), $dirs_under_root ) ) {
            array_pop($current_script_path);
        }
        $current_script_path = implode('/', $current_script_path) . '/';
        define('THINKUP_BASE_URL', $current_script_path);
    }

    /**
     * utility function to generate var dump to string
     */
    public static function var_dump_ret($mixed = null) {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
