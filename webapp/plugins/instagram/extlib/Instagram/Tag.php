<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram;

use \Instagram\Collection\TagMediaCollection;

/**
 * Tag class
 *
 * @see \Instagram\Instagram->getTag()
 * {@link https://github.com/galen/PHP-Instagram-API/blob/master/Examples/tag.php}
 * {@link http://galengrover.com/projects/instagram/?example=tag.php}
 */
class Tag extends \Instagram\Core\BaseObjectAbstract {

    /**
     * Get tag media
     *
     * Retrieve the recent media posted with this tag
     *
     * This can be paginated with the next_max_id param obtained from MediaCollection->getNext()
     *
     * @param array $params Optional params to pass to the endpoint
     * @return \Instagram\Collection\MediaCollection
     * @access public
     */
    public function getMedia( array $params = null ) {
        return new TagMediaCollection( $this->proxy->getTagMedia( $this->getApiId(), $params ), $this->proxy );
    }

    /**
     * Get media count
     *
     * @return int
     * @access public
     */
    public function getMediaCount() {
        return (int)$this->data->media_count;
    }

    /**
     * Get tag name
     *
     * @return string
     * @access public
     */
    public function getName() {
        return $this->data->name;
    }

    /**
     * Get ID
     *
     * The ID for a tag is it's name, so return the name
     *
     * @return string
     * @access public
     */
    public function getId() {
        return $this->getName();
    }

    /**
     * Magic toString method
     *
     * Return the tag name
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->getName();
    }

}