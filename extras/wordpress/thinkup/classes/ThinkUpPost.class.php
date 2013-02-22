<?php
/**
 *
 * ThinkUp/extras/wordpress/thinkup/classes/ThinkUpPost.class.php
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
 * ThinkUp Post
 * Represents a single post in the ThinkUp datastore.
 *
 * @author Sam Rose
 */
class ThinkUpPost {

    /**
     * The unique identifier for the post.
     *
     * @var int/long The id of the post.
     */
    private $post_id;

    /**
     * The network to which this post belongs.
     *
     * @var string
     */
    private $network;

    /**
     * Stores the post's information. Is undefined until a call to getPostInfo() is made. Don't reference it directly.
     *
     * @var Object
     */
    private $post_info;

    /**
     * Cache variable for the user/url parsed post content.
     *
     * @var string
     */
    private $parsed_post_text;

    /**
     * The prepared sql query to get the ost info from the database for this post.
     *
     * @var PreparedSQLQuery
     */
    private $get_post_info_sql;

    /**
     * The prepared sql query to get the reply count from the database for this post.
     *
     * @var PreparedSQLQuery
     */
    private $get_reply_count_sql;

    /**
     * The prepared sql query to get the replies from the database for this post.
     *
     * @var PreparedSQLQuery
     */
    private $get_replies_sql;

    /**
     * Pass in the post_id to get information about a post.
     *
     * Note: I wanted validate that the post ID passed in is actually a valid numeric value but it's more difficult
     * than originally perceived. The is_int() function is dependent on the host environment. If they are only working
     *  in a 32-bit system then it will not evaluate numbers larger than 2^32-1 properly. Suggestions welcome.
     *
     * http://www.php.net/manual/en/function.is-int.php
     *
     * @param int $post_id
     * @param str $network "twitter" or "facebook"
     */
    public function __construct($post_id, $network = 'twitter') {
        $this->post_id = $post_id;
        $this->network = $network;
    }

    /**
     * Returns a formatted version of how many replies this post has, eg.
     *
     * "12 Twitter replies" for a Tweet with 12 replies.
     *
     * Uses the before and after variables.
     *
     * @return str
     */
    public function getFormattedReplyCount($atts) {
        $count = $this->getReplyCount();
        $atts = $this->parseSpecialTokens($atts);
        return "{$atts['before']}{$count}{$atts['after']}";
    }

