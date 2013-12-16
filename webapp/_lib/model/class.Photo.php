<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Photo.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
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
 * Photo class
 *
 * This class represents a photo posted on networks like Instagram.
 *
 * @author Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
 *
 */
class Photo extends Post {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Internal ID of photo post.
     */
    var $post_key;
    /**
     * @var str Native filter used on the photo.
     */
    var $filter;
    /**
     * @var str URL of standard resolution image file.
     */
    var $standard_resolution_url;
    /**
     * @var str URL of low resolution image file.
     */
    var $low_resolution_url;
    /**
     * @var str URL of thumbnail image file.
     */
    var $thumbnail_url;
    public function __construct($val = false) {
        parent::__construct($val);
        $this->id = $val['id'];
        $this->post_key = $val['post_key'];
        $this->filter = $val['filter'];
        $this->standard_resolution_url = $val['standard_resolution_url'];
        $this->low_resolution_url = $val['low_resolution_url'];
        $this->thumbnail_url = $val['thumbnail_url'];
    }
}