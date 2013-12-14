<?php
namespace Instagram\Core;

require_once THINKUP_WEBAPP_PATH.'plugins/instagram/extlib/SplClassLoader.php';
$loader = new \SplClassLoader('Instagram', THINKUP_WEBAPP_PATH.'plugins/instagram/extlib');
$loader->register();

class Proxy {
    protected $client;

    protected $access_token = null;

    protected $client_id = null;

    protected $api_url = '';

    public function __construct( \Instagram\Net\ClientInterface $client, $access_token = null ) {
        $this->client = $client;
        $this->access_token = $access_token;
    }

    public function getAccessToken( array $data ) {
        return new \Instagram\Net\ApiResponse(file_get_contents(
            THINKUP_WEBAPP_PATH.'plugins/instagram/tests/testdata/access_token_'.$data['client_id']));
    }

    public function setAccessToken( $access_token ) {
        $this->access_token = $access_token;
    }

    public function setClientID( $client_id ) {
        $this->client_id = $client_id;
    }

    public function logout() {}

    protected function getObjectMedia( $api_endpoint, $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/%s/%s/media/recent', $this->api_url, strtolower( $api_endpoint ), $id  ),
            $params
        );
        return $response->getRawData();
    }

    public function getLocationMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Locations', $id, $params );
    }

    public function getTagMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Tags', $id, $params );
    }

    public function getUserMedia( $id, array $params = null ) {
        return $this->getObjectMedia( 'Users', $id, $params );
    }

    public function getUser( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    public function getUserFollows( $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s/follows', $this->api_url, $id ),
            $params
        );
        return $response->getRawData();
    }

    public function getUserFollowers( $id, array $params = null ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/%s/followed-by', $this->api_url, $id ),
            $params
        );
        return $response->getRawData();
    }

    public function getMediaComments( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s/comments', $this->api_url, $id )
        );
        return $response->getRawData();
    }

    public function getMediaLikes( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s/likes', $this->api_url, $id )
        );
        return $response->getRawData();
    }

    public function getCurrentUser() {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/users/self', $this->api_url )
        );
        return $response->getData();
    }

    public function getMedia( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/media/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    public function getTag( $tag ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/tags/%s', $this->api_url, $tag )
        );
        return $response->getData();
    }

    public function getLocation( $id ) {
        $response = $this->apiCall(
            'get',
            sprintf( '%s/locations/%s', $this->api_url, $id )
        );
        return $response->getData();
    }

    public function searchUsers( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/search',
            $params
        );
        return $response->getRawData();
    }

    public function searchTags( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/tags/search',
            $params
        );
        return $response->getRawData();
    }

    public function searchMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/media/search',
            $params
        );
        return $response->getRawData();
    }

    public function searchLocations( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/locations/search',
            $params
        );
        return $response->getRawData();
    }

    public function getPopularMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/media/popular',
            $params
        );
        return $response->getRawData();
    }

    public function getFeed( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/feed',
            $params
        );
        return $response->getRawData();
    }

    public function getFollowRequests( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/requested-by',
            $params
        );
        return $response->getRawData();
    }

    public function getLikedMedia( array $params = null ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . '/users/self/media/liked',
            $params
        );
        return $response->getRawData();
    }

    public function getRelationshipToCurrentUser( $user_id ) {
        $response = $this->apiCall(
            'get',
            $this->api_url . sprintf( '/users/%s/relationship', $user_id )
        );
        return $response->getData();
    }

    public function modifyRelationship( $user_id, $relationship ) {
        $response = $this->apiCall(
            'post',
            $this->api_url . sprintf( '/users/%s/relationship', $user_id ),
            array( 'action' => $relationship )
        );
        return $response->getData();
    }

    public function like( $media_id ) {
        $this->apiCall(
            'post',
            $this->api_url . sprintf( '/media/%s/likes', $media_id )
        );
    }

    public function unLike( $media_id ) {
        $this->apiCall(
            'delete',
            $this->api_url . sprintf( '/media/%s/likes', $media_id )
        );
    }

    public function addMediaComment( $media_id, $text ) {
        $this->apiCall(
            'post',
            $this->api_url . sprintf( '/media/%s/comments', $media_id ),
            array( 'text' => $text )
        );
    }

    public function deleteMediaComment( $media_id, $comment_id ) {
        $this->apiCall(
            'delete',
            $this->api_url . sprintf( '/media/%s/comments/%s', $media_id, $comment_id )
        );
    }

    private function apiCall( $method, $url, array $params = null, $throw_exception = true ){
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '_', $url);
        $url = str_replace('?', '_', $url);
        $url = trim($method.$url.'_'.implode('_', $params), '_');
        $FAUX_DATA_PATH = THINKUP_WEBAPP_PATH.'plugins/instagram/tests/testdata/';
        if($this->access_token !== 'fauxaccesstokeninvalid') {
            $response = new \Instagram\Net\ApiResponse(self::decodeFileContents($FAUX_DATA_PATH.$url));
        } else {
            $response = new \Instagram\Net\ApiResponse(self::decodeFileContents(
                $FAUX_DATA_PATH.'invalid_access_token'));
        }

        if ( !$response->isValid() ) {
            if ( $throw_exception ) {
                if ( $response->getErrorType() == 'OAuthAccessTokenException' ) {
                    throw new \Instagram\Core\ApiAuthException( $response->getErrorMessage(),
                    $response->getErrorCode(), $response->getErrorType() );
                }
                else {
                    throw new \Instagram\Core\ApiException( $response->getErrorMessage(),
                    $response->getErrorCode(), $response->getErrorType() );
                }
            }
            else {
                return false;
            }
        }
        return $response;
    }

    private static function decodeFileContents($file_path) {
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$file_path. '
';
        }
        $contents=file_get_contents($file_path);
        return $contents;
    }
}
