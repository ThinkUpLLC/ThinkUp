<?php
/**
 * Mutex Data Access Object implementation
 *
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class MutexMySQLDAO extends PDODAO implements MutexDAO {
    /**
     * Try to obtain a named mutex.
     * @param string $name
     * @param integer $timeout Default is 1 second.
     * @return boolean True if the mutex was obtained, false if another thread was already holding this mutex.
     */
    public function getMutex($name, $timeout=1) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT GET_LOCK(':name', ':timeout') AS result";
        $vars = array(
            ':name' => $lock_name,
            ':timeout' => $timeout
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }

    /**
     * Release a named mutex.
     * @param string $name
     * @return boolean True when a lock was released. False otherwise.
     */
    public function releaseMutex($name) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT RELEASE_LOCK(':name') AS result";
        $vars = array(
            ':name' => $lock_name
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }
}