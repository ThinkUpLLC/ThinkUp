<?php 
/*
 Plugin Name: ThinkTank Integration
 Plugin URI: http://thinktankapp.com
 Description: Displays ThinkTank data on your WordPress blog.
 Version: 0.5
 Author: Gina Trapani
 Author URI: http://ginatrapani.org
 */

/*  Copyright 2009  Gina Trapani  (email : ginatrapani at gmail dot com)
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// [thinktank_chronological_archive]
function thinktank_chron_archive_handler($atts) {

    extract(shortcode_atts(array('twitter_username'=>get_option('thinktank_twitter_username'), 'title'=>'<h3><a href="http://twitter.com/#twitter_username#/">@#twitter_username#</a>\'s Tweets in Chronological Order (sans replies)</h3>', 'before'=>'<br /><ul>', 'after'=>'</ul>', 'before_tweet'=>'<li>', 'after_tweet'=>'</li>', 'before_date'=>'', 'after_date'=>'', 'before_tweet_html'=>'', 'after_tweet_html'=>'', 'date_format'=>'Y.m.d, g:ia', 'gmt_offset'=>get_option('gmt_offset'), ), $atts));
    
    $options_array = thinktank_get_options_array();
    
    if ($options_array['thinktank_server']['value'] != '')
        $wpdb2 = new wpdb($options_array['thinktank_dbusername']['value'], $options_array['thinktank_dbpw']['value'], $options_array['thinktank_db']['value'], $options_array['thinktank_server']['value']);
    else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }
    
    $sql = $wpdb2->prepare("select pub_date, post_text, post_id from ".$options_array['thinktank_table_prefix']['value']."posts where author_username='%s' and in_reply_to_user_id is null  order by pub_date asc", $twitter_username);
    
    $tweets = $wpdb2->get_results($sql);
    
    if ($tweets) {
        echo str_replace('#twitter_username#', $twitter_username, $title);
        echo "{$before}";
        foreach ($tweets as $t) {
            $tweet_content = linkUrls($t->post_text);
            $tweet_content = linkTwitterUsers($tweet_content);
            echo "{$before_tweet}{$before_tweet_html}{$tweet_content}{$after_tweet_html} {$before_date}<a href=\"http://twitter.com/{$twitter_username}/statuses/{$t->post_id}/\">".actual_time($date_format, $gmt_offset, strtotime($t->pub_date))."</a>{$after_date}{$after_tweet}";
        }
        echo "{$after}";
    } else {
        echo "No tweets found in ThinkTank for {$twitter_username}.";
    }
}

// [thinktank_status_replies post_id="12345"]
function thinktank_replies_handler($atts) {

    extract(shortcode_atts(array('post_id'=>0, 'twitter_username'=>get_option('thinktank_twitter_username'), 'title'=>'<h3>Public Twitter replies to <a href="http://twitter.com/#twitter_username#/statuses/#post_id#/">@#twitter_username#\'s tweet</a>:</h3>', 'before'=>'<br /><ul>', 'after'=>'</ul>', 'before_tweet'=>'<li>', 'after_tweet'=>'</li>', 'before_user'=>'<b>', 'after_user'=>'</b>', 'before_tweet_html'=>'', 'after_tweet_html'=>'', 'date_format'=>'Y.m.d, g:ia', 'gmt_offset'=>8, ), $atts));
    
    $options_array = thinktank_get_options_array();
    
    if ($options_array['thinktank_server']['value'] != '')
        $wpdb2 = new wpdb($options_array['thinktank_dbusername']['value'], $options_array['thinktank_dbpw']['value'], $options_array['thinktank_db']['value'], $options_array['thinktank_server']['value']);
    else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }
    
    $sql = $wpdb2->prepare("select 
				t.*, u.*
			from 
				".$options_array['thinktank_table_prefix']['value']."posts t
			inner join 
				".$options_array['thinktank_table_prefix']['value']."users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_reply_to_post_id = %0.0f 
				AND u.is_protected = 0	
			order by 
				follower_count desc;", $post_id);
				
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
            $output .= "{$before_tweet}{$before_user}<a href=\"http://twitter.com/{$t->author_username}/statuses/{$t->post_id}/\">{$t->author_username}</a>{$after_user}: {$before_tweet_html}{$tweet_content}{$after_tweet_html}{$after_tweet}";
        }
        $output .= "{$after}";
    } else {
        $output .= "No replies found for status {$post_id}.";
    }
    return $output;
}

// [thinktank_status_reply_count post_id="12345"]
function thinktank_reply_count_handler($atts) {
    extract(shortcode_atts(array('post_id'=>0, 'before'=>'<a href="#permalink#">', 'after'=>' Twitter replies</a>', ), $atts));
    
    $options_array = thinktank_get_options_array();
    
    if ($options_array['thinktank_server']['value'] != '')
        $wpdb2 = new wpdb($options_array['thinktank_dbusername']['value'], $options_array['thinktank_dbpw']['value'], $options_array['thinktank_db']['value'], $options_array['thinktank_server']['value']);
    else {
        global $wpdb;
        $wpdb2 = $wpdb;
    }

    
    $sql = $wpdb2->prepare("select 
				count(*)
			from 
				".$options_array['thinktank_table_prefix']['value']."posts t
			inner join 
				".$options_array['thinktank_table_prefix']['value']."users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_reply_to_post_id=%0.0f
				AND u.is_protected = 0	
			order by 
				follower_count desc;", $post_id);
    //echo $sql;
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
    $text = preg_replace("/(^|\s)(([a-zA-Z]+:\/\/)([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"$2\">$2</a>", $text);
    
    /**
     * match www.something.domain/path/file.extension?some=variable&another=asf%
     * $1 is a possible space, this keeps us from linking href="[link]" etc
     * $2 is the whole URL that was matched.  The protocol is missing, so we assume http://
     * $3 is www.
     * $4 is the URL matched without the www.
     * $5 is the URL parameters
     */
    $text = preg_replace("/(^|\s)(www\.([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"http://$2\">$2</a>", $text);
    
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


function thinktank_menu() {
    add_options_page('ThinkTank Plug-in Options', 'ThinkTank', 6, __FILE__, 'thinktank_options');
}

function thinktank_get_options_array() {

    $arr = array('thinktank_twitter_username'=>array('key'=>'thinktank_twitter_username', 'label'=>'Default Twitter username:', 'description'=>'(Required) Override this by using the twitter_username parameter in the shortcode', 'type'=>'text', 'value'=>get_option('thinktank_twitter_username')), 'thinktank_table_prefix'=>array('key'=>'thinktank_table_prefix', 'label'=>'ThinkTank table prefix:', 'description'=>'(Optional) For example <i>tt_</i>', 'type'=>'text', 'value'=>get_option('thinktank_table_prefix')), 'thinktank_server'=>array('key'=>'thinktank_server', 'label'=>'ThinkTank database server:', 'description'=>'(Optional) If ThinkTank is located in a different database than WordPress', 'type'=>'text', 'value'=>get_option('thinktank_server')), 'thinktank_db'=>array('key'=>'thinktank_db', 'label'=>'ThinkTank database name:', 'description'=>'(Optional) If ThinkTank is located in a different database than WordPress', 'type'=>'text', 'value'=>get_option('thinktank_db')), 'thinktank_dbusername'=>array('key'=>'thinktank_dbusername', 'label'=>'ThinkTank database username:', 'description'=>'(Optional) If ThinkTank is located in a different database than WordPress', 'type'=>'text', 'value'=>get_option('thinktank_dbusername')), 'thinktank_dbpw'=>array('key'=>'thinktank_dbpw', 'label'=>'ThinkTank database password:', 'description'=>'(Optional) If ThinkTank is located in a different database than WordPress', 'type'=>'password', 'value'=>thinktank_unscramble_password(get_option('thinktank_dbpw'))));
    return $arr;
    
}

//Don't want to store passwords in plaintext in the database
//This isn't perfect but it's better than clear text
function thinktank_scramble_password($password) {
    $salt = substr(str_pad(dechex(mt_rand()), 8, '0', STR_PAD_LEFT), -8);
    $modified = $password.$salt;
    $secured = $salt.base64_encode(bin2hex(strrev(str_rot13($modified))));
    return $secured;
}

function thinktank_unscramble_password($stored_password) {
    $salt = substr($stored_password, 0, 8);
    $modified = substr($stored_password, 8, strlen($stored_password) - 8);
    $modified = str_rot13(strrev(pack("H*", base64_decode($modified))));
    $password = substr($modified, 0, strlen($modified) - 8);
    return $password;
}

function thinktank_options() {
    // variables for the field and option names
    $options_hidden_field_name = 'thinktank_submit_hidden';
    
    $options_array = thinktank_get_options_array();
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if ($_POST[$options_hidden_field_name] == 'Y') {
    
        foreach ($options_array as $opt) {
            // Read their posted value
            $opt['value'] = $_POST[$opt['key']];
            // Save the posted value in the database
            
            if ($opt['key'] == 'thinktank_dbpw')
                update_option($opt['key'], thinktank_scramble_password($opt['value']));
            else
                update_option($opt['key'], $opt['value']);
        }
        // Put an options updated message on the screen
        
?>
<div class="updated">
    <p>
        <strong><?php _e('Options saved.', 'mt_trans_domain'); ?></strong>
    </p>
</div>
<?php 
}
// Now display the options editing screen
echo '<div class="wrap">';
// header
echo "<h2>".__('ThinkTank Plugin Options', 'mt_trans_domain')."</h2>";
// options form
?>
<form name="form1" method="post" action="">
    <input type="hidden" name="<?php echo $options_hidden_field_name; ?>" value="Y">
    <table>
        <?php 
        foreach ($options_array as $opt) {
            if ($opt['key'] == 'thinktank_dbpw')
                $field_value = thinktank_unscramble_password(get_option($opt['key']));
            else
                $field_value = get_option($opt['key']);
            
        ?>
        <tr>
            <td align="right" valign="top">
                <?php _e($opt['label'], 'mt_trans_domain'); ?>
            </td>
            <td>
                <input type="<?php echo $opt['type']; ?>" name="<?php echo $opt['key'] ?>" value="<?php echo $field_value ?>" size="20">
                <br/>
                <small>
                    <?php echo $opt['description']; ?>
                </small>
            </td>
        </tr>
        <?php } ?>
    </table>
    <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
    </p>
</form>
</div>
<?php 
}

add_action('admin_menu', 'thinktank_menu');

add_shortcode('thinktank_chronological_archive', 'thinktank_chron_archive_handler');
add_shortcode('thinktank_status_replies', 'thinktank_replies_handler');
add_shortcode('thinktank_reply_count', 'thinktank_reply_count_handler');


?>
