<?php
/*
 Plugin Name: ThinkUp Integration
 Plugin URI: http://thinkupapp.com
 Description: Displays ThinkUp data on your WordPress blog.
 Version: 0.5
 Author: Gina Trapani
 Author URI: http://ginatrapani.org
 */

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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */

// [thinkup_chronological_archive]
function thinkup_chron_archive_handler($atts) {

    extract(shortcode_atts(array('twitter_username'=>get_option('thinkup_twitter_username'), 'title'=>
    '<h3><a href="http://twitter.com/#twitter_username#/">@#twitter_username#</a>\'s Tweets in Chronological Order '.
    '(sans replies)</h3>', 'before'=>'<br /><ul>', 'after'=>'</ul>', 'before_tweet'=>'<li>', 
    'before_tweet_alt'=>'<li class="alt">', 'after_tweet'=>'</li>', 
    'before_date'=>'', 'after_date'=>'', 'before_tweet_html'=>'', 'after_tweet_html'=>'', 'date_format'=>'Y.m.d, g:ia',
    'gmt_offset'=>get_option('gmt_offset'), 'order'=>'desc', ), $atts));

    $options_array = thinkup_get_options_array();

    if ($options_array['thinkup_server']['value'] != '') {
        $wpdb2 = new wpdb($options_array['thinkup_dbusername']['value'], $options_array['thinkup_dbpw']['value'],
        $options_array['thinkup_db']['value'], $options_array['thinkup_server']['value']);
    } else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }

    $sql = $wpdb2->prepare("select pub_date, post_text, post_id from ".$options_array['thinkup_table_prefix']['value'].
    "posts where author_username='%s' and in_reply_to_user_id is null order by pub_date ".$order, $twitter_username);
    $tweets = $wpdb2->get_results($sql);

    if ($tweets) {
        echo str_replace('#twitter_username#', $twitter_username, $title);
        echo "{$before}";

        $cur = 0;
        foreach ($tweets as $t) {
            $tweet_content = htmlentities ($t->post_text);
            $tweet_content = linkUrls($tweet_content);
            $tweet_content = linkTwitterUsers($tweet_content);
            echo "{$before_tweet}{$before_tweet_html}{$tweet_content}{$after_tweet_html} {$before_date}";
            if ($cur % 2) {
                echo $before_tweet;
            } else {
                echo $before_tweet_alt;
            }
            echo "{$after_tweet_html} {$before_date}
            <a href=\"http://twitter.com/{$twitter_username}/statuses/{$t->post_id}/\">".
            actual_time($date_format, $gmt_offset, strtotime($t->pub_date))."</a>{$after_date}{$after_tweet}";
            $cur++;
        }
        echo "{$after}";
    } else {
        echo "No tweets found in ThinkUp for {$twitter_username}.";
    }
}

