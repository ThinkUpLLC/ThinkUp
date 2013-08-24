<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Photo.php
 *
 * Copyright (c) 2013 Nilaksh Das
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
 * @author Nilaksh Das <nilakshdas[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das
 *
 */
class Photo extends Post {
    /**
     * @var int Interal unique ID of photo.
     */
    var $id;
    /**
     * @var int ID of the row in the posts table for more information on this photo.
     */
    var $internal_post_id;
    /**
     * @var string URL of the photo page inside the respective service.
     */
    var $photo_page;
    /**
     * @var string Native filter used on this photo.
     */
    var $filter;
    /**
     * @var string URL of standard resolution image file of this photo.
     */
    var $standard_resolution_url;
    /**
     * @var string URL of low resolution image file of this photo.
     */
    var $low_resolution_url;
    /**
     * @var string URL of thumbnail image file of this photo.
     */
    var $thumbnail_url;
    /**
     * Constructor
     * @param array $val Array of key/value pairs
     * @return Photo
     */
    public function __construct($val) {
        parent::__construct($val);
        $this->id = $val['id'];
        $this->internal_post_id = $val['internal_post_id'];
        $this->photo_page = $val['photo_page'];
        $this->filter = $val['filter'];
        $this->standard_resolution_url = $val['standard_resolution_url'];
        $this->low_resolution_url = $val['low_resolution_url'];
        $this->thumbnail_url = $val['thumbnail_url'];
    }
}