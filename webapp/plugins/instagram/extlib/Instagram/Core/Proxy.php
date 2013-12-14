<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Core;

/**
 * Proxy
 *
 * This class performs all the API calls
 *
 * It uses the supplied HTTP client as a default (cURL)
 * 
 */
class Proxy {

    /**
     * HTTP Client
     * 
     * @var \Instagram\Net\ClientInterface
     * @access protected
     */
    protected $client;

    /**
     * Instagram access token
     * 
     * @var string
     * @access protected
     */
    protected $access_token = null;

    /**
     * Client ID
     * 
     * @var string
     * @access protected
     */
    protected $client_id = null;

    /**
     * API URL
     * 
     * @var string
     * @access protected
     */
    protected $api_url = 'https://api.instagram.com/v1';

    /**
     * Constructor
     *
     * @param \Instagram\Net\ClientInterface $client HTTP Client
     * @param string $access_token The access token from authentication
     * @access public
     */
    public function __construct( \Instagram\Net\ClientInterface $client, $access_token = null ) {
        $this->client = $client;
        $this->access_token = $access_token;
    }

    /**
     * Get the access token
     * @param array $data Auth data
     * @return string Returns the access token
     */
    public function getAccessToken( array $data ) {
        $response = $this->apiCall(
            'post',
            'https://api.instagram.com/oauth/access_token',
            $data
        );
        return $response;
    }

    /**
     * Set the access token
     * 
     * @param string $access_token The access token
     * @access public
     */
    public function setAccessToken( $access_token ) {
        $this->access_token = $access_token;
    }

    /**
     * Set the client ID
     * 
     * @param string $client_id the client ID
     * @access public
     */
    public function setClientID( $client_id ) {
        $this->client_id = $client_id;
    }

    /**
     * Logout of instagram
     *
     * This hasn't been implemented by instagram yet
     * 
     * @access public
     */
    public function logout() {
        $this->client->get( 'https://instagram.com/accounts/logout/', array() );
    }

    /**
     * Get the media associated with an object
     *
     * This function is used by the individual object functions
     * getLocationMedia, getTagMedia, atc...
     * 
     * @param  string $api_endpoint API endpoint for the object type
     * @param  string $id Id of the object to get the media for
     * @param  array $params Extra parameters for the API call
     * @return StdClass Returns the raw response
     * @access protected
     */
    protected function getObjectMedia( $api_endpoint, $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/%s/%s/media/recent', $this->api_url, strtolower( $api_endpoint ), $id  ),
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get location media
     * 
     * @param string $id Location ID
     * @param array $params Extra params to pass to the API
     * @return StdClass Returns the location media
     * @access public
     */
    public function getLocationMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Locations', $id, $params );
    }

