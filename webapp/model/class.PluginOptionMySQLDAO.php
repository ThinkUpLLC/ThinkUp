<?php
/**
 * Plugin Option Data Access Object
 * The data access object for retrieving and saving plugin option data for thinkup
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

require_once 'model/class.PDODAO.php';
require_once 'model/interface.PluginOptionDAO.php';
require_once 'model/class.PluginOption.php';
require_once 'model/exceptions/class.BadArgumentException.php';

class PluginOptionMySQLDAO extends PDODAO implements PluginOptionDAO {

    public static $cached_options = array();

    public function deleteOption($id) {
        $q = 'DELETE FROM #prefix#plugin_options WHERE id = :id';
        $stmt = $this->execute($q, array(':id' => $id));
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertOption($plugin_id, $name, $value) {
        $q = 'INSERT INTO #prefix#plugin_options
                (plugin_id, option_name, option_value)
            VALUES
                (:plugin_id, :option_name, :option_value)';
        $stmt = $this->execute($q,
        array(':plugin_id' => $plugin_id, ':option_name' => $name, ':option_value' => $value) );
        return $this->getInsertId($stmt);
    }

    public function updateOption($id, $name, $value) {
        $q = 'UPDATE #prefix#plugin_options
            SET
                option_name = :option_name, 
                option_value = :option_value
            WHERE 
                id = :id';
        $stmt = $this->execute($q,
        array(':id' => $id, ':option_name' => $name, ':option_value' => $value) );
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getOptions($plugin_id = null, $cached = false) {
        $options = null;
        $cache_key = (! is_null($plugin_id) ) ? ($plugin_id . 'id'): 'all';
        if($cached && isset(self::$cached_options[$cache_key])) {
            $options = self::$cached_options[$cache_key];
        }
        if( is_null($options)) {
            $q = 'SELECT id, plugin_id, option_name, option_value
                FROM 
                    #prefix#plugin_options
                WHERE ';
            $q .= $plugin_id ? 'plugin_id = :plugin_id' : 'TRUE';
            $data = null;
            if($plugin_id) {
                $data = array(':plugin_id' => $plugin_id);
                $stmt = $this->execute($q, $data);
            } else {
                $stmt = $this->execute($q);
            }
            $stmt = $this->execute($q, $data);
            $options = $this->getDataRowsAsObjects($stmt, 'PluginOption');
            if(isset($options[0])) {
                if($cached) {
                    self::$cached_options[$cache_key] = $options;
                }
            } else {
                $options = null;
            }
        }
        return $options;
    }

    public function getOptionsHash($plugin_id, $cached = false) {
        $options = $this->getOptions($plugin_id, $cached);
        $options_hash = array();
        if(count( $options) > 0 ) {
            foreach ($options as $option) {
                $options_hash[ $option->option_name ] = $option;
            }
        }
        return $options_hash;
    }

    public function isValidPluginId($plugin_id) {
        $q = 'SELECT id FROM  #prefix#plugins where id = :id';
        $data = array(':id' => $plugin_id);
        $stmt = $this->execute($q, $data);
        return $this->getDataIsReturned($stmt);
    }
}