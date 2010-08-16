<?php
require_once 'model/class.Post.php';
require_once 'model/class.Link.php';

/**
 * Post Iterator. Used to iterate through the cursor of sql results for Posts
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class PostIterator implements Iterator {

    /*
     * @var object A PDO statment handle
     */
    private $stmt;

    /*
     * @var Post The current row, cursor value
     */
    private $row;

    /*
     * @var boolean defines if the current interation is valid
     */
    private $valid = false;

    /**
     * Contructor
     */
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }

    /**
     * Empty method, for this case nothing
     */
    function rewind() {
        // we can't rewind this stmt, so this won't do anything
    }

    /**
     * Returns the current row/Post
     * @return Post Current Post
     */
    function current() {
        return $this->row;
    }

    /**
     * Returns the current Post key/id
     * @return int The current Post id
     */
    function key() {
        return $this->row->id;
    }

    /*
     * Returns true if there is a row to fetch
     * @return boolean There is another value/row
     */
    function valid() {
        $this->valid = false;
        if(! is_null($this->stmt)) {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                $post = new Post($row);
                #$link = new Link($row);
                #$post->link = $link;
                $this->row = $post;
                $this->valid = true;
            }
        }
        return $this->valid;
    }

    /**
     * Empty method, for this case does nothing
     */
    function next() {
        // we handle the row call in vaide, so...
    }
}
