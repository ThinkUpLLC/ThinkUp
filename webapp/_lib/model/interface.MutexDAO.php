<?php
/**
 * Mutex Data Access Object interface
 *
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
interface MutexDAO {
    /**
     * Try to obtain a named mutex.
     * @param string $name
     * @return boolean True if the mutex was obtained, false if another thread was already holding this mutex.
     */
    public function getMutex($name);

    /**
     * Release a named mutex.
     * @param string $name
     */
    public function releaseMutex($name);

}