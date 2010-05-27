<?php
class Utils {

    function GetDeltaTime($dtTime1, $dtTime2) {
        $nUXDate1 = strtotime($dtTime1->format("Y-m-d H:i:s"));
        $nUXDate2 = strtotime($dtTime2->format("Y-m-d H:i:s"));

        $nUXDelta = $nUXDate1 - $nUXDate2;
        $strDeltaTime = "".$nUXDelta / 60 / 60; // sec -> hour

        $nPos = strpos($strDeltaTime, ".");
        if ($nPos !== false)
        $strDeltaTime = substr($strDeltaTime, 0, $nPos + 3);

        return $strDeltaTime;
    }

    function getPercentage($num, $denom) {
        if ((isset($num)) && (isset($denom))) {
            if ($num > 0) {
                return ($denom * 100) / ($num);
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public static function curl_get_file_contents($URL) {
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

    static public function getPlugins($dir) {
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

    static public function getPluginViewDirectory($shortname) {
        $config = Config::getInstance();
        $view_path = $config->getValue('source_root_path');
        $view_path .= 'webapp/plugins/'.$shortname.'/view/';
        return $view_path;
    }

    static public function getURLWithParams($url, $params){
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
?>
