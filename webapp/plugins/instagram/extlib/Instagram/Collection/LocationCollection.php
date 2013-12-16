<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Comment Collection
 *
 * Holds a collection of comments
 */
class LocationCollection extends \Instagram\Collection\CollectionAbstract {

    /**
     * Set the collection data
     *
     * @param StdClass $raw_data
     * @access public
     */
    public function setData( $raw_data ) {
        $this->data = $raw_data->data;
        $this->convertData( '\Instagram\Location' );
    }

}