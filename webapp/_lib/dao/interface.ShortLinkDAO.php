<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.ShortLinkDAO.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Short Link Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
interface ShortLinkDAO {
    /**
     * Insert short link.
     * @param int $link_id
     * @param str $short_url
     * @return int Insert ID
     */
    public function insert($link_id, $short_url);

    /**
     * Get all short URLs captured in the past 48 hours.
     * @param str $bitly_url
     * @return array ShortLink objects
     */
    public function getLinksToUpdate($bitly_url);

    /**
     * Save click count for a short URL.
     * @param str $short_url
     * @param int $click_count
     * @return int Number of rows updated
     */
    public function saveClickCount($short_url, $click_count);

    /**
     * Get last 10 posts/short URLs with click counts greater than 0 for a given service user.
     * @param Instance $instance
     * @param int $limit How many rows to return
     * @return array post_text, short_url, click_count
     */
    public function getRecentClickStats(Instance $instance, $limit);

    /**
     * Check if user has any links with clicks posted on or before since_date minus last_x_days
     * @param Instance $instance
     * @param int $last_x_days
     * @param str $since Date in Y-m-d format, defaults to null (today)
     * @return bool
     */
    public function doesHaveClicksSinceDate(Instance $instance, $last_x_days, $since=null);

    /**
     * Get the average short link click total for the past X days from since_date
     * @param Instance $instance
     * @param $last_x_days
     * @param $since Date in Y-m-d format, defaults to null (today)
     * @return Mixed int or bool False if no average
     */
    public function getAverageClickCount(Instance $instance, $last_x_days, $since=null);

    /**
     * Get the highest short link click total for the past X days from since_date
     * @param Instance $instance
     * @param $last_x_days
     * @param $since Date in Y-m-d format, defaults to null (today)
     * @return Mixed int or bool False if no average
     */
    public function getHighestClickCount(Instance $instance, $last_x_days, $since=null);

    /**
     * Get the highest click count total for a given link ID.
     * @param $link_id
     * @return int Null if no clik count available
     */
    public function getHighestClickCountByLinkID($link_id);
}