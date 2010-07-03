<?php
/** Deprecated MySQLDAO class--DO NOT USE
 *
 * Do not use this class in any new code. Instead, use the PDODAO system in place. This deprecated class is still here
 * only for existing unit tests that use it. The production webapp no longer utilizes this class.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class MySQLDAO {
    var $logger;
    var $db;

    public function __construct($d, $l=null) {
        $this->logger = $l;
        $this->db = $d;
    }

    function executeSQL($q) {
        $r = null;
        try {
            $r = $this->db->exec($q);
        } catch(Exception $e) {
            if ( isset($this->logger) && $this->logger != null ){
                $this->logger->logStatus($e->getMessage(), get_class($this));
            } else {
                die(get_class($this) ." | " . $e->getMessage());
            }
        }
        return $r;
    }
}
