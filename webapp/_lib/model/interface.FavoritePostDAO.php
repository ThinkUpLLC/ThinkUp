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

    
    public function addFavorite($favoriter_id, $vals);
    public function unFavorite($tid, $uid, $network);
    
    public function getAllFPosts($owner_id, $network, $count, $page);
    public function getAllFPostsUB($owner_id, $network, $count, $ub);
    public function getAllFPostsByUsername($username, $network, $count, $page);
    
    public function getAllFPostsByUsernameIterator($owner_id, $network, $count);
    public function getAllFPostsIterator($user_id, $network, $count, $include_replies);

}
