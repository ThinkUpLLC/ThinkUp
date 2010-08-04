<?php
/**
 * GeoEncoder Crawler
 *
 * The GeoEncoder crawler retrieves geolocation information for a post.
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GeoEncoderCrawler {

    static $is_api_available = true;

    const SUCCESS = 1;
    const ZERO_RESULTS = 2;
    const OVER_QUERY_LIMIT = 3;
    const REQUEST_DENIED = 4;
    const INVALID_REQUEST = 5;

    /**
     * Perform Geoencoding using the data available in fields place or location
     * @var PostDAO $pdao
     * @var array $post
     * @return NULL
     */
    public function performGeoencoding($pdao, $post) {
        if (self::$is_api_available) {
            $logger = Logger::getInstance();
            $ldao = DAOFactory::getDAO('LocationDAO');
            $post_id = $post['post_id'];
            if ($post['place']!='') {
                $location = $post['place'];
            } else {
                $location = $post['location'];
            }
            $reply_retweet_distance = 0;
            $is_reverse_geoencoded = 0;
            $find_geodata = explode(':', $location, 2);
            if (isset($find_geodata[1])) {
                $check_geodata = explode(',', trim($find_geodata[1]), 2);
                if (isset($check_geodata[0]) && isset($check_geodata[1])) {
                    $check_geodata[0] = trim($check_geodata[0]);
                    $check_geodata[1] = trim($check_geodata[1]);
                    if (is_string($find_geodata[0]) && is_numeric($check_geodata[0]) && is_numeric($check_geodata[1])){
                        $post['geo'] = $check_geodata[0].' '.$check_geodata[1];
                        $is_reverse_geoencoded = 1;
                        self::performReverseGeoencoding($pdao, $post);
                    }
                }
            }
            if (!$is_reverse_geoencoded) {
                $data = $ldao->getLocation($location);
                if (isset($data)) {
                    if ($post['in_reply_to_post_id']!=NULL || $post['in_retweet_of_post_id']!=NULL) {
                        $reply_retweet_distance = $this->getDistance($pdao, $post, $data['latlng']);
                        if (!$reply_retweet_distance) {
                            return;
                        }
                    }
                    $pdao->setGeoencodedPost($post_id, self::SUCCESS, $data['full_name'], $data['latlng'],
                    $reply_retweet_distance);
                    $logger->logStatus('Lat/long coordinates found in DB', get_class($this));
                    return;
                }
                $string = self::getDataForGeoencoding($location);
                $obj=json_decode($string);
                if ($obj->status == "OK") {
                    $geodata = $obj->results[0]->geometry->location->lat.','.$obj->results[0]->geometry->location->lng;
                    $short_location = $location;
                    $location = $obj->results[0]->formatted_address;
                    if ($post['in_reply_to_post_id']!=NULL || $post['in_retweet_of_post_id']!=NULL) {
                        $reply_retweet_distance = $this->getDistance($pdao, $post, $geodata);
                        if (!$reply_retweet_distance) {
                            return;
                        }
                    }
                    $pdao->setGeoencodedPost($post_id, self::SUCCESS, $location, $geodata, $reply_retweet_distance);
                    $logger->logStatus('Lat/long coordinates retrieved via API', get_class($this));
                    $vals = array (
                        'short_name'=>$short_location,
                        'full_name'=>$location,
                        'latlng'=>$geodata
                    );
                    $ldao->addLocation($vals);
                } else {
                    self::failedToGeoencode($pdao, $post_id, $obj->status);
                }
            }
        }
    }

    /**
     * Perform Reverse Geoencoding using the data available in field geo
     * @var PostDAO $pdao
     * @var array $post
     * @return NULL
     */
    public function performReverseGeoencoding($pdao, $post) {
        if (self::$is_api_available) {
            $logger = Logger::getInstance();
            $ldao = DAOFactory::getDAO('LocationDAO');
            $post_id = $post['post_id'];
            $geodata = $post['geo'];
            $reply_retweet_distance = 0;
            $data = $ldao->getLocation($geodata);
            if (isset($data)) {
                if ($post['in_reply_to_post_id']!=NULL || $post['in_retweet_of_post_id']!=NULL) {
                    $reply_retweet_distance = $this->getDistance($pdao, $post, $data['latlng']);
                    if (!$reply_retweet_distance) {
                        return;
                    }
                }
                $pdao->setGeoencodedPost($post_id, self::SUCCESS, $data['full_name'], $data['latlng'],
                $reply_retweet_distance);
                $logger->logStatus('Lat/long coordinates found in DB', get_class($this));
                return;
            }
            $string = self::getDataForReverseGeoencoding($geodata);
            $geodata = explode(' ', $geodata, 2);
            $geodata = $geodata[0].','.$geodata[1];
            $obj=json_decode($string);
            if ($obj->status == 'OK') {
                foreach ($obj->results as $p) {
                    switch($p->types[0]) {
                        case 'neighborhood':
                        case 'sublocality':
                        case 'locality':
                        case 'administrative_area_level_3':
                        case 'administrative_area_level_2':
                        case 'administrative_area_level_1':
                            $location = $p->formatted_address;
                            if ($post['in_reply_to_post_id']!=NULL || $post['in_retweet_of_post_id']!=NULL) {
                                $reply_retweet_distance = $this->getDistance($pdao, $post, $geodata);
                                if (!$reply_retweet_distance) {
                                    return;
                                }
                            }
                            $pdao->setGeoencodedPost($post_id, self::SUCCESS, $location, $geodata,
                            $reply_retweet_distance);
                            $logger->logStatus('Lat/long coordinates retrieved via API', get_class($this));
                            $vals = array (
                                'short_name'=>$post['geo'],
                                'full_name'=>$location,
                                'latlng'=>$geodata
                            );
                            $ldao->addLocation($vals);
                            return;
                    }
                }
            } else {
                self::failedToGeoencode($pdao, $post_id, $obj->status);
            }
        }
    }

    /**
     * Method to Update post if validation of geo-location data of post results in failure
     * @var PostDAO $pdao
     * @var int $post_id
     * @var string $is_geo_encoded
     * @return NULL
     */
    public function failedToGeoencode($pdao, $post_id, $is_geo_encoded) {
        switch ($is_geo_encoded) {
            case 'ZERO_RESULTS':
                $pdao->setGeoencodedPost($post_id, self::ZERO_RESULTS);
                break;
            case 'OVER_QUERY_LIMIT':
                self::$is_api_available = false;
                $pdao->setGeoencodedPost($post_id, self::OVER_QUERY_LIMIT);
                break;
            case 'REQUEST_DENIED':
                $pdao->setGeoencodedPost($post_id, self::REQUEST_DENIED);
                break;
            case 'INVALID_REQUEST':
                $pdao->setGeoencodedPost($post_id, self::INVALID_REQUEST);
        }
    }

    /**
     * Calculate distance between reply and initial post
     * @var string $location1
     * @var string $location2
     * @return int $distance
     */
    public function getDistanceBetweenPosts($location1, $location2) {
        $latitude = array(
            '0' => 0,
            '1' => 0
        );
        $longitude = array(
            '0' => 0,
            '1' => 0
        );
        $place1 = explode(',',$location1,2);
        $latitude[0] = $place1[0];
        $longitude[0] = $place1[1];
        $place2 = explode(',',$location2,2);
        $latitude[1] = $place2[0];
        $longitude[1] = $place2[1];
        if ($latitude[0] == 0 || $latitude[1] == 0 || $longitude[0] == 0 || $longitude[1] == 0) {
            return 0;
        }
        $theta = $longitude[0] - $longitude[1];
        $sine = sin(deg2rad($latitude[0])) * sin(deg2rad($latitude[1]));
        $cosine = cos(deg2rad($latitude[0])) * cos(deg2rad($latitude[1])) * cos(deg2rad($theta));
        $distance = $sine + $cosine;
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        $distance = $distance * 1.609344;
        return (round($distance));
    }

    /**
     * Method to find distance between reply and initial post
     * @var PostDAO $pdao
     * @var array $post
     * @var str $geodata
     * @return int $reply_retweet_distance
     */
    public function getDistance($pdao, $post, $geodata) {
        $reply_retweet_distance = false;
        if ($post['in_reply_to_post_id']!=NULL) {
            if ($pdao->isPostInDB($post['in_reply_to_post_id'], 'twitter')) {
                $original_post = $pdao->getPost($post['in_reply_to_post_id'], 'twitter');
                if ($original_post->is_geo_encoded == 1) {
                    $o_post_geo = $original_post->geo;
                    $reply_retweet_distance = self::getDistanceBetweenPosts($geodata, $o_post_geo);
                } else if ($original_post->is_geo_encoded == 0) {
                    return false;
                }
            } else {
                $reply_retweet_distance = -1;
            }
        }
        if ($post['in_retweet_of_post_id']!=NULL) {
            if ($pdao->isPostInDB($post['in_retweet_of_post_id'], 'twitter')) {
                $original_post = $pdao->getPost($post['in_retweet_of_post_id'], 'twitter');
                if ($original_post->is_geo_encoded == 1) {
                    $o_post_geo = $original_post->geo;
                    $reply_retweet_distance = self::getDistanceBetweenPosts($geodata, $o_post_geo);
                } else if ($original_post->is_geo_encoded == 0) {
                    return false;
                }
            } else {
                $reply_retweet_distance = -1;
            }
        }
        return $reply_retweet_distance;
    }

    /**
     * Retrieve data for Geoencoding
     * @var string $location
     * @return string $filecontents
     */
    public function getDataForGeoencoding ($location) {
        $location = urlencode($location);
        $url = "http://maps.google.com/maps/api/geocode/json?address=".$location."&sensor=true";
        $filecontents=file_get_contents("$url");
        return $filecontents;
    }

    /**
     * Retrieve data for Reverse Geoencoding
     * @var float $latitude
     * @var float $longitude
     * @return string $filecontents
     */
    public function getDataForReverseGeoencoding($latlng) {
        $latlng = explode(' ', $latlng, 2);
        $url = "http://maps.google.com/maps/api/geocode/json?latlng=$latlng[0],$latlng[1]&sensor=true";
        $filecontents=file_get_contents("$url");
        return $filecontents;
    }
}