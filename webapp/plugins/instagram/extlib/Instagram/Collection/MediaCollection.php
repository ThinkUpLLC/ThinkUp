<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Media Collection
 *
 * Holds a collection of media
 */
class MediaCollection extends \Instagram\Collection\CollectionAbstract {

    /**
     * Set the collection data
     *
     * @param StdClass $raw_data
     * @access public
     */
    public function setData( $raw_data ) {
        $this->data = $raw_data->data;
        $this->pagination = isset( $raw_data->pagination ) ? $raw_data->pagination : null;
        $this->convertData( '\Instagram\Media' );
    }

    /**
     * Get next max id
     *
     * Get the next max id for use in pagination
     *
     * @return string Returns the next max id
     * @access public
     */
    public function getNextMaxId() {
        return isset( $this->pagination->next_max_id ) ? $this->pagination->next_max_id : null;
    }

    /**
     * Get next url
     *
     * Get the API url for the next page of media
     * You shouldn't need to use this
     *
     * @return string Returns the next url
     * @access public
     */
    public function getNextUrl() {
        return isset( $this->pagination->next_url ) ? $this->pagination->next_url : null;
    }

    /**
     * Get next max id
     *
     * Get the next max id for use in pagination
     *
     * @return string Returns the next max id
     * @access public
     */
    public function getNext() {
        return $this->getNextMaxId();
    }

}