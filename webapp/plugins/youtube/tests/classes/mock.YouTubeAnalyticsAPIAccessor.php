<?php
/**
 *
 * ThinkUp/webapp/plugins/youtube/model/mock.YouTubeAnalyticsAPIAccessor.php
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
 * Mock YouTube Analytics API Accessor
 *
 * Reads test data files instead of the actual YouTube servers for the purposes of running tests.
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kaliar
 */
class YouTubeAnalyticsAPIAccessor {
    /**
     * @var str
     */
    var $api_domain = 'https://www.googleapis.com/youtube/analytics/v1/';
    /**
     * @var str
     */
    var $access_token_request_url = "https://accounts.google.com/o/oauth2/token";
    /**
     * @var str
     */
    var $data_location = 'webapp/plugins/youtube/tests/apidata/';

    /**
     * Make a YouTube API request.
     * @param str $path
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public function apiRequest($path, $access_token, $fields=null) {
        //Todays date, used to detect dynamically changing filenames
        $date = date('Y-m-d');
        $two_days_ago = date('Y-m-d', strtotime('-2 days'));

        //The name of a file whose name is too long for ThinkUp to use
        $file1 = 'reports-access_token=at-ids=channel==UC-start-date=2013-04-21-end-date='.$date.'-metrics=favorit';
        $file1 .= 'esAdded,favoritesRemoved,shares,subscribersGained,subscribersLost,estimatedMinutesWatched,averageVi';
        $file1 .= 'ewDuration,averageViewPercentage,views,likes,dislikes-filters=video==H';

        // The name of a another file whose name is too long for ThinkUp to use
        $file2 = 'reports-access_token=at-ids=channel==UC-start-date=2013-06-05-end-date='.$date.'-metrics=favorit';
        $file2 .= 'esAdded,favoritesRemoved,shares,subscribersGained,subscribersLost,estimatedMinutesWatched,averageVi';
        $file2 .= 'ewDuration,averageViewPercentage,views,likes,dislikes-filters=video==g';

        $file3 = 'reports-access_token=at-ids=channel==UC-start-date=2013-06-05-end-date='.$date.'-metrics=favorit';
        $file3 .= 'esAdded,favoritesRemoved,shares,subscribersGained,subscribersLost,estimatedMinutesWatched,averageVi';
        $file3 .= 'ewDuration,averageViewPercentage,views,likes,dislikes-filters=video==a';

        $file4 = 'reports-access_token=at-ids=channel==UC-start-date='.$two_days_ago.'-end-date='.$two_days_ago.'-metri';
        $file4 .= 'cs=favoritesAdded,favoritesRemoved,shares,subscribersGained,subscribersLost,estimatedMinutesWatche';
        $file4 .= 'd,averageViewDuration,averageViewPercentage,views,likes,dislikes-filters=video==a';

        $url = $this->api_domain.$path.'?access_token='.$access_token;
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

        // Two file names are too long for ThinkUp so we hash those filenames and read a file with that name instead
        if($url == $file1) {
            $url = '25f3d173cf75c34c8005370a066d39ed';
        }
        if($url == $file2) {
            $url = '18c645d6b4cb20d1753fc14fdff50dcd';
        }
        if($url == $file3) {
            $url = '73ebb609998a4d8d1bf26d981239863f';
        }
        if($url == $file4) {
            $url = 'e6eaf8352122390429b3026a48692ad8';
        }

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
            echo "READING LOCAL TEST DATA FILE: ".$file_path. '';
        }
        $contents=  file_get_contents($file_path);
        if ($decode_json) {
            $decoded = json_decode($contents);
            if ($decoded == null && $debug) {
                echo "JSON was not decoded! Check if it is valid JSON at http://jsonlint.com/";
            }
            return $decoded;
        } else {
            return $contents;
        }
    }

    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the
     * prefix at the start and the access token at the end as well as everything in
     * between. It is literally the raw URL that needs to be passed in.
     *
     * @param str $path
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public function rawPostApiRequest($path, $fields, $decode_json=true) {
        $fields_string = '';
        foreach ($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }

        // Replace the &'s in the string with a -
        $fields_string = str_replace('&', '-', $fields_string);
        $fields_string = rtrim($fields_string,'-');

        $file_path = $path . $fields_string;

        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        return self::rawApiRequest($file_path, $decode_json);
    }

    /**
     * Internal use only
     * @param str $path
     * @param book $decode_json If true, return decoded JSON
     * @return array Decoded JSON response
     */
    private function rawApiRequest($path, $decode_json=true) {
        $url = $path;

        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;

        $url = preg_replace('/([\?\&])access_token\=[^\?\&]+([\?\&])*/', "$1", $url);
        $url = preg_replace('/[\?\&]$/', '', $url);
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace($this->access_token_request_url, 'tok-', $url);
        $url = str_replace(':', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        // If we are in debug mode report that were reading a local test file
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$FAUX_DATA_PATH.$url. '
';
        }
        return self::decodeFileContents($FAUX_DATA_PATH.$url, $decode_json);
    }

    /**
     * For testing purposes only
     * @param str $folder
     */
    public function setDataLocation($folder) {
        $this->data_location = $this->data_location.$folder;
    }
}
