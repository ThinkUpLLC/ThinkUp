<?php
/**
 * Location Data Access Object Interface
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

interface LocationDAO {
    /**
     * Returns a given 'location' existing in storage.
     * @param str $location
     * @return array Details of Location
     */
    public function getLocation($location);
    
    /**
     * Adds a location to DB
     * @param array Details of Location
     * @return int update count
     */
    public function addLocation($vals);
    
    /**
     * Returns all locations in table
     * @return array Details of Locations
     */
    public function getAllLocations();
}