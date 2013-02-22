<?php
/**
 *
 * ThinkUp/extras/dev/makemodel/classes/class.ModelMaker.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 *
 * ModelMaker
 *
 * Generates model object definitions based on a table name.
 */
class ModelMaker {
    /**
     *
     * @var str Name of the table the object will be based on.
     */
    var $table_name;
    /**
     *
     * @var str Name of the resulting object.
     */
    var $object_name;
    /**
     *
     * @var str Name of the object's parent (optional).
     */
    var $parent_object_name=null;
    /*
     * @var PDO Database handle
     */
    static $pdo=null;
    /**
     *
     * @var Config
     */
    var $config;
    /**
     *
     * @param str $table_name
     * @param str $object_name
     * @param str $parent_object_name
     * @return ModelMaker
     */
    public function __construct($table_name, $object_name, $parent_object_name = null) {
        $this->table_name = $table_name;
        $this->object_name = $object_name;
        $this->parent_object_name = $parent_object_name;
        $this->config = Config::getInstance();
        //connect to database
        if (is_null(self::$pdo)) {
            self::$pdo = $this->connect();
        }
    }
    /**
     * @return str Object definition
     */
    public function makeModel() {
        //show full columns from table;
        $columns = array();
        try {
            $stmt = self::$pdo->query('SHOW FULL COLUMNS FROM ' . $this->table_name);
            while ($row = $stmt->fetch()) {
                $row['PHPType'] = $this->converMySQLTypeToPHP($row['Type']);
                $columns[$row['Field']] = $row;
            }
        } catch(Exception $e) {
            throw new Exception('Unable to show columns from "' . $this->table_name . '" - ' . $e->getMessage());
        }

        //instantiate Smarty, assign results to view
        $view_mgr = new ViewManager();
        $view_mgr->assign('fields', $columns);
        $view_mgr->assign('object_name', $this->object_name);
        $view_mgr->assign('parent_name', $this->parent_name);

        $tpl_file = THINKUP_ROOT_PATH . 'extras/dev/makemodel/view/model_object.tpl';
        //output results
        $results = $view_mgr->fetch($tpl_file);
        return $results;
    }
    /**
     *
     * Connect to database using PDO
     * @return PDO
     */
    private function connect() {
        $db_string = sprintf("mysql:dbname=%s;host=%s", $this->config->getValue('db_name'),
        $this->config->getValue('db_host'));
        if ($this->DEBUG) {
            echo "DEBUG: Connecting to $db_string\n";
        }
        $db_socket = $this->config->getValue('db_socket');
        if ( $db_socket) {
            $db_string.=";unix_socket=".$db_socket;
        }
        $pdo = new PDO($db_string, $this->config->getValue('db_user'), $this->config->getValue('db_password'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     *
     * @param str $mysql_type
     * @return str PHP datatype
     */
    private function converMySQLTypeToPHP($mysql_type) {
        //if index of opening paren is not zero, substring start to opening paren
        if ($mysql_type == "int(1)") {
            $mysql_type = "bool";
        }
        if (strpos($mysql_type, "(") !== false) {
            $mysql_type = substr($mysql_type, 0, strpos($mysql_type, "("));
        }
        switch ($mysql_type) {
            case "varchar":
                return "str";
                break;
            case "bigint":
                return "int";
                break;
            case "timestamp":
                return "str";
                break;
            case "datetime":
                return "str";
                break;
            case "decimal":
                return "float";
                break;
            default:
                return $mysql_type;
        }
    }
}