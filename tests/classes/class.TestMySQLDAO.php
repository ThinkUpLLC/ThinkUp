<?php
/**
 *
 * ThinkUp/tests/classes/class.TestMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Christoffer Viken, Gina Trapani, Mark Wilkie
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
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Christoffer Viken, Gina Trapani, Mark Wilkie
 *
 * MySQL TestDAO implementation class for TestOfPDODAO and TestOfDAOFactory
 */
class TestMySQLDAO extends PDODAO implements TestDAO {

    // test select query using $stmt directly...
    public function getUserCount($min_id, $user_name) {
        $sql = "select * from #prefix#users where id > :id and user_name like :user_name order by user_id";
        $stmt = $this->execute($sql, array(':id'=>$min_id, ':user_name'=>'%'.$user_name.'%'));
        $result = $stmt->fetchAll();
        return $result;
    }

    // test insert and parent getInsertCount()
    public function insertDataGetCount($name, $id) {
        $sql = "insert into #prefix#test_table (test_name, test_id) values (:test_name, :test_id)";
        $stmt = $this->execute($sql, array(':test_name'=>$name, ':test_id'=>$id));
        return $this->getInsertCount($stmt);
    }

    // test insert and parent getInsertId()
    public function insertDataGetId($name, $id) {
        $sql = "insert into #prefix#test_table (test_name, test_id) values (:test_name, :test_id)";
        $stmt = $this->execute($sql, array(':test_name'=>$name, ':test_id'=>$id));
        return $this->getInsertId($stmt);
    }

    // test multi insert and parent getInsertCount()
    public function insertMultiDataGetCount($insert_data) {
        $sql = "insert into #prefix#test_table (test_name, test_id) values ";
        $values = null;
        $i = 0;
        $binds = array();
        foreach ($insert_data as $data) {
            if (!is_null($values)) {
                $values .= ',';
            }
            $values .= '(:name'.$i.', :id'.$i.')';
            $binds[':name'.$i] = $data[0];
            $binds[':id'.$i] = $data[1];
            $i++;
        }
        $sql .= $values;
        $stmt = $this->execute($sql, $binds);
        return $this->getInsertCount($stmt);
    }

    // test bad sql
    public function badSql() {
        $sql = "select na form mooo";
        $stmt = $this->execute($sql);
    }

    // test bad binds...
    public function badBinds() {
        $sql = "select test_name from #prefix#test_table where test_id = :test_id";
        $stmt = $this->execute($sql);
    }

    // test update test_name record
    public function update($name, $id) {
        $sql = "update #prefix#test_table set test_name = :test_name where test_id = :test_id";
        $stmt = $this->execute($sql, array(':test_name'=>$name, ':test_id'=>$id));
        return $this->getUpdateCount($stmt);
    }

    // test update test_name record(s)
    public function updateMulti($name, $id) {
        $sql = "update #prefix#test_table set test_name = :test_name where test_id > :test_id";
        $stmt = $this->execute($sql, array(':test_name'=>$name, ':test_id'=>$id));
        return $this->getUpdateCount($stmt);
    }

    // test select one record
    public function selectRecord($id) {
        $sql = "select id, test_name, test_id from #prefix#test_table where test_id = :test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getDataRowAsObject($stmt, 'TestData');
    }

    //select one record array
    public function selectRecordAsArray($id) {
        $sql = "select id, test_name, test_id from #prefix#test_table where test_id = :test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getDataRowAsArray($stmt);
    }

    // test select many records
    public function selectRecords($id) {
        $sql = "select id, test_name, test_id from #prefix#test_table where test_id >= :test_id order by test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getDataRowsAsObjects($stmt, 'TestData');
    }

    // test select many records with limit
    public function selectRecordsWithLimit($limit) {
        $sql = "select id, test_name, test_id from #prefix#test_table order by test_id LIMIT :limit";
        $stmt = $this->execute($sql, array(':limit' => $limit));
        return $this->getDataRowsAsObjects($stmt, 'TestData');
    }


    // test select many records as array
    public function selectRecordsAsArrays($id) {
        $sql = "select id, test_name, test_id from #prefix#test_table where test_id >= :test_id order by test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getDataRowsAsArrays($stmt, 'TestData');
    }

    // test delete records
    public function delete($id) {
        $sql = "delete from #prefix#test_table where test_id > :test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getUpdateCount($stmt);
    }

    // test record exist
    public function isExisting($id) {
        $sql = "select id, test_name, test_id from #prefix#test_table where test_id = :test_id";
        $stmt = $this->execute($sql, array(':test_id'=>$id));
        return $this->getDataIsReturned($stmt);
    }

    //test BoolToDB
    public function testBoolToDB($val) {
        return $this->convertBoolToDB($val);
    }

    public function getTimezoneOffset() {
        $sql = "SELECT @@session.time_zone AS tz_offset";
        $stmt = $this->execute($sql);
        return $this->getDataRowAsArray($stmt);
    }

    //for testing purposes only, when you need to start with a fresh new PDO connection
    public static function destroyPDO() {
        self::$PDO = null;
    }

}
