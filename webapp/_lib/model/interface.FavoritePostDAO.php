<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.FavoritePostDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Amy Unruh
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
 *
 * FavoritePost Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Amy Unruh
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Amy Unruh
 *
 */
interface FavoritePostDAO extends PostDAO {
    /**
     * Inserts the given post record (if it does not already exist), then creates a row in the favorites 'join' table
     * to store information about the 'favorited' relationship. $vals holds the parsed post information.
     * @param int $favoriter_id
     * @param array $vals
     * @return int
     */
    public function addFavorite($favoriter_id, $vals);
    /**
     * 'Unfavorites' a post with respect to a given user, by removing the relevant entry from
     * the favorites table.
     * @param int $tid
     * @param int $uid
     * @param str $network
     * @return int
     */
    public function unFavorite($tid, $uid, $network);
    /**
     * Wrapper function for getAllFavoritePostsByUserID. Supports pagination.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @return array Posts with link object set
     */
    public function getAllFavoritePosts($owner_id, $network, $count, $page);
    /**
     * Wrapper function for getAllFavoritePostsByUserID. Takes an 'upper bound' argument ($ub)-- if set,
     * only posts with id < $ub are retrieved.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param int $ub
     * @return array Posts with link object set
     */
    public function getAllFavoritePostsUpperBound($owner_id, $network, $count, $ub);
    /**
     * wrapper function for getAllFavoritePostsByUsernameOrderedBy
     * @param str $username
     * @param str $network
     * @param int $count
     * @return array Posts with link object set
     */
    public function getAllFavoritePostsByUsername($username, $network, $count);
    /**
     * iterator wrapper for getAllFavoritePostsByUsernameOrderedBy
     * @param str $username
     * @param str $network
     * @param int $count
     * @return PostIterator
     */
    public function getAllFavoritePostsByUsernameIterator($username, $network, $count);
    /**
     * iterator wrapper for getAllFavoritePostsByUserID
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return PostIterator
     */
    public function getAllFavoritePostsIterator($user_id, $network, $count);
}