// [thinkup_status_replies post_id="12345"]
function thinkup_replies_handler($atts) {

    extract(shortcode_atts(array('post_id'=>0, 'network'=>'twitter',
    'twitter_username'=>get_option('thinkup_twitter_username'), 'title'=>'<h3>Public Twitter replies to '.
    '<a href="http://twitter.com/#twitter_username#/statuses/#post_id#/">@#twitter_username#\'s tweet</a>:</h3>',
    'before'=>'<br /><ul>', 'after'=>'</ul>', 'before_tweet'=>'<li>', 'after_tweet'=>'</li>', 'before_user'=>'<b>',
    'after_user'=>'</b>', 'before_tweet_html'=>'', 'after_tweet_html'=>'', 'date_format'=>'Y.m.d, g:ia', 
    'gmt_offset'=>8, ), $atts));

    $options_array = thinkup_get_options_array();

    if ($options_array['thinkup_server']['value'] != '') {
        $wpdb2 = new wpdb($options_array['thinkup_dbusername']['value'], $options_array['thinkup_dbpw']['value'],
        $options_array['thinkup_db']['value'], $options_array['thinkup_server']['value']);
    }else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }

    $sql = $wpdb2->prepare("select
                p.*, u.*
            from 
                ".$options_array['thinkup_table_prefix']['value']."posts p
            inner join 
                ".$options_array['thinkup_table_prefix']['value']."users u 
            on 
                p.author_user_id = u.user_id 
            where 
                in_reply_to_post_id = %0.0f 
                AND p.network = '%s'
                AND p.is_protected = 0    
            order by 
                follower_count desc;", $post_id, $network);

    $replies = $wpdb2->get_results($sql);

    $output = '';
    if ($replies) {
        $modified_title = str_replace('#twitter_username#', $twitter_username, $title);
        $modified_title = str_replace('#post_id#', $post_id, $modified_title);
        $output .= "{$before}";
        $output .= "{$modified_title}";
        foreach ($replies as $t) {
            $tweet_content = strip_starter_username($t->post_text);
            $tweet_content = linkUrls($tweet_content);
            $tweet_content = linkTwitterUsers($tweet_content);
            $output .= "{$before_tweet}{$before_user}<a href=\"http://twitter.com/{$t->author_username}/statuses/".
            "{$t->post_id}/\">{$t->author_username}</a>{$after_user}: {$before_tweet_html}{$tweet_content}".
            "{$after_tweet_html}{$after_tweet}";
        }
        $output .= "{$after}";
    } else {
        $output .= "No replies found for status {$post_id}.";
    }
    return $output;
}

// [thinkup_status_reply_count post_id="12345"]
function thinkup_reply_count_handler($atts) {
    extract(shortcode_atts(array('post_id'=>0, 'before'=>'<a href="#permalink#">', 'after'=>' Twitter replies</a>',
    'network'=>'twitter', ), $atts));

    $options_array = thinkup_get_options_array();

    if ($options_array['thinkup_server']['value'] != '') {
        $wpdb2 = new wpdb($options_array['thinkup_dbusername']['value'], $options_array['thinkup_dbpw']['value'],
        $options_array['thinkup_db']['value'], $options_array['thinkup_server']['value']);
    } else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }


    $sql = $wpdb2->prepare("select
                count(*)
            from 
                ".$options_array['thinkup_table_prefix']['value']."posts p
            inner join 
                ".$options_array['thinkup_table_prefix']['value']."users u 
            on 
                p.author_user_id = u.user_id 
            where 
                p.in_reply_to_post_id=%0.0f 
                AND p.network = '%s' 
                AND p.is_protected = 0    
            order by 
                follower_count desc;", $post_id, $network);

    $count = $wpdb2->get_var($sql);
    $before_mod = str_replace('#permalink#', get_permalink(), $before);
    return "{$before_mod}{$count}{$after}";
}

function linkTwitterUsers($text) {
    $text = preg_replace('/(^|\s)@(\w*)/i', '$1<a href="http://twitter.com/$2" class="twitter-user">@$2</a>', $text);
    return $text;
}

