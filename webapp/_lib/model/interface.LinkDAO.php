<?php
/**
 * Link Data Access Object Interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */
interface LinkDAO {
    /**
     * Inserts a link into the database.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param int $post_id
     * @param str $network
     * @param bool $is_image
     * @return int insert ID
     */
    public function insert($url, $expanded, $title, $post_id, $network, $is_image = false );

    /**
     * Sets a expanded URL in storage.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param bool $is_image
     * @return int Update count
     */
    public function saveExpandedURL($url, $expanded, $title = '', $is_image = false );

    /**
     * Stores a error message.
     * @param str $url
     * @param str $error_text
     * @return int insert ID
     */
    public function saveExpansionError($url, $error_text);

    /**
     * Updates a URL in storage
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param int $post_id
     * @param str $network
     * @param bool $is_image
     * @return int Update count
     */
    public function update($url, $expanded, $title, $post_id, $network, $is_image = false );

    /**
     * Get the links posted by a users friends
     * @param int $user_id
     * @param str $network
     * @return array with Link objects
     */
    public function getLinksByFriends($user_id, $network);

    /**
     * Get the images posted by a users friends
     * @param int $user_id
     * @param str $network
     * @return array numbered keys, with Link objects
     */
    public function getPhotosByFriends($user_id, $network);

    /**
     * Gets a number of links that has not been expanded.
     * Non standard output - Sceduled for deprecation.
     * @param int $limit
     * @return array with numbered keys, with strings
     */
    public function getLinksToExpand($limit = 1500);

    /**
     * Gets all links with short URL statring with a prefix.
     * Non standard output - Scheduled for deprecation.
     * @param str $url
     * @return array with numbered keys, with strings
     */
    public function getLinksToExpandByURL($prefix);

    /**
     * Gets a link with a given ID
     * @param int $id
     * @return Link Object
     */
    public function getLinkById($id);

    /**
     * Gets the link with spscified short URL
     * @param $url
     * @return Link Object
     */
    public function getLinkByUrl($url);
}
