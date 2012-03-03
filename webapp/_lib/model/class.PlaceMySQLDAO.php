<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PlaceMySQLDAO.php
 *
 * Copyright (c) 2011-2012 Amy Unruh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2011-2012  Amy Unruh
 * @author Amy Unruh
 */
class PlaceMySQLDAO extends PDODAO implements PlaceDAO {
    public function insertPlace(array $place, $post_id, $network) {
        if (!$place) {
            return null;
        }
        $place_id = null;

        // if we have a place_id, then insert into tu_places
        if (isset($place['id'])) {
            $place_id = $place['id'];
            $this->logger->logDebug("processing place: " . $place_id, __METHOD__.','.__LINE__);
            $bounding_box = $place['bounding_box'];
            //@TODO check that type is polygon
            $coords = $bounding_box['coordinates'][0];

            // build bbox string
            $points = array();
            foreach ($coords as $coord) {
                $points[] = $coord[0] . " " . $coord[1];
            }
            // complete w/ first point again
            $points[] = $coords[0][0] . " " . $coords[0][1];
            $polystr = 'Polygon((' . join(',', $points) . '))';

            $q  = "INSERT IGNORE INTO #prefix#places ";
            $q .= "(place_id, place_type, name, full_name, country_code, country, network, bounding_box, longlat) ";
            $q .= "VALUES (:place_id, :place_type, :name, :full_name, :country_code, :country, :network, " .
                "PolygonFromText(:bounding_box), Centroid(PolygonFromText(:bounding_box)))";
            $vars = array(
                ':place_id' => (string)$place_id,
                ':place_type' => $place['place_type'],
                ':name' => $place['name'],
                ':full_name' => $place['full_name'],
                ':country_code' => $place['country_code'],
                ':country' => $place['country'],
                ':network' => $network,
                ':bounding_box' => $polystr
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

            $ps = $this->execute($q, $vars);
            $res = $this->getUpdateCount($ps);
        }

        // If point coords are set, add that information.
        // Include the place id if it was set; otherwise that field will be null.
        if (isset($place['point_coords']) && isset($place['point_coords']['coordinates']) && $post_id) {
            $point_coords = $place['point_coords'];
            //@TODO confirm that data is of type 'Point'
            $pcstr = 'Point(' . $point_coords['coordinates'][0] . ' ' . $point_coords['coordinates'][1] . ')';
            $q  = "INSERT IGNORE INTO #prefix#places_posts ";
            $q .= "(post_id, place_id, longlat, network) VALUES (";
            $q .= ":post_id, :place_id, PointFromText(:point), :network)";
            $vars = array(
                ':place_id' => (string)$place_id,
                ':post_id' => (string)$post_id,
                ':point' => $pcstr,
                ':network' => $network
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

            $ps = $this->execute($q, $vars);
            $res2 = $this->getUpdateCount($ps);
        }
    }

    public function insertGenericPlace(array $place, $network) {
        if (!$place) {
            return null;
        }
        $q  = "INSERT IGNORE INTO #prefix#places ";
        $q .= "(place_id, place_type, name, full_name, country_code, country, network, bounding_box, longlat, icon, ";
        $q .= "map_image) VALUES (:place_id, :place_type, :name, :full_name, :country_code, :country, :network, ";
        $q .= "PolygonFromText(:bounding_box), GeomFromText(:latlng), :icon, :map_image )";
        $vars = array(
            ':place_id' => isset($place['id']) ?  (string)$place['id'] : null,
            ':place_type' => isset($place['place_type']) ?  $place['place_type'] : null,
            ':name' => isset($place['name']) ? $place['name'] : null,
            ':full_name' => isset($place['full_name']) ? $place['full_name'] : null,
            ':country_code' => isset($place['country_code']) ? $place['country_code'] : null,
            ':country' => isset($place['country']) ? $place['country'] : null,
            ':network' => isset($network) ? $network : null,
            ':bounding_box' => isset($place['bounding_box']) ? $place['bounding_box'] : null,
            ':latlng' => isset($place['lat_lng']) ? $place['lat_lng'] : null,
            ':icon' => isset($place['icon']) ? $place['icon'] : null,
            ':map_image' => isset($place['map_image']) ? $place['map_image'] : null
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        return $res;
    }

    public function getPlaceByID($place_id) {
        $q = "SELECT id, place_id, place_type, name, full_name, country_code, country, network, icon, map_image, ";
        $q .= "AsText(longlat) AS longlat, AsText(bounding_box) AS bounding_box FROM #prefix#places ";
        $q .= "WHERE place_id = :place_id";
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, array( ':place_id' => $place_id));
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            return $row;
        } else {
            return null;
        }
    }

    public function getPostPlace($post_id, $network = 'twitter') {
        $q = "SELECT id, AsText(longlat) AS longlat, post_id, place_id, network FROM #prefix#places_posts " .
        "WHERE post_id = :post_id AND network = :network";

        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, array(
            ':post_id' => (string)$post_id,
            ':network' => $network));
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            return $row;
        } else {
            return null;
        }
    }
}
