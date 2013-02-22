<?php
/**
 *
 * ThinkUp/webapp/_lib/class.JSONDecoder.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * @author Gina Trapani
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013  Gina Trapani
 */
class JSONDecoder {
    /**
     * Decode JSON.
     * @param str $json
     * @param bool $assoc Whether or not to return an associative array, defaults to false
     * @return mixed Decoded JSON
     * @throws JSONDecoderException
     */
    public static function decode($json, $assoc = false) {
        if (empty($json)) {
            throw new JSONDecoderException('Cannot decode an empty string');
        }
        $result = json_decode($json, $assoc);
        /*
         http://www.php.net/manual/en/function.json-last-error.php
         JSON_ERROR_NONE  No error has occurred
         JSON_ERROR_DEPTH    The maximum stack depth has been exceeded
         JSON_ERROR_STATE_MISMATCH   Invalid or malformed JSON
         JSON_ERROR_CTRL_CHAR    Control character error, possibly incorrectly encoded
         JSON_ERROR_SYNTAX   Syntax error
         JSON_ERROR_UTF8 Malformed UTF-8 characters, possibly incorrectly encoded    PHP 5.3.3
         */
        if (function_exists('json_last_error')) { //PHP 5.3 and later
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $error =  'The maximum stack depth has been exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error =  'Invalid or malformed JSON';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Control character error, possibly incorrectly encoded';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error due to malformed JSON';
                    break;
                    //            PHP 5.3.3 only
                    //            case JSON_ERROR_UTF8:
                    //                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    //                break;
                case JSON_ERROR_NONE:
                default:
                    $error = '';
            }
        }
        if (!empty($error)) {
            throw new JSONDecoderException('JSON Error: '.$error);
        } elseif ($result === null) {
            throw new JSONDecoderException('JSON Error decoding data');
        }
        return $result;
    }
}