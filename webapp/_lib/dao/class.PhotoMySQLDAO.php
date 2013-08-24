<?php
/**
 *
 * ThinkUp/webapp/_lib/dao/class.PhotoMySQLDAO.php
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
 * Photo Data Access Object
 *
 * The data access object for retrieving and saving photos in the ThinkUp database
 *
 * @author Nilaksh Das <nilakshdas[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das
 *
 */
class PhotoMySQLDAO extends PostMySQLDAO implements PhotoDAO {
    /**
     * Fields required for a photo.
     * @var array
     */
    var $REQUIRED_PHOTO_FIELDS =  array('photo_page', 'standard_resolution_url');

    /**
     * Optional fields in a photo
     * @var array
     */
    var $OPTIONAL_PHOTO_FIELDS = array('filter', 'low_resolution_url', 'thumbnail_url');

    /**
     * Checks to see if the $vals array contains all the required fields to insert a photo
     * @param array $vals
     * @return bool
     */
    private function hasAllRequiredFields($vals) {
        $result = true;
        foreach ($this->REQUIRED_PHOTO_FIELDS as $field) {
            if ( !isset($vals[$field]) ) {
                $this->logger->logError("Missing photo $field value", __METHOD__.','.__LINE__);
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Creates the Photo object from an associated Post object
     * @param Post $post
     * @return Photo
     */
    private function getPhotoObjectFromAssociatedPostObject(Post $post) {
        $q = "SELECT ph.* ";
        $q .= "FROM #prefix#photos AS ph ";
        $q .= "WHERE ph.internal_post_id=:associated_post_key;";
        $vars = array(':associated_post_key' => $post->id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $photo_row = $this->getDataRowAsArray($ps);

        foreach ($post as $key => $value) {
            if ($key != 'id') { $photo_row[$key] = $value; }
        }

        $photo = new Photo($photo_row);
        return $photo;
    }

    public function addPhoto($vals) {
        if (self::hasAllRequiredFields($vals)) {
            // Insert values into the post table
            $internal_post_id = parent::addPost($vals);

            // If the post insertion went fine insert the values that go into the photos table
            if ($internal_post_id) {
                //SQL variables to bind
                $vars = array();
                //SQL query
                $q = "INSERT IGNORE INTO #prefix#photos SET ";
                //Set up required fields
                foreach ($this->REQUIRED_PHOTO_FIELDS as $field) {
                    $q .= $field."=:".$field.", ";
                    $vars[':'.$field] = $vals[$field];
                }
                //Set up any optional fields
                foreach ($this->OPTIONAL_PHOTO_FIELDS as $field) {
                    if (isset($vals[$field]) && $vals[$field] != '') {
                        $q .= $field."=:".$field.", ";
                        $vars[':'.$field] = $vals[$field];
                    }
                }

                // Append the internal post ID
                $q .= 'internal_post_id=:internal_post_id;';
                $vars[':internal_post_id'] = $internal_post_id;

                // Insert the video in the database
                if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
                $ps = $this->execute($q, $vars);
                $res = $this->getInsertId($ps);
                return $res;
            }
        } else {
            $status_message = "Could not insert photo ID ".$vals["post_id"].", missing values";
            $this->logger->logError($status_message, __METHOD__.','.__LINE__);
            //doesn't have all req'd values
            return false;
        }
    }

    public function getPhoto($post_id, $network, $is_public = false) {
        $associated_post = parent::getPost($post_id,$network,$is_public);
        $photo = self::getPhotoObjectFromAssociatedPostObject($associated_post);
        return $photo;
    }
}