<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Core;

/**
 * API Exception
 *
 * This exception type will be thrown for any API error
 *
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/index.php#L48}
 */
class ApiException extends \Exception {

    /**
     * Invalid APU URI
     */
    const TYPE_NOT_ALLOWED  = 'APINotAllowedError';

    /**
     * Authorization error
     */
    const TYPE_OAUTH        = 'OAuthAccessTokenException';

    /**
     * Type of error
     * 
     * @var string
     */
    protected $type;

    /**
     * Constructor
     * 
     * @param string  $message Error message
     * @param integer $code Error code
     * @param string  $type Error type
     * @param Exception $previous Previous exception
     */
    public function __construct( $message = null, $code = 0, $type = null, \Exception $previous = null ) {
        $this->type = $type;
        parent::__construct( $message, $code, $previous );
    }

    /**
     * Get error type
     * 
     * @return string Get teh error type
     */
    public function getType() {
        return $this->type;
    }

}