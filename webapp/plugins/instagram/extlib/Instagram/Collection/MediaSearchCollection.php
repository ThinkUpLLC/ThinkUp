<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Media Search Collection
 *
 * Holds a collection of searched media
 */
class MediaSearchCollection extends \Instagram\Collection\CollectionAbstract {

    /**
     * Next max timestamp for use in pagination
     *
     * @var int
     * @access protected
     */
    protected $next_max_timestamp;

    /**
     * Set the collection data
     *
     * @param StdClass $raw_data
     * @access public
     */
    public function setData( $raw_data ) {
        $this->data = $raw_data->data;
        $this->convertData( '\Instagram\Media' );
        $this->next_max_timestamp = count( $this->data ) ? $this->data[ count( $this->data )-1 ]->getCreatedTime() : null;
    }

    /**
     * Get next max timestamp
     *
     * Get the next max timestamp for use in pagination
     *
     * @return string Returns the next max timestamp
     * @access public
     */
    public function getNextMaxTimeStamp() {
        return $this->next_max_timestamp;
    }

    /**
     * Get next max timestamp
     *
     * Get the next max timestamp for use in pagination
     *
     * @return string Returns the next max timestamp
     * @access public
     */
    public function getNext() {
        return $this->getNextMaxTimeStamp();
    }

}