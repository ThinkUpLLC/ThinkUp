<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Collection;

/**
 * Abstract Collection
 *
 * All Collections extend this class
 */
abstract class CollectionAbstract implements \IteratorAggregate, \ArrayAccess, \Countable {

    /**
     * Holds the pagination data for the collection
     *
     * @var StdClass
     * @access protected
     */
    protected $pagination;

    /**
     * Holds the data for the collection
     *
     * @var array
     * @access protected
     */
    protected $data = array();

    /**
     * Holds the position for the iterator
     *
     * @var integer
     * @access protected
     */
    protected $position;

    /**
     * Constructor
     *
     * Sets the data and child object's proxies
     *
     * @param StdClass $data Data from the API
     * @param \Instagram\Core\Proxy $proxy Proxy to pass on to teh collection's objects
     * @access public
     */
    public function __construct( $raw_data = null, \Instagram\Core\Proxy $proxy = null ) {
        if ( $raw_data ) {
            $this->setData( $raw_data );
        }
        if ( $proxy ) {
            $this->setProxies( $proxy );
        }
    }

    /**
     * Set the collections data
     *
     * @param StdClass $data Data from the API
     * @access public
     */
    public abstract function setData( $raw_data );

    /**
     * Get the collection's data
     *
     * @return StdClass
     * @access public
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get a collection item
     *
     * @param int $position Item to retrieve, starting at 0
     * @return mixed Returns the collection item at the position
     * @access public
     */
    public function getItem( $position ) {
        return isset( $this->data[$position] ) ? $this->data[$position] : null;
    }

    /**
     * Get a slice of the collection
     *
     * @param int $offset Where to start the slice
     * @param int $length Length of the slice
     * @return array Returns a slice of the array
     * @access public
     */
    public function getSlice( $offset, $length ) {
        return array_slice( $this->data, $offset, $length );
    }

    /**
     * Add data
     *
     * Add data from another collection the this collection
     *
     * @param \Instagram\Collection\CollectionAbstract $object Object to add the data of
     * @access public
     */
    public function addData( \Instagram\Collection\CollectionAbstract $object ) {
        $this->data = array_merge( $this->data, $object->getData() );
    }

    /**
     * Convert the collection's objects
     *
     * Child classes use this to turn the objects into the correct class
     *
     * @param string $object
     * @access protected
     */
    protected function convertData( $object ) {
        $this->data = array_map(
            function( $c ) use( $object ) {
                return new $object( $c );
            },
            $this->data
        );
    }

    /**
     * Set object proxies
     *
     * Sets all the child object's proxies
     *
     * @param \Instagram\Core\Proxy $proxy
     * @access public
     */
    public function setProxies( \Instagram\Core\Proxy $proxy ) {
        foreach( $this->data as $object ) {
            $object->setProxy( $proxy );
        }
    }

    /**
     * Implode the collection
     *
     * Implode the collection into a string
     *
     * Example - Get a media's tags into a comma delimited string
     *
     * $media->getTags()->implode(
     *     function( $t ){ return sprintf( '<a href="?example=tag.php&tag=%1$s">#%1$s</a>', $t ); }
     * )
     *
     * @param Closure $callback Function to run on the collection 
     * @param string $sep Implode separator
     * @access public
     */
    public function implode( \Closure $callback = null, $sep = ', ' ) {
        if ( !count( $this->getData() ) ) {
            return null;
        }
        if ( !$callback ) {
            $callback = function( $i ){ return $i->__toString(); };
        }
        foreach( $this->getData() as $item ) {
            $items[] = $callback( $item );
        }
        return implode( $sep, $items );
    }

    /**
     * IteratorAggregate
     *
     * {@link http://us2.php.net/manual/en/class.iteratoraggregate.php}
     */
    public function getIterator(){
        return new \ArrayIterator( $this->data );
    }

    /**
     * ArrayAccess
     *
     * {@link http://us2.php.net/manual/en/class.arrayaccess.php}
     */
    public function offsetExists( $offset ) {
        return isset( $this->data[$offset] );
    }
    public function offsetGet( $offset ) {
        return $this->data[$offset];
    }
    public function offsetSet( $offset, $value ) {
        trigger_error( "You can't set collection data");
    }
    public function offsetUnset( $offset ) {
        trigger_error( "You can't unset collection data" );
    }

    /**
     * Countable
     * 
     * {@link http://us2.php.net/manual/en/class.countable.php}
     */
    public function count() {
        return count( $this->data );
    }

}