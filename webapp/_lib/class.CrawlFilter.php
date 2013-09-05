<?php
/**
 *
 * ThinkUp/webapp/_lib/class.CrawlFilter.php
 *
 * Copyright (c) 2013 Eduard Cucurella, Daniel Giribet
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
 *
 * CrawlFilter
 *
 * The object that manages ThinkUp parameters to filter the instances to crawl 
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella, Daniel Giribet
 * @author Eduard Cucurella <ecucurella[dot]t[at]tv3[dot]cat>
 * @author Daniel Giribet <dgiribet[dot]g[at]tv3[dot]cat>
 *
 */
class CrawlFilter {
    
    /**
     *  Set filter parameters
     *  @param Filter $filter
     *  @param Selected $selected
     */
    public static function setFilterParameters($filter,$selected) {
        SessionCache::put('filter',$filter);
        SessionCache::put('selected',$selected);        
    }

    /**
     *  Get filter parameter
     *  @return int Filter
     */
    public static function getFilter() {
        if (SessionCache::isKeySet('filter')) {
            return SessionCache::get('filter');
        } else {
            return -1;
        }
    }
    
    /**
     *  Get selected parameter
     *  @return int Selected
     */
    public static function getSelected() {
        if (SessionCache::isKeySet('selected')) {
            return SessionCache::get('selected');
        } else {
            return -1;
        }
    }

    /**
     * @return bool Is filter needed
     */
    public static function isFilterNeeded() {
        if (SessionCache::isKeySet('filter') && SessionCache::isKeySet('selected')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Unset filter when used
     */
    public static function setFilterUsed() {
        SessionCache::unsetKey('filter');
        SessionCache::unsetKey('selected');
    }
    
}
