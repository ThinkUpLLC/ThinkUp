<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.FilteredInstanceDAO.php
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
 * Filtered Instance Data Access Object Interface
 * Used to add a filter to retrieved instances, which can be used to optimise or
 * paralelise crawling.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella, Daniel Giribet
 * @author Eduard Cucurella <ecucurella[dot]t[at]tv3[dot]cat>
 * @author Daniel Giribet <dgiribet[dot]g[at]tv3[dot]cat>
 *
 */
interface FilteredInstanceDAO {
    
    /**
     * Get SQL filter to add to instance query
     * @return str SQL filter
     */
    public function getCrawlFilter();
    
    /**
     * Establish if an instance query filter is required
     * @return bool true if filter is needed
     */
    public function hasCrawlFilter();
    
}