function linkUrls($text) {
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

function actual_time($format, $offset, $timestamp) {
    //Offset is in hours from gmt, including a - sign if applicable.
    //So lets turn offset into seconds
    $offset = $offset * 60 * 60;
    $timestamp = $timestamp + $offset;
    //Remember, adding a negative is still subtraction ;)
    return gmdate($format, $timestamp);
}

function strip_starter_username($text) {
    return preg_replace("/^@[a-zA-Z0-9_]+/", '', $text);
}


function thinkup_menu() {
    add_options_page('ThinkUp Plug-in Options', 'ThinkUp', 6, __FILE__, 'thinkup_options');
}

function thinkup_get_options_array() {

    $arr = array('thinkup_twitter_username'=>array('key'=>'thinkup_twitter_username',
    'label'=>'Default Twitter username:', 'description'=>'(Required) Override this by using the twitter_username '.
    'parameter in the shortcode', 'type'=>'text', 'value'=>get_option('thinkup_twitter_username')), 
    'thinkup_table_prefix'=>array('key'=>'thinkup_table_prefix', 'label'=>'ThinkUp table prefix:', 
    'description'=>'(Optional) For example <i>tu_</i>', 'type'=>'text', 'value'=>get_option('thinkup_table_prefix')), 
    'thinkup_server'=>array('key'=>'thinkup_server', 'label'=>'ThinkUp database server:', 
    'description'=>'(Optional) If ThinkUp is located in a different database than WordPress', 'type'=>'text', 
    'value'=>get_option('thinkup_server')), 'thinkup_db'=>array('key'=>'thinkup_db', 'label'=>'ThinkUp database name:',
    'description'=>'(Optional) If ThinkUp is located in a different database than WordPress', 'type'=>'text', 
    'value'=>get_option('thinkup_db')), 'thinkup_dbusername'=>array('key'=>'thinkup_dbusername',
    'label'=>'ThinkUp database username:', 'description'=>'(Optional) If ThinkUp is located in a different database '.
    'than WordPress', 'type'=>'text', 'value'=>get_option('thinkup_dbusername')), 'thinkup_dbpw'=>array(
    'key'=>'thinkup_dbpw', 'label'=>'ThinkUp database password:', 'description'=>
    '(Optional) If ThinkUp is located in a different database than WordPress', 'type'=>'password', 
    'value'=>thinkup_unscramble_password(get_option('thinkup_dbpw'))));
    return $arr;

}

//Don't want to store passwords in plaintext in the database
//This isn't perfect but it's better than clear text
function thinkup_scramble_password($password) {
    $salt = substr(str_pad(dechex(mt_rand()), 8, '0', STR_PAD_LEFT), -8);
    $modified = $password.$salt;
    $secured = $salt.base64_encode(bin2hex(strrev(str_rot13($modified))));
    return $secured;
}

function thinkup_unscramble_password($stored_password) {
    $salt = substr($stored_password, 0, 8);
    $modified = substr($stored_password, 8, strlen($stored_password) - 8);
    $modified = str_rot13(strrev(pack("H*", base64_decode($modified))));
    $password = substr($modified, 0, strlen($modified) - 8);
    return $password;
}

function thinkup_options() {
    // variables for the field and option names
    $options_hidden_field_name = 'thinkup_submit_hidden';

    $options_array = thinkup_get_options_array();

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if ($_POST[$options_hidden_field_name] == 'Y') {

        foreach ($options_array as $opt) {
            // Read their posted value
            $opt['value'] = $_POST[$opt['key']];
            // Save the posted value in the database

            if ($opt['key'] == 'thinkup_dbpw')
            update_option($opt['key'], thinkup_scramble_password($opt['value']));
            else
            update_option($opt['key'], $opt['value']);
        }
        // Put an options updated message on the screen

        ?>
<div class="updated">
<p><strong><?php _e('Options saved.', 'mt_trans_domain'); ?></strong></p>
</div>
        <?php
    }
    // Now display the options editing screen
    echo '<div class="wrap">';
    // header
    echo "<h2>".__('ThinkUp Plugin Options', 'mt_trans_domain')."</h2>";
    // options form
    ?>
<form name="form1" method="post" action=""><input type="hidden"
    name="<?php echo $options_hidden_field_name; ?>" value="Y">
<table>
<?php
foreach ($options_array as $opt) {
    if ($opt['key'] == 'thinkup_dbpw')
    $field_value = thinkup_unscramble_password(get_option($opt['key']));
    else
    $field_value = get_option($opt['key']);

    ?>
    <tr>
        <td align="right" valign="top"><?php _e($opt['label'], 'mt_trans_domain'); ?>
        </td>
        <td><input type="<?php echo $opt['type']; ?>"
            name="<?php echo $opt['key'] ?>" value="<?php echo $field_value ?>"
            size="20"> <br />
        <small> <?php echo $opt['description']; ?> </small></td>
    </tr>
    <?php } ?>
</table>
<p class="submit"><input type="submit" name="Submit"
    value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" /></p>
</form>
</div>
    <?php
}

add_action('admin_menu', 'thinkup_menu');

add_shortcode('thinkup_chronological_archive', 'thinkup_chron_archive_handler');
add_shortcode('thinkup_status_replies', 'thinkup_replies_handler');
add_shortcode('thinkup_reply_count', 'thinkup_reply_count_handler');
?>