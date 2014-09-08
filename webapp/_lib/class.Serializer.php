<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Serializer.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 * @copyright 2014  Gina Trapani
 */
class Serializer {
    /**
     * Unserialize a string.
     * @param str $string
     * @return mixed Unserialized data
     * @throws SerializerException
     */
    public static function unserializeString($serialized_string) {
        if (empty($serialized_string)) {
            throw new SerializerException('Cannot unserialize an empty string');
        }
        $result = unserialize($serialized_string);
        if ($result === false) {
            throw new SerializerException('String is unserializable');
        }
       return $result;
    }
}