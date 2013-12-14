<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram;

use \Instagram\Comment;
use \Instagram\User;
use \Instagram\Location;
use \Instagram\Collection\CommentCollection;
use \Instagram\Collection\TagCollection;
use \Instagram\Collection\UserCollection;

/**
 * Media class
 *
 * @see \Instagram\Instagram->getLocation()
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/media.php}
 * {@link http://galengrover.com/projects/instagram/?example=media.php}
 */
class Media extends \Instagram\Core\BaseObjectAbstract {

    /**
     * User cache
     * 
     * @var \Instagram\User
     */
    protected $user = null;

    /**
     * Comments cache
     *
     * @var \Instagram\Collection\CommentCollection
     */
    protected $comments = null;

    /**
     * Location cache
     *
     * @var \Instagram\Location
     */
    protected $location = null;

    /**
     * Tags cache
     *
     * @var \Instagram\Collection\TagCollection
     */
    protected $tags = null;

    /**
     * Get the thumbnail
     *
     * @return string
     * @access public
     */
    public function getThumbnail() {
        return $this->data->images->thumbnail;
    }

    /**
     * Get the standard resolution image
     *
     * @return string
     * @access public
     */
    public function getStandardRes() {
        return $this->data->images->standard_resolution;
    }

    /**
     * Get the low resolution image
     *
     * @return string
     * @access public
     */
    public function getLowRes() {
        return $this->data->images->low_resolution;
    }

    /**
     * Get the media caption
     *
     * @return string
     * @access public
     */
    public function getCaption() {
        if ( $this->data->caption ) {
            return new Comment( $this->data->caption );
        }
        return null;
    }

    /**
     * Get the created time
     *
     * @param string $format {@link http://php.net/manual/en/function.date.php}
     * @return string
     * @access public
     */
    public function getCreatedTime( $format = null ) {
        if ( $format ) {
            $date = date( $format, $this->data->created_time );
        }
        else {
            $date = $this->data->created_time;
        }
        return $date;
    }

    /**
     * Get the user that posted the media
     *
     * @return \Instagram\User
     * @access public
     */
    public function getUser() {
        if ( !$this->user ) {
            $this->user = new User( $this->data->user, $this->proxy );
        }
        return $this->user;
    }

    /**
     * Get media comments
     *
     * Return all the comments associated with a media
     *
     * @return \Instagram\CommentCollection
     * @access public
     */
    public function getComments() {
        if ( !$this->comments ) {
            $this->comments = new CommentCollection( $this->proxy->getMediaComments( $this->getApiId() ), $this->proxy );
        }
        return $this->comments;
    }

    /**
     * Get the media filter
     *
     * @return string
     * @access public
     */
    public function getFilter() {
        return $this->data->filter;
    }

    /**
     * Get the media's tags
     *
     * @return \Instagram\Collection\TagCollection
     * @access public
     */
    public function getTags() {
        if ( !$this->tags ) {
            $this->tags = new TagCollection( $this->data->tags, $this->proxy );
        }
        return $this->tags;
    }

    /**
     * Get the media's link
     *
     * @return string
     * @access public
     */
    public function getLink() {
        return $this->data->link;
    }

    /**
     * Get the media's likes count
     *
     * @return int
     * @access public
     */
    public function getLikesCount() {
        return (int)$this->data->likes->count;
    }

    /**
     * Get media likes
     *
     * Media objects contain the first 10 likes. You can get these likes by passing `false`
     * to this method. Using the internal likes of a media object cause issues when liking/disliking media.
     *
     * @param bool $fetch_from_api Query the API or use internal
     * @return \Instagram\UserCollection
     * @access public
     */
    public function getLikes( $fetch_from_api = true ) {
        if ( !$fetch_from_api ) {
            return new UserCollection( $this->data->likes );
        }
        $user_collection = new UserCollection( $this->proxy->getMediaLikes( $this->getApiId() ), $this->proxy );
        $user_collection->setProxies( $this->proxy );
        $this->likes = $user_collection;
        return $this->likes;
    }

    /**
     * Get location status
     *
     * Will return true if any location data is associated with the media
     *
     * @return bool
     * @access public
     */
    public function hasLocation() {
        return isset( $this->data->location->latitude ) && isset( $this->data->location->longitude );
    }

    /**
     * Get location status
     *
     * Will return true if the media has a named location attached to it
     *
     * Some media only has lat/lng data
     *
     * @return bool
     * @access public
     */
    public function hasNamedLocation() {
        return isset( $this->data->location->id );
    }

    /**
     * Get the location
     *
     * Returns the location associated with the media or null if no location data is available
     *
     * @param bool $force_fetch Don't use the cache
     * @return \Instagram\Location|null
     * @access public
     */
    public function getLocation( $force_fetch = false ) {
        if ( !$this->hasLocation() ) {
            return null;
        }
        if ( !$this->location || (bool)$force_fetch ) {
            $this->location = new Location( $this->data->location, isset( $this->data->location->id ) ? $this->proxy : null );
        }
        return $this->location;
    }

    /**
     * Magic toString method
     *
     * Returns the media's thumbnail url
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->getThumbnail()->url;
    }

}