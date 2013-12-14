<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Core;

/**
 * API Auth Exception
 *
 * This exception type will be thrown if the access token you are using is no longer valid.
 *
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/index.php#L39}
 */
class ApiAuthException extends \Instagram\Core\ApiException {}