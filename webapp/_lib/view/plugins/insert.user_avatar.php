<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/insert.user_avatar.php
 *
 * Copyright (c) 2015 Gina Trapani
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
 */
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Insert user avatar with image proxy if one is available.
 *
 * Type:     insert
 * Name:     user_avatar
 * Date:     January 20, 2015
 * Purpose:  Returns user avatar URL
 * Input:    avatar_url, image_proxy_sig
 * Example:  {insert name="user_avatar" avatar_url="http://example.com/example.jpg" "image_proxy_sig"="abc"}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Mark Wilkie
 * @version 1.0
 */
function smarty_insert_user_avatar($params, &$smarty) {
    if (empty($params['avatar_url'])) {
        trigger_error("Missing 'avatar_url' parameter");
        return;
    } else {
        $avatar_url_https = preg_replace('/^http:(.+)$/', "https:$1", $params['avatar_url']);
        if (!empty($params['avatar_size']) && $params['avatar_size'] == 'original') {
            //Get the original version of the avatar
            //https://dev.twitter.com/overview/general/user-profile-images-and-banners
            $avatar_url_https = str_replace('_normal', '', $avatar_url_https);
        } else {
            //Get the bigger version of the avatar
            //https://dev.twitter.com/overview/general/user-profile-images-and-banners
            $avatar_url_https = str_replace('_normal', '_bigger', $avatar_url_https);
        }
        if (!empty($params['image_proxy_sig'])) {
            return 'https://www.thinkup.com/join/img.php?url='.$avatar_url_https
                ."&t=avatar&s=".$params['image_proxy_sig'];
        } else {
            return $avatar_url_https;
        }
    }
}
