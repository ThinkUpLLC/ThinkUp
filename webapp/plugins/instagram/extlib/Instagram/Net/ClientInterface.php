<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Net;

/**
 * Client Interface
 *
 * All clients must implement this interface
 *
 * The 4 http functions just need to return the raw data from the API
 */
interface ClientInterface {

    function get( $url, array $data = null );
    function post( $url, array $data = null );
    function put( $url, array $data = null );
    function delete( $url, array $data = null );

}