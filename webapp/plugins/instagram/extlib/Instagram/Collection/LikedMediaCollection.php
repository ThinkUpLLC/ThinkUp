<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Liked Media Collection
 *
 * Holds a collection of liked media
 */
class LikedMediaCollection extends \Instagram\Collection\MediaCollection {

    /**
     * Get the next max like ID
     * 
     * @return string
     * @access public
     */
    public function getNextMaxLikeId() {
        return isset( $this->pagination->next_max_like_id ) ? $this->pagination->next_max_like_id : null;
    }

    /**
     * Get the next max like ID
     * 
     * @return string
     * @access public
     */
    public function getNext() {
        return $this->getNextMaxLikeId();
    }

}