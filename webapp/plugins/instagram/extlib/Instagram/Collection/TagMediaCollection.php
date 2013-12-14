<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Tag Media Collection
 *
 * Holds a collection of media associated with a tag
 */
class TagMediaCollection extends \Instagram\Collection\MediaCollection {

    /**
     * Get next max tag id
     *
     * Get the next max tag id for use in pagination
     *
     * @return string Returns the next max tag id
     * @access public
     */
    public function getNextMaxTagId() {
        return isset( $this->pagination->next_max_tag_id ) ? $this->pagination->next_max_tag_id : null;
    }

    /**
     * Get min tag id
     *
     * Get the minimum tag id.
     * if you're using the Realtime API and fetch media from /media/recent for Tags,
     * you should save the min_tag_id and pass it in next time you hit that endpoint
     * in response to a realtime push; you'll receive all media since the last time you checked.
     *
     * @return string Return the min tag id
     * @access public
     */
    public function getMinTagId() {
        return isset( $this->pagination->min_tag_id ) ? $this->pagination->min_tag_id : null;
    }


    /**
     * Get next max tag id
     *
     * Get the next max tag id for use in pagination
     *
     * @return string Returns the next max tag id
     * @access public
     */
    public function getNext() {
        return $this->getNextMaxTagId();
    }

}