<?php
/**
 * OwnerInstance class
 *
 * This class represents an owner instance
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class OwnerInstance {
    /*
     * @var int owner id
     */
    var $owner_id;
    /*
     * @var int instance id
     */
    var $instance_id;

    /**
     * Constructor
     * @param int owner id - optional
     * @param int instance id - optional
     */
    function __construct($oid = null, $iid = null) {
        if($oid) { $this->owner_id = $oid; }
        if($iid) { $this->instance_id = $iid; }
    }
}

