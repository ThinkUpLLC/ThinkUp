<?php
/**
 *
 * ThinkUp/webapp/plugins/youtube/model/mock.YouTubeAPIV2Accessor.php
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
 *
 * Mock YouTube API V2 Accessor
 *
 * Reads test data files instead of the actual YouTube servers for the purposes of running tests.
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */
class YouTubeAPIV2Accessor {
    /**
     * @var str
     */
    var $api_domain = 'https://gdata.youtube.com/feeds/api/';
    /**
     * @var str
     */
    var $data_location = 'webapp/plugins/youtube/tests/apidata/';
    /**
     * Make a YouTube API V2 request.
     * @param str $path
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public function apiRequest($path, $fields=null) {
        $url = $this->api_domain.$path;
        if ($fields != null ) {
            foreach ($fields as $key=>$value) {
                $url = $url.'&'.$key.'='.$value;
            }
        }
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace(':', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        return self::decodeFileContents($FAUX_DATA_PATH.$url);
    }

    public function basicApiRequest($url) {
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace(':', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        return self::decodeFileContents($FAUX_DATA_PATH.$url);
    }

    private static function decodeFileContents($file_path, $decode_json=true) {
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        $create_files = (getenv('CREATE_FILES')!==false) ? true : false;
        if ($create_files) {
            if($debug){
                echo 'CREATING '.$file_path."\n\n";
            }
            exec('touch '.$file_path);
        }
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$file_path. "\n\n";
        }
        $contents=  file_get_contents($file_path);
        if ($decode_json) {
            $decoded = json_decode($contents);
            if ($decoded == null && $debug) {
                echo "JSON was not decoded!";
            }
            return $decoded;
        } else {
            return $contents;
        }
    }

    /**
     * For testing purposes only
     * @param str $folder
     */
    public function setDataLocation($folder) {
        $this->data_location = $this->data_location.$folder;
    }
}
