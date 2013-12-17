<?php

class InstagramAPIAccessor {
    /**
     * Make an API request.
     * @param str $path
     * @param str $access_token
     * @param str $fields Comma-delimited list of fields to return from FB API
     * @return array Decoded JSON response
     */
    public static function apiRequest($type, $id, $access_token, $params = array()) {
        $instagram = new Instagram\Instagram($access_token);
        if($type == 'user') {
            return $instagram->getUser($id);
        } else if($type == 'friends') {
            $user = $instagram->getUser($id);
            return $user->getFollowers();
        } else if($type == 'media') {
            $user = $instagram->getUser($id);
            $media = $user->getMedia($params);
            return $media;
        }
    }
}
