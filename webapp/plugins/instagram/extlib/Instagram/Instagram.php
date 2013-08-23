<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram;

use \Instagram\Collection\MediaSearchCollection;
use \Instagram\Collection\TagCollection;
use \Instagram\Collection\UserCollection;
use \Instagram\Collection\MediaCollection;
use \Instagram\Collection\LocationCollection;
use \Instagram\CurrentUser;
use \Instagram\User;
use \Instagram\Media;
use \Instagram\Tag;
use \Instagram\Location;

/**
 * Instagram!
 *
 * All objects are created through this object
 */
class Instagram extends \Instagram\Core\BaseObjectAbstract {

    /**
     * Constructor
     *
     * You can supply a client, proxy, and an access token via the config array
     *
     * @param string $access_token Instagram access token obtained through authentication
     * @param \Instagram\Net\ClientInterface $client Client object used to connect to the API
     * @access public
     */
    public function __construct( $access_token = null, \Instagram\Net\ClientInterface $client = null ) {
        $this->proxy = new \Instagram\Core\Proxy( $client ?: new \Instagram\Net\CurlClient, $access_token ?: null );
    }

    /**
     * Set the access token
     *
     * Most API calls require an access ID
     * 
     * @param string $access_token
     * @access public
     */
    public function setAccessToken( $access_token ) {
        $this->proxy->setAccessToken( $access_token );
    }

    /**
     * Set the client ID
     *
     * Some API calls can be called with only a Client ID
     * 
     * @param string $client_id Client ID
     * @access public
     */
    public function setClientID( $client_id ) {
        $this->proxy->setClientId( $client_id );
    }

    /**
     * Logout
     *
     * This doesn't actually work yet, waiting for Instagram to implement it in their API
     *
     * @access public
     */
    public function logout() {
        $this->proxy->logout();
    }

    /**
     * Get user
     *
     * Retrieve a user given his/her ID
     *
     * @param int $id ID of the user to retrieve
     * @return \Instagram\User
     * @access public
     */
    public function getUser( $id ) {
        $user = new User( $this->proxy->getUser( $id ), $this->proxy );
        return $user;
    }

    /**
     * Get user by Username
     *
     * Retrieve a user given their username
     *
     * @param string $username Username of the user to retrieve
     * @return \Instagram\User
     * @access public
     * @throws \Instagram\ApiException
     */
    public function getUserByUsername( $username ) {
        $user = $this->searchUsers( $username, array( 'count' => 1 ) )->getItem( 0 );
        if ( $user ) {
            try {
                return $this->getUser( $user->getId() );
            } catch( \Instagram\Core\ApiException $e ) {
                if ( $e->getType() == $e::TYPE_NOT_ALLOWED ) {
                    return $user;
                }
            }
        }
        throw new \Instagram\Core\ApiException( 'username not found', 400, 'InvalidUsername' );
    }

    /**
     * Check if a user is private
     *
     * @return bool
     * @access public
     */
    public function isUserPrivate( $user_id ) {
        $relationship = $this->proxy->getRelationshipToCurrentUser( $user_id );
        return (bool)$relationship->target_user_is_private;
    }

    /**
     * Get media
     *
     * Retreive a media object given it's ID
     *
     * @param int $id ID of the media to retrieve
     * @return \Instagram\Media
     * @access public
     */
    public function getMedia( $id ) {
        $media = new Media( $this->proxy->getMedia( $id ), $this->proxy );
        return $media;
    }

    /**
     * Get Tag
     *
     * @param string $tag Tag to retrieve
     * @return \Instagram\Tag
     * @access public
     */
    public function getTag( $tag ) {
        $tag = new Tag( $this->proxy->getTag( $tag ), $this->proxy );
        return $tag;
    }

    /**
     * Get location
     *
     * Retreive a location given it's ID
     *
     * @param int $id ID of the location to retrieve
     * @return \Instagram\Location
     * @access public
     */
    public function getLocation( $id ) {
        $location = new Location( $this->proxy->getLocation( $id ), $this->proxy );
        return $location;
    }

    /**
     * Get current user
     *
     * Returns the current user wrapped in a CurrentUser object
     *
     * @return \Instagram\CurrentUser
     * @access public
     */
    public function getCurrentUser() {
        $current_user = new CurrentUser( $this->proxy->getCurrentUser(), $this->proxy );
        return $current_user;
    }

    /**
     * Get popular media
     *
     * Returns current popular media
     *
     * @return \Instagram\Collection\MediaCollection
     * @access public
     */
    public function getPopularMedia() {
        $popular_media = new MediaCollection( $this->proxy->getPopularMedia(), $this->proxy );
        return $popular_media;
    }

    /**
     * Search users
     *
     * Search the users by username
     *
     * @param string $query Search query
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\Collection\UserCollection
     * @access public
     */
    public function searchUsers( $query, array $params = null ) {
        $params = (array)$params;
        $params['q'] = $query;
        $user_collection = new UserCollection( $this->proxy->searchUsers( $params ), $this->proxy );
        return $user_collection;
    }

    /**
     * Search Media
     *
     * Returns media that is a certain distance from a given lat/lng
     *
     * To specify a distance, pass the distance (in meters) in the $params
     *
     * Default distance is 1000m
     *
     * @param float $lat Latitude of the search
     * @param float $lng Longitude of the search
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\Collection\MediaSearchCollection
     * @access public
     */
    public function searchMedia( $lat, $lng, array $params = null ) {
        $params = (array)$params;
        $params['lat'] = (float)$lat;
        $params['lng'] = (float)$lng;
        $media_collection =  new MediaSearchCollection( $this->proxy->searchMedia( $params ), $this->proxy );
        return $media_collection;
    }

    /**
     * Search for tags
     *
     * @param string $query Search query
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\Collection\TagCollection
     * @access public
     */
    public function searchTags( $query, array $params = null ) {
        $params = (array)$params;
        $params['q'] = $query;
        $tag_collection =  new TagCollection( $this->proxy->searchTags( $params ), $this->proxy );
        return $tag_collection;
    }

    /**
     * Search Locations
     *
     * Returns locations that are a certain distance from a given lat/lng
     *
     * To specify a distance, pass the distance (in meters) in the $params
     *
     * Default distance is 1000m
     *
     * @param float $lat Latitude of the search
     * @param float $lng Longitude of the search
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\LocationCollection
     * @access public
     */
    public function searchLocations( $lat, $lng, array $params = null ) {
        $params = (array)$params;
        $params['lat'] = (float)$lat;
        $params['lng'] = (float)$lng;
        $location_collection = new LocationCollection( $this->proxy->searchLocations( $params ), $this->proxy );
        return $location_collection;
    }

}