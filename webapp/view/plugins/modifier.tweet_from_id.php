<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty Tweet from ID plugin
 *
 * Type:     modifier<br>
 * Name:     tweet_from_id<br>
 * Date:     March 9, 2010
 * Purpose:  Converts a tweet id into a full Tweet object.
 * Input:    status id
 * Example:  {$tweet->in_reply_to_id|tweet_from_id}
 * @author   Thomas Woodham
 * @version 1.0
 * @param integer
 * @return object
 */
function smarty_modifier_tweet_from_id($status_id) {
    $post_dao = DAOFactory::getDAO('PostDAO');
    if( $status_id > 0 ){
        $tweet = $post_dao->getPost( $status_id );
    } else {
        $tweet = new Post( array( 'id' => 0, 'status_id' => 0 ) );
    }
    return $tweet;
}
?>