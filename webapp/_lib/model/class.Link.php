<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Link.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 *
 * Link object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author christoffer Viken <christoffer[at]viken[dot]me>
 */
class Link {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str Link URL as it appears inside the post, ie, shortened in tweets.
     */
    var $url;
    /**
     * @var str Link URL expanded from its shortened form.
     */
    var $expanded_url;
    /**
     * @var str Link title.
     */
    var $title = '';
    /**
     * @var str Link description.
     */
    var $description = '';
    /**
     * @var str URL of a thumbnail image associated with this link.
     */
    var $image_src = '';
    /**
     * @var str Link or image caption.
     */
    var $caption = '';
    /**
     * @var int Internal ID of the post in which this link appeared.
     */
    var $post_key;
    /**
     * @var str Details of any error expanding a link.
     */
    var $error = '';
    /**
     * Container post
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
        if ($val){
            $this->constructValIncluded($val);
        } else {
            $this->constructNoVal();
        }
    }

    /**
     * Subroutine for construct for when arguments are passed
     * @param array $val
     */
    private function constructValIncluded($val){
        if (isset($val["url"])) {
            if (isset($val["id"])) {
                $this->id = $val["id"];
            }
            $this->url = $val["url"];
            if (isset($val["expanded_url"])) {
                $this->expanded_url = $val["expanded_url"];
            }

            if (isset($val["title"])) {
                $this->title = $val["title"];
            }

            if (isset($val["post_key"])) {
                $this->post_key = $val["post_key"];
            }

            if (isset($val["error"])) {
                $this->error = $val["error"];
            }

            if (isset($val["description"])) {
                $this->description = $val['description'];
            }
            if (isset($val["image_src"])) {
                $this->image_src = $val['image_src'];
            }
            if (isset($val["caption"])) {
                $this->caption = $val['caption'];
            }
        }
    }

    /**
     * Construct for when no value is passed, i.e. during slipstreaming
     */
    private function constructNoVal(){
        if (isset($this->other['author_user_id'])){
            $this->other['id'] = $this->id;
            $this->other['post_key'] = $this->post_key;
            $this->container_post = new Post($this->other);
        }
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

    /**
     * If http:// is missing from the beginning of a string which represents a URL, add it.
     * @param str $url
     * @return str
     */
    public static function addMissingHttp($url) {
        return ((0===stripos($url, 'http')) ? $url : 'http://'.$url);
    }
}
