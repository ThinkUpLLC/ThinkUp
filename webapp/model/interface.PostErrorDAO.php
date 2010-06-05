<?php
/**
 * PostError Data Access Object
 *
 * Inserts post errors into the tt_post_error table.
 * Example post error text includes:
 * "No status found with that ID."
 * "Sorry, you are not authorized to see this status."
 * "This account is currently suspended."
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PostErrorDAO {
    /**
     * Insert a post error
     * @param int $id ID of the post that got the error
     * @param int $error_code The HTTP error code (such as 404 not found or 403 not authorized)
     * @param string $error_text Description of the error
     * @param int $issued_to ID of the authorized user who got the error.
     */
    public function insertError($id, $error_code, $error_text, $issued_to);
}