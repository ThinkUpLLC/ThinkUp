<?php
/**
 * Utils
 *
 * Generic, reusable, common utility methods
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
            if ($numerator > 0) {
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
     * Get plugins that exist in the ThinkTank plugins directory
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
                    $requiredFile = $dir.DIRECTORY_SEPARATOR.$file;
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
        $config = Config::getInstance();
        $view_path = $config->getValue('source_root_path');
        $view_path .= 'webapp/plugins/'.$shortname.'/view/';
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
}
