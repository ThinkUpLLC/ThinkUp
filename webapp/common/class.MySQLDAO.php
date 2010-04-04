<?php 
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
?>
