<?php
/**
 *
 * ThinkUp/tests/TestOfPlaceMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPlaceMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var PlaceMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao = new PlaceMySQLDAO();
    }

    protected function buildData() {
        $builders = array();
        $point = 'POINT(-0.159403 51.5424360)';
        $centroid = 'POINT(-0.159403 51.5424365)';
        $bounding_box = 'POLYGON((-0.213503 51.512805,-0.105303 51.512805,-0.105303 51.572068,-0.213503 51.572068,' .
             '-0.213503 51.512805))';
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testInsertPlaceBBoxOnly() {
        $place = array (
            'bounding_box' => array (
                'type' => 'Polygon',
                'coordinates' => array  (
        array (
        array(-97.73818308, 30.29930703),
        array(-97.710741, 30.29930703),
        array(-97.710741, 30.31480602),
        array(-97.73818308, 30.31480602),
        ))
        ),
                'country_code' => 'US',
                'country' => 'United States',
                'url' => 'http://api.twitter.com/1/geo/id/1a16a1d70500c27d.json',
                'name' => 'Hyde Park',
                'place_type' => 'neighborhood',
                'attributes' => array(),
                'id' => '1a16a1d70500c27d',
                'full_name' => 'Hyde Park, Austin'
                );
                $this->dao->insertPlace($place, 123456, 'twitter');
                $res = $this->dao->getPlaceByID('1a16a1d70500c27d');
                $this->assertEqual(sizeof($res), 12);
                $this->assertEqual($res['place_id'], '1a16a1d70500c27d');
                $this->assertEqual($res['name'], 'Hyde Park');
                $this->assertPattern('/POINT\(-97.72446/', $res['longlat']);
                $this->assertPattern('/ 30.3070565/', $res['longlat']);
                $this->assertEqual($res['bounding_box'],
                'POLYGON((-97.73818308 30.29930703,-97.710741 30.29930703,-97.710741 ' .
                '30.31480602,-97.73818308 30.31480602,-97.73818308 30.29930703))');
                $res = $this->dao->getPostPlace(123456);
                $this->assertEqual($res, null);
    }

    public function testInsertPlace() {
        $place1 = array (
            'bounding_box' => array (
            'type' => 'Polygon',
            'coordinates' => array  (
        array(
        array(-97.73818308, 30.29930703),
        array(-97.710741, 30.29930703),
        array(-97.710741, 30.31480602),
        array(-97.73818308, 30.31480602),
        ))
        ),
            'country_code' => 'US',
            'country' => 'United States',
            'url' => 'http://api.twitter.com/1/geo/id/1a16a1d70500c27d.json',
            'name' => 'Hyde Park',
            'place_type' => 'neighborhood',
            'attributes' => array(),
            'id' => '1a16a1d70500c27d',
            'full_name' => 'Hyde Park, Austin',
            'point_coords' => array(
            'type' => 'Point',
            'coordinates' => array(-97.723366,30.296095)
        )
        );
        $place2 = array (
            'bounding_box' => array (
            'type' => 'Polygon',
            'coordinates' => array  (
        array(
        array(-97.73818308, 30.29930703),
        array(-97.710741, 30.29930703),
        array(-97.710741, 30.31480602),
        array(-97.73818308, 30.31480602),
        ))
        ),
            'country_code' => 'US',
            'country' => 'United States',
            'url' => 'http://api.twitter.com/1/geo/id/1a16a1d70500c27d.json',
            'name' => 'Hyde Park',
            'place_type' => 'neighborhood',
            'attributes' => array(),
            'id' => '2a16a1d70500c27d',
            'full_name' => 'Hyde Park 2, Austin',
            'point_coords' => array(
            'type' => 'Point',
            'coordinates' => array(-97.723366,30.296095)
        )
        );
        $this->dao->insertPlace($place1, 123456, 'twitter');
        $this->dao->insertPlace($place2, 123457, 'twitter');
        $res = $this->dao->getPlaceByID('1a16a1d70500c27d');
        $this->assertEqual(sizeof($res), 12);
        $this->assertEqual($res['place_id'], '1a16a1d70500c27d');
        $this->assertEqual($res['name'], 'Hyde Park');
        $this->assertPattern('/POINT\(-97.72446/', $res['longlat']);
        $this->assertPattern('/ 30.3070565/', $res['longlat']);
        $this->assertEqual($res['bounding_box'], 'POLYGON((-97.73818308 30.29930703,-97.710741 30.29930703,'.
        '-97.710741 30.31480602,-97.73818308 30.31480602,-97.73818308 30.29930703))');

        $res = $this->dao->getPlaceByID('2a16a1d70500c27d');
        $this->assertEqual($res['place_id'], '2a16a1d70500c27d');

        $res = $this->dao->getPostPlace(123456);
        $this->assertEqual(sizeof($res), 5);
        $this->assertEqual($res['post_id'], 123456);
        $this->assertEqual($res['place_id'], '1a16a1d70500c27d');
        $this->assertEqual($res['longlat'], 'POINT(-97.723366 30.296095)');

        $res = $this->dao->getPostPlace(123457);
        $this->assertEqual($res['post_id'], 123457);
    }

    public function testInsertGenericPlace() {
        // Set all possible fields
        $places['id'] = 123;
        $places['place_type'] = "Park";
        $places['name'] = "A Park";
        $places['full_name'] = "The Greatest Park";
        $places['country_code'] = "UK";
        $places['country'] = "United Kingdom";
        $places['icon'] = "http://www.iconlocation.com";
        $places['lat_lng'] = 'POINT(51.514 -0.1167)';
        $places['bounding_box'] = 'POLYGON((-0.213503 51.512805,-0.105303 51.512805,-0.105303 51.572068,'.
         '-0.213503 51.572068, -0.213503 51.512805)))';
        $places['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $this->dao->insertGenericPlace($places, 1234, 'foursquare');
        // Get the place from the database
        $res = $this->dao->getPlaceByID('123');

        // Check all 12 fields were returned
        $this->assertEqual(sizeof($res), 12);
        // Check the place ID was set correctly
        $this->assertEqual($res['place_id'], '123');
        // Check the type was set correctly
        $this->assertEqual($res['place_type'], 'Park');
        // Check the name was set correctly
        $this->assertEqual($res['name'], 'A Park');
        // Check the fullname was set correctly
        $this->assertEqual($res['full_name'], 'The Greatest Park');
        // Check the country code was set correctly
        $this->assertEqual($res['country_code'], 'UK');
        // Check the country was set correctly
        $this->assertEqual($res['country'], 'United Kingdom');
        // Check the icon was set correctly
        $this->assertEqual($res['icon'], 'http://www.iconlocation.com');
        // Check the point was set correctly
        $this->assertPattern('/POINT\(51.514/', $res['longlat']);
        $this->assertPattern('/ -0.1167/', $res['longlat']);
        // Check the bounding box was set correctly
        $this->assertEqual($res['bounding_box'], 'POLYGON((-0.213503 51.512805,-0.105303 51.512805,-0.105303 51.572068,'.
         '-0.213503 51.572068,-0.213503 51.512805))');
        // Check the map image was set correctly
        $this->assertEqual($res['map_image'], 'http://www.mapimage.com');
    }

    public function testInsertPlacePointCoordsOnly() {
        $place = array (
            'point_coords' => array(
            'type' => 'Point',
            'coordinates' => array(-97.723366,30.296095)
        )
        );
        $this->dao->insertPlace($place, 123456, 'twitter');
        $res = $this->dao->getPostPlace(123456);
        $this->assertEqual(sizeof($res), 5);
        $this->assertEqual($res['post_id'], 123456);
        $this->assertEqual($res['place_id'], null);
        $this->assertEqual($res['longlat'], 'POINT(-97.723366 30.296095)');
    }

    //@TODO Add more tests now that the FixtureBuilder supports functions like Point()

    //See TestOfPostMySQLDAO for more PlaceMySQLDAO tests
}
