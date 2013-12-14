<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram;

use \Instagram\Collection\MediaCollection;

/**
 * Location class
 *
 * Some media has a location associated to it. This location will have an ID and a name.
 * Some media has no location associated, but has a lat/lng. These location objects will return null or '' for certain method calls
 *
 * @see \Instagram\Instagram->getCurrentUser()
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/location.php}
 * {@link http://galengrover.com/projects/instagram/?example=location.php}
 */
class Location extends \Instagram\Core\BaseObjectAbstract {

    /**
     * Get location media
     *
     * Retrieve the recent media posted to a given location
     *
     * This can be paginated with the next_max_id param obtained from MediaCollection->getNext()
     *
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\Collection\MediaCollection
     * @access public
     */
    public function getMedia( array $params = null ) {
        return new MediaCollection( $this->proxy->getLocationMedia( $this->getApiId(), $params ), $this->proxy );
    }

    /**
     * Get location ID
     *
     * @return string|null
     * @access public
     */
    public function getId() {
        return isset( $this->data->id ) ? $this->data->id : null;
    }

    /**
     * Get location name
     *
     * @return string|null
     * @access public
     */
    public function getName() {
        return isset( $this->data->name ) ? $this->data->name : null;
    }

    /**
     * Get location longitude
     *
     * Get the longitude of the location
     *
     * @return string|null
     * @access public
     */
    public function getLat() {
        return isset( $this->data->latitude ) && is_float( $this->data->latitude ) ? $this->data->latitude : null;
    }

    /**
     * Get location latitude
     *
     * Get the latitude of the location
     *
     * @return string|null
     * @access public
     */
    public function getLng() {
        return isset( $this->data->longitude ) && is_float( $this->data->longitude ) ? $this->data->longitude : null;
    }

    /**
     * Magic toString method
     *
     * Returns the location's name
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->getName() ? $this->getName() : '';
    }

}