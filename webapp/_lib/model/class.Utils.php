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
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Utils {

    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @author http://recursive-design.com/blog/2008/03/11/format-json-with-php/
     * @param string $json The original JSON string to process.
     * @return string Indented version of the original JSON string.
     */
    public static function indentJSON($json) {

        $result = '';
        $pos = 0;
        $str_len = strlen($json);
        $indent_str = '    ';
        $new_line = "\n";
        $prev_char = '';
        $prev_prev_char = '';
        $out_of_quotes = true;

        for ($i = 0; $i <= $str_len; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"') {
                if ( $prev_char != "\\") {
                    $out_of_quotes = !$out_of_quotes;
                } elseif ($prev_prev_char == "\\") {
                    $out_of_quotes = !$out_of_quotes;
                }
                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else if (($char == '}' || $char == ']') && $out_of_quotes) {
                $result .= $new_line;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indent_str;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $out_of_quotes) {
                $result .= $new_line;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indent_str;
                }
            }

            $prev_prev_char = $prev_char;
            $prev_char = $char;
        }

        return $result;
    }

    /**
     * Becuse PHP doesn't have a data type large enough to hold some of the
     * numbers that Twitter deals with, this function strips the double
     * quotes off every string that contains only numbers inside the double
     * quotes.
     *
     * @param string $encoded_json JSON formatted string.
     * @return string Encoded JSON with numeric strings converted to numbers.
     */
    public static function convertNumericStrings($encoded_json) {
        return preg_replace('/\"((?:-)?[0-9]+(\.[0-9]+)?)\"/', '$1', $encoded_json);
    }

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
     * Get the contents of a URL via GET
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
     * Get the contents of a URL via POST
     * @param str $URL
     * @param array $fields
     * @return str contents
     */
    public static function getURLContentsViaPost($URL, array $fields) {
        $fields_string = '';
        //url-ify the data for the POST
        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string,'&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,$URL);
        curl_setopt($ch,CURLOPT_POST,count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute post
        $contents = curl_exec($ch);

        //close connection
        curl_close($ch);
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
     * This method uses a raw regex instead of filter_var because as of PHP 5.3.3,
     * filter_var($email, FILTER_VALIDATE_EMAIL) validates local email addresses.
     * From 5.2 to 5.3.3, it does not.
     * Therefore, this method uses the PHP 5.2 regex instead of filter_var in order to return consistent results
     * regardless of PHP version.
     * http://svn.php.net/viewvc/php/php-src/trunk/ext/filter/logical_filters.c?r1=297250&r2=297350
     *
     * @param str $email Email address to validate
     * @return bool Whether or not it's a valid address
     */
    public static function validateEmail($email = '') {
        //return filter_var($email, FILTER_VALIDATE_EMAIL));
        $reg_exp = "/^((\\\"[^\\\"\\f\\n\\r\\t\\b]+\\\")|([A-Za-z0-9_][A-Za-z0-9_\\!\\#\\$\\%\\&\\'\\*\\+\\-\\~\\".
        "/\\=\\?\\^\\`\\|\\{\\}]*(\\.[A-Za-z0-9_\\!\\#\\$\\%\\&\\'\\*\\+\\-\\~\\/\\=\\?\\^\\`\\|\\{\\}]*)*))@((\\".
        "[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.".
        "((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\\])|".
        "(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.".
        "((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|".
        "((([A-Za-z0-9])(([A-Za-z0-9\\-])*([A-Za-z0-9]))?(\\.(?=[A-Za-z0-9\\-]))?)+[A-Za-z]+))$/D";
        //return (preg_match($reg_exp, $email) === false)?false:true;
        return (preg_match($reg_exp, $email)>0)?true:false;
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

        $dirs_under_root = array('account', 'post', 'session', 'user', 'install', 'tests');
        $current_script_path = explode('/', $_SERVER['PHP_SELF']);
        array_pop($current_script_path);
        if ( in_array( end($current_script_path), $dirs_under_root ) ) {
            array_pop($current_script_path);
        }
        $current_script_path = implode('/', $current_script_path) . '/';
        define('THINKUP_BASE_URL', $current_script_path);
    }

    /**
     * Generate var dump to string.
     * @return str
     */
    public static function varDumpToString($mixed = null) {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * FOR TESTING AND DEBUGGING USE ONLY. DO NOT USE IN PRODUCTION APPLICATION CODE.
     * Given a PDO SQL statement with parameters to bind, replaces the :param tokens with the parameters and return
     * a string for display/debugging purposes.
     * @param str $sql
     * @param arr $vars
     * @return str
     */
    public static function mergeSQLVars($sql, $vars) {
        foreach ($vars as $k => $v) {
            $sql = str_replace($k, (is_int($v))?$v:"'".$v."'", $sql);
        }
        $config = Config::getInstance();
        $prefix = $config->getValue('table_prefix');
        $gmt_offset = $config->getGMTOffset();
        $sql = str_replace('#gmt_offset#', $gmt_offset, $sql);
        $sql = str_replace('#prefix#', $prefix, $sql);
        return $sql;
    }

    /**
     * If date.timezone is not set in php.ini, default to America/Los_Angeles to avoid date() warning about
     * using system settings.
     * This method exists to avoid the warning which Smarty triggers in views that don't have access to a
     * THINKUP_CFG timezone setting yet, like during installation, or when a config file doesn't exist.
     */
    public static function setDefaultTimezonePHPini() {
        if (ini_get('date.timezone') == false) {
            // supress the date_default_timezone_get() warn as php 5.3.* doesn't like when date.timezone is not set in
            // php.ini, but many systems comment it out by default, or have no php.ini by default
            $error_reporting = error_reporting(); // save old reporting setting
            error_reporting( E_ERROR | E_USER_ERROR ); // turn off warning messages
            $tz = date_default_timezone_get(); // get tz if we can
            error_reporting( $error_reporting ); // reset error reporting
            if(! $tz) { // if no $tz defined, use UTC
                $tz = 'UTC';
            }
            ini_set('date.timezone',$tz);
        }
    }
}
