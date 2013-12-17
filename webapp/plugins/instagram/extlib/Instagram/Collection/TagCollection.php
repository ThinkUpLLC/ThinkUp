<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Tag Collection
 *
 * Holds a collection of tags
 */
class TagCollection extends \Instagram\Collection\CollectionAbstract {

    /**
     * Set the collection data
     *
     * @param StdClass $raw_data
     * @access public
     */
    public function setData( $raw_data ) {
        if ( isset( $raw_data->data ) ) {
            $this->data = $raw_data->data;
        }
        elseif( is_array( $raw_data ) ) {
            $this->data = array_map( function( $t ){ return (object)array( 'name' => $t ); }, $raw_data );
        }
        $this->convertData( '\Instagram\Tag' );
    }

    /**
     * Get an array of tag names
     * 
     * @return array Returns an array of tags
     * @access public
     */
    public function toArray() {
        $tags = array();
        foreach( $this->data as $tag ) {
            $tags[] = $tag->getName();
        }
        return $tags;
    }

}