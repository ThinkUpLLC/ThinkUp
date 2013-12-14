<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram;

/**
 * Helper class
 *
 * Example:
 *
 * $tags_closure = function($m){
 *     return sprintf( '<a href="?example=tag.php&tag=%s">%s</a>', $m[1], $m[0] );
 * };
 *
 * $mentions_closure = function($m){
 *    return sprintf( '<a href="?example=user.php&user=%s">%s</a>', $m[1], $m[0] );
 * };
 *
 * echo \Instagram\Helper::parseTagsAndMentions( $media->getCaption(), $tags_closure, $mentions_closure )
 */
class Helper {

    /**
     * Parse mentions in a string
     *
     * Finds mentions in a string (@mention) and applies a callback function each one
     *
     * @param string $text Text to parse
     * @param \Closure $callback Function to apply to each mention
     * @return string Returns the text after the callback have been applied to each mention
     * @access public
     */
    public static function parseMentions( $text, \Closure $callback ) {
        return preg_replace_callback( '~@(.+?)(?=\b)~', $callback, $text );
    }

    /**
     * Parse tags in a string
     *
     * Finds tags in a string (#username) and applies a callback function each one
     *
     * @param string $text Text to parse
     * @param \Closure $callback Function to apply to each tag
     * @return string Returns the text after the callback have been applied to each tag
     * @access public
     */
    public static function parseTags( $text, \Closure $callback ) {
        return preg_replace_callback( '~#(.+?)(?=\b)~', $callback, $text );
    }

    /**
     * Parse mentions and tags in a string
     *
     * Finds mentions and tags in a string (@mention, #tag) and applies a callback function each one
     *
     * @param string $text Text to parse
     * @param \Closure $tags_callback Function to apply to each tag
     * @param \Closure $mentions_callback Function to apply to each mention
     * @return string Returns the text after the callbacks have been applied to tags and mentions
     * @access public
     */
    public static function parseTagsAndMentions( $text, \Closure $tags_callback, \Closure $mentions_callback ) {
        $text = self::parseTags( $text, $tags_callback );
        $text = self::parseMentions( $text, $mentions_callback );
        return $text;
    }

    /**
     * Is the comment deletable
     *
     * Checks if a comment is deletable by checking if the current user posted the comment
     * or if the comment was added to one of the current user's media
     * 
     * @param  \Instagram\Comment $comment The comment
     * @param  \Instagram\Media $media The media the comment was added to
     * @param  \Instagram\CurrentUser $current_user Current user
     * @access public
     */
    public static function commentIsDeletable( \Instagram\Comment $comment, \Instagram\Media $media, \Instagram\CurrentUser $current_user ) {
        return
            $comment->getUser()->getId() == $current_user->getId() ||
            $media->getUser()->getId() == $current_user->getId();
    }

}