Utils
=====

ThinkUp/webapp/_lib/model/class.Utils.php

Copyright (c) 2009-2011 Gina Trapani

Utils

Generic, reusable, common utility methods



Methods
-------

indentJSON
~~~~~~~~~~
* **@author** http://recursive-design.com/blog/2008/03/11/format-json-with-php/
* **@param** string $json The original JSON string to process.
* **@return** string Indented version of the original JSON string.


Indents a flat JSON string to make it more human-readable.

.. code-block:: php5

    <?php
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


convertNumericStrings
~~~~~~~~~~~~~~~~~~~~~
* **@param** string $encoded_json JSON formatted string.
* **@return** string Encoded JSON with numeric strings converted to numbers.


Becuse PHP doesn't have a data type large enough to hold some of the
numbers that Twitter deals with, this function strips the double
quotes off every string that contains only numbers inside the double
quotes.

.. code-block:: php5

    <?php
        public static function convertNumericStrings($encoded_json) {
            return preg_replace('/\"((?:-)?[0-9]+(\.[0-9]+)?)\"/', '$1', $encoded_json);
        }


getPercentage
~~~~~~~~~~~~~
* **@param** int $numerator
* **@param** int $denominator
* **@return** int Percentage


Get percentage

.. code-block:: php5

    <?php
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


getURLContents
~~~~~~~~~~~~~~
* **@param** str $URL
* **@return** str contents


Get the contents of a URL

.. code-block:: php5

    <?php
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


getPlugins
~~~~~~~~~~
* **@param** str $dir
* **@return** array Plugins


Get plugins that exist in the ThinkUp plugins directory

.. code-block:: php5

    <?php
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


getPluginViewDirectory
~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $shortname Plugin short name
* **@return** str view path


Get plugin view directory

.. code-block:: php5

    <?php
        public static function getPluginViewDirectory($shortname) {
            self::defineConstants();
            $view_path = THINKUP_WEBAPP_PATH.'plugins/'.$shortname.'/view/';
            return $view_path;
        }


getURLWithParams
~~~~~~~~~~~~~~~~
* **@param** str $url
* **@param** array $params
* **@return** str URL


Get URL with params
Build URL with params given an array

.. code-block:: php5

    <?php
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


validateEmail
~~~~~~~~~~~~~
* **@param** str $email Email address to validate
* **@return** bool Whether or not it's a valid address


Validate email address
This method uses a raw regex instead of filter_var because as of PHP 5.3.3,
filter_var($email, FILTER_VALIDATE_EMAIL) validates local email addresses.
From 5.2 to 5.3.3, it does not.
Therefore, this method uses the PHP 5.2 regex instead of filter_var in order to return consistent results
regardless of PHP version.
http://svn.php.net/viewvc/php/php-src/trunk/ext/filter/logical_filters.c?r1=297250&r2=297350

.. code-block:: php5

    <?php
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


validateURL
~~~~~~~~~~~
* **@param** str $url
* **@return** bool Whether or not it's a "valid" URL


Validate URL

.. code-block:: php5

    <?php
        public static function validateURL($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }


defineConstants
~~~~~~~~~~~~~~~

Define application constants

.. code-block:: php5

    <?php
        public static function defineConstants() {
            self::defineConstantRootPath();
            self::defineConstantWebappPath();
            self::defineConstantBaseUrl();
        }


defineConstantRootPath
~~~~~~~~~~~~~~~~~~~~~~

Define the root path to ThinkUp on the filesystem

.. code-block:: php5

    <?php
        public static function defineConstantRootPath() {
            if ( defined('THINKUP_ROOT_PATH') ) return;
    
            define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
        }


defineConstantWebappPath
~~~~~~~~~~~~~~~~~~~~~~~~

Define the ThinkUp's web root on the filesystem

.. code-block:: php5

    <?php
        public static function defineConstantWebappPath() {
            if ( defined('THINKUP_WEBAPP_PATH') ) return;
    
            if (file_exists(THINKUP_ROOT_PATH . 'webapp')) {
                define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
            } else {
                define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'thinkup/');
            }
        }


defineConstantBaseUrl
~~~~~~~~~~~~~~~~~~~~~

Define base URL, the same as $THINKUP_CFG['site_root_path']

.. code-block:: php5

    <?php
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


varDumpToString
~~~~~~~~~~~~~~~
* **@return** str


Generate var dump to string.

.. code-block:: php5

    <?php
        public static function varDumpToString($mixed = null) {
            ob_start();
            var_dump($mixed);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }


mergeSQLVars
~~~~~~~~~~~~
* **@param** str $sql
* **@param** arr $vars
* **@return** str


Given a PDO SQL statement with parameters to bind, replaces the :param tokens with the parameters and return
a string for display/debugging purposes.

.. code-block:: php5

    <?php
        public static function mergeSQLVars($sql, $vars) {
            foreach ($vars as $k => $v) {
                $sql = str_replace($k, (is_int($v))?$v:"'".$v."'", $sql);
            }
            return $sql;
        }