    /**
     * Get tag media
     * 
     * @param string $id Location ID
     * @param array $params Extra params to pass to the API
     * @return StdClass Returns the location media
     * @access public
     */
    public function getTagMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Tags', $id, $params );
    }

    /**
     * Get user media
     * 
     * @param string $id Location ID
     * @param array $params Extra params to pass to the API
     * @return StdClass Returns the location media
     * @access public
     */
    public function getUserMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Users', $id, $params );
    }

    /**
     * Get user
     * 
     * @param string $id User ID
     * @return StdClass Returns the user data
     * @access public
     */
    public function getUser( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    /**
     * Get a user's follows
     * 
     * @param string $id User's ID
     * @param array $params Extra params to pass to the API
     * @return StdClass Returns the user's followers
     * @access public
     */
    public function getUserFollows( $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s/follows', $this->api_url, $id ),
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get a user's followers
     * 
     * @param string $id User's ID
     * @param array $params Extra params to pass to the API
     * @return StdClass Returns the user's followers
     * @access public
     */
    public function getUserFollowers( $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s/followed-by', $this->api_url, $id ),
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get media comments
     * 
     * @param string $id Media ID
     * @return StdClass Returns the media data
     * @access public
     */
    public function getMediaComments( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s/comments', $this->api_url, $id )
        );
        return $response->getRawData();
    }

    /**
     * Get media likes
     * 
     * @param string $id Media ID
     * @return StdClass Returns the media likes
     * @access public
     */
    public function getMediaLikes( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s/likes', $this->api_url, $id )
        );
        return $response->getRawData();
    }

    /**
     * Get media comments
     * 
     * @return StdClass Returns the current user data
     * @access public
     */
    public function getCurrentUser() {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/self', $this->api_url )
        );
        return $response->getData();
    }

    /**
     * Get media
     * 
     * @param string $id Media ID
     * @return StdClass Returns the media data
     * @access public
     */
    public function getMedia( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    /**
     * Get tag
     * 
     * @param string $id Tag ID
     * @return StdClass Returns the tag data
     * @access public
     */
    public function getTag( $tag ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/tags/%s', $this->api_url, $tag )
        );
        return $response->getData();
    }

    /**
     * Get location
     * 
     * @param string $id Location ID
     * @return StdClass Returns the location data
     * @access public
     */
    public function getLocation( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/locations/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    /**
     * Search users
     * 
     * @param array $params Search params
     * @return array Returns an array of user data
     * @access public
     */
    public function searchUsers( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/search',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Search tags
     * 
     * @param array $params Search params
     * @return array Returns an array of tag data
     * @access public
     */
    public function searchTags( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/tags/search',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Search media
     * 
     * @param array $params Search params
     * @return array Returns an array of media data
     * @access public
     */
    public function searchMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/media/search',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Search locations
     * 
     * @param array $params Search params
     * @return array Returns an array of location data
     * @access public
     */
    public function searchLocations( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/locations/search',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get popular media
     * 
     * @param array $params Extra params
     * @return array Returns an array of popular media data
     * @access public
     */
    public function getPopularMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/media/popular',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get the current user's feed
     * 
     * @param array $params Extra params
     * @return array Returns an array of media data
     * @access public
     */
    public function getFeed( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/feed',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get the current users follow requests
     * 
     * @param $params Extra params (not used in API, here in case it's added)
     * @return array Returns an array of user data
     * @access public
     */
    public function getFollowRequests( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/requested-by',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get the current user's liked media
     * 
     * @param array $params Extra params
     * @return array Returns an array of media data
     * @access public
     */
    public function getLikedMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/media/liked',
            $params
        );
        return $response->getRawData();
    }

    /**
     * Get a user's relationship to the current user
     * 
     * @param string $user_id User to check relationship for
     * @return StdClass Returns the relationship
     * @access public
     */
    public function getRelationshipToCurrentUser( $user_id ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . sprintf( '/users/%s/relationship', $user_id )
        );
        return $response->getData();
    }

    /**
     * Modify a relationship with the current user
     * @param string $user_id User ID of the user to change the relationship for
     * @param string $relationship New relationship {@link http://instagram.com/developer/endpoints/relationships/#post_relationship}
     * @return StdClass Returns the status
     * @access public
     */
    public function modifyRelationship( $user_id, $relationship ) {
        $response = $this->apiCall(
            'post',
            $this->api_url . sprintf( '/users/%s/relationship', $user_id ),
            array( 'action' => $relationship )
        );
        return $response->getData();
    }

    /**
     * Add a like form the current user on a media
     * 
     * @param string $media_id Media ID to like
     * @return StdClass Returns the status
     * @access public
     */
    public function like( $media_id ) {
        $this->apiCall(
            'post',
            $this->api_url . sprintf( '/media/%s/likes', $media_id )
        );
    }

    /**
     * Delete a like form the current user on a media
     * 
     * @param string $media_id Media ID to unlike
     * @return StdClass Returns the status
     * @access public
     */
    public function unLike( $media_id ) {
        $this->apiCall(
            'delete',
            $this->api_url . sprintf( '/media/%s/likes', $media_id )
        );
    }

    /**
     * Add a comment to a media
     * 
     * @param string $media_id Media ID
     * @param string $text Comment text
     * @return StdClass Returns the status
     * @access public
     */
    public function addMediaComment( $media_id, $text ) {
        $this->apiCall(
            'post',
            $this->api_url . sprintf( '/media/%s/comments', $media_id ),
            array( 'text' => $text )
        );
    }

    /**
     * Delete a comment from a media
     * 
     * @param string $media_id Media ID
     * @param string $comment_id Comment ID to delete
     * @return StdClass
     * @access public
     */
    public function deleteMediaComment( $media_id, $comment_id ) {
        $this->apiCall(
            'delete',
            $this->api_url . sprintf( '/media/%s/comments/%s', $media_id, $comment_id )
        );
    }

    /**
     * Make a call to the API
     * 
     * @param string $method HTTP method to use
     * @param string $url URL
     * @param array $params API parameters
     * @param boolean $throw_exception True to throw exceptoins
     * @throws APIException, APIAuthException
     * @return  \Instagram\Net\ApiResponse Returns teh API response
     * @access private
     */
    private function apiCall( $method, $url, array $params = null, $throw_exception = true ){

        $raw_response = $this->client->$method(
            $url,
            array(
                'access_token'  => $this->access_token,
                'client_id'     => isset( $params['client_id'] ) ? $params['client_id'] : $this->client_id
            ) + (array) $params
        );

        $response = new \Instagram\Net\ApiResponse( $raw_response );

        if ( !$response->isValid() ) {
            if ( $throw_exception ) {
                if ( $response->getErrorType() == 'OAuthAccessTokenException' ) {
                    throw new \Instagram\Core\ApiAuthException( $response->getErrorMessage(), $response->getErrorCode(), $response->getErrorType() );
                }
                else {
                    throw new \Instagram\Core\ApiException( $response->getErrorMessage(), $response->getErrorCode(), $response->getErrorType() );
                }
            }
            else {
                return false;
            }
        }
        return $response;
    }


}