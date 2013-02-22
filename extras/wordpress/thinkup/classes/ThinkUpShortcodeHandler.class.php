<?php
/**
 *
 * ThinkUp/extras/wordpress/thinkup/classes/ThinkUpShortcodeHandler.class.php
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
 *
 * ThinkUp Shorcode Handler
 * Parses shortcodes in blog posts.
 *
 * @author Sam Rose
 */
class ThinkUpShortcodeHandler {
    /**
     * Constructor of this class adds all of the shortcode hook/actions.
     * Instantiating this class serves only to hook its static methods to the correct shortcodes.
     * @return ThinkUpShortcodeHandler
     */
    public function __construct() {
        add_shortcode('thinkup_post_replies', array('ThinkUpShortcodeHandler', 'postReplies'));
        // backwards compatibility hook
        add_shortcode('thinkup_status_replies', array('ThinkUpShortcodeHandler', 'postReplies'));

        add_shortcode('thinkup_reply_count', array('ThinkUpShortcodeHandler', 'postReplyCount'));
        add_shortcode('thinkup_chronological_archive', array('ThinkUpShortcodeHandler', 'chronologicalArchive'));
    }

    /**
     * Hooked in to the shortcode api for the shortcode [thinkup_post_replies post_id="<post_id_here>"]
     *
     * @param array $atts
     * @return array
     */
    public static function postReplies($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_post_replies');

        $post = new ThinkUpPost($atts['post_id'], $atts['network']);

        return $post->getFormattedReplies($atts);
    }

    /**
     * Hooked in to the shortcode api for the shortcode [thinkup_reply_count post_id="<post_id_here>"]
     *
     * @param array $atts
     * @return array
     */
    public static function postReplyCount($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_reply_count');
        $post = new ThinkUpPost($atts['post_id'], $atts['network']);
        return $post->getFormattedReplyCount($atts);
    }

    /**
     * Hooked in to the shortcode api for the shortcode [thinkup_chronological_archive]
     *
     * @param array $atts
     * @return array
     */
    public static function chronologicalArchive($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_chronological_archive');
        $user = new ThinkUpUser($atts['username'], $atts['network']);
        return $user->getFormattedChronologicalArchive($atts['order'], $atts);
    }

    /**
     * Get the attributes with defaults for each type of shortcode.
     *
     * @param array $atts The $atts variable sent to the shortcode function.
     * @param string $shortcode The shortcode handle.
     * @return array The shortcode atts parsed and compared to the defaults.
     */
    public static function getShortcodeAtts($atts, $shortcode) {
        //create an array of default shortcode values
        $default_atts = array(
            'post_id' => 0,
            'network' => 'twitter',
            'username'=>get_option('thinkup_twitter_username'),
            'title'=> '<h3><a href="#userlink#">#username#</a>\'s Posts in Chronological Order (sans replies)</h3>',
            'before'=>'<br /><ul>',
            'after'=>'</ul>',
            'before_post'=>'<li>',
            'after_post'=>'</li>',
            'before_date'=>'<br /><small>',
            'after_date'=>'</small>',
            'before_user' => '<b>',
            'after_user' => ':</b> ',
            'date_format'=>'Y.m.d, g:ia',
            'gmt_offset'=>get_option('gmt_offset'),
            'order'=>'DESC');

        // fine tune the default shortcode atts for each shortcode
        switch($shortcode) {
            case 'thinkup_post_replies':
                $default_atts['title'] = '<h3>Public replies to #username#\'s post:</h3>#original_post#<br /><br />';
                break;
            case 'thinkup_chronological_archive':
                break;
            case 'thinkup_reply_count':
                $default_atts['before'] = '<a href="#postlink#">This post has ';
                $default_atts['after'] = ' replies</a>';
                break;
        }
        // return the atts
        return shortcode_atts($default_atts, $atts);
    }
}
?>
