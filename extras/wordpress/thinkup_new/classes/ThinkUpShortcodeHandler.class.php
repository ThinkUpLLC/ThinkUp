<?php
/**
 *
 * ThinkUp/extras/wordpress/thinkup/thinkup.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 */

/**
 * A class to handle the parsing of shortcodes in blog posts.
 *
 * @author Sam Rose
 */
class ThinkUpShortcodeHandler {
    /**
     * Constructor of this class adds all of the shortcode hook/actions.
     * Creating an instantiation of this class serves only to hook its
     * static methods to the correct shortcodes.
     */
    function __construct() {

        add_shortcode('thinkup_status_replies',
                array('ThinkUpShortcodeHandler', 'statusReplies'));
        add_shortcode('thinkup_reply_count',
                array('ThinkUpShortcodeHandler', 'statusReplyCount'));
        add_shortcode('thinkup_chronological_archive',
                array('ThinkUpShortcodeHandler', 'chronologicalArchive'));

    }

    /**
     * Hooked in to the shortcode api for the shortcode
     * [thinkup_status_replies post_id="<post_id_here>"]
     *
     * @param <type> $atts
     * @return <type>
     */
    public static function statusReplies($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_status_replies');

        $post = new ThinkUpPost($atts['post_id'], $atts['network']);

        return $post->getFormattedReplies($atts);
    }

    /**
     * Hooked in to the shortcode api for the shortcode
     * [thinkup_reply_count post_id="<post_id_here>"]
     *
     * @param <type> $atts
     * @return <type>
     */
    public static function statusReplyCount($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_status_reply_count');

        $post = new ThinkUpPost($atts['post_id'], $atts['network']);
        
        return $post->getFormattedReplyCount($atts);
    }

    /**
     * Hooked in to the shortcode api for the shortcode
     * [thinkup_chronological_archive]
     *
     * @param <type> $atts
     * @return <type>
     */
    public static function chronologicalArchive($atts) {
        $atts = self::getShortcodeAtts($atts, 'thinkup_chronological_archive');

        $user = new ThinkUpUser($atts['username'], $atts['network']);

        return $user->getFormattedChronologicalArchive($atts['order'], $atts);
    }

    /**
     * This function does all of the work for shortcodes. the shortcode default
     * array was getting large and copy pasted a lot so I decided to hide it
     * in here for added modularity.
     *
     * This function handles passing it to the shortcode_atts() WordPress
     * function.
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
            'title'=> '<h3><a href="#userlink#">#username#</a>\'s Posts in Chronological Order '.
    '(sans replies)</h3>',
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
            case 'thinkup_status_replies':
                $default_atts['title'] = '<h3>Public replies to
                    <a href="http://twitter.com/#username#/statuses/#post_id#/">
                    #username#\'s post</a>:</h3>#original_post#<br /><br />';
                break;

            case 'thinkup_chronological_archive':

                break;

            case 'thinkup_status_reply_count':
                $default_atts['before'] = '<a href="#postlink#">This post has ';
                $default_atts['after'] = ' replies</a>';
                break;
        }

        // return the atts 
        return shortcode_atts($default_atts, $atts);
    }
}
?>
