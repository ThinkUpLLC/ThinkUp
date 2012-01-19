<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterInstance.php
 *
 * Copyright (c) 2011-2012 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Gina Trapani
 *
 * Twitter Instance
 *
 * Twitter plugin's instance metadata.
 */
class TwitterInstance extends Instance {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Last page of replies fetched for this instance.
     */
    var $last_page_fetched_replies;
    /**
     * @var int Last page of tweets fetched for this instance.
     */
    var $last_page_fetched_tweets;
    /**
     * @var int Last favorite post ID of the instance saved.
     */
    var $last_favorite_id;
    /**
     * @var int Last page of older favorites checked for backfilling.
     */
    var $last_unfav_page_checked;
    /**
     * @var int Last page of favorites fetched.
     */
    var $last_page_fetched_favorites;
    public function __construct($row = false) {
        parent::__construct($row);
        if ($row) {
            $this->id = $row['id'];
            $this->last_page_fetched_replies = $row['last_page_fetched_replies'];
            $this->last_page_fetched_tweets = $row['last_page_fetched_tweets'];
            $this->last_favorite_id = $row['last_favorite_id'];
            $this->last_unfav_page_checked = $row['last_unfav_page_checked'];
            $this->last_page_fetched_favorites = $row['last_page_fetched_favorites'];
        }
    }
}