    /**
     * Returns the number of replies this post has.
     *
     * @return int Number of replies to this post.
     */
    public function getReplyCount() {
        $wpdb = ThinkUpWordPressPlugin::getDatabaseConnection();
        $options_array = ThinkUpWordPressPlugin::getOptionsArray();

        if (!isset($this->get_reply_count_sql)) {
            // database may be on same server but not same db as wordpress
            $db = $wpdb->escape($options_array['thinkup_db']['value']);
            $prefix = $options_array['thinkup_table_prefix']['value'];

            $this->get_reply_count_sql = $wpdb->prepare("select
                count(*)
            from
            `$db`.`".$prefix."posts` p
            inner join
            `$db`.`".$prefix."users` u
            on
                p.author_user_id = u.user_id
            where
                p.in_reply_to_post_id={$wpdb->escape($this->post_id)}
                AND p.network = '%s'
                AND p.is_protected = 0
            order by
                follower_count desc;", $this->network);
        }
        return $wpdb->get_var($this->get_reply_count_sql);
    }

    /**
     * Returns an array of objects (as returned by wpdb->get_results()) containing information about posts that
     * reply to this one.
     *
     * @return ObjectArray
     */
    public function getReplies() {
        $wpdb2 = ThinkUpWordPressPlugin::getDatabaseConnection();
        if (!isset($this->get_replies_sql)) {
            $options_array = ThinkUpWordPressPlugin::getOptionsArray();
            // database may be on same server but not same db as wordpress
            $db = $wpdb2->escape($options_array['thinkup_db']['value']);
            $prefix = $options_array['thinkup_table_prefix']['value'];


            $this->get_replies_sql = $wpdb2->prepare("select
                p.*, u.*, p.network
            from
            `$db`.`".$prefix."posts` p
            inner join
            `$db`.`".$prefix."users` u
            on
                p.author_user_id = u.user_id
            where
                in_reply_to_post_id ={$wpdb2->escape($this->post_id)}
                AND p.network = '%s'
                AND p.is_protected = 0
            order by
                follower_count desc;", $this->network);
        }
        return $wpdb2->get_results($this->get_replies_sql);
    }

    /**
     * Returns the replies to this post as initialized ThinkUpPost objects.
     *
     * @return ThinkUpPost Array of ThinkUpPosts.
     */
    public function getRepliesAsPosts() {
        $replies = $this->getReplies();
        $return = array();

        foreach ($replies as $reply) {
            $return[] = new ThinkUpPost($reply->post_id, $reply->network);
        }

        return $return;
    }

    /**
     * Returns all replies to this post formatted ready for display.
     *
     * @param array $atts An array of shortcode attributes from the getShortcodeAtts function.
     * @return str
     */
    public function getFormattedReplies($atts) {
        $atts = $this->parseSpecialTokens($atts);
        $replies = $this->getRepliesAsPosts();
        $output = '';
        if ($replies) {
            $atts = $this->parseSpecialTokens($atts);

            $output .= "{$atts['before']}";
            $output .= "{$atts['title']}";
            foreach ($replies as $reply) {
                $output .= self::stripStarterUsername($reply->getParsedContent($atts));
            }
            $output .= "{$atts['after']}";
        } else {
            $output .= "No replies found for post ID {$this->post_id}.";
        }

        return $output;
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
            $atts[$key] = str_replace('#username#', $this->getAuthorUsername(), $atts[$key]);
            $atts[$key] = str_replace('#post_id#', $this->getPostID(), $atts[$key]);
            $atts[$key] = str_replace('#original_post#', $this->getLinkedContent(), $atts[$key]);
            $atts[$key] = str_replace('#postlink#', $this->getLink(), $atts[$key]);
        }

        // flag these atts is parsed
        $atts['special_tokens_parsed'] = true;
        return $atts;
    }

    /**
     * Returns this post's ID number.
     *
     * @return int/long This post's ID.
     */
    public function getPostID() {
        $post = $this->getPostInfo();
        return $this->post_id;
    }

    /**
     * Returns the raw text content of this post. No linked usernames or URLs.
     *
     * @return string Raw text content of this post.
     */
    public function getRawContent() {
        $post = $this->getPostInfo();
        return $post->post_text;
    }

    /**
     * Returns the text content of this post with usernames and URLs properly linked.
     *
     * @param array $atts An array of attributes passed to this function via the shortcode that calls it.
     * @return str Parsed text content of this post.
     */
    public function getParsedContent($atts) {
        $atts = $this->parseSpecialTokens($atts);
        $post = $this->getPostInfo();

        if (!isset($this->parsed_post_text)) {
            // string html entities
            $this->parsed_post_text = htmlentities($post->post_text);

            $this->parsed_post_text = self::linkUsers($this->parsed_post_text);

            // link URLs
            $this->parsed_post_text = self::linkUrls($this->parsed_post_text);
        }

        $date = self::actualTime($atts['date_format'], $atts['gmt_offset'], strtotime($post->pub_date));

        if ($this->network == "twitter") {
            $user = $this->getLink($this->getAuthorUsername());
        } else {
            $user = $this->getAuthorFullname();
        }

        return $atts['before_post'].$atts['before_user'].$user.$atts['after_user'].
        $this->parsed_post_text.$atts['before_date'].
        $date.$atts['after_date'].$atts['after_post'];
    }

    public function getLinkedContent() {
        return self::linkUsers(self::linkUrls($this->getRawContent()));
    }

    /**
     * Returns the username of this post's author.
     *
     * @return string Author's username.
     */
    public function getAuthorUsername() {
        $post = $this->getPostInfo();
        return $post->author_username;
    }

    /**
     * Returns the full name of this post's author.
     *
     * @return string Author's full name.
     */
    public function getAuthorFullName() {
        $post = $this->getPostInfo();
        return $post->author_fullname;
    }

    /**
     * Returns the author user ID of this post.
     * @return int
     */
    public function getAuthorUserID() {
        $post = $this->getPostInfo();
        return $post->author_user_id;
    }

    /**
     * Return an object (as returned by $wpdb->get_row()) of this post's database record.
     *
     * @return Object Post's database record.
     */
    public function getPostInfo() {
        if (!isset($this->post_info)) {
            $wpdb = ThinkUpWordPressPlugin::getDatabaseConnection();

            if (!isset($this->get_post_info_sql)) {
                $options_array = ThinkUpWordPressPlugin::getOptionsArray();

                // database may be on same server but not same db as wordpress
                $db = $wpdb->escape($options_array['thinkup_db']['value']);
                $prefix = $options_array['thinkup_table_prefix']['value'];

                $this->get_post_info_sql = $wpdb->prepare("
                     SELECT *
                     FROM
                     `$db`.`".$prefix."posts`
                     WHERE
                         post_id = {$wpdb->escape($this->post_id)}
                         AND network = %s;", $this->network);
            }
            $this->post_info = $wpdb->get_row($this->get_post_info_sql);
        }

        return $this->post_info;
    }

    /**
     * If this function is called with the $text parameter not equal to null then it will return a link to this post
     * in an anchor tag with the $text paramater as the text in between the tag.
     *
     * If this function is called without any parameters it will simply return a link to this post without any anchor
     * tag or formatting.
     *
     * @param str $text The text to go inside the anchor tag.
     * @return str A link to this post.
     */
    public function getLink($text = null) {
        if ($this->network == 'twitter') {
            if ($text) {
                return "<a href=\"http://twitter.com/{$this->getAuthorUsername()}/statuses/".
                    "{$this->getPostID()}/\">{$text}</a>";
            } else {
                return "http://twitter.com/{$this->getAuthorUsername()}/statuses/".
                    "{$this->getPostID()}/";
            }
        } else if ($this->network == 'facebook') {
            if ($text) {
                return "<a href=\"http://facebook.com/permalink.php?story_fbid={$this->getPostID()}&".
                "id={$this->getAuthorUserID()}\">{$text}</a>";
            } else {
                return "http://facebook.com/permalink.php?story_fbid={$this->getPostID()}&".
                "id={$this->getAuthorUserID()}";
            }
        }
    }

    /**
     * Returns a string containing the network that this post blongs to.
     *
     * e.g. twitter, facebook
     *
     * @return string The network this post is on.
     */
    public function getNetwork() {
        return $this->network;
    }

    /**
     * Parses the $text parameter and links all of the @ usernames to their appropriate Twitter profiles.
     *
     * @param str $text
     * @return str Tweet with @ usernames linked.
     */
    public static function linkUsers($text, $network = 'twitter') {
        if ($network == 'twitter') {
            $text = preg_replace( '/(^|\s)@(\w*)/i', '$1<a href="http://twitter.com/$2" class="twitter-user">@$2</a>',
            $text);
        } else {
            // no code for this yet
        }
        return $text;
    }

    /**
     * Parses the raw text links in a text string into their html anchor tagged equivalents.
     *
     * @param str $text Text to parse links.
     * @return str String with links linked.
     */
    public static function linkUrls($text) {
        /**
         * match protocol://address/path/file.extension?some=variable&another=asf%
         * $1 is a possible space, this keeps us from linking href="[link]" etc
         * $2 is the whole URL
         * $3 is protocol://
         * $4 is the URL without the protocol://
         * $5 is the URL parameters
         */
        $text = preg_replace("/(^|\s)(([a-zA-Z]+:\/\/)([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i",
        "$1<a href=\"$2\">$2</a>", $text);

        /**
         * match www.something.domain/path/file.extension?some=variable&another=asf%
         * $1 is a possible space, this keeps us from linking href="[link]" etc
         * $2 is the whole URL that was matched.  The protocol is missing, so we assume http://
         * $3 is www.
         * $4 is the URL matched without the www.
         * $5 is the URL parameters
         */
        $text = preg_replace("/(^|\s)(www\.([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i",
        "$1<a href=\"http://$2\">$2</a>", $text);

        return $text;
    }

    /**
     * Strips the first username in the post. For example, if a post
     * started with @samwhoo, this function would remove it.
     *
     * @param str $text
     * @return str Post without username at start.
     */
    public static function stripStarterUsername($text) {
        return preg_replace("/@([a-zA-Z0-9_])+/", '', $text, 1);
    }

    /**
     * Strips all usernames from the post.
     *
     * @param str $text
     * @return str Post without usernames.
     */
    public static function stripUsernames($text) {
        return preg_replace("/@([a-zA-Z0-9_])+/", '', $text);
    }

    /**
     * Get the formatted time with GMT offset figured in.
     *
     * @param str $format
     * @param int $offset
     * @param int $timestamp
     * @return Date
     */
    public static function actualTime($format, $offset, $timestamp) {
        //Offset is in hours from gmt, including a - sign if applicable.
        //So lets turn offset into seconds
        $offset = $offset * 60 * 60;
        $timestamp = $timestamp + $offset;
        //Remember, adding a negative is still subtraction ;)
        return gmdate($format, $timestamp);
    }
}
?>