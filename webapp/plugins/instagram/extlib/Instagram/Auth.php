<?php

/**
 * Instagram PHP
 * @author Galen Grover <galenjr@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

namespace Instagram;

/**
 * Auth class
 *
 * Handles authentication
 * 
 * {@link https://github.com/galen/PHP-Instagram-API#authentication}
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/_auth.php}
 */
class Auth {

    /**
     * Configuration array
     *
     * Contains a default client and proxy
     *
     * client_id:       These three items are required for authorization
     * redirect_uri:    URL that the Instagram API shoudl redirect to
     * grant_type:      Grant type from the Instagram API. Only authorization_code is accepted right now.
     * scope:           {@link http://instagram.com/developer/auth/#scope}
     * display:         Pass in "touch" if you'd like your authenticating users to see a mobile-optimized
     *                  version of the authenticate page and the sign-in page. 
     *
     * @var array
     * @access protected
     */
        protected $config = array(
        'client_id'     => '',
        'client_secret' => '',
        'redirect_uri'  => '',
        'grant_type'    => 'authorization_code',
        'scope'         => array( 'basic' ),
        'display'       => ''
        );

    /**
     * Constructor
     *
     * @param array $config Configuration array
     * @param \Instagram\Net\ClientInterface $client Client object used to connect to the API
     * @access public
     */
    public function __construct( array $config = null, \Instagram\Net\ClientInterface $client = null ) {
        $this->config = (array) $config + $this->config;
        $this->proxy = new \Instagram\Core\Proxy( $client ? $client : new \Instagram\Net\CurlClient );
    }

    /**
     * Authorize
     *
     * Redirects the user to the Instagram authorization url
     * @access public
     */
    public function authorize() {
        header(
            sprintf(
                'Location:https://api.instagram.com/oauth/authorize/?client_id=%s&redirect_uri=%s&response_type=code&scope=%s',
                $this->config['client_id'],
                $this->config['redirect_uri'],
                implode( '+', $this->config['scope'] )
            )
        );
        exit;
    }

    /**
     * Get the access token
     *
     * POSTs to the Instagram API and obtains and access key
     *
     * @param string $code Code supplied by Instagram
     * @return string Returns the access token
     * @throws \Instagram\Core\ApiException
     * @access public
     */
    public function getAccessToken( $code ) {
        $post_data = array(
            'client_id'         => $this->config['client_id'],
            'client_secret'     => $this->config['client_secret'],
            'grant_type'        => $this->config['grant_type'],
            'redirect_uri'      => $this->config['redirect_uri'],
            'code'              => $code
        );
        $response = $this->proxy->getAccessToken( $post_data );
        if ( isset( $response->getRawData()->access_token ) ) {
            return $response->getRawData()->access_token;
        }
        throw new \Instagram\Core\ApiException( $response->getErrorMessage(), $response->getErrorCode(), $response->getErrorType() );
    }


}