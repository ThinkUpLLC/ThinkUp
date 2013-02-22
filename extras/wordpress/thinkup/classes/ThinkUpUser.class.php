<?php
/**
 *
 * ThinkUp/extras/wordpress/thinkup/classes/ThinkUpUser.class.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * ThinkUp User
 * This class allows you to instantiate a user and retrieve their information from the ThinkUp user database table.
 *
 * @author Sam Rose
 */
class ThinkUpUser {

    /**
     * The username of the user.
     *
     * @var string
     */
    private $username;

    /**
     * The network that the user is on (e.g. Facebook, Twitter)
     *
     * @var string
     */
    private $network;

    /**
     * Cache variable for storing user info.
     *
     * @var object
     */
    private $user_info;

    /**
     * Constructor
     * @param str $username
     * @param str $network
     * @return ThinkUpUser
     */
    public function __construct($username, $network ='twitter') {
        if (!$username || $username == '') {
            $username = get_option('thinkup_twitter_username');
        }
        $this->username = $username;
        $this->network = $network;
    }

    /**
     * Returns the username of this user.
     *
     * @return string User's username
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Returns an object as returned by $wpdb->get_row() of the user's row in the ThinkUp tu_users database table.
     *
     * @return array
     */
    public function getUserInfo() {
        if (!$this->user_info) {
            $wpdb = ThinkUpWordPressPlugin::getDatabaseConnection();
            $options_array = ThinkUpWordPressPlugin::getOptionsArray();

            // database may be on same server but not same db as wordpress
            $db = $wpdb->escape($options_array['thinkup_db']['value']);
            $prefix = $options_array['thinkup_table_prefix']['value'];

            $sql = $wpdb->prepare("SELECT * FROM $db.{$prefix}users
                WHERE user_name='%s'
                    AND network='%s'", $this->username, $this->network);

            $this->user_info = $wpdb->get_row($sql);
        }
        return $this->user_info;
    }

    /**
     * Returns the user's ID.
     *
     * @return int
     */
    public function getUserID() {
        $user = $this->getUserInfo();
        return $user->user_id;
    }

    /**
     * Returns a string containing the network that this user belongs to.
     *
     * e.g. twitter, facebook
     *
     * @return str The network that this user belongs to.
     */
    public function getNetwork() {
        return $this->network;
    }

    /**
     * If the $text parameter is supplied, this function will return an anchor tagged link to this user's profile.
     *
     * If $text is not supplied, this function will return just a URL to this user's profile.
     *
     * This function is network aware.
     *
     * @param str $text
     * @return str
     */
    public function getLink($text = null) {
        if ($this->network == 'twitter') {
            if ($text) {
                return "<a href=\"http://twitter.com/{$this->getUsername()}".
                    "\">{$text}</a>";
            } else {
                return "http://twitter.com/{$this->getUsername()}/";
            }
        } else if ($this->network == 'facebook') {
            if ($text) {
                return "<a href=\"http://facebook.com/profile.php?id={$this->getUserID()}\">{$text}</a>";
            } else {
                return "http://facebook.com/profile.php?id={$this->getUserID()}";
            }
        } else {
            // not implemented yet
            return null;
        }
    }

    /**
     * Returns an array of objects (as returned by $wpdb->get_results()) of this user's recent posts.
     *
     * @param int $count Amount of rows to return.
     * @return ObjectArray
     */
    public function getRecentPosts($count = 15, $order = 'DESC') {
        $wpdb = ThinkUpWordPressPlugin::getDatabaseConnection();
        $options_array = ThinkUpWordPressPlugin::getOptionsArray();

        // database may be on same server but not same db as wordpress
        $db = $wpdb->escape($options_array['thinkup_db']['value']);
        $prefix = $options_array['thinkup_table_prefix']['value'];

        if ($count >= 0) {
            $sql = $wpdb->prepare("
                SELECT *
                FROM $db.".$prefix."posts
                WHERE author_username='%s'
                    AND in_reply_to_user_id is null
                    AND network='%s'
                ORDER BY pub_date {$wpdb->escape($order)}
                LIMIT %d", $this->username, $this->network, $count);
        }
        else {
            $sql = $wpdb->prepare("
                SELECT *
                FROM $db.".$prefix."posts
                WHERE author_username='%s'
                    AND in_reply_to_user_id is null
                    AND network='%s'
                ORDER BY pub_date {$wpdb->escape($order)}",
            $this->username, $this->network);
        }
        return $wpdb->get_results($sql);
    }

    /**
     * Returns an array of ThinkUpPost objects of this user's recent posts.
     *
     * @param int $count Amount of rows to return.
     * @return ObjectArray
     */
    public function getRecentPostsAsPosts($count = 15, $order = 'DESC') {
        $posts = $this->getRecentPosts($count, $order);
        $posts_as_posts = array();

        foreach ($posts as $post) {
            $posts_as_posts[] = new ThinkUpPost($post->post_id, $post->network);
        }
        return $posts_as_posts;
    }

    /**
     * Displays the recent posts for this user.
     *
     * @param int $count The number of posts to show.
     * @param str $order The order in which to display these posts.
     * @param array $atts An array of attributes passed by the shortcode that calls this function.
     * @return str Recent posts ready for display.
     */
    public function displayRecentPosts($count = 15, $order = 'desc', $atts = null) {
        if (!is_array($atts)) {
            $atts = ThinkUpShortcodeHandler::getShortcodeAtts();
        }

        return self::displayPosts(
        $this->getRecentPostsAsPosts($count, $order), $atts);
    }

    /**
     * Displays a list of posts passed as an array of ThinkUpPost objects in the $posts parameter.
     *
     * @param string $posts The posts to display as ThinkUpPost objects.
     * @param array $atts An array of attributes passed by the shortcode that calls this function.
     * @return string The posts ready for display.
     */
    public static function displayPosts($posts, $atts = null) {
        if (!is_array($atts)) {
            $atts = ThinkUpShortcodeHandler::getShortcodeAtts();
        }

        if ($posts) {
            $return = '';

            foreach ($posts as $post) {
                $return .= $post->getParsedContent($atts);
            }

            return $atts['before'].$return.$atts['after'];
        } else {
            return $atts['before']."No posts to display.".$atts['after'];
        }
    }

    /**
     * Returns an array of this user's posts in chronological order based on the $order variable. Returns posts
     * as ThinkUpPost objects.
     *
     * @param str $order Either 'ASC' or 'DESC'.
     * @return array An array of ThinkupPost objects.
     */
    public function getChronologicalArchive($order = 'DESC') {
        return $this->getRecentPostsAsPosts(-1, $order);
    }

    /**
     * Parses a list of special tokens such as #username# and #post_id# into values that can be used in the attributes.
     *
     * @param array $atts
     * @return array
     */
    private function parseSpecialTokens($atts) {
        // check if these atts have already been parsed
        if (isset($atts['special_tokens_parsed'])) {
            return $atts;
        }

        foreach ($atts as $key => $att) {
            $atts[$key] = str_replace('#username#', $this->getUsername(), $atts[$key]);
            $atts[$key] = str_replace('#userlink#', $this->getLink(), $atts[$key]);
        }

        // flag these atts is parsed
        $atts['special_tokens_parsed'] = true;
        return $atts;
    }

    /**
     * Formats the chronological posts ready for being displayed. Primarily for use by the chronological
     * archive shortcode and takes a lot of arguments as a result.
     *
     * @param str $order
     * @param array $atts The attributes passed by the shortcode function.
     * @return str
     */
    public function getFormattedChronologicalArchive($order = 'DESC', $atts = null) {

        $posts = $this->getChronologicalArchive($order);

        if (!is_array($atts)) {
            $atts = ThinkUpShortcodeHandler::getShortcodeAtts();
        }

        $output = '';

        if ($posts) {
            $modified_title = str_replace('#username#', $this->username, $atts['title']);
            $modified_title = str_replace('#userlink#', $this->getLink(), $modified_title);
            $output .= "{$atts['before']}";
            $output .= "{$modified_title}";
            foreach ($posts as $post) {
                if ($post->getPostID() != 0) {
                    $output .= $post->getParsedContent($atts);
                }
            }
            $output .= "{$atts['after']}";
        } else {
            $output .= "No posts found for this user.";
        }

        return $output;
    }
}
?>
