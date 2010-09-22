<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Link.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 *
 * Link object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author christoffer Viken <christoffer[at]viken[dot]me>
 */

class Link {
    /**
     * Unique Identifier in storage.
     * @var int
     */
    var $id;
    /**
     * Shortform URL
     * @var str
     */
    var $url;
    /**
     * Expanded URL
     * @var str
     */
    var $expanded_url;
    /**
     * Title of target page
     * @var str
     */
    var $title;
    /**
     * Click count
     * @var int
     */
    var $clicks;
    /**
     * ID of the post which this link was found
     * @var int
     */
    var $post_id;
    /**
     * Network of the post this link is in
     * @var str
     */
    var $network;
    /**
     * Link to an image?
     * @var bool
     */
    var $is_image;
    /**
     * Error message
     * @var str
     */
    var $error;
    /**
     * Direct image URL
     * @var str
     */
    var $img_src;
    /**
     * Container tweet
     * @var Post object
     */
    var $container_post;
    /**
     * Other values,
     * i.e. like properties for objects contained within a property of this object
     * @var array
     */
    var $other = array();
    /**
     * Constructor
     * @param array $val
     */
    public function __construct($val = false) {
        if($val){
            $this->constructValIncluded($val);
        }
        else {
            $this->constructNoVal();
        }
    }

    /**
     * Subroutine for construct for when arguments are passed
     * @param array $val
     */
    private function constructValIncluded($val){
        if (isset($val["url"])) {
            $this->id = $val["id"];
            $this->url = $val["url"];
            if (isset($val["expanded_url"])) {
                $this->expanded_url = $val["expanded_url"];
            }

            if (isset($val["title"])) {
                $this->title = $val["title"];
            }

            if (isset($val["clicks"])) {
                $this->clicks = $val["clicks"];
            }

            if (isset($val["post_id"])) {
                $this->post_id = $val["post_id"];
            }

            if (isset($val["network"])) {
                $this->network = $val["network"];
            }

            $this->is_image = PDODAO::convertDBToBool($val["is_image"]);

            if (isset($val["error"])) {
                $this->error = $val["error"];
            }
        }
    }

    /**
     * Construct for when no value is passed, i.e. during slipstreaming
     */
    private function constructNoVal(){
        if (isset($this->other['author_user_id'])){
            $this->other['id'] = $this->id;
            $this->other['post_id'] = $this->post_id;
            $this->other['network'] = $this->network;
            $this->container_post = new Post($this->other);
        }
        $this->is_image = PDODAO::convertDBToBool($this->is_image);
    }

    /**
     * For overloading when attempting to set undeclared properties
     * @param str $key
     * @param mixed $val
     */
    public function __set($key, $val){
        switch($key){
            default:
                $this->other[$key] = $val;
        }
    }
